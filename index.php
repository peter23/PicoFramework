<?php

	// ===== INIT
	define('BASE_URL', '/test');
	define('STATIC_BASE_URL', '/test/s');

	require('db.php');

	$q = isset($_GET['q']) ? rtrim($_GET['q'], ' /') : '';
	if(!$q)  $q = '/default';

	try {
		runController($q);
	} catch(Exception $e) {
		runController('/_404');
	}


	// ===== CORE
	function allowIncludeFile($file) {
		if(
			(strpos($file, '../')!==false)
			||
			(strpos($file, '/..')!==false)
			||
			(!file_exists($file))
		) {
			return false;
		} else {
			return true;
		}
	}


	function runController($name) {
		$file = 'controllers'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new Exception('Controller "'.$name.'" can not be included');
		} else {
			require($file);
		}
	}


	function runView($name, $data = array()) {
		$file = 'views'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new Exception('View "'.$name.'" can not be included');
		} else {
			extract($data);
			require($file);
		}
	}


	// ===== UTILS
	// controller url
	function _U($q, $params = '') {
		if(is_array($params)) {
			$params = http_build_query($params);
		}
		return BASE_URL.'/?q='.$q.($params ? '&'.$params : $params);
	}
	// static url
	function _US($q) {
		return STATIC_BASE_URL.$q;
	}
	// html escape
	function _H($s) {
		return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
	}
