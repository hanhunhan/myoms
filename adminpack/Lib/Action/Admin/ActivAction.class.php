<?php
	class ActivAction extends ExtendAction{
		 /*�ϲ���ǰģ���URL����*/
		private $_merge_url_param = array();
		private $model;
		private $isedit;

        //���캯��
		public function __construct() 
		{
            $this->model = new Model();
			parent::__construct();
			// Ȩ��ӳ���
			$this->authorityMap = array(
			);
			
			//TAB URL����
			$this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
			
			!empty($_GET['RECORDID']) ? $this->_merge_url_param['activId'] = $_GET['RECORDID'] : '';
			!empty($_GET['ACTIVID']) ? $this->_merge_url_param['ACTIVID'] = $_GET['ACTIVID'] : '';
			!empty($_GET['ACTIVID']) ? $this->_merge_url_param['activId'] = $_GET['ACTIVID'] : '';
			!empty($_GET['activId']) ? $this->_merge_url_param['activId'] = $_GET['activId'] : '';
			//2 �༭ 1 �鿴 3 ����
			!empty($_GET['active']) ? $this->_merge_url_param['active'] = $_GET['active'] : '';
			!empty($_GET['CHANGE']) ? $this->_merge_url_param['CHANGE'] = $_GET['CHANGE'] : '';
			!empty($_GET['CID']) ? $this->_merge_url_param['CID'] = $_GET['CID'] : '';
			!empty($_GET['CASEID']) ? $this->_merge_url_param['prjid'] = $_GET['CASEID'] : '';
			!empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
			!empty($_GET['RECORDID']) ? $this->_merge_url_param['CID'] = $_GET['RECORDID'] : '';
			!empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
			!empty($_GET['type']) ? $this->_merge_url_param['type'] = $_GET['type'] : '';
			!empty($_GET['flowType']) ? $this->_merge_url_param['flowType'] = $_GET['flowType'] : '';
			!empty($_GET['tabNum']) ? $this->_merge_url_param['tabNum'] = $_GET['tabNum'] : '';
			!empty($_GET['showOpinion']) ? $this->_merge_url_param['showOpinion'] = $_GET['showOpinion'] : '';
			!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
			$this->isedit = false;
			if($_REQUEST['flowId']){
				if( judgeFlowEdit($_REQUEST['flowId'],$_SESSION['uinfo']['uid']) ){//�ж��Ƿ�ص������� 
					$this->isedit = true;
				}
			}
		}
    
		// �����
		 function activPro(){
             $prjid = $_REQUEST['prjid'];
             if ($_REQUEST['act'] == 'checkAmount') {  // ��������Ƿ���ȷ
                 $atotal = M('Erp_actibudgetfee')->where(" ACTIVITIES_ID ='" . $_REQUEST['activitiesId'] . "'")->sum('AMOUNT');

                 if ($atotal > $this->_post('param')) {
                     $result['status'] = 'n';
                     $sy = $btotal - $atotal;
                     $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '�ѵ��ڻԤ���ܶ���лԤ���ܶ�Ϊ' . $atotal);
                 } else {
                     $result['status'] = 'y';
                     $result['info'] = '';

                 }
                 echo json_encode($result);
                 exit();
             }

             if ($_REQUEST['active'] && empty($_REQUEST['activId'])) {
                 $_REQUEST['activId'] = $_REQUEST['paramId'];
             }

             Vendor('Oms.Form');
             $form = new Form();

             $project = D('Erp_project')->where("ID=$prjid")->find();
             $tuser = M('Erp_users')->where('ID=' . $project['CUSER'])->find();
             $form->initForminfo(134);
             $form->FORMTYPE = 'FORM';

             //ҵ������
             if ($_REQUEST['CHANGE'] == '-1') {  // ����
                 // ���ú�ͬ��Ϊֻ������
                 $form->setMyField('CONTRACT_NO', 'READONLY', -1);

                 if ($_REQUEST['activId'] and $_REQUEST['active'] == 2)//�����Ŀ�»�༭
                 {
                     $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 and (b.FSTATUS=2 or b.FSTATUS=4)) where 1=1 ');
                 } else {
                     $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 ) where 1=1 ');
                 }

                 if ($_GET['activId']) $prj_active = 1;
             } else {
                 if ($_REQUEST['active'] == 3 or $_REQUEST['active'] == 2)//��Ŀ�»����.�༭
                 {
                     $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 and (b.FSTATUS=2 or b.FSTATUS=4)) where 1=1 ');
                     $form->setMyField('CONTRACT_NO', 'FORMVISIBLE', 0);  // ��Ŀ�»���غ�ͬ���ֶ�
				 } else {
                     $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 ) where 1=1 ');
                 }

                 if ($_GET['active']) $prj_active = 1;
             }
