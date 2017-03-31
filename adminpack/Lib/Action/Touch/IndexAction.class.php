<?php
class IndexAction extends ExtendAction {
    /**
     * 对应经管系统办卡客户的页签号
     */
    const ADMIN_REG_MEMBER_TAB = 22;

    const USER_INFO_SQL = <<<USER_INFO_SQL
        SELECT T.ID,
               T.ROLEID,
               T.USERNAME,
               T.CITYS AS CITIES,
               T.CITY,
               T.DEPTID,
               T.ISVALID,
               C.PY AS CITY_PY,
               C.NAME AS CITY_NAME,
               G.LOAN_GROUPNAME AS GROUP_NAME,
               G.LOAN_GROUPALL,
               G.LOAN_BASE,
               G.LOAN_VMEM,
               G.LOAN_GROUPNAME
        FROM ERP_USERS T
        LEFT JOIN ERP_CITY C ON C.ID = T.CITY
        LEFT JOIN ERP_GROUP G ON G.LOAN_GROUPID = T.ROLEID
        WHERE T.USERNAME = %s
USER_INFO_SQL;


	public function _initialize(){
		parent::_initialize();
	}

	/**
	* 首页展现
	*/
	public function index(){
		$this->display('Index:index');
	}

    protected function loginFromOA($data) {
        $response = false;
        if ($data['TOKEN'] && $data['uid'] && $data['TIMESTAMP']) {
            $username = $data['uid'];
            $timestamp = $data['TIMESTAMP'];
            $token = $data['TOKEN'];
            $qResult = D('erp_users')->query(sprintf(self::USER_INFO_SQL, "'{$username}'"));
            if (!empty($qResult)) {
                $userInfo = $qResult[0];
            }
            if (!empty($userInfo)) {
                if (empty($userInfo['CITY'])) {  // 如果从用户表未获取城市信息，则通过部门查找到城市信息
                    $deptInfoTmp = M('erp_dept')->where('ID=' . $userInfo['DEPTID'])->find();
                    $deptInfo = $this->getuserdept($deptInfoTmp['PARENTID']);
                    $userInfo['CITY'] = intval($deptInfo['CITY_ID']);
                    $cityInfo = M('erp_city')->field('PY')->where("ID = {$userInfo['CITY']}")->find();
                    if (!empty($cityInfo)) {
                        $userInfo['CITY_PY'] = $cityInfo['PY'];
                    }
                }


                if (md5(C('DEFAULTPWD') . $timestamp . $username) == $token) {
                    $_SESSION['uinfo'] = array(
                        'uid' => $userInfo['ID'],//用户ID
                        'role' => $userInfo['ROLEID'],//用户角色
                        'uname' => $userInfo['USERNAME'],//用户名
                        'deptid' => $userInfo['DEPTID'],//部门编号
                        'tname' => $userInfo['NAME'],//用户姓名
                        'pocity' => $userInfo['CITIES'],//用户城市权限
                        //'pofrom'=> $record['LOAN_POWERFROM'],//用户条口
                        'currentLogin' => time(),//当前登陆时间
                        //'lastLogin'=> $record['LOAN_LOGINTIME'],//用户上次登陆时间
                        //'flow'=> $record['LOAN_FLOW']//流程权限
                        'city' => $userInfo['CITY'],//所属城市
                        'city_name' => $userInfo['CITY_NAME'],  // 城市名称
                        'city_py' => $userInfo['CITY_PY'],//所属城市拼音
                        'p_auth_all' => $userInfo['LOAN_GROUPALL'],
                        'loan_base' => $userInfo['LOAN_BASE'],
                        'p_vmem_all' => $userInfo['LOAN_VMEM'],
                        'group_name' => $userInfo['LOAN_GROUPNAME']
                    );

                    $response = true;
                }
            }
        }

        return $response;
    }

