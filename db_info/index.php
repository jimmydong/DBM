<?
/**
 * 公共文件：数据库结构说明
 * DB info version 2
 * 
 * by jimmy.dong@gmail.com
 * 
 *
 * DOC 表的结构
 * CREATE TABLE `_system__doc` (
 *   `table` varchar(60) NOT NULL default '',
 *   `field` varchar(60) NOT NULL default '',
 *   `content` varchar(200) NOT NULL default '',
 *   `remark` text NOT NULL,
 *   PRIMARY KEY  (`table`,`field`)
 * ) TYPE=MyISAM COMMENT='数据表说明文档';
 *
 * DOC 表table字段保留字含义
 *   _all - 全局缺省值
 *
 * DOC 表field字段保留字含义
 *   _remark - 对表的特殊说明 content=简要说明 remark=详细说明
 *   _log - 对表的修改说明 content=修改说明 remark=历史纪录
 *
 *
 */
//环境配置
define('IS_TEST', true);
session_start();

//自动加载
$autoPath = dirname(__FILE__);$path = get_include_path();
if (strpos($path.PATH_SEPARATOR, $autoPath.PATH_SEPARATOR) === false) set_include_path($path.PATH_SEPARATOR.$autoPath);
spl_autoload_extensions('.class.php');
spl_autoload_register('spl_autoload');

//YEPF(如果不需要DEBUG，可以不加载YEPF)
if(!defined('YEPF_PATH')){
	if($_SERVER['YEPF_PATH_3']) define('YEPF_PATH',$_SERVER['YEPF_PATH_3']);
	else{
		if(file_exists('/WORK/HTML/YEPF3')) define('YEPF_PATH', '/WORK/HTML/YEPF3');
		elseif(file_exists(dirname(__FILE__) . '/YEPF3')) define('YEPF_PATH', dirname(__FILE__) . '/YEPF3');
		elseif(file_exists(dirname(__FILE__) . '/../YEPF3')) define('YEPF_PATH', dirname(__FILE__) . '/../YEPF3');
		else die("Can't find YEPF3");
	}
}
include YEPF_PATH . '/global.inc.php';

//最小MVC
$request = lib\Request::getInstance();
try
{
	$mvc = new lib\App($request->_c, $request->_a);  
	$mvc->run();
}
catch (\Exception $err)
{
    header("Content-type:text/html;charset=utf-8");
	var_dump($err);	
}


/*-------------------------------------- funcitons -------------------------------------------*/
function showhead($title="", $charset="GBK")
{
	if($title) $title="$title";
	$re = <<< end_of_print
<!DOCTYPE html>
<html>
<head>
	<title>JDTK - $title</title>
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Last-Modified" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache, must-revalidate">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<meta http-equiv="Content-Type" content="text/html; charset={$charset}">
	<link rel="stylesheet" href="func.css" type="text/css">
	<script language=javascript src="func.js"></script>
</head>
<body bgcolor="#FFFFFF" text="#000000" topmargin="10" >
	<p><a href=./>返回首页</a></p>
end_of_print;

	echo $re;
}
function showhelp($showdoc,$helpdoc,$markflag='')
{
	$showdoc=text2html($showdoc);
	$helpdoc=text2string(text2html($helpdoc));
	$helpdoc=addslashes($helpdoc);
	if($markflag==1)$markflag='※';
	if (trim($helpdoc)=='') return $showdoc;
	else return "<span onmouseover=\"show_help('$helpdoc',event);\" onmouseout=\"show_help('',event);\">$showdoc $markflag</span>";
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

/**
 * 调试函数，用以取代var_dump。
 * 不同于var_dump：多个参数间使用 , 分隔，而不是空格
 */
function var_dump2(){
	$varArray = func_get_args();
	foreach($varArray as $var) var_dump($var);
	$t = debug_backtrace(1);
	$caller = $t[0]['file'].':'.$t[0]['line'];
	echo " -- from $caller --";
	exit;
}

function gbk2utf8($in){
	if(is_string($in)){
		$re = iconv('gbk','utf-8',$in);
	}elseif(is_array($in)){
		foreach($in as $k=>$v){
			$k = iconv('gbk','utf-8',$k);
			$re[$k] = gbk2utf8($v);
		}
	}else{
		$re = null;
	}
	return $re;
}
function utf82gbk($in){
	if(is_string($in)){
		$re = iconv('utf-8','gbk',$in);
	}elseif(is_array($in)){
		foreach($in as $k=>$v){
			$k = iconv('utf-8','gbk',$k);
			$re[$k] = utf82gbk($v);
		}
	}else{
		$re = null;
	}
	return $re;
}