<?php

/*
  2-Way Encryption Scheme
  Peter Hanneman | SecureCube

  Version: v1.1 (1/26/2010)
  Abstract: Ultra secure 2-way encryption for Linux & Unix.
  Disclaimer: This algorithm has been tested against several types of partial knowledge, statistical and brute force attacks; however, I do not claim that this is an unbreakable encryption scheme and as such do not recommend using this in mission critical applications.
  Bug Reports: Thanks to your feedback over the past 6 months I've been able to continue improving the algorithm, if you have any suggestions, questions or concerns feel free to shoot me an email.
  Contact: hannemanp@gmail.com
 */

class TwoWayEncryption {

    //Generates a 320 bit private key: The SHA-1 hash function is preformed on the UNIX epoch and an additional random 750 to 1000 bits of entropy generated by the /dev/urandom algorithm.
    public static function genPrivateKey() {
        $privateKey = sha1(mktime() . shell_exec('head -c ' . mt_rand(750, 1000) . ' < /dev/urandom'));
        return $privateKey;
    }

    //Generates a 256 bit public key: The MD5 hash function is performed on the UNIX epoch and an additional random amount of entropy from the /urandom function.
    public static function genPublicKey() {
        $publicKey = md5(mktime() . shell_exec('head -c ' . mt_rand(250, 350) . ' < /dev/urandom'));
        return $publicKey;
    }

    //Returns an encrypted cipherstream provided plaintext and a private key.
    public static function encrypt($plainText, $privateKey) {
        $publicKey = self::genPublicKey();
        $textArray = str_split($plainText);

        $shiftKeyArray = array();
        for ($i = 0; $i < ceil(sizeof($textArray) / 40); $i++) {
            array_push($shiftKeyArray, sha1($privateKey . $i . $publicKey));
        }
        $cipherTextArray = array();
        for ($i = 0; $i < sizeof($textArray); $i++) {
            $cipherChar = ord($textArray[$i]) + ord($shiftKeyArray[$i]);
            $cipherChar -= floor($cipherChar / 255) * 255;
            $cipherTextArray[$i] = dechex($cipherChar);
        }

        unset($textarray);
        unset($shiftKeyArray);
        unset($cipherChar);

        $cipherStream = implode("", $cipherTextArray) . ":" . $publicKey;

        unset($publicKey);
        unset($cipherTextArray);

        return $cipherStream;
    }

    //Returns plaintext given the cipherstream and the same private key used to make it.
    public static function decrypt($cipherStream, $privateKey) {
        $cipherStreamArray = explode(":", $cipherStream);
        unset($cipherStream);
        $cipherText = $cipherStreamArray[0];
        $publicKey = $cipherStreamArray[1];
        unset($cipherStreamArray);

        $cipherTextArray = array();
        for ($i = 0; $i < strlen($cipherText); $i+=2) {
            array_push($cipherTextArray, substr($cipherText, $i, 2));
        }
        unset($cipherText);

        $shiftKeyArray = array();
        for ($i = 0; $i < ceil(sizeof($cipherTextArray) / 40); $i++) {
            array_push($shiftKeyArray, sha1($privateKey . $i . $publicKey));
        }
        unset($privateKey);
        unset($publicKey);

        $plainChar = null;
        $plainTextArray = array();
        for ($i = 0; $i < sizeof($cipherTextArray); $i++) {
            $plainChar = hexdec($cipherTextArray[$i]) - ord($shiftKeyArray[$i]);
            $plainChar -= floor($plainChar / 255) * 255;
            $plainTextArray[$i] = chr($plainChar);
        }

        unset($cipherTextArray);
        unset($shiftKeyArray);
        unset($plainChar);

        $plainText = implode("", $plainTextArray);
        return $plainText;
    }

    /*
      //Demo Section
      $text2encrypt = htmlspecialchars($_REQUEST["txt"]); //Text you want encrypted, check for special characters.
      // Encrpyt
      $privateKey = genPrivateKey(); //Generate a private key - This will made into a static value after generating it once
      $ciphertext = encrypt($text2encrypt, $privateKey); //Catches the ciphertext from the encrypt function
      // Encrpyted content
      $secret_information = $ciphertext . '.' . $privateKey;

      // Decrypt
      $cipherParts = explode(".", $secret_information); //Only for the purpose of this example to show you the various parts more clearly (see below)
      $plaintext = decrypt($cipherParts[0], $cipherParts[1]); //Catches the plaintext from the decrypt function
     *
     */

    /* echo 'Original Text: "'.$text2encrypt.'"';
      echo '<p></p>';
      echo 'Private Key: '.$privateKey;
      echo '<p></p>';
      echo 'Public Key: '.$cipherParts[1];
      echo '<p></p>';
      echo 'Cipher Text: '.$cipherParts[0];
      echo '<p></p>';
      echo 'Decrypted Text: "'.$plaintext.'"';
      echo '<p></p>';
      echo $ciphertext.'.'.$privateKey; */
}

?>