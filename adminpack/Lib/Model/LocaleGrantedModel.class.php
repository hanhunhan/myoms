<?php

/**
 * �ֳ�����MODEL
 *
 * @author liuhu
 */
class LocaleGrantedModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'LOCALE_GRANTED';
    
    /***���ż�¼����״̬����***/
    private $_conf_reim_status_remark = array(
                                '0' => 'δ����',
                                '1' => '������',
                                '2' => '�ѱ���'
                            );
    
    /***���ż�¼����״̬***/
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
     * ����ֳ�������Ϣ
     * @param array $grant_info �ֳ�������Ϣ
     * @return mixed ��ӳɹ����ز������ݱ�ţ�����ʧ�ܷ���FALSE
     */
    public function add_grant_info($grant_info)
    {   
        $insertId = FALSE;
        
    	if(is_array($grant_info) && !empty($grant_info))
    	{   
    		// �����������ز���ID
    		$insertId = $this->add($grant_info);
    	}
        //echo $this->getLastSql();
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݱ��ɾ���ֳ�������Ϣ
     *
     * @access	protected
     * @param	mixed $ids �ֳ�������Ϣ���
     * @return	int ɾ��������0ɾ��ʧ��
     */
    public function delete_info_by_mid($ids)
    {
    	$cond_where = "";
    	 
    	if(is_array($ids) && !empty($ids))
    	{
    		$ids_str = implode(',', $ids);
    		$cond_where = " MID IN (".$ids_str.")";
    	}
    	else
    	{
    		$id  = intval($ids);
    		$cond_where = " MID = '".$id."'";
    	}
    	 
    	$delte_num = self::delete_info_by_cond($cond_where);
    	 
    	return $delte_num > 0  ? $delte_num : FALSE;
    }
    
    
    /**
     * ɾ��������Ϣ
     *
     * @access	public
     * @param	string  $cond_where ɾ������
     * @return	mixed ɾ���ɹ����ظ���������ɾ������FALSE
     */
    public function delete_info_by_cond($cond_where)
    {	
    	$del_num = 0;
    	if($cond_where != '')
    	{
    		$del_num = $this->where($cond_where)->delete();
    		//echo $this->getLastSql();
    	}
    
    	return $del_num > 0  ? $del_num : FALSE;
    }
    
    
    /**
     * ���ݱ�Ÿ��ķ��ż�¼����״̬Ϊ����������
     *
     * @access	public
     * @param	mixed  $$reim_listids ���������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_granted_to_reim_not_apply_by_reim_listid($reim_listids)
    {   
        $cond_where = "";
    
    	if(is_array($reim_listids) && !empty($reim_listids))
    	{
    		$ids_str = implode(',', $reim_listids);
    		$cond_where = " REIM_LIST_ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$reim_list_id  = intval($reim_listids);
    		$cond_where = " REIM_LIST_ID = '".$reim_list_id."'";
    	}
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['not_apply'];
        $update_arr['REIM_LIST_ID'] = 0;
        
        $update_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * ���ݷ��ż�¼��Ÿ��ķ��ż�¼����״̬Ϊ����������
     *
     * @access	public
     * @param	mixed  $ids ���ż�¼���
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_granted_to_reim_not_apply_by_id($ids)
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
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['not_apply'];
        $update_arr['REIM_LIST_ID'] = 0;
        
        $update_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * ���ݱ�Ÿ��ķ��ż�¼����״̬Ϊ����������
     *
     * @access	public
     * @param	mixed  $ids ���ż�¼���
     * @param	mixed  $reim_list_id ���������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_granted_to_reim_applied_by_id($ids, $reim_list_id)
    {   
        $update_num = 0;
        
        if(!empty($ids) && $reim_list_id > 0 )
        {
            $update_arr['REIM_STATUS'] = $this->_conf_reim_status['applied'];
            $update_arr['REIM_LIST_ID'] = intval($reim_list_id);

            $update_num = self::update_info_by_id($ids, $update_arr);
        }
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * ���ݱ�������Ÿ��ķ��ż�¼����״̬Ϊ�ѱ���
     *
     * @access	public
     * @param	mixed  $reim_list_ids ���������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_granted_to_reimbursed_by_id($reim_list_ids)
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
        
        $update_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * ���·�����Ϣ
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
     * ���·�����Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_info_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����������ȡ��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ���ż�¼ID
     * @param array $search_field �����ֶ�
     * @return	array 
     */
    public function get_info_by_id($ids, $search_field = array())
    {
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
        
        return $info;
    }
    
    
    /**
     * ����������ȡ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array 
     */
    public function get_info_by_cond($cond_where, $search_field = array())
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
        
        return $info;
    }
}

/* End of file LocaleGrantedModel.class.php */
/* Location: ./Lib/Model/LocaleGrantedModel.class.php */