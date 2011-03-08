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

		$this->addPageData('header/assets/js', array(
		    '/admin-new/js/user.js'
		));

		$user = DBObject::collection('Member', DBObject::CONSISTENCY_ABSOLUTE);
		$user->applySort("AutoID");

		$this->addPageData('body/users', $user->getMembers());

		$this->loadPage();
	}

}
?>