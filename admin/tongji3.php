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
		if(!is_array($_REQUEST['itemid'])) $_REQUEST['itemid'] = explode(',',$_REQUEST['itemid']);
		foreach($_REQUEST['itemid'] as $val){
			$itemids = implode(',',$_REQUEST['itemid']);
			$dept_arr[$val] =  getChildrens($db,$val) ;
		}
		$dept_list = getTab_info($db,$_REQUEST['itemid']);  


	}//else //$sql = "SELECT * FROM new_user_result ";
	//var_dump($dept_arr);
	$row = $db->getAll($sql); 
	$total = count($row);
	$result['total'] = $total;
	$list = array();
	if($row){
		/*$sql = "SELECT * FROM new_exam where status=1 ";
		$exam = $db->getAll($sql); 
		foreach($exam as $val){
			$rexam[$val['id']] = $val['question'];
		}
		*/
		foreach($row as $val){
			 
			foreach($dept_arr as $keyy=>$vall ){    
				if(in_array($val['dept'],$vall)){
					$list[$keyy]['dept_exams_counts'] += 1;
					for($i=1;$i<14;$i++){
						$list[$keyy]['dept_total_score'] += $val['score'.$i];
						 
						//$quartile_avg['dept_'.$keyy][] = $val['score'.$i];
						$list[$keyy]['dept_by'][] = $val['score'.$i];
						
					}
					//$list[$keyy]['dept_by'][] = $list[$keyy]['dept_total_score'];
					
				}
			}
			for($i=1;$i<14;$i++){
				$all_score_arr[] =  $val['score'.$i];
			}


		}
		
		 
		$result['total'] = count($dept_arr);
		$footer['queue'] = g2u('TOTAL');
		$all_exams = array(); 
		foreach($dept_arr as $keyy=>$vall ){ 
			$list[$keyy]['average'] +=$list[$keyy]['dept_exams_counts'] ? round( $list[$keyy]['dept_total_score']/($list[$keyy]['dept_exams_counts']*13) ,2):0;
			$list[$keyy]['quartile'] = Quartile($list[$keyy]['dept_by'],$list[$keyy]['dept_exams_counts']*13);
			$list[$keyy]['dept'] =  g2u($dept_list[$keyy]['DEPT_NAME']);

			 
			$allscore += $list[$keyy]['dept_total_score'];
			$allnum +=$list[$keyy]['dept_exams_counts'];
			if(is_array($list[$keyy]['dept_by']))$all_exams = array_merge($all_exams,$list[$keyy]['dept_by']);
			 
			//$result['rows'][] = $list[$keyy];
			$sort[$keyy] = $list[$keyy]['average'];

		}
		arsort($sort);
		$i=1;
		foreach($sort as $keyy=>$vall){
			$list[$keyy]['queue'] = $i++;
			$result['rows'][] = $list[$keyy];

		}
		//$footer['average'] = round($allscore/($allnum*13),2);
		$footer['average'] = $all_score_arr ?round(array_sum($all_score_arr)/count($all_score_arr),2):0;
		$footer['quartile'] = Quartile($all_exams,$allnum*13);
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
	 
	$objActSheet->setCellValue('A'.$i, g2u('排名'));
	$objActSheet->setCellValue('B'.$i, g2u('业务实体'));
	$objActSheet->setCellValue('C'.$i, g2u('平均分值'));
	$objActSheet->setCellValue('D'.$i, g2u('75分位'));
	 
foreach($data as $key=>$val){
		$i++;
		$objActSheet->setCellValue('A'.$i, $key+1);
		$objActSheet->setCellValue('B'.$i, $val['dept']);
		$objActSheet->setCellValue('C'.$i, $val['average']);
		$objActSheet->setCellValue('D'.$i, $val['quartile']);
		 
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
 
  <table id="tj" class="easyui-datagrid" title="按业务实体分值由高到低排列" style="width:100%;height:auto"
			data-options="rownumbers:true,singleSelect:true,url:'tongji3.php?action=gettongji',method:'get',toolbar:'#tb'" showFooter="true">
		<thead>
			<tr>
				 
				<th data-options="field:'queue',width:100">排名</th>
				<th data-options="field:'dept',width:150,align:'center'"> 业务实体</th>
				 
				<th data-options="field:'average',width:100,align:'center'"> 平均分值</th>
				<th data-options="field:'quartile',width:100,align:'center'"> 75分位</th>
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
		 
		window.location.href='tongji3.php?itemid='+$('#cc').combotree('getValues')+'&action=gettongji&action2=export';
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

 
</script>
</body>
</html>