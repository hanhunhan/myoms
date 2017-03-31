<?php
class FinancialAction extends ExtendAction{
    /**
     * ���뿪ƱȨ��
     */
    const IMPORTINVOICE = 175;

    /**
     * ������ƱȨ��
     */
    const EXPORTINVOICE = 176;

    /**
     * ȷ�ϱ���Ȩ��
     */
    const REIM_CONFIRM = 733;

    /**
     * ���Ȩ��
     */
    const REIM_REFUSE = 332;

    /**
     * �Ǹ��ֳɱ�ȷ��Ȩ��
     */
    const NONCASHCOST = 408;

    private $model;
    private $_merge_url_param = array();
    //���캯��
    public function __construct()
    {
        $this->model = new Model();
        parent::__construct();
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'importInvoice' => self::IMPORTINVOICE,
            'exportInvoice' => self::EXPORTINVOICE,
            'reim_confirm' => self::REIM_CONFIRM,
            'reim_refuse' => self::REIM_REFUSE,
            'noncashcost' => self::NONCASHCOST
        );

        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['parentchooseid']) ? $this->_merge_url_param['contract_id'] = $_GET['parentchooseid'] : $_GET['contract_id'];
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;

        /**�û������Ϣ**/
        //�û�ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //����
        $this->city = intval($_SESSION['uinfo']['city']);
        //����ƴ��
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);
    }

    /**
    *�տ�ȷ��
    *��Ա���в���ȷ��״̬ ��1δȷ��   2����ȷ��   3��ȷ��
    * ������в���ȷ��״̬ ��1δȷ��  2��ȷ��
    */
    public function financialConfirm()
    {
        $city_channel = $this->channelid;
        if($_POST["confirmMethod"])
        {
            //����ȷ���տ�� �޸İ쿨��Ա���в���ȷ��״̬
            $cardmember = D("Erp_cardmember");
            $payment = D("Erp_member_payment");

            $payment_model = D("MemberPay");
            $cardmember_model = D("Member");
            $financial_status = $cardmember_model->get_conf_confirm_status();
            $payment_status = $payment_model->get_conf_status();

            $res = 0;
            //֧��ͬʱѡ�������¼  �������в���ȷ��
            $memberId = $_REQUEST["memberId"];
            $confirmMethod = $_REQUEST["confirmMethod"];// 1��ʾͨ����ԱID����ȷ��  2��ʾͨ��������ϸID����ȷ��
            $paymentId = $_REQUEST["paymentId"];//������ϸ��¼��ID

            //���������û���ȷ��
            if($confirmMethod == 1)
            {
                if(isset($memberId) && count($memberId) >= 1)
                {
                    foreach($memberId as $val)
                    {
                       $where = "MID = ".$val;
                       $no_payment_record = $payment->where($where)->find();
                       if(!$no_payment_record)
                       {
                           echo 0;
                           userLog()->writeLog($val, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:��������ؼ�¼��0', serialize($_POST));
                           exit();
                       }
                    }
                    $memberIdstr = implode(",",$memberId);
                    $where = "ID in (".$memberIdstr.") and FINANCIALCONFIRM=3";
                    $r = $cardmember->where($where)->select();
                    if($r)//$r Ϊ�� ��ʾ���Ѿ���ȷ�Ϲ���
                    {
                        echo 0;
                        userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:��ȷ�ϣ�0', serialize($_POST));
                        exit();
                    }
                    else
                    {
                        //��ѡ��¼ȫ��Ϊδȷ�ϻ򲿷�ȷ�ϵ� ��������ȷ�� STATUS=1��ʾ����δȷ��  STATUS=2��ʾ���񲿷�ȷ�� 3��ȷ��
                        $sql = "select A.ID from ERP_MEMBER_PAYMENT A where A.MID in(".$memberIdstr.") and STATUS = ".$payment_status["wait_confirm"];
                        //echo $sql;die;
                        //�ҳ�������ϸ���ж�Ӧ��Ա������δȷ�ϵļ�¼���в���ȷ��
                        $listId = $this->model->query($sql);//$listId Ϊ��λ��������
                        if($listId){
                                $listIdstr = implode(",",array2new($listId));
                                $where = "ID in(".$listIdstr.")";
                                $this->model->startTrans();
                                $res = $payment->where($where)->setField("STATUS",$payment_status["confirmed"]);//ִ�гɹ� ���ر�Ӱ�������
                                $some_confirm_member = "";//����ȷ�ϻ�Ա
                                $all_confirm_member = "";//ȫ��ȷ�ϻ�Ա
                                foreach ($memberId as $key=>$val)
                                {
                                    $member_info = $cardmember->where("ID=".$val)->field(array("UNPAID_MONEY"))->find();
                                    $unpaid_money = $member_info["UNPAID_MONEY"];
                                    if($unpaid_money > 0)
                                    {
                                        $some_confirm_member .= $val.",";
                                    }
                                    else
                                    {
                                        //$res1 = $cardmember->where($where1)->setField("FINANCIALCONFIRM",$financial_status["confirmed"]);
                                        $all_confirm_member .= $val.",";
                                        //echo $all_confirm_member;
                                    }
                                }

                                //�޸Ĵ���δ���ɽ��Ļ�Ա����ȷ��״̬Ϊ����ȷ��
                                if($some_confirm_member)
                                {
                                    $some_confirm_member = rtrim($some_confirm_member,",");
                                    $where1 = "ID IN(".$some_confirm_member.")";
                                    $some_up_num = $cardmember->where($where1)->setField("FINANCIALCONFIRM",$financial_status["part_confirmed"]);
                                    //echo $this->model->_sql();
                                    if(!$some_up_num)
                                    {
                                        $this->model->rollback();
                                        userLog()->writeLog($some_confirm_member, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:����ȷ��:ʧ��:4', serialize($_POST));
                                        echo 4;exit;
                                    }
                                }

                                //�޸Ĳ�����δ���ɽ��Ĳ���ȷ��״̬Ϊ��ȷ��
                                if($all_confirm_member)
                                {

                                    $all_confirm_member = rtrim($all_confirm_member,",");
                                    $where1 = "ID IN(".$all_confirm_member.")";
                                    $all_up_num = $cardmember->where($where1)->setField("FINANCIALCONFIRM",$financial_status["confirmed"]);

                                   // echo $this->model->_sql();
                                    if(!$all_up_num)
                                    {
                                        $this->model->rollback();
                                        userLog()->writeLog($all_up_num, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:ȫ��ȷ��:ʧ��:4', serialize($_POST));
                                        echo 4;exit;
                                    }
                                }
                                //die;
                                //����ȷ��������������������������ϸ(����ȷ�ϣ���Ȼ��ô����)
                                $res2 = $this->add_income_after_financial_confirm(2,0,array(),array2new($listId));
                                if(!$res2){
                                    $this->model->rollback();
                                    userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:����������:ʧ��:4', serialize($_POST));
                                    echo 4;exit;
                                }

                                if($res){
                                    $this->model->commit();
                                    userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��::�ɹ�:3', serialize($_POST));
                                    echo 3;exit();
                                }else{
                                    $this->model->rollback();
                                    userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��::ʧ��:4', serialize($_POST));
                                    echo 4;exit();
                                }
                        }else{
                            echo 5;
                            userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��::ʧ��:5', serialize($_POST));
                            exit();
                        }
                    }
                }
                elseif(count($memberId) < 1)//δѡ���κ�һ����¼
                {
                    echo 1;
                    userLog()->writeLog('', $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��::ʧ��:1', serialize($_POST));
                    exit();
                }
            }
            elseif($confirmMethod == 2)//ͨ��������ϸ��ID����ȷ��
            {
                if(isset($paymentId) && count($paymentId) >= 1)
                {
                    $paymentIdstr = implode(",",$paymentId);
                    $where = "ID in(".$paymentIdstr.") and STATUS = ".$payment_status["confirmed"];
                    $r = $payment->where($where)->select();
                    if($r)
                    {
                        echo 0;
                        userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��::ʧ��:0', serialize($_POST));
                        exit();
                    }
                    else
                    {
                        $where = "ID in(".$paymentIdstr.")";
                        $this->model->startTrans();
                        $res = D("Erp_member_payment")->where($where)->setField("STATUS",$payment_status["confirmed"]);

                        //����ȷ��������������������������ϸ
                        $res2 = $this->add_income_after_financial_confirm($confirmMethod,0,array(),$paymentId);
                        if(!$res2)
                        {
                            userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:����������:ʧ��:4', serialize($_POST));
                            $this->model->rollback();
                            echo 4;exit;
                        }
                        if($res)//���ִ�гɹ� �޸Ļ�Ա����״̬
                        {
                            $where = "ID in(".$paymentIdstr.")";
                            $mid = $payment->where($where)->field("MID")->find();
                            $mid = $mid['MID'];
                            $where1 = "MID = $mid and STATUS = ".$payment_status["wait_confirm"];

                            $someConf = $payment->where($where1)->field("ID")->find();
                            //echo $this->model->_sql();
                            //var_dump($someConf);
                            $member_info = $cardmember->where("ID=".$mid)->field(array("UNPAID_MONEY"))->find();
                            $unpaid_money = intval($member_info["UNPAID_MONEY"]);

                            if(!empty($someConf) || $unpaid_money > 0)//�û�Ա״̬��Ϊ����ȷ��2
                            {
                                $sql = "update ERP_CARDMEMBER set FINANCIALCONFIRM=".$financial_status["part_confirmed"]." where ID = ".$mid;
                                $res1 = $this->model->execute($sql);
                                //var_dump($res1);die;
                                 if($res1){
                                     $this->model->commit();
                                     userLog()->writeLog($mid, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:����ȷ��:�ɹ�:3', serialize($_POST));
                                     echo 3;exit();
                                 }else{
                                     $this->model->rollback();
                                     userLog()->writeLog($mid, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:����ȷ��:ʧ��:4', serialize($_POST));
                                     echo 4;exit();
                                }
                            }
                            else
                            {
                                 $sql = "update ERP_CARDMEMBER set FINANCIALCONFIRM=".$financial_status["confirmed"]." where ID = ".$mid;
                                 $res2 = $this->model->execute($sql);
                                 if($res2){
                                     $this->model->commit();
                                     userLog()->writeLog($mid, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:ȫ��ȷ��:�ɹ�:3', serialize($_POST));
                                     echo 3;exit();
                                 }else{
                                      $this->model->rollback();
                                     userLog()->writeLog($mid, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:ȫ��ȷ��:ʧ��:4', serialize($_POST));
                                      echo 4;exit();
                                 }
                             }
                         }
                        else
                        {
                             $this->model->rollback();
                            userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��::ʧ��:4', serialize($_POST));
                             echo 4;exit();
                         }
                    }
                }
                else
                {
                    echo 1;
                    exit();
                }
            }
            else
            {
                echo 2;
                exit();
            }
        }
        else
        {
            Vendor('Oms.Form');
            $form = new Form();
            $arr_param = array(
                    array('2','CARDSTATUS') ,
                    array('3','RECEIPTSTATUS'),
                    array('4','INVOICE_STATUS'),
                    array('5','FINANCIALCONFIRM')
                );
            $children = array(array('������ϸ',U('/Financial/payment')));
            $where = "FINANCIALCONFIRM IN(1,2)";
            $form->initForminfo(117);
            if($_POST["search1_t"] || $_POST["search2_t"] || $_POST["search3_t"] || $_POST["search4_t"])
            {
                $sqltext = "(SELECT DISTINCT A.*  FROM ERP_CARDMEMBER A LEFT JOIN ERP_MEMBER_PAYMENT B ON B.MID=A.ID)";
                $form->SQLTEXT = $sqltext;
            }
            $form->where("CITY_ID = ".$city_channel." AND FINANCIALCONFIRM IN(1,2) AND PAID_MONEY != 0 AND STATUS=1");
            //���ø��ʽ
            $member_pay = D('MemberPay');
            $pay_arr = $member_pay->get_conf_pay_type();
            $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);
//            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
            $formHtml = $form->setChildren($children)->showStatusTable($arr_param)->getResult();
            $this->assign('form',$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
            $this->assign('paramUrl',$this->_merge_url_param);
            $this->display('financial_confirm');
        }
    }


    /**
     *  ����ȡ��ȷ��
    * ȡ��ȷ��
    */
    public function cancleConfirm()
    {
        //���ؽ����
        $return  =  array(
            'status'=>false,
            'msg'=>'',
            'data'=>null,
        );

        if($_POST)
        {
            //model
            $payment_model = D("MemberPay");
            $cardmember_model = D("Member");
            $cardmember = D("Erp_cardmember");
            $payment = D("Erp_member_payment");

            //����״̬������״̬
            $financial_status = $cardmember_model->get_conf_confirm_status();
            $payment_status = $payment_model->get_conf_status();

            $memberId = $_REQUEST["memberId"];
            $paymentId = $_REQUEST["paymentId"];
            $cancleMethod = $_REQUEST["cancleMethod"];

            //ͨ����ԱID����ȡ��ȷ��
            if($cancleMethod == 1)
            {
                if(isset($memberId) && count($memberId) >= 1)
                {
                    foreach($memberId as $val)
                    {
                       $where = "MID = ".$val;
                       $no_payment_record = $payment->where($where)->find();
                       if(!$no_payment_record)
                       {
                           echo 5;
                           userLog()->writeLog($val, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:��ѯerp_member_payment:ʧ��:5', serialize($_POST));
                           exit();
                       }
                    }
                    $memberIdstr = implode(",",$memberId);
                    $where = "ID in(".$memberIdstr.") and FINANCIALCONFIRM = 1";
                    $r = $cardmember->where($where)->select();
                    if($r)
                    {
                        echo 0;//��ʾ��δȷ��״̬ ���ܽ���ȡ������
                        userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:��ѯErp_cardmember:ʧ��:0', serialize($_POST));
                        exit();
                    }
                    else
                    {
                        $sql = "select ID from ERP_MEMBER_PAYMENT where MID in(".$memberIdstr.") and status = 1";
                        $listId = $this->model->query($sql);
                        if(!$listId)
                        {
                            echo 5;//��ʾ��ѡ�����м�¼��û���κ�һ�����б�ȷ�ϵ��տ�
                            userLog()->writeLog($val, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:��ѯerp_member_payment:ʧ��:5', serialize($_POST));
                            exit();
                        }
                        else
                        {
                            $listIdstr ="";
                            $listIdstr = implode(",",array2new($listId));
                            $where = "ID in(".$listIdstr.") AND STATUS != 4";
                            $this->model->startTrans();
                            $res = $payment->where($where)->setField("STATUS",$payment_status["wait_confirm"]);//�޸���ϸ����״̬Ϊδȷ�ϣ�0��
                            //ͬʱ�޸Ļ�Ա����״̬Ϊδȷ��
                            $res1 = $cardmember->where("ID in(".$memberIdstr.")")->setField("FINANCIALCONFIRM",$financial_status["no_confirm"]);

                            //����ȡ��ȷ�Ϻ�������������Ӹ�������ϸ
                            $res2 = $this->add_income_after_financial_confirm(2,1,array(),array2new($listId));
                            if(!$res2){
                                $this->model->rollback();
                                userLog()->writeLog($listIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:������������Ӹ�������ϸ:ʧ��:4', serialize($_POST));
                                echo 4;exit;
                            }

                            if($res && $res1){
                                $this->model->commit();
                                userLog()->writeLog($listIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��::�ɹ�:3', serialize($_POST));
                                echo 3;exit();
                            }else{
                                $this->model->rollback();
                                userLog()->writeLog($listIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��::ʧ��:4', serialize($_POST));
                                echo 4;exit();
                            }
                        }
                    }

                }
                else
                {
                    echo 1;//��ʾ��ѡ���¼
                    exit();
                }
            }
            elseif($cancleMethod == 2)//ͨ���տ���ϸID����ȡ��
            {
                if(isset($paymentId) && count($paymentId) >= 1)
                {
                    $paymentIdstr = implode(",",$paymentId);
                    $where = "ID in(".$paymentIdstr.") and STATUS = ".$payment_status["confirmed"];
                    $r = $payment->where($where)->field("ID")->find();
                    //echo $payment->_sql();die;
                    if(empty($r)){
                        userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:��ѯErp_member_payment:ʧ��:6', serialize($_POST));
                       echo 6;
                       exit();
                    }else{

                        //������ڵȴ�ȷ��״̬����
                        $where = "ID in(".$paymentIdstr.") and STATUS = ".$payment_status["wait_confirm"];
                        $r = $payment->where($where)->field("ID")->find();
                        if($r){
                            echo 0;
                            userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:��ѯErp_member_payment:ʧ��:0', serialize($_POST));
                            exit();
                        }

                        $where = "ID in(".$paymentIdstr.")";
                        $this->model->startTrans();
                        $res = $payment->where($where)->setField("STATUS",$payment_status["wait_confirm"]);  //�޸�ѡ���տ��״̬
                        //����ȡ��ȷ�Ϻ�������������Ӹ�������ϸ
                        if($res)
                        {
                            $res3 = $this->add_income_after_financial_confirm($cancleMethod,1,array(),$paymentId);
                        }
                        if(!$res3){
                            $this->model->rollback();
                            userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:������������Ӹ�������ϸ:ʧ��:4', serialize($_POST));
                            echo 4;exit;
                        }

                         $mid = $payment->where($where)->field("MID")->find(); //���ݱ��޸ĵļ�¼ �ҳ����޸ļ�¼�а���������MID
                         $mid = $mid["MID"];
                         $where = "MID = $mid and STATUS = ".$payment_status["confirmed"];
                         $someConf = $payment->where($where)->field("MID")->find();//����ҵ� ��Щ��Ա��״̬��Ϊ����ȷ�ϣ�ʣ�µĻ�ԱΪδȷ��

                         if(!empty($someConf)){
                             $someConf = $someConf["MID"];
                             $res1 = $cardmember->where("ID = ".$someConf)->setField("FINANCIALCONFIRM",$financial_status["part_confirmed"]);
                             if($res1){
                                 $this->model->commit();
                                 userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:����ȡ��:�ɹ�:3', serialize($_POST));
                                 echo 3;exit();
                             }else{
                                 $this->model->rollback();
                                 userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], 'ȡ��ȷ��:����ȡ��:ʧ��:4', serialize($_POST));
                                 echo 4;exit();
                             }

                         }else{
                             $res2 = $cardmember->where("ID = ".$mid)->setField("FINANCIALCONFIRM",$financial_status["no_confirm"]);
                             if($res2){
                                 $this->model->commit();
                                 echo 3;exit();
                             }else{
                                 $this->model->rollback();
                                 echo 4;exit();
                             }
                         }
                    }
                }
                else
                {
                    echo 1;
                    exit();
                }
            }
            else
            {
                echo 2;
                exit();
            }
         }
    }

    /**
     * ��Ա��Ʊ�б�ҳ
     * @param none
     * return
     */
    public function invoice()
    {
        $city_channel = $this->channelid;
        Vendor('Oms.Form');
        $form = new Form();
        $arr_param = array(
                array('2','CARDSTATUS') ,
                array('3','RECEIPTSTATUS'),
                array('4','INVOICE_STATUS'),
                array('5','FINANCIALCONFIRM')
            );
        $GABTN = '<a id="importInvoice" href="javascript:;" class="btn btn-info btn-sm">���뿪Ʊ</a>'
             . '<a id="exportInvoice" href="javascript:;" class="btn btn-info btn-sm">������Ʊ</a>';
        $form->initForminfo(117)->where("CITY_ID = ".$city_channel);
        $form->GABTN = $GABTN;
        //���ø��ʽ
        $member_pay = D('MemberPay');
        $pay_arr = $member_pay->get_conf_pay_type();
        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
        $formhtml = $form
            ->where("(INVOICE_STATUS = 5 OR CHANGE_INVOICE_STATUS = 2)")
            ->showStatusTable($arr_param)
            ->getResult();
        $this->assign('form',$formhtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        //�����������
        $this->assign('filter_sql',$form->getFilterSql());
        $this->display('financial_invoice');
    }


    /**
    +----------------------------------------------------------
    * ����ҳ�浼�뿪Ʊ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function importInvoice()
    {
        //��ʼ��
        $member_model = D("Member");
        $billing_mode =D("BillingRecord");

        //���ȡ��
        if($_POST["cancle"])
        {
            $this->redirect("Financial/invoice");
        }
        //����ϴ�
        if( $_FILES )
        {
            //����
            if($_FILES["upfile"]["size"] > 5000000) {
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '��Ʊ:���뿪Ʊ:�ļ���С���ܳ��� 5M !:ʧ��', serialize($_FILES['upfile']));
                die("�ļ�����ע�⣺�ļ���С���ܳ��� 5M !");
            }


            //��ȡ�ļ���׺��
            $file_name = $_FILES["upfile"]["name"];
            $file_name_arr = explode(".",$file_name);
            $file_ext = $file_name_arr[count($file_name_arr)-1];

            if($file_ext != "xls" && $file_ext != "xlsx") {
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '��Ʊ:���뿪Ʊ:�ļ���С���ܳ��� 5M !:ʧ��', serialize($_FILES['upfile']));
                die("�ϴ��ļ�����excel����������ϴ���");
            }


            //��ȡ�ļ�
            $file = $_FILES["upfile"]["tmp_name"];

            //��ȡ��Ʊ�Ŀ�Ʊ״̬
            $member_invoice_status = $member_model->get_conf_invoice_status();

            Vendor('phpExcel.PHPExcel');
            Vendor('phpExcel.IOFactory.php');
            Vendor('phpExcel.Reader.Excel5.php');

            $PHPExcel = new PHPExcel();
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if(!$PHPReader->canRead($file)){
                $PHPReader = new PHPExcel_Reader_Excel5();
                if(!$PHPReader->canRead($file)){
                    echo 'no Excel !';
                    userLog()->writeLog('', $_SERVER["REQUEST_URI"], '��Ʊ:���뿪Ʊ:��excel�ļ�:ʧ��', serialize($_FILES['upfile']));
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

            $data = array();
            //ѭ��EXCEL
            for($currentRow = 3; $currentRow <= $allRow; $currentRow++){
                //���ݺ�
                $receiptno = $currentSheet->getCellByColumnAndRow((ord(A) - 65),$currentRow)->getValue();
                $receiptno = str_replace(","," ",$receiptno);

                //��Ʊ���
                $invoiceno = $currentSheet->getCellByColumnAndRow((ord(R) - 65),$currentRow)->getValue();
                $invoiceno = u2g($invoiceno);

                //���
                $money = $currentSheet->getCellByColumnAndRow((ord(L) - 65),$currentRow)->getValue();

                //��Ʊ��ע
                $remark = $currentSheet->getCellByColumnAndRow((ord(F) - 65),$currentRow)->getValue();
                $remark = u2g($remark);

                //��Ʊ˰��
                $taxrate = get_taxrate_by_citypy($this->user_city_py);

                //��Ʊ˰��
                $tax = ($currentSheet->getCellByColumnAndRow((ord(N)-65),$currentRow)->getValue());

                //��ֵ
                $data[] =array("receiptno"=>$receiptno,"invoiceno"=>$invoiceno,"money"=>$money,"remark"=>$remark,"taxrate"=>$taxrate,'tax'=>$tax);
            }

            //����
            $i = 0;
            //���صĽ������
            $return_error = "";

            foreach($data as $key=>$val)
            {
                //��������ڷ�Ʊ���
                if(empty($val["invoiceno"])) {
                    $return_error .= "��" . ($key + 1) . "������Ʊ���û����д.<br />";
                    continue;
                }

                $cond_where = "RECEIPTNO='".$val['receiptno']."' AND CITY_ID = ".$_SESSION["uinfo"]["city"]." AND "
                    . "(INVOICE_STATUS = 5 OR CHANGE_INVOICE_STATUS=2)";
                //��ȡ��Ա��Ϣ
                $member_info = $member_model->get_info_by_cond($cond_where,array("ID","CASE_ID","CHANGE_INVOICE_STATUS","INVOICE_NO","PRJ_ID","PRJ_NAME"));

                if(empty($member_info) || !$member_info[0]["ID"])
                {
                    $return_error .= "��" . ($key + 1) . "����״̬������Ҫ��.<br />";
                    continue;
                }

                //��Ʊ��Ա�Ŀ�Ʊ����巢Ʊ��
                if($member_info[0]["CHANGE_INVOICE_STATUS"] == 2)
                {
                    $id = $member_info[0]["ID"];
                    $caseid = $member_info[0]["CASE_ID"];
                    $prj_id = $member_info[0]["PRJ_ID"];

                    //��ȡ��ͬ���
                    $contract_num = M("erp_project")
                        ->field("CONTRACT")
                        ->where('ID = ' . $prj_id)
                        ->find();
                    $contract_num = $contract_num['CONTRACT'];

                    $update_arr["INVOICE_NO"] = $val["invoiceno"];
                    $update_arr["CONFIRMTIME"] = date("Y-m-d H:i:s",time());
                    //��Ա��Ʊ״̬Ϊ�ɹ�״̬
                    $update_arr["CHANGE_INVOICE_STATUS"] = 3;

                    $this->model->startTrans();

                    $up_num = $member_model->update_info_by_id($id,$update_arr);


                    if(!$up_num) {
                        $return_error .= "��" . ($key + 1) . "������Ʊʧ��1.<br />";
                        $this->model->rollback();
                        continue;
                    }


                    $remark = $val["remark"] ? $val["remark"] : "��";
                    //����Ʊ��¼������Ӻ�忪Ʊ����
                    $insert_arr["CASE_ID"] = $caseid;
                    $insert_arr["CONTRACT_ID"] = $id;
                    $insert_arr["INVOICE_NO"] = $member_info[0]["INVOICE_NO"];
                    $insert_arr["INVOICE_MONEY"] = 0-$val['money'];
                    $insert_arr["TAX"] = round((0-$val['money'])/(1+$val["taxrate"]) * $val["taxrate"],2);
                    $insert_arr["USER_ID"] = $_SESSION['uinfo']['uid'];
                    $insert_arr["CREATETIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["INVOICE_TIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["REMARK"] = "��巢Ʊ";
                    $insert_arr["INVOICE_TYPE"] = 2;

                    $insert_billing_id_e = $billing_mode->add_billing_info($insert_arr);

                    if(!$insert_billing_id_e){
                        $return_error .= "��" . ($key + 1) . "������Ʊʧ��2.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    //�����������Ӻ������
                    $this->add_income_after_financial_invoice(array($id),1,$insert_billing_id_e);

                    //ͬ����ͬϵͳ��Ҫ������(����Ʊ������)
                    $invoiceno_arr[$contract_num][] = array(
                        'invoice_no' => $member_info[0]["INVOICE_NO"],
                        'invoice_money' => -$val['money'],
                        'invoice_tax' => $insert_arr["TAX"],
                        'invoice_note' => "��巢Ʊ",
                    );

                    //����Ʊ��¼��������µĿ�Ʊ����
                    $insert_arr["CASE_ID"] = $caseid;
                    $insert_arr["CONTRACT_ID"] = $id;
                    $insert_arr["INVOICE_NO"] = $val['invoiceno'];
                    $insert_arr["INVOICE_MONEY"] = $val['money'];
                    $insert_arr["TAX"] = round($val['money']/(1+$val["taxrate"]) * $val["taxrate"],2);
                    $insert_arr["USER_ID"] = $_SESSION['uinfo']['uid'];
                    $insert_arr["CREATETIME"] =  date("Y-m-d H:i:s",time());
                    $insert_arr["INVOICE_TIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["REMARK"] = $remark;
                    $insert_arr["INVOICE_TYPE"] = 2;
                    $insert_billing_id = $billing_mode->add_billing_info($insert_arr);

                    if(!$insert_billing_id){
                        $return_error .= "��" . ($key + 1) . "������Ʊʧ��3.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    $res_add = $this->add_income_after_financial_invoice(array($id),1,$insert_billing_id);

                    if(!$res_add){
                        $return_error .= "��" . ($key + 1) . "������Ʊʧ��4.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    //ͬ����ͬϵͳ��Ҫ������(����Ʊ������)
                    $invoiceno_arr[$contract_num][] = array(
                        'invoice_no' => $val['invoiceno'],
                        'invoice_money' => $val['money'],
                        'invoice_tax' => $insert_arr["TAX"],
                        'invoice_note' => $remark,
                    );

                    //�ύ
                    $this->model->commit();
                    $i++;

                }
                //������Ա�Ŀ�Ʊ
                else if($member_info[0]["CHANGE_INVOICE_STATUS"] == 1)
                {
                    $id = $member_info[0]["ID"];
                    $caseid = $member_info[0]["CASE_ID"];
                    $prj_id = $member_info[0]["PRJ_ID"];

                    $update_arr["INVOICE_STATUS"] = $member_invoice_status["invoiced"];
                    $update_arr["INVOICE_NO"] = $val["invoiceno"];
                    $update_arr["CONFIRMTIME"] = date("Y-m-d H:i:s",time());
                    $update_arr["CONFIRM_UID"] = $_SESSION["uinfo"]["uid"];

                    $this->model->startTrans();
                    $up_num = $member_model->update_info_by_id($id,$update_arr);

                    if(!$up_num) {
                        $return_error .= "��" . ($key + 1) . "������Ʊʧ��1.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    $remark = $val["remark"] ? $val["remark"] : "��";
                    $insert_arr["CASE_ID"] = $caseid;
                    $insert_arr["CONTRACT_ID"] = $id;
                    $insert_arr["INVOICE_NO"] = $val['invoiceno'];
                    $insert_arr["INVOICE_MONEY"] = $val['money'];
                    $insert_arr["TAX"] = round($val['money']/(1+$val["taxrate"]) * $val["taxrate"],2);
                    $insert_arr["USER_ID"] = $_SESSION['uinfo']['uid'];
                    $insert_arr["CREATETIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["INVOICE_TIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["REMARK"] = $remark;
                    $insert_arr["INVOICE_TYPE"] = 2;

                    $insert_billing_id = $billing_mode->add_billing_info($insert_arr);

                    if(!$insert_billing_id){
                        $return_error .= "��" . ($key + 1) . "������Ʊʧ��2.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    $ret_add = $this->add_income_after_financial_invoice(array($id),1,$insert_billing_id);

                    if(!$ret_add){
                        $return_error .= "��" . ($key + 1) . "������Ʊʧ��3.<br />";
                        $this->model->rollback();
                        continue;
                    }


                    $project_cost_model = D("ProjectCost");
					//$house_model = D("House");
					//$house_data = $house_model->get_house_info_by_prjid($prj_id,array('ISFUNDPOOL' ));
					//$ispool_arr = array('1'=>0,'0'=>1,'-1'=>1);//�Ƿ��ʽ�ض�Ӧ��ϵ����
                    $paymentlist = M('Erp_member_payment')->where("STATUS=1 and MID=$id and PAY_TYPE=1")->select();
					if($paymentlist){
						$cost_insert_id = 1;
						foreach($paymentlist as $pone){
							if($pone['PAY_TYPE']==1){
								//������� �����
								$cost_info['CASE_ID'] = $caseid;
								//ҵ��ʵ���� �����
								$cost_info['ENTITY_ID'] =  $id;
								$cost_info['EXPEND_ID'] = $insert_billing_id;
								$cost_info['ORG_ENTITY_ID'] = $id;
								$cost_info['ORG_EXPEND_ID'] = $insert_billing_id;

								// �ɱ���� �����
								$cost_info['FEE'] = get_pos_fee($_SESSION["uinfo"]["city"],$pone['TRADE_MONEY'],$pone['MERCHANT_NUMBER']);
								//�����û���� �����
								$cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
								//����ʱ�� �����
								$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());
								//�Ƿ��ʽ�أ�0��1�ǣ� �����
								$cost_info['ISFUNDPOOL'] = 0;
								//�ɱ�����ID �����
								$cost_info['ISKF'] = 1;
								//����˰ ��ѡ�
								$cost_info['INPUT_TAX'] = 0;
								//�ɱ�����ID �����
								//$cost_info['FEE_ID'] = $v["FEE_ID"];
								$cost_info['EXPEND_FROM'] = 28;
								$cost_info['FEE_REMARK'] = "��Ա��ƱPOS��������";
								$cost_info['FEE_ID'] = 95;

								$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
								if(!$cost_insert_id){

									break;
								}
							}
						}

						if(!$cost_insert_id){
							$return_error .= "��" . ($key + 1) . "������Ʊʧ��4.<br />";
							$this->model->rollback();
							continue;
						}
					}

                    //��ȡ��ͬ���
                    $contract_num = M("erp_project")
                        ->field("CONTRACT")
                        ->where('ID = ' . $prj_id)
                        ->find();
                    $contract_num = $contract_num['CONTRACT'];

                    //ͬ����ͬϵͳ��Ҫ������
                    $invoiceno_arr[$contract_num][] = array(
                        'invoice_no' => $val['invoiceno'],
                        'invoice_money' => $val['money'],
                        'invoice_tax' => round($val['money']/(1+$val["taxrate"]) * $val["taxrate"],2),
                        'invoice_note' => $remark,
                    );

                    $this->model->commit();
                    userLog()->writeLog('', $_SERVER["REQUEST_URI"], '��Ʊ:���뿪Ʊ::�ɹ�', serialize($_FILES['upfile']));
                    $i++;
                }
            }

            /****�첽ͬ������ͬϵͳ****/
            if($invoiceno_arr){
                foreach($invoiceno_arr as $key=>$val){
                    $cur_invoiceno = array();
                    $invoice_moneys = $invoice_taxs = 0;
                    foreach($val as $k=>$v){
                        //��Ʊ���
                        $cur_invoiceno[] = $v['invoice_no'];
                        //���
                        $invoice_moneys += $v['invoice_money'];
                        //˰��
                        $invoice_taxs += $v['invoice_tax'];
                        //˵��
                        $invoice_notes = $v['invoice_note'];

                        //���������Ʊ
                        if(($v['invoice_no']+1) != $val[$k+1]['invoice_no']){
                            if(count($cur_invoiceno) == 1){
                                $invoice_nos = $cur_invoiceno[0];
                            }else{
                                $invoice_nos = $cur_invoiceno[0].'-'.end($cur_invoiceno);
                            }

                            //ͬ����ͬ��Ʊ����
                            $tongji_url =  CONTRACT_API . 'sync_ct_invoice.php?city=' . $this->user_city_py  . '###contractnum=' . $key . '###money='.$invoice_moneys.'###tax='.$invoice_taxs.'###invono='.$invoice_nos.'###type=2###date='.date('Y-m-d').'###note='.urlencode('����ϵͳ�Զ�ͬ��');
                            api_log($this->city,$tongji_url,0,$this->uid,1);

                            unset($cur_invoiceno,$invoice_moneys,$invoice_taxs);
                        }
                    }
                }
            }

            //ƥ����
            $result["state"] = $i > 0?1:0;

            //ƥ��ʧ��ԭ��
            //1����Ա��¼�ķ�Ʊ���δ��д��
            //2����Ա��¼�Ѿ���ƥ��������Ҹû�ԱҲû������ͨ���Ļ���Ʊ������
            //3����Ա���վݱ�������޷��ҵ���֮ƥ��Ļ�Ա��

            $result["msg"] .= "��,��ƥ���� ".$i ."�����ݣ�<br />";
            $result["msg"] .= $return_error;
            die($result["msg"]);
        }
        else
        {
            $this->display("financial_import");
        }
    }

    /**
    +----------------------------------------------------------
    * ����ҳ�浼����Ʊ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function exportInvoice()
    {
        //��ȡ��������
        $filter_sql = isset($_GET['Filter_Sql'])?trim($_GET['Filter_Sql']):'';

        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '��������ؿ�Ʊ��';//
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

        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A2:R2')->getFont()->setBold(true);

        $styleArray = array(
            'borders' => array (
                'allborders' => array (
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array ('argb' => 'FF000000'),//����border��ɫ
                ),
            ),
        );

        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'��������ܿ�Ʊ'));
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'���ݺ�'));
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('C2', iconv("gbk//ignore","utf-8//ignore",'����˰��'));
        $objActSheet->setCellValue('D2', iconv("gbk//ignore","utf-8//ignore",'������ַ�绰'));
        $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'�����������˺�'));
        $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ��ע'));
        $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'��Ʒ����'));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",'������λ'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'����ͺ�'));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",'����'));
        $objActSheet->setCellValue('K2', iconv("gbk//ignore","utf-8//ignore",'����'));
        $objActSheet->setCellValue('L2', iconv("gbk//ignore","utf-8//ignore",'���'));
        $objActSheet->setCellValue('M2', iconv("gbk//ignore","utf-8//ignore",'˰��'));
        $objActSheet->setCellValue('N2', iconv("gbk//ignore","utf-8//ignore",'˰��'));
        $objActSheet->setCellValue('O2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ��'));
        $objActSheet->setCellValue('P2', iconv("gbk//ignore","utf-8//ignore",'������'));
        $objActSheet->setCellValue('Q2', iconv("gbk//ignore","utf-8//ignore",'�տ���'));
        $objActSheet->setCellValue('R2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ���'));
        $objActSheet->mergeCells('A1:R1');

        $member_id = $_REQUEST["member_id"];

        if($member_id) {
            $sql = "select * from ERP_CARDMEMBER where ID IN($member_id) AND (INVOICE_STATUS = 5 OR CHANGE_INVOICE_STATUS = 2)";
        }
        else{
            $sql = "select * from ERP_CARDMEMBER where  (INVOICE_STATUS = 5 OR CHANGE_INVOICE_STATUS = 2) " . $filter_sql . ' AND CITY_ID = ' . $this->channelid;
        }

        $res = $this->model->query($sql);
        //var_dump($res);DIE;
        if(is_array($res)){
            $i = 3;
            foreach($res as $k => $r){
                #���滻
                $receiptno = str_replace(","," ",$r['RECEIPTNO']);
                $objActSheet->setCellValueExplicit('A'.$i, iconv("gbk//ignore","utf-8//ignore",$receiptno), PHPExcel_Cell_DataType::TYPE_STRING);
                $realname = str_replace(array("/","��",",","��")," ",$r['REALNAME']);
                $realname = iconv("gbk//ignore","utf-8//ignore",$realname);
                $objActSheet->setCellValue('B'.$i, $realname);
                $objActSheet->setCellValue('G'.$i, iconv("gbk//ignore","utf-8//ignore",'�����'));
                $objActSheet->setCellValue('L'.$i, $r['PAID_MONEY']);
                $objActSheet->setCellValue('M'.$i, 6);

                //$objActSheet->getRowDimension($i)->setRowHeight(-1);
                $objActSheet->getRowDimension($i)->setRowHeight(24);
                $i++;
                if($objActSheet->getRowDimension($i)->getRowHeight() > 0){
                    $objActSheet->getRowDimension($i)->setRowHeight($objActSheet->getRowDimension($i)->getRowHeight()+20);
                }
            }
        }
        $objActSheet->getStyle('A1:R'.($i-1))->applyFromArray($styleArray);
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
        userLog()->writeLog('', $_SERVER["REQUEST_URI"], '��Ʊ:������Ʊ:�ɹ�', serialize($_FILES['upfile']));
        exit;
    }

    /**
    +----------------------------------------------------------
    * ����ȷ��
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function reimConfirm()
    {
        $city_channel = $this->channelid;
        //������Model
        $reim_list_model = D("ReimbursementList");
        //������ϸModel
        $reim_detail_model = D("ReimbursementDetail");
        //��������Model
        $reim_type_model = D("ReimbursementType");
        //��Ŀ�ɱ�model
        $project_cost_model = D("ProjectCost");

        $purchase_list_model = D("PurchaseList");
        $purchase_requisition_model = D("PurchaseRequisition");
        $warehouse_model = D("Warehouse");

        $uid = $_SESSION["uinfo"]["uid"];
        $reim_list_status = $reim_list_model->get_conf_reim_list_status();
        $reim_list_status_remark = $reim_list_model->get_conf_reim_list_status_remark();
        $reim_detail_status = $reim_detail_model->get_conf_reim_detail_status();
        $reim_list_type = $reim_type_model->get_reim_type();

        //���ؽ����
        $result = array(
            'state'=>0,
            'msg'=>'',
        );

        $error_str = '';
        //ѡ�б�����  ���б���ȷ��
        if($_POST["reim_id"])
        {
            $reim_id = !empty($_POST["reim_id"]) ? $_POST["reim_id"] : 0;

            if(is_array($reim_id) && !empty($reim_id))
            {
                $fail_confirm = array();//ȷ��ʧ�ܵı������ı��

                //���ʱ����Ƿ񳬳��ж� --- �Ѿ����ύ��ʱ���ж�
                foreach ($reim_id as $key=>$val){

                    $loan_case = D("ProjectCase")->get_conf_case_Loan();
                    $loan_case_str = implode(",",array_keys($loan_case));

                    //1,2,14,15  �ɹ�   Ԥ�������   ���ڲɹ�    С�۷�ɹ� ֧������������   �����ж�
                    $reim_sql = "select  C.projectname,A.case_id,A.type,sum(money) as money from erp_reimbursement_detail A left join erp_case B on A.case_id = B.id";
                    $reim_sql .= " left join erp_project C on B.project_id = C.id";
                    $reim_sql .= " where A.status = 0 AND A.type not in(1,2,14,15,16) and list_id = $val and B.scaletype in ($loan_case_str)";
                    $reim_sql .= " group by C.projectname,A.case_id,A.type";

                    $reim_data = M("erp_reimbursement_detail")->query($reim_sql);

                    foreach($reim_data as $k=>$v){
                        //�ֽ���������ѷ������=�ѷ������+��ǩԼ�ͻ���
                        if($v['TYPE'] == 7){
                            if($ret_loan_limit = is_overtop_payout_limit($v['CASE_ID'], $v['MONEY'],1)){
                                $error_str .= "�������Ϊ��$val,��Ŀ��" . $v['PROJECTNAME'] . "���������ʱ����򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ� " . "<br />";
                            }
                        }else{
                            if($ret_loan_limit = is_overtop_payout_limit($v['CASE_ID'], $v['MONEY'],0,1)){
                                $error_str .= "�������Ϊ��$val,��Ŀ��" . $v['PROJECTNAME'] . "���������ʱ����򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ� " . "<br />";
                            }
                        }

                    }
                }
                //����д���ֱ�Ӵ��
                if(!empty($error_str)){
                    $result['msg'] = g2u($error_str);
                    die(json_encode($result));
                }
                //���ʱ����Ƿ񳬳��ж� --- ����

                foreach ($reim_id as $key=>$val)
                {
                    $reim_type = $reim_list_model->get_info_by_id($val,array("TYPE"));

                    $this->model->startTrans();
                    //����ȷ�ϱ���
                    $list_up_num = $reim_list_model->sub_reim_list_to_completed($val, $uid);
                    $detail_up_num = $reim_detail_model->sub_reim_detail_to_completed($val);

                    //������ȷ�ϳɹ��� �����Ӧ�ɱ����ɱ���¼��                    
                    if($list_up_num && $detail_up_num)
                    {
                        //���ݱ�����ID�ҵ����еı�����ϸ
                        $search_arr = array("ID","CITY_ID","CASE_ID","BUSINESS_ID","INPUT_TAX","MONEY","STATUS",
                                            "ISFUNDPOOL","ISKF", "TYPE","FEE_ID","BUSINESS_PARENT_ID","PURCHASER_BEE_ID","DEPT_ID","NCTYPE");
                        $reim_detail_info = $reim_detail_model->get_detail_info_by_listid($val,$search_arr);


                        foreach ($reim_detail_info as $k => $v)
                        {
                            if($v["STATUS"] == $reim_detail_status["reim_detail_completed"] && in_array($reim_type[0]['TYPE'],array(3,4,6,9,10,12, 21, 22, 23, 24, 25))) {
                                //����״̬
                                $status_up_array = array();
                                switch (intval($reim_type[0]['TYPE'])) {
                                    case 3:
                                        $status_up_array['AGENCY_REWARD_STATUS'] = 5;
                                        break;
                                    case 4:
                                        $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 5;
                                        break;
                                    case 6:
                                        $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 5;
                                        break;
                                    case 9:
                                        $status_up_array['AGENCY_REWARD_STATUS'] = 5;
                                        break;
                                    case 10:
                                    case 22:
                                        $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 5;
                                        break;
                                    case 12:
                                    case 23:
                                        $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 5;
                                        break;
                                    case 21:
                                    case 24:
                                    case 25:
                                        $status_up_array['OUT_REWARD_STATUS'] = 5;
                                        break;
                                }

                               // $member_table = in_array($reim_type[0]['TYPE'],array(3,4,6))?"erp_cardmember":"erp_member_distribution";
                                $reim_status_up = M("erp_cardmember")->where("ID = {$v['BUSINESS_ID']}")->save($status_up_array);
                            }

                           if($v["STATUS"] == $reim_detail_status["reim_detail_completed"])//�ѱ�������ϸ¼��ɱ�
                           {
                                switch ($v["TYPE"])
                                {
                                    case "1":
                                        $purchase_cotract_model = D("PurchaseContract");
                                        $res = $purchase_cotract_model->sub_contract_to_reimbursed_by_reim_listid($val);
                                        //echo $this->model->_sql(); die;
                                        $purchase_list_model->update_to_reimbursed_by_id($v["BUSINESS_ID"]);
                                        $prId = $purchase_list_model->where("ID = {$v['BUSINESS_ID']}")->getField('pr_id');

                                        //�����û���ԭ��Ŀ����
                                        $dbPurchase = D('PurchaseList')->getPurchaseJoinReq($v["BUSINESS_ID"]);
                                        $returnIncome = D('PurchaseList')->insertDisplaceIncome($dbPurchase[0]);

                                    case "16":  // ֧������������
                                    case "2":
                                        $benefits_model = D("Benefits");
                                        $bus_id = $reim_detail_model->get_detail_info_by_id($v["ID"],array("BUSINESS_ID"));
                                        $benefits_status_arr = $benefits_model->get_cost_status();
                                        $benefits_status = $benefits_status_arr["have_reimed"];
                                        $res = $benefits_model->
                                            update_info_by_id($bus_id[0]["BUSINESS_ID"],array("ISCOST"=>$benefits_status));
                                        break;
                                    case "7":
                                        $locale_granted_model = D("LocaleGranted");
                                        $res = $locale_granted_model->sub_granted_to_reimbursed_by_id($val);
                                        //sub_granted_to_reimbursed_by_id
                                        break;
                                    case "13":
                                        $cost_suplement_model = D("CostSupplement");
                                        $bus_id = $reim_detail_model->get_detail_info_by_id($v["ID"],array("BUSINESS_ID"));
                                        $cost_status_arr = $cost_suplement_model->get_cost_supplement_status();
                                        $cost_status = $cost_status_arr["have_reim"];
                                        $res = $cost_suplement_model->
                                            update_cost_supplement_info_by_ids($bus_id[0]["BUSINESS_ID"],array("STATUS"=>$cost_status));
                                        continue;
                                        break;
                                    case "14":
                                        $from_arr = $warehouse_model->get_conf_from();
                                        $satus_arr = $warehouse_model->get_conf_status();
                                        $purchase_cotract_model = D("PurchaseContract");
                                        $res = $purchase_cotract_model->sub_contract_to_reimbursed_by_reim_listid($val);

                                        //���ݲɹ���ϸid�ҵ���ϸ
                                        $purchase_info = $purchase_list_model->get_purchase_list_by_id($v["BUSINESS_ID"]);

                                        if(!empty($purchase_info) && $purchase_info[0]['NUM'] > 0)
                                        {
                                            $ware_info = array();
                                            $ware_info['PL_ID'] = $purchase_info[0]['ID'];
                                            $ware_info['BRAND'] = $purchase_info[0]['BRAND'];
                                            $ware_info['MODEL'] = $purchase_info[0]['MODEL'];
                                            $ware_info['PRODUCT_NAME'] = $purchase_info[0]['PRODUCT_NAME'];
                                            $ware_info['FEE_ID'] = $purchase_info[0]['FEE_ID'];
                                            $ware_info['IS_KF'] = $purchase_info[0]['IS_KF'];
                                            $ware_info['INPUT_TAX'] = $v["INPUT_TAX"]; ///$purchase_info[0]['INPUT_TAX'];
                                            $ware_info['IS_FUNDPOOL'] = $purchase_info[0]['IS_FUNDPOOL'];
                                            $ware_info['ADDTIME'] = date('Y-m-d H:i:s');
                                            $ware_info['IS_FROM'] = $from_arr['bulk_purchase'];
                                            $ware_info['STATUS'] = $satus_arr['audited'];
                                            $ware_info['CITY_ID'] = $v['CITY_ID'];

                                            //���ݹ��������ⵥ��
                                            $reim_total_cost = floatval($v["MONEY"]);
                                            $warehouse_num = floatval($purchase_info[0]['NUM']);
                                            $warehouse_unit_price = self::_get_avg_price($reim_total_cost, $warehouse_num);

                                            if($reim_total_cost / $warehouse_num != $warehouse_unit_price)
                                            {
                                                $high_price = $reim_total_cost - ($warehouse_num - 1 ) * $warehouse_unit_price;
                                                $ware_info['PRICE'] = $high_price;
                                                $ware_info['NUM'] = 1;
                                                //��������Ϣ
                                                $cost_insert_id_high = $warehouse_model->add_warehouse_info($ware_info);
                                                $warehouse_num = $warehouse_num - $ware_info['NUM'];
                                            }

                                            if($warehouse_num > 0)
                                            {
                                                $ware_info['PRICE'] = $warehouse_unit_price;
                                                $ware_info['NUM'] = $warehouse_num;

                                                //��������Ϣ
                                                $cost_insert_id = $warehouse_model->add_warehouse_info($ware_info);
                                            }

                                            if($cost_insert_id_high || $cost_insert_id)
                                            {
                                               $purchase_list_model->update_to_in_warehouse_by_id($v["BUSINESS_ID"]);
                                            }
                                        }
                                        continue;
                                        break;
                                    case "15":
                                         //С�۷�ɹ�������
                                         $bee_detail_model = D('PurchaseBeeDetails');
                                         //����С�۷�������ϸ״̬
                                         $update_result = $bee_detail_model->where('ID='.$v['PURCHASER_BEE_ID'])->save(array('STATUS' => 2));
										 if($update_result) send_result_to_zk($v['PURCHASER_BEE_ID'],$this->channelid );//ͬ�����ڿ�
                                         break;
                                    case "17":
                                        // �����н��Ӷ����
                                        $dbResult = D('erp_commission_reim_detail')->where("POST_COMMISSION_ID = {$v['BUSINESS_ID']}")->save(array(
                                            'STATUS' => 3
                                        ));
                                        if ($dbResult !== false) {
                                            $remainPostComisAmount = D('ReimbursementList')->getRemainFxPostComisReimAmount($v['BUSINESS_PARENT_ID'], $v['BUSINESS_ID']);
                                            if ($remainPostComisAmount <= 0) {
                                                // ��ȫ����
                                                $dbResult = D('erp_post_commission')->where("ID = {$v['BUSINESS_ID']}")->save(array(
                                                    'POST_COMMISSION_STATUS' => 3
                                                ));
                                            } else {
                                                // ���ֱ���
                                                $dbResult = D('erp_post_commission')->where("ID = {$v['BUSINESS_ID']}")->save(array(
                                                    'POST_COMMISSION_STATUS' => 2
                                                ));
                                            }
                                        }
                                        if ($dbResult === false) {
                                            $fail_confirm[] = $val;
                                        }
                                        break;
                                    default :
                                        $res = 1;
                                        break;
                                }

                                $cost_info['CASE_ID'] = $v["CASE_ID"];                          //������� �����       
                                $cost_info['ENTITY_ID'] = $val;       //�������뵥id                          
                                $cost_info['EXPEND_ID'] = $v["ID"];   //������ϸid                         
                                $cost_info['ORG_ENTITY_ID'] = $v["BUSINESS_PARENT_ID"];     //  ҵ��ʵ�� ����Ŀid ��������
                                $cost_info['ORG_EXPEND_ID'] = $v["BUSINESS_ID"];                //ҵ����ϸ��� �����(�ɹ���id)
                                $cost_info['FEE'] = $v["MONEY"];                               // �ɱ���� ����� 
                                $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];            //�����û���� �����
                                $cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //����ʱ�� �����
                                $cost_info['ISFUNDPOOL'] = $v["ISFUNDPOOL"];                  //�Ƿ��ʽ�أ�0��1�ǣ� �����
                                $cost_info['ISKF'] = $v["ISKF"];                             //�ɱ�����ID �����
                                $cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //����˰ ��ѡ�
                                $cost_info['FEE_ID'] = $v["FEE_ID"];                         //�ɱ�����ID �����
								if($v["DEPT_ID"])$cost_info['DEPT_ID'] = $v["DEPT_ID"]; 
								if( $v["NCTYPE"] )$cost_info['NCTYPE'] = $v["NCTYPE"]; 


                                switch ($reim_type[0]["TYPE"])
                                {
                                    case "1":
                                    case "15":
                                        $cost_info['EXPEND_FROM'] = 4;
                                        $cost_info['FEE_REMARK'] = "�ɹ�����";
                                        break;
                                    case "2":
                                        $cost_info['EXPEND_FROM'] = 20;
                                        $cost_info['FEE_REMARK'] = "Ԥ������ñ���";
                                        break;
                                    case "3":
                                        $cost_info['EXPEND_FROM'] = 7   ;
                                        $cost_info['FEE_REMARK'] = "���̻�Ա�н�Ӷ����";
                                        break;
                                    case "4":
                                        $cost_info['EXPEND_FROM'] = 10;
                                        $cost_info['FEE_REMARK'] = "���̻�Ա�н�ɽ���������";
                                        break;
                                    case "5":
                                        $cost_info['EXPEND_FROM'] = 13;
                                        $cost_info['FEE_REMARK'] = "���̻�Ա��ҵ����Ӷ����";
                                        break;
                                    case "6":
                                        $cost_info['EXPEND_FROM'] = 16;
                                        $cost_info['FEE_REMARK'] = "���̻�Ա��ҵ���ʳɽ���������";
                                        break;
                                    case "7":
                                        $cost_info['EXPEND_FROM'] = 24;
                                        $cost_info['FEE_REMARK'] = "�ֽ𷢷ű���";
                                        break;
                                    case "8":
                                        $cost_info['EXPEND_FROM'] = 16;
                                        $cost_info['FEE_REMARK'] = "����������";
                                        break;
                                    case "9":
                                    case "17":
                                        $cost_info['EXPEND_FROM'] = 7;
                                        $cost_info['FEE_REMARK'] = "������Ա�н�Ӷ����";
                                        break;
                                    case "10":
                                    case "22":
                                        $cost_info['EXPEND_FROM'] = 10;
                                        $cost_info['FEE_REMARK'] = "������Ա�н�ɽ���������";
                                        break;
                                    case "11":
                                        $cost_info['EXPEND_FROM'] = 13;
                                        $cost_info['FEE_REMARK'] = "������Ա��ҵ����Ӷ����";
                                        break;
                                    case "12":
                                    case "23":
                                        $cost_info['EXPEND_FROM'] = 16;
                                        $cost_info['FEE_REMARK'] = "������Ա��ҵ���ʳɽ���������";
                                        break;
                                    case "13":
                                        $cost_info['EXPEND_FROM'] = 26;
                                        $cost_info['FEE_REMARK'] = "�ɱ���䱨��";
                                        break;
                                    case '16':
                                        $cost_info['EXPEND_FROM'] = 33;
                                        $cost_info['FEE_REMARK'] = "֧�����������ñ���ͨ��";
                                        break;
                                    case "21":
                                    case "24":
                                    case "25":
                                        $cost_info['EXPEND_FROM'] = 35;
                                        $cost_info['FEE_REMARK'] = '�����ⲿ�ɽ���������';
                                    default :
                                        break;
                                }
                                if( $v["TYPE"] != "14" && $v["TYPE"] != "13")
                                {//var_dump($cost_info);
                                    $cost_insert_id = $project_cost_model->add_cost_info($cost_info);
                                }
								if($v["ISFUNDPOOL"]){
									$ruleArr = array('3','4','5','6','7','9','10','11','12','17','21','22','23','24','25');
									//��֧��ҵ��Ѵ���
									$tprice = $v["MONEY"];
									$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$v['CASE_ID']."' and TYPE=1";
									$finalaccounts = M()->query($sql);
									$xgfee = $finalaccounts[0]['TOBEPAID_FUNDPOOL'] > $tprice ? $finalaccounts[0]['TOBEPAID_FUNDPOOL']-$tprice  : 0;
									if($xgfee!=$finalaccounts[0]['TOBEPAID_FUNDPOOL'] && $finalaccounts[0]['STATUS']==2 && in_array($v['TYPE'],$ruleArr)){
										D('Erp_finalaccounts')->where("CASE_ID='".$v['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_FUNDPOOL'=>$xgfee) );
									}
								}

                                if(!$cost_insert_id && $returnIncome!==false)
                                {
                                    $this->model->rollback();
                                    $fail_confirm[] = $val;
                                }
                                else
                                {
                                    $this->model->commit();
                                }
                            }

                        }
                    }
                    else
                    {
                        $this->model->rollback();
                        $fail_confirm[] = $val;
                    }
                }
                //var_dump($fail_confirm);die;
                if(is_array($fail_confirm) && !empty($fail_confirm))
                {
                    $fail_str = implode(",", $fail_confirm);
                    $result["state"] = 0;
                    $result["msg"] = "���Ϊ $fail_str �ı�������ȷ��ʧ�ܣ������³��ԣ�";
                }
                else
                {
                    $result["state"] = 1;
                    $result["msg"] = "����ȷ�ϳɹ���";
                }
            }

            //��־
            userLog()->writeLog(implode(',', $_POST["reim_id"]), $_SERVER["REQUEST_URI"], '����ȷ��:ȷ�ϱ���:' . $result["msg"] , serialize($_POST));
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        //����ҳ�棬��ʾ������
        else
        {
            Vendor('Oms.Form');
            $form = new Form();
            $children = array(
                            array('������ϸ',U('/Financial/reimDetail')),
                            array('�������',U('/Financial/loanMoney')),
                );
            $conf_where = "STATUS = ".$reim_list_status['reim_list_sub']." and CITY_ID = ".$city_channel;
            $form->initForminfo(176)
                ->where($conf_where)
                ->setMyField("TYPE", "LISTCHAR", array2listchar($reim_list_type))
                ->setMyField("STATUS", "LISTCHAR", array2listchar($reim_list_status_remark))
                ->setChildren($children);

            $form->SQLTEXT = '(select a.*,b.LOANMONEY  from ERP_REIMBURSEMENT_LIST a
        left join (select  APPLICANT,sum(UNREPAYMENT) as LOANMONEY from ERP_LOANAPPLICATION where STATUS IN(2,6) AND CITY_ID = '. $this->channelid . ' group by APPLICANT) b
        on a.APPLY_UID = b.APPLICANT )';

            //$form->DELCONDITION = "%STATUS%==0";
            $form->DELABLE = 0;
            $form->EDITABLE = 0;  // ���ɱ༭
            $form->SHOWCHECKBOX = -1;
            $form->GABTN = '<a id="reim_confirm" href="javascript:;" onclick="reim_confirm()" class="btn btn-info btn-sm">ȷ�ϱ���</a>'
                         . '<a id="reim_refuse" href="javascript:;" onclick="reim_refuse()" class="btn btn-info btn-sm">���</a>  <a id="reim_confirm_time" href="javascript:;"   class="btn btn-info btn-sm" operate_type="edit_purchase">�༭ȷ��ʱ��</a>';

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
            $formHtml = $form->getResult();
            $this->assign("form",$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
            $this->assign("paramUrl",$this->_merge_url_param);
            $this->display('financial_reimconfirm');
        }
    }

    /**
     * �����ر�����
     *@param none
     * return boolean TRUE�ɹ�\FALSEʧ��
     */
    public function reim_refuse()
    {
        $reim_list_model = D("ReimbursementList");
        $reim_detail_model = D("ReimbursementDetail");
        $reim_type_model = D("ReimbursementType");
		//��Ŀ�ɱ�model
        $project_cost_model = D("ProjectCost");

        //����ʧ�ܵı�������� ����
        $fail_reim_arr = array();

        //����ʧ�ܵı�������ţ����Ӻ���ַ���
        $fail_reim_str = "";

        $reim_list_id = !empty($_POST["reim_id"]) ? $_POST["reim_id"] : "";
        $amount = !empty($_POST["amount"]) ? $_POST["amount"] : "";
        $reim_list_id_str = implode(',', $reim_list_id);
        $reim_lists = $reim_list_model->field('ID,TYPE')->where('ID IN ('.$reim_list_id_str.')')->select();
        foreach ($reim_lists as $key=>$val){
            $money = 0-$amount[$key];
            $bee_reim = false;
            //�����С�۷�ɹ������򵥶������ش���
            if ($val['TYPE']==15){
                $bee_reim = true;
            }

            if ($val['TYPE'] == 16) {
                // ���ص�����֧�����ñ�������
                $this->revertFundPoolCostApply($val, $fail_reim_arr);
            } else {
                $this->model->startTrans();
                $refuse_result = $reim_list_model->sub_reim_list_backto_apply($val['ID'], $money,$bee_reim);
                if(!$refuse_result){
                    $this->model->rollback();
                    $fail_reim_arr[] = $val['ID'];
                }else{
                    //С�۷�ɹ����̲��ش���
                    if ($bee_reim){
                        //��ȡ���б�������
                        $reim_details = $reim_detail_model->where('LIST_ID='.$val['ID'])->select();
                        if (!empty($reim_details)){
                            $need_change_status = array();
                            $cost_insert_id  = true;
                            foreach ($reim_details as $v){
                                $need_change_status[] = $v['PURCHASER_BEE_ID'];
                                $cost_info = array();
                                $cost_info['CASE_ID'] = $v["CASE_ID"];                          //������� �����
                                $cost_info['ENTITY_ID'] = $val['ID'];       //�������뵥id
                                $cost_info['EXPEND_ID'] = $v["ID"];   //������ϸid
                                $cost_info['ORG_ENTITY_ID'] = $v["BUSINESS_PARENT_ID"];     //  ҵ��ʵ�� ����Ŀid ��������
                                $cost_info['ORG_EXPEND_ID'] = $v["BUSINESS_ID"];                //ҵ����ϸ��� �����(�ɹ���id)
                                $cost_info['FEE'] = - $v["MONEY"];                               // �ɱ���� �����
                                $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];            //�����û���� �����
                                $cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //����ʱ�� �����
                                $cost_info['ISFUNDPOOL'] = $v["ISFUNDPOOL"];                  //�Ƿ��ʽ�أ�0��1�ǣ� �����
                                $cost_info['ISKF'] = $v["ISKF"];                             //�ɱ�����ID �����
                                $cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //����˰ ��ѡ�
                                $cost_info['FEE_ID'] = $v["FEE_ID"];
                                $cost_info['EXPEND_FROM'] = 31;
                                $cost_info['FEE_REMARK'] = "�ɹ��������벵��";//var_dump($cost_info);
                                $cost_insert_id = $project_cost_model->add_cost_info($cost_info);
                                if(!$cost_insert_id){
                                    //$this->model->rollback();
                                    break;
                                }

                            }
                            $model_bee_work = D('PurchaseBeeDetails');
                            $need_change_status = implode(',', $need_change_status);
                            $update = $model_bee_work->where("ID IN ($need_change_status)")->save(array('STATUS'=>3));

                            //var_dump( $need_change_status);


                            if (!$update || !$cost_insert_id){
                                $this->model->rollback();
                                $fail_reim_arr[] = $val['ID'];
                            }else{


                                send_result_to_zk($need_change_status,$this->channelid );//ͬ�����ڿ�
                            }
                        }
                    }
                    $dbResult = true;
                    // ����Ƿ����н��Ӷ�������򽫹�����ϸ����Ϊδ����
                    if ($val['TYPE'] == 17) {
                        $dbResult = D('erp_commission_reim_detail')->where("REIM_LIST_ID = {$val['ID']}")->save(array('STATUS' => 1));
                    }
                    if ($dbResult !== false) {
                        $this->model->commit();
                    } else {
                        $this->model->rollback();
                    }

                }
            }

        }

        if(is_array($fail_reim_arr) && !empty($fail_reim_arr))
        {
            $fail_reim_str = implode(",",$fail_reim_arr);
            $result["state"] = 0;
            $result["msg"] = "���Ϊ ".$fail_reim_str ."�ı���������ʧ�ܣ���";
        }
        else
        {
            $result["state"] = 1;
            $result["msg"] = "���б����������˳ɹ�����";
        }

        userLog()->writeLog(implode(',', $_POST["reim_id"]), $_SERVER["REQUEST_URI"], '����ȷ��:���:' . $result["msg"] , serialize($_POST));
        $result["msg"] = g2u($result["msg"]);

        echo json_encode($result);
        exit;
    }


    /**
    +----------------------------------------------------------
    * ����ҳ�渶����ϸչʾ�б�
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function payment()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form =  $form->initForminfo(126);
        //���ø��ʽ
        $member_pay = D('MemberPay');
        $member = D("Member");
        $where = "STATUS != 4";
        $pay_arr = $member_pay->get_conf_pay_type();
        $_conf_status_remark = $member->get_conf_all_status_remark();
        $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE)->where($where);
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('form',$formHtml);
        $this->display('payment');
    }

    /**
    +----------------------------------------------------------
    * ����ҳ�汨����ϸչʾ�б�
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function reimDetail()
    {
        if($_REQUEST['jinxianshui']){
			Vendor('Oms.Form');
			$form = new Form();
			$form->initForminfo(200);
			$form= $form->getResult();
            $this->assign('form',$form);

            $this->display('reim_details_tax');
			exit;
		}
		//�������뵥MODEL
        $reim_list_model = D('ReimbursementList');
        //����MODEL
        $reim_detail_model = D('ReimbursementDetail');
        //��������
        $reim_type_model = D('ReimbursementType');

        //������ϸ״̬��־����
        $reim_detail_status_remark = $reim_detail_model->get_conf_reim_detail_status_remark();

        //�ɱ����Model
        $cost_supplement_model = D("CostSupplement");
        $cost_supplement_status = $cost_supplement_model->get_cost_supplement_status();

        Vendor('Oms.Form');
		$form = new Form();
        $form2 = new Form();
		$form2->initForminfo(75);
		$form2->setMyField('DEPT_ID', 'EDITTYPE', '23', FALSE);//
		$form2->setMyField('DEPT_ID', 'LISTSQL', 'SELECT ID, DEPTFULLNAME, PARENTID FROM MV_ERP_DEPTFULLNAME  where ISVALID=-1', FALSE);
		 
		$form3 = new Form();
		$form3->initForminfo(137);
		$form3->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
        $form3->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
		$feeOptions = addslashes(u2g($form3->getSelectTreeOption('FEE_ID', '', -1)));
		 $this->assign('feeOptions',$feeOptions); 
		$deptOptions = addslashes(u2g($form2->getSelectTreeOption('DEPT_ID', '', -1)));
		 $this->assign('deptOptions',$deptOptions);
	 
		$nclist = D("Erp_nctype")->select();
		foreach($nclist as $key=>$one){
			$nctyeOptions .="<option value=\"".$one['ID']."\">".$one['TYPENAME']."</option>"; 

		}
 
		 $this->assign('nctyeOptions',$nctyeOptions); 
		 
        //���ݱ�����Id��ȡ��������
        $reim_list_id = $_REQUEST["parentchooseid"];
        $reim_list_info = $reim_list_model->get_info_by_id($reim_list_id,array("TYPE"));
        $reim_list_type = $reim_list_info[0]["TYPE"];
        $this->assign('reim_list_type',$reim_list_type);
        switch($reim_list_type)
        {
            case "1":
            case "14":
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $uid = intval($_SESSION['uinfo']['uid']);
                $city = $this->channelid;
                $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
                $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

                $cond_where = "LIST_ID = '".$list_id."' AND STATUS != 4";
                $form->initForminfo(182)
                    ->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1')
                    ->where($cond_where);
                $form->SQLTEXT = <<<SQLTEXT
                (SELECT L.BRAND,
                      L.MODEL,
                      L.NUM,
                      L.NUM_LIMIT,
                      L.PRICE,
                      L.PRICE_LIMIT,
                      L.PRODUCT_NAME,
                      L.P_ID,
                      L.ADD_TIME,
                      getReimPurchaseContractId(L.CONTRACT_ID, B.ID) AS CONTRACT_ID,
                      L.COST_OCCUR_TIME,
                      L.PURCHASE_OCCUR_TIME,
                      D.*,
                      B.PROJECTNAME,
                      S.NAME SUPPLIER_NAME
               FROM ERP_REIMBURSEMENT_DETAIL D
               LEFT JOIN ERP_CASE A ON D.CASE_ID = A.ID
               LEFT JOIN ERP_PROJECT B ON A.PROJECT_ID = B.ID
               INNER JOIN ERP_PURCHASE_LIST L ON D.BUSINESS_ID = L.ID
               INNER JOIN ERP_PURCHASE_REQUISITION PR ON D.CASE_ID = PR.CASE_ID
               INNER JOIN ERP_SUPPLIER S ON S.ID = L.S_ID
               AND PR.ID = L.PR_ID)
SQLTEXT;

                //���ñ���������
                $type_arr = $reim_type_model->get_reim_type();
                $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                //������ϸ״̬����
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);

                //�ɹ���
                $user_sql = "select ID,NAME from erp_users";
                $form->setMyField('P_ID', 'LISTSQL',$user_sql, FALSE);

                //��������
                $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);

                //��ͬ��
//                $form->setMyField('CONTRACT_ID', 'LISTSQL', 'SELECT ID, CONTRACTID '
//                    . ' FROM ERP_CONTRACT', FALSE);

                //����״̬����ɾ����ť�Ƿ���ʾ
                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
                $form->SHOWCHECKBOX = -1;

                $form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>�༭����˰</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>�༭������ϸ</a>";
                $formHtml= $form->getResult();
                $this->assign('form',$formHtml);
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');
                break;
            case "2":
            case "16":
                $reim_detail_id = $reim_detail_model->get_detail_info_by_listid($reim_list_id, array("ID"));
                $reim_detail_id_str = "(";
                foreach ($reim_detail_id as $key => $val) {
                    $reim_detail_id_str .= $val["ID"] . ",";
                }
                $reim_detail_id_str = rtrim($reim_detail_id_str, ",");
                $reim_detail_id_str .= ")";
                //echo $reim_detail_id_str;die;
                $conf_where = "ID IN" . $reim_detail_id_str . " AND ISCOST != 4";
                if ($reim_list_type == 16) {
                    $form->initForminfo(203);
                    $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                    $form->SQLTEXT = <<<SQL
                        (SELECT A.ID,
                               A.MONEY AMOUNT,
                               A.STATUS ISCOST,
                               A.INPUT_TAX,
                               B.PROJECT_NAME,
                               B.SCALE_TYPE,
                               B.ADDTIME,
                               B.AUSER_ID NAME,
                               B.CASE_ID,
                               B.SUPPLIER,
                                P.contract,
                                B.attachment,
                                B.desript
                        FROM ERP_REIMBURSEMENT_DETAIL A
                        LEFT JOIN ERP_BENEFITS B ON A.BUSINESS_ID=B.ID
                        LEFT JOIN erp_project P ON P.id = B.project_id
                        WHERE A.TYPE={$reim_list_type})
SQL;
                } else {
                    $form->initForminfo(115);
                    $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                    $form->SQLTEXT = "(SELECT A.ID,A.MONEY AMOUNT,A.STATUS ISCOST,A.INPUT_TAX,B.PROJECT_NAME,B.SCALE_TYPE,B.ADDTIME,B.AUSER_ID NAME,B.CASE_ID, B.DESRIPT,B.SUPPLIER FROM ERP_REIMBURSEMENT_DETAIL A LEFT JOIN ERP_BENEFITS B ON A.BUSINESS_ID=B.ID WHERE A.TYPE=" . $reim_list_type . ")";
                }

                $form->where($conf_where);
                $form->GABTN = "";
//                $form->GCBTN = '<a id="j-sequence" class="j-showalert btn btn-info btn-sm"  href="javascript:;">����</a>
//                                <a id="j-search" class="j-showalert btn btn-info btn-sm" href="javascript:;">����</a>
//                                <a class="j-refresh btn btn-warning btn-sm" onclick="window.location.reload();" href="javascript:;">ˢ��</a>';
                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
                $form->SHOWDETAIL = 0;
                $form->setMyField("STATUS", "GRIDVISIBLE", "0")
                    ->setMyField("CASE_ID", "GRIDVISIBLE", '-1')
                    ->setMyField("PROJECT_NAME", "GRIDQUEUE", '2')
                    ->setMyField("NAME", "LISTSQL", "SELECT ID,NAME FROM ERP_USERS")
                    ->setMyField("ISCOST", "LISTCHAR", array2listchar($reim_detail_status_remark))
                    ->setMyField('SUPPLIER', 'EDITTYPE', 21, FALSE)
            ->setMyField('SUPPLIER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);
                $form->SHOWCHECKBOX = -1;
                $form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>�༭����˰</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>�༭������ϸ</a>";
                $form = $form->getResult();
                $this->assign('form', $form);
                $this->assign('paramUrl', $paramUrl);
                $this->display('reim_details');
                break;
            case "3":
            case "4":
            case "5":
            case "6":
            case "7":
            case "21":
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $uid = intval($_SESSION['uinfo']['uid']);
                $city = $this->channelid;
                $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
                $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

                //����LIST��ѯ����������
                $list_info = $reim_list_model->get_info_by_id($list_id, array('TYPE'));
                $reim_type = !empty($list_info[0]['TYPE']) ? intval($list_info[0]['TYPE']) : 0;

                //echo $reim_type;
                if(in_array($reim_type, array(3,4,5,6,21)) )
                {
                    $form_id = 177;
                }
                else if( $reim_type == 7)
                {
                    $form_id = 178;
                }

                $cond_where = "LIST_ID = '".$list_id."' AND STATUS != 4";
                $form = $form->initForminfo($form_id)->where($cond_where);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                //�������������3,4,5,6 ����ȡͳ�����ݣ�
                switch($reim_type){
                    case "3":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.AGENCY_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;
                    case "4":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.AGENCY_DEAL_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;
                    case "5":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.PROPERTY_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;
                    case "6":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.PROPERTY_DEAL_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;
                    case "21":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.OUT_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;

                }

                if(in_array($reim_type,array(3,4,5,6,21)) && $showForm != 2){
                    $total_pro = array();
                    $total_pro = D()->query($sql);
                    $maxCount = count($total_pro);
                    $agency_reward = 0;
                    $agency_count = 0;
                    if($total_pro){
                        foreach($total_pro as $key=>$val){
                            $agency_reward = $agency_reward + $val['AGENCY_REWARD'];
                            $agency_count = $agency_count +  $val['AGENCY_COUNT'];
                        }
                        $total_pro[$maxCount]['PRJ_ID'] = 0;
                        $total_pro[$maxCount]['PRJ_NAME'] = '�ϼ�';
                        $total_pro[$maxCount]['AGENCY_REWARD'] = $agency_reward;
                        $total_pro[$maxCount]['AGENCY_COUNT'] = $agency_count;
                    }

                    $this->assign('total_pro',$total_pro);
                }

                //����״̬����ɾ����ť�Ƿ���ʾ
                //$form->DELCONDITION = '%STATUS% == 0';

                if(in_array($reim_type, array(3,4,5,6,21)) )
                {
                    $member_model = D('Member');

                    //���û�Ա��Դ
                    $source_arr = $member_model->get_conf_member_source_remark();
                    $form = $form->setMyField('SOURCE', 'LISTCHAR',
                            array2listchar($source_arr), FALSE);

                    //������
                    $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                    $form->setMyField("ROOMNO", "GRIDVISIBLE", '-1')
                        ->setMyField("HOUSETOTAL", "GRIDVISIBLE", '-1')
                        ->setMyField("HOUSEAREA", "GRIDVISIBLE", '-1');

                    switch($reim_type)
                    {
                        case 3:
                            $form->setMyField("AGENCY_REWARD", "GRIDVISIBLE", '-1');
                            break;
                        case 4:
                            $form->setMyField("AGENCY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                            break;
                        case 5:
                            $form->setMyField("PROPERTY_REWARD", "GRIDVISIBLE", '-1');
                            break;
                        case 6:
                            $form->setMyField("PROPERTY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                            break;
                        case 21:
                            $form->setMyField("OUT_REWARD", "GRIDVISIBLE", '-1');
                            break;
                    }
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
                    $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                    $form->setMyField("ISKF", "GRIDVISIBLE", '0')
                        ->setMyField("TYPE", "GRIDVISIBLE", '0')
                        ->setMyField("STATUS", "GRIDVISIBLE", '0')
                        ->setMyField("ADD_UID", "GRIDVISIBLE", '-1');
                    $form->DELABLE = 0;

                    //���ñ���������
                    $type_arr = $reim_type_model->get_reim_type();
                    $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                    //������ϸ״̬����
                    $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                    $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
                }
                //����ɾ����ť�Ƿ���ʾ
                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
				$form->SHOWCHECKBOX = -1;
				$form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>�༭����˰</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>�༭������ϸ</a>";
                $formHtml= $form->getResult();
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
                $this->assign('form',$formHtml);
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');

                break;
            case "8":
                break;
            case "9":
            case "10":
            case "11":
            case "12":
            case "25":
            $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
            $uid = intval($_SESSION['uinfo']['uid']);
            $city = $this->channelid;
            $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
            $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

            //����LIST��ѯ����������
            $list_info = $reim_list_model->get_info_by_id($list_id, array('TYPE'));
            $reim_type = !empty($list_info[0]['TYPE']) ? intval($list_info[0]['TYPE']) : 0;

            $cond_where = "LIST_ID = '".$list_id."' AND STATUS != 4";
            $form = $form->initForminfo(177)->where($cond_where);
            $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
            //�������������9,10,11,12����ȡͳ�����ݣ�
            switch($reim_type){
                case "9":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.AGENCY_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                    break;
                case "10":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.AGENCY_DEAL_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                    break;
                case "11":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.PROPERTY_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                    break;
                case "12":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.PROPERTY_DEAL_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                    break;
                case "25":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.OUT_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;

            }

            if(in_array($reim_type,array(9,10,11,12,25)) && $showForm != 2){
                $total_pro = array();
                $total_pro = D()->query($sql);
                $maxCount = count($total_pro);
                $agency_reward = 0;
                $agency_count = 0;
                if($total_pro){
                    foreach($total_pro as $key=>$val){
                        $agency_reward = $agency_reward + $val['AGENCY_REWARD'];
                        $agency_count = $agency_count +  $val['AGENCY_COUNT'];
                    }
                    $total_pro[$maxCount]['PRJ_ID'] = 0;
                    $total_pro[$maxCount]['PRJ_NAME'] = '�ϼ�';
                    $total_pro[$maxCount]['AGENCY_REWARD'] = $agency_reward;
                    $total_pro[$maxCount]['AGENCY_COUNT'] = $agency_count;
                }

                $this->assign('total_pro',$total_pro);
            }

            //����״̬����ɾ����ť�Ƿ���ʾ
            //$form->DELCONDITION = '%STATUS% == 0';

            if(in_array($reim_type, array(9,10,11,12,25)) )
            {
                $member_model = D('Member');

                //���û�Ա��Դ
                $source_arr = $member_model->get_conf_member_source_remark();
                $form = $form->setMyField('SOURCE', 'LISTCHAR',
                    array2listchar($source_arr), FALSE);

                //������
                $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                $form->setMyField("ROOMNO", "GRIDVISIBLE", '-1')
                    ->setMyField("HOUSETOTAL", "GRIDVISIBLE", '-1')
                    ->setMyField("HOUSEAREA", "GRIDVISIBLE", '-1');

                switch($reim_type)
                {
                    case 9:
                        $form->setMyField("AGENCY_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 10:
                        $form->setMyField("AGENCY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 11:
                        $form->setMyField("PROPERTY_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 12:
                        $form->setMyField("PROPERTY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 25:
                        $form->setMyField("OUT_REWARD", "GRIDVISIBLE", '-1');
                        break;
                }
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

            //����ɾ����ť�Ƿ���ʾ
            $form->EDITABLE = 0;
            $form->ADDABLE = 0;
            $form->DELABLE = 0;
            $form->SHOWCHECKBOX = -1;
            $form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>�༭����˰</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>�༭������ϸ</a>";
            $formHtml= $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
            $this->assign('form',$formHtml);
            $this->assign('paramUrl',$paramUrl);
            $this->display('reim_details');

            break;
            case "22":
            case "23":
            case "24":
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $uid = intval($_SESSION['uinfo']['uid']);
                $city = $this->channelid;
                $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
                $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

                //����LIST��ѯ����������
                $list_info = $reim_list_model->get_info_by_id($list_id, array('TYPE'));
                $reim_type = !empty($list_info[0]['TYPE']) ? intval($list_info[0]['TYPE']) : 0;

                $arrFeeScale = D('ReimbursementDetail')->getFeeScalesByListID($list_id);
                $cond_where = "LIST_ID = '".$list_id."' AND STATUS!=4";
                $form = $form->initForminfo(179)->where($cond_where);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                //�����շѱ�׼
                $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($arrFeeScale['1']), FALSE);
                //�н�Ӷ��
                $form->setMyField('AGENCY_REWARD', 'EDITTYPE', 22, FALSE);
                $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['2']), FALSE);
                //�ⲿ�ɽ�����
                $form->setMyField('OUT_REWARD', 'EDITTYPE', 22, FALSE);
                $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['3']), FALSE);
                // �ⲿ�ɽ�����
                 $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['3']), FALSE);

                //�н�ɽ���
                $form->setMyField('AGENCY_DEAL_REWARD', 'EDITTYPE', 22, FALSE);
                $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['4']), FALSE);
                //��ҵ�ɽ�����
                $form->setMyField('PROPERTY_DEAL_REWARD', 'EDITTYPE', 22, FALSE);
                $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['5']), FALSE);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                $form->setMyField("MONEY", "GRIDVISIBLE", '0')
                        ->setMyField("TYPE", "GRIDVISIBLE", '0')
                        ->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '0')
                        ->setMyField("ISKF", "GRIDVISIBLE", '0')
                        ->setMyField("STATUS", "GRIDVISIBLE", '0');
                switch ($reim_type)
                {
                    case 9:
                        $form->setMyField("AGENCY_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 10:
                    case 22:
                        $form->setMyField("AGENCY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                        $form->setMyField("MONEY", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISKF", "GRIDVISIBLE", '-1');
                        break;
                    case 11:
                        $form->setMyField("PROPERTY_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 12:
                    case 23:
                        $form->setMyField("PROPERTY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                        $form->setMyField("MONEY", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISKF", "GRIDVISIBLE", '-1');
                        break;
                    case 21:
                        $form->setMyField("MONEY", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISKF", "GRIDVISIBLE", '-1');
                    case 24:
                        $form->setMyField("OUT_REWARD", "GRIDVISIBLE", '-1');
                        $form->setMyField("MONEY", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISKF", "GRIDVISIBLE", '-1');
                        break;
                    default :
                        break;
                }

                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;

                $member_model = D('Member');

                //���û�Ա��Դ
                $source_arr = $member_model->get_conf_member_source_remark();
                $form = $form->setMyField('SOURCE', 'LISTCHAR',
                        array2listchar($source_arr), FALSE);

                //������
                $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);

                //����֤������
                $certificate_type_arr = $member_model->get_conf_certificate_type();
                $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',
                        array2listchar($certificate_type_arr), FALSE);

                /***��Ʊ״̬***/
                $conf_invoice_status = $member_model->get_conf_invoice_status_remark();
                if (in_array($reim_type, array(17, 22, 23, 24))) {
                    // ����Ǵӷ���Ӷ��������ģ�����ʾ��Ӷ�ķ�Ʊ״̬���ؿ�״̬
                    $form->setMyField('INVOICE_STATUS', 'LISTCHAR', array2listchar(array(
                        1 => 'δ��Ʊ',
                        2 => '���ֿ�Ʊ',
                        3 => '��ɿ�Ʊ'
                    )));
                } else {
                    $form = $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                        array2listchar($conf_invoice_status['INVOICE_STATUS']), FALSE);
                    $form->setMyField("PAYMENT_STATUS", "FORMVISIBILE", 0)
                        ->setMyField("PAYMENT_STATUS", "GRIDVISIBILE", 0);
                }


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
                $form->SHOWCHECKBOX = -1;
				$form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>�༭����˰</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>�༭������ϸ</a>";
                $formHtml = $form->getResult();
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
                $this->assign('form',$formHtml);
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');
                break;
            //������Ա�н��Ӷ
            case "17":
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $form->initforminfo(207);
                $form->SQLTEXT = "(SELECT m.REALNAME,
                                  m.HOUSETOTAL,
                                  m.TOTAL_PRICE,
                                  concatUnit(m.AGENCY_REWARD_AFTER, f1.Stype) AGENCY_REWARD_AFTER,
                                  m.PRJ_NAME,
                                  m.ID AS MEMBER_ID,
                                  m.MOBILENO,
                                  d.amount,
                                  d.percent,
                                  d.status,
                                  d.reim_list_id,
                                  d.reim_detail_id,
                                  d.id,
                                  c.INVOICE_STATUS,
                                  c.PAYMENT_STATUS,
                                  r.isfundpool,
                                  r.iskf,
                                  r.INPUT_TAX
                           FROM erp_commission_reim_detail d
                           LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                           LEFT JOIN erp_reimbursement_detail r ON r.id = d.reim_detail_id
                           LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
                           LEFT JOIN ERP_FEESCALE f1 ON f1.case_id = m.case_id
                           AND f1.amount = m.AGENCY_REWARD_AFTER
                           AND f1.SCALETYPE = 2
                           AND f1.ISVALID = -1
                           AND f1.MTYPE = 1)";
                $cond_where = "REIM_LIST_ID = '".$list_id."' AND STATUS!=4";
                $form->where($cond_where)
                    ->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1')
                    ->setMyField("PAYMENT_STATUS", "GRIDVISIBLE", '-1')
                    ->setMyField("PAYMENT_AMOUNT", "GRIDVISIBLE", '-1');
                $form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>�༭����˰</a>" . "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>�༭������ϸ</a>";
                $form->FKFIELD="";
                $form->SHOWCHECKBOX = -1;
                $form->SHOWSEQUENCE = 0;
                $form->EDITABLE=0;
                $form->DELABLE=0;
                $formHtml= $form->getResult();
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
                $this->assign('form',$formHtml);
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');
                break;
            //��Ŀ�³ɱ���䱨��
            case "13":
                $form->initForminfo(183);
                $form->SQLTEXT = "(SELECT A.BUSINESS_ID ID,A.STATUS,A.LIST_ID,A.INPUT_TAX,"
                  . "B.CASE_ID,B.PRODUCT_NAME,B.BRAND,B.MODEL,B.PRICE,B.NUM,B.FEE_ID, B.IS_FUNDPOOL,B.IS_KF,B.PUR_DATE,B.SUP_TYPE,"
                    . "B.NUM*B.PRICE SUM_MONEY FROM ERP_REIMBURSEMENT_DETAIL A ,ERP_COST_SUPPLEMENT B"
                    . " WHERE A.BUSINESS_ID = B.ID and A.TYPE=13)";
                $cond_where = "LIST_ID = ".$reim_list_id;
                $form->where($cond_where);
                //�Ƿ��ʽ��
                $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), FALSE);

                //�Ƿ�۷�
                $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), FALSE);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                //����״̬
                //״̬��ʶ
                $reim_status_remark = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_status_remark), FALSE);
                $form->SHOWCHECKBOX = -1;
				$form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>�༭����˰</a>" . "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>�༭������ϸ</a>";;
                           // . "<a id='save_input_tax'href='javascript:;' class='btn btn-info btn-sm'>�������˰</a>";
                $formHtml= $form->getResult();
                $this->assign('form',$formHtml);
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');
                break;
//            case 14:
//                break;
            case 15:
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $reim_details_status = $reim_detail_model->get_conf_reim_detail_status();
                $cond_where = "LIST_ID = '".$list_id."' AND STATUS != '".$reim_details_status['reim_detail_deleted']."'";
                $form = $form->initForminfo(198)->where($cond_where);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                //�Ƿ��ʽ��
                $form = $form->setMyField('ISFUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), FALSE);
                //�Ƿ�۷�
                $form = $form->setMyField('ISKF', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), FALSE);
                //$file1_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="1" href="javascript:void(0);">���ܱ�</a>';
               // $file2_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="2" href="javascript:void(0);">��ϸ��</a>';
				$file1_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="1" href="javascript:void(0);">���ܱ�</a>';
				$file2_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="2" href="javascript:void(0);">��ϸ��</a>';
                $file3_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="3" href="javascript:void(0);">��������ϸ</a>';
                $form->CZBTN = $file1_btn.' '.$file2_btn.' '.$file3_btn;
                //���ñ�����ϸ����
                $type_arr = $reim_type_model->get_reim_type();
                $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
                //������ϸ״̬
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
                //����˰ֻ��
                $form->setMyField('INPUT_TAX', 'READONLY', '-1', TRUE);
                //��������
                $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                        . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
				$form->SHOWCHECKBOX = -1;
				$form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>�༭����˰</a>" . "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>�༭������ϸ</a>";;
                            //. "<a id='save_input_tax'href='javascript:;' class='btn btn-info btn-sm'>�������˰</a>";
                $formHtml = $form->getResult();
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
                $this->assign('form', $formHtml);
                $this->display('reim_details');
        }
    }


    /**
    +----------------------------------------------------------
    * ����ҳ��������չʾ�б�
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function loanMoney()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(209);

        //��ȡ����ID
        $listId = isset($_GET['parentchooseid'])?intval($_GET['parentchooseid']):0;
        $rlId = isset($_GET['RLID'])?intval($_GET['RLID']):0;

        //�������SQL
        $form->SQLTEXT = '(SELECT R.ID AS RLID,L.ID,L.CITY_ID,R.MONEY AS LOANMONEY,R.REIMID,L.PAYTYPE,T.NAME AS CITYNAME,P.ID AS PID,P.CONTRACT,L.STATUS,L.AMOUNT,L.REPAY_TIME,L.UNREPAYMENT,L.RESON,L.APPLICANT,U.NAME AS USERNAME,APPDATE FROM ERP_LOANAPPLICATION L
LEFT JOIN ERP_CITY T ON L.CITY_ID = T.ID
LEFT JOIN ERP_PROJECT P ON L.PID = P.ID
LEFT JOIN ERP_USERS U ON L.APPLICANT = U.ID
RIGHT JOIN ERP_REIMLOAN R ON L.ID = R.LOANID WHERE R.REIMID = ' . $listId . ')';

        //�������
        $form->PKFIELD = 'RLID';
        $form->PKVALUE = $rlId;

        $form->ADDABLE = 0;
        $form->EDITABLE = 0;
        $form->DELABLE = 0;
        $form->GABTN = "";
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
        $this->assign('form',$formHtml);
        $this->display("loan_money");
    }


    /**
    +----------------------------------------------------------
    * �����������ݽ��жԱ� ƥ�䵽�����Զ�ȷ���տ�
    +----------------------------------------------------------
    * @param $file Ҫ��ȡ���ļ�
    +----------------------------------------------------------
    * @return $data
    +----------------------------------------------------------
    */
    public function importBankData()
    {

        //���ص������ʽ
        $return = array(
            'status' => false,
            'msg' => '',
            'data' => null,
        );

        if($_FILES)
        {
            if($_FILES["upfile"]["size"] > 5000000)
            {
                $return['msg'] = g2u("�ļ�����ע�⣺�ļ���С���ܳ��� 5M !");
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:������������:' . $return["msg"] . ':�ɹ�', serialize($_FILES['upfile']));
                die(json_encode($return));
            }

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
                    echo 'no Excel !';
                    userLog()->writeLog('', $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:������������:��������ؼ�¼:ʧ��', serialize($_FILES['upfile']));
                    return ;
                }
            }

            /*****��ȡexcel������*****/
            $objPHPExcel = $PHPReader->load($file,"UTF-8");
            $currentSheet = $objPHPExcel->getSheet(0);
            $allColumn = $currentSheet->getHighestColumn();

            /**ȡ��һ���ж�����*/
            $allRow = $currentSheet->getHighestRow();

            //�ж�֧������¼��
            if($allRow>102){
                $return['msg'] = g2u('�Բ������֧�ֵ���100����¼��');
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:������������:���֧�ֵ���100����¼:ʧ��', serialize($_FILES['upfile']));
                die(json_encode($return));
            }

            $paylist =  D("Erp_member_payment");
            $member_model = D('Member');

            //�̻����
            $shbh =  trim($currentSheet->getCellByColumnAndRow((ord(A) - 65),2)->getValue());
            $shbh = substr(trim($shbh),-15);

            $data = array();
            for($currentRow = 5; $currentRow <= $allRow; $currentRow++){
                //ʱ��
                $date = $currentSheet->getCell("B".$currentRow)->getValue();
                $time = $currentSheet->getCell("C".$currentRow)->getValue();
                $tradetime = $date. ' ' .date('H:i:s', $time);
                //�ն˺�
                $terminalnum = $currentSheet->getCell("D".$currentRow)->getValue();
                //���׽��
                $trademoney = $currentSheet->getCell("E".$currentRow)->getValue();
                //���ײο���
                $alljsnum = trim($currentSheet->getCell("H".$currentRow)->getValue());
                //6λ������
                $jsnum = substr($alljsnum,-6);
                //��������
                $tradetype = $currentSheet->getCell("I".$currentRow)->getValue();
                $tradetype = iconv("utf-8","gbk",$tradetype);
                //���ź�4λ
                $allcardnum = $currentSheet->getCell("J".$currentRow)->getValue();//����
                $cardnum = substr($allcardnum,-4);
                //��������
                $cardbank = $currentSheet->getCell("K".$currentRow)->getValue();
                $cardbank = iconv("utf-8","gbk",$cardbank);
                //������
                $cardtype = $currentSheet->getCell("L".$currentRow)->getValue();
                $cardtype = iconv("utf-8","gbk",$cardtype);

                //�����������
                if($jsnum && $cardnum) {
                    $data[] = array("shbh" => $shbh,
                        "cardnum" => $cardnum,
                        "jsnum" => $jsnum,
                        "trademoney" => $trademoney,
                        "tradetime" => $tradetime,
                        "terminalnum" => $terminalnum,
                        "alljsnum" => $alljsnum,
                        "tradetype" => $tradetype,
                        "allcardnum" => $allcardnum,
                        "cardbank" => $cardbank,
                        "cardtype" => $cardtype,
                    );
                }
            }

            $i = 0;
            $error_str = '';

            //ѭ����������
            foreach($data as $key=>$val)
            {
                $res = $paylist->where("substr(CVV2,-4) = '{$val["cardnum"]}' and RETRIEVAL = '{$val["jsnum"]}' and TRADE_MONEY = {$val["trademoney"]} AND MERCHANT_NUMBER = '{$val["shbh"]}' AND STATUS = 0")
                    ->field(array("ID","MID"))->select();

                if(!$res){
                    $error_str .= "��" . ($key+1) . "��,δƥ�䵽��صĸ�����ϸ, �����̻���ţ����ź���λ����λ�����ţ�������Ƿ���д��ȷ~<br />";
                    continue;
                }

                //������ϸID
                $id = $res[0]["ID"];
                //�û�ID
                $mid = $res[0]["MID"];

                //���жϸø�����ϸ��״̬
                $ret_member_payment = M("erp_member_payment")
                    ->field("STATUS")->where("ID = " . $id)->find();

                if($ret_member_payment['STATUS'] == 1)
                {
                    $error_str .= "��" . ($key+1) . "��,����������ϸ�������Ѿ�ȷ��~<br />";
                    continue;
                }

                //�������δȷ��״̬
                $this->model->startTrans();

                $sql = "update ERP_MEMBER_PAYMENT set STATUS = 1 where ID=" . $id;
                $r[$i] = $this->model->execute($sql);
                //�޸Ķ�Ӧ��Ա״̬

                //�жϸû�Ա�Ƿ���δȷ�ϵ��տ���
                $pay_info = $paylist->where("MID = " . $mid . " AND STATUS = 0")->field("ID")->select();
                $member_info = $member_model->get_info_by_ids($mid, array("UNPAID_MONEY"));

                $unpaidmoney = $member_info[0]['UNPAID_MONEY'];

                //�û�Ա������δȷ�Ͽ��״̬�޸�Ϊ����ȷ��
                if ($pay_info[0]["ID"] || $unpaidmoney>0) {
                    $sql = "UPDATE ERP_CARDMEMBER SET FINANCIALCONFIRM = 2 WHERE ID = " . $mid;
                    $up_num = $this->model->execute($sql);
                }
                //�û�Ա������δȷ�Ͽ��״̬�޸�Ϊ��ȷ��
                else {
                    $sql = "UPDATE ERP_CARDMEMBER SET FINANCIALCONFIRM = 3 WHERE ID = " . $mid;
                    $up_num = $this->model->execute($sql);
                }

                //����ȷ������
                $res_income = $this->add_income_after_financial_confirm(2, 0, array(), array($id));

                if ($up_num && $r[$i] && $res_income) {
                    $i += 1;
                    $this->model->commit();
                    $conftime = date("Y-m-d H:i:s");
                    $confuser = $_SESSION["uinfo"]["uname"];
                    $dealres = 1;
                    //��¼����������־
                    $sql = "insert into ERP_BANKDATA_LOG(SHBH,TRADE_TIME,TERMINAL_NUM,TRADE_MONEY,"
                        . "JS_NUM,TRADE_TYPE,CARD_NUM,CARD_BANK,CARD_TYPE,CONFIRM_TIME,CONFIRM_USER,"
                        . "DEAL_RESULT,MID) values('" . $val["shbh"] . "',to_date('" . $val["tradetime"] . "','yyyy/mm/dd'),'" . $val["terminalnum"]
                        . "'," . $val["trademoney"] . ",'" . $val["alljsnum"] . "','" . $val["tradetype"] . "','" . $val["allcardnum"]
                        . "','" . $val["cardbank"] . "','" . $val["cardtype"] . "',to_date('" . $conftime . "','yyyy/mm/dd'),'" . $confuser . "',"
                        . $dealres . "','" . $mid . ")";
                    $up_num1 = $this->model->execute($sql);
                }
                else
                {
                    $this->model->rollback();
                    $conftime = "";
                    $confuser = "";
                    $dealres = 0;
                    $sql = "insert into ERP_BANKDATA_LOG(SHBH,TRADE_TIME,TERMINAL_NUM,TRADE_MONEY,"
                        . "JS_NUM,TRADE_TYPE,CARD_NUM,CARD_BANK,CARD_TYPE,CONFIRM_TIME,CONFIRM_USER,"
                        . "DEAL_RESULT,MID) values('" . $val["shbh"] . "','" . $val["tradetime"] . "','" . $val["terminalnum"]
                        . "'," . $val["trademoney"] . ",'" . $val["alljsnum"] . "','" . $val["tradetype"] . "','" . $val["allcardnum"]
                        . "','" . $val["cardbank"] . "','" . $val["cardtype"] . "','" . $conftime . "','" . $confuser . "',"
                        . $dealres . "','" . $mid . ")";
                    $this->model->execute($sql);

                    $error_str .= "��" . ($key+1) . "��,�Ա�ʧ��~<br />";
                    continue;
                }
            }

            //���շ���
            if($i>0){
                $return["status"] = 1;
                $return["msg"] = "�ף��˴ζԱȹ�ƥ���� ".$i ."������<br />";
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:������������:' . $return["msg"] . ':�ɹ�', serialize($_FILES['upfile']));
            }
            else
            {
                $return["status"] = 0;
                $return["msg"] = "�Բ����ף��Ա�ʧ��<br />";
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:������������:' . $return["msg"] . ':ʧ��', serialize($_FILES['upfile']));
            }
            if($error_str) {
                $return["msg"] .= "�˴ζԱȻ������������⣺<br />" . $error_str;
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], 'Ԥ��ȷ��:������������:' . $return["msg"] . ':ʧ��', serialize($_FILES['upfile']));
            }
            $return["msg"] = g2u($return["msg"]);
            die(json_encode($return));

        }else{
            $this->display('financial_importbankdata');
        }
    }

    /**
    +----------------------------------------------------------
    * �˿����
    +----------------------------------------------------------
    * @param  none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function refundProgress()
    {
        $this->model = new model();
        vendor("Oms.Form");
        $form = new Form();
        $sql = "select b.maxstep,b.addtime,b.status,c.flowtype,e.name from erp_flowset a 
                left join erp_flows b on a.id= b.flowsetid 
                left join erp_flowtype c on a.flowtype = c.id
                left join erp_users e on e.id=b.adduser where c.id=10";
        $flow = $this->model->query($sql);
        $totalrecode = count($flow);
        $totalrecode = ($totalrecode > 0 )?$totalrecode : 0;
        $page = ($_REQUEST['page']>0)?intval($_REQUEST['page']):1;
        $pageSize = $_REQUEST['pageSize']?intval($_REQUEST['pageSize']):20;
        $pages = ceil($totalrecode/$pageSize);
        $pagehtml = $form->getPage($totalrecode,$page,$pageSize,$pages);
        $this->assign("flow",$flow);
        $this->assign("pagehtml",$pagehtml);
        $this->display("financial_refund");
    }


    /**
    +----------------------------------------------------------
    * ������Ŀ����
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function prjSummary()
    {
       if($_REQUEST["save"]){//���񱸰�
           $sql = "update ERP_HOUSE set ISRECORD=2 where PRO_NAME='".$_SESSION["prjname"]."' and ISRECORD !=2";
           $res = $this->model->execute($sql);
           if($res){
               js_alert("�����ɹ�","",0);
           }else{
               js_alert("����Ŀ�Ѿ�����","",0);
           }

       }
       if($_REQUEST["flowid"]){
           $_SESSION["prjname"] = $_REQUEST["prjname"];
           Vendor('Oms.workflow');
           $flow = new workflow();
           $ID = $_REQUEST['flowid'];
           $html = $flow->createHtml($ID);
           $this->assign('html',$html);

       }
       Vendor('Oms.Form');
       $form = new Form();
       $form =  $form->initForminfo(131)->getResult();
       $this->assign('form',$form);
       $this->display('financial_prjsummary');
    }

    /**
    +----------------------------------------------------------
    * ҵ��Ʊ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function yw_invoice()
    {

        Vendor('Oms.Form');
        $form = new Form();

        $where = "IS_NEED_INVOICE = 1 AND CITY_ID = ". $this->channelid;
        $children = array(
                       array("��Ʊ��¼",U("/Financial/InvoiceRecord&from=invoice",$this->_merge_url_param)),
                       array("�ؿ��¼",U("/Financial/refundRecords&from=invoice",$this->_merge_url_param)),
                   );
        $form->initForminfo(124);

        //����formsql
        $form->SQLTEXT = "(SELECT A.ID,A.CASE_ID,A.CONTRACT_NO,A.SIGN_USER,A.COMPANY,A.STATUS,B.SCALETYPE,C.PROJECTNAME,C.ID PROJECT_ID,to_char(A.START_TIME,'YYYY-MM-DD') START_TIME,to_char(A.END_TIME,'YYYY-MM-DD')
                    END_TIME,to_char(A.PUB_TIME,'YYYY-MM-DD') PUB_TIME,A.CONF_TIME,A.MONEY,A.IS_NEED_INVOICE,A.CITY_ID,A.INCOME_TYPE
                    FROM ERP_INCOME_CONTRACT A
                    LEFT JOIN ERP_CASE B ON A.CASE_ID=B.ID
                    LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID=C.ID
                    WHERE (C.PSTATUS=3 OR (C.ASTATUS=2 OR C.ASTATUS=4)) AND B.SCALETYPE !=7 AND B.SCALETYPE !=1)";

        //���ñ���ť
        $form->DELABLE = 0;
        $form->ADDABLE = 0;


        // ����
        $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_CITY', FALSE);

        //����form����
        $formHtml = $form ->setChildren($children)
            ->where($where)
            ->getResult();

        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->display("yw_invoice_refund");
    }

     /**
    +----------------------------------------------------------
    * ҵ��Ʊ�ؿ�
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function yw_refund()
    {
        Vendor('Oms.Form');
        $form = new Form();

        $where = "CITY_ID = ".$this->channelid;

        $children = array(
                        array("�ؿ��¼",U("/Financial/refundRecords&from=refund",$this->_merge_url_param)),
                        array("��Ʊ��¼",U("/Financial/InvoiceRecord&from=refund",$this->_merge_url_param)),
                    );

        $form->initForminfo(124);

        //����formsql
        $form->SQLTEXT = "(SELECT A.ID,A.CASE_ID,A.CONTRACT_NO,A.SIGN_USER,A.COMPANY,A.STATUS,B.SCALETYPE,C.PROJECTNAME,C.ID PROJECT_ID,to_char(A.START_TIME,'YYYY-MM-DD') START_TIME,to_char(A.END_TIME,'YYYY-MM-DD')
                    END_TIME,to_char(A.PUB_TIME,'YYYY-MM-DD') PUB_TIME,A.CONF_TIME,A.MONEY,A.IS_NEED_INVOICE,A.CITY_ID,A.INCOME_TYPE
                    FROM ERP_INCOME_CONTRACT A
                    LEFT JOIN ERP_CASE B ON A.CASE_ID=B.ID
                    LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID=C.ID
                    WHERE (C.PSTATUS=3 OR (C.ASTATUS=2 OR C.ASTATUS=4)) AND B.SCALETYPE !=7 AND B.SCALETYPE !=1)";


        $form->DELABLE = 0;
        $form->ADDABLE = 0;

        //��ȡҵ������
        $businessclass = M('erp_businessclass')->select();
        $scaletype_arr = array();
        foreach ($businessclass as $key=>$val) {
            $scaletype_arr[$val['ID']] = $val['YEWU'];
        }
        $form->setMyField('SCALETYPE', 'LISTCHAR', array2listchar($scaletype_arr), FALSE);
        $form->setMyField('CITY_ID','LISTSQL','SELECT ID,NAME FROM ERP_CITY WHERE ISVALID = -1', FALSE);

        $form = $form ->setChildren($children)
            ->setMyField("PROJECT_ID", "GRIDVISIBLE", "-1")
            ->setMyField("SCALETYPE", "GRIDVISIBLE", "-1")
            ->where($where)
            ->getResult();

        $this->assign('form',$form);
        $this->display("yw_refund");
    }

    /**
    +----------------------------------------------------------
    * ҵ��Ʊ��¼
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function InvoiceRecord()
    {
       $billing_model = D("BillingRecord");
       $contract_model = D("Contract");
       $billing_status_remark = $billing_model->get_invoice_status_remark();
       $contract_id = $_REQUEST["parentchooseid"];
       $contract_info = $contract_model->get_contract_info_by_id($contract_id,array("CASE_ID"));
       $case_id = $contract_info[0]["CASE_ID"];

       Vendor('Oms.Form');
       $form = new Form();
       $form->initForminfo(136);
       $form->orderField = "STATUS ASC";

        $from = isset($_GET['from'])?trim($_GET["from"]):'';
        $act = isset($_GET['act'])?trim($_GET["act"]):'';

        //��ȡ��Ʊ״ֵ̬
        $invoice_status = D("BillingRecord")->get_invoice_status();

        $invoice_show = '';
        if($from == 'invoice') {
            switch ($act) {
                case 'change_invoice':
                    $form->GABTN = '<a onclick="confirm_change_invoice()" href="javascript:;" class="btn btn-info btn-sm">ȷ�ϻ�Ʊ</a>';
                    $invoice_show = $invoice_status['change_vote'];
                    break;
                case 'refund_invoice':
                    $form->GABTN = '<a onclick="confirm_refund_invoice()" href="javascript:;" class="btn btn-info btn-sm">ȷ����Ʊ</a>';
                    $invoice_show = $invoice_status['refund_vote'];
                    break;
                default:
                    $form->GABTN = '<a onclick="save_data()" href="javascript:;" class="btn btn-info btn-sm">��������</a>'
                        . '<a onclick="do_invoice()" href="javascript:;" class="btn btn-info btn-sm">ȷ�Ͽ�Ʊ</a>';
                    $invoice_show = "3,4,6,7,8,9";
                    break;
            }
        }

        //����ǻؿ����
        if($from == 'refund')
            $invoice_show = "3,4,6,7,8,9";

        $form->EDITABLE = 0;
        $form->DELABLE = 0;
        $form->ADDABLE = 0;
        $where = "STATUS IN($invoice_show) AND CASE_ID = '" . $case_id . "'";

        $form = $form->where($where)
            ->setMyField("TAX", "GRIDVISIBLE", "-1")
            ->setMyField("STATUS", "LISTCHAR", array2listchar($billing_status_remark))
            ->setMyField("INVOICE_MONEY", "FIELDMEANS", "˰�Ѻϼ�");

//        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
        $formHtml = $form->getResult(); //��ʾ��Ӳ���¶�Ӧ�Ŀ�Ʊ��¼
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign("case_id", $case_id);
        //��Ϊ
        $this->assign("act", $act);
        $this->assign('form', $formHtml);

        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('financial_invoice_records');
    }

    /**
    +----------------------------------------------------------
     * ҵ��Ʊ
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function business_change_invoice(){
        Vendor('Oms.Form');
        $form = new Form();

        $form->initForminfo(124);

        $formsql  = <<<INVOICE_SQL
        (SELECT F.*,C.SCALETYPE,P.PROJECTNAME,P.ID PROJECT_ID FROM
        (
        SELECT DISTINCT B.ID,B.CASE_ID,B.CONTRACT_NO,B.SIGN_USER,B.COMPANY,B.STATUS,to_char(B.START_TIME,'YYYY-MM-DD') START_TIME,to_char(B.END_TIME,'YYYY-MM-DD') END_TIME,to_char(B.PUB_TIME,'YYYY-MM-DD') PUB_TIME,B.CONF_TIME,B.MONEY,B.CITY_ID FROM ERP_BILLING_RECORD A INNER JOIN ERP_INCOME_CONTRACT B ON A.CONTRACT_ID = B.ID WHERE A.STATUS = %d
        ) F
        LEFT JOIN ERP_CASE C ON F.CASE_ID=C.ID
        LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID
        WHERE (P.PSTATUS=3 OR (P.ASTATUS=2 OR P.ASTATUS=4)) AND C.SCALETYPE !=7 AND C.SCALETYPE !=1)
INVOICE_SQL;

        //��Ʊ״̬
        $invoice_status = D("BillingRecord")->get_invoice_status();
        $formsql = sprintf($formsql,$invoice_status['change_vote']);

        //����formsql
        $form->SQLTEXT = $formsql;

        $children = array(
            array("���뻻Ʊ��¼",U("/Financial/InvoiceRecord&from=invoice&act=change_invoice",$this->_merge_url_param)),
        );

        $where = " CITY_ID = ". $this->channelid;

        //���ñ���ť
        $form->DELABLE = 0;
        $form->ADDABLE = 0;

        // ����
        $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_CITY', FALSE);

        //����form����
        $formHtml = $form ->setChildren($children)
            ->where($where)
            ->getResult();

        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->display("business_change_invoice");
    }

    /**
    +----------------------------------------------------------
     * ҵ����Ʊ
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function business_refund_invoice(){
        Vendor('Oms.Form');
        $form = new Form();

        $form->initForminfo(124);

$formsql  = <<<INVOICE_SQL
        (SELECT F.*,C.SCALETYPE,P.PROJECTNAME,P.ID PROJECT_ID FROM
        (
        SELECT DISTINCT B.ID,B.CASE_ID,B.CONTRACT_NO,B.SIGN_USER,B.COMPANY,B.STATUS,to_char(B.START_TIME,'YYYY-MM-DD') START_TIME,to_char(B.END_TIME,'YYYY-MM-DD') END_TIME,to_char(B.PUB_TIME,'YYYY-MM-DD') PUB_TIME,B.CONF_TIME,B.MONEY,B.CITY_ID FROM ERP_BILLING_RECORD A INNER JOIN ERP_INCOME_CONTRACT B ON A.CONTRACT_ID = B.ID WHERE A.STATUS = %d
        ) F
        LEFT JOIN ERP_CASE C ON F.CASE_ID=C.ID
        LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID
        WHERE (P.PSTATUS=3 OR (P.ASTATUS=2 OR P.ASTATUS=4)) AND C.SCALETYPE !=7 AND C.SCALETYPE !=1)
INVOICE_SQL;

        //��Ʊ״̬
        $invoice_status = D("BillingRecord")->get_invoice_status();
        $formsql = sprintf($formsql,$invoice_status['refund_vote']);

        //����formsql
        $form->SQLTEXT = $formsql;

        //���ñ���ť
        $form->DELABLE = 0;
        $form->ADDABLE = 0;

        $where = " CITY_ID = ". $this->channelid;
        // �����б�
        $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_CITY', FALSE);

        $children = array(
            array("������Ʊ��¼",U("/Financial/InvoiceRecord&from=invoice&act=refund_invoice",$this->_merge_url_param)),
        );

        //����form����
        $formHtml = $form ->setChildren($children)
            ->where($where)
            ->getResult();

        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->display("business_refund_invoice");
    }

    /**
    +----------------------------------------------------------
     * ȷ�ϻ�Ʊ
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function confirm_change_invoice(){

        /***��������Ʊһ��Ʊ�����***/

        //���ؽ����
        $result = array(
            'status' => false,
            'msg'=>'',
            'data'=>null,
        );

        $invoice_ids = $_POST['invoiceid'];
        $invoice_time = $_POST['invoice_time'];
        $invoice_no = $_POST['invoice_no'];
		$invoice_no_old = $_POST['invoice_no_old'];
        $invoice_biz_type = $_POST['invoice_biz_type'];

        $error_str = '';

        if(count($invoice_ids)<1) {
            $result['msg'] = g2u('��,������ѡ��һ����¼��');
            die(@json_encode($result));
        }

        foreach($invoice_ids as $key=>$val){
            if(!$invoice_time[$key] || !$invoice_no[$key] || !$invoice_biz_type[$key])
                $error_str .= "��" . ($key+1) . "������Ʊʱ�䡢��Ʊ��Ż��߷�Ʊ����δ��д!<br />";
			if($invoice_no_old[$key] != $invoice_no[$key]){
				$sqll = "select * from  ERP_BILLING_RECORD  A left join ERP_CASE B on A.CASE_ID=B.ID left join ERP_PROJECT C on C.ID=B.PROJECT_ID  where A.INVOICE_NO='".$invoice_no[$key]."' and C.CITY_ID=".$this->channelid;
				$resss = M()->query($sqll);
				if($resss){
					$error_str .= "��" . ($key+1) . "���� ��Ʊ����ڵ�ǰ�������Ѵ���!<br />";
				}
			}
        }

        if($error_str)
        {
            $result['msg'] = g2u($error_str);
            die(json_encode($result));
        }
 
        $case_model = D("ProjectCase");
        $billing_model = D("BillingRecord");
        $contract_model = D("Contract");

        //��Ʊ״̬
        $invoice_status = D("BillingRecord")->get_invoice_status();

        //ҵ��
        $this->model->startTrans();

        load("@.contract_common");
        foreach($invoice_ids as $key=>$val)
        {
            //��ȡ��Ʊ��Ϣ
            $billing_info = $billing_model->get_info_by_id($val,array("INVOICE_TIME","INVOICE_NO","CONTRACT_ID","CASE_ID", "INVOICE_BIZ_TYPE"));

            $case_id =  $billing_info[0]['CASE_ID'];
            $contract_id =  $billing_info[0]['CONTRACT_ID'];

            $contract_info =  $contract_model->get_contract_info_by_id($contract_id,array("CONTRACT_NO"));

            /*-------------------���븺ֵ��¼----------------------*/

            //��ȡҵ������
            $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
            $scale_type = $case_info[0]["SCALETYPE"];

            //����������
            $is_positive = false;
            $res_income_negative = $this->add_income_after_financial_invoice(array($contract_id),$scale_type,$val,$is_positive,true);

            if(!$res_income_negative)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //���»���Ʊ��״̬
            $up_val['STATUS'] = $invoice_status['have_change_voted'];
            $update_id = $this->model->table("erp_billing_record")->where("ID = " . $val)->save($up_val);

            if(!$update_id)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //���ݷ�ƱID�ҵ���Ʊ��Ϣ
            $billing_info = $billing_model->get_info_by_id($val,array("INVOICE_MONEY","TAX","INVOICE_NO","REMARK","INVOICE_TIME", "INVOICE_BIZ_TYPE"));

            $format_date = oracle_date_format($billing_info[0]["INVOICE_TIME"]);
            $data_arr["date"] = $format_date?$format_date:$billing_info[0]["INVOICE_TIME"];
            $data_arr["money"] = -$billing_info[0]["INVOICE_MONEY"];

            //��Ʊ˰��
            $taxrate = get_taxrate_by_citypy($this->user_city_py);
            $data_arr["tax"] = round((0-$billing_info[0]["INVOICE_MONEY"])/(1+$taxrate) * $taxrate,2);

            $data_arr["invono"] = $billing_info[0]["INVOICE_NO"];
            //Ӳ�� 1  ��Ӳ�� 2
//            $data_arr["type"] = $scale_type==3?1:2;
            $data_arr["type"] = $billing_info[0]["INVOICE_BIZ_TYPE"];
            $data_arr["note"] = urlencode($billing_info[0]["REMARK"]);
            $data_arr["city"] = $this->user_city_py;
            $data_arr["contractnum"] = $contract_info[0]["CONTRACT_NO"];

            //ͬ����Ʊ���ݵ���ͬϵͳ
            $save_result_negative = saveInvoice2Con($data_arr);

            if(!$save_result_negative)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }
            /*-------------------���븺ֵ��¼----------------------*/


            /*-------------------������¼�¼----------------------*/

            $insert_arr = $this->model->table("erp_billing_record")
                ->field("CASE_ID,CONTRACT_ID,INVOICE_NO,USER_ID,CREATETIME,REMARK,APPLY_USER_ID,TAX,STATUS,INVOICE_TIME,INVOICE_TYPE,FLOW_ID,INVOICE_MONEY,INVOICE_CLASS,INVOICE_BIZ_TYPE")
                ->where("ID = " .  $val)
                ->find();

            $insert_arr['INVOICE_MONEY'] = $insert_arr['INVOICE_MONEY'];
            $insert_arr['REMARK'] = '��ţ�' . $val . '���뻻��Ʊ';
            $insert_arr['INVOICE_NO'] = $invoice_no[$key];
            $insert_arr['INVOICE_TIME'] = $invoice_time[$key];
            $insert_arr['INVOICE_BIZ_TYPE'] = $invoice_biz_type[$key];
            $insert_arr['STATUS'] =  $invoice_status['have_invoiced'];
            unset($insert_arr['NUMROW']);

            $insert_id = $this->model->table("erp_billing_record")->add($insert_arr);

            if(!$insert_id)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //����Ƿ�����Ա��Ʊ �޸ķ�����Ա��״̬����д��Ʊ����
            $up_dis_member_success = true;

            if( $scale_type == 2 )
            {
                /*$member_distribution_model = D("MemberDistribution");

                $cond_where = "RELATE_INVOICE_ID = ".$val;

                $update_arr['INVOICE_STATUS'] = 2;
                $update_arr['RELATE_INVOICE_ID'] = $insert_id;
                $update_arr['INVOICE_NO'] = $invoice_no[$key];
				
                //���·���״̬
                $up_dis_member_num =$member_distribution_model->update_info_by_cond($update_arr, $cond_where);*/
				$temp = array();
				$temp['BILLING_RECORD_ID'] =  $insert_id;
				$temp['INVOICE_NO'] =  $invoice_no[$key];

				
				$up_dis_member_num = M("Erp_commission_invoice_detail")->where("BILLING_RECORD_ID=$val")->save($temp);

                if(!$up_dis_member_num)
                    $up_dis_member_success = false;
            }

            if(!$up_dis_member_success)
            {
                //$this->model->rollback();
                //die(@json_encode($result));
            }

            //��������
            $res_income_positive = $this->add_income_after_financial_invoice(array($contract_id),$scale_type,$insert_id,true,true);

            if(!$res_income_positive)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //���ݷ�ƱID�ҵ���Ʊ��Ϣ
            $billing_info = $billing_model->get_info_by_id($insert_id,array("INVOICE_MONEY","TAX","INVOICE_NO","REMARK","INVOICE_TIME", "INVOICE_BIZ_TYPE"));

            $format_date = oracle_date_format($billing_info[0]["INVOICE_TIME"]);
            $data_arr["date"] = $format_date?$format_date:$billing_info[0]["INVOICE_TIME"];
            $data_arr["money"] = $billing_info[0]["INVOICE_MONEY"];

            //��Ʊ˰��
            $taxrate = get_taxrate_by_citypy($this->user_city_py);
            $data_arr["tax"] = round($billing_info[0]["INVOICE_MONEY"]/(1+$taxrate) * $taxrate,2);

            $data_arr["invono"] = $billing_info[0]["INVOICE_NO"];
            //Ӳ�� 1  ��Ӳ�� 2
