<?
/**
 * 公共文件：数据库结构说明
 *
 * DOC 表的结构
 * CREATE TABLE `_system__doc` (
 *   `table` varchar(60) NOT NULL default '',
 *   `field` varchar(60) NOT NULL default '',
 *   `content` varchar(200) NOT NULL default '',
 *   `remark` text NOT NULL,
 *   PRIMARY KEY  (`table`,`field`)
 * ) TYPE=MyISAM COMMENT='数据表说明文档';
 *
 * DOC 表table字段保留字含义
 *   _all - 全局缺省值
 *
 * DOC 表field字段保留字含义
 *   _remark - 对表的特殊说明 content=简要说明 remark=详细说明
 *   _log - 对表的修改说明 content=修改说明 remark=历史纪录
 *
 * @author jimmy
 * @packet JDK
 * @version 2004.09.15
 * @param $q $q2 注意：必须提供两个相同的数据库连接 | $action | $modify=1 编辑模式
 */

if($action=='updatecomment')
{
    if ($table=='') ErrExit("Error: 传入参数错误!");
    print <<< end_of_print
    <form method=post action=$currenturl name=form1>
    <input type=hidden name=serverid value='$serverid'>
    <input type=hidden name=database value='$database'>
    <input type=hidden name=table value='$table'>
    <input type=hidden name=action value='updatecommentdone'>
    <p>为 $table 表修改注释
    <p>新注释：<input type=text size=60 name=comment value='$comment'> （少于60字节）
    <p><input type=submit value=确定>
    </form>
end_of_print;
exit;
}

if($action=='updatecommentdone')
{
    if ($table=='') ErrExit("Error: 传入参数错误!");
    $comment=addslashes($comment);
    if ($q->query("ALTER TABLE `$table` COMMENT = '$comment'"))
    {
        redirect("$currenturl?serverid=$serverid&database=$database&modify=1#$table", "系统信息：信息更新成功！", 2);
        exit;
    }
    else
        ErrExit("数据库操作失败！");
}

if($action=='addfield')
{
    if ($table=='' || $field=='') ErrExit("Error: 传入参数错误!");
    print <<< end_of_print
    <form method=post action=$currenturl name=form1>
    <input type=hidden name=serverid value='$serverid'>
    <input type=hidden name=database value='$database'>
    <input type=hidden name=table value='$table'>
    <input type=hidden name=field value='$field'>
    <input type=hidden name=action value='addfielddone'>
    <p>为 $table 表 $field 项添加说明
    <p>设定为缺省值：<input type=checkbox name=all value=1>是
    <p>一般说明：<input type=text size=60 name=content value=''> （少于200字节）
    <p>详细说明：<textarea rows=6 cols=60 name=remark></textarea>
    <p><input type=submit value=确定>
    </form>
end_of_print;
exit;
}

if($action=='addfielddone')
{
    if ($table=='' || $field=='') ErrExit("Error: 传入参数错误!");
    $content=addslashes($content);
    $remark=addslashes($remark);
    $tablename=$table;
    if($all==1)$table="_all";
    if ($q->query("REPLACE _system__doc SET `table`='$table', `field`='$field', `content`='$content', `remark`='$remark'"))
    {
        redirect("$currenturl?modify=1&serverid=$serverid&database=$database&modify=1#$tablename", "系统信息：信息更新成功！", 2);
        exit;
    }
    else
        ErrExit("数据库操作失败！");
}
// add by bandry, 2017-08-14 批量修改
if($action=='addfielddoneBatch')
{
    $data = $_POST['data'];
    if ($table=='' || empty($data)) ErrExit("Error: 传入参数错误!");
    $tablename = $table;
    foreach ($data as $field => $info) {
        $content=addslashes($info['des']);
        $remark=addslashes($info['remark']);
        if($all == 1) $table = "_all";
        if ($q->query("REPLACE _system__doc SET `table`='$table', `field`='$field', `content`='$content', `remark`='$remark'")) {
        } else {
            ErrExit("数据库操作失败！");
        }
    }
    redirect("$currenturl?modify=1&serverid=$serverid&database=$database&modify=1#$tablename", "系统信息：信息更新成功！", 2);
}

