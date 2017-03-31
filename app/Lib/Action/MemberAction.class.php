<?php
class MemberAction extends ExtendAction{
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
        load("@.member_common");
    }
    
    
    public function index()
    {
        $city = $_SESSION["uinfo"]["city"];;

        $search = $this->_post('search');
        $model = D('member');
        $where = "m_city  = '$city'";

        if($search)
        {
          $where .= " and ( m_uid = '".$search."' or m_email= '".$search."' "
                  . "or m_moblie = '".$search."' )";  
        }
        
        $count = $model->where($where)->count();

        import("ORG.Util.Page");
        $p = new Page($count,C('PAGESIZE'));
        $para = "&bu_del=".$bu_del;
        if($para) $p->parameter = $para;
        $page = $p->show();	

        $user_info = $model->where($where)->limit($p->firstRow.','.$p->listRows)->select();

        $this->assign('search',$search);
        $this->assign('page',$page);
        $this->assign('data',$user_info);
        $this->display();
    }

    
    /**
    +----------------------------------------------------------
    * ������Ȼ�����ͻ�¼��
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function newMember()
    {
        
        $time_now = time();
        //��ȡ��ǰ����
        $city_id = $_SESSION["uinfo"]["city"];
        //$user_city = $_SESSION[DB_PREFIX.'city']; //�û����ڳ���
        $city = M("Erp_city");
        $user_city_py = $city->where("ID=$city_id")->field("PY")->select();
        $user_city_py = strtolower($user_city_py[0]["PY"]);

        if ($_POST) 
        {
            $pro_id = abs(intval($_POST['prjname'])); //��ĿId
			//$pro_id = 1;//��ĿId����1
            $telno = trim(strip_tags($_POST['telno']));//�ͻ��ֻ���
            $cusname = trim(strip_tags($_POST['cusname']));//�ͻ�����
            if (!$pro_id || !preg_match('/^1[0-9]{10}$/',$telno) || strlen($cusname) < 3 ) 
            {
                echo 0;
                exit();
            }            
            $project = M('Erp_house')->where(array('id'=> $pro_id))->limit(1)->select();
            //var_dump($project);die;
            if (!$project) 
            {
				js_alert('û���ҵ������Ŀ',U('Member/newMember'),0);
				exit();
            }

            $activename = urlencode($project['PRO_NAME'].'��Ȼ����');
            //$loupanids = $project['pro_listid'];
            $loupanids = $project[0]['REL_NEWHOUSEID'];
            $cpi_arr = array(
                    'city'=>$user_city_py,
                    'mobile'=>$telno,
                    'username'=>$cusname,
                    'activefrom'=>231,
                    'activename'=>$activename,
                    'loupanids'=>$loupanids,
                    //'loupanids'=>247,//¥��Id����247
                    );
            $url_crm = submit_crm_data_by_api($cpi_arr);//boolen
            
            if($url_crm)
            {
                js_alert('����ɹ�',U('Member/index'),0);
            }
            else
            {
                js_alert('������¼��������Ƿ���ȷ',U('Member/newMember'),0);
            }
		}else{
			Vendor('Oms.Form');
			$form = new Form();
            // $sql = "select ID,PRO_NAME from ERP_HOUSE where PROJECT_ID ="
            //    . "(select PRO_ID from ERP_PROROLE where USE_ID=".$_SESSION["uinfo"]["uid"]."and ISVALID=-1)";
			$form =  $form->initForminfo(110)->setMyField("prjname","LISTSQL",$sql)->getResult();
			$this->assign('form',$form);
			$this->display('new_member');

		}	
    }
	
	
     /**
    +----------------------------------------------------------
    * ��Ա����ȷ��
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function arrivalConfirm()
	{   
        $city_id = $_SESSION["uinfo"]["city"];
        $user_city = $_SESSION[DB_PREFIX.'city']; //�û����ڳ���
        $city = M("Erp_city");
        $user_city_py = $city->where("'ID=$city_id")->field("PY")->select();
        $user_city_py = strtolower($user_city_py[0]["PY"]);
        $form_sub_auth_key = md5("HOUSE365_JINGGUAN_".date('Ymd').'_'.$p_uid);
        $action_type = isset($_POST['action_type']) ? strip_tags($_POST['action_type']) : '';
        $is_login_from_oa = isset($_SESSION[DB_PREFIX.'fromoa']) ? $_SESSION[DB_PREFIX.'fromoa'] : false;
        $project_id = $_POST["project_id"];
        if($project_id && $action_type == ""){                
            //����¥��ID ��ȡ��������ĿID
            $house = M("Erp_house");
            $project_listid = $house->where("PROJECT_ID = $project_id")->field("REL_NEWHOUSEID")->select();
            $project_listid = $project_listid[0]["REL_NEWHOUSEID"];                
            echo $project_listid;
            exit();
        }

        switch ($action_type)
        {  
            //ǩ��ȷ��
            case 'arrive_confirm':
                //ȷ����֤��
                $authcode_key = strip_tags($_POST['authcode_key']);

                //ͨ��¥�̱��
                $project_listid = intval($_POST['project_listid']);

                //��Ŀ���
                $project_id = intval($_POST['project_id']);

                //��֤��
                $code = strip_tags($_POST['code']);

                //�ͻ���ʵ����
                $truename = strip_tags($_POST['name']);

                //�ͻ��ֻ�����
                $telno = strip_tags($_POST['phone']);

                //������Դ
                $is_from = strip_tags($_POST['is_from']);

                //�ͻ�ID
                $customer_id = intval($_POST['customer_id']);

                //���ݿͻ���֤���ȡ����ĿID
                $user_project_id = intval($_POST['user_project_id']);

                if( $authcode_key == $form_sub_auth_key && $customer_id > 0)
                {   
                    if( $project_id > 0 && $code > 0)
                    {   
                        //�ж���֤���õ���Ŀ�뵱ǰѡ�е���Ŀ�Ƿ�һ��
                        if( ($is_from == 1 && $user_project_id == $project_id ) || 
                            ($is_from == 2 && $user_project_id == $project_listid ) )
                        {
                            if($is_from == 1)
                            {   
                                //������Ŀ��ź��ֻ������ѯFGJϵͳ���Ƿ�����Ѿ�ȷ�ϵ��û���
                                //������������޷��ٴε���ȷ��
                                $fgj_user_info = array();
                                $fgj_user_info = get_fgj_userinfo_by_pid_telno($project_listid, $telno);
                                if(is_array($fgj_user_info) && !empty($fgj_user_info) &&
                                    $fgj_user_info['result'] == 1 && !empty($fgj_user_info['data']))
                                {   
                                    $is_confirmed = 0;
                                    foreach ($fgj_user_info['data'] as $key => $value)
                                    {
                                        //0��ʾδ�������ڣ�1��ʾ�Ѿ����˱�����
                                        if($value['overProtection'] == 0)
                                        {   
                                            //����ȷ��״̬ 1δ��֤��0�����Ѿ���֤
                                            if( $value['status'] == 0 && $value['overProtection'] == 0 )
                                            {
                                                $is_confirmed = 1;
                                            }
                                        }
                                    }

                                    if($is_confirmed == 1)
                                    {
                                        $msg = '���û��Ѿ��ڷ��ܼ�ϵͳ�е���ȷ�ϣ��޷��ٴε���ȷ��';
                                        halt_http_referer($msg);
                                        exit;
                                    }
                                }
                                $result = arrival_confirm_crm($customer_id, $code);	
                            }
                            else if($is_from == 2)
                            {   
                                //������ID
                                $ag_id = intval($_POST['ag_id']);
                                //����ID
                                $cp_id = intval($_POST['cp_id']);

                                //FGJǩ��ȷ�ϸ���CRM�û�״̬
                                $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($project_id, $telno, $user_city_py);
                                if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 && 
                                        !empty($userinfo_crm_arr['meminfo']))
                                {    
                                    if( $userinfo_crm_arr['meminfo']['codestatus'] == 1 )
                                    {

                                        $msg = '���û��Ѿ���CRMϵͳ�е���ȷ�ϣ��޷��ٴε���ȷ��';
                                        halt_http_referer($msg);
                                        exit;
                                    }
                                    else
                                    {

                                        //CRM���û�ID
                                        $customer_id_crm = 0;
                                        $customer_id_crm = $userinfo_crm_arr['meminfo']['pmid'];
                                        $up_result = update_crm_user_source($customer_id_crm , 5);
                                    }

                                    //var_dump($result);
                                }
                                $result = arrival_confirm_fgj($customer_id, $ag_id, $cp_id);
                                $is_sucess = intval($result['result']);
                                $msg = $is_sucess == 1 ? '����ȷ�ϳɹ�' : '��֤ʧ�ܣ���֤����Ч���ѹ���';
                               // arrival_confirm_log($customer_id , $truename , $telno , $code , 
                               // $project_listid , $project_id , $is_from , $is_sucess);
                            }

                        }
                        else
                        {
                            //��Ϊ��Ȼ������ӵ�CRM
                            $reg_city = isset($cfg['citypinyin'][$user_city]) ? $cfg['citypinyin'][$user_city] : '';
                            $project_name = strip_tags($_POST['project_name_hide']);
                            $reg_result = register_natural_customer($reg_city , $truename , $telno , $project_listid , $project_name);
                            $msg = "��֤���ȡ��Ŀ��Ϣ�뵱ǰ��Ŀ��Ϣ��һ��";
                            $msg .= $reg_result == 1 ? ',�û���Ϣ����Ϊ��Ȼ����¼�룡' : '!';                     
                        }
                    }
                    else
                    {
                        $msg = "��Ŀ���ƺ���֤�������д";
                    }
                }
                else
                {
                    $msg = "��������,��֤ʧ��";
                }
                halt_http_referer($msg);
                exit;
                break;

            //������֤���ȡ�û���Ϣ
            case 'ajax_userinfo_by_code':
                $code = intval($_POST['code']);
                $project_listid = intval($_POST['project_listid']);
                $userinfo = get_userinfo_by_code($code, $project_listid);
                echo json_encode($userinfo);
                exit;
                break;

            //���û�û����֤��ʱ�������û��ֻ�����򾭼����ֻ������ȡ�û���Ϣ
            case 'ajax_userinfo_by_telno':
                $customer_telno = trim($_POST['customer_telno']);
                $agent_telno = trim($_POST['agent_telno']);
                if(strlen($agent_telno) == 0)
                {
                    $project_id = intval($_POST['project_id']);
                }
                else
                {
                    $project_id = intval($_POST['project_listid']);
                }
                $userinfo = get_userinfo_by_telno($project_id, $customer_telno, $agent_telno);
                echo json_encode($userinfo);
                exit;
                break;
        }

        /*$selected_city_name = isset($_COOKIE['rt_cookie']['city_name']) ? 
                iconv('utf8', 'gbk', strip_tags($_COOKIE['rt_cookie']['city_name'])) : '';
        $selected_city_id = isset($_COOKIE['rt_cookie']['city_id']) ? 
                intval($_COOKIE['rt_cookie']['city_id']) : 0;        
        $selected_project_id = isset($_COOKIE['rt_cookie']['project_id']) ? 
                intval($_COOKIE['rt_cookie']['project_id']) : 0;
        $selected_pro_name = isset($_COOKIE['rt_cookie']['pro_name']) ? 
                iconv('utf8', 'gbk', strip_tags($_COOKIE['rt_cookie']['pro_name'])) : 'ѡ����Ŀ';
        $selected_pro_listid = isset($_COOKIE['rt_cookie']['pro_listid']) ? 
                intval($_COOKIE['rt_cookie']['pro_listid']) : 0; 
       */
        Vendor('Oms.Form');
        $form = new Form();
       // $sql = "select ID,PRO_NAME from ERP_HOUSE where PROJECT_ID ="
          //  . "(select PRO_ID from ERP_PROROLE where USE_ID=".$_SESSION["uinfo"]["uid"]."and ISVALID=-1)";
        $form = $form->initForminfo(111)->setMyField("prjname","LISTSQL",$sql)->getResult();
        $this->assign('form',$form); 
        $this->assign('form_sub_auth_key',$form_sub_auth_key);
        $this->assign('project_listid',$project_listid);
        $this->assign('project_id',$project_id);
        $this->display('arrival_confirm'); 
	}
    
    
    /**
    +----------------------------------------------------------
    * ע����̰쿨��Ա
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function RegMember()
    {   
        $id = !empty($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	
    	//ʵ������ԱMODEL
    	$member_model = D('Member');
        
        /***��ȡ��Ա�쿨����Ʊ����Ʊ״̬***/
        $status_arr = $member_model->get_conf_all_status_remark();
        
    	// �޸�
    	if($this->isPost() && !empty($_POST) &&  $faction == 'saveFormData' 
    			&& $id > 0)
    	{	
    		$member_info = array();
    		$member_info['REALNAME'] = u2g($_POST['REALNAME']);
    		$member_info['MOBILENO'] = $_POST['MOBILENO'];
    		$member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
    		$member_info['SOURCE'] = $_POST['SOURCE'];
    		$member_info['CARDTIME'] = $_POST['CARDTIME'];
    		$member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
    		$member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
    		$member_info['ROOMNO'] = u2g($_POST['ROOMNO']);
    		$member_info['HOUSETOTAL'] = $_POST['HOUSETOTAL'];
    		$member_info['HOUSEAREA'] = $_POST['HOUSEAREA'];
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //���Ϲ�
                    $member_info['subscribetime'] = strtotime(trim($_POST['subscribetime']));
                    $member_info['subscribedate'] = date('Y-m-d',$info['subscribetime']);
                    break;
                case '3':
                    //��ǩԼ
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    break;
                case '4':
                    //�˿�
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    break;
            }
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['SIGNTIME'] = $_POST['SIGNTIME'];
            $member_info['SIGNEDSUITE'] = $_POST['SIGNEDSUITE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            $member_info['RECEIPTNO'] = trim(str_replace(array("��","/","��"),",", $_POST['RECEIPTNO']));
    		$member_info['INVOICE_STATUS'] = $_POST['INVOICE_STATUS'];
    		$member_info['INVOICE_NO'] = $_POST['INVOICE_NO'];
    		$member_info['IS_TAKE'] = $_POST['IS_TAKE'];
    		$member_info['IS_SMS'] = $_POST['IS_SMS'];
    		//$member_info['IS_DISTRIBUTED'] = intval($_POST['IS_DISTRIBUTED']);
            $member_info['TOTAL_PRICE'] =  intval($_POST['TOTAL_PRICE']);
            $member_info['AGENCY_REWARD'] = intval($_POST['AGENCY_REWARD']);
            $member_info['AGENCY_DEAL_REWARD'] = intval($_POST['AGENCY_DEAL_REWARD']);
            //$member_info['PROPERTY_REWARD'] = intval($_POST['PROPERTY_REWARD']);
            $member_info['PROPERTY_DEAL_REWARD'] = intval($_POST['PROPERTY_DEAL_REWARD']);
    		$member_info['NOTE'] = u2g($_POST['NOTE']);
    		$member_info['UPDATETIME'] = date('Y-m-d');
            
    		$update_num = 0;
    		$update_num = $member_model->update_info_by_id($id, $member_info);
    		
    		if($update_num > 0)
    		{   
                if($_POST['CARDSTATUS_OLD'] < $member_info['CARDSTATUS'] && $member_info['CARDSTATUS'] > 2)
                {
                    switch($member_info['CARDSTATUS'])
                    {
                        case '3':
                            $tlfcard_status = 2;
                            $tlfcard_signtime = $member_info['SIGNTIME'];
                            $tlfcard_backtime = 0;
                            break;
                        case '4':
                            $tlfcard_status = 3;
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = $member_info['BACKTIME'];
                            break;
                    }
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $_POST['CITY_ID'];
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                            $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].$member_info['CARDTIME']);
                    $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = $member_info['CARDTIME'];
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {
                        $house_info = M('erp_house')->field('REL_NEWHOUSEID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['REL_NEWHOUSEID']) ? 
                                intval($house_info['REL_NEWHOUSEID']) : '';
                        
                        $crm_api_arr['floor_id'] = $pro_listid;
                    }
                    
                    submit_crm_data_by_api($crm_api_arr);
                }
                
    			$result['status'] = 1;
    			$result['msg'] = 'OK';
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = 'error';
    		}
    		
    		echo json_encode($result);
    		exit;
    	}
    	//����
    	else if ($this->isPost() && !empty($_POST) && $faction == 'saveFormData')
        {   
            $member_info = array();
            $member_info['CITY_ID'] = $_POST['CITY_ID'];
            $member_info['PRJ_ID'] = $_POST['PRJ_ID'];
            $member_info['PRJ_NAME'] =  u2g($_POST['PRJ_NAME']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'ds');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            $member_info['CASE_ID'] = $case_id;
            $member_info['REALNAME'] =  u2g($_POST['REALNAME']);
            $member_info['MOBILENO'] = $_POST['MOBILENO'];
            $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            $member_info['SOURCE'] = $_POST['SOURCE'];
            $member_info['CARDTIME'] = $_POST['CARDTIME'];
            $member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            $member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
            $member_info['ROOMNO'] =  u2g($_POST['ROOMNO']);
            $member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            $member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //���Ϲ�
                    $member_info['subscribetime'] = strtotime(trim($_POST['subscribetime']));
                    $member_info['subscribedate'] = date('Y-m-d',$info['subscribetime']);
                    break;
                case '3':
                    //��ǩԼ
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    break;
                case '4':
                    //�˿�
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    break;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['SIGNTIME'] = $_POST['SIGNTIME'];
            $member_info['SIGNEDSUITE'] = $_POST['SIGNEDSUITE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            $member_info['RECEIPTNO'] = trim(str_replace(array("��","/","��"),",", $_POST['RECEIPTNO']));
            $member_info['INVOICE_STATUS'] = $_POST['INVOICE_STATUS'];
            $member_info['INVOICE_NO'] = $_POST['INVOICE_NO'];
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            $member_info['ATTACH'] = $this->_post('ATTACH');
            $member_info['TOTAL_PRICE'] = intval($_POST['TOTAL_PRICE']);
            $member_info['AGENCY_REWARD'] = intval($_POST['AGENCY_REWARD']);
            $member_info['AGENCY_DEAL_REWARD'] = intval($_POST['AGENCY_DEAL_REWARD']);
            $member_info['PROPERTY_DEAL_REWARD'] = intval( $_POST['PROPERTY_DEAL_REWARD']);
            $member_info['ADD_UID'] = intval($_SESSION['uinfo']['uid']);
            $member_info['NOTE'] =  u2g($_POST['NOTE']);
            $member_info['CREATETIME'] = date('Y-m-d');
            
            /****�Ƿ���Ҫ����ȷ��****/
            $is_crm_confirm = intval($_POST['is_crm_confirm']);
            $is_fgj_confirm = intval($_POST['is_fgj_confirm']);
            if($is_crm_confirm == 1 || $is_fgj_confirm == 1)
            {
                //�ͻ�ID
                $customer_id = intval($_POST['customer_id']);
                //��֤��
                $code = strip_tags($_POST['code']);
                //������Դ
                $is_from = strip_tags($_POST['is_from']);
                //����ȷ��
                if($is_from == 1 && $is_crm_confirm == 1)
                {
                    $result = arrival_confirm_crm($customer_id , $code);
                }
                else if($is_from == 2 && $is_fgj_confirm == 1)
                {   
                    //������ID
                    $ag_id = intval($_POST['ag_id']);
                    //����ID
                    $cp_id = intval($_POST['cp_id']);
                    //���в���
                    $user_city_py = $_SESSION['uinfo']['city'];
                    $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($member_info['PRJ_ID'], 
                    		$member_info['MOBILENO'], $user_city_py);
                    if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 && 
                            !empty($userinfo_crm_arr['meminfo']))
                    {   
                        if( $userinfo_crm_arr['meminfo']['codestatus'] != 1 )
                        {
                            //CRM���û�ID
                            $customer_id_crm = 0;
                            $customer_id_crm = $userinfo_crm_arr['meminfo']['pmid'];
                            $up_result = update_crm_user_source($customer_id_crm , 5);
                        }
                    }

                    $result = arrival_confirm_fgj($customer_id , $ag_id , $cp_id);
                }

                //��¼��־
                $is_sucess = intval($result['result']);
                $msg = $is_sucess == 1 ? '����ȷ�ϳɹ�' : '��֤ʧ�ܣ���֤����Ч���ѹ���';
                arrival_confirm_log($customer_id, $member_info['REALNAME'], $member_info['MOBILENO'], 
                		$code, $_POST['LIST_ID'], $member_info['PRJ_ID'], $is_from, $is_sucess);
            }
            /****�Ƿ���Ҫ����ȷ��****/
            
            $insert_id = $member_model->add_member_info($member_info);
            if($insert_id > 0)
            {
                //���Ͷ���
                if(isset($member_info['IS_SMS']) && $member_info['IS_SMS'] == 2 
                    && $member_info['CARDSTATUS'] < 4)
                {   
                    $msg = "�𾴵�365��Ա".$member_info['REALNAME']."��".
                           "���ѳɹ�֧����Ϣ�����".$member_info['PAID_MONEY']."Ԫ���ͷ�����400-8181-365";

                    send_sms($msg, $member_info['MOBILENO'], $this->channelid);
                }
                
                //crm 
                if($member_info['CARDSTATUS'])
                {
                    switch($member_info['CARDSTATUS'])
                    {
                        case '1':
                        case '2':
                        $tlfcard_status = 1;
                        $tlfcard_signtime = 0;
                        $tlfcard_backtime = 0;
                        break;
                        case '3':
                            $tlfcard_status = 2;
                            $tlfcard_signtime = $member_info['SIGNTIME'];
                            $tlfcard_backtime = 0;
                        break;
                        case '4':
                            $tlfcard_status = 3;
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = $member_info['BACKTIME'];
                        break;
                    }
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $_POST['CITY_ID'];
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                            $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].$member_info['CARDTIME']);
                    $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = $member_info['CARDTIME'];
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {   
                        $house_info = M('erp_house')->field('REL_NEWHOUSEID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['REL_NEWHOUSEID']) ? 
                                intval($house_info['REL_NEWHOUSEID']) : '';
                        
                        $crm_api_arr['floor_id'] = $pro_listid;
                    }
                    
                    submit_crm_data_by_api($crm_api_arr);
                }
                
                $result['status'] = 2;
                $result['msg'] = '��ӻ�Ա�ɹ�';
            }
			else
            {	
            	$result['status'] = 0;
            	$result['msg'] = '��ӻ�Աʧ�ܣ�';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else
        {
            Vendor('Oms.Form');
            $form = new Form();
            $form = $form->initForminfo(103)->where("CITY_ID = '".$this->channelid."'");
            
            //����֤������
            $certificate_type_arr = $member_model->get_conf_certificate_type();
            $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR', 
            		array2listchar($certificate_type_arr), FALSE);
            
            //���ø��ʽ
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', 
	        		array2listchar($pay_arr), FALSE);
	        
	        //�쿨״̬
	        $form = $form->setMyField('CARDSTATUS', 'LISTCHAR', 
	        		array2listchar($status_arr['CARDSTATUS']), FALSE);
	        
	        //��Ʊ״̬
	        $form = $form->setMyField('INVOICE_STATUS', 'LISTCHAR', 
	        		array2listchar($status_arr['INVOICE_STATUS']), FALSE);
	        
	        //�վ�״̬
	        $form = $form->setMyField('RECEIPTSTATUS', 'LISTCHAR', 
	        		array2listchar($status_arr['RECEIPTSTATUS']), FALSE);
            
            //��ҳ��
            if($_GET['showForm'] == '1')
            {   
                //�޸ļ�¼ID
                $modify_id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;
                
                //���þ�������Ϣ
                $userinfo = array();
                $uid = intval($_SESSION['uinfo']['uid']);
                $username = strip_tags($_SESSION['uinfo']['tname']);
                $form = $form->setMyFieldVal('ADD_USERNAME', $username, TRUE);
                
                if($modify_id > 0)
                {	
                	$search_field = array('CASE_ID');
                	$userinfo = $member_model->get_info_by_id($modify_id, $search_field);
                	
                    //�����շѱ�׼
                    $case_id =  !empty($userinfo['CASE_ID']) ? intval($userinfo['CASE_ID']) : 0;
                    $feescale = array();
                    $project = D('Project');
                    $feescale = $project->get_feescale_by_cid($case_id);

                    $fees_arr = array();
                    if(is_array($feescale) && !empty($feescale) )
                    {
                        foreach($feescale as $key => $value)
                        {
                            $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'];
                        }

                        //�����շѱ�׼
                        $form = $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //�н�Ӷ��
                        $form = $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
                        //��ҵ����Ӷ��
                        $form = $form->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //�н�ɽ���
                        $form = $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //��ҵ�ɽ�����
                        $form = $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
                    }
                    $member_model = D('Member');
                    $member_info = $member_model->get_info_by_id($modify_id, array('CITY_ID', 'PRJ_ID'));
                    
                    if(is_array($member_info) && !empty($member_info))
                    {
                        $input_arr = array(
                                array('name' => 'CITY_ID', 'val' => $member_info['CITY_ID'], 'id' => 'CITY_ID'),
                                array('name' => 'PRJ_ID', 'val' => $member_info['PRJ_ID'], 'id' => 'PRJ_ID'),
                                );
                        $form = $form->addHiddenInput($input_arr);
                    }
                }
                else
                {	
                	//����ҳ���������
                    $input_arr = array(
                            array('name' => 'ADD_UID', 'val' => $userinfo['ID'], 'class' => 'ADD_UID'),
                            array('name' => 'is_fgj_confirm', 'val' => '', 'id' => 'is_fgj_confirm'),
                            array('name' => 'is_crm_confirm', 'val' => '', 'id' => 'is_crm_confirm'),
                            array('name' => 'code', 'val' => '', 'id' => 'code'),
                            array('name' => 'ag_id', 'val' => '', 'id' => 'ag_id'),
                            array('name' => 'cp_id', 'val' => '', 'id' => 'cp_id'),
                            array('name' => 'is_from', 'val' => '', 'id' => 'is_from'),
                            array('name' => 'customer_id', 'val' => '', 'id' => 'customer_id'),
                            array('name' => 'multi_user_to_jump', 'val' => '', 'id' => 'multi_user_to_jump'),
                            array('name' => 'multi_from_to_jump', 'val' => '', 'id' => 'multi_from_to_jump'),
                            );
                    $form = $form->addHiddenInput($input_arr);
                }
            }
            else 
            {	
            	/***�б�ҳ��������***/
            	//�����
                $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                //�����շѱ�׼
                $form = $form->setMyField('TOTAL_PRICE', 'EDITTYPE',"1", TRUE);
                //����ȷ��״̬
                $form = $form->setMyField('FINANCIALCONFIRM', 'LISTCHAR', array2listchar($status_arr['FINANCIALCONFIRM']), FALSE);
            }
            
            //��״̬��ɫarray('1','BSTATUS') 1Ϊ���Ͷ�ӦERP_STATUS_TYPE��
            //BSTATUSΪ��Ҫ����ɫ���ֶ���
            $arr_param = array(
                            array('2','CARDSTATUS') , 
                            array('3','RECEIPTSTATUS'), 
                            array('4','INVOICE_STATUS'), 
                            array('5','FINANCIALCONFIRM')
                        );
            $form = $form->showStatusTable($arr_param);
            $children_data = array(
                                array('������ϸ', U('/Member/show_pay_list')),
                                array('�˿��¼', U('/Member/show_refund_list')),
                                array('��Ʊ��¼', U('/Member/show_bill_list'))
                            );
            $formhtml =  $form->setChildren($children_data)->getResult();
            $this->assign('form',$formhtml);
            $this->display('reg_member'); 
        }
    }
    
    
    /**
    +----------------------------------------------------------
    * ��ʾ������ϸ�б�
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function show_pay_list()
    {   
        $mid = isset($_POST['MID']) ? intval($_POST['MID']) : 0;
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $uid = intval($_SESSION['uinfo']['uid']);
		
        //���֧����Ϣ
        if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id == 0)
        {   
            $member_paymet = M('erp_member_payment');
            
            $pay_info = array();
            $pay_info['MID'] = $this->_post('MID');
            $pay_info['REAL_NAME'] = $this->_post('REAL_NAME');
            $pay_info['PAY_TYPE'] = $this->_post('PAY_TYPE');
            $pay_info['TRADE_MONEY'] = $this->_post('TRADE_MONEY');
            $pay_info['ORIGINAL_MONEY'] = $pay_info['TRADE_MONEY'];
            $pay_info['RETRIEVAL'] = $this->_post('RETRIEVAL');
            $pay_info['CVV2'] = $this->_post('CVV2');
            $pay_info['MERCHANT_NUMBER'] = $this->_post('MERCHANT_NUMBER');
            $pay_info['TRADE_TIME'] = $this->_post('TRADE_TIME');
            $pay_info['ADD_UID'] = $uid;
            
            $insert_id = $member_paymet->add($pay_info);
            
            if($insert_id > 0)
            {	
            	//���»�Ա�ѽ��ɺ�δ���ɽ��
            	$member_pay = D('MemberPay');
            	$paid_money = $member_pay->get_sum_pay($mid);
            	
            	//��ѯ��Ա��Ϣ
            	$member_model = D('Member');
            	$member_info = array();
            	$search_field = array('TOTAL_PRICE','REDUCE_MONEY');
            	$member_info = $member_model->get_info_by_id($mid, $search_field);
            	
            	//������
				$reduce_money = !empty($member_info['REDUCE_MONEY']) ? 
									floatval($member_info['REDUCE_MONEY']) : 0;
				//�����շѱ�׼
				$total_price = !empty($member_info['TOTAL_PRICE']) ? 
                                    floatval($member_info['TOTAL_PRICE']) : 0;
                
            	//֧�����֧�����͸���Ϊ�ۺ�
            	$paid_money > $pay_info['TRADE_MONEY'] ? $update_arr['PAY_TYPE'] = 4 : '';
            	$update_arr['PAID_MONEY'] = $paid_money;
            	$update_arr['UNPAID_MONEY'] = $total_price > 0 ? 
                                              $total_price - $paid_money - $reduce_money : 0;
            	$member_model->update_info_by_id($mid, $update_arr);
                
                //���������Ϣ�������
                $member_info = $member_model->get_info_by_id($mid, array('CASE_ID'));
                $income_info['CASE_ID'] = $member_info['CASE_ID'];
                $income_info['ENTITY_ID'] = $mid;
                $income_info['PAY_ID'] = $insert_id;
                $income_info['INCOME_FROM'] = 1;//���̻�Ա֧��
                $income_info['INCOME'] = $pay_info['TRADE_MONEY'];
                $income_info['INCOME_REMARK'] = '';
                $income_info['ADD_UID'] = $uid;
                $income_info['OCCUR_TIME'] = $pay_info['TRADE_TIME'];
                
                $income_model = D('ProjectIncome');
                $income_model->add_income_info($income_info);
                
                $result['status'] = 1;
                $result['msg'] = '��ӳɹ�';
            }
			else
            {	
            	$result['status'] = 0;
            	$result['msg'] = '���ʧ�ܣ�';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id > 0)
        {	
            //�޸�֧����Ϣ
            $member_paymet = M('erp_member_payment');
            
            $pay_info = array();
            $pay_info['MID'] = $this->_post('MID');
            $pay_info['REAL_NAME'] = $this->_post('REAL_NAME');
            $pay_info['PAY_TYPE'] = $this->_post('PAY_TYPE');
            $pay_info['TRADE_MONEY'] = $this->_post('TRADE_MONEY');
            $pay_info['ORIGINAL_MONEY'] = $pay_info['TRADE_MONEY'];
            $pay_info['RETRIEVAL'] = $this->_post('RETRIEVAL');
            $pay_info['CVV2'] = $this->_post('CVV2');
            $pay_info['MERCHANT_NUMBER'] = $this->_post('MERCHANT_NUMBER');
            $pay_info['TRADE_TIME'] = $this->_post('TRADE_TIME');
            
            $up_num = $member_paymet->where("ID = '".$id."'")->save($pay_info);
            
            if($up_num > 0)
            {	
            	//���»�Ա�ѽ��ɺ�δ���ɽ��
            	$member_pay = D('MemberPay');
            	$paid_money = $member_pay->get_sum_pay($mid);
            	 
            	//��ѯ��Ա��Ϣ
            	$member_model = D('Member');
            	$member_info = array();
            	$search_field = array('TOTAL_PRICE','REDUCE_MONEY');
            	$member_info = $member_model->get_info_by_id($mid, $search_field);
            	 
            	//������
            	$reduce_money = !empty($member_info['REDUCE_MONEY']) ?
            						floatval($member_info['REDUCE_MONEY']) : 0;
            	
            	//�����շѱ�׼
            	$total_price = !empty($member_info['TOTAL_PRICE']) ? 
            						floatval($member_info['TOTAL_PRICE']) : 0;
            	
            	//֧�����֧�����͸���Ϊ�ۺ�
            	$paid_money > $pay_info['TRADE_MONEY'] ? $update_arr['PAY_TYPE'] = 4 : '';
            	
            	$update_arr['PAID_MONEY'] = $paid_money;
            	$update_arr['UNPAID_MONEY'] = $total_price - $paid_money - $reduce_money;
            	$member_model->update_info_by_id($mid, $update_arr);
            	
                $result['status'] = 1;
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] = '�޸�ʧ�ܣ�';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else 
        {
            Vendor('Oms.Form');
            $form = new Form();
            
            $form = $form->initForminfo(121);
                    
            $member = D('Member');
            $m_id = intval($this->_get('parentchooseid'));
            
            $member_info = array();
            $member_info = $member->get_info_by_id($m_id, array('CITY_ID', 'REALNAME'));
            $member_name = !empty($member_info['REALNAME']) ? $member_info['REALNAME'] : '';
            $form = $form->setMyFieldVal('REAL_NAME', $member_name, 0);
            
            //���ø��ʽ
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), FALSE);
	        
	        //�����̻����
	        $merchant_arr = array();
	        $city_id = !empty($member_info['CITY_ID']) ? intval($member_info['CITY_ID']) : 0;
	        $merchant_info = array();
	        $merchant_info = M('erp_merchant')->where("CITY_ID = '".$city_id."'")->select();
	        if(is_array($merchant_info) && !empty($merchant_info))
	        {
	        	foreach($merchant_info as $key => $value)
	        	{	
	        		$large_str = '';
	        		$value['IS_LARGE'] == 1 ? $large_str .= '[���]' : '';
	        		$merchant_arr[$value['MERCHANT_NUMBER']] = $value['MERCHANT_NUMBER'].$large_str;
	        	}
	        }
	        $form = $form->setMyField('MERCHANT_NUMBER', 'LISTCHAR', array2listchar($merchant_arr), FALSE);
            $form = $form->getResult();
            $this->assign('form',$form);
            $this->display('pay_list'); 
        }
    }
    
    
    /**
    +----------------------------------------------------------
    * ��ӻ�Ա������ϸ��Ϣ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function add_pay_info()
    { 
        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(121)->getResult();
        $this->assign('form',$form);
        $this->display('add_pay'); 
    }
    
    
    /**
    +----------------------------------------------------------
    * ��ʾ�˿���ϸ�б�
    +----------------------------------------------------------
    * @param none
     * 
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function show_refund_list()
    {	
    	$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	$showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
    	$member_refund = D('MemberRefund');
    	
    	//�޸��˿�����
    	if($showForm == 1 &&  $faction == 'saveFormData' && $id > 0)
    	{	
    		$up_num = 0;
    		$refund_money = isset($_POST['REFUND_MONEY']) ? 
    							intval($_POST['REFUND_MONEY']) : 0;
    		if($refund_money > 0)
    		{
    			$update_arr['REFUND_MONEY'] = $refund_money;
    			$up_num = $member_refund->update_refund_detail_by_id($id, $update_arr);
    		}
    		
    	    if($up_num > 0)
            {
                $this->success('�޸ĳɹ���',U('Member/show_refund_list'));
            }
            else
            {
                $this->error('�޸�ʧ�ܣ�');
            }
            exit;
    	}
        else if($faction == 'delData')
        {   
            $del_id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
            $up_num = $member_refund->del_refund_detail_by_id($del_id);

            $result['status'] = $up_num > 0 ? 'success' : 'error';
            echo json_encode($result);
            exit;
        }
    	else
    	{
	        Vendor('Oms.Form');
	        $form = new Form();
	        
	        $form = $form->initForminfo(122);
	        //���û�Ա���
	        $form = $form->setMyField('MID','LISTSQL','SELECT ID,REALNAME FROM ERP_CARDMEMBER', TRUE);
	        //���ø��ʽ
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);
	        //���ò�����
	        $form = $form->setMyField('APPLY_UID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);
	        
	        //��ȡ�˿���ϸ�˿�״̬
	        $refund_status_remark_arr = array();
	        $refund_status_remark_arr = $member_refund->get_conf_refund_status_remark();
	        $form = $form->setMyField('REFUND_STATUS', 'LISTCHAR', 
	        		array2listchar($refund_status_remark_arr), TRUE);
            
	        $form = $form->getResult();
	       	
	        $this->assign('form',$form);
	        $this->display('refund_list');
    	}
    }
    
    
    /**
    +----------------------------------------------------------
    * ��ʾ��Ʊ��¼�б�
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function show_bill_list()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(123);
        //���û�Ա���
	    $form = $form->setMyField('MID','LISTSQL','SELECT ID,REALNAME FROM ERP_CARDMEMBER', TRUE);
        $form = $form->getResult();
        $this->assign('form',$form);
        $this->display('bill_list');
    }
    
    
    /**
    +----------------------------------------------------------
    * ���뿪Ʊ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function apply_invoice ()
    {
        $info = array();
        $member_obj = D('Member');
        $id_arr = $_POST['memberId'];
        $info['state']  = 0;
        if(is_array($id_arr) && !empty($id_arr))
        {
            foreach ($id_arr as $mid)
            {   
                $mid = intval($_id);
                $member_info = array();
                $search_field = array(`CITY_ID`,`REALNAME`,`CARDSTATUS`,`INVOICE_STATUS`,`ROOMNO`);
                $member_info = $member_obj->get_info_by_id($mid, $search_field);
                
                if(is_array($member_info) && !empty($member_info))
                {
                    #�Ͼ����������
                    if($member_info['CITY_ID'] == 1)
                    {
                        if($member_info['CARDSTATUS'] != 3 || 
                            $member_info['CARDSTATUS'] != 1 || trim($member_info['ROOMNO']) == '')
                        {
                            $info['realname'] .= $member_info['REALNAME'] . "��";
                            $info['msg'] = '�����Ͽ�Ʊ״̬������쿨״̬����Ʊ״̬��¥������[����]��';
                        }
                    }
                    else
                    {
                        if($member_info['CARDSTATUS'] != 3 || $member_info['INVOICE_STATUS'] != 1)
                        {
                            $info['realname'] .= $member_info['REALNAME'] . "��";
                            $info['msg'] = '�����Ͽ�Ʊ״̬������쿨״̬����Ʊ״̬��';
                        }
                    }
                }
            }
            $info['realname'] = rtrim($info['realname'],"��");

            if(!$info['realname'])
            {   
                $result = FALSE;
                $result = $member_obj->update_info_by_id($id_arr, array('INVOICE_STATUS' => '2'));
                if( $result > 0)
                {
                    $info['state']  = 1;
                    $info['msg']  = '��Ʊ����ɹ�';
                }
                else
                {
                    $info['msg']  = '��Ʊʧ��!';
                }
            }
        }
        else
        {
            $info['msg']  = '������ѡ��һ����¼!';
        }

        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
    }
    
    
    /**
    +----------------------------------------------------------
    * �����ֻ������ȡ�û���CRM/FGJϵͳ�е���Ϣ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function get_minfo_by_telno()
    {
        //ajax�����ֻ���¥�̱�Ż�ȡ�û���Ϣ
        if(isset($_POST['action_type']) && $_POST['action_type'] == 'ajax_userinfo_by_telno')
        {   
            $project_id = intval($_POST['project_id']);
            $telno = strip_tags($_POST['telno']);
            $pro_listid = isset($_POST['pro_listid']) ? intval($_POST['pro_listid']) : 0;
            
            $cond_where = "ID = ".intval($_SESSION['uinfo']['city']);
            $city_info = M('erp_city')->field('PY')->where($cond_where)->find();
            $user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';
            $userinfo = get_userinfo_by_pid_telno($project_id, $telno, $pro_listid, $user_city_py);

            echo json_encode($userinfo);
            exit;
        }
    }
    
    
    /**
     +----------------------------------------------------------
     * �ֳ������ֽ�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function locale_granted()
    {   
        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(169);
            
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = strip_tags($_SESSION['uinfo']['tname']);
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	$showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        
        $grant_model = D('LocaleGranted');
        
    	if($showForm == 1 && $faction == 'saveFormData' && $id > 0)
    	{	
            $grant_info = array();
            $grant_info['PRJ_ID'] = intval($_POST['PRJ_ID']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($grant_info['PRJ_ID'], 'ds');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            if($case_id <= 0)
    		{
    			$result['status'] = 0;
    			$result['msg'] = g2u('��Ŀ�޵���ҵ���޷�����ֳ����ż�¼');
                echo json_encode($result);
                exit;
    		}
            $grant_info['CASE_ID'] = $case_id;
            $grant_info['CITY_ID'] = intval($_POST['CITY_ID']);
            $grant_info['MONEY'] = floatval($_POST['MONEY']);
            $grant_info['NUM'] = floatval($_POST['NUM']);
            $grant_info['ISFUNDPOOL'] = intval($_POST['ISFUNDPOOL']);
            $grant_info['OCCUR_TIME'] = $_POST['OCCUR_TIME'];
            $grant_info['ATTACHMENTS'] = $_POST['ATTACHMENTS'];
            $grant_info['UPDATETIME'] = date('Y-m-d H:i:s');
            
            $update_num = $grant_model->update_info_by_id($id, $grant_info);
            
            if($update_num > 0)
    		{
    			$result['status'] = 1;
    			$result['msg'] = 'OK';
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = 'error';
    		}
    		
    		echo json_encode($result);
    		exit;
        }
        else if($showForm == 1 && $faction == 'saveFormData' && $id == 0)
        {   
            $grant_info = array();
            $grant_info['PRJ_ID'] = intval($_POST['PRJ_ID']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($grant_info['PRJ_ID'], 'ds');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            if($case_id <= 0)
    		{
    			$result['status'] = 0;
    			$result['msg'] = g2u('��Ŀ�޵���ҵ���޷�����ֳ����ż�¼');
                echo json_encode($result);
                exit;
    		}
            $grant_info['CASE_ID'] = $case_id;
            $grant_info['CITY_ID'] = intval($_POST['CITY_ID']);
            $grant_info['MONEY'] = floatval($_POST['MONEY']);
            $grant_info['NUM'] = floatval($_POST['NUM']);
            $grant_info['ADD_UID'] = $uid;
            $grant_info['ISFUNDPOOL'] = intval($_POST['ISFUNDPOOL']);
            $grant_info['OCCUR_TIME'] = $_POST['OCCUR_TIME'];
            //$grant_info['ATTACHMENTS'] = $_POST['ATTACHMENTS'];
            $grant_info['CREATTIME'] = date('Y-m-d H:i:s');
            
            
            $insert_id = $grant_model->add_grant_info($grant_info);
            
            if($insert_id > 0)
    		{
    			$result['status'] = 1;
    			$result['msg'] = 'OK';
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = 'error';
    		}
    		
    		echo json_encode($result);
    		exit;
        }
        else if($showForm == 1 && $faction == 'delData')
        {
            
        }
        else 
        {  
            //����ҳ
            if($showForm == 1)
            {   
                $modify_id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
                
                //�༭ҳ����Ŀ������ʾ�����в�������ĿID���أ�
                if( $modify_id > 0 )
                {   
                    $serach_field = array('PRJ_ID', 'CITY_ID');
                    $grand_info = $grant_model->get_info_by_id($modify_id, $serach_field);
                    
                    if(is_array($grand_info) && !empty($grand_info))
                    {
                        $prj_id = intval($grand_info[0]['PRJ_ID']);
                        $city_id = intval($grand_info[0]['CITY_ID']);
                        
                        $input_arr = array(
                            array('name' => 'PRJ_ID', 'val' => $prj_id, 'id' => 'PRJ_ID'),
                            array('name' => 'CITY_ID', 'val' => $city_id, 'id' => 'CITY_ID'),
                        );
                        $form =  $form->addHiddenInput($input_arr);
                        
                        $project_info = array();
                        $project_model = D('Project');         
                        $project_info = $project_model->get_info_by_id($prj_id);
                        if(is_array($project_info) && !empty($project_info))
                        {
                            $form->setMyFieldVal('PRJ_NAME', $project_info[0]['PROJECTNAME']);
                        }
                    }
                }
                
                //������
                $form = $form->setMyField('ADD_UID', 'EDITTYPE', 22);
                $form = $form->setMyField('ADD_UID', 'LISTCHAR', array2listchar(array($uid => $username)), TRUE);
                $form = $form->setMyFieldVal('ADD_UID', $uid);
            }
            else
            {
                //��Ŀ����
                $form = $form->setMyField('PRJ_ID', 'LISTSQL', "SELECT ID, PROJECTNAME FROM ERP_PROJECT", TRUE);
                
                //������
                $form = $form->setMyField('ADD_UID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
            }
        }
        
        $form = $form->getResult();
        $this->assign('form', $form);
        $this->display('locale_granted');
    }
    
    
    /**
     +----------------------------------------------------------
     * �̻���Ź���
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function merchant_manage()
    {
    	Vendor('Oms.Form');
    	$form = new Form();
    	$form = $form->initForminfo(147);
    	//���ó���
    	$form = $form->setMyField('CITY_ID','LISTSQL','SELECT ID,NAME FROM ERP_CITY WHERE ISVALID = -1', FALSE);
    	$form = $form->getResult();
    	$this->assign('form', $form);
    	$this->display('merchant_manage');
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
    public function export_member()
    {
        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '��Ա����';
        $objPHPExcel = new PHPExcel();          
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//Ĭ���п�
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//Ĭ���п�
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'����'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);   
        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'���'));
        $objActSheet->setCellValue('B1', iconv("gbk//ignore","utf-8//ignore",'��Ա����'));
        $objActSheet->setCellValue('C1', iconv("gbk//ignore","utf-8//ignore",'�������ֻ���'));
        $objActSheet->setCellValue('D1', iconv("gbk//ignore","utf-8//ignore",'¥������'));
        $objActSheet->setCellValue('E1', iconv("gbk//ignore","utf-8//ignore",'�쿨����'));
        $objActSheet->setCellValue('F1', iconv("gbk//ignore","utf-8//ignore",'�쿨״̬'));
        $objActSheet->setCellValue('G1', iconv("gbk//ignore","utf-8//ignore",'�վ�״̬'));
        $objActSheet->setCellValue('H1', iconv("gbk//ignore","utf-8//ignore",'��Ʊ״̬'));
        $objActSheet->setCellValue('I1', iconv("gbk//ignore","utf-8//ignore",'����ȷ��״̬'));
        $objActSheet->setCellValue('J1', iconv("gbk//ignore","utf-8//ignore",'������'));
        $objActSheet->setCellValue('K1', iconv("gbk//ignore","utf-8//ignore",'֤������'));
        $objActSheet->setCellValue('L1', iconv("gbk//ignore","utf-8//ignore",'֤������'));
        $objActSheet->setCellValue('M1', iconv("gbk//ignore","utf-8//ignore",'���ʽ'));
        $objActSheet->setCellValue('N1', iconv("gbk//ignore","utf-8//ignore",'�ѽɽ��'));
        $objActSheet->setCellValue('O1', iconv("gbk//ignore","utf-8//ignore",'δ���ɽ��'));
        $objActSheet->setCellValue('P1', iconv("gbk//ignore","utf-8//ignore",'�����շѱ�׼'));
        $objActSheet->setCellValue('Q1', iconv("gbk//ignore","utf-8//ignore",'�վݱ��'));
        
        $result = array();
        $member = D('Member');
        $cond_where = "ID > 0";
        $result = $member->get_info_by_cond($cond_where);
        
        if(is_array($result))
        {
            $i = 2;
            foreach($result as $k => $r)
            {
                $objActSheet->setCellValue('A'.$i, $r['ID']);
                $realname = iconv("gbk//ignore", "utf-8//ignore", $r['REALNAME']);
                $objActSheet->setCellValue('B'.$i, $realname);
                $objActSheet->setCellValue('C'.$i, " ".$r['MOBILENO']);
                $objActSheet->setCellValue('D'.$i, $r['ROOMNO']);
                $card_time = oracle_date_format($r['CARDTIME']);
                $objActSheet->setCellValue('E'.$i, $card_time);
                $objActSheet->setCellValue('F'.$i, $r['CARDSTATUS']);
                $objActSheet->setCellValue('G'.$i, $r['INVOICE_STATUS']);
                $objActSheet->setCellValue('H'.$i, $r['RECEIPTSTATUS']);
                $objActSheet->setCellValue('I'.$i, $r['FINANCIALCONFIRM']);
                $objActSheet->setCellValue('J'.$i, $r['ADD_UID']);
                $objActSheet->setCellValue('K'.$i, $r['CERTIFICATE_TYPE']);
                $objActSheet->setCellValue('L'.$i, " ".$r['CERTIFICATE_NO']);
                $objActSheet->setCellValue('M'.$i, $r['PAY_TYPE']);
                $objActSheet->setCellValue('N'.$i, $r['PAID_MONEY']);
                $objActSheet->setCellValue('O'.$i, $r['UNPAID_MONEY']);
                $objActSheet->setCellValue('P'.$i, $r['TOTAL_PRICE']);
                $objActSheet->setCellValue('Q'.$i, $r['RECEIPTNO']);
                $objActSheet->getRowDimension($i)->setRowHeight(24);
                $i++;
            }
        }
        
        ob_end_clean();
        ob_start();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');               
        exit;
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
    public function import_member()
    {
        if($_FILES)
        {                      
            $file = $_FILES["upfile"]["tmp_name"];
            Vendor('phpExcel.PHPExcel');
            Vendor('phpExcel.IOFactory.php');
            Vendor('phpExcel.Reader.Excel5.php');
            $PHPExcel = new PHPExcel();
            $PHPReader = new PHPExcel_Reader_Excel2007();
            
            if(!$PHPReader->canRead($file))
            {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if(!$PHPReader->canRead($file))
                {
                    echo 'no Excel';
                    return ;
                }
            }            
            $PHPExcel = $PHPReader->load($file);
            /**��ȡexcel�ļ��еĵ�һ��������*/
            $currentSheet = $PHPExcel->getSheet(0);
            /**ȡ�������к�*/
            $allColumn = $currentSheet->getHighestColumn();
            /**ȡ��һ���ж�����*/
            $allRow = $currentSheet->getHighestRow();
            /**�ӵڶ��п�ʼ�������Ϊexcel���е�һ��Ϊ����*/
        }
        else
        {
            
        }
    }    
 }