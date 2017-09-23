<?
header("Content-Type: text/html; charset=GBK");
include("../db_admin/config.inc.php");

// 处理变量 registerglobals=off
if ( function_exists('ini_get') ) {
	$onoff = ini_get('register_globals');
} else {
	$onoff = get_cfg_var('register_globals');
}
if ($onoff != 1) {
	@extract($_SERVER, EXTR_SKIP);
	@extract($_COOKIE, EXTR_SKIP);
	@extract($_FILES, EXTR_SKIP);
	@extract($_POST, EXTR_SKIP);
	@extract($_GET, EXTR_SKIP);
	@extract($_ENV, EXTR_SKIP);
}
if(isset($PHPSESSID)) { 
session_id($PHPSESSID); 
}
else $PHPSESSID = session_id();
// 处理变量 magic_quotes=off
function stripslashesarray (&$arr) {
  while (list($key,$val)=each($arr)) {
    if ($key!="templatesused" and $key!="argc" and $key!="argv") {
			if (is_string($val) AND (strtoupper($key)!=$key OR ("".intval($key)=="$key"))) {
				$arr["$key"] = stripslashes($val);
			} else if (is_array($val) AND ($key == '_POST' OR $key == '_GET' OR strtoupper($key)!=$key)) {
				$arr["$key"] = stripslashesarray($val);
			}
	  }
  }
  return $arr;
}
if (get_magic_quotes_gpc() and is_array($GLOBALS)) {
  if (isset($attachment)) {
    $GLOBALS['attachment'] = addslashes($GLOBALS['attachment']);
  }
  if (isset($avatarfile)) {
    $GLOBALS['avatarfile'] = addslashes($GLOBALS['avatarfile']);
  }
  $GLOBALS = stripslashesarray($GLOBALS);
}
set_magic_quotes_runtime(0);

if ($_SERVER['SCRIPT_NAME'] and substr($_SERVER['SCRIPT_NAME'] , -strlen('.php')) == '.php') {
	$currenturl = strtolower($_SERVER['SCRIPT_NAME']);
	$currentfullurl= $_SERVER['REQUEST_URI'];//含参数的路径
} elseif ($_SERVER['REDIRECT_URL'] and substr($_SERVER['REDIRECT_URL'] , -strlen('.php')) == '.php') {
	$currenturl = strtolower($_SERVER['REDIRECT_URL']);
	$currentfullurl = $_SERVER['REDIRECT_URL'];
} else {
	$currenturl = strtolower($_SERVER['PHP_SELF']);
	$currentfullurl= $_SERVER['REQUEST_URI'];//含参数的路径
}

function showhead($title="")
{
    if($title) $title=" - $title";
    print <<< end_of_print
        <html>
        <head>
        <title>YOKA - Jimmy Tool Kit - $title</title>
        <META HTTP-EQUIV="Expires" CONTENT="0">
        <META HTTP-EQUIV="Last-Modified" CONTENT="0">
        <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache, must-revalidate">
        <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
        <meta http-equiv="Content-Type" content="text/html; charset=GBK">
        <link rel="stylesheet" href="func.css" type="text/css">
        <script language=javascript src="func.js"></script>
        </head>
        <body bgcolor="#FFFFFF" text="#000000" topmargin="10" >
        <p><a href=./>返回首页</a></p>
end_of_print;
}
function showhelp($showdoc,$helpdoc,$markflag='')
{
    $showdoc=text2html($showdoc);
    $helpdoc=text2string(text2html($helpdoc));
    $helpdoc=addslashes($helpdoc);
    if($markflag==1)$markflag='※';
    if (trim($helpdoc)=='') echo $showdoc;
    else print <<< end_of_print
    <span onmouseover="show_help('$helpdoc',event);" onmouseout="show_help('',event);">$showdoc $markflag</span>
end_of_print;
}
function text2html($mytext)
{
    $mystring=htmlspecialchars($mytext, ENT_COMPAT, 'gb2312');
    $mystring=str_replace(" ","&nbsp;",$mystring);
    $mystring=nl2br($mystring);
    return $mystring;
}
function text2string($mytext)
{
    $mystring=$mytext;
    $s_return=chr(13).chr(10);
    $mystring=str_replace($s_return,"\\n",$mystring);
    return $mystring;
}
?>
