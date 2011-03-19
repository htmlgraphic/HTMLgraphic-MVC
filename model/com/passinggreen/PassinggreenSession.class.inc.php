<?php

Loader::load("model", "DBObject");

class PassinggreenSession extends DBObject
{

  private static $SESSION_ID = "session_id";
  private static $OPEN_DATE = "open_date";
  private static $CLOSE_DATE = "close_date";

  protected function db_name()
  {
    return "passinggreen_com";
  }

  protected function db()
  {
    return DatabaseFactory::passinggreen_db();
  }

  protected function table()
  {
    return "session";
  }

  public static function dummyMode()
  {
    return false;
  }

  public function where_clause()
  {
    return "`" . self::primary_key() . "` = '{$this->getID()}'";
  }

  public static function primary_key()
  {
    return self::$SESSION_ID;
  }

  public function getOpenDate($format = "Y-m-d H:i:s")
  {
    return date($format, strtotime($this->getDBValue(self::$OPEN_DATE)));
  }

  public function setOpenDate($open_date)
  {
    $this->setDBValue(self::$OPEN_DATE, $open_date);
  }

  public function getCloseDate($format = "Y-m-d H:i:s")
  {
    return date($format, strtotime($this->getDBValue(self::$CLOSE_DATE)));
  }

  public function setCloseDate($close_date)
  {
    $this->setDBValue(self::$CLOSE_DATE, $close_date);
  }

  public static function opendateFilter($open_date)
  {
    return array("column" => self::$OPEN_DATE, "value" => $open_date);
  }

  public static function closedateFilter($close_date, $comparison = null)
  {
    if ($comparison)
    {
      return array("column" => self::$CLOSE_DATE, "value" => $close_date, "comparison" => $comparison);
    }

    return array("column" => self::$CLOSE_DATE, "value" => $close_date);
  }

  public static function opendateSort()
  {
    return self::$OPEN_DATE;
  }

  public static function closedateSort()
  {
    return self::$CLOSE_DATE;
  }

}

?>