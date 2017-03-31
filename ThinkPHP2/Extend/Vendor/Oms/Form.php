<?php
/**
 +------------------------------------------------------------------------------
 * Form ��������
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-04-15   $
 +------------------------------------------------------------------------------
 */

 //�����ļ�
if (is_file(dirname(__FILE__).'/Field.php')){
	include dirname(__FILE__).'/Field.php';
}else {
	die('Sorry. Not load Field file.');
}
if (is_file(dirname(__FILE__).'/Mycols.php')){
	include dirname(__FILE__).'/Mycols.php';
}else {
	die('Sorry. Not load Mycols file.');
}
if (is_file(dirname(__FILE__).'/Changerecord.php')){
	include dirname(__FILE__).'/Changerecord.php';
}else {
	die('Sorry. Not load Changerecord file.');
}
class Form{
	 
    protected $FORMNO           = 0; //������
	protected $FORMTITLE           = null;//������� GRID/FORM����ı���
	protected $FORMTYPE            = null ;//�������� GRID/FORM
	protected $SQLTEXT            = null ;//����Դ
	protected $PKFIELD            = 'ID' ;//����Դ������
	protected $PKVALUE            = null ;//����Դ������ֵ
	protected $PARENTID            =null; //��ҳ��ID
	protected $EDITABLE            = -1 ;//�ɱ༭ -1 ����Ĭ�ϣ� 0 ��
	protected $ADDABLE            = -1;//������ -1 ����Ĭ�ϣ� 0 ��
	protected $DELABLE            = -1 ;//��ɾ�� -1 ����Ĭ�ϣ� 0 ��
	protected $FILTERABLE            = -1 ;//�ɹ��� -1 ����Ĭ�ϣ� 0 ��
	protected $SORTABLE            = -1 ;//������ -1 ����Ĭ�ϣ� 0 ��
	protected $SHOWDETAIL            = -1 ;//��ʾ��ϸ��Ϣ  -1 ����Ĭ�ϣ� 0 ��
	protected $GRIDMODE            = 1 ;//GRID��ʾ��ʽ ��ʾģʽ   
	//Ĭ�ϣ�1  1-grid���༭/��ʾ��  2-grid����ʾ��+form���༭�� 


	protected $WIDTH            = null ;//������ 
	protected $LABELWIDTH            = '15%' ;//��ǩ��� 
	protected $LOGPATH            = 'log/' ;//��־�ļ�·��-
	protected $INCPATH            = 'inc/' ;//INC·��-
	protected $IMGPATH            = 'img/' ;//ͼƬ·��-
	protected $SAVEERRLOG            = 0 ;//�Ƿ񱣴������־ -1 ����0 ��Ĭ�ϣ�-
	protected $DELCONDITION            = null ;//ɾ������-
	protected $EDITCONDITION            = null ;//�༭����-
	protected $BISQL            = null ;//����ǰִ��SQL-
	protected $AISQL            = null ;//�����ִ��SQL-
	protected $BUSQL            = null ;//����ǰִ��SQL-
	protected $AUSQL            = null ;//���º�ִ��SQL-
	protected $BDSQL            = null ;//ɾ��ǰִ��SQL-
	protected $ADSQL            = null ;//ɾ����ִ��SQL-
	protected $GABTN            = null ;//�����ڹ��ܰ�ť֮���html��䣨grid��ʽ�� 
	protected $GCBTN            = null ;//�滻ԭ�й��ܰ�ť��html��䣨grid��ʽ�� 
	protected $CZBTN            = null ;//�滻ԭ�в�����ť��html��䣨grid��ʽ�� ***����  ������ͨ�� $form->CZBTN = array('%AMOUNT%==23331'=>'<a>����1</a>','%AMOUNT%==2333'=>'<a>����2</a><a>����3</a>');  ���ַ�ʽ���ñ༭ɾ������Ĳ�����ť array('����1'=>'��ʾ���','����2'=>'��ʾ���2')
	protected $SHOWBOTTOMBTN            = -1 ;//��ʾ�ײ���ť 
	protected $FORMAFTERDATA            = null ;//����������֮���html��䣨form��ʽ�� 
	protected $FORMBEFOREDATA            = null ;//����������֮ǰ��html��䣨form��ʽ�� 
	protected $GRIDAFTERDATA            = null ;//����������֮���html��䣨GRID��ʽ�� 
	protected $GRIDBEFOREDATA            = null ;//����������֮ǰ��html��䣨GRID��ʽ�� 
	protected $MAXLENGTH            = null ;//�༭�ı������󳤶� 
	protected $SHOWIMGBTN            = 0 ;//��ʾͼ�ΰ�ť ��ʾΪͼ�ΰ�ť Ĭ�ϣ���-
	protected $LOGERR            = 0 ;//�Ƿ񽫴��󱣴�����־�ļ�
	protected $CHANGEROWS            = -1 ;// �Ƿ���Ը���ÿҳ��ʾ���� Ĭ�ϣ��� 
	protected $SQLTEXTFILTER            = null ;//����Դ��������-
	protected $FORMAFTERBTN            = null ;//�����ڰ�ť֮���html��䣨form��ʽ�� 
	protected $FORMCHANGEBTN            = null ;//�滻ԭ�й��ܰ�ť��html��䣨form��ʽ�� 
	protected $FORMFORWARD            = null ;//FORM�ύ����תurl ***���� ��
	protected $mvarCols					= null;	//�ֶμ���
	protected $model					= null;  //ʵ����һ��model����  
    protected $result            = null ;//ִ�н�� ***���� ��
	protected $iscontinue            = false ;//���json֮���Ƿ����ִ�� ***���� ��
	//protected $userFunc            = null ;//�ص������� ***���� ��
	//protected $userParams           = null ;//�ص��������� ***���� ��
	protected $sqlwhere                =null;//��ѯ����
	protected $children                =null;//��ҳ��
	protected $NOINCREMENT             =null;//�Ƿ�������
	protected $FKFIELD                 =null;//����Դ�Ը����ڵĹ����ֶ�
	protected $SHOWPKFIELD             =-1;//��ʾ�������-1��ʾ  0����ʾ
	protected $SHOWSEQUENCE            =-1;//��ʾ���-1��ʾ  0����ʾ
	protected $SHOWCHECKBOX              =0 ;//��ʾ��ѡ�� -1��ʾ  0����ʾ
	protected $SHOWSTATUSTABLE       = null;//��ʾ״̬��ʾ
	protected $CUSTOMACTION           = null;//�Զ���action��ַ
    protected $hidden_input_arr       = array();//form���ֶζ�������input
    protected $new_td_arr       	= array();//form���ֶ�����µ��У��ɱ༭�У�
	protected $FILTERSQL              = null;//��������
	protected $NOPERATE             = 0;
	protected $changeRecord           = false;//��¼���
	protected $changeRecordVersionId           = null;//��¼����汾id
	protected $FormeditType               = 1;//1�༭״̬ 2ֻ��״̬


	 /**
     +----------------------------------------------------------
     * ���캯�� ȡ��ģ�����ʵ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($pageSize) {
		//$this->FORMNO = 1;
		// $this->mvarCols = new Mycols(Field);
		//$Model = new Model(); 
		//$this->mvarCols->_init($this->FORMNO);
		$this->model = new Model();
		$this->mvarCols = new Mycols(Field);
    }

	/**
	+----------------------------------------------------------
	 * ��������ֵ
	+----------------------------------------------------------
	 * @access public
	+----------------------------------------------------------
	 */
	public function setAttribute($name,$value) {
		if(property_exists($this,$name))
			$this->$name = $value;
	}

