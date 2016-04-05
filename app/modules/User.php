<?php

	class Module_User extends BaseModule {

		public function generateRandomString($length) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for($i = 0; $i < $length; $i++) {
				$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}

		public function create($email, $password) {
			$password_salt = $this->generateRandomString(8);
			$this->DB
				->insertInto('users', array(
					'email' => $email,
					'password' => hash('sha256', $password_salt.$password),
					'password_salt' => $password_salt,
				))
				->execute();
		}

		public function auth($email, $password) {
			if(!($password_salt = $this->DB
				->select('password_salt')
				->from('users')
				->where('email', $email)
				->fetchVal()
			)) {
				throw new MsgException('Incorrect email or password');
			}
			if(!($id = $this->DB
				->select('id')
				->from('users')
				->where(array(
					'email' => $email,
					'password' => hash('sha256', $password_salt.$password),
				))
				->fetchVal()
			)) {
				throw new MsgException('Incorrect email or password');
			}
			getModule('Auth')->auth($id);
			return $id;
		}

		public function getDataByUId($id) {
			return $this->DB
				->select('id', 'email', 'is_admin')
				->from('users')
				->where('id', $id)
				->fetch();
		}

	}
