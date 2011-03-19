<?php

class AssetController extends Controller
{

  function activate()
  {
    Loader::load('module', 'file/CombineAssetsModule');
    $filename = URL::getPathMap(array('file'));
    $filename = $filename['file'];

    $cache = CombineAssetsModule::getCombinedAsset($filename);

    if ($cache && is_string($cache))
    {
      list($hash, $extension) = explode('.', $filename);

      $mimetype = ($extension == 'js') ? 'javascript' : 'css';
      $lastmod = substr($hash, 32);

      $lastmod = gmdate('D, d M Y H:i:s', intval($lastmod)) . ' GMT';
      $etag = '"' . md5($lastmod) . '"';

      $ifmod = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastmod : null;

      $iftag = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] == $etag : null;

      //stop cache-control and pragma from making caching fail
      header("Cache-Control:");
      header("Pragma:");

      // Fancy-pants conditional get
      if (($ifmod || $iftag) && ($ifmod !== false && $iftag !== false))
      {
        header("ETag: $etag");
        header('HTTP/1.0 304 Not Modified');
        exit;
      }

      header("ETag: $etag");
      header("Last-Modified: $lastmod");
      header("Content-type: text/$mimetype; charset: UTF-8");

      $expires = "Expires: " . gmdate("D, d M Y H:i:s", time() + 31556926) . " GMT";
      header($expires);
      echo $cache;
    }
    else
    {
      header('HTTP/1.0 404 Not Found');
      echo "Could not load resource: $filename";
      error_log('[Asset Lookup] Failed to locate ' . $filename);
    }
    Config::set('HideDebugger', true);
    exit;
  }

}

?>