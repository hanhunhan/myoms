<?php

/**
 * ��Ŀ����ģ��
 *
 * @author liuhu
 */
class ProjectIncomeModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'INCOME_LIST';
    
    //������Դ���
    private $_conf_income_from = array(
                                '1' => '���̻�Ա֧��',
                                '2' => 'ȷ�ϵ��̻�Ա����',
                                '3' => '���̻�Ա��Ʊ',
                                '4' => '���̻�Ա�˿�',
                                '5' => 'ɾ�����̻�Ա����',
                                '6' => '�ɱ�����',
                                '7' => '������Ա�ؿ�',
                                '8' => '������Ա��Ʊ',
                                '9' => '�޸ķ�����Ա�ؿ�',
                                '10' => 'ɾ��������Ա�ؿ�',
                                '11' => 'Ӳ��ؿ�',
                                '12' => 'Ӳ�㿪Ʊ',
                                '13' => '�޸�Ӳ��ؿ�',
                                '14' => 'ɾ��Ӳ��ؿ�',
                                '15' => '��ؿ�',
                                '16' => '���Ʊ',
                                '17' => '�޸Ļ�ؿ�',
                                '18' => 'ɾ����ؿ�',
                                '19' => '�ɱ���������'
                            );
    
    //����״̬
    private $_conf_income_status = array(
                            '1' => 'ҵ��Ԥ��',
                            '2' => '����(ȷ��)Ԥ��',
                            '3' => '��Ʊ����',
                            '4' => '�ؿ�����'
                        );
    
   //������Դ������״̬��Ӧ��ϵ
   private $_conf_status_from_map = array(
                                        '1' => array(1),
                                        '2' => array(2,19),
                                        '3' => array(3,8,12,16),
                                        '4' => array(7,11,15)
                                    );
   
   //��Ҫ��ѯ���������¼״̬��������Դ
   private $_conf_get_last_income_status = array(4);
   
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ����������Դ
     *
     * @access	public
     * @param	none
     * @return	array ������Դ����
     */
    public function get_conf_income_from ()
    {
        return $this->_conf_income_from;
    }
    
    
    /**
     * ��������״̬����
     *
     * @access	public
     * @param	none
     * @return	array ������Դ����
     */
    public function get_conf_income_status()
    {
        return $this->_conf_income_status;
    }
    
    
    /**
     * �����Ŀ����
     *
     * @access	public
     * @param	string  $income_info ������Ϣ����
     * @param  int     $cost_info['CASE_ID']    ������� �����
     * @param  int     $income_info['ENTITY_ID']  ҵ��ʵ���� �����
     * @param  int     $income_info['PAY_ID']  ������ϸ��� �����
     * @param  int     $income_info['INCOME_FROM'] ������Դ �����
     * @param  float   $income_info['INCOME'] ������ �����
     * @param  string  $income_info['INCOME_REMARK'] �������� ��ѡ�
     * @param  float   $income_info['OUTPUT_TAX'] ����˰ ��ѡ� 
     * @param  int     $income_info['ADD_UID']    �����û���� �����
     * @param  date    $income_info['OCCUR_TIME'] ����ʱ�� �����
     * @return	mixed  �ɹ����������ţ�ʧ�ܷ���FALSE
     */
    public function add_income_info($income_info)
    {   
        $insert_result = FALSE;
        $income_arr = array();
        
        //�������
        $income_arr['CASE_ID'] = intval($income_info['CASE_ID']);
        if($income_arr['CASE_ID'] > 0 )
        {   
            /**���ݰ�����Ż�ȡ��Ҫ�İ�����Ϣ**/
            $project_case = D('ProjectCase');
            $caseinfo = array();
            $search_field = array('SCALETYPE ', 'CUSER', 'PROJECT_ID');
            $caseinfo = $project_case->get_info_by_id($income_arr['CASE_ID'], $search_field);
            
            if(is_array($caseinfo) && !empty($caseinfo))
            {   
                //��Ŀ���
                $income_arr['PROJECT_ID'] = !empty($caseinfo[0]['PROJECT_ID']) ? 
                                            intval($caseinfo[0]['PROJECT_ID']) : 0;
                //��������
                $income_arr['CASE_TYPE'] = !empty($caseinfo[0]['SCALETYPE']) ? 
                                            intval($caseinfo[0]['SCALETYPE']) : 0;
                //����������
                $income_arr['USER_ID'] = !empty($caseinfo[0]['CUSER']) ? 
                                            intval($caseinfo[0]['CUSER']) : 0;
                //�������������ڲ���
                $userinfo = array();
                $cond_where = "ID = '".$income_arr['USER_ID']."'";
                $userinfo = M('erp_users')->field('DEPTID')->where($cond_where)->find();
                $income_arr['DEPT_ID'] = !empty($userinfo['DEPTID']) ? 
                                            intval($userinfo['DEPTID']) : 0;
                //���������˲������ڳ���
                $deptinfo = array();
                $cond_where = "ID = '".$income_arr['DEPT_ID']."'";
                $deptinfo = M('erp_dept')->field('CITY_ID')->where($cond_where)->find();
                $income_arr['CITY_ID'] = !empty($deptinfo['CITY_ID']) ? 
                                            intval($deptinfo['CITY_ID']) : 0;
            }
        }
        else
        {
            return $insert_result;
        }
        
        //ҵ��ʵ���ţ���Ա��š�����ͬ��š��������뵥��š�����
        $income_arr['ENTITY_ID'] = intval($income_info['ENTITY_ID']);
        //������ϸ���
        $income_arr['PAY_ID'] = intval($income_info['PAY_ID']);
        //������Դ
        $income_arr['INCOME_FROM'] = intval($income_info['INCOME_FROM']);
        //������
        $income_arr['INCOME'] = floatval($income_info['INCOME']);
        //����������(�Ǳ���)
        $income_arr['INCOME_REMARK'] = strip_tags($income_info['INCOME_REMARK']);
        //����˰���Ǳ��
        $income_arr['OUTPUT_TAX'] = floatval($income_info['OUTPUT_TAX']);
        //�����ID
        $income_arr['ADD_UID'] = intval($income_info['ADD_UID']);
        //���뷢��ʱ��
        $income_arr['OCCUR_TIME'] = $income_info['OCCUR_TIME'];
        //����״̬
        if(in_array($income_arr['INCOME_FROM'], $this->_conf_get_last_income_status))
        {   
            /**��ȡ����һ������״̬**/
            $last_income = $this->get_last_income_by_pid($income_arr['CASE_ID'], 
                    $income_arr['ENTITY_ID'], $income_arr['PAY_ID']);
            
            $status = !empty($last_income['STATUS']) ? 
                        intval($last_income['STATUS']) : 0;
        }
        else
        {
            $status = self::_get_status_by_from($income_arr['INCOME_FROM']);
        }
        
        $income_arr['STATUS'] = $status;

        if(empty($income_arr['STATUS']))
        { 
            return $insert_result;
        }
        $insert_result = $this->add($income_arr);
        return $insert_result > 0 ? $insert_result : FALSE;
    }
    
    
    /**
     * �޸���Ŀ������Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	int  $case_id �������
     * @param	int  $entity_id ҵ��ʵ���ţ���Ա��ţ����߹���ͬ��š�����
     * @param	int  $pay_id ֧����ϸ��Ż��߿�Ʊ��¼���
     * @param	int  $status ����״̬
     * @return	mixed   ɾ���ɹ����ظ���������ɾ������FALSE
     */
    public function update_income_info($update_arr, $case_id, $entity_id, $pay_id = 0, $status = '')
    {   
        $up_num = 0;
        
        $case_id  = intval($case_id);
        $entity_id = intval($entity_id);
        $pay_id = intval($pay_id);
        $status = intval($status);
        
        if($case_id > 0 && $entity_id > 0)
        {   
            $cond_where = "CASE_ID = '".$case_id."' AND ENTITY_ID = '".$entity_id."' ";
            $pay_id > 0 ? $cond_where .= " AND PAY_ID = '".$pay_id."'" : "";
            $status > 0 ? $cond_where .= " AND STATUS = '".$status."'" : "";
            
            $up_num = self::update_info_by_cond($update_arr, $cond_where);
        }
        
        return $up_num;
    }
    
    
    /**
     * ��������������Ϣ
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
     * ɾ����Ŀ������Ϣ
     *
     * @access	public
     * @param	int  $case_id �������
     * @param	int  $entity_id ҵ��ʵ���ţ���Ա��ţ����߹���ͬ��š�����
     * @param	int  $pay_id ֧����ϸ��Ż��߿�Ʊ��¼���
     * @param	int  $status ����״̬
     * @return	mixed   ɾ���ɹ����ظ���������ɾ������FALSE
     */
    public function delete_income_info($case_id, $entity_id, $pay_id = 0, $status = '')
    {   
        $del_num = FALSE;
        
        $case_id  = intval($case_id);
        $entity_id = intval($entity_id);
        $pay_id = intval($pay_id);
        $status = intval($status);
        
        if($case_id > 0 && $entity_id > 0)
        {   
            $cond_where = "CASE_ID = '".$case_id."' AND ENTITY_ID = '".$entity_id."' ";
            $pay_id > 0 ? $cond_where .= " AND PAY_ID = '".$pay_id."'" : "";
            $status > 0 ? $cond_where .= " AND STATUS = '".$status."'" : "";
            
            $del_num = self::delete_info_by_cond($cond_where);
        }
        
        return $del_num;
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
    	}
    
    	return $del_num > 0  ? $del_num : FALSE ;
    }
    
    
    /**
     * ������Դȷ������״̬
     *
     * @access	public
     * @param	int  $from ��Դ��־
     * @return	mixed ƥ��ɹ�����״̬��־��ƥ��ʧ�ܷ���FALSE
     */
    private function _get_status_by_from($from)
    {   
        $status = FALSE;
        $from  = intval($from);
        
        if( $from > 0)
        {
            foreach($this->_conf_status_from_map as $key => $value)
            {
                if(in_array($from, $value))
                {
                    $status = $key;
                    break;
                }
            }
        }
        
        return $status;
    }
    
    
    /**
     * ����֧����ϸ��Ų�ѯ���һ��������ϸ��¼
     *
     * @access	public
     * @param	int  $case_id �������
     * @param	int  $entity_id ҵ��ʵ���ţ���Ա��ţ����߹���ͬ��š�����
     * @param	int  $pay_id ֧����ϸ��Ż��߿�Ʊ��¼���
     * @return	array ������ϸ
     */
    public function get_last_income_by_pid($case_id, $entity_id, $pay_id)
    {   
        $income_info = array();
        
        $case_id = intval($case_id);
        $entity_id = intval($entity_id);
        $pay_id = intval($pay_id);
        
        if($case_id > 0 && $entity_id > 0 && $pay_id > 0)
        {
            $cond_where = "CASE_ID = '".$case_id."' AND "
                    . " ENTITY_ID = '".$entity_id."' AND PAY_ID = '".$pay_id."' ";
            $income_info = $this->where($cond_where)->order('ID DESC')->find();
        }
        
        return $income_info;
    }
}

/* End of file ProjectIncomeModel.class.php */
/* Location: ./Lib/Model/ProjectIncomeModel.class.php */