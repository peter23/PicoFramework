<?php

	date_default_timezone_set('UTC');

	class MsgException extends Exception { }


	function doRedirect($url) {
		header('Location: '.$url);
		die();
	}
