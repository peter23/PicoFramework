<?php

	if(getModule('Auth')->auth_user_id) {
		header('Location: '._U('/'));
		die();
	}
