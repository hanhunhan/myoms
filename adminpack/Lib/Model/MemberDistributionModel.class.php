<?php

/**
 * ������Ա������
 *
 * @author liuhu
 */
class MemberDistributionModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'MEMBER_DISTRIBUTION';
    
    
    //���캯��
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * ��ӷ�����Ա��Ϣ
     * @param array $member_info ��Ա��Ϣ����
     * @return mixed ��ӳɹ����ز������ݱ�ţ�����ʧ�ܷ���FALSE
     */
    public function add_member_info($member_info)
    {
    	if(is_array($member_info) && !empty($member_info))
    	{
    		// �����������ز���ID
    		$insertId = $this->add($member_info);
    	}
    
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݱ��ɾ��������Ա��Ϣ
     *
     * @access	protected
     * @param	mixed $ids ���̻�Ա���
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
     * ���ݱ��ɾ��������Ա��Ϣ
     *
     * @access	protected
     * @param	mixed $ids Ҫɾ���ķ�����Ա���
     * @return	int ɾ��������0ɾ��ʧ��
     */
    public function delete_info_by_id($ids)
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
    	
    	$delte_num = self::delete_info_by_cond($cond_where);
    	
    	return $delte_num > 0  ? $delte_num : FALSE;
    }

    
    /**
     * ɾ��������Ա��Ϣ
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
     * ���ݵ��̻�Ա��Ÿ��·�����Ա��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_info_by_mid($ids, $update_arr)
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
    
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݷ�����Ա��Ÿ��·�����Ա��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_info_by_id($ids, $update_arr)
    {
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
     * ����ĳ��������Ա��Ϣ
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
            //echo $this->getLastSql();
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݻ�Ա��Ż�ȡ������Ա��Ϣ����һ�û���
     *
     * @access	public
     * @param  int $id ����ID
     * @param array $search_field �����ֶ�
     * @return	array ������Ա��Ϣ
     */
    public function get_info_by_id($id, $search_field = array())
    {   
        $info = array();
        
        $id = intval($id);
        if($id <= 0)
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where("ID = $id")->find();
        }
        else
        {
            $info = $this->where("ID = $id")->find();
        }
        //echo $this->_sql();
        return $info;
    }
    
    /**
     * ���ݻ�Ա��Ż�ȡ������Ա��Ա��Ϣ�����û���
     *
     * @access	public
     * @param  array $ids ����ID
     * @param array $search_field �����ֶ�
     * @return	array ������Ա��Ա��Ϣ
     */
    public function get_info_by_ids($ids, $search_field = array())
    {   
        $info = array();
        
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",",$ids);
            $conf_where = "ID IN ($id_str)";
        }
        else
        {
            $conf_where = "1 = 0";
        } 
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where($conf_where)->select();
        }
        else
        {
            $info = $this->where($conf_where)->select();
        }
        //echo $this->_sql();
        return $info;
    } 
    
   /**
     * ����������ȡ������Ա��Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
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
        //echo $this->getLastSql();
        return $info;
    }
}

/* End of file MemberDistributionModel.class.php */
/* Location: ./Lib/Model/MemberDistributionModel.class.php */