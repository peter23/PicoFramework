<?php


	define('ROOT_DIR', dirname(__DIR__));


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
			$file = ROOT_DIR.'/app/config/'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new LoadException('Config "'.$name.'" can not be loaded');
			} else {
				$configs[$name] = include($file);
				return $configs[$name];
			}
		}
	}


	function runController($name, $data = array()) {
		//here is very-super-light and stupid routing
		$try_name = $name;
		do {
			$files = array(
				ROOT_DIR.'/app/controllers'.$try_name.'.php',
				ROOT_DIR.'/app/controllers'.$try_name.'/_default.php',
			);
			foreach($files as $file) {
				if(allowIncludeFile($file)) {
					if(strlen($try_name) != strlen($name)) {
						$qparam_controllers = getConfig('qparam_controllers');
						if(isset($qparam_controllers[$try_name])) {
							$_QPARAM = substr($name, strlen($try_name)+1);
						} else {
							break;
						}
					}
					extract($data);
					include($file);
					return;
				}
			}
		} while( ($try_name = dirname($try_name)) && (strlen($try_name) > 1) );
		throw new LoadException('Controller "'.$name.'" can not be loaded');
	}


	function getRunController($name) {
		ob_start();
		runController($name);
		return ob_get_clean();
	}


	function runView($name, $data = array()) {
		$file = ROOT_DIR.'/app/views/'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new LoadException('View "'.$name.'" can not be loaded');
		} else {
			extract($data);
			include($file);
		}
	}


	function getRunView($name, $data = array()) {
		ob_start();
		runView($name, $data);
		return ob_get_clean();
	}


	function initDatabase() {
		require_once(ROOT_DIR.'/system/fluentpdo/FluentPDO.php');
		$cfg = getConfig('db');
		return new FluentPDO('mysql:host='.$cfg['HOST'].';dbname='.$cfg['NAME'].';charset=utf8', $cfg['USER'], $cfg['PASS']);
	}


	function getModule($name) {
		//it is singleton, isn't it?
		static $modules, $DB;
		if(!isset($modules)) {
			$modules = array();
			$DB = initDatabase();
		}
		if(isset($modules[$name])) {
			return $modules[$name];
		} else {
			$file = ROOT_DIR.'/modules/'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new LoadException('Module "'.$name.'" can not be loaded');
			} else {
				include($file);
				$classname = preg_replace('#[^a-z0-9\_]#i', '_', 'Module_'.$name);
				$modules[$name] = new $classname($DB);
				return $modules[$name];
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

	function formatException(&$e) {
		$trace = $e->getTrace();
		foreach($trace as &$trace1) {
			$trace1 = $trace1['file'].':'.$trace1['line'].':'.$trace1['function'];
		}
		unset($trace1);
		return "\n".$e->getMessage()."\n".implode("\n", $trace)."\n";
	}



	// ===== BASE MODULE
	class BaseModule {

		protected $DB;

		public function __construct(&$DB) {
			$this->DB = $DB;
		}

	}
