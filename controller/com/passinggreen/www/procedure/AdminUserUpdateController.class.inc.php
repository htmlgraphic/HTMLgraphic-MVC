<?php

Loader::load("controller", "/controller/ModelController");
Loader::load("model", "com/passinggreen/member/Member");
Loader::load("model", "com/passinggreen/CryptKey");
Loader::load("vendor", "crypt/TwoWayEncryption");

class AdminUserUpdateController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::getRequest();

        Config::set("HideDebugger", true);

        if (isset($params["id"])) {
            $user = new Member($params["id"]);

            if (isset($user) && $user->isValid()) {
                $user->setEmail($params["useremail"]);
                $user->setLevel($params["level"]);
                $user->setIsEnabled($params["is_enabled"]);
                $user->setFirstname($params["userFirstname"]);
                $user->setLastname($params["userLastname"]);

                if ($user->save()) {
                    $return->id = $user->getID();
                    $return->updated = true;
                    echo json_encode($return);
                    return;
                } else {
                    $error = DatabaseFactory::passinggreen_db()->getLastError();

                    $return->error = "could not update Member object because: " . $error;
                    echo json_encode($return);
                    return;
                }
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