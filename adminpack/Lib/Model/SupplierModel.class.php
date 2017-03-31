<?php
/**
 * ��Ӧ�̹�����
 *
 * @author 
 */

class SupplierModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'SUPPLIER';
    
    /***��Ӧ��״̬��������***/
    private $_conf_status_remark = array(
                                0 => '��Ч',
                                1 => '��Ч'
                            );
    
    /***��Ӧ��״̬״̬***/
    private $_conf_status = array( 
                                'invalid' => 0,
                                'valid' => 1
                            );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ��ȡ��Ӧ��״̬��������
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
     * ��ȡ��Ӧ��״̬
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
     * ��ӹ�Ӧ����Ϣ
     *
     * @access	public
     * @param	array  $supplier_info ��Ӧ����Ϣ
     * @return	mixed  �ɹ���������ID��ʧ�ܷ���FALSE
     */
    public function add_supplier_info ($supplier_info)
    {
        if(is_array($supplier_info) && !empty($supplier_info))
        {   
            // �����������ز���ID
            $insertId = $this->add($supplier_info);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݱ�Ÿ�����ϸ��Ϣ
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
     * ������Ϣ
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
     * ����ID��ѯ��Ϣ
     *
     * @access	public
     * @param  mixed $ids
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ϣ
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

/* End of file SupplierModel.class.php */
/* Location: ./Lib/Model/SupplierModel.class.php */