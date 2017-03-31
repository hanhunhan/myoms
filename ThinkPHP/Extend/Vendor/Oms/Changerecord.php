<?php
/**
 +------------------------------------------------------------------------------
 * Changerecord 变更记录类
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-11-02   $
 +------------------------------------------------------------------------------
 */

 
class Changerecord{
	protected $changeTable             ='ERP_CHANGELOG'; 
	protected $model					= null;  //实例化一个model对象  
	protected $fields					= null;		//操作表的字段 array
	protected $tablePk                  ='ID';//操作表的主键　
	 
	 
	 /**
     +----------------------------------------------------------
     * 构造函数 取得模板对象实例
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($pageSize) {
		$this->model = new Model();
		
    }
	 
	/**
     +----------------------------------------------------------
     *保存变更记录
     +----------------------------------------------------------
     * @access public
	 @param array $opt 参数表达式 {TABLE ,COLUMS,BID,CID,CDATE,APPLICANT }
	 @param array $data 数据
	 +----------------------------------------------------------
     * @return id
     +----------------------------------------------------------
     */
	public function saveRecords($opt,$data){
		if($this->fields){
			$this->model->startTrans();
			$bj = true;
			foreach($this->fields as $v){//var_dump($data);
				if($this->escapeString($data[$v])!=$this->escapeString($data[$v.'_OLD'])){
					$options['TABLEE'] = $opt['TABLE'];
					$options['COLUMS'] = $v;//$opt['COLUMS'];
					$options['BID'] = $opt['BID'];
					$options['VALUEE'] =  $data[$v];
					$options['ORIVALUEE'] =  $data[$v.'_OLD'];
					$options['CID'] = $opt['CID'];
					$options['CDATE'] = $opt['CDATE'];
					$options['STATUS'] = 0;//未审核1  已审核-1
					$options['APPLICANT'] = $opt['APPLICANT'];
					$options['ISNEW'] = $opt['ISNEW'];
					if($changeid = $this->checkChangeVersion($options) ){
						 $uoptions['VALUEE'] = $options['VALUEE'];
						 $sql = $this->getUpdateChangeSql($uoptions,$changeid);
                        $actionDesc = "更新变更数据";
					}else {
                        $sql = $this->getInsertChangeSql($options);
                        $actionDesc = "添加变更数据";
                    }
					//echo $sql;
					$affect = $this->model->execute($sql);
                    // 获取操作的数据对象ID，即数据在表中的ID
                    $objId = intval($changeid) > 0 ? $changeid : $affect;
					if(!$affect){   
						$bj = false;
						$this->model->rollback();
                        userLog()->writeLog($objId, $_SERVER["REQUEST_URI"], $actionDesc . '失败', serialize($options));  // 添加日志
						break;
					}
				}
				
			}
			if($bj) {
                $this->model->commit();
                userLog()->writeLog($objId, $_SERVER["REQUEST_URI"], $actionDesc . '成功', serialize($options));  // 添加日志
            }
		}
		return $bj;
	}
	/**
     +----------------------------------------------------------
     *获取变更记录
     +----------------------------------------------------------
     * @access public
	 * @param array $param 参数表达式 {TABLE ,BID,CID }
	  
	 +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
	 public function getRecords($param){
		foreach($this->fields as $v){
			$wheresql =  " where  TABLEE='".$param['TABLE']."' and BID= ".$param['BID']."   "; 
			$wheresql .= " and COLUMS='".$v."'";
			$wheresql .=  $param['CID']?" and CID=".$param['CID']: " order by ID desc ";
			 
			$sql="select VALUEE,ORIVALUEE,ISNEW  from  $this->changeTable $wheresql" ;
			$data = $this->model->query($sql);
			if($data){
				$temp[$v]['VALUEE'] = $data[0]['VALUEE'];
				$temp[$v]['ORIVALUEE'] = $data[0]['ORIVALUEE'];
				$temp[$v]['ISNEW'] = $data[0]['ISNEW'];
			}

		}
		 
		return $temp;
	 }
	 /**
     +----------------------------------------------------------
     *获取变更记录
     +----------------------------------------------------------
     * @access public
	 * @param array $param 参数表达式 {TABLE ,BID,CID }
	  
	 +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
	 public function getFieldRecords($table,$bid,$cid,$colum ){
	 
		$sql="select ID,$colum  from  $table WHERE ID in ( ".$bid." ) " ;
		$data_ori = $this->model->query($sql); 
		$wheresql =  " where  TABLEE='".$table."' and BID in( ".$bid." )  "; 
		$wheresql .= " and COLUMS='".$colum."'";
		$wheresql .=  $cid?" and CID=".$cid: " order by ID desc ";
		 
		$sql="select BID,VALUEE,ORIVALUEE,ISNEW  from  $this->changeTable $wheresql" ;
		$data = $this->model->query($sql); 
		$temp = array();
		foreach($data_ori as $val){
			$temp[$val['ID']]['VALUEE'] = $val[$colum];
			$temp[$val['ID']]['ORIVALUEE'] = $val[$colum];
			//$temp['ISNEW'] = $val['ISNEW'];
		}
		foreach($data as $val){
			$temp[$val['BID']]['VALUEE'] = $val['VALUEE'];
			$temp[$val['BID']]['ORIVALUEE'] = $val['ORIVALUEE'];
			//$temp['ISNEW'] = $val['ISNEW'];
		}
		//$versionC = $temp['ISNEW']==-1?'[增]':'[原]'.(is_null($temp['ORIVALUEE'])?'未设置':'');
		//$orivalue = !is_null($temp['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$temp['ORIVALUEE'].'</span>':'';
		//$defaultValue = $temp['VALUEE']   ;  
		 
		//return $defaultValue.$versionC;
		foreach($temp as $val){
			$VALUEE += $val['VALUEE'];
			$ORIVALUEE += $val['ORIVALUEE'];
			//$temp['ISNEW'] = $val['ISNEW'];
		}
		$res['VALUEE'] = $VALUEE;
		$res['ORIVALUEE'] = $ORIVALUEE;
		return $res;
	 }


	 /**
     +----------------------------------------------------------
     * 变更记录生效
     +----------------------------------------------------------
     * @access public
	 * @param num $CID  变更版本号
	  
	 +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
	 public function setRecords($CID){
		//foreach($this->fields as $v){
			
			 
			$sql="select *  from  $this->changeTable where CID=".$CID ;
			$data = $this->model->query($sql);
			$this->model->startTrans();
			$bj = true;
			
			foreach($data as $v){
				/*if($v['ISNEW']){
					$sql = "UPDATE ".$v['TABLEE']." SET ISVALID = -1 WHERE ID = ".$v['BID'];
				}else{
					$sql = $this->getUpdataTableSql($v['TABLEE'], $v['COLUMS'],$this->tablePk,$v['BID'], $v['VALUEE'] );//更新所操作表 
				}*/
				if($v['ISNEW']){
					$sql = "UPDATE ".$v['TABLEE']." SET ISVALID = -1 WHERE ID = ".$v['BID'];
					$affect1 = $this->model->execute($sql);//echo '1- '.$sql;var_dump($affect1);
					if($affect1 === false){
						$bj = false;
						$this->model->rollback();
						break;
					}
				} 
				

