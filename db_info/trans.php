<?php
/**
 * ����ת������
 * 
 */
include("db_info.ini.php");

switch($_REQUEST['do']){
	case 'toArray': //����ת���顣���ã� 0���ı��ظ� 1��ͼ�Ļظ� 2�����ֻظ� 3����Ƶ�ظ�
		$code = $_REQUEST['code'];
		$code = str_replace('��',':',$code);
		preg_match_all('/([0-9]+)\:([^\s]+)/', $code, $reg, PREG_SET_ORDER);
		foreach($reg as $v){
			$re[$v[1]] = $v[2];
		}
		$re = var_export($re, true);
		break;
	default:
		$re = '����û��ת��ָ��';
		break;
}

print <<< end_of_print
<pre>
{$re}
</pre>

end_of_print;