if($action=='updatefield')
{
    if (!$fieldinfo=$q->query_first("SELECT * FROM `_system__doc` WHERE `table`='$table' AND `field`='$field'"))
    {
        if (!$fieldinfo=$q->query_first("SELECT * FROM `_system__doc` WHERE `table`='_all' AND `field`='$field'"))
            ErrExit("Error: 传入参数错误!");
        $notice="<font color=red>注意：当前项目未定义，显示为缺省值</font>";
    }
    print <<< end_of_print
    <form method=post action=$currenturl name=form1>
    <input type=hidden name=serverid value='$serverid'>
    <input type=hidden name=database value='$database'>
    <input type=hidden name=table value='$table'>
    <input type=hidden name=field value='$field'>
    <input type=hidden name=action value='addfielddone'>
    <p>为 $table 表 $field 项添加说明 $notice
    <p>设定为缺省值：<input type=checkbox name=all value=1>是
    <p>一般说明：<input type=text size=60 name=content value='{$fieldinfo[content]}'> （少于200字节）
    <p>详细说明：<textarea rows=6 cols=60 name=remark>{$fieldinfo[remark]}</textarea>
    <p><input type=submit value=确定>
    </form>
end_of_print;
exit;
}
if($action=='createdoc')
{
    $q->query("CREATE TABLE `_system__doc` ( `table` varchar(60) NOT NULL default '', `field` varchar(60) NOT NULL default '', `content` varchar(200) NOT NULL default '', `remark` text NOT NULL, PRIMARY KEY (`table`,`field`) )");
    echo "<font color=red><b>已添加文档结构。请勿删除_system__doc表！</b></font><hr>\n";
    $modify=1;
}
if($action=='addlog')
{
    if ($table=='') ErrExit("Error: 传入参数错误!");
    print <<< end_of_print
    <form method=post action=$currenturl name=form1>
    <input type=hidden name=serverid value='$serverid'>
    <input type=hidden name=database value='$database'>
    <input type=hidden name=table value='$table'>
    <input type=hidden name=field value='_log'>
    <input type=hidden name=action value='addlogdone'>
    <p>为 $table 表添加修改LOG
    <p>LOG内容：<input type=text size=60 name=content value=''> （少于200字节）
    <p><input type=submit value=确定>
    </form>
end_of_print;
exit;
}

if($action=='addlogdone')
{
    if ($table=='' || $field!='_log') ErrExit("Error: 传入参数错误!");
    $loginfo=$q->query_first("SELECT * FROM `_system__doc` WHERE `table`='$table' AND `field`='_log'");
    $content=date("Y-m-d H:i:s ").addslashes($content);
    $remark=$loginfo['content']."<br>\n".$loginfo['remark'];
    $sql = "REPLACE _system__doc SET `table`='$table', `field`='$field', `content`='$content', `remark`='$remark'";
    if ($q->query($sql))
    {
        redirect("$currenturl?serverid=$serverid&database=$database&modify=1#$table", "系统信息：LOG录入操作成功！", 2);
        exit;
    }
    else
        ErrExit("数据库操作失败！");
}

if($action=='addremark')
{
    if ($table=='') ErrExit("Error: 传入参数错误!");
    print <<< end_of_print
    <form method=post action=$currenturl name=form1>
    <input type=hidden name=serverid value='$serverid'>
    <input type=hidden name=database value='$database'>
    <input type=hidden name=table value='$table'>
    <input type=hidden name=field value='_remark'>
    <input type=hidden name=action value='addfielddone'>
    <p>为 $table 表添加特别说明
    <p>一般说明：<input type=text size=60 name=content value=''> （少于200字节）
    <p>详细说明：<textarea rows=6 cols=60 name=remark></textarea>
    <p><input type=submit value=确定>
    </form>
end_of_print;
exit;
}
if($action=='updateremark')
{
    if (!$fieldinfo=$q->query_first("SELECT * FROM `_system__doc` WHERE `table`='$table' AND `field`='_remark'")) ErrExit("Error: 传入参数错误!");
    print <<< end_of_print
    <form method=post action=$currenturl name=form1>
    <input type=hidden name=serverid value='$serverid'>
    <input type=hidden name=database value='$database'>
    <input type=hidden name=table value='$table'>
    <input type=hidden name=field value='_remark'>
    <input type=hidden name=action value='addfielddone'>
    <p>为 $table 表添加特别说明
    <p>一般说明：<input type=text size=60 name=content value='{$fieldinfo[content]}'> （少于200字节）
    <p>详细说明：<textarea rows=6 cols=60 name=remark>{$fieldinfo[remark]}</textarea>
    <p><input type=submit value=确定>
    </form>
end_of_print;
exit;
}

