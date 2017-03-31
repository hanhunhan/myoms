<?php

/**
 * ��ͬ����
 *
 * @author liuhu
 */
class ContractModel extends Model{

    const PROJECT_DELETED_STATUS = 2;
    const PROJECT_TERMINATED_PSTATUS = 5;

    /**
     * ���ҷ��ճ��SCALETYPE
     */
    const FWFSC_SCALETYPE = 8;

    /**
     * Ӳ���SCALETYPE
     */
    const YG_SCALETYPE = 3;

    /**
     * ���SCALETYPE
     */
    const HD_SCALETYPE = 4;
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'INCOME_CONTRACT';

    //���캯��
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * ��Ӻ�ͬ��Ϣ
     * @param array $contract_info ��ͬ��Ϣ
     * info['CASE_ID']          ������š����
     * $info['CONTRACT_NO']     ��ͬ�š����
        $info['COMPANY']        ��ͬǩԼ��˾�����
        $info['START_TIME']     ��ʼʱ�䡾���
        $info['END_TIME']       ����ʱ�䡾���
        $info['PUB_TIME']       �������ڡ�ѡ�
        $info['CONF_USER']      ��ͬȷ���ˡ�ѡ�
        $info['CONF_TIME']      ��ͬȷ��ʱ�䡾ѡ�
        $info['STATUS']         ��ͬ״̬�����
        $info['MONEY']          ��ͬ�����
        $info['CONTRACT_TYPE']  ��ͬ���͡����
        $info['IS_NEED_INVOICE'] �Ƿ���Ҫ��Ʊ��ѡ� Ĭ��Ϊ0
        $info['SIGN_USER']      ǩԼ�ˡ����롿
        $info['ADD_TIME']       ���ʱ�䡾ѡ�
        $info['CITY_PY']        ����ƴ�������
     * @return mixed ��ӳɹ����ز������ݱ�ţ�����ʧ�ܷ���FALSE
     */
    public function add_contract_info($contract_info)
    {
        $insertId = FALSE;
        $info = array();
        
    	if(is_array($contract_info) && !empty($contract_info))
    	{   
            if(!$contract_info['CASE_ID'] || !$contract_info['CONTRACT_NO'] || !$contract_info['COMPANY']
                || !$contract_info['START_TIME'] || !$contract_info['END_TIME'] || !$contract_info['STATUS'] 
 
                /*|| !$contract_info['MONEY']  || !$contract_info['CONTRACT_TYPE'] || !$contract_info['SIGN_USER']*/
                || !$contract_info['CITY_PY'])
 
            {
               js_alert("param error");
                exit;
            }
            //$city_id = $this->where("PY = ".$data['CITY_PY'])->field("ID")->find();
            $sql = "SELECT ID FROM ERP_CITY WHERE PY = '".$contract_info['CITY_PY']."'";
            $city_id = $this->query($sql);
            $city_id = $city_id[0]["ID"];
            $info["CITY_ID"] = $city_id;
            $info['CASE_ID'] = intval($contract_info['CASE_ID']);
            $info['CONTRACT_NO'] = strip_tags($contract_info['CONTRACT_NO']);
            $info['COMPANY'] = strip_tags($contract_info['COMPANY']);
            $info['START_TIME'] = $contract_info['START_TIME'];
            $info['END_TIME'] = $contract_info['END_TIME'];
            $info['PUB_TIME'] = $contract_info['PUB_TIME'];
            $info['CONF_USER'] = intval($contract_info['CONF_USER']);
            $info['CONF_TIME'] = $contract_info['CONF_TIME'];
            $info['STATUS'] = intval($contract_info['STATUS']);
            $info['MONEY'] = floatval($contract_info['MONEY']);
            $info['CONTRACT_TYPE'] = intval($contract_info['CONTRACT_TYPE']);
            $info['IS_NEED_INVOICE'] =  $contract_info['IS_NEED_INVOICE'] ? intval($contract_info['IS_NEED_INVOICE']) : 0;
            $info['SIGN_USER'] = strip_tags($contract_info['SIGN_USER']);
            $info['ADD_TIME'] = strip_tags($contract_info['ADD_TIME']);
            $info['CITY_PY'] = strip_tags($contract_info['CITY_PY']);
            
    		// �����������ز���ID
    		$insertId = $this->add($info);
    	}
        //echo $this->getLastSql();
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ����ҵ��ID��ȡ��ͬ���
     *
     * @access	public
     * @param	int  $case_id ҵ��ID
     * @param array $search_field �����ֶ�
     * @return	array ��ͬ��Ϣ
     */
    public function get_contract_info_by_caseid($case_id, $search_field = array())
    {   
        $contract_info = array();
        
        $case_id = intval($case_id);
        if($case_id > 0)
        {   
            $cond_whewe = "CASE_ID = '".$case_id."'";
            $contract_info = self::get_info_by_cond($cond_whewe, $search_field);
        }
        
        return $contract_info;
    }
    
    
    /**
     * ����ID��ȡ��ͬ���
     *
     * @access	public
     * @param	mixed  $ids �������߶�����ͬID
     * @param array $search_field �����ֶ�
     * @return	array ��ͬ��Ϣ
     */
    public function get_contract_info_by_id($ids, $search_field = array())
    {   
        $contract_info = array();
        
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
        
        $contract_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $contract_info;
    }
    
    
    /**
     * ����������ȡ��Ŀ��ͬ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��ͬ��Ϣ
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $contract_info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $contract_info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $contract_info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $contract_info = $this->where($cond_where)->select();
        }
        //echo $this->getLastSql();
        return $contract_info;
    }
    
    
    /**
     * ����ID���������ͬ��Ϣ
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
            $cond_where = "ID in($id_str)";
        }
        else
        {
            $cond_where = "ID=$ids";
        }
        
        $res = $this->table($table)->where($cond_where)->save($update_arr);
        
        return $res;
    }
    
    /**
     * �����������������ͬ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where Ҫ���µļ�¼
     * @param array $update_arr Ҫ���µ��ֶεļ�ֵ��
     * @return	
     */
    public function update_info_by_cond($cond_where,$update_arr)
    {
        $res = $this->where($cond_where)->save($update_arr);
        //var_dump($res);
       // echo $this->_sql();
        return $res;
    }

    /**
     * @return array����û�״̬�ֶ�
     */
    public function get_displace_status_remark(){
        return $this->_displace_status_remark;
    }

    protected $_displace_status_remark = array(
        0 => "���û�",
        1 => "�����û�",
        2 => "ȫ���û�",
    );

    /**
     * �жϺ�ͬ���Ƿ����
     * @param $contractNo
     * @param $scaleType
     * @param $cityId
     * @return bool
     */
    public function isExistContract($contractNo, $scaleType, $cityId) {
        $contractNo = trim($contractNo);
        if (empty($contractNo)) {
            return false;
        }

        $statusFieldName = '';
        $statusFieldNameList = D('Project')->getStatusFieldNameList();
        if (notEmptyArray($statusFieldNameList)) {
            $statusFieldName = $statusFieldNameList[$scaleType];
        }

        if (empty($statusFieldName)) {
            return false;
        }

        $contractProjectSql = <<<CONTRACT_PROJECT_SQL
            SELECT
                p.status,
                p.acstatus,
                p.pstatus
            FROM erp_project p
CONTRACT_PROJECT_SQL;
        $where = " WHERE p.contract = '{$contractNo}' AND p.city_id = {$cityId} ";
        $sql = $contractProjectSql . $where;
        $project = $this->query($sql);
        if (notEmptyArray($project)) {
            foreach ($project as $item) {
                if (empty($item[$statusFieldName])) {
                    continue;
                }
                
                $pStatus = intval($item['PSTATUS']);
                $status = intval($item['STATUS']);
                // ���ʱ��ֹ������ɾ��״̬�������ѭ��
                if ($pStatus == self::PROJECT_TERMINATED_PSTATUS || $status == self::PROJECT_DELETED_STATUS) {
                    continue;
                } else {
                    // ����ֱ�ӷ���Ϊ���ҵ���ͬ��ͬ״̬
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }

    }

    /**
     * ͬ���ؿ���Ϣ
     * @param $contractnum
     * @param $contract_id
     * @param string $citypy
     * @return bool
     */
    public function syncRefundData($contractnum, $contract_id, $citypy = "nj") {
        load("@.contract_common");
        $refundRecords = get_backmoney_data_by_no($citypy, $contractnum);
        if (empty($refundRecords) || (is_array($refundRecords) && count($refundRecords) == 0)) {
            return true;
        }
        //����ͬ�ؿ��¼���뵽����ϵͳ�����ݿ���
        if (!empty($refundRecords)) {
            $contract_model = D("Contract");
            $payment_model = D("PaymentRecord");

            $conf_where = "ID = '" . $contract_id . "'";
            $field_arr = array("CASE_ID", "ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            // ��ȡ��Ŀ������
            if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                $scaleType = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
            }

            foreach ($refundRecords as $key => $val) {
                $taxrate = get_taxrate_by_citypy($citypy);
                $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);

                $refund_data["MONEY"] = $val["money"];
                $refund_data["CREATETIME"] = $val["date"];
                $refund_data["REMARK"] = $val["note"];
                $refund_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                $refund_data["CONTRACT_ID"] = $contract_id;
                $insert_reund_id = $payment_model->add_refund_records($refund_data);
                if (!$insert_reund_id) {
                    return false;
                } else {
                    //����������ϸ��¼
                    if ($scaleType == self::YG_SCALETYPE) {
                        $income_info['INCOME_FROM'] = 11;
                    } else if ($scaleType == self::FWFSC_SCALETYPE) {
                        $income_info['INCOME_FROM'] = 22;
                    } else if ($scaleType == self::HD_SCALETYPE) {
                        $income_info['INCOME_FROM'] = 15;
                    }

                    $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                    $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['INCOME'] = $val["money"];
                    $income_info['OUTPUT_TAX'] = $tax;
                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                    $income_info['OCCUR_TIME'] = $val["date"];
                    $income_info['PAY_ID'] = $insert_reund_id;
                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                    $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['ORG_PAY_ID'] = $insert_reund_id;

                    $ProjectIncome_model = D("ProjectIncome");
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if (!$res) {
                        return false;
                    }
                }
            }

        }
        return $insert_reund_id ? $insert_reund_id : false;
    }

    /**
     * ͬ����Ʊ��Ϣ
     * @param $contractnum
     * @param $contract_id
     * @param string $citypy
     * @return bool
     */
    public function syncInvoiceData($contractnum, $contract_id, $citypy = "nj") {
        load("@.contract_common");
        $invoiceRecords = get_invoice_data_by_no($citypy, $contractnum);
        if (empty($invoiceRecords) || (is_array($invoiceRecords) && count($invoiceRecords) == 0)) {
            return true;
        }
        //����ͬ��Ʊ��¼���뵽����ϵͳ�����ݿ���
        if (!empty($invoiceRecords)) {
            $billing_model = D("BillingRecord");
            $billing_status = $billing_model->get_invoice_status();

            $contract_model = D("Contract");
            $conf_where = "ID = '$contract_id'";
            $field_arr = array("CASE_ID", "ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                $scaleType = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
            }

            foreach ($invoiceRecords as $key => $val) {
                $taxrate = get_taxrate_by_citypy($citypy);
                $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);

                $invoice_data["INVOICE_MONEY"] = $val["money"];
                $invoice_data["TAX"] = $tax;
                $invoice_data["INVOICE_NO"] = $val["invono"];
                $invoice_data["REMARK"] = $val["note"];
                $invoice_data["INVOICE_TIME"] = $val["date"];
                $invoice_data["STATUS"] = $billing_status["have_invoiced"];
                $invoice_data["CONTRACT_ID"] = $contract_id;
                $invoice_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                $invoice_data["CREATETIME"] = $val["date"];
                $invoice_data["INVOICE_TYPE"] = 1;
                if ($val['type']) {
                    // ��Ʊ���ͣ������Ʊ���Ͳ�Ϊ1��2���򽫷�Ʊ��������Ϊ�����
                    // ��������Ϊ1�����ѣ���2������ѣ�
                    if (!in_array($val['type'], array(1, 2))) {
                        $val['type'] = 2;
                    }
                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                }
                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);
                if (!$insert_invoice_id) {
                    return false;
                } else {
                    //����������ϸ��¼
                    if ($scaleType == self::YG_SCALETYPE) {
                        $income_info['INCOME_FROM'] = 12;
                    } else if ($scaleType == self::FWFSC_SCALETYPE) {
                        $income_info['INCOME_FROM'] = 23;
                    } else if ($scaleType == self::HD_SCALETYPE) {
                        $income_info['INCOME_FROM'] = 16;
                    }
                    $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                    $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['INCOME'] = $val["money"];
                    $income_info['OUTPUT_TAX'] = $tax;
                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                    $income_info['OCCUR_TIME'] = $val["date"];
                    $income_info['PAY_ID'] = $insert_invoice_id;
                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                    $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['ORG_PAY_ID'] = $insert_invoice_id;

                    $ProjectIncome_model = D("ProjectIncome");
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if (!$res) {
                        return false;
                    }
                }
            }
        }
        return $insert_invoice_id ? $insert_invoice_id : false;
    }
}

/* End of file ContractModel.class.php */
/* Location: ./Lib/Model/ContractModel.class.php */