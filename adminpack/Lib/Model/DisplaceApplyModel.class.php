<?php

/**
 * �û���Դ����ģ��
 *
 * @author xuke
 */
class DisplaceApplyModel extends Model{
    protected $tablePrefix = 'ERP_';
    protected $tableName = 'DISPLACE_APPLYLIST';  // �û���Դ�����б�
    protected $tableNameDetail = 'DISPLACE_APPLYDETAIL';  // �û���Դ��������

    const AUDIT_PASS_STATUS = 2;

    /**
     * ������ϸ�б�SQL
     */
    const SALE_DETAIL_LIST_SQL = <<<SALE_MANAGE_DETAIL_SQL
        SELECT
            d.id,
            d.list_id,
            d.amount,
            RTRIM(to_char(d.money,'fm99999999990.99'),'.') AS money,
            w.id AS did,
            w.brand,
            w.model,
            w.product_name,
            w.source,
            w.inbound_status as status,
            w.alarmtime,
            w.livetime,
            RTRIM(to_char(w.price,'fm99999999990.99'),'.') AS WAREHOUSE_PRICE,
            p.contract AS contract_no,
            p.projectname AS project_name,
            to_char(w.changetime,'yyyy-MM-dd ') DAMAGETIME,
            (CASE WHEN w.inbound_status = 5 THEN 0 ELSE w.num END ) NUM,
            w.use_num
        FROM ERP_DISPLACE_APPLYDETAIL d
        LEFT JOIN ERP_DISPLACE_WAREHOUSE w ON w.id = d.did
        LEFT JOIN erp_case c ON w.case_id = c.id
        LEFT JOIN erp_project p ON p.id = c.project_id
SALE_MANAGE_DETAIL_SQL;

    /**
     * ��ȡ��Ʊ��صļ�¼
     */
    const SALE_MANAGE_BILLING_LIST = <<<SALE_MANAGE_BILLING_LIST
        SELECT
            w.case_id,
            i.id AS contract_id,
            d.money,
            d.amount,
            a.city_id
        FROM erp_displace_applylist a
        LEFT JOIN erp_displace_applydetail d ON d.list_id = a.id
        LEFT JOIN erp_displace_warehouse w ON w.id = d.did
        LEFT JOIN erp_displace_requisition r on r.id = w.dr_id
        LEFT JOIN erp_income_contract i ON i.id = r.contract_id
SALE_MANAGE_BILLING_LIST;

    /**
     * ɾ����ϸ��SQL���
     */
    const DELETE_DETAIL_SQL = <<<DELETE_DETAIL_SQL
        DELETE
        FROM erp_displace_applydetail
DELETE_DETAIL_SQL;

    /**
     * ɾ����ϸ��SQL���
     */
    const DELETE_LIST_SQL = <<<DELETE_LIST_SQL
        DELETE
        FROM erp_displace_applylist
DELETE_LIST_SQL;

    const APPLY_LIST_SQL = <<<APPLY_LIST_SQL
        SELECT
            a.case_id,
            a.city_id,
            a.buyer,
            a.apply_reason
        FROM erp_displace_applylist a
APPLY_LIST_SQL;

    const SALE_CHANGE_APPLY_DETAIL_SQL = <<<SALE_CHANGE_APPLY_DETAIL_SQL
        SELECT
            d1.did as warehouse_id,
            d1.amount as new_amount,
            d2.amount as old_amount
        FROM erp_displace_applydetail d1
        LEFT JOIN erp_displace_applydetail d2 ON d2.id = d1.org_sale_detail_id


SALE_CHANGE_APPLY_DETAIL_SQL;

    /**
     * ��ȡ�������뵥����ϸ�б�
     * @param $listId int �������뵥
     * @return bool|mixed
     */
    public function getSaleDetailList($listId) {
        if (intval($listId) <= 0) {
            return false;
        }

        $response = array();
        $where = " WHERE d.list_id = {$listId} ";
        $order = " ORDER BY d.id DESC ";
        $sql = self::SALE_DETAIL_LIST_SQL . $where . $order;

        $dbResult = $this->query($sql);
        if (notEmptyArray($dbResult)) {
            foreach ($dbResult as $item) {
                $temp = $item;
                //$temp['REMAIN_AMOUNT'] = intval($temp['NUM']); ��治��Ҫ����
                unset($temp['NUM']);
                $response []= $temp;
            }
        }
        return $response;
    }

