<?php
Loader::load('controller', 'AdminPageController');

class AdminUsersController extends AdminPageController
{
	function activate()
	{
		$this->setPageData('header/title', 'Users');
		$this->setPageView('admin/AdminPage');
		$this->setBodyView('admin/body/Users');

		$this->addPageData('header/meta', array(
		    'description' => 'Passing Green :: Administration :: Users'
		));

		$this->loadPage();
	}

}
?>