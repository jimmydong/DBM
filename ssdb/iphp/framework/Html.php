<?php
class Html{
	private static $config = array();

	function __construct(){
		throw new Exception("Static class");
	}
	
	static function host(){
		$host = $_SERVER['HTTP_HOST'];
		$port = $_SERVER['SERVER_PORT'];
		if(strpos($host, ':') === false && $port != 80 && $port != 443){
			$host .= ":{$port}";
		}
		return $host;
	}
	
	static function base_url(){
		if(App::$base_url){
			return App::$base_url;
		}
		static $link = null;
		if($link === null){
			$host = $_SERVER['HTTP_HOST'];
			$port = $_SERVER['SERVER_PORT'];
			if(strpos($host, ':') === false && $port != 80 && $port != 443){
				$host .= ":{$port}";
			}
			$path = dirname($_SERVER['SCRIPT_NAME']);
			if($path == '/' || $path == "\\"){
				$path = ''; 
			}
			if($_SERVER['HTTPS'] || $port == 443){
				$link = "https://{$host}{$path}";
			}else{
				$link = "http://{$host}{$path}";
			}
		}
		return $link;
	}


	static function init($config=array()){
		self::$config = $config;
	}

	static function build_url_query($param, $static=false){
		$arr = array();
		foreach($param as $k=>$v){
			if($static && !is_array($v)){
				// 路径中不能包含%2F
				$v = urlencode(str_replace('/', '%2F', $v));
				$arr[] = $k . '/' . $v;
			}else{
				if(is_array($v)){
					foreach($v as $n=>$s){
						$n = urlencode($n);
						$arr[] = $k . "[$n]=" . urlencode($s);
					}
				}else{
					$arr[] = $k . '=' . urlencode($v);
				}
			}
		}
		if($static){
			$query = join('/', $arr);
		}else{
			$query = join('&', $arr);
		}
		return $query;
	}
	
	private static function is_static_resource($url){
		static $exts = array('js', 'css');
		$ps = explode('?', $url);
		$url = $ps[0];
		$ps = explode('#', $url);
		$url = $ps[0];
		$ps = explode('.', $url);
		$ext = $ps[count($ps) - 1];
		return in_array($ext, $exts);
	}

	static function link($url, $param=array()){
		if(strpos($url, 'http://') === false && strpos($url, 'https://') === false){
			$url = trim($url, '/');
			$url = self::base_url() . '/' . $url;
		}
		if(App::$asset_md5){
			if(App::$asset_md5 && !isset($param['_v']) && self::is_static_resource($url)){
				$relative_path = substr($url, strlen(self::base_url().'/'));
				if(isset(App::$asset_md5[$relative_path])){
					$param['_v'] = App::$asset_md5[$relative_path];
				}
			}
		}else{
			if(App::$version && !isset($param['_v']) && self::is_static_resource($url)){
				$param['_v'] = App::$version;
			}
		}
		if($param){
			if(strpos($url, '?')){
				$url .= '&';
			}else{
				$url .= '?';
			}
			$url .= self::build_url_query($param);
		}
		return $url;
	}

	/**
	 * 生成操作的链接
	 */
	static function action($act, $param=array()){
		$rewrite = App::$router->rewrite == true;
		$url = App::$baseUrl . '/';

		if($act == '' && $param){
			$act = App::$router->act;
		}

		$param_url = self::build_url_query($param, $rewrite);
		if($rewrite){
			if($act){
				$url .= "{$act}";
			}
			if($param_url){
				if($rewrite){
					$url .= '/' . $param_url;
				}else{
					$url .= '?' . $param_url;
				}
			}
		}else{
			$url .= '';
			if($act && $act != 'index'){
				$url .= "?act={$act}";
				if($param_url){
					$url .= '&' . $param_url;
				}
			}else{
				if($param_url){
					if($rewrite){
						$url .= '/' . $param_url;
					}else{
						$url .= '?' . $param_url;
					}
				}
			}
		}

		return $url;
	}
	
	static function select($name, $options, $default=''){
		$html = '';
		$html .= "<select name=\"$name\">";
		foreach($options as $k=>$v){
			$k = trim($k);
			$sel = strcmp($k, $default)==0? ' selected="selected"' : '';
			$html .= "<option value=\"$k\"$sel>$v</option>";
		}
		$html .= "</select>\n";
		return $html;
	}
}

