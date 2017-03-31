<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FlowtypeModel extends Model{
     
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'FLOWTYPE';
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     *  不同的工作流不同的颜色
     * @return array
     */
    public function get_status_color(){
        $color = array(
            /**项目模块**/
            //项目下活动申请
            'xiangmuxiahuodong'=>'#00c0ef',
            //独立活动变更
            'dulihuodongbiangeng'=>'#00c0ef',
            //项目下活动变更
            'xiangmuxiahuodongbiangeng'=>'#00c0ef',
            //标准调整
            'biaozhuntiaozheng'=>'#00c0ef',
            //垫资比例调整
            'dianziedu'=>'#00c0ef',
            //项目决算
            'xiangmujuesuan'=>'#00c0ef',
            //项目终止
            'xiangmuzhongzhi'=>'#00c0ef',
            //独立活动立项
            'dulihuodong'=>'#00c0ef',
            //立项申请
            'lixiangshenqing'=>'#00c0ef',
            //立项变更
            'lixiangbiangeng'=>'#00c0ef',
            //成本划拨
            'chengbenhuabo'=>'#00c0ef',
            //项目---决算
            'xiangmujuesuan'=>'#00c0ef',

            /**会员模块***/
            //退款审核
            'tksq'=>'#dd4b39',
            //会员退票流程
            'huiyuantuipiao'=>'#dd4b39',
            //会员换发票流程
            'huiyuanhuanpiao'=>'#dd4b39',
            //减免申请流程
            'jianmianshenqing'=>'#dd4b39',

            /**财务模块**/
            //合同开票
            'hetongkaipiao'=>'#f39c12',
            //非付现成本申请
            'feifuxianchengbenshenqing'=>'#f39c12',
            //业务津贴
            'yewujintie'=>'#f39c12',
            //报销申请
            'baoxiaoshenqing'=>'#f39c12',
            //借款申请
            'jiekuanshenqing'=>'#f39c12',
            //小蜜蜂报销超额流程
            'xiaomifengchaoe'=>'#f39c12',
            //预算外其他费用申请
            'yusuanqita'=>'#f39c12',
            //非现金支付申请
            'feixianjinzhifushenqing'=>'#f39c12',
            //垫资比例超额报销
            'dianzibilichaoe' =>'#f39c12',

            /**采购模块**/
            //采购申请
            'caigoushenqing'=>'#3d9970',

            /**置换模块**/
            'zhihuanshenqing'=>'#3d9970', //置换申请
            'neibulingyong'=>'#3d9970', //置换内部领用
            'shoumai'=>'#3d9970', //置换售卖
            'baosun'=>'#3d9970', //置换报损
            'shoumaibiangeng'=>'#3d9970', //置换报损

        );

        return $color;
    }

    /**
     * 根据流程类型ID查询流程类型信息
     *
     * @access	public
     * @param  mixed $ids 流程类型编号
     * @param array $search_field 搜索字段
     * @return	array 流程类型信息
     */
    public function get_info_by_id($ids, $search_field = array())
    {   
        $cond_where = "";
        $flow_type_info = array();
       
        if(is_array($ids) && !empty($ids))
        {   
            $ids_str = implode(',', $ids);
            $cond_where = " ID IN (".$ids_str.")";
        }
        else
        {
            $id  = intval($ids);
            $cond_where = " ID = '".$id."'";
        }
        
        $flow_type_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $flow_type_info;
    }
    
    /**
     * 根据条件获取流程类型信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 项目信息
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $flow_type_info = array();
        
        $cond_where = strip_tags($cond_where);
        
        $table = $this->tablePrefix.$this->tableName;
         
        if(empty($cond_where) || $cond_where == "")
        {
            return $project_info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $flow_type_info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $flow_type_info = $this->where($cond_where)->select();
        }
       // echo $this->getLastSql();
        return $flow_type_info;
    }
}