	/**
     +----------------------------------------------------------
     *���������
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return id
     +----------------------------------------------------------
     */
	public function saveFormData(){
		$options['table'] = $this->SQLTEXT;
		$data = $_POST; 
		$arr = explode('^',$this->PKFIELD);
		if($_POST[$this->PKFIELD] && $this->NOINCREMENT!='-1'){
			foreach($arr as  $v){
				$condition[] = $v.'='."'".$data[$v]."'";
			} 
			$conditionStr = implode(' and ',$condition);
			$sql = $this->changeRecord ? '' : $this->getUpdataSql($this->SQLTEXT,$data, $conditionStr);
			$Bsql = $this->BUSQL;
			$Asql = $this->AUSQL;
		}else{  
			if(count($arr)==1 )$data[$this->PKFIELD] = 'SEQ_'.$this->SQLTEXT.'.'.nextval;
			if($this->NOINCREMENT=='-1') {$data[$this->PKFIELD] = $_POST[$this->PKFIELD] ; }
			$sql = $this->getInsertSql($data,$options);
			$Bsql = $this->BISQL;
			$Asql = $this->AISQL;
			
		}    //echo $sql;
		//echo $sql = get_magic_quotes_gpc() ? $sql : addslashes($sql);
		$this->model->startTrans();
		$result['BRows'] = $Bsql ? $this->model->execute($Bsql):1;
		$result['numRows'] = $sql ?  $this->model->execute($sql) : 1;//�������ʱ��ֱ�Ӹ���
		$lastId =  $this->model->getLastInsID();
		$result['ARows'] = $Asql ? $this->model->execute($Asql):1;
		if($result['BRows'] && $result['numRows'] && $result['ARows']){
			$this->model->commit();
			$result['status'] = $data[$this->PKFIELD] ? 1 : 2;
			$result['msg'] = g2u('�ɹ�');
			$result['numRows'] = $result['numRows'];
			//$lastId =  $this->model->getLastInsID();
			$result['lastId'] = $lastId;
			if($this->FORMFORWARD){
				if(strstr($this->FORMFORWARD,'?'))$result['forward'] = $this->FORMFORWARD .'&paramId='.$lastId;
				else $result['forward'] = $this->FORMFORWARD .'?paramId='.$lastId;
			}
		}else{
			$this->model->rollback();
			$result['status'] = 0;
			$result['msg'] = g2u('ʧ��');
		}
		if($this->changeRecord){
			$changer = new Changerecord();
			$changer->fields=$this->getAllCols();
			$optt['TABLE'] = $this->SQLTEXT;
			$optt['BID'] = $result['lastId'] ? $result['lastId'] :$data[$this->PKFIELD] ;
			$optt['CID'] = $this->changeRecordVersionId?$this->changeRecordVersionId:1;//����汾id
			$optt['CDATE'] = date('Y-m-d h:i:s');
			$optt['APPLICANT'] = $_SESSION['uinfo']['uid'];
			$optt['ISNEW'] = $sql ? -1 :0;//���� �� �޸�
			$changer->saveRecords($optt,$data);

		}
		$this->result = $result;
		//call_user_func($this->userFunc,$this->userParams);
		echo json_encode($result);
		if(!$this->iscontinue) exit( );
	}
	 
	/**
     +----------------------------------------------------------
     *����GRID������
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return id
     +----------------------------------------------------------
     */
	public function saveGridData(){
		$options['table'] = $this->SQLTEXT;
		$data = $_POST;
		if($data['IDS']){
			$idArr =array_filter(explode(',',$data['IDS']) );
			$arr = $this->getGridCols();
			foreach($idArr as $key=>$val){
				$temp = array();
				foreach($arr as $k=>$v){
					$temp[$v->FIELDNAME] = $data[$val.'_'.$v->FIELDNAME] ;  
				} 
				$sql = $this->getUpdataSql($this->SQLTEXT,$temp," $this->PKFIELD = '$val'");

				$result['upIds'].= $this->model->execute($sql);
			}
			$result['forward'] = $data['LOCATIONURL'];
		}
		if($data['addids']){
			$pkarr = explode('^',$this->PKFIELD);
			for($i=1;$i<=$data['addids'];$i++){
				$arr = $this->getGridCols();
				$temp = array();
				foreach($arr as $k=>$v){
					$temp[$v->FIELDNAME] = $data['new_'.$v->FIELDNAME.$i]  ;  
				} 
				if(count($pkarr)==1 )$temp[$this->PKFIELD] = 'SEQ_'.$this->SQLTEXT.'.'.nextval;
				if($this->NOINCREMENT=='-1') {$temp[$this->PKFIELD] = $_POST[$this->PKFIELD] ; }//������
			 
				$sql = $this->getInsertSql($temp,$options);
				$this->model->execute($sql);
				$result['addIds'].= ','. $this->model->getLastInsID();

			}
			$result['forward'] = $data['LOCATIONURL'];
		}
		$result['status'] = 1;
		$result['msg'] = 'ok';
		$this->result = $result;
		//exit( json_encode($result));
		echo json_encode($result);
		if(!$this->iscontinue) exit( );
	}
  
     /**
     +----------------------------------------------------------
     *ɾ��������
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return json
     +----------------------------------------------------------
     */
    public function delData(){
       $tableName = $this->SQLTEXT;
       $data = $_REQUEST;
       $result= array();
       if ($data['ID']) {
		
		 if(strpos($data['ID'],'^')) {
			 $temp = explode('^',$data['ID']);
			 $arr =array();
			 foreach($temp as $k=>$v){
				 if($k%2==1) $arr[] = $temp[$k-1]."='".$v."'"; 
			 }
			 $strWhere =  implode(' and ',$arr);
		 }else $strWhere = $this->PKFIELD."='{$data['ID']}'";
		 $this->model->startTrans();
		 $result['BRows'] = $this->BDSQL ? $this->model->execute($this->BDSQL):1;
		 $sql = $this->getDeleteSql($tableName,$strWhere);
		 $result['ARows'] = $this->ADSQL ? $this->model->execute($this->ADSQL):1;
         $affect = $this->model->execute($sql);
         if ($result['BRows'] && $affect && $result['ARows']) {
		   $this->model->commit();
           $result['status'] = 'success';
         } else {
		   $this->model->rollback();
           $result['status'] = 'error';
         }
       }
	   $this->result = $result;
       //die(json_encode($result));
	   echo json_encode($result);
	   if(!$this->iscontinue) exit( );
       
    }
    
    /**
	 +----------------------------------------------------------
     * ɾ�����
	 +----------------------------------------------------------
     * @param type $tblname  ����
     * @param type $strWhere ɾ������
	 +----------------------------------------------------------
     * @return string
	 +----------------------------------------------------------
     */
    public function getDeleteSql($tableName,$strWhere = '') {
       $sql = '';
       $strWhere = $strWhere != '' ? " WHERE $strWhere" : '';
       $sql = "DELETE  FROM {$tableName} {$strWhere}";
       return $sql;
    }
     
