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

		public function generateRandomString($length, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
			$charactersLength = strlen($characters);
			$randomString = '';
			for($i = 0; $i < $length; $i++) {
				$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}

		public function cryptStr($s, $key = false) {
			$iv = $this->generateRandomString(8);
			return $iv.openssl_encrypt(
				gzdeflate(time().$s),
				'BF',
				$key ? $key : getConfig('crypt', 'key'),
				0,
				$iv
			);
		}

		public function decryptStr($s, $key = false, $lifetime = false) {
			$iv = substr($s, 0, 8);
			$s = substr($s, 8);
			$ret = gzinflate(openssl_decrypt(
				$s,
				'BF',
				$key ? $key : getConfig('crypt', 'key'),
				0,
				$iv
			));
			$time = time();
			$time_strlen = strlen($time);
			$ret_time = substr($ret, 0, $time_strlen);
			if( $lifetime && (($time - $ret_time) > $lifetime) ) {
				return false;
			} else {
				return substr($ret, $time_strlen);
			}
		}

		public function cryptStrSmall($s, $key = false, $iv = false) {
			return openssl_encrypt(
				$s,
				'BF',
				$key ? $key : getConfig('crypt', 'key_small'),
				0,
				$iv ? $iv : getConfig('crypt', 'iv_small')
			);
		}

		public function decryptStrSmall($s, $key = false, $iv = false) {
			return openssl_decrypt(
				$s,
				'BF',
				$key ? $key : getConfig('crypt', 'key_small'),
				0,
				$iv ? $iv : getConfig('crypt', 'iv_small')
			);
		}

	}
