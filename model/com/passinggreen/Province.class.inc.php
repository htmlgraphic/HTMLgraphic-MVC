<?php

Loader::load('model', array(
            "DBObject"
        ));
Loader::load('model', 'com/passinggreen/Country');

class Province extends DBObject {

    public function __construct($mem_id = null) {
        if (isset($mem_id)) {
            $this->setDBValue("autoid", $mem_id);
        }
    }

    public function can_load() {
        $id = $this->getDBValue("autoid");

        return isset($id);
    }

    static function primary_key() {
        return "autoid";
    }

    protected function db() {
        return DatabaseFactory::passinggreen_db();
    }

    protected function master_db() {
        return DatabaseFactory::passinggreen_master_db();
    }

    protected function table() {
        return "provinces";
    }

    protected function where_clause() {
        return "`autoid` = '{$this->getDBValue('autoid')}'";
    }

    public function getID() {
        return $this->getDBValue($this->primary_key());
    }

    function __toString() {
        return "Province: {$this->getID()}";
    }

    public function getCountry() {
        return new Country($this->getDBValue("id"));
    }

    public function setCountry($value) {
        if (is_object($value) && is_a($value, 'Country')) {
            $this->setDBValue("id", $value->getID());
        }
    }

    public function getProvince() {
        return $this->getDBValue("name");
    }

    function setProvince($value) {
        $this->setDBValue("name", $value);
    }

    public static function symbolFilter($symbol) {
        return array("column" => "symbol", "value" => $symbol);
    }

    public static function countryFilter($country) {
        if (is_object($country) && is_a($country, 'Country')) {
            return array("column" => "id", "value" => $country->getID());
        }
    }

}

?>