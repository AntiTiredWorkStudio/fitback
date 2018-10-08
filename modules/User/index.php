<?php
	header("Content-Type: text/html;charset=utf-8"); 
	LIB('us');
	/*if(!KeyChain('') || !isset($_REQUEST['db'])){
		echo '验证失败!';
		exit;
	}*/
	$USM = (new UserManager());
	
	switch($_REQUEST['us']){
	case 'log'://登录
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['uuid'])){
			$result = $USM->Login($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['uuid']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
	case 'reg'://注册
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['uuid'])){
			$result = $USM->Regist($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['uuid']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
    case 'pch'://更改用户权限【操作用户的权限需大于被操作用户】
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['otherTele']) && isset($_REQUEST['perm']) && isset($_REQUEST['dtime'])){
            $result = $USM->UpdatePermission($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['otherTele'],$_REQUEST['perm'],$_REQUEST['dtime']);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
    case 'plist'://获得权限列表【超级管理员】
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
            echo json_encode($USM->GetPermissionList($_REQUEST['tele'],$_REQUEST['key']));
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
   /* case 'gk'://仅用于接口测试
        echo  json_encode(UserManager::GeneratePermissionKey('13439955175',86400,'admin'));
        break;
    case 'ga':
        echo json_encode($USM->GetUserPermissions('13439955175'));
        break;*/
	default:
		break;
	}
?>