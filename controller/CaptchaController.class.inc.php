<?php

class CaptchaController extends Controller
{

  function __construct()
  {
    parent::__construct();
    Config::set('HideDebugger', true);
    $this->captcha = Loader::loadNew('utility', 'captcha/Captcha');
  }

  function activate()
  {
    if (Request::_GET('audio'))
    {
      $this->captcha->getAudioCaptcha();
    }
    else
    {
      $this->captcha->getImageCaptcha();
    }
  }

}

?>