//            $data_arr["type"] = $scale_type==3?1:2;
            $data_arr["type"] = $billing_info[0]["INVOICE_BIZ_TYPE"];
            $data_arr["note"] = urlencode("���뻻��Ʊ");
            $data_arr["city"] = $this->user_city_py;
            $data_arr["contractnum"] = $contract_info[0]["CONTRACT_NO"];

            $save_result_positive = saveInvoice2Con($data_arr);//ͬ����Ʊ���ݵ���ͬϵͳ

            if(!$save_result_positive)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }
            /*-------------------������¼�¼----------------------*/
        }

        $this->model->commit();
        $result["status"] = true;
        $result["msg"] = g2u("��Ʊ�ɹ���");
        die(json_encode($result));

    }

    /**
    +----------------------------------------------------------
     * ȷ����Ʊ
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function confirm_refund_invoice(){
        /***��������Ʊһ����Ʊ�����***/

        //���ؽ����
        $result = array(
            'status' => false,
            'msg'=>'',
            'data'=>null,
        );

        $invoice_ids = $_POST['invoiceid'];

        if(count($invoice_ids)<1) {
            $result['msg'] = g2u('������ѡ������һ����¼��');
            die(@json_encode($result));
        }

        $case_model = D("ProjectCase");
        $billing_model = D("BillingRecord");
        $contract_model = D("Contract");

        //��Ʊ״̬
        $invoice_status = D("BillingRecord")->get_invoice_status();

        //ҵ��
        $this->model->startTrans();

        load("@.contract_common");
        foreach($invoice_ids as $key=>$val)
        {
            //��ȡ��Ʊ��Ϣ
            $billing_info = $billing_model->get_info_by_id($val,array("INVOICE_TIME","INVOICE_NO","CONTRACT_ID","CASE_ID"));

            $case_id =  $billing_info[0]['CASE_ID'];
            $contract_id =  $billing_info[0]['CONTRACT_ID'];

            $contract_info =  $contract_model->get_contract_info_by_id($contract_id,array("CONTRACT_NO"));

            /*------------------���¼�¼----------------------*/

            //��ȡҵ������
            $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
            $scale_type = $case_info[0]["SCALETYPE"];

            //����������
            $is_p = false;
            $res_income_negative = $this->add_income_after_financial_invoice(array($contract_id),$scale_type,$val,$is_p,true);

            if(!$res_income_negative)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //����Ƿ���ҵ��
            $up_dis_member_success = true;
            if( $scale_type == 2 )
            {
               /*$member_distribution_model = D("MemberDistribution");

                $cond_where = "RELATE_INVOICE_ID = ".$val;
                $update_arr['INVOICE_STATUS'] = 4;

                //���·���״̬
                $up_dis_member_num =$member_distribution_model->update_info_by_cond($update_arr, $cond_where);*/

				$temp = array();
				//$temp['BILLING_RECORD_ID'] =  $insert_id;
				//$temp['INVOICE_NO'] =  $invoice_no[$key];
				$temp['INVOICE_STATUS'] = 9;

				
				$up_dis_member_num = M("Erp_commission_invoice_detail")->where("BILLING_RECORD_ID=$val")->save($temp);

                if(!$up_dis_member_num)
                    $up_dis_member_success = false;
				if($up_dis_member_num){
					$comm_invo_detail = M("Erp_commission_invoice_detail")->where("BILLING_RECORD_ID=$val")->select();
					foreach($comm_invo_detail as $cval){
						$POST_COMMISSION_ID = $cval['POST_COMMISSION_ID'];
						$comlist = M("Erp_commission_invoice_detail")->where("POST_COMMISSION_ID=$POST_COMMISSION_ID")->select();
						$invoice_nums = 0;
						$b_counts = count($comlist);
						$tttemp = array();
						foreach($comlist as $vall){
							if($vall['INVOICE_STATUS']==3)
								$invoice_nums++;
							
						}
						if($invoice_nums==0 ){//δ��Ʊ
							$tttemp['INVOICE_STATUS'] = 1;
							
						}elseif($invoice_nums<$b_counts){//���ֿ�Ʊ
							$tttemp['INVOICE_STATUS'] = 2;
						}elseif($invoice_nums==$b_counts){//��Ʊ
							$tttemp['INVOICE_STATUS'] = 3;
						}
						$ressss = M("Erp_post_commission")->where("ID=$POST_COMMISSION_ID")->save($tttemp);
						if(!$ressss)
						$up_dis_member_success2 = false;
					}

					

				}

            }

            if(!$up_dis_member_success)
            {
               // $this->model->rollback();
               // die(@json_encode($result));
            }
			 if(!$up_dis_member_success2)
            {
               // $this->model->rollback();
                //die(@json_encode($result));
            }

            //����״̬
            $up_val['STATUS'] = $invoice_status['have_refund_voted'];
            $update_id = $this->model->table("erp_billing_record")->where("ID = " . $val)->save($up_val);

            if(!$update_id)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //���ݷ�ƱID�ҵ���Ʊ��Ϣ
            $billing_info = $billing_model->get_info_by_id($val,array("INVOICE_MONEY","TAX","INVOICE_NO","REMARK","INVOICE_TIME", "INVOICE_BIZ_TYPE"));

            $format_date = oracle_date_format($billing_info[0]["INVOICE_TIME"]);
            $data_arr["date"] = $format_date?$format_date:$billing_info[0]["INVOICE_TIME"];
            $data_arr["money"] = -$billing_info[0]["INVOICE_MONEY"];

            //��Ʊ˰��
            $taxrate = get_taxrate_by_citypy($this->user_city_py);
            $data_arr["tax"] = round((0-$billing_info[0]["INVOICE_MONEY"])/(1+$taxrate) * $taxrate,2);

            $data_arr["invono"] = $billing_info[0]["INVOICE_NO"];
            //Ӳ�� 1  ��Ӳ�� 2
//            $data_arr["type"] = $scale_type==3?1:2;
            $data_arr["type"] = $billing_info[0]["INVOICE_BIZ_TYPE"];
            $data_arr["note"] = urlencode("������Ʊ");
            $data_arr["city"] = $this->user_city_py;
            $data_arr["contractnum"] = $contract_info[0]["CONTRACT_NO"];

            //ͬ����Ʊ���ݵ���ͬϵͳ
            $save_result_negative = saveInvoice2Con($data_arr);

            if(!$save_result_negative)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }
            /*-------------------���¼�¼----------------------*/
        }

        $this->model->commit();
        $result["status"] = true;
        $result["msg"] = g2u("��Ʊ�ɹ���");
        die(json_encode($result));

    }


    /**
    +----------------------------------------------------------
    * ��ͬ��Ʊȷ��
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function do_invoice()
    {
        //��ͬ���
        $contractid = trim($_REQUEST["contract_id"]);
        //��ƱID
        $invoice_ids = $_POST['invoiceid'];
        //��Ʊʱ��
        $invoice_time = $_POST['invoice_time'];
        //��Ʊ���
        $invoice_no = $_POST['invoice_no'];
        $invoice_biz_type = $_POST['invoice_biz_type'];  // ��Ʊ����

        //���ؽ����
        $result = array(
            'state' => 0,
            'msg'=>null,
            'data'=>null,
        );

        $income_model = D("Contract");
        $case_model = D("ProjectCase");
        $billing_model = D("BillingRecord");

        $contract_info = $income_model->get_contract_info_by_id($contractid,array("CONTRACT_TYPE","CONTRACT_NO","CASE_ID"));

        if(empty($contract_info)){
            die(json_encode($result));
        }

        $case_id = $contract_info[0]["CASE_ID"];

        //�ѿ�Ʊ
        $field_arr["STATUS"] = 4;

        //�ɹ���ʶ
        $succes_count = 0;
        $error_str = '';

        //ҵ��
        $this->model->startTrans();
        foreach($invoice_ids as $key=>$val)
        {
            $id = $val;
            if (D('BillingRecord')->isDuplicateInvoiceNo($invoice_no[$key], $contractid, $this->channelid)) {
                D()->rollback();
                echo json_encode(array(
                    'state' => 0,
                    'msg' => g2u(sprintf('��Ʊ�����ظ����ظ���Ʊ��Ϊ%s', $invoice_no[$key]))
                ));
                exit;
            }

            //ͨ����ֵ��ȡ
            $billing_info[0]["INVOICE_NO"] = u2g($invoice_no[$key]);
            $billing_info[0]["INVOICE_TIME"] = $invoice_time[$key];
            $billing_info[0]["INVOICE_BIZ_TYPE"] = $invoice_biz_type[$key];  // ��Ʊ����

            //���û����д��Ʊ�źͷ�Ʊʱ��
            if(!$billing_info[0]["INVOICE_NO"] || !$billing_info[0]["INVOICE_TIME"] || !$billing_info[0]["INVOICE_BIZ_TYPE"])
            {
                $result["state"] = 0;
                $result["msg"] = g2u("��Ʊʱ�䡢��Ʊ����ͷ�Ʊ����δ��д��δ���棬����д�����棡");
                die(json_encode($result));
            }
            else
            {
                $field_arr['INVOICE_TIME'] = $invoice_time[$key];
                $field_arr['INVOICE_NO'] = u2g($invoice_no[$key]);
                $field_arr['INVOICE_BIZ_TYPE'] = u2g($invoice_biz_type[$key]);
                $res = $billing_model->update_info_by_id($id,$field_arr);

                //�жϸú�ͬ���Ƿ���������ͨ���ķ�Ʊ
                $where = "CONTRACT_ID = ".$contractid." AND STATUS=3";
                $invoice_info = $billing_model->get_info_by_cond($where,array("ID"));

                //�����ͬ��û�д���Ʊ�ķ�Ʊ��¼ �޸ĺ�ͬ��IS_NEED_INVOICE=0
                $up_num_contract = true;
                if(!$invoice_info)
                    $up_num_contract = $income_model->update_info_by_id($contractid,array("IS_NEED_INVOICE"=>0));

                //����������ϸ��¼;
                $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
                $scale_type = $case_info[0]["SCALETYPE"];

                //����Ƿ�����Ա��Ʊ �޸ķ�����Ա��״̬����д��Ʊ����
                if( $scale_type == 2 )
                {
                    $up_dis_member_num_fail = false;

//                    $member_distribution_model = D("MemberDistribution");
//
//                    $cond_where = "RELATE_INVOICE_ID = ".$id;
//                    $update_arr = array("INVOICE_STATUS"=>2,"INVOICE_NO"=>$billing_info[0]["INVOICE_NO"]);
//
//                    //���·���״̬
//                    $up_dis_member_num =$member_distribution_model->update_info_by_cond($update_arr, $cond_where);

                    // ������¼����Ʊȷ��֮��Ĳ���
                    $up_dis_member_num = $this->fxAfterInvoiceConfirm($id, $billing_info[0]["INVOICE_NO"]);

                    if ($up_dis_member_num === false) {
                        $up_dis_member_num_fail = true;
                    }
                }

                //��������
                $res_1 = $this->add_income_after_financial_invoice(array($contractid),$scale_type,$id);

                //���ݷ�ƱId�ҵ���Ʊ��Ϣ
                $billing_info = $billing_model->get_info_by_id($id,array("INVOICE_MONEY","TAX","INVOICE_NO","REMARK","INVOICE_TIME", "INVOICE_BIZ_TYPE","FROMTYPE","FROMLISTID"));

                $format_date = oracle_date_format($billing_info[0]["INVOICE_TIME"]);
                $data_arr["date"] = $format_date?$format_date:$billing_info[0]["INVOICE_TIME"];
                $data_arr["money"] = $billing_info[0]["INVOICE_MONEY"];
                $data_arr["tax"] = $billing_info[0]["TAX"];
                $data_arr["invono"] = $billing_info[0]["INVOICE_NO"];
                //Ӳ�� 1  ��Ӳ�� 2
//                $data_arr["type"] = $scale_type==3?1:2;
                $data_arr["type"] = $billing_info[0]["INVOICE_BIZ_TYPE"];  // ��Ʊ���ͣ�1=���ѣ�2=�����
                $data_arr["note"] = urlencode($billing_info[0]["REMARK"]);
                $data_arr["city"] = $this->user_city_py;
                $data_arr["contractnum"] = $contract_info[0]["CONTRACT_NO"];

                load("@.contract_common");
                //ͬ����Ʊ���ݵ���ͬϵͳ
                $save_result = saveInvoice2Con($data_arr);

                //������û���Ʒ���������Ŀ�Ʊ���״̬
                if ($billing_info[0]['FROMTYPE'] == 2) {
                    $updateBusinessStatus = D("DisplaceApply")->updateListStatus($billing_info[0]['FROMLISTID'], 3); //����״̬��δ����״̬
                }

                //������ϲ������ɹ�
                if($res && $res_1 && $save_result && !$up_dis_member_num_fail && $up_num_contract && $updateBusinessStatus!==false)
                {
                    $succes_count += 1;
                }
                else
                {
                    $error_str .= "��" . $k+1 . "�У����Ϊ<$id>��Ʊȷ��ʧ��!<br />";

                }
            }
        }

        if(count($invoice_ids) == $succes_count){
            $this->model->commit();
            $result["state"] = 1;
            $result["msg"] = g2u("��Ʊ�ɹ���");
        }
        else
        {
            $this->model->rollback();
            $result["state"] = 0;
            $result["msg"] = g2u($error_str);
        }
        die(json_encode($result));
    }



    /**���濪Ʊ���ݣ���Ʊ���ڣ���Ʊ���룬˰�
     *@param none
     *return none
     */
    public function save_data()
    {
        $contract_id = intval($_REQUEST['contract_id']);

        $invoice_ids = $_POST['invoiceid'];
        $invoice_time = $_POST['invoice_time'];
        $invoice_no = $_POST['invoice_no'];
        $invoice_biz_type = $_POST['invoice_biz_type'];
        $billing_model = D("BillingRecord");

        D()->startTrans();
        foreach($invoice_ids as $key=>$val)
        {
            $id = $val;
            $field_arr['INVOICE_NO'] = u2g($invoice_no[$key]);
            if (D('BillingRecord')->isDuplicateInvoiceNo($field_arr['INVOICE_NO'],$contract_id, $this->channelid)) {
                D()->rollback();
                echo json_encode(array(
                    'state' => 0,
                    'msg' => g2u(sprintf('��Ʊ�����ظ����ظ���Ʊ��Ϊ%s', $field_arr['INVOICE_NO']))
                ));
                exit;
            }

            $field_arr['INVOICE_TIME'] = $invoice_time[$key];
            $field_arr['INVOICE_BIZ_TYPE'] = $invoice_biz_type[$key];
            $res = $billing_model->update_info_by_id($id,$field_arr);

            // ����Ƿ�����Ʊ�����޸���Ӧ�Ŀ�Ʊ��ϸ
            if ($res !== false) {
                $fxInvoiceDetailCount = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$val}")->count();
                if ($fxInvoiceDetailCount) {
                    // ���Ŀ�Ʊ��ϸ
                    $res = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$val}")->save(array('INVOICE_NO' => $field_arr['INVOICE_NO']));
                }
            }

            if($res === false)
            {
                D()->rollback();
                $result["state"] = 0;
                $result["msg"] = "���ݱ�����������³��ԣ�";
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
        }
        D()->commit();  // �ύ����
        $result["state"] = 1;
        $result["msg"] = "����ɹ���";
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }



    /**
   +----------------------------------------------------------
   * ҵ��ؿ��¼
   +----------------------------------------------------------
   * @param none
   +----------------------------------------------------------
   * @return none
   +----------------------------------------------------------
   */
    public function refundRecords()
    {
        $income_model = D("Contract");
        $payment_model =D("PaymentRecord");
        $project_income_model = D("ProjectIncome");
        $project_case_model = D("ProjectCase");
        $contractid = $this->_merge_url_param['contract_id'];
        //��ȡurl�е��������
        $id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $add = isset($_GET['add']) ? intval($_GET['add']) : '';

        $city_id = $_SESSION["uinfo"]["city"];
        $city = M("Erp_city")->find($city_id);
        $city_py = strtolower($city["PY"]);

        $contract_info = $income_model->get_contract_info_by_id($contractid,array("CONTRACT_TYPE","CONTRACT_NO","CASE_ID","MONEY"));
        $case_id = $contract_info[0]["CASE_ID"];
        //����case_id�ҵ�ҵ������
        $case_info = $project_case_model->get_info_by_id($case_id,array("SCALETYPE"));
        $scale_type = $case_info[0]["SCALETYPE"];

        $this->assign('caseId', $case_id);
        $this->assign('scaleType', $scale_type);

        //����
        if($showForm == 3 && $faction == 'saveFormData' && $id == 0)
        {
            if($scale_type != 2)//�������͵ĺ�ͬ����Ҫ�жϻؿ����Ƿ񳬹���ͬ���
            {
                //��ѯ���ú�ͬ�����еĻؿ����ܺ�
                $sql = "SELECT sum(MONEY) SUM_MONEY FROM ERP_PAYMENT_RECORDS WHERE CONTRACT_ID = ".$contractid;
                $sum_money = $this->model->query($sql);
                $sum_money = $sum_money ? $sum_money[0]["SUM_MONEY"] : 0;

                //�ж��ѻؿ�����ϱ��λؿ����Ƿ񳬹���ͬ���
                if(bccomp($sum_money + $_REQUEST["MONEY"],$contract_info[0]["MONEY"],2) > 0)
                {
                    $result["status"] = 0;
                    $result["msg"] = "�ѻؿ��".$sum_money."��"
                        . "+ ���λؿ��".$_REQUEST["MONEY"]."��"
                        . "������ͬ���ܽ�".$contract_info[0]["MONEY"]."����������ӻؿ�";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
            }

            //��֤
            $hasBilling = intval($_REQUEST['HAS_BILLING']); //�Ƿ������Ʊ

            if($_REQUEST['HAS_BILLING'] && empty($_REQUEST['BILLING_RECORD_ID'])){
                $result["msg"] = g2u('�Բ����ף���Ҫ������Ʊʱ����ѡ����Ҫ�����ķ�Ʊ�ţ�');
                die(@json_encode($result));
            }

            //����ؿ�����             
            $data["CASE_ID"] = $contract_info[0]["CASE_ID"];
            $data["MONEY"] = $_REQUEST["MONEY"];
            $data["CREATETIME"] = $_REQUEST["CREATETIME"];
            $data["REMARK"] = u2g($_REQUEST["REMARK"]);
            $data['BILLING_RECORD_ID'] = $_REQUEST['BILLING_RECORD_ID'];  // ��Ʊ�����Ӧ�Ŀ�Ʊ��¼ID
            $data["PAYMENT_METHOD"] = intval($_REQUEST["PAYMENT_METHOD"]);
            $data["CONTRACT_ID"] = $contractid;
            $data["HAS_BILLING"] = $_REQUEST['HAS_BILLING']; //�Ƿ���Ҫ��Ʊ��

            $this->model->startTrans();
            $refundid = $payment_model->add_refund_records($data);//�����ؿ��¼
            //var_dump($refundid);die;
            if(!$refundid)
            {   $this->model->rollback();
                $result["status"] = 0;
                $result["msg"] = "��ӻؿ��¼ʧ�ܣ�";
                $result["msg"] = u2g($result["msg"]);
                echo json_encode($result);
                exit;
            }
            else
            {
                //����������ϸ��¼   
                switch($scale_type)
                {
                    case 3:
                        $income_info['INCOME_FROM'] = 11;
                        break;
                    case 2:
                        $income_info['INCOME_FROM'] = 7;
                        // ��ȡ��Ʊ��Ӧ�Ŀ�Ʊ������������¶�Ӧ�Ŀ�Ʊ״̬ (����������)
                        if($hasBilling) {
                            $res1 = D('PaymentRecord')->updateFxCommissionPaymentStatus($data);
                        }
                        break;
                    case 4:
                        $income_info['INCOME_FROM'] = 15;
                        break;
                    case 8:
                        $income_info['INCOME_FROM'] = 22;
                        break;
                    default :
                        break;

                }
                $income_info['CASE_ID'] = $data["CASE_ID"];
                $income_info['ENTITY_ID'] = $contractid;
                $income_info['ORG_ENTITY_ID'] = $contractid;
                $income_info['INCOME'] =  $data["MONEY"];
                $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s");
                $income_info['PAY_ID'] = $refundid;
                $income_info['ORG_PAY_ID'] = $refundid;
                $income_info['INCOME_REMARK'] = $data["REMARK"];
                $income_info['PAYMENT_METHOD'] = $data["PAYMENT_METHOD"];

                //��������˰
                $taxrate = get_taxrate_by_citypy($city_py);
                $output_tax = round($data["MONEY"]/(1 + $taxrate) * $taxrate,2);
                $income_info['OUTPUT_TAX'] = $output_tax;

                if ($res1 !== false) {
                    $res1 = $this->add_income_after_financial_refund($income_info);
                }

                //����ؿ����ݵ���ͬ����ϵͳ 
                $data_arr = array(
                            "city"=>$city_py,
                            "contractnum"=>$contract_info[0]["CONTRACT_NO"],
                            "money"=>$_REQUEST["MONEY"],
                            "type"=> $scale_type == 3 ? 1 : 2,
                            "date"=>$_REQUEST["CREATETIME"],
                            "note"=>urlencode(u2g($_REQUEST["REMARK"])),
                            //"omsid"=>$refundid               //�ؿ��¼id
                        );

                load("@.contract_common");
                $resarr = saveRefund2Con($data_arr);//ͬ����ͬ�ؿ����� 
                $bakck_id = !empty($resarr["backid"]) ? intval($resarr["backid"]) : 0;
                $up_num = $payment_model->update_info_by_id($refundid , array("BACKID"=>$bakck_id));
                if($res1 && $bakck_id > 0 && $up_num)
//                if(true)  // ��ʽ�汾��Ҫ�������һ�д��룬���д����ǲ����õ�
                {
                    $this->model->commit();
                    $result["status"] = 2;
                    $result["msg"] = "��ӻؿ��¼�ɹ�";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
                else
                {
                    $this->model->rollback();
                    $result["status"] = 0;
                    $result["msg"] = "��ӻؿ��¼ʧ�ܣ�".$res1."--".$bakck_id."--".$up_num;
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }

            }
        } elseif( $showForm == 1 && $faction == 'saveFormData' && $id > 0 ) { //�޸Ļؿ��¼����

            $hasBilling = intval($_REQUEST['HAS_BILLING']); //�Ƿ������Ʊ

            //��֤
            if($hasBilling && empty($_REQUEST['BILLING_RECORD_ID'])){
                $result["msg"] = g2u('�Բ����ף���Ҫ������Ʊʱ����ѡ������ķ�Ʊ�ţ�');
                die(@json_encode($result));
            }

            if($hasBilling) {
                $remainPayAmount = D('PaymentRecord')->getRemainPayAmount(intval($_POST['BILLING_RECORD_ID']));
                $maxApplyAmount = $remainPayAmount + floatval($_POST['MONEY_OLD']);
                if ($remainPayAmount < 0 || $maxApplyAmount < $_POST['MONEY']) {
                    $msg = sprintf('�޸�ʧ�ܣ����ɻؿ���Ϊ%s', $maxApplyAmount);
                    if ($remainPayAmount < 0) {
                        $msg = sprintf('�ѻؿ�Ľ���Ѿ������������ɻؿ���%sԪ������ϵ����Ա���д���', -$remainPayAmount);
                    }
                    echo json_encode(array(
                        'status' => 0,
                        'msg' => g2u($msg)
                    ));
                    exit;
                }
            }

            $current_day = intval(date("d"));//��ǰ����
            $current_mouth = date("Y-m");//��ǰ�·�

            //��ȡ���޸ĵļ�¼���·�
            $payment_records_ctime = D("Erp_payment_records")->field("CREATETIME")->where("ID=$id")->find();
            $format_date = oracle_date_format($payment_records_ctime["CREATETIME"]);
            if($format_date)
            {
                $payment_records_cm = substr($format_date,0,7);
            }
            else
            {
                $payment_records_cm = substr($payment_records_ctime["CREATETIME"],0,7);
            }
            if($current_day > 5)//����Ժ�ֻ���޸ĵ��µ�����
            {
                if(strcmp($current_mouth,$payment_records_cm) == 1)
                {
                    $result["status"] = 0;
                    $result["msg"] = "�Բ���ÿ�����֮��ֻ���޸ĵ������ݣ������޸����»ؿ�����";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
            }

            $data["CASE_ID"] = $contract_info[0]["CASE_ID"];
            $data["MONEY"] = $_REQUEST["MONEY"];
            $data["CREATETIME"] = $_REQUEST["CREATETIME"];
            $data["PAYMENT_METHOD"] = intval($_REQUEST["PAYMENT_METHOD"]);
            $data["REMARK"] = u2g($_REQUEST["REMARK"]);
            $data["HAS_BILLING"] = intval($_REQUEST["HAS_BILLING"]);
            $this->model->startTrans();
            $up_num = $payment_model->update_info_by_id($id,$data);//�޸Ļؿ��¼
            $data['BILLING_RECORD_ID'] = trim($_REQUEST['BILLING_RECORD_ID']);  // ��Ʊ��¼��

            //ͬ���޸�������е�����   
            switch($scale_type)
            {
                case 3:
                    $income_from = 11;
                    break;
                case 2:
                    $income_from = 7;
                    if($hasBilling) {
                        $updated = D('PaymentRecord')->updateFxCommissionPaymentStatus($data, $msg);
                        if ($updated === false) {
                            $msg = empty($msg) ? '��Ʊʧ��' : $msg;
                            ajaxReturnJSON(false, g2u($msg));  // todo
                        }
                    }
                    break;
                case 4:
                    $income_from = 15;
                    break;
                default :
                    break;

            }

            //��������˰
            $taxrate = get_taxrate_by_citypy($city_py);
            $output_tax = round($data["MONEY"]/(1 + $taxrate) * $taxrate,2);
            $update_arr = array("INCOME"=>$data["MONEY"],"OUTPUT_TAX"=>$output_tax,"PAYMENT_METHOD"=>$data["PAYMENT_METHOD"]);
            $payment_up_num = $project_income_model
                ->update_income_info($update_arr, $contract_info[0]["CASE_ID"],$contractid,$id,$income_from);
            //ECHO M()->_sql();die;
            //ͬ���޸ĺ�ͬϵͳ�е�����
            load("@.contract_common");
            $backid = $payment_model->get_info_by_id($id,array("BACKID"));
            $ht_arr = array(
                            "city"=>$city_py,
                            "contractnum"=>$contract_info[0]["CONTRACT_NO"],
                            "money"=>$_REQUEST["MONEY"],
							"type"=>$scale_type == 3 ? 1 : 2,
                            "date"=>$_REQUEST["CREATETIME"],
                            "note"=>urlencode(u2g($_REQUEST["REMARK"])),
                            "backid"=>$backid[0]["BACKID"]
                        );
            $resarr = saveRefund2Con($ht_arr);//ͬ����ͬ�ؿ����� 
            //var_dump($up_num);var_dump($payment_up_num);var_dump($resarr);DIE;
            if($up_num && $payment_up_num && $resarr)
            {
                $this->model->commit();
                $result["status"] = 1;
                $result["msg"] = "�޸Ļؿ����ݳɹ���";
            }
            else
            {
                $this->model->rollback();
                $result["status"] = 0;
                $result["msg"] = "�޸Ļؿ�����ʧ�ܣ���";
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(135);
        if($_GET["from"] == "invoice")
        {
            $form->ADDABLE = 0;
            $form->EDITABLE = 0;
        }
        $form = $form->where("CONTRACT_ID = ".$contractid);
        $form = $form->where("CASE_ID = {$case_id}");

        if (in_array($scale_type, array(2, 3, 4 , 8))) {
                $ofInvoiceNoSql = <<<SQL
                SELECT B.ID,
                       B.INVOICE_NO
                FROM ERP_BILLING_RECORD B
                WHERE B.STATUS = 4
                  AND B.CASE_ID = {$case_id}
                  AND B.CONTRACT_ID={$contractid}

SQL;
            if ($showForm == 3) {
                $ofInvoiceNoSql .= " AND B.INVOICE_MONEY > (
                  SELECT nvl(SUM(P.MONEY), 0)
                    FROM ERP_PAYMENT_RECORDS P
                    WHERE P.BILLING_RECORD_ID = B.ID)";
            }
            $form->setMyField('BILLING_RECORD_ID', 'LISTSQL', $ofInvoiceNoSql);
        } else {
            $form->setMyField('BILLING_RECORD_ID', 'FORMVISIBLE', 0);
        }

        // ��Ʊ���벻���޸�
        if ($showForm == 1) {
            $form->setMyField('BILLING_RECORD_ID', 'READONLY', -1);
            $form->setMyField('HAS_BILLING', 'READONLY', -1);
            $paymentRecord = D("Erp_payment_records")->field("CREATETIME","BILLING_RECORD_ID")->where("ID=$id")->find();

            if(!$paymentRecord['BILLING_RECORD_ID']){ //û�й�����Ʊ�ŵ���ʾ��
                $form = $form->setMyFieldVal('HAS_BILLING', 0, TRUE);
            }
        }

        //���ûؿʽ
        $form->setMyField('PAYMENT_METHOD', 'LISTCHAR', array2listchar(D('PaymentRecord')->payment_method()));

        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������

        $this->assign('form',$formHtml);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->display('financial_invoice_records');
    }

    //�жϽ���˰��������
    //�������˰
    public function save_input_tax()
    {
        $ids = $_REQUEST["fid"]; //������ϸ�������
        $purchase = D("PurchaseList");
        $reim_detail_model = D("ReimbursementDetail");
        $this->model->startTrans();
        foreach($_REQUEST["input_tax"] as $key=>$val)
        {
            $data["INPUT_TAX"] = $val;
            $reim_detail_up_num = $reim_detail_model->update_reim_detail_by_id($ids["$key"],$data);
            //$purchase_id = $reim_detail_model->get_detail_info_by_id($ids["$key"],array("BUSINESS_ID"));
            //$purchase_up_num = $purchase ->update_purchase_list_by_id($purchase_id[0]["BUSINESS_ID"],$data);
            if(!is_int($reim_detail_up_num) )
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "����˰������������ԣ�";
                userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '����ȷ��:�������˰:' . json_encode($result) , serialize($_POST));
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
        }
        $this->model->commit();
        $result["state"] = 1;
        $result["msg"] = "����˰����ɹ���";
        userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '����ȷ��:�������˰:' . json_encode($result) , serialize($_POST));
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }

	//�жϽ���˰��������
    //�������˰
    public function save_input_reimDetail()
    {
        $ids = $_REQUEST["fid"]; //������ϸ�������
        $purchase = D("PurchaseList");
        $reim_detail_model = D("ReimbursementDetail");
		if($_REQUEST['action']=='getReimDtail'){
			//$ids = implode(',', $_POST["fid"]);
			$res = $reim_detail_model->get_detail_info_by_id($_POST["fid"]);
			foreach($res as $key=>$one){
				$temp[$one['ID']]['DEPT_ID'] = $one['DEPT_ID'];
				$temp[$one['ID']]['NCTYPE'] = $one['NCTYPE'];
				$temp[$one['ID']]['ISKF'] = $one['ISKF'];
				$temp[$one['ID']]['MONEY'] = $one['MONEY'];
				$temp[$one['ID']]['RID'] = $one['ID'];
				$temp[$one['ID']]['FEE_ID'] = $one['FEE_ID'];



			}
			echo json_encode($temp);
            exit;

		}
        $this->model->startTrans();
		foreach($_REQUEST["fid"] as $key=>$val){
			$dd[$key]["DEPT_ID"] = $_REQUEST["DEPTID"][$key] ;
			$dd[$key]["NCTYPE"] = $_REQUEST["NCTYPE"][$key] ;
			$dd[$key]["FEE_ID"] = $_REQUEST["FEEID"][$key] ;
			$dd[$key]["MONEY"] = $_REQUEST["MONEY"][$key] ;
			$dd[$key]["ISKF"] = $_REQUEST["ISKF"][$key] ;
			 
			
		}
        foreach($dd as $key=>$val)
        {
            $data["DEPT_ID"] = $val['DEPT_ID'];
			$data["NCTYPE"] = $val['NCTYPE'];
			$data["FEE_ID"] = $val['FEE_ID'];
			$data["MONEY"] = $val['MONEY'];
			$data["ISKF"] = $val['ISKF'];
            $reim_detail_up_num = $reim_detail_model->update_reim_detail_by_id($ids["$key"],$data);
			if($reim_detail_up_num){
				$remOne = $reim_detail_model->get_detail_info_by_id($ids["$key"]);
				if($remOne[0]['TYPE']==1 ||$remOne[0]['TYPE']==14 ){
					$temp['FEE_ID'] = $data["FEE_ID"];
					$purchase_up = D('Erp_purchase_list')->where("ID=".$remOne[0]['BUSINESS_ID'])->save($temp);
				}

			}
            //$purchase_id = $reim_detail_model->get_detail_info_by_id($ids["$key"],array("BUSINESS_ID"));
            //$purchase_up_num = $purchase ->update_purchase_list_by_id($purchase_id[0]["BUSINESS_ID"],$data);
            if(!is_int($reim_detail_up_num) )
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "������ϸ������������ԣ�";
                userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '����ȷ��:���汨����ϸ:' . json_encode($result) , serialize($_POST));
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
        }
        $this->model->commit();
        $result["state"] = 1;
        $result["msg"] = "������ϸ����ɹ���";
        userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '����ȷ��:�༭������ϸ:' . json_encode($result) , serialize($_POST));
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }


     
    //���汨��ȷ��ʱ��
    public function ajax_update_reimtime()
    {
        $ids = $_REQUEST["reimDetailId"]; //������ϸ�������
        //var_dump($ids);
        //$reim_list_model = D("ReimbursementList");
        //$this->model->startTrans();
        foreach($_REQUEST["REIMTIME"] as $key=>$val)
        {
          //  $data["REIM_TIME"] = $val;
           // $reim_list_up_num = $reim_list_model->update_reim_list_by_cond($data,"ID=".$ids["$key"]);
		  $reim_list_up_num =  D()->execute("update ERP_REIMBURSEMENT_LIST set REIM_TIME=to_date('$val','yyyy-mm-dd hh24:mi:ss') where ID=".$ids["$key"]);
		//  echo "update ERP_REIMBURSEMENT_LIST set REIM_TIME=(to_date('$val','yyyy-mm-dd hh24:mi:ss') where ID=".$ids["$key"];
            //$purchase_id = $reim_detail_model->get_detail_info_by_id($ids["$key"],array("BUSINESS_ID"));
            //$purchase_up_num = $purchase ->update_purchase_list_by_id($purchase_id[0]["BUSINESS_ID"],$data);
            if(!is_int($reim_list_up_num) )
            {
                //$this->model->rollback();
                $result["status"] = 0;
                $result["msg"] = "����ȷ��ʱ�䱣����������ԣ�";
                userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '����ȷ��:����ȷ��ʱ��:' . json_encode($result) , serialize($_POST));
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
        }
        //$this->model->commit();
        $result["status"] = 1;
        $result["msg"] = "����ȷ��ʱ�䱣��ɹ���";
        userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '����ȷ��:����ȷ��ʱ��:' . json_encode($result) , serialize($_POST));
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }


    /*
     * ����ȷ������� ����������еļ�¼
     * @param $memberid array() ��ԱID
     * @param $paymentid array()��Ա֧��id
     * @param $method ����ȷ�Ϸ�ʽ
     * @param $iscancle �Ƿ���ȡ��ȷ�ϲ��� 0��ȡ��ȷ�� 1ȡ��ȷ��
     * @param return ���ؽ�� �ɹ����ز����id ʧ�ܷ���false
     */
    public function add_income_after_financial_confirm($method,$iscancle = 0,
            $memberid=array(),$paymentid=array())
    {
        $member_model = D("Member");
        $member_payment_model = D("MemberPay");
        $ProjectIncome_model = D("ProjectIncome");
        if($method == 1 && empty($paymentid) && !empty($memberid)){
            foreach ($memberid as $key=>$val){
                //���ݻ�ԱID( MID )�ҵ�����ID��CASEID��                                        
                $member_info =$member_model->get_info_by_id($val,array("ID","CASE_ID"));
               // var_dump($member_info);die;
                $income_info['CASE_ID'] = $member_info["CASE_ID"];
                $income_info['ENTITY_ID'] = $member_info["ID"];
                $income_info['ORG_ENTITY_ID'] = $member_info["ID"];
                $income_info['INCOME_FROM'] = 2;
                $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s");

                $field_arr = array("ID","TRADE_MONEY");
                $cond_where = "MID = '".$val."' AND STATUS != 4";
                $payment_info = $member_payment_model->get_payinfo_by_cond($cond_where,$field_arr);
                foreach ($payment_info as $k=>$v){
                    $income_info['PAY_ID'] = $v["ID"];
                    $income_info['ORG_PAY_ID'] = $v["ID"];
                    if($iscancle == 0){
                        $income_info['INCOME'] = $v["TRADE_MONEY"];
                        $income_info['INCOME_REMARK'] = '��ԱԤ��ȷ��';
                    }elseif($iscancle == 1){//ȡ��ȷ�ϣ����븺�Ľ��ֵ
                        $income_info['INCOME'] = 0-$v["TRADE_MONEY"];
                        $income_info['INCOME_REMARK'] = '��Աȡ��Ԥ��ȷ��';
                    }
                    //var_dump($income_info);
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if(!$res){
                        return false;
                    }
                }
            }
        }
        else if($method == 2 && empty($memberid) && !empty($paymentid))
        {
            foreach ($paymentid as $k=>$v){
                $field_arr = array("MID","TRADE_MONEY");
                $payment_info = $member_payment_model->get_payinfo_by_id($v,$field_arr);
                $member_info =$member_model->get_info_by_id($payment_info[0]["MID"],array("CASE_ID"));

                $income_info['CASE_ID'] = $member_info["CASE_ID"];
                $income_info['ENTITY_ID'] = $payment_info[0]["MID"];
                $income_info['ORG_ENTITY_ID'] = $payment_info[0]["MID"];
                $income_info['INCOME_FROM'] = 2;
                $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s");
                $income_info['PAY_ID'] = $v;
                $income_info['ORG_PAY_ID'] = $v;
                if($iscancle == 0){
                    $income_info['INCOME'] = $payment_info[0]["TRADE_MONEY"];
                }elseif($iscancle == 1){//ȡ��ȷ�ϣ����븺�Ľ��ֵ
                    $income_info['INCOME'] = 0-$payment_info[0]["TRADE_MONEY"];
                }

                $res = $ProjectIncome_model->add_income_info($income_info);
                if(!$res){
                    return false;
                }
            }
        }

        return $res;
    }


    /*
    * ����Ի�Ա���п�Ʊ�� ����������ϸ��¼
    * @param $entityid array() ҵ��ʵ���ţ���Ա��š�����ͬ��š��������뵥��š�����
    * @param $scale_type ҵ������ 1���̻�Ա 2���� 3Ӳ�� 4�
    * @pay_id ��Ʊid
    * @is_positive �Ƿ�����ֵ
    * @tax ����˰  true �����ݿ���  false��0
    * @param return ���ؽ�� �ɹ����ز����id ʧ�ܷ���false
    */
    public function add_income_after_financial_invoice($entityid,$scale_type,$pay_id,$is_positive=true,$tax=true)
    {
        $member_model = D("Member");
        $BillingRecord_model = D("BillingRecord");
        $ProjectIncome_model = D("ProjectIncome");
        //var_dump($scale_type);die;
        foreach($entityid as $key=>$val)
        {
            $income_info['ENTITY_ID'] = $val;
            $income_info['ORG_ENTITY_ID'] = $val;
            $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
            $income_info['CITY_ID'] = $_SESSION["uinfo"]["city"];
            $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s");

            switch($scale_type)
            {
                //���̻�Ա
                case "1":
                    $income_info['INCOME_FROM'] = 3;
                    //���ݺ�ͬID���ԱID�ҵ���Ʊ��¼��Ϣ
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE = 2 and ID = ".$pay_id;
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;
                //������Ա
                case "2":
                    $income_info['INCOME_FROM'] = 8;
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE=3  and ID = ".$pay_id;
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;
                //Ӳ���ͬ
                case "3":
                    $income_info['INCOME_FROM'] = 12;

                    //���ݺ�ͬ��� ���ҷ�Ʊ״̬Ϊ�ѿ� ��Ʊ����Ϊ��ͬ��Ʊ�����з�Ʊ��¼
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE=1  and ID = ".$pay_id;
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;
                //���ͬ
                case "4":
                    $income_info['INCOME_FROM'] = 16;

                    //���ݺ�ͬ��� ���ҷ�Ʊ״̬Ϊ�ѿ� ��Ʊ����Ϊ��ͬ��Ʊ�����з�Ʊ��¼
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE=1  and ID = ".$pay_id;
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;

                //���ҷ��ճ�
                case "8":
                    $income_info['INCOME_FROM'] = 23;

                    //���ݺ�ͬ��� ���ҷ�Ʊ״̬Ϊ�ѿ� ��Ʊ����Ϊ��ͬ��Ʊ�����з�Ʊ��¼
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE=1  and ID = ".$pay_id;
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;
            }

            if(is_array($billing_record_info) && !empty($billing_record_info))
            {
                foreach ($billing_record_info as $k=>$v)
                {
                    $income_info['PAY_ID'] = $v["ID"];
                    $income_info['ORG_PAY_ID'] = $v["ID"];
                    //�Ƿ��ǲ��������滹�Ǹ�����
                    $income_info['INCOME'] = $is_positive?$v["INVOICE_MONEY"]:-$v["INVOICE_MONEY"];
                    $income_info['OUTPUT_TAX'] = $tax?$v["TAX"]:0;
                    if($tax) {
                        $income_info['OUTPUT_TAX'] = $is_positive?$v["TAX"]:-$v["TAX"];
                    }
                    if($v["INVOICE_MONEY"] < 0)
                    {
                        $income_info['INCOME_REMARK'] = "��Ա��Ʊ���";
                    }
                    $income_info['CASE_ID'] = $v["CASE_ID"];
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if(!$res)
                    {
                       return false;
                    }
                }
            }
            else
            {
                return false;
            }

        }
        return $res;
    }


    /*
    * �����Ӳ��/����лؿ�� ����������ϸ��¼
    * @param $income_info array() ����������ϸ���ֶεļ�ֵ��
    * @param return ���ؽ�� �ɹ����ز����id ʧ�ܷ���false
    */
    public function add_income_after_financial_refund($income_info){
        $ProjectIncome_model = D("ProjectIncome");
        $res = $ProjectIncome_model->add_income_info($income_info);
        return $res;
    }

    /**
     * Ӷ������
     * @param
     */
    public function callback_commission()
    {
        $city_channel = $this->channelid;
        Vendor('Oms.Form');
        $form = new Form();
        $commission_model = D("CommissionBack");

        //��ȡurl�е��������
        $id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        if($faction && $showForm ==1 && $id > 0){//
            if( !empty($_REQUEST["CALLBACK_DATE"]) ) $update_data["CALLBACK_DATE"] = strip_tags(trim($_REQUEST['CALLBACK_DATE']));
            if( !empty($_REQUEST["STATUS"]) ) $update_data["STATUS"] = strip_tags(trim($_REQUEST['STATUS']));
            $res = $commission_model->update_commission_info_by_id($id,$update_data);
            if($res)
            {
                $result["status"] = 1;
                $result["msg"] = "����ɹ���";
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "����ʧ�ܣ�";
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }

        $form->initForminfo(175)->where("CITY_ID = ".$city_channel." AND STATUS != 4");
        $sql = "(select a.ID,a.STATUS,a.CALLBACK_DATE,a.TYPE,b.CITY_ID,b.PRJ_NAME,
                    b.REALNAME,b.MOBILENO,b.PAID_MONEY,d.MONEY AGENCY_REWARD,C.STATUS REIM_STATUS
                    FROM ERP_COMMISSION_BACK a
                    left join ERP_CARDMEMBER b ON a.MID=b.ID 
                    LEFT JOIN ERP_REIMBURSEMENT_LIST c ON a.REIM_LIST_ID=c.ID 
                    left join erp_reimbursement_detail d on d.list_id = c.id
                    WHERE b.STATUS=1 AND c.TYPE IN(3,4,5,6) AND c.STATUS !=4 AND d.STATUS != 4 and a.mid = d.business_id)";
        $form->SQLTEXT = $sql;
        //����Ӷ���˻�״̬
        $commission_status_remark = $commission_model->get_conf_commission_status_remark();
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar($commission_status_remark), FALSE);
        $form->DELCONDITION = "%STATUS% == 0";
        $form->EDITCONDITION = "%STATUS% == 0";
        $formHtml = $form->getResult();
        $this->assign("form",$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->display("callback_commission");
    }

    /**
     * ����ӵ������ʱ��
     */
    public function save_commssion_callback_data()
    {
        if( !empty($_REQUEST["callback_date"]) )
        {
            $update_data["CALLBACK_DATE"] = strip_tags(trim($_REQUEST['callback_date']));
        }
        if( !empty($_REQUEST["status"]) )
        {
            $update_data["STATUS"] = strip_tags(trim($_REQUEST['status']));
        }
		!empty($_REQUEST["fid"])?$id = strip_tags(intval($_REQUEST['fid'])):0;
		$commission_model = D("CommissionBack");
        $res = $commission_model->update_commission_info_by_id($id,$update_data);
        if($res)
        {
            $result["status"] = 1;
            $result["msg"] = "����ɹ���";
        }
        else
        {
            $result["status"] = 0;
            $result["msg"] = "����ʧ�ܣ�";
        }
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }

    /*
     *�������˱��ģ������
     * @param none
     * @return none 
     */
    public function downloadMoudle($file="", $down_name = '�������������ϸ��ѯ��')
    {
        $file = "./Public/Uploads/down_template/�������������ϸ��ѯ��.xls";
        $suffix = substr($file,strrpos($file,'.')); //��ȡ�ļ���׺
        $down_name = $down_name.$suffix; //���ļ������������غ������
        //�жϸ������ļ�������� 
        if(!file_exists($file))
        {
            die("��Ҫ���ص��ļ��Ѳ����ڣ������Ǳ�ɾ��");
        }
        $fp = fopen($file,"r");
        $file_size = filesize($file);
        //�����ļ���Ҫ�õ���ͷ
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length:".$file_size);
        header("Content-Disposition: attachment; filename=".$down_name);
        $buffer = 1024;
        $file_count = 0;
        //��������������� 
        while(!feof($fp) && $file_count < $file_size)
        {
          $file_con = fread($fp,$buffer);
          $file_count += $buffer;
          echo $file_con;
        }
            fclose($fp);
        }


    /**
     * �Ǹ��ֳɱ�ȷ��
     * @param none
     * return none
     */
    public function no_outlaycoat_confirm()
    {
        //������Ϊ
        $act = isset($_POST['act'])?trim($_POST['act']):'';

        //ȷ��
        if($act=='confirm'){
            $Ids = $_POST['Ids'];

            //���ؽ����
            $return = array(
              'status'=>false,
              'data'=>'',
              'msg'=>'',
            );

            //����ѭ��
            $i = 0;
            foreach($Ids as $key=>$val){
                $ret = M('erp_noncashcost')->where("ID=$val AND STATUS = 2")->save(array('STATUS'=>5));
                if(!$ret){
                    $return['msg'] .= '���Ϊ' . $val . "ȷ��ʧ��!\n";
                }
                else
                {
                    //�ؿ���뵽��ͬϵͳ
                    //��ȡ��ͬ���
                    $noncashcost_data = M("erp_noncashcost")
                        ->field("contract_no,amount")
                        ->where("id = " . $val)
                        ->find();

                    //��ͬ���
                    $contractnum = $noncashcost_data['CONTRACT_NO'];
                    //�ʽ�س�ֽ��
                    $zjccd_money = $noncashcost_data['AMOUNT'];

                    //���뵽api����log����
                    $tongji_url =  CONTRACT_API . 'sync_ct_backmoney.php?city=' . $this->channelid_py  . '###type=1###contractnum=' . $contractnum  .'###zjccd_money='.$zjccd_money.'###date='.date('Y-m-d').'###note='.urlencode('����ϵͳ�Զ�ͬ��');
                    api_log($this->channelid,$tongji_url,0,$this->uid,1);

                    $i++;
                }
            }

            if($i>0){
                $return['status'] = true;
                $return['msg'] = "�ף���ȷ��".$i."����\n" . $return['msg'];

            }
            $return['msg'] = g2u($return['msg']);

            die(@json_encode($return));
        }


        Vendor('Oms.Form');
        $form = new Form();

        $form =  $form->initForminfo(197);
        //SQL���¸�ֵ
        $form->SQLTEXT = '((SELECT A.*,B.PROJECTNAME,B.CITY_ID,A.CONTRACT_NO CONTRACT  FROM ERP_NONCASHCOST A LEFT JOIN ERP_PROJECT B  ON A.PROJECT_ID = B.ID  WHERE (A.STATUS = 2 OR A.STATUS = 5)  AND B.CITY_ID=' . $this->channelid . ') ORDER BY A.ID DESC)';

        //��չ�ֲ�����(��������)
        $form->setAttribute('NOPERATE',1);

        //������
        $form->setMyField('APPLY_USER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        //����
        $form->setMyField('CITY_ID', 'LISTSQL','SELECT ID, NAME FROM ERP_CITY', TRUE);
        //���״̬
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar(array("0"=>'δ�ύ���',"1"=>'��˹�����',"2"=>'���ͨ��',"3"=>'δ���ͨ��',"5"=>'��ȷ��')), FALSE);
        //����
        $form->setMyField('TYPE', 'LISTCHAR', array2listchar(array("1"=>'���',"2"=>'���',"3"=>'����',"4"=>'����')), TRUE);

//        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
        //��ȡ��Ⱦҳ��
        $formhtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('form',$formhtml);
        $this->display('noncashcost');
    }

    /**
     +----------------------------------------------------------
     * ��ȡ�˿�۸�
     +----------------------------------------------------------
     * @param float $total_price �ܳɱ�
     * @param int $total_num ������
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    private function _get_avg_price($total_price , $total_num)
    {
        $return_price = 0;

        if($total_price % $total_num == 0)
        {
            $return_price = $total_price / $total_num;
        }
        else
        {
            $unit_price = round($total_price /  $total_num , 2);
            $remain_price = $total_price - ($total_num - 1) * $unit_price;
            $return_price = $unit_price < $remain_price ? $unit_price : $remain_price;
        }

        return $return_price;
    }

    /**
     * ����ʽ�ط�������
     * @param $reimListData
     * @return bool
     */
    private function revertFundPoolCostApply($reimListData, &$fail_reim_arr) {
        $dbResult = false;
        if (notEmptyArray($reimListData)) {
            D()->startTrans();
            $reimDetailList = D("ReimbursementDetail")->where("LIST_ID = {$reimListData['ID']}")->select();
            if (notEmptyArray($reimDetailList)) {
                foreach ($reimDetailList as $k => $v) {
                    $costData = D("Benefits")->getFundPoolCost($v['BUSINESS_ID']);
                    $costData['FEE'] = -$costData['FEE'];
                    $costData['EXPEND_FROM'] = 34;
                    $dbResult = D('ProjectCost')->add_cost_info($costData);  // ���һ�����Ĳɹ��ɱ�
                    if ($dbResult !== false) {
                        $dbResult = D('ReimbursementDetail')->where("ID = {$v['ID']}")->save(array('STATUS' => 3));  // ���±�����ϸ
                    }

                    if ($dbResult !== false ) {
                        // �����ʽ�ط���״̬
                        $dbResult = D('Benefits')->where("ID = {$v['BUSINESS_ID']}")->save(array(
                            "ISCOST" => 1,
                            "STATUS" => 1
                        ));
                    }

                    if ($dbResult === false) {
                        break;
                    }
                }
            }

            if ($dbResult !== false) {  // ���±����б�
                $dbResult = D('ReimbursementList')->where("ID = {$reimListData['ID']}")->save(array('STATUS' => 3));
            }

            if ($dbResult === false) {
                D()->rollback();
                $fail_reim_arr[] = $reimListData['ID'];
            } else {
                D()->commit();
            }
        }
    }

    private function fxAfterInvoiceConfirm($id, $invoiceNo) {
        $response = false;
        if (intval($id) && !empty($invoiceNo)) {
            // ��Ҫ���µ�����
            // 1�����¿�Ʊ��ϸ commission_invoice_detail
            // 2������Ӷ���¼ post_commission
            $response = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$id}")->save(array(
                'INVOICE_STATUS' => 3,  // �ѿ�Ʊ
                'INVOICE_NO' => $invoiceNo
            ));

            if ($response !== false) {
                $memberIdSql = <<<SQL
                    SELECT c.card_member_id,
                    d.post_commission_id,
                    m.case_id,
                    m.agency_reward_after amount,
                    m.housetotal
                    FROM erp_commission_invoice_detail d
                    LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                    LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
                    WHERE d.billing_record_id = %d
SQL;
                $memberIdList = D()->query(sprintf($memberIdSql, $id));
                if (count($memberIdList)) {
                    foreach ($memberIdList as $item) {
                        // ��ȡʣ��������Ӷ��Ŀ
                        $remainMoney = D('BillingRecord')->getRemainFxPostComisInvoice($item['CARD_MEMBER_ID'], $item['POST_COMMISSION_ID']);

                        if (abs($remainMoney) < 1) {
                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('INVOICE_STATUS' => 3));

                        } else {
                            // ����շѱ�׼δ��Ʊ������0����˵���ǲ��ֿ�Ʊ, INVOICE_STATUS = 2˵���ǲ��ֿ�Ʊ
                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('INVOICE_STATUS' => 2));
                        }
//                        if ($remainMoney > 0) {
//                            // ����շѱ�׼δ��Ʊ������0����˵���ǲ��ֿ�Ʊ, INVOICE_STATUS = 2˵���ǲ��ֿ�Ʊ
//                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('INVOICE_STATUS' => 2));
//                        } else {
//                            // INVOICE_STATUS = 3����ɿ�Ʊ
//                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('INVOICE_STATUS' => 3));
//                        }

                        if ($response === false) {
                            break;
                        }
                    }
                }
            }
        }

        return $response;
    }

    public function ajaxRemainInvoicePayAmount() {
        $billingRecordId = intval($_GET['billing_record_id']);
        $scaleType = intval($_GET['scale_type']);
        $data = array();
        if ($billingRecordId) {
            $remainAmount = D('PaymentRecord')->getRemainPayAmount($billingRecordId);
            if ($remainAmount > 0) {
                $data['remain_amount'] = $remainAmount;
                if ($scaleType == 2) { // ����Ƿ���ȷ���Ƿ������Ʊ��
                    $invoiceCount = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$billingRecordId} AND INVOICE_STATUS != 9")->count();
                    if ($invoiceCount) {
                        $data['contain_member'] = 1;  // �ÿ�Ʊ��¼�¹��л�Ա
                    } else {
                        $data['contain_member'] = -1; // �ÿ�Ʊ��¼��û�л�Ա
                    }
                }

                ajaxReturnJSON(true, g2u('��ȡʣ��ؿ���ɹ�'), $data);
            } else {
                ajaxReturnJSON(false, g2u('�÷�Ʊ�Ѿ�ȫ���ؿ�'));
            }
        }
        ajaxReturnJSON(false, g2u('��ȡʣ�࿪Ʊ���ʧ��'));
    }
}
/* End of file FinancialAction.class.php */
/* Location: ./Lib/Action/FinancialAction.class.php */