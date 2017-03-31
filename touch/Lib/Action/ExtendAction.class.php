<?php
	class ExtendAction extends Action{
		public $noLoginWaitSecond = '0';
		public $noLoginMessage = '您的登录过期，页面跳转中~';
		public $noPowerCity = '您没有城市的权限';
		public $noPowerFrom = '您没有条口权限';
		public $channelid = '';//当前城市权限
		public $testidd = '';//当前城市权限
		public $powercity = '';//所有城市权限
		public $allpower = '';
		public $power = '';//当前条口
		public $city_config_array;
		public $city_config;
		//public $nodelist ='';//所有的公海权限
		//public $nodename = '';

		/**
		 * 初始化基类（相关权限判断）
		 */
		function _initialize(){
			//http://221.231.141.162/tlfjk_mobile/?m=login&a=login&authcode=a5d1KEhYI7xj7lreJ2phk%2B2hQDbATzih8A%2BrolDYSDQGMgtEjNFhNdo
			//authcode权限判断(session 赋值)
			$authcode = isset($_GET['authcode'])?trim($_GET['authcode']):'';
			if(!empty($authcode)) {
				$authcode = get_authcode($authcode);
				list($uid, $gid, $username) = explode("$", $authcode);

				//session赋值
				$this->setUserSession($username);
			}

			//模块和方法
			$m = MODULE_NAME;
			$a = ACTION_NAME;

			$model = $m.'/'.$a;//模块
			if(in_array($model,C('NONEPOWER'))) return;

			//是否登录过期(跳转到登陆页面)
			if(!is_array($_SESSION['uinfo']) ) {
				echo "<script>location.href='".U("Index/login")."'</script>";
				exit();
			}

			//用户的城市操作权限
			$channel = $_SESSION['uinfo']['pocity'];

			if(empty($channel) && empty($_SESSION['uinfo']['city'])){
				$this->redirect("Index/login","",$this->noLoginWaitSecond,$this->noPowerCity);
			}
			$this->powercity = $channel;
			$channel = explode(',',$channel);
			$channel = array_filter($channel);

			//城市权限条口
			$channelid = $this->_request('channelid');
			if(!$channelid ){
				$channelid = $_COOKIE['CHANNELID']; //echo $channelid;
			}
			if(!in_array($channelid,$channel)){
				$channelid = '';
			}

			if(!$channelid ){ $channelid = $channel[0] ;}
			$this->channelid = $channelid;

			cookie('CHANNELID',$channelid,3600*24);//城市id

			$cityarr = M('erp_city')->where("ISVALID=-1")->select();
			foreach($cityarr as $v){
				$this->city_config_array[$v['ID']] = $v['PY'];
				$this->city_config[$v['ID']] = $v['NAME'];
			}
			$this->city = $this->city_config_array[$channelid]; //echo $channelid;
			cookie('CITYEN',$this->city);//城市拼音简写

			//给予默认城市
			if(in_array($model,C('NONEROLE'))) return ;//这个页面不做角色判断哦

			//（模块权限的认证）
			$auth = $this->roleAuth($m,$a);

			//没有权限
			if($auth==false){
				if(IS_AJAX){
					$ress['msg'] = g2u('您的权限不足，请联系管理员');
					die(@json_encode($ress));
				}else{
					$this->error('您的权限不足，请联系管理员！');
				}
			}
		}

		/**
		 * 根据用户ID赋值用户的相关信息
		 * @param $username 用户ID
		 */
		protected function setUserSession($username){
			//获取用户的相关信息
			$record = M('Erp_users')->where("USERNAME='".$username."' ")->find();
			//获取用户所在部门的相关信息
			$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();
			//获取用户的相关城市权限
			$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']))) )));

			//所属的城市拼音
			$user_city_py = '';
			if(!$dept['CITY_ID'] ) {
				$dept = $this->getuserdept($dept['PARENTID']);
				$cond_where = "ID = ".intval($dept['CITY_ID']);
				$city_info = M('erp_city')->field('PY')->where($cond_where)->find();
				$user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';
			}

			//session赋值
			$_SESSION['uinfo'] = array(
				'uid'=> $record['ID'],//用户ID
				'role'=> $record['ROLEID'],//用户角色
				'uname'=> $record['USERNAME'],//用户名
				'deptid'=> $record['DEPTID'],//部门编号
				'psw' => $record['PASSWORD'],
				'tname'=> $record['NAME'],//用户姓名
				'pocity'=> $pocity,//用户城市权限
				'currentLogin'=> time(),//当前登陆时间
				'city'=>$dept['CITY_ID'],//所属城市
				'city_py' => $user_city_py,//所属城市拼音
				'is_login_from_oa' => true,//是否来自OA
				//'lastLogin'=> $record['LOAN_LOGINTIME'],//用户上次登陆时间
				//'flow'=> $record['LOAN_FLOW']//流程权限
			);

		}

		/**
		 * 用户的相关权限验证
		 *
		 * @param $m 模块
		 * @param $a 方法
		 * @return bool|void
		 */
		protected function roleAuth($m,$a){

			//用户权限组
			$groupid = $_SESSION['uinfo']['role'];
			$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();
	
			$groupval = $group['LOAN_GROUPVAL'];

			//没有权限
			if(empty($groupval)) return;

			$groupval = explode(',',$groupval);
			$status = false;
			$nop = false;//带参数的role如果存在却没有设置权限则认为无权限无需继续验证
			$record = M('erp_role')->where("LOAN_ROLEPARENTID<>0 and LOAN_ROLEDISPLAY=0 and LOAN_PARAM is not null ")->select();//LOAN_ROLEDISPLAY 1为删除
			if(is_array($record)){
				foreach($record as $rval){
					$paramCheck = true;
					
					if($rval['LOAN_PARAM']){
						$paramtemp = array_filter( explode(';',$rval['LOAN_PARAM']) );
						foreach($paramtemp as  $vv){
							$vvarr = explode('=',$vv);
							$paramCheck = $_REQUEST[trim($vvarr[0])] == $vvarr[1] ? true:false;  //var_dump($paramCheck);
							if(!$paramCheck) break;//有一个参数不一样就退出
						}
					}  
					if($rval['LOAN_ROLECONTROL']==$m && $rval['LOAN_ROLEACTION']==$a && $paramCheck){
						if(in_array($rval['LOAN_ROLEID'],$groupval) ){

							$status = true;
						}else{
							$nop = true;
						}
						
					}
				}
			}
			if($status==false && $nop==false){
				$record = M('erp_role')->where("LOAN_ROLEPARENTID<>0 and LOAN_ROLEDISPLAY=0 and LOAN_PARAM is null ")->select();
				if(is_array($record)){
					foreach($record as $rval){
						if($rval['LOAN_ROLECONTROL']==$m && $rval['LOAN_ROLEACTION']==$a && in_array($rval['LOAN_ROLEID'],$groupval)){
							$status = true;
						}
					}
				}
			}
	
			return $status;
		}
        
	}
?>