<?php

/**
 * PicoFramework is a very lightweight modular MVC-framerwork with filesystem-based routing
 *
 * For more information @see readme.md
 *
 * @link https://github.com/peter23/PicoFramework
 * @author i@peter23.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */


	require('system/core.php');
	processRequest(getConfig('paths', 'ROUTE'));
