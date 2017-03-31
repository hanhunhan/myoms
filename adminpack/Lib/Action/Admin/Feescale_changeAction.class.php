<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Feescale_changeAction extends ExtendAction{
    private $model;
    private $tab_num;
   /*合并当前模块的URL参数*/
    private $_merge_url_param = array();
    
    public function __construct() 
    {
      
        parent::__construct();
        $this->model = new Model();
        $this->tab_num = 18;
        //TAB URL参数
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

    //标准调整申请
    public function feescale_change_list() {
        $prjid = $_REQUEST["prjid"];
        $this->project_case_auth($prjid);//项目业务权限判断

        $feescale_change_model = D("FeescaleChange");
        $feescale_model = D("Feescale");
        $project_model = D("Project");
        $case_model = D("ProjectCase");


        $showForm = $_REQUEST["showForm"] ? $_REQUEST["showForm"] : "";
        $faction = $_REQUEST["faction"] ? $_REQUEST["faction"] : "";
        $id = $_REQUEST["ID"] ? $_REQUEST["ID"] : 0;
        $scaletype = $_REQUEST["scaletype"];

        //调整单状态数组
        $feescale_change_status = $feescale_change_model->get_conf_requisition_status();
        //调整明细状态数组
        $feescale_status = $feescale_model->get_conf_feescale_status();

        $project_info = $project_model->get_info_by_id($prjid, array("PROJECTNAME"));

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(144);

        if ($scaletype)//新增页面自动填充案件编号 根据项目ID和业务类型获取案件编号
        {
            $sql = "select ID from ERP_CASE where PROJECT_ID=$prjid and SCALETYPE = $scaletype";
            $caseid = $this->model->query($sql);
            $caseid = $caseid[0]["ID"];
            $form->setMyFieldVal("CASE_ID", $caseid, false);
            echo $caseid;
            die;
        }

        if (($showForm == 3 || $showForm == 1) && $faction == "")//新增标准调整记录（标准调整表）
        {
            // 获取业务类型选项列表
            $listchar = $this->getScaleTypeOptions($prjid);
            $adate = date("Y-m-d H:i:s", time());
            $auser = $_SESSION["uinfo"]["tname"];

            //var_dump($listchar);die;
        } else if ($showForm == 3 && $faction == "saveFormData")//保存数据
        {
            if ($this->isShowOptionBtn($_POST['CASE_ID']) == self::HIDE_OPTION_BTN) {
                $scaleTypeNames = D('ProjectCase')->get_conf_case_type_remark();
                $aName = $scaleTypeNames[$_POST['SCALETYPE']];
                $result = array(
                    'status' => 0,
                    'msg' => $aName . '项目处于终止或结算阶段，不能进行标准调整'
                );
            } else {
                $data["CASE_ID"] = $_POST["CASE_ID"];

                $data["TYPE"] = $_POST["TYPE"];
                $data["ADATE"] = $_POST["ADATE"];
                $data["AUSER"] = u2g(trim($_POST["AUSER"]));
                $data["STATUS"] = 1;//状态为 1未申请默认 2审核中 3已审核
                $res = $feescale_change_model->add_standard_adjustment($data);
                if ($res > 0) {
                    $result['status'] = 1;
                    $result['msg'] = '添加成功';
                } else {
                    $result['status'] = 0;
                    $result['msg'] = '添加失败';
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
                $result['msg'] = '修改成功';
            } else {
                $result['status'] = 0;
                $result['msg'] = '修改失败';
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
                        $result["msg"] = "删除成功!";
                    } else {
                        $this->model->rollback();
                        $result["status"] = "error";
                        $result["msg"] = "删除失败!";
                    }

                } else {
                    $this->model->rollback();
                    $result["state"] = "error";
                    $result["msg"] = "删除失败!";
                }
            } else {
                $del_list_num = $feescale_change_model->del_feescale_change_by_id($id);
                if ($del_list_num) {
                    $this->model->commit();
                    $result["status"] = "success";
                    $result["msg"] = "删除成功!";
                } else {
                    $this->model->rollback();
                    $result["status"] = "error";
                    $result["msg"] = "删除失败!";
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

        $children = array(array('标准明细', U('/Feescale_change/show_feescale_list', $this->_merge_url_param)));


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
            $form->GABTN = '<a onclick="showFlow();" href="javascript:;" id="show_steps" class="btn btn-info btn-sm">查看流程图</a>';
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
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display("feescale_change_list");
    } 
    
    //显示标准明细
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
        
        //标准调整单状态数组
        $feescale_change_status = $feescale_change_model->get_conf_requisition_status(); 
        
        //标准明细状态标志数组
        $feescale_status_remark = $feescale_model->get_conf_feescale_status_remark();
        
        //标准明细状态数组
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
                js_alert("不存在未提交的标准申请，请先新增标准申请",
                    U("Feescale_change/show_feescale_list",$this->_merge_url_param),1);
                exit();
            }
           $form ->setMyFieldVal("PAYDATE", date("Y-m-d H:i:s"),true)
               ->setMyFieldVal("STATUS",$feescale_status["not_sub"],TRUE);
        }
      
        //如果是分销案例的单套收费标准或者中介佣金标准，则转换FORM表单格式
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
            $form->setMyField('AMOUNT','FIELDMEANS','值',false); // 将“金额”改为“值”
                //->setMyField('PERCENTAGE','FORMVISIBLE',-1,false)
               // ->setMyField('PERCENTAGE','GRIDVISIBLE',-1,false)
               // ->setMyField('AMOUNT','GRIDVISIBLE',"0",false);
                //->setMyField("AMOUNT", "FORMVISIBLE", "0",false);
        }
        //分销的奖励都有百分比
        if(($type ==3 ||$type ==4 ||$type ==5 ||$type ==6 )&& $scale_type ==2){
            $form->setMyField("STYPE", 'FORMVISIBLE',-1,false);
            $form->setMyField("STYPE", 'GRIDVISIBLE',-1,false);
            $form->setMyField('AMOUNT','FIELDMEANS','值',false); // 将“金额”改为“值”
        }
        if( $faction == "saveFormData"){
            $execStime = strtotime($_POST['EXECSTIME']);
            $execEtime = strtotime($_POST['EXECETIME']);
            if ($execStime > $execEtime) {
                $result['status'] = 0;
                $result['msg'] = g2u('执行开始时间应小于执行结束时间');

                echo json_encode($result);
                exit;
            }
            //标准调整值不能相同
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
            //判断不存在之后继续判断变更数据
            $where = $condition." AND ISVALID = 0 AND STATUS IS NOT NULL AND STATUS != 4 AND CASE_ID = {$case_id}";
            $result = $this->isRepeatValue($where);
            if($result['status'] == 0){
                echo json_encode($result);
                exit;
            }

        }
        if($showForm == 3 && $faction == "saveFormData" && $id == 0)//新增
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
                $data["STATUS"] = 1;//状态默认为 1未确认
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
                    $result['msg'] = g2u('执行开始时间应小于执行结束时间');

                    echo json_encode($result);
                    exit;
                }
                 if($insertid)
                 {
                     $result["status"] = 2;
                     $result["msg"] = "新增成功!";
                 }
                 else
                 {
                     $result["status"] = 0;
                     $result["msg"] = "新增失败!";
                 }
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
        }
        else if( $faction == "delData" && $id != 0)//删除
        {
            $del_num = $feescale_model->del_info_by_id($id);
            if($del_num)
            {
                $result["status"] = 'success';
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
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign("form",$formHtml);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->display("feescale_list");
    }

    
    //提交标准调整申请流程
    public function opinionFlow(){
        
        $feescale_change_model = D("FeescaleChange");
        $feescale_model = D("Feescale");
        $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//项目ID
        $uid = intval($_SESSION['uinfo']['uid']);
        $recordId = isset($_GET['RECORDID']) ? intval($_GET['RECORDID']) : 0;
        //var_dump($RECORDID);        
        Vendor('Oms.workflow');			
        $workflow = new workflow();
        $type = "biaozhuntiaozheng";//标准修改流程类型 11
       
        $flowId = $_REQUEST['flowId'];
        if($flowId){
            $click = $workflow->nextstep($flowId);

            $form=$workflow->createHtml($flowId);

            if($_REQUEST['savedata']){
                if($_REQUEST['flowNext']){
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str){
                        js_alert('办理成功',U('Flow/workStep'));
                    }else{
                        js_alert('办理失败');
                    }
                }elseif($_REQUEST['flowPass']){

                    $str = $workflow->passWorkflow($_REQUEST);
                    if($str){
                        js_alert('同意成功',U('Flow/workStep'));
                    }else{
                        js_alert('同意失败');
                    }
                }elseif($_REQUEST['flowNot']){

                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str){
                        js_alert('否决成功',U('Flow/workStep'));
                    }else{
                        js_alert('否决失败');
                    }

                }elseif($_REQUEST['flowStop']){

                    $auth = $workflow->flowPassRole($flowId);
                    if(!$auth){
                        js_alert('未经过必经角色');exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str){
                        js_alert('备案成功',U('Flow/workStep'));
                    }else{
                        js_alert('备案失败');
                    }
                }
				exit;
            }
        }else{
           $flow_type_pinyin = "biaozhuntiaozheng";
           $auth = $workflow->start_authority($flow_type_pinyin);
           if(!$auth)
           {
               js_alert('暂无权限');
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
                if($str)//提交成功 修改流程状态为2正在审核中
                {   
                    js_alert('提交成功',U('Feescale_change/feescale_change_list',$this->_merge_url_param));
                }
                else
                {
                    //$this->model->rollback();
                    js_alert('提交失败',U('Feescale_change/opinionFlow',$this->_merge_url_param));
                }
            }                  
        }    
        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('opinionFlow');
    }
    
    //查看流程图
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
                 $result["msg"] = "对不起，未找到相关流程信息";
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
    
    //判断允许新增收费标准
    public function is_allow_add_feescale(){
        $standardid = $_REQUEST["standardid"];
       // var_dump($standardid);
        if(!$standardid){//未选择任何一条调整申请，不允许新增
            $msg = "您还没有指定标准调整申请，请先选择调整申请";
            $msg = g2u($msg);
            echo $msg;
            exit;
        }else{
            $feescale_change_model = D("FeescaleChange");
            $info = $feescale_change_model->get_info_by_ids($standardid,array("STATUS"));
            $status = $info["STATUS"];
            //var_dump($status);
            if($status != 1){
                $msg = "流程已经提交或已经审核通过，不能再新增标准，请重新添加调整申请";
                $msg = g2u($msg);
                echo $msg;
                exit;
            }
        }
    }
    
    //判断能否提交流程
    public function is_allow_sub_to_flow()
    {
        $standard_id = intval($_POST["standard_id"]) ? intval($_POST["standard_id"]) : 0;
        $scale_type = intval($_POST["scale_type"]) ? intval($_POST["scale_type"]) : 0;
        
        //判断业务是否已经决
        $is_summery = is_scale_have_summery($prjid,$scale_type);
        if($is_summery)
        {
            $result["state"] = 0;
            $result["msg"] = "该业务已经被决算或正在决算审核中，不能申请标准调整！";
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
            $result["msg"] = "该调整单尚未添加任何标准明细，不能提交流程,请添加标准明细后再提交申请流程";
        }
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }


    /**
     * 获取业务类型选项
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
        // 有电商
        if($res[0]["BSTATUS"] !== null) {
            $listchar .= '电商^1^';
        }

        // 有分销
        if ($res[0]["MSTATUS"] !== null) {
            $listchar .= '分销^2^';
        }

        // 有非我方收筹
        if ($res[0]['SCSTATUS'] !== null) {
            $listchar .= '非我方收筹^8^';
        }

        return $listchar;

    }

    //判断标准调整值是否重复
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


