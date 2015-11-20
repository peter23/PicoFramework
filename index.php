<?php

	require('system/core.php');

	require('app/custom.php');

	processRequest(getConfig('paths', 'ROUTE'));