//
//             //�ж��Ƕ����or ��Ŀ�»
//             if ($_REQUEST['CHANGE'] == '-1') {
//                 if ($_GET['activId']) $prj_active = 1;
//             } else {
//                 if ($_GET['active']) $prj_active = 1;
//             }

             if ($_GET['paramId'] && !$_GET['active']) {//// �����������ҵ������
                 $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 ) where 1=1 ');
                 $form->setMyFieldVal('BUSINESSCLASS_ID', 4, true);
             }

             if ($_REQUEST['active'] == 3) {
                 $form->setMyFieldVal('PROJECT_ID', $prjid, true);
             }

             if ($_REQUEST['CHANGE'] == '-1') {
                 $CID = $_REQUEST['CID'] ? $_REQUEST['CID'] : $_REQUEST['ACTIVID'];

                 $form->changeRecord = true;
                 $form->changeRecordVersionId = $CID;
                 //$form->PKVALUE= $_REQUEST['activId'];
                 if ($_REQUEST['activId']) {
                     $form->PKVALUE = $_REQUEST['activId'];
                 } else {
                     $case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID=' . $prjid)->find();
                     $form->setMyFieldVal('CASE_ID', $case['ID'], true);
                     $one = M('Erp_activities')->field('ID,PRINCOME')->where('CASE_ID=' . $case['ID'])->find();
                     $form->PKVALUE = $one['ID'];
                     $form->setMyFieldVal('BUSINESSCLASS_ID', 4, true);
                 }
                 if ($_REQUEST['active'] == '1' && $this->isedit == false) {
                     $form->FormeditType = 2;
                 } else {
                     $dept = M('Erp_dept')->where("ID=" . $tuser['DEPTID'])->find();
                     $form->setMyField('DEPT_ID', 'LISTSQL', 'select ID,DEPTNAME from ERP_DEPT where ISVALID=-1 and PARENTID=' . $dept['PARENTID']);
                     //$form->setMyFieldVal('APPLICANT',$_SESSION['uinfo']['uid'],true);

                 }
                 if ($_REQUEST['active']) {
                     $form->setMyField('BUSINESSCLASS_ID', 'READONLY', -1, true);
                 }
             } else {
                 if ($_REQUEST['active']) {//��Ŀ�»

                     if ($_REQUEST['active'] == '1') {
                         $form->PKVALUE = $_REQUEST['activId'];
                         if ($this->isedit == false) $form->FormeditType = 2;
                     } else {
                         if ($_REQUEST['active'] == '2') {
                             $form->PKVALUE = $_REQUEST['activId'];
                             $form->setMyFieldVal('PROJECT_ID', $prjid, true);
                         } else { 
                             $form->setMyFieldVal('APPLICANT', $_SESSION['uinfo']['uid'], true);
                         }
                         $dept = M('Erp_dept')->where("ID=" . $tuser['DEPTID'])->find();
                         $form->setMyField('DEPT_ID', 'LISTSQL', 'select ID,DEPTNAME from ERP_DEPT where ISVALID=-1 and PARENTID=' . $dept['PARENTID']);

                         //$form->setMyFieldVal('APTIME',date("Y-m-d H:m:s"),true);
                     }
                 } else {//�����

                     $case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID=' . $prjid)->find();

                     $form->setMyFieldVal('CASE_ID', $case['ID'], true);
                     $one = M('Erp_activities')->field('ID,PRINCOME')->where('CASE_ID=' . $case['ID'])->find();

                     if ($one) {
                         $form->PKVALUE = $one['ID'];
                     } else {
                         $form->setMyFieldVal('APPLICANT', $project['CUSER'], true);
                     }
                     $form->setMyFieldVal('BUSINESSCLASS_ID', 4, true);

                     if ($form->FormeditType == 1) {//�����༭״̬
                         $dept = M('Erp_dept')->where("ID=" . $tuser['DEPTID'])->find();
                         $form->setMyField('DEPT_ID', 'LISTSQL', 'select ID,DEPTNAME from ERP_DEPT where ISVALID=-1 and PARENTID=' . $dept['PARENTID']);

                     }

                     if ($project['PSTATUS'] > 2 && $this->isedit == false) {
                         $form->FormeditType = 2;
                         $form->setMyField('DEPT_ID', 'LISTSQL', 'select ID,DEPTNAME from ERP_DEPT where ISVALID=-1');
                     }
                 }
             }
             $activId = $one['ID'] ? $one['ID'] : $_REQUEST['activId'];
             if ($_REQUEST['faction'] == 'saveFormData') {  // ��������
                 $BUSINESSCLASS_ID = $_REQUEST['BUSINESSCLASS_ID'];

                 // �жϺ�ͬ���Ƿ����
                 $contractNoOld = trim($_REQUEST['CONTRACT_NO_OLD']);
                 $contractNoNew = trim($_REQUEST['CONTRACT_NO']);
                 $isExistContract = false; // Ĭ�����ݿ��в����ڸú�ͬ��
                 // �����ͬ��Ϊ�գ����ʾ���ݿ���δ�洢��ͬ��
                 if ($contractNoOld !== '') {
                     if ($contractNoNew && $contractNoNew != $contractNoOld) {
                         $isExistContract = D('Contract')->isExistContract($contractNoNew, 4, $this->channelid);
                     }

                 } else {
                    if ($contractNoNew) {
                        $isExistContract = D('Contract')->isExistContract($contractNoNew, 4, $this->channelid);
                    }
                 }

                 if ($isExistContract) {
                     echo json_encode(array(
                         'status' => 'error',
                         'msg' => g2u('ϵͳ���Ѵ��ں�ͬ��' . $contractNoNew . ', ���޸ĺ�ͬ�ţ�')
                     ));
                     exit;
                 }

                 // ����Ŀ�б����ͬ��
                 if ($contractNoOld !== $contractNoNew) {
                     $updateProject = D('Project')->where("ID = {$prjid}")->save(array(
                         'CONTRACT' => $contractNoNew
                     ));
                     if ($updateProject === false) {
                         echo json_encode(array(
                             'status' => 'error',
                             'msg' => g2u('�������ڲ�����')
                         ));
                         exit;
                     }
                 }

                 $case = M('Erp_case')->where("SCALETYPE = $BUSINESSCLASS_ID and PROJECT_ID=$prjid")->find();
                 if ($BUSINESSCLASS_ID == 4) {
                     $case_id = $case['ID'];
                     $form->setMyFieldVal('CASE_ID', $case_id, true);
                 } else {
                     // ��Ŀ�»
                     if ($this->isShowOptionBtn($case['ID']) == self::HIDE_OPTION_BTN) {
                         js_alert("��Ŀ��������ֹ�����״̬�����������Ŀ�»��", U("Activ/activPro",$this->_merge_url_param), 1);
                         exit;
                     } else {
                         if (empty($_GET['activId'])) {
                             $cdata['CTIME'] = date('Y-m-d H:i:s', time());
                             $cdata['CUSER'] = $_SESSION['uinfo']['uid'];
                             $cdata['SCALETYPE'] = 7;
                             $cdata['PARENTID'] = $case['ID'];
                             $cdata['PROJECT_ID'] = $prjid;
                             $case_id = M('Erp_case')->add($cdata);
                             $form->setMyFieldVal('CASE_ID', $case_id, true);
                         } else {
                             $activities = M('Erp_activities')->where('ID=' . $_GET['activId'])->find();
                             $form->setMyFieldVal('CASE_ID', $activities['CASE_ID'], true);
                         }
                     }
                 }

				 $title = $_REQUEST['TITLE'] ? u2g($_REQUEST['TITLE']):"";

					 $prjId = $_REQUEST['prjid'];
					 $sql = "";
					 $sql = "select * from erp_project where id=" . $prjId;
					 $project_arr = D()->query($sql);
					 foreach ($project_arr as $projects) {
						 if ($projects['ACSTATUS'] >= 1) {
							 //��ɾ��������״̬����ֹ����Ŀ����Ŀ���ƿ����ظ�
							 $bsql = "select PROJECTNAME from erp_project where ACSTATUS >=1 and PSTATUS !=5 and STATUS !=2 and CITY_ID = ".$this->channelid. " and ID !=".$prjId;
							 $ds_projects = D()->query($bsql);
							 foreach ($ds_projects as $ds_project) {
								 if ($title == $ds_project['PROJECTNAME']) {
									 $result['status'] = 0;
									 $result['msg'] = g2u('��ҵ�����Ѿ��д���Ŀ���ƣ������');
									 echo json_encode($result);
									 exit;
								 }
							 }
						 }
					 }

                 if ($project['ACSTATUS'] == 1) { //ͬ����Ŀ��Ϣ
                     $postContractNum = $this->_post('CONTRACT_NUM');
                     $temp['CONTRACT'] = empty($postContractNum) ? $contractNoNew : $postContractNum;
                     $_POST['PRO_NAME'] = htmlspecialchars($_POST['PRO_NAME']);
                     $temp['PROJECTNAME'] = iconv('UTF-8', 'GBK', $_POST['TITLE']);
                     $temp['COMPANY'] = iconv('UTF-8', 'GBK', $_POST['DEV_ENT']);
                     D('Erp_project')->where("ID=$prjid")->save($temp);
                 }

             }
             $this->assign('ACTIVID', $form->PKVALUE);

             if ($_REQUEST['active']) {
                 $this->_merge_url_param['active'] = $_REQUEST['active'] == 3 ? 2 : $_REQUEST['active'];
                 $this->_merge_url_param['activId'] = $_REQUEST['activId'];
             }

             $form->FORMFORWARD = U('Activ/activPro', $this->_merge_url_param);  //�������ת*/

