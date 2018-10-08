<?php
	header("Content-Type: text/html;charset=utf-8"); 
	include_once("DBManager.php");

	$DBM = (new DBManager());
	
	switch($_REQUEST['db']){
	case 'ini'://初始化数据库【管理员,后期需屏蔽的接口】
		$DBM->InitDB();
		echo '初始化完成 继续...!';
		break;
	default:
		break;
	}

?>