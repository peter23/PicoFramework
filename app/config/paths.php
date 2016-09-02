<?php

/**
 * Paths configuration.
 * Should returns an array with the following elements: ROUTE, BASE_URL, STATIC_BASE_URL.
 * Please see the code and comment unnecessary lines.
 *
 * A part of PicoFramework.
 * For more information @see readme.md
 */


	//subdirectory
	$q = substr($_SERVER['REQUEST_URI'], 4); // /app


	//we can take route from the whole url
	$q = $_SERVER['REQUEST_URI'];
	$q = explode('?', $q);
	$q = $q[0];


	//or from one parameter
	$q = isset($_GET['q']) ? $_GET['q'] : '';


	return array(
		'ROUTE' => $q,
		//subdirectory
		'BASE_URL' => '/app',
		//the whole url is a route
		'BASE_URL' => '',
		//route is in the parameter
		'BASE_URL' => '/?q=',
		'STATIC_BASE_URL' => '/s',
	);
