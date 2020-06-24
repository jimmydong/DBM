<?php
/**
 * 最小MVC
 * 
 */

namespace controller;

class Base extends \lib\Controller {
	public static $cfg;
	
	public function __construct($request, $response) {
		parent::__construct($request, $response);
		include("../db_admin/config.inc.php");
		self::$cfg = $cfg;
	}
	
	/**
	 * 在controller之前的处理 
	 */
	public function before($_controller, $_action){
		//nothing
	}
	
	/**
	 * 在controller之后的处理
	 * 【注意：controller中exit/die则无法获得执行】 
	 */
	public function after($_controller, $_action){
		//nothing
	}
	
	/**
	 * 调用模板（php模板）
	 */
	public function display($response, $template = ''){
		if($template == '') $template = $this->_controller . '/' . $this->_action;
		include( dirname(dirname(__FILE__)) . '/template/' . $template . '.tmpl.php' );
	}
	
	//判断用户是否是移动端访问
	public function check_is_mobile()
	{
		if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
			$is_mobile = false;
		} elseif ( (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') === false)  // many mobile devices (all iPhone, iPad, etc.)
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false 
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'UCWEB') !== false ) {
			$is_mobile = true;
		} else {
			$is_mobile = false;
		}
		
		return $is_mobile;
	}
	
	/**
	 * 判断微信环境
	 * @return boolean
	 */
	function _isWeixin(){
		/**
		 * JS处理方法：
		 * function _isWeixin(){
		 var ua = navigator.userAgent.toLowerCase();
		 if(ua.match(/MicroMessenger/i)=="micromessenger") {
		 return true;
		 } else {
		 return false;
		 }
		 }
		 */
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
			return true;
		}
		return false;
	}

	/**
	 * 转化URL
	 * Enter description here ...
	 * @param $controller
	 * @param $action
	 * @param $param
	 */
	public function _url($controller='', $action='', $param=array()){
		if($controller == '') $controller = $this->_controller;
		if($action == '') $action = $this->_action;
		$param['_c'] = $controller;
		$param['_a'] = $action;
		return "./index.php?" . http_build_query($param);
	}

	public function redirect($url){
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("location: {$url}");
		exit;
	}
	
	public function json_ok($msg = 'OK', $data = ''){
		if (!$data) $data = new \stdClass();
		header('content-type: application/json;charset=utf-8');
		echo json_encode(array('success'=>true, 'msg'=>$msg, 'data'=>$data), JSON_UNESCAPED_UNICODE);
		exit;
	}

	public function json_fail($msg = 'Fail', $data = ''){
		if (!$data) $data = new \stdClass();
		header('content-type: application/json;charset=utf-8');
		echo json_encode(array('success'=>false, 'msg'=>$msg, 'data'=>$data), JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	static public function init_db($serverid='', $database=''){
		//数据库连接
		$serverid = $serverid?:$_SESSION['serverid'];
		$database = $database?:$_SESSION['database'];
		if($serverid && $database && self::$cfg['Servers'][$serverid]){
			$serverinfo=self::$cfg['Servers'][$serverid];
			$tmp_str = "
			class DB_glb extends \lib\DB_Mysql {
			var \$Host     = '{$serverinfo['host']}:{$serverinfo['port']}';
			var \$Database = '{$database}';
			var \$User     = '{$serverinfo['user']}';
			var \$Password = '{$serverinfo['password']}';
		}
		";
			eval($tmp_str);
			return $serverinfo;
		}else{
			var_dump($serverid, $database);
			die('数据库设定失败');
			//return false;
		}
	}
}
