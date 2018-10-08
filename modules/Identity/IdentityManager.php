<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);

require "Res/autoload.php";
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

LIB('ke');
LIB('us');
class IdentityManager extends DBManager{
    //云存储服务配置
	public $CloudOptions = [
		'ak'=>'d-SztTGFAV7_BX-dKRtM8y1diABoXe1zxCgd-2yi',
		'sk'=>'CWv29dzAFng2KZ15Cf21Pv6FoOoWtB3-nzh1zgJH',
		'domain'=>'http://fit.antit.top',
		'bucket'=>'fitchain'
	];
	
	public function IdentityManager(){
		parent::__construct();
	}

	//生成随机文件名
	function GenerateFileName($tele){
		return sha1($tele.PRC_TIME());
	}

	//获取代币地址,但需要用户必须通过实名认证
	public function GetCoinAdress($tele){
        $SelectCondition = [
            'tele'=>$tele,
            'state'=>'SUCCESS',
            '_logic'=>'AND'
        ];
        $sResult = $this->SelectDataFromTable($GLOBALS['tables']['tIdentity']['name'],$SelectCondition);
        if($sResult){
            $backmsg = RESPONDINSTANCE('0');
            $backmsg['cAdress'] = mysql_fetch_array($sResult)['cadress'];
            return $backmsg;
        }else{
            return RESPONDINSTANCE('41');
        }
    }

