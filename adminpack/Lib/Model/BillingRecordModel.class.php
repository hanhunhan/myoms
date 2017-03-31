<?php

/* 
 * ��Ʊ������
 * author xuyemei
*/

class BillingRecordModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'BILLING_RECORD';
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    //��Ʊ״̬��ʶ����
    protected $_invoice_status_remark = array(
                                    1 => "δ����",
                                    2 => "������",
                                    3 => "��ͨ��",
                                    4 => "�ѿ�Ʊ",
                                    5 => "�ѷ��",
                                    6 => "���뻻Ʊ��",
                                    7 => "������Ʊ��",
                                    8 => "�ѻ�Ʊ",
                                    9 => "����Ʊ",
    );
    
    //��Ʊ״̬����
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
        1 => "��ͨ��Ʊ",
        2 => "ר�÷�Ʊ",
    );

    protected $_invoice_biz_type = array(
        1=> "����",
        2=> "�����",
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
     * ������Ʊ��¼
     * $data["CREATETIME"]      //����ʱ��        
     * $data["INVOICE_MONEY"]   //�����
     * $data["REMARK"]          //��ע��ѡ� 
     * $data["STATUS"]          //��Ʊ״̬�������Ĭ��Ϊ1
     * $data["CONTRACT_ID"]     //����ʵ��ID����Աid ��ͬid �����������
     * $data["APPLY_USER_ID"]   //�����ˡ����
     * $data["CASE_ID"]         //������š����
     * $data["INVOICE_TYPE"]    //��Ʊ���ͣ���ͬ��Ʊ1 ���̻�Ա2  ������Ա3�������
     * return $insertId �ɹ���������ID || ʧ�� : FALSE
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
			$billing_arr["INVOICE_BIZ_TYPE"] = $data["INVOICE_BIZ_TYPE"];  // ��Ʊ���ͣ�1=���ѣ�2=�����
			$billing_arr["FILES"] = $data["FILES"];  // �����б�
            if($data["INVOICE_TIME"])
            {
                $billing_arr["INVOICE_TIME"] = $data["INVOICE_TIME"];
            }
            if($data["INVOICE_NO"])
            {
                $billing_arr["INVOICE_NO"] = $data["INVOICE_NO"];
            }
            
    		// �����������ز���ID
    		$insertId = $this->add($billing_arr);
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
     * ���ݺ�ͬ��Ż��Ա��Ų��ҷ�Ʊ��Ϣ
     *
     * @access	public
     * @param	string  $conid 
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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
     * ��ȡ��Ʊ��Դ
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
     * ����idɾ����Ʊ
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
     * ��������ɾ����Ʊ
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

    /**
     * ��ȡ��Ʊ��Ŀ���� 1=���� 2=�����
     * @param $case_id
     * @return int|null
     */
    public function get_invoice_biz_type($case_id) {
        $response = null;
        if (intval($case_id)) {
            $scale_type = D('ProjectCase')->where("ID = {$case_id}")->getField('SCALETYPE');
            // �����Ӳ����Ŀ���򷵻ع���id�����򷵻ط����id
            if ($scale_type == 3) {
                $response = 1;  // ����id
            } else {
                $response = 2;  // �����id
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
                // �߳���Ʊ��Ľ��
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
                // �߳���Ʊ��Ľ��
                $payTotalAmount = floatval(D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$postComisId} AND INVOICE_STATUS != 9")->sum('AMOUNT'));
                $response = round($totalAmount, 2) - $payTotalAmount;
            }
        }

        return round($response, 2);
    }

    /**
     * ����Ƿ�Ϊ�ظ��ķ�Ʊ����
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