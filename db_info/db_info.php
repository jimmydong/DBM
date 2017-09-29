<?
/**
 * JD tools 
 * 全局数据库设计参考
 *
 * @author jimmy 
 * @version 2004.09.15
 * @param
 * @update 2009.07.08
 */
include("db_info.ini.php");
include("db_mysql.inc.php");
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

$q=new DB_glb;
showhead("数据库内容");
if($modify){
	$do_modify=0;
}else {
	$do_modify=1;
}
if($do_modify){ $state = '浏览状态';}
else $state = '编辑状态';
print <<< end_of_print
<script src="http://mcdn.yishengdaojia.cn/media/js/jquery-2.1.3.min.js"></script>
<script src="http://mcdn.yishengdaojia.cn/media/tableExport/myexport.js"></script>
<script src="http://m.iyishengyuan.com/media/tableExport/mybase64.js"></script>
<h1>#{$serverid} [{$serverinfo[host]}:{$serverinfo[port]} {$serverinfo[ext_name]}] 数据库：{$database}</h1>
<hr size=1>
<p><a href=db_info.php?modify={$do_modify}&serverid={$serverid}&database={$database}&step={$step}>切换编辑模式</a>  当前状态：[{$state}]
<hr size=1>
end_of_print;

$q=new DB_glb;
$q2=new DB_glb;
include("db_info.inc.php");
?>
<hr size=1>
<p><a href=db_info.php?modify=<?=$do_modify?>&serverid=<?=$serverid?>&database=<?=$database?>&step=<?=$step?>>切换编辑模式</a>
<p>
<p>

[ALTER TABLE `test` ADD `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;]
<br/>
<form method=post action='trans.php' target=_blank>
<input type=hidden name='do' value='toArray' />
转array： <input type=text size=60 name='code' /><input type=submit value='提交'/>
</form>
