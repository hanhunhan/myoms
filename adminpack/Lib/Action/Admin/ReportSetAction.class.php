<?php
	class ReportSetAction extends ExtendAction{
		
		 
		 function DbConnetct(){// 
			 
			Vendor('Oms.Form');
			$FORMNO =84; 
			$form = new Form();
			$form =  $form->initForminfo($FORMNO )->getResult();
			$this->assign('form',$form);
			$this->display('DbConnetct');
		 }
		  function DbSource(){// 
			 
			Vendor('Oms.Form');
			$FORMNO =85; 
			$form = new Form();
			$form =  $form->initForminfo($FORMNO )->getResult();
			$this->assign('form',$form);
			$this->display('DbSource');
		 }
		  function ReportType(){// 
			 
			Vendor('Oms.Form');
			$FORMNO =86; 
			$form = new Form();
			$form =  $form->initForminfo($FORMNO )->getResult();
			$this->assign('form',$form);
			$this->display('ReportType');
		 }
		 function DimenSio(){// 
			 
			Vendor('Oms.Form');
			$FORMNO =87; 
			$form = new Form();
			$form =  $form->initForminfo($FORMNO )->getResult();
			$this->assign('form',$form);
			$this->display('DimenSio');
		 }
		  function DimOrder(){// 
			 
			Vendor('Oms.Form');
			$FORMNO =88; 
			$form = new Form();
			$form->CZBTN ='<a class="contrtable-link" onclick="editThis(this);" href="javascript:;">修改</a>
			|<a class="contrtable-link" onclick="detailThis(this);" href="javascript:;">明细</a>|<a class="contrtable-link" onclick="fdel(this,\'\');" href="javascript:;"> 删除</a>';
			$form =  $form->initForminfo($FORMNO )->getResult();
			$this->assign('form',$form);
			$this->display('DimOrder');
		 }
		 function DimOrderDetail(){// 
			$_REQUEST['search5']='DIMORDERID';
			$_REQUEST['search5_s']='3';
			$_REQUEST['search5_t']=$_REQUEST['DimorderId']; 
			$data = D('dimorder')->where("ID='".$_REQUEST['DimorderId']."'")->find();
			Vendor('Oms.Form');
			$FORMNO =89; 
			$Lsql ="select ID,DNAME from DIMENSIO where DBSOURCEID='".$data['DBSOURCEID']."' and TYPE=1";
			$form = new Form();
			$form =  $form->initForminfo($FORMNO )->setMyFieldVal('DIMORDERID',$_REQUEST['DimorderId'],true)->setMyField('DIMENSIOID',LISTSQL,$Lsql)->getResult();
			$this->assign('form',$form);
			$this->display('DimOrderDetail');
		 }
		   function Myreport(){// 
			 
			Vendor('Oms.Form');
			$FORMNO =90; 
			$form = new Form();
			$form->CZBTN ='<a class="contrtable-link" onclick="viewThis(this);" href="javascript:;">预览</a>
			|<a class="contrtable-link" onclick="editThis(this);" href="javascript:;">修改</a>
			|<a class="contrtable-link" onclick="detailThis(this);" href="javascript:;">明细</a>|<a class="contrtable-link" onclick="tblparmThis(this);" href="javascript:;">统计参数</a>|<a class="contrtable-link" onclick="fdel(this,\'\');" href="javascript:;"> 删除</a>';
			$form =  $form->initForminfo($FORMNO )->getResult();
			$this->assign('form',$form);
			$this->display('Myreport');
		 } 
		 function MyreportDetail(){//
			$_REQUEST['search5']='REPORTID';
			$_REQUEST['search5_s']='3';
			$_REQUEST['search5_t']=$_REQUEST['ReportId']; 
			$data = D('myreport')->where("ID='".$_REQUEST['ReportId']."'")->find(); 
			Vendor('Oms.Form');
			$FORMNO =91; 
			$Lsql ="select ID,DESCRIPTION  from DIMENSIO where DBSOURCEID='".$data['DBSOURCEID']."'";
			//$Lsql ="select ID,DESCRIPTION  from DIMENSIO where TYPE=1 ";
			$form = new Form();
			$form =  $form->initForminfo($FORMNO )->setMyFieldVal('REPORTID',$_REQUEST['ReportId'],true)->setMyField('DIMENSIO',LISTSQL,$Lsql)->getResult();
			$this->assign('form',$form);
			$this->display('MyreportDetail');
		 } 
		  function Tblparm(){// 
			 $_REQUEST['search5']='REPORTID';
			$_REQUEST['search5_s']='3';
			$_REQUEST['search5_t']=$_REQUEST['ReportId']; 
			$list = D('myreportdetail')->where("REPORTID='".$_REQUEST['ReportId']."'")->select();
			foreach($list as $v ){
				$temp[] = $v['DIMENSIO'];
			}
			$ids = implode(',',$temp);
			$Lsql ="select ID,DNAME from DIMENSIO where ID in ($ids)"; 
			Vendor('Oms.Form');
			$FORMNO =92; 
			$form = new Form();
			$form =  $form->initForminfo($FORMNO )->setMyFieldVal('REPORTID',$_REQUEST['ReportId'],true)->setMyField('DIMENSIOID',LISTSQL,$Lsql)->getResult();
			$this->assign('form',$form);
			$this->display('Tblparm');
		 } 
		 function viewReport(){//预览报表
			Vendor('Oms.Report');
			$report = new Report();
			echo $r = $report->initReport($_REQUEST['ReportId'])->getReport();
		 }
		  function viewReport2(){//预览报表2
			//$report = D('myreport')->where("ID='".$_REQUEST['ReportId']."'")->find(); 
			Vendor('Oms.MyReport');
			$report = new MyReport();
			$report->initReport($_REQUEST['ReportId']);
			 
			 
			$this->assign('columns',$report->columns); 
			$this->assign('rows',$report->rows);
			$this->assign('values',$report->values); 
			$this->assign('tbl',$report->tbl);
			$this->assign('dataSource',$report->dataSource);  
			$this->display('viewReport2');
		 }


	}
 