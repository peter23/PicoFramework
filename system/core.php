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
	define('APP_DIR', ROOT_DIR.'/app');


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
			(strpos($file, '../') !== false)
			||
			(strpos($file, '/..') !== false)
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
			$file = APP_DIR.'/config/'.$name.'.php';
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
		if(allowIncludeFile(APP_DIR.'/middlewares/_zzz_last.php')) {
			$middlewares[] = '/_zzz_last';
		}
		$try_name = $name;
		do {
			if(allowIncludeFile(APP_DIR.'/middlewares'.$try_name.'.php')) {
				$middlewares[] = $try_name;
			}
		} while( ($try_name = dirname($try_name)) && (strlen($try_name) > 1) );
		if(allowIncludeFile(APP_DIR.'/middlewares/_default.php')) {
			$middlewares[] = '/_default';
		}
		$middlewares = array_reverse($middlewares);
		return $middlewares;
	}


	function runController($name, $data = array(), $middlewares = array()) {
		//here is very-super-light and stupid routing
		$_QNAME = $name;
		do {
			$files = array(
				APP_DIR.'/controllers'.$_QNAME.'._QPARAM.php' => '_QPARAM',
				APP_DIR.'/controllers'.$_QNAME.'.php' => '',
				APP_DIR.'/controllers'.$_QNAME.'/_default._QPARAM.php' => '_QPARAM',
				APP_DIR.'/controllers'.$_QNAME.'/_default.php' => '',
			);
			$please_break = false;
			foreach($files as $file => $file_type) {
				if(allowIncludeFile($file)) {
					$is_qparam_file = $file_type === '_QPARAM';
					$is_sub_path = strlen($_QNAME) !== strlen($name);
					if($is_qparam_file ^ $is_sub_path) {
						$please_break = true;
						continue;
					}
					if($is_qparam_file && $is_sub_path) {
						$_QPARAM = substr($name, strlen($_QNAME)+1);
					}
					foreach($middlewares as $middleware) {
						include(APP_DIR.'/middlewares'.$middleware.'.php');
					}
					extract($data);
					include($file);
					return;
				}
			}
			if($please_break)  break;
		} while( ($_QNAME = dirname($_QNAME)) && (strlen($_QNAME) > 1) );
		throw new LoadException('Controller "'.$name.'" can not be loaded');
	}


	function runView($name, $data = array()) {
		$file = APP_DIR.'/views/'.$name.'.php';
		if(!allowIncludeFile($file)) {
			throw new LoadException('View "'.$name.'" can not be loaded');
		} else {
			extract(htmlEscape($data));
			include($file);
		}
	}


	function getDatabaseConnection() {
		require_once(ROOT_DIR.'/system/PicoDatabase/PicoDatabase.php');
		$cfg = getConfig('db');
		return new PicoDatabase($cfg['HOST'], $cfg['USER'], $cfg['PASS'], $cfg['NAME'], 'utf8');
	}


	function getModule($name, $data = array()) {
		$DB = dataRepo('getModule_DB');
		if($DB === null) {
			$DB = getDatabaseConnection();
			dataRepo('getModule_DB', $DB);
		}
		$repo_key = 'getModule|'.$name;
		$module = dataRepo($repo_key);
		if($module === null) {
			$file = APP_DIR.'/modules/'.$name.'.php';
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
		if(!isset($repo))  $repo = array();
		if($val !== null) {
			$repo[$key] = $val;
		} else {
			return isset($repo[$key]) ? $repo[$key] : null;
		}
	}

	function dataRepoPush($key, $val) {
		$arr = dataRepo($key);
		if(!is_array($arr))  $arr = array();
		$arr[] = $val;
		dataRepo($key, $arr);
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
			if(is_array($params))  $params = http_build_query($params);
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
			if( (count($s) === 2) && isset($s[0]) && ($s[0] === dataRepo('DONT_ESCAPE')) ) {
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
		if(!dataRepo('DONT_ESCAPE')) {
			//quite unique
			dataRepo('DONT_ESCAPE', '^%DONT_ESCAPE_'.microtime(true));
		}
		return array(dataRepo('DONT_ESCAPE'), $v);
	}

	function formatException(&$e, $include_file = true) {
		if($include_file) {
			$file_line = $e->getFile().':'.$e->getLine();
		}
		$trace = $e->getTrace();
		foreach($trace as &$trace1) {
			$trace1 = (isset($trace1['file']) ? $trace1['file'] : '<unknown file>')
				.':'.(isset($trace1['line']) ? $trace1['line'] : '<unknown line>')
				.':'.(isset($trace1['function']) ? $trace1['function'] : '<unknown function>');
		}
		unset($trace1);
		return "\n".$e->getMessage()."\n".($include_file ? $file_line."\n" : '').implode("\n", $trace)."\n";
	}



	// ===== BASE MODULE
	class BaseModule {

		protected $DB;

		public function __construct(&$DB) {
			$this->DB = $DB;
		}

	}



	require(APP_DIR.'/custom.php');
