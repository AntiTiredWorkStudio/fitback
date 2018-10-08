<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);

LIB('ke');
LIB('or');
LIB('md');

class MinerManager extends DBManager{
	public $Miners = null;
	public function MinerManager(){
		parent::__construct();

        try{
            $result = MinerSellInfo::GetAllSellsInfo();
            if(isset($result['miners'])){
                $this->Miners = $result['miners'];
            }else{
                $this->Miners = null;
            }
        }catch(Exception $err){

        }
	}



	//获取用户最高级的矿机订单
	private function GetUserHighestLevelMinerOrder($tele,$auth){
		$ODM = new OrderManager();
		$result = $ODM->GetOrders($tele,$auth);
		$targetOrder = null;
		$cLevel = 0;

		if(!$result || empty($result['orders'])){
            return null;
        }
		
		foreach($result['orders'] as $key=>$value){
			if($value['state'] == 'SUCCESS'){
				if(isset($this->Miners[$value['mtype']])){
					if(($this->Miners[$value['mtype']]['level'])>$cLevel){
						$cLevel = $this->Miners[$value['mtype']]['level'];
						$targetOrder = $value;
					}
				}
			}
		}
		if(!empty($targetOrder)){
			return $targetOrder;
		}else{
			return null;
		}
	}

	//fit的变化量
    public function DeltaFit($tele,$fit){
        $digData = $this->GetDigAdmin($tele);
		file_put_contents("withdraw.txt",json_encode($digData));
        if($digData['code'] != 0){
            return $digData;
        }
        //已包含矿机,更新矿机信息
        $selectCondition = [
            'tele'=>$tele,//手机号
            '_logic'=>' '
        ];

        $updatePars = [
            'tmcount'=>$digData['tmcount']+$fit
        ];
		
        $updateResult = $this->UpdateDataToTable($GLOBALS['tables']['tMiner']['name'],$updatePars,$selectCondition);
        if($updateResult){
            return RESPONDINSTANCE("0");
        }else{
            return RESPONDINSTANCE("34");
        }
    }

	public function SetFit($tele,$fit){

        //已包含矿机,更新矿机信息
        $selectCondition = [
            'tele'=>$tele,//手机号
            '_logic'=>' '
        ];

        $updatePars = [
            'tmcount'=>$fit
        ];
        $updateResult = $this->UpdateDataToTable($GLOBALS['tables']['tMiner']['name'],$updatePars,$selectCondition);
        if($updateResult){
            return RESPONDINSTANCE("0");
        }else{
            return RESPONDINSTANCE("34");
        }
    }

	//计算挖矿量
	public function CaculateMine($stepsByDay,$mtype){
		$realSteps = ($stepsByDay>10000)?10000:$stepsByDay;
		$awardRate = $realSteps/10000;
		$awardMine = $this->Miners[$mtype]['daylimt'];
		return ($awardRate < 1.0)?(($awardRate*$awardMine)/2.0):$awardMine;
	}

    //获取挖矿信息(下载步数数据)
    private function GetDigAdmin($tele){
            $selectCondition = [
                'tele'=>$tele,//手机号
                '_logic'=>' '
            ];
            $result = $this->SelectDataFromTable($GLOBALS['tables']['tMiner']['name'],$selectCondition);
            if(empty($result)){
                return RESPONDINSTANCE('35');//没有生效的矿机
            }else{
                $backResult = RESPONDINSTANCE('0');
                $arr = mysql_fetch_array($result);
                $backResult['mtype'] = $arr['mtype'];
                $backResult['mcount'] = $arr['mcount'];
                $backResult['steps'] = $arr['steps'];
                $backResult['cdays'] = $arr['cdays'];
                $backResult['tmcount'] = $arr['tmcount'];
                //$backResult['ordered'] = $this->GetUserHighestLevelMinerOrder($tele,$auth);
                return $backResult;
            }
    }

