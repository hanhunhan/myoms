<?php
	class ActivAction extends ExtendAction{
		 /*合并当前模块的URL参数*/
		private $_merge_url_param = array();
		private $model;
		private $isedit;

        //构造函数
		public function __construct() 
		{
            $this->model = new Model();
			parent::__construct();
			// 权限映射表
			$this->authorityMap = array(
			);
			
			//TAB URL参数
			$this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
			
			!empty($_GET['RECORDID']) ? $this->_merge_url_param['activId'] = $_GET['RECORDID'] : '';
			!empty($_GET['ACTIVID']) ? $this->_merge_url_param['ACTIVID'] = $_GET['ACTIVID'] : '';
			!empty($_GET['ACTIVID']) ? $this->_merge_url_param['activId'] = $_GET['ACTIVID'] : '';
			!empty($_GET['activId']) ? $this->_merge_url_param['activId'] = $_GET['activId'] : '';
			//2 编辑 1 查看 3 新增
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
				if( judgeFlowEdit($_REQUEST['flowId'],$_SESSION['uinfo']['uid']) ){//判断是否回到发起人 
					$this->isedit = true;
				}
			}
		}
    
		// 活动立项
		 function activPro(){
             $prjid = $_REQUEST['prjid'];
             if ($_REQUEST['act'] == 'checkAmount') {  // 检查数据是否正确
                 $atotal = M('Erp_actibudgetfee')->where(" ACTIVITIES_ID ='" . $_REQUEST['activitiesId'] . "'")->sum('AMOUNT');

                 if ($atotal > $this->_post('param')) {
                     $result['status'] = 'n';
                     $sy = $btotal - $atotal;
                     $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '已低于活动预算总额！现有活动预算总额为' . $atotal);
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

             //业务类型
             if ($_REQUEST['CHANGE'] == '-1') {  // 活动变更
                 // 设置合同号为只读属性
                 $form->setMyField('CONTRACT_NO', 'READONLY', -1);

                 if ($_REQUEST['activId'] and $_REQUEST['active'] == 2)//变更项目下活动编辑
                 {
                     $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 and (b.FSTATUS=2 or b.FSTATUS=4)) where 1=1 ');
                 } else {
                     $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 ) where 1=1 ');
                 }

                 if ($_GET['activId']) $prj_active = 1;
             } else {
                 if ($_REQUEST['active'] == 3 or $_REQUEST['active'] == 2)//项目下活动新增.编辑
                 {
                     $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 and (b.FSTATUS=2 or b.FSTATUS=4)) where 1=1 ');
                     $form->setMyField('CONTRACT_NO', 'FORMVISIBLE', 0);  // 项目下活动隐藏合同号字段
				 } else {
                     $form->setMyField('BUSINESSCLASS_ID', 'LISTSQL', 'select ID,YEWU from (select a.ID,a.YEWU from ERP_BUSINESSCLASS a left join ERP_CASE b on a.ID = b.SCALETYPE where b.PROJECT_ID = ' . $prjid . ' and  a.ID<>7 ) where 1=1 ');
                 }

                 if ($_GET['active']) $prj_active = 1;
             }
