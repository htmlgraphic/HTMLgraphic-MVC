<?php
Loader::load('model', array(
		  "DBObject"
	   ));

class Member extends DBObject
{

	private static $MEMBERSHIP_LEVEL = "membership_lvl";
	private static $START_SUBSCRIBE = 'start_subscribe';
	private static $END_SUBSCRIBE = 'end_subscribe';
	private static $LAST_SUBMIT = 'last_submit';
	private static $LAST_LOGIN = 'last_login';
	private static $SUBMITS_LEFT = 'submits_left';
	private static $STATUS = 'status';
	private static $SUSPEND_MSG = "suspend_msg";
	const BASIC = "Basic";
	const BASIC_PLUS = "Basic PLUS";
	const PLATINUM = "Platinum";

	const SUSPENDED_PENDING_INVESTIGATION = "SUSPENDED PENDING INVESTIGATION";
	const SUSPENDED_FUTURE_SUBMISSIONS = "Suspended Future Submissions";
	const SUSPENDED_EMAIL_BOUNCING = "Suspended Email Bouncing";
	const SUSPENDED_INVALID_AUTHOR_NAME = "Suspended Invalid Author Name";
	const SUSPENDED_POSTHUMOUS = "Suspended Posthumous";

	const PREMIUM_STATUS = 2;

	const MAX_ALTERNATE_AUTHORS = 50;

	const MAX_DRAFTS_ALLOWED = 30;

	const BASIC_SUBMITS = 10;
	const BASIC_PLUS_SUBMITS = 25;
	const UNLIMITED_SUBMITS = "Unlimited"; //This would be PLATINUM_SUBMITS as well

	private static $stored_member;
	private static $current_member = null;

	public function __construct($mem_id = null)
	{
		if (isset($mem_id))
			$this->setDBValue("id", $mem_id);
		else
		{
			$this->setDBValue(self::$START_SUBSCRIBE, date("Y-m-d"));
		}
	}

	public function can_load()
	{
		$id = $this->getDBValue("id");

		return isset($id);
	}

	function getMemCacheKey()
	{
		return "ea_member({$this->getID()})";
	}

	static function allowMemCache()
	{
		return false;
	}

	static function primary_key()
	{
		return "id";
	}

	public static function findMemberWithEmail($email)
	{
		return self::lookup_member(null, $email);
	}

	public static function lookup_member($id=null, $email=null, $author=null)
	{
		$id = (int) $id;
		$email = trim($email);
		$author = addslashes($author);

		$sql = "SELECT `id` , `email`, `author` FROM `members`";
		if ($id || $email || $author)
		{
			$sql .= " WHERE";
		}
		else
		{
			return null;
		}


		if ($id)
		{
			$sql.= " `id` = '$id'";
			if ($email || $author)
				$sql.= " &&";
		}
		if ($email)
		{
			$sql.= " `email` = '$email'";
			if ($author)
				$sql.= " &&";
		}
		if ($author)
			$sql.= " `author` = '$author'";
		//	echo "$sql<br>";
		if ($res = DatabaseFactory::passinggreen_db()->query($sql))
		{

			if ($res->num_rows == 1)
			{
				$member = $res->fetch_object();

				return new Member($member->id);
			}
			else
				return null;
		}

		return null;
	}

	public static function get_current_member()
	{
		$id = $_REQUEST["id"];
		if (self::$current_member == null)
		{
			return self::$current_member;
		}
		self::$current_member = new Member($id);

		return self::$current_member;
	}

	public static function get_member($id)
	{
		if (self::$stored_member->id == $id)
		{

			return self::$stored_member;
		}
		self::$stored_member = new Member($id);

		return self::$stored_member;
	}

	public static function status_to_string($status)
	{

		$status = (int) $status;
		switch ($status)
		{
			case 0:
				return "Normal";
			case 2:
				return "Premium";
			default:
				return "Unknown";
		}
		return "Unknown";
	}

	protected function db()
	{
		return DatabaseFactory::ea_articles_db();
	}

	protected function master_db()
	{
		return DatabaseFactory::ea_db1_db();
	}

	protected function table()
	{
		return "members";
	}

	protected function where_clause()
	{

		return "`member_id` = '{$this->getDBValue('member_id')}'";
	}

	public function getID()
	{
		return $this->getDBValue("member_id");
	}

