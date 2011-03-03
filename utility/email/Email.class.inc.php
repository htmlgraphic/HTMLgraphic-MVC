<?php
Loader::load('model', "DBObject");
Loader::load('utility', "database/DatabaseFactory");
Loader::load('vendor', "phpmailer/phpmailer.config.php");

class Email extends DBOBject
{

	static protected $ID_COLUMN = "email_id";
	private $send_as_html;
	public $fromName;
	public $fromEmail;
	public $body;
	public $subject;
	public $to = array();
	public $cc = array();
	public $bcc = array();

	function __construct($id=null)
	{
		if ($id !== null && $id >= 0)
			$this->setDBValue(self::$ID_COLUMN, $id);
		else
		{
			$this->loaded = true;
			$this->setDBValue(self::$ID_COLUMN, null);
		}
	}

	function can_load()
	{
		$id = $this->getDBValue(self::$ID_COLUMN);

		return isset($id);
	}

	protected function db()
	{
		return DatabaseFactory::ea_email_templates_db();
	}

	protected function table()
	{
		return "email";
	}

	protected function where_clause()
	{
		return "`" . self::$ID_COLUMN . "` = '{$this->getID()}'";
	}

	function getID()
	{
		return $this->getDBValue(self::$ID_COLUMN);
	}

	function setFromAddress($name, $email)
	{
		$this->getEmailer()->FromName = $name;
		$this->getEmailer()->From = $email;

		$this->fromName = $name;
		$this->fromEmail = $email;
	}

	function addMemberToAddress(Member $member)
	{
		$this->associateMember($member);
		$this->addToAddress($member->getFullName(), $member->getEmail());
	}

	//associates this email with a member for transaction purposes
	private $recordMember = false;

	function associateMember(Member $member)
	{
		$this->recordMember = $member;
	}

	function addToAddress($name, $email)
	{
		$this->getEmailer()->AddAddress($email, $name);
		$this->to[] = array($email, $name);
	}

	function addCCAddress($name, $email)
	{
		$this->getEmailer()->AddCC($email, $name);
		$this->cc[] = array($email, $name);
	}

	function addBCCAddress($name, $email)
	{
		$this->getEmailer()->AddBCC($email, $name);
		$this->bcc[] = array($email, $name);
	}

	function addReplyAddress($name, $email)
	{
		$this->getEmailer()->AddReplyTo($email, $name);
	}

	function addBounceAddress($email)
	{
		$this->getEmailer()->Sender = $email;
	}

	function sendAsHTML()
	{
		$this->send_as_html = true;
	}

	function sendAsPlainText()
	{
		$this->send_as_html = false;
	}

	function send($html_mode = false)
	{

		if (Config::isLive() || Config::isStaging())
		{
			$mail = $this->getEmailer();
			if ($this->send_as_html)
			{
				//many mail servers break 8bit encoding by adding newlines, QP prevents that for ASCII-only emails (like this)
				$mail->Encoding = "quoted-printable";
				$mail->isHTML(true);
			}
			$mail->Subject = $this->getSubjectWithReplacements();
			$mail->Body = $this->getBodyWithReplacements();

			if (!$mail->Send())
			{
				error_log("ERROR: mail not sent: {$mail->ErrorInfo}");
				return false;
			}
			else if ($this->recordMember)
			{
				Loader::load('model', 'com/htmlgraphic/history/Transaction');
				DBObject::mutator('Transaction', 'RecordContactEmailSentAction', array($this, $this->recordMember))->activate();
			}
			return true;
		}
		else
		{ //this is so you can see if you would have sent an email on a dev box
			if ($this->recordMember)
			{
				Loader::load('model', 'com/htmlgraphic/history/Transaction');
				DBObject::mutator('Transaction', 'RecordContactEmailSentAction', array($this, $this->recordMember))->activate();
			}
			//error_log(print_r($this->recordMember,true));
			Debugger::log("<span style='color: #fff;'>Subject:</span> " . $this->getSubjectWithReplacements() . "<br><br><span style='color: #fff;'>-----Body-----</span><br>" . $this->getBodyWithReplacements() . "<br><span style='color: #fff;'>-----Body-----</span>");
			return true;
		}
	}

	function getErrorInfo()
	{
		return $this->getEmailer()->ErrorInfo;
	}

