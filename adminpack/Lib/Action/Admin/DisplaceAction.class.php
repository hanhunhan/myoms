<?php

/**
 * �û�������
 */
class DisplaceAction extends ExtendAction {

    /**
     * ���ύ��������Ȩ��
     */
    const NON_CASH_COST_COMMIT = 500;

    /**
     * ��ѯ�ɹ�����FlowID��SQL
     */
    const DISPLACE_FLOWID_SQL = <<<DISPLACE_FLOWID_SQL
        SELECT ID
        FROM ERP_FLOWS T
        WHERE T.FLOWSETID = 31
          AND T.RECORDID = %d
DISPLACE_FLOWID_SQL;

    /**
     * �����б�SQL
     */
    const SALE_MANAGE_LIST_SQL = <<<SALE_MANAGE_LIST
        SELECT
            a.ID,
            a.STATUS,
            a.INVOICE_STATUS,
            a.APPLY_USER_ID,
            a.APPLY_TIME,
            a.APPLY_REASON,
            u.name AS APPLY_USERNAME,
            a.buyer
        FROM ERP_DISPLACE_APPLYLIST a
        LEFT JOIN erp_users u ON u.id = a.APPLY_USER_ID
SALE_MANAGE_LIST;

    const SALE_MANAGE_DETAIL_SQL = <<<SALE_MANAGE_DETAIL_SQL
        SELECT
            d.id,
            d.list_id,
            d.amount,
            RTRIM(to_char(d.money,'fm99999999990.99'),'.') AS money,
            w.brand,
            w.model,
            w.product_name,
            w.source,
            w.inbound_status as status,
            w.alarmtime,
            w.livetime,
            w.num,
            RTRIM(to_char(w.price,'fm99999999990.99'),'.') AS price,
            i.contract_no,
            p.projectname AS project_name,
            w.changetime AS DAMAGETIME
        FROM ERP_DISPLACE_APPLYDETAIL d
        LEFT JOIN ERP_DISPLACE_WAREHOUSE w ON w.id = d.did
        LEFT JOIN ERP_DISPLACE_REQUISITION r ON r.id = w.dr_id
        LEFT JOIN ERP_INCOME_CONTRACT i ON i.id = r.contract_id
        LEFT JOIN erp_case c ON w.case_id = c.id
        LEFT JOIN erp_project p ON p.id = c.project_id
SALE_MANAGE_DETAIL_SQL;

    const EXPORT_SALE_MANAGE_DETAIL_SQL = <<<EXPORT_SALE_MANAGE_DETAIL_SQL
SELECT
            d.id,
            d.list_id,
            d.amount,
            d.money,
            w.brand,
            w.model,
            w.product_name,
            w.source,
            w.inbound_status,
            w.alarmtime,
            w.livetime,
            w.num,
            w.price,
            i.contract_no,
            p.projectname AS project_name,
            w.changetime AS DAMAGETIME,
            l.status
        FROM ERP_DISPLACE_APPLYDETAIL d
        LEFT JOIN ERP_DISPLACE_WAREHOUSE w ON w.id = d.did
        LEFT JOIN ERP_DISPLACE_REQUISITION r ON r.id = w.dr_id
        LEFT JOIN ERP_INCOME_CONTRACT i ON i.id = r.contract_id
        LEFT JOIN erp_case c ON w.case_id = c.id
        LEFT JOIN erp_project p ON p.id = c.project_id
        LEFT JOIN erp_displace_applylist l ON l.id = d.list_id
EXPORT_SALE_MANAGE_DETAIL_SQL;





    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();

    private $city = 0;
    private $uid = 0;
    private $_tab_number = 7;

    private $typeParam = array(); //1�������� 2���ڲ�����  3������
    /**
     * ���ֽ�֧�����͹�����
     */
    const NON_CASH_COST_TYPE = 'feifuxianchengbenshenqing';

    //���캯��
    public function __construct() {
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'sub_displace' => array(
                'yg'=>849,
                'hd'=>850,
            ),
            'submit_flow' => array(
                'shoumai' =>873,
                'neibulingyong' => 873,
                'baosun' => 873,
            ),
            'commit_change_apply' =>array(
                'default' =>874,
            )
        );

        parent::__construct();

