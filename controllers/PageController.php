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

  public function viewAction() {
 		$fileId = $this->getParam('fileid');
		$fichier = get_record_by_id('File', $fileId);
		$this->view->private = 0;
		if (! $fichier) {
      $this->view->private = 1;
      return;
		}
		$filename = str_replace('png', 'jpg', $fichier->filename);
		$this->view->fullSize = "<img src='" . WEB_ROOT . '/files/original/' . metadata($fichier, 'filename') . "' id='transcript-image'  />";
    $this->view->xmlFileName =  substr($filename, 0, strpos($filename, '.')) . '.xml';
    $options = unserialize(get_option('transcript_options', array()));
    $this->view->options = $options['options'];
    $this->view->french_names = $options['french_names'];
    $this->view->controles = json_encode(unserialize(get_option('transcript_controle', array())));
    $this->view->ilvl = $options['ilvl'];
    $this->view->clvl = $options['clvl'];
    $this->view->visu = $options['visu'];

		set_current_record('file', $fichier);

		// Fichier suivant / précédent
		require_once(PUBLIC_THEME_DIR . '/eman/custom.php');

    $this->view->fileId = $fileId;
    $this->view->filename = $filename;
		$this->view->pager = files_pager('/transcription/', 'transcriptions');
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

		$this->view->nb_fichiers = $nb_fichiers[0]['nb'];
		$this->view->nb_transcriptions = $nb_transcriptions[0]['nb'];
    $this->view->content = "Statistiques";
  }

  public function exportteiAction() {
 		$fileId = $this->getParam('fileid');
		$fichier = get_record_by_id('File', $fileId);
		set_current_record('file', $fichier);
  	$templateFileName = BASE_DIR . '/teibp/content/eman-transcription-template.xml';
  	$template = file_get_contents($templateFileName);
    $transcription = metadata('file', array('Transcript', 'Transcription'));

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($transcription);

    // Ajout d'une root node si absente
    if (! $xml) {
      $transcription = "<div>" . $transcription ."</div>";
      $xml = simplexml_load_string($transcription);
    }
    $doc = new DOMDocument;
    $doc->loadXML($transcription, LIBXML_PARSEHUGE);


    // Supprimer <i> et class
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
    $text = metadata('file', array('Transcript', 'Transcription'));
    $transcription = new Zend_Form_Element_Textarea('transcription');
    $transcription->setLabel('');
    $transcription->setValue($text);
    $form->addElement($transcription);

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel('Enregistrer la transcription');
    $form->addElement($submit);

 		return $form;
	}

	public function transcribeAction()
	{

		$fileId = $this->view->fileId = $this->getParam('fileid');
		$fichier = get_record_by_id('File', $fileId);
		set_current_record('file', $fichier);

		$form = $this->getForm();

    // Get preferences
    $options = unserialize(get_option('transcript_options', array()));
//     Zend_Debug::dump($options);
    $this->view->options = $options['options'];
    $this->view->french_names = $options['french_names'];
    $this->view->controles = json_encode(unserialize(get_option('transcript_controle', array())));
    $this->view->ilvl = $options['ilvl'];
    $this->view->clvl = $options['clvl'];
    $this->view->visu = $options['visu'];
//     $this->view->niveaux = $options['niveaux'];
		$filename = str_replace(array('png', 'JPEG'), array('jpg', 'jpg'), $fichier->filename);
		$this->view->fullSize = "<img src='" . WEB_ROOT . '/files/original/' . metadata($fichier, 'filename') . "' id='transcript-image'  />";
		$size = getimagesize(WEB_ROOT . '/files/thumbnails/' . $filename);
    $this->view->xmlFileName =  substr($filename, 0, strpos($filename, '.')) . '.xml';
    $this->view->imgwidth = $size[0];
    $this->view->imgheight = $size[1];
		$this->view->thumbnail = "<img style='width:" . $size[0] . "px;height:" . $size[1] . "px;' id='thumbnail' src='" . WEB_ROOT . '/files/thumbnails/' . metadata($fichier, 'filename') . "' />";

		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				// Sauvegarde form dans DB
				$db = get_db();
				// Find element_id for transcription
				$element_id = $db->query("SELECT id FROM `$db->Elements` WHERE name ='Transcription' AND description = 'A TEI tagged representation of the document.'")->fetchAll();
				$element_id = $element_id[0]['id'];
				$transcriptionText = $formData['transcription'];
				$transcriptionTextDb = $db->quote($transcriptionText);
				$transcriptionsDir = BASE_DIR . '/teibp/transcriptions/';
				$templateFileName = BASE_DIR . '/teibp/content/eman-transcription-template.xml';
				$template = file_get_contents($templateFileName);
				$xmlFileName = substr($fichier->filename, 0, strpos($fichier->filename, '.')) . '.xml';

				if ($transcriptionText == "<div></div>") {
					$db->query("DELETE FROM `$db->ElementTexts` WHERE record_id = $fileId AND element_id = $element_id");
					unlink($transcriptionsDir. $xmlFileName);
					$this->_helper->flashMessenger('Transcription effacée.');
				} else {
					$old = $db->query("SELECT 1 FROM `$db->ElementTexts` WHERE record_id = $fileId AND record_type = 'File' AND element_id = $element_id")->fetchAll();
					if ($old) {
						$query = "UPDATE `$db->ElementTexts` SET text = $transcriptionTextDb WHERE record_id = $fileId AND record_type = 'File' AND element_id = $element_id";
					}	else {
						$query = "INSERT INTO `$db->ElementTexts` (record_type, record_id, element_id, html, text) VALUES ('File', $fileId, $element_id, 1, $transcriptionTextDb)";
					}

					$db->query($query);

					// Sauvegarde dans le fichier xml

					// Remplacement des tags illégaux en HTML
					$transcriptionText = str_replace("<body>", "<body>" . $transcriptionText, $template);
					$transcriptionText = str_replace(array("<headd", "</headd>","<ttable", "</ttable>", "xmllang", "<tittle", "</tittle>","<ffigure", "</ffigure"), array("<head", "</head>","<table", "</table>", "xml:lang", "<title", "</title>","<figure", "</figure"), $transcriptionText);
					$transcriptionFile = fopen($transcriptionsDir. $xmlFileName, 'w');
					fwrite($transcriptionFile, $transcriptionText);
					fclose($transcriptionFile);
					$this->_helper->flashMessenger('Transcription sauvegardée.');
				}
			}
		}
		$this->view->form = $form;
		$this->view->linkToFile = WEB_ROOT . '/files/show/' . $fileId;
		$this->view->content = "";
		if (plugin_is_active("Commenting")) {
			ob_start(); // We need to capture plugin output
// 			CommentingPlugin::showComments(array('comments' => array('order' => 'desc', 'id' => $fileId), null, 'Item'));
// 			CommentingPlugin::showComments(array('comments' => array('id' => $fileId)));
			CommentingPlugin::showComments();
			$this->view->comments = '<div style="clear:both;">' . ob_get_contents() . '</div>';
			ob_end_clean();
		} else {
			$this->view->comments = "";
		}

		// Fichier suivant / précédent
		require_once(PUBLIC_THEME_DIR . '/eman/custom.php');
		$this->view->pager = files_pager('/transcribe/', 'transcriptions');
	}

	public function adminAction()
	{
		$form = $this->getAdminForm();
		$form2 = $this->getFileform();

		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
  		if (isset($formData['MAX_FILE_SIZE'])) {
        if (!$form2->zefile->receive()) {
            print "Error receiving the file";
        }
        $location = $form2->zefile->getFileName();
    		$fileContent = file_get_contents($location);
        $options = explode('||||', $fileContent);
        set_option('transcript_options', $options[0]);
        set_option('transcript_controle', $options[1]);
  		} else {
  			if ($form->isValid($formData)) {
//     			Zend_Debug::dump($formData);
  				// Sauvegarde tags choisis dans DB
          unset($formData['submit']);
  				$options = array();
          $options['options']	= array();
  				foreach ($formData as $tag => $data) {
    				if (is_array($data)) {
      				if (in_array('act', $data)) {
        				$options['options'][$tag][] = "act";
      				}
      				if (in_array('balises', $data)) {
        				$options['options'][$tag][] = "balises";
      				}
      				if (in_array('icones', $data)) {
        				$options['options'][$tag][] = "icones";
      				}
      				if (strstr($tag, '_ilvl')) {
                $options['ilvl'][substr($tag, 0, -5)] = $data;
      				} elseif (strstr($tag, '_clvl')) {
                $options['clvl'][substr($tag, 0, -5)] = $data;
      				}
    				} elseif (strstr($tag, '_fn')) {
              $options['french_names'][$tag] = $formData[$tag];
    				} elseif (strstr($tag, '_visu')) {
              $options['visu'][substr($tag, 0, -5)] = trim($data);
    				}
  				}
  				$options = serialize($options);

          set_option('transcript_options', $options);

  				$this->_helper->flashMessenger('Transcript tags preferences saved.');
  			}
  		}
		}
	  $this->view->form = $form;
    $this->view->formfichier = $form2;
	  $this->view->content = "Admin	Transcript";
  }

  public function getFileform() {
      $form = new Zend_Form();
      $form->setName('FileoptionsForm');
      $form->setAttrib('enctype', 'multipart/form-data');
      $fichier = new Zend_Form_Element_File('zefile');
//       $fichier->setLabel("Télécharger un fichier d'options");
      $form->addElement($fichier);
      $submit = new Zend_Form_Element_Submit('submit2');
      $submit->setLabel('Recharger les options archivées.');
      $submit->setAttrib('class', 'add button small red');
      $form->addElement($submit);
//       $this->prettifyFileForm($form);
      return $form;
  }

	public function controleAction()
	{
		$form = $this->getControleForm();

		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				// Sauvegarde controle de cohérence dans DB
				$db = get_db();
				$options = array();
				unset($formData['submit']);
//         $options['options']	= array();
        foreach ($formData as $tag => $controle) {
          $options[$tag] = explode(' ', $controle);
        }
				$options = serialize($options);
        set_option('transcript_controle', $options);
				$this->_helper->flashMessenger('Transcript control preferences saved.');
			}
		}
		$form = $this->getControleForm();
		if (isset($form)) {
		  $this->view->form = $form;
		  $this->view->content = "Admin	Transcript";
		}
	}

	public function listAction()
	{
    $db = get_db();
    $orderBy = "file, item_title";
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
    $query = "SELECT f.original_filename file, f.id file_id, f.item_id item_id, i.text item_title
                                            FROM `$db->ElementTexts` t
                                                    INNER JOIN `$db->Files` f ON f.id = t.record_id
                                                    INNER JOIN `$db->ElementTexts` i ON f.item_id = i.record_id
                                            WHERE i.record_type = 'Item' AND i.element_id = 50 AND t.element_id = (SELECT id FROM `$db->Elements` WHERE `name` = 'Transcription' and element_set_id <> 3) ORDER BY $orderBy $orderDir";
    $transcriptions = $db->query($query)->fetchAll();
    $list = "<table>";
    $list .= "<thead><tr>" . browse_sort_links(array(__('Item') => 'item_title', __('Fichier') => 'file',), array('link_tag' => 'th scope="col"', 'list_tag' => '')) . "<th>Caractères</th></tr></thead><tbody>";
    foreach ($transcriptions as $id => $transcription) {
  		$fichier = get_record_by_id('File', $transcription['file_id']);
  		set_current_record('file', $fichier);
      $caracteres = strlen(metadata('file', array('Transcript', 'Transcription')));
      $list .= "<tr><td><a href='" . WEB_ROOT . '/items/show/'. $transcription['item_id'] . "'>" . $transcription['item_title'] . "</a></td><td><a href='" . WEB_ROOT . '/transcribe/'. $transcription['file_id'] . "'>" . $transcription['file'] . "</a></td><td>" . $caracteres . "</td></tr>";
    }
    $list .= "</tbody></table>";
    $this->view->content = $list;
	}

	public function getAdminForm($tag = null)
	{

		$form = new Zend_Form();
		$form->setName('TranscriptAdminForm');

    $file = file_get_contents(PLUGIN_DIR . '/Transcript/javascripts/buttons.json');
    $tags = json_decode($file);

    usort($tags->buttons, function ($a, $b) {return strcmp($a->cl, $b->cl);});
    $options = get_option('transcript_options', array());

    if ($options <> '') {
      $options = unserialize($options);
    } else {
      $options = array('options' => array(), 'french_names' => array());
    }
//   Zend_Debug::dump($options);
    if (isset($tag) && $tag <> null) {
      $newtag = new stdClass();
      $newtag->cl = $tag;
      $tags->buttons[] = $newtag;
//       $options['options'][$tag] = array('act');
    }

		foreach ($tags->buttons as $id => $tag) {
      $tagElement = new Zend_Form_Element_MultiCheckbox($tag->cl, array(
        'multiOptions' => array('act' => 'Active', 'balises' => 'Commentaires', 'icones' => 'Icones'),
      ));
      $values = array();
      if (key_exists($tag->cl, $options['options'])) {
        if (in_array('act', $options['options'][$tag->cl])) {
          $values[] = 'act';
        }
        if (in_array('balises', $options['options'][$tag->cl])) {
          $values[] = 'balises';
        }
        if (in_array('icones', $options['options'][$tag->cl])) {
          $values[] = 'icones';
        }
      }
      $tagElement->setValue($values);
      $label = strtoupper($tag->cl);
      $label = str_replace(array("FFIGURE","HEADD", "TITTLE"), array ("FIGURE", "HEAD", "TITLE"), $label);
      if (isset ($tag->att)) {
        $label .= "\n\r(" . $tag->att;
        if (isset ($tag->att2)) {
          $label .= ", " . $tag->att2;
        }
        $label .= ')';
      }
/*
      $d = $tagElement->getDecorator('Label');//->setOption('class', 'transcript-tag');
      Zend_Debug::dump($d);
*/
      $tagElement->setLabel($label);
      $tagElement->getDecorator('Label')->setOption('tagClass', 'transcript-tag');
			$form->addElement($tagElement);

			// Icone présent à quel niveau de détail de la transcription ?
      $iconLevel = new Zend_Form_Element_MultiCheckbox($tag->cl . '_ilvl', array(
        'multiOptions' => array('un' => 'Affichage 1', 'deux' => 'Affichage 2'),
      ));
      $iconLevel->setLabel("Niveau d'affichage de l'icone");
      isset($options['ilvl'][$tag->cl]) ? $ilvl = $options['ilvl'][$tag->cl] : $ilvl = '';
      $iconLevel->setValue($ilvl);
      $form->addElement($iconLevel);

			// Commentaire présent à quel niveau de détail de la transcription ?
      $comLevel = new Zend_Form_Element_MultiCheckbox($tag->cl . '_clvl', array(
        'multiOptions' => array('un' => 'Affichage 1', 'deux' => 'Affichage 2'),
      ));
      $comLevel->setLabel("Niveau d'affichage du commentaire ?");
      isset($options['clvl'][$tag->cl]) ? $clvl = $options['clvl'][$tag->cl] : $clvl = '';
      $comLevel->setValue($clvl);
      $form->addElement($comLevel);


			// Signalement balise
      $visuName = new Zend_Form_Element_Text($tag->cl . '_visu');
      $visuName->setLabel('Texte du commentaire');
      isset($options['visu'][$tag->cl]) ? $visu = $options['visu'][$tag->cl] : $visu = '';
      $visuName->setValue($visu);
      $form->addElement($visuName);
			// Tag's French name
      $frenchName = new Zend_Form_Element_Text($tag->cl . '_fn');
      $frenchName->setLabel('Nom dans le menu');
      isset($options['french_names'][$tag->cl . '_fn']) ? $french = $options['french_names'][$tag->cl . '_fn'] : $french = '';
      $frenchName->setValue($french);
      $form->addElement($frenchName);
		}

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Enregistrer les options des tags');
		$form->addElement($submit);

		$form = $this->prettifyForm($form);
		return $form;
	}

	public function getControleForm()
	{

		$form = new Zend_Form();
		$form->setName('TranscriptControleForm');

    $options = unserialize(get_option('transcript_controle', array()));

    $file = file_get_contents(PLUGIN_DIR . '/Transcript/javascripts/controle.json');
    $tags = json_decode($file);
    $tags = get_object_vars($tags);
    ksort($tags);

    if (!$options) {
      foreach ($tags as $tagname => $tag) {
        $options[$tagname] = implode(' ', $tag);
      }
    }
    ksort($options);

//         Zend_Debug::dump($options);

		foreach ($tags as $tag => $contexte) {
			// Tag's name
      $controle = new Zend_Form_Element_Text($tag);
      $controle->setLabel(strtoupper($tag));
  		if (isset($options[$tag])) {
        $controle->setValue(implode (' ', $options[$tag]));
  		} else {
        $controle->setValue(implode (' ', $tags->{$tag}));
  		}
      $controle->setAttrib('size', '60');
      $form->addElement($controle);
		}


		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Enregistrer les règles de cohérence des tags');
		$form->addElement($submit);

		$form = $this->prettifyForm($form);
		return $form;
	}

  private function prettifyFileForm($form) {
		// Prettify form
		$form->setDecorators(array(
				'File',
				 array('HtmlTag', array('tag' => 'table')),
				'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
//         array('Label', array('tag' => 'th')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
		));
/*
		$form->setElementDecorators(array(
				'ViewHelper',
				'Errors',
				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
				array('Label', array('tag' => 'td', 'style' => 'text-align:right;float:right;')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
		));
*/
		return $form;
	}

	private function prettifyForm($form) {
		// Prettify form
		$form->setDecorators(array(
				'FormElements',
				 array('HtmlTag', array('tag' => 'table')),
				'Form'
		));
		$form->setElementDecorators(array(
				'ViewHelper',
				'Errors',
				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
				array('Label', array('tag' => 'td', 'style' => 'text-align:right;float:right;')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
		));
		return $form;
	}
}
