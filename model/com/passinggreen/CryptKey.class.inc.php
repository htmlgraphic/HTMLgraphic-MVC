<?php

Loader::load('model', array(
            "DBObject"
        ));

class CryptKey extends DBObject
{

  public function __construct($id = null)
  {
    if (isset($id))
    {
      $this->setDBValue("UserID", $id);
    }
  }

  public function can_load()
  {
    $id = $this->getDBValue("UserID");

    return isset($id);
  }

  static function primary_key()
  {
    return "UserID";
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
    return "keys";
  }

  protected function where_clause()
  {
    return "`UserID` = '{$this->getDBValue('UserID')}'";
  }

  public function getID()
  {
    return $this->getDBValue($this->primary_key());
  }

  public function __toString()
  {
    return "CryptKey: {$this->getID()}";
  }

  public static function findCryptKeyByUserID($id)
  {
    $id = (int)$id;

    $sql = "SELECT `UserID` FROM `keys`";

    if ($id)
    {
      $sql .= " WHERE";
    }
    else
    {
      return null;
    }


    if ($id)
    {
      $sql .= " `UserID` = '$id'";
    }

    if ($res = DatabaseFactory::passinggreen_master_db()->query($sql))
    {

      if ($res->num_rows == 1)
      {
        $crypt_key = $res->fetch_object();

        return new self($crypt_key->UserID);
      }
      else
        return null;
    }

    return null;
  }

  public function getKey()
  {
    return $this->getDBValue("value");
  }

  public function setKey($value)
  {
    $this->setDBValue("value", $value);
  }

  public function getUserID()
  {
    return $this->getDBValue("UserID");
  }

  public function setUserID($value)
  {
    $this->setDBValue("UserID", $value);
  }

  public static function userIDFilter($userID)
  {
    return array("column" => "UserID", "value" => $userID);
  }

}

?>