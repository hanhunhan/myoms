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
class ProReport{
	protected $ReportId           = 0; //����ID
	protected $x          = null; // ��
	protected $y          = null; // /��
	protected $c           =null;//���
	protected $p           =null;//����
	protected $tbl            = null;//filter
	protected $model   =null;//
	protected $dataSource = null;//����Դ�б�
	protected $TreeData;
 

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
			$sql1 = "select b.DNAME,b.DATASOURCE,b.PARENT from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$Id." and a.TYPE=1 order by a.QUEUE asc";
			$sql2 = "select b.DNAME,b.DATASOURCE,b.PARENT from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$Id." and a.TYPE=2 order by a.QUEUE asc  ";
			$sql3 = "select b.DNAME,b.DATASOURCE from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$Id." and a.TYPE=3 order by a.QUEUE asc ";
			$xres= $this->model->query($sql1);//��
			$yres = $this->model->query($sql2);
			$cres = $this->model->query($sql3);
			//$reportlist1 = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."' and TYPE=1  ")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();//��
			//$reportlist2 = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."' and TYPE=2 ")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();//��
			//$values = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."'  and  TYPE=3")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();// ��
			$pres= $this->model->table('tblparm')->where("REPORTID='".$_REQUEST['ReportId']."'  ")->select();//��
			$this->x=$this->getArrayList($xres);
			$this->y=$this->getArrayList($yres);  
			//$this->y=$this->TreeData;
			   // print_r($this->x);
			 
			
	} 
	//��ȡά������
	public function getDimensioSource($str){
		 if(stristr($str,'select')){	 
			$data = $this->model->query($str); 
		 }else{
			$data = explode(',',$str);
		 }

		 return $data ;


	}
	public function getArrayList($arr){
		foreach($arr as $key=>$val){
			 
			$source = next($val);
			$pfield = next($val);
			$sourceres[] = $this->getDimensioSource($source);
			


		}     //var_dump($sourceres); 
		//foreach($sourceres as $key=>$val){
			//$this->arryQueue($key,$val,$sourceres,0); 
		//}
		$this->TreeData = array();
		return $this->arryQueue(0,$sourceres[0],$sourceres,0); 

	}
	public function arryQueue($key,$val,$sourceres,$fid){
		if($val){
		foreach($val as $k=>$v){
				$id =current($v);
				$name = next($v);
				$pid = next($v);
				$v['id'] = $id;
				 
				$v['NAME'] = $name;
			 
				$v['count'] = $key+1;
				if($pid!=null  ){//??������
					$v['cid'] = $id;
					$v['fid'] = $fid; 
					if($pid==$fid){
						
						$this->TreeData[] = $v; 
						if(count($sourceres)>$key+1 && $id!=0){
						$this->arryQueue($key+1,$sourceres[$key+1],$sourceres,$id );

					}
						
					}
					
				}else{
					$v['cid'] = count($this->TreeData)+1;
					$v['fid'] = $fid;
					$this->TreeData[] = $v; 
					if(count($sourceres)>$key+1 && $id!=0){
						$this->arryQueue($key+1,$sourceres[$key+1],$sourceres,$v['cid'] );

					}
				}
		} 
		}
		return $this->TreeData;
	}
	/**
         * �����鰴Ҫ������
         */
	public function getCatelist($data ,$parentId=0,$count=1){
		if($data){
				foreach($data as $key=>$val){ 
					$id =current($val);
					next($val); 
					$pid = next($val);
					$val['cid'] = $id;
					$val['fid'] = $pid;
					if($pid == $parentId) {
						$val['count'] = $count;
							
						$this->TreeData[] = $val;
						unset($data[$key]);
						$this->getCatelist($data,$id,$count+1);
					}
				}
			
		} 
		return $this->TreeData;
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