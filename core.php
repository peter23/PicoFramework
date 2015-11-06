<?php

	require('config.php');


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


	function initDatabase() {
		require('FluentPDO/FluentPDO.php');
		return new FluentPDO(new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS));
	}


	function getModel($name) {
		$file = 'models'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new Exception('Model "'.$name.'" can not be included');
		} else {
			static $models, $db;
			if(!isset($models) && !isset($db)) {
				$models = array();
				$db = initDatabase();
			}
			if(!isset($models[$name])) {
				require($file);
				//$models[$name] =
			}
			return $models[$name];
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