	function isPageWorthyError()
	{
		return (!isset($this->getEmailer()->shouldPage) && $this->getEmailer()->shouldPage);
	}

	private $emailer;

	private function getEmailer()
	{
		if (!isset($this->emailer))
		{

			$mail = new PHPMailer();
			if (EA_ENABLE_SMTP)
			{
				$mail->IsSMTP();
				$mail->Host = EA_SMTP_SERVER;
				if (EA_SMTP_USER && EA_SMTP_PASSWORD)
				{
					$mail->Username = EA_SMTP_USER;
					$mail->Password = EA_SMTP_PASSWORD;
				}
			}
			$this->emailer = $mail;
		}

		return $this->emailer;
	}

	private function performReplacementsForString($string)
	{
		$variables = $this->getReplacementVariables();

		if (is_string($string) && isset($variables) && is_array($variables))
		{

			foreach ($variables as $variable => $replacement)
			{
				$variable = "%__{$variable}__%";
				$string = str_replace($variable, $replacement, $string);
			}
		}

		return $string;
	}

	private $body_did_change = false;

	public function setBody($body)
	{
		if ($body != $this->getBody())
		{
			$this->body_template = $body;
			$this->body_did_change = true;
		}
		$this->body = $body;
	}

	private $body_template;

	public function getBody()
	{
		if (!isset($this->body_template))
		{

			$body = "";
			if ($this->getID() !== null && $this->getID() != 0)
			{
				$sql = "SELECT `body` FROM `email_body` WHERE `body_id` = '{$this->getDBValue('body_id')}'";

				if ($result = $this->db()->query($sql))
				{
					if ($row = $result->fetch_object())
					{
						$body = $row->body;
					}
				}
			}
			$this->body_template = $body;
		}

		return $this->body_template;
	}

	private $subject_did_change = false;

	public function setSubject($subject)
	{
		if ($subject != $this->getSubject())
		{
			if (!isset($this->previous_subject_template))
				$this->previous_subject_template = $this->subject_template;
			$this->subject_template = $subject;
			$this->subject_did_change = true;
		}
		$this->subject = $subject;
	}

	private $previous_subject_template;
	private $subject_template;

	public function getSubject()
	{
		if (!isset($this->subject_template))
		{
			$subject = "";

			if ($this->getID() !== null && $this->getID() != 0)
			{
				$sql = "SELECT `subject` FROM `email_subject` WHERE `subject_id` = '{$this->getDBValue('subject_id')}'";
				if ($result = $this->db()->query($sql))
				{
					if ($row = $result->fetch_object())
					{
						$subject = $row->subject;
					}
				}
			}

			$this->subject_template = $subject;
		}

		return $this->subject_template;
	}

	public function addFile($path, $name = "", $encoding = "base64", $type = "application/octet-stream")
	{
		$this->getEmailer()->AddAttachment($path, $name, $encoding, $type);
	}

	public function addFileAsString($string, $filename="", $encoding="base64", $type="application/octet-stream")
	{
		$this->getEmailer()->AddStringAttachment($string, $filename, $encoding, $type);
	}

	public function getBodyWithReplacements()
	{
		return $this->performReplacementsForString($this->getBody());
	}

	public function getSubjectWithReplacements()
	{
		return $this->performReplacementsForString($this->getSubject());
	}

	public function clearReplacements()
	{
		$this->replacement_variables = array();
	}

	public function addReplacementsForObject($object)
	{

		$interfaces = class_implements(get_class($object));

		if (isset($interfaces) && is_array($interfaces) && in_array('EmailReplace', $interfaces))
		{
			$array = $object->email_substitutions();
			$this->addReplacementsFromArray($array);
		}
	}

	public function addReplacementsFromArray($array)
	{
		if (is_array($array))
		{

			$variables = $this->getReplacementVariables();
			if (is_array($variables))
				$this->replacement_variables = array_merge($array, $variables);
			else
				$this->replacement_variables = $array;
		}
	}

	private $replacement_variables;

