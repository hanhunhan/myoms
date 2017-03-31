<?php

/**
 * ������ϸ������
 *
 * @author liuhu
 */
class ReimbursementDetailModel extends Model {

    /**
     * ��λ��Ԫ
     */
    const UNIT_RMB_YUAN = 'Ԫ';

    /**
     * ��λ��%
     */
    const UNIT_PERCENT = '%';

    /***������ϸ��***/
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'REIMBURSEMENT_DETAIL';

    /***������ϸ״̬***/
    private $_conf_reim_details_status = array(
        'reim_detail_no_sub' => 0,    //δ�ύ
        'reim_detail_completed' => 1,    //�ѱ���
        'reim_detail_deleted' => 4,   //ɾ��������ϸ
        'reim_detail_rejected' => 3,    //�Ѳ���
    );

    /***��ϸ�˿�״̬***/
    private $_conf_reim_details_remark = array(
        0 => 'δ����',
        1 => '�ѱ���',
        4 => 'ɾ��',
        3 => '�Ѳ���',
    );
    
    /**���캯��**/
    public function __construct()
    {
    	parent::__construct();
    }
    
    /**
     * ��ȡ������ϸ״̬
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_detail_status()
    {
        return $this->_conf_reim_details_status;
    }
    
    
    /**
     * ��ȡ����������ϸ״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_detail_status_remark()
    {
        return $this->_conf_reim_details_remark;
    }
    
    
    /**
     * ��ӱ�����ϸ
     *
     * @access	public
     * @param	array  $reim_details_arr �˿���Ϣ
     * @return	mixed  �ɹ������˿��ţ�ʧ�ܷ���FALSE
     */
    public function add_reim_details($reim_details_arr)
    {
    	$insertId = 0;
    
    	if(is_array($reim_details_arr) && !empty($reim_details_arr))
    	{
    		// �����������ز���ID
    		$insertId = $this->add($reim_details_arr);
            //echo $this->getLastSql();
    	}
    
    	return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * �������뵥IDɾ��������ϸ��Ϣ
     *
     * @access	public
     * @param	int  $listid ���뵥���
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_reim_detail_by_listid($listid)
    {
        $up_num = 0;
        $listid = intval($listid);
        
    	if($listid > 0)
    	{	
            $cond_where = "LIST_ID = '".$listid."'";
            
    		$up_num = self::del_reim_detail_by_cond($cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �������뵥IDɾ��������ϸ��Ϣ
     *
     * @access	public
     * @param	int  $id ������ϸID
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_reim_detail_by_id($id)
    {
        $up_num = 0;
        $id = intval($id);
        
    	if($id > 0)
    	{	
            $cond_where = "ID = '".$id."'";
            
    		$up_num = self::del_reim_detail_by_cond($cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ��������ɾ��������ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_reim_detail_by_cond($cond_where)
    {   
        $up_num = 0;
        
    	if($cond_where != '')
    	{
            $update_arr = array();
            $update_arr['STATUS'] = intval($this->_conf_reim_details_status['reim_detail_deleted']);
            
    		$up_num = self::update_reim_detail_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �������ͨ���������뵥
     *
     * @access	public
     * @param	$int  $list_id ���������
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_reim_detail_to_completed($list_id)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//���ύ״̬
    		$no_sub_status  = $this->_conf_reim_details_status['reim_detail_no_sub'];
    		$cond_where .= " AND STATUS = '".$no_sub_status."'";
            
    		$update_arr['STATUS'] = $this->_conf_reim_details_status['reim_detail_completed'];
            
    		$up_num = self::update_reim_detail_by_cond($update_arr, $cond_where);
    	}
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ID���±�����ϸ��Ϣ
     *
     * @access	public
     * @param	int  $id ������ϸID
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function update_reim_detail_by_id($id, $update_arr = array())
    {
        $up_num = 0;
        $id = intval($id);
        
    	if($id > 0)
    	{	
            $cond_where = "ID = '".$id."'";
            
    		$up_num = self::update_reim_detail_by_cond($update_arr,$cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    /**
     * ����ָ���������±���������ϸ��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_reim_detail_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
        
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    	//echo $this->getLastSql();
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݱ������뵥��Ż�ȡ�����ܽ��
     *
     * @access	public
     * @param	int  $list_id  �������뵥���
     * @return	float �����ܽ��
     */
    public function get_sum_total_money_by_listid($list_id)
    {	
    	$amount = 0;
    	
    	$list_id = intval($list_id);
    	
    	if($list_id > 0)
    	{	
    		$status_deleted = $this->_conf_reim_details_status['reim_detail_deleted'];
    		$cond_where = "LIST_ID = '".$list_id."' AND STATUS != '".$status_deleted."' ";
            //echo $cond_where;
    		$amount = $this->where($cond_where)->sum('MONEY');
            //echo $this->getLastSql();
    	}
    	
    	return floatval($amount);
    }
    
    /**
     * ����LIST_ID��ȡ������ϸ
     * @param int $list_id ������id
     * @param array() $search_arr Ҫ��ѯ�ֶεļ�ֵ��
     * return array() $info ������ϸ����
     */
    public function get_detail_info_by_listid($list_id, $search_arr)
    {
        $list_id = intval($list_id);
        $info = array();
        if( $list_id >0 )
        {
            $conf_where = "LIST_ID = ".$list_id;
            if(is_array($search_arr) && !empty($search_arr))
            {
                $info = $this->where($conf_where)->field($search_arr)->select();
            }
            
            return $info;
            
        }
    }
    
    /**
     * ����LIST_ID��ȡ������ϸ
     * @param mixed $ids ��ϸid
     * @param array() $search_arr Ҫ��ѯ�ֶεļ�ֵ��
     * return array() $info ������ϸ����
     */
    public function get_detail_info_by_id($ids,$search_arr = array())
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
        if(is_array($search_arr) && !empty($search_arr))
        {
            $info = $this->where($cond_where)->field($search_arr)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        return $info ? $info : false;
    }
    
    /**
     * ����������ȡ������ϸ
     * @param string $cond_where ��ѯ����
     * @param array() $search_arr Ҫ��ѯ�ֶεļ�ֵ��
     * return array() $info ������ϸ����
     */
    public function get_detail_info_by_cond($cond_where,$search_arr = array())
    {
        $info = array();
        if(is_array($search_arr) && !empty($search_arr))
        {
            $info = $this->where($cond_where)->field($search_arr)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        
        return $info ? $info : false;
    }
    
    /**
     * �Ƿ��Ѿ�����������
     *
     * @access	public
     * @param	int  $case_id  �������
     * @param	int  $business_id  ����ҵ����
     * @param	int  $type  ��������
     * @return	boolean TRUE ���� FALSE������
     */
    public function is_exisit_reim_detail($case_id, $business_id, $type)
    {
        $num = 0;
    	
    	$case_id = intval($case_id);
        $business_id = intval($business_id);
        $type = intval($type);
    	
    	if($business_id > 0 && $type > 0)
    	{	
    		$status_deleted = $this->_conf_reim_details_status['reim_detail_deleted'];
    		$cond_where = "CASE_ID = '".$case_id."' AND BUSINESS_ID = '".$business_id."' "
                        . "AND TYPE = '".$type."' AND  STATUS != '".$status_deleted."' ";
    		$num = $this->where($cond_where)->count();
    	}
    	
    	return $num > 0 ? TRUE : FALSE;
    }

    /**
     * ��ȡFeeScale
     * @param $listID
     */
    public function getFeeScalesByListID($listID) {
        $caseID = D('ReimbursementDetail')->where("LIST_ID  = {$listID}")->getField('CASE_ID');
        $feeScale = D('Project')->get_feescale_by_cid($caseID);

        if (is_array($feeScale) && count($feeScale)) {
            foreach ($feeScale as $key => $value) {
                $unit = $value['STYPE'] == 0 ? self::UNIT_RMB_YUAN : self::UNIT_PERCENT;
                $arrFee[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'] . $unit;
            }
        }

        return  $arrFee;
    }

    /**
     * ɾ��������ϸ
     * @param array $data
     */
    public function handleDelReimDetail($data = array()) {
        $response = false;
        if (notEmptyArray($data)) {
            if ($data['TYPE'] == 17) {
                $response = D('erp_commission_reim_detail')->where("REIM_DETAIL_ID = {$data['ID']}")->delete();
            } else {
                $updateStatusData = array();
                switch ($data['TYPE']) {
                    case 22:
                        $updateStatusData['AGENCY_DEAL_REWARD_STATUS'] = 1;
                        break;
                    case 23:
                        $updateStatusData['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                        break;
                    default:
                        $updateStatusData['OUT_REWARD_STATUS'] = 1;
                }
                $response = D('Member')->where("ID = {$data['BUSINESS_ID']}")->save($updateStatusData);
            }
        }
        return $response;
    }
}

/* End of file ReimbursementDetailModel.class.php */
/* Location: ./Lib/Model/ReimbursementDetailModel.class.php */