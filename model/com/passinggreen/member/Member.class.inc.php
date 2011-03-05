<?php
Loader::load('model', array(
		  "DBObject"
	   ));

class Member extends DBObject
{

	private static $stored_member;
	private static $current_member = null;

	const BASIC_USER = "member";
	const ADMIN_USER = "admin";
	const GOD_USER = "superadmin";
	public function __construct($mem_id = null)
	{
		if (isset($mem_id))
		{
			$this->setDBValue("AutoID", $mem_id);
		}
		else
		{
			$this->setDBValue("date_added", date("Y-m-d H:i:s"));
		}
	}

	public function can_load()
	{
		$id = $this->getDBValue("AutoID");

		return isset($id);
	}

	function getMemCacheKey()
	{
		return "hg_member({$this->getID()})";
	}

	static function allowMemCache()
	{
		return false;
	}

	static function primary_key()
	{
		return "AutoID";
	}

	public static function findMemberWithEmail($email)
	{
		return self::lookup_member(null, $email);
	}

	public static function lookup_member($id=null, $email=null)
	{
		$id = (int) $id;
		$email = trim($email);

		$sql = "SELECT `AutoID` , `useremail`, `userFirstname`, `userLastname` FROM `user_signup`";

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
			$sql.= " `AutoID` = '$id'";
			if ($email || $author)
				$sql.= " &&";
		}

		if ($email)
		{
			$sql.= " `useremail` = '$email'";
		}

		if ($res = DatabaseFactory::passinggreen_master_db()->query($sql))
		{

			if ($res->num_rows == 1)
			{
				$member = $res->fetch_object();

				return new Member($member->AutoID);
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
		return DatabaseFactory::passinggreen_db();
	}

	protected function master_db()
	{
		return DatabaseFactory::passinggreen_master_db();
	}

	protected function table()
	{
		return "user_signup";
	}

	protected function where_clause()
	{
		return "`AutoID` = '{$this->getDBValue('AutoID')}'";
	}

	public function getID()
	{
		return $this->getDBValue($this->primary_key());
	}

	public function getPasswordHash()
	{
		return $this->getDBValue("passwd");
	}

	public function setPassword($password)
	{
		$passwordHash = hash('sha256', $password);
		$this->setPasswordHash($passwordHash);
	}

	public function setPasswordHash($passwordHash)
	{
		$this->setDBValue('passwd', $passwordHash);
	}

	public function getFullName()
	{
		return $this->getFirstName() . " " . $this->getLastName();
	}

	public function getFirstName()
	{
		return $this->getDBValue("userFirstname");
	}

	function setFirstName($value)
	{
		$this->setDBValue("userFirstname", $value);
	}

	public function getLastName()
	{
		return $this->getDBValue("userLastname");
	}

	function setLastName($value)
	{
		$this->setDBValue("userLastname", $value);
	}

	function getAddress1()
	{
		return $this->getDBValue("userAddr1");
	}

	function setAddress1($value)
	{
		$this->setDBValue("userAddr1", $value);
	}

	function getAddress2()
	{
		return $this->getDBValue("userAddr2");
	}

	function setAddress2($value)
	{
		$this->setDBValue("userAddr2", $value);
	}

	function getCity()
	{
		return $this->getDBValue("userCity");
	}

	function setCity($value)
	{
		$this->setDBValue("userCity", $value);
	}

	function getState()
	{
		return $this->getDBValue("userState");
	}

	function setState($value)
	{
		$this->setDBValue("userState", $value);
	}

	function getZipCode()
	{
		return $this->getDBValue("userZip");
	}

	function setZipCode($value)
	{
		$this->setDBValue("userZip", $value);
	}

	function getPhoneNumber()
	{
		return $this->getDBValue("userPhone");
	}

	function setPhoneNumber($value)
	{
		$this->setDBValue("userPhone", $value);
	}

	function getBusinessName()
	{
		return $this->getDBValue("userCompany");
	}

	function setBusinessName($value)
	{
		$this->setDBValue("userCompany", $value);
	}

	function getIP()
	{
		return $this->getDBValue("last_ip");
	}

	function setIP($value)
	{
		$this->setDBValue("last_ip", $value);
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
		return $this->getDBValue("useremail");
	}

	function setEmail($value)
	{
		$this->setDBValue("useremail", $value);
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
		return $this->getDBValue("userCountry");
	}

	function setCountry($value)
	{
		$this->setDBValue("userCountry", $value);
	}

	function validatePassword($password)
	{
		$query = "SELECT `AutoID` FROM `" . $this->table() . "` WHERE `passwd` = '" . hash('sha256', $password) . "' && `AutoID` = '{$this->getID()}'";
		$result = DatabaseFactory::passinggreen_master_db()->query($query);

		if ($result && $result->num_rows)
		{
			return true;
		}

		return false;
	}

	function isEmployee()
	{
		return $this->getEmployeeID();
	}

	function getEmployeeID()
	{
		Loader::load('model', 'com/htmlgraphic/employee/Employee');
		$employee = Employee::lookupEmployeeWithMember($this);

		if (isset($employee) && $employee->is_valid())
		{
			return $employee->getID();
		}
		else
		{
			return false;
		}
	}

	function __toString()
	{
		return "Member: {$this->getID()}";
	}

	public function getLastLogin($format = "Y-m-d H:i:s")
	{
		$login = $this->getDBValue("last_login");

		if ($login == "0000-00-00 00:00:00")
		{
			return;
		}

		return date($format, strtotime($this->getDBValue("last_login")));
	}

	public function recordLogin()
	{
		$date = date('Y-m-d H:i:s');
		$query = "UPDATE " . $this->table() . " SET last_login='$date' WHERE id='{$this->getID()}'";
		$submit = $this->db()->execute($query);
	}

	public function updateLastLogin()
	{
		$date = date('Y-m-d H:i:s');
		$this->setDBValue('last_login', $date);
	}

	static function maxMemberID()
	{
		$sql = "SELECT MAX(id) as max_id FROM `user_signup`";

		if ($result = $this->db()->query($sql))
		{
			$info = $result->fetch_object();
			return $info->max_id;
		}

		return false;
	}

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
	public static function emailFilter($email)
	{
		return array("column" => "useremail", "value" => $email);
	}

	public static function countryFilter(Country $country)
	{
		return array("column" => "userCountry", "value" => $country->getCountry());
	}

	public static function adminFilter()
	{
		return array("column" => "level", "value" => self::ADMIN_USER);
	}

	public static function godFilter()
	{
		return array("column" => "level", "value" => self::GOD_USER);
	}

	public static function neverLoggedInFilter()
	{
		return array('column' => "last_login", 'value' => "0000-00-00 00:00:00");
	}

	public static function lastLoginAfterFilter($date)
	{
		return array(
		    'column' => "last_login",
		    'value' => date('Y-m-d', strtotime($date)),
		    'comparison' => '>='
		);
	}

	public static function lastLoginBeforeFilter($date)
	{
		return array(
		    'column' => "last_login",
		    'value' => date('Y-m-d', strtotime($date)),
		    'comparison' => '<='
		);
	}

}
?>