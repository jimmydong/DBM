<?php
showhead("数据库内容");
print <<< end_of_print
<script src="http://mcdn.yishengdaojia.cn/media/js/jquery-2.1.3.min.js"></script>
<script src="http://mcdn.yishengdaojia.cn/media/tableExport/myexport.js"></script>
<script src="http://m.iyishengyuan.com/media/tableExport/mybase64.js"></script>
<script src="http://acdn.yishengdaojia.cn/media/layer/layer.js"></script>
<h1>$response->h1</h1>
<hr size=1>
<p>使用说明： 双击“说明”进行修改，双击“详细”生成映射</p>
<hr size=1>
{$response->re}
end_of_print;

$db_comment = $response->db_comment;
$doc_content = $response->doc_content;
$doc_remark = $response->doc_remark;
foreach($response->db_info as $table_name=>$table_info)
{
    $log = showhelp($doc_content[$table_name][_log],$doc_content[$table_name][_log]);
    $log .= "<a href=./php?_a=log&table={$table_name}>newlog&raquo;</a><br>";

    if($doc_content[$table_name][_remark])
    {
        $remark = showhelp($doc_content[$table_name][_remark],$doc_remark[$table_name][_remark]);
        $remark .= "<a href=./php?_a=remark&table={$table_name}>newremark&raquo;</a>";
    }
    else
    {
    	$remark = '';
        $remark .= "<a href=./php?_a=remark&table={$table_name}>addremark&raquo;</a>";
    }
	if($db_comment[$table_name] == '')$db_comment[$table_name] = '&gt;&gt;';
	print <<< end_of_print
    <a name='{$table_name}'>
    <h3><a href=#top>↑</a>数据表【{$table_name}】 - <span class="table_comment" data="{$table_name}">{$db_comment[$table_name]}</span>(数据量:{$response->db_rows[$table_name]})</h3>
    <a href="javascript:;" onclick="$('#t_{$table_name}').tableExport({type:'csv',escape:'false'})">导出CSV</a> | 
    <a href="./?_c=trans&_a=table&table={$table_name}" target=_blank>生成定义</a>
    <div class=boxc>
    {$log} {$remark}
    </div>
    <table id="t_{$table_name}" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="80%" align=center>
      <tr>
        <td bgcolor="#868786" width=200><b><font color=white>字段名</font></b></td>
        <td bgcolor="#868786" width=150><b><font color=white>类型</font></b></td>
        <td bgcolor="#868786" width=240><b><font color=white>说明</font></b></td>
        <td bgcolor="#868786" width=*><b><font color=white>详细</font></b></td>
      </tr>
end_of_print;
        foreach($table_info as $key=>$val)
        {
        	$showdoc=$doc_content[$table_name][$val[name]];
        	if($showdoc=='' && $doc_content[_all][$val[name]]){
        		//使用通用注释
        		$showdoc= '* ' . $doc_content[_all][$val[name]];
        		$use_all = 1;
        	}else{
        		$use_all = 0;
        	}
        	$helpdoc=$doc_remark[$table_name][$val[name]]?:$doc_remark[_all][$val[name]];
        	$height = 22 * count(explode("\n", $helpdoc)) + 22;
       		$type = showhelp("{$val[type]}({$val[len]})","<b>".$val[type].implode(' ',$val[args])."</b><br>null:".$val['null']."<br>key:<b>".$val[key]."</b><br>default:<b>".$val['default']."</b><br>".$val[extra]);
       		print <<< end_of_print
    <tr class="table_data" d_table="{$table_name}" d_column="{$val['name']}" d_all="{$use_all}" onmouseover="this.style.backgroundColor='#EDEDFD';" onmouseout="this.style.backgroundColor='#FFFFFF';">
        <td class="table_column" valign=middle><font color=#666666><b>{$val['name']}</b></font></td>
        <td valign=middle>{$type}</td>
        <td class="table_doc" valign=middle>{$showdoc}</td>
		<td class="table_help">{$helpdoc}</td>
	</tr>
end_of_print;
        }
        echo "    </table> <br><br>";
}

