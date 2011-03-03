<?php
//handling of all errors.  Do not call directly.. 
//use PHP's trigger_error()
//email and log error:  trigger_error("error text",  E_USER_ERROR );
//log error only: 		trigger_error("error text" );

class ErrorHandler
{
	//http://www.php.net/manual/en/errorfunc.constants.php
	private $alert_emails = array("tom@htmlgraphic.com");
	private $exception;
	private $memCacheKey;

	public function __construct(ErrorException $exception)
	{
		$this->exception = $exception;
	}

	public function ProcessError()
	{
		//error_log(" checking error: " . $this->exception->getMessage()); 
		switch ($this->exception->getSeverity())
		{
			case E_USER_ERROR: //called from: trigger_error("error text",  E_USER_ERROR );
				$this->send_to_error_log();
				$this->email_alert();
				break;
			case E_USER_WARNING:  //called from: trigger_error("error text",  E_USER_WARNING);
				$this->send_to_error_log();
				break;
			case E_USER_NOTICE:   //called from: trigger_error("error text");
				$this->send_to_error_log();
				break;
		}

		return;
	}

	public static function shutdownCheck()
	{
		//error_log("shutdown check");
		if ($error = error_get_last()) {

			if ($error['type'] == E_COMPILE_ERROR) {
				$exception = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);

				if (strpos($exception->getMessage(), "Cannot redeclare class") !== false) {
					//send email alerts for duplicate class declarations.. error already logged by php
					$ErrorHandler = new ErrorHandler($exception);
					$ErrorHandler->email_alert();
				}
			}
		}
		return;
	}

	private function send_to_error_log()
	{
		error_log("ErrorHandler: " . $this->exception->getMessage() . " in " . $this->exception->getFile() . " on line " . $this->exception->getLine());
	}

	//for purpose of not over flooding emails on similar errors. Just send one email,  per error, every X minutes
	private function error_recently_sent()
	{
		Loader::load('utility', "memcache/MemCache");
		if (!EAMemCache::isAvailable()) {
			//error_log("memcache not available for ErrorHandler. no email alert sent");	
			return true;
		}

		$result = EAMemCache::get($this->getMemCacheKey(), time());

		if (isset($result) && $result > strtotime("-10 minutes")) {
			//error_log("error has been emailed in the last X minutes. do not sent email alert. $memCacheKey - $result");
			return true;
		}
		//error_log("update/set current error. $memCacheKey - $result");
		EAMemCache::set($this->getMemCacheKey(), time());
		return false;
	}

	private function getMemCacheKey()
	{
		if (!$this->memCacheKey)
			return $this->memCachKey = "errorhandler(" . $this->exception->getFile() . "," . $this->exception->getLine() . ") Total: 2";

		return $this->memCachekey;
	}

	private function email_alert()
	{
		if ($this->error_recently_sent()) {
			//return;	//error already emailed recently
		}

		if (!Config::isLive()) {
			error_log("Email alert would have been sent for this error.");

			global $argv;
			if (isset($argv[1])) {
				$this->alert_emails = array($argv[1]);
			}
			else {
				return; //do not send emails for testing
			}
		}

		$error_level = $this->exception->getSeverity();
		$subject = "PHP Error[" . $error_level . "] : " . $this->exception->getFile();

		$body = "";
		$body .= "\n Error sent from /utility/ErrorHandler.class";
		$body .= "\n PHP Error #: " . $error_level;
		$body .= "\n Error Message: " . $this->exception->getMessage();
		$body .= "\n File: " . $this->exception->getFile();
		$body .= "\n Line: " . $this->exception->getLine();
		$body .= "\n DateTime: " . date("Y-m-d H:i:s");

		if (isset($_SERVER['REMOTE_ADDR'])) {
			$body .= "\n Remote Address: " . $_SERVER['REMOTE_ADDR'];
		}
		if (isset($_SERVER['SERVER_ADDR'])) {
			$body .= "\n Server Address: " . $_SERVER['SERVER_ADDR'];
		}
		if (isset($_SERVER['SERVER_NAME'])) {
			$body .= "\n Server Name: " . $_SERVER['SERVER_NAME'];
		}
		$body .= "\n Trace: " . $this->exception->getTraceAsString();

		Loader::load('utility', "email/Email");

		$email = new Email();
		$email->setBody($body);
		$email->setSubject($subject);
		$email->setFromAddress("Error Alert", "");
		$email->addToAddress("Debugging", "tom@htmlgraphic.com");
		$email->sendAsPlainText();
		$email->send();
	}
}
?>