<?php
	header("Content-Type: text/html;charset=utf-8"); 
	
	LIB('ve');
	
	$VEM = (new VersionManager());
	
	switch($_REQUEST['ve']){
    case 'vset'://增加版本【管理员】
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['ver'])){
            $pars = [];
            if(isset($_REQUEST['aurl'])){
                $pars["url_android"]=$_REQUEST['aurl'];
            }
            if(isset($_REQUEST['iurl'])){
                $pars["url_ios"]=$_REQUEST['iurl'];
            }
            if(isset($_REQUEST['state'])){
                $pars["state"]=$_REQUEST['state'];
            }
            $result = $VEM->SetVersion($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['ver'],$pars);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
    case 'vkey'://生成应用校验key【管理员】
            if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['ver'])){
                $result = $VEM->GenerateAppVersionKey($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['ver']);
                echo json_encode($result);
            }else{
                echo FAILED(100,'请求错误');
            }
            break;
    case 'vchk'://版本检查
        if(isset($_REQUEST['ver']) && isset($_REQUEST['avkey']) ){
            $result = $VEM->CheckVersion($_REQUEST['ver'],$_REQUEST['avkey']);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
	default:
		break;
	}
?>