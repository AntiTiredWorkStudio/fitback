<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);

LIB('ke');//鉴权
LIB('us');//权限
class VersionManager extends DBManager{

    //生成app版本密匙
    public static function GenerateAppSecretKey($version,$onlineTime,$urlAndroid,$urlios){
        return sha1($version.'-'.$onlineTime.'-'.$urlAndroid.'-'.$urlios);
    }

    //current版本是否高于target版本
    public static function IsVersionHigher($current,$target){
        $tArr = explode('.',$target);
        $cArr = explode('.',$current);

        if($cArr[0]>99 || $cArr[1]>99 || $cArr[2]>99 || $tArr[0]>99 || $tArr[1]>99 || $tArr[2]>99){
            return false;
        }

        $currentSize = $cArr[0]*10000 + $cArr[1]*100 + $cArr[2];
        $targetSize = $tArr[0]*10000 + $tArr[1]*100 + $tArr[2];

        return $currentSize > $targetSize;

    }

    //版本是否一致
    public static function IsVersion($current,$target){
        $tArr = explode('.',$target);
        $cArr = explode('.',$current);

        if($cArr[0]>99 || $cArr[1]>99 || $cArr[2]>99 || $tArr[0]>99 || $tArr[1]>99 || $tArr[2]>99){
            return false;
        }

        $currentSize = $cArr[0]*10000 + $cArr[1]*100 + $cArr[2];
        $targetSize = $tArr[0]*10000 + $tArr[1]*100 + $tArr[2];

        return $currentSize == $targetSize;
    }

    public $versionList;
    public $highestVersion;

    public function VersionManager(){
		parent::__construct();
        $result = $this->SelectDataFromTable($GLOBALS['tables']['tVersion']['name'],[]);
        if($result) {
            $this->versionList = [];
            $this->highestVersion = "0.0.0";
            while ($row = mysql_fetch_array($result)) {

                $this->versionList[$row['version']] = [];
                foreach ($row as $key => $value) {
                    if(is_numeric($key)){
                        continue;
                    }
                    if(self::IsVersionHigher($row['version'],$this->highestVersion) && $row['state']=="ONLINE"){
                        $this->highestVersion = $row['version'];
                    }
                    $this->versionList[$row['version']][$key] = $value;
                }
            }
        }
       // echo json_encode(VersionManager::IsVersionHigher('1.11.0','1.1.21'));
	}

    //检查版本
	public function CheckVersion($version,$appkey){
        $seleCondition = [
            'version'=>$version,
            '_logic'=>' '
        ];
        $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tVersion']['name'],$seleCondition);
        $sarray = mysql_fetch_array($sresult);

        $lowversionMsg = RESPONDINSTANCE('63');
        $lowversionMsg['tVersion'] = $this->versionList[$this->highestVersion];
        if($sarray) {
            $backMsg = RESPONDINSTANCE('0');
            $targetkey = VersionManager::GenerateAppSecretKey($sarray['version'],$sarray['url_android'],$sarray['url_ios'],$sarray['ptime']);
            if($appkey == $targetkey){
                if(self::IsVersion($version,$this->highestVersion)){
                    return $backMsg;
                }else {
                    if ($this->versionList[$version]['state'] == "ONLINE") {
                        $backMsg['tVersion'] = $this->versionList[$this->highestVersion];
                        return $backMsg;
                    }else{
                        return $lowversionMsg;
                    }
                }
            }else{
                return $lowversionMsg;
            }
            return $backMsg;
        }else{
            return $lowversionMsg;
        }
    }

	//生成版本密钥
	public function GenerateAppVersionKey($tele,$auth,$version){
        $KEM = new KeyChain();

        $result= $KEM->Confirm_Key($tele,$auth);
        /*
        *		在此校验管理员key
        */
        if(!UserManager::EasyCheckingPermission($tele,'manage')){
            return RESPONDINSTANCE('49');
        }

        if($result['result'] == 'true') {
            $seleCondition = [
                'version'=>$version,
                '_logic'=>' '
            ];
            $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tVersion']['name'],$seleCondition);
            $sarray = mysql_fetch_array($sresult);
            if($sarray) {
                $backMsg = RESPONDINSTANCE('0');
                $backMsg['key'] = VersionManager::GenerateAppSecretKey($sarray['version'],$sarray['url_android'],$sarray['url_ios'],$sarray['ptime']);
                return $backMsg;
            }else{
                return RESPONDINSTANCE('62');
            }
        }else{
            return RESPONDINSTANCE('10');
        }
    }

	//设置版本信息
	public function SetVersion($tele,$auth,$version,$pars){
        $KEM = new KeyChain();

        $result= $KEM->Confirm_Key($tele,$auth);
        /*
        *		在此校验管理员key
        */

        if(!UserManager::EasyCheckingPermission($tele,'manage')){
            return RESPONDINSTANCE('49');
        }

        if($result['result'] == 'true') {
            $seleCondition = [
                'version'=>$version,
                '_logic'=>' '
            ];
            $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tVersion']['name'],$seleCondition);
            $sarray = mysql_fetch_array($sresult);
            if($sarray){
                if(empty($pars)){
                    return RESPONDINSTANCE('59');
                }

                $updateCondition = [
                    'version'=>$version,
                    '_logic'=>' '
                ];
                $uresult = $this->UpdateDataToTable($GLOBALS['tables']['tVersion']['name'],$pars,$updateCondition);
                if($uresult){
                    $backmsg = RESPONDINSTANCE('0');
                    $backmsg['action'] = "update";
                    return $backmsg;
                }else{
                    return RESPONDINSTANCE('60');
                }
            }else{
                if(!(isset($pars['url_android']) && isset($pars['url_ios']) && isset($pars['state']))){
                    return RESPONDINSTANCE('59');
                }
                $insertArr = $pars;
                $insertArr['version'] = $version;
                $insertArr['ptime'] = PRC_TIME();
                $iresult = $this->InsertDataToTable($GLOBALS['tables']['tVersion']['name'],$insertArr);
                if($iresult){
                    $backmsg = RESPONDINSTANCE('0');
                    $backmsg['action'] = "add";
                    return $backmsg;
                }else{
                    return RESPONDINSTANCE('61');
                }
            }
        }else{
            return RESPONDINSTANCE('10');
        }
    }
}
	
?>