<?php
	header("Content-Type: text/html;charset=utf-8"); 
	
	LIB('md');
	
	$MDM = (new MinerDefManager());
	
	switch($_REQUEST['md']){
	case 'cre'://创建矿机【管理员】
		if(isset($_REQUEST['mtype']) && isset($_REQUEST['mname']) && isset($_REQUEST['daylimt']) && isset($_REQUEST['total'])&& isset($_REQUEST['price']) && isset($_REQUEST['display']) && isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['level'])){
			$result = $MDM->CreateMiner($_REQUEST['mtype'],$_REQUEST['mname'],$_REQUEST['daylimt'],$_REQUEST['total'],$_REQUEST['price'],$_REQUEST['display'],$_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['level']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'upd'://更新矿机【管理员】
		if(isset($_REQUEST['mtype']) && isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $MDM->SetMinerInfo(
				//必须参数
				$_REQUEST['mtype'],
				$_REQUEST['tele'],
				$_REQUEST['key'],
				//选填参数
                (isset($_REQUEST['mname']))?$_REQUEST['mname']:null,
                (isset($_REQUEST['daylimt']))?$_REQUEST['daylimt']:null,
                (isset($_REQUEST['total']))?$_REQUEST['total']:null,
                (isset($_REQUEST['price']))?$_REQUEST['price']:null,
                (isset($_REQUEST['display']))?$_REQUEST['display']:null,
                (isset($_REQUEST['level']))?$_REQUEST['level']:null
			);
			echo json_encode($result);
		}else{
				echo FAILED(100,'请求错误');	
		}
		break;
	case 'del'://删除矿机【管理员】
		if(isset($_REQUEST['mtype']) && isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $MDM->DeleteMinerInfo(
				//必须参数
				$_REQUEST['mtype'],
				$_REQUEST['tele'],
				$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'gta'://获取全部矿机【管理员】
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $MDM->GetMinersInfo(
				//必须参数
				$_REQUEST['tele'],
				$_REQUEST['key'],0);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'gtd'://获取正在售卖的矿机
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $MDM->GetMinersInfo(
				//必须参数
				$_REQUEST['tele'],
				$_REQUEST['key'],1);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'gsg'://获取单个矿机
		if(isset($_REQUEST['mtype']) && isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $MDM->GetSingleMinerInfo(
				//必须参数
				$_REQUEST['mtype'],
				$_REQUEST['tele'],
				$_REQUEST['key'],0);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	default:
		break;
	}
?>