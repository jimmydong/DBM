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

<div id="app">
	<hr size=1>
	<a name=top></a><div class=boxh>表索引</div>
	<div class=boxb>
	<table border=0>
	<tr v-for="(table, tableName) in db_info" key="tableName">
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;· <a href="javascript:void(0)" @click="goAnchor(tableName)">{{tableName}}</a> </td>
		<td> <i>...{{table.comment}}</i></td>
	</tr>
	</table>
	</div>
	
	<div v-if="err_msg" class=boxb>{{err_msg}}</div>    
	
	<div v-for="(table, tableName) in db_info" key="tableName">	
		<div :id="tableName"></div>
	    <h3><a href=#top>↑</a>数据表【{{tableName}}】 - <span class="table_comment" data="">&gt;&gt;{{table.comment}}</span>(数据量:{{table.rows}})</h3>
	    快捷： <a href="javascript:void(0)" @click="open('./?_c=trans&_a=table&table='+tableName)">生成_slim</a> <a href="javascript:void(0)" @click="open('slim',tableName)">生成graphQL</a>
	    <table class="table" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="80%" align=center>
	      <tr>
	        <td bgcolor="#868786" width=200><b><font color=white>字段名</font></b></td>
	        <td bgcolor="#868786" width=150><b><font color=white>类型</font></b></td>
	        <td bgcolor="#868786" width=240><b><font color=white>说明</font></b></td>
	        <td bgcolor="#868786" width=*><b><font color=white>详细</font></b></td>
	      </tr>    
	      <tr v-for="(col, k) in table.list" key="k" class="table_data" onmouseover="this.style.backgroundColor='#EDEDFD';" onmouseout="this.style.backgroundColor='#FFFFFF';">
	        <td class="table_column" valign=middle><font color=#666666><b>{{col.name}}</b></font></td>
	        <td valign=middle>{{col.type}}</td>
	        <td class="table_doc " valign=middle @dbclick="edit(tableName, col.name)">{{db_info[tableName].content[col.name]|db_all[tableName][col.name].content}}</td>
			<td class="table_help" @dbclick="edit(tableName, col.name)">{{db_info[tableName].remark[col.name]|db_all[tableName][col.name].remark}}</td>
		  </tr>    
		</table>
		<br><br>
	</div>
</div>

<hr size=1>
<p>
[ALTER TABLE `test` ADD `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;]
<br/>

<div id="dialog" style="margin: 20px;display: none">
	说明：<input size=36 :val="form.content"> <input type=checkbox :val="form.all">默认<br/>
	详细：<br/>
	<textarea rows=10 cols=48>{{form.remark}}</textarea><br/>
	<button @click="editClose">确定</button>
</div>
<div id="dialog2" style="margin: 20px;display: none">
	说明：<input id="dialog_doc2" size=36><br/>
	<button @click="close">确定</button>
</div>
</body>
<script>
var vm = new Vue({
	el: '#app',
	data: {
		err_msg: '',
		db_info: {},
		db_all: {},
		form: {}
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
				self.db_info = re.data.db_info
				self.db_all = re.data.db_all
			}
		}, 'JSON');
	},
	created: function(){
	},
	methods: {
		goAnchor: function(name){
			var anchor = this.$el.querySelector('#'+name)
			document.documentElement.scrollTop = 1000
		},
		open: function(action, tableName){
			$.post("./?_c=trans&_a=" + action,{ajax:true, table: tableName}, function(re){
				layer.open({
					  type: 1,
					  title: false,
					  closeBtn: 1,
					  shadeClose: true,
					  area: ['460px','320px'],
					  content: re
				});
			}, 'HTML');
		},
		close: function(){
			layer.closeAll();
		},
		edit: funciton(tableName, colName){
			this.form = {
					tableName: tableName,
					colName: colName,
					content: this.db_info[tableName].content[col.name]?this.db_info[tableName].content[col.name]:this.db_all[tableName][col.name].content,
					remark: this.db_info[tableName].remark[col.name]?this.db_info[tableName].remark[col.name]:this.db_all[tableName][col.name].remark
			}
			
			layer.open({
			  type: 1,
			  title: false,
			  closeBtn: 1,
			  shadeClose: true,
			  area: ['460px','320px'],
			  content: $("#dialog")
			});					
		},
		editClose: function(){
			var self = this
			$.post("./?_a=edit", this.form, function(re){
				if(re.success){
					self.db_info[tableName].content[col.name] = form.content
					self.db_info[tableName].remark[col.name] = form.remark
				}else{
					alert(re.msg);
				}
			},'JSON');
			layer.closeAll();
		}
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
