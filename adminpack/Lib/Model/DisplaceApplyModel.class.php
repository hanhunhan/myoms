<?php

/**
 * 置换资源售卖模型
 *
 * @author xuke
 */
class DisplaceApplyModel extends Model{
    protected $tablePrefix = 'ERP_';
    protected $tableName = 'DISPLACE_APPLYLIST';  // 置换资源售卖列表
    protected $tableNameDetail = 'DISPLACE_APPLYDETAIL';  // 置换资源售卖详情

    const AUDIT_PASS_STATUS = 2;

    /**
     * 售卖明细列表SQL
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
     * 获取开票相关的记录
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
     * 删除明细的SQL语句
     */
    const DELETE_DETAIL_SQL = <<<DELETE_DETAIL_SQL
        DELETE
        FROM erp_displace_applydetail
DELETE_DETAIL_SQL;

    /**
     * 删除明细的SQL语句
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
     * 获取售卖申请单的明细列表
     * @param $listId int 售卖申请单
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
                //$temp['REMAIN_AMOUNT'] = intval($temp['NUM']); 库存不需要处理
                unset($temp['NUM']);
                $response []= $temp;
            }
        }
        return $response;
    }

    /**
     * 保存售卖变更数据
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
            // 添加售卖变更申请单
            $insertData = array();
            $insertData['BUYER'] = $buyer;
            $insertData['STATUS'] = 0;  // 未申请
            $insertData['TYPE'] = 4;  // 售卖变更
            $insertData['ORG_SALE_LIST_ID'] = $listId;  // 原售卖ID
            $insertData['CASE_ID'] = $post['case_id'];  // 案例编号
            $insertData['CITY_ID'] = $post['city_id'];  // 城市编号
            $insertData['APPLY_USER_ID'] = $_SESSION['uinfo']['uid'];
            $insertData['APPLY_TIME'] = date('Y-m-d H:i:s');
            empty($remark) or $insertData['APPLY_REASON'] = $remark;

            $dbResult = D('erp_displace_applylist')->add($insertData);
            if ($dbResult === false) {
                return false;
            }
            $appListId = $dbResult;

            // 添加售卖变更明细
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

                // 更改库存数量
                $carryAmount = intval($item['oldAmount']) - intval($item['newAmount']);
                $warehouseId = intval($item['did']);

                //if($carryAmount < 0) //如果改大则变更库存值
                //    $dbResult = D('erp_displace_warehouse')->where("ID = {$warehouseId}")->setInc("NUM", $carryAmount);

                if ($dbResult === false) {
                    return false;
                }
            }

            if ($dbResult !== false) {
                $dbResult = D('erp_displace_applylist')->where("ID = {$listId}")->save(array('STATUS' => 4));  // 状态改为售卖变更中
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
     * 删除售卖变更记录
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
        // 只有处于未提交的状态才可以删除
        if ($status != 0) {
            $msg = '只有处于未提交状态才可以删除';
            return false;
        }

        $dbResult = $this->revertSaleChange2Warehouse($id, $msg);
        if ($dbResult === false) {
            return false;
        }


        if (intval($orgSaleListId) <= 0) {
            $msg = '不存在售卖记录，无法删除';
            return false;
        }

        // 先删除明细
        $sql = self::DELETE_DETAIL_SQL . " WHERE list_id = {$id} ";
        $dbResult = $this->query($sql);
        if ($dbResult === false) {
            return false;
        }

        // 再删除售卖单
        $sql = self::DELETE_LIST_SQL . " WHERE id = {$id} AND type = 4 ";
        $dbResult = $this->query($sql);
        if ($dbResult === false) {
            return false;
        }

        // 再更新状态
        $dbResult = $this->where("ID = {$orgSaleListId}")->save(array(
            'STATUS' => self::AUDIT_PASS_STATUS
        ));

        if ($dbResult === false) {
            return false;
        }
    }


    /**
     * 更新状态
     * @param $listId  recording的ID
     * @return bool
     */
    public function updateListStatus($listId,$status){
        // 将开票状态设置为申请中
        $dbResult = M("erp_displace_applylist")->where("ID = {$listId}")->save(array(
            'INVOICE_STATUS' => $status
        ));

        if($dbResult===false){
            return false;
        }

        return true;
    }


    /**
     * 添加开票记录
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
                $insertData['INVOICE_TYPE'] = 1;  // 合同开票
                $insertData['STATUS'] = 1;
                $insertData["INVOICE_CLASS"] = $invoiceClass; // 1=普通开票， 2=专用发票
                $insertData["INVOICE_BIZ_TYPE"] = $invoiceBizType;  // 发票类型：1=广告费，2=服务费
                $insertData["FROMTYPE"] = 2; //来源类型
                $insertData["FROMLISTID"] = $saleListId; //来源业务ID
                if (floatval($taxRate) >= 0) {
                    $insertData["TAX"] = round($insertData['INVOICE_MONEY'] * $taxRate / (1 + $taxRate) , 2);
                } else {
                    $insertData["TAX"] = 0;  // 税率为0
                }

                $dbResult = D("BillingRecord")->add($insertData);
                $invoiceId = $dbResult;
                if ($dbResult !== false) {
                    // 将开票状态设置为申请中
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
     * 计算总额
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
     * 获取资源置换申请单（几种类型通用）
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
     * 将置换仓库的数据退回到原仓库
     */
    public function revertSaleChange2Warehouse($listId, &$msg = '') {
        if (intval($listId) <= 0) {
            $msg = '参数错误';
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
                    $msg = '退库错误';
                    return false;
                }
            }
        }

        return true;
    }
}