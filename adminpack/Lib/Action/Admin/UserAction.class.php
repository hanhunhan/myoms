<?php

class UserAction extends ExtendAction {
	private $UserLog;
	 //构造函数
    public function __construct() {
		Vendor('Oms.UserLog');
		$this->UserLog = UserLog::Init();
        parent::__construct();
    }

    function viewUserNew() {
        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(125);
        $form->orderField = "ISVALID ASC,ID ASC";

        if ($_REQUEST['ID'] && $_REQUEST['showForm'] == 1) {

            if ($_REQUEST['ISCITY']) {
                $form->setMyField('DEPTID', 'READONLY', '-1', false);
                $form->setMyField('ROLEID', 'READONLY', '-1', false);
                $form->setMyField('NAME', 'READONLY', '-1', false);
                $form->setMyField('USERNAME', 'READONLY', '-1', false);
                $form->setMyField('TITLE', 'READONLY', '-1', false);
                $form->setMyField('ISVALID', 'READONLY', '-1', false);
                $form->setMyField('ISBUYER', 'READONLY', '-1', false);
            } else {
                $form->setMyField('CITY', 'READONLY', '-1', false);
                $form->setMyField('CITYS', 'READONLY', '-1', false);
            }
            $deptOptions = addslashes(u2g($form->getSelectTreeOption('DEPTID', '', -1)));
            $this->assign('deptOptions', $deptOptions);
            $this->assign('deptID', $_SESSION['uinfo']['deptid']);
            $this->assign('ID', $_REQUEST['ID']);
        }

        if ($_REQUEST['showForm'] == 3 && $_REQUEST['act'] == 'addparttime') {
            $form->SQLTEXT = 'ERP_USERS';
            $form->setMyField('PASSWORD', 'FORMVISIBLE', '-1', false);
            $form->setMyFieldVal('ISPARTTIME', '-1', false);

            $form->setMyFieldVal('CITY', $this->channelid, TRUE);
            $form->setMyFieldVal('CITYS', $this->channelid, TRUE);

            if ($_REQUEST['PASSWORD'] && $_REQUEST['faction'] == 'saveFormData') {
                $form->setMyFieldVal('PASSWORD', md5($_REQUEST['PASSWORD']), false);
            }

        } else $form->setMyField('PASSWORD', 'ISVIRTUAL', true);
        if ($_SESSION['uinfo']['loan_base'] != 1) $form->setMyField('ROLEID', 'LISTSQL', 'select LOAN_GROUPID,LOAN_GROUPNAME from ERP_GROUP where LOAN_GROUPDEL=0 and LOAN_GROUPSTATUS=1 and LOAN_BASE=0');
        if ($_REQUEST['showForm'] != 3 && $_REQUEST['ID'] && $_REQUEST['PASSWORD'] && $_REQUEST['faction'] == 'saveFormData') {
            if ($user['PASSWORD'] != $_REQUEST['PASSWORD']) {
                $form->setMyFieldVal('PASSWORD', md5($_REQUEST['PASSWORD']), false);

            }

        }
        $deptOptions = addslashes(u2g($form->getSelectTreeOption('DEPTID', '', -1)));
        $this->assign('deptOptions', $deptOptions);
        $form->GABTN = '<a href="' . U('/User/viewUserNew', 'showForm=3&act=addparttime') . '" class="btn btn-info btn-sm">新增兼职用户</a>';
        $form->CZBTN = '<a class="contrtable-link fedit btn btn-default btn-xs" onclick="baseInfo(this);" href ="javascript:void(0)">信息编辑</a> <a class="contrtable-link fedit btn btn-info btn-xs" onclick="cityInfo(this);" href ="javascript:void(0)">城市编辑</a><a class="contrtable-link fedit  btn btn-danger btn-xs" onclick="delInfo(this);" href ="javascript:void(0)">删除</a>';
        $condition = "CITY in (" . $this->channelid . ",0)";

        $formHtml = $form->where($condition)->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 保存上次检索的结果
        $this->assign('action', 'viewUserNew');
        $this->assign('form', $formHtml);
        $this->display('viewUserNew');
    }

