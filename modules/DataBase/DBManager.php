<?php
//引用此页面前需先引用conf.php
error_reporting(E_ALL ^ E_DEPRECATED);
class DBManager{
	//获取配置文件
	public function C(){
		return $GLOBALS['options'];
	}
	//获取数据表
	public function T(){
		return $GLOBALS['tables'];
	}
	public $dbLink = null;

	//创建数据库链接
	public function CreateDBLink(){
		if($this->dbLink == null)
			$this->dbLink = $this->DBLink();
	}

    //关闭数据库链接
	public function Finished(){
		if(!empty($this->dbLink)){
			
			mysql_close($this->dbLink);
		}
	}

	//获取表中行号
	public function CountTableRow($tableName){
		$con = $this->DBLink();
		$sql = 'select count(*) as value from `'.$tableName.'`';
		//file_put_contents('count.txt',$sql);
		$result = mysql_query($sql,$con);
		return mysql_fetch_array($result)[0];
	}

	//判断是否存在数据
	public function ExistRowInTable($tableName,$conditionArray,$closeDBLink = false){
		$con = $this->DBLink();
		$sql = 'SELECT * FROM `'.$tableName.'` WHERE ';
		foreach($conditionArray as $key=>$value){
			$sql = $sql.$key.'= "'.$value['var'].'" '.((isset($value['log']))?$value['log']:'');
		}
		//file_put_contents('sql.txt',$sql);
		$result = mysql_query($sql,$con);
		if($closeDBLink){
			mysql_close($con);
		}
		
		return mysql_fetch_row($result);
	}

	//插入数据
	public function InsertDataToTable($tableName,$array,$closeDBLink = false){
		$con = $this->DBLink();
		$sqlPart0 = 'INSERT INTO `'.$tableName.'`(';			
		$sqlPart1 = ') VALUES (';
		$sqlPart2 = ')';
		$keys = '';
		$values = '';
		foreach($array as $key=>$value){
			$keys = $keys.'`'.$key.'`,';
			$values = $values.'"'.$value.'"'.',';
		}
		$keys = substr($keys, 0, -1);
		$values = substr($values, 0, -1);
		$result = mysql_query($sqlPart0.$keys.$sqlPart1.$values.$sqlPart2,$con);
		
		//file_put_contents('testselect.txt',$sqlPart0.$keys.$sqlPart1.$values.$sqlPart2);
		//echo $sqlPart0.$keys.$sqlPart1.$values.$sqlPart2;
		if($closeDBLink){
			mysql_close($con);
		}
		return $result;
	}

    //更新数据
	public function UpdateDataToTable($tableName,$valArray,$conArray,$closeDBLink = false){
		$hasCond = false;
		$con = $this->DBLink();  
		$sql = 'UPDATE `'.$tableName.'` SET ';			
		
		$cond = ((isset($conArray['_logic']) && $conArray['_logic']=="AND")?(1):(0));
		
		$val = '';
		$logic = ((isset($conArray['_logic']))?$conArray['_logic']:'AND');
		foreach($valArray as $key=>$value){
			$val =$val.' `'.$key.'`="'.$value.'",';
		}
		
		foreach($conArray as $key=>$value){
			if($value=="" || $key=='_logic'){
				continue;
			}
			if($cond=='1' || $cond=='0'){
				$cond = "";
			}
			if(!$hasCond){
				$hasCond = true;
			}
			$cond =$cond.' `'.$key.'`="'.$value.'" '.$logic.' ';
		}
		if($hasCond){
			$cond = substr($cond, 0, (($logic=='AND')?(-4):(-3)));
		}
		$val = substr($val, 0, -1);
		$sql = $sql.$val.' WHERE '.$cond;
		$result = mysql_query($sql,$con);
		//echo $sql.'</br>';
		if($closeDBLink){
			mysql_close($con);
		}
		return $result;
	}

