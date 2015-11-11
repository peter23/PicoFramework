<?php

	require('system/core.php');

	require('app/custom.php');

	$q = isset($_GET['q']) ? rtrim($_GET['q'], ' /') : '';
	if(!$q)  $q = '/default';

	try {
		runController($q);
	} catch(LoadException $e) {
		error_log(formatException($e));
		runController('/_404');
	}