	private function getReplacementVariables()
	{
		if (!isset($this->replacement_variables))
		{
			$array = array();

			if (isset($_SERVER['REMOTE_ADDR']))
				$array["IP"] = $_SERVER['REMOTE_ADDR'];

			if (isset($_SERVER['HTTP_USER_AGENT']))
				$array["HTTP_USER_AGENT"] = $_SERVER['HTTP_USER_AGENT'];

			if (isset($_SERVER['REMOTE_ADDR']))
				$array["REMOTE_ADDR"] = $_SERVER['REMOTE_ADDR']; //no host, we don't do reverse lookups for performance

				if (isset($_SERVER['REMOTE_HOST']))
				$array["REMOTE_HOST"] = $_SERVER['REMOTE_HOST'];

			if (isset($_SERVER['HTTP_HOST']))
				$array["HTTP_HOST"] = $_SERVER['HTTP_HOST'];

			if (isset($_SERVER['SERVER_ADDR']))
				$array["SERVER_ADDR"] = $_SERVER['SERVER_ADDR'];

			if (isset($_SERVER['SCRIPT_FILENAME']))
				$array["SCRIPT_FILENAME"] = $_SERVER['SCRIPT_FILENAME'];

			if (isset($_SERVER['HTTP_USER_AGENT']))
				$array["HTTP_USER_AGENT"] = $_SERVER['HTTP_USER_AGENT'];

			$array["DATE"] = date("Y/m/d H:i:s");

			$this->replacement_variables = $array;
		}

		return $this->replacement_variables;
	}

	private function add_new_subject()
	{
		$subjectSQL = "INSERT INTO `email_subject` (`subject`) VALUES ('" . addslashes($this->getSubject()) . "')";

		if ($this->db()->execute($subjectSQL))
		{
			$subject_id = $this->db()->last_insert_id();
		}
		else
		{

			$subjectSQL = "SELECT `subject_id` FROM `email_subject` WHERE `subject` = '{$this->getSubject()}'";
			if ($subjectResult = $this->db()->query($subjectSQL))// &&
			{
				if ($subjectRow = $subjectResult->fetch_object())
				{
					$subject_id = $subjectRow->subject_id;
				}
			}
		}

		return $subject_id;
	}

	function save()
	{
		if ($this->getID() == null || $this->getID() == 0)
		{
			$bodySQL = "INSERT INTO `email_body` (`body`) VALUES ('" . addslashes($this->getBody()) . "')";

			if ($this->db()->execute($bodySQL))
			{
				$body_id = $this->db()->last_insert_id();

				$subject_id = $this->add_new_subject();
			}

			if (isset($body_id) && isset($subject_id))
			{
				$sql = "INSERT INTO `email` (`subject_id`,`body_id`) VALUES ('$subject_id','$body_id')";
				if ($this->db()->execute($sql))
				{
					$this->setDBValue(self::$ID_COLUMN, $this->db()->last_insert_id());
					return true;
				}
			}
		}
		else
		{
			$subject_id = $this->getDBValue("subject_id");
			$body_id = $this->getDBValue("body_id");
			$email_id = $this->getDBValue("email_id");

			if ($this->body_did_change)
			{
				$sql = "UPDATE `email_body` SET `body` = '" . addslashes($this->getBody()) . "' WHERE `body_id` = '{$this->getDBValue('body_id')}'";
				if (!$this->db()->execute($sql))
					return false;
			}

			if ($this->subject_did_change)
			{
				$sql = "SELECT * FROM `email` WHERE `subject_id` = '{$this->getDBValue('subject_id')}'"; //.addslashes($this->previous_subject_template)."'";
				if ($result = $this->db()->query($sql))
				{
					$subjectUsedElseWhere = false;
					while ($subjectResult = $result->fetch_object())
					{
						if ($subjectResult->email_id != $this->getID())
						{
							$subjectedUsedElseWhere = true;
						}
					}
				}
				if ($subjectUsedElseWhere)
				{
					$id = $this->add_new_subject();
					$this->setDBValue("subject_id", $id);
				}
				else
				{
					$sql = "UPDATE `email_subject` SET `subject` = '" . addslashes($this->getSubject()) . "' WHERE `subject_id` = '{$this->getDBValue('subject_id')}'";
					if (!$this->db()->execute($sql))
						return false;
				}
			}


			$sql = $this->changes_as_sql_update();
			return $this->db()->execute($sql);
		}

		return false;
	}

	public static function isValidAddress($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
	}

}
?>