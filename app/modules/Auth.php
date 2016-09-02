<?php

	class Module_Auth {

		public $auth_user_id;
		public $auth_user_data;

		public function __construct() {
			$Session = getModule('Session');
			if(
				isset($Session->data['auth_user_id'])
				&&
				isset($Session->data['auth_user_agent'])
				&&
				$Session->data['auth_user_id']
				&&
				($Session->data['auth_user_agent'] == $_SERVER['HTTP_USER_AGENT'])
				&&
				($this->auth_user_data = getModule('User')->getDataByUId($Session->data['auth_user_id']))
			) {
				$this->auth_user_id = $Session->data['auth_user_id'];
			}
		}

		public function auth($user_id) {
			$Session = getModule('Session');
			$Session->data['auth_user_id'] = $user_id;
			$Session->data['auth_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$Session->write();
			$this->auth_user_id = $user_id;
			$this->auth_user_data = getModule('User')->getDataByUId($user_id);
		}

		public function unauth() {
			$Session = getModule('Session');
			unset($Session->data['auth_user_id']);
			unset($Session->data['auth_user_agent']);
			$Session->write();
			$this->auth_user_id = null;
			$this->auth_user_data = null;
		}

	}
