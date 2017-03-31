<?php

/**
 * Class Uc_userAction
 */
class Uc_userAction extends ExtendAction
{
    public function add()
    {
        $act = $this->_post('act');
        if ($act == 'add') {
            $mobile = $this->_post('mobile');
            $nickname = $this->_post('username');
            $password = $this->_post('password');

            include '../functions/global.func.php';

            $salt = _generate_key(6);
            $mixed = md5(md5($password) . $salt);

            $data = array(
                'mobile' => $mobile,
                'password' => $mixed,
                'salt' => $salt,
                'nickname' => $nickname,
                'status' => 0,
                'regip' => get_client_ip(),
                'regdate' => time(),
            );
            $res = M('uc_user')->add($data);
            if (!$res) {
                $this->error('����û�ʧ��', U('Uc_user/add'));
                exit();
            } else {
                $this->app_register($mobile, $password);
                $this->success('����û��ɹ���', U('Uc_user/add'));
                exit();
            }
        }

        $this->display('add');
    }

    /**
     *
     */
    public function listinfo()
    {
        import("ORG.Util.Page");
        $count = M('uc_user')->count();
        $p = new Page($count, C('PAGESIZE'));
        $page = $p->show();
        $listinfo = M('uc_user')->order('userid desc')->limit($p->firstRow . ',' . $p->listRows)->select();
        foreach ($listinfo as $key => $user) {
            $listinfo[$key]['regdate'] = date('Y-m-d H:i:s', $user['regdate']);
            $listinfo[$key]['lastlogintime'] = date('Y-m-d H:i:s', $user['lastlogintime']);
            $listinfo[$key]['status'] = $user['status'] == 0 ? '����' : '<font color="darkgrey">��ֹ</font>';
            $listinfo[$key]['font'] = $user['status'] == 0 ? '��ֹ' : '���';
        }

        $this->assign('page', $page);
        $this->assign('listinfo', $listinfo);
        $this->display('listinfo');
    }

    public function edit()
    {
        if($this->_post('act') == 'edit'){
            $userid = (int)$this->_post('userid');
            $password = $this->_post('repassword');
            $nickname = $this->_post('username');
            $update = array();
            if($nickname){
                $update['nickname'] = $nickname;
            }
            if($password){
                include '../functions/global.func.php';
                $salt = _generate_key(6);
                $update['password'] = md5(md5($password) . $salt);
                $update['salt'] = $salt;

                /*ͬ���޸�*/
                $user = M('uc_user')->where('userid='.$userid)->select();
                $mobile = $user[0]['mobile'];
                $this->app_change_password($mobile, $password);
            }
            $res = M('uc_user')->where('userid='.$userid)->save($update);
            if (!$res) {
                $this->error('�޸�ʧ��', U('Uc_user/edit', array('id'=> $userid)));
                exit();
            } else {
                $this->success('�޸ĳɹ���', U('Uc_user/edit', array('id'=> $userid)));
                exit();
            }
        }

        $userid = intval($this->_get('id'));
        $userinfo = M('uc_user')->where('userid='.$userid)->limit(1)->select();
        $userinfo = $userinfo[0];

        $this->assign('userinfo', $userinfo);
        $this->display('edit');
    }

    public function ban(){
        $userid = (int)$this->_get('id');
        $userinfo = M('uc_user')->where('userid='.$userid)->limit(1)->select();
        $userinfo = $userinfo[0];
        if($userinfo['status'] == -1){
            $status = 0;
            $notice = '���';
        }else{
            $status = -1;
            $notice = '��ֹ';
        }
        $update = array(
            'status' => $status
        );
        $res = M('uc_user')->where('userid='.$userid)->save($update);
        if (!$res) {
            $this->error($notice.'�û�ʧ��', $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            $this->success($notice.'�û��ɹ���', $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    /**
     * @param $mobile
     * @param $password
     */
    public function app_change_password($mobile, $password)
    {
        /*ͬ���޸����뿪ʼ*/
        $api = 'http://passport.house365.com/?app=api&act=changePwd';
        $param = array(
            'phone'     => $mobile,
            'newpass'   => urlencode($password),
            'signature' => ''
        );
        file_put_contents('b.txt', $api. '&'. http_build_query($param));
        curl_post_contents($api. '&'. http_build_query($param), array());
        /*ͬ���޸��������*/
    }

    /**
     * @param $mobile
     * @param $password
     */
    public function app_register($mobile, $password)
    {
        /*ͬ��ע�Ὺʼ*/
        $api = 'http://passport.house365.com/?app=api&act=register';
        $param = array(
            'phone'     => $mobile,
            'password'  => urlencode($password),
            'signature' => ''
        );
        curl_post_contents($api. '&'. http_build_query($param), array());
        /*ͬ��ע�����*/
        return ;
    }
}