<?php
namespace controller;

class Trans extends Base {
	/*
	 * 数据类型定义
	 * 【注意】与 BaseModel 中的定义要保持一致
	 */
	const TYPE_NONE = 0;	//未定义
	const TYPE_INT = 1;		//整数
	const TYPE_STRING = 2;	//字符串
	const TYPE_FLOAT = 3;	//浮点数
	const TYPE_JSON = 4;	//JSON格式字符串
	const TYPE_DATE = 5;	//日期
	const TYPE_DATETIME = 6;	//日期时间
	const TYPE_TIMESTAMP = 7;	//时间戳整数
	//const TYPE_LIST = 8;  //逗号分隔的
	
	/*
	 * 数据保护类型
	 * 【注意】与 BaseModel 中的处理要保持一致
	 */
	const PROTECT_NONE = 0;
	const PROTECT_MOBILE = 1;
	const PROTECT_LIGHT = 2;
	const PROTECT_DEEP = 3;
	
	public function __construct($request, $response){
		parent::__construct($request, $response);
	}
	public function toArray($request, $response){
		$code = $request->code;
		$response->out = "['map'=>" . $this->_trans($code) . "]";
		if($request->ajax){
			return $this->json_ok('ok', $response->out);
		}else{
			$this->display($response);
		}
	}
	/**
	 * 生成 _slim 定义
	 * @param unknown $request
	 * @param unknown $response
	 */
	public function slim($request, $response){
		return $this->table($request, $response);
	}
	/**
	 * _slim 别名
	 * @param unknown $request
	 * @param unknown $response
	 */
	public function table($request, $response){
		$table = $request->table;
		

		self::init_db();
		$db=new \DB_glb;
		
		$info = [];
		$rows = $db->fetchAll("select * from _system__doc where `table` = '{$table}'");
		if($rows)foreach($rows as $row){
			$info[strtolower($row['field'])] = $row;
		}
		
		$all = [];
		$rows = $db->fetchAll("select * from _system__doc where `table` = '_all'");
		foreach($rows as $row){
			$all[strtolower($row['field'])] = $row;
		}
		
		$re = []; $ftype = [];
		$rows = $db->fetchAll("DESCRIBE `{$table}`");
		foreach($rows as $row){
			$field = strtolower($row['Field']);
			if($info[$field]['content']){
				$re[$field] = $info[$field];
			}elseif($all[$field]['content']){
				$re[$field] = $all[$field];
			}else{
				$re[$field] = false;
			}
			$ftype[$field] = $row['Type'];
		}
		$re = gbk2utf8($re);
		foreach($re as $k=>$v){
			if(in_array($k, ['create_time','update_time','remark','del_flag'])) continue;
			
			//保护处理
			//TODO::自动检查mobile、user_name、true_name等
			$protect = ", 'protect' => 0";
			
			//类型处理
			$type = '';
			if(preg_match('/json/', $k)){
				//特殊字段
				$type = ", 'type'=>" . self::TYPE_JSON;
			}else{
				//按mysql定义
				preg_match('/(.*)\(.*\)/', $ftype[$k], $reg);
				switch($reg[1]){
					case 'datetime':
						$type = ", 'type'=>" . self::TYPE_DATETIME;
						break;
					case 'date':
						$type = ", 'type'=>" . self::TYPE_DATE;
						break;
					case 'timestamp':
						$type = ", 'type'=>" . self::TYPE_TIMESTAMP;
						break;
					case 'year':
					case 'time':
					case 'tinyint':
					case 'smallint':
					case 'mediumint':
					case 'int':
					case 'bigint':
						$type = ", 'type'=>" . self::TYPE_INT;
						break;
					case 'float':
					case 'double':
					case 'decimal':
						$type = ", 'type'=>" . self::TYPE_FLOAT;
						break;
					case 'char':
					case 'varchar':
					case 'tinyblob':
					case 'tinytext':
					case 'blob':
					case 'text':
					case 'mediumblob':
					case 'longblob':
					case 'longtext':
						$type = ", 'type'=>" . self::TYPE_STRING;
						break;
					default:
						$type = ", 'type'=>" . self::TYPE_NONE;
						break;
				}
			}
				
			if(! $v){
				//未作定义
				$out[] = "'{$k}' => ['filter'=>0, title'=>'{$k}', {$type}],";
			}else{
				//自动转义定义
				$trans = '';
				$remark = stripslashes(trim($v['remark']));
				if(preg_match('/\'referer\'=>/', $remark)){
					//自动引用（格式：'referer'=>["\\YsConfig","sex_des"]）
					$trans = ', ' . $remark;			
				}elseif(preg_match('/\'func\'=>/', $remark)){
					//自动计算（格式：'func'=>["\\model\\User","getNameById"]）
					$trans = ',' . $remark;
				}elseif(preg_match('/(0|1)[ ]?(:|：)/', $remark) || preg_match('/(0|1)[ ]?=>/', $remark)){
					//数值映射（格式：0:正常,1:锁定  或 0=>'正常',1=>'锁定' 或 [0=>'正常',1=>'锁定']）
					$tmp = $this->_trans($remark);
					if($tmp){
						$trans = ", 'map'=>" . $tmp;
					}
				}
				$out[] = "'{$k}' => ['filter'=>0, 'title'=>'{$v['content']}'{$type}{$trans}],";
			}
		}
		$response->out = "public static \$define_slim = [\n	" . implode("\n	", $out) . "\n];";
		
		if($request->ajax){
			echo nl2br($response->out);
		}else{
			$this->display($response);
		}
	}
	/**
	 * 辅助函数： 自动修正手误
	 * @param unknown $code
	 */
	public function _trans($code){
		$code = str_replace('：', ':', $code);
		$code = str_replace([',', '，', '　', ' ', '  ', "'", '"'], '', $code);
		$code = str_replace("（", '(', $code);
		$code = str_replace('）', ')', $code);
		preg_match_all('/(-?[0-9]+)\s?(\:|=>)\s?([^\s]+[\)]?)/', $code, $reg, PREG_SET_ORDER);
		foreach($reg as $v){
			$re[$v[1]] = $v[3];
		}
		$re = var_export($re, true);
		$re = str_replace([" ","　","\t","\n","\r"], '', $re);
		if(preg_match('/^array\((.*)\)$/', $re, $reg)){
			$re = "[".$reg[1]."]";
		}
		return $re;
	}
}