<?php
class MemberAction extends ExtendAction{
    private $is_login_from_oa = false;
    private $city_id = 1;
    private $uid = 0;
    private $uname = '';
    private $user_city_py = 'nj';

    //���캯��
    public function __construct() 
    {
        parent::__construct();
        load("@.member_common");
        $this->is_login_from_oa = ($_SESSION['uinfo']['is_login_from_oa']==true) ?true:false;
        $this->city_id = intval($_SESSION['uinfo']['city']);
        $this->uid = intval($_SESSION['uinfo']['uid']);
        $this->uname = trim($_SESSION['uinfo']['uname']);
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);
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
            $cusname = trim(strip_tags($_POST['cusname']));

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
            $activename = urlencode($project_info['PROJECTNAME'].'��Ȼ����');
            $project_listid = $project_info[0]['REL_NEWHOUSEID'];

            $cpi_arr = array(
                    'city'=>$this->user_city_py,
                    'mobile'=>$telno,
                    'username'=>$cusname,
                    'activefrom'=>231,
                    'activename'=>$activename,
                    'loupanids'=>$project_listid,
                    );

            $url_crm = submit_crm_data_by_api($cpi_arr);

            //CRM����ֵ
            if($url_crm){
                $return['status'] = true;
                $return['msg'] = "���û��Ѿ��ɹ�¼��";
            }
            else
            {
                $return['msg'] = g2u("������¼��������Ƿ���ȷ");
            }

