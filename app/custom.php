<?php

	date_default_timezone_set('UTC');


	class MsgException extends Exception { }


	function doRedirect($url) {
		header('Location: '.$url);
		die();
	}


	function getHostURL() {
		return 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'];
	}
