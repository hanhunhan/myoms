<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MemberDiscountAction extends ExtendAction{
    /**
     * ��Ա�����ύ������Ȩ��
     */
    const SUB_TO_DISCOUNT_LIST = 355;

    private $model;
    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();
    
    //���캯��
    public function __construct() 
    {
        $this->model = new Model();
        parent::__construct();

        // Ȩ��ӳ���
        $this->authorityMap = array(
            'sub_to_discount_list' => self::SUB_TO_DISCOUNT_LIST
        );

        //TAB URL����
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
        !empty($_GET['mid_str']) ? $this->_merge_url_param['mid_str'] = $_GET['mid_str'] : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : '';
    }
    
    /**
    +----------------------------------------------------------
    * ��Ա����
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function add_member_discount_detail()
    {
        $member_discount_model = D("MemberDiscount"); 
		$member_model = D("Member");
        $detail_status = $member_discount_model->get_conf_discount_detail_status();//������ϸ״̬����
        $member_ids = $_REQUEST["memberId"];
		$checks = $member_model->check_member_front_yong($member_ids);
		if($checks){
			$result["state"] = 0;
			$result["msg"] = "����ʧ�ܣ���Ӷ��Ա������������⣡";
			$result["msg"] = g2u($result["msg"]);
            
            echo json_encode($result);//die; 
			die;

		}
        if(is_array($member_ids) && !empty($member_ids))
        {     
            //��ѡ�л�Ա���뵽������ϸ����
            $this->model->startTrans();
            foreach($member_ids as $key=>$val)
            {
                //�̵���ѡ��Ա�Ƿ�����������⡢
                $cond_where = "MID=".$val;
                $cond_where .= " AND STATUS NOT IN (".$detail_status["discount_stop"].",". $detail_status["discount_delete"] .")";
                $dis_mem_info = $member_discount_model->get_discount_detail_by_cond($cond_where,array("ID"));
                if($dis_mem_info)
                {
                    $result["state"] = 2;
                    $result["msg"] = "����ѡ�Ļ�Ա������������ⱻ������������ٴ��������!";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    die;
                }
                $detail_data["MID"] = $val;
                $detail_data["REDUCE_MONEY"] = 0;
                $detail_data["STATUS"] = $detail_status["discount_no_sub"];
                $detail_data["APPLY_TIME"] = date("Y-m-d H:i:s");
                $detail_data["APPLY_USER"] = $_SESSION["uinfo"]["tname"];
                $detail_data["LIST_ID"] = 0;
                $detail_data["CITY_ID"] = $_SESSION["uinfo"]["city"];
                $res = $member_discount_model->add_discount_details($detail_data);

                if($res)//�ɹ����뵽������ϸ�еĻ�Աid����
                {
                    $discount_detail_arr[] = $val;
                }
                  
            }

            //��ѡ��Ա���ɹ����뵽������ϸ
            if(!array_diff($discount_detail_arr,$member_ids) && !array_diff($member_ids,$discount_detail_arr))
            {
                $this->model->commit();
                $result["state"] = 1;
                $result["msg"] = "��ϲ�������ɹ�!";

            }
            else
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "����ʧ�ܣ������ԣ�";
            }

            $member_id_str = implode(",",$member_ids);
            $result["msg"] = g2u($result["msg"]);
            $result["mid_str"] = $member_id_str;
            $result["list_id"] = 0;
            echo json_encode($result);//die;                                   
        }              
        
    }
    
    public function show_discount_detail()
    {
        $city_channel = $this->channelid;
        $member_discount_model = D("MemberDiscount");
        
        $member_discount_status_remark = $member_discount_model->get_conf_discount_detail_status_remark();        
        $list_status = $member_discount_model->get_conf_discount_list_status();//���ⵥ״̬����
        $detail_status = $member_discount_model->get_conf_discount_detail_status();//������ϸ״̬����
        $uid = $_SESSION["uinfo"]["uid"];
        Vendor('Oms.Form');
        $form = new Form();  
        $form->initForminfo(171);
        if($_REQUEST["flowId"])
        {
            $where = " LIST_ID=".$_REQUEST["RECORDID"];
            $is_edit = judgeFlowEdit(intval($_REQUEST["flowId"]),$_SESSION["uinfo"]["uid"]);
            if(!$is_edit)
            {
                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
                $form->GABTN = "";
            }
            else
            {
                $form->EDITABLE = -1;
                $form->ADDABLE = -1; 
                $form->DELABLE = 0;
            }            
        }
        else
        {
            $where = "CITY_ID = ".$city_channel;
            if(!$this->p_auth_all)
            {
                $where .= " AND PRJ_ID IN (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = ".$uid." AND ISVALID = -1 AND (ERP_ID = 1 OR ERP_ID = 2 ))";
            }
            $form->DELCONDITION = "%STATUS% == 1";
        }

        $form->where($where)->setMyField("STATUS","LISTCHAR",array2listchar($member_discount_status_remark));
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign("form",$formHtml);
        $this->assign("paramUrl",$this->_merge_url_param);
        $this->assign("is_flow",$is_flow);
        $this->assign("list_id",$list_id);
        $this->display("member_discount");
    }
    
    /**
     * ���뵽��˵�
     */
    public function sub_to_discount_list()
    {
        $discount_dis = $_POST["discount_ids"] ? $_POST["discount_ids"] : "";        
        $member_discount_model = D("MemberDiscount");    
        $member_model = D("Member");
        
        $detail_status = $member_discount_model->get_conf_discount_detail_status();//������ϸ״̬����
        $discount_id_str = implode(",", $discount_dis);
        $cond_where = "ID IN($discount_id_str)";
        $search_arr = array("ID","REDUCE_MONEY","STATUS","MID","LIST_ID");
        $discount_detail_info = $member_discount_model->get_discount_detail_by_cond($cond_where,$search_arr);
        if( $discount_detail_info )
        {
            foreach($discount_detail_info as $key=>$val)
            {
            
                if(floatval($val["REDUCE_MONEY"]) == 0 || $val["STATUS"] > 1)
                {
                    if(floatval($val["REDUCE_MONEY"]) == 0)
                    {
                        $result["state"] = 0;
                        $result["msg"] = "����������Ϊ0������д��ȷ�Ľ������";
                    }
                    else if($val["STATUS"] > 1)
                    {
                        $result["state"] = 0;
                        $result["msg"] = "����ѡ�ļ�¼�а����������ύҪ��ģ�ֻ��δ�ύ�ļ�¼�ſ����ύ��������ѡ��";
                    }
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;                   
                }              
            }
            foreach ($discount_detail_info as $val)
            {
                if( $val["LIST_ID"] )
                {
                    $detail_status = $member_discount_model->get_conf_discount_detail_status();//������ϸ״̬����
                    $update_arr = array("LIST_ID"=>$val["LIST_ID"],"STATUS"=>$detail_status["discount_no_sub"]);
                    $up_num = $member_discount_model->update_discount_detail_by_id($discount_dis, $update_arr);
                    if($up_num)
                    {
                        $result["state"] = 2;
                        $result["recordid"] = $val["LIST_ID"];
                        $result["msg"] = "�����ɹ�";
                    }
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
            }
        }

        $list_status = $member_discount_model->get_conf_discount_list_status();//���ⵥ״̬����
        $detail_status = $member_discount_model->get_conf_discount_detail_status();//������ϸ״̬����

        //���Ӽ������뵥,������������ϸ
        $list_data["APPLY_USER_ID"] = $_SESSION["uinfo"]["uid"];
        $list_data["APPLY_TIME"] = date("Y-m-d H:i:s");       
        $list_data["STATUS"] = $list_status["discount_list_no_sub"];   
        $this->model->startTrans();
        $insertid = $member_discount_model->add_discount_list($list_data);
        if($insertid)
        {
            $update_arr = array("LIST_ID"=>$insertid,"STATUS"=>$detail_status["discount_no_sub"]);
            $up_num = $member_discount_model->update_discount_detail_by_id($discount_dis, $update_arr);
            if($up_num)
            {
                $this->model->commit();
                $result["state"] = 2;
                $result["recordid"] = $insertid;
                $result["msg"] = "�����ɹ�";
            }
            else
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "����ʧ�ܣ�";
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }       
    }
    
    /**
    +----------------------------------------------------------
    * ��������
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function opinionFlow()
    {
        $member_discount_model = D("MemberDiscount");
        $member_model = D("Member");
        Vendor('Oms.workflow');			
        $workflow = new workflow();

        $flowId = $_REQUEST['flowId'];
        if($flowId)
        {
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
                    if($str)
                    {
                        //����ͬ����޸Ļ�Ա�����������
                        $cond_where = "LIST_ID=".$_REQUEST['RECORDID'];
                        $field_arr = array("MID","REDUCE_MONEY");
                        $info = $member_discount_model->get_discount_detail_by_cond($cond_where,$field_arr);
                        foreach($info as $key=>$val)
                        {
                            $mid[$key] = $val["MID"];
                            $reduce_money[$key] = $val["REDUCE_MONEY"];
                        }
                        $field_arr = array("TOTAL_PRICE","PAID_MONEY","UNPAID_MONEY","REDUCE_MONEY");
                        foreach($mid as $k=>$v)
                        {
                            $member_info = $member_model->get_info_by_id($v,$field_arr);
                            $total_price = $member_info["TOTAL_PRICE"];
                            $paid_money = $member_info["PAID_MONEY"];
                            $unpaid_money = $member_info["UNPAID_MONEY"];
                            
                            if($paid_money == $total_price)
                            {
                                $unpaid_money = $unpaid_money-$reduce_money[$k];
                            }
                            else if($paid_money < $total_price)
                            {
                                $unpaid_money = $unpaid_money - $reduce_money[$k];
                            }
                            
                            $update_arr["UNPAID_MONEY"] = $unpaid_money;
                            $update_arr["REDUCE_MONEY"] = $reduce_money[$k];
                            
                            //�������ͨ����δ�ɽ��С�ڵ���0���Ҹû�Ա���нɷѼ�¼����ȷ�ϣ����޸ĸû�Ա�Ĳ���ȷ��״̬Ϊ��ȷ��
                            if($unpaid_money <= 0)
                            {
                                $member_payment_info = D("Erp_member_payment")->field("ID")->where("MID = ".$v." AND STATUS = 0")->select();
                                if(!$member_payment_info)
                                {
                                    $update_arr["FINANCIALCONFIRM"] = 3;
                                }
                            }
                            $up_num = $member_model->update_info_by_id($v,$update_arr);
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
            $flowtype_pinyin = "jianmianshenqing";
            $auth = $workflow->start_authority($flowtype_pinyin);
            if(!$auth)
            {
                $this->error('����Ȩ��');
            }
            $form = $workflow->createHtml();
            if($_REQUEST['savedata'])
            {   
                $flow_data['type'] = $flowtype_pinyin;
                $flow_data['CASEID'] = "";
                $flow_data['RECORDID'] = $_REQUEST['RECORDID'];                    
                $flow_data['INFO'] = $_POST['INFO'];
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES']; 
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']); 
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']); 
                
                $str = $workflow->createworkflow($flow_data);
                if($str)
                {      
                    js_alert('�ύ�ɹ�',U('MemberDiscount/opinionFlow',$this->_merge_url_param));
                    exit;
                }
                else
                {
                    js_alert('�ύʧ��',U('MemberDiscount/opinionFlow',$this->_merge_url_param));
                    exit;
                }
            }
        }
        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow');       
    }
    
    public function update_discount_info()
    {
        $member_discount_model = D("MemberDiscount");
        $data["REDUCE_MONEY"] = $_REQUEST["reduce_money"];
        $detail_id = $_REQUEST["detail_id"];
        $up_num = $member_discount_model->update_discount_detail_by_id($detail_id,$data);
        echo $up_num;
    }
}