/*
 *-------------------------------------------缺省操作：显示数据库结构信息----------------------------------
 */
//获取数据库结构信息
$no_doc=true;
$q->query("SHOW TABLE STATUS");
while($q->next_record())
{
    $table=$q->Record;
    if($table[Name]!="_system__doc")
    {
        $db_index[]=$table[Name];
        $comment = $table[Comment]; if(preg_match('/InnoDB/',$comment))$comment = '';
        $db_comment[$table[Name]] = $comment;
        $db_rows[$table[Name]] = $table[Rows];
        //$q2->query("SELECT * FROM {$table[Name]} LIMIT 1");
        //$db_info[$table[Name]] = $q2->get_fields();
        $db_info[$table[Name]] = $q2->get_fullfields($table[Name]);
    }
    else
    {
        $q2->query("SELECT * FROM {$table[Name]}");
        $tmp_info = $q2->get_fields();
        if ($tmp_info[0]['name']=='table' && $tmp_info[1]['name']=='field') $no_doc=false;
    }
}
if(count($db_index)==0) ErrExit("当前库中没有可显示的表结构");

echo "<a name=top></a><div class=boxh>表索引</div>\n<div class=boxb>\n<table border=0>\n";
asort($db_index);
foreach($db_index as $tablename)
{
    echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;· <a href=#$tablename>$tablename</a> </td><td> <i>...$db_comment[$tablename]</i></td></tr>\n";
}
echo "</table>\n</div>\n";

//读出DOC表内容
if(!$no_doc)
{
    $q->query("SELECT * FROM _system__doc");
    while($q->next_record())
    {
        if ($q->f('table')=='' || $q->f('field')=='') continue;
        $doc_content[$q->f('table')][$q->f('field')] = $q->f('content');
        $doc_remark[$q->f('table')][$q->f('field')] = $q->f('remark');
    }
}
else
{
    if ($modify==1)
    {
        echo "<div class=boxb>Warrning: <p>未找到内容介绍文档结构，编辑模式禁止！<a href={$currenturl}?serverid=$serverid&database=$database&action=createdoc>创建文档结构</a></div>";
        $modify = 0;
    }
}

