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
    
    /***��Ա��Ʊ״̬***/
    private $_conf_change_invoice_status = array(
							    		'no_change_invoice' => '1',     //δ����
							    		'apply_change_invoice_success' => '2',  //����ɹ�
							    		'have_change' => '3',       //�ѻ�δ��
							    		'has_taken' => '4',      //�ѻ�����
                                        'apply_change_invoice'=>'5',//������
							    	);
    
     /***����ȷ��״̬***/
    private $_conf_confirm_status = array(
							    		'no_confirm' => '1',     //δȷ��
							    		'part_confirmed' => '2', //����ȷ��
							    		'confirmed' => '3',      //��ȷ��
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
    
    /**��Ա״̬****/
    private $_conf_status_remark = array(
                                    '0' => "��ɾ��",
                                    '1' => "��Ч"
                                    );
    
    
    /**��Ա״̬****/
    private $_conf_status = array(
                                    'deleted' => 0,
                                    'valid' => 1
                                    );
    
    private $_conf_zx_standard = array( '1' => 'ë��', 
                                        '2' => '��װ��'
                                    );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ��ȡ��Ա״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_status()
    {
    	return $this->_conf_status;
    }
    
   /*
    *��ȡ��Ա��Ʊ״̬����
    * @access public
    * @param none
    * @return array 
    */
    public function get_conf_change_invoice_status()
    {
        return $this->_conf_change_invoice_status;
    }


    /**
     * ��ȡ��Ա״̬״̬
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_status_remark()
    {
    	return $this->_conf_status_remark;
    }
    
    /**
     * ��ȡ����ȷ��״̬
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_confirm_status()
    {
    	return $this->_conf_confirm_status;
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
     * װ�ޱ�׼
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_zx_standard()
    {
        return $this->_conf_zx_standard;
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
        $member_source_remark = array();
        
        $salemethod_arr = 
                    M('erp_salemethod')->where('ISVALID = -1')->field('ID,NAME')->order('VERSON DESC,ORDERID ASC')->select();

        if(is_array($salemethod_arr) && !empty($salemethod_arr))
        {
            foreach($salemethod_arr as $key => $value)
            {
                $member_source_remark[$value['ID']] = $value['NAME'];
            }
        }
        
        return $member_source_remark;
    }

    /**
     * ��ȡ��Ŀ������
     * @param $prj_id
     * @return array
     */
    public function getPrjSaleMethod($prj_id){
        //���û�Ա��Դ(�޸�ʱ��Ҫ��ǰ��Ա��Ŀ��Ϣ��ע�����λ��)
        $source_arr = $this->get_conf_member_source_remark();

        $project_model = D('Project');
        $project_sale_arr = $project_model->get_project_budget_sale_by_prjid($prj_id);

        $temp_arr = array();
        if(is_array($project_sale_arr) && !empty($project_sale_arr))
        {
            foreach($project_sale_arr as $key => $value)
            {
                if(key_exists($value['SALEMETHODID'], $source_arr))
                {
                    $temp_arr[$value['SALEMETHODID']] = $source_arr[$value['SALEMETHODID']];
                }
            }

            $source_arr = $temp_arr;
        }
        else
        {
            $source_arr = $temp_arr;
        }
        return $source_arr;
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
    	 //echo M()->_sql();die;
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
     * @param	int $mid �쿨��Ա���
     * @return	mixed ɾ��������FALSEɾ��ʧ��
     */
    public function delete_info_by_id($mid)
    {   
        $mid = intval($mid);
        
        if($mid > 0)
        {
            $update_arr['STATUS'] = $this->_conf_status['deleted'];
            $update_num = self::update_info_by_id($mid, $update_arr);
        }
        
        return $update_num > 0 ? $update_num : FALSE;
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
        //echo $this->getLastSql();
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

    public function get_userlist_by_cond( $city, $truename = '', $telno = '', $start = 0, $limit = 5){
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
        $cond_where .= "  and erp_cardmember.status = 1  ";

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
            ->field("erp_project.id,erp_project.projectname,erp_house.pro_listid,erp_project.contract")
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
        
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",",$ids);
            $conf_where = "ID IN ($id_str)";
        }
        else
        {   
            $id = intval($ids);
            $conf_where = "ID = '".$id."'";
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
//        echo $this->_sql();
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

//        ��ȷ����Ŀ��
//        1.ӵ�е���ҵ�����Ŀ�����״̬Ϊ������
//        2.�����·�ID
//        3.����ӵ�������Ŀ�ĵ���ҵ��Ȩ��

        //��ȡ�û�Ȩ����Ŀ
        $project_arr = M("erp_project")
                        ->join("erp_prorole on erp_project.id = erp_prorole.pro_id")
                        ->join("erp_case on erp_prorole.erp_id = erp_case.scaletype and erp_prorole.pro_id = erp_case.project_id")
                        ->join("erp_house on erp_project.id = erp_house.project_id")
                        ->field("erp_project.id,erp_case.scaletype,erp_project.projectname,erp_house.pro_listid")
                        ->where("erp_prorole.isvalid=-1 and erp_prorole.use_id=$uid and erp_project.city_id=$city and erp_house.pro_listid>0 and (erp_project.bstatus=2 or erp_project.bstatus=4) and erp_prorole.erp_id = 1 and erp_project.status !=2")
                        ->limit("$start,$limit")
                        ->select();

        return $project_arr;
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
    public function get_projectfxinfo_by_uid( $uid , $city , $start = 0 , $limit = 50 , $order_field = 'id' , $order = 'asc' )
    {
        $time = time();
        $project_arr = array();

//        ��ȷ����Ŀ��
//        1.ӵ�з���ҵ�����Ŀ�����״̬Ϊ������
//        2.�����·�ID
//        3.����ӵ�������Ŀ�ķ���ҵ��Ȩ��

        //��ȡ�û�Ȩ����Ŀ
        $project_arr = M("erp_project")
            ->join("erp_prorole on erp_project.id = erp_prorole.pro_id")
            ->join("erp_case on erp_prorole.erp_id = erp_case.scaletype and erp_prorole.pro_id = erp_case.project_id")
            ->join("erp_house on erp_project.id = erp_house.project_id")
            ->field("erp_project.id,erp_case.scaletype,erp_project.projectname,erp_house.pro_listid")
            ->where("erp_prorole.isvalid=-1 and erp_prorole.use_id=$uid and erp_project.city_id=$city and erp_house.pro_listid>0 and (erp_project.mstatus=2 or erp_project.mstatus=4) and erp_prorole.erp_id = 2 and erp_project.status !=2")
            ->limit("$start,$limit")
            ->select();

        return $project_arr;
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
    public function get_arrivalprojectinfo_by_uid( $uid , $city , $start = 0 , $limit = 50 , $order_field = 'id' , $order = 'asc' )
    {
        $time = time();
        $project_arr = array();

//        ��ȷ����Ŀ��
//        1.ӵ�е���,����ҵ�����Ŀ�����״̬Ϊ������
//        2.�����·�ID
//        3.����ӵ�������Ŀ�ĵ��̣�����ҵ��Ȩ��

        //��ȡ�û�Ȩ����Ŀ
        $project_arr = M("erp_project")
            ->join("erp_prorole on erp_project.id = erp_prorole.pro_id")
            ->join("erp_case on erp_prorole.erp_id = erp_case.scaletype and erp_prorole.pro_id = erp_case.project_id")
            ->join("erp_house on erp_project.id = erp_house.project_id")
            ->field("erp_project.id,erp_case.scaletype,erp_project.projectname,erp_house.pro_listid")
            ->where("erp_prorole.isvalid=-1 and erp_prorole.use_id=$uid and erp_project.city_id=$city and erp_house.pro_listid>0 and erp_project.status !=2
                and (erp_project.bstatus=2 or erp_project.bstatus=4 or erp_project.mstatus=2 or erp_project.mstatus=4) and (erp_prorole.erp_id = 1 or erp_prorole.erp_id = 2) ")
            ->limit("$start,$limit")
            ->select();
        foreach($project_arr as $key =>$project){
            if($project_arr[$key]['ID'] == $project_arr[$key+1]['ID'] ){
                unset($project_arr[$key]);
            }
        }
        return $project_arr;
    }

    /**
     * @param $merchant_str  �̻����
     * @param $cityid  ����
     * @return bool     �����Ƿ��Ǵ���̻����
     */
    public function isLargeMerchant($merchant_str,$cityid){

        $merchant_info = M('erp_merchant')->where("CITY_ID = '".$cityid."'")->select();
        if(is_array($merchant_info) && !empty($merchant_info))
        {
            foreach($merchant_info as $key => $value)
            {
                if($value['IS_LARGE'] == 1 && $value['MERCHANT_NUMBER'] == $merchant_str) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *  ��ȡ�����ļ�
     * @param $type   REPORT_CUSTOM  �Զ���  MEMBER_ADD ��Ա���
     * @param $userid
     * @return string
     */
    public function get_user_config($type,$userid){
        $return = '';
        $cfg = M('Erp_user_cfg')->where("TYPE = '$type' AND ADDUID ='" . $userid . "'")->find();

        if($cfg)
            $return  = trim($cfg['CONFIG']);
 
        return $return;
    }

    /**
     * @param $type  REPORT_CUSTOM  �Զ���  MEMBER_ADD ��Ա���
     * @param $config  ��������
     * @param $userid   �û���
     */
    public function put_user_config($type,$config,$userid){

        $return = false;

        $cfg = M('Erp_user_cfg')->where("TYPE = '$type' AND ADDUID ='" . $userid . "'")->find();

        //���û�л�ȡ����ӣ��������
        if (!$cfg) {

            $info['TYPE'] = strtoupper($type);
            $info['CONFIG'] = $config;
            $info['DATELINE'] = date('Y-m-d H:i:s', time());
            $info['ADDUID'] = $userid;
            $return = M('Erp_user_cfg')->add($info);

        } else {

            $info['CONFIG'] = $config;
            $info['DATELINE'] = date('Y-m-d H:i:s', time());

            $return = M('Erp_user_cfg')
                ->where("ID='" . intval($cfg['ID']) . "'")->save($info);
        }

        return $return;
    }
	/**
     * ���Ļ�Ա״̬
     *
	 * @access	public
     * @param	int  $mid ��Ա���
     * @param 	string $conf_status ����״̬�ַ�����wait_confirm��
     * @return	int  ״ֵ̬
     */
    public function set_member_status($mid, $status = 1)
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "ID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "ID = '".$mid."'";
        }
        
        if($cond_where == '')
        {
            return $info;
        }
        $update_arr['REWARD_STATUS'] = $status;
        $up_num = $this->where($cond_where)->save($update_arr);
        
        return $up_num > 0  ? $up_num : FALSE;
    }
	/**
     * ͬ��CRM
     *
	 * @access	public
     * @param	int  $mid ��Ա���
     * @param 	string $conf_status ����״̬�ַ�����wait_confirm��
     * @return	int  ״ֵ̬
     */
    public function submit_member_crm($mid, $city )
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "ID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "ID = '".$mid."'";
        }
        
        if($cond_where == '')
        {
            return $info;
        }
        
        $res = $this->where($cond_where)->select(); 
		foreach($res as $member_info){  
			 if($member_info['REWARD_STATUS'] == 5)
			{   
				$crm_api_arr = array();
				$crm_api_arr['username'] = urlencode($member_info['REALNAME']);
				$crm_api_arr['mobile'] = strip_tags($member_info['MOBILENO']);
				$crm_api_arr['activefrom'] = 104;
				$crm_api_arr['city'] = $city;
				$crm_api_arr['activename'] =  urlencode($member_info['PRJ_NAME'].
						'�˿�'. oracle_date_format($member_info['CARDTIME'], 'Y-m-d').$conf_zx_standard[$member_info['DECORATION_STANDARD']]);
				$crm_api_arr['importfrom'] = urlencode('��������غ�̨');
				$crm_api_arr['tlfcard_status'] = 3;
				$crm_api_arr['tlfcard_creattime'] = strtotime(oracle_date_format($member_info['CARDTIME'], 'Y-m-d'));
				$crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
				$crm_api_arr['tlfcard_signtime'] = strtotime(oracle_date_format($member_info['SIGNTIME'], 'Y-m-d'));
				$crm_api_arr['tlfcard_backtime'] = time();
				$crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
				$crm_api_arr['projectid'] = $member_info['PRJ_ID'];

				if($member_info['CARDSTATUS'] == 3)
				{
					$house_info = M('erp_house')->field('PRO_LISTID')->
							where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();

					$pro_listid = !empty($house_info['PRO_LISTID']) ?
							intval($house_info['PRO_LISTID']) : '';

					$crm_api_arr['floor_id'] = $pro_listid;
				}

				$vvv = submit_crm_data_by_api($crm_api_arr);   
			}
		}

        
        return $res  ? $res : FALSE;
    }


 
/**
     * ����Ա״̬
     *
	 * @access	public
     * @param	int  $mid ��Ա���
     * @param 	string $conf_status ����״̬�ַ�����wait_confirm��
     * @return	int  ״ֵ̬
     */
    public function check_member_status($mid ,$type=1)
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "ID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "ID = '".$mid."'";
        }
        $cond_where .= $type==1 ? " AND REWARD_STATUS>1 " : " AND REWARD_STATUS>1 " ;
        if($cond_where == '')
        {
            return $info;
        }
        
        $res = $this->where($cond_where)->select();
        
        return $res ? $res : FALSE;
    }
	public function check_member_status3($mid ,$type=1)
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "ID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "ID = '".$mid."'";
        }
        $cond_where .= $type==1 ? " AND REWARD_STATUS<>4 " : " AND REWARD_STATUS<>4  " ;
        if($cond_where == '')
        {
            return $info;
        }
        
        $res = $this->where($cond_where)->select();
        
        return $res ? $res : FALSE;
    }
	public function check_member_status2($mid  )
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "ID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "ID = '".$mid."'";
        }
        $cond_where .= " AND REWARD_STATUS in(2,3) " ;
        if($cond_where == '')
        {
            return $info;
        }
        
        $res = $this->where($cond_where)->select();
        
        return $res ? $res : FALSE;
    }

	/**
     * ����ԱӶ�����
     *
	 * @access	public
     * @param	int  $mid ��Ա���
     * @param 	string $conf_status ����״̬�ַ�����wait_confirm��
     * @return	int  ״ֵ̬
     */
    public function check_member_yong($mid  )
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "ID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "ID = '".$mid."'";
        }
        $cond_where .= " AND TOTAL_PRICE_AFTER is null ";
        if($cond_where == '')
        {
            return $info;
        }
        
        $res = $this->where($cond_where)->select();
        
        return $res ? $res : FALSE;
    }
	/**
     * ����ԱӶ����� �Ƿ���ǰӶ
     *
	 * @access	public
     * @param	int  $mid ��Ա���
     * @param 	string $conf_status ����״̬�ַ�����wait_confirm��
     * @return	int  ״ֵ̬
     */
    public function check_member_front_yong($mid  )
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "ID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "ID = '".$mid."'";
        }
        $cond_where .= " AND TOTAL_PRICE =0 AND IS_DIS=2";
        if($cond_where == '')
        {
            return $info;
        }
        //echo $cond_where;
        $res = $this->where($cond_where)->select();
        
        return $res ? $res : FALSE;
    }


    /**
     * @param $fromCase
     * @param $receiptNo
     * @return array|mixed
     * @throws Exception
     */
    private function getMemberInfo($fromCase,$memberId){
        $memberInfo = array();

        try {
            $sql = "SELECT REALNAME,ID,INVOICE_STATUS,INVOICE_NO,CARDSTATUS,AGENCY_REWARD_STATUS,AGENCY_DEAL_REWARD_STATUS,PROPERTY_DEAL_REWARD_STATUS,OUT_REWARD_STATUS,IS_DIS,TOTAL_PRICE,TOTAL_PRICE_AFTER FROM ERP_CARDMEMBER M WHERE M.ID = '$memberId' AND STATUS = 1 AND CASE_ID = $fromCase";
            $memberInfo = D('Erp_project')->query($sql);
        } catch (Exception $e) {
            throw $e;
        }

        return $memberInfo;
    }


    /**
     * @param $toCase
     * @return array|mixed
     * @throws Exception
     */
    private function projectMemberInfo($toCase){
        $projectInfo = array();

        try {
            $sql = "SELECT PROJECTNAME,P.ID AS ID,C.SCALETYPE FROM ERP_PROJECT P INNER JOIN ERP_CASE C ON P.ID = C.PROJECT_ID AND C.SCALETYPE IN (1,2) AND C.ID = " . $toCase;
            $projectInfo = D('Erp_project')->query($sql);
        } catch (Exception $e) {
            throw $e;
        }

        return $projectInfo;
    }


    /**
     * ��ȡ�ú�Ӷ��Ա�Ƿ��ѿ�Ʊ
     * @param $memberId
     * @return bool
     * @throws Exception
     */
    private function isInvoiced($memberId){

        $return = false;

        try {
            $sql = 'select A.invoice_status from erp_post_commission A inner join erp_commission_invoice_detail B on A.id = B.post_commission_id where A.card_member_id = ' . $memberId;
            $invoiceStatus = D('Erp_post_commission')->query($sql);

            if($invoiceStatus && is_array($invoiceStatus)){
                $return = true;
            }
        } catch (Exception $e){
            throw $e;
        }

        return $return;
    }

    /**
     * ��ȡ��Ӷ�н�Ӷ���Ƿ��Ѿ�����
     * @param $memberId
     * @return bool
     * @throws Exception
     */
    private function isReimed($memberId){

        $return = false;

        try {
            $sql = 'select A.post_commission_status from erp_post_commission A inner join erp_commission_reim_detail B on A.id = B.post_commission_id where A.card_member_id = ' . $memberId;
            $postCommissionStatus = D('Erp_post_commission')->query($sql);

            if($postCommissionStatus && is_array($postCommissionStatus)){
                $return = true;
            }
        } catch (Exception $e){
            throw $e;
        }

        return $return;
    }

    /**
     * ת����Ŀ
     * @param $fromCaseId
     * @param $toCaseId
     * @param $memberId
     * @return array
     * @throws Exception
     */
    public function convertMember($fromCaseId,$toCaseId,$memberId){
        //���ض���
        $return = array(
            'status'=>false,
            'msg'=>'',
        );

        try {
            //��ȡ�û���Ϣ
            $memberInfo = $this->getMemberInfo($fromCaseId,$memberId);

            //��ȡ��Ŀ��Ϣ
            $projectInfo = $this->projectMemberInfo($toCaseId);


            if(!$memberInfo || !$projectInfo){
                $return['msg'] = '��Ŀ��Ϣ���û���Ϣ����';
                return $return;
            }

            //��Ŀ����
            $projectName = $projectInfo[0]['PROJECTNAME'];
            //Ŀ����ĿID
            $prjId = $projectInfo[0]['ID'];
            //��ԱID
            $memberId = $memberInfo[0]['ID'];
            //��Ա����
            $realName = $memberInfo[0]['REALNAME'];
            //��Ա״̬
            $memberInvoiceStatus = $memberInfo[0]['INVOICE_STATUS'];
            //��Ա��Ʊ���
            $invoice_no = $memberInfo[0]['INVOICE_NO'];
            //�쿨״̬
            $cardStatus = $memberInfo[0]['CARDSTATUS'];

            //�Ƿ��Ƿ�����Ŀ
            $isDis = intval($memberInfo[0]['IS_DIS']);

            //ǰӶ�����շѱ�׼
            $totalPriceBefore = $memberInfo[0]['TOTAL_PRICE'];

            //��Ӷ�����շѱ�׼
            $totalPriceAfter = $memberInfo[0]['TOTAL_PRICE_AFTER'];

            //�ж���Ϣ ����Ƿ�����Ӷ�Ƿ��Ѿ���Ʊ
            if($totalPriceAfter > 0 && $this->isInvoiced($memberId)){
                $return['msg'] = $memberId . $realName . '��Ӷ�Ѿ���Ʊ��';
                return $return;
            }

            //�ж���Ϣ �Ƿ��Ѿ��������Ѿ���������ת��
            if($memberInfo[0]['AGENCY_REWARD_STATUS'] > 1 || $memberInfo[0]['AGENCY_DEAL_REWARD_STATUS'] > 1 || $memberInfo[0]['PROPERTY_DEAL_REWARD_STATUS'] > 1 || $memberInfo[0]['OUT_REWARD_STATUS'] > 1){
                $return['msg'] = $memberId . '<' . $realName . '>' . '�з����Ѿ�������߱���������ת�ƣ�';
                return $return;
            }

            if($totalPriceAfter > 0 && $this->isReimed($memberId)){
                $return['msg'] = $memberId . '<' . $realName . '>' . '��Ӷ�з�������������ѱ���������ת�ƣ�';
                return $return;
            }

            if($memberInfo){

                /** ���»�Ա��ʼ */

                /***����erp_cardmember CASEID  projectName  prj_id **/
                $update_member = array();
                $update_member['PRJ_ID'] = $prjId;
                $update_member['CASE_ID'] = $toCaseId;
                $update_member['PRJ_NAME'] = $projectName;
                $update_member_ret = M('Erp_cardmember')->where('ID='.$memberId)->save($update_member);

                /** ���»�Ա���� */

                /** ���¿�Ʊ���뿪ʼ */
                if($isDis == 1 || ($isDis == 2 && $totalPriceBefore)){

                    //����erp_income_list ����
                    $update_income_list = array();
                    $update_income_list['CASE_ID'] = $toCaseId;
                    $update_income_list['PROJECT_ID'] = $prjId;
                    $update_income_list_ret = M('Erp_income_list')->where('CASE_TYPE IN (1,2) AND INCOME_FROM IN(1,2,3,4,5,20) AND ENTITY_ID = '.$memberId)->save($update_income_list);

                    //�Ѿ���Ʊ + ��Ʊ + �˿���Ϊ
                    if($memberInvoiceStatus == 2 || $memberInvoiceStatus == 3 || $memberInvoiceStatus == 4 || $cardStatus == 4) {
                        //�����Ʊ�ˣ�����erp_billing_record
                        $update_billing_arr = array();
                        $update_billing_arr['CASE_ID'] = $toCaseId;
                        $update_billing_ret = M('Erp_billing_record')->where("INVOICE_NO = '{$invoice_no}' AND CONTRACT_ID = {$memberId}")->save($update_billing_arr);


                        //�����Ʊ�ˣ�����erp_cost_list
                        $update_cost_arr = array();
                        $update_cost_arr['CASE_ID'] = $toCaseId;
                        $update_cost_arr['PROJECT_ID'] = $prjId;
                        $update_cost_ret = M('Erp_cost_list')->where("CASE_ID = {$fromCaseId} AND ENTITY_ID = $memberId AND EXPEND_FROM = 28")->save($update_cost_arr);

                        /**�����Ʊ�ˣ�֪ͨ��ͬϵͳ�޸�
                         * ��ʱ������
                         * ֱ�Ӻ�ͬϵͳ�޸� **/
                    }
                }
                /** ���¿�Ʊ������� */


                /** �������ݴ��� */

//                $sql = 'select id,list_id from Erp_reimbursement_detail where business_id = ' . $memberId . ' and case_id = ' . $fromCaseId . ' and type in(3,4,5,6,21)';
//                $queryRet = D()->query($sql);
//
//                if(!empty($queryRet)){
//                    foreach($queryRet as $key=>$val){
//
//                        //���±�����
//                        $sql = 'update Erp_reimbursement_detail set case_id = ' . $toCaseId . ' where case_id = ' . $fromCaseId . ' and id = ' . $val['ID'];
//                        $updateRetReim = D()->query($sql);
//
//                        if($updateRetReim===false)
//                            break;
//
//                        //���³ɱ���
//                        $entityId = $val['LIST_ID'];
//                        $expandId = $val['ID'];
//
//                        $update_cost_arr = array();
//                        $update_cost_arr['CASE_ID'] = $toCaseId;
//                        $update_cost_arr['PROJECT_ID'] = $prjId;
//                        $updateCostReimRet = M('Erp_cost_list')->where("CASE_ID = {$fromCaseId} AND ENTITY_ID = $entityId AND EXPAND_ID = $expandId AND EXPEND_FROM IN(7,10,13,16)")->save($update_cost_arr);
//
//                        if($updateCostReimRet===false)
//                            break;
//                    }
//                }

                /** �������ݴ��� */

            }

            //���ؽ��
            if($update_member_ret && ($update_income_list_ret!==false) && ($update_billing_ret!==false) && ($update_cost_ret!==false)) {
                $return['status'] = true;
                $return['msg'] = 'ת�Ƴɹ�';
            }
            else{
                $return['msg'] = 'ת��ʧ��';
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $return;
    }
}

/* End of file MemberModel.class.php */
/* Location: ./Lib/Model/MemberModel.class.php */