    function viewUser() {
        $where = '1=1';
        $para = '';
        $searchtype = $this->_request('seartchtype');

        $keyword = $this->_request('keyword');
        $keyword = trim($keyword);
        if ($searchtype && $keyword) {
            switch ($searchtype) {
                case 1:
                    $where .= " and LOAN_USERNAME like '%$keyword%'";
                    break;
                case 2:
                    $where .= " and LOAN_TRUENAME like '%$keyword%'";
                    break;
            }
            $para .= 'seartchtype=' . urlencode($searchtype) . '&';
            $para .= 'keyword=' . urlencode($keyword) . '&';
            $this->assign('con', array('searchtype' => $searchtype, 'keyword' => $keyword));
        }
        //$where.=" and LOAN_USERCITY like '%".$this->channelid."%' ";  //echo $this->channelid;

        $depart = C('department_aray');//部门
        $count = M('admin_user')->where($where)->count();
        import("ORG.Util.Page");
        $p = new Page($count, C('PAGESIZE'));
        if ($para) $p->parameter = $para;
        $page = $p->show();
        $re = M('admin_user')->where($where)->order('LOAN_CREATED desc')->limit($p->firstRow . ',' . $p->listRows)->select();
        if (is_array($re)) {
            foreach ($re as $key => $val) {
                $re[$key]['LOAN_USERDEPART'] = $depart[$val['LOAN_USERDEPART']];
                $ret = M('admin_group')->where("loan_groupID='$val[LOAN_USERGROUP]'")->find();
                $judge = $ret['LOAN_GROUPSTATUS'] == 1 ? '' : '<font style="color:red">[非激活]</font>';
                $re[$key]['LOAN_USERGROUP'] = $ret['LOAN_GROUPNAME'] . $judge;

            }
        }
        //var_dump(M('admin_user')->getLastSql());
        $this->assign('page', $page);
        $this->assign('re', $re);
        $this->assign('depart', $depart);
        $this->display('user');
    }

