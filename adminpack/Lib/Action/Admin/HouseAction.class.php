<?php

class HouseAction extends ExtendAction {

    const HIDDEN_FORM_COLUMN = 0;

    private $model;
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();

    private $isedit;

    /**
     * 需检查预算费用是否存在的项目
     * @var array
     */
    protected $needCheckFees = array(self::DS, self::FX, self::FWFSC);

    //构造函数
    public function __construct() {
        $this->model = new Model();
        parent::__construct();

        //TAB URL参数
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : $_REQUEST['RECORDID'];
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['benefits_id']) ? $this->_merge_url_param['benefits_id'] = $_GET['benefits_id'] : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
        !empty($_GET['CHANGE']) ? $this->_merge_url_param['CHANGE'] = $_GET['CHANGE'] : '';
        !empty($_GET['CID']) ? $this->_merge_url_param['CID'] = $_GET['CID'] : '';

        !empty($_GET['active']) ? $this->_merge_url_param['active'] = $_GET['active'] : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
        !empty($_GET['type']) ? $this->_merge_url_param['type'] = $_GET['type'] : '';
        !empty($_GET['flowType']) ? $this->_merge_url_param['flowType'] = $_GET['flowType'] : '';
        !empty($_GET['tabNum']) ? $this->_merge_url_param['tabNum'] = $_GET['tabNum'] : 0;
		  !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
        if ($_GET['flowType'] == 'lixiangbiangeng') {
            $this->_merge_url_param['prjid'] = $_GET['CASEID'];
            $this->_merge_url_param['CID'] = $_GET['RECORDID'];
        }
        $this->isedit = false;
        if ($_REQUEST['flowId']) {
            if (judgeFlowEdit($_REQUEST['flowId'], $_SESSION['uinfo']['uid'])) {//判断是否回到发起人
                $this->isedit = true;
            }
        }

    }

    /**
     * 隐藏资金池相关字段
     * @param $form
     */
    private function hiddenFundPool(&$form) {
        // 是否资金池费用
        $form->setMyField('ISFUNDPOOL', 'FORMVISIBLE', self::HIDDEN_FORM_COLUMN);

        // 特殊资金池描述
        $form->setMyField('SPECIALFPDESCRIPTION', 'FORMVISIBLE', self::HIDDEN_FORM_COLUMN);

        // 资金池比例
        $form->setMyField('FPSCALE', 'FORMVISIBLE', self::HIDDEN_FORM_COLUMN);
        $form->setMyField('FPSCALE', 'GRIDVISIBLE', self::HIDDEN_FORM_COLUMN);
    }

    //详情
    function projectDetail() {
        $prjId = $this->_merge_url_param['prjid'];//$_REQUEST['prjid'];
        $this->project_pro_auth($prjId, $_REQUEST['flowId']);
        $project = D('Erp_project')->where("ID=$prjId")->find();

        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(114);
        if ($_REQUEST['CHANGE'] == -1) {//变更状态

            $form->setMyField('CIT_ID', 'READONLY', '-1', false);
            $form->setMyField('CONTRACT_NUM', 'READONLY', '-1', false);
            $form->setMyField('REL_PROPERTY', 'READONLY', '-1', false);

            $form->changeRecord = true;
            $form->changeRecordVersionId = $_REQUEST['CID'];
            if ($_REQUEST['active'] == '1') {
                $form->FormeditType = 2;//查看状态
            }
        } else {
            if ($project['PSTATUS'] > 2) $form->FormeditType = 2;
        }
        if ($_REQUEST['flowId']) {
            if (judgeFlowEdit($_REQUEST['flowId'], $_SESSION['uinfo']['uid'])) {//判断是否回到发起人
                $form->FormeditType = 1;//可以编辑状态

            }
        }

        // 是否为非我方收筹项目
        if ($project['SCSTATUS'] !== null && $project['SCSTATUS'] >= 1 && $project['MSTATUS'] === null) {
            $this->hiddenFundPool($form);
        }

        //分销项目新增是否有其他收入字段
        if($project['MSTATUS'] !== null && $project['MSTATUS'] >=1){
            $form->setMyField("OTHERINCOME","FORMVISIBLE",-1);
        }
        $one = D('Erp_house')->where("PROJECT_ID ='$prjId'")->find();
        if ($one) {
            $_REQUEST['ID'] = $one['ID'];
            //$form->where('ID='.$one['ID'] );
        } else {

            $form->setMyFieldVal('CUSTOMER_MAN', $project['CUSER'], true);
        }

        $form->setMyFieldVal('PROJECT_ID', $prjId, true);
        $form->setMyfield('ISAGENTCARD', 'FORMVISIBLE', -1, false);
        $form->setMyfield('FILES', 'FORMVISIBLE', -1, false);

        $form->hidden_input_arr = array(
            array(
                'name' => 'FORNANJING',
                'value' => '',
                'id' => 'FORNANJING'
            ),
            array(
                'name' => 'REL_NEWHOUSEID',
                'value' => '',
                'id' => 'REL_NEWHOUSEID'
            )
        );

        if ($_REQUEST['faction'] == 'saveFormData') {
            /***判断合同是否上传***/
            if ($_POST['CONTRACT_FILE'] == '') {
                $result['status'] = 0;
                $result['msg'] = g2u('保存失败，项目合同相关附件必须上传!');

                echo json_encode($result);
                exit;
            }

            //判断新建项目名称是否重复，但已删除和立项状态已终止的项目，项目名称可以重复
            $projectname = $_REQUEST['PRO_NAME'] ? u2g($_REQUEST['PRO_NAME']):"";
            $cityID = $_REQUEST['CIT_ID'];
            if (!empty($_REQUEST['PRO_NAME_OLD']) && $_REQUEST['PRO_NAME'] != $_REQUEST['PRO_NAME_OLD']) {
                $sql = "";
                $sql = "select * from erp_project where id=".$prjId;
                $project_arr =  D()->query($sql);
                foreach($project_arr as $projects){
                    if($projects['BSTATUS'] >=1 ){
                        $bsql = "select PROJECTNAME from erp_project where BSTATUS >=1 and PSTATUS !=5 and STATUS !=2 and CITY_ID = ".$cityID ."and ID !=".$prjId;
                        $ds_projects = D()->query($bsql);
                        foreach($ds_projects as $ds_project){
                            if($projectname == $ds_project['PROJECTNAME']){
                                $result['status'] = 0;
                                $result['msg'] = g2u('本业务下已经有此项目名称，请更换');
                                echo json_encode($result);
                                exit;
                            }
                        }
                    }
                    if($projects['MSTATUS'] >=1){
                        $bsql = "select PROJECTNAME from erp_project where MSTATUS >=1 and PSTATUS !=5 and STATUS !=2 and CITY_ID = ".$cityID."and ID !=".$prjId;
                        $ds_projects = D()->query($bsql);
                        foreach($ds_projects as $ds_project){
                            if($projectname == $ds_project['PROJECTNAME']){
                                $result['status'] = 0;
                                $result['msg'] = g2u('本业务下已经有此项目名称，请更换');
                                echo json_encode($result);
                                exit;
                            }
                        }
                    }
                    if($projects['SCSTATUS'] >=1 ){
                        $bsql = "select PROJECTNAME from erp_project where SCSTATUS >=1 and PSTATUS !=5 and STATUS !=2 and CITY_ID = ".$cityID."and ID !=".$prjId;
                        $ds_projects = D()->query($bsql);
                        foreach($ds_projects as $ds_project){
                            if($projectname == $ds_project['PROJECTNAME']){
                                $result['status'] = 0;
                                $result['msg'] = g2u('本业务下已经有此项目名称，请更换');
                                echo json_encode($result);
                                exit;
                            }
                        }
                    }
                }
            }

            if ($project['PSTATUS'] == 2 || $project['PSTATUS'] == 6) {
                $temp['CONTRACT'] = $this->_post('CONTRACT_NUM');
                $_POST['PRO_NAME'] = htmlspecialchars($_POST['PRO_NAME']);
                $temp['PROJECTNAME'] = iconv('UTF-8', 'GBK', $_POST['PRO_NAME']);
                $temp['COMPANY'] = iconv('UTF-8', 'GBK', $_POST['DEV_ENT']);
                D('Erp_project')->where("ID=$prjId")->save($temp);
            }
        }
        $form->FORMFORWARD = U('House/projectDetail', $this->_merge_url_param);  //保存后跳转
        $form = $form->getResult();

        $this->assign('project', $project);
        $this->assign('prjId', $prjId);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('form', $form);
        $this->display('projectDetail');
    }
	//修改客户经理
    function projectDetail2() {
		if($_REQUEST['ID']){
			$house = D('Erp_house')->where("ID ='".$_REQUEST['ID']."'")->find();
			
		}elseif($_REQUEST['prjid']){
			$house = D('Erp_house')->where("PROJECT_ID ='".$_REQUEST['prjid']."'")->find();
			$_REQUEST['ID'] = $house['ID'];
			 

		}
		$this->_merge_url_param['prjid'] = $house['PROJECT_ID'];
		//$this->_merge_url_param['ID'] = $house['ID'];
        //$prjId = $this->_merge_url_param['ID'];//$_REQUEST['prjid'];
        $this->project_pro_auth($prjId, $_REQUEST['flowId']);
        //$project = D('Erp_project')->where("ID=$prjId")->find();
		
        Vendor('Oms.Form');
        $form = new Form();
		if($_REQUEST['prjid']) {
			$_REQUEST['showForm'] = 1;
		}
		//$form->PKVALUE = $_REQUEST['ID'];
        $form = $form->initForminfo(202);
         

         
 

         

        if ($_REQUEST['faction'] == 'saveFormData') {
           
           // if ($project['PSTATUS'] == 2 || $project['PSTATUS'] == 6) {
                $temp['CUSER'] = $this->_post('CUSTOMER_MAN');
                
                D('Erp_project')->where("ID=".$this->_post('PROJECT_ID'))->save($temp);
           // }
        }
		if ($_REQUEST['ID'] && $_REQUEST['showForm'] == 1) {
			//$CUSTOMER_MAN = addslashes(u2g($form->getSelectTreeOption('CUSTOMER_MAN', '', -1))); 
		}
        $form = $form->getResult();

        $this->assign('project', $project);
        //$this->assign('prjId', $prjId);
		$this->assign('CUSTOMER_MAN', $CUSTOMER_MAN);
        $this->assign('paramUrl', $this->_merge_url_param);
        if($_REQUEST["ID"] || $_REQUEST["prjid"]) $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('form', $form);
        $this->display('projectDetail2');
    }

    //立项预算
    function projectBudget() {
        $prjId = $_REQUEST['prjid'];
        // 将项目执行终止日期改为一天的23:59:59
        if ($_REQUEST['TODATE'] != '') {
            //if (strlen($_REQUEST['TODATE']) < 18) {
               // $_REQUEST['TODATE'] = sprintf('%s %s', $_REQUEST['TODATE'], '23:59:59');
                //$_POST['TODATE'] = sprintf('%s %s', $_POST['TODATE'], '23:59:59');
            //}
        }
        $this->project_pro_auth($prjId, $_REQUEST['flowId']);
        $project = D('Erp_project')->where("ID=$prjId")->find();
        $house = M('Erp_house')->where("PROJECT_ID=$prjId")->find();

        Vendor('Oms.Form');
        $form = new Form();
        $prjId = $_REQUEST['prjid'];
        $clist = D('Erp_case')->where("PROJECT_ID=$prjId ")->select();
        foreach ($clist as $v) {
            $temp[] = $v['ID'];
        }
        $ids = implode(',', $temp);

        $form->initForminfo(127);
        $form->where("CASE_ID in ($ids)");
        $form->setMyFieldVal('FPSCALE', $house['FPSCALE'], true);
        $form->setMyFieldVal('PROJECT_ID', $_REQUEST['prjid'], true);

        if ($this->_get('CHANGE') == -1) {

            $form->ADDABLE = 0;
            $form->changeRecord = true;
            $form->changeRecordVersionId = $_REQUEST['CID'];

            if ($_REQUEST['active'] == '1' && $this->isedit == false) $form->CZBTN = ' ';
            $hchange = M('Erp_changelog')->where("CID='" . $_REQUEST['CID'] . "' and BID='" . $house['ID'] . "' and TABLEE='ERP_HOUSE' and COLUMS='FPSCALE'")->find();
        } else {
            $form->ADDABLE = 0;
            if ($project['PSTATUS'] > 2 && $this->isedit == false) $form->CZBTN = ' ';
        }

        // 是否为非我方收筹项目
        if ($project['SCSTATUS'] !== null && $project['SCSTATUS'] >= 1 && $project['MSTATUS'] === null) {
            $this->hiddenFundPool($form);
        }

        $form->GCBTN = $form->GCBTN . '<a href="javascript:;" onclick="budgetfeetotal();" class="btn btn-danger btn-sm">综合统计</a>';
        if ($_REQUEST['showForm'] && $_REQUEST['ID']) {//编辑

            $form->setMyField('CASE_ID', 'ISVIRTUAL', -1, true);
            $form->setMyField('SCALETYPE', 'READONLY', -1, true);
			$form->FORMFORWARD = __APP__."/House/projectBudget/prjid/$prjId/CHANGE/".$this->_get("CHANGE")."/CID/".$this->_get("CID")."/active/".$this->_get('active')."/tabNum/".$_REQUEST['tabNum']."/SELECTID/".$_REQUEST['ID'].'/CASEID/$prjId/flowId/'.$_REQUEST['flowId'].'/RECORDID/'.$_REQUEST['RECORDID'].'/type/'.$_REQUEST['type'].'flowType/'.$_REQUEST['flowType'];
        } elseif ($_REQUEST['showForm']) {//新增
            if ($_REQUEST['faction'] == 'saveFormData') {
                $cdata['CTIME'] = date('Y-m-d H:i:s', time());
                $cdata['CUSER'] = $_SESSION['uinfo']['uid'];
                $cdata['SCALETYPE'] = $_REQUEST['SCALETYPE'];
                $cdata['PROJECT_ID'] = $prjId;
                $caseid = D('Erp_case')->add($cdata);
                if ($caseid) $form->setMyFieldVal('CASE_ID', $caseid, true);
                else {
                    js_alert('用户添加失败！');
                    exit();
                }
            }
        }
        $form->DELABLE = 0;
        //echo $form->getFilter();
		
        $cparam = '&operate=' . $_GET['operate'] . '&flowId=' . $_GET['flowId'].'&stemd='.time();
        $children = array(
            array('预算费用', U('/House/budGetFee?CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID") . '&active=' . $this->_get('active') . $cparam)),
            array('单套收费标准', U('/House/feescale?SCALETYPE=1&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('中介佣金标准', U('/House/feescale?SCALETYPE=2&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            //array('置业顾问佣金标准',U('/House/feescale?SCALETYPE=3')),
            array('中介成交奖标准', U('/House/feescale?SCALETYPE=4&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('置业顾问成交奖标准', U('/House/feescale?SCALETYPE=5&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('带看奖标准', U('/House/feescale?SCALETYPE=6&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
        );

        // 如果是单独的非我方收筹项目，则去掉“单套收费标准”页面

        if ($project['SCSTATUS'] !== null && $project['SCSTATUS'] >= 1 && $project['MSTATUS'] === null) {
            $children = array(
                array('预算费用', U('/House/budGetFee?CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID") . '&active=' . $this->_get('active') . $cparam)),
                array('中介佣金标准', U('/House/feescale?SCALETYPE=2&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
                array('中介成交奖标准', U('/House/feescale?SCALETYPE=4&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
                array('置业顾问成交奖标准', U('/House/feescale?SCALETYPE=5&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
                array('带看奖标准', U('/House/feescale?SCALETYPE=6&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            );
        }
        //如果是电商或分销业务，则添加“外部奖励标准”页面，非我方收筹没有外部奖励标准
        if(($project['MSTATUS'] !== null && $project['MSTATUS'] >=1) || ($project['BSTATUS'] !== null && $project['BSTATUS'] >=1))  {
            $children = array(
            array('预算费用', U('/House/budGetFee?CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID") . '&active=' . $this->_get('active') . $cparam)),
            array('单套收费标准', U('/House/feescale?SCALETYPE=1&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('中介佣金标准', U('/House/feescale?SCALETYPE=2&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('外部成交奖励', U('/House/feescale?SCALETYPE=3&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('中介成交奖标准', U('/House/feescale?SCALETYPE=4&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('置业顾问成交奖标准', U('/House/feescale?SCALETYPE=5&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('带看奖标准', U('/House/feescale?SCALETYPE=6&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            );
        }
        $form->setChildren($children);
        $form = $form->getResult();
        $this->assign('form', $form);
        $this->assign('hchange', $hchange);
		$this->assign('SELECTID', $_REQUEST['SELECTID']);
        $this->assign('prjid', $prjId);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->display('projectBudget');
    }

    //预算费用
    function budGetFee() {

        $model = new Model();
        if ($this->_get('parentchooseid')) {
            $casedata = $model->query("select PROJECT_ID,SUMPROFIT,B.SCALETYPE from ERP_PRJBUDGET A left join ERP_CASE B on A.CASE_ID=B.ID where A.ID=" . $this->_get('parentchooseid'));

            // 如果存在变更状态，则先从变更表中获取数据
            if ($this->_request('CHANGE') == -1 && isset($_REQUEST['CID'])) {
                $sql = "SELECT t.valuee SUMPROFIT FROM ERP_CHANGELOG t WHERE t.CID = {$_REQUEST['CID']} AND t.COLUMS = 'SUMPROFIT' and t.BID=". $this->_get('parentchooseid');
                $changeLog = $model->query($sql);
                if (is_array($changeLog) && count($changeLog)) {
                    $estimate_money = $changeLog[0]['SUMPROFIT'];
                }
            }

            if ($casedata[0]) {
                $project = D('Erp_project')->where("ID=" . $casedata[0]['PROJECT_ID'])->find();
                // 如果没有变更，则从原表取数据
                if ($estimate_money === null) {
                    $estimate_money = $casedata[0]['SUMPROFIT'];//预估总收益
                }
            }
        }

        $house = M('Erp_house')->where("PROJECT_ID=$project[ID]")->find();
        Vendor('Oms.Changerecord');
		$upcounts = 0;
        $changer = new Changerecord();
        $changer->fields = array('REMARK', 'AMOUNT', 'RATIO');
        //$changer->fields=array('AMOUNT','RATIO');
        $ajaxReturnIDs = array();  // 异步返回ID
        if ($this->_post('postfee') == 'save') {
            $feelist = D('Erp_fee')->where("ISVALID=-1")->select();
            foreach ($feelist as $k => $v) {
                $temp = array();
                $temp['AMOUNT'] = $this->_post($v['ID'] . '_AMOUNT');
                $temp['RATIO'] = (float)$this->_post($v['ID'] . '_RATIO');
                $temp['REMARK'] =  $this->_post($v['ID'].'_REMARK') ;
                $temp['ISONLINE'] = $v['ISONLINE'];
                $ID = $this->_post($v['ID'] . '_ID');
                $STATUS = $this->_post($v['STATUS'] . '_STATUS');

                if ($ID) {
                    //if($this->_post($v['ID'].'_AMOUNT')){
                    //$temp['ISVALID'] = $this->_post('CHANGE') == -1?0:-1;
                    if ($this->_post('CHANGE') == -1) {
                        $temp['AMOUNT_OLD'] = (float)$this->_post($v['ID'] . '_AMOUNT_OLD');
                        $temp['RATIO_OLD'] = (float)$this->_post($v['ID'] . '_RATIO_OLD');
                        $temp['REMARK_OLD'] = $_POST[$v['ID'] . '_REMARK_OLD']; //$this->_post($v['ID'].'_REMARK_OLD') ;

                       if( is_null($temp['AMOUNT_OLD'])) {
                            $device['ISNEW'] = -1;
                            $device['TABLE'] = 'ERP_BUDGETFEE';
                            $device['BID'] = $ID;
                            $device['CID'] = $this->_post('CID');
                            $device['APPLICANT'] = $_SESSION['uinfo']['uid'];//var_dump($temp);
                            $update = D("Erp_budgetfee")->where("ID = $ID")->save($temp);
                        }else  {
                            $device['TABLE'] = 'ERP_BUDGETFEE';
                            $device['BID'] = $ID;
                            $device['CID'] = $this->_post('CID');
                            $device['CDATE'] = date('Y-m-d h:m:s');
                            $device['APPLICANT'] = $_SESSION['uinfo']['uid'];
                            $device['ISNEW'] = 0;
                        }
                        $resss = $changer->saveRecords($device, $temp); 
						if( $resss) $upcounts++;

                    } else {
                        $update = D("Erp_budgetfee")->where("ID = $ID")->save($temp);
						

                    }
                    //$update = D("Erp_budgetfee")->where("ID = $ID")->save($temp);

                    /*}else{

                        if($this->_post('CHANGE') == -1){//？、
                            $delete = D("Erp_budgetfee")->where("ID = $ID and ISVALID = 0 ")->delete();
                            $delLog = D("Erp_changelog")->where("bid = $ID and cid = {$this->_post('CID')}")->delete();
                        }else{
                            $delete = D("Erp_budgetfee")->where("ID = $ID and ISVALID = -1 ")->delete();
                        }
                    }*/

                } else {
                    if ($this->_post($v['ID'] . '_AMOUNT')) {
                        $temp['ISVALID'] = $this->_post('CHANGE') == -1 ? 0 : -1;
                        $temp['ADDTIME'] = date('Y-m-d h:m:s');
                        $temp['FEEID'] = $v['ID'];
                        $temp['BUDGETID'] = $this->_post('BUDGETID');

                        $insertId = D("Erp_budgetfee")->add($temp);
                        $ajaxReturnIDs[$v['ID']] = $insertId;
                        if ($this->_post('CHANGE') == -1) {
                            $temp['AMOUNT_OLD'] = $this->_post($v['ID'] . '_AMOUNT_OLD');
                            $temp['RATIO_OLD'] = (float)$this->_post($v['ID'] . '_RATIO_OLD');
                            $temp['REMARK_OLD'] = $_POST[$v['ID'] . '_REMARK_OLD'];//$this->_post($v['ID'].'_REMARK_OLD') ;

                            $device['TABLE'] = 'ERP_BUDGETFEE';
                            $device['BID'] = $insertId;
                            $device['CID'] = $this->_post('CID');
                            $device['CDATE'] = date('Y-m-d h:m:s');
                            $device['APPLICANT'] = $_SESSION['uinfo']['uid'];
                            $device['ISNEW'] = -1;

                            $resss =$changer->saveRecords($device, $temp);
							if( $resss) $upcounts++;
                        }
						
                    }
                }
            }
            if ($this->_post('CHANGE') != -1) {

                $budget = D('Budget');
                $budget->update_statistics($this->_get('parentchooseid'));
                $buddata['OFFLINE_COST_SUM'] = $this->_post('108_AMOUNT');
                $buddata['OFFLINE_COST_SUM_PROFIT'] = $this->_post('109_AMOUNT');
                $buddata['OFFLINE_COST_SUM_PROFIT_RATE'] = $this->_post('110_AMOUNT');
                $buddata['PRO_TAXES'] = $this->_post('101_AMOUNT');
                $buddata['PRO_TAXES_PROFIT'] = $this->_post('102_AMOUNT');
                $buddata['PRO_TAXES_PROFIT_RATE'] = $this->_post('103_AMOUNT');
                $buddata['ONLINE_COST'] = $this->_post('106_AMOUNT');
                $buddata['ONLINE_COST_RATE'] = $this->_post('107_AMOUNT');
                $budget->set_budgetfee($this->_post('BUDGETID'), $buddata);//保存到预算表
            }

            // 如果是异步调用
            if ($_REQUEST['is_ajax'] == 1) {
                echo json_encode(array(
                    'code' => 'ok',
                    'data' => $upcounts
                ));
                exit;
            }
        }

        $return_tr = array(
            '39' => '<td rowspan="46">费用类别—线下</td><td>经纪服务费</td><td colspan="2">中介费</td>',
            '41' => '<td rowspan="2">数据营销费</td><td colspan="2">短信费</td>',
            '42' => '<td colspan="2">电话费</td>',
            '45' => '<td rowspan="9">渠道费</td><td rowspan="3">场地费</td><td>超市/商场</td>',
            '46' => '<td>进小区</td>',
            '47' => '<td>写字楼</td>',
            '49' => '<td rowspan="2">租车费(载人)</td><td>大巴车</td>',
            '50' => '<td>出租车</td>',
            '51' => '<td colspan="2">运输费(载物)</td>',
            '53' => '<td>推广费</td><td>SEO/SEM推广</td>',
            '54' => '<td colspan="2">案场暖场费</td>',
            '55' => '<td colspan="2">网友食品费</td>',
            '57' => '<td rowspan="2">人员工资</td><td colspan="2">公司员工</td>',
            '58' => '<td colspan="2">兼职人员</td>',
            '60' => '<td rowspan="4">业务费</td><td colspan="2">业务津贴</td>',
            '61' => '<td colspan="2">其他费用</td>',
            '62' => '<td colspan="2">实际应酬</td>',
            '63' => '<td colspan="2">差旅费</td>',
            '65' => '<td rowspan="4">制作费</td><td colspan="2">宣传品</td>',
            '66' => '<td colspan="2">布展费</td>',
            '67' => '<td colspan="2">单页</td>',
            '68' => '<td colspan="2">X展架</td>',
            '70' => '<td rowspan="5">外部广告费</td><td colspan="2">大牌</td>',
            '71' => '<td colspan="2">LED</td>',
            '72' => '<td colspan="2">公交/地铁</td>',
            '73' => '<td colspan="2">电台</td>',
            '74' => '<td colspan="2">报纸/杂志</td>',
            '76' => '<td rowspan="4">宣传费</td><td colspan="2">网友</td>',
            '77' => '<td colspan="2">置业顾问</td>',
            '78' => '<td colspan="2">客户</td>',
            '79' => '<td colspan="2">其他</td>',
            '80' => '<td colspan="3">支付第三方费用</td>',
            '82' => '<td>项目分成</td><td colspan="2">利润分成</td>',
            '84' => '<td rowspan="4">带看费</td><td colspan="2">老带新</td>',
            '85' => '<td colspan="2">新带新</td>',
            '86' => '<td colspan="2">中介带看</td>',
            '87' => '<td colspan="2">渠道带看</td>',
            '89' => '<td>成交费</td><td colspan="2">成交奖励</td>',
            '91' => '<td>内部佣金</td><td colspan="2">内部提成</td>',
            '93' => '<td>外部佣金</td><td colspan="2">外部奖励</td>',
            '95' => '<td>POS手续费</td><td colspan="2">POS手续费</td>',
            '96' => '<td colspan="3">税金(支付第三方费用的10%)</td>',
            '97' => '<td colspan="3">其他</td>',
            '108' => '<td colspan="3">付现成本</td>',
            '109' => '<td colspan="3">付现利润</td>',
            '110' => '<td colspan="3">付现利润率</td>',

            '101' => '<td rowspan="3">税后项目情况(供参考)</td><td colspan="3">除资金池外项目税金</td>',
            '102' => '<td colspan="3">税后项目利润</td>',
            '103' => '<td colspan="3">税后项目利润率</td>',
            '98' => '<td rowspan="4">费用类别—线上</td><td colspan="3">广告预算（折后价）</td>',
            '99' => '<td colspan="3">地产首页配送广告（折后）</td>',
            '106' => '<td colspan="3">扣除线下+线上支出利润</td>',
            '107' => '<td colspan="3">扣除线下+线上支出利润率</td>',
        );
		if($this->channelid==1){
			$return_tr['98'] = '<td rowspan="4">费用类别—线上</td><td colspan="3">广告预算</td>';
			$return_tr['99'] = '<td colspan="3">地产首页配送广告</td>';
		}
        $noinput_arr = array(101, 102, 103, 106, 107, 108, 109, 110);
        $html = '';
        //电商
        if ($casedata[0]['SCALETYPE'] == 1 ) {
            $model = M();
            if ($_REQUEST['CHANGE'] == '-1') {
                $isrout_data = $model->query("select isroutine({$casedata[0]['PROJECT_ID']} , {$_REQUEST['CID']}, {$_REQUEST['parentchooseid']}) as T from dual");
            } else {
                $isrout_data = $model->query('select isroutine(' . $casedata[0]['PROJECT_ID'] . ') as T from dual');
            }
            $isroutine = $isrout_data[0]['T'];
            if ($isroutine == 1) $isrout_html = '[非常规]';
            else $isrout_html = '[常规]';
        }
        //分销
        if ($casedata[0]['SCALETYPE'] == 2) {
            $model = M();
            if ($_REQUEST['CHANGE'] == '-1') {
                $isrout_data = $model->query("select isfxroutine({$casedata[0]['PROJECT_ID']} , {$_REQUEST['CID']}, {$_REQUEST['parentchooseid']}) as T from dual");
            } else {
                $isrout_data = $model->query('select isfxroutine(' . $casedata[0]['PROJECT_ID'] . ') as T from dual');
            }

            $isroutine = $isrout_data[0]['T'];
            if ($isroutine == 1) $isrout_html = '[非常规]';
            else $isrout_html = '[常规]';
        }
        //非我方收筹
        if ($casedata[0]['SCALETYPE'] == 8) {
            $model = M();
            if ($_REQUEST['CHANGE'] == '-1') {
                $isrout_data = $model->query("select isfwfscroutine({$casedata[0]['PROJECT_ID']} , {$_REQUEST['CID']}, {$_REQUEST['parentchooseid']}) as T from dual");
            } else {
                $isrout_data = $model->query('select isfwfscroutine(' . $casedata[0]['PROJECT_ID'] . ') as T from dual');
            }
            $isroutine = $isrout_data[0]['T'];
            if ($isroutine == 1) $isrout_html = '[非常规]';
            else $isrout_html = '[常规]';
        }

		if($_REQUEST['CHANGE'] == '-1'){
			$list = M('Erp_budgetsale')->where("  PROJECTT_ID=" . $project['ID'])->select();
			foreach($list as $vvv){
				$budgetsaleId[] = $vvv['ID'];
			}
			$bids = implode(',',$budgetsaleId);
			$sets =  $changer->getFieldRecords('ERP_BUDGETSALE',$bids,$_REQUEST['CID'],'SETS' );
			if($sets['VALUEE']!=$sets['ORIVALUEE']){
				$sets =  $sets['VALUEE'].'<span style="color:#f00;">[原]</span>'.$sets['ORIVALUEE'];
			}else{
				$sets =   $sets['ORIVALUEE'];
			}

			$customers =  $changer->getFieldRecords('ERP_BUDGETSALE',$bids,$_REQUEST['CID'],'CUSTOMERS' );
			//$customers =  $customers['VALUEE'].'<span style="color:#f00;">[原]</span>'.$customers['ORIVALUEE'];
			if($customers['VALUEE']!=$customers['ORIVALUEE']){
				$customers =  $customers['VALUEE'].'<span style="color:#f00;">[原]</span>'.$customers['ORIVALUEE'];
			}else{
				$customers = $customers['ORIVALUEE'];
			}
		}else{
			 $sets = M('Erp_budgetsale')->where("ISVALID=-1 and PROJECTT_ID=" . $project['ID'])->sum('SETS');
			$customers = M('Erp_budgetsale')->where("ISVALID=-1 and PROJECTT_ID=" . $project['ID'])->sum('CUSTOMERS');
		}
        
        $isrout_html = $project['PROJECTNAME'] . "<span style='color:#f00;'>" . $isrout_html . "</span>";
        $html = $html . " <table width='90%' cellspacing='0' cellpadding='10' border='1' style='border-collapse: collapse;' align='center'>
			<tr><td colspan='7' align='center'  style=' font-size:18px;'>$isrout_html</td></tr>
			<tr><td colspan='3' rowspan='2'  align='center'  >目标分解</td><td colspan='2' align='center'  >预估成交套数</td><td colspan='2' align='center'  >预估导客量</td></tr>
			<tr> <td colspan='2' align='center'  > " . $sets . "</td><td colspan='2' align='center'  >" . $customers . " </td></tr>

			<tr><td colspan='4'>费用类型</td> <td>金额（元）</td> <td> 	费用占比（%）</td> <td>费用说明</td>  </tr><input type='hidden' name='postfee' value='save'><input type='hidden' name='CHANGE' value='" . $this->_get('CHANGE') . "'><input type='hidden' name='CID' value='" . $this->_get('CID') . "'><input type='hidden' name='BUDGETID' value='" . $this->_get('parentchooseid') . "'>";

        #线下费用
        //$offline_cost = unserialize($row['offline_cost']);
        if ($_REQUEST['CHANGE'] == '-1') {
            $list = D('Erp_budgetfee')->where('BUDGETID=' . $this->_get('parentchooseid'))->order("ID ASC")->select();
            $hchange = M('Erp_changelog')->where("CID='" . $_REQUEST['CID'] . "' and BID='" . $house['ID'] . "' and TABLEE='ERP_HOUSE' and COLUMS='FPSCALE'")->find();
        } else {
            $list = D('Erp_budgetfee')->where('BUDGETID=' . $this->_get('parentchooseid') . 'AND ISVALID=-1')->order("ID ASC")->select();
        }


        $valueArr = array();
        foreach ($list as $v) {
            $valueArr[$v['FEEID'] . '_REMARK'] = $v['REMARK'];
            $valueArr[$v['FEEID'] . '_AMOUNT'] = $v['AMOUNT'];
            $valueArr[$v['FEEID'] . '_RATIO'] = $v['RATIO'];
            $valueArr[$v['FEEID'] . '_ID'] = $v['ID'];
            $valueArr[$v['FEEID'] . '_STATUS'] = $v['STATUS'];
        }

        foreach ($return_tr as $k => $v) {
            $optt['TABLE'] = 'ERP_BUDGETFEE';
            $optt['BID'] = $valueArr[$k . '_ID'];
            $optt['CID'] = $this->_get('CID');//变更版本id

            $changarr = $changer->getRecords($optt);
            //$isonline = $v['ISONLINE']==-1?"isonline='1'":"isonline='0'";
            $isonline = ($k == 99 || $k == 98) ? "isonline='1'" : "isonline='0'";
            /*$amoutOri = $amoutOri?"<font class='orifont'>[". $amoutOri."]</font>" :'';
            $ratioOri = $ratioOri? "<font class='orifont'>[". $ratioOri."%]</font>" :'';
            $remarkOri = $remarkOri ?  "<font class='orifont'>[". $remarkOri."]</font>":'';*/
            $html = $html . "<tr>";
            if ($project['PSTATUS'] > 2 && ($_REQUEST['CHANGE'] != '-1') && $this->isedit == false) {

                //$html = $html.$v."<td>".$valueArr[$k.'_AMOUNT']."</td> <td><span >".$valueArr[$k.'_RATIO']."</span> %</td><td>".$valueArr[$k.'_REMARK']."</td> </tr>";


                //$isonline = ($k==99 || $k==98)?"isonline='1'":"isonline='0'";

                if (in_array($k, $noinput_arr)) {
                    $html = $html . $v . "<td>  <span id='" . $k . "_AMOUNT'   ></span></td> <td> </td><td> </td> </tr>";
                } else {
                    $html = $html . $v . "<td>" . $valueArr[$k . '_AMOUNT'] . " <input name='" . $k . "_AMOUNT' fid='$k' value='" . $valueArr[$k . '_AMOUNT'] . "'  $isonline  type='hidden' class='AMOUNT'/></td> <td><span >" . $valueArr[$k . '_RATIO'] . "</span> % <input fid='$k'  name='" . $k . "_RATIO' $isonline class='RATIO'  value='" . $valueArr[$k . '_RATIO'] . "' type='hidden' /></td><td>" . $valueArr[$k . '_REMARK'] . "</td> </tr>";
                }
                $showbutton = 0;
            } else {

                if ($_REQUEST['CHANGE'] == -1) {

                    $currentAmount = $changarr['AMOUNT'] ? $changarr['AMOUNT']['VALUEE'] : $valueArr[$k . '_AMOUNT'];
                    $oriAmount = $changarr['AMOUNT'] ? $changarr['AMOUNT']['ORIVALUEE'] : '';
                    $infoAmount = $changarr['AMOUNT'] ? ($changarr['AMOUNT']['ISNEW'] ==-1 ? "<span class='fred'>[新增]</span>" : "<span class='fclos fred'>[原]" . $oriAmount . "</span>") : '';
                    $idAmount = $valueArr[$k . '_ID'];

                    $currentRatio = $changarr['RATIO'] ? $changarr['RATIO']['VALUEE'] : $valueArr[$k . '_RATIO'];
                    if (!$currentRatio) $currentRatio = 0;
                    $oriRatio = $changarr['RATIO'] ? $changarr['RATIO']['ORIVALUEE'] : '';
                    $infoRatio = $changarr['RATIO'] ? ($changarr['RATIO']['ISNEW']==-1 ? "<span class='fred'>[新增]</span>" : "<span class='fclos fred'>[原]" . $oriRatio . "</span>") : '';

                    $idRatio = $valueArr[$k . '_ID'];

                    $currentRemark = $changarr['REMARK'] ? $changarr['REMARK']['VALUEE'] : $valueArr[$k . '_REMARK'];
                    $oriRemark = $changarr['REMARK'] ? $changarr['REMARK']['ORIVALUEE'] : '';
                    $infoRemark = $changarr['REMARK'] ? ($changarr['REMARK']['ISNEW'] ==-1? "<span class='fred'>[新增]</span>" : "<span class='fclos fred'>[原]" . $oriRemark . "</span>") : '';


                    if ($_REQUEST['active'] == '1' && $this->isedit == false) {

                        $html = $html . $v . "<td>" . $currentAmount . $infoAmount . "</td> <td><span >" . $currentRatio . $infoRatio . "</span> %</td><td>" . $currentRemark . $infoRemark . "</td> </tr>";

                        $showbutton = 0;
                    } else {
                        if (in_array($k, $noinput_arr)) {

                            $html = $html . $v . "<td> <input name='" . $k . "_AMOUNT' fid='$k' value='" . $currentAmount . "'   $isonline type='text' size='15' datatype='/^-?\d+(\.\d{0,2})?$/' errormsg='请输入数字' ignore='ignore'/><input name='" . $k . "_AMOUNT_OLD'  value='" . $currentAmount . "' type='hidden'  />  " . $infoAmount . " </td> <td> </td><td>   <input name='" . $k . "_ID'  value='" . $valueArr[$k . '_ID'] . "' type='hidden'/> <input name='" . $k . "_STATUS'  value='" . $valueArr[$k . '_STATUS'] . "' type='hidden'/>  </td> </tr>";


                        } else {

                            $html = $html . $v . "<td> <input name='" . $k . "_AMOUNT' fid='$k' value='" . $currentAmount . "' class='AMOUNT' $isonline type='text' size='15' datatype='/^-?\d+(\.\d{0,2})?$/' errormsg='请输入数字' ignore='ignore'/><input name='" . $k . "_AMOUNT_OLD'  value='" . $currentAmount . "' type='hidden'  />  " . $infoAmount . " </td> <td><span >" . $valueArr[$k . '_RATIO'] . "</span> %  <input fid='$k'  name='" . $k . "_RATIO' $isonline class='RATIO'  value='" . $currentRatio . "' type='hidden' /> <input    name='" . $k . "_RATIO_OLD'    value='" . $currentRatio . "' type='hidden' />  " . $infoRatio . " </td><td> <input name='" . $k . "_REMARK' datatype='*' errormsg='输入带有不合法的符号' ignore='ignore' value='" . $currentRemark . "'  type='text' size='20'/> <input name='" . $k . "_REMARK_OLD'  value='" . $currentRemark . "' type='hidden'/>" . $infoRemark . " <input name='" . $k . "_ID'  value='" . $valueArr[$k . '_ID'] . "' type='hidden'/> <input name='" . $k . "_STATUS'  value='" . $valueArr[$k . '_STATUS'] . "' type='hidden'/> </td> </tr>";
                        }

                        $showbutton = -1;
                    }

                } else {
                    if (in_array($k, $noinput_arr)) {
                        $html = $html . $v . "<td> <input name='" . $k . "_AMOUNT' fid='$k' value='" . $valueArr[$k . '_AMOUNT'] . "'  $isonline type='text' size='15' datatype='/^-?\d+(\.\d{0,2})?$/' errormsg='请输入数字' ignore='ignore'/></td> <td> </td><td><input name='" . $k . "_ID' value='" . $valueArr[$k . '_ID'] . "' type='hidden'/>  </td> </tr>";

                    } else {
                        $html = $html . $v . "<td> <input name='" . $k . "_AMOUNT' fid='$k' value='" . $valueArr[$k . '_AMOUNT'] . "' class='AMOUNT' $isonline type='text' size='15' datatype='/^-?\d+(\.\d{0,2})?$/' errormsg='请输入数字' ignore='ignore'/></td> <td><span >" . $valueArr[$k . '_RATIO'] . "</span> %  <input fid='$k'  name='" . $k . "_RATIO' $isonline class='RATIO'  value='" . $valueArr[$k . '_RATIO'] . "' type='hidden' /></td><td> <input name='" . $k . "_REMARK' datatype='*' errormsg='输入带有不合法的符号' ignore='ignore' value='" . $valueArr[$k . '_REMARK'] . "'  type='text' size='20'/><input name='" . $k . "_ID' value='" . $valueArr[$k . '_ID'] . "' type='hidden'/></td> </tr>";
                    }
                    $showbutton = -1;//
                }

            }
        }
        $html = $html . "</table>";
        $this->assign('html', $html);
		$this->assign('reflash', $_REQUEST['reflash']);
        $this->assign('url', $_SERVER['REQUEST_URI']);
        $this->assign('estimate_money', $estimate_money);
        $this->assign('showbutton', $showbutton);
        $this->display('budgetfee');

    }

    //预算费用 综合
    function budGetFeeTotal() {
        $model = new Model();
        $estimate_money = 0;
        if ($this->_get('prjid')) {
            $casedata = $model->query("select A.ID,PROJECT_ID,SUMPROFIT from ERP_PRJBUDGET A left join ERP_CASE B on A.CASE_ID=B.ID where B.PROJECT_ID=" . $this->_get('prjid'));

            $project = D('Erp_project')->where("ID=" . $this->_get('prjid'))->find();


        }


        $return_tr = array(
            '39' => '<td rowspan="46">费用类别—线下</td><td>经纪服务费</td><td colspan="2">中介费</td>',
            '41' => '<td rowspan="2">数据营销费</td><td colspan="2">短信费</td>',
            '42' => '<td colspan="2">电话费</td>',
            '45' => '<td rowspan="9">渠道费</td><td rowspan="3">场地费</td><td>超市/商场</td>',
            '46' => '<td>进小区</td>',
            '47' => '<td>写字楼</td>',
            '49' => '<td rowspan="2">租车费(载人)</td><td>大巴车</td>',
            '50' => '<td>出租车</td>',
            '51' => '<td colspan="2">运输费(载物)</td>',
            '53' => '<td>推广费</td><td>SEO/SEM推广</td>',
            '54' => '<td colspan="2">案场暖场费</td>',
            '55' => '<td colspan="2">网友食品费</td>',
            '57' => '<td rowspan="2">人员工资</td><td colspan="2">公司员工</td>',
            '58' => '<td colspan="2">兼职人员</td>',
            '60' => '<td rowspan="4">业务费</td><td colspan="2">业务津贴</td>',
            '61' => '<td colspan="2">其他费用</td>',
            '62' => '<td colspan="2">实际应酬</td>',
            '63' => '<td colspan="2">差旅费</td>',
            '65' => '<td rowspan="4">制作费</td><td colspan="2">宣传品</td>',
            '66' => '<td colspan="2">布展费</td>',
            '67' => '<td colspan="2">单页</td>',
            '68' => '<td colspan="2">X展架</td>',
            '70' => '<td rowspan="5">外部广告费</td><td colspan="2">大牌</td>',
            '71' => '<td colspan="2">LED</td>',
            '72' => '<td colspan="2">公交/地铁</td>',
            '73' => '<td colspan="2">电台</td>',
            '74' => '<td colspan="2">报纸/杂志</td>',
            '76' => '<td rowspan="4">宣传费</td><td colspan="2">网友</td>',
            '77' => '<td colspan="2">置业顾问</td>',
            '78' => '<td colspan="2">客户</td>',
            '79' => '<td colspan="2">其他</td>',
            '80' => '<td colspan="3">支付第三方费用</td>',
            '82' => '<td>项目分成</td><td colspan="2">利润分成</td>',
            '84' => '<td rowspan="4">带看费</td><td colspan="2">老带新</td>',
            '85' => '<td colspan="2">新带新</td>',
            '86' => '<td colspan="2">中介带看</td>',
            '87' => '<td colspan="2">渠道带看</td>',
            '89' => '<td>成交费</td><td colspan="2">成交奖励</td>',
            '91' => '<td>内部佣金</td><td colspan="2">内部提成</td>',
            '93' => '<td>外部佣金</td><td colspan="2">外部奖励</td>',
            '95' => '<td>POS手续费</td><td colspan="2">POS手续费</td>',
            '96' => '<td colspan="3">税金(支付第三方费用的10%)</td>',
            '97' => '<td colspan="3">其他</td>',
            '108' => '<td colspan="3">付现成本</td>',
            '109' => '<td colspan="3">付现利润</td>',
            '110' => '<td colspan="3">付现利润率</td>',
            '101' => '<td rowspan="3">税后项目情况(供参考)</td><td colspan="3">除资金池外项目税金</td>',
            '102' => '<td colspan="3">税后项目利润</td>',
            '103' => '<td colspan="3">税后项目利润率</td>',
            '98' => '<td rowspan="4">费用类别—线上</td><td colspan="3">广告预算（折后价）</td>',
            '99' => '<td colspan="3">地产首页配送广告（折后）</td>',
            '106' => '<td colspan="3">扣除线下+线上支出利润</td>',
            '107' => '<td colspan="3">扣除线下+线上支出利润率</td>',
        );
        $sets = M('Erp_budgetsale')->where("ISVALID=-1 and PROJECTT_ID=" . $project['ID'])->sum('SETS');
        $customers = M('Erp_budgetsale')->where("ISVALID=-1 and PROJECTT_ID=" . $project['ID'])->sum('CUSTOMERS');
        $noinput_arr = array(101, 102, 103, 106, 107, 108, 109, 110);

        $html = '';
        $html = $html . "<table width='90%' cellspacing='0' cellpadding='10' border='1' style='border-collapse: collapse;' align='center'>
			
			
			<tr><td colspan='3' rowspan='2'  align='center'  >目标分解</td><td colspan='2' align='center'  >预估成交套数</td><td colspan='2' align='center'  >预估导客量</td></tr>
			<tr> <td colspan='2' align='center'  > " . $sets . "</td><td colspan='2' align='center'  >" . $customers . " </td></tr>
			
			<tr><td colspan='4'>费用类型</td> <td>金额（元）</td> <td> 	费用占比（%）</td> <td>费用说明</td>  </tr><input type='hidden' name='postfee' value='save'><input type='hidden' name='CHANGE' value='" . $this->_get('CHANGE') . "'><input type='hidden' name='CID' value='" . $this->_get('CID') . "'><input type='hidden' name='BUDGETID' value='" . $this->_get('parentchooseid') . "'>";

        # 费用
        //$offline_cost = unserialize($row['offline_cost']);
        $valueArr = array();
        foreach ($casedata as $caseone) {
            $estimate_money += $caseone['SUMPROFIT'];//预估总收益

            $list = D('Erp_budgetfee')->where('BUDGETID=' . $caseone['ID'] . 'AND ISVALID=-1 and FEEID<100')->order("ID ASC")->select();


            foreach ($list as $v) {
                $valueArr[$v['FEEID'] . '_REMARK'] .= $v['REMARK'];
                $valueArr[$v['FEEID'] . '_AMOUNT'] += $v['AMOUNT'];
                //$valueArr[$v['FEEID'].'_RATIO'] = $v['RATIO'];
                $valueArr[$v['FEEID'] . '_ID'] = $v['ID'];
                //$valueArr[$v['FEEID'].'_STATUS'] = $v['STATUS'];
            }


        }


        foreach ($return_tr as $k => $v) {
            $isonline = ($k == 99 || $k == 98) ? "isonline='1'" : "isonline='0'";
            $html = $html . "<tr>";
            if (in_array($k, $noinput_arr)) {
                $html = $html . $v . "<td>  <span id='" . $k . "_AMOUNT'   ></span></td> <td> </td><td> </td> </tr>";
            } else {
                $html = $html . $v . "<td>" . $valueArr[$k . '_AMOUNT'] . " <input name='" . $k . "_AMOUNT' fid='$k' value='" . $valueArr[$k . '_AMOUNT'] . "'  $isonline  type='hidden' class='AMOUNT'/></td> <td><span >" . $valueArr[$k . '_RATIO'] . "</span> % <input fid='$k'  name='" . $k . "_RATIO' $isonline class='RATIO'  value='" . $valueArr[$k . '_RATIO'] . "' type='hidden' /></td><td>" . $valueArr[$k . '_REMARK'] . "</td> </tr>";
            }
            $showbutton = 0;
        }
        $html = $html . "</table>";
        $this->assign('html', $html);
		$this->assign('reflash', $_REQUEST['reflash']);
        $this->assign('estimate_money', $estimate_money);
        $this->assign('showbutton', $showbutton);
        $this->display('budgetfee');

    }

    //目标分解
    function budgetSale() {
        $prjId = $_REQUEST['prjid'];
        //$this->project_auth($prjId,array(1,2),$_REQUEST['flowId']);//项目权限判断
        $this->project_pro_auth($prjId, $_REQUEST['flowId']);
        //$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
        $project = D('Erp_project')->where("ID=$prjId")->find();

        Vendor('Oms.Form');
        $form = new Form();

        $form = $form->initForminfo(120);
        if ($this->_get('CHANGE') == -1) {
            $form->changeRecord = true;
            $form->changeRecordVersionId = $_REQUEST['CID'];
            if ($_REQUEST['active'] == '1' && $this->isedit == false) {
                $form->CZBTN = ' ';
                $form->ADDABLE = 0;
                /*$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">排序</a>'.
                '<a id="j-search" class="j-showalert" href="javascript:;">搜索</a>'.
                '<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">刷新</a>';*/
            }
            $form->where("PROJECTT_ID=$prjId");
        } else {
            if ($project['PSTATUS'] > 2 && $this->isedit == false && $_REQUEST['tabNum']!=202) {
                $form->CZBTN = ' ';
                $form->ADDABLE = 0;
                /*$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">排序</a>'.
                '<a id="j-search" class="j-showalert" href="javascript:;">搜索</a>'.
                '<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">刷新</a>';*/
            }
            $form->setMyFieldVal('ISVALID', '-1', true);
            $form->where("PROJECTT_ID=$prjId AND ISVALID = -1");
        }

        if ($_REQUEST['showForm']) {
            $form->setMyFieldVal('PROJECTT_ID', $prjId, true);
        }

        //如果是编辑和新增
        if($_REQUEST['showForm']==1 || $_REQUEST['showForm']==3){
            $form->setMyField('SALEMETHODID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SALEMETHOD WHERE ISVALID=-1 AND VERSON = 2 ORDER BY ORDERID', false);
        }

        $form = $form->getResult();
        $this->assign('form', $form);
        $this->assign('prjId', $prjId);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->display('budgetSale');
    }

    //收费标准
    function feescale() {
        $case_id = 0;
        $prb_id = intval($this->_get('parentchooseid'));//2093;
        if ($prb_id > 0) {
            $model = new Model();
            $sql = "SELECT PROJECT_ID, A.CASE_ID,B.SCALETYPE FROM ERP_PRJBUDGET A LEFT JOIN "
                . " ERP_CASE B ON A.CASE_ID = B.ID where A.ID = " . $prb_id;
            $casedata = $model->query($sql);

            if ($casedata[0]) {
                $project = D('Erp_project')->where("ID=" . $casedata[0]['PROJECT_ID'])->find();
                $case_id = intval($casedata[0]['CASE_ID']);//4210
            }
        }

        //$paramUrl = '&prjid='.$_REQUEST['prjid'];
        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(130);
		if ($this->_get('CHANGE') == -1) {
		   $form->setMyFieldVal('ISVALID', '0', true);
		   $form->setMyFieldVal('CID', $_REQUEST['CID'], true);
           $form->changeRecord = true;
           $form->changeRecordVersionId = $_REQUEST['CID'];

		 $form->where('SCALETYPE=' . $_REQUEST['SCALETYPE'] . '  and  (CID = '.$_REQUEST['CID'].' or ISVALID = -1 )');
		   if( $this->isedit == false && $_REQUEST['flowId']){

			   $form->CZBTN = ' ';
			   $form->ADDABLE = 0;

		   }else

		   $form->CZBTN = array(
							 '%ISVALID%==0' => '<a class="contrtable-link fedit btn btn-primary btn-xs" onclick="editThis(this);" title="编辑"  href="javascript:void(0);">
			<i class="glyphicon glyphicon-edit"></i>
			</a>
			<a class="contrtable-link fedit btn btn-success btn-xs" onclick="viewThis(this);" title="查看"   href="javascript:void(0);">
			<i class="glyphicon glyphicon-eye-open"></i>
			</a>
			<a class="contrtable-link btn btn-danger btn-xs" onclick="delThis(this);"  title="删除" href="javascript:void(0);">
			<i class="glyphicon glyphicon-trash"></i>
			</a>' 
						 );
		}else{
		   $form->setMyFieldVal('ISVALID', '-1', true);
		   $form->where('SCALETYPE=' . $_REQUEST['SCALETYPE'] . " AND ISVALID = -1");

		}

        //分销且只有 单套收费标准和中介佣金标准才有前佣和后佣的方法
        if ($casedata[0]['SCALETYPE'] == 2 && ($_REQUEST['SCALETYPE'] == 1 || $_REQUEST['SCALETYPE'] == 2)) {
            $form->setMyfield('MTYPE', 'FORMVISIBLE', -1, false);
            $form->setMyfield('MTYPE', 'GRIDVISIBLE', -1, false);
            $form->setMyfield('EXECSTIME', 'FORMVISIBLE', -1, false);
            $form->setMyfield('EXECSTIME', 'GRIDVISIBLE', -1, false);
            $form->setMyfield('EXECETIME', 'FORMVISIBLE', -1, false);
            $form->setMyfield('EXECETIME', 'GRIDVISIBLE', -1, false);
        }
        if (($casedata[0]['SCALETYPE'] == 2 ) && ($_REQUEST['SCALETYPE'] == 1 || $_REQUEST['SCALETYPE'] == 2|| $_REQUEST['SCALETYPE'] == 3|| $_REQUEST['SCALETYPE'] == 4|| $_REQUEST['SCALETYPE'] ==5)) { //分销 且 是单套收费标准
            $form->setMyField('STYPE', 'FORMVISIBLE', -1, false);
            $form->setMyField('STYPE', 'GRIDVISIBLE', -1, false);
            $form->setMyField('AMOUNT', 'FIELDMEANS', '值', false);

            // $form->setMyField('PERCENTAGE','FORMVISIBLE',-1,false);
            // $form->setMyField('PERCENTAGE','GRIDVISIBLE',-1,false);

        }
        if ($project['PSTATUS'] > 2 && $this->isedit == false && $this->_get('CHANGE') != -1) {
            $form->CZBTN = ' ';
            $form->ADDABLE = 0;
            /*$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">排序</a>'.
            '<a id="j-search" class="j-showalert" href="javascript:;">搜索</a>'.
            '<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">刷新</a>';*/
        }

        $form->setMyFieldVal('PRJ_ID', $_REQUEST['parentchooseid'], true)->setMyFieldVal('SCALETYPE', $_REQUEST['SCALETYPE'], true);
        if ($_REQUEST['showForm'] == 3) $form->setMyFieldVal('PAYDATE', date('Y-m-d', time()), true);
        if ($_REQUEST['faction'] == 'saveFormData') {
            $execStime = strtotime($_POST['EXECSTIME']);
            $execEtime = strtotime($_POST['EXECETIME']);
            if ($execStime > $execEtime) {
                $result['status'] = 0;
                $result['msg'] = g2u('执行开始时间应小于执行结束时间');
                echo json_encode($result);
                exit;
            }
            //同一标准值不能相同
            $condition = ' WHERE 1=1 ';
            if($_REQUEST['SCALETYPE']){
                $condition .= " AND SCALETYPE =".$_REQUEST['SCALETYPE'];
            };
            if(isset($_POST['MTYPE']) && ($_POST['MTYPE'] !== "")){
                $condition .= " AND MTYPE =".$_POST['MTYPE'];
            }
            if(isset($_POST['STYPE']) && $_POST['STYPE'] !== ""){
                $condition .= " AND STYPE=".$_POST['STYPE'];
            }

            if($_REQUEST['ID']){
                $condition .= " AND ID !=".$_REQUEST['ID'];
            }
            $where = $condition." AND ISVALID = -1 AND CASE_ID = {$case_id}";
            $result = $this->isRepeatValue($where);
            if($result['status'] == 0){
                echo json_encode($result);
                exit;
            }
            //立项变更判断，先判断有效，在判断变更
             if($_REQUEST['CID']) {
                 $where = $condition." AND CID = " . $_REQUEST['CID'] . " AND  ISVALID = 0 ";
                 $result = $this->isRepeatValue($where);
                 if($result['status'] == 0){
                     echo json_encode($result);
                     exit;
                 }
             }
        } else if ($_REQUEST['faction'] == 'delData') {  // 删除标准操作
            if ($_REQUEST['CHANGE'] == -1) {
                $bid = $_REQUEST['ID'];
                $cid = $_REQUEST['CID'];
                $dbResult = D('erp_changelog')->where("TABLEE = 'ERP_FEESCALE' AND BID = {$bid} AND CID = {$cid}")->delete();
                if ($dbResult === false) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('服务器内部错误');
                    echo json_encode($result);
                    exit;
                }
            }
        }
		$form->colArr = array('ISVALID');
		
        //添加隐藏参数
        $input_arr = array(
            array('name' => 'CASE_ID', 'val' => $case_id, 'class' => 'CASE_ID'),
        );
        $form = $form->addHiddenInput($input_arr);
        $form = $form->getResult();
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('scaleType',$_REQUEST['SCALETYPE']);
        $this->assign('form', $form);
		$this->assign('CID', $_REQUEST['CID']);
		$this->assign('PID', $_REQUEST['parentchooseid']);
        $this->display('feescale');
    }

    function opinionFlow() {
		 
        if ($_REQUEST['flowType'] == 'lixiangbiangeng') { // 立项变更审批
            $prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['CASEID'];
            $flowId = !empty($_REQUEST['flowId']) ? intval($_REQUEST['flowId']) : 0;
            $recordId = !empty($_REQUEST['RECORDID']) ? intval($_REQUEST['RECORDID']) : 0;
            $flowType = $_REQUEST['flowType'] ? $_REQUEST['flowType'] : '';

            if (!$flowId && !$flowType) {
                js_alert();
            }
            Vendor('Oms.workflow');
            $workflow = new workflow();
            Vendor('Oms.Changerecord');
            $changer = new Changerecord();

            if ($flowId > 0) {
                //处理已经存在的工作流
                $click = $workflow->nextstep($flowId);
                $form = $workflow->createHtml($flowId);

                if ($_REQUEST['savedata']) {
                    //下一步
                    if ($_REQUEST['flowNext']) {
                        $str = $workflow->handleworkflow($_REQUEST);
                        if ($str) {
                            js_alert('办理成功', U('Flow/workStep'));
                        } else {
                            js_alert('办理失败');
                        }
                    } //通过按钮
                    else if ($_REQUEST['flowPass']) {
                        $str = $workflow->passWorkflow($_REQUEST);

                        if ($str) {
                            js_alert('同意成功', U('Flow/workStep'));
                        } else {
                            js_alert('同意失败');
                        }
                    } //否决按钮
                    else if ($_REQUEST['flowNot']) {
                        $str = $workflow->notWorkflow($_REQUEST);
                        if ($str) {
                            //$project_model = D('Project');
                            // $project_model->update_finalaccounts_nopass_status($prjId);


                            js_alert('否决成功', U('Flow/workStep'));
                        } else {
                            js_alert('否决失败');
                        }
                    } //终止按钮
                    else if ($_REQUEST['flowStop']) {
                        $auth = $workflow->flowPassRole($flowId);

                        if (!$auth) {
                            js_alert('未经过必经角色');
                            exit;
                        }

                        $str = $workflow->finishworkflow($_REQUEST);
                        if ($str) {
                            $CID = $_REQUEST['RECORDID'];
                            $changer->setRecords($CID);

                            $project_model = D('Project');
                            $project_model->set_project_change($prjId);//变更后的数据统计
                            //$ress =$project_model->update_finalaccounts_status($prjId);

                            //更改project 名称
                            $PRO_NAME = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('PRO_NAME');
							 $DEV_ENT = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('DEV_ENT');
							  $CONTRACT_NUM = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('CONTRACT_NUM');

                            $UPDATE = M("Erp_project")->where("ID = $prjId")->setField("PROJECTNAME", $PRO_NAME);
							 $UPDATE = M("Erp_project")->where("ID = $prjId")->setField("CONTRACT", $CONTRACT_NUM);
							  $UPDATE = M("Erp_project")->where("ID = $prjId")->setField("COMPANY", $DEV_ENT);

                            js_alert('备案成功', U('Flow/workStep'));
                        } else {
                            js_alert('备案失败');
                        }
                    }
                    exit;
                }
            } else {

                //创建工作流
                $auth = $workflow->start_authority($flowType);
                if (!$auth) {
                    js_alert('暂无权限');
                }
                $form = $workflow->createHtml();

                if ($_REQUEST['savedata']) {
                    $form = $workflow->createHtml();

                    if ($_REQUEST['savedata']) {
                        if ($recordId) {
                            $project_model = D('Project');
                            $pstatus = $project_model->get_Change_Flow_Status($recordId);

                            if ($pstatus == '1') {
                                js_alert('请勿重复提交哦', U('House/opinionFlow', $this->_merge_url_param));
                            } else {
                                $_REQUEST['type'] = $_REQUEST['flowType'];
                                $str = $workflow->createworkflow($_REQUEST);
                                if ($str) {
                                    js_alert('提交成功', U('House/opinionFlow', $this->_merge_url_param));
                                } else {
                                    js_alert('提交失败', U('House/opinionFlow', $this->_merge_url_param));
                                }
                            }

                        }
                    }
                }
            }

            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);

        } else {//立项审批-===============
            $prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['RECORDID'];
            $type = $_REQUEST['type'] ? $_REQUEST['type'] : 'lixiangshenqing';
            if ($_REQUEST['CHANGE'] == '-1') {
                js_alert();
            }

            if (!$type) {
                $this->error('工作流类型不存在');
            }

            //工作流ID
            $flowId = !empty($_REQUEST['flowId']) ?
                intval($_REQUEST['flowId']) : 0;

            //工作流关联业务ID
            $recordId = !empty($_REQUEST['RECORDID']) ?
                intval($_REQUEST['RECORDID']) : 0;

            Vendor('Oms.workflow');
            $workflow = new workflow();

            if ($flowId > 0) {
				 
                //处理已经存在的工作流
                $click = $workflow->nextstep($flowId);
                $form = $workflow->createHtml($flowId);

                if ($_REQUEST['savedata']) {
                    //下一步
                    if ($_REQUEST['flowNext']) {
                        $str = $workflow->handleworkflow($_REQUEST);
                        if ($str) {
                            js_alert('办理成功', U('Flow/workStep'));
                        } else {
                            js_alert('办理失败');
                        }
                    } //通过按钮
                    else if ($_REQUEST['flowPass']) {
                        $str = $workflow->passWorkflow($_REQUEST);

                        if ($str) {
                            /**
                             *  如果该项目存在非我方收筹
                             */
                            $fwfscCase = D('ProjectCase')->where('PROJECT_ID = ' . $recordId . ' AND SCALETYPE = ' . self::FWFSC)->find();
                            if (is_array($fwfscCase) && count($fwfscCase)) {
                                $this->addFwfscIncomeContract($recordId, $fwfscCase['ID']);
                            }

                            $project_model = D('Project');
                            $ress = $project_model->update_pass_status($_REQUEST['RECORDID']);;//审核通过

                            /*如果该项目存在分销业务，则通过合同编号获取合同系统中合同信息，
                            并存储在经管系统合同表中*/
                            $project_case_model = D('ProjectCase');
                            $case_type = 'fx';
                            $isexists = $project_case_model->is_exists_case_type($recordId, $case_type);
                            $contractInfo = D('Contract')->where('CASE_ID = ' . $recordId)->find();
                            if (is_array($contractInfo) && count($contractInfo)) {
                                $hadContract = true;
                            } else {
                                $hadContract = false;
                            }

                            if ($isexists && !$hadContract) {
                                //查询项目合同信息
                                $cond_where = "PROJECT_ID = '" . $recordId . "'";
                                $house_info = M('erp_house')->field('CONTRACT_NUM')->where($cond_where)->find();
                                $contract_no = !empty($house_info['CONTRACT_NUM']) ?
                                    $house_info['CONTRACT_NUM'] : '';
								$contract_no  = trim($contract_no );
                                $city_model = D('City');
                                $city_info = $city_model->get_city_info_by_id($this->channelid, array('PY'));
                                $city_py = !empty($city_info['PY']) ? strtolower(strip_tags($city_info['PY'])) : '';
                                load("@.contract_common");
                                $contract_info = getContractData($city_py, $contract_no);

                                if (is_array($contract_info) && !empty($contract_info)) {
                                    $info = array();
                                    $case_info = $project_case_model->get_info_by_pid($recordId, $case_type, array('ID'));
                                    $info['CASE_ID'] = $case_info[0]['ID'];
                                    $info['CONTRACT_NO'] = $contract_info['fullcode'];
                                    $info['COMPANY'] = $contract_info['contunit'];
                                    $info['START_TIME'] = date('Y-m-d H:i:s', $contract_info['contbegintime']);
                                    $info['END_TIME'] = date('Y-m-d H:i:s', $contract_info['contendtime']);
                                    $info['PUB_TIME'] = !empty($contract_info['pubdate']) ?
                                        date('Y-m-d H:i:s', strtotime($contract_info['pubdate'])) : '';
                                    $info['CONF_TIME'] = !empty($contract_info['confirmtime']) ?
                                        date('Y-m-d H:i:s', $contract_info['confirmtime']) : '';
                                    $info['STATUS'] = $contract_info['step'];
                                    $info['MONEY'] = $contract_info['contmoney'];
                                    $info['ADD_TIME'] = date('Y-m-d H:i:s', time());
                                    $info['CONTRACT_TYPE'] = $contract_info['type'];
                                    $info['IS_NEED_INVOICE'] = 0;
                                    $info['SIGN_USER'] = $contract_info['addman'];
                                    $info['CITY_PY'] = $city_py;
                                    //取工作流发起人所在城市
                                    $creator_info = $workflow->get_Flow_Creator_Info($flowId);
                                    $info['CITY_ID'] = $creator_info['CITY'];

                                    $contract_model = D('Contract');
                                    $contract_id = $contract_model->add_contract_info($info);

                                    /***同步合同开票和回款记录到经管系统***/
                                    if ($contract_id > 0) {
                                        //根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
                                        $refundRecords = get_backmoney_data_by_no($city_py, $contract_no);

                                        $payment_model = D("PaymentRecord");
                                        if (!empty($refundRecords)) {
                                            foreach ($refundRecords as $key => $val) {
                                                $refund_data["MONEY"] = $val["money"];
                                                $refund_data["CREATETIME"] = $val["date"];
                                                $refund_data["REMARK"] = $val["note"];
                                                $refund_data["CASE_ID"] = $case_info[0]['ID'];
                                                $refund_data["CONTRACT_ID"] = $contract_id;
                                                $insert_reund_id = $payment_model->add_refund_records($refund_data);

                                                if ($insert_reund_id) {
                                                    //新增收益明细记录  
                                                    $taxrate = get_taxrate_by_citypy($city_py);
                                                    $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);

                                                    $income_info['INCOME_FROM'] = 7;
                                                    $income_info['CASE_ID'] = $case_info[0]['ID'];
                                                    $income_info['ENTITY_ID'] = $contract_id;
                                                    $income_info['INCOME'] = $val["money"];
                                                    $income_info['OUTPUT_TAX'] = $tax;
                                                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                                                    $income_info['OCCUR_TIME'] = $val["date"];
                                                    $income_info['PAY_ID'] = $insert_reund_id;
                                                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                                                    $income_info['ORG_ENTITY_ID'] = $contract_id;
                                                    $income_info['ORG_PAY_ID'] = $insert_reund_id;

                                                    $ProjectIncome_model = D("ProjectIncome");
                                                    $res = $ProjectIncome_model->add_income_info($income_info);
                                                }
                                            }
                                        }

                                        //根据合同号和城市拼音，获取合同的开票记录,并将数据同步到经管系统
                                        $invoiceRecords = get_invoice_data_by_no($city_py, $contract_no);

                                        if (!empty($invoiceRecords)) {
                                            $billing_model = D("BillingRecord");
                                            $billing_status = $billing_model->get_invoice_status();

                                            foreach ($invoiceRecords as $key => $val) {
                                                $taxrate = get_taxrate_by_citypy($city_py);
                                                $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);

                                                $invoice_data["INVOICE_MONEY"] = $val["money"];
                                                $invoice_data["TAX"] = $tax;
                                                $invoice_data["INVOICE_NO"] = $val["invono"];
                                                $invoice_data["REMARK"] = $val["note"];
                                                $invoice_data["INVOICE_TIME"] = $val["date"];
                                                $invoice_data["STATUS"] = $billing_status["have_invoiced"];
                                                $invoice_data["CONTRACT_ID"] = $contract_id;
                                                $invoice_data["CASE_ID"] = $case_info[0]['ID'];
                                                $invoice_data["CREATETIME"] = $val["date"];
                                                $invoice_data["INVOICE_TYPE"] = 1;
                                                if ($val['type']) {
                                                    // 发票类型，如果发票类型不为1或2，则将发票类型设置为2(服务费)
                                                    // 否则设置为1（广告费）或2（服务费）
                                                    if (!in_array($val['type'], array(1, 2))) {
                                                        $val['type'] = 2;
                                                    }
                                                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                                                }

                                                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);

                                                if ($insert_invoice_id) {
                                                    //新增收益明细记录           
                                                    $income_info['INCOME_FROM'] = 8;
                                                    $income_info['CASE_ID'] = $case_info[0]['ID'];
                                                    $income_info['ENTITY_ID'] = $contract_id;
                                                    $income_info['INCOME'] = $val["money"];
                                                    $income_info['OUTPUT_TAX'] = $tax;
                                                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                                                    $income_info['OCCUR_TIME'] = $val["date"];
                                                    $income_info['PAY_ID'] = $insert_invoice_id;
                                                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                                                    $income_info['ORG_ENTITY_ID'] = $contract_id;
                                                    $income_info['ORG_PAY_ID'] = $insert_invoice_id;

                                                    $ProjectIncome_model = D("ProjectIncome");
                                                    $res = $ProjectIncome_model->add_income_info($income_info);
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            js_alert('同意成功', U('Flow/workStep'));
                        } else {
                            js_alert('同意失败');
                        }
                    } //否决按钮
                    else if ($_REQUEST['flowNot']) {
                        $str = $workflow->notWorkflow($_REQUEST);
                        if ($str) {
                            $project_model = D('Project');
                            $project_model->update_nopass_status($prjId);;//审核 不通过
                            js_alert('否决成功', U('Flow/workStep'));
                        } else {
                            js_alert('否决失败');
                        }
                    } //终止按钮
                    else if ($_REQUEST['flowStop']) {
                        $auth = $workflow->flowPassRole($flowId);

                        if (!$auth) {
                            js_alert('未经过必经角色');
                            exit;
                        }

                        $str = $workflow->finishworkflow($_REQUEST);

                        if ($str) {
                            $project_model = D('Project');
                            $ress = $project_model->update_pass_status($_REQUEST['RECORDID']);//审核通过

                            /**
                             *  如果该项目存在非我方收筹
                             */
                            $fwfscCase = D('ProjectCase')->where('PROJECT_ID = ' . $recordId . ' AND SCALETYPE = ' . self::FWFSC)->find();
                            if (is_array($fwfscCase) && count($fwfscCase)) {
                                $this->addFwfscIncomeContract($recordId, $fwfscCase['ID']);
                            }

                            /*如果该项目存在分销业务，则通过合同编号获取合同系统中合同信息，
                            并存储在经管系统合同表中*/
                            $project_case_model = D('ProjectCase');
                            $case_type = 'fx';
                            $isexists = $project_case_model->is_exists_case_type($recordId, $case_type);
                            $contractInfo = D('Contract')->where('CASE_ID = ' . $recordId)->find();
                            if (is_array($contractInfo) && count($contractInfo)) {
                                $hadContract = true;
                            } else {
                                $hadContract = false;
                            }

                            if ($isexists && !$hadContract) {
                                //查询项目合同信息
                                $cond_where = "PROJECT_ID = '" . $recordId . "'";
                                $house_info = M('erp_house')->field('CONTRACT_NUM')->where($cond_where)->find();
                                $contract_no = !empty($house_info['CONTRACT_NUM']) ?
                                    $house_info['CONTRACT_NUM'] : '';
								$contract_no  = trim($contract_no );
                                $city_model = D('City');
                                $city_info = $city_model->get_city_info_by_id($this->channelid, array('PY'));
                                $city_py = !empty($city_info['PY']) ? strtolower(strip_tags($city_info['PY'])) : '';
                                load("@.contract_common");
                                $contract_info = getContractData($city_py, $contract_no);

                                if (is_array($contract_info) && !empty($contract_info)) {
                                    $info = array();
                                    $case_info = $project_case_model->get_info_by_pid($recordId, $case_type, array('ID'));
                                    $info['CASE_ID'] = $case_info[0]['ID'];
                                    $info['CONTRACT_NO'] = $contract_info['fullcode'];
                                    $info['COMPANY'] = $contract_info['contunit'];
                                    $info['START_TIME'] = date('Y-m-d H:i:s', $contract_info['contbegintime']);
                                    $info['END_TIME'] = date('Y-m-d H:i:s', $contract_info['contendtime']);
                                    $info['PUB_TIME'] = !empty($contract_info['pubdate']) ?
                                        date('Y-m-d H:i:s', strtotime($contract_info['pubdate'])) : '';
                                    $info['CONF_TIME'] = !empty($contract_info['confirmtime']) ?
                                        date('Y-m-d H:i:s', $contract_info['confirmtime']) : '';
                                    $info['STATUS'] = $contract_info['step'];
                                    $info['MONEY'] = $contract_info['contmoney'];
                                    $info['ADD_TIME'] = date('Y-m-d H:i:s', time());
                                    $info['CONTRACT_TYPE'] = $contract_info['type'];
                                    $info['IS_NEED_INVOICE'] = 0;
                                    $info['SIGN_USER'] = $contract_info['addman'];
                                    $info['CITY_PY'] = $city_py;

                                    //取工作流发起人所在城市
                                    $creator_info = $workflow->get_Flow_Creator_Info($flowId);
                                    $info['CITY_ID'] = $creator_info['CITY'];

                                    $contract_model = D('Contract');
                                    $contract_id = $contract_model->add_contract_info($info);

                                    /***同步合同开票和回款记录到经管系统***/
                                    if ($contract_id > 0) {
                                        //根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
                                        $refundRecords = get_backmoney_data_by_no($city_py, $contract_no);

                                        $payment_model = D("PaymentRecord");
                                        if (!empty($refundRecords)) {
                                            foreach ($refundRecords as $key => $val) {
                                                $refund_data["MONEY"] = $val["money"];
                                                $refund_data["CREATETIME"] = $val["date"];
                                                $refund_data["REMARK"] = $val["note"];
                                                $refund_data["CASE_ID"] = $case_info[0]['ID'];
                                                $refund_data["CONTRACT_ID"] = $contract_id;
                                                $insert_reund_id = $payment_model->add_refund_records($refund_data);

                                                if ($insert_reund_id) {
                                                    $taxrate = get_taxrate_by_citypy($city_py);
                                                    $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);

                                                    //新增收益明细记录           
                                                    $income_info['INCOME_FROM'] = 7;
                                                    $income_info['CASE_ID'] = $case_info[0]['ID'];
                                                    $income_info['ENTITY_ID'] = $contract_id;
                                                    $income_info['INCOME'] = $val["money"];
                                                    $income_info['OUTPUT_TAX'] = $tax;
                                                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                                                    $income_info['OCCUR_TIME'] = $val["date"];
                                                    $income_info['PAY_ID'] = $insert_reund_id;
                                                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                                                    $income_info['ORG_ENTITY_ID'] = $contract_id;
                                                    $income_info['ORG_PAY_ID'] = $insert_reund_id;

                                                    $ProjectIncome_model = D("ProjectIncome");
                                                    $res = $ProjectIncome_model->add_income_info($income_info);
                                                }
                                            }
                                        }

                                        //根据合同号和城市拼音，获取合同的开票记录,并将数据同步到经管系统
                                        $invoiceRecords = get_invoice_data_by_no($city_py, $contract_no);

                                        if (!empty($invoiceRecords)) {
                                            $billing_model = D("BillingRecord");
                                            $billing_status = $billing_model->get_invoice_status();

                                            foreach ($invoiceRecords as $key => $val) {
                                                $invoice_data["INVOICE_MONEY"] = $val["money"];
                                                $invoice_data["TAX"] = $val["tax"];
                                                $invoice_data["INVOICE_NO"] = $val["invono"];
                                                $invoice_data["REMARK"] = $val["note"];
                                                $invoice_data["INVOICE_TIME"] = $val["date"];
                                                $invoice_data["STATUS"] = $billing_status["have_invoiced"];
                                                $invoice_data["CONTRACT_ID"] = $contract_id;
                                                $invoice_data["CASE_ID"] = $case_info[0]['ID'];
                                                $invoice_data["CREATETIME"] = $val["date"];
                                                $invoice_data["INVOICE_TYPE"] = 1;
                                                if ($val['type']) {
                                                    // 发票类型，如果发票类型不为1或2，则将发票类型设置为2(服务费)
                                                    // 否则设置为1（广告费）或2（服务费）
                                                    if (!in_array($val['type'], array(1, 2))) {
                                                        $val['type'] = 2;
                                                    }
                                                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                                                }
                                                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);

                                                if ($insert_invoice_id) {
                                                    //新增收益明细记录 
                                                    $taxrate = get_taxrate_by_citypy($city_py);
                                                    $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);
                                                    $income_info['OUTPUT_TAX'] = $tax;
                                                    $income_info['INCOME_FROM'] = 8;
                                                    $income_info['CASE_ID'] = $case_info[0]['ID'];
                                                    $income_info['ENTITY_ID'] = $contract_id;
                                                    $income_info['INCOME'] = $val["money"];
                                                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                                                    $income_info['OCCUR_TIME'] = $val["date"];
                                                    $income_info['PAY_ID'] = $insert_invoice_id;
                                                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                                                    $income_info['ORG_ENTITY_ID'] = $contract_id;
                                                    $income_info['ORG_PAY_ID'] = $insert_invoice_id;

                                                    $ProjectIncome_model = D("ProjectIncome");
                                                    $res = $ProjectIncome_model->add_income_info($income_info);
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            js_alert('备案成功', U('Flow/workStep'));
                        } else {
                            js_alert('备案失败');
                        }
                    }
                    exit;
                }
            } else {
                //创建工作流
                $auth = $workflow->start_authority($type);
                if (!$auth) {
                    js_alert("暂无权限");
                }
                $model = M();
                // $fees = $model->query("select * from ERP_BUDGETFEE a left join ERP_PRJBUDGET b on a.budgetid=b.id left join Erp_CASE c on b.case_id = c.id where c.project_id=$prjId");
                $passFees = $this->checkFeesPassed($prjId);
                if (!$passFees) {
                    js_alert('请先填写预算费用', U('House/projectBudget', $this->_merge_url_param));
                    exit;
                }
                if ($this->needBudgetSale($prjId)) {  // 是否需要目标分解
                    $budgetsale = M('Erp_budgetsale')->where("PROJECTT_ID=$prjId")->select();
                    if (!$budgetsale) {

                        js_alert('请先填写目标分解', U('House/budgetSale', $this->_merge_url_param));
                        exit;
                    }
                }
				$url = __APP__ . '/Touch/ProjectSet/show/prjid/' . $prjId;
				header("Location:$url");exit();
                $form = $workflow->createHtml();

                if ($_REQUEST['savedata']) {


                    if ($prjId) {
                        $project_model = D('Project');
                        $pstatus = $project_model->get_project_status($prjId);
                        if ($pstatus == 2) {

                            $flow_data['type'] = 'lixiangshenqing';//$type;
                            //$flow_data['CASEID'] = '';
                            $flow_data['RECORDID'] = $prjId;
                            $flow_data['INFO'] = strip_tags($_POST['INFO']);
                            $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                            $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                            $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                            $flow_data['FILES'] = $_POST['FILES'];
                            $flow_data['ISMALL'] = intval($_POST['ISMALL']);
                            $flow_data['ISPHONE'] = intval($_POST['ISPHONE']);
                            $flow_data['COPY_USERID'] = intval($_POST['COPY_USERID']);
                            $str = $workflow->createworkflow($flow_data);

                            if ($str) {
                                //提交..申请
                                $project_model = D('Project');
                                $project_model->update_check_status($prjId);//审核中
                                js_alert('提交成功', U('House/opinionFlow', $this->_merge_url_param));
                                exit;
                            } else {
                                js_alert('提交失败', U('House/opinionFlow', $this->_merge_url_param));
                                exit;
                            }
                        } else {
                            js_alert('请不要重复提交', U('House/opinionFlow', $this->_merge_url_param));
                            exit;
                        }
                    }
                }
            } 

            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('current_url', U('Business/opinionFlow', $this->_merge_url_param));
        }
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->display('opinionFlow');
    }

    function projectAuth() {

        $projId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['projId'];
        $this->project_case_auth($projId);//项目业务权限判断
        $proj = M('Erp_project')->where("ID = {$projId}")->find();
        $case = M('Erp_case')->field("id,scaletype")->where("PROJECT_ID = {$projId}")->select();

        $projectCase = D("ProjectCase");
        $caseLabel = $projectCase->get_conf_case_type_remark();

        $caseArr = array();
        $caseList = array();
        if ($case) {
            foreach ($case as $key => $value) {
                $caseArr[$key] = $value['SCALETYPE'];
                $caseList[$value['SCALETYPE']] = $caseLabel[$value['SCALETYPE']];
            }
        }

        /*//有电商类型的时候放开驻场
        $business_type = 1;//电商类型
        $field_type = 6;// 驻场类型
        if($caseArr && in_array($business_type,$caseArr)){
            array_push($caseArr,$field_type);
        }*/
        //print_r($caseArr);exit;
        if ($_REQUEST['add']) {

            $uId = $_REQUEST['uId'];
            $prjId = $_REQUEST['projId'];
            $erpId = $_REQUEST['erpId'];

            $sign = true;
            $project = M('Erp_prorole');

            if ($erpId) {
                /*电商默认新增驻场权限 --start--*/
                /*if($erpId == 1){
                    foreach(explode(',',$uId) as $key=>$v){
                        $auth = $project->where("erp_id = 6 and pro_id = {$prjId} and use_id={$v}")->find();//检查是否有驻场权限

                        if($auth){//有
                            if(!$auth['ISVALID']){
                                $update = $project->where("ID = {$auth['ID']}")->setField("ISVALID",-1);
                            }
                        }else{
                            $data = array();
                            $data['USE_ID']=$v;
                            $data['PRO_ID']=$prjId;
                            $data['ERP_ID']=6;
                            $data['ISVALID']=-1;
                            $add = $project->add($data);
                        }
                    }
                }*/
                /*--end--*/

                $update = $project->where("erp_id = {$erpId} and pro_id = {$prjId}")->setField('ISVALID', 0);

                foreach (explode(',', $uId) as $k => $val) {
                    $affect = $project->where("use_id = {$val} and pro_id = {$prjId} and erp_id = {$erpId}")->find();

                    if (!$affect) {
                        $data['USE_ID'] = $val;
                        $data['PRO_ID'] = $prjId;
                        $data['ERP_ID'] = $erpId;
                        $data['ISVALID'] = -1;
                        $add = M('Erp_prorole')->add($data);

                        if (!$add) {
                            $sign = false;
                        }

                    } else {

                        $up = M('Erp_prorole')->where("id = {$affect['ID']}")->setField('ISVALID', -1);
                        if (!$up) {
                            $sign = false;
                        }

                    }
                }
            }

            if ($sign) {
                js_alert('提交成功', U('House/projectAuth', array('prjid' => $prjId)));

            } else {
                js_alert('提交失败', U('House/projectAuth', array('prjid' => $prjId)));

            }
            exit;
        }

        $this->assign('projId', $projId);
        $this->assign('project', $proj);
        $this->assign('caseStr', implode(',', $caseArr));
        $this->assign('caseList', $caseList);
        $this->display('authority');
    }

    /*
    **驻场权限
    */
    function fieldAuth() {
        if ($_POST['submit']) {
            $USE_ID = $_POST['uId'];
            $PRO_ID = intval($_POST['projId']);
            $ERP_ID = intval($_POST['erpId']);
            $SIGN = TRUE;

            if ($USE_ID && $PRO_ID && $ERP_ID) {
                $update = M("Erp_prorole")->where("PRO_ID = {$PRO_ID} and ERP_ID= {$ERP_ID}")->setField("ISVALID", 0);

                foreach (explode(',', $USE_ID) as $v) {
                    $AUTH = M("Erp_prorole")->where("PRO_ID = {$PRO_ID} and ERP_ID= {$ERP_ID} and USE_ID = {$v}")->find();

                    if ($AUTH) {
                        $EDIT = M("Erp_prorole")->where("ID = {$AUTH['ID']}")->setField("ISVALID", -1);

                        if (!$EDIT) $SIGN = FALSE;
                    } else {
                        $DATA = array();
                        $DATA['USE_ID'] = intval($v);
                        $DATA['ERP_ID'] = $ERP_ID;
                        $DATA['PRO_ID'] = $PRO_ID;
                        $DATA['ISVALID'] = -1;

                        $ADD = M("Erp_prorole")->add($DATA);

                        if (!$ADD) $SIGN = FALSE;
                    }
                }
            }

            if ($SIGN) {
                js_alert("成功", U("House/fieldAuth"));
            } else {
                js_alert("失败", U("House/fieldAuth"));
            }
            exit;
        }

        $this->display('fieldAuth');
    }

    //关联产品
    function relateProduct() {
        $model = new Model();
        Vendor('Oms.Changerecord');
        $changer = new Changerecord();
        $changer->fields = array('ISVAILD');

        $projectId = $_REQUEST['prjid'];

        $houseId = $this->getHouseIdByPid($projectId);
        if (!$houseId) {
            js_alert("请先保存项目信息");
            exit;
        }
        $cid = $_REQUEST['CID'];
        $change = $_REQUEST['CHANGE'];

        if ($_REQUEST['status']) {

            $types = M('Erp_products_type')->select();

            foreach ($types as $key => $val) {
                $product = M('Erp_relatedproducts')->where("CHANGPINID = {$val['ID']} and house_id = {$houseId}")->find();


                if ($_REQUEST['pid']) {//有数据的情况
                    if ($change == -1) {//变更

                        if ($product) {//原先表中存在记录

                            $change_log = M("Erp_changelog")->where("BID = {$product['ID']}")->find();//查看变更

                            if ($change_log) {
                                if (!in_array($val['ID'], $_REQUEST['pid'])) {
                                    $del = M("Erp_changelog")->where("BID = {$product['ID']}")->delete();
                                }
                            } else {

                                if (!$product['ISVAILD']) {
                                    if (in_array($val['ID'], $_REQUEST['pid'])) {
                                        $temp['ISVAILD'] = -1;
                                        $temp['ISVAILD_OLD'] = 0;

                                        $device['TABLE'] = 'ERP_RELATEDPRODUCTS';
                                        $device['BID'] = $product['ID'];
                                        $device['CID'] = $cid;
                                        $device['CDATE'] = date('Y-m-d h:m:s');
                                        $device['APPLICANT'] = $_SESSION['uinfo']['uid'];
                                        $device['ISNEW'] = -1;

                                        $changer->saveRecords($device, $temp);
                                    }
                                } else {
                                    if (!in_array($val['ID'], $_REQUEST['pid'])) {
                                        $temp['ISVAILD'] = 0;
                                        $temp['ISVAILD_OLD'] = 1;

                                        $device['TABLE'] = 'ERP_RELATEDPRODUCTS';
                                        $device['BID'] = $product['ID'];
                                        $device['CID'] = $cid;
                                        $device['CDATE'] = date('Y-m-d h:m:s');
                                        $device['APPLICANT'] = $_SESSION['uinfo']['uid'];
                                        $device['ISNEW'] = 0;

                                        $changer->saveRecords($device, $temp);

                                        $update = M('Erp_relatedproducts')->where("id={$product['ID']}")->setField("ISVAILD", 0);
                                    }
                                }
                            }
                        } else {//未存在记录
                            if (in_array($val['ID'], $_REQUEST['pid'])) {
                                $insert = array(
                                    'HOUSE_ID' => $houseId,
                                    'ISVAILD' => 0,
                                    'CHANGPINID' => $val['ID']
                                );

                                $BID = M('Erp_relatedproducts')->add($insert);

                                $temp['ISVAILD'] = -1;
                                $temp['ISVAILD_OLD'] = '';

                                $device['TABLE'] = 'ERP_RELATEDPRODUCTS';
                                $device['BID'] = $BID;
                                $device['CID'] = $cid;
                                $device['CDATE'] = date('Y-m-d h:m:s');
                                $device['APPLICANT'] = $_SESSION['uinfo']['uid'];
                                $device['ISNEW'] = -1;

                                $changer->saveRecords($device, $temp);
                            }
                        }

                    } else {//未变更情况
                        if ($product) {//原表中存在数据
                            if ($product['ISVAILD']) {
                                if (!in_array($val['ID'], $_REQUEST['pid'])) {
                                    $update = M('Erp_relatedproducts')->where("id={$product['ID']}")->setField("ISVAILD", 0);
                                }
                            } else {
                                if (in_array($val['ID'], $_REQUEST['pid'])) {
                                    $update = M('Erp_relatedproducts')->where("id = {$product['ID']}")->setField('ISVAILD', '-1');
                                }
                            }
                        } else {//原表中未存在数据
                            if (in_array($val['ID'], $_REQUEST['pid'])) {
                                $insert = array(
                                    'HOUSE_ID' => $houseId,
                                    'ISVAILD' => -1,
                                    'CHANGPINID' => $val['ID']
                                );

                                $add = M('Erp_relatedproducts')->add($insert);
                            }
                        }
                    }
                } else {//数据为空的情况
                    if ($product) {
                        if ($_REQUEST['CHANGE'] == -1) {

                            $change_log = M("Erp_changelog")->where("BID = {$product['ID']}")->find();//查看变更

                            if ($change_log) {

                                $del = M("Erp_changelog")->where("BID = {$product['ID']}")->delete();
                            } else {
                                if ($product['ISVAILD']) {
                                    $temp['ISVAILD'] = 0;
                                    $temp['ISVAILD_OLD'] = -1;

                                    $device['TABLE'] = 'ERP_RELATEDPRODUCTS';
                                    $device['BID'] = $product['ID'];
                                    $device['CID'] = $cid;
                                    $device['CDATE'] = date('Y-m-d h:m:s');
                                    $device['APPLICANT'] = $_SESSION['uinfo']['uid'];
                                    $device['ISNEW'] = 0;

                                    $changer->saveRecords($device, $temp);
                                }
                            }

                        } else {
                            if ($product['ISVAILD']) {
                                $update = M('Erp_relatedproducts')->where("id={$product['ID']}")->setField("ISVAILD", 0);
                            }
                        }
                    }

                }
            }
        }


        $project = D('Erp_project')->where("ID=$projectId")->find();

        if ($change) {
            $sql = "select a.*,c.isvaild,c.id as BID from  erp_products_type a left join (select * from erp_relatedproducts b where b.house_id = {$houseId}) c on a.id = c.changpinid ";
        } else {
            $sql = "select a.*,c.isvaild,c.id as BID from  erp_products_type a left join (select * from erp_relatedproducts b where b.house_id = {$houseId} ) c on a.id = c.changpinid ";
        }
        $record = $model->query($sql);
        if ($_REQUEST['CHANGE'] == '-1') {
            $button = 1;
            if ($_REQUEST['active'] == '1' && $this->isedit == false) $button = 0;
        } else {
            if ($project['PSTATUS'] > 2 && $this->isedit == false) $button = 0;
            else $button = 1;
        }
        $disable = $button ? '' : 'disabled="true" ';
        $html = "<form class='registerform' method='post' action=" . U('House/relateProduct', array('status' => '1', 'prjid' => $projectId, 'CHANGE' => $change, 'CID' => $cid, 'flowId' => $_REQUEST['flowId'], 'tabNum' => $_REQUEST['tabNum'])) . "><div class='contractinfo-table'><table><thead><tr><td>产品类型</td><td>是否有效</td></tr></thead><tbody>";

        foreach ($record as $key => $val) {

            if ($_REQUEST['CHANGE'] == '-1') {

                $optt['TABLE'] = 'ERP_RELATEDPRODUCTS';
                $optt['BID'] = $val['BID'];
                $optt['CID'] = $this->_get('CID');//变更版本id
                $changarr = $changer->getRecords($optt);

                $current = $changarr['ISVAILD'] ? $changarr['ISVAILD']['VALUEE'] : $val['ISVAILD'];

                $info = $changarr['ISVAILD'] ? ($changarr['ISVAILD']['ISNEW'] ? "<span class='fred'>[新增]</span>" : "<span class='fred'>[原]已设置</span>") : '';

                $html .= "<tr><td>{$val['CHANPINLEIXING']}</td><td>" . ($current ? "<input type='checkbox' value={$val['ID']} name='pid[]' checked />" : "<input type='checkbox' value={$val['ID']} name='pid[]'  />") . " $info</td></tr>";
            } else {

                $html .= "<tr><td>{$val['CHANPINLEIXING']}</td><td>" . ($val['ISVAILD'] ? "<input $disable type='checkbox' value={$val['ID']} name='pid[]' checked />" : "<input $disable type='checkbox' value={$val['ID']} name='pid[]'  />") . "</td></tr>";
            }
        }

        $html .= "</tbody></table></div>" . ($button ? "<div class='handle-btn'><input type='submit' value='保&nbsp;存' class='btn-blue' /></div></form>" : '</form>');

        $this->assign('form', $html);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->display('relateProduct');
    }

    //根据项目id获取houseid
    public function getHouseIdByPid($pid) {
        $id = M('Erp_house')->where("project_id = {$pid}")->getField('id');

        return $id;
    }

    //新增业务类型
    public function add_benefits($data) {
        $benefits = M("Erp_benefits");
        $res = $benefits->add($data);
        //echo $this->model->getLastSql();
        return $res;
    }

    //根据项目Id获取项目信息
    public function get_prj_info_by_prjid($id, $array) {
        $project = M("Erp_project");
        $info = $project->where("ID=$id")->field($array)->find();
        return $info;
    }

    /**
     * 是否需要目标分解
     * @param $prjId
     * @return bool
     */
    private function needBudgetSale($prjId) {
        $project = D('Erp_project')->where("ID=$prjId")->find();
        if (empty($project)) {
            return false;
        }

        if ($project['SCSTATUS'] !== null && $project['SCSTATUS'] >= 1 && $project['MSTATUS'] === null) {
            return false;
        }

        return true;
    }

    /**
     * 增加非我非收筹合同
     * @param $projId 项目id
     * @param $caseId
     * @return bool 数据是否添加成功
     */
    private function addFwfscIncomeContract($projId, $caseId) {
        $contractInfo = D('Contract')->where('CASE_ID = ' . $caseId)->find();
        if (is_array($contractInfo) && count($contractInfo)) {
            return;
        }

        $project = D('erp_project')->where('ID=' . $projId)->find();
        if (empty($project)) {
            return array(
                'result' => false,
                'msg' => '项目不能为空'
            );
        }

        $contractNo = $project['CONTRACT'];
        $cityid = $project['CITY_ID'];  // 从项目列表中获取城市编号
        $sql = "select PY from ERP_CITY where ID=" . $cityid;
        $citypy = $this->model->query($sql);
        $citypy = strtolower($citypy[0]["PY"]);//用户城市拼音
        //获取合同基本信息
        load("@.contract_common");
        $fetchedData = getContractData($citypy, $contractNo);
        if ($fetchedData === false) {
            return array(
                'result' => false,
                'msg' => '获取合同数据出错'
            );
        }

        $toInsertData['CONTRACT_NO'] = $contractNo;
        $toInsertData['COMPANY'] = $fetchedData['contunit'];
        $toInsertData['START_TIME'] = date("Y-m-d", $fetchedData['contbegintime']);
        $toInsertData['END_TIME'] = date("Y-m-d", $fetchedData['contendtime']);
        $toInsertData['PUB_TIME'] = $fetchedData['pubdate'];
        $toInsertData['CONF_TIME'] = empty($fetchedData['confirmtime']) ?
            '' : date("Y-m-d", $fetchedData['confirmtime']);
        $toInsertData['MONEY'] = $fetchedData['contmoney'];
        $toInsertData['STATUS'] = $fetchedData['step'];  // todo
        $toInsertData['SIGN_USER'] = $fetchedData['addman'];
        $toInsertData['CONTRACT_TYPE'] = $fetchedData['type'];
        $toInsertData['ADD_TIME'] = date("Y-m-d H:i:s");  // 添加时间
        $toInsertData['CASE_ID'] = $caseId;  // 添加时间
        $toInsertData['CITY_PY'] = $citypy;
        $toInsertData['CITY_ID'] = $cityid;
        unset($fetchedData);

        // 执行事务
        $this->model->startTrans();
        $insertedId = D("Contract")->add_contract_info($toInsertData);
        if ($insertedId !== false) {
            //根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
            $insert_refund_id = $this->save_refund_data($contractNo, $insertedId, $citypy);
            //根据合同号和城市拼音，获取合同开票记录，并将数据同步到经管系统
            $insert_invoice_id = $this->save_invoice_data($contractNo, $insertedId, $citypy);
            if ($insert_invoice_id !== false && $insert_refund_id !== false) {
                $this->model->commit();
                return array(
                    'result' => true,
                    'msg' => '合同添加成功'
                );
            } else {
                $this->model->rollback();
                $error = '';
                if ($insert_refund_id == false) {
                    $error .= '获取合同的回款记录错误';
                }

                if ($insert_invoice_id == false) {
                    $error = empty($error) ? '获取合同的开票记录错误' :
                        $error . '， 获取合同的开票记录错误';
                }

                // 返回结果
                return array(
                    'result' => false,
                    'msg' => $error
                );
            }
        } else {
            return array(
                'result' => false,
                'msg' => '添加合同出错'
            );
        }
    }

    /**
     * +----------------------------------------------------------
     *根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
     * +----------------------------------------------------------
     * @param  none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function save_refund_data($contractnum, $contract_id, $citypy = "nj") {
        load("@.contract_common");
        $refundRecords = get_backmoney_data_by_no($citypy, $contractnum);
        if (empty($refundRecords) || (is_array($refundRecords) && count($refundRecords) == 0)) {
            return true;
        }
        //将合同回款记录插入到经管系统的数据库中
        if (!empty($refundRecords)) {
            $contract_model = D("Contract");
            $payment_model = D("PaymentRecord");

            $conf_where = "ID = '" . $contract_id . "'";
            $field_arr = array("CASE_ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            // 获取项目的类型
            if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                $scaleType = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
            }

            foreach ($refundRecords as $key => $val) {
                $refund_data["MONEY"] = $val["money"];
                $refund_data["CREATETIME"] = $val["date"];
                $refund_data["REMARK"] = $val["note"];
                $refund_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                $refund_data["CONTRACT_ID"] = $contract_id;
                $insert_reund_id = $payment_model->add_refund_records($refund_data);
                if (!$insert_reund_id) {
                    return false;
                } else {
                    //新增收益明细记录
                    if ($scaleType == self::YG) {
                        $income_info['INCOME_FROM'] = 11;
                    } else if ($scaleType == self::FWFSC) {
                        $income_info['INCOME_FROM'] = 22;
                    }
                    $taxrate = get_taxrate_by_citypy($citypy);
                    $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);
                    $income_info['OUTPUT_TAX'] = $tax;

                    $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                    $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['INCOME'] = $val["money"];
                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                    $income_info['OCCUR_TIME'] = $val["date"];
                    $income_info['PAY_ID'] = $insert_reund_id;
                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                    $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['ORG_PAY_ID'] = $insert_reund_id;

                    $ProjectIncome_model = D("ProjectIncome");
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if (!$res) {
                        return false;
                    }
                }
            }

        }
        return $insert_reund_id ? $insert_reund_id : false;
    }

    /**
     * +----------------------------------------------------------
     *根据合同号和城市拼音，获取合同的开票记录,并将数据同步到经管系统
     * +----------------------------------------------------------
     * @param  $contractnum 合同号
     * @param  $contract_id 合同id
    +----------------------------------------------------------
     * @param $citypy 所在城市拼音
    +----------------------------------------------------------
     * @return bool
     */
    public function save_invoice_data($contractnum, $contract_id, $citypy = "nj") {
        load("@.contract_common");
        $invoiceRecords = get_invoice_data_by_no($citypy, $contractnum);
        if (empty($invoiceRecords) || (is_array($invoiceRecords) && count($invoiceRecords) == 0)) {
            return true;
        }
        //将合同开票记录插入到经管系统的数据库中
        if (!empty($invoiceRecords)) {
            $billing_model = D("BillingRecord");
            $billing_status = $billing_model->get_invoice_status();

            $contract_model = D("Contract");
            $conf_where = "ID = '$contract_id'";
            $field_arr = array("CASE_ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                $scaleType = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
            }

            foreach ($invoiceRecords as $key => $val) {
                $invoice_data["INVOICE_MONEY"] = $val["money"];
                $invoice_data["TAX"] = $val["tax"];
                $invoice_data["INVOICE_NO"] = $val["invono"];
                $invoice_data["REMARK"] = $val["note"];
                $invoice_data["INVOICE_TIME"] = $val["date"];
                $invoice_data["STATUS"] = $billing_status["have_invoiced"];
                $invoice_data["CONTRACT_ID"] = $contract_id;
                $invoice_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                $invoice_data["CREATETIME"] = $val["date"];
                $invoice_data["INVOICE_TYPE"] = 1;
                if ($val['type']) {
                    // 发票类型，如果发票类型不为1或2，则将发票类型设置为服务费
                    // 否则设置为1（广告费）或2（服务费）
                    if (!in_array($val['type'], array(1, 2))) {
                        $val['type'] = 2;
                    }
                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                }
                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);
                if (!$insert_invoice_id) {
                    return false;
                } else {
                    //新增收益明细记录
                    if ($scaleType == self::YG) {
                        $income_info['INCOME_FROM'] = 12;
                    } else if ($scaleType == self::FWFSC) {
                        $income_info['INCOME_FROM'] = 23;
                    }
                    $taxrate = get_taxrate_by_citypy($citypy);
                    $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);
                    $income_info['OUTPUT_TAX'] = $tax;

                    $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                    $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['INCOME'] = $val["money"];
                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                    $income_info['OCCUR_TIME'] = $val["date"];
                    $income_info['PAY_ID'] = $insert_invoice_id;
                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                    $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['ORG_PAY_ID'] = $insert_invoice_id;

                    $ProjectIncome_model = D("ProjectIncome");
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if (!$res) {
                        return false;
                    }
                }
            }
        }
        return $insert_invoice_id ? $insert_invoice_id : false;
    }

    /**
     * 检查是否填写好预算费用
     * @param $projectID
     * @return bool
     */
    private function checkFeesPassed($projectID) {
        $sql = "
            SELECT t.id,
                   t.scaleType
            FROM erp_case t
            WHERE t.project_id = {$projectID}
        ";

        $arrCases = M()->query($sql);
        if (is_array($arrCases) && count($arrCases)) {
            foreach($arrCases as $key =>$case) {
                if (!in_array($case['SCALETYPE'], $this->needCheckFees)) {
                    unset($arrCases[$key]);
                } else {
                    $arrTargetCases []= $case['ID'];
                }
            }

            if (count($arrCases) == 0) {
                return true;
            }

            $strCases = implode(',', $arrTargetCases);
            $sql = "
                SELECT count(1) CNT
                FROM ERP_PRJBUDGET t
                WHERE t.case_id IN ({$strCases}) AND t.SUMPROFIT > 0
            ";

            $countRec = M()->query($sql);
            if (is_array($countRec) && $countRec[0]['CNT'] == count($arrCases)) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }


    public function ajaxIsFundPoolProject() {
        $projId = intval($_GET['projId']);
        if ($projId) {
            $dbResult = D('House')->get_isfundpool_by_prjid($projId);
        } else {
            $dbResult = false;
        }

        ajaxReturnJSON($dbResult);
    }

    //判断是否有同一值
    public function isRepeatValue($where){
        $sql = "select Amount from erp_feescale".$where;
        $amount_arr = D()->query($sql);
        foreach ($amount_arr as $amounts) {
            if ($_POST['AMOUNT'] == $amounts['AMOUNT']  ) {
                $result['status'] = 0;
                $result['msg'] = g2u('同一标准值不能相同');
                return $result;
            }else{
                $result['status'] = 1;
            }
        }
        $result['status'] = 1;
        return $result;
    }
}

?>