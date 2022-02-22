<?php

class Transcript_BrowserController extends Omeka_Controller_AbstractActionController
{

  public function init() {
    $this->db = get_db();
    $db = $this->db;
    $this->transcriptionTermId = $db->query("SELECT id FROM `$db->Elements` WHERE name ='Transcription' AND description = 'A TEI tagged representation of the document.'")->fetchObject()->id;
    $this->user = new StdClass;
    $user = current_user();
    $user ? $this->user = $user : $this->user->role = 'public';
  }

	private function getForm($file = null)
	{
    $form = new Zend_Form();
    $form->setName('TranscriptionForm');
    // Retrieve list of item's metadata
    $db = $this->db;
    if ($file) {
      $text = metadata($file, array('Transcript', 'Transcription'));
    } else {
      $text = '';
    }
    $transcription = new Zend_Form_Element_Textarea('transcription');
    $transcription->setLabel('');
    $transcription->setAttrib('class', 'invisible');
    $transcription->setValue($text);
    $form->addElement($transcription);

    $fileId = new Zend_Form_Element_Hidden('fileid');
    if ($file) : $fileId->setvalue($file->id); endif;
    $form->addElement($fileId);

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel('Enregistrer la transcription');
    $form->addElement($submit);

 		return $form;
	}

	private function getToolbarForm($itemId = null, $fileId = null, $class = 'admin')
	{
 		$fileId = $this->getParam('fileid');

    $form = new Zend_Form();
    $form->setName('ToolbarForm');
    $form->setAttrib('class', $class);

    $db = $this->db;
    // Retrieve items' list
    $items = $db->query("SELECT i.id id, e.text title FROM `$db->Items` i LEFT OUTER JOIN `$db->ElementTexts` e ON i.id = e.record_id AND e.record_type = 'Item' AND e.element_id = 50 ORDER BY i.id ASC")->fetchAll();

    $listItems = [];
    foreach ($items as $i => $item) {
      if ($item['title']) {
        $title = explode(PHP_EOL, wordwrap($item['title'], 80));
        $title = array_shift($title);
      } else {
        $title = 'Item ' . $item['id'];
      }
      $listItems[$item['id']] = $title;
    }

    $listItems = eman_sort_array($listItems, 'text', null, 'a');
    $itemsList = new Zend_Form_Element_Select('items');
		$itemsList->setMultiOptions($listItems);
		$itemsList->setLabel("Items");
		if ($itemId) : $itemsList->setValue($itemId); endif;
		$form->addElement($itemsList);

    $filesList = new Zend_Form_Element_Select('files');
		$filesList->setMultiOptions([0 => 'None']);
		$filesList->setLabel("Fichiers");
		if ($fileId) : $filesList->setValue($fileId); endif;
		if (! $this->user) {
  		$filesList->setAttrib('class', 'hidden');
		}
		$form->addElement($filesList);

    if ($this->user) {
      $upload = new Zend_Form_Element_File('import');
      $upload->setLabel('Imp. XML')
               ->setDestination('/data/www/Omeka/upload');
  		$form->addElement($upload);
      $form->setAttrib('enctype', 'multipart/form-data');
    }

 		return $form;
	}

  public function ajaxItemIdFromFileIdAction() {
 		$fileId = $this->getParam('fileid');
 		$file = get_record_by_id('File', $fileId);
    $this->_helper->json($file->item_id);
  }

  public function ajaxFetchFilesAction() {
 		$itemId = $this->getParam('itemid');
 		$db = $this->db;
 		$files = $db->query("SELECT f.id id, f.original_filename name, e.text title, f.order ordre FROM `$db->Files` f LEFT JOIN `$db->ElementTexts` e ON f.id = e.record_id AND e.record_type = 'File' AND e.element_id = 50 WHERE f.item_id = $itemId ORDER BY ordre ASC, title ASC, name ASC")->fetchAll();
    $listFiles = [];
    foreach ($files as $i => $file) {
      $file['title'] ? $title = substr($file['title'], 0, 100) . " (" . $file['name'] . ")" : $title = "[Sans titre] - Fichier " . $file['name'];
      $listFiles[$file['id']] = sprintf("%1$04d_$title" , $i);
    }
    $this->_helper->json($listFiles);
  }

