<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ����������������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/12/2
 */

class DisplaceSaleChange extends FlowBase {
    /**
     * ���ͨ��״̬
     */
    const AUDIT_PASS_STATUS = 2;

    /**
     * �������ǰ�����Ϣ
     */
    const SALE_CHANGE_BEFORE_AFTER_SQL = <<<SALE_CHANGE_BEFORE_AFTER_SQL
        SELECT
            a.org_sale_list_id AS org_list_id,
            d1.did as displace_warehouse_id,
            d1.id AS new_detail_id,
            d1.amount AS new_amount,
            d1.money AS new_money,
            d2.id AS old_detail_id,
            d2.amount as old_amount,
            d2.money as old_money
        FROM erp_displace_applylist a
        LEFT JOIN erp_displace_applydetail d1 ON d1.list_id = a.id
        LEFT JOIN erp_displace_applydetail d2 ON d2.id = d1.org_sale_detail_id
SALE_CHANGE_BEFORE_AFTER_SQL;

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
    }

    function nextstep($flowId) {
        $this->model->startTrans();
        $result = $this->workflow->nextstep($flowId);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    function createHtml($flowId) {
        // TODO: Implement createHtml() method.
    }

    /**
     * ת��
     * @param $data
     * @return bool
     */
    function handleworkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->handleworkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * ͨ��
     * @param $data
     * @return bool
     */
    function passWorkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->passWorkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * ���
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {
        $this->model->startTrans();
        D()->startTrans();
        $result = $this->workflow->notWorkflow($data);
        if ($result !== false) { //������ù�
            $result = $this->afterDenySuccess($data['recordId']);
        }

        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    private function doChangeDisplaceWarehouse($data = array(), &$warehouse = array(), &$orgListId = 0, &$msg = '') {
        if (empty($data)) {
            return false;
        }

        if($data['diff_amount']>0){ //�������ֵ�����������򱸰�ʧ��
            $msg = '�������ڲ�����0';
            return false;
        }

        $updateApplyDetailData = array();
        $updateApplyDetailData['AMOUNT'] = $data['new_amount'];
        $updateApplyDetailData['MONEY'] = $data['new_price'];
        $dbResult = D('erp_displace_applydetail')->where("ID = {$data['old_detail_id']}")->save($updateApplyDetailData);
        if ($dbResult === false) {
            $msg = '�������ڲ�����1';
            return false;
        }

        //���¿��ֵ
        $sql = "SELECT A.*,to_char(A.INBOUND_TIME,'YYYY-MM-DD hh24:mi:ss') as NEW_INBOUND_TIME FROM ERP_DISPLACE_WAREHOUSE A WHERE A.ID = " . $data['displace_warehouse_id'];
        $queryRet = D()->query($sql);
        if($queryRet===false) {
            $msg = '�������ڲ�����2';
            return false;
        }

        if($data['diff_amount']<0){

            //����һ���µ�����
            $addData = $queryRet[0];
            $addData['NUM'] = - $data['diff_amount']; //����
            $addData['INBOUND_STATUS'] = 2; //�����
            $addData['USE_NUM'] = 0; //��Ŀ����ֵΪ0
            //ʱ��ת��
            $addData['LIVETIME'] = oracle_date_format($addData['LIVETIME'],'Y-m-d H:i:s');
            $addData['ALARMTIME'] = oracle_date_format($addData['ALARMTIME'],'Y-m-d H:i:s');
            $addData['ADD_TIME'] = oracle_date_format($addData['ADD_TIME'],'Y-m-d H:i:s');
            $addData['INBOUND_TIME'] = $addData['NEW_INBOUND_TIME'];

            unset($addData['ID']);
            unset($addData['UPDATE_USERID']);
            unset($addData['UPDATE_TIME']);
            unset($addData['PARENTID']);

            $resLess = M("Erp_displace_warehouse")
                ->add($addData);

            if($resLess===false){
                $msg = '�������ڲ�����3';
                return false;
            }

            //����ԭ���ֵ
            $sql = "update erp_displace_warehouse set num = num + {$data['diff_amount']} where id = " . $data['displace_warehouse_id'];
            $updateRet  = D()->query($sql);

            if($updateRet === false){
                $msg = '�������ڲ�����4';
                return false;
            }
        }

        // ��ԭ������״̬�����������״̬�޸�Ϊδ��Ʊ״̬
        $dbResult = D('erp_displace_applylist')->where("ID = {$orgListId}")->save(array(
            'STATUS' => self::AUDIT_PASS_STATUS
        ));

        return $dbResult;
    }

    /**
     * ���ѿ��
     * @param array $data
     * @return bool
     */
    private function changeDisplaceWarehouse($data = array(), &$warehouse, $orgListId) {
        // �õ�ԭ������¿���������۸���Ϣ
        if (empty($data)) {
            return false;
        }

        foreach ($data as $item) {
            // �Ȳ����ٸ���
            $dbResult = $this->doChangeDisplaceWarehouse($item, $warehouse, $orgListId);
            if ($dbResult === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * ���³ɱ���
     */
    private function addIncomeList() {

        return true;
    }

    /**
     * �����¾ɿ�����
     * @param $data
     * @return array|void
     */
    private function calcWarehouseDiff($data) {
        if (empty($data)) {
            return;
        }

        // ����ֵ��ȥ��ֵ
        $response = array();
        $diffAmount = intval($data['NEW_AMOUNT']) - intval($data['OLD_AMOUNT']);
        $diffTotalMoney = floatval($data['NEW_AMOUNT'] * $data['NEW_MONEY']) - floatval($data['OLD_AMOUNT'] * $data['OLD_MONEY']);

        $response['displace_warehouse_id'] = $data['DISPLACE_WAREHOUSE_ID'];
        $response['diff_amount'] = $diffAmount;
        $response['new_amount'] = $data['NEW_AMOUNT'];
        $response['new_detail_id'] = $data['NEW_DETAIL_ID'];
        $response['old_detail_id'] = $data['OLD_DETAIL_ID'];
        $response['new_price'] = $data['NEW_MONEY'];
        $response['diff_total_money'] = $diffTotalMoney;

        return $response;
    }

    /**
     * ��ȡ���ǰ�����Ϣ
     * @param $recordId
     * @param $displaceWarehouseDiff
     * @param $totalMoneyDiff
     * @return bool
     */
    private function getCompareSaleChange($recordId, &$displaceWarehouseDiff, &$totalMoneyDiff, &$orgListId = 0) {
        if (intval($recordId) <= 0) {
            return false;
        }

        try {
            $where = " WHERE a.id = {$recordId} ";
            $sql = self::SALE_CHANGE_BEFORE_AFTER_SQL . $where;

            $dbResult = D()->query($sql);
            if ($dbResult === false) {
                return false;
            }

            $orgListId = $dbResult[0]['ORG_LIST_ID'];  // ԭ�����б�ID
            $displaceWarehouseDiff = array();
            foreach ($dbResult as $item) {
                $temp = $this->calcWarehouseDiff($item);
                $displaceWarehouseDiff [] = $temp;
                $totalMoneyDiff += $temp['diff_total_money'];
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * ����������ɹ�
     * @param $listId
     * @param string $msg
     * @return bool
     */
    public function afterDenySuccess($listId, &$msg = '') {
        if (intval($listId) <= 0) {
            return true;
        }

        $where = " WHERE a.id = {$listId} ";
        $sql = self::SALE_CHANGE_BEFORE_AFTER_SQL . $where;
        $dbResult = D()->query($sql);
        if ($dbResult === false) {
            return false;
        }

        $orgListId = $dbResult[0]['ORG_LIST_ID'];  // ԭ�����б�ID
        $result = D('erp_displace_applylist')->where("ID = {$orgListId}")->save(array(
            'STATUS' => self::AUDIT_PASS_STATUS
        ));

        return $result;
    }

    /**
     * ��������ɹ�֮��Ĵ���
     */
    private function afterSaleChangeSuccess($recordId) {
        $displaceWarehouseDiff = array();  // �¾ɴ洢���컯
        $totalIncomeDiff = 0;  // ����Ĳ�ֵ

        $dbResult = $this->getCompareSaleChange($recordId, $displaceWarehouseDiff, $totalIncomeDiff, $orgListId);
        if ($dbResult === false) {
            return false;
        }

        // �Ƚ�ԭ����еļ�¼���з���
        $dbResult = $this->changeDisplaceWarehouse($displaceWarehouseDiff, $warehouse, $orgListId);
        if ($dbResult === false) {
            return false;
        }

        return $dbResult !== false;
    }

    /**
     * ����
     * @param $data
     * @return bool
     */
    function finishworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Flow/flowList', 'status=1'),
        );
        $auth = $this->workflow->flowPassRole($data['flowId']);

        if(!$auth) {
            $response['message'] = 'δ�����ؾ���ɫ';
            return $response;
        }

        $this->model->startTrans();
        $response['status'] = $this->workflow->finishworkflow($data);
        if ($response['status']) {
            // ��������ɹ�֮���Ƚ�displace_warehouse���е����ݸ��ģ��ٸ�������Ĳ������ɱ���
            $response['status'] = $this->afterSaleChangeSuccess($data['recordId']);
        }

        if ($response['status'] !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $response;
    }

    /**
     * �ύ�������������
     * @param $data
     * @return array
     */
    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => $_SERVER['HTTP_REFERER']
        );

        // ���Ȩ��
//        $auth = $this->workflow->start_authority('shoumaibiangeng');
        $auth = true;
        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '����Ȩ��';
        } else {
            D()->startTrans();
            $dbResult = $this->workflow->createworkflow($data);
            if ($dbResult !== false) {
                D()->commit();
                $response['status'] = true;
                $response['message'] = '����������ύ��ˣ�';
            } else {
                D()->rollback();
                $response['status'] = false;
                $response['message'] = '�ύ���ʧ�ܣ�';
            }
        }

        return $response;
    }
}