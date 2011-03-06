<?php
Loader::load("controller", "/controller/ModelController");

class AdminUserUpdateController extends ModelController
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

			$user = $this->loadModel('com/passinggreen/member/Member', $params['id']);

			if (isset($user) && $user->isValid())
			{

			}
			else
			{
				$return->error = "ID is invalid.";
				echo json_encode($return);
				return;
			}
		}
		else
		{
			$return->error = "Member class was called without an ID defined";
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

	public function prefill()
	{
		$return = new stdClass;
		$params = Request::getRequest();

		//Config::set("HideDebugger", true); //comment this out to debug

		if (isset($params['id']))
		{
			$user = $this->loadModel('com/passinggreen/member/Member', $params['id']);


			if (isset($user) && $user->isValid())
			{
				$return->object = $user;
			}
			else
			{
				$return->error = "ID is invalid.";
				echo json_encode($return);
				return;
			}
		}

		try
		{
			// Get prefilled data
		} catch (Exception $e)
		{
			$return->error = $e->getMessage();
		}

		$return->id = $params['id'];

		echo json_encode($return);

		return;
	}

}
?>