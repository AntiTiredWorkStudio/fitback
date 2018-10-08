<?php
	header("Content-Type: text/html;charset=utf-8"); 
	
	LIB('wi');
	
	$WDM = (new WithdrawManager());
	
	switch($_REQUEST['wi']){
        case 'ini'://初始化提现状态
            if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
                //检查是否实名认证
                //获取默认地址
                //获取当前余额
                $result = $WDM->InitWithdraw($_REQUEST['tele'],$_REQUEST['key']);
                echo json_encode($result);
            }else{
                echo  FAILED(100,'请求错误');
            }
            break;
	case 'apl'://申请提现
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['mcount']) && isset($_REQUEST['adress'])){
			$result = $WDM->SubmitWithdraw($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['mcount'],$_REQUEST['adress']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
    case 'gsi'://加载单一提现记录
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['id'])) {
            $result = $WDM->GetSingleWithdraw($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['id']);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
	case 'gwi'://加载提现记录
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
            $result = $WDM->GetWithdraw($_REQUEST['tele'],$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');	
		}
		break;
    case 'aud'://人工审核【管理员】  wresult值0为假1为真
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['id']) && isset($_REQUEST['wresult'])){
            $result = $WDM->FinishedWithdraw($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['id'],$_REQUEST['wresult']);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
    /*case 'test':
        echo  $WDM->GenerateWithdrawID();
        break;*/
	default:
		break;
	}
?>