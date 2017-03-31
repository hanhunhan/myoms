<?php
/**
 * �������MODEL
 *
 * @author liuhu
 */
class WarehouseUseModel extends Model {
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'WAREHOUSE_USE_DETAILS';
    
    /***���������ϸ״̬***/
    private  $_conf_status = array(
                                    'not_confirm' => 0,   //δȷ��
                                    'confirmed' => 1,     //��ȷ��
                                );
    
    /***���������ϸ״̬����***/
    private  $_conf_status_remark = array(
                                        0 => 'δȷ��',
                                        1 => '��ȷ��',
                                    );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ��ȡ���������ϸ״̬����
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
     * ��ȡ���������ϸ״̬��������
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
     * ��ӿ��������ϸ
     *
     * @access	public
     * @param	array  $detail_arr ������ϸ
     * @return	mixed  �ɹ�����������ϸ��ţ�ʧ�ܷ���FALSE
     */
    public function add_used_info($detail_arr)
    {
        $insertId = 0;
        
        if(is_array($detail_arr) && !empty($detail_arr))
        {   
            // �����������ز���ID
            $insertId = $this->add($detail_arr);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    /**
     * �������ñ��ɾ��������ϸ
     * @access	public
     * @param	int  $id  �ɹ�������ϸ
     * @return	mixed  Ӱ��������ʧ�ܷ���FALSE
     */
    public function del_use_info_by_id($id)
    {   
        $up_num = 0;
        $id = intval($id);
        
        if($id > 0)
        {
            $cond_where = "ID = '".$id."'";
            $up_num = $this->where($cond_where)->delete();
        }
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݲɹ���ϸ��ţ�ȷ������
     *
     * @access	public
     * @param	mixed  $purchase_id �ɹ���ϸ���
     * @return	mixed  Ӱ��������ʧ�ܷ���FALSE
     */
    public function confirm_used_by_purchase_id($purchase_id)
    {   
        $update_arr['STATUS'] = $this->_conf_status['confirmed'];
        $update_arr['CONFIRM_TIME'] = date('Y-m-d H:i:s');
        
        $up_num =  self::update_info_by_id($purchase_id, $update_arr);
        
        return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݲɹ���ϸ��ţ�ȡ��ȷ������״̬
     *
     * @access	public
     * @param	mixed  $purchase_id �ɹ���ϸ���
     * @return	mixed  Ӱ��������ʧ�ܷ���FALSE
     */
    public function cancel_confirm_used_by_purchase_id($purchase_id)
    {   
        $update_arr['STATUS'] = $this->_conf_status['not_confirm'];
        $update_arr['CONFIRM_TIME'] = '';
        
        $up_num =  self::update_info_by_id($purchase_id, $update_arr);
        
        return $up_num > 0  ? $up_num : FALSE;
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
     * ������������ĳ����ϸ��Ϣ
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
     * ��ȡ���һ��������Ϣ
     * @access	public
     * @param	int  $purchase_id  �ɹ���ϸ���
     * @return	 array $use_info
     */
    public function get_last_use_info_by_purchase_id($purchase_id)
    {
        $use_info = array();
        
        if( $purchase_id > 0)
        {
            $use_info = $this->where("PL_ID = '".$purchase_id."'")->order('ID DESC')->find();
        }
        
        return $use_info;
    }

    /**
     * ��ȡ�ɹ�ʹ���û��ֿ���Ϣ
     * @param $purchase_id
     * @return array
     */
    public function getDisplaceUseByPurchaseId($purchase_id){
        $use_info = array();

        if( $purchase_id > 0)
        {
            $use_info = $this->where("PL_ID = '".$purchase_id."' AND TYPE = 2")->order('ID DESC')->select();
        }

        return $use_info;
    }

    /**
     * ��ȡ�����������  �û���������
     * @param $purchase_id
     * @param $useType
     * @return int
     */
    public function getSumnumByPurchaseId($purchase_id,$useType){

        if( $purchase_id > 0){
            $queryRet = M('erp_warehouse_use_details')->where("PL_ID = '".$purchase_id."' AND TYPE = {$useType}")->sum('USE_NUM');
            return intval($queryRet);
        }

        return 0;
    }
}

/* End of file WarehouseUseModel.class.php */
/* Location: ./Lib/Model/WarehouseUseModel.class.php */