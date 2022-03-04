<?php

/*
 * Transcript Plugin
 *
 * Transcription Tool for the Eman project
 *
 */

class TranscriptPlugin extends Omeka_Plugin_AbstractPlugin
{
	/**
	 * The name of the Transcript element set.
	 */
	const ELEMENT_SET_NAME = 'Transcript';

  protected $_hooks = array(
		'public_content_top',
		'define_routes',
		'define_acl',
		'install',
		'admin_files_panel_buttons',
  );

  protected $_filters = array(
    'admin_navigation_main',
    'admin_items_form_tabs',
    'admin_collections_form_tabs',
    'admin_files_form_tabs',
  );

  public function filterAdminItemsFormTabs($tabs, $args)
  {
        unset ($tabs['Transcript']);
        return $tabs;
  }

  public function filterAdminCollectionsFormTabs($tabs, $args)
  {
  	unset ($tabs['Transcript']);
  	return $tabs;
  }

  public function filterAdminFilesFormTabs($tabs, $args)
  {
        unset ($tabs['Transcript']);
        return $tabs;
  }

  function hookAdminFilesPanelButtons($args)
  {
    echo "<a class='big green button' href='" . WEB_ROOT . "/transcript/browse?fileid=" . $args['record']->id . "'>Transcrire ce fichier</a>";
  }

  function hookPublicContentTop($args)
  {
		$params = Zend_Controller_Front::getInstance()->getRequest()->getParams();

    if (isset($params['action'])) {
      if ($params['action'] == 'transcribe' || ! isset($params['id'])) : return; endif;
    }
    if (isset($params['id']) && $params['action'] == 'files-show') {
      $file = get_record_by_id('file', $params['id']);
      // Si le fichier n'est pas public, on ne fait rien.
      if (! isset($file) && ! $file) : return; endif;
    }

  	if ($currentUser = current_user()) {
  		$transcribeLink = "";
			if (in_array($currentUser->role, array('super', 'admin', 'author', 'editor', 'researcher'))) {
    		if ($params['controller'] == 'files' && $params['action'] == 'show' || $params['controller'] == 'eman' && $params['action'] == 'files-show') {
  				$transcribeLink = WEB_ROOT . "/transcript/browse?fileid=" . $params['id'];
    			$linkText = 'Voir la transcription et transcrire ce fichier';
  			} elseif ($params['controller'] == 'eman' && $params['action'] == 'items-show') {
    			$db = get_db();
    			$fileId = $db->query("SELECT id FROM `$db->Files` f WHERE item_id = ? ORDER BY f.order ASC, id ASC LIMIT 1", $params['id'])->fetchObject();
          if (! $fileId) : return; endif;
    			$transcribeLink = WEB_ROOT . "/transcript/browse?fileid=" . $fileId->id;
    			$linkText = 'Voir la transcription et transcrire cet item';
  			}
  			if (isset($linkText)) {
  				print "<a class='eman-edit-link' id='transcript-view-transcription' href='$transcribeLink'>$linkText</a>";
  			}
  	  }
    } else {
 			$db = get_db();
      $viewLink = "";
			$tElementId = $db->query("SELECT id FROM `$db->Elements` WHERE name ='Transcription' AND description = 'A TEI tagged representation of the document.'")->fetchObject()->id;
      if ($params['controller'] == 'files' && $params['action'] == 'show' || $params['controller'] == 'eman' && $params['action'] == 'files-show') {
        $hasTranscription = $db->query("SELECT 1 yes FROM `$db->ElementTexts` WHERE record_id = ? AND record_type = 'File' AND element_id = ?", [$params['id'], $tElementId])->fetchObject();
        if (isset($hasTranscription->yes)) {
  				$viewLink = WEB_ROOT . "/transcript/browse?fileid=" . $params['id'];
    			$linkText = 'Voir la transcription de ce fichier';
        }
			} elseif ($params['controller'] == 'eman' && $params['action'] == 'items-show') {
  			$fileId = $db->query("SELECT id FROM `$db->Files` f WHERE item_id = ? ORDER BY f.order ASC, id ASC LIMIT 1", $params['id'])->fetchObject()->id;
        $hasTranscription = $db->query("SELECT 1 yes FROM `$db->ElementTexts` e LEFT JOIN `$db->Files` f ON f.id = e.record_id LEFT JOIN `$db->Items` i ON f.item_id = i.id WHERE e.record_type = 'File' AND i.id = ? AND e.element_id = ?", [$params['id'], $tElementId])->fetchObject();
        if (isset($hasTranscription->yes)) {
    			$viewLink = WEB_ROOT . "/transcript/browse?fileid=" . $fileId;
    			$linkText = 'Voir la transcription de cet item';
    		}
  		}
			if (isset($linkText) && ! isset($params['error_handler'])) {
				print "<a class='eman-edit-link' style='margin-top:-55px;' href='$viewLink'>$linkText</a>";
			}
    }
  	return true;
  }


