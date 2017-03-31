<?php

/**
 * ��Ӷ����Model
 *
 * @author xuke
 */
class PostCommissionModel extends Model {
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'POST_COMMISSION';

    /**
     * ���ݷ�����ϸ�ı䶯�޸�����
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
     * ��ȡ������Ա�ѻؿ�����ܶ�
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
     * ��ȡ������¼�ؿ�״̬
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
     * ����Ӷ���¼�Ļؿ�״̬
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
                // ��ϸ��Ϊδ�ؿ�
                case 1:
                    if ($req['commission_payment_status'] == 2) {
                        $paidInvoiceCount = D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$req['post_commission_id']} AND (PAYMENT_STATUS = 2 OR PAYMENT_STATUS = 3)")->count();
                        if ($paidInvoiceCount == 0) {
                            $updateData['PAYMENT_STATUS'] = 1;  // ����Ϊδ�ؿ�
                            $updateDetailData['PAYMENT_AMOUNT'] = 0;
                            $needUpdateDetail = true;
                        }
                    }
                    break;
                // ��ϸ��Ϊ���ֻؿ�
                case 2:
                    // ԭ״̬Ϊδ�ؿ�
                    if ($req['commission_payment_status'] == 1) {
                        // ������״̬����Ϊ���ֻؿ�
                        $updateData['PAYMENT_STATUS'] = 2;
                    }
                    break;
                // Ĭ����Ϊ��ȫ�ؿ�
                default:
                    if ($req['commission_invoice_status'] == 3) {
                        $noPaidInvoiceCount = D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$req['post_commission_id']} AND (INVOICE_STATUS = 1 OR INVOICE_STATUS = 2)")->count();
                        if ($noPaidInvoiceCount == 0) {
                            $updateData['PAYMENT_STATUS'] = 3;  // �ѻؿ�
                            $needUpdateMember = true;
                        } else {
                            $updateData['PAYMENT_STATUS'] = 2;  // ���ֻؿ�
                            $needUpdateMember = true;
                        }
                    } else {
                        $updateData['PAYMENT_STATUS'] = 2;  // ���ֻؿ�
                    }
//                    if (floatval($req['total_paid_amount']) === floatval($req['total_price_after'])) {  // ����ȫ�ؿ�
//                        $updateData['PAYMENT_STATUS'] = 3;  // �ѻؿ�
//                        $needUpdateMember = true;
//                    } else if (floatval($req['total_paid_amount']) < floatval($req['total_price_after'])) {
//                        $updateData['PAYMENT_STATUS'] = 2;
//                    }
            }
            $updateData['UPDATETIME'] = date('Y-m-d H:i:s');
            $response = $this->where("ID = {$req['post_commission_id']}")->save($updateData);

            if ($response !== false && $needUpdateMember) {
                // ��Ա��Ӷ״̬��Ϊ�ѽ�Ӷ
                $response = D('Member')->where("ID = {$req['card_member_id']}")->save(array('REWARD_STATUS' => 3));
            }

            if ($response !== false && $needUpdateDetail) {
                $response = D('erp_commission_invoice_detail')->where("ID = {$req['invoice_id']}")->save($updateDetailData);
            }
        }
        return $response;
    }
}
