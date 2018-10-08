<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);

LIB('ke');

class ValidateManager extends DBManager{

	//生成验证码
	public function GenerateCode($tele){

		$code = rand(100000,999999);//生成验证码

		/*测试用
		*/
		if($GLOBALS['options']['debug']){
			$code = 123456;
		}
		
		//var_dump($result);
		/*
		 *  追加发送短信验证码
		 *
		*/
		
		$timeStamp = PRC_TIME();

		$content = [
			'tele'=>$tele,
			'code'=>$code,
			'time'=>$timeStamp
		];
		
		$ExistCondition = [
			'tele'=>[
			'var'=>$tele
			]
		];
		
		$SelectCondition = [
			'tele'=>$tele,
			'_logic' => 'AND'
		];
		
		if($this->ExistRowInTable($GLOBALS['tables']['tValidate']['name'],$ExistCondition)){
			$pars = mysql_fetch_array($this->SelectDataFromTable($GLOBALS['tables']['tValidate']['name'],$SelectCondition));
			if((PRC_TIME() - $pars['time'])>60){
				$this->DeletDataFromTable($GLOBALS['tables']['tValidate']['name'],$SelectCondition);
			}else{
				return RESPONDINSTANCE('4',60-(PRC_TIME() - $pars['time']));
			}
		}
		$result = $this->InsertDataToTable($GLOBALS['tables']['tValidate']['name'],$content);

		if(!$GLOBALS['options']['debug']){
			$sresult = $this->nowapi_call($tele,$code);//发送验证码
		}else{
			$sresult = true;
		}
		
		$content['code'] = 0;
		$content['result'] = $result;
		$content['sresult'] = $sresult;
		$content['time'] = $timeStamp;
		
		return $content;
	}

	//检查验证码
	public function ConfirmCode($tele,$code){
		$condition = [
			'tele'=>[
			'var'=>$tele
			]
		];
		
		$exist = $this->ExistRowInTable($GLOBALS['tables']['tValidate']['name'],$condition);
		
		
		$condition = [
			'tele'=>$tele,
			'_logic' => 'AND'
		];
		
		$resultInstance = null;
		
		if($exist){
			$pars = mysql_fetch_array($this->SelectDataFromTable($GLOBALS['tables']['tValidate']['name'],$condition));
			
			if($pars['code'] == $code){
				if((PRC_TIME() - $pars['time']>900)){
					$resultInstance = RESPONDINSTANCE('16');//验证码失效
				}else{
					$resultInstance = RESPONDINSTANCE(0);//验证成功
				}
				$this->DeletDataFromTable($GLOBALS['tables']['tValidate']['name'],$condition);
				//echo 0;
			}else{
				$resultInstance = RESPONDINSTANCE(2);//验证码错误
			}
		}else{
			$resultInstance = RESPONDINSTANCE(3);//还未获取验证码
		}
		return $resultInstance;
	}

    //检查验证码并生成KeyChain
	public function ConfirmCodeAndGenerateKeychain($tele,$code){
		$result = $this->ConfirmCode($tele,$code);
		if($result['result'] == 'true'){
			$KEM = new KeyChain();
			$gResult = $KEM->Update_KeyChain($tele);
			foreach($gResult as $key=>$value){
				$result[$key] = $value;
			}
		}
		return $result;
	}
	
	public function ValidateManager(){
		parent::__construct();
	}

	//发送验证码
	function nowapi_call($tele,$code){
		$a_parm = [];
		$a_parm['app']='sms.send';
		$a_parm['tempid']='51517';
		$a_parm['param']=urlencode('code='.$code);
		$a_parm['phone']=$tele;
		$a_parm['appkey']='16194';
		$a_parm['sign']='6bafcd948b55092641cb9023fb986359';
		$a_parm['format']='json';
		
		if(!is_array($a_parm)){
			return false;
		}

		$a_parm['format']=empty($a_parm['format'])?'json':$a_parm['format'];
		$apiurl=empty($a_parm['apiurl'])?'http://api.k780.com/?':$a_parm['apiurl'].'/?';
		unset($a_parm['apiurl']);
		foreach($a_parm as $k=>$v){
			$apiurl.=($k.'='.$v.'&');
		}
		$apiurl=substr($apiurl,0,-1);

		if(!$callapi=file_get_contents($apiurl)){
			return false;
		}
		//format
		if($a_parm['format']=='base64'){
			$a_cdata=unserialize(base64_decode($callapi));
		}elseif($a_parm['format']=='json'){
			if(!$a_cdata=json_decode($callapi,true)){
				return false;
			}
		}else{
			return false;
		}
		//array
		if($a_cdata['success']!='1'){
			echo $a_cdata['msgid'].' '.$a_cdata['msg'];
			return false;
		}
		return $a_cdata['result'];
	}
}
?>