print <<< end_of_print

<hr size=1>
<p>

[ALTER TABLE `test` ADD `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;]
<br/>

<div id="dialog" style="margin: 20px;display: none">
	<input id="dialog_table_name" type=hidden>
	<input id="dialog_table_column" type=hidden>
	说明：<input id="dialog_doc" size=36> <input type=checkbox id="dialog_all">默认<br/>
	详细：<br/>
	<textarea id="dialog_help" rows=10 cols=48></textarea><br/>
	<button id="dialog_close">确定</button>
</div>
<div id="dialog2" style="margin: 20px;display: none">
	<input id="dialog_table_name2" type=hidden>
	说明：<input id="dialog_doc2" size=36><br/>
	<button id="dialog_close2">确定</button>
</div>



<script>
$(function(){
	$.fn.center = function(){
		var top = ($(window).height() - this.height())/2;
		var left = ($(window).width() - this.width())/2;
		var scrollTop = $(document).scrollTop();
		var scrollLeft = $(document).scrollLeft();
		return this.css( { position : 'absolute', 'top' : top + scrollTop, left : left + scrollLeft } ).show();
	}
});
var obj;
$(".table_doc").dblclick(function(){
	obj = this;
	
	$("#dialog_doc").val($(this).text());
	$("#dialog_help").val($(this).parent('.table_data').find(".table_help").text());
	$("#dialog_table_name").val($(this).parent('.table_data').attr('d_table'));
	$("#dialog_table_column").val($(this).parent('.table_data').find(".table_column").text());
	if($(this).parent('.table_data').attr('d_all') == 1)$("#dialog_all").prop("checked", "checked");
	else $("#dialog_all").prop("checked", false);
	
	layer.open({
	  type: 1,
	  title: false,
	  closeBtn: 1,
	  shadeClose: true,
	  area: ['460px','320px'],
	  content: $("#dialog")
	});
		
});
$(".table_comment").dblclick(function(){
	obj = this;
	$("#dialog_table_name2").val($(this).attr('data'));
	$("#dialog_doc2").val($(this).html());
	
	layer.open({
	  type: 1,
	  title: false,
	  closeBtn: 1,
	  shadeClose: true,
	  area: ['460px','320px'],
	  content: $("#dialog2")
	});
		
});
$("#dialog_close").click(function(){
	$.post("./?_a=edit",{
		table_name:$("#dialog_table_name").val(),
		table_column:$("#dialog_table_column").val(),
		doc:$("#dialog_doc").val(),
		help:$("#dialog_help").val(),
		all:$("#dialog_all").prop("checked")?1:0
	},function(re){
		if(re.success){
			$(obj).html($("#dialog_doc").val());
			$(obj).parent('.table_data').find(".table_help").html($("#dialog_help").val());
		}else{
			alert(re.msg);
		}
	},'JSON');
	layer.closeAll();
});
$("#dialog_close2").click(function(){
	$.post("./?_a=comment",{
		table_name:$("#dialog_table_name2").val(),
		doc:$("#dialog_doc2").val()
	},function(re){
		if(re.success){
			$(obj).html($("#dialog_doc2").val());
		}else{
			alert(re.msg);
		}
	},'JSON');
	layer.closeAll();
});
$(".table_help").dblclick(function(){
	if($(this).text() == ''){
		$(this).parent('.table_data').find(".table_doc").trigger('dblclick');
	}else{
		$.post("./?_c=trans&_a=toArray",{ajax:1, code:$(this).text()},function(re){
			if(re.success){
					layer.open({
					  type: 1,
					  title: false,
					  closeBtn: 1,
					  shadeClose: true,
					  area: ['400px','300px'],
					  content: re.data
					});
			}else{
				alert(re.msg);
			}
		},'JSON');
	}
});
</script>

end_of_print;