	/**
	 +----------------------------------------------------------
     * �������
	 +----------------------------------------------------------
     * @param type $tblname  ����
     * @param type $arrField �ֶ�����
     * @param type $strWhere ��������
	 +----------------------------------------------------------
     * @return string
	 +----------------------------------------------------------
     */
    public  function getUpdataSql($tblname, $arrField, $strWhere = '') {
        $arr = array();
		$arr = $this->getAllCols();
		 
		foreach($arr as $key=>$val){
			//if(isset($arrField[$val]) || array_key_exists($val,$arrField )) $da[$val] = $arrField[$val]; 
			//else $da[$val] = $this->getMyField($val)->SETVALUE;
			 $da[$val] =!is_null( $this->getMyField($val)->SETVALUE) ? $this->getMyField($val)->SETVALUE:  $arrField[$val];
		}; 
		$intFieldNum = count($da);
        for($i=0; $i<$intFieldNum; $i++) {
            $value =$this->parseValue( $da[key($da)]);
			 
            $strFieldValues .= "," . key($da) ."=" . $value." ";
            next($da);
        }
        $strWhere = $strWhere != '' ? " WHERE $strWhere" : '';
        $sql  = "UPDATE " . $tblname ." SET " . ltrim($strFieldValues, ',')
              . $strWhere;
        return $sql;
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
    public function getInsertSql($data,$options=array(),$replace=false) {
        $values  =  $fields    = array();  
        $arr = $this->getAllCols();
		 
		foreach($arr as $key=>$val){
			// $da[$val] = isset($data[$val]) ? $data[$val]:$this->getMyField($val)->SETVALUE; 
			$da[$val] = !is_null($this->getMyField($val)->SETVALUE) ?$this->getMyField($val)->SETVALUE: $data[$val] ; 
		}; 
		 

        foreach ($da as $key=>$val){
            $value   =  $this->parseValue($val); 
            if(is_scalar($value)) { // ���˷Ǳ�������
				
                $values[]   =  $value;
                $fields[]   =  $this->parseKey($key);
            } 
        }
		if(!strpos($this->PKFIELD,'^') && $this->NOINCREMENT!='-1' ){
			$values[] = $data[$this->PKFIELD];
			$fields[] = $this->PKFIELD;

		}
       $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$options['table'].' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        return $sql ;  //.= $this->parseLock(isset($options['lock'])?$options['lock']:false);
       
    }
	 /**
     +----------------------------------------------------------
     * �ֶ�������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseKey(&$key) {
        return $key;
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
        if($this->checkDateIsValid($value) && is_string($value)){
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
		 
		if(mb_detect_encoding($string, 'UTF-8') === 'UTF-8')
		$str = iconv('UTF-8','GBK',$str);//ajaxֻ�ܴ�uft-8
		$str = str_replace("'","''",$str);
		$str = get_magic_quotes_gpc()?$str:addslashes($str);
        return $str;
		//return $str;
    }
	/**
	 +----------------------------------------------------------
	 * У�����ڸ�ʽ�Ƿ���ȷ
	 +----------------------------------------------------------
	 * @access public
	 * @param string $date ����
	 * @param string $formats ��Ҫ����ĸ�ʽ����
	 +----------------------------------------------------------
	 * @return boolean
	 +----------------------------------------------------------
	

	function checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d hh:mi:ss", "Y/m/d", "Y/m/d hh24:mi:ss","Y-m-d hh:mi:ss","Y-m-d hh24:mi:ss")) {
		$unixTime = strtotime($date);
		if (!$unixTime) { //strtotimeת�����ԣ����ڸ�ʽ��Ȼ���ԡ�
			return false;
		}

		//У�����ڵ���Ч�ԣ�ֻҪ��������һ����ʽ��OK
		foreach ($formats as $format) {
			if (date($format, $unixTime) == $date) {
				return true;
			}
		}

		return false;
	}
	*/
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
     *��ʼ��FORM����
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   this
     +----------------------------------------------------------
     */
	public function initForminfo($formno){
		$formno = $_REQUEST['childrenformno']?$_REQUEST['childrenformno']:$formno;
		$data = $this->model->query("select * from FORM  where FORMNO=".$formno);
		if($info = $data[0]){
			foreach($info as $key=>$val){
				if(!is_null($val)){
					if(property_exists('FORM',$key))$this->$key = $val;
				}
			}
		} 
		//$cols = new Mycols(Field);
		$this->mvarCols->getMycols($this->model,$formno);  
		
		return $this;
	}
	/**
     +----------------------------------------------------------
     *�����ҳ���ѯ����
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return    
     +----------------------------------------------------------
     */
	public function addchildrenWhere( ){
		 
		if($_REQUEST['parentchooseid'] && $this->FKFIELD){
			$this->where($this->FKFIELD."='".$_REQUEST['parentchooseid']."'")->setMyFieldVal($this->FKFIELD,$_REQUEST['parentchooseid'],true);

		} 
	}
	/**
     +----------------------------------------------------------
     *����Զ����ֶ�
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   this
     +----------------------------------------------------------
     */
	public function addMyField($field){
		 
		//$cols = new Mycols(Field);
		$this->mvarCols->addItem($field);
		return $this;
	}
	/**
     +----------------------------------------------------------
     *��̬�޸��ֶ�ֵ
     +----------------------------------------------------------
     * @access public
	 * @param string $fieldName �ֶ�����
	 * @param string $property �ֶ� ֵ
	 * @param string $readonly �Ƿ�ֻ��
	 +----------------------------------------------------------
     * @return   this
     +----------------------------------------------------------
     */
	public function setMyFieldVal($fieldName,$value,$readonly=false){
		
		foreach( $this->mvarCols as $key=>$val){
			
			if($val->FIELDNAME==$fieldName && $value!=null){
				 $this->mvarCols[$key]->SETVALUE = $value;
				 if($readonly==true)$this->mvarCols[$key]->READONLY = -1;
				 return $this;
			}
		}

		return $this;
	}
	/**
     +----------------------------------------------------------
     *��̬�޸��ֶ�����
     +----------------------------------------------------------
     * @access public
	 * @param string $fieldName �ֶ�����
	 * @param string $property �ֶ�����
	 * @param string $property �ֶ�����ֵ
	 * @param string $readonly �Ƿ�ֻ��
	 +----------------------------------------------------------
     * @return   this
     +----------------------------------------------------------
     */
	public function setMyField($fieldName,$property,$value,$readonly=false){
		
		foreach( $this->mvarCols as $key=>$val){
			
			if($val->FIELDNAME==$fieldName && $value!=null){
				 $this->mvarCols[$key]->$property = $value;
				 if($readonly==true)$this->mvarCols[$key]->READONLY = -1;
				 return $this;
			}
		}

		return $this;
	}
	/**
     +----------------------------------------------------------
     *����ֶ�
     +----------------------------------------------------------
     * @access public
	 * @param string $fieldName �ֶ�����
	  
	 +----------------------------------------------------------
     * @return   this field
     +----------------------------------------------------------
     */
	public function getMyField($fieldName){
		
		foreach( $this->mvarCols as $key=>$val){
			
			if($val->FIELDNAME==$fieldName  ){
				  
				 return $val;
			}
		}

	}
	/**
     +----------------------------------------------------------
     *��ڷ���
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getResult(){ 
		$this->addchildrenWhere();
		if($_REQUEST['faction']=='getNextcol'){
			return $this->getNextcol($_REQUEST['fieldname'],$_REQUEST['parentkey']);
		}elseif($_REQUEST['faction']=='getSelectTreeOption'){
			return $this->getSelectTreeOption($_REQUEST['fieldname'],$_REQUEST['parentkey']);
		}elseif($_REQUEST['faction']=='saveFormData'){
			return $this->saveFormData();
		}elseif($_REQUEST['faction']=='saveGridData'){
			return $this->saveGridData();

		}elseif($_REQUEST['faction']=='getSortCols'){
			return $this->getSortCols();
		}elseif($_REQUEST['faction']=='getFilterCols'){
			return $this->getFilterCols();
		}elseif($_REQUEST['faction']=='getSelectOption'){
			return $this->getSelectOption();
		}elseif($_REQUEST['faction']=='delData'){
             return $this->delData();
        }elseif($_REQUEST['faction']=='uploadFile'){
             return $this->uploadFile();
        }
		//showForm ��1 �༭ 2�鿴 3������
		if($this->FORMTYPE=='FORM' ||$_REQUEST['showForm'] ){ 
			if($_REQUEST['showForm']==3)$_REQUEST['showForm']=1;
			$this->FormeditType = ($_REQUEST['showForm']==1 || $_REQUEST['showForm'] ==2) ?$_REQUEST['showForm'] :$this->FormeditType ; 

			return $this->getFormHtml();
		}elseif($this->FORMTYPE=='GRID'){ 
			return $this->getGridHtml();
		}

	}
	/**
     +----------------------------------------------------------
     *��ȡ��HTML
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getFormHtml(){
		
		$formArr = $this->getFormCols();
        if(!strpos($this->PKFIELD,'^'))$colArr[] = $this->PKFIELD;
		foreach($formArr as $k=>$v){
			if($v->ISVIRTUAL!=-1){
				if($v->EDITTYPE == 13)$colArr[] = 'to_char('.$v->FIELDNAME.',\'YYYY-MM-DD\') as '.$v->FIELDNAME;
				else $colArr[] = $v->FIELDNAME;
			}
			 
			$maxColno = $v->COLNO > $maxColno ? $v->COLNO : $maxColno;
			$arr[$v->LINENO][$v->COLNO] = $v;

		}
		$this->PKVALUE = $this->PKVALUE ? $this->PKVALUE :$_REQUEST['ID'];
		if($this->PKVALUE) {
			if(!strpos($this->PKVALUE,'^'))
				$result = $this->getRowById($this->PKVALUE,$colArr);
			else $result = $this->getRowByIds($this->PKVALUE,$colArr);
		}
		/*foreach( $this->mvarCols as $key=>$val){
			//$a .= $val->creatFormColsHtml($this->model);
			
			$maxColno = $val->COLNO > $maxColno ? $val->COLNO : $maxColno;
			//if($arr[$val->LINENO][$val->COLNO]){
				 
			//}else $arr[$val->LINENO][$val->COLNO] = $val; 
			if( $val->FORMVISIBLE ==-1)$arr[$val->LINENO][$val->COLNO] = $val;
			
		}*/
		//var_dump($arr);
		ksort($arr);

		if($this->changeRecord){
			$changer = new Changerecord();
			$changer->fields=$this->getAllCols();
			$optt['TABLE'] = $this->SQLTEXT;
			$optt['BID'] = $this->PKVALUE;
			$optt['CID'] = $this->changeRecordVersionId?$this->changeRecordVersionId:0;//����汾id
			//$optt['CDATE'] = date('Y-m-d h:i:s');
			//$optt['APPLICANT'] = $_SESSION['uinfo']['uid'];
			//$changer->saveRecords($optt,$data);
			$changarr = $changer->getRecords($optt); 

		}
		
		foreach($arr as $key=>$val){ 
			$param = array();

			$td = '';
			$param['FORMTYPE'] = 'FORM'; 
			$param['MAXLENGTH'] =  $this->MAXLENGTH ? $this->MAXLENGTH :'';
			//$param['NEWADD'] = $this->PKVALUE ? 'edit' :'add';
			$param['FormeditType'] =$this->FormeditType;
			if(count($val)==1 && $maxColno>1){
					$attr = $maxColno*2-1;
					$param['attr'] = "colspan='$attr'";
					$vall = current($val);
					if($result){
						$param['value'] = $vall->ISVIRTUAL==-1 ?  null: $result[$vall->FIELDNAME];
						$param['CHILDREN'] = $result[$vall->CHILDREN]; 
						if($this->changeRecord && $this->changeRecordVersionId && $changarr[$vall->FIELDNAME]){
							$param['value'] = $changarr[$vall->FIELDNAME]['VALUEE'];
							$param['ORIVALUEE'] = $changarr[$vall->FIELDNAME]['ORIVALUEE']; 
							$param['ISNEW'] = $changarr[$vall->FIELDNAME]['ISNEW'];  

						}
					}
					$td = $vall->creatFormColsHtml($this->model, $param);
					
			}else{
				$tdNum = 0;
				for($x=1;$x <= $maxColno;$x++){

					$param['value'] = null;
					$param['ORIVALUEE'] =null; 
					$param['ISNEW'] = null;
					 
					if($td=='<td></td><td></td>'){
						$td='';
						$tdNum +=1;
					} 
					$param['value'] = ($result && $val[$x]) ?  $result[$val[$x]->FIELDNAME] :null;
					$param['CHILDREN'] = ($result && $val[$x]) ? $result[$val[$x]->CHILDREN]:''; 
					if($this->changeRecord && $this->changeRecordVersionId && $changarr[$val[$x]->FIELDNAME]){
						$param['value'] = $changarr[$val[$x]->FIELDNAME]['VALUEE'];
						$param['ORIVALUEE'] = $changarr[$val[$x]->FIELDNAME]['ORIVALUEE']; 
						$param['ISNEW'] =  $changarr[$val[$x]->FIELDNAME]['ISNEW']; 

					} 
					$td .= $val[$x] ? $val[$x]->creatFormColsHtml($this->model,$param) : '<td></td><td></td>';
					//if($v->PARENTCOL) $td .=  "<script> getNextcol('".$v->PARENTCOL."','".$val[$v->FIELDNAME]."','".$val[$this->PKFIELD]."','".$val[$v->PARENTCOL]."'); </script>";
				} 
				for($i=0;$i<$tdNum;$i++){
					$td .= '<td></td><td></td>';
				}
			}
			$html .= "<tr> $td </tr>";
		}
		$actionUrl = $this->CUSTOMACTION ? $this->CUSTOMACTION:$this->joinUrl(__SELF__,'faction=saveFormData&FORMNO='.$this->FORMNO.'&paramId='.$_REQUEST['paramId'] );
		$ljstr = strstr(__SELF__,'?') ?  '&' :'?'; 
		$bhtml = $this->FormeditType!=2 ? '<input type="submit" value="��&nbsp;��" class="btn-blue" /> <input type="button" value="��&nbsp;��" class="btn-gray j-formclose" /> ' :'';
        $bhtml .= $this->FORMAFTERBTN;
		$fhtml = '<style>.caseinfo-table table td:nth-of-type(2n+1){width:'.$this->LABELWIDTH.'}</style>';
		$fhtml .= '<form class="registerform" method="post" action="'.$actionUrl.'">';
		if($this->PKVALUE ) $fhtml .= '<input type="hidden" name="'.$this->PKFIELD.'" value="'.$this->PKVALUE.'" />';
        if(is_array($this->hidden_input_arr) && !empty($this->hidden_input_arr))
        {
            foreach($this->hidden_input_arr as $key => $value)
            {   
                $input_name = !empty($value['name']) ? $value['name'] : 'input_name';
                $input_val = !empty($value['val']) ? $value['val'] : '';
                $input_class = !empty($value['class']) ? $value['class'] : '';
                $input_id = !empty($value['id']) ? $value['id'] : '';
                
               $fhtml .= '<input type="hidden" id = "'.$input_id.'" class = "'.$input_class.'" '
                       . 'name="'.$input_name.'" value="'.$input_val.'" />';
            }
        }
		$fhtml .= ' <div class="caseinfo-table"> ';
		$fhtml  .= '<table style="table-layout:fixed;width:'.$this->WIDTH.'"  >'.$html.'</table>';
		$fhtml .= '</div><div class="handle-btn">';
		$fhtml .= $this->FORMCHANGEBTN ? $this->FORMCHANGEBTN :$bhtml;
		$fhtml .= '</div></form><script>  appUrl = "'.__APP__.'"; actionUrl = "'.__ACTION__.'";</script>';
		return $this->FORMBEFOREDATA.$fhtml.$this->FORMAFTERDATA;
	}
	/**
     +----------------------------------------------------------
     *��ѯ����
     +----------------------------------------------------------
     * @access public
	 * @param array $data ����
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	 public function where($data){
		 if(is_array($data)){
			 $temp = array();
			 foreach($data as $k=>$v){
				 $temp[] = $k."='$v'";
			 }
			 $data = implode('and',$data);
		 } 
		 if($this->sqlwhere !=null) $this->sqlwhere .= ' and '. $data; 
		 else  $this->sqlwhere  =   $data; 
		 return $this;

	 }
	/**
     +----------------------------------------------------------
     *��ȡgridHTML
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getGridHtml(){

		$arr = $this->getGridCols();
		$theads  = $this->SHOWCHECKBOX == -1 ?  '<td><input name="checkall" id="checkall" type="checkbox" value="" /></td>':'';
        $theads  .= $this->SHOWSEQUENCE == -1 ?  '<td>���</td>':'';
		$theads  .= strpos($this->PKFIELD,'^') || ($this->SHOWPKFIELD==0 )? '': '<td>���</td>';
		if(!strpos($this->PKFIELD,'^') )$colArr[] = $this->PKFIELD;
		foreach($arr as $k=>$v){
			$theads .= '<td>'.$v->FIELDMEANS.'</td>';
			if($v->ISVIRTUAL!=-1){
				if($v->EDITTYPE == 13)$colArr[] = 'to_char('.$v->FIELDNAME.',\'YYYY-MM-DD\') as '.$v->FIELDNAME;
				else $colArr[] = $v->FIELDNAME;
			}
		}

		//�������Ƿ�����
		$display = $this->NOPERATE?' style="display:none" ':'';

		//������������
		if(is_array($this->new_td_arr)){
			foreach($this->new_td_arr as $key=>$val){
				$theads .= '<td>' . $val['TDNAME'] . '</td>';
			}
		}

		$theads .= '<td ' . $display . ' width="200">����</td>';

		$where = array();
		$sqlparam['ORDERBY'] = $this->getSortSql();
		$sqlparam['FILTERSQL'] = $this->getFilterSql();
		if($sqlparam['FILTERSQL'] ) $where[] = $sqlparam['FILTERSQL'];
		if($this->sqlwhere) $where[] = ' and ' .$this->sqlwhere;   
		$sqlparam['FILTERSQL'] = implode(' ',$where); 
		$total = $this->getRowsNum($sqlparam);
		
		$page = ($_REQUEST['page']>0)?intval($_REQUEST['page']):1;
        $pageSize = $_REQUEST['pageSize']?intval($_REQUEST['pageSize']):30;
        $pages = ceil($total/$pageSize);
		$pagehtml = $this->getPage($total,$page,$pageSize,$pages);

		$rows = $this->getRows( $sqlparam,$colArr,($page-1)*$pageSize,($page-1)*$pageSize+$pageSize); 
		$param['MAXLENGTH'] =  $this->MAXLENGTH ? $this->MAXLENGTH :'';
		$ljstr = strstr(__SELF__,'?') ?  '&' :'?';
		if(strpos($this->PKFIELD,'^') ){
			$parr = explode('^',$this->PKFIELD);
		} 
		foreach($rows as $key=>$val){
			$param['value'] = null;
			$param['ORIVALUEE'] = null; 
			$param['ISNEW'] =  null; 
			$PKFIELD = array();
			if($parr ){
				foreach($parr as $vv){
					$PKFIELD[] = $vv.'^'.$val[$vv];
				}
				$val[$this->PKFIELD] = implode('^',$PKFIELD);
			}
			$param['PKFIELD'] = $val[$this->PKFIELD];
			$trs .=   '<tr class="itemlist" fid="'.$val[$this->PKFIELD].'">' ;
			$trs  .= $this->SHOWCHECKBOX == -1 ?  '<td><input name="checkedtd" class="checkedtd" type="checkbox" value="'.$val[$this->PKFIELD].'" /></td>':'';
			$trs .=  $this->SHOWSEQUENCE == -1 ? '<td>'.(($page-1)*$pageSize+$key+1).'</td>' :'';
			$trs .= strpos($this->PKFIELD,'^') || ($this->SHOWPKFIELD==0)?'':'<td>'.$val[$this->PKFIELD].'</td>';
			foreach($arr as $k=>$v){
				$param['value'] = null;
				$param['ORIVALUEE'] = null; 
				$param['ISNEW'] = null;
				if($this->changeRecord){
					$changer = new Changerecord();
					$changer->fields=$this->getAllCols();
					$optt['TABLE'] = $this->SQLTEXT;
					$optt['BID'] = $val[$this->PKFIELD];
					$optt['CID'] = $this->changeRecordVersionId?$this->changeRecordVersionId:0;//����汾id
					//$optt['CDATE'] = date('Y-m-d h:i:s');
					//$optt['APPLICANT'] = $_SESSION['uinfo']['uid'];
					//$changer->saveRecords($optt,$data);
					$changarr = $changer->getRecords($optt);

				} 
	 
				 //$trs .= '<td>'.$val[$v->FIELDNAME].'</td>';
				 $param['value'] = $val[$v->FIELDNAME];
				 $param['CHILDREN']  = $val[$v->CHILDREN];
				if($this->changeRecord && $this->changeRecordVersionId && $changarr[$v->FIELDNAME]){
						$param['value'] = $changarr[$v->FIELDNAME]['VALUEE'];
						$param['ORIVALUEE'] = $changarr[$v->FIELDNAME]['ORIVALUEE']; 
						$param['ISNEW'] =  $changarr[$v->FIELDNAME]['ISNEW']; 

				}

				 $trs .=  $v->creatFormColsHtml($this->model, $param);
				 //if($v->PARENTCOL) $trs .=  "<script> getNextcol('".$v->PARENTCOL."','".$val[$v->FIELDNAME]."','".$val[$this->PKFIELD]."','".$val[$v->PARENTCOL]."'); </script>";
			}
			//$formUrl = $this->GRIDMODE==2  ?  __ACTION__.'&FORMNO='.$this->FORMNO.'&showForm=1&ID='.$val[$this->PKFIELD] :'';


			//��������
			if(is_array($this->new_td_arr)){
				foreach($this->new_td_arr as $k=>$v){
					switch(strtoupper($v['TYPE'])) {
						//input����
						case 'INPUT':
							$trs .= "<td><input type='text' idval='" . $param['PKFIELD'] . "' class='" . $v['INPUTNAME'] . "' name='" . $param['PKFIELD'] . "_" . $v['INPUTNAME'] . "' /></td>";
							break;
						//select����
						case 'SELECT':
							$sql = $v['LISTSQL'];
							if($v['LISTSQL_VAL'])
								$sql = str_replace("LISTSQL_VAL",$val[$v['LISTSQL_VAL']],$sql);
							$data = F(md5($sql));
							if(!$data){
								$data = $this->model->query($sql);
								F(md5($sql),$data);
							}

							//�����������
							$trs .= "<td><select id='" . $param['PKFIELD'] . "_" . $v['INPUTNAME'] . "' name = '" . $param['PKFIELD'] . "_" . $v['INPUTNAME'] . "'>";
							$trs .= "<option value='0'>--��ѡ��--</option>";
							foreach ($data as $datak => $datav) {
								//�����Ҫ������
								if($v['DISABLED']){
									$selected = $param['PROJECT_TYPE_ID']==$datav['ID']?'selected':'';
								}
								$trs .= "<option value='" . $datav['ID'] . "' $selected>" . $datav['YEWU'] . "</option>";
							}
							$trs .= "</select></td>";
							break;
					}
				}
			}

			if($this->CZBTN){
				$czbtn = null;
				if(is_string($this->CZBTN) ){
					 
					$czbtn = $this->CZBTN;
					 
				}elseif(is_array($this->CZBTN)){
					foreach($this->CZBTN as $czkey=>$czvalue){
						 
						$czbtn .= $this->getContionResult($czkey,$val) ?$czvalue :'';
					}

				}

				$trs .= '<td class="fedit" ' . $display . ' fid="' . $val[$this->PKFIELD] . '"> ';
				$trs .= $czbtn;
				$trs .= '</td></tr>';

			}else{
				$pkfield = stristr($this->PKFIELD,'^')? 'ID':$this->PKFIELD;
				$formUrl =$this->joinUrl(__SELF__,'showForm=1&'.$pkfield.'='.$val[$this->PKFIELD] ) ;
				$formUrl2 =$this->joinUrl(__SELF__,'showForm=2&'.$pkfield.'='.$val[$this->PKFIELD] ) ;
				//$formUrl =  $this->joinUrl($formUrl,'&fromurl='.urlencode($formUrl));
				$trs .= '<td class="fedit"' . $display . '> ';
				$trs .= $this->EDITABLE==-1 && $this->getContionResult($this->EDITCONDITION,$val) ? '<a href="javascript:void(0);" class="contrtable-link fedit" fid="'.$val[$this->PKFIELD].'" onclick="fedit(this,\''.$formUrl.'\');">�༭</a>':'';
				$trs .= '<a href="javascript:void(0);" class="contrtable-link fedit" fid="'.$val[$this->PKFIELD].'" onclick="fedit(this,\''.$formUrl2.'\');">�鿴</a>';
				$trs .= $this->DELABLE==-1 && $this->getContionResult($this->DELCONDITION,$val)  ? '<a href="javascript:void(0);" class="contrtable-link " fid="'.$val[$this->PKFIELD].'" onclick="ofdel(this);" >ɾ��</a>':'';
				$trs .= '</td></tr>';
			}
			if(count($rows ) -1 == $key){
				$trs .= '<tr style="display:none;" class="quickadd">';
				$trs .='<td> </td>';
				$trs .= '<td></td>';
				foreach($arr as $k=>$v){
					$param['CHILDREN'] = $val[$v->CHILDREN];
					$param['value'] = null;
					$param['PKFIELD'] = 'new';
					//$param['houzui'] = '[]';
					$trs .= $v->creatFormColsHtml($this->model, $param);
					 
				}
				$trs .= '<td></td>';
				$trs .= '</tr>';
				 
			}

		}
		

		$fhtml = ($this->GRIDMODE==1 || $this->GRIDMODE==2|| $this->GRIDMODE==3) ? '<form class="registerform" method="post" action="'.$this->joinUrl(__SELF__,'faction=saveGridData&FORMNO='.$this->FORMNO).' ">' :'';
		$fhtml .= ' <div class="contractinfo-table">';
		$fhtml .= '<table> <thead><tr>'.$theads.'</tr> </thead> <tbody>';
		$fhtml .= $trs.'</tbody></table></div>';
		$fhtml .= '<div class="page">'.$pagehtml.'</div>';
		$buttons = $this->GCBTN ? $this->GCBTN:$this->GABTN;
		$fhtml .= '<div class="buttons">'.$buttons.'</div>';
		$fhtml .=  ($this->GRIDMODE==1 || $this->GRIDMODE==3) ? '<input type="hidden" name="formtype" id="formtype" value="grid" /><input type="hidden" name="IDS" id="IDS" value="" /><input type="hidden" name="addids" id="addids" value="0" /><input type="hidden" name="LOCATIONURL" id="LOCATIONURL" value=""/><div class="handle-btn"><input type="submit" value="ȷ&nbsp;��" class="btn-blue" />  <input type="button" value="��&nbsp;��" class="btn-gray  j-pageclose" /></div> </form>' :'';
		$fhtml .= '</form>';
		$fhtml .= '<script>  appUrl = "'.__APP__.'";actionUrl = "'.__ACTION__.'"; </script>'; 
		if($this->children){
			$fhtml .= '<script> __d();var toiframetype__ =2 ; </script>'; 
				$fhtml .= '<div class="handle-tab-twolevl" id="ifmulli"> <div class="topline"></div><ul class="twolevelul">';
				foreach($this->children as $ckey=>$cval){
					//$selected = $ckey==0 ? 'class="twolevelli on"':'class="twolevelli"';
					if($ckey==0){
						$selected =  'class="twolevelli on"';
						$fhtml .= '<script> actionUrl = "'.$cval[1].'"; </script>'; 

					}else{
						$selected = 'class="twolevelli"';
					}
					$fhtml .= '<li '.$selected .'  ><a href="javascript:void(0);" onclick="toiframe(\''.$cval[1].'\',this)">'.$cval[0].'</a></li>';
				}
				$fhtml .= '</ul><iframe src ="" frameborder="0" marginheight="0" marginwidth="0" frameborder="0" scrolling="auto" id="ifm" name="ifm"   height="500" width="100%"> </iframe>  </div>';
		}else{
			$this->children = $this->getChildren($this->FORMNO);
			if($this->children){
				$fhtml .= '<script> __d(); var toiframetype__ =1 ;</script>'; 
				$fhtml .= '<div class="handle-tab-twolevl" id="ifmulli"> <div class="topline"></div><ul class="twolevelul">';
				foreach($this->children as $ckey=>$cval){
					$selected = $ckey==0 ? 'class="twolevelli on"':'class="twolevelli"';
					$fhtml .= '<li '.$selected .' fno="'.$cval['FORMNO'].'" ><a href="javascript:void(0);" onclick="toiframe(actionUrl,this,'.$cval['FORMNO'].',null)">'.$cval['FORMTITLE'].'</a></li>';
				}
				$fhtml .= '</ul><iframe src ="" frameborder="0" marginheight="0" marginwidth="0" frameborder="0" scrolling="auto" id="ifm" name="ifm"   height="500" width="100%"> </iframe>  </div>';
			}
		}
		return $this->GRIDBEFOREDATA.$fhtml.$this->GRIDAFTERDATA.$this->SHOWSTATUSTABLE;

	}
	/**
     +----------------------------------------------------------
     *ִ������ 
     +----------------------------------------------------------
     * @access public
	 * @param string $str ����
	 +----------------------------------------------------------
     * @return     
     +----------------------------------------------------------
     */
	public function getContionResult($str,$row){
		 
		if($str){
			preg_match_all('/%([\s\S]*?)%/',$str,$matchs);  
			for($i=0; $i<count($matchs[0]);$i++ ){
					$row[$matchs[1][$i]] =  $row[$matchs[1][$i]] ? $row[$matchs[1][$i]] : 'null';
				    $str = str_replace($matchs[0][$i],$row[$matchs[1][$i]],$str);
					 
				  

			} 
			$res = true;
			$stt = "if($str) \$res=true; else \$res=false;"; 
			 
			@eval($stt); 
		    
			return $res ;
			 
		}else return true;

		 
	}