				$sql = $this->getUpdataTableSql($v['TABLEE'], $v['COLUMS'],$this->tablePk,$v['BID'], $v['VALUEE'] );//更新所操作表 
				 
				
				$affect = $this->model->execute($sql);//echo '2-'.$sql;var_dump($affect);
				if(!$affect  ){
					$bj = false;
					$this->model->rollback();
					break;
				}
			}
			
			$affect = $this->setChangeStatus($CID,-1); //更新记录表的状态为生效
			if($affect === false){
				$bj = false;
				$this->model->rollback();
			}
			if($bj ) $this->model->commit();
			

		//}
		
        
		return $bj;
	 }
	 
	 /**
     +----------------------------------------------------------
     * 插入记录
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function getInsertChangeSql( $options=array()  ) {
        
         $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$this->changeTable.' ( TABLEE,COLUMS,BID,VALUEE,ORIVALUEE,CID,CDATE,STATUS,APPLICANT,ISNEW) VALUES (\''.$options['TABLEE'].'\',\''.$options['COLUMS'].'\','.$options['BID'].',\''.$this->escapeString($options['VALUEE']).'\',\''.$this->escapeString($options['ORIVALUEE']).'\','.$options['CID'].',to_date(\''.$options['CDATE'].'\',\'yyyy-mm-dd hh24:mi:ss\'),'.$options['STATUS'].','.$options['APPLICANT'].','.$options['ISNEW'].')';
        return $sql ;  //.= $this->parseLock(isset($options['lock'])?$options['lock']:false);
       
    }
	public function getUpdateChangeSql($options=array(),$changeid){
		
		$intFieldNum = count($options);
        for($i=0; $i<$intFieldNum; $i++) {
            $value =$this->parseValue( $options[key($options)]);
			 
            $strFieldValues .= "," . key($options) ."=" . $value." ";
            next($options);
        }
        $strWhere = ' where ID='.$changeid;
          $sql  = "UPDATE " . $this->changeTable ." SET " . ltrim($strFieldValues, ',')  . $strWhere;
        return $sql;
	}
	 /**
     +----------------------------------------------------------
     * 设置记录状态
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $cid 版本号
     * @param array $status 状态值
   
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
	public function setChangeStatus($cid,$status){
		$sql  = $this->getUpdataTableSql($this->changeTable,'STATUS','CID',$cid,$status); ;
		return $this->model->execute($sql);

	}
	/**
	 +----------------------------------------------------------
     * 更新语句
	 +----------------------------------------------------------
     * @param type $tblname  表名
     * @param type $arrField 字段 
     * @param type $strWhere 更新条件
	 +----------------------------------------------------------
     * @return string
	 +----------------------------------------------------------
     */
    public  function getUpdataTableSql($tblname, $Field,$Pk,$BID, $VALUEE ){
		$VALUEE =$this->parseValue( $VALUEE);
        $strWhere = " WHERE $strWhere " ;
        $sql  = "UPDATE " . $tblname ." SET ".$Field."="." ".$VALUEE."  where $Pk=".$BID ;
        return $sql;
    }
	/**
	 +----------------------------------------------------------
     * 验证当前版本是否已经插入数据
	 +----------------------------------------------------------
     * @param type $tblname  表名
     * @param type $arrField 字段 
     * @param type $strWhere 更新条件
	 +----------------------------------------------------------
     * @return num
	 +----------------------------------------------------------
     */
    public  function  checkChangeVersion($options) {
        $sql  = "select ID from $this->changeTable where TABLEE='".$options['TABLEE']."' and COLUMS='".$options['COLUMS']."'  and BID=".$options['BID']." and CID=".$options['CID']." ";
		$data =  $this->model->query($sql);
		return $data[0]['ID'];

       
    }
	  /**
     +----------------------------------------------------------
     * SQL指令安全过滤
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  SQL字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function escapeString($str) {
		//$str=mb_detect_encoding($str,array('ASCII','GB2312','GBK','UTF-8')) == 'UTF-8' ? iconv('UTF-8','GBK',$str): $str ;  
		$ac = mb_detect_encoding($str,array('ASCII','GB2312','EUC-CN','GBK','UTF-8'));  
		if(!in_array(strtoupper($ac),array('ASCII','GB2312','EUC-CN','GBK') ) ){
			$str = iconv('UTF-8','GB2312',$str);//ajax只能传uft-8
		}
		//echo mb_detect_encoding($str,array('ASCII','GB2312','GBK','UTF-8'));
		$str = str_replace("'","''",$str);
		//$str = get_magic_quotes_gpc() ? $str : addslashes($str);
        return $str;
		//return $str;
    }

	/**
     +----------------------------------------------------------
     * value分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $value
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseValue($value) {
        if( is_string($value) && $this->checkDateIsValid($value)){
			$value = 'to_date(\''.$this->escapeString($value).'\',\'yyyy-mm-dd hh24:mi:ss\')';
		}elseif(is_string($value)) {
            $value = '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value   =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
			$value ='\''.$this->escapeString(implode(',',$value)).'\'';
           // $value   =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_null($value)){
            $value   =  'null';
        }
        return $value;
    }
	function checkDateIsValid($date)
	{
		 if($date == date('Y-m-d H:i:s',strtotime($date)) || $date == date('Y-m-d',strtotime($date))){
		  return true;
		 }else{
		  return false;
		 }
	 
	}
	 /**
     +----------------------------------------------------------
     * 魔术方法 有不存在的操作的时候执行
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method 方法名
     * @param array $args 参数
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {

         
    }
	 /**
     +----------------------------------------------------------
     * 自动变量设置
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name 属性名称
     * @param $value  属性值
     +----------------------------------------------------------
     */
    public function __set($name ,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
    }

    /**
     +----------------------------------------------------------
     * 自动变量获取
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name 属性名称
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return isset($this->$name)?$this->$name:null;
    }
	/**
     +----------------------------------------------------------
     * 析构方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // 释放查询
        
    }
}