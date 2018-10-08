<?php
	header("Content-Type: text/html;charset=utf-8"); 
	/*if(!KeyChain('') || !isset($_REQUEST['db'])){
		echo '验证失败!';
		exit;
	}*/
	
	LIB('ke');
	$KEM = new KeyChain();
	switch($_REQUEST['ke']){
	case 'con'://校验用户身份ID
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			echo json_encode($KEM->Confirm_KeyChain($_REQUEST['tele'],$_REQUEST['key']));
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
	default:
		break;
	}
?>