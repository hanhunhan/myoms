<?php
ini_set('display_errors','on');
error_reporting(E_ALL);
include_once("../common/mysql.class.php");
include_once("../common/function.php");
include_once("../common/phpExcel/PHPExcel.php");
include_once("../common/check.php");
include_once("../common/auth.php");
if($_REQUEST['action']=='gettongji'){
	$page = $_REQUEST['page']?$_REQUEST['page']:1;
	$rows = $_REQUEST['rows']?$_REQUEST['rows']:50;
	$star = ($page-1)*$rows;
	$lenth = $rows;
	$limit = " limit $star ,$lenth";
	//if($_REQUEST['action2']=='export' && !$_REQUEST['itemid'])
		//$limit = ''; 
 
	if($_REQUEST['itemid']){
		$itemid = implode(',',getChildrens($db,$_REQUEST['itemid']) );
	  	$sql = "SELECT * FROM ".$DB_PREFIX."new_user a left join ".$DB_PREFIX."new_user_result b  on a.USER_ID=b.uid where a.DEPT_ID in($itemid) and a.needsend=1  order by b.id desc";
	}else $sql = "SELECT * FROM ".$DB_PREFIX."new_user a left join ".$DB_PREFIX."new_user_result b  on a.USER_ID=b.uid  where a.needsend=1  order by b.id desc";
	   
		$row = $db->getAll($sql); 
		$total = count($row);
		$sql .= $limit;
		$row = $db->getAll($sql);
		
		$result['total'] = $total;
	if($row){
			 


		foreach($row as $key=>$val){ 
			$deptarr = geParents($db,$val['DEPT_ID']);
			$deptarr = array_reverse($deptarr);
			$one['dept0'] = $deptarr[1] ? g2u($deptarr[1]):'';
			$one['dept1'] = $deptarr[2] ? g2u($deptarr[2]):'';
			$one['dept2'] = $deptarr[3] ? g2u($deptarr[3]):'';
			$one['dept3'] = $deptarr[4] ? g2u($deptarr[4]):'';
			$one['dept4'] = $deptarr[5] ? g2u($deptarr[5]):'';
			$one['username'] = g2u($val['USER_NAME']);
			$one['userid'] =  $val['USER_ID'] ;
			$one['oastatus'] =  $val['oastatus'] ;
			$one['other'] =  g2u($val['other']) ;
			$code = trim($val['USER_ID']).'$test';
			$one['code'] =  urlencode(get_authcode($code,'ENCODE'));
			  
			for($i=1;$i<14;$i++){
				$one['question'.$i] = $val['score'.$i];
			}
			$result['rows'][] = $one;
		}
		
		 
		 
		
	}else{
		$result['rows'] =array();
		$result['total']=$total;
	}
	if($_REQUEST['action2']=='export'){
		export_data($result['rows']); //exit;
	}else {
		echo json_encode($result); exit;
	}
}elseif($_REQUEST['action']=='oa_notice'){

	if($_REQUEST['touid']){
		$touid = $_REQUEST['touid'];
		$sql = "SELECT * FROM ".$DB_PREFIX."new_user where USER_ID='$touid'";
		$user = $db->getOne($sql); 
		$touser = $user['USER_NAME'];
		$touid = $user['USER_ID'];
		//$url = 'http://'.$_SERVER['HTTP_HOST'].'/'
		$sql = "SELECT * FROM ".$DB_PREFIX."new_admin_set where id=1";
		$set = $db->getOne($sql); 
		$startime = $set['startime'];
		$endtime = $set['endtime'];
		$sendtime = date("Y��m��d�� H:i:s");
		$subject = "��μ�������������֯��Χ���߲���";
		$code= $touid.'$'.$touser;
		$authcode = urlencode(get_authcode($code,'ENCODE'));
		$urll = "http://zt.house365.com/njzt/2016/11/21/zzfw/mark/index.php?authcode=$authcode";
		$content =  <<< eod2
 
$touser :<br>���ã�<br>
���������������μ���֯��Χ���߲�����ʱ���ѽӽ�β����<br> 
 ����������µ�ַ�������ƽ̨��<a href='$urll' target='_blank'> $urll</a> <br> 
 
 ��������ר��������ַ����������Ϣ�󶨣�����ת�������ˡ������Ŷӳ�ŵ�����ϸ�֤���η����������Ժ�����׼ȷ�ԡ�<br> 
 ����޷�����������ӣ��������֯��ΧOAͨ��ֱ������<br> 
 ���β���������<font color="#ff0000"> $startime</font> ��Ч���� <font color="#ff0000"> $endtime</font> ʧЧ����ץ��ʱ����ɲ�����<br> 
 �������� <br> 
$sendtime <br>
 
<br> <br> <br> <br> 
 ����(Declaration):<br> 
 ���ʼ����б�����Ϣ���������ռ������á���ֹ�κ���δ��������������κ���ʽ�������������ڲ��ֵ�й¶�����ƻ�ɢ����������ʹ�ñ��ʼ��е���Ϣ������������˱��ʼ������������绰���ʼ�֪ͨ�����˲�ɾ�����ʼ���лл��<br> 
 This email contains confidential information, which is intended only for the receiver. Any use of the information contained herein in any way (including, but not limited to, total or partial disclosure, reproduction, or dissemination) by persons other than the intended recipient(s) is prohibited. If you receive this email in error, please notify the sender by phone or email immediately and delete it. Thanks!
eod2;
		oa_notice($_REQUEST['touid'], $_REQUEST['touid'], $subject, $content);exit;
	}
}



