<?php
error_reporting(E_ALL ^ E_DEPRECATED);

LIB('db');

class KeyChain extends DBManager{
	//用户鉴权key有且仅在验证手机验证码步骤可以手动更新!
	//仅用于验证authkey,并不重新创建
	public function Confirm_Key($tele,$key){
		$ExistCondition = [
			'tele'=>[
			'var'=>$tele
			]
		];
		$SelectCondition = [
			'tele'=>$tele,
			'_logic' => 'AND'
		];
		$tstamp = PRC_TIME();

		//file_put_contents('confirm_key.txt',(($tele=="") == 1).'<=>'.$key);
		/*if(($tele=="") == '1'){
            return RESPONDINSTANCE('10');//key为空
        }*/
		if($this->ExistRowInTable($GLOBALS['tables']['tAuth']['name'],$ExistCondition)){
			$pars = mysql_fetch_array($this->SelectDataFromTable($GLOBALS['tables']['tAuth']['name'],$SelectCondition));
			if($pars['auth'] != $key){
				//$c = $pars['auth'] .'!='. $key.'</br>';
				//echo $c;
				return RESPONDINSTANCE('10');//key不匹配
			}else{
				if(($pars['time'] - $tstamp)>0){
					
					return RESPONDINSTANCE('0');//key正确
				}else{
					return RESPONDINSTANCE('5');//key过时
				}
			}
		}else{
			return RESPONDINSTANCE('6');//没有key
		}
	}
	
	//验证旧版authkey的同时,创建并返回新authkey
	public function Confirm_KeyChain($tele,$key){
		$ExistCondition = [
			'tele'=>[
			'var'=>$tele
			]
		];
		
		$SelectCondition = [
			'tele'=>$tele,
			'_logic' => 'AND'
		];
		$tstamp = PRC_TIME();
		if($this->ExistRowInTable($GLOBALS['tables']['tAuth']['name'],$ExistCondition)){
			$pars = mysql_fetch_array($this->SelectDataFromTable($GLOBALS['tables']['tAuth']['name'],$SelectCondition));
			if($pars['auth'] != $key){
				return RESPONDINSTANCE('10');
			}else{
				if(($pars['time'] - $tstamp)>0){
					$this->DeletDataFromTable($GLOBALS['tables']['tAuth']['name'],$SelectCondition);
					return RESPONDINSTANCE('0','',$this->Generate_KeyChain($tele,$tstamp));
				}else{
					$this->DeletDataFromTable($GLOBALS['tables']['tAuth']['name'],$SelectCondition);
					return RESPONDINSTANCE('5');
				}
			}
		}else{
			return RESPONDINSTANCE('6');
		}
	}

	//更新某用户的密钥
	public function Update_KeyChain($tele){
		$SelectCondition = [
			'tele'=>$tele,
			'_logic' => 'AND'
		];
		$this->DeletDataFromTable($GLOBALS['tables']['tAuth']['name'],$SelectCondition);
		return $this->Generate_KeyChain($tele,PRC_TIME());
	}

	//生成密钥
	public function Generate_KeyChain($tele,$tstamp){
		$time = $tstamp+604800;
		$rand = rand(10000,99999);
		$arr = [
			"tl" => $tele,
			"ts" => $time,
			"rd" => $rand
		];
		$keychain = sha1($tele.$time.$rand);
		
		
		$content = [
			'tele'=>$tele,
			'time'=>$time,
			'rand'=>$rand,
			'auth'=>$keychain
		];
		
		$contentReturn = [
			'tele'=>$tele,
			'time'=>$time,
			'rand'=>$rand
		];
		
		$result = $this->InsertDataToTable($GLOBALS['tables']['tAuth']['name'],$content);
		

		return $contentReturn;
	}
	
	public function KeyChain(){
		parent::__construct();
	}
}

?>