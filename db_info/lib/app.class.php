<?php
namespace lib;
use \Exception;

/**
 * 简单的mvc实现
 *
 */
class App {

	private $_controller = 'index';
	private $_action = 'index';
	private $_model = '';

	public function __construct($controller, $action, $model = '') {
		if ($controller)
			$this->_controller = $controller;
		if ($action)
			$this->_action = $action;
		if ($model)
			$this->_model = $model;
	}

	/**
	 * 启动一个controller
	 */
	public function run() {
		$request = Request::getInstance();
		$response = new Response();
		$response->_c = $response->_controller = $this->_controller;
		$response->_a = $response->_action = $this->_action;

		if($this->_model)$controller = "\\controller\\".  $this->_model . "\\"  . $this->_controller;
		else $controller = "\\controller\\". $this->_controller;
		try {
			if (!class_exists($controller))
				throw new Exception("no controller called $controller ");
			$obj = new $controller($request, $response);
			if (!method_exists($obj, $this->_action) && !method_exists($obj, '__call') && $this->_controller != 'index') //允许使用魔术方法
				throw new Exception("'$controller' has not method '{$this->_action}' ");
			$obj->before($this->_controller, $this->_action);
			$re = $obj->{$this->_action}($request, $response);
			if(is_string($re))echo $re;
			elseif(is_array($re)){echo "<pre>";print_r($re);echo "</pre>";}
			elseif(is_bool($re)){
				if(!$re){
					//wait ...	
				}
			}else{
				//do nothing
			}
			$obj->after($this->_controller, $this->_action);
		} catch (Exception $e) {
			if(defined('IS_TEST') && IS_TEST){
				var_dump($e);
			}else{
				echo "System Error:  Please contact customer service.";
			}
		}
	}


}