function export_data($data){
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);
	$objActSheet = $objPHPExcel->getActiveSheet();
	$Exceltitle = '��֯��Χ���и�ҵ��ʵ����������ֲ�����ֵ�����';
	$objActSheet->setTitle(g2u($Exceltitle));
	$objActSheet->getDefaultRowDimension()->setRowHeight(16);//Ĭ���п�
	$objActSheet->getDefaultColumnDimension()->setWidth(16);//Ĭ���п�
	$objActSheet->getDefaultStyle()->getFont()->setName(g2u('����'));
	$objActSheet->getDefaultStyle()->getFont()->setSize(10);
	$objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
	$objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objActSheet->getRowDimension('1')->setRowHeight(40);
	$objActSheet->getRowDimension('2')->setRowHeight(26);
	
	$i = 1;
	$objActSheet->getStyle('A'.$i.':T'.$i)->getFont()->setName('Candara' );
	$objActSheet->getStyle('A'.$i.':T'.$i)->getFont()->setSize(12);
	$objActSheet->getStyle('A'.$i.':T'.$i)->getFont()->setBold(true);
	 
	$objActSheet->setCellValue('A'.$i, g2u('��ϵ'));
	$objActSheet->setCellValue('B'.$i, g2u('��λ/����'));
	$objActSheet->setCellValue('C'.$i, g2u('һ������'));
	$objActSheet->setCellValue('D'.$i, g2u('��������'));
	$objActSheet->setCellValue('E'.$i, g2u('��������'));
	$objActSheet->setCellValue('F'.$i, g2u('Ա��'));
	$objActSheet->setCellValue('G'.$i, g2u('��1��'));
	$objActSheet->setCellValue('H'.$i, g2u('��2��'));
	$objActSheet->setCellValue('I'.$i, g2u('��3��'));
	$objActSheet->setCellValue('J'.$i, g2u('��4��'));
	$objActSheet->setCellValue('K'.$i, g2u('��5��'));
	$objActSheet->setCellValue('L'.$i, g2u('��6��'));
	$objActSheet->setCellValue('M'.$i, g2u('��7��'));
	$objActSheet->setCellValue('N'.$i, g2u('��8��'));
	$objActSheet->setCellValue('O'.$i, g2u('��9��'));
	$objActSheet->setCellValue('P'.$i, g2u('��10��'));
	$objActSheet->setCellValue('Q'.$i, g2u('��11��'));
	$objActSheet->setCellValue('R'.$i, g2u('��12��'));
	$objActSheet->setCellValue('S'.$i, g2u('��13��'));
	$objActSheet->setCellValue('T'.$i, g2u('�������'));
	 
	foreach($data as $key=>$val){
		$i++;
		$objActSheet->setCellValue('A'.$i, $val['dept0']);
		$objActSheet->setCellValue('B'.$i, $val['dept1']);
		$objActSheet->setCellValue('C'.$i, $val['dept2']);
		$objActSheet->setCellValue('D'.$i, $val['dept3']);
		$objActSheet->setCellValue('E'.$i, $val['dept4']);
		$objActSheet->setCellValue('F'.$i, $val['username']);
		$objActSheet->setCellValue('G'.$i, $val['question1']);
		$objActSheet->setCellValue('H'.$i, $val['question2']);
		$objActSheet->setCellValue('I'.$i, $val['question3']);
		$objActSheet->setCellValue('J'.$i, $val['question4']);
		$objActSheet->setCellValue('K'.$i, $val['question5']);
		$objActSheet->setCellValue('L'.$i, $val['question6']);
		$objActSheet->setCellValue('M'.$i, $val['question7']);
		$objActSheet->setCellValue('N'.$i, $val['question8']);
		$objActSheet->setCellValue('O'.$i, $val['question9']);
		$objActSheet->setCellValue('P'.$i, $val['question10']);
		$objActSheet->setCellValue('Q'.$i, $val['question11']);
		$objActSheet->setCellValue('R'.$i, $val['question12']);
		$objActSheet->setCellValue('S'.$i, $val['question13']);
		$objActSheet->setCellValue('T'.$i, $val['other']);
		 
	}
	//$objActSheet->getRowDimension($i)->setRowHeight(24);
	if($i > 1000)
	{
		//exit;
	}
	ob_end_clean();
	ob_start();
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
	header("Content-Type:application/force-download");
	header("Content-Type:application/vnd.ms-execl");
	header("Content-Type:application/octet-stream");
	header("Content-Type:application/download");
	header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xlsx");
	header("Content-Transfer-Encoding:binary");

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');               
	exit;
 
}

