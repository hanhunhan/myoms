<?php
set_time_limit(1800);
include_once("../common/mysql.class.php");
include_once("../common/function.php");
include_once("../common/phpExcel/PHPExcel.php");
include_once("../common/check.php");
if($_REQUEST['action']=='gettongji'){
	$sql = "SELECT * FROM ".$DB_PREFIX."new_user_result ";
	$row = $db->getAll($sql); 
	$ttotal = count($row);
	foreach($row as $val){
		for($i=1;$i<14;$i++){
			$score_total +=  $val['score'.$i];
			$score_total_arr[$i] += $val['score'.$i];
		}
	}
	$average_total = round($score_total/($ttotal*13),2);
	if($_REQUEST['itemid']){
		$itemid = implode(',',getChildrens($db,$_REQUEST['itemid']) );
		$sql = "SELECT * FROM ".$DB_PREFIX."new_user_result where dept in($itemid) ";
	}else $sql = "SELECT * FROM ".$DB_PREFIX."new_user_result ";
		$row = $db->getAll($sql); 
		$total = count($row);
		$result['total'] = $total;
		if($row){
		$sql = "SELECT * FROM ".$DB_PREFIX."new_exam where status=1 order by queue asc";
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
				$res[$i]['1-2'] += ($val['score'.$i]==1 || $val['score'.$i]==2 )?1:0;
				$total_arr['1-2'] += ($val['score'.$i]==1 || $val['score'.$i]==2 )?1:0;
				$res[$i]['3'] += ($val['score'.$i]==3 )?1:0;
				$total_arr['3'] += ($val['score'.$i]==3  )?1:0;
				$res[$i]['4'] += ($val['score'.$i]==4 )?1:0;
				$total_arr['4'] += ($val['score'.$i]==4  )?1:0;
				$res[$i]['5'] += ($val['score'.$i]==5 )?1:0;
				$total_arr['5'] += ($val['score'.$i]==5  )?1:0;
				$res[$i]['question'] = g2u($rexam[$i]);
				$quartile3[$i][] = $val['score'.$i];
				 
			}


		}
		
		for($i=1;$i<14;$i++){
			$res[$i]['quartile'] = Quartile($quartile3[$i],$res[$i]['counts']);
				 
			if($res[$i]['counts'])$res[$i]['average'] =  round($res[$i]['total']/$res[$i]['counts'],2);
			//$result['rows'][] = $res[$i];
				 
		}

		foreach($row as $val){
			 
			for($i=1;$i<14;$i++){
			 
				$res[$i]['variance'] += pow(($val['score'.$i] - $res[$i]['quartile']),2);
				$res[$i]['variance_num'] +=1;


				 
			}



		}
		//total 
		$quartile_all  = array();
		$total_arr['question'] = 'TOTAL';
		for($i=1;$i<14;$i++){
			$res[$i]['variance'] = round(sqrt($res[$i]['variance']/$res[$i]['variance_num']),2);
			 //$result['rows'][] = $res[$i];
			 $total_arr['counts'] += $res[$i]['counts'];
			// $total_arr['1-2'] += ($i==1 ||  $i==2 )?$res[$i]['counts']:0;
			// $total_arr['3'] += ($i==3 )? $res[$i]['counts']:0;
			// $total_arr['4'] += ($i==4 )?$res[$i]['counts'] :0;
			// $total_arr['5'] += ($i==5 )?$res[$i]['counts'] :0;
			 $total_arr['total'] += $res[$i]['total'];
			 $quartile_all = array_merge( $quartile_all,$quartile3[$i] );
			 $res['variance_total'] += $res[$i]['variance'] ;
			 $res[$i]['average_total'] = round($score_total_arr[$i]/$ttotal,2);
				 
		}
		foreach($list as $key=>$val){
			$result['rows'][] = $res[$val];
		}
		$total_arr['average'] = round($total_arr['total']/$total_arr['counts'],2);
		$total_arr['quartile'] =  Quartile($quartile_all,$total_arr['counts']);
		$total_arr['average_total'] = $average_total;
		
		/*foreach($row as $val){
			 
			for($i=1;$i<14;$i++){
			 
				$total_arr['variance'] += pow(($val['score'.$i] - $total_arr['average'] ),2);
				 
 
			}



		}*/
		//$total_arr['variance'] = round(sqrt($total_arr['variance']/($total*13)),2);
		$total_arr['variance'] = round($res['variance_total'] /13,2);

		$result['rows'][] = $total_arr;
		$result['total'] = 13;
		 
		
	}else{
		$result['rows'] =array();
		$result['total']=0;
	}
	if($_REQUEST['action2']=='export'){
		export_data($result['rows']); //exit;
	}else {
		echo json_encode($result); exit;
	}
}

