<?php
/**
 +------------------------------------------------------------------------------
 * 报表类
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
class MyReport{
	protected $ReportId           = 0; //报表ID
	protected $columns          = null; // 横
	protected $rows          = null; // /纵
	protected $values           =null;//测度
	protected $tbl            = null;//filter
	protected $model   =null;//
	protected $dataSource = null;//数据源列表
 

	/**
     +----------------------------------------------------------
     * 构造函数 取得模板对象实例
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
	 
		$this->model = new Model();
		$this->dataSource = $this->getDataSource();
		
		 
    }
	/**
     +----------------------------------------------------------
     * 初始化
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $Id 报表ID
     * @ 
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function initReport($Id){
			$this->getJson($Id);
			$this->saveLayout($Id);
			//$model = new Model();
			$this->ReportId = $Id;
			$report = $this->getData('MYREPORT',$Id); 
			$dataSrId = $report['DBSOURCEID'];
			if(!$_REQUEST['dbsourceId'] || $dataSrId==$_REQUEST['dbsourceId'] ){
				//$reportlist1 = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."' and TYPE=1  ")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();//横
				//$reportlist2 = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."' and TYPE=2 ")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();//纵
				//$values = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."'  and  TYPE=3")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();// 测
				$sql1 = "select b.DNAME from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$_REQUEST['ReportId']." and a.TYPE=1  ";
				$sql2 = "select b.DNAME from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$_REQUEST['ReportId']." and a.TYPE=2  ";
				$sql3 = "select b.DNAME from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$_REQUEST['ReportId']." and a.TYPE=3  ";
				$reportlist1 = $this->model->query($sql1);
				$reportlist2 = $this->model->query($sql2);
				$values = $this->model->query($sql3);
				$reportlist3 = $this->model->table('tblparm')->where("REPORTID='".$_REQUEST['ReportId']."'  ")->select();//参
 
				foreach($reportlist1 as $v){
					$columns[] = "'".$v['DNAME']."'";
				}  
				$this->columns = implode(',',$columns); 
				foreach($reportlist2 as $v){
					$rows[] =  "'".$v['DNAME']."'";
				}
				$this->rows = implode(',',$rows);
				foreach($reportlist3 as $v){
					$tbl[] =  "'".$v['DIMENSIO']."'";
				}
				$this->tbl = implode(',',$tbl);
				foreach($values as $v){ 
					$tt[] = "{field:'$v[DNAME]',op:'sum'}";

				}
				$this->values = implode(',',$tt);
			} 
			
	}
	/**
     +----------------------------------------------------------
     * 保存布局
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $Id 报表ID
     * @ 
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function saveLayout($Id){
			if($_REQUEST['saveLayout']){
				$report = $this->getData('MYREPORT',$Id); 
				$dataSrId = $report['DBSOURCEID'];
				if($_REQUEST['dbsourceId']!=$dataSrId){
					$this->saveDatasource($Id ,$_REQUEST['dbsourceId'] );//修改报表数据源
				}
				if($_REQUEST['rows'] ){
					//$temp = explode(',',$_REQUEST['rows']);
					$this->delMyreportDetail($Id,2);
					foreach($_REQUEST['rows'] as $key=>$v){
						$v = iconv('UTF-8', 'GB2312', $v);
						$v = $this->getDimensioID($v,$dataSrId); 
						$this->saveMyreportDetail($Id,$v,2,$key);
					}
				}else{
					$this->delMyreportDetail($Id,2);
				}
				if($_REQUEST['columns']){
					//$temp = explode(',',$_REQUEST['columns']);
					$this->delMyreportDetail($Id,1);
					foreach($_REQUEST['columns'] as $key=>$v){
						$v = iconv('UTF-8', 'GB2312', $v);
						$v = $this->getDimensioID($v,$dataSrId);
						$this->saveMyreportDetail($Id,$v,1,$key);
					}

				}else{
					$this->delMyreportDetail($Id,1);
				}
				if($_REQUEST['values']){
					//$temp = explode(',',$_REQUEST['values']);
					$this->delMyreportDetail($Id,3);
					foreach($_REQUEST['values'] as $key=>$v){
						$v = iconv('UTF-8', 'GB2312', $v['field']);
						$v = $this->getDimensioID($v,$dataSrId);
						$this->saveMyreportDetail($Id,$v,3,$key);
					}

				}else{
					$this->delMyreportDetail($Id,3);
				}
				if($_REQUEST['filters']){
					//$temp = explode(',',$_REQUEST['filters']);
					$this->delTblparm($Id );
					foreach($_REQUEST['filters'] as $key=>$v){
						$v = iconv('UTF-8', 'GB2312', $v);
						//$dimensio = $this->getDimensioID($v,$dataSrId);
						$this->saveTblparm($Id,$v );
					}

				}else{
					$this->delTblparm($Id );
				}
			exit();

			}
			
			
	}
	public function getDimensioID($Dname,$dataSrId){
		 $dimensio = $this->getData('DIMENSIO',$Dname,'DESCRIPTION'," and DBSOURCEID=$dataSrId "); 
		 return $dimensio['ID'];
	}
	public function saveMyreportDetail($reportid,$dimensio,$type,$queue){ 
		 
		  $this->model->execute("INSERT INTO MYREPORTDETAIL(ID,REPORTID,DIMENSIO,TYPE,QUEUE)VALUES(SEQ_MYREPORTDETAIL.nextval,$reportid,'$dimensio',$type,$queue)");  
	}
	public function delMyreportDetail($reportid,$type ){
		  $this->model->execute("delete from MYREPORTDETAIL where REPORTID=$reportid and TYPE=$type");  
	}
	public function saveTblparm($reportid,$dimensio ){
		  $this->model->execute("INSERT INTO TBLPARM(ID,REPORTID,DIMENSIOID )VALUES(SEQ_MYREPORTDETAIL.nextval,$reportid,'$dimensio' )");
	}
	public function delTblparm($reportid  ){
		  $this->model->execute("delete from TBLPARM where REPORTID=$reportid  ");  
	}
	public function saveDatasource($id ,$dbsourceId ){
		  $this->model->execute("update MYREPORT set DBSOURCEID=$dbsourceId where ID=$id  ");  
	}
	/**
     +----------------------------------------------------------
     * 获取数据源  
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @   
     * @ 
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getDataSource(){
		 $sql= "select * from DBSOURCE  ";
		 $data = $this->model->query($sql); 
		 return $data ;
	}
	/**
     +----------------------------------------------------------
     * 获取数据 json
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $Id 报表ID
     * @ 
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getJson($Id){
			if($_REQUEST['getJson']){
				$report = $this->getData('MYREPORT',$Id);
				$dbsourceId = $_REQUEST['dbsourceId'] ?$_REQUEST['dbsourceId'] :$report['DBSOURCEID'];
				$dataSr = $this->getData('DBSOURCE',$dbsourceId);
				$res = $this->getDb($report['DBSOURCEID'])->query($dataSr['SQLTEXT']); 
				$res = $this->url_encode($res);
				exit((json_encode($res )));
			}
			
	}

	function url_encode($str) {
		if(is_array($str)) {
			foreach($str as $key=>$value){
				//$str2[urlencode($key)] = $this->url_encode($value);
				$str2[iconv("gbk", "utf-8", $key)] = $this->url_encode($value);
			}
		}else{
			//$str2 = urlencode($str);
			$str2 = iconv("gbk", "utf-8", $str);
		}

		return $str2;
	}
	function encodeArr($arr){
		foreach($arr as $key=>$value){
			$key2 = iconv('GB2312', 'UTF-8', $key);
			if(is_array($value)){
				$arr2[$key2] = $this->encodeArr($value);
			}else{
				$arr2[$key2] = iconv('GB2312', 'UTF-8', $value);
			}
		}
		return $arr2;
	}
	 
 
	/**
     +----------------------------------------------------------
     * 获取数据
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $table 表
     * @param string $ID  键值
     * @param string $Field 字段
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getData($table,$Id,$Field='ID',$where=null){
		  $sql= "select * from $table where $Field ='$Id' $where";
		 $data = $this->model->query($sql); 
		 return $data[0];
	}


	 /**
     +----------------------------------------------------------
     * 获取数据库DB  
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $ID 报表ID
     * 
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getDb($Id){
		 $Id = $Id ? $Id : 0;
		 if($Id ) {
			 //if($this->model->db($Id)){
				//return $this->model->db($Id); 
			 //}else {
				 
				 $dataSr = $this->getData('DBSOURCE',$Id); 
				 $Dbtype = C('DBTYPE');
				 $dataString = $this->getData('DBCONNETCT',$dataSr['DBCONNETCTID']);
				 $str = $Dbtype[$dataString['DBTYPE']].'://'.$dataString['DBUSER'].':'.$dataString['DBPWD'].'@'.$dataString['DBHOST'].':'.$dataString['DBPORT'].'/'.$dataString['DB'];
				 return $this->model->db($Id,$str);
			//}
		 }else return $this->model->db(0);

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
}