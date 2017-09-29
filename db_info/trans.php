<?php
/**
 * 辅助转换工具
 * 
 */
include("db_info.ini.php");

switch($_REQUEST['do']){
	case 'toArray': //描述转数组。适用： 0：文本回复 1：图文回复 2：音乐回复 3：视频回复
		$code = $_REQUEST['code'];
		$code = str_replace('：',':',$code);
		$code = str_replace('　',' ',$code);
		$code = str_replace('  ',' ',$code);
		preg_match_all('/(-?[0-9]+)\s?(\:|=>)\s?([^\s]+)/', $code, $reg, PREG_SET_ORDER);
		foreach($reg as $v){
			$re[$v[1]] = $v[3];
		}
		$re = var_export($re, true);
		break;
	default:
		$re = '错误：没有转换指令';
		break;
}
showhead();
print <<< end_of_print
<pre>
{$re}
</pre>
end_of_print;
echo str_replace(',)',']',str_replace('array(','[',str_replace(array(" ","　","\t","\n","\r"), '', $re)));