//
//             //判定是独立活动or 项目下活动
//             if ($_REQUEST['CHANGE'] == '-1') {
//                 if ($_GET['activId']) $prj_active = 1;
//             } else {
//                 if ($_GET['active']) $prj_active = 1;
//             }

             if ($_GET['paramId'] && !$_GET['active']) {//// 独立活动新增后业务类型
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
                 if ($_REQUEST['active']) {//项目下活动

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
                 } else {//独立活动

                     $case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID=' . $prjid)->find();

                     $form->setMyFieldVal('CASE_ID', $case['ID'], true);
                     $one = M('Erp_activities')->field('ID,PRINCOME')->where('CASE_ID=' . $case['ID'])->find();

                     if ($one) {
                         $form->PKVALUE = $one['ID'];
                     } else {
                         $form->setMyFieldVal('APPLICANT', $project['CUSER'], true);
                     }
                     $form->setMyFieldVal('BUSINESSCLASS_ID', 4, true);

                     if ($form->FormeditType == 1) {//新增编辑状态
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
             if ($_REQUEST['faction'] == 'saveFormData') {  // 保存数据
                 $BUSINESSCLASS_ID = $_REQUEST['BUSINESSCLASS_ID'];

                 // 判断合同号是否存在
                 $contractNoOld = trim($_REQUEST['CONTRACT_NO_OLD']);
                 $contractNoNew = trim($_REQUEST['CONTRACT_NO']);
                 $isExistContract = false; // 默认数据库中不存在该合同号
                 // 如果合同号为空，则表示数据库中未存储合同号
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
                         'msg' => g2u('系统中已存在合同号' . $contractNoNew . ', 请修改合同号！')
                     ));
                     exit;
                 }

                 // 在项目中保存合同号
                 if ($contractNoOld !== $contractNoNew) {
                     $updateProject = D('Project')->where("ID = {$prjid}")->save(array(
                         'CONTRACT' => $contractNoNew
                     ));
                     if ($updateProject === false) {
                         echo json_encode(array(
                             'status' => 'error',
                             'msg' => g2u('服务器内部错误')
                         ));
                         exit;
                     }
                 }

                 $case = M('Erp_case')->where("SCALETYPE = $BUSINESSCLASS_ID and PROJECT_ID=$prjid")->find();
                 if ($BUSINESSCLASS_ID == 4) {
                     $case_id = $case['ID'];
                     $form->setMyFieldVal('CASE_ID', $case_id, true);
                 } else {
                     // 项目下活动
                     if ($this->isShowOptionBtn($case['ID']) == self::HIDE_OPTION_BTN) {
                         js_alert("项目正处于终止或决算状态，不能添加项目下活动！", U("Activ/activPro",$this->_merge_url_param), 1);
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
							 //已删除和立项状态已终止的项目，项目名称可以重复
							 $bsql = "select PROJECTNAME from erp_project where ACSTATUS >=1 and PSTATUS !=5 and STATUS !=2 and CITY_ID = ".$this->channelid. " and ID !=".$prjId;
							 $ds_projects = D()->query($bsql);
							 foreach ($ds_projects as $ds_project) {
								 if ($title == $ds_project['PROJECTNAME']) {
									 $result['status'] = 0;
									 $result['msg'] = g2u('本业务下已经有此项目名称，请更换');
									 echo json_encode($result);
									 exit;
								 }
							 }
						 }
					 }

                 if ($project['ACSTATUS'] == 1) { //同步项目信息
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

             $form->FORMFORWARD = U('Activ/activPro', $this->_merge_url_param);  //保存后跳转*/

//              $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->purchaseManageOptions, $_REQUEST['CASE_TYPE']);
             $form = $form->getResult();

             $this->assign('form', $form);
             $this->assign('prj_active', $prj_active);
             $this->assign('paramUrl', $this->_merge_url_param);
             $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
             $this->display('activPro');
		 }
         
         
		//项目下活动
		function activProX()
        { 
			$prjid = $_REQUEST['prjid'];
			$this->project_case_auth($prjid );//项目业务权限判断
			Vendor('Oms.Form');			
			$form = new Form();

			//$children = array(
                 //array("活动决算",U("Activ/activSummery",$this->_merge_url_param)),
                // array("报销申请",U("Activ/reimList",$this->_merge_url_param)),
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
					$form->GCBTN =  '<a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="submitActiv();" >提交</a>';
				$form->GCBTN .= '<a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="changeActiv();" >活动变更</a> <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="execActiv();" >执行</a>';
				$form->CZBTN = array(
					'%STATUS% == 0'=>'<a class="contrtable-link btn btn-primary btn-xs" onclick="editActiv(this);" href="javascript:;"><i class="glyphicon glyphicon-edit"></i></a><a class="contrtable-link btn btn-danger btn-xs" onclick="delActiv(this);" href="javascript:;"><i class="glyphicon glyphicon-trash"></i></a>',
					'%STATUS% == 1'=>'<a class="contrtable-link btn btn-success btn-xs" onclick="viewActiv(this);" href="javascript:;"><i class="glyphicon glyphicon-eye-open"></i></a>','%STATUS% == 2'=>'<a class="contrtable-link btn btn-success btn-xs" onclick="viewActiv(this);" href="javascript:;">查看</a>',
					'%STATUS% == 3'=>'<a class="contrtable-link btn btn-success btn-xs" onclick="viewActiv(this);" href="javascript:;">查看</a>'
				);
			$form->setMyField("SUMMERY_MONEY", "GRIDVISIBLE", "-1");
			$formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
            // $this->assign('isShowOptionBtn', $this->isProjectContextMenuRunnable($prjid, 'benefits'));
			$this->assign('form',$formHtml);
			$this->assign('paramUrl',$this->_merge_url_param);
			$this->assign('prjid',$prjid);
			$this->display('activProX');
		 }
         
         
		 //活动预算
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
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '超出活动预算总额！总预算只剩余'.$sy) ;
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
                $form->setMyFieldVal('CID',$CID ,true);  // 变更版本号
                $this->assign('CID', $CID);  // 项目变更号
				if($_REQUEST['activId']){
					if($_REQUEST['active'] == '1' && $this->isedit==false){
						$form->CZBTN=' ';
						//$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">排序</a>'.'<a id="j-search" class="j-showalert" href="javascript:;">搜索</a>'.'<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">刷新</a>';
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
						//$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">排序</a>'.'<a id="j-search" class="j-showalert" href="javascript:;">搜索</a>'.'<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">刷新</a>';
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
					$form->setMyFieldVal('CASE_ID',$case['ID'] ,true);  // 设置CASE_ID

					$form->where($where);
				}
				
			}else{
				if($_REQUEST['active']){
					$form->where("ACTIVITIES_ID=".$_REQUEST['activId']." AND ISVALID = -1");

					if($_REQUEST['active'] == '1' && $this->isedit==false){
						$form->CZBTN=' ';
						//$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">排序</a>'.'<a id="j-search" class="j-showalert" href="javascript:;">搜索</a>'.'<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">刷新</a>';
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
						//$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">排序</a>'.'<a id="j-search" class="j-showalert" href="javascript:;">搜索</a>'.'<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">刷新</a>';
						$form->ADDABLE = 0;
					}
					$form->setMyFieldVal('ISVALID','-1' ,true);
					$form->setMyFieldVal('CASE_ID',$case['ID'] ,true);

				}
			}  

             // 编辑或新增状态时预加载费用类型字段
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
		  //活动预算
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
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '总预算超出！总预算只剩余'.$sy) ;
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
			//$form->CZBTN = array('%AMOUNT%==23331'=>'<a>测试1</a>','%AMOUNT%==2333'=>'<a>测试2</a><a>测试3</a>');//动态判断操作按钮
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
				$result['msg'] = g2u('成功');
				$result['status'] =1;
				
			}else{
				$result['msg'] = g2u('失败');
				$result['status'] =0;
			}

			echo json_encode($result);exit;
			
		}
		  //独立活动立项审批意见
		function opinionFlow()
    {  
        $prjId = $_REQUEST['prjid']?$_REQUEST['prjid']:$_REQUEST['CASEID'];
		$case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID='.$prjId)->find();
		$one = M('Erp_activities')->where('CASE_ID='.$case['ID'])->find();
		$ACTIVID = $one['ID']; 
		$fees = M('Erp_actibudgetfee')->where(" ISVALID=-1 and ACTIVITIES_ID=".$ACTIVID)->select();
		if(!$fees){
			js_alert('请先填写预算费用');exit;
		}
		//判断费用是否相等
		$budgetFee = $one['BUDGET'];//预算费用
		
		$pri_Budget_Fee = M('Erp_actibudgetfee')->where(" ACTIVITIES_ID ='".$ACTIVID."'")->sum('AMOUNT');//实际预算费用
		
		if($budgetFee >  $pri_Budget_Fee)
		{
			js_alert('预算费用与实际预算费用相差 '.($budgetFee-$pri_Budget_Fee)."元");exit;
		}
		if($budgetFee <  $pri_Budget_Fee)
		{
			js_alert('预算费用小于实际预算费用');exit;
		}

		$type = $_REQUEST['flowType'] ? $_REQUEST['flowType']:'';
        
        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;
        
        Vendor('Oms.workflow');			
        $workflow = new workflow();
       
        if($flowId > 0)
        {
            //处理已经存在的工作流
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);
			
            if($_REQUEST['savedata'])
            {
                //下一步
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('办理成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('办理失败');
                    }
                }
                //通过按钮
                else if($_REQUEST['flowPass'])
                {
					
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {   
						/*$projectMod = D('Project');
						$projectMod->update_pass_status($prjId);*/
                        js_alert('同意成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('同意失败');
                    }
                }
                //否决按钮
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('否决成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('否决失败');
                    }
                }
                //终止按钮
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('未经过必经角色');exit;
					}

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {
                        $projectMod = D('Project');
						$projectMod->update_pass_status($prjId);
						
						js_alert('备案成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('备案失败');
                    }
                }
				exit;
            }
        }
        else
        {  
			$auth = $workflow->start_authority('dulihuodong');
			if(!$auth){
				js_alert('暂无权限');
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
							
							$project_model->update_check_status($prjId);//审核中
							
							js_alert('提交成功',U('Activ/opinionFlow',$this->_merge_url_param));
						}else{
							js_alert('提交失败');
						}
					}else{
							js_alert('请不要重复提交', U('Activ/opinionFlow',$this->_merge_url_param));

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
	//项目下活动审批
	function XiangMuOpinionFlow(){
		
        $prjId = $_REQUEST['prjid']?$_REQUEST['prjid']:$_REQUEST['CASEID'];
	
		$type = $_REQUEST['flowType'] ? $_REQUEST['flowType']:'';
		$activId = $_REQUEST['activId']?$_REQUEST['activId']:$_REQUEST['RECORDID'];
       

		//判断费用是否相等
		if($_REQUEST['act']=='checkbudgetFee'){ 

			$fees = M('Erp_actibudgetfee')->where("ACTIVITIES_ID=".$activId)->select();
			
			$activety = M("Erp_activities")->where("ID = {$activId}")->find();
			$budgetFee = $activety['BUDGET'];//预算费用
		
			$pri_Budget_Fee = M('Erp_actibudgetfee')->where("ISVALID = -1 AND ACTIVITIES_ID =".$activId)->sum('AMOUNT');//实际预算费用
			
			if(!$fees){
				$result['status'] = 'n';
				$result['info'] = g2u('请先填写预算费用');
				 
			}elseif($budgetFee >  $pri_Budget_Fee){
				$result['status'] = 'n';
				$result['info'] = g2u('预算费用与实际预算费用相差 '.($budgetFee-$pri_Budget_Fee).'元');
			}elseif($budgetFee <  $pri_Budget_Fee){
				$result['status'] = 'n';
				$result['info'] = g2u('预算费用小于实际预算费用');
			}else{
				$result['status'] = 'y';
			} 
			echo json_encode($result);exit;

		}


        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;
        
        Vendor('Oms.workflow');			
        $workflow = new workflow();
        
        if($flowId > 0)
        {
            //处理已经存在的工作流
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata'])
            {
                //下一步
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('办理成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('办理失败');
                    }
                }
                //通过按钮
                else if($_REQUEST['flowPass'])
                {
					
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {   
                        js_alert('同意成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('同意失败');
                    }
                }
                //否决按钮
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('否决成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('否决失败');
                    }
                }
                //终止按钮
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('未经过必经角色');exit;
					}
                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {
                        $ProjectCase = D('ProjectCase');
						$ProjectCase->set_case_by_activitiesId($activId,2);
						js_alert('备案成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('备案失败');
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
				js_alert('暂无权限');
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

						//更新申请时间
						$aptime = date("Y/m/d H:m:s");
						$update = M()->execute("update erp_activities set aptime = to_date('{$aptime}','yyyy/mm/dd HH24:MI:SS') where id = $activId");
					
						js_alert('提交成功',U('Activ/XiangMuOpinionFlow', $this->_merge_url_param ));exit;
					}else{
						js_alert('提交失败');exit;
					}
				}else{
					js_alert('请不要重复提交', U('Activ/XiangMuOpinionFlow',$this->_merge_url_param));

					exit;
				}
            }
        }
        
        $this->assign('form', $form);
		$this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('XiangMuOpinionFlow');
	}

	 //活动变更审批意见
    function opinionFlowChange() {
		$prjId = $_REQUEST['prjid']?$_REQUEST['prjid']:$_REQUEST['CASEID'];
	
		$type = $_REQUEST['flowType'] ? $_REQUEST['flowType']:'';
        
        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ? intval($_REQUEST['flowId']) : 0;
		
		$activId = $_REQUEST['ACTIVID']?$_REQUEST['ACTIVID']:$_REQUEST['activId'];

        Vendor('Oms.workflow');			
        $workflow = new workflow();
		Vendor('Oms.Changerecord');			
		$changer = new Changerecord();

          
		if($_REQUEST['act'] == 'checkbudgetFee')
		{
			$activety = M("Erp_activities")->where("ID = {$activId}")->find();
			$budgetFee = $activety['BUDGET'];//预算费用

			//预算费用变更
			$params = array(
					'TABLE' => 'ERP_ACTIVITIES',
					'BID' => $activId,
					'CID' => $_REQUEST['RECORDID']
				);
			$changer->fields=array('BUDGET');
			$change_budget = $changer->getRecords($params);
			$change_budget_Fee = $change_budget['BUDGET']['VALUEE'];
			$budgetFee = $change_budget_Fee ? $change_budget_Fee : $budgetFee;

			//实际预算费用变更
            $pri_Budget_Fee = 0;
            //判断费用是否相等
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
				$result['msg'] = g2u('请先填写预算费用');
			}
			elseif($budgetFee >  $pri_Budget_Fee)
			{
				$result['status'] = 'n';
				$result['msg'] = g2u('预算费用与实际预算费用相差 '.($budgetFee-$pri_Budget_Fee)."元");
			}
			elseif($budgetFee <  $pri_Budget_Fee)
			{
				$result['status'] = 'n';
				$result['msg'] = g2u('预算费用小于实际预算费用');
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
            //处理已经存在的工作流
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata'])
            {
                //下一步
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('办理成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('办理失败');
                    }
                }
                //通过按钮
                else if($_REQUEST['flowPass'])
                {
					
                    $str = $workflow->passWorkflow($_REQUEST);
                    if($str)
                    {   
						
                        js_alert('同意成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('同意失败');
                    }
                }
                //否决按钮
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('否决成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('否决失败');
                    }
                }
                //终止按钮
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('未经过必经角色');exit;
					}

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {
                        $CID = $_REQUEST['RECORDID'];
						$changer->setRecords($CID);

						//更新project名称
						$actData = M("Erp_activities")->where("ID = {$activId}")->find();
						$project_ac =  M("Erp_project")->where("ID = {$prjId}")->find();
						if($project_ac['ACSTATUS'] ){
							$update_Project = M("Erp_project")->where("ID = {$prjId}")->setField("PROJECTNAME",$actData['TITLE']);
						}

                        if (intval($_REQUEST['activId']) > 0) {
                            D('ActiveBudget')->onActiveChangeSuccess($CID);
                        }
                             
						js_alert('备案成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('备案失败');
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
				js_alert('暂无权限');
			}
			
			$form=$workflow->createHtml();
			if($_REQUEST['savedata'])
			{
				$project_model = D('Project');
				
				$pstatus = $project_model->get_Change_Flow_Status($_REQUEST['RECORDID']);

				if($pstatus == '1')
				{
					js_alert('请勿重复提交哦',U('Activ/opinionFlowChange',$this->_merge_url_param));
				}
				else
				{
					$_REQUEST['type'] = $_REQUEST['flowType'];
					$str = $workflow->createworkflow($_REQUEST);
					if($str){

						js_alert('提交成功',U('Activ/opinionFlowChange',$this->_merge_url_param));
					}else{
						js_alert('提交失败',U('Activ/opinionFlowChange',$this->_merge_url_param));
					}
				}
            }
        }
        
        $this->assign('form', $form);
		$this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlowChange');
    }
    
    //项目下活动决算
	/*public function activSummery(){       
        //案例Model
        $project_case_model = D("ProjectCase");
        //成本填充MOdel
        $cost_supplement_model = D("CostSupplement");
        //项目成本Model
        $cost_model = D("ProjectCost");
        
        //状态标识
        $cost_supplement_status_remark = $cost_supplement_model->get_cost_supplement_status_remark();
        //状态
        $cost_supplement_status = $cost_supplement_model->get_cost_supplement_status();
        $cost_sup_type_status = $cost_supplement_model->get_cost_sup_type();
        
        $prjid = $_REQUEST["prjid"];
        
        $case_info = $project_case_model->get_info_by_pid($prjid,"ds",array("ID"));
        
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
		$showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
        $id = isset($_GET['ID']) ? strip_tags($_GET['ID']) : 0;
        
        $activeid = isset($_REQUEST["parentchooseid"]) ? intval($_REQUEST["parentchooseid"]) : 0;        
        $active_info = D("Erp_activities")->where("ID = $activeid")->field(array("CASE_ID","BUSINESSFEE","STATUS"))->find();
        if($faction == "saveFormData" && $showForm == 3 && $id == 0)//新增
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
                $result["msg"] = "新增失败！填充的总金额必须等于决算的剩余总金额";
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
            //var_dump($sup_data);die;
            $insertid = $cost_supplement_model->add_cost_supplement_info($sup_data);
            if($insertid)
            {
                $result["status"] = 2;
                $result["msg"] = "新增成功！";               
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "新增失败！";
            }
            
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;

        }
        else if($faction == "saveFormData" && $showForm == 1 && $id > 0)//编辑
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
                $result["msg"] = "修改失败！填充的总金额必须等于决算的剩余总金额";
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
            if($up_num)
            {
                $result["status"] = 1;
                $result["msg"] = "修改成功！";
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "修改失败，请重试！";
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        else if($faction == "delData" && $id > 0)//删除
        {
            $del_num = $cost_supplement_model->del_cost_supplement_info_by_ids($id);
            if($del_num)
            {
                $result["status"] = 2;
                $result["msg"] = "删除成功！";               
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "删除失败！";
            }
            
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;           
        }
         
        //我方已开销的资金池费用
        //$sql = "SELECT SUM(FEE) SUM_FUNDPOOL_COST FROM ERP_COST_LIST WHERE CASE_ID = ".$active_info["CASE_ID"]." AND ISFUNDPOOL=1";
        //$cost_fundpool_fee = M()->query($sql);
        //$myfee = $cost_fundpool_fee ? $cost_fundpool_fee[0]["SUM_FUNDPOOL_COST"] : 0;
        
        //开发商支付的资金池费用
        //$businessfee = $active_info["BUSINESSFEE"] ? $active_info["BUSINESSFEE"] : 0;

        Vendor('Oms.Form');			
        $form = new Form(); 
        $cond_where = "ENTITY_ID = ".$activeid;
        $form->initForminfo(183)->where($cond_where);
        if($showForm > 0 )
        {   
            //费用类型(树形结构)
            $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
                    . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 and ISONLINE=0', FALSE);
        }
        else
        {   
            //费用类型
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 and ISONLINE=0', FALSE);
        }
        
        //设置按钮显示
        $form->DELCONDITION = "%STATUS% == 1";
        $form->EDITCONDITION = "%STATUS% == 1";
        
        //是否资金池
        $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '是', 0 => '否')), FALSE)
                    ->setMyFieldVal('IS_FUNDPOOL', "1", true)
                    ->setMyFieldVal('STATUS', "1",TRUE);

        //是否扣非
        $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar(array(1 => '是', 0 => '否')), FALSE);
        
        //设置状态
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($cost_supplement_status_remark), FALSE);
        
        $form->GABTN = "<a id = 'add_costsupplement' href = 'javascript:;'>新增</a>"
                    . "<a href='javascript:;' onclick='applyReimburse()'>提交财务报销</a>";
        $form->FKFIELD = "";    
        $form = $form->getResult();
        $this->assign("form",$form);
        $this->assign("paramUrl",$this->_merge_url_param);
        $this->assign("myfee",$myfee);
        $this->assign("businessfee",$businessfee);
        $this->display("activSummeryX");
    }*/
    
    //生成报销单
    public function applyReim()
    {
        
        $prjid = isset($_REQUEST["prjid"]) ? intval($_REQUEST["prjid"]) : 0;
        //$this->project_auth($prjid,array(1,2));
        $cost_sup_id = isset($_REQUEST["cost_sup_id"]) ? $_REQUEST["cost_sup_id"] : "";
        
        //报销单Model
        $reim_list_model = D("ReimbursementList");
        //报销明细Model
        $reim_detail_model = D("ReimbursementDetail");
        //报销类型MOdel
        $reim_type_model = D("ReimbursementType");
        //成本填充Model
        $cost_supplement_moel = D("CostSupplement");
        //项目Model
        //$project_model = D("Project");        
        //$project_info = $project_model->get_info_by_id($prjid,array("TLF_PROJECT_ID"));
        //判断是否是资金池项目
        $house_info = D("Erp_house")->field(array("ISFUNDPOOL"))->where("PROJECT_ID =".$prjid)->find();
        $isfundpull = $house_info["ISFUNDPOOL"];
        if( $isfundpull == 1 )
        {
            $result['state'] = 0;
            $result["msg"] = "对不起，该项目不是资金池项目，不符合申请条件！";
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit();
        }
        
        
        //报销单状态数组
        $reim_list_status = $reim_list_model->get_conf_reim_list_status();
        
        //报销单状态标志数组
        $reim_list_status_remark = $reim_list_model->get_conf_reim_list_status_remark();
        
        //报销明细状态数组
        $reim_detail_status = $reim_detail_model->get_conf_reim_detail_status();
        
        //成本填充状态
        $cost_supplement_status = $cost_supplement_moel->get_cost_supplement_status();
        
        //判断所选记录是否已经申请报销
        $cost_sup_id_str = implode(",", $cost_sup_id);
        $cond_where = "ID IN($cost_sup_id_str) AND STATUS > 1";
        $is_reim = $cost_supplement_moel->get_cost_supplement_info_by_cond($cond_where,array("ID"));
        
        if($is_reim)
        {
            $result['state'] = 0;
            $result["msg"] = "您所选的记录中包含已经申请报销的，不能重复申请，请重新选择！";
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit();
        }
        
        //报销类型数组
        $reim_type = $reim_type_model->get_reim_type();       
        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);
        //当前用户姓名
        $user_truename = $_SESSION['uinfo']['tname'];
        //当前城市编号
        $city_id = intval($this->channelid);
        
        //报销单类型
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

        //判断该用户 下是否存在该类型且尚未提交的报销申请
        $last_id = $reim_list_model->get_last_reim_list($uid, $reimburse_type, $city_id);
        $last_id = $last_id["ID"];
        //var_dump($last_id);die;
        $this->model->startTrans();
        if( !$last_id)//没找到最新的报销单新建一张报销单
        {
            //生成新的报销单             
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
        
        //加入报销明细表失败的记录编号
        $fail_row = "";
        //加入报销明细表成功的记录编号
        $success_row = "";
        
        //添加新的报销明细
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
                //修改成本填充状态为申请中 报销单状态为已提交
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
            $result["msg"] = "申请报销失败！！！";
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
                $result["msg"] = "编号为 ".$fail_row." 的记录申请失败，编号为 ".$success_row." 记录申请成功";
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit();
            }
            else
            {
                $this->model->commit();
                $result['state'] = 1;
                $result["msg"] = "申请报销成功";
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit();
            }
        }
        
    }
    
    //报销单
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
                        array('报销明细',U('/Activ/reimDetail',$this->_merge_url_param)),
                        array('关联借款',U('/Activ/loanMoney',$this->_merge_url_param)),
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
    
    //获取电商下活动的决算金额
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
        //开发商支付的资金池费用
        $businessfee = $activ_info["BUSINESSFEE"];

        //我方已开销的资金池费用(财务已报销的资金池费用)
        //$sql = "SELECT SUM(FEE) SUM_FUNDPOOL_COST FROM ERP_COST_LIST WHERE CASE_ID = ".$activ_info["CASE_ID"]." AND ISFUNDPOOL=1";
        $sql = "SELECT SUM(MONEY)SUM_FUNDPOOL_COST FROM ERP_REIMBURSEMENT_DETAIL A WHERE A.CASE_ID =".$case_id.
            " AND A.ISFUNDPOOL=1 AND A.STATUS=1";
        $cost_fundpool_fee = M()->query($sql);
        $myfee = $cost_fundpool_fee[0]["SUM_FUNDPOOL_COST"] ? $cost_fundpool_fee[0]["SUM_FUNDPOOL_COST"] : 0;

        //需要决算的费用为
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