<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Feescale_changeAction extends ExtendAction{
    private $model;
    private $tab_num;
   /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();
    
    public function __construct() 
    {
      
        parent::__construct();
        $this->model = new Model();
        $this->tab_num = 18;
        //TAB URL����
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : 
                                    $this->_merge_url_param['RECORDID'] = $_GET['parentchooseid']; 
        !empty($_GET['parentchooseid']) ? $this->_merge_url_param['parentchooseid'] = $_GET['parentchooseid'] : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : '';
        $this->_merge_url_param['TAB_NUMBER'] = $this->tab_num;
    }

    //��׼��������
    public function feescale_change_list() {
        $prjid = $_REQUEST["prjid"];
        $this->project_case_auth($prjid);//��Ŀҵ��Ȩ���ж�

        $feescale_change_model = D("FeescaleChange");
        $feescale_model = D("Feescale");
        $project_model = D("Project");
        $case_model = D("ProjectCase");


        $showForm = $_REQUEST["showForm"] ? $_REQUEST["showForm"] : "";
        $faction = $_REQUEST["faction"] ? $_REQUEST["faction"] : "";
        $id = $_REQUEST["ID"] ? $_REQUEST["ID"] : 0;
        $scaletype = $_REQUEST["scaletype"];

        //������״̬����
        $feescale_change_status = $feescale_change_model->get_conf_requisition_status();
        //������ϸ״̬����
        $feescale_status = $feescale_model->get_conf_feescale_status();

        $project_info = $project_model->get_info_by_id($prjid, array("PROJECTNAME"));

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(144);

        if ($scaletype)//����ҳ���Զ���䰸����� ������ĿID��ҵ�����ͻ�ȡ�������
        {
            $sql = "select ID from ERP_CASE where PROJECT_ID=$prjid and SCALETYPE = $scaletype";
            $caseid = $this->model->query($sql);
            $caseid = $caseid[0]["ID"];
            $form->setMyFieldVal("CASE_ID", $caseid, false);
            echo $caseid;
            die;
        }

        if (($showForm == 3 || $showForm == 1) && $faction == "")//������׼������¼����׼������
        {
            // ��ȡҵ������ѡ���б�
            $listchar = $this->getScaleTypeOptions($prjid);
            $adate = date("Y-m-d H:i:s", time());
            $auser = $_SESSION["uinfo"]["tname"];

            //var_dump($listchar);die;
        } else if ($showForm == 3 && $faction == "saveFormData")//��������
        {
            if ($this->isShowOptionBtn($_POST['CASE_ID']) == self::HIDE_OPTION_BTN) {
                $scaleTypeNames = D('ProjectCase')->get_conf_case_type_remark();
                $aName = $scaleTypeNames[$_POST['SCALETYPE']];
                $result = array(
                    'status' => 0,
                    'msg' => $aName . '��Ŀ������ֹ�����׶Σ����ܽ��б�׼����'
                );
            } else {
                $data["CASE_ID"] = $_POST["CASE_ID"];

                $data["TYPE"] = $_POST["TYPE"];
                $data["ADATE"] = $_POST["ADATE"];
                $data["AUSER"] = u2g(trim($_POST["AUSER"]));
                $data["STATUS"] = 1;//״̬Ϊ 1δ����Ĭ�� 2����� 3�����
                $res = $feescale_change_model->add_standard_adjustment($data);
                if ($res > 0) {
                    $result['status'] = 1;
                    $result['msg'] = '��ӳɹ�';
                } else {
                    $result['status'] = 0;
                    $result['msg'] = '���ʧ��';
                }
            }

            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        } else if ($showForm == 1 && $faction == "saveFormData" && $id) {
            $data["CASE_ID"] = $_POST["CASE_ID"];
            $data["TYPE"] = $_POST["TYPE"];
            //$data["ADATE"] = $_POST["ADATE"];
            $data["AUSER"] = u2g(trim($_POST["AUSER"]));
            $res = $feescale_change_model->update_standardadjust_by_id($id, $data);
            if ($res > 0) {
                $result['status'] = 1;
                $result['msg'] = '�޸ĳɹ�';
            } else {
                $result['status'] = 0;
                $result['msg'] = '�޸�ʧ��';
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        } elseif ($faction == "delData" && $id > 0) {
            $this->model->startTrans();
            $feescale_info = $feescale_model->get_info_by_ch_id($id, array("ID"));
            if ($feescale_info) {
                $cond_where = "CH_ID = $id";
                $del_detail_num = $feescale_model->del_info_by_cond($cond_where);
                if ($del_detail_num) {
                    $del_list_num = $feescale_change_model->del_feescale_change_by_id($id);
                    if ($del_list_num) {
                        $this->model->commit();
                        $result["status"] = "success";
                        $result["msg"] = "ɾ���ɹ�!";
                    } else {
                        $this->model->rollback();
                        $result["status"] = "error";
                        $result["msg"] = "ɾ��ʧ��!";
                    }

                } else {
                    $this->model->rollback();
                    $result["state"] = "error";
                    $result["msg"] = "ɾ��ʧ��!";
                }
            } else {
                $del_list_num = $feescale_change_model->del_feescale_change_by_id($id);
                if ($del_list_num) {
                    $this->model->commit();
                    $result["status"] = "success";
                    $result["msg"] = "ɾ���ɹ�!";
                } else {
                    $this->model->rollback();
                    $result["status"] = "error";
                    $result["msg"] = "ɾ��ʧ��!";
                }
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }

        if ($prjid) {
            $case_id = $case_model->get_info_by_pid($prjid, "", array("ID"));
            foreach ($case_id as $key => $val) {
                $caseid_arr[] = $val["ID"];
            }
            $caseid_str = implode(",", $caseid_arr);
            $where = "CASE_ID in(" . $caseid_str . ")";
        } else {
            $where = "CASE_ID = " . $this->_merge_url_param['CASEID'] . " and ID = " . $this->_merge_url_param['RECORDID'];
        }


        $feescale_change_status_arr = $feescale_change_model->get_conf_requisition_status_remark();

        $children = array(array('��׼��ϸ', U('/Feescale_change/show_feescale_list', $this->_merge_url_param)));


        $form->setChildren($children)
            ->setMyField("SCALETYPE", "LISTCHAR", $listchar)
            //->setMyFieldVal("CASE_ID", $case_id, true)
            ->setMyFieldVal("ADATE", $adate, true)
            ->setMyfieldVal("AUSER", $auser, true)
            ->setMyFieldVal("PROJECTNAME", $project_info[0]["PROJECTNAME"], TRUE)
            ->setMyfield("STATUS", "LISTCHAR", array2listchar($feescale_change_status_arr))
            ->where($where);
        if ($_REQUEST["flowId"]) {

            $is_edit = judgeFlowEdit(intval($_REQUEST["flowId"]), $_SESSION["uinfo"]["uid"]);
            if (!$is_edit) {
                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
            } else {
                $form->EDITABLE = -1;
                $form->ADDABLE = -1;
                $form->DELABLE = 0;
            }
            $form->GABTN = '<a onclick="showFlow();" href="javascript:;" id="show_steps" class="btn btn-info btn-sm">�鿴����ͼ</a>';
            $form->DELCONDITION = '%STATUS% == ' . $feescale_change_status['not_sub'];
        } else {
            $form->ADDABLE = -1;
            $form->EDITCONDITION = '%STATUS% == ' . $feescale_change_status['not_sub'];
            $form->DELCONDITION = '%STATUS% == ' . $feescale_change_status['not_sub'];
        }
        $formHtml = $form->getResult();
        $this->assign("form", $formHtml);
        $this->assign("recordid", $_REQUEST["parentchooseid"]);
        $this->assign("prjid", $prjid);
        // $this->assign('isShowOptionBtn', $this->isProjectContextMenuRunnable($prjid, 'feeScaleChange'));
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display("feescale_change_list");
    } 
    
    //��ʾ��׼��ϸ
    public function show_feescale_list()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(145);

        $fid= $_REQUEST["parentchooseid"]; 
        $showForm = $_REQUEST["showForm"] ? $_REQUEST["showForm"] : "";
        $faction = $_REQUEST["faction"] ? $_REQUEST["faction"] : ""; 
        $id = $_REQUEST["ID"] ? $_REQUEST["ID"] : 0;
        
        $feescale_change_model = D("FeescaleChange");
        $feescale_model = D("Feescale");
        
        //��׼������״̬����
        $feescale_change_status = $feescale_change_model->get_conf_requisition_status(); 
        
        //��׼��ϸ״̬��־����
        $feescale_status_remark = $feescale_model->get_conf_feescale_status_remark();
        
        //��׼��ϸ״̬����
        $feescale_status = $feescale_model->get_conf_feescale_status();
        
        $feescale_change_info = $feescale_change_model->get_info_by_ids($fid,array("CASE_ID","STATUS","TYPE"));
        //var_dump($feescale_change_info);
        $case_id = $feescale_change_info["CASE_ID"];
        $status = $feescale_change_info["STATUS"];  
        $type = $feescale_change_info["TYPE"];
        if($showForm == 3 && $_REQUEST["faction"] == "")
        {
             if($status != $feescale_change_status["not_sub"])
            {
                js_alert("������δ�ύ�ı�׼���룬����������׼����",
                    U("Feescale_change/show_feescale_list",$this->_merge_url_param),1);
                exit();
            }
           $form ->setMyFieldVal("PAYDATE", date("Y-m-d H:i:s"),true)
               ->setMyFieldVal("STATUS",$feescale_status["not_sub"],TRUE);
        }
      
        //����Ƿ��������ĵ����շѱ�׼�����н�Ӷ���׼����ת��FORM����ʽ
        $case_info = D("ProjectCase")->get_info_by_cond("ID = '".$case_id."'",array("SCALETYPE"));
        $scale_type = $case_info[0]["SCALETYPE"];
        if(($type == 1||$type == 2 )&& $scale_type == 2)
        {
            $form->setMyField("EXECSTIME", 'FORMVISIBLE',-1,false);
            $form->setMyField("EXECSTIME", 'GRIDVISIBLE',-1,false);
            $form->setMyField("EXECETIME", 'FORMVISIBLE',-1,false);
            $form->setMyField("EXECETIME", 'GRIDVISIBLE',-1,false);
            $form->setMyField("MTYPE", 'FORMVISIBLE',-1,false);
            $form->setMyField("MTYPE", 'GRIDVISIBLE',-1,false);
            $form->setMyField("STYPE", 'FORMVISIBLE',-1,false);
            $form->setMyField("STYPE", 'GRIDVISIBLE',-1,false);
            $form->setMyField('AMOUNT','FIELDMEANS','ֵ',false); // ��������Ϊ��ֵ��
                //->setMyField('PERCENTAGE','FORMVISIBLE',-1,false)
               // ->setMyField('PERCENTAGE','GRIDVISIBLE',-1,false)
               // ->setMyField('AMOUNT','GRIDVISIBLE',"0",false);
                //->setMyField("AMOUNT", "FORMVISIBLE", "0",false);
        }
        //�����Ľ������аٷֱ�
        if(($type ==3 ||$type ==4 ||$type ==5 ||$type ==6 )&& $scale_type ==2){
            $form->setMyField("STYPE", 'FORMVISIBLE',-1,false);
            $form->setMyField("STYPE", 'GRIDVISIBLE',-1,false);
            $form->setMyField('AMOUNT','FIELDMEANS','ֵ',false); // ��������Ϊ��ֵ��
        }
        if( $faction == "saveFormData"){
            $execStime = strtotime($_POST['EXECSTIME']);
            $execEtime = strtotime($_POST['EXECETIME']);
            if ($execStime > $execEtime) {
                $result['status'] = 0;
                $result['msg'] = g2u('ִ�п�ʼʱ��ӦС��ִ�н���ʱ��');

                echo json_encode($result);
                exit;
            }
            //��׼����ֵ������ͬ
            $condition = ' WHERE 1=1 ';
            $condition .=" AND SCALETYPE =".$type;
            if(isset($_POST['MTYPE']) && $_POST['MTYPE'] !== ""){
                $condition .= " AND MTYPE =".$_POST['MTYPE'];
            }
            if(isset($_POST['STYPE']) && $_POST['STYPE'] !== ""){
                $condition .= " AND STYPE=".$_POST['STYPE'];
            }
            if($_REQUEST['ID']){
                $condition .= " AND ID !=".$_REQUEST['ID'];
            }
            $where = $condition. " AND ISVALID = -1 AND CASE_ID = {$case_id}";
            $result = $this->isRepeatValue($where);
            if($result['status'] == 0){
                echo json_encode($result);
                exit;
            }
            //�жϲ�����֮������жϱ������
            $where = $condition." AND ISVALID = 0 AND STATUS IS NOT NULL AND STATUS != 4 AND CASE_ID = {$case_id}";
            $result = $this->isRepeatValue($where);
            if($result['status'] == 0){
                echo json_encode($result);
                exit;
            }

        }
        if($showForm == 3 && $faction == "saveFormData" && $id == 0)//����
        {            
                $prjbudget_id = M("Erp_prjbudget")->field("ID")->where("CASE_ID=".$case_id)->select();
                $data["PRJ_ID"] = $prjbudget_id[0]["ID"];
                $data["SCALE"] = strip_tags($_POST["SCALE"]) ? u2g((strip_tags($_POST["SCALE"]))) : "";
                $data["SCALETYPE"] = $type;
                $data["REMARK"] = strip_tags($_POST["REMARK"]) ? strip_tags($_POST["REMARK"]) : "";
                $data["CH_ID"] = strip_tags($_POST["CH_ID"]) ? strip_tags($_POST["CH_ID"]) : "";
                $data["PAYDATE"] = date("Y-m-d H:m:s");
                $data["CASE_ID"] = $case_id ? $case_id : 0;
                $data["REASON"] = strip_tags($_POST["REASON"]) ? u2g(strip_tags($_POST["REASON"])) : "";
                $data["STATUS"] = 1;//״̬Ĭ��Ϊ 1δȷ��
                $data["ISVALID"] = 0;               
                $data["STYPE"] = (isset($_POST["STYPE"])) ? intval($_POST["STYPE"]) : "";
                $data["MTYPE"] = (isset($_POST["MTYPE"])) ? intval($_POST["MTYPE"]) : "";
                $data["AMOUNT"] = ($_POST["AMOUNT"] !== "") ? floatval($_POST["AMOUNT"]) : "";
                $data['EXECSTIME'] = $_POST['EXECSTIME'];
                $data['EXECETIME'] = $_POST['EXECETIME'];
                //$data["PERCENTAGE"] = (isset($_POST["PERCENTAGE"])) ? floatval($_POST["PERCENTAGE"]) : "";
                //var_dump($data);DIE;
                $insertid = $feescale_model->add_feescale_info($data);
                if (strtotime($data['EXECSTIME']) > strtotime($data['EXECETIME'])) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('ִ�п�ʼʱ��ӦС��ִ�н���ʱ��');

                    echo json_encode($result);
                    exit;
                }
                 if($insertid)
                 {
                     $result["status"] = 2;
                     $result["msg"] = "�����ɹ�!";
                 }
                 else
                 {
                     $result["status"] = 0;
                     $result["msg"] = "����ʧ��!";
                 }
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
        }
        else if( $faction == "delData" && $id != 0)//ɾ��
        {
            $del_num = $feescale_model->del_info_by_id($id);
            if($del_num)
            {
                $result["status"] = 'success';
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
        
        $form->where("CH_ID = $fid")
            ->setMyFieldVal("CASE_ID", $case_id, true)
            ->setMyFieldVal("CH_ID", $fid,true)
            ->setMyField("STATUS", "LISTCHAR",  array2listchar($feescale_status_remark));
        
        if($_REQUEST["flowId"])
        {
            $is_edit = judgeFlowEdit(intval($_REQUEST["flowId"]),$_SESSION["uinfo"]["uid"]);
            if(!$is_edit)
            {
                $form->EDITABLE = 0;    
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
            }
            else
            {
               $form->EDITABLE = -1; 
               $form->ADDABLE = -1;
               $form->DELABLE = -1;
            }
            
        }
        else
        {
            $form->EDITCONDITION = '%STATUS% == '.$feescale_status['not_sub'];
            $form->DELCONDITION = '%STATUS% == '.$feescale_status['not_sub'];
        }
        
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
        $this->assign("form",$formHtml);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->display("feescale_list");
    }

    
    //�ύ��׼������������
    public function opinionFlow(){
        
        $feescale_change_model = D("FeescaleChange");
        $feescale_model = D("Feescale");
        $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//��ĿID
        $uid = intval($_SESSION['uinfo']['uid']);
        $recordId = isset($_GET['RECORDID']) ? intval($_GET['RECORDID']) : 0;
        //var_dump($RECORDID);        
        Vendor('Oms.workflow');			
        $workflow = new workflow();
        $type = "biaozhuntiaozheng";//��׼�޸��������� 11
       
        $flowId = $_REQUEST['flowId'];
        if($flowId){
            $click = $workflow->nextstep($flowId);

            $form=$workflow->createHtml($flowId);

            if($_REQUEST['savedata']){
                if($_REQUEST['flowNext']){
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str){
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('����ʧ��');
                    }
                }elseif($_REQUEST['flowPass']){

                    $str = $workflow->passWorkflow($_REQUEST);
                    if($str){
                        js_alert('ͬ��ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('ͬ��ʧ��');
                    }
                }elseif($_REQUEST['flowNot']){

                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str){
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('���ʧ��');
                    }

                }elseif($_REQUEST['flowStop']){

                    $auth = $workflow->flowPassRole($flowId);
                    if(!$auth){
                        js_alert('δ�����ؾ���ɫ');exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str){
                        js_alert('�����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('����ʧ��');
                    }
                }
				exit;
            }
        }else{
           $flow_type_pinyin = "biaozhuntiaozheng";
           $auth = $workflow->start_authority($flow_type_pinyin);
           if(!$auth)
           {
               js_alert('����Ȩ��');
           }
           $form = $workflow->createHtml();

            if($_REQUEST['savedata'])
            {   
                $flow_data['type'] = $flow_type_pinyin; 
                $flow_data['CASEID'] = $_REQUEST["CASEID"];
                $flow_data['RECORDID'] = $recordId;               
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES']; 
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']); 
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']); 
                //var_dump($flow_data);die;
                $str = $workflow->createworkflow($flow_data);
               // var_dump($str);die;
                if($str)//�ύ�ɹ� �޸�����״̬Ϊ2���������
                {   
                    js_alert('�ύ�ɹ�',U('Feescale_change/feescale_change_list',$this->_merge_url_param));
                }
                else
                {
                    //$this->model->rollback();
                    js_alert('�ύʧ��',U('Feescale_change/opinionFlow',$this->_merge_url_param));
                }
            }                  
        }    
        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('opinionFlow');
    }
    
    //�鿴����ͼ
    public function show_flow_step()
    {
        $feescale_change_model = D("FeescaleChange");
        $feescale_change_id = isset($_POST['standard_id']) ? intval($_POST['standard_id']) : 0;       
        $info = $feescale_change_model->get_info_by_ids($feescale_change_id,array("CASE_ID"));
        $case_id = $info["CASE_ID"]; 
        $type = 11;        
        if($_REQUEST["flowid"])
        {            
            Vendor('Oms.workflow');			
            $workflow = new workflow();
            $flow = $workflow->chartworkflow($_REQUEST["flowid"]);
            $this->assign("flow",$flow);
            $this->display("show_workflow");    
        }
        else
        {
            $sql = "select d.id from(select b.*,a.flowtype from erp_flowset a 
                    left join erp_flows b on a.id= b.flowsetid) d
                    where CASEID = $case_id AND FLOWTYPE = $type and RECORDID = ".$feescale_change_id;
            //echo $sql;
            $res = $this->model->query($sql);
            $flowid = $res[0]["ID"];
            if(!$flowid)
            {
                 $result["state"] = 0;
                 $result["msg"] = "�Բ���δ�ҵ����������Ϣ";
            }
            else
            {
                $result["state"] = 1;
                $result["msg"] = $flowid;
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
    }
    
    //�ж����������շѱ�׼
    public function is_allow_add_feescale(){
        $standardid = $_REQUEST["standardid"];
       // var_dump($standardid);
        if(!$standardid){//δѡ���κ�һ���������룬����������
            $msg = "����û��ָ����׼�������룬����ѡ���������";
            $msg = g2u($msg);
            echo $msg;
            exit;
        }else{
            $feescale_change_model = D("FeescaleChange");
            $info = $feescale_change_model->get_info_by_ids($standardid,array("STATUS"));
            $status = $info["STATUS"];
            //var_dump($status);
            if($status != 1){
                $msg = "�����Ѿ��ύ���Ѿ����ͨ����������������׼����������ӵ�������";
                $msg = g2u($msg);
                echo $msg;
                exit;
            }
        }
    }
    
    //�ж��ܷ��ύ����
    public function is_allow_sub_to_flow()
    {
        $standard_id = intval($_POST["standard_id"]) ? intval($_POST["standard_id"]) : 0;
        $scale_type = intval($_POST["scale_type"]) ? intval($_POST["scale_type"]) : 0;
        
        //�ж�ҵ���Ƿ��Ѿ���
        $is_summery = is_scale_have_summery($prjid,$scale_type);
        if($is_summery)
        {
            $result["state"] = 0;
            $result["msg"] = "��ҵ���Ѿ�����������ھ�������У����������׼������";
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        $feescale_model = D("Feescale");
        $feescale_info = $feescale_model->get_info_by_ch_id($standard_id,array("ID"));
        if($feescale_info)
        {
            $result["state"] = 1;
            $result["msg"] = "";
        }
        else
        {
            $result["state"] = 0;
            $result["msg"] = "�õ�������δ����κα�׼��ϸ�������ύ����,����ӱ�׼��ϸ�����ύ��������";
        }
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }


    /**
     * ��ȡҵ������ѡ��
     * @param $projectID
     * @return bool|string
     */
    private function getScaleTypeOptions($projectID) {
        if (empty($projectID)) {
            return false;
        }

        $sql = "select BSTATUS,MSTATUS, SCSTATUS from ERP_PROJECT WHERE ID=".$projectID;
        $res = $this->model->query($sql);
        $listchar = '';
        // �е���
        if($res[0]["BSTATUS"] !== null) {
            $listchar .= '����^1^';
        }

        // �з���
        if ($res[0]["MSTATUS"] !== null) {
            $listchar .= '����^2^';
        }

        // �з��ҷ��ճ�
        if ($res[0]['SCSTATUS'] !== null) {
            $listchar .= '���ҷ��ճ�^8^';
        }

        return $listchar;

    }

    //�жϱ�׼����ֵ�Ƿ��ظ�
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


