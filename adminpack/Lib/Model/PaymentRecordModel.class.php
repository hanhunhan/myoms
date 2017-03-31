<?php

/**
 * �ؿ��¼��MODEl
 */

class PaymentRecordModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PAYMENT_RECORDS';//�ؿ��¼��

    //�ؿʽ
    private $_conf_payment_method = array(
        '1' => 'ת֧',
        '2' => '���',
        '3' => '�ֽ�',
        '4' => '�û�',
        '5' => '�Ǹ���',
        '6' => '����',
    );

    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * @return array ��ȡ�ؿʽ
     */
    public function payment_method(){
        return $this->_conf_payment_method;
    }
    
    //�����ؿ��¼
    public function add_refund_records($data)
    {   
        $insertId = 0;
        if(is_array($data) && !empty($data))
    	{ 
            $insertId = $this->add($data);
        }
        
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    /**
     * ����ID��ѯ��Ϣ
     *
     * @access	public
     * @param  mixed $ids ID���
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ϣ
     */
    public function get_info_by_id($ids, $search_field = array())
    {   
        $cond_where = "";
        $info = array();
        
        if(is_array($ids) && !empty($ids))
        {   
            $ids_str = implode(',', $ids);
            $cond_where = " ID IN (".$ids_str.")";
        }
        else
        {
            $id  = intval($ids);
            $cond_where = " ID = '".$id."'";
        }
        
        $info = self::get_info_by_cond($cond_where, $search_field);
        //echo $this->_sql();
        return $info;
    }
    
    /**
     * ����������ȡ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $project_info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        
        return $info;
    }
    
    
    /**
     * ����ID������Ϣ
     *
     * @access	public
     * @param	string  $ids Ҫ���µļ�¼
     * @param array $update_arr Ҫ���µ��ֶ�
     * @return	
     */
    public function update_info_by_id($ids, $update_arr)
    {
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN ($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        
        $res = self::update_info_by_cond($cond_where, $update_arr);
        //echo $this->getLastSql();
        return $res;
    }
    
    
    /**
     * ��������������Ϣ
     *
     * @access	public
     * @param	string  $cond_where Ҫ���µļ�¼
     * @param array $update_arr Ҫ���µ��ֶεļ�ֵ��
     * @return	
     */
    public function update_info_by_cond($cond_where, $update_arr)
    {
        $up_num = 0;
        
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    /**
     * ����idɾ���ؿ�
     *
     * @access	public
     * @param	mixed $ids id��
     * @param array $update_arr Ҫ���µ��ֶεļ�ֵ��
     * @return	
     */
    public function del_info_by_id($ids)
    {
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = ".$ids;
        }        
        $del_num = self::del_info_by_cond($cond_where);
        return $del_num ? $del_num : false;
    }
    
    /**
     * ��������ɾ���ؿ�
     *
     * @access	public
     * @param	mixed $ids id��
     * @param array $update_arr Ҫ���µ��ֶεļ�ֵ��
     * @return	
     */
    public function del_info_by_cond($cond_where = '')
    {
        if($cond_where)
        {
            $del_num = $this->where($cond_where)->delete();
        }
        
       return $del_num ? $del_num : false;
    }

    public function updateFxCommissionPaymentStatus($data, &$msg = '') {
        $response = false;
        if (notEmptyArray($data)) {
            $invoicedAmount = D('PaymentRecord')->getRemainPayAmount($data['BILLING_RECORD_ID']);
            // �����Ʊ�Ľ����ؿ�Ľ����ȣ�����¶�Ӧ�Ļؿ�״̬
            if (abs(floatval($invoicedAmount)) < 1) {
                // ���¿�Ʊ��¼��Ӧ�Ļؿ�״̬���ؿ���ϸ��¼��Ӷ���¼�ģ�
                // ������ϸ�Ļؿ�״̬
                $updateSql = <<<SQL
                    UPDATE erp_commission_invoice_detail d
                    SET d.payment_amount = d.amount,
                        d.payment_status = 3
                    WHERE d.billing_record_id = {$data['BILLING_RECORD_ID']}
SQL;
                $response = D()->query($updateSql);
                if ($response === false) {
                    return false;
                }

                // ����Ӷ���¼�Ļؿ�״̬
                $sql = <<<SQL
                    SELECT d.id,
                           d.amount,
                           d.billing_record_id,
                           d.post_commission_id,
                           m.housetotal,
                           m.total_price_after,
                           m.case_id,
                           c.card_member_id
                    FROM erp_commission_invoice_detail d
                    LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                    LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
                    WHERE d.billing_record_id = {$data['BILLING_RECORD_ID']} and d.invoice_status = 3
SQL;
                $comisInvoiceDetails = D('erp_commission_invoice_detail')->query($sql);
                if (notEmptyArray($comisInvoiceDetails)) {
                    foreach ($comisInvoiceDetails as $item) {
                        $feeScaleAmount = getFeeScaleAmount($item['CASE_ID'], $item['TOTAL_PRICE_AFTER'], $item['HOUSETOTAL']);
                        $paidAmount = D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$item['POST_COMMISSION_ID']} AND PAYMENT_STATUS = 3")->sum('PAYMENT_AMOUNT');
                        if (abs($feeScaleAmount - $paidAmount) < 1) {
                            // ��ȫ�ؿ�
                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('PAYMENT_STATUS' => 3));
                            if ($response !== false) {
                                // ��ԱӶ��״̬��Ϊ�ѽ�Ӷ
                                $response = D('Member')->where("ID = {$item['CARD_MEMBER_ID']}")->save(array('REWARD_STATUS' => 3));
                            }
                        } else if ($paidAmount > 0) {
                            // ���ֻؿ�
                            $paymentStatus = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->getField('PAYMENT_STATUS');
                            if ($paymentStatus == 1) {
                                // δ�ؿ��״̬���޸Ļؿ�״̬�ͽ�Ӷ״̬�����ֻؿ����ɻؿ���޸�
                                $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('PAYMENT_STATUS' => 2));
                                if ($response !== false) {
                                    // ��ԱӶ��״̬��Ϊ������״̬
                                    $response = D('Member')->where("ID = {$item['CARD_MEMBER_ID']}")->save(array('REWARD_STATUS' => 2));
                                }
                            }
                        }
                    }
                }
            } else if ($invoicedAmount < 0) {
                $response = false;
                $msg = sprintf('�޷��ύ����Ʊ�Ļؿ����ۼ��Ѵ��ڿ�Ʊ���, ����ܻؿ�Ķ����%sԪ',floatval($_POST['MONEY']) + $invoicedAmount);
            } else {
                $response = true;
            }
        }
        return $response;
    }

    public function getRemainPayAmount($billingRecordId) {
        $response = 0;
        if ($billingRecordId) {
            $totalAmount = floatval(D('BillingRecord')->where("ID = {$billingRecordId}")->getField('INVOICE_MONEY'));
            $payTotalAmount = floatval(D('PaymentRecord')->where("BILLING_RECORD_ID = {$billingRecordId}")->sum('MONEY'));
            $response = round($totalAmount, 2) - $payTotalAmount;
        }

        return round($response, 2);
    }
}