	public function getPasswordHash()
	{
		return $this->getDBValue("passwd_hash");
	}

	public function setPassword($password)
	{
		$passwordHash = hash('sha256', $password);
		$this->setPasswordHash($passwordHash);
	}

	public function setPasswordHash($passwordHash)
	{
		$this->setDBValue('passwd_hash', $passwordHash);
	}

	public function getFullName()
	{
		return $this->getFirstName() . " " . $this->getLastName();
	}

	public function getFirstName()
	{
		return $this->getDBValue("fname");
	}

	function setFirstName($value)
	{
		$this->setDBValue("fname", $value);
	}

	public function getLastName()
	{
		return $this->getDBValue("lname");
	}

	function setLastName($value)
	{
		$this->setDBValue("lname", $value);
	}

	function getAddress1()
	{
		return $this->getDBValue("address1");
	}

	function setAddress1($value)
	{
		$this->setDBValue("address1", $value);
	}

	function getAddress2()
	{
		return $this->getDBValue("address2");
	}

	function setAddress2($value)
	{
		$this->setDBValue("address2", $value);
	}

	function getCity()
	{
		return $this->getDBValue("city");
	}

	function setCity($value)
	{
		$this->setDBValue("city", $value);
	}

	function getState()
	{
		return $this->getDBValue("state");
	}

	function setState($value)
	{
		$this->setDBValue("state", $value);
	}

	function getZipCode()
	{
		return $this->getDBValue("zip");
	}

	function setZipCode($value)
	{
		$this->setDBValue("zip", $value);
	}

	function getPhoneNumber()
	{
		return $this->getDBValue("phone");
	}

	function setPhoneNumber($value)
	{
		$this->setDBValue("phone", $value);
	}

	function getFaxNumber()
	{
		return $this->getDBValue("fax");
	}

	function setFaxNumber($value)
	{
		$this->setDBValue("fax", $value);
	}

	function getBusinessName()
	{
		return $this->getDBValue("business_name");
	}

	function setBusinessName($value)
	{
		$this->setDBValue("business_name", $value);
	}

	function getURL()
	{
		return $this->getDBValue("url");
	}

	function setURL($value)
	{
		$this->setDBValue("url", $value);
	}

	function getIMName()
	{
		return $this->getDBValue("im_name");
	}

	function setIMName($value)
	{
		$this->setDBValue("im_name", $value);
	}

	function getIMClient()
	{
		return $this->getDBValue("im");
	}

	function setIMClient($value)
	{
		$this->setDBValue("im", $value);
	}

	function getIP()
	{
		return $this->getDBValue("ip");
	}

	function setIP($ip)
	{
		$this->setDBValue("ip", $ip);
	}

	function getRSS()
	{
		return $this->getDBValue("rss");
	}

	function setRSS($rss)
	{
		$this->setDBValue("rss", $rss);
	}

	private $profile;

	public function getProfile()
	{
		if ($this->profile)
			return $this->profile;

		Loader::load("model", "com/passinggreen/member/MemberProfile");
		$this->profile = new MemberProfile($this);
		return $this->profile;
	}

	public function getEmail()
	{
		return $this->getDBValue("email");
	}

	function setEmail($value)
	{
		$this->setDBValue("email", $value);
	}

	public function setStatus($status)
	{
		$this->setDBValue(self::$STATUS, $status);
	}

	public function getStatus()
	{
		return $this->getDBValue(self::$STATUS);
	}

	function getCountry()
	{
		return $this->getDBValue("country");
	}

	function setCountry($value)
	{
		$this->setDBValue("country", $value);
	}

	function validatePassword($password)
	{
		$query = "SELECT `id` FROM `members` WHERE `passwd_hash` = '" . hash('sha256', $password) . "' && `id` = '{$this->getID()}'";
		$result = DatabaseFactory::ea_db1_db()->query($query);

		if ($result && $result->num_rows)
			return true;

		return false;
	}

	//this could check for to make sure the id isn't blank but should be fine for now.
	function isEmployee()
	{
		return $this->getEmployeeID();
	}

	function getEmployeeID()
	{
		Loader::load('model', 'com/htmlgraphic/employee/Employee');
		$employee = Employee::lookupEmployeeWithMember($this);

		if (isset($employee) && $employee->is_valid())
			return $employee->getID();
		else
			return false;
	}

