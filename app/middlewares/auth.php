<?php

	if(!getModule('Auth')->auth_user_id) {
		getModule('Utils')->doRedirect(_U('/noauth/login'));
	}
