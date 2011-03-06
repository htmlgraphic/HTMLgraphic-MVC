<?php
Loader::load('controller', '/controller/PageController');

abstract class AdminPageController extends PageController
{

	protected $settings = array(
	    'header' => array(
		   'title' => 'Passing Green',
		   'meta' => array(
			  'keywords' => '',
			  'description' => ''
		   ),
		   'metaProperties' => array(),
		   /*'assets' => array(
			  'css' => array(
				 '/admin/css/default.css'
			  )
		   ),*/
		   'ga_load' => false, // Google Analytics
		   'ga_options' => array(
			  '_trackPageview'
		   ),
		   'show_banner' => false,
		   'sidebar_view' => 'parts/Sidebar',
		   'sidebar' => array()
	    ),
	    'content_header' => array(
		   'logo' => '/admin/images/logo.jpg'
	    ),
	    'breadcrumb' => array(),
	    'body' => array(),
	    'footer' => array()
	);
	protected $navigation = array(
	    array(
		   'link' => '/admin/dashboard',
		   'name' => 'Dashboard'
	    ),
	    array(
		   'link' => '/admin/users',
		   'name' => 'Users'
	    ),
	    array(
		   'link' => '/admin/settings',
		   'name' => 'Settings'
	    ),
	    array(
		   'link' => '/admin/help',
		   'name' => 'Help'
	    )
	);

	function __construct()
	{
		parent::__construct();

		$this->addPageData('header/assets/css', array(
		    '/admin-new/css/reset.css',
		    '/admin-new/css/framework.css',
		    '/admin-new/css/ui.visualized.css',
		    '/admin-new/css/jquery.cluetip.css',
		    '/admin-new/css/ui-coffee/ui.css'
			   /* array(
			     'conditional' => 'lt IE 9',
			     'path' => '/admin/css/landing.ie.css'
			     ) */
		));

		$this->addPageData('header/assets/js', array(
		    '/admin-new/js/jquery.js',
		    '/admin-new/js/jquery.ui.js',
		    '/admin-new/js/jquery.browser.js',
		    '/admin-new/js/jquery.validator.js',
		    '/admin-new/js/jquery.tablesorter.js',
		    '/admin-new/js/jquery.tablesorter.pager.js',
		    '/admin-new/js/jquery.daterangepicker.js',
		    '/admin-new/js/jquery.maskedinput.js',
		    '/admin-new/js/jquery.corners.js',
		    '/admin-new/js/jquery.hoverintent.js',
		    '/admin-new/js/jquery.visualize.js',
		    '/admin-new/js/jquery.autoupload.js',
		    '/admin-new/js/jquery.qtip.js',
		    '/admin-new/js/general.js',
		    '/admin-new/js/messagestack.js',
		    '/admin-new/js/colorbox.js',
		    '/admin-new/js/landing.js'
		));

		//$this->addPageData('nav/bgcolor', "#f66");

		$this->member = Config::get('Member');
	}

	protected function loadPage()
	{
		if (!isset($this->settings['header']['ga_options']['_setAccount']))
		{
			$this->settings['header']['ga_options']['_setAccount'] = Config::get('GoogleAnalyticsID');
		}

		//automatically prepend same text to all headers
		if (isset($this->settings['header']['title']) && $this->settings['header']['title'])
			$this->settings['header']['title'] = 'Passing Green :: ' . $this->settings['header']['title'];

		if (isset($this->settings['breadcrumb']))
			$this->settings['header']['breadcrumb'] = $this->settings['breadcrumb'];

		parent::loadPage();
	}

}