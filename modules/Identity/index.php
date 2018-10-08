<?php
	header("Content-Type: text/html;charset=utf-8"); 
	
	LIB('id');
	
	$IDM = (new IdentityManager());
	
	switch($_REQUEST['id']){
	case 'gen'://发起实名认证
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $IDM->GenerateUploadInfo(
				$_REQUEST['tele'],
				$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'res'://接受实名认证信息上传结果
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['rname']) && isset($_REQUEST['iurl']) && isset($_REQUEST['postfix']) && isset($_REQUEST['cadress'])){
			$result = $IDM->SubmitUploadResult(
				$_REQUEST['tele'],
				$_REQUEST['key'],$_REQUEST['rname'],$_REQUEST['iurl'],$_REQUEST['postfix'],$_REQUEST['cadress']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
	case 'get'://获取实名认证结果
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
            $result = $IDM->GetIdentityResult($_REQUEST['tele'],
                $_REQUEST['key']);
            echo  json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
	    break;
    case 'gsb'://获取全部实名认证申请【管理员】
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['state'])){
            $result = $IDM->GetAllIdentityRequest($_REQUEST['tele'],
                $_REQUEST['key'],$_REQUEST['state']);
            echo  json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
         break;
    case 'aud'://人工审核【管理员】  aresult值0为假1为真
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['aresult'])){
            $result = $IDM->ConfirmIdentityRequest($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['aresult']);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
	default:
		break;
	}
?>