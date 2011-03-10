<?php

Loader::load("controller", "/controller/ModelController");

class AdminUserDeleteController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::getRequest();

        Config::set("HideDebugger", true); //comment this out to debug

        if (isset($params['id'])) {
            if ($params['id'] == Config::get("Member")->getID()) {
                $return->error = "ID matches logged in ID and cannot delete!";

                echo json_encode($return);
                return;
            }

            $user = $this->loadModel('com/passinggreen/member/Member', $params['id']);

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