	/**
     +----------------------------------------------------------
     *��ȡ ��ҳ��
     +----------------------------------------------------------
     * @access public
	 * @param num $formno ��No
	 +----------------------------------------------------------
     * @return     
     +----------------------------------------------------------
     */
	public function getChildren($formno){
		 
		 
		$data = $this->model->query("select FORMNO, FORMTITLE from  FORM  where PARENTID= $formno "  );
        
		return $data ;
	}
	/**
     +----------------------------------------------------------
     *���� ��ҳ��
     +----------------------------------------------------------
     * @access public
	 * @param num $formno ��No
	 +----------------------------------------------------------
     * @return     
     +----------------------------------------------------------
     */
	public function setChildren($data){
		$this->children=$data;
		return $this;
		 
		 
	}

	/**
     +----------------------------------------------------------
     *��ȡ sort sql
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
     public function getSortSql(){
		 $sortType =array('1'=>'asc','2'=>'desc');
		 if($_REQUEST['sort1'] )$sortSql[] =   $_REQUEST['sort1'].' '.$sortType[$_REQUEST['sort1_t']];	
		 if($_REQUEST['sort2'] )$sortSql[] =   $_REQUEST['sort2'].' '.$sortType[$_REQUEST['sort2_t']];
		 if($_REQUEST['sort3'] )$sortSql[] =   $_REQUEST['sort3'].' '.$sortType[$_REQUEST['sort3_t']];	
		 if($sortSql){
			 return 'order by '.implode(',',$sortSql);
		 }else{
			 if(!strpos($this->PKFIELD,'^'))return 'order by '.$this->PKFIELD.' desc';
			 else{
				 $tarr  = explode('^',$this->PKFIELD);
				 return 'order by '.$tarr[1].' desc';
			 }
		 }
		 return false;
	 }
	 /**
     +----------------------------------------------------------
     *��ȡ Filter sql
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return sql
     +----------------------------------------------------------
     */
     public function getFilterSql(){
		 $sql = '';
		 if($_REQUEST['search1'] )$sql .=   $this->pjsql($_REQUEST['search1'],$_REQUEST['search1_s'],$_REQUEST['search1_t']);	
		 if($_REQUEST['search2'] )$sql .=   $this->pjsql($_REQUEST['search2'],$_REQUEST['search2_s'],$_REQUEST['search2_t']);	
		 if($_REQUEST['search3'] )$sql .=   $this->pjsql($_REQUEST['search3'],$_REQUEST['search3_s'],$_REQUEST['search3_t']);	
		 if($_REQUEST['search4'] )$sql .=   $this->pjsql($_REQUEST['search4'],$_REQUEST['search4_s'],$_REQUEST['search4_t']);
		 if($_REQUEST['search5'] )$sql .=   $this->pjsql($_REQUEST['search5'],$_REQUEST['search5_s'],$_REQUEST['search5_t']);
		 
		 return $sql;
	 }
	  /**
     +----------------------------------------------------------
     *��ȡ ƴ��sql
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return sql
     +----------------------------------------------------------
     */
	 public function pjsql($name,$type,$val){
		$filterType =array('1'=>'ģ��','2'=>'Ϊ��','3'=>'=','4'=>'�ǿ�','5'=>'>=','6'=>'<=','7'=>'>','8'=>'<','9'=>'in');
		$val =  addslashes($val);
		$theCols = $this->getCols($name);
		$sqltype = array(21,31,41,23);
		$chartype = array(22,32,42);
		if( in_array($theCols->EDITTYPE,$sqltype)){
			if(strpos($theCols->LISTSQL,'$parentKey')) str_replace($theCols->PARENTCOL,'1',$theCols->LISTSQL);
			 $List = $theCols->LISTSQL ? $theCols->transforListsql($this->model,1) : ''; //���ܣ�

		}elseif( in_array($theCols->EDITTYPE,$chartype)){
			 $List = $theCols->LISTCHAR ? $theCols->transforListchar() : '';

		}
		foreach($List as $k=>$v){
			if($val==$v ||(($type==1) && mb_strpos($v,$val)>-1 )  ){ $val =$k; break;} 
		}
		if($type==1){
			return  " and $name like '%".$val."%' ";
		}elseif($type==2){
			return  " and $name is null ";
		}elseif($type==4){
			return  " and $name is not null ";
		}elseif($type==9){
			return  " and $name in  ($val) ";
		}else{
			return " and $name $filterType[$type]  '$val'  ";
		}

	 }
    