    function addUser() {
        $depart = C('department_aray');//部门
        $powerfrom = C('power_come_from');//权限条口
        $powerseafrom = C('combile_power');//公海条口
        $city = C('city_array');//城市
        $group = M('admin_group')->where('LOAN_GROUPDEL=0 and LOAN_GROUPSTATUS=1')->select();
        $act = $this->_post('act');
        if ($act == 'add') {
            /*用户名判断*/
            $username = trim($this->_post('username'));
            if ($username == '') {
                js_show('usernameinfo', 1, '*用户名不能为空！');
                exit();
            }

            $record = M('admin_user')->where("LOAN_USERNAME='$username'")->find();
            if ($record) {
                js_show('usernameinfo', 1, '*用户名已存在！');
                exit();
            }
            js_show('usernameinfo');
            /*用户名判断*/

            $ss = array();
            $ss['LOAN_UPLOG'] = $_SESSION['uinfo']['uname'];//修改人
            $ss['LOAN_USERCITY'] = $this->_post('hometown');//用户城市
            $ss['LOAN_USERNAME'] = $this->_post('username');//用户名
            $ss['LOAN_TRUENAME'] = $this->_post('truename');//姓名
            $ss['LOAN_USERDEPART'] = $this->_post('department');//部门
            $ss['LOAN_POS'] = $this->_post('pos');//职位
            $ss['LOAN_USERGROUP'] = $this->_post('group');//角色
            $ss['LOAN_USERPWD'] = md5(C('DEFAULTPWD'));//默认密码
            $ss['LOAN_POWERCITY'] = join(',', $_POST['pcity']);//城市权限
            if ($_POST['pcity']) {//判断城市
                $loan_powerSeafrom = '';
                foreach ($_POST['pcity'] as $city) {
                    if ($_POST['spower'][$city]) {
                        $loan_powerSeafrom[$city] = $_POST['spower'][$city];
                    }
                }
                $ss['loan_powerSeafrom'] = serialize($loan_powerSeafrom);//公海条口权限
            }
            $ss['LOAN_POWERFROM'] = join(',', $_POST['cpower']);//条口权限
            $ss['LOAN_CREATED'] = $ss['LOAN_UPDATED'] = time();
            $affected = D('Admin_user')->add($ss); //echo D('Admin_user')->getLastSql();

            if ($affected) {
				$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],"用户添加成功！" ,serialize($ss));
                js_alert('用户添加成功！', U('User/viewUser'), $sty = 1);
                exit();//用户组的编辑*/
            } else {
				$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],"用户添加失败！" ,serialize($ss));
                js_alert('用户添加失败！');
                exit();
            }
        }

        $this->assign('action', U('User/addUser'));
        $this->assign('todo', 'add');
        $this->assign('menutitle', '用户添加');
        $this->assign('group', $group);
        $this->assign('city', $city);
        $this->assign('depart', $depart);
        $this->assign('powerfrom', $powerfrom);
        $this->assign('powerseafrom', $powerseafrom);
        $this->display('adduser');
    }

    function editUser() {
        $depart = C('department_aray');//部门
        $powerfrom = C('power_come_from');//权限条口
        $powerseafrom = C('combile_power');//公海条口
        $city = C('city_array');//城市
        $group = M('admin_group')->where('LOAN_GROUPDEL=0')->select();
        $todo = $this->_get('todo');
        $act = $this->_post('act');
        if ($todo == 'edit') {
            $id = $this->_get('id');
            $userinfo = M('admin_user')->where("LOAN_USERID='$id'")->find();
            $userinfo['loan_powerSeafrom'] = unserialize($userinfo['loan_powerSeafrom']);
            $this->assign('userinfo', $userinfo);
            $this->assign('todo', $todo);
            $this->assign('refurl', base64_encode($_SERVER['HTTP_REFERER']));

        }
        if ($act == 'edit') {
            $refurl = $this->_post('refurl');
            if ($refurl) {
                $refurl = base64_decode($refurl);
            } else {
                $refurl = '';
            }
            $id = $this->_post('id');
            /*用户名判断*/
            $username = trim($this->_post('username'));
            if ($username == '') {
                js_show('usernameinfo', 1, '*用户名不能为空！');
                exit();
            }
            js_show('usernameinfo');
            /*用户名判断*/
            $ss = array();
            $ss['LOAN_UPLOG'] = $_SESSION['uinfo']['uname'];//修改人
            $ss['LOAN_USERCITY'] = $this->_post('hometown');//用户城市
            $ss['LOAN_USERNAME'] = $this->_post('username');//用户名
            $ss['LOAN_TRUENAME'] = $_REQUEST['truename'];//$this->_post('truename');//姓名
            $ss['LOAN_USERDEPART'] = $this->_post('department');//部门
            $ss['LOAN_POS'] = $this->_post('pos');//职位
            $ss['LOAN_USERGROUP'] = $this->_post('group');//角色
            $ss['LOAN_POWERCITY'] = join(',', $_POST['pcity']);//城市权限
            if ($_POST['pcity']) {//判断城市
                $loan_powerSeafrom = '';
                foreach ($_POST['pcity'] as $city) {
                    if ($_POST['spower'][$city]) {
                        $loan_powerSeafrom[$city] = $_POST['spower'][$city];
                    }
                }
                $ss['loan_powerSeafrom'] = serialize($loan_powerSeafrom);//公海条口权限
            }
            $ss['LOAN_POWERFROM'] = join(',', $_POST['cpower']);//条口权限
            $ss['LOAN_UPDATED'] = time();
            $affected = D('Admin_user')->where("LOAN_USERID='$id'")->save($ss);
            if ($affected) {
				$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],"用户修改成功！" ,serialize($ss));
                js_alert('用户修改成功！', $refurl, $sty = 1);
                exit();//用户组的编辑*/
            } else {
				$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],"用户添加失败！" ,serialize($ss));
                js_alert('用户修改失败！');
                exit();
            }
        }
        $this->assign('action', U('User/editUser'));
        $this->assign('menutitle', '用户修改');
        $this->assign('group', $group);
        $this->assign('city', $city);
        $this->assign('depart', $depart);
        $this->assign('powerfrom', $powerfrom);
        $this->assign('powerseafrom', $powerseafrom);
        $this->display('adduser');
    }

    function lockUser() {
        $act = $this->_get('act');
        if ($act == 'lock') {
            $id = $this->_get('id');
            $ss['LOAN_LOCK'] = $this->_get('val');
            M('admin_user')->where("LOAN_USERID='$id'")->save($ss);
            if ($ss['LOAN_LOCK']) {
                $info = '用户锁定成功！';
            } else {
                $info = '用户解锁成功！';
            }
			$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],$info ,serialize($ss));
            $this->success($info, U('User/viewUser'));
            exit();
        }
    }

    function userinfoNew() {
        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(125);
        $form->FORMTYPE = 'FORM';
        $_REQUEST['ID'] = $_SESSION['uinfo']['uid'];

        if ($_REQUEST['showForm'] == 1 && $_REQUEST['ID']) {
            // todo
        }

        $formhtml = $form->getResult();
        $this->assign('action', 'userinfoNew');  // 用于控制页面页签
        $this->assign('form', $formhtml);
        $this->display('viewUserNew');
    }

    function userinfo() {
        $act = $this->_post('act');
        $uid = $_SESSION['uinfo']['uid'];
        $userinfo = M('admin_user')->where("LOAN_USERID='$uid'")->find();

        if ($act == 'edit') {
            $user = array();
            $user['LOAN_TRUENAME'] = $this->_post('username');
            $user['LOAN_MOBILE'] = $this->_post('mo');
            $user['LOAN_QQ'] = $this->_post('qq');
            $user['LOAN_EMAIL'] = $this->_post('email');
            $affected = D('Admin_user')->where("LOAN_USERID='$uid'")->save($user);
            if ($affected === false) {
				$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],'个人信息修改失败',serialize($user));
                $this->error('个人信息修改失败', U('User/userinfo'));
                exit();
            } else {
				$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],'个人信息修改成功！',serialize($user));
                $this->success('个人信息修改成功！', U('User/userinfo'));
                exit();
            }
        }

        if ($act == 'pwd') {
            $pwd = trim($this->_post('oldpwd'));
            $newpwd = $this->_post('newpwd');
            if (md5($pwd) != $userinfo['LOAN_USERPWD']) {
                $this->error('旧密码输入不正确！', U('User/userinfo'));
                exit();
            }
            if ($newpwd) {
                $user = array();
                $user['LOAN_USERPWD'] = md5($newpwd);
                $affected = D('Admin_user')->where("LOAN_USERID='$uid'")->save($user);
                if ($affected === false) {
					$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],'个人密码修改失败',serialize($user));
                    $this->error('个人密码修改失败', U('User/userinfo'));
                    exit();
                } else {
					$this->UserLog->writeLog($affected,$_SERVER["REQUEST_URI"],'个人密码修改成功！',serialize($user));
                    $this->success('个人密码修改成功！', U('User/userinfo'));
                    exit();
                }
            }

        }
        $userinfo = M('admin_user')->where("LOAN_USERID='$uid'")->find();
        $this->assign('depart', C('department_aray'));
        $this->assign('userinfo', $userinfo);
        $this->display('userinfo');
    }
}

?>