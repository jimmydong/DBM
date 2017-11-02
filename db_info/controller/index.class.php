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
		$no_doc=true;
		$q->query("SHOW TABLE STATUS");
		while($q->next_record())
		{
		    $table = gbk2utf8($q->Record);
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
		if(count($db_index)==0) {
			$response->re = "当前库中没有可显示的表结构";
			return $this->display($response);
		}
		$re = "<a name=top></a><div class=boxh>表索引</div>\n<div class=boxb>\n<table border=0>\n";
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
	public function comment($request, $response){
		if($request->step){
			$table = $request->table;
			if ($table=='') return $this->json_fail("Error: 传入参数错误!");
			$comment=addslashes(utf82gbk($request->comment));
			
			self::init_db();
			$q=new \DB_glb;
			
			if ($q->query("ALTER TABLE `$table` COMMENT = '$comment'")){
				$this->redirect("./?_a=show");
			}else{
				die("数据库操作失败！");
			}
		}else{
			$response->table = $request->table;
			$this->display($response);
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
		$q=new \DB_glb;
		$q->query("CREATE TABLE `_system__doc` (
					  `table` varchar(60) NOT NULL default '',
					  `field` varchar(60) NOT NULL default '',
					  `content` varchar(200) NOT NULL default '',
					  `remark` text NOT NULL,
					  PRIMARY KEY  (`table`,`field`)
					) TYPE=MyISAM COMMENT='数据表说明文档';"
		);
		return $this->redirect("./?_a=show");
	}
	
}