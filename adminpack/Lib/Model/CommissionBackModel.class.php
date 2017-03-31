<?php

/* 
 * 中介佣金索回Model
 */
class CommissionBackModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'COMMISSION_BACK';
    
    //佣金索回状态标志数组
    protected $_conf_commission_status_remark = array(
                                        1 => '已索回',
                                        0 => '未索回',
                                        4 => '已删除',
    );
    
    //佣金索回数组
    protected $_conf_commission_status = array(
                                'have_back'=>1,
                                'no_back'  => 0,
                                'have_del' =>4,
                                
    );
    
    //构造方法
    public function __construct($name = '') {
        parent::__construct($name);
    }

    //获取佣金索回状态标识数组
    public function get_conf_commission_status_remark(){
        return $this->_conf_commission_status_remark;
    }
    
    //获取佣金索回状态数组
     public function get_conf_commission_status(){
        return $this->_conf_commission_status;
    }
    

    /**新增佣金索回记录
     * @param $commission_info array() 新增字段键值对
     * return $insertId 返回新插入的记录的Id 
     * 失败返回false
     */
    public function add_commission_info($commission_info){
        if(is_array($commission_info) && !empty($commission_info))
        {   
            // 自增主键返回插入ID
            $options['table'] = parent::getTableName();
            $insertId = $this->add($commission_info, $options);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    /**根据ID修改信息
     * @param $ids mixed 需要修改的记录的id 单个ID或数组
     * @param $update_arr array() 要修改的字段的键值对
     * return 成功  返回被形象的行数  失败 返回false 
     */
    
    public function update_commission_info_by_id($ids,$update_arr){
        $cond_where = "";
        
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
        
        $up_num = self::update_commission_info_by_cond($update_arr, $cond_where);
        
        return $up_num > 0  ? $up_num : FALSE;
        
    }
    
    /**
     * 根据条件
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_commission_info_by_cond($update_arr, $cond_where)
    {
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    		//echo $this->getLastSql();
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
        
    }
    
    /**
     * 根据ID删除会员佣金记录
     * @param $ids mixed  ID
     * return bool
     */
    public function del_commission_info_by_id($ids){

        if(is_array($ids) && !empty($ids)){
            $id_str = implode(",", $ids);
            $conf_where = "ID IN($id_str)";
        }else{
            $conf_where = "ID = $ids";
        }
        
        $result = $this->del_commission_info_by_conf();
        
        return $result;
    }
    
     /**
     * 根据条件删除会员佣金记录
     * @param $ids mixed  ID
     * return bool
     */

     public function del_commission_info_by_conf($conf_where = ""){
   
        if($conf_where){
            $update_arr["STATUS"] = $this->_conf_commission_status["have_del"];
            $result = $this->where($conf_where)->save($update_arr);
        }        
        return $result;
    }
 
}

