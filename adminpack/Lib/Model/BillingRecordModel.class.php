<?php

/* 
 * 开票管理类
 * author xuyemei
*/

class BillingRecordModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'BILLING_RECORD';
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    //发票状态标识数组
    protected $_invoice_status_remark = array(
                                    1 => "未申请",
                                    2 => "已申请",
                                    3 => "已通过",
                                    4 => "已开票",
                                    5 => "已否决",
                                    6 => "申请换票中",
                                    7 => "申请退票中",
                                    8 => "已换票",
                                    9 => "已退票",
    );
    
    //发票状态数组
    protected $_invoice_status = array(
                                    "no_apply" => 1,
                                    "auditing" => 2,
                                    "have_audited" => 3,
                                    "have_invoiced" => 4,
                                    "have_voted" => 5,
                                    "change_vote" => 6,
                                    "refund_vote" => 7,
                                    "have_change_voted" => 8,
                                    "have_refund_voted" => 9,
    );

    protected $_invoice_class = array(
        1 => "普通发票",
        2 => "专用发票",
    );

    protected $_invoice_biz_type = array(
        1=> "广告费",
        2=> "服务费",
    );
    
    public function get_invoice_status_remark()
   {
        return $this->_invoice_status_remark;
   }

    public  function get_invoice_biz_type_remark(){
        return $this->_invoice_biz_type;
    }
    
    public function get_invoice_status()
    {
        return $this->_invoice_status;
    }

    public function get_invoice_class(){
        return $this->_invoice_class;
    }
    
    /**
     * 新增开票记录
     * $data["CREATETIME"]      //创建时间        
     * $data["INVOICE_MONEY"]   //金额【必填】
     * $data["REMARK"]          //备注【选填】 
     * $data["STATUS"]          //发票状态【必填】，默认为1
     * $data["CONTRACT_ID"]     //关联实体ID（会员id 合同id ……）【必填】
     * $data["APPLY_USER_ID"]   //申请人【必填】
     * $data["CASE_ID"]         //案例编号【必填】
     * $data["INVOICE_TYPE"]    //发票类型（合同开票1 电商会员2  分销会员3）【必填】
     * return $insertId 成功：新增的ID || 失败 : FALSE
     */
    public function add_billing_info($data)
    {
        if(is_array($data) && !empty($data))
    	{
            $billing_arr["CASE_ID"] = $data["CASE_ID"];
            $billing_arr["CREATETIME"] = $data["CREATETIME"];
            $billing_arr["INVOICE_MONEY"] = $data["INVOICE_MONEY"];
            $billing_arr["REMARK"] = $data["REMARK"];
            $billing_arr["STATUS"] = $data["STATUS"] ? $data["STATUS"] : 1;
            $billing_arr["CONTRACT_ID"] = $data["CONTRACT_ID"];
            $billing_arr["APPLY_USER_ID"] = $data["APPLY_USER_ID"];
            $billing_arr["INVOICE_TYPE"] = $data["INVOICE_TYPE"];
            $billing_arr["TAX"] = $data["TAX"];
			$billing_arr["INVOICE_CLASS"] = $data["INVOICE_CLASS"];
			$billing_arr["INVOICE_BIZ_TYPE"] = $data["INVOICE_BIZ_TYPE"];  // 发票类型：1=广告费，2=服务费
			$billing_arr["FILES"] = $data["FILES"];  // 附件列表
            if($data["INVOICE_TIME"])
            {
                $billing_arr["INVOICE_TIME"] = $data["INVOICE_TIME"];
            }
            if($data["INVOICE_NO"])
            {
                $billing_arr["INVOICE_NO"] = $data["INVOICE_NO"];
            }
            
    		// 自增主键返回插入ID
    		$insertId = $this->add($billing_arr);
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
        $benefits_info = array();
        
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
        
        $benefits_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $benefits_info;
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
            return $info;
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
        //echo $this->_sql();
        return $info;
    }
    
    
    /**
     * 根据合同编号或会员编号查找发票信息
     *
     * @access	public
     * @param	string  $conid 
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_info_by_conid($conid, $search_field = array())
    {   
        $info = array();
        
        $cond_where = "";
        
        if(is_array($conid) && !empty($conid))
        {
            $conid_str = implode(',', $conid);
            $cond_where = "CONTRACT_ID IN (".$conid_str.")";
        }
        else 
        {   
            $conid_str = intval($conid_str);
            $cond_where = "CONTRACT_ID = '".$conid_str."'";
        }
        
        if($cond_where == '')
        {
            return $info;
        }
        
        $info = self::get_info_by_cond($cond_where, $search_field);
        
        return $info;
    }

    /**
     * 获取发票来源
     * @param $flowId
     * @return int
     */
    public function bRFromType($flowId){
        $return = array();

        $sql = 'SELECT FROMTYPE,FROMLISTID FROM ERP_BILLING_RECORD WHERE FLOW_ID = ' . $flowId;
        $return = D()->query($sql);

        return $return;
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
     * 根据id删除开票
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
     * 根据条件删除开票
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

    /**
     * 获取发票类目类型 1=广告费 2=服务费
     * @param $case_id
     * @return int|null
     */
    public function get_invoice_biz_type($case_id) {
        $response = null;
        if (intval($case_id)) {
            $scale_type = D('ProjectCase')->where("ID = {$case_id}")->getField('SCALETYPE');
            // 如果是硬广项目，则返回广告费id，否则返回服务费id
            if ($scale_type == 3) {
                $response = 1;  // 广告费id
            } else {
                $response = 2;  // 服务费id
            }
        }

        return $response;
    }

    public function getRemainFxPostComisInvoice($memberId, $postComisId) {
        $response = 0;

        if ($memberId && $postComisId) {
            $dbResult = D('erp_cardmember')->field('CASE_ID, TOTAL_PRICE_AFTER, HOUSETOTAL')->where("ID = {$memberId}")->find();
            if (notEmptyArray($dbResult)) {
                $totalAmount = getFeeScaleAmount($dbResult['CASE_ID'], $dbResult['TOTAL_PRICE_AFTER'], $dbResult['HOUSETOTAL']);
                // 踢出退票后的金额
                $payTotalAmount = floatval(D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$postComisId} AND INVOICE_STATUS = 3")->sum('AMOUNT'));
                $response = round($totalAmount, 2) - $payTotalAmount;
            }
        }

        return round($response, 2);
    }

    public function getRemainFxPostComisInvoiceAmount($memberId, $postComisId) {
        $response = 0;

        if ($memberId && $postComisId) {
            $dbResult = D('erp_cardmember')->field('CASE_ID, TOTAL_PRICE_AFTER, HOUSETOTAL')->where("ID = {$memberId}")->find();
            if (notEmptyArray($dbResult)) {
                $totalAmount = getFeeScaleAmount($dbResult['CASE_ID'], $dbResult['TOTAL_PRICE_AFTER'], $dbResult['HOUSETOTAL']);
                // 踢出退票后的金额
                $payTotalAmount = floatval(D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$postComisId} AND INVOICE_STATUS != 9")->sum('AMOUNT'));
                $response = round($totalAmount, 2) - $payTotalAmount;
            }
        }

        return round($response, 2);
    }

    /**
     * 检查是否为重复的发票号码
     * @param $invoiceNo
     * @return bool
     */
    public function isDuplicateInvoiceNo($invoiceNo,$contractid, $cityId) {
        $response = false;
        if (!empty($invoiceNo)) {
            $sql = <<<INVOICE_COUNT_SQL
                SELECT COUNT(1) CNT
                FROM erp_billing_record r
                LEFT JOIN erp_case c ON c.id = r.case_id
                LEFT JOIN erp_project p ON p.id = c.project_id
                WHERE r.invoice_no = '{$invoiceNo}'
                AND p.city_id = {$cityId}
                AND r.contract_id = {$contractid}
                AND r.status in (4, 6, 7)
INVOICE_COUNT_SQL;

            $dbResult = D()->query($sql);
            if (notEmptyArray($dbResult)) {
                $invoiceNoCount = $dbResult[0]['CNT'];
            } else {
                $invoiceNoCount = 0;
            }

            $response = $invoiceNoCount > 0;
        }

        return $response;

    }
}

/* End of file BillingRecordModel.class.php */
/* Location: ./Lib/Model/BillingRecordModel.class.php */