  public function ajaxFetchFilePictureAction() {
 		$fileId = $this->getParam('fileid');
 		if ($file = get_record_by_id('File', $fileId)) {
   		$fileName = str_replace(['png', 'PNG', 'jpeg', 'JPEG', 'JPG'], 'jpg', metadata($file, 'filename'));
   		$title = metadata($file, ['Dublin Core', 'Title']);
   		$title ? null : $title = "[Sans Titre] - Fichier $fileId";
   		$image = "<h3>$title</h3><a id='transcript-image-anchor-$fileId' /><div class='click-zoom'><label><input type='checkbox' /><img src='" . WEB_ROOT . '/files/fullsize/' . $fileName . "' id='transcript-image-$fileId' class='transcript-image' /></label></div>";
      $this->_helper->json([$image]);
    } else {
      $error = '';
   		if (! is_numeric($fileId)) {
     		$error .= "<br /> .. l'identifiant ne semble pas être numérique.";
   		}
   		$error .= "<a id='transcript-image-anchor-$fileId' /><h4>Fichier image avec l'id '" . $fileId . "' non trouvé.</h4>";
	    $this->_helper->json($error);
    }
  }

  public function ajaxFetchTranscriptionAction() {
 		$fileId = $this->getParam('fileid');
 		if (! $fileId || $fileId == 'null') {
   		return false;
 		}
 		$db = $this->db;
 		$file = get_record_by_id('File', $fileId);
    $firstFileId = $this->firstFileId($file->item_id);
    $fileInfo = "<h1><a target='_blank' href='". WEB_ROOT . '/files/fullsize/' . $file->filename . "'>Fichier original brut</a> - <a target='_blank' href='" . WEB_ROOT . "/files/show/" . $file->id . "'>Notice du fichier</a> - <a target='_blank' href='" . WEB_ROOT . "/teibp/transcriptions/" . str_replace(['png', 'PNG', 'jpeg', 'JPEG', 'JPG', 'jpg'], 'xml', $file->filename) ."'>Transcription TEI du fichier</a> - <a target='_blank' href='" . WEB_ROOT . "/items/show/" . $file->item_id . "'>Notice de l'item</a></h1>";

 		$transcription = $db->query("SELECT e.text text FROM `$db->Files` f LEFT JOIN `$db->ElementTexts` e ON f.id = e.record_id AND e.record_type = 'File' AND e.element_id = ? WHERE f.id = $fileId", $this->transcriptionTermId)->fetchObject();

 		$fileName = str_replace(['png', 'PNG', 'jpeg', 'JPEG', 'JPG'], 'jpg', metadata($file, 'filename'));
 		$title = metadata($file, ['Dublin Core', 'Title']);
 		$title ? null : $title = "[Sans Titre] - Fichier $fileId";

 		$image = "<a id='transcript-image-anchor-$fileId' /><h3>$title</h3><div class='click-zoom'><label><input type='checkbox'><img src='" . WEB_ROOT . '/files/fullsize/' . $fileName . "' id='transcript-image-$fileId' class='transcript-image' /></div></div>";
    $transcription->text ? $termes = $this->termOccurrences($transcription->text) : $termes = "";
    $this->_helper->json(['transcription' => $transcription->text, 'image' => $image, 'fileinfo' => $fileInfo, 'firstfileid' => $firstFileId, 'termes' => $termes]);
	}

