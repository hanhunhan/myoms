<?php

/**
 * ��Ŀ�ɱ���
 *
 * @author liuhu
 */
class ProjectCostModel extends Model {
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'COST_LIST';
    
    //�ɱ���Դ���
    private $_conf_cost_from = array(
                                '1' => '����ɹ�',
                                '2' => '�ɹ���ͬǩ��',
                                '3' => '�ɹ���������',
                                '4' => '�ɹ�����ͨ��',
                                '5' => '��Ա�ɽ��н�Ӷ��',
                                '6' => '�н�Ӷ�����뱨��',
                                '7' => '�н�Ӷ����ͨ��',
                                '8' => '��Ա�ɽ��н�ɽ���',
                                '9' => '�н�ɽ������뱨��',
                                '10' => '�н�ɽ�������ͨ��',
                                '11' => '��Ա�ɽ���ҵӶ��',
                                '12' => '��ҵӶ�����뱨��',
                                '13' => '��ҵӶ����ͨ��',
                                '14' => '��Ա�ɽ���ҵ�ɽ���',
                                '15' => '��ҵ�ɽ������뱨��',
                                '16' => '��ҵ�ɽ�������ͨ��',
                                '17' => 'ҵ�����',
                                '18' => 'Ԥ�����������ý���',
                                '19' => 'Ԥ���������������뱨��',
                                '20' => 'Ԥ�����������ñ���ͨ��',
                                '21' => '�ɱ�����',
                                '22' => '�����ֽ�',
                                '23' => '�����ֽ����뱨��',
                                '24' => '�����ֽ���ͨ��',
                                '25' => '�ɱ�������뱨��',
                                '26' => '�ɱ���䱨��ͨ��',
    							'27' => '�ɹ��˿�'
                            );
    
    //�ɱ�״̬
    private $_conf_cost_status = array(
                            '1' => '������δ����',
                            '2' => '�ѷ���δ����',
                            //'3' => '���뱨��',
                            '4' => '�ѱ���'
                        );
    
    //�ɱ���Դ��ɱ�״̬��Ӧ��ϵ
    private $_conf_status_from_map = array(
                                        '1' => array(1),
                                        '2' => array(2,5,8,11,14,18,22),
                                        '3' => array(3,6,9,12,15,19,23,25),
                                        '4' => array(4,7,10,13,16,17,20,21,24,26,27)
                                    );
    
    //�ɱ�����
    private $_conf_cost_type = array(
                            '1' => '�ɹ�',
                            '2' => '�н�Ӷ��',
                            '3' => '�н�ɽ���',
                            '4' => '��ҵӶ��',
                            '5' => '��ҵ�ɽ���',
                            '6' => 'ҵ�����',
                            '7' => 'Ԥ������������',
                            '8' => '�ɱ�����',
                            '9' => '�����ֽ�',
                            '10' => '�ɱ����',
                        );
    