    //删除数据
	public function DeletDataFromTable($tableName,$conArray,$closeDBLink = false){
		$hasCond = false;
		$con = $this->DBLink();  
		$sql = 'DELETE FROM `'.$tableName.'`';			
		
		$cond = ((isset($conArray['_logic']) && $conArray['_logic']=="AND")?(1):(0));
		
		$logic = ((isset($conArray['_logic']))?$conArray['_logic']:'AND');
		
		
		foreach($conArray as $key=>$value){
			if($value=="" || $key=='_logic'){
				continue;
			}
			if($cond=='1' || $cond=='0'){
				$cond = "";
			}
			if(!$hasCond){
				$hasCond = true;
			}
			$cond =$cond.' `'.$key.'`="'.$value.'" '.$logic.' ';
		}
		if($hasCond){
			$cond = substr($cond, 0, (($logic=='AND')?(-4):(-3)));
		}
		$sql = $sql.' WHERE '.$cond;
		$result = mysql_query($sql,$con);
		//echo $sql;
		if($closeDBLink){
			mysql_close($con);
		}
		return $result;
	}

    //查找数据
	public function SelectDataFromTable($tableName,$conArray,$closeDBLink = false,$field='*'){
		$hasCond = false;
		$con = $this->DBLink();  
		$sql = 'SELECT '.$field.' FROM `'.$tableName.'`';	
		

		if(!empty($conArray)){
			$cond = ((isset($conArray['_logic']) && $conArray['_logic']=="AND")?(1):(0));
			
			$logic = ((isset($conArray['_logic']))?$conArray['_logic']:'AND');
			
			
			foreach($conArray as $key=>$value){
			    if(strpos($key, '_') === 0){
			        continue;
                }
				if($value=="" || $key=='_logic'){
					continue;
				}
				if($cond=='1' || $cond=='0'){
					$cond = "";
				}
				if(!$hasCond){
					$hasCond = true;
				}
				$cond =$cond.' `'.$key.'`="'.$value.'" '.$logic.' ';
			}
			if($hasCond){
				$cond = substr($cond, 0, (($logic=='AND')?(-4):(-3)));
			}
		}
		
		if(!empty($conArray)){
			$sql = $sql.' WHERE '.$cond;
		}
		
		if(isset($conArray['_orderby']) && isset($conArray['_orderrule'])){
			
            $sql = $sql.' ORDER BY '.'`'.$conArray['_orderby'].'` '.$conArray['_orderrule'];
        }
		$result = mysql_query($sql,$con);
		if($closeDBLink){
			mysql_close($con);
		}
		return $result;
	}
	
	public function DBManager(){
		$con = $this->DBLink();
		$this->dbLink = $con;
		if(!$con)
		{
		  die('Could not connect: ' . mysql_error());
		}

		if(mysql_query("CREATE DATABASE ".$this->C()['database'],$con))
		{
		  echo "数据库创建</br>";
		}
		else
		{
			if(mysql_errno() != 1007){
				echo "Can not creating database: " . mysql_errno()."</br>";
			}
		}
		mysql_close($con);
	}

    //初始化数据库
	public function InitDB(){
		$link = $this->DBLink();
		foreach($this->T() as $key=>$value){
			if(!$this->ExistTable($value['name'],$link)){
				$real_command = str_replace('#DBName#',$value['name'],$value['command']);
				$r = mysql_query($real_command,$link);
				if($r){
					echo '表 '.$value['name'].' 创建</br>';
				}else{
					echo '表 '.$value['name'].' 创建失败</br>';
				}
			}else{
				echo '表 '.$value['name'].' 存在</br>';
			}

			if(array_key_exists('default',$value)){
                foreach ($value['default'] as $dkey => $dvalue) {
                    if($this->InsertDataToTable($value['name'],$dvalue)) {
                        echo '<div style="white-space:pre">   *初始化数据项[' . $dkey . "]成功</br></div>";
                    }else{
                        echo '<div style="white-space:pre">   *初始化数据项[' . $dkey . "]失败</br></div>";
                    }
			    }
            }
		}
		mysql_close($link);
	}
	
	public function ExistTable($tableName,$con){
		$result =mysql_fetch_row(mysql_query("SHOW TABLES LIKE '".$tableName."' ",$con));
		if($result){
			return true;
		}else{
			return false;
		}
	}

	//快速获取数据库链接
	public function DBLink(){
		$con = mysql_connect("localhost",$this->C()['admin'],$this->C()['password']);
		mysql_set_charset('utf8');
		if($con){
			mysql_select_db($this->C()['database']);
		}
		return $con;
	}
}
?>