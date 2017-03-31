<?php
class IndexAction extends ExtendAction {
	public function _initialize(){
		parent::_initialize();
	}

	/**
	* ��ҳչ��
	*/
	public function index(){
		$this->display('Index:index');
	}

	/**
	 * ��½����
	 */
	public function login(){//�û���½��֤

		$act = $this->_post('act');
		if($act=='login'){
			//�û���
			$username = $this->_post('uname');
			//����
			$password = $this->_post('psw');

			//��ȡ��Ϣ
			//$json_user = curl_get_contents(OA_API . "api_prj.php?a=login&uid=$username&pwd=$password");
			//$userinfo = @json_decode($json_user);

			//���������
			//if(!$userinfo->u_id)
				//js_alert('�û������������',U("Index/login"));

			//��ȡ�û���Ϣ
			$record = M('Erp_users')->where("USERNAME='".$username."'")->find();

			//�Ƿ�����
			if($record['ISVALID'] != '-1')
				js_alert('�˺��ѱ�������',U("Index/login"));

			//��ȡ�û�Ȩ��
			$g = M('erp_group')->where("LOAN_GROUPID=$record[USERGROUP] and LOAN_GROUPSTATUS=0 or LOAN_GROUPDEL=1")->find();

			if(is_array($g))
				js_alert('Ȩ���ѱ�������',U("Index/login"));

			//��ȡ�û�������Ϣ
			$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();

			//�û���������ƴ��
			$user_city = 0;
			$user_city_py = '';

			//�Ȼ�ȡ���˳��У�û�����ȡ������������
			$user_city = $record['CITY'];
			if(!$user_city && $dept['ID'] ) {
				$dept = $this->getuserdept($dept['PARENTID']);
				$user_city = $dept['CITY_ID'];
			}

			//TODO ����CITY������CITY�󶨣�
			$user_city = 1;
			//��ȡ��ƴ
			$cond_where = "ID = ".intval($user_city);
			$city_info = M('erp_city')->field('PY')->where($cond_where)->find();
			$user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';

			//�û�Ȩ��
			$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']))) )));

			//session��ֵ
			$_SESSION['uinfo'] = array(
				'uid'=> $record['ID'],//�û�ID
				'role'=> $record['ROLEID'],//�û���ɫ
				'uname'=> $record['USERNAME'],//�û���
				'deptid'=> $record['DEPTID'],//���ű��
				'psw' => $password,
				'tname'=> $record['NAME'],//�û�����
				'pocity'=> $pocity,//�û�����Ȩ��
				'currentLogin'=> time(),//��ǰ��½ʱ��
				'city'=>$user_city,//��������
				'city_py' => $user_city_py,//��������ƴ��
			);

			die("<script>location.href='".U("Member/RegMember")."'</script>");
		}

		//������ת����½ҳ��
		$_SESSION['uinfo'] = null;
		$this->display('login');
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

	
}