<?php

	require('FluentPDO/FluentPDO.php');

	$db = new FluentPDO(new PDO('mysql:dbname=test;charset=utf8', 'test', 'test'));

?>