<?php

Loader::load("model", array(
            "DBObject"
        ));

class Referral extends DBObject
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
    return "referrals";
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
    return "Referral: {$this->getID()}";
  }

  public function getUserID()
  {
    return $this->getDBValue("UserID");
  }

  public function setUserID($value)
  {
    $this->setDBValue("UserID", $value);
  }

  public function getVendorID()
  {
    return $this->getDBValue("VendorID");
  }

  public function setVendorID($value)
  {
    $this->setDBValue("VendorID", $value);
  }

  public function getPriority()
  {
    return $this->getDBValue("priority");
  }

  public function setPriority($value)
  {
    $this->setDBValue("priority", $value);
  }

  public function getStatus()
  {
    return $this->getDBValue("status");
  }

  public function setStatus($value)
  {
    $this->setDBValue("status", $value);
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

  public function getDateCompleted($format = "Y-m-d H:i:s")
  {
    $dateCompleted = $this->getDBValue("dateAdded");

    if ($dateCompleted == "0000-00-00 00:00:00" || !$dateCompleted)
    {
      return;
    }

    return date($format, strtotime($this->getDBValue("dateCompleted")));
  }

  public function setDateCompleted($value = null)
  {
    if (!is_null($value) && strtotime($value) !== false)
    {
      $date = date('Y-m-d H:i:s', strtotime($value));
    }
    else
    {
      $date = date('Y-m-d H:i:s');
    }

    $this->setDBValue("dateCompleted", $date);
  }

  public function getSaleAmount()
  {
    return $this->getDBValue("saleAmount");
  }

  public function setSaleAmount($value)
  {
    $this->setDBValue("saleAmount", $value);
  }

  public function getReferralData()
  {
    return unserialize($this->getDBValue("referralData"));
  }

  public function setReferralData($value)
  {
    $this->setDBValue("referralData", serialize($value));
  }

  public function getNeed()
  {
    return $this->getDBValue("need");
  }

  public function setNeed($value)
  {
    $this->setDBValue("need", $value);
  }

  public static function userIDFilter($value)
  {
    return array("column" => "UserID", "value" => $value);
  }

  public static function vendorIDFilter($value)
  {
    return array("column" => "VendorID", "value" => $value);
  }

}

?>