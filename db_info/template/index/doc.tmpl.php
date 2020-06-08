<?php
showhead("数据库文档 - vue版", "utf-8");
?>
<script src="http://mcdn.yishengdaojia.cn/media/js/jquery-2.1.3.min.js"></script>
<script src="http://mcdn.yishengdaojia.cn/media/tableExport/mybase64.js"></script>
<script src="http://acdn.yishengdaojia.cn/media/layer/layer.js"></script>
<script src="http://mcdn.yishengdaojia.cn/media/vue/vue.js"></script>

<h1><?php echo $response->h1;?></h1>

<hr size=1>
<p>使用说明： 双击表名、字段说明进行修改 <a href='./?_a=show' style='color:red'>返回旧版</a></p>

<div id="app">
	<hr size=1>
	<a name=top></a><div class=boxh @dblclick="foldIndex">表索引 (双击可折叠)</div>
	<div class=boxb>
	<table v-if="showIndex" border=0>
	<tr v-for="(table, tableName) in db_info" key="tableName">
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;· <a href="javascript:void(0)" @click="goAnchor(tableName)">{{tableName}}</a> </td>
		<td> <i>...{{table.comment}}</i></td>
	</tr>
	</table>
	</div>
	<div v-if="err_msg" class=boxb>{{err_msg}}</div>    
	
	<div v-for="(table, tableName) in db_info" key="tableName">	
		<div :id="tableName"></div>
	    <h3><a href=#top>↑</a><span @dblclick="comment(tableName, table.comment)">数据表【{{tableName}}】</span> - <span class="table_comment"  @dblclick="comment(tableName, table.comment)">&gt;&gt;{{table.comment}}</span>(数据量:{{table.rows}})</h3>
	    快捷： <a href="javascript:void(0)" @click="open('./?_c=trans&_a=table&table='+tableName)">[slim]</a> <a href="javascript:void(0)" @click="open('slim',tableName)">[graphQL]</a>
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
	        <td class="table_doc " valign=middle :class="isAll(tableName, col.name)" @dblclick="edit(tableName, col.name)">{{content(tableName, col.name)}}</td>
			<td class="table_help" @dblclick="edit(tableName, col.name)">{{remark(tableName, col.name)}}</td>
		  </tr>    
		</table>
		<br><br>
	</div>
	<div id="dialog" style="margin: 20px;display: none">
	说明：<input id="dialog_content" size=36 val=""> <input id="dialog_all" type=checkbox val="1">默认<br/>
	详细：<br/>
	<textarea id="dialog_remark" rows=10 cols=48></textarea><br/>
	<button @click="editClose">确定</button>
	</div>
	<div id="dialogTable" style="margin: 20px;display: none">
	表说明：<input id="dialog_comment" size=36 val="">
	<button @click="commentClose">确定</button>
	</div>
</div>


<hr size=1>
<p>
[ALTER TABLE `test` ADD `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;]
<br/>

</body>
<script>
var vm = new Vue({
	el: '#app',
	data: {
		err_msg: '',
		db_info: {
			'loading...': {
				comment: '数据加载中,请耐心等候'
			}
		},
		db_all: {},
		form: {},
		showIndex: true
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
				window.setTimeout(function(){
					$.fn.center = function(){
						var top = ($(window).height() - this.height())/2;
						var left = ($(window).width() - this.width())/2;
						var scrollTop = $(document).scrollTop();
						var scrollLeft = $(document).scrollLeft();
						return this.css( { position : 'absolute', 'top' : top + scrollTop, left : left + scrollLeft } ).show();
					}
				}, 200)
			}
		}, 'JSON');
	},
	created: function(){},
	methods: {
		isAll(tableName, colName){
			try{
				if(this.db_info[tableName].list[colName].content){
					return '';
				}
			}catch(e){}
			try{
				if(this.db_all[colName].content) {
					return 'all';
				}
			}catch(e){}
			return ''
		},
		content(tableName, colName){
			try{
				if(this.db_info[tableName].list[colName].content) return this.db_info[tableName].list[colName].content
			}catch(e){}
			try{
				return this.db_all[colName].content
			}catch(e){}
			return ''
		},
		remark(tableName, colName){
			try{
				if(this.db_info[tableName].list[colName].remark) return this.db_info[tableName].list[colName].remark
			}catch(e){}
			try{
				return this.db_all[colName].remark
			}catch(e){}
			return ''
		},
		goAnchor: function(name){
			var anchor = this.$el.querySelector('#'+name)
			document.body.scrollTop = anchor.offsetTop
			document.documentElement.scrollTop = anchor.offsetTop
		},
		foldIndex: function(){
			if(this.showIndex) this.showIndex = false
			else this.showIndex = true
		},
		open: function(action, tableName){
			$.post("./?_c=trans&_a=" + action,{ajax:true, table: tableName}, function(re){
				layer.open({
					  type: 1,
					  title: false,
					  closeBtn: 1,
					  shadeClose: true,
					  area: ['640px','480px'],
					  content: re
				});
			}, 'HTML');
		},
		close: function(){
			layer.closeAll();
		},
		edit: function(tableName, colName){
			var self = this
			this.form = {
					tableName: tableName,
					colName: colName,
			}
			$("#dialog_content").val(this.content(tableName, colName))
			$("#dialog_remark").val(this.remark(tableName, colName))
			$("#dialog_all").prop("checked", this.isAll(tableName, colName))
			
			layer.open({
			  type: 1,
			  title: false,
			  closeBtn: 1,
			  shadeClose: true,
			  area: ['480px','320px'],
			  content: $("#dialog")
			})					
		},
		editClose: function(){
			var self = this
			$.post("./?_a=edit", self.form, function(re){
				if(re.success){
					if(self.form.all){
						Vue.set(self.db_all, self.form.colName,{
								content: self.form.content,
								remark: self.form.remark
						})
					}else{
						self.db_info[self.form.tableName].list[self.form.colName].content = self.form.content
						self.db_info[self.form.tableName].list[self.form.colName].remark = self.form.remark
					}
				}else{
					alert(re.msg);
				}
			},'JSON');
			layer.closeAll();
		},
		comment: function(tableName, comment){
			var self = this
			this.form = {
					tableName: tableName,
			}
			$("#dialog_comment").val(comment)
			
			layer.open({
			  type: 1,
			  title: false,
			  closeBtn: 1,
			  shadeClose: true,
			  area: ['480px','240px'],
			  content: $("#dialogTable")
			})	
		},
		commentClose: function(){
			var self = this
			var comment = $("#dialog_comment").val()
			$.post("./?_a=comment", {table_name: self.form.tableName, comment: comment}, function(re){
				if(re.success){
					self.db_info[self.form.tableName].comment = comment
				}else{
					alert(re.msg);
				}
			},'JSON');
			layer.closeAll();
		}
	}
})
// var vmDialog = new Vue({
// 	data: {
// 		form: {}
// 	},
// 	method: {

// 	}
// })
</script>
</html>