foreach($db_info as $table_name=>$table_info)
{
    ?>
    <a name='<?=$table_name?>'>
    <h3><a href=#top>↑</a>数据表【<?=$table_name?>】 - <?=$db_comment[$table_name]?><?if($modify==1)echo "<a href={$currenturl}?serverid=$serverid&database=$database&action=updatecomment&table={$table_name}&comment=".urlencode($db_comment[$table_name]).">&raquo;</a>"?>(数据量:<?=$db_rows[$table_name]?>)</h3>
    <a href="javascript:;" onclick="$('#t_<?=$table_name?>').tableExport({type:'csv',escape:'false'})">导出CSV</a>
    <div class=boxc>
    <?
    showhelp($doc_content[$table_name][_log],$doc_content[$table_name][_log]);
    if($modify==1) //编辑模式
    {
        echo "<a href={$currenturl}?serverid=$serverid&database=$database&action=addlog&table={$table_name}>newlog&raquo;</a><br>";
    }

    if($doc_content[$table_name][_remark])
    {
        showhelp($doc_content[$table_name][_remark],$doc_remark[$table_name][_remark]);
        if($modify==1)
        {
            echo "<a href={$currenturl}?serverid=$serverid&database=$database&action=updateremark&table={$table_name}>newremark&raquo;</a>";
        }
    }
    else
    {
        if($modify==1)
        {
            echo "<a href={$currenturl}?serverid=$serverid&database=$database&action=addremark&table={$table_name}>addremark&raquo;</a>";
        }
    }
    ?>
    </div>
    <table id="t_<?=$table_name?>" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="80%" align=center>
      <tr>
        <td bgcolor="#868786" width=200><b><font color=white>字段名</font></b></td>
        <td bgcolor="#868786" width=150><b><font color=white>类型</font></b></td>
        <td bgcolor="#868786" width=240><b><font color=white>说明</font></b></td>
        <td bgcolor="#868786" width=*><b><font color=white>详细</font></b></td>
      </tr>
      <form action="" method="post">
        <input type="hidden" name="action" value="addfielddoneBatch" />
        <input type="hidden" name="table" value="<?php echo $table_name ?>" />
        <input type=hidden name="serverid" value='<?php echo $serverid ?>' />
        <input type=hidden name="database" value='<?php echo $database ?>' />
        <?
        foreach($table_info as $key=>$val)
        {
            ?>
      <tr onmouseover="this.style.backgroundColor='#EDEDFD';" onmouseout="this.style.backgroundColor='#FFFFFF';">
        <td valign=top><font color=#666666><b><?showhelp($val[name],"<b>".$val[type].implode(' ',$val[args])."</b><br>null:".$val['null']."<br>key:<b>".$val[key]."</b><br>default:<b>".$val['default']."</b><br>".$val[extra]);?></b></font></td>
        <td valign=top><?=$val[type]?>(<?=$val[len]?>)</td>
        <td valign=middle><?
                $showdoc=$doc_content[$table_name][$val[name]];
                if($showdoc=='') $showdoc=$doc_content[_all][$val[name]];
                $helpdoc=$doc_remark[$table_name][$val[name]]?:$doc_remark[_all][$val[name]];
                $height = 22 * count(explode("\n", $helpdoc)) + 22;
                if($modify==1) //编辑模式
                {
                    if($showdoc=='')
                    {
                        echo "<a href={$currenturl}?serverid=$serverid&database=$database&action=addfield&table={$table_name}&field={$val[name]}>&raquo;</a>";
                    }
                    else
                    {
                        echo "<a href={$currenturl}?serverid=$serverid&database=$database&action=updatefield&table={$table_name}&field={$val[name]}>&raquo;</a>";
                    }
             		echo <<<TABLETD
                <input type="text" name="data[{$val[name]}][des]" value="{$showdoc}" style="width:200px;margin:3px auto;" />
TABLETD;
                }else{
                	showhelp($showdoc,$helpdoc,1);
             	}	
             	
             ?>
		</td>
			<?php 
			if($modify == 1){
			?>
		<td><textarea name="data[<?php echo $val[name] ?>][remark]" style="width:80%;height:<?php echo $height ?>;margin:5px;"><?=$helpdoc?></textarea></td>
	  </tr>
      <tr><td></td><td></td><td colspan="2"><input type="submit" value="保存说明" /></td></tr>
            <?php
			}else{
				echo "<td>$helpdoc</textarea></td></tr>";
            }
        }
        ?>
    </form>
    </table>
    <br>
    <br>
    <?
}

/*----------------- function define -------------------*/
function redirect($backurl,$message,$delay)
{
$delay=20;
    echo "<meta http-equiv='content-type' content='text/html; charset=gb2312'>";
    echo "<Meta HTTP-EQUIV='refresh' content='$delay;url=$backurl'>";
    echo "<p><br><table border=1 cellpadding=0 cellspacing=0 style='border-collapse: collapse; bordercolor=#444444' width=80% align=center><tr align=center><td>$message</td></tr>\n";
    echo "<tr align=center><td>如果没有自动返回，请<a href='$backurl'>点击这里</a></td></tr></table>\n";
    echo "<script language=javascript>\nfunction redirect()\n{window.location='$backurl';}\nvar timer = setTimeout('redirect()',".intval($delay*100).");\ntimer;</script>";
    exit;
}
?>
