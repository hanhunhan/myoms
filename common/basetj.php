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
		$sendtime = date("Y年m月d日 H:i:s");
		$subject = "请参加三六五网的组织氛围在线测评";
		$code= $touid.'$'.$touser;
		$authcode = urlencode(get_authcode($code,'ENCODE'));
		$urll = "http://zt.house365.com/njzt/2016/11/21/zzfw/mark/index.php?authcode=$authcode";
		$content =  <<< eod2
 
$touser :<br>您好！<br>
三六五网邀请您参加组织氛围在线测评的时间已接近尾声。<br> 
 请您点击以下地址进入测评平台：<a href='$urll' target='_blank'> $urll</a> <br> 
 
 这是您的专属测评地址，与您的信息绑定，请勿转发给他人。调研团队承诺，将严格保证本次反馈的匿名性和数据准确性。<br> 
 如果无法打开上面的链接，请进入组织氛围OA通栏直接作答。<br> 
 本次测试邀请于<font color="#ff0000"> $startime</font> 生效，于 <font color="#ff0000"> $endtime</font> 失效。请抓紧时间完成测评。<br> 
 三六五网 <br> 
$sendtime <br>
 
<br> <br> <br> <br> 
 声明(Declaration):<br> 
 本邮件含有保密信息，仅限于收件人所用。禁止任何人未经发件人许可以任何形式（包括但不限于部分地泄露、复制或散发）不当地使用本邮件中的信息。如果您错收了本邮件，请您立即电话或邮件通知发件人并删除本邮件，谢谢！<br> 
 This email contains confidential information, which is intended only for the receiver. Any use of the information contained herein in any way (including, but not limited to, total or partial disclosure, reproduction, or dissemination) by persons other than the intended recipient(s) is prohibited. If you receive this email in error, please notify the sender by phone or email immediately and delete it. Thanks!
eod2;
		oa_notice($_REQUEST['touid'], $_REQUEST['touid'], $subject, $content);exit;
	}
}



