<?php
namespace controller;

class Trans extends Base {
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
	public function table($request, $response){
		$table = $request->table;
		

		self::init_db();
		$db=new \DB_glb;
		
		$rows = $db->fetchAll("select * from _system__doc where `table` = '{$table}'");
		foreach($rows as $row){
			$info[strtolower($row['field'])] = $row;
		}
		$rows = $db->fetchAll("select * from _system__doc where `table` = '_all'");
		foreach($rows as $row){
			$all[strtolower($row['field'])] = $row;
		}
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
		}
		$re = gbk2utf8($re);
		foreach($re as $k=>$v){
			if(in_array($k, ['create_time','update_time','remark','del_flag'])) continue;
			if(! $v) $out[] = "'{$k}' => ['title'=>'未标注']";
			else{
				//需要转义的字段
				$type = '';
				if(preg_match('/_id|province|city|json/', $k)){
					$type = ", 'type'=>1";
				}
				//自动映射的字段
				$map = '';
				if(preg_match('/(0|1)[ ]?(:|：)/', $v['remark']) || preg_match('/(0|1)[ ]?=>/', $v['remark'])){
					$tmp = trans($v['remark']);
					if($tmp){
						$map = ", 'map'=>" . $tmp;
					}
				}
				$out[] = "'{$k}' => ['title'=>'{$v['content']}'{$map}{$type}],";
			}
		}
		$response->out = "public static \$define_slim = array(\n	" . implode("\n	", $out) . "\n);";
		
		$this->display($response);
	}
	public function _trans($code){
		$code = str_replace('：',':',$code);
		$code = str_replace(',',' ',$code);
		$code = str_replace('，',' ',$code);
		$code = str_replace('　',' ',$code);
		$code = str_replace('  ',' ',$code);
		$code = str_replace("'",'',$code);
		$code = str_replace('"','',$code);
		preg_match_all('/(-?[0-9]+)\s?(\:|=>)\s?([^\s]+)/', $code, $reg, PREG_SET_ORDER);
		foreach($reg as $v){
			$re[$v[1]] = $v[3];
		}
		$re = var_export($re, true);
		$re = str_replace(',)',']',str_replace('array(','[',str_replace(array(" ","　","\t","\n","\r"), '', $re)));
		return $re;
	}
}