<?php

Loader::load("controller", "/controller/ModelController");

class AdminUserAddController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::getRequest();

        Config::set("HideDebugger", true);

        $user = $this->loadModel('com/passinggreen/member/Member');

        $user->setEmail($params['useremail']);
        $user->setLevel($params['level']);
        $user->setIsEnabled($params['is_enabled']);
        $user->setFirstname($params['userFirstname']);
        $user->setLastname($params['userLastname']);

        if ($user->save()) {
            $return->id = $user->getID();
            $return->created = true;

            echo json_encode($return);
            return;
        } else {
            $return->error = "Could not create Member object!";

            echo json_encode($return);
            return;
        }
    }

}

?>