<?php

/**
 * �ɹ���ͬ����
 *
 * @author liuhu
 */
class PurchaseContractModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CONTRACT';
    
    /***��ͬǩԼ״̬����***/
    private $_conf_sign_remark = array(
                                '0' => 'δǩԼ',
                                '-1' => '��ǩԼ'
                            );
    
    /***��ͬǩԼ״̬***/
    private $_conf_sign = array(
                                'not_sign' => 0,
                                'sign' => -1
                            );
    
    /***��ͬ����״̬����***/
    private $_conf_reim_status_remark = array(
                                '0' => 'δ����',
                                '1' => '������',
                                '2' => '�ѱ���'
                            );
    
    /***��ͬ����״̬***/
    private $_conf_reim_status = array(
                                'not_apply' => 0,
                                'applied' => 1,
                                'reimbursed' => 2,
                            );
    
    //���캯��
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * ��ȡ��ͬǩԼ״̬��������
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_sign_remark()
    {
        return $this->_conf_sign_remark;
    }
    
    
    /**
     * ��ȡ��ͬǩԼ״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_sign()
    {
        return $this->_conf_sign;
    }
    
    
    /**
     * ��ȡ��ͬ����״̬��������
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_status_remark()
    {
        return $this->_conf_reim_status_remark;
    }
    
    
    /**
     * ��ȡ��ͬ����״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_status()
    {
        return $this->_conf_reim_status;
    }
    
    
    /**
     * ��Ӻ�ͬ��Ϣ
     * @param array $contract_info ��ͬ��Ϣ
     * @return mixed ��ӳɹ����ز������ݱ�ţ�����ʧ�ܷ���FALSE
     */
    public function add_contract_info($contract_info)
    {
        $insertId = FALSE;
        $info = array();
        
    	if(is_array($contract_info) && !empty($contract_info))
    	{   
    		$insertId = $this->add($info);
        }
        
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
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
     * �����������ͬ��Ϣ
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
     * ���ݺ�ͬ�������ĺ�ͬ����״̬Ϊ����������
     *
     * @access	public
     * @param	mixed  $reim_list_ids ���������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_contract_to_reim_not_apply_by_reim_listid($reim_list_ids)
    {   
        $cond_where = "";
    
    	if(is_array($reim_list_ids) && !empty($reim_list_ids))
    	{
    		$ids_str = implode(',', $reim_list_ids);
    		$cond_where = " REIM_LIST_ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$reim_list_id  = intval($reim_list_ids);
    		$cond_where = " REIM_LIST_ID = '".$reim_list_id."'";
    	}
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['not_apply'];
        $update_arr['REIM_LIST_ID'] = 0;
        
        $update_num = self::update_info_by_cond($cond_where, $update_arr);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * ���ݺ�ͬ�������ĺ�ͬ����״̬Ϊ����������
     *
     * @access	public
     * @param	mixed  $contract_ids ��ͬ���
     * @param	mixed  $reim_list_id ���������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_contract_to_reim_applied_by_id($contract_ids, $reim_list_id)
    {   
        $update_num = 0;
        
        if(!empty($contract_ids) && $reim_list_id > 0 )
        {
            $update_arr['REIM_STATUS'] = $this->_conf_reim_status['applied'];
            $update_arr['REIM_LIST_ID'] = intval($reim_list_id);
            
            $update_num = self::update_info_by_id($contract_ids, $update_arr);
        }
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * ���ݱ����ĵ���Ÿ��ĺ�ͬ����״̬Ϊ�ѱ���
     *
     * @access	public
     * @param	mixed  $reim_list_ids ���������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_contract_to_reimbursed_by_reim_listid($reim_list_ids)
    {   
        $cond_where = "";
        
    	if(is_array($reim_list_ids) && !empty($reim_list_ids))
    	{
    		$ids_str = implode(',', $reim_list_ids);
    		$cond_where = " REIM_LIST_ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$reim_list_id  = intval($reim_list_ids);
    		$cond_where = " REIM_LIST_ID = '".$reim_list_id."'";
    	}
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['reimbursed'];
        
        $update_num = self::update_info_by_cond($cond_where, $update_arr);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * ���ݲɹ���ϸ��Ÿ��ĺ�ͬ����״̬Ϊ�ѱ���
     *
     * @access	public
     * @param	mixed  $list_ids �ɹ���ϸ���
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_contract_to_reimbursed_by_listid($list_ids)
    {   
        $cond_where = "";
        
    	if(is_array($list_ids) && !empty($list_ids))
    	{
    		$ids_str = implode(',', $list_ids);
    		$cond_where = " ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$list_id  = intval($list_ids);
    		$cond_where = " ID = '".$list_id."'";
    	}
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['reimbursed'];
        
        $update_num = self::update_info_by_cond($cond_where, $update_arr);
        
        return $update_num > 0 ? $update_num : FALSE;
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
            $cond_where = "ID IN ($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        
        $res = $this->table($table)->where($cond_where)->save($update_arr);
        
        return $res;
    }
    
    
    /**
     * �����������º�ͬ��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_info_by_cond($cond_where, $update_arr)
    {
    	$up_num = 0;
        
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
            //echo $this->getLastSql();
    	}
        
    	return $up_num > 0  ? $up_num : FALSE;
    }

    /**
     * �жϺ�ͬ�Ƿ�Ϊ������ú�ͬ
     * @param $contractID
     * @return bool
     */
    public function isFromStockContract($contractID){
        // num ��������
        // use_num ��������
        if (empty($contractID)) {
            throw_exception("��ѯPURCHASE_LIST��, CONTRACT_ID����Ϊ��");
        }

        //��ȡ��Ӧ������
        $sql = "SELECT A.ID FROM ERP_CONTRACT A LEFT  JOIN  ERP_SUPPLIER B ON  A.SUPPLIER_ID = B.ID ";
        $sql .= " WHERE B.TYPE = 1 AND A.ID = " . $contractID;

        $result = $this->query($sql);
        if (is_array($result) && count($result)) {
             return true;
        }

        return false;
    }
}

/* End of file PurchaseContract.class.php */
/* Location: ./Lib/Model/PurchaseContract.class.php */