function export_data($data){
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);
	$objActSheet = $objPHPExcel->getActiveSheet();
	$Exceltitle = '分值统计';
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
	 
	$objActSheet->setCellValue('A'.$i, g2u('编号'));
	$objActSheet->setCellValue('B'.$i, g2u('问题'));
	$objActSheet->setCellValue('C'.$i, g2u('样本数量'));
	$objActSheet->setCellValue('D'.$i, g2u('1-2分样本数量'));
	$objActSheet->setCellValue('E'.$i, g2u('3分样本数量'));
	$objActSheet->setCellValue('F'.$i, g2u('4分样本数量'));
	$objActSheet->setCellValue('G'.$i, g2u('5分样本数量'));
	$objActSheet->setCellValue('H'.$i, g2u('平均分值'));
	$objActSheet->setCellValue('I'.$i, g2u('75分位'));
	$objActSheet->setCellValue('J'.$i, g2u('方差'));
foreach($data as $key=>$val){
		$i++;
		$objActSheet->setCellValue('A'.$i, $key+1);
		$objActSheet->setCellValue('B'.$i, $val['question']);
		$objActSheet->setCellValue('C'.$i, $val['counts']);
		$objActSheet->setCellValue('D'.$i, $val['1-2']);
		$objActSheet->setCellValue('E'.$i, $val['3']);
		$objActSheet->setCellValue('F'.$i, $val['4']);
		$objActSheet->setCellValue('G'.$i, $val['5']);
		$objActSheet->setCellValue('H'.$i, $val['average']);
		$objActSheet->setCellValue('I'.$i, $val['quartile']);
		$objActSheet->setCellValue('J'.$i, $val['variance']);
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
font-size:8px;
font-weight:700;
width:370px;
}
.graph,.oran, .green, .blue, .red, .black{
position:relative;
text-align:left;
color:#444444;
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
			data-options="rownumbers:true,singleSelect:false,url:'tongji1.php?action=gettongji',method:'get',toolbar:'#tb'">
		<thead>
			<tr>
				 
				<th data-options="field:'question',width:230">问题</th>
				 
				<th data-options="field:'counts',width:80,align:'center'">样本数量</th>
				<th data-options="field:'1-2',width:100,align:'center'">1-2分样本数量</th>
				<th data-options="field:'3',width:70,align:'center'">3分样本数量</th>
				<th data-options="field:'4',width:70,align:'center'">4分样本数量</th>
				<th data-options="field:'5',width:70,align:'center'">5分样本数量</th>
				<th data-options="field:'status',width:380,align:'center',formatter:imgforMatter"> 柱形图显示分值分布比例</th>
					
				<th data-options="field:'average',width:70,align:'center'"> 平均分值</th>
				<th data-options="field:'average_total',width:70,align:'center'"> 集团均值</th>
				<th data-options="field:'quartile',width:70,align:'center'"> 75分位</th>
				<th data-options="field:'variance',width:70,align:'center'"> 方差</th>
			</tr>
		</thead>
	</table>
	<div id="tb" style="padding:5px;height:auto">
		 
		<div>
		 <input id="cc" class="easyui-combotree" data-options="url:'ajax_getDepartment.php',method:'get',label:'部门:',labelPosition:'left',multiple:true,cascadeCheck:false" style="width:50%">
			<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="doSearch();" >统计</a>
			<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="export_data();" >导出</a>
			<div class="graph2"><span class="oran" style="width:18px"></span>1-2分 <span class="green" style="width:18px"></span>3分 <span class="blue" style="width:18px"></span>4分 <span class="red" style="width:18px"></span>5分 </div>
		</div>
	</div>
 
<script type="text/javascript">
	function doSearch(){    
		$('#tj').datagrid('load',{
			itemid: $('#cc').combotree('getValues')
			 
		});
	}
	function export_data(){    
		 
		window.location.href='tongji1.php?itemid='+$('#cc').combotree('getValues')+'&action=gettongji&action2=export';
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