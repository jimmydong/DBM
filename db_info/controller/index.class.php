<?php
namespace controller;

class Index extends Base {
	public function __construct($request, $response){
		parent::__construct($request, $response);
	}
	public function index($request, $response){
		if($request->step){
			$serverid = $request->serverid;
			if(!self::$cfg['Servers'][$serverid])die("Can't find DB!");
			else $serverinfo=self::$cfg['Servers'][$serverid];
			$tmp_str = "
			class DB_glb extends \lib\DB_Mysql {
			var \$Host     = '{$serverinfo[host]}:{$serverinfo[port]}';
			var \$Database = 'mysql';
			var \$User     = '{$serverinfo[user]}';
			var \$Password = '{$serverinfo[password]}';
			}
			";
			eval($tmp_str);
			$q=new \DB_glb;
			$q->query("SHOW DATABASES");
			while($record=$q->next_record()){
				if($record[Database]=='information_schema' || $record[Database]=='mysql')continue;
				$re .= "<option value='{$record[Database]}'>{$record[Database]}</option>";
			}
			$response->re = $re;
			$response->step = $request->step;
			$response->serverid = $serverid;
		}
		
		$this->display($response);
	}
	
	public function show($request, $response){
		if($serverid = $request->serverid){
			$_SESSION['serverid'] = $serverid;
		}else{
			$serverid = $_SESSION['serverid'];
		}
		if($database = $request->database){
			$_SESSION['database'] = $database;
		}else{
			$database = $_SESSION['database'];
		}
		
		$serverinfo = self::init_db($serverid, $database);
		$response->h1 = "#{$serverid} [{$serverinfo[host]}:{$serverinfo[port]} {$serverinfo[ext_name]}] 数据库：{$database}";
		
		$q=new \DB_glb;
		$q2=new \DB_glb;
		//获取数据库结构信息
		$q->query("SHOW TABLE STATUS");
		while($q->next_record())
		{
		    $table = gbk2utf8($q->Record);
		    if($table[Name]!="_system__doc")
		    {
		    	//获取表字段信息
		        $db_index[]=$table[Name];
		        $comment = $table[Comment]; if(preg_match('/InnoDB/',$comment))$comment = '';
		        $db_comment[$table[Name]] = $comment;
		        $db_rows[$table[Name]] = $table[Rows];
		        $db_info[$table[Name]] = $q2->get_fullfields($table[Name]);
		    }
		}
		if(count($db_index)==0) {
			$response->re = "当前库中没有可显示的表结构";
			return $this->display($response);
		}
		$re = "<a name=top></a><div id='index' class=boxh>表索引(点击折叠)</div>\n<div class=boxb>\n<table border=0>\n";
		asort($db_index);
		foreach($db_index as $tablename)
		{
			$re .= "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;· <a href=#$tablename>$tablename</a> </td><td> <i>...$db_comment[$tablename]</i></td></tr>\n";
		}
		$re .= "</table>\n</div>\n";
		
		$q->query("SELECT table_name FROM information_schema.TABLES WHERE table_schema='{$database}' and table_name ='_system__doc'");
		if(! $q->next_record()){
			$re .= "<div class=boxb>Warrning: <p>未找到内容介绍文档结构。<a href=?_c=index&_a=createdoc>创建文档结构</a></div>";
		}else{
			//读出DOC表内容
			$q->query("SELECT * FROM _system__doc");
			while($q->next_record())
			{
				if ($q->f('table')=='' || $q->f('field')=='') continue;
				$doc_content[$q->f('table')][$q->f('field')] = $q->f('content');
				$doc_remark[$q->f('table')][$q->f('field')] = $q->f('remark');
			}
		}
		
		$response->db_info = $db_info;
		$response->db_comment = $db_comment;
		$response->db_rows = $db_rows;
		$response->doc_content = gbk2utf8($doc_content);
		$response->doc_remark = gbk2utf8($doc_remark);
		$response->modify = $modify;
		$response->re = $re;
		$this->display($response);
	}
	public function edit($request, $response){
		$table = $request->table_name;
		$column = $request->table_column;
		if($table == '' || $column == '') return $this->json_fail('参数不正确');
		
		self::init_db();
		$q=new \DB_glb;
		
		$content = addslashes(utf82gbk($request->doc));
		$remark = addslashes(utf82gbk($request->help));
		$table = $request->table_name;
		$field = $request->table_column;
		if($request->all==1)$table="_all";
		$sql = "REPLACE _system__doc SET `table`='$table', `field`='$field', `content`='$content', `remark`='$remark'";
		if ($q->query($sql))
		{
			return $this->json_ok('ok');
		}
		else{
			return $this->json_fail("数据库操作失败！");
		}
	}
	public function log($request, $response){
		if($request->step){
			$table = $request->table;
			if ($table=='') return $this->json_fail("Error: 传入参数错误!");
			$content=date("Y-m-d H:i:s ").addslashes(utf82gbk($request->content));
			$field = '_log';
			$loginfo=$q->query_first("SELECT * FROM `_system__doc` WHERE `table`='$table' AND `field`='_log'");
			$remark=$loginfo['content']."<br>\n".$loginfo['remark'];
			$sql = "REPLACE _system__doc SET `table`='$table', `field`='$field', `content`='$content', `remark`='$remark'";
			if ($q->query($sql)){
				return $this->redirect("./?_a=show");
			}else{
				die("数据库操作失败！");
			}
		}else{
			$response->table = $request->table;
			$this->display($response);
		}
	}
	public function createdoc($request, $response){
		$database = $_SESSION['database'];
		$serverinfo = self::init_db($serverid, $database);
		
		$q=new \DB_glb;
		$q->query(iconv('utf-8', 'gbk', "CREATE TABLE IF NOT EXISTS `_system__doc` (
			  `table` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `field` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `content` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `remark` text COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY  (`table`,`field`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='数据表说明文档';"
		));
		return $this->redirect("./?_a=show");
	}
	public function comment($request, $response){
		$table_name = $request->table_name;
		$doc = iconv('utf-8','gbk',$request->doc);
		
		if(! $table_name || ! $doc) return $this->json_fail('请填写内容');
		
		$database = $_SESSION['database'];
		$serverinfo = self::init_db($serverid, $database);
		$q=new \DB_glb;
		$q->query("alter table `{$table_name}` comment '{$doc}'");
		return $this->json_ok();
	}
	public function doc($request, $response){
		if($serverid = $request->serverid){
			$_SESSION['serverid'] = $serverid;
		}else{
			$serverid = $_SESSION['serverid'];
		}
		if($database = $request->database){
			$_SESSION['database'] = $database;
		}else{
			$database = $_SESSION['database'];
		}
		
		$serverinfo = self::init_db($serverid, $database);
		$response->h1 = "#{$serverid} [{$serverinfo[host]}:{$serverinfo[port]} {$serverinfo[ext_name]}] 数据库：{$database}";
	
		$this->display($response);
	}
	public function ajax($request, $response){
		if($serverid = $request->serverid){
			$_SESSION['serverid'] = $serverid;
		}else{
			$serverid = $_SESSION['serverid'];
		}
		if($database = $request->database){
			$_SESSION['database'] = $database;
		}else{
			$database = $_SESSION['database'];
		}
	
		$serverinfo = self::init_db($serverid, $database);
		
		$q=new \DB_glb;
		$q2=new \DB_glb;
		//获取数据库结构信息
		$q->query("SHOW TABLE STATUS");
		$db_info = [];
		while($q->next_record())
		{
			$table = gbk2utf8($q->Record);
			if($table['Name']!="_system__doc")
			{
				//获取表字段信息
				$comment = $table[Comment]; if(preg_match('/InnoDB/',$comment))$comment = '';
				$db_info[$table[Name]] = [
						'comment' => $comment,
						'rows'	=> $table['Rows'],
						'list' => $q2->get_fullfields($table['Name'])
				];
			}
		}
		if(count($db_info) == 0) {
			return $this->json_fail("当前库中没有可显示的表结构");
		}
		asort($db_info);
	
		$msg = '';
		$q->query("SELECT table_name FROM information_schema.TABLES WHERE table_schema='{$database}' and table_name ='_system__doc'");
		if(! $q->next_record()){
			$msg = "未找到内容介绍文档结构。<a href=?_c=index&_a=createdoc>创建文档结构</a>";
		}else{
			//读出DOC表内容
			$q->query("SELECT * FROM _system__doc");
			while($q->next_record())
			{
				if ($q->f('table')=='' || $q->f('field')=='') continue;
				$db_info[$q->f('table')]['content'][$q->f('field')] = $q->f('content');
				$db_info[$q->f('table')]['remark'][$q->f('field')] = $q->f('remark');
			}
		}
		foreach($db_info as $table_name=>$table_info)
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
		
// 		    foreach($table_info as $key=>$val)
// 	        {
// 	        	$showdoc=$doc_content[$table_name][$val[name]];
// 	        	if($showdoc=='' && $doc_content[_all][$val[name]]){
// 	        		//使用通用注释
// 	        		$all_class = 'all';
// 	        		$showdoc= $doc_content[_all][$val[name]];
// 	        		$use_all = 1;
// 	        	}else{
// 	        		$all_class = '';
// 	        		$use_all = 0;
// 	        	}
// 	        	$helpdoc=$doc_remark[$table_name][$val[name]]?:$doc_remark[_all][$val[name]];
// 	        	$height = 22 * count(explode("\n", $helpdoc)) + 22;
// 	       		$type = showhelp("{$val[type]}({$val[len]})","<b>".$val[type].implode(' ',$val[args])."</b><br>null:".$val['null']."<br>key:<b>".$val[key]."</b><br>default:<b>".$val['default']."</b><br>".$val[extra]);
// 	        }
	        
			return $this->json_ok($msg, ['db_info'=>$db_info]);
			
		}		
	}
}