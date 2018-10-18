<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);

LIB('ke');
LIB('us');

//矿机销售管理器
class MinerSellInfo extends DBManager{

	public static function GetAllSellsInfo(){
		$DBM = new DBManager();
		$sresult = $DBM->SelectDataFromTable($GLOBALS['tables']['tMinerDef']['name'],null);
		if($sresult){
			$backMsg = RESPONDINSTANCE('0');
			while($row = mysql_fetch_array($sresult)){
				$backMsg['miners'][$row['mtype']]=[];
				$backMsg['miners'][$row['mtype']]['mname']=$row['mname'];
				$backMsg['miners'][$row['mtype']]['daylimt']=$row['daylimt'];
				$backMsg['miners'][$row['mtype']]['mtotal']=$row['mtotal'];
				$backMsg['miners'][$row['mtype']]['msell']=$row['msell'];
				$backMsg['miners'][$row['mtype']]['mprice']=$row['mprice'];
				$backMsg['miners'][$row['mtype']]['mdisplay']=$row['mdisplay'];
				$backMsg['miners'][$row['mtype']]['level']=$row['level'];
			}
			return $backMsg;
		}else{
			return RESPONDINSTANCE('24');
		}
	}

	public $mType = '';
	public $mName = '';
	public $mPrice = 0;
	public $mTotal = 0;
	public $mSell = 0;
	public $level = 0;
	public $success = false;

	//获取存货
	public function GetSurplus(){
		return $this->mTotal-$this->mSell;
	}
	//还有存货卖出
	public function CanSelled(){
		return $this->mSell<$this->mTotal;
	}
	//卖出
	public function Selled(){
		
		$updateArray = [
			'msell'=>($this->mSell+1),
		];
		$conditionArray = [
			'mtype'=>$this->mType,
			'_logic'=>' '
		];
		$result = $this->UpdateDataToTable($GLOBALS['tables']['tMinerDef']['name'],$updateArray,$conditionArray);
		if($result){
			return true;
		}else{
			return false;
		}
	}

	/*public function __construct(){
		$this->mType = '';
		$this->success = false;
		$this->mName = '';
		$this->mPrice = 0;
		$this->mTotal = 0;
		$this->mSell = 0;
		$this->level = 0;
	}*/

	public function MinerSellInfo($mtype){
		parent::__construct();
		$conditionArray = [
			'mtype' => $mtype,
			'_logic'=>' '
		];
		$existCondition = [
			'mtype'=>[
			'var'=>$mtype
			]
		];
		
		$this->mType = $mtype;
		
		$exist = $this->ExistRowInTable($GLOBALS['tables']['tMinerDef']['name'],$existCondition);
		
		if($exist){
			$this->success = true;
		}
		$sresult = $this->SelectDataFromTable($GLOBALS['tables']['tMinerDef']['name'],$conditionArray);
		$sresult = mysql_fetch_array($sresult);
		
		$this->mName = $sresult['mname'];
		$this->mPrice = $sresult['mprice'];
		$this->mTotal = $sresult['mtotal'];
		$this->mSell = $sresult['msell'];
		$this->level = $sresult['level'];
	}
}

class MinerDefManager extends DBManager{

	public function MinerDefManager(){
		parent::__construct();
	}

	/*
	 *
	 *   增加矿机的Level字段  用于判定矿机的优先级 同时用于订单号开头
	 *
	*/

