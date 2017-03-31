<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MemberDiscountAction extends ExtendAction{
    /**
     * 会员减免提交工作流权限
     */
    const SUB_TO_DISCOUNT_LIST = 355;

    private $model;
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();
    
    //构造函数
    public function __construct() 
    {
        $this->model = new Model();
        parent::__construct();

        // 权限映射表
        $this->authorityMap = array(
            'sub_to_discount_list' => self::SUB_TO_DISCOUNT_LIST
        );

        //TAB URL参数
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
        !empty($_GET['mid_str']) ? $this->_merge_url_param['mid_str'] = $_GET['mid_str'] : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : '';
    }
    
    /**
    +----------------------------------------------------------
    * 会员减免
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
        $detail_status = $member_discount_model->get_conf_discount_detail_status();//减免明细状态数组
        $member_ids = $_REQUEST["memberId"];
		$checks = $member_model->check_member_front_yong($member_ids);
		if($checks){
			$result["state"] = 0;
			$result["msg"] = "操作失败，后佣会员不允许申请减免！";
			$result["msg"] = g2u($result["msg"]);
            
            echo json_encode($result);//die; 
			die;

		}
        if(is_array($member_ids) && !empty($member_ids))
        {     
            //将选中会员加入到减免明细表中
            $this->model->startTrans();
            foreach($member_ids as $key=>$val)
            {
                //盘点所选会员是否允许申请减免、
                $cond_where = "MID=".$val;
                $cond_where .= " AND STATUS NOT IN (".$detail_status["discount_stop"].",". $detail_status["discount_delete"] .")";
                $dis_mem_info = $member_discount_model->get_discount_detail_by_cond($cond_where,array("ID"));
                if($dis_mem_info)
                {
                    $result["state"] = 2;
                    $result["msg"] = "您所选的会员已申请减免或减免被否决，不允许再次申请减免!";
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

                if($res)//成功加入到减免明细中的会员id数组
                {
                    $discount_detail_arr[] = $val;
                }
                  
            }

            //所选会员均成功加入到减免明细
            if(!array_diff($discount_detail_arr,$member_ids) && !array_diff($member_ids,$discount_detail_arr))
            {
                $this->model->commit();
                $result["state"] = 1;
                $result["msg"] = "恭喜，操作成功!";

            }
            else
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "操作失败，请重试！";
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
        $list_status = $member_discount_model->get_conf_discount_list_status();//减免单状态数组
        $detail_status = $member_discount_model->get_conf_discount_detail_status();//减免明细状态数组
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
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign("form",$formHtml);
        $this->assign("paramUrl",$this->_merge_url_param);
        $this->assign("is_flow",$is_flow);
        $this->assign("list_id",$list_id);
        $this->display("member_discount");
    }
    
    /**
     * 加入到审核单
     */
    public function sub_to_discount_list()
    {
        $discount_dis = $_POST["discount_ids"] ? $_POST["discount_ids"] : "";        
        $member_discount_model = D("MemberDiscount");    
        $member_model = D("Member");
        
        $detail_status = $member_discount_model->get_conf_discount_detail_status();//减免明细状态数组
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
                        $result["msg"] = "申请减免金额不能为0，请填写正确的金额数！";
                    }
                    else if($val["STATUS"] > 1)
                    {
                        $result["state"] = 0;
                        $result["msg"] = "您所选的记录中包含不符合提交要求的，只有未提交的记录才可以提交，请重新选择！";
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
                    $detail_status = $member_discount_model->get_conf_discount_detail_status();//减免明细状态数组
                    $update_arr = array("LIST_ID"=>$val["LIST_ID"],"STATUS"=>$detail_status["discount_no_sub"]);
                    $up_num = $member_discount_model->update_discount_detail_by_id($discount_dis, $update_arr);
                    if($up_num)
                    {
                        $result["state"] = 2;
                        $result["recordid"] = $val["LIST_ID"];
                        $result["msg"] = "操作成功";
                    }
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
            }
        }

        $list_status = $member_discount_model->get_conf_discount_list_status();//减免单状态数组
        $detail_status = $member_discount_model->get_conf_discount_detail_status();//减免明细状态数组

        //增加减免申请单,并关联减免明细
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
                $result["msg"] = "操作成功";
            }
            else
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "操作失败！";
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }       
    }
    
    /**
    +----------------------------------------------------------
    * 审批流程
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
                    if($str)
                    {
                        //流程同意后，修改会员表中相关数据
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
                            
                            //如果减免通过后，未缴金额小于等于0并且该会员所有缴费记录均已确认，则修改该会员的财务确认状态为已确认
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
            $flowtype_pinyin = "jianmianshenqing";
            $auth = $workflow->start_authority($flowtype_pinyin);
            if(!$auth)
            {
                $this->error('暂无权限');
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
                    js_alert('提交成功',U('MemberDiscount/opinionFlow',$this->_merge_url_param));
                    exit;
                }
                else
                {
                    js_alert('提交失败',U('MemberDiscount/opinionFlow',$this->_merge_url_param));
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