<?php

/**
 * �ɹ�������
 *
 * @author liuhu
 */
class PurchaseAction extends ExtendAction {
    /**
     * �����ʽ����Ŀ
     */
    const NOT_FUND_POOL_PROJECT = 1;

    /**
     * �Ǹ��ֳɱ�����
     */
    const NON_CASH_COST_ADD = 499;

    /**
     * ���ύ��������Ȩ��
     */
    const NON_CASH_COST_COMMIT = 500;

    /**
     * ���ڲɹ��ύ�ɹ�����Ȩ��
     */
    const SUB_PURCHASE = 336;

    /**
     * ���ڲɹ��ɹ�����ͼȨ��
     */
    const SHOW_FLOW_STEP = 0;

    /**
     * ��ѯ�ɹ�����FlowID��SQL
     */
    const PURCHASE_FLOWID_SQL = <<<PURCHASE_FLOWID_SQL
        SELECT ID
        FROM ERP_FLOWS T
        WHERE T.FLOWSETID = 8
          AND T.RECORDID = %d
PURCHASE_FLOWID_SQL;

    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();
	private $allow = array('2','4');

    /**
     * ���ֽ�֧�����͹�����
     */
    const NON_CASH_COST_TYPE = 'feifuxianchengbenshenqing';

    //���캯��
    public function __construct() {
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'non_cash_cost_commit' => self::NON_CASH_COST_COMMIT,
            'sub_purchase' => array(
                'ds' => 203,
                'fwfsc' => 558,
                'fx'=>515,
                'xmxhd'=>477,
                'yg'=>536,
                'hd'=>548,
                //���ڲɹ�
                'dzcg'=>336,
            ),
        );
        parent::__construct();

