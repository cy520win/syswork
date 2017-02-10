<?php
 
define('IS_CGI', (0 === strpos(PHP_SAPI, 'cgi') || false !== strpos(PHP_SAPI, 'fcgi')) ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

/*if (!IS_CLI) {
    exit('forbidden!');
}*/

require_once './vendor/autoload.php';

global $configInfo;
$configInfo = require_once 'config.php';
 
 
if ($tag == 'zhiboToErp') {

} else {
    echo '';
    die();
}
