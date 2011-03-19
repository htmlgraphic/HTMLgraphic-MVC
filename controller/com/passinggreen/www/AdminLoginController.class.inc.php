<?php

Loader::load('controller', 'AdminPageController');

class AdminLoginController extends AdminPageController
{

  public function activate()
  {
    if (isset($_GET['destroy']))
    {
      $this->logout();
      $this->redirect('/admin/');
    }
    elseif (Request::_POST())
    {
      $this->login();
    }
    elseif (Config::get('Member'))
    {
      // continue to requested page
    }
    else
    {
      $this->show_form();
    }
  }

  public function useSSL()
  {
    return true;
  }

  private function login()
  {
    Loader::load('utility', 'session/Session');
    Loader::load('model', 'com/passinggreen/member/Member'
    );

    $member = Member::findMemberWithEmail(addslashes(Request::getPost('email')));
    $valid = false;

    if (isset($member) && $member->isValid() && ($member->getLevel() == 'admin' || $member->getLevel() == 'superadmin' || $member->getLevel() == 'developer'))
    {
      $valid = ($member->validatePassword(Request::getPost('password')));
    }

    if ($valid)
    {
      $member->recordLogin();
      Session::instance()->setModelDefault($member);
      Debugger::log('valid');
    }
    else
    {
      Debugger::log('invalid');
      $this->setPageData('error', 'Incorrect username or password.');
      $this->show_form();
    }
  }

  private function logout()
  {
    Session::instance()->clearModelDefaultForClassName('Member');
    Config::set('Member', null);
  }

  private function show_form()
  {
    $this->setPageData('header/title', 'Admin Login');
    $this->setPageView('admin/AdminPage');
    $this->setBodyView('admin/body/Login');

    $this->addPageData('login_form', true);

    $this->loadPage();
    exit;
  }

}

?>