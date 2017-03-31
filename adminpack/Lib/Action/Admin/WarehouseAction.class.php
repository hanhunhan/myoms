<?php

/**
 * �ֿ���������
 *
 * @author liuhu
 */
class WarehouseAction extends ExtendAction{
    /**
     * �����������û��ֿ�����
     */
    const USE_FROM_DISPLACE_WAREHOUSE = 2;

    /**
     * �ɹ���ϸ��δ�ɹ�
     */
    const NOT_PURCHASED_STATUS = 0;

    /**
     * �ɹ���ϸ�Ѿ��ɹ�
     */
    const PURCHASED_STATUS = 1;

    /**
     * ȷ�����Ȩ��
     */
    const CONFIRM_TO_WAREHOUSE = 661;

    /**
     * ���Ȩ��
     */
    const SEND_BACK = 662;

    const STORAGE_SQL = <<<STORAGE_SQL
            SELECT ID,
                   BRAND,
                   MODEL,
                   PRODUCT_NAME,
                   IS_FROM,
                   to_char(ADDTIME,'YYYY-MM-DD') AS ADDTIME,
                   PRICE,
                   NUM,
                   USE_NUM,
                   STATUS
            FROM ERP_WAREHOUSE
            WHERE PRODUCT_NAME LIKE %s
              AND STATUS = %d
              AND CITY_ID = %d
              AND NUM > USE_NUM
            ORDER BY ID DESC
STORAGE_SQL;

    const DISPLACE_SQL = <<<DISPLACE_SQL
            SELECT A.ID,
                   A.BRAND,
                   A.MODEL,
                   A.PRODUCT_NAME,
                   A.PRICE,
                   A.NUM
            FROM ERP_DISPLACE_WAREHOUSE A
            LEFT JOIN ERP_DISPLACE_REQUISITION B
            ON A.DR_ID = B.ID
            WHERE A.PRODUCT_NAME LIKE %s
              AND A.INBOUND_STATUS = %d
              AND B.CITY_ID = %d
              AND��A.NUM > 0
            ORDER BY A.ID DESC
DISPLACE_SQL;

    const DISPLACE_PROJECTNAME_SQL = <<<DISPLACE_PROJECTNAME_SQL
            SELECT max(A.ID) as ID,
                   A.BRAND,
                   A.MODEL,
                   A.PRODUCT_NAME
            FROM ERP_DISPLACE_WAREHOUSE A
            LEFT JOIN ERP_DISPLACE_REQUISITION B
            ON A.DR_ID = B.ID
            WHERE A.PRODUCT_NAME LIKE %s
              AND B.CITY_ID = %d
              GROUP BY
                   A.BRAND,
                   A.MODEL,
                   A.PRODUCT_NAME
            ORDER BY ID DESC
DISPLACE_PROJECTNAME_SQL;


    
    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();
    
