<?php
/**
 +------------------------------------------------------------------------------
 * ������
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
class MyReport{
	protected $ReportId           = 0; //����ID
	protected $columns          = null; // ��
	protected $rows          = null; // /��
	protected $values           =null;//���
	protected $tbl            = null;//filter
	protected $model   =null;//
	protected $dataSource = null;//����Դ�б�
 

	/**
     +----------------------------------------------------------
     * ���캯�� ȡ��ģ�����ʵ��
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
     * ��ʼ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $Id ����ID
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
				//$reportlist1 = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."' and TYPE=1  ")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();//��
				//$reportlist2 = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."' and TYPE=2 ")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();//��
				//$values = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."'  and  TYPE=3")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();// ��
				$sql1 = "select b.DNAME from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$_REQUEST['ReportId']." and a.TYPE=1  ";
				$sql2 = "select b.DNAME from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$_REQUEST['ReportId']." and a.TYPE=2  ";
				$sql3 = "select b.DNAME from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$_REQUEST['ReportId']." and a.TYPE=3  ";
				$reportlist1 = $this->model->query($sql1);
				$reportlist2 = $this->model->query($sql2);
				$values = $this->model->query($sql3);
				$reportlist3 = $this->model->table('tblparm')->where("REPORTID='".$_REQUEST['ReportId']."'  ")->select();//��
 
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
     * ���沼��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $Id ����ID
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
					$this->saveDatasource($Id ,$_REQUEST['dbsourceId'] );//�޸ı�������Դ
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
     * ��ȡ����Դ  
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
     * ��ȡ���� json
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $Id ����ID
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
     * ��ȡ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $table ��
     * @param string $ID  ��ֵ
     * @param string $Field �ֶ�
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
     * ��ȡ���ݿ�DB  
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $ID ����ID
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
}