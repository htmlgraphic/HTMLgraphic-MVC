<?php

Loader::load("controller", "/controller/ModelController");
Loader::load("model", "com/passinggreen/member/Member");
Loader::load("model", "com/passinggreen/Referral");
Loader::load("model", "com/passinggreen/CryptKey");
Loader::load("vendor", "crypt/TwoWayEncryption");

class AdminUserGetController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::getRequest();

        //Config::set("HideDebugger", true); //comment this out to debug

        if (isset($params["id"])) {
            $user = new Member($params["id"]);

            if (isset($user) && $user->isValid()) {
                // retrieve account balance based on transactions
                $user_balance = 29.99;

                // referrals
                $user_referrals_passed = DBObject::collection("Referral", DBObject::CONSISTENCY_ABSOLUTE)->applyUserIDFilter($user->getID())->getReferralCount();
                $user_referrals_received = DBObject::collection("Referral", DBObject::CONSISTENCY_ABSOLUTE)->applyVendorIDFilter($user->getID())->getReferralCount();
                Debugger::log("refs passed: $user_referrals_passed");
                Debugger::log("refs recvd: $user_referrals_received");

                // decrypt cc field if it is not empty
                $user_crypt_key = CryptKey::findCryptKeyByUserID($user->getID());
                if (isset($user_crypt_key) && $user_crypt_key->isValid()) {
                    Debugger::log("User crypt private key: " . $user_crypt_key->getKey());
                    $decoded_cc_data = TwoWayEncryption::decrypt($user->getCC(), $user_crypt_key->getKey());
                } else {
                    $decoded_cc_data = null;
                }

                $return->id = $user->getID(); // object id
                $return->user = $user->toArray(); // set the base return, values can be overridden below.
                $return->user["userCompanyType[]"] = unserialize($return->user['userCompanyType']);
                $return->user["siteAreas[]"] = explode(',', $return->user['siteAreas']);
                $return->user["balance"] = number_format($user_balance, 2);

                if (!is_null($decoded_cc_data)) {
                    $return->user["ccc"] = $decoded_cc_data;
                    $return->user["ccc_status"] = $decoded_cc_data['status'];
                    $return->user["ccc_tcode"] = $decoded_cc_data['tcode'];
                    $return->user["ccc_ccNum"] = $decoded_cc_data['ccNum'];
                    $return->user["ccc_MM"] = $decoded_cc_data['ccMM'];
                    $return->user["ccc_YY"] = $decoded_cc_data['ccYear'];
                    $return->user["ccc_ccCode"] = $decoded_cc_data['ccCODE'];
                }

                $return->user["referralsPassed"] = $user_referrals_passed;
                $return->user["referralsReceived"] = $user_referrals_received;

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