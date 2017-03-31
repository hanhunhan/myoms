<?php

/* 
 * ���ʱ���Model��
 * 
 */
class PayoutChangeModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PAYOUT';
    
    public function __construct() {
        parent::__construct();
    }
    
    //״̬��ʶ
    protected $_payout_status_remark = array(
                                1=>"δ����",
                                2=>"�����룬�����",
                                3=>"���ͨ��",
                                4=>"���δͨ��",
                                
                            );
    //״̬
    protected $_payout_status = array(
                                "no_audit"=>1,  //δ����
                                "applied"=>2,   //�����룬�����
                                "passed"=>3,    //���ͨ��
                                "no_passed"=>4, //���δ
                                
                            );
    
    public function get_payout_status_remark(){
        return $this->_payout_status_remark;
    }
    
    public function get_payout_status(){
        return $this->_payout_statusk;
    }
    /**
     * �������ʱ�����¼
     * @param array $data Ҫ�����ֶεļ�ֵ��
     * return �ɹ��������ID \ ʧ�ܣ�false
     */
    public function add_payout_info($data)
    {
        $insertid = "";
        if(is_array($data) && !empty($data))
        {
            $insertid = $this->add($data);
        }
        return $insertid ? $insertid : false;
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
        
        return $info;
    }
    
    /**
     * ����ID��Ϣ
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

    	$up_num = self::update_info_by_cond($cond_where,$update_arr);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ�����������˿���ϸ��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_info_by_cond( $cond_where,$update_arr)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$options['table'] = $this->tablePrefix.$this->tableName;
    		$up_num = $this->where($cond_where)->save($update_arr, $options);
    		//echo $this->getLastSql();
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    
}

