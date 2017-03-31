<?php

/**
 * ���̹��������
 *
 * @author liuhu
 */
class BusinessAction extends ExtendAction{
    
    /***TABҳ�������鼯��***/
    private $_merge_url_param = array();
    
    /**��ҳǩ���**/
    private $_tab_number = 5;
    
    /**ҵ�������ַ�������**/
    private $_case_type = 'ds'; 
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
        
        /***TAB URL����***/
        //��Ŀ���
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        //ҳǩ���
        $this->_merge_url_param['TAB_NUMBER'] = $this->_tab_number;
        //ҵ������
        $this->_merge_url_param['CASE_TYPE'] = $this->_case_type;
        //����������
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        //ҵ����ID
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        //������ҵ����
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] =  intval($_GET['RECORDID']) : '';
        //���������
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] =  intval($_GET['flowId']) : '';
        //��������������
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
        
        //��ĿȨ���ж�
        self::project_auth($this->_merge_url_param['prjid'], 1, $this->_merge_url_param['flowId']);
    }
    
    /**
     +----------------------------------------------------------
     * Ĭ�ϲ���
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

        //��ת
        $url =  U('Purchase/purchase_manage', $this->_merge_url_param);
        halt2('', $url);
        exit;
    }
}

/* End of file BusinessAction.class.php */
/* Location: ./Lib/Action/BusinessAction.class.php */