<?
/**
 * JD tools 
 * ȫ�����ݿ���Ʋο�
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
showhead("���ݿ�����");
if($modify)$do_modify=0;
else $do_modify=1;
print <<< end_of_print
<h1>#{$serverid} [{$serverinfo[host]}:{$serverinfo[port]} {$serverinfo[ext_name]}] ���ݿ⣺{$database}</h1>
<hr size=1>
<p><a href=db_info.php?modify={$do_modify}&serverid={$serverid}&database={$database}&step={$step}>�л��༭ģʽ</a>
<hr size=1>
end_of_print;

$q=new DB_glb;
$q2=new DB_glb;
include("db_info.inc.php");
?>
<hr size=1>
<p><a href=db_info.php?modify=<?=$do_modify?>&serverid=<?=$serverid?>&database=<?=$database?>&step=<?=$step?>>�л��༭ģʽ</a>
<p>
<p>


