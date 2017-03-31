<?php

/* 
 * ���ʱ��������������
 * 
 */
class Payout_changeAction extends ExtendAction{
    private $model;
    private $tab_num;
    private $_merge_url_param = array();
    public function __construct(){
        $this->model = new Model();
        $this->tab_num = 19;
        parent::__construct();
        !empty($_REQUEST["prjid"]) ? $this->_merge_url_param["prjid"] = $_REQUEST["prjid"] : 0;
        !empty($_REQUEST["CASEID"]) ? $this->_merge_url_param["CASEID"] = $_REQUEST["CASEID"] : 0;
        !empty($_REQUEST["FLOWTYPE"]) ? $this->_merge_url_param["FLOWTYPE"] = $_REQUEST["FLOWTYPE"] : "";
        !empty($_REQUEST["RECORDID"]) ? $this->_merge_url_param["RECORDID"] = $_REQUEST["RECORDID"] : 0;   
        !empty($_REQUEST["scale_type"]) ? $this->_merge_url_param["scale_type"] = $_REQUEST["scale_type"] : 0;
        !empty($_REQUEST["last_payout_list_id"]) ? $this->_merge_url_param["last_payout_list_id"] = $_REQUEST["last_payout_list_id"] : 0;
        !empty($_REQUEST["flowId"]) ? $this->_merge_url_param["flowId"] = $_REQUEST["flowId"] : 0;
        $this->_merge_url_param['TAB_NUMBER'] = $this->tab_num;
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
    }

