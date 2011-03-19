<?php

Loader::load("controller", "/controller/ModelController");
Loader::load("model", "com/passinggreen/member/Member");
Loader::load("model", "com/passinggreen/CryptKey");
Loader::load("vendor", "crypt/TwoWayEncryption");

class AdminUserUpdateController extends ModelController
{

  function __construct()
  {
    
  }

  function execute()
  {
    $return = new stdClass;
    $params = Request::_REQUEST();

    Config::set("HideDebugger", true);

    if (isset($params["id"]))
    {
      $user = new Member($params["id"]);

      if (isset($user) && $user->isValid())
      {
        if ($params["passwd"] != "")
        {
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

        if ($user->save())
        {
          // encrypt any credit card information then set the CC field
          // check for an existing private key first
          $existing_privateKey = CryptKey::findCryptKeyByUserID($user->getID());
          $cc_privateKey = null;

          if (isset($existing_privateKey) && $existing_privateKey->isValid())
          {
            // retrieve existing key
            Debugger::log("User crypt private key: " . $existing_privateKey->getKey());
            $cc_privateKey = $existing_privateKey->getKey();
          }
          else
          {
            // store key into keys table
            $user_key = new CryptKey();
            $user_key->setUserID($user->getID());
            $user_key->setKey(TwoWayEncryption::genPrivateKey());

            if ($user_key->save())
            {
              Debugger::log("Saved user crypt private key.");
              $cc_privateKey = $user_key->getKey();
            }
            else
            {
              Debugger::log("Saving user crypt private key failed! Refusing to encrypt contents!");
            }
          }

          if (!is_null($cc_privateKey))
          {
            $cc_data = unserialize(TwoWayEncryption::decrypt($user->getCC(), $cc_privateKey));
            $cc_data = array_merge(
                    $cc_data, array(
                /* 'status' => $rs->ccStatus,
                  'details' => $rs->ccDetails,
                  'error' => $rs->ccError,
                  'tcode' => $rs->ccTCode, 
                "details" => $params["ccc"], */
                "name" => $params["ccc_name"],
                "ccNum" => $params["ccc_ccNum"],
                "MM" => $params["ccc_MM"],
                "YY" => $params["ccc_YY"],
                "ccCODE" => $params["ccc_ccCode"]
                    )
            );
            $cc_encrypted_data = TwoWayEncryption::encrypt(serialize($cc_data), $cc_privateKey);
            $user->setCC($cc_encrypted_data);
            $user->save();
          }

          $return->id = $user->getID();
          $return->updated = true;
          echo json_encode($return);
          return;
        }
        else
        {
          $error = DatabaseFactory::passinggreen_db()->getLastError();

          $return->error = "could not update Member object because: " . $error;
          echo json_encode($return);
          return;
        }
      }
      else
      {
        $return->error = "ID is invalid!";
        echo json_encode($return);
        return;
      }
    }
    else
    {
      $return->error = "Missing ID!";
      echo json_encode($return);
      return;
    }
  }

}

?>