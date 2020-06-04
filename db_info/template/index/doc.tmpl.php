<?php
showhead("数据库文档 - vue版");
?>
<script src="http://mcdn.yishengdaojia.cn/media/js/jquery-2.1.3.min.js"></script>
<script src="http://mcdn.yishengdaojia.cn/media/tableExport/mybase64.js"></script>
<script src="http://acdn.yishengdaojia.cn/media/layer/layer.js"></script>
<script src="http://mcdn.yishengdaojia.cn/media/vue/vue.js"></script>

<h1><?php echo $response->h1;?></h1>

<hr size=1>
<p>使用说明： 双击“说明”进行修改，双击“详细”生成映射</p>

<template id="app">
	<hr size=1>
	<a name=top></a><div class=boxh>表索引</div>
	<div class=boxb>
	<table border=0>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;· <a href=#163_city_order>163_city_order</a> </td>
		<td> <i>...</i></td>
	</tr>
	</table>
	</div>
	
	<div v-if="err_msg" class=boxb>{{err_msg}}</div>    
	
	<div>	
		<a name='163_city_order'>
	    <h3><a href=#top>↑</a>数据表【163_city_order】 - <span class="table_comment" data="163_city_order">&gt;&gt;</span>(数据量:2)</h3>
	    <a href="javascript:;" onclick="$('#t_163_city_order').tableExport({type:'csv',escape:'false'})">导出CSV</a> | 
	    <a href="./?_c=trans&_a=table&table=163_city_order" target=_blank>生成定义</a>
	    <div class=boxc>
	    <a href=./php?_a=log&table=163_city_order>newlog&raquo;</a><br> <a href=./php?_a=remark&table=163_city_order>addremark&raquo;</a>
	    </div>
	    <table class="table" id="t_163_city_order" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="80%" align=center>
	      <tr>
	        <td bgcolor="#868786" width=200><b><font color=white>字段名</font></b></td>
	        <td bgcolor="#868786" width=150><b><font color=white>类型</font></b></td>
	        <td bgcolor="#868786" width=240><b><font color=white>说明</font></b></td>
	        <td bgcolor="#868786" width=*><b><font color=white>详细</font></b></td>
	      </tr>    
	      <tr class="table_data" d_table="163_city_order" d_column="id" d_all="0" onmouseover="this.style.backgroundColor='#EDEDFD';" onmouseout="this.style.backgroundColor='#FFFFFF';">
	        <td class="table_column" valign=middle><font color=#666666><b>id</b></font></td>
	        <td valign=middle><span onmouseover="show_help('&lt;b&gt;int11&lt;/b&gt;&lt;br&gt;null:NO&lt;br&gt;key:&lt;b&gt;PRI&lt;/b&gt;&lt;br&gt;default:&lt;b&gt;&lt;/b&gt;&lt;br&gt;auto_increment',event);" onmouseout="show_help('',event);">int() </span></td>
	        <td class="table_doc " valign=middle></td>
			<td class="table_help"></td>
		  </tr>    
		  <tr class="table_data" d_table="163_city_order" d_column="city" d_all="0" onmouseover="this.style.backgroundColor='#EDEDFD';" onmouseout="this.style.backgroundColor='#FFFFFF';">
	        <td class="table_column" valign=middle><font color=#666666><b>city</b></font></td>
	        <td valign=middle><span onmouseover="show_help('&lt;b&gt;varchar200&lt;/b&gt;&lt;br&gt;null:NO&lt;br&gt;key:&lt;b&gt;&lt;/b&gt;&lt;br&gt;default:&lt;b&gt;&lt;/b&gt;&lt;br&gt;',event);" onmouseout="show_help('',event);">varchar() </span></td>
	        <td class="table_doc " valign=middle></td>
			<td class="table_help"></td>
		  </tr>
		  <tr class="table_data" d_table="163_city_order" d_column="number" d_all="0" onmouseover="this.style.backgroundColor='#EDEDFD';" onmouseout="this.style.backgroundColor='#FFFFFF';">
	        <td class="table_column" valign=middle><font color=#666666><b>number</b></font></td>
	        <td valign=middle><span onmouseover="show_help('&lt;b&gt;int11&lt;/b&gt;&lt;br&gt;null:NO&lt;br&gt;key:&lt;b&gt;&lt;/b&gt;&lt;br&gt;default:&lt;b&gt;&lt;/b&gt;&lt;br&gt;',event);" onmouseout="show_help('',event);">int() </span></td>
	        <td class="table_doc " valign=middle></td>
			<td class="table_help"></td>
		  </tr>
		</table>
		<br><br>
	</div>
</template>

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
var vm = new Vue({
	el: '#app',
	data: {
		err_msg: '',
		db_info: {},
	},
	computed: {},
	watch: {},
	mounted: function(){
		var self = this;
		$.post('./?_a=ajax', {}, function(re){
			if(! re.success){
				if(re.msg) self.err_msg = re.msg
				else self.err_msg = JSON.stringfy(re)
			}else{
				self.table_index = re.data.table_index
				self.table_list = re.table_list
			}
		}, 'JSON');
	},
	created: function(){
	},
	method: {
		
	}
})

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
	//防止误操作，全部为“非默认”
	//if($(this).parent('.table_data').attr('d_all') == 1)$("#dialog_all").prop("checked", "checked");
	//else $("#dialog_all").prop("checked", false);
	
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
</html>