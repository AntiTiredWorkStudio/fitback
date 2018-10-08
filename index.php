<?php

    header("Access-Control-Allow-Origin:*");

    header("Content-Type: text/html;charset=utf-8;");

	include_once("public/conf.php");
	include_once("public/lib.php");
	$requestArray = [];

	foreach($_REQUEST as $key=>$value){
		array_push($requestArray,$key);
	}

	if(empty($requestArray)){
		die(FAILED('98'));
	}

	REQUEST($requestArray[0]);
?>