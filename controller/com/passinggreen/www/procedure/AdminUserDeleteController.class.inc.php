<?php

Loader::load("controller", "/controller/ModelController");
Loader::load("model", "com/passinggreen/member/Member");

class AdminUserDeleteController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::_REQUEST();

        Config::set("HideDebugger", true);

        if (isset($params['id'])) {
            if ($params['id'] == Config::get("Member")->getID()) {
                $return->error = "ID matches logged in ID and cannot delete!";

                echo json_encode($return);
                return;
            }

            $user = new Member($params["id"]);

            if (isset($user) && $user->isValid()) {
                $user->delete();
                $return->id = $params['id'];
                $return->deleted = true;
                echo json_encode($return);
                return;
            } else {
                $return->error = "ID is invalid!";
                echo json_encode($return);
                return;
            }
        } else {
            $return->error = "Missing ID!";
            echo json_encode($return);
            return;
        }
    }

}

?>