    	/**
     +----------------------------------------------------------
     *��ȡ getPage
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
    public function getPage($totalRecords,$page,$pageSize,$pages){
        
        if ($n = strpos($_SERVER['REQUEST_URI'],'&page')){  
          $currentUrl = substr($_SERVER['REQUEST_URI'],0,$n);
        } else {
          $currentUrl = $_SERVER['REQUEST_URI'];
        }
		$bhtml .= $this->SORTABLE ==-1 ?"<a href='javascript:;' class='j-showalert' id='j-sequence'>����</a>":'';
        $bhtml .= $this->FILTERABLE ==-1 ?"<a href='javascript:;' class='j-showalert' id='j-search'>����</a>":'';
        $bhtml .="<a href='javascript:;' onclick='window.location.reload();' class='j-refresh'>ˢ��</a>";
        $bhtml .= $this->ADDABLE==-1 ? "<a href='".$_SERVER['REQUEST_URI'].'&showForm=3'."'>����</a>":''; 
		$bhtml .= $this->GRIDMODE==3 ? "<a href='javascript:;' onclick='quickadd();' >��������</a>":'';
        
		$bhtml = $this->GCBTN ? '' : $bhtml;
		//$bhtml .= $this->GABTN;
		$Selected = 'Selected'.$pageSize;
		$$Selected  = "selected='selected'";
        $pageHtml = "<div class='fleft'>";
        $pageHtml .="����<s>{$totalRecords}</s>������(".(($page-1)*$pageSize+1)." - ".($page*$pageSize).")&nbsp;ÿҳ";
        $pageHtml .= $this->CHANGEROWS==-1 ? "<select  name='pageSize' class='pageSize'><option $Selected10 value='10'>10</option><option $Selected20 value='20'>20</option><option $Selected30 value='30'>30</option></select>��" : $pageSize.'��';
        $pageHtml .= $this->SHOWBOTTOMBTN==-1 ?  $bhtml : '';
        $pageHtml .="</div><div class='fright'>";
        $pageHtml .="<a href=".$currentUrl."&page=1&pageSize=".$pageSize.">��ҳ</a>";
		if($page-1)
			$pageHtml .="<a href=".$currentUrl."&page=".($page-1)."&pageSize=".$pageSize.">��һҳ</a>";
		else 
			$pageHtml .="<a href='javascript:void(0);'>��һҳ</a>";
		if($page+1<=$pages)
			$pageHtml .="<a href=".$currentUrl."&page=".($page+1)."&pageSize=".$pageSize.">��һҳ</a>";
		else
			$pageHtml .="<a href='javascript:void(0);'>��һҳ</a>";
        $pageHtml .="<a href=".$currentUrl."&page=".$pages."&pageSize=".$pageSize.">ĩҳ</a>";
        $pageHtml .="</div>";
        
        return $pageHtml;
    }

	/**
     +----------------------------------------------------------
     *��ȡgetNextcol
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getNextcol($fieldName,$parentKey){
		if($field = $this->getCols($fieldName) ) {
			$res = $field->transforListsql($this->model,$parentKey);
			foreach($res as $k=>$v){
				$res[$k] = iconv('GBK', 'UTF-8', $v);
			}
		}
		//exit(json_encode($res) ); 
		echo json_encode($res);
		if(!$this->iscontinue) exit( );
	}
	/**
     +----------------------------------------------------------
     *��ȡgetSelectTreeOption
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getSelectTreeOption($fieldName,$parentKey){
		if($field = $this->getCols($fieldName) ) {
			$selectList = $field->LISTSQL ? $field->transforListsqlTree($this->model,$parentKey) : ''; 
			$selectList = $field->transforListTree($selectList,0,1);
			$options ='<option value="">��ѡ��</option>';
			foreach($selectList as $key=>$val){ 
				reset($val);
				$value = current($val);
				$name = next($val);

				$selected = ($defaultValue==$value&& $defaultValue !='') ? 'selected="selected"':'';
				if($defaultValue==$value ) $Dfv =$name;$count=$val['count']>1? '��':'';
				$bq = $field->getXbq(2*($val['count']-1),'&nbsp');
				$options .= "<option value='".$value."' $selected  >$bq  $count ".$name."</option>"; //style='padding-left:".(20*($val['count']-1)) ."px;'
			}
		}
		echo iconv('GBK', 'UTF-8', $options);
		if(!$this->iscontinue) exit( );
	}
	/**
     +----------------------------------------------------------
     *��ȡcols
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return  cols
     +----------------------------------------------------------
     */
	public function getCols($fieldName){
		foreach( $this->mvarCols as $key=>$val){
			if($val->FIELDNAME == $fieldName) return $val;
		}
		return false;
	}
	/**
     +----------------------------------------------------------
     *��ȡall cols
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array all cols
     +----------------------------------------------------------
     */
	public function getAllCols(){
		foreach( $this->mvarCols as $key=>$val){
			if($val->ISVIRTUAL!=-1) $arr[] = $val->FIELDNAME ;
		}
		return $arr;
	}
	/**
     +----------------------------------------------------------
     *��ȡ Form����ʾ cols
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array all cols
     +----------------------------------------------------------
     */
	public function getFormCols(){
		foreach( $this->mvarCols as $key=>$val){
			if( $val->FORMVISIBLE ==-1){
				if($val->PARENTCOL){
					$this->setMyField($val->PARENTCOL,'CHILDREN',$val->FIELDNAME);
				}
				$arr[] = $val;
			}
		}
		return $arr;
	}
	/**
     +----------------------------------------------------------
     *��ȡ Grid����ʾ cols
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array all cols
     +----------------------------------------------------------
     */
	public function getGridCols(){
		foreach( $this->mvarCols as $key=>$val){
			if( $val->GRIDVISIBLE ==-1){
				if($val->PARENTCOL){
					$this->setMyField($val->PARENTCOL,'CHILDREN',$val->FIELDNAME);
				}
				$arr[] = $val;
			}
		}
		return $arr;
	}
	/**
     +----------------------------------------------------------
     *��ȡ������ cols
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array   cols
     +----------------------------------------------------------
     */
	public function getSortCols(){
		foreach( $this->mvarCols as $key=>$val){
			if( $val->SORT ==-1 && $val->GRIDVISIBLE ==-1) {
				$temp['FIELDNAME'] = $val->FIELDNAME;
				$temp['FIELDMEANS'] = iconv('GBK', 'UTF-8', $val->FIELDMEANS);
				$arr[] = $temp;
			}
		}
		//exit(json_encode($arr) ); 
		echo json_encode($arr);
		if(!$this->iscontinue) exit( );
	}
	/**
     +----------------------------------------------------------
     *��ȡ ɸѡ cols
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array   cols
     +----------------------------------------------------------
     */
	public function getFilterCols(){
		foreach( $this->mvarCols as $key=>$val){
			if( $val->FILTER ==-1  && $val->GRIDVISIBLE ==-1)  {
				$temp['FIELDNAME'] = $val->FIELDNAME;
				$temp['FIELDMEANS'] = iconv('GBK', 'UTF-8', $val->FIELDMEANS);
				$arr[] = $temp;
			}
		}
		//exit(json_encode($arr) ); 
		echo json_encode($arr);
		if(!$this->iscontinue) exit( );
	}
	/**
     +----------------------------------------------------------
     *��ȡ ɸѡ ������ select�����б�
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array   json
     +----------------------------------------------------------
     */
	public function getSelectOption(){
		$field = $this->getMyField($_REQUEST['selectfield']);
		$arr = $field->getSelectOption($this->model); 
		foreach($arr as $k=>$v){
				$arr[$k] = iconv('GBK', 'UTF-8', $v);
		}
		echo json_encode($arr);
		if(!$this->iscontinue) exit( );
	}
	/**
     +----------------------------------------------------------
     *��ȡ ����Դ ��¼
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array 
     +----------------------------------------------------------
     */
	public function getRows($param,$colArr=null,$begin,$end){
		$fields = $colArr ? implode(',',$colArr):'*';
		$wheresql = $param['FILTERSQL']? ' where 1=1'.$param['FILTERSQL'] : ''; 
		$bandanspager="select $fields from  $this->SQLTEXT  $wheresql  ".$param['ORDERBY'];
	 	$sql=" SELECT * FROM
		(
			SELECT A.*, rownum r
			FROM
			( ".$bandanspager.") A
			WHERE rownum <= $end
		 ) B
		WHERE r > $begin";
		$data = $this->model->query($sql);
		return $data;
	}
	/**
     +----------------------------------------------------------
     *��ȡ ��ѯ����
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   str 
     +----------------------------------------------------------
     */
	public function getFilter(){
		$this->FILTERSQL = $this->getFilterSql();
		if($this->FILTERSQL ) $where[] = $this->FILTERSQL ;
		if($this->sqlwhere ) $where[] = ' and ' .$this->sqlwhere;   
		$this->FILTERSQL = implode(' ',$where); 
		 
        
		return $this->FILTERSQL;
	}
	/**
     +----------------------------------------------------------
     *��ȡ ����Դ ��¼����
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   total 
     +----------------------------------------------------------
     */
	public function getRowsNum($param ){
		 
		$wheresql = $param['FILTERSQL']? ' where 1=1'.$param['FILTERSQL'] : ''; 
		$data = $this->model->query("select count(*) as NUMS from  $this->SQLTEXT  $wheresql  "  );
        
		return $data[0]['NUMS'];
	}
	/**
     +----------------------------------------------------------
     *����ID��ȡ ����Դ ��¼
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array 
     +----------------------------------------------------------
     */
	public function getRowById($id,$colArr=null){
		$fields = $colArr ? implode(',',$colArr):'*';// echo "select $fields from  $this->SQLTEXT  where $this->PKFIELD = $id  ";
		$data = $this->model->query("select $fields from  $this->SQLTEXT  where $this->PKFIELD = $id  " );
		if($data[0]) 
			return $data[0];
		else return false;
	}
	/**
     +----------------------------------------------------------
     *����IDs��ȡ ����Դ ��¼
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array 
     +----------------------------------------------------------
     */
	public function getRowByIds($id,$colArr=null){
		$fields = $colArr ? implode(',',$colArr):'*';
		$arr = explode('^',$id); 
		$temp =array();
		$conditions = null; 
		foreach($arr as $k=>$v){
			if($k%2==1  ) $temp[] =  $arr[$k-1]."='".$v ."'";
		}
		$conditions = implode(' and ',$temp);
		$data = $this->model->query("select $fields from  $this->SQLTEXT  where $conditions  " );
		if($data[0]) 
			return $data[0];
		else return false;
	}
	/**
     +----------------------------------------------------------
     *�ϴ�����
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array 
     +----------------------------------------------------------
     */
	public function uploadFile(){
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// ʵ�����ϴ���
		$upload->maxSize  = 10000000;// ���ø����ϴ���С
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg','rar','zip','doc','docx','xls');// ���ø����ϴ�����
		$upload->savePath =  './Public/Uploads/';// ���ø����ϴ�Ŀ¼
		if(!$upload->upload()) {// �ϴ�������ʾ������Ϣ
			$this->error($upload->getErrorMsg());
		}else{// �ϴ��ɹ�
			$this->success(' ok');
		}
		exit(1);
	}
    
    
    /**
     +----------------------------------------------------------
     *�������INPUT
	 +----------------------------------------------------------
     * @param  array �ֶ���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function addHiddenInput($input_array)
    {   
        if(is_array($input_array) && !empty($input_array))
        {
            $this->hidden_input_arr = $input_array;
        }
        
        return $this;
    }

	/**
	+----------------------------------------------------------
	 *�������INPUT
	+----------------------------------------------------------
	 * @param  array �ֶ���Ϣ
	+----------------------------------------------------------
	 * @access public
	+----------------------------------------------------------
	 */
	public function addNewTd($input_array)
	{
		if(is_array($input_array) && !empty($input_array))
		{
			$this->new_td_arr = $input_array;
		}

		return $this;
	}
            
