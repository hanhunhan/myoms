<?php

/**
 * �ɹ���Ա���ܿ����� 
 */

class PurchasingAction extends ExtendAction{

    // �ɹ���Ա������ɾ���ɹ���ϸȨ��
    const REJECT_PURCHASING = 779;
    
    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();
    
    /**��ҳǩ���**/
    private $_tab_number = 2;
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
        //TAB URL����
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
    *  �ɹ���ϸ
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

            $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1');//�ɹ�������
            $form->setMyField('END_TIME', 'GRIDVISIBLE', -1);//�����ʹ�ʱ��

            $yc_btn = '<a class = "delete_from_contract contrtable-link btn btn-info btn-sm" href="javascript:void(0);">�Ӻ�ͬ�Ƴ�</a>';
            $param = array('ischildren' => $ischildren, 'parentchooseid' => $parent_id,  'showForm' => 2);
            $operate_url = U('Purchasing/details/', $param);
            $view_btn = "<a onclick=\"fthisShow(this,'".$operate_url."');\" fid = '".$id."' class=\"contrtable-link fedit btn btn-info btn-sm\" href=\"javascript:void(0);\">�鿴</a>";
            $form->CZBTN = array('%STATUS%==0' => $yc_btn.' '.$view_btn, '%STATUS% > 0' => $view_btn);
        }
        else
        {
            $form->SQLTEXT = "( SELECT A.*, B.END_TIME,P.Contract,P.PROJECTNAME from ERP_PURCHASE_LIST A LEFT JOIN "
                . " ERP_PURCHASE_REQUISITION B on A.PR_ID = B.ID "
                . " LEFT JOIN ERP_CASE C ON A.CASE_ID = C.ID "
                . " LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID "
                . "where (B.STATUS = 2 OR B.STATUS = 4) AND B.CITY_ID = '".$this->channelid."' AND (A.TYPE=2 OR (A.TYPE=1 AND A.FEE_ID!=58)) AND A.STATUS != 2 AND A.STATUS != 3)";
            $form->setMyField('PURCHASE_COST', 'GRIDVISIBLE', '0');//�ɹ��ɱ�
            $form->setMyField('TOTAL_COST', 'GRIDVISIBLE', '0');//�ϼƽ��
            $form->setMyField('S_ID', 'GRIDVISIBLE', -1);//��Ӧ��
            $form->setMyField('END_TIME', 'GRIDVISIBLE', -1);//�����ʹ�ʱ��
            $form->where("(CONTRACT_ID is null and STATUS != 4) and P_ID = ".$_SESSION['uinfo']['uid']);

            $form->GABTN = '<a href="javascript:;" id="edit_purchase" operate_type= "edit_purchase" fid = "0" class="btn btn-info btn-sm">�ɹ�</a>'
                . '<a href="javascript:;" onclick="get_lower_price();" id="lower_price" class="btn btn-info btn-sm">��ʷ�ɹ��۸�</a>';
            
//            $form->GABTN .= '<a href="javascript:;" id="addcontract" onclick="addcontract();" class="btn btn-info btn-sm">������ͬ</a> '
//                    . ' <a href="javascript:;" onclick="aptocontract();" id="aptocontract" class="btn btn-info btn-sm">�������к�ͬ</a>';
            $form->GABTN .= '<a href="javascript:;" onclick="addReim();" id="add_reim" class="btn btn-info btn-sm">���ɱ�������</a>';

            /***��ҳ��***/
            $children = array(
                            array('������ϸ',U('/Purchasing/use_detail_list')),
                            );
            $form->setChildren($children);
        }
		
        $form->setMyField('PRICE', 'GRIDVISIBLE', -1);//�ɽ���
        $form->setMyField('NUM', 'GRIDVISIBLE', -1);//��������
        $form->setMyField('P_ID', 'GRIDVISIBLE', '0');
        $form->setMyField('STOCK_NUM', 'GRIDVISIBLE', '0');
        if($showForm > 0 )
        {
            //��������(���νṹ)
            $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
                    . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
        }
        else
        {
            //��������
            $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
        }
        
        $list_arr = array(1 => '��', 0 => '��');
        
        //�Ƿ��ʽ��
	    $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar($list_arr), FALSE);
        //�Ƿ�۷�
	    $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar($list_arr), FALSE);
        
        //�ɹ���ϸMDOEL
        $purchase_list_model = D('PurchaseList');
        $purchase_arr = $purchase_list_model->get_conf_list_status_remark();
        
        //״̬��Ϣ
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($purchase_arr), FALSE);
        
        //��Ӧ��
        $form->setMyField('S_ID', 'EDITTYPE', 21, FALSE);
        $form = $form->setMyField('S_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);
        
        //�ɹ���
        $form->setMyField('P_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('P_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
             
        //�ɹ�������
        $form->setMyField('APPLY_USER_ID', 'FORMVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('APPLY_USER_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
        
        //�ɹ�����
        $purchase_model = D('PurchaseRequisition');
        $purchase_type_arr = $purchase_model->get_conf_purchase_type_remark();
        $form->setMyField('TYPE', 'GRIDVISIBLE', '-1', TRUE);
        $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($purchase_type_arr), FALSE);

        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('ischildren',$this->_get('ischildren'));
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('details');
    }
    
    
    /**
    +----------------------------------------------------------
    *  ������ͬ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function add_contract()
    {   
        $uid = $_SESSION['uinfo']['uid'];
        
        //�ɹ���ϸ�������
        $purchaseId_arr = $_GET['purchaseId'];
        
        if(is_array($purchaseId_arr) && !empty($purchaseId_arr))
        {
            //�ɹ���ϸ
            $purchase_list_model = D('PurchaseList');
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchaseId_arr);
            
            //�ɹ����뵥
            $purchase_model = D('PurchaseRequisition');
            $purchase_type = $purchase_model->get_conf_purchase_type();
            
            //�ɱ�MODEL
            $cost_model = D('ProjectCost');
            
            if(is_array($purchase_list_info) && !empty($purchase_list_info))
            {
                $supplier_arr = array();
                $conract_num = 0;
                $data_empty_num = 0;
                $purchased_info = array();
                
                foreach($purchase_list_info as $key => $value)
                {
                    //��Ӧ�̡��ɹ����������������Ƿ���д
                    if($value['TYPE'] == $purchase_type['project_purchase'])
                    {
                        //��Ŀ�ɹ��Ƿ���ڹ�Ӧ�̡��ɹ�����/��������û����д������
                        if( ($value['S_ID'] == 0 || $value['NUM'] == 0) 
                                && $value['USE_NUM'] == 0)
                        {
                            $data_empty_num ++;
                            continue;
                        }
                    }
                    else if($value['TYPE'] == $purchase_type['bulk_purchase'])
                    {
                        //���ڲɹ��Ƿ���ڹ�Ӧ�̡��ɹ�����Ϊ��Ĳɹ���¼
                        if($value['S_ID'] == 0 || $value['NUM'] == 0 )
                        {
                            $data_empty_num ++;
                            continue;
                        }
                    }
                    
                    //��Ӧ��
                    $sid = intval($value['S_ID']);
                    $supplier_arr[$sid] = $sid;
                    
                    //�Ƿ�����Ѽ����ͬ��
                    if( $value['CONTRACT_ID'] > 0)
                    {
                        $conract_num ++;
                        continue;
                    }
                    
                    //��Ӻ�ͬ�Ĳɹ���ϸ���
                    $purchased_info[$value['ID']] = $value['ID'];
                }
                
                //���ݲ�����
                if($data_empty_num > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('������Ϣ��д�������Ĳɹ���ϸ���޷�������ͬ��');
                    
                    echo json_encode($res);
                    exit;
                }
                
                //�����Ѽ����ͬ�Ĳɹ���ϸ
                if($conract_num > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('�����Ѽ����ͬ�Ĳɹ���ϸ���޷�������ͬ��');
                    
                    echo json_encode($res);
                    exit;
                }
                
                //��Ӧ�̲���ͬ�����޷���Ӻ�ͬ
                $supplier_num = count($supplier_arr);
                if($supplier_num > 1)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('�ɹ���ϸ��Ӧ�̲�һ�£��޷�������ͬ��');
                    echo json_encode($res);
                    exit;
                }
                
                //��ͬMODEL
                $contract_model = D('erp_contract');
                $cdata['PROMOTER'] = $uid;
                $cdata['STATUS'] = 0;
                $cdata['TYPE'] = 1;
                $cdata['ISSIGN'] = 0;
                $cdata['CITY_ID'] = $this->channelid;
                $cdata['SUPPLIER_ID'] = reset($supplier_arr);
                
                if($cdata['SUPPLIER_ID'] == 0)
                {   
                    //��ѯ�ó������ù�Ӧ��
                    $supplier_info = 
                            M('erp_supplier')->field('ID')->
                            where("CITY_ID = '".$this->channelid."' AND TYPE = 1 AND STATUS = 1")->
                            find();
                    
                    if(empty($supplier_info))
                    {
                        $res['status'] = 0;
                        $res['msg'] = g2u('���ʧ�ܣ������ù�Ӧ����Ϣ����Ҫ�ڹ�Ӧ�̹��������Ч�����ù�Ӧ����Ϣ��');
                        
                        echo json_encode($res);
                        exit;
                    }
                    
                    $cdata['SUPPLIER_ID'] = intval($supplier_info['ID']);
                }
                
                //��Ӻ�ͬ
                $contract_id = $contract_model->add($cdata);
                
                if($contract_id > 0)
                {  
                    //�ɹ����뵥
                    $purchase_model = D('PurchaseRequisition');
                    
                    //��ϸ�Ѳɹ�״̬
                    $list_status = $purchase_list_model->get_conf_list_status();
                    
                    //���²ɹ����ɹ���ͬ���
                    $update_num = $purchase_list_model->add_to_contract($purchased_info, $contract_id);
                }
                
                if($contract_id > 0 && $update_num > 0 )
                {
                    $res['status'] = 1;
                    $res['msg'] = g2u('������ͬ�ɹ���');
                }  
                else 
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('������ͬʧ�ܣ�');
                }
            }
            else
            {
                $res['status'] = 0;
                $res['msg'] = g2u('δ�ҵ���زɹ���ϸ��');
            }
        }
        else
        {
            $res['status'] = 0;
            $res['msg'] = g2u('����ѡ��ɹ���ϸ��');
        }
        
        echo json_encode($res);
    }
    
    
    /**
    +----------------------------------------------------------
    *  �������к�ͬ
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function append_to_contract()
    {   
        $uid = $_SESSION['uinfo']['uid'];
        
        //�ɹ���ϸ�������
        $purchaseId_arr = $_REQUEST['selecttr'];;
        
        //��ͬ���
        $contract_id = intval($_REQUEST['aptocontractId']);
        
        if(is_array($purchaseId_arr) && !empty($purchaseId_arr) && $contract_id > 0)
        {   
            //�ɹ���ϸ
            $purchase_list_model = D('PurchaseList');
            $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($purchaseId_arr);
            
            //�ɹ����뵥
            $purchase_model = D('PurchaseRequisition');
            $purchase_type = $purchase_model->get_conf_purchase_type();
            
            if(is_array($purchase_list_info) && !empty($purchase_list_info))
            {   
                //��Ӧ�̲�ͬ
                $supplier_diff_num = 0;
                //�Ѽ����ͬ
                $conract_num = 0;
                //���ݲ�����
                $data_empty_num = 0;
                $purchased_info = array();
                
                //���ݺ�ͬ��Ų�ѯ��Ӧ����Ϣ
                $contract_model = D('erp_contract');
                $contract_supplier_info = $contract_model->
                        where("ID = '".$contract_id."' ")->field('SUPPLIER_ID')->find();
                
                foreach($purchase_list_info as $key => $value)
                {
                    // �����ȫ�����õ���ϸ�����ж�ѡ��ĺ�ͬ�Ƿ������ú�ͬ
                    // ����������ú�ͬ���������ʾ����������
                    if (intval($value['NUM']) == 0 && intval($value['USE_NUM']) > 0) {
                        $isFromStockContract = D('PurchaseContract')->isFromStockContract($contract_id);
                        if (!$isFromStockContract) {
                            echo json_encode(array(
                                'status' => 0,
                                'msg' => g2u('��ѡ��ͬ�������ú�ͬ����ǰΪȫ�����õĲɹ������ܼ���ú�ͬ')
                            ));
                            exit;
                        }
                    }

                    //��Ӧ�̡��ɹ����������������Ƿ���д
                    if($value['TYPE'] == $purchase_type['project_purchase'])
                    {
                        //��Ŀ�ɹ��Ƿ���ڹ�Ӧ�̡��ɹ�����/��������û����д������
                        if( ($value['S_ID'] == 0 || $value['NUM'] == 0) 
                                && $value['USE_NUM'] == 0)
                        {
                            $data_empty_num ++;
                            continue;
                        }
                    }
                    else if($value['TYPE'] == $purchase_type['bulk_purchase'])
                    {
                        //���ڲɹ��Ƿ���ڹ�Ӧ�̡��ɹ�����Ϊ��Ĳɹ���¼
                        if($value['S_ID'] == 0 || $value['NUM'] == 0 )
                        {
                            $data_empty_num ++;
                            continue;
                        }
                    }
                    
                    //��Ӧ��
                    if(( $value['S_ID'] > 0 && $contract_supplier_info['SUPPLIER_ID'] != $value['S_ID']))
                    {
                    	$supplier_diff_num ++;
                    	continue;
                    }
                    
                    //�Ƿ�����Ѽ����ͬ��
                    if( $value['CONTRACT_ID'] > 0)
                    {
                        $conract_num ++;
                        continue;
                    }
                    
                    //��Ӻ�ͬ�Ĳɹ���ϸ���
                    $purchased_info[$value['ID']] = $value['ID'];
                }
                
                //�ɹ���Ӧ��
                if($buy_not_supplier > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('��Ӧ��δ��д���޷������ͬ��');

                    echo json_encode($res);
                    exit;
                }
                
                //���ݲ�����
                if($data_empty_num > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('�ɹ���Ϣ��д�������Ĳɹ���ϸ���޷������ͬ��');
                    
                    echo json_encode($res);
                    exit;
                }
                
                //�����Ѽ����ͬ�Ĳɹ���ϸ
                if($conract_num > 0)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('�����Ѽ����ͬ�Ĳɹ���ϸ���޷������ͬ��');
                    
                    echo json_encode($res);
                    exit;
                }
                
                //��Ӧ�̲���ͬ�����޷���Ӻ�ͬ
                if($supplier_diff_num >= 1)
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('�ɹ���ϸ��Ӧ�������к�ͬ��Ӧ�̲�һ�£��޷������ͬ��');
                    echo json_encode($res);
                    exit;
                }
                
                if($contract_id > 0)
                {   
                    //�ɹ����뵥
                    $purchase_model = D('PurchaseRequisition');
                    //��ϸ�Ѳɹ�״̬
                    $list_status = $purchase_list_model->get_conf_list_status();
                    //���²ɹ���ϸ״���ɹ���ͬ���
                    $update_num = $purchase_list_model->add_to_contract($purchased_info, $contract_id);
                }
                
                if($contract_id > 0 && $update_num > 0 )
                {
                    $res['status'] = 1;
                    $res['msg'] = g2u('�������к�ͬ�ɹ���');
                }  
                else 
                {
                    $res['status'] = 0;
                    $res['msg'] = g2u('�������к�ͬʧ�ܣ�');
                }
            }
            else
            {
                $res['status'] = 0;
                $res['msg'] = g2u('δ�ҵ���زɹ���ϸ��');
            }
        }
        else
        {
            $res['status'] = 0;
            $res['msg'] = g2u('����ѡ��ɹ���ϸ�Ͳɹ���ͬ��');
        }
        
        echo json_encode($res);
    }
    
    
    /**
    +----------------------------------------------------------
    *  �ɹ���Ʒ������ϸ
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
        //״̬
        $use_status_remark = $warehouse_use_model->get_conf_status_remark();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($use_status_remark), FALSE);    
		$form= $form->getResult();
		$this->assign('form',$form);
		$this->display('use_detail_list');
    }
    
	/**
    +----------------------------------------------------------
    *  �ɹ���ͬ
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
			$form->where("TYPE = 1 AND ISSIGN = 0 AND PROMOTER = ".$uid." AND CITY_ID =". $this->channelid);//δ�ύ�ĺ�ͬ
			$form->SHOWSEQUENCE = 0;
			$form->DELABLE = 0;
			$form->EDITABLE = 0;
			$form->ADDABLE = 0;
			$form->GABTN = ' ';
			$form->GRIDAFTERDATA = '<div class="handle-btn"><input type="button" value="ȷ&nbsp;��" onclick="parent.submitaptocontract();" class="btn btn-primary" />  <input type="button" value="��&nbsp;��" class="j-pageclose btn btn-default" onclick="parent.layer.closeAll();" /></div>';
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
                '%ISSIGN%==-1' => '<a href="javascript:;" onclick="fthisShow(this,\''.U('Purchasing/contract','&kjcontract='.$this->_get('kjcontract')).'\');" class="contrtable-link btn btn-success btn-xs" title="�鿴"><i class="glyphicon glyphicon-eye-open"></i></a>',
                '%ISSIGN%==0' => '<a href="javascript:;" onclick="fthisedit(this,\''.U('Purchasing/contract','&kjcontract='.$this->_get('kjcontract')).'\');" class="contrtable-link btn btn-primary btn-xs" title="�༭"><i class="glyphicon glyphicon-edit"></i></a><a href="javascript:;" onclick="fthisShow(this,\''.U('Purchasing/contract','&kjcontract='.$this->_get('kjcontract')).'\');" class="contrtable-link btn btn-success btn-xs" title="�鿴"><i class="glyphicon glyphicon-eye-open"></i></a><a href="javascript:;" onclick="fthisDelContract(this );" class="contrtable-link btn btn-danger btn-xs" title="ɾ��"><i class="glyphicon glyphicon-trash"></i></a>');
			$form->GABTN = '<a href="javascript:void(0);" onclick="addkjcontract();" class="btn btn-info btn-sm">������ܺ�ͬ</a>
					<a href="javascript:void(0);" onclick="addreimbursement();" class="btn btn-info btn-sm">���ɱ�������</a>';
            
			$children = array(
				array('�ɹ���ϸ',U('/Purchasing/details?ischildren=1')),
			);
            
			$form->setChildren($children);
            
			if($this->_get('kjcontract') == 1 || $contract['TYPE'] == 2)
            {
				$form->setMyField('RELATIONID','FORMVISIBLE','0');
				$form->setMyField('REIM_STATUS','FORMVISIBLE','0');
				$form->setMyField('REIM_LIST_ID','FORMVISIBLE','0');
				$form->setMyFieldVal('TYPE','2',true);//��ܺ�ͬ
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
				$form->GABTN = '<a href="javascript:void(0);" onclick="addkjcontract();" class="btn btn-info btn-sm">������ܺ�ͬ</a>';
				$form->setChildren(null);
			}else 
			$form->where("TYPE = 1 AND PROMOTER = ".$uid." AND CITY_ID = ". $this->channelid);
		}
        
        /***�����ͬ***/
		if($_REQUEST['faction'] == 'saveFormData')
        {   
            //δ�ύ�ĺ�ͬ
			$form->setMyFieldVal('STATUS', '0' , TRUE);
			$form->setMyFieldVal('PROMOTER' , $uid);
            
            $id = intval($_POST['ID']);
            $issign = intval($_POST['ISSIGN']);
            $issign_old = intval($_POST['ISSIGN_OLD']);
            $contract_type = intval($_POST['TYPE']);
            
            /***�жϺ�ͬ�Ƿ��ϴ�***/
            if($_POST['FILEURL'] == '' && $contract_type == 2)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('����ʧ�ܣ���ͬ�����ϴ�!');

                echo json_encode($result);
                exit;
            }

            //���״̬�޸�Ϊ��ǩԼ
            if($id > 0 && $issign == -1 && $issign_old == 0)
            {   
                //�ɹ���ϸMODEL
                $purchase_list_model = D('PurchaseList');
                
                //���ݺ�ͬ��Ż�ȡ��ͬ�����вɹ���ϸ
                $purchase_list_info = $purchase_list_model->get_purchase_list_by_contract_id($id);
                
                if(is_array($purchase_list_info) && !empty($purchase_list_info))
                {   
                    //�ɱ�MODEL
                    $cost_model = D('ProjectCost');
                    
                    //�ɹ����뵥MODEL
                    $purchase_model = D('PurchaseRequisition');
                    
                    //�ֿ�����MODEL
                    $warehouse_use_model = D('WarehouseUse');
                    
                    //�ɹ�����
                    $purchase_type = $purchase_model->get_conf_purchase_type();
                    
                    foreach ($purchase_list_info as $key => $value)
                    {   
                        //�ɹ�ȫ��ͨ���ֿ����õ���ϸ���⴦��
                        if($value['USE_NUM'] > 0  && $value['NUM'] == 0 )
                        {
                            //���²ɹ���ϸ״̬Ϊ����
                            $update_num = $purchase_list_model->update_to_reimbursed_by_id($value['ID']);

                            //���²ɹ����뵥�Ѳɹ�(���ݲɹ�����Ų�ѯ�������вɹ���ϸ�Ƿ��Ѿ����ɹ����)
                            $is_all_purchased = $purchase_list_model->is_all_purchased($value['PR_ID']);

                            if($is_all_purchased)
                            {
                                $finish_result = $purchase_model->update_to_finished_by_id($value['PR_ID']);
                            }
                            
                            //���º�ͬΪ�ѱ���
                            $purchase_contract_model = D('PurchaseContract');
                            $contract_reimed = $purchase_contract_model->sub_contract_to_reimbursed_by_listid($value['CONTRACT_ID']);
                            if ($contract_reimed > 0) {
                                // ״ֵ̬Ϊ2�����ѱ���
                                $_POST['REIM_STATUS'] = 2;
                                $_REQUEST['REIM_STATUS'] = 2;

                                // �ڳɱ��������һ���ѱ����Ĳɹ�����
                                $this->addReimedCostList($value, $uid);
                            }
                        }
                        else
                        {
                            //���²ɹ���ϸ״̬Ϊ�Ѳɹ�
                            $update_num = $purchase_list_model->update_to_purchased_by_id($value['ID']);

                            //���²ɹ����뵥�Ѳɹ�(���ݲɹ�����Ų�ѯ�������вɹ���ϸ�Ƿ��Ѿ����ɹ����)
                            $is_all_purchased = $purchase_list_model->is_all_purchased($value['PR_ID']);

                            if($is_all_purchased)
                            {
                                $finish_result = $purchase_model->update_to_finished_by_id($value['PR_ID']);
                            }

                            //��Ŀ�ɹ���ϸ��ɱ���¼
                            if($value['TYPE'] == $purchase_type['project_purchase'])
                            { 
                                //��Ŀ�ɹ�ȷ������
                                if($value['USE_NUM'] > 0)
                                {
                                    $use_confirm_result = 
                                        $warehouse_use_model->confirm_used_by_purchase_id($value['ID']);
                                }

                                /***�ɱ�����***/
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

                                /***�ɹ����ֳɱ����뵽�ɱ�����***/
                                $buy_cost = $value['PRICE'] * $value['NUM'];
                                if($buy_cost > 0)
                                {
                                    $cost_info['EXPEND_FROM'] = 2;  //�ɹ���ͬǩ��
                                    $cost_info['FEE'] = $buy_cost;
                                    $cost_info['FEE_REMARK'] = '�ɹ���ͬǩ��';

                                    $result_buy = $cost_model->add_cost_info($cost_info);
                                }

                                //���ô��������ӳɱ�
                                if($value['USE_NUM'] > 0)
                                {
                                    /***���ò��ֽ���ɱ��⣬�ѱ���״̬***/
                                    $cost_info['EXPEND_FROM'] = 4;  //�ѱ���
                                    $cost_info['FEE'] = $value['USE_TOATL_PRICE'];
                                    $cost_info['FEE_REMARK'] = '�ɹ����óɱ�';

                                    $result_use = $cost_model->add_cost_info($cost_info);
                                }
                            }
                        }
                    }
                }
            }
            else if($id == 0)
            {   
                //��ͬMODEL
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
                
                //��Ӻ�ͬ
                $contract_id = $contract_model->add($cdata);
                
                if($contract_id > 0)
                {
                    $result['status'] = 1;
                    $result['msg'] = g2u('��ӳɹ�!');
                }
                else
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('���ʧ��!');
                }
                
                echo json_encode($result);
                exit;
            }
		}
        
        /***��Ӧ��***/
        if($showForm == 1 && $contract_id > 0)
        {   
            //�޸�
            $contract = M('Erp_contract')->field('TYPE,SUPPLIER_ID')->where("ID=".$this->_get('ID'))->find();
            $supplier_id = $contract['SUPPLIER_ID'];
            $sql = "SELECT ID,NAME FROM ERP_SUPPLIER WHERE (STATUS = 1 AND CITY_ID = '".$this->channelid."') OR ID = '".$supplier_id."'  ";
            $form->setMyField( 'SUPPLIER_ID', 'LISTSQL', $sql,true);
        }
        //������ܺ�ͬ
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
        
        //����״̬
        $purchase_contract_model = D('PurchaseContract');
        $conf_reim_status = $purchase_contract_model->get_conf_reim_status_remark();
        $form->setMyField('REIM_STATUS', 'LISTCHAR' , array2listchar($conf_reim_status), TRUE);
        
		$formHtml = $form->getResult();
		$this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
		$this->assign('layer',$this->_get('layer'));
		$this->assign('paramUrl',$this->_merge_url_param);
		if($this->_get('kjcontract') != 1){
		$this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param)); 
		}
		$this->display('contract');
	}
    
    
	/**
	 +----------------------------------------------------------
	 * ɾ����ͬ 
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
			$result['info']=u2g('�ɹ�');
		}else{
			$result['status'] = 0;
			$result['info']=u2g('ʧ��');
		}
        
		 echo json_encode($result);
	}
    
    
	/**
	 +----------------------------------------------------------
	 * �ɹ���������
	 +----------------------------------------------------------
	 * @param none
	 +----------------------------------------------------------
	 * @return none
	 +----------------------------------------------------------
	 */
	public function reim_manage()
	{   
		//����MODEL
		$reim_type_model = D('ReimbursementType');
		$reim_list_model = D('ReimbursementList');
		$reim_detail_model = D('ReimbursementDetail');
        
		$uid = intval($_SESSION['uinfo']['uid']);
		$city = $this->channelid;
		$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
		$showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
		
        //������״̬
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
            //���������
			$list_id = intval($_GET['ID']);
            
			$del_list_result = FALSE;
			$del_detail_result = FALSE;
            M()->startTrans();
			if($list_id > 0)
			{   
                $reim_detail = $reim_list_model->get_info_by_id($list_id ,array('TYPE'));
				//ͨ����������Ż�ȡ������ϸ�еĺ�ͬ���
                $reim_detail_model = D('ReimbursementDetail');

                //������������
                $returnUse = D("InboundUse")->backDisplaceUse($list_id);
                
                //ɾ��������
                $del_list_result = $reim_list_model->del_reim_list_by_ids($list_id);
                //ɾ��������ϸ
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
					//���²ɹ���ͬ����״̬Ϊδ����
					$purchase_contract_model = D('PurchaseContract');
//					$up_num_contract = $purchase_contract_model->sub_contract_to_reim_not_apply_by_reim_listid($list_id);

                    // ���²ɹ���ϸ��״̬Ϊ�Ѳɹ�
                    $updatedPurchase = $this->afterReimDetailDel($list_id, $reim_detail[0]['TYPE']);


				}
                //ɾ����������ϵ
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
				$info['msg']  = g2u('ɾ���ɹ�');
			}
			else if(!$del_detail_result)
			{
				M()->rollback();
				$info['status']  = 'error';
				$info['msg']  = g2u('������ϸɾ��ʧ��');
			}
			else
			{
				M()->rollback();
				$info['status']  = 'error';
				$info['msg']  = g2u('ɾ��ʧ��');
			}
	
			echo json_encode($info);
			exit;
		} else if ($faction == 'saveFormData') {  // ��������
            $reimListId = $_POST['ID'];
            $attachment = u2g($_POST['ATTACHMENT']);
            $dbResult = D('ReimbursementList')->where("ID = {$reimListId}")->save(array('ATTACHMENT' => $attachment));

            if ($dbResult !== false) {
                ajaxReturnJSON(true, u2g('���������޸ĳɹ�'));
            } else {
                ajaxReturnJSON(false, u2g('���������޸�ʧ��'));
            }
        }
        
        if($this->_merge_url_param['flowId'] > 0)
        {
            //��������ڱ༭Ȩ��
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

            if($flow_edit_auth)
            {   
                //����༭
                $form->EDITABLE = -1;
                $form->GABTN = "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>�������</a>";
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
            //����״̬����ɾ����ť�Ƿ���ʾ
            $form->DELCONDITION = '%STATUS% == 0';
            $form->EDITCONDITION = '%STATUS% == 0 AND (%TYPE% == 1 OR %TYPE% == 14)';

            $form->GABTN = "<a id='sub_reim_apply' href='javascript:;' class='btn btn-info btn-sm'>�ύ��������</a>  "
                    . "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>�������</a>";
                //."<a id = 'show_flow_step'  href='javascript:;' class='btn btn-info btn-sm'>���������ͼ</a>";
        }
        
		//���ñ���������
		$type_arr = $reim_type_model->get_reim_type();
		$form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
		 
		//���ñ�����״̬
		$status_arr = $reim_list_model->get_conf_reim_list_status_remark();
		$form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr), FALSE);
		 
		//����ҳ
		if($showForm > 0)
		{
			//�����
			$form = $form->setMyField('REIM_UID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
		}
	
		$children_data = array(
				array('������ϸ', U('/Purchasing/reim_detail_manage', 'fromTab=2')),
				array('�������', U('Loan/related_loan'))
		);
        
		$form =  $form->setChildren($children_data);
		$formHtml = $form->getResult();
		$this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
		$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
		$this->display('reim_manage');
	}
	
	
	/**
	 +----------------------------------------------------------
	 * ������ϸ
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
		 
		//�������뵥MODEL
		$reim_list_model = D('ReimbursementList');
		//����MODEL
		$reim_detail_model = D('ReimbursementDetail');
		//��������
		$reim_type_model = D('ReimbursementType');
		$list_info = $reim_list_model->get_info_by_id($list_id, array('STATUS','TYPE')); 
		Vendor('Oms.Form');
		$form = new Form();
        //����ʾ��ɾ��������
		if($faction == 'delData')
		{
			$id = intval($_GET['ID']);

            //������֤
            //ɾ����ϸʣ�����С�ڽ����
            if(D("Loan")->checkDelReim($list_id,$id)){
                $info['status']  = 'error';
                $info['msg']  = g2u('�Բ������˱����������Ľ�����Ѵ��ڱ�����ɾ��ʧ��!');
                die(json_encode($info));
            }

			$del_detail_result = FALSE;
			$up_list_result = FALSE;

            //������������
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

                    // ɾ�������õĳɱ�
                    $deleted = D('ProjectCost')->where("ORG_ENTITY_ID = {$reimDetail['BUSINESS_PARENT_ID']} AND ORG_EXPEND_ID = {$reimDetail['BUSINESS_ID']} AND EXPEND_FROM = 4 AND STATUS = 4")->delete();
				}
			}
			 
			if($del_detail_result > 0 && $up_list_result > 0 && $updatedPurchaseList > 0 && $deleted !== false && $returnUse!==false)
			{
				$info['status']  = 'success';
				$info['msg']  = g2u('ɾ���ɹ�');
			}
			else if(!$up_list_result)
			{
				$info['status']  = 'error';
				$info['msg']  = g2u('�������뵥������ʧ��');
			}
			else
			{
				$info['status']  = 'error';
				$info['msg']  = g2u('ɾ��ʧ��');
			}
			 
			echo json_encode($info);
			exit;
		}
		else if($faction == 'saveFormData' && $id > 0 )
		{	
			$reim_detail_info = $reim_detail_model->get_detail_info_by_id($id);
			
			if(is_array($reim_detail_info) && !empty($reim_detail_info))
			{
				//���������дʱ��Ҫ�жϲ��ܴ��ڲɹ�����*�ɹ�����
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
						$result['msg'] = '�޸ĳɹ�';
					}
					else
					{
						$result['status'] = 0;
						$result['msg'] = '�޸�ʧ��';
					}
				}
				else
				{
					$result['status'] = 0;
					$result['msg'] = '�޸�ʧ�ܣ��������ܴ��ڲɹ�����*�ɹ�����';
				}
			}
			else
			{
				$result['status'] = 0;
				$result['msg'] = '�޸�ʧ�ܣ�����ر�����ϸ��Ϣ';
			}
		
			$result['msg'] = g2u($result['msg']);
			echo json_encode($result);
			exit;
		}
		$reim_details_status = $reim_detail_model->get_conf_reim_detail_status();
		$cond_where = "LIST_ID = '".$list_id."' AND STATUS != '".$reim_details_status['reim_detail_deleted']."'";
		if ($list_info[0]['TYPE']==15){
		    $form = $form->initForminfo(198)->where($cond_where);
		    //�Ƿ��ʽ��
		    $form = $form->setMyField('ISFUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), FALSE);
		    //�Ƿ�۷�
		    $form = $form->setMyField('ISKF', 'LISTCHAR', array2listchar(array(1 => '��', 0 => '��')), FALSE);
		    $file1_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="1" href="javascript:void(0);">���ܱ�</a>';
		    $file2_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="2" href="javascript:void(0);">��ϸ��</a>';
            $file3_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="3" href="javascript:void(0);">��������ϸ</a>';
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

		    /***����״̬���Ʊ༭ɾ����ť�Ƿ���ʾ***/
		    $reim_list_status = !empty($list_info[0]['STATUS']) ? intval($list_info[0]['STATUS']) : 0;
		    $conf_reim_list_status = $reim_list_model->get_conf_reim_list_status();
		    if($conf_reim_list_status['reim_list_no_sub'] == $reim_list_status)
		    {
		        $form->EDITABLE = '-1';  // �ɱ༭
                $form->DELABLE = '-1';  // ��ɾ��
		    } 
			if($reimDetail['TYPE']==14)  $form->setMyField('PROJECTNAME', 'NOTNULL', '0', true);
		    //��ͬ���
