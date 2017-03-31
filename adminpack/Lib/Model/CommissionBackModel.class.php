<?php

/* 
 * �н�Ӷ������Model
 */
class CommissionBackModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'COMMISSION_BACK';
    
    //Ӷ������״̬��־����
    protected $_conf_commission_status_remark = array(
                                        1 => '������',
                                        0 => 'δ����',
                                        4 => '��ɾ��',
    );
    
    //Ӷ����������
    protected $_conf_commission_status = array(
                                'have_back'=>1,
                                'no_back'  => 0,
                                'have_del' =>4,
                                
    );
    
    //���췽��
    public function __construct($name = '') {
        parent::__construct($name);
    }

    //��ȡӶ������״̬��ʶ����
    public function get_conf_commission_status_remark(){
        return $this->_conf_commission_status_remark;
    }
    
    //��ȡӶ������״̬����
     public function get_conf_commission_status(){
        return $this->_conf_commission_status;
    }
    

    /**����Ӷ�����ؼ�¼
     * @param $commission_info array() �����ֶμ�ֵ��
     * return $insertId �����²���ļ�¼��Id 
     * ʧ�ܷ���false
     */
    public function add_commission_info($commission_info){
        if(is_array($commission_info) && !empty($commission_info))
        {   
            // �����������ز���ID
            $options['table'] = parent::getTableName();
            $insertId = $this->add($commission_info, $options);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    /**����ID�޸���Ϣ
     * @param $ids mixed ��Ҫ�޸ĵļ�¼��id ����ID������
     * @param $update_arr array() Ҫ�޸ĵ��ֶεļ�ֵ��
     * return �ɹ�  ���ر����������  ʧ�� ����false 
     */
    
    public function update_commission_info_by_id($ids,$update_arr){
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
        
        $up_num = self::update_commission_info_by_cond($update_arr, $cond_where);
        
        return $up_num > 0  ? $up_num : FALSE;
        
    }
    
    /**
     * ��������
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_commission_info_by_cond($update_arr, $cond_where)
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
     * ����IDɾ����ԱӶ���¼
     * @param $ids mixed  ID
     * return bool
     */
    public function del_commission_info_by_id($ids){

        if(is_array($ids) && !empty($ids)){
            $id_str = implode(",", $ids);
            $conf_where = "ID IN($id_str)";
        }else{
            $conf_where = "ID = $ids";
        }
        
        $result = $this->del_commission_info_by_conf();
        
        return $result;
    }
    
     /**
     * ��������ɾ����ԱӶ���¼
     * @param $ids mixed  ID
     * return bool
     */

     public function del_commission_info_by_conf($conf_where = ""){
   
        if($conf_where){
            $update_arr["STATUS"] = $this->_conf_commission_status["have_del"];
            $result = $this->where($conf_where)->save($update_arr);
        }        
        return $result;
    }
 
}

