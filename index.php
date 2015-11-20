<?php

	require('system/core.php');

	require('app/custom.php');

	$paths_cfg = getConfig('paths');
	processRequest($paths_cfg['ROUTE']);
