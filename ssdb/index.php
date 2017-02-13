<?php
error_reporting(E_ALL & ~E_NOTICE);

//define('YEPF_IS_DEBUG', 'yoka-inc4');
//include('../../YEPF3/global.inc.php');

define('APP_PATH', dirname(__FILE__) . '/app');
// You can change IPHP_PATH
define('IPHP_PATH', dirname(__FILE__) . '/iphp');
require_once(IPHP_PATH . '/loader.php');

App::set_base_url("/ssdb/");
App::run();
