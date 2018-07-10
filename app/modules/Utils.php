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

		public function generateRandomString($length) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for($i = 0; $i < $length; $i++) {
				$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}

		public function cryptStr($s, $key = false) {
			$iv = $this->generateRandomString(16);
			return $iv.openssl_encrypt(
				gzdeflate($s),
				'AES256',
				$key ? $key : getConfig('crypt', 'key'),
				0,
				$iv
			);
		}

		public function decryptStr($s, $key = false) {
			$iv = substr($s, 0, 16);
			$s = substr($s, 16);
			return gzinflate(openssl_decrypt(
				$s,
				'AES256',
				$key ? $key : getConfig('crypt', 'key'),
				0,
				$iv
			));
		}

	}
