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

		//Config::set("HideDebugger", true); //comment this out to debug

		if (isset($params['id']))
		{
			if (Config::get("Member")->getID() == $params['id'])
			{
				$return->error = "Member ID is the same as user who is logged on. Will not delete.";
				echo json_encode($return);
				return;
			}

			$member = $this->loadModel('com/passinggreen/member/Member', $params['id']);

			if (isset($member) && $member->isValid())
			{
				Debugger::log($member->getFirstName());
				$member->setFirstName("FirstName" . mt_rand(4000, 4500));
				Debugger::log($member->getFirstName());
				$member->save();
			}
			else
			{
				$return->error = "Member ID is invalid.";
				echo json_encode($return);
				return;
			}
		}
		else
		{
			$return->error = "Member class was called without an Member ID defined";
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

		$return->id = $params['id'];
		$return->deleted = true;

		echo json_encode($return);

		return;
	}

}
?>