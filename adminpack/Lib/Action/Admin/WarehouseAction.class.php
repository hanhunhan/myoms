<?php

/**
 * 仓库管理控制器
 *
 * @author liuhu
 */
class WarehouseAction extends ExtendAction{
    /**
     * 领用来自于置换仓库类型
     */
    const USE_FROM_DISPLACE_WAREHOUSE = 2;

    /**
     * 采购明细尚未采购
     */
    const NOT_PURCHASED_STATUS = 0;

    /**
     * 采购明细已经采购
     */
    const PURCHASED_STATUS = 1;

    /**
     * 确认入库权限
     */
    const CONFIRM_TO_WAREHOUSE = 661;

    /**
     * 打回权限
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
              AND　A.NUM > 0
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


    
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();
    
    /***TAB页签编号***/
    private $_tab_number = 7;
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
        // 权限映射表
        $this->authorityMap = array(
            'confirm_to_warehouse' => self::CONFIRM_TO_WAREHOUSE,
            'send_back' => self::SEND_BACK
        );
        
        //TAB URL参数
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
    * 仓库管理
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
        
        //来源
        $from_arr = $warehouse_model->get_conf_from();
        
        //状态数组
        $status_arr = $warehouse_model->get_conf_status();
        
        //查询退库未审核的数据
        $cond_wehre = " STATUS = '".$status_arr['audited']."' AND CITY_ID = ".$this->channelid;
    	$form = $form->initForminfo(180)->where($cond_wehre);
        
    	//来源设置
    	$from_arr_remark = $warehouse_model->get_conf_from_remark();
    	$form->setMyField('IS_FROM', 'LISTCHAR', array2listchar($from_arr_remark), FALSE);
    	
    	//状态设置
    	$status_arr_remark = $warehouse_model->get_conf_status_remark();
    	$form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr_remark), FALSE);
        
        /***隐藏相关字段***/
        $form->setMyField('IS_KF', 'FORMVISIBLE', '0', FALSE);
        $form->setMyField('IS_FUNDPOOL', 'FORMVISIBLE', '0', FALSE);
        $form->setMyField('FEE_ID', 'FORMVISIBLE', '0', FALSE);
        $form->setMyField('INPUT_TAX', 'FORMVISIBLE', '0', FALSE);
        
        if($showForm > 0)
        {
            //费用类型
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
        }
        
    	$formHtml = $form->getResult();
    	$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
    	$tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ? 
    	$this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
    	$this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));
    	$this->display('warehouse_manage');
    }
    
    
    /**
    +----------------------------------------------------------
    * 退库管理
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
        
        //来源
        $from_arr = $warehouse_model->get_conf_from();
        
        //状态数组
        $status_arr = $warehouse_model->get_conf_status();
        
        //查询退库未审核的数据
        $cond_wehre = "IS_FROM = '".$from_arr['return_to_warehouse']."' "
                    . "AND STATUS = '".$status_arr['not_audit']."' AND CITY_ID = ".$this->channelid;
    	$form = $form->initForminfo(180)->where($cond_wehre);
        
    	//来源设置
    	$from_arr_remark = $warehouse_model->get_conf_from_remark();
    	$form->setMyField('IS_FROM', 'LISTCHAR', array2listchar($from_arr_remark), FALSE);
    	
    	//状态设置
    	$status_arr_remark = $warehouse_model->get_conf_status_remark();
    	$form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr_remark), FALSE);
        
        if($showForm > 0)
        {
            //费用类型
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
        }
    	
    	/****设置操作按钮****/
    	$form->GABTN =  '<a id = "confirm_to_warehouse" href="javascript:;"  class="btn btn-info btn-sm">确认入库</a>';
        $form->GABTN .=  '<a id = "send_back" href="javascript:;" class="btn btn-info btn-sm">打回</a>';

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
    	$formHtml = $form->getResult();
    	$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
    	$tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ? 
    	$this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
    	$this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));
    	$this->display('returned_warehouse_manage');
    }
    
    
    /**
     +----------------------------------------------------------
     * 申请退库
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function return_to_warehouse()
    {
    	$result = array();
        
    	//采购明细编号
    	$purchase_id = !empty($_GET['purchase_id']) ? $_GET['purchase_id'] : 0;
        $apply_back_num = !empty($_GET['apply_back_num']) ? $_GET['apply_back_num'] : 0;
        
    	if($purchase_id > 0 && $apply_back_num > 0)
    	{
            //采购明细编号
            $purchase_list_model = D('PurchaseList');

            //查询采购明细数据(已报销数据)
            $purchase_list_info = array();
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchase_id);

            //循环添加采购明细信息到库存表表
            if(is_array($purchase_list_info) && !empty($purchase_list_info))
            {
                $warehouse_model = D('Warehouse');
                $from_arr = $warehouse_model->get_conf_from();
                $satus_arr = $warehouse_model->get_conf_status();

                //采购明细是否已经报销过
                $purchase_status = $purchase_list_model->get_conf_list_status();
                if($purchase_list_info[0]['STATUS'] != $purchase_status['reimbursed'])
                {
                    $result['state']  = 0;
                    $result['msg']  = '退库申请失败，已报销的采购才能申请退库。';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                //是否已经退库判断
                $conf_back_stock_status = $purchase_list_model->get_conf_back_stock_status();
                if($purchase_list_info[0]['BACK_STOCK_STATUS'] == $conf_back_stock_status['applied'])
                {
                    $result['state']  = 0;
                    $result['msg']  = '退库申请失败， 正在申请退库的采购不能再次申请退库';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                /***判断申请退库数量是否已经超过采购+领用数量***/
                //已退库数量
                $returned_num = $purchase_list_info[0]['STOCK_NUM'];

                $useWarehouse = D('WarehouseUse')->getSumnumByPurchaseId($purchase_id,1); //库存领用数量
                $useDisplace = D('WarehouseUse')->getSumnumByPurchaseId($purchase_id,2); //置换池领用数量

                //已采购数量（可退数量）
                $purchased_num = $useWarehouse + $purchase_list_info[0]['NUM'];


                //根据采购信息查询该条采购报销的金额
                $reim_cost_price = 0;
                $reim_cost_info = array();
                $reim_detail_model = D('ReimbursementDetail');
                $cond_where = "CITY_ID = '".$this->channelid."' AND "
                        . " CASE_ID = '".$purchase_list_info[0]['CASE_ID']."' AND "
                        . " BUSINESS_PARENT_ID = '".$purchase_list_info[0]['PR_ID']."' AND "
                        . " BUSINESS_ID = '".$purchase_list_info[0]['ID']."' AND STATUS = 1 ";
                $reim_cost_info = $reim_detail_model->get_detail_info_by_cond($cond_where, array('MONEY'));

                //存在采购的时候，报销数据必须存在
                if(empty($reim_cost_info) && $purchase_list_info[0]['NUM']>0)
                {
                    $result['state']  = 0;
                    $result['msg']  = '退库申请失败， 未查到采购报销数据';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit; 
                }
                $reim_cost_price = floatval($reim_cost_info[0]['MONEY']);
                //采购总成本(报销总金额+领用总金额)

                $useTotalPrice = $purchase_list_info[0]['USE_TOATL_PRICE']; //使用领用金额
                $useTotalPrice = ($useWarehouse/($useWarehouse+$useDisplace)) * $useTotalPrice;  //获取真正的使用金额 （置换不能退）

                $total_price = $reim_cost_price + $useTotalPrice;
                //允许申请退库数量
                $back_num_enable = $purchased_num - $returned_num;
                
                if($back_num_enable < $apply_back_num)
                {
                    $result['state']  = 0;
                    $result['msg']  = '退库申请失败，申请退库数量太多。<br>'
                        . '申请退库数量不得大于（采购数量 + 库存池领用数量 - 已退库数量）<br>'
                        . '置换仓库领用数量：<font color="red">' . $useDisplace . '</font> 库存池领用数量：<font color="red">' . $useWarehouse . '</font><br>'
                        . '从置换仓库领用的物品，不能做退库操作!';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                //退库信息
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
                //根据规则计算退库价格
                $return_price = self::_get_return_price($total_price, $purchased_num);

                if($return_price <= 0)
                {
                    $result['state']  = 0;
                    $result['msg']  = '退库申请失败，退库价格异常';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                //当申请剩余数量全部退库的时候，如果退库价价格为四舍五入得到，则存储两条退库信息（最后一条存最高单价）
                if($back_num_enable == $apply_back_num)
                {   
                    if($total_price / $purchased_num != $return_price)
                    {   
                        $high_price = $total_price - ($purchased_num - 1 ) * $return_price;
                        $purchase_info['PRICE'] = $high_price;
                        $purchase_info['NUM'] = 1;

                        //添加入库信息
                        $isert_id_high_price = $warehouse_model->return_to_warehouse($purchase_info);

                        $apply_back_num = $apply_back_num -1;
                    }
                }

                if($apply_back_num > 0)
                {
                    $purchase_info['PRICE'] = $return_price;
                    $purchase_info['NUM'] = $apply_back_num;

                    //添加入库信息
                    $isert_id = $warehouse_model->return_to_warehouse($purchase_info);
                }

                if($isert_id_high_price > 0 || ($apply_back_num > 0 && $isert_id > 0))
                {   
                    //退库申请中
                    $update_num = $purchase_list_model->update_to_apply_back_stock_by_id($purchase_id);

                    $result['state']  = 1;
                    $result['msg']  = '退库申请添加成功，等待确认退库';
                }
                else
                {
                    $result['state']  = 0;
                    $result['msg']  = '退库申请添加失败';
                }
            }
            else
            {
                $result['state']  = 0;
                $result['msg']  = '没查询到符合条件的数据';
            }
    	}
    	else
    	{
            $result['state']  = 0;
            $result['msg']  = '退库申请失败，必须选择一条采购明细并且退库数量大于0';
    	}
    
        $result['msg'] = g2u($result['msg']);
        echo json_encode($result);
    }
    
    
    /**
     +----------------------------------------------------------
     * 获取退库价格
     +----------------------------------------------------------
     * @param float $total_price 采购总成本
     * @param int $total_num 采购总数量
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
     * 确认退库入库
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
    	
    	//退库明细编号
    	$warehouse_id = !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : array();
        
    	if(!empty($warehouse_id))
    	{   
            /**更新退库申请**/
    		$warehouse_model = D('Warehouse');
    		$update_num = $warehouse_model->confirm_to_warehouse($warehouse_id);
            
            /**查询本次确定退库的信息**/
            $search_field = array('ID', 'PL_ID', 'NUM', 'PRICE', 'IS_FROM', 'STATUS');
            $warehouse_info = $warehouse_model->get_product_info_by_ids($warehouse_id, $search_field);
            
            if(is_array($warehouse_info) && !empty($warehouse_info))
            {   
                $purchase_list_model = D('PurchaseList');
                $cost_model = D('ProjectCost');
                
                //采购信息需要查询字段
                $purchase_serach_filed = array('ID', 'CASE_ID', 'PR_ID', 'NUM', 
                								'PRICE', 'IS_KF', 'IS_FUNDPOOL', 'FEE_ID');
                
                foreach($warehouse_info as $key => $value)
                {   
                	if($value['PL_ID'] > 0)
                	{
	                    //当前采购是否有未确定退库的申请
	                    $not_confirm_num = $warehouse_model->get_not_confrim_num_by_pl_id($value['PL_ID']);
	                    
                        //如果不存在则同时更新采购退库数量和退库状态
	                    if($not_confirm_num == 0)
	                    {
	                        $update_purchase = 
	                            $purchase_list_model->update_stock_num_by_id($value['PL_ID'], $value['NUM']);
	                    }
	                    else
	                    {   
                            //如果存在则只更新数量
	                        $update_arr['STOCK_NUM'] =  $value['NUM'];
	                        $update_purchase = 
	                        $purchase_list_model->update_purchase_list_by_id($value['PL_ID'], $update_arr);
	                    }
	                    
	                    //确认退库则减少采购成本
	                    if($update_purchase > 0)
	                    {	
	                    	/***通过采购明细编号获取采购明细信息***/
	                    	$purchase_info = $purchase_list_model->get_purchase_list_by_id($value['PL_ID'], $purchase_serach_filed);
							
	                    	if(is_array($purchase_info) && !empty($purchase_info))
	                    	{
			                    $cost_info = array();
			                    $cost_info['CASE_ID'] = $purchase_info[0]['CASE_ID'];
			                    $cost_info['ENTITY_ID'] = $purchase_info[0]['PR_ID'];
			                    $cost_info['EXPEND_ID'] = $value['PL_ID'];
			                    $cost_info['ORG_ENTITY_ID'] = $purchase_info[0]['PR_ID'];
			                    $cost_info['ORG_EXPEND_ID'] = $value['PL_ID'];
			                    $cost_info['EXPEND_FROM'] = 27;//采购退库
			                    $cost_info['FEE'] = - $value['NUM'] * $value['PRICE'];
			                    $cost_info['FEE_REMARK'] = '申请退库通过';
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
    			$result['msg']  = '退库失败，请选择确认入库的信息';
            }
    		
    	    if($update_num > 0)
    		{   
                //更新采购明细退库数量
    			$result['state']  = 1;
    			$result['msg']  = '退库成功';
    		}
    		else
    		{
    			$result['state']  = 0;
    			$result['msg']  = '退库失败';
    		}
    	}
    	else
    	{
    		$result['state']  = 0;
    		$result['msg']  = '没查询到符合条件的数据';
    	}
    	
    	$result['msg'] = g2u($result['msg']);
    	echo json_encode($result);
    }
    
    /**
     +----------------------------------------------------------
     * 打回退库申请
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
    	
    	//退库明细编号
    	$warehouse_id = !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : array();
    
    	if(!empty($warehouse_id))
    	{
    		/**根据退库信息，更新申请记录**/
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
				
    			//更新采购
    			$update_purchase = $purchase_list_model->update_apply_send_back_by_id($arr_list_id);
    			
    			//更新退库申请记录
    			$update_num = $warehouse_model->application_send_back($warehouse_id);
    			
    			if($update_num > 0 && $update_purchase)
    			{
    				//更新采购明细退库数量
    				$result['state']  = 1;
    				$result['msg']  = '打回成功';
    			}
    			else
    			{
    				$result['state']  = 0;
    				$result['msg']  = '打回失败';
    			}
    		}
    		else
    		{
    			$result['state']  = 0;
    			$result['msg']  = '没查询到符合条件的数据';
    		}
    	}
    	else
    	{
    		$result['state']  = 0;
    		$result['msg']  = '打回失败，请选择信息';
    	}
    
    	$result['msg'] = g2u($result['msg']);
    	echo json_encode($result);
    }
    
    
    /**
     +----------------------------------------------------------
     * 异步从库存情况
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
        //最高限价
        $price_limit = floatval($_GET['price_limit']);
        //城市
        $city_id = $this->channelid;
        
        $warehouse_model = D('Warehouse');
        // 获取采购仓库中的库存数
        $total_num = $warehouse_model->get_total_num_by_name($brand, $model, $product_name, $price_limit, $city_id);
        // 获取置换仓库中的库存数
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
     * 从采购仓库中领用，仓库中存放物品的地方可能不止一处，故可能发生从仓库中的不同ID中领用
     * @param $purchase array 采购信息
     * @param $remainNeedAmount int 最大需要领用的数量
     * @param array $warehouseUse
     * @return bool
     */
    private function useFromPurchaseWarehouse($purchase, $remainNeedAmount, &$warehouseUse = array()) {
        if (intval($remainNeedAmount) === 0) {
            return true;
        }

        //已领用数量
        $useAmount = 0;
        //领用物品总金额
        $useTotalMoney = 0;
        //物品品牌
        $brand = $purchase['BRAND'];
        //物品型号
        $model = $purchase['MODEL'];
        //物品名称
        $productName = $purchase['PRODUCT_NAME'];
        //最高限价
        $priceLimit = $purchase['PRICE_LIMIT'];
        $purchaseListId = $purchase['ID'];

        /***仓库MODEL***/
        $warehouseModel = D('Warehouse');
        /***库存领用MODEL***/
        $warehouseUseModel = D('WarehouseUse');
        // 状态
        $use_status = $warehouseUseModel->get_conf_status();

        while($remainNeedAmount > $useAmount) {
            //查询最早的符合搜索条件的库存记录
            $wareHouseProductList =
                $warehouseModel->get_earliest_puroduct_info_by_search_key($brand, $model, $productName, $priceLimit, $this->channelid);
            // 取数据的过程中出现错误
            if ($wareHouseProductList === false) {
                return false;
            }

            if (notEmptyArray($wareHouseProductList)) {
                $wareHouseProduct = $wareHouseProductList[0];  // 获取一条产品信息
                // 仓库中某个ID下可以领用的数量
                $enable_use_num = $wareHouseProduct['NUM'] - $wareHouseProduct['USE_NUM'];
                // 本次申请领用剩余的数量
                $need_use_num = $remainNeedAmount - $useAmount;
                //本次领用数量
                $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;

                $used_info = array();
                //申请领用的采购明细编号
                $used_info['PL_ID'] = $purchaseListId;
                //领用物品库存编号
                $used_info['WH_ID'] = $wareHouseProduct['ID'];
                //领用物品库存单价
                $used_info['USE_PRICE'] = $wareHouseProduct['PRICE'];
                //领用数量
                $used_info['USE_NUM'] = $used_num_this_time;
                //领用时间
                $used_info['USE_TIME'] = date('Y-m-d H:i:s');
                //状态
                $used_info['STATUS'] = $use_status['not_confirm'];
                //插入领用关系表数据
                $insert_id = $warehouseUseModel->add_used_info($used_info);
                //更新库存数量
                $up_num = $warehouseModel->update_warehouse_use_num($wareHouseProduct['ID'], $used_num_this_time);
                //已领用数量
                if ($insert_id > 0 && $up_num > 0) {
                    $useAmount += $used_num_this_time;
                    //本次领用总金额
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
     * 从置换仓库中领用
     * @param $purchase array 采购信息
     * @param $remainNeedAmount int 最大需要领用的数量
     * @param array $warehouseUse
     * @return bool
     */
    private function useFromDisplaceWarehouse($purchase, $remainNeedAmount, &$warehouseUse = array()) {
        if (intval($remainNeedAmount) === 0) {
            return true;
        }

        //已领用数量
        $useAmount = 0;
        //领用物品总金额
        $useTotalMoney = 0;
        //物品品牌
        $brand = $purchase['BRAND'];
        //物品型号
        $model = $purchase['MODEL'];
        //物品名称
        $productName = $purchase['PRODUCT_NAME'];
        //最高限价
        $priceLimit = $purchase['PRICE_LIMIT'];
        $purchaseListId = $purchase['ID'];

        /***仓库MODEL***/
        $warehouseModel = D('Warehouse');
        /***库存领用MODEL***/
        $warehouseUseModel = D('WarehouseUse');
        // 状态
        $use_status = $warehouseUseModel->get_conf_status();

        while($remainNeedAmount > $useAmount) {
            //查询最早的符合搜索条件的库存记录
            $warehouseProductList = D('Displace')->getDisplaceWarehouseProduct(array(
                'brand' => $brand,
                'model' => $model,
                'name' => $productName
            ), $priceLimit, $this->channelid);
            // 取数据的过程中出现错误
            if ($warehouseProductList === false) {
                return false;
            }

            if (notEmptyArray($warehouseProductList)) {
                // 仓库中某个ID下可以领用的数量
                $warehouseProduct = $warehouseProductList[0];
                $enable_use_num = $warehouseProduct['NUM'];
                // 本次申请领用剩余的数量
                $need_use_num = $remainNeedAmount - $useAmount;
                //本次领用数量
                $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;

                $used_info = array();
                //申请领用的采购明细编号
                $used_info['PL_ID'] = $purchaseListId;
                //领用物品库存编号
                $used_info['WH_ID'] = $warehouseProduct['ID'];
                //领用物品库存单价
                $used_info['USE_PRICE'] = $warehouseProduct['PRICE'];
                //领用数量
                $used_info['USE_NUM'] = $used_num_this_time;
                //领用时间
                $used_info['USE_TIME'] = date('Y-m-d H:i:s');
                //状态
                $used_info['STATUS'] = $use_status['not_confirm'];
                $used_info['TYPE'] = self::USE_FROM_DISPLACE_WAREHOUSE;
                //插入领用关系表数据
                $insert_id = $warehouseUseModel->add_used_info($used_info);
                //更新库存数量
                $up_num = D('Displace')->updateWarehouseUseNum($warehouseProduct['ID'], $used_num_this_time);
                //已领用数量
                if ($insert_id > 0 && $up_num > 0) {
                    $useAmount += $used_num_this_time;
                    //本次领用总金额
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
     * 仓库领用或退库完成之后的操作
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
            $msg = '领用或退库数量为0，无需更新采购明细';
            return true;
        }

        if (abs($usedNum) > 0) {
            /***更新采购明细领用数量、领用物品总金额***/
            $purchaseListModel = D('PurchaseList');
            $update_arr = array();
            $update_arr['USE_NUM'] = array('exp', "USE_NUM + " . $usedNum);
            $update_arr['USE_TOATL_PRICE'] = array('exp', "USE_TOATL_PRICE + " . $usedTotalMoney);
            $dbResult = $purchaseListModel->update_purchase_list_by_id($purchaseListId, $update_arr);

            if ($dbResult === false) {
                // 更新采购明细失败
                $msg = '服务器内部错误';
                return false;
            }

            //操作过后采购明细
            $updatedPurchaseList = $purchaseListModel->get_purchase_list_by_id($purchaseListId);
            if ($updatedPurchaseList === false) {
                // 获取数据库数据错误
                $msg = '服务器内部错误';
                return false;
            }

            // 更新采购的情况
            $updateData = array();
            $purchaseStatus = (intval($updatedPurchaseList[0]['USE_NUM']) == 0 &&
                intval($updatedPurchaseList[0]['NUM']) == 0
            ) ? self::NOT_PURCHASED_STATUS : self::PURCHASED_STATUS;
            $updateData['STATUS'] = $purchaseStatus;
            if (empty($updatedPurchaseList[0]['COST_OCCUR_TIME'])) {
                // 记录费用发生时间
                $updateData['COST_OCCUR_TIME'] = date('Y-m-d H:i:s');
            }
            if (empty($updatedPurchaseList[0]['PURCHASE_OCCUR_TIME'])) {
                // 记录费用录入时间
                $updateData['PURCHASE_OCCUR_TIME'] = date('Y-m-d H:i:s');
            }
            $dbResult = D('PurchaseList')->where("id = {$purchaseListId}")->save($updateData);
            if ($dbResult === false) {
                $msg = '服务器内部错误';
                return false;
            }

            if (D('PurchaseList')->is_all_purchased($updatedPurchaseList[0]['PR_ID'])) {
                // 采购明细全部采购完成则设置采购申请单为采购完成状态
                $dbResult = D('PurchaseRequisition')->where("ID = {$updatedPurchaseList[0]['PR_ID']}")->save(array('STATUS' => 4));
            } else {
                // 否则，设置采购申请单为申请通过状态
                $dbResult = D('PurchaseRequisition')->where("ID = {$updatedPurchaseList[0]['PR_ID']}")->save(array('STATUS' => 2));
            }

            if ($dbResult === false) {
                $msg = '服务器内部错误';
                return false;
            }
        } else {
            if ($usedNum > 0) {
                $msg = '领用失败';
            } else {
                $msg = '退库失败';
            }

            return false;
        }
    }

    /**
     * 领用操作
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
            $response['msg'] = '领用数量为0，无需领用';
            return true;
        }

        // 采购仓库领用情况
        $purchaseWarehouseUse = array(
            'amount' => 0,
            'total_money' => 0
        );

        // 置换仓库领用情况
        $displaceWarehouseUse = array(
            'amount' => 0,
            'total_money' => 0
        );
        $purchaseListId = $purchasedProduct['ID'];  // 购买采购ID
        $purchaseWarehouseStatus = $this->useFromPurchaseWarehouse($purchasedProduct, $applyNum, $purchaseWarehouseUse);
        if ($purchaseWarehouseStatus === false) {
            $response['state'] = 0;
            $response['msg'] = '从采购仓库领用失败';
            return false;
        }

        // 本次采购仓库中没有领到足够多的物品，则继续到置换仓库中领取
        $remainApplyNum = $applyNum - $purchaseWarehouseUse['amount'];
        if ($remainApplyNum > 0) {
            $displaceWarehouseStatus = $this->useFromDisplaceWarehouse($purchasedProduct, $remainApplyNum, $displaceWarehouseUse);
        } else {
            $displaceWarehouseStatus = true;
        }

        if ($displaceWarehouseStatus === false) {
            $response['state'] = 0;
            $response['msg'] = '从置换仓库领用失败';
            return false;
        }

        // 领用成功
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
            $response['msg'] = '领用失败';
            return false;
        }

        $response['state'] = 1;
        $response['msg'] = sprintf('领用成功，从采购仓库领用了%d件，从置换仓库领用了%d件。', $purchaseWarehouseUse['amount'], $displaceWarehouseUse['amount']);
        $response['purchase_status'] = $purchaseStatus;
        return true;
    }

    /**
     * 将已领用的物品退至采购或置换仓库中
     * @param $revertNum int 退库的数量
     * @param $purchaseListId int 采购的ID
     * @param $usageReverted array 退款的情况
     * @param $response
     * @return bool
     */
    private function revert2Warehouse($revertNum, $purchaseListId, $usageReverted, &$response) {
        $purchaseWarehouseModel = D('Warehouse'); // 采购仓库MODEL
        $displaceWarehouseModel = D('Displace'); // 置换仓库MODEL
        $warehouseUseModel = D('WarehouseUse'); // 库存领用MODEL
        $usedNum = 0; // 已退领用数量
        $useTotalMoney = 0; // 已退总金额
        $absRevertNum = abs($revertNum);
        while ($usedNum < $absRevertNum) {
            //查询最后领用的物品
            $usageInfo = $warehouseUseModel->get_last_use_info_by_purchase_id($purchaseListId);
            // 获取使用信息失败时，退库失败
            if ($usageInfo === false) {
                return false;
            }

            $warehouseType = $usageInfo['TYPE'];  // 仓库类型，1=采购仓库，2=置换仓库
            if (notEmptyArray($usageInfo)) {
                $enable_use_num = $usageInfo['USE_NUM']; // 本次能退领个数
                $need_use_num = $absRevertNum - $usedNum;  // 还需要退领的数量
                $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;  // 本次退领数量
                if ($used_num_this_time >= $enable_use_num) { // 更新或者删除领用明细
                    //删除本条领用明细
                    $update_use_info = $warehouseUseModel->del_use_info_by_id($usageInfo['ID']);
                } else {
                    //更新本条领用明细信息
                    $update_arr = array();
                    $update_arr['USE_NUM'] = array('exp', "USE_NUM - " . $used_num_this_time);
                    $update_use_info = $warehouseUseModel->update_info_by_id($usageInfo['ID'], $update_arr);
                }

                //退回仓库（更新库存情况）
                if ($warehouseType == 1) {
                    $up_num = $purchaseWarehouseModel->update_warehouse_use_num($usageInfo['WH_ID'], -$used_num_this_time);
                } else if ($warehouseType == 2) {
                    $up_num = $displaceWarehouseModel->updateWarehouseUseNum($usageInfo['WH_ID'], -$used_num_this_time);
                }


                //已退领数量
                if ($update_use_info > 0 && $up_num > 0) {
                    $usedNum += $used_num_this_time;
                    $useTotalMoney += -($used_num_this_time * $usageInfo['USE_PRICE']); // 本次退领总金额
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
            $response['msg'] = empty($msg) ? '退库失败' : $msg;
        }

        $response['state'] = 1;
        $response['msg'] = '退库成功，共退回仓库' . abs($usedNum) . '件';
        $response['purchase_status'] = $purchaseStatus;
        return true;
    }
    
    /**
     +----------------------------------------------------------
     * 异步从仓库领用物品
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    /**
    +----------------------------------------------------------
     * 异步从仓库领用物品
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function ajax_get_from_warehouse()
    {
        //采购明细编号
        $purchase_list_id = intval($_GET['purchase_list_id']);

        //申请领用数量
        $apply_num = floatval($_GET['apply_num']);

        //查询采购明细信息，根据申请数量领用领用物品
        if($purchase_list_id > 0 && $apply_num != 0)
        {
            /***仓库MODEL***/
            $warehouse_model = D('Warehouse');

            /***库存领用MODEL***/
            $warehouse_use_model = D('WarehouseUse');

            /***采购明细MODEL***/
            $purchase_list_model = D('PurchaseList');

            //采购明细
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchase_list_id);
            if(is_array($purchase_list_info) && !empty($purchase_list_info))
            {
                //物品品牌
                $brand = $purchase_list_info[0]['BRAND'];
                //物品型号
                $model = $purchase_list_info[0]['MODEL'];
                //物品名称
                $product_name = $purchase_list_info[0]['PRODUCT_NAME'];
                //最高限价
                $price_limit = $purchase_list_info[0]['PRICE_LIMIT'];
                //申请采购数量
                $apply_buy_num = $purchase_list_info[0]['NUM_LIMIT'];
                //已领用数量
                $used_num = $purchase_list_info[0]['USE_NUM'];
                //已购买数量
                $bought_num = $purchase_list_info[0]['NUM'];
                //采购合同
                $contract_id = $purchase_list_info[0]['CONTRACT_ID'];

                //采购明细信息，如果已经添加到采购合同则不可以编辑
                if( $contract_id  > 0 )
                {
                    $result['state']  = 0;
                    $result['msg']  = '采购申请已加入采购合同，无法修改领用数量';
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

                /***判断申请采购数量减去已领用和已购买数量是否小于本次申请领用数量***/
                if( ($apply_buy_num - $used_num - $bought_num) < $apply_num)
                {
                    $result['state']  = 0;
                    $result['msg']  = '实际采购数量大于申请采购数量';
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }
            }
            else
            {
                $result['state']  = 0;
                $result['msg']  = '参数错误';
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }

            //??
            $use_status = $warehouse_use_model->get_conf_status();

            //申请领用数量大于0
            if($apply_num > 0)
            {
                //已领用数量
                $used_num = 0;

                //领用物品总金额
                $use_total_price = 0;

                while($apply_num > $used_num)
                {
                    //本次循环领用数量
                    $used_num_this_time = 0;

                    //查询最早的符合搜索条件的库存记录
                    $puroduct_info =
                        $warehouse_model->get_earliest_puroduct_info_by_search_key($brand, $model, $product_name, $price_limit, $this->channelid);
                    if(is_array($puroduct_info) && !empty($puroduct_info))
                    {
                        //能领用的数量
                        $enable_use_num = $puroduct_info[0]['NUM'] - $puroduct_info[0]['USE_NUM'];

                        //还需要领用的数量
                        $need_use_num  = $apply_num - $used_num;

                        //本次领用数量
                        $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;

                        $used_info = array();

                        //申请领用的采购明细编号
                        $used_info['PL_ID'] = $purchase_list_id;
                        //领用物品库存编号
                        $used_info['WH_ID'] = $puroduct_info[0]['ID'];
                        //领用物品库存单价
                        $used_info['USE_PRICE'] = $puroduct_info[0]['PRICE'];
                        //领用数量
                        $used_info['USE_NUM'] = $used_num_this_time;
                        //领用时间
                        $used_info['USE_TIME'] = date('Y-m-d H:i:s');
                        //??
                        $used_info['STATUS'] = $use_status['not_confirm'];

                        //插入领用关系表数据
                        $insert_id = $warehouse_use_model->add_used_info($used_info);

                        //更新库存数量
                        $up_num = $warehouse_model->update_warehouse_use_num($puroduct_info[0]['ID'], $used_num_this_time);

                        //已领用数量
                        if( $insert_id > 0 && $up_num > 0)
                        {
                            $used_num += $used_num_this_time;

                            //本次领用总金额
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
                //已退领用数量
                $used_num = 0;

                //已退总金额
                $use_total_price = 0;

                $apply_num_abs = abs($apply_num);
                while( $used_num < $apply_num_abs )
                {
                    //查询最后领用的物品
                    $use_info = array();
                    $use_info = $warehouse_use_model->get_last_use_info_by_purchase_id($purchase_list_id);

                    if(is_array($use_info) && !empty($use_info))
                    {
                        //本次能退领个数
                        $enable_use_num = $use_info['USE_NUM'];

                        //还需要退领的数量
                        $need_use_num  = $apply_num_abs - $used_num;

                        //本次退领数量
                        $used_num_this_time = $need_use_num > $enable_use_num ? $enable_use_num : $need_use_num;

                        //更新或者删除领用明细
                        if($used_num_this_time >= $enable_use_num)
                        {
                            //删除本条领用明细
                            $update_use_info = $warehouse_use_model->del_use_info_by_id($use_info['ID']);
                        }
                        else
                        {
                            //更新本条领用明细信息
                            $update_arr = array();
                            $update_arr['USE_NUM'] = array('exp', "USE_NUM - " .$used_num_this_time);
                            $update_use_info = $warehouse_use_model->update_info_by_id($use_info['ID'], $update_arr);
                        }

                        //退回仓库（更新库存情况）
                        $up_num = $warehouse_model->update_warehouse_use_num($use_info['WH_ID'], - $used_num_this_time);

                        //已退领数量
                        if( $update_use_info > 0 && $up_num > 0)
                        {
                            $used_num += $used_num_this_time;

                            //本次退领总金额
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
                /***更新采购明细领用数量、领用物品总金额***/
                $purchase_list_model = D('PurchaseList');
                $update_arr = array();
                $update_arr['USE_NUM'] =  array('exp', "USE_NUM + " .$used_num);
                $update_arr['USE_TOATL_PRICE'] = array('exp', "USE_TOATL_PRICE + " .$use_total_price);
                $up_num = $purchase_list_model->update_purchase_list_by_id($purchase_list_id, $update_arr);

                //返回结果
                $result['state']  = 1;
                //操作过后采购明细
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
                    $result['msg']  = '领用成功，共领用库存数量：'.$used_num;
                }
                else
                {
                    $result['msg']  = '更新领用数量成功，退回领用数量：'.abs($used_num);
                }

                if($up_num == FALSE)
                {
                    $result['msg']  .= "采购明细，领用数量与金额更新失败";
                } else {
                    // 更新采购的情况
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
                            // 记录费用发生时间
                            $updateData['COST_OCCUR_TIME'] = $curDate;
                        }

                        if (empty($purchase_list_info_latest[0]['PURCHASE_OCCUR_TIME'])) {
                            // 记录费用录入时间
                            $updateData['PURCHASE_OCCUR_TIME'] = $curDate;
                        }
                    }

                    $updatedStatus = D('PurchaseList')->where("id = {$purchase_list_id}")->save($updateData);
                    if ($updatedStatus !== false) {
                        if (D('PurchaseList')->is_all_purchased($purchase_list_info_latest[0]['PR_ID'])) {
                            // 采购明细全部采购完成则设置采购申请单为采购完成状态
                            $updatedStatus = D('PurchaseRequisition')->where("ID = {$purchase_list_info_latest[0]['PR_ID']}")->save(array('STATUS' => 4));
                        } else {
                            // 否则，设置采购申请单为申请通过状态
                            $updatedStatus = D('PurchaseRequisition')->where("ID = {$purchase_list_info_latest[0]['PR_ID']}")->save(array('STATUS' => 2));
                        }
                    }
                    $result['purchase_status'] = $purchaseStatus;
                }
            }
            else
            {
                $result['state']  = 0;
                $result['msg']  = '领用失败';
            }
        }
        else
        {
            $result['state']  = 0;
            $result['msg']  = '领用失败，参数异常';
        }

        $result['msg'] = g2u($result['msg']);
        echo json_encode($result);
    }

    public function ajax_get_from_warehouse2() {
        $purchaseListId = intval($_GET['purchase_list_id']); // 采购明细编号
        $applyNum = floatval($_GET['apply_num']); // 申请领用数量

        //查询采购明细信息，根据申请数量领用领用物品
        if($purchaseListId > 0 && $applyNum != 0) {
            //采购明细
            $purchasedListInfo = D('PurchaseList')->get_purchase_list_by_id($purchaseListId);
            if ($purchasedListInfo === false || empty($purchasedListInfo)) {
                $msg = $purchasedListInfo === false ? '服务器内部错误' : '传入参数有误';
                echo json_encode(array(
                    'state' => 0,
                    'msg' => g2u($msg)
                ));
                exit;
            }

            if (notEmptyArray($purchasedListInfo)) {
                $apply_buy_num = $purchasedListInfo[0]['NUM_LIMIT']; // 申请采购数量
                $usedNum = $purchasedListInfo[0]['USE_NUM']; // 已领用数量
                $bought_num = $purchasedListInfo[0]['NUM']; // 已购买数量

                // 判断申请采购数量减去已领用和已购买数量是否小于本次申请领用数量
                if (($apply_buy_num - $usedNum - $bought_num) < $applyNum) {
                    $result['state'] = 0;
                    $result['msg'] = g2u('实际采购数量大于申请采购数量');
                    echo json_encode($result);
                    exit;
                }

                if ($applyNum < 0 && $usedNum < abs($applyNum)) {
                    $result['state'] = 0;
                    $result['msg'] = g2u('退库数量大于已领用的数量');
                    echo json_encode($result);
                    exit;
                }
            }

            $purchaseWarehouseUse = array();  // 领用情况
            $usageReverted = array(); // 退库情况
            $response = array();
            $applyStatus = false;
            D()->startTrans();
            if ($applyNum > 0) {
                // 领用操作
                $applyStatus = $this->useFromWarehouse($purchasedListInfo[0], $applyNum, $purchaseWarehouseUse, $response);
            } else if ($applyNum < 0) {
                // 退库操作
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
                'msg' => g2u('参数错误')
            ));
        }
    }

    /**
     * 通过关键字获取库存商品 + 置换池数据
     * 置换仓库和退库池两个库存取内容
     */
    public function ajaxMatchedStorage() {
        $response = array();

        //根据关键词获取库存信息
        $search_key = $this->_request('keyword');
        $search_type = $this->_request('search_type'); //displace：置换  默认：采购

        //库存
        $sql = sprintf(self::STORAGE_SQL, "'%{$search_key}%'", 1, $this->channelid);
        $list = D('Warehouse')->query($sql);
        if (is_array($list) && count($list)) {
            foreach($list as $item) {
                $tmp['label'] = g2u(sprintf("品名[<strong style='color: red;'>%s</strong>]， 品牌[<strong>%s</strong>]， 型号[<strong>%s</strong>]，可用数量[<strong>%d</strong>], 单价[<strong>%s</strong>元] - 库存池",
                    $item['PRODUCT_NAME'], $item['BRAND'], $item['MODEL'], intval($item['NUM']) - intval($item['USE_NUM']), $item['PRICE']));

                if($search_type=='displace') { //如果是置换
                    $tmp['label'] = g2u(sprintf("品名[<strong style='color: red;'>%s</strong>]， 品牌[<strong>%s</strong>]， 型号[<strong>%s</strong>] - 库存池",
                        $item['PRODUCT_NAME'], $item['BRAND'], $item['MODEL']));
                }

                $tmp['value'] = g2u($item['PRODUCT_NAME']);
                $tmp['model'] = g2u($item['MODEL']);
                $tmp['brand'] = g2u($item['BRAND']);
                $tmp['price'] = g2u($item['PRICE']);
                $response []= $tmp;
            }
        }

        //置换池
        $sql = sprintf(self::DISPLACE_SQL, "'%{$search_key}%'", 2, $this->channelid);

        if($search_type=='displace') //直接是置换
            $sql = sprintf(self::DISPLACE_PROJECTNAME_SQL, "'%{$search_key}%'", $this->channelid);

        $list = D('Displace_warehouse')->query($sql);
        if (is_array($list) && count($list)) {
            foreach($list as $item) {
                $tmp['label'] = g2u(sprintf("品名[<strong style='color: red;'>%s</strong>]， 品牌[<strong>%s</strong>]， 型号[<strong>%s</strong>]，可用数量[<strong>%d</strong>], 单价[<strong>%s</strong>元] - 置换仓库",
                    $item['PRODUCT_NAME'], $item['BRAND'], $item['MODEL'], intval($item['NUM']), $item['PRICE']));

                if($search_type=='displace') { //如果是置换
                    $tmp['label'] = g2u(sprintf("品名[<strong style='color: red;'>%s</strong>]， 品牌[<strong>%s</strong>]， 型号[<strong>%s</strong>] - 置换仓库",
                        $item['PRODUCT_NAME'], $item['BRAND'], $item['MODEL']));
                }

                $tmp['value'] = g2u($item['PRODUCT_NAME']);
                $tmp['model'] = g2u($item['MODEL']);
                $tmp['brand'] = g2u($item['BRAND']);
                $tmp['price'] = g2u($item['PRICE']);
                $response []= $tmp;
            }
        }

        //如果为空
        if(empty($response)){
            $response[0]['id'] = 0;
            $response[0]['label'] = '';
        }

        echo json_encode($response);
    }
}

/* End of file WarehouseAction.class.php */
/* Location: ./Lib/Action/WarehouseAction.class.php */