<?php
class RTStatisticsAction extends ExtendAction{

    private $uid;
    private $city_id;

    //���캯��
    public function __construct()
    {
        parent::__construct();
        //���ػ�Աģ�鹫�ú����ļ�
        load("@.member_common");

        //����ID
        $this->city_id = intval($_SESSION['uinfo']['city']);
        //�û�ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //�û�����
        $this->uname = trim($_SESSION['uinfo']['uname']);
        //���м��
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);

        $this->_merge_url_param['TAB_NUMBER'] = intval($this->_tab_number) ;
    }

    /*
     *  ��ͨ�ĳ��к���Ŀ��ʹ�����
     */
    public function city_pro_detail(){

        $show_details = isset($_GET['show_details']) ? $_GET['show_details'] : 0;

        //���е���ϸ��Ŀ����
        if($show_details == 0)
        {
            $start_day = isset($_POST['search_btime'])?trim($_POST['search_btime']):'2016-02-01 00:00:00';
            $end_day = isset($_POST['search_etime'])?trim(date("Y-m-d H:i:s",strtotime($_POST['search_etime'])+86399)):date("Y-m-d H:i:s",time()+86399);

            //���б������
            $sql = "SELECT distinct(city_id) AS city  FROM erp_cardmember";
            $result = M()->query($sql);

            //��ȡ����
            $sql = "select name,py,id from erp_city  where isvalid = -1 order by id";
            $city_data = M()->query($sql);
            $city_arr = array();
            foreach($city_data as $key=>$val){
                $city_arr[$val['ID']] = $val['NAME'];
            }

            //��ȡ�û�����
            $sql = "SELECT COUNT(id) as num, city_id  FROM erp_cardmember WHERE "
                ."createtime >= to_date('".$start_day."','yyyy-mm-dd hh24:mi:ss') AND createtime <= to_date('".$end_day."','yyyy-mm-dd hh24:mi:ss') GROUP BY city_id ";
            $result_member = M()->query($sql);

            $member_arr = array();
            if(is_array($result_member) && !empty($result_member))
            {
                foreach ($result_member as $key => $value)
                {
                    $member_arr[$value['CITY_ID']] = $value['NUM'];
                }
            }

            //��ȡǩ������
            $sql = "SELECT project_id, count(id) AS arrival_num FROM erp_arrival_confirm_log  WHERE "
                . "createtime >=  to_date('".$start_day."','yyyy-mm-dd hh24:mi:ss') AND createtime <= to_date('".$end_day."','yyyy-mm-dd hh24:mi:ss')  GROUP BY project_id";
            $arrival_arr_temp = M()->query($sql);

            $arrival_arr = array();
            if(is_array($arrival_arr_temp) && !empty($arrival_arr_temp))
            {
                foreach ($arrival_arr_temp as $key => $value)
                {
                    $prj_id = intval($value['PROJECT_ID']);
                    $sql = "select city_id from erp_project where id = '".$prj_id."'";
                    $city = M()->query($sql);
                    $city = $city[0]['CITY_ID'];
                    $arrival_arr[$city] += !empty($value['ARRIVAL_NUM']) ? intval($value['ARRIVAL_NUM']) : 0;
                }
            }

            $tbl_str = "<table align='center' border = '1' cellspacing = 0 style = 'height:100px;margin-top:20px' class='jbtab tdbgwt'>";
            $tbl_str .= '<tr><td colspan="9" align="center">ͳ�����ڣ�'.$start_day.' �� '.$end_day.'</td></tr>';
            $tbl_str .= "<tr><th>����</th><th>����ȷ��ʹ������(�ֻ���)</th><th>����ȷ��ʹ������(ȫ��)</th>"
                ."<th>�쿨�û�����(ȫ��)</th><th>�쿨�û�ʹ������</th><th>�쿨�û�IOS���û���</th><th>�쿨�û���׿���û���</th>"
                ."<th>״̬���ʹ������</th><th>��Ȼ����ʹ������</th></tr>";
            if(is_array($result) && !empty($result))
            {
                foreach ($result as $key => $value)
                {
                    $stat_arr = array();
                    $city_id = intval($value['CITY']);

                    //ѭ�����У���ȡÿ��������ÿ�ֿͻ������ͼ�ʹ������
                    $sql = "SELECT operate_type, COUNT(*) AS num FROM erp_rt_operate_log WHERE "
                        . " city = '".$city_id."' AND operate_time >= to_date('".$start_day."','yyyy-mm-dd hh24:mi:ss') AND operate_time <= to_date('".$end_day."','yyyy-mm-dd hh24:mi:ss') GROUP BY operate_type";
                    $stat_arr = M()->query($sql);

                    $sql = "SELECT city, COUNT(*) AS num,from_device FROM erp_rt_operate_log WHERE "
                        ." city =". $city_id ." AND operate_time >= to_date('".$start_day."','yyyy-mm-dd hh24:mi:ss') AND operate_time <= to_date('".$end_day."','yyyy-mm-dd hh24:mi:ss') "
                        ." AND operate_type = 1 AND from_device IN (1,2) GROUP BY from_device,city";
                    $from_arr = M()->query($sql);

                    $sql = "SELECT COUNT(*) AS num FROM erp_rt_operate_log WHERE "
                        ." city =". $city_id ." AND operate_time >= to_date('".$start_day."','yyyy-mm-dd hh24:mi:ss') AND operate_time <= to_date('".$end_day."','yyyy-mm-dd hh24:mi:ss') "
                        ." AND operate_type = 3 AND from_device IN (1,2,3)";
                    $daochang_arr = M()->query($sql);

                    $ios = isset($from_arr[0]) ? $from_arr[0]['NUM']:0;
                    $android = isset($from_arr[1]) ? $from_arr[1]['NUM']:0;

                    $tbl_str .= '<tr align = "center">';
                    $tbl_str .= "<td><a href='javascript:void(0);' onclick=\"show_statics_details('".$value['CITY']."', '".$start_day."', '".$end_day."')\">".$city_arr[$value['CITY']]."</a></td>";

                    $temp_arr = array();
                    foreach($stat_arr as $stat_val)
                    {
                        $temp_arr[$stat_val['OPERATE_TYPE']] =  $stat_val['NUM'];
                    }

                    //����ȷ��(�ֻ�)
                    $arrival_num = isset($daochang_arr[0]['NUM']) ? intval($daochang_arr[0]['NUM']) : 0;
                    $tbl_str .= "<td>".$arrival_num."</td>";

                    //����ȷ��ʹ������(ȫ��)
                    $pc_arrival_num = !empty($arrival_arr[$city_id]) ? $arrival_arr[$city_id] : 0;
                    $tbl_str .= "<td>". $pc_arrival_num."</td>";

                    //�쿨�û�����(ȫ��)
                    $bk_num_all = isset($member_arr[$city_id]) ? intval($member_arr[$city_id]) : 0;
                    $tbl_str .= "<td>".$bk_num_all."</td>";

                    //�쿨�û�ʹ������
                    $bk_num = isset($temp_arr['1']) ? intval($temp_arr['1']) : 0;
                    $tbl_str .= "<td>".$bk_num."</td>";

                    //�쿨�û�IOS���û���
                    $tbl_str .= "<td>".$ios."</td>";

                    //�쿨�û���׿���û���
                    $tbl_str .= "<td>".$android."</td>";

                    //״̬���ʹ������
                    $sc_num = isset($temp_arr['2']) ? intval($temp_arr['2']) : 0;
                    $tbl_str .= "<td>".$sc_num."</td>";

                    //��Ȼ����ʹ������
                    $zrlk_num = isset($temp_arr['4']) ? intval($temp_arr['4']) : 0;
                    $tbl_str .= "<td>".$zrlk_num."</td>";
                    $tbl_str .= '</tr>';
                }
            }
            $tbl_str .= '</table>';
        }
        else
        {
            $city_id = isset($_GET['city_id']) ? $_GET['city_id'] : 0;

            if($city_id > 0)
            {
                $start_day_g = (isset($_GET['start_day']) && !empty($_GET['start_day'])) ? $_GET['start_day'] : '2015-07-31';
                $end_day_g = (isset($_GET['end_day']) && !empty($_GET['end_day'])) ? trim(date("Y-m-d H:i:s",strtotime($_GET['end_day'])+86399)): date('Y-m-d H:i:s',time()+86399);

                $statistic_arr = array();

                //����ȷ��ʹ������(�ֻ���)
                $sql = "SELECT prjid, COUNT(*) AS arrival_num_sj FROM erp_rt_operate_log WHERE "
                    . " city = '".$city_id."' AND operate_type = 3 AND from_device IN (1,2,3) AND "
                    ." operate_time >= to_date('".$start_day_g."','yyyy-mm-dd hh24:mi:ss') AND operate_time <= to_date('".$end_day_g."','yyyy-mm-dd hh24:mi:ss') GROUP BY prjid";

                $stat_arr = M()->query($sql);
                if(is_array($stat_arr) && !empty($stat_arr))
                {
                    foreach ($stat_arr as $key => $value)
                    {
                        $prj_id = intval($value['PRJID']);
                        $statistic_arr[$prj_id]['arrival_num_sj'] = intval($value['ARRIVAL_NUM_SJ']);
                    }
                }

                //����ȷ��ʹ������(ȫ��)
                $sql = "SELECT c.project_id, count(c.id) AS arrival_num FROM erp_arrival_confirm_log c "
                    ." INNER JOIN erp_project p ON c.project_id = p.id WHERE p.city_id = '".$city_id."' AND "
                    . " c.createtime >= to_date('".$start_day_g."','yyyy-mm-dd hh24:mi:ss') AND c.createtime <= to_date('".$end_day_g."','yyyy-mm-dd hh24:mi:ss') GROUP BY c.project_id";
                $arrival_arr_temp = M()->query($sql);

                if(is_array($arrival_arr_temp) && !empty($arrival_arr_temp))
                {
                    foreach ($arrival_arr_temp as $key => $value)
                    {
                        $prj_id = intval($value['PROJECT_ID']);
                        $statistic_arr[$prj_id]['arrival_num_all'] = intval($value['ARRIVAL_NUM']);
                    }
                }

                //ȫ���쿨��Ա
                $result_member = array();
                $sql = "SELECT COUNT(m.id) AS num, m.prj_id, p.projectname FROM erp_cardmember m "
                    ." LEFT JOIN erp_project p ON m.prj_id = p.id WHERE m.city_id = '".$city_id."' "
                    ." AND m.createtime >= to_date('". $start_day_g ."','yyyy-mm-dd hh24:mi:ss') AND m.createtime <= to_date('".$end_day_g."','yyyy-mm-dd hh24:mi:ss') GROUP BY m.prj_id,p.projectname";

                $result_member = M()->query($sql);

                if(is_array($result_member) && !empty($result_member))
                {
                    foreach ($result_member as $key => $value)
                    {
                        $statistic_arr[$value['PRJ_ID']]['bk_num'] = $value['NUM'];
                        $statistic_arr[$value['PRJ_ID']]['PROJECTNAME'] = $value['PROJECTNAME'];
                    }
                }

                //�쿨�û�ʹ������
                $result_bk = array();
                $sql = "SELECT prjid, COUNT(id) AS bk_num FROM erp_rt_operate_log WHERE city = '".$city_id."' "
                    ." AND operate_type = 1 AND operate_time >= to_date('".$start_day_g."','yyyy-mm-dd hh24:mi:ss') AND operate_time <= to_date('".$end_day_g."','yyyy-mm-dd hh24:mi:ss') GROUP BY prjid";
                $result_bk = M()->query($sql);

                if(is_array($result_bk) && !empty($result_bk))
                {
                    foreach ($result_bk as $key => $value)
                    {
                        $statistic_arr[$value['PRJID']]['bk_num_rt'] = $value['BK_NUM'];
                    }
                }

                $tbl_str = "<table align='center' border = '1' cellspacing = 0 width='95%' class='jbtab tdbgwt'>";
                $tbl_str .= "<tr><th>��Ŀ���</th><th>��Ŀ����</th>";
                $tbl_str .= "<th>����ȷ��ʹ������(�ֻ���)</th><th>����ȷ��ʹ������(ȫ��)</th>";
                $tbl_str .= "<th>�쿨�û�����(ȫ��)</th><th>�쿨�û�ʹ������(�ֻ�)</th></tr>";

                if(is_array($statistic_arr) && !empty($statistic_arr))
                {
                    foreach ($statistic_arr as $key => $value)
                    {
                        $tbl_str .= '<tr align = "center">';
                        //��Ŀ���
                        $tbl_str .= "<td>".$key."</td>";

                        //��Ŀ����
                        if(!empty($value['PROJECTNAME']))
                        {
                            $pro_name = $value['PROJECTNAME'];
                        }
                        else
                        {
                            if($key > 0)
                            {
                                $sql = "SELECT PROJECTNAME FROM erp_project WHERE id = '".$key."'";
                                $pro_name = M()->query($sql);
                                $pro_name = $pro_name[0]['PROJECTNAME'];
                            }
                            else
                            {
                                $pro_name = 'δ֪��Ŀ';
                            }
                        }

                        $tbl_str .= "<td>".$pro_name."</td>";
                        //����ȷ��(sj)
                        $arrival_num_sj = isset($value['arrival_num_sj']) ? $value['arrival_num_sj'] : 0;
                        $tbl_str .= "<td>".$arrival_num_sj."</td>";
                        //����ȷ��(ȫ��)
                        $arrival_num_all = isset($value['arrival_num_all']) ? $value['arrival_num_all'] : 0;
                        $tbl_str .= "<td>".$arrival_num_all."</td>";
                        //�쿨�û�ȫ��
                        $bk_num = isset($value['bk_num']) ? $value['bk_num'] : 0;
                        $tbl_str .= "<td>".$bk_num."</td>";
                        //�쿨�û�ʹ��(��ͨ)
                        $bk_rt_num = isset($value['bk_num_rt']) ? $value['bk_num_rt'] : 0;
                        $tbl_str .= "<td>".$bk_rt_num."</td>";
                        $tbl_str .= '</tr>';
                    }
                }
                $tbl_str .= '</table>';
            }
            else
            {
                $tbl_str = "<table align='center' border = '0' cellspacing = 0 width='90%'>";
                $tbl_str .= '<tr align = "center">';
                $tbl_str .= '<td style="color:red;">����������ƣ��鿴��Ŀ��ϸͳ������</td>';
                $tbl_str .= '</tr>';
                $tbl_str .= '</table>';
            }

        }

        //��Ⱦ
        $this->assign('start_day', $start_day);
        $this->assign('end_day', $end_day);

        //�Ƿ�չ�ֳ�����ϸ
        $this->assign('show_details', $show_details);

        $this->assign('tbl_str',$tbl_str);
        $this->display('city_pro_detail');
    }


    /*
     *  չ����ͨ����ϸ����
     */
    public function rt_detail(){

        //where ������ֵ
        $where = "WHERE 1=1";

        if($_REQUEST['act'] == 'search'){

            if($search_realname = trim($_REQUEST['search_realname'])){
                $where .= " AND A.realname LIKE '%".$search_realname."%'";
            }
            //��ѯʱ�䣨��ʼ��
            if($search_btime = $_REQUEST['search_btime']){
                $where .= " AND to_char(B.operate_time,'YYYY-MM-DD hh24:mi:ss') >= '".$search_btime."'";
            }

            //��ѯʱ�䣨������
            if($search_etime = $_REQUEST['search_etime']){
                $where .= " AND to_char(B.operate_time,'YYYY-MM-DD hh24:mi:ss') < '".$search_etime."'";
            }

            //������
            if($add_username = trim($_REQUEST['add_username'])){
                $where .= " AND A.add_username LIKE '%".$add_username."%'";
            }

            if($search_city = intval($_REQUEST['search_city'])){
                $where .= " AND B.city = $search_city";
            }

        }

        //�ֻ��豸���
        $device = array(
            '1'=>'ƻ���ֻ�',
            '2'=>'��׿�ֻ�',
            '3'=>'�����ֻ�',
            '4'=>'PC��',
        );

        //��ȡ����
        $sql = "select name,py,id from erp_city  where isvalid = -1 order by id";
        $city_data = M()->query($sql);

        #��ȡ��ͨ��������
        $sql = "select A.realname,B.from_device,A.cardtime,B.operate_time,A.add_username,C.projectname,B.prjid";
        $sql .=" from erp_cardmember A right join erp_rt_operate_log B on B.user_id = A.id  left join erp_project C on B.prjid = C.id";
        $sql .=" $where and  B.operate_type = 1 order by B.prjid";

        $rt_data = M()->query($sql);

        #��ȡ��Ŀ��Ϣ
        $sql = "select B.prjid,count(1) as prjcount from erp_cardmember A Right join erp_rt_operate_log B on B.user_id = A.id $where AND B.operate_type = 1 Group by B.prjid ";

        $pro_info = M()->query($sql);

        $pro_count = array();
        foreach($pro_info as $key=>$val){
            $pro_count[$val['PRJID']]['count'] = $val['PRJCOUNT'];
        }

        #��ȡȷ�ϵ���������
        $sql = "select B.prjid,count(1) as confirm from erp_cardmember A Right join erp_rt_operate_log B on B.user_id = A.id $where AND B.operate_type = 3 Group by B.prjid ";
        $pro_confirm = M()->query($sql);

        foreach($pro_confirm as $key=>$val){
            $pro_count[$val['PRJID']]['confirm'] = $val['CONFIRM'];
        }

        //��Ⱦ
        $this->assign('search_realname', $search_realname);
        $this->assign('search_btime', $search_btime);
        $this->assign('search_etime', $search_etime);
        $this->assign('add_username', $add_username);
        $this->assign('search_city', $search_city);

        $this->assign('pro_count', $pro_count);
        $this->assign('device', $device);
        $this->assign('rt_data',$rt_data);
        $this->assign('city_data',$city_data);
        $this->display('rt_detail');

    }

}
