<?php

	class Module_Session extends BaseModule {

		public $data;

		public $session_name = 'SID';
		private $session_internal_name = 'PicoFramework';

		public function __construct() {
			session_name($this->session_name);
			session_start();
			if(!isset($_SESSION[$this->session_internal_name]))  $_SESSION[$this->session_internal_name] = array();
			$this->data = $_SESSION[$this->session_internal_name];
			session_write_close();
		}

		public function write() {
			session_start();
			$_SESSION[$this->session_internal_name] = $this->data;
			session_write_close();
		}

		public function setOneTimeMessage($type, $msg) {
			if(!isset($this->data['onetime_messages']) || !is_array($this->data['onetime_messages'])) {
				$this->data['onetime_messages'] = array();
			}
			$this->data['onetime_messages'][$type] = $msg;
			$this->write();
		}

		public function getOneTimeMessages() {
			if(isset($this->data['onetime_messages']) && is_array($this->data['onetime_messages'])) {
				$ret = $this->data['onetime_messages'];
				unset($this->data['onetime_messages']);
				$this->write();
				return $ret;
			} else {
				return array();
			}
		}

	}
