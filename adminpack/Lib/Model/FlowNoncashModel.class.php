<?php
/**
 * 非付现成本工作流MODEL类
 *
 * @author sjm
 */
class FlowNoncashModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'FLOW_NONCASH';
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * 根据FLOWID获取信息
     * @param mixed $flowId 工作流id
     * return $array 
     */
    public function get_info($flowId)
    {
        if($flowId)
        {
            $info = $this->where(array('FLOWID' => $flowId))->select();
            return $info;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 添加信息
     * @param mixed $flowId 工作流id
     * @param array $nonCashCostIds 非付现成本id
     * return $array 
     */
    public function add_flow_noncash($flowId, $nonCashCostIds)
    {
        $response = FALSE;
        if($flowId && is_array($nonCashCostIds) && !empty($nonCashCostIds))
        {   
            foreach($nonCashCostIds as $nonCashCostId)
            {
                // 自增主键返回插入ID
                $res = $this->add(array('NONCASHCOSTID' => $nonCashCostId, 'FLOWID' => $flowId, 'STATUS' => 0));
            }
            $response = TRUE;
        }
        
        return $response;
    }
}

/* End of file FlowNoncashModel.class.php */
/* Location: ./Lib/Model/FlowNoncashModel.class.php */