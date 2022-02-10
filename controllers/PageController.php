<?php

class Transcript_PageController extends Omeka_Controller_AbstractActionController
{
  public function importAction() {
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
  			$this->view->content = "Nouvelles options.";
      }
    }

    $this->view->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $this->view->content = "";
  }

  public function exportAction() {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="transcript-options.json"');
    $this->view->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $this->view->content = "";
  }

  public function resetAction() {
    set_option('transcript_options', '');
    $file = file_get_contents(PLUGIN_DIR . '/Transcript/javascripts/controle.json');
    $tags = json_decode($file);
    $options = array();
    foreach ($tags as $tagname => $tag) {
      $options[$tagname] = $tag;
    }

		$options = serialize($options);
    set_option('transcript_controle', $options);

  	$this->_helper->flashMessenger('Les options par défaut ont été restaurées.');
    $this->view->content = "";
    $this->_helper->redirector('controle');
  }

  public function statsAction() {
		// Sauvegarde form dans DB
		$db = get_db();
		// Find all transcriptions and count them
		$nb_transcriptions = $db->query("SELECT count(e.id) nb FROM `$db->Elements` d, `$db->ElementTexts` e WHERE d.name = 'Transcription' AND d.description = 'A TEI tagged representation of the document.' AND d.id = e.element_id")->fetchAll();
		$nb_fichiers = $db->query("SELECT count(id) nb FROM `$db->Files`")->fetchAll();
    $nbItems = $db->query("SELECT COUNT(id) nb  FROM `$db->Items`")->fetchAll();
    $nbItemsWithTranscriptions = $db->query("SELECT COUNT(DISTINCT(f.item_id)) nb
              FROM `$db->Files` f
                LEFT JOIN `$db->ElementTexts` t ON f.id = t.record_id
                LEFT JOIN `$db->Elements` e ON e.id = t.element_id
              WHERE t.record_type = 'File' AND e.name = 'Transcription' AND e.description = 'A TEI tagged representation of the document.'")->fetchAll();
    // Find all files referenced by a PTR somewhere
    $nbFilesWithPTR = $db->query("SELECT text FROM `$db->ElementTexts` el LEFT JOIN `$db->Elements` e ON e.id = el.element_id WHERE e.description = 'A TEI tagged representation of the document.' AND el.record_type = 'File'")->fetchAll();
    $nbEnCours = $nb_transcriptions[0]['nb'];
    $fileIds = [];
    foreach ($nbFilesWithPTR as $i => $t) {
      libxml_use_internal_errors(true);
      $xml = new DOMdocument;
      $xml->encoding = "utf-8";
      $xml->formatOutput = true;
      $xml->preserveWhiteSpace = false;
      $xml->loadXML($t['text'], LIBXML_PARSEHUGE);
      $ptrs = $xml->getElementsByTagName('ptr');
      for ($i = 0; $i <= $ptrs->length - 1; $i++) {
        $ptr = $ptrs->item($i);
        $ptr->getAttribute('target') ? $fileIds[] = $ptr->getAttribute('target') : $fileIds[$i] = -1;
      }
    }
    $nbEnCours += count(array_unique($fileIds));
		$this->view->nb_items = $nbItems[0]['nb'];
		$this->view->nb_items_en_cours = $nbItemsWithTranscriptions[0]['nb'];
		$this->view->nb_fichiers = $nb_fichiers[0]['nb'];
		$this->view->nb_fichiers_en_cours = $nbEnCours;
    $this->view->percent_files = round($nbEnCours / $nb_fichiers[0]['nb'] , 2) * 100;
		$this->view->nb_transcriptions = $nb_transcriptions[0]['nb'];
    $this->view->content = "Statistiques";
    $this->view->percent = round($nbItemsWithTranscriptions[0]['nb'] / $nbItems[0]['nb'] , 2) * 100;
  }

  public function exportteiAction() {
 		$fileId = $this->getParam('fileid');
		$fichier = get_record_by_id('File', $fileId);
		set_current_record('file', $fichier);
  	$templateFileName = BASE_DIR . '/teibp/content/eman-transcription-template.xml';
  	$template = file_get_contents($templateFileName);
    $transcription = metadata('file', ['Transcript', 'Transcription']);

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($transcription);

    // Ajout d'une root node si absente
    if (! $xml) {
      $transcription = "<div>" . $transcription ."</div>";
      $xml = simplexml_load_string($transcription);
    }
    $doc = new DOMDocument;
    $doc->loadXML($transcription, LIBXML_PARSEHUGE);

    // Supprimer <i>
		$is = $doc->getElementsByTagName('i');
    while ($is->length > 0) {
      $p = $is->item(0);
      $p->parentNode->removeChild($p);
    }

    $xpath = new DOMXPath($doc);
    foreach($xpath->query('//*') as $tag) {
       $tag->removeAttribute('class');
    }

    $transcription = $doc->saveXML($doc->documentElement, LIBXML_NOXMLDECL);

		$transcriptionText = str_replace("<body>", "<body>" . $transcription, $template);
		$transcriptionText = str_replace(array("<headd", "</headd>","<ttable", "</ttable>", "xmllang", "<tittle", "</tittle>", "handshift", "persname", "<ffigure", "</ffigure"), array("<head", "</head>","<table", "</table>", "xml:lang", "<title", "</title>", "handShift", "persName", "<figure", "</figure>"), $transcriptionText);

    $this->view->transcription = $transcriptionText;
  }

	public function getForm()
	{
    $form = new Zend_Form();
    $form->setName('TranscriptionForm');
    // Retrieve list of item's metadata
    $db = get_db();
    $text = metadata('file', ['Transcript', 'Transcription']);
    $transcription = new Zend_Form_Element_Textarea('transcription');
    $transcription->setLabel('');
    $transcription->setAttrib('class', 'invisible');
    $transcription->setValue($text);
    $form->addElement($transcription);

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel('Enregistrer la transcription');
    $form->addElement($submit);

 		return $form;
	}

	public function optionsAction()
	{
    $form = new Zend_Form();
    $form->setName('OptionsForm');

    $field = new Zend_Form_Element_Text('teipburl');
    $field->setLabel('URL de l\'instance TEI Publisher pour le rendu des transcriptions');
    $field->setValue(get_option('teipburl'));
    $form->addElement($field);

    for ($i=1; $i <= 5; $i++) {
      $field = new Zend_Form_Element_Text('field_' . $i);
      $field->setLabel('Nom du champ d\'index supplémentaire ' . $i);
      $field->setValue(get_option('transcript_field_' . $i));
//       $field->setRequired(true);
      $form->addElement($field);
    }

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel('Sauvegarder les options.');
    $submit->setAttrib('class', 'add button small red');
    $form->addElement($submit);

		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
        set_option('transcript_field_1', $formData['field_1']);
        set_option('transcript_field_2', $formData['field_2']);
        set_option('transcript_field_3', $formData['field_3']);
        set_option('transcript_field_4', $formData['field_4']);
        set_option('transcript_field_5', $formData['field_5']);
        set_option('teipburl', $formData['teipburl']);
				$this->_helper->flashMessenger('Transcript options saved.');
			}
		}
    $this->prettifyForm($form);
		$this->view->content = $form;
	}

	public function listAction()
	{
    $db = get_db();
    $admin = strpos($_SERVER['REQUEST_URI'], 'admin');
    $orderBy = "item_title, file, collection_title, characters";
    if (isset($_GET['sort_field'])) {
      $orderBy = $_GET['sort_field'];
    }
    $orderDir = '';
    if (isset($_GET['sort_dir'])) {
            if ($_GET['sort_dir'] =='a') {
                    $orderDir = 'ASC';
            } else {
                    $orderDir = 'DESC';
            }
    }
    $orderBy == 'collection_title' ? $orderBy = ("collection_title $orderDir, item_title, file, characters") : null;
    $orderBy == 'item_title' ? $orderBy = ("item_title $orderDir, file, collection_title, characters") : null;
    $orderBy == 'characters' ? $orderBy = ("characters $orderDir, item_title, file, collection_title") : null;
    // Transcription element id
    $transcriptionElementId = $db->query("SELECT id FROM `$db->Elements` WHERE name = 'Transcription' AND description = 'A TEI tagged representation of the document.'")->fetchAll();
    $transcriptionElementId = $transcriptionElementId[0]['id'];

    $list = "<table id='transcript-list'>";
    $where = '';
    if ($admin) {
      $list .= "<thead><tr>" . browse_sort_links([__('Collection') => 'collection_title', __('Item') => 'item_title', __('Fichier') => 'file', __('Caractères') => 'characters'], ['link_tag' => 'th scope="col"', 'list_tag' => '']) . "</tr></thead><tbody>";
    } else {
      $list .= "<thead><tr>" . browse_sort_links([__('Collection') => 'collection_title', __('Item') => 'item_title', __('Fichier') => 'file'], ['link_tag' => 'th scope="col"', 'list_tag' => '']) . "</tr></thead><tbody>";
      $where = ' WHERE LENGTH(t.text) > 0';
    }

    $query = "SELECT f.original_filename file, f.id file_id, f.item_id item_id, IFNULL(ct.text, '[Pas de collection]') collection_title, c.id collection_id, it.text item_title, COALESCE(CHAR_LENGTH(t.text), 0) characters, t.text
              FROM `$db->Files` f
                LEFT JOIN `$db->ElementTexts` ft ON ft.record_id = f.id AND ft.record_type = 'File' AND ft.element_id = 50
                LEFT JOIN `$db->Items` items ON items.id = f.item_id
                LEFT JOIN `$db->ElementTexts` it ON items.id = it.record_id AND it.record_type = 'Item' AND it.element_id = 50
                LEFT JOIN `$db->Collections` c ON items.collection_id = c.id
                LEFT JOIN `$db->ElementTexts` ct ON c.id = ct.record_id AND ct.record_type = 'Collection' AND ct.element_id = 50
                LEFT JOIN `$db->ElementTexts` t ON f.id = t.record_id AND t.record_type = 'File' AND t.element_id = $transcriptionElementId
              WHERE f.item_id <> 0
              GROUP BY file
              ORDER BY $orderBy $orderDir";

    $transcriptions = $db->query($query)->fetchAll();
    $currentItem = 0;
    $collectionTitle = $currentCollection = '';
    $nbFiles = 0;
    $files = [];
    foreach ($transcriptions as $i => $transcription) {
      $files[$transcription['file_id']] = $transcription['file'];
    }
    $files[-1] = "Attribut 'target' non renseigné";
    foreach ($transcriptions as $id => $transcription) {
      $chunks = $fileIds = [];
      if ($currentItem <> $transcription['item_id']) {
        $currentItem = $transcription['item_id'];
        $transcription['item_title'] ? $itemTitle = $transcription['item_title'] : $itemTitle = "[Sans titre]";
        $itemLink = "<a target='_blank' href='" . WEB_ROOT . '/items/show/'. $transcription['item_id'] . "'>" . $itemTitle . "</a>";
        $itemTranscribed = false;
      } else {
        $itemLink = "";
      }
      if ($currentCollection <> $transcription['collection_title']) {
        $currentCollection = $transcription['collection_title'];
        $collectionTitle = $transcription['collection_title'];
      }
      $caracteres = $transcription['characters'];
      if ($caracteres > 0) {
        $itemTranscribed = true;
        libxml_use_internal_errors(true);
        $xml = new DOMdocument;
        $xml->encoding = "utf-8";
        $xml->formatOutput = true;
        $xml->preserveWhiteSpace = false;
        $xml->loadXML($transcription['text'], LIBXML_PARSEHUGE);
        $ptrs = $xml->getElementsByTagName('ptr');
        $fileIds = [];
        for ($i = 0; $i <= $ptrs->length - 1; $i++) {
          $ptr = $ptrs->item($i);
          $ptr->getAttribute('target') ? $fileIds[$i] = $ptr->getAttribute('target') : $fileIds[$i] = -1;
        }
        $chunks = explode('<ptr', $transcription['text']);
        // Le premier bloc de texte ne nous intéresse pas => le premier <ptr> est censé être au début de la transcription
        array_shift($chunks);
      } else {
        $fileIds = [0 => $transcription['file_id']];
      }
      $texts = [];
      $fileLinks = "";
      foreach ($chunks as $i => $chunk) {
        if (strlen($chunk) > 0 && $admin) {
          if (isset($files[$fileIds[$i]])) {
            $fileLinks .= "<a target='_blank' class='sub-file' href='" . WEB_ROOT . '/transcript/browse?fileid='. $fileIds[$i] . "'>" . $files[$fileIds[$i]] . " (" . strlen($chunk) . ")</a><br />";
          } else {
            $fileLinks .= "Fichier " . $fileIds[$i] . " référencé en PTR mais absent de la base.<br />";            }
        }
      }
      if (! in_array($transcription['file_id'], $fileIds) || $caracteres == 0 && $admin && ! $itemTranscribed) {
        if ((count($chunks) > 1 || array_count_values(array_column($transcriptions, 'item_id'))[$transcription['item_id']] == 1) && ! $admin) {
          $firstFile = $db->query("SELECT id FROM `$db->Files` WHERE item_id = ? ORDER BY id LIMIT 1", $transcription['item_id'])->fetchObject();
          $fileLinks = "<a target='_blank' href='" . WEB_ROOT . '/transcript/browse?fileid='. $firstFile->id . "'>Voir la transcription.</a>";
        } else {
        $fileLinks = "<a target='_blank' href='" . WEB_ROOT . '/transcript/browse?fileid='. $transcription['file_id'] . "'>" . $transcription['file'] . " (" . $transcription['characters'] . ")</a><br />" . $fileLinks;
        }
        $admin ? $numCar = "<td class='characters'>" . $caracteres . "</td>" : $numCar = "<td class='characters'></td>";
        $list .= "<tr class='normal'><td>$collectionTitle</td><td>$itemLink</td><td>$fileLinks</td>$numCar</tr>";
      }
      $collectionTitle = '';
      $nbFiles++;
    }
    $list = "<h1>Liste des transcriptions</h1><br ><br />" . $list . "</tbody></table>";
    $this->view->content = $list;
	}

  public function indexAction() {
 		$db = get_db();
 		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			unset($formData['zz_submit']);
			foreach ($formData as $id => $term) {
  			$fieldsValues = [];
        for ($j = 1; $j <= 5; $j++) {
          $fieldsValues[] = $term['field_' . $j];
        }
        $linkedTerms = '';
        if (isset($term['linked_terms'])) {
          // Update other terms
          foreach ($term['linked_terms'] as $x => $linkedTermId) {
            $linkedTermLinkedTerms = $db->query("SELECT linkedterms FROM `$db->TranscriptTerms` WHERE id = ?", [$linkedTermId])->fetchObject();
            $linkedTermLinkedTerms = explode(',', $linkedTermLinkedTerms->linkedterms);
            $linkedTermLinkedTerms[] = $id;
            $db->query("UPDATE `$db->TranscriptTerms` SET linkedterms = ? WHERE id = ?", [implode(',', array_unique($linkedTermLinkedTerms, SORT_NUMERIC)), $linkedTermId])->execute();
          }
          $linkedTerms = implode(',', $term['linked_terms']);
        }
        $fieldsValues = $term['field_1'] . '§'. $term['field_2'] . '§' . $term['field_3'] . '§' . $term['field_4'] . '§' . $term['field_5'] . '§';
        // Update main (current) term
        $db->query("UPDATE `$db->TranscriptTerms` SET definition = ?, fieldsvalues = ?, linkedterms = ? WHERE id = ?", [$term['definition'], $fieldsValues, $linkedTerms, $id])->execute();
			}
    }
 		$refresh = $this->getParam('refresh');
 		$letter = $this->getParam('letter');
 		$delete = $this->getParam('delete');

 		if ($delete) {
   		$db->query("DELETE FROM `$db->TranscriptTerms` WHERE id = $delete");
 		}

 		$where = '';
 		$letter ? null : $letter = 'a';
 		$where = "WHERE name LIKE '$letter%'";

    $dictionnaire = $db->query("SELECT id, name, definition FROM `$db->TranscriptTerms` ORDER BY name")->fetchAll();

    $dico = $alphabet = [];
    foreach ($dictionnaire as $i => $terme) {
      $dico[$terme['name']] = ['id' => $terme['id'], 'definition' => $terme['definition']] ;
      $alphabet[mb_substr($terme['name'], 0 , 1)] = "<a href='" . WEB_ROOT . "/admin/transcript/index?letter=" . mb_substr($terme['name'], 0 , 1) . "'>" . mb_strtoupper(mb_substr($terme['name'], 0 , 1)) . "</a>";
    }
    $alphabet = "<ul id='alphabet'><li>" . implode("</li><li>", $alphabet) . "</li></ul>";
    if ($refresh == 1) {
      // Scan des transcriptions à la recherche des tags 'term'
      $transcriptions = $db->query("SELECT record_id id, text FROM `$db->ElementTexts` WHERE record_type='File' AND text LIKE '%<term%' ORDER BY record_id")->fetchAll();
      $occurrences = [];
      foreach($transcriptions as $i => $transcription) {
        $xml = @new SimpleXMLElement($transcription['text']);
        $terms = $xml->xpath('//term');
        foreach ($terms as $term) {
          $texte = (string) $term->attributes()->ref[0];
          if (mb_substr($texte, 0, 1) == '#') {
            $texte = substr($texte, 1);
          }
          if ($texte && ! in_array($texte, array_keys($dico))) {
            $db->query("INSERT INTO `$db->TranscriptTerms` values(null, ?, 'À définir.', ?, '', '')", [$texte, $transcription['id']]);
            $dico[$texte] = ['id' => $db->lastInsertId(), 'definition' => 'À définir.'];
          }
          if (! isset($occurrences[$texte])) {
            $occurrences[$texte] = [];
          }
          ! isset($occurrences[$texte][$transcription['id']]) ? $occurrences[$texte][$transcription['id']] = [] : null;
          $n = (string) $term->attributes()->n[0];
          if ($n) {
            $occurrences[$texte][$transcription['id']][] = $n;
          }
        }
      }
      $occurrences = eman_sort_array($occurrences, 'value_list', null, 'a');
      foreach ($occurrences as $term => $files) {
        $refs = json_encode($files);
        $db->query("UPDATE `$db->TranscriptTerms` SET occurrences = ? WHERE id = ?", [json_encode($files), $dico[$term]['id']]);
      }
    }
    $form = $this->getTermsForm($where);
    $this->view->content = $alphabet . $form;
  }

  public function glossaireAction() {
    $db = get_db();
    $dictionnaire = $db->query("SELECT name FROM `$db->TranscriptTerms` ORDER BY name")->fetchAll();
    $content = "<ul class='transcript-list-terms'>";
    foreach ($dictionnaire as $i => $term) {
      $content .= "<li class='transcript-term'><a href='" . WEB_ROOT . "/transcript/show/" . $term['name'] . "'>" . ucfirst($term['name']) . "</a></li>";
    }
    $content .= "</ul>";
    $this->view->content = $content;
  }

  public function showTermAction() {
 		$term = $this->getParam('term');
    $this->view->occurrences = "Ce terme n'apparaît dans aucune transcription.";
    $this->view->fieldsvalues = '';
    $db = get_db();
    $transcriptionElementId = $db->query("SELECT id FROM `$db->Elements` WHERE name = 'Transcription' AND description = 'A TEI tagged representation of the document.'")->fetchObject();
    $transcriptionElementId = $transcriptionElementId->id;
    $termInfo = $db->query("SELECT id, name, definition, occurrences, fieldsvalues, linkedterms FROM `$db->TranscriptTerms` WHERE name = ?", $term)->fetchAll();
    if ($termInfo) {
      $this->view->term = ucfirst($termInfo[0]['name']);
      $this->view->definition = $termInfo[0]['definition'];
      $files = json_decode($termInfo[0]['occurrences']);
			$links = '<table><thead><td>Fichier</td><td>Occurrence</td><td>Contexte</td></thead>';
			if (is_object($files)) {
  			foreach ($files as $fileId => $anchors) {
    			$file = get_record_by_id('File', $fileId);
    			$title = metadata($file, ['Dublin Core', 'Title']);
    			$title ? null : $title = "Fichier $fileId";
          $transcription = $db->query("SELECT text FROM `$db->ElementTexts` WHERE record_id = ? AND record_type = 'File' AND element_id = ?", [$fileId, $transcriptionElementId])->fetchObject();
    			if ($anchors) {
      			$links .= "<tr><td colspan='3'><a target='_blank' href='" . WEB_ROOT . "/transcript/browse?fileid=$fileId'>$title</a></td></tr>";
      			foreach ($anchors as $x => $anchor) {
        			$context = $this->termContext($term, $anchor, $transcription->text, 500);
        			$links .= "<td></td><td><a target='_blank' href='" . WEB_ROOT . "/transcript/browse?fileid=$fileId#$anchor'>$anchor</a></td><td>[...] $context [...]</td></tr>";
            }
    			} else {
      			$links .= "<tr><td colspan='2'><a target='_blank' href='" . WEB_ROOT . "/transcript/browse?fileid=$fileId'>$title</a></td></tr>";
    			}
  			}
  		} else {
    			$file = get_record_by_id('File', $files);
    			$title = metadata($file, ['Dublin Core', 'Title']);
    			$title ? null : $title = "Fichier $files";
      		$links .= "<a target='_blank' href='" . WEB_ROOT . "/transcript/browse?fileid=$files'>$title</a>, ";
  		}
// 			$this->view->occurrences = substr($links, 0, -2) . '</table>';
			$this->view->occurrences = $links . '</table>';
			$fieldsvalues = explode('§', $termInfo[0]['fieldsvalues']);
      for ($j = 0; $j <= 4; $j++) {
  			$label = get_option('transcript_field_' . ($j + 1));
  			if (isset($fieldsvalues[$j]) && $fieldsvalues[$j] <> '') {
    			$this->view->fieldsvalues .= "<h4>$label : </h4><p>" . $fieldsvalues[$j] . '</p>';
        }
      }
      if ($termInfo[0]['linkedterms']) {
        $linkedTerms = explode(',', $termInfo[0]['linkedterms']);
        $termes = $db->fetchPairs("SELECT id, name FROM `$db->TranscriptTerms` ORDER BY name");
  			$termLinks = [];
  			foreach ($linkedTerms as $i => $id) {
    			$termLinks[] = "<a target='_blank' href='" . WEB_ROOT . "/transcript/show/" . $termes[$id] . "'>" . $termes[$id] . "</a>";
  			}
        $this->view->linkedTerms = implode(', ', $termLinks);
      } else {
        $this->view->linkedTerms = 'Aucun';
      }
    } else {
      $this->view->term = ucfirst($term);
      $this->view->definition = "Ce terme ne figure pas encore dans l'index";
    }
  }

	public function getTermsForm($where)
	{
    $db = get_db();
		$form = new Zend_Form();
		$form->setName('TranscriptTermsForm');
    $form->setSubFormDecorators(['FormElements', 'Fieldset']);

    $dictionnaire = $db->query("SELECT id, name, definition, occurrences, fieldsvalues, linkedterms FROM `$db->TranscriptTerms` $where ORDER BY name")->fetchAll();

    $listTerms = $db->query("SELECT id, name FROM `$db->TranscriptTerms` ORDER BY name")->fetchAll();

    $terms = [];
		foreach ($listTerms as $i => $term) {
  		$terms[$term['id']] = $term['name'];
    }

    foreach ($dictionnaire as $i => $term) {
      $subForm = new Zend_Form_SubForm('term_' . $term['id']);
      $subForm->setIsArray(true);

      $termElement = new Zend_Form_Element_Textarea('definition');
      $termElement->setLabel(ucfirst($term['name']));
      $termElement->setValue($term['definition']);
      $termElement->setAttrib('rows', '5');
      $termElement->setAttrib('class', 'transcript-term-definition');
      $termElement->setBelongsTo('term_' . $term['id']);
      $subForm->addElement($termElement);

      $fieldsvalues = explode('§', $term['fieldsvalues']);

      for ($j = 1; $j <= 5; $j++) {
        $field = new Zend_Form_Element_Text('field_' . $j);
        $name = get_option('transcript_field_' . $j);
        if (! $name) : continue; endif;
        $field->setLabel(get_option('transcript_field_' . $j));
        isset($fieldsvalues[$j -1]) ? $field->setValue($fieldsvalues[$j -1]) :$field->setValue('');
        $field->setAttrib('class', 'fieldName');
        $field->setBelongsTo('term_' . $term['id']);
        $subForm->addElement($field);
      }

      $linkedTerms = explode(',', $term['linkedterms']);
			$selectField = new Zend_Form_Element_Multiselect('linked_terms');
			$selectField->setLabel("Termes liés")
 				->setMultiOptions($terms);
 			$selectField->setValue(array_values($linkedTerms));
			$selectField->setAttrib('class', "transcript-linked-terms");
			$subForm->addElement($selectField);

 			$occurrences = new Zend_Form_Element_Note('occurrences');
			$occurrences->setLabel('');
      $files = json_decode($term['occurrences']);
			$links = '';
			if (is_object($files)) {
  			foreach ($files as $fileId => $anchors) {
    			$file = get_record_by_id('File', $fileId);
    			$title = metadata($file, ['Dublin Core', 'Title']);
    			$title ? null : $title = "Fichier $fileId";
    			if ($anchors) {
      			$links .= "<a target='_blank' href='" . WEB_ROOT . "/transcript/browse?fileid=$fileId'>$title</a> (";
      			foreach ($anchors as $x => $anchor) {
        			$links .= "<a target='_blank' href='" . WEB_ROOT . "/transcript/browse?fileid=$fileId#$anchor'>$anchor</a>, ";
            }
      			$links = substr($links, 0, -2) . "), ";
    			} else {
      			$links .= "<a target='_blank' href='" . WEB_ROOT . "/transcript/browse?fileid=$fileId'>$title</a>, ";
    			}
  			}
			} else {
  			$links .= "<a target='_blank' href='" . WEB_ROOT . "/transcript/browse?fileid=$fileId'>$title</a>, ";
			}
			$occurrences->setValue("Présent dans : " . substr($links, 0, -2) . "<br /><br /><a href='" . WEB_ROOT . "/admin/transcript/index?delete=" . $term['id'] . "'>Supprimer ce terme</a> - <a target='_blank' href='" . WEB_ROOT . "/transcript/show/" . $term['name'] . "'>Voir la page publique de ce terme</a>");
      $termElement->setAttrib('class', 'transcript-term-occurrences');
			$occurrences->setBelongsTo('terms');
			$subForm->addElement($occurrences);
      $termElement = new Zend_Form_Element_Hidden('id');
      $termElement->setValue($term['id']);
      $termElement->setBelongsTo('term_' . $term['id']);
      $subForm->addElement($termElement);

    	// Prettify form
    	$subForm->setDecorators(array(
    			'FormElements',
    			 array('HtmlTag', array('tag' => 'table')),
//          Pour garder la notation array dans $formData
//     			'Form'
    	));
    	$subForm->setElementDecorators(array(
    			'ViewHelper',
    			'Errors',
    			array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    			array('Label', array('tag' => 'td', 'class' => 'transcript-term-label', 'tagClass' => 'transcript-term-description-label')),
    			array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    	));

      $form->addSubForm($subForm, $term['id']);
    }

		$submit = new Zend_Form_Element_Submit('zz_submit');
		$submit->setLabel('Enregistrer les informations des termes.');
		$form->addElement($submit);

		return $form;
  }

  private function termContext($term, $ref, $transcription, $length = 100) {
    $term = "#" . $term;
    $transcription = strip_tags($transcription, '<term>');
    $search = "<term ref=\"$term\" n=\"$ref\">";
		$termPos = mb_stripos($transcription, $search, 0, 'UTF-8');
		$left = mb_substr($transcription, $termPos);
		$right = mb_substr($left, 0, mb_stripos($left, '</term>', 0, 'UTF-8'));
		$highlight = mb_substr($right, mb_stripos($right, '>', 0, 'UTF-8') + 1);
		$termPos > $length / 2 ? $contextStart = $termPos - $length / 2 : $contextStart = 0;
		$transcription = str_replace($highlight, "<span class='bold'>$highlight</span>", $transcription);
		$context = strip_tags(mb_substr($transcription, $contextStart, $length), '<span>');

		return $context;
  }

	private function prettifyForm($form) {
		$form->setDecorators(array(
				'FormElements',
				 array('HtmlTag', array('tag' => 'table')),
				'Form'
		));
		$form->setElementDecorators(array(
				'ViewHelper',
				'Errors',
				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
				array('Label', array('tag' => 'td', 'class' => 'transcript-term-label', 'tagClass' => 'transcript-term-description-label')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
		));
		return $form;
	}
}
