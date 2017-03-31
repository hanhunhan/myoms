<?php
class IndexAction extends ExtendAction {
	public function _initialize(){
		parent::_initialize();
	}

	/**
	* 首页展现
	*/
	public function index(){
		$this->display('Index:index');
	}

	/**
	 * 登陆操作
	 */
	public function login(){//用户登陆验证

		$act = $this->_post('act');
		if($act=='login'){
			//用户名
			$username = $this->_post('uname');
			//密码
			$password = $this->_post('psw');

			//获取信息
			//$json_user = curl_get_contents(OA_API . "api_prj.php?a=login&uid=$username&pwd=$password");
			//$userinfo = @json_decode($json_user);

			//如果不存在
			//if(!$userinfo->u_id)
				//js_alert('用户名或密码错误！',U("Index/login"));

			//获取用户信息
			$record = M('Erp_users')->where("USERNAME='".$username."'")->find();

			//是否锁定
			if($record['ISVALID'] != '-1')
				js_alert('账号已被锁定！',U("Index/login"));

			//获取用户权限
			$g = M('erp_group')->where("LOAN_GROUPID=$record[USERGROUP] and LOAN_GROUPSTATUS=0 or LOAN_GROUPDEL=1")->find();

			if(is_array($g))
				js_alert('权限已被锁定！',U("Index/login"));

			//获取用户部门信息
			$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();

			//用户所属城市拼音
			$user_city = 0;
			$user_city_py = '';

			//先获取个人城市，没有则获取部门所属城市
			$user_city = $record['CITY'];
			if(!$user_city && $dept['ID'] ) {
				$dept = $this->getuserdept($dept['PARENTID']);
				$user_city = $dept['CITY_ID'];
			}

			//TODO 部门CITY（个人CITY绑定）
			$user_city = 1;
			//获取简拼
			$cond_where = "ID = ".intval($user_city);
			$city_info = M('erp_city')->field('PY')->where($cond_where)->find();
			$user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';

			//用户权限
			$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']))) )));

			//session赋值
			$_SESSION['uinfo'] = array(
				'uid'=> $record['ID'],//用户ID
				'role'=> $record['ROLEID'],//用户角色
				'uname'=> $record['USERNAME'],//用户名
				'deptid'=> $record['DEPTID'],//部门编号
				'psw' => $password,
				'tname'=> $record['NAME'],//用户姓名
				'pocity'=> $pocity,//用户城市权限
				'currentLogin'=> time(),//当前登陆时间
				'city'=>$user_city,//所属城市
				'city_py' => $user_city_py,//所属城市拼音
			);

			die("<script>location.href='".U("Member/RegMember")."'</script>");
		}

		//否则跳转到登陆页面
		$_SESSION['uinfo'] = null;
		$this->display('login');
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

	
}