    //获取挖矿信息(下载步数数据)
	public function GetDig($tele,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		if($result['result'] == 'true'){
			$selectCondition = [
					'tele'=>$tele,//手机号
					'_logic'=>' '
				];
			$result = $this->SelectDataFromTable($GLOBALS['tables']['tMiner']['name'],$selectCondition);
			if(empty($result)){
				return RESPONDINSTANCE('35');//没有生效的矿机
			}else{
				$backResult = RESPONDINSTANCE('0');
				$arr = mysql_fetch_array($result);
				$backResult['mtype'] = $arr['mtype'];
				$backResult['mcount'] = $arr['mcount'];
				$backResult['steps'] = $arr['steps'];
				$backResult['cdays'] = $arr['cdays'];
                $backResult['tmcount'] = $arr['tmcount'];
                $backResult['ordered'] = $this->GetUserHighestLevelMinerOrder($tele,$auth);
				return $backResult;
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}
	
	//挖矿(提交步数数据)
	public function Dig($steps,$tele,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		if($result['result'] == 'true'){
			$selectCondition = [
					'tele'=>$tele,//手机号
					'_logic'=>' '
				];
			$result = $this->SelectDataFromTable($GLOBALS['tables']['tMiner']['name'],$selectCondition);

			//echo $result;

            $arr = mysql_fetch_array($result);

            if($arr['steps']>$steps || $steps<0 || ($steps-$arr['steps'])<0){
                $steps = $arr['steps'];
//			   return RESPONDINSTANCE('10');
            }
           /* if(!$arr){//查询不到矿机
                return RESPONDINSTANCE('29');
            }*/
           $tStamp = PRC_TIME();

			if(!$arr || empty($arr) || DAY($tStamp) < $arr['vday']){
                return RESPONDINSTANCE('35');//没有生效的矿机
			}else{
				//$arr = mysql_fetch_array($result);
				//var_export($result);


				$mineCount = $this->CaculateMine($steps,$arr['mtype']);
				$updatePars = [
					'steps'=>$steps,
					'mcount'=>$mineCount,
                    'ltimes'=>$tStamp
				];
				$updResult = $this->UpdateDataToTable($GLOBALS['tables']['tMiner']['name'],$updatePars,$selectCondition);
				if($updResult){
					$backResult = RESPONDINSTANCE('0');
					$backResult['mtype'] = $arr['mtype'];
					$backResult['mcount'] = $mineCount;
					$backResult['steps'] = $steps;
					$backResult['cdays'] = $arr['cdays'];
					$backResult['tmcount'] = $arr['tmcount'];

                    $initResult = null;
                    if(DAY($tStamp)> DAY($arr['ltimes'])){
                        file_put_contents("newday.txt",DAY($tStamp).'>'.DAY($arr['ltimes']));
                        $initResult = $this->Init($tele,$auth);
                    }

					if(!empty($initResult)){
                        $backResult['init'] = $initResult;
                    }
					return $backResult;
				}else{
					return RESPONDINSTANCE('34');
				}
			}
		}else{
			return RESPONDINSTANCE('10');
		}
	}
	
	//初始化矿机
	public function Init($tele,$auth){
		
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		//file_put_contents('MM_Init.txt',json_encode($result));
		if($result['result'] == 'true'){
			$existCondition = [
					'tele'=>[
					'var'=>$tele
				]
			];
			$exist = $this->ExistRowInTable($GLOBALS['tables']['tMiner']['name'],$existCondition);
            $targetOrder = $this->GetUserHighestLevelMinerOrder($tele,$auth);
            if(empty($targetOrder)){
                return RESPONDINSTANCE('29');//没有购买矿机
            }
			$tStamp = PRC_TIME();
            $iscreated = false;
		    if(!$exist){//有订单没有矿机,创建矿机信息至数据库

                $insertContent = [
                    'tele'=>$tele,//手机号
                    'mtype'=>$targetOrder['mtype'],//矿机类型
                    'tmcount'=>0,//该矿机的总挖矿数量
                    'mcount'=>0,//挖矿数量
                    'steps' =>0,//当日步数
                    'cdays'=>0,//连续登陆天数
                    'ltimes'=>$tStamp,//最后操作时间戳
                    'vday'=>(DAY($targetOrder['ptime'])+1)//激活日期
                ];
                $insResult = $this->InsertDataToTable($GLOBALS['tables']['tMiner']['name'],$insertContent);
                if(!$insResult){
                    return RESPONDINSTANCE('30');//插入失败
                }
                $iscreated = true;
                /*else{
                    $insertContent['create'] = "true";
                    return array_merge(RESPONDINSTANCE('0'),$insertContent);
                }*/
            }



				//已包含矿机,更新矿机信息
				$selectCondition = [
					'tele'=>$tele,//手机号
					'_logic'=>' '
				];
				
				/*查找当前矿机*/
				
				$selectResult = $this->SelectDataFromTable($GLOBALS['tables']['tMiner']['name'],$selectCondition);
				if($selectResult){
					$today = DAY($tStamp);
					
					$selectArray = mysql_fetch_array($selectResult);

					//echo $today.' => '.$selectArray['vday']
                    //file_put_contents("vdat.txt",$today.'<'.$selectArray['vday']);
					if($today < $selectArray['vday']){//未到激活日期
                        $unusageBackMsg = RESPONDINSTANCE('32');//未激活标识
                        $unusageBackMsg['order'] = $targetOrder;//订单信息
                        $unusageBackMsg['mtype'] = $selectArray['mtype'];//未激活的矿机类型
                        $unusageBackMsg['vday'] = DAY2TIME($selectArray['vday']);//激活时间
                        return $unusageBackMsg;//矿机未生效
                    }

					//已经激活

					$BackMsg = RESPONDINSTANCE('0');
					$BackMsg['create'] = $iscreated?'true':'false';
                    $BackMsg['order'] = $targetOrder;
                    $lastDay = DAY($selectArray['ltimes']);
					//echo ($today-1)*86400 . '</br>';
					//echo $selectArray['ltimes'];
					//echo $lastDay.' => '.$today;
					//file_put_contents("init.txt","init:".$lastDay.'<=>'.$today);
					if($lastDay<$today){//距离上次操作时间过去1天以上，本次操作是否在新的一天

						$BackMsg['new'] = 'true';//检查是否有升级，并且到升级了时间

                        //矿机购买信息校验错误
/*						$targetOrder = $this->GetUserHighestLevelMinerOrder($tele,$auth);
						if(empty($targetOrder)){
							return RESPONDINSTANCE('33');
						}*/
						
						$updatePars = [];
						//矿机类型有变动并且到达生效时间
						if($selectArray['mtype']!=$targetOrder['mtype'] && DAY($tStamp)> DAY($targetOrder['ptime'])){
							//更新矿机信息
							$updatePars['mtype'] = $targetOrder['mtype'];
                            $updatePars['vday'] = DAY($targetOrder['ptime']);

							$BackMsg['mtype'] = $targetOrder['mtype'];
							$BackMsg['newminer'] = 'true';
						}else{
							$BackMsg['newminer'] = 'false';
						}
						
						$mcount = $this->CaculateMine($selectArray['steps'],$selectArray['mtype']);
						
						//提取信息（步数）
						$extractData = [
							'tele' => $tele,
							'day' => DAY($selectArray['ltimes']),
							'steps' => $selectArray['steps'],
							'mtype' => $selectArray['mtype'],
							'mcount' => $mcount   //$selectArray['mcount']
						];
						/*
						* 此处应将extractData插入至记录表
						*/

						//日常更新
						$cdays = (($today - $lastDay) == 1 && $selectArray['steps']>=10000)?($selectArray['cdays']+1):0;
						
						$updatePars['steps'] = 0;
						$updatePars['mcount'] = 0;
						$updatePars['tmcount'] = $selectArray['tmcount']+$mcount;
						$updatePars['ltimes'] = $tStamp;
						$updatePars['cdays'] = $cdays;

                        $updateResult = $this->UpdateDataToTable($GLOBALS['tables']['tMiner']['name'],$updatePars,$selectCondition);

                        if(!$updateResult){
                            return RESPONDINSTANCE('34');//更新错误
                        }

                        //返回的信息
						$BackMsg['mtype'] = $targetOrder['mtype'];//新矿机类型
						$BackMsg['steps'] = 0;//新一天的步数
						$BackMsg['mcount'] = 0;//新一天的挖矿量
						$BackMsg['tmcount'] = $selectArray['tmcount']+$mcount;//挖矿总量
						$BackMsg['ltimes'] = $tStamp;//最后操作时间
						$BackMsg['day'] =  DAY($tStamp);//当前日期
						$BackMsg['cdays'] = $cdays;//连续登陆天数
                        $BackMsg['vday'] = DAY2TIME((isset($updatePars['vday'])?$updatePars['vday']:$selectArray['vday']));//矿机激活日期
						$BackMsg['lastDay'] = $extractData;//前一天数据

						
						return $BackMsg;
					}else{
						$BackMsg['new'] = 'false';
						$BackMsg['vday'] = DAY2TIME($selectArray['vday']);
						$BackMsg['mtype'] = $selectArray['mtype'];
                        $BackMsg['newminer'] = 'false';
                        $BackMsg['steps'] = $selectArray['steps'];
						return $BackMsg;
					}
				}else{
					return RESPONDINSTANCE('31');//获取失败
				}

		}else{
			return RESPONDINSTANCE('10');
		}
	}

}

?>