	/**
     +----------------------------------------------------------
     *���url
	 +----------------------------------------------------------
     * @param  
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	public function joinUrl($url,$vars){
		$info =  parse_url($url); 
		// ��������
		if(is_string($vars)) { // aaa=1&bbb=2 ת��������
			parse_str($vars,$vars);
		}elseif(!is_array($vars)){
			$vars = array();
		}
		// var_dump($vars);

		if(isset($info['query'])) { // ������ַ������� �ϲ���vars
			parse_str($info['query'],$params);//var_dump($params);
			unset($params['s']); 
			$vars = array_merge($params,$vars);
			
		}//var_dump($vars);
		$str = http_build_query( $vars);
		$ljstr = strstr($url,'?') ?  '&' :'?'; 
		$arr = explode('&',$url);
		return $arr[0].$ljstr.$str;

	}

	public function getStatusTable($valarr,$bjarr){
		$Id = $valarr[0];
		$Field = $valarr[1];
		$data = $this->model->query("select A.*,B.TNAME from ERP_STATUS A left join ERP_STATUS_TYPE B on A.TYPE=B.ID where A.TYPE = $Id   order by A.QUEUE ASC " );
		$data2 = $this->model->query("select  STATUS,COLOR from ERP_STATUS   where  TYPE = $Id   order by  QUEUE ASC " );
		
		$html = '<tr><td>'.$data[0]['TNAME'].'</td>';
		foreach($data as $k=>$v){
			$html .= '<td>'.$v['STATUSNAME'].'</td>';
		}
		$html .= '</tr>';
		$html .= '<tr><td> ͼ��</td>';
		foreach($data as $k=>$v){
			$html .= '<td style="background:'.$v['COLOR'].'!important;"></td>';
		}
		$html .= '</tr>';
		 $arr = array ('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5);

  
		$script =  '<script> 	$(document).ready(function(){ var jsondata =  '.json_encode($data2).' ; ';
		$script .='$(".contractinfo-table tbody tr").each(function(){';
		$script .='var tag = $(this).attr("fid")+"_"+"'.$Field.'";';
		$script .='var status = $("[name=\'"+tag+"\']").val();';
		$script .='	var color = findcolor(jsondata,status);';
		$script .='$("[name=\'"+tag+"\']").parent().parent().find("span").first().hide() ;';
		$script .='	$("[name=\'"+tag+"\']").parent().parent().css({"background-color":color }); });});</script>';
		if($bjarr[$Id]>1) return $script;
				 
		
		return $html.$script;
	}
    
    
	public function showStatusTable($arrparam){
		$this->SHOWSTATUSTABLE ='<div class="divgridtable"  ><table class="gridtable">';
		foreach($arrparam as $value){
			$bjarr[$value[0]]++;
			$this->SHOWSTATUSTABLE .= $this->getStatusTable($value,$bjarr);
		}
		$this->SHOWSTATUSTABLE .= '</table> </div>'; 
		
		 
		return $this;
	}
	/**
     +----------------------------------------------------------
     *��־
	 +----------------------------------------------------------
     * @param string $data ��־��Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	public function logs($data){

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