  public function ajaxFetchRenditionAction() {
 		$fileId = $this->getParam('fileid');

		if (! $fileId) {
      return true;
		} else {
  		$fichier = get_record_by_id('File', $fileId);
      $fileName = str_replace(['png', 'PNG', 'jpeg', 'JPEG', 'JPG', 'jpg'], 'xml', $fichier->filename);
    }

 		$db = $this->db;
    $messages = '';
 		$transcription = $db->query("SELECT e.text text FROM `$db->Files` f LEFT JOIN `$db->ElementTexts` e ON f.id = e.record_id AND e.record_type = 'File' AND e.element_id = ? WHERE f.id = $fileId", $this->transcriptionTermId)->fetchObject();

		$firstFileId = $this->firstFileId($fichier->item_id);

    if ($fileId <> $firstFileId) {
      // PTR ?
      libxml_use_internal_errors(true);
      $xml = new DOMdocument;
      $xml->encoding = "utf-8";
      $xml->formatOutput = true;
      $xml->preserveWhiteSpace = false;
      $firstFileTranscription = $db->query("SELECT e.text text FROM `$db->Files` f LEFT JOIN `$db->ElementTexts` e ON f.id = e.record_id AND e.record_type = 'File' AND e.element_id = ? WHERE f.id = ?", [$this->transcriptionTermId, $firstFileId])->fetchObject();
      @$xml->loadXML($firstFileTranscription->text, LIBXML_PARSEHUGE);
      $ptrs = $xml->getElementsByTagName('ptr');
      $fileIds = [];
      for ($i = 0; $i <= $ptrs->length - 1; $i++) {
        $ptr = $ptrs->item($i);
        $ptr->getAttribute('target') ? $fileIds[] = $ptr->getAttribute('target') : $fileIds[$i] = -1;
      }
      if (in_array($fileId, $fileIds)) {
        $messages = "Attention : ce fichier est référencé dans la transcription du <a href='" . WEB_ROOT . "/transcript/browse?fileid=$firstFileId'>premier fichier de cet item</a>";
      }
    }

    $termes = $this->termOccurrences($transcription->text);

    $fileInfo = "<h1><a target='_blank' href='". WEB_ROOT . '/files/fullsize/' . $fichier->filename . "'>Fichier original brut</a> - <a target='_blank' href='" . WEB_ROOT . "/files/show/" . $fichier->id . "'>Notice du fichier</a> - <a target='_blank' href='" . WEB_ROOT . "/teibp/transcriptions/" . str_replace(['png', 'PNG', 'jpeg', 'JPEG', 'JPG', 'jpg'], 'xml', $fichier->filename) ."'>Transcription TEI du fichier</a> - <a target='_blank' href='" . WEB_ROOT . "/items/show/" . $fichier->item_id . "'>Notice de l'item</a></h1>";

    $filePath = BASE_DIR . '/teibp/transcriptions/' . $fileName;

    if (! file_exists($filePath)) {
      $this->_helper->json(['transcription' => 'Pas de transcription pour ce fichier.', 'fileinfo' => $fileInfo, 'termes' => $termes, 'messages' => $messages]);
      return;
    }

    // Contenu de la transcription
    $xmlContent = file_get_contents($filePath);

    // Appel pour le rendu
    $url = get_option('teipburl');

    if ($url) {
//       $url = 'http://localhost:8080/exist/apps/tei-publisher/api/preview?wc=true&odd=EMAN.odd';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $headers = array(
         "Accept: text/html",
         "Content-Type: application/xml;",
         "Expect:"
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlContent);
      $result = curl_exec($curl);
      curl_close($curl);
    } else {
      $result = "<p>Vous ne semblez pas avoir configuré l'URL TEI Publisher.</p>";
    }

