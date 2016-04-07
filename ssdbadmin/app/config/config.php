<?php
define('ENV', 'online');

return array(
	'env' => ENV,
	'logger' => array(
		'level' => 'all', // none/off|(LEVEL)
		'dump' => 'file', // none|html|file, 可用'|'组合
		'files' => array( // ALL|(LEVEL)
			#'ALL'	=> dirname(__FILE__) . '/../../logs/' . date('Y-m') . '.log',
		),
	),
	'servers' => array(
		array(
			'host' => 'web02',
			'port' => '6379',
			'password' => 'yishengDaojia@2015ASDFGHJKL12345',
		),
	),
	'login' => array(
		'name' => 'yisheng',
		'password' => 'yisheng@2015', // at least 6 characters
	),
);
