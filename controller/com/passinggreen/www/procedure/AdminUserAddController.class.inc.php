<?php

Loader::load("controller", "/controller/ModelController");
Loader::load("model", "com/passinggreen/member/Member");
Loader::load("model", "com/passinggreen/CryptKey");
Loader::load("vendor", "crypt/TwoWayEncryption");

class AdminUserAddController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::getRequest();

        Config::set("HideDebugger", true);

        $user = new Member();
        $user->setEmail($params["useremail"]);
        $user->setLevel($params["level"]);
        $user->setIsEnabled($params["is_enabled"]);
        $user->setFirstname($params["userFirstname"]);
        $user->setLastname($params["userLastname"]);
        $user->setShipAddr1($params["shipAddr1"]);

        if ($user->save()) {
            $return->id = $user->getID();
            $return->created = true;
            echo json_encode($return);
            return;
        } else {
            $error = DatabaseFactory::passinggreen_db()->getLastError();
            
            if ($error->number() == 1062) {
                $return->error = "e-mail address already exists.";
                echo json_encode($return);
                return;
            }
            
            $return->error = "could not create Member object because: " . $error;
            echo json_encode($return);
            return;
        }
    }

}

?>