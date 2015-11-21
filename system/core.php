<?php


	define('ROOT_DIR', dirname(__DIR__));


	// ===== CORE

	function processRequest($q) {
		$q = rtrim($q, ' /');
		if(!$q)  $q = '/';

		try {
			runController($q);
		} catch(LoadException $e) {
			error_log(formatException($e));
			runController('/_404');
		}
	}


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


	function getConfig($name, $param = false) {
		static $configs;
		if(!isset($configs)) {
			$configs = array();
		}
		if(!isset($configs[$name])) {
			$file = ROOT_DIR.'/app/config/'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new LoadException('Config "'.$name.'" can not be loaded');
			} else {
				$configs[$name] = include($file);
			}
		}
		if(!$param) {
			return $configs[$name];
		} else {
			return $configs[$name][$param];
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
							break 2;
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


	function getRunController($name, $data = array()) {
		ob_start();
		runController($name, $data);
		return ob_get_clean();
	}


	function runView($name, $data = array()) {
		$file = ROOT_DIR.'/app/views/'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new LoadException('View "'.$name.'" can not be loaded');
		} else {
			extract(htmlEscape($data));
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
		if(!isset($modules[$name])) {
			$file = ROOT_DIR.'/app/modules/'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new LoadException('Module "'.$name.'" can not be loaded');
			} else {
				include($file);
				$classname = preg_replace('#[^a-z0-9\_]#i', '_', 'Module_'.$name);
				$modules[$name] = new $classname($DB);
			}
		}
		return $modules[$name];
	}


	class LoadException extends Exception { }



	// ===== UTILS

	// controller url
	function _U($q, $params = '') {
		$ret = getConfig('paths', 'BASE_URL').$q;
		if($params) {
			if(strpos($ret, '?') === false) {
				$ret .= '?';
			} else {
				$ret .= '&';
			}
			if(is_array($params)) {
				$params = http_build_query($params);
			}
			$ret .= $params;
		}
		return $ret;
	}

	// static url
	function _US($q) {
		return getConfig('paths', 'STATIC_BASE_URL').$q;
	}

	//quite unique
	define('DONT_ESCAPE', '^%DONT_ESCAPE_'.microtime(true));

	function htmlEscape($s) {
		if(!is_array($s)) {
			return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
		} else {
			if((count($s) == 2) && isset($s[0]) && ($s[0] == DONT_ESCAPE)) {
				return $s[1];
			} else {
				foreach($s as &$s1) {
					$s1 = htmlEscape($s1);
				} unset($s1);
				return $s;
			}
		}
	}

	function dontHtmlEscape($v) {
		return array(DONT_ESCAPE, $v);
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
