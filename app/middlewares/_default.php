<?php

if(!defined('PicoFramework_default_middleware_done')) {

	define('PicoFramework_default_middleware_done', true);


	date_default_timezone_set('UTC');

	class MsgException extends Exception { }


	function doRedirect($url) {
		header('Location: '.$url);
		die();
	}


}
