<?php

class HouseAction extends ExtendAction {

    const HIDDEN_FORM_COLUMN = 0;

    private $model;
    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();

    private $isedit;

    /**
     * ����Ԥ������Ƿ���ڵ���Ŀ
     * @var array
     */
    protected $needCheckFees = array(self::DS, self::FX, self::FWFSC);

    //���캯��
    public function __construct() {
        $this->model = new Model();
        parent::__construct();

        //TAB URL����
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
            if (judgeFlowEdit($_REQUEST['flowId'], $_SESSION['uinfo']['uid'])) {//�ж��Ƿ�ص�������
                $this->isedit = true;
            }
        }

    }

    /**
     * �����ʽ������ֶ�
     * @param $form
     */
    private function hiddenFundPool(&$form) {
        // �Ƿ��ʽ�ط���
        $form->setMyField('ISFUNDPOOL', 'FORMVISIBLE', self::HIDDEN_FORM_COLUMN);

        // �����ʽ������
        $form->setMyField('SPECIALFPDESCRIPTION', 'FORMVISIBLE', self::HIDDEN_FORM_COLUMN);

        // �ʽ�ر���
        $form->setMyField('FPSCALE', 'FORMVISIBLE', self::HIDDEN_FORM_COLUMN);
        $form->setMyField('FPSCALE', 'GRIDVISIBLE', self::HIDDEN_FORM_COLUMN);
    }

    //����
    function projectDetail() {
        $prjId = $this->_merge_url_param['prjid'];//$_REQUEST['prjid'];
        $this->project_pro_auth($prjId, $_REQUEST['flowId']);
        $project = D('Erp_project')->where("ID=$prjId")->find();

        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(114);
        if ($_REQUEST['CHANGE'] == -1) {//���״̬

            $form->setMyField('CIT_ID', 'READONLY', '-1', false);
            $form->setMyField('CONTRACT_NUM', 'READONLY', '-1', false);
            $form->setMyField('REL_PROPERTY', 'READONLY', '-1', false);

            $form->changeRecord = true;
            $form->changeRecordVersionId = $_REQUEST['CID'];
            if ($_REQUEST['active'] == '1') {
                $form->FormeditType = 2;//�鿴״̬
            }
        } else {
            if ($project['PSTATUS'] > 2) $form->FormeditType = 2;
        }
        if ($_REQUEST['flowId']) {
            if (judgeFlowEdit($_REQUEST['flowId'], $_SESSION['uinfo']['uid'])) {//�ж��Ƿ�ص�������
                $form->FormeditType = 1;//���Ա༭״̬

            }
        }

        // �Ƿ�Ϊ���ҷ��ճ���Ŀ
        if ($project['SCSTATUS'] !== null && $project['SCSTATUS'] >= 1 && $project['MSTATUS'] === null) {
            $this->hiddenFundPool($form);
        }

        //������Ŀ�����Ƿ������������ֶ�
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
            /***�жϺ�ͬ�Ƿ��ϴ�***/
            if ($_POST['CONTRACT_FILE'] == '') {
                $result['status'] = 0;
                $result['msg'] = g2u('����ʧ�ܣ���Ŀ��ͬ��ظ��������ϴ�!');

                echo json_encode($result);
                exit;
            }

            //�ж��½���Ŀ�����Ƿ��ظ�������ɾ��������״̬����ֹ����Ŀ����Ŀ���ƿ����ظ�
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
                                $result['msg'] = g2u('��ҵ�����Ѿ��д���Ŀ���ƣ������');
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
                                $result['msg'] = g2u('��ҵ�����Ѿ��д���Ŀ���ƣ������');
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
                                $result['msg'] = g2u('��ҵ�����Ѿ��д���Ŀ���ƣ������');
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
        $form->FORMFORWARD = U('House/projectDetail', $this->_merge_url_param);  //�������ת
        $form = $form->getResult();

        $this->assign('project', $project);
        $this->assign('prjId', $prjId);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->assign('form', $form);
        $this->display('projectDetail');
    }
	//�޸Ŀͻ�����
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

    //����Ԥ��
    function projectBudget() {
        $prjId = $_REQUEST['prjid'];
        // ����Ŀִ����ֹ���ڸ�Ϊһ���23:59:59
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

        // �Ƿ�Ϊ���ҷ��ճ���Ŀ
        if ($project['SCSTATUS'] !== null && $project['SCSTATUS'] >= 1 && $project['MSTATUS'] === null) {
            $this->hiddenFundPool($form);
        }

        $form->GCBTN = $form->GCBTN . '<a href="javascript:;" onclick="budgetfeetotal();" class="btn btn-danger btn-sm">�ۺ�ͳ��</a>';
        if ($_REQUEST['showForm'] && $_REQUEST['ID']) {//�༭

            $form->setMyField('CASE_ID', 'ISVIRTUAL', -1, true);
            $form->setMyField('SCALETYPE', 'READONLY', -1, true);
			$form->FORMFORWARD = __APP__."/House/projectBudget/prjid/$prjId/CHANGE/".$this->_get("CHANGE")."/CID/".$this->_get("CID")."/active/".$this->_get('active')."/tabNum/".$_REQUEST['tabNum']."/SELECTID/".$_REQUEST['ID'].'/CASEID/$prjId/flowId/'.$_REQUEST['flowId'].'/RECORDID/'.$_REQUEST['RECORDID'].'/type/'.$_REQUEST['type'].'flowType/'.$_REQUEST['flowType'];
        } elseif ($_REQUEST['showForm']) {//����
            if ($_REQUEST['faction'] == 'saveFormData') {
                $cdata['CTIME'] = date('Y-m-d H:i:s', time());
                $cdata['CUSER'] = $_SESSION['uinfo']['uid'];
                $cdata['SCALETYPE'] = $_REQUEST['SCALETYPE'];
                $cdata['PROJECT_ID'] = $prjId;
                $caseid = D('Erp_case')->add($cdata);
                if ($caseid) $form->setMyFieldVal('CASE_ID', $caseid, true);
                else {
                    js_alert('�û����ʧ�ܣ�');
                    exit();
                }
            }
        }
        $form->DELABLE = 0;
        //echo $form->getFilter();
		
        $cparam = '&operate=' . $_GET['operate'] . '&flowId=' . $_GET['flowId'].'&stemd='.time();
        $children = array(
            array('Ԥ�����', U('/House/budGetFee?CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID") . '&active=' . $this->_get('active') . $cparam)),
            array('�����շѱ�׼', U('/House/feescale?SCALETYPE=1&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('�н�Ӷ���׼', U('/House/feescale?SCALETYPE=2&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            //array('��ҵ����Ӷ���׼',U('/House/feescale?SCALETYPE=3')),
            array('�н�ɽ�����׼', U('/House/feescale?SCALETYPE=4&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('��ҵ���ʳɽ�����׼', U('/House/feescale?SCALETYPE=5&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('��������׼', U('/House/feescale?SCALETYPE=6&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
        );

        // ����ǵ����ķ��ҷ��ճ���Ŀ����ȥ���������շѱ�׼��ҳ��

        if ($project['SCSTATUS'] !== null && $project['SCSTATUS'] >= 1 && $project['MSTATUS'] === null) {
            $children = array(
                array('Ԥ�����', U('/House/budGetFee?CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID") . '&active=' . $this->_get('active') . $cparam)),
                array('�н�Ӷ���׼', U('/House/feescale?SCALETYPE=2&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
                array('�н�ɽ�����׼', U('/House/feescale?SCALETYPE=4&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
                array('��ҵ���ʳɽ�����׼', U('/House/feescale?SCALETYPE=5&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
                array('��������׼', U('/House/feescale?SCALETYPE=6&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            );
        }
        //����ǵ��̻����ҵ������ӡ��ⲿ������׼��ҳ�棬���ҷ��ճ�û���ⲿ������׼
        if(($project['MSTATUS'] !== null && $project['MSTATUS'] >=1) || ($project['BSTATUS'] !== null && $project['BSTATUS'] >=1))  {
            $children = array(
            array('Ԥ�����', U('/House/budGetFee?CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID") . '&active=' . $this->_get('active') . $cparam)),
            array('�����շѱ�׼', U('/House/feescale?SCALETYPE=1&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('�н�Ӷ���׼', U('/House/feescale?SCALETYPE=2&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('�ⲿ�ɽ�����', U('/House/feescale?SCALETYPE=3&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('�н�ɽ�����׼', U('/House/feescale?SCALETYPE=4&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('��ҵ���ʳɽ�����׼', U('/House/feescale?SCALETYPE=5&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
            array('��������׼', U('/House/feescale?SCALETYPE=6&CHANGE=' . $this->_get("CHANGE") . '&CID=' . $this->_get("CID")) . $cparam),
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

    //Ԥ�����
    function budGetFee() {

        $model = new Model();
        if ($this->_get('parentchooseid')) {
            $casedata = $model->query("select PROJECT_ID,SUMPROFIT,B.SCALETYPE from ERP_PRJBUDGET A left join ERP_CASE B on A.CASE_ID=B.ID where A.ID=" . $this->_get('parentchooseid'));

            // ������ڱ��״̬�����ȴӱ�����л�ȡ����
            if ($this->_request('CHANGE') == -1 && isset($_REQUEST['CID'])) {
                $sql = "SELECT t.valuee SUMPROFIT FROM ERP_CHANGELOG t WHERE t.CID = {$_REQUEST['CID']} AND t.COLUMS = 'SUMPROFIT' and t.BID=". $this->_get('parentchooseid');
                $changeLog = $model->query($sql);
                if (is_array($changeLog) && count($changeLog)) {
                    $estimate_money = $changeLog[0]['SUMPROFIT'];
                }
            }

            if ($casedata[0]) {
                $project = D('Erp_project')->where("ID=" . $casedata[0]['PROJECT_ID'])->find();
                // ���û�б�������ԭ��ȡ����
                if ($estimate_money === null) {
                    $estimate_money = $casedata[0]['SUMPROFIT'];//Ԥ��������
                }
            }
        }

        $house = M('Erp_house')->where("PROJECT_ID=$project[ID]")->find();
        Vendor('Oms.Changerecord');
		$upcounts = 0;
        $changer = new Changerecord();
        $changer->fields = array('REMARK', 'AMOUNT', 'RATIO');
        //$changer->fields=array('AMOUNT','RATIO');
        $ajaxReturnIDs = array();  // �첽����ID
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

                        if($this->_post('CHANGE') == -1){//����
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
                $budget->set_budgetfee($this->_post('BUDGETID'), $buddata);//���浽Ԥ���
            }

            // ������첽����
            if ($_REQUEST['is_ajax'] == 1) {
                echo json_encode(array(
                    'code' => 'ok',
                    'data' => $upcounts
                ));
                exit;
            }
        }

        $return_tr = array(
            '39' => '<td rowspan="46">�����������</td><td>���ͷ����</td><td colspan="2">�н��</td>',
            '41' => '<td rowspan="2">����Ӫ����</td><td colspan="2">���ŷ�</td>',
            '42' => '<td colspan="2">�绰��</td>',
            '45' => '<td rowspan="9">������</td><td rowspan="3">���ط�</td><td>����/�̳�</td>',
            '46' => '<td>��С��</td>',
            '47' => '<td>д��¥</td>',
            '49' => '<td rowspan="2">�⳵��(����)</td><td>��ͳ�</td>',
            '50' => '<td>���⳵</td>',
            '51' => '<td colspan="2">�����(����)</td>',
            '53' => '<td>�ƹ��</td><td>SEO/SEM�ƹ�</td>',
            '54' => '<td colspan="2">����ů����</td>',
            '55' => '<td colspan="2">����ʳƷ��</td>',
            '57' => '<td rowspan="2">��Ա����</td><td colspan="2">��˾Ա��</td>',
            '58' => '<td colspan="2">��ְ��Ա</td>',
            '60' => '<td rowspan="4">ҵ���</td><td colspan="2">ҵ�����</td>',
            '61' => '<td colspan="2">��������</td>',
            '62' => '<td colspan="2">ʵ��Ӧ��</td>',
            '63' => '<td colspan="2">���÷�</td>',
            '65' => '<td rowspan="4">������</td><td colspan="2">����Ʒ</td>',
            '66' => '<td colspan="2">��չ��</td>',
            '67' => '<td colspan="2">��ҳ</td>',
            '68' => '<td colspan="2">Xչ��</td>',
            '70' => '<td rowspan="5">�ⲿ����</td><td colspan="2">����</td>',
            '71' => '<td colspan="2">LED</td>',
            '72' => '<td colspan="2">����/����</td>',
            '73' => '<td colspan="2">��̨</td>',
            '74' => '<td colspan="2">��ֽ/��־</td>',
            '76' => '<td rowspan="4">������</td><td colspan="2">����</td>',
            '77' => '<td colspan="2">��ҵ����</td>',
            '78' => '<td colspan="2">�ͻ�</td>',
            '79' => '<td colspan="2">����</td>',
            '80' => '<td colspan="3">֧������������</td>',
            '82' => '<td>��Ŀ�ֳ�</td><td colspan="2">����ֳ�</td>',
            '84' => '<td rowspan="4">������</td><td colspan="2">�ϴ���</td>',
            '85' => '<td colspan="2">�´���</td>',
            '86' => '<td colspan="2">�н����</td>',
            '87' => '<td colspan="2">��������</td>',
            '89' => '<td>�ɽ���</td><td colspan="2">�ɽ�����</td>',
            '91' => '<td>�ڲ�Ӷ��</td><td colspan="2">�ڲ����</td>',
            '93' => '<td>�ⲿӶ��</td><td colspan="2">�ⲿ����</td>',
            '95' => '<td>POS������</td><td colspan="2">POS������</td>',
            '96' => '<td colspan="3">˰��(֧�����������õ�10%)</td>',
            '97' => '<td colspan="3">����</td>',
            '108' => '<td colspan="3">���ֳɱ�</td>',
            '109' => '<td colspan="3">��������</td>',
            '110' => '<td colspan="3">����������</td>',

            '101' => '<td rowspan="3">˰����Ŀ���(���ο�)</td><td colspan="3">���ʽ������Ŀ˰��</td>',
            '102' => '<td colspan="3">˰����Ŀ����</td>',
            '103' => '<td colspan="3">˰����Ŀ������</td>',
            '98' => '<td rowspan="4">�����������</td><td colspan="3">���Ԥ�㣨�ۺ�ۣ�</td>',
            '99' => '<td colspan="3">�ز���ҳ���͹�棨�ۺ�</td>',
            '106' => '<td colspan="3">�۳�����+����֧������</td>',
            '107' => '<td colspan="3">�۳�����+����֧��������</td>',
        );
		if($this->channelid==1){
			$return_tr['98'] = '<td rowspan="4">�����������</td><td colspan="3">���Ԥ��</td>';
			$return_tr['99'] = '<td colspan="3">�ز���ҳ���͹��</td>';
		}
        $noinput_arr = array(101, 102, 103, 106, 107, 108, 109, 110);
        $html = '';
        //����
        if ($casedata[0]['SCALETYPE'] == 1 ) {
            $model = M();
            if ($_REQUEST['CHANGE'] == '-1') {
                $isrout_data = $model->query("select isroutine({$casedata[0]['PROJECT_ID']} , {$_REQUEST['CID']}, {$_REQUEST['parentchooseid']}) as T from dual");
            } else {
                $isrout_data = $model->query('select isroutine(' . $casedata[0]['PROJECT_ID'] . ') as T from dual');
            }
            $isroutine = $isrout_data[0]['T'];
            if ($isroutine == 1) $isrout_html = '[�ǳ���]';
            else $isrout_html = '[����]';
        }
        //����
        if ($casedata[0]['SCALETYPE'] == 2) {
            $model = M();
            if ($_REQUEST['CHANGE'] == '-1') {
                $isrout_data = $model->query("select isfxroutine({$casedata[0]['PROJECT_ID']} , {$_REQUEST['CID']}, {$_REQUEST['parentchooseid']}) as T from dual");
            } else {
                $isrout_data = $model->query('select isfxroutine(' . $casedata[0]['PROJECT_ID'] . ') as T from dual');
            }

            $isroutine = $isrout_data[0]['T'];
            if ($isroutine == 1) $isrout_html = '[�ǳ���]';
            else $isrout_html = '[����]';
        }
        //���ҷ��ճ�
        if ($casedata[0]['SCALETYPE'] == 8) {
            $model = M();
            if ($_REQUEST['CHANGE'] == '-1') {
                $isrout_data = $model->query("select isfwfscroutine({$casedata[0]['PROJECT_ID']} , {$_REQUEST['CID']}, {$_REQUEST['parentchooseid']}) as T from dual");
            } else {
                $isrout_data = $model->query('select isfwfscroutine(' . $casedata[0]['PROJECT_ID'] . ') as T from dual');
            }
            $isroutine = $isrout_data[0]['T'];
            if ($isroutine == 1) $isrout_html = '[�ǳ���]';
            else $isrout_html = '[����]';
        }

		if($_REQUEST['CHANGE'] == '-1'){
			$list = M('Erp_budgetsale')->where("  PROJECTT_ID=" . $project['ID'])->select();
			foreach($list as $vvv){
				$budgetsaleId[] = $vvv['ID'];
			}
			$bids = implode(',',$budgetsaleId);
			$sets =  $changer->getFieldRecords('ERP_BUDGETSALE',$bids,$_REQUEST['CID'],'SETS' );
			if($sets['VALUEE']!=$sets['ORIVALUEE']){
				$sets =  $sets['VALUEE'].'<span style="color:#f00;">[ԭ]</span>'.$sets['ORIVALUEE'];
			}else{
				$sets =   $sets['ORIVALUEE'];
			}

			$customers =  $changer->getFieldRecords('ERP_BUDGETSALE',$bids,$_REQUEST['CID'],'CUSTOMERS' );
			//$customers =  $customers['VALUEE'].'<span style="color:#f00;">[ԭ]</span>'.$customers['ORIVALUEE'];
			if($customers['VALUEE']!=$customers['ORIVALUEE']){
				$customers =  $customers['VALUEE'].'<span style="color:#f00;">[ԭ]</span>'.$customers['ORIVALUEE'];
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
			<tr><td colspan='3' rowspan='2'  align='center'  >Ŀ��ֽ�</td><td colspan='2' align='center'  >Ԥ���ɽ�����</td><td colspan='2' align='center'  >Ԥ��������</td></tr>
			<tr> <td colspan='2' align='center'  > " . $sets . "</td><td colspan='2' align='center'  >" . $customers . " </td></tr>

			<tr><td colspan='4'>��������</td> <td>��Ԫ��</td> <td> 	����ռ�ȣ�%��</td> <td>����˵��</td>  </tr><input type='hidden' name='postfee' value='save'><input type='hidden' name='CHANGE' value='" . $this->_get('CHANGE') . "'><input type='hidden' name='CID' value='" . $this->_get('CID') . "'><input type='hidden' name='BUDGETID' value='" . $this->_get('parentchooseid') . "'>";

        #���·���
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
            $optt['CID'] = $this->_get('CID');//����汾id

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
                    $infoAmount = $changarr['AMOUNT'] ? ($changarr['AMOUNT']['ISNEW'] ==-1 ? "<span class='fred'>[����]</span>" : "<span class='fclos fred'>[ԭ]" . $oriAmount . "</span>") : '';
                    $idAmount = $valueArr[$k . '_ID'];

                    $currentRatio = $changarr['RATIO'] ? $changarr['RATIO']['VALUEE'] : $valueArr[$k . '_RATIO'];
                    if (!$currentRatio) $currentRatio = 0;
                    $oriRatio = $changarr['RATIO'] ? $changarr['RATIO']['ORIVALUEE'] : '';
                    $infoRatio = $changarr['RATIO'] ? ($changarr['RATIO']['ISNEW']==-1 ? "<span class='fred'>[����]</span>" : "<span class='fclos fred'>[ԭ]" . $oriRatio . "</span>") : '';

                    $idRatio = $valueArr[$k . '_ID'];

                    $currentRemark = $changarr['REMARK'] ? $changarr['REMARK']['VALUEE'] : $valueArr[$k . '_REMARK'];
                    $oriRemark = $changarr['REMARK'] ? $changarr['REMARK']['ORIVALUEE'] : '';
                    $infoRemark = $changarr['REMARK'] ? ($changarr['REMARK']['ISNEW'] ==-1? "<span class='fred'>[����]</span>" : "<span class='fclos fred'>[ԭ]" . $oriRemark . "</span>") : '';


                    if ($_REQUEST['active'] == '1' && $this->isedit == false) {

                        $html = $html . $v . "<td>" . $currentAmount . $infoAmount . "</td> <td><span >" . $currentRatio . $infoRatio . "</span> %</td><td>" . $currentRemark . $infoRemark . "</td> </tr>";

                        $showbutton = 0;
                    } else {
                        if (in_array($k, $noinput_arr)) {

                            $html = $html . $v . "<td> <input name='" . $k . "_AMOUNT' fid='$k' value='" . $currentAmount . "'   $isonline type='text' size='15' datatype='/^-?\d+(\.\d{0,2})?$/' errormsg='����������' ignore='ignore'/><input name='" . $k . "_AMOUNT_OLD'  value='" . $currentAmount . "' type='hidden'  />  " . $infoAmount . " </td> <td> </td><td>   <input name='" . $k . "_ID'  value='" . $valueArr[$k . '_ID'] . "' type='hidden'/> <input name='" . $k . "_STATUS'  value='" . $valueArr[$k . '_STATUS'] . "' type='hidden'/>  </td> </tr>";


                        } else {

                            $html = $html . $v . "<td> <input name='" . $k . "_AMOUNT' fid='$k' value='" . $currentAmount . "' class='AMOUNT' $isonline type='text' size='15' datatype='/^-?\d+(\.\d{0,2})?$/' errormsg='����������' ignore='ignore'/><input name='" . $k . "_AMOUNT_OLD'  value='" . $currentAmount . "' type='hidden'  />  " . $infoAmount . " </td> <td><span >" . $valueArr[$k . '_RATIO'] . "</span> %  <input fid='$k'  name='" . $k . "_RATIO' $isonline class='RATIO'  value='" . $currentRatio . "' type='hidden' /> <input    name='" . $k . "_RATIO_OLD'    value='" . $currentRatio . "' type='hidden' />  " . $infoRatio . " </td><td> <input name='" . $k . "_REMARK' datatype='*' errormsg='������в��Ϸ��ķ���' ignore='ignore' value='" . $currentRemark . "'  type='text' size='20'/> <input name='" . $k . "_REMARK_OLD'  value='" . $currentRemark . "' type='hidden'/>" . $infoRemark . " <input name='" . $k . "_ID'  value='" . $valueArr[$k . '_ID'] . "' type='hidden'/> <input name='" . $k . "_STATUS'  value='" . $valueArr[$k . '_STATUS'] . "' type='hidden'/> </td> </tr>";
                        }

                        $showbutton = -1;
                    }

                } else {
                    if (in_array($k, $noinput_arr)) {
                        $html = $html . $v . "<td> <input name='" . $k . "_AMOUNT' fid='$k' value='" . $valueArr[$k . '_AMOUNT'] . "'  $isonline type='text' size='15' datatype='/^-?\d+(\.\d{0,2})?$/' errormsg='����������' ignore='ignore'/></td> <td> </td><td><input name='" . $k . "_ID' value='" . $valueArr[$k . '_ID'] . "' type='hidden'/>  </td> </tr>";

                    } else {
                        $html = $html . $v . "<td> <input name='" . $k . "_AMOUNT' fid='$k' value='" . $valueArr[$k . '_AMOUNT'] . "' class='AMOUNT' $isonline type='text' size='15' datatype='/^-?\d+(\.\d{0,2})?$/' errormsg='����������' ignore='ignore'/></td> <td><span >" . $valueArr[$k . '_RATIO'] . "</span> %  <input fid='$k'  name='" . $k . "_RATIO' $isonline class='RATIO'  value='" . $valueArr[$k . '_RATIO'] . "' type='hidden' /></td><td> <input name='" . $k . "_REMARK' datatype='*' errormsg='������в��Ϸ��ķ���' ignore='ignore' value='" . $valueArr[$k . '_REMARK'] . "'  type='text' size='20'/><input name='" . $k . "_ID' value='" . $valueArr[$k . '_ID'] . "' type='hidden'/></td> </tr>";
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

    //Ԥ����� �ۺ�
    function budGetFeeTotal() {
        $model = new Model();
        $estimate_money = 0;
        if ($this->_get('prjid')) {
            $casedata = $model->query("select A.ID,PROJECT_ID,SUMPROFIT from ERP_PRJBUDGET A left join ERP_CASE B on A.CASE_ID=B.ID where B.PROJECT_ID=" . $this->_get('prjid'));

            $project = D('Erp_project')->where("ID=" . $this->_get('prjid'))->find();


        }


        $return_tr = array(
            '39' => '<td rowspan="46">�����������</td><td>���ͷ����</td><td colspan="2">�н��</td>',
            '41' => '<td rowspan="2">����Ӫ����</td><td colspan="2">���ŷ�</td>',
            '42' => '<td colspan="2">�绰��</td>',
            '45' => '<td rowspan="9">������</td><td rowspan="3">���ط�</td><td>����/�̳�</td>',
            '46' => '<td>��С��</td>',
            '47' => '<td>д��¥</td>',
            '49' => '<td rowspan="2">�⳵��(����)</td><td>��ͳ�</td>',
            '50' => '<td>���⳵</td>',
            '51' => '<td colspan="2">�����(����)</td>',
            '53' => '<td>�ƹ��</td><td>SEO/SEM�ƹ�</td>',
            '54' => '<td colspan="2">����ů����</td>',
            '55' => '<td colspan="2">����ʳƷ��</td>',
            '57' => '<td rowspan="2">��Ա����</td><td colspan="2">��˾Ա��</td>',
            '58' => '<td colspan="2">��ְ��Ա</td>',
            '60' => '<td rowspan="4">ҵ���</td><td colspan="2">ҵ�����</td>',
            '61' => '<td colspan="2">��������</td>',
            '62' => '<td colspan="2">ʵ��Ӧ��</td>',
            '63' => '<td colspan="2">���÷�</td>',
            '65' => '<td rowspan="4">������</td><td colspan="2">����Ʒ</td>',
            '66' => '<td colspan="2">��չ��</td>',
            '67' => '<td colspan="2">��ҳ</td>',
            '68' => '<td colspan="2">Xչ��</td>',
            '70' => '<td rowspan="5">�ⲿ����</td><td colspan="2">����</td>',
            '71' => '<td colspan="2">LED</td>',
            '72' => '<td colspan="2">����/����</td>',
            '73' => '<td colspan="2">��̨</td>',
            '74' => '<td colspan="2">��ֽ/��־</td>',
            '76' => '<td rowspan="4">������</td><td colspan="2">����</td>',
            '77' => '<td colspan="2">��ҵ����</td>',
            '78' => '<td colspan="2">�ͻ�</td>',
            '79' => '<td colspan="2">����</td>',
            '80' => '<td colspan="3">֧������������</td>',
            '82' => '<td>��Ŀ�ֳ�</td><td colspan="2">����ֳ�</td>',
            '84' => '<td rowspan="4">������</td><td colspan="2">�ϴ���</td>',
            '85' => '<td colspan="2">�´���</td>',
            '86' => '<td colspan="2">�н����</td>',
            '87' => '<td colspan="2">��������</td>',
            '89' => '<td>�ɽ���</td><td colspan="2">�ɽ�����</td>',
            '91' => '<td>�ڲ�Ӷ��</td><td colspan="2">�ڲ����</td>',
            '93' => '<td>�ⲿӶ��</td><td colspan="2">�ⲿ����</td>',
            '95' => '<td>POS������</td><td colspan="2">POS������</td>',
            '96' => '<td colspan="3">˰��(֧�����������õ�10%)</td>',
            '97' => '<td colspan="3">����</td>',
            '108' => '<td colspan="3">���ֳɱ�</td>',
            '109' => '<td colspan="3">��������</td>',
            '110' => '<td colspan="3">����������</td>',
            '101' => '<td rowspan="3">˰����Ŀ���(���ο�)</td><td colspan="3">���ʽ������Ŀ˰��</td>',
            '102' => '<td colspan="3">˰����Ŀ����</td>',
            '103' => '<td colspan="3">˰����Ŀ������</td>',
            '98' => '<td rowspan="4">�����������</td><td colspan="3">���Ԥ�㣨�ۺ�ۣ�</td>',
            '99' => '<td colspan="3">�ز���ҳ���͹�棨�ۺ�</td>',
            '106' => '<td colspan="3">�۳�����+����֧������</td>',
            '107' => '<td colspan="3">�۳�����+����֧��������</td>',
        );
        $sets = M('Erp_budgetsale')->where("ISVALID=-1 and PROJECTT_ID=" . $project['ID'])->sum('SETS');
        $customers = M('Erp_budgetsale')->where("ISVALID=-1 and PROJECTT_ID=" . $project['ID'])->sum('CUSTOMERS');
        $noinput_arr = array(101, 102, 103, 106, 107, 108, 109, 110);

        $html = '';
        $html = $html . "<table width='90%' cellspacing='0' cellpadding='10' border='1' style='border-collapse: collapse;' align='center'>
			
			
			<tr><td colspan='3' rowspan='2'  align='center'  >Ŀ��ֽ�</td><td colspan='2' align='center'  >Ԥ���ɽ�����</td><td colspan='2' align='center'  >Ԥ��������</td></tr>
			<tr> <td colspan='2' align='center'  > " . $sets . "</td><td colspan='2' align='center'  >" . $customers . " </td></tr>
			
			<tr><td colspan='4'>��������</td> <td>��Ԫ��</td> <td> 	����ռ�ȣ�%��</td> <td>����˵��</td>  </tr><input type='hidden' name='postfee' value='save'><input type='hidden' name='CHANGE' value='" . $this->_get('CHANGE') . "'><input type='hidden' name='CID' value='" . $this->_get('CID') . "'><input type='hidden' name='BUDGETID' value='" . $this->_get('parentchooseid') . "'>";

        # ����
        //$offline_cost = unserialize($row['offline_cost']);
        $valueArr = array();
        foreach ($casedata as $caseone) {
            $estimate_money += $caseone['SUMPROFIT'];//Ԥ��������

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

    //Ŀ��ֽ�
    function budgetSale() {
        $prjId = $_REQUEST['prjid'];
        //$this->project_auth($prjId,array(1,2),$_REQUEST['flowId']);//��ĿȨ���ж�
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
                /*$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">����</a>'.
                '<a id="j-search" class="j-showalert" href="javascript:;">����</a>'.
                '<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">ˢ��</a>';*/
            }
            $form->where("PROJECTT_ID=$prjId");
        } else {
            if ($project['PSTATUS'] > 2 && $this->isedit == false && $_REQUEST['tabNum']!=202) {
                $form->CZBTN = ' ';
                $form->ADDABLE = 0;
                /*$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">����</a>'.
                '<a id="j-search" class="j-showalert" href="javascript:;">����</a>'.
                '<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">ˢ��</a>';*/
            }
            $form->setMyFieldVal('ISVALID', '-1', true);
            $form->where("PROJECTT_ID=$prjId AND ISVALID = -1");
        }

        if ($_REQUEST['showForm']) {
            $form->setMyFieldVal('PROJECTT_ID', $prjId, true);
        }

        //����Ǳ༭������
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

    //�շѱ�׼
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
							 '%ISVALID%==0' => '<a class="contrtable-link fedit btn btn-primary btn-xs" onclick="editThis(this);" title="�༭"  href="javascript:void(0);">
			<i class="glyphicon glyphicon-edit"></i>
			</a>
			<a class="contrtable-link fedit btn btn-success btn-xs" onclick="viewThis(this);" title="�鿴"   href="javascript:void(0);">
			<i class="glyphicon glyphicon-eye-open"></i>
			</a>
			<a class="contrtable-link btn btn-danger btn-xs" onclick="delThis(this);"  title="ɾ��" href="javascript:void(0);">
			<i class="glyphicon glyphicon-trash"></i>
			</a>' 
						 );
		}else{
		   $form->setMyFieldVal('ISVALID', '-1', true);
		   $form->where('SCALETYPE=' . $_REQUEST['SCALETYPE'] . " AND ISVALID = -1");

		}

        //������ֻ�� �����շѱ�׼���н�Ӷ���׼����ǰӶ�ͺ�Ӷ�ķ���
        if ($casedata[0]['SCALETYPE'] == 2 && ($_REQUEST['SCALETYPE'] == 1 || $_REQUEST['SCALETYPE'] == 2)) {
            $form->setMyfield('MTYPE', 'FORMVISIBLE', -1, false);
            $form->setMyfield('MTYPE', 'GRIDVISIBLE', -1, false);
            $form->setMyfield('EXECSTIME', 'FORMVISIBLE', -1, false);
            $form->setMyfield('EXECSTIME', 'GRIDVISIBLE', -1, false);
            $form->setMyfield('EXECETIME', 'FORMVISIBLE', -1, false);
            $form->setMyfield('EXECETIME', 'GRIDVISIBLE', -1, false);
        }
        if (($casedata[0]['SCALETYPE'] == 2 ) && ($_REQUEST['SCALETYPE'] == 1 || $_REQUEST['SCALETYPE'] == 2|| $_REQUEST['SCALETYPE'] == 3|| $_REQUEST['SCALETYPE'] == 4|| $_REQUEST['SCALETYPE'] ==5)) { //���� �� �ǵ����շѱ�׼
            $form->setMyField('STYPE', 'FORMVISIBLE', -1, false);
            $form->setMyField('STYPE', 'GRIDVISIBLE', -1, false);
            $form->setMyField('AMOUNT', 'FIELDMEANS', 'ֵ', false);

            // $form->setMyField('PERCENTAGE','FORMVISIBLE',-1,false);
            // $form->setMyField('PERCENTAGE','GRIDVISIBLE',-1,false);

        }
        if ($project['PSTATUS'] > 2 && $this->isedit == false && $this->_get('CHANGE') != -1) {
            $form->CZBTN = ' ';
            $form->ADDABLE = 0;
            /*$form->GCBTN = '<a id="j-sequence" class="j-showalert" href="javascript:;">����</a>'.
            '<a id="j-search" class="j-showalert" href="javascript:;">����</a>'.
            '<a class="j-refresh" onclick="window.location.reload();" href="javascript:;">ˢ��</a>';*/
        }

        $form->setMyFieldVal('PRJ_ID', $_REQUEST['parentchooseid'], true)->setMyFieldVal('SCALETYPE', $_REQUEST['SCALETYPE'], true);
        if ($_REQUEST['showForm'] == 3) $form->setMyFieldVal('PAYDATE', date('Y-m-d', time()), true);
        if ($_REQUEST['faction'] == 'saveFormData') {
            $execStime = strtotime($_POST['EXECSTIME']);
            $execEtime = strtotime($_POST['EXECETIME']);
            if ($execStime > $execEtime) {
                $result['status'] = 0;
                $result['msg'] = g2u('ִ�п�ʼʱ��ӦС��ִ�н���ʱ��');
                echo json_encode($result);
                exit;
            }
            //ͬһ��׼ֵ������ͬ
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
            //�������жϣ����ж���Ч�����жϱ��
             if($_REQUEST['CID']) {
                 $where = $condition." AND CID = " . $_REQUEST['CID'] . " AND  ISVALID = 0 ";
                 $result = $this->isRepeatValue($where);
                 if($result['status'] == 0){
                     echo json_encode($result);
                     exit;
                 }
             }
        } else if ($_REQUEST['faction'] == 'delData') {  // ɾ����׼����
            if ($_REQUEST['CHANGE'] == -1) {
                $bid = $_REQUEST['ID'];
                $cid = $_REQUEST['CID'];
                $dbResult = D('erp_changelog')->where("TABLEE = 'ERP_FEESCALE' AND BID = {$bid} AND CID = {$cid}")->delete();
                if ($dbResult === false) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�������ڲ�����');
                    echo json_encode($result);
                    exit;
                }
            }
        }
		$form->colArr = array('ISVALID');
		
        //������ز���
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
		 
        if ($_REQUEST['flowType'] == 'lixiangbiangeng') { // ����������
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
                //�����Ѿ����ڵĹ�����
                $click = $workflow->nextstep($flowId);
                $form = $workflow->createHtml($flowId);

                if ($_REQUEST['savedata']) {
                    //��һ��
                    if ($_REQUEST['flowNext']) {
                        $str = $workflow->handleworkflow($_REQUEST);
                        if ($str) {
                            js_alert('����ɹ�', U('Flow/workStep'));
                        } else {
                            js_alert('����ʧ��');
                        }
                    } //ͨ����ť
                    else if ($_REQUEST['flowPass']) {
                        $str = $workflow->passWorkflow($_REQUEST);

                        if ($str) {
                            js_alert('ͬ��ɹ�', U('Flow/workStep'));
                        } else {
                            js_alert('ͬ��ʧ��');
                        }
                    } //�����ť
                    else if ($_REQUEST['flowNot']) {
                        $str = $workflow->notWorkflow($_REQUEST);
                        if ($str) {
                            //$project_model = D('Project');
                            // $project_model->update_finalaccounts_nopass_status($prjId);


                            js_alert('����ɹ�', U('Flow/workStep'));
                        } else {
                            js_alert('���ʧ��');
                        }
                    } //��ֹ��ť
                    else if ($_REQUEST['flowStop']) {
                        $auth = $workflow->flowPassRole($flowId);

                        if (!$auth) {
                            js_alert('δ�����ؾ���ɫ');
                            exit;
                        }

                        $str = $workflow->finishworkflow($_REQUEST);
                        if ($str) {
                            $CID = $_REQUEST['RECORDID'];
                            $changer->setRecords($CID);

                            $project_model = D('Project');
                            $project_model->set_project_change($prjId);//����������ͳ��
                            //$ress =$project_model->update_finalaccounts_status($prjId);

                            //����project ����
                            $PRO_NAME = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('PRO_NAME');
							 $DEV_ENT = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('DEV_ENT');
							  $CONTRACT_NUM = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('CONTRACT_NUM');

                            $UPDATE = M("Erp_project")->where("ID = $prjId")->setField("PROJECTNAME", $PRO_NAME);
							 $UPDATE = M("Erp_project")->where("ID = $prjId")->setField("CONTRACT", $CONTRACT_NUM);
							  $UPDATE = M("Erp_project")->where("ID = $prjId")->setField("COMPANY", $DEV_ENT);

                            js_alert('�����ɹ�', U('Flow/workStep'));
                        } else {
                            js_alert('����ʧ��');
                        }
                    }
                    exit;
                }
            } else {

                //����������
                $auth = $workflow->start_authority($flowType);
                if (!$auth) {
                    js_alert('����Ȩ��');
                }
                $form = $workflow->createHtml();

                if ($_REQUEST['savedata']) {
                    $form = $workflow->createHtml();

                    if ($_REQUEST['savedata']) {
                        if ($recordId) {
                            $project_model = D('Project');
                            $pstatus = $project_model->get_Change_Flow_Status($recordId);

                            if ($pstatus == '1') {
                                js_alert('�����ظ��ύŶ', U('House/opinionFlow', $this->_merge_url_param));
                            } else {
                                $_REQUEST['type'] = $_REQUEST['flowType'];
                                $str = $workflow->createworkflow($_REQUEST);
                                if ($str) {
                                    js_alert('�ύ�ɹ�', U('House/opinionFlow', $this->_merge_url_param));
                                } else {
                                    js_alert('�ύʧ��', U('House/opinionFlow', $this->_merge_url_param));
                                }
                            }

                        }
                    }
                }
            }

            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);

        } else {//��������-===============
            $prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['RECORDID'];
            $type = $_REQUEST['type'] ? $_REQUEST['type'] : 'lixiangshenqing';
            if ($_REQUEST['CHANGE'] == '-1') {
                js_alert();
            }

            if (!$type) {
                $this->error('���������Ͳ�����');
            }

            //������ID
            $flowId = !empty($_REQUEST['flowId']) ?
                intval($_REQUEST['flowId']) : 0;

            //����������ҵ��ID
            $recordId = !empty($_REQUEST['RECORDID']) ?
                intval($_REQUEST['RECORDID']) : 0;

            Vendor('Oms.workflow');
            $workflow = new workflow();

            if ($flowId > 0) {
				 
                //�����Ѿ����ڵĹ�����
                $click = $workflow->nextstep($flowId);
                $form = $workflow->createHtml($flowId);

                if ($_REQUEST['savedata']) {
                    //��һ��
                    if ($_REQUEST['flowNext']) {
                        $str = $workflow->handleworkflow($_REQUEST);
                        if ($str) {
                            js_alert('����ɹ�', U('Flow/workStep'));
                        } else {
                            js_alert('����ʧ��');
                        }
                    } //ͨ����ť
                    else if ($_REQUEST['flowPass']) {
                        $str = $workflow->passWorkflow($_REQUEST);

                        if ($str) {
                            /**
                             *  �������Ŀ���ڷ��ҷ��ճ�
                             */
                            $fwfscCase = D('ProjectCase')->where('PROJECT_ID = ' . $recordId . ' AND SCALETYPE = ' . self::FWFSC)->find();
                            if (is_array($fwfscCase) && count($fwfscCase)) {
                                $this->addFwfscIncomeContract($recordId, $fwfscCase['ID']);
                            }

                            $project_model = D('Project');
                            $ress = $project_model->update_pass_status($_REQUEST['RECORDID']);;//���ͨ��

                            /*�������Ŀ���ڷ���ҵ����ͨ����ͬ��Ż�ȡ��ͬϵͳ�к�ͬ��Ϣ��
                            ���洢�ھ���ϵͳ��ͬ����*/
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
                                //��ѯ��Ŀ��ͬ��Ϣ
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
                                    //ȡ���������������ڳ���
                                    $creator_info = $workflow->get_Flow_Creator_Info($flowId);
                                    $info['CITY_ID'] = $creator_info['CITY'];

                                    $contract_model = D('Contract');
                                    $contract_id = $contract_model->add_contract_info($info);

                                    /***ͬ����ͬ��Ʊ�ͻؿ��¼������ϵͳ***/
                                    if ($contract_id > 0) {
                                        //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
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
                                                    //����������ϸ��¼  
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

                                        //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ŀ�Ʊ��¼,��������ͬ��������ϵͳ
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
                                                    // ��Ʊ���ͣ������Ʊ���Ͳ�Ϊ1��2���򽫷�Ʊ��������Ϊ2(�����)
                                                    // ��������Ϊ1�����ѣ���2������ѣ�
                                                    if (!in_array($val['type'], array(1, 2))) {
                                                        $val['type'] = 2;
                                                    }
                                                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                                                }

                                                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);

                                                if ($insert_invoice_id) {
                                                    //����������ϸ��¼           
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

                            js_alert('ͬ��ɹ�', U('Flow/workStep'));
                        } else {
                            js_alert('ͬ��ʧ��');
                        }
                    } //�����ť
                    else if ($_REQUEST['flowNot']) {
                        $str = $workflow->notWorkflow($_REQUEST);
                        if ($str) {
                            $project_model = D('Project');
                            $project_model->update_nopass_status($prjId);;//��� ��ͨ��
                            js_alert('����ɹ�', U('Flow/workStep'));
                        } else {
                            js_alert('���ʧ��');
                        }
                    } //��ֹ��ť
                    else if ($_REQUEST['flowStop']) {
                        $auth = $workflow->flowPassRole($flowId);

                        if (!$auth) {
                            js_alert('δ�����ؾ���ɫ');
                            exit;
                        }

                        $str = $workflow->finishworkflow($_REQUEST);

                        if ($str) {
                            $project_model = D('Project');
                            $ress = $project_model->update_pass_status($_REQUEST['RECORDID']);//���ͨ��

                            /**
                             *  �������Ŀ���ڷ��ҷ��ճ�
                             */
                            $fwfscCase = D('ProjectCase')->where('PROJECT_ID = ' . $recordId . ' AND SCALETYPE = ' . self::FWFSC)->find();
                            if (is_array($fwfscCase) && count($fwfscCase)) {
                                $this->addFwfscIncomeContract($recordId, $fwfscCase['ID']);
                            }

                            /*�������Ŀ���ڷ���ҵ����ͨ����ͬ��Ż�ȡ��ͬϵͳ�к�ͬ��Ϣ��
                            ���洢�ھ���ϵͳ��ͬ����*/
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
                                //��ѯ��Ŀ��ͬ��Ϣ
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

                                    //ȡ���������������ڳ���
                                    $creator_info = $workflow->get_Flow_Creator_Info($flowId);
                                    $info['CITY_ID'] = $creator_info['CITY'];

                                    $contract_model = D('Contract');
                                    $contract_id = $contract_model->add_contract_info($info);

                                    /***ͬ����ͬ��Ʊ�ͻؿ��¼������ϵͳ***/
                                    if ($contract_id > 0) {
                                        //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
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

                                                    //����������ϸ��¼           
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

                                        //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ŀ�Ʊ��¼,��������ͬ��������ϵͳ
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
                                                    // ��Ʊ���ͣ������Ʊ���Ͳ�Ϊ1��2���򽫷�Ʊ��������Ϊ2(�����)
                                                    // ��������Ϊ1�����ѣ���2������ѣ�
                                                    if (!in_array($val['type'], array(1, 2))) {
                                                        $val['type'] = 2;
                                                    }
                                                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                                                }
                                                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);

                                                if ($insert_invoice_id) {
                                                    //����������ϸ��¼ 
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

                            js_alert('�����ɹ�', U('Flow/workStep'));
                        } else {
                            js_alert('����ʧ��');
                        }
                    }
                    exit;
                }
            } else {
                //����������
                $auth = $workflow->start_authority($type);
                if (!$auth) {
                    js_alert("����Ȩ��");
                }
                $model = M();
                // $fees = $model->query("select * from ERP_BUDGETFEE a left join ERP_PRJBUDGET b on a.budgetid=b.id left join Erp_CASE c on b.case_id = c.id where c.project_id=$prjId");
                $passFees = $this->checkFeesPassed($prjId);
                if (!$passFees) {
                    js_alert('������дԤ�����', U('House/projectBudget', $this->_merge_url_param));
                    exit;
                }
                if ($this->needBudgetSale($prjId)) {  // �Ƿ���ҪĿ��ֽ�
                    $budgetsale = M('Erp_budgetsale')->where("PROJECTT_ID=$prjId")->select();
                    if (!$budgetsale) {

                        js_alert('������дĿ��ֽ�', U('House/budgetSale', $this->_merge_url_param));
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
                                //�ύ..����
                                $project_model = D('Project');
                                $project_model->update_check_status($prjId);//�����
                                js_alert('�ύ�ɹ�', U('House/opinionFlow', $this->_merge_url_param));
                                exit;
                            } else {
                                js_alert('�ύʧ��', U('House/opinionFlow', $this->_merge_url_param));
                                exit;
                            }
                        } else {
                            js_alert('�벻Ҫ�ظ��ύ', U('House/opinionFlow', $this->_merge_url_param));
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
        $this->project_case_auth($projId);//��Ŀҵ��Ȩ���ж�
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

        /*//�е������͵�ʱ��ſ�פ��
        $business_type = 1;//��������
        $field_type = 6;// פ������
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
                /*����Ĭ������פ��Ȩ�� --start--*/
                /*if($erpId == 1){
                    foreach(explode(',',$uId) as $key=>$v){
                        $auth = $project->where("erp_id = 6 and pro_id = {$prjId} and use_id={$v}")->find();//����Ƿ���פ��Ȩ��

                        if($auth){//��
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
                js_alert('�ύ�ɹ�', U('House/projectAuth', array('prjid' => $prjId)));

            } else {
                js_alert('�ύʧ��', U('House/projectAuth', array('prjid' => $prjId)));

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
    **פ��Ȩ��
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
                js_alert("�ɹ�", U("House/fieldAuth"));
            } else {
                js_alert("ʧ��", U("House/fieldAuth"));
            }
            exit;
        }

        $this->display('fieldAuth');
    }

    //������Ʒ
    function relateProduct() {
        $model = new Model();
        Vendor('Oms.Changerecord');
        $changer = new Changerecord();
        $changer->fields = array('ISVAILD');

        $projectId = $_REQUEST['prjid'];

        $houseId = $this->getHouseIdByPid($projectId);
        if (!$houseId) {
            js_alert("���ȱ�����Ŀ��Ϣ");
            exit;
        }
        $cid = $_REQUEST['CID'];
        $change = $_REQUEST['CHANGE'];

        if ($_REQUEST['status']) {

            $types = M('Erp_products_type')->select();

            foreach ($types as $key => $val) {
                $product = M('Erp_relatedproducts')->where("CHANGPINID = {$val['ID']} and house_id = {$houseId}")->find();


                if ($_REQUEST['pid']) {//�����ݵ����
                    if ($change == -1) {//���

                        if ($product) {//ԭ�ȱ��д��ڼ�¼

                            $change_log = M("Erp_changelog")->where("BID = {$product['ID']}")->find();//�鿴���

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
                        } else {//δ���ڼ�¼
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

                    } else {//δ������
                        if ($product) {//ԭ���д�������
                            if ($product['ISVAILD']) {
                                if (!in_array($val['ID'], $_REQUEST['pid'])) {
                                    $update = M('Erp_relatedproducts')->where("id={$product['ID']}")->setField("ISVAILD", 0);
                                }
                            } else {
                                if (in_array($val['ID'], $_REQUEST['pid'])) {
                                    $update = M('Erp_relatedproducts')->where("id = {$product['ID']}")->setField('ISVAILD', '-1');
                                }
                            }
                        } else {//ԭ����δ��������
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
                } else {//����Ϊ�յ����
                    if ($product) {
                        if ($_REQUEST['CHANGE'] == -1) {

                            $change_log = M("Erp_changelog")->where("BID = {$product['ID']}")->find();//�鿴���

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
        $html = "<form class='registerform' method='post' action=" . U('House/relateProduct', array('status' => '1', 'prjid' => $projectId, 'CHANGE' => $change, 'CID' => $cid, 'flowId' => $_REQUEST['flowId'], 'tabNum' => $_REQUEST['tabNum'])) . "><div class='contractinfo-table'><table><thead><tr><td>��Ʒ����</td><td>�Ƿ���Ч</td></tr></thead><tbody>";

        foreach ($record as $key => $val) {

            if ($_REQUEST['CHANGE'] == '-1') {

                $optt['TABLE'] = 'ERP_RELATEDPRODUCTS';
                $optt['BID'] = $val['BID'];
                $optt['CID'] = $this->_get('CID');//����汾id
                $changarr = $changer->getRecords($optt);

                $current = $changarr['ISVAILD'] ? $changarr['ISVAILD']['VALUEE'] : $val['ISVAILD'];

                $info = $changarr['ISVAILD'] ? ($changarr['ISVAILD']['ISNEW'] ? "<span class='fred'>[����]</span>" : "<span class='fred'>[ԭ]������</span>") : '';

                $html .= "<tr><td>{$val['CHANPINLEIXING']}</td><td>" . ($current ? "<input type='checkbox' value={$val['ID']} name='pid[]' checked />" : "<input type='checkbox' value={$val['ID']} name='pid[]'  />") . " $info</td></tr>";
            } else {

                $html .= "<tr><td>{$val['CHANPINLEIXING']}</td><td>" . ($val['ISVAILD'] ? "<input $disable type='checkbox' value={$val['ID']} name='pid[]' checked />" : "<input $disable type='checkbox' value={$val['ID']} name='pid[]'  />") . "</td></tr>";
            }
        }

        $html .= "</tbody></table></div>" . ($button ? "<div class='handle-btn'><input type='submit' value='��&nbsp;��' class='btn-blue' /></div></form>" : '</form>');

        $this->assign('form', $html);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['tabNum'], $this->_merge_url_param));
        $this->display('relateProduct');
    }

    //������Ŀid��ȡhouseid
    public function getHouseIdByPid($pid) {
        $id = M('Erp_house')->where("project_id = {$pid}")->getField('id');

        return $id;
    }

    //����ҵ������
    public function add_benefits($data) {
        $benefits = M("Erp_benefits");
        $res = $benefits->add($data);
        //echo $this->model->getLastSql();
        return $res;
    }

    //������ĿId��ȡ��Ŀ��Ϣ
    public function get_prj_info_by_prjid($id, $array) {
        $project = M("Erp_project");
        $info = $project->where("ID=$id")->field($array)->find();
        return $info;
    }

    /**
     * �Ƿ���ҪĿ��ֽ�
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
     * ���ӷ��ҷ��ճ��ͬ
     * @param $projId ��Ŀid
     * @param $caseId
     * @return bool �����Ƿ���ӳɹ�
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
                'msg' => '��Ŀ����Ϊ��'
            );
        }

        $contractNo = $project['CONTRACT'];
        $cityid = $project['CITY_ID'];  // ����Ŀ�б��л�ȡ���б��
        $sql = "select PY from ERP_CITY where ID=" . $cityid;
        $citypy = $this->model->query($sql);
        $citypy = strtolower($citypy[0]["PY"]);//�û�����ƴ��
        //��ȡ��ͬ������Ϣ
        load("@.contract_common");
        $fetchedData = getContractData($citypy, $contractNo);
        if ($fetchedData === false) {
            return array(
                'result' => false,
                'msg' => '��ȡ��ͬ���ݳ���'
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
        $toInsertData['ADD_TIME'] = date("Y-m-d H:i:s");  // ���ʱ��
        $toInsertData['CASE_ID'] = $caseId;  // ���ʱ��
        $toInsertData['CITY_PY'] = $citypy;
        $toInsertData['CITY_ID'] = $cityid;
        unset($fetchedData);

        // ִ������
        $this->model->startTrans();
        $insertedId = D("Contract")->add_contract_info($toInsertData);
        if ($insertedId !== false) {
            //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
            $insert_refund_id = $this->save_refund_data($contractNo, $insertedId, $citypy);
            //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ��Ʊ��¼����������ͬ��������ϵͳ
            $insert_invoice_id = $this->save_invoice_data($contractNo, $insertedId, $citypy);
            if ($insert_invoice_id !== false && $insert_refund_id !== false) {
                $this->model->commit();
                return array(
                    'result' => true,
                    'msg' => '��ͬ��ӳɹ�'
                );
            } else {
                $this->model->rollback();
                $error = '';
                if ($insert_refund_id == false) {
                    $error .= '��ȡ��ͬ�Ļؿ��¼����';
                }

                if ($insert_invoice_id == false) {
                    $error = empty($error) ? '��ȡ��ͬ�Ŀ�Ʊ��¼����' :
                        $error . '�� ��ȡ��ͬ�Ŀ�Ʊ��¼����';
                }

                // ���ؽ��
                return array(
                    'result' => false,
                    'msg' => $error
                );
            }
        } else {
            return array(
                'result' => false,
                'msg' => '��Ӻ�ͬ����'
            );
        }
    }

    /**
     * +----------------------------------------------------------
     *���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
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
        //����ͬ�ؿ��¼���뵽����ϵͳ�����ݿ���
        if (!empty($refundRecords)) {
            $contract_model = D("Contract");
            $payment_model = D("PaymentRecord");

            $conf_where = "ID = '" . $contract_id . "'";
            $field_arr = array("CASE_ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            // ��ȡ��Ŀ������
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
                    //����������ϸ��¼
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
     *���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ŀ�Ʊ��¼,��������ͬ��������ϵͳ
     * +----------------------------------------------------------
     * @param  $contractnum ��ͬ��
     * @param  $contract_id ��ͬid
    +----------------------------------------------------------
     * @param $citypy ���ڳ���ƴ��
    +----------------------------------------------------------
     * @return bool
     */
    public function save_invoice_data($contractnum, $contract_id, $citypy = "nj") {
        load("@.contract_common");
        $invoiceRecords = get_invoice_data_by_no($citypy, $contractnum);
        if (empty($invoiceRecords) || (is_array($invoiceRecords) && count($invoiceRecords) == 0)) {
            return true;
        }
        //����ͬ��Ʊ��¼���뵽����ϵͳ�����ݿ���
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
                    // ��Ʊ���ͣ������Ʊ���Ͳ�Ϊ1��2���򽫷�Ʊ��������Ϊ�����
                    // ��������Ϊ1�����ѣ���2������ѣ�
                    if (!in_array($val['type'], array(1, 2))) {
                        $val['type'] = 2;
                    }
                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                }
                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);
                if (!$insert_invoice_id) {
                    return false;
                } else {
                    //����������ϸ��¼
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
     * ����Ƿ���д��Ԥ�����
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

    //�ж��Ƿ���ͬһֵ
    public function isRepeatValue($where){
        $sql = "select Amount from erp_feescale".$where;
        $amount_arr = D()->query($sql);
        foreach ($amount_arr as $amounts) {
            if ($_POST['AMOUNT'] == $amounts['AMOUNT']  ) {
                $result['status'] = 0;
                $result['msg'] = g2u('ͬһ��׼ֵ������ͬ');
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