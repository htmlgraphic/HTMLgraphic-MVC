<?php
Loader::load("controller", "/controller/Controller");
Loader::load("controller", "/controller/ControllerResponse");

abstract class ModelController extends Controller
{

	private static $shared_response;

	public function sharedResponse()
	{
		if (!isset(self::$shared_response))
		{
			self::$shared_response = new ControllerResponse();
		}
		return self::$shared_response;
	}

	/*
	  final protected function getDataForController(ModelController $controller)
	  {
	  return $this->sharedResponse()->getData(get_class($controller));
	  }
	  final public function getData($key=null)
	  {
	  $data = $this->getDataForController($this);
	  if(isset($key))
	  {
	  if(isset($data[$key]))
	  return $data[$key];
	  }
	  else
	  return $data;
	  }
	 */
	final function setData($data)
	{
		$this->sharedResponse()->setData($data);
	}

	final function addMessage($message, $type = "msg")
	{
		$this->sharedResponse()->addUserMessage($message, $type);
	}

	final function addErrorMessage($error)
	{
		$this->sharedResponse()->addUserMessage($error, "err_msg");
	}

	final function activate()
	{
		$response = self::sharedResponse();
		$response->beginTransaction(get_class($this));

		if ($this->execute() === false)
			$response->abortTransaction(get_class($this), $this->failed_msg);
		else
			return $response->endTransaction(get_class($this));
	}

	abstract protected function execute();

	private $failed_msg;
	private $failed = false;

	final protected function setFailedMessage($msg)
	{
		$this->failed = true;
		if (isset($msg))
			$this->failed_msg = $msg;
	}

	function getObjectsForDelete()
	{
		return null;
	}

	function getTransactionsForDelete()
	{
		return null;
	}

	function getMessagesForDelete()
	{
		return null;
	}

	function getEmailsForDelete()
	{
		return null;
	}

	function getTransactionsForChanges($insert = false)
	{
		return null;
	}

	function getMessagesForChanges($insert = false)
	{
		return null;
	}

	function getEmailsForChanges($insert = false)
	{
		return null;
	}

	function getObjectsForUpdates()
	{
		return null;
	}

	function getObjectsForInserts()
	{
		return null;
	}

}
?>