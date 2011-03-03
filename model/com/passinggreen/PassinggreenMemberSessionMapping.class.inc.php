<?php
Loader::load("model", "DBObject");

class PassinggreenMemberSessionMapping extends DBObject
{

	private static $MEMBER_ID = "member_id";
	private static $SESSION_ID = "session_id";
	private static $SIGNUP_DATE = "signup_date";
	private static $COMPLIMENTARY = "complimentary";
	public static $COMPLIMENTARY_STATUS = 1;
	public static $NOT_COMPLIMENTARY_STATUS = 0;

	protected function db_name()
	{
		return "passinggreen_com";
	}

	protected function db()
	{
		return DatabaseFactory::passinggreen_db();
	}

	protected function master_db()
	{
		return DatabaseFactory::passinggreen_master_db();
	}

	static function dummyMode()
	{
		return false;
	}

	protected function table()
	{
		return "member_to_session";
	}

	public function where_clause()
	{
		return "`" . self::primary_key() . "` = '{$this->getID()}'";
	}

	public static function primary_key()
	{
		return self::$MEMBER_ID;
	}

	private $member;

	public function getMember()
	{
		if (!isset($this->member))
		{
			Loader::load("model", "com/passinggreen/member/Member");
			$this->member = new Member($this->getDBValue(self::$MEMBER_ID));
		}
		return $this->member;
	}

	public function setMember(Member $member)
	{
		$this->member = $member;
		$this->setDBValue(self::$MEMBER_ID, $member->getID());
	}

	private $formulasession;

	public function getSession()
	{
		if (!isset($this->formulasession))
		{
			Loader::load("model", "com/passinggreen/PassinggreenSession");
			$this->formulasession = new FormulaSession($this->getDBValue(self::$SESSION_ID));
		}
		return $this->formulasession;
	}

	public function setSession($session = null)
	{
		$this->setDBValue(self::$SESSION_ID, 0);
	}

	public function getSignupDate($format = "Y-m-d H:i:s")
	{
		return date($format, strtotime($this->getDBValue(self::$SIGNUP_DATE)));
	}

	public function setSignupDate($signup_date)
	{
		$this->setDBValue(self::$SIGNUP_DATE, $signup_date);
	}

	public function getComplimentary()
	{
		return $this->getDBValue(self::$COMPLIMENTARY);
	}

	public function setComplimentary($complimentary)
	{
		$this->setDBValue(self::$COMPLIMENTARY, $complimentary);
	}

	public static function memberFilter(Member $member)
	{
		return array("column" => self::$MEMBER_ID, "value" => $member->getID());
	}

	public static function sessionFilter(FormulaSession $formulasession)
	{
		return array("column" => self::$SESSION_ID, "value" => $formulasession->getID());
	}

	public static function signupdateFilter($signup_date)
	{
		return array("column" => self::$SIGNUP_DATE, "value" => $signup_date);
	}

	public static function complimentaryFilter($complimentary)
	{
		return array("column" => self::$COMPLIMENTARY, "value" => $complimentary);
	}

	public static function memberSort()
	{
		return self::$MEMBER_ID;
	}

	public static function sessionSort()
	{
		return self::$SESSION_ID;
	}

	public static function signupdateSort()
	{
		return self::$SIGNUP_DATE;
	}

}
?>