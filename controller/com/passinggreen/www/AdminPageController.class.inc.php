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
		   'assets' => array(
			  'css' => array(
				 '/css/module.css'
			  )
		   ),
		   'ga_load' => true,
		   'ga_options' => array(
			  '_trackPageview'
		   ),
		   'show_banner' => false,
		   'sidebar_view' => 'parts/Sidebar',
		   'sidebar' => array()
	    ),
	    'content_header' => array(
		   'logo' => '/images/logo.jpg'
	    ),
	    'breadcrumb' => array(),
	    'body' => array(),
	    'footer' => array()
	);
	protected $navigation = array(
	    array(
		   'link' => '/admin/home',
		   'name' => 'Home'
	    ),
	    array(
		   'link' => '/admin/users',
		   'name' => 'Users'
	    )
	);

	function __construct()
	{
		parent::__construct();

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

	protected function module_is_active(FormulaModule $module, $check_employee = true)
	{
		Loader::load('module', 'com/passinggreen/ModuleIsActiveModule');
		$active_module = new ModuleIsActiveModule($module, $check_employee);
		return $active_module->activate();
	}

	protected function get_module_link(FormulaModule $module)
	{
		if ($this->module_is_active($module, false))
			return 'Continue on to <a href="/' . str_replace(' ', '', $module->getName()) . '">' . $module->getName() . '</a>';
		else
			return "{$module->getName()} will be available on {$module->calculateStartDate($this->memberSession->getSignupDate('U'), 'F jS')}";
	}

}