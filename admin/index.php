<?php
	error_reporting(E_ALL ^ E_NOTICE);
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$http  = (strtolower($_SERVER['HTTPS']) == 'on' ? 'https:' : 'http:');
	$extra = 'pageHome.php';
	header("Location: $http//$host$uri/$extra");
	exit;
?>