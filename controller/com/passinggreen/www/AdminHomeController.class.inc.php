<?php
Loader::load('controller', 'DefaultPageController');

//Loader::load('utility', 'response/MessageLogger');
//Loader::load('utility', 'lyris/Lyris');
//Loader::load('utility', 'email/Email');

class AdminHomeController extends DefaultPageController
{
	function activate()
	{
		if (Config::get('Member'))
		{
			Loader::load('model', array(
					  'com/passinggreen/member/Member',
					  'com/passinggreen/PassinggreenMemberSessionMapping'
				   ));

			$formulaMember = new PassinggreenMemberSessionMapping(Config::get('Member')->getID());

			/*if (isset($formulaMember) && $formulaMember->isValid())
				$this->redirect('/dashboard/', '302');*/
		}

		$postArgs = Request::_POST();
		Debugger::log($postArgs);

		if (count($postArgs))
		{
			if ($postArgs['list'] == 'newsletter')
			{
				$email = $postArgs['newsletter_email'];
				$name = $postArgs['newsletter_name'];
				$this->addPageData('body/newsletter_email', $email);
				$this->addPageData('body/newsletter_name', $name);
				$this->setPageData('body/form', 'newsletter');
				$list = self::$EMAIL_LIST;
			}
			else if ($postArgs['list'] == 'affiliate')
			{
				$email = $postArgs['affiliate_email'];
				$name = $postArgs['affiliate_name'];
				$this->addPageData('body/affiliate_email', $email);
				$this->addPageData('body/affiliate_name', $name);
				$this->setPageData('body/form', 'affiliate');
				$list = self::$AFFILIATE_LIST;
			}
			if (!isset($email) || $email == '' || !Email::isValidAddress($email))
				MessageLogger::logError("You must enter a valid email address.");
			else if (!isset($name) || $name == '')
				MessageLogger::logError("You must enter a name.");
			else
				$this->signupForEAFormula($email, $name, $list);
		}

		$this->addPageData('header/assets/css', array(
		    '/css/landing.css',
		    '/css/colorbox.css',
		    array(
			   'conditional' => 'lt IE 9',
			   'path' => '/css/landing.ie.css'
		    )
		));

		$this->addPageData('header/assets/js', array(
		    '/js/jquery-1.4.3.min.js',
		    '/js/colorbox.js',
		    '/js/landing.js'
		));

		$this->addPageData('header/meta', array(
		    'description' => 'Passing Green :: Administration'
		));

		$this->setBodyView('Order');

		$this->setSecondaryHeaderView('parts/LandingSecondaryHeader');
		$this->loadPage();
	}

	private static $BLERG = 'blerg';

}
?>