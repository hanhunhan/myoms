<?php

/**
 * 报销类型管理类
 *
 * @author liuhu
 */
class ReimbursementTypeModel extends Model{
        
    /***报销类型***/
    private $_reim_type = array(
                                1 =>    '采购',
                                2 =>    '预算外其他费用',
                                3 =>    '电商会员中介佣金',
                                4 =>    '电商会员中介成交奖励',
                                5 =>    '电商会员置业顾问佣金',
                                6 =>    '电商会员置业顾问成交奖励',
                                7 =>    '现金带看奖',
                                8 =>    '带看奖',
                                9 =>    '分销会员中介佣金',
                                10 =>   '分销会员中介成交奖励',
                                11 =>   '分销会员置业顾问佣金',
                                12 =>   '分销会员置业顾问成交奖励',
                                14 =>   '大宗采购',
                                15 =>   '小蜜蜂采购',
                                16 =>   '支付第三方费用',
                                17 =>   '分销后佣中介佣金',  // 从佣金管理申请
                                21 =>   '电商会员外部成交奖励',
                                22 =>   '分销会员中介成交奖励',  // 从佣金管理申请
                                23 =>   '分销会员置业顾问成交奖励',  // 从佣金管理申请
                                24 =>   '分销外部成交奖励',  // 从佣金管理申请
								25 =>   '分销会员外部成交奖励（前佣）',//(前佣)
                            );
      
    /**
     * 获取报销类型
     *
     * @access	public
     * @param  none 
     * @return	array 报销类型数组
     */
    public function get_reim_type()
    {
        return $this->_reim_type;
    }
}

/* End of file ReimbursementListModel.class.php */
/* Location: ./Lib/Model/ReimbursementListModel.class.php */