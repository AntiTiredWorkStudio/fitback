<?php
	header("Content-Type: text/html;charset=utf-8"); 
	LIB('va');

	/*
	生成验证码 :va=gen&tele=13439955175
	核实验证码:va=con&tele=13439955175&val=123456
	*/
	$VAM = (new ValidateManager());
	
	switch($_REQUEST['va']){
	case 'gen'://生成并发送验证码
		if(isset($_REQUEST['tele'])){
			$respond = $VAM->GenerateCode($_REQUEST['tele']);
			//$respondObj = json_decode($respond,true);
			if($respond['result']){
				echo SUCCESS($respond);
			}else{
				echo FAILED(1,'生成验证码失败');
			}
		}else{
			 echo FAILED(100,'请求错误');
		}
		break;
	case 'con'://校验验证码
		if(isset($_REQUEST['tele']) && isset($_REQUEST['val'])){
			$respond = $VAM->ConfirmCode($_REQUEST['tele'],$_REQUEST['val']);
			echo json_encode($respond);
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
	case 'cok'://校验并生成验证码
		if(isset($_REQUEST['tele']) && isset($_REQUEST['val'])){
			$respond = $VAM->ConfirmCodeAndGenerateKeychain($_REQUEST['tele'],$_REQUEST['val']);
			echo json_encode($respond);
		}else{
			 echo FAILED(100,'请求错误');
		}
		break;
	default:
		break;
	}
?>