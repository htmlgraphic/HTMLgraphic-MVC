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
        $params = Request::_REQUEST();

        Config::set("HideDebugger", true);

        $user = new Member();
        if ($params["passwd"] != "") {
            $user->setPassword($params["passwd"]);
        }
        $user->setLevel($params["level"]);
        $user->setIsEnabled($params["is_enabled"]);
        $user->setUserFirstname($params["userFirstname"]);
        $user->setUserLastname($params["userLastname"]);
        $user->setUserEmail($params["useremail"]);
        $user->setUserBio($params["userBio"]);
        $user->setWeb($params["web"]);
        $user->setUserCompany($params["userCompany"]);
        $user->setUserCompanyType($params["userCompanyType"]);
        $user->setUserAddr1($params["userAddr1"]);
        $user->setUserAddr2($params["userAddr2"]);
        $user->setUserCity($params["userCity"]);
        $user->setUserState($params["userState"]);
        $user->setUserCountry($params["userCountry"]);
        $user->setUserZip($params["userZip"]);
        $user->setUserPhone($params["userPhone"]);
        $user->setUserAltPhone($params["userAltPhone"]);
        $user->setUserFax($params["userFax"]);
        $user->setSiteAreas($params["siteAreas"]);
        $user->setUpdates($params["updates"]);
        $user->setPaymentType($params["paymentType"]);
        $user->setPaymentTypeDetails($params["paymentTypeDetails"]);
        $user->setShipAddr1($params["shipAddr1"]);
        $user->setShipAddr2($params["shipAddr2"]);
        $user->setShipCity($params["shipCity"]);
        $user->setShipState($params["shipState"]);
        $user->setShipCountry($params["shipCountry"]);
        $user->setShipZip($params["shipZip"]);

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