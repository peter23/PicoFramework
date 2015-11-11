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
			$file = 'app/config/'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new LoadException('Config "'.$name.'" can not be loaded');
			} else {
				$configs[$name] = require($file);
				return $configs[$name];
			}
		}
	}


	function runController($name) {
		$file = 'app/controllers'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new LoadException('Controller "'.$name.'" can not be loaded');
		} else {
			require($file);
		}
	}


	function getRunController($name) {
		ob_start();
		runController($name);
		return ob_get_clean();
	}


	function runView($name, $data = array()) {
		$file = 'app/views'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new LoadException('View "'.$name.'" can not be loaded');
		} else {
			extract($data);
			require($file);
		}
	}


	function getRunView($name, $data = array()) {
		ob_start();
		runView($name, $data);
		return ob_get_clean();
	}


	function initDatabase() {
		require('system/fluentpdo/FluentPDO/FluentPDO.php');
		$cfg = getConfig('db');
		$pdo = new PDO('mysql:host='.$cfg['HOST'].';dbname='.$cfg['NAME'].';charset=utf8', $cfg['USER'], $cfg['PASS']);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		return new FluentPDO($pdo);
	}


	function getModel($name) {
		//it is singleton, isn't it?
		static $models, $DB;
		if(!isset($models)) {
			$models = array();
			$DB = initDatabase();
		}
		if(isset($models[$name])) {
			return $models[$name];
		} else {
			$file = 'app/models'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new LoadException('Model "'.$name.'" can not be loaded');
			} else {
				require($file);
				$classname = preg_replace('#[^a-z0-9\_]#i', '_', 'Model'.$name);
				$models[$name] = new $classname($DB);
				return $models[$name];
			}
		}
	}


	class LoadException extends Exception { }



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

	// echo html escape
	function _EH($s) {
		echo(_HE($s));
	}

	function setSessionMessage($type, $msg) {
		if(!isset($_SESSION['session_messages']) || !is_array($_SESSION['session_messages'])) {
			$_SESSION['session_messages'] = array();
		}
		$_SESSION['session_messages'][$type] = $msg;
	}

	function getSessionMessages() {
		if(isset($_SESSION['session_messages']) && is_array($_SESSION['session_messages'])) {
			$ret = $_SESSION['session_messages'];
			unset($_SESSION['session_messages']);
			return $ret;
		} else {
			return array();
		}
	}

	function formatException($e) {
		$trace = $e->getTrace();
		foreach($trace as &$trace1) {
			$trace1 = $trace1['file'].':'.$trace1['line'].':'.$trace1['function'];
		}
		unset($trace1);
		return "\n".$e->getMessage()."\n".implode("\n", $trace)."\n";
	}

	function generateRandomString($length) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for($i = 0; $i < $length; $i++) {
			$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}



	// ===== BASIC MODEL
	class BaseModel {

		protected $DB;

		public function __construct(&$DB) {
			$this->DB = $DB;
		}

	}
