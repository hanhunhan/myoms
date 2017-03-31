<?php

/**
 * ��Ŀ�ɱ���
 *
 * @author liuhu
 */
class ProjectCostModel extends Model {
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'COST_LIST';

    const USE_PERCENT = 0.4; //�ɹ���Ŀ���ñ���
    
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
    							'27' => '�ɹ��˿�',
								'28' => '��Ա��ƱPOS��������',
								'29' => '����������˰��',
								'30' => '�ڿͲɹ���������',
								'31' => '�ɹ��������벵��',
                                '32' => '֧���������������뱨��',
                                '33' => '֧�����������ñ���ͨ��',
                                '34' => '֧���������������뱨�������',
                                '35' => '�����ⲿ�ɽ���������',
                                '36' => '�ʽ�س��'
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
                                        '2' => array(2,5,8,11,14,18,22,30,31,32, 34),
                                        '3' => array(3,6,9,12,15,19,23,25),
                                        '4' => array(4,7,10,13,16,17,20,21,24,26,27,28,29,33,35,36)
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
							'11' => 'POS������',
							'12' => '����������˰��',
                            '13' => '֧������������',
                            '14' => '�ⲿ�ɽ�����'
                        );
    
    //�ɱ���Դ��ɱ����Ͷ�Ӧ��ϵ
    private $_conf_type_from_map = array(
                                        '1' => array(1, 2, 3, 4, 27,30,31),
                                        '2' => array(5, 6, 7),
                                        '3' => array(8, 9, 10),
                                        '4' => array(11, 12, 13),
                                        '5' => array(14, 15, 16),
                                        '6' => array(17),
                                        '7' => array(18, 19, 20),
                                        '8' => array(21,36),
                                        '9' => array(22, 23, 24),
                                        '10' => array(25, 26),
										'11' => array(28),
										'12' => array(29),
										'13' => array(32, 33, 34),  // ֧������������
                                        '14' => array(35) //�ⲿ�ɽ�����
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
            $caseinfo = array();
            $search_field = array('SCALETYPE ', 'CUSER', 'PROJECT_ID', 'PARENTID');
            $caseinfo = $project_case->get_info_by_id($cost_info['CASE_ID'], $search_field);
            
            $cost_info['SUB_CASE_ID'] = 0;
            if(is_array($caseinfo) && !empty($caseinfo))
            {   
                /***����������ϼ�ҵ����CASEID���洢�ϼ�ҵ������ţ���ǰҵ������Ŵ洢SUB_CASE_ID***/
                $parent_case_id = intval($caseinfo[0]['PARENTID']);
                if( $parent_case_id > 0)
                {   
                    //������ҵ�������
                    $cost_info['SUB_CASE_ID'] = intval($cost_info['CASE_ID']);
                    
                    //��ҵ�������
                    $cost_info['CASE_ID'] = $parent_case_id;
                }
                
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
               if(!$cost_info['DEPT_ID']) $cost_info['DEPT_ID'] = !empty($userinfo['DEPTID']) ? 
                                            intval($userinfo['DEPTID']) : 0;
			  
                //�������������ڳ���
                $cost_info['CITY_ID'] = !empty($userinfo['CITY']) ? 
                                            intval($userinfo['CITY']) : 0;
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

    /**
     * ��ɱ���������ʽ�ط���
     * @param $bizId
     * @param $copiedData
     * @return bool|mixed
     */
    public function addFundPoolCost($bizId, &$copiedData) {
        $result = false;
        if (intval($bizId)) {
            $sql = <<<SQL
              SELECT b.*,
                    u.deptid,
                    p.city_id
              FROM erp_benefits b
              LEFT JOIN erp_users u ON u.id = b.auser_id
              LEFT JOIN erp_project p ON p.id = b.project_id
              WHERE b.id = %d
SQL;
            $dbResult = $this->query(sprintf($sql, $bizId));
            if (notEmptyArray($dbResult)) {
                $dbResult = $dbResult[0];
                $data['CASE_ID'] = $dbResult['CASE_ID'];  //������� �����
                $data['CASE_TYPE'] = $dbResult['SCALE_TYPE'];  // ��Ŀ����
                $data['ENTITY_ID'] = $bizId;  // ҵ��ʵ���� �����
                $data['EXPEND_ID'] = $bizId;  // �ɱ���ϸ��� �����
                $data['ORG_ENTITY_ID'] = $bizId;  // ҵ��ʵ���� �����
                $data['ORG_EXPEND_ID'] = $bizId;
                $data['FEE'] = $dbResult['AMOUNT'];  // �ɱ���� �����
                $data['ADD_UID'] = $_SESSION['uinfo']['uid'];  //�����û���� �����
                $data['OCCUR_TIME'] = date('Y-m-d H:i:s');  //����ʱ�� �����
                $data['ISFUNDPOOL'] = 1;  // �Ƿ��ʽ�أ�0��1�ǣ� �����
                $data['ISKF'] = 0;  // �Ƿ�۷� �����
                $data['FEE_REMARK'] = $dbResult['DESRIPT']; //�������� ��ѡ�
                $data['INPUT_TAX'] = 0; // ����˰ ��ѡ�
                $data['FEE_ID'] = 80; // ֧������������
                $data['EXPEND_FROM'] = 32; // ֧������������
                $data['STATUS'] = 2;  //
                $data['PROJECT_ID'] = $dbResult['PROJECT_ID'];
                $data['USER_ID'] = $dbResult['AUSER_ID'];
                $data['DEPT_ID'] = $dbResult['DEPTID'];
                $data['CITY_ID'] = $dbResult['CITY_ID'];
                $data['TYPE'] = 13;  // �ɱ�����Ϊ֧������������
                $copiedData = $data;
                return true;

                $costId = $this->add($data);
                if ($costId > 0) {
                    $result = true;
                    $copiedData = $data;
                }
            }
        }

        return $result;
    }

    /**
     * ����ɹ�ʱ�������һ��������Ϣ���ɱ���������õ���Ŀ���٣��������һ����ֵ��������Ϣ
     * ���ԣ���ÿ����ɱ������������Ϣʱ����Ҫ�Ȳ�ѯ��ǰ�ɹ���ϸ�µ��ܵ����óɱ��������ܵ����óɱ�
     * �ٵ������������
     * @param $purchaseId �ɹ���ϸ���
     * @param $msg
     * @param bool $updateWarehouseCost �Ƿ�������õĳɱ�
     * @return bool|mixed
     * @internal param $purchase
     */
    public function insertOrUpdateCostList($purchaseId, &$msg, $updateWarehouseCost = true) {
        $dbResult = false;
        if ($purchaseId > 0) {
            $sql = <<<QUERY_PURCHASE
                    SELECT R.ID AS REQ_ID,
                           R.STATUS AS REQ_STATUS,
                           R.USER_ID,
                           R.CASE_ID,
                           R.PRJ_ID,
                           L.CONTRACT_ID,
                           L.ID AS DETAIL_ID,
                           L.STATUS AS DETAIL_STATUS,
                           C.SCALETYPE,
                           C.PROJECT_ID,
                           L.CITY_ID,
                           L.IS_FUNDPOOL,
                           L.IS_KF,
                           L.FEE_ID,
                           L.APPLY_USER_ID,
                           L.NUM,
                           L.PRICE,
                           L.TYPE,
                           D.DEPTID
                    FROM ERP_PURCHASE_LIST L
                    LEFT JOIN ERP_PURCHASE_REQUISITION R ON R.ID = L.PR_ID
                    LEFT JOIN ERP_CASE C ON C.ID = R.CASE_ID
                    LEFT JOIN ERP_USERS D ON D.ID = L.APPLY_USER_ID
                    WHERE L.ID = %d
QUERY_PURCHASE;

            $dbPurchase = D()->query(sprintf($sql, $purchaseId));
            if (notEmptyArray($dbPurchase)) {
                $dbPurchase = $dbPurchase[0];
                if ($updateWarehouseCost) {
                    $dbResult = $this->updateWarehousePurchaseCost($dbPurchase);  // ������óɱ����������ݿ�
                } else {
                    $dbResult = true;
                }

                if ($dbResult !== false) {  // �Թ���Ĵ���
                    if ($dbPurchase['NUM'] > 0) {
                        $fee = intval($dbPurchase['NUM']) * floatval($dbPurchase['PRICE']);  // �������
                        $dbFoundCnt = D('ProjectCost')->where("ORG_EXPEND_ID = {$purchaseId} AND EXPEND_FROM = 2 AND ORG_ENTITY_ID = {$dbPurchase['REQ_ID']} AND STATUS = 2")->count();
                        if ($dbFoundCnt > 0) {
                            $data['FEE'] = $fee;
                            $data['OCCUR_TIME'] = date('Y-m-d H:i:s');  // ����ʱ�� �����
                            $dbResult = D('ProjectCost')->where("ORG_EXPEND_ID = {$purchaseId} AND STATUS = 2")->save($data);
                        } else {
                            $data['CASE_ID'] = $dbPurchase['CASE_ID'];  //������� �����
                            $data['CASE_TYPE'] = $dbPurchase['SCALETYPE'];  // ��Ŀ����
                            $data['ENTITY_ID'] = $dbPurchase['REQ_ID'];  // ҵ��ʵ���� �����
                            $data['EXPEND_ID'] = $dbPurchase['DETAIL_ID'];  // �ɱ���ϸ��� �����
                            $data['ORG_ENTITY_ID'] = $dbPurchase['REQ_ID'];  // ҵ��ʵ���� �����
                            $data['ORG_EXPEND_ID'] = $dbPurchase['DETAIL_ID'];
                            $data['FEE'] = $fee;  // �ɱ���� �����
                            $data['ADD_UID'] = $_SESSION['uinfo']['uid'];  //�����û���� �����
                            $data['OCCUR_TIME'] = date('Y-m-d H:i:s');  //����ʱ�� �����
                            $data['ISFUNDPOOL'] = $dbPurchase['IS_FUNDPOOL'];  // �Ƿ��ʽ�أ�0��1�ǣ� �����
                            $data['ISKF'] = $dbPurchase['IS_KF'];  // �Ƿ�۷� �����
                            $data['FEE_REMARK'] = '�ɹ�����ɹ�'; //�������� ��ѡ�
                            $data['INPUT_TAX'] = 0; // ����˰ ��ѡ�
                            $data['FEE_ID'] = $dbPurchase['FEE_ID']; // �ɱ�����ID �����
                            $data['EXPEND_FROM'] = 2; // ԭ�����ɹ���ͬǩ�������ڴ���ɹ�����ɹ���
                            $data['STATUS'] = 2;  //
                            $data['PROJECT_ID'] = $dbPurchase['PROJECT_ID'];
                            $data['USER_ID'] = $dbPurchase['APPLY_USER_ID'];
                            $data['DEPT_ID'] = $dbPurchase['DEPTID'];
                            $data['CITY_ID'] = $dbPurchase['CITY_ID'];
                            $data['TYPE'] = 1;  // �ɱ�����Ϊ�ɹ�
                            $dbResult = D('ProjectCost')->add_cost_info($data);
                        }
                    } else {
                        // û�й���ʱɾ���ɱ���Ӧ�ĳɱ�
                        $dbResult = D('ProjectCost')->where("ORG_EXPEND_ID = {$purchaseId} AND EXPEND_FROM = 2 AND ORG_ENTITY_ID = {$dbPurchase['REQ_ID']} AND STATUS = 2")->delete();
                    }
                }
            }

        }
        return $dbResult;
    }

    /**
     * ������õĲɹ��ɱ�
     * @param $dbPurchase array ���ݿ��ѱ��������
     * @return bool
     */
    private function updateWarehousePurchaseCost($dbPurchase) {
        // ��ѯ�Ƿ������Ӧ�ĳɱ���¼
        $warehouseCost = D('PurchaseList')->getWarehouseCost($dbPurchase['DETAIL_ID'], $dbPurchase['REQ_ID']);
        $warehouseUsage = D('PurchaseList')->getWarehouseUsage($dbPurchase['DETAIL_ID']);
        $dbResult = $this->updateWarehouseCost($warehouseUsage, $warehouseCost, $dbPurchase);

        return $dbResult;
    }

    /**
     * ���²ɹ����óɱ�
     * @param $warehouseUsage array ���ñ��м�¼���������
     * @param $warehouseCost array �ɱ����м�¼���������
     * @param $dbPurchase
     * @return bool
     */
    private function updateWarehouseCost($warehouseUsage, $warehouseCost, $dbPurchase) {

        $dbResult = false;
        if ($warehouseUsage['total_num'] > 0) {  // �����������ϸ����������

            $data['FEE'] = intval($warehouseUsage['warehouse_total_num']) * floatval($warehouseUsage['price'])
                + intval($warehouseUsage['displace_ware_total_num']) * floatval($warehouseUsage['price']) * self::USE_PERCENT;  //�ɱ����ڿ������ + �û��ֿ�����

            if ($warehouseCost['status']) {  // �����³ɱ����ɱ�������ڸ����ɹ���ϸ
                $data['OCCUR_TIME'] = date('Y-m-d H:i:s');
                if (floatval($data['FEE']) != floatval($warehouseCost['fee'])) {  // ���ݿⱣ��۸������õ��۸�һ��ʱ������
                    $dbResult = D('ProjectCost')->where("ID = {$warehouseCost['id']}")->save($data);
                } else {
                    $dbResult = true;
                }
            } else {  // ��������ɱ����ɱ����ﲻ���ڸ����ɹ���ϸ�Ĳɹ�����
                $data['CASE_ID'] = $dbPurchase['CASE_ID'];  //������� �����
                $data['CASE_TYPE'] = $dbPurchase['SCALETYPE'];  // ��Ŀ����
                $data['ENTITY_ID'] = $dbPurchase['REQ_ID'];  // ҵ��ʵ���� �����
                $data['EXPEND_ID'] = $dbPurchase['DETAIL_ID'];  // �ɱ���ϸ��� �����
                $data['ORG_ENTITY_ID'] = $dbPurchase['REQ_ID'];  // ҵ��ʵ���� �����
                $data['ORG_EXPEND_ID'] = $dbPurchase['DETAIL_ID'];
                //$data['FEE'] = intval($warehouseUsage['total_num']) * floatval($warehouseUsage['price']);  // �ɱ���� �����
                $data['ADD_UID'] = $_SESSION['uinfo']['uid'];  //�����û���� �����
                $data['OCCUR_TIME'] = date('Y-m-d H:i:s');  //����ʱ�� �����
                $data['ISFUNDPOOL'] = $dbPurchase['IS_FUNDPOOL'];  // �Ƿ��ʽ�أ�0��1�ǣ� �����
                $data['ISKF'] = $dbPurchase['IS_KF'];  // �Ƿ�۷� �����

                if($warehouseUsage['displace_ware_total_num'])
                    $displaceWareRemark = '�û��ֿ�-';

                if($warehouseUsage['warehouse_total_num'])
                    $wareHouseRemark = '���ֿ�';

                $data['FEE_REMARK'] = "�ɹ�������{$displaceWareRemark}{$wareHouseRemark}����"; //�������� ��ѡ�

                $data['INPUT_TAX'] = empty($warehouseUsage['input_tax_rate']) ? 0 : $data['FEE'] * $warehouseUsage['input_tax_rate']; // ����˰ ��ѡ�
                $data['FEE_ID'] = $dbPurchase['FEE_ID']; // �ɱ�����ID �����
                $data['EXPEND_FROM'] = 4; // �ɹ�����������
                $data['STATUS'] = 4;  // �ѱ�����
                $data['PROJECT_ID'] = $dbPurchase['PROJECT_ID'];
                $data['USER_ID'] = $dbPurchase['APPLY_USER_ID'];
                $data['DEPT_ID'] = $dbPurchase['DEPTID'];
                $data['CITY_ID'] = $dbPurchase['CITY_ID'];
                $data['TYPE'] = 1;  // �ɱ�����Ϊ�ɹ�

                $dbResult = D('ProjectCost')->add($data);

            }
        } else {  // û����������
            if ($warehouseCost['status']) {
                $dbResult = D('ProjectCost')->where("ID = {$warehouseCost['id']}")->delete();
            } else {
                $dbResult = true;  // û�и��������ݿ���������Ϊtrue
            }
        }

        return $dbResult;
    }
}

/* End of file ProjectCostModel.class.php */