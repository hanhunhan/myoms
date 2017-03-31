<?php

/**
 * 电商业务办卡客户管理类
 *
 * @author liuhu
 */
class MemberModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CARDMEMBER';
    
    /***会员开票状态***/
    private $_conf_invoice_status = array(
							    		'no_invoice' => '1',     //未开
							    		'apply_invoice' => '5',  //开票申请中
							    		'invoiced' => '2',       //已开未领
							    		'has_taken' => '3',      //已领
							    		'callback' => '4'       //已回收
							    	);


    /***办卡状态****/
    private $_conf_card_status = array(
                                    '1' => '已办未成交',
                                    '2' => '已办已认购',
                                    '3' => '已办已签约',
                                    );
    /**收据状态****/
    private $_conf_receipt_status = array(
                                    '2' => "已开未领",
                                    '3' => "已领",
                                    '4' => "已收回",
                                    );


    /***证件号码****/
    private $_conf_certificate_type = array(
    								'1' => '身份证',
						    		'2' => '户口簿',
						    		'3' => '军官证',
						    		'4' => '士兵证',
						    		'5' => '警官证',
						    		'6' => '护照',
						    		'7' => '台胞证',
						    		'8' => '回乡证',
						    		'9' => '身份证（港澳）',
						    		'10' => '营业执照',
						    		'11' => '法人代码',
						    		'12' => '其它',
    								);

    private $_conf_member_source_remark = array(
                                        '1' => '中介',
                                        '2' => '渠道',
                                        '3' => '数据营销',
                                        '4' => '拓客',
                                        '5' => '线上',
                                        '6' => '自然来客'
                                    );
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 获取会员开票状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_status()
    {
    	return $this->_conf_invoice_status;
    }
    
    
    /**
     * 获取会员开票状态描述数组
     *
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_status_remark()
    {
    	$conf_invoice_status_remark = array();
    	 
    	$conf_invoice_status_remark = self::get_conf_all_status_remark('INVOICE_STATUS');
    	 
    	return $conf_invoice_status_remark;
    }
    
    
    /**
     * 获取证件类型数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_certificate_type()
    {
    	return $this->_conf_certificate_type;
    }

    /**
     * 获取会员来源数组描述
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_member_source_remark()
    {
        return $this->_conf_member_source_remark;
    }
    
    
    /**
     * 获取会员开票、办卡、收据、财务确认状态数组
     *
     * @access	public
     * @param	string $field_name 开票/办卡/收据/发票/状态字段名称
     * @return	array
     */
    public function get_conf_all_status_remark($field_name = '')
    {   
    	$cond_where = "T.ID = S.TYPE AND S.STATUS > 0 ";
    	$cond_where .= $field_name !== '' ?
    	"AND T.FIELD_NAME = '".$field_name."' " : " AND T.FIELD_NAME IS NOT NULL";
    	$order_by = "TYPE ASC,QUEUE ASC";
    	$statu_info = M()->table(array('ERP_STATUS_TYPE'=>'T', 'ERP_STATUS'=>'S'))->
    	field('T.FIELD_NAME, S.STATUS, S.STATUSNAME')->where($cond_where)->order($order_by)->select();
        
    	$status_arr = array();
    	foreach($statu_info as $key => $value)
    	{
    		$status_arr[$value['FIELD_NAME']][$value['STATUS']] = $value['STATUSNAME'];
    	}
    	 
    	return $status_arr;
    }
    
    
    /**
     * 添加办卡会员信息
     * @param array $member_info 会员信息数组
     * @return mixed 添加成功返回插入数据编号，插入失败返回FALSE
     */
    public function add_member_info($member_info) 
    {   
        if(is_array($member_info) && !empty($member_info))
        {   
            // 自增主键返回插入ID
            $options['table'] = parent::getTableName();
            $insertId = $this->add($member_info, $options);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据编号删除办卡会员信息
     *
     * @access	protected
     * @return	int 删除条数，0删除失败
     */
    public function delete_info_by_id()
    {   
        
    }
    
    
    /**
     * 根据多个编号批量删除办卡会员信息
     *
     * @access	protected
     * @param	array  $arr_mids 办卡会员编号数组
     * @return	int 删除条数，0删除失败
     */
    public function delete_info_by_ids($arr_mids)
    {   
        
    }
	    
	
    /**
     * 更新办卡会员信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_id($ids, $update_arr)
    {   
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
        
        $up_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $up_num > 0  ? $up_num : FALSE;
    }

    /**
     * get_userlist_by_cond
     *
     * 根据条件获取办卡用户信息
     *
     * @access  public
     * @param   int $city 城市编号
     * @param   string $truename 真实姓名
     * @param   string $telno 手机号码
     * @param   int $start 偏移量
     * @param   int $limit 显示个数
     * @return  array  楼盘信息数组
     */

    public function get_userlist_by_cond( $city, $truename = '', $telno = '', $start = 0, $limit = 2){
        //返回用户列表
        $userinfo_arr = array();

        $now_date = date("Y-m-d",time());
        $telno = trim(strip_tags($telno));
        $truename = trim(strip_tags($truename));

        if($truename == '' &&  $telno == '')
        {
            return $userinfo_arr;
        }

        $cond_where = " 1=1 ";

        if( $city != '')
        {
            $cond_where .= "and erp_cardmember.city_id =".intval($city);
        }

        if( $telno != '')
        {
            $cond_where .= " and erp_cardmember.mobileno like '%".$telno."%'";
        }

        if($truename != '')
        {
            $cond_where .= " and erp_cardmember.realname like '%".$truename."%' ";
        }
        $cond_where .= " and erp_project.bstatus = 2 and erp_project.etime < to_date('$now_date','yyyy-mm-dd')";

        $userinfo_arr = $this->join("erp_project on erp_cardmember.prj_id = erp_project.id")
                            ->field("erp_cardmember.id,erp_cardmember.realname,erp_cardmember.mobileno,erp_cardmember.looker_mobileno,erp_cardmember.city_id,erp_cardmember.prj_id,erp_project.projectname,erp_project.etime")
                            ->where($cond_where)
                            ->order("erp_cardmember.id desc")
                            ->limit("$start,$limit")
                            ->select();

        return $userinfo_arr;
    }

    /**
     * get_project_arr_by_pid
     *
     * 根据条件获取办卡用户信息
     *
     * @access  public
     * @param   mixed $fid 项目编号，数组或者整数
     * @return  array  楼盘信息数组
     */
    function get_project_arr_by_pid($fid)
    {
        //返回数据
        $project_arr = array();

        $cond_where = ' 1=1 ';

        if(is_array($fid) && !empty($fid))
        {
            $pid_str = implode( ',' , $fid );
            $cond_where .= " and erp_project.id in (".$pid_str.")";
        }
        else if($fid > 0)
        {
            $cond_where .= " and erp_project.id = '".$fid."'";
        }

        $project_arr = M("erp_project")
            ->join("erp_house on erp_project.id = erp_house.project_id")
            ->field("erp_project.id,erp_project.projectname,erp_house.rel_newhouseid")
            ->where($cond_where)
            ->select();

        return $project_arr;
    }


    /**
     * 更新某条办卡会员信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_cond($update_arr, $cond_where)
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
     * get_userinfo_by_uid
     *
     * 根据用户名获取办卡用户信息
     *
     * @access  public
     * @param   int $uid 用户ID
     * @return  array  客户详细信息
     */
    function get_userinfo_by_uid($uid)
    {
        //返回数据
        $userinfo_arr = array();

        $uid = intval($uid);
        $cond_where = " id = '".$uid."'";

        $userinfo_arr = $this
            ->where($cond_where)
            ->find();

        return $userinfo_arr;
    }

    /**
     * get_cityinfo
     *
     * 获取城市的信息
     *
     * @access  public
     * @param   none
     * @return  array  城市的相关信息
     */
    function get_cityinfo($method='name')
    {
        //返回数据
        $cityinfo_arr = array();

        $cityinfo_arr = M("erp_city")
            ->field("id,name,py")
            ->select();

        if($method=='name') {
            foreach ($cityinfo_arr as $key => $val) {
                $cityinfo[$val['ID']] = $val['NAME'];
            }
        }
        else if($method='py'){
            foreach ($cityinfo_arr as $key => $val) {
                $cityinfo[$val['ID']] = $val['PY'];
            }
        }

        return $cityinfo;
    }


    /**
     * 根据条件办卡会员信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 办卡会员信息
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {
        $info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        
        return $info;
    }
    
    
    /**
     * 根据会员编号获取办卡会员信息（单一用户）
     *
     * @access	public
     * @param  int $id 搜索ID
     * @param array $search_field 搜索字段
     * @return	array 办卡会员信息
     */
    public function get_info_by_id($id, $search_field = array())
    {   
        $info = array();
        
        $id = intval($id);
        if($id <= 0)
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where("ID = $id")->find();
        }
        else
        {
            $info = $this->where("ID = $id")->find();
        }
        //echo $this->_sql();
        return $info;
    }
    
    /**
     * 根据会员编号获取办卡会员信息（多用户）
     *
     * @access	public
     * @param  array $ids 搜索ID
     * @param array $search_field 搜索字段
     * @return	array 办卡会员信息
     */
    public function get_info_by_ids($ids, $search_field = array())
    {   
        $info = array();
        
        if(is_array($ids) && !empty($ids)){
            $id_str = implode(",",$ids);
            $conf_where = "ID in($id_str)";
        }else{
            $conf_where = " id = '$ids' ";
        } 
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where($conf_where)->select();
        }
        else
        {
            $info = $this->where($conf_where)->select();
        }
        //echo $this->_sql();
        return $info;
    }

    /**
     * 根据项目ID获取项目所在城市简拼
     *
     * @access	public
     * @param  array $ids 搜索ID
     * @return	$str
     */
    public function get_pro_city_py($ids)
    {
        $info = array();

        if(is_array($ids) && !empty($ids)){
            $id_str = implode(",",$ids);
            $conf_where = "ID in($id_str)";
        }else{
            $conf_where = " id = $ids ";
        }

        //获取项目信息
        $project_citys = M("erp_project")
            ->field("city_id,id")
            ->where($conf_where)
            ->select();

        //获取城市信息
        $city_info  = $this->get_cityinfo('py');

        foreach($project_citys as $key=>$val){
            $info[$val['ID']]['py'] = $city_info[$val['CITY_ID']];
            $info[$val['ID']]['city_id'] = $val['CITY_ID'];
        }

        return $info;
    }


    /**
     * get_projectinfo_by_cond
     *
     * 根据条件获取楼盘信息
     *
     * @access  public
     * @param   int $uid  用户id
     * @param   int $city 用户所在城市
     * @param   string $start 偏移量
     * @param   string $limit 显示个数
     * @param   string $order_field 排序字段
     * @param   string $order 升序降序
     * @return  array  楼盘信息数组
     */
    public function get_projectinfo_by_uid( $uid , $city , $start = 0 , $limit = 50 , $order_field = 'id' , $order = 'asc' )
    {
        $time = time();
        $project_arr = array();

        //erp_prorole.erp_id = 1 电商
        //获取用户权限项目
        $project_arr = M("erp_project")
                        ->join("erp_prorole on erp_project.id = erp_prorole.pro_id")
                        ->join("erp_case on erp_prorole.erp_id = erp_case.scaletype and erp_prorole.pro_id = erp_case.project_id")
                        ->join("erp_house on erp_project.id = erp_house.project_id")
                        ->field("erp_project.id,erp_case.scaletype,erp_project.projectname,erp_house.rel_newhouseid")
                        ->where("erp_prorole.isvalid=-1 and erp_prorole.use_id=$uid and erp_project.city_id=$city and erp_house.rel_newhouseid>0 and erp_project.bstatus=2 and erp_prorole.erp_id = 1")
                        ->limit("$start,$limit")
                        ->select();

        $sql = "SELECT erp_project.id,erp_house.rel_newhouseid,erp_project.projectname,erp_case.scaletype from erp_project left join erp_case on erp_case.project_id = erp_project.id left join erp_house on erp_project.id = erp_house.project_id  where (erp_project.id = 54  or erp_project.id = 93) and erp_case.scaletype = 1";
        $project_arr = M("erp_project")->query($sql);

        return $project_arr;
    }

        
}

/* End of file MemberModel.class.php */
/* Location: ./Lib/Model/MemberModel.class.php */