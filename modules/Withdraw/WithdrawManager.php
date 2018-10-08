<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);

LIB('ke');//鉴权
LIB('id');//实名认证
LIB('mi');//矿机
class WithdrawManager extends DBManager{
	public function WithdrawManager(){
		parent::__construct();
	}

    //生成订单号
    public function GenerateWithdrawID(){

        $timeStamp = PRC_TIME();
        $randVar = 10000+DAY($timeStamp)+$timeStamp%89999;
        return (10000000+$randVar)+$this->CountTableRow($GLOBALS['tables']['tWithdraw']['name']);
    }

    //初始化提现
    public function InitWithdraw($tele,$auth){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);
        if($result['result'] == 'true') {
            $identify = new IdentityManager();
            $idResult = $identify->GetIdentityResult($tele, $auth);
            if($idResult['result'] != 'true'){
                return RESPONDINSTANCE('41');
            }
            $iInfo = $identify->GetCoinAdress($tele, $auth);

            if ($iInfo['result'] == 'true') {
                $miner = new MinerManager();
                $mInfo = $miner->GetDig($tele, $auth);
                $backMsg = RESPONDINSTANCE('0');
                $backMsg['adress'] = $iInfo['cAdress'];
                $backMsg['tmcount'] = $mInfo['tmcount'];
                return $backMsg;
            } else {
                return RESPONDINSTANCE('41');
            }
        }else{
            return RESPONDINSTANCE('10');
        }
    }

    //提交提现申请
    public function SubmitWithdraw($tele,$auth,$fcount,$adress){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);
        if($result['result'] == 'true') {
            $currentWithdraw = $this->GetUnSuccessWithdraw($tele);
            if(isset($currentWithdraw['withdraw'])){
                if(count($currentWithdraw['withdraw'])>=3){
                    return RESPONDINSTANCE('55');
                }
            }
            $MMI = new MinerManager();
            $CurrentMinerInfo = $MMI->GetDig($tele,$auth);
            if($CurrentMinerInfo['code'] != 0){
                return RESPONDINSTANCE('33');
            }

            if(!isset($CurrentMinerInfo['tmcount']) || $fcount>$CurrentMinerInfo['tmcount']){
                return RESPONDINSTANCE('56');
            }else{
                $substract = $MMI->SetFit($tele,$CurrentMinerInfo['tmcount']-$fcount);
                if($substract['code'] != 0){
                    return $substract;
                }
            }

            $identify = new IdentityManager();
            $iInfo = $identify->GetIdentityResult($tele, $auth);

            if ($iInfo['result'] != 'true') {
                return RESPONDINSTANCE('41');
            }
            $wID = $this->GenerateWithdrawID();
            $timeStamp = PRC_TIME();
            $insertArray = [
                'id'=>$wID,
                'tele'=>$tele,
                'fit'=>$fcount,
                'state'=>'SUBMIT',
                'cadress'=>$adress,
                'ctime'=>$timeStamp
            ];
            $insertResult = $this->InsertDataToTable($GLOBALS['tables']['tWithdraw']['name'],$insertArray);
            if($insertResult){
                $backMsg = RESPONDINSTANCE('0');
                $backMsg["id"] = $wID;
                return $backMsg;
            }else{
                return RESPONDINSTANCE('42');
            }
        }else{
            return RESPONDINSTANCE('10');
        }
    }

    //获取单个提现记录(管理员用)
    private function GetSingleWithdrawAdmin($id){
        $conditionArray = [
            'id' => $id,
            '_logic'=>' '
        ];
        $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tWithdraw']['name'],$conditionArray);
		$withdraws = mysql_fetch_array($sresult);
        if($withdraws) {
            $backMsg = RESPONDINSTANCE('0');
            $backMsg['withdraw'] = $withdraws;
            return $backMsg;
        }
        else{
            return RESPONDINSTANCE('43');
        }
    }
    //获取单个提现记录
    public function GetSingleWithdraw($tele,$auth,$id){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);
        if($result['result'] == 'true') {
            $conditionArray = [
                'tele' => $tele,
                'id' => $id,
                '_logic'=>'AND'
            ];
            $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tWithdraw']['name'],$conditionArray);
            if($sresult) {
                $backMsg = RESPONDINSTANCE('0');
                $backMsg['withdraw'] = mysql_fetch_array($sresult);
                return $backMsg;
            }
            else{
                return RESPONDINSTANCE('43');
            }
        }else{
            return RESPONDINSTANCE('10');
        }
    }

    //获取正在处理的提现请求
    private function GetUnSuccessWithdraw($tele){

            $conditionArray = [
                'tele' => $tele,
                '_logic'=>' '
            ];
            $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tWithdraw']['name'],$conditionArray);
            if($sresult) {
                $backMsg = RESPONDINSTANCE('0');
                $backMsg['withdraw'] = [];
                while ($row = mysql_fetch_array($sresult)) {
                    if($row['state'] != "SUBMIT"){
                        continue;
                    }
                    $backMsg['withdraw'][$row['id']] = [
                        'fit'=>$row['fit'],
                        'state'=>$row['state'],
                        'cadress'=>$row['cadress'],
                        'ctime'=>$row['ctime']
                    ];
                }
                return $backMsg;
            }
            else{
                return RESPONDINSTANCE('43');
            }
    }

    //获取提现记录
    public function GetWithdraw($tele,$auth){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);

        if($result['result'] == 'true') {
            $conditionArray = [
                'tele' => $tele,
                '_logic'=>' '
            ];
            $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tWithdraw']['name'],$conditionArray);
            if($sresult) {
                $backMsg = RESPONDINSTANCE('0');
                $backMsg['withdraw'] = [];
                while ($row = mysql_fetch_array($sresult)) {
                    $backMsg['withdraw'][$row['id']] = [
                        'fit'=>$row['fit'],
                        'state'=>$row['state'],
                        'cadress'=>$row['cadress'],
                        'ctime'=>$row['ctime']
                    ];
                }
                return $backMsg;
            }
            else{
                return RESPONDINSTANCE('43');
            }
        }else{
            return RESPONDINSTANCE('10');
        }
    }

    //完成提现【管理员】
    public function FinishedWithdraw($tele, $auth, $id, $wresult){
            $KEM = new KeyChain();
            $result= $KEM->Confirm_Key($tele,$auth);

            if(!UserManager::EasyCheckingPermission($tele,'manage')){
                return RESPONDINSTANCE('49');
            }

            if($result['result'] == 'true') {
                $updateArray = [
                    'state'=>($wresult?'SUCCESS':'FAILED')
                ];
                $conditionArray = [
                    'id'=>$id,
                    '_logic'=>' '
                ];

                $sWithdraw = $this->GetSingleWithdrawAdmin($id);

                if($sWithdraw['withdraw']['state'] != "SUBMIT"){
                    return RESPONDINSTANCE('57');
                }

                if($updateArray['state'] == "FAILED"){

					
                    if($sWithdraw['code'] == '0' && isset($sWithdraw['withdraw']) && !empty($sWithdraw['withdraw'])){
                        $MMI = new MinerManager();
                        $dResult = $MMI->DeltaFit($sWithdraw['withdraw']['tele'],$sWithdraw['withdraw']['fit']);
                        if($dResult['code'] != '0'){
                            return $dResult;
                        }
                    }else{
                        return $sWithdraw;
                    }
                }

                $uresult = $this->UpdateDataToTable($GLOBALS['tables']['tWithdraw']['name'], $updateArray,$conditionArray);
                if ($uresult) {
                    return RESPONDINSTANCE('0');
                }else{
                    return RESPONDINSTANCE('52');
                }
            }else{
                return RESPONDINSTANCE('10');
            }
    }
}
	
?>