    $this->_helper->json(['transcription' => $result, 'fileinfo' => $fileInfo, 'firstfileid' => $firstFileId, 'termes' => $termes]);
  }

	public function ajaxFetchFilesGalleryAction() {
		$filesGallery = "";
		$itemId = $this->getParam('itemid');
		$item = get_record_by_id('Item', $itemId);
		$db = $this->db;
		$transcriptTagId = $db->query("SELECT id FROM `$db->Elements` WHERE `name` = 'Transcription'")->fetchObject();
		if (metadata($item, 'has files')) {
			$filesGallery .= '<span>' . count($item->Files) . ' Fichier(s)' . '</span><div id="itemfiles" class="element">';
			$fileIds = [];
			$filesGallery .= '<div id="files-carousel" style="width:450px;margin:0 auto;">';
			foreach ($item->Files as $file) {
  			if ($transcriptTagId) {
    			if ($db->query("SELECT 1 FROM `$db->ElementTexts` WHERE record_type='File' AND record_id = " . $file->id . " AND element_id = " . $transcriptTagId->id)->fetchAll()) {
      			$fileIds[] = "<span>" . $file->id . "</span>";
    			}
  			}
        $filesGallery .= '<div id="' . $file->id . '" class="item-file image-jpeg">';
        $filesGallery .= '<a href="' . WEB_ROOT . '/files/show/' . $file->id . '">';
        $filesGallery .= '<img class="thumb" data-lazy="' . WEB_ROOT .'/files/square_thumbnails/' . str_replace(['png', 'PNG', 'jpeg', 'JPEG', 'JPG'], 'jpg', strtolower($file->filename)) . '" alt="' . $file->title . '" title="' . $file->title . '" />';
        $filesGallery .= '</a></div>';
			}
			$this->view->markTranscripted = implode('', $fileIds);
      $filesGallery .= '</div></div>';
		}
    $this->_helper->json($filesGallery);
	}

	public function browseAction()
	{
    $this->view->userRole = $this->user->role;
  	$db = $this->db;
		$fileId = $this->getParam('fileid');
		$message = "";
		if (! $fileId) {
      $fileName = $form = $this->view->pager = '';
      $form = $this->getForm();
      // If no fileId passed, get the first one in db
      $file = $db->query("SELECT id, item_id FROM `$db->Files` LIMIT 1")->fetchObject();
      $fileId = $file->id;
      $itemId = $file->item_id;
    }
    $fichier = get_record_by_id('File', $fileId);
		if ( ! $fichier) {
  		$this->view->content = "Ce fichier n'existe pas ou vous n'y avez pas accès.";
  		return;
		}
		$itemId = metadata($fichier, 'item_id');
    $fileName = $fichier->filename;
		set_current_record('file', $fichier);
    $form = $this->getForm($fichier);
 		require_once(PUBLIC_THEME_DIR . '/eman/custom.php');
    // Fichier suivant / précédent
		$this->view->pager = files_pager('/transcript/browse?fileid=', 'transcriptions');

    // Browser code
    if ($this->user) {
      $toolbar = $this->getToolbarForm($itemId, $fileId);
      $toolbar .= "<button id='regroup'>Regrouper les transcriptions de cet item.</button>";
      $toolbar .= "<button id='suppress'>Supprimer les transcriptions des autres fichiers de cet item.</button>";
    } else {
      $item = get_record_by_id('Item', $itemId);
      $toolbar = "<h3>" . strip_tags(metadata($item, ['Dublin Core', 'Title'])) . "</h3>";
      $toolbar .= $this->getToolbarForm($itemId, $fileId, "public");
    }

    $this->view->toolbar = $toolbar;

    // Get preferences
    $options = unserialize(get_option('transcript_options', array()));
    $this->view->options = $options['options'];
    $this->view->french_names = $options['french_names'];
    // Deduce constraints from XML schema
    $xmlSchema = simplexml_load_file(PLUGIN_DIR . '/Transcript/resources/cm-tei-schema.xml');
    $constraints = [];
    foreach ($xmlSchema as $tag => $members) {
      foreach ($members->children as $i => $childTag) {
        $childTag = (string) $childTag;
        ! isset($constraints[$childTag]) ? $constraints[$childTag] = [] : null;
        $constraints[$childTag][] = $tag;
      }
    }
    $controles = '{';
    foreach ($constraints as $name => $tag) {
      $controles .= '"' . $name . '":["' . implode('","', $tag) . '"],';
    }
    $controles = substr($controles, 0, -1) . '}';
    $this->view->controles = $controles;

		$filename = str_replace(['png', 'PNG', 'jpeg', 'JPEG', 'JPG'], 'jpg', $fileName);
		if ($fileName) {
  		$this->view->fullSize = "<img src='" . WEB_ROOT . '/files/fullsize/' . $filename . "' id='transcript-image-$fileId' class='transcript-image'  />";
		} else {
  		$this->view->fullSize = "<img src='" . WEB_ROOT . "/plugins/Transcript/resources/no-image.jpg' id='transcript-image-0' class='transcript-image' />";
		}
		$size = getimagesize(WEB_ROOT . '/files/thumbnails/' . $filename);
    $this->view->xmlFileName =  substr($filename, 0, strpos($filename, '.')) . '.xml';
    $this->view->imgwidth = $size[0];
    $this->view->imgheight = $size[1];
		$this->view->thumbnail = "<img style='width:" . $size[0] . "px;height:" . $size[1] . "px;' id='thumbnail' src='" . WEB_ROOT . '/files/thumbnails/' . $fileName . "' />";

		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
  			$doc = new DOMDocument();
  			$xml = $doc->createDocumentFragment();
  			@$xml->appendXML(str_replace("&nbsp;", "", $formData['transcription']));
        $xml = $doc->importNode($xml, true);
        @$doc->appendChild($doc->createElement('body'))->appendChild($xml);
				$this->saveTranscription($doc->saveXML($doc->documentElement, LIBXML_NOXMLDECL), $fileId);
			}
		}

		$this->view->form = $form;
		$this->view->content = "";
		if (plugin_is_active("Commenting")) {
  		// TODO : Maybe get rid of that part
			ob_start(); // We need to capture plugin output
// 			CommentingPlugin::showComments(array('comments' => array('order' => 'desc', 'id' => $fileId), null, 'Item'));
// 			CommentingPlugin::showComments(array('comments' => array('id' => $fileId)));
			CommentingPlugin::showComments();
			$this->view->comments = '<div style="clear:both;">' . ob_get_contents() . '</div>';
			ob_end_clean();
		} else {
			$this->view->comments = "";
		}
	}

  private function saveTranscription($xml, $fileId) {
    $file = get_record_by_id('File', $fileId);

    libxml_use_internal_errors(true);
		$doc = new DOMDocument();
    $doc->loadXML($xml, LIBXML_PARSEHUGE);
    // Supprimer <i>
    $is = $doc->getElementsByTagName('i');
    while ($is->length > 0) {
      $p = $is->item(0);
      $p->parentNode->removeChild($p);
    }

    $xml = $doc->saveXML($doc->documentElement); // LIBXML_NOXMLDECL => Non supporté

    $xml = preg_replace("/<\\?xml.*\\?>/", '', $doc->saveXML($doc), 1);

    $transcriptionsDir =  BASE_DIR . '/teibp/transcriptions/';

    $template = $this->createTeiHeader($fileId);

		$xmlFileName = substr($file->filename, 0, strpos($file->filename, '.')) . '.xml';

		$db = $this->db;

		if ($xml == "<div></div>" || $xml == "<body><div/></body>" || $xml == "<body/>" || strpos($xml, '<body/>')) {
			$db->query("DELETE FROM `$db->ElementTexts` WHERE record_id = ? AND element_id = ?", [$fileId, $this->transcriptionTermId]);
			@unlink($transcriptionsDir. $xmlFileName);
			$this->_helper->flashMessenger('Transcription effacée.');
		} else {
			$old = $db->query("SELECT 1 FROM `$db->ElementTexts` WHERE record_id = $fileId AND record_type = 'File' AND element_id = ?", [$this->transcriptionTermId])->fetchAll();
			if ($old) {
				$query = "UPDATE `$db->ElementTexts` SET text = ? WHERE record_id = $fileId AND record_type = 'File' AND element_id = " . $this->transcriptionTermId;
			}	else {
				$query = "INSERT INTO `$db->ElementTexts` (record_type, record_id, element_id, html, text) VALUES ('File', $fileId, " . $this->transcriptionTermId . ", 1, ?)";
			}
			$db->query($query, $xml);

			$this->_helper->flashMessenger('Transcription sauvegardée.');

			// Insertion de la transcription dans le template
			$xml = str_replace(['</body>', '<body>'], ['', $xml], $template);

			// Remplacement des tags illégaux en HTML
			$xml = str_replace(array("<headd", "</headd>","<ttable", "</ttable>", "xmllang", "<tittle", "</tittle>","<ffigure", "</ffigure"), array("<head", "</head>","<table", "</table>", "xml:lang", "<title", "</title>","<figure", "</figure"), $xml);
			// Sauvegarde dans le fichier xml
			$transcriptionFile = fopen($transcriptionsDir. $xmlFileName, 'w');
			fwrite($transcriptionFile, $xml);
			fclose($transcriptionFile);
		}
  }

	public function validateAction() {
    require_once('validate.php');
    $xml = json_decode($this->_request->getPost('xml'));
    $validator = new XmlValidator;
    $validated = $validator->validateFeeds($xml);
    if ($validated) {
    	$errors = ['OK'];
    } else {
    	$resp = $validator->displayErrors();
    	$errors = [];
      foreach($resp[0] as $i => $error) {
        $errors[] = $error;
      }
    }
    $this->_helper->json($errors);
	}

  public function importTranscriptionAction() {
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			$form = new Zend_Form();
			if ($form->isValid($formData)) {
        $upload = new Zend_File_Transfer();
        $files = $upload->getFileInfo();
        if (!$files) {
            print "Veuillez sélectionner un fichier.";
        } else {
          $uploadPath = BASE_DIR . '/teibp/transcriptions/';
          $file = $files['file'];
          // Save uploaded XML file
          $upload->setDestination($uploadPath);
          $upload->receive();
          $filePath = $upload->getFileName();
          $uploadedFile = $uploadPath . basename($filePath);
          move_uploaded_file($filePath, $uploadedFile);
          $emanTags = new DOMDocument();
          $emanTags->load(PLUGIN_DIR . '/Transcript/resources/cm-tei-schema.xml');
          $xpath = new DOMXpath($emanTags);
          $emanTags = $xpath->query("//cm_tei_schema//*");
          $emantagNames = [];
          foreach($emanTags as $tag) {
            $emantagNames[$tag->nodeName] = $tag->nodeName;
          }
          sort($emantagNames);
          $emanTagsList = "<select class='dest' id='dest-xxx'><option value='none'>[None]</option>";
          foreach($emantagNames as $i => $tag) {
            $emanTagsList .= "<option value='$tag'>$tag</option>";
          }
          $emanTagsList .= "</select>";

          libxml_use_internal_errors(true);
          $transcription = new DOMDocument();
          $transcription->load($upload->getFileName());
          $xpath = new DOMXpath($transcription);
          $xpath->registerNamespace("tei", "http://www.tei-c.org/ns/1.0");
          $tags = $xpath->query("//tei:text//*");
          $tagNames = [];
          foreach($tags as $tag) {
            $tagNames[$tag->nodeName] = $tag->nodeName;
          }
          sort($tagNames);
          $html = "<table id='transcript-mapping'>";
          $html .= "<thead><tr><td class='header'>Tag d'origine dans le fichier à importer</td><td class='header'>Tag de destination dans le modèle EMAN</td></tr></thead>";
          foreach($tagNames as $i => $tag) {
            $tagList = str_replace("'$tag'", "'$tag' selected", $emanTagsList, $none);
            $none == 0 ? $red = 'red' : $red = '';
            $html .= "<tr><td class='row'><span class='orig' id='orig-$tag'>$tag</span></td><td class='row right $red'>" . str_replace('xxx', $tag, $tagList) . "</td></tr>";
          }
          $html .= "<tr><td colspan='2' id='messages'><button id='bt-import'>Importer >>></button></td>";
          $html .= "<tr><td colspan='2' id='xml-file-path'>$uploadedFile</td>";
          $html .= "</table>";
          echo $html;
        }
			}
    }
    $this->view->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $this->view->content = "";
  }

  public function doImportAction() {
    $xmlFile = $this->_request->getPost('xmlFile');
    $mapping = $this->_request->getPost('map');
    $fileId = $this->_request->getPost('fileId');
    libxml_use_internal_errors(true);
		$doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadXML(file_get_contents($xmlFile), LIBXML_PARSEHUGE);
    // Supprimer le header
    $text = $doc->getElementsByTagName('text')->item(0);
    $doc = new DOMDocument('1.0', 'utf-8'); // encoding important !!!
    $doc->appendChild($doc->importNode($text, true));
    if ($mapping) {
      // Remplacement des tags : on stocke l'ancien tag dans un attribut rend
      foreach ($mapping as $orig => $dest) {
        $origs = $doc->getElementsByTagName($orig);
        for ($i = $origs->length - 1; $i >= 0; $i --) {
          $nodePre = $origs->item($i);
          if ($dest == 'nomap') {
            $comment = $doc->createComment(' ' . $doc->saveXML($nodePre) . ' ');
            $nodePre->parentNode->replaceChild($comment, $nodePre);
          } else {
            $nodeDiv = $doc->createElement($dest, $nodePre->nodeValue);
            $attribute = $doc->createAttribute('rend');
            $attribute->value = $orig;
            $nodeDiv->appendChild($attribute);
            foreach ($nodePre->attributes as $attrName => $attrNode) {
              $nodeDiv->setAttribute($attrName, $attrNode->nodeValue);
            }
            $nodePre->parentNode->replaceChild($nodeDiv, $nodePre);
          }
        }
      }
    }
    // Sauvegarde base et fichier
    $this->saveTranscription($doc->saveXML($doc->getElementsByTagName('body')->item(0), LIBXML_NOXMLDECL), $fileId);
    // Retour call jQuery
    $transcription = new DOMDocument;
    $transcription->appendChild($transcription->importNode($doc->getElementsByTagName('body')->item(0), true));
    echo $transcription->saveXML($transcription->documentElement, LIBXML_NOXMLDECL);
    $this->view->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $this->view->content = "";
  }

  private function createTeiHeader($fileId) {

    $file = get_record_by_id('file', $fileId);
    $item = get_record_by_id('Item', metadata($file, 'item_id'));

    $header = new DOMDocument();
    $header->encoding = "utf-8";
    $header->formatOutput = true;
    $header->preserveWhiteSpace = false;

		$templateFileName = BASE_DIR . '/teibp/content/eman-transcription-template.xml';

    $header->loadXML(file_get_contents("$templateFileName"));

    $teiTag = $header->getElementsByTagName('TEI')->item(0);
    $teiHeader = $header->getElementsByTagName('teiHeader')->item(0);
    $fileDesc = $header->getElementsByTagName('fileDesc')->item(0);

    // teiHeader : Informations sur la notice
    $titleStmt = $header->getElementsByTagName('titleStmt')->item(0);
    $title = $header->createElement('title');
    $fileTitle = metadata($item, array('Dublin Core', 'Title'));
    $title->nodeValue = 'Fichier - ' . $fileTitle;
    $titleStmt->appendChild($title);
    $author = $header->createElement('author');
    $author->nodeValue = metadata($item, array('Dublin Core', 'Creator'));
    $titleStmt->appendChild($author);
    $fileDesc->appendChild($titleStmt);

    $editionStmt = $header->createElement('editionStmt');
    $edition = $header->createElement('edition');
    $date = $header->createElement('date');
    $date->nodeValue = metadata($item, array('Dublin Core', 'Date'));
    $edition->appendChild($date);
    $editionStmt->appendChild($edition);
    $respStmt = $header->createElement('repStmt');
    $resp = $header->createElement('resp');
    $resp->nodeValue = "chargé d'édition/chercheur";
    $name = $header->createElement('name');
    $name->nodeValue = metadata($item, array('Dublin Core', 'Contributor'));
    $respStmt->appendChild($resp);
    $respStmt->appendChild($name);
    $editionStmt->appendChild($respStmt);
    $fileDesc->appendChild($editionStmt);

    $publicationStmt = $header->getElementsByTagName('publicationStmt')->item(0);
    $publicationStmt->parentNode->removeChild($publicationStmt);
    $publicationStmt = $header->createElement('publicationStmt');

    $publisher = $header->createElement('publisher');
    $publisher->nodeValue = metadata($item, array('Dublin Core', 'Publisher'));
    $publicationStmt->appendChild($publisher);

    $pubPlace = $header->createElement('pubPlace');
    $pubPlace->nodeValue = 'PARIS';
    $publicationStmt->appendChild($pubPlace);
    $address = $header->createElement('address');
    $addressLine = $header->createElement('addrLine');
    $addressLine->nodeValue = 'https://eman-archives.org';
    $instanceAttribute = $header->createAttribute('id');
    $instanceAttribute->value = substr(BASE_DIR, strrpos(BASE_DIR, '/') + 1);
    $addressLine->appendChild($instanceAttribute);
    $address->appendChild($addressLine);
    $publicationStmt->appendChild($address);

    $idno = $header->createElement('idno');
    $idno->nodeValue = metadata($item, array('Dublin Core', 'Identifier'));
    $publicationStmt->appendChild($idno);
    $date = $header->createElement('date');
    $date->nodeValue = metadata($item, array('Dublin Core', 'Date'));
    $publicationStmt->appendChild($date);

    $availability = $header->createElement('availability');
    $license = $header->createElement('license');
    $license->nodeValue = metadata($item, array('Dublin Core', 'Rights'));
    $availability->appendChild($license);
    $publicationStmt->appendChild($availability);
    $fileDesc->appendChild($publicationStmt);

    $sourceDesc = $header->createElement('sourceDesc');
    $bibl = $header->createElement('bibl');
    $bibl->nodeValue = metadata($item, array('Dublin Core', 'Source'));
    $sourceDesc->appendChild($bibl);
    $fileDesc->appendChild($sourceDesc);

    $profileDesc = $header->createElement('profileDesc');
    $abstract = $header->createElement('abstract');
    $abstract->nodeValue = metadata($item, array('Dublin Core', 'Description'));
    $profileDesc->appendChild($abstract);
    $textClass = $header->createElement('textClass');
    $keywords = $header->createElement('keywords');
    $schemeAttribute = $header->createAttribute('scheme');
    $schemeAttribute->value = '???';
    $keywords->appendChild($schemeAttribute);

    $list = $header->createElement('list');
    $list->nodeValue = '';

    $keywords->appendChild($list);
    $textClass->appendChild($keywords);
    $profileDesc->appendChild($textClass);

    $langUsage = $header->createElement('langUsage');
    $language = $header->createElement('language');
    $identAttribute = $header->createAttribute('ident');
    $identAttribute->value = 'fre';
    $language->appendChild($identAttribute);
    $language->nodeValue = metadata($item, array('Dublin Core', 'Language'));
    $langUsage->appendChild($language);
    $profileDesc->appendChild($langUsage);

    $teiHeader->appendChild($profileDesc);

    $encodingDesc = $header->createElement('encodingDesc');
    $ptr = $header->createElement('ptr');
    $target = $header->createAttribute('target');
    $target->nodeValue = $fileId;
    $ptr->appendChild($target);
    $cRef = $header->createAttribute('cRef');
    $cRef->nodeValue = $file->filename;
    $ptr->appendChild($cRef);
    $encodingDesc->appendChild($ptr);
    $projectDesc = $header->createElement('projectDesc');
    $projectDesc->nodeValue = metadata($item, array('Dublin Core', 'Description'));
    $encodingDesc->appendChild($projectDesc);
    $teiHeader->appendChild($encodingDesc);

    return $header->saveXML($header->documentElement, LIBXML_NOXMLDECL);
  }

  // Terms occurrences
  private function termOccurrences($transcription) {
    if (! $transcription) : return ""; endif;
    try {
      $xml = @new SimpleXMLElement($transcription);
      $xml = $xml->text;
    } catch (Exception $e) {
      if ($this->user->role <> 'public') {
        $resp = "Les termes ne peuvent pas être extraits.<br />Le code de la transcription n'est pas valide, veuillez le vérifier.";
      } else {
        $resp = "Il n'y a pas de termes indexés dans cette transcription.";
      }
      return ["<h3>Liste des termes indexés dans cette transcription</h3>" . $resp];
    }
    $terms = $xml->xpath("//body//term");
    $termes = [];
    foreach ($terms as $term) {
      $ref = $term->attributes()->ref[0];
      if (mb_substr($ref, 0, 1) == '#') {
        $ref = mb_substr((string) $ref, 1);
      } else {
        $ref = (string) $ref;
      }
      $t = mb_strtoupper(mb_substr($ref, 0, 1)) . mb_substr($ref, 1);
      $termes[] = "<a target='_blank' href='" . WEB_ROOT . "/transcript/show/$t'>$t</a>";
    }
    $termes = eman_sort_array($termes, 'text');
    $message = "<h3>Liste des termes indexés dans cette transcription</h3>";
    empty($termes) ? $termes = [$message . "Il n'y a pas de termes indexés dans cette transcription."] : $termes = [$message . implode(', ', array_unique($termes))];

    return $termes;
  }

  private function firstFileId($itemId) {
    $db = $this->db;
    $fileId = $db->query("SELECT f.id FROM `$db->Files` f RIGHT JOIN `$db->ElementTexts` e ON e.record_id = f.id AND e.record_type = 'File' AND e.element_id = ? WHERE f.item_id = ? ORDER BY f.order, f.id LIMIT 1", [$this->transcriptionTermId, $itemId])->fetchObject();
    return $fileId->id;
  }

  public function regroupAction() {
    $db = $this->db;
    $itemId = $this->getParam('itemid');
    $query = "SELECT f.original_filename file, f.id file_id, f.item_id item_id, COALESCE(CHAR_LENGTH(t.text), 0) characters, t.text
              FROM `$db->Files` f
                LEFT JOIN `$db->Items` items ON items.id = f.item_id
                LEFT JOIN `$db->ElementTexts` t ON f.id = t.record_id AND t.record_type = 'File' AND t.element_id = ?
              WHERE f.item_id = ?
              ORDER BY f.order, f.id";
    $files = $db->query($query, [$this->transcriptionTermId, $itemId])->fetchAll();
    $transcription = '';
    if ($files) {
      $firstFile = $files[0];
      $transcription = str_replace(['<body>', '</body>'], '', $firstFile['text']);
      for ($i = 1; $i < count($files); $i++) {
        $files[$i]['characters'] == 0 ? $text = " [Imago est, non legitur] " : $text = $files[$i]['text'];
        $transcription .= '<ptr target="' . $files[$i]['file_id'] . '" />' . $text;
      }
      $transcription = "<body>$transcription</body>";
    }
    $this->_helper->json($transcription);
  }

  public function suppressAction() {
    $db = $this->db;
    $itemId = $this->getParam('itemid');
    // Base
    $firstfileId = $this->firstFileId($itemId);
    $query = "DELETE FROM `$db->ElementTexts` WHERE record_type = 'File' AND element_id = ? AND record_id IN (SELECT id FROM `$db->Files` WHERE item_id = ? AND id <> ?)";
    $db->query($query, [$this->transcriptionTermId, $itemId, $firstfileId])->execute();
    // Fichiers
    $query = "SELECT filename FROM `$db->Files` WHERE item_id = ? AND id <> ?";
    $filenames = $db->query($query, [$itemId, $firstfileId])->fetchAll();
    $files = [];
    foreach ($filenames as $i => $filename) {
      $filename = BASE_DIR . '/teibp/transcriptions/' . str_replace(['png', 'PNG', 'jpeg', 'JPEG', 'JPG', 'jpg'], 'xml', $filename['filename']);
      unlink($filename);
      $files[] = $filename;
    }
    $this->_helper->json("Transcriptions supprimées pour l'item " . $itemId . ".<br />Fichiers supprimés : " . implode(', ', $files));
  }

	private function prettifyForm($form) {
		$form->setDecorators(array(
			'FormElements',
			 array('HtmlTag', array('tag' => 'div', 'class' => 'transcript-toolbar-form')),
			'Form'
		));
    $form->setElementDecorators(array(
        'ViewHelper',
        'Errors',
        array('Description', array('tag' => 'p', 'class' => 'description')),
        array('HtmlTag',     array('class' => 'form-div')),
        array('Label',       array('class' => 'form-label'))
      )
    );
    $form->setElementDecorators(array(
        'ViewHelper',
        'Label',
        new Zend_Form_Decorator_HtmlTag(array('tag' => 'div','class'=>'elem-wrapper'))
      )
    );

		return $form;
	}
}
