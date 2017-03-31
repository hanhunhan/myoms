<?php
/**
 +------------------------------------------------------------------------------
 * Form 表单生成类
 +------------------------------------------------------------------------------
 * @category   hhh

 * @author    hhh
 * @version   $Id: Form.php  2015-04-15   $
 +------------------------------------------------------------------------------
 */

 //引入文件
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
    /**
     * 匹配HTML<a></a>标签的正则表达式
     */
    /*const HTML_A_TAG_PREG = "|(<a.*?id\s*=\s*[\"'](.*?)[\"'].*?>(.*?)</a>)|";*/
    const HTML_A_TAG_PREG = "|(<a[\s\S\]*?id=[\s]*?[\"']([\w]+)[\s]*?[\"'][\s\S]*?>([\s\S]+?)</a>)|";

    /**
     * a标签数组所在的行数
     */
    const HTML_A_ROW = 1;

    /**
     * 标签的ID数组所在的行数
     */
    const HTML_ID_ROW = 2;

    protected $cachedFields = array("DEPTID", "FEE_ID","DEPT_ID");

    protected $lastSortArr;  // 上次排序的数组
    protected $lastFilterArr; // 上次检索的数组
    protected $FORMNO           = 0; //界面编号
	protected $FORMTITLE           = null;//界面标题 GRID/FORM上面的标题
	protected $FORMTYPE            = null ;//界面类型 GRID/FORM
	protected $SQLTEXT            = null ;//数据源
	protected $PKFIELD            = 'ID' ;//数据源的主键
	protected $PKVALUE            = null ;//数据源的主键值
	protected $PARENTID            =null; //父页面ID
	protected $EDITABLE            = -1 ;//可编辑 -1 允许（默认） 0 否
	protected $ADDABLE            = -1;//可新增 -1 允许（默认） 0 否
    protected $CHECKABLE          = -1; // 是否允许查看 -1=允许 0=不允许
	protected $DELABLE            = -1 ;//可删除 -1 允许（默认） 0 否
	protected $FILTERABLE            = -1 ;//可过滤 -1 允许（默认） 0 否
	protected $SORTABLE            = -1 ;//可排序 -1 允许（默认） 0 否
	protected $SHOWDETAIL            = -1 ;//显示详细信息  -1 允许（默认） 0 否
	protected $GRIDMODE            = 1 ;//GRID显示方式 显示模式
	//默认：1  1-grid（编辑/显示）  2-grid（显示）+form（编辑）


	protected $WIDTH            = null ;//网格宽度
	protected $LABELWIDTH            = '15%' ;//标签宽度
	protected $LOGPATH            = 'log/' ;//日志文件路径-
	protected $INCPATH            = 'inc/' ;//INC路径-
	protected $IMGPATH            = 'img/' ;//图片路径-
	protected $SAVEERRLOG            = 0 ;//是否保存错误日志 -1 允许0 否（默认）-
	protected $DELCONDITION            = null ;//删除条件-
	protected $EDITCONDITION            = null ;//编辑条件-
	protected $BISQL            = null ;//插入前执行SQL-
	protected $AISQL            = null ;//插入后执行SQL-
	protected $BUSQL            = null ;//更新前执行SQL-
	protected $AUSQL            = null ;//更新后执行SQL-
	protected $BDSQL            = null ;//删除前执行SQL-
	protected $ADSQL            = null ;//删除后执行SQL-
	protected $GABTN            = null ;//插入在功能按钮之后的html语句（grid方式）
	protected $GCBTN            = null ;//替换原有功能按钮的html语句（grid方式） *改 为 浮动按钮
	protected $CZBTN            = null ;//替换原有操作按钮的html语句（grid方式） ***新增  还可以通过 $form->CZBTN = array('%AMOUNT%==23331'=>'<a>测试1</a>','%AMOUNT%==2333'=>'<a>测试2</a><a>测试3</a>');  这种方式设置编辑删除以外的操作按钮 array('条件1'=>'显示结果','条件2'=>'显示结果2')
	protected $SHOWBOTTOMBTN            = -1 ;//显示底部按钮
	protected $FORMAFTERDATA            = null ;//插入在数据之后的html语句（form方式）
	protected $FORMBEFOREDATA            = null ;//插入在数据之前的html语句（form方式）
	protected $GRIDAFTERDATA            = null ;//插入在数据之后的html语句（GRID方式）
	protected $GRIDBEFOREDATA            = null ;//插入在数据之前的html语句（GRID方式）
	protected $MAXLENGTH            = null ;//编辑文本框的最大长度
	protected $SHOWIMGBTN            = 0 ;//显示图形按钮 显示为图形按钮 默认：否-
	protected $LOGERR            = 0 ;//是否将错误保存至日志文件
	protected $CHANGEROWS            = -1 ;// 是否可以更改每页显示行数 默认：是
	protected $SQLTEXTFILTER            = null ;//数据源过滤条件-
	protected $FORMAFTERBTN            = null ;//插入在按钮之后的html语句（form方式）
	protected $FORMCHANGEBTN            = null ;//替换原有功能按钮的html语句（form方式）
	protected $FORMFORWARD            = null ;//FORM提交后跳转url ***新增 虚
	protected $mvarCols					= null;	//字段集合
	protected $model					= null;  //实例化一个model对象
    protected $result            = null ;//执行结果 ***新增 虚
	protected $iscontinue            = false ;//输出json之后是否继续执行 ***新增 虚
	//protected $userFunc            = null ;//回调函数名 ***新增 虚
	//protected $userParams           = null ;//回调函数参数 ***新增 虚
	protected $sqlwhere                =null;//查询条件
	protected $children                =null;//子页面
	protected $NOINCREMENT             =null;//是否自增长
	protected $FKFIELD                 =null;//数据源对父窗口的关联字段
	protected $SHOWPKFIELD             =-1;//显示主键编号-1显示  0不显示
	protected $SHOWSEQUENCE            =-1;//显示序号-1显示  0不显示
	protected $SHOWCHECKBOX              =0 ;//显示多选框 -1显示  0不显示
	protected $SHOWSTATUSTABLE       = null;//显示状态标示
	protected $CUSTOMACTION           = null;//自定义action地址
    protected $hidden_input_arr       = array();//form表单字段定义隐藏input
    protected $new_td_arr       	= array();//form表单字段添加新的列（可编辑列）
	protected $FILTERSQL              = null;//过滤条件
	protected $NOPERATE             = 0;
	protected $changeRecord           = false;//记录变更
	protected $changeRecordVersionId           = null;//记录变更版本id
	protected $FormeditType               = 1;//1编辑状态 2只读状态
	protected $orderField		         =null;//排序字段 及方式 如  id desc
	protected $queryString				= null;
	protected $sortString               = null;
	protected $TABLELAYOUT               =null;//table布局样式 table-layout
	protected $FieldEncry                = -1;//字段值加星号 -1生效  0正常显示
	protected $compareField             =array();// CZBTN 的对比字段
	protected $showSaveCfg             =false; // 是否显示保存配置按钮
	protected $colArr               =array();

	 /**
     +----------------------------------------------------------
     * 构造函数 取得模板对象实例
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
	 * 设置属性值
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
     *保存表单数据
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
            $actionDesc = '更新业务数据';
			foreach($arr as  $v){
				$condition[] = $v.'='."'".$data[$v]."'";
			}
			$conditionStr = implode(' and ',$condition);
			$sql = $this->changeRecord ? '' : $this->getUpdataSql($this->SQLTEXT,$data, $conditionStr);
			$Bsql = $this->BUSQL;
			$Asql = $this->AUSQL;
		}else{
            $actionDesc = '插入业务数据';
			if(count($arr)==1 )$data[$this->PKFIELD] = 'SEQ_'.$this->SQLTEXT.'.'.nextval;
			if($this->NOINCREMENT=='-1') {$data[$this->PKFIELD] = $_POST[$this->PKFIELD] ; }
			$sql = $this->getInsertSql($data,$options);
			$Bsql = $this->BISQL;
			$Asql = $this->AISQL;

		}
		//echo $sql;
		//echo $sql = get_magic_quotes_gpc() ? $sql : addslashes($sql);
		$this->model->startTrans();
		$result['BRows'] = $Bsql ? $this->model->execute($Bsql):1;
		$result['numRows'] = $sql ?  $this->model->execute($sql) : 1;//变更申请时不直接更改
		$lastId =  $this->model->getLastInsID();
		$result['ARows'] = $Asql ? $this->model->execute($Asql):1;
		if($result['BRows'] && $result['numRows'] && $result['ARows']){
			$this->model->commit();
            userLog()->writeLog($result['numRows'], $_SERVER["REQUEST_URI"], $actionDesc . '成功：' . $this->SQLTEXT, serialize($data));
			$result['status'] = $data[$this->PKFIELD] ? 1 : 2;
			$result['msg'] = g2u('成功');
			$result['numRows'] = $result['numRows'];
			//$lastId =  $this->model->getLastInsID();
			$result['lastId'] = $lastId;
			if($this->FORMFORWARD){
				if(strstr($this->FORMFORWARD,'?'))$result['forward'] = $this->FORMFORWARD .'&paramId='.$lastId;
				else $result['forward'] = $this->FORMFORWARD .'?paramId='.$lastId;
			}
		}else{
			$this->model->rollback();
            userLog()->writeLog($this->PKFIELD, $_SERVER["REQUEST_URI"], $actionDesc . '失败：' . $this->SQLTEXT, serialize($data));
			$result['status'] = 0;
			$result['msg'] = g2u('失败');
		}
		if($this->changeRecord){
			$changer = new Changerecord();
			$changer->fields=$this->getAllCols();
			$optt['TABLE'] = $this->SQLTEXT;
			$optt['BID'] = $result['lastId'] ? $result['lastId'] :$data[$this->PKFIELD] ;
			$optt['CID'] = $this->changeRecordVersionId?$this->changeRecordVersionId:1;//变更版本id
			$optt['CDATE'] = date('Y-m-d H:i:s');
			$optt['APPLICANT'] = $_SESSION['uinfo']['uid'];
			$optt['ISNEW'] = $sql ? -1 :0;//新增 或 修改
			$changer->saveRecords($optt,$data);

		}
        if (empty($result['forward'])) {
            if ($_REQUEST['fromUrl']) {
                $result['forward'] = $_REQUEST['fromUrl'];
            }
        }
		$this->result = $result;
		echo json_encode($result);
		if(!$this->iscontinue) exit( );
	}

	/**
     +----------------------------------------------------------
     *保存GRID表单数据
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
				if($this->NOINCREMENT=='-1') {$temp[$this->PKFIELD] = $_POST[$this->PKFIELD] ; }//非自增

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
     *删除表单数据
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
		   $result['msg']  = '';
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
     * 删除语句
	 +----------------------------------------------------------
     * @param type $tblname  表名
     * @param type $strWhere 删除条件
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
     * 更新语句
	 +----------------------------------------------------------
     * @param type $tblname  表名
     * @param type $arrField 字段数组
     * @param type $strWhere 更新条件
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
			 $da[$val] =!is_null( $this->getMyField($val)->SETVALUE) ? $this->getMyField($val)->SETVALUE :  $arrField[$val];
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
    public function getInsertSql($data,$options=array(),$replace=false) {
        $values  =  $fields    = array();
        $arr = $this->getAllCols();

		foreach($arr as $key=>$val){
			// $da[$val] = isset($data[$val]) ? $data[$val]:$this->getMyField($val)->SETVALUE;
			$da[$val] = !is_null($this->getMyField($val)->SETVALUE) ?$this->getMyField($val)->SETVALUE : $data[$val] ;
		};


        foreach ($da as $key=>$val){
            $value   =  $this->parseValue($val);
            if(is_scalar($value)) { // 过滤非标量数据

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
     * 字段名分析
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
        if($this->checkDateIsValid($value) && is_string($value)){
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
		if(mb_detect_encoding($str, 'UTF-8')==='UTF-8')
		$str = iconv('UTF-8','GBK',$str);//ajax只能传uft-8
		$str = str_replace("'","''",$str);
		//$str = get_magic_quotes_gpc() ? $str : addslashes($str);
		if($this->SQLTEXT!='FORM' &&  $this->SQLTEXT!='FORMLIST'){
			$str = strip_tags($str);

		}
        return $str;
		//return $str;
    }
	/**
	 +----------------------------------------------------------
	 * 校验日期格式是否正确
	 +----------------------------------------------------------
	 * @access public
	 * @param string $date 日期
	 * @param string $formats 需要检验的格式数组
	 +----------------------------------------------------------
	 * @return boolean
	 +----------------------------------------------------------


	function checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d hh:mi:ss", "Y/m/d", "Y/m/d hh24:mi:ss","Y-m-d hh:mi:ss","Y-m-d hh24:mi:ss")) {
		$unixTime = strtotime($date);
		if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
			return false;
		}

		//校验日期的有效性，只要满足其中一个格式就OK
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
     *初始化FORM属性
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
     *添加子页面查询条件
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
     *添加自定义字段
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
     *动态修改字段值
     +----------------------------------------------------------
     * @access public
	 * @param string $fieldName 字段名称
	 * @param string $property 字段 值
	 * @param string $readonly 是否只读
	 +----------------------------------------------------------
     * @return   this
     +----------------------------------------------------------
     */
	public function setMyFieldVal($fieldName,$value,$readonly=false){

		foreach( $this->mvarCols as $key=>$val){

			if($val->FIELDNAME==$fieldName){
				if(!is_null($value)) $this->mvarCols[$key]->SETVALUE = $value;
				if($readonly==true)$this->mvarCols[$key]->READONLY = -1;
				return $this;
			}
		}

		return $this;
	}
	/**
     +----------------------------------------------------------
     *动态修改字段属性
     +----------------------------------------------------------
     * @access public
	 * @param string $fieldName 字段名称
	 * @param string $property 字段属性
	 * @param string $property 字段属性值
	 * @param string $readonly 是否只读
	 +----------------------------------------------------------
     * @return   this
     +----------------------------------------------------------
     */
	public function setMyField($fieldName,$property,$value,$readonly=false){

		foreach( $this->mvarCols as $key=>$val){

			if($val->FIELDNAME==$fieldName && $value !== null){
				 $this->mvarCols[$key]->$property = $value;
				 if($readonly==true)$this->mvarCols[$key]->READONLY = -1;
				 return $this;
			}
		}

		return $this;
	}
	/**
     +----------------------------------------------------------
     *获的字段
     +----------------------------------------------------------
     * @access public
	 * @param string $fieldName 字段名称

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
     *入口方法
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getResult(){
//        echo $this->FORMNO;
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
		//showForm （1 编辑 2查看 3新增）
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
     *获取表单HTML
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getFormHtml(){

		$formArr = $this->getFormCols();
        //var_dump($formArr);
        if(!strpos($this->PKFIELD,'^'))$colArr[] = $this->PKFIELD;
		foreach($formArr as $k=>$v){
			if($v->ISVIRTUAL!=-1){

				if($v->EDITTYPE == 13){
					$colArr[] = 'to_char('.$v->FIELDNAME.',\'YYYY-MM-DD\') as '.$v->FIELDNAME;
				}elseif($v->EDITTYPE == 132){
					$colArr[] = 'to_char('.$v->FIELDNAME.',\'YYYY-MM-DD hh24:mi:ss\') as '.$v->FIELDNAME;
				}elseif($v->FIELDTYPE == 2222){
					$colArr[] = "to_char(".$v->FIELDNAME.",'fm99999999990.99')  ";
				}
				else $colArr[] = $v->FIELDNAME;
			}

			$maxColno = $v->COLNO > $maxColno ? $v->COLNO : $maxColno;
			$arr[$v->LINENO][$v->COLNO] = $v;

		}
		$this->PKVALUE = $this->PKVALUE ? $this->PKVALUE :$_REQUEST['ID'];

		if($this->PKVALUE) {
			if(strpos($this->PKVALUE,'^') === false)
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
			$optt['CID'] = $this->changeRecordVersionId?$this->changeRecordVersionId:0;//变更版本id
			//$optt['CDATE'] = date('Y-m-d h:i:s');
			//$optt['APPLICANT'] = $_SESSION['uinfo']['uid'];
			//$changer->saveRecords($optt,$data);
			$changarr = $changer->getRecords($optt);

		}

		foreach($arr as $key=>$val){
			$param = array();

			$td = '';
			$param['FORMTYPE'] = 'FORM';
			$param['FieldEncry'] = $this->FieldEncry;
			$param['MAXLENGTH'] =  $this->MAXLENGTH ? $this->MAXLENGTH :'';
			//$param['NEWADD'] = $this->PKVALUE ? 'edit' :'add';
			$param['FormeditType'] =$this->FormeditType;
			if(count($val)==1 && $maxColno>1){
					$attr = $maxColno*2-1;
					$param['attr'] = "colspan='$attr'";
					$vall = current($val);
					if($result){
						$param['value'] = $vall->ISVIRTUAL==-1 ?  null: $result[trim($vall->FIELDNAME)];
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
					$param['CHILDREN'] = ($result && $val[$x]) ? $result[$val[$x]->CHILDREN]:'';

					if ($param['value'] === null) {
                        $param['value'] = ($result && $val[$x]) ?  $result[$val[$x]->FIELDNAME] :null;
                    }
					if($this->changeRecord && $this->changeRecordVersionId && $changarr[$val[$x]->FIELDNAME]){
						$param['value'] = $changarr[$val[$x]->FIELDNAME]['VALUEE'];
						$param['ORIVALUEE'] = $changarr[$val[$x]->FIELDNAME]['ORIVALUEE'];
						$param['ISNEW'] =  $changarr[$val[$x]->FIELDNAME]['ISNEW'];
					}

					$td .= $val[$x] ? $val[$x]->creatFormColsHtml($this->model,$param) : '<td></td><td></td>';
				}
				for($i=0;$i<$tdNum;$i++){
					$td .= '<td></td><td></td>';
				}
			}
			$html .= "<tr> $td </tr>";
		}
		$actionUrl = $this->CUSTOMACTION ? $this->CUSTOMACTION:$this->joinUrl(__SELF__,'faction=saveFormData&FORMNO='.$this->FORMNO.'&paramId='.$_REQUEST['paramId'] );
		$ljstr = strstr(__SELF__,'?') ?  '&' :'?';
		$bhtml = $this->FormeditType!=2 ? '<input type="submit" value="保存" class="btn btn-primary" /> <input type="button" value="返回" class="btn btn-default j-formclose" /> ' :'';
        //保存当前配置
		if($this->showSaveCfg)
			$bhtml .= '<input type="button" value="保存当前设置" onclick="save_cfg();" class="btn btn-danger" id="savecfg" />';
		$bhtml .= $this->FORMAFTERBTN;

		$fhtml = '<style>.caseinfo-table table td:nth-of-type(2n+1){width:'.$this->LABELWIDTH.'}</style><script type="text/javascript" src="Public/uploadify/jquery.uploadify.js"></script><link href="Public/uploadify/uploadify.css" type="text/css" rel="stylesheet"/>';
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
     *查询条件
     +----------------------------------------------------------
     * @access public
	 * @param array $data 条件
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
     *获取gridHTML
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getGridHtml(){

		$arr = $this->getGridCols();
		$theads  = $this->SHOWCHECKBOX == -1 ?  '<th><input name="checkall" id="checkall" type="checkbox" value="" /></th>':'';
        $theads  .= $this->SHOWSEQUENCE == -1 ?  '<th width="40">序号</th>':'';
		$theads  .= strpos($this->PKFIELD,'^') || ($this->SHOWPKFIELD==0 )? '': '<th width="40">编号</th>';
		if(!strpos($this->PKFIELD,'^') )$this->colArr[] = $this->PKFIELD;
		foreach($arr as $k=>$v){
			$GRIDTDWIDTH = $v->GRIDTDWIDTH ? 'style="width:'.$v->GRIDTDWIDTH.'px!important;"' :'';
			$theads .= '<th    '.$GRIDTDWIDTH.'>'.$v->FIELDMEANS.' </th>';
			if($v->ISVIRTUAL!=-1){
				if($v->EDITTYPE == 13){
					$this->colArr[] = 'to_char('.$v->FIELDNAME.',\'YYYY-MM-DD\') as '.$v->FIELDNAME;
				}elseif($v->EDITTYPE == 132){
					$this->colArr[] = 'to_char('.$v->FIELDNAME.',\'YYYY-MM-DD hh24:mi:ss\') as '.$v->FIELDNAME;
				}elseif($v->FIELDTYPE == 2222){
					$this->colArr[] = "to_char(".$v->FIELDNAME.",'fm99999999990.99')  ";
				}
				else $this->colArr[] = $v->FIELDNAME;
			}
		}
  
		//操作列是否隐藏
		$display = $this->NOPERATE?' style="display:none" ':'';

		//给表格添加新列
		if(is_array($this->new_td_arr)){
			foreach($this->new_td_arr as $key=>$val){
				$theads .= '<th>' . $val['TDNAME'] . '</th>';
			}
		}

		$theads .= '<th ' . $display . ' width="80">操作</th>';

		$where = array();
		$sqlparam['ORDERBY'] = $this->getSortSql();
		$sqlparam['FILTERSQL'] = $this->getFilterSql();
		if($sqlparam['FILTERSQL'] ) $where[] = $sqlparam['FILTERSQL'];
		if($this->sqlwhere) $where[] = ' and ' .$this->sqlwhere;
		$sqlparam['FILTERSQL'] = implode(' ',$where);
		$total = $this->getRowsNum($sqlparam);

		$page=($_POST['page']>0)?$_POST['page'] :($_GET['page']?$_GET['page']:1);
        $pageSize=($_POST['pageSize']>0)?$_POST['pageSize'] :($_GET['pageSize']?$_GET['pageSize']:($this->children?10:20));
        $pages = ceil($total/$pageSize);
		$pagehtml = $this->getPage($total,$page,$pageSize,$pages);
		$param['FieldEncry'] = $this->FieldEncry;
		$rows = $this->getRows( $sqlparam,$this->colArr,($page-1)*$pageSize,($page-1)*$pageSize+$pageSize);
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
			$trs .= '<tr class="itemlist" fid="'.$val[$this->PKFIELD].'">';
			$trs .= $this->SHOWCHECKBOX == -1 ? '<td><input name="checkedtd" class="checkedtd" type="checkbox" value="'.$val[$this->PKFIELD].'" /></td>':'';
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
					$optt['CID'] = $this->changeRecordVersionId?$this->changeRecordVersionId:0;//变更版本id
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


			//增加新列
			if(is_array($this->new_td_arr)){
				foreach($this->new_td_arr as $k=>$v){
					switch(strtoupper($v['TYPE'])) {
						//input类型
						case 'INPUT':
							$trs .= "<td><input type='text' idval='" . $param['PKFIELD'] . "' class='" . $v['INPUTNAME'] . "' name='" . $param['PKFIELD'] . "_" . $v['INPUTNAME'] . "' /></td>";
							break;
						//select类型
						case 'SELECT':
							$sql = $v['LISTSQL'];
							if($v['LISTSQL_VAL'])
								$sql = str_replace("LISTSQL_VAL",$val[$v['LISTSQL_VAL']],$sql);
							$data = F(md5($sql));
							if(!$data){
								$data = $this->model->query($sql);
								F(md5($sql),$data);
							}

							//如果是隐藏域
							$trs .= "<td><select id='" . $param['PKFIELD'] . "_" . $v['INPUTNAME'] . "' name = '" . $param['PKFIELD'] . "_" . $v['INPUTNAME'] . "'>";
							//如果只有一条业务就没必要选择
							if(count($data)>1) {
								$trs .= "<option value='0'>--请选择--</option>";
							}
							foreach ($data as $datak => $datav) {
								//如果需要锁定域
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
				$trs .= $this->EDITABLE==-1 && $this->getContionResult($this->EDITCONDITION,$val) ? '<a href="javascript:void(0);" class="contrtable-link fedit btn btn-primary btn-xs" fid="'.$val[$this->PKFIELD].'" title="编辑" onclick="fedit(this,\''.$formUrl.'\');"><i class="glyphicon glyphicon-edit"></i></a>':'';
				$trs .= $this->CHECKABLE == -1 ? '<a href="javascript:void(0);" class="contrtable-link fedit btn btn-success btn-xs" fid="'.$val[$this->PKFIELD].'" title="查看" onclick="fedit(this,\''.$formUrl2.'\');"><i class="glyphicon glyphicon-eye-open"></i></a>' : '';
				$trs .= $this->DELABLE==-1 && $this->getContionResult($this->DELCONDITION,$val)  ? '<a href="javascript:void(0);" class="contrtable-link btn btn-danger btn-xs del-record" title="删除" fid="'.$val[$this->PKFIELD].'" onclick="ofdel(this);" ><i class="glyphicon glyphicon-trash"></i></a>':'';
                if (!$this->EDITABLE && !$this->CHECKABLE && !$this->DELABLE) {
                    $trs .= '---';
                }
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

		$tbclass = $this->TABLELAYOUT ? 'style="table-layout:'.$this->TABLELAYOUT.';"':'';
		$fhtml = ($this->GRIDMODE==1 || $this->GRIDMODE==2|| $this->GRIDMODE==3) ? '<form class="registerform" method="post" action="'.$this->joinUrl(__SELF__,'faction=saveGridData&FORMNO='.$this->FORMNO).' ">' :'';
		$fhtml .= '  <script type="text/javascript" src="./Public/js/jquery-ui.js"></script><link href="./Public/css/jquery-ui.css" type="text/css" rel="stylesheet"/><script type="text/javascript" src="Public/uploadify/jquery.uploadify.js"></script><link href="Public/uploadify/uploadify.css" type="text/css" rel="stylesheet"/><div class="contractinfo-table">';
		$fhtml .= '<table '.$tbclass.'> <thead><tr class="fixth">'.$theads.'</tr><tr class="fixthdisplay">'.$theads.'</tr></thead> <tbody>';
		$fhtml .= $trs.'</tbody></table></div>';
		$fhtml .= '<div class="page">'.$pagehtml.'</div>';
		//$buttons = $this->GCBTN ? $this->GCBTN:$this->GABTN;
		$buttons = $this->GCBTN ;
		$fhtml .= '<div class="buttons btn-group">'.$buttons.'</div>';
		$fhtml .=  ($this->GRIDMODE==1 || $this->GRIDMODE==3) ? '<input type="hidden" name="formtype" id="formtype" value="grid" /><input type="hidden" name="IDS" id="IDS" value="" /><input type="hidden" name="addids" id="addids" value="0" /><input type="hidden" name="LOCATIONURL" id="LOCATIONURL" value=""/><div class="handle-btn"><input type="submit" value="确&nbsp;定" class="btn-blue" />  <input type="button" value="关&nbsp;闭" class="btn-gray  j-pageclose" /></div> </form>' :'';
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
				$fhtml .= '</ul><iframe src ="" frameborder="0" marginheight="0" marginwidth="0" frameborder="0" scrolling="auto" id="ifm" name="ifm" height="500"    width="100%"> </iframe>  </div>';
		}else{
			$this->children = $this->getChildren($this->FORMNO);
			if($this->children){
				$fhtml .= '<script> __d(); var toiframetype__ =1 ;</script>';
				$fhtml .= '<div class="handle-tab-twolevl" id="ifmulli"> <div class="topline"></div><ul class="twolevelul">';
				foreach($this->children as $ckey=>$cval){
					$selected = $ckey==0 ? 'class="twolevelli on"':'class="twolevelli"';
					$fhtml .= '<li '.$selected .' fno="'.$cval['FORMNO'].'" ><a href="javascript:void(0);" onclick="toiframe(actionUrl,this,'.$cval['FORMNO'].',null)">'.$cval['FORMTITLE'].'</a></li>';
				}
				$fhtml .= '</ul><iframe src ="" frameborder="0" marginheight="0" marginwidth="0" frameborder="0" scrolling="auto" id="ifm" name="ifm" height="500"  width="100%"> </iframe>  </div>';
			}
		}
		return $this->GRIDBEFOREDATA.$fhtml.$this->GRIDAFTERDATA.$this->SHOWSTATUSTABLE;
	}
	/**
     +----------------------------------------------------------
     *执行条件
     +----------------------------------------------------------
     * @access public
	 * @param string $str 条件
	 +----------------------------------------------------------
     * @return
     +----------------------------------------------------------
     */
	public function getContionResult($str,$row){

		if($str){
			preg_match_all('/%([\s\S]*?)%/',$str,$matchs);
			for($i=0; $i<count($matchs[0]);$i++ ){
					$row[$matchs[1][$i]] = !is_null( $row[$matchs[1][$i]] ) ? $row[$matchs[1][$i]] : 'null';
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
     *获取 子页面
     +----------------------------------------------------------
     * @access public
	 * @param num $formno 表单No
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
     *设置 子页面
     +----------------------------------------------------------
     * @access public
	 * @param num $formno 表单No
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
     *获取 sort sql
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
     public function getSortSql(){
		$sort_arr['sort1'] = isset($_POST['sort1']) ? $_POST['sort1'] : $_GET['sort1'];
		$sort_arr['sort1_t'] = isset($_POST['sort1']) && isset($_POST['sort1']) ? $_POST['sort1_t'] : $_GET['sort1_t'];
		$sort_arr['sort2'] = isset($_POST['sort2']) ? $_POST['sort2'] : $_GET['sort2'];
		$sort_arr['sort2_t'] = isset($_POST['sort2'])&& isset($_POST['sort2']) ? $_POST['sort2_t'] : $_GET['sort2_t'];
		$sort_arr['sort3'] = isset($_POST['sort3']) ? $_POST['sort3'] : $_GET['sort3'];
		$sort_arr['sort3_t'] = isset($_POST['sort3'])&&isset($_POST['sort3']) ? $_POST['sort3_t'] : $_GET['sort3_t'];
		$this->sortString = $sort_arr;
         $this->lastSortArr = $sort_arr;
		 $sortType =array('1'=>'asc','2'=>'desc');
		 if($sort_arr['sort1'] )$sortSql[] =   $sort_arr['sort1'].' '.$sortType[$sort_arr['sort1_t']];
		 if($sort_arr['sort2'] )$sortSql[] =   $sort_arr['sort2'].' '.$sortType[$sort_arr['sort2_t']];
		 if($sort_arr['sort3'] )$sortSql[] =   $sort_arr['sort3'].' '.$sortType[$sort_arr['sort3_t']];
		 if($sortSql){
			 if($this->PKFIELD)
			 return 'order by '.implode(',',$sortSql).','.$this->PKFIELD;
			 else  return 'order by '.implode(',',$sortSql);
		 }else{
			if(!strpos($this->PKFIELD,'^')){
				 if($this->orderField)
					return 'order by '.$this->orderField ;
				else
					return 'order by '.$this->PKFIELD.' desc';
			}else{
				 $tarr  = explode('^',$this->PKFIELD);

				 return 'order by '.$tarr[1].' desc';
			}
		 }
		 return false;
	 }
	 /**
     +----------------------------------------------------------
     *获取 Filter sql
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return sql
     +----------------------------------------------------------
     */
     public function getFilterSql(){
		$query_arr['search1'] = isset($_POST['search1'])? $_POST['search1']: $_GET['search1'];
		$query_arr['search1_s'] = isset($_POST['search1'])? $_POST['search1_s']: $_GET['search1_s'];
		$query_arr['search1_t'] = isset($_POST['search1'])? $_POST['search1_t'] :$_GET['search1_t'];
		$query_arr['search1_t_type'] = isset($_POST['search1'])? $_POST['search1_t_type'] :$_GET['search1_t_type'];
		$query_arr['search1_t_select'] = isset($_POST['search1'])? $_POST['search1_t_select'] :$_GET['search1_t_select'];

		$query_arr['search2'] = isset($_POST['search2'])? $_POST['search2'] :$_GET['search2'];
		$query_arr['search2_s'] = isset($_POST['search2'])? $_POST['search2_s']: $_GET['search2_s'];
		$query_arr['search2_t'] = isset($_POST['search2'])? $_POST['search2_t'] :$_GET['search2_t'];
		$query_arr['search2_t_type'] = isset($_POST['search2'])? $_POST['search2_t_type'] :$_GET['search2_t_type'];
        $query_arr['search2_t_select'] = isset($_POST['search2'])? $_POST['search2_t_select'] :$_GET['search2_t_select'];

		$query_arr['search3'] = isset($_POST['search3'])? $_POST['search3']: $_GET['search3'];
		$query_arr['search3_s'] = isset($_POST['search3'])? $_POST['search3_s']: $_GET['search3_s'];
		$query_arr['search3_t'] = isset($_POST['search3'])? $_POST['search3_t'] :$_GET['search3_t'];
		$query_arr['search3_t_type'] = isset($_POST['search3'])? $_POST['search3_t_type'] :$_GET['search3_t_type'];
        $query_arr['search3_t_select'] = isset($_POST['search3'])? $_POST['search3_t_select'] :$_GET['search3_t_select'];

		$query_arr['search4'] = isset($_POST['search4'])? $_POST['search4']: $_GET['search4'];
		$query_arr['search4_s'] = isset($_POST['search4'])? $_POST['search4_s'] :$_GET['search4_s'];
		$query_arr['search4_t'] = isset($_POST['search4'])? $_POST['search4_t']: $_GET['search4_t'];
		$query_arr['search4_t_type'] = isset($_POST['search4'])? $_POST['search4_t_type'] :$_GET['search4_t_type'];
        $query_arr['search4_t_select'] = isset($_POST['search4'])? $_POST['search4_t_select'] :$_GET['search4_t_select'];

		$query_arr['search5'] = isset($_POST['search5'])? $_POST['search5']: $_GET['search5'];
		$query_arr['search5_s'] = isset($_POST['search5'])? $_POST['search5_s'] :$_GET['search5_s'];
		$query_arr['search5_t'] = isset($_POST['search5'])? $_POST['search5_t']: $_GET['search5_t'];
		$query_arr['search5_t_type'] = isset($_POST['search5'])? $_POST['search5_t_type'] :$_GET['search5_t_type'];
        $query_arr['search5_t_select'] = isset($_POST['search5'])? $_POST['search5_t_select'] :$_GET['search5_t_select'];

         if (isset($_POST['last_filter_result'])) {
             $filterArrFromPost = u2g(json_decode($_POST['last_filter_result'], true));

             // 检查搜索条件是否为空
             $isNullQuery = true;
             foreach ($query_arr as $val) {
                 if ($val !== null) {
                    $isNullQuery = false;
                     break;
                 }
             }
             if ($isNullQuery) {
                 $query_arr = $filterArrFromPost;
             }
         }
        $this->lastFilterArr = $query_arr;
		$sql = '';
		 if($query_arr['search1'] ){
			 $sql .=   $this->pjsql($query_arr['search1'],$query_arr['search1_s'],$query_arr['search1_t'],$query_arr['search1_t_type']);

		 }
		 if($query_arr['search2'] ){
			 $sql .=   $this->pjsql($query_arr['search2'],$query_arr['search2_s'],$query_arr['search2_t'],$query_arr['search2_t_type']);

		 }
		 if($query_arr['search3'] ){
			 $sql .=   $this->pjsql($query_arr['search3'],$query_arr['search3_s'],$query_arr['search3_t'],$query_arr['search3_t_type']);

		 }
		 if($query_arr['search4'] ){
			 $sql .=   $this->pjsql($query_arr['search4'],$query_arr['search4_s'],$query_arr['search4_t'],$query_arr['search4_t_type']);

		 }
		 if($query_arr['search5'] ){
			 $sql .=   $this->pjsql($query_arr['search5'],$query_arr['search5_s'],$query_arr['search5_t'],$query_arr['search5_t_type']);

		 }

		$this->queryString = $query_arr; //var_dump($_POST);
		return $sql;
	 }
	 /**替换参数
	  * Returns the url query as associative array
	  * @param	 string	 query
	  * @return	 array	 params
	  */

	public function replace_param($url,$reparam,$sortString ){
		$param_arr = $this->convertUrlQuery($url,$reparam,$sortString );

		$params = $this->getUrlQuery($param_arr);
		return $params;

	}
	/** 获取url参数 转数组
	  * Returns the url query as associative array
	  * @param	 string	 query
	  * @return	 array	 params
	  */
	public function convertUrlQuery($query,$reparam,$sortString){
		$queryParts = explode('&', $query);

		$params = array();
		foreach ($queryParts as $param)
		{
			$item = explode( '=' , $param);
			$params[$item[0]] = $item[1];
		}

        $temp_arr['?s'] = $params['?s'];
        unset($params['?s']);

		$ret = array_merge($temp_arr, $reparam,$params , $sortString);

		return $ret;
	}

	/**  数组 赚querystring
	  * Returns the url query as associative array
	  * @param	array	 params
	  * @return	string	 query
	*/
	public function getUrlQuery($array_query){
		$tmp = array();
		foreach($array_query as $k=>$param)
		{
			if($param !== '')$tmp[] = $k.'='.$param;
		}
		$params = implode('&',$tmp);
		return $params;
	}
	  /**
     +----------------------------------------------------------
     *获取 拼接sql
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return sql
     +----------------------------------------------------------
     */
	 public function pjsql($name,$type,$val,$fieldtype){
		$filterType =array('1'=>'模糊','2'=>'为空','3'=>'=','4'=>'非空','5'=>'>=','6'=>'<=','7'=>'>','8'=>'<','9'=>'in');
		$val =  addslashes($val);
		$theCols = $this->getCols($name);
		$sqltype = array(21,31,41,23);
		$chartype = array(22,32,42);
		if( in_array($theCols->EDITTYPE,$sqltype)){
			if(strpos($theCols->LISTSQL,'$parentKey')) str_replace($theCols->PARENTCOL,'1',$theCols->LISTSQL);
			 $List = $theCols->LISTSQL ? $theCols->transforListsql($this->model,1) : ''; //性能？

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
			if($fieldtype=='date'){
				$val=" to_date('$val','yyyy-mm-dd' ) ";
				return " and to_date(to_char($name,'yyyy-mm-dd'),'yyyy-mm-dd') $filterType[$type]  $val  ";
			}else{
				$val=" '$val' ";
				return " and $name $filterType[$type]  $val  ";
			}

		}

	 }

    	/**
     +----------------------------------------------------------
     *获取 getPage
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
    public function getPage($totalRecords,$page,$pageSize,$pages){

        /*if ($n = strpos($_SERVER['REQUEST_URI'],'&page')){
          $currentUrl = substr($_SERVER['REQUEST_URI'],0,$n);
        } else {
          $currentUrl = $_SERVER['REQUEST_URI'];
        }*/
		if ($n = strpos($_SERVER['REQUEST_URI'],'?')){
          $currentUrl = substr($_SERVER['REQUEST_URI'],0,$n);
		  $currentUrl2 = substr($_SERVER['REQUEST_URI'],$n);

		  $this->queryString['page'] = null;
		  $this->queryString['pageSize'] = null;
		  $currentUrl .=  $this->replace_param($currentUrl2,$this->queryString,$this->sortString);
        } else {
          $currentUrl = $_SERVER['REQUEST_URI'];
        }

        $bhtml = "<div class='fleft btn-group comm-btn-group'>";
		$bhtml .= $this->SORTABLE ==-1 ?"<a class='j-showalert btn btn-warning btn-sm' href='javascript:;' id='j-sequence'><i class='glyphicon glyphicon-sort'></i>&nbsp;排序</a>":'';
        $bhtml .= $this->FILTERABLE ==-1 ?"<a class='j-showalert btn btn-warning btn-sm' href='javascript:;' id='j-search'><i class='glyphicon glyphicon-search'></i>&nbsp;搜索</a>":'';
        $bhtml .="<a href='javascript:;' onclick='window.location.reload();' class='j-refresh btn btn-warning btn-sm'><i class='glyphicon glyphicon-refresh'></i>&nbsp;刷新</a>";
        $bhtml .= $this->ADDABLE==-1 ? "<a class='btn btn-warning btn-sm' href='".$_SERVER['REQUEST_URI'].'&showForm=3'."'><i class='glyphicon glyphicon-plus'></i>&nbsp;新增</a>":'';
		$bhtml .= $this->GRIDMODE==3 ? "<a  class='btn btn-warning btn-sm' href='javascript:;' onclick='quickadd();' ><i class='glyphicon glyphicon-plus'></i>&nbsp;快速新增</a>":'';
        $bhtml .= "</div>";

		//$bhtml = $this->GCBTN ? '' : $bhtml;
        $bhtml .= "<div class='btn-group gabtn-group'>";
		$bhtml .= $this->GABTN;
        $bhtml .= "</div>";

		$Selected = 'Selected'.$pageSize;
		$$Selected  = "selected='selected'";
        $bhtml = $this->SHOWBOTTOMBTN==-1 ?  $bhtml : '';
        $pageHtml = "<div class='page-auth-btn'>" . $bhtml . "</div>";
        $pageHtml .= "<div class='fleft' style='margin-top: 10px;color:#999'>";
        $pageHtml .="共有<s>[{$totalRecords}]</s>条数据&nbsp;(".(($page-1)*$pageSize+1)."-".($page*$pageSize).")&nbsp;&nbsp;每页&nbsp;";
        $pageHtml .= $this->CHANGEROWS==-1 ? "<select  name='pageSize' class='pageSize' onchange=\"form_resetpagesize('$currentUrl', this.options[selectedIndex].value)\" ><option $Selected10 value='10'>10</option><option $Selected20 value='20'>20</option><option $Selected30 value='30'>30</option><option $Selected100 value='100'>100</option><option $Selected150 value='150'>150</option></select>&nbsp;条" : $pageSize.'条';

        $pageHtml .="</div><div class='fright btn-group pagination'>";
        $pageHtml .="<a class='btn btn-default btn-sm' href=".$currentUrl."&page=1&pageSize=".$pageSize.">首页</a>";
		if($page-1)
			$pageHtml .="<a class='btn btn-default btn-sm' href=".$currentUrl."&page=".($page-1)."&pageSize=".$pageSize.">上一页</a>";
		else
			$pageHtml .="<a class='btn btn-default btn-sm' href='javascript:void(0);'>上一页</a>";
		if($page+1<=$pages)
			$pageHtml .="<a class='btn btn-default btn-sm' href=".$currentUrl."&page=".($page+1)."&pageSize=".$pageSize.">下一页</a>";
		else
			$pageHtml .="<a class='btn btn-default btn-sm' href='javascript:void(0);'>下一页</a>";
        $pageHtml .= sprintf('<textarea style="display: none;" id="refer_from">%s&page=%s&pageSize=%s</textarea>', $currentUrl, $page, $pageSize); // 当前页的url
        $pageHtml .="<a class='btn btn-default btn-sm' href=".$currentUrl."&page=".$pages."&pageSize=".$pageSize.">末页</a>";
        $pageHtml .="</div>";

        return $pageHtml;
    }

	/**
     +----------------------------------------------------------
     *获取getNextcol
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
     *获取getSelectTreeOption
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return html
     +----------------------------------------------------------
     */
	public function getSelectTreeOption($fieldName,$parentKey,$outtype=1){
		if($field = $this->getCols($fieldName) ) {

            if (in_array($fieldName, $this->cachedFields) && file_exists("{$fieldName}.json")) {
                $selectList = u2g(json_decode(file_get_contents("{$fieldName}.json"), true));
            } else {
                $selectList = $field->LISTSQL ? $field->transforListsqlTree($this->model,$parentKey) : '';
                $selectList = $field->transforListTree($selectList,0,1);
                if (!empty($selectList) && in_array($fieldName, $this->cachedFields)) {
                    file_put_contents("{$fieldName}.json", json_encode(g2u($selectList), true));
                }
            }

			$options ='<option value="">请选择</option>';
			foreach($selectList as $key=>$val){
				reset($val);
				$value = current($val);
				$name = next($val);

				$selected = ($defaultValue==$value&& $defaultValue !='') ? 'selected="selected"':'';
				if($defaultValue==$value ) $Dfv =$name;$count=$val['count']>1? '├':'';
				$bq = $field->getXbq(2*($val['count']-1),'&nbsp');
				$options .= "<option value='".$value."' $selected  >$bq  $count ".$name."</option>"; //style='padding-left:".(20*($val['count']-1)) ."px;'
			}
		}
		if($outtype==1){
			header("Content-type: text/html; charset=utf-8");
			echo iconv('GBK', 'UTF-8', $options);
			if(!$this->iscontinue) exit( );
		}else {
			return iconv('GBK', 'UTF-8', $options);
		}

	}
	/**
     +----------------------------------------------------------
     *获取cols
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
     *获取all cols
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
     *获取 Form可显示 cols
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
     *获取 Grid可显示 cols
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
     *获取可排序 cols
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
     *获取 筛选 cols
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array   cols
     +----------------------------------------------------------
     */
	public function getFilterCols(){
		foreach( $this->mvarCols as $key=>$val){
			if( $val->FILTER ==-1  )  {//&& $val->GRIDVISIBLE ==-1
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
     *获取 筛选 搜索的 select下拉列表
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array   json
     +----------------------------------------------------------
     */
	public function getSelectOption(){
		$dateArr = array(13,131,132);
		$treeArr = array(23);
		$selectArr = array(22,32,42);
		$autocompleteArr = array(21,31,41);
		$field = $this->getMyField($_REQUEST['selectfield']);
		if(in_array($field->EDITTYPE,$dateArr) ){
			$data['type'] = 'date';

		}elseif(in_array($field->EDITTYPE,$treeArr) ){//tree
			$data['type'] = 'tree';
			$data['list'] = $this->getSelectTreeOption($field->FIELDNAME,$_REQUEST['parentkey'],2);

		}elseif(in_array($field->EDITTYPE,$autocompleteArr) ){
			$arr = $field->getautocompleteOption($this->model);
			$data['type'] = 'autocomplete';
			$data['list'] = $arr;

		}elseif(in_array($field->EDITTYPE,$selectArr) ){
			$arr = $field->getSelectOption($this->model);
			foreach($arr as $k=>$v){
					$arr[$k] = iconv('GBK', 'UTF-8', $v);
			}
			$data['type'] = 'select';
			$data['list'] = $arr;

		}else{
			$data['type'] = 'input';
		}
		echo json_encode($data);
		if(!$this->iscontinue) exit();
	}
	/**
     +----------------------------------------------------------
     *获取 数据源 记录
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array
     +----------------------------------------------------------
     */
	public function getRows($param,$colArr=null,$begin,$end){
		$colArr = array_merge($colArr,$this->compareField);
		//var_dump($colArr);
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

		//echo $sql;

		$data = $this->model->query($sql);
		return $data;
	}
	/**0
     +----------------------------------------------------------
     *获取 查询条件
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
     *获取 数据源 记录总数
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
     *根据ID获取 数据源 记录
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
     *根据IDs获取 数据源 记录
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
     *上传附件
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array
     +----------------------------------------------------------
     */
	public function uploadFile(){
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 10000000;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg','rar','zip','doc','docx','xls');// 设置附件上传类型
		$upload->savePath =  './Public/Uploads/';// 设置附件上传目录
		if(!$upload->upload()) {// 上传错误提示错误信息
			$this->error($upload->getErrorMsg());
		}else{// 上传成功
			$this->success(' ok');
		}
		exit(1);
	}


    /**
     +----------------------------------------------------------
     *添加隐藏INPUT
	 +----------------------------------------------------------
     * @param  array 字段信息
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
	 *添加隐藏INPUT
	+----------------------------------------------------------
	 * @param  array 字段信息
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
     *组合url
	 +----------------------------------------------------------
     * @param
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	public function joinUrl($url,$vars){
		$info =  parse_url($url);
		// 解析参数
		if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
			parse_str($vars,$vars);
		}elseif(!is_array($vars)){
			$vars = array();
		}
		// var_dump($vars);

		if(isset($info['query'])) { // 解析地址里面参数 合并到vars
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
		$data2 = $this->model->query("select  STATUS,COLOR,TYPE from ERP_STATUS   where  TYPE = $Id   order by  QUEUE ASC " );

		$html = '<tr><td>'.$data[0]['TNAME'].'</td>';
		foreach($data as $k=>$v){
			$html .= '<td>'.$v['STATUSNAME'].'</td>';
		}
		$html .= '</tr>';
		$html .= '<tr><td> 图例</td>';
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
		$script .='var curtext = $("[name=\'"+tag+"\']").parent().parent().find("span").first().html() ;';
		if($v['TYPE']==1) {
			$script .= 'if(curtext){$("[name=\'"+tag+"\']").parent().parent().find("span").addClass("btn btn-status").css({"background-color":color,"color":"#FFF","width":"100px;"});}';
		}
		$script .='	$("[name=\'"+tag+"\']").parent().parent().css({"color":color }).attr("alt", curtext ).attr("title", curtext ); });});</script>';
		if($bjarr[$Id]>1) return $script;

		return $html.$script;
		//return false;
	}


	public function showStatusTable($arrparam){
		$this->SHOWSTATUSTABLE ='<div class="divgridtable" style="display: none;"  ><table class="gridtable">';
		foreach($arrparam as $value){
			$bjarr[$value[0]]++;
			$this->SHOWSTATUSTABLE .= $this->getStatusTable($value,$bjarr);
		}
		$this->SHOWSTATUSTABLE .= '</table> </div>';


		return $this;
	}
	/**
     +----------------------------------------------------------
     *日志
	 +----------------------------------------------------------
     * @param string $data 日志信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	public function logs($data){

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

    /**
     * 剥离A标签
     * @return array
     */
    public function stripGAButtonTags() {
        $response = array();
        $nextLine = PHP_EOL;

        if (preg_match_all(self::HTML_A_TAG_PREG, $this->GABTN, $matches) !== false) {
            $response['error'] = false;
            $response['matched'] = array($matches[self::HTML_A_ROW], $matches[self::HTML_ID_ROW]);
        }
        $file = fopen('authorityMap.txt', 'w');

        $str1 = '';
        foreach($matches[self::HTML_ID_ROW] as $key => $item) {
            $str1 .= '/**' . $nextLine;
            $str1 .=" * {$matches[3][$key]}权限" . $nextLine;
            $str1 .=" */". $nextLine;

            $str1 .= 'const ' . strtoupper($item) . ' = ' . '0;' . $nextLine . $nextLine;
        };
        fwrite($file, $str1);

        $str = '// 权限映射表 ' .$nextLine.'$this->authorityMap = ' . "array(" . $nextLine;
        foreach($matches[2] as $item) {
            $str .= "'{$item}' => self::" . strtoupper($item) . "," . $nextLine;
        }
        $str = substr($str, 0, strlen($str) - strlen($nextLine) - 1);
        $str .= $nextLine . ");" . $nextLine . $nextLine . $nextLine;
        fwrite($file, $str);

        fclose($file);
        return $response;
    }

    /**
     * 控制与是否有权限关联的按钮
     * @param array|用户的权限列表 $authorities 用户的权限列表
     * @param array|需要传入项目类型进行判断 $map 需要传入项目类型进行判断
     * 大致结构如下：
     * array(
     *    commit_btn => 100
     * )
     * OR
     * array(
     *    commit_btn = array(
     *      'ds' => 200,
     *      'yg' => 300
     *    )
     * )
     * @param array $options  新增、编辑、查看、删除
     * @param string $scaleType
     */
    public function refineGAButtons($authorities = array(), $map = array(), $options = array(), $scaleType = 'default') {
        $result = $this->stripGAButtonTags();
        if ($result['error'] == true) {
            return;
        }

        foreach (array(
                     '_add' => 'ADDABLE',  // 新增
                     '_check' => 'CHECKABLE',  // 查看
                     '_edit' => 'EDITABLE',  // 编辑
                     '_del' => 'DELABLE'  // 删除
                 ) as $name => $opt) {
            if (in_array($name, array_keys($options))) {
                // 是否有新增权限
                if (is_array($options[$name]) && count($options[$name])) {
                    if (!in_array($options[$name][$scaleType], $authorities)) {
                        $this->$opt = 0;
                    }
                } else if (!in_array($options[$name], $authorities)) {
                    $this->$opt = 0;
                }
            }
        }

        // 如果不存在按钮或没有权限映射表，则直接退出
        if (!is_array($map) || empty($this->GABTN)) {
            return;
        }

        foreach ($result['matched'][1] as $key => $item) {
            if (in_array($item, array_keys($map))) {
                if (is_array($map[$item]) && count($map[$item])) {
                    $needCheckAuthorityID = $map[$item][$scaleType];
                } else {
                    $needCheckAuthorityID = $map[$item];
                }

                if (!in_array($needCheckAuthorityID, $authorities)) {
                    unset($result['matched'][0][$key]);
                }
            }
        }

        $this->GABTN = '';
        foreach ($result['matched'][0] as $item) {
            $this->GABTN .= $item;
        }
    }
}