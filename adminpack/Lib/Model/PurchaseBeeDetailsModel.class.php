<?php
/**
 * С�۷�ɹ��ڿͻ�ִ��
 *
 * @author zhang Xiaojun
 */
class PurchaseBeeDetailsModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PURCHASER_BEE_DETAILS';
    
    //���캯��
    public function __construct(){
        parent::__construct();
    }
    
    public function get_bee_detail_status(){
        return $status = array(
            0 => 'δ�ύ����',
            1 => '���ύ����',
            2 => '�ѱ���',
            3 => '�Ѳ���',
            4 => '�������������',
        );
    }
	/**
     * ����ָ����������С�۷�������Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_bee_detail_info($update_arr, $cond_where){	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
}

/* End of file PurchaseListModel.class.php */
/* Location: ./Lib/Model/PurchaseListModel.class.php */