    /**
     * ���������������
     * @param array $post
     * @return bool
     */
    public function saveSaleChange($post = array(), &$msg = '') {
        if (notEmptyArray($post)) {
            $items = $post['items'];
            $remark = trim(u2g($post['remark']));
            $buyer = trim(u2g($post['buyer']));
            $listId = $post['list_id'];
            $response = null;
            // �������������뵥
            $insertData = array();
            $insertData['BUYER'] = $buyer;
            $insertData['STATUS'] = 0;  // δ����
            $insertData['TYPE'] = 4;  // �������
            $insertData['ORG_SALE_LIST_ID'] = $listId;  // ԭ����ID
            $insertData['CASE_ID'] = $post['case_id'];  // �������
            $insertData['CITY_ID'] = $post['city_id'];  // ���б��
            $insertData['APPLY_USER_ID'] = $_SESSION['uinfo']['uid'];
            $insertData['APPLY_TIME'] = date('Y-m-d H:i:s');
            empty($remark) or $insertData['APPLY_REASON'] = $remark;

            $dbResult = D('erp_displace_applylist')->add($insertData);
            if ($dbResult === false) {
                return false;
            }
            $appListId = $dbResult;

            // ������������ϸ
            foreach ($items as $item) {
                $insertData = array();
                $insertData['DID'] = $item['did'];
                $insertData['AMOUNT'] = $item['newAmount'];
                $insertData['MONEY'] = $item['newMoney'];
                $insertData['LIST_ID'] = $appListId;
                $insertData['ORG_SALE_DETAIL_ID'] = $item['id'];

                $dbResult = D('erp_displace_applydetail')->add($insertData);
                if ($dbResult === false) {
                    return false;
                }

                // ���Ŀ������
                $carryAmount = intval($item['oldAmount']) - intval($item['newAmount']);
                $warehouseId = intval($item['did']);

                //if($carryAmount < 0) //����Ĵ��������ֵ
                //    $dbResult = D('erp_displace_warehouse')->where("ID = {$warehouseId}")->setInc("NUM", $carryAmount);

                if ($dbResult === false) {
                    return false;
                }
            }

            if ($dbResult !== false) {
                $dbResult = D('erp_displace_applylist')->where("ID = {$listId}")->save(array('STATUS' => 4));  // ״̬��Ϊ���������
            }

            if ($dbResult === false) {
                return false;
            }
        } else {
            return false;
        }

        return $appListId;
    }

    /**
     * ɾ�����������¼
     * @param $id
     * @return bool
     */
    public function deleteSaleChange($id, &$msg) {
        if (intval($id) <= 0) {
            return false;
        }

        $dbResult = $this->field("ORG_SALE_LIST_ID, STATUS")->where(" ID = {$id} ")->find();
        if ($dbResult === false) {
            return false;
        }
        $orgSaleListId = $dbResult['ORG_SALE_LIST_ID'];
        $status = $dbResult['STATUS'];
        // ֻ�д���δ�ύ��״̬�ſ���ɾ��
        if ($status != 0) {
            $msg = 'ֻ�д���δ�ύ״̬�ſ���ɾ��';
            return false;
        }

        $dbResult = $this->revertSaleChange2Warehouse($id, $msg);
        if ($dbResult === false) {
            return false;
        }


        if (intval($orgSaleListId) <= 0) {
            $msg = '������������¼���޷�ɾ��';
            return false;
        }

        // ��ɾ����ϸ
        $sql = self::DELETE_DETAIL_SQL . " WHERE list_id = {$id} ";
        $dbResult = $this->query($sql);
        if ($dbResult === false) {
            return false;
        }

        // ��ɾ��������
        $sql = self::DELETE_LIST_SQL . " WHERE id = {$id} AND type = 4 ";
        $dbResult = $this->query($sql);
        if ($dbResult === false) {
            return false;
        }

        // �ٸ���״̬
        $dbResult = $this->where("ID = {$orgSaleListId}")->save(array(
            'STATUS' => self::AUDIT_PASS_STATUS
        ));

        if ($dbResult === false) {
            return false;
        }
    }


    /**
     * ����״̬
     * @param $listId  recording��ID
     * @return bool
     */
    public function updateListStatus($listId,$status){
        // ����Ʊ״̬����Ϊ������
        $dbResult = M("erp_displace_applylist")->where("ID = {$listId}")->save(array(
            'INVOICE_STATUS' => $status
        ));

        if($dbResult===false){
            return false;
        }

        return true;
    }


