<?php

/**
 * 合同管理
 *
 * @author liuhu
 */
class ContractModel extends Model{

    const PROJECT_DELETED_STATUS = 2;
    const PROJECT_TERMINATED_PSTATUS = 5;

    /**
     * 非我方收筹的SCALETYPE
     */
    const FWFSC_SCALETYPE = 8;

    /**
     * 硬广的SCALETYPE
     */
    const YG_SCALETYPE = 3;

    /**
     * 活动的SCALETYPE
     */
    const HD_SCALETYPE = 4;
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'INCOME_CONTRACT';

    //构造函数
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * 添加合同信息
     * @param array $contract_info 合同信息
     * info['CASE_ID']          案例编号【必填】
     * $info['CONTRACT_NO']     合同号【必填】
        $info['COMPANY']        合同签约公司【必填】
        $info['START_TIME']     开始时间【必填】
        $info['END_TIME']       结束时间【必填】
        $info['PUB_TIME']       发布日期【选填】
        $info['CONF_USER']      合同确认人【选填】
        $info['CONF_TIME']      合同确认时间【选填】
        $info['STATUS']         合同状态【必填】
        $info['MONEY']          合同金额【必填】
        $info['CONTRACT_TYPE']  合同类型【必填】
        $info['IS_NEED_INVOICE'] 是否需要开票【选填】 默认为0
        $info['SIGN_USER']      签约人【必须】
        $info['ADD_TIME']       添加时间【选填】
        $info['CITY_PY']        城市拼音【必填】
     * @return mixed 添加成功返回插入数据编号，插入失败返回FALSE
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
            
    		// 自增主键返回插入ID
    		$insertId = $this->add($info);
    	}
        //echo $this->getLastSql();
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据业务ID获取合同编号
     *
     * @access	public
     * @param	int  $case_id 业务ID
     * @param array $search_field 搜索字段
     * @return	array 合同信息
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
     * 根据ID获取合同编号
     *
     * @access	public
     * @param	mixed  $ids 单条或者多条合同ID
     * @param array $search_field 搜索字段
     * @return	array 合同信息
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
     * 根据条件获取项目合同信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 合同信息
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
     * 根据ID更新收益合同信息
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
     * 根据条件更新收益合同信息
     *
     * @access	public
     * @param	string  $cond_where 要更新的记录
     * @param array $update_arr 要跟新的字段的键值对
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
     * @return array获得置换状态字段
     */
    public function get_displace_status_remark(){
        return $this->_displace_status_remark;
    }

    protected $_displace_status_remark = array(
        0 => "非置换",
        1 => "部分置换",
        2 => "全部置换",
    );

    /**
     * 判断合同号是否存在
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
                // 如果时终止或者已删除状态，则继续循环
                if ($pStatus == self::PROJECT_TERMINATED_PSTATUS || $status == self::PROJECT_DELETED_STATUS) {
                    continue;
                } else {
                    // 否则直接返回为已找到相同合同状态
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }

    }

    /**
     * 同步回款信息
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
        //将合同回款记录插入到经管系统的数据库中
        if (!empty($refundRecords)) {
            $contract_model = D("Contract");
            $payment_model = D("PaymentRecord");

            $conf_where = "ID = '" . $contract_id . "'";
            $field_arr = array("CASE_ID", "ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            // 获取项目的类型
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
                    //新增收益明细记录
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
     * 同步开票信息
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
        //将合同开票记录插入到经管系统的数据库中
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
                    // 发票类型，如果发票类型不为1或2，则将发票类型设置为服务费
                    // 否则设置为1（广告费）或2（服务费）
                    if (!in_array($val['type'], array(1, 2))) {
                        $val['type'] = 2;
                    }
                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                }
                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);
                if (!$insert_invoice_id) {
                    return false;
                } else {
                    //新增收益明细记录
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