<?php
	header("Content-Type: text/html;charset=utf-8"); 
	LIB('or');
	$ODM = new OrderManager();
	switch($_REQUEST['or']){
	case 'sub'://产生订单
		if(isset($_REQUEST['tele']) && isset($_REQUEST['mtype']) && isset($_REQUEST['key'])){
			$result = $ODM->SubmitOrder($_REQUEST['tele'],$_REQUEST['mtype'],$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
	case 'success'://支付成功
		if(isset($_REQUEST['id']) && isset($_REQUEST['tele']) && isset($_REQUEST['key']) &&isset($_REQUEST['pr']) && isset($_REQUEST['pa'])){
			$result = $ODM->OrderSuccess($_REQUEST['id'],$_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['pr'],$_REQUEST['pa']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
    case 'failed'://支付失败
        if(isset($_REQUEST['id']) && isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
            $result = $ODM->OrderFailed($_REQUEST['id'],$_REQUEST['tele'],$_REQUEST['key']);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
	case 'cancel'://取消订单
		if(isset($_REQUEST['id']) && isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $ODM->OrderCancel($_REQUEST['id'],$_REQUEST['tele'],$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
	case 'invalid'://订单失效
		if(isset($_REQUEST['id']) && isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $ODM->OrderInvalid($_REQUEST['id'],$_REQUEST['tele'],$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
    case 'gets'://获取单独订单
        if(isset($_REQUEST['tele']) && isset($_REQUEST['key']) && isset($_REQUEST['id'])){
            $result = $ODM->GetSingleOrder($_REQUEST['tele'],$_REQUEST['key'],$_REQUEST['id']);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
	case 'get'://获取订单
		if(isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			$result = $ODM->GetOrders($_REQUEST['tele'],$_REQUEST['key']);
			echo json_encode($result);
		}else{
			echo FAILED(100,'请求错误');
		}
		break;
	case 'pay'://判断支付条件是否满足
		if(isset($_REQUEST['id']) && isset($_REQUEST['tele']) && isset($_REQUEST['key'])){
			echo json_encode($ODM->PayCheck($_REQUEST['id'],$_REQUEST['tele'],$_REQUEST['key']));
		}else{
			echo FAILED(100,'请求错误');
		}

		break;
	case 'wxpay':
	    if(isset($_REQUEST['id']) && isset($_REQUEST['type']) && isset($_REQUEST['price']) && isset($_REQUEST['tele']) && isset($_REQUEST['key'])) {

            // echo $_SERVER["REMOTE_ADDR"];
            //   var_export();
            //1.统一下单方法
            //$result = (new ShoppingController())->actionPays();
            //var_export($result);
            //$result = (new WechatPay($_REQUEST['id'],$_REQUEST['type'], $_REQUEST['price']))->generatePrepayId();
            $result = (new WechatPay($_REQUEST['id'],$_REQUEST['type'], $_REQUEST['price']))->getPayResponse($_REQUEST['tele'],$_REQUEST['key']);
            echo json_encode($result);
        }else{
            echo FAILED(100,'请求错误');
        }
        break;
	default:
		break;
	}
?>