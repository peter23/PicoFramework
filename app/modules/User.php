<?php

	class Module_User extends BaseModule {

		public function create($email, $password, $role) {
			$password_salt = getModule('Utils')->generateRandomString(8);
			$this->DB
				->insertInto('users', array(
					'email' => $email,
					'password' => hash('sha256', hash('sha256', hash('sha256', $password_salt.$password))),
					'password_salt' => $password_salt,
					'role' => $role,
				))
				->execute();
			return $this->DB->insert_id;
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
					'password' => hash('sha256', hash('sha256', hash('sha256', $password_salt.$password))),
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
				->select('*')
				->from('users')
				->where('id', $id)
				->fetch();
		}

		public function update($id, $data) {
			if(isset($data['password']) && $data['password']) {
				$data['password_salt'] = getModule('Utils')->generateRandomString(8);
				$data['password'] = hash('sha256', hash('sha256', hash('sha256', $data['password_salt'].$data['password'])));
			}
			$this->DB->update('users', $data)->where('id', $id)->execute();
		}

	}
