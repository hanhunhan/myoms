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
		$sql = "SELECT * FROM ".$DB_PREFIX."new_exam where status=1  order by queue asc ";
		$exam = $db->getAll($sql); 
		foreach($exam as $val){
			$rexam[$val['id']] = $val['question'];
			$listt[$val['queue']] = $val['id'];
		}
		
		foreach($row as $val){
			 
			 
			for($i=1;$i<14;$i++){
				//$list[$i]['tscore'] += $val['score'.$i];
				//$list[$i]['nums'] += 1;
				foreach($dept_arr as $keyy=>$vall){
					if(in_array($val['dept'],$vall)){
						$list[$i][$keyy]['score'] += $val['score'.$i];
						$list[$i][$keyy]['nums'] += 1;
						//$list[$i]['score_dept'] += $val['score'.$i];
						//$list[$i]['nums_dept'] += 1;
						$list[$i]['score_dept'][$val['uid']]  = $val['score'.$i];
					}
				}

			}
			$allnum++;

		}
		
		for($i=1;$i<14;$i++){
			//$list[$i]['tscore'] += $val['score'.$i];
			//$list[$i]['nums'] += 1;
			$sort = array();
			foreach($dept_arr as $keyy=>$vall){
				$list[$i][$keyy]['dept'] = g2u($dept_list[$keyy]['DEPT_NAME']);
				$list[$i][$keyy]['avg'] = $list[$i][$keyy]['nums'] ? round( $list[$i][$keyy]['score']/$list[$i][$keyy]['nums'],2):0;
				$sort[$keyy] = $list[$i][$keyy]['avg'];
			}
			arsort($sort);
			foreach($sort as $key=>$val){
				$new_arr[$i][] = $list[$i][$key];
				 
			}

		}
		$dept_arr_nums = count($dept_arr);
		for($i=0;$i<$dept_arr_nums;$i++){
			$temp['queue']= $i+1;
			//for($ii=0;$ii<14;$ii++){
			foreach($listt as $kk=>$ii){
				$temp['question'.$kk] = $new_arr[$ii][$i]['dept'].'('.$new_arr[$ii][$i]['avg'].')';
				$temp['question_dept'.$kk] =  $new_arr[$ii][$i]['dept'] ;
				$temp['question_val'.$kk] =  $new_arr[$ii][$i]['avg'] ;
				
			}
			$result['rows'][] = $temp;
		}
		
		 
		$result['total'] = $dept_arr_nums;
		$footer['queue'] = g2u('TOTAL');
		 
		//for($i=1;$i<14;$i++){
		foreach($listt as $kk=>$i){
			//$footer['question'.$i] = $list[$i]['nums_dept']?round( $list[$i]['score_dept']/$list[$i]['nums_dept'],2):0;
			//$list[$i]['score_dept'][$val['USER_ID']]
			$footer['question'.$kk] = $footer['question_val'.$kk] = $list[$i]['score_dept'] ?round(array_sum($list[$i]['score_dept'])/count($list[$i]['score_dept']),2):0;

		} 
		 
		$footer['score'] = round($allscore/$allnum,2);
		 
		$result['footer'][] = $footer;
	 

		
	}else{
		$result['rows'] =array();
		$result['total']=0;
	}
	if($_REQUEST['action2']=='export'){
		$footer['queue'] = 'TOTAL';
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
	$Exceltitle = '按业务实体单题分值由高到低排名';
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
	$objActSheet->getStyle('A'.$i.':AK'.$i)->getFont()->setName('Candara' );
	$objActSheet->getStyle('A'.$i.':AK'.$i)->getFont()->setSize(12);
	$objActSheet->getStyle('A'.$i.':AK'.$i)->getFont()->setBold(true);
	
	$objActSheet->setCellValue('A'.$i, g2u('排名'));
	$words = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI');
	for($ii=1;$ii<27;$ii=$ii+2){
		$keyt1 = $ii;
		$keyt2 = $ii+1;
		$num = ($ii+1)/2;
		$objActSheet->setCellValue($words[$keyt1].$i, g2u('第'.$num.'题'));
		$objActSheet->setCellValue($words[$keyt2].$i, g2u('第'.$num.'题'));
		$objActSheet->mergeCells( $words[$keyt1].$i.':'.$words[$keyt2].$i);
	}
	$i = 2;
	$objActSheet->setCellValue('A'.$i, g2u('排名'));
	for($ii=1;$ii<27;$ii=$ii+2){
		$keyt1 = $ii;
		$keyt2 = $ii+1;
		$num = ($ii+1)/2;
		 
		$objActSheet->setCellValue($words[$keyt1].$i, g2u('部门'));
		$objActSheet->setCellValue($words[$keyt2].$i, g2u('分值'));
		 
	}
 
	/*$i = 2;
	$objActSheet->setCellValue('A'.$i, g2u('排名'));

	$objActSheet->setCellValue('B'.$i, g2u('部门'));
	$objActSheet->setCellValue('C'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'B2:C2');

	$objActSheet->setCellValue('D'.$i, g2u('部门'));
	$objActSheet->setCellValue('E'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'D2:E2');

	$objActSheet->setCellValue('F'.$i, g2u('部门'));
	$objActSheet->setCellValue('H'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'F2:H2');

	$objActSheet->setCellValue('I'.$i, g2u('部门'));
	$objActSheet->setCellValue('J'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'I2:J2');

	$objActSheet->setCellValue('K'.$i, g2u('部门'));
	$objActSheet->setCellValue('L'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'K2:L2');
	$objActSheet->setCellValue('M'.$i, g2u('部门'));
	$objActSheet->setCellValue('N'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'M2:N2');
	$objActSheet->setCellValue('O'.$i, g2u('部门'));
	$objActSheet->setCellValue('P'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'O2:P2');
	$objActSheet->setCellValue('Q'.$i, g2u('部门'));
	$objActSheet->setCellValue('R'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'Q2:R2');
	$objActSheet->setCellValue('S'.$i, g2u('部门'));
	$objActSheet->setCellValue('T'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'S2:T2');
	$objActSheet->setCellValue('U'.$i, g2u('部门'));
	$objActSheet->setCellValue('V'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'U2:V2');
	$objActSheet->setCellValue('W'.$i, g2u('部门'));
	$objActSheet->setCellValue('X'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'W2:X2');
	$objActSheet->setCellValue('Y'.$i, g2u('部门'));
	$objActSheet->setCellValue('Z'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'Y2:Z2');
	$objActSheet->setCellValue('AA'.$i, g2u('部门'));
	$objActSheet->setCellValue('AB'.$i, g2u('分值'));
	//$objActSheet->mergeCells( 'AA2:AB2');
	 */
foreach($data as $key=>$val){
		$i++;
		if($key+1==count($data)){
			$objActSheet->setCellValue('A'.$i, 'TOTAL');
		}else{
			$objActSheet->setCellValue('A'.$i, $key+1);
		}

		for($ii=1;$ii<27;$ii=$ii+2){
			$keyt1 = $ii;
			$keyt2 = $ii+1;
			$num = ($ii+1)/2;
			 
			//$objActSheet->setCellValue($words[$keyt1].$i, g2u('部门'));
			//$objActSheet->setCellValue($words[$keyt2].$i, g2u('分值'));
			$objActSheet->setCellValue($words[$keyt1].$i, $val['question_dept'.$num]);
			$objActSheet->setCellValue($words[$keyt2].$i, $val['question_val'.$num]);
		 
		}
		/*if($key+1==count($data)){
			$objActSheet->setCellValue('A'.$i, 'TOTAL');
		}else{
			$objActSheet->setCellValue('A'.$i, $key+1);
		}
		$objActSheet->setCellValue('B'.$i, $val['question1']);
		$objActSheet->setCellValue('C'.$i, $val['question_val1']);

		$objActSheet->setCellValue('D'.$i, $val['question2']);
		$objActSheet->setCellValue('E'.$i, $val['question_val2']);

		$objActSheet->setCellValue('F'.$i, $val['question3']);
		$objActSheet->setCellValue('H'.$i, $val['question_val3']);

		$objActSheet->setCellValue('I'.$i, $val['question4']);
		$objActSheet->setCellValue('J'.$i, $val['question_val4']);

		$objActSheet->setCellValue('K'.$i, $val['question5']);
		$objActSheet->setCellValue('L'.$i, $val['question_val5']);

		$objActSheet->setCellValue('M'.$i, $val['question6']);
		$objActSheet->setCellValue('N'.$i, $val['question_val6']);

		$objActSheet->setCellValue('O'.$i, $val['question7']);
		$objActSheet->setCellValue('P'.$i, $val['question_val7']);

		$objActSheet->setCellValue('Q'.$i, $val['question8']);
		$objActSheet->setCellValue('R'.$i, $val['question_val8']);

		$objActSheet->setCellValue('S'.$i, $val['question9']);
		$objActSheet->setCellValue('T'.$i, $val['question_val9']);

		$objActSheet->setCellValue('U'.$i, $val['question10']);
		$objActSheet->setCellValue('V'.$i, $val['question_val10']);

		$objActSheet->setCellValue('W'.$i, $val['question11']);
		$objActSheet->setCellValue('X'.$i, $val['question_val11']);
		
		$objActSheet->setCellValue('Y'.$i, $val['question12']);
		$objActSheet->setCellValue('Z'.$i, $val['question_val12']);

		$objActSheet->setCellValue('AA'.$i, $val['question13']);
		$objActSheet->setCellValue('AB'.$i, $val['question_val13']);
		*/
		 
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
 
  <table id="tj" class="easyui-datagrid" title="按业务实体单题分值由高到低排名" style="width:100%;height:auto"
			data-options="rownumbers:true,singleSelect:true,url:'tongji4.php?action=gettongji',method:'get',toolbar:'#tb'" showFooter="true">
		<thead>
			<tr>
				<th data-options="field:'queue',width:100">排名</th> 
				
				 
				
				 
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
		 
		window.location.href='tongji4.php?itemid='+$('#cc').combotree('getValues')+'&action=gettongji&action2=export';
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