    /***TABҳǩ���***/
    private $_tab_number = 7;
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'confirm_to_warehouse' => self::CONFIRM_TO_WAREHOUSE,
            'send_back' => self::SEND_BACK
        );
        
        //TAB URL����
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? intval($_GET['prjid']) : 0;
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
        !empty($_GET['purchase_id']) ? $this->_merge_url_param['purchase_id'] =  intval($_GET['purchase_id']) : '';
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
    }
    
    
    /**
    +----------------------------------------------------------
    * �ֿ����
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function warehouse_manage()
    {   
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        
        Vendor('Oms.Form');
        
    	$form = new Form();
        $warehouse_model = D('Warehouse');
        
        //��Դ
        $from_arr = $warehouse_model->get_conf_from();
        
        //״̬����
        $status_arr = $warehouse_model->get_conf_status();
        
        //��ѯ�˿�δ��˵�����
        $cond_wehre = " STATUS = '".$status_arr['audited']."' AND CITY_ID = ".$this->channelid;
    	$form = $form->initForminfo(180)->where($cond_wehre);
        
    	//��Դ����
    	$from_arr_remark = $warehouse_model->get_conf_from_remark();
    	$form->setMyField('IS_FROM', 'LISTCHAR', array2listchar($from_arr_remark), FALSE);
    	
    	//״̬����
    	$status_arr_remark = $warehouse_model->get_conf_status_remark();
    	$form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr_remark), FALSE);
        
        /***��������ֶ�***/
        $form->setMyField('IS_KF', 'FORMVISIBLE', '0', FALSE);
        $form->setMyField('IS_FUNDPOOL', 'FORMVISIBLE', '0', FALSE);
        $form->setMyField('FEE_ID', 'FORMVISIBLE', '0', FALSE);
        $form->setMyField('INPUT_TAX', 'FORMVISIBLE', '0', FALSE);
        
        if($showForm > 0)
        {
            //��������
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
        }
        
    	$formHtml = $form->getResult();
    	$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
    	$tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ? 
    	$this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
    	$this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));
    	$this->display('warehouse_manage');
    }
    
    
    /**
    +----------------------------------------------------------
    * �˿����
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function returned_warehouse_manage()
    {   
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        
        Vendor('Oms.Form');
    	$form = new Form();
        
        $warehouse_model = D('Warehouse');
        
        //��Դ
        $from_arr = $warehouse_model->get_conf_from();
        
        //״̬����
        $status_arr = $warehouse_model->get_conf_status();
        
        //��ѯ�˿�δ��˵�����
        $cond_wehre = "IS_FROM = '".$from_arr['return_to_warehouse']."' "
                    . "AND STATUS = '".$status_arr['not_audit']."' AND CITY_ID = ".$this->channelid;
    	$form = $form->initForminfo(180)->where($cond_wehre);
        
    	//��Դ����
    	$from_arr_remark = $warehouse_model->get_conf_from_remark();
    	$form->setMyField('IS_FROM', 'LISTCHAR', array2listchar($from_arr_remark), FALSE);
    	
    	//״̬����
    	$status_arr_remark = $warehouse_model->get_conf_status_remark();
    	$form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr_remark), FALSE);
        
        if($showForm > 0)
        {
            //��������
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
        }
    	
    	/****���ò�����ť****/
    	$form->GABTN =  '<a id = "confirm_to_warehouse" href="javascript:;"  class="btn btn-info btn-sm">ȷ�����</a>';
        $form->GABTN .=  '<a id = "send_back" href="javascript:;" class="btn btn-info btn-sm">���</a>';

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
    	$formHtml = $form->getResult();
    	$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
    	$tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ? 
    	$this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
    	$this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));
    	$this->display('returned_warehouse_manage');
    }
    
    
    /**
     +----------------------------------------------------------
     * �����˿�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function return_to_warehouse()
    {
    	$result = array();
        
    	//�ɹ���ϸ���
    	$purchase_id = !empty($_GET['purchase_id']) ? $_GET['purchase_id'] : 0;
        $apply_back_num = !empty($_GET['apply_back_num']) ? $_GET['apply_back_num'] : 0;
        
    	if($purchase_id > 0 && $apply_back_num > 0)
    	{
            //�ɹ���ϸ���
            $purchase_list_model = D('PurchaseList');

            //��ѯ�ɹ���ϸ����(�ѱ�������)
            $purchase_list_info = array();
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchase_id);

            //ѭ����Ӳɹ���ϸ��Ϣ�������
            if(is_array($purchase_list_info) && !empty($purchase_list_info))
            {
                $warehouse_model = D('Warehouse');
                $from_arr = $warehouse_model->get_conf_from();
                $satus_arr = $warehouse_model->get_conf_status();

                //�ɹ���ϸ�Ƿ��Ѿ�������
                $purchase_status = $purchase_list_model->get_conf_list_status();
                if($purchase_list_info[0]['STATUS'] != $purchase_status['reimbursed'])
                {
                    $result['state']  = 0;
                    $result['msg']  = '�˿�����ʧ�ܣ��ѱ����Ĳɹ����������˿⡣';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                //�Ƿ��Ѿ��˿��ж�
                $conf_back_stock_status = $purchase_list_model->get_conf_back_stock_status();
                if($purchase_list_info[0]['BACK_STOCK_STATUS'] == $conf_back_stock_status['applied'])
                {
                    $result['state']  = 0;
                    $result['msg']  = '�˿�����ʧ�ܣ� ���������˿�Ĳɹ������ٴ������˿�';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                /***�ж������˿������Ƿ��Ѿ������ɹ�+��������***/
                //���˿�����
                $returned_num = $purchase_list_info[0]['STOCK_NUM'];

                $useWarehouse = D('WarehouseUse')->getSumnumByPurchaseId($purchase_id,1); //�����������
                $useDisplace = D('WarehouseUse')->getSumnumByPurchaseId($purchase_id,2); //�û�����������

                //�Ѳɹ�����������������
                $purchased_num = $useWarehouse + $purchase_list_info[0]['NUM'];


                //���ݲɹ���Ϣ��ѯ�����ɹ������Ľ��
                $reim_cost_price = 0;
                $reim_cost_info = array();
                $reim_detail_model = D('ReimbursementDetail');
                $cond_where = "CITY_ID = '".$this->channelid."' AND "
                        . " CASE_ID = '".$purchase_list_info[0]['CASE_ID']."' AND "
                        . " BUSINESS_PARENT_ID = '".$purchase_list_info[0]['PR_ID']."' AND "
                        . " BUSINESS_ID = '".$purchase_list_info[0]['ID']."' AND STATUS = 1 ";
                $reim_cost_info = $reim_detail_model->get_detail_info_by_cond($cond_where, array('MONEY'));

                //���ڲɹ���ʱ�򣬱������ݱ������
                if(empty($reim_cost_info) && $purchase_list_info[0]['NUM']>0)
                {
                    $result['state']  = 0;
                    $result['msg']  = '�˿�����ʧ�ܣ� δ�鵽�ɹ���������';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit; 
                }
                $reim_cost_price = floatval($reim_cost_info[0]['MONEY']);
                //�ɹ��ܳɱ�(�����ܽ��+�����ܽ��)

                $useTotalPrice = $purchase_list_info[0]['USE_TOATL_PRICE']; //ʹ�����ý��
                $useTotalPrice = ($useWarehouse/($useWarehouse+$useDisplace)) * $useTotalPrice;  //��ȡ������ʹ�ý�� ���û������ˣ�

                $total_price = $reim_cost_price + $useTotalPrice;
                //���������˿�����
                $back_num_enable = $purchased_num - $returned_num;
                
                if($back_num_enable < $apply_back_num)
                {
                    $result['state']  = 0;
                    $result['msg']  = '�˿�����ʧ�ܣ������˿�����̫�ࡣ<br>'
                        . '�����˿��������ô��ڣ��ɹ����� + ������������ - ���˿�������<br>'
                        . '�û��ֿ�����������<font color="red">' . $useDisplace . '</font> ��������������<font color="red">' . $useWarehouse . '</font><br>'
                        . '���û��ֿ����õ���Ʒ���������˿����!';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                //�˿���Ϣ
                $purchase_info = array();
                $purchase_info['PL_ID'] = $purchase_list_info[0]['ID'];
                $purchase_info['BRAND'] = $purchase_list_info[0]['BRAND'];
                $purchase_info['MODEL'] = $purchase_list_info[0]['MODEL'];
                $purchase_info['PRODUCT_NAME'] = $purchase_list_info[0]['PRODUCT_NAME'];
                $purchase_info['FEE_ID'] = $purchase_list_info[0]['FEE_ID'];
                $purchase_info['IS_KF'] = $purchase_list_info[0]['IS_KF'];
                $purchase_info['INPUT_TAX'] = $purchase_list_info[0]['INPUT_TAX'];
                $purchase_info['IS_FUNDPOOL'] = $purchase_list_info[0]['IS_FUNDPOOL'];
                $purchase_info['ADDTIME'] = date('Y-m-d H:i:s');
                $purchase_info['IS_FROM'] = intval($from_arr['return_to_warehouse']);
                $purchase_info['STATUS'] = intval($satus_arr['not_audit']);
                $purchase_info['CITY_ID'] = $purchase_list_info[0]['CITY_ID'];

                $isert_id_high_price = 0;
                //���ݹ�������˿�۸�
                $return_price = self::_get_return_price($total_price, $purchased_num);

                if($return_price <= 0)
                {
                    $result['state']  = 0;
                    $result['msg']  = '�˿�����ʧ�ܣ��˿�۸��쳣';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                //������ʣ������ȫ���˿��ʱ������˿�ۼ۸�Ϊ��������õ�����洢�����˿���Ϣ�����һ������ߵ��ۣ�
                if($back_num_enable == $apply_back_num)
                {   
                    if($total_price / $purchased_num != $return_price)
                    {   
                        $high_price = $total_price - ($purchased_num - 1 ) * $return_price;
                        $purchase_info['PRICE'] = $high_price;
                        $purchase_info['NUM'] = 1;

                        //��������Ϣ
                        $isert_id_high_price = $warehouse_model->return_to_warehouse($purchase_info);

                        $apply_back_num = $apply_back_num -1;
                    }
                }

                if($apply_back_num > 0)
                {
                    $purchase_info['PRICE'] = $return_price;
                    $purchase_info['NUM'] = $apply_back_num;

                    //��������Ϣ
                    $isert_id = $warehouse_model->return_to_warehouse($purchase_info);
                }

                if($isert_id_high_price > 0 || ($apply_back_num > 0 && $isert_id > 0))
                {   
                    //�˿�������
                    $update_num = $purchase_list_model->update_to_apply_back_stock_by_id($purchase_id);

                    $result['state']  = 1;
                    $result['msg']  = '�˿�������ӳɹ����ȴ�ȷ���˿�';
                }
                else
                {
                    $result['state']  = 0;
                    $result['msg']  = '�˿��������ʧ��';
                }
            }
            else
            {
                $result['state']  = 0;
                $result['msg']  = 'û��ѯ����������������';
            }
    	}
    	else
    	{
            $result['state']  = 0;
            $result['msg']  = '�˿�����ʧ�ܣ�����ѡ��һ���ɹ���ϸ�����˿���������0';
    	}
    
        $result['msg'] = g2u($result['msg']);
        echo json_encode($result);
    }
    
    
    /**
     +----------------------------------------------------------
     * ��ȡ�˿�۸�
     +----------------------------------------------------------
     * @param float $total_price �ɹ��ܳɱ�
     * @param int $total_num �ɹ�������
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    private function _get_return_price($total_price , $total_num)
    {	
        $return_price = 0;

        if($total_price % $total_num == 0)
        {
            $return_price = $total_price / $total_num;
        }
        else
        {
            $unit_price = round($total_price /  $total_num , 2);
            $remain_price = $total_price - ($total_num - 1) * $unit_price;
            $return_price = $unit_price > $remain_price ? $remain_price : $unit_price;
        }

        return $return_price;
    }
    
    
    /**
     +----------------------------------------------------------
     * ȷ���˿����
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function confirm_to_warehouse()
    {	
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$result = array();
    	
    	//�˿���ϸ���
    	$warehouse_id = !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : array();
        
    	if(!empty($warehouse_id))
    	{   
            /**�����˿�����**/
    		$warehouse_model = D('Warehouse');
    		$update_num = $warehouse_model->confirm_to_warehouse($warehouse_id);
            
            /**��ѯ����ȷ���˿����Ϣ**/
            $search_field = array('ID', 'PL_ID', 'NUM', 'PRICE', 'IS_FROM', 'STATUS');
            $warehouse_info = $warehouse_model->get_product_info_by_ids($warehouse_id, $search_field);
            
            if(is_array($warehouse_info) && !empty($warehouse_info))
            {   
                $purchase_list_model = D('PurchaseList');
                $cost_model = D('ProjectCost');
                
                //�ɹ���Ϣ��Ҫ��ѯ�ֶ�
                $purchase_serach_filed = array('ID', 'CASE_ID', 'PR_ID', 'NUM', 
                								'PRICE', 'IS_KF', 'IS_FUNDPOOL', 'FEE_ID');
                
                foreach($warehouse_info as $key => $value)
                {   
                	if($value['PL_ID'] > 0)
                	{
	                    //��ǰ�ɹ��Ƿ���δȷ���˿������
	                    $not_confirm_num = $warehouse_model->get_not_confrim_num_by_pl_id($value['PL_ID']);
	                    
                        //�����������ͬʱ���²ɹ��˿��������˿�״̬
	                    if($not_confirm_num == 0)
	                    {
	                        $update_purchase = 
	                            $purchase_list_model->update_stock_num_by_id($value['PL_ID'], $value['NUM']);
	                    }
	                    else
	                    {   
                            //���������ֻ��������
	                        $update_arr['STOCK_NUM'] =  $value['NUM'];
	                        $update_purchase = 
	                        $purchase_list_model->update_purchase_list_by_id($value['PL_ID'], $update_arr);
	                    }
	                    
	                    //ȷ���˿�����ٲɹ��ɱ�
	                    if($update_purchase > 0)
	                    {	
	                    	/***ͨ���ɹ���ϸ��Ż�ȡ�ɹ���ϸ��Ϣ***/
	                    	$purchase_info = $purchase_list_model->get_purchase_list_by_id($value['PL_ID'], $purchase_serach_filed);
							
	                    	if(is_array($purchase_info) && !empty($purchase_info))
	                    	{
			                    $cost_info = array();
			                    $cost_info['CASE_ID'] = $purchase_info[0]['CASE_ID'];
			                    $cost_info['ENTITY_ID'] = $purchase_info[0]['PR_ID'];
			                    $cost_info['EXPEND_ID'] = $value['PL_ID'];
			                    $cost_info['ORG_ENTITY_ID'] = $purchase_info[0]['PR_ID'];
			                    $cost_info['ORG_EXPEND_ID'] = $value['PL_ID'];
			                    $cost_info['EXPEND_FROM'] = 27;//�ɹ��˿�
			                    $cost_info['FEE'] = - $value['NUM'] * $value['PRICE'];
			                    $cost_info['FEE_REMARK'] = '�����˿�ͨ��';
			                    $cost_info['ADD_UID'] = $uid;
			                    $cost_info['OCCUR_TIME'] = date('Y-m-d H:i:s');
			                    $cost_info['ISKF'] = $purchase_info[0]['IS_KF'];
			                    $cost_info['ISFUNDPOOL'] = $purchase_info[0]['IS_FUNDPOOL'];
			                    $cost_info['FEE_ID'] = $purchase_info[0]['FEE_ID'];
			                    $add_result = $cost_model->add_cost_info($cost_info);
	                    	}
	                    }
                	}
                }
            }
            else
            {
            	$result['state']  = 0;
    			$result['msg']  = '�˿�ʧ�ܣ���ѡ��ȷ��������Ϣ';
            }
    		
    	    if($update_num > 0)
    		{   
                //���²ɹ���ϸ�˿�����
    			$result['state']  = 1;
    			$result['msg']  = '�˿�ɹ�';
    		}
    		else
    		{
    			$result['state']  = 0;
    			$result['msg']  = '�˿�ʧ��';
    		}
    	}
    	else
    	{
    		$result['state']  = 0;
    		$result['msg']  = 'û��ѯ����������������';
    	}
    	
    	$result['msg'] = g2u($result['msg']);
    	echo json_encode($result);
    }
    
    /**
     +----------------------------------------------------------
     * ����˿�����
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function application_send_back()
    {
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$result = array();
    	
    	$warehouse_model = D('Warehouse');
    	
    	//�˿���ϸ���
    	$warehouse_id = !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : array();
    
    	if(!empty($warehouse_id))
    	{
    		/**�����˿���Ϣ�����������¼**/
    		$search_field = array('PL_ID', 'STATUS');
    		$warehouse_info = $warehouse_model->get_product_info_by_ids($warehouse_id, $search_field);
    
    		if(is_array($warehouse_info) && !empty($warehouse_info))
    		{
    			$purchase_list_model = D('PurchaseList');
    			
    			$conf_back_status = $warehouse_model->get_conf_status();
    			$arr_list_id = array();
    			foreach($warehouse_info as $key => $value)
    			{
    				if($value['PL_ID'] > 0 && $value['STATUS'] == $conf_back_status['not_audit'])
    				{
    					$arr_list_id[$key] = $value['PL_ID'];
    				}
    			}
				
    			//���²ɹ�
    			$update_purchase = $purchase_list_model->update_apply_send_back_by_id($arr_list_id);
    			
    			//�����˿������¼
    			$update_num = $warehouse_model->application_send_back($warehouse_id);
    			
    			if($update_num > 0 && $update_purchase)
    			{
    				//���²ɹ���ϸ�˿�����
    				$result['state']  = 1;
    				$result['msg']  = '��سɹ�';
    			}
    			else
    			{
    				$result['state']  = 0;
    				$result['msg']  = '���ʧ��';
    			}
    		}
    		else
    		{
    			$result['state']  = 0;
    			$result['msg']  = 'û��ѯ����������������';
    		}
    	}
    	else
    	{
    		$result['state']  = 0;
    		$result['msg']  = '���ʧ�ܣ���ѡ����Ϣ';
    	}
    
    	$result['msg'] = g2u($result['msg']);
    	echo json_encode($result);
    }
    
    
    /**
     +----------------------------------------------------------
     * �첽�ӿ�����
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function ajax_get_warehouse_num()
    {   
        $total_num = 0;
        
        $brand = u2g($_GET['brand']);
        $model = u2g($_GET['model']);
        $product_name = u2g($_GET['product_name']);
        //����޼�
        $price_limit = floatval($_GET['price_limit']);
        //����
        $city_id = $this->channelid;
        
        $warehouse_model = D('Warehouse');
        // ��ȡ�ɹ��ֿ��еĿ����
        $total_num = $warehouse_model->get_total_num_by_name($brand, $model, $product_name, $price_limit, $city_id);
        // ��ȡ�û��ֿ��еĿ����
        $displace_total_num = D('Displace')->getTotalNumByName(array(
            'brand' => $brand,
            'model' => $model,
            'name' => $product_name
        ), $price_limit, $city_id);
        
        if($total_num > 0 or $displace_total_num > 0)
        {
            $result['state']  = 1;
            $result['total_num'] = intval($total_num);
            $result['displace_total_num'] = intval($displace_total_num);
        }
        else
        {
            $result['state']  = 0;
            $result['total_num'] = 0;
            $result['displace_total_num'] = 0;
        }
        
    	echo json_encode($result);
    }

    /**
     * �Ӳɹ��ֿ������ã��ֿ��д����Ʒ�ĵط����ܲ�ֹһ�����ʿ��ܷ����Ӳֿ��еĲ�ͬID������
     * @param $purchase array �ɹ���Ϣ
     * @param $remainNeedAmount int �����Ҫ���õ�����
     * @param array $warehouseUse
     * @return bool
     */
    private function useFromPurchaseWarehouse($purchase, $remainNeedAmount, &$warehouseUse = array()) {
        if (intval($remainNeedAmount) === 0) {
            return true;
        }

        //����������
        $useAmount = 0;
        //������Ʒ�ܽ��
        $useTotalMoney = 0;
        //��ƷƷ��
        $brand = $purchase['BRAND'];
        //��Ʒ�ͺ�
        $model = $purchase['MODEL'];
        //��Ʒ����
        $productName = $purchase['PRODUCT_NAME'];
        //����޼�
        $priceLimit = $purchase['PRICE_LIMIT'];
        $purchaseListId = $purchase['ID'];

        /***�ֿ�MODEL***/
        $warehouseModel = D('Warehouse');
        /***�������MODEL***/
        $warehouseUseModel = D('WarehouseUse');
        // ״̬
        $use_status = $warehouseUseModel->get_conf_status();

        while($remainNeedAmount > $useAmount) {
            //��ѯ����ķ������������Ŀ���¼
            $wareHouseProductList =
                $warehouseModel->get_earliest_puroduct_info_by_search_key($brand, $model, $productName, $priceLimit, $this->channelid);
            // ȡ���ݵĹ����г��ִ���
            if ($wareHouseProductList === false) {
                return false;
            }

            if (notEmptyArray($wareHouseProductList)) {
                $wareHouseProduct = $wareHouseProductList[0];  // ��ȡһ����Ʒ��Ϣ
                // �ֿ���ĳ��ID�¿������õ�����
                $enable_use_num = $wareHouseProduct['NUM'] - $wareHouseProduct['USE_NUM'];
                // ������������ʣ�������
                $need_use_num = $remainNeedAmount - $useAmount;
                //������������
                $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;

                $used_info = array();
                //�������õĲɹ���ϸ���
                $used_info['PL_ID'] = $purchaseListId;
                //������Ʒ�����
                $used_info['WH_ID'] = $wareHouseProduct['ID'];
                //������Ʒ��浥��
                $used_info['USE_PRICE'] = $wareHouseProduct['PRICE'];
                //��������
                $used_info['USE_NUM'] = $used_num_this_time;
                //����ʱ��
                $used_info['USE_TIME'] = date('Y-m-d H:i:s');
                //״̬
                $used_info['STATUS'] = $use_status['not_confirm'];
                //�������ù�ϵ������
                $insert_id = $warehouseUseModel->add_used_info($used_info);
                //���¿������
                $up_num = $warehouseModel->update_warehouse_use_num($wareHouseProduct['ID'], $used_num_this_time);
                //����������
                if ($insert_id > 0 && $up_num > 0) {
                    $useAmount += $used_num_this_time;
                    //���������ܽ��
                    $useTotalMoney += $used_num_this_time * $wareHouseProduct['PRICE'];
                } else {
                    return false;
                }
            } else {
                $warehouseUse['amount'] = $useAmount;
                $warehouseUse['total_money'] = $useTotalMoney;
                return true;
            }
        }

        $warehouseUse['amount'] = $useAmount;
        $warehouseUse['total_money'] = $useTotalMoney;
        return true;
    }

    /**
     * ���û��ֿ�������
     * @param $purchase array �ɹ���Ϣ
     * @param $remainNeedAmount int �����Ҫ���õ�����
     * @param array $warehouseUse
     * @return bool
     */
    private function useFromDisplaceWarehouse($purchase, $remainNeedAmount, &$warehouseUse = array()) {
        if (intval($remainNeedAmount) === 0) {
            return true;
        }

        //����������
        $useAmount = 0;
        //������Ʒ�ܽ��
        $useTotalMoney = 0;
        //��ƷƷ��
        $brand = $purchase['BRAND'];
        //��Ʒ�ͺ�
        $model = $purchase['MODEL'];
        //��Ʒ����
        $productName = $purchase['PRODUCT_NAME'];
        //����޼�
        $priceLimit = $purchase['PRICE_LIMIT'];
        $purchaseListId = $purchase['ID'];

        /***�ֿ�MODEL***/
        $warehouseModel = D('Warehouse');
        /***�������MODEL***/
        $warehouseUseModel = D('WarehouseUse');
        // ״̬
        $use_status = $warehouseUseModel->get_conf_status();

        while($remainNeedAmount > $useAmount) {
            //��ѯ����ķ������������Ŀ���¼
            $warehouseProductList = D('Displace')->getDisplaceWarehouseProduct(array(
                'brand' => $brand,
                'model' => $model,
                'name' => $productName
            ), $priceLimit, $this->channelid);
            // ȡ���ݵĹ����г��ִ���
            if ($warehouseProductList === false) {
                return false;
            }

            if (notEmptyArray($warehouseProductList)) {
                // �ֿ���ĳ��ID�¿������õ�����
                $warehouseProduct = $warehouseProductList[0];
                $enable_use_num = $warehouseProduct['NUM'];
                // ������������ʣ�������
                $need_use_num = $remainNeedAmount - $useAmount;
                //������������
                $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;

                $used_info = array();
                //�������õĲɹ���ϸ���
                $used_info['PL_ID'] = $purchaseListId;
                //������Ʒ�����
                $used_info['WH_ID'] = $warehouseProduct['ID'];
                //������Ʒ��浥��
                $used_info['USE_PRICE'] = $warehouseProduct['PRICE'];
                //��������
                $used_info['USE_NUM'] = $used_num_this_time;
                //����ʱ��
                $used_info['USE_TIME'] = date('Y-m-d H:i:s');
                //״̬
                $used_info['STATUS'] = $use_status['not_confirm'];
                $used_info['TYPE'] = self::USE_FROM_DISPLACE_WAREHOUSE;
                //�������ù�ϵ������
                $insert_id = $warehouseUseModel->add_used_info($used_info);
                //���¿������
                $up_num = D('Displace')->updateWarehouseUseNum($warehouseProduct['ID'], $used_num_this_time);
                //����������
                if ($insert_id > 0 && $up_num > 0) {
                    $useAmount += $used_num_this_time;
                    //���������ܽ��
                    $useTotalMoney += $used_num_this_time * $warehouseProduct['PRICE'];
                } else {
                    return false;
                }
            } else {
                $warehouseUse['amount'] = $useAmount;
                $warehouseUse['total_money'] = $useTotalMoney;
                return true;
            }
        }

        $warehouseUse['amount'] = $useAmount;
        $warehouseUse['total_money'] = $useTotalMoney;
        return true;
    }

    /**
     * �ֿ����û��˿����֮��Ĳ���
     * @param array $warehouseUsage
     * @param int $purchaseStatus
     * @param string $msg
     * @return bool
     */
    private function updatePurchaseAfterWarehouseOperate($warehouseUsage = array(), &$purchaseStatus = 0, &$msg = '') {
        $purchaseListId = $warehouseUsage['purchase_list_id'];
        $usedNum = $warehouseUsage['used_num'];
        $usedTotalMoney = $warehouseUsage['used_total_money'];
        if (intval($usedNum) === 0) {
            $msg = '���û��˿�����Ϊ0��������²ɹ���ϸ';
            return true;
        }

        if (abs($usedNum) > 0) {
            /***���²ɹ���ϸ����������������Ʒ�ܽ��***/
            $purchaseListModel = D('PurchaseList');
            $update_arr = array();
            $update_arr['USE_NUM'] = array('exp', "USE_NUM + " . $usedNum);
            $update_arr['USE_TOATL_PRICE'] = array('exp', "USE_TOATL_PRICE + " . $usedTotalMoney);
            $dbResult = $purchaseListModel->update_purchase_list_by_id($purchaseListId, $update_arr);

            if ($dbResult === false) {
                // ���²ɹ���ϸʧ��
                $msg = '�������ڲ�����';
                return false;
            }

            //��������ɹ���ϸ
            $updatedPurchaseList = $purchaseListModel->get_purchase_list_by_id($purchaseListId);
            if ($updatedPurchaseList === false) {
                // ��ȡ���ݿ����ݴ���
                $msg = '�������ڲ�����';
                return false;
            }

            // ���²ɹ������
            $updateData = array();
            $purchaseStatus = (intval($updatedPurchaseList[0]['USE_NUM']) == 0 &&
                intval($updatedPurchaseList[0]['NUM']) == 0
            ) ? self::NOT_PURCHASED_STATUS : self::PURCHASED_STATUS;
            $updateData['STATUS'] = $purchaseStatus;
            if (empty($updatedPurchaseList[0]['COST_OCCUR_TIME'])) {
                // ��¼���÷���ʱ��
                $updateData['COST_OCCUR_TIME'] = date('Y-m-d H:i:s');
            }
            if (empty($updatedPurchaseList[0]['PURCHASE_OCCUR_TIME'])) {
                // ��¼����¼��ʱ��
                $updateData['PURCHASE_OCCUR_TIME'] = date('Y-m-d H:i:s');
            }
            $dbResult = D('PurchaseList')->where("id = {$purchaseListId}")->save($updateData);
            if ($dbResult === false) {
                $msg = '�������ڲ�����';
                return false;
            }

            if (D('PurchaseList')->is_all_purchased($updatedPurchaseList[0]['PR_ID'])) {
                // �ɹ���ϸȫ���ɹ���������òɹ����뵥Ϊ�ɹ����״̬
                $dbResult = D('PurchaseRequisition')->where("ID = {$updatedPurchaseList[0]['PR_ID']}")->save(array('STATUS' => 4));
            } else {
                // �������òɹ����뵥Ϊ����ͨ��״̬
                $dbResult = D('PurchaseRequisition')->where("ID = {$updatedPurchaseList[0]['PR_ID']}")->save(array('STATUS' => 2));
            }

            if ($dbResult === false) {
                $msg = '�������ڲ�����';
                return false;
            }
        } else {
            if ($usedNum > 0) {
                $msg = '����ʧ��';
            } else {
                $msg = '�˿�ʧ��';
            }

            return false;
        }
    }

    /**
     * ���ò���
     * @param $purchasedProduct
     * @param $applyNum
     * @param array $warehouseUsage
     * @param array $response
     * @return bool
     * @internal param $remainNeedAmount
     */
    private function useFromWarehouse($purchasedProduct, $applyNum, &$warehouseUsage = array(), &$response = array()) {
        if (intval($applyNum) === 0) {
            $response['state'] = 1;
            $response['msg'] = '��������Ϊ0����������';
            return true;
        }

        // �ɹ��ֿ��������
        $purchaseWarehouseUse = array(
            'amount' => 0,
            'total_money' => 0
        );

        // �û��ֿ��������
        $displaceWarehouseUse = array(
            'amount' => 0,
            'total_money' => 0
        );
        $purchaseListId = $purchasedProduct['ID'];  // ����ɹ�ID
        $purchaseWarehouseStatus = $this->useFromPurchaseWarehouse($purchasedProduct, $applyNum, $purchaseWarehouseUse);
        if ($purchaseWarehouseStatus === false) {
            $response['state'] = 0;
            $response['msg'] = '�Ӳɹ��ֿ�����ʧ��';
            return false;
        }

        // ���βɹ��ֿ���û���쵽�㹻�����Ʒ����������û��ֿ�����ȡ
        $remainApplyNum = $applyNum - $purchaseWarehouseUse['amount'];
        if ($remainApplyNum > 0) {
            $displaceWarehouseStatus = $this->useFromDisplaceWarehouse($purchasedProduct, $remainApplyNum, $displaceWarehouseUse);
        } else {
            $displaceWarehouseStatus = true;
        }

        if ($displaceWarehouseStatus === false) {
            $response['state'] = 0;
            $response['msg'] = '���û��ֿ�����ʧ��';
            return false;
        }

        // ���óɹ�
        $warehouseUsage['purchase'] = $purchaseWarehouseUse;
        $warehouseUsage['displace'] = $displaceWarehouseUse;
        $usedNum = $purchaseWarehouseUse['amount'] + $displaceWarehouseUse['amount'];
        $usedTotalMoney = $purchaseWarehouseUse['total_money'] + $displaceWarehouseUse['total_money'];
        $updateStatusAfterUse = $this->updatePurchaseAfterWarehouseOperate(array(
            'purchase_list_id' => $purchaseListId,
            'used_num' => $usedNum,
            'used_total_money' => $usedTotalMoney
        ), $purchaseStatus, $msg);

        if ($updateStatusAfterUse === false) {
            $response['state'] = 0;
            $response['msg'] = '����ʧ��';
            return false;
        }

        $response['state'] = 1;
        $response['msg'] = sprintf('���óɹ����Ӳɹ��ֿ�������%d�������û��ֿ�������%d����', $purchaseWarehouseUse['amount'], $displaceWarehouseUse['amount']);
        $response['purchase_status'] = $purchaseStatus;
        return true;
    }

    /**
     * �������õ���Ʒ�����ɹ����û��ֿ���
     * @param $revertNum int �˿������
     * @param $purchaseListId int �ɹ���ID
     * @param $usageReverted array �˿�����
     * @param $response
     * @return bool
     */
    private function revert2Warehouse($revertNum, $purchaseListId, $usageReverted, &$response) {
        $purchaseWarehouseModel = D('Warehouse'); // �ɹ��ֿ�MODEL
        $displaceWarehouseModel = D('Displace'); // �û��ֿ�MODEL
        $warehouseUseModel = D('WarehouseUse'); // �������MODEL
        $usedNum = 0; // ������������
        $useTotalMoney = 0; // �����ܽ��
        $absRevertNum = abs($revertNum);
        while ($usedNum < $absRevertNum) {
            //��ѯ������õ���Ʒ
            $usageInfo = $warehouseUseModel->get_last_use_info_by_purchase_id($purchaseListId);
            // ��ȡʹ����Ϣʧ��ʱ���˿�ʧ��
            if ($usageInfo === false) {
                return false;
            }

            $warehouseType = $usageInfo['TYPE'];  // �ֿ����ͣ�1=�ɹ��ֿ⣬2=�û��ֿ�
            if (notEmptyArray($usageInfo)) {
                $enable_use_num = $usageInfo['USE_NUM']; // �������������
                $need_use_num = $absRevertNum - $usedNum;  // ����Ҫ���������
                $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;  // ������������
                if ($used_num_this_time >= $enable_use_num) { // ���»���ɾ��������ϸ
                    //ɾ������������ϸ
                    $update_use_info = $warehouseUseModel->del_use_info_by_id($usageInfo['ID']);
                } else {
                    //���±���������ϸ��Ϣ
                    $update_arr = array();
                    $update_arr['USE_NUM'] = array('exp', "USE_NUM - " . $used_num_this_time);
                    $update_use_info = $warehouseUseModel->update_info_by_id($usageInfo['ID'], $update_arr);
                }

                //�˻زֿ⣨���¿�������
                if ($warehouseType == 1) {
                    $up_num = $purchaseWarehouseModel->update_warehouse_use_num($usageInfo['WH_ID'], -$used_num_this_time);
                } else if ($warehouseType == 2) {
                    $up_num = $displaceWarehouseModel->updateWarehouseUseNum($usageInfo['WH_ID'], -$used_num_this_time);
                }


                //����������
                if ($update_use_info > 0 && $up_num > 0) {
                    $usedNum += $used_num_this_time;
                    $useTotalMoney += -($used_num_this_time * $usageInfo['USE_PRICE']); // ���������ܽ��
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        $usedNum = $usedNum > 0 ? -$usedNum : 0;
        $usageReverted['amount'] = $usedNum;
        $usageReverted['total_money'] = $useTotalMoney;

        $updateStatusAfterUse = $this->updatePurchaseAfterWarehouseOperate(array(
            'purchase_list_id' => $purchaseListId,
            'used_num' => $usedNum,
            'used_total_money' => $useTotalMoney
        ), $purchaseStatus, $msg);

        if ($updateStatusAfterUse === false) {
            $response['state'] = 0;
            $response['msg'] = empty($msg) ? '�˿�ʧ��' : $msg;
        }

        $response['state'] = 1;
        $response['msg'] = '�˿�ɹ������˻زֿ�' . abs($usedNum) . '��';
        $response['purchase_status'] = $purchaseStatus;
        return true;
    }
    
    /**
     +----------------------------------------------------------
     * �첽�Ӳֿ�������Ʒ
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    /**
    +----------------------------------------------------------
     * �첽�Ӳֿ�������Ʒ
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function ajax_get_from_warehouse()
    {
        //�ɹ���ϸ���
        $purchase_list_id = intval($_GET['purchase_list_id']);

        //������������
        $apply_num = floatval($_GET['apply_num']);

        //��ѯ�ɹ���ϸ��Ϣ������������������������Ʒ
        if($purchase_list_id > 0 && $apply_num != 0)
        {
            /***�ֿ�MODEL***/
            $warehouse_model = D('Warehouse');

            /***�������MODEL***/
            $warehouse_use_model = D('WarehouseUse');

            /***�ɹ���ϸMODEL***/
            $purchase_list_model = D('PurchaseList');

            //�ɹ���ϸ
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchase_list_id);
            if(is_array($purchase_list_info) && !empty($purchase_list_info))
            {
                //��ƷƷ��
                $brand = $purchase_list_info[0]['BRAND'];
                //��Ʒ�ͺ�
                $model = $purchase_list_info[0]['MODEL'];
                //��Ʒ����
                $product_name = $purchase_list_info[0]['PRODUCT_NAME'];
                //����޼�
                $price_limit = $purchase_list_info[0]['PRICE_LIMIT'];
                //����ɹ�����
                $apply_buy_num = $purchase_list_info[0]['NUM_LIMIT'];
                //����������
                $used_num = $purchase_list_info[0]['USE_NUM'];
                //�ѹ�������
                $bought_num = $purchase_list_info[0]['NUM'];
                //�ɹ���ͬ
                $contract_id = $purchase_list_info[0]['CONTRACT_ID'];

                //�ɹ���ϸ��Ϣ������Ѿ���ӵ��ɹ���ͬ�򲻿��Ա༭
                if( $contract_id  > 0 )
                {
                    $result['state']  = 0;
                    $result['msg']  = '�ɹ������Ѽ���ɹ���ͬ���޷��޸���������';
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                /***�ж�����ɹ�������ȥ�����ú��ѹ��������Ƿ�С�ڱ���������������***/
                if( ($apply_buy_num - $used_num - $bought_num) < $apply_num)
                {
                    $result['state']  = 0;
                    $result['msg']  = 'ʵ�ʲɹ�������������ɹ�����';
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }
            }
            else
            {
                $result['state']  = 0;
                $result['msg']  = '��������';
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }

            //??
            $use_status = $warehouse_use_model->get_conf_status();

            //����������������0
            if($apply_num > 0)
            {
                //����������
                $used_num = 0;

                //������Ʒ�ܽ��
                $use_total_price = 0;

                while($apply_num > $used_num)
                {
                    //����ѭ����������
                    $used_num_this_time = 0;

                    //��ѯ����ķ������������Ŀ���¼
                    $puroduct_info =
                        $warehouse_model->get_earliest_puroduct_info_by_search_key($brand, $model, $product_name, $price_limit, $this->channelid);
                    if(is_array($puroduct_info) && !empty($puroduct_info))
                    {
                        //�����õ�����
                        $enable_use_num = $puroduct_info[0]['NUM'] - $puroduct_info[0]['USE_NUM'];

                        //����Ҫ���õ�����
                        $need_use_num  = $apply_num - $used_num;

                        //������������
                        $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;

                        $used_info = array();

                        //�������õĲɹ���ϸ���
                        $used_info['PL_ID'] = $purchase_list_id;
                        //������Ʒ�����
                        $used_info['WH_ID'] = $puroduct_info[0]['ID'];
                        //������Ʒ��浥��
                        $used_info['USE_PRICE'] = $puroduct_info[0]['PRICE'];
                        //��������
                        $used_info['USE_NUM'] = $used_num_this_time;
                        //����ʱ��
                        $used_info['USE_TIME'] = date('Y-m-d H:i:s');
                        //??
                        $used_info['STATUS'] = $use_status['not_confirm'];

                        //�������ù�ϵ������
                        $insert_id = $warehouse_use_model->add_used_info($used_info);

                        //���¿������
                        $up_num = $warehouse_model->update_warehouse_use_num($puroduct_info[0]['ID'], $used_num_this_time);

                        //����������
                        if( $insert_id > 0 && $up_num > 0)
                        {
                            $used_num += $used_num_this_time;

                            //���������ܽ��
                            $use_total_price += $used_num_this_time * $puroduct_info[0]['PRICE'];
                        }
                        else
                        {
                            break;
                        }
                    }
                    else
                    {
                        break;
                    }
                }
            }
            else if($apply_num < 0)
            {
                //������������
                $used_num = 0;

                //�����ܽ��
                $use_total_price = 0;

                $apply_num_abs = abs($apply_num);
                while( $used_num < $apply_num_abs )
                {
                    //��ѯ������õ���Ʒ
                    $use_info = array();
                    $use_info = $warehouse_use_model->get_last_use_info_by_purchase_id($purchase_list_id);

                    if(is_array($use_info) && !empty($use_info))
                    {
                        //�������������
                        $enable_use_num = $use_info['USE_NUM'];

                        //����Ҫ���������
                        $need_use_num  = $apply_num_abs - $used_num;

                        //������������
                        $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;

                        //���»���ɾ��������ϸ
                        if($used_num_this_time >= $enable_use_num)
                        {
                            //ɾ������������ϸ
                            $update_use_info = $warehouse_use_model->del_use_info_by_id($use_info['ID']);
                        }
                        else
                        {
                            //���±���������ϸ��Ϣ
                            $update_arr = array();
                            $update_arr['USE_NUM'] = array('exp', "USE_NUM - " .$used_num_this_time);
                            $update_use_info = $warehouse_use_model->update_info_by_id($use_info['ID'], $update_arr);
                        }

                        //�˻زֿ⣨���¿�������
                        $up_num = $warehouse_model->update_warehouse_use_num($use_info['WH_ID'], - $used_num_this_time);

                        //����������
                        if( $update_use_info > 0 && $up_num > 0)
                        {
                            $used_num += $used_num_this_time;

                            //���������ܽ��
                            $use_total_price += - ($used_num_this_time * $use_info['USE_PRICE']);
                        }
                        else
                        {
                            break;
                        }
                    }
                    else
                    {
                        break;
                    }
                }

                $used_num = $used_num > 0 ? - $used_num : 0;
            }

            if( abs($used_num) > 0)
            {
                /***���²ɹ���ϸ����������������Ʒ�ܽ��***/
                $purchase_list_model = D('PurchaseList');
                $update_arr = array();
                $update_arr['USE_NUM'] =  array('exp', "USE_NUM + " .$used_num);
                $update_arr['USE_TOATL_PRICE'] = array('exp', "USE_TOATL_PRICE + " .$use_total_price);
                $up_num = $purchase_list_model->update_purchase_list_by_id($purchase_list_id, $update_arr);

                //���ؽ��
                $result['state']  = 1;
                //��������ɹ���ϸ
                $purchase_list_info_latest = $purchase_list_model->get_purchase_list_by_id($purchase_list_id);
                if(is_array($purchase_list_info_latest) && !empty($purchase_list_info_latest))
                {
                    $result['use_num'] = $purchase_list_info_latest[0]['USE_NUM'];
                    $result['use_total_price'] = $purchase_list_info_latest[0]['USE_TOATL_PRICE'];
                }
                else
                {
                    $result['use_num'] = 0;
                    $result['use_total_price'] = 0;
                }

                if($used_num > 0)
                {
                    $result['msg']  = '���óɹ��������ÿ��������'.$used_num;
                }
                else
                {
                    $result['msg']  = '�������������ɹ����˻�����������'.abs($used_num);
                }

                if($up_num == FALSE)
                {
                    $result['msg']  .= "�ɹ���ϸ�����������������ʧ��";
                } else {
                    // ���²ɹ������
                    if (intval($purchase_list_info_latest[0]['USE_NUM']) == 0 &&
                        intval($purchase_list_info_latest[0]['NUM']) == 0) {
                        $purchaseStatus = 0;
                    } else {
                        $purchaseStatus = 1;
                    }

                    $updateData['STATUS'] = $purchaseStatus;
                    if ($used_num > 0) {
                        $curDate = date('Y-m-d H:i:s');
                        if (empty($purchase_list_info_latest[0]['COST_OCCUR_TIME'])) {
                            // ��¼���÷���ʱ��
                            $updateData['COST_OCCUR_TIME'] = $curDate;
                        }

                        if (empty($purchase_list_info_latest[0]['PURCHASE_OCCUR_TIME'])) {
                            // ��¼����¼��ʱ��
                            $updateData['PURCHASE_OCCUR_TIME'] = $curDate;
                        }
                    }

                    $updatedStatus = D('PurchaseList')->where("id = {$purchase_list_id}")->save($updateData);
                    if ($updatedStatus !== false) {
                        if (D('PurchaseList')->is_all_purchased($purchase_list_info_latest[0]['PR_ID'])) {
                            // �ɹ���ϸȫ���ɹ���������òɹ����뵥Ϊ�ɹ����״̬
                            $updatedStatus = D('PurchaseRequisition')->where("ID = {$purchase_list_info_latest[0]['PR_ID']}")->save(array('STATUS' => 4));
                        } else {
                            // �������òɹ����뵥Ϊ����ͨ��״̬
                            $updatedStatus = D('PurchaseRequisition')->where("ID = {$purchase_list_info_latest[0]['PR_ID']}")->save(array('STATUS' => 2));
                        }
                    }
                    $result['purchase_status'] = $purchaseStatus;
                }
            }
            else
            {
                $result['state']  = 0;
                $result['msg']  = '����ʧ��';
            }
        }
        else
        {
            $result['state']  = 0;
            $result['msg']  = '����ʧ�ܣ������쳣';
        }

        $result['msg'] = g2u($result['msg']);
        echo json_encode($result);
    }

    public function ajax_get_from_warehouse2() {
        $purchaseListId = intval($_GET['purchase_list_id']); // �ɹ���ϸ���
        $applyNum = floatval($_GET['apply_num']); // ������������

        //��ѯ�ɹ���ϸ��Ϣ������������������������Ʒ
        if($purchaseListId > 0 && $applyNum != 0) {
            //�ɹ���ϸ
            $purchasedListInfo = D('PurchaseList')->get_purchase_list_by_id($purchaseListId);
            if ($purchasedListInfo === false || empty($purchasedListInfo)) {
                $msg = $purchasedListInfo === false ? '�������ڲ�����' : '�����������';
                echo json_encode(array(
                    'state' => 0,
                    'msg' => g2u($msg)
                ));
                exit;
            }

            if (notEmptyArray($purchasedListInfo)) {
                $apply_buy_num = $purchasedListInfo[0]['NUM_LIMIT']; // ����ɹ�����
                $usedNum = $purchasedListInfo[0]['USE_NUM']; // ����������
                $bought_num = $purchasedListInfo[0]['NUM']; // �ѹ�������

                // �ж�����ɹ�������ȥ�����ú��ѹ��������Ƿ�С�ڱ���������������
                if (($apply_buy_num - $usedNum - $bought_num) < $applyNum) {
                    $result['state'] = 0;
                    $result['msg'] = g2u('ʵ�ʲɹ�������������ɹ�����');
                    echo json_encode($result);
                    exit;
                }

                if ($applyNum < 0 && $usedNum < abs($applyNum)) {
                    $result['state'] = 0;
                    $result['msg'] = g2u('�˿��������������õ�����');
                    echo json_encode($result);
                    exit;
                }
            }

            $purchaseWarehouseUse = array();  // �������
            $usageReverted = array(); // �˿����
            $response = array();
            $applyStatus = false;
            D()->startTrans();
            if ($applyNum > 0) {
                // ���ò���
                $applyStatus = $this->useFromWarehouse($purchasedListInfo[0], $applyNum, $purchaseWarehouseUse, $response);
            } else if ($applyNum < 0) {
                // �˿����
                $applyStatus = $this->revert2Warehouse($applyNum, $purchaseListId, $usageReverted, $response);
            }

            if ($applyStatus === false) {
                D()->rollback();
                $response['msg'] = g2u($response['msg']);
                echo json_encode($response);
            } else {
                D()->commit();
                $response['msg'] = g2u($response['msg']);
                echo json_encode($response);
            }
        } else {
            echo json_encode(array(
                'state' => 0,
                'msg' => g2u('��������')
            ));
        }
    }

    /**
     * ͨ���ؼ��ֻ�ȡ�����Ʒ + �û�������
     * �û��ֿ���˿���������ȡ����
     */
    public function ajaxMatchedStorage() {
        $response = array();

        //���ݹؼ��ʻ�ȡ�����Ϣ
        $search_key = $this->_request('keyword');
        $search_type = $this->_request('search_type'); //displace���û�  Ĭ�ϣ��ɹ�

        //���
        $sql = sprintf(self::STORAGE_SQL, "'%{$search_key}%'", 1, $this->channelid);
        $list = D('Warehouse')->query($sql);
        if (is_array($list) && count($list)) {
            foreach($list as $item) {
                $tmp['label'] = g2u(sprintf("Ʒ��[<strong style='color: red;'>%s</strong>]�� Ʒ��[<strong>%s</strong>]�� �ͺ�[<strong>%s</strong>]����������[<strong>%d</strong>], ����[<strong>%s</strong>Ԫ] - ����",
                    $item['PRODUCT_NAME'], $item['BRAND'], $item['MODEL'], intval($item['NUM']) - intval($item['USE_NUM']), $item['PRICE']));

                if($search_type=='displace') { //������û�
                    $tmp['label'] = g2u(sprintf("Ʒ��[<strong style='color: red;'>%s</strong>]�� Ʒ��[<strong>%s</strong>]�� �ͺ�[<strong>%s</strong>] - ����",
                        $item['PRODUCT_NAME'], $item['BRAND'], $item['MODEL']));
                }

                $tmp['value'] = g2u($item['PRODUCT_NAME']);
                $tmp['model'] = g2u($item['MODEL']);
                $tmp['brand'] = g2u($item['BRAND']);
                $tmp['price'] = g2u($item['PRICE']);
                $response []= $tmp;
            }
        }

        //�û���
        $sql = sprintf(self::DISPLACE_SQL, "'%{$search_key}%'", 2, $this->channelid);

        if($search_type=='displace') //ֱ�����û�
            $sql = sprintf(self::DISPLACE_PROJECTNAME_SQL, "'%{$search_key}%'", $this->channelid);

        $list = D('Displace_warehouse')->query($sql);
        if (is_array($list) && count($list)) {
            foreach($list as $item) {
                $tmp['label'] = g2u(sprintf("Ʒ��[<strong style='color: red;'>%s</strong>]�� Ʒ��[<strong>%s</strong>]�� �ͺ�[<strong>%s</strong>]����������[<strong>%d</strong>], ����[<strong>%s</strong>Ԫ] - �û��ֿ�",
                    $item['PRODUCT_NAME'], $item['BRAND'], $item['MODEL'], intval($item['NUM']), $item['PRICE']));

                if($search_type=='displace') { //������û�
                    $tmp['label'] = g2u(sprintf("Ʒ��[<strong style='color: red;'>%s</strong>]�� Ʒ��[<strong>%s</strong>]�� �ͺ�[<strong>%s</strong>] - �û��ֿ�",
                        $item['PRODUCT_NAME'], $item['BRAND'], $item['MODEL']));
                }

                $tmp['value'] = g2u($item['PRODUCT_NAME']);
                $tmp['model'] = g2u($item['MODEL']);
                $tmp['brand'] = g2u($item['BRAND']);
                $tmp['price'] = g2u($item['PRICE']);
                $response []= $tmp;
            }
        }

        //���Ϊ��
        if(empty($response)){
            $response[0]['id'] = 0;
            $response[0]['label'] = '';
        }

        echo json_encode($response);
    }
}

/* End of file WarehouseAction.class.php */
/* Location: ./Lib/Action/WarehouseAction.class.php */