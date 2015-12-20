<?php

	class Module_Session extends BaseModule {

		public $data;

		public $session_name = 'SID';
		private $session_internal_name = 'PicoFramework';

		public function __construct() {
			session_name($this->session_name);
			session_set_cookie_params(86400);
			session_start();
			if(!isset($_SESSION[$this->session_internal_name]))  $_SESSION[$this->session_internal_name] = array();
			$this->data = $_SESSION[$this->session_internal_name];
			session_write_close();
		}

		public function write() {
			@session_start();
			$_SESSION[$this->session_internal_name] = $this->data;
			session_write_close();
		}

		public function setOneTimeMessage($type, $msg) {
			if(!isset($this->data['onetime_messages']) || !is_array($this->data['onetime_messages'])) {
				$this->data['onetime_messages'] = array();
			}
			$this->data['onetime_messages'][$type][] = $msg;
			$this->write();
		}

		public function getOneTimeMessages($types) {
			$need_write = false;
			if(!is_array($types)) {
				$types = array($types);
			}
			foreach($types as $type) {
				if(isset($this->data['onetime_messages'][$type])) {
					$ret[$type] = $this->data['onetime_messages'][$type];
					unset($this->data['onetime_messages'][$type]);
					$need_write = true;
				} else {
					$ret[$type] = array();
				}
			}
			if($need_write) {
				$this->write();
			}
			return $ret;
		}

	}
