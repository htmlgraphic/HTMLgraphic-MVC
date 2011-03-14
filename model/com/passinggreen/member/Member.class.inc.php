<?php

Loader::load('model', array(
            "DBObject"
        ));

class Member extends DBObject {
    const BASIC_USER = "user";
    const BASIC_MEMBER = "member";
    const ADMIN_USER = "admin";
    const GOD_USER = "superadmin";

    public function __construct($mem_id = null) {
        if (isset($mem_id)) {
            $this->setDBValue("AutoID", $mem_id);
        } else {
            $this->setDBValue("date_added", date("Y-m-d H:i:s"));
        }
    }

    public function can_load() {
        $id = $this->getDBValue("AutoID");

        return isset($id);
    }

    static function primary_key() {
        return "AutoID";
    }

    public static function findMemberWithEmail($email) {
        return self::lookup_member(null, $email);
    }

    public static function lookup_member($id=null, $email=null) {
        $id = (int) $id;
        $email = trim($email);

        $sql = "SELECT `AutoID` , `useremail`, `userFirstname`, `userLastname` FROM `user_signup`";

        if ($id || $email || $author) {
            $sql .= " WHERE";
        } else {
            return null;
        }


        if ($id) {
            $sql.= " `AutoID` = '$id'";
            if ($email || $author)
                $sql.= " &&";
        }

        if ($email) {
            $sql.= " `useremail` = '$email'";
        }

        if ($res = DatabaseFactory::passinggreen_master_db()->query($sql)) {

            if ($res->num_rows == 1) {
                $member = $res->fetch_object();

                return new Member($member->AutoID);
            }
            else
                return null;
        }

        return null;
    }

    protected function db() {
        return DatabaseFactory::passinggreen_db();
    }

    protected function master_db() {
        return DatabaseFactory::passinggreen_master_db();
    }

    protected function table() {
        return "user_signup";
    }

    protected function where_clause() {
        return "`AutoID` = '{$this->getDBValue('AutoID')}'";
    }

    public function getID() {
        return $this->getDBValue($this->primary_key());
    }

    function __toString() {
        return "Member: {$this->getID()}";
    }

    public function getPasswordHash() {
        return $this->getDBValue("passwd");
    }

    public function setPassword($password) {
        $passwordHash = hash('sha1', $password);
        $this->setPasswordHash($passwordHash);
    }

    public function setPasswordHash($passwordHash) {
        $this->setDBValue('passwd', $passwordHash);
    }

    public function getFullName() {
        return $this->getFirstName() . " " . $this->getLastName();
    }

    public function getFirstName() {
        return $this->getDBValue("userFirstname");
    }

    function setFirstName($value) {
        $this->setDBValue("userFirstname", $value);
    }

    public function getLastName() {
        return $this->getDBValue("userLastname");
    }

    function setLastName($value) {
        $this->setDBValue("userLastname", $value);
    }

    function getAddress1() {
        return $this->getDBValue("userAddr1");
    }

    function setAddress1($value) {
        $this->setDBValue("userAddr1", $value);
    }

    function getAddress2() {
        return $this->getDBValue("userAddr2");
    }

    function setAddress2($value) {
        $this->setDBValue("userAddr2", $value);
    }

    function getCity() {
        return $this->getDBValue("userCity");
    }

    function setCity($value) {
        $this->setDBValue("userCity", $value);
    }

    function getState() {
        return $this->getDBValue("userState");
    }

    function setState($value) {
        $this->setDBValue("userState", $value);
    }

    function getZipCode() {
        return $this->getDBValue("userZip");
    }

    function setZipCode($value) {
        $this->setDBValue("userZip", $value);
    }

    function getPhoneNumber() {
        return $this->getDBValue("userPhone");
    }

    function setPhoneNumber($value) {
        $this->setDBValue("userPhone", $value);
    }

    function getBusinessName() {
        return $this->getDBValue("userCompany");
    }

    function setBusinessName($value) {
        $this->setDBValue("userCompany", $value);
    }

    function getIP() {
        return $this->getDBValue("last_ip");
    }

    function setIP($value) {
        $this->setDBValue("last_ip", $value);
    }

    public function getEmail() {
        return $this->getDBValue("useremail");
    }

    function setEmail($value) {
        $this->setDBValue("useremail", $value);
    }

    public function setIsEnabled($value) {
        $this->setDBValue("is_enabled", $value);
    }

    public function getIsEnabled() {
        return $this->getDBValue("is_enabled");
    }

    public function setLevel($value) {
        $this->setDBValue("level", $value);
    }

    public function getLevel() {
        return $this->getDBValue("level");
    }

    function getCountry() {
        return $this->getDBValue("userCountry");
    }

    function setCountry($value) {
        $this->setDBValue("userCountry", $value);
    }

    function getCC() {
        return $this->getDBValue("cc");
    }

    function setCC($value) {
        $this->setDBValue("cc", $value);
    }

    function validatePassword($password) {
        $query = "SELECT `AutoID` FROM `" . $this->table() . "` WHERE `passwd` = '" . hash('sha1', $password) . "' && `AutoID` = '{$this->getID()}'";
        $result = DatabaseFactory::passinggreen_master_db()->query($query);

        if ($result && $result->num_rows) {
            return true;
        }

        return false;
    }

    public function getLastLogin($format = "Y-m-d H:i:s") {
        $login = $this->getDBValue("last_login");

        if ($login == "0000-00-00 00:00:00" || !$login) {
            return;
        }

        return date($format, strtotime($this->getDBValue("last_login")));
    }

    public function recordLogin() {
        $date = date('Y-m-d H:i:s');
        $query = "UPDATE " . $this->table() . " SET `last_login`='$date' WHERE `AutoID`='{$this->getID()}'";
        $submit = $this->db()->execute($query);
    }

    public function updateLastLogin() {
        $date = date('Y-m-d H:i:s');
        $this->setDBValue('last_login', $date);
    }

    public static function emailFilter($email) {
        return array("column" => "useremail", "value" => $email);
    }

    public static function countryFilter(Country $country) {
        return array("column" => "userCountry", "value" => $country->getCountry());
    }

    public static function userFilter() {
        return array("column" => "level", "value" => self::BASIC_USER);
    }

    public static function memberFilter() {
        return array("column" => "level", "value" => self::BASIC_MEMBER);
    }

    public static function adminFilter() {
        return array("column" => "level", "value" => self::ADMIN_USER);
    }

    public static function godFilter() {
        return array("column" => "level", "value" => self::GOD_USER);
    }

    public static function neverLoggedInFilter() {
        return array('column' => "last_login", 'value' => "0000-00-00 00:00:00");
    }

    public static function lastLoginAfterFilter($date) {
        return array(
            'column' => "last_login",
            'value' => date('Y-m-d', strtotime($date)),
            'comparison' => '>='
        );
    }

    public static function lastLoginBeforeFilter($date) {
        return array(
            'column' => "last_login",
            'value' => date('Y-m-d', strtotime($date)),
            'comparison' => '<='
        );
    }

}

?>