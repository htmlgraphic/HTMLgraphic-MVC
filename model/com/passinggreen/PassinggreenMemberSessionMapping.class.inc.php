<?php
Loader::load("model", "DBObject");

class PassinggreenMemberSessionMapping extends DBObject
{

	private static $MEMBER_ID = "AutoID";
	private static $SESSION_ID = "session_id";
	private static $CREATED_DATE = "date_added";

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
		return "user_signup_to_session";
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

	private $passinggreensession;

	public function getSession()
	{
		if (!isset($this->passinggreensession))
		{
			Loader::load("model", "com/passinggreen/PassinggreenSession");
			$this->passinggreensession = new PassinggreenSession($this->getDBValue(self::$SESSION_ID));
		}
		return $this->passinggreensession;
	}

	public function setSession($session = null)
	{
		$this->setDBValue(self::$SESSION_ID, 0);
	}

	public function getCreationDate($format = "Y-m-d H:i:s")
	{
		return date($format, strtotime($this->getDBValue(self::$CREATED_DATE)));
	}

	public function setCreationDate($signup_date)
	{
		$this->setDBValue(self::$CREATED_DATE, $signup_date);
	}

	public static function memberFilter(Member $member)
	{
		return array("column" => self::$MEMBER_ID, "value" => $member->getID());
	}

	public static function sessionFilter(FormulaSession $formulasession)
	{
		return array("column" => self::$SESSION_ID, "value" => $formulasession->getID());
	}

	public static function creationDateFilter($signup_date)
	{
		return array("column" => self::$CREATED_DATE, "value" => $signup_date);
	}

	public static function memberSort()
	{
		return self::$MEMBER_ID;
	}

	public static function sessionSort()
	{
		return self::$SESSION_ID;
	}

	public static function creationDateSort()
	{
		return self::$CREATED_DATE;
	}

}
?>