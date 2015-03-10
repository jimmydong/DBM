<?
/**
 * DB list by jimmy
 *
 */
include("db_info.ini.php");
//var_dump($cfg['Servers']);
print <<< end_of_print
<html>
<head>
<meta http-equiv="Content-Language" content="zh-cn">
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>CLUB SOHU - DB INFO SYSTEM</title>
</head>
<body>
<p>DB文档系统</p>
end_of_print;
?><?
if(!$step || !$serverid){
	echo "第一步： 选择数据库服务器：<br><form name=form1 id=form1 method=post action=index.php><select name=serverid><option value=''>--select server--</option>";
	foreach($cfg['Servers'] as $serverid=>$serverinfo){
	if($serverinfo['echo']){
	if($serverid!=1)echo $serverinfo['echo'];
	}else{
	echo "<option value={$serverid} style='background-color: {$serverinfo[ext_color]};'>";
	echo "{$serverid} {$serverinfo[host]}:{$serverinfo[port]} {$serverinfo[ext_name]}";
	echo "</option>\n";
	}
	}
	echo "<input type=hidden name=step value=1><input type=submit value=NEXT></form>";
}else{
	include("db_mysql.inc.php");
	if(!$cfg['Servers'][$serverid])die("Can't find DB!");
	else $serverinfo=$cfg['Servers'][$serverid];
	$tmp_str = "
	class DB_glb extends DB_Sql {
	  var \$Host     = '{$serverinfo[host]}:{$serverinfo[port]}';
	  var \$Database = 'mysql';
	  var \$User     = '{$serverinfo[user]}';
	  var \$Password = '{$serverinfo[password]}';
	}
	";
	eval($tmp_str);
	
	$q=new DB_glb;
	echo "第二步：选择需要操作的数据库：<br><form name=form1 id=form1 method=post action=db_info.php>";
	echo "<input type=hidden name=step value=1>";
	echo "<input type=hidden name=serverid value=$serverid>";
	echo "<select name=database><option value=''>--select database--</option>";
	$q->query("SHOW DATABASES");
	while($record=$q->next_record()){
	if($record[Database]=='information_schema' || $record[Database]=='mysql')continue;
	echo "<option value='{$record[Database]}'>{$record[Database]}</option>";
	}
	echo "<input type=submit value=NEXT></form>";
}
