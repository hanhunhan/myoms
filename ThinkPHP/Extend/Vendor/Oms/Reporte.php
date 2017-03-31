<?php
if (is_file(dirname(__FILE__).'/Collection.php')){
	include dirname(__FILE__).'/Collection.php';
}else {
	die('Sorry. Not load Collection file.');
}
if (is_file(dirname(__FILE__).'/Ceils.php')){
	include dirname(__FILE__).'/Ceils.php';
}else {
	die('Sorry. Not load Ceils file.');
}
/**
 +------------------------------------------------------------------------------
 * 报表类
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
class Report{
	protected $RID           = 0; //报表ID
	protected $REPORTNAME           = null;//界面标题 GRID/FORM上面的标题
	protected $REPORTTYPE            = null ;//界面类型 GRID/FORM
	protected $DBSOURCEID            = null ;//数据源
	protected $width            = '100%' ;//table宽度
	protected $height            = 'auto' ;//table高度
	protected $cols				 = 0 ;//列数
	protected $rows				 = 0 ;//行数
	protected $model					= null;  //实例化一个model对象 
	protected $X                 = null;//横维
	protected $Y                 = null;//纵维
	protected $diment = null;//维度
	protected $measure = null;//测度
	protected $dimentcount = 0;//维度的层数
	protected $dimentTd = null;//维度详细
	protected $measureTd = null;//测度详细
	protected $searchInput = null;//搜索的字段
	//protected $list				 = null;

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
     * 初始化
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method 方法名
     * @param array $args 参数
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function initReport($Id){
		$this->RID = $Id;
		$data = $this->model->query("select * from MYREPORT where ID='$Id'"); //var_dump($data);
		if($info = $data[0]){
			$this->REPORTNAME = $info['REPORTNAME'];
			$this->REPORTTYPE = $info['REPORTTYPE'];
			$this->DBSOURCEID = $info['DBSOURCEID'];// 报表数据源ID
		}
		
		return $this;
	}
	/**
     +----------------------------------------------------------
     * 输出查询条件输入框
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param  
     *  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getSearchInput(){
		$filterType = C('filterType'); 
		$this->searchInput = $r = $this->model->query("select A.INPUTTYPE,B.DNAME,B.DESCRIPTION from TBLPARM A left join DIMENSIO B on A.DIMENSIOID=B.ID where A.REPORTID='$this->RID' ");  
		
		$html = ' <form class="registerform" action="" method="post" ><table width="90%" cellspacing="0"  align="center" ><tr><td><div>统计参数：';
	    foreach($r as $k=>$v){
			$html .= $v['DESCRIPTION'] ;
			$html .= '<select name="'.$v['DNAME'].'_conditiontype">';
			foreach($filterType as $kk=>$vv){
				$html .='<option value="'.$kk.'">'.$vv.'</option>';
			}
			$html .= '</select>';
			$html .=  '<input name="'.$v['DNAME'].'" type="text"/>';
		}
		$html .= '<input type="submit" name="" value="提交">';
		$html .= '</div></td></tr></table></form><br>';
		$html .='<table width="90%" cellspacing="0" cellpadding="10" border="0" align="center" style="border-collapse: collapse;" ><tr><td> <h3 style="text-align:center;">'.$this->REPORTNAME.'报表</h3></td></tr></table> ';
		return $html;

	}
	/**
     +----------------------------------------------------------
     * 输出报表
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param  
     *  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getReport(){
		$this->X = $this->getMyReportDetail($this->RID,1); //获取横维var_dump($this->X);
		$this->Y = $this->getMyReportDetail($this->RID,2); //var_dump($this->Y);
		$Di = $this->getData('DIMENSIO',$this->X[0]['DIMENSIO']);
		if($Di['TYPE']==1){//维度为横维
			$this->diment = $this->Diment($this->X);//横维是维度
			$this->measure = $this->Measure($this->Y);//纵维是测度
			$this->dimentcount = count($this->X); // print_r($this->diment);
			return $this->getTableB();  
		}else{
			$this->diment = $this->Diment($this->Y);//纵维是维度
			$this->measure = $this->Measure($this->X);//横维是测度
			$this->dimentcount = count($this->Y);// print_r($this->diment);
			return $this->getTableA();
		}
		         
		//print_r($this->measure);
		//echo json_encode($this->diment);
		
		//$this->cols = ;
		//$this->cols = ;


	}
	/**
     +----------------------------------------------------------
     * 维度为纵维的table
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param  array $data
     *  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	 
	public function getTableA(){
		 $arrcount = array();
		 $arrthish = array();
		 $arrowscount = array();
		 for($i=0; $i < $this->dimentcount; $i++){
			$arrcount[]=0;//遍历计数
			$arrthish[]=0; //进制
			$arrowscount[]=0;//跨行计数
		 }
		 $h = $this->diment->h;//最后一层高度
		 $table = $this->getSearchInput();
		 $table .='  <table width="90%" cellspacing="0" cellpadding="10" border="1" align="center" style="border-collapse: collapse;" >';      //print_r($this->diment);
		 $table .= '<tr> ';    //var_dump($this->dimentTd);
		 foreach($this->dimentTd  as $k=>$v ){//维度说明
			 $table .= '<td> '.$v['DESCRIPTION'] .'</td>'; 
		 }
		 $measure = $this->measure->children;
		 foreach( $measure as $k=>$v ){//测度
				$table .= '<td> '.$v->name .'</td>'; 
		 }
		 $r = $this->getSoureData();//获取数据
		 $table .= '</tr> '; 
		 for($i=0;$i < $h ;$i++){
			$sk = array();//记录维度
			$dimensio = $this->diment; 
			$table .= '<tr> ';  
			for($ii=0; $ii < $this->dimentcount; $ii++){ 
				 
			     $co = $arrcount[$ii];   // var_dump($dimensio->children[5]);
				 $dimensio = $dimensio->children[$co];    //var_dump($arrcount);
				// if($ii==0){ echo $arrcount[$ii].'-' ;  }
				 if($dimensio) $arrthish[$ii] = $dimensio->thish; //echo '-';//$dimensio->thish; 
				 //echo $arrowscount[$ii]; echo '-'; echo $arrthish[$ii];
				 if($ii != $this->dimentcount-1  ){
					 $table .=  $arrowscount[$ii]==0 ? '<td rowspan="'.$this->getChildrenAll($dimensio).'">'.$dimensio->name .'</td>':'';
					  
				 }
				 else  $table .=   '<td  >'.$dimensio->name .' </td>' ;
				 if($dimensio->name) $sk[]=$dimensio->value;//记录维度
				 else $sk[]=null;
				 if($arrowscount[$ii] < $this->getChildrenAll($dimensio)-1 ) $arrowscount[$ii]++;
				 else $arrowscount[$ii]=0;
				// echo $arrowscount[$ii];

				if($ii==$this->dimentcount-1){
					if($dimensio->thish-1>$arrcount[$ii] ){
						$arrcount[$ii]++;
					}else{
						$arrcount[$ii]=0;
						 
						$tii = $ii;
						//while($tii >= 1){//满则进位  待改进
							
							if(($arrthish[$tii-1]-1 )> $arrcount[$tii-1] ){
								$arrcount[$tii-1]++; 
							}else {
								$arrcount[$tii-1]=0;
								//$arrcount[$tii-2]++;
								if(($arrthish[$tii-2]-1 )> $arrcount[$tii-2] ){
									$arrcount[$tii-2]++;
								}else {
									$arrcount[$tii-2]=0;
									$arrcount[$tii-3]++;


								}


							}
							//$tii--;

						//}
					}
				}

			

			} 
			$skk = array();
			foreach($this->dimentTd as $k=>$v){
				if( $sk[$k])$skk[$v['DNAME']] = $sk[$k];
			}
			//var_dump($sk);
			//var_dump($skk); 
			foreach($measure as $ki=>$vi ){
				$measureValue = $this->searchArray($r[$vi->value],$skk); 
				$jg = $measureValue[$vi->value] ? $measureValue[$vi->value]:0; 
				$table .= '<td> '.$jg.' </td>'; 
		    }

			$table .='</tr> ';
		 }
		 return $table .='</table> ';

	}
	/**
     +----------------------------------------------------------
     * 结果查找所需值
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param   array $data 被的统计数据
     * @param   array $sk 条件
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function searchArray($data,$sk){
		
		foreach($data as $k=>$v){
			foreach($sk as $kk=>$vv){
				$res = $v[$kk] == $vv ? true:false;
			}
			if($res) return $v;
		}

	}
	/**
     +----------------------------------------------------------
     * 获取统计结果
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param    
     *  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getSoureData(){//获取数据
		$this->dimentTd;//维度
		$this->measureTd;//测度
		//var_dump($this->measureTd);
		//var_dump($_REQUEST);
		$filterType = C('filterType');
		$where = array();
		foreach($this->searchInput as $v){
			if( $_REQUEST[$v['DNAME']] ){
				//$where[]  = $v['DNAME'].'='.$_REQUEST[$v['DNAME']];
				$name = $v['DNAME'];
				$val = $_REQUEST[$v['DNAME']];
				$type = $_REQUEST[$v['DNAME'].'_conditiontype']; 
				if($type==1){
					$where[] =  " $name like '%".$val."%' ";
				}elseif($type==2){
					$where[] = " $name is null ";
				}elseif($type==4){
					$where[] =   " $name is not null ";
				}elseif($type==9){
					$where[] =   " $name in  ($val) ";
				}else{
					$where[] =  " $name $filterType[$type]  '$val'  ";
				}
				
			}
		}
		$where = implode('and',$where); 
		$where = $where ? ' where '.$where :$where;
		foreach($this->measureTd as $k=>$v){
			$soure = $this->getData('DBSOURCE',$v['DBSOURCEID']);
			$feildarr = array();
			$feild = 'sum('.$v['DNAME'].') as '.$v['DNAME'].'';
			foreach($this->dimentTd as $kk=>$vv){
				$feildarr[] = $vv['DNAME'];
			}
			$feildarr = implode(',',$feildarr);
			$feild = $feild.','. $feildarr;
			  $sql = "select $feild from (".$soure['SQLTEXT']." $where )  group by $feildarr ";
			$r[$v['DNAME']] = $this->getDb($v['DBSOURCEID'])->query($sql); 
		}

		return $r;

		
	}
	/**
     +----------------------------------------------------------
     * 统计所有子节点数量
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param   ceils $data
     *  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	 
	public function getChildrenAll($data){
		  
		  if( $data->children) {
			  //$count1 = count($data->children);
			  foreach($data->children as $k=>$v){
				  $count += $this->getChildrenAll($v);
			  }
		  }else $count=1;
		return $count;
	}
	/**
     +----------------------------------------------------------
     * 维度为横维的table
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param  array $data
     *  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getTableB(){
		 $r = $this->getSoureData();//获取数据
		 $h = $this->diment->h;//最后一层高度
		 $table = $this->getSearchInput();
		 $table .=' <table width="100%" cellspacing="0" cellpadding="10" border="1" align="center" style="border-collapse: collapse;" >';      //print_r($this->diment);
		 //$table .= '<tr> ';    //var_dump($this->dimentTd);
		  // print_r($this->diment);
		  
         $dimensio = $this->diment;
		 $dimentTd = $this->dimentTd;
         for($i=0;$i<$this->dimentcount;$i++){
			 $dimensio = $dimensio->children ? $dimensio->children:$temp; //print_r($dimensio);
			 $table .= '<tr>'; //print_r($dimensio);
			     $table .= '<td>'.$dimentTd[$i]['DESCRIPTION'] .'</td>';
			     $temp = array();
				 foreach($dimensio as $k=>$v){
					  $table .= ' <td colspan="'.$this->getChildrenAll($v).'">'.$v->name.'</td> ';
					  if($v->children) $temp = array_merge($temp,$v->children); 
					 // else var_dump($v->children);
					 
				 }//var_dump($temp);
			  
			 $table .= '</tr>';

		 }
		 $skk = array();
         $measure = $this->measure->children;
		 foreach( $measure as $k=>$v ){//测度
				$table .= '<tr><td> '.$v->name .'</td>'; 
				 for($i=0;$i<$h;$i++){
					 $skk[$i] = $skk[$i] ? $skk[$i]:$this->getTdArr($i);
					 $measureValue = $this->searchArray($r[$v->value],$skk[$i]); 
					 $jg = $measureValue[$v->value] ? $measureValue[$v->value]:0; 
					 $table .= '<td> '.$jg.' </td>'; 
					 //$table .= ' <td> '.$measureValue[$v->value].'  </td>'; 
				 }
		 }
		
		  $table .= '</tr>'; 	 

			//$table .='</tr> ';
		 
		 return $table .='</table> ';

	}
	public function getTdArr($TdNum){
		 $arrcount = array();
		 $arrthish = array();
		 $arrowscount = array();
		 for($i=0; $i < $this->dimentcount; $i++){
			$arrcount[]=0;//遍历计数
			$arrthish[]=0; //进制
			$arrowscount[]=0;//跨行计数
		 }
		 $h = $this->diment->h;//最后一层高度
		 
		 for($i=0;$i < $h ;$i++){
			$sk = array();//记录维度
			$dimensio = $this->diment; 
			 
			for($ii=0; $ii < $this->dimentcount; $ii++){ 
				 
			     $co = $arrcount[$ii];   // var_dump($dimensio->children[5]);
				 $dimensio = $dimensio->children[$co];    //var_dump($arrcount);
				 
				 if($dimensio) $arrthish[$ii] = $dimensio->thish; //echo '-';//$dimensio->thish; 
				 
				 if($dimensio->name) $sk[]=$dimensio->value;//记录维度
				 else $sk[]=null;
				 if($arrowscount[$ii] < $this->getChildrenAll($dimensio)-1 ) $arrowscount[$ii]++;
				 else $arrowscount[$ii]=0;
				 

				if($ii==$this->dimentcount-1){
					if($dimensio->thish-1>$arrcount[$ii] ){
						$arrcount[$ii]++;
					}else{
						$arrcount[$ii]=0;
						 
						$tii = $ii;
						//while($tii >= 1){//满则进位  待改进
							
							if(($arrthish[$tii-1]-1 )> $arrcount[$tii-1] ){
								$arrcount[$tii-1]++; 
							}else {
								$arrcount[$tii-1]=0;
								//$arrcount[$tii-2]++;
								if(($arrthish[$tii-2]-1 )> $arrcount[$tii-2] ){
									$arrcount[$tii-2]++;
								}else {
									$arrcount[$tii-2]=0;
									$arrcount[$tii-3]++;


								}


							}
							//$tii--;

						//}
					}
				}

			

			} 
			$skk = array();
			foreach($this->dimentTd as $k=>$v){
				if( $sk[$k])$skk[$v['DNAME']] = $sk[$k];
			}
			if($TdNum == $i) return $skk;
			 
		 }
		 
	}
	/**
     +----------------------------------------------------------
     * 维度
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param  array $data
     *  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function Diment($data){
		$arr = array();  
		foreach($data as $key=>$value){
			$this->dimentTd[]= $dimensio = $this->getData('DIMENSIO', $value['DIMENSIO']);
			if($dimensio['PARENT']){
				if($data[$key-1]){
						 $list = array();
						 $dimensiot = $this->getData('DIMENSIO', $data[$key-1]['DIMENSIO']);
						 //$rowst = $this->getSource($dimensiot['DATASOURCE'] );//var_dump($rowst);
						 $rowst = $arr[$key-1];
						 foreach($rowst as $k=>$v){
							 //$rows = array_merge($rows , $this->getSource($dimensio['DATASOURCE'],$dimensio['PARENT'],$k) );	
							 $rows = $this->getSource($dimensio['DATASOURCE'],$dimensio['PARENT'],$v->value);
							 if($rows){
								 foreach($rows as $kk=>$vv){
									 $ceil = new Ceils();
									 $ceil->name = $vv['val'];
									 $ceil->value = $kk;
									 $ceil->parentField = $dimensio['PARENT'];
									 $ceil->parentKey = $vv['parentKey'];
									 $list[] = $ceil;
									 //print_r($list);
								 }
							 }else{
									 $ceil = new Ceils();
									 $ceil->name = '';
									 $ceil->value = $v->value*$this->randpw();
									 $ceil->parentField = $dimensio['PARENT'];
									 $ceil->parentKey = $v->value;
									 $list[] = $ceil;

							 }

						 }
						


				}
			}else 
			{
				 $rows = $this->getSource($dimensio['DATASOURCE'],$dimensio['PARENT'],$parentKey);	 
				 $list =   array();
				 foreach($rows as $k=>$v){
					 $ceil = new Ceils();
					 $ceil->name = $v;
					 $ceil->value = $k;
					 $ceil->parentField = $dimensio['PARENT'];
					 
					 $list[] = $ceil;
					 

				 }
			}
			 $arr[] = $list;

          


		}
		$temp = array_reverse($arr);// print_r($arr);
		$len = 0;
		foreach($temp as $k=>$v){
			//if($k==0) $len = count($v);
			//else {
				//echo  $len.'*'.count($v).'|';
				//$len = $len*count($v);
			//}
			foreach($v as $key=>$value){
				$v[$key]->thish = count($v);
				
			}
			$parentField  =$v[0]->parentField;
			if($temp[$k+1]){
				if( $parentField ==null){
					
					foreach($temp[$k+1] as $kk=>$vv){
						$vv->children = $v;
					}
					 
				}else{
					    foreach($temp[$k+1] as $kk=>$vv){ 
							$arrlist = null;
							foreach($v as $kkk=>$vvv){  //echo $vv->value .'-'. $vvv->parentKey.'|';var_dump($vvv);
								if($vv->value == $vvv->parentKey) 
								$arrlist[] = $vvv;
							}
							if($arrlist==null){
								$ceil = new Ceils();
								//$ceil->name = 1;
								//$ceil->value = 1;
								$ceil->thish = 1;
								$ceil->parentKey = $vv->value;
								$arrlist[] = $ceil;//可删？
							}else{
								$tthish = count($arrlist);
								foreach($arrlist as $kkk=>$vvv){
									$vvv->thish = $tthish;
								}

							}
							$vv->children = $arrlist;

							//$vv->children = $v[$vv->value];
						}
					 
				}
			}

		}
		$topCeil = new Ceils();
		$topCeil->name = 'top';
		$topCeil->children =  $temp[count($temp)-1];
		$topCeil->h = $this->getChildrenAll($topCeil);//$len;
        return $topCeil;

		 


	}
	/**
     +----------------------------------------------------------
     * 随机数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param  int $len 长度
     * @param  string $format 类型
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function randpw($len=8,$format='NUMBER'){ 
		$is_abc = $is_numer = 0; 
		$password = $tmp ='';   
		switch($format){ 
			case 'ALL': 
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			break; 
			case 'CHAR': 
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			break; 
			case 'NUMBER': $chars='0123456789'; 
			break; 
			default : $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'; 
			break; } // www.jb51.net 
			mt_srand((double)microtime()*1000000*getmypid()); 
			while(strlen($password)<$len){ 
				$tmp =substr($chars,(mt_rand()%strlen($chars)),1); 
				if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
					$is_numer = 1; 
				} 
				if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
					$is_abc = 1; 
				} 
				$password.= $tmp; 
			 } 
			 if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){ 
				$password = randpw($len,$format); 
			 } 
			return $password; 
		} 
	/**
     +----------------------------------------------------------
     * 测度
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param  
     *  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function Measure($data){
		 foreach($data as $key=>$value){
				 $this->measureTd[] = $dimensio = $this->getData('DIMENSIO', $value['DIMENSIO']);
				 $ceil = new Ceils();
				 $ceil->name = $dimensio['DESCRIPTION'];
				 $ceil->value = $dimensio['DNAME'];
				  
				 $list[] = $ceil;
		 }

		$topCeil = new Ceils();	
		$topCeil->children = $list;
        return $topCeil;
		 


	}
	 /**
     +----------------------------------------------------------
     *  获取 数据源 
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function getSource($listchar,$parentField='',$parentKey=''){
		 if(strpos($listchar,'^') ){
			 return $this->transforListchar($listchar);
		 }else  return $this->transforListsql($listchar ,$parentField,$parentKey);
	 }

	 /**
     +----------------------------------------------------------
     *  转换 数据源 Array
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function transforListchar($listchar){
		$arr =  array();
		if($listchar){
			$tempArr = explode('^',$listchar); 
			if(is_array($tempArr)){
				foreach($tempArr as $key=>$val){
					if($key%2==1  ) $arr[$val] = $tempArr[$key-1];
				}
			}
		}
		return $arr;
	 }
	 /**
     +----------------------------------------------------------
     *  转换 数据源 SQL
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function transforListsql($listchar,$parentField='',$parentKey=''){
		$arr =  array();
		if($listchar){
			if($parentField && $parentKey) $listchar .= " where $parentField='$parentKey' ";
			$data = $this->model->query($listchar); 
			if($data){
				foreach($data as $key=>$val){ 
					if($parentField && $parentKey) {
						$key = current($val);
						$temp['parentKey']=$parentKey; 
						$temp['val']= next($val);
						$arr[$key] = $temp;
					}else $arr[current($val)] = next($val); 
				}
			}
		} 
		return $arr;
	 }
	/**
     +----------------------------------------------------------
     * 获取数据库DB  
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $ID 数据源ID
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
     * 获取维度明细
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $ID 报表ID
     * @param string $type 类型
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getMyReportDetail($Id,$type){   //echo "select * from MYREPORTDETAIL where REPORTID='$Id' and TYPE ='$type' order by QUEUE asc";
		  $data = $this->model->query("select * from MYREPORTDETAIL where REPORTID='$Id' and TYPE ='$type' order by QUEUE asc");
		  return $data;
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
	public function getData($table,$Id,$Field='ID'){
		  $data = $this->model->query("select * from $table where $Field ='$Id'");
		  return $data[0];
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