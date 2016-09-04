<?php

/**
 * PicoFramework is a very lightweight modular MVC-framerwork with filesystem-based routing
 *
 * For more information @see readme.md
 *
 * @link https://github.com/peter23/PicoFramework
 * @author i@peter23.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */


	define('ROOT_DIR', dirname(__DIR__));


	// ===== CORE

	function processRequest($q) {
		$q = rtrim($q, ' /');
		if(!$q)  $q = '/';

		try {
			runController(
				$q,
				array(),
				getMiddlewares($q)
			);
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
		$repo_key = 'getConfig|'.$name;
		$config = dataRepo($repo_key);
		if($config === null) {
			$file = ROOT_DIR.'/app/config/'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new LoadException('Config "'.$name.'" can not be loaded');
			} else {
				$config = include($file);
				dataRepo($repo_key, $config);
			}
		}
		if(!$param) {
			return $config;
		} else {
			return $config[$param];
		}
	}


	function getMiddlewares($name) {
		//here is middleware processing
		$middlewares = array();
		$try_name = $name;
		do {
			if(allowIncludeFile(ROOT_DIR.'/app/middlewares'.$try_name.'.php')) {
				$middlewares[] = $try_name;
			}
		} while( ($try_name = dirname($try_name)) && (strlen($try_name) > 1) );
		if(allowIncludeFile(ROOT_DIR.'/app/middlewares/_default.php')) {
			$middlewares[] = '/_default';
		}
		return $middlewares;
	}


	function runController($name, $data = array(), $middlewares = array()) {
		//here is very-super-light and stupid routing
		$_QNAME = $name;
		do {
			$files = array(
				ROOT_DIR.'/app/controllers'.$_QNAME.'.php',
				ROOT_DIR.'/app/controllers'.$_QNAME.'/_default.php',
			);
			foreach($files as $file) {
				if(allowIncludeFile($file)) {
					if(strlen($_QNAME) !== strlen($name)) {
						$qparam_controllers = getConfig('qparam_controllers');
						if(isset($qparam_controllers[$_QNAME])) {
							$_QPARAM = substr($name, strlen($_QNAME)+1);
						} else {
							break 2;
						}
					}
					foreach($middlewares as $middleware) {
						include(ROOT_DIR.'/app/middlewares'.$middleware.'.php');
					}
					extract($data);
					include($file);
					return;
				}
			}
		} while( ($_QNAME = dirname($_QNAME)) && (strlen($_QNAME) > 1) );
		throw new LoadException('Controller "'.$name.'" can not be loaded');
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


	function initDatabase() {
		require_once(ROOT_DIR.'/system/PicoDatabase/PicoDatabase.php');
		$cfg = getConfig('db');
		return new PicoDatabase($cfg['HOST'], $cfg['USER'], $cfg['PASS'], $cfg['NAME'], 'utf8');
	}


	function getModule($name, $data = array()) {
		$DB = dataRepo('getModule_DB');
		if($DB === null) {
			$DB = initDatabase();
			dataRepo('getModule_DB', $DB);
		}
		$repo_key = 'getModule|'.$name;
		$module = dataRepo($repo_key);
		if($module === null) {
			$file = ROOT_DIR.'/app/modules/'.$name.'.php';
			if(!allowIncludeFile($file)) {
				throw new LoadException('Module "'.$name.'" can not be loaded');
			} else {
				include($file);
				$classname = preg_replace('#[^a-z0-9\_]#i', '_', 'Module_'.$name);
				$module = new $classname($DB, $data);
				dataRepo($repo_key, $module);
			}
		}
		return $module;
	}


	class LoadException extends Exception { }



	// ===== REPO

	function dataRepo($key, $val = null) {
		//it is singleton, isn't it?
		static $repo;
		if(!isset($repo)) {
			$repo = array();
		}
		if($val !== null) {
			$repo[$key] = $val;
		} else {
			return isset($repo[$key]) ? $repo[$key] : null;
		}
	}



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

	function htmlEscape($s) {
		if(!is_array($s)) {
			return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
		} else {
			if(defined('DONT_ESCAPE') && (count($s) === 2) && isset($s[0]) && ($s[0] === DONT_ESCAPE)) {
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
		if(!defined('DONT_ESCAPE')) {
			//quite unique
			define('DONT_ESCAPE', '^%DONT_ESCAPE_'.microtime(true));
		}
		return array(DONT_ESCAPE, $v);
	}

	function formatException(&$e) {
		$trace = $e->getTrace();
		foreach($trace as &$trace1) {
			$trace1 = (isset($trace1['file']) ? $trace1['file'] : '<unknown file>')
				.':'.(isset($trace1['line']) ? $trace1['line'] : '<unknown line>')
				.':'.(isset($trace1['function']) ? $trace1['function'] : '<unknown function>');
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