    //�ɱ���Դ��ɱ����Ͷ�Ӧ��ϵ
    private $_conf_type_from_map = array(
                                        '1' => array(1, 2, 3, 4, 27),
                                        '2' => array(5, 6, 7),
                                        '3' => array(8, 9, 10),
                                        '4' => array(11, 12, 13),
                                        '5' => array(14, 15, 16),
                                        '6' => array(17),
                                        '7' => array(18, 19, 20),
                                        '8' => array(21),
                                        '9' => array(22, 23, 24),
                                        '10' => array(25, 26)
                                    );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ���سɱ���Դ
     *
     * @access	public
     * @param	none
     * @return	array ������Դ����
     */
    public function get_conf_cost_from ()
    {
        return $this->_conf_cost_from;
    }
    
    
    /**
     * ���سɱ�״̬����
     *
     * @access	public
     * @param	none
     * @return	array ������Դ����
     */
    public function get_conf_cost_status()
    {
        return $this->_conf_cost_status;
    }
    
    
    /**
     * �����Ŀ�ɱ�
     *
     * @access	public
     * @param	string  $cost_info �ɱ���Ϣ����
     * @param  int     $cost_info['CASE_ID']    ������� �����
     * @param  int     $cost_info['ENTITY_ID']  ҵ���� �����
     * @param  int     $cost_info['EXPEND_ID']  �ɱ���ϸ��� �����
     * @param  int     $cost_info['ORG_ENTITY_ID']  ԭʼҵ���� �����
     * @param  int     $cost_info['ORG_EXPEND_ID']  ԭʼ�ɱ���ϸ��� �����
     * @param  int     $cost_info['EXPEND_FROM'] �ɱ���Դ �����
     * @param  float   $cost_info['FEE'] �ɱ���� �����
     * @param  string  $cost_info['FEE_REMARK'] �������� ��ѡ�
     * @param  float  $cost_info['INPUT_TAX'] ����˰ ��ѡ� 
     * @param  int     $cost_info['ADD_UID']    �����û���� �����
     * @param  date    $cost_info['OCCUR_TIME'] ����ʱ�� �����
     * @param  int     $cost_info['ISFUNDPOOL'] �Ƿ��ʽ�أ�0��1�ǣ� �����
     * @param  int     $cost_info['IS_KF'] �ɱ�����ID �����
     * @param  int     $cost_info['FEE_ID'] �ɱ�����ID �����
     * @return	mixed  �ɹ����سɱ���ţ�ʧ�ܷ���FALSE
     */
    public function add_cost_info($cost_info)
    {   
        $insert_result = FALSE;
        $cost_arr = array();
        
        //�������
        $cost_info['CASE_ID'] = intval($cost_info['CASE_ID']);
        
        if($cost_info['CASE_ID'] > 0 )
        {   
            /**���ݰ�����Ż�ȡ��Ҫ�İ�����Ϣ**/
            $project_case = D('ProjectCase');
			$project = D('Project');
            $caseinfo = array();
            $search_field = array('SCALETYPE ', 'CUSER', 'PROJECT_ID');
            $caseinfo = $project_case->get_info_by_id($cost_info['CASE_ID'], $search_field);
            
            if(is_array($caseinfo) && !empty($caseinfo))
            {   
                //��Ŀ���
                $cost_info['PROJECT_ID'] = !empty($caseinfo[0]['PROJECT_ID']) ? 
                                            intval($caseinfo[0]['PROJECT_ID']) : 0;
                //��������
                $cost_info['CASE_TYPE'] = !empty($caseinfo[0]['SCALETYPE']) ? 
                                            intval($caseinfo[0]['SCALETYPE']) : 0;
                //����������
                $cost_info['USER_ID'] = !empty($caseinfo[0]['CUSER']) ? 
                                            intval($caseinfo[0]['CUSER']) : 0;
                //�������������ڲ���
                $userinfo = array();
                $cond_where = "ID = '".$cost_info['USER_ID']."'";
                $userinfo = M('erp_users')->field('DEPTID,CITY')->where($cond_where)->find();
                $cost_info['DEPT_ID'] = !empty($userinfo['DEPTID']) ? 
                                            intval($userinfo['DEPTID']) : 0;
				$projectinfo = $project->get_info_by_id($cost_info['PROJECT_ID'],array('CITY_ID'));
                //�������������ڳ���
                //$cost_info['CITY_ID'] = !empty($userinfo['CITY']) ?   intval($userinfo['CITY']) : 0;
				$cost_info['CITY_ID'] = !empty($projectinfo[0]['CITY_ID']) ?   intval($projectinfo[0]['CITY_ID']) : 0;
				
            }
        }
        else
        {
            return $insert_result;
        }
        
        //ҵ��ʵ���ţ��ɹ����뵥��š�ҵ��������뵥��š�����
        $cost_info['ENTITY_ID'] = intval($cost_info['ENTITY_ID']);
        //�ɱ���ϸ���
        $cost_info['EXPEND_ID'] = intval($cost_info['EXPEND_ID']);
        //ԭʼҵ��ʵ���ţ��ɹ����뵥��š�ҵ��������뵥��š�����
        $cost_info['ORG_ENTITY_ID'] = intval($cost_info['ORG_ENTITY_ID']);
        //ԭʼ�ɱ���ϸ���
        $cost_info['ORG_EXPEND_ID'] = intval($cost_info['ORG_EXPEND_ID']);
        //�ɱ���Դ
        $cost_info['EXPEND_FROM'] = intval($cost_info['EXPEND_FROM']);
        //�ɱ����
        $cost_info['FEE'] = floatval($cost_info['FEE']);
        //�ɱ��������(�Ǳ���)
        $cost_info['FEE_REMARK'] = strip_tags($cost_info['FEE_REMARK']);
        //����˰���Ǳ��
        $cost_info['INPUT_TAX'] = floatval($cost_info['INPUT_TAX']);
        //�����ID
        $cost_info['ADD_UID'] = intval($cost_info['ADD_UID']);
        //�ɱ�����ʱ��
        $cost_info['OCCUR_TIME'] = $cost_info['OCCUR_TIME'];
        //�ɱ�״̬
        $status = self::_get_status_by_from($cost_info['EXPEND_FROM']);
        $cost_info['STATUS'] = $status;
        
        if(empty($cost_info['STATUS']))
        { 
            return $insert_result;
        }
        
        $type = self::_get_type_by_from($cost_info['EXPEND_FROM']);
        
        if(empty($type))
        { 
            return $insert_result;
        }
        
        $cost_info['TYPE'] = $type;
        
        //�Ƿ��ʽ��
        $cost_info['ISFUNDPOOL'] = intval($cost_info['ISFUNDPOOL']);
        //�Ƿ�۷�
        $cost_info['ISKF'] = intval($cost_info['ISKF']);
        //�ɱ�����ID
        $cost_info['FEE_ID'] = intval($cost_info['FEE_ID']);
        
        $insert_result = $this->add($cost_info);
        
        return $insert_result > 0 ? $insert_result : FALSE;
    }
    
    
    /**
     * ������Դȷ�ϳɱ�״̬
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
     * ������Դȷ�ϳɱ�����
     *
     * @access	public
     * @param	int  $from ��Դ��־
     * @return	mixed ƥ��ɹ�����״̬��־��ƥ��ʧ�ܷ���FALSE
     */
    private function _get_type_by_from($from)
    {   
        $type = FALSE;
        $from  = intval($from);
        
        if( $from > 0)
        {
            foreach($this->_conf_type_from_map as $key => $value)
            {
                if(in_array($from, $value))
                {
                    $type = $key;
                    break;
                }
            }
        }
        
        return $type;
    }
    
    
    /**
     * �޸���Ŀ�ɱ���Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	int  $case_id �������
     * @param	int  $entity_id ҵ��ʵ���ţ��ɹ����뵥��ţ��������뵥��š�����
     * @param	int  $expend_id �ɹ���ϸ��������ϸID
     * @param	int  $status �ɱ���¼״̬
     * @return	mixed   ɾ���ɹ����ظ���������ɾ������FALSE
     */
    public function update_income_info($update_arr, $case_id, $entity_id, $expend_id = 0, $status = '')
    {   
        $up_num = 0;
        
        $case_id  = intval($case_id);
        $entity_id = intval($entity_id);
        $expend_id = intval($expend_id);
        $status = intval($status);
        
        if($case_id > 0 && $entity_id > 0)
        {   
            $cond_where = "CASE_ID = '".$case_id."' AND ENTITY_ID = '".$entity_id."' ";
            $pay_id > 0 ? $cond_where .= " AND EXPEND_ID = '".$expend_id."'" : "";
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
    	}
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ɾ��ָ����Ŀ�ɱ���Ϣ
     *
     * @access	public
     * @param	int  $case_id �������
     * @param	int  $entity_id ҵ��ʵ���ţ��ɹ����뵥��ţ��������뵥��š�����
     * @param	int  $expend_id �ɹ���ϸ��������ϸID
     * @param	int  $status �ɱ���¼״̬
     * @return	mixed   ɾ���ɹ����ظ���������ɾ������FALSE
     */
    public function delete_income_info($case_id, $entity_id, $expend_id = 0, $status = '')
    {   
        $del_num = FALSE;
        
        $case_id  = intval($case_id);
        $entity_id = intval($entity_id);
        $expend_id = intval($expend_id);
        $status = intval($status);
        
        if($case_id > 0 && $entity_id > 0)
        {   
            $cond_where = "CASE_ID = '".$case_id."' AND ENTITY_ID = '".$entity_id."' ";
            $pay_id > 0 ? $cond_where .= " AND EXPEND_ID = '".$expend_id."'" : "";
            $status > 0 ? $cond_where .= " AND STATUS = '".$status."'" : "";
            
            $del_num = self::delete_info_by_cond($cond_where);
        }
        
        return $del_num;
    }
    
    
    /**
     * ��������ɾ����Ϣ
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
     * ����������ȡ�ɱ���Ϣ
     * @param string $cond_where ����
     * @param array  $search_arr ��ѯ�ֶ�
     * return $info �ɹ�:���� \ ʧ�� ��false
     */
    public function get_cost_info_by_cond($cond_where,$search_arr)
    {
        $info = array();
        if(is_array($search_arr) && !empty($search_arr))
        {
            $info = $this->where($cond_where)->field($search_arr)->select();
        }
        return $info ? $info : false;
        
    }
}

/* End of file ProjectCostModel.class.php */
/* Location: ./Lib/Model/ProjectCostModel.class.php */