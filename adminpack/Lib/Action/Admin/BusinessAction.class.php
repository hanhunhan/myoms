<?php

/**
 * 电商管理控制器
 *
 * @author liuhu
 */
class BusinessAction extends ExtendAction{
    
    /***TAB页参数数组集合***/
    private $_merge_url_param = array();
    
    /**子页签编号**/
    private $_tab_number = 5;
    
    /**业务类型字符串描述**/
    private $_case_type = 'ds'; 
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
        
        /***TAB URL参数***/
        //项目编号
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        //页签编号
        $this->_merge_url_param['TAB_NUMBER'] = $this->_tab_number;
        //业务类型
        $this->_merge_url_param['CASE_TYPE'] = $this->_case_type;
        //工作流类型
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        //业务案例ID
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        //工作流业务编号
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] =  intval($_GET['RECORDID']) : '';
        //工作流编号
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] =  intval($_GET['flowId']) : '';
        //工作流操作类型
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
        
        //项目权限判断
        self::project_auth($this->_merge_url_param['prjid'], 1, $this->_merge_url_param['flowId']);
    }
    
    /**
     +----------------------------------------------------------
     * 默认操作
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function index()
    {
        if (!empty($this->_merge_url_param['TAB_NUMBER'])) {
            $hasTabAuthority = $this->checkTabAuthority($this->_merge_url_param['TAB_NUMBER']);
            if ($hasTabAuthority['result']) {
                $roleInfo = D('erp_role')->where("LOAN_ROLEID = {$hasTabAuthority['roleID']}")->find();
                $url = U("{$roleInfo['LOAN_ROLECONTROL']}/{$roleInfo['LOAN_ROLEACTION']}", $this->_merge_url_param);
                halt2('', $url);
                return;
            }
        }

        //跳转
        $url =  U('Purchase/purchase_manage', $this->_merge_url_param);
        halt2('', $url);
        exit;
    }
}

/* End of file BusinessAction.class.php */
/* Location: ./Lib/Action/BusinessAction.class.php */