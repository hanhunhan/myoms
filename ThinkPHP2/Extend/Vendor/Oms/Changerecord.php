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
			foreach($this->fields as $v){var_dump($data);
				if($data[$v]!=$data[$v.'_OLD']){
					$options['TABLEE'] = $opt['TABLE'];
					$options['COLUMS'] = $v;//$opt['COLUMS'];
					$options['BID'] = $opt['BID'];
					$options['VALUEE'] = $this->escapeString( $data[$v]);
					$options['ORIVALUEE'] = $this->escapeString( $data[$v.'_OLD']);
					$options['CID'] = $opt['CID'];
					$options['CDATE'] = $opt['CDATE'];
					$options['STATUS'] = 0;//δ���1  �����-1
					$options['APPLICANT'] = $opt['APPLICANT'];
					$options['ISNEW'] = $opt['ISNEW'];
					if($changeid = $this->checkChangeVersion($options) ){
						 $uoptions['VALUEE'] = $options['VALUEE'];
						 $sql = $this->getUpdateChangeSql($uoptions,$changeid);
					}else $sql = $this->getInsertChangeSql($options);
					$affect = $this->model->execute($sql);
					if(!$affect){ echo $sql;
						$bj = false;
						$this->model->rollback();
						break;
					}
				}
				
			}
			if($bj) $this->model->commit();

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
				if($v['ISNEW']){
					$sql = "UPDATE ".$v['TABLEE']." SET ISVALID = -1 WHERE ID = ".$v['BID'];
				}else{
					$sql = $this->getUpdataTableSql($v['TABLEE'], $v['COLUMS'],$this->tablePk,$v['BID'], $v['VALUEE'] );//������������ 
				}
				
				$affect = $this->model->execute($sql);
				if(!$affect){
					$bj = false;
					$this->model->rollback();
					break;
				}
			}
			
			$affect = $this->setChangeStatus($CID,-1); //���¼�¼���״̬Ϊ��Ч
			if(!$affect){
				$bj = false;
				$this->model->rollback();
			}
			if($bj ) $this->model->commit();
			

		//}
		
        
		return $temp;
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
        
         $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$this->changeTable.' ( TABLEE,COLUMS,BID,VALUEE,ORIVALUEE,CID,CDATE,STATUS,APPLICANT,ISNEW) VALUES (\''.$options['TABLEE'].'\',\''.$options['COLUMS'].'\','.$options['BID'].',\''.$options['VALUEE'].'\',\''.$options['ORIVALUEE'].'\','.$options['CID'].',to_date(\''.$options['CDATE'].'\',\'yyyy-mm-dd hh:mi:ss\'),'.$options['STATUS'].','.$options['APPLICANT'].','.$options['ISNEW'].')';
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
        $strWhere = " WHERE $strWhere " ;
        $sql  = "UPDATE " . $tblname ." SET ".$Field."="."'".$VALUEE."' where $Pk=".$BID ;
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
		$str = iconv('UTF-8','GBK',$str);//ajaxֻ�ܴ�uft-8
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
        if($this->checkDateIsValid($value)){
			$value = 'to_date(\''.$this->escapeString($value).'\',\'yyyy-mm-dd hh:mi:ss\')';
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