//              $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->purchaseManageOptions, $_REQUEST['CASE_TYPE']);
             $form = $form->getResult();

             $this->assign('form', $form);
             $this->assign('prj_active', $prj_active);
             $this->assign('paramUrl', $this->_merge_url_param);
             $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
             $this->display('activPro');
		 }
         
         
		//��Ŀ�»
		function activProX()
        { 
			$prjid = $_REQUEST['prjid'];
			$this->project_case_auth($prjid );//��Ŀҵ��Ȩ���ж�
			Vendor('Oms.Form');			
			$form = new Form();

			//$children = array(
                 //array("�����",U("Activ/activSummery",$this->_merge_url_param)),
                // array("��������",U("Activ/reimList",$this->_merge_url_param)),
                 //);
			$form->initForminfo(134);//->setChildren($children);
				$caselist = M('Erp_case')->field('ID')->where('SCALETYPE=7 and   PROJECT_ID='.$prjid)->select();
				foreach($caselist as $v){
					$temp[] = $v['ID'];
				} 
				$ids = implode(',',$temp);
				$form->where("CASE_ID in ( $ids )");
				$form->ADDABLE = 0;
				if(in_array(471,$this->getUserAuthorities()))
					$form->GCBTN =  '<a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="submitActiv();" >�ύ</a>';
				$form->GCBTN .= '<a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="changeActiv();" >����</a> <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="execActiv();" >ִ��</a>';
				$form->CZBTN = array(
					'%STATUS% == 0'=>'<a class="contrtable-link btn btn-primary btn-xs" onclick="editActiv(this);" href="javascript:;"><i class="glyphicon glyphicon-edit"></i></a><a class="contrtable-link btn btn-danger btn-xs" onclick="delActiv(this);" href="javascript:;"><i class="glyphicon glyphicon-trash"></i></a>',
					'%STATUS% == 1'=>'<a class="contrtable-link btn btn-success btn-xs" onclick="viewActiv(this);" href="javascript:;"><i class="glyphicon glyphicon-eye-open"></i></a>','%STATUS% == 2'=>'<a class="contrtable-link btn btn-success btn-xs" onclick="viewActiv(this);" href="javascript:;">�鿴</a>',
					'%STATUS% == 3'=>'<a class="contrtable-link btn btn-success btn-xs" onclick="viewActiv(this);" href="javascript:;">�鿴</a>'
				);
			$form->setMyField("SUMMERY_MONEY", "GRIDVISIBLE", "-1");
			$formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
            // $this->assign('isShowOptionBtn', $this->isProjectContextMenuRunnable($prjid, 'benefits'));
			$this->assign('form',$formHtml);
			$this->assign('paramUrl',$this->_merge_url_param);
			$this->assign('prjid',$prjid);
			$this->display('activProX');
		 }
         
         
		 //�Ԥ��
		 function activBudget(){
			if($_REQUEST['act']=='checkAmount'){
				if($_REQUEST['tid']) $sqlstr = ' and ID<>'.$_REQUEST['tid'];
				$activi = M('Erp_activities')->where("ID=".$_REQUEST['activitiesId'])->find();

				//$btotal = $activi['BUDGET'];
				$btotal = intval($activi['MYFEE']) + intval($activi['BUSINESSFEE']); 

				$atotal = M('Erp_actibudgetfee')->where(" ACTIVITIES_ID ='".$_REQUEST['activitiesId']."' $sqlstr ")->sum('AMOUNT');  

				 
				if($btotal-$atotal-$this->_post('param') < 0){
				  $result['status'] = 'n';
				  $sy = $btotal-$atotal;
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '�����Ԥ���ܶ��Ԥ��ֻʣ��'.$sy) ;
				} else{
				  $result['status'] = 'y';
				  $result['info'] = '';
				   
				}
				 echo json_encode($result); exit();
			}elseif($_REQUEST['act']=='getproportion'){
				if($_REQUEST['tid']) $sqlstr = ' and ID<>'.$_REQUEST['tid'];
				if($_REQUEST['CHANGE']!='-1')  $sqlstr .= ' and ISVALID=-1' ;
				$atotal = M('Erp_actibudgetfee')->where(" ACTIVITIES_ID ='".$_REQUEST['activitiesId']."'  $sqlstr ")->sum('AMOUNT');
				if($atotal)
				$result['proportion'] = round($_REQUEST['amount']/($atotal+$_REQUEST['amount']) *100,2);
				else $result['proportion']=100;
				 echo json_encode($result); exit();
			}
			 
			Vendor('Oms.Form');			
			$form = new Form();
			$prjid = $_REQUEST['prjid'];
			$project = D('Erp_project')->where("ID=$prjid")->find();
			$form->initForminfo(141);
			
			if($_REQUEST['CHANGE'] == '-1'){
				$CID = $_REQUEST['CID']?$_REQUEST['CID']:$_REQUEST['RECORDID'];
				$form->changeRecord=true;
				$form->changeRecordVersionId= $CID;
                $form->setMyFieldVal('CID',$CID ,true);  // ����汾��
                $this->assign('CID', $CID);  // ��Ŀ�����
				if($_REQUEST['activId']){
					if($_REQUEST['active'] == '1' && $this->isedit==false){
						$form->CZBTN=' ';
						//$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">����</a>'.'<a id="j-search" class="j-showalert" href="javascript:;">����</a>'.'<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">ˢ��</a>';
						$form->ADDABLE = 0;
					}
                    $case_id = D('Erp_activities')->where("ID = {$_REQUEST['activId']}")->getField('CASE_ID');
                    if ($case_id === false) {
                        $case_id = null;
                    }
					$form->setMyFieldVal('ACTIVITIES_ID',$_REQUEST['activId'] ,true);
					$form->setMyFieldVal('ISVALID',0 ,true);
					$form->setMyFieldVal('CASE_ID',$case_id ,true);
					$form->where(sprintf("ACTIVITIES_ID = %d AND (CID = %d OR ISVALID = %d)", $_REQUEST['activId'], $CID, -1));
//					$form->where("ACTIVITIES_ID=".$_REQUEST['activId']);

				}else{
					$case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID='.$_REQUEST['prjid'])->find();
					
					$activities = M('Erp_activities')->field('ID')->where('CASE_ID='.$case['ID'])->find();
					 
					if($_REQUEST['active'] == '1' && $this->isedit==false){
						$form->CZBTN=' ';
						//$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">����</a>'.'<a id="j-search" class="j-showalert" href="javascript:;">����</a>'.'<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">ˢ��</a>';
						$form->ADDABLE = 0;
					}

                    if ($_REQUEST['active'] == 1) {
                        // $where = "ACTIVITIES_ID = {$activities['ID']} AND ID IN (SELECT BID FROM ERP_CHANGELOG WHERE CID = {$CID})";
                        $where = "ACTIVITIES_ID = {$activities['ID']}";
                    } else if ($_REQUEST['active'] == 2) {
                        $where = "ACTIVITIES_ID = {$activities['ID']}";
                    }
                    $where .= sprintf(" AND (CID = %d OR ISVALID = %d)", $CID, -1);  // todo
					$form->setMyFieldVal('ACTIVITIES_ID',$activities['ID'] ,true);
					$form->setMyFieldVal('ISVALID','0' ,true);
					$form->setMyFieldVal('CASE_ID',$case['ID'] ,true);  // ����CASE_ID

					$form->where($where);
				}
				
			}else{
				if($_REQUEST['active']){
					$form->where("ACTIVITIES_ID=".$_REQUEST['activId']." AND ISVALID = -1");

					if($_REQUEST['active'] == '1' && $this->isedit==false){
						$form->CZBTN=' ';
						//$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">����</a>'.'<a id="j-search" class="j-showalert" href="javascript:;">����</a>'.'<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">ˢ��</a>';
						$form->ADDABLE = 0;
					}
					if($_REQUEST['activId']){
						$form->setMyFieldVal('ACTIVITIES_ID',$_REQUEST['activId'] ,true);
						$activs = M('Erp_activities')->field('CASE_ID')->where('ID='.$_REQUEST['activId'])->find();
					$form->setMyFieldVal('CASE_ID',$activs['CASE_ID'] ,true);
					}
					$form->setMyFieldVal('ISVALID','-1' ,true);
					
				}else{
					$case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID='.$_REQUEST['prjid'])->find();
					$activities = M('Erp_activities')->field('ID')->where('CASE_ID='.$case['ID'])->find();
					$form->setMyFieldVal('ACTIVITIES_ID',$activities['ID'] ,true);
					$form->where("ACTIVITIES_ID=".$activities['ID']."AND ISVALID = -1");
					if($project['PSTATUS']>2 && $this->isedit==false){
						$form->CZBTN=' ';
						//$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">����</a>'.'<a id="j-search" class="j-showalert" href="javascript:;">����</a>'.'<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">ˢ��</a>';
						$form->ADDABLE = 0;
					}
					$form->setMyFieldVal('ISVALID','-1' ,true);
					$form->setMyFieldVal('CASE_ID',$case['ID'] ,true);

				}
			}  

             // �༭������״̬ʱԤ���ط��������ֶ�
             if ($_REQUEST['showForm'] == 1  || ($_REQUEST['showForm'] == 3 && empty($_REQUEST['faction']))) {
                 $feeOptions = addslashes(u2g($form->getSelectTreeOption('FEE_ID', '', -1)));
                 $this->assign('feeOptions', $feeOptions);
             }


			$form = $form->getResult();
			$this->assign('form',$form);
			$this->assign('CHANGE',$_REQUEST['CHANGE']);
			$this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
			$this->assign('paramUrl',$this->_merge_url_param);
			$this->assign('activitiesId', $_REQUEST['activId']?$_REQUEST['activId']:$activities['ID'] );
			$this->assign('atfeeID', $_REQUEST['ID'] );
			$this->display('activBudget');
		 }
		  //�Ԥ��
		 function activBudgetX(){
			if($_REQUEST['act']=='checkAmount'){
				$activi = M('Erp_activities')->where("ID=".$_REQUEST['activitiesId'])->find();
				$case = M('Erp_case')->where('ID='.$activi['CASE_ID'])->find(); 
				$model = new Model();
				$data = $model->query('select B.ID from ERP_CASE A left join ERP_PRJBUDGET   B on A.ID = B.CASE_ID where  A.PROJECT_ID='.$case['PROJECT_ID']);  
				foreach($data as $v){
					$temp[] = $v['ID'];
				}
				$temp=array_filter($temp);
				$ids = implode(',',$temp);
				$btotal = M('erp_budgetfee')->where("ISVALID=-1 and BUDGETID in($ids)")->sum('AMOUNT');

				$data = $model->query('select B.ID from ERP_CASE A left join ERP_ACTIVITIES   B on A.ID = B.CASE_ID where A.PROJECT_ID='.$case['PROJECT_ID']);
				$temp = array();
				foreach($data as $v){
					$temp[] = $v['ID'];
				}
				$temp=array_filter($temp);
				$ids = implode(',',$temp);
				if($_REQUEST['tid']) $sqlstr = ' and ID<>'.$_REQUEST['tid'];
				$atotal = M('erp_actibudgetfee')->where("ACTIVITIES_ID in ($ids) $sqlstr ")->sum('AMOUNT');
				if( $btotal-$atotal-$this->_post('param') < 0){
				  $result['status'] = 'n';
				  $sy = $btotal-$atotal;
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '��Ԥ�㳬������Ԥ��ֻʣ��'.$sy) ;
				} else{
				  $result['status'] = 'y';
				  $result['info'] = '';
				   
				}
				 echo json_encode($result); exit();
			}
			//$paramUrl = '&prjid='.$_REQUEST['prjid'].'&formtype='.$this->_get('formtype');
			Vendor('Oms.Form');			
			$form = new Form();
			 
			$form->initForminfo(141);
			//$form->CZBTN = array('%AMOUNT%==23331'=>'<a>����1</a>','%AMOUNT%==2333'=>'<a>����2</a><a>����3</a>');//��̬�жϲ�����ť
			//$case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID='.$_REQUEST['prjid'])->find();
			//$activities = M('Erp_activities')->field('ID')->where('CASE_ID='.$case['ID'])->find();
			//$form->setMyFieldVal('ACTIVITIES_ID',$activities['ID'] ,true);
			//$form->where("ACTIVITIES_ID=".$activities['ID']);
			$form = $form->getResult();
			$this->assign('activitiesId',$_REQUEST['parentchooseid']);
			$this->assign('form',$form);
			//$this->assign('paramUrl',$paramUrl);
			$this->assign('paramUrl',$this->_merge_url_param);
			$this->display('activBudgetX');
		 }

		function delActiv(){
			$activId = $_REQUEST['activId'];

            $budgetDel = M('Erp_actibudgetfee')->where("activities_id = $activId")->delete();
            $activDel = M('Erp_activities')->where("id = $activId")->delete();

			if($activDel) {
				$result['msg'] = g2u('�ɹ�');
				$result['status'] =1;
				
			}else{
				$result['msg'] = g2u('ʧ��');
				$result['status'] =0;
			}

			echo json_encode($result);exit;
			
		}
		  //����������������
		function opinionFlow()
    {  
        $prjId = $_REQUEST['prjid']?$_REQUEST['prjid']:$_REQUEST['CASEID'];
		$case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID='.$prjId)->find();
		$one = M('Erp_activities')->where('CASE_ID='.$case['ID'])->find();
		$ACTIVID = $one['ID']; 
		$fees = M('Erp_actibudgetfee')->where(" ISVALID=-1 and ACTIVITIES_ID=".$ACTIVID)->select();
		if(!$fees){
			js_alert('������дԤ�����');exit;
		}
		//�жϷ����Ƿ����
		$budgetFee = $one['BUDGET'];//Ԥ�����
		
		$pri_Budget_Fee = M('Erp_actibudgetfee')->where(" ACTIVITIES_ID ='".$ACTIVID."'")->sum('AMOUNT');//ʵ��Ԥ�����
		
		if($budgetFee >  $pri_Budget_Fee)
		{
			js_alert('Ԥ�������ʵ��Ԥ�������� '.($budgetFee-$pri_Budget_Fee)."Ԫ");exit;
		}
		if($budgetFee <  $pri_Budget_Fee)
		{
			js_alert('Ԥ�����С��ʵ��Ԥ�����');exit;
		}

		$type = $_REQUEST['flowType'] ? $_REQUEST['flowType']:'';
        
        //������ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;
        
        Vendor('Oms.workflow');			
        $workflow = new workflow();
       
        if($flowId > 0)
        {
            //�����Ѿ����ڵĹ�����
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);
			
            if($_REQUEST['savedata'])
            {
                //��һ��
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                }
                //ͨ����ť
                else if($_REQUEST['flowPass'])
                {
					
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {   
						/*$projectMod = D('Project');
						$projectMod->update_pass_status($prjId);*/
                        js_alert('ͬ��ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('ͬ��ʧ��');
                    }
                }
                //�����ť
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('���ʧ��');
                    }
                }
                //��ֹ��ť
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('δ�����ؾ���ɫ');exit;
					}

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {
                        $projectMod = D('Project');
						$projectMod->update_pass_status($prjId);
						
						js_alert('�����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                }
				exit;
            }
        }
        else
        {  
			$auth = $workflow->start_authority('dulihuodong');
			if(!$auth){
				js_alert('����Ȩ��');
			}

            $url = __APP__  . '/Touch/Activ/process/prjId/' . $prjId;
            header("Location:$url");
            exit();

            $form=$workflow->createHtml();
			if($_REQUEST['savedata']){
				if($prjId){
                    $project_model = D('Project');
					$pstatus = $project_model->get_project_status($prjId);
					if($pstatus==2){
						$flow_data['type'] = 'dulihuodong';//$type; 
						$flow_data['CASEID'] = $prjId;
						$flow_data['RECORDID'] = $ACTIVID;
						//$flow_data['ACTIVID'] = $ACTIVID;
						$flow_data['INFO'] = strip_tags($_POST['INFO']);
						$flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
						$flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
						$flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
						$flow_data['FILES'] = $_POST['FILES']; 
						$flow_data['ISMALL'] =  intval($_POST['ISMALL']); 
						$flow_data['ISPHONE'] =  intval($_POST['ISPHONE']); 
						$flow_data['COPY_USERID'] =  intval($_POST['COPY_USERID']); 
						$str = $workflow->createworkflow($flow_data);
						if($str){
							
							$project_model->update_check_status($prjId);//�����
							
							js_alert('�ύ�ɹ�',U('Activ/opinionFlow',$this->_merge_url_param));
						}else{
							js_alert('�ύʧ��');
						}
					}else{
							js_alert('�벻Ҫ�ظ��ύ', U('Activ/opinionFlow',$this->_merge_url_param));

							exit;
					}
				}
            }
        }
        
        $this->assign('form', $form);
		$this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow');
    }
	//��Ŀ�»����
	function XiangMuOpinionFlow(){
		
        $prjId = $_REQUEST['prjid']?$_REQUEST['prjid']:$_REQUEST['CASEID'];
	
		$type = $_REQUEST['flowType'] ? $_REQUEST['flowType']:'';
		$activId = $_REQUEST['activId']?$_REQUEST['activId']:$_REQUEST['RECORDID'];
       

		//�жϷ����Ƿ����
		if($_REQUEST['act']=='checkbudgetFee'){ 

			$fees = M('Erp_actibudgetfee')->where("ACTIVITIES_ID=".$activId)->select();
			
			$activety = M("Erp_activities")->where("ID = {$activId}")->find();
			$budgetFee = $activety['BUDGET'];//Ԥ�����
		
			$pri_Budget_Fee = M('Erp_actibudgetfee')->where("ISVALID = -1 AND ACTIVITIES_ID =".$activId)->sum('AMOUNT');//ʵ��Ԥ�����
			
			if(!$fees){
				$result['status'] = 'n';
				$result['info'] = g2u('������дԤ�����');
				 
			}elseif($budgetFee >  $pri_Budget_Fee){
				$result['status'] = 'n';
				$result['info'] = g2u('Ԥ�������ʵ��Ԥ�������� '.($budgetFee-$pri_Budget_Fee).'Ԫ');
			}elseif($budgetFee <  $pri_Budget_Fee){
				$result['status'] = 'n';
				$result['info'] = g2u('Ԥ�����С��ʵ��Ԥ�����');
			}else{
				$result['status'] = 'y';
			} 
			echo json_encode($result);exit;

		}


        //������ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;
        
        Vendor('Oms.workflow');			
        $workflow = new workflow();
        
        if($flowId > 0)
        {
            //�����Ѿ����ڵĹ�����
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata'])
            {
                //��һ��
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                }
                //ͨ����ť
                else if($_REQUEST['flowPass'])
                {
					
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {   
                        js_alert('ͬ��ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('ͬ��ʧ��');
                    }
                }
                //�����ť
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('���ʧ��');
                    }
                }
                //��ֹ��ť
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('δ�����ؾ���ɫ');exit;
					}
                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {
                        $ProjectCase = D('ProjectCase');
						$ProjectCase->set_case_by_activitiesId($activId,2);
						js_alert('�����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                }
				exit;
            }
        }
        else
        {   
			
			if(!$type){
				js_alert();
			}

			$auth = $workflow->start_authority($type);
			if(!$auth){
				js_alert('����Ȩ��');
			}	
			
			$form=$workflow->createHtml();
			if($_REQUEST['savedata']){
				
				if($activety['STATUS']==0){
					$flow_data['type'] = 'xiangmuxiahuodong';//$type;
					$flow_data['CASEID'] = $prjId;
					$flow_data['RECORDID'] = $activId;
					$flow_data['ACTIVID'] = '';
					$flow_data['INFO'] = strip_tags($_POST['INFO']);
					$flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
					$flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
					$flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
					$flow_data['FILES'] = $_POST['FILES'];
					$flow_data['ISMALL'] =  intval($_POST['ISMALL']);
					$flow_data['ISPHONE'] =  intval($_POST['ISPHONE']);
					$flow_data['COPY_USERID'] =  intval($_POST['COPY_USERID']); 
					$str = $workflow->createworkflow($flow_data);
					if($str){

						//��������ʱ��
						$aptime = date("Y/m/d H:m:s");
						$update = M()->execute("update erp_activities set aptime = to_date('{$aptime}','yyyy/mm/dd HH24:MI:SS') where id = $activId");
					
						js_alert('�ύ�ɹ�',U('Activ/XiangMuOpinionFlow', $this->_merge_url_param ));exit;
					}else{
						js_alert('�ύʧ��');exit;
					}
				}else{
					js_alert('�벻Ҫ�ظ��ύ', U('Activ/XiangMuOpinionFlow',$this->_merge_url_param));

					exit;
				}
            }
        }
        
        $this->assign('form', $form);
		$this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('XiangMuOpinionFlow');
	}

	 //�����������
    function opinionFlowChange() {
		$prjId = $_REQUEST['prjid']?$_REQUEST['prjid']:$_REQUEST['CASEID'];
	
		$type = $_REQUEST['flowType'] ? $_REQUEST['flowType']:'';
        
        //������ID
        $flowId = !empty($_REQUEST['flowId']) ? intval($_REQUEST['flowId']) : 0;
		
		$activId = $_REQUEST['ACTIVID']?$_REQUEST['ACTIVID']:$_REQUEST['activId'];

        Vendor('Oms.workflow');			
        $workflow = new workflow();
		Vendor('Oms.Changerecord');			
		$changer = new Changerecord();

          
		if($_REQUEST['act'] == 'checkbudgetFee')
		{
			$activety = M("Erp_activities")->where("ID = {$activId}")->find();
			$budgetFee = $activety['BUDGET'];//Ԥ�����

			//Ԥ����ñ��
			$params = array(
					'TABLE' => 'ERP_ACTIVITIES',
					'BID' => $activId,
					'CID' => $_REQUEST['RECORDID']
				);
			$changer->fields=array('BUDGET');
			$change_budget = $changer->getRecords($params);
			$change_budget_Fee = $change_budget['BUDGET']['VALUEE'];
			$budgetFee = $change_budget_Fee ? $change_budget_Fee : $budgetFee;

			//ʵ��Ԥ����ñ��
            $pri_Budget_Fee = 0;
            //�жϷ����Ƿ����
            $fees = M('Erp_actibudgetfee')->where(sprintf("ACTIVITIES_ID = %d AND (CID = %d OR ISVALID = -1)", $activId, $_REQUEST['RECORDID']))->select();
			if($fees) {
				$param = array(
					'TABLE' => 'ERP_ACTIBUDGETFEE',
					'CID' => $_REQUEST['RECORDID']
				);
				
				foreach($fees as $fee){
					$param['BID'] = $fee['ID'];
					$changer->fields=array('AMOUNT');
					$Records = $changer->getRecords($param);

					if($Records){
                        $pri_Budget_Fee += $Records['AMOUNT']['VALUEE'];
					} else {
                        $pri_Budget_Fee += M('Erp_actibudgetfee')->where('ID = ' . $fee['ID'])->getField('AMOUNT');
                    }
				}
			}
			
			if(!$fees) {
				$result['status'] = 'n';
				$result['msg'] = g2u('������дԤ�����');
			}
			elseif($budgetFee >  $pri_Budget_Fee)
			{
				$result['status'] = 'n';
				$result['msg'] = g2u('Ԥ�������ʵ��Ԥ�������� '.($budgetFee-$pri_Budget_Fee)."Ԫ");
			}
			elseif($budgetFee <  $pri_Budget_Fee)
			{
				$result['status'] = 'n';
				$result['msg'] = g2u('Ԥ�����С��ʵ��Ԥ�����');
			}
			elseif($budgetFee = $pri_Budget_Fee)
			{
				$result['status'] = 'y';
				$result['msg'] = '';
			}

			echo json_encode($result);exit;
		}

        if($flowId > 0)
        {
            //�����Ѿ����ڵĹ�����
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata'])
            {
                //��һ��
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                }
                //ͨ����ť
                else if($_REQUEST['flowPass'])
                {
					
                    $str = $workflow->passWorkflow($_REQUEST);
                    if($str)
                    {   
						
                        js_alert('ͬ��ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('ͬ��ʧ��');
                    }
                }
                //�����ť
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('���ʧ��');
                    }
                }
                //��ֹ��ť
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('δ�����ؾ���ɫ');exit;
					}

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {
                        $CID = $_REQUEST['RECORDID'];
						$changer->setRecords($CID);

						//����project����
						$actData = M("Erp_activities")->where("ID = {$activId}")->find();
						$project_ac =  M("Erp_project")->where("ID = {$prjId}")->find();
						if($project_ac['ACSTATUS'] ){
							$update_Project = M("Erp_project")->where("ID = {$prjId}")->setField("PROJECTNAME",$actData['TITLE']);
						}

                        if (intval($_REQUEST['activId']) > 0) {
                            D('ActiveBudget')->onActiveChangeSuccess($CID);
                        }
                             
						js_alert('�����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                }
				exit;
            }
        }
        else
        {   
			if(!$type){
				js_alert("");
			}
			$auth = $workflow->start_authority($type);
			if(!$auth){
				js_alert('����Ȩ��');
			}
			
			$form=$workflow->createHtml();
			if($_REQUEST['savedata'])
			{
				$project_model = D('Project');
				
				$pstatus = $project_model->get_Change_Flow_Status($_REQUEST['RECORDID']);

				if($pstatus == '1')
				{
					js_alert('�����ظ��ύŶ',U('Activ/opinionFlowChange',$this->_merge_url_param));
				}
				else
				{
					$_REQUEST['type'] = $_REQUEST['flowType'];
					$str = $workflow->createworkflow($_REQUEST);
					if($str){

						js_alert('�ύ�ɹ�',U('Activ/opinionFlowChange',$this->_merge_url_param));
					}else{
						js_alert('�ύʧ��',U('Activ/opinionFlowChange',$this->_merge_url_param));
					}
				}
            }
        }
        
        $this->assign('form', $form);
		$this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlowChange');
    }
    
    //��Ŀ�»����
	/*public function activSummery(){       
        //����Model
        $project_case_model = D("ProjectCase");
        //�ɱ����MOdel
        $cost_supplement_model = D("CostSupplement");
        //��Ŀ�ɱ�Model
        $cost_model = D("ProjectCost");
        
        //״̬��ʶ
        $cost_supplement_status_remark = $cost_supplement_model->get_cost_supplement_status_remark();
        //״̬
        $cost_supplement_status = $cost_supplement_model->get_cost_supplement_status();
        $cost_sup_type_status = $cost_supplement_model->get_cost_sup_type();
        
        $prjid = $_REQUEST["prjid"];
        
        $case_info = $project_case_model->get_info_by_pid($prjid,"ds",array("ID"));
        
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
		$showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
        $id = isset($_GET['ID']) ? strip_tags($_GET['ID']) : 0;
        
        $activeid = isset($_REQUEST["parentchooseid"]) ? intval($_REQUEST["parentchooseid"]) : 0;        
        $active_info = D("Erp_activities")->where("ID = $activeid")->field(array("CASE_ID","BUSINESSFEE","STATUS"))->find();
        if($faction == "saveFormData" && $showForm == 3 && $id == 0)//����
        {
            $sup_data["CASE_ID"] = $active_info["CASE_ID"];
            $sup_data["BRAND"] = strip_tags($_REQUEST["BRAND"]) ? u2g(strip_tags(trim($_REQUEST["BRAND"]))): "";
            $sup_data["IS_FUNDPOOL"] = isset($_REQUEST["IS_FUNDPOOL"]) ? intval($_REQUEST["IS_FUNDPOOL"]) : "";
            $sup_data["IS_KF"] = isset($_REQUEST["IS_KF"]) ? intval($_REQUEST["IS_KF"]) : "";
            $sup_data["MODEL"] = strip_tags($_REQUEST["MODEL"]) ? u2g(strip_tags($_REQUEST["MODEL"])) : "";
            $sup_data["NUM"] = strip_tags($_REQUEST["NUM"]) ? intval(strip_tags($_REQUEST["NUM"])) : 0;
            $sup_data["PRICE"] = strip_tags($_REQUEST["PRICE"]) ? floatval(strip_tags($_REQUEST["PRICE"])) : 0.00;
            $sup_data["PRODUCT_NAME"] = strip_tags($_REQUEST["PRODUCT_NAME"]) ? u2g(strip_tags(trim($_REQUEST["PRODUCT_NAME"]))) : "";
            $sup_data["PUR_DATE"] = strip_tags($_REQUEST["PUR_DATE"]) ? strip_tags($_REQUEST["PUR_DATE"]) : "";
            $sup_data["STATUS"] = $cost_supplement_status["no_apply"];
            $sup_data["SUP_TYPE"] = $cost_sup_type_status["active_cost"];
            $sup_data["ENTITY_ID"] = intval($_GET["parentchooseid"]);
            
            $summery_money = $this->get_summery_money($activeid);

            if(floatval($summery_money) != floatval($sup_data["NUM"]*$sup_data["PRICE"]))
            {
                $result["status"] = 0;
                $result["msg"] = "����ʧ�ܣ������ܽ�������ھ����ʣ���ܽ��";
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
            //var_dump($sup_data);die;
            $insertid = $cost_supplement_model->add_cost_supplement_info($sup_data);
            if($insertid)
            {
                $result["status"] = 2;
                $result["msg"] = "�����ɹ���";               
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "����ʧ�ܣ�";
            }
            
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;

        }
        else if($faction == "saveFormData" && $showForm == 1 && $id > 0)//�༭
        {
            $update_arr["BRAND"] = strip_tags($_REQUEST["BRAND"]) ? u2g(strip_tags(trim($_REQUEST["BRAND"]))): "";
            $update_arr["IS_KF"] = isset($_REQUEST["IS_KF"]) ? intval($_REQUEST["IS_KF"]) : "";
            $update_arr["MODEL"] = strip_tags($_REQUEST["MODEL"]) ? u2g(strip_tags($_REQUEST["MODEL"])) : "";
            $update_arr["NUM"] = strip_tags($_REQUEST["NUM"]) ? intval(strip_tags($_REQUEST["NUM"])) : 0;
            $update_arr["PRICE"] = strip_tags($_REQUEST["PRICE"]) ? floatval(strip_tags($_REQUEST["PRICE"])) : 0.00;
            $update_arr["PRODUCT_NAME"] = strip_tags($_REQUEST["PRODUCT_NAME"]) ? u2g(strip_tags(trim($_REQUEST["PRODUCT_NAME"]))) : "";
            $update_arr["PUR_DATE"] = strip_tags($_REQUEST["PUR_DATE"]) ? strip_tags($_REQUEST["PUR_DATE"]) : "";
            $up_num = $cost_supplement_model->update_cost_supplement_info_by_ids($id,$update_arr);
            //echo M()->_sql();
            $summery_money = $this->get_summery_money($activeid);
            if(floatval($summery_money) != floatval($sup_data["NUM"]*$sup_data["PRICE"]))
            {
                $result["status"] = 0;
                $result["msg"] = "�޸�ʧ�ܣ������ܽ�������ھ����ʣ���ܽ��";
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
            if($up_num)
            {
                $result["status"] = 1;
                $result["msg"] = "�޸ĳɹ���";
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "�޸�ʧ�ܣ������ԣ�";
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        else if($faction == "delData" && $id > 0)//ɾ��
        {
            $del_num = $cost_supplement_model->del_cost_supplement_info_by_ids($id);
            if($del_num)
            {
                $result["status"] = 2;
                $result["msg"] = "ɾ���ɹ���";               
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "ɾ��ʧ�ܣ�";
            }
            
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;           
        }
         
        //�ҷ��ѿ������ʽ�ط���
        //$sql = "SELECT SUM(FEE) SUM_FUNDPOOL_COST FROM ERP_COST_LIST WHERE CASE_ID = ".$active_info["CASE_ID"]." AND ISFUNDPOOL=1";
        //$cost_fundpool_fee = M()->query($sql);
        //$myfee = $cost_fundpool_fee ? $cost_fundpool_fee[0]["SUM_FUNDPOOL_COST"] : 0;
        
        //������֧�����ʽ�ط���
        //$businessfee = $active_info["BUSINESSFEE"] ? $active_info["BUSINESSFEE"] : 0;

        Vendor('Oms.Form');			
        $form = new Form(); 
        $cond_where = "ENTITY_ID = ".$activeid;
        $form->initForminfo(183)->where($cond_where);
        if($showForm > 0 )
        {   
            //��������(���νṹ)
            $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
                    . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 and ISONLINE=0', FALSE);
        }
        else
        {   
            //��������
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 and ISONLINE=0', FALSE);
        }
        
        //���ð�ť��ʾ
        $form->DELCONDITION = "%STATUS% == 1";
        $form->EDITCONDITION = "%STATUS% == 1";
        
        //�Ƿ��ʽ��
        $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), FALSE)
                    ->setMyFieldVal('IS_FUNDPOOL', "1", true)
                    ->setMyFieldVal('STATUS', "1",TRUE);

        //�Ƿ�۷�
        $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), FALSE);
        
        //����״̬
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($cost_supplement_status_remark), FALSE);
        
        $form->GABTN = "<a id = 'add_costsupplement' href = 'javascript:;'>����</a>"
                    . "<a href='javascript:;' onclick='applyReimburse()'>�ύ������</a>";
        $form->FKFIELD = "";    
        $form = $form->getResult();
        $this->assign("form",$form);
        $this->assign("paramUrl",$this->_merge_url_param);
        $this->assign("myfee",$myfee);
        $this->assign("businessfee",$businessfee);
        $this->display("activSummeryX");
    }*/
    
    //���ɱ�����
    public function applyReim()
    {
        
        $prjid = isset($_REQUEST["prjid"]) ? intval($_REQUEST["prjid"]) : 0;
        //$this->project_auth($prjid,array(1,2));
        $cost_sup_id = isset($_REQUEST["cost_sup_id"]) ? $_REQUEST["cost_sup_id"] : "";
        
        //������Model
        $reim_list_model = D("ReimbursementList");
        //������ϸModel
        $reim_detail_model = D("ReimbursementDetail");
        //��������MOdel
        $reim_type_model = D("ReimbursementType");
        //�ɱ����Model
        $cost_supplement_moel = D("CostSupplement");
        //��ĿModel
        //$project_model = D("Project");        
        //$project_info = $project_model->get_info_by_id($prjid,array("TLF_PROJECT_ID"));
        //�ж��Ƿ����ʽ����Ŀ
        $house_info = D("Erp_house")->field(array("ISFUNDPOOL"))->where("PROJECT_ID =".$prjid)->find();
        $isfundpull = $house_info["ISFUNDPOOL"];
        if( $isfundpull == 1 )
        {
            $result['state'] = 0;
            $result["msg"] = "�Բ��𣬸���Ŀ�����ʽ����Ŀ������������������";
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit();
        }
        
        
        //������״̬����
        $reim_list_status = $reim_list_model->get_conf_reim_list_status();
        
        //������״̬��־����
        $reim_list_status_remark = $reim_list_model->get_conf_reim_list_status_remark();
        
        //������ϸ״̬����
        $reim_detail_status = $reim_detail_model->get_conf_reim_detail_status();
        
        //�ɱ����״̬
        $cost_supplement_status = $cost_supplement_moel->get_cost_supplement_status();
        
        //�ж���ѡ��¼�Ƿ��Ѿ����뱨��
        $cost_sup_id_str = implode(",", $cost_sup_id);
        $cond_where = "ID IN($cost_sup_id_str) AND STATUS > 1";
        $is_reim = $cost_supplement_moel->get_cost_supplement_info_by_cond($cond_where,array("ID"));
        
        if($is_reim)
        {
            $result['state'] = 0;
            $result["msg"] = "����ѡ�ļ�¼�а����Ѿ����뱨���ģ������ظ����룬������ѡ��";
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit();
        }
        
        //������������
        $reim_type = $reim_type_model->get_reim_type();       
        //��ǰ�û����
        $uid = intval($_SESSION['uinfo']['uid']);
        //��ǰ�û�����
        $user_truename = $_SESSION['uinfo']['tname'];
        //��ǰ���б��
        $city_id = intval($this->channelid);
        
        //����������
        $reimburse_type = 13;
        
        $field_arr = array("ID","PRICE","NUM","CASE_ID","STATUS","IS_KF");
        $cost_sup_info = $cost_supplement_moel->get_cost_supplement_info_by_ids($cost_sup_id,$field_arr); 
        $amount = 0;
        foreach ($cost_sup_info as $key=>$val)
        {
            if($val["STATUS"] == 1)
            {
                $amount += $val["PRICE"]*$val["NUM"];
            }
            
        }

        //�жϸ��û� ���Ƿ���ڸ���������δ�ύ�ı�������
        $last_id = $reim_list_model->get_last_reim_list($uid, $reimburse_type, $city_id);
        $last_id = $last_id["ID"];
        //var_dump($last_id);die;
        $this->model->startTrans();
        if( !$last_id)//û�ҵ����µı������½�һ�ű�����
        {
            //�����µı�����             
            $list_arr["AMOUNT"] = $amount;
            $list_arr["TYPE"] = $reimburse_type;                      
            $list_arr["STATUS"] = $reim_list_status["reim_list_sub"];
            $list_arr["APPLY_UID"] = $uid;
            $list_arr["APPLY_TRUENAME"] = $user_truename;
            $list_arr["APPLY_TIME"] = date("Y-m-d H:m:s");
            $list_arr["CITY_ID"] = $city_id;
            //var_dump($list_arr); 
            $last_id = $reim_list_model->add_reim_list($list_arr);
        }
        
        //���뱨����ϸ��ʧ�ܵļ�¼���
        $fail_row = "";
        //���뱨����ϸ��ɹ��ļ�¼���
        $success_row = "";
        
        //����µı�����ϸ
        foreach($cost_sup_info as $key=>$val)
        {
            //var_dump($val);
            $reim_details_arr["LIST_ID"] = $last_id;
            $reim_details_arr["CITY_ID"] = $city_id;
            $reim_details_arr["CASE_ID"] = $val["CASE_ID"];
            $reim_details_arr["BUSINESS_ID"] = $cost_sup_id[$key];
            $reim_details_arr["MONEY"] = $val["NUM"]*$val["PRICE"];
            $reim_details_arr["STATUS"] = $reim_detail_status["reim_detail_sub"];
            $reim_details_arr["TYPE"] = $reimburse_type;
            $reim_details_arr["ISKF"] = $val["IS_KF"];
            $reim_details_arr["FEE_ID"] = 80;
            $reim_details_arr["BUSINESS_PARENT_ID"] = $cost_sup_id[$key];
            //var_dump($reim_details_arr);die;
            $res = $reim_detail_model->add_reim_details($reim_details_arr); 
            
            if( !$res )
            {
                $fail_row .= $cost_sup_id[$key].",";
            }
            else
            {
                //�޸ĳɱ����״̬Ϊ������ ������״̬Ϊ���ύ
                $cost_status = $cost_supplement_status["appling"];
                $list_status = $reim_list_status["reim_list_sub"];
                $cost_supplement_moel->update_cost_supplement_info_by_ids($cost_sup_id[$key],array("STATUS"=>$cost_status));
                $cond_where = "ID = $last_id";
                $reim_list_model->update_reim_list_by_cond(array("STATUS"=>$list_status),$cond_where);
                $success_row .= $cost_sup_id[$key].",";
                
            }
        }
       
        $fail_row = rtrim($fail_row,",");
        $success_row = rtrim($success_row,",");
        if( $success_row == "")
        {
            $this->model->rollback();
            $result['state'] = 0;
            $result["msg"] = "���뱨��ʧ�ܣ�����";
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit();
        }
        else
        {
            if($fail_row)
            {
                $this->model->commit();
                $result['state'] = 2;
                $result["msg"] = "���Ϊ ".$fail_row." �ļ�¼����ʧ�ܣ����Ϊ ".$success_row." ��¼����ɹ�";
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit();
            }
            else
            {
                $this->model->commit();
                $result['state'] = 1;
                $result["msg"] = "���뱨���ɹ�";
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit();
            }
        }
        
    }
    
    //������
    /*public function reimList()
    {
        $reim_list_model = D("ReimbursementList");
        $reim_detail_model = D("ReimbursementDetail");
        $reim_type_model = D("ReimbursementType");

        $uid = $_SESSION["uinfo"]["uid"];            
        $reim_list_status = $reim_list_model->get_conf_reim_list_status();
        $reim_list_status_remark = $reim_list_model->get_conf_reim_list_status_remark();
        $reim_detail_status = $reim_detail_model->get_conf_reim_detail_status();
        $reim_list_type = $reim_type_model->get_reim_type();
        
        Vendor('Oms.Form');
        $form = new Form();      
        $children = array(
                        array('������ϸ',U('/Activ/reimDetail',$this->_merge_url_param)),
                        array('�������',U('/Activ/loanMoney',$this->_merge_url_param)),
            );        
        $conf_where = "APPLY_UID = ".$uid."and TYPE =13";
        $form->initForminfo(176)
           // ->where($conf_where)
            ->setMyField("TYPE", "LISTCHAR", array2listchar($reim_list_type))
            ->setMyField("STATUS", "LISTCHAR", array2listchar($reim_list_status_remark))
            ->setChildren($children);

        $form = $form->getResult();
        $this->assign("form",$form);
        $this->assign("paramUrl",$this->_merge_url_param);
        $this->display('activ_reim_list');
    }*/
    
    //��ȡ�����»�ľ�����
    public function get_summery_money($activeid = "")
    {
        if(!$activeid)
        {
            $activeid = $_POST["activId"] ? intval($_POST["activId"]) : 0;
            $activ_info = D("Erp_activities")->where("ID = ".$activeid)->field(array("CASE_ID","BUSINESSFEE"))->find();
            
        }
        else
        {
           $activ_info = D("Erp_activities")->where("ID = ".$activeid)->field(array("CASE_ID","BUSINESSFEE"))->find();
        }
        
        $case_id = $activ_info["CASE_ID"];
        //������֧�����ʽ�ط���
        $businessfee = $activ_info["BUSINESSFEE"];

        //�ҷ��ѿ������ʽ�ط���(�����ѱ������ʽ�ط���)
        //$sql = "SELECT SUM(FEE) SUM_FUNDPOOL_COST FROM ERP_COST_LIST WHERE CASE_ID = ".$activ_info["CASE_ID"]." AND ISFUNDPOOL=1";
        $sql = "SELECT SUM(MONEY)SUM_FUNDPOOL_COST FROM ERP_REIMBURSEMENT_DETAIL A WHERE A.CASE_ID =".$case_id.
            " AND A.ISFUNDPOOL=1 AND A.STATUS=1";
        $cost_fundpool_fee = M()->query($sql);
        $myfee = $cost_fundpool_fee[0]["SUM_FUNDPOOL_COST"] ? $cost_fundpool_fee[0]["SUM_FUNDPOOL_COST"] : 0;

        //��Ҫ����ķ���Ϊ
        $summery_money = $businessfee-$myfee;       
        $summery_money =  $summery_money > 0 ? $summery_money : 0;
        if(!$_POST["activId"])
        {
            return $summery_money;
        }
        else
        {
            echo $summery_money;exit;
        }
       
    }

        public function getContractList() {
            $response = null;
            $keyword = $_REQUEST['keywords'];
            if ($keyword) {
                $url = CONTRACT_LIST . sprintf('?&keywords=%s&city=%s', $keyword, $this->channelid_py);
                $response = curl_get_contents($url);
            }

            echo $response;
        }
}
?>