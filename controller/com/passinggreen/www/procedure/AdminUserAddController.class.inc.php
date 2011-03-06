<?php
Loader::load("controller", "/controller/ModelController");

class AdminUserAddController extends ModelController
{
	function __construct()
	{

	}

	function execute()
	{
		$return = new stdClass;
		$params = Request::getRequest();

		Config::set("HideDebugger", true); //comment this out to debug

		/* if (Config::get("Member")->getID() == $params['id'])
		  {
		  $return->error = "Member ID is the same as user who is logged on. Will not delete.";
		  echo json_encode($return);
		  return;
		  } */

		$member = $this->loadModel('com/passinggreen/member/Member');

		$member->setEmail($params['useremail']);
		$member->setLevel($params['level']);
		$member->setIsEnabled($params['is_enabled']);
		$member->setFirstname($params['userFirstname']);
		$member->setLastname($params['userLastname']);

		if (!$member->save())
		{
			$return->error = "Could not create Member object.";
			echo json_encode($return);
			return;
		}

		try
		{
			// do delete
		} catch (Exception $e)
		{
			$return->error = $e->getMessage();
		}

		$return->id = $member->getID();
		$return->created = true;

		echo json_encode($return);

		return;
	}

}
?>