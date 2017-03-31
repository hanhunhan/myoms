<?php

/**
 * 后佣管理Model
 *
 * @author xuke
 */
class PostCommissionModel extends Model {
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'POST_COMMISSION';

    /**
     * 根据分销明细的变动修改数据
     * @param $req
     * @return bool
     */
    public function updateThroughInvoiceDetailChange($req) {
        $response = false;
        if (notEmptyArray($req)) {
            $req['commission_payment_status'] = $this->getPaymentStatus($req);
            $req['commission_invoice_status'] = $this->getInvoiceStatus($req);
            $req['total_paid_amount'] = $this->getMemberTotalPaidAmount($req);
            $response = $this->handlePaymentStatus($req);
        }

        return $response;
    }

    /**
     * 获取分销会员已回款金额的总额
     * @param $req
     * @return int
     */
    private function getMemberTotalPaidAmount($req) {
        $response = 0;
        if (intval($req['post_commission_id'])) {
            $sql = <<<SQL
            SELECT nvl(sum(p.money), 0) amount
            FROM erp_payment_records p
            WHERE p.billing_record_id IN
                (SELECT d.billing_record_id
                 FROM erp_commission_invoice_detail d
                 WHERE d.post_commission_id = %d
                   AND d.invoice_status = 3)
SQL;
            $dbResult = $this->query(sprintf($sql, $req['post_commission_id']));
            if (notEmptyArray($dbResult)) {
                $response = $dbResult[0]['AMOUNT'];
            }
        }

        return $response;
    }

    /**
     * 获取分销记录回款状态
     * @param $req
     * @return int|mixed
     */
    public function getPaymentStatus($req) {
        $response = 1;
        if (intval($req['post_commission_id'])) {
            $response = $this->where("ID = {$req['post_commission_id']}")->getField('payment_status');
        }
        return $response;
    }

    public function getInvoiceStatus($req) {
        $response = 1;
        if (intval($req['post_commission_id'])) {
            $response = $this->where("ID = {$req['post_commission_id']}")->getField('invoice_status');
        }

        return $response;
    }

    /**
     * 更新佣金记录的回款状态
     * @param $req
     * @return bool
     */
    private function handlePaymentStatus($req) {
        $response = false;
        if (notEmptyArray($req)) {
            $needUpdateMember = false;
            $needUpdateDetail = false;
            $updateData = array();
            $updateDetailData = array();
            switch($req['detail_payment_status']) {
                // 明细置为未回款
                case 1:
                    if ($req['commission_payment_status'] == 2) {
                        $paidInvoiceCount = D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$req['post_commission_id']} AND (PAYMENT_STATUS = 2 OR PAYMENT_STATUS = 3)")->count();
                        if ($paidInvoiceCount == 0) {
                            $updateData['PAYMENT_STATUS'] = 1;  // 更新为未回款
                            $updateDetailData['PAYMENT_AMOUNT'] = 0;
                            $needUpdateDetail = true;
                        }
                    }
                    break;
                // 明细置为部分回款
                case 2:
                    // 原状态为未回款
                    if ($req['commission_payment_status'] == 1) {
                        // 待更新状态更新为部分回款
                        $updateData['PAYMENT_STATUS'] = 2;
                    }
                    break;
                // 默认置为完全回款
                default:
                    if ($req['commission_invoice_status'] == 3) {
                        $noPaidInvoiceCount = D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$req['post_commission_id']} AND (INVOICE_STATUS = 1 OR INVOICE_STATUS = 2)")->count();
                        if ($noPaidInvoiceCount == 0) {
                            $updateData['PAYMENT_STATUS'] = 3;  // 已回款
                            $needUpdateMember = true;
                        } else {
                            $updateData['PAYMENT_STATUS'] = 2;  // 部分回款
                            $needUpdateMember = true;
                        }
                    } else {
                        $updateData['PAYMENT_STATUS'] = 2;  // 部分回款
                    }
//                    if (floatval($req['total_paid_amount']) === floatval($req['total_price_after'])) {  // 已完全回款
//                        $updateData['PAYMENT_STATUS'] = 3;  // 已回款
//                        $needUpdateMember = true;
//                    } else if (floatval($req['total_paid_amount']) < floatval($req['total_price_after'])) {
//                        $updateData['PAYMENT_STATUS'] = 2;
//                    }
            }
            $updateData['UPDATETIME'] = date('Y-m-d H:i:s');
            $response = $this->where("ID = {$req['post_commission_id']}")->save($updateData);

            if ($response !== false && $needUpdateMember) {
                // 会员结佣状态改为已结佣
                $response = D('Member')->where("ID = {$req['card_member_id']}")->save(array('REWARD_STATUS' => 3));
            }

            if ($response !== false && $needUpdateDetail) {
                $response = D('erp_commission_invoice_detail')->where("ID = {$req['invoice_id']}")->save($updateDetailData);
            }
        }
        return $response;
    }
}
