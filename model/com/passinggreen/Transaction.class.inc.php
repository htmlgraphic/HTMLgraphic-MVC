<?php

Loader::load("model", array(
            "DBObject"
        ));

class Transaction extends DBObject
{

  public function __construct($mem_id = null)
  {
    if (isset($mem_id))
    {
      $this->setDBValue("AutoID", $mem_id);
    }
    else
    {
      $this->setDBValue("dateAdded", date("Y-m-d H:i:s"));
    }
  }

  public function can_load()
  {
    $id = $this->getDBValue("AutoID");

    return isset($id);
  }

  static function primary_key()
  {
    return "AutoID";
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
    return "transactions";
  }

  protected function where_clause()
  {
    return "`AutoID` = '{$this->getDBValue('AutoID')}'";
  }

  public function getID()
  {
    return $this->getDBValue($this->primary_key());
  }

  public function __toString()
  {
    return "Transaction: {$this->getID()}";
  }

  public function getUserID()
  {
    return $this->getDBValue("UserID");
  }

  public function setUserID($value)
  {
    $this->setDBValue("UserID", $value);
  }

  public function getReferralID()
  {
    return $this->getDBValue("ReferralID");
  }

  public function setReferralID($value)
  {
    $this->setDBValue("ReferralID", $value);
  }

  public function getAmount()
  {
    return $this->getDBValue("amount");
  }

  public function setAmount($value)
  {
    $this->setDBValue("amount", $value);
  }

  public function getDateAdded($format = "Y-m-d H:i:s")
  {
    $dateAdded = $this->getDBValue("dateAdded");

    if ($dateAdded == "0000-00-00 00:00:00" || !$dateAdded)
    {
      return;
    }

    return date($format, strtotime($this->getDBValue("dateAdded")));
  }

  public function getDateUpdated($format = "Y-m-d H:i:s")
  {
    $dateUpdated = $this->getDBValue("dateUpdated");

    if ($dateUpdated == "0000-00-00 00:00:00" || !$dateUpdated)
    {
      return;
    }

    return date($format, strtotime($this->getDBValue("dateUpdated")));
  }

  public function setDateUpdated($value = null)
  {
    if (!is_null($value) && strtotime($value) !== false)
    {
      $date = date('Y-m-d H:i:s', strtotime($value));
    }
    else
    {
      $date = date('Y-m-d H:i:s');
    }

    $this->setDBValue("dateUpdated", $date);
  }

  public function getProcessLog()
  {
    return $this->getDBValue("processLog");
  }

  public function setProcessLog($value)
  {
    $this->setDBValue("processLog", $value);
  }

  public static function userIDFilter($value)
  {
    return array("column" => "UserID", "value" => $value);
  }

  public static function referralIDFilter($value)
  {
    return array("column" => "ReferralID", "value" => $value);
  }

}

?>