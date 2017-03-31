<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FlowtypeModel extends Model{
     
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'FLOWTYPE';
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     *  ��ͬ�Ĺ�������ͬ����ɫ
     * @return array
     */
    public function get_status_color(){
        $color = array(
            /**��Ŀģ��**/
            //��Ŀ�»����
            'xiangmuxiahuodong'=>'#00c0ef',
            //��������
            'dulihuodongbiangeng'=>'#00c0ef',
            //��Ŀ�»���
            'xiangmuxiahuodongbiangeng'=>'#00c0ef',
            //��׼����
            'biaozhuntiaozheng'=>'#00c0ef',
            //���ʱ�������
            'dianziedu'=>'#00c0ef',
            //��Ŀ����
            'xiangmujuesuan'=>'#00c0ef',
            //��Ŀ��ֹ
            'xiangmuzhongzhi'=>'#00c0ef',
            //���������
            'dulihuodong'=>'#00c0ef',
            //��������
            'lixiangshenqing'=>'#00c0ef',
            //������
            'lixiangbiangeng'=>'#00c0ef',
            //�ɱ�����
            'chengbenhuabo'=>'#00c0ef',
            //��Ŀ---����
            'xiangmujuesuan'=>'#00c0ef',

            /**��Աģ��***/
            //�˿����
            'tksq'=>'#dd4b39',
            //��Ա��Ʊ����
            'huiyuantuipiao'=>'#dd4b39',
            //��Ա����Ʊ����
            'huiyuanhuanpiao'=>'#dd4b39',
            //������������
            'jianmianshenqing'=>'#dd4b39',

            /**����ģ��**/
            //��ͬ��Ʊ
            'hetongkaipiao'=>'#f39c12',
            //�Ǹ��ֳɱ�����
            'feifuxianchengbenshenqing'=>'#f39c12',
            //ҵ�����
            'yewujintie'=>'#f39c12',
            //��������
            'baoxiaoshenqing'=>'#f39c12',
            //�������
            'jiekuanshenqing'=>'#f39c12',
            //С�۷䱨����������
            'xiaomifengchaoe'=>'#f39c12',
            //Ԥ����������������
            'yusuanqita'=>'#f39c12',
            //���ֽ�֧������
            'feixianjinzhifushenqing'=>'#f39c12',
            //���ʱ��������
            'dianzibilichaoe' =>'#f39c12',

            /**�ɹ�ģ��**/
            //�ɹ�����
            'caigoushenqing'=>'#3d9970',

            /**�û�ģ��**/
            'zhihuanshenqing'=>'#3d9970', //�û�����
            'neibulingyong'=>'#3d9970', //�û��ڲ�����
            'shoumai'=>'#3d9970', //�û�����
            'baosun'=>'#3d9970', //�û�����
            'shoumaibiangeng'=>'#3d9970', //�û�����

        );

        return $color;
    }

    /**
     * ������������ID��ѯ����������Ϣ
     *
     * @access	public
     * @param  mixed $ids �������ͱ��
     * @param array $search_field �����ֶ�
     * @return	array ����������Ϣ
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
     * ����������ȡ����������Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
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