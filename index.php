<?php

	require('core.php');

	$q = isset($_GET['q']) ? rtrim($_GET['q'], ' /') : '';
	if(!$q)  $q = '/default';

	try {
		runController($q);
	} catch(Exception $e) {
		runController('/_404');
	}
