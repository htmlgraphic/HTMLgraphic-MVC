<?php
Loader::load("utility", "session/Session");

class PassinggreenRouter extends SiteRouter
{

	private $unprotected_urls = array(
	    'AdminLoginController',
	    '/controller/RedirectController'
	);

	function __construct()
	{
		ini_set("session.cookie_lifetime", 2592000); // 30 days
		parent::__construct();
		$docroot = Config::get('DocRoot');
		Config::set('ControllerRoot', "$docroot/controller/com/passinggreen/www");
		Config::set('ViewRoot', "$docroot/view/com/passinggreen/www");
		Config::set('GoogleAnalyticsID', '');
		Config::set('VirtualRoot', realpath("$docroot/../html/passinggreen_com/www"));
	}

	function route()
	{
		URL::add(array(
				  '/admin/' => 'AdminHomeController',
				  '/admin/home' => 'AdminHomeController',
				  '/admin/login' => 'AdminLoginController',
				  '/admin/rpc/*' => '/controller/RpcController',
			   ));

		URL::alias('/admin', '/admin/index.php');
		URL::alias('/admin/home', '/admin/home/');

		$controller_path = URL::getControllerFromUrl();

		Loader::load('model', array(
				  'com/passinggreen/member/Member'
			   ));

		if (!in_array($controller_path, $this->unprotected_urls))
		{
			ini_set("session.cookie_lifetime", 2592000); // 30 days
			$member = Session::instance()->getModelDefaultForClassName("Member");

			if (!isset($member) || !$member->isValid())
			{
				$this->show_login();
			}
			else
			{
				/* $formulaMember = DBObject::collection('PassinggreenMemberSessionMapping', DBObject::CONSISTENCY_ABSOLUTE)
				  ->applyMemberFilter($member)
				  ->getFirstFormulaMemberSessionMapping();

				  if (!isset($formulaMember) || !$formulaMember->isValid())
				  {
				  $this->show_login();
				  } */
			}
		}

		$member = Session::instance()->getModelDefaultForClassName("Member");
		Config::set('Member', $member);
		$controller = $this->loadController($controller_path);
		$controller->activate();
	}

	private function show_login()
	{
		$controller = $this->loadController('AdminLoginController');
		$controller->activate();
		$member = Session::instance()->getModelDefaultForClassName("Member");
	}

}