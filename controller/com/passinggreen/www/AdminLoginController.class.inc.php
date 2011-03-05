<?php
Loader::load('controller', 'ModulePageController');

class AdminLoginController extends ModulePageController
{
	function activate()
	{
		if (isset($_GET['destroy']))
		{
			$this->logout();
			$this->redirect('/admin/');
		}
		elseif (Request::getPost())
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

	private function login()
	{
		Loader::load('utility', 'session/Session');
		Loader::load('model', array(
				  'com/passinggreen/member/Member',
				  'com/passinggreen/PassinggreenMemberSessionMapping'
			   ));

		$member = Member::findMemberWithEmail(addslashes(Request::getPost('email')));
		var_dump(Request::getPost());
		exit;

		$valid = false;

		if (isset($member) && $member->isValid())
		{
			$adminMember = new PassinggreenMemberSessionMapping($member->getID());
			$valid = ($member->validatePassword(Request::getPost('password')) && isset($adminMember) && $adminMember->isValid());
		}

		if ($valid)
		{
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
		$this->setPageView('admin/body/Login');

		$this->addPageData('header/assets/js', array(
		    '/js/jquery-1.4.3.min.js',
		    '/js/colorbox.js',
		    '/js/landing.js'
		));

		$this->loadPage();
		exit;
	}

}
?>