    /**
     * ��ӿ�Ʊ��¼
     * @param array $post
     * @return bool
     * @internal param $saleListId
     */
    public function doAddInvoice($post = array()) {
        $saleListId = $post['list_id'];
        $invoiceStatus = $post['invoice_status'];
        $invoiceClass = $post['invoice_class'];
        $invoiceBizType = $post['invoice_biz_type'];
        $invoiceRemark = u2g($post['invoice_desc']);
        $totalMoney= $post['total_amount'];
        if (intval($saleListId) < 0 || $invoiceStatus != 1) {
            return false;
        }

        $where = " WHERE a.id = {$saleListId} AND a.type = 1 ";
        $sql = self::SALE_MANAGE_BILLING_LIST . $where;
        $response = false;
        try {
            $dbResult = $this->query($sql);
            if (notEmptyArray($dbResult)) {
                //$totalMoney = $this->getTotalMoney($dbResult);
                $cityPY = D('erp_city')->where("ID = {$dbResult[0]['CITY_ID']}")->getField('PY');
                $taxRate = get_taxrate_by_citypy($cityPY);
                $insertData['CASE_ID'] = $dbResult[0]['CASE_ID'];
                $insertData['CONTRACT_ID'] = $dbResult[0]['CONTRACT_ID'];
                $insertData['INVOICE_MONEY'] = $totalMoney;
                $insertData['CREATETIME'] = date('Y-m-d H:i:s');
                $insertData['APPLY_USER_ID'] = $_SESSION['uinfo']['uid'];
                $insertData['REMARK'] = $invoiceRemark;
                $insertData['INVOICE_TYPE'] = 1;  // ��ͬ��Ʊ
                $insertData['STATUS'] = 1;
                $insertData["INVOICE_CLASS"] = $invoiceClass; // 1=��ͨ��Ʊ�� 2=ר�÷�Ʊ
                $insertData["INVOICE_BIZ_TYPE"] = $invoiceBizType;  // ��Ʊ���ͣ�1=���ѣ�2=�����
                $insertData["FROMTYPE"] = 2; //��Դ����
                $insertData["FROMLISTID"] = $saleListId; //��Դҵ��ID
                if (floatval($taxRate) >= 0) {
                    $insertData["TAX"] = round($insertData['INVOICE_MONEY'] * $taxRate / (1 + $taxRate) , 2);
                } else {
                    $insertData["TAX"] = 0;  // ˰��Ϊ0
                }

                $dbResult = D("BillingRecord")->add($insertData);
                $invoiceId = $dbResult;
                if ($dbResult !== false) {
                    // ����Ʊ״̬����Ϊ������
                    $dbResult = $this->where("ID = {$saleListId}")->save(array(
                        'INVOICE_STATUS' => 2
                    ));
                }
                if ($dbResult !== false) {
                    $response['record_id'] = $insertData['CONTRACT_ID'];
                    $response['case_id'] = $insertData['CASE_ID'];
                    $response['invoice_id'] = $invoiceId;
                }
            }
        } catch (Exception $e) {
            return false;
        }

        return $response;
    }

    /**
     * �����ܶ�
     * @param array $data
     * @return float|int
     */
    private function getTotalMoney($data = array()) {
        $response = 0;
        if (notEmptyArray($data)) {
            foreach ($data as $item) {
                $response += floatval($item['AMOUNT']) * floatval($item['MONEY']);
            }
        }

        return $response;
    }

    /**
     * ��ȡ��Դ�û����뵥����������ͨ�ã�
     * @param array $request
     */
    public function getApplyList($request = array()) {
        if (empty($request)) {
            return false;
        }

        $listId = $request['list_id'];
        if (intval($listId) <= 0) {
            return false;
        }

        $response = array();
        $dbResult = $this->field('id')->where("ID = {$listId}")->find();
        if (notEmptyArray($dbResult)) {
            $response = $dbResult;
        }

        return $response;
    }

    /**
     * ���û��ֿ�������˻ص�ԭ�ֿ�
     */
    public function revertSaleChange2Warehouse($listId, &$msg = '') {
        if (intval($listId) <= 0) {
            $msg = '��������';
            return false;
        }

        $where = " WHERE d1.list_id = {$listId} ";
        $sql = self::SALE_CHANGE_APPLY_DETAIL_SQL . $where;
        $dbResult = $this->query($sql);
        if (notEmptyArray($dbResult)) {
            $detailList = $dbResult;
            foreach ($detailList as $item) {
                $carryAmount = intval($item['NEW_AMOUNT']) - intval($item['OLD_AMOUNT']);
                $warehouseId = $item['WAREHOUSE_ID'];
                $sql = <<<UPDATE_WAREHOUSE_SQL
                    UPDATE erp_displace_warehouse
                    SET num = num + {$carryAmount}
                    WHERE id = {$warehouseId}
UPDATE_WAREHOUSE_SQL;

                if ($this->query($sql) === false) {
                    $msg = '�˿����';
                    return false;
                }
            }
        }

        return true;
    }
}