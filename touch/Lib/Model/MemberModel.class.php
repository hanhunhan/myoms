<?php

/**
 * ����ҵ��쿨�ͻ�������
 *
 * @author liuhu
 */
class MemberModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CARDMEMBER';
    
    /***��Ա��Ʊ״̬***/
    private $_conf_invoice_status = array(
							    		'no_invoice' => '1',     //δ��
							    		'apply_invoice' => '5',  //��Ʊ������
							    		'invoiced' => '2',       //�ѿ�δ��
							    		'has_taken' => '3',      //����
							    		'callback' => '4'       //�ѻ���
							    	);


    /***�쿨״̬****/
    private $_conf_card_status = array(
                                    '1' => '�Ѱ�δ�ɽ�',
                                    '2' => '�Ѱ����Ϲ�',
                                    '3' => '�Ѱ���ǩԼ',
                                    );
    /**�վ�״̬****/
    private $_conf_receipt_status = array(
                                    '2' => "�ѿ�δ��",
                                    '3' => "����",
                                    '4' => "���ջ�",
                                    );


    /***֤������****/
    private $_conf_certificate_type = array(
    								'1' => '���֤',
						    		'2' => '���ڲ�',
						    		'3' => '����֤',
						    		'4' => 'ʿ��֤',
						    		'5' => '����֤',
						    		'6' => '����',
						    		'7' => '̨��֤',
						    		'8' => '����֤',
						    		'9' => '���֤���۰ģ�',
						    		'10' => 'Ӫҵִ��',
						    		'11' => '���˴���',
						    		'12' => '����',
    								);

    private $_conf_member_source_remark = array(
                                        '1' => '�н�',
                                        '2' => '����',
                                        '3' => '����Ӫ��',
                                        '4' => '�ؿ�',
                                        '5' => '����',
                                        '6' => '��Ȼ����'
                                    );
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ��ȡ��Ա��Ʊ״̬����
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
     * ��ȡ��Ա��Ʊ״̬��������
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
     * ��ȡ֤����������
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
     * ��ȡ��Ա��Դ��������
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
     * ��ȡ��Ա��Ʊ���쿨���վݡ�����ȷ��״̬����
     *
     * @access	public
     * @param	string $field_name ��Ʊ/�쿨/�վ�/��Ʊ/״̬�ֶ�����
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
     * ��Ӱ쿨��Ա��Ϣ
     * @param array $member_info ��Ա��Ϣ����
     * @return mixed ��ӳɹ����ز������ݱ�ţ�����ʧ�ܷ���FALSE
     */
    public function add_member_info($member_info) 
    {   
        if(is_array($member_info) && !empty($member_info))
        {   
            // �����������ز���ID
            $options['table'] = parent::getTableName();
            $insertId = $this->add($member_info, $options);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݱ��ɾ���쿨��Ա��Ϣ
     *
     * @access	protected
     * @return	int ɾ��������0ɾ��ʧ��
     */
    public function delete_info_by_id()
    {   
        
    }
    
    
    /**
     * ���ݶ���������ɾ���쿨��Ա��Ϣ
     *
     * @access	protected
     * @param	array  $arr_mids �쿨��Ա�������
     * @return	int ɾ��������0ɾ��ʧ��
     */
    public function delete_info_by_ids($arr_mids)
    {   
        
    }
	    
	
    /**
     * ���°쿨��Ա��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ����������ȡ�쿨�û���Ϣ
     *
     * @access  public
     * @param   int $city ���б��
     * @param   string $truename ��ʵ����
     * @param   string $telno �ֻ�����
     * @param   int $start ƫ����
     * @param   int $limit ��ʾ����
     * @return  array  ¥����Ϣ����
     */

    public function get_userlist_by_cond( $city, $truename = '', $telno = '', $start = 0, $limit = 2){
        //�����û��б�
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
     * ����������ȡ�쿨�û���Ϣ
     *
     * @access  public
     * @param   mixed $fid ��Ŀ��ţ������������
     * @return  array  ¥����Ϣ����
     */
    function get_project_arr_by_pid($fid)
    {
        //��������
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
     * ����ĳ���쿨��Ա��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * �����û�����ȡ�쿨�û���Ϣ
     *
     * @access  public
     * @param   int $uid �û�ID
     * @return  array  �ͻ���ϸ��Ϣ
     */
    function get_userinfo_by_uid($uid)
    {
        //��������
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
     * ��ȡ���е���Ϣ
     *
     * @access  public
     * @param   none
     * @return  array  ���е������Ϣ
     */
    function get_cityinfo($method='name')
    {
        //��������
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
     * ���������쿨��Ա��Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array �쿨��Ա��Ϣ
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
     * ���ݻ�Ա��Ż�ȡ�쿨��Ա��Ϣ����һ�û���
     *
     * @access	public
     * @param  int $id ����ID
     * @param array $search_field �����ֶ�
     * @return	array �쿨��Ա��Ϣ
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
     * ���ݻ�Ա��Ż�ȡ�쿨��Ա��Ϣ�����û���
     *
     * @access	public
     * @param  array $ids ����ID
     * @param array $search_field �����ֶ�
     * @return	array �쿨��Ա��Ϣ
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
     * ������ĿID��ȡ��Ŀ���ڳ��м�ƴ
     *
     * @access	public
     * @param  array $ids ����ID
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

        //��ȡ��Ŀ��Ϣ
        $project_citys = M("erp_project")
            ->field("city_id,id")
            ->where($conf_where)
            ->select();

        //��ȡ������Ϣ
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
     * ����������ȡ¥����Ϣ
     *
     * @access  public
     * @param   int $uid  �û�id
     * @param   int $city �û����ڳ���
     * @param   string $start ƫ����
     * @param   string $limit ��ʾ����
     * @param   string $order_field �����ֶ�
     * @param   string $order ������
     * @return  array  ¥����Ϣ����
     */
    public function get_projectinfo_by_uid( $uid , $city , $start = 0 , $limit = 50 , $order_field = 'id' , $order = 'asc' )
    {
        $time = time();
        $project_arr = array();

        //erp_prorole.erp_id = 1 ����
        //��ȡ�û�Ȩ����Ŀ
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