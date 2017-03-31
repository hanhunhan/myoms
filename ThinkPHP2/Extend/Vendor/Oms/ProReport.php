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
class ProReport{
	protected $ReportId           = 0; //报表ID
	protected $x          = null; // 横
	protected $y          = null; // /纵
	protected $c           =null;//测度
	protected $p           =null;//参数
	protected $tbl            = null;//filter
	protected $model   =null;//
	protected $dataSource = null;//数据源列表
	protected $TreeData;
 

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
			$sql1 = "select b.DNAME,b.DATASOURCE,b.PARENT from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$Id." and a.TYPE=1 order by a.QUEUE asc";
			$sql2 = "select b.DNAME,b.DATASOURCE,b.PARENT from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$Id." and a.TYPE=2 order by a.QUEUE asc  ";
			$sql3 = "select b.DNAME,b.DATASOURCE from MYREPORTDETAIL a left join DIMENSIO b on a.DIMENSIO=b.ID where  a.REPORTID=".$Id." and a.TYPE=3 order by a.QUEUE asc ";
			$xres= $this->model->query($sql1);//横
			$yres = $this->model->query($sql2);
			$cres = $this->model->query($sql3);
			//$reportlist1 = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."' and TYPE=1  ")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();//横
			//$reportlist2 = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."' and TYPE=2 ")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();//纵
			//$values = $this->model->table('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."'  and  TYPE=3")->field('DIMENSIO,QUEUE')->order('QUEUE asc')->select();// 测
			$pres= $this->model->table('tblparm')->where("REPORTID='".$_REQUEST['ReportId']."'  ")->select();//参
			$this->x=$this->getArrayList($xres);
			$this->y=$this->getArrayList($yres);  
			//$this->y=$this->TreeData;
			   // print_r($this->x);
			 
			
	} 
	//获取维度数据
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
				if($pid!=null  ){//??待完善
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
         * 对数组按要求排序
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