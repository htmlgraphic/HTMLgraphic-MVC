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


		$members = DBObject::collection('Member', DBObject::CONSISTENCY_ABSOLUTE);
		$members->applySort("AutoID");

		//Debugger::log(Var_Dump::display($members->getMembers(), true));
		
		$this->addPageData('body/members', $members->getMembers());

		$this->loadPage();
	}

}
?>