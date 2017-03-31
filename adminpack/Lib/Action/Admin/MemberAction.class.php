
<?php
class MemberAction extends ExtendAction{
    /**
     * �����˿�Ȩ��
     */
    const REFUND_BY_MID = 183;

    /**
     * ���뿪ƱȨ��
     */
    const APPLY_INVOICE = 611;

    /**
     * �������Ȩ��
     */
    const APPLY_DISCOUNT = 290;

    /**
     * ������ƱȨ��
     */
    const RECYCLE_INVOICE = 302;

    /**
     * ���뻻��ƱȨ��
     */
    const CHANGE_INVOICE = 385;

    /**
     * �н�Ӷ����Ȩ��
     */
    const AGENCY_REWARD_REIM = 613;

    /**
     * �н�ɽ���������Ȩ��
     */
    const AGENCY_DEAL_REWARD_REIM = 715;

    /**
     * ��ҵ�ɽ���������Ȩ��
     */
    const PROPERTY_DEAL_REWARD_REIM = 716;

    /**
     * ��Ա����Ȩ��
     */
    const DOWNLOAD_MEMBER = 608;

    /**
     * ��Ա����Ȩ��
     */
    const IMPORT_MEMBER = 609;

    /**
     * �鿴��ԱȨ��
     */
    const VIEW_MEMBERINFO = 377;

    /**
     * ������״̬Ȩ��
     */
    const BATCH_CHANGE_STATUS = 614;


    /**
     * �ֽ𷢷��౨��
     */
    const CASH_PAYMENT_REIM = 7;

    /**
     * �����˿�Ȩ��
     */
    const REFUND_BY_DETAILS = 610;

    /**
     * ���뱨��Ȩ��
     */
    const LOCALE_GRANTED_REIM = 640;

    /**
     * �ύ��������Ȩ��
     */
    const SUB_REIM_APPLY = 641;

    /**
     * �������Ȩ��
     */
    const RELATED_MY_LOAN = 642;

    /**
     * ��ѯ���ʱ�����������FlowID��SQL
     */
    const PAYOUT_FLOWID_SQL = <<<PAYOUT_FLOWID_SQL
        SELECT ID
        FROM ERP_FLOWS T
        WHERE T.FLOWSETID = 35
          AND T.RECORDID = %d
PAYOUT_FLOWID_SQL;

    private $showPayListOptions = array(
        '_add' => array(
            'default' => 628,
        ),
        '_check' => array(
            'default' => 629
        ),
        '_edit' => array(
            'default' => 713
        ),
        '_del' => array(
            'default' => 714
        )
    );

    private $showRefundListOptions = array(
        '_check' => array(
            'default' => 632
        ),
        '_edit' => array(
            'default' => 631
        ),
        '_del' => array(
            'default' => 633
        )
    );

    private $showBillListOptions = array(
        '_check' => array(
            'default' => 635
        )
    );

    private $localeGrantedOptions = array(
        '_add' => array(
            'default' => 636,
        ),
        '_check' => array(
            'default' => 638
        ),
        '_edit' => array(
            'default' => 637
        ),
        '_del' => array(
            'default' => 639
        )
    );

    private $uid;
    private $city_id;
    
    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();
    
    /**��ҳǩ���**/
    private $_tab_number = 22;
    
    //���캯��
    public function __construct() 
    {	
        parent::__construct();
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'refund_by_mid' => self::REFUND_BY_MID,
            'apply_invoice' => self::APPLY_INVOICE,
            'apply_discount' => self::APPLY_DISCOUNT,
            'recycle_invoice' => self::RECYCLE_INVOICE,
            'change_invoice' => self::CHANGE_INVOICE,
            'agency_reward_reim' => self::AGENCY_REWARD_REIM,
            'agency_deal_reward_reim' => self::AGENCY_DEAL_REWARD_REIM,
            'property_deal_reward_reim' => self::PROPERTY_DEAL_REWARD_REIM,
            'download_member' => self::DOWNLOAD_MEMBER,
            'import_member' => self::IMPORT_MEMBER,
            'view_memberinfo' => self::VIEW_MEMBERINFO,
            'batch_change_status' => self::BATCH_CHANGE_STATUS,
            'refund_by_details' => self::REFUND_BY_DETAILS,
            'locale_granted_reim' => self::LOCALE_GRANTED_REIM,
            'sub_reim_apply' => self::SUB_REIM_APPLY,
            'related_my_loan' => self::RELATED_MY_LOAN,
        );
        //���ػ�Աģ�鹫�ú����ļ�
        load("@.member_common");

