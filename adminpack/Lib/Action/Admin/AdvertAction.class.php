<?php
    class AdvertAction extends ExtendAction{
        /**
         * Ӳ��CASE
         */
        const YG_CASE_ALIAS = 'yg';

        /**
         * �CASE
         */
        const HD_CASE_ALIAS = 'hd';

        /**
         * ���ҷ��ճ�CASE
         */
        const FWFSC_CASE_ALIAS = 'fwfsc';

        /**
         * ���ҷ��ճ��SCALETYPE
         */
        const FWFSC_SCALETYPE = 8;

        /**
         * Ӳ���SCALETYPE
         */
        const YG_SCALETYPE = 3;

        /**
         * ��λ��Ԫ
         */
        const UNIT_RMB_YUAN = 'Ԫ';

        /**
         * ��λ��%
         */
        const UNIT_PERCENT = '%';

        /**
         * �����뿪Ʊ��Ȩ��
         */
        const APPLYINVOICE = 743;

        /**
         * ���뻻ƱȨ��
         */
        const CHANGEINVOICE = 770;

        /**
         * ������ƱȨ��
         */
        const REFUNDINVOICE = 772;


        /**
         * ͬ����ͬϵͳȨ��
         */
        const SYN_CONTRACT_SYSTEM = 0;

        private $model;
        private $tab_num;
        private $_merge_url_param = array();

        private $contractOptions = array(
            '_check' => array(
                'default' => 737,
                self::HD_CASE_ALIAS => 549,
                self::YG_CASE_ALIAS => 524,
                self::FWFSC_CASE_ALIAS => 550
            ),
            '_add' => array(
                self::YG_CASE_ALIAS => 523
            )
        );

        private $invoiceOptions = array(
            '_add' => array(
                'default' => 738,
            ),
            '_check' => array(
                'default' => 739
            ),
            '_edit' => array(
                'default' => 740
            ),
            '_del' => array(
                'default' => 742
            )
        );

        //���캯��
		public function __construct() 
		{
            // Ȩ��ӳ���
            $this->authorityMap = array(
                'applyInvoice' => self::APPLYINVOICE,
                'export_members' => 766,
                'changeInvoice' => self::CHANGEINVOICE,
                'refundInvoice' => self::REFUNDINVOICE,
                //�����ͬ�û��������ذ�ť��Ӳ��=��844���=��871����ͬ���Խ���=��872
                'change_contract_displace' =>array(
                    'yg'=>844,
                    'hd'=>871,
                    'default'=>872,
                )
            );

            $this->model = new Model();
			parent::__construct();
            if($_GET['is_from'] == 1)
            {
                $this->tab_num = 11;
            }
            elseif($_GET['is_from'] == 2 && $_GET["CASE_TYPE"] == 'hd')
            {
                $this->tab_num = 12;
            }
            else if($_GET['is_from'] == 2 && $_GET["CASE_TYPE"] == 'xmxhd')
            {
                $this->tab_num = 17;
            }
            else if ($this->_get('is_from') == 2 && $this->_get('CASE_TYPE') == 'fwfsc') {
                $this->tab_num = 24;
            }
            elseif($_GET['is_from'] == 3)
            {
                $this->tab_num = 13;
            }
                        
            !empty($_GET['prjid']) ? $this->_merge_url_param['prjid'] = $_GET['prjid'] : 0;
            !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
            !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
            !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
            if(!empty($_GET['TAB_NUMBER'])) 
            { 
                $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']);
            }
            else 
            {
                $this->_merge_url_param['TAB_NUMBER'] = $this->tab_num;

            }
            !empty($_GET['parentchooseid']) ? $this->_merge_url_param['contract_id'] = $_GET['parentchooseid'] :$_GET['contract_id']; 
            !empty($_GET['contract_type']) ? $this->_merge_url_param['contract_type'] = $_GET['contract_type'] : '';            
            !empty($_GET['invoiceId']) ? $this->_merge_url_param['invoiceId'] = $_GET['invoiceId'] : '';
            !empty($_GET['is_from']) ? $this->_merge_url_param['is_from'] = $_GET['is_from'] : '';
            !empty($_GET['scale_type']) ? $this->_merge_url_param['scale_type'] = $_GET['scale_type'] :
                ($_GET["CASE_TYPE"] ? $_GET["CASE_TYPE"] : "");
            !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = $_GET['CASE_TYPE'] : '';
            !empty($_GET['activId']) ? $this->_merge_url_param['activId'] = $_GET['activId'] : '';
            !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
			!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
            
		}

        public function index(){
            if ($_REQUEST['CASE_TYPE'] == 'yg') {
                $this->_merge_url_param['tab_num'] = 11;
            } else if($_REQUEST['CASE_TYPE'] == 'fwfsc') {
                $this->_merge_url_param['tab_num'] = 24;
            } else if ($_REQUEST['CASE_TYPE'] == 'hd') {
                $this->_merge_url_param['tab_num'] = 12;
            } else if ($_REQUEST['CASE_TYPE'] == 'xmxhd') {
                $this->_merge_url_param['tab_num'] = 17;
            }else {
                $this->_merge_url_param['tab_num'] = 13;
                $this->_merge_url_param['is_from'] = 3;
            }

            $hasTabAuthority = $this->checkTabAuthority($this->_merge_url_param['tab_num']);
            if ($hasTabAuthority['result']) {
                $roleInfo = D('erp_role')->where("LOAN_ROLEID = {$hasTabAuthority['roleID']}")->find();
                $url = U("{$roleInfo['LOAN_ROLECONTROL']}/{$roleInfo['LOAN_ROLEACTION']}", $this->_merge_url_param);
                halt2('', $url);
                return;
            }
        }
           
        /**
        +----------------------------------------------------------
        *������ͬ
        +----------------------------------------------------------
        * @param $file Ҫ��ȡ���ļ�
        +----------------------------------------------------------
        * @return $data 
        +----------------------------------------------------------
        */
        public function contract()
        {

            $act = isset($_REQUEST['act'])?trim($_REQUEST['act']):'';

            //ͬ����ͬϵͳ
            if($act=='syc_contract')
            {
                $return = array(
                    'status'=>false,
                    'msg'=>'',
                    'data'=>null,
                );

                $contract = $_REQUEST['contract'];

                $contract_model = M("Erp_income_contract");

                $error_str = '';

                foreach($contract as $key=>$val){
                    $contract_ret = $contract_model->field("contract_no,city_py")
                        ->where("ID = $val")->find();

                    $contract_url = CONTRACT_API . "get_ct_info.php?city=" . $contract_ret['CITY_PY'] . "&contractnum=" . $contract_ret['CONTRACT_NO'];
                    $contract_data = curl_get_contents($contract_url);

                    $contract_data = unserialize($contract_data);

                    if(empty($contract_data)) {
                        $error_str .= "��" . ($key + 1) . "����ͬ���Ӻ�ͬϵͳ��δȡ������(���ܸ���ͬ״̬�й�)!\n";
                        continue;
                    }

                    //������λ
                    $data['COMPANY'] =  $contract_data['contunit'];
                    //��ʼʱ��
                    $data['START_TIME'] = date("Y-m-d",$contract_data['contbegintime']);
                    //����ʱ��
                    $data['END_TIME'] = date("Y-m-d",$contract_data['contendtime']);
                    //��ͬ״̬
                    $data['STATUS'] = $contract_data['step'];
                    //��ͬ���
                    $data['MONEY'] = $contract_data['contmoney'];
                    //��ͬ����
                    $data['CONTRACT_TYPE'] = $contract_data['type'];
                    //��ͬǩԼ��
                    $data['SIGN_USER'] = $contract_data['addman'];
                    //�ѷ������
                    $data['ISSUEAMOUNT'] = $contract_data['all_fb'];
                    //����ȷ��ʱ��
                    if($contract_data['confirmtime'])
                        $data['CONF_TIME'] = date("Y-m-d H:i:s",$contract_data['confirmtime']);

                    $update = $contract_model->where("ID = $val")->save($data);

                    if(!$update){
                        $error_str .= "��" . ($key + 1) . "����ͬ������ʧ��!\n";
                        continue;
                    }

                }

                if($error_str)
                {
                    $return['msg'] = g2u($error_str);
                }
                else
                {
                    $return['status'] = true;
                }

                die(@json_encode($return));

            }

            $prjId = $_REQUEST["prjid"] ? $_REQUEST["prjid"] : 0;

            $income_contract_model = D("Contract");
            $project_case_model = D('ProjectCase');
            $scale_type_conf = $project_case_model->get_conf_case_type();//ҵ���������� ds fx yg hd cp
            //var_dump($scale_type_conf);
            //var_dump($this->_merge_url_param['scale_type']);            
            $id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
            $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
            $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';

            $is_from = $_REQUEST["is_from"];//��ȡ��Դ 1Ӳ�� 2� 3��ͬ�˵�
            $activId = $_REQUEST["activId"] ? $_REQUEST["activId"] : 0;

            //��������������
            if ($_REQUEST["flowId"]) {
                $case_id = $_REQUEST["CASEID"];
                $case_info = $project_case_model->get_info_by_id($case_id, array("SCALETYPE"));
                $scale_type = $case_info[0]["SCALETYPE"];
                if ($scale_type == $scale_type_conf["yg"]) {
                    $is_from = 1;
                } else if ($scale_type == $scale_type_conf["hd"] || $scale_type == $scale_type_conf["ds"]) {
                    $is_from = 2;
                } else {
                    $is_from = 3;
                }

            } //��ҵ�����ڽ���
            else {
                //��Ӳ�����
                if ($is_from == 1) {
                    $scale_type = $scale_type_conf["yg"];
                } else if ($is_from == 2) {
                    //�ӵ����»ִ�н���
                    if ($_GET["CASE_TYPE"] == 'xmxhd' && $_GET["businessclass"] == 1) {
                        //$case_id = $_GET["CASEID"];
                        $scale_type = $scale_type_conf["ds"];
                    } //�����»ִ�н���
                    else if ($_GET["CASE_TYPE"] == 'xmxhd' && $_GET["businessclass"] == 2) {
                        $scale_type = $scale_type_conf["fx"];
                    } //Ӳ���»ִ�н���
                    else if ($_GET["CASE_TYPE"] == 'xmxhd' && $_GET["businessclass"] == 3) {
                        $scale_type = $scale_type_conf["yg"];
                    } else if ($_GET['CASE_TYPE'] == 'fwfsc') {  // �ӷ��ҷ��ճ�ִ�н���
                        $scale_type = $scale_type_conf['fwfsc'];
                    } //�����ִ�н���
                    else {
                        $scale_type = $scale_type_conf["hd"];
                    }

                }

                //�ж���ĿȨ��
                if ($_REQUEST["is_from"] == 1 || $_REQUEST["is_from"] == 2) {
                    $this->project_auth($prjId, $scale_type, $this->_merge_url_param['flowId']);
                }

                //�������Ŀ�»��ִ�У���ת���ɹ�����tabҳ
                if ($_GET["CASE_TYPE"] == 'xmxhd') {
                    $this->redirect("Purchase/purchase_manage", $this->_merge_url_param);
                    die;
                }
                //������Ŀid��ҵ�����ͻ�ȡ��ͬ������Ϣ
                if ($scale_type == 3) {
                    $case_info = $project_case_model->get_info_by_pid($prjId, 'yg', array('ID'));
                } else if ($scale_type == 4) {
                    $case_info = $project_case_model->get_info_by_pid($prjId, 'hd', array('ID'));
                } else if ($scale_type == 1) {
                    $case_info = $project_case_model->get_info_by_pid($prjId, 'ds', array('ID'));
                } else if ($scale_type == 8) {
                    $case_info = $project_case_model->get_info_by_pid($prjId, 'fwfsc', array('ID'));
                }

                $case_id = $case_info[0]['ID'];

            }

            $cityid = $_SESSION["uinfo"]["city"];
            $sql = "select PY,NAME from ERP_CITY where ID=" . $cityid;
            $city_info = $this->model->query($sql);

            if ($is_from == 1 && $showForm == 3 && $faction == '')//��� ���� ��ťʱ�ж�ҵ������ Ӳ�㲻���������Ӻ�ͬ
            {
                $sql = "select ID from ERP_INCOME_CONTRACT where CASE_ID "
                    . "=(select ID from ERP_CASE where PROJECT_ID=" . $prjId . " and SCALETYPE = 3)";
                $res = $this->model->query($sql);
                if ($res) {//�����Ӳ���ͬ  �������ٴ���Ӻ�ͬ
                    js_alert("��Ӳ�㰸�����к�ͬ���������ٴ����", U("Advert/contract", $this->_merge_url_param));
                    exit;
                }
            }

            //��Ӻ�ͬ��Ϣ�������ͬ��ERP_INCOME_CONTRACT��
            if ($showForm == 3 && $faction == 'saveFormData') {
                if (empty($case_info)) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('���ʧ�ܣ�δ�鵽��ĿӲ�㰸����Ϣ��');
                    echo json_encode($result);
                    exit;
                }

                $case_id = $case_info[0]['ID'];
                if ($_POST["CONTRACT_NO"]) $data["CONTRACT_NO"] = $_POST["CONTRACT_NO"];
                if ($_POST["COMPANY"]) $data["COMPANY"] = u2g(trim($_POST["COMPANY"]));
                $prjname = u2g(trim($_POST["PROJECTNAME"]));
                if (trim($_POST["START_TIME"])) $data["START_TIME"] = trim($_POST["START_TIME"]);
                if (trim($_POST["END_TIME"])) $data["END_TIME"] = trim($_POST["END_TIME"]);
                if (trim($_POST["PUB_TIME"]) != "") $data["PUB_TIME"] = trim($_POST["PUB_TIME"]);
                if (trim($_POST["CONF_TIME"]) != "") $data["CONF_TIME"] = trim($_POST["CONF_TIME"]);
                if (strip_tags(trim($_POST["CONF_USER"]))) $data["CONF_USER"] = strip_tags(trim($_POST["CONF_USER"]));
                if ($_POST["MONEY"]) $data["MONEY"] = intval($_POST["MONEY"]);
                if ($_POST["STATUS"]) $data["STATUS"] = trim($_POST["STATUS"]);
                if ($_POST["ADDMAN"]) $data["SIGN_USER"] = strip_tags($_POST["ADDMAN"]);
                if ($_POST["CONTRACT_TYPE"]) $data["CONTRACT_TYPE"] = strip_tags($_POST["CONTRACT_TYPE"]);
                $data["ADD_TIME"] = date("Y-m-d H:i:s", time());
                $data["CASE_ID"] = $case_id;
                $data["CITY_PY"] = $city_info[0]["PY"];//�û�����ƴ��
                $data["CITY_ID"] = $cityid;
                //var_dump($data);die;
                $this->model->startTrans();
				//if($data["MONEY"]>0){
					$insertid = $income_contract_model->add_contract_info($data);
				//}else{  
					//$result['status'] = 0;
					//$result['msg'] = g2u('��ͬ���Ϊ�㣬����ʧ�ܣ�');
					//echo json_encode($result);
                   // exit;
				//}

                //���Ӳ���ͬ�ɹ� ��д��Ŀ���еĺ�ͬ��š���Ŀ���ƺͺ�ͬ��λ,��ȡ��ͬϵͳ��Ʊ���ؿ�
                if ($insertid) {
                    $contractnum = $_POST["CONTRACT_NO"];
                    $cityid = $_SESSION["uinfo"]["city"];
                    $sql = "select PY from ERP_CITY where ID=" . $cityid;
                    $citypy = $this->model->query($sql);
                    $citypy = strtolower($citypy[0]["PY"]);//�û�����ƴ��

                    $project_model = D("Project");
                    $update_arr["CONTRACT"] = $data["CONTRACT_NO"];
                    $update_arr["PROJECTNAME"] = $prjname;
                    $update_arr["COPANY"] = $data["COMPANY"];
                    $up_num = $project_model->update_prj_info_by_id($prjId, $update_arr);

                    //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
                    $insert_refund_id = $this->save_refund_data($contractnum, $insertid, $citypy);

                    //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ��Ʊ��¼����������ͬ��������ϵͳ
                    $insert_invoice_id = $this->save_invoice_data($contractnum, $insertid, $citypy);

                    //var_dump($insert_invoice_id);die;

                    if ($up_num) {
                        $this->model->commit();
                        $result['status'] = 2;
                        $result['msg'] = '��Ӻ�ͬ�ɹ�';
                    } else {
                        $this->model->rollback();
                        $result['status'] = 0;
                        $result['msg'] = '��Ӻ�ͬʧ��';
                    }
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                } else {
                    $result['status'] = 0;
                    $result['msg'] = '��Ӻ�ͬʧ��,����ԭ���ǣ�<br />'
                        . '����Ŀ�����Լ���ͬ����ϵͳ���Ѿ����ڣ������ͬ�ŵ���д�Ƿ���ȷ';
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

            } else if ($id > 0 && $faction == 'delData') {

                //ɾ����ͬʱ��ͬʱҪɾ����Ӧ�Ŀ�Ʊ�ͻؿ��¼����Ҫ�޸Ķ�Ӧ��Ŀ����Ŀ���Ƹ���ͬ��
                $payment_model = D("PaymentRecord");
                $billing_model = D("BillingRecord");
                $contract_case_info = $income_contract_model->get_contract_info_by_id($id, array("CASE_ID"));
                $contract_case_id = $contract_case_info[0]["CASE_ID"];
                $cond_where = "CASE_ID = " . $contract_case_id;
                //ɾ���ؿ�
                $payment_del_num = $payment_model->del_info_by_cond($cond_where);
                //ɾ����Ʊ
                $billing_del_num = $billing_model->del_info_by_cond($cond_where);
                //�޸���Ŀ���Ƹ���ͬ�ţ��ÿգ�
                $project_model = D("Project");
                $update_arr["CONTRACT"] = "";
                $update_arr["PROJECTNAME"] = "";
                $update_arr["COPANY"] = "";
                $up_num = $project_model->update_prj_info_by_id($prjId, $update_arr);
            }

            Vendor('Oms.Form');
            $form = new Form();
            $children = array(
                array("��Ʊ��¼", U("/Advert/InvoiceRecord", $this->_merge_url_param)),
                array("�ؿ��¼", U("/Advert/refundRecords", $this->_merge_url_param))
            );
            //echo $is_from;
            $contract_model = D("Contract");
            $displace_status_remark =  $contract_model->get_displace_status_remark();
            $form->initForminfo(124);
            //����
            $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_CITY', FALSE);
            $form->setMyFieldVal("CITY_ID", $cityid, True);
            $form->setMyFieldVal("SCALETYPE", $scale_type, TRUE);
            $form->setMyField("DISPLACE", "GRIDVISIBLE", "-1");
            $form->setMyField("DISPLACE","LISTCHAR",array2listchar($displace_status_remark));
            $form->setMyField("ISSUEAMOUNT", "GRIDVISIBLE", "-1");

            $form->DELABLE = 0;
            if ($is_from == 1 || $is_from == 2) //�û�ͨ��Ӳ�������
            {
                $where = "CASE_ID=" . $case_id;
            } else if ($is_from == 3)//�û�ͨ����ͬ����˵�����$is_from=3
            {
                $sign_user = strtolower($_SESSION["uinfo"]["uname"]);
                $where = "SIGN_USER= '" . $sign_user . "' AND CITY_ID = '" . $cityid . "'";
            }
            if ($is_from == 2 || $is_from == 3 || $_REQUEST["flowId"]) {
                $form->ADDABLE = 0;
            }

            //׷��ͬ����ͬϵͳ����
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='syn_contract_system'>ͬ����ͬϵͳ</a>";
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='change_contract_displace'>�����ͬ�û�����</a>";
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='save_displace_property'>�����û�����</a>";
            $form->setChildren($children)->where($where);
            $caseType = !empty($_REQUEST['CASE_TYPE']) ? $_REQUEST['CASE_TYPE'] : 'default';
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->contractOptions, $caseType);
            $formHtml = $form->getResult();
            $this->assign('form', $formHtml);

            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
            $this->assign('prjid', $prjId);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('contract_type', $contract_type);
            $this->assign('is_from', $is_from);
            $this->assign('scale_type', $scale_type);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->display('advert_contract');
        }

        /**
         *$id  ���id
         */
        public function changeDisplace()
        {
            $id = intval($_REQUEST["fid"]);
            $sql = "select * from erp_project where id =".$id;
            $projectDisplace = $this->model->query($sql);
            $this->assign("projectDisplace",$projectDisplace);
            $this->display('change_displace');
        }
        /**
        +----------------------------------------------------------
        *���ݺ�ͬ�Ż�ȡ��ͬ��������
        +----------------------------------------------------------
        * @param $file Ҫ��ȡ���ļ�
        +----------------------------------------------------------
        * @return $data 
        +----------------------------------------------------------
        */
        public function get_contract_data()
        {
            $contractnum = $_REQUEST["contractnum"];
			$projectId = $_REQUEST['projectId'];
            $cityid = $this->channelid;//$_SESSION["uinfo"]["city"];
            $sql = "select PY from ERP_CITY where ID=".$cityid;
            $citypy = $this->model->query($sql);
            $citypy = strtolower($citypy[0]["PY"]);//�û�����ƴ��
			$sql = "select b.CONTRACT_NO  as CONTRACT from ERP_CASE a left join ERP_INCOME_CONTRACT b on a.ID=b.CASE_ID where a.SCALETYPE in (select SCALETYPE from ERP_CASE where PROJECT_ID='$projectId') and b.CONTRACT_NO='$contractnum' and A.FSTATUS<>7 and b.CITY_ID='$cityid'";
			$res = M()->query($sql);  
			if($res){
				$result['msg'] = g2u('�ú�ͬ���Ѿ�����ͬҵ������ʹ�ã�');
				echo $str = json_encode($result);
				  
			}else{
				//��ȡ��ͬ������Ϣ 
				load("@.contract_common");
				$contractData = getContractData($citypy,$contractnum);
				//var_dump($contractData);
				$contractData["contunit"] = iconv("gbk","utf-8",$contractData["contunit"]);
				$contractData["prjname"] = iconv("gbk", "utf-8", $contractData["prjname"]);
				$contractData["contbegintime"] = date("Y-m-d",$contractData["contbegintime"]);
				$contractData["contendtime"] = date("Y-m-d",$contractData["contendtime"]);
				$contractData["contmoney"] = intval($contractData["contmoney"]);
				$contractData["confirmtime"] =$contractData["confirmtime"] ? date("Y-m-d",$contractData["confirmtime"]):' ';
				$contractData["pubdate"] =$contractData["pubdate"] ? $contractData["pubdate"] : '';   
				echo json_encode($contractData);
			}
        }

        /**
        +----------------------------------------------------------
         * �����ͬ�û�����
        +----------------------------------------------------------
         * @return $data
        +----------------------------------------------------------
         */
        public function updateDisplaceProperty(){

            //���ض���
            $return = array(
                'status'=>0,
                'msg'=>'',
                'data'=>null,
            );

            $fId  = isset($_POST['fid']) ? intval($_POST['fid']) : 0; //��ͬID
            $data['DISPLACE']  = isset($_POST['displace']) ? intval($_POST['displace']) : ""; //�û�����ֵ

            //��������
            $contractModel = D("Contract");
            $updateRet = $contractModel
                ->where('ID = ' . $fId)
                ->save($data);

            if($updateRet===false){
                $return['msg'] = g2u("�ף�����ʧ�ܣ������ԣ�");
            }else {
                $return['status'] = 1;
                $return['msg'] = g2u("�ף������ɹ���");
            }

            //���ؽ����
            die(@json_encode($return));

        }
        /**
        +----------------------------------------------------------
        *�ؿ���ϸ��¼
        +----------------------------------------------------------
        * @param $file Ҫ��ȡ���ļ�
        +----------------------------------------------------------
        * @return $data 
        +----------------------------------------------------------
        */
        public function refundRecords(){

          //��ȡurl�е��������
          $id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
          $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
          $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
          $add = isset($_GET['add']) ? intval($_GET['add']) : '';
          $contractid = $_REQUEST["parentchooseid"];
          Vendor('Oms.Form');
          $form = new Form();            
          $_REQUEST["prjid"] ? $prjId = $_REQUEST["prjid"] : 0;            
          $sql = "select CASE_ID from ERP_INCOME_CONTRACT where ID=".$contractid;
          $res = $this->model->query($sql);
          $caseId = $res[0]["CASE_ID"];  
          $where = "CASE_ID = '".$caseId."' AND CONTRACT_ID = '".$contractid."'";
          $form->initForminfo(135);
          
          $form->ADDABLE = 0;
          $form->EDITABLE = 0;
          $form->DELABLE = 0;
          $formHtml = $form
                    ->where($where)                      
                    ->getResult();    
          $this->assign('form',$formHtml);
          $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
          $this->display('advert_refund_records');            
        }

         
        
        /**
        +----------------------------------------------------------
        *���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
        +----------------------------------------------------------
        * @param  none
        +----------------------------------------------------------
        * @return none
        +----------------------------------------------------------
        */
        public function save_refund_data($contractnum, $contract_id,$citypy="nj")
        {
            load("@.contract_common");
            $refundRecords = get_backmoney_data_by_no($citypy,$contractnum);
            if (empty($refundRecords) || (is_array($refundRecords) && count($refundRecords) == 0)) {
                return true;
            }
            //����ͬ�ؿ��¼���뵽����ϵͳ�����ݿ���
            if(!empty($refundRecords))
            {
               $contract_model = D("Contract");
               $payment_model = D("PaymentRecord");
               
               $conf_where = "ID = '".$contract_id."'";
               $field_arr = array("CASE_ID");
               $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
                // ��ȡ��Ŀ������
                if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                    $scaleType  = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
                }

               foreach($refundRecords as $key=>$val)
               {
				    $taxrate = get_taxrate_by_citypy($citypy);
					$tax = round($val["money"]/(1 + $taxrate) * $taxrate,2);

                    $refund_data["MONEY"] = $val["money"];
                    $refund_data["CREATETIME"] = $val["date"];
                    $refund_data["REMARK"] = $val["note"];
                    $refund_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                    $refund_data["CONTRACT_ID"] = $contract_id;
                    $insert_reund_id = $payment_model->add_refund_records($refund_data);
                    if(!$insert_reund_id)
                    {
                        return false;
                    }
                    else
                    {
                        //����������ϸ��¼
                        if ($scaleType == self::YG_SCALETYPE) {
                            $income_info['INCOME_FROM'] = 11;
                        } else if ($scaleType == self::FWFSC_SCALETYPE) {
                            $income_info['INCOME_FROM'] = 22;
                        }

                        $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                        $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                        $income_info['INCOME'] =  $val["money"];
                        $income_info['OUTPUT_TAX'] =  $tax;
                        $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                        $income_info['OCCUR_TIME'] = $val["date"];
                        $income_info['PAY_ID'] = $insert_reund_id;                
                        $income_info['INCOME_REMARK'] = u2g($val["note"]);
                        $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];                
                        $income_info['ORG_PAY_ID'] = $insert_reund_id;

                        $ProjectIncome_model = D("ProjectIncome");
                        $res = $ProjectIncome_model->add_income_info($income_info);
                        if(!$res)
                        {
                            return false;
                        }
                    }
                }
               
            }
            return $insert_reund_id ? $insert_reund_id : false;
        }
          
       /**
        +----------------------------------------------------------
        *��Ʊ��ϸ
        +----------------------------------------------------------
        * @param  none
        +----------------------------------------------------------
        * @return none
        +----------------------------------------------------------
        */
        public function InvoiceRecord()
        {   
            $billing_model = D("BillingRecord");
            $is_from = !empty($_REQUEST["is_from"]) ? $_REQUEST["is_from"] : 1;
            $contractid = !empty($_GET["parentchooseid"]) ? $_GET["parentchooseid"] : 0;//����ͬID
            $city_id = $_SESSION["uinfo"]["city"]; 
            $city_py = D("Erp_city")->field("PY")->find($city_id);
            $city_py = $city_py["PY"];
            $id = isset($_REQUEST['ID']) ? intval($_REQUEST['ID']) : 0;
            $faction = isset($_REQUEST['faction']) ? strip_tags($_REQUEST['faction']) : '';
            $showForm = isset($_REQUEST['showForm']) ? intval($_REQUEST['showForm']) : '';

            //���ݵ�ǰ��ͬID ��ȡ��Ӧ�İ���ID
            $sql = "select CASE_ID from ERP_INCOME_CONTRACT where ID=".$contractid;
            $case_id = $this->model->query($sql);
            $case_id = $case_id[0]["CASE_ID"]; 
            $date = date("Y-m-d H:i:s");
            
            if($showForm == 3 && $faction == 'saveFormData' && $id == 0)//�û��������  ������Ʊ��¼
            {   
                $taxrate = get_taxrate_by_citypy($city_py); 
				//var_dump($taxrate);die;
                $tax = round($_POST["INVOICE_MONEY"]/(1 + $taxrate) * $taxrate,2);
                if($contractid == 0)
                {
                  $result["status"] = 0;
                  $result["msg"] = "��ѡ���ͬ��¼";
                  echo json_encode(g2u($result));
                  exit;
                }              
                $data["CREATETIME"] = $_POST["CREATETIME"];     
                $data["INVOICE_MONEY"] = $_POST["INVOICE_MONEY"];//���
                $data["REMARK"] = u2g($_POST["REMARK"]);
                $data["TAX"] = $tax;
                $data["STATUS"] = 1;
                $data["CONTRACT_ID"] = $contractid;
                $data["APPLY_USER_ID"] = $_POST["APPLY_USER_ID"];
                $data["CASE_ID"] = $case_id;
                $data["INVOICE_TYPE"] = 1;
				$data["INVOICE_CLASS"] =  $_POST["INVOICE_CLASS"];
				$data["INVOICE_BIZ_TYPE"] =  $_POST["INVOICE_BIZ_TYPE"];

                //var_dump($data);die;
                $res = $billing_model->add_billing_info($data);
                
                if($res)
                {
                   $result["status"] = 2;
                   $result["msg"] = "������Ʊ��¼�ɹ�";
                }
                else
                {
                   $result["status"] = 0;
                   $result["msg"] = "������Ʊ��¼ʧ��";
                }
                echo json_encode(g2u($result));
                exit;
            } 
            elseif($showForm == 1 && $faction == 'saveFormData' && $id > 0)
            {
                $taxrate = get_taxrate_by_citypy($city_py);
                //var_dump($taxrate);die;
                $tax = round($_POST["INVOICE_MONEY"]/(1 + $taxrate) * $taxrate,2);
                $data["INVOICE_MONEY"] = $_POST["INVOICE_MONEY"];//���
                $data["TAX"] = $tax;
                $data["REMARK"] = u2g($_POST["REMARK"]);
				$data["INVOICE_CLASS"] =  $_POST["INVOICE_CLASS"] ;
                $data["INVOICE_BIZ_TYPE"] = $_POST["INVOICE_BIZ_TYPE"];

                $up_num = $billing_model->update_info_by_id($id,$data);
                if($up_num)
                {
                    $result["status"] = 1;
                    $result["msg"] = "�޸Ŀ�Ʊ���ݳɹ���";
                }
                else
                {
                    $result["status"] = 0;
                    $result["msg"] = "�޸Ŀ�Ʊ����ʧ�ܣ���";
                }
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            } else if ($faction === 'delData') { //ɾ����¼

                $delId = intval($_GET['ID']); //ɾ��ID

                //�ж�״̬
                $currentRequisiton = D('BillingRecord')->get_info_by_id($delId, array('FROMLISTID,FROMTYPE,STATUS'));

                if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                    $currentRequisiton[0]['STATUS'] != 1
                ) {
                    $result['msg'] = '�ף���δ���롱�Ŀ�Ʊ��¼����ɾ��Ŷ��';
                    $result['forward'] = U('Advert/displaceApply', $this->_merge_url_param);
                }


                D()->startTrans();
                if ($delId > 0) {

                    //ɾ����ϸ
                    $delBillingRecord = D("BillingRecord")->del_info_by_id($delId);

                    if($currentRequisiton[0]['FROMTYPE']==2){ //��Դ���û���Ʒ�����Ļ�
                        $updateBusinessStatus = D("DisplaceApply")->updateListStatus($currentRequisiton[0]['FROMLISTID'], 1); //����״̬��δ����״̬
                    }

                    if ($delBillingRecord && $updateBusinessStatus!==false) {
                        $result['status'] = 'success';
                        $result['msg'] = g2u('�ף�ɾ���ɹ�!');
                        D()->commit();
                    } else {
                        $result['msg'] = g2u('�ף�ɾ��ʧ��,������!');
                        D()->rollback();
                    }
                }

                //������
                die(@json_encode($result));
            }
            
            Vendor('Oms.Form');
            $form = new Form();
            
            $billing_status = $billing_model->get_invoice_status();
            $billing_status_remark = $billing_model->get_invoice_status_remark();
            $where = "CASE_ID = '".$case_id."'";
            $form->initForminfo(136)->where($where)
                ->setMyField("RELATIVE_CUS", "GRIDVISIBLE", "0")
                ->setMyField("TAX", "GRIDVISIBLE", "0")
                ->setMyField("STATUS", "LISTCHAR",array2listchar($billing_status_remark));
            if($showForm == 1 || $showForm == 3)
            {

                $form->setMyFieldVal("CREATETIME", $date,true)
                    ->setMyFieldVal("STATUS", 1,true)
                    ->setMyFieldVal("APPLY_USER_ID", $_SESSION['uinfo']['uid'],true);
                if( $showForm == 3 )
                {
                    $form->setMyFieldVal("STATUS", "1", true);
                    // ������Ŀ��������Ĭ�Ϸ�Ʊ����
                    $invoiceBizType = D('BillingRecord')->get_invoice_biz_type($case_id);  // todo
                    if ($invoiceBizType) {
                        $form->setMyFieldVal("INVOICE_BIZ_TYPE", $invoiceBizType, false);
                    }
                }
            }

            if($is_from == 3)
            {
                $form->ADDABLE = "-1";
                $form->DELABLE = "-1";
                $form->EDITABLE = "-1";
                $form->GABTN = "<a id='applyInvoice' class='btn btn-info btn-sm' href = 'JavaScript:;'>���뿪Ʊ</a>";
                $form->GABTN .= "<a id='changeInvoice' class='btn btn-info btn-sm' href = 'JavaScript:;'>���뻻Ʊ</a>";
                $form->GABTN .= "<a id='refundInvoice' class='btn btn-info btn-sm' href = 'JavaScript:;'>������Ʊ</a>";
                $form->SHOWCHECKBOX = "-1";
                $form->setMyField('FILES', 'FORMVISIBLE', 0)
                    ->setMyField('FILES', 'GRIDVISIBLE', 0);
                
            }

            // ���ҷ��ճﲻ��������Ʊ��¼
            if ($this->_request('CASE_TYPE') == 'fwfsc') {
                $form->ADDABLE = 0;
            }

            if( $_REQUEST["flowId"])
            {
                $form->DELABLE = 0;
                $form->EDITABLE = 0;   
            }               
            $form->DELCONDITION = '%STATUS% == '.$billing_status["no_apply"];
            $form->EDITCONDITION = '(%FROMTYPE% != 2 AND %STATUS% == '.$billing_status["no_apply"] . ')'; //�û������Ŀ�Ʊ�����ܱ༭ ������
            $this->setPageOptionsVisible($this->_request('CASE_TYPE'), $form);  // ���÷�ҳ��������ز����Ŀɼ�
            $caseType = !empty($_REQUEST['CASE_TYPE']) ? $_REQUEST['CASE_TYPE'] : 'default';
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->invoiceOptions, $caseType);
            $formHtml =  $form->getResult();
            $this->assign("case_id",$case_id);
            $this->assign('form',$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
            $this->assign('contractid',$contractid);
            $this->assign('paramUrl',$this->_merge_url_param);
            $this->display('advert_invoice_records');
        }
        
       /**
        +----------------------------------------------------------
        *����Ʊ����
        +----------------------------------------------------------
        * @param  none
        +----------------------------------------------------------
        * @return none
        +----------------------------------------------------------
        */
        public function applyInvoice()
        {
            //�ж���ѡ��¼�Ƿ��Ѿ����뿪Ʊ
            $billing_model = D("BillingRecord");

            //����POST  ���GET
            $caseid = isset($_POST["CASEID"]) ? $_POST["CASEID"] : $_GET["CASEID"];
            $recordId = isset($_POST["RECORDID"]) ? $_POST["RECORDID"] : $_GET["RECORDID"];
            $invoiceId = isset($_POST["invoiceId"]) ? $_POST["invoiceId"] : $_GET["invoiceId"];

            if ($_GET["flowId"]) {
                $cond_where = "FLOW_ID=" . $_GET["flowId"];
                $invoice_info = $billing_model->get_info_by_cond($cond_where, array("ID"));
                $invoiceId = $invoice_info[0]["ID"];
            }

            $case_info = D("ProjectCase")->get_info_by_id($caseid);
            $scale_type = $case_info[0]['SCALETYPE'];

            $where = " where 1=1 ";
            //����ǻҵ������ݺ�ͬ��������
            $where .= $scale_type == 4?" and  contract_id=$recordId ":" and case_id=$caseid ";

            //����$caseid �ҵ��ð��������еĿ�Ʊ��¼
            if($scale_type == 4) {
                $sql1 = "select MONEY from erp_income_contract where id = $recordId";
            }
            else{
                $sql1 = "select MONEY from erp_income_contract where case_id = $caseid";
            }

            $money = M("Erp_income_contract")->query($sql1);
            $money = $money[0]["MONEY"];//��ͬ���

            $invoice_info = $billing_model->get_info_by_id($invoiceId, array("INVOICE_MONEY", "REMARK"));
            $invoice_money = $invoice_info[0]["INVOICE_MONEY"];//����������
            $remark = $invoice_info[0]["REMARK"];

            $sql3 = "select sum(INVOICE_MONEY) SUM_MONEY from erp_billing_record $where and status IN(4,6,7)";
            $sum_money = $billing_model->query($sql3);
            $sum_money = $sum_money[0]["SUM_MONEY"] ? $sum_money[0]["SUM_MONEY"] : 0;//�ۼƿ�Ʊ���

            $sql4 = "select sum(INVOICE_MONEY) SUM_MONEY from erp_billing_record $where and status IN(2,3,4,6,7)";
            $sum_money_apply = $billing_model->query($sql4);
            $sum_money_apply = $sum_money_apply[0]["SUM_MONEY"] ? $sum_money_apply[0]["SUM_MONEY"] : 0;//�ۼƿ�Ʊ+�����н��

            $billing_status = $billing_model->field("STATUS,ID")->where("ID = $invoiceId")->find($invoiceId);

            $billing_status = $billing_status["STATUS"];
            if($_REQUEST["is_ajax"])
            {
                //״̬�ж�
                if($billing_status != 1)
                {
                     $result["state"] = 0;
                     $result["msg"] = "�Բ��𣬸ü�¼��������ѱ�������������뿪Ʊ";
                     $result["msg"] = g2u($result["msg"]);
                     echo json_encode($result);
                     exit;
                }

                if($_REQUEST["CASE_TYPE"] != "fx")
                {
                    if($money < $sum_money_apply + $invoice_money)
                    {
                        $result["state"] = 0;
                        $result["msg"] = "�ú�ͬ�ۼ�������Ŀ�Ʊ���Ϊ ".$sum_money_apply
                            . " Ԫ��<br />����������Ϊ ".$invoice_money." Ԫ��<br />"
                            . "���������� + �ۼ������� > ��ͬ��� ���������뿪Ʊ��";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                     else
                    {
                        $result["state"] = 1;
                        $result["msg"] = "";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                }
                else
                {
                    $result["state"] = 1;
                    $result["msg"] = "";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
                
            }
            elseif(!$_REQUEST["is_ajax"])
            {
                Vendor('Oms.Form');
                $form = new Form();
                $sql = "select a.id,a.apply_user_id,to_char(a.createtime,'yyyy-MM-dd HH24:mi:ss') CREATETIME,
                        a.case_id,b.contract_no,c.scaletype,d.projectname,d.city_id
                         from erp_billing_record a 
                         left join erp_income_contract b on a.contract_id=b.id
                         left join erp_case c on a.case_id=c.id
                         left join erp_project d on c.project_id=d.id where a.case_id=$caseid and a.id=$invoiceId";

                $form->initForminfo(164);
                $form->SQLTEXT = "($sql)";

                if($_REQUEST["flowId"])
                {
                    $form->GABTN = " ";
                }

                $case_info = D("ProjectCase")->get_info_by_id($caseid,array("SCALETYPE","PROJECT_ID"));
                $scale_type = $case_info[0]["SCALETYPE"];
                $project_id = $case_info[0]["PROJECT_ID"];

                // ����Ƿ���ҵ��
                if($scale_type == self::FX)
                {
                    // ����Ƿ���ҵ�����ӵ�����Ա����
                    $form->GABTN .= "<a id='export_members' class='btn btn-info btn-sm' href='javascript:void(0);'>����������Ա</a>";

                    $cond_where = "CASE_ID = '{$caseid}' AND RELATE_INVOICE_ID = '{$invoiceId}'";
                    $form_m = new Form();
                    $form_m->initForminfo(154)->where($cond_where);
                    $form_m->EDITABLE = 0;
                    $form_m->ADDABLE = 0;
                    $form_m->SHOWBOTTOMBTN = 0;
                    $form_m->GABTN = '';
                    $form_m->SHOWCHECKBOX = 0;
                    /***����֤������***/
                    $member_model = D('Member');
                    $certificate_type_arr = $member_model->get_conf_certificate_type();
                    $form_m->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',array2listchar($certificate_type_arr), FALSE);
                    $conf_invoice_status = $member_model->get_conf_invoice_status_remark();
                    $form_m->setMyField('INVOICE_STATUS', 'LISTCHAR',array2listchar($conf_invoice_status['INVOICE_STATUS']), FALSE);
                    $form_m->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);

                    //�����շѱ�׼
                    $feescale = array();
                    $feescale = D('Project')->get_feescale_by_cid($caseid);

                    $fees_arr = array();
                    if(is_array($feescale) && !empty($feescale) )
                    {
                        foreach($feescale as $key => $value)
                        {
                            $unit = $value['STYPE'] == 0 ? self::UNIT_RMB_YUAN : self::UNIT_PERCENT; // ���BUG #15383
                            $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'] . $unit;
                        }

                        //�����շѱ�׼
                        $form_m->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //�н�Ӷ��
                        $form_m->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
                        //��ҵ����Ӷ��
                        $form_m->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //�н�ɽ���
                        $form_m->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //��ҵ�ɽ�����
                        $form_m->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);

                        //������Ŀ����
                        $project_info = D("PROJECT")->get_info_by_id($project_id);
                        $form_m->setMyFieldVal('PRJ_NAME', $project_info[0]['PROJECTNAME'], 0);
                    }
                    $form_member_distribution = $form_m->getResult();
                }

                //���ó���
                $form->setMyField("CITY_ID", "LISTSQL", "SELECT ID,NAME FROM ERP_CITY");
                $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(), $this->scaleTypeAliasMap[$case_info[0]['SCALETYPE']]);

                $formHtml = $form->getResult();

                //���ID�뷢ƱIDһ��  չ����Ŀ��Ϣ (����ID�������)
                if(!$_REQUEST['ID'] || ($_REQUEST['ID'] && $invoiceId == $_REQUEST['ID'])) {
                    $this->assign('form', $formHtml);
                }
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
                $this->assign('paramUrl',$this->_merge_url_param);
                $this->assign('money',$money);
                $this->assign('invoice_money',$invoice_money);
                $this->assign('sum_money',$sum_money);
                $this->assign('remark',$remark);
                //����Ƿ�����Ա��Ʊ��չʾ������Ա����Ϣ
                $this->assign('scale_type',$scale_type);

                //���ID�뷢ƱID��һ��  չ�ַ�����Ա��Ϣ (����ID�������)
                if(!$_REQUEST['ID'] || ($_REQUEST['ID'] && $invoiceId != $_REQUEST['ID'])) {
                    $this->assign('form_member_distribution', $form_member_distribution);
                }
                $this->display('advert_apply_invoice'); 
            }             
        }




        /**
        +----------------------------------------------------------
         *���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ŀ�Ʊ��¼,��������ͬ��������ϵͳ
        +----------------------------------------------------------
         * @param  $contractnum ��ͬ��
         * @param  $contract_id ��ͬid
        +----------------------------------------------------------
         * @param $citypy ���ڳ���ƴ��
        +----------------------------------------------------------
         */

        public function save_invoice_data($contractnum,$contract_id,$citypy = "nj")
        {
            load("@.contract_common");
            $invoiceRecords = get_invoice_data_by_no($citypy,$contractnum);
            if (empty($invoiceRecords) || (is_array($invoiceRecords) && count($invoiceRecords) == 0)) {
                return true;
            }
            //����ͬ��Ʊ��¼���뵽����ϵͳ�����ݿ���
            if(!empty($invoiceRecords))
            {
                $billing_model = D("BillingRecord");
                $billing_status = $billing_model->get_invoice_status();

                $contract_model = D("Contract");
                $conf_where = "ID = '$contract_id'";
                $field_arr = array("CASE_ID");
                $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
                if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                    $scaleType  = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
                }

                foreach($invoiceRecords as $key=>$val)
                {
                    $taxrate = get_taxrate_by_citypy($citypy);
                    $tax = round($val["money"]/(1 + $taxrate) * $taxrate,2);

                    $invoice_data["INVOICE_MONEY"] = $val["money"];
                    $invoice_data["TAX"] = $tax;
                    $invoice_data["INVOICE_NO"] = $val["invono"];
                    $invoice_data["REMARK"] = $val["note"];
                    $invoice_data["INVOICE_TIME"] = $val["date"];
                    $invoice_data["STATUS"] = $billing_status["have_invoiced"];
                    $invoice_data["CONTRACT_ID"] = $contract_id;
                    $invoice_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                    $invoice_data["CREATETIME"] = $val["date"];
                    $invoice_data["INVOICE_TYPE"] = 1;
                    if ($val['type']) {
                        // ��Ʊ���ͣ������Ʊ���Ͳ�Ϊ1��2���򽫷�Ʊ��������Ϊ�����
                        // ��������Ϊ1�����ѣ���2������ѣ�
                        if (!in_array($val['type'], array(1, 2))) {
                            $val['type'] = 2;
                        }
                        $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                    }
                    $insert_invoice_id = $billing_model->add_billing_info($invoice_data);
                    if(!$insert_invoice_id)
                    {
                        return false;
                    }
                    else
                    {
                        //����������ϸ��¼
                        if ($scaleType == self::YG_SCALETYPE) {
                            $income_info['INCOME_FROM'] = 12;
                        } else if ($scaleType == self::FWFSC_SCALETYPE) {
                            $income_info['INCOME_FROM'] = 23;
                        }
                        $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                        $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                        $income_info['INCOME'] =  $val["money"];
                        $income_info['OUTPUT_TAX'] =  $tax;
                        $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                        $income_info['OCCUR_TIME'] = $val["date"];
                        $income_info['PAY_ID'] = $insert_invoice_id;
                        $income_info['INCOME_REMARK'] = u2g($val["note"]);
                        $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];
                        $income_info['ORG_PAY_ID'] = $insert_invoice_id;

                        $ProjectIncome_model = D("ProjectIncome");
                        $res = $ProjectIncome_model->add_income_info($income_info);
                        if(!$res)
                        {
                            return false;
                        }
                    }
                }
            }
            return $insert_invoice_id ? $insert_invoice_id : false;
        }

        
        /**
        +----------------------------------------------------------
        *  �����ˡ�����Ʊ ��Ӳ�㡢��������ȣ�
        +----------------------------------------------------------
        */
        public function change_refund_invoice()
        {
            //���ؽ����
            $return = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            $invoiceId = isset($_REQUEST['invoiceId'])?$_REQUEST['invoiceId']:0;
            $type =  isset($_REQUEST['type'])?$_REQUEST['type']:'change_invoice';

            $type_info = $type == 'change_invoice'?"��Ʊ":"��Ʊ";

            $invoice_status = D("BillingRecord")->get_invoice_status();

            //����ʼ
            D()->startTrans();

            $flag = false;
            $error_str= '';
            foreach($invoiceId as $key=>$val){
                $ret = M("erp_billing_record")->field("STATUS")->where("ID = $val")
                    ->find();
                //�Ѿ���Ʊ
                if($ret && $ret['STATUS']==$invoice_status['have_invoiced']){
                    $refundAmount = floatval(D('PaymentRecord')->where("BILLING_RECORD_ID = {$val}")->sum('MONEY'));
                    if ($refundAmount <= 0) {
                        //��Ʊ����Ʊ
                        $data['STATUS'] = $type=='change_invoice'?$invoice_status['change_vote']:$invoice_status['refund_vote'];
                        $update = M("erp_billing_record")->where("ID = $val")->save($data);
                        if(!$update)
                            $flag = true;
                    } else {
                        $error_str .= "��" . ($key+1) . "����Ʊ��¼�Ѿ��ؿ��������" . $type_info . "! <br />" ;
                    }

                }
                else
                {
                    $error_str .= "��" . ($key+1) . "������Ʊ��¼״̬������ ! <br />" ;
                }
            }

            //������ڱ���
            if($error_str){
                D()->rollback();
                $return['status'] = false;
                $return['msg'] = g2u($error_str);
                die(@json_encode($return));
            }

            //�������ʧ��
            if($flag){
                D()->rollback();
                $return['status'] = false;
                $return['msg'] = g2u("�Բ�������{$type_info}ʧ�ܣ�");
                die(@json_encode($return));
            }

            D()->commit();
            $return['status'] = true;
            $return['msg'] = g2u("�ף�����{$type_info}�ɹ���");
            die(@json_encode($return));
        }
        
        //�����ؿ��¼����
        public function addRefund($data,$invoiceId = "")
        {
            $payment_model = D("PaymentRecord");
            $this->model->startTrans();
            $insertid = $payment_model->add_refund_records($data);
            if($insertid){
                $result['status'] = 2;
                $result['msg'] = '��ӻؿ��¼�ɹ�';
                $this->model->commit();   
            }else{
                $result['status'] = 0;
                $result['msg'] = '��ӻؿ��¼ʧ��';
                $this->model->rollback();   

            }
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;               
        }
    
        
        /**
        +----------------------------------------------------------
        *�ɹ�����
        +----------------------------------------------------------
        * @param  none
        +----------------------------------------------------------
        * @return none
        +----------------------------------------------------------
        */
        public function show_purchase_requestion(){           
            //�ɹ�����Model
            $purchase_requisition_model = D('PurchaseRequisition');
            //�ɹ���ϸModel
            $purchase_list_model = D("PurchaseList");
            
            $uid = intval($_SESSION['uinfo']['uid']);
            $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
            $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
            $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
            $prj_id = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//��ĿID         
            $scale_type = $_REQUEST["scale_type"];
            
            $case_model = D("ProjectCase");
            $conf_where = "PROJECT_ID=$prj_id and SCALETYPE=$scale_type";
            $field_arr = array("ID");
            $case_id = $case_model->get_info_by_cond($conf_where,$field_arr);
            $case_id = $case_id[0]["ID"]; 
            //echo $this->model->_sql();
            //var_dump($case_id);
            //�����ɹ�����
            if($this->isPost() && !empty($_POST) && $showForm == 3 && 
                $faction == 'saveFormData' && $id == 0 )
            {   
                $requisition = array();
                $requisition['CASE_ID'] = $case_id;
                $requisition['REASON'] = u2g($_POST['REASON']);
                $requisition['USER_ID'] = $uid;
                $dept_id = intval($_SESSION['uinfo']['DEPTID']);
                $requisition['DEPT_ID'] = $dept_id;
                $requisition['APPLY_TIME'] = date('Y-m-d');
                $requisition['END_TIME'] = $this->_post('END_TIME');
                $requisition['PRJ_ID'] = $prj_id;

                //�ɹ���״̬
                $requisition_status = $purchase_requisition_model->get_conf_requisition_status();
                $status = !empty($requisition_status['not_sub']) ? intval($requisition_status['not_sub']) : 0;
                $requisition['STATUS'] = $status;
                $insert_id = $purchase_requisition_model->add_purchase_requisition($requisition);
                
                $result = array();
                if($insert_id > 0)
                {   
                    $result['status'] = 2;
                    $result['forward'] = U('Advert/show_purchase_requestion',$this->_merge_url_param);
                }
                else 
                {   
                    $result['status'] = 0;
                    $result['msg'] = '���ʧ��';
                    $result['forward'] = U('Advert/show_purchase_requestion',$this->_merge_url_param);
                }

                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            //�޸Ĳɹ����뵥
            else if($this->isPost() && !empty($_POST) && $showForm == 1 && $faction == 'saveFormData' && $id > 0 )
            {   
                $result = array();
                //��ǰ�ɹ���״̬��ֻ��û���ύ�Ĳɹ������ܱ༭
                $current_requisiton = array();
                $current_requisiton = $purchase_requisition_model->get_purchase_by_id($id, array('STATUS'));

                //�ɹ���״̬
                $requisition_status = $purchase_requisition_model->get_conf_requisition_status();
                $status = !empty($requisition_status['not_sub']) ? intval($requisition_status['not_sub']) : 0;

                if(is_array($current_requisiton) && !empty($current_requisiton) && 
                        $status != $current_requisiton[0]['STATUS'] )
                {
                    $result['status'] = 0;
                    $result['msg'] = 'δ�ύ�Ĳɹ�������ܱ༭';
                    $result['forward'] = U('Advert/show_purchase_requestion');
                }
                else
                {
                    $requisition = array();
                    $requisition['REASON'] = u2g($_POST['REASON']);
                    $requisition['END_TIME'] = $this->_post('END_TIME');
                    $up_num = 0;
                    $up_num = $purchase_requisition_model->update_purchase_by_id($id, $requisition);

                    if($up_num > 0)
                    {   
                        $result['status'] = 1;
                        $result['forward'] = U('Advert/show_purchase_requestion', $this->_merge_url_param);
                    }
                    else 
                    {   
                        $result['status'] = 0;
                        $result['msg'] = '�޸�ʧ��';
                        $result['forward'] = U('Advert/show_purchase_requestion', $this->_merge_url_param);
                    }
                }

                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            else
            {
                Vendor('Oms.Form');
                $form = new Form();
                $form->initForminfo(133);
                if($showForm == 3)
                {   
                    $input_arr = array(
                       array('name' => 'PRJ_ID', 'val' => $prj_id, 'class' => 'PRJ_ID' )
                    );
                    $form = $form->addHiddenInput($input_arr);
                    $project = D("Project");
                    //��Ŀ����
                    $project_info = $project->get_info_by_id($prj_id);
                    $form = $form->setMyFieldVal('PRJ_NAME', $project_info[0]['PROJECTNAME'], 0);
                }
                //��Ŀ����
                $form = $form->setMyField('PRJ_ID', 'LISTSQL', 'SELECT ID,PROJECTNAME FROM ERP_PROJECT', TRUE);
                
                //������
                $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);
                //״̬
                $requisition_status_remark = $purchase_requisition_model->get_conf_requisition_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($requisition_status_remark), TRUE);

                //���ð�ťչʾ���
                $form->EDITCONDITION = '%STATUS% == 0';
                $form->DELCONDITION = '%STATUS% == 0';   
                $children_data = array( array('�ɹ���ϸ', U('/Purchase/purchase_list',$this->_merge_url_param)) );
                $form->setChildren($children_data);
                $form = $form
                    ->where("CASE_ID = $case_id")
                    ->getResult();
                $this->assign("form",$form);
                $this->assign('contract_type',$_REQUEST["contract_type"]);
                $this->assign('case_id',$case_id);
                $this->assign('paramUrl',$this->_merge_url_param);
                $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
                $this->display("advert_show_purchase_requestion");
            }
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
        public function opinionFlow()
        {   
            $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//��ĿID
            $uid = intval($_SESSION['uinfo']['uid']);
            $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
            $invoiceid = !empty($_GET["invoiceId"]) ? $_GET["invoiceId"] : 0;

            Vendor('Oms.workflow');			
            $workflow = new workflow();            
            $flowId = $_REQUEST['flowId'];
            if($flowId)
            { 
                $billing_model = D("BillingRecord");
                $contract_model = D("Contract");
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
                            $cond_where = "FLOW_ID = ".$flowId;
                            $update_arr = array("STATUS"=>5);
                            $billing_model->update_info_by_cond($cond_where,$update_arr);
                            
                            //����Ƿ����ĺ�ͬ��Ʊ�����̷�����ö�Ӧ������Ա�������±�ѡ�п�Ʊ,�����䷢Ʊ״̬��Ϊδ��
                            $case_id = intval($_REQUEST["CASEID"]);
                            $case_model = D("ProjectCase");
                            $cond_where = "ID = ".$case_id;
                            $case_info = $case_model->get_info_by_cond($cond_where,array("SCALETYPE"));
                            if($case_info[0]["SCALETYPE"] == 2)
                            {
                                $this->updateDistribution($flowId, $case_id, 1);
                            }
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
                        if($str){
                            $cond_where1 = "FLOW_ID = ".$flowId;
                            $update_arr1 = array("STATUS"=>3);
                            $billing_model->update_info_by_cond($cond_where1,$update_arr1);                           
                            $contract_model->update_info_by_id($_REQUEST["RECORDID"], array("IS_NEED_INVOICE"=>1));
                            js_alert('�����ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('����ʧ��');
                        }
                    }
					exit;
                }
            }
            else
            {
                $flowtype_pinyin = "hetongkaipiao";
                $auth = $workflow->start_authority($flowtype_pinyin);
                
                if(!$auth)
                {
                    js_alert('����Ȩ��');
                }
                $form = $workflow->createHtml();
                if($_REQUEST['savedata'])
                {   
                    $flow_data['type'] = $flowtype_pinyin;
                    $flow_data['CASEID'] = $_REQUEST['CASEID'];
                    $flow_data['RECORDID'] = $_REQUEST['RECORDID'];                    
                    $flow_data['INFO'] = strip_tags($_POST['INFO']);
                    $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                    $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                    $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                    $flow_data['FILES'] = $_POST['FILES']; 
                    $flow_data['ISMALL'] =  intval($_POST['ISMALL']); 
                    $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']); 
                    //var_dump($auth);die;                  
                    $str = $workflow->createworkflow($flow_data);
                    if($str)
                    { 
                        $sql = "UPDATE ERP_BILLING_RECORD SET STATUS=2,FLOW_ID = $str WHERE ID=".$invoiceid;
                        $this->model->execute($sql);
                        //����Ƿ����ĺ�ͬ��Ʊ����ת������ҳ��
                        $case_id = intval($_REQUEST["CASEID"]);
                        $case_model = D("ProjectCase");
                        $cond_where = "ID = ".$case_id;
                        $case_info = $case_model->get_info_by_cond($cond_where,array("SCALETYPE"));
                        if($case_info[0]["SCALETYPE"] == 2)
                        {
                            js_alert('�����ɹ�',U('MemberDistribution/open_billing_record',$this->_merge_url_param),1);
                        }
                        else
                        {
                            js_alert('�����ɹ�',U('Advert/contract',$this->_merge_url_param),1); 
                        }                          
                        exit;
                    }
                    else
                    {
                        js_alert('����ʧ��',U('Advert/opinionFlow',$this->_merge_url_param),1);
                        exit;
                    }
                }
            }
            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('current_url', U('Advert/opinionFlow',$this->_merge_url_param));
            $this->assign('contract_type',$_REQUEST["contract_type"]);
            $this->display('advert_opinionFlow');
        }

        /**
         * ������Ӧ��memeber_distribution���¼״̬
         * @param $flowId ������id
         * @param $caseId ����id
         * @param $targetStatus Ŀ��״̬
         */
        private function updateDistribution($flowId, $caseId, $targetStatus)
        {
            if (empty($flowId) || empty($caseId)) {
                return;
            }
            $aBillingRec = D('BillingRecord')->where('FLOW_ID = ' . $flowId)->find();
            if (!empty($aBillingRec)) {
                $relateInvoiceID = $aBillingRec['ID'];
                $memberDistributionModel = D("MemberDistribution");
                $cond_where = "CASE_ID = $caseId AND RELATE_INVOICE_ID = $relateInvoiceID";
                $update_arr = array("INVOICE_STATUS" => $targetStatus,"RELATE_INVOICE_ID"=>NULL);
                $memberDistributionModel->startTrans();
                $result = $memberDistributionModel->update_info_by_cond($update_arr,$cond_where);
                if ($result !== false) {
                    $memberDistributionModel->commit();
                } else {
                    $memberDistributionModel->rollback();
                }

            }
        }

        /**
         * ���÷�ҳ�����Ĳ����Ƿ�ɼ�
         * @param $caseType
         * @param $form
         */
        private function setPageOptionsVisible($caseType, &$form)
        {
            if (empty($caseType) || empty($form)) {
                return;
            }

            // Ӳ��Ͷ�����������������ɼ�
            if ($caseType == self::YG_CASE_ALIAS || $caseType == self::HD_CASE_ALIAS) {
                $form->ADDABLE = 0;
            }
        }
    }