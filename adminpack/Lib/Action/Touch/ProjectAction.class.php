<?php
	class ProjectAction extends ExtendAction{
		 public $param=array();
		 function caselist(){ 
			//$_REQUEST['search5']='CREATOR';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_SESSION['uinfo']['uid'];

			Vendor('Oms.Form');
			$form = new Form(); 
			//$form->CZBTN = '<a class="contrtable-link" onclick="paylist(this);"  href="javascript:void(0);"> ÿ��֧��</a> //<a class="contrtable-link" onclick="customerlist(this);"  href="javascript:void(0);">�쿨�û�</a> <a //class="contrtable-link" onclick="thisedit(this);"  href="javascript:void(0);">�༭</a>
			//<a class="contrtable-link" onclick="fdel(this);"   href="javascript:void(0);">ɾ��</a> '; 
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
				$form->NOINCREMENT = '-1';//������
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
		 function ajaxchecktype(){//����
			  if($_REQUEST['ID']) 
			  $res = D('prjbudget')->where("CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' and ID<>'".$_REQUEST['ID']."' ")->select();
			  else  $res = D('prjbudget')->where("CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' ")->select();
			   //echo "CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' ";
			  if($res){
				  $result['status'] = 'n';
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '�����͵�Ԥ���Ѵ���,��ѡ����������') ;
			  }else {
				  $result['status'] = 'y';
				   $result['info'] = '';
			  }
			  echo json_encode($result);
		 }
		  function ajaxcheckfee(){//����
			 $ress = D('l_fee')->where("PARENTID='".$_REQUEST['param']."'")->select();
			 if($ress){
				  $result['status'] = 'n';
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '��ѡ��������ѡ��') ;
			 }else{
				  if($_REQUEST['ID']) $res = D('budgetfee')->where("FEEID='".$_REQUEST['param']."' and BUDGETID='".$_REQUEST['BUDGETID']."' and ID<>'".$_REQUEST['ID']."' ")->select();
				  else $res = D('budgetfee')->where("FEEID='".$_REQUEST['param']."' and BUDGETID='".$_REQUEST['BUDGETID']."'   ")->select();
				   //echo "CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' ";
				  if($res){
					  $result['status'] = 'n';
					  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '��Ԥ���Ѿ���ӹ��÷�������,��ѡ����������') ;
				  }else {
					  $result['status'] = 'y';
					   $result['info'] = '';
				  }
			 }
			  echo json_encode($result);
		 }
		  function ajaxchecksale(){//�ֽ�
			  if($_REQUEST['ID'])
			  $res = D('budgetsale')->where("SALEMETHODID='".$_REQUEST['param']."' and BUDGETID='".$_REQUEST['BUDGETID']."' and ID<>'".$_REQUEST['ID']."' ")->select();
			  else   $res = D('budgetsale')->where("SALEMETHODID='".$_REQUEST['param']."' and BUDGETID='".$_REQUEST['BUDGETID']."'  ")->select();
			   //echo "CASEID='".$_REQUEST['CASEID']."' and BUDGETTYPE='".$_REQUEST['param']."' ";
			  if($res){
				  $result['status'] = 'n';
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '��Ԥ���Ѿ���ӹ������۷�ʽ,��ѡ���������۷�ʽ') ;
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
			//<a class="contrtable-link" onclick="tongji(this);"   href="javascript:void(0);">ͳ�� </a> 
			//<a class="contrtable-link" onclick="thedit(this);"   href="javascript:void(0);">�༭ </a> <a class="contrtable-link flock" onclick="fdel(this );"   href="javascript:void(0);">ɾ��</a>'; 
			$children = array(array('Ԥ�����',U('/Project/budgetfee')),array('Ԥ������Ŀ��ֽ�',U('/Project/budgetsale')));
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
		 function budgetfee(){//Ԥ�����
			//if($_REQUEST['showForm'] && empty($_REQUEST['budgetid'])  )exit("<script>alert('�����������Ԥ�㣡');self.location=document.referrer;</script>");
			Vendor('Oms.Form');

			//$_REQUEST['search5']='BUDGETID';
			//$_REQUEST['search5_s']='3';
			//$_REQUEST['search5_t']=$_REQUEST['budgetid'];

			$form = new Form(); 
			
			$form =  $form->initForminfo(98)->where("BUDGETID='".$_REQUEST['budgetid']."'")->setMyFieldVal('BUDGETID',$_REQUEST['budgetid'],true)->getResult();
			 

			 
			$this->assign('form',$form);
			 
			$this->display('budgetfee');

		 }
		 function budgetsale(){//Ԥ������Ŀ��ֽ�
			 if($_REQUEST['showForm'] && empty($_REQUEST['budgetid'])  )exit("<script>alert('�����������Ԥ�㣡');self.location=document.referrer;</script>");
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
			if($_REQUEST['prjbudgetId']){//����
				$this->param['budgetid'] = $_REQUEST['prjbudgetId'];
				$budget = D('prjbudget')->where("ID='".$_REQUEST['prjbudgetId']."'")->find();
				$project = D('project')->where("CASEID='".$budget['CASEID']."'")->find();
				$type = D('businesstype')->where("ID='".$budget['BUDGETTYPE']."'")->find();
				$project['FEE'] = $budget['FEE'];//�����շѱ�׼
				$project['SUMPROFIT'] = $budget['SUMPROFIT'];//Ԥ��������
				$project['ADBUDGET'] = $budget['ADBUDGET'];//���Ԥ�㣨�ۺ�ۣ�
				$project['ADINDEXBUDGET'] = $budget['ADINDEXBUDGET'];//�ز���ҳ���͹�棨�ۺ�
				$project['AVERAGESETS'] = $budget['AVERAGESETS'];//�¾�ȥ������
				$project['FIRSTSETS'] = $budget['FIRSTSETS'];//�״�ȥ������
				$project['FROMDATE'] = $this->fomatdate($budget['FROMDATE']);//
				$project['TODATE'] = $this->fomatdate($budget['TODATE']);//
				$project['BUDGETTYPE'] = '('.$type['TYPENAME'].')';
				$uesr= D('admin_user')->where("LOAN_USERID='".$project['MANAGER_ID'] ."'")->find();
				$project['MANAGER_ID'] =$uesr['LOAN_USERNAME'];



			}elseif($_REQUEST['gcaseid']){//�ۺ�
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
				$project['FEE'] = $fee;//�����շѱ�׼
				$project['SUMPROFIT'] = $sumprofit;//Ԥ��������
				$project['ADBUDGET'] = $ADBUDGET;//���Ԥ�㣨�ۺ�ۣ�
				$project['ADINDEXBUDGET'] = $ADINDEXBUDGET;//�ز���ҳ���͹�棨�ۺ�
				$project['AVERAGESETS'] = $AVERAGESETS;//�¾�ȥ������
				$project['FIRSTSETS'] = $FIRSTSETS;//�״�ȥ������
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
			 preg_match('/(?<d>\d{2})-(?<m>\d{1,2})��\s*-(?<y>\d{2})/',$s,$m);
			 return date('Y-m-d',strtotime($m['y'].'-'.$m['m'].'-'.$m['d']));
		 }
		 function getTotalCost(){//�ܼƷ���
			
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			
			$res = D("budgetfee")->where($map)->sum('amount');
			$res = $res ? $res :0;
			//var_dump(D("budgetfee")->getLastSql());
			return $res;
		}
		function getFeeInfo($InputName){//����˵��
			
			$fee = D("l_fee")->where("INPUTNAME='$InputName'")->find();//echo $this->param['budgetid'].'--';
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			$map['FEEID'] = $fee['ID'];
			$res = D("budgetfee")->where($map)->find();
			 
			//var_dump(D("budgetfee")->getLastSql());
			return $res['REMARK'];
		}
		function getdata($InputName){//����
			$fee = D("l_fee")->where("INPUTNAME='$InputName'")->find();//echo $this->param['budgetid'].'--';
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			$map['FEEID'] = $fee['ID'];
			$res = D("budgetfee")->where($map)->sum('amount');
			$res = $res ? $res :0;
			//var_dump(D("budgetfee")->getLastSql());
			return $res;
		}
		function getsaledata($name,$field='SETS'){//Ŀ��ֽ�
			$salem = D("L_salemethod")->where("NAME='$name'")->find();
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			$map['SALEMETHODID'] = $salem['ID'];
			$res = D("budgetsale")->where($map)->sum("$field");
			$res = $res ? $res :0;
			//var_dump(D("budgetfee")->getLastSql());
			return $res;
		}
		function getsaledataTotal( $field='SETS'){//Ŀ��ֽ��ܼ�
			 
			$map['BUDGETID'] = array('IN',$this->param['budgetid']);
			 
			$res = D("budgetsale")->where($map)->sum("$field");
			$res = $res ? $res :0;
			//var_dump(D("budgetfee")->getLastSql());
			return $res;
		}
		 #����Ŀ���
		function get_prj_tbl($row){
			
			$cfg['sales_target'] = array(
				'1' => "�н�",
				'2' => "����",
				'3' => "����Ӫ��",
				'4' => "�ؿ�",
				'5' => "����",
				'6' => "��Ȼ����",
			);
			$db_arr = array(
				'CONTRACTNO'=>'��ͬ���',
				'MANAGER_ID'=>'��Ŀ����',
				'PRJNAME'=>'��Ŀ����',
				'STANDARDNAME'=>'����¥��',
				'PROPERTYTYPE'=>'��ҵ���',
				'STANDARDID'=>'����ID',
				'DEVELOPER'=>'������ҵ',
				'ADDRESS'=>'��Ŀ��ַ',
				'SOURCE'=>'��������Դ',
				'DISCOUNT'=>'�������Ż�',
				'PERMIT'=>'��Ŀ�������֤',
				'CAPITALPOOL'=>'�ʽ��', //isfundpool
				'ADVANTAGE'=>'��Ŀ����Դ����',
				'INFERIORITY'=>'��Ŀ����Դ����',
				'PROFIT'=>'��������',
				'REMARK'=>'��ע',
				'AVERAGESETS'=>'ǰһ�����¾�ȥ������',
				'FIRSTSETS'=>'���һ�ο��̣����ƣ�ȥ������',
			);

			$return_tr = array(
				'agency'=>'<td rowspan="46">�����������</td><td>���ͷ����</td><td colspan="2">�н��</td>',
				'sms'=>'<td rowspan="2">����Ӫ����</td><td colspan="2">���ŷ�</td>',
				'phone'=>'<td colspan="2">�绰��</td>',
				'market'=>'<td rowspan="9">������</td><td rowspan="3">���ط�</td><td>����/�̳�</td>',
				'into_village'=>'<td>��С��</td>',
				'into_office'=>'<td>д��¥</td>',
				'bus'=>'<td rowspan="2">�⳵��(����)</td><td>��ͳ�</td>',
				'taxi'=>'<td>���⳵</td>',
				'transportation'=>'<td colspan="2">�����(����)</td>',
				'seo'=>'<td>�ƹ��</td><td>SEO/SEM�ƹ�</td>',
				'field_warmup'=>'<td colspan="2">����ů����</td>',
				'netfriend_foot'=>'<td colspan="2">����ʳƷ��</td>',
				'employees'=>'<td rowspan="2">��Ա����</td><td colspan="2">��˾Ա��</td>',
				'parttime_staff'=>'<td colspan="2">��ְ��Ա</td>',
				'business_benefits'=>'<td rowspan="4">ҵ���</td><td colspan="2">ҵ�����</td>',
				'business_other'=>'<td colspan="2">��������</td>',
				'actual_entertainment'=>'<td colspan="2">ʵ��Ӧ��</td>',
				'travel_expenses'=>'<td colspan="2">���÷�</td>',
				'propaganda'=>'<td rowspan="4">������</td><td colspan="2">����Ʒ</td>',
				'exhibition'=>'<td colspan="2">��չ��</td>',
				'onesheet'=>'<td colspan="2">��ҳ</td>',
				'xdisplay'=>'<td colspan="2">Xչ��</td>',
				'major_suit'=>'<td rowspan="5">�ⲿ����</td><td colspan="2">����</td>',
				'led'=>'<td colspan="2">LED</td>',
				'bus_sub'=>'<td colspan="2">����/����</td>',
				'radio'=>'<td colspan="2">��̨</td>',
				'newspaper'=>'<td colspan="2">��ֽ/��־</td>',
				'net_friend'=>'<td rowspan="4">������</td><td colspan="2">����</td>',
				'home_buyers'=>'<td colspan="2">��ҵ����</td>',
				'customer'=>'<td colspan="2">�ͻ�</td>',
				'publicity_other'=>'<td colspan="2">����</td>',
				'third_party'=>'<td colspan="3">֧������������</td>',
				'profit_sharing'=>'<td>��Ŀ�ֳ�</td><td colspan="2">����ֳ�</td>',
				'old_new'=>'<td rowspan="4">������</td><td colspan="2">�ϴ���</td>',
				'new_new'=>'<td colspan="2">�´���</td>',
				'intermediary_watch'=>'<td colspan="2">�н����</td>',
				'channel_watch'=>'<td colspan="2">��������</td>',
				'transaction_rewards'=>'<td>�ɽ���</td><td colspan="2">�ɽ�����</td>',
				'internal_commission'=>'<td>�ڲ�Ӷ��</td><td colspan="2">�ڲ����</td>',
				'external_rewards'=>'<td>�ⲿӶ��</td><td colspan="2">�ⲿ����</td>',
				'pos'=>'<td>POS������</td><td colspan="2">POS������</td>',
				'taxes'=>'<td colspan="3">˰��(֧�����������õ�10%)</td>',
				'other'=>'<td colspan="3">����</td>',
			);

			$html = '';
			$html = $html . "<table width='90%' cellspacing='0' cellpadding='10' border='1' style='border-collapse: collapse;' align='center'>";

			#����
			$html = $html . "<tr><td colspan='12' align='center'><h1 style='font-weight:600;font-size:16px;' >\"{$row['PRJNAME']}\"����Ԥ��� {$row['BUDGETTYPE']}</h1></td></tr>";

			$html = $html . "<tr><td colspan='2'>ִ������</td><td colspan='10'>".date('Y-m-d',strtotime($row['FROMDATE'])).' - '.date('Y-m-d',strtotime($row['TODATE']))."</td></tr>";

			# ��������
			$i = 0;
			foreach($db_arr as $k=>$v)
			{
				if($i%2==0)
					if($k=='PERMIT')
					{
						$temp_sp = $row[$k]?"��":"��";
					 
						$text = $temp_sp;
						$html = $html . "<tr><td colspan='2'>{$v}</td><td colspan='3'>" . $text . "</td>";	
					}
					else{
						$html = $html . "<tr><td colspan='2'>{$v}</td><td colspan='3'>" . $row[$k]. "</td>";	
					}	
				else
					if($k=='CAPITALPOOL'){
						$temp = $row[$k]==-1? "��" . " ����:{$row['RATIOPOOL']}%":"��";
						 
						//$text = $row_old[$k]?get_contrast($temp,$old_temp):$temp;
						$html = $html . "<td colspan='2'>" . $v . "</td><td colspan='5'>" . $temp . "</td></tr>";				
					}
					else{
						$html = $html . "<td colspan='2'>{$v}</td><td colspan='5'>" . $row[$k] . "</td></tr>";
					}
				$i ++;
			}
			$html = $html . "<tr><td colspan='4'>" . $row['PRJNAME'] . "</td><td>��Ԫ��</td><td>�����շѱ�׼��Ԫ/�ף�</td><td rowspan='2'>Ŀ��ֽ�</td><td>Ԥ���ɽ�����</td><td>Ԥ��������</td><td rowspan='8'>����ռ��</td><td rowspan='8'>����˵��</td><td rowspan='8'>��ע</td></tr>";

			 
			 
			$html = $html . "<tr><td colspan='4' rowspan='7'>Ԥ������</td><td rowspan='7'>"  . $row['SUMPROFIT'].  "</td><td rowspan='7'>" . $row['FEE']. "</td><td>" .$this->getsaledataTotal('SETS'). "</td><td>" . $this->getsaledataTotal('CUSTOMERS'). "</td></tr>";

			#����Ŀ��
			//$sales_targets = unserialize($row['sales_target']);
			 
			foreach($cfg['sales_target'] as $k=>$v){
				$html = $html . "<tr><td>$v</td><td>" . $this->getsaledata($v,'SETS')   . "</td><td>" .   $this->getsaledata($v,'CUSTOMERS') . "</td></tr>";
			}

			#���·���
			//$offline_cost = unserialize($row['offline_cost']);
			 
			foreach($return_tr as $k=>$v)
			{
				$html = $html . "<tr>";
				$html = $html . $v . "<td>" . $this->getdata($k) . "</td><td>--</td><td>--</td><td></td><td></td><td>" .round($this->getdata($k)/$this->param['totalCost'],4) * 100 . "% </td><td>" . $this->getFeeInfo($k)."  </td><td>--</td></tr>";
			}

			$html = $html . "<tr><td colspan='3'>���ֳɱ�</td><td>" .$this->param['totalCost'] . "</td><td>--</td><td>--</td><td></td><td></td><td>--</td><td>--</td><td>--</td></tr>";
			$html = $html . "<tr><td colspan='3'>��������</td><td>" .($row['SUMPROFIT']- $this->param['totalCost']) . "</td><td>--</td><td>--</td><td></td><td></td><td>--</td><td>--</td><td>--</td></tr>";
			$html = $html . "<tr><td colspan='3'>����������</td><td>" . (round(($row['SUMPROFIT']- $this->param['totalCost'])/$row['SUMPROFIT'],4)*100)."%" . "</td><td>--</td><td>--</td><td></td><td></td><td>--</td><td>" . ' --' . "</td><td>--</td></tr>";

			#���Ϸ���
			//$online_cost = unserialize($row['online_cost']);
			 
			$html = $html . "<tr><td rowspan='3'>�����������</td><td colspan='3'>���Ԥ�㣨�ۺ�ۣ�</td><td>" . $row['ADBUDGET']. "</td><td colspan='3'>�ز���ҳ���͹�棨�ۺ�</td><td colspan='3'>" . $row['ADINDEXBUDGET']. "</td><td>" . ''. "</td></tr>";
			$html = $html . "<tr><td colspan='3'>�۳�����+����֧������</td><td>" . ($row['SUMPROFIT']- $this->param['totalCost']-$row['ADBUDGET']) . "</td><td colspan='6'>--</td><td></td></tr>";
			$html = $html . "<tr><td colspan='3'>�۳�����+����֧��������</td><td>" .(round( ($row['SUMPROFIT']- $this->param['totalCost']-$row['ADBUDGET'])/$row['SUMPROFIT'],4)*100) . "</td><td colspan='6'>--</td><td></td></tr>";
			$html = $html . "</table>";
			$html .= "<div class='lxts'>������ʾ</div><div class='tscontent'>  <div class='tishi' id='lirundp' evs='".(round(($row['SUMPROFIT']- $this->param['totalCost'])/$row['SUMPROFIT'],4)*100)."'>  -</div>";
			$html .= "<div class='tishi' id='shouruyq' evs='".($row['SUMPROFIT']- $this->param['totalCost'])."'>  -</div>";
			$html .= "<div class='tishi' id='guanggaoyq' evs='".$row['ADBUDGET']."'>  -</div> </div>";

			return $html;
		}
        
        
        /**
        +----------------------------------------------------------
        * ajax��ȡ¥���б���Ϣ
        +----------------------------------------------------------
        * @access public
        +----------------------------------------------------------
        * @param string $keyword �ؼ���
        * @param int $city_id ���б��
        +----------------------------------------------------------
        * @return json ¥������
        +----------------------------------------------------------
        */
        public function ajax_get_project_list()
        {   
            //���ݹؼ��ʻ�ȡ¥����Ϣ
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
                $ajax_data[0]['label'] =  g2u('����¥����Ϣ');  
            }
            
            echo json_encode($ajax_data);
            exit;
        }
        
        
        /**
         +----------------------------------------------------------
         * ajax��ȡ�շѱ�׼
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         * @param int $prj_id ¥��ID
         * @param int $scale_type �շ�����
         * @param int $status ���ü�¼״̬
         +----------------------------------------------------------
         * @return json ��������
         +----------------------------------------------------------
         */
        public function ajax_get_feescale()
        {	
        	//��Ŀ���
        	$prj_id =  intval($this->_request('prj_id'));
        	
        	//��������
            $scaletype = $this->_request('scale_type');
        	$scaletype = !empty($scaletype) ? intval($scaletype) : '';
        	
        	//����״̬
        	$status = $this->_request('status');
        	$status = !empty($status) ? intval($status) : '';
        	
        	//ҵ������
        	$case_type = $this->_request('case_type');
        	$case_type = !empty($case_type) ? strip_tags($case_type) : '';
        	
        	$case_model = D('ProjectCase');
        	
        	$feescale = array();
        	if($prj_id > 0 && !empty($case_type) )
        	{	
        		
        		//����ҵ�����ͻ�ȡ������Ϣ
	        	$caseinfo = array();
	        	$caseinfo = $case_model->get_info_by_pid($prj_id, $case_type);
	        	
	        	//���ݰ�����š��������͡�״̬��ȡ���ñ�׼��Ϣ
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
         * ajax��ȡ��Ŀ¥����Ϣ
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         * @param int $prj_id ¥��ID
         +----------------------------------------------------------
         * @return json ��Ŀ¥����Ϣ
         +----------------------------------------------------------
         */
        public function ajax_get_houseinfo_by_pid()
        {   
            $ajax_data = array();
            $house_model = M('erp_house');
            $project_id = intval($_REQUEST['project_id']);
            $ajax_data = $house_model->where("PROJECT_ID = '".$project_id."'")->find();
            
            //��Ŀ¥����Ϣ��Ϊ����Ҫת�����ı���
            $ajax_data = !empty($ajax_data) ? g2u($ajax_data) : $ajax_data;
            
            echo json_encode($ajax_data);
        	exit;
        }

		/**
         +----------------------------------------------------------
         * ajax��ȡ��ĿĿ��ֽ����۷�ʽ
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         * @param int $prj_id ¥��ID
         +----------------------------------------------------------
         * @return json ��Ŀ¥����Ϣ
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
            
            //��Ŀ¥����Ϣ��Ϊ����Ҫת�����ı���
            $ajax_data = !empty($ajax_data) ? g2u($ajax_data) : $ajax_data;
            
            echo json_encode($ajax_data);
            exit;
        }
	}
?>