        //TAB URL����
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? intval($_GET['prjid']) : 0;
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
        !empty($_GET['purchase_id']) ? $this->_merge_url_param['purchase_id'] = intval($_GET['purchase_id']) : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = intval($_GET['RECORDID']) : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['is_from']) ? $this->_merge_url_param['is_from'] = strip_tags($_GET['is_from']) : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
        //!empty($_GET['typeParam']) ? $this->_merge_url_param['typeParam'] = strip_tags($_GET['typeParam']) : ''; //typeparam ����

        $this->uid = intval($_SESSION['uinfo']['uid']); //�û�ID
        $this->city = intval($_SESSION['uinfo']['city']); //����ID
        $this->deptId = intval($_SESSION['uinfo']['deptid']); //����ID

        //��ʼ������
        $this->typeParam = array(
            'shoumai'=>array(
                'type'=>1,//����ֵ
                'opreateButton'=>true,//�Ƿ�չ�ֲ�����ť
                'tag'=>'������ϸ', //ҳǩ����
                'isInvoice'=>true,
            ),
            'neibulingyong'=>array(
                'type'=>2,
                'opreateButton'=>false,
                'tag'=>'�ڲ�������ϸ',
                'isInvoice'=>false,
            ),
            'baosun'=>array(
                'type'=>3,
                'opreateButton'=>false,
                'tag'=>'������ϸ',
                'isInvoice'=>false,
            ),
        );

    }


    /**
     * �û����ܹ���
     * +----------------------------------------------------------
     * @param none
     * +----------------------------------------------------------
     * @return none
     */
    public function displaceApply(){

        //���ظ�ʽ
        $result = array(
            'status'=>0,
            'msg'=>'',
            'forward'=>'',
            'data'=>null,
        );

        //���ղ���
        $caseType = $this->_merge_url_param['CASE_TYPE']; //ҵ������
        $prjId = $this->_merge_url_param['prjid']; //��ĿID
        $caseId = $this->_merge_url_param['CASEID']; //����ID
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : 0; //չ����ʽ
        $faction = isset($_GET['faction']) ? trim($_GET['faction']) : ''; //������Ϊ
        $postId = isset($_POST['ID']) ? intval($_POST['ID']) : 0; //�ɹ���ID

        //����MODEL
        $caseModel = D('ProjectCase');
        $displaceModel = D('Displace');

        //����û���
        if (!empty($_POST) && $faction == 'saveFormData' && $postId == 0) {
            $requisition = array();
            //��ȡ����ID
            $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;

            $requisition['CASE_ID'] = $caseId;
            $requisition['REASON'] = u2g($_POST['REASON']);
            $requisition['USER_ID'] = $this->uid;
            $requisition['DEPT_ID'] = $this->deptId;
            $requisition['APPLY_TIME'] = date('Y-m-d H:i:s');
            $requisition['END_TIME'] = $this->_post('END_TIME');
            $requisition['PRJ_ID'] = $prjId;
            $requisition['CONTRACT_ID'] = $this->_post('CONTRACT_ID');
            $requisition['CITY_ID'] = $this->city;
            $requisition['STATUS'] = 0; //δ�ύ

            $insertId = M('Erp_displace_requisition')->add($requisition);

            if ($insertId > 0) {
                $result['status'] = 1;
                $result['msg'] = '��,�û�������ӳɹ�!';
                $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
                //��־
                userLog()->writeLog( $insertId, $_SERVER["REQUEST_URI"],  '�û�������ӳɹ�', serialize($requisition));
            } else {
                $result['msg'] = '��,�û��������ʧ�ܣ�';
                $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
                userLog()->writeLog($insertId, $_SERVER["REQUEST_URI"],  '�û��������ʧ��', serialize($requisition));
            }

            //���ؽ����
            $result['msg'] = g2u($result['msg']);
            die(@json_encode($result));

        } else if (!empty($_POST) && $faction == 'saveFormData' && $postId > 0) { //�޸��û���

            //�ж�״̬
            $currentRequisiton = $displaceModel->getDisplaceById($postId, array('CASE_ID,STATUS'));

            //ֻ��δ�ύ�Ĳ��ܱ༭
            if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                $currentRequisiton[0]['STATUS'] != 0
            ) {
                $result['msg'] = '�ף���δ�ύ�����û�������ܱ༭Ŷ��';
                $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
            } else {
                $requisition = array();

                $requisition['REASON'] = u2g($_POST['REASON']);
                $requisition['USER_ID'] = $this->uid;
                $requisition['DEPT_ID'] = $this->deptId;
                $requisition['APPLY_TIME'] = date('Y-m-d H:i:s');
                $requisition['END_TIME'] = $this->_post('END_TIME');
                $requisition['PRJ_ID'] = $prjId;
                $requisition['CONTRACT_ID'] = $this->_post('CONTRACT_ID');
                $requisition['CITY_ID'] = $this->city;
                $requisition['STATUS'] = $this->_post('STATUS');

                $updateRet = M('Erp_displace_requisition')
                    ->where('ID = ' . $postId)->save($requisition);

                if ($updateRet !== false) {
                    $result['status'] = 1;
                    $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
                } else {
                    $result['status'] = 0;
                    $result['msg'] = '�ף��޸�ʧ�ܣ��������ݣ�';
                    $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
                }
            }

            //���ؽ����
            $result['msg'] = g2u($result['msg']);
            die(@json_encode($result));

        } else if ($faction === 'delData') { //ɾ����¼

            $delId = intval($_GET['ID']); //ɾ��ID

            //�ж�״̬
            $currentRequisiton = $displaceModel->getDisplaceById($delId, array('CASE_ID,STATUS'));

            if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                $currentRequisiton[0]['STATUS'] != 0
            ) {
                $result['msg'] = '�ף���δ�ύ�����û��������ɾ��Ŷ��';
                $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
            }

            if ($delId > 0) {

                //ɾ����ϸ
                $delDisplaceRet = $displaceModel->delDisplaceById($delId);

                if ($delDisplaceRet) {
                    $result['status'] = 'success';
                    $result['msg'] = g2u('�ף�ɾ���ɹ�!');
                } else {
                    $result['msg'] = g2u('�ף�ɾ��ʧ��,������!');
                }
            }

            //������
            die(@json_encode($result));

        } else { //�б�չ��
            Vendor('Oms.Form');
            $form = new Form();

            //��ȡwhere����
            $condWhere = "CASE_ID = '" . $caseId . "'";
            if (!$caseId) {
                $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
                $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;
                $condWhere = "CASE_ID = '" . $caseId . "'";
            }

            $form = $form->initForminfo(204)->where($condWhere);
            //��ʼ�����
            $form->SQLTEXT = '(SELECT L.*,M.TOTAL_MONEY FROM ERP_DISPLACE_REQUISITION L LEFT JOIN
(SELECT SUM(NUM * PRICE) AS TOTAL_MONEY,DR_ID FROM ERP_DISPLACE_WAREHOUSE GROUP BY DR_ID) M ON L.ID = M.DR_ID)';

            //�ֶ�չ��
            $form = $form->setMyField('CONTRACT_ID', 'LISTSQL', 'SELECT ID,CONTRACT_NO FROM ERP_INCOME_CONTRACT WHERE DISPLACE IN(1,2) AND CASE_ID = ' . $caseId, FALSE); //displace �����û�����ȫ�û�
            $requisitionStatus = $displaceModel->get_conf_requisition_status();
            $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($requisitionStatus), TRUE);
            $form = $form->setMyField('PRJ_ID', 'LISTSQL', 'SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE ID = ' . $prjId, FALSE);
            $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', FALSE);

            if ($showForm >= 1) {
                $form = $form->setMyFieldVal('PRJ_ID', $prjId, TRUE);
                $form = $form->setMyFieldVal('USER_ID', $this->uid, TRUE);

                if($showForm==3){ //��� ��������ʱ��
                    $form->setMyField('APPLY_TIME','FORMVISIBLE',0,false);
                }
            } else {
                //״̬Ϊ0ʱ
                $form->EDITCONDITION = '%STATUS% == 0';
                $form->DELCONDITION = '%STATUS% == 0';
            }

            $childrenData = array(array('�û���ϸ', U('/Displace/displaceDetail', $this->_merge_url_param)));
            //��ťǰ��
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap,array(),$caseType);  // Ȩ��ǰ��
            //�����Ⱦ
            $formHtml = $form->setChildren($childrenData)->getResult();
            $this->assign('form', $formHtml);

            $this->assign('isShowOptionBtn', $this->isShowOptionBtn($caseId));
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('caseType', $caseType); //ҵ������
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->display('displaceApply');
        }

    }


    /**
     * �û���ϸ����
     * +----------------------------------------------------------
     * @param none
     * +----------------------------------------------------------
     * @return none
     */
    public function displaceDetail(){

        //���ظ�ʽ
        $result = array(
            'status'=>0,
            'msg'=>'',
            'data'=>null,
        );

        //��ȡ����
        $postId = isset($_POST['ID']) ? intval($_POST['ID']) : 0; //��ϸID
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : ''; //ִ�в���
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';  //չ����ʽ
        $displaceRequisitionId = isset($_GET['parentchooseid']) ?
            intval($_GET['parentchooseid']) : 0;  //�û�����ID
        $caseType = $this->_merge_url_param['CASE_TYPE']; //ҵ������
        $prjId = $this->_merge_url_param['prjid']; //��ĿID
        $caseId = $this->_merge_url_param['CASEID']; //����ID

        //�û���MODEL
        $displaceModel = D('Displace');
        $caseModel = D('ProjectCase');

        //����û���ϸ
        if (!empty($_POST) && $faction == 'saveFormData' && $postId == 0) {
            $displaceWarehouse = array();

            //��ȡ����ID
            $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;

            $displaceWarehouse['DR_ID'] = $displaceRequisitionId; //�û���ID
            $displaceWarehouse['BRAND'] = u2g(strip_tags($_POST['BRAND'])); //Ʒ��
            $displaceWarehouse['MODEL'] = u2g(strip_tags($_POST['MODEL'])); //�ͺ�
            $displaceWarehouse['PRODUCT_NAME'] = u2g(strip_tags($_POST['PRODUCT_NAME'])); //Ʒ��
            $displaceWarehouse['NUM'] = intval($_POST['NUM']); //�û�����
            $displaceWarehouse['PRICE'] = floatval($_POST['PRICE']); //�û���
            $displaceWarehouse['SOURCE'] = u2g(strip_tags($_POST['SOURCE'])); //��Դ
            $displaceWarehouse['LIVETIME'] = $_POST['LIVETIME']; //��Դ
            $displaceWarehouse['STATUS'] = 0; //δ���
            $displaceWarehouse['ADD_USERID'] = $this->uid; //�����
            $displaceWarehouse['INBOUND_NUM'] = intval($_POST['NUM']); //�ڿ�����
            $displaceWarehouse['INBOUND_STATUS'] = 1; //δ���״̬
            $displaceWarehouse['INVOICE_STATUS'] = 1; //δ��Ʊ
            $displaceWarehouse['ALARMTIME'] = $_POST['ALARMTIME']; //����ʱ��
            $displaceWarehouse['CASE_ID'] = intval($caseId);
            $displaceWarehouse['ADD_TIME'] = date('Y-m-d H:i:s');
            $displaceWarehouse['CITY_ID'] = $this->city;


            //����û���ϸ��Ϣ
            $insertId = M('Erp_displace_warehouse')->add($displaceWarehouse);

            if ($insertId) {
                $result['status'] = 1;
                $result['msg'] = '�ף��û���ϸ��ӳɹ���';
            } else {
                $result['msg'] = '�ף��û���ϸ���ʧ�ܣ������ԣ�';
            }

            //���ؽ����
            $result['msg'] = g2u($result['msg']);
            die(@json_encode($result));

        } else if ($faction == 'saveFormData' && $postId > 0) { //��������

            //��ȡ����ID
            $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;

            //�ж�״̬
            $currentRequisiton = $displaceModel->getDisplaceById($postId, array('CASE_ID,STATUS'));

            if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                $currentRequisiton[0]['STATUS'] != 0
            ) {
                $result['msg'] = '�ף���δ�ύ�����û�������ϸ���ܱ༭Ŷ��';
                $result['forward'] = U('Displace/displaceDetail', $this->_merge_url_param);
            }

            $displaceWarehouse = array();
            $displaceWarehouse['DR_ID'] = $displaceRequisitionId; //�û���ID
            $displaceWarehouse['BRAND'] = u2g(strip_tags($_POST['BRAND'])); //Ʒ��
            $displaceWarehouse['MODEL'] = u2g(strip_tags($_POST['MODEL'])); //�ͺ�
            $displaceWarehouse['PRODUCT_NAME'] = u2g(strip_tags($_POST['PRODUCT_NAME'])); //Ʒ��
            $displaceWarehouse['NUM'] = intval($_POST['NUM']); //�û�����
            //$displaceWarehouse['INBOUND_NUM'] = intval($_POST['NUM']); //�ڿ�����
            $displaceWarehouse['PRICE'] = floatval($_POST['PRICE']); //�û���
            $displaceWarehouse['SOURCE'] = u2g(strip_tags($_POST['SOURCE'])); //��Դ
            $displaceWarehouse['LIVETIME'] = $_POST['LIVETIME']; //��Դ
            //$displaceWarehouse['STATUS'] = $_POST['STATUS']; //���״̬
            $displaceWarehouse['ADD_USERID'] = $this->uid; //�����
            $displaceWarehouse['ALARMTIME'] = $_POST['ALARMTIME']; //����ʱ��
            $displaceWarehouse['CASE_ID'] = intval($caseId);

            //�����û���ϸ��Ϣ
            $updateRet = M('Erp_displace_warehouse')
                ->where('ID = ' . $postId)->save($displaceWarehouse);

            if ($updateRet !== false) {
                $result['status'] = 1;
                $result['msg'] = '�ף��޸ĳɹ�!';
            } else {
                $result['msg'] = '�ף��޸�ʧ��!';
            }

            //���ؽ����
            $result['msg'] = g2u($result['msg']);
            die(@json_encode($result));

        } if ($faction == 'delData') { //ɾ����¼

            $delId = intval($_GET['ID']); //ɾ��ID

            //�ж�״̬
            $currentRequisiton = $displaceModel->getDisplaceDetailById($delId, array('CASE_ID,STATUS'));

            if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                $currentRequisiton[0]['STATUS'] != 0
            ) {
                $result['msg'] = '�ף���δ�ύ�����û���ϸ����ɾ��Ŷ��';
            }

            if ($delId > 0) {

                //ɾ����ϸ
                $delDisplaceRet = $displaceModel->delDisplaceDetailById($delId);

                if ($delDisplaceRet) {
                    $result['status'] = 'success';
                    $result['msg'] = g2u('�ף�ɾ���ɹ�!');
                } else {
                    $result['status'] = 'error';
                    $result['msg'] = g2u('�ף�ɾ��ʧ��,������!');
                }
            }

            //������
            die(@json_encode($result));

        } else { //����չ��

            Vendor('Oms.Form');
            $form = new Form(); //��ʼ��

            $condWhere = " DR_ID = '" . $displaceRequisitionId . "'"; //����
            $form = $form->initForminfo(210)->where($condWhere);

            //��ȡ����ID
            $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;

            //�ֶ�չ��
            $requisitionStatus = $displaceModel->get_conf_list_status();
            $form = $form->setMyField('INBOUND_STATUS', 'LISTCHAR', array2listchar($requisitionStatus), TRUE);
            $form = $form->setMyField('CASE_ID', 'LISTSQL', 'SELECT C.ID,PROJECTNAME FROM ERP_PROJECT P INNER JOIN ERP_CASE C ON P.ID = C.PROJECT_ID WHERE C.ID = ' . $caseId, FALSE);
            $form = $form->setMyField('ADD_USERID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', FALSE);

            //չ��Ϊ�б�
            if($showForm == 0){
                //״̬Ϊ0ʱ
                $form->EDITCONDITION = '%STATUS% == 0';
                $form->DELCONDITION = '%STATUS% == 0';

                //δ�ύ��ʱ��ſ������
                $displaceStatus = $displaceModel->getDisplaceById($displaceRequisitionId,array('CASE_ID','STATUS'));
                if($displaceStatus[0]['STATUS'] != 0)
                    $form->ADDABLE = 0;
            }else{
                $form = $form->setMyFieldVal('CASE_ID', $caseId, TRUE);
                $form = $form->setMyFieldVal('ADD_USERID', $this->uid, TRUE);
                if($showForm==3){ //��� ��������ʱ��
                    $form->setMyField('ADD_TIME','FORMVISIBLE',0,false);
                }
                if($showForm==1){
                    $form->setMyfield('ADD_TIME','EDITABLE',0,TRUE);
                }
            }

            // ����Ǳ༭������״̬����Ԥ�Ȼ�ȡ���������б�
            //todo ����չ���б�
            if ($showForm == 1 || ($showForm == 3 && empty($faction))) {
                if (!empty($_REQUEST['CASE_TYPE'])) {
                    $this->assign('product_name_autocomplete', '1');  // Ʒ����������
                } else {
                    $this->assign('product_name_autocomplete', '-1');  // Ʒ������������
                }
            }

            //��˺�༭ɾ����ť����ʾ
            $RequisitionStatus = M("Erp_displace_requisition")->where("ID=".$displaceRequisitionId)->getField('STATUS');
            if($RequisitionStatus != 0){
                $form->EDITABLE = '0';
                $form->DELABLE = '0';
            }
            $formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
            $this->assign('form', $formHtml);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->display('displaceDetail');
        }
    }


    /**
     * +----------------------------------------------------------
     * �û������Ƿ�����ύ����
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function checkDisplaceDetailById() {

        //���ظ�ʽ
        $result = array(
            'status'=>0,
            'msg'=>'',
            'forward'=>'',
            'data'=>null,
        );


        //�û�ID
        $drId = !empty($_GET['drId']) ? intval($_GET['drId']) : 0;

        //���ݼ��
        if(!$drId){
            $result['msg'] = g2u('�ף���ѡ���û����뵥!');
            die(@json_encode($result));
        }

        //��ϸ����
        $displaceWarehouse = M('Erp_displace_warehouse')->where('DR_ID = ' . $drId)->select();

        if(!$displaceWarehouse){
            $result['msg'] = g2u('�ף����û���ϸ,�޷��ύ�û����룡');
        }
        else{
            $result['status'] = 1;
            $result['msg'] = g2u('�ף��ύ�û�����ɹ���');
        }


        //var_dump($result);
        //���ؽ��
        die(@json_encode($result));
    }

    /**
     * +----------------------------------------------------------
     * �û��ֿ�
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function wareHouse(){
        //���ղ���
        $showForm = isset($_REQUEST['showForm'])?intval($_REQUEST['showForm']):0;

        $displaceModel = D('Displace');

        Vendor('Oms.Form');
        $form = new Form(); //��ʼ��

        $form = $form->initForminfo(211);

        $sql = "SELECT A.ID,A.BRAND,A.MODEL,A.PRODUCT_NAME,A.SOURCE,A.INBOUND_STATUS AS INBOUND_STATUS,A.ALARMTIME,
                A.LIVETIME,DECODE(SUBSTR(A.PRICE,1,1),'.','0'||A.PRICE,A.PRICE) AS PRICE,
                A.NUM,I.CONTRACT_NO AS CONTRACT_ID,P.PROJECTNAME AS PROJECT_NAME,
                TO_CHAR(A.CHANGETIME,'YYYY-MM-DD ') DAMAGETIME,A.INBOUND_TIME,A.UPDATE_TIME,
                GETDISPLACEORDERID(A.ALARMTIME, A.LIVETIME, A.ID, A.INBOUND_STATUS) AS ORDER_ID
                FROM ERP_DISPLACE_WAREHOUSE A
                LEFT JOIN ERP_CASE C  ON A.CASE_ID = C.ID
                LEFT JOIN ERP_DISPLACE_REQUISITION R ON R.ID = A.DR_ID
                LEFT JOIN ERP_INCOME_CONTRACT I ON I.ID = R.CONTRACT_ID
                LEFT JOIN ERP_PROJECT P ON P.ID = C.PROJECT_ID WHERE A.STATUS =2 AND A.CITY_ID =".$this->channelid;;  //status = 2 ��ʾ�û��������ͨ��

        $form->SQLTEXT = "($sql)";
        $form->orderField = "ORDER_ID DESC";

        $displaceStatus = $displaceModel->get_conf_list_status();
        $form->setMyField("INBOUND_STATUS","LISTCHAR",array2listchar($displaceStatus));
        //��ťȨ��
        $form->GABTN = "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='export_excel'>��������</a>";
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='confirm_inbound'>ȷ�����</a>";
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='discount_sale'>�ۣ��磩������</a>";
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='inner_use'>��˾�ڲ�����</a>";
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='damage_report'>����</a>";

        //���α༭��ɾ�������
        enableRecordReadOnly($form);

        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        // ��ҳ�洫���ϴμ�������
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        // �����������
        $this->assign('filter_sql',$form->getFilterSql());
        // �����������
        $this->assign('sort_sql',$form->getSortSql());
        $tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ?
            $this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
        $this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));

        //ҳ��չ��
        $this->display('displace_warehouse');
    }

    /**
     * +----------------------------------------------------------
     * �û��ֿ�ȷ�����
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function confirmInbound(){

        //���ض���
        $result = array(
            'status'=>0,
            'msg'=>'',
            'data'=>null,
        );

        //��֤����
        $fId =  $_REQUEST["fId"];
        $numList = $_REQUEST["num"];

        $errorStr = '';
        foreach($fId as $key=>$val){

            if(intval($numList[$key])==0){
                $errorStr .= '��' . ($key + 1) . '�У���,����д��Ӧ���������' . "\n";
                continue;
            }

            $displaceNum = M('Erp_displace_warehouse')
                ->field('NUM')
                ->where('ID = ' . $val)
                ->find();

            //��������û�������
            if($numList[$key] > $displaceNum['NUM']){
                $errorStr .= '��' . ($key + 1) . '�У���,�����������ʵ���û�����!' . "\n";
                continue;
            }
        }

        //������֤����
        if(!empty($errorStr)){
            $result['msg'] = $errorStr;
            die(json_encode(g2u($result)));
        }

        //������
        D()->startTrans();
        foreach($numList as $key=>$num){

            $data = array();

            //��ȡԭʼ����
            $queryRet = M("Erp_displace_warehouse")
                ->where("id =".$fId[$key])
                ->select();

            $numOld = $queryRet[0]['NUM']; //δ�������
            //���������
            if($num == $numOld){
                $data['INBOUND_TIME'] = date('Y-m-d H:i:s'); //���ʱ��
                $data['INBOUND_STATUS'] = 2; //�����״̬
                $data['INBOUND_USERID'] = $this->uid; //�����
                $res = M("Erp_displace_warehouse")
                    ->where("id =".$fId[$key])
                    ->save($data);

            //���С���û���������
            }else if($num < $numOld){
                $data = $queryRet[0];
                $data['INBOUND_TIME'] = date('Y-m-d H:i:s'); //���ʱ��
                $data['INBOUND_STATUS'] = 2; //���״̬�������
                $data['INBOUND_USERID'] = $this->uid; //�����
                $data['PARENTID'] = $fId[$key];
                $data['NUM'] = $num; //�������

                //ʱ��ת��
                $data['LIVETIME'] = oracle_date_format($data['LIVETIME']);
                $data['ALARMTIME'] = oracle_date_format($data['ALARMTIME']);
                $data['ADD_TIME'] = oracle_date_format($data['ADD_TIME']);
                unset($data['ID']);

                $resLess = M("Erp_displace_warehouse")
                    ->add($data);

                $numNew = $numOld - $num; //ʣ������
                $updateRet = D()->query("update erp_displace_warehouse set NUM ='{$numNew}' where ID =".$fId[$key]);
            }

            if($res===false || $resLess===false || $updateRet===false){
                $result['msg'] = g2u('�ף����ʧ�ܣ�������!');
                D()->rollback();
                die(json_encode($result));
            }
        }
        //�ύ
        D()->commit();

        $result['status'] = 1;
        $result['msg'] = g2u('���ɹ�!');
        die(json_encode($result));
    }

    /**
     * @param null $form
     */
    private function enableRecordReadOnly(&$form = null) {
        if (empty($form)) {
            return;
        }

        $form->EDITABLE = 0;  // ���ܱ༭
        $form->ADDABLE = 0;  // ��������
        $form->DELABLE = 0;  // ����ɾ��
    }

    /**
     * ��������
     */
    public function saleMange() {

        //���ղ���
        $typeOpreate = isset($_REQUEST['typeParam'])?trim($_REQUEST['typeParam']):'';
        $faction = isset($_REQUEST['faction'])?trim($_REQUEST['faction']):'';


        if ($faction == 'delData') { //ɾ����¼

            $delId = intval($_GET['ID']); //ɾ��ID

            //�ж�״̬
            $currentApplyStatus = D("InboundUse")->getApplyListStatusById($delId);

            if (is_array($currentApplyStatus) && !empty($currentApplyStatus) &&
                $currentApplyStatus[0]['STATUS'] != 0
            ) {
                $result['msg'] = '�ף���δ�ύ�����������ɾ��Ŷ��';
                $result['forward'] = U('Displace/saleMange', $this->_merge_url_param);
            }

            if ($delId > 0) {

                D()->startTrans();

                //���¿��ֵ
                $updateApplyRet = D("InboundUse")->updateInboundUseById($delId);

                //ɾ����ϸ
                $delApplyRet = D("InboundUse")->delDisplaceApplyById($delId);

                if ($delApplyRet !== false && $updateApplyRet!==false) {
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
        $form = new Form(); //��ʼ��

        $form = $form->initForminfo(213);
        enableRecordReadOnly($form);  // ��¼ֻ�ܲ鿴
        $form->DELABLE = -1;  //�ſ�ɾ��
        $form->DELCONDITION = '%STATUS% == 0';

        $where = " WHERE A.TYPE = " . $this->typeParam[$typeOpreate]['type'] ." AND A.CITY_ID = ".$this->city;  // todo

        $form->SQLTEXT = sprintf("(%s%s)", self::SALE_MANAGE_LIST_SQL, $where);;

        if($this->typeParam[$typeOpreate]['opreateButton']) {
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='change_sale'>�������</a>";
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='apply_invoice'>���뿪Ʊ</a>";
        }
        //�ύ������
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='submit_flow'>�ύ������</a>";

        if(!$this->typeParam[$typeOpreate]['isInvoice']) {
            $form->setMyField('INVOICE_STATUS', 'GRIDVISIBLE', '0', FALSE);
            $form->setMyField('INVOICE_STATUS', 'FORMVISIBLE', '0', FALSE);
        }

        //������ڲ����ò鿴��Ҳ���ʾ
        if($typeOpreate == "baosun" || $typeOpreate == "neibulingyong"){
            $form->setmyfield('BUYER','GRIDVISIBLE',0);
            $form->setmyfield('BUYER','FORMVISIBLE',0);
            $form->setmyfield('BUYER','SORT',0);
            $form->setmyfield('BUYER','FILTER',0);

        }
        // ��ҳ��
        $form->setChildren(array(
            array($this->typeParam[$typeOpreate]['tag'], U('Displace/saleManageDetail', $this->_merge_url_param))
        ));
        $typeParam = $caseType = !empty($_REQUEST['typeParam']) ? $_REQUEST['typeParam'] : 'default';
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(),$typeParam);
        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->assign('typeOpreate', $typeOpreate);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        // �����������
        $this->assign('filter_sql',$form->getFilterSql());
        // �����������
        $this->assign('sort_sql',$form->getSortSql());
        $tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ?
        $this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;

        $this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));

        $this->display('sale_manage');
    }

    /**
     * �����������ϸ�б�
     */
    public function saleManageDetail() {

        //���ղ���
        $typeOpreate = isset($_REQUEST['typeParam'])?trim($_REQUEST['typeParam']):'';
        $parentChooseId = $_REQUEST['parentchooseid'];

        Vendor('Oms.Form');
        $form = new Form(); //��ʼ��

        $form->initForminfo(214);
        $sql = self::SALE_MANAGE_DETAIL_SQL;
        $form->FKFIELD = 'LIST_ID';
        $form->SQLTEXT = "($sql)";
        $type =M("Erp_displace_applylist")->where("ID=".$parentChooseId)->getField("TYPE");
        $form->setMyField('NUM','GRIDVISIBLE',0);
        //1=>������2=>�ڲ����� 3=>���� 4=>�������
        if($type == 3){
            $form->setMyField('MONEY','GRIDVISIBLE',0);
        }

        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
		// ��ҳ�洫���ϴμ�������
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->display('sale_manage_detail');
    }

    /**
     * ��ȡ������ϸ
     */
    public function getApplyDetail() {
        $listId = $_REQUEST['list_id'];
        if (intval($listId) <= 0) {
            ajaxReturnJSON(false, g2u('ȱ�ٲ���'));
        }

        try {
            $response = array();
            $dbResult = D('DisplaceApply')->getSaleDetailList($listId);
            if ($dbResult !== false) {
                $response['list'] = $dbResult;
                $response['total_money'] = getTotalMoney($dbResult, 'AMOUNT', 'MONEY');
                ajaxReturnJSON(1, '��ȡ������ϸ�ɹ�', $response);
            } else {
                ajaxReturnJSON(0, '��ȡ������ϸʧ��');
            }
        } catch (Exception $e) {
            ajaxReturnJSON(0, $e->getMessage());
        }
    }

    /**
     * ���������
     */
    public function doSaleChange() {
        $post = $_POST;
        $post['city_id'] = $this->channelid; //��ֵ����

        if (notEmptyArray($post)) {
            D()->startTrans();
            $dbResult = D('DisplaceApply')->saveSaleChange($post);
            if ($dbResult !== false) {
                D()->commit();
                ajaxReturnJSON(1, '�������ɹ�', $dbResult);
            } else {
                D()->rollback();
                ajaxReturnJSON(0, '������ʧ��');
            }
        } else {
            ajaxReturnJSON(0, 'ȱʧ����');
        }
    }

    /**
     * �����������
     */
    public function saleChangeManage() {
        // ɾ���������
        $faction = $_REQUEST['faction'];
        if ($faction == 'delData') {
            $id = $_REQUEST['ID'];
            if (intval($id) <= 0) {
                ajaxReturnJSON(0, 'ȱ�ٲ���');
            }

            try {
                D()->startTrans();
                $msg = '';
                $dbResult = D('DisplaceApply')->deleteSaleChange($id, $msg);
                if ($dbResult !== false) {
                    D()->commit();
                    $response['status'] = 'success';
                    $response['msg'] = g2u('ɾ���ɹ�');
                } else {
                    D()->rollback();
                    $msg = empty($msg) ? 'ɾ��ʧ��' : $msg;
                    $response['status'] = 'error';
                    $response['msg'] = g2u($msg);
                }

            } catch (Exception $e) {
                D()->rollback();
                $response['status'] = 'error';
                $response['msg'] = g2u('�������ڲ�����');
            }

            echo json_encode($response);
            exit;
        }

        Vendor('Oms.Form');
        $form = new Form(); //��ʼ��
        $displaceModel = D('Displace');
        $form = $form->initForminfo(213);
        $form->setMyField('BUYER', 'GRIDVISIBLE', -1)
            ->setMyField('BUYER', 'FORMVISIBLE', -1)
             ->setMyField('INVOICE_STATUS', 'GRIDVISIBLE', 0)
             ->setMyField('INVOICE_STATUS', 'FORMVISIBLE', 0);
        $form->EDITABLE = 0;  // ���ܱ༭
        $form->DELABLE = -1;  // ����ɾ��
        $form->DELCONDITION = '%STATUS% == 0';
        $where = " WHERE a.status = 5 ";
        $where = " WHERE a.type = 4 and a.city_id = ".$this->channelid;  // todo
        $form->SQLTEXT = sprintf("(%s%s)", self::SALE_MANAGE_LIST_SQL, $where);
        $form->GABTN = "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='commit_change_apply'>�ύ�������</a>";

        // ��ҳ��
        $form->setChildren(array(
            array('���������ϸ', U('Displace/saleManageDetail', $this->_merge_url_param))
        ));
        $requisitionStatus = $displaceModel->get_conf_requisition_status();
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar($requisitionStatus), TRUE);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);
        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        // �����������
        $this->assign('filter_sql',$form->getFilterSql());
        // �����������
        $this->assign('sort_sql',$form->getSortSql());
        $tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ?
            $this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
        $this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));


        $this->display('sale_manage');
    }

    /**
     * �����û���Դ��¼
     */
    public function export() {
        $sql = self::EXPORT_SALE_MANAGE_DETAIL_SQL;
        $sql = '(' . $sql . ')';
        $sql = " SELECT * FROM {$sql} WHERE 1 = 1";

        if ($_GET['filter']) {
            $sql .= sprintf(" %s ", $_GET['filter']);
        }

        if ($_GET['sort']) {
            $sql .= sprintf(" %s ", $_GET['sort']);
        }
        $records = D()->query($sql);
        $dataFormat = array(
            'ID' => array(
                'name' => '���'
            ),
            'PROJECT_NAME' => array(
                'name' => '��Ŀ����'
            ),
            'BRAND' => array(
                'name' => 'Ʒ��'
            ),
            'MODEL' => array(
                'name' => '�ͺ�'
            ),
            'PRODUCT_NAME' => array(
                'name' => 'Ʒ��'
            ),
            'SOURCE' => array(
                'name' => '��Դ'
            ),
            'PRICE' => array(
                'name' => '����'
            ),
            'NUM' => array(
                'name' => '����'
            ),
            'ALARMTIME' => array(
                'name' => '����ʱ��'
            ),
            'LIVETIME' => array(
                'name' => '��Чʱ��'
            ),
            'DAMAGETIME' => array(
                'name' => '����ʱ��'
            )
        );

        $this->initExport($objPHPExcel, $objActSheet, '�û���Դ������¼�б�', self::DEFAULT_EXCEL_COLUMN_WIDTH, self::DEFAULT_EXCEL_ROW_HEIGHT);
        $row = 1;
        $this->commonExportAction($objActSheet, $records, $row, '�û���Դ������¼�б�', $dataFormat, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));
        ob_end_clean();
        ob_start();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=" . '�û���Դ������¼�б�' . date("YmdHis") . ".xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * �����û��ֿ��¼
     */
    public function exportDisplace() {

        //��ȡ���ݵ�sql���
        $sql = "SELECT * FROM (SELECT A.ID,A.BRAND,A.MODEL,A.PRODUCT_NAME,A.SOURCE,A.INBOUND_STATUS AS INBOUND_STATUS,
                TO_CHAR(A.ALARMTIME,'YYYY-MM-DD') NEW_ALARMTIME,A.ALARMTIME,
                TO_CHAR(A.LIVETIME,'YYYY-MM-DD') NEW_LIVETIME,A.LIVETIME,
                A.INBOUND_TIME,A.UPDATE_TIME,
                DECODE(SUBSTR(A.PRICE,1,1),'.','0'||A.PRICE,A.PRICE) AS PRICE,
                to_number(A.NUM) AS NUM,I.CONTRACT_NO AS CONTRACT_ID,P.PROJECTNAME AS PROJECT_NAME,
                TO_CHAR(A.CHANGETIME,'YYYY-MM-DD') DAMAGETIME,
                TO_CHAR(A.INBOUND_TIME,'YYYY-MM-DD HH24:mi:ss') NEW_INBOUND_TIME,
                TO_CHAR(A.UPDATE_TIME,'YYYY-MM-DD HH24:mi:ss') NEW_UPDATE_TIME,
                GETDISPLACEORDERID(A.ALARMTIME, A.LIVETIME, A.ID, A.INBOUND_STATUS) AS ORDER_ID
                FROM ERP_DISPLACE_WAREHOUSE A
                LEFT JOIN ERP_DISPLACE_REQUISITION  R ON R.ID = A.DR_ID
                LEFT JOIN ERP_INCOME_CONTRACT I ON I.ID = R.CONTRACT_ID
                LEFT JOIN ERP_CASE C  ON A.CASE_ID = C.ID
                LEFT JOIN ERP_PROJECT P ON P.ID = C.PROJECT_ID WHERE A.STATUS =2 AND R.CITY_ID =".$this->channelid. ") WHERE 1=1 ";

        //��������
        if ($_GET['filter'])
            $sql .= sprintf(" %s ", $_GET['filter']);

        //��������
        if ($_GET['sort'])
            $sql .= sprintf(" %s ", $_GET['sort']);

        $records = D()->query($sql);

        $inboundStatus = D("Displace")->get_conf_list_status(); //��ȡ�ڿ�״̬

        $dataFormat = array(
            'ID' => array(
                'name' => '���'
            ),
            'CONTRACT_ID' => array(
                'name' => '��ͬ���'
            ),
            'PROJECT_NAME' => array(
                'name' => '��Ŀ����'
            ),
            'BRAND' => array(
                'name' => 'Ʒ��'
            ),
            'MODEL' => array(
                'name' => '�ͺ�'
            ),
            'PRODUCT_NAME' => array(
                'name' => 'Ʒ��'
            ),
            'SOURCE' => array(
                'name' => '��Դ'
            ),
            'PRICE' => array(
                'name' => '����'
            ),
            'NUM' => array(
                'name' => '����',
                'dataType'=>'number',
            ),
            'NEW_ALARMTIME' => array(
                'name' => '����ʱ��'
            ),
            'NEW_LIVETIME' => array(
                'name' => '��Чʱ��'
            ),
            'NEW_INBOUND_TIME' => array(
                'name' => '���ʱ��'
            ),
            'NEW_UPDATE_TIME' => array(
                'name' => '����ʱ��'
            ),
            'INBOUND_STATUS' => array(
                'name' => '��Ʒ״̬',
                'map' => $inboundStatus,
            ),
        );

        $this->initExport($objPHPExcel, $objActSheet, '�û���Դ�ֿ��¼�б�', self::DEFAULT_EXCEL_COLUMN_WIDTH, self::DEFAULT_EXCEL_ROW_HEIGHT);
        $row = 1;
        $this->commonExportAction($objActSheet, $records, $row, '�û���Դ�ֿ��¼�б�', $dataFormat, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));

        ob_end_clean();
        ob_start();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=" . '�û���Դ�ֿ��¼�б�' . date("YmdHis") . ".xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    /**
     * ��ȡѡ���û��ֿ���ϸ
     */
    public function  ajaxGetDisplaceData(){
        //���ض���
        $response = array(
            'status' => false,
            'msg' => "",
            'total' => 0,
            'list' => array()
        );

        //��ȡ����
        $act = trim($_POST['act_name']); //������Ϊ
        $DisplaceIds = $_POST['displace_ids']; //�û�ID

        if(notEmptyArray($DisplaceIds)){
            $DisplaceIdStr = sprintf('(%s)', implode(',', $DisplaceIds));
            $sql = <<<DISPLACE_SQL
                SELECT A.ID,
                       R.CONTRACT_ID AS CONTRACT,
                       P.PROJECTNAME,
                       A.BRAND,
                       A.MODEL,
                       A.PRODUCT_NAME,
                       A.SOURCE,
                       RTRIM(to_char(A.PRICE,'fm99999999990.99'),'.') AS PRICE,
                       A.NUM,
                       A.INBOUND_STATUS
                FROM ERP_DISPLACE_WAREHOUSE A
                LEFT JOIN ERP_DISPLACE_REQUISITION R ON R.ID = A.DR_ID
                LEFT JOIN ERP_CASE C ON A.CASE_ID = C.ID
                LEFT JOIN ERP_PROJECT P ON P.ID = C.PROJECT_ID
                WHERE A.ID IN {$DisplaceIdStr}
DISPLACE_SQL;
            $dbDisplaceList = D()->query($sql);

            $mapDisplaceList = array();

            //ѭ���ж�
            foreach($dbDisplaceList as $key=>$displace ){
                if($displace['INBOUND_STATUS'] != 2){
                    ajaxReturnJSON(false, g2u(sprintf('���Ϊ%d���û���Ʒ��״̬���ǡ�����⡱,���ܽ��иò�����', $displace['ID'])));
                }
                if($act == "discount_sale"){
                    if($key < count($dbDisplaceList) -1 ){
                        if($dbDisplaceList[$key]['CONTRACT'] !== $dbDisplaceList[$key + 1]['CONTRACT'] ||
                            $dbDisplaceList[$key]['PROJECTNAME'] !== $dbDisplaceList[$key + 1]['PROJECTNAME']){
                            ajaxReturnJSON(false, g2u("ֻ����ͬ����Ŀ���ƺͺ�ͬ���ܽ�������"));
                        }
                    }
                }
                if($displace['NUM'] == 0){
                    ajaxReturnJSON(false, g2u(sprintf('���Ϊ%d���û���Ʒ���������Ϊ0�������ύ���иò�����', $displace['ID'])));
                }

                //��ͬ��
                $displace['CONTRACT'] = M("Erp_income_contract")->where("ID=".$displace['CONTRACT'])->getField('CONTRACT_NO');
                $mapDisplaceList[] = $displace;
                $response['total'] += 1;
                $response['total_money'] += round($displace['PRICE'] * $displace['NUM'],2);
            }

            $response['status'] = true;
            $response['list'] = $mapDisplaceList;
        }

        ajaxReturnJSON(true, g2u('success'), g2u($response));
    }

    /**
     * �����Ʒ�ۣ��磩������ ״̬1
     * �ڲ����� ״̬2
     * ���� ״̬3
     */
    public function ajaxPostInboundUse(){

        //���ض���
        $response = array(
            'status'=>0,
            'msg'=>'',
            'flowType'=>null,
            'flowId'=>'',
        );

        //��������
        $displaceData = $_REQUEST['displace_data']; //����������ϸ
        $inboundUse = $_REQUEST['Inbound_status']; //״̬

        //������֤
        $errorStr = '';
        if($displaceData['list']){
            foreach($displaceData['list'] as $key=>$val){
                $sql = 'select num,price from erp_displace_warehouse where id = ' . $val['id'];
                $queryRet = D()->query($sql);

                if(empty($queryRet)){
                    $errorStr = '�ף��ύʧ�ܣ�������!';
                    break;
                }

                if(intval($val['amount'])<=0 || floatval($val['money'])<=0){
                    $errorStr = '�ף���������������Ϊ�ղ��Ҳ���С��0!';
                    break;
                }

                if($queryRet[0]['NUM'] && $queryRet[0]['NUM'] < $val['amount']){
                    $errorStr = '�ף���˶��ύ����!';
                    break;
                }
            }
        }

        if($errorStr) {
            $response['msg'] = g2u($errorStr);
            die(json_encode($response));
        }

        $flag = false; //������ʾ

        D()->startTrans(); //����ʼ
        $insertData = array();
        $insertData['APPLY_TIME'] = date('Y-m-d H:i:s');
        $insertData['APPLY_USER_ID'] = $this->uid;
        $insertData['TYPE'] =  $inboundUse;  //����������
        $insertData['APPLY_REASON'] = u2g($displaceData['reason']);
        $insertData['STATUS'] = 0; //״̬δ�ύ
        $insertData['BUYER'] = u2g($displaceData['buyer']); //���
        $insertData['CITY_ID'] = $this->city;

        $insertId = M("Erp_displace_applylist")
            ->add($insertData);

        if($insertId > 0){
            foreach ($displaceData['list'] as $displaceSale){
                //������ϸ����
                $itemData['DID'] = $displaceSale['id'];
                $itemData['LIST_ID'] = $insertId;
                $itemData['AMOUNT'] = $displaceSale['amount'];
                $itemData['MONEY'] = $displaceSale['money'];

                $insertDetailId = M("Erp_displace_applydetail")
                    ->add($itemData);
                if ($insertDetailId === false ) {
                    break;
                }

                //����ҵ�����,�������Ӧ�ļ���
                $sql = 'UPDATE ERP_DISPLACE_WAREHOUSE SET NUM = NUM - ' . $displaceSale['amount'] . ' WHERE ID = ' . $displaceSale['id'];

                $dbResult = M("Erp_displace_warehouse")->query($sql);
                if ($dbResult === false ) {
                    break;
                }
            }

        }

        $flowDisplayTypePY = D("InboundUse")->get_flow_displace_type(); //��ȡTYPE����

        //���ؽ����
        if(!$insertId || $dbResult === false){
            D()->rollback();
            $response['msg'] = g2u("�ף�����ʧ�ܣ������ԣ�");
        }else{
            D()->commit();
            $response['status'] = 1;
            $response['msg'] = g2u("�ף������ɹ���");
            $response['flowTypePinYin'] = $flowDisplayTypePY[$inboundUse];
            $response['flowId'] = $insertId;
        }
        echo json_encode($response);
    }


    /**
     * ��ȡ��Ʊ���
     */
    function getTotalMoney() {

        //���ؽ����
        $response = array(
            'status'=>0,
            'msg'=>'',
            'data'=>null,
        );

        $listId = intval($_REQUEST['list_id']);

        $totalMoney = D('Displace')->getSaleTotal($listId);

        if($totalMoney){
            $response['status'] = 1;
            $response['data']['totalMoney'] = $totalMoney;
        }

        die(@json_encode($response));
    }

    /**
     * �������뿪Ʊ
     */
    public function applyInvoice() {
        $request = $_POST['request'];

        //��֤����

        $listId = intval($request['list_id']);

        $sql = 'SELECT INVOICE_STATUS FROM ERP_DISPLACE_APPLYLIST WHERE ID = '. $listId;
        $saleListInfo = D()->query($sql);

        if(!$saleListInfo || $saleListInfo[0]['INVOICE_STATUS']!=1){
            ajaxReturnJSON(0, '��Ʊ״̬������δ����״̬��');
        }

        try {
            D()->startTrans();
            $dbResult = D('DisplaceApply')->doAddInvoice($request);
            if ($dbResult !== false) {
                D()->commit();
                ajaxReturnJSON(1, '�ύ��Ʊ����ɹ�', $dbResult);
            } else {
                D()->rollback();
                ajaxReturnJSON(0, '�������ڲ�����');
            }
        } catch (Exception $e) {
            D()->rollback();
            ajaxReturnJSON(0, '�������ڲ�����');
        }
    }

    /**
     * �ύ�����������
     */
    public function commitSaleChange() {
        $request = $_POST['request'];
        try {
            $dbResult = D('DisplaceApply')->getApplyList($request);
            if (notEmptyArray($dbResult)) {
                ajaxReturnJSON(1, '����ɹ�', $dbResult);
            } else {
                ajaxReturnJSON(0, '�������ڲ�����');
            }
        } catch (Exception $e) {
            ajaxReturnJSON(0, '�������ڲ�����');
        }
    }

    /**
     * +----------------------------------------------------------
     * ��ѯ�������Ƿ�����ύ����
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function checkDisplaceFlow() {

        //���ض���
        $return = array(
            'status'=>0,
            'msg'=>'',
            'data'=>null,
        );


        $dId = !empty($_GET['dId']) ? intval($_GET['dId']) : 0;

        //������֤
        if(!$dId){
            $return['msg'] = '��ѡ������һ����¼';
            die(json_encode($return));
        }

        //״̬�˶�
        $sql = 'SELECT STATUS FROM ERP_DISPLACE_APPLYLIST WHERE ID = ' . $dId;
        $queryRet = D()->query($sql);

        if($queryRet[0]['STATUS']==0){
            $return['status'] = 1;
            $return['msg'] = g2u('�ύ����ɹ�');
        }

        die(@json_encode($return));
    }


    /**
     * ��ȡ�û�����Ĺ�����ID
     */
    public function getFlowId() {
        $response = array(
            'status' => false,
            'message' => '��������',
            'data' => ''
        );
        $displaceId = $_REQUEST['displaceId'];
        if (intval($displaceId) > 0) {
            try {
                $result = D()->query(sprintf(self::DISPLACE_FLOWID_SQL, $displaceId));
                if (notEmptyArray($result)) {
                    $response['status'] = true;
                    $response['message'] = '��ȡ������ID�ɹ�';
                    $response['data'] = $result[0]['ID'];
                } else {
                    $response['message'] = '���û�������δ��������!';
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
        }

        echo json_encode(g2u($response));
    }

}

/* End of file PurchaseAction.class.php */