function export_data($data){
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);
	$objActSheet = $objPHPExcel->getActiveSheet();
	$Exceltitle = '组织氛围调研各业务实体各题样本分布及分值情况表';
	$objActSheet->setTitle(g2u($Exceltitle));
	$objActSheet->getDefaultRowDimension()->setRowHeight(16);//默认行宽
	$objActSheet->getDefaultColumnDimension()->setWidth(16);//默认列宽
	$objActSheet->getDefaultStyle()->getFont()->setName(g2u('宋体'));
	$objActSheet->getDefaultStyle()->getFont()->setSize(10);
	$objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
	$objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objActSheet->getRowDimension('1')->setRowHeight(40);
	$objActSheet->getRowDimension('2')->setRowHeight(26);
	
	$i = 1;
	$objActSheet->getStyle('A'.$i.':T'.$i)->getFont()->setName('Candara' );
	$objActSheet->getStyle('A'.$i.':T'.$i)->getFont()->setSize(12);
	$objActSheet->getStyle('A'.$i.':T'.$i)->getFont()->setBold(true);
	 
	$objActSheet->setCellValue('A'.$i, g2u('体系'));
	$objActSheet->setCellValue('B'.$i, g2u('单位/部门'));
	$objActSheet->setCellValue('C'.$i, g2u('一级部门'));
	$objActSheet->setCellValue('D'.$i, g2u('二级部门'));
	$objActSheet->setCellValue('E'.$i, g2u('三级部门'));
	$objActSheet->setCellValue('F'.$i, g2u('员工'));
	$objActSheet->setCellValue('G'.$i, g2u('第1题'));
	$objActSheet->setCellValue('H'.$i, g2u('第2题'));
	$objActSheet->setCellValue('I'.$i, g2u('第3题'));
	$objActSheet->setCellValue('J'.$i, g2u('第4题'));
	$objActSheet->setCellValue('K'.$i, g2u('第5题'));
	$objActSheet->setCellValue('L'.$i, g2u('第6题'));
	$objActSheet->setCellValue('M'.$i, g2u('第7题'));
	$objActSheet->setCellValue('N'.$i, g2u('第8题'));
	$objActSheet->setCellValue('O'.$i, g2u('第9题'));
	$objActSheet->setCellValue('P'.$i, g2u('第10题'));
	$objActSheet->setCellValue('Q'.$i, g2u('第11题'));
	$objActSheet->setCellValue('R'.$i, g2u('第12题'));
	$objActSheet->setCellValue('S'.$i, g2u('第13题'));
	$objActSheet->setCellValue('T'.$i, g2u('其他意见'));
	 
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
 
  <table id="tj" class="easyui-datagrid" title="组织氛围调研各业务实体各题样本分布及分值情况表" style="width:100%;height:auto"
			data-options="rownumbers:true,singleSelect:true,url:'basetj.php?action=gettongji',method:'get',toolbar:'#tb',pagination:true,pageSize:50">
		<thead>
			<tr>
				 
				 
				 
				<th data-options="field:'dept0',width:80,align:'center'">体系</th>
				<th data-options="field:'dept1',width:80,align:'center'">单位/部门</th>
				<th data-options="field:'dept2',width:80,align:'center'">一级部门</th>
				<th data-options="field:'dept3',width:80,align:'center'">二级部门</th>
				<th data-options="field:'dept4',width:80,align:'center'">三级部门</th>
				 
				<th data-options="field:'username',width:100,align:'center'">员工</th>
				 
				<th data-options="field:'question1',width:100,align:'left'"> 第1题</th>
				<th data-options="field:'question2',width:100,align:'left'"> 第2题</th>
				<th data-options="field:'question3',width:100,align:'left'"> 第3题</th>
				<th data-options="field:'question4',width:100,align:'left'"> 第4题</th>
				<th data-options="field:'question5',width:100,align:'left'"> 第5题</th>
				<th data-options="field:'question6',width:100,align:'left'"> 第6题</th>
				<th data-options="field:'question7',width:100,align:'left'"> 第7题</th>
				<th data-options="field:'question8',width:100,align:'left'"> 第8题</th>
				<th data-options="field:'question9',width:100,align:'left'"> 第9题</th>
				<th data-options="field:'question10',width:100,align:'left'"> 第10题</th>
				<th data-options="field:'question11',width:100,align:'left'"> 第11题</th>
				<th data-options="field:'question12',width:100,align:'left'"> 第12题</th>
				<th data-options="field:'question13',width:100,align:'left'"> 第13题</th>
				<th data-options="field:'other',width:100,align:'left'"> 其他意见</th>
				<th data-options="field:'oastatus',width:100,align:'center',formatter:statusfornotice"> 邮件状态</th>
				<th data-options="field:'status',width:100,align:'center',formatter:statusforMatter"> 状态</th>
				 
			</tr>
		</thead>
	</table>
	<div id="tb" style="padding:5px;height:auto">
		 
		<div>
		 <input id="cc" class="easyui-combotree" data-options="url:'ajax_getDepartment.php',method:'get',label:'部门:',labelPosition:'left',multiple:true" style="width:50%">
			<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="doSearch();" >查询</a>
			<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="export_data();" >导出</a>
			 
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
			var value = '已答题';
		}else{
			var value = '<input type="button" name="sendoanotice" onclick="sendoanotice(\''+row['userid']+'\')" value="发送" />  <a href="../mark/index.php?authcode='+row['code']+'" target="_blank">测试</a>';
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
				alert('发送成功'); 

			}
		});
		 
		
	}
	function statusfornotice(value,row,index){
		if(row['oastatus']==1){
			var value = '已发送';
		}else{
			var value = '未发送';
		}
		 
		return value;
	}
 
</script>
</body>
</html>