            //TODO ʹ��ͳ����־
            die(json_encode($return));
		}

        //��ĿȨ��
        $projects = $member_model->get_projectinfo_by_uid($this->uid,$this->city_id);
        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        $this->assign('projects',$projects);

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

                               // arrival_confirm_log($customer_id , $truename , $telno , $code , 
                               // $project_listid , $project_id , $is_from , $is_sucess);
                            }

                            //��ȷ���ؽ��
                            $return['status'] = true;
                            $return['msg'] = "ȷ�Ͻ���";

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


        //��ĿȨ��
        $projects = $member_model->get_projectinfo_by_uid($this->uid,$this->city_id);

        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        $this->assign('form_sub_auth_key',$form_sub_auth_key);
        $this->assign('projects',$projects);
        $this->display('Member:arrival_confirm');
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
    	//ʵ������ԱMODEL
    	$member_model = D('Member');
        
        /***��ȡ��Ա�쿨����Ʊ����Ʊ״̬***/
        $status_arr = $member_model->get_conf_all_status_remark();

        //�̻����
        $merchant_arr = array();
        $merchant_info = M('erp_merchant')->where("CITY_ID = '".$this->city_id."'")->select();
        if(is_array($merchant_info) && !empty($merchant_info))
        {
            foreach($merchant_info as $key => $value)
            {
                $large_str = '';
                $value['IS_LARGE'] == 1 ? $large_str .= '[���]' : '';
                $merchant_arr[$value['MERCHANT_NUMBER']] = $value['MERCHANT_NUMBER'].$large_str;
            }
        }

        //������Ա
        if($this->isPost() && !empty($_POST))
        {

            //�������ݽṹ
            $return = array(
              'status'=>false,
              'msg'=>'',
              'data'=>null,
            );

            $member_info = array();
            //��ĿID
            $member_info['PRJ_ID'] = intval($_POST['PRJID']);
            //����
            $pro_city_info = $member_model->get_pro_city_py($member_info['PRJ_ID']);
            $pro_city_py = $pro_city_info[$member_info['PRJ_ID']]['py'];

            $member_info['CITY_ID'] = $pro_city_info[$member_info['PRJ_ID']]['city_id'];

            //¥��ID
            $pro_listid = intval($_POST['LIST_ID']);
            //��Ŀ����
            $member_info['PRJ_NAME'] =  u2g($_POST['PRJ_NAME']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'ds');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            //����ID
            $member_info['CASE_ID'] = $case_id;
            //��Ա����
            $member_info['REALNAME'] =  u2g($_POST['REALNAME']);
            //�ֻ�����
            $member_info['MOBILENO'] = $_POST['MOBILENO'];
            //�������ֻ���
            $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            //��Դ
            $member_info['SOURCE'] = $_POST['SOURCE'];
            //�쿨ʱ��
            $member_info['CARDTIME'] = $_POST['CARDTIME'];
            //֤������
            $member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            //֤������
            $member_info['CERTIFICATE_NO'] = $_POST['IDCARDNO'];
            //¥����
            $member_info['ROOMNO'] =  u2g($_POST['ROOMNO']);
            //�����ܼ�
            $member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            //�������
            $member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            //�쿨״̬
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //���Ϲ�
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SUBSCRIBEDATE'] = date('Y-m-d',$member_info['SUBSCRIBETIME']);
                    break;
                case '3':
                    //��ǩԼ
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = $_POST['SIGNEDSUITE'];
                    break;
            }
            //�վݺ���
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            //�վݱ��
            $member_info['RECEIPTNO'] = trim(str_replace(array("��","/","��"),",", $_POST['RECEIPTNO']));
            //��Ʊ״̬�����뱣��δ��״̬��
            $member_info['INVOICE_STATUS'] = 1;
            //�Ƿ����
            $member_info['IS_TAKE'] = $_POST['ISTAKE'];
            //�Ƿ��Ͷ���
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            //�����շѱ�׼
            $member_info['TOTAL_PRICE'] = intval($_POST['TOTAL_PRICE']);
            //�н�Ӷ��
            $member_info['AGENCY_REWARD'] = intval($_POST['AGENCY_REWARD']);
            //�н�ɽ�����
            $member_info['AGENCY_DEAL_REWARD'] = intval($_POST['AGENCY_DEAL_REWARD']);
            //��ҵ���ʳɽ�����
            $member_info['PROPERTY_DEAL_REWARD'] = intval( $_POST['PROPERTY_DEAL_REWARD']);
            //�ύ��
            $member_info['ADD_UID'] = intval($this->uid);
            //��ע
            $member_info['NOTE'] =  u2g($_POST['NOTE']);
            //����ʱ��
            $member_info['CREATETIME'] = date('Y-m-d h:i:s');


            /**������֤**/
            $returnstr = '';

            if($member_info['REALNAME'] == '')
                $returnstr .= "����д��Ա������\n";

            if($member_info['CERTIFICATE_TYPE']==1 && preg_match("/^(\d{18,18}|\d{15,15}|\d{17,17}x)$/",$member_info['CERTIFICATE_NO']))
                $returnstr .= "����д��ȷ�����֤�ţ�\n";

            if($member_info['CERTIFICATE_TYPE'] != 1 && $member_info['CERTIFICATE_NO']==''){
                $returnstr .= "֤�����벻��Ϊ�գ�\n";
            }

            if(empty($member_info['SOURCE']))
                $returnstr .= "��ѡ���Ա��Դ��\n";

            if($member_info['SOURCE']==1 && (empty($member_info['AGENCY_REWARD']) || empty($member_info['AGENCY_DEAL_REWARD'])))
                $returnstr .= "��Ա��ԴΪ�н�,�н�Ӷ����н�ɽ������\n";

            if(empty($member_info['TOTAL_PRICE']))
                $returnstr .= "��ѡ��д�����շѱ�׼��\n";

            if(empty($member_info['RECEIPTNO']))
                $returnstr .= "��ѡ��д�վݱ�ţ�\n";



            //���ʽ
            switch(count($_POST['PAYTYPE'])){
                case 1:
                    $member_info['PAY_TYPE'] = $_POST['PAYTYPE'][0];
                    break;
                case 0:
                    $member_info['PAY_TYPE'] = 0;
                    break;
                default:
                    $member_info['PAY_TYPE'] = 4;
                    break;
            }


            /**������ϸ**/
            /**�ѽ��ɽ��+δ���ɽ��**/
            $paid_money = 0;
            $unpaid_money = 0;

            if(!empty($_POST['PAYTYPE']) && is_array($_POST['PAYTYPE']) && $_POST['PAYTYPE'][0] !='') {
                foreach ($_POST['PAYTYPE'] as $key=>$val){
                    //�����POS����ʽ
                    if($val==1){
                        if(strlen($_POST['RETRIEVAL'][$key]) != 6){
                            $returnstr .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ��6λ����������\n";
                        }
                        if(empty($_POST['MERCHANTNUMBER'][$key])){
                            $returnstr .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ���̻����δѡ��\n";
                        }
                        if(empty($_POST['TRADEMONEY'][$key])){
                            $returnstr .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ��ԭʼ���׽���Ϊ�գ�\n";
                        }

                        //�ж��Ƿ��Ǵ���(�̻����)
                        if(strpos($merchant_arr[$_POST['MERCHANTNUMBER'][$key]],"���")!==false){
                            if(strlen($_POST['CVV2'][$key])<10){
                                $returnstr .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ���̻����ѡ�����дȫ���ţ�\n";
                            }
                        }
                        else
                        {
                            if(strlen($_POST['CVV2'][$key]) != 4) {
                                $returnstr .= '��' . ($key+1) . '��,' . "���ʽΪPOS���ĸ�����ϸ�����ſ��ź���λ��\n";
                            }
                        }
                    }
                    //������ֽ��������ʽ
                    else if($val==2 || $val==3){
                        if(empty($_POST['TRADEMONEY'][$key])){
                            $returnstr .= '��' . ($key+1) . '��,' . "���ʽΪ�ֽ���������ĸ�����ϸ��ԭʼ���׽���Ϊ�գ�\n";
                        }
                    }

                    //�ѽ��ɽ��
                    $paid_money += $_POST['TRADEMONEY'][$key];
                }
            }

            $member_info['PAID_MONEY'] = $paid_money;
            $member_info['UNPAID_MONEY'] = $member_info['TOTAL_PRICE'] - $paid_money;

            //�����շѱ�׼����ɽ���ȷ��
            if($member_info['UNPAID_MONEY']<0)
                $returnstr .= "�Բ�������д�Ľ��׽��֮�� > �����շѱ�׼��\n";

            //����������֤
            if($returnstr)
            {
                $return['msg'] = g2u($returnstr);
                die(@json_encode($return));
            }

            /**����ʼ**/
            $member_model->startTrans();
            $sign = false;
            //����ֵ
            $insert_member_id = $member_model->add_member_info($member_info);

            if(!$insert_member_id)
                $sign = true;

            //������ϸ
            if($insert_member_id > 0){
                if(!empty($_POST['PAYTYPE']) && is_array($_POST['PAYTYPE']) && $_POST['PAYTYPE'][0] !='') {
                    $member_paymet = M('erp_member_payment');
                    foreach($_POST['PAYTYPE'] as $key=>$val){
                        $pay_info = array();
                        $pay_info['MID'] = $insert_member_id;
                        $pay_info['PAY_TYPE'] = $_POST['PAYTYPE'][$key];
                        $pay_info['TRADE_MONEY'] = $_POST['TRADEMONEY'][$key];
                        //ԭʼ���׽��
                        $pay_info['ORIGINAL_MONEY'] = $_POST['TRADEMONEY'][$key];
                        $pay_info['ADD_UID'] = $this->uid;
                        $pay_info['TRADE_TIME'] = $_POST['TRADETIME'][$key];

                        //POS��
                        if($val==1) {
                            $pay_info['RETRIEVAL'] = $_POST['RETRIEVAL'][$key];
                            $pay_info['CVV2'] = $_POST['CVV2'][$key];
                            $pay_info['MERCHANT_NUMBER'] = $_POST['MERCHANTNUMBER'][$key];
                        }

                        $insert_payment_id = $member_paymet->add($pay_info);
                        if(!$insert_payment_id)
                            $sign = true;

                        //��ӵ���Ŀ�����
                        $income_info['CASE_ID'] = $member_info['CASE_ID'];
                        $income_info['ENTITY_ID'] = $insert_member_id;
                        $income_info['PAY_ID'] = $insert_payment_id;
                        $income_info['INCOME_FROM'] = 1;//���̻�Ա֧��
                        $income_info['INCOME'] = $pay_info['TRADE_MONEY'];
                        $income_info['INCOME_REMARK'] = '�쿨��Ա';
                        $income_info['ADD_UID'] = $this->uid;
                        $income_info['OCCUR_TIME'] = $pay_info['TRADE_TIME'];

                        $income_model = D('ProjectIncome');
                        $ret_bft =  $income_model->add_income_info($income_info);

                        if(!$ret_bft)
                            $sign = true;
                    }
                }
            }

            //�����ύ
            if(!$sign) {
                $member_model->commit();
                $return['status'] = true;
                $return['msg'] = g2u('��ӻ�Ա�ɹ���');
            }
            else {
                $member_model->rollback();
                $return['msg'] = g2u('��ӻ�Աʧ�ܣ�');
            }

            /**���Ͷ��ź�������CRM**/
            if($insert_member_id > 0)
            {
                //���Ͷ���
                if(isset($member_info['IS_SMS']) && $member_info['IS_SMS'] == 2)
                {   
                    $msg = "�𾴵�365��Ա".$member_info['REALNAME']."��".
                           "���ѳɹ�֧����Ϣ�����".$member_info['PAID_MONEY']."Ԫ���ͷ�����400-8181-365";
                    send_sms($msg, $member_info['MOBILENO'], $this->user_city_py);
                }
                
                //������crm
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
                    }
                    $crm_api_arr = array();
                    //�û���
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    //����
                    $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                    //���
                    $crm_api_arr['activefrom'] = 104;
                    //����
                    $crm_api_arr['city'] = $pro_city_py;
                    //��Ϊ
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                            $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].$member_info['CARDTIME']);
                    //��Դ
                    $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = $member_info['CARDTIME'];
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = trim($this->uname);
                    //��ĿID
                    $crm_api_arr['projectid'] = $member_info['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                        $crm_api_arr['floor_id'] = $pro_listid;

                    //�ύ
                    $ret = submit_crm_data_by_api($crm_api_arr);
                }
            }

            /****�Ƿ���Ҫ����ȷ��-����****/
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

                    $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($member_info['PRJ_ID'],
                        $member_info['MOBILENO'], $this->user_city_py);
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
            //��������
            die(json_encode($return));
        }

        //�쿨����
        $today = date("Y-m-d",time());
        //��ĿȨ��
        $projects = $member_model->get_projectinfo_by_uid($this->uid,$this->city_id);
        //֤������
        $certificate = $member_model->get_conf_certificate_type();
        //��Ա��Դ
        $member_source = $member_model->get_conf_member_source_remark();

       /***��ӻ�Աʱ��״̬��ֵ***/
        //�쿨״̬
        $card_status = array(
            '1'=>'�Ѱ�δ�ɽ�',
            '2'=>'�Ѱ����Ϲ�',
            '3'=>'�Ѱ���ǩԼ',
        );
        //�վ�״̬
        $receipt_status = array(
            '2' => "�ѿ�δ��",
            '3' => "����",
        );
        //��Ʊ״̬
        $invoice_status = array(
            '1' => "δ��",
        );

        //cookieֵ
        $selected_project_id = isset($_COOKIE['rt_cookie']['project_id']) ? intval($_COOKIE['rt_cookie']['project_id']) : 0;
        $selected_pro_name = isset($_COOKIE['rt_cookie']['pro_name']) ? iconv('utf8', 'gbk', strip_tags($_COOKIE['rt_cookie']['pro_name'])) : 'ѡ����Ŀ';
        $selected_pro_listid = isset($_COOKIE['rt_cookie']['pro_listid']) ? intval($_COOKIE['rt_cookie']['pro_listid']) : 0;

        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        //�����
        $this->assign('adduid',$this->uid);

        //��Ŀ��Ϣ
        $this->assign('projects',$projects);
        $this->assign('today',$today);
        $this->assign('certificate',$certificate);
        $this->assign('member_source',$member_source);

        $this->assign('card_status',$card_status);
        $this->assign('receipt_status',$receipt_status);
        $this->assign('invoice_status',$invoice_status);
        //�̻����
        $this->assign('merchant_arr',$merchant_arr);
        //cookieֵ��Ĭ����Ŀ��
        $this->assign('selected_project_id',$selected_project_id);
        $this->assign('selected_pro_name',$selected_pro_name);
        $this->assign('selected_pro_listid',$selected_pro_listid);
        $this->display('Member:reg_member');

    }

    /**
     +----------------------------------------------------------
     * ״̬���
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function changeStatus(){

        //��������
        $action_type = isset($_REQUEST['action_type'])?strip_tags($_REQUEST['action_type']):'';

        //keyֵ��֤
        $form_sub_auth_key = md5("HOUSE365_RONGTONG_".date('Ymd').'_'.$this->uname);

        //ʵ������ԱMODEL
        $member_model = D('Member');

        //��ȡ��������
        $city_info = $member_model->get_cityinfo();

        switch ($action_type)
        {
            //�����ͻ��б�
            case 'serach_user_list':

                //�����û�����
                $userinfo = array();

                //ȷ����֤��
                $authcode_key = strip_tags($_POST['authcode_key']);

                //�û���
                $truename = strip_tags($_POST['truename']);

                //�ͻ��ֻ�����
                $telno = strip_tags($_POST['telno']);

                if( $authcode_key == $form_sub_auth_key)
                {
                    //�����û�����
                    if($truename != '' || $telno != '')
                    {
                        $userinfo = $member_model->get_userlist_by_cond($this->city_id , $truename , $telno);
                    }

                    if(!empty($userinfo)){
                        foreach ($userinfo as $key=>$val) {
                            $userinfo[$key]['CITY_NAME'] = $city_info[$userinfo[$key]['CITY_ID']];
                        }
                    }
                }
                else
                {
                    js_alert('��������');
                }
                break;
            //ajax ��ȡ�����б�
            case 'ajax_serach_user_list':

                $return = array(
                    'status'=>false,
                    'msg'=>'',
                    'data'=>null,
                );

                //ȷ����֤��
                $authcode_key = strip_tags($_POST['authcode_key']);

                //�û���
                $truename = strip_tags($_POST['truename']);

                //�ͻ��ֻ�����
                $telno = strip_tags($_POST['telno']);

                //��ҳҳ��
                $page = strip_tags($_POST['next_page']);

                //ÿҳ��ʾ����
                $limit = strip_tags($_POST['perpage_num']);

                $start = ( $page - 1 ) * $limit;

                if( $authcode_key == $form_sub_auth_key)
                {
                    if( ($page - 1) >= 0 )
                    {
                        $userinfo = array('result' => 1,'authcode_key' => $authcode_key);
                        //�����û�����
                        if($truename != '' || $telno != '')
                        {
                            $userinfo_temp = $member_model->get_userlist_by_cond($this->city_id , $truename , $telno , $start , $limit);
                        }


                        if (!empty($userinfo_temp) && is_array($userinfo_temp)){

                            //��������
                            $city_info = $member_model->get_cityinfo();

                            foreach ($userinfo_temp as $key => $value) {
                                $userinfo['user_list'][$key]['id'] = $value['ID'];
                                $userinfo['user_list'][$key]['realname'] = iconv('GBK', 'UTF-8', $value['REALNAME']);
                                $userinfo['user_list'][$key]['mobileno'] = $value['MOBILENO'];
                                $userinfo['user_list'][$key]['projectname'] = g2u($value['PROJECTNAME']);
                                $userinfo['user_list'][$key]['cityname'] = g2u($city_info[$value['CITY_ID']]);
                            }

                            //��������
                            $return['status'] = true;
                            $return['data'] = $userinfo;
                        }
                    }
                }

                die(@json_encode($return));
                break;
            //�鿴�ͻ�����
            case 'get_userinfo':
                $today = date('Y-m-d');
                //�û�ID
                $user_id =  intval($_GET['uid']);
                //key
                $authcode_key = strip_tags($_GET['authcode_key']);

                if( $user_id > 0 && $authcode_key == $form_sub_auth_key)
                {
                    $userinfo = $member_model->get_userinfo_by_uid($user_id);

                    //��Ŀ����
                    $project_name = '';
                    //¥��ID
                    $pro_listid = 0 ;

                    if(!empty($userinfo))
                    {
                        $prjid = intval($userinfo['PRJ_ID']);
                        //��ȡ��Ŀ��Ϣ
                        $project_arr = $member_model->get_project_arr_by_pid($prjid);

                        if(is_array($project_arr) && !empty($project_arr))
                        {
                            $project_name = $project_arr[0]['PROJECTNAME'];
                            $pro_listid = $project_arr[0]['REL_NEWHOUSEID'];
                        }
                    }


                    $card_status = array();
                    $receipt_status = array();
                    $invoice_status = array();

                    $current_card_status = $userinfo['CARDSTATUS'];

                    //�쿨״̬
                    switch(intval($current_card_status)){
                        case 1:
                            $card_status = array(
                                '1' => "�Ѱ�δ�ɽ�",
                                '2' => "�Ѱ����Ϲ�",
                                '3' => "�Ѱ���ǩԼ",
                            );
                            break;
                        case 2:
                            $card_status = array(
                                '2' => "�Ѱ����Ϲ�",
                                '3' => "�Ѱ���ǩԼ",
                            );
                            break;
                        case 3:
                            $card_status = array(
                                '3' => "�Ѱ���ǩԼ",
                            );
                            break;
                        case 4:
                            $card_status = array(
                                '4' => "�˿�",
                            );
                            break;
                    }

                    //��ǰ��Ʊ״̬
                    $current_invoice_status = $userinfo['INVOICE_STATUS'];

                    //��Ʊ״̬
                    switch(intval($current_invoice_status)){
                        case 1:
                            $invoice_status = array(
                                '1' => "δ��",
                                '5' => "������",
                            );
                            break;
                        case 2:
                            $invoice_status = array(
                                '2' => "�ѿ�δ��",
                                '3' => "����",
                                '4' => "���ջ�",
                            );
                            break;
                        case 3:
                            $invoice_status = array(
                                '3' => "����",
                                '4' => "���ջ�",
                            );
                            break;
                        case 4:
                            $invoice_status = array(
                                '4' => "���ջ�",
                            );
                            break;
                        case 5:
                            $invoice_status = array(
                                '1' => "δ��",
                                '5' => "������",
                            );
                            break;
                    }


                    //�վ�״̬
                    $current_receipt_status = $userinfo['RECEIPTSTATUS'];

                    switch(intval($current_receipt_status)){
                        case 2:
                            $receipt_status = array(
                                '2' => "�ѿ�δ��",
                                '3' => "����",
                                '4' => "���ջ�",
                            );
                            break;
                        case 3:
                            $receipt_status = array(
                                '3' => "����",
                                '4' => "���ջ�",
                            );
                            break;
                        case 4:
                            $receipt_status = array(
                                '4' => "���ջ�",
                            );
                            break;
                    }

                }
                else
                {
                    js_alert('��������');
                }
                break;

            //�����û�״̬
            case 'update_user_status':

                $return = array(
                    'status'=>false,
                    'msg'=>'',
                    'data'=>null,
                );

                //�˺ű��
                $user_id =  intval($_POST['uid']);
                //������֤��
                $authcode_key = strip_tags($_POST['authcode_key']);
                //�쿨״̬
                $cardstatus = intval($_POST['cardstatus']);
                //�վ�״̬
                $receiptstatus = intval($_POST['receiptstatus']);
                //��Ʊ״̬
                $invoicestatus = strip_tags($_POST['invoicestatus']);
                //�������ֻ���
                $looker_mobileno = trim(strip_tags($_POST['looker_mobileno']));
                //�Ϲ�ʱ��
                $subscribetime = trim($_POST['subscribetime']);
                //ǩԼʱ��
                $signtime = trim($_POST['signtime']);


                //��ȡ�û���Ϣ
                $memberinfo = $member_model->get_userinfo_by_uid($user_id);

                if($memberinfo) {
                    //����֮ǰ�İ쿨״̬
                    $old_cardstatus = intval($memberinfo['CARDSTATUS']);

                    //�û�����
                    $realname = trim($memberinfo['REALNAME']);

                    //�û�����
                    $mobileno = trim($memberinfo['MOBILENO']);

                    //��Դ
                    $source = intval($memberinfo['SOURCE']);

                    //��Ա��������
                    $card_creattime = strtotime(oracle_date_format($memberinfo['CREATETIME']));

                    //��ĿID
                    $project_id = intval($memberinfo['PRJ_ID']);
                }

                $projectinfo = $member_model->get_project_arr_by_pid($project_id);

                //��Ŀ����
                $pro_city_info = $member_model->get_pro_city_py($project_id);
                $pro_city_py = $pro_city_info[$project_id]['py'];

                if($projectinfo) {
                    //��Ŀ����
                    $project_name = $projectinfo[0]['PROJECTNAME'];
                    //¥��ID
                    $pro_listid = $projectinfo[0]['REL_NEWHOUSEID'];
                }

                $returnstr = "";
                if(!$user_id || empty($memberinfo))
                    $returnstr .= "�û���Ϣ����\n";

                if(!$looker_mobileno && !preg_match("/^1[3-9]\d{9}$/",$looker_mobileno))
                    $returnstr .= "�ֻ������ʽ����ȷ\n";

                if($authcode_key != $form_sub_auth_key)
                    $returnstr .= "������֤����ȷ\n";

                //����쿨״̬�ǡ��Ѱ��Ϲ����Ѱ���ǩԼ��,ʱ����Ҫ�ж�
                if($cardstatus==2 && !$subscribetime)
                    $returnstr .= "�쿨״̬Ϊ�Ϲ����Ϲ�ʱ����\n";

                if($cardstatus==3 && !$signtime)
                    $returnstr .= "�쿨״̬ΪǩԼ��ǩԼʱ����\n";

                //������֤����
                if($returnstr){
                    $return['msg'] = g2u($returnstr);
                    die(@json_encode($return));
                }

                //��������
                $up_arr = array();

                switch($cardstatus)
                {
                    case '2':
                        $up_arr['SUBSCRIBETIME'] = $subscribetime;
                        break;
                    case '3':
                        $up_arr['SIGNTIME'] = $signtime;
                        break;
                }
                $up_arr['CARDSTATUS'] = $cardstatus;
                $up_arr['RECEIPTSTATUS'] = $receiptstatus;
                $up_arr['INVOICE_STATUS'] = $invoicestatus;
                $up_arr['LOOKER_MOBILENO'] = $looker_mobileno;
                $up_arr['UPDATETIME'] = date("Y-m-d h:i:s",time());


                //����ֵ
                $update_member_id = $member_model->update_info_by_id($user_id, $up_arr);

                if(!$update_member_id){
                    $return['msg'] = g2u("����ʧ��!");
                    die(@json_encode($return));
                }
                else
                {
                    $return['status'] = true;
                    $return['msg'] = "״̬���³ɹ�!";
                }

                //״̬��¼
                $operate_type = 2;
                $operate_remark = '�û�״̬���';
                $operate_user = $this->uname;
                $from_device = get_user_agent_device('num');
                //submit_user_operate_log($user_id, $operate_type, $operate_remark, $operate_user, $from_device, $user_city, intval($_POST['prjid']));


                /******�쿨״̬������ãң�����״̬ͬ��******/
                //����쿨״̬���
                if($old_cardstatus != $cardstatus)
                {
                    //��Ϊ
                    $activename = $project_name."�쿨״̬:".$cardstatus."���ڣ�".date("Y-m-d h:i:s",time());

                    if($old_cardstatus < $cardstatus)
                    {
                        if($cardstatus == 3)
                        {
                            //CRM֪ͨ��Ϣ
                            $tlfcard_status = 2;
                            $tlfcard_signtime = time();
                            $tlfcard_backtime = 0;

                            //�ύCRM����
                            $crm_api_arr = array();
                            $crm_api_arr['username'] = urlencode($realname);
                            $crm_api_arr['mobile'] = $mobileno;
                            $crm_api_arr['activefrom'] = 104;
                            $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                            $crm_api_arr['city'] = $pro_city_py;
                            $crm_api_arr['activename'] = urlencode($activename);
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = $card_creattime;
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                            $crm_api_arr['tlf_username'] = urlencode($this->uname);
                            $crm_api_arr['projectid'] = $project_id;
                            $crm_api_arr['floor_id'] = $pro_listid;

                            submit_crm_data_by_api($crm_api_arr);
                        }
                    }

                    //״̬���ˣ��쳣
                    if($old_cardstatus > $cardstatus)
                    {
                        if($old_cardstatus > 2)
                        {
                            switch($cardstatus){
                                case '1':
                                case '2':
                                    $tlfcard_status = 1;
                                    $tlfcard_signtime = 0;
                                    $tlfcard_backtime = 0;
                                    break;
                            }

                            //�ύCRM����
                            $crm_api_arr = array();
                            $crm_api_arr['username'] = urlencode($realname);
                            $crm_api_arr['mobile'] = $mobileno;
                            $crm_api_arr['activefrom'] = 104;
                            $crm_api_arr['activename'] = urlencode($activename);
                            $crm_api_arr['city'] = $pro_city_py;
                            $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = $card_creattime;
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlf_username'] = urlencode($this->uname);
                            $crm_api_arr['projectid'] = $project_id;

                            submit_crm_data_by_api($crm_api_arr);
                        }
                    }

                }
                /******�쿨״̬������ãң�����״̬ͬ��******/
                die(@json_encode($return));
                break;
        }
        
        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        $this->assign('form_sub_auth_key',$form_sub_auth_key);
        
        //ʱ��
        $this->assign('today',$today);
        
        //�������û���Ϣ
        $this->assign('truename',$truename);
        $this->assign('telno',$telno);
        
        //���ݻ�ȡ�û���Ϣ
        $this->assign('userinfo',$userinfo);
        
        //��Ŀ��Ϣ
        $this->assign('project_name',$project_name);
        $this->assign('pro_listid',$pro_listid);
        
        //�쿨��Ϣ
        $this->assign('card_status',$card_status);
        $this->assign('receipt_status',$receipt_status);
        $this->assign('invoice_status',$invoice_status);
        
        //�û���ǰ�쿨��Ϣ
        $this->assign('current_card_status',$current_card_status);
        $this->assign('current_invoice_status',$current_invoice_status);
        $this->assign('current_receipt_status',$current_receipt_status);

        //��������
        $this->assign('action_type',$action_type);

        $this->display('Member:status_change');
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
            //��ĿID
            $project_id = intval($_POST['project_id']);
            //�绰����
            $telno = strip_tags($_POST['telno']);
            //¥��ID
            $pro_listid = isset($_POST['pro_listid']) ? intval($_POST['pro_listid']) : 0;

            $userinfo = get_userinfo_by_pid_telno($project_id, $telno, $pro_listid, $this->user_city_py);

            die(@json_encode($userinfo));
        }
    }

 }