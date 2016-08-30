<?php

	if(getModule('Auth')->auth_user_id) {
		doRedirect(_U('/'));
	}
