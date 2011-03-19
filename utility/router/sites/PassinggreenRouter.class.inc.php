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
                '/admin-new/' => 'AdminDashboardController',
                '/admin-new/dashboard' => 'AdminDashboardController',
                '/admin-new/users' => 'AdminUsersController',
                '/admin-new/settings/global' => 'AdminSettingsGlobalController',
                '/admin-new/login' => 'AdminLoginController',
                '/admin-new/rpc/*' => 'AdminRpcController',
            ));

    URL::alias('/admin-new/', '/admin-new/index.php');
    URL::alias('/admin-new/dashboard', '/admin-new/dashboard/');
    URL::alias('/admin-new/users', '/admin-new/users/');
    URL::alias('/admin-new/settings/global', array('/admin-new/settings/global/', '/admin-new/settings/', '/admin-new/settings'));

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