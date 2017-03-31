<?php
class IndexAction extends ExtendAction {
    /**
     * ��Ӧ����ϵͳ�쿨�ͻ���ҳǩ��
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
	* ��ҳչ��
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
                if (empty($userInfo['CITY'])) {  // ������û���δ��ȡ������Ϣ����ͨ�����Ų��ҵ�������Ϣ
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
                        'uid' => $userInfo['ID'],//�û�ID
                        'role' => $userInfo['ROLEID'],//�û���ɫ
                        'uname' => $userInfo['USERNAME'],//�û���
                        'deptid' => $userInfo['DEPTID'],//���ű��
                        'tname' => $userInfo['NAME'],//�û�����
                        'pocity' => $userInfo['CITIES'],//�û�����Ȩ��
                        //'pofrom'=> $record['LOAN_POWERFROM'],//�û�����
                        'currentLogin' => time(),//��ǰ��½ʱ��
                        //'lastLogin'=> $record['LOAN_LOGINTIME'],//�û��ϴε�½ʱ��
                        //'flow'=> $record['LOAN_FLOW']//����Ȩ��
                        'city' => $userInfo['CITY'],//��������
                        'city_name' => $userInfo['CITY_NAME'],  // ��������
                        'city_py' => $userInfo['CITY_PY'],//��������ƴ��
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
	 * ��½����
	 */
	public function login(){//�û���½��֤
        $loginOK = false;
        if ($_REQUEST['TOKEN'] && $_REQUEST['uid'] && $_REQUEST['TIMESTAMP']) {
            $loginOK = $this->loginFromOA($_REQUEST);
        } else {
            $act = $this->_post('act');
            if($act == 'login') {
                $username = trim($this->_post('uname')); //�û���
                $password = md5(trim($this->_post('psw'))); //����

                $sql = sprintf(self::USER_INFO_SQL, "'{$username}'");  // ����
//                $sql .= " AND T.PASSWORD = '{$password}'";  // ��ʽ
                //��ȡ�û���Ϣ
                $dbResult = D('erp_users')->query($sql);

                if (!empty($dbResult)) {
                    $userInfo = $dbResult[0];
                }

                //��û�и��û�
                if(empty($userInfo)) {
                    js_alert('�Բ�����������û�������',U("Index/login"));
                    exit();
                }

                //�Ƿ�����
                if($userInfo['ISVALID'] != '-1') {
                    js_alert('�˺��ѱ�������', U("Index/login"));
                    exit();
                }

                if (empty($userInfo['CITY'])) {  // ������û���δ��ȡ������Ϣ����ͨ�����Ų��ҵ�������Ϣ
                    $deptInfoTmp = M('erp_dept')->where('ID=' . $userInfo['DEPTID'])->find();
                    $deptInfo = $this->getuserdept($deptInfoTmp['PARENTID']);
                    $userInfo['CITY'] = intval($deptInfo['CITY_ID']);
                    $cityInfo = M('erp_city')->field('PY')->where("ID = {$userInfo['CITY']}")->find();
                    if (!empty($cityInfo)) {
                        $userInfo['CITY_PY'] = $cityInfo['PY'];
                    }
                }

                // �洢�û���Ϣ
                $_SESSION['uinfo'] = array(
                    'uid' => $userInfo['ID'],//�û�ID
                    'role' => $userInfo['ROLEID'],//�û���ɫ
                    'uname' => $userInfo['USERNAME'],//�û���
                    'deptid' => $userInfo['DEPTID'],//���ű��
                    'tname' => $userInfo['NAME'],//�û�����
                    'pocity' => $userInfo['CITIES'],//�û�����Ȩ��
                    //'pofrom'=> $record['LOAN_POWERFROM'],//�û�����
                    'currentLogin' => time(),//��ǰ��½ʱ��
                    //'lastLogin'=> $record['LOAN_LOGINTIME'],//�û��ϴε�½ʱ��
                    //'flow'=> $record['LOAN_FLOW']//����Ȩ��
                    'city' => $userInfo['CITY'],//��������
                    'city_py' => $userInfo['CITY_PY'],//��������ƴ��
                    'p_auth_all' => $userInfo['LOAN_GROUPALL'],
                    'loan_base' => $userInfo['LOAN_BASE'],
                    'p_vmem_all' => $userInfo['LOAN_VMEM'],
                    'group_name' => $userInfo['LOAN_GROUPNAME']
                );

                $loginOK = true;
            }
        }

        $this->assign('year', date('Y'));  // ��Ȩ��Ϣ��Ҫ��ʾ�����
		//������ת����½ҳ��
        if (!$loginOK) {
            $_SESSION['uinfo'] = null;
            $this->display('login2');
        } else {
            // ��ת�����ѡ��ҳ��
            die("<script>location.href='".U("Index/chooseCate")."'</script>");
        }
	}

	/**
	 * ���ݲ���ID��ȡ������Ϣ
	 * @param $deptid ����ID
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
	 * �˳�����
	 */
	function loginOut(){//�˳�����
		//���session
		$_SESSION['uinfo'] = null;
		//���cookie
		clear_cookie(CHANNELID);
		clear_cookie(POWER);
		clear_cookie(CITYEN);
		//��½ҳ��
		echo "<script>location.href='".U("Index/index")."'</script>";
	}

    /**
     * ѡ��Ҫ���������
     */
    public function chooseCate() {
        $this->assign('year', date('Y'));
        $this->display('choose_cate');
    }
}