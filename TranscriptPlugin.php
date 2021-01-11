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
    echo "<a class='big green button' href='" . WEB_ROOT . "/transcribe/" . $args['record']->id . "'>Transcrire ce fichier</a>";
  }

  function hookPublicContentTop($args)
  {
		$params = Zend_Controller_Front::getInstance()->getRequest()->getParams();

    if (isset($params['action'])) {
      if ($params['action'] == 'transcribe' || ! isset($params['id'])) : return; endif;
    }
    if (isset($params['id'])) {
      $file = get_record_by_id('file', $params['id']);
    }
    // Si le fichier n'est pas public, on ne fait rien.
    if (! isset($file)) : return; endif;

  	if ($currentUser = current_user()) {
  		$transcribeLink = "";
  		if ($params['controller'] == 'files' && $params['action'] == 'show' || $params['controller'] == 'eman' && $params['action'] == 'files-show') {
  			if (in_array($currentUser->role, array('super', 'admin', 'author', 'editor', 'researcher'))) {
  				$transcribeLink = WEB_ROOT . "/transcribe/" . $params['id'];
  				print "<a class='eman-edit-link' style='margin-top:-55px;' href='$transcribeLink'>Transcrire ce fichier</a>";
  			}
  	  }
    }

    // Lien vers la transcription
  	if ($params['controller'] == 'files' && $params['action'] == 'show' || $params['controller'] == 'eman' && $params['action'] == 'files-show') {
    	$xmlFileName =  substr($file->filename, 0, strpos($file->filename, '.')) . '.xml';
    	if (file_exists(BASE_DIR . '/teibp/transcriptions/' . $xmlFileName)) :
        print "<a class='eman-edit-link' style='margin-top:-30px;' href='" . WEB_ROOT . "/transcription/" . metadata('file', 'id') . "'>Afficher la transcription</a>";
      endif;
		}

  	return true;
  }


  function hookDefineRoutes($args)
  {
  	// Don't add these routes on the public side to avoid conflicts.
		$router = $args['router'];
		// Add transcribe page.
		$router->addRoute(
				'transcript_view',
				new Zend_Controller_Router_Route(
						'transcription/:fileid',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'view',
								'fileid'			=> ''
						)
				)
		);
		$router->addRoute(
				'transcript_transcribe_form',
				new Zend_Controller_Router_Route(
						'transcribe/:fileid',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'transcribe',
								'fileid'			=> ''
						)
				)
		);
		// Add admin pages.
		$router->addRoute(
				'transcript_admin',
				new Zend_Controller_Router_Route(
						'transcript',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'admin',
						)
				)
		);
		$router->addRoute(
				'transcript_admin_controle',
				new Zend_Controller_Router_Route(
						'transcript/controle',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'controle',
						)
				)
		);
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
		// Add exports pages.
		$router->addRoute(
				'transcript_export_tei',
				new Zend_Controller_Router_Route(
						'transcript/exporttei/:fileid',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'exporttei',
								'fileid'			=> '',  						)
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
				'transcript_admin_reset',
				new Zend_Controller_Router_Route(
						'transcript/reset',
						array(
								'module' => 'transcript',
								'controller'   => 'page',
								'action'       => 'reset',
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
//   			'resource' => 'Transcript_Page',
  	);
  	return $nav;
  }

  function hookDefineAcl($args)
  {
  	$acl = $args['acl'];
  	$TranscriptAdmin = new Zend_Acl_Resource('Transcript_Page');
  	$acl->add($TranscriptAdmin);
//     $acl->allow(array('super', 'admin'), array('Transcript_Page'));
    $acl->allow(array('super', 'admin'), 'Transcript_Page', 'admin');

    if (plugin_is_active("More User Roles")) {
      $users = array('author', 'editor', 'researcher');
    } else {
      $users = array('researcher');
    }
    $acl->deny($users, 'Transcript_Page');
    $acl->allow($users, 'Transcript_Page', array('list', 'stats', 'transcribe'));
    $acl->allow(null, 'Transcript_Page', array('view'));
  }

  /**
   * Install Transcript.
   */
  public function hookInstall()
  {
  	// Don't install if an element set by the name "Transcript" already exists.
  	if ($this->_db->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME)) {
  		throw new Omeka_Plugin_Installer_Exception(
  				__('An element set by the name "%s" already exists. You must delete '
  						. 'that element set to install this plugin.', self::ELEMENT_SET_NAME)
  		);
  	}

  	$elementSetMetadata = array('name' => self::ELEMENT_SET_NAME);
  	$elements = array(
  			array('name' => 'Transcription',
  					'description' => 'A TEI tagged representation of the document.')
  	);
  	insert_element_set($elementSetMetadata, $elements);

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

  	$this->_installOptions();
  }

}
