<?php

	//we can take route from the whole url
	$q = $_SERVER['REQUEST_URI'];
	$q = explode('?', $q);
	$q = $q[0];


	//or from one parameter
	$q = isset($_GET['q']) ? $_GET['q'] : '';


	return array(
		'ROUTE' => $q,
		'BASE_URL' => '/',
		'STATIC_BASE_URL' => '/s',
	);
