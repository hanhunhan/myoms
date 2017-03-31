<?php
set_time_limit(1800);
include_once("../common/mysql.class.php");
include_once("../common/function.php");
include_once("../common/phpExcel/PHPExcel.php");
include_once("../common/check.php");
if($_REQUEST['action']=='gettabs'){
	if($_REQUEST['itemid']){
		$result = getTab($db,$_REQUEST['itemid']) ;
		echo json_encode($result); exit;
	}

}else
if($_REQUEST['action']=='gettongji'){
	if($_REQUEST['itemid']){
		$itemid = implode(',',getChildrens($db,$_REQUEST['itemid']) );
		$sql = "SELECT * FROM ".$DB_PREFIX."new_user_result where dept in($itemid) ";
		$result = getTab($db,$_REQUEST['itemid']);
		if(!is_array($_REQUEST['itemid'])) $_REQUEST['itemid'] = explode(',',$_REQUEST['itemid']);
		foreach($_REQUEST['itemid'] as $val){
			//$itemids = implode(',',$_REQUEST['itemid']);
			$dept_arr[$val] =  getChildrens($db,$val) ;
		}


	}//else  $sql = "SELECT * FROM new_user_result ";
	//var_dump($dept_arr);
	$row = $db->getAll($sql); 
	$total = count($row);
	$result['total'] = $total;
	if($row){
		$sql = "SELECT * FROM ".$DB_PREFIX."new_exam where status=1 order by queue asc ";
		$exam = $db->getAll($sql); 
		foreach($exam as $val){
			$rexam[$val['id']] = $val['question'];
			$list[$val['queue']] = $val['id'];
		}
		
		foreach($row as $val){
			//$quartile3 = array();
			for($i=1;$i<14;$i++){
				$res[$i]['total'] += $val['score'.$i];
				$res[$i]['counts'] += 1;
			  //var_dump($dept_arr);
				foreach($dept_arr as $keyy=>$vall ){   
					if(in_array($val['dept'],$vall)){
						
						$res[$i]['dept_total_'.$keyy] += $val['score'.$i];
						$res[$i]['dept_counts_'.$keyy] += 1;
						$totals['dept_'.$keyy] += $val['score'.$i];
						$totals['dept_counts_'.$keyy] += 1;
						$quartile_avg['dept_'.$keyy][] = $val['score'.$i];
					}
				}
				$res[$i]['question'] = g2u($rexam[$i]);
				$quartile3[$i][] = $val['score'.$i];

				$quartile3_all[] = $val['score'.$i];
				 
				 
			}


		}
		
		for($i=1;$i<14;$i++){
			foreach($dept_arr as $keyy=>$vall ){ 
				$res[$i]['dept_'.$keyy] = $res[$i]['dept_counts_'.$keyy]?round($res[$i]['dept_total_'.$keyy]/$res[$i]['dept_counts_'.$keyy],2):0;
			}
			$res[$i]['quartile'] = Quartile($quartile3[$i],$res[$i]['counts']);
				 
			if($res[$i]['counts'])$res[$i]['average'] =  round($res[$i]['total']/$res[$i]['counts'],2);
			//$result['rows'][] = $res[$i];

				 
		}
		foreach($list as $key=>$val){
			$result['rows'][] = $res[$val];
		}
		$result['total'] = 13;
		$footer['question'] = g2u('均分');
		$footer2['question'] = g2u('75分位');
		foreach($dept_arr as $keyy=>$vall ){
			$footer['dept_'.$keyy]  = $totals['dept_counts_'.$keyy]? round($totals['dept_'.$keyy]/$totals['dept_counts_'.$keyy],2) : 0;
			$allscore += $totals['dept_'.$keyy];
			$allnum += $totals['dept_counts_'.$keyy];
			$footer2['dept_'.$keyy] = Quartile($quartile_avg['dept_'.$keyy],$totals['dept_counts_'.$keyy]);

		}
		$footer['average'] =  $quartile3_all ? round(array_sum($quartile3_all)/count($quartile3_all),2):0; //$allnum?round($allscore/$allnum,2):0;

		$footer2['quartile'] = Quartile($quartile3_all,count($quartile3_all));
		$result['footer'][] = $footer; 
		$result['footer'][] = $footer2;

		
	}else{
		$result['rows'] =array();
		$result['total']=0;
	}
	if($_REQUEST['action2']=='export'){
		$tab = getTab($db,$_REQUEST['itemid']) ;
		$result['rows'][] = $footer;
		$result['rows'][] = $footer2;
		export_data($result['rows'],$tab); //exit;
	}else {
		echo json_encode($result); exit;
	}
}


function export_data($data,$tab){
	$words = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI');
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);
	$objActSheet = $objPHPExcel->getActiveSheet();
	$Exceltitle = '组织氛围调研各业务实体间对比情况分析表';
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
	 
	//$objActSheet->setCellValue('A'.$i, g2u('编号'));
	//$objActSheet->setCellValue('B'.$i, g2u('问题'));
	foreach($tab as $key=>$val){
		$objActSheet->setCellValue($words[$key].$i, $val['title']);

	}

	 
	
	//$objActSheet->setCellValue('H'.$i, g2u('平均分值'));
	//$objActSheet->setCellValue('I'.$i, g2u('75分位'));
 
	foreach($data as $keyy=>$vall){
		$i++;
		foreach($tab as $key=>$val){
			$objActSheet->setCellValue($words[$key].$i, $vall[$val['field']]);

		} 
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
 
  <table id="tj" class="easyui-datagrid" title="组织氛围调研各业务实体间对比情况分析表" style="width:100%;height:auto"
			data-options="rownumbers:true,singleSelect:true,url:'tongji2.php?action=gettongji',method:'get',toolbar:'#tb'" showFooter="true">
		<thead>
			<tr>
				 
				<th data-options="field:'question',width:280">问题</th>
				<th data-options="field:'listprice',width:150,align:'center'"> 业务实体</th>
				 
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
	function changetab(data){
		$('#tj').datagrid({
			columns:[data ]
		});
	}
	function export_data(){    
		 
		window.location.href='tongji2.php?itemid='+$('#cc').combotree('getValues')+'&action=gettongji&action2=export';
	}
	function doSearch(){
		$.ajax({
			type: 'post',
			url: '',
			data: '&action=gettabs&itemid='+$('#cc').combotree('getValues'),
			beforeSend: function () {

			},
			success: function (d) {
				 var data = eval("(" + d + ")");
				changetab(data); 
				$('#tj').datagrid('load',{
					itemid: $('#cc').combotree('getValues')
					 
				});

			}
		});
		 
		
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