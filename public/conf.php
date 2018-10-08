<?php
//核心配置文件
define('KEY','konglf');
define('ADMIN','admin_init');
//配置信息
$options = [
    'debug'  => true,//调试模式
    'token'  => 'konglf',
    // 'aes_key' => null, // 可选
	'server' => '127.0.0.1',	//网站/数据库ip
    'admin'  => 'antitired',	//数据库用户名
    'password'  => 'antit2016',	//数据库用户密码
	'database' => 'RunningMiner', 	//数据库名
    'APP_ID' => 'wx4d0c0ffedf4a51cc',//微信AppID
    'MCH_KEY'=>'fitminer12345678FITMINER12345678',//微信商户key
    'MCH_ID'=>'1514357161',//微信商户号
    'Notify_Url'=>'http://paysdk.weixin.qq.com/notify.php'//'http://www.antit.top:8001/fitback/index.php'
];

//模块配置
$modules = [
	'db' => ['rq'=>'modules/DataBase/index.php',//数据库模块
			'lib'=>'modules/DataBase/DBManager.php'],
	'va' => ['rq'=>'modules/Validate/index.php',//验证码模块
			'lib'=>'modules/Validate/ValidateManager.php'],
	'us' => ['rq'=>'modules/User/index.php',//用户管理模块(管理员权限未实现)
			'lib'=>'modules/User/UserManager.php'],
	'ke' => ['rq'=>'modules/KeyChain/index.php',//密钥模块
			'lib'=>'modules/KeyChain/KeyChain.php'],
	'or' => ['rq'=>'modules/Order/index.php',//订单管理模块
			'lib'=>'modules/Order/OrderManager.php'],
	'md' => ['rq'=>'modules/MinerDef/index.php',//矿机货架模块
			'lib'=>'modules/MinerDef/MinerDefManager.php'],
	'mi' => ['rq'=>'modules/Miner/index.php',//挖矿模块
			'lib'=>'modules/Miner/MinerManager.php'],
	'id' => ['rq'=>'modules/Identity/index.php',//实名认证模块
			'lib'=>'modules/Identity/IdentityManager.php'],
    'wi'=>['rq'=>'modules/Withdraw/index.php',//提现申请模块
        'lib'=>'modules/Withdraw/WithdrawManager.php']
];

//矿机订单
/*$orderprefix=[
	'mini' => 1,//迷你矿机
	'mid' => 2,//人气矿机
	'max' => 3,//精品矿机
	'ultra' => 4 //超级矿机
];*/

//错误配置
$fallbacks = [
	'1' => "已存在:#FALLTEXT#",
	'2' => "验证码错误",
	'3' => "未获取验证码",
	'4' => "还有#FALLTEXT#秒才能获取验证码",
	'5' => "密匙过期,需重新进行验证",
	'6' => "不存在密钥,需进行验证",
	'10' => "密钥错误,访问失败",
	'11' => "注册失败",
	'12' => "已经存在该用户,不能注册",
	'13' => "不存在该用户,不能登录",
	'14' => "订单创建错误",
	'15' => "错误的矿机类型",
	'16' => "验证码失效",
	'17' => "订单更新失败",
	'18' => "矿机建立失败",
	'19' => "无更新参数",
	'20' => "矿机信息更新失败",
	'21' => "矿机信息删除失败",
	'22' => "找不到矿机信息",
	'23' => "矿机信息已经存在",
	'24' => "矿机信息获取失败",
	'25' => "矿机价格信息校验失败,订单失效",
	'26' => "矿机订单已失效",
	'27' => "该类矿机已售罄",
	'28' => "矿机订单已超过支付时间",
	'29' => "没有购买矿机",
	'30' => "添加矿机失败",
	'31' => "查找矿机失败",
	'32' => "矿机还未生效",
	'33' => "矿机购买信息校验错误",
	'34' => "矿机信息更新失败",
	'35' => "没有生效的矿机",
	'36' => "挖矿信息更新失败",
	'37' => "已经实名认证",
	'38' => "实名认证审核中",
    '39' => "实名认证信息提交错误",
    '40' => "实名认证信息更新错误",
    '41' => "用户未通过实名认证",
    '42' => "提现申请提交失败",
    '43' => "提现请求获取失败",
    '44' => "不存在该用户",
    '45' => "不存权限#FALLTEXT#",
    '46' => "权限不匹配",
    '47' => "授权过时",
    '48' => "权限查询错误",
    '49' => "用户权限不足",
    '50' => "权限更新失败",
    '51' => "实名认证信息获取失败",
    '52' => "提现请求更新失败",
	'53' => "该类型矿机已经被购买",
    '54' => "订单获取失败",
    '55' => "正在处理的提现次数超过上限",
    '56' => "账户余额不足",
    '57' => "提现请求已处理完成",
    '58' => "支付请求失败",
	'98' => "模块不存在",
	'99' => "请求错误",
	'100' => "参数错误:#FALLTEXT#"
];

