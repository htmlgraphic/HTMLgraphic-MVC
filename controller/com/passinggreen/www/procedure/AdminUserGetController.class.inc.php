<?php

Loader::load("controller", "/controller/ModelController");

class AdminUserGetController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::getRequest();

        Config::set("HideDebugger", true); //comment this out to debug

        if (isset($params['id'])) {
            $user = $this->loadModel('com/passinggreen/member/Member', $params['id']);

            if (isset($user) && $user->isValid()) {
                $return->id = $user->getID();
                $return->user = $user->toArray();
                $return->user['balance'] = number_format(40.50, 2);

                echo json_encode($return);
                return;
            } else {
                $return->error = "ID is invalid.";

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