?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gbk" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="renderer" content="webkit">
<title></title>
<link rel="stylesheet" type="text/css" href="../js/easyui/themes/default/easyui.css">
	<link rel="stylesheet" type="text/css" href="../js/easyui/themes/icon.css">

<link rel="stylesheet" href="../css/pintuer.css">
<link rel="stylesheet" href="../css/admin.css">
<script src="../js/jquery.js"></script>
<script src="../js/easyui/jquery.easyui.min.js"></script>
<script src="../js/pintuer.js"></script>
<style>
#graphbox{
border:1px solid #e7e7e7;
padding:10px;
width:250px;
background-color:#f8f8f8;
margin:5px 0;
}
#graphbox h2{
color:#666666;
font-family:Arial;
font-size:18px;
font-weight:700;
}
.graph{
position:relative;
background-color:#F0EFEF;
border:1px solid #cccccc;
padding:2px;
font-size:13px;
font-weight:700;
width:210px;
}
.graph,.oran, .green, .blue, .red, .black{
position:relative;
text-align:left;
color:#ffffff;
height:18px;
line-height:18px;
font-family:Arial;
 
}
.graph2{
position:relative;
text-align:left;
float:right; 
height:18px;
line-height:18px;
font-family:Arial;
margin-right:150px;
}
 
 
.graph .green{background-color:#66CC33;}
.graph .blue{background-color:#3399CC;}
.graph .red{background-color:red;}
.graph .oran{background-color:#ff6600;}
.graph span{display:inline-block;}
.graph2 .green{background-color:#66CC33;}
.graph2 .blue{background-color:#3399CC;}
.graph2 .red{background-color:red;}
.graph2 .oran{background-color:#ff6600;}
.graph2 span{display:inline-block;}
</style>
</head>
<body>
 
  <table id="tj" class="easyui-datagrid" title="��֯��Χ���и�ҵ��ʵ����������ֲ�����ֵ�����" style="width:100%;height:auto"
			data-options="rownumbers:true,singleSelect:true,url:'basetj.php?action=gettongji',method:'get',toolbar:'#tb',pagination:true,pageSize:50">
		<thead>
			<tr>
				 
				 
				 
				<th data-options="field:'dept0',width:80,align:'center'">��ϵ</th>
				<th data-options="field:'dept1',width:80,align:'center'">��λ/����</th>
				<th data-options="field:'dept2',width:80,align:'center'">һ������</th>
				<th data-options="field:'dept3',width:80,align:'center'">��������</th>
				<th data-options="field:'dept4',width:80,align:'center'">��������</th>
				 
				<th data-options="field:'username',width:100,align:'center'">Ա��</th>
				 
				<th data-options="field:'question1',width:100,align:'left'"> ��1��</th>
				<th data-options="field:'question2',width:100,align:'left'"> ��2��</th>
				<th data-options="field:'question3',width:100,align:'left'"> ��3��</th>
				<th data-options="field:'question4',width:100,align:'left'"> ��4��</th>
				<th data-options="field:'question5',width:100,align:'left'"> ��5��</th>
				<th data-options="field:'question6',width:100,align:'left'"> ��6��</th>
				<th data-options="field:'question7',width:100,align:'left'"> ��7��</th>
				<th data-options="field:'question8',width:100,align:'left'"> ��8��</th>
				<th data-options="field:'question9',width:100,align:'left'"> ��9��</th>
				<th data-options="field:'question10',width:100,align:'left'"> ��10��</th>
				<th data-options="field:'question11',width:100,align:'left'"> ��11��</th>
				<th data-options="field:'question12',width:100,align:'left'"> ��12��</th>
				<th data-options="field:'question13',width:100,align:'left'"> ��13��</th>
				<th data-options="field:'other',width:100,align:'left'"> �������</th>
				<th data-options="field:'oastatus',width:100,align:'center',formatter:statusfornotice"> �ʼ�״̬</th>
				<th data-options="field:'status',width:100,align:'center',formatter:statusforMatter"> ״̬</th>
				 
			</tr>
		</thead>
	</table>
	<div id="tb" style="padding:5px;height:auto">
		 
		<div>
		 <input id="cc" class="easyui-combotree" data-options="url:'ajax_getDepartment.php',method:'get',label:'����:',labelPosition:'left',multiple:true" style="width:50%">
			<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="doSearch();" >��ѯ</a>
			<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="export_data();" >����</a>
			 
	</div>
 
<script type="text/javascript">
	function doSearch(){    
		$('#tj').datagrid('load',{
			itemid: $('#cc').combotree('getValues')
			 
		});
	}

	function export_data(){    
		 
		window.location.href='basetj.php?itemid='+$('#cc').combotree('getValues')+'&action=gettongji&action2=export';
	}
	function statusforMatter(value,row,index){
		if(row['question1']){
			var value = '�Ѵ���';
		}else{
			var value = '<input type="button" name="sendoanotice" onclick="sendoanotice(\''+row['userid']+'\')" value="����" />  <a href="../mark/index.php?authcode='+row['code']+'" target="_blank">����</a>';
		}
		 
		return value;
	}
	 
	function sendoanotice(touid){
		$.ajax({
			type: 'post',
			url: '',
			data: '&action=oa_notice&touid='+touid,
			beforeSend: function () {

			},
			success: function (d) {
				alert('���ͳɹ�'); 

			}
		});
		 
		
	}
	function statusfornotice(value,row,index){
		if(row['oastatus']==1){
			var value = '�ѷ���';
		}else{
			var value = 'δ����';
		}
		 
		return value;
	}
 
</script>
</body>
</html>