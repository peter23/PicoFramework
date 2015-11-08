<?php

	class Model_EMail extends BaseModel {

		public function sendView($to, $view, $view_data = array(), $from_email = false, $from_name = false) {
			$text = getRunView($view, $view_data);
			$subject = strtok($text, "\n");
			$text = substr($text, strlen($subject)+1);
			$subject = strip_tags($subject);
			return $this->send($to, $subject, $text, $from_email, $from_name);
		}

		public function send($to, $subject, $text = '', $from_email = false, $from_name = false) {
			$cfg = getConfig('email');
			if(!$from_email && isset($cfg['FROM_EMAIL'])) {
				$from_email = $cfg['FROM_EMAIL'];
			}
			if(!$from_name && isset($cfg['FROM_NAME'])) {
				$from_name = $cfg['FROM_NAME'];
			}
			return mail(
				$to,
				'=?UTF-8?B?'.base64_encode($subject).'?=',
				$text,
				'From: '.($from_name ? '=?UTF-8?B?'.base64_encode($from_name).'?= ' : '').'<'.$from_email.'>'."\r\n"
					.'Content-type: text/html; charset=utf-8'."\r\n"
					.'Content-Transfer-Encoding: 8bit'."\r\n"
					.'Content-Disposition: inline'."\r\n"
			);
		}

	}

?>