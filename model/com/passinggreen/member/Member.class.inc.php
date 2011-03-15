<?php

Loader::load("model", array(
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

    public function __toString() {
        return "Member: {$this->getID()}";
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

    public function validatePassword($password) {
        $query = "SELECT `AutoID` FROM `" . $this->table() . "` WHERE `passwd` = '" . hash('sha1', $password) . "' && `AutoID` = '{$this->getID()}'";
        $result = $this->master_db()->query($query);

        if ($result && $result->num_rows) {
            return true;
        }

        return false;
    }

    /*
     * FIELD ACCESSERS
     */

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
        return $this->getUserFirstname() . " " . $this->getUserLastname();
    }

    public function getUserFirstname() {
        return $this->getDBValue("userFirstname");
    }

    function setUserFirstname($value) {
        $this->setDBValue("userFirstname", $value);
    }

    public function getUserLastname() {
        return $this->getDBValue("userLastname");
    }

    public function setUserLastname($value) {
        $this->setDBValue("userLastname", $value);
    }

    public function getUserEmail() {
        return $this->getDBValue("useremail");
    }

    public function setUserEmail($value) {
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

    public function getUserBio() {
        return $this->getDBValue("userBio");
    }

    public function setUserBio($value) {
        $this->setDBValue("userBio", $value);
    }

    public function getWeb() {
        return $this->getDBValue("web");
    }

    public function setWeb($value) {
        $this->setDBValue("web", $value);
    }

    public function getUserCompany() {
        return $this->getDBValue("userCompany");
    }

    public function setUserCompany($value) {
        $this->setDBValue("userCompany", $value);
    }

    public function getUserCompanyType() {
        return unserialize($this->getDBValue("userCompanyType"));
    }

    public function setUserCompanyType($value) {
        $this->setDBValue("userCompanyType", serialize($value));
    }

    public function getUserPhone() {
        return $this->getDBValue("userPhone");
    }

    public function setUserPhone($value) {
        $this->setDBValue("userPhone", $value);
    }

    public function getUserAltPhone() {
        return $this->getDBValue("userAltPhone");
    }

    public function setUserAltPhone($value) {
        $this->setDBValue("userAltPhone", $value);
    }

    public function getUserFax() {
        return $this->getDBValue("userFax");
    }

    public function setUserFax($value) {
        $this->setDBValue("userFax", $value);
    }

    public function getUserBirthday() {
        return $this->getDBValue("userBirthday");
    }

    public function setUserBirthday($value) {
        $this->setDBValue("userBirthday", $value);
    }

    public function getUserAddr1() {
        return $this->getDBValue("userAddr1");
    }

    public function setUserAddr1($value) {
        $this->setDBValue("userAddr1", $value);
    }

    public function getUserAddr2() {
        return $this->getDBValue("userAddr2");
    }

    public function setUserAddr2($value) {
        $this->setDBValue("userAddr2", $value);
    }

    public function getUserCity() {
        return $this->getDBValue("userCity");
    }

    public function setUserCity($value) {
        $this->setDBValue("userCity", $value);
    }

    public function getUserState() {
        return $this->getDBValue("userState");
    }

    public function setUserState($value) {
        $this->setDBValue("userState", $value);
    }

    public function getUserZip() {
        return $this->getDBValue("userZip");
    }

    public function setUserZip($value) {
        $this->setDBValue("userZip", $value);
    }

    public function getUserCountry() {
        return $this->getDBValue("userCountry");
    }

    public function setUserCountry($value) {
        $this->setDBValue("userCountry", $value);
    }

    public function getIP() {
        return $this->getDBValue("last_ip");
    }

    public function setIP($value) {
        $this->setDBValue("last_ip", $value);
    }

    public function getAgent() {
        return $this->getDBValue("agent");
    }

    public function setAgent($value) {
        $this->setDBValue("agent", $value);
    }

    public function getSiteAreas() {
        return $this->getDBValue("siteAreas");
    }

    public function setSiteAreas($value) {
        $this->setDBValue("siteAreas", @implode(',', $value));
    }

    public function getUpdates() {
        return $this->getDBValue("updates");
    }

    public function setUpdates($value) {
        $this->setDBValue("updates", $value);
    }

    public function getPaymentType() {
        return $this->getDBValue("paymentType");
    }

    public function getShipAddr1() {
        return $this->getDBValue("shipAddr1");
    }

    public function setShipAddr1($value) {
        $this->setDBValue("shipAddr1", $value);
    }

    public function getShipAddr2() {
        return $this->getDBValue("shipAddr2");
    }

    public function setShipAddr2($value) {
        $this->setDBValue("shipAddr2", $value);
    }

    public function getShipCity() {
        return $this->getDBValue("shipCity");
    }

    public function setShipCity($value) {
        $this->setDBValue("shipCity", $value);
    }

    public function getShipState() {
        return $this->getDBValue("shipState");
    }

    public function setShipState($value) {
        $this->setDBValue("shipState", $value);
    }
    
    public function getShipZip() {
        return $this->getDBValue("shipZip");
    }
    
    public function setShipZip($value) {
        $this->setDBValue("shipZip", $value);
    }

    public function getShipCountry() {
        return $this->getDBValue("shipCountry");
    }

    public function setShipCountry($value) {
        $this->setDBValue("shipCountry", $value);
    }

    public function getCC() {
        return $this->getDBValue("cc");
    }

    public function setCC($value) {
        $this->setDBValue("cc", $value);
    }

    public function getLastLogin($format = "Y-m-d H:i:s") {
        $login = $this->getDBValue("last_login");

        if ($login == "0000-00-00 00:00:00" || !$login) {
            return;
        }

        return date($format, strtotime($this->getDBValue("last_login")));
    }

    public function updateLastLogin() {
        $date = date('Y-m-d H:i:s');
        $this->setDBValue('last_login', $date);
    }

    public function recordLogin() {
        $date = date('Y-m-d H:i:s');
        $query = "UPDATE " . $this->table() . " SET `last_login`='$date' WHERE `AutoID`='{$this->getID()}'";
        $submit = $this->db()->execute($query);
    }

    public function getLastModified($format = "Y-m-d H:i:s") {
        $mod = $this->getDBValue("date_modified");

        if ($mod == "0000-00-00 00:00:00" || !$mod) {
            return;
        }

        return date($format, strtotime($this->getDBValue("date_modified")));
    }

    public function updateLastModified() {
        $date = date('Y-m-d H:i:s');
        $this->setDBValue('date_modified', $date);
    }

    /*
     * FILTERS
     */

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