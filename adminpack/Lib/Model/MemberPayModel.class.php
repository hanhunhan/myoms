<?php

/**
 * ��Ա����MODEL��
 *
 * @author liuhu
 */
class MemberPayModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'MEMBER_PAYMENT';
    
    /*���ʽ*/
    private  $_conf_pay_type = array(
                                1 => 'POS��', 
                                2 => '����', 
                                3 => '�ֽ�',
                                4 => '�ۺ�'
                                );
    
    /***������ϸ����״̬ƴ��***/
    private  $_conf_status = array(
                        'wait_confirm' => '0',		//�ȴ�ȷ��
                        'confirmed' => '1', 		//��ȷ��
                        'confirm_failure' => '2',	//ȷ��ʧ��
                        'deleted' => '4'            //ɾ��
    					);
    
    /***������ϸ״̬***/
    private  $_conf_status_remark = array(
                        '0' => 'δȷ��',		//�ȴ�ȷ��
                        '1' => '��ȷ��', 	//��ȷ��
                        '2' => 'ȷ��ʧ��'	//ȷ��ʧ��
    					);
    
    /***������ϸ�˿�״̬***/
    private  $_conf_refund_status = array(
                                        'no_refund' => 0,		//δ�����˿�
                                        'apply_refund' => 1,	//�����˿���
                                        );
    
    /***������ϸ�˿�״̬***/
    private  $_conf_refund_status_remark = array(
                                                0 => 'δ����',
                                                1 => '������',
                                                );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ��ȡ���ʽ����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_pay_type()
    {
    	return $this->_conf_pay_type;
    }
    
    /**
     * ��ȡ������ϸ״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_status()
    {
    	return $this->_conf_status;
    }
    
    /**
     * ��ȡ������ϸ״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_status_remark()
    {
    	return $this->_conf_refund_status_remark;
    }
    
    /**
     * ��ȡ������ϸ�˿�״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_status()
    {
    	return $this->_conf_refund_status;
    }
    
    /**
     * ��ȡ������ϸ�˿�״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_status_remark()
    {
    	return $this->_conf_status_remark;
    }
    
    /**
     * ���֧����ϸ��Ϣ
     * @param array $pay_info ֧����ϸ����
     * @return mixed ��ӳɹ����ز������ݱ�ţ�����ʧ�ܷ���FALSE
     */
    public function add_member_info($pay_info) 
    {   
        if(is_array($pay_info) && !empty($pay_info))
        {   
            // �����������ز���ID
            $insertId = $this->add($pay_info);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݸ�����ϸ���ɾ��������ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_pay_detail_by_id($id)
    {   
        $up_num = 0;
        $id = intval($id);
        
        if($id > 0)
        {
            $cond_where = "ID = '".$id."'";
            $up_num = self::del_pay_detail_by_cond($cond_where);
        }
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݻ�Ա���ɾ��������ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_pay_detail_by_mid($mid)
    {   
        $up_num = 0;
        $mid = intval($mid);
        
        if($mid > 0)
        {   
            $cond_where = "MID = '".$mid."' AND STATUS != '".$this->_conf_status['confirmed']."'";
            $up_num = self::del_pay_detail_by_cond($cond_where);
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
    public function del_pay_detail_by_cond($cond_where)
    {   
        $up_num = 0;
        
    	if($cond_where != '')
    	{   
            $update_arr['STATUS'] = $this->_conf_status['deleted'];
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݱ�Ÿ��¸�����ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_info_by_id($ids, $update_arr)
    {
    	$cond_where = "";
    
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
    
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
  	
    /**
     * ������������ĳ��������ϸ��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_info_by_cond($update_arr, $cond_where)
    {
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    		//echo $this->getLastSql();
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݸ����ţ���ȡ������ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $pay_id ������ϸ��š�������ߵ���������ϸ��š�
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_payinfo_by_id($pay_id, $search_field = array())
    {
        $info = array();
        $cond_where = "";
        if(is_array($pay_id) && !empty($pay_id))
        {
            $pay_id_str = implode(',', $pay_id);
            $cond_where = "ID IN (".$pay_id_str.")";
        }
        else 
        {   
            $pay_id = intval($pay_id);
            $cond_where = "ID = '".$pay_id."'";
        }
        
        if($cond_where == '')
        {
            return $info;
        }
        
        $info = self::get_payinfo_by_cond($cond_where, $search_field);
        
        return $info;
    }
    
    
    /**
     * ���ݻ�Ա��ţ���ȡ������ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $mid ��Ա���[������ߵ�����Ա���]
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_payinfo_by_mid($mid, $search_field = array())
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "MID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "MID = '".$mid."'";
        }
        
        if($cond_where == '')
        {
            return $info;
        }
        
        $info = self::get_payinfo_by_cond($cond_where, $search_field);
        
        return $info;
    }
    
    
    /**
     * ����������ȡ������ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_payinfo_by_cond($cond_where, $search_field = array())
    {
        $info = array();
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        //echo $this->getLastSql();
        return $info;
    }
    
    
    /**
     * ���ݻ�Ա��Ż�ȡ�����ܺ�
     *
     * @access	public
     * @param	int  $mid ��Ա���
     * @param 	string $conf_status ����״̬�ַ�����wait_confirm��
     * @return	array ��ѯ���
     */
    public function get_sum_pay($mid, $status = '')
    {   
    	$trade_money = 0;
    	$mid = intval($mid);
        
    	if($mid <= 0)
    	{
            return $trade_money;
    	}
    	
    	//��ѯ����
    	$cond_where = "MID = '".$mid."'";
    	
    	$conf_status = $this->get_conf_status();
    	if(!empty($status) && !empty($conf_status) &&
    			 array_key_exists($status, $conf_status) )
    	{
            $cond_where .= " AND STATUS = '".$conf_status[$status]."'";
    	}
        else
        {
            $cond_where .= " AND STATUS IN (".$conf_status['wait_confirm'].",".$conf_status['confirmed'].")";
        }
        
    	$trade_money = $this->where($cond_where)->sum('TRADE_MONEY - REFUND_MONEY');
        
    	return floatval($trade_money);
    }
}

/* End of file MemberPayModel.class.php */
/* Location: ./Lib/Model/MemberPayModel.class.php */