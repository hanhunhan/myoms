<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 会员换发票控制器
 * edit xuyemei
 */

class ChangeInvoiceAction extends ExtendAction{
    private $model;
    private $_merge_url_param = array();
   
    //构造方法
    public function __construct() {
        parent::__construct();
        $this->model = new Model();
        !empty($_REQUEST["CASEID"]) ? $this->_merge_url_param["CASEID"] = $_REQUEST["CASEID"] : 0;
        !empty($_REQUEST["FLOWTYPE"]) ? $this->_merge_url_param["FLOWTYPE"] = $_REQUEST["FLOWTYPE"] : "";
        !empty($_REQUEST["RECORDID"]) ? $this->_merge_url_param["RECORDID"] = $_REQUEST["RECORDID"] : 0;   
        !empty($_REQUEST["flowId"]) ? $this->_merge_url_param["flowId"] = $_REQUEST["flowId"] : 0;
        !empty($_REQUEST["operate"]) ? $this->_merge_url_param["operate"] = $_REQUEST["operate"] : 0;
        !empty($_REQUEST["memberid"]) ? $this->_merge_url_param["memberid"] = $_REQUEST["memberid"] : 0;
        
    }
    
    /*
     * 申请换发票列表展示页面
     * @param none
     * return none
     */
    public function change_invoice_manage()
    {
        //echo 111;
        $memberid = $_REQUEST["memberid"] ? $_REQUEST["memberid"] : $_REQUEST["RECORDID"];
        $sql = "SELECT ID FROM ERP_CHANGE_INVOICE_DETAIL WHERE MID = ".$memberid;
        $res = M()->query($sql);
        if(!$res)
        {
            //插入换票记录
            $ch_arr['MID'] = $memberid;
            $ch_arr["APPLY_TIME"] = date("Y-m-d H:i:s");
            $ch_arr["APPLY_USER_ID"] = $_SESSION["uinfo"]["uid"];
            $ch_arr["STATUS"] = 1;
            $insertid = M("Erp_change_invoice_detail")->add($ch_arr);
        }
        
        Vendor('Oms.Form');			
        $form = new Form();
        $where = "MID = ".$memberid;
        $form->initForminfo(189)->where($where);

        if($_REQUEST["flowId"])
        {
            $is_edit = judgeFlowEdit(intval($_REQUEST["flowId"]),$_SESSION["uinfo"]["uid"]);
            if(!$is_edit)
            {
                $form->GABTN = "";
            }
        }
        $form = $form->getResult();

        $this->assign("form",$form);
        $this->assign("paramUrl",$this->_merge_url_param);
        $this->assign("memberid",$memberid);
        $this->display("change_invoice_manage"); 

    }
    
    /*
     * 会员换发票申请操作
     * @param  none
     * return none
     */
    public function apply_change_invoice()
    {
        $member_model = D("Member");
        $billing_model = D("BillingRecord");
        //要申请换发票的会员的 id 数组
        $memberids  = $_POST["memberid"];
        $change_invoice_status = $member_model->get_conf_change_invoice_status();
        //当前日期与会员开票日期的关系，判断是否走流程        
        $current_month =intval(date("m"));//当前月份
        //所选会员的开票月份
        $member_info = $member_model->get_info_by_ids($memberids,array("CONFIRMTIME","CASE_ID","INVOICE_NO","CHANGE_INVOICE_STATUS"));        
        if($member_info[0]["CHANGE_INVOICE_STATUS"] == $change_invoice_status["apply_change_invoice_success"] || 
            $member_info[0]["CHANGE_INVOICE_STATUS"] == $change_invoice_status["apply_change_invoice"])
        {
            $result["state"] = 0;
            $result["msg"] = "该会员换票操作正在执行中，不能重复申请!";
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        //转换oracle时间格式
        $format_date = oracle_date_format($member_info[0]["CONFIRMTIME"]);
        //preg_match('/(?<d>\d{2})-(?<m>\d{1,2})月\s*-(?<y>\d{2})/',$member_info[0]["CONFIRMTIME"],$match);
        if($format_date)
        {
            $invoice_time_month = intval(substr($format_date,5,2));
        }
        else
        {
            $invoice_time_month = intval(substr($member_info[0]["CONFIRMTIME"],5,2));
        }
        $case_id = $member_info[0]["CASE_ID"];
        //如果在同月
        if( $current_month == $invoice_time_month)
        {
            //修改会员的换票状态为申请中
            $change_invoice_status = $member_model->get_conf_change_invoice_status();
            $this->model->startTrans();
            $up_num=$member_model->update_info_by_id($memberids,array("CHANGE_INVOICE_STATUS"=>$change_invoice_status["apply_change_invoice_success"]));
            if($up_num)
            {  
                $this->model->commit();
                $result["state"] = 1;
                $result["msg"] = "申请成功!";                   
            }
            else
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "申请失败!";
                
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        //不在同月
        else if($current_month != $invoice_time_month)
        {
            //$this->redirect("ChangeInvoice/change_invoice_manage");
            $result["state"] = 2;
            $result["msg"] = "开票时间与换发票时间不在同月，请走流程进行审批!";
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
            
        }
        
    }
    
    /*
     * 会员换发票流程审批
     * @param none
     * return none
     */
    public function opinionFlow()
    {   
        //流程类型
        $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'huiyuanhuanpiao'")->find();

        $type = !empty($workflow_type_info['ID']) ? $workflow_type_info['ID'] : '';
        
        if($type == '')
        {
            $this->error('工作流类型不存在');
            exit;
        }
        
        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ? intval($_REQUEST['flowId']) : 0;
        
        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ? intval($_REQUEST['RECORDID']) : 0;
        
        Vendor('Oms.workflow');
        $workflow = new workflow();

        if($flowId > 0)
        {
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata'])
            {
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
                else if($_REQUEST['flowPass'])
                {
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {     
                        js_alert('同意成功', U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('同意失败');
                    }
                    
                }
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {   
                        //流程被否决
                        js_alert('否决成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('否决失败');
                    }
                    
                }
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('未经过必经角色');exit;
					}
                    
                    $str = $workflow->finishworkflow($_REQUEST);
                    //var_dump($str);die;
                    if($str)
                    {
                        //流程同意后，修改项目预算表                        
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
            $flow_type_py = "huiyuanhuanpiao";
            $auth = $workflow->start_authority($flow_type_py);
            if(!$auth)
            {
                $this->error('您无权限创建该工作流');
            }
            $form = $workflow->createHtml();

            if($_REQUEST['savedata'])
            {   
                if($this->_merge_url_param["memberid"])
                {
                    $memberid = $this->_merge_url_param["memberid"];
                    $case_id = D("Erp_cardmember")->where("ID = ".$memberid)->field("CASE_ID")->find();
                    $case_id = $case_id["CASE_ID"];
                }
                else
                {
                    $case_id = $_REQUEST["CASEID"]; 
                }
                $flow_data['type'] = $flow_type_py; 
                $flow_data['CASEID'] = $case_id;
                $flow_data['RECORDID'] = $_REQUEST["memberid"];//用会员ID来作为流程中的RECORDID
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES'];
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']);
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']);                
               
                $str = $workflow->createworkflow($flow_data);
                //var_dump($str);die;
                if($str)
                {        
                    $this->success('提交成功', U('ChangeInvoice/change_invoice_manage', $this->_merge_url_param));                    
                }
                else
                {
                    $this->error('提交失败', U('ChangeInvoice/change_invoice_manage', $this->_merge_url_param));
                }
                exit;
            }
        }
        
        $this->assign('form', $form);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->display('opinionFlow');
    }
}