	function __tostring()
	{
		return "Member: {$this->getID()}";
	}

	public function getLastLogin($format = "Y-m-d H:i:s")
	{
		$login = $this->getDBValue("last_login");
		if ($login == "0000-00-00 00:00:00")
			return;
		return date($format, strtotime($this->getDBValue("last_login")));
	}

	public function recordLogin()
	{
		if (!isset($_REQUEST["admin"]))
		{
			$date = date('Y-m-d H:i:s');
			$query = "UPDATE members SET last_login='$date' WHERE id='{$this->getID()}'";
			$submit = $this->db()->execute($query);
		}
	}

	public function updateLastLogin()
	{
		if (!isset($_REQUEST["admin"]))
		{
			$date = date('Y-m-d H:i:s');
			$this->setDBValue('last_login', $date);
		}
	}

	static function maxMemberID()
	{
		$sql = "SELECT MAX(id) as max_id FROM {$this->table()}";
		if ($result = $this->db()->query($sql))
		{
			$info = $result->fetch_object();
			return $info->max_id;
		}

		return false;
	}

	private static $table_settings = array(
	    "gedit",
	    "cmnt_email",
	    "rank_view",
	    "msg_pref",
	    "msg_notify",
	    "msg_lock",
	    "sig_notify",
	    "handicap",
	    "auto_save",
	    "article_sub",
	    "article_app",
	    "article_prob",
	    "author_photo",
	    "author_bio");
	private $external_settings = array();

	/* public function getPreference($type)
	  {
	  if (in_array($type, self::$table_settings))
	  {
	  return $this->getDBValue($type);
	  }
	  else
	  {
	  if (!isset($external_settings[$type]))
	  {
	  Loader::load('model', 'com/passinggreen/member/settings/MemberSettings');
	  $this->external_settings[$type] = new MemberSettings($this, $type);
	  }
	  return $this->external_settings[$type]->getSetting();
	  }
	  }

	  public function setPreference($type, $value, $delay_save = false)
	  {
	  if (in_array($type, self::$table_settings))
	  {
	  return $this->setDBValue($type, $value);
	  }
	  else
	  {
	  if (!isset($external_settings[$type]))
	  {
	  Loader::load('model', 'com/passinggreen/member/settings/MemberSettings');
	  $this->external_settings[$type] = new MemberSettings($this, $type);
	  }
	  $this->external_settings[$type]->setSetting($value);
	  if (!$delay_save)
	  $this->external_settings[$type]->save();
	  }
	  } */
	//Overrides DBObject, for Member.AccountInformation.Update
	public function change_as_history($key, $new, $prev)
	{
		switch ($key)
		{
			case 'im':
				$key = "IM Client";
				break;
			case 'im_name':
				$key = "IM Name";
				break;
			case 'fname':
				$key = "First Name";
				break;
			case 'lname':
				$key = "Last Name";
				break;
			case 'address1':
				$key = "Address 1";
				break;
			case 'address2':
				$key = "Address 2";
				break;
			case 'url':
				$key = "URL";
				break;
			default:
				$key = str_replace("_", " ", $key);
				$key = ucwords($key);
		}//end switch
		if ($new == "")
		{
			$new = "empty";
		}
		if ($prev == "")
		{
			$prev = "empty";
		}
		$change = "$key: $new (was $prev)\n";

		return $change;
	}

	public static function emailFilter($email)
	{
		return array("column" => "email", "value" => $email);
	}

	public static function countryFilter(Country $country)
	{
		return array("column" => "country", "value" => $country->getCountry());
	}

	public static function adminFilter()
	{
		return array("column" => self::$MEMBERSHIP_LEVEL, "value" => self::BASIC);
	}

	public static function neverLoggedInFilter()
	{
		return array('column' => self::$LAST_LOGIN, 'value' => "0000-00-00 00:00:00");
	}

	public static function lastLoginAfterFilter($date)
	{
		return array(
		    'column' => self::$LAST_LOGIN,
		    'value' => date('Y-m-d', strtotime($date)),
		    'comparison' => '>='
		);
	}

	public static function lastLoginBeforeFilter($date)
	{
		return array(
		    'column' => self::$LAST_LOGIN,
		    'value' => date('Y-m-d', strtotime($date)),
		    'comparison' => '<='
		);
	}

}
?>