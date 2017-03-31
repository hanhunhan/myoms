<?php

/**
 * 回款记录表MODEl
 */

class PaymentRecordModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PAYMENT_RECORDS';//回款记录表

    //回款方式
    private $_conf_payment_method = array(
        '1' => '转支',
        '2' => '电汇',
        '3' => '现金',
        '4' => '置换',
        '5' => '非付现',
        '6' => '划拨',
    );

    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * @return array 获取回款方式
     */
    public function payment_method(){
        return $this->_conf_payment_method;
    }
    
    //新增回款记录
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
     * 根据ID查询信息
     *
     * @access	public
     * @param  mixed $ids ID编号
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 信息
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
     * 根据条件获取信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
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
     * 根据ID更新信息
     *
     * @access	public
     * @param	string  $ids 要更新的记录
     * @param array $update_arr 要跟新的字段
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
     * 根据条件更新信息
     *
     * @access	public
     * @param	string  $cond_where 要更新的记录
     * @param array $update_arr 要跟新的字段的键值对
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
     * 根据id删除回款
     *
     * @access	public
     * @param	mixed $ids id号
     * @param array $update_arr 要跟新的字段的键值对
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
     * 根据条件删除回款
     *
     * @access	public
     * @param	mixed $ids id号
     * @param array $update_arr 要跟新的字段的键值对
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
            // 如果开票的金额与回款的金额相等，则更新对应的回款状态
            if (abs(floatval($invoicedAmount)) < 1) {
                // 更新开票记录对应的回款状态（回款明细记录及佣金记录的）
                // 更新明细的回款状态
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

                // 更新佣金记录的回款状态
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
                            // 完全回款
                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('PAYMENT_STATUS' => 3));
                            if ($response !== false) {
                                // 会员佣金状态改为已结佣
                                $response = D('Member')->where("ID = {$item['CARD_MEMBER_ID']}")->save(array('REWARD_STATUS' => 3));
                            }
                        } else if ($paidAmount > 0) {
                            // 部分回款
                            $paymentStatus = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->getField('PAYMENT_STATUS');
                            if ($paymentStatus == 1) {
                                // 未回款的状态才修改回款状态和结佣状态，部分回款和完成回款不做修改
                                $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('PAYMENT_STATUS' => 2));
                                if ($response !== false) {
                                    // 会员佣金状态改为已申请状态
                                    $response = D('Member')->where("ID = {$item['CARD_MEMBER_ID']}")->save(array('REWARD_STATUS' => 2));
                                }
                            }
                        }
                    }
                }
            } else if ($invoicedAmount < 0) {
                $response = false;
                $msg = sprintf('无法提交，发票的回款金额累计已大于开票金额, 最多能回款的额度是%s元',floatval($_POST['MONEY']) + $invoicedAmount);
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