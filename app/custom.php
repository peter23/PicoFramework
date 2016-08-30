<?php

	date_default_timezone_set('UTC');


	class MsgException extends Exception { }


	//some helpers

	function doRedirect($url) {
		header('Location: '.$url);
		die();
	}

	function getHostURL() {
		return 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'];
	}

	function getRunController($name, $data = array()) {
		ob_start();
		runController($name, $data);
		return ob_get_clean();
	}

	function getRunView($name, $data = array()) {
		ob_start();
		runView($name, $data);
		return ob_get_clean();
	}