    public function  payout_change()
    {
        $flowid = $_REQUEST["flowId"] ? $_REQUEST["flowId"] : 0;
        $payout_model = D("PayoutChange");

        $prjid = $_REQUEST["prjid"];
        $scale_type = $_REQUEST["scale_type"];
        $form_data = array();
        $this->project_case_auth($prjid);//��Ŀҵ��Ȩ���ж�
        //�ɱ�������
        if ($_REQUEST["layer"] && $_REQUEST["chengbenhuabo"]) {
            $case_model = D("ProjectCase");
            $case_info = $case_model->get_info_by_pid($prjid, "", array("ID", "SCALETYPE", "FSTATUS"));

            foreach ($case_info as $key => $val) {
                //1. ���������ͨ��  2.���4�ֲ�Ʒ   ���̡�������Ӳ�㡢������ҷ��ճ�
                if (($val['FSTATUS'] == 2 || $val['FSTATUS'] == 4) && ($val["SCALETYPE"] <= 4 || $val["SCALETYPE"] == 8)) {
                    $res = D("Erp_businessclass")->field("YEWU")->where("ID=" . $val["SCALETYPE"])->find();
                    $business_type[$val["SCALETYPE"]] = $res["YEWU"];
                }
            }
            $this->assign("business_type", $business_type);
            //�Ƿ�۷�
            $this->assign("koufei", 1);
        } elseif ($_REQUEST["check_chengbenhuabo"]) { // ���ɱ������Ƿ���ڷ�������
            $return = array(
                'status' => 0,
                'msg' => '',
                'data' => null,
            );

            $case_model = D("ProjectCase");
            $case_info = $case_model->get_info_by_pid($prjid, "", array("ID", "SCALETYPE", "FSTATUS"));

            //ҵ��״̬�����и���
            $flag_count = 0;
            if ($case_info) {
                $return['status'] = 1;
                foreach ($case_info as $key => $val) {
                    //1. �������Ŀ��ҵ���ڽ�����    2.���4�ֲ�Ʒ   ���̡�������Ӳ�㡢�
                    if (($val['FSTATUS'] == 2 || $val['FSTATUS'] == 4) && ($val["SCALETYPE"] <= 4 || $val["SCALETYPE"] == 8)) {
                        $return_s_type = $val['SCALETYPE'];
                        $flag_count++;
                    }
                }
                $return['data'] = array('flag_count' => $flag_count, 'scale_type' => $return_s_type);
            }
            die(@json_encode($return));
        } elseif ($_REQUEST["layer"]) {
            $project_model = D("Project");
            $case_model = D("ProjectCase");
            $case_info = $case_model->get_info_by_pid($prjid, "", array("ID", "SCALETYPE"));
            foreach ($case_info as $key => $val) {
                if ($val["SCALETYPE"] == 1 || $val["SCALETYPE"] == 2 || $val["SCALETYPE"] == 8) {
                    $res = D("Erp_businessclass")->field("YEWU")->where("ID=" . $val["SCALETYPE"])->find();
                    $business_type[$val["SCALETYPE"]] = $res["YEWU"];
                }
            }
            $this->assign("business_type", $business_type);
        } else {
            if ($_REQUEST["is_ajax"] == 1) {
                $textsql = "select a.id CASE_ID,b.city_id from erp_case a 
                left join erp_project b on a.project_id=b.id where b.id =" . $prjid . " and a.scaletype = " . $scale_type;
                $field = $this->model->query($textsql);
                //�жϸð����Ƿ��Ѿ������
                $cond_where = "CASE_ID = " . $field[0]["CASE_ID"];
                $is_exits = $payout_model->get_info_by_cond($cond_where, array("ID"));
                //var_dump($is_exits);die;
                if ($is_exits)//����Ѿ����� ajax��ʾ����н��
                {
                    $result["state"] = 1;
                    $result["msg"] = $is_exits[0]["ID"];
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                } else   //û�о�������չʾ
                {
                    //���ӵ��ʱ��������¼
                    $data["CASE_ID"] = $field[0]["CASE_ID"];
                    $data["APPLY_USER"] = $_SESSION["uinfo"]["tname"];
                    $data["APPLY_DATE"] = date("Y-m-d H:i:s", time());

                    $data["CITY_ID"] = $field[0]["CITY_ID"];
                    $data["STATUS"] = 1;
                    $insertid = $payout_model->add_payout_info($data);
                    if (!$insertid) {
                        $result["state"] = 0;
                        $result["msg"] = "ϵͳ���������ԣ���";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                    $this->_merge_url_param["RECORDID"] = $insertid;
                    $result["state"] = 1;
                    $result["msg"] = $insertid;
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
            } else if (!$_REQUEST["is_ajax"]) {
                if (!$this->_merge_url_param["RECORDID"]) {
                    $last_payout_list_id = $_REQUEST["last_payout_list_id"];
                } else {
                    $last_payout_list_id = $this->_merge_url_param["RECORDID"];
                }

                //�ж���Ŀ�Ƿ��Ѿ�����
                $is_summery = is_scale_have_summery($prjid, $scale_type);
                if ($is_summery) {
                    $is_summery = 1;
                } else {
                    $is_summery = 0;
                }

                //չʾ��������
                $payout_status = D("PayoutChange")->get_payout_status_remark();
                $payout_info = D("PayoutChange")->get_info_by_id($last_payout_list_id,
                    array("REASON", "APPLY_USER", "APPLY_DATE", "NEW_PAY_OUT", "CASE_ID", "STATUS"));
                $reason = $payout_info[0]["REASON"];         //����ԭ��
                $apply_user = $payout_info[0]["APPLY_USER"]; //������
                $apply_time = $payout_info[0]["APPLY_DATE"]; //����ʱ��
                //$apply_time = "22-12��-15";
                //���ʱ���ʽΪ22-12��-15������ת��Ϊ2015-12-22
                preg_match('/(?<d>\d{2})-(?<m>\d{1,2})��\s*-(?<y>\d{2})/', $apply_time, $match);
                if ($match) {
                    $apply_time = date('Y-m-d', strtotime($match['y'] . '-' . $match['m'] . '-' . $match['d']));
                }
                $apply_rate = $payout_info[0]["NEW_PAY_OUT"];//������ʱ���
                $case_id = $payout_info[0]["CASE_ID"];
                $status = $payout_status[$payout_info[0]["STATUS"]];
                $this->assign('payoutStatus', $payout_info[0]['STATUS']);  // ����״ֵ̬
                $projectcase_info = D("ProjectCase")->get_info_by_id($case_id, array("PROJECT_ID", "SCALETYPE"));
                $scale_type = D("Erp_businessclass")->field("YEWU")->where("ID = " . $projectcase_info[0]["SCALETYPE"])->find();

                $project_info = D("Project")->get_info_by_id($projectcase_info[0]["PROJECT_ID"], array("PROJECTNAME", "CONTRACT", "CITY_ID"));
                $city = D("Erp_city")->field("NAME")->where("ID=" . $project_info[0]["CITY_ID"])->select();

                $rrate = D('ProjectCase')->getLoanMoney($case_id,0, 2,1); // �ѵ��ʱ���
                $vloan = D('ProjectCase')->getLoanMoney($case_id,0, 1); // �ѵ��ʽ��
                $precost = null;
                $prerate = null;
                D('ProjectCase')->getPreCostPreRate($case_id, $precost, $prerate); // ����Ԥ�㸶�ֳɱ�������Ԥ�㸶�ֳɱ���

                $payout_money = $vloan ? $vloan : 0;                      //�ѵ��ʽ��
                $rate = $prerate ? round(floatval($prerate), 2) : 0;                         //����Ԥ�㸶�ֳɱ���
                $payout_rate = $rrate ? floatval($rrate) : 0;                      //�ѵ��ʱ���
                $payout_cost = $precost ? $precost : 0;                  //����Ԥ�㸶�ֳɱ�

                $form_data = array(
                    "CASE_ID" => $case_id,
                    "PROJECTNAME" => $project_info[0]["PROJECTNAME"],
                    "CONTRACE_NO" => $project_info[0]["CONTRACT"],
                    "APPLY_USER" => $apply_user,
                    "APPLY_TIME" => $apply_time,
                    "CITY" => $city[0]["NAME"],
                    "SCALE_TYPE" => $scale_type["YEWU"],
                    "STATUS" => $status,
                    "PAYOUT_MONEY" => $payout_money,
                    "RATE" => $rate,
                    "PAYOUT_RATE" => $payout_rate,
                    "PAYOUT_COST" => $payout_cost,
                    "NEW_PAY_OUT" => $apply_rate,
                    "REASON" => $reason,
                );
                $this->assign("form_data", $form_data);
                $this->assign("paramUrl", $this->_merge_url_param);
                $this->assign("last_id", $last_payout_list_id);
                if (!$_REQUEST["flowId"]) {
                    $status_control = $payout_info[0]["STATUS"];
                } else {
                    $status_control = 0;
                }
                $this->assign("status", $status_control);
            }
        }
        $this->assign('isShowOptionBtn', $this->isShowOptionBtn($case_id));  // �Ƿ���ʾ�ɲ�����ť
        $this->assign("is_summery", $is_summery);
        $this->assign("layer", $_REQUEST["layer"]);
        $this->assign('flowid', $flowid);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display("payout_change");
    }
        
    
    /**
     +----------------------------------------------------------
     * �������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    function opinionFlow()
    {   
        //��������
        $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'dianziedu'")->find();

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
                        //����ͨ�� ��д���ʱ�������ĿԤ�����
                        $where_cond = "CASE_ID=".$_REQUEST["CASEID"];
                        $payout_info = D("PayoutChange")->get_info_by_cond($where_cond,array("NEW_PAY_OUT"));
                        $save_data = array("PAYOUT"=>round($payout_info[0]["NEW_PAY_OUT"]/100,2));
                        $up_num = D("Erp_prjbudget")->where("CASE_ID=".$_REQUEST["CASEID"])->save($save_data);
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
                    if($str)
                    {
                        //����ͬ����޸���ĿԤ���
                        $new_pay_out = D("PayoutChange")->get_info_by_id($_REQUEST["RECORDID"], array("NEW_PAY_OUT"));
                        $new_pay_out = round($new_pay_out[0]["NEW_PAY_OUT"]/100,2);
                        $res = D("Erp_prjbudget")->where("CASE_ID=".$_REQUEST["CASEID"])->setField("PAYOUT",$new_pay_out);
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
            $flow_type_py = "dianziedu";
            $auth = $workflow->start_authority($flow_type_py);
            if(!$auth)
            {
                $this->error('����Ȩ�޴����ù�����');
            }
            $form = $workflow->createHtml();

            if($_REQUEST['savedata'])
            {   
                if($this->_merge_url_param["prjid"])
                {
                    $scale_type = $this->_merge_url_param["scale_type"];
                    $prj_id = $this->_merge_url_param["prjid"];
                    $case_id = D("Erp_case")->where("SCALETYPE=".$scale_type." and PROJECT_ID=".$prj_id)->field("ID")->select();
                    $case_id = $case_id[0]["ID"];
                }
                else
                {
                    $case_id = $_REQUEST["CASEID"]; 
                }
                $flow_data['type'] = $flow_type_py; 
                $flow_data['CASEID'] = $case_id;
                $flow_data['RECORDID'] = $_REQUEST["RECORDID"];
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES'];
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']);
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']);                
               
                //var_dump($flow_data);die;
                $str = $workflow->createworkflow($flow_data);
                
                if($str)
                {        
                    $this->success('�ύ�ɹ�', U('Payout_change/payout_change', $this->_merge_url_param));                    
                }
                else
                {
                    $this->error('�ύʧ��', U('Payout_change/opinionFlow', $this->_merge_url_param));
                }
                exit;
            }
        }
        
        $this->assign('form', $form);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('opinionFlow');
    }
    
    
    //ajax�첽��������
    public function insert_payout_data(){
        $payout_model = D("PayoutChange");
        if(!empty($_POST["reason"])) $update_arr["REASON"] = strip_tags((u2g($_POST["reason"])));
        if(!empty($_POST["new_pay_out"])) $update_arr["NEW_PAY_OUT"] = strip_tags(trim($_POST["new_pay_out"]));
        $lastid = $_POST["lastid"];
        $up_num = $payout_model->update_info_by_id($lastid,$update_arr);
        //echo M()->_sql();die;
        if($up_num)
        {
            $result["state"] = 1;
            $result["msg"] = "���ݱ���ɹ���";
        }
        else
        {
            $result["state"] = 0;
            $result["msg"] = "���ݱ���ʧ�ܣ�";
        }
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
     }
    
    
    //ѡ��ҵ������ ajax
    public function scale_type()
    {
        $project_model = D("Project");
        if(!empty($_REQUEST["prjid"])) $prj_id = $_REQUEST["prjid"];
        $project_model = D("Project");
        $case_model = D("ProjectCase");
         //$prj_info = $project_model->get_info_by_id($prj_id,array("BSTATUS","MSTATUS"));
        $case_info = $case_model->get_info_by_pid($prj_id,"",array("ID","SCALETYPE"));
        foreach($case_info as $key=>$val)
        {
           $res = D("Erp_businessclass")->field("YEWU")->where("ID=".$val["SCALETYPE"])->find();
           $business_type[$val["SCALETYPE"]] = $res["YEWU"];
        }
        $this->assign("business_type",$business_type);
        $this->display("choose_scale_type");       
    }
}
