<?php

	class Module_Utils {

		public function doRedirect($url) {
			header('Location: '.$url);
			die();
		}

		public function getHostURL() {
			return 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'];
		}

		public function getRunController($name, $data = array()) {
			ob_start();
			runController($name, $data);
			return ob_get_clean();
		}

		public function getRunView($name, $data = array()) {
			ob_start();
			runView($name, $data);
			return ob_get_clean();
		}

	}
