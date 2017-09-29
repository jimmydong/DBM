<?php
/**
 * 辅助转换工具
 * 
 */
include("db_info.ini.php");

$re = ['success'=>false, 'msg'=>'error'];
switch($_REQUEST['todo']){
	case 'toArray': //描述转数组。适用： 0：文本回复 1：图文回复 2：音乐回复 3：视频回复
		$code = $_REQUEST['code'];
		$code = str_replace('：',':',$code);
		preg_match_all('/([0-9]+)\:([^\s]+)/', $code, $reg, PREG_SET_ORDER);
		foreach($reg as $v){
			$re[$v[1]] = $v[2];
		}
		$re = ['success'=>true, 'data'=>var_export($re, true)];
		break;
	default:
		$re = ['success'=>false, 'msg'=>'错误：没有转换指令'];
		break;
}

echo json_encode($re, JSON_UNESCAPED_UNICODE);