  function hookDefineRoutes($args)
  {
		$router = $args['router'];
		// Add admin pages.
		$router->addRoute(
				'transcript_admin_list',
				new Zend_Controller_Router_Route(
						'transcript/list',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'list',
						)
				)
		);
		$router->addRoute(
				'transcript_admin_stats',
				new Zend_Controller_Router_Route(
						'transcript/stats',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'stats',
						)
				)
		);
		$router->addRoute(
				'transcript_admin_options',
				new Zend_Controller_Router_Route(
						'transcript/options',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'options',
						)
				)
		);
		$router->addRoute(
				'transcript_validate',
				new Zend_Controller_Router_Route(
						'transcript/validate',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'validate',
								'xml'			=> ''
						)
				)
		);
		$router->addRoute(
				'transcript_browser',
				new Zend_Controller_Router_Route(
						'transcript/browse',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'browse',
						)
				)
		);
		$router->addRoute(
				'transcript_fetch_files_for_item',
				new Zend_Controller_Router_Route(
						'transcript/fetchfiles',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'ajax-fetch-files',
						)
				)
		);
		$router->addRoute(
				'transcript_fetch_transcription',
				new Zend_Controller_Router_Route(
						'transcript/fetchtranscription',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'ajax-fetch-transcription',
						)
				)
		);
		$router->addRoute(
				'transcript_fetch_rendition',
				new Zend_Controller_Router_Route(
						'transcript/fetchrendition',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'ajax-fetch-rendition',
						)
				)
		);
		$router->addRoute(
				'transcript_fetch_files_gallery',
				new Zend_Controller_Router_Route(
						'transcript/fetchfilesgallery',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'ajax-fetch-files-gallery',
						)
				)
		);
		$router->addRoute(
				'transcript_fileid_fromitemid',
				new Zend_Controller_Router_Route(
						'transcript/fetchitemid',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'ajax-item-id-from-file-id',
						)
				)
		);
		$router->addRoute(
				'transcript_fetch_file_picture',
				new Zend_Controller_Router_Route(
						'transcript/fetchfilepicture',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'ajax-fetch-file-picture',
						)
				)
		);
		$router->addRoute(
				'transcript_regroup',
				new Zend_Controller_Router_Route(
						'transcript/regroup',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'regroup',
						)
				)
		);
		$router->addRoute(
				'transcript_suppress',
				new Zend_Controller_Router_Route(
						'transcript/suppress',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'suppress',
						)
				)
		);
		$router->addRoute(
				'transcript_import',
				new Zend_Controller_Router_Route(
						'transcript/importtranscription',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'import-transcription',
						)
				)
		);
		$router->addRoute(
				'transcript_do_import',
				new Zend_Controller_Router_Route(
						'transcript/do-import',
						array(
								'module' => 'transcript',
								'controller'   => 'browser',
								'action'       => 'do-import',
						)
				)
		);
		$router->addRoute(
				'transcript_show_term',
				new Zend_Controller_Router_Route(
						'transcript/show/:term',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'show-term',
								'term'       => '',
						)
				)
		);
		$router->addRoute(
				'transcript_list_terms',
				new Zend_Controller_Router_Route(
						'transcript/glossaire',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'glossaire',
						)
				)
		);
		$router->addRoute(
				'transcript_refresh_index',
				new Zend_Controller_Router_Route(
						'transcript/index',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'index',
						)
				)
		);
    // Add import/export pages.
		$router->addRoute(
				'transcript_export_tei',
				new Zend_Controller_Router_Route(
						'transcript/exporttei/:fileid',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'exporttei',
								'fileid'			=> '',
            )
				)
		);
		$router->addRoute(
				'transcript_admin_export',
				new Zend_Controller_Router_Route(
						'transcript/export',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'export',
						)
				)
		);
		$router->addRoute(
				'transcript_admin_import',
				new Zend_Controller_Router_Route(
						'transcript/import',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'import',
						)
				)
		);
  }

  /**
   * Add the pages to the public main navigation options.
   *
   * @param array Navigation array.
   * @return array Filtered navigation array.
   */
  public function filterAdminNavigationMain($nav)
  {
  	$nav[] = array(
  			'label' => __('Transcript'),
  			'uri' => url('transcript/list'),
  			'resource' => 'Transcript_Page',
  	);
  	return $nav;
  }

  function hookDefineAcl($args)
  {
  	$acl = $args['acl'];
  	$TranscriptAdmin = new Zend_Acl_Resource('Transcript_Page');
  	$acl->add($TranscriptAdmin);
    $acl->allow(array('super', 'admin'), 'Transcript_Page', 'admin');

    if (plugin_is_active("More User Roles")) {
      $users = array('author', 'editor', 'researcher');
    } else {
      $users = array('researcher');
    }
    $acl->deny($users, 'Transcript_Page');
    $acl->allow(null, 'Transcript_Page', null);

  }

  /**
   * Install Transcript.
   */
  public function hookInstall()
  {
  	// Don't install if an element set by the name "Transcript" already exists.
  	if (! $this->_db->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME)) {

    	$elementSetMetadata = array('name' => self::ELEMENT_SET_NAME);
    	$elements = array(
    			array('name' => 'Transcription',
    					'description' => 'A TEI tagged representation of the document.')
    	);
    	insert_element_set($elementSetMetadata, $elements);

/*
  		throw new Omeka_Plugin_Installer_Exception(
  				__('An element set by the name "%s" already exists. You must delete '
  						. 'that element set to install this plugin.', self::ELEMENT_SET_NAME)
  		);
*/
  	}

  	// Create table for admin
  	$db = $this->_db;
  	$sql = "CREATE TABLE IF NOT EXISTS `$db->Transcript` (
    	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    	`active` int(10) unsigned NOT NULL DEFAULT 0,
    	`name` varchar(1000) NOT NULL,
    	`properties` varchar(1000) NOT NULL,
      PRIMARY KEY (`id`)
  	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
  	$db->query($sql);

  	$sql = "CREATE TABLE IF NOT EXISTS `$db->TranscriptTerms` (
    	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    	`name` varchar(1000) NOT NULL,
    	`definition` varchar(1000) NOT NULL,
    	`occurrences` varchar(5000) NULL,
    	`fieldsvalues` varchar(5000) NULL,
    	`linkedterms` varchar(500) NULL,
    	PRIMARY KEY (`id`)
  	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
  	$db->query($sql);

  	$this->_installOptions();
  }
}
