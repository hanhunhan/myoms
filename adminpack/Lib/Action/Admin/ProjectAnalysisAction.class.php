<?php

/**
 * 项目运行状况分析
 *
 * @author liuhu
 */
class ProjectAnalysisAction extends ExtendAction{
    /**
     * 资金池状况新增
     */
    const FUND_POOL_STATUS_ADD = 498;
    private $model;
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();
    
    
    //构造函数
    public function __construct() 
    {
         $this->model = new Model();
		parent::__construct();
        
        /**TAB URL参数**/
        //项目编号
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        //案例类型
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
        //工作流类型
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = strip_tags($_GET['FLOWTYPE']) : '';
        //案例类型
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        //页签编号
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] =  intval($_GET['RECORDID']) : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] =  intval($_GET['CASEID']) : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] =  intval($_GET['flowId']) : '';
        !empty($_GET['is_from']) ? $this->_merge_url_param['is_from'] = $_GET['is_from'] : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
    }
    
    
    /**
     +----------------------------------------------------------
     * 收益列表
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function income_list()
    {   
        //当前城市编号
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
        
        //项目名称
        $sql = "SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE CITY_ID = '".$city_id."'";
	    $form = $form->setMyField('PROJECT_ID', 'LISTSQL', $sql, FALSE);
        
        //项目类型
        $case_model = D('ProjectCase');
        $case_type_remark = $case_model->get_conf_case_type_remark();
        $form = $form->setMyField('CASE_TYPE','LISTCHAR', array2listchar($case_type_remark), FALSE);
        
        //收益来源
        $income_from = $income_model->get_conf_income_from();
        $form = $form->setMyField('INCOME_FROM','LISTCHAR', array2listchar($income_from), FALSE);
        
        //收益状态
        $income_status = $income_model->get_conf_income_status();
        $form = $form->setMyField('STATUS','LISTCHAR', array2listchar($income_status), FALSE);
        
        //经办人
        $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('income_list'); 
    }
    
    
    /**
     +----------------------------------------------------------
     * 成本列表
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function cost_list()
    {   
        //当前城市编号
    	$city_id = intval($this->channelid);
        $prj_id = $this->_merge_url_param['prjid'];
        
        //项目成本MODEL
        $cost_model = D('ProjectCost');
        
        Vendor('Oms.Form');
        $form = new Form();
        
        $cond_where = "";
        /***项目列表入口\案例下活动执行入口****/
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
                /**根据案例编号获取需要的案例信息**/
                $project_case_model = D('ProjectCase');
                $caseinfo = array();
                $caseinfo = $project_case_model->get_info_by_pid($prj_id, $case_type ,array('ID '));
                $cond_where .= "PROJECT_ID = '".$prj_id."' AND CASE_ID = '".intval($caseinfo[0]['ID'])."' AND ISFUNDPOOL = 0";
            }
        }
        /***工作流入口****/
        else if(!empty($_GET['CASEID']))
        {
            $cond_where .= "CASE_ID = '".intval($_GET['CASEID'])."' AND ISFUNDPOOL = 0";
        }
        else 
        {   
            $cond_where .= "1 = 0";
        }
        
        $form = $form->initForminfo(156)->where($cond_where);
        
        //项目名称
        $sql = "SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE CITY_ID = '".$city_id."'";
	    $form = $form->setMyField('PROJECT_ID', 'LISTSQL', $sql, FALSE);
        
        //项目类型
        $case_model = D('ProjectCase');
        $case_type_remark = $case_model->get_conf_case_type_remark();
        $form = $form->setMyField('CASE_TYPE', 'LISTCHAR', array2listchar($case_type_remark), FALSE);
        
        //成本来源
        $cost_from = $cost_model->get_conf_cost_from();
        $form = $form->setMyField('EXPEND_FROM', 'LISTCHAR', array2listchar($cost_from), FALSE);
        
        //成本状态
        $cost_status = $cost_model->get_conf_cost_status();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($cost_status), FALSE);
        
        //经办人
        $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('cost_list'); 
    }
    
    
    /**
     +----------------------------------------------------------
     * 成本列表
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function occurred_cost_list()
    {        
        //当前城市编号
    	$city_id = intval($this->channelid);
        $prj_id = $this->_merge_url_param['prjid'];
        $case_type = $this->_merge_url_param['CASE_TYPE'];
        
        //项目成本MODEL
        $cost_model = D('ProjectCost');
        
        Vendor('Oms.Form');
        $form = new Form();
        
        $cond_where = "";
        
        /***工作流入口***/
        if(!empty($_GET['CASEID']))
        {
            if ($case_type == 'xmxhd') {
                $cond_where .= sprintf("SUB_CASE_ID = %d AND ISFUNDPOOL = 1", $_REQUEST['CASEID']);
            } else {
                $cond_where .= "CASE_ID = '".intval($_GET['CASEID'])."' AND ISFUNDPOOL = 1";
            }

        }
        /***项目列表入口***/
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
        
        //项目名称
        $sql = "SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE CITY_ID = '".$city_id."'";
	    $form = $form->setMyField('PROJECT_ID', 'LISTSQL', $sql, FALSE);
        
        //项目类型
        $case_model = D('ProjectCase');
        $case_type_remark = $case_model->get_conf_case_type_remark();
        $form = $form->setMyField('CASE_TYPE', 'LISTCHAR', array2listchar($case_type_remark), FALSE);
        
        //成本来源
        $cost_from = $cost_model->get_conf_cost_from();
        $form = $form->setMyField('EXPEND_FROM', 'LISTCHAR', array2listchar($cost_from), FALSE);
        
        //成本状态
        $cost_status = $cost_model->get_conf_cost_status();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($cost_status), FALSE);
        
        //经办人
        $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        $formHtml = $form->getResult();
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->assign('is_from',$_GET['is_from']);
        $this->display('occurred_cost_list'); 
    }
    
    
    /**
     +----------------------------------------------------------
     * 项目资金池情况
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
        
        //当前日期
        $current_day = date('j');
        
        $add_endtime = 3;
        $add_stattime = 25;
        if($faction == 'saveFormData' && $id == 0)
    	{	
            if($current_day > $add_endtime && $current_day < $add_stattime)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('添加失败,添加数据时间范围：当月'.$add_stattime.'号到次月'.$add_endtime.'日');
                
                echo json_encode($result);
                exit;
            }
            
            $add_info = array();
            $add_info['PRJ_ID'] = intval($prj_id);
            if($add_info['PRJ_ID'] == 0)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('添加失败,城市参数异常');
                
                echo json_encode($result);
                exit;
            }
            
            //城市参数
            $add_info['CITY_ID'] = intval($city_id);
            //发生月份
            $add_info['OCCUR_MONTH'] = strip_tags($_POST['OCCUR_MONTH']);
            
            //检查是否已经添加过该月成本
            $cost_num = M('erp_project_fundpool_cost')->
                    where("PRJ_ID = '".$prj_id."' AND OCCUR_MONTH = '".$add_info['OCCUR_MONTH']."'")->count();
			$pre_fundpool_cost = M('erp_project_fundpool_cost')->
                    where("PRJ_ID = '".$prj_id."' AND OCCUR_MONTH <> '".$add_info['OCCUR_MONTH']."'")->order('ID DESC')->find();//var_dump($pre_fundpool_cost);
			$con_income = $pre_fundpool_cost ? $pre_fundpool_cost['CONFIRMED_INCOME'] : 0;
            if($cost_num > 0)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('添加失败,'.$add_info['OCCUR_MONTH'].'月数据已存在，无需重复添加');
                
                echo json_encode($result);
                exit;
            }
            
            $case_model = D('ProjectCase');
            $case_info = array();
            $case_info = $case_model->get_info_by_pid($prj_id, $this->_merge_url_param['CASE_TYPE']);
            
            if(empty($case_info))
            {
                $result['status'] = 0;
                $result['msg'] = g2u('添加失败,无相关类型案例信息');
                
                echo json_encode($result);
                exit;
            }
            $this->model->startTrans();
            //累计发生金额成本合计
            $add_info['FUNDPOOL_COST_INCURRED'] = floatval($_POST['FUNDPOOL_COST_INCURRED']);
            //累计开票收入
            $add_info['CONFIRMED_INCOME'] = floatval($_POST['CONFIRMED_INCOME']);
            //资金池比例
            $add_info['FUNDPOOL_RATIO'] = floatval($_POST['FUNDPOOL_RATIO']);
            //资金池成本
            $add_info['FUNDPOOL_COST']  = ($add_info['CONFIRMED_INCOME'] * $add_info['FUNDPOOL_RATIO']) / 100;
            //孰高
            $add_info['MAX_FUNDPOOL_COST']  =  max($add_info['FUNDPOOL_COST_INCURRED'], $add_info['FUNDPOOL_COST']);
            
            //累计预扣
            $add_info['PRE_DEDUCTION_TOTAL']    = 
                    $add_info['FUNDPOOL_COST_INCURRED'] > $add_info['FUNDPOOL_COST'] ? 
                        0 : $add_info['FUNDPOOL_COST'] - $add_info['FUNDPOOL_COST_INCURRED'];
			//当月预扣
            $add_info['PRE_DEDUCTION_MONTH'] = (float)$add_info['PRE_DEDUCTION_TOTAL'] - $pre_fundpool_cost['PRE_DEDUCTION_TOTAL'];//当月累计预扣-上个月的累计预扣
				//max($add_info['FUNDPOOL_COST_INCURRED'], $add_info['FUNDPOOL_COST']);;
            //备注
            $add_info['REMARK'] = strip_tags(u2g($_POST['REMARK']));
            $add_info['ADD_TIME'] = date('Y-m-d H:i:s');
            $add_info['ADD_UID'] = $uid;
            $add_info['CASE_ID'] = $case_info[0]['ID'];
            
            $add_result = M('erp_project_fundpool_cost')->add($add_info);

			if(!$add_result){
				$this->model->rollback();
				$return_error = '资金池状况添加失败';
			}
			$project_cost_model = D("ProjectCost");
			$project_case_model = D("ProjectCase");
			$case_info = $project_case_model->get_info_by_pid($prj_id, 'ds', array('ID'));
			//案例编号 【必填】
			$cost_info['CASE_ID'] = $case_info[0]['ID'];
			//业务实体编号 【必填】
			$cost_info['ENTITY_ID'] = $prj_id;
			$cost_info['EXPEND_ID'] = $prj_id;
			$cost_info['ORG_ENTITY_ID'] = $prj_id;
			$cost_info['ORG_EXPEND_ID'] = $prj_id;

			// 成本金额 【必填】
			$cost_info['FEE'] =  ( ($add_info['CONFIRMED_INCOME']-$con_income) * $add_info['FUNDPOOL_RATIO']) / 100  *0.1 ;//税金
			//操作用户编号 【必填】
			$cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
			//发生时间 【必填】
			$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());
			//是否资金池（0否，1是） 【必填】
			$cost_info['ISFUNDPOOL'] = 0;
			//成本类型ID 【必填】
			$cost_info['ISKF'] = 1;
			//进项税 【选填】
			$cost_info['INPUT_TAX'] = 0;
			//成本类型ID 【必填】
			//$cost_info['FEE_ID'] = $v["FEE_ID"];
			$cost_info['EXPEND_FROM'] = 29;
			$cost_info['FEE_REMARK'] = "第三方费用税金";
			$cost_info['FEE_ID'] = 96;
			$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
			if(!$cost_insert_id){
				$this->model->rollback();
				$return_error = '税金添加失败';
			}
            $this->model->commit();
            if($add_result && $cost_insert_id)
    		{
    			$result['status'] = 1;
    			$result['msg'] = g2u('添加成功');
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = g2u('添加失败');
    		}
            
    		echo json_encode($result);
    		exit;
        }
        else if($faction == 'saveFormData' && $id > 0)
        {     
			$sql = "select * from ERP_PROJECT_FUNDPOOL_COST where  TO_DATE('".$_POST['OCCUR_MONTH']."','yyyy-mm')> TO_DATE(OCCUR_MONTH,'yyyy-mm') and PRJ_ID = '".$prj_id."' order by id desc ";
			$pre_fundpool_cost = M()->query($sql);
			$up_info = array();
            
            //累计发生金额成本合计
            $up_info['FUNDPOOL_COST_INCURRED'] = floatval($_POST['FUNDPOOL_COST_INCURRED']);
            //累计开票收入
            $up_info['CONFIRMED_INCOME'] = floatval($_POST['CONFIRMED_INCOME']);
            //资金池比例
            $up_info['FUNDPOOL_RATIO'] = floatval($_POST['FUNDPOOL_RATIO']);
            //资金池成本
            $up_info['FUNDPOOL_COST']  = ($up_info['CONFIRMED_INCOME'] * $up_info['FUNDPOOL_RATIO']) / 100;
            //孰高
            $up_info['MAX_FUNDPOOL_COST']  =  max($up_info['FUNDPOOL_COST_INCURRED'], $up_info['FUNDPOOL_COST']);
            //累计预扣
            $up_info['PRE_DEDUCTION_TOTAL']    = 
                    $up_info['FUNDPOOL_COST_INCURRED'] > $up_info['FUNDPOOL_COST'] ? 
                        0 : $up_info['FUNDPOOL_COST'] - $up_info['FUNDPOOL_COST_INCURRED'];
			//当月预扣
            $up_info['PRE_DEDUCTION_MONTH'] = (float)$up_info['PRE_DEDUCTION_TOTAL'] - $pre_fundpool_cost[0]['PRE_DEDUCTION_TOTAL'];//当月累计预扣-上个月的累计预扣
			 
            //备注
            $up_info['REMARK'] = strip_tags(u2g($_POST['REMARK']));
            $update_result = M('erp_project_fundpool_cost')->where("ID = '".$id."'")->save($up_info);
            
            if($update_result)
    		{
    			$result['status'] = 1;
    			$result['msg'] = g2u('修改成功');
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = g2u('修改失败');
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
        
        //新增界面
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
                $this->error('添加失败,添加数据时间范围：当月'.$add_stattime.'号到次月'.$add_endtime.'日');
            }
            
            $form = $form->setMyFieldVal('OCCUR_MONTH', $month_val, TRUE);
            //根据项目编号查询资金池比例（固定资金池只读，非固定资金池比例自己填写）
            
            $house_model = D('House');
            $search_field = array('ISFUNDPOOL', 'FPSCALE', 'SPECIALFPDESCRIPTION');
            $house_info = $house_model->get_house_info_by_prjid($prj_id, $search_field);
            
            if(is_array($house_info) && !empty($house_info))
            {   
                //常规资金池
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
                    $this->error('非资金池项目，无法添加数据');
                }
            }
            else
            {
                $this->error('项目无资金池信息，无法添加数据');
            }
            
            $case_model = D('ProjectCase');
            $case_info = array();
            $case_info = $case_model->get_info_by_pid($prj_id, $this->_merge_url_param['CASE_TYPE']);
            $case_id = !empty($case_info) ? intval($case_info[0]['ID']) : 0;
            
            /***项目累计发生的资金池成本***/
            $sql = "SELECT getprjdata_new($case_id , 36) cost from dual";
            $cost_info = M('')->query($sql);
            $cost_incurred = !empty($cost_info) ? floatval($cost_info[0]['COST']) : 0;
            
            /***项目累计开票收入***/
            $sql = "SELECT getprjdata_new($case_id , 2) income from dual";
            $income_info = M('')->query($sql);
            $confirm_income = !empty($income_info) ? floatval($income_info[0]['INCOME']) : 0;
            
            //根据项目编号获取项目累计资金池成本、开票收入
            $form->setMyFieldval('FUNDPOOL_COST_INCURRED', $cost_incurred, TRUE);
            $form = $form->setMyFieldVal('CONFIRMED_INCOME', $confirm_income, TRUE);
        }elseif($showForm == 1){
				
            $house_model = D('House');
            $search_field = array('ISFUNDPOOL', 'FPSCALE', 'SPECIALFPDESCRIPTION');
            $house_info = $house_model->get_house_info_by_prjid($prj_id, $search_field);
            
            if(is_array($house_info) && !empty($house_info))
            {   
                //常规资金池
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
        
        //工作流入口
        if($this->_merge_url_param['flowId'] > 0)
        {
            //工作流入口编辑权限
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);
            
            if($flow_edit_auth)
            {   
                //允许编辑
                $form->EDITABLE = '-1';
                $form->GABTN = '';
                $form->ADDABLE = '0';
            }
            else
            {
                //删除
                $form->DELCONDITION = '1==0';
                //编辑
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = '0';
                $form->GABTN = '';
            }
        }
        
        $children_data = array(
                        array('发生明细', U('ProjectAnalysis/occurred_cost_list', $this->_merge_url_param)),
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
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('fund_pool_status'); 
    }
}

/* End of file BusinessAction.class.php */
/* Location: ./Lib/Action/BusinessAction.class.php */