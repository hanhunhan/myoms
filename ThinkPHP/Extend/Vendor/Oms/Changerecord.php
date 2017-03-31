<?php
/**
 +------------------------------------------------------------------------------
 * Changerecord �����¼��
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-11-02   $
 +------------------------------------------------------------------------------
 */

 
class Changerecord{
	protected $changeTable             ='ERP_CHANGELOG'; 
	protected $model					= null;  //ʵ����һ��model����  
	protected $fields					= null;		//��������ֶ� array
	protected $tablePk                  ='ID';//�������������
	 
	 
	 /**
     +----------------------------------------------------------
     * ���캯�� ȡ��ģ�����ʵ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($pageSize) {
		$this->model = new Model();
		
    }
	 
	/**
     +----------------------------------------------------------
     *��������¼
     +----------------------------------------------------------
     * @access public
	 @param array $opt �������ʽ {TABLE ,COLUMS,BID,CID,CDATE,APPLICANT }
	 @param array $data ����
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
					$options['STATUS'] = 0;//δ���1  �����-1
					$options['APPLICANT'] = $opt['APPLICANT'];
					$options['ISNEW'] = $opt['ISNEW'];
					if($changeid = $this->checkChangeVersion($options) ){
						 $uoptions['VALUEE'] = $options['VALUEE'];
						 $sql = $this->getUpdateChangeSql($uoptions,$changeid);
                        $actionDesc = "���±������";
					}else {
                        $sql = $this->getInsertChangeSql($options);
                        $actionDesc = "��ӱ������";
                    }
					//echo $sql;
					$affect = $this->model->execute($sql);
                    // ��ȡ���������ݶ���ID���������ڱ��е�ID
                    $objId = intval($changeid) > 0 ? $changeid : $affect;
					if(!$affect){   
						$bj = false;
						$this->model->rollback();
                        userLog()->writeLog($objId, $_SERVER["REQUEST_URI"], $actionDesc . 'ʧ��', serialize($options));  // �����־
						break;
					}
				}
				
			}
			if($bj) {
                $this->model->commit();
                userLog()->writeLog($objId, $_SERVER["REQUEST_URI"], $actionDesc . '�ɹ�', serialize($options));  // �����־
            }
		}
		return $bj;
	}
	/**
     +----------------------------------------------------------
     *��ȡ�����¼
     +----------------------------------------------------------
     * @access public
	 * @param array $param �������ʽ {TABLE ,BID,CID }
	  
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
     *��ȡ�����¼
     +----------------------------------------------------------
     * @access public
	 * @param array $param �������ʽ {TABLE ,BID,CID }
	  
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
		//$versionC = $temp['ISNEW']==-1?'[��]':'[ԭ]'.(is_null($temp['ORIVALUEE'])?'δ����':'');
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
     * �����¼��Ч
     +----------------------------------------------------------
     * @access public
	 * @param num $CID  ����汾��
	  
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
					$sql = $this->getUpdataTableSql($v['TABLEE'], $v['COLUMS'],$this->tablePk,$v['BID'], $v['VALUEE'] );//������������ 
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
				

				$sql = $this->getUpdataTableSql($v['TABLEE'], $v['COLUMS'],$this->tablePk,$v['BID'], $v['VALUEE'] );//������������ 
				 
				
				$affect = $this->model->execute($sql);//echo '2-'.$sql;var_dump($affect);
				if(!$affect  ){
					$bj = false;
					$this->model->rollback();
					break;
				}
			}
			
			$affect = $this->setChangeStatus($CID,-1); //���¼�¼���״̬Ϊ��Ч
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
     * �����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ����
     * @param array $options �������ʽ
     * @param boolean $replace �Ƿ�replace
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
     * ���ü�¼״̬
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $cid �汾��
     * @param array $status ״ֵ̬
   
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
     * �������
	 +----------------------------------------------------------
     * @param type $tblname  ����
     * @param type $arrField �ֶ� 
     * @param type $strWhere ��������
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
     * ��֤��ǰ�汾�Ƿ��Ѿ���������
	 +----------------------------------------------------------
     * @param type $tblname  ����
     * @param type $arrField �ֶ� 
     * @param type $strWhere ��������
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
     * SQLָ�ȫ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  SQL�ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function escapeString($str) {
		//$str=mb_detect_encoding($str,array('ASCII','GB2312','GBK','UTF-8')) == 'UTF-8' ? iconv('UTF-8','GBK',$str): $str ;  
		$ac = mb_detect_encoding($str,array('ASCII','GB2312','EUC-CN','GBK','UTF-8'));  
		if(!in_array(strtoupper($ac),array('ASCII','GB2312','EUC-CN','GBK') ) ){
			$str = iconv('UTF-8','GB2312',$str);//ajaxֻ�ܴ�uft-8
		}
		//echo mb_detect_encoding($str,array('ASCII','GB2312','GBK','UTF-8'));
		$str = str_replace("'","''",$str);
		//$str = get_magic_quotes_gpc() ? $str : addslashes($str);
        return $str;
		//return $str;
    }

	/**
     +----------------------------------------------------------
     * value����
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
     * ħ������ �в����ڵĲ�����ʱ��ִ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ������
     * @param array $args ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {

         
    }
	 /**
     +----------------------------------------------------------
     * �Զ���������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name ��������
     * @param $value  ����ֵ
     +----------------------------------------------------------
     */
    public function __set($name ,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
    }

    /**
     +----------------------------------------------------------
     * �Զ�������ȡ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name ��������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return isset($this->$name)?$this->$name:null;
    }
	/**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // �ͷŲ�ѯ
        
    }
}