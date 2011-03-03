<?php
define('EA_ENABLE_SMTP',true);
define('EA_SMTP_SERVER','localhost');
define('EA_SMTP_USER','');
define('EA_SMTP_PASSWORD','');

Loader::load('vendor', "phpmailer/phpmailer.lang-en.php");
Loader::load('vendor', "phpmailer/class.phpmailer.php");
?>
