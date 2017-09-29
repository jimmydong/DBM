<?php
/**
 * ����ת������
 * 
 */
include("db_info.ini.php");

$re = ['success'=>false, 'msg'=>'error'];
switch($_REQUEST['todo']){
	case 'toArray': //����ת���顣���ã� 0���ı��ظ� 1��ͼ�Ļظ� 2�����ֻظ� 3����Ƶ�ظ�
		$code = $_REQUEST['code'];
		$code = str_replace('��',':',$code);
		preg_match_all('/([0-9]+)\:([^\s]+)/', $code, $reg, PREG_SET_ORDER);
		foreach($reg as $v){
			$re[$v[1]] = $v[2];
		}
		$re = ['success'=>true, 'data'=>var_export($re, true)];
		break;
	default:
		$re = ['success'=>false, 'msg'=>'����û��ת��ָ��'];
		break;
}

echo json_encode($re, JSON_UNESCAPED_UNICODE);