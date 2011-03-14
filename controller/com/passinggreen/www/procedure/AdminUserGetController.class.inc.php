<?php

Loader::load('controller', '/controller/ModelController');
Loader::load('model', 'com/passinggreen/member/Member');
Loader::load('model', 'com/passinggreen/CryptKey');
Loader::load('vendor', 'crypt/crypt.php');

class AdminUserGetController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::getRequest();

        Config::set("HideDebugger", true); //comment this out to debug

        if (isset($params['id'])) {
            $user = new Member($params['id']);

            if (isset($user) && $user->isValid()) {
                // retrieve account balance based on transactions
                $user_balance = 29.99;

                $user_crypt_key = CryptKey::findCryptKeyByUserID($user->getID());

                // decrypt cc field if it is not empty
                if (isset($user_crypt_key) && $user_crypt_key->isValid()) {
                    $decoded_cc_data = decrypt($user->getCC(), $user_crypt_key->getKey());
                    //$return->user_key = $user_crypt_key->toArray();
                }

                $return->id = $user->getID();
                $return->user = $user->toArray();
                $return->user['userCompanyType[]'] = unserialize($return->user['userCompanyType']);
                $return->user['siteAreas[]'] = explode(',', $return->user['siteAreas']);
                $return->user['balance'] = number_format($user_balance, 2);
                $return->user['cc'] = $decoded_cc_data;

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