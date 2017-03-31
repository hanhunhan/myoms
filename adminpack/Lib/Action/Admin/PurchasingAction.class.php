<?php

/**
 * 采购人员功能控制器 
 */

class PurchasingAction extends ExtendAction{

    // 采购人员界面下删除采购明细权限
    const REJECT_PURCHASING = 779;
    
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();
    
    /**子页签编号**/
    private $_tab_number = 2;
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
        //TAB URL参数
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['purchase_id']) ? $this->_merge_url_param['purchase_id'] = $_GET['purchase_id'] : ''; 
		!empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
		!empty($_GET['beeId']) ? $this->_merge_url_param['beeId'] = $_GET['beeId'] : '';
		!empty($_GET['beeWork']) ? $this->_merge_url_param['beeWork'] = $_GET['beeWork'] : '';
		!empty($_GET['purchaseIds']) ? $this->_merge_url_param['purchaseIds'] = $_GET['purchaseIds'] : '';
		if (!empty($_GET['TAB_NUMBER'])){
		    $this->_merge_url_param['TAB_NUMBER'] = $_GET['TAB_NUMBER'];
		}else{
		    $this->_merge_url_param['TAB_NUMBER'] = $this->_tab_number;
		}
    }

    public function index() {
        $hasTabAuthority = $this->checkTabAuthority(2);
        if ($hasTabAuthority['result']) {
            $roleInfo = D('erp_role')->where("LOAN_ROLEID = {$hasTabAuthority['roleID']}")->find();
            $url = U("{$roleInfo['LOAN_ROLECONTROL']}/{$roleInfo['LOAN_ROLEACTION']}", $this->_merge_url_param);
            halt2('', $url);
            return;
        }
    }
    
    
    /**
    +----------------------------------------------------------
    *  采购明细
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function details()
    {
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $ischildren = $this->_get('ischildren');
        $parent_id = intval($this->_get('parentchooseid'));
        $id = intval($this->_get('ID'));
        Vendor('Oms.Form');
        $form = new Form();

        $form->initForminfo(137);
        
        if($ischildren == 1)
        {
            $form->FKFIELD = 'CONTRACT_ID';
            $form->where(" P_ID = ".$_SESSION['uinfo']['uid']);
            $form->SHOWCHECKBOX = 0;
            $form->DELABLE = -1;

            $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1');//采购申请人
            $form->setMyField('END_TIME', 'GRIDVISIBLE', -1);//最晚送达时间

            $yc_btn = '<a class = "delete_from_contract contrtable-link btn btn-info btn-sm" href="javascript:void(0);">从合同移除</a>';
            $param = array('ischildren' => $ischildren, 'parentchooseid' => $parent_id,  'showForm' => 2);
            $operate_url = U('Purchasing/details/', $param);
            $view_btn = "<a onclick=\"fthisShow(this,'".$operate_url."');\" fid = '".$id."' class=\"contrtable-link fedit btn btn-info btn-sm\" href=\"javascript:void(0);\">查看</a>";
            $form->CZBTN = array('%STATUS%==0' => $yc_btn.' '.$view_btn, '%STATUS% > 0' => $view_btn);
        }
        else
        {
            $form->SQLTEXT = "( SELECT A.*, B.END_TIME,P.Contract,P.PROJECTNAME from ERP_PURCHASE_LIST A LEFT JOIN "
                . " ERP_PURCHASE_REQUISITION B on A.PR_ID = B.ID "
                . " LEFT JOIN ERP_CASE C ON A.CASE_ID = C.ID "
                . " LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID "
                . "where (B.STATUS = 2 OR B.STATUS = 4) AND B.CITY_ID = '".$this->channelid."' AND (A.TYPE=2 OR (A.TYPE=1 AND A.FEE_ID!=58)) AND A.STATUS != 2 AND A.STATUS != 3)";
            $form->setMyField('PURCHASE_COST', 'GRIDVISIBLE', '0');//采购成本
            $form->setMyField('TOTAL_COST', 'GRIDVISIBLE', '0');//合计金额
            $form->setMyField('S_ID', 'GRIDVISIBLE', -1);//供应商
            $form->setMyField('END_TIME', 'GRIDVISIBLE', -1);//最晚送达时间
            $form->where("(CONTRACT_ID is null and STATUS != 4) and P_ID = ".$_SESSION['uinfo']['uid']);

            $form->GABTN = '<a href="javascript:;" id="edit_purchase" operate_type= "edit_purchase" fid = "0" class="btn btn-info btn-sm">采购</a>'
                . '<a href="javascript:;" onclick="get_lower_price();" id="lower_price" class="btn btn-info btn-sm">历史采购价格</a>';
            
//            $form->GABTN .= '<a href="javascript:;" id="addcontract" onclick="addcontract();" class="btn btn-info btn-sm">新增合同</a> '
//                    . ' <a href="javascript:;" onclick="aptocontract();" id="aptocontract" class="btn btn-info btn-sm">加入已有合同</a>';
            $form->GABTN .= '<a href="javascript:;" onclick="addReim();" id="add_reim" class="btn btn-info btn-sm">生成报销申请</a>';

            /***子页面***/
            $children = array(
                            array('领用明细',U('/Purchasing/use_detail_list')),
                            );
            $form->setChildren($children);
        }
		
        $form->setMyField('PRICE', 'GRIDVISIBLE', -1);//成交价
        $form->setMyField('NUM', 'GRIDVISIBLE', -1);//购买数量
        $form->setMyField('P_ID', 'GRIDVISIBLE', '0');
        $form->setMyField('STOCK_NUM', 'GRIDVISIBLE', '0');
        if($showForm > 0 )
        {
            //费用类型(树形结构)
            $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
                    . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
        }
        else
        {
            //费用类型
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
        }
        
        $list_arr = array(1 => '是', 0 => '否');
        
        //是否资金池
	    $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar($list_arr), FALSE);
        //是否扣非
	    $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar($list_arr), FALSE);
        
        //采购明细MDOEL
        $purchase_list_model = D('PurchaseList');
        $purchase_arr = $purchase_list_model->get_conf_list_status_remark();
        
        //状态信息
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($purchase_arr), FALSE);
        
        //供应商
        $form->setMyField('S_ID', 'EDITTYPE', 21, FALSE);
        $form = $form->setMyField('S_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);
        
        //采购人
        $form->setMyField('P_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('P_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
             
        //采购发起人
        $form->setMyField('APPLY_USER_ID', 'FORMVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('APPLY_USER_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
        
        //采购类型
        $purchase_model = D('PurchaseRequisition');
        $purchase_type_arr = $purchase_model->get_conf_purchase_type_remark();
        $form->setMyField('TYPE', 'GRIDVISIBLE', '-1', TRUE);
        $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($purchase_type_arr), FALSE);

        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('ischildren',$this->_get('ischildren'));
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('details');
    }
    
    
    /**
    +----------------------------------------------------------
    *  新增合同
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function add_contract()
    {   
        $uid = $_SESSION['uinfo']['uid'];
        
        //采购明细编号数组
        $purchaseId_arr = $_GET['purchaseId'];
        
        if(is_array($purchaseId_arr) && !empty($purchaseId_arr))
        {
            //采购明细
            $purchase_list_model = D('PurchaseList');
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchaseId_arr);
            
            //采购申请单
            $purchase_model = D('PurchaseRequisition');
            $purchase_type = $purchase_model->get_conf_purchase_type();
            
            //成本MODEL
            $cost_model = D('ProjectCost');
            
            if(is_array($purchase_list_info) && !empty($purchase_list_info))
            {
                $supplier_arr = array();
                $conract_num = 0;
                $data_empty_num = 0;
                $purchased_info = array();
                
                foreach($purchase_list_info as $key => $value)
                {
                    //供应商、采购数量、领用数量是否填写
                    if($value['TYPE'] == $purchase_type['project_purchase'])
                    {
                        //项目采购是否存在供应商、采购数量/领用数量没有填写的数据
                        if( ($value['S_ID'] == 0 || $value['NUM'] == 0) 
                                && $value['USE_NUM'] == 0)
                        {
                            $data_empty_num ++;
                            continue;
                        }
                    }
                    else if($value['TYPE'] == $purchase_type['bulk_purchase'])
                    {
                        //大宗采购是否存在供应商、采购数量为零的采购记录
                        if($value['S_ID'] == 0 || $value['NUM'] == 0 )
                        {
                            $data_empty_num ++;
                            continue;
                        }
                    }
                    
                    //供应商
                    $sid = intval($value['S_ID']);
                    $supplier_arr[$sid] = $sid;
                    
                    //是否存在已加入合同的
                    if( $value['CONTRACT_ID'] > 0)
                    {
                        $conract_num ++;
                        continue;
                    }
                    
                    //添加合同的采购明细编号
                    $purchased_info[$value['ID']] = $value['ID'];
                }
                
                //数据不完整
                if($data_empty_num > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('存在信息填写不完整的采购明细，无法新增合同！');
                    
                    echo json_encode($res);
                    exit;
                }
                
                //存在已加入合同的采购明细
                if($conract_num > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('存在已加入合同的采购明细，无法新增合同！');
                    
                    echo json_encode($res);
                    exit;
                }
                
                //供应商不相同提醒无法添加合同
                $supplier_num = count($supplier_arr);
                if($supplier_num > 1)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('采购明细供应商不一致，无法新增合同！');
                    echo json_encode($res);
                    exit;
                }
                
                //合同MODEL
                $contract_model = D('erp_contract');
                $cdata['PROMOTER'] = $uid;
                $cdata['STATUS'] = 0;
                $cdata['TYPE'] = 1;
                $cdata['ISSIGN'] = 0;
                $cdata['CITY_ID'] = $this->channelid;
                $cdata['SUPPLIER_ID'] = reset($supplier_arr);
                
                if($cdata['SUPPLIER_ID'] == 0)
                {   
                    //查询该城市领用供应商
                    $supplier_info = 
                            M('erp_supplier')->field('ID')->
                            where("CITY_ID = '".$this->channelid."' AND TYPE = 1 AND STATUS = 1")->
                            find();
                    
                    if(empty($supplier_info))
                    {
                        $res['status'] = 0;
                        $res['msg'] = g2u('添加失败，无领用供应商信息，需要在供应商管理添加有效的领用供应商信息！');
                        
                        echo json_encode($res);
                        exit;
                    }
                    
                    $cdata['SUPPLIER_ID'] = intval($supplier_info['ID']);
                }
                
                //添加合同
                $contract_id = $contract_model->add($cdata);
                
                if($contract_id > 0)
                {  
                    //采购申请单
                    $purchase_model = D('PurchaseRequisition');
                    
                    //明细已采购状态
                    $list_status = $purchase_list_model->get_conf_list_status();
                    
                    //更新采购填充采购合同编号
                    $update_num = $purchase_list_model->add_to_contract($purchased_info, $contract_id);
                }
                
                if($contract_id > 0 && $update_num > 0 )
                {
                    $res['status'] = 1;
                    $res['msg'] = g2u('新增合同成功！');
                }  
                else 
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('新增合同失败！');
                }
            }
            else
            {
                $res['status'] = 0;
                $res['msg'] = g2u('未找到相关采购明细！');
            }
        }
        else
        {
            $res['status'] = 0;
            $res['msg'] = g2u('请先选择采购明细！');
        }
        
        echo json_encode($res);
    }
    
    
    /**
    +----------------------------------------------------------
    *  加入已有合同
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function append_to_contract()
    {   
        $uid = $_SESSION['uinfo']['uid'];
        
        //采购明细编号数组
        $purchaseId_arr = $_REQUEST['selecttr'];;
        
        //合同编号
        $contract_id = intval($_REQUEST['aptocontractId']);
        
        if(is_array($purchaseId_arr) && !empty($purchaseId_arr) && $contract_id > 0)
        {   
            //采购明细
            $purchase_list_model = D('PurchaseList');
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchaseId_arr);
            
            //采购申请单
            $purchase_model = D('PurchaseRequisition');
            $purchase_type = $purchase_model->get_conf_purchase_type();
            
            if(is_array($purchase_list_info) && !empty($purchase_list_info))
            {   
                //供应商不同
                $supplier_diff_num = 0;
                //已加入合同
                $conract_num = 0;
                //数据不完整
                $data_empty_num = 0;
                $purchased_info = array();
                
                //根据合同编号查询供应商信息
                $contract_model = D('erp_contract');
                $contract_supplier_info = $contract_model->
                        where("ID = '".$contract_id."' ")->field('SUPPLIER_ID')->find();
                
                foreach($purchase_list_info as $key => $value)
                {
                    // 如果是全部领用的明细，则判断选择的合同是否是领用合同
                    // 如果不是领用合同，则给出提示，结束流程
                    if (intval($value['NUM']) == 0 && intval($value['USE_NUM']) > 0) {
                        $isFromStockContract = D('PurchaseContract')->isFromStockContract($contract_id);
                        if (!$isFromStockContract) {
                            echo json_encode(array(
                                'status' => 0,
                                'msg' => g2u('所选合同不是领用合同，当前为全部领用的采购，不能加入该合同')
                            ));
                            exit;
                        }
                    }

                    //供应商、采购数量、领用数量是否填写
                    if($value['TYPE'] == $purchase_type['project_purchase'])
                    {
                        //项目采购是否存在供应商、采购数量/领用数量没有填写的数据
                        if( ($value['S_ID'] == 0 || $value['NUM'] == 0) 
                                && $value['USE_NUM'] == 0)
                        {
                            $data_empty_num ++;
                            continue;
                        }
                    }
                    else if($value['TYPE'] == $purchase_type['bulk_purchase'])
                    {
                        //大宗采购是否存在供应商、采购数量为零的采购记录
                        if($value['S_ID'] == 0 || $value['NUM'] == 0 )
                        {
                            $data_empty_num ++;
                            continue;
                        }
                    }
                    
                    //供应商
                    if(( $value['S_ID'] > 0 && $contract_supplier_info['SUPPLIER_ID'] != $value['S_ID']))
                    {
                    	$supplier_diff_num ++;
                    	continue;
                    }
                    
                    //是否存在已加入合同的
                    if( $value['CONTRACT_ID'] > 0)
                    {
                        $conract_num ++;
                        continue;
                    }
                    
                    //添加合同的采购明细编号
                    $purchased_info[$value['ID']] = $value['ID'];
                }
                
                //采购供应商
                if($buy_not_supplier > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('供应商未填写，无法加入合同！');

                    echo json_encode($res);
                    exit;
                }
                
                //数据不完整
                if($data_empty_num > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('采购信息填写不完整的采购明细，无法加入合同！');
                    
                    echo json_encode($res);
                    exit;
                }
                
                //存在已加入合同的采购明细
                if($conract_num > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('存在已加入合同的采购明细，无法加入合同！');
                    
                    echo json_encode($res);
                    exit;
                }
                
                //供应商不相同提醒无法添加合同
                if($supplier_diff_num >= 1)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('采购明细供应商与已有合同供应商不一致，无法加入合同！');
                    echo json_encode($res);
                    exit;
                }
                
                if($contract_id > 0)
                {   
                    //采购申请单
                    $purchase_model = D('PurchaseRequisition');
                    //明细已采购状态
                    $list_status = $purchase_list_model->get_conf_list_status();
                    //更新采购明细状填充采购合同编号
                    $update_num = $purchase_list_model->add_to_contract($purchased_info, $contract_id);
                }
                
                if($contract_id > 0 && $update_num > 0 )
                {
                    $res['status'] = 1;
                    $res['msg'] = g2u('加入已有合同成功！');
                }  
                else 
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('加入已有合同失败！');
                }
            }
            else
            {
                $res['status'] = 0;
                $res['msg'] = g2u('未找到相关采购明细！');
            }
        }
        else
        {
            $res['status'] = 0;
            $res['msg'] = g2u('请先选择采购明细和采购合同！');
        }
        
        echo json_encode($res);
    }
    
    
    /**
    +----------------------------------------------------------
    *  采购物品领用明细
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function use_detail_list()
    {   
        $purchase_list_id = intval($_GET['parentchooseid']);
        
		Vendor('Oms.Form');			
		$form = new Form();
        $cond_where = "PL_ID = '".$purchase_list_id."'";
		$form->initForminfo(181)->where($cond_where);
        
        $warehouse_use_model = D('WarehouseUse');
        //状态
        $use_status_remark = $warehouse_use_model->get_conf_status_remark();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($use_status_remark), FALSE);    
		$form= $form->getResult();
		$this->assign('form',$form);
		$this->display('use_detail_list');
    }
    
	/**
    +----------------------------------------------------------
    *  采购合同
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
	public function contract()
    {   
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $contract_id = isset($_GET['ID']) ? intval($_GET['ID']) : '';
		$uid = $_SESSION['uinfo']['uid'];
		$contract = M('Erp_contract')->field('TYPE,SUPPLIER_ID')->where("ID=".$this->_get('ID'))->find();
        
		Vendor('Oms.Form');			
		$form = new Form();
		$form->initForminfo(150);
		
		if( $this->_get('layer') == 1)
        {   
			$form->where("TYPE = 1 AND ISSIGN = 0 AND PROMOTER = ".$uid." AND CITY_ID =". $this->channelid);//未提交的合同
			$form->SHOWSEQUENCE = 0;
			$form->DELABLE = 0;
			$form->EDITABLE = 0;
			$form->ADDABLE = 0;
			$form->GABTN = ' ';
			$form->GRIDAFTERDATA = '<div class="handle-btn"><input type="button" value="确&nbsp;定" onclick="parent.submitaptocontract();" class="btn btn-primary" />  <input type="button" value="关&nbsp;闭" class="j-pageclose btn btn-default" onclick="parent.layer.closeAll();" /></div>';
			$form->setMyField('PROMOTER','GRIDVISIBLE','0');
			$form->setMyField('TYPE','GRIDVISIBLE','0');
			$form->setMyField('STATUS','GRIDVISIBLE','0');
			$form->setMyField('ISSIGN','GRIDVISIBLE','0');
			$form->setMyField('FILEURL','GRIDVISIBLE','0');
		}
        else
        {   
			$form->DELABLE = 0;
			$form->ADDABLE = 0;
			
			$form->CZBTN = array( 
                '%ISSIGN%==-1' => '<a href="javascript:;" onclick="fthisShow(this,\''.U('Purchasing/contract','&kjcontract='.$this->_get('kjcontract')).'\');" class="contrtable-link btn btn-success btn-xs" title="查看"><i class="glyphicon glyphicon-eye-open"></i></a>',
                '%ISSIGN%==0' => '<a href="javascript:;" onclick="fthisedit(this,\''.U('Purchasing/contract','&kjcontract='.$this->_get('kjcontract')).'\');" class="contrtable-link btn btn-primary btn-xs" title="编辑"><i class="glyphicon glyphicon-edit"></i></a><a href="javascript:;" onclick="fthisShow(this,\''.U('Purchasing/contract','&kjcontract='.$this->_get('kjcontract')).'\');" class="contrtable-link btn btn-success btn-xs" title="查看"><i class="glyphicon glyphicon-eye-open"></i></a><a href="javascript:;" onclick="fthisDelContract(this );" class="contrtable-link btn btn-danger btn-xs" title="删除"><i class="glyphicon glyphicon-trash"></i></a>');
			$form->GABTN = '<a href="javascript:void(0);" onclick="addkjcontract();" class="btn btn-info btn-sm">新增框架合同</a>
					<a href="javascript:void(0);" onclick="addreimbursement();" class="btn btn-info btn-sm">生成报销申请</a>';
            
			$children = array(
				array('采购明细',U('/Purchasing/details?ischildren=1')),
			);
            
			$form->setChildren($children);
            
			if($this->_get('kjcontract') == 1 || $contract['TYPE'] == 2)
            {
				$form->setMyField('RELATIONID','FORMVISIBLE','0');
				$form->setMyField('REIM_STATUS','FORMVISIBLE','0');
				$form->setMyField('REIM_LIST_ID','FORMVISIBLE','0');
				$form->setMyFieldVal('TYPE','2',true);//框架合同
				$form->setMyFieldVal('PROMOTER',$uid,true);
			}
            else
            {
				$form->setMyFieldVal('TYPE',1 , true);
                $form->setMyField('FILEURL', 'NOTNULL' , '0', false);
			}
            if($this->_get('kjcontract') == 1){
				$form->where("TYPE = 2  AND  CITY_ID =". $this->channelid);
				$form->setMyField('RELATIONID','GRIDVISIBLE','0');
				$form->GABTN = '<a href="javascript:void(0);" onclick="addkjcontract();" class="btn btn-info btn-sm">新增框架合同</a>';
				$form->setChildren(null);
			}else 
			$form->where("TYPE = 1 AND PROMOTER = ".$uid." AND CITY_ID = ". $this->channelid);
		}
        
        /***保存合同***/
		if($_REQUEST['faction'] == 'saveFormData')
        {   
            //未提交的合同
			$form->setMyFieldVal('STATUS', '0' , TRUE);
			$form->setMyFieldVal('PROMOTER' , $uid);
            
            $id = intval($_POST['ID']);
            $issign = intval($_POST['ISSIGN']);
            $issign_old = intval($_POST['ISSIGN_OLD']);
            $contract_type = intval($_POST['TYPE']);
            
            /***判断合同是否上传***/
            if($_POST['FILEURL'] == '' && $contract_type == 2)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('保存失败，合同必须上传!');

                echo json_encode($result);
                exit;
            }

            //如果状态修改为已签约
            if($id > 0 && $issign == -1 && $issign_old == 0)
            {   
                //采购明细MODEL
                $purchase_list_model = D('PurchaseList');
                
                //根据合同编号获取合同绑定所有采购明细
                $purchase_list_info = $purchase_list_model->get_purchase_list_by_contract_id($id);
                
                if(is_array($purchase_list_info) && !empty($purchase_list_info))
                {   
                    //成本MODEL
                    $cost_model = D('ProjectCost');
                    
                    //采购申请单MODEL
                    $purchase_model = D('PurchaseRequisition');
                    
                    //仓库领用MODEL
                    $warehouse_use_model = D('WarehouseUse');
                    
                    //采购类型
                    $purchase_type = $purchase_model->get_conf_purchase_type();
                    
                    foreach ($purchase_list_info as $key => $value)
                    {   
                        //采购全部通过仓库领用的明细特殊处理
                        if($value['USE_NUM'] > 0  && $value['NUM'] == 0 )
                        {
                            //更新采购明细状态为报销
                            $update_num = $purchase_list_model->update_to_reimbursed_by_id($value['ID']);

                            //更新采购申请单已采购(根据采购单编号查询下面所有采购明细是否已经都采购完成)
                            $is_all_purchased = $purchase_list_model->is_all_purchased($value['PR_ID']);

                            if($is_all_purchased)
                            {
                                $finish_result = $purchase_model->update_to_finished_by_id($value['PR_ID']);
                            }
                            
                            //更新合同为已报销
                            $purchase_contract_model = D('PurchaseContract');
                            $contract_reimed = $purchase_contract_model->sub_contract_to_reimbursed_by_listid($value['CONTRACT_ID']);
                            if ($contract_reimed > 0) {
                                // 状态值为2代表已报销
                                $_POST['REIM_STATUS'] = 2;
                                $_REQUEST['REIM_STATUS'] = 2;

                                // 在成本表里添加一条已报销的采购申请
                                $this->addReimedCostList($value, $uid);
                            }
                        }
                        else
                        {
                            //更新采购明细状态为已采购
                            $update_num = $purchase_list_model->update_to_purchased_by_id($value['ID']);

                            //更新采购申请单已采购(根据采购单编号查询下面所有采购明细是否已经都采购完成)
                            $is_all_purchased = $purchase_list_model->is_all_purchased($value['PR_ID']);

                            if($is_all_purchased)
                            {
                                $finish_result = $purchase_model->update_to_finished_by_id($value['PR_ID']);
                            }

                            //项目采购明细入成本记录
                            if($value['TYPE'] == $purchase_type['project_purchase'])
                            { 
                                //项目采购确认领用
                                if($value['USE_NUM'] > 0)
                                {
                                    $use_confirm_result = 
                                        $warehouse_use_model->confirm_used_by_purchase_id($value['ID']);
                                }

                                /***成本数组***/
                                $cost_info = array();
                                $cost_info['CASE_ID'] = $value['CASE_ID'];
                                $cost_info['ENTITY_ID'] = $value['PR_ID'];
                                $cost_info['EXPEND_ID'] = $value['ID'];
                                $cost_info['ORG_ENTITY_ID'] = $value['PR_ID'];
                                $cost_info['ORG_EXPEND_ID'] = $value['ID'];
                                $cost_info['ADD_UID'] = $uid;
                                $cost_info['OCCUR_TIME'] = $value['ADD_TIME'];
                                $cost_info['ISKF'] = $value['IS_KF'];
                                $cost_info['ISFUNDPOOL'] = $value['IS_FUNDPOOL'];
                                $cost_info['FEE_ID'] = $value['FEE_ID'];

                                /***采购部分成本插入到成本表中***/
                                $buy_cost = $value['PRICE'] * $value['NUM'];
                                if($buy_cost > 0)
                                {
                                    $cost_info['EXPEND_FROM'] = 2;  //采购合同签订
                                    $cost_info['FEE'] = $buy_cost;
                                    $cost_info['FEE_REMARK'] = '采购合同签订';

                                    $result_buy = $cost_model->add_cost_info($cost_info);
                                }

                                //领用大于零才添加成本
                                if($value['USE_NUM'] > 0)
                                {
                                    /***领用部分进入成本库，已报销状态***/
                                    $cost_info['EXPEND_FROM'] = 4;  //已报销
                                    $cost_info['FEE'] = $value['USE_TOATL_PRICE'];
                                    $cost_info['FEE_REMARK'] = '采购领用成本';

                                    $result_use = $cost_model->add_cost_info($cost_info);
                                }
                            }
                        }
                    }
                }
            }
            else if($id == 0)
            {   
                //合同MODEL
                $contract_model = D('erp_contract');
                
                $cdata['CONTRACTID'] = strip_tags(u2g($_POST['CONTRACTID']));
                $cdata['PROMOTER'] = $uid;
                $cdata['TYPE'] = $contract_type;
                $cdata['SUPPLIER_ID'] = intval($_POST['SUPPLIER_ID']);
                $cdata['SIGINGTIME'] = addslashes($_POST['SIGINGTIME']);
                $cdata['REIM_STATUS'] = intval($_POST['REIM_STATUS']);
                $cdata['ISSIGN'] = $issign;
                $cdata['FILEURL'] = u2g($_POST['FILEURL']);
                $cdata['CITY_ID'] = $this->channelid;
                
                //添加合同
                $contract_id = $contract_model->add($cdata);
                
                if($contract_id > 0)
                {
                    $result['status'] = 1;
                    $result['msg'] = g2u('添加成功!');
                }
                else
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败!');
                }
                
                echo json_encode($result);
                exit;
            }
		}
        
        /***供应商***/
        if($showForm == 1 && $contract_id > 0)
        {   
            //修改
            $contract = M('Erp_contract')->field('TYPE,SUPPLIER_ID')->where("ID=".$this->_get('ID'))->find();
            $supplier_id = $contract['SUPPLIER_ID'];
            $sql = "SELECT ID,NAME FROM ERP_SUPPLIER WHERE (STATUS = 1 AND CITY_ID = '".$this->channelid."') OR ID = '".$supplier_id."'  ";
            $form->setMyField( 'SUPPLIER_ID', 'LISTSQL', $sql,true);
        }
        //新增框架合同
        else if($showForm == 1)
        {
            $form->setMyField( 'SUPPLIER_ID', 'LISTSQL', 
                "SELECT ID,NAME FROM ERP_SUPPLIER WHERE STATUS = 1 AND CITY_ID =". $this->channelid);
        }
        else
        {
            $form->setMyField( 'SUPPLIER_ID', 'LISTSQL', 
                'SELECT ID,NAME FROM ERP_SUPPLIER WHERE 1 = 1 AND CITY_ID ='. $this->channelid);
        }
        
        $form->setMyField( 'RELATIONID', 'LISTSQL' , 
                'SELECT ID,CONTRACTID FROM ERP_CONTRACT where TYPE = 2 AND CITY_ID = '.$this->channelid);
        
        //报销状态
        $purchase_contract_model = D('PurchaseContract');
        $conf_reim_status = $purchase_contract_model->get_conf_reim_status_remark();
        $form->setMyField('REIM_STATUS', 'LISTCHAR' , array2listchar($conf_reim_status), TRUE);
        
		$formHtml = $form->getResult();
		$this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
		$this->assign('layer',$this->_get('layer'));
		$this->assign('paramUrl',$this->_merge_url_param);
		if($this->_get('kjcontract') != 1){
		$this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param)); 
		}
		$this->display('contract');
	}
    
    
	/**
	 +----------------------------------------------------------
	 * 删除合同 
	 +----------------------------------------------------------
	 * @param none
	 +----------------------------------------------------------
	 * @return none
	 +----------------------------------------------------------
	 */
	public function del_contract(){ 
		$contract_id = $this->_get('contract_id');
		$data['CONTRACT_ID']='';
		$contract = M('Erp_purchase_list')->where("CONTRACT_ID=$contract_id")->find();
		$res = M('Erp_purchase_list')->where("CONTRACT_ID=$contract_id")->save($data);
		$data2['STATUS'] =2 ;
		$res = M('Erp_purchase_requisition')->where("ID=".$contract['PR_ID'])->save($data2);
		$ress = M('Erp_contract')->where("ID=$contract_id")->delete();
		if($ress) {
			$result['status']=1;
			$result['info']=u2g('成功');
		}else{
			$result['status'] = 0;
			$result['info']=u2g('失败');
		}
        
		 echo json_encode($result);
	}
    
    
	/**
	 +----------------------------------------------------------
	 * 采购报销管理
	 +----------------------------------------------------------
	 * @param none
	 +----------------------------------------------------------
	 * @return none
	 +----------------------------------------------------------
	 */
	public function reim_manage()
	{   
		//报销MODEL
		$reim_type_model = D('ReimbursementType');
		$reim_list_model = D('ReimbursementList');
		$reim_detail_model = D('ReimbursementDetail');
        
		$uid = intval($_SESSION['uinfo']['uid']);
		$city = $this->channelid;
		$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
		$showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
		
        //报销单状态
        $reim_list_status = $reim_list_model->get_conf_reim_list_status();
		Vendor('Oms.Form');
		$form = new Form();
		$cond_where = "APPLY_UID = '".$uid."' AND CITY_ID = '".$city."' "
				. "AND TYPE IN (1, 14, 15) AND STATUS != '".$reim_list_status['reim_deleted']."'";
		$form = $form->initForminfo(176)->where($cond_where);

        $form->SQLTEXT = '(select a.*,b.LOANMONEY  from ERP_REIMBURSEMENT_LIST a
        left join (select  APPLICANT,sum(UNREPAYMENT) as LOANMONEY from ERP_LOANAPPLICATION where STATUS IN(2,6) AND CITY_ID = '. $city . ' group by APPLICANT) b
        on a.APPLY_UID = b.APPLICANT )';
        
		if($faction == 'delData') {
            //报销单编号
			$list_id = intval($_GET['ID']);
            
			$del_list_result = FALSE;
			$del_detail_result = FALSE;
            M()->startTrans();
			if($list_id > 0)
			{   
                $reim_detail = $reim_list_model->get_info_by_id($list_id ,array('TYPE'));
				//通过报销单编号获取报销明细中的合同编号
                $reim_detail_model = D('ReimbursementDetail');

                //回退领用数据
                $returnUse = D("InboundUse")->backDisplaceUse($list_id);
                
                //删除报销单
                $del_list_result = $reim_list_model->del_reim_list_by_ids($list_id);
                //删除报销明细
                $del_detail_result = $reim_detail_model->del_reim_detail_by_listid($list_id);


				if($reim_detail[0]['TYPE']==15){
					$reim_detail_list = $reim_detail_model->get_detail_info_by_listid($list_id,array('PURCHASER_BEE_ID'));
					foreach($reim_detail_list as $one){
						$update_arr['STATUS'] = 0;
						$conf_where['ID'] = $one['PURCHASER_BEE_ID'];
						$up_num_contract = D('PurchaseBeeDetails')->update_bee_detail_info($update_arr,$conf_where);//var_dump($conf_where);
						if(!$up_num_contract) break;
					}
					 
				}else{
					//更新采购合同报销状态为未申请
					$purchase_contract_model = D('PurchaseContract');
//					$up_num_contract = $purchase_contract_model->sub_contract_to_reim_not_apply_by_reim_listid($list_id);

                    // 更新采购明细的状态为已采购
                    $updatedPurchase = $this->afterReimDetailDel($list_id, $reim_detail[0]['TYPE']);


				}
                //删除关联借款关系
                $loan_model = D('Loan');
//                $up_num_loan = $loan_model->cancle_related_loan_by_reim_ids($list_id);
                $up_num_loan = $loan_model->cancleRelatedLoan($list_id);
			}
            //var_dump($del_list_result); var_dump($del_detail_result); var_dump($up_num_contract);
//			if($up_num_contract && $del_list_result && $del_detail_result)
			if($updatedPurchase !== false && $del_list_result && $del_detail_result)
			{
				M()->commit();
				$info['status']  = 'success';
				$info['msg']  = g2u('删除成功');
			}
			else if(!$del_detail_result)
			{
				M()->rollback();
				$info['status']  = 'error';
				$info['msg']  = g2u('报销明细删除失败');
			}
			else
			{
				M()->rollback();
				$info['status']  = 'error';
				$info['msg']  = g2u('删除失败');
			}
	
			echo json_encode($info);
			exit;
		} else if ($faction == 'saveFormData') {  // 保存数据
            $reimListId = $_POST['ID'];
            $attachment = u2g($_POST['ATTACHMENT']);
            $dbResult = D('ReimbursementList')->where("ID = {$reimListId}")->save(array('ATTACHMENT' => $attachment));

            if ($dbResult !== false) {
                ajaxReturnJSON(true, u2g('报销申请修改成功'));
            } else {
                ajaxReturnJSON(false, u2g('报销申请修改失败'));
            }
        }
        
        if($this->_merge_url_param['flowId'] > 0)
        {
            //工作流入口编辑权限
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

            if($flow_edit_auth)
            {   
                //允许编辑
                $form->EDITABLE = -1;
                $form->GABTN = "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>关联借款</a>";
                $form->ADDABLE = '0';
            }
            else
            {
                $form->DELCONDITION = '1==0';
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = '0'; 
                $form->GABTN = '';
            }
        }
        else
        {
            //根据状态控制删除按钮是否显示
            $form->DELCONDITION = '%STATUS% == 0';
            $form->EDITCONDITION = '%STATUS% == 0 AND (%TYPE% == 1 OR %TYPE% == 14)';

            $form->GABTN = "<a id='sub_reim_apply' href='javascript:;' class='btn btn-info btn-sm'>提交报销申请</a>  "
                    . "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>关联借款</a>";
                //."<a id = 'show_flow_step'  href='javascript:;' class='btn btn-info btn-sm'>超额报销流程图</a>";
        }
        
		//设置报销单类型
		$type_arr = $reim_type_model->get_reim_type();
		$form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
		 
		//设置报销单状态
		$status_arr = $reim_list_model->get_conf_reim_list_status_remark();
		$form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr), FALSE);
		 
		//详情页
		if($showForm > 0)
		{
			//审核人
			$form = $form->setMyField('REIM_UID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
		}
	
		$children_data = array(
				array('报销明细', U('/Purchasing/reim_detail_manage', 'fromTab=2')),
				array('关联借款', U('Loan/related_loan'))
		);
        
		$form =  $form->setChildren($children_data);
		$formHtml = $form->getResult();
		$this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
		$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
		$this->display('reim_manage');
	}
	
	
	/**
	 +----------------------------------------------------------
	 * 报销明细
	 +----------------------------------------------------------
	 * @param none
	 +----------------------------------------------------------
	 * @return none
	 +----------------------------------------------------------
	 */
	public function reim_detail_manage()
	{
		$list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
		$uid = intval($_SESSION['uinfo']['uid']);
		$city = $this->channelid;
		$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
		$showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
		$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
		 
		//报销申请单MODEL
		$reim_list_model = D('ReimbursementList');
		//报销MODEL
		$reim_detail_model = D('ReimbursementDetail');
		//报销类型
		$reim_type_model = D('ReimbursementType');
		$list_info = $reim_list_model->get_info_by_id($list_id, array('STATUS','TYPE')); 
		Vendor('Oms.Form');
		$form = new Form();
        //不显示已删除的数据
		if($faction == 'delData')
		{
			$id = intval($_GET['ID']);

            //数据验证
            //删除明细剩余金额不能小于借款金额
            if(D("Loan")->checkDelReim($list_id,$id)){
                $info['status']  = 'error';
                $info['msg']  = g2u('对不起，您此报销单关联的借款金额已大于报销金额，删除失败!');
                die(json_encode($info));
            }

			$del_detail_result = FALSE;
			$up_list_result = FALSE;

            //回退领用数据
            $returnUse = D("InboundUse")->backDisplaceUse($list_id);
			 
			if($id > 0)
			{
				$del_detail_result = $reim_detail_model->del_reim_detail_by_id($id);
                $reimDetail = $reim_detail_model->where("ID = {$id}")->find();
				if($del_detail_result)
				{
					$total_amount = $reim_detail_model->get_sum_total_money_by_listid($list_id);
					$up_list_result = $reim_list_model->update_reim_list_amount($list_id, $total_amount, 'cover');
                    if ($up_list_result !== false) {
                        $updatedPurchaseList = D('PurchaseList')->reset2NotPurchase($id);
                    }

                    // 删除纯领用的成本
                    $deleted = D('ProjectCost')->where("ORG_ENTITY_ID = {$reimDetail['BUSINESS_PARENT_ID']} AND ORG_EXPEND_ID = {$reimDetail['BUSINESS_ID']} AND EXPEND_FROM = 4 AND STATUS = 4")->delete();
				}
			}
			 
			if($del_detail_result > 0 && $up_list_result > 0 && $updatedPurchaseList > 0 && $deleted !== false && $returnUse!==false)
			{
				$info['status']  = 'success';
				$info['msg']  = g2u('删除成功');
			}
			else if(!$up_list_result)
			{
				$info['status']  = 'error';
				$info['msg']  = g2u('报销申请单金额更新失败');
			}
			else
			{
				$info['status']  = 'error';
				$info['msg']  = g2u('删除失败');
			}
			 
			echo json_encode($info);
			exit;
		}
		else if($faction == 'saveFormData' && $id > 0 )
		{	
			$reim_detail_info = $reim_detail_model->get_detail_info_by_id($id);
			
			if(is_array($reim_detail_info) && !empty($reim_detail_info))
			{
				//报销金额填写时需要判断不能大于采购单价*采购数量
				$buy_price = floatval($_POST['PRICE']);
				$buy_num = floatval($_POST['NUM']);
				$money = floatval($_POST['MONEY']);
				if($money <= $buy_price * $buy_num)
				{
					$list_id = $reim_detail_info[0]['LIST_ID'];
                
					$update_arr = array();
					$update_arr['MONEY'] = $money; 
					$up_result = $reim_detail_model->update_reim_detail_by_id($id, $update_arr);
					
					if($up_result)
					{
						$total_amount = $reim_detail_model->get_sum_total_money_by_listid($list_id);
						//var_dump($total_amount);
						$up_list_result = $reim_list_model->update_reim_list_amount($list_id, $total_amount, 'cover');
					}
					
					if($up_result > 0 && $up_list_result > 0)
					{
						$result['status'] = 1;
						$result['msg'] = '修改成功';
					}
					else
					{
						$result['status'] = 0;
						$result['msg'] = '修改失败';
					}
				}
				else
				{
					$result['status'] = 0;
					$result['msg'] = '修改失败，报销金额不能大于采购单价*采购数量';
				}
			}
			else
			{
				$result['status'] = 0;
				$result['msg'] = '修改失败，无相关报销明细信息';
			}
		
			$result['msg'] = g2u($result['msg']);
			echo json_encode($result);
			exit;
		}
		$reim_details_status = $reim_detail_model->get_conf_reim_detail_status();
		$cond_where = "LIST_ID = '".$list_id."' AND STATUS != '".$reim_details_status['reim_detail_deleted']."'";
		if ($list_info[0]['TYPE']==15){
		    $form = $form->initForminfo(198)->where($cond_where);
		    //是否资金池
		    $form = $form->setMyField('ISFUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '是', 0 => '否')), FALSE);
		    //是否扣非
		    $form = $form->setMyField('ISKF', 'LISTCHAR', array2listchar(array(1 => '是', 0 => '否')), FALSE);
		    $file1_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="1" href="javascript:void(0);">汇总表</a>';
		    $file2_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="2" href="javascript:void(0);">明细表</a>';
            $file3_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="3" href="javascript:void(0);">带看奖明细</a>';
		    $form->CZBTN = $file1_btn.' '.$file2_btn.' '.$file3_btn;
		}else{ 
			$reimDetail = M('Erp_reimbursement_detail')->where("ID=".$_REQUEST['ID'])->find(); 
		    $form = $form->initForminfo(182)->where($cond_where);
            $form->SQLTEXT = <<<SQLTEXT
                (SELECT L.BRAND,
                          L.MODEL,
                          L.NUM,
                          L.NUM_LIMIT,
                          L.PRICE,
                          L.PRICE_LIMIT,
                          L.PRODUCT_NAME,
                          L.P_ID,
                          L.ADD_TIME,
                          getReimPurchaseContractId(L.CONTRACT_ID, B.ID) AS CONTRACT_ID,
                          L.COST_OCCUR_TIME,
                          L.PURCHASE_OCCUR_TIME,
                          D.*,
                          B.PROJECTNAME,
                          S.NAME SUPPLIER_NAME
               FROM ERP_REIMBURSEMENT_DETAIL D
               LEFT JOIN ERP_CASE A ON D.CASE_ID = A.ID
               LEFT JOIN ERP_PROJECT B ON A.PROJECT_ID = B.ID
               INNER JOIN ERP_PURCHASE_LIST L ON D.BUSINESS_ID = L.ID
               INNER JOIN ERP_PURCHASE_REQUISITION PR ON D.CASE_ID = PR.CASE_ID
               INNER JOIN ERP_SUPPLIER S ON S.ID = L.S_ID
               AND PR.ID = L.PR_ID)
SQLTEXT;

		    /***根据状态控制编辑删除按钮是否显示***/
		    $reim_list_status = !empty($list_info[0]['STATUS']) ? intval($list_info[0]['STATUS']) : 0;
		    $conf_reim_list_status = $reim_list_model->get_conf_reim_list_status();
		    if($conf_reim_list_status['reim_list_no_sub'] == $reim_list_status)
		    {
		        $form->EDITABLE = '-1';  // 可编辑
                $form->DELABLE = '-1';  // 可删除
		    } 
			if($reimDetail['TYPE']==14)  $form->setMyField('PROJECTNAME', 'NOTNULL', '0', true);
		    //合同编号
//		    $form->setMyField('CONTRACT_ID', 'LISTSQL', 'SELECT ID, CONTRACTID FROM ERP_CONTRACT', TRUE);
		    //采购人
		    $form->setMyField('P_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
		    $form->setMyField('ISFUNDPOOL', 'READONLY', '-1', TRUE);
		    $form->setMyField('ISKF', 'READONLY', '-1', TRUE);
            if ($showForm == 1) {  // 报销明细的编辑状态费用发生时间不可编辑
                $form->setMyField('COST_OCCUR_TIME', 'READONLY', '-1', TRUE);
            }
		}
		//设置报销明细类型
		$type_arr = $reim_type_model->get_reim_type();
		$form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
		//报销明细状态
		$reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
		$form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
		//进项税只读
		$form->setMyField('INPUT_TAX', 'READONLY', '-1', TRUE);
		//费用类型
		if($showForm > 0 ){
		    //费用类型(树形结构)
		    $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
		    $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
		            . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
		}else{
		    //费用类型
		    $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
		            . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
		}
        $form->setMyField('INPUT_TAX','GRIDVISIBLE',0,false);
        $form->setMyField('INPUT_TAX','FORMVISIBLE',0,false);
		$form = $form->getResult();
		$this->assign('form', $form);
		$this->display('reim_detail_manage');
	}
    
    
    /**
     +----------------------------------------------------------
     * 异步更新采购明细购买情况
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function ajax_update_purchase_buy_info() {
        $purchaseData = $_POST['purchase_data'];
        if (notEmptyArray($purchaseData)) {
            D()->startTrans();
            $dbResult = false;
            $msg = '';
            foreach($purchaseData as $k => $v) {
                $dbResult = $this->updatePurchaseList($v, $msg);
                $purchaseType = $dbPurchase = D('PurchaseList')->where("ID = {$v['purchase_id']}")->getField('type');
                if ($purchaseType == 1) {
                    if ($dbResult !== false) {
                        $dbResult = D('ProjectCost')->insertOrUpdateCostList($v['purchase_id'], $msg, false);
                    }
                }

                if ($dbResult === false) {
                    break;
                }
            }

            if ($dbResult !== false) {
                D()->commit();
                ajaxReturnJSON(1, g2u('采购信息更新成功'));
            } else {
                D()->rollback();
                $msg = empty($msg) ? '采购信息更新失败' : $msg;
                ajaxReturnJSON(0, g2u($msg));
            }
        }
    }
    
    
    /**
     +----------------------------------------------------------
     * 退款明细从退款单中删除
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function delete_from_contract()
    {
        //删除的退款单编号
        $purchase_details_id = intval($_POST['purchase_details_id']);
        
        if($purchase_details_id > 0)
        {   
            $purchase_list_model = D('PurchaseList');
            $update_num = $purchase_list_model->delete_from_contract($purchase_details_id);
            
            if($update_num > 0)
            {   
                $info['state']  = 1;
                $info['msg']  = '从合同中移除成功';
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '从合同中移除失败';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '参数错误';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * 审批意见
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    function opinionFlow()
    {   
        $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//项目ID
        $uid = intval($_SESSION['uinfo']['uid']);
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();
        
        $type = $_REQUEST['FLOWTYPE'] ? $_REQUEST['FLOWTYPE'] : "baoxiaoshenqing";
        
        $flowId = $_REQUEST['flowId'];
        $recordId = $_REQUEST['RECORDID'];
		$caseId = $_REQUEST['CASEID'];
        
        if($flowId)
        {
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata']){
                if($_REQUEST['flowNext']){
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str){
                        js_alert('办理成功',U('Flow/workStep'));
                    }else{
                        js_alert('办理失败');
                    }
                }elseif($_REQUEST['flowPass']){
					
                    $str = $workflow->passWorkflow($_REQUEST);
                    if($str){
                        js_alert('同意成功',U('Flow/workStep'));
                    }else{
                        js_alert('已同意失败');
                    }
                }elseif($_REQUEST['flowNot']){
					
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str){
                        js_alert('否决成功',U('Flow/workStep'));
                    }else{
                        js_alert('否决失败');
                    }

                }elseif($_REQUEST['flowStop']){

					$auth = $workflow->flowPassRole($flowId);
					if(!$auth){
						js_alert('未经过必经角色');exit;
					}

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str){
                        $param = array(
                            'prj_id' => '',
                            'prj_name' => '',
                            'p_id' => '',
                            'p_name' => '',
                            'price_limit' => '',
                            'num_limit' => '',
                            'city' => '',
                            'pro_listid' => '',
                            'rel_newhouseid' => '',
                            'rel_newhouse' => '',
                            'end_time' => '',
                            'key' => '',
                        );
                        $this->_zk_api($param);
                        js_alert('备案成功',U('Flow/workStep'));
                    }else{
                        js_alert('备案失败');
                    }
                }
				exit;
            }
        }
        else
        {
            $auth = $workflow->start_authority($type);

            $form = $workflow->createHtml();

            if($_REQUEST['savedata'])
            {   
                $purchase_id = !empty($_GET['purchase_id']) ? intval($_GET['purchase_id']) : 0;

                $flow_data['type'] = $type; 
                $flow_data['CASEID'] = $caseId;
                $flow_data['RECORDID'] = $recordId;
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES']; 
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']); 
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']); 

                $str = $workflow->createworkflow($flow_data);
                if($str)
                {   
                    $purchase_model = D('Purchase');
                    $up_num = $purchase_model->submit_purchase_by_id($purchase_id);
                    js_alert('提交成功',U('Purchasing/opinionFlow',$this->_merge_url_param));
                    exit;
                }
                else
                {
                    js_alert('提交失败',U('Purchasing/opinionFlow',$this->_merge_url_param));
                    exit;
                }
            }
        }
        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('current_url', U('Purchasing/opinionFlow',$this->_merge_url_param));
		$this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('opinionFlow');
    } 
    /**
     * 小蜜蜂采购列表页（同采购明细）
     */
    public function bee(){
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $ischildren = $this->_get('ischildren');
        $parent_id = intval($this->_get('parentchooseid'));
        $id = intval($this->_get('ID'));
        Vendor('Oms.Form');
        $form = new Form();

        $form->initForminfo(195);
        
        $form->SQLTEXT = "( SELECT A.*, B.END_TIME, P.PROJECTNAME from ERP_PURCHASE_LIST A LEFT JOIN "
            . " ERP_PURCHASE_REQUISITION B on A.PR_ID = B.ID LEFT JOIN ERP_PROJECT P ON P.ID = B.PRJ_ID where A.FEE_ID=58 AND B.STATUS = 2 AND A.TYPE = 1 AND B.CITY_ID = '".$this->channelid."')";
        
        $form->setMyField('PURCHASE_COST', 'GRIDVISIBLE', '0');//采购成本
        $form->setMyField('TOTAL_COST', 'GRIDVISIBLE', '0');//合计金额
        $form->setMyField('S_ID', 'GRIDVISIBLE', -1);//供应商
        $form->setMyField('END_TIME', 'GRIDVISIBLE', -1);//最晚送达时间
       if($_REQUEST['beeId']){
		   $form->where("ID=".$_REQUEST['beeId']);
	   }else {
		   $form->where("CONTRACT_ID is null and P_ID = ".$_SESSION['uinfo']['uid']);
	   }
        $zk_send_btn = "<a class=\"contrtable-link fedit send_zk\" href=\"javascript:void(0);\" class='btn btn-info btn-sm'>同步</a>";
        $form->CZBTN = array('%ZK_STATUS%==0' => $zk_send_btn);
        /***子页面***/
		if($_REQUEST['purchaseIds']|| $_REQUEST['beeId']){
			$url = '/Purchasing/bee_detail_list';
			if($_REQUEST['purchaseIds']) $url.= '/purchaseIds/'.$_REQUEST['purchaseIds'];
			if($_REQUEST['beeId']) $url.= '/beeId/'.$_REQUEST['beeId'];

			$children = array(array('小蜜蜂采购任务明细',U($url)), );
		}else{
			$children = array(array('小蜜蜂采购任务明细',U('/Purchasing/bee_detail_list')), );
		}
        $form->setChildren($children);
		
        $form->setMyField('PRICE', 'GRIDVISIBLE', -1);//成交价
        $form->setMyField('NUM', 'GRIDVISIBLE', -1);//购买数量
        $form->setMyField('P_ID', 'GRIDVISIBLE', '0');
        $form->setMyField('STOCK_NUM', 'GRIDVISIBLE', '0');
        $list_arr = array(1 => '是', 0 => '否');
        $list_arr_zk = array(1 => '已同步', 0 => '未同步');
        //是否已同步至总客
        $form = $form->setMyField('ZK_STATUS', 'LISTCHAR', array2listchar($list_arr_zk), FALSE);
        //是否资金池
	    $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar($list_arr), FALSE);
        //是否扣非
	    $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar($list_arr), FALSE);
        
        //采购明细MDOEL
        $purchase_list_model = D('PurchaseList');
        $purchase_arr = $purchase_list_model->get_conf_list_status_remark();
        
        //状态信息
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($purchase_arr), FALSE);
        
        //供应商
        $form->setMyField('S_ID', 'EDITTYPE', 21, FALSE);
        $form = $form->setMyField('S_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);
        
        //采购人
        $form->setMyField('P_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('P_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
             
        //采购发起人
        $form->setMyField('APPLY_USER_ID', 'FORMVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('APPLY_USER_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
        
        //采购类型
        $purchase_model = D('PurchaseRequisition');
        $purchase_type_arr = $purchase_model->get_conf_purchase_type_remark();
        $form->setMyField('TYPE', 'GRIDVISIBLE', '-1', TRUE);
        $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($purchase_type_arr), FALSE);
        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('ischildren',$this->_get('ischildren'));
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('bee');
    }
    /**
     * 发送小蜜蜂任务至众客
     */
    public function send_to_zk(){
        //请求参数判断
        $id = $_GET['id'];
        if (!$id){
            ajaxJsonReturn(false,'',401);//请求参数错误
        }
        //获取小蜜蜂采购明细
        $purchase_list_model = D('PurchaseList');//采购明细MDOEL
        $bee_field = array('ID', 'PR_ID', 'PRICE_LIMIT','NUM_LIMIT','ZK_STATUS');
        $bee = $purchase_list_model->field($bee_field)->where("ID=$id AND ZK_STATUS=0")->find();
        if (!$bee || empty($bee)){
            ajaxJsonReturn(false,'',402);//请求内容不存在
        }
        //获取采购申请明细
        $purchase_model = D('PurchaseRequisition'); //采购申请单MODEL
        $requestion_field = array(
            'to_char(END_TIME, \'YYYY-MM-DD HH24:MI:SS\') as END_TIME',
            'CITY_ID',
            'REASON',
            'PRJ_ID',
        );
        $requestion = $purchase_model->field($requestion_field)->find($bee['PR_ID']);
        if (!$requestion || empty($requestion)){
            ajaxJsonReturn(false,'',402);//请求内容不存在
        }
        //发送请求
        $curl_result = $this->_zk_api($requestion,$bee);
        if ($curl_result){
            $curl_result = json_decode($curl_result);
            if ($curl_result->code==200){
                $update_result = $purchase_list_model->where('ID='.$id)->save(array('ZK_STATUS'=>1));
                if ($update_result){
                    ajaxJsonReturn(true,'',200);//请求内容不存在
                }
            }
        }
        ajaxJsonReturn(false,'',501);//请求众客接口出错
    }
    /**
     * 发送小蜜蜂任务报销结果至众客
     */
    public function send_result_to_zk(){
        $post = $_POST['data'];
        if (empty($post)){
            ajaxJsonReturn(400);//无选中数据
        }
        //获取所有详情
        $id_str = implode(',', $post);

        //获取需要反馈的所有小蜜蜂任务详情
        $model = D('PurchaseBeeDetails');
        $requestion = $model->where("ID in ($id_str) AND IS_BACK_TO_ZK=0 AND STATUS IN (2,3)")->select();
        if (!$requestion || empty($requestion)){
            ajaxJsonReturn(false,'',402);//没有需要反馈的任务
        }
        //众客接口地址
        $api = ZKAPI2;//http://zk.house365.com:8008/
        //获取城市简拼
        $model_city = D('City');
        $city_id = intval($this->channelid);
        $city = $model_city->get_city_info_by_id($city_id);
        $citypy = strtolower($city["PY"]);
        //遍历并反馈至众客
        foreach ($requestion as $v){
            $param = array(
                'p_id'        => $v['P_ID'],
                'task_id'     => $v['TASK_ID'],
                'supplier_id' => $v['SUPPLIER_ID'],
                'status'      => $v['STATUS'],
                'city'        => $citypy,
                'mark'        => '',
                'key'         => md5(md5($v['P_ID'].$citypy)."BEE"),
            );
            //发送请求
            $result = curlPost($api, $param);
            //请求失败返回错误码
            if (!$result || empty($result)){
                ajaxJsonReturn(false,'',400);
            }
            $result = json_decode($result);
            if ($result->code==200){
                $model->where('ID='.$v['ID'])->save(array('IS_BACK_TO_ZK'=>1));
            }
        }
        ajaxJsonReturn(true,'',200);
    }
    /**
     * 小蜜蜂任务详情页面
     */
    public function bee_detail_list(){
		Vendor('Oms.Form');			
		$form = new Form();
		$form->initForminfo(196);
		$model = D('PurchaseBeeDetails');
        //状态
        $status = $model->get_bee_detail_status();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status), FALSE);
        $form = $form->setMyField('IS_BACK_TO_ZK', 'LISTCHAR', array2listchar(array(0=>'否',1=>'是')), FALSE);
        $file1_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="1" href="javascript:void(0);" class="btn btn-info btn-xs">汇总表</a>';
        $file2_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="2" href="javascript:void(0);" class="btn btn-info btn-xs">明细表</a>';
        $file3_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="3" href="javascript:void(0);" class="btn btn-info btn-xs">带看奖明细</a>';
        $form->CZBTN = $file1_btn.' '.$file2_btn.' '.$file3_btn;   
        $form->GABTN = '<a href="javascript:;" id="commit_bee_detail" class="btn btn-info btn-sm">报销</a>'.' '.'<a href="javascript:;" id="back_result_to_zk" data-id="'.$_REQUEST['parentchooseid'].'" class="btn btn-info btn-sm">报销状态反馈</a>';
		if($_REQUEST['purchaseIds'] || $_REQUEST['beeId']){
			if($_REQUEST['purchaseIds']){
				$form->where("ID in(".$_REQUEST['purchaseIds'].") or CSTATUS =1 or STATUS=4");
			}else {
				$form->where(" CSTATUS =1 or STATUS=4");
			}
		}
        $form= $form->getResult();
		$this->assign('form',$form);
		$this->display('bee_detail_list');
    }
    /**
     * 小蜜蜂任务提交申请
     */
    public function bee_detail_commit(){
        //获取要提交的内容
        $post = $_POST['data'];
        if (empty($post)){
            ajaxJsonReturn(400);//无选中数据
        }
        //获取所有详情
        $id_str = implode(',', $post);
        //实例化对象
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //获取所有提交的需要报销的小蜜蜂采购明细任务
		$bee_list_org = $model_bee_work->where("ID IN($id_str)")->select();
		foreach ($bee_list_org as $key=>$val){
			if($val['STATUS']!=0){
				  ajaxJsonReturn(503); //
			}

		}

        $bee_list_org = $model_bee_work->where("STATUS=0 AND ID IN($id_str)")->select(); 
        if (empty($bee_list_org)){
            ajaxJsonReturn(401);//选中数据不存在或已提交报销申请
        } 
		$reim_money_total = 0;
        //小蜜蜂采购明细
		//检测金额是否已经超出报销范围
        $need_change_status = array();
        $bee_id = $bee_list_org[0]['P_ID'];
		foreach ($bee_list_org as $key=>$val){
			//仅能报销相同的供应商
            if (!empty($supplier)){
                if ($supplier != $val['SUPPLIER_ID']){
                    ajaxJsonReturn(404); //请选择相同的供应商进行报销
                }
            }
            $supplier = $val['SUPPLIER_ID'];
			$reim_money_total+=$val['REIM_MONEY'];
			$detail_sup_list[$val['SUPPLIER_ID']][] = $val;
			$need_change_status[] = $val['ID'];

		}
        $bee = $model_bee->find($bee_id);
        if (empty($bee)){
            ajaxJsonReturn(403);//小蜜蜂采购明细不存在
        }
        //计算总共可以报销的金额
        $bee['TOTAL'] = $bee['PRICE_LIMIT'] * $bee['NUM_LIMIT'];
		$map['P_ID'] = array('eq',$bee_id);
		$map['STATUS'] = array('in','1,2');
        $bee_ybx = $model_bee_work->where($map)->select();  
        if (!empty($bee_ybx)){
            $bee_list = array_merge($bee_list_org,$bee_ybx);
        }else $bee_list= $bee_list_org;
        $money_total = 0;
        
        //供应商
        $supplier = '';
        foreach ($bee_list as $key=>$val){
            //清楚可能存在的异常操作数据
            if ($val['P_ID'] != $bee_id){
                unset($bee_list[$key]);
                continue;
            }
            
            //$need_change_status[] = $val['ID'];
            $money_total+=$val['REIM_MONEY'];
        }
        if(empty($need_change_status)){ 
            ajaxJsonReturn(401);
        } 
        if($money_total > $bee['TOTAL']){
            if ($bee['IS_APPLY_PROCESS']==1){
                ajaxJsonReturn(405);
            }
            $need_do_flows = implode('-', $need_change_status);
            ajaxJsonReturn(402,$need_do_flows,$bee_id); //选中报销金额已超出总预算金额，是否申请工作流
        }
		
        //审核完毕生成报销申请
        $reim_list_model = D('ReimbursementList');      //报销申请单MODEL
        $reim_detail_model = D('ReimbursementDetail');  //报销明细MODEL
        $reim_list_model->startTrans();
        //生成报销申请单
        $uid = intval($_SESSION['uinfo']['uid']);//当前用户编号
        $user_truename = $_SESSION['uinfo']['tname'];//当前用户姓名
        $city_id = intval($this->channelid);//当前城市编号
		foreach ($detail_sup_list as $key=>$bee_value){
			
			//$tempone = $reim_detail_model->where("STATUS=0 AND BUSINESS_ID=".$bee_value[0]['P_ID'])->find();
			$sql = "select A.* from ERP_REIMBURSEMENT_DETAIL A left join ERP_PURCHASER_BEE_DETAILS B on A.PURCHASER_BEE_ID=B.ID left join ERP_REIMBURSEMENT_LIST C on A.LIST_ID=C.ID where  C.STATUS=0 AND A.BUSINESS_ID=".$bee_value[0]['P_ID']." AND B.SUPPLIER_ID=".$bee_value[0]['SUPPLIER_ID'];
			$tempone = M()->query($sql);
			$reim_money_total_temp = 0;
			foreach($bee_value as $one){
				$reim_money_total_temp+=$one['REIM_MONEY'];
			}
			$list_arr = array();
			
			if($tempone){
				 
				foreach($tempone as $one){
					$reim_money_total_temp+=$one['MONEY'];
					
				}
				 
				$list_arr["AMOUNT"] = $reim_money_total_temp;
				$last_id = $tempone[0]['LIST_ID'];
				$reim_list_model->where("ID=".$last_id)->save($list_arr);
			}else{
				//var_dump($list_arr);
				//$list_arr = array();
				$list_arr["AMOUNT"] = $reim_money_total_temp;
				$list_arr["TYPE"] = 15;
				$list_arr["APPLY_UID"] = $uid;
				$list_arr["APPLY_TRUENAME"] = $user_truename;
				$list_arr["APPLY_TIME"] = date("Y-m-d H:m:s");
				$list_arr["CITY_ID"] = $city_id;
				$last_id = $reim_list_model->add_reim_list($list_arr);
				if (!$last_id){
					$reim_list_model->rollback();
					ajaxJsonReturn(500);//添加报销申请失败
					exit;
				}

			}
			 //生成报销明细
			foreach ($bee_value as $key=>$value){
				$detail_add = array(
					'LIST_ID' => $last_id,
					'CITY_ID' => $city_id,
					'CASE_ID' => $bee['CASE_ID'],
					'BUSINESS_ID' => $bee['ID'],//$value['ID'],
					'PURCHASER_BEE_ID' =>  $value['ID'],
					'BUSINESS_PARENT_ID' => $bee['PR_ID'],
					'MONEY' => $value['REIM_MONEY'],
					'STATUS' => 0,
					'APPLY_TIME' => date('Y-m-d H:i:s'),
					'ISFUNDPOOL' => $bee['IS_FUNDPOOL'],
					'ISKF' => $bee['IS_KF'],
					'TYPE' => 15,
					'FEE_ID' => $bee['FEE_ID'],
				); 
				$reuslt_add = $reim_detail_model->add_reim_details($detail_add);
				if (!$reuslt_add){
					$reim_list_model->rollback();
					ajaxJsonReturn(501);  //添加报销明细失败
					exit;
				}
			}

		}
       
       
        //修改小蜜蜂任务报销状态
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>1));
        if (!$update_result){
            $reim_list_model->rollback();
            ajaxJsonReturn(502);  //修改小蜜蜂任务报销状态失败
        }
        $reim_list_model->commit();
        ajaxJsonReturn(200);
    }
    /**
     * 导出小蜜蜂任务详情附件
     */
    public function export_bee_file(){
        $id = $_GET['id'];
		$reimId = $_GET['reimId'];
        
		if($reimId ){
			$one = M('Erp_reimbursement_detail')->where("ID=".$reimId)->find();
			$id = $one['PURCHASER_BEE_ID'];
		}
        $bee = D('PurchaseBeeDetails')->find($id);

        $file = $bee['FILE'.$_GET['file']];
        $param = json_decode($file,true);
        
        if ($_GET['file']==1){
            $this->_export_bee_file_total($param,$bee);
        } else if ($_GET['file']==2) {
            $this->_export_bee_file_details($param,$bee);
        } else{
            //带看奖明细表
            $this->_export_bee_data_details($param,$bee);
        }
    }
    
    /**
     * 小蜜蜂采购任务附件3
     * @param unknown $param
     */
    private function _export_bee_data_details($param,$bee){
        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '带看奖金明细表';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//默认行宽
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//默认列宽
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'宋体'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);
        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A3:R3')->getFont()->setBold(true);
        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'带看奖金明细表'));
        
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'任务日期'));
        $objActSheet->mergeCells('B2:D2');
        $taskStart = oracle_date_format($bee['EXEC_START'], 'Y-m-d');
        $taskEnd = oracle_date_format($bee['EXEC_END'], 'Y-m-d');
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",$taskStart.' 至 '.$taskEnd));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",'客户总数合计:'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",count($param)));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",'奖金合计:'));
        
        $objActSheet->setCellValue('A3', iconv("gbk//ignore","utf-8//ignore",'任务名称'));
        $objActSheet->setCellValue('B3', iconv("gbk//ignore","utf-8//ignore",'劳务机构'));
        $objActSheet->setCellValue('C3', iconv("gbk//ignore","utf-8//ignore",'兼职人员'));
        $objActSheet->setCellValue('D3', iconv("gbk//ignore","utf-8//ignore",'兼职人员联系电话'));
        $objActSheet->setCellValue('E3', iconv("gbk//ignore","utf-8//ignore",'任务日期'));
        $objActSheet->setCellValue('F3', iconv("gbk//ignore","utf-8//ignore",'到访时间'));
        $objActSheet->setCellValue('G3', iconv("gbk//ignore","utf-8//ignore",'客户姓名'));
        $objActSheet->setCellValue('H3', iconv("gbk//ignore","utf-8//ignore",'客户手机'));
        $objActSheet->setCellValue('I3', iconv("gbk//ignore","utf-8//ignore",'客户性别'));
        $objActSheet->setCellValue('J3', iconv("gbk//ignore","utf-8//ignore",'客户等级'));
        $objActSheet->setCellValue('K3', iconv("gbk//ignore","utf-8//ignore",'带看奖金'));
        $objActSheet->setCellValue('L3', iconv("gbk//ignore","utf-8//ignore",'备注'));
        $objActSheet->mergeCells('A1:L1');
        $i = 4;
        $allmoney = 0;
        foreach($param as $k => $r){
            $file = iconv("gbk","utf-8",$r['file']);
            $file = str_replace(array("/","、",",","，")," ",$file);
            $file = json_decode($file,true);
            
            $allmoney += $r['10'];
            #做替换
            $objActSheet->setCellValue('A'.$i, $r['0']);
            $objActSheet->setCellValue('B'.$i, $r['1']);
            $objActSheet->setCellValue('C'.$i, $r['2']);
            $objActSheet->setCellValue('D'.$i, $r['3']);
            $objActSheet->setCellValue('E'.$i, $r['4']);
            $objActSheet->setCellValue('F'.$i, $r['5']);
            $objActSheet->setCellValue('G'.$i, $r['6']);
            $objActSheet->setCellValue('H'.$i, $r['7']);
            $objActSheet->setCellValue('I'.$i, $r['8']);
            $objActSheet->setCellValue('J'.$i, $r['9']);
            $objActSheet->setCellValue('K'.$i, $r['10']);
            $objActSheet->setCellValue('L'.$i, $r['11']);
            $objActSheet->getRowDimension($i)->setRowHeight(24);
            $i++;
            if($objActSheet->getRowDimension($i)->getRowHeight() > 0){
                $objActSheet->getRowDimension($i)->setRowHeight($objActSheet->getRowDimension($i)->getRowHeight()+20);
            }
        }
        $objActSheet->setCellValue('K2', iconv("gbk//ignore","utf-8//ignore",$allmoney));
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xlsx");
        header("Content-Transfer-Encoding:binary");
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    
    /**
     * 小蜜蜂采购任务附件1
     * @param unknown $param
     */
    private function _export_bee_file_details($param,$bee){
        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '拓客费用明细表';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//默认行宽
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//默认列宽
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'宋体'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);
        $objActSheet->getRowDimension('3')->setRowHeight(26);
        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A3:R3')->getFont()->setBold(true);
        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'拓客费用明细表'));
        
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'任务日期:'));
        $objActSheet->mergeCells('B2:D2');
        $taskStart = oracle_date_format($bee['EXEC_START'], 'Y-m-d');
        $taskEnd = oracle_date_format($bee['EXEC_END'], 'Y-m-d');
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",$taskStart.' 至 '.$taskEnd));
        $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'工资合计:'));
        $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'奖金合计:'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'复核总计:'));
        
        $objActSheet->setCellValue('A3', iconv("gbk//ignore","utf-8//ignore",'任务名称'));
        $objActSheet->setCellValue('B3', iconv("gbk//ignore","utf-8//ignore",'劳务机构'));
        $objActSheet->setCellValue('C3', iconv("gbk//ignore","utf-8//ignore",'兼职人员'));
        $objActSheet->setCellValue('D3', iconv("gbk//ignore","utf-8//ignore",'联系电话'));
        $objActSheet->setCellValue('E3', iconv("gbk//ignore","utf-8//ignore",'任务日期'));
        $objActSheet->setCellValue('F3', iconv("gbk//ignore","utf-8//ignore",'工资标准'));
        $objActSheet->setCellValue('G3', iconv("gbk//ignore","utf-8//ignore",'复核工资'));
        $objActSheet->setCellValue('H3', iconv("gbk//ignore","utf-8//ignore",'奖励客户'));
        $objActSheet->setCellValue('I3', iconv("gbk//ignore","utf-8//ignore",'复核带看奖金'));
        $objActSheet->setCellValue('J3', iconv("gbk//ignore","utf-8//ignore",'复核杂费'));
        $objActSheet->setCellValue('K3', iconv("gbk//ignore","utf-8//ignore",'复核小计'));
        $objActSheet->mergeCells('A1:K1');
        $i = 4;
        $i = 4;
        $gz = 0; //工资合计
        $jj = 0; //奖金合计
        $fh = 0; //复核总计
        
        foreach($param as $k => $r){
            $file = iconv("gbk","utf-8",$r['file']);
            $file = str_replace(array("/","、",",","，")," ",$file);
            $file = json_decode($file,true);
            #做替换
            $objActSheet->setCellValue('A'.$i, $r['0']);
            $objActSheet->setCellValue('B'.$i, $r['1']);
            $objActSheet->setCellValue('C'.$i, $r['2']);
            $objActSheet->setCellValue('D'.$i, $r['3']);
            $objActSheet->setCellValue('E'.$i, $r['4']);
            $objActSheet->setCellValue('F'.$i, (float)$r['5'].iconv("gbk//ignore","utf-8//ignore",'元/人天'));
            $objActSheet->setCellValue('G'.$i, $r['6'].iconv("gbk//ignore","utf-8//ignore",'元'));
            $objActSheet->setCellValue('H'.$i, $r['7'].iconv("gbk//ignore","utf-8//ignore",'人'));
            $objActSheet->setCellValue('I'.$i, $r['8'].iconv("gbk//ignore","utf-8//ignore",'元'));
            $objActSheet->setCellValue('J'.$i, '--');
            $objActSheet->setCellValue('K'.$i, $r['9'].iconv("gbk//ignore","utf-8//ignore",'元'));
            $objActSheet->getRowDimension($i)->setRowHeight(24);
            $i++;
            if($objActSheet->getRowDimension($i)->getRowHeight() > 0){
                $objActSheet->getRowDimension($i)->setRowHeight($objActSheet->getRowDimension($i)->getRowHeight()+20);
            }
            $gz += $r['6'];
            $jj += $r['8'];
            $fh += $r['9'];
        }
        $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",$gz.'元'));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",$jj.'元'));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",$fh.'元'));

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xlsx");
        header("Content-Transfer-Encoding:binary");
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    /**
     * 小蜜蜂采购任务附件1
     * @param unknown $param
     */
    private function _export_bee_file_total($param,$bee){
        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '拓客费用汇总表';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//默认行宽
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//默认列宽
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'宋体'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);
        $objActSheet->getRowDimension('3')->setRowHeight(26);
        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A3:R3')->getFont()->setBold(true);
        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'拓客费用汇总表(按日汇总)'));
        
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'任务日期:'));
        $objActSheet->mergeCells('B2:D2');
        $taskStart = oracle_date_format($bee['EXEC_START'], 'Y-m-d');
        $taskEnd = oracle_date_format($bee['EXEC_END'], 'Y-m-d');
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",$taskStart.' 至 '.$taskEnd));
        $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'工资合计:'));
        $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'奖金合计:'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'复核总计:'));
        
        $objActSheet->setCellValue('A3', iconv("gbk//ignore","utf-8//ignore",'任务名称'));
        $objActSheet->setCellValue('B3', iconv("gbk//ignore","utf-8//ignore",'劳务机构'));
        $objActSheet->setCellValue('C3', iconv("gbk//ignore","utf-8//ignore",'任务日期'));
        $objActSheet->setCellValue('D3', iconv("gbk//ignore","utf-8//ignore",'工资标准'));
        $objActSheet->setCellValue('E3', iconv("gbk//ignore","utf-8//ignore",'签到人数'));
        $objActSheet->setCellValue('F3', iconv("gbk//ignore","utf-8//ignore",'复核出勤'));
        $objActSheet->setCellValue('G3', iconv("gbk//ignore","utf-8//ignore",'复核工资'));
        $objActSheet->setCellValue('H3', iconv("gbk//ignore","utf-8//ignore",'复核奖金'));
        $objActSheet->setCellValue('I3', iconv("gbk//ignore","utf-8//ignore",'复核杂费'));
        $objActSheet->setCellValue('J3', iconv("gbk//ignore","utf-8//ignore",'金额合计'));
        
        $objActSheet->mergeCells('A1:J1');
        $i = 4;
        $gz = 0; //工资合计
        $jj = 0; //奖金合计
        $fh = 0; //复核总计
        foreach($param as $k => $r){
            $file = iconv("gbk","utf-8",$r['file']);
            $file = str_replace(array("/","、",",","，")," ",$file);
            $file = json_decode($file,true);
            #做替换
            $objActSheet->setCellValue('A'.$i, $r['0']);
            $objActSheet->setCellValue('B'.$i, $r['1']);
            $objActSheet->setCellValue('C'.$i, $r['2']);
            $objActSheet->setCellValue('D'.$i, (float)$r['3'].iconv("gbk//ignore","utf-8//ignore",'元/人天'));
            $objActSheet->setCellValue('E'.$i, $r['4'].iconv("gbk//ignore","utf-8//ignore",'人'));
            $objActSheet->setCellValue('F'.$i, $r['5'].iconv("gbk//ignore","utf-8//ignore",'人天'));
            $objActSheet->setCellValue('G'.$i, $r['6']);
            $objActSheet->setCellValue('H'.$i, $r['7']);
            $objActSheet->setCellValue('I'.$i, '--');
            $objActSheet->setCellValue('J'.$i, $r['8']);
            $objActSheet->getRowDimension($i)->setRowHeight(24);
            $i++;
            if($objActSheet->getRowDimension($i)->getRowHeight() > 0){
                $objActSheet->getRowDimension($i)->setRowHeight($objActSheet->getRowDimension($i)->getRowHeight()+20);
            }
            $gz += $r['6'];
            $jj += $r['7'];
            $fh += $r['8'];
        }
        $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",$gz.'元'));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",$jj.'元'));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",$fh.'元'));
        
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xls");
        header("Content-Transfer-Encoding:binary");
    
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    /**
     +----------------------------------------------------------
     * 小蜜蜂流程审批意见
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    function BeeOpinionFlow()
    {
        $uid = intval($_SESSION['uinfo']['uid']);
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
    
        Vendor('Oms.workflow');
        $workflow = new workflow();
    
        $type = $_REQUEST['FLOWTYPE'] ? $_REQUEST['FLOWTYPE'] : "xiaomifengchaoe";
    
        $flowId = $_REQUEST['flowId'];
        $beeId = !empty($_GET['beeId']) ? $_GET['beeId'] : 0;//项目ID
        $beeDetailsId = !empty($_GET['beeWork']) ? str_replace('-', ',', $_GET['beeWork']) : 0;
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        if($flowId){
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);
    
            if($_REQUEST['savedata']){
                if($_REQUEST['flowNext']){
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str){
                        js_alert('办理成功',U('Flow/workStep'));
                    }else{
                        js_alert('办理失败');
                    }
                }elseif($_REQUEST['flowNot']){
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str){
                       // $model_bee_work->where('STATUS=4 AND P_ID='.$_REQUEST['beeId'])->save(array('STATUS'=>3));
						$this->_bee_option_follow_fail($_REQUEST['beeId']);
						js_alert('否决成功',U('Flow/workStep'));
                    }else{
                        js_alert('否决失败');
                    }
                }elseif($_REQUEST['flowStop']){
                    $auth = $workflow->flowPassRole($flowId);
                    if(!$auth){
                        js_alert('未经过必经角色');exit;
                    }
                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str){
                        $this->_bee_option_follow_success($_REQUEST['beeId']);
                        js_alert('备案成功',U('Flow/workStep'));
                    }else{
                        js_alert('备案失败');
                    }
                }
                exit;
            }
        }else{
            $auth = $workflow->start_authority($type);
            $form = $workflow->createHtml();
            if($_REQUEST['savedata']){
                $purchase_id = !empty($_GET['purchase_id']) ? intval($_GET['purchase_id']) : 0;
                $flow_data['type'] = $type;
                $flow_data['CASEID'] = 0;
                $flow_data['RECORDID'] = $beeId;
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES'];
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']);
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']);
                $str = $workflow->createworkflow($flow_data);
                if($str){
                    //更新小蜜蜂明细是否已发布流程状态
                    $model_bee->where('ID='.$beeId)->save(array('IS_APPLY_PROCESS'=>1));
                    $model_bee_work->where("ID IN ($beeDetailsId)")->save(array('STATUS'=>4));
                    js_alert('提交成功',U('Purchasing/bee',$this->_merge_url_param));
                    exit;
                }else{
                    js_alert('提交失败',U('Purchasing/BeeOpinionFlow',$this->_merge_url_param));
                    exit;
                }
            }
        }
        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('current_url', U('Purchasing/BeeOpinionFlow',$this->_merge_url_param));
        $this->assign('tabs',$this->getTabs(25, $this->_merge_url_param));
        $this->display('beeOpinionFlow');
    }
    /**
     * 超额流程审批通过自动生成报销申请
     * @param unknown $bee_id
     */
    private function _bee_option_follow_success($bee_id){
        //实例化对象
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //小蜜蜂采购明细
        $bee = $model_bee->find($bee_id);
        if (empty($bee)){
            return false;//小蜜蜂采购明细不存在
        }
        //获取所有提交的需要报销的小蜜蜂采购明细任务
        $bee_list = $model_bee_work->where("P_ID=$bee_id AND STATUS=4")->select();
        $money_total = 0;
        foreach ($bee_list as $key=>$val){
            $need_change_status[] = $val['ID'];
            $money_total+=$val['REIM_MONEY'];
        }
        //审核完毕生成报销申请
        $reim_list_model = D('ReimbursementList');      //报销申请单MODEL
        $reim_detail_model = D('ReimbursementDetail');  //报销明细MODEL
        $reim_list_model->startTrans();
        //生成报销申请单
        $uid = $bee['P_ID'];//intval($_SESSION['uinfo']['uid']);//当前用户编号
		$user = M('Erp_users')->where("ID=$uid")->find();
        $user_truename = $user['NAME'];  //$_SESSION['uinfo']['tname'];//当前用户姓名
        $city_id = intval($this->channelid);//当前城市编号
        $list_arr = array();
        $list_arr["AMOUNT"] = $money_total;
        $list_arr["TYPE"] = 15;
        $list_arr["APPLY_UID"] = $uid;
        $list_arr["APPLY_TRUENAME"] = $user_truename;
        $list_arr["APPLY_TIME"] = date("Y-m-d H:m:s");
        $list_arr["CITY_ID"] = $city_id;
        $last_id = $reim_list_model->add_reim_list($list_arr);
        if (!$last_id){
            $reim_list_model->rollback();
            return false;  //添加报销申请失败
        }
        //生成报销明细
        foreach ($bee_list as $key=>$value){
            $detail_add = array(
                'LIST_ID' => $last_id,
                'CITY_ID' => $city_id,
                'CASE_ID' => $bee['CASE_ID'],
                'BUSINESS_ID' => $bee['ID'],
				'PURCHASER_BEE_ID' =>  $value['ID'],
                'BUSINESS_PARENT_ID' => $bee['PR_ID'],
                'MONEY' => $value['REIM_MONEY'],
                'STATUS' => 0,
                'APPLY_TIME' => date('Y-m-d H:i:s'),
                'ISFUNDPOOL' => $bee['IS_FUNDPOOL'],
                'ISKF' => $bee['IS_KF'],
                'TYPE' => 15,
                'FEE_ID' => $bee['FEE_ID'],
            );
            $reuslt_add = $reim_detail_model->add_reim_details($detail_add);
            if (!$reuslt_add){
                $reim_list_model->rollback();
                return false;  //添加报销明细失败
            }
        }
        //修改小蜜蜂任务报销状态
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>1,'CSTATUS'=>1));
        if (!$update_result){
            $reim_list_model->rollback();
            return false;  //修改小蜜蜂任务报销状态失败
        }
        $reim_list_model->commit();
        return true;
    }
	/**
     * 超额流程审批通过自动生成报销申请
     * @param unknown $bee_id
     */
    private function _bee_option_follow_fail($bee_id){
        //实例化对象
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //小蜜蜂采购明细
        $bee = $model_bee->find($bee_id);
        if (empty($bee)){
            return false;//小蜜蜂采购明细不存在
        }
        //获取所有提交的需要报销的小蜜蜂采购明细任务
        $bee_list = $model_bee_work->where("P_ID=$bee_id AND STATUS=4")->select();
        foreach ($bee_list as $key=>$val){
            $need_change_status[] = $val['ID'];
            //$money_total+=$val['REIM_MONEY'];
        } 
        //审核完毕生成报销申请
       // $reim_list_model = D('ReimbursementList');      //报销申请单MODEL
        //$reim_detail_model = D('ReimbursementDetail');  //报销明细MODEL
        M()->startTrans();
        //生成报销申请单
        $uid = intval($_SESSION['uinfo']['uid']);//当前用户编号
        $user_truename = $_SESSION['uinfo']['tname'];//当前用户姓名
        $city_id = intval($this->channelid);//当前城市编号
         
		$project_cost_model = D("ProjectCost");
        //生成报销明细
		$cost_insert_id = true;
        foreach ($bee_list as $key=>$value){
            $cost_info = array();
			$cost_info['CASE_ID'] = $bee["CASE_ID"]; //案例编号 【必填】       
			$cost_info['ENTITY_ID'] = $bee["PR_ID"];                                 
			$cost_info['EXPEND_ID'] = $bee["ID"];                            
			$cost_info['ORG_ENTITY_ID'] = $bee["PR_ID"];                    
			$cost_info['ORG_EXPEND_ID'] = $bee["ID"];                  //业务实体编号 【必填】
			$cost_info['FEE'] = -$value['REIM_MONEY'];                // 成本金额 【必填】 
			$cost_info['ADD_UID'] = $bee["P_ID"];//$_SESSION["uinfo"]["uid"];            //操作用户编号 【必填】
			$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //发生时间 【必填】
			$cost_info['ISFUNDPOOL'] = $bee["IS_FUNDPOOL"];                  //是否资金池（0否，1是） 【必填】
			$cost_info['ISKF'] = $bee["IS_KF"];                             //成本类型ID 【必填】
			//$cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //进项税 【选填】
			$cost_info['FEE_ID'] =  $bee["FEE_ID"];   
			$cost_info['EXPEND_FROM'] = 31; //?
			$cost_info['FEE_REMARK'] = "采购报销超额申请驳回";//成本类型ID 【必填】
			$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
			if(!$cost_insert_id){
				$cost_insert_id = false;
				break;
			}
		}
        //修改小蜜蜂任务报销状态
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>3,'CSTATUS'=>1));
        if (!$update_result || !$cost_insert_id){
            M()->rollback();
            return false;  //修改小蜜蜂任务报销状态失败
        }
		send_result_to_zk($need_change_status,$this->channelid );//同步到众客
        M()->commit();
        return true;
    }
    /**
     * 小蜜蜂超额流程审批详情展示
     */
    public function bee_work_flow(){
        $this->_merge_url_param['TAB_NUMBER'] = 25;
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $ischildren = $this->_get('ischildren');
        $parent_id = intval($this->_get('parentchooseid'));
        $id = intval($this->_get('ID'));
        $bee_id = $_GET['beeId'];
        Vendor('Oms.Form');
        $form = new Form();
        
        $form->initForminfo(195);
        
        $form->SQLTEXT = "( SELECT A.*, B.END_TIME from ERP_PURCHASE_LIST A LEFT JOIN "
                . " ERP_PURCHASE_REQUISITION B on A.PR_ID = B.ID where A.ID=$bee_id AND A.FEE_ID=58 AND B.STATUS = 2 AND B.CITY_ID = '".$this->channelid."')";
        $form->setMyField('PURCHASE_COST', 'GRIDVISIBLE', '0');//采购成本
        $form->setMyField('TOTAL_COST', 'GRIDVISIBLE', '0');//合计金额
        $form->setMyField('S_ID', 'GRIDVISIBLE', -1);//供应商
        $form->setMyField('END_TIME', 'GRIDVISIBLE', -1);//最晚送达时间
        $form->where("CONTRACT_ID is null");
        $zk_send_btn = "<a class=\"contrtable-link fedit send_zk\" href=\"javascript:void(0);\">同步</a>";
        $form->CZBTN = array('%ZK_STATUS%==0' => $zk_send_btn);
        /***子页面***/
        $children = array(array('小蜜蜂采购任务明细',U('/Purchasing/bee_detail_list')), );
        $form->setChildren($children);

        $form->setMyField('PRICE', 'GRIDVISIBLE', -1);//成交价
        $form->setMyField('NUM', 'GRIDVISIBLE', -1);//购买数量
        $form->setMyField('P_ID', 'GRIDVISIBLE', '0');
        $form->setMyField('STOCK_NUM', 'GRIDVISIBLE', '0');
        $list_arr = array(1 => '是', 0 => '否');
        $list_arr_zk = array(1 => '已同步', 0 => '未同步');
        //是否已同步至总客
        $form = $form->setMyField('ZK_STATUS', 'LISTCHAR', array2listchar($list_arr_zk), FALSE);
        //是否资金池
        $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar($list_arr), FALSE);
        //是否扣非
        $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar($list_arr), FALSE);

        //采购明细MDOEL
        $purchase_list_model = D('PurchaseList');
        $purchase_arr = $purchase_list_model->get_conf_list_status_remark();

        //状态信息
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($purchase_arr), FALSE);

        //供应商
        $form->setMyField('S_ID', 'EDITTYPE', 21, FALSE);
        $form = $form->setMyField('S_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);

        //采购人
        $form->setMyField('P_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('P_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
         
        //采购发起人
        $form->setMyField('APPLY_USER_ID', 'FORMVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('APPLY_USER_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);

        //采购类型
        $purchase_model = D('PurchaseRequisition');
        $purchase_type_arr = $purchase_model->get_conf_purchase_type_remark();
        $form->setMyField('TYPE', 'GRIDVISIBLE', '-1', TRUE);
        $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($purchase_type_arr), FALSE);
        $form = $form->getResult();
        $this->assign('form',$form);
        $this->assign('ischildren',$this->_get('ischildren'));
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('bee');
    }

    /**
     * 已领用的采购需要在成本表里添加一条记录
     * @param $purchaseInfo
     */
    private function addReimedCostList($purchaseInfo, $uid) {
        $sql = "
            SELECT P.*, C.SCALETYPE
            FROM erp_purchase_requisition P
            LEFT JOIN erp_case C ON P.CASE_ID = C.ID
            WHERE P.ID = {$purchaseInfo['PR_ID']}
        ";

        $purchaseRequisition = M()->query($sql);
        if (is_array($purchaseRequisition) && count($purchaseRequisition)) {
            $data = array(
                'CASE_ID' => $purchaseInfo['CASE_ID'],  //案例编号 【必填】
                'CASE_TYPE' => $purchaseRequisition[0]['SCALETYPE'],  // 电商项目
                'ENTITY_ID' => $purchaseInfo['PR_ID'],// 业务实体编号 【必填】
                'EXPEND_ID' => $purchaseInfo['ID'],// 成本明细编号 【必填】
                'ORG_ENTITY_ID' => $purchaseInfo['PR_ID'],  // 业务实体编号 【必填】
                'ORG_EXPEND_ID' => $purchaseInfo['ID'],
                'FEE' => $purchaseInfo['USE_TOATL_PRICE'],  // 成本金额 【必填】
                'ADD_UID' => $uid,  //操作用户编号 【必填】
                'OCCUR_TIME' => $purchaseInfo['ADD_TIME'], //发生时间 【必填】
                'ISFUNDPOOL' => $purchaseInfo['IS_FUNDPOOL'],  // 是否资金池（0否，1是） 【必填】
                'ISKF' => $purchaseInfo['IS_KF'], // 是否扣非 【必填】
                'INPUT_TAX' => $purchaseInfo['INPUT_TAX'],// 进项税 【选填】
                'FEE_ID' => $purchaseInfo['FEE_ID'], // 成本类型ID 【必填】
                'EXPEND_FROM' => 4, // 来源类型，报销通过
                'STATUS' => 4,  // 报销通过
                'PROJECT_ID' => $purchaseRequisition[0]['PRJ_ID'],
                'USER_ID' => $purchaseRequisition[0]['USER_ID'],
                'DEPT_ID' => $purchaseRequisition[0]['DEPT_ID'],
                'CITY_ID' => $purchaseInfo['CITY_ID'],
                'FEE_REMARK' => '采购来自于领用' //费用描述 【选填】
            );

            M()->startTrans();
            $inserted = D('ProjectCost')->add($data);
            if ($inserted !== false) {
                M()->commit();
            } else {
                M()->rollback();
            }
        }
    }

    public function ajaxRejectPurchasing() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => ''
        );

        $fid = intval(trim($_REQUEST['fid']));
        if ($fid > 0) {
            $purchase = D('erp_purchase_list')->where("ID = {$fid}")->find();
            if (empty($purchase['CONTRACT_ID'])) {
                $result = true;  // 操作数据库结果
                D()->startTrans();
                if (intval($purchase['USE_NUM']) != 0) {
                    $revertedData = array(
                        'use_num' => $purchase['USE_NUM'],
                        'purchaseId' => $purchase['ID']
                    );

                    // 退库
                    $result = $this->rejectToWarehouse($revertedData);
                }

                if ($result !== false) {
                    // 删除相应的采购明细
                    $result = D('erp_purchase_list')->where("ID = {$fid}")->delete();
                    if ($result !== false) {
                        $reqPurchaseCount = D('erp_purchase_list')->where("PR_ID = {$purchase['PR_ID']}")->count();
                        if ($reqPurchaseCount <= 0) {
                            // 如果该采购明细所在的采购申请只有一条，则把采购申请也删除
                            $result = D('erp_purchase_requisition')->where("ID = {$purchase['PR_ID']}")->delete();
                        }
                    }
                }

                if ($result !== false) {
                    $response['message'] = '采购明细删除成功';
                    D()->commit();
                } else {
                    $response['message'] = '采购明细删除失败';
                    D()->rollback();
                }
            }
        }

        $response['status'] = $result;
        echo json_encode(g2u($response));
    }

    /**
     * 将已领用采购退库
     * @param $data
     * @return array|bool|mixed
     */
    private function rejectToWarehouse($data) {
        $result = false;
        $warehouseUse = D('erp_warehouse_use_details')
            ->where("PL_ID = {$data['purchaseId']}")
            ->order('ID DESC')
            ->select();

        if (is_array($warehouseUse)) {
            foreach ($warehouseUse as $k => $v) {
                // 更新库存信息
                $upWarehouseSql = <<<UPDATE_NUM_SQL
                UPDATE ERP_WAREHOUSE
                SET USE_NUM = USE_NUM - %d
                WHERE ID = %d
UPDATE_NUM_SQL;
                $result = D()->query(sprintf($upWarehouseSql, $v['USE_NUM'], $v['WH_ID']));

                // 删除领用记录
                if ($result !== false) {
                    $result = D('erp_warehouse_use_details')->where("ID = {$v['ID']}")->delete();
                } else {
                    return false;
                }
            }
        }

        return $result;
    }

    /**
     * 保存采购，更新采购表
     * @param array $purchase 采购信息
     * @param string $msg 操作结果
     * @return bool
     */
    private function updatePurchaseList($purchase, &$msg) {
        $result = false;
        if (notEmptyArray($purchase)) {
            $purchaseId = $purchase['purchase_id'];
            if ($purchaseId > 0) {
                $purchaseListModel = D('PurchaseList');

                // 根据采购明细查询采购信息
                $dbPurchase = D('PurchaseList')->where("ID = {$purchaseId}")->find();
                if (empty($dbPurchase)) {
                    $msg = '采购明细信息异常，采购信息更新失败';
                    return false;
                }

                // 非领用状态数据完整性验证
                if ($dbPurchase['USE_NUM'] == 0 && ($purchase['buy_num'] == 0 || $purchase['buy_price'] == 0 || $purchase['supplier_id'] == 0)) {
                    $msg = '采购无领用时,采购供应商、采购单价、采购数量都必须填写';
                    return false;
                }

                if (intval($purchase['buy_num']) > 0) {
                    //判断采购单价 是否大于最高限价
                    if ($purchase['buy_price'] > $dbPurchase['PRICE_LIMIT']) {
                        $msg = '采购成交价大于最高限价，采购信息更新失败';
                        return false;
                    }

                    //判断采购数量+领用数量 是否大于申请数量
                    if ($purchase['buy_num']  > ($dbPurchase['NUM_LIMIT'] - $dbPurchase['USE_NUM'])) {
                        $msg = '采购数量超过申请数量，采购信息更新失败';
                        return false;
                    }

                    $update_arr['S_ID'] = $purchase['supplier_id'];
                    $update_arr['NUM'] = $purchase['buy_num'];
                    $update_arr['PRICE'] = $purchase['buy_price'];
                }

                $purchaseOccurTime = $purchaseListModel->where("ID = {$purchaseId}")->getField('PURCHASE_OCCUR_TIME');
                if (empty($purchaseOccurTime)) {
                    $update_arr['PURCHASE_OCCUR_TIME'] = date('Y-m-d H:i:s');
                }

                $update_arr['COST_OCCUR_TIME'] = $purchase['cost_occur_time'];  // 费用发生时间
                $update_arr['STATUS'] = 1;  // 采购明细修改为已采购
                $update_num = $purchaseListModel->update_purchase_list_by_id($purchaseId, $update_arr);


                if ($update_num !== false) {
                    if (D('PurchaseList')->is_all_purchased($dbPurchase['PR_ID'])) {
                        // 采购明细全部采购完成则设置采购申请单为采购完成状态
                        $update_num = D('PurchaseRequisition')->where("ID = {$dbPurchase['PR_ID']}")->save(array('STATUS' => 4));
                    } else {
                        // 否则，设置采购申请单为申请通过状态
                        $update_num = D('PurchaseRequisition')->where("ID = {$dbPurchase['PR_ID']}")->save(array('STATUS' => 2));
                    }
					//待支付业务费处理
					//$sql = "select * from ERP_CASE t where ID='".$dbPurchase['CASE_ID']."' ";
					//$tcase = M()->query($sql);
					$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$dbPurchase['CASE_ID']."' and TYPE=1";
					$finalaccounts = M()->query($sql);
					$fee =  $update_arr['NUM'] *  $update_arr['PRICE'];
					$xgfee = $finalaccounts[0]['TOBEPAID_FUNDPOOL'] > $fee  ? $finalaccounts[0]['TOBEPAID_FUNDPOOL']-$fee : 0;
					if($xgfee!=$finalaccounts[0]['TOBEPAID_FUNDPOOL'] && $finalaccounts[0]['STATUS']==2 &&$dbPurchase['IS_FUNDPOOL']==1){
						D('Erp_finalaccounts')->where("CASE_ID='".$dbPurchase['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_FUNDPOOL'=>$xgfee) );
					}
                }

                return $update_num;
            } else if ($purchaseId == 0) {
                $msg = '请选择需要更新的采购明细';
                return false;
            } else {
                $msg = '采购信息更新失败，采购明细信息异常！';
                return false;
            }

        }
        return $result;
    }

    private function afterReimDetailDel($list_id, $type) {
        $dbResult = false;
        if ($type == 15) {
            // todo
        } else {
            $reim_detail_list = D('ReimbursementDetail')->get_detail_info_by_listid($list_id, array('BUSINESS_ID', 'BUSINESS_PARENT_ID'));
            if (notEmptyArray($reim_detail_list)) {
                foreach ($reim_detail_list as $one) {
                    $dbResult = D('PurchaseList')->where("ID = {$one['BUSINESS_ID']}")->save(array('STATUS' => 1));
                    if ($dbResult !== false) {
                        $dbResult = D('PurchaseRequisition')->where("ID = {$one['BUSINESS_PARENT_ID']}")->save(array('STATUS' => 2));
                    }

                    if ($dbResult !== false) {
                        // 删掉领用的采购成本
                        $warehouseCost = D('PurchaseList')->getWarehouseCost($one['BUSINESS_ID'], $one['BUSINESS_PARENT_ID']);
                        if ($warehouseCost['status']) {
                            $dbResult = D('ProjectCost')->where("ID = {$warehouseCost['id']}")->delete();
                        }
                    }

                    if ($dbResult === false) {
                        break;
                    }


                }
            }
        }

        return $dbResult;
    }
}

/* End of file PurchasingAction.class.php */
/* Location: ./Lib/Action/PurchasingAction.class.php */