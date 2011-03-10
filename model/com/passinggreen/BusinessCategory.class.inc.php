<?php

Loader::load('model', array(
            "DBObject"
        ));

class BusinessCategory extends DBObject {

    public function __construct($mem_id = null) {
        if (isset($mem_id)) {
            $this->setDBValue("AutoID", $mem_id);
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
        return "business_categories";
    }

    protected function where_clause() {
        return "`AutoID` = '{$this->getDBValue('AutoID')}'";
    }

    public function getID() {
        return $this->getDBValue($this->primary_key());
    }

    function __toString() {
        return "Business Category: {$this->getID()}";
    }

    public function getCategory() {
        return $this->getDBValue("category");
    }

    function setCategory($value) {
        $this->setDBValue("category", $value);
    }

    public static function companyFilter($company) {
        return array("column" => "company", "value" => $company);
    }

}

?>