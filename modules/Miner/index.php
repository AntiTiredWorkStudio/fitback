<?php
	header("Content-Type: text/html;charset=utf-8"); 
	
	LIB('mi');
	
	$MIM = (new MinerManager());
	
	switch($_REQUEST['mi']){
	case 'ini'://初始化挖矿信息，刷新挖矿状态
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $MIM->Init(
				$_REQUEST['tele'],
				$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'dig'://挖掘
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['step'])){
			$result = $MIM->Dig(
				$_REQUEST['step'],
				$_REQUEST['tele'],
				$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'gdig'://获取挖矿信息
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $MIM->GetDig(
				$_REQUEST['tele'],
				$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'test':
	   //echo json_encode($MIM->CaculateMine(1234,''));

       break;
	default:
	    echo  'null';
		break;
	}
?>