	//获取矿机 display=0 为查询所有 display = 1 为查询正在售卖
	public function GetMinersInfo($tele,$auth,$display){
		$KEM = new KeyChain();
		
		$conditionArray = [
			'mdisplay' => $display,
			'_orderby'=>'level',
			'_orderrule'=>'DESC',
			'_logic'=>' '
		];
		if($display == 0){
			$conditionArray = null;
		}

		
		
		$result= $KEM->Confirm_Key($tele,$auth);
		if($result['result'] == 'true'){
			
			$sresult = $this->SelectDataFromTable($GLOBALS['tables']['tMinerDef']['name'],$conditionArray);
			
			if($sresult){
				$backMsg = RESPONDINSTANCE('0');
				while($row = mysql_fetch_array($sresult)){
					$backMsg['miners'][$row['mtype']]=[];
					$backMsg['miners'][$row['mtype']]['mname']=$row['mname'];
					$backMsg['miners'][$row['mtype']]['daylimt']=$row['daylimt'];
					$backMsg['miners'][$row['mtype']]['mtotal']=$row['mtotal'];
					$backMsg['miners'][$row['mtype']]['msell']=$row['msell'];
					$backMsg['miners'][$row['mtype']]['mprice']=$row['mprice'];
					$backMsg['miners'][$row['mtype']]['mdisplay']=$row['mdisplay'];
					$backMsg['miners'][$row['mtype']]['level']=$row['level'];
				}
				return $backMsg;
			}else{
				return RESPONDINSTANCE('24');
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}

	//查询矿机
	public function GetSingleMinerInfo($mtype,$tele,$auth){
		$KEM = new KeyChain();
		$conditionArray = [
			'mtype' => $mtype,
			'_logic'=>' '
		];
		$existCondition = [
			'mtype'=>[
			'var'=>$mtype
			]
		];
		
		$result= $KEM->Confirm_Key($tele,$auth);
		//var_export($result);
		if($result['result'] == 'true'){
			$exist = $this->ExistRowInTable($GLOBALS['tables']['tMinerDef']['name'],$existCondition);
			if(!$exist){
				return RESPONDINSTANCE('22');
			}
			$result = $this->SelectDataFromTable($GLOBALS['tables']['tMinerDef']['name'],$conditionArray);
			
			if($result){
				$backMsg = RESPONDINSTANCE('0');
				$result = mysql_fetch_array($result);
				$backMsg['miners']=[$result['mtype']=>[]];
				$backMsg['miners'][$result['mtype']]['mname']=$result['mname'];
				$backMsg['miners'][$result['mtype']]['daylimt']=$result['daylimt'];
				$backMsg['miners'][$result['mtype']]['mtotal']=$result['mtotal'];
				$backMsg['miners'][$result['mtype']]['msell']=$result['msell'];
				$backMsg['miners'][$result['mtype']]['mprice']=$result['mprice'];
				$backMsg['miners'][$result['mtype']]['mdisplay']=$result['mdisplay'];
				$backMsg['miners'][$result['mtype']]['level']=$result['level'];
				return $backMsg;
			}else{
				return RESPONDINSTANCE('24');
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}

	//删除矿机信息【管理员】
	public function DeleteMinerInfo($mtype,$tele,$auth){
		$KEM = new KeyChain();
		//$result= ['result'=>"true"];
		$condition = [
			'mtype'=>$mtype,
			'_logic' => 'AND'
		];
		
		$existCondition = [
			'mtype'=>[
			'var'=>$mtype
			]
		];
		
		$result= $KEM->Confirm_Key($tele,$auth);
		/*
		*		在此校验管理员key
		*/

        if(!UserManager::EasyCheckingPermission($tele,'manage')){
            return RESPONDINSTANCE('49');
        }

		if($result['result'] == 'true'){
			$exist = $this->ExistRowInTable($GLOBALS['tables']['tMinerDef']['name'],$existCondition);
			if(!$exist){
				return RESPONDINSTANCE('22');
			}
			$result = $this->DeletDataFromTable($GLOBALS['tables']['tMinerDef']['name'],$condition);
			//var_dump($result);
			if($result){
				return RESPONDINSTANCE('0');
			}else{
				return RESPONDINSTANCE('21');
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}
	
	//修改矿机信息【管理员】
	public function SetMinerInfo($mtype,$tele,$auth,$name=null,$daylim=null,$total=null,$price=null,$display=null,$level=null){
		$KEM = new KeyChain();
		//$result= ['result'=>"true"];
		$result= $KEM->Confirm_Key($tele,$auth);
		/*
		*		在此校验管理员key
		*/

        if(!UserManager::EasyCheckingPermission($tele,'manage')){
            return RESPONDINSTANCE('49');
        }


		$updatePars = [];
		$conditionArray = [
			'mtype'=>$mtype,
			'_logic'=>' '
		];
		
		$existCondition = [
			'mtype'=>[
			'var'=>$mtype
			]
		];
		
		if(!empty($name)){
			$updatePars['mname'] = $name;
		}
		if(!empty($daylim)){
			$updatePars['daylimt'] = $daylim;
		}
		if(!empty($total)){
			$updatePars['mtotal'] = $total;
		}
		if(!empty($price)){
			$updatePars['mprice'] = $price;
		}
		if(!empty($display)){
			$updatePars['mdisplay'] = $display;
		}
		
		if(!empty($level)){
			$updatePars['level'] = $level;
		}
		
		if(empty($updatePars)){
			return RESPONDINSTANCE('19');
		}
		
		if($result['result'] == 'true'){
			$exist = $this->ExistRowInTable($GLOBALS['tables']['tMinerDef']['name'],$existCondition);
			if(!$exist){
				return RESPONDINSTANCE('22');
			}
			$result = $this->UpdateDataToTable($GLOBALS['tables']['tMinerDef']['name'],$updatePars,$conditionArray);
			if($result){
				return RESPONDINSTANCE('0');
			}else{
				return RESPONDINSTANCE('36');
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}
	
	//创建矿机信息【管理员】
	public function CreateMiner($minerType,$minerName,$daylim,$total,$price,$display,$tele,$auth,$level){
		$KEM = new KeyChain();
		//$result= ['result'=>"true"];
		$result= $KEM->Confirm_Key($tele,$auth);
		/*
		*		在此校验管理员key(未实现)
		*/

		if(!UserManager::EasyCheckingPermission($tele,'manage')){
            return RESPONDINSTANCE('49');
        }

		
		$insertContent = [
			'mtype'=>$minerType,
			'mname'=>$minerName,
			'level'=>$level,
			'daylimt'=>$daylim,
			'mtotal'=>$total,
			'msell'=>0,
			'mprice'=>$price,
			'mdisplay'=>$display
		];
		
		$existCondition = [
			'mtype'=>[
			'var'=>$minerType
			]
		];
		
		if($result['result'] == 'true'){
			$exist = $this->ExistRowInTable($GLOBALS['tables']['tMinerDef']['name'],$existCondition);
			if($exist){
				return RESPONDINSTANCE('23');
			}
			$insResult = $this->InsertDataToTable($GLOBALS['tables']['tMinerDef']['name'],$insertContent);
			if(!$insResult){
				return RESPONDINSTANCE('18');
			}else{
				return RESPONDINSTANCE('0');
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}
}

?>