    //获取实名认证结果
    public function GetIdentityResult($tele,$auth){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);
        if($result['result'] == 'true') {

            $existCondition = [
                'tele'=>[
                    'var'=>$tele,
                    'log'=>'AND '
                ],
                'state'=>[
                    'var'=>'SUCCESS'
                ]
            ];

            $existResult = $this->ExistRowInTable($GLOBALS['tables']['tIdentity']['name'],$existCondition);

            if($existResult) {

                $selectCondition = [
                    'tele'=>$tele,
                    '_logic' => ' '];

                $result = $this->SelectDataFromTable($GLOBALS['tables']['tIdentity']['name'],$selectCondition);
                $result = mysql_fetch_array($result);
                $backMsg = RESPONDINSTANCE('0');
                $backMsg['address'] = $result["cadress"];

                return $backMsg;//实名认证通过
            }else{
                $backMsg = RESPONDINSTANCE('41');
                $selectCondition = [
                'tele'=>$tele,
                '_logic' => ' '];

                $result = $this->SelectDataFromTable($GLOBALS['tables']['tIdentity']['name'],$selectCondition);
                if($result){
                    $backMsg['state'] = mysql_fetch_array($result)['state'];
                }else{
                    $backMsg['state'] = 'NONE';
                }
                return $backMsg;
            }
        }else{
            return RESPONDINSTANCE('10');//鉴权失败
        }
    }

	//发起实名认证请求
	public function GenerateUploadInfo($tele,$auth){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		
		
		$SelectCondition = [
			'tele'=>$tele,
			//'state'=>'SUCCESS',
			'_logic'=>' '
		];
		
		
		if($result['result'] == 'true'){
			
			$sResult = $this->SelectDataFromTable($GLOBALS['tables']['tIdentity']['name'],$SelectCondition);
			if($sResult){
				$identityState = mysql_fetch_array($sResult)['state'];

				switch($identityState){
					case 'SUCCESS':
						return RESPONDINSTANCE('37');//通过实名认证
					case 'SUBMIT':
                        $this->DeletDataFromTable($GLOBALS['tables']['tIdentity']['name'],$SelectCondition);
                        break;
                    case 'VERIFY':
                        return RESPONDINSTANCE('38');//实名认证正在审核中
					case 'FAILED'://未通过实名认证但是存在申请记录,需要修改认证记录为初态
						$this->DeletDataFromTable($GLOBALS['tables']['tIdentity']['name'],$SelectCondition);
						break;
					case 'NONE':
                    $this->DeletDataFromTable($GLOBALS['tables']['tIdentity']['name'],$SelectCondition);
                        break;
					default:
						break;
				}
			}
			
			
			$auth = new Auth($this->CloudOptions['ak'], $this->CloudOptions['sk']);
			$token = $auth->uploadToken($this->CloudOptions['bucket']);
			$backMsg = RESPONDINSTANCE('0');
			$backMsg['uptoken']=$token;
			$backMsg['domain']=$this->CloudOptions['domain'];
			$backMsg['fileName']=$this->GenerateFileName($tele);
			
			$insertContent = [
				'tele'=>$tele,
				'rname'=>'',
				'imgurl'=>$this->CloudOptions['domain'].'/'.$backMsg['fileName'],
				'state'=>'SUBMIT',
				'cadress'=>'',
			];
			$backMsg['new'] =$this->InsertDataToTable($GLOBALS['tables']['tIdentity']['name'],$insertContent);
			 
			
			return $backMsg;
		}else{
			return RESPONDINSTANCE('10');
		}
	}
	
	//实名认证信息上传结果
	public function SubmitUploadResult($tele,$auth,$realName,$imgUrl,$postfix,$coinAdress){
		$KEM = new KeyChain();
		$result= $KEM->Confirm_Key($tele,$auth);
		if($result['result'] == 'true'){
			$existCondition = [
				'tele'=>[
					'var'=>$tele,
					'log'=>'AND '
				],
				'imgurl'=>[
					'var'=>$imgUrl,
					'log'=>'AND '
				],
				'state'=>[
					'var'=>'SUBMIT'
				]
			];

            $updateCondition = [
                'tele'=>$tele,
                '_logic'=>' '
            ];

            if($postfix == '*'){
                $postfix = '';
            }else{
                $postfix='.'.$postfix;
            }

			$updateArray = [
			    'rname'=>$realName,
                'imgurl'=>$imgUrl.$postfix,
                'cadress'=>$coinAdress,
                'state'=>'VERIFY'
            ];

            $existResult = $this->ExistRowInTable($GLOBALS['tables']['tIdentity']['name'],$existCondition);

			if($existResult){
                $updateResult = $this->UpdateDataToTable($GLOBALS['tables']['tIdentity']['name'],$updateArray,$updateCondition);
                if($updateResult){
                    return RESPONDINSTANCE('0');
                }else{
                    return RESPONDINSTANCE('40');//实名认证信息更新错误
                }
            }else{
                return RESPONDINSTANCE('39');//实名认证信息提交错误
            }

		}else{
			return RESPONDINSTANCE('10');
		}
	}

	//实名认证审核【管理员】
	public function ConfirmIdentityRequest($tele,$auth,$sResult){
        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);

        if(!UserManager::EasyCheckingPermission($tele,'manage')){
            return RESPONDINSTANCE('49');
        }

        if($result['result'] == 'true') {
            $updateCondition = [
                'tele' => $tele,
                '_logic' => ' '
            ];

            $updateArray = [
                'state' => ($sResult?'SUCCESS':'FAILED'),
            ];

            $updateResult = $this->UpdateDataToTable($GLOBALS['tables']['tIdentity']['name'], $updateArray, $updateCondition);
        }else{
            return RESPONDINSTANCE('10');
        }
    }

    //获取所有实名认证申请【管理员】
    public function GetAllIdentityRequest($tele,$auth,$state){

        $KEM = new KeyChain();
        $result= $KEM->Confirm_Key($tele,$auth);

        if(!UserManager::EasyCheckingPermission($tele,'manage')){
            return RESPONDINSTANCE('49');
        }

        if($result['result'] == 'true') {
            $selectCondition = [
                'state' => $state,
                '_logic' => ' '
            ];

            $sresult = $this->SelectDataFromTable($GLOBALS['tables']['tIdentity']['name'], $selectCondition);

            if(!$sresult){
                return RESPONDINSTANCE('51');
            }

            $backMsg = RESPONDINSTANCE('0');
            $backMsg['identity']=[];
            while ($row = mysql_fetch_array($sresult)){
                $backMsg['identity'][$row['tele']] = [
                    'rname'=> $row['rname'],
                    'imgurl'=> $row['imgurl'],
                    'state'=> $row['state'],
                    'cadress'=> $row['cadress'],
                ];
            }
            return $backMsg;

        }else{
            return RESPONDINSTANCE('10');
        }
    }
}
?>