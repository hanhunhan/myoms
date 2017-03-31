<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * ��Ա����Ʊ������
 * edit xuyemei
 */

class ChangeInvoiceAction extends ExtendAction{
    private $model;
    private $_merge_url_param = array();
   
    //���췽��
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
     * ���뻻��Ʊ�б�չʾҳ��
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
            //���뻻Ʊ��¼
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
     * ��Ա����Ʊ�������
     * @param  none
     * return none
     */
    public function apply_change_invoice()
    {
        $member_model = D("Member");
        $billing_model = D("BillingRecord");
        //Ҫ���뻻��Ʊ�Ļ�Ա�� id ����
        $memberids  = $_POST["memberid"];
        $change_invoice_status = $member_model->get_conf_change_invoice_status();
        //��ǰ�������Ա��Ʊ���ڵĹ�ϵ���ж��Ƿ�������        
        $current_month =intval(date("m"));//��ǰ�·�
        //��ѡ��Ա�Ŀ�Ʊ�·�
        $member_info = $member_model->get_info_by_ids($memberids,array("CONFIRMTIME","CASE_ID","INVOICE_NO","CHANGE_INVOICE_STATUS"));        
        if($member_info[0]["CHANGE_INVOICE_STATUS"] == $change_invoice_status["apply_change_invoice_success"] || 
            $member_info[0]["CHANGE_INVOICE_STATUS"] == $change_invoice_status["apply_change_invoice"])
        {
            $result["state"] = 0;
            $result["msg"] = "�û�Ա��Ʊ��������ִ���У������ظ�����!";
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        //ת��oracleʱ���ʽ
        $format_date = oracle_date_format($member_info[0]["CONFIRMTIME"]);
        //preg_match('/(?<d>\d{2})-(?<m>\d{1,2})��\s*-(?<y>\d{2})/',$member_info[0]["CONFIRMTIME"],$match);
        if($format_date)
        {
            $invoice_time_month = intval(substr($format_date,5,2));
        }
        else
        {
            $invoice_time_month = intval(substr($member_info[0]["CONFIRMTIME"],5,2));
        }
        $case_id = $member_info[0]["CASE_ID"];
        //�����ͬ��
        if( $current_month == $invoice_time_month)
        {
            //�޸Ļ�Ա�Ļ�Ʊ״̬Ϊ������
            $change_invoice_status = $member_model->get_conf_change_invoice_status();
            $this->model->startTrans();
            $up_num=$member_model->update_info_by_id($memberids,array("CHANGE_INVOICE_STATUS"=>$change_invoice_status["apply_change_invoice_success"]));
            if($up_num)
            {  
                $this->model->commit();
                $result["state"] = 1;
                $result["msg"] = "����ɹ�!";                   
            }
            else
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "����ʧ��!";
                
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        //����ͬ��
        else if($current_month != $invoice_time_month)
        {
            //$this->redirect("ChangeInvoice/change_invoice_manage");
            $result["state"] = 2;
            $result["msg"] = "��Ʊʱ���뻻��Ʊʱ�䲻��ͬ�£��������̽�������!";
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
            
        }
        
    }
    
    /*
     * ��Ա����Ʊ��������
     * @param none
     * return none
     */
    public function opinionFlow()
    {   
        //��������
        $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'huiyuanhuanpiao'")->find();

        $type = !empty($workflow_type_info['ID']) ? $workflow_type_info['ID'] : '';
        
        if($type == '')
        {
            $this->error('���������Ͳ�����');
            exit;
        }
        
        //������ID
        $flowId = !empty($_REQUEST['flowId']) ? intval($_REQUEST['flowId']) : 0;
        
        //����������ҵ��ID
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
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                    
                }
                else if($_REQUEST['flowPass'])
                {
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {     
                        js_alert('ͬ��ɹ�', U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('ͬ��ʧ��');
                    }
                    
                }
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {   
                        //���̱����
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('���ʧ��');
                    }
                    
                }
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('δ�����ؾ���ɫ');exit;
					}
                    
                    $str = $workflow->finishworkflow($_REQUEST);
                    //var_dump($str);die;
                    if($str)
                    {
                        //����ͬ����޸���ĿԤ���                        
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
            $flow_type_py = "huiyuanhuanpiao";
            $auth = $workflow->start_authority($flow_type_py);
            if(!$auth)
            {
                $this->error('����Ȩ�޴����ù�����');
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
                $flow_data['RECORDID'] = $_REQUEST["memberid"];//�û�ԱID����Ϊ�����е�RECORDID
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
                    $this->success('�ύ�ɹ�', U('ChangeInvoice/change_invoice_manage', $this->_merge_url_param));                    
                }
                else
                {
                    $this->error('�ύʧ��', U('ChangeInvoice/change_invoice_manage', $this->_merge_url_param));
                }
                exit;
            }
        }
        
        $this->assign('form', $form);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->display('opinionFlow');
    }
}