	/**
	 * 登陆操作
	 */
	public function login(){//用户登陆验证
        $loginOK = false;
        if ($_REQUEST['TOKEN'] && $_REQUEST['uid'] && $_REQUEST['TIMESTAMP']) {
            $loginOK = $this->loginFromOA($_REQUEST);
        } else {
            $act = $this->_post('act');
            if($act == 'login') {
                $username = trim($this->_post('uname')); //用户名
                $password = md5(trim($this->_post('psw'))); //密码

                $sql = sprintf(self::USER_INFO_SQL, "'{$username}'");  // 测试
//                $sql .= " AND T.PASSWORD = '{$password}'";  // 正式
                //获取用户信息
                $dbResult = D('erp_users')->query($sql);

                if (!empty($dbResult)) {
                    $userInfo = $dbResult[0];
                }

                //有没有该用户
                if(empty($userInfo)) {
                    js_alert('对不起，您输入的用户名有误！',U("Index/login"));
                    exit();
                }

                //是否锁定
                if($userInfo['ISVALID'] != '-1') {
                    js_alert('账号已被锁定！', U("Index/login"));
                    exit();
                }

                if (empty($userInfo['CITY'])) {  // 如果从用户表未获取城市信息，则通过部门查找到城市信息
                    $deptInfoTmp = M('erp_dept')->where('ID=' . $userInfo['DEPTID'])->find();
                    $deptInfo = $this->getuserdept($deptInfoTmp['PARENTID']);
                    $userInfo['CITY'] = intval($deptInfo['CITY_ID']);
                    $cityInfo = M('erp_city')->field('PY')->where("ID = {$userInfo['CITY']}")->find();
                    if (!empty($cityInfo)) {
                        $userInfo['CITY_PY'] = $cityInfo['PY'];
                    }
                }

                // 存储用户信息
                $_SESSION['uinfo'] = array(
                    'uid' => $userInfo['ID'],//用户ID
                    'role' => $userInfo['ROLEID'],//用户角色
                    'uname' => $userInfo['USERNAME'],//用户名
                    'deptid' => $userInfo['DEPTID'],//部门编号
                    'tname' => $userInfo['NAME'],//用户姓名
                    'pocity' => $userInfo['CITIES'],//用户城市权限
                    //'pofrom'=> $record['LOAN_POWERFROM'],//用户条口
                    'currentLogin' => time(),//当前登陆时间
                    //'lastLogin'=> $record['LOAN_LOGINTIME'],//用户上次登陆时间
                    //'flow'=> $record['LOAN_FLOW']//流程权限
                    'city' => $userInfo['CITY'],//所属城市
                    'city_py' => $userInfo['CITY_PY'],//所属城市拼音
                    'p_auth_all' => $userInfo['LOAN_GROUPALL'],
                    'loan_base' => $userInfo['LOAN_BASE'],
                    'p_vmem_all' => $userInfo['LOAN_VMEM'],
                    'group_name' => $userInfo['LOAN_GROUPNAME']
                );

                $loginOK = true;
            }
        }

        $this->assign('year', date('Y'));  // 版权信息需要显示的年份
		//否则跳转到登陆页面
        if (!$loginOK) {
            $_SESSION['uinfo'] = null;
            $this->display('login2');
        } else {
            // 跳转至类别选择页面
            die("<script>location.href='".U("Index/chooseCate")."'</script>");
        }
	}

	/**
	 * 根据部门ID获取部门信息
	 * @param $deptid 部门ID
	 * @return mixed
	 */
	protected function getuserdept($deptid){
		if($deptid)	$dept = M('erp_dept')->where('ID='.$deptid)->find();
		if($dept && $dept['CITY_ID']==null){
			$dept = $this->getuserdept($dept['PARENTID']);
		}else{
			return $dept;
		}
		return $dept;
	}


	/**
	 * 退出操作
	 */
	function loginOut(){//退出操作
		//清除session
		$_SESSION['uinfo'] = null;
		//清除cookie
		clear_cookie(CHANNELID);
		clear_cookie(POWER);
		clear_cookie(CITYEN);
		//登陆页面
		echo "<script>location.href='".U("Index/index")."'</script>";
	}

    /**
     * 选择要处理的事务
     */
    public function chooseCate() {
        $this->assign('year', date('Y'));
        $this->display('choose_cate');
    }
}