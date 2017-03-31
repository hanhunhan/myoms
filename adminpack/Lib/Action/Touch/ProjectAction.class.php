<?php
	class ProjectAction extends ExtendAction{
		 public $param=array();
		 function caselist(){ 
			//$_REQUEST['search5']='CREATOR';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_SESSION['uinfo']['uid'];

			Vendor('Oms.Form');
			$form = new Form(); 
			//$form->CZBTN = '<a class="contrtable-link" onclick="paylist(this);"  href="javascript:void(0);"> 每日支出</a> //<a class="contrtable-link" onclick="customerlist(this);"  href="javascript:void(0);">办卡用户</a> <a //class="contrtable-link" onclick="thisedit(this);"  href="javascript:void(0);">编辑</a>
			//<a class="contrtable-link" onclick="fdel(this);"   href="javascript:void(0);">删除</a> '; 
			$sql = "select ID,FOLWNAME from FLOW where id in(".$_SESSION['uinfo']['flow'].")";
			if(!$_REQUEST['ID'] && $_REQUEST['showForm']==1){
				
		$form = $form->initForminfo(95)->where("CREATOR='".$_SESSION['uinfo']['uid']."'")->setMyFieldVal('CREATOR',$_SESSION['uinfo']['uid'],true)->setMyFieldVal('CLERKID',$_SESSION['uinfo']['uid'],true)->setMyFieldVal('CREATETIME',date('Y-m-d'),true)->setMyField('FLOW','LISTSQL',$sql)->getResult();
			}else $form =  $form->initForminfo(95)->where("CREATOR='".$_SESSION['uinfo']['uid']."'")->setMyField('FLOW','FORMVISIBLE','0')->getResult();
			$this->assign('form',$form);
			$this->display('caselist');	 
		 }
		 function ajaxgetFlow(){
			$flow = D('flow')->where("ID='".$_REQUEST['flowId']."' ")->find();
			$flowVersion = D('flowversion')->where("FLOWID='".$_REQUEST['flowId']."' ")->find();
			$result['TYPEID'] = $flow['BUSINESSTYPE'];
			$result['WFVID'] = $flowVersion['ID'];
			echo json_encode($result);

		 }
		 function doproject(){ 
			Vendor('Oms.Form');
			$form = new Form(); 
			$project =  D('project')->where("CASEID='".$_REQUEST['gcaseid']."' ")->find(); 
			//$_REQUEST['ID'] = $project ? $_REQUEST['gcaseid']: null;
			if($project){
				$_REQUEST['ID'] = $_REQUEST['gcaseid'];
			}else{
				//$_POST['CASEID'] = null;
				//$_REQUEST['NOINCREMENT'] = '-1';
				$form->NOINCREMENT = '-1';//非自增
			}
			$paramUrl = 'showForm=1&gcaseid='.$_REQUEST['gcaseid'];
			$paramUrl2 = 'gcaseid='.$_REQUEST['gcaseid'];
			$form->FORMFORWARD = U('Project/prjbudget',$paramUrl2);;
			$form =  $form->initForminfo(96)->setMyFieldVal('CASEID',$_REQUEST['gcaseid'],true)->getResult();
			
			

			$this->assign('form',$form);
			$this->assign('paramUrl',$paramUrl);
			$this->assign('paramUrl2',$paramUrl2);
			$this->display('doproject');
			 
		 }
		 function ajaxchecktype(){//类型
			  if($_REQUEST['ID']) 
			  $res = D('prjbudget')->where("CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' and ID<>'".$_REQUEST['ID']."' ")->select();
			  else  $res = D('prjbudget')->where("CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' ")->select();
			   //echo "CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' ";
			  if($res){
				  $result['status'] = 'n';
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '该类型的预算已存在,请选择其他类型') ;
			  }else {
				  $result['status'] = 'y';
				   $result['info'] = '';
			  }
			  echo json_encode($result);
		 }
		  function ajaxcheckfee(){//费用
			 $ress = D('l_fee')->where("PARENTID='".$_REQUEST['param']."'")->select();
			 if($ress){
				  $result['status'] = 'n';
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '请选择无子类选项') ;
			 }else{
				  if($_REQUEST['ID']) $res = D('budgetfee')->where("FEEID='".$_REQUEST['param']."' and BUDGETID='".$_REQUEST['BUDGETID']."' and ID<>'".$_REQUEST['ID']."' ")->select();
				  else $res = D('budgetfee')->where("FEEID='".$_REQUEST['param']."' and BUDGETID='".$_REQUEST['BUDGETID']."'   ")->select();
				   //echo "CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' ";
				  if($res){
					  $result['status'] = 'n';
					  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '本预算已经添加过该费用类型,请选择其他费用') ;
				  }else {
					  $result['status'] = 'y';
					   $result['info'] = '';
				  }
			 }
			  echo json_encode($result);
		 }
		  function ajaxchecksale(){//分解
			  if($_REQUEST['ID'])
			  $res = D('budgetsale')->where("SALEMETHODID='".$_REQUEST['param']."' and BUDGETID='".$_REQUEST['BUDGETID']."' and ID<>'".$_REQUEST['ID']."' ")->select();
			  else   $res = D('budgetsale')->where("SALEMETHODID='".$_REQUEST['param']."' and BUDGETID='".$_REQUEST['BUDGETID']."'  ")->select();
			   //echo "CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' ";
			  if($res){
				  $result['status'] = 'n';
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '本预算已经添加过该销售方式,请选择其他销售方式') ;
			  }else {
				  $result['status'] = 'y';
				   $result['info'] = '';
			  }
			  echo json_encode($result);
		 }
		 function prjbudget(){ 
			//$_REQUEST['search5']='CASEID';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_REQUEST['gcaseid'];

			Vendor('Oms.Form');
			$form = new Form(); 
			//$form->CZBTN = ' 
			//<a class="contrtable-link" onclick="tongji(this);"   href="javascript:void(0);">统计 </a> 
			//<a class="contrtable-link" onclick="thedit(this);"   href="javascript:void(0);">编辑 </a> <a class="contrtable-link flock" onclick="fdel(this );"   href="javascript:void(0);">删除</a>'; 
			$children = array(array('预算费用',U('/Project/budgetfee')),array('预算销售目标分解',U('/Project/budgetsale')));
			$form = $form->initForminfo(97)->where("CASEID='".$_REQUEST['gcaseid']."'")->setMyFieldVal('CASEID',$_REQUEST['gcaseid'],true)->setChildren($children)->getResult();

			//$form->initForminfo(97);
			//$form->where("CASEID='".$_REQUEST['gcaseid']."'")->setMyFieldVal('CASEID',$_REQUEST['gcaseid'],true);
			//$form =  $form->getResult();
			 

			$paramUrl = 'showForm=1&gcaseid='.$_REQUEST['gcaseid']; 
			$paramUrl2 = 'gcaseid='.$_REQUEST['gcaseid']; 
			$this->assign('form',$form);
			$this->assign('paramUrl',$paramUrl);
			$this->assign('paramUrl2',$paramUrl2);
			//$this->assign('showForm',$_REQUEST['showForm']);

			$this->display('prjbudget');
			 
		 }
		 function budgetfee(){//预算费用
			//if($_REQUEST['showForm'] && empty($_REQUEST['budgetid'])  )exit("<script>alert('请先添加立项预算！');self.location=document.referrer;</script>");
			Vendor('Oms.Form');

			//$_REQUEST['search5']='BUDGETID';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_REQUEST['budgetid'];

			$form = new Form(); 
			
			$form =  $form->initForminfo(98)->where("BUDGETID='".$_REQUEST['budgetid']."'")->setMyFieldVal('BUDGETID',$_REQUEST['budgetid'],true)->getResult();
			 

			 
			$this->assign('form',$form);
			 
			$this->display('budgetfee');

		 }
		 function budgetsale(){//预算销售目标分解
			 if($_REQUEST['showForm'] && empty($_REQUEST['budgetid'])  )exit("<script>alert('请先添加立项预算！');self.location=document.referrer;</script>");
			Vendor('Oms.Form');
			//$_REQUEST['search5']='BUDGETID';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_REQUEST['budgetid'];
			$form = new Form(); 
			
			$form =  $form->initForminfo(99)->where("BUDGETID='".$_REQUEST['budgetid']."'")->setMyFieldVal('BUDGETID',$_REQUEST['budgetid'],true)->getResult();
			 

			 
			$this->assign('form',$form);
			 
			$this->display('budgetsale');
		 }
		 

		 function l_fee(){
			Vendor('Oms.Form');
			$form = new Form(); 
			$form =  $form->initForminfo(100)->getResult();
			$this->assign('form',$form);
			$this->display('caselist');
		 }
		 function L_salemethod(){
			Vendor('Oms.Form');
			$form = new Form();
			$form =  $form->initForminfo(101)->getResult();
			$this->assign('form',$form);
			$this->display('caselist');
		 }
		 
		 function paylist(){
			 
			//$_REQUEST['search5']='CASEID';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_REQUEST['gcaseid'];
			$paramUrl = 'gcaseid='.$_REQUEST['gcaseid'];
			 
			Vendor('Oms.Form');
			$form = new Form();
			$form =  $form->initForminfo(102)->where("CASEID='".$_REQUEST['gcaseid']."'")->setMyFieldVal('CASEID',$_REQUEST['gcaseid'],true)->getResult();
			$this->assign('form',$form);
			$this->assign('paramUrl',$paramUrl);
			$this->display('paylist');

		 }
		 function customerlist(){
			//$_REQUEST['search5']='CASEID';
			////$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_REQUEST['gcaseid'];
			$paramUrl = 'gcaseid='.$_REQUEST['gcaseid'];
			Vendor('Oms.Form');
			$form = new Form();
			$form =  $form->initForminfo(103)->where("CASEID='".$_REQUEST['gcaseid']."'")->setMyFieldVal('CASEID',$_REQUEST['gcaseid'],true)->getResult();
			$this->assign('form',$form);
			$this->assign('paramUrl',$paramUrl);
			$this->display('customerlist');

		 }
		 function xiaoshouzk(){
			//$_REQUEST['search5']='CASEID';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_REQUEST['gcaseid'];
			$paramUrl = 'zhtongji='.$_REQUEST['zhtongji'].'&gcaseid='.$_REQUEST['gcaseid'];


			$list = D('prjbudget')->where("CASEID='8'")->select();
			foreach($list as $k=>$v){
					$temp[] = $v['ID'];
					 
			}
			$this->param['budgetid'] = implode(',',$temp);
			$chengjiaots = $this->getsaledataTotal('SETS');
			$daokel = $this->getsaledataTotal('CUSTOMERS');
			$qurencj = D('customerlist')->where("CASEID='".$_REQUEST['gcaseid']."'")->count();
			$bkshouru = D('customerlist')->where("CASEID='".$_REQUEST['gcaseid']."'")->sum('PAYED');
			$bkshouru = $bkshouru ? $bkshouru:0;
			$this->assign('chengjiaots',$chengjiaots);
			$this->assign('daokel',$daokel);
			$this->assign('qurencj',$qurencj);
			$this->assign('bkshouru',$bkshouru);
			Vendor('Oms.Form');
			$form = new Form();
			$form =  $form->initForminfo(104)->where("CASEID='".$_REQUEST['gcaseid']."'")->setMyFieldVal('CASEID',$_REQUEST['gcaseid'],true)->getResult();
			$this->assign('form',$form);
			$this->assign('paramUrl',$paramUrl);
			$this->assign('zhtongji',$_REQUEST['zhtongji']);
			$this->display('xiaoshouzk');

		 }
		 function qurendaochang(){
			//$_REQUEST['search5']='CASEID';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_REQUEST['gcaseid'];
			$paramUrl = 'gcaseid='.$_REQUEST['gcaseid'];
			Vendor('Oms.Form');
			$form = new Form();
			$form =  $form->initForminfo(105)->where("CASEID='".$_REQUEST['gcaseid']."'")->getResult();
			$this->assign('form',$form);
			$this->assign('paramUrl',$paramUrl);
			$this->display('qurendaochang');

		 }
		  function caiwuzk(){
			 
			$paramUrl = '&zhtongji='.$_REQUEST['zhtongji'].'&gcaseid='.$_REQUEST['gcaseid'];
		    $zhichu = D("paylist")->where("CASEID='".$_REQUEST['gcaseid']."'")->sum("AMOUNT");
			$shouru = D("customerlist")->where("CASEID='".$_REQUEST['gcaseid']."'")->sum("PAYED");
			$project = D("project")->where("CASEID='8'")->find();
			$WHOCOLLECT = $project['WHOCOLLECT'];
			$RATIOPOOL = $project['RATIOPOOL'];
			$zhichu = $zhichu ? $zhichu:0;
			$shouru = $shouru ? $shouru:0;
			if($WHOCOLLECT ==1){
				 $kshouru = $shouru*(100-$RATIOPOOL )/100;
			}elseif($WHOCOLLECT ==2){
				$kshouru = $shouru*($RATIOPOOL )/100;
			}
			
			$maoli = $shouru-$zhichu;
			$kmaoli = $kshouru-$zhichu;
			$this->assign('zhichu',$zhichu);
			$this->assign('shouru',$shouru);
			$this->assign('kshouru',$kshouru);
			$this->assign('maoli',$maoli);
			$this->assign('kmaoli',$kmaoli);
			$this->assign('paramUrl',$paramUrl);
			$this->assign('zhtongji',$_REQUEST['zhtongji']);
			$this->display('caiwuzk');

		 }
		 function report(){
			//$prjbudget = D('prjbudget')->where("ID='".$_REQUEST['prjbudgetId']."'")->find();
			//$project = D('project')->where("CASEID='".$_REQUEST['caseid']."'")->find();
			if($_REQUEST['prjbudgetId']){//单个
				$this->param['budgetid'] = $_REQUEST['prjbudgetId'];
				$budget = D('prjbudget')->where("ID='".$_REQUEST['prjbudgetId']."'")->find();
				$project = D('project')->where("CASEID='".$budget['CASEID']."'")->find();
				$type = D('businesstype')->where("ID='".$budget['BUDGETTYPE']."'")->find();
				$project['FEE'] = $budget['FEE'];//单套收费标准
				$project['SUMPROFIT'] = $budget['SUMPROFIT'];//预估总收益
				$project['ADBUDGET'] = $budget['ADBUDGET'];//广告预算（折后价）
				$project['ADINDEXBUDGET'] = $budget['ADINDEXBUDGET'];//地产首页配送广告（折后）
				$project['AVERAGESETS'] = $budget['AVERAGESETS'];//月均去化套数
				$project['FIRSTSETS'] = $budget['FIRSTSETS'];//首次去化套数
				$project['FROMDATE'] = $this->fomatdate($budget['FROMDATE']);//
				$project['TODATE'] = $this->fomatdate($budget['TODATE']);//
				$project['BUDGETTYPE'] = '('.$type['TYPENAME'].')';
				$uesr= D('admin_user')->where("LOAN_USERID='".$project['MANAGER_ID'] ."'")->find();
				$project['MANAGER_ID'] =$uesr['LOAN_USERNAME'];



			}elseif($_REQUEST['gcaseid']){//综合
				$list = D('prjbudget')->where("CASEID='".$_REQUEST['gcaseid']."'")->select();
				$fee = 0;
				$sumprofit = 0;
				$ADBUDGET =0;
				$ADINDEXBUDGET=0;
				$AVERAGESETS=0;
				$FIRSTSETS=0;
				$FROMDATE = 0;
				$TODATE = 0;
				foreach($list as $k=>$v){
					$temp[] = $v['ID'];
					$fee += $v['FEE'];
					$sumprofit += $v['SUMPROFIT'];
					$ADBUDGET += $v['ADBUDGET'];
					$ADINDEXBUDGET += $v['ADINDEXBUDGET'];
					$FIRSTSETS +=$v['FIRSTSETS'];
					$AVERAGESETS +=$v['AVERAGESETS'];
					if( $FROMDATE==0) $FROMDATE=$this->fomatdate($v['FROMDATE']) ;
					if( $TODATE==0) $TODATE=$this->fomatdate($v['TODATE']) ;
					$FROMDATE =  strtotime($this->fomatdate($v['FROMDATE']))<strtotime($this->fomatdate($v['FROMDATE']))?$this->fomatdate($v['FROMDATE']):$FROMDATE;
					$TODATE =  strtotime( $this->fomatdate($v['TODATE']) )>strtotime($TODATE)? $this->fomatdate($v['TODATE']):$TODATE;
				}
				$this->param['budgetid'] = implode(',',$temp);
				$project = D('project')->where("CASEID='".$_REQUEST['gcaseid']."'")->find();
				$project['FEE'] = $fee;//单套收费标准
				$project['SUMPROFIT'] = $sumprofit;//预估总收益
				$project['ADBUDGET'] = $ADBUDGET;//广告预算（折后价）
				$project['ADINDEXBUDGET'] = $ADINDEXBUDGET;//地产首页配送广告（折后）
				$project['AVERAGESETS'] = $AVERAGESETS;//月均去化套数
				$project['FIRSTSETS'] = $FIRSTSETS;//首次去化套数
				$project['FROMDATE'] = $FROMDATE;//
				$project['TODATE'] = $TODATE;//
				$uesr= D('admin_user')->where("LOAN_USERID='".$project['MANAGER_ID'] ."'")->find();
				$project['MANAGER_ID'] =$uesr['LOAN_USERNAME'];
			}
			$this->param['totalCost'] = $this->getTotalCost();
			$html = $this->get_prj_tbl($project);
			$this->assign('html',$html);
			//$this->assign('paramUrl',$paramUrl);
			$this->display('report');


		 }

		 function fomatdate($s){
			 preg_match('/(?<d>\d{2})-(?<m>\d{1,2})月\s*-(?<y>\d{2})/',$s,$m);
			 return date('Y-m-d',strtotime($m['y'].'-'.$m['m'].'-'.$m['d']));
		 }
		 function getTotalCost(){//总计费用
			
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			
			$res = D("budgetfee")->where($map)->sum('amount');
			$res = $res ? $res :0;
			//var_dump(D("budgetfee")->getLastSql());
			return $res;
		}
		function getFeeInfo($InputName){//费用说明
			
			$fee = D("l_fee")->where("INPUTNAME='$InputName'")->find();//echo $this->param['budgetid'].'--';
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			$map['FEEID'] = $fee['ID'];
			$res = D("budgetfee")->where($map)->find();
			 
			//var_dump(D("budgetfee")->getLastSql());
			return $res['REMARK'];
		}
		function getdata($InputName){//费用
			$fee = D("l_fee")->where("INPUTNAME='$InputName'")->find();//echo $this->param['budgetid'].'--';
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			$map['FEEID'] = $fee['ID'];
			$res = D("budgetfee")->where($map)->sum('amount');
			$res = $res ? $res :0;
			//var_dump(D("budgetfee")->getLastSql());
			return $res;
		}
		function getsaledata($name,$field='SETS'){//目标分解
			$salem = D("L_salemethod")->where("NAME='$name'")->find();
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			$map['SALEMETHODID'] = $salem['ID'];
			$res = D("budgetsale")->where($map)->sum("$field");
			$res = $res ? $res :0;
			//var_dump(D("budgetfee")->getLastSql());
			return $res;
		}
		function getsaledataTotal( $field='SETS'){//目标分解总计
			 
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			 
			$res = D("budgetsale")->where($map)->sum("$field");
			$res = $res ? $res :0;
			//var_dump(D("budgetfee")->getLastSql());
			return $res;
		}
		 #画项目表格
		function get_prj_tbl($row){
			
			$cfg['sales_target'] = array(
				'1' => "中介",
				'2' => "渠道",
				'3' => "数据营销",
				'4' => "拓客",
				'5' => "线上",
				'6' => "自然来客",
			);
			$db_arr = array(
				'CONTRACTNO'=>'合同编号',
				'MANAGER_ID'=>'项目经理',
				'PRJNAME'=>'项目名称',
				'STANDARDNAME'=>'关联楼盘',
				'PROPERTYTYPE'=>'物业类别',
				'STANDARDID'=>'关联ID',
				'DEVELOPER'=>'开发企业',
				'ADDRESS'=>'项目地址',
				'SOURCE'=>'团立方房源',
				'DISCOUNT'=>'团立方优惠',
				'PERMIT'=>'项目销售许可证',
				'CAPITALPOOL'=>'资金池', //isfundpool
				'ADVANTAGE'=>'项目及房源优势',
				'INFERIORITY'=>'项目及房源劣势',
				'PROFIT'=>'收益内容',
				'REMARK'=>'备注',
				'AVERAGESETS'=>'前一个月月均去化套数',
				'FIRSTSETS'=>'最近一次开盘（加推）去化套数',
			);

			$return_tr = array(
				'agency'=>'<td rowspan="46">费用类别—线下</td><td>经纪服务费</td><td colspan="2">中介费</td>',
				'sms'=>'<td rowspan="2">数据营销费</td><td colspan="2">短信费</td>',
				'phone'=>'<td colspan="2">电话费</td>',
				'market'=>'<td rowspan="9">渠道费</td><td rowspan="3">场地费</td><td>超市/商场</td>',
				'into_village'=>'<td>进小区</td>',
				'into_office'=>'<td>写字楼</td>',
				'bus'=>'<td rowspan="2">租车费(载人)</td><td>大巴车</td>',
				'taxi'=>'<td>出租车</td>',
				'transportation'=>'<td colspan="2">运输费(载物)</td>',
				'seo'=>'<td>推广费</td><td>SEO/SEM推广</td>',
				'field_warmup'=>'<td colspan="2">案场暖场费</td>',
				'netfriend_foot'=>'<td colspan="2">网友食品费</td>',
				'employees'=>'<td rowspan="2">人员工资</td><td colspan="2">公司员工</td>',
				'parttime_staff'=>'<td colspan="2">兼职人员</td>',
				'business_benefits'=>'<td rowspan="4">业务费</td><td colspan="2">业务津贴</td>',
				'business_other'=>'<td colspan="2">其他费用</td>',
				'actual_entertainment'=>'<td colspan="2">实际应酬</td>',
				'travel_expenses'=>'<td colspan="2">差旅费</td>',
				'propaganda'=>'<td rowspan="4">制作费</td><td colspan="2">宣传品</td>',
				'exhibition'=>'<td colspan="2">布展费</td>',
				'onesheet'=>'<td colspan="2">单页</td>',
				'xdisplay'=>'<td colspan="2">X展架</td>',
				'major_suit'=>'<td rowspan="5">外部广告费</td><td colspan="2">大牌</td>',
				'led'=>'<td colspan="2">LED</td>',
				'bus_sub'=>'<td colspan="2">公交/地铁</td>',
				'radio'=>'<td colspan="2">电台</td>',
				'newspaper'=>'<td colspan="2">报纸/杂志</td>',
				'net_friend'=>'<td rowspan="4">宣传费</td><td colspan="2">网友</td>',
				'home_buyers'=>'<td colspan="2">置业顾问</td>',
				'customer'=>'<td colspan="2">客户</td>',
				'publicity_other'=>'<td colspan="2">其他</td>',
				'third_party'=>'<td colspan="3">支付第三方费用</td>',
				'profit_sharing'=>'<td>项目分成</td><td colspan="2">利润分成</td>',
				'old_new'=>'<td rowspan="4">带看费</td><td colspan="2">老带新</td>',
				'new_new'=>'<td colspan="2">新带新</td>',
				'intermediary_watch'=>'<td colspan="2">中介带看</td>',
				'channel_watch'=>'<td colspan="2">渠道带看</td>',
				'transaction_rewards'=>'<td>成交费</td><td colspan="2">成交奖励</td>',
				'internal_commission'=>'<td>内部佣金</td><td colspan="2">内部提成</td>',
				'external_rewards'=>'<td>外部佣金</td><td colspan="2">外部奖励</td>',
				'pos'=>'<td>POS手续费</td><td colspan="2">POS手续费</td>',
				'taxes'=>'<td colspan="3">税金(支付第三方费用的10%)</td>',
				'other'=>'<td colspan="3">其他</td>',
			);

			$html = '';
			$html = $html . "<table width='90%' cellspacing='0' cellpadding='10' border='1' style='border-collapse: collapse;' align='center'>";

			#标题
			$html = $html . "<tr><td colspan='12' align='center'><h1 style='font-weight:600;font-size:16px;' >\"{$row['PRJNAME']}\"立项预算表 {$row['BUDGETTYPE']}</h1></td></tr>";

			$html = $html . "<tr><td colspan='2'>执行日期</td><td colspan='10'>".date('Y-m-d',strtotime($row['FROMDATE'])).' - '.date('Y-m-d',strtotime($row['TODATE']))."</td></tr>";

			# 基本属性
			$i = 0;
			foreach($db_arr as $k=>$v)
			{
				if($i%2==0)
					if($k=='PERMIT')
					{
						$temp_sp = $row[$k]?"有":"无";
					 
						$text = $temp_sp;
						$html = $html . "<tr><td colspan='2'>{$v}</td><td colspan='3'>" . $text . "</td>";	
					}
					else{
						$html = $html . "<tr><td colspan='2'>{$v}</td><td colspan='3'>" . $row[$k]. "</td>";	
					}	
				else
					if($k=='CAPITALPOOL'){
						$temp = $row[$k]==-1? "是" . " 比例:{$row['RATIOPOOL']}%":"否";
						 
						//$text = $row_old[$k]?get_contrast($temp,$old_temp):$temp;
						$html = $html . "<td colspan='2'>" . $v . "</td><td colspan='5'>" . $temp . "</td></tr>";				
					}
					else{
						$html = $html . "<td colspan='2'>{$v}</td><td colspan='5'>" . $row[$k] . "</td></tr>";
					}
				$i ++;
			}
			$html = $html . "<tr><td colspan='4'>" . $row['PRJNAME'] . "</td><td>金额（元）</td><td>单套收费标准（元/套）</td><td rowspan='2'>目标分解</td><td>预估成交套数</td><td>预估导客量</td><td rowspan='8'>费用占比</td><td rowspan='8'>费用说明</td><td rowspan='8'>备注</td></tr>";

			 
			 
			$html = $html . "<tr><td colspan='4' rowspan='7'>预估收入</td><td rowspan='7'>"  . $row['SUMPROFIT'].  "</td><td rowspan='7'>" . $row['FEE']. "</td><td>" .$this->getsaledataTotal('SETS'). "</td><td>" . $this->getsaledataTotal('CUSTOMERS'). "</td></tr>";

			#销售目标
			//$sales_targets = unserialize($row['sales_target']);
			 
			foreach($cfg['sales_target'] as $k=>$v){
				$html = $html . "<tr><td>$v</td><td>" . $this->getsaledata($v,'SETS')   . "</td><td>" .   $this->getsaledata($v,'CUSTOMERS') . "</td></tr>";
			}

			#线下费用
			//$offline_cost = unserialize($row['offline_cost']);
			 
			foreach($return_tr as $k=>$v)
			{
				$html = $html . "<tr>";
				$html = $html . $v . "<td>" . $this->getdata($k) . "</td><td>--</td><td>--</td><td></td><td></td><td>" .round($this->getdata($k)/$this->param['totalCost'],4) * 100 . "% </td><td>" . $this->getFeeInfo($k)."  </td><td>--</td></tr>";
			}

			$html = $html . "<tr><td colspan='3'>付现成本</td><td>" .$this->param['totalCost'] . "</td><td>--</td><td>--</td><td></td><td></td><td>--</td><td>--</td><td>--</td></tr>";
			$html = $html . "<tr><td colspan='3'>付现利润</td><td>" .($row['SUMPROFIT']- $this->param['totalCost']) . "</td><td>--</td><td>--</td><td></td><td></td><td>--</td><td>--</td><td>--</td></tr>";
			$html = $html . "<tr><td colspan='3'>付现利润率</td><td>" . (round(($row['SUMPROFIT']- $this->param['totalCost'])/$row['SUMPROFIT'],4)*100)."%" . "</td><td>--</td><td>--</td><td></td><td></td><td>--</td><td>" . ' --' . "</td><td>--</td></tr>";

			#线上费用
			//$online_cost = unserialize($row['online_cost']);
			 
			$html = $html . "<tr><td rowspan='3'>费用类别—线上</td><td colspan='3'>广告预算（折后价）</td><td>" . $row['ADBUDGET']. "</td><td colspan='3'>地产首页配送广告（折后）</td><td colspan='3'>" . $row['ADINDEXBUDGET']. "</td><td>" . ''. "</td></tr>";
			$html = $html . "<tr><td colspan='3'>扣除线下+线上支出利润</td><td>" . ($row['SUMPROFIT']- $this->param['totalCost']-$row['ADBUDGET']) . "</td><td colspan='6'>--</td><td></td></tr>";
			$html = $html . "<tr><td colspan='3'>扣除线下+线上支出利润率</td><td>" .(round( ($row['SUMPROFIT']- $this->param['totalCost']-$row['ADBUDGET'])/$row['SUMPROFIT'],4)*100) . "</td><td colspan='6'>--</td><td></td></tr>";
			$html = $html . "</table>";
			$html .= "<div class='lxts'>立项提示</div><div class='tscontent'>  <div class='tishi' id='lirundp' evs='".(round(($row['SUMPROFIT']- $this->param['totalCost'])/$row['SUMPROFIT'],4)*100)."'>  -</div>";
			$html .= "<div class='tishi' id='shouruyq' evs='".($row['SUMPROFIT']- $this->param['totalCost'])."'>  -</div>";
			$html .= "<div class='tishi' id='guanggaoyq' evs='".$row['ADBUDGET']."'>  -</div> </div>";

			return $html;
		}
        
        
        /**
        +----------------------------------------------------------
        * ajax获取楼盘列表信息
        +----------------------------------------------------------
        * @access public
        +----------------------------------------------------------
        * @param string $keyword 关键词
        * @param int $city_id 城市编号
        +----------------------------------------------------------
        * @return json 楼盘数据
        +----------------------------------------------------------
        */
        public function ajax_get_project_list()
        {   
            //根据关键词获取楼盘信息
            $project = D('Project');
            $search_key =  u2g(urldecode($this->_request('keyword')));
            $project_info = $project->get_my_project_list($search_key);
            
            $ajax_data = array();
            if(!empty($project_info) && is_array($project_info))
            {   
                foreach ($project_info as $key => $value)
                {   
                    $ajax_data[$key]['id'] = $value['ID'];
                    $ajax_data[$key]['city_id'] = $value['CITY_ID'];
                    $ajax_data[$key]['label'] = g2u($value['PROJECTNAME']);
                }
            }
            else
            {
                $ajax_data[0]['id'] = 0;
                $ajax_data[$key]['city_id'] = 0;
                $ajax_data[0]['label'] =  g2u('暂无楼盘信息');  
            }
            
            echo json_encode($ajax_data);
            exit;
        }
        
        
        /**
         +----------------------------------------------------------
         * ajax获取收费标准
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         * @param int $prj_id 楼盘ID
         * @param int $scale_type 收费类型
         * @param int $status 费用记录状态
         +----------------------------------------------------------
         * @return json 费用数据
         +----------------------------------------------------------
         */
        public function ajax_get_feescale()
        {	
        	//项目编号
        	$prj_id =  intval($this->_request('prj_id'));
        	
        	//费用类型
            $scaletype = $this->_request('scale_type');
        	$scaletype = !empty($scaletype) ? intval($scaletype) : '';
        	
        	//费用状态
        	$status = $this->_request('status');
        	$status = !empty($status) ? intval($status) : '';
        	
        	//业务类型
        	$case_type = $this->_request('case_type');
        	$case_type = !empty($case_type) ? strip_tags($case_type) : '';
        	
        	$case_model = D('ProjectCase');
        	
        	$feescale = array();
        	if($prj_id > 0 && !empty($case_type) )
        	{	
        		
        		//根据业务类型获取案例信息
	        	$caseinfo = array();
	        	$caseinfo = $case_model->get_info_by_pid($prj_id, $case_type);
	        	
	        	//根据案例编号、费用类型、状态获取费用标准信息
	        	if(is_array($caseinfo) && !empty($caseinfo))
	        	{	
	        		$case_id = !empty($caseinfo[0]['ID']) ? intval($caseinfo[0]['ID']) : 0;
	        		$project = D('Project');
	        		$feescale = $project->get_feescale_by_cid($case_id, $scaletype, $status);
	        	}
        	}
			
        	$ajax_data = array();
        	if(!empty($feescale) && is_array($feescale))
        	{
        		foreach ($feescale as $key => $value)
        		{
        			$ajax_data[$key]['ID'] = $value['ID'];
        			$ajax_data[$key]['CASE_ID'] = $value['CASE_ID'];
        			$ajax_data[$key]['SCALETYPE'] = $value['SCALETYPE'];
        			$ajax_data[$key]['AMOUNT'] = $value['AMOUNT'];
        		}
        	}
        	else
        	{
        		$ajax_data[0]['ID'] = 0;
        	}
        	
        	echo json_encode($ajax_data);
        	exit;
        }
        
        
        /**
         +----------------------------------------------------------
         * ajax获取项目楼盘信息
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         * @param int $prj_id 楼盘ID
         +----------------------------------------------------------
         * @return json 项目楼盘信息
         +----------------------------------------------------------
         */
        public function ajax_get_houseinfo_by_pid()
        {   
            $ajax_data = array();
            $house_model = M('erp_house');
            $project_id = intval($_REQUEST['project_id']);
            $ajax_data = $house_model->where("PROJECT_ID = '".$project_id."'")->find();
            
            //项目楼盘信息不为空需要转换中文编码
            $ajax_data = !empty($ajax_data) ? g2u($ajax_data) : $ajax_data;
            
            echo json_encode($ajax_data);
        	exit;
        }

		/**
         +----------------------------------------------------------
         * ajax获取项目目标分解销售方式
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         * @param int $prj_id 楼盘ID
         +----------------------------------------------------------
         * @return json 项目楼盘信息
         +----------------------------------------------------------
         */
        public function ajax_get_project_budget_sale_by_pid()
        {   
            $ajax_data = array();
            $prj_id =  !empty($_GET['prj_id']) ? intval($_GET['prj_id']) : 0;
            
            if($prj_id > 0)
            {   
                $member_model = D('Member');
                $source_arr = $member_model->get_conf_member_source_remark();
                 
                $project_model = D('Project');
                $project_sale_arr = $project_model->get_project_budget_sale_by_prjid($prj_id);
                
                $temp_arr = array();
                if(is_array($project_sale_arr) && !empty($project_sale_arr))
                {
                    foreach($project_sale_arr as $key => $value)
                    {   
                        if(key_exists($value['SALEMETHODID'], $source_arr))
                        {
                            $temp_arr[$key]['ID'] = $value['SALEMETHODID'];
                            $temp_arr[$key]['NAME'] = $source_arr[$value['SALEMETHODID']];
                        } 
                    }
                    
                    $ajax_data = $temp_arr;
                }
                else
                {
                    $ajax_data[]['ID'] = 0;
                    $ajax_data[]['NAME'] = '';
                }
            }
            
            //项目楼盘信息不为空需要转换中文编码
            $ajax_data = !empty($ajax_data) ? g2u($ajax_data) : $ajax_data;
            
            echo json_encode($ajax_data);
            exit;
        }
	}
?>