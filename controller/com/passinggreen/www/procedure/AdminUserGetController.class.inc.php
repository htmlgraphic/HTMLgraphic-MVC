<?php

Loader::load("controller", "/controller/ModelController");
Loader::load("model", "com/passinggreen/member/Member");
Loader::load("model", "com/passinggreen/Referral");
Loader::load("model", "com/passinggreen/Transaction");
Loader::load("model", "com/passinggreen/CryptKey");
Loader::load("vendor", "crypt/TwoWayEncryption");

class AdminUserGetController extends ModelController
{

  function __construct()
  {
    
  }

  function execute()
  {
    $return = new stdClass;
    $params = Request::_REQUEST();

    Config::set("HideDebugger", true); //comment this out to debug

    if (isset($params["id"]))
    {
      $user = new Member($params["id"]);

      if (isset($user) && $user->isValid())
      {
        // retrieve account balance based on transactions
        $user_balance = 0.00;
        $user_commission = 0.00;
        /* $Referral_Join = DBObject::collection("Referral");
          $Member_Join = DBObject::collection("Member");
          $Member_Vendor_Join = DBObject::collection("Member");
          $Transactions = DBObject::collection("Transaction")
          ->applyJoin("LEFT OUTER", $Referral_Join, array("ReferralID", "AutoID"))
          ->applyJoin("LEFT OUTER", $Member_Join, array("UserID", "AutoID"))
          ->applyJoin("LEFT OUTER", $Member_Vendor_Join, array("VendorID", "AutoID"))
          ->applyUserIDFilter($user->getID());
          Debugger::log(Var_Dump::display($Transactions->getTransactions(), true));
         */

        $q = "SELECT
				t.`amount`,
				r.*,
				CONCAT(user.`userFirstname`, CONCAT(' ',user.`userLastname`)) as _from,
				CONCAT(vendor.`userFirstname`, CONCAT(' ',vendor.`userLastname`)) as _to
			FROM  `transactions` t

			LEFT OUTER JOIN `referrals` r
				ON (t.`ReferralID` = r.`AutoID`)

			LEFT OUTER JOIN `user_signup` user
				ON (r.`UserID` = user.`AutoID`)

			LEFT OUTER JOIN `user_signup` vendor
				ON (r.`VendorID` = vendor.`AutoID`)

			WHERE t.`UserID` ='" . $user->getID() . "'";
        $user_transactions = DatabaseFactory::passinggreen_db()->query($q);
        Debugger::log(Var_Dump::display($user_transactions, true));

        while ($user_transactions_row = $user_transactions->fetch_object())
        {
          if ($user_transactions_row->UserID == $user->getID())
          {
            $user_balance += $user_transactions_row->amount;
            //$referrals_passed[] = $row;
          }
          else if ($user_transactions_row->VendorID == $user->getID())
          {
            $user_commission += $user_transactions_row->amount;
            //$referrals_received[] = $row;
          }

          #else
          #echo "PG.com +".$row->amount."<br>";
          #echo "<pre>".print_r($row, true)."</pre>";
        }

        // referrals
        $user_referrals_passed = DBObject::collection("Referral", DBObject::CONSISTENCY_ABSOLUTE)->applyUserIDFilter($user->getID())->getReferralCount();
        $user_referrals_received = DBObject::collection("Referral", DBObject::CONSISTENCY_ABSOLUTE)->applyVendorIDFilter($user->getID())->getReferralCount();
        Debugger::log("refs passed: $user_referrals_passed");
        Debugger::log("refs recvd: $user_referrals_received");

        // decrypt cc field if it is not empty
        $user_crypt_key = CryptKey::findCryptKeyByUserID($user->getID());
        if (isset($user_crypt_key) && $user_crypt_key->isValid())
        {
          Debugger::log("User crypt private key: " . $user_crypt_key->getKey());
          $decoded_cc_data = unserialize(TwoWayEncryption::decrypt($user->getCC(), $user_crypt_key->getKey()));
        }
        else
        {
          $decoded_cc_data = null;
        }

        $return->id = $user->getID(); // object id
        $return->user = $user->toArray(); // set the base return, values can be overridden below.
        $return->user["userCompanyType[]"] = $user->getUserCompanyType();
        $return->user["siteAreas[]"] = $user->getSiteAreas();
        $return->user["passwd"] = null;
        $return->user["balance"] = number_format($user_balance, 2);

        if (!is_null($decoded_cc_data))
        {
          $return->user["ccc"] = $decoded_cc_data["details"];
          $return->user["ccc_status"] = $decoded_cc_data["status"];
          $return->user["ccc_tcode"] = $decoded_cc_data["tcode"];
          $return->user["ccc_ccNum"] = $decoded_cc_data["ccNum"];
          $return->user["ccc_MM"] = $decoded_cc_data["ccMonth"];
          $return->user["ccc_YY"] = $decoded_cc_data["ccYear"];
          $return->user["ccc_ccCode"] = $decoded_cc_data["ccCODE"];
        }

        $return->user["referralsPassed"] = $user_referrals_passed;
        $return->user["referralsReceived"] = $user_referrals_received;
        $return->user["accountBalance"] = number_format($user_balance, 2);
        $return->user["commissionDue"] = number_format($user_commission, 2);
        echo json_encode($return);
        return;
      }
      else
      {
        $return->error = "ID is invalid.";
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