//		    $form->setMyField('CONTRACT_ID', 'LISTSQL', 'SELECT ID, CONTRACTID FROM ERP_CONTRACT', TRUE);
		    //�ɹ���
		    $form->setMyField('P_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
		    $form->setMyField('ISFUNDPOOL', 'READONLY', '-1', TRUE);
		    $form->setMyField('ISKF', 'READONLY', '-1', TRUE);
            if ($showForm == 1) {  // ������ϸ�ı༭״̬���÷���ʱ�䲻�ɱ༭
                $form->setMyField('COST_OCCUR_TIME', 'READONLY', '-1', TRUE);
            }
		}
		//���ñ�����ϸ����
		$type_arr = $reim_type_model->get_reim_type();
		$form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
		//������ϸ״̬
		$reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
		$form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
		//����˰ֻ��
		$form->setMyField('INPUT_TAX', 'READONLY', '-1', TRUE);
		//��������
		if($showForm > 0 ){
		    //��������(���νṹ)
		    $form->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
		    $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, '
		            . ' PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
		}else{
		    //��������
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
     * �첽���²ɹ���ϸ�������
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
                ajaxReturnJSON(1, g2u('�ɹ���Ϣ���³ɹ�'));
            } else {
                D()->rollback();
                $msg = empty($msg) ? '�ɹ���Ϣ����ʧ��' : $msg;
                ajaxReturnJSON(0, g2u($msg));
            }
        }
    }
    
    
    /**
     +----------------------------------------------------------
     * �˿���ϸ���˿��ɾ��
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function delete_from_contract()
    {
        //ɾ�����˿���
        $purchase_details_id = intval($_POST['purchase_details_id']);
        
        if($purchase_details_id > 0)
        {   
            $purchase_list_model = D('PurchaseList');
            $update_num = $purchase_list_model->delete_from_contract($purchase_details_id);
            
            if($update_num > 0)
            {   
                $info['state']  = 1;
                $info['msg']  = '�Ӻ�ͬ���Ƴ��ɹ�';
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '�Ӻ�ͬ���Ƴ�ʧ��';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '��������';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * �������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    function opinionFlow()
    {   
        $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//��ĿID
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
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('����ʧ��');
                    }
                }elseif($_REQUEST['flowPass']){
					
                    $str = $workflow->passWorkflow($_REQUEST);
                    if($str){
                        js_alert('ͬ��ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('��ͬ��ʧ��');
                    }
                }elseif($_REQUEST['flowNot']){
					
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str){
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('���ʧ��');
                    }

                }elseif($_REQUEST['flowStop']){

					$auth = $workflow->flowPassRole($flowId);
					if(!$auth){
						js_alert('δ�����ؾ���ɫ');exit;
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
                        js_alert('�����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('����ʧ��');
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
                    js_alert('�ύ�ɹ�',U('Purchasing/opinionFlow',$this->_merge_url_param));
                    exit;
                }
                else
                {
                    js_alert('�ύʧ��',U('Purchasing/opinionFlow',$this->_merge_url_param));
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
     * С�۷�ɹ��б�ҳ��ͬ�ɹ���ϸ��
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
        
        $form->setMyField('PURCHASE_COST', 'GRIDVISIBLE', '0');//�ɹ��ɱ�
        $form->setMyField('TOTAL_COST', 'GRIDVISIBLE', '0');//�ϼƽ��
        $form->setMyField('S_ID', 'GRIDVISIBLE', -1);//��Ӧ��
        $form->setMyField('END_TIME', 'GRIDVISIBLE', -1);//�����ʹ�ʱ��
       if($_REQUEST['beeId']){
		   $form->where("ID=".$_REQUEST['beeId']);
	   }else {
		   $form->where("CONTRACT_ID is null and P_ID = ".$_SESSION['uinfo']['uid']);
	   }
        $zk_send_btn = "<a class=\"contrtable-link fedit send_zk\" href=\"javascript:void(0);\" class='btn btn-info btn-sm'>ͬ��</a>";
        $form->CZBTN = array('%ZK_STATUS%==0' => $zk_send_btn);
        /***��ҳ��***/
		if($_REQUEST['purchaseIds']|| $_REQUEST['beeId']){
			$url = '/Purchasing/bee_detail_list';
			if($_REQUEST['purchaseIds']) $url.= '/purchaseIds/'.$_REQUEST['purchaseIds'];
			if($_REQUEST['beeId']) $url.= '/beeId/'.$_REQUEST['beeId'];

			$children = array(array('С�۷�ɹ�������ϸ',U($url)), );
		}else{
			$children = array(array('С�۷�ɹ�������ϸ',U('/Purchasing/bee_detail_list')), );
		}
        $form->setChildren($children);
		
        $form->setMyField('PRICE', 'GRIDVISIBLE', -1);//�ɽ���
        $form->setMyField('NUM', 'GRIDVISIBLE', -1);//��������
        $form->setMyField('P_ID', 'GRIDVISIBLE', '0');
        $form->setMyField('STOCK_NUM', 'GRIDVISIBLE', '0');
        $list_arr = array(1 => '��', 0 => '��');
        $list_arr_zk = array(1 => '��ͬ��', 0 => 'δͬ��');
        //�Ƿ���ͬ�����ܿ�
        $form = $form->setMyField('ZK_STATUS', 'LISTCHAR', array2listchar($list_arr_zk), FALSE);
        //�Ƿ��ʽ��
	    $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar($list_arr), FALSE);
        //�Ƿ�۷�
	    $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar($list_arr), FALSE);
        
        //�ɹ���ϸMDOEL
        $purchase_list_model = D('PurchaseList');
        $purchase_arr = $purchase_list_model->get_conf_list_status_remark();
        
        //״̬��Ϣ
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($purchase_arr), FALSE);
        
        //��Ӧ��
        $form->setMyField('S_ID', 'EDITTYPE', 21, FALSE);
        $form = $form->setMyField('S_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);
        
        //�ɹ���
        $form->setMyField('P_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('P_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
             
        //�ɹ�������
        $form->setMyField('APPLY_USER_ID', 'FORMVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('APPLY_USER_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
        
        //�ɹ�����
        $purchase_model = D('PurchaseRequisition');
        $purchase_type_arr = $purchase_model->get_conf_purchase_type_remark();
        $form->setMyField('TYPE', 'GRIDVISIBLE', '-1', TRUE);
        $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($purchase_type_arr), FALSE);
        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('ischildren',$this->_get('ischildren'));
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs',$this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('bee');
    }
    /**
     * ����С�۷��������ڿ�
     */
    public function send_to_zk(){
        //��������ж�
        $id = $_GET['id'];
        if (!$id){
            ajaxJsonReturn(false,'',401);//�����������
        }
        //��ȡС�۷�ɹ���ϸ
        $purchase_list_model = D('PurchaseList');//�ɹ���ϸMDOEL
        $bee_field = array('ID', 'PR_ID', 'PRICE_LIMIT','NUM_LIMIT','ZK_STATUS');
        $bee = $purchase_list_model->field($bee_field)->where("ID=$id AND ZK_STATUS=0")->find();
        if (!$bee || empty($bee)){
            ajaxJsonReturn(false,'',402);//�������ݲ�����
        }
        //��ȡ�ɹ�������ϸ
        $purchase_model = D('PurchaseRequisition'); //�ɹ����뵥MODEL
        $requestion_field = array(
            'to_char(END_TIME, \'YYYY-MM-DD HH24:MI:SS\') as END_TIME',
            'CITY_ID',
            'REASON',
            'PRJ_ID',
        );
        $requestion = $purchase_model->field($requestion_field)->find($bee['PR_ID']);
        if (!$requestion || empty($requestion)){
            ajaxJsonReturn(false,'',402);//�������ݲ�����
        }
        //��������
        $curl_result = $this->_zk_api($requestion,$bee);
        if ($curl_result){
            $curl_result = json_decode($curl_result);
            if ($curl_result->code==200){
                $update_result = $purchase_list_model->where('ID='.$id)->save(array('ZK_STATUS'=>1));
                if ($update_result){
                    ajaxJsonReturn(true,'',200);//�������ݲ�����
                }
            }
        }
        ajaxJsonReturn(false,'',501);//�����ڿͽӿڳ���
    }
    /**
     * ����С�۷�������������ڿ�
     */
    public function send_result_to_zk(){
        $post = $_POST['data'];
        if (empty($post)){
            ajaxJsonReturn(400);//��ѡ������
        }
        //��ȡ��������
        $id_str = implode(',', $post);

        //��ȡ��Ҫ����������С�۷���������
        $model = D('PurchaseBeeDetails');
        $requestion = $model->where("ID in ($id_str) AND IS_BACK_TO_ZK=0 AND STATUS IN (2,3)")->select();
        if (!$requestion || empty($requestion)){
            ajaxJsonReturn(false,'',402);//û����Ҫ����������
        }
        //�ڿͽӿڵ�ַ
        $api = ZKAPI2;//http://zk.house365.com:8008/
        //��ȡ���м�ƴ
        $model_city = D('City');
        $city_id = intval($this->channelid);
        $city = $model_city->get_city_info_by_id($city_id);
        $citypy = strtolower($city["PY"]);
        //�������������ڿ�
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
            //��������
            $result = curlPost($api, $param);
            //����ʧ�ܷ��ش�����
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
     * С�۷���������ҳ��
     */
    public function bee_detail_list(){
		Vendor('Oms.Form');			
		$form = new Form();
		$form->initForminfo(196);
		$model = D('PurchaseBeeDetails');
        //״̬
        $status = $model->get_bee_detail_status();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status), FALSE);
        $form = $form->setMyField('IS_BACK_TO_ZK', 'LISTCHAR', array2listchar(array(0=>'��',1=>'��')), FALSE);
        $file1_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="1" href="javascript:void(0);" class="btn btn-info btn-xs">���ܱ�</a>';
        $file2_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="2" href="javascript:void(0);" class="btn btn-info btn-xs">��ϸ��</a>';
        $file3_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="3" href="javascript:void(0);" class="btn btn-info btn-xs">��������ϸ</a>';
        $form->CZBTN = $file1_btn.' '.$file2_btn.' '.$file3_btn;   
        $form->GABTN = '<a href="javascript:;" id="commit_bee_detail" class="btn btn-info btn-sm">����</a>'.' '.'<a href="javascript:;" id="back_result_to_zk" data-id="'.$_REQUEST['parentchooseid'].'" class="btn btn-info btn-sm">����״̬����</a>';
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
     * С�۷������ύ����
     */
    public function bee_detail_commit(){
        //��ȡҪ�ύ������
        $post = $_POST['data'];
        if (empty($post)){
            ajaxJsonReturn(400);//��ѡ������
        }
        //��ȡ��������
        $id_str = implode(',', $post);
        //ʵ��������
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //��ȡ�����ύ����Ҫ������С�۷�ɹ���ϸ����
		$bee_list_org = $model_bee_work->where("ID IN($id_str)")->select();
		foreach ($bee_list_org as $key=>$val){
			if($val['STATUS']!=0){
				  ajaxJsonReturn(503); //
			}

		}

        $bee_list_org = $model_bee_work->where("STATUS=0 AND ID IN($id_str)")->select(); 
        if (empty($bee_list_org)){
            ajaxJsonReturn(401);//ѡ�����ݲ����ڻ����ύ��������
        } 
		$reim_money_total = 0;
        //С�۷�ɹ���ϸ
		//������Ƿ��Ѿ�����������Χ
        $need_change_status = array();
        $bee_id = $bee_list_org[0]['P_ID'];
		foreach ($bee_list_org as $key=>$val){
			//���ܱ�����ͬ�Ĺ�Ӧ��
            if (!empty($supplier)){
                if ($supplier != $val['SUPPLIER_ID']){
                    ajaxJsonReturn(404); //��ѡ����ͬ�Ĺ�Ӧ�̽��б���
                }
            }
            $supplier = $val['SUPPLIER_ID'];
			$reim_money_total+=$val['REIM_MONEY'];
			$detail_sup_list[$val['SUPPLIER_ID']][] = $val;
			$need_change_status[] = $val['ID'];

		}
        $bee = $model_bee->find($bee_id);
        if (empty($bee)){
            ajaxJsonReturn(403);//С�۷�ɹ���ϸ������
        }
        //�����ܹ����Ա����Ľ��
        $bee['TOTAL'] = $bee['PRICE_LIMIT'] * $bee['NUM_LIMIT'];
		$map['P_ID'] = array('eq',$bee_id);
		$map['STATUS'] = array('in','1,2');
        $bee_ybx = $model_bee_work->where($map)->select();  
        if (!empty($bee_ybx)){
            $bee_list = array_merge($bee_list_org,$bee_ybx);
        }else $bee_list= $bee_list_org;
        $money_total = 0;
        
        //��Ӧ��
        $supplier = '';
        foreach ($bee_list as $key=>$val){
            //������ܴ��ڵ��쳣��������
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
            ajaxJsonReturn(402,$need_do_flows,$bee_id); //ѡ�б�������ѳ�����Ԥ����Ƿ����빤����
        }
		
        //���������ɱ�������
        $reim_list_model = D('ReimbursementList');      //�������뵥MODEL
        $reim_detail_model = D('ReimbursementDetail');  //������ϸMODEL
        $reim_list_model->startTrans();
        //���ɱ������뵥
        $uid = intval($_SESSION['uinfo']['uid']);//��ǰ�û����
        $user_truename = $_SESSION['uinfo']['tname'];//��ǰ�û�����
        $city_id = intval($this->channelid);//��ǰ���б��
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
					ajaxJsonReturn(500);//��ӱ�������ʧ��
					exit;
				}

			}
			 //���ɱ�����ϸ
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
					ajaxJsonReturn(501);  //��ӱ�����ϸʧ��
					exit;
				}
			}

		}
       
       
        //�޸�С�۷�������״̬
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>1));
        if (!$update_result){
            $reim_list_model->rollback();
            ajaxJsonReturn(502);  //�޸�С�۷�������״̬ʧ��
        }
        $reim_list_model->commit();
        ajaxJsonReturn(200);
    }
    /**
     * ����С�۷��������鸽��
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
            //��������ϸ��
            $this->_export_bee_data_details($param,$bee);
        }
    }
    
    /**
     * С�۷�ɹ����񸽼�3
     * @param unknown $param
     */
    private function _export_bee_data_details($param,$bee){
        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '����������ϸ��';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//Ĭ���п�
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//Ĭ���п�
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'����'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);
        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A3:R3')->getFont()->setBold(true);
        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'����������ϸ��'));
        
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->mergeCells('B2:D2');
        $taskStart = oracle_date_format($bee['EXEC_START'], 'Y-m-d');
        $taskEnd = oracle_date_format($bee['EXEC_END'], 'Y-m-d');
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",$taskStart.' �� '.$taskEnd));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",'�ͻ������ϼ�:'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",count($param)));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",'����ϼ�:'));
        
        $objActSheet->setCellValue('A3', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('B3', iconv("gbk//ignore","utf-8//ignore",'�������'));
        $objActSheet->setCellValue('C3', iconv("gbk//ignore","utf-8//ignore",'��ְ��Ա'));
        $objActSheet->setCellValue('D3', iconv("gbk//ignore","utf-8//ignore",'��ְ��Ա��ϵ�绰'));
        $objActSheet->setCellValue('E3', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('F3', iconv("gbk//ignore","utf-8//ignore",'����ʱ��'));
        $objActSheet->setCellValue('G3', iconv("gbk//ignore","utf-8//ignore",'�ͻ�����'));
        $objActSheet->setCellValue('H3', iconv("gbk//ignore","utf-8//ignore",'�ͻ��ֻ�'));
        $objActSheet->setCellValue('I3', iconv("gbk//ignore","utf-8//ignore",'�ͻ��Ա�'));
        $objActSheet->setCellValue('J3', iconv("gbk//ignore","utf-8//ignore",'�ͻ��ȼ�'));
        $objActSheet->setCellValue('K3', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('L3', iconv("gbk//ignore","utf-8//ignore",'��ע'));
        $objActSheet->mergeCells('A1:L1');
        $i = 4;
        $allmoney = 0;
        foreach($param as $k => $r){
            $file = iconv("gbk","utf-8",$r['file']);
            $file = str_replace(array("/","��",",","��")," ",$file);
            $file = json_decode($file,true);
            
            $allmoney += $r['10'];
            #���滻
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
     * С�۷�ɹ����񸽼�1
     * @param unknown $param
     */
    private function _export_bee_file_details($param,$bee){
        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '�ؿͷ�����ϸ��';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//Ĭ���п�
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//Ĭ���п�
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'����'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);
        $objActSheet->getRowDimension('3')->setRowHeight(26);
        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A3:R3')->getFont()->setBold(true);
        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'�ؿͷ�����ϸ��'));
        
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'��������:'));
        $objActSheet->mergeCells('B2:D2');
        $taskStart = oracle_date_format($bee['EXEC_START'], 'Y-m-d');
        $taskEnd = oracle_date_format($bee['EXEC_END'], 'Y-m-d');
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",$taskStart.' �� '.$taskEnd));
        $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'���ʺϼ�:'));
        $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'����ϼ�:'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'�����ܼ�:'));
        
        $objActSheet->setCellValue('A3', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('B3', iconv("gbk//ignore","utf-8//ignore",'�������'));
        $objActSheet->setCellValue('C3', iconv("gbk//ignore","utf-8//ignore",'��ְ��Ա'));
        $objActSheet->setCellValue('D3', iconv("gbk//ignore","utf-8//ignore",'��ϵ�绰'));
        $objActSheet->setCellValue('E3', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('F3', iconv("gbk//ignore","utf-8//ignore",'���ʱ�׼'));
        $objActSheet->setCellValue('G3', iconv("gbk//ignore","utf-8//ignore",'���˹���'));
        $objActSheet->setCellValue('H3', iconv("gbk//ignore","utf-8//ignore",'�����ͻ�'));
        $objActSheet->setCellValue('I3', iconv("gbk//ignore","utf-8//ignore",'���˴�������'));
        $objActSheet->setCellValue('J3', iconv("gbk//ignore","utf-8//ignore",'�����ӷ�'));
        $objActSheet->setCellValue('K3', iconv("gbk//ignore","utf-8//ignore",'����С��'));
        $objActSheet->mergeCells('A1:K1');
        $i = 4;
        $i = 4;
        $gz = 0; //���ʺϼ�
        $jj = 0; //����ϼ�
        $fh = 0; //�����ܼ�
        
        foreach($param as $k => $r){
            $file = iconv("gbk","utf-8",$r['file']);
            $file = str_replace(array("/","��",",","��")," ",$file);
            $file = json_decode($file,true);
            #���滻
            $objActSheet->setCellValue('A'.$i, $r['0']);
            $objActSheet->setCellValue('B'.$i, $r['1']);
            $objActSheet->setCellValue('C'.$i, $r['2']);
            $objActSheet->setCellValue('D'.$i, $r['3']);
            $objActSheet->setCellValue('E'.$i, $r['4']);
            $objActSheet->setCellValue('F'.$i, (float)$r['5'].iconv("gbk//ignore","utf-8//ignore",'Ԫ/����'));
            $objActSheet->setCellValue('G'.$i, $r['6'].iconv("gbk//ignore","utf-8//ignore",'Ԫ'));
            $objActSheet->setCellValue('H'.$i, $r['7'].iconv("gbk//ignore","utf-8//ignore",'��'));
            $objActSheet->setCellValue('I'.$i, $r['8'].iconv("gbk//ignore","utf-8//ignore",'Ԫ'));
            $objActSheet->setCellValue('J'.$i, '--');
            $objActSheet->setCellValue('K'.$i, $r['9'].iconv("gbk//ignore","utf-8//ignore",'Ԫ'));
            $objActSheet->getRowDimension($i)->setRowHeight(24);
            $i++;
            if($objActSheet->getRowDimension($i)->getRowHeight() > 0){
                $objActSheet->getRowDimension($i)->setRowHeight($objActSheet->getRowDimension($i)->getRowHeight()+20);
            }
            $gz += $r['6'];
            $jj += $r['8'];
            $fh += $r['9'];
        }
        $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",$gz.'Ԫ'));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",$jj.'Ԫ'));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",$fh.'Ԫ'));

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
     * С�۷�ɹ����񸽼�1
     * @param unknown $param
     */
    private function _export_bee_file_total($param,$bee){
        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '�ؿͷ��û��ܱ�';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//Ĭ���п�
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//Ĭ���п�
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'����'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);
        $objActSheet->getRowDimension('3')->setRowHeight(26);
        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A3:R3')->getFont()->setBold(true);
        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'�ؿͷ��û��ܱ�(���ջ���)'));
        
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'��������:'));
        $objActSheet->mergeCells('B2:D2');
        $taskStart = oracle_date_format($bee['EXEC_START'], 'Y-m-d');
        $taskEnd = oracle_date_format($bee['EXEC_END'], 'Y-m-d');
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",$taskStart.' �� '.$taskEnd));
        $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'���ʺϼ�:'));
        $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'����ϼ�:'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'�����ܼ�:'));
        
        $objActSheet->setCellValue('A3', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('B3', iconv("gbk//ignore","utf-8//ignore",'�������'));
        $objActSheet->setCellValue('C3', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('D3', iconv("gbk//ignore","utf-8//ignore",'���ʱ�׼'));
        $objActSheet->setCellValue('E3', iconv("gbk//ignore","utf-8//ignore",'ǩ������'));
        $objActSheet->setCellValue('F3', iconv("gbk//ignore","utf-8//ignore",'���˳���'));
        $objActSheet->setCellValue('G3', iconv("gbk//ignore","utf-8//ignore",'���˹���'));
        $objActSheet->setCellValue('H3', iconv("gbk//ignore","utf-8//ignore",'���˽���'));
        $objActSheet->setCellValue('I3', iconv("gbk//ignore","utf-8//ignore",'�����ӷ�'));
        $objActSheet->setCellValue('J3', iconv("gbk//ignore","utf-8//ignore",'���ϼ�'));
        
        $objActSheet->mergeCells('A1:J1');
        $i = 4;
        $gz = 0; //���ʺϼ�
        $jj = 0; //����ϼ�
        $fh = 0; //�����ܼ�
        foreach($param as $k => $r){
            $file = iconv("gbk","utf-8",$r['file']);
            $file = str_replace(array("/","��",",","��")," ",$file);
            $file = json_decode($file,true);
            #���滻
            $objActSheet->setCellValue('A'.$i, $r['0']);
            $objActSheet->setCellValue('B'.$i, $r['1']);
            $objActSheet->setCellValue('C'.$i, $r['2']);
            $objActSheet->setCellValue('D'.$i, (float)$r['3'].iconv("gbk//ignore","utf-8//ignore",'Ԫ/����'));
            $objActSheet->setCellValue('E'.$i, $r['4'].iconv("gbk//ignore","utf-8//ignore",'��'));
            $objActSheet->setCellValue('F'.$i, $r['5'].iconv("gbk//ignore","utf-8//ignore",'����'));
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
        $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",$gz.'Ԫ'));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",$jj.'Ԫ'));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",$fh.'Ԫ'));
        
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
     * С�۷������������
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
        $beeId = !empty($_GET['beeId']) ? $_GET['beeId'] : 0;//��ĿID
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
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('����ʧ��');
                    }
                }elseif($_REQUEST['flowNot']){
                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str){
                       // $model_bee_work->where('STATUS=4 AND P_ID='.$_REQUEST['beeId'])->save(array('STATUS'=>3));
						$this->_bee_option_follow_fail($_REQUEST['beeId']);
						js_alert('����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('���ʧ��');
                    }
                }elseif($_REQUEST['flowStop']){
                    $auth = $workflow->flowPassRole($flowId);
                    if(!$auth){
                        js_alert('δ�����ؾ���ɫ');exit;
                    }
                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str){
                        $this->_bee_option_follow_success($_REQUEST['beeId']);
                        js_alert('�����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('����ʧ��');
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
                    //����С�۷���ϸ�Ƿ��ѷ�������״̬
                    $model_bee->where('ID='.$beeId)->save(array('IS_APPLY_PROCESS'=>1));
                    $model_bee_work->where("ID IN ($beeDetailsId)")->save(array('STATUS'=>4));
                    js_alert('�ύ�ɹ�',U('Purchasing/bee',$this->_merge_url_param));
                    exit;
                }else{
                    js_alert('�ύʧ��',U('Purchasing/BeeOpinionFlow',$this->_merge_url_param));
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
     * ������������ͨ���Զ����ɱ�������
     * @param unknown $bee_id
     */
    private function _bee_option_follow_success($bee_id){
        //ʵ��������
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //С�۷�ɹ���ϸ
        $bee = $model_bee->find($bee_id);
        if (empty($bee)){
            return false;//С�۷�ɹ���ϸ������
        }
        //��ȡ�����ύ����Ҫ������С�۷�ɹ���ϸ����
        $bee_list = $model_bee_work->where("P_ID=$bee_id AND STATUS=4")->select();
        $money_total = 0;
        foreach ($bee_list as $key=>$val){
            $need_change_status[] = $val['ID'];
            $money_total+=$val['REIM_MONEY'];
        }
        //���������ɱ�������
        $reim_list_model = D('ReimbursementList');      //�������뵥MODEL
        $reim_detail_model = D('ReimbursementDetail');  //������ϸMODEL
        $reim_list_model->startTrans();
        //���ɱ������뵥
        $uid = $bee['P_ID'];//intval($_SESSION['uinfo']['uid']);//��ǰ�û����
		$user = M('Erp_users')->where("ID=$uid")->find();
        $user_truename = $user['NAME'];  //$_SESSION['uinfo']['tname'];//��ǰ�û�����
        $city_id = intval($this->channelid);//��ǰ���б��
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
            return false;  //��ӱ�������ʧ��
        }
        //���ɱ�����ϸ
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
                return false;  //��ӱ�����ϸʧ��
            }
        }
        //�޸�С�۷�������״̬
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>1,'CSTATUS'=>1));
        if (!$update_result){
            $reim_list_model->rollback();
            return false;  //�޸�С�۷�������״̬ʧ��
        }
        $reim_list_model->commit();
        return true;
    }
	/**
     * ������������ͨ���Զ����ɱ�������
     * @param unknown $bee_id
     */
    private function _bee_option_follow_fail($bee_id){
        //ʵ��������
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //С�۷�ɹ���ϸ
        $bee = $model_bee->find($bee_id);
        if (empty($bee)){
            return false;//С�۷�ɹ���ϸ������
        }
        //��ȡ�����ύ����Ҫ������С�۷�ɹ���ϸ����
        $bee_list = $model_bee_work->where("P_ID=$bee_id AND STATUS=4")->select();
        foreach ($bee_list as $key=>$val){
            $need_change_status[] = $val['ID'];
            //$money_total+=$val['REIM_MONEY'];
        } 
        //���������ɱ�������
       // $reim_list_model = D('ReimbursementList');      //�������뵥MODEL
        //$reim_detail_model = D('ReimbursementDetail');  //������ϸMODEL
        M()->startTrans();
        //���ɱ������뵥
        $uid = intval($_SESSION['uinfo']['uid']);//��ǰ�û����
        $user_truename = $_SESSION['uinfo']['tname'];//��ǰ�û�����
        $city_id = intval($this->channelid);//��ǰ���б��
         
		$project_cost_model = D("ProjectCost");
        //���ɱ�����ϸ
		$cost_insert_id = true;
        foreach ($bee_list as $key=>$value){
            $cost_info = array();
			$cost_info['CASE_ID'] = $bee["CASE_ID"]; //������� �����       
			$cost_info['ENTITY_ID'] = $bee["PR_ID"];                                 
			$cost_info['EXPEND_ID'] = $bee["ID"];                            
			$cost_info['ORG_ENTITY_ID'] = $bee["PR_ID"];                    
			$cost_info['ORG_EXPEND_ID'] = $bee["ID"];                  //ҵ��ʵ���� �����
			$cost_info['FEE'] = -$value['REIM_MONEY'];                // �ɱ���� ����� 
			$cost_info['ADD_UID'] = $bee["P_ID"];//$_SESSION["uinfo"]["uid"];            //�����û���� �����
			$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //����ʱ�� �����
			$cost_info['ISFUNDPOOL'] = $bee["IS_FUNDPOOL"];                  //�Ƿ��ʽ�أ�0��1�ǣ� �����
			$cost_info['ISKF'] = $bee["IS_KF"];                             //�ɱ�����ID �����
			//$cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //����˰ ��ѡ�
			$cost_info['FEE_ID'] =  $bee["FEE_ID"];   
			$cost_info['EXPEND_FROM'] = 31; //?
			$cost_info['FEE_REMARK'] = "�ɹ������������벵��";//�ɱ�����ID �����
			$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
			if(!$cost_insert_id){
				$cost_insert_id = false;
				break;
			}
		}
        //�޸�С�۷�������״̬
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>3,'CSTATUS'=>1));
        if (!$update_result || !$cost_insert_id){
            M()->rollback();
            return false;  //�޸�С�۷�������״̬ʧ��
        }
		send_result_to_zk($need_change_status,$this->channelid );//ͬ�����ڿ�
        M()->commit();
        return true;
    }
    /**
     * С�۷䳬��������������չʾ
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
        $form->setMyField('PURCHASE_COST', 'GRIDVISIBLE', '0');//�ɹ��ɱ�
        $form->setMyField('TOTAL_COST', 'GRIDVISIBLE', '0');//�ϼƽ��
        $form->setMyField('S_ID', 'GRIDVISIBLE', -1);//��Ӧ��
        $form->setMyField('END_TIME', 'GRIDVISIBLE', -1);//�����ʹ�ʱ��
        $form->where("CONTRACT_ID is null");
        $zk_send_btn = "<a class=\"contrtable-link fedit send_zk\" href=\"javascript:void(0);\">ͬ��</a>";
        $form->CZBTN = array('%ZK_STATUS%==0' => $zk_send_btn);
        /***��ҳ��***/
        $children = array(array('С�۷�ɹ�������ϸ',U('/Purchasing/bee_detail_list')), );
        $form->setChildren($children);

        $form->setMyField('PRICE', 'GRIDVISIBLE', -1);//�ɽ���
        $form->setMyField('NUM', 'GRIDVISIBLE', -1);//��������
        $form->setMyField('P_ID', 'GRIDVISIBLE', '0');
        $form->setMyField('STOCK_NUM', 'GRIDVISIBLE', '0');
        $list_arr = array(1 => '��', 0 => '��');
        $list_arr_zk = array(1 => '��ͬ��', 0 => 'δͬ��');
        //�Ƿ���ͬ�����ܿ�
        $form = $form->setMyField('ZK_STATUS', 'LISTCHAR', array2listchar($list_arr_zk), FALSE);
        //�Ƿ��ʽ��
        $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar($list_arr), FALSE);
        //�Ƿ�۷�
        $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar($list_arr), FALSE);

        //�ɹ���ϸMDOEL
        $purchase_list_model = D('PurchaseList');
        $purchase_arr = $purchase_list_model->get_conf_list_status_remark();

        //״̬��Ϣ
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($purchase_arr), FALSE);

        //��Ӧ��
        $form->setMyField('S_ID', 'EDITTYPE', 21, FALSE);
        $form = $form->setMyField('S_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);

        //�ɹ���
        $form->setMyField('P_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('P_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
         
        //�ɹ�������
        $form->setMyField('APPLY_USER_ID', 'FORMVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'GRIDVISIBLE', '-1', TRUE);
        $form->setMyField('APPLY_USER_ID', 'EDITTYPE', '21', TRUE);
        $form->setMyField('APPLY_USER_ID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);

        //�ɹ�����
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
     * �����õĲɹ���Ҫ�ڳɱ��������һ����¼
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
                'CASE_ID' => $purchaseInfo['CASE_ID'],  //������� �����
                'CASE_TYPE' => $purchaseRequisition[0]['SCALETYPE'],  // ������Ŀ
                'ENTITY_ID' => $purchaseInfo['PR_ID'],// ҵ��ʵ���� �����
                'EXPEND_ID' => $purchaseInfo['ID'],// �ɱ���ϸ��� �����
                'ORG_ENTITY_ID' => $purchaseInfo['PR_ID'],  // ҵ��ʵ���� �����
                'ORG_EXPEND_ID' => $purchaseInfo['ID'],
                'FEE' => $purchaseInfo['USE_TOATL_PRICE'],  // �ɱ���� �����
                'ADD_UID' => $uid,  //�����û���� �����
                'OCCUR_TIME' => $purchaseInfo['ADD_TIME'], //����ʱ�� �����
                'ISFUNDPOOL' => $purchaseInfo['IS_FUNDPOOL'],  // �Ƿ��ʽ�أ�0��1�ǣ� �����
                'ISKF' => $purchaseInfo['IS_KF'], // �Ƿ�۷� �����
                'INPUT_TAX' => $purchaseInfo['INPUT_TAX'],// ����˰ ��ѡ�
                'FEE_ID' => $purchaseInfo['FEE_ID'], // �ɱ�����ID �����
                'EXPEND_FROM' => 4, // ��Դ���ͣ�����ͨ��
                'STATUS' => 4,  // ����ͨ��
                'PROJECT_ID' => $purchaseRequisition[0]['PRJ_ID'],
                'USER_ID' => $purchaseRequisition[0]['USER_ID'],
                'DEPT_ID' => $purchaseRequisition[0]['DEPT_ID'],
                'CITY_ID' => $purchaseInfo['CITY_ID'],
                'FEE_REMARK' => '�ɹ�����������' //�������� ��ѡ�
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
                $result = true;  // �������ݿ���
                D()->startTrans();
                if (intval($purchase['USE_NUM']) != 0) {
                    $revertedData = array(
                        'use_num' => $purchase['USE_NUM'],
                        'purchaseId' => $purchase['ID']
                    );

                    // �˿�
                    $result = $this->rejectToWarehouse($revertedData);
                }

                if ($result !== false) {
                    // ɾ����Ӧ�Ĳɹ���ϸ
                    $result = D('erp_purchase_list')->where("ID = {$fid}")->delete();
                    if ($result !== false) {
                        $reqPurchaseCount = D('erp_purchase_list')->where("PR_ID = {$purchase['PR_ID']}")->count();
                        if ($reqPurchaseCount <= 0) {
                            // ����òɹ���ϸ���ڵĲɹ�����ֻ��һ������Ѳɹ�����Ҳɾ��
                            $result = D('erp_purchase_requisition')->where("ID = {$purchase['PR_ID']}")->delete();
                        }
                    }
                }

                if ($result !== false) {
                    $response['message'] = '�ɹ���ϸɾ���ɹ�';
                    D()->commit();
                } else {
                    $response['message'] = '�ɹ���ϸɾ��ʧ��';
                    D()->rollback();
                }
            }
        }

        $response['status'] = $result;
        echo json_encode(g2u($response));
    }

    /**
     * �������òɹ��˿�
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
                // ���¿����Ϣ
                $upWarehouseSql = <<<UPDATE_NUM_SQL
                UPDATE ERP_WAREHOUSE
                SET USE_NUM = USE_NUM - %d
                WHERE ID = %d
UPDATE_NUM_SQL;
                $result = D()->query(sprintf($upWarehouseSql, $v['USE_NUM'], $v['WH_ID']));

                // ɾ�����ü�¼
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
     * ����ɹ������²ɹ���
     * @param array $purchase �ɹ���Ϣ
     * @param string $msg �������
     * @return bool
     */
    private function updatePurchaseList($purchase, &$msg) {
        $result = false;
        if (notEmptyArray($purchase)) {
            $purchaseId = $purchase['purchase_id'];
            if ($purchaseId > 0) {
                $purchaseListModel = D('PurchaseList');

                // ���ݲɹ���ϸ��ѯ�ɹ���Ϣ
                $dbPurchase = D('PurchaseList')->where("ID = {$purchaseId}")->find();
                if (empty($dbPurchase)) {
                    $msg = '�ɹ���ϸ��Ϣ�쳣���ɹ���Ϣ����ʧ��';
                    return false;
                }

                // ������״̬������������֤
                if ($dbPurchase['USE_NUM'] == 0 && ($purchase['buy_num'] == 0 || $purchase['buy_price'] == 0 || $purchase['supplier_id'] == 0)) {
                    $msg = '�ɹ�������ʱ,�ɹ���Ӧ�̡��ɹ����ۡ��ɹ�������������д';
                    return false;
                }

                if (intval($purchase['buy_num']) > 0) {
                    //�жϲɹ����� �Ƿ��������޼�
                    if ($purchase['buy_price'] > $dbPurchase['PRICE_LIMIT']) {
                        $msg = '�ɹ��ɽ��۴�������޼ۣ��ɹ���Ϣ����ʧ��';
                        return false;
                    }

                    //�жϲɹ�����+�������� �Ƿ������������
                    if ($purchase['buy_num']  > ($dbPurchase['NUM_LIMIT'] - $dbPurchase['USE_NUM'])) {
                        $msg = '�ɹ��������������������ɹ���Ϣ����ʧ��';
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

                $update_arr['COST_OCCUR_TIME'] = $purchase['cost_occur_time'];  // ���÷���ʱ��
                $update_arr['STATUS'] = 1;  // �ɹ���ϸ�޸�Ϊ�Ѳɹ�
                $update_num = $purchaseListModel->update_purchase_list_by_id($purchaseId, $update_arr);


                if ($update_num !== false) {
                    if (D('PurchaseList')->is_all_purchased($dbPurchase['PR_ID'])) {
                        // �ɹ���ϸȫ���ɹ���������òɹ����뵥Ϊ�ɹ����״̬
                        $update_num = D('PurchaseRequisition')->where("ID = {$dbPurchase['PR_ID']}")->save(array('STATUS' => 4));
                    } else {
                        // �������òɹ����뵥Ϊ����ͨ��״̬
                        $update_num = D('PurchaseRequisition')->where("ID = {$dbPurchase['PR_ID']}")->save(array('STATUS' => 2));
                    }
					//��֧��ҵ��Ѵ���
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
                $msg = '��ѡ����Ҫ���µĲɹ���ϸ';
                return false;
            } else {
                $msg = '�ɹ���Ϣ����ʧ�ܣ��ɹ���ϸ��Ϣ�쳣��';
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
                        // ɾ�����õĲɹ��ɱ�
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