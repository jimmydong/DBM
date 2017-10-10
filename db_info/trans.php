<?php
/**
 * ����ת������
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
	case 'toArray': //����ת���顣���ã� 0���ı��ظ� 1��ͼ�Ļظ� 2�����ֻظ� 3����Ƶ�ظ�
		$code = $_REQUEST['code'];
		$out = trans($code);
		break;
	case 'table': //����table���� define_slim (������� BaseModel::_slim)
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
			if(! $v) $out[] = "'{$k}' => ['title'=>'δ��ע']";
			else{
				//��Ҫת����ֶ�
				$type = '';
				if(preg_match('/_id|province|city|json/', $k)){
					$type = ", 'type'=>1";
				}
				//�Զ�ӳ����ֶ�
				$map = '';
				if(preg_match('/(0|1)[ ]?(:|��)/', $v['remark']) || preg_match('(0|1)[ ]?=>', $v['remark'])){
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
		$out = '����û��ת��ָ��';
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
	$code = str_replace('��',':',$code);
	$code = str_replace(',',' ',$code);
	$code = str_replace('��',' ',$code);
	$code = str_replace('��',' ',$code);
	$code = str_replace('  ',' ',$code);
	$code = str_replace("'",'',$code);
	$code = str_replace('"','',$code);
	preg_match_all('/(-?[0-9]+)\s?(\:|=>)\s?([^\s]+)/', $code, $reg, PREG_SET_ORDER);
	foreach($reg as $v){
		$re[$v[1]] = $v[3];
	}
	$re = var_export($re, true);
	$re = str_replace(',)',']',str_replace('array(','[',str_replace(array(" ","��","\t","\n","\r"), '', $re)));
	return $re;
}