        //����ID
        $this->city_id = intval($_SESSION['uinfo']['city']);
        //�û�ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //�û�����
        $this->uname = trim($_SESSION['uinfo']['uname']);
        //�û�����
        $this->tname = trim($_SESSION['uinfo']['tname']);
        //���м��
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);
        
        $this->_merge_url_param['TAB_NUMBER'] = intval($this->_tab_number) ;
    }

    public function main() {
        $this->_merge_url_param['TAB_NUMBER'] = 22;
        if (!empty($this->_merge_url_param['TAB_NUMBER'])) {
            $hasTabAuthority = $this->checkTabAuthority($this->_merge_url_param['TAB_NUMBER']);
            if ($hasTabAuthority['result']) {
                $roleInfo = D('erp_role')->where("LOAN_ROLEID = {$hasTabAuthority['roleID']}")->find();
                $url = U("{$roleInfo['LOAN_ROLECONTROL']}/{$roleInfo['LOAN_ROLEACTION']}", $this->_merge_url_param);
                halt2('', $url);
                return;
            }
        }
    }
    
    
    public function index()
    {
       
		
		$city = $_SESSION["uinfo"]["city"];

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
        //ʵ������ԱMODEL
        $member_model = D('Member');
        $city_channel = $this->channelid;
        $uid = $_SESSION["uinfo"]["uid"];

        //�����ύ
        if ($this->isPost() && !empty($_POST))
        {

            //�������ݽṹ
            $return = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            //��ĿID
            $project_id = intval($_POST['project_id']);
            //�ͻ��ֻ���
            $telno = trim(strip_tags($_POST['telno']));
            //�ͻ�����
            $cusname = u2g(trim(strip_tags($_POST['cusname'])));

            /**������֤**/
            $returnstr = '';
            if (!$project_id)
                $returnstr .= "��ѡ����Ŀ����\n";

            if(!preg_match('/^1[0-9]{10}$/',$telno))
                $returnstr .= "�ֻ�����д����\n";

            if(strlen($cusname) < 3)
                $returnstr .= "�ͻ�������д����\n";

            //��ȡ��Ŀ��Ϣ
            $project_info = $member_model->get_project_arr_by_pid($project_id);

            if (empty($project_info))
                $returnstr .= "�Բ�����ѡ�����Ŀ������\n";

            if(!empty($returnstr))
            {
                $return['msg'] = g2u($returnstr);
                die(@json_encode($return));
            }

            //CRM���
            $activename = urlencode($project_info[0]['PROJECTNAME'].'��Ȼ����');
            $project_listid = $project_info[0]['PRO_LISTID'];

            $cpi_arr = array(
                'city'=>$this->user_city_py,
                'mobile'=>$telno,
                'username'=>$cusname,
                'activefrom'=>231,
                'activename'=>$activename,
                'loupanids'=>$project_listid,
            );

            $crm_url = submit_crm_data_by_api_url($cpi_arr);
            //crm���
            $ret_log = api_log($this->city_id,$crm_url,0,$this->uid,2);

            //CRM����ֵ
            if($ret_log){
                $return['status'] = true;
                $return['msg'] = g2u("���û��Ѿ��ɹ�¼��");

                /**
                 *ʹ��ͳ����־
                 ***/
                $operate_type = 4;
                $operate_remark = '��Ȼ����¼��';
                $operate_user = $this->uid;
                $from_device = get_user_agent_device('num');
                submit_user_operate_log(0, $operate_type, $operate_remark,$operate_user, $from_device, $this->city_id, $project_id);
            }
            else
            {
                $return['msg'] = g2u("������¼��������Ƿ���ȷ");
            }

            //TODO ʹ��ͳ����־
            die(json_encode($return));
        }

        //��ĿȨ��
        $projects = $member_model->get_projectinfo_by_uid($uid,$city_channel);
        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        $this->assign('projects',$projects);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('new_member');
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
        $uid = $_SESSION["uinfo"]["uid"];
        $city_channel = $this->channelid;
        //ʵ������ԱMODEL
        $member_model = D('Member');

        //md5����key
        $form_sub_auth_key = md5("HOUSE365_JINGGUAN_".date('Ymd').'_'.$this->uname);

        //��������
        $action_type = isset($_POST['action_type']) ? trim($_POST['action_type']) : '';

        switch ($action_type)
        {
            //ǩ��ȷ��
            case 'arrive_confirm':

                $return = array(
                    'status'=>false,
                    'msg'=>'',
                    'data'=>null,
                );

                //ȷ����֤��
                $authcode_key = strip_tags($_POST['authcode_key']);

                //ͨ��¥�̱��
                $project_listid = intval($_POST['project_listid']);

                //��Ŀ���
                $project_id = intval($_POST['project_id']);

                //��֤��
                $code = strip_tags($_POST['code']);

                //�ͻ���ʵ����
                $truename = strip_tags($_POST['truename']);

                //�ͻ��ֻ�����
                $telno = strip_tags($_POST['telno']);

                //������Դ
                $is_from = strip_tags($_POST['is_from']);

                //�ͻ�ID
                $customer_id = intval($_POST['customer_id']);

                //���ݿͻ���֤���ȡ����ĿID
                $user_project_id = intval($_POST['user_project_id']);

                //project����
                $project_name = strip_tags($_POST['project_name']);

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
                                        $return['msg'] = g2u('���û��Ѿ��ڷ��ܼ�ϵͳ�е���ȷ�ϣ��޷��ٴε���ȷ��');
                                        die(@json_encode($return));
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
                                $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($project_id, $telno, $this->user_city_py);
                                if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 &&
                                    !empty($userinfo_crm_arr['meminfo']))
                                {
                                    if( $userinfo_crm_arr['meminfo']['codestatus'] == 1 )
                                    {
                                        $return['msg'] = g2u('���û��Ѿ���CRMϵͳ�е���ȷ�ϣ��޷��ٴε���ȷ��');
                                        die(@json_encode($return));
                                    }
                                    else
                                    {
                                        //CRM���û�ID
                                        $customer_id_crm = 0;
                                        $customer_id_crm = $userinfo_crm_arr['meminfo']['pmid'];
                                        $up_result = update_crm_user_source($customer_id_crm , 5);
                                    }
                                }
                                $result = arrival_confirm_fgj($customer_id, $ag_id, $cp_id);
                                $is_sucess = intval($result['result']);
                                $msg = $is_sucess == 1 ? '����ȷ�ϳɹ�' : '��֤ʧ�ܣ���֤����Ч���ѹ���';

                                //����ȷ����־��¼
                                arrival_confirm_log($customer_id , $truename , $telno , $code , $project_listid , $project_id , $is_from , $is_sucess);

                                //ʹ��ͳ����־
                                $operate_type = 3;
                                $operate_remark = '����ȷ��';
                                $operate_user = $this->uid;
                                $from_device = get_user_agent_device('num');
                                submit_user_operate_log($customer_id, $operate_type, $operate_remark, $operate_user, $from_device, $this->city_id, $project_id);

                            }

                            //��ȷ���ؽ��
                            $return['status'] = true;
                            //$return['msg'] = "ȷ�Ͻ���";
                            $msg = "ȷ�Ͻ���";

                        }
                        else
                        {
                            //��Ϊ��Ȼ������ӵ�CRM
                            $reg_result = register_natural_customer($this->user_city_py , $truename , $telno , $project_listid , $project_name);
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
                    $msg = "���������ͻ����� -> ��֤ʧ��";
                }
                $return['msg'] = g2u($msg);
                die(@json_encode($return));
                break;

            //������֤���ȡ�û���Ϣ
            case 'ajax_userinfo_by_code':
                $code = intval($_POST['code']);
                $project_listid = intval($_POST['project_listid']);
                $userinfo = get_userinfo_by_code($code, $project_listid);
                //var_dump($project_listid);
                die(@json_encode($userinfo));
                break;

            //���û�û����֤��ʱ�������û��ֻ�����򾭼����ֻ������ȡ�û���Ϣ
            case 'ajax_userinfo_by_telno':
                $customer_telno = trim($_POST['customer_telno']);
                $agent_telno = trim($_POST['agent_telno']);

                //������о����˵绰ȡԭ��ĿID
                if(strlen($agent_telno) == 0)
                {
                    $project_id = intval($_POST['project_id']);
                }
                //����ǿͻ��绰ȡ�·���ĿID
                else
                {
                    $project_id = intval($_POST['project_listid']);
                }
                $userinfo = get_userinfo_by_telno($project_id, $customer_telno, $agent_telno);
                die(@json_encode($userinfo));
                break;
        }
        //��ĿȨ������
        $projects = $member_model-> get_arrivalprojectinfo_by_uid($uid,$city_channel);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->assign('form_sub_auth_key',$form_sub_auth_key);
        $this->assign('projects',$projects);
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
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = $_SESSION['uinfo']['tname'];
        $showForm = intval($_GET['showForm']);
        //������Ϊ
        $act = !empty($_POST['act']) ? intval($_POST['act']) : '';
    	
    	//ʵ������ԱMODEL
    	$member_model = D('Member');
        
        /***��ȡ��Ա�쿨����Ʊ����Ʊ״̬***/
        $status_arr = $member_model->get_conf_all_status_remark();
        
        //װ�ޱ�׼
        $conf_zx_standard = $member_model->get_conf_zx_standard();

        //����Ǳ�������
        if($act=='savecfg'){

            $return = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            $formdata_str = $_POST['formdata'];
            parse_str($formdata_str,$formdata);

            $member_info = array();
            //�����������
            if(!empty($formdata)){
                $member_info['CITY_ID'] = $formdata['CITY_ID'];
                $member_info['PRJ_ID'] = $formdata['PRJ_ID'];
                $member_info['PRJ_NAME'] =  u2g($formdata['PRJ_NAME']);
                //��ȡcaseid
                $case_model = D('ProjectCase');
                $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'ds');
                $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
                $member_info['CASE_ID'] = $case_id;
                $member_info['SOURCE'] = $formdata['SOURCE'];

                $member_info['CERTIFICATE_TYPE'] = $formdata['CERTIFICATE_TYPE'];

                $member_info['CARDSTATUS'] = $formdata['CARDSTATUS'];

                $member_info['SIGNEDSUITE'] = intval($formdata['SIGNEDSUITE']);

                $member_info['RECEIPTSTATUS'] = $formdata['RECEIPTSTATUS'];

                $member_info['IS_TAKE'] = $formdata['IS_TAKE'];

                $member_info['IS_SMS'] = $formdata['IS_SMS'];

                $member_info['TOTAL_PRICE'] = intval($formdata['TOTAL_PRICE']);

                $member_info['PAID_MONEY'] = 0;
                $member_info['UNPAID_MONEY'] = floatval($formdata['TOTAL_PRICE']);

                $member_info['AGENCY_REWARD'] = floatval($formdata['AGENCY_REWARD']);
                $member_info['AGENCY_DEAL_REWARD'] = floatval($formdata['AGENCY_DEAL_REWARD']);
                $member_info['PROPERTY_DEAL_REWARD'] = floatval( $formdata['PROPERTY_DEAL_REWARD']);

                $member_info['DECORATION_STANDARD'] = intval( $formdata['DECORATION_STANDARD']);
                $member_info['LEAD_TIME'] = $formdata['LEAD_TIME'];

                $member_info['ADD_UID'] = $this->uid;
                $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];

                $member_info_str = serialize($member_info);

                $ret = D("Member")->put_user_config('MEMBER_ADD',$member_info_str,$this->uid);
            }

            if($ret){
                $return['status'] = true;
                $return['msg'] = g2u('�ף����浱ǰ���̻�Ա���óɹ���');
            }
            die(@json_encode($return));
        }

    	//�޸Ļ�Ա��Ϣ
    	if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id > 0)
    	{	
    		$member_info = array();
    		$member_info['REALNAME'] = u2g($_POST['REALNAME']);
            
            //�������ز�������
            if($_POST['MOBILENO'] != $_POST['MOBILENO_OLD'])
            {
                $member_info['MOBILENO'] = $_POST['MOBILENO'];
                if($member_info['MOBILENO'] == "")
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�����빺�����ֻ��ţ�');
                    echo json_encode($result);
                    exit;
                }
            }

            if($_POST['LOOKER_MOBILENO'] != $_POST['LOOKER_MOBILENO_OLD'])
            {
                $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            }
            
    		$member_info['SOURCE'] = $_POST['SOURCE'];
    		$member_info['CARDTIME'] = $_POST['CARDTIME'];
    		$member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            
            //֤������
            if (trim($_POST['CERTIFICATE_NO']) == '') 
            {
                $result['status'] = 0;
                $result['msg'] = g2u('�޸�ʧ�ܣ�֤�����������д��');
                
                echo json_encode($result);
                exit;
            }
                
            //������벿������
            if($_POST['CERTIFICATE_NO'] != $_POST['CERTIFICATE_NO_OLD'])
            {
                $member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
                if($member_info['CERTIFICATE_TYPE'] == 1)
                {   
                    if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) 
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ�ܣ����֤�����ʽ����ȷ��');

                        echo json_encode($result);
                        exit;
                    } 
                }
            }
            
    		$member_info['ROOMNO'] = u2g($_POST['ROOMNO']);
    		$member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
    		$member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
			$member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            //����
            $member_info['ATTACH'] = u2g($_POST['ATTACH']);
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //���Ϲ�
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if($member_info['SUBSCRIBETIME'] == ''|| $member_info['SUBSCRIBETIME'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�Ѱ����Ϲ����Ϲ����ڱ�����д��');
                        
                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '3':
                    //��ǩԼ
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    if($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ��ǩԼ���ں�ǩԼ����������д��');

                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ��¥�����ű�����д��');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ������ʱ�䡢װ��׼������д��');
                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '4':
                    //�˿�
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    
                    if($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�˿����˿����ں��˿������˱�����д��');
                        echo json_encode($result);
                        exit;
                    }
                    break;
            }
            
            /*  �Ѱ쿨δ�ɽ�״̬�Ļ�Ա�����޸�Ϊ�Ѱ쿨���Ϲ������Ѱ쿨��ǩԼ
                �Ѱ쿨���Ϲ�״̬�Ļ�Ա�����޸�Ϊ�Ѱ쿨��ǩԼ
                �Ѱ쿨��ǩԼ�Ļ�Ա���޷��޸�
                ���˿��Ļ�Ա���޷��޸� 
            */
            $cardstatus_old = intval($_POST['CARDSTATUS_OLD']);
            if($cardstatus_old == 1 && !in_array($member_info['CARDSTATUS'], array(1,2,3)))
            {
    			$result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���쿨״̬�����Ѱ쿨δ�ɽ�״̬��ֻ�����޸�Ϊ�Ѱ쿨���Ϲ������Ѱ쿨��ǩԼ');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 2 && !in_array($member_info['CARDSTATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���쿨״̬�����Ѱ쿨���Ϲ�״̬��ֻ�����޸�Ϊ�Ѱ쿨��ǩԼ');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 3 && $member_info['CARDSTATUS'] != 3)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���쿨״̬�����Ѱ쿨��ǩԼ״̬�������޸�');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 4 && $member_info['CARDSTATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���쿨״̬�������˿�״̬�������޸�');
                
                echo json_encode($result);
                exit;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            /**
                �ѿ�δ������޸�Ϊ��������ջ�
                ��������޸�Ϊ���ջ�  
                ���ջ��޷��޸��վ�״̬
             */    
            $receiptstatus_old = intval($_POST['RECEIPTSTATUS_OLD']);
            if($receiptstatus_old == 2 && !in_array($member_info['RECEIPTSTATUS'], array(2,3,4)))
            {
    			$result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���վ�״̬�����ѿ�δ�죬ֻ�����޸�Ϊ��������ջ�');
                
                echo json_encode($result);
                exit;
            }
            else if($receiptstatus_old == 3 && !in_array($member_info['RECEIPTSTATUS'], array(2,3,4)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���վ�״̬��������״̬��ֻ�����޸�Ϊ�ѿ�δ������ջ�');
                
                echo json_encode($result);
                exit;
            }
            else if($receiptstatus_old == 4 && $member_info['RECEIPTSTATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���վ�״̬�������ջ�״̬�������޸�');
                
                echo json_encode($result);
                exit;
            }
            $member_info['RECEIPTNO'] = trim(str_replace(array("��","/","��")," ", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
			if($member_info['RECEIPTNO'] != $_POST['RECEIPTNO_OLD']){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 $result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��,�ó������Ѿ�������ͬ���վݱ�ţ�');
					
					echo json_encode($result);
					exit;
				}
			}
    		$member_info['INVOICE_STATUS'] = $_POST['INVOICE_STATUS'];
            /**
                δ��״̬�������޸�
                ������״̬���������޸�
                �ѿ�δ��״̬�������޸�Ϊ����
                ����״̬���޷��޸�
                ���ջ�״̬���޷��޸�״
		    */    
            $invoicestatus_old = intval($_POST['INVOICE_STATUS_OLD']);

            if($invoicestatus_old == 1 && $member_info['INVOICE_STATUS'] != 1)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬����δ��״̬�������޸�');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 2 && !in_array($member_info['INVOICE_STATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬�����ѿ�δ��״̬��ֻ�����޸�Ϊ����');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 3 && !in_array($member_info['INVOICE_STATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬��������״̬��ֻ�����޸�Ϊ�ѿ�δ��');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 4 && $member_info['INVOICE_STATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬�������ջ�״̬���������޸�');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 5 && ($member_info['INVOICE_STATUS'] != 5 && $member_info['INVOICE_STATUS'] != 1))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬����������״̬��ֻ���޸�Ϊδ����������״̬');
                
                echo json_encode($result);
                exit;
            }
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            $member_info['TOTAL_PRICE'] = floatval($_POST['TOTAL_PRICE']);

            $total_price_old = floatval($_POST['TOTAL_PRICE_OLD']);
            if($total_price_old != $member_info['TOTAL_PRICE']){

                if($member_info['INVOICE_STATUS'] != 1){
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�����շѱ�׼�޸Ĺ����޸ĵ����շѱ�׼����Ʊ״ֻ̬����δ����');

                    die(@json_encode($result));
                }

                //����δ���ɽ��
                $member_info['UNPAID_MONEY'] = $member_info['TOTAL_PRICE'] - floatval($_POST['PAID_MONEY']) - floatval($_POST['REDUCE_MONEY']);
                //����ȷ��״̬
                $userInfo = $member_model->get_userinfo_by_uid($id);

                $member_pay = D('MemberPay');
                $confirmMoney = $member_pay->get_sum_pay($id,'confirmed');

                if($confirmMoney==0){
                    $member_info['FINANCIALCONFIRM'] = 1;
                }
                else if($confirmMoney + $_POST['REDUCE_MONEY'] < $member_info['TOTAL_PRICE']){
                    $member_info['FINANCIALCONFIRM'] = 2;
                }
                else if($confirmMoney + $_POST['REDUCE_MONEY'] >= $member_info['TOTAL_PRICE']){
                    $member_info['FINANCIALCONFIRM'] = 3;
                }
            }

            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            /**�н�Ӷ��������޸�ʱ���鿴��Ա�Ƿ��Ѿ�������н�Ӷ����**/
            if($member_info['AGENCY_REWARD'] != floatval($_POST['AGENCY_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 3);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�н�Ӷ�������뱨��,�޷��޸�!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
            
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            /**�н�ɽ�������������޸�ʱ���鿴��Ա�Ƿ��Ѿ�������н�ɽ���������**/
            if($member_info['AGENCY_DEAL_REWARD'] != floatval($_POST['AGENCY_DEAL_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 4);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�н�ɽ����������뱨��,�޷��޸�!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
            $member_info['PROPERTY_DEAL_REWARD'] = floatval($_POST['PROPERTY_DEAL_REWARD']);
            /**��ҵ���ʳɽ�������������޸�ʱ���鿴��Ա�Ƿ��Ѿ��������ҵ���ʳɽ���������**/
            if($member_info['PROPERTY_DEAL_REWARD'] != floatval($_POST['PROPERTY_DEAL_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 6);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,��ҵ���ʳɽ����������뱨��,�޷��޸�!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
    		$member_info['NOTE'] = u2g($_POST['NOTE']);
                $member_info['AGENCY_NAME'] = u2g($_POST['AGENCY_NAME']);
    		$member_info['UPDATETIME'] = date('Y-m-d');
			$member_info['OUT_REWARD']=$_POST['OUT_REWARD'];
            //$member_info['OUT_REWARD_STATUS']=1;
            //�н���Դ���쿨״̬���Ѱ���ǩԼ����
            if(($member_info['SOURCE'] == 1 || $member_info['SOURCE'] == 7 || $member_info['SOURCE'] == 8) && $member_info['CARDSTATUS'] == 3 )
            {
                if($member_info['AGENCY_REWARD'] == 0)
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�н�Ӷ�������д');
                    
                    echo json_encode($result);
                    exit;
                }
            }

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
                            $tlfcard_signtime = strtotime($member_info['SIGNTIME']);
                            $tlfcard_backtime = 0;
                            break;
                        case '4':
                            $tlfcard_status = 3; 
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = strtotime($member_info['BACKTIME']);
                            break;
                    }
                    
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = strip_tags($_POST['MOBILENO_HIDDEN']);
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $this->city;
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                    $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].
                    $member_info['CARDTIME'].$conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';
                        
                        $crm_api_arr['floor_id'] = $pro_listid;
                    }
                    
                    submit_crm_data_by_api($crm_api_arr);
                }

                //֪ͨȫ������׼����ϵͳ
                if($_POST['CARDSTATUS_OLD'] != $member_info['CARDSTATUS']){

                    $qltStatus = 3;

                    switch($member_info['CARDSTATUS'])
                    {
                        case '1':
                            $qltStatus = 3;
                            break;
                        case '2':
                            $qltStatus = 4;
                            break;
                        case '3':
                            $qltStatus = 5;
                            break;
                    }

                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$_POST['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltUserInfo = $member_model->get_userinfo_by_uid($id);

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $qltUserInfo['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,$uid,3);
                }

                $result['status'] = 1;
                $result['msg'] = '�޸ĳɹ�';
    		}
    		else
    		{
                    $result['status'] = 0;
                    $result['msg'] = '�޸�ʧ��';
    		}
    		
            $result['msg'] = g2u($result['msg']);

            if ($result['forward'] == '' && $_REQUEST['fromUrl']) {
                $result['forward'] = $_REQUEST['fromUrl'];  // ��ת��ַ
            }

    		echo json_encode($result);
    		exit;
    	}
    	//����
    	else if (!empty($_POST) && $faction == 'saveFormData')
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
            if($member_info['CERTIFICATE_TYPE'] == 1)
            {
                if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) 
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('���ʧ�ܣ����֤�����ʽ����ȷ��');
                    
                    echo json_encode($result);
                    exit;
                } 
            }
            else if (trim($member_info['CERTIFICATE_NO']) == '') 
            {
                $result['status'] = 0;
                $result['msg'] = g2u('���ʧ�ܣ�֤�����������д��');

                echo json_encode($result);
                exit;
            }
            
            $member_info['ROOMNO'] =  u2g($_POST['ROOMNO']);
            $member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            $member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
            
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //���Ϲ�
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if($member_info['SUBSCRIBETIME'] == '' || $member_info['SUBSCRIBETIME'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�Ѱ����Ϲ����Ϲ����ڱ�����д��');
                        
                        echo json_encode($result);
                        exit;
                    }
                break;
                case '3':
                    //��ǩԼ
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    if($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ��ǩԼ���ں�ǩԼ����������д��');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ��¥�����ű�����д��');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ������ʱ�䡢װ��׼������д��');
                        echo json_encode($result);
                        exit;
                    }
                break;
                case '4':
                    //�˿�
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    
                    if($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�˿����˿����ں��˿������˱�����д��');
                        echo json_encode($result);
                        exit;
                    }
                break;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            $member_info['RECEIPTNO'] = trim(str_replace(array("��","/","��"),",", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
			if($member_info['RECEIPTNO'] ){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 $result['status'] = 0;
					$result['msg'] = g2u('���ʧ��,�ó������Ѿ�������ͬ���վݱ�ţ�');
					
					echo json_encode($result);
					exit;
				}
			}
            $member_info['INVOICE_STATUS'] = 1; //����Ĭ��δ��
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            //����
            $member_info['ATTACH'] = u2g($_POST['ATTACH']);
            $member_info['TOTAL_PRICE'] = intval($_POST['TOTAL_PRICE']);
            $member_info['PAID_MONEY'] = 0;
            $member_info['UNPAID_MONEY'] = floatval($_POST['TOTAL_PRICE']);
            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            $member_info['PROPERTY_DEAL_REWARD'] = floatval( $_POST['PROPERTY_DEAL_REWARD']);
            
            //�н���Դ
            if(($member_info['SOURCE'] == 1 || $member_info['SOURCE'] == 7 || $member_info['SOURCE'] == 8) && $member_info['CARDSTATUS'] == 3 )
            {
                if($member_info['AGENCY_REWARD'] == 0)
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('���ʧ��,�н�Ӷ�������д');

                    echo json_encode($result);
                    exit;
                }
            }
            
            $member_info['ADD_UID'] = intval($_SESSION['uinfo']['uid']);
            $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];
            $member_info['NOTE'] =  u2g($_POST['NOTE']);
            $member_info['AGENCY_NAME'] =  u2g($_POST['AGENCY_NAME']);
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            $member_info['CREATETIME'] = date('Y-m-d H:i:s');
            $member_info['STATUS'] = 1;
            
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
                //arrival_confirm_log($customer_id, $member_info['REALNAME'], $member_info['MOBILENO'],
                //		$code, $_POST['LIST_ID'], $member_info['PRJ_ID'], $is_from, $is_sucess);
            }
            /****�Ƿ���Ҫ����ȷ��****/
			$member_info['IS_DIS']=1;//����
			$member_info['OUT_REWARD']=$_POST['OUT_REWARD'];
			$member_info['OUT_REWARD_STATUS']=1;
            $insert_id = $member_model->add_member_info($member_info);
            
            if($insert_id > 0)
            {
                //���Ͷ���
                if(isset($member_info['IS_SMS']) && $member_info['IS_SMS'] == 2 
                    && $member_info['CARDSTATUS'] < 4)
                {
                    $msg = "�𾴵�365��Ա".$member_info['REALNAME']."��"."���Ѱ쿨�ɹ�,�ͷ�����400-8181-365��";
                    send_sms($msg, $member_info['MOBILENO'], $this->city_config_array[$this->channelid]);
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
                            $tlfcard_signtime = strtotime($member_info['SIGNTIME']);
                            $tlfcard_backtime = 0;
                        break;
                        case '4':
                            $tlfcard_status = 3;
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = strtotime($member_info['BACKTIME']);
                        break;
                    }
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $this->city;
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                            $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].$member_info['CARDTIME'].$conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {   
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';
                        
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
        else if ($faction == 'delData')
        {   
            //�鿴��Ա�Ƿ���ڲ�����ȷ�ϵĸ�����ϸ��������ڲ�����ɾ��
            $mid = intval($_GET['ID']);
            $update_num = 0;
            
            if($mid > 0)
            {   
                $member_pay = D('MemberPay');
                $member_pay_info = $member_pay->get_payinfo_by_mid($mid);
                $conf_pay_status = $member_pay->get_conf_status();

                //��ȡ��Ա��Ϣ
                $member_info = $member_model->get_info_by_id($mid);
                
                $confirm_payment_num = 0;
                if(is_array($member_pay_info) && !empty($member_pay_info))
                {
                    foreach($member_pay_info as $key => $value)
                    {
                        if($value['STATUS'] == $conf_pay_status['confirmed'])
                        {
                            $confirm_payment_num ++;
                        }
                    }
                    
                    if($confirm_payment_num > 0)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('ɾ����Աʧ�ܣ����ڲ���ȷ�ϸ�����ϸ');
                        echo json_encode($result);
                        exit;
                    }
                    
                    //ɾ����Ա������ϸ��Ϣ
                    $delete_payment = $member_pay->del_pay_detail_by_mid($mid);
					
                    if($delete_payment > 0)
                    {
                        $income_from = 1;//���̻�Ա֧��
                        $income_model = D('ProjectIncome');
                        //ɾ������
                        foreach($member_pay_info as $key => $value)
                        {
                                $income_model->delete_income_info($member_info['CASE_ID'], $mid, $value['ID'], $income_from);
                        }
                    }
                }
                
                //ɾ����Ա��Ϣ
                $update_num = $member_model->delete_info_by_id($mid);
                
                //ɾ�����
                if($update_num > 0)
                {
                    /***�˿�֪ͨCRM***/
                    if($member_info['CARDSTATUS'] != 4)
                    {   
                        $crm_api_arr = array();
                        $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                        $crm_api_arr['mobile'] = strip_tags($member_info['MOBILENO']);
                        $crm_api_arr['activefrom'] = 104;
                        $crm_api_arr['city'] = $this->city;
                        $crm_api_arr['activename'] =  urlencode($member_info['PRJ_NAME'].
                                '�˿�'. oracle_date_format($member_info['CARDTIME'], 'Y-m-d').$conf_zx_standard[$member_info['DECORATION_STANDARD']]);
                        $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                        $crm_api_arr['tlfcard_status'] = 3;
                        $crm_api_arr['tlfcard_creattime'] = strtotime(oracle_date_format($member_info['CARDTIME'], 'Y-m-d'));
                        $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                        $crm_api_arr['tlfcard_signtime'] = strtotime(oracle_date_format($member_info['SIGNTIME'], 'Y-m-d'));
                        $crm_api_arr['tlfcard_backtime'] = time();
                        $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                        $crm_api_arr['projectid'] = $member_info['PRJ_ID'];

                        if($member_info['CARDSTATUS'] == 3)
                        {
                            $house_info = M('erp_house')->field('PRO_LISTID')->
                                    where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();

                            $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                    intval($house_info['PRO_LISTID']) : '';

                            $crm_api_arr['floor_id'] = $pro_listid;
                        }

                        submit_crm_data_by_api($crm_api_arr);
                    }

                    //��Ա������־
                    $log_info = array();
                    $log_info['OP_UID'] = $uid;
                    $log_info['OP_USERNAME'] = $username;
                    $log_info['OP_LOG'] = 'ɾ����Ա��Ϣ��'.$mid.'��';
                    $log_info['ADD_TIME'] = date('Y-m-d H:i:s');
                    $log_info['OP_CITY'] = $this->channelid;
                    $log_info['OP_IP'] = GetIP();
                    $log_info['TYPE'] = 2;
                    
                    member_opreate_log($log_info);

                    //ȫ������׼����ϵͳ(���״̬)
                    $qltStatus = 6;
                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$member_info['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $member_info['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,intval($_SESSION['uinfo']['uid']),3);
                    
                    $result['status'] = 'success';
                    $result['msg'] = 'ɾ����Ա�ɹ�';
                }
                else
                {	
                    $result['status'] = 'error';
                    $result['msg'] = 'ɾ����Աʧ�ܣ�';
                }
            }
            else 
            {
                $result['status'] = 'error';
                $result['msg'] = '�����쳣��';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else
        {   
            Vendor('Oms.Form');
            $form = new Form();

            $form = $form->initForminfo(103);
            $form->SQLTEXT = "(";
            $form->SQLTEXT .= "SELECT DISTINCT * FROM ERP_CARDMEMBER WHERE CITY_ID = '".$this->channelid."' AND STATUS = 1 ";
            
            //�Ƿ��в鿴ȫ����Ȩ��
            if(!$this->p_auth_all)
            {
                $form->SQLTEXT .= " AND PRJ_ID IN "
                    . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 1) ";
            }
            
            //�Ƿ��Լ�����
            if(!$this->p_vmem_all)
            {
                $form->SQLTEXT .= " AND ADD_UID = $uid";
            }

            //������ϸ���ѯ������ҵ��������
            if(!empty($_REQUEST))
            {   
                //�ض������������ı�������ʽ��ʹ�������ֶ���Ϊ�Ӳ�ѯ����
                $pay_search_filed = array ('RETRIEVAL', 'CVV2', 'MERCHANT_NUMBER', 'TRADE_TIME', 'STATUS','REFUND_TIME','REFUND_APPLY_TIME');
                
                $cond_where = "";
                for($i = 1 ; $i <= 4 ; $i ++)
                {   
                    if(in_array($_REQUEST['search'.$i], $pay_search_filed))
                    {   
                       //ƴ���Ӳ�ѯSQL
                        if($_REQUEST['search'.$i] == "REFUND_APPLY_TIME"){
                            $_REQUEST['search'.$i] = "CREATETIME";
                        }
                       $cond_where .=  
                            $form->pjsql($_REQUEST['search'.$i], $_REQUEST['search'.$i.'_s'], $_REQUEST['search'.$i.'_t'], $_REQUEST['search'.$i.'_t_type']);
                       
                       //ƴ���Ӳ�ѯ�󣬷�ҳ��Ҫ�õ�������������װ������������ҳ����ʹ��
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'='.$_REQUEST['search'.$i];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_s='.$_REQUEST['search'.$i.'_s'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t='.$_REQUEST['search'.$i.'_t'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t_type='.$_REQUEST['search'.$i.'_t_type'];
                       
                       unset($_POST['search'.$i]);
                       unset($_POST['search'.$i.'_s']);
                       unset($_POST['search'.$i.'_t']);
                       unset($_POST['search'.$i.'_t_type']);

                       unset($_GET['search'.$i]);
                       unset($_GET['search'.$i.'_s']);
                       unset($_GET['search'.$i.'_t']);
                       unset($_GET['search'.$i.'_t_type']);

                    }
                }
                
                if($cond_where != '')
                {
                    if($_REQUEST['search1'] == "CREATETIME" or $_REQUEST['search2'] == "CREATETIME" or
                    $_REQUEST['search3'] == "CREATETIME" or $_REQUEST['search4'] == "CREATETIME"){
                        $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID  FROM ERP_MEMBER_REFUND_DETAIL WHERE 1=1 " . $cond_where . ")";
                    }else {
                        $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID FROM ERP_MEMBER_PAYMENT WHERE STATUS != 4 " . $cond_where . ")";
                    }
                }
            }

            $form->SQLTEXT .= " AND IS_DIS=1 )";
            // echo $form->SQLTEXT;
            //�ֻ���������
            $form->setMyField('MOBILENO', 'ENCRY', '4,8', FALSE);
            $form->setMyField('LOOKER_MOBILENO', 'ENCRY', '4,8', FALSE);
			$form->setMyField('TOTAL_PRICE_AFTER', 'FORMVISIBLE', '0', FALSE);
			$form->setMyField('AGENCY_REWARD_AFTER', 'FORMVISIBLE', '0', FALSE);
            
            //����֤������
            $certificate_type_arr = $member_model->get_conf_certificate_type();
            $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR', 
            		array2listchar($certificate_type_arr), FALSE);

            //֤������
            $form->setMyField('CERTIFICATE_NO', 'ENCRY', '4,13', FALSE);
            
            //���ø��ʽ
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', 
	        		array2listchar($pay_arr), FALSE);
	        
	        //�쿨״̬
            $card_status_arr = $status_arr['CARDSTATUS'];
            if($showForm == 3)
            {
                array_pop($card_status_arr); 

            }
	        $form->setMyField('CARDSTATUS', 'LISTCHAR', 
	        		array2listchar($card_status_arr), FALSE);
            
            $current_time = date('Y-m-d');
            if($showForm == 3)
            {   
                //����ʱ��չʾ�쿨ʱ��Ĭ��
                $form->setMyFieldVal('CARDTIME', $current_time, false);
            }
	        
	        //��Ʊ״̬
            if($showForm == 3)
            {   
                //����ҳ�淢Ʊֻ����Ĭ��Ϊδ��
                $form->setMyFieldVal('INVOICE_STATUS', '1', TRUE );
                $form->setMyField('INVOICE_STATUS', 'LISTCHAR', 
	        		array2listchar($status_arr['INVOICE_STATUS']), TRUE);
                
                $form->setMyFieldVal('INVOICE_STATUS', '1', TRUE );
            }
            else
            {
                $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                        array2listchar($status_arr['INVOICE_STATUS']), FALSE);
            }
            
            //֧����Ϣ����ȷ��״̬��֧����������ɾ��
            $conf_pay_status = $member_pay->get_conf_status_remark();
            $form->setMyField('STATUS', 'LISTCHAR',
                        array2listchar($conf_pay_status), FALSE);
	        
	        //�վ�״̬
            $receipt_status_arr = $status_arr['RECEIPTSTATUS'];
            if($showForm == 3)
            {
                array_pop($receipt_status_arr); 
            }
	        $form->setMyField('RECEIPTSTATUS', 'LISTCHAR', 
	        		array2listchar($receipt_status_arr), FALSE);
            
            //��ҳ��
            /***
             *   $showForm 1: �༭
             *   $showForm 2: �鿴
             *   $showForm 3: ����
             */
            if($showForm == 1 || $showForm == 3 || $showForm == 2 )
            {   
                //�޸ļ�¼ID
                $modify_id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;

                //���þ�������Ϣ
                $userinfo = array();
                $form->setMyFieldVal('ADD_USERNAME', $username, TRUE);

                if($modify_id > 0)
                {
                    $search_field = array('PRJ_ID', 'CASE_ID','ADD_USERNAME');
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
                                if($value['AMOUNT'][0]=='.'){
                                    $value['AMOUNT'] = '0' . $value['AMOUNT'];
                                }
                                $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'];
                            }

                        //���þ�����(�༭״̬�����ֲ���)
                        $form->setMyFieldVal('ADD_USERNAME', $userinfo['ADD_USERNAME'], TRUE);
                        //�����շѱ�׼
                        $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //�н�Ӷ��
                        $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
                        //��ҵ����Ӷ��
                       // $form->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
						  $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //�н�ɽ���
                        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //��ҵ�ɽ�����
                        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
                    }
                    
                    //��ԱMODEL
                    $member_model = D('Member');
                    $member_info = $member_model->get_info_by_id($modify_id, array('CITY_ID', 'PRJ_ID','CASE_ID','MOBILENO'));
                    
                    if(is_array($member_info) && !empty($member_info))
                    {
                        $input_arr = array(
                                array('name' => 'CITY_ID', 'val' => $member_info['CITY_ID'], 'id' => 'CITY_ID'),
                                array('name' => 'PRJ_ID', 'val' => $member_info['PRJ_ID'], 'id' => 'PRJ_ID'),
                                array('name' => 'CASE_ID', 'val' => $member_info['CASE_ID'], 'id' => 'CASE_ID'),
                                array('name' => 'MOBILENO_HIDDEN', 'val' => $member_info['MOBILENO'], 'id' => 'MOBILENO_HIDDEN'),
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
                    $form->addHiddenInput($input_arr);
                }
            }
            else 
            {	
            	/***�б�ҳ��������***/
            	//�����
                $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                //�����շѱ�׼
                $form->setMyField('TOTAL_PRICE', 'EDITTYPE',"1", TRUE);
                //����ȷ��״̬
                $form->setMyField('FINANCIALCONFIRM', 'LISTCHAR', array2listchar($status_arr['FINANCIALCONFIRM']), FALSE);
            }

            //���û�Ա��Դ
            $source_arr = $member_model->get_conf_member_source_remark();

            //�༭��������ĿID��ѯ��Ŀ�ֽ�Ŀ�����۷�ʽ
            if($showForm == 1)
            {
                //���û�Ա��Դ(�޸�ʱ��Ҫ��ǰ��Ա��Ŀ��Ϣ��ע�����λ��)

                $prj_id =  !empty($userinfo['PRJ_ID']) ? intval($userinfo['PRJ_ID']) : 0;
                $source_arr = $member_model->getPrjSaleMethod($prj_id);

            }
            else if($showForm == 2)
            {
                //���û�Ա��Դ
                $source_arr = $member_model->get_conf_member_source_remark();

                //��ԱMODEL
                $id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;
                $member_model = D('Member');
                $member_info = $member_model->get_info_by_id($id, array('ADD_USERNAME'));

                //���������
                $form->setMyFieldVal('ADD_USERNAME', $member_info['ADD_USERNAME'], TRUE);
            }

            /**
             *  �����������Ա������Ӧ�ı������ã�ֱ�Ӷ�ȡ
             */
            if($showForm==3){

                //��������ID --- ������Ա�����ظ��޸�
                $user_config = $member_model->get_user_config('MEMBER_ADD',$this->uid);
                $user_config = unserialize($user_config);

                if($user_config){
                    //�����շѱ�׼
                    $case_id =  !empty($user_config['CASE_ID']) ? intval($user_config['CASE_ID']) : 0;
                    $feescale = D('Project')->get_feescale_by_cid($case_id);

                    if(is_array($feescale) && !empty($feescale) )
                    {
                        foreach($feescale as $key => $value)
                        {
                            if($value['AMOUNT'][0]=='.'){
                                $value['AMOUNT'] = '0' . $value['AMOUNT'];
                            }
                            $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'];
                        }

                        //���þ�����(�༭״̬�����ֲ���)
                        $form->setMyFieldVal('ADD_USERNAME', $user_config['ADD_USERNAME'], TRUE);
                        //�����շѱ�׼
                        $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //�н�Ӷ��
                        $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
                        //��ҵ����Ӷ��  �ⲿ����
                        $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //�н�ɽ���
                        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //��ҵ�ɽ�����
                        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
                    }

                    //���¸�ֵ
                    $form->setMyFieldVal('PRJ_NAME', $user_config['PRJ_NAME'], FALSE);
                    $form->setMyFieldVal('SOURCE', $user_config['SOURCE'], FALSE);
                    $form->setMyFieldVal('CERTIFICATE_TYPE', $user_config['CERTIFICATE_TYPE'], FALSE);
                    $form->setMyFieldVal('CARDSTATUS', $user_config['CARDSTATUS'], FALSE);
                    $form->setMyFieldVal('SIGNEDSUITE', $user_config['SIGNEDSUITE'], FALSE);
                    $form->setMyFieldVal('RECEIPTSTATUS', $user_config['RECEIPTSTATUS'], FALSE);
                    $form->setMyFieldVal('IS_TAKE', $user_config['IS_TAKE'], FALSE);
                    $form->setMyFieldVal('IS_SMS', $user_config['IS_SMS'], FALSE);
                    $form->setMyFieldVal('TOTAL_PRICE', $user_config['TOTAL_PRICE'], FALSE);
                    $form->setMyFieldVal('UNPAID_MONEY', $user_config['UNPAID_MONEY'], TRUE);
                    $form->setMyFieldVal('PAID_MONEY', $user_config['PAID_MONEY'], TRUE);
                    $form->setMyFieldVal('AGENCY_REWARD', $user_config['AGENCY_REWARD'], FALSE);
                    $form->setMyFieldVal('AGENCY_DEAL_REWARD', $user_config['AGENCY_DEAL_REWARD'], FALSE);
                    $form->setMyFieldVal('PROPERTY_DEAL_REWARD', $user_config['PROPERTY_DEAL_REWARD'], FALSE);
                    $form->setMyFieldVal('LEAD_TIME', $user_config['LEAD_TIME'], FALSE);
                    $form->setMyFieldVal('DECORATION_STANDARD', $user_config['DECORATION_STANDARD'], FALSE);


                    $input_arr = array(
                        array('name' => 'CITY_ID', 'val' => $user_config['CITY_ID'], 'id' => 'CITY_ID'),
                        array('name' => 'PRJ_ID', 'val' => $user_config['PRJ_ID'], 'id' => 'PRJ_ID'),
                        array('name' => 'CASE_ID', 'val' => $user_config['CASE_ID'], 'id' => 'CASE_ID'),
                    );
                    $form = $form->addHiddenInput($input_arr);

                    //��Ա��Դ
                    $source_arr = $member_model->getPrjSaleMethod($user_config['PRJ_ID']);
                }
            }

            $form->setMyField('SOURCE', 'LISTCHAR', array2listchar($source_arr), FALSE);
            
            //װ�ޱ�׼
            $form->setMyField('DECORATION_STANDARD', 'LISTCHAR', array2listchar($conf_zx_standard), FALSE);

            //����ֱ����Ա
            $form->setMyField('DIRECTSALLER', 'FORMVISIBLE', -1, FALSE);
            $form->setMyField('DIRECTSALLER', 'GRIDVISIBLE', -1, FALSE);
            //��ʾ���浱ǰ���ð�ť (��� + �༭)
            if($showForm==1 || $showForm==3)
                $form->showSaveCfg = true;
            
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
            $form->GABTN.="<a id='lock_member' href='javascript:;' data-id = 0 class='btn btn-info btn-sm'>
            ��Ա����
            </a>
            <a id='unlock_member' href='javascript:;' data-id = 1 class='btn btn-info btn-sm'>
            ��Ա����
            </a>";

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
            $formhtml =  $form->setChildren($children_data)->getResult();

            $this->assign('form', $formhtml);
            $this->assign('showForm', $showForm);
            //�����������
            $this->assign('filter_sql',$form->getFilterSql());
            //�����������
            $this->assign('sort_sql',$form->getSortSql());
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
			$this->assign('case_type','ds');
            $this->display('reg_member');
        }
    }
    

	 /**
    +----------------------------------------------------------
    * ע������쿨��Ա
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function DisRegMember()
    {   
        $id = !empty($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = $_SESSION['uinfo']['tname'];
        $showForm = intval($_GET['showForm']);
		
        //������Ϊ
        $act = !empty($_POST['act']) ? intval($_POST['act']) : '';
    	
    	//ʵ������ԱMODEL
    	$member_model = D('Member');
        
        /***��ȡ��Ա�쿨����Ʊ����Ʊ״̬***/
        $status_arr = $member_model->get_conf_all_status_remark();
        
        //װ�ޱ�׼
        $conf_zx_standard = $member_model->get_conf_zx_standard();

        //����Ǳ�������
        if($act=='savecfg'){

            $return = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            $formdata_str = $_POST['formdata'];
            parse_str($formdata_str,$formdata);

            $member_info = array();
            //�����������
            if(!empty($formdata)){
                $member_info['CITY_ID'] = $formdata['CITY_ID'];
                $member_info['PRJ_ID'] = $formdata['PRJ_ID'];
                $member_info['PRJ_NAME'] =  u2g($formdata['PRJ_NAME']);
                //��ȡcaseid
                $case_model = D('ProjectCase');
                $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'fx');
                $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
                $member_info['CASE_ID'] = $case_id;
                $member_info['SOURCE'] = $formdata['SOURCE'];

                $member_info['CERTIFICATE_TYPE'] = $formdata['CERTIFICATE_TYPE'];

                $member_info['CARDSTATUS'] = $formdata['CARDSTATUS'];

                $member_info['SIGNEDSUITE'] = intval($formdata['SIGNEDSUITE']);

                $member_info['RECEIPTSTATUS'] = $formdata['RECEIPTSTATUS'];

                $member_info['IS_TAKE'] = $formdata['IS_TAKE'];

                $member_info['IS_SMS'] = $formdata['IS_SMS'];

                $member_info['TOTAL_PRICE'] = intval($formdata['TOTAL_PRICE']);
				$member_info['TOTAL_PRICE_AFTER'] = intval($formdata['TOTAL_PRICE_AFTER']);
                $member_info['PAID_MONEY'] = 0;
                $member_info['UNPAID_MONEY'] = floatval($formdata['TOTAL_PRICE']);

                $member_info['AGENCY_REWARD'] = floatval($formdata['AGENCY_REWARD']);
                $member_info['AGENCY_DEAL_REWARD'] = floatval($formdata['AGENCY_DEAL_REWARD']);
                $member_info['PROPERTY_DEAL_REWARD'] = floatval( $formdata['PROPERTY_DEAL_REWARD']);

                $member_info['DECORATION_STANDARD'] = intval( $formdata['DECORATION_STANDARD']);
                $member_info['LEAD_TIME'] = $formdata['LEAD_TIME'];
				$member_info['AGENCY_REWARD_AFTER'] = $formdata['AGENCY_REWARD_AFTER'];
				$member_info['OUT_REWARD'] = $formdata['OUT_REWARD'];

                $member_info['ADD_UID'] = $this->uid;
                $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];

                $member_info_str = serialize($member_info);

                $ret = D("Member")->put_user_config('DISMEMBER_ADD',$member_info_str,$this->uid);
            }

            if($ret){
                $return['status'] = true;
                $return['msg'] = g2u('�ף����浱ǰ������Ա���óɹ���');
            }
            die(@json_encode($return));
        }
		if(!empty($_POST) ){
			$project = D('Project');
			$case_model = D('ProjectCase');
			$case_info = $case_model->get_info_by_pid($_POST['PRJ_ID'], 'fx');
			$case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
			$flag1 = $project->get_feescale_by_cid_stype($case_id,1, $_POST['TOTAL_PRICE'],1,0);
			$flag2 = $project->get_feescale_by_cid_stype($case_id,1, $_POST['TOTAL_PRICE_AFTER'],1,1);
			$flag3 = $project->get_feescale_by_cid_stype($case_id,2, $_POST['AGENCY_REWARD'],1,0);
			$flag4 = $project->get_feescale_by_cid_stype($case_id,2, $_POST['AGENCY_REWARD_AFTER'],1,1);
			if($flag1 || $flag2 || $flag3  ||$flag4 ){
				if(!$_POST['HOUSETOTAL']){
					$result['status'] = 0;
					$result['msg'] = g2u('�շѱ�׼��Ӷ��Ϊ�ٷֱȣ�������д�����ܼ�!');
					echo json_encode($result);
					exit;
				}
			}
			$flag5 = $project->get_feescale_by_cid_stype($case_id,3, $_POST['OUT_REWARD'],1);
			$flag6 = $project->get_feescale_by_cid_stype($case_id,4, $_POST['AGENCY_DEAL_REWARD'],1);
			$flag7 = $project->get_feescale_by_cid_stype($case_id,5, $_POST['PROPERTY_DEAL_REWARD'],1);
			//var_dump($flag5 );var_dump($flag6 );var_dump($_POST['OUT_REWARD']);  
			if($flag5 || $flag6 || $flag7  ){
				if(!$_POST['HOUSETOTAL']){
					$result['status'] = 0;
					$result['msg'] = g2u('����ҵ���ʳɽ��������н�ɽ��������ⲿ�ɽ�����ѡ��ٷֱȣ�������д�����ܼ�!');
					echo json_encode($result);
					exit;
				}
				if($_POST['TOTAL_PRICE'] && !$_POST['TOTAL_PRICE_AFTER'] ){
					$result['status'] = 0;
					$result['msg'] = g2u('�������շѱ�׼ֻѡ����ǰӶ�� �н�ɽ��������ⲿ�ɽ���������ҵ���ʳɽ����������ǽ��!');
					echo json_encode($result);
					exit;

				}
			}
		} 

    	//�޸Ļ�Ա��Ϣ
    	if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id > 0)
    	{	
    		$member_info = array();
    		$member_info['REALNAME'] = u2g($_POST['REALNAME']);
            
            //�������ز�������
            if($_POST['MOBILENO'] != $_POST['MOBILENO_OLD'])
            {
                $member_info['MOBILENO'] = $_POST['MOBILENO'];
                if($member_info['MOBILENO'] == "")
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�����빺�����ֻ��ţ�');
                    echo json_encode($result);
                    exit;
                }
            }
			if($_POST['TOTAL_PRICE']=='' &&  $_POST['TOTAL_PRICE_AFTER']=='' ){
				$result['status'] = 0;
				$result['msg'] = g2u('�޸�ʧ��,��ѡ��ǰӶ�շѱ�׼���ߺ�Ӷ�շѱ�׼��');
				echo json_encode($result);
				exit;

			}elseif($_POST['TOTAL_PRICE_AFTER']==''){
				
                 

				$OUT_REWARD = $project->get_feescale_by_cid_stype($case_id,3, $_POST['OUT_REWARD']);
				if($OUT_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��,�����շѱ�׼ֻ��ǰӶ���ⲿ�ɽ���������Ϊ�ٷֱȣ�');
				}
				$AGENCY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id,4, $_POST['AGENCY_DEAL_REWARD']);
				if($AGENCY_DEAL_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��,�����շѱ�׼ֻ��ǰӶ���н�ɽ���������Ϊ�ٷֱȣ�');
				}
				$PROPERTY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id,5, $_POST['PROPERTY_DEAL_REWARD']);
				if($PROPERTY_DEAL_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��,�����շѱ�׼ֻ��ǰӶ����ҵ���ʳɽ���������Ϊ�ٷֱȣ�');
				}
				  
				if($OUT_REWARD || $AGENCY_DEAL_REWARD ||$PROPERTY_DEAL_REWARD ){
					echo json_encode($result);
					exit;
				}

			}
			if($_POST['TOTAL_PRICE']){
				if(!$_POST['RECEIPTSTATUS']){
					$result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��, ��ѡ��ǰӶ�շѱ�׼�ı���ѡ���վ�״̬��');
					echo json_encode($result);
					exit;
				}
				if(!$_POST['RECEIPTNO']){
					$result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��, ��ѡ��ǰӶ�շѱ�׼�ı�����д�վݱ�ţ�');
					echo json_encode($result);
					exit;
				}

				if($_POST['AGENCY_REWARD_STATUS']>1){
					if($_POST['AGENCY_REWARD']!=$_POST['AGENCY_REWARD_OLD']){
					$result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��, �н�Ӷ�������뱨���������޸ģ�');
					echo json_encode($result);
					exit;

				}
				 
				}
				if($_POST['AGENCY_DEAL_REWARD_STATUS']>1){
					 
					if($_POST['AGENCY_DEAL_REWARD']!=$_POST['AGENCY_DEAL_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('�޸�ʧ��, �н�ɽ����������뱨���������޸ģ�');
						echo json_encode($result);
						exit;

					}
				}
				if($_POST['PROPERTY_DEAL_REWARD_STATUS']>1){
					 
					if($_POST['PROPERTY_DEAL_REWARD']!=$_POST['PROPERTY_DEAL_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('�޸�ʧ��, ��ҵ���ʳɽ����������뱨���������޸ģ�');
						echo json_encode($result);
						exit;

					}
				}
				if($_POST['OUT_REWARD_STATUS']>1){
					 
					if($_POST['OUT_REWARD']!=$_POST['OUT_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('�޸�ʧ��, �ⲿ�ɽ����������뱨���������޸ģ�');
						echo json_encode($result);
						exit;

					}
				}

				


			}
			if($_POST['TOTAL_PRICE_AFTER_OLD'] && ($_POST['REWARD_STATUS']==2 || $_POST['REWARD_STATUS']==3 ) ){
				if($_POST['AGENCY_REWARD_STATUS']>1){
					if($_POST['AGENCY_REWARD']!=$_POST['AGENCY_REWARD_OLD']){
					$result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��, �н�Ӷ�������뱨���������޸ģ�');
					echo json_encode($result);
					exit;

				}
				 
				}
				if($_POST['AGENCY_DEAL_REWARD_STATUS']>1){
					 
					if($_POST['AGENCY_DEAL_REWARD']!=$_POST['AGENCY_DEAL_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('�޸�ʧ��, �н�ɽ����������뱨���������޸ģ�');
						echo json_encode($result);
						exit;

					}
				}
				if($_POST['PROPERTY_DEAL_REWARD_STATUS']>1){
					 
					if($_POST['PROPERTY_DEAL_REWARD']!=$_POST['PROPERTY_DEAL_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('�޸�ʧ��, ��ҵ���ʳɽ����������뱨���������޸ģ�');
						echo json_encode($result);
						exit;

					}
				}
				if($_POST['OUT_REWARD_STATUS']>1){
					 
					if($_POST['OUT_REWARD']!=$_POST['OUT_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('�޸�ʧ��, �ⲿ�ɽ����������뱨���������޸ģ�');
						echo json_encode($result);
						exit;

					}
				}

			}

			if($_POST['TOTAL_PRICE_AFTER_OLD'] && ($_POST['REWARD_STATUS']==2 || $_POST['REWARD_STATUS']==3 ) && $_POST['INVOICE_STATUS']>1 ){
				 
				if($_POST['TOTAL_PRICE_AFTER']!=$_POST['TOTAL_PRICE_AFTER_OLD']){
					$result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��, �ú�Ӷģʽ��Ա,�������Ӷ�������뿪Ʊ,�������޸ĺ�Ӷ�շѱ�׼��');
					echo json_encode($result);
					exit;
				}

				 
			}
			if($_POST['TOTAL_PRICE_AFTER_OLD']!=$_POST['TOTAL_PRICE_AFTER'] ){
				$onee = M('Erp_post_commission')->where("CARD_MEMBER_ID=".$_POST['ID'])->find();
				if($onee){
					$ressss = M('Erp_commission_invoice_detail')->where("POST_COMMISSION_ID=".$onee['ID'])->find();
					if($ressss){
						$result['status'] = 0;
						$result['msg'] = g2u('�޸�ʧ��, �ú�Ӷģʽ��Ա,�������Ӷ�������뿪Ʊ,�������޸ĺ�Ӷ�շѱ�׼  ��');
						echo json_encode($result);
						exit;
					}
				}

			}
			if($_POST['AGENCY_REWARD_AFTER_OLD']!=$_POST['AGENCY_REWARD_AFTER'] ){
				$onee = M('Erp_post_commission')->where("CARD_MEMBER_ID=".$_POST['ID'])->find();
				if($onee){
					$ressss = M('Erp_commission_reim_detail')->where("POST_COMMISSION_ID=".$onee['ID'])->find();
					if($ressss){
						$result['status'] = 0;
						$result['msg'] = g2u('�޸�ʧ��, �ú�Ӷģʽ��Ա,�Ѿ������н�Ӷ����,�������޸ĺ�Ӷ�н�Ӷ���׼  ��');
						echo json_encode($result);
						exit;
					}
				}

			}

			 
			$mres = D('Erp_member_payment')->where('MID='.$id)->select(); //var_dump($mres);
			if($mres && !$_POST['TOTAL_PRICE']){
				$result['status'] = 0;
				$result['msg'] = g2u('�и�����ϸ�ķ�����Ա,ǰӶ�շѱ�׼����Ϊ��!');
				echo json_encode($result);
				exit;
			}

			if(($_POST['REWARD_STATUS']==3||$_POST['REWARD_STATUS']==2) && !$_POST['TOTAL_PRICE_AFTER']){

				$result['status'] = 0;
				$result['msg'] = g2u('�������Ӷ�Ŀͻ�����Ӷ�շѱ�׼����ѡ��');
				echo json_encode($result);
				exit;
			}

            if($_POST['LOOKER_MOBILENO'] != $_POST['LOOKER_MOBILENO_OLD'])
            {
                $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            }
            
    		$member_info['SOURCE'] = $_POST['SOURCE'];
    		$member_info['CARDTIME'] = $_POST['CARDTIME'];
    		$member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            
            //֤������
            if (trim($_POST['CERTIFICATE_NO']) == '') 
            {
                $result['status'] = 0;
                $result['msg'] = g2u('�޸�ʧ�ܣ�֤�����������д��');
                
                echo json_encode($result);
                exit;
            }
                
            //������벿������
            if($_POST['CERTIFICATE_NO'] != $_POST['CERTIFICATE_NO_OLD'])
            {
                $member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
                if($member_info['CERTIFICATE_TYPE'] == 1)
                {   
                    if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) 
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ�ܣ����֤�����ʽ����ȷ��');

                        echo json_encode($result);
                        exit;
                    } 
                }
            }
            
    		$member_info['ROOMNO'] = u2g($_POST['ROOMNO']);
    		$member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
    		$member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            //����
            $member_info['ATTACH'] = u2g($_POST['ATTACH']);
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //���Ϲ�
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if($member_info['SUBSCRIBETIME'] == ''|| $member_info['SUBSCRIBETIME'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�Ѱ����Ϲ����Ϲ����ڱ�����д��');
                        
                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '3':
                    //��ǩԼ
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    if($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ��ǩԼ���ں�ǩԼ����������д��');

                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ��¥�����ű�����д��');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ������ʱ�䡢װ��׼������д��');
                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '4':
                    //�˿�
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    
                    if($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�޸�ʧ��,�쿨״̬Ϊ�˿����˿����ں��˿������˱�����д��');
                        echo json_encode($result);
                        exit;
                    }
                    break;
            }
            
            /*  �Ѱ쿨δ�ɽ�״̬�Ļ�Ա�����޸�Ϊ�Ѱ쿨���Ϲ������Ѱ쿨��ǩԼ
                �Ѱ쿨���Ϲ�״̬�Ļ�Ա�����޸�Ϊ�Ѱ쿨��ǩԼ
                �Ѱ쿨��ǩԼ�Ļ�Ա���޷��޸�
                ���˿��Ļ�Ա���޷��޸� 
            */
            $cardstatus_old = intval($_POST['CARDSTATUS_OLD']);
            if($cardstatus_old == 1 && !in_array($member_info['CARDSTATUS'], array(1,2,3)))
            {
    			$result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���쿨״̬�����Ѱ쿨δ�ɽ�״̬��ֻ�����޸�Ϊ�Ѱ쿨���Ϲ������Ѱ쿨��ǩԼ');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 2 && !in_array($member_info['CARDSTATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���쿨״̬�����Ѱ쿨���Ϲ�״̬��ֻ�����޸�Ϊ�Ѱ쿨��ǩԼ');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 3 && $member_info['CARDSTATUS'] != 3)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���쿨״̬�����Ѱ쿨��ǩԼ״̬�������޸�');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 4 && $member_info['CARDSTATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���쿨״̬�������˿�״̬�������޸�');
                
                echo json_encode($result);
                exit;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            /**
                �ѿ�δ������޸�Ϊ��������ջ�
                ��������޸�Ϊ���ջ�  
                ���ջ��޷��޸��վ�״̬
             */    
            $receiptstatus_old = intval($_POST['RECEIPTSTATUS_OLD']);
            if($receiptstatus_old == 2 && !in_array($member_info['RECEIPTSTATUS'], array(2,3,4)))
            {
    			$result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���վ�״̬�����ѿ�δ�죬ֻ�����޸�Ϊ��������ջ�');
                
                echo json_encode($result);
                exit;
            }
            else if($receiptstatus_old == 3 && !in_array($member_info['RECEIPTSTATUS'], array(2,3,4)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���վ�״̬��������״̬��ֻ�����޸�Ϊ�ѿ�δ������ջ�');
                
                echo json_encode($result);
                exit;
            }
            else if($receiptstatus_old == 4 && $member_info['RECEIPTSTATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ���վ�״̬�������ջ�״̬�������޸�');
                
                echo json_encode($result);
                exit;
            }
            $member_info['RECEIPTNO'] = trim(str_replace(array("��","/","��")," ", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
			if($member_info['RECEIPTNO'] != $_POST['RECEIPTNO_OLD']){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 $result['status'] = 0;
					$result['msg'] = g2u('�޸�ʧ��,�ó������Ѿ�������ͬ���վݱ�ţ�');
					
					echo json_encode($result);
					exit;
				}
			}

    		$member_info['INVOICE_STATUS'] = $_POST['INVOICE_STATUS'];
            /**
                δ��״̬�������޸�
                ������״̬���������޸�
                �ѿ�δ��״̬�������޸�Ϊ����
                ����״̬���޷��޸�
                ���ջ�״̬���޷��޸�״
		    */    
            $invoicestatus_old = intval($_POST['INVOICE_STATUS_OLD']);

            if($invoicestatus_old == 1 && $member_info['INVOICE_STATUS'] != 1)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬����δ��״̬�������޸�');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 2 && !in_array($member_info['INVOICE_STATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬�����ѿ�δ��״̬��ֻ�����޸�Ϊ����');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 3 && !in_array($member_info['INVOICE_STATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬��������״̬��ֻ�����޸�Ϊ�ѿ�δ��');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 4 && $member_info['INVOICE_STATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬�������ջ�״̬���������޸�');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 5 && ($member_info['INVOICE_STATUS'] != 5 && $member_info['INVOICE_STATUS'] != 1))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��,Υ����Ʊ״̬����������״̬��ֻ���޸�Ϊδ����������״̬');
                
                echo json_encode($result);
                exit;
            }
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            $member_info['TOTAL_PRICE'] = floatval($_POST['TOTAL_PRICE']);

            $total_price_old = floatval($_POST['TOTAL_PRICE_OLD']);
            if($total_price_old != $member_info['TOTAL_PRICE']){

                if($member_info['INVOICE_STATUS'] != 1){
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�����շѱ�׼�޸Ĺ����޸ĵ����շѱ�׼����Ʊ״ֻ̬����δ����');

                    die(@json_encode($result));
                }

                //����δ���ɽ��
                $member_info['UNPAID_MONEY'] = $member_info['TOTAL_PRICE'] - floatval($_POST['PAID_MONEY']) - floatval($_POST['REDUCE_MONEY']);
                //����ȷ��״̬
                $userInfo = $member_model->get_userinfo_by_uid($id);

                $member_pay = D('MemberPay');
                $confirmMoney = $member_pay->get_sum_pay($id,'confirmed');

                if($confirmMoney==0){
                    $member_info['FINANCIALCONFIRM'] = 1;
                }
                else if($confirmMoney + $_POST['REDUCE_MONEY'] < $member_info['TOTAL_PRICE']){
                    $member_info['FINANCIALCONFIRM'] = 2;
                }
                else if($confirmMoney + $_POST['REDUCE_MONEY'] >= $member_info['TOTAL_PRICE']){
                    $member_info['FINANCIALCONFIRM'] = 3;
                }
            }

            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            /**�н�Ӷ��������޸�ʱ���鿴��Ա�Ƿ��Ѿ�������н�Ӷ����**/
            if($member_info['AGENCY_REWARD'] != floatval($_POST['AGENCY_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 3);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�н�Ӷ�������뱨��,�޷��޸�!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
            
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            /**�н�ɽ�������������޸�ʱ���鿴��Ա�Ƿ��Ѿ�������н�ɽ���������**/
            if($member_info['AGENCY_DEAL_REWARD'] != floatval($_POST['AGENCY_DEAL_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 4);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,�н�ɽ����������뱨��,�޷��޸�!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
            $member_info['PROPERTY_DEAL_REWARD'] = floatval($_POST['PROPERTY_DEAL_REWARD']);
            /**��ҵ���ʳɽ�������������޸�ʱ���鿴��Ա�Ƿ��Ѿ��������ҵ���ʳɽ���������**/
            if($member_info['PROPERTY_DEAL_REWARD'] != floatval($_POST['PROPERTY_DEAL_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 6);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,��ҵ���ʳɽ����������뱨��,�޷��޸�!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
    		$member_info['NOTE'] = u2g($_POST['NOTE']);
                $member_info['AGENCY_NAME'] = u2g($_POST['AGENCY_NAME']);
    		$member_info['UPDATETIME'] = date('Y-m-d');
            
            //�н���Դ���쿨״̬���Ѱ���ǩԼ����
            if(($member_info['SOURCE'] == 1 || $member_info['SOURCE'] == 7 || $member_info['SOURCE'] == 8) && $member_info['CARDSTATUS'] == 3 )
            {
                if($member_info['AGENCY_REWARD'] == 0 && $_POST['AGENCY_REWARD_AFTER'] == 0)
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�޸�ʧ��,ǰӶ���ߺ�Ӷ�н�Ӷ�����������дһ��');
                    
                    echo json_encode($result);
                    exit;
                }
            }

    		$update_num = 0;
			$member_info['FILINGTIME']=$_POST['FILINGTIME'];
			$member_info['TOTAL_PRICE_AFTER']=$_POST['TOTAL_PRICE_AFTER'];
			$member_info['AGENCY_REWARD_AFTER']=$_POST['AGENCY_REWARD_AFTER'];
			$member_info['OUT_REWARD']=$_POST['OUT_REWARD'];
			$member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
    		$update_num = $member_model->update_info_by_id($id, $member_info);
    		
    		if($update_num > 0)
    		{   
                if($_POST['CARDSTATUS_OLD'] < $member_info['CARDSTATUS'] && $member_info['CARDSTATUS'] > 2)
                {
                    switch($member_info['CARDSTATUS'])
                    {
                        case '3':
                            $tlfcard_status = 2;
                            $tlfcard_signtime = strtotime($member_info['SIGNTIME']);
                            $tlfcard_backtime = 0;
                            break;
                        case '4':
                            $tlfcard_status = 3; 
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = strtotime($member_info['BACKTIME']);
                            break;
                    }
                    
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = strip_tags($_POST['MOBILENO_HIDDEN']);
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $this->city;
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                    $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].
                    $member_info['CARDTIME'].$conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';
                        
                        $crm_api_arr['floor_id'] = $pro_listid;
                    }
                    
                    submit_crm_data_by_api($crm_api_arr);
                }


                //֪ͨȫ������׼����ϵͳ
                if($_POST['CARDSTATUS_OLD'] != $member_info['CARDSTATUS']){

                    $qltStatus = 3;

                    switch($member_info['CARDSTATUS'])
                    {
                        case '1':
                            $qltStatus = 3;
                            break;
                        case '2':
                            $qltStatus = 4;
                            break;
                        case '3':
                            $qltStatus = 5;
                            break;
                    }

                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$_POST['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltUserInfo = $member_model->get_userinfo_by_uid($id);

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $qltUserInfo['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,$uid,3);
                }

                $result['status'] = 1;
                $result['msg'] = '�޸ĳɹ�';

            }
    		else
    		{
                    $result['status'] = 0;
                    $result['msg'] = '�޸�ʧ��';
    		}
    		
            $result['msg'] = g2u($result['msg']);

            if ($result['forward'] == '' && $_REQUEST['fromUrl']) {
                $result['forward'] = $_REQUEST['fromUrl'];  // ��ת��ַ
            }

    		echo json_encode($result);
    		exit;
    	}
    	//����
    	else if (!empty($_POST) && $faction == 'saveFormData')
        {   
            $member_info = array();
            $member_info['CITY_ID'] = $_POST['CITY_ID'];
            $member_info['PRJ_ID'] = $_POST['PRJ_ID'];
            $member_info['PRJ_NAME'] =  u2g($_POST['PRJ_NAME']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'fx');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            $member_info['CASE_ID'] = $case_id;
            $member_info['REALNAME'] =  u2g($_POST['REALNAME']);
            $member_info['MOBILENO'] = $_POST['MOBILENO'];
            $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            $member_info['SOURCE'] = $_POST['SOURCE'];
            $member_info['CARDTIME'] = $_POST['CARDTIME'];
            $member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            $member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            if($member_info['CERTIFICATE_TYPE'] == 1)
            {
                if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) 
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('���ʧ�ܣ����֤�����ʽ����ȷ��');
                    
                    echo json_encode($result);
                    exit;
                } 
            }

            else if (trim($member_info['CERTIFICATE_NO']) == '') 
            {
                $result['status'] = 0;
                $result['msg'] = g2u('���ʧ�ܣ�֤�����������д��');

                echo json_encode($result);
                exit;
            }
			if($_POST['TOTAL_PRICE']=='' &&  $_POST['TOTAL_PRICE_AFTER']=='' ){
				$result['status'] = 0;
				$result['msg'] = g2u('���ʧ��,��ѡ��ǰӶ�շѱ�׼���ߺ�Ӷ�շѱ�׼��');
				echo json_encode($result);
				exit;

			}elseif($_POST['TOTAL_PRICE_AFTER']==''){
				//$project = D('Project');
				////$case_model = D('ProjectCase');
                //$case_info = $case_model->get_info_by_pid($_POST['PRJ_ID'], 'fx');
                //$case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
                 

				$OUT_REWARD = $project->get_feescale_by_cid_stype($case_id,3, $_POST['OUT_REWARD']);
				if($OUT_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('���ʧ��,�����շѱ�׼ֻ��ǰӶ���ⲿ�ɽ���������Ϊ�ٷֱȣ�');
				}
				$AGENCY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id,4, $_POST['AGENCY_DEAL_REWARD']);
				if($AGENCY_DEAL_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('���ʧ��,�����շѱ�׼ֻ��ǰӶ���н�ɽ���������Ϊ�ٷֱȣ�');
				}
				$PROPERTY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id,5, $_POST['PROPERTY_DEAL_REWARD']);
				if($PROPERTY_DEAL_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('���ʧ��,�����շѱ�׼ֻ��ǰӶ����ҵ���ʳɽ���������Ϊ�ٷֱȣ�');
				}
				  
				if($OUT_REWARD || $AGENCY_DEAL_REWARD ||$PROPERTY_DEAL_REWARD ){
					echo json_encode($result);
					exit;
				}

			}
            if($_POST['TOTAL_PRICE']){
				if(!$_POST['RECEIPTSTATUS']){
					$result['status'] = 0;
					$result['msg'] = g2u('���ʧ��, ��ѡ��ǰӶ�շѱ�׼�ı���ѡ���վ�״̬��');
					echo json_encode($result);
					exit;
				}
				if(!$_POST['RECEIPTNO']){
					$result['status'] = 0;
					$result['msg'] = g2u('���ʧ��, ��ѡ��ǰӶ�շѱ�׼�ı�����д�վݱ�ţ�');
					echo json_encode($result);
					exit;
				}


			}
            $member_info['ROOMNO'] =  u2g($_POST['ROOMNO']);
            $member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            $member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
            
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //���Ϲ�
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if($member_info['SUBSCRIBETIME'] == '' || $member_info['SUBSCRIBETIME'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�Ѱ����Ϲ����Ϲ����ڱ�����д��');
                        
                        echo json_encode($result);
                        exit;
                    }
                break;
                case '3':
                    //��ǩԼ
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    if($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ��ǩԼ���ں�ǩԼ����������д��');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ��¥�����ű�����д��');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�Ѱ���ǩԼ������ʱ�䡢װ��׼������д��');
                        echo json_encode($result);
                        exit;
                    }
                break;
                case '4':
                    //�˿�
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    
                    if($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('���ʧ��,�쿨״̬Ϊ�˿����˿����ں��˿������˱�����д��');
                        echo json_encode($result);
                        exit;
                    }
                break;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            $member_info['RECEIPTNO'] = trim(str_replace(array("��","/","��"),",", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
			if($member_info['RECEIPTNO'] ){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 $result['status'] = 0;
					$result['msg'] = g2u('���ʧ��,�ó������Ѿ�������ͬ���վݱ�ţ�');
					
					echo json_encode($result);
					exit;
				}
			}

            $member_info['INVOICE_STATUS'] = 1; //����Ĭ��δ��
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            //����
            $member_info['ATTACH'] = u2g($_POST['ATTACH']);
            $member_info['TOTAL_PRICE'] = intval($_POST['TOTAL_PRICE']);
            $member_info['PAID_MONEY'] = 0;
            $member_info['UNPAID_MONEY'] = floatval($_POST['TOTAL_PRICE']);
            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            $member_info['PROPERTY_DEAL_REWARD'] = floatval( $_POST['PROPERTY_DEAL_REWARD']);
            
            //�н���Դ
            if($member_info['SOURCE'] == 1 && $member_info['CARDSTATUS'] == 3 )
            {
                if($member_info['AGENCY_REWARD'] == 0  && $_POST['AGENCY_REWARD_AFTER'] == 0)
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('���ʧ��,ǰӶ���ߺ�Ӷ�н�Ӷ�����������дһ��');

                    echo json_encode($result);
                    exit;
                }
            }
            
            $member_info['ADD_UID'] = intval($_SESSION['uinfo']['uid']);
            $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];
            $member_info['NOTE'] =  u2g($_POST['NOTE']);
            $member_info['AGENCY_NAME'] =  u2g($_POST['AGENCY_NAME']);
            $member_info['CREATETIME'] = date('Y-m-d H:i:s');
            $member_info['STATUS'] = 1;
            
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
                //arrival_confirm_log($customer_id, $member_info['REALNAME'], $member_info['MOBILENO'],
                //		$code, $_POST['LIST_ID'], $member_info['PRJ_ID'], $is_from, $is_sucess);
            }
			$member_info['IS_DIS']=2;//����
			$member_info['FILINGTIME']=$_POST['FILINGTIME'];
			$member_info['TOTAL_PRICE_AFTER']=$_POST['TOTAL_PRICE_AFTER'];
			$member_info['AGENCY_REWARD_AFTER']=$_POST['AGENCY_REWARD_AFTER'];
			$member_info['OUT_REWARD']=$_POST['OUT_REWARD'];
			$member_info['REWARD_STATUS']=1;
			$member_info['OUT_REWARD_STATUS']=1;

            /****�Ƿ���Ҫ����ȷ��****/
            $insert_id = $member_model->add_member_info($member_info);
            
            if($insert_id > 0)
            {
                //���Ͷ���
                if(isset($member_info['IS_SMS']) && $member_info['IS_SMS'] == 2 
                    && $member_info['CARDSTATUS'] < 4)
                {
                    $msg = "�𾴵�365��Ա".$member_info['REALNAME']."��"."���Ѱ쿨�ɹ�,�ͷ�����400-8181-365��";
                    send_sms($msg, $member_info['MOBILENO'], $this->city_config_array[$this->channelid]);
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
                            $tlfcard_signtime = strtotime($member_info['SIGNTIME']);
                            $tlfcard_backtime = 0;
                        break;
                        case '4':
                            $tlfcard_status = 3;
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = strtotime($member_info['BACKTIME']);
                        break;
                    }
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $this->city;
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                            $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].$member_info['CARDTIME'].$conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {   
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';
                        
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
            	$result['msg'] = '��ӻ�Աʧ�ܣ�@';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }

        else if($faction == 'delData')
        {   
            //�鿴��Ա�Ƿ���ڲ�����ȷ�ϵĸ�����ϸ��������ڲ�����ɾ��
            $mid = intval($_GET['ID']);
            $update_num = 0;
            
            if($mid > 0)
            {   
                $member_pay = D('MemberPay');
				$members = D('Member');
                $member_pay_info = $member_pay->get_payinfo_by_mid($mid);
                $conf_pay_status = $member_pay->get_conf_status();

                //��ȡ��Ա��Ϣ
                $member_info = $member_model->get_info_by_id($mid);
                
                $confirm_payment_num = 0;
				$confirm_yong_num = 0;
                if(is_array($member_pay_info) && !empty($member_pay_info))
                {
                    foreach($member_pay_info as $key => $value)
                    {
                        if($value['STATUS'] == $conf_pay_status['confirmed'])
                        {
                            $confirm_payment_num ++;
                        }
						 
                    }
                    
                    if($confirm_payment_num > 0)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('ɾ����Աʧ�ܣ����ڲ���ȷ�ϸ�����ϸ');
                        echo json_encode($result);
                        exit;
                    }
                    
                    //ɾ����Ա������ϸ��Ϣ
                    $delete_payment = $member_pay->del_pay_detail_by_mid($mid);
					
                    if($delete_payment > 0)
                    {
                        $income_from = 1;//���̻�Ա֧��
                        $income_model = D('ProjectIncome');
                        //ɾ������
                        foreach($member_pay_info as $key => $value)
                        {
                                $income_model->delete_income_info($member_info['CASE_ID'], $mid, $value['ID'], $income_from);
                        }
                    }
                }
                $ss = $members->check_member_status2($mid); 
				if($ss){
					$result['status'] = 0;
					$result['msg'] = g2u('ɾ����Աʧ�ܣ������������Ӷ�Ļ�Ա');
					echo json_encode($result);
					exit;
				}
                //ɾ����Ա��Ϣ
                $update_num = $member_model->delete_info_by_id($mid);
                
                //ɾ�����
                if($update_num > 0)
                {
                    /***�˿�֪ͨCRM***/
                    if($member_info['CARDSTATUS'] != 4)
                    {   
                        $crm_api_arr = array();
                        $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                        $crm_api_arr['mobile'] = strip_tags($member_info['MOBILENO']);
                        $crm_api_arr['activefrom'] = 104;
                        $crm_api_arr['city'] = $this->city;
                        $crm_api_arr['activename'] =  urlencode($member_info['PRJ_NAME'].
                                '�˿�'. oracle_date_format($member_info['CARDTIME'], 'Y-m-d').$conf_zx_standard[$member_info['DECORATION_STANDARD']]);
                        $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                        $crm_api_arr['tlfcard_status'] = 3;
                        $crm_api_arr['tlfcard_creattime'] = strtotime(oracle_date_format($member_info['CARDTIME'], 'Y-m-d'));
                        $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                        $crm_api_arr['tlfcard_signtime'] = strtotime(oracle_date_format($member_info['SIGNTIME'], 'Y-m-d'));
                        $crm_api_arr['tlfcard_backtime'] = time();
                        $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                        $crm_api_arr['projectid'] = $member_info['PRJ_ID'];

                        if($member_info['CARDSTATUS'] == 3)
                        {
                            $house_info = M('erp_house')->field('PRO_LISTID')->
                                    where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();

                            $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                    intval($house_info['PRO_LISTID']) : '';

                            $crm_api_arr['floor_id'] = $pro_listid;
                        }

                        submit_crm_data_by_api($crm_api_arr);
                    }

                    //��Ա������־
                    $log_info = array();
                    $log_info['OP_UID'] = $uid;
                    $log_info['OP_USERNAME'] = $username;
                    $log_info['OP_LOG'] = 'ɾ����Ա��Ϣ��'.$mid.'��';
                    $log_info['ADD_TIME'] = date('Y-m-d H:i:s');
                    $log_info['OP_CITY'] = $this->channelid;
                    $log_info['OP_IP'] = GetIP();
                    $log_info['TYPE'] = 2;
                    
                    member_opreate_log($log_info);

                    //ȫ������׼����ϵͳ(���״̬)
                    $qltStatus = 6;
                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$member_info['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $member_info['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,intval($_SESSION['uinfo']['uid']),3);

                    $result['status'] = 'success';
                    $result['msg'] = 'ɾ����Ա�ɹ�';
                }
                else
                {	
                    $result['status'] = 'error';
                    $result['msg'] = 'ɾ����Աʧ�ܣ�';
                }
            }
            else 
            {
                $result['status'] = 'error';
                $result['msg'] = '�����쳣��';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else
        {   
            Vendor('Oms.Form');
            $form = new Form();
            
            $form = $form->initForminfo(103);
            $form->SQLTEXT = "(";
            $form->SQLTEXT .= "SELECT DISTINCT * FROM ERP_CARDMEMBER WHERE CITY_ID = '".$this->channelid."' AND STATUS = 1 ";
            
            //�Ƿ��в鿴ȫ����Ȩ��
            if(!$this->p_auth_all)
            {
                $form->SQLTEXT .= " AND PRJ_ID IN "
                    . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 2) ";
            }
            
            //�Ƿ��Լ�����
            if(!$this->p_vmem_all)
            {
                $form->SQLTEXT .= " AND ADD_UID = $uid";
            }
			/*
            //������ϸ���ѯ������ҵ��������
            if(!empty($_REQUEST))
            {   
                //�ض������������ı�������ʽ��ʹ�������ֶ���Ϊ�Ӳ�ѯ����
                $pay_search_filed = array ('RETRIEVAL', 'CVV2', 'MERCHANT_NUMBER', 'TRADE_TIME', 'STATUS');
                
                $cond_where = "";
                for($i = 1 ; $i <= 4 ; $i ++)
                {   
                    if(in_array($_REQUEST['search'.$i], $pay_search_filed))
                    {   
                       //ƴ���Ӳ�ѯSQL
                       $cond_where .=  
                            $form->pjsql($_REQUEST['search'.$i], $_REQUEST['search'.$i.'_s'], $_REQUEST['search'.$i.'_t'], $_REQUEST['search'.$i.'_t_type']);
                       
                       //ƴ���Ӳ�ѯ�󣬷�ҳ��Ҫ�õ�������������װ������������ҳ����ʹ��
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'='.$_REQUEST['search'.$i];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_s='.$_REQUEST['search'.$i.'_s'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t='.$_REQUEST['search'.$i.'_t'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t_type='.$_REQUEST['search'.$i.'_t_type'];
                       
                       unset($_POST['search'.$i]);
                       unset($_POST['search'.$i.'_s']);
                       unset($_POST['search'.$i.'_t']);
                       unset($_POST['search'.$i.'_t_type']);
                       
                       unset($_GET['search'.$i]);
                       unset($_GET['search'.$i.'_s']);
                       unset($_GET['search'.$i.'_t']);
                       unset($_GET['search'.$i.'_t_type']);
                    }
                }
                
                if($cond_where != '')
                {
                    $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID FROM ERP_MEMBER_PAYMENT WHERE STATUS != 4 ".$cond_where.")";
                }
            }
			*/
			///////////////

			//������ϸ���ѯ������ҵ��������
            if(!empty($_REQUEST))
            {   
                //�ض������������ı�������ʽ��ʹ�������ֶ���Ϊ�Ӳ�ѯ����
                $pay_search_filed = array ('RETRIEVAL', 'CVV2', 'MERCHANT_NUMBER', 'TRADE_TIME', 'STATUS','REFUND_TIME','REFUND_APPLY_TIME');
                
                $cond_where = "";
                for($i = 1 ; $i <= 4 ; $i ++)
                {   
                    if(in_array($_REQUEST['search'.$i], $pay_search_filed))
                    {   
                       //ƴ���Ӳ�ѯSQL
                        if($_REQUEST['search'.$i] == "REFUND_APPLY_TIME"){
                            $_REQUEST['search'.$i] = "CREATETIME";
                        }
                       $cond_where .=  
                            $form->pjsql($_REQUEST['search'.$i], $_REQUEST['search'.$i.'_s'], $_REQUEST['search'.$i.'_t'], $_REQUEST['search'.$i.'_t_type']);
                       
                       //ƴ���Ӳ�ѯ�󣬷�ҳ��Ҫ�õ�������������װ������������ҳ����ʹ��
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'='.$_REQUEST['search'.$i];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_s='.$_REQUEST['search'.$i.'_s'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t='.$_REQUEST['search'.$i.'_t'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t_type='.$_REQUEST['search'.$i.'_t_type'];
                       
                       unset($_POST['search'.$i]);
                       unset($_POST['search'.$i.'_s']);
                       unset($_POST['search'.$i.'_t']);
                       unset($_POST['search'.$i.'_t_type']);
                       
                       unset($_GET['search'.$i]);
                       unset($_GET['search'.$i.'_s']);
                       unset($_GET['search'.$i.'_t']);
                       unset($_GET['search'.$i.'_t_type']);
                    }
                }
                
                if($cond_where != '')
                {
                    if($_REQUEST['search1'] == "CREATETIME" or $_REQUEST['search2'] == "CREATETIME" or
                    $_REQUEST['search3'] == "CREATETIME" or $_REQUEST['search4'] == "CREATETIME"){
                        $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID  FROM ERP_MEMBER_REFUND_DETAIL WHERE 1=1 " . $cond_where . ")";
                    }else {
                        $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID FROM ERP_MEMBER_PAYMENT WHERE STATUS != 4 " . $cond_where . ")";
                    }
                }
            }
			///////////////
            $form->SQLTEXT .= " AND IS_DIS=2 )";
            //echo $form->SQLTEXT;
            //�ֻ���������
            $form->setMyField('MOBILENO', 'ENCRY', '4,8', FALSE);
            $form->setMyField('LOOKER_MOBILENO', 'ENCRY', '4,8', FALSE);
			$form->setMyField('TOTAL_PRICE', 'FIELDMEANS', 'ǰӶ�շѱ�׼', FALSE);
			//$form->setMyField('TOTAL_PRICE', 'ISVIRTUAL', '-1', FALSE);
			$form->setMyField('AGENCY_REWARD', 'FIELDMEANS', 'ǰӶ�н�Ӷ��', FALSE);
			//$form->setMyField('AGENCY_REWARD', 'ISVIRTUAL', '-1', FALSE);
			$form->setMyField('FILINGTIME', 'FORMVISIBLE', '-1', FALSE);

            //ֱ����Ա
            $form->setMyField('DIRECTSALLER','FORMVISIBLE',-1);
            $form->setMyField('DIRECTSALLER','GRIDVISIBLE',-1);
            //����֤������
            $certificate_type_arr = $member_model->get_conf_certificate_type();
            $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR', 
            		array2listchar($certificate_type_arr), FALSE);

            //֤������
            $form->setMyField('CERTIFICATE_NO', 'ENCRY', '4,13', FALSE);
            
            //���ø��ʽ
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', 
	        		array2listchar($pay_arr), FALSE);
	        
			$form->GABTN = "<a id='refund_by_mid' href='javascript:;' class='btn btn-info btn-sm'>
				�����˿�
			</a>
			<a id='apply_invoice' href='javascript:;' class='btn btn-info btn-sm'>
				���뿪Ʊ
			</a>
			<a onclick='discount();' href='javascript:;' class='btn btn-info btn-sm' id='apply_discount'>
				�������
			</a>
			<a onclick='recycle_invoice();' href='javascript:;' class='btn btn-info btn-sm' id='recycle_invoice'>
				������Ʊ
			</a>
			<a onclick='change_invoice()' href='javascript:;' class='btn btn-info btn-sm' id='change_invoice'>
				���뻻��Ʊ
			</a>
			<a id='agency_reward_reim' href='javascript:;' class='btn btn-info btn-sm'>
				�н�Ӷ����
			</a>
			<a id='agency_deal_reward_reim' href='javascript:;' class='btn btn-info btn-sm'>
				�н�ɽ���������
			</a>
			<a id='property_deal_reward_reim' href='javascript:;' class='btn btn-info btn-sm'>
				��ҵ�ɽ���������
			</a>
			<a id='out_reward_reim' href='javascript:;' class='btn btn-info btn-sm'>
				�ⲿ�ɽ���������
			</a>
			<a id='download_member' href='javascript:;' class='btn btn-info btn-sm'>
				��Ա����
			</a>
			<a id='import_member' href='javascript:;' class='btn btn-info btn-sm'>
				��Ա����
			</a>
			<a id='view_memberinfo' href='javascript:;' class='btn btn-info btn-sm'>
				�鿴��Ա
			</a>
			<a id='batch_change_status' href='javascript:;' class='btn btn-info btn-sm'>
				���������Ϣ
			</a><a id='pro_refund' href='javascript:;' class='btn btn-info btn-sm'>
				�����˷�
			</a>
			<a  id='pro_post_commission'  href='javascript:;' class='btn btn-info btn-sm'>
				�����Ӷ
			</a>
			<a  id='move_member_prj'  href='javascript:;' class='btn btn-info btn-sm'>
				��Ŀת��
			</a>";
	        //�쿨״̬
            $card_status_arr = $status_arr['CARDSTATUS'];
            if($showForm == 3)
            {
                array_pop($card_status_arr); 

            }
	        $form->setMyField('CARDSTATUS', 'LISTCHAR', 
	        		array2listchar($card_status_arr), FALSE);
            
            $current_time = date('Y-m-d');
            if($showForm == 3)
            {   
                //����ʱ��չʾ�쿨ʱ��Ĭ��
               // $form->setMyFieldVal('CARDTIME', $current_time, false);
            }
	        
	        //��Ʊ״̬
            if($showForm == 3)
            {   
                //����ҳ�淢Ʊֻ����Ĭ��Ϊδ��
                $form->setMyFieldVal('INVOICE_STATUS', '1', TRUE );
                $form->setMyField('INVOICE_STATUS', 'LISTCHAR', 
	        		array2listchar($status_arr['INVOICE_STATUS']), TRUE);
                
                $form->setMyFieldVal('INVOICE_STATUS', '1', TRUE );
            }
            else
            {
                $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                        array2listchar($status_arr['INVOICE_STATUS']), FALSE);
            }
            
            //֧����Ϣ����ȷ��״̬��֧����������ɾ��
            $conf_pay_status = $member_pay->get_conf_status_remark();
            $form->setMyField('STATUS', 'LISTCHAR',
                        array2listchar($conf_pay_status), FALSE);
	        
	        //�վ�״̬
            $receipt_status_arr = $status_arr['RECEIPTSTATUS'];
            if($showForm == 3)
            {
                array_pop($receipt_status_arr); 
            }
	        $form->setMyField('RECEIPTSTATUS', 'LISTCHAR', 
	        		array2listchar($receipt_status_arr), FALSE);
            
            //��ҳ��
            /***
             *   $showForm 1: �༭
             *   $showForm 2: �鿴
             *   $showForm 3: ����
             */
            if($showForm == 1 || $showForm == 3 || $showForm == 2 )
            {   
                //�޸ļ�¼ID
                $modify_id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;

                //���þ�������Ϣ
                $userinfo = array();
                $form->setMyFieldVal('ADD_USERNAME', $username, TRUE);

                if($modify_id > 0)
                {
                    $search_field = array('PRJ_ID', 'CASE_ID','ADD_USERNAME');
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
                            $dw = $value['STYPE']?'%':'Ԫ';
                            if ($value['AMOUNT'][0] == '.') {
                                $value['AMOUNT'] = '0' . $value['AMOUNT'];
                            }
							if($value['SCALETYPE']==1 || $value['SCALETYPE']==2){
								
								if($value['MTYPE'])$fees_arr[$value['SCALETYPE'].'_'.$value['MTYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
								else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;


							}else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
                        }

                        //���þ�����(�༭״̬�����ֲ���)
                        $form->setMyFieldVal('ADD_USERNAME', $userinfo['ADD_USERNAME'], TRUE);
                        //�����շѱ�׼
                        $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
						$form->setMyField('TOTAL_PRICE_AFTER', 'LISTCHAR', array2listchar($fees_arr['1_1']), FALSE);
                        //�н�Ӷ��
                        $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
						  $form->setMyField('AGENCY_REWARD_AFTER', 'LISTCHAR', array2listchar($fees_arr['2_1']), FALSE);
                        //��ҵ����Ӷ��  �ⲿ����
                        $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //�н�ɽ���
                        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //��ҵ�ɽ�����
                        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
                    }
                    
                    //��ԱMODEL
                    $member_model = D('Member');
                    $member_info = $member_model->get_info_by_id($modify_id, array('CITY_ID', 'PRJ_ID','CASE_ID','MOBILENO'));
                    
                    if(is_array($member_info) && !empty($member_info))
                    {
                        $input_arr = array(
                                array('name' => 'CITY_ID', 'val' => $member_info['CITY_ID'], 'id' => 'CITY_ID'),
                                array('name' => 'PRJ_ID', 'val' => $member_info['PRJ_ID'], 'id' => 'PRJ_ID'),
                                array('name' => 'CASE_ID', 'val' => $member_info['CASE_ID'], 'id' => 'CASE_ID'),
                                array('name' => 'MOBILENO_HIDDEN', 'val' => $member_info['MOBILENO'], 'id' => 'MOBILENO_HIDDEN'),
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
                    $form->addHiddenInput($input_arr);
                }
            }
            else 
            {	
            	/***�б�ҳ��������***/
            	//�����
                $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                //�����շѱ�׼
                $form->setMyField('TOTAL_PRICE', 'EDITTYPE',"1", TRUE);
				$form->setMyField('TOTAL_PRICE_AFTER', 'EDITTYPE',"1", TRUE);
				$form->setMyField('AGENCY_REWARD_AFTER', 'EDITTYPE',"1", TRUE);
				$form->setMyField('OUT_REWARD', 'EDITTYPE',"1", TRUE);
                //����ȷ��״̬
                $form->setMyField('FINANCIALCONFIRM', 'LISTCHAR', array2listchar($status_arr['FINANCIALCONFIRM']), FALSE);
            }

            //���û�Ա��Դ
            $source_arr = $member_model->get_conf_member_source_remark();

            //�༭��������ĿID��ѯ��Ŀ�ֽ�Ŀ�����۷�ʽ
            if($showForm == 1)
            {   
                //���û�Ա��Դ(�޸�ʱ��Ҫ��ǰ��Ա��Ŀ��Ϣ��ע�����λ��)
                $prj_id =  !empty($userinfo['PRJ_ID']) ? intval($userinfo['PRJ_ID']) : 0;
                //��Ա��Դ
                $source_arr = $member_model->getPrjSaleMethod($prj_id);
            }
            else if($showForm == 2)
            {
                //���û�Ա��Դ
                $source_arr = $member_model->get_conf_member_source_remark();

                //��ԱMODEL
                $id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;
                $member_model = D('Member');
                $member_info = $member_model->get_info_by_id($id, array('ADD_USERNAME'));

                //���������
                $form->setMyFieldVal('ADD_USERNAME', $member_info['ADD_USERNAME'], TRUE);
            }

            /**
             *  �����������Ա������Ӧ�ı������ã�ֱ�Ӷ�ȡ
             */
            if($showForm==3){

                //��������ID --- ������Ա�����ظ��޸�
                $user_config = $member_model->get_user_config('DISMEMBER_ADD',$this->uid);
                $user_config = unserialize($user_config);

                if($user_config){
                    //�����շѱ�׼
                    $case_id =  !empty($user_config['CASE_ID']) ? intval($user_config['CASE_ID']) : 0;
                    $feescale = D('Project')->get_feescale_by_cid($case_id);
  //var_dump( $feescale);
                    if(is_array($feescale) && !empty($feescale) )
                    {
                        foreach($feescale as $key => $value)
                        {  
						   $dw = $value['STYPE']  ? '%' : 'Ԫ';
                            if ($value['AMOUNT'][0] == '.') {
                                $value['AMOUNT'] = '0' . $value['AMOUNT'];
                            }
							if($value['SCALETYPE']==1 || $value['SCALETYPE']==2){
								
								if($value['MTYPE'])$fees_arr[$value['SCALETYPE'].'_'.$value['MTYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
								else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;


							}else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
                        }

                        //���þ�����(�༭״̬�����ֲ���)
                        $form->setMyFieldVal('ADD_USERNAME', $user_config['ADD_USERNAME'], TRUE);
                        //�����շѱ�׼
                        $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //�н�Ӷ��
                        $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
						  $form->setMyField('AGENCY_REWARD_AFTER', 'LISTCHAR', array2listchar($fees_arr['2_1']), FALSE);
                        //��ҵ����Ӷ��
                        //$form->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
						//��Ӷ
						$form->setMyField('TOTAL_PRICE_AFTER', 'LISTCHAR', array2listchar($fees_arr['1_1']), FALSE);
						 
                        //�н�ɽ���
                        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //��ҵ�ɽ�����
                        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
						$form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                    }

                    //���¸�ֵ
					$form->setMyFieldVal('AGENCY_REWARD_AFTER', $user_config['AGENCY_REWARD_AFTER'], FALSE);
					$form->setMyFieldVal('OUT_REWARD', $user_config['OUT_REWARD'], FALSE);
                    $form->setMyFieldVal('PRJ_NAME', $user_config['PRJ_NAME'], FALSE);
                    $form->setMyFieldVal('SOURCE', $user_config['SOURCE'], FALSE);
                    $form->setMyFieldVal('CERTIFICATE_TYPE', $user_config['CERTIFICATE_TYPE'], FALSE);
                    $form->setMyFieldVal('CARDSTATUS', $user_config['CARDSTATUS'], FALSE);
                    $form->setMyFieldVal('SIGNEDSUITE', $user_config['SIGNEDSUITE'], FALSE);
                    $form->setMyFieldVal('RECEIPTSTATUS', $user_config['RECEIPTSTATUS'], FALSE);
                    $form->setMyFieldVal('IS_TAKE', $user_config['IS_TAKE'], FALSE);
                    $form->setMyFieldVal('IS_SMS', $user_config['IS_SMS'], FALSE);
                    $form->setMyFieldVal('TOTAL_PRICE', $user_config['TOTAL_PRICE'], FALSE);
					$form->setMyFieldVal('TOTAL_PRICE_AFTER', $user_config['TOTAL_PRICE_AFTER'], FALSE);
                    $form->setMyFieldVal('UNPAID_MONEY', $user_config['UNPAID_MONEY'], TRUE);
                    $form->setMyFieldVal('PAID_MONEY', $user_config['PAID_MONEY'], TRUE);
                    $form->setMyFieldVal('AGENCY_REWARD', $user_config['AGENCY_REWARD'], FALSE);
                    $form->setMyFieldVal('AGENCY_DEAL_REWARD', $user_config['AGENCY_DEAL_REWARD'], FALSE);
                    $form->setMyFieldVal('PROPERTY_DEAL_REWARD', $user_config['PROPERTY_DEAL_REWARD'], FALSE);
                    $form->setMyFieldVal('LEAD_TIME', $user_config['LEAD_TIME'], FALSE);
                    $form->setMyFieldVal('DECORATION_STANDARD', $user_config['DECORATION_STANDARD'], FALSE);

					$form->setMyFieldVal('FILINGTIME', date("Y-m-d"), FALSE);
                    $input_arr = array(
                        array('name' => 'CITY_ID', 'val' => $user_config['CITY_ID'], 'id' => 'CITY_ID'),
                        array('name' => 'PRJ_ID', 'val' => $user_config['PRJ_ID'], 'id' => 'PRJ_ID'),
                        array('name' => 'CASE_ID', 'val' => $user_config['CASE_ID'], 'id' => 'CASE_ID'),
                    );
                    $form = $form->addHiddenInput($input_arr);

                    //��Ա��Դ
                    $source_arr = $member_model->getPrjSaleMethod($user_config['PRJ_ID']);
                }
            }

			//$form->setMyFieldVal('FILINGTIME', date("Y-m-d"), FALSE);
            $form->setMyField('SOURCE', 'LISTCHAR', array2listchar($source_arr), FALSE);
            $form->setMyField('TOTAL_PRICE_AFTER', 'GRIDVISIBLE', '-1', FALSE);
			$form->setMyField('RECEIPTSTATUS', 'NOTNULL', '0', FALSE);
			$form->setMyField('RECEIPTNO', 'NOTNULL', '0', FALSE);
			$form->setMyField('CARDTIME', 'NOTNULL', '0', FALSE);
			$form->setMyField('TOTAL_PRICE', 'NOTNULL', '0', FALSE);
			$form->setMyField('REWARD_STATUS', 'GRIDVISIBLE', '-1', FALSE);
			$form->setMyField('REWARD_STATUS', 'FORMVISIBLE', '-1', FALSE);
			$form->setMyField('REWARD_STATUS', 'FILTER', '-1', FALSE);
			$form->setMyField('REWARD_STATUS', 'SORT', '-1', FALSE);
			$form->setMyField('TOTAL_PRICE_AFTER', 'FILTER', '-1', FALSE);
			$form->setMyField('TOTAL_PRICE_AFTER', 'SORT', '-1', FALSE);
			$form->setMyField('AGENCY_REWARD_AFTER', 'FILTER', '-1', FALSE);
			$form->setMyField('AGENCY_REWARD_AFTER', 'SORT', '-1', FALSE);
			$form->setMyField('OUT_REWARD_STATUS', 'FILTER', '-1', FALSE);
			$form->setMyField('OUT_REWARD_STATUS', 'SORT', '-1', FALSE);
			$form->setMyField('OUT_REWARD', 'FILTER', '-1', FALSE);
			$form->setMyField('OUT_REWARD', 'SORT', '-1', FALSE);
			$form->setMyField('FILINGTIME', 'FILTER', '-1', FALSE);
			$form->setMyField('FILINGTIME', 'SORT', '-1', FALSE);

            //װ�ޱ�׼
            $form->setMyField('DECORATION_STANDARD', 'LISTCHAR', array2listchar($conf_zx_standard), FALSE);

            //��ʾ���浱ǰ���ð�ť (��� + �༭)
            if($showForm==1 || $showForm==3)
                $form->showSaveCfg = true;
            
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

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
            $formhtml =  $form->setChildren($children_data)->getResult();

            $this->assign('form', $formhtml);
            $this->assign('showForm', $showForm);
            //�����������
            $this->assign('filter_sql',$form->getFilterSql());
            //�����������
            $this->assign('sort_sql',$form->getSortSql());
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
			$this->assign('case_type','fx');
            $this->display('reg_member');
        }
    }


    /**
    +----------------------------------------------------------
     * ��Ա��Ŀת��
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
    */
    public function moveProject(){

        //���ؽ����
        $return = array(
            'status' => false,
            'msg' => '',
            'data' => null,
        );

        $memberIdsStr = isset($_REQUEST['memberIds'])?$_REQUEST['memberIds']:0; //ת�ƶ���
        $fromCaseId = isset($_REQUEST['fromCaseId'])?$_REQUEST['fromCaseId']:0; //��Դ��Ŀ
        $toCaseId = isset($_REQUEST['toCaseId'])?$_REQUEST['toCaseId']:0; //Ŀ����Ŀ

        $isCheck = isset($_REQUEST['isCheck'])?intval($_REQUEST['isCheck']):0; //�Ƿ��Ǽ������
        $showWindow = isset($_REQUEST['showWindow'])?$_REQUEST['showWindow']:0; //չ��

        $memberIds = explode(",",$memberIdsStr);

        //������֤
        if(empty($memberIds) || count($memberIds) < 1){
            $return['msg'] = '�Բ����ף���ѡ������һ����¼���в���!';
            die(@json_encode(g2u($return)));
        }

        //��Ŀ��֤
        $sql = 'select DISTINCT CASE_ID from erp_cardmember where id in(' . $memberIdsStr .  ')';
        $queryRet = D('Erp_cardmember')->query($sql);

        if(is_array($queryRet) && count($queryRet) > 1){
            $return['msg'] = '�Բ����ף���ѡ��ͬһ����Ŀ���в���!';
            die(@json_encode(g2u($return)));
        }

        //�����뱨����Ӷ�н�Ӷ�𣨻��ѱ��������ܽ�����Ŀת��
        foreach($memberIds as $member){
            $agencyStatus = M("Erp_cardmember")->where("ID=".$member)->getField('AGENCY_REWARD_STATUS');
            $agencyResultSql = "select d.pencent  from erp_commission_invoice_detail d
            LEFT JOIN erp_post_commission c on c.id = d.post_commission_id where c.card_member_id = ".$member;
            $agencyResult = D()->query($agencyResultSql);
            if(notEmptyArray($agencyResult)){
                $return['msg'] = '�Բ����ף����Ϊ'.$member.'�����뱨����Ӷ�н�Ӷ�𣨻��ѱ��������ܽ�����Ŀת��!';
                die(@json_encode(g2u($return)));
            }
            //��Ա��Ʊ��ϸ��״̬Ϊδ����������У���Ӧ�ò�����ת��
            $invoiceResultSql = "select d.invoice_status  from erp_commission_invoice_detail d
            LEFT JOIN erp_post_commission c on c.id = d.post_commission_id where c.card_member_id = ".$member;
            $invoiceResult = D()->query($invoiceResultSql);
            if(notEmptyArray($invoiceResult)){
                $return['msg'] = '�Բ����ף����Ϊ'.$member.'�����뿪Ʊ��Ա���ܽ�����Ŀת��!';
                die(@json_encode(g2u($return)));

            }
        }

        //����Ǽ������
        if($isCheck){
            $return['status'] = true;
            die(@json_encode(g2u($return)));
        }

        //��չ��
        if($showWindow){

            $fromCaseId = $queryRet[0]['CASE_ID'];

            //��ȡtype����
            $caseTypePY = D('ProjectCase')->get_casetype_by_caseid($fromCaseId);
            $caseTypes = D('ProjectCase')->get_conf_case_type();
            $caseType = $caseTypes[$caseTypePY];

            $caseTypeRemark = D('ProjectCase')->get_conf_case_type_remark();

            //��ȡ��Ŀ��Ϣ
            $sql = 'select PROJECTNAME from erp_project P left join erp_case C ON P.id = C.project_id where C.id = ' . $fromCaseId;
            $projectInfo = D()->query($sql);

            //��ȡ��Ŀ��Ϣ
            $sql = 'SELECT C.ID,P.PROJECTNAME,P.CONTRACT FROM ERP_PROJECT P LEFT JOIN ERP_CASE C ON P.ID = C.PROJECT_ID WHERE P.PSTATUS = 3 AND P.STATUS != 2 AND PROJECTNAME IS NOT NULL ';
            $sql .= ' AND C.SCALETYPE = ' . $caseType;
            $sql .= ' AND P.CITY_ID = ' . $this->channelid;
            $allPro = D()->query($sql);

            $this->assign('memberCount',count($memberIds)); //��Ա����
            $this->assign('memberIds',$memberIdsStr); //ת�ƻ�Ա
            $this->assign('fromProjectName',$projectInfo[0]['PROJECTNAME']);  //��Ŀ����
            $this->assign('allPro',$allPro);  //��Ŀ
            $this->assign('fromCaseId',$fromCaseId);  //��ԴCASEID
            $this->assign('caseType',$caseTypeRemark[$caseType]);

            $this->display('moveProject');

            exit();
        }

        //��Ŀת��
        if(is_array($memberIds) && $fromCaseId && $toCaseId) {
            $errorStr = '';
            $memberObj = D('Member');

            $memberObj->startTrans();

            //ѭ������
            foreach ($memberIds as $key => $val) {
                $convertReturn = $memberObj->convertMember($fromCaseId, $toCaseId ,$val);
                if (!$convertReturn['status']) {
                    $errorStr .= $convertReturn['msg'] . "<br />";
                }
            }

            //���ؽ����
            if ($errorStr == '') {
                $return['status'] = true;
                $return['msg'] = '�ף�ת����Ŀ�ɹ�!';
                $memberObj->commit();
            } else {
                $return['status'] = false;
                $return['msg'] = "�Բ����ף����������⵼��ʧ��:<br />" . $errorStr;
                $memberObj->rollback();
            }
        }

        die(@json_encode(g2u($return)));

    }

	 /**
    +----------------------------------------------------------
    * �˷�����
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function return_member(){
		//
		if($_REQUEST['faction']=='getfeescale'){
			$memberId_arr = array();
            $memberId_arr = $_REQUEST['memberId'];
			foreach($memberId_arr as $one)
			{
				$temp = M('Erp_cardmember')->where('ID='.$one)->find();
				$dw = D('Project')->get_feescale_by_cid_val2($temp['CASE_ID'],1,$temp['TOTAL_PRICE_AFTER'],1);
				$temp2['memberid'] = $one;
				$temp2['dw'] = $dw[0]['STYPE']==1 ?'%':' Ԫ';
				$temp2['dw'] = g2u($temp2['dw']);
				$res[] = $temp2;
			}
			$result['status'] = 1;
			$result['data'] = $res ;
			echo json_encode($result);
			exit;

			
			
		}
		if($_REQUEST['faction'] == 'delData')
        {
            $member = D("Member");
			$temp['REWARD_STATUS']=1;
			$del_result = $member->where("ID=".$_REQUEST['ID'])->save($temp);
            if($del_result)
            {
                $info['status']  = 'success';
                $info['msg']  = g2u('ɾ���ɹ�');
            }
            else
            {
                $info['status']  = 'error';
                $info['msg']  = g2u('ɾ��ʧ��');
            }
            
            echo json_encode($info);
            exit;
        }
		if($_REQUEST['refund_method']=='mid'){
			$member = D("Member");
			$memberId_arr = array();
            $memberId_arr = $_REQUEST['memberId'];
			$rr = $member->check_member_status($memberId_arr);
			
			if($rr && $_REQUEST['re_type']=='pro'){
				$info['state']  = 0;
                $info['msg']  ='��ѡ�ͻ�״̬���������˷����������������Ӷ���ѽ�Ӷ���������˷������˷���';
				$info['msg'] = g2u($info['msg']);
				echo json_encode($info);
				exit;
			}
			$rr2 = $member->check_member_yong($memberId_arr);
			if($rr2 && $_REQUEST['re_type']=='pro'){
				$info['state']  = 0;
                $info['msg']  ='��ѡ�ͻ��б����к�Ӷ�շѱ�׼���������˷���';
				$info['msg'] = g2u($info['msg']);
				echo json_encode($info);
				exit;
			}
			$status = $_REQUEST['STATUS'] ? $_REQUEST['STATUS']:5;
			if($status==5 && $_REQUEST['re_type']=='tuifang'){
				$rr3 = $member->check_member_status3($memberId_arr);
				if($rr3){
					$info['state']  = 0;
					$info['msg']  ='ʧ��,���˷��Ĳ�������ȷ���˷���';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
			}
			$res = $member->set_member_status($memberId_arr,$status);
			if($res){
				$info['state']  = 1;
                $info['msg']  ='ִ�гɹ�';
				if($status==5){ 
					$member->submit_member_crm($memberId_arr,$this->city);
				}
			}else{
				$info['state']  = 0;
                $info['msg']  ='ʧ��';
			}
			$info['msg'] = g2u($info['msg']);
			echo json_encode($info);
			exit;

		}else{
			$uid = intval($_SESSION['uinfo']['uid']);
			Vendor('Oms.Form');
			$form = new Form(); 
			$form = $form->initForminfo(208);
			 $form->SQLTEXT = "(";
            $form->SQLTEXT .= "SELECT DISTINCT * FROM ERP_CARDMEMBER WHERE CITY_ID = '".$this->channelid."' AND STATUS = 1 ";
            //����״̬����ɾ����ť�Ƿ���ʾ
            $form->DELCONDITION = '%REWARD_STATUS% <> 5';
            //�Ƿ��в鿴ȫ����Ȩ��
            if(!$this->p_auth_all)
            {
                $form->SQLTEXT .= " AND PRJ_ID IN "
                    . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 2) ";
            }
            
            //�Ƿ��Լ�����
            if(!$this->p_vmem_all)
            {
                $form->SQLTEXT .= " AND ADD_UID = $uid";
            }
			$form->SQLTEXT .= " AND��REWARD_STATUS in(4,5)  )";
			//$form->where("REWARD_STATUS in(4,5) AND CITY_ID = '".$this->channelid."'");
			$formhtml = $form->getResult();
			$this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
			$this->assign('form', $formhtml);
			$this->display('return_member');
		}

	}
	 /**
    +----------------------------------------------------------
    * �����Ӷ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function post_commission(){
		if($_REQUEST['refund_method']=='mid'){
			$commission = D("Erp_post_commission");
			$mid = $_REQUEST['memberId'];
			$member = D("Member");
			$rr = $member->check_member_status($mid,2);
			if($rr ){
				$info['state']  = 0;
                $info['msg']  ='��ѡ�ͻ�״̬���������Ӷ���������������Ӷ���ѽ�Ӷ���������˷������˷���';
				$info['msg'] = g2u($info['msg']);
				echo json_encode($info);
				exit;
			}

			if(is_array($mid) && !empty($mid))
			{
				$mid_str = implode(',', $mid);
				$cond_where = "CARD_MEMBER_ID IN (".$mid_str.")";
				$cond_where2=  "ID IN (".$mid_str.")";
			}
			else 
			{   
				$midid = intval($mid);
				$cond_where = "CARD_MEMBER_ID = '".$midid."'";
				$cond_where2 = "ID = '".$midid."'";

			}
			$list = $commission->where($cond_where)->select(); 
			if(!$list){
				$mlist = D('Erp_cardmember')->where($cond_where2)->select();
				$flag =  true;
				foreach($mlist as $one){
					if(!($one['TOTAL_PRICE_AFTER'] && $one['CARDSTATUS']==3 )){
						$flag = false;
						break;
					}
				}
				if($flag){
					foreach($mid as $value){
						$temp = array();
						$temp['CARD_MEMBER_ID'] = $value;
						$temp['INVOICE_STATUS']  = 1;
						$temp['PAYMENT_STATUS']  = 1;
						$temp['POST_COMMISSION_STATUS']  = 1;
						$temp['CREATETIME'] = date('Y-m-d H:i:s');
						 
						$res = $commission->add($temp);  

						
					}
					if($res){
						$res2 = $member->set_member_status($mid,2);
						$info['state']  = 1;
						$info['msg']  ='����ɹ�';
					}else{
						$info['state']  = 0;
						$info['msg']  ='ʧ��';
					}
				}else{
					$info['state']  = 0;
					$info['msg']  ='��ѡ��Ա�쿨״̬����Ϊ�Ѱ���ǩԼ�Ҵ��ں�Ӷȡ��';
				}
			}else{
				$info['state']  = 0;
                $info['msg']  ='�벻Ҫ�ظ�����';
			}

			
			$info['msg'] = g2u($info['msg']);
			echo json_encode($info);
			exit;

		}

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
    public function apply_view_memberinfo()
    {
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = strip_tags($_SESSION['uinfo']['tname']);
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        
        $mid_str = isset($_GET['memberid']) ? strip_tags($_GET['memberid']) : "";
        
        Vendor('Oms.Form');
        $form = new Form();
        
        $form = $form->initForminfo(185);
        
        if(!empty($mid_str))
        {   
            $form->SQLTEXT = "(";
            $form->SQLTEXT .= "SELECT DISTINCT * FROM ERP_CARDMEMBER WHERE CITY_ID = '".$this->channelid."' AND ID IN (".$mid_str.") AND STATUS = 1 ";
            
            //�Ƿ��в鿴ȫ����Ȩ��
            if(!$this->p_auth_all)
            {
                $form->SQLTEXT .= " AND PRJ_ID IN "
                    . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 1) ";
            }
            $form->SQLTEXT .= ")";
            
            if($showForm == '')
            {
                $log_info = array();
                $log_info['OP_UID'] = $uid;
                $log_info['OP_USERNAME'] = $username;
                $log_info['OP_LOG'] = '�鿴��Ա��Ϣ��'.$mid_str.'��';
                $log_info['ADD_TIME'] = date('Y-m-d H:i:s');
                $log_info['OP_CITY'] = $this->channelid;
                $log_info['OP_IP'] = GetIP();
                $log_info['TYPE'] = 1;

                member_opreate_log($log_info);
            }
        }
        else
        {
            $form->where('1= 0');
        }
        
        //ʵ������ԱMODEL
    	$member_model = D('Member');
        
        //����֤������
        $certificate_type_arr = $member_model->get_conf_certificate_type();
        $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR', array2listchar($certificate_type_arr), FALSE);
        //
        //�����
        $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        $form =  $form->getResult();
        $this->assign('form', $form);
        $this->display('apply_view_memberinfo');
    }
    
    
    //������־
    public function show_operate_log()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(186);
        $formHtml =  $form->where('OP_CITY='.$this->channelid)->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����ϴμ����Ľ��
        $this->assign('form', $formHtml);
        $this->display('show_operate_log');
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
        //���̻�Ա֧��
	    $income_from = 1;
        $member_pay = D('MemberPay');
		$member_model = D('Member');
		//�ж��Ƿ�ֻ�к�Ӷ
		if($_REQUEST['showForm']==3){
			$caseinfo = array();
			$case_model = D('ProjectCase');
            $caseinfo = $case_model->get_info_by_pid($_REQUEST['parentchooseid'], 'fx');
			$case_id = !empty($caseinfo[0]['ID']) ? intval($caseinfo[0]['ID']) : 0;
			$project = D('Project');
            $dtsf_front= $project->get_feescale_by_cid($case_id, null, null,0)?1:0;
			$mem = $member_model->where("ID=$mid")->find();
			$is_dis = $mem['IS_DIS'];
			if( $is_dis==2 && !$mem['TOTAL_PRICE'] ){
				$result['status'] = 0;
                $result['msg'] =  g2u('�÷�����Ŀֻ�С���Ӷ��ȡ��ģʽ���޷�����������ϸ��');
                echo json_encode($result);
				//js_alert('�÷�����Ŀֻ�С���Ӷ��ȡ��ģʽ���޷�����������ϸ��');
                exit;
			}
		}
        
        //���֧����Ϣ
        if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id == 0)
        {   
            //��ѯ��Ա��Ϣ
            //$member_model = D('Member');
            $member_info = array();
            $search_field = array( 'CASE_ID', 'TOTAL_PRICE', 
                'REDUCE_MONEY', 'UNPAID_MONEY', 'FINANCIALCONFIRM');
            $member_info = $member_model->get_info_by_id($mid, $search_field);
            $trade_money = floatval($this->_post('TRADE_MONEY'));
            
            if($trade_money > $member_info['UNPAID_MONEY'])
            {
                $result['status'] = 0;
                $result['msg'] =  g2u('���ʧ�ܣ�֧��������δ���ɽ��');
                echo json_encode($result);
                exit;
            }
            
            $pay_info = array();
            $pay_info['MID'] = $this->_post('MID');
            $pay_info['REAL_NAME'] = $this->_post('REAL_NAME');
            $pay_info['PAY_TYPE'] = $this->_post('PAY_TYPE');
            $pay_info['TRADE_MONEY'] = $this->_post('TRADE_MONEY');
            $pay_info['ORIGINAL_MONEY'] = $pay_info['TRADE_MONEY'];
            
            if($pay_info['PAY_TYPE'] == 1)
            {
                $pay_info['MERCHANT_NUMBER'] = trim($this->_post('MERCHANT_NUMBER'));
                $pay_info['RETRIEVAL'] = trim($this->_post('RETRIEVAL'));
                $pay_info['CVV2'] = trim($this->_post('CVV2'));
                
                if($pay_info['MERCHANT_NUMBER'] == '' || 
                    $pay_info['RETRIEVAL'] == '' || 
                    $pay_info['CVV2'] == '')
                {
                    $result['status'] = 0;
                    $result['msg'] = '���ʧ��,POS�����ʽ\'6λ������\',\'���ź���λ\',\'�̻����\'����';
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }
            }
            
            //������ϸ״̬
            $status_arr = $member_pay->get_conf_status();
            $pay_info['STATUS'] = $status_arr['wait_confirm'];
            $pay_info['TRADE_TIME'] = $this->_post('TRADE_TIME');
            $pay_info['ADD_UID'] = $uid;
            
            $insert_id = $member_pay->add_member_info($pay_info);
            
            if($insert_id > 0)
            {
            	//���»�Ա�ѽ��ɺ�δ���ɽ��
            	$paid_money = $member_pay->get_sum_pay($mid);
            	
            	//������
                $reduce_money = !empty($member_info['REDUCE_MONEY']) ? 
                                    floatval($member_info['REDUCE_MONEY']) : 0;
                //�����շѱ�׼
                $total_price = !empty($member_info['TOTAL_PRICE']) ? 
                    floatval($member_info['TOTAL_PRICE']) : 0;
                
            	//֧�����֧�����͸���Ϊ�ۺ�
            	$paid_money > $pay_info['TRADE_MONEY'] ? 
                    $update_arr['PAY_TYPE'] = 4 : $update_arr['PAY_TYPE'] = $pay_info['PAY_TYPE'];
            	$update_arr['PAID_MONEY'] = $paid_money;
                //FINANCIALCONFIRM����ȷ��״̬��1δȷ�ϡ�2����ȷ�ϡ�3��ȷ�ϣ�
                $confirm_status = $member_model->get_conf_confirm_status();
                $member_info['FINANCIALCONFIRM'] == $confirm_status['confirmed'] ? 
                        $update_arr['FINANCIALCONFIRM'] = $confirm_status['part_confirmed'] : '';
            	$update_arr['UNPAID_MONEY'] = $total_price > 0 ? 
                                              $total_price - $paid_money - $reduce_money : 0;
            	$member_model->update_info_by_id($mid, $update_arr);
                
                //���������Ϣ�������
                $income_info['CASE_ID'] = $member_info['CASE_ID'];
                $income_info['ENTITY_ID'] = $mid;
                $income_info['PAY_ID'] = $insert_id;
                $income_info['INCOME_FROM'] = $income_from;
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
            $pay_info = array();
            $pay_info['MID'] = intval($this->_post('MID'));
            $pay_info['REAL_NAME'] = addslashes(strip_tags($this->_post('REAL_NAME')));
            $pay_info['PAY_TYPE'] = intval($this->_post('PAY_TYPE'));
            $pay_info['TRADE_MONEY'] = floatval($this->_post('TRADE_MONEY'));
            $pay_info['ORIGINAL_MONEY'] = floatval($pay_info['TRADE_MONEY']);
            $pay_info['RETRIEVAL'] = addslashes(strip_tags($this->_post('RETRIEVAL')));
            $pay_info['CVV2'] = addslashes(strip_tags($this->_post('CVV2')));
            $pay_info['MERCHANT_NUMBER'] = addslashes(strip_tags($this->_post('MERCHANT_NUMBER')));
            $pay_info['TRADE_TIME'] = $this->_post('TRADE_TIME');
            
            //��ѯ��Ա��Ϣ
            $member_model = D('Member');
            $member_info = array();
            $search_field = array( 'CASE_ID', 'TOTAL_PRICE', 'PAID_MONEY',
                'REDUCE_MONEY', 'UNPAID_MONEY', 'FINANCIALCONFIRM');
            $member_info = $member_model->get_info_by_id($pay_info['MID'], $search_field);
            
            $paid_money = $member_pay->get_sum_pay($mid);
            
            //��������
            if($paid_money - floatval($this->_post('TRADE_MONEY_OLD')) + $pay_info['TRADE_MONEY'] > ($member_info['TOTAL_PRICE'] - $member_info['REDUCE_MONEY']) )
            {
                $result['status'] = 0;
                $result['msg'] =  g2u('�޸�ʧ�ܣ�֧��������Ӧ�����');
                echo json_encode($result);
                exit;
            }


            //�ж��Ƿ��Ǵ���(�̻����)
            if($pay_info['PAY_TYPE'] ==1)
            {
                if ($member_model->isLargeMerchant($pay_info['MERCHANT_NUMBER'], $this->city_id)) {
                    if (strlen($pay_info['CVV2']) < 10) {
                        $result['status'] = 0;
                        $result['msg'] = g2u("���ʽΪPOS���ĸ�����ϸ���̻����ѡ�����дȫ���ţ�");
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    if (strlen($pay_info['CVV2']) != 4) {
                        $result['status'] = 0;
                        $result['msg'] = g2u("���ʽΪPOS���ĸ�����ϸ�����ſ��ź���λ��");
                        echo json_encode($result);
                        exit;
                    }
                }
            }
            
            $member_pay = D('MemberPay');

            //�����Ѿ�ȷ�ϣ���������޸�
            $pay_old_info = $member_pay->get_payinfo_by_id($id,array("STATUS,TRADE_MONEY,PAY_TYPE,MERCHANT_NUMBER"));
            if($pay_old_info[0]['STATUS'] == 1){
                if($pay_info['TRADE_MONEY'] != $pay_old_info[0]['TRADE_MONEY']){
                    $result['status'] = 0;
                    $result['msg'] = g2u("�Բ��𣬸ñʽ������Ѿ�ȷ�ϣ������޸ġ����׽���");
                    die(json_encode($result));
                }

                if($pay_info['PAY_TYPE'] != $pay_old_info[0]['PAY_TYPE']){
                    $result['status'] = 0;
                    $result['msg'] = g2u("�Բ��𣬸ñʽ������Ѿ�ȷ�ϣ����ʽ�����޸ģ�");
                    die(json_encode($result));
                }

                if($pay_info['MERCHANT_NUMBER'] != $pay_old_info[0]['MERCHANT_NUMBER']){
                    $result['status'] = 0;
                    $result['msg'] = g2u("�Բ��𣬸ñʽ������Ѿ�ȷ�ϣ��̻���Ų����޸ģ�");
                    die(json_encode($result));
                }
            }

            $up_num = $member_pay->update_info_by_id($id, $pay_info);
            
            if($up_num > 0)
            {	
            	//���»�Ա�ѽ��ɺ�δ���ɽ��
            	$paid_money = $member_pay->get_sum_pay($mid);
            	 
            	//��ѯ��Ա��Ϣ
            	$member_model = D('Member');
            	$member_info = array();
            	$search_field = array('CASE_ID', 'TOTAL_PRICE', 'REDUCE_MONEY');
            	$member_info = $member_model->get_info_by_id($mid, $search_field);
            	 
            	//������
            	$reduce_money = !empty($member_info['REDUCE_MONEY']) ?
            			floatval($member_info['REDUCE_MONEY']) : 0;
            	
            	//�����շѱ�׼
            	$total_price = !empty($member_info['TOTAL_PRICE']) ? 
            				floatval($member_info['TOTAL_PRICE']) : 0;

            	//֧�����֧�����͸���Ϊ�ۺ�
            	$paid_money > $pay_info['TRADE_MONEY'] ? $update_arr['PAY_TYPE'] = 4 : ($update_arr['PAY_TYPE'] = $pay_info['PAY_TYPE']);
            	
            	$update_arr['PAID_MONEY'] = $paid_money;
            	$update_arr['UNPAID_MONEY'] = $total_price - $paid_money - $reduce_money;
            	$member_model->update_info_by_id($mid, $update_arr);

                /****�޸�������Ϣ****/
                $income_info['INCOME'] = $pay_info['TRADE_MONEY'];
                $income_info['INCOME_REMARK'] = '��Ա֧���޸�';
                
                $income_model = D('ProjectIncome');
                $income_model->update_income_info($income_info, $member_info['CASE_ID'], $mid, $id, $income_from);
            	
                $result['status'] = 1;
                $result['msg'] = '�޸ĳɹ���';
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
        else if($faction == 'delData')
        {
            $del_detail_result = FALSE;
            $del_id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
            $mid = isset($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;

            if($del_id > 0)
            {   
                $member_pay = D('MemberPay');
                $del_result = $member_pay->del_pay_detail_by_id($del_id);
                
                if($del_result > 0)
                {
                    //���»�Ա�ѽ��ɺ�δ���ɽ��
                    $paid_money = $member_pay->get_sum_pay($mid);
                    
                    //��ѯ��Ա��Ϣ
                    $member_model = D('Member');
                    $member_info = array();
                    $search_field = array('CASE_ID', 'TOTAL_PRICE', 'REDUCE_MONEY');
                    $member_info = $member_model->get_info_by_id($mid, $search_field);
                    
                    //������
                    $reduce_money = !empty($member_info['REDUCE_MONEY']) ?
                                        floatval($member_info['REDUCE_MONEY']) : 0;
                    
                    //�����շѱ�׼
                    $total_price = !empty($member_info['TOTAL_PRICE']) ? 
                                        floatval($member_info['TOTAL_PRICE']) : 0;
                    
                    $update_arr['PAID_MONEY'] = $paid_money;
                    $update_arr['UNPAID_MONEY'] = $total_price - $paid_money - $reduce_money;
                    if($paid_money == 0)
                    {
                        $update_arr['PAY_TYPE'] = 0;
                    }
                    $member_model->update_info_by_id($mid, $update_arr);
                    
                    //ɾ������
                    $income_model = D('ProjectIncome');   
                    $income_model->delete_income_info($member_info['CASE_ID'], $mid, $del_id, $income_from);
                }
            }
            
            if($del_result)
            {
                $info['status']  = 'success';
                $info['msg']  = g2u('ɾ���ɹ�');
            }
            else
            {
                $info['status']  = 'error';
                $info['msg']  = g2u('ɾ��ʧ��');
            }
            
            echo json_encode($info);
            exit;
        }
        else 
        {
            Vendor('Oms.Form');
            $form = new Form();
            $form = $form->initForminfo(121)->where("STATUS != 4");
            $member = D('Member');
            $m_id = intval($this->_get('parentchooseid'));
            
            $member_info = array();
            $member_info = $member->get_info_by_id($m_id, array('CITY_ID', 'REALNAME'));
            $member_name = !empty($member_info['REALNAME']) ? $member_info['REALNAME'] : '';
            $form = $form->setMyFieldVal('REAL_NAME', $member_name, 0);
            
            //���û�Ա����
            $form = $form->setMyField('MID', 'LISTSQL', "SELECT ID, REALNAME FROM ERP_CARDMEMBER", FALSE);
            
            //���ø��ʽ
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
            array_pop($pay_arr);//ȥ���ۺϸ���
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), FALSE);
            
            //���ø�����ϸ״̬
            $status_arr = $member_pay->get_conf_status_remark();
            $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr), FALSE);
            
            //���ø�����ϸ�˿�״̬
            $refund_status_arr = $member_pay->get_conf_refund_status_remark();
            $form = $form->setMyField('REFUND_STATUS', 'LISTCHAR', array2listchar($refund_status_arr), TRUE);
	        
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
            
            //����״̬����ɾ����ť�Ƿ���ʾ
            $form->DELCONDITION = '%STATUS% == 0';
	        $form = $form->setMyField('MERCHANT_NUMBER', 'LISTCHAR', array2listchar($merchant_arr), FALSE);
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->showPayListOptions);  // ��ťǰ��
            $formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
            $this->assign('form',$formHtml);
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
    	if($faction == 'saveFormData' && $id > 0)
    	{
    		$up_num = 0;
    		$refund_money = isset($_POST['REFUND_MONEY']) ? 
    							intval($_POST['REFUND_MONEY']) : 0;
            
            $trade_money = isset($_POST['TRADE_MONEY']) ? 
    							intval($_POST['TRADE_MONEY']) : 0;
            
            $refund_money_total = isset($_POST['REFUND_MONEY_TOTAL']) ? 
                    intval($_POST['REFUND_MONEY_TOTAL']) : 0;
            
            if($refund_money > ($trade_money - $refund_money_total))
            {
                $result['status'] =  0;
                $result['msg'] =  g2u('�޸�ʧ�ܣ��˿���ܳ������׽���ȥ�ۼ��˿��');
                
                echo json_encode($result);
                exit;
            }
            
    		if($refund_money > 0)
    		{
    			$update_arr['REFUND_MONEY'] = $refund_money;
    			$up_num = $member_refund->update_refund_detail_by_id($id, $update_arr);
    		}
    		
    	    if($up_num > 0)
            {   
                $result['status'] = 1;
                $result['msg'] =  g2u('�޸ĳɹ�');
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] =  g2u('�޸�ʧ��');
            }
            
            echo json_encode($result);
            exit;
    	}
        else if($faction == 'delData')
        {   
            $del_id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
            
            $refund_details = $member_refund->get_refund_detail_by_id($del_id, array('PAY_ID', 'REFUND_STATUS'));
            
            if(empty($refund_details))
            {
                $result['status'] =  'error';
                $result['msg'] =   g2u('ɾ��ʧ�ܣ������쳣');
                echo json_encode($result);
                exit;
            }
            
            $no_sub_status = intval($refund_status_arr['refund_no_sub']);
            if($no_sub_status != $refund_details['REFUND_STATUS'])
            {
                $result['status'] =  'error';
                $result['msg'] =  g2u('ɾ��ʧ�ܣ�δ�����˿���˵�֮ǰ���˿�����ſ���ɾ��');
                echo json_encode($result);
                exit;
            }
            
            //���¸�����Ϣ
            $member_pay = D('MemberPay');
            $conf_refund_status = $member_pay->get_conf_refund_status();
            $update_arr['REFUND_STATUS'] = $conf_refund_status['no_refund'];
            $update_pay = $member_pay->update_info_by_id($refund_details['PAY_ID'], $update_arr);
            
            //ɾ���˿���Ϣ������״̬��
            $up_num = $member_refund->del_refund_detail_by_id($del_id);
            
            if($up_num > 0)
            {
                $result['status'] =  'success';
                $result['msg'] =  g2u('ɾ���ɹ�');
            }
            else
            {
                $result['status'] =  'error';
                $result['msg'] =  g2u('ɾ��ʧ��');
            }
            
            echo json_encode($result);
            exit;
        }
    	else
    	{
	        Vendor('Oms.Form');
	        $form = new Form();
	        
            $refund_status_arr = $member_refund->get_conf_refund_status();
            $cond_where = " REFUND_STATUS != '".$refund_status_arr['refund_delete']."'";
            
	        $form = $form->initForminfo(122)->where($cond_where);
	        //���û�Ա���
	        $form = $form->setMyField('MID', 'LISTSQL','SELECT ID,REALNAME FROM ERP_CARDMEMBER', TRUE);
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
            
            //����״̬����ɾ����ť�Ƿ���ʾ()
            $no_sub_status = intval($refund_status_arr['refund_no_sub']);
            
            $form->DELCONDITION = '%REFUND_STATUS% == '.$no_sub_status;
            $form->EDITCONDITION = '%REFUND_STATUS% == '.$no_sub_status;

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->showRefundListOptions);  // ��ťǰ��
	        $formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
	        $this->assign('form',$formHtml);
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
        $form = $form->initForminfo(123)->where("INVOICE_TYPE = 2");
        //���û�Ա���
	    $form = $form->setMyField('CONTRACT_ID','LISTSQL','SELECT ID,REALNAME FROM ERP_CARDMEMBER', TRUE);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->showBillListOptions);  // ��ťǰ��
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
        $this->assign('form',$formHtml);
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
        $id_arr = $_POST['memberId'];
        $nj_no_pass_num = 0;
        $no_pass_num = 0;
        
        if(is_array($id_arr) && !empty($id_arr))
        {   
            $member_info = array();
            $search_field = array('CITY_ID','REALNAME','CARDSTATUS','INVOICE_STATUS','ROOMNO','UNPAID_MONEY');
            $member_model = D('Member');
            $invoice_status = $member_model->get_conf_invoice_status();
            $member_info = $member_model->get_info_by_ids($id_arr, $search_field);
            
            if(is_array($member_info) && !empty($member_info))
            {  
                foreach ($member_info as $key => $value)
                {   
                    #�Ͼ����������
                    if($value['CITY_ID'] == 1)
                    {
                        if($value['CARDSTATUS'] != 3 || 
                            $value['INVOICE_STATUS'] != $invoice_status['no_invoice'] || 
                            trim($value['ROOMNO']) == '' || 
                               $value['UNPAID_MONEY'] != 0)
                        {   
                            $nj_no_pass_num ++;
                        }
                    }
                    else
                    {
                        if($value['CARDSTATUS'] != 3 || 
                            $value['INVOICE_STATUS'] != $invoice_status['no_invoice'] || 
                            $value['UNPAID_MONEY'] != 0)
                        {   
                            $no_pass_num ++;
                        }
                    }
                }
            }
            else 
            {
                $info['state']  = 0;
                $info['msg'] = g2u('����ʧ�ܣ���Ա��Ϣ�쳣');
                echo json_encode($info);
                exit;
            }
            
            if($nj_no_pass_num > 0)
            {   
                $info['state']  = 0;
                $msg = '���뿪Ʊʧ��,['.$nj_no_pass_num.']�����ݲ����Ͽ�Ʊ������'
                        . '<br>����쿨��Ա��Ϣ�Ƿ��������������'
                        . '<br>1���쿨״̬Ϊ�Ѱ���ǩԼ��'
                        . '<br>2����Ʊ״̬Ϊδ����'
                        . '<br>3��¥����������д��'
                        . '<br>4��������δ���ɽ�'
                        . '<br>5��������ϸ�������ȷ�ϣ�'
                        . '<br>6��������ϸ���������˿';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            if($no_pass_num > 0)
            {   
                $info['state']  = 0;
                $msg = '���뿪Ʊʧ��,['.$no_pass_num.']�����ݲ����Ͽ�Ʊ������'
                        . '<br>����쿨��Ա��Ϣ�Ƿ��������������'
                        . '<br>1���쿨״̬Ϊ�Ѱ���ǩԼ��'
                        . '<br>2����Ʊ״̬Ϊδ����'
                        . '<br>3��������δ���ɽ��'
                        . '<br>4��������ϸ�������ȷ�ϣ�'
                        . '<br>5��������ϸ���������˿';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            //��ѯ��Ա��Ϣ������ϸ�Ƿ��Ѿ�������ȷ��
            $pay_list_no_confrim_num = 0;
            $member_pay_model = D('MemberPay');
            //֧����ϸȷ��״̬
            $conf_pay_status = $member_pay_model->get_conf_status();
            //֧����ϸ�˿�״̬
            $conf_pay_refund_status = $member_pay_model->get_conf_refund_status();
            
            //֧����ϸ��Ϣ
            $pay_list_info = $member_pay_model->get_payinfo_by_mid($id_arr, array('STATUS', 'REFUND_STATUS'));
            //δȷ����ϸ����
            $pay_list_no_confrim_num = 0;
            //�˿���ϸ����
            $pay_list_refund_num = 0;
            if(is_array($pay_list_info) && !empty($pay_list_info))
            {
                foreach($pay_list_info as $key => $value)
                {   
                    //δȷ��
                    if($value['STATUS'] == $conf_pay_status['wait_confirm'])
                    {
                        $pay_list_no_confrim_num ++;
                    }
                    
                    //�˿�
                    if($value['REFUND_STATUS'] != $conf_pay_refund_status['no_refund'])
                    {
                        $pay_list_refund_num ++;
                    }
                }
                
                if($pay_list_no_confrim_num > 0)
                {
                    $info['state']  = 0;
                    $info['msg'] = g2u('���뿪Ʊʧ�ܣ�����δȷ�ϵĸ�����ϸ��Ϣ');
                    echo json_encode($info);
                    exit;
                }
                
                if($pay_list_refund_num > 0)
                {
                    $info['state']  = 0;
                    $info['msg'] = g2u('���뿪Ʊʧ�ܣ����������˿�ĸ�����ϸ');
                    echo json_encode($info);
                    exit;
                }
            }
            else
            {
                $info['state']  = 0;
                $info['msg'] = g2u('��֧����ϸ��Ϣ�Ļ�Ա�޷����뿪Ʊ');
                echo json_encode($info);
                exit;
            }
            
            $result = FALSE;
            $result = $member_model->update_info_by_id($id_arr, array('INVOICE_STATUS' => $invoice_status['apply_invoice']));
            
            if( $result > 0)
            {
                $info['state']  = 1;
                $info['msg']  = '��Ʊ����ɹ�';
            }
            else
            {   
                $info['state']  = 0;
                $info['msg']  = '��Ʊʧ��!';
            }
        }
        else
        {   
            $info['state']  = 0;
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
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = strip_tags($_SESSION['uinfo']['tname']);
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	$showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        
        Vendor('Oms.Form');
        $form = new Form();
        
        $form = $form->initForminfo(169);
        $form->SQLTEXT = "(SELECT * FROM ERP_LOCALE_GRANTED WHERE CITY_ID = '".$this->channelid."' AND PRJ_ID IN "
            . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 1) )";
        
        $grant_model = D('LocaleGranted');
    	if($faction == 'saveFormData' && $id > 0)
    	{	
            $grant_info = array();
            $grant_info['MONEY'] = floatval($_POST['MONEY']);
            $grant_info['NUM'] = floatval($_POST['NUM']);
            $grant_info['ISFUNDPOOL'] = intval($_POST['ISFUNDPOOL']);
            $grant_info['ISKF'] = intval($_POST['ISKF']);
            $grant_info['ATTACHMENTS'] = u2g($_POST['ATTACHMENTS']);
            $grant_info['UPDATETIME'] = date('Y-m-d H:i:s');
            
            if($grant_info['ATTACHMENTS'] == '')
            {
                $result['status'] = 0;
    		$result['msg'] = g2u('�޸�ʧ�ܣ�����������ӣ�');
                echo json_encode($result);
                exit;
            }
            
            $update_num = $grant_model->update_info_by_id($id, $grant_info);
            
            if($update_num > 0)
            {
                $result['status'] = 1;
                $result['msg'] = g2u('�޸ĳɹ�');
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] = g2u('�޸�ʧ��');
            }

            echo json_encode($result);
            exit;
        }
        else if($faction == 'saveFormData' && $id == 0)
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
            
            //��ĿMODEL
            $project_model = D('Project');
            $project_info = $project_model->get_info_by_id($grant_info['PRJ_ID'], array('BSTATUS'));
            $ds_status = !empty($project_info[0]['BSTATUS']) ? intval($project_info[0]['BSTATUS']) : 0;
            
            if($ds_status != 2)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('��Ŀ����ҵ����ִ���У��޷�����ֳ����ż�¼');
                echo json_encode($result);
                exit;
            }
            
            $is_overtop = is_overtop_payout_limit($case_id);
            if($is_overtop)
            {
                $result['status'] = 0;
    		$result['msg'] = g2u('�ɱ��Ѿ��������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ����*���ֳɱ��ʣ����޷�����ֳ����ż�¼');
                echo json_encode($result);
                exit;
            }
            
            $grant_info['CASE_ID'] = $case_id;
            $grant_info['CITY_ID'] = intval($_POST['CITY_ID']);
            $grant_info['MONEY'] = floatval($_POST['MONEY']);
            $grant_info['NUM'] = floatval($_POST['NUM']);
            $grant_info['ADD_UID'] = $uid;
            $grant_info['ISFUNDPOOL'] = intval($_POST['ISFUNDPOOL']);
            $grant_info['ISKF'] = intval($_POST['ISKF']);
            $grant_info['OCCUR_TIME'] = $_POST['OCCUR_TIME'];
            $grant_info['ATTACHMENTS'] = u2g($_POST['ATTACHMENTS']);
            $grant_info['CREATTIME'] = date('Y-m-d H:i:s');
            
            if($grant_info['ATTACHMENTS'] == '')
            {
                $result['status'] = 0;
    		    $result['msg'] = g2u('���ʧ�ܣ�����������ӣ�');
                echo json_encode($result);
                exit;
            }
            
            $insert_id = $grant_model->add_grant_info($grant_info);
            
            if($insert_id > 0)
            {
                $result['status'] = 1;
                $result['msg'] = g2u('��ӳɹ�');
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] = g2u('���ʧ��');
            }

            echo json_encode($result);
            exit;
        }
        else 
        { 
            //����ҳ
            if( $showForm > 0 )
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
            }
            else
            {
                //��Ŀ����
                $form = $form->setMyField('PRJ_ID', 'LISTSQL', "SELECT ID, PROJECTNAME FROM ERP_PROJECT", TRUE);
                
                //����״̬����ɾ����ť�Ƿ���ʾ
                $form->DELCONDITION = '%REIM_STATUS% == 0';
                $form->EDITCONDITION = '%REIM_STATUS% == 0';
            }
            
            //������
            if($showForm == 3 )
            {
                $form->setMyField('ADD_UID', 'EDITTYPE', 22);
                $form->setMyField('ADD_UID', 'LISTCHAR', array2listchar(array($uid => $username)), TRUE);
                $form->setMyFieldVal('ADD_UID', $uid);
                //$form->setMyField('OCCUR_TIME', 'READONLY', '-1');
                $form->setMyFieldVal('OCCUR_TIME', date('Y-m-d', time()));
            }
            else
            {
                $form = $form->setMyField('ADD_UID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
            }
            
            //����״̬
            $conf_reim_status = $grant_model->get_conf_reim_status_remark();
            $form->setMyField( 'REIM_STATUS', 'LISTCHAR' , array2listchar($conf_reim_status), TRUE);
        }

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->localeGrantedOptions);  // ��ťǰ��
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('form', $formHtml);
        $this->assign('showForm', $showForm);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
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
    	$formHtml = $form->getResult();
    	$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
    	$this->display('merchant_manage');
    }
    
    
    /**
     +----------------------------------------------------------
     * ��Ա���ñ�������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function reim_manage()
    {	
        //����MODEL
        $reim_type_model = D('ReimbursementType');
        $reim_list_model = D('ReimbursementList');
        $reim_detail_model = D('ReimbursementDetail');
        
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$city = $this->channelid;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
        
        if($faction == 'delData')
        {   
            $list_id = intval($_GET['ID']);

            $del_list_result = FALSE;
            $del_detail_result = FALSE;

            $memberInfo = $reim_detail_model->get_detail_info_by_listid($list_id,array("BUSINESS_ID","CASE_ID","TYPE","STATUS"));

            if($memberInfo){
                foreach($memberInfo as $k1=>$v1){
                    if($v1['STATUS']==0) {
                        $reim_type = $v1['TYPE'];
                        $memberIds = $v1['BUSINESS_ID'] . ",";
                    }
                }
                $memberIds = trim($memberIds,",");
            }

            if(in_array($reim_type,array(3,4,6,9,10,11,12,21,25))) {
                //����״̬
                $status_up_array = array();
                switch (intval($reim_type)) {
                    case 3:
                        $status_up_array['AGENCY_REWARD_STATUS'] = 1;
                        break;
                    case 4:
                        $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 1;
                        break;
                    case 6:
                        $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                        break;
					case 9:
						$status_up_array['AGENCY_REWARD_STATUS'] = 1;
						break;
					case 10:
						$status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 1;
						break;
					case 12:
						$status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 1;
						break;
					case 21:
						$status_up_array['OUT_REWARD_STATUS'] = 1;
						break;
					case 25:
						$status_up_array['OUT_REWARD_STATUS'] = 1;
						break;
                }

                $reim_status_up = M("Erp_cardmember")->where("ID IN ({$memberIds})")->save($status_up_array);
            }

            //�������ID > 0 && $reim_status_upִ�н����Ϊfalse
            if($list_id > 0 && ($reim_status_up!==false))
            {   
                $del_list_result = $reim_list_model->del_reim_list_by_ids($list_id);
                
                if($del_list_result)
                {
                    $del_detail_result = $reim_detail_model->del_reim_detail_by_listid($list_id);
                }
                //var_dump($del_detail_result);
                //���·��ż�¼����״̬Ϊδ����
                $locale_granted_model = D('LocaleGranted');
                $up_num_granted = $locale_granted_model->sub_granted_to_reim_not_apply_by_reim_listid($list_id);

                //���¹������
                $loan_model = D('Loan');
                $up_num_loan = $loan_model->cancleRelatedLoan($list_id);
            }
            
            if($del_list_result > 0 && $del_detail_result > 0)
            {
                $info['status']  = 'success';
                $info['msg']  = g2u('ɾ���ɹ�');
            }
            else if(!$del_detail_result)
            {   
                $info['status']  = 'error';
                $info['msg']  = g2u('������ϸɾ��ʧ��');
            }
            else 
            {   
                $info['status']  = 'error';
                $info['msg']  = g2u('ɾ��ʧ��');
            }
            
            echo json_encode($info);
    		exit;
        }
        
        Vendor('Oms.Form');
    	$form = new Form();
        
        //�������뵥״̬
        $list_status = $reim_list_model->get_conf_reim_list_status();
    	$cond_where = "APPLY_UID = '".$uid."' AND CITY_ID = '".$city."' "
                . "AND TYPE IN (3,4,5,6,7,9,10,11,12,21,25) AND STATUS != '".$list_status['reim_deleted']."' ";
    	$form = $form->initForminfo(176)->where($cond_where);

        $form->SQLTEXT = '(select a.*,b.LOANMONEY  from ERP_REIMBURSEMENT_LIST a
        left join (select  APPLICANT,sum(UNREPAYMENT) as LOANMONEY from ERP_LOANAPPLICATION where STATUS IN(2,6) AND CITY_ID = '. $city . ' group by APPLICANT) b
        on a.APPLY_UID = b.APPLICANT )';
        
        if($this->_merge_url_param['flowId'] > 0)
        {
            //��������ڱ༭Ȩ��
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

            if($flow_edit_auth)
            {
                //����༭
                //$form->EDITABLE = -1;
                $form->GABTN = "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>�������</a>";
                $form->ADDABLE = '0';
            }
            else
            {
                $form->DELCONDITION = '1==0';
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = '0'; 
                $form->GABTN = '';
            }
        }
        else
        {
            //����״̬����ɾ����ť�Ƿ���ʾ
            $form->DELABLE = -1;
            $form->DELCONDITION = '%STATUS% == 0';
            //$form->EDITABLE = -1;
            $form->EDITCONDITION = '%STATUS% == 0';
            $form->GABTN = "<a id='sub_reim_apply' href='javascript:;' class='btn btn-info btn-sm'>�ύ��������</a>  "
                    . "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>�������</a>"
                    ."<a id = 'show_flow_step'  href='javascript:;' class='btn btn-info btn-sm'>���������ͼ</a>";

        }
        $form->EDITABLE = 0;
    	//���ñ���������
    	$type_arr = $reim_type_model->get_reim_type();
    	$form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
    	
    	//���ñ�����״̬
    	$status_arr = $reim_list_model->get_conf_reim_list_status_remark();
    	$form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr), FALSE);
    	
    	//����ҳ
    	if($showForm > 0)
    	{
    		//�����
    		$form = $form->setMyField('REIM_UID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
    	}
		
    	$children_data = array(
    			array('������ϸ', U('Member/reim_detail_manage', 'fromTab=22')),
    			array('�������', U('Loan/related_loan'))
    	);
        
        
    	$form =  $form->setChildren($children_data);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->localeGrantedOptions);  // ��ťǰ��
    	$formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
    	$this->assign('form', $formHtml);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
    	$this->display('reim_manage');
    }
    
    
    /**
     +----------------------------------------------------------
     * ������ϸ
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function reim_detail_manage()
    {	
    	$list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$city = $this->channelid;
    	$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	$showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
    	$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        //������Ϊ
        $act = isset($_REQUEST['act'])?trim($_REQUEST['act']):'';
        
    	//�������뵥MODEL
    	$reim_list_model = D('ReimbursementList');
        //����MODEL
        $reim_detail_model = D('ReimbursementDetail');
        //��������
        $reim_type_model = D('ReimbursementType');

        if($act=='changeStatus'){
            $return = array(
                'msg'=>'����ʧ��',
                'data'=>null,
                'state'=>0,
            );

            $idArr = $_REQUEST['idArr'];
            $isFundPool = isset($_REQUEST['isfundpool'])?intval($_REQUEST['isfundpool']):0;

            $ids = implode(",",$idArr);
            $sql = "UPDATE ERP_REIMBURSEMENT_DETAIL SET ISFUNDPOOL = $isFundPool WHERE ID IN($ids)";
            $updateRet = $reim_detail_model->query($sql);

            if($updateRet!==false){
                $return['msg'] = '�����ɹ�';
                $return['state'] = 1;
            }

            die(json_encode(g2u($return)));
        }
        
    	Vendor('Oms.Form');
    	$form = new Form();
        
        if($faction == 'saveFormData' && $id > 0)
    	{	
            $update_arr = array();
            $update_arr['ISFUNDPOOL'] = intval($_POST['ISFUNDPOOL']);
            $update_arr['ISKF'] = intval($_POST['ISKF']);
            $update_arr['MONEY'] = floatval($_POST['MONEY']);
            $update_num = $reim_detail_model->update_reim_detail_by_id($id, $update_arr);
            
            if($update_num > 0)
    		{
                $aReimDetail = $reim_detail_model->where('ID = ' . $id)->find();;
                $this->syncAssocTable($aReimDetail, $update_arr);  // ͬ�������ż�¼��

                $reim_list_info = $reim_detail_model->get_detail_info_by_id($id, array('LIST_ID'));
                $reim_list_id = !empty($reim_list_info) ? $reim_list_info[0]['LIST_ID'] : 0;

                $total_amount = $reim_detail_model->get_sum_total_money_by_listid($reim_list_id);
                $up_list_result = $reim_list_model->update_reim_list_amount($reim_list_id, $total_amount, 'cover');
                        
    			$result['status'] = 1;
    			$result['msg'] = g2u('�޸ĳɹ�');
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��');
    		}
            
    		echo json_encode($result);
    		exit;
        }
        else
        {
            //����LIST��ѯ����������
            $list_info = $reim_list_model->get_info_by_id($list_id, array('TYPE', 'STATUS'));
            $reim_type = !empty($list_info[0]['TYPE']) ? intval($list_info[0]['TYPE']) : 0;
            $reim_list_status = !empty($list_info[0]['STATUS']) ? intval($list_info[0]['STATUS']) : 0;
            if(in_array($reim_type, array(3,4,5,6,9,10,11,12,21,25)) )
            {
                $form_id = 177;
            }
            else if( $reim_type == 7)
            {
                $form_id = 178;
            }

            $detail_status = $reim_detail_model->get_conf_reim_detail_status();
            $cond_where = "LIST_ID = '".$list_id."' AND STATUS != '".$detail_status['reim_detail_deleted']."'";
            $form = $form->initForminfo($form_id)->where($cond_where);
            
            //֤������
            $form->setMyField('CERTIFICATE_NO', 'ENCRY', '4,13', FALSE);
            
            //����״̬���Ʊ༭ɾ����ť�Ƿ���ʾ
            $conf_reim_list_status = $reim_list_model->get_conf_reim_list_status();
            if($conf_reim_list_status['reim_list_no_sub'] == $reim_list_status)
            {   
                $form->EDITABLE = '-1';
                $form->DELABLE = '-1';
            }

            if($faction == 'delData')
            {
                $id = intval($_GET['ID']);

                //������֤
                //ɾ����ϸʣ�����С�ڽ����
                if(D("Loan")->checkDelReim($list_id,$id)){
                    $info['status']  = 'error';
                    $info['msg']  = g2u('�Բ������˱����������Ľ�����Ѵ��ڱ�����ɾ��ʧ��!');
                    die(json_encode($info));
                }

                $del_detail_result = FALSE;
                $up_list_result = FALSE;

                $memberInfo = $reim_detail_model->get_detail_info_by_cond(" ID=$id AND STATUS=0 ",array("BUSINESS_ID","CASE_ID","TYPE"));
                $memberId = $memberInfo[0]['BUSINESS_ID'];
                $reim_type = $memberInfo[0]['TYPE'];

                if(in_array($reim_type,array(3,4,6,9,10,11,12,21,25))) {
                    //����״̬
                    $status_up_array = array();
                    switch (intval($reim_type)) {
                        case 3:
                            $status_up_array['AGENCY_REWARD_STATUS'] = 1;
                            break;
                        case 4:
                            $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 1;
                            break;
                        case 6:
                            $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                            break;
						case 9:
                            $status_up_array['AGENCY_REWARD_STATUS'] = 1;
                            break;
						case 10:
                            $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 1;
                            break;
						case 12:
                            $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                            break;
						case 21:
                            $status_up_array['OUT_REWARD_STATUS'] = 1;
                            break;
						case 25:
                            $status_up_array['OUT_REWARD_STATUS'] = 1;
                            break;
                    }
                    $reim_status_up = M("Erp_cardmember")->where("ID = {$memberId}")->save($status_up_array);
                }
 
                if($id > 0 && $reim_status_up !== false)
                {   
                    $del_detail_result = $reim_detail_model->del_reim_detail_by_id($id);

                    if($del_detail_result)
                    {	
                        $reim_list_info= $reim_detail_model->get_detail_info_by_id($id, array('LIST_ID','BUSINESS_ID'));
                        $reim_list_id = !empty($reim_list_info) ? intval($reim_list_info[0]['LIST_ID']) : 0;
                    
                        $total_amount = $reim_detail_model->get_sum_total_money_by_listid($reim_list_id);
                        $up_list_result = $reim_list_model->update_reim_list_amount($reim_list_id, $total_amount, 'cover');
                        
                        $grant_id = !empty($reim_list_info) ? intval($reim_list_info[0]['BUSINESS_ID']) : 0;
                        
                        //���·��ż�¼����״̬Ϊδ����
                        $locale_granted_model = D('LocaleGranted');
                        $up_num_granted = $locale_granted_model->sub_granted_to_reim_not_apply_by_id($grant_id);
                    }
                }

                if($del_detail_result > 0 && $up_list_result > 0)
                {
                    $info['status']  = 'success';
                    $info['msg']  = g2u('ɾ���ɹ�');
                }
                else if(!$up_list_result)
                {
                    $info['status']  = 'error';
                    $info['msg']  = g2u('�������뵥������ʧ��');
                }
                else
                {
                    $info['status']  = 'error';
                    $info['msg']  = g2u('ɾ��ʧ��');
                }

                echo json_encode($info);
                exit;
            }

            if(in_array($reim_type, array(3,4,5,6,9,10,11,12,21,25)) )
            {
                $member_model = D('Member');

                //���û�Ա��Դ
                $source_arr = $member_model->get_conf_member_source_remark();
                $form = $form->setMyField('SOURCE', 'LISTCHAR',
                        array2listchar($source_arr), FALSE);

                //������
                $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);

                //�Ƿ��ʽ��
                //$form->setMyFieldVal('ISFUNDPOOL',0,FALSE);

                //����֤������
                $certificate_type_arr = $member_model->get_conf_certificate_type();
                $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',
                        array2listchar($certificate_type_arr), FALSE);

                //���ø��ʽ
                $member_pay = D('MemberPay');
                $pay_arr = $member_pay->get_conf_pay_type();
                $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);

                //���ñ���������
                $type_arr = $reim_type_model->get_reim_type();
                $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                //������ϸ״̬����
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
            }
            else if( $reim_type == 7)
            {   
                //��Ŀ����
                $form = $form->setMyField('PRJ_ID', 'LISTSQL', 'SELECT ID, PROJECTNAME FROM ERP_PROJECT', TRUE);

                //���ñ���������
                $type_arr = $reim_type_model->get_reim_type();
                $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                //������ϸ״̬����
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
            }
        }
		 $form->setMyField('AGENCY_NAME','READONLY',-1,false);
        $form->setMyField('INPUT_TAX','GRIDVISIBLE',0,false);
        $form->setMyField('INPUT_TAX','FORMVISIBLE',0,false);

        if($reim_type==3 || $reim_type==4){
            $form->GABTN='<a href = "javascript:;" id= "changeStatus" class="btn btn-info btn-sm">����ʽ��״̬</a>';
        }

        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
        $this->display('reim_detail_manage');
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
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
        Vendor('phpExcel.PHPExcel');
        if($_REQUEST['case_type']=='fx'){
			$Exceltitle = '�����ͻ�����';
		}else{
			$Exceltitle = '���̻�Ա����';
		}
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(g2u($Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//Ĭ���п�
        $objActSheet->getDefaultColumnDimension()->setWidth(16);//Ĭ���п�
        $objActSheet->getDefaultStyle()->getFont()->setName(g2u('����'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);
        
        $i = 1;
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setName('Candara' );
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setSize(12);
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setBold(true);
        
        //�ϲ���Ԫ��
        $objActSheet->setCellValue('A'.$i, g2u('��Ա��Ϣ'));
        $objActSheet->mergeCells( 'A1:Z1');
        $objActSheet->setCellValue('AA'.$i, g2u('֧����Ϣ'));
        $objActSheet->mergeCells( 'AA1:AI1');
        
        $i = $i +1;
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setName('Candara' );
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setSize(10);
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setBold(true);
        
        $objActSheet->setCellValue('A'.$i, g2u('���'));
        $objActSheet->setCellValue('B'.$i, g2u('��Ա����'));
        $objActSheet->setCellValue('C'.$i, g2u('�������ֻ���'));
        $objActSheet->setCellValue('D'.$i, g2u('֤������'));
        $objActSheet->setCellValue('E'.$i, g2u('֤������'));
        $objActSheet->setCellValue('F'.$i, g2u('��Ŀ����'));
        $objActSheet->setCellValue('G'.$i, g2u('¥������'));
        $objActSheet->setCellValue('H'.$i, g2u('��Ա��Դ'));
        $objActSheet->setCellValue('I'.$i, g2u('�쿨����'));
        $objActSheet->setCellValue('J'.$i, g2u('�쿨״̬'));
        $objActSheet->setCellValue('K'.$i, g2u('�վ�״̬'));
        $objActSheet->setCellValue('L'.$i, g2u('�վݱ��'));
        $objActSheet->setCellValue('M'.$i, g2u('��Ʊ״̬'));
        $objActSheet->setCellValue('N'.$i, g2u('��Ʊ���'));
        $objActSheet->setCellValue('O'.$i, g2u('��Ʊʱ��'));
        $objActSheet->setCellValue('P'.$i, g2u('����ȷ��״̬'));
        $objActSheet->setCellValue('Q'.$i, g2u('���ʽ'));
        $objActSheet->setCellValue('R'.$i, g2u('�ѽɽ��'));
        $objActSheet->setCellValue('S'.$i, g2u('δ���ɽ��'));
        $objActSheet->setCellValue('T'.$i, g2u('ֱ����Ա'));
        if($_REQUEST['case_type']=='fx'){
			$objActSheet->setCellValue('U'.$i, g2u('ǰӶ�շѱ�׼'));
			$objActSheet->setCellValue('V'.$i, g2u('��Ӷ�շѱ�׼'));
			$objActSheet->setCellValue('W'.$i, g2u('�Ƿ����'));
			$objActSheet->setCellValue('X'.$i, g2u('�����ܼ�'));
			$objActSheet->setCellValue('Y'.$i, g2u('���͹�˾'));
			$objActSheet->setCellValue('Z'.$i, g2u('����ʱ��'));
			$objActSheet->setCellValue('AA'.$i, g2u('װ�ޱ�׼'));
			$objActSheet->setCellValue('AB'.$i, g2u('������'));
			$objActSheet->setCellValue('AC'.$i, g2u('��ע'));
			$objActSheet->setCellValue('AD'.$i, g2u('֧����ʽ'));
			$objActSheet->setCellValue('AE'.$i, g2u('֧�����'));
			$objActSheet->setCellValue('AF'.$i, g2u('ˢ����'));
			$objActSheet->setCellValue('AG'.$i, g2u('ˢ����'));
			$objActSheet->setCellValue('AH'.$i, g2u('ˢ����'));
			$objActSheet->setCellValue('AI'.$i, g2u('POS����'));
			$objActSheet->setCellValue('AJ'.$i, g2u('���ź���λ'));
			$objActSheet->setCellValue('AK'.$i, g2u('�˿���'));
			$objActSheet->setCellValue('AL'.$i, g2u('�˿�ʱ��'));
            $objActSheet->setCellValue('AM'.$i, g2u('�Ϲ�ʱ��'));
            $objActSheet->setCellValue('AN'.$i, g2u('ǩԼʱ��'));

		}else{
			$objActSheet->setCellValue('U'.$i, g2u('�����շѱ�׼'));
		
			$objActSheet->setCellValue('V'.$i, g2u('�Ƿ����'));
			$objActSheet->setCellValue('W'.$i, g2u('�����ܼ�'));
			$objActSheet->setCellValue('X'.$i, g2u('���͹�˾'));
			$objActSheet->setCellValue('Y'.$i, g2u('����ʱ��'));
			$objActSheet->setCellValue('Z'.$i, g2u('װ�ޱ�׼'));
			$objActSheet->setCellValue('AA'.$i, g2u('������'));
			$objActSheet->setCellValue('AB'.$i, g2u('��ע'));
			$objActSheet->setCellValue('AC'.$i, g2u('֧����ʽ'));
			$objActSheet->setCellValue('AD'.$i, g2u('֧�����'));
			$objActSheet->setCellValue('AE'.$i, g2u('ˢ����'));
			$objActSheet->setCellValue('AF'.$i, g2u('ˢ����'));
			$objActSheet->setCellValue('AG'.$i, g2u('ˢ����'));
			$objActSheet->setCellValue('AH'.$i, g2u('POS����'));
			$objActSheet->setCellValue('AI'.$i, g2u('���ź���λ'));
			$objActSheet->setCellValue('AJ'.$i, g2u('�˿���'));
			$objActSheet->setCellValue('AK'.$i, g2u('�˿�ʱ��'));
		}
		//if($_REQUEST['case_type']=='fx') $objActSheet->setCellValue('AK'.$i, g2u('��Ӷ�շѱ�׼'));


        //��ȡ��������
        $filter_sql = isset($_GET['Filter_Sql'])?trim($_GET['Filter_Sql']):'';
        //��ȡ��������
        $sort_sql = isset($_GET['Sort_Sql'])?trim($_GET['Sort_Sql']):'';


        /***��ѯ��Ҫ�����Ļ�Ա��Ϣ***/
        $search_field = array('ID', 'PRJ_NAME', 'REALNAME', 'MOBILENO', 'SOURCE','ROOMNO', 
        					'CARDSTATUS', 'CARDTIME', 'INVOICE_STATUS', 'INVOICE_NO',
                            'CONFIRMTIME', 'RECEIPTSTATUS','RECEIPTNO' , 'DIRECTSALLER',
        					'FINANCIALCONFIRM', 'ADD_USERNAME', 'CERTIFICATE_TYPE', 
        					'CERTIFICATE_NO', 'PAY_TYPE', 'PAID_MONEY', 'UNPAID_MONEY',
        					'TOTAL_PRICE', 'TOTAL_PRICE', 'LEAD_TIME', 'IS_TAKE','SUBSCRIBETIME','SIGNTIME',
                            'DECORATION_STANDARD', 'NOTE', 'HOUSETOTAL','AGENCY_NAME','TOTAL_PRICE_AFTER,CASE_ID');
        
        $search_str = implode(',', $search_field);
        $result = array();
		$query_sql = "SELECT $search_str FROM ERP_CARDMEMBER WHERE "
                . "CITY_ID = '".$this->channelid."' AND STATUS = 1 AND PRJ_ID IN ".
				"(SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' "
                . "AND ISVALID = '-1' AND ERP_ID = 1) ";


        $query_sql = "SELECT $search_str FROM ERP_CARDMEMBER WHERE "
            . "CITY_ID = '".$this->channelid."' AND STATUS = 1 ";


        //�Ƿ��в鿴ȫ����Ȩ��
        if(!$this->p_auth_all)
        {
            $query_sql .= " AND PRJ_ID IN ".
                "(SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' "
                . "AND ISVALID = '-1' AND ERP_ID = 1) ";
        }

        //�Ƿ��Լ�����
        if(!$this->p_vmem_all)
        {
            $query_sql  .= " AND ADD_UID = $uid ";
        }
		if($_REQUEST['case_type']=='fx'){
			$query_sql  .= " AND IS_DIS=2 ";

		}else {
			$query_sql  .= " AND IS_DIS=1 ";
		}

        if($filter_sql)
            $query_sql .= $filter_sql;

        if($sort_sql)
            $query_sql .= ' ' . $sort_sql;

        //��ȡ����
        $result = M('')->query($query_sql);
        
        if(is_array($result))
        {	
            if(count($result) > 1000)
            {
                $this->error('�������1000�����ݣ���ѯ�����ݳ���1000��');
            }
            
            $member_model = D('Member');
            //��ȡ��Ա�쿨����Ʊ����Ʊ״̬
            $status_arr = $member_model->get_conf_all_status_remark();

            //���ø��ʽ
            $member_pay_model = D('MemberPay');
            $pay_arr = $member_pay_model->get_conf_pay_type();

            //����֤������
            $certificate_type_arr = $member_model->get_conf_certificate_type();

            //������׼
            $conf_zx_standard_arr = $member_model->get_conf_zx_standard();
            
            //��Ա��Դ
            $conf_member_source = $member_model->get_conf_member_source_remark();

            $member_ids = array();
            foreach($result as $key => $value)
            {
               $member_ids[] =  $value['ID'];
            }
            
            //��ѯ���л�Ա������Ϣ
        	$member_pay_info = $member_pay_model->get_payinfo_by_mid($member_ids);
            $pay_num = count($member_pay_info);
            
            $member_pay_arr = array();
            for($j = 0 ; $j < $pay_num; $j ++)
            {
                $member_pay_arr[$member_pay_info[$j]['MID']][] = $member_pay_info[$j];
            }
            
            $i = $i +1;
            foreach($result as $k => $r)
            {
                $objActSheet->setCellValue('A'.$i, $r['ID']);
                $objActSheet->setCellValue('B'.$i, g2u($r['REALNAME']) );
                
                $objActSheet->setCellValue('D'.$i, g2u($certificate_type_arr[$r['CERTIFICATE_TYPE']]));
//                if($this->p_auth_all)
//                {
//                    $objActSheet->setCellValue('C'.$i, " ".substr_replace($r['MOBILENO'], '****', 5, 4));
//                    $objActSheet->setCellValue('E'.$i, " ".substr_replace($r['CERTIFICATE_NO'], '**********', 5, 9));
//                }
//                else
//                {
//                    $objActSheet->setCellValue('C'.$i, " ".substr_replace($r['MOBILENO'], '****', 5, 4));
//                    $objActSheet->setCellValue('E'.$i, " ".substr_replace($r['CERTIFICATE_NO'], '**********', 5, 9));
//                }
                $objActSheet->setCellValue('C'.$i, g2u($r['MOBILENO']));
                $objActSheet->setCellValue('E'.$i, g2u("=\"$r[CERTIFICATE_NO]\""));
                $objActSheet->setCellValue('F'.$i, g2u($r['PRJ_NAME']) );
                $objActSheet->setCellValue('G'.$i, g2u($r['ROOMNO']));
                $objActSheet->setCellValue('H'.$i, g2u($conf_member_source[$r['SOURCE']]));//��Ա��Դ
                $objActSheet->setCellValue('I'.$i, oracle_date_format($r['CARDTIME']));
                $objActSheet->setCellValue('J'.$i, g2u($status_arr['CARDSTATUS'][$r['CARDSTATUS']]));
                $objActSheet->setCellValue('K'.$i, g2u($status_arr['RECEIPTSTATUS'][$r['RECEIPTSTATUS']]));
                $objActSheet->setCellValueExplicit('L'.$i, $r['RECEIPTNO']);
                $objActSheet->setCellValue('M'.$i, g2u($status_arr['INVOICE_STATUS'][$r['INVOICE_STATUS']]));
                $objActSheet->setCellValue('N'.$i, $r['INVOICE_NO']);
                $objActSheet->setCellValue('O'.$i, oracle_date_format($r['CONFIRMTIME']));
                $objActSheet->setCellValue('P'.$i, g2u($status_arr['FINANCIALCONFIRM'][$r['FINANCIALCONFIRM']]));
                $objActSheet->setCellValue('Q'.$i, g2u($pay_arr[$r['PAY_TYPE']]));
                $objActSheet->setCellValue('R'.$i, $r['PAID_MONEY']);
                $objActSheet->setCellValue('S'.$i, $r['UNPAID_MONEY']);
                $objActSheet->setCellValue('T'.$i, g2u($r['DIRECTSALLER']));
                $objActSheet->setCellValue('U'.$i, $r['TOTAL_PRICE']);
				$is_take = $r['IS_TAKE'] == 1 ? '��' : '��';
				if($_REQUEST['case_type']=='fx'){
					$onetemp = D('Project')->get_feescale_by_cid_val2($r['CASE_ID'],1,$r['TOTAL_PRICE_AFTER'],1);
					if($onetemp)$bfb = $onetemp[0]['STYPE'] == 1 ? '%' : 'Ԫ';
					else $bfb= '';
					 

					$objActSheet->setCellValue('V'.$i, g2u($r['TOTAL_PRICE_AFTER'].$bfb));
					$objActSheet->setCellValue('W'.$i, g2u($is_take));  //�Ƿ����
					$objActSheet->setCellValue('X'.$i, $r['HOUSETOTAL']);
					$objActSheet->setCellValue('Y'.$i, g2u($r['AGENCY_NAME']));
					$objActSheet->setCellValue('Z'.$i, oracle_date_format($r['LEAD_TIME']));
					$objActSheet->setCellValue('AA'.$i, g2u($conf_zx_standard_arr[$r['DECORATION_STANDARD']]));
					$objActSheet->setCellValue('AB'.$i, g2u($r['ADD_USERNAME']));
					$objActSheet->setCellValue('AC'.$i, g2u($r['NOTE']));
                    $objActSheet->setCellValue('AM'.$i, oracle_date_format($r['SUBSCRIBETIME']));
                    $objActSheet->setCellValue('AN'.$i, oracle_date_format($r['SIGNTIME']));
					//��ѯ�����¼
					$member_pay_num = count($member_pay_arr[$r['ID']]);
					
					if($member_pay_num > 0)
					{
						for($start_row = 0 ; $start_row < $member_pay_num; $start_row ++ )
						{
							$objActSheet->setCellValue('AD'.($i + $start_row),  g2u($pay_arr[$member_pay_arr[$r['ID']][$start_row]['PAY_TYPE']]));
							$objActSheet->setCellValue('AE'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['TRADE_MONEY']);
							//���ڸ�ʽ
							$format_time = strtotime(oracle_date_format($member_pay_arr[$r['ID']][$start_row]['TRADE_TIME']));
							$objActSheet->setCellValue('AF'.($i + $start_row), date('Y', $format_time));
							$objActSheet->setCellValue('AG'.($i + $start_row), date('m', $format_time));
							$objActSheet->setCellValue('AH'.($i + $start_row), date('d', $format_time));
							$objActSheet->setCellValue('AI'.($i + $start_row), " ".$member_pay_arr[$r['ID']][$start_row]['MERCHANT_NUMBER']);
							$objActSheet->setCellValue('AJ'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['CVV2']);
							$objActSheet->setCellValue('AK'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['REFUND_MONEY']);
							$objActSheet->setCellValue('AL'.($i + $start_row), oracle_date_format($member_pay_arr[$r['ID']][$start_row]['REFUND_TIME']));

						}
						
						$i += $member_pay_num - 1;
					}
				}else{
					$objActSheet->setCellValue('V'.$i, g2u($is_take));  //�Ƿ����
					$objActSheet->setCellValue('W'.$i, $r['HOUSETOTAL']);
					$objActSheet->setCellValue('X'.$i, g2u($r['AGENCY_NAME']));
					$objActSheet->setCellValue('Y'.$i, oracle_date_format($r['LEAD_TIME']));
					$objActSheet->setCellValue('Z'.$i, g2u($conf_zx_standard_arr[$r['DECORATION_STANDARD']]));
					$objActSheet->setCellValue('AA'.$i, g2u($r['ADD_USERNAME']));
					$objActSheet->setCellValue('AB'.$i, g2u($r['NOTE']));
					
					//��ѯ�����¼
					$member_pay_num = count($member_pay_arr[$r['ID']]);
					
					if($member_pay_num > 0)
					{
						for($start_row = 0 ; $start_row < $member_pay_num; $start_row ++ )
						{
							$objActSheet->setCellValue('AC'.($i + $start_row),  g2u($pay_arr[$member_pay_arr[$r['ID']][$start_row]['PAY_TYPE']]));
							$objActSheet->setCellValue('AD'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['TRADE_MONEY']);
							//���ڸ�ʽ
							$format_time = strtotime(oracle_date_format($member_pay_arr[$r['ID']][$start_row]['TRADE_TIME']));
							$objActSheet->setCellValue('AE'.($i + $start_row), date('Y', $format_time));
							$objActSheet->setCellValue('AF'.($i + $start_row), date('m', $format_time));
							$objActSheet->setCellValue('AG'.($i + $start_row), date('d', $format_time));
							$objActSheet->setCellValue('AH'.($i + $start_row), " ".$member_pay_arr[$r['ID']][$start_row]['MERCHANT_NUMBER']);
							$objActSheet->setCellValue('AI'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['CVV2']);
							$objActSheet->setCellValue('AJ'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['REFUND_MONEY']);
							$objActSheet->setCellValue('AK'.($i + $start_row), oracle_date_format($member_pay_arr[$r['ID']][$start_row]['REFUND_TIME']));
						}
						
						$i += $member_pay_num - 1;
					}
				}
               
                
				if($_REQUEST['case_type']=='fx'){
					//$objActSheet->setCellValue('AK'.$i, g2u($r['TOTAL_PRICE_AFTER']));
				}
                
                $objActSheet->getRowDimension($i)->setRowHeight(24);
                
                $i++;
                
//                if($i > 1000)
//                {
//                    exit;
//                }
            }


            $log_info = array();
            $log_info['OP_UID'] = $this->uid;
            $log_info['OP_USERNAME'] = $this->tname;
            $log_info['OP_LOG'] = '������Ա��Ϣ��'.implode(",",$member_ids).'��';
            $log_info['ADD_TIME'] = date('Y-m-d H:i:s');
            $log_info['OP_CITY'] = $this->channelid;
            $log_info['OP_IP'] = GetIP();
            $log_info['TYPE'] = 1;

            member_opreate_log($log_info);
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
        header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xlsx");
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

        //��Ա����
        if(!empty($_POST['opreate']) && $_POST['opreate'] == 'import_data' && !empty($_FILES))
        {
            //���ؽ��
            $return = array(
                'state'=>0,
                'msg'=>'',
                'data'=>null,
            );

            //�����ַ���
            $error_str = '';

            //��ȡ��Ա��Ϣ EXCEL
            $tmp_file = $_FILES ['upfile'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['upfile'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];
            $caseType = trim($_REQUEST['case_type']); //ҵ������

            //ҵ��˵��
            $caseInfo = $caseType=='ds'?'����':'����';

            /*�б��ǲ���.xls�ļ����б��ǲ���excel�ļ�*/
            if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls")             
            {
                $return['msg'] = g2u('�Բ��𣬽�֧��xlsx/xls��ʽ�ļ���');
                die(json_encode($return));
            }
            
            //�жϴ�С
            Vendor('phpExcel.PHPExcel');
            Vendor('phpExcel.IOFactory.php');
            Vendor('phpExcel.Reader.Excel5.php');
            
            $PHPExcel = new PHPExcel();
            $PHPReader = new PHPExcel_Reader_Excel2007();
            
            $objPHPExcel = $PHPReader->load($tmp_file, 'UTF-8');
            /**��ȡexcel�ļ��еĵ�һ��������*/
            $currentSheet = $objPHPExcel->getSheet(0);
            /**ȡ�������к�*/
            $allColumn = $currentSheet->getHighestColumn();
            /**ȡ��һ���ж�����*/
            $allRow = $currentSheet->getHighestRow();

            //�ж�֧������¼��
            if($allRow>102){
                $return['msg'] = g2u('�Բ������֧�ֵ���100����¼��');
                die(json_encode($return));
            }

            $insert_member_data = array();
            $insert_pay_data = array();
            
            //��Ŀ��Ϣ���⴦��
            //Ȩ����Ŀ(ֻ����������ĿȨ�޵���Ŀ)
            $project = D('Project');
            $permission_project = $project->get_permission_project_by_uid($this->uid , $caseType);

            if(is_array($permission_project) && !empty($permission_project))
            {
                foreach ($permission_project as $key => $value)
                {
                    $project_id_arr[] = $value['PRO_ID'];
                }

                $cond['ID']  = array('IN', $project_id_arr);
            }
            if($caseType=='ds') {
                $cond['BSTATUS'] = array('IN', array(2, 3, 4));
            }else{
                $cond['MSTATUS'] = array('IN', array(2, 3, 4));
            }
            //����
            $cond['CITY_ID']  = array('EQ', $this->city_id);

            $project_info = $project->field('ID,PROJECTNAME')->where($cond)->select();

            $project_num = count($project_info);

            if($project_num == 0)
            {
                $return['msg'] = g2u('�Բ��𣬵���ʧ�ܣ���ǰ�û�û��' . $caseInfo . '��ĿȨ�ޣ�');
                die(json_encode($return));
            }
            
            $project_info_flip = array();
            foreach($project_info as $key => $value)
            {
                $project_info_flip[$value['PROJECTNAME']] = $value['ID']; 
            }
            
            $member_model = D('Member');
            $member_pay_model = D('MemberPay');
            
            //������׼
            $conf_zx_standard_arr = $member_model->get_conf_zx_standard();
            //��Ա״̬
            $status_arr = $member_model->get_conf_all_status_remark('CARDSTATUS');
            //�վ�״̬
            $receipt_status_arr = $member_model->get_conf_all_status_remark('RECEIPTSTATUS');
            //��Ʊ״̬
            $invoice_status_arr = $member_model->get_conf_invoice_status_remark();
            //��Ҫ��ת������������Ϣ
            $cfg_certificate_type_flip = array_flip($member_model->get_conf_certificate_type());
            $cfg_source_flip = array_flip($member_model->get_conf_member_source_remark());
            $cfg_cardstatus_flip = array_flip($status_arr['CARDSTATUS']);
            $cfg_pay_type_flip = array_flip($member_pay_model->get_conf_pay_type());
            $cfg_receiptstatus_flip = array_flip($receipt_status_arr['RECEIPTSTATUS']);
            $cfg_invoice_status_flip = array_flip($invoice_status_arr['INVOICE_STATUS']);


            //���Ͷ��ź��Ƿ����
            $cfg_sms_flip = array('������' => 1, '����' => 2);
            $cfg_istake_flip = array('��' => 2, '��' => 1);


            $i = 0;
            $prj_id_arr = array(); //��Ŀ�������
            $prj_add_user = array();//����������
            for($currentRow = 3; $currentRow <= $allRow; $currentRow++)
            {   
                //*��Ŀ����
                $PRJ_NAME = $objPHPExcel->getActiveSheet()->getCell("A".$currentRow)->getValue();

                if(trim($PRJ_NAME) == '')
                {
                    $error_str .= "��" . ($i+1) ."�У���Ŀ����Ϊ��<br />";
                    $i++;
                    continue;
                }
				//�жϵ��̷���
				$insert_member_data[$i]['IS_DIS'] = $_REQUEST['case_type'] == 'fx'?2:1;
                //��Ŀ����
                $insert_member_data[$i]['PRJ_NAME'] = trim(u2g($PRJ_NAME));

                //��ĿID
                $insert_member_data[$i]['PRJ_ID'] = intval($project_info_flip[$insert_member_data[$i]['PRJ_NAME']]);

                if(empty($insert_member_data[$i]['PRJ_ID']))
                {
                    $error_str .= "��" . ($i+1) ."�У�������{$caseInfo}��ĿȨ�޷�Χδ�鵽 <" . $insert_member_data[$i]['PRJ_NAME'] . "> ����Ŀ��Ϣ<br />";
                    $i++;
                    continue;
                }

                $prj_id_arr[] = $insert_member_data[$i]['PRJ_ID'];

                //��Ա���ڳ���
                $insert_member_data[$i]['CITY_ID'] = $this->channelid;

                //*�ֻ�����
                $insert_member_data[$i]['MOBILENO'] = $objPHPExcel->getActiveSheet()->getCell("B".$currentRow)->getValue();

                //*��Ա����
                $insert_member_data[$i]['REALNAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("C".$currentRow)->getValue());

                //�������ֻ���
                $insert_member_data[$i]['LOOKER_MOBILENO'] = $objPHPExcel->getActiveSheet()->getCell("D".$currentRow)->getValue();

                //*֤������
                $CERTIFICATE_TYPE = u2g($objPHPExcel->getActiveSheet()->getCell("E".$currentRow)->getValue());
                $insert_member_data[$i]['CERTIFICATE_TYPE'] = intval($cfg_certificate_type_flip[$CERTIFICATE_TYPE]);

                //*֤������
                $insert_member_data[$i]['CERTIFICATE_NO'] = $objPHPExcel->getActiveSheet()->getCell("F".$currentRow)->getValue();

                //*��Ա��Դ
                $SOURCE = u2g($objPHPExcel->getActiveSheet()->getCell("G".$currentRow)->getValue());
                $insert_member_data[$i]['SOURCE'] = intval($cfg_source_flip[$SOURCE]);

                //*�Ƿ����
                $IS_TAKE = u2g($objPHPExcel->getActiveSheet()->getCell("H".$currentRow)->getValue());
                $insert_member_data[$i]['IS_TAKE'] = intval($cfg_istake_flip[$IS_TAKE]);

                //*�쿨״̬
                $CARDSTATUS = trim(u2g($objPHPExcel->getActiveSheet()->getCell("I".$currentRow)->getValue()));
                $insert_member_data[$i]['CARDSTATUS'] = intval($cfg_cardstatus_flip[$CARDSTATUS]);

                //*�쿨����
                $cardtime = $objPHPExcel->getActiveSheet()->getCell("J".$currentRow)->getValue();
                $insert_member_data[$i]['CARDTIME'] = $cardtime?date('Y-m-d', strtotime($cardtime)):null;

                //�Ϲ�����
                $subscribetime = $objPHPExcel->getActiveSheet()->getCell("K".$currentRow)->getValue();
                $insert_member_data[$i]['SUBSCRIBETIME'] = $subscribetime?date('Y-m-d', strtotime($subscribetime)):null;

                //ǩԼ����
                $signtime = $objPHPExcel->getActiveSheet()->getCell("L".$currentRow)->getValue();
                $insert_member_data[$i]['SIGNTIME'] = $signtime?date('Y-m-d', strtotime($signtime)):null;

                //ֱ����Ա
                $insert_member_data[$i]['DIRECTSALLER']  = u2g($objPHPExcel->getActiveSheet()->getCell("M".$currentRow)->getValue());

                //*���ʽ
                $PAY_TYPE = u2g($objPHPExcel->getActiveSheet()->getCell("N".$currentRow)->getValue());
                $insert_member_data[$i]['PAY_TYPE'] = intval($cfg_pay_type_flip[$PAY_TYPE]);

                //----------------------������ϸ----------------------//
                $insert_pay_data[$i]['PAY_TYPE'] = $insert_member_data[$i]['PAY_TYPE'];

                //6λ������
                $insert_pay_data[$i]['RETRIEVAL'] = u2g($objPHPExcel->getActiveSheet()->getCell("O".$currentRow)->getValue());

                //���ź�4λ
                $insert_pay_data[$i]['CVV2'] = u2g($objPHPExcel->getActiveSheet()->getCell("P".$currentRow)->getValue());

                //ԭʼ����ʱ��
                $trade_time = u2g($objPHPExcel->getActiveSheet()->getCell("Q".$currentRow)->getFormattedValue());
                $insert_pay_data[$i]['TRADE_TIME'] = $trade_time?date('Y-m-d H:i:s', strtotime($trade_time)):null;

                //ԭʼ���׽��
                $insert_pay_data[$i]['ORIGINAL_MONEY'] = floatval(u2g($objPHPExcel->getActiveSheet()->getCell("R".$currentRow)->getValue()));
                $insert_pay_data[$i]['TRADE_MONEY'] = $insert_pay_data[$i]['ORIGINAL_MONEY'];

                //�̻����
                $insert_pay_data[$i]['MERCHANT_NUMBER'] = $objPHPExcel->getActiveSheet()->getCell("S".$currentRow)->getValue();

                //----------------------������ϸ----------------------//
				if($_REQUEST['case_type']=='fx'){
					$insert_member_data[$i]['OUT_REWARD_STATUS'] = 1;
					$insert_member_data[$i]['REWARD_STATUS'] = 1;
					//ǰӶ�շѱ�׼
					$insert_member_data[$i]['TOTAL_PRICE'] = $objPHPExcel->getActiveSheet()->getCell("T".$currentRow)->getValue();
					//��Ӷ�շѱ�׼
					$insert_member_data[$i]['TOTAL_PRICE_AFTER'] = $objPHPExcel->getActiveSheet()->getCell("U".$currentRow)->getValue();

					//ǰӶ�н�Ӷ��
					$insert_member_data[$i]['AGENCY_REWARD'] = $insert_member_data[$i]['TOTAL_PRICE']?$objPHPExcel->getActiveSheet()->getCell("V".$currentRow)->getValue():'';
					//��Ӷ�н�Ӷ��
					$insert_member_data[$i]['AGENCY_REWARD_AFTER'] = $insert_member_data[$i]['TOTAL_PRICE_AFTER']?$objPHPExcel->getActiveSheet()->getCell("W".$currentRow)->getValue():'';
					//����ʱ��
					//$insert_member_data[$i]['FILINGTIME'] = $objPHPExcel->getActiveSheet()->getCell("W".$currentRow)->getValue();
					$filengtime = $objPHPExcel->getActiveSheet()->getCell("X".$currentRow)->getValue();
					$insert_member_data[$i]['FILINGTIME'] = $filengtime?date('Y-m-d', strtotime($filengtime)):null;

					//*�վ�״̬
					$RECEIPTSTATUS = u2g($objPHPExcel->getActiveSheet()->getCell("Y".$currentRow)->getValue());
					$insert_member_data[$i]['RECEIPTSTATUS'] = intval($cfg_receiptstatus_flip[$RECEIPTSTATUS]);

					//�վݱ��
					$receiptno = $objPHPExcel->getActiveSheet()->getCell("Z".$currentRow)->getValue();
					$receiptno = preg_replace('/([^0-9])+/',' ',$receiptno);
					$receiptno = preg_replace('/(\s)+/',' ',$receiptno);
					$insert_member_data[$i]['RECEIPTNO'] = $receiptno;

					//¥������
					$insert_member_data[$i]['ROOMNO'] = $objPHPExcel->getActiveSheet()->getCell("AA".$currentRow)->getValue();

					//�������
					$insert_member_data[$i]['HOUSEAREA'] = $objPHPExcel->getActiveSheet()->getCell("AB".$currentRow)->getValue();

					//�����ܼ�
					$insert_member_data[$i]['HOUSETOTAL'] = $objPHPExcel->getActiveSheet()->getCell("AC".$currentRow)->getValue();

					//���͹�˾
					$insert_member_data[$i]['AGENCY_NAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("AD".$currentRow)->getValue());

					//����ʱ��
					$lead_time = u2g($objPHPExcel->getActiveSheet()->getCell("AE".$currentRow)->getFormattedValue());
					$insert_member_data[$i]['LEAD_TIME'] = $lead_time?date('Y-m-d H:i:s', strtotime($lead_time)):null;

					//װ�ޱ�׼
					$decoration_standard = trim($objPHPExcel->getActiveSheet()->getCell("AF".$currentRow)->getValue());
					$insert_member_data[$i]['DECORATION_STANDARD'] = array_search(u2g($decoration_standard),$conf_zx_standard_arr);
					$insert_member_data[$i]['DECORATION_STANDARD'] = intval($insert_member_data[$i]['DECORATION_STANDARD']);

					//*������
					$insert_member_data[$i]['ADD_USERNAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("AG".$currentRow)->getValue());
					$prj_add_user[] = $insert_member_data[$i]['ADD_USERNAME'];

					//��ע
					$insert_member_data[$i]['NOTE'] = u2g($objPHPExcel->getActiveSheet()->getCell("AH".$currentRow)->getValue());
					$insert_member_data[$i]['CREATETIME'] = date('Y-m-d H:i:s');

				}else{
					//�����շѱ�׼
					$insert_member_data[$i]['TOTAL_PRICE'] = $objPHPExcel->getActiveSheet()->getCell("T".$currentRow)->getValue();

					//�н�Ӷ��
					$insert_member_data[$i]['AGENCY_REWARD'] = $objPHPExcel->getActiveSheet()->getCell("U".$currentRow)->getValue();

					//*�վ�״̬
					$RECEIPTSTATUS = u2g($objPHPExcel->getActiveSheet()->getCell("V".$currentRow)->getValue());
					$insert_member_data[$i]['RECEIPTSTATUS'] = intval($cfg_receiptstatus_flip[$RECEIPTSTATUS]);

					//�վݱ��
					$receiptno = $objPHPExcel->getActiveSheet()->getCell("W".$currentRow)->getValue();
					$receiptno = preg_replace('/([^0-9])+/',' ',$receiptno);
					$receiptno = preg_replace('/(\s)+/',' ',$receiptno);
					$insert_member_data[$i]['RECEIPTNO'] = $receiptno;

					//¥������
					$insert_member_data[$i]['ROOMNO'] = $objPHPExcel->getActiveSheet()->getCell("X".$currentRow)->getValue();

					//�������
					$insert_member_data[$i]['HOUSEAREA'] = $objPHPExcel->getActiveSheet()->getCell("Y".$currentRow)->getValue();

					//�����ܼ�
					$insert_member_data[$i]['HOUSETOTAL'] = $objPHPExcel->getActiveSheet()->getCell("Z".$currentRow)->getValue();

					//���͹�˾
					$insert_member_data[$i]['AGENCY_NAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("AA".$currentRow)->getValue());

					//����ʱ��
					$lead_time = u2g($objPHPExcel->getActiveSheet()->getCell("AB".$currentRow)->getFormattedValue());
					$insert_member_data[$i]['LEAD_TIME'] = $lead_time?date('Y-m-d H:i:s', strtotime($lead_time)):null;

					//װ�ޱ�׼
					$decoration_standard = trim($objPHPExcel->getActiveSheet()->getCell("AC".$currentRow)->getValue());
					$insert_member_data[$i]['DECORATION_STANDARD'] = array_search(u2g($decoration_standard),$conf_zx_standard_arr);
					$insert_member_data[$i]['DECORATION_STANDARD'] = intval($insert_member_data[$i]['DECORATION_STANDARD']);

					//*������
					$insert_member_data[$i]['ADD_USERNAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("AD".$currentRow)->getValue());
					$prj_add_user[] = $insert_member_data[$i]['ADD_USERNAME'];

					//��ע
					$insert_member_data[$i]['NOTE'] = u2g($objPHPExcel->getActiveSheet()->getCell("AE".$currentRow)->getValue());
					$insert_member_data[$i]['CREATETIME'] = date('Y-m-d H:i:s');


                }
                $i ++;
            }

            //�ȵ�һ��������֤
            if($error_str){
                $return['msg'] = g2u($error_str);
                die(@json_encode($return));
            }

            //�ڶ���������֤
            $error_str = '';

            $case_model = D("ProjectCase");

            /***���ݹ�����֤***/
            foreach($insert_member_data as $key => $value)
            {

                //��ȡcaseid
				if($_REQUEST['case_type']=='fx'){
					$case_info = $case_model->get_info_by_pid($value['PRJ_ID'], 'fx',array('ID','PROJECT_ID'));
					$case_id = $case_info[0]['ID'];
				}else{
					$case_info = $case_model->get_info_by_pid($value['PRJ_ID'], 'ds',array('ID','PROJECT_ID'));
					$case_id = $case_info[0]['ID'];
				}


                // �����շѱ�׼ = �ѽ� + δ��
                $insert_member_data[$key]['PAID_MONEY'] = $insert_pay_data[$key]['TRADE_MONEY'];
                $insert_member_data[$key]['UNPAID_MONEY'] = $value['TOTAL_PRICE'] - $insert_pay_data[$key]['TRADE_MONEY'];

                //���δ���ɽ��С��0
                if($insert_member_data[$key]['UNPAID_MONEY'] < 0){
                    $error_str .= "��" . ($key+1) . "�У��ѽ��ɽ�� > �����շѱ�׼��<br />";
                    continue;
                }

                //��Ʊ״̬
                $insert_member_data[$key]['INVOICE_STATUS'] = 1;

                //�����Ͷ���
                $insert_member_data[$key]['IS_SMS'] = 1;

                //��֤��ĿȨ��
//                $ret_data = M("erp_prorole")
//                    ->where("use_id = " . $this->uid . " and pro_id = " . $value['PRJ_ID'] . " and erp_id = 1 and isvalid = -1")
//                    ->select();
//
//                if(!$ret_data){
//                    $error_str .= "��" . ($key+1) . "�У���Ŀ�޵���ҵ�������û�и���Ŀ��Ȩ�ޣ�<br />";
//                    continue;
//                }

                //��֤������
                $add_user_info = M('Erp_users')
                    ->field('ID,USERNAME,NAME')
                    ->where("USERNAME = '{$value['ADD_USERNAME']}'")
                    ->find();

                if(!$add_user_info){
                    $error_str .= "��" . ($key+1) . "�У���������Ч��<br />";
                    continue;
                }

                //��Ӱ��������Ϣ
                $insert_member_data[$key]['CASE_ID'] = $case_id;

                //��Ӿ�������Ϣ
                $insert_member_data[$key]['ADD_UID'] = $add_user_info['ID'];
                $insert_member_data[$key]['ADD_USERNAME'] = $add_user_info['NAME'];

                //�ֻ�����
                if($value['MOBILENO'] == '') {
                    $error_str .= "��" . ($key+1) . "�У�����д�������ֻ��ţ�<br />";
                    continue;
                }

                //�ֻ�����֤
                if($value['MOBILENO'] && !preg_match("/^1[3-9]\d{9}$/",$value['MOBILENO'])){
                    $error_str .= "��" . ($key+1) . "�У�����д��ȷ�Ĺ������ֻ��ţ�<br />";
                    continue;
                }

                //����
                if($value['REALNAME'] == '') {
                    $error_str .= "��" . ($key+1) . "�У�����д��Ա������<br />";
                    continue;
                }

                //֤������
                if(empty($value['CERTIFICATE_TYPE'])) {
                    $error_str .= "��" . ($key+1) . "�У���ѡ��֤�����ͣ�<br />";
                    continue;
                }

                //֤������
                if($value['CERTIFICATE_TYPE']==1 && !preg_match("/^(\d{18}|\d{15}|\d{17}(x|X))$/",$value['CERTIFICATE_NO'])) {
                    $error_str .= "��" . ($key+1) . "�У�����д��ȷ�����֤�ţ�<br />";
                    continue;
                }

                //֤���Ų�Ϊ��
                if($value['CERTIFICATE_TYPE'] != 1 && $value['CERTIFICATE_NO']=='') {
                    $error_str .= "��" . ($key+1) . "�У�֤�����벻��Ϊ�գ�<br />";
                    continue;
                }

                //��Ա��Դ
                if(empty($value['SOURCE'])) {
                    $error_str .= "��" . ($key+1) . "�У���ѡ���Ա��Դ��<br />";
                    continue;
                }

                //�Ƿ��н����
                if(empty($value['IS_TAKE'])) {
                    $error_str .= "��" . ($key+1) . "�У���ѡ���Ƿ��н������<br />";
                    continue;
                }

                //�쿨״̬
                if(empty($value['CARDSTATUS'])) {
                    $error_str .= "��" . ($key+1) . "�У���ѡ���Ա�쿨״̬��<br />";
                    continue;
                }

                //�쿨����
                if(empty($value['CARDTIME']) && $_REQUEST['case_type']!='fx') {
                    $error_str .= "��" . ($key+1) . "�У�����д��Ա�쿨ʱ�䣡<br />";
                    continue;
                }

                //���ʽ
                if(empty($value['PAY_TYPE']) && $_REQUEST['case_type']!='fx' ) {
                    $error_str .= "��" . ($key+1) . "�У���ѡ���Ա���ʽ��<br />";
                    continue;
                }

                //�վ�״̬
                if(empty($value['RECEIPTSTATUS']) && $_REQUEST['case_type']!='fx' ){
                    $error_str .= "��" . ($key+1) . "�У���ѡ���վ�״̬��<br />";
                    continue;
                }
				//�վݱ��
				if($value['RECEIPTNO']  ){
						$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$value['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
						if($receiptno){
							
							$error_str .= "��" . ($key+1) . "�ó������Ѿ�������ͬ���վݱ��<br />";
							
							continue;
						}
				}
				if($_REQUEST['case_type']=='fx'){
					if($value['TOTAL_PRICE']){
						if(empty($value['TOTAL_PRICE'])   ) {
							$error_str .= "��" . ($key+1) . "�У���ѡ��д�����շѱ�׼!<br />";
							continue;
						}

						if(empty($value['RECEIPTNO'])   ) {
							$error_str .= "��" . ($key+1) . "�У�����д�վݱ�ţ�<br />";
							continue;
						}

					}
					if(empty($value['FILINGTIME'])){
						$error_str .= "��" . ($key+1) . "�У�����д����ʱ�䣡<br />";
							continue;

					}

					if( empty($value['TOTAL_PRICE']) && empty($value['TOTAL_PRICE_AFTER'])){
						$error_str .= "��" . ($key+1) . "�У� ǰӶ�շѱ�׼���ߺ�Ӷ�շѱ�׼����һ�<br />";
						continue;
					}

					
					if($value['CARDSTATUS']==3 && ($value['SOURCE']==1 || $value['SOURCE']==7 || $value['SOURCE']==8) && empty($value['AGENCY_REWARD']) && empty($value['AGENCY_REWARD_AFTER'])) {
						$error_str .= "��" . ($key+1) . "�У��쿨״̬Ϊ�Ѱ���ǩԼ����Ա��ԴΪ�н���߷�����˾,ǰӶ�н�Ӷ����ߺ�Ӷ�н�Ӷ�����һ�<br />";
						continue;
					}

					$project = D('Project');
					$case_model = D('ProjectCase');
					$case_info = $case_model->get_info_by_pid($value['PRJ_ID'], 'fx');
					$case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
					//$flag1 = $project->get_feescale_by_cid_stype($case_id,1, $value['TOTAL_PRICE'],1,0);
					$flag2 = $project->get_feescale_by_cid_stype($case_id,1, $value['TOTAL_PRICE_AFTER'],1,1);
					//$flag3 = $project->get_feescale_by_cid_stype($case_id,2, $value['AGENCY_REWARD'],1,0);
					$flag4 = $project->get_feescale_by_cid_stype($case_id,2, $value['AGENCY_REWARD_AFTER'],1,1);
					if( $flag2  ||$flag4 ){
						if(!$value['HOUSETOTAL']){
							 
							$error_str .= "��" . ($key+1) . "�У���Ӷ�շѱ�׼��Ӷ��Ϊ�ٷֱȣ�������д�����ܼ�!<br />";
							continue;
						}
					}


				}else{
					if($value['CARDSTATUS']==3 && ($value['SOURCE']==1 || $value['SOURCE']==7 || $value['SOURCE']==8) && empty($value['AGENCY_REWARD'])) {
						$error_str .= "��" . ($key+1) . "�У��쿨״̬Ϊ�Ѱ���ǩԼ����Ա��ԴΪ�н�������˾,�н�Ӷ����<br />";
						continue;
					}
				}

                if(empty($value['TOTAL_PRICE']) && $_REQUEST['case_type']!='fx' ) {
                    $error_str .= "��" . ($key+1) . "�У���ѡ��д�����շѱ�׼!<br />";
                    continue;
                }

                if(empty($value['RECEIPTNO']) && $_REQUEST['case_type']!='fx' ) {
                    $error_str .= "��" . ($key+1) . "�У�����д�վݱ�ţ�<br />";
                    continue;
                }

                if($value['CITY_ID']==1 && $value['CARDSTATUS']==3 && $value['ROOMNO']=='') {
                    $error_str .= "��" . ($key+1) . "�У��쿨�û�Ϊ�Ѱ���ǩԼ״̬������д¥�����ţ�<br />";
                    continue;
                }

                if($value['CARDSTATUS']==3 && ($value['LEAD_TIME']=='' || !$value['DECORATION_STANDARD'])) {
                    $error_str .= "��" . ($key+1) . "�У��쿨�û�Ϊ�Ѱ���ǩԼ״̬������д����ʱ���װ�ޱ�׼��<br />";
                    continue;
                }

                //�쿨״̬

                $card_flag = false;
                switch($value['CARDSTATUS'])
                {
                    //���Ϲ�
                    case '2':
                        $insert_member_data[$key]['SUBSCRIBETIME'] = strip_tags($value['SUBSCRIBETIME']);
                        $insert_member_data[$key]['SIGNTIME'] = null;
                        $insert_member_data[$key]['SIGNEDSUITE'] = null;
                        if(!$value['SUBSCRIBETIME']) {
                            $error_str .= "��" . ($key + 1) . "�У��쿨״̬Ϊ�Ѱ����Ϲ����Ϲ����ڱ�����д��<br />";
                            $card_flag = true;
                        }
                        break;
                    case '3':
                        //��ǩԼ
                        $insert_member_data[$key]['SIGNTIME'] = strip_tags($value['SIGNTIME']);
                        //ǩԼ����Ĭ��Ϊ1
                        $insert_member_data[$key]['SIGNEDSUITE'] = 1;

                        if(empty($value['SIGNTIME']))
                        {
                            $error_str .= "��" . ($key + 1) . "�У��쿨״̬Ϊ�Ѱ���ǩԼ��ǩԼ���ڱ�����д��<br />";
                            $card_flag = true;
                        }
                        break;
                }

                if($card_flag){
                    continue;
                }

                //�쿨״ֻ̬֧��    �Ѱ�δ�ɽ����Ѱ���ǩԼ���Ѱ����Ϲ�
                if($value['CARDSTATUS']>3){
                    $error_str .= "��" . ($key + 1) . "�У��ף��쿨״ֻ̬֧��  �Ѱ�δ�ɽ����Ѱ���ǩԼ���Ѱ����Ϲ���<br />";
                    continue;
                }

                //���ʽ����֤
                //�̻����
                $merchant_arr = array();
                $merchant_info = M('erp_merchant')->where("CITY_ID = '".$this->city_id."'")->select();
                if(is_array($merchant_info) && !empty($merchant_info))
                {
                    foreach($merchant_info as $k => $v)
                    {
                        $large_str = '';
                        $v['IS_LARGE'] == 1 ? $large_str .= '[���]' : '';
                        $merchant_arr[$v['MERCHANT_NUMBER']] = $v['MERCHANT_NUMBER'].$large_str;
                    }
                }

                $pay_flag = false;

                //�����POS����ʽ
                if($value['PAY_TYPE']==1){
                    if(strlen($insert_pay_data[$key]['RETRIEVAL']) != 6){
                        $error_str .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ��6λ����������<br />";
                        $pay_flag = true;
                    }
                    if(empty($insert_pay_data[$key]['MERCHANT_NUMBER'])){
                        $error_str .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ���̻����δ��д��<br />";
                        $pay_flag = true;
                    }
                    if(empty($insert_pay_data[$key]['TRADE_TIME']) && $_REQUEST['case_type']!='fx'){
                        $error_str .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ��ԭʼ����ʱ�䲻��Ϊ�գ�<br />";
                        $pay_flag = true;
                    }
                    if($insert_pay_data[$key]['TRADE_MONEY']==0){
                        $error_str .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ��ԭʼ���׽���Ϊ�գ�<br />";
                        $pay_flag = true;
                    }

                    //�ж��Ƿ��Ǵ���(�̻����)
                    if(strpos($merchant_arr[$insert_pay_data[$key]['MERCHANT_NUMBER']],"���")!==false){
                        if(strlen($insert_pay_data[$key]['CVV2'])<10){
                            $error_str .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ���̻����ѡ�����дȫ���ţ�<br />";
                            $pay_flag = true;
                        }
                    }
                    else
                    {
                        if(strlen($insert_pay_data[$key]['CVV2']) != 4) {
                            $error_str .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ�����ſ��ź���λ��<br />";
                            $pay_flag = true;
                        }
                    }
                }
                //������ֽ��������ʽ
                else if($value['PAY_TYPE']==2 || $value['PAY_TYPE']==3){
                    if($insert_pay_data[$key]['TRADE_MONEY']==0 || empty($insert_pay_data[$key]['TRADE_TIME'])){
                        $error_str .= '��' . ($key+1) . '��,' . "���ʽΪ�ֽ���������ĸ�����ϸ��ԭʼ���׽���ԭʼ����ʱ�䲻��Ϊ�գ�<br />";
                        $pay_flag = true;
                    }
                }

                if($pay_flag){
                    continue;
                }
            }

            //���ؽ����
            if($error_str){
                $return['msg'] = g2u($error_str);
                die(@json_encode($return));
            }

            //-------------------���ݿ����-------------------//
            //��������
            $insert_success_num = 0;
            //�����Ա����
            $member_num = count($insert_member_data);
            
            $member_pay = D('MemberPay');
            $pay_status_arr = $member_pay->get_conf_status();

            //����MODEL
            $income_model = D('ProjectIncome');
            $insert_member_id = 0;

            if(is_array($insert_member_data) && !empty($insert_member_data))
            {
                $this->model = new Model();
                //����ʼ
                $this->model->startTrans();
                foreach($insert_member_data as $key => $member_info)
                {
                    //��ӻ�Ա��Ϣ
                    $insert_member_id = $member_model->add_member_info($member_info);

                    if($insert_member_id > 0)
                    {

                        //������crm
                        if($member_info['CARDSTATUS']) {
                            switch ($member_info['CARDSTATUS']) {
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
                            }
                            $crm_api_arr = array();
                            //�û���
                            $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                            //����
                            $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                            //���
                            $crm_api_arr['activefrom'] = 104;
                            //����
                            $crm_api_arr['city'] = $this->user_city_py;
                            //װ�ޱ�׼
                            $conf_zx_standard = $member_model->get_conf_zx_standard();
                            //��Ϊ
                            $crm_api_arr['activename'] = urlencode($member_info['PRJ_NAME'] . "-" .
                                    $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']] . "-" . $member_info['CARDTIME'] . "-" .
                                    $conf_zx_standard[$member_info['DECORATION_STANDARD']]);

                            //��Դ
                            $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                            //֧��ʱ��
                            $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = $member_info['CARDTIME'];
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                            $crm_api_arr['tlf_username'] = trim($this->uname);
                            //��ĿID
                            $crm_api_arr['projectid'] = $member_info['PRJ_ID'];

                            //if ($member_info['CARDSTATUS'] == 3)
                             //   $crm_api_arr['floor_id'] = $member_info['PRJ_ID'];

                            //�ύ
                            $crm_url = submit_crm_data_by_api_url($crm_api_arr);
                            $ret_log = api_log($this->city_id,$crm_url,0,$this->uid,2);
                        }

                    }
                    
                    //���֧����Ϣ
                    if($insert_member_id > 0 && !empty($insert_pay_data[$key]) && $insert_pay_data[$key]['TRADE_MONEY']  )
                    {
                        //������ϸ״̬
                        $insert_pay_data[$key]['MID'] = $insert_member_id;
                        $insert_pay_data[$key]['STATUS'] = $pay_status_arr['wait_confirm'];
                        $insert_pay_data[$key]['ADD_UID'] = $this->uid;
                        $insert_payment_id = $member_pay->add_member_info($insert_pay_data[$key]);
 
                        if($insert_payment_id > 0)
                        {
                            //���������Ϣ�������
                            $income_info['CASE_ID'] = $member_info['CASE_ID'];
                            $income_info['ENTITY_ID'] = $insert_member_id;
                            $income_info['PAY_ID'] = $insert_payment_id;
                            $income_info['INCOME_FROM'] = 1;//���̻�Ա֧��
                            $income_info['INCOME'] = $insert_pay_data[$key]['TRADE_MONEY'];
                            $income_info['INCOME_REMARK'] = '';
                            $income_info['ADD_UID'] = $this->uid;
                            $income_info['OCCUR_TIME'] = $insert_pay_data[$key]['TRADE_TIME'];
                            $insert_sy_id = $income_model->add_income_info($income_info);
                        }
                    }

                    //�ύ�������
					if($insert_pay_data[$key]['TRADE_MONEY']){
						if( $insert_member_id > 0 && $insert_payment_id > 0 && $insert_sy_id > 0 && $ret_log)
                        $insert_success_num ++;
					}else{
						if( $insert_member_id > 0 && $ret_log)
                        $insert_success_num ++;
					}
                }
            }


            //������
            if($insert_success_num == $member_num)
            {   
                $this->model->commit();
                $return['state']  = 1;
                $return['msg'] = g2u('��Ա���ݵ���ɹ�');
                die( json_encode($return));
            }
            else
            {   
                $this->model->rollback();
                $return['state']  = 0;
                $return['msg'] = g2u("��Ա���ݵ���ʧ��,������.... $insert_member_id  -  $insert_payment_id  - $insert_sy_id - $ret_log");
                die(json_encode($return));
            }
        }
        else
        {
            $this->assign('case_type',$_REQUEST['case_type']);
			$this->display('import_member');
        }
    }

    /**
     * ��ȡ�ɹ�����Ĺ�����ID
     */
    public function getFlowId() {
        $response = array(
            'status' => false,
            'message' => '��������',
            'data' => ''
        );
        $advanceId = $_REQUEST['AdvanceId'];
        if (intval($advanceId) > 0) {
            try {
                $result = D()->query(sprintf(self::PAYOUT_FLOWID_SQL, $advanceId));
                if (notEmptyArray($result)) {
                    $response['status'] = true;
                    $response['message'] = '��ȡ������ID�ɹ�';
                    $response['data'] = $result[0]['ID'];
                } else {
                    $response['message'] = '�ñ�����Ŀ��δ���𳬶����!';
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
        }

        echo json_encode(g2u($response));
    }
	/**
    +----------------------------------------------------------
    * ���ܾ�״̬�Ա�
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
	public function prjbudget(){
		if($_POST['sub']){
			//print_r($_REQUEST);EXIT;
			$map = array();
			$prj_id = $_POST['prj_id'];//��Ŀid
			$buildno = $_POST['buildno'];//����
			$map['prj_id'] = $prj_id;
			$map['prj_name'] = array('like',trim($_POST['prj_name']));
			$map['roomno'] = array('like',($buildno)."-%");
			//print_r($map);exit;
			import('ORG.Util.Page');
			$count = M("Erp_cardmember")->where($map)->count();
			$Page = new Page($count);
			$show = $Page->show();//��ҳ
			$memberData = M("Erp_cardmember")
						  ->where($map)
						  ->limit($Page->firstRow.','.$Page->listRows)
						  ->select();
			
			if($memberData){
				$prjid = M('Erp_house')
						->where("project_id =".$prj_id)
						->getField("FORNANJING");//��ȡprjid
				
				if($prjid){
					$manageData=get_Compare_Data($prjid,$buildno);//���ܾ�����
					
					if($manageData){
						foreach($memberData as &$member){
							$room = explode('-',$member['ROOMNO']);
							foreach($manageData as $manage){
								if($room[1] == intval($manage['room'])){
									$member['HOUSESTATUS'] = $manage['presaleid'];
								}
							}
						}
					}
				}
			}
			
			$this->assign('page',$show);
			$this->assign('searchInfo',array('prj_name'=>$_POST['prj_name'],'prj_id'=>$prj_id,'buildno'=>$buildno));
			$this->assign('memberData',$memberData);
		}
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
		$this->display();
	}
    
    
    /**
     +----------------------------------------------------------
     * �����޸�״̬����
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function show_change_status_window()
    {
        Vendor('Oms.Form');
    	$form = new Form();
    	$form = $form->initForminfo(188);
        
        //ʵ������ԱMODEL
    	$member_model = D('Member');

        //��ȡ��Ŀ�����Ƿ�ͬ�������ͬ���Ը��Ľ�������ͬ��������ʾ,1��ͬ��0��ͬ
        $reward = $_REQUEST['isReward'] ? $_REQUEST['isReward']: '0';
        $memberId = $_REQUEST['memberId'] ? $_REQUEST['memberId']: '0';
        if($reward == 1){
            $form->setMyField('PROPERTY_DEAL_REWARD','FORMVISIBLE',-1)
                ->setMyField('AGENCY_DEAL_REWARD','FORMVISIBLE',-1)
                ->setMyField('OUT_REWARD','FORMVISIBLE',-1);
            if($memberId) {
                $caseId = M("Erp_cardmember")->where("ID=" . $memberId)->getField("CASE_ID");

                //�����շѱ�׼
                $feescale = array();
                $project = D('Project');
                $feescale = $project->get_feescale_by_cid($caseId);

                $fees_arr = array();
                if(is_array($feescale) && !empty($feescale) ) {
                    foreach ($feescale as $key => $value) {
                        $dw = $value['STYPE']?'%':'Ԫ';
                        if ($value['AMOUNT'][0] == '.') {
                            $value['AMOUNT'] = '0' . $value['AMOUNT'];
                        }
                        if($value['SCALETYPE']==1 || $value['SCALETYPE']==2){

                        if($value['MTYPE'])$fees_arr[$value['SCALETYPE'].'_'.$value['MTYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
                        else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;

                        }else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
                    }
                }

                //��ҵ����Ӷ��
                // $form->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                //�н�ɽ���
                $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                //��ҵ�ɽ�����
                $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
            }
        }
        /***��ȡ��Ա�쿨���վݡ���Ʊ״̬***/
        $status_arr = $member_model->get_conf_all_status_remark();
        
        //�쿨״̬
        $card_status_arr = $status_arr['CARDSTATUS'];
        $form->setMyField('CARDSTATUS', 'LISTCHAR', 
                array2listchar($card_status_arr), FALSE);
        
        //��Ʊ״̬
        $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                array2listchar($status_arr['INVOICE_STATUS']), FALSE);
        
        //�վ�״̬
        $receipt_status_arr = $status_arr['RECEIPTSTATUS'];
        $form->setMyField('RECEIPTSTATUS', 'LISTCHAR', 
                array2listchar($receipt_status_arr), FALSE);

        //��Ʊ״̬
        $form->setMyFieldVal('SUBSCRIBETIME', date('Y-m-d H:i:s',time()) , FALSE);
        $form->setMyFieldVal('SIGNTIME', date('Y-m-d H:i:s',time()) , FALSE);

        //֧��ʱ��
        $form->setMyFieldVal('LEAD_TIME', date('Y-m-d H:i:s',time()) , FALSE);

        //װ�ޱ�׼
        $conf_zx_standard = $member_model->get_conf_zx_standard();
        $form->setMyField('DECORATION_STANDARD', 'LISTCHAR', array2listchar($conf_zx_standard), FALSE);

        //�Ե���ʽ���֣��滻ԭ�б��棬ȡ����ť����
        $form->FORMCHANGEBTN = ' ';
        
        $form = $form->getResult();
        $this->assign('form', $form);
        $this->display('change_status_window');
    }
    
    /**
     +----------------------------------------------------------
     * �����޸Ļ�Ա״̬
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function batch_change_status()
    {
        $info = array();
        $id_arr = $_GET['memberId'];
        $card_status = !empty($_GET['card_status']) ? intval($_GET['card_status']) : 0;
        $invoice_status = !empty($_GET['invoice_status']) ? intval($_GET['invoice_status']) : 0;
        $receipstatus = !empty($_GET['receipstatus']) ?  intval($_GET['receipstatus']) : 0;
        $subscribetime = !empty($_GET['subscribetime']) ?  trim($_GET['subscribetime']) : '';
        $signtime = !empty($_GET['signtime']) ?  trim($_GET['signtime']) : '';
        $lead_time = !empty($_GET['lead_time']) ?  trim($_GET['lead_time']) : '';
        $decoration_standard = !empty($_GET['decoration_standard']) ?  trim($_GET['decoration_standard']) : 0;
        $property_deal_reward = !empty($_GET['property_deal_reward']) ?  trim($_GET['property_deal_reward']) : 0;
        $agency_deal_reward = !empty($_GET['agency_deal_reward']) ?  trim($_GET['agency_deal_reward']) : 0;
        $out_reward = !empty($_GET['out_reward']) ?  trim($_GET['out_reward']) : 0;

        $data = array();
        if ($property_deal_reward > 0) {
            $data['PROPERTY_DEAL_REWARD'] = $property_deal_reward;
        }
        if ($agency_deal_reward > 0) {
            $data['AGENCY_DEAL_REWARD'] = $agency_deal_reward;
        }
        if ($out_reward > 0) {
            $data['OUT_REWARD'] = $out_reward;
        }
        D()->startTrans();
        if(!empty($data) && count($data)>0){
            foreach($id_arr as $id){
                $out_reward_status = D('erp_cardmember')->where("ID=".$id)->getField("OUT_REWARD_STATUS");
                $agency_deal_reward_status = D('erp_cardmember')->where("ID=".$id)->getField("AGENCY_DEAL_REWARD_STATUS");
                $property_deal_reward_status = D('erp_cardmember')->where("ID=".$id)->getField("PROPERTY_DEAL_REWARD_STATUS");

				if( !empty($_GET['out_reward'])){
					if($out_reward_status == 2 || $out_reward_status == 5 ){
						D()->rollback();
						$info['state'] = 0;
						$info['msg'] = g2u('�޸�ʧ��,�ⲿ�ɽ����������뱨�����ѱ���,�޷��޸�');
						$info['out_reward_status'] =$out_reward_status;
						echo json_encode($info);
						exit;

					}
				}
				if( !empty($_GET['agency_deal_reward'])){
					if($agency_deal_reward_status != 1  ){
						D()->rollback();
						$info['state'] = 0;
						$info['msg'] = g2u('�޸�ʧ��,�н�ɽ����������뱨�����ѱ���,�޷��޸�');
						echo json_encode($info);
						exit;

					}
				}
				if( !empty($_GET['property_deal_reward'])){
					if($property_deal_reward_status != 1    ){
						D()->rollback();
						$info['state'] = 0;
						$info['msg'] = g2u('�޸�ʧ��,��ҵ���ʳɽ����������뱨�����ѱ���,�޷��޸�');
						echo json_encode($info);
						exit;

					}
				}
                $updataNum = D('erp_cardmember')->where("ID=".$id)->save($data);
                if($updataNum == ""){
                    D()->rollback();
                    $info['state'] = 0;
                    $info['msg'] = g2u('����ʧ��');
                    echo json_encode($info);
                    exit;
                }
            }
        }
        if($card_status == 0 && $invoice_status == 0 && $receipstatus == 0 && $property_deal_reward == 0 && $agency_deal_reward == 0 && $out_reward == 0)
        {
            D()->rollback();
            $info['state']  = 0;
            $info['msg'] = g2u('��������ʧ�ܣ��쿨״̬����Ʊ״̬���վ�״̬����Ҫѡ��һ��');
            echo json_encode($info);
            exit;
        }
        
                //�Ѱ����Ϲ�
        if($card_status == '2' && $subscribetime == '')
        {
            D()->rollback();
            $info['state']  = 0;
            $info['msg'] = g2u('��������ʧ�ܣ��쿨״̬Ϊ�Ѱ����Ϲ����Ϲ��������ڱ�����д');
            echo json_encode($info);
            exit;
        }
        //�Ѱ���ǩԼ
        else if($card_status == '3' && ($signtime == '' || $lead_time=='' || $decoration_standard==0))
        {
            D()->rollback();
            $info['state']  = 0;
            $info['msg'] = g2u('��������ʧ�ܣ��쿨״̬Ϊ�Ѱ���ǩԼ��ǩԼ���ڡ�����ʱ�䡢װ�ޱ�׼������д');
            echo json_encode($info);
            exit;
        }
        
        $card_status_no_pass_num = 0;
        $invoice_status_no_pass_num = 0;
        $receipstatus_no_pass_num = 0;
        
        if(is_array($id_arr) && !empty($id_arr))
        {   
            $member_model = D('Member');
            
            //��Ա��Ʊ״̬
            $conf_invoice_status = $member_model->get_conf_invoice_status();
            
            //��Ա��Ϣ
            $member_info = array();
            $search_field = array('CARDSTATUS','INVOICE_STATUS','RECEIPTSTATUS','PRJ_NAME','PRJ_ID','REALNAME','MOBILENO','CITY_ID','CREATETIME');
            $member_info = $member_model->get_info_by_ids($id_arr, $search_field);
            
            if(is_array($member_info) && !empty($member_info))
            {  
                foreach ($member_info as $key => $value)
                {   
                    if($card_status > 0)
                    {
                        //�쿨״̬�ж�
                        if($value['CARDSTATUS'] == 1 && !in_array($card_status, array(1,2,3)))
                        {   
                            $card_status_no_pass_num ++;
                        }
                        else if($value['CARDSTATUS'] == 2 && !in_array($card_status, array(2,3)))
                        {
                            $card_status_no_pass_num ++;
                        }
                        else if($value['CARDSTATUS'] == 3 && $card_status != 3)
                        {
                            $card_status_no_pass_num ++;
                        }
                        else if($value['CARDSTATUS'] == 4 && $card_status != 4)
                        {
                            $card_status_no_pass_num ++;
                        }
                    }
                    
                    if($invoice_status > 0)
                    {
                        //��Ʊ״̬�ж�
                        if($value['INVOICE_STATUS'] == 1 && $invoice_status != 1)
                        {   
                            $invoice_status_no_pass_num ++;
                        }
                        else if($value['INVOICE_STATUS'] == 2 && !in_array($invoice_status, array(2,3)))
                        {
                            $invoice_status_no_pass_num ++;
                        }
                        else if($value['INVOICE_STATUS'] == 3 && !in_array($invoice_status, array(2,3)))
                        {
                            $invoice_status_no_pass_num ++;
                        }
                        else if($value['INVOICE_STATUS'] == 4 && $invoice_status != 4)
                        {
                            $invoice_status_no_pass_num ++;
                        }
                        else if($value['INVOICE_STATUS'] == 5 && ($invoice_status != 5 && $invoice_status != 1))
                        {
                            $invoice_status_no_pass_num ++;
                        }
                    }
                    
                    if($receipstatus > 0)
                    {
                        //�վ�״̬�ж�
                        if($value['RECEIPTSTATUS'] == 2 && !in_array($receipstatus, array(2,3,4)))
                        {   
                            $receipstatus_no_pass_num ++;
                        }
                        else if($value['RECEIPTSTATUS'] == 3 && !in_array($receipstatus, array(2,3,4)))
                        {
                            $receipstatus_no_pass_num ++;
                        }
                        else if($value['RECEIPTSTATUS'] == 4 && $receipstatus != 4)
                        {
                            $receipstatus_no_pass_num ++;
                        }
                    }
                }
            }
            else 
            {
                D()->rollback();
                $info['state']  = 0;
                $info['msg'] = g2u('��������ʧ�ܣ���Ա��Ϣ�쳣');
                echo json_encode($info);
                exit;
            }
            
            if($card_status_no_pass_num > 0)
            {
                D()->rollback();
                $info['state']  = 0;
                $msg = '�����޸�״̬ʧ��,['.$card_status_no_pass_num.']�����ݲ�����������'
                        . '<br>�޸Ļ�Ա�쿨״̬��Ҫ��������������'
                        . '<br>1���Ѱ쿨δ�ɽ�״̬�Ļ�Ա�����޸�Ϊ�Ѱ쿨���Ϲ������Ѱ쿨��ǩԼ��'
                        . '<br>2���Ѱ쿨���Ϲ�״̬�Ļ�Ա�����޸�Ϊ�Ѱ쿨��ǩԼ��'
                        . '<br>3���Ѱ쿨��ǩԼ�Ļ�Ա���޷��޸ģ�'
                        . '<br>4�����˿��Ļ�Ա���޷��޸ġ�';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            if($invoice_status_no_pass_num > 0)
            {
                D()->rollback();
                $info['state']  = 0;
                $msg = '�����޸�״̬ʧ��,['.$invoice_status_no_pass_num.']�����ݲ�����������'
                        . '<br>�޸Ļ�Ա��Ʊ״̬��Ҫ��������������'
                        . '<br>1��δ��״̬�������޸ģ�'
                        . '<br>2��������״̬��ֻ���޸�Ϊ��δ������״̬��'
                        . '<br>3���ѿ�δ��״̬�������޸�Ϊ���죻'
                        . '<br>4������״̬�������޸�Ϊ�ѿ�δ�죻'
                        . '<br>5�����ջ�״̬���޷��޸�״̬��';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            if($receipstatus_no_pass_num > 0)
            {
                D()->rollback();
                $info['state']  = 0;
                $msg = '�����޸�״̬ʧ��,['.$receipstatus_no_pass_num.']�����ݲ�����������'
                        . '<br>�޸Ļ�Ա�վ�״̬��Ҫ��������������'
                        . '<br>1���ѿ�δ����޸�Ϊ��������ջأ�'
                        . '<br>2��������޸�Ϊ���ջػ��ѿ�δ�죻'
                        . '<br>3�����ջز����޸��վ�״̬��';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            $update_arr = array();
            $card_status > 0 ? $update_arr['CARDSTATUS'] = $card_status : "";
            $invoice_status > 0 ? $update_arr['INVOICE_STATUS'] = $invoice_status : "";
            $receipstatus > 0 ? $update_arr['RECEIPTSTATUS'] = $receipstatus : "";
            if($card_status==3) {
                $update_arr['SIGNTIME'] = $signtime;
                //ǩԼ����
                $update_arr['SIGNEDSUITE'] = 1;
            }
            else if($card_status==2){
                $update_arr['SUBSCRIBETIME'] = $subscribetime;
            }
            if($lead_time != "" or $decoration_standard != ""){
                $update_arr['LEAD_TIME'] = $lead_time;
                $update_arr['DECORATION_STANDARD'] = $decoration_standard;
            }

            if($property_deal_reward > 0) {
                $update_arr['PROPERTY_DEAL_REWARD'] = $property_deal_reward;
            }

            if($agency_deal_reward > 0) {
                $update_arr['AGENCY_DEAL_REWARD'] = $agency_deal_reward;
            }

            if($out_reward > 0) {
                $update_arr['OUT_REWARD'] = $out_reward;
            }

            if(!empty($update_arr))
            {
                $result = $member_model->update_info_by_id($id_arr, $update_arr);
            }

            //ͬ����crmϵͳ
            /***��ȡ��Ա�쿨����Ʊ����Ʊ״̬***/
            $status_arr = $member_model->get_conf_all_status_remark();

            //װ�ޱ�׼
            $conf_zx_standard = $member_model->get_conf_zx_standard();

            //����������Ϣ
            $city_info =  $member_model->get_cityinfo("py");

            foreach ($member_info as $key => $value) {
                if ($value['CARDSTATUS'] != $card_status && $card_status) {
                    //��Ϊ
                    $activename = $value['PRJ_NAME'] . "�쿨״̬:{$status_arr['CARDSTATUS'][$card_status]}" . " ���ڣ�" . date("Y-m-d",time()) . $conf_zx_standard[$decoration_standard];

                    if ($value['CARDSTATUS'] < $card_status) {
                        if ($card_status == 3) {
                            //CRM֪ͨ��Ϣ
                            $tlfcard_status = 2;
                            $tlfcard_signtime = time();
                            $tlfcard_backtime = 0;

                            //�ύCRM����
                            $crm_api_arr = array();
                            $crm_api_arr['username'] = urlencode($value['REALNAME']);
                            $crm_api_arr['mobile'] = $value['MOBILENO'];
                            $crm_api_arr['activefrom'] = 104;
                            $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                            $crm_api_arr['city'] =$city_info[$value['CITY_ID']];
                            $crm_api_arr['activename'] = urlencode($activename);
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = strtotime(oracle_date_format($member_info['CREATETIME'], 'Y-m-d'));
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                            $crm_api_arr['tlf_username'] = urlencode($this->uname);
                            $crm_api_arr['projectid'] = $value['PRJ_ID'];
                            //$crm_api_arr['floor_id'] = $pro_listid;
                            $crm_api_arr['pay_time'] = strtotime($lead_time);

                            $crm_url = submit_crm_data_by_api_url($crm_api_arr);
                            $ret_log = api_log($value['CITY_ID'],$crm_url,0,$this->uid,2);
                        }
                    }

                    //״̬���ˣ��쳣
                    if ($value['CARDSTATUS'] > $card_status) {
                        if ($value['CARDSTATUS'] > 2) {
                            switch ($card_status) {
                                case '1':
                                case '2':
                                    $tlfcard_status = 1;
                                    $tlfcard_signtime = 0;
                                    $tlfcard_backtime = 0;
                                    break;
                            }

                            //�ύCRM����
                            $crm_api_arr = array();
                            $crm_api_arr['username'] = urlencode($value['REALNAME']);
                            $crm_api_arr['mobile'] = $value['MOBILENO'];
                            $crm_api_arr['activefrom'] = 104;
                            $crm_api_arr['activename'] = urlencode($activename);
                            $crm_api_arr['city'] = $city_info[$value['CITY_ID']];
                            $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = strtotime($value['CREATETIME']);
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlf_username'] = urlencode($this->uname);
                            $crm_api_arr['projectid'] = $value['PRJ_ID'];
                            $crm_api_arr['pay_time'] = strtotime($lead_time);

                            $crm_url = submit_crm_data_by_api_url($crm_api_arr);
                            $ret_log = api_log($value['CITY_ID'],$crm_url,0,$this->uid,2);
                        }
                    }

                    //ȫ������׼����ϵͳ
                    $qltStatus = 3;

                    switch($card_status)
                    {
                        case '1':
                            $qltStatus = 3;
                            break;
                        case '2':
                            $qltStatus = 4;
                            break;
                        case '3':
                            $qltStatus = 5;
                            break;
                    }

                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$value['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $value['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,$this->uid,3);

                }
            }
            
            if( $result > 0)
            {
                D()->commit();
                $info['state']  = 1;
                $info['msg']  = '�����޸�״̬�ɹ�';
            }
            else
            {
                D()->rollback();
                $info['state']  = 0;
                $info['msg']  = '�����޸�״̬ʧ��!';
            }
        }
        else
        {
            D()->rollback();
            $info['state']  = 0;
            $info['msg']  = '������ѡ��һ����¼!';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
    }

    /**
     * ͬ�������ż�¼��
     */
    private function syncAssocTable($reimDetail, $toUpdateArr) {
        if (empty($reimDetail) || empty($reimDetail['TYPE'])) {
            return;
        }

        // ������ֽ𷢷�����
        if ($reimDetail['TYPE'] && $reimDetail['TYPE'] == self::CASH_PAYMENT_REIM) {
            $localeGrantedModel = D('LocaleGranted');
            $localeGrantedModel->startTrans();
            $updated = $localeGrantedModel->where('ID = ' . $reimDetail['BUSINESS_ID'])->save($toUpdateArr);
            if ($updated !== false) {
                $localeGrantedModel->commit();
            } else {
                $localeGrantedModel->rollback();
            }
        }
    }

    //���̰쿨�ͻ�������������Ա����0����Ա����1
    public function lock_unlock(){
        //�������ݽṹ
        $return = array(
            'state'=>false,
            'msg'=>'����ʧ��',
            'data'=>null,
        );
        $contract = M("Erp_income_contract")->field('ID')->select();
        $type = $_REQUEST['type'];
        $midList = $_REQUEST['memberId'] ? $_REQUEST['memberId']: "0";
        if($type ==  0){
            if(count($midList) > 0){
                foreach($midList as $mid){
                    $data['LOCK_UNLOCK'] = 0;
                    $res = M("Erp_cardmember")->where("ID=".$mid)->save($data);
                }
            }
        }else{
            if(count($midList) > 0){
                foreach($midList as $mid){
                    $data['LOCK_UNLOCK'] = 1;
                    $res = M("Erp_cardmember")->where("ID=".$mid)->save($data);
                }
            }
        }
        if($res > 0){
            $return = array(
                'state'=> True,
                'msg'=>'�����ɹ�',
                'data'=>null,
            );
        }
        die(json_encode(g2u($return)));
    }
 }
 
/* End of file MemberAction.class.php */
/* Location: ./Lib/Action/MemberAction.class.php */