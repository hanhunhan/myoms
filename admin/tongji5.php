<?php
set_time_limit(1800);
include_once("../common/mysql.class.php");
include_once("../common/function.php");
include_once("../common/phpExcel/PHPExcel.php");
include_once("../common/check.php");
 
if($_REQUEST['action']=='gettongji'){
	if($_REQUEST['itemid']){
		$itemid = implode(',',getChildrens($db,$_REQUEST['itemid']) );
		$sql = "SELECT * FROM ".$DB_PREFIX."new_user_result where dept in($itemid) ";
		//$result = getTab($db,$_REQUEST['itemid']);
		//foreach($_REQUEST['itemid'] as $val){
			//$itemids = implode(',',$_REQUEST['itemid']);
			//$dept_arr[$val] =  getChildrens($db,$val) ;
		//}
		if(!is_array($_REQUEST['itemid'])) $_REQUEST['itemid'] = explode(',',$_REQUEST['itemid']);
		$dept_list = getTab_info($db,$_REQUEST['itemid']);  
		foreach($dept_list as $val){
			$dept_name[] = $val['DEPT_NAME'];
		}
		$dept_name = implode(',',$dept_name);


	}//else //$sql = "SELECT * FROM new_user_result ";
	//var_dump($dept_arr);
	$row = $db->getAll($sql); 
	$total = count($row);
	$result['total'] = $total;
	$list = array();
	$allnum = 0;
	if($row){
		$sql = "SELECT * FROM ".$DB_PREFIX."new_exam where status=1 ";
		$exam = $db->getAll($sql); 
		foreach($exam as $val){
			$rexam[$val['id']] = $val['question'];
		}
		
		foreach($row as $val){
			 
			 
			for($i=1;$i<14;$i++){
				$list[$i]['tscore'] += $val['score'.$i];
				$list[$i]['nums'] += 1;

			}
			$allnum++;

		}
		
		for($i=1;$i<14;$i++){
			$list[$i]['dept'] = g2u($dept_name);
			$list[$i]['question'] = g2u($rexam[$i]);
			$list[$i]['score'] = $list[$i]['nums'] ? round($list[$i]['tscore']/$list[$i]['nums'],2):0;
			$allscore += $list[$i]['tscore'];
			$sort[$i] = $list[$i]['score'];

		}
		
		 
		$result['total'] = 13;
		$footer['dept'] = g2u('TOTAL');
		 
		 
		arsort($sort);//var_dump($sort);
		$i=1;
		foreach($sort as $keyy=>$vall){
			$list[$keyy]['queue'] = $i++;
			$result['rows'][] = $list[$keyy];

		}
		$footer['score'] = round($allscore/($allnum*13),2);
		 
		$result['footer'][] = $footer; 
	 

		
	}else{
		$result['rows'] =array();
		$result['total']=0;
	}
	if($_REQUEST['action2']=='export'){
		$footer['dept'] = 'TOTAL';
		$result['rows'][] = $footer; 

		export_data($result['rows']); //exit;
	}else {
		echo json_encode($result); exit;
	}
}


function export_data($data){
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);
	$objActSheet = $objPHPExcel->getActiveSheet();
	$Exceltitle = '按业务实体分值由高到低排列';
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
	$objActSheet->getStyle('A'.$i.':K'.$i)->getFont()->setName('Candara' );
	$objActSheet->getStyle('A'.$i.':K'.$i)->getFont()->setSize(12);
	$objActSheet->getStyle('A'.$i.':K'.$i)->getFont()->setBold(true);
	 
	$objActSheet->setCellValue('A'.$i, g2u('业务实体'));
	$objActSheet->setCellValue('B'.$i, g2u('排名'));
	$objActSheet->setCellValue('C'.$i, g2u('题目'));
	$objActSheet->setCellValue('D'.$i, g2u('分值'));
	 
foreach($data as $key=>$val){
		$i++;
		$objActSheet->setCellValue('A'.$i, $val['dept'] );
		$objActSheet->setCellValue('B'.$i, $val['queue']);
		$objActSheet->setCellValue('C'.$i, $val['question'] );
		$objActSheet->setCellValue('D'.$i, $val['score']);
	 
	}
	//$objActSheet->getRowDimension($i)->setRowHeight(24);
	if($i > 1000)
	{
		exit;
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
 
</style>
</head>
<body>
 
  <table id="tj" class="easyui-datagrid" title="按业务实体内单题分值由高到低排名" style="width:100%;height:auto"
			data-options="rownumbers:true,singleSelect:true,url:'tongji5.php?action=gettongji',method:'get',toolbar:'#tb'" showFooter="true">
		<thead>
			<tr>
				 
				
				<th data-options="field:'dept',width:300,align:'center',formatter:formatCellTooltip"> 业务实体</th>
				<th data-options="field:'queue',width:100">排名</th>
				 
				<th data-options="field:'question',width:300,align:'left'"> 题目</th>
				<th data-options="field:'score',width:100,align:'center'"> 分值</th>
			</tr>
		</thead>
	</table>
	<div id="tb" style="padding:5px;height:auto">
		 
		<div>
		 <input id="cc" class="easyui-combotree" data-options="url:'ajax_getDepartment.php',method:'get',label:'部门:',labelPosition:'left',multiple:true,cascadeCheck:false" style="width:50%">
			<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="doSearch();" >统计</a>
			<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="export_data();" >导出</a>
			 
		</div>
	</div>
 
<script type="text/javascript">
	 
	function doSearch(){
		$('#tj').datagrid('load',{
			itemid: $('#cc').combotree('getValues')
			 
		});
		 
		
	}
	function export_data(){    
		 
		window.location.href='tongji5.php?itemid='+$('#cc').combotree('getValues')+'&action=gettongji&action2=export';
	}
	function imgforMatter(value,row,index){
		var oran = row['1-2']?row['1-2']/row['counts']*100:0;
		var green = row['3']?row['3']/row['counts']*100:0;
		var blue = row['4']?row['4']/row['counts']*100:0;
		var red = row['5']?row['5']/row['counts']*100:0;
		var value = '<div class="graph">';
		if(oran) value += '<span class="oran" style="width:'+oran+'%;"> '+oran.toFixed(2)+'%</span>';
		if(green) value += '<span class="green" style="width:'+green+'%;">'+green.toFixed(2)+'%</span>';
		if(blue) value += '<span class="blue" style="width:'+blue+'%;">'+blue.toFixed(2)+'%</span>';
		if(red) value += '<span class="red" style="width:'+red+'%;">'+red.toFixed(2)+'%</span>';
		value += '</div> ';
		return value;
	}
	function formatCellTooltip(value){  
		return "<span title='" + value + "'>" + value + "</span>";  
	}  

 
</script>
</body>
</html>