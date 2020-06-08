<html>
<head>
<meta http-equiv="Content-Language" content="zh-cn">
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>CLUB SOHU - DB INFO SYSTEM</title>
</head>
<body>
<p>DB文档系统</p>
<?
if(!$response->step){
	echo "第一步： 选择数据库服务器：<br><form name=form1 id=form1 method=post action=index.php><select name=serverid><option value=''>--select server--</option>";
	foreach(self::$cfg['Servers'] as $serverid=>$serverinfo){
	if($serverinfo['echo']){
		if($serverid!=1){
			echo $serverinfo['echo'];
		}
		}else{
			echo "<option value={$serverid} style='background-color: {$serverinfo[ext_color]};'>";
			echo "{$serverid} {$serverinfo[host]}:{$serverinfo[port]} {$serverinfo[verbose]}";
			echo "</option>\n";
		}
	}
	echo "<input type=hidden name=step value=1><input type=submit value=NEXT></form>";
}else{
	print <<< end_of_print
	第二步：选择需要操作的数据库：<br><form name=form1 id=form1 method=post action="index.php?_a=doc">
	<input type=hidden name=step value=1>
	<input type=hidden name=serverid value={$response->serverid}>
	<select name=database><option value=''>--select database--</option>
	{$response->re}
	<input type=submit value=NEXT></form>
end_of_print;
}
