<?php

Loader::load('model', array(
            "DBObject"
        ));

class Country extends DBObject {

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
        return "countries";
    }

    protected function where_clause() {
        return "`autoid` = '{$this->getDBValue('autoid')}'";
    }

    public function getID() {
        return $this->getDBValue($this->primary_key());
    }

    function __toString() {
        return "Country: {$this->getID()}";
    }

    public function getCountry() {
        return $this->getDBValue("country");
    }

    function setCategory($value) {
        $this->setDBValue("country", $value);
    }

    public static function symbolFilter($symbol) {
        return array("column" => "symbol", "value" => $symbol);
    }

    public static function showFilter($show) {
        return array("column" => "show", "value" => ($show) ? "yes" : "no");
    }

}

?>