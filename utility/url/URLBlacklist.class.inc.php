<?php

class URLBlacklist
{

  public static function is_blacklisted($url)
  {
    return self::isBlacklisted($url);
  }

  public static function isBlacklisted($url)
  {
    @$url_parsed = parse_url($url);

    if (isset($url_parsed['host']))
    {
      $url = $url_parsed['host'] . '/';

      //follow google canonicalization rules
      $url = urldecode($url); //remove hex encodings
      $url = preg_replace('/^\.*/', '', $url); //remove leading dots
      $url = preg_replace('/\.*$/', '', $url); //remove trailing dots
      $url = preg_replace('/\.+/', '.', $url); //replace consecutive dots
      $url = preg_replace('/\/+/', '/', $url); //replace consecutive slashes

      $hash = md5($url);

      $sql = "SELECT * FROM `malware_hashes` WHERE `hash` = '{$hash}'";
      $result = DatabaseFactory::ea_safe_browsing_db()->query($sql);

      return ($result->num_rows) ? true : false;
    }
  }

}

?>