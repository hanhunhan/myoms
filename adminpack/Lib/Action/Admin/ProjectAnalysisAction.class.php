<?php

/**
 * ��Ŀ����״������
 *
 * @author liuhu
 */
class ProjectAnalysisAction extends ExtendAction{
    /**
     * �ʽ��״������
     */
    const FUND_POOL_STATUS_ADD = 498;
    private $model;
    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();
    
    
    //���캯��
    public function __construct() 
    {
         $this->model = new Model();
		parent::__construct();
        
        /**TAB URL����**/
        //��Ŀ���
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        //��������
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
        //����������
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = strip_tags($_GET['FLOWTYPE']) : '';
        //��������
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        //ҳǩ���
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] =  intval($_GET['RECORDID']) : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] =  intval($_GET['CASEID']) : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] =  intval($_GET['flowId']) : '';
        !empty($_GET['is_from']) ? $this->_merge_url_param['is_from'] = $_GET['is_from'] : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
    }
    
    
    /**
     +----------------------------------------------------------
     * �����б�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function income_list()
    {   
        //��ǰ���б��
    	$city_id = intval($this->channelid);
        $prj_id = $this->_merge_url_param['prjid'];
        $case_type = $this->_merge_url_param['CASE_TYPE'];
        $income_model = D('ProjectIncome');
        Vendor('Oms.Form');
        $form = new Form();
        
        $cond_where = "";
        
        if(!empty($_GET['CASEID']))
        {
            $cond_where .= "CASE_ID = '".intval($_GET['CASEID'])."'";
        }
        else if(!empty($case_type) && $prj_id > 0)
        {   
            $case_model = D('ProjectCase');
            $conf_case_type = $case_model->get_conf_case_type();
            $case_type_id = intval($conf_case_type[$case_type]);
            
            $cond_where .= "PROJECT_ID = '".$prj_id."' AND CASE_TYPE = '".$case_type_id."'";
        }
        else 
        {   
            //CITY_ID = '".$city_id."'
            $cond_where .= "1 = 0";
        }
        
        $form = $form->initForminfo(155)->where($cond_where);
        
        //��Ŀ����
        $sql = "SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE CITY_ID = '".$city_id."'";
	    $form = $form->setMyField('PROJECT_ID', 'LISTSQL', $sql, FALSE);
        
        //��Ŀ����
        $case_model = D('ProjectCase');
        $case_type_remark = $case_model->get_conf_case_type_remark();
        $form = $form->setMyField('CASE_TYPE','LISTCHAR', array2listchar($case_type_remark), FALSE);
        
        //������Դ
        $income_from = $income_model->get_conf_income_from();
        $form = $form->setMyField('INCOME_FROM','LISTCHAR', array2listchar($income_from), FALSE);
        
        //����״̬
        $income_status = $income_model->get_conf_income_status();
        $form = $form->setMyField('STATUS','LISTCHAR', array2listchar($income_status), FALSE);
        
        //������
        $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('income_list'); 
    }
    
    
    /**
     +----------------------------------------------------------
     * �ɱ��б�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function cost_list()
    {   
        //��ǰ���б��
    	$city_id = intval($this->channelid);
        $prj_id = $this->_merge_url_param['prjid'];
        
        //��Ŀ�ɱ�MODEL
        $cost_model = D('ProjectCost');
        
        Vendor('Oms.Form');
        $form = new Form();
        
        $cond_where = "";
        /***��Ŀ�б����\�����»ִ�����****/
        if(!empty($this->_merge_url_param['CASE_TYPE']) && $prj_id > 0)
        {   
            $case_model = D('ProjectCase');
            $case_type = $this->_merge_url_param['CASE_TYPE'];
            
            if($case_type == 'xmxhd')
            {   
                $cond_where .= "PROJECT_ID = '".$prj_id."' AND SUB_CASE_ID = '".intval($_GET['CASEID'])."' AND ISFUNDPOOL = 0";
            }
            else
            {   
                /**���ݰ�����Ż�ȡ��Ҫ�İ�����Ϣ**/
                $project_case_model = D('ProjectCase');
                $caseinfo = array();
                $caseinfo = $project_case_model->get_info_by_pid($prj_id, $case_type ,array('ID '));
                $cond_where .= "PROJECT_ID = '".$prj_id."' AND CASE_ID = '".intval($caseinfo[0]['ID'])."' AND ISFUNDPOOL = 0";
            }
        }
        /***���������****/
        else if(!empty($_GET['CASEID']))
        {
            $cond_where .= "CASE_ID = '".intval($_GET['CASEID'])."' AND ISFUNDPOOL = 0";
        }
        else 
        {   
            $cond_where .= "1 = 0";
        }
        
        $form = $form->initForminfo(156)->where($cond_where);
        
        //��Ŀ����
        $sql = "SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE CITY_ID = '".$city_id."'";
	    $form = $form->setMyField('PROJECT_ID', 'LISTSQL', $sql, FALSE);
        
        //��Ŀ����
        $case_model = D('ProjectCase');
        $case_type_remark = $case_model->get_conf_case_type_remark();
        $form = $form->setMyField('CASE_TYPE', 'LISTCHAR', array2listchar($case_type_remark), FALSE);
        
        //�ɱ���Դ
        $cost_from = $cost_model->get_conf_cost_from();
        $form = $form->setMyField('EXPEND_FROM', 'LISTCHAR', array2listchar($cost_from), FALSE);
        
        //�ɱ�״̬
        $cost_status = $cost_model->get_conf_cost_status();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($cost_status), FALSE);
        
        //������
        $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('cost_list'); 
    }
    
    
    /**
     +----------------------------------------------------------
     * �ɱ��б�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function occurred_cost_list()
    {        
        //��ǰ���б��
    	$city_id = intval($this->channelid);
        $prj_id = $this->_merge_url_param['prjid'];
        $case_type = $this->_merge_url_param['CASE_TYPE'];
        
        //��Ŀ�ɱ�MODEL
        $cost_model = D('ProjectCost');
        
        Vendor('Oms.Form');
        $form = new Form();
        
        $cond_where = "";
        
        /***���������***/
        if(!empty($_GET['CASEID']))
        {
            if ($case_type == 'xmxhd') {
                $cond_where .= sprintf("SUB_CASE_ID = %d AND ISFUNDPOOL = 1", $_REQUEST['CASEID']);
            } else {
                $cond_where .= "CASE_ID = '".intval($_GET['CASEID'])."' AND ISFUNDPOOL = 1";
            }

        }
        /***��Ŀ�б����***/
        else if(!empty($case_type) && $prj_id > 0)
        {   
            $case_model = D('ProjectCase');
            $conf_case_type = $case_model->get_conf_case_type();
            $case_type_id = intval($conf_case_type[$case_type]);

            $cond_where .= sprintf("PROJECT_ID = %d AND (CASE_TYPE = %d OR CASE_TYPE = 7) AND ISFUNDPOOL = 1", $prj_id, $case_type_id);
        }
        else 
        {
            $cond_where .= "1 = 0";
        }
        
        $form = $form->initForminfo(156)->where($cond_where);
        
        //��Ŀ����
        $sql = "SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE CITY_ID = '".$city_id."'";
	    $form = $form->setMyField('PROJECT_ID', 'LISTSQL', $sql, FALSE);
        
        //��Ŀ����
        $case_model = D('ProjectCase');
        $case_type_remark = $case_model->get_conf_case_type_remark();
        $form = $form->setMyField('CASE_TYPE', 'LISTCHAR', array2listchar($case_type_remark), FALSE);
        
        //�ɱ���Դ
        $cost_from = $cost_model->get_conf_cost_from();
        $form = $form->setMyField('EXPEND_FROM', 'LISTCHAR', array2listchar($cost_from), FALSE);
        
        //�ɱ�״̬
        $cost_status = $cost_model->get_conf_cost_status();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($cost_status), FALSE);
        
        //������
        $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->assign('is_from',$_GET['is_from']);
        $this->display('occurred_cost_list'); 
    }
    
    
    /**
     +----------------------------------------------------------
     * ��Ŀ�ʽ�����
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function fund_pool_status()
    {   
        Vendor('Oms.Form');
        $form = new Form();
        $prj_id = $this->_merge_url_param['prjid'];
        $city_id = $this->channelid;
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	$showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
    	$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        
        //��ǰ����
        $current_day = date('j');
        
        $add_endtime = 3;
        $add_stattime = 25;
        if($faction == 'saveFormData' && $id == 0)
    	{	
            if($current_day > $add_endtime && $current_day < $add_stattime)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('���ʧ��,�������ʱ�䷶Χ������'.$add_stattime.'�ŵ�����'.$add_endtime.'��');
                
                echo json_encode($result);
                exit;
            }
            
            $add_info = array();
            $add_info['PRJ_ID'] = intval($prj_id);
            if($add_info['PRJ_ID'] == 0)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('���ʧ��,���в����쳣');
                
                echo json_encode($result);
                exit;
            }
            
            //���в���
            $add_info['CITY_ID'] = intval($city_id);
            //�����·�
            $add_info['OCCUR_MONTH'] = strip_tags($_POST['OCCUR_MONTH']);
            
            //����Ƿ��Ѿ���ӹ����³ɱ�
            $cost_num = M('erp_project_fundpool_cost')->
                    where("PRJ_ID = '".$prj_id."' AND OCCUR_MONTH = '".$add_info['OCCUR_MONTH']."'")->count();
			$pre_fundpool_cost = M('erp_project_fundpool_cost')->
                    where("PRJ_ID = '".$prj_id."' AND OCCUR_MONTH <> '".$add_info['OCCUR_MONTH']."'")->order('ID DESC')->find();//var_dump($pre_fundpool_cost);
			$con_income = $pre_fundpool_cost ? $pre_fundpool_cost['CONFIRMED_INCOME'] : 0;
            if($cost_num > 0)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('���ʧ��,'.$add_info['OCCUR_MONTH'].'�������Ѵ��ڣ������ظ����');
                
                echo json_encode($result);
                exit;
            }
            
            $case_model = D('ProjectCase');
            $case_info = array();
            $case_info = $case_model->get_info_by_pid($prj_id, $this->_merge_url_param['CASE_TYPE']);
            
            if(empty($case_info))
            {
                $result['status'] = 0;
                $result['msg'] = g2u('���ʧ��,��������Ͱ�����Ϣ');
                
                echo json_encode($result);
                exit;
            }
            $this->model->startTrans();
            //�ۼƷ������ɱ��ϼ�
            $add_info['FUNDPOOL_COST_INCURRED'] = floatval($_POST['FUNDPOOL_COST_INCURRED']);
            //�ۼƿ�Ʊ����
            $add_info['CONFIRMED_INCOME'] = floatval($_POST['CONFIRMED_INCOME']);
            //�ʽ�ر���
            $add_info['FUNDPOOL_RATIO'] = floatval($_POST['FUNDPOOL_RATIO']);
            //�ʽ�سɱ�
            $add_info['FUNDPOOL_COST']  = ($add_info['CONFIRMED_INCOME'] * $add_info['FUNDPOOL_RATIO']) / 100;
            //���
            $add_info['MAX_FUNDPOOL_COST']  =  max($add_info['FUNDPOOL_COST_INCURRED'], $add_info['FUNDPOOL_COST']);
            
            //�ۼ�Ԥ��
            $add_info['PRE_DEDUCTION_TOTAL']    = 
                    $add_info['FUNDPOOL_COST_INCURRED'] > $add_info['FUNDPOOL_COST'] ? 
                        0 : $add_info['FUNDPOOL_COST'] - $add_info['FUNDPOOL_COST_INCURRED'];
			//����Ԥ��
            $add_info['PRE_DEDUCTION_MONTH'] = (float)$add_info['PRE_DEDUCTION_TOTAL'] - $pre_fundpool_cost['PRE_DEDUCTION_TOTAL'];//�����ۼ�Ԥ��-�ϸ��µ��ۼ�Ԥ��
				//max($add_info['FUNDPOOL_COST_INCURRED'], $add_info['FUNDPOOL_COST']);;
            //��ע
            $add_info['REMARK'] = strip_tags(u2g($_POST['REMARK']));
            $add_info['ADD_TIME'] = date('Y-m-d H:i:s');
            $add_info['ADD_UID'] = $uid;
            $add_info['CASE_ID'] = $case_info[0]['ID'];
            
            $add_result = M('erp_project_fundpool_cost')->add($add_info);

			if(!$add_result){
				$this->model->rollback();
				$return_error = '�ʽ��״�����ʧ��';
			}
			$project_cost_model = D("ProjectCost");
			$project_case_model = D("ProjectCase");
			$case_info = $project_case_model->get_info_by_pid($prj_id, 'ds', array('ID'));
			//������� �����
			$cost_info['CASE_ID'] = $case_info[0]['ID'];
			//ҵ��ʵ���� �����
			$cost_info['ENTITY_ID'] = $prj_id;
			$cost_info['EXPEND_ID'] = $prj_id;
			$cost_info['ORG_ENTITY_ID'] = $prj_id;
			$cost_info['ORG_EXPEND_ID'] = $prj_id;

			// �ɱ���� �����
			$cost_info['FEE'] =  ( ($add_info['CONFIRMED_INCOME']-$con_income) * $add_info['FUNDPOOL_RATIO']) / 100  *0.1 ;//˰��
			//�����û���� �����
			$cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
			//����ʱ�� �����
			$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());
			//�Ƿ��ʽ�أ�0��1�ǣ� �����
			$cost_info['ISFUNDPOOL'] = 0;
			//�ɱ�����ID �����
			$cost_info['ISKF'] = 1;
			//����˰ ��ѡ�
			$cost_info['INPUT_TAX'] = 0;
			//�ɱ�����ID �����
			//$cost_info['FEE_ID'] = $v["FEE_ID"];
			$cost_info['EXPEND_FROM'] = 29;
			$cost_info['FEE_REMARK'] = "����������˰��";
			$cost_info['FEE_ID'] = 96;
			$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
			if(!$cost_insert_id){
				$this->model->rollback();
				$return_error = '˰�����ʧ��';
			}
            $this->model->commit();
            if($add_result && $cost_insert_id)
    		{
    			$result['status'] = 1;
    			$result['msg'] = g2u('��ӳɹ�');
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = g2u('���ʧ��');
    		}
            
    		echo json_encode($result);
    		exit;
        }
        else if($faction == 'saveFormData' && $id > 0)
        {     
			$sql = "select * from ERP_PROJECT_FUNDPOOL_COST where  TO_DATE('".$_POST['OCCUR_MONTH']."','yyyy-mm')> TO_DATE(OCCUR_MONTH,'yyyy-mm') and PRJ_ID = '".$prj_id."' order by id desc ";
			$pre_fundpool_cost = M()->query($sql);
			$up_info = array();
            
            //�ۼƷ������ɱ��ϼ�
            $up_info['FUNDPOOL_COST_INCURRED'] = floatval($_POST['FUNDPOOL_COST_INCURRED']);
            //�ۼƿ�Ʊ����
            $up_info['CONFIRMED_INCOME'] = floatval($_POST['CONFIRMED_INCOME']);
            //�ʽ�ر���
            $up_info['FUNDPOOL_RATIO'] = floatval($_POST['FUNDPOOL_RATIO']);
            //�ʽ�سɱ�
            $up_info['FUNDPOOL_COST']  = ($up_info['CONFIRMED_INCOME'] * $up_info['FUNDPOOL_RATIO']) / 100;
            //���
            $up_info['MAX_FUNDPOOL_COST']  =  max($up_info['FUNDPOOL_COST_INCURRED'], $up_info['FUNDPOOL_COST']);
            //�ۼ�Ԥ��
            $up_info['PRE_DEDUCTION_TOTAL']    = 
                    $up_info['FUNDPOOL_COST_INCURRED'] > $up_info['FUNDPOOL_COST'] ? 
                        0 : $up_info['FUNDPOOL_COST'] - $up_info['FUNDPOOL_COST_INCURRED'];
			//����Ԥ��
            $up_info['PRE_DEDUCTION_MONTH'] = (float)$up_info['PRE_DEDUCTION_TOTAL'] - $pre_fundpool_cost[0]['PRE_DEDUCTION_TOTAL'];//�����ۼ�Ԥ��-�ϸ��µ��ۼ�Ԥ��
			 
            //��ע
            $up_info['REMARK'] = strip_tags(u2g($_POST['REMARK']));
            $update_result = M('erp_project_fundpool_cost')->where("ID = '".$id."'")->save($up_info);
            
            if($update_result)
    		{
    			$result['status'] = 1;
    			$result['msg'] = g2u('�޸ĳɹ�');
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = g2u('�޸�ʧ��');
    		}
            
    		echo json_encode($result);
    		exit;
        }
        
        $form = $form->initForminfo(173);
        if(!empty($this->_merge_url_param['CASEID']))
        {
            $cond_where .= "CASE_ID = ".intval($this->_merge_url_param['CASEID']);
            $form->where($cond_where);
        }
        else
        {
            $form->where("PRJ_ID = '".$prj_id."'");
        }
        
        //��������
        if($showForm == 3)
        {   
            if($current_day >= $add_stattime)
            {
                $month_val = date('Y-m' , time());
            }
            else if($current_day <= $add_endtime)
            {
                $month_val = date('Y-m' ,strtotime('-1 month'));
            }
            else
            {
                $this->error('���ʧ��,�������ʱ�䷶Χ������'.$add_stattime.'�ŵ�����'.$add_endtime.'��');
            }
            
            $form = $form->setMyFieldVal('OCCUR_MONTH', $month_val, TRUE);
            //������Ŀ��Ų�ѯ�ʽ�ر������̶��ʽ��ֻ�����ǹ̶��ʽ�ر����Լ���д��
            
            $house_model = D('House');
            $search_field = array('ISFUNDPOOL', 'FPSCALE', 'SPECIALFPDESCRIPTION');
            $house_info = $house_model->get_house_info_by_prjid($prj_id, $search_field);
            
            if(is_array($house_info) && !empty($house_info))
            {   
                //�����ʽ��
                if($house_info[0]['ISFUNDPOOL'] == -1)
                {  
                    $form->setMyFieldval('FUNDPOOL_RATIO', $house_info[0]['FPSCALE'], true);
                }
                else if($house_info[0]['ISFUNDPOOL'] == 0)
                {
                    $form->setMyFieldval('REMARK', $house_info[0]['SPECIALFPDESCRIPTION'], FALSE);
                }
                else if($house_info[0]['ISFUNDPOOL'] == 1)
                {
                    $this->error('���ʽ����Ŀ���޷��������');
                }
            }
            else
            {
                $this->error('��Ŀ���ʽ����Ϣ���޷��������');
            }
            
            $case_model = D('ProjectCase');
            $case_info = array();
            $case_info = $case_model->get_info_by_pid($prj_id, $this->_merge_url_param['CASE_TYPE']);
            $case_id = !empty($case_info) ? intval($case_info[0]['ID']) : 0;
            
            /***��Ŀ�ۼƷ������ʽ�سɱ�***/
            $sql = "SELECT getprjdata_new($case_id , 36) cost from dual";
            $cost_info = M('')->query($sql);
            $cost_incurred = !empty($cost_info) ? floatval($cost_info[0]['COST']) : 0;
            
            /***��Ŀ�ۼƿ�Ʊ����***/
            $sql = "SELECT getprjdata_new($case_id , 2) income from dual";
            $income_info = M('')->query($sql);
            $confirm_income = !empty($income_info) ? floatval($income_info[0]['INCOME']) : 0;
            
            //������Ŀ��Ż�ȡ��Ŀ�ۼ��ʽ�سɱ�����Ʊ����
            $form->setMyFieldval('FUNDPOOL_COST_INCURRED', $cost_incurred, TRUE);
            $form = $form->setMyFieldVal('CONFIRMED_INCOME', $confirm_income, TRUE);
        }elseif($showForm == 1){
				
            $house_model = D('House');
            $search_field = array('ISFUNDPOOL', 'FPSCALE', 'SPECIALFPDESCRIPTION');
            $house_info = $house_model->get_house_info_by_prjid($prj_id, $search_field);
            
            if(is_array($house_info) && !empty($house_info))
            {   
                //�����ʽ��
                if($house_info[0]['ISFUNDPOOL'] == -1)
                {  
                    $form->setMyField('FUNDPOOL_RATIO', 'READONLY', '-1',false);
                }
                 
                 
            }
		}
        
        if($showForm > 0)
        {
            $form->setMyField('FUNDPOOL_COST', 'FORMVISIBLE', '0', TRUE);
            $form->setMyField('MAX_FUNDPOOL_COST', 'FORMVISIBLE', '0', TRUE);
            $form->setMyField('PRE_DEDUCTION_TOTAL', 'FORMVISIBLE', '0', TRUE); 
        }
        
        //���������
        if($this->_merge_url_param['flowId'] > 0)
        {
            //��������ڱ༭Ȩ��
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);
            
            if($flow_edit_auth)
            {   
                //����༭
                $form->EDITABLE = '-1';
                $form->GABTN = '';
                $form->ADDABLE = '0';
            }
            else
            {
                //ɾ��
                $form->DELCONDITION = '1==0';
                //�༭
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = '0';
                $form->GABTN = '';
            }
        }
        
        $children_data = array(
                        array('������ϸ', U('ProjectAnalysis/occurred_cost_list', $this->_merge_url_param)),
            );
    	$form =  $form->setChildren($children_data);
        $form->refineGAButtons($this->getUserAuthorities(), array(), array(
            '_add' => 498,
        ));
        $formHtml = $form->getResult();
        if (empty($case_id)) {
            $case_info = D('ProjectCase')->get_info_by_pid($prj_id, $this->_merge_url_param['CASE_TYPE']);
            $case_id = !empty($case_info) ? intval($case_info[0]['ID']) : 0;
        }
        $this->assign('isShowOptionBtn', $this->isShowOptionBtn($case_id));
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('fund_pool_status'); 
    }
}

/* End of file BusinessAction.class.php */
/* Location: ./Lib/Action/BusinessAction.class.php */