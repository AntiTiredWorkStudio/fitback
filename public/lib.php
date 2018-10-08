<?php
//框架公有库(GLOBALS作用域,只用于写方法)
//引用请求接口
function REQUEST($key){
	try{
		if(!isset($GLOBALS['modules'][$key])){
			die(json_encode(RESPONDINSTANCE('99')));
		}
		include_once($GLOBALS['modules'][$key]['rq']);
	}catch(Exception $err){
		die($err);
	}
}

//引用库接口
function LIB($key){
	try{
		if(!isset($GLOBALS['modules'][$key])){
			die(json_encode(RESPONDINSTANCE('98')));
		}
		include_once($GLOBALS['modules'][$key]['lib']);
	}catch(Exception $err){
		die($err);
	}
}

//请求失败
function FAILED($key,$context =''){
	//$GLOBALS['FALLBACKTEXT'] = $contex;
	$result = [];
	if(!isset($GLOBALS['fallbacks'][$key])){
		$result['result'] = 'false';
		$result['code'] = '-1';
		$result['context'] = '没有该类错误:'.$key;
	}
	$result['result'] = 'false';
	$result['code'] = $key;
	$result['context'] = str_replace('#FALLTEXT#',$context,$GLOBALS['fallbacks'][$key]);
	return json_encode($result);
}

//请求成功
function SUCCESS($infoArray){
	$result = [];
	if(!isset($infoArray['result'])){
		$result['result'] = 'true';
	}
	if(!isset($infoArray['code'])){
		$result['code'] = '0';
	}
	if(!isset($infoArray['context'])){
		$result['context'] = '请求成功';
	}
	foreach($infoArray as $key=>$value){
		$result[$key] = $value;
	}
	return json_encode($result);
}

function RESPONDINSTANCE($code = 0,$fallContext='',$infoArray = null){
	$result = [];
	if($code == 0){
		$result = [
			'result'=>'true',
			'code'=>$code,
			'context'=>'请求成功'
		];
	}else{
		$result = [
			'result'=>'false',
			'code'=>$code,
			'context'=>$GLOBALS['fallbacks'][$code]
		];
	}
	
	$result['context'] = str_replace('#FALLTEXT#',$fallContext,$result['context']);
	
	if($infoArray != null){
		foreach($infoArray as $key=>$value){
			$result[$key] = $value;
		}
	}
	return $result;
}

//通过时间戳计算天数
function DAY($tStamp){
    return ($tStamp - $tStamp%86400)/86400;
}

//通过天数计算时间戳
function DAY2TIME($day){
    return $day*86400;
}

//判断通用返回模板的返回结果是否成功
function ISSUCCESS($backMsg){
    return is_array($backMsg) && key_exists('result',$backMsg) && $backMsg['result'];
}

function PRC_TIME(){
    return time()+8*3600;
}
?>