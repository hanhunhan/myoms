<?php
class BrokerAction extends ExtendAction
{

    public function index()
    {
        import("ORG.Util.Page");
        $count = M('keeper_jjr_customproject')->where("kjcp_city='$this->city'")->count();
        $p = new Page($count, C('PAGESIZE'));
        $page = $p->show();

        $listinfo = M('keeper_jjr_customproject')->join(" keeper_jjr_custominfo a on a.kjci_id = keeper_jjr_customproject.kjcp_kjci_id")->order('a.kjci_id desc')->where("kjcp_city='$this->city'")->limit($p->firstRow . ',' . $p->listRows)->select();

        foreach ($listinfo as $key => $value)
        {
            $prj_id[] = $value['brp_prj_id'];
        }

        if(!empty($prj_id))
        {
            $prj_str = implode(',', array_filter($prj_id));
            $res = M('project')->where("prj_id in ($prj_str)")->select();
        }

        $prj = array();
        foreach ($res as $value)
        {
            $prj[$value['prj_id']] = $value;
        }

        foreach ($listinfo as $key => $val)
        {
            $id = $val['brp_prj_id'];
            $listinfo[$key] = array_merge($prj[$id], $val);
            $listinfo[$key]['status'] = $this->get_status(
                $val['kjcp_admin_status'],
                $val['kjcp_kfs_status'],
                $val['kjcp_jjr_status'],
                $val['kjcp_customer_status']
            );
            $listinfo[$key]['status_num'] = $this->get_status_num(
                $val['kjcp_admin_status'],
                $val['kjcp_kfs_status'],
                $val['kjcp_jjr_status'],
                $val['kjcp_customer_status']
            );
            $listinfo[$key]['create_time'] = date('Y-m-d H:i', $listinfo[$key]['create_time']);
            $userids[] = $val['kjcp_kju_id'];
        }

        $data = array();
        if(!empty($userids))
        {
            $userids = implode(',', array_filter($userids));
            $res = M('uc_user')->where("userid in ($userids)")->select();
            foreach ($res as $user)
            {
                $data[$user['userid']] = $user;
            }
        }
        foreach ($listinfo as $key => $val)
        {
            $listinfo[$key]['user_phone'] = $data[$val['kjcp_kju_id']]['mobile'];
        }


        $this->assign('page', $page);
        $this->assign('listinfo', $listinfo);
        $this->display('customer_list');
    }

    public function project()
    {
        import("ORG.Util.Page");
        $count = M('broker_project')->where("city='$this->city'")->count();
        $p = new Page($count, C('PAGESIZE'));
        $page = $p->show();

        $listinfo = M('broker_project')->join(" project pro on broker_project.prj_id = pro.prj_id")->order('sort desc')->limit($p->firstRow . ',' . $p->listRows)->where("prj_city='$this->city'")->select();

        $this->assign('page', $page);
        $this->assign('re', $listinfo);
        $this->display('project_list');
    }

    public function jieyong()
    {
        $id = intval($this->_get('id'));

        $data = array(
            'kjcp_admin_status' => 2 ,
            'kjcp_kfs_status' => 2 ,
            'kjcp_jjr_status' => 4
        );

        $affected = M('keeper_jjr_customproject')->where("kjcp_id='".$id."' AND kjcp_city='$this->city'")->save($data);
        if($affected){
            $this->success('�޸ĳɹ���',U("Broker/index"));
        }else{
            $this->error('�޸�ʧ�ܣ�',U("Broker/index"));
        }
        die();
    }


    public function ban()
    {
        $id = intval($this->_get('id'));

        $data = array(
            'kjcp_admin_status' => 3 ,
            'kjcp_kfs_status' => 2 ,
            'kjcp_jjr_status' => 5
        );

        $affected = M('keeper_jjr_customproject')->where("kjcp_id='".$id."' AND kjcp_city='$this->city'")->save($data);
        if($affected){
            $this->success('�޸ĳɹ���',U("Broker/index"));
        }else{
            $this->error('�޸�ʧ�ܣ�',U("Broker/index"));
        }
        die();
    }

    public function edit()
    {
        $item = $_POST['item'];
        $id = intval($this->_get('id'));
        if($id)
        {
            $info = M('broker_project')->where("prj_id='$id' AND city='$this->city'")->find();
        }

        if($item)
        {
            if($item['edit']> 0)
            {
                $prj_id = intval($item['edit']);
                unset($item['edit'], $item['prj_id']);
                $affected = M('broker_project')->where("prj_id='$prj_id' AND city='$this->city'")->save($item);
            }
            else
            {
                $item['city'] = $this->city;
                $affected = M('broker_project')->add($item);
            }
            if($affected){
                $this->success('�޸ĳɹ���',U("Broker/project"));
            }else{
                $this->error('�޸�ʧ�ܣ�',U("Broker/project"));
            }
            die();
        }
        $prj_list = $this->get_prj_list();
        $this->assign('id',$id);
        $this->assign('re',$info);
        $this->assign('prj_list', $prj_list);
        $this->display('project');
    }

    function get_prj_list()
    {
        $where=" prj_city='".$this->city."' AND recommend=1";
        $prj_list = M('project')->where($where)->order("prj_sort desc")->select();
        return $prj_list;
    }

    function delete()
    {
        $id = intval($this->_get('id'));
        $affected = M('broker_project')->where("prj_id='$id' AND city='$this->city'")->delete();
        if($affected){
            $this->success('ɾ���ɹ���',U("Broker/project"));
        }else{
            $this->error('ɾ��ʧ�ܣ�',U("Broker/project"));
        }

    }


    /**
     * ��ȡ��ǰ�Ƽ�������״̬
     */
    public function get_status($admin_status, $kfs_status, $jjr_status, $customer_status)
    {
        if($admin_status == 3 && $kfs_status == 2 && $jjr_status == 5)
        {
            return '�Ѿ���';
        }
        if($admin_status == 3)
        {
            return '365���δͨ��';
        }
        if($admin_status == 2 && $kfs_status == 1)
        {
            return '365����� ������δ���';
        }
        if($admin_status == 2 && $kfs_status == 3)
        {
            return '365����� �����̿����̾���';
        }
        if($admin_status == 2 && $kfs_status == 2 && $jjr_status == 2 && $customer_status == 1)
        {
            return '�Ϲ���';
        }
        if($admin_status == 2 && $kfs_status == 2 && $jjr_status == 4)
        {
            return '�ѽ�Ӷ';
        }
    }

    /**
     * ��ȡ��ǰ�Ƽ�������״̬
     */
    public function get_status_num($admin_status, $kfs_status, $jjr_status, $customer_status)
    {
        if($admin_status == 3 && $kfs_status == 2 && $jjr_status == 5)
        {
            return 5; //'����';
        }
        if($admin_status == 3)
        {
            return 0;  //'365���δͨ��';
        }
        if($admin_status == 2 && $kfs_status == 1)
        {
            return 1;  //'365����� ������δ���';
        }
        if($admin_status == 2 && $kfs_status == 3)
        {
            return 2;//'365����� �����̿����̾���';
        }
        if($admin_status == 2 && $kfs_status == 2 && $jjr_status == 2 && $customer_status == 1)
        {
            return 3; //'�Ϲ�';
        }
        if($admin_status == 2 && $kfs_status == 2 && $jjr_status == 4)
        {
            return 4; //'��Ӷ';
        }
    }

}