//权限级别定义
$permissions = [
    'admin'=>10,//超级管理员
    'manage'=>5,//管理员
    'user'=>1//用户
];

//数据库配置
$tables = [
	'tUser' => [
		'name'=>'user',
		'command'=> "CREATE TABLE `#DBName#` ( `tele` TEXT NOT NULL , `openid` TEXT NOT NULL , `time` INT NOT NULL , `uuid` TEXT NOT NULL , `state` TEXT NOT NULL , PRIMARY KEY (`tele`(11))) ENGINE = InnoDB DEFAULT CHARSET=UTF8;",
        'default'=>[
            'admin'=>[
                'tele'=>'10000000000',
                'openid'=>'',
                'time'=>time(),
                'uuid'=>'-1',
                'state'=> ADMIN
            ]
        ]
	],
	'tValidate'=>[
		'name' => 'validate',
		'command'=> "CREATE TABLE `#DBName#` ( `tele` TEXT NOT NULL , `code` TEXT NOT NULL , `time` INT NOT NULL , PRIMARY KEY (`tele`(11))) ENGINE = InnoDB DEFAULT CHARSET=UTF8;"
	],
	'tMiner'=>[
		'name' => 'miner',
		'command' => "CREATE TABLE `#DBName#` ( `tele` TEXT NOT NULL , `mtype` TEXT NOT NULL , `tmcount` DOUBLE NOT NULL ,`mcount` DOUBLE NOT NULL ,`steps` INT NOT NULL , `cdays` INT NOT NULL ,`ltimes` INT NOT NULL , `vday` INT NOT NULL , PRIMARY KEY (`tele`(11))) ENGINE = InnoDB DEFAULT CHARSET=UTF8;"
	],
	'tMinerDef'=>[
		'name' => 'minerdef',
		'command' => "CREATE TABLE `#DBName#` ( `mtype` TEXT NOT NULL ,`mname` TEXT NOT NULL ,`level` INT NOT NULL , `daylimt` DOUBLE NOT NULL , `mtotal` INT NOT NULL , `msell` INT NOT NULL , `mprice` FLOAT NOT NULL ,`mdisplay` BOOLEAN NOT NULL, PRIMARY KEY (`mtype`(20))) ENGINE = InnoDB DEFAULT CHARSET=UTF8;"
	],
	'tAuth'=>[
		'name'=>'authkey',
		'command' =>"CREATE TABLE `#DBName#` ( `tele` TEXT NOT NULL , `time` INT NOT NULL , `rand` INT NOT NULL , `auth` TEXT NOT NULL , PRIMARY KEY (`tele`(11))) ENGINE = InnoDB DEFAULT CHARSET=UTF8;",
        'default'=>[
            'admin'=>[
                'tele'=>'10000000000',
                'time'=>($tadmin = time()+86400*1000),
                'rand'=>'12345',
                'auth'=>sha1('10000000000'.$tadmin.'123456')
            ]
        ]
	],
	'tOrder'=>[
		'name' => 'order',
		'command' => "CREATE TABLE `#DBName#` ( `id` TEXT NOT NULL , `state` ENUM('SUCCESS','PAYMENT','CANCEL','INVALID') NOT NULL , `tele` TEXT NOT NULL , `mtype` TEXT NOT NULL ,`price` INT NOT NULL , `ctime` INT NOT NULL , `ptime` INT NOT NULL , `paccess` TEXT NOT NULL , PRIMARY KEY (`id`(30))) ENGINE = InnoDB DEFAULT CHARSET=UTF8;"
	],
	'tIdentity'=>[
		'name'=>'identity',
		'command'=> "CREATE TABLE `#DBName#` ( `tele` TEXT NOT NULL , `rname` TEXT NOT NULL , `imgurl` TEXT NOT NULL , `state` ENUM('NONE','SUBMIT','VERIFY','FAILED','SUCCESS') NOT NULL , `cadress` TEXT NOT NULL , PRIMARY KEY (`tele`(11))) ENGINE = InnoDB DEFAULT CHARSET=UTF8;"
	],
    'tWithdraw'=>[
        'name'=>'withdraw',
        'command'=>"CREATE TABLE `#DBName#` ( `id` TEXT NOT NULL , `tele` TEXT NOT NULL , `fit` DOUBLE NOT NULL , `state` ENUM('SUBMIT','SUCCESS','FAILED') NOT NULL , `cadress` TEXT NOT NULL ,`ctime` INT NOT NULL , PRIMARY KEY (`id`(30))) ENGINE = InnoDB DEFAULT CHARSET=UTF8;"
    ]
];


ini_set('date.timezone','Asia/Shanghai');
?>