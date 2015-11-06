<?php

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


	function getConfig($name) {
		static $configs;
		if(!isset($configs)) {
			$configs = array();
		}
		if(isset($configs[$name])) {
			return $configs[$name];
		} else {
			$file = 'config/'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new Exception('Config "'.$name.'" can not be loaded');
			} else {
				$configs[$name] = require($file);
				return $configs[$name];
			}
		}
	}


	function runController($name) {
		$file = 'controllers'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new Exception('Controller "'.$name.'" can not be loaded');
		} else {
			require($file);
		}
	}


	function runView($name, $data = array()) {
		$file = 'views'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new Exception('View "'.$name.'" can not be loaded');
		} else {
			extract($data);
			require($file);
		}
	}


	function initDatabase() {
		require('FluentPDO/FluentPDO.php');
		$cfg = getConfig('db');
		return new FluentPDO(new PDO('mysql:host='.$cfg['HOST'].';dbname='.$cfg['NAME'].';charset=utf8', $cfg['USER'], $cfg['PASS']));
	}


	function getModel($name) {
		static $models, $DB;
		if(!isset($models)) {
			$models = array();
			$DB = initDatabase();
		}
		if(isset($models[$name])) {
			return $models[$name];
		} else {
			$file = 'models'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new Exception('Model "'.$name.'" can not be loaded');
			} else {
				require($file);
				//$models[$name] =
				return $configs[$name];
			}
		}
	}


	// ===== UTILS

	// controller url
	function _U($q, $params = '') {
		if(is_array($params)) {
			$params = http_build_query($params);
		}
		$cfg = getConfig('paths');
		return $cfg['BASE_URL'].'/?q='.$q.($params ? '&'.$params : $params);
	}

	// static url
	function _US($q) {
		$cfg = getConfig('paths');
		return $cfg['STATIC_BASE_URL'].$q;
	}

	// html escape
	function _HE($s) {
		return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
	}
