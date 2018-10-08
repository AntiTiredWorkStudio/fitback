<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);

LIB('ke');
LIB('md');




class ShoppingController{

    public $enableCsrfValidation = false;
    /*
     配置参数
   */
    protected $config = array(
        'appid' => "wx4d0c0ffedf4a51cc",//"wx4d0c0ffedf4a51cc",                  /*微信开放平台上的应用id*/
        'mch_id' => "1514357161",//"1514357161",                 /*微信申请成功之后邮件中的商户id*/
        'api_key' => "fitminer12345678FITMINER12345678",//"fitminer12345678FITMINER12345678",                /*在微信商户平台上自己设定的api密钥 32位*/
        'notify_url' => 'http://www.antit.top/a/wepay.php'             /*自定义的回调程序地址id*/
    );
    public function actionPays(){

        header("Content-type: text/html; charset=utf-8");
        //$bm_number = 'Cs123456';                //订单ID
        //$uid='123456';                          //uid
        //$price='1';                                //价格
        //$reannumb = $this->randomkeys(4).time().$uid.$this->randomkeys(4);   //获取随机的订单号
        $order_id = time()+rand(10000,99999);   //这里是我接收的APP的订单ID
       /* if($order_id == 0){
            json_encode('非法数据',0);
        }
//        }
        $order = D('order');
        $order_info = $order->where('order_id="'.$order_id.'"')->find(); //通过订单ID吧订单信息查出来
        //建立请求
        //show_bug($order_info);*/
        $out_trade_no = $order_id;
        $price = 1;   //付款金额
        //$body = $order_info['order_name'];  //商品详情
        //$order_sn = I('order_sn');
        $response = $this->getPrePayOrder('商品订单支付',$out_trade_no,$price);
        var_export($response) ;

        //$x = $this->getOrder($response['prepay_id']);   //返回给客户的二次签名
        //exit(json_encode(array('response'=>$response,'x'=>$x,'status'=>1)));     //x返回的是APP需要的数据
    }
    //获取预支付订单
    public function getPrePayOrder($body, $out_trade_no, $total_fee){

        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $notify_url = $this->config["notify_url"];
        $onoce_str = $this->getRandChar(32);
        $data["appid"] = $this->config["appid"];
        $data["body"] = $body;
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["notify_url"] = $notify_url;
        $data["out_trade_no"] = $out_trade_no;
        $data["spbill_create_ip"] = "117.100.89.178";//$this->get_client_ip();
        $data["total_fee"] = $total_fee*100;
        $data["trade_type"] = "APP";
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        var_export($data);
        $xml = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);
        //将微信返回的结果xml转成数组
        return $this->xmlstr_to_array($response);
    }

    //执行第二次签名，才能返回给客户端使用
    public function getOrder($prepayId){

        $data["appid"] = $this->config["appid"];

        $data["noncestr"] = $this->getRandChar(32);;

        $data["package"] = "Sign=WXPay";

        $data["partnerid"] = $this->config['mch_id'];

        $data["prepayid"] = $prepayId;

        $data["timestamp"] = time();

        $s = $this->getSign($data, false);

        $data["sign"] = $s;

        return $data;

    }

    /*
        生成签名
    */
    function getSign($Obj)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[strtolower($k)] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo "【string】 =".$String."</br>";
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$this->config['api_key'];
        //echo "<textarea style='width: 50%; height: 150px;'>$String</textarea> <br />";
        //签名步骤三：MD5加密
        $result_ = strtoupper(md5($String));
        return $result_;
    }

    /**

     *    作用：产生随机字符串，不长于32位

     */

    public function randomkeys($length)
    {

        $pattern = '1234567890123456789012345678905678901234';

        $key = null;

        for ($i = 0; $i < $length; $i++) {

            $key .= $pattern{mt_rand(0, 30)};    //生成php随机数

        }

        return $key;

    }

    //获取指定长度的随机字符串
    function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    //数组转xml
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    //post https请求，CURLOPT_POSTFIELDS xml格式
    function postXmlCurl($xml,$url,$second=30)
    {
        //初始化curl
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data)
        {
            curl_close($ch);
            return $data;
        }
        else
        {
            $error = curl_errno($ch);
            //echo "curl出错，错误码:$error"."<br>";
            return json_encode(array('code'=>$error,'status'=>0));
            //return false;
        }
    }

    /*
        获取当前服务器的IP
    */
    function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

    //将数组转成uri字符串
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        $reqPar=null;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
    xml转成数组
     */
    function xmlstr_to_array($xmlstr) {

        //将XML转为array
        return json_decode(json_encode(simplexml_load_string($xmlstr, 'SimpleXMLElement',LIBXML_NOCDATA)), true);

    }
    function domnode_to_array($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if(isset($child->tagName)) {
                        $t = $child->tagName;
                        if(!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    }
                    elseif($v) {
                        $output = (string) $v;
                    }
                }
                if(is_array($output)) {
                    if($node->attributes->length) {
                        $a = array();
                        foreach($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if(is_array($v) && count($v)==1 && $t!='@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

}






class WechatPay{

    public $minerType;
    public $minerPrice;
    public $App_ID;
    public $Mhc_ID;
    public $App_Key;
    public $notify_Url;
    public $order_id;
    public function __construct($orderid,$mtype,$mprice){
        $this->minerPrice = $mprice;
        $this->minerType  = $mtype;
        $this->order_id = $orderid;
        $this->App_ID = $GLOBALS['options']['APP_ID'];
        $this->App_Key = $GLOBALS['options']['MCH_KEY'];
        $this->Mhc_ID = $GLOBALS['options']['MCH_ID'];
        $this->notify_Url = $_SERVER["REMOTE_ADDR"];

    }

    function generateNonce()
    {
        return md5(uniqid('', true));
    }

    public function getPayResponse($tele,$auth){

        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);
        $backMsg = RESPONDINSTANCE('0');
        if($result['result'] == 'true') {

            $prepay_Result = $this->generatePrepayId();
            if($prepay_Result['result']!='true'){
                return $prepay_Result;
            }
            $prepay_id = $prepay_Result['prepay_id'];
            $response = array(
                'appid' => $this->App_ID,
                'partnerid' => $this->Mhc_ID,
                'prepayid' => $prepay_id,
                'package' => 'Sign=WXPay',
                'noncestr' => $this->generateNonce(),
                'timestamp' => time(),
            );
            $response['sign'] = $this->calculateSign($response, $this->App_Key);
            $backMsg['pay'] = $response;
            return $backMsg;
        }else{
            $backMsg = RESPONDINSTANCE('10');
            return $backMsg;
        }
    }

    public function generatePrepayId()
    {
        $params = array(
            'appid'            => $this->App_ID,
            'mch_id'           => $this->Mhc_ID,
            'nonce_str'        => $this->generateNonce(),
            'body'             => 'FitMiner',
            'out_trade_no'     => time(),
            'total_fee'        => $this->minerPrice,
            'spbill_create_ip' => $this->notify_Url,//$_SERVER["REMOTE_ADDR"],
            'notify_url'       => "http://www.antit.top/fitback/index.php",
            'trade_type'       => 'APP',
        );

        $params['sign'] = $this->calculateSign($params, $this->App_Key);

        $xml = $this->getXMLFromArray($params);


        $result =  $this->https_post("https://api.mch.weixin.qq.com/pay/unifiedorder",$xml);

        //file_put_contents("xml.txt",$result);

        $xml = simplexml_load_string($result);

        if($xml->return_code != "SUCCESS"){
            $errmsg = RESPONDINSTANCE('58');
            $errmsg['error']['return_code'] = (string)$xml->return_code;
            $errmsg['error']['return_msg'] = (string)$xml->return_msg;
            return $errmsg;
        }


        $backMsg = RESPONDINSTANCE('0');
        $backMsg['prepay_id'] = (string)$xml->prepay_id;
        //file_put_contents("xml.txt",$xml->return_code);

        return $backMsg;
    }

    function calculateSign($arr, $key)
    {
        ksort($arr);

        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $k != "key" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        //var_export($arr);
        //echo $buff;
        file_put_contents("buff.txt",$buff);

        $result = strtoupper(md5($buff . "&key=" . $key));

        return $result;
    }
    /**
     * Get xml from array
     */
    function getXMLFromArray($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val) {
            if (is_numeric($val)) {
                $xml =$xml. '<'.$key.'>'.$val.'</'.$key.'>';
            } else {
                //$xml =$xml. '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
                $xml =$xml. '<'.$key.'>'.$val.'</'.$key.'>';
            }
        }
        $xml =$xml. '</xml>';
        return $xml;
    }



    private function https_post($url,$param)
    {
        $ch = curl_init();
        //如果$param是数组的话直接用
        curl_setopt($ch, CURLOPT_URL, $url);
        //如果$param是json格式的数据，则打开下面这个注释
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //         'Content-Type: application/json',
        //         'Content-Length: ' . strlen($param))
        // );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //如果用的协议是https则打开鞋面这个注释
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $data = curl_exec($ch);

        curl_close($ch);
        return $data;

    }

}


class OrderManager extends DBManager{
	
	//生成订单号
	public function GenerateOrderID($minerDef){
        if(empty($minerDef) || !isset($minerDef->level)){
            return -1;
        }
        $timeStamp = PRC_TIME();
        $randVar = 10000+DAY($timeStamp)+$timeStamp%89999;
        return ($minerDef->level*10000000+$randVar)+$this->CountTableRow($GLOBALS['tables']['tOrder']['name']);

    }

	//订单生成
	public function SubmitOrder($tele,$mtype,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
        $minerdef = new MinerSellInfo($mtype);
		$orderid = $this->GenerateOrderID($minerdef);
		if($orderid == -1){
			return RESPONDINSTANCE('15');
		}
		//echo 'state:['.$minerdef->success.' ]';
		//var_export($minerdef);
		//echo 'state:['.$minerdef->success.' ]';
		if(!$minerdef->success){
			
			return RESPONDINSTANCE('14');//矿机信息获取失败
		}
		
		if(!$minerdef->CanSelled()){
			return RESPONDINSTANCE('27');//矿机售罄
		}
		
		$price = $minerdef->mPrice;

        $existCondition = [
            'tele'=>[
                'var'=>$tele,
                'log'=>' AND '
            ],
            'mtype'=>[
                'var'=>$mtype,
                'log'=>' AND '
            ],
            'state'=>[
                'var'=>'SUCCESS'
            ]
        ];
		$exist = $this->ExistRowInTable($GLOBALS['tables']['tOrder']['name'],$existCondition);
		if($exist){
			return RESPONDINSTANCE('53');
		}
			
		/*
		 *应设定为矿机价格(待矿机定义模块开发完毕后获取,这个模块已经快要开发完毕了)
		*/
		$insertContent = [
			'id'=>$orderid,
			'state'=>'PAYMENT',
			'tele'=>$tele,
			'mtype'=>$mtype,
			'price'=>$price,
			'ctime'=>PRC_TIME(),
			'ptime'=>-1,
			'paccess'=>'-'
		];
		
		
		if($result['result'] == 'true'){//取得接口的访问权
			$insResult = $this->InsertDataToTable($GLOBALS['tables']['tOrder']['name'],$insertContent);
			if(!$insResult){
				return RESPONDINSTANCE('14');
			}else{
				$backMsg = RESPONDINSTANCE('0');
				$backMsg['order']=[
					'id'=>$orderid,
					'mtype'=>$mtype,
					'price'=>$price
				];
				return $backMsg;
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}

	//订单支付失败
	public function OrderFailed($id,$tele,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);

		$conditionArray = [
			'id'=>$id,
			'tele' => $tele,
			'_logic'=>'AND'
		];
		if($result['result'] == 'true'){//取得接口的访问权
			

			$orderObject = $this->GetOrder($id);

			$orderState = $orderObject['state'];

			if($orderState != 'PAYMENT'){
				return RESPONDINSTANCE('26');
			}else{
				$payCheckObject = $this->PayCheck($id,$tele,$auth);
				if($payCheckObject['code']!='0'){
					return $payCheckObject;
				}
				$result = $this->DeletDataFromTable($GLOBALS['tables']['tOrder']['name'],$conditionArray);
			}
			if($result){
				return RESPONDINSTANCE('0');//正常失败
			}else{
				return RESPONDINSTANCE('20');//矿机信息更新失败
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}

    //订单支付成功
    public function OrderSuccess($id,$tele,$auth,$price,$paccess){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);
        $updateArray = [
            'state'=>'SUCCESS',
            'ptime'=>PRC_TIME(),
            'paccess'=>$paccess
        ];
        $conditionArray = [
            'id'=>$id,
            'tele' => $tele,
            '_logic'=>'AND'
        ];
        if($result['result'] == 'true'){//取得接口的访问权


            $orderObject = $this->GetOrder($id);

            $orderState = $orderObject['state'];

            $orderMinerType = $orderObject['mtype'];

            if($orderState != 'PAYMENT'){
                return RESPONDINSTANCE('26');
            }else{
                $payCheckObject = $this->PayCheck($id,$tele,$auth);
                if($payCheckObject['code']!='0'){
                    return $payCheckObject;
                }
                $result = $this->UpdateDataToTable($GLOBALS['tables']['tOrder']['name'],$updateArray,$conditionArray);
            }


            $minerdef = new MinerSellInfo($orderMinerType);

            if($price != $minerdef->mPrice){//金额校验失败
                $this->OrderInvalid($id,$tele,$auth);
                return RESPONDINSTANCE('25');
            }
            if($result){
                $minerdef->Selled();
                return RESPONDINSTANCE('0');//正常售出
            }else{
                return RESPONDINSTANCE('20');//矿机信息更新失败
            }
        }else{
            return RESPONDINSTANCE('10');
        }
    }

	//订单取消
	public function OrderCancel($id,$tele,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		$updateArray = [
			'state'=>'CANCEL'
		];
		$conditionArray = [
			'id'=>$id,
			'tele' => $tele,
			'_logic'=>'AND'
		];
		if($result['result'] == 'true'){//取得接口的访问权
			$result = $this->UpdateDataToTable($GLOBALS['tables']['tOrder']['name'],$updateArray,$conditionArray);
			if($result){
				return RESPONDINSTANCE('0');
			}else{
				return RESPONDINSTANCE('17');
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}

	//订单失效
	public function OrderInvalid($id,$tele,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		$updateArray = [
			'state'=>'INVALID'
		];
		$conditionArray = [
			'id'=>$id,
			'tele' => $tele,
			'_logic'=>'AND'
		];
		if($result['result'] == 'true'){//取得接口的访问权
			$result = $this->UpdateDataToTable($GLOBALS['tables']['tOrder']['name'],$updateArray,$conditionArray);
			if($result){
				return RESPONDINSTANCE('0');
			}else{
				return RESPONDINSTANCE('17');
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}

	//根据id获取订单(私有)
	function GetOrder($id){
		$conditionArray = [
			'id' => $id,
			'_logic'=>' '
		];
		$result = $this->SelectDataFromTable($GLOBALS['tables']['tOrder']['name'],$conditionArray);
		if($result){
			return mysql_fetch_array($result);
		}else{
			return [];
		}
	}


    //根据id获取订单
    public function GetSingleOrder($tele,$auth,$id){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);
        if($result['result'] == 'true') {//取得接口的访问权
            $conditionArray = [
                'id' => $id,
                '_logic' => ' '
            ];
            $result = $this->SelectDataFromTable($GLOBALS['tables']['tOrder']['name'], $conditionArray);
            if ($result) {
                $backMsg = RESPONDINSTANCE('0');
                $backMsg['order'] = mysql_fetch_array($result);
                return $backMsg;
            } else {
                return RESPONDINSTANCE('54');
            }
        }else{
            return RESPONDINSTANCE('10');
        }
    }

	//订单支付条件判断(超时及售罄判断)
	public function PayCheck($id,$tele,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		if($result['result'] == 'true'){//取得接口的访问权
			$orderObject = $this->GetOrder($id);

			$orderMinerType = $orderObject['mtype'];
			
			if($orderObject['state'] == "PAYMENT"){
				if((PRC_TIME() - $orderObject['ctime'])>1800){//30分钟内未进行支付
					$this->OrderInvalid($id,$tele,$auth);//超时,订单失效
					return RESPONDINSTANCE('28');
				}
				$minerdef = new MinerSellInfo($orderMinerType);
				if($minerdef->CanSelled()){
					return RESPONDINSTANCE('0');//满足支付条件,可以进行支付
				}else{
					$this->OrderInvalid($id,$tele,$auth);
					return RESPONDINSTANCE('27');//矿机售罄,订单失效
				}
			}else{
				return RESPONDINSTANCE('0');//订单已完结,不涉及支付信息
			}
			
			
		}else{
			return RESPONDINSTANCE('10');
		}
	}
	
	//获取用户所有订单
	public function GetOrders($tele,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		$updateArray = [
			'state'=>'INVALID'
		];
		$conditionArray = [
			'tele' => $tele,
			'_logic'=>' '
		];
		if($result['result'] == 'true'){//取得接口的访问权
			$result = $this->SelectDataFromTable($GLOBALS['tables']['tOrder']['name'],$conditionArray);
			if($result){
				$orders = [];
				while($row = mysql_fetch_array($result))
				{
					if($row['state'] == "PAYMENT" && $this->PayCheck($row['id'],$tele,$auth)['code']!='0'){
						$row['state'] = "INVALID";
					}
					$orders[$row['id']] = [];
					foreach($row as $key=>$value){
						if(is_numeric($key)){
							continue;
						}
						$orders[$row['id']][$key] = $value;
					}
				}
				$backMsg = RESPONDINSTANCE('0');
				$backMsg['orders'] = $orders;
				return $backMsg;
			}else{
				return RESPONDINSTANCE('17');
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}
	
	public function OrderManager(){
		parent::__construct();
	}
}
?>