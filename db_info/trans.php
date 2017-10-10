<?php
/**
 * 辅助转换工具
 * 
 */
include("db_info.ini.php");
include("db_mysql.inc.php");
if($serverid){
	if(!$cfg['Servers'][$serverid])die("Can't find DB!");
	else $serverinfo=$cfg['Servers'][$serverid];
	if($database==''){$step='';$database='mysql';}
	$tmp_str = "
	class DB_glb extends DB_Sql {
	var \$Host     = '{$serverinfo[host]}:{$serverinfo[port]}';
	var \$Database = '{$database}';
	var \$User     = '{$serverinfo[user]}';
	var \$Password = '{$serverinfo[password]}';
	}
	";
	eval($tmp_str);
	$db = new DB_glb;
}
switch($_REQUEST['do']){
	case 'toArray': //描述转数组。适用： 0：文本回复 1：图文回复 2：音乐回复 3：视频回复
		$code = $_REQUEST['code'];
		$out = trans($code);
		break;
	case 'table': //整个table生成 define_slim (用于配合 BaseModel::_slim)
		$rows = $db->fetchAll("select * from _system__doc where `table` = '{$table}'");
		foreach($rows as $row){
			$info[strtolower($row['field'])] = $row;
		}
		$rows = $db->fetchAll("select * from _system__doc where `table` = '_all'");
		foreach($rows as $row){
			$all[strtolower($row['field'])] = $row;
		}
		$rows = $db->fetchAll("DESCRIBE `{$table}`");
		foreach($rows as $row){
			$field = strtolower($row['Field']);
			if($info[$field]['content']){
				$re[$field] = $info[$field];
			}elseif($all[$field]['content']){
				$re[$field] = $all[$field];
			}else{
				$re[$field] = false;
			}
		}
		foreach($re as $k=>$v){
			if(in_array($k, ['create_time','update_time','remark','del_flag'])) continue;
			if(! $v) $out[] = "'{$k}' => ['title'=>'未标注']";
			else{
				//需要转义的字段
				$type = '';
				if(preg_match('/_id|province|city|json/', $k)){
					$type = ", 'type'=>1";
				}
				//自动映射的字段
				$map = '';
				if(preg_match('/(0|1)[ ]?(:|：)/', $v['remark']) || preg_match('(0|1)[ ]?=>', $v['remark'])){
					$tmp = trans($v['remark']);
					if($tmp){
						$map = ", 'map'=>" . $tmp;
					}
				}
				$out[] = "'{$k}' => ['title'=>'{$v['content']}'{$map}{$type}];";
			}
		}
		$out = "public static \$define_slim = array(\n	" . implode("\n	", $out) . "\n);";
		break;
	default:
		$out = '错误：没有转换指令';
		break;
}
showhead();
print <<< end_of_print
<pre>
{$out}
</pre>
end_of_print;

/*---------------------------------------------functions----------------------------------------------*/
function trans($code){
	$code = str_replace('：',':',$code);
	$code = str_replace(',',' ',$code);
	$code = str_replace('，',' ',$code);
	$code = str_replace('　',' ',$code);
	$code = str_replace('  ',' ',$code);
	$code = str_replace("'",'',$code);
	$code = str_replace('"','',$code);
	preg_match_all('/(-?[0-9]+)\s?(\:|=>)\s?([^\s]+)/', $code, $reg, PREG_SET_ORDER);
	foreach($reg as $v){
		$re[$v[1]] = $v[3];
	}
	$re = var_export($re, true);
	$re = str_replace(',)',']',str_replace('array(','[',str_replace(array(" ","　","\t","\n","\r"), '', $re)));
	return $re;
}