        //TAB URL����
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? intval($_GET['prjid']) : 0;
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
        !empty($_GET['purchase_id']) ? $this->_merge_url_param['purchase_id'] = intval($_GET['purchase_id']) : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = intval($_GET['RECORDID']) : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = intval($_GET['flowId']) : '';
        !empty($_GET['is_from']) ? $this->_merge_url_param['is_from'] = strip_tags($_GET['is_from']) : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
    }


    /**
     * +----------------------------------------------------------
     * �ɹ�����
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function purchase_manage() {
        $uid = intval($_SESSION['uinfo']['uid']);
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $prj_id = $this->_merge_url_param['prjid'] ;//��ĿID
        $case_type = $this->_merge_url_param['CASE_TYPE'];
        $city_id = $this->channelid;
        $project = D('Project');

        //�ɹ����뵥MODEL
        $purchase_model = D('PurchaseRequisition');

        //��Ӳɹ�����
        if (!empty($_POST) && $faction == 'saveFormData' && $id == 0) {
            $requisition = array();
            $prj_id = $this->_post('PRJ_ID');
            $case_id = $this->_post('CASE_ID');
            $case_model = D('ProjectCase');

            if ($case_id == 0) {
                $case_info = $case_model->get_info_by_pid($prj_id, $case_type);
                $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            }

            /***�ɹ�����Ҫ�ж�ҵ�������Ƿ���ִ��״̬***/
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_id($case_id, array('FSTATUS'));

            //������Ϣ
            if (is_array($case_info) && !empty($case_info)) {
                if (!in_array($case_info[0]['FSTATUS'],$this->allow)   ) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�ɹ��������ʧ��,ҵ�����Ͳ���ִ��״̬���޷������ɹ�����');
                    $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
                    echo json_encode($result);
                    exit;
                }
            }

            if (in_array($case_type, array('yg' ))) {
                //û�к�ͬ��Ϣ�޷������ɹ�
                $contract_model = D('Contract');
                $contract_info = array();
                $contract_info = $contract_model->get_contract_info_by_caseid($case_id);

                if (empty($contract_info)) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�ɹ��������ʧ��,δ��Ӻ�ͬ�޷�������');
                    $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
                    echo json_encode($result);
                    exit;
                }
            }

            $requisition['CASE_ID'] = $case_id;
            $requisition['REASON'] = u2g($_POST['REASON']);
            $requisition['USER_ID'] = $uid;
            $dept_id = intval($_SESSION['uinfo']['DEPTID']);
            $requisition['DEPT_ID'] = $dept_id;
            $requisition['APPLY_TIME'] = date('Y-m-d H:i:s');
            $requisition['END_TIME'] = $this->_post('END_TIME');
            $requisition['PRJ_ID'] = $prj_id;
            $purchase_type = $purchase_model->get_conf_purchase_type();
            $requisition['TYPE'] = intval($purchase_type['project_purchase']);
            $requisition['CITY_ID'] = $city_id;

            //�ɹ���״̬
            $requisition_status = $purchase_model->get_conf_requisition_status();
            $status = !empty($requisition_status['not_sub']) ? intval($requisition_status['not_sub']) : 0;
            $requisition['STATUS'] = $status;
            $insert_id = $purchase_model->add_purchase_requisition($requisition);

            $result = array();
            if ($insert_id > 0) {
                $result['status'] = 2;
                $result['msg'] = '�ɹ�������ӳɹ�';
                userLog()->writeLog( $insert_id, $_SERVER["REQUEST_URI"],  '�ɹ�������ӳɹ�', serialize($requisition));
                $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
            } else {
                $result['status'] = 0;
                $result['msg'] = '�ɹ��������ʧ��';
                $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
                userLog()->writeLog($insert_id, $_SERVER["REQUEST_URI"],  '�ɹ��������ʧ��', serialize($requisition));
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } else if (!empty($_POST) && $faction == 'saveFormData' && $id > 0) {
            $result = array();

            //��ǰ�ɹ���״̬��ֻ��û���ύ�Ĳɹ������ܱ༭
            $current_requisiton = array();
            $current_requisiton = $purchase_model->get_purchase_by_id($id, array('CASE_ID,STATUS'));

            /***�ɹ�����Ҫ�ж�ҵ�������Ƿ���ִ��״̬***/
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_id($current_requisiton[0]['CASE_ID'], array('FSTATUS'));

            //������Ϣ
            if (is_array($case_info) && !empty($case_info)) {
                if ( !in_array($case_info[0]['FSTATUS'],$this->allow) ) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('�ɹ������޸�ʧ��,ҵ�����Ͳ���ִ��״̬���޷��޸Ĳɹ�����');
                    $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
                    echo json_encode($result);
                    exit;
                }
            }

            //�ɹ���״̬
            $requisition_status = $purchase_model->get_conf_requisition_status();
            $status = !empty($requisition_status['not_sub']) ? intval($requisition_status['not_sub']) : 0;

            if (is_array($current_requisiton) && !empty($current_requisiton) &&
                $status != $current_requisiton[0]['STATUS']
            ) {
                $result['status'] = 0;
                $result['msg'] = 'δ�ύ�Ĳɹ�������ܱ༭';
                $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
            } else {
                $requisition = array();
                $requisition['REASON'] = u2g($_POST['REASON']);
                $requisition['END_TIME'] = $this->_post('END_TIME');
                $up_num = 0;
                $up_num = $purchase_model->update_purchase_by_id($id, $requisition);

                if ($up_num > 0) {
                    $result['status'] = 1;
                    $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
                } else {
                    $result['status'] = 0;
                    $result['msg'] = '�޸�ʧ��';
                    $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
                }
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } else if ($faction == 'delData') {
            $del_id = intval($_GET['ID']);

            if ($del_id > 0) {
                //ɾ����ϸ
                $purchase_list_model = D('PurchaseList');
                $del_purchase_list = $purchase_list_model->del_purchase_list_by_pr_ids($del_id);

                //ɾ�����뵥
                $del_purchase = $purchase_model->del_purchase_by_ids($del_id);

                if ($del_purchase) {
                    $info['status'] = 'success';
                    $info['msg'] = g2u('ɾ���ɹ�');
                } else {
                    $info['status'] = 'error';
                    $info['msg'] = g2u('ɾ��ʧ��');
                }
            } else {
                $info['status'] = 'error';
                $info['msg'] = g2u('��������ɾ��ʧ��');
            }


            echo json_encode($info);
            exit;
        } else {
            Vendor('Oms.Form');
            $form = new Form();

            //����MODEL
            $case_model = D('ProjectCase');
            $case_type = $this->_merge_url_param['CASE_TYPE'];

            $case_id = 0;
            if ($case_type != '' && $prj_id > 0) {
                $case_id = $this->_merge_url_param['CASEID'];

                if ($case_id > 0) {
                    $cond_where = "CASE_ID = '" . $case_id . "'";
                } else {
                    $case_info = $case_model->get_info_by_pid($prj_id, $case_type);
                    $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
                    $cond_where = "CASE_ID = '" . $case_id . "'";
                }
            } else if (!empty($this->_merge_url_param['RECORDID'])) {
                $id = !empty($this->_merge_url_param['RECORDID']) ? intval($this->_merge_url_param['RECORDID']) : 0;
                $cond_where = "ID = '" . $id . "'";
            }

            $form = $form->initForminfo(133)->where($cond_where); 
            if ($showForm >= 1) {
                $input_arr = array(
                    array('name' => 'PRJ_ID', 'val' => $prj_id, 'class' => 'PRJ_ID'),
                    array('name' => 'CASE_ID', 'val' => $case_id, 'class' => 'CASE_ID')
                );
                $form = $form->addHiddenInput($input_arr);

                if($showForm==3){
					//��Ŀ����
					$project_info = $project->get_info_by_id($prj_id); 
					$form = $form->setMyFieldVal('PRJ_ID', $prj_id, TRUE);
				}
            } else {
				if ($case_type=='yg') {
					$form->SQLTEXT = '(select A.*,B.contract_no as CONTRACT_NUM from ERP_PURCHASE_REQUISITION  a left join ERP_INCOME_CONTRACT b on a.CASE_ID = b.CASE_ID )';
					$form = $form->setMyField('CONTRACT_NUM','ISVIRTUAL','0',true);
				}elseif ($case_type=='hd') {  
					if($prj_id){
						$temlist = M('Erp_purchase_requisition')->where('PRJ_ID='.$prj_id)->select();
						foreach($temlist as $one){
							$temp[] = $one['CASE_ID'];
						}
						$strr = implode(',',$temp);  
					}else  $strr = $_REQUEST['CASEID'];
					$onecontract = M('Erp_income_contract')->where("CASE_ID in ($strr)")->order("ID DESC")->find();
					$form->SQLTEXT = 'ERP_PURCHASE_REQUISITION';
					$form = $form->setMyFieldVal('CONTRACT_NUM',$onecontract['CONTRACT_NO'],true);  
					$form = $form->setMyField('CONTRACT_NUM','ISVIRTUAL','-1',true);
				}else{
					$form->SQLTEXT = '(select A.*,B.contract_num from ERP_PURCHASE_REQUISITION  a left join ERP_HOUSE b on a.PRJ_ID = b.PROJECT_ID) ';
					$form = $form->setMyField('CONTRACT_NUM','ISVIRTUAL','0',true);
				}
				
                //��Ŀ����
                $form = $form->setMyField('PRJ_ID', 'LISTSQL', 'SELECT ID, PROJECTNAME FROM ERP_PROJECT', TRUE);
                //������
                $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                //״̬
                $requisition_status_remark = $purchase_model->get_conf_requisition_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($requisition_status_remark), TRUE);

                if ($this->_merge_url_param['flowId'] > 0) {
                    //��������ڱ༭Ȩ��
                    $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

                    if ($flow_edit_auth) {
                        $form->EDITABLE = -1;   //����༭
                        $form->ADDABLE = '0';   //����������
                        $form->GABTN = '';      //���������ύ��ť
                    } else {
                        $form->DELCONDITION = '1==0';   //������ɾ��
                        $form->EDITCONDITION = '1==0';  //������༭
                        $form->ADDABLE = '0';   //����������
                        $form->GABTN = '';  //������ť����
                    }
                } else {
                    //���ð�ťչʾ���
                    $form->EDITCONDITION = '%STATUS% == 0';
                    $form->DELCONDITION = '%STATUS% == 0';
                }
            }

            $children_data = array(array('�ɹ���ϸ', U('/Purchase/purchase_list', $this->_merge_url_param)));
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(), $case_type);
            $formHtml = $form->setChildren($children_data)->getResult();
            $this->assign('form', $formHtml);

            $this->assign('isShowOptionBtn', $this->isShowOptionBtn($case_id));
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));

            //�һ��˵�contextMenu
            $contextMenu = $this->getContextMenu($case_id);
            $this->assign('CONTEXT_MENU', $contextMenu);
            $this->display('purchase_manage');
        }
    }


    /**
     * +----------------------------------------------------------
     * ���ڲɹ�����
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function bulk_purchase_manage() {
        $uid = intval($_SESSION['uinfo']['uid']);
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $city_id = $this->channelid;
        $project = D('Project');

        //�ɹ����뵥MODEL
        $purchase_model = D('PurchaseRequisition');
        $purchase_type = $purchase_model->get_conf_purchase_type();

        //��Ӳɹ�����
        if (!empty($_POST) && $faction == 'saveFormData' && $id == 0) {
            $requisition = array();
            $requisition['REASON'] = u2g($_POST['REASON']);
            $requisition['USER_ID'] = $uid;
            $dept_id = intval($_SESSION['uinfo']['DEPTID']);
            $requisition['DEPT_ID'] = $dept_id;
            $requisition['APPLY_TIME'] = date('Y-m-d H:i:s');
            $requisition['TYPE'] = intval($purchase_type['bulk_purchase']);
            $requisition['CITY_ID'] = $city_id;
            $requisition['CASE_ID'] = -1;
            $requisition['PRJ_ID'] = 0;

            //�ɹ���״̬
            $requisition_status = $purchase_model->get_conf_requisition_status();
            $status = !empty($requisition_status['not_sub']) ? intval($requisition_status['not_sub']) : 0;
            $requisition['STATUS'] = $status;
            $insert_id = $purchase_model->add_purchase_requisition($requisition);

            $result = array();
            if ($insert_id > 0) {
                $result['status'] = 2;
                $result['msg'] = '�ɹ�������ӳɹ�';
                $result['forward'] = U('Purchase/bulk_purchase_manage');
            } else {
                $result['status'] = 0;
                $result['msg'] = '�ɹ��������ʧ��';
                $result['forward'] = U('Purchase/bulk_purchase_manage');
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } else if (!empty($_POST) && $faction == 'saveFormData' && $id > 0) {
            $result = array();
            //��ǰ�ɹ���״̬��ֻ��û���ύ�Ĳɹ������ܱ༭
            $current_requisiton = array();
            $current_requisiton = $purchase_model->get_purchase_by_id($id, array('STATUS'));

            //�ɹ���״̬
            $requisition_status = $purchase_model->get_conf_requisition_status();
            $status = !empty($requisition_status['not_sub']) ? intval($requisition_status['not_sub']) : 0;

            if (is_array($current_requisiton) && !empty($current_requisiton) &&
                $status != $current_requisiton[0]['STATUS']
            ) {
                $result['status'] = 0;
                $result['msg'] = 'δ�ύ�Ĳɹ�������ܱ༭';
                $result['forward'] = U('Purchase/bulk_purchase_manage');
            } else {
                $requisition = array();
                $requisition['REASON'] = u2g($_POST['REASON']);
                $up_num = 0;
                $up_num = $purchase_model->update_purchase_by_id($id, $requisition);

                if ($up_num > 0) {
                    $result['status'] = 1;
                    $result['msg'] = '�޸ĳɹ�';
                    $result['forward'] = U('Purchase/bulk_purchase_manage', $this->_merge_url_param);
                } else {
                    $result['status'] = 0;
                    $result['msg'] = '�޸�ʧ��';
                    $result['forward'] = U('Purchase/bulk_purchase_manage', $this->_merge_url_param);
                }
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } else if ($faction == 'delData') {
            $del_id = intval($_GET['ID']);

            if ($del_id > 0) {
                //ɾ����ϸ
                $purchase_list_model = D('PurchaseList');
                $del_purchase_list = $purchase_list_model->del_purchase_list_by_pr_ids($del_id);

                //ɾ�����뵥
                $del_purchase = $purchase_model->del_purchase_by_ids($del_id);


                if ($del_purchase) {
                    $info['status'] = 'success';
                    $info['msg'] = g2u('ɾ���ɹ�');
                } else {
                    $info['status'] = 'error';
                    $info['msg'] = g2u('ɾ��ʧ��');
                }
            } else {
                $info['status'] = 'error';
                $info['msg'] = g2u('��������ɾ��ʧ��');
            }


            echo json_encode($info);
            exit;
        } else {
            Vendor('Oms.Form');
            $form = new Form();

            //��ѯ����
            if ($this->_merge_url_param['RECORDID'] > 0) {
                $cond_where = "ID = '" . $this->_merge_url_param['RECORDID'] . "'";
            } else {
                $cond_where = "USER_ID = '" . $uid . "' AND CITY_ID = '" . $city_id . "' "
                    . " AND TYPE = '" . $purchase_type['bulk_purchase'] . "'";
            }

            $form = $form->initForminfo(133)->where($cond_where);

            //��Ŀ���Ʋ���ʾ
            $form->setMyField('PRJ_NAME', 'FORMVISIBLE', '0', TRUE);
            $form->setMyField('PRJ_NAME', 'GRIDVISIBLE', '0', TRUE);
            $form->setMyField('PRJ_ID', 'FORMVISIBLE', '0', TRUE);
            $form->setMyField('PRJ_ID', 'GRIDVISIBLE', '0', TRUE);

            //���������ʹ�ʱ�䲻��ʾ
            $form->setMyField('END_TIME', 'GRIDVISIBLE', '0', TRUE);
            $form->setMyField('END_TIME', 'FORMVISIBLE', '0', TRUE);

            //������
            $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);

            //״̬
            $requisition_status_remark = $purchase_model->get_conf_requisition_status_remark();
            $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($requisition_status_remark), TRUE);

            if ($this->_merge_url_param['flowId'] > 0) {
                //��������ڱ༭Ȩ��
                $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

                if ($flow_edit_auth) {
                    $form->EDITABLE = -1;   //����༭
                    $form->ADDABLE = '0';   //����������
                    $form->GABTN = '';      //���������ύ��ť
                } else {
                    $form->DELCONDITION = '1==0';   //������ɾ��
                    $form->EDITCONDITION = '1==0';  //������༭
                    $form->ADDABLE = '0';   //����������
                    $form->GABTN = '';  //������ť����
                }
            } else {
                //���ð�ťչʾ���
                $form->EDITCONDITION = '%STATUS% == 0';
                $form->DELCONDITION = '%STATUS% == 0';
            }

            $children_data = array(array('�ɹ���ϸ', U('/Purchase/purchase_list', $this->_merge_url_param)));
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(), 'dzcg');
            $formhtml = $form->setChildren($children_data)->getResult();
        }

        $this->assign('form', $formhtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs(6, $this->_merge_url_param));
        $this->display('bulk_purchase_manage');

    }


    /**
     * +----------------------------------------------------------
     * �ɹ���ϸ
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function purchase_list() {
        $uid = intval($_SESSION['uinfo']['uid']);
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $purchase_requisition_id = isset($_GET['parentchooseid']) ?
            intval($_GET['parentchooseid']) : 0;
        //�ɹ����뵥MODEL
        $purchase_model = D('PurchaseRequisition');

        //�ɹ���ϸMDOEL
        $purchase_list_model = D('PurchaseList');

        //��Ӳɹ�����
        if (!empty($_POST) && $faction == 'saveFormData' && $id == 0) {
            $purchase_info = array();
            $pr_id = intval($_POST['PR_ID']);

            //��ѯ�ɹ����뵥����
            $purchase_type_info = array();
            $purchase_type_info = $purchase_model->get_purchase_by_id($pr_id, array('TYPE', 'CASE_ID', 'USER_ID', 'CITY_ID'));

            /***�жϲɹ���Ϣ�Ƿ���Ч***/
            if (empty($purchase_type_info) || empty($purchase_type_info[0]['TYPE'])) {
                $result['status'] = 0;
                $result['msg'] = g2u('�ɹ���ϸ���ʧ�ܣ��ɹ������޷�ȷ�ϣ�');
                echo json_encode($result);
                exit;
            }

            /***�ɹ�����Ҫ�ж�ҵ�������Ƿ���ִ��״̬***/
            if (is_array($purchase_type_info) && !empty($purchase_type_info)) {
                $case_id = intval($purchase_type_info[0]['CASE_ID']);
                $case_model = D('ProjectCase');
                $case_info = $case_model->get_info_by_id($case_id, array('FSTATUS'));

                //������Ϣ
                if (is_array($case_info) && !empty($case_info)) {
                    if (!in_array($case_info[0]['FSTATUS'],$this->allow)) {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�ɹ���ϸ�������޸�ʧ��,ҵ�����Ͳ���ִ��״̬���޷��������޸Ĳɹ���ϸ');
                        $result['forward'] = U('Purchase/purchase_list', $this->_merge_url_param);
                        echo json_encode($result);
                        exit;
                    }
                }
            }

            $purchase_info['PR_ID'] = $pr_id;
            $purchase_info['BRAND'] = u2g(strip_tags($_POST['BRAND']));
            $purchase_info['MODEL'] = u2g(strip_tags($_POST['MODEL']));
            $purchase_info['PRODUCT_NAME'] = u2g(strip_tags($_POST['PRODUCT_NAME']));
            $purchase_info['NUM_LIMIT'] = floatval($_POST['NUM_LIMIT']);
            $purchase_info['PRICE_LIMIT'] = floatval($_POST['PRICE_LIMIT']);
            $purchase_info['FEE_ID'] = intval($_POST['FEE_ID']);
            $purchase_info['IS_FUNDPOOL'] = intval($_POST['IS_FUNDPOOL']);
            $purchase_info['IS_KF'] = intval($_POST['IS_KF']);
            $purchase_info['P_ID'] = intval($_POST['P_ID']);
            $purchase_type = intval($purchase_type_info[0]['TYPE']);
            $purchase_info['TYPE'] = intval($purchase_type);
            $purchase_info['CASE_ID'] = intval($purchase_type_info[0]['CASE_ID']);
            $purchase_info['ADD_TIME'] = date('Y-m-d H:i:s');
            $purchase_info['APPLY_USER_ID'] = $uid;
            $purchase_info['CITY_ID'] = intval($purchase_type_info[0]['CITY_ID']);

            //��Ӳɹ���ϸ��Ϣ
            $insert_id = $purchase_list_model->add_purchase_list($purchase_info);
			
			if($_POST['IS_FUNDPOOL']){
				$tprice = $purchase_info['PRICE_LIMIT']* $purchase_info['NUM_LIMIT'];
				//��֧��ҵ��Ѵ���
				$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$purchase_info['CASE_ID']."' and TYPE=1";
				$finalaccounts = M()->query($sql);
				$xgfee = $finalaccounts[0]['TOBEPAID_FUNDPOOL'] > $tprice ? $finalaccounts[0]['TOBEPAID_FUNDPOOL']-$tprice  : 0;
				if($xgfee!=$finalaccounts[0]['TOBEPAID_FUNDPOOL'] && $finalaccounts[0]['STATUS']==2){
					D('Erp_finalaccounts')->where("CASE_ID='".$purchase_info['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_FUNDPOOL'=>$xgfee) );
				}
			}

            if ($insert_id > 0) {
                $result['status'] = 2;
                $result['msg'] = '�ɹ���ϸ��ӳɹ�';
            } else {
                $result['status'] = 0;
                $result['msg'] = '�ɹ���ϸ���ʧ��';
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } else if ($faction == 'saveFormData' && $id > 0) {
            //��ѯ��ǰ�ɹ���ϸ��Ϣ
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($id, array('CASE_ID'));

            /***�ɹ�����Ҫ�ж�ҵ�������Ƿ���ִ��״̬***/
            if (is_array($purchase_list_info) && !empty($purchase_list_info)) {
                $case_id = intval($purchase_list_info[0]['CASE_ID']);
                $case_model = D('ProjectCase');
                $case_info = $case_model->get_info_by_id($case_id, array('FSTATUS'));

                //������Ϣ
                if (is_array($case_info) && !empty($case_info)) {
                    if (!in_array($case_info[0]['FSTATUS'],$this->allow)) {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�ɹ���ϸ�޸�ʧ��,ҵ�����Ͳ���ִ��״̬���޷��޸Ĳɹ���ϸ');
                        $result['forward'] = U('Purchase/purchase_list', $this->_merge_url_param);
                        echo json_encode($result);
                        exit;
                    }
                }
            }
			$purchase_list = $purchase_list_model->get_purchase_list_by_id($id );
			
            $purchase_info = array();
            $purchase_info['BRAND'] = u2g(strip_tags($_POST['BRAND']));
            $purchase_info['MODEL'] = u2g(strip_tags($_POST['MODEL']));
            $purchase_info['PRODUCT_NAME'] = u2g(strip_tags($_POST['PRODUCT_NAME']));
            $purchase_info['NUM_LIMIT'] = floatval($_POST['NUM_LIMIT']);
            $purchase_info['PRICE_LIMIT'] = floatval($_POST['PRICE_LIMIT']);
            $purchase_info['FEE_ID'] = intval($_POST['FEE_ID']);
            $purchase_info['IS_FUNDPOOL'] = intval($_POST['IS_FUNDPOOL']);
            $purchase_info['IS_KF'] = intval($_POST['IS_KF']);
            $purchase_info['P_ID'] = intval($_POST['P_ID']);

           
			//���²ɹ���ϸ��Ϣ
            $update_num = 0;
            $update_num = $purchase_list_model->update_purchase_list_by_id($id, $purchase_info);
			
			/*$oldtprice = $purchase_list[0]['NUM_LIMIT']* $purchase_list[0]['PRICE_LIMIT'] 
			$tprice = $purchase_info['PRICE_LIMIT']* $purchase_info['NUM_LIMIT'];

			if($oldtprice!=$tprice){
			
				//��֧��ҵ��Ѵ���
				$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$purchase_info['CASE_ID']."' and TYPE=1";
				$finalaccounts = M()->query($sql);
				$xgfee = $finalaccounts[0]['TOBEPAID_FUNDPOOL'] > tprice ? $finalaccounts[0]['TOBEPAID_FUNDPOOL']-$tprice  : 0;
				if($xgfee!=$finalaccounts[0]['TOBEPAID_FUNDPOOL']){
					D('Erp_finalaccounts')->where("CASE_ID='".$purchase_info['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_FUNDPOOL'=>$xgfee) );
				}
			}
*/
            if ($update_num > 0) {
                $result['status'] = 1;
                $result['msg'] = '�޸ĳɹ�';
            } else {
                $result['status'] = 0;
                $result['msg'] = '�޸�ʧ��';
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } else {
            Vendor('Oms.Form');
            $form = new Form();
            $cond_where = " PR_ID = '" . $purchase_requisition_id . "'";
            $form = $form->initForminfo(137)->where($cond_where);

            //������Ŀ�Ƿ�Ϊ�ʽ����Ŀ
            $is_fundpool = FALSE;

            //��ѯ�ɹ����뵥����
            $purchase_type_info = array();
            $purchase_type_info =
                $purchase_model->get_purchase_by_id($purchase_requisition_id, array('CASE_ID', 'TYPE', 'STATUS', 'PRJ_ID'));

            //�ɹ���������
            $purchase_type = $purchase_model->get_conf_purchase_type();


            //ҵ��ɹ���ʾ���ÿ������ֶ�
            if (!empty($purchase_type_info) && !empty($purchase_type) &&
                $purchase_type_info[0]['TYPE'] == $purchase_type['project_purchase']
            ) {
                $project_case_model = D('ProjectCase');
                $case_id = $purchase_type_info[0]['CASE_ID'];

                //��������
                if ($showForm > 0) {
                    //������������
                    $case_type_self = $project_case_model->get_casetype_by_caseid($case_id);

                    //����ǻ���͵Ĳɹ����ݻԤ����ʾ��������
                    if ($case_type_self == 'hd' || $case_type_self == 'xmxhd') {
                        //��ѯ�����Ԥ��
                        $hd_ys_fee_arr = M('Erp_actibudgetfee')
                            ->field("FEE_ID")
                            ->where("CASE_ID = '" . $case_id . "' AND ISVALID = '-1'")
                            ->select();

                        if (!empty($hd_ys_fee_arr) && is_array($hd_ys_fee_arr)) {
                            $hd_ys_fee_id_str = '';
                            foreach ($hd_ys_fee_arr as $key => $value) {
                                $hd_ys_fee_id_str .= $hd_ys_fee_id_str != '' ? ',' . $value['FEE_ID'] : $value['FEE_ID'];
                            }

                            $cond_where = "ID IN ($hd_ys_fee_id_str)";
                        } else {
                            $cond_where = "ID = 0";
                        }
						$FEE_ID_EDITTYPE  =1;
                        //��������
						$form->setMyField('FEE_ID', 'EDITTYPE', '21', FALSE);
                        $form->setMyField('FEE_ID', 'LISTSQL', "SELECT ID, NAME  FROM ERP_FEE WHERE " . $cond_where . " AND ISVALID = -1 AND ISONLINE = 0", FALSE);
                    } else {
                        //��������(���νṹ)
                        $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
                        $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
                            . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
                    }
                } else {
                    //��������
                    $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                        . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
                }

                /***���òɹ����Ͳ���ʾ***/
                $form->setMyField('TYPE', 'GRIDVISIBLE', '0', FALSE);

                //�����˿��������˿�״̬��ʾ
                $form->setMyField('BACK_STOCK_STATUS', 'GRIDVISIBLE', '-1', TRUE);

                $conf_back_stock_status = $purchase_list_model->get_conf_back_stock_status_remark();
                $form->setMyField('BACK_STOCK_STATUS', 'LISTCHAR', array2listchar($conf_back_stock_status), TRUE);
                $form->setMyField('STOCK_NUM', 'GRIDVISIBLE', '-1', TRUE);

                /***���ò�����ť***/
                $form->GABTN = '<a id="change_buyer" href="javascript:;" class="btn btn-info btn-sm">����ɹ���</a>'
                    . '<a id="return_stock" href="javascript:;" class="btn btn-info btn-sm">�����˿�</a>'
                    . '<a id="abandon_purchase" href="javascript:;" class="btn btn-info btn-sm">�����ɹ�</a>'
                    . '<a id="view_task_details" href="javascript:;" class="btn btn-info btn-sm">�鿴������ϸ</a>';

                if ($showForm > 0) {
                    /***���òɹ����Ͳ���ʾ***/
                    $form->setMyField('TYPE', 'GRIDVISIBLE', '0', FALSE);

                    //����CASEID��ѯ�ɹ�������������
                    $case_type = $project_case_model->get_casetype_by_caseid($case_id, 1);

                    /***�������Ͱ��������ʽ�����ȷ�����ʽ�ء��۷�ѡ�***/
                    if ($case_type == 'ds') {
                        $prj_id = $purchase_type_info[0]['PRJ_ID'];
                        $house_model = D('House');
                        $is_fundpool = $house_model->get_isfundpool_by_prjid($prj_id);

                        if (!$is_fundpool) {
                            //�Ƿ��ʽ��
                            $form->setMyField('IS_FUNDPOOL', 'DEFAULTVALUE', 0, TRUE);

                            //�Ƿ�۷�
                            // $form->setMyField('IS_KF', 'DEFAULTVALUE', 0, TRUE);
                        }
                    } else {
                        //�Ƿ��ʽ��
                        $form->setMyField('IS_FUNDPOOL', 'DEFAULTVALUE', 0, TRUE);

                        //�Ƿ�۷�
                        // $form->setMyField('IS_KF', 'DEFAULTVALUE', 1, FALSE);
                    }
                }
            } else if (!empty($purchase_type_info) && !empty($purchase_type) &&
                $purchase_type_info[0]['TYPE'] == $purchase_type['bulk_purchase'])
            {
                //��������
                if ($showForm > 0) {  
                    //��������(���νṹ)
                    $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
                    $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
                        . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
                } else {
                    //��������
                    $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                        . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
                }

                /***���òɹ����Ͳ���ʾ***/
                $form->setMyField('TYPE', 'GRIDVISIBLE', '0', FALSE);

                /***�������ÿ�治��ʾ***/
                $form->setMyField('USE_TOATL_PRICE', 'GRIDVISIBLE', '0', FALSE);
                $form->setMyField('USE_NUM', 'GRIDVISIBLE', '0', FALSE);

                //����������ʾ
                $form->setMyField('FEE_ID', 'FORMVISIBLE', '-1', false);
                $form->setMyField('FEE_ID', 'GRIDVISIBLE', '-1', false);

                //�Ƿ��ʽ��
                $form->setMyField('IS_FUNDPOOL', 'FORMVISIBLE', '0', TRUE);
                $form->setMyField('IS_FUNDPOOL', 'GRIDVISIBLE', '0', TRUE);

                //�Ƿ�۷�
                $form->setMyField('IS_KF', 'FORMVISIBLE', '0', TRUE);
                $form->setMyField('IS_KF', 'GRIDVISIBLE', '0', TRUE);

                /****���ò�����ť****/
                $form->GABTN = '<a id = "change_buyer" href="javascript:;" class="btn btn-info btn-sm">����ɹ���</a>'
                    . '<a id="abandon_purchase" href="javascript:;" class="btn btn-info btn-sm">�����ɹ�</a>';
            }

            //ָ���ɹ���[�ɹ�������Ա + �û��Լ�]
            $user_model = M('erp_users');
            $form->setMyField('P_ID', 'EDITTYPE', '23', FALSE);
            $form->setMyField('P_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS WHERE ('
                . ' CITY = ' . $this->channelid . ' AND ISVALID = -1 AND ISBUYER = 1) OR ID = ' . $_SESSION['uinfo']['uid'], FALSE);
            $purOptions = addslashes(u2g($form->getSelectTreeOption('P_ID', '', -1)));
            $this->assign('purOptions', $purOptions);

            //�ɹ�Ա��
            $buyer_info = $user_model->field('ID,NAME')
                ->where('CITY = ' . $this->channelid . ' AND ISVALID = -1 AND ISBUYER = 1')
                ->select();

            //��ǰ�û�
            $current_user[0]['ID'] = $uid;
            $current_user[0]['NAME'] = $_SESSION['uinfo']['tname'];

            //�ϲ���ǰ�û��Ͳɹ�Ա��
            $buyer_info = !empty($buyer_info) ? array_merge($buyer_info, $current_user) : $current_user;

            $purchase_user = array();
            foreach ($buyer_info as $key => $value) {
                $purchase_user[$value['ID']] = $value['NAME'];
            }

            if ($showForm == 3) {
                $form->setMyField('P_ID', 'LISTCHAR', array2listchar($purchase_user), FALSE);
                $form->setMyField('APPLY_USER_ID', 'EDITTYPE', '1', false);
                $form->setMyFieldVal('APPLY_USER_ID', $_SESSION['uinfo']['tname'], TRUE);
            } else {
                if ($showForm == 1) {
                    $form->setMyField('P_ID', 'LISTCHAR', array2listchar($purchase_user), FALSE);
                } else {
                    $form->setMyField('P_ID', 'EDITTYPE', '21', FALSE);
                    $form->setMyField('P_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", FALSE);
                }

                //������
                $form->setMyField('APPLY_USER_ID', 'FORMVISIBLE', '-1', TRUE);
                $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1', TRUE);
                $form->setMyField('APPLY_USER_ID', 'EDITTYPE', '21', TRUE);
                $form->setMyField('APPLY_USER_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
            }

            //�Ƿ��ʽ��
            $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), !$is_fundpool);

            //�Ƿ�۷�
            $form->setMyField('IS_KF', 'DEFAULTVALUE', 1, FALSE);
            $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')));

            //״̬��Ϣ
            $purchase_arr = $purchase_list_model->get_conf_list_status_remark();
            $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($purchase_arr), FALSE);

            //��������ڰ�ť��ʾ�ж�
            if ($this->_merge_url_param['flowId'] > 0) {
                //��������ڱ༭Ȩ��
                $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

                if ($flow_edit_auth) {
                    //����༭
                    $form->EDITABLE = '-1'; //����༭
                    $form->DELABLE = '-1';  //����ɾ��
                    $form->ADDABLE = '-1';  //��������
                } else {

                    $form->DELCONDITION = '1==0';   //������ɾ��
                    $form->EDITCONDITION = '1==0';  //������༭
                    $form->ADDABLE = '0';   //����������
                    $form->GABTN = ''; //����������ײ���ť
                }
            } //ɾ�����༭��ť
            else if (is_array($purchase_type_info) && !empty($purchase_type_info)) {
                $purchase_requisition_status = $purchase_model->get_conf_requisition_status();

                //��ѯ�ɹ����뵥״̬δ�ύ���Ա༭��ɾ��
                if ($purchase_requisition_status['not_sub'] == $purchase_type_info[0]['STATUS']) {
                    $form->EDITABLE = '-1';
                    $form->DELABLE = '-1';
                    $form->ADDABLE = '-1';
                }
            } else {
                $purchase_status = $purchase_list_model->get_conf_list_status();
                $form->DELCONDITION = '%STATUS% == ' . $purchase_status['not_purchased'];
                $form->EDITCONDITION = '%STATUS% == ' . $purchase_status['not_purchased'];
            }


            // ����Ǳ༭������״̬����Ԥ�Ȼ�ȡ���������б�
            if ($showForm == 1 || ($showForm == 3 && empty($faction))) {
                if($FEE_ID_EDITTYPE!=1)$feeOptions = addslashes(u2g($form->getSelectTreeOption('FEE_ID', '', -1)));
                $this->assign('feeOptions', $feeOptions);
                if (!empty($_REQUEST['CASE_TYPE'])) {
                    $this->assign('product_name_autocomplete', '1');  // Ʒ����������
                } else {
                    $this->assign('product_name_autocomplete', '-1');  // Ʒ������������
                }
            }

            $formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
            $this->assign('purchase_user', $purchase_user);
            $this->assign('purchaseApplyId',$purchase_requisition_id);
            $this->assign('form', $formHtml);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->display('purchase_list');
        }
    }


    /**
     * +----------------------------------------------------------
     * ��ѯ�ɹ����Ƿ�����ύ����
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function check_purchase_list_by_pid() {
        $p_id = !empty($_GET['p_id']) ? intval($_GET['p_id']) : 0;
        $purchase_list_num = 0;

        if ($p_id > 0) {
            //�ɹ���ϸ������ѯ
            $purchase_list_model = D('PurchaseList');
            $purchase_list_num = $purchase_list_model->count_purchase_list_by_pid($p_id);

            if (!$purchase_list_num) {
                $info['status'] = 0;
                $info['msg'] = g2u('�޲ɹ���ϸ,�޷��ύ�ɹ�����');
                echo json_encode($info);
                exit;
            }

            //��ѯ�ɹ����뵥����
            $purchase_info = array();
            $purchase_model = D('PurchaseRequisition');
            $purchase_info = $purchase_model->get_purchase_by_id($p_id, array('CASE_ID'));

            /***���̲ɹ�����Ҫ�ж���Ŀ�Ƿ��Ѿ���ֹ���߰��***/
            if (is_array($purchase_info) && !empty($purchase_info)) {
                $case_id = intval($purchase_info[0]['CASE_ID']);
                $case_model = D('ProjectCase');
                //������Ϣ
                $case_info = $case_model->get_info_by_id($case_id);
                //������������
                $case_type_arr = $case_model->get_conf_case_type();
                //��������
                if (is_array($case_info) && !empty($case_info) && $case_info[0]['SCALETYPE'] == $case_type_arr['ds']) {
                    $prj_id = intval($case_info[0]['PROJECT_ID']);
                    $project_model = D('Project');
                    $project_info = $project_model->get_info_by_id($prj_id);

                    if (is_array($project_info) &&
                        ($project_info[0]['BSTATUS'] == 3 || $project_info[0]['BSTATUS'] == 5)
                    ) {
                        $result['status'] = 0;
                        $result['msg'] = g2u('�ύ�ɹ�����ʧ��,��Ŀ������ֹ����״̬');
                        echo json_encode($result);
                        exit;
                    }
                }
            }

            $info['status'] = 1;
            $info['msg'] = g2u('�ύ�ɹ�����ɹ�');
        } else if ($p_id == 0) {
            $info['status'] = 0;
            $info['msg'] = g2u('��ѡ��ɹ����뵥');
        }

        echo json_encode($info);
        exit;
    }


    /**
     * +-----------------------------------------------------
     * ��ȡ�ɹ�������ϸ
     * +-----------------------------------------------------
     *
     */
    public function ajaxGetPurchaseTaskData(){
        //���ض���
        $response = array(
            'status' => false,
            'msg' => "",
            'list' => array()
        );
        $purchaseApplyId  = $_POST['purchaseDetailList'];
        if(notEmptyArray($purchaseApplyId)) {
            $sql = "select ID,TASK_ID,TASK_NAME,SUPPLIER,to_char(EXEC_START,'YYYY-MM-DD') as EXEC_START,
                to_char(EXEC_END,'YYYY-MM-DD') as EXEC_END,TOTAL_NUM,TOTAL_WAGES,
                TOTAL_BONUS,TOTAL_MONEY,REIM_MONEY,MARK,STATUS,IS_BACK_TO_ZK
                from ERP_PURCHASER_BEE_DETAILS where 1=1 and P_ID=" .$purchaseApplyId[0];
            $purchaseTaskData = D()->query($sql);
            foreach($purchaseTaskData as $key =>$value){
                switch($value['IS_BACK_TO_ZK']){
                    CASE 1:
                        $purchaseTaskData[$key]['IS_BACK_TO_ZK'] = '��';
                        break;
                    CASE 0:
                        $purchaseTaskData[$key]['IS_BACK_TO_ZK'] = '��';
                        break;
                }
                switch($value['STATUS']){
                    CASE 0:
                        $purchaseTaskData[$key]['STATUS'] = 'δ�ύ����';
                        break;
                    CASE 1:
                        $purchaseTaskData[$key]['STATUS'] = '���ύ����';
                        break;
                    CASE 2:
                        $purchaseTaskData[$key]['STATUS'] = '�ѱ���';
                        break;
                    CASE 3:
                        $purchaseTaskData[$key]['STATUS'] = '�Ѳ���';
                        break;
                    CASE 4:
                        $purchaseTaskData[$key]['STATUS'] = '�������������';
                        break;
                }
            }
            if($purchaseTaskData) {
                $response['status'] = true;
                $response['list'] = $purchaseTaskData;
            }
        }
        ajaxReturnJSON(true, g2u('success'), g2u($response));
    }

    /**
     * +----------------------------------------------------------
     * �������
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    function opinionFlow() {
        $uid = intval($_SESSION['uinfo']['uid']);

        //��������
        $type = !empty($_REQUEST['FLOWTYPE']) ? $_REQUEST['FLOWTYPE'] : 'caigoushenqing';

        //������ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ?
            intval($_REQUEST['RECORDID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();

        if ($flowId > 0) {
            //�����Ѿ����ڵĹ�����
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //��һ��
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                } //ͨ����ť
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {
                        js_alert('ͬ��ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('ͬ��ʧ��');
                    }
                } //�����ť
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {
                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('���ʧ��');
                    }
                } //������ť
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);

                    if (!$auth) {
                        js_alert('δ�����ؾ���ɫ');
                        exit;
                    }

                    $cost_model = D('ProjectCost');
                    $cost_info = array();

                    //�ɹ����뵥MODEL
                    $purchase_model = D('PurchaseRequisition');

                    //�ɹ���ϸMDOEL
                    $purchase_list_model = D('PurchaseList');
                    $search_field = array(
                        'DEPT_ID', 
                        'USER_ID', 
                        'CASE_ID', 
                        'APPLY_TIME',
                        'to_char(END_TIME, \'YYYY-MM-DD HH24:MI:SS\') as END_TIME',
                        'CITY_ID',
                        'REASON',
                        'PRJ_ID',
                    );
                    $purchase_info = $purchase_model->get_purchase_by_id($recordId, $search_field);
                    
                    //���ݲɹ����뵥�Ż�ȡ�ɹ���ϸ
                    $serach_list_field = array('ID', 'PR_ID', 'PRICE_LIMIT',
                        'NUM_LIMIT', 'FEE_ID', 'IS_FUNDPOOL','ZK_STATUS',
                        'IS_KF');
                    $purchase_list_info =
                        $purchase_list_model->get_purchase_list_by_prid($recordId, $serach_list_field);
                    //����֮ǰ�ж��Ƿ񳬹����ʶ�ȣ����̡�����ҵ��)
                    $project_case_model = D('ProjectCase');
                    $case_type = $project_case_model->get_casetype_by_caseid(intval($purchase_info[0]['CASE_ID']), 1);

                    if ($case_type == 'ds' || $case_type == 'fx' || $case_type == 'fwfsc') {
                        $cost_total = 0;
                        foreach ($purchase_list_info as $key => $value) {
                            $cost_total += $value['PRICE_LIMIT'] * $value['NUM_LIMIT'];
                        }

                        //��ѯ���̡�����ҵ���CASE_ID
                        $search_field = array('ID', 'PARENTID');
                        $caseinfo = $project_case_model->get_info_by_id(intval($purchase_info[0]['CASE_ID']), $search_field);

                        $case_id = intval($caseinfo[0]['PARENTID']) > 0 ? intval($caseinfo[0]['PARENTID']) : intval($purchase_info[0]['CASE_ID']);
                        $is_over_top = is_overtop_payout_limit($case_id, $cost_total);

                        if ($is_over_top) {
                            js_alert('����ʧ��,�ɱ��ѳ������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ�');
                            exit;
                        }
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    $str = true;
                    if ($str) {
                        //���̱������ɱ�����,ѭ���ɹ���ϸ�����뵽�ɱ�����
                        if (is_array($purchase_list_info) && !empty($purchase_list_info)) {
                            foreach ($purchase_list_info as $key => $value) {
                                $cost_info['CASE_ID'] = $purchase_info[0]['CASE_ID'];
                                $cost_info['ENTITY_ID'] = $value['PR_ID'];
                                $cost_info['EXPEND_ID'] = $value['ID'];
                                $cost_info['ORG_ENTITY_ID'] = $value['PR_ID'];
                                $cost_info['ORG_EXPEND_ID'] = $value['ID'];
                                $cost_info['EXPEND_FROM'] = 1;//����ɹ�����
                                $cost_info['FEE'] = $value['PRICE_LIMIT'] * $value['NUM_LIMIT'];
                                $cost_info['FEE_REMARK'] = '����ɹ�ͨ��';
                                $cost_info['ADD_UID'] = $uid;
                                $cost_info['OCCUR_TIME'] = date('Y-m-d H:i:s');
                                $cost_info['ISKF'] = $value['IS_KF'];
                                $cost_info['ISFUNDPOOL'] = $value['IS_FUNDPOOL'];
                                $cost_info['FEE_ID'] = $value['FEE_ID'];

                                $result = $cost_model->add_cost_info($cost_info);
                                if ($value['FEE_ID'] == 58){
                                    $curl_result = $this->_zk_api($purchase_info[0],$value);
                                    if ($curl_result){
                                        $curl_result = json_decode($curl_result);
                                        if ($curl_result->code==200){
                                            $purchase_list_model->where('ID='.$value['ID'])->save(array('ZK_STATUS'=>1));
                                        }
                                    }
                                }
                            }
                        }
                        js_alert('�����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                }
                exit;
            }
        } else {
            //����������
            $auth = $workflow->start_authority($type);

            if (!$auth) {
                $this->error('����Ȩ��');
            }
            $form = $workflow->createHtml();
            $case_type = $this->_merge_url_param['CASE_TYPE'];

            $activid = 0;
            switch ($case_type) {
                case 'ds':
                    $activid = 1;
                    break;
                case 'fx':
                    $activid = 2;
                    break;
                case 'yg':
                    $activid = 3;
                    break;
                case 'hd':
                    $activid = 4;
                    break;
                case 'cp':
                    $activid = 5;
                    break;
                case 'fwfsc':
                    $activid = 8;
                    break;
                default :
                    $activid = 1;
            }

            if ($_REQUEST['savedata']) {
                if ($type == self::NON_CASH_COST_TYPE) {
                    // ����Ƿ��ֽ�֧��������
                    $nonCashCostId = $_GET['noncashcost_id'];
                    if($this->addNonCashCostWorkFlow($workflow, $activid, $nonCashCostId, $_POST)) {
                        // ��������ӳɹ�
                        js_alert('�ύ�ɹ�', U('Purchase/opinionFlow', $this->_merge_url_param));
                        exit;
                    } else {
                        // �����������ʧ��
                        js_alert('�ύʧ��', U('Purchase/opinionFlow', $this->_merge_url_param));
                        exit;
                    }
                } else {
                    // ����ǲɹ��������߸÷�֧
                    //�ɹ����뵥MODEL
                    $purchase_model = D('PurchaseRequisition');

                    $purchase_id = !empty($_GET['purchase_id']) ? intval($_GET['purchase_id']) : 0;

                    //�ɹ����뵥
                    $pruchase_info = $purchase_model->get_purchase_by_id($purchase_id, array('CASE_ID'));

                    /***�ɹ�����Ҫ�ж�ҵ�������Ƿ���ִ��״̬***/
                    $case_model = D('ProjectCase');
                    $case_info = $case_model->get_info_by_id($pruchase_info[0]['CASE_ID'], array('FSTATUS'));

                    //������Ϣ
                    if (is_array($case_info) && !empty($case_info)) {
                        if (!in_array($case_info[0]['FSTATUS'],$this->allow)) {
                            $result['status'] = 0;
                            $result['msg'] = g2u('�ɹ��������̴���ʧ��,ҵ�����Ͳ���ִ��״̬���޷������ɹ���������');
                            $result['forward'] = U('Purchase/purchase_manage', $this->_merge_url_param);
                            echo json_encode($result);
                            exit;
                        }
                    }

                    if (is_array($pruchase_info) && !empty($pruchase_info[0]['CASE_ID'])) {
                        $flow_data['type'] = $type;
                        $flow_data['CASEID'] = $pruchase_info[0]['CASE_ID'];
                        $flow_data['RECORDID'] = $purchase_id;
                        $flow_data['INFO'] = strip_tags($_POST['INFO']);
                        $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                        $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                        $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                        $flow_data['FILES'] = $_POST['FILES'];
                        $flow_data['ISMALL'] = intval($_POST['ISMALL']);
                        $flow_data['ISPHONE'] = intval($_POST['ISPHONE']);
                        $flow_data['ACTIVID'] = intval($activid);
                        $str = $workflow->createworkflow($flow_data);
                        if ($str) {
                            //�ύ�ɹ�����
                            $up_num = $purchase_model->submit_purchase_by_id($purchase_id);

                            js_alert('�ύ�ɹ�', U('Purchase/opinionFlow', $this->_merge_url_param));
                            exit;
                        } else {
                            js_alert('�ύʧ��', U('Purchase/opinionFlow', $this->_merge_url_param));
                            exit;
                        }
                    }
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('opinionFlow');
    }


    /**
     * +----------------------------------------------------------
     * ���ڲɹ��������
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    function bulk_purchase_opinionFlow() {
        $this->_merge_url_param['TAB_NUMBER'] = 6;

        //��������
        $type = !empty($_REQUEST['FLOWTYPE']) ? $_REQUEST['FLOWTYPE'] : 'caigoushenqing';

        //������ID
        $flowId = !empty($_REQUEST['flowId']) ? intval($_REQUEST['flowId']) : 0;

        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ? intval($_REQUEST['RECORDID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();

        if ($flowId > 0) {
            //�����Ѿ����ڵĹ�����
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //��һ��
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                } //ͨ����ť
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {
                        js_alert('ͬ��ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('ͬ��ʧ��');
                    }
                } //�����ť
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {
                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('���ʧ��');
                    }
                } //��ֹ��ť
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);

                    if (!$auth) {
                        js_alert('δ�����ؾ���ɫ');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {
                        js_alert('�����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                }
            }
        } else {
            //����������
            $auth = $workflow->start_authority($type);
            if (!$auth) {
                $this->error('����Ȩ��');
            }
            $form = $workflow->createHtml();

            if ($_REQUEST['savedata']) {
                //�ɹ����뵥MODEL
                $purchase_model = D('PurchaseRequisition');
                $purchase_id = !empty($_GET['purchase_id']) ? intval($_GET['purchase_id']) : 0;

                $flow_data['type'] = $type;
                $flow_data['CASEID'] = 0;
                $flow_data['RECORDID'] = $purchase_id;
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES'];
                $flow_data['ISMALL'] = intval($_POST['ISMALL']);
                $flow_data['ISPHONE'] = intval($_POST['ISPHONE']);
                $str = $workflow->createworkflow($flow_data);

                if ($str) {
                    //�ύ�ɹ�����
                    $up_num = $purchase_model->submit_purchase_by_id($purchase_id);
                    js_alert('�ύ�ɹ�', U('Purchase/bulk_purchase_opinionFlow', $this->_merge_url_param));
                    exit;
                } else {
                    js_alert('�ύʧ��', U('Purchase/bulk_purchase_opinionFlow', $this->_merge_url_param));
                    exit;
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('opinionFlow');
    }


    /**
     * +----------------------------------------------------------
     * ���Ĳɹ���
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function change_buyer() {
        $result = array();

        //�ɹ���ϸ���
        $purchase_id = !empty($_POST['purchase_id']) ? $_POST['purchase_id'] : 0;

        //�ɹ���ID
        $buyer_id = !empty($_POST['buyer_id']) ? intval($_POST['buyer_id']) : 0;

        if ($buyer_id > 0 && $purchase_id > 0) {
            //�ɹ����뵥MODEL
            $purchase_model = D('PurchaseRequisition');

            //�ɹ���ϸMODEL
            $purchase_list_model = D('PurchaseList');

            //�ɹ���ϸ��Ϣ
            $purchase_list_info = array();
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchase_id, array('PR_ID'));
            if (empty($purchase_list_info)) {
                $result['state'] = 0;
                $result['msg'] = '�ɹ���ϸ��Ϣ�쳣���޷�����ɹ���';

                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            //�ɹ���ϸ�б���ɹ�����״̬Ϊδ�ɹ�ʱ���Ա��
            foreach($purchase_id as $purchaseId){
                $purchaseStatus = M("Erp_purchase_list")->where("id=".$purchaseId)->getField("status");
                if($purchaseStatus != 0 ){
                    $result['status'] = 0;
                    $result['msg'] = g2u("�ɹ�״̬����δ�ɹ����޷�����ɹ���");
                    echo json_encode($result);
                    exit;
                }
            }
//            //�ɹ����뵥���
//            $purchase_requisition_id = intval($purchase_list_info[0]['PR_ID']);
//
//            //�ɹ����뵥����
//            $purchase_info = array();
//            $purchase_info = $purchase_model->get_purchase_by_id($purchase_requisition_id, array('STATUS'));
//
//            //�ɹ����뵥״̬
//            $purchase_requisition_status = $purchase_model->get_conf_requisition_status();
//
//            //��ѯ�ɹ����뵥״̬δ�ύ���Ա༭��ɾ��
//            if ($purchase_requisition_status['not_sub'] != $purchase_info[0]['STATUS']) {
//                $result['state'] = 0;
//                $result['msg'] = '�ɹ��������ύ���޷�����ɹ���';
//
//                $result['msg'] = g2u($result['msg']);
//                echo json_encode($result);
//                exit;
//            }

            $update_num = 0;
            $update_arr['P_ID'] = $buyer_id;
            $update_num = $purchase_list_model->update_purchase_list_by_id($purchase_id, $update_arr);

            if ($update_num > 0) {
                $result['state'] = 1;
                $result['msg'] = '����ɹ��˳ɹ�';
            } else {
                $result['state'] = 0;
                $result['msg'] = '����ɹ���ʧ��';
            }
        } else {
            $result['state'] = 0;
            $result['msg'] = '��ѡ��Ҫָ���Ĳɹ��ˣ���Ҫ����Ĳɹ���ϸ';
        }

        $result['msg'] = g2u($result['msg']);
        echo json_encode($result);
    }

    /**
     * �Ǹ��ֳɱ�ҳǩ���
     */
    public function non_cash_cost() {

        $uid = $_SESSION['uinfo']['uid'];  // �û�ID
        $showForm = $this->_request('showForm');
        $faction = $this->_request('faction');
        $caseType = $this->_request('CASE_TYPE');  // ��������
        $projectID = $this->_merge_url_param['prjid'];  // ��ĿID
        $caseID = $this->_merge_url_param['CASEID'];  // ����ID
        $flowId = $this->_request('flowId');
        $city_id = $this->channelid;
//        $this->project_pro_auth($projectID, $flowId);
        if (empty($flowId) && $caseType !== 'ds') {  // �ǵ�����Ŀ�����޷�������ֽ�֧��
            js_alert('�ǵ�����Ŀ���޷�������ֽ�֧��', U('Purchase/purchase_list', $this->_merge_url_param));
            exit;
        }

        if ($showForm == 3) {
            if ($faction == 'saveFormData') {
                // ����������ERP_NONCASHCOST���еļ�¼
                $toInsertData['APPLY_USER'] = $uid;
                $toInsertData['PROJECT_ID'] = $projectID;
                if (empty($caseID) || !$caseID) {
                    $caseID = $this->getProjectCaseId($projectID, $caseType);
                }
                $attachment = $this->_post('ATTACHMENT');
                if (empty($attachment)) {
                    echo json_encode(array(
                        'status' => 0,
                        'msg' => g2u('���ϴ�����')
                    ));
                    exit;
                }
                $toInsertData['CASE_ID'] = $caseID;
                $toInsertData['TYPE'] = $this->_post('TYPE');  // �ɱ�����
                $toInsertData['AMOUNT'] = $this->_post('AMOUNT');  // ���
                $toInsertData['ATTACHMENT'] = $attachment;//iconv("utf-8", "gbk", $attachment);  // ����
                $toInsertData['CONTRACT_NO'] = $this->_post('CONTRACT_NO');  // ��ͬ��
                $toInsertData['ADDED_AT'] = date('Y-m-d H:i:s');  // ���ʱ��
                $toInsertData['STATUS'] = 0;  // δ�ύ
                $toInsertData['SCALETYPE'] = $this->_post('SCALETYPE'); //ҵ������
                $toInsertData['FEE_ID'] = intval($_POST['FEE_ID']);; //��������

                // �������񣬴������ݿ�
                $nonCashCostModel = D('NonCashCost');
                $nonCashCostModel->startTrans();
                $insertedID = $nonCashCostModel->addRecord($toInsertData);
                if ($insertedID !== false) {
                    $nonCashCostModel->commit();  // �ύ����
                    $result['status'] = 2;
                    $result['msg'] = '�Ǹ��ֳɱ���ӳɹ�';
                } else {
                    $nonCashCostModel->rollback();  // �ع�����
                    $result['status'] = 0;
                    $result['msg'] = '�Ǹ��ֳɱ����ʧ��';
                }

                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            } else {
                // ����Ƿ��ʽ����Ŀ����������ʽ����Ŀ����ʾ������ӷǸ��ֳɱ�
                $isFundPool = D('House')->where('PROJECT_ID = ' . $projectID)->getField('ISFUNDPOOL');
                if ($isFundPool == self::NOT_FUND_POOL_PROJECT) {
                    js_alert('���ʽ����Ŀ���������ӷǸ��ֳɱ�');
                    exit;
                }
            }
        }
        if($showForm == 1){
            if ($faction == 'saveFormData'){
                $ID = $_REQUEST['ID'];
                $toChangeData['APPLY_USER'] = $uid;
                $toChangeData['PROJECT_ID'] = $projectID;
                $attachment = $this->_post('ATTACHMENT');
                if (empty($attachment)) {
                    echo json_encode(array(
                        'status' => 0,
                        'msg' => g2u('���ϴ�����')
                    ));
                    exit;
                }
                $toChangeData['TYPE'] = $this->_post('TYPE');  // �ɱ�����
                $toChangeData['AMOUNT'] = $this->_post('AMOUNT');  // ���
                $toChangeData['ATTACHMENT'] = $attachment;//iconv("utf-8", "gbk", $attachment);  // ����
                $toChangeData['CONTRACT_NO'] = $this->_post('CONTRACT_NO');  // ��ͬ��
                $toChangeData['ADDED_AT'] = date('Y-m-d H:i:s');  // ���ʱ��
                $toChangeData['SCALETYPE'] = $this->_post('SCALETYPE'); //ҵ������
                $toChangeData['FEE_ID'] = intval($_POST['FEE_ID']);; //��������

                // �������񣬴������ݿ�
                $nonCashCostModel = D('NonCashCost');
                $nonCashCostModel->startTrans();
                $changeID = $nonCashCostModel->changeRecord($toChangeData,$ID);
                if ($changeID  !== false) {
                    $nonCashCostModel->commit();  // �ύ����
                    $result['status'] = 2;
                    $result['msg'] = '�Ǹ��ֳɱ��޸ĳɹ�';
                } else {
                    $nonCashCostModel->rollback();  // �ع�����
                    $result['status'] = 0;
                    $result['msg'] = '�Ǹ��ֳɱ��޸�ʧ��';
                }

                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
        }
        Vendor('Oms.Form');
        $form = new Form();
        if ($flowId > 0) {
            $recordID = $this->_request('RECORDID');
            $form->initForminfo(194)->where('ID = ' . $recordID);
        } else {
            $form->initForminfo(194)->where('PROJECT_ID = ' . $projectID);
        }

        $projectCase = D("ProjectCase");
        $scaleTypeArr = $projectCase->get_conf_case_type_remark();
        if ($showForm >= 1) {
            $id = $_REQUEST['ID'];
            $feeId = M("Erp_noncashcost")->where("ID = ".$id)->getField("FEE_ID");
			$scaletype = M("Erp_noncashcost")->where("ID = ".$id)->getField("SCALETYPE");
            $this->assign("feeId",$feeId);
            $this->assign("showForm",$showForm);
            $sql = "select c.scaletype  from erp_noncashcost n left join erp_project p  on n.project_id = p.id left join  erp_case c on c.project_id = p.id
                       where n.id =".$id;
            $scaleTypeData = D()->query($sql);//var_dump($scaleTypeData );
			//$scaletype = $scaleTypeData[0]['SCALETYPE'];
			$this->assign('scaletype',$scaletype);
            $scaleTypeArr = $projectCase->getSelectList($scaleTypeData);
            $form = $form->setMyField('SCALETYPE', 'LISTCHAR', array2listchar($scaleTypeArr), FALSE);
        } else {

            //ҵ������
            $form = $form->setMyField('SCALETYPE', 'LISTCHAR', array2listchar($scaleTypeArr), FALSE);

            // todo
            if ($this->_merge_url_param['flowId'] > 0) {
                //��������ڱ༭Ȩ��
                $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

                if ($flow_edit_auth) {
                    $form->EDITABLE = -1;   //����༭
                    $form->ADDABLE = '0';   //����������
                    $form->GABTN = '';      //���������ύ��ť
                } else {
                    $form->DELCONDITION = '1==0';   //������ɾ��
                    $form->EDITCONDITION = '1==0';  //������༭
                    $form->ADDABLE = '0';   //����������
                    $form->GABTN = '';  //������ť����
                }
            } else {
                //���ð�ťչʾ���
                $form->EDITCONDITION = '%STATUS% == 0';
                $form->DELCONDITION = '%STATUS% == 0';
            }
        }
        //��������
        $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
        $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
            . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
//        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, self::NON_CASH_COST_ADD);
        $formHtml = $form->getResult();
        if (empty($caseID)) {
            $caseInfo = D('ProjectCase')->get_info_by_pid($projectID, $this->_request('CASE_TYPE'));
            $caseID = empty($caseInfo) ? null : intval($caseInfo[0]['ID']);
        }

        $prjContract = M("Erp_project")->where("ID=".$projectID)->getField("CONTRACT");
        $this->assign('prjContract',$prjContract);
        $this->assign('isShowOptionBtn', $this->isShowOptionBtn($caseID));
        $this->assign('form', $formHtml);  
		$this->assign('projectID', $projectID);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('non_cash_cost');
    }

    private function getProjectCaseId($projectId, $caseType) {
        //����MODEL
        $projectCaseModel = D('ProjectCase');

        $caseId = false;
        if ($caseType != '' && $projectId > 0) {
            $case_info = $projectCaseModel->get_info_by_pid($projectId, $caseType);
            $caseId = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : false;
        }

        return $caseId;
    }

    private function addNonCashCostWorkFlow(&$workflow, $activid, $nonCashCostId, $post = null) {
        if (empty($nonCashCostId) || !$nonCashCostId) {
            return false;
        }
        $itsModel = D('NonCashCost');
        $itsRecord = $itsModel->where('ID = ' . $nonCashCostId)->find();
        if (is_array($itsRecord) && count($itsRecord)) {
            if ($itsRecord['STATUS'] != '0' && $itsRecord['STATUS'] !== null) {
                return false;
            }
            $flow_data['type'] = self::NON_CASH_COST_TYPE;
            $flow_data['CASEID'] = $itsRecord['CASE_ID'];
            $flow_data['RECORDID'] = $nonCashCostId;
            $flow_data['INFO'] = strip_tags($post['INFO']);
            $flow_data['DEAL_INFO'] = strip_tags($post['DEAL_INFO']);
            $flow_data['DEAL_USER'] = strip_tags($post['DEAL_USER']);
            $flow_data['DEAL_USERID'] = intval($post['DEAL_USERID']);
            $flow_data['FILES'] = $post['FILES'];
            $flow_data['ISMALL'] = intval($post['ISMALL']);
            $flow_data['ISPHONE'] = intval($post['ISPHONE']);
            $flow_data['ACTIVID'] = intval($activid);
            $insertedWorkFlow = $workflow->createworkflow($flow_data);
            if ($insertedWorkFlow !== false) {
                //$itsModel->startTrans();
                $updatedRows = $itsModel->where('ID = ' . $nonCashCostId)->save(array(
                    'STATUS' => 1  // �����״̬
                ));
                if ($updatedRows !== false) {
                   // $itsModel->commit();
                    return $updatedRows;
                } else {
                  //  $itsModel->rollback();
                    return false;
                }

            } else {
                return false;
            }
        }

        return false;
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
        $purchaseId = $_REQUEST['purchaseId'];
        if (intval($purchaseId) > 0) {
            try {
                $result = D()->query(sprintf(self::PURCHASE_FLOWID_SQL, $purchaseId));
                if (notEmptyArray($result)) {
                    $response['status'] = true;
                    $response['message'] = '��ȡ������ID�ɹ�';
                    $response['data'] = $result[0]['ID'];
                } else {
                    $response['message'] = '�òɹ�������δ��������!';
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
        }

        echo json_encode(g2u($response));
    }

    /**
     * �����ɹ�����
     */
    public function abandon() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => ''
        );

        $purchaseDetailList = $_REQUEST['purchase_detail_list'];
        if (notEmptyArray($purchaseDetailList)) {
            $purchaseList = D('PurchaseList')->getPurchaseJoinReq($purchaseDetailList);
            if (notEmptyArray($purchaseList)) {
                // ��ÿ���ɹ���ϸ���д���
                try {
                    D()->startTrans();
                    $dbResult = false;
                    foreach ($purchaseList as $k => $purchase) {
                        if ($purchase['REQ_STATUS'] != 2) {
                            switch(intval($purchase['REQ_STATUS'])) {
                                case 0:
                                case 1:
                                case 3:
                                    $msg = "���ͨ���Ĳɹ��ſ��Խ��з����ɹ�����";
                                    break;
                                case 4:
                                    $msg = "�Ѳɹ���ɵĲ���ִ�з����ɹ�����";
                                    break;
                            }

                            break;  // ��ֹѭ��
                        } else {
                            if (!empty($purchase['CONTRACT_ID'])) {
                                $msg = '�ɹ��Ѽ���ɹ���ͬ���޷������ɹ�';
                                break; // ��ֹѭ��
                            }

                            if (intval($purchase['USE_NUM']) > 0 || intval($purchase['NUM'] > 0)) {
                                $msg = "�ɹ����ѽ��вɹ������ܷ��������ɹ�";
                                break;  // ��ֹѭ��
                            }
                        }

                        $dbResult = $this->abandonOnePurchase($purchase, $msg);
                        if ($dbResult === false) {
                            break;
                        }

                    }

                    if ($dbResult !== false) {
                        D()->commit();
                        $response['status'] = true;
                        $response['message'] = '�ɹ���������ɹ�';
                    } else {
                        D()->rollback();
                        $response['status'] = false;
                        $msg = empty($msg) ? "�ɹ��������ʧ��" : $msg;
                        $response['message'] = $msg;
                    }
                } catch (Exception $e) {
                    D()->rollback();
                    $response['status'] = false;
                    $response['message'] = $e->getMessage();
                }
            }
        }

        echo json_encode(g2u($response));
    }

    /**
     * �������òɹ��˿�
     * @param $data
     * @return array|bool|mixed
     */
    private function rejectToWarehouse($data) {
        $result = false;
        $warehouseUse = D('erp_warehouse_use_details')
            ->where("PL_ID = {$data['purchaseId']}")
            ->order('ID DESC')
            ->select();

        if (is_array($warehouseUse)) {
            foreach ($warehouseUse as $k => $v) {
                // ���¿����Ϣ
                $upWarehouseSql = <<<UPDATE_NUM_SQL
                UPDATE ERP_WAREHOUSE
                SET USE_NUM = USE_NUM - %d
                WHERE ID = %d
UPDATE_NUM_SQL;
                $result = D()->query(sprintf($upWarehouseSql, $v['USE_NUM'], $v['WH_ID']));

                // ɾ�����ü�¼
                if ($result !== false) {
                    $result = D('erp_warehouse_use_details')->where("ID = {$v['ID']}")->delete();
                } else {
                    return false;
                }
            }
        }

        return $result;
    }

    /**
     * ������һ���ɹ�����
     * ��Ϊ�������裺
     * 1�������˿�����˿�
     * 2����purchase_list����ɾ����Ӧ����ϸ
     * 3) ��cost_list����ɾ����Ӧ�ĳɱ�
     * @param $purchase
     * @return array|bool|mixed
     */
    private function abandonOnePurchase($purchase, &$msg) {
        $dbResult = false;
        // ֻ��δ�ɹ���û�м����ͬ�Ĳɹ��ſɷ���
        if (empty($purchase['CONTRACT_ID']) && intval($purchase['DETAIL_STATUS']) == 0) {
            if (intval($purchase['USE_NUM']) != 0) {
                $revertedData = array(
                    'use_num' => $purchase['USE_NUM'],
                    'purchaseId' => $purchase['ID']
                );

                // �˿�
                $dbResult = $this->rejectToWarehouse($revertedData);
            } else {
                $dbResult = true;
            }

            if ($dbResult !== false) {
                // ɾ����Ӧ�Ĳɹ���ϸ
                $dbResult = D('erp_purchase_list')->where("ID = {$purchase['DETAIL_ID']}")->delete();
            } else {
                $msg = "��������Ʒ�˿�ʧ��";
            }

            // �ӳɱ�����ɾ����Ӧ����Ŀ
            if ($dbResult !== false) {
                $dbResult = $this->delPurchaseCost($purchase);  // �ӳɱ�����ɾ����Ӧ�Ĳɹ��ɱ�
            }
        } else {
            if (!empty($purchase['CONTRACT_ID'])) {
                $msg = '�ɹ��Ѽ���ɹ���ͬ���޷������ɹ�';
            } else {
                $msg = '�ɹ�����ִ���У��޷�����';
            }
        }

        return $dbResult;
    }

    /**
     * �ӳɱ�����ɾ����ɹ���صļ�¼
     * @param $purchase
     * @return bool
     */
    private function delPurchaseCost($purchase) {
        $response = false;
        if (notEmptyArray($purchase)) {
            $response = D('erp_cost_list')->where("ORG_ENTITY_ID = {$purchase['REQ_ID']} AND ORG_EXPEND_ID = {$purchase['DETAIL_ID']}")->delete();
        }
        return $response;
    }
}

/* End of file PurchaseAction.class.php */
/* Location: ./Lib/Action/PurchaseAction.class.php */