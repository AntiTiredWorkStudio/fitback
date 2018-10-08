<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);

LIB('ke');

class UserManager extends DBManager{

    /************************************权限管理**************************************/

    //快速检查用户权限等级
    public static function EasyCheckingPermission($tele,$permissionType){
        return ISSUCCESS((new UserManager())->CheckPermission($tele,$permissionType));
    }

    //生成权限key
    public static function GeneratePermissionKey($tele, $durnationTimeLength, $permissionType){//用户，权限长度

        if(!isset($GLOBALS['permissions'][$permissionType])){
            return RESPONDINSTANCE('45',$permissionType);
        }

        $timestamp = PRC_TIME();
        $tLength = ($durnationTimeLength == -1)?-1:($timestamp+$durnationTimeLength);//-1代表永久
        $data = json_encode([
            'tele'=>$tele,
            'dtime'=>$tLength,
            'permission'=>$permissionType
        ]);

        $result = RESPONDINSTANCE('0');
        $result['pkey'] = base64_encode($data);
        return $result;
    }

    //解码AuthKey base64 => json => array
    private static function DecodePermissionKey($PermissionKey){
        return json_decode(base64_decode($PermissionKey),true);
    }

    //获取权限定义列表
    public function GetPermissionList($tele, $auth){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);
        if(!ISSUCCESS($this->CheckPermission('tele','admin'))){
            return RESPONDINSTANCE('49');
        }
        if($result['result'] == 'true') {//取得接口的访问权
            $backMsg = RESPONDINSTANCE('0');
            $backMsg['permissions']=$GLOBALS['permissions'];
            return $backMsg;
        }else{
            return RESPONDINSTANCE('10');
        }
    }

    //获取用户权限【坚决不能公开】
    public function GetUserPermissions($tele){

            $selectCondition = [
                'tele'=>$tele,
                '_logic'=>' '
            ];
            $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tUser']['name'],$selectCondition);
            if($sresult){//找到用户
                $content = mysql_fetch_array($sresult);
                $timeStamp = PRC_TIME();
                $backMsg = RESPONDINSTANCE('0');
                if(!empty($content['state'])) {//包含权限key
                    $authArray = UserManager::DecodePermissionKey($content['state']);//解码

                    if($authArray['dtime'] != -1 && $authArray['dtime']<$timeStamp){//授权码过期
                        return RESPONDINSTANCE('47');//授权过时
                    }

                    $backMsg['auth'] = $authArray['auth'];

                    $backMsg['dtime'] = $authArray['dtime'];

                    return $backMsg;
                }else {//不包含权限key自动默认为永久user

                    $backMsg['auth'] = 'user';

                    $backMsg['dtime'] = -1;

                    return $backMsg;
                }
            }else{//未找到用户
                return RESPONDINSTANCE('44');//返回用户查询错误
            }
    }

    //检查用户权限（无需验证身份key）
    public function CheckPermission($tele, $cPermissionType){
            $selectCondition = [
                'tele'=>$tele,
                '_logic'=>' '
            ];
            $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tUser']['name'],$selectCondition);
            if($sresult){//找到用户
                $content = mysql_fetch_array($sresult);
                $timeStamp = PRC_TIME();
                if(!empty($content['state'])) {//包含权限key

                    if($content['state'] == ADMIN){
                        return RESPONDINSTANCE('0');//权限验证通过
                    }

                    $authArray = UserManager::DecodePermissionKey($content['state']);//解码

                    if($authArray['dtime'] != -1 && $authArray['dtime']<$timeStamp){//授权码过期
                        return RESPONDINSTANCE('47');//授权过时
                    }

                    try {//判断权限已经被定义
                        $targetLevel = $GLOBALS['permissions'][$cPermissionType];
                        $currentLevel = $GLOBALS['permissions'][$authArray['auth']];
                    }catch (Exception $err){
                        return RESPONDINSTANCE('46');//权限不匹配,验证失败
                    }

                    if ($targetLevel <= $currentLevel) {//判断权限级别,目标级别小于当前用户权限级别
                        return RESPONDINSTANCE('0');//权限验证通过
                    }else{
                        return RESPONDINSTANCE('46');//权限不匹配,验证失败
                    }

                }else {//不包含权限key自动默认为永久user

                    try {//判断权限已经被定义
                        $targetLevel = $GLOBALS['permissions'][$cPermissionType];
                        $currentLevel = $GLOBALS['permissions']['user'];//为空则默认级别为用户级别
                    }catch (Exception $err){
                        return RESPONDINSTANCE('46');//权限不匹配,验证失败
                    }

                    if ($targetLevel <= $currentLevel) {//判断权限级别,目标级别小于当前用户权限级别
                        return RESPONDINSTANCE('0');//权限验证通过
                    }else{
                        return RESPONDINSTANCE('46');//权限不匹配,验证失败
                    }
                }
            }else{//未找到用户
                return RESPONDINSTANCE('44');//返回用户查询错误
            }
    }

    //用户为其他用户授权(用户权限级别需高于目标用户)
    public function UpdatePermission($tele, $auth, $targetTele, $tPermissionType, $durnationTime){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);

        if($result['result'] == 'true') {//取得接口的访问权

            $cAuthMsg = $this->GetUserPermissions($tele);
            $tAuthMsg = $this->GetUserPermissions($targetTele);

            if(ISSUCCESS($cAuthMsg) && ISSUCCESS($tAuthMsg)){//获取用户权限成功（过期会失败）

                $currentLevel = $GLOBALS['permissions'][$cAuthMsg['auth']];

                $targetLevel = $GLOBALS['permissions'][$tAuthMsg['auth']];

                $finalLevel = $GLOBALS['permissions'][$tPermissionType];

                if($targetLevel<$currentLevel && $finalLevel <=$currentLevel){
                    $updateCondition = [
                        'tele'=>$tele,
                        '_logic'=>' '
                    ];

                    $updateArray = [
                        'state'=>self::GeneratePermissionKey($targetTele,$durnationTime,$tPermissionType)
                    ];

                   $updateResult = $this->UpdateDataToTable($GLOBALS['tables']['tUser']['name'],$updateArray,$updateCondition);
                   if($updateResult){//更新结果
                       return RESPONDINSTANCE('0');//更新成功
                   }else{
                       return RESPONDINSTANCE('50');//更新失败
                   }
                }else{
                    return RESPONDINSTANCE('49');//用户权限不足
                }
            }else{//未找到用户
                return RESPONDINSTANCE('48');//返回用户查询错误
            }
        }else{//鉴权失败
            return RESPONDINSTANCE('10');
        }
    }

    /************************************用户管理**************************************/

    //注册
	public function Regist($tele,$auth,$uuid){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		$insertContent = [
			'tele'=>$tele,
			'openid'=>'',
			'time'=>PRC_TIME(),
			'uuid'=>$uuid,
			'state'=>UserManager::GeneratePermissionKey($tele,-1,'user')['pkey']//授予永久用户权限
		];
		
		$existCondition = [
			'tele'=>[
			'var'=>$tele
			]
		];
		
		if($result['result'] == 'true'){//取得接口的访问权
			if($this->ExistRowInTable($GLOBALS['tables']['tUser']['name'],$existCondition)){
				return RESPONDINSTANCE('12');
			}
			$insResult = $this->InsertDataToTable($GLOBALS['tables']['tUser']['name'],$insertContent);
			if(!$insResult){
				return RESPONDINSTANCE('11');
			}else{
				return RESPONDINSTANCE('0');
			}
		}else{//鉴权失败
			return RESPONDINSTANCE($result['code']);
		}
	}

	//每次打开App必经步骤,（登录）
    public function Login($tele,$auth,$uuid){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);

        $SelectCondition = [
            'tele'=>$tele,
            '_logic' => 'AND'
        ];


        $existCondition = [
            'tele'=>[
                'var'=>$tele
            ]
        ];
        /*
        * 更新用户的时间戳(未实现)
        */
        if($result['result'] == 'true'){//取得接口的访问权
            if($this->ExistRowInTable($GLOBALS['tables']['tUser']['name'],$existCondition)){
                $pars = mysql_fetch_array($this->SelectDataFromTable($GLOBALS['tables']['tUser']['name'],$SelectCondition));

                $keypars = mysql_fetch_array($this->SelectDataFromTable($GLOBALS['tables']['tAuth']['name'],$SelectCondition));

                if(($keypars['time'] - PRC_TIME())<172800){
                    /*
                     *	提升密钥时限
                     */
                    $newKey = $KEM->Update_KeyChain($tele);
                    $res = RESPONDINSTANCE('0');
                    foreach($newKey as $key=>$value){
                        $res[$key] = $value;
                    }
                    return $res;
                }

                if($pars['uuid']!=$uuid){
                    $this->UpdateUUID($tele,$uuid);
                    $newKey = $KEM->Update_KeyChain($tele);
                    $res = RESPONDINSTANCE('0');
                    foreach($newKey as $key=>$value){
                        $res[$key] = $value;
                    }
                    return $res;
                }else{
                    return RESPONDINSTANCE('0');
                }
            }else{
                return RESPONDINSTANCE('13');
            }
        }else{//鉴权失败
            return RESPONDINSTANCE($result['code']);
        }
    }

    //更新设备号
    public function UpdateUUID($tele,$uuid){
        $updateArray = [
            'uuid'=>$uuid
        ];
        $conditionArray = [
            'tele'=>$tele,
            '_logic'=>' '
        ];
        $result = $this->UpdateDataToTable($GLOBALS['tables']['tUser']['name'],$updateArray,$conditionArray
        );
        return $result;
    }
	
	public function UserManager(){
		parent::__construct();
	}
}
?>