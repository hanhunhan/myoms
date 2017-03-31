<?php
/**
 * 采购供应商管理
 *
 * @author liuhu
 */
class SupplierAction extends ExtendAction{
    
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();
    
    /***TAB页签编号***/
    private $_tab_number = 7;
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
        
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
    * 供应商管理
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function supplier_manage()
    {
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        $city_id = isset($_POST['CITY_ID']) ? intval($_POST['CITY_ID']) : 0;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';

        if(!empty($_POST) && $faction == 'saveFormData' && $id > 0)
        {
            if($city_id == 0)
            {
                $info['state']  = 0;
                $info['msg'] = g2u('城市参数异常');

                echo json_encode($info);
                exit;
            }
            
            //类型状态变化，查看是否已经有
            if( ($_POST['TYPE'] != $_POST['TYPE_OLD'] && $_POST['TYPE'] == 1) || 
                    ($_POST['STATUS'] == 1 && $_POST['STATUS_OLD'] == 0 && $_POST['TYPE'] == 1) )
            {
                $num = M('erp_supplier')->where("CITY_ID = '".$city_id."' AND TYPE = 1 AND STATUS = 1")->count();
                if($num >= 1)
                {
                    $info['state']  = 0;
                    $info['msg'] = g2u('只能添加一个领用采购商');
                    
                    echo json_encode($info);
                    exit;
                }
            }  
        }
        else if(!empty($_POST) && $faction == 'saveFormData' && $id == 0)
        {   
            if($city_id == 0)
            {
                $info['state']  = 0;
                $info['msg'] = g2u('城市参数异常');

                echo json_encode($info);
                exit;
            }
            
            $num = M('erp_supplier')->where("CITY_ID = '".$city_id."' AND TYPE = 1 AND STATUS = 1")->count();
			if( $_POST['TYPE'] == 1 )
            {
				if($num >= 1)
				{
					$info['state']  = 0;
					$info['msg'] = g2u('只能添加一个领用采购商');

					echo json_encode($info);
					exit;
				}
			}
            $_POST['ADDTIME'] = date('Y-m-d H:i:s');
        }

        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(143)->where("CITY_ID = '".$this->channelid."'");
        
        if( !$this->isPost() && empty($_POST))
        {
	        //所在城市
	        $city_model = M('erp_city');
	        $city_arr = array();
	        $city_arr = $city_model->field('ID,NAME')->where("ISVALID = -1 AND ID IN (".$_SESSION['uinfo']['pocity'].")")->order('PY ASC')->select();
			if(is_array($city_arr) && !empty($city_arr))
			{
				foreach($city_arr as $key=>$value)
				{
					$temp_arr[$value['ID']] = $value['NAME'];
				}
				$form->setMyField('CITY_ID', 'LISTCHAR', array2listchar($temp_arr), FALSE);
				unset($city_arr);
				unset($temp_arr);
			}
            
			//添加人
			$input_arr = array(
					array('name' => 'USER_ID', 'val' => $uid, 'class' => 'USER_ID' )
			);
            
			$form = $form->addHiddenInput($input_arr);
        }
        
        $type_arr = array(0 => '采购供应商', 1 => '领用供应商');
        $layer_num = $_GET['layer_num'];
        if($layer_num == 1)
        {   
            $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), TRUE);
            
            //所在城市
	        $city_model = M('erp_city');
	        $city_arr = array();
	        $city_arr = $city_model->field('ID,NAME')->where("ID = '".$this->channelid."' AND ISVALID = -1")->find();
			if(is_array($city_arr) && !empty($city_arr))
			{   
                $temp_arr[$city_arr['ID']] = $city_arr['NAME'];
                $form->setMyField('CITY_ID', 'DEFAULTVALUE', $city_arr['ID'], TRUE);
				$form = $form->setMyField('CITY_ID', 'LISTCHAR', array2listchar($temp_arr), TRUE);
            }
            
            //状态参数
            $form->setMyField('STATUS', 'DEFAULTVALUE', '1', TRUE);
            
            //以弹框方式出现，替换原有保存，取消按钮，勿动
            $form->FORMCHANGEBTN = ' ';
        }
        else
        {   
            //供应商干类型
            $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
            $tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ? 
            $this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
            $this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));
        }
        
        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->display('supplier_manage');
    }
    
    
    /**
     +----------------------------------------------------------
     * 获取较低价的供应商
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function get_lower_price_supplier()
    {
    	/***采购明细MODEL***/
    	$purchase_list_model = D('PurchaseList');
    	
    	//采购明细编号
    	$purchase_list_id = intval($_GET['purchase_list_id']);
    	
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
            //价格限制
            $price_limit = $purchase_list_info[0]['PRICE_LIMIT'];
            //城市参数
            $city_id = $purchase_list_info[0]['CITY_ID'];
			
			$lower_price_purchase = array();
			$lower_price_purchase = $purchase_list_model->get_lower_price_by_search($brand, $model, $product_name, $city_id, 10);
            
			if(is_array($lower_price_purchase) && !empty($lower_price_purchase))
			{
				//查询供应商信息
				foreach($lower_price_purchase as $key => $value)
				{
					$sid_arr[] = $value['S_ID'];
				}
				
				/***供应商信息***/
				$supplier_model = D('Supplier');
				$supplier_info = array();
                
                
                $ids_str = implode(',', $sid_arr);
                $cond_where = " ID IN (".$ids_str.")  AND STATUS = 1";
				$supplier_info_temp = $supplier_model->get_info_by_cond($cond_where);
                
				if(!empty($supplier_info_temp) && !empty($supplier_info_temp))
				{	
					$supplier_status_arr = $supplier_model->get_conf_status_remark();
                    
					foreach($supplier_info_temp as $s_key => $s_val)
					{
						$supplier_info[$s_val['ID']]['s_id'] = $s_val['ID'];
						$supplier_info[$s_val['ID']]['s_name'] = $s_val['NAME'];
						$supplier_info[$s_val['ID']]['s_status'] = $supplier_status_arr[$s_val['STATUS']];
						$supplier_info[$s_val['ID']]['s_city'] = $s_val['CITY_ID'];
						$supplier_info[$s_val['ID']]['s_contact'] = $s_val['CONTACT'];
						$supplier_info[$s_val['ID']]['s_telno'] = $s_val['CONTACT_TELNO'];
						$supplier_info[$s_val['ID']]['s_address'] = $s_val['ADDRESS'];
					}
                    
					unset($supplier_info_temp);
				}
				
				$data_info = array();
				foreach($lower_price_purchase as $key => $value)
				{   
                    if(!empty($supplier_info[$value['S_ID']]['s_name']))
                    {
                        $data_info[$key]['id'] = $value['ID'];
                        $data_info[$key]['price'] = $value['PRICE'];
                        $data_info[$key]['s_id'] = $value['S_ID'];
                        $data_info[$key]['s_name'] = $supplier_info[$value['S_ID']]['s_name'];
                        $data_info[$key]['s_status'] = $supplier_info[$value['S_ID']]['s_city'];
                        $data_info[$key]['s_city'] = $supplier_info[$value['S_ID']]['s_city'];
                        $data_info[$key]['s_contact'] = $supplier_info[$value['S_ID']]['s_contact'];
                        $data_info[$key]['s_telno'] = $supplier_info[$value['S_ID']]['s_telno'];
                        $data_info[$key]['s_address'] = $supplier_info[$value['S_ID']]['s_address'];
                    }
				}
			}
        }
        
        $this->assign('data_info', $data_info);
        $this->display('lower_price_supplier');
    }
    
    
    /**
     +----------------------------------------------------------
     * 根据关键词获取供应商信息
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function get_supplier_by_keyword()
    {
        // 查询供应商
        $name =  u2g(urldecode($this->_request('keyword')));
        
        $data = array();
        if($name != '')
        {
            //查询
            $cond_where = "NAME LIKE '%".$name."%' ";
            $cond_where .= " AND STATUS = 1 AND TYPE = 0";
            $cond_where .= " AND CITY_ID = $this->channelid";
            $supplier_model = D('Supplier');
            $data = $supplier_model->get_info_by_cond($cond_where);
        }
        
        $supplier = array();
        if(is_array($data) && !empty($data))
        {
            foreach($data as $k => $v)
            {
                $supplier[$k]['id'] = $v['ID'];
                $supplier[$k]['label'] = g2u($v['NAME']);
                $supplier[$k]['telno'] =  g2u($v['CONTACT_TELNO']);
            }
        }
        else 
        {
            $supplier[0]['id'] = 0;
            $supplier[0]['label'] = g2u('无符合条件的供应商');
            $supplier[0]['telno'] = '';
        }
        
        echo json_encode($supplier);
    }
    
    
    /**
     +----------------------------------------------------------
     * 异步添加供应商
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function ajax_add_supplier_info()
    {   
        if(!empty($_POST))
        {
            $uid = intval($_SESSION['uinfo']['uid']);
            
            //供应商MODEL
            $supplier_model = D('Supplier');
            
            //供应商信息
            $add_info = array();
            $add_info['NAME'] = u2g(strip_tags($_POST['name']));
            $add_info['CITY_ID'] = intval($_POST['city_id']);
            $add_info['CONTACT'] =  u2g(strip_tags($_POST['truename']));
            $add_info['CONTACT_TELNO'] = u2g(strip_tags($_POST['telno']));
            $add_info['ADDRESS'] =  u2g(strip_tags($_POST['address']));
            $add_info['ADDTIME'] = date('Y-m-d H:i:s');
            $add_info['STATUS'] = intval($_POST['status']);
            //$info['STATUS'] = $conf_status['status'];
            $add_info['USER_ID'] = $uid;
            
            $insert_id = $supplier_model->add_supplier_info($add_info);

            if($insert_id > 0)
            {
                $info['state']  = 1;
                $info['supplier_id']  = $insert_id;
                $info['msg'] = g2u('供应商信息添加成功');
            }
            else
            {
                $info['state']  = 0;
                $info['supplier_id']  = 0;
                $info['msg'] = g2u('供应商信息添加失败');
            }
        }
        else
        {
            $info['state']  = 0;
            $info['supplier_id']  = 0;
            $info['msg'] = g2u('信息异常，操作失败');
        }
        
        echo json_encode($info);
        exit;
    }
}

/* End of file SupplierAction.class.php */
/* Location: ./Lib/Action/SupplierAction.class.php */