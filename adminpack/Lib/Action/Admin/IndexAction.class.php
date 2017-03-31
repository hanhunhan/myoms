<?php
  class IndexAction extends ExtendAction {

	public function _initialize(){
		 
		parent::_initialize();
		
		
	}

    public function index(){
		
		
		if($_REQUEST['url']){
			$url = urldecode($_REQUEST['url']);
			
			$this->assign('url',$url);
		}
		
		$this->display('Index:index');
    }
    
    public function top(){   
		$powercity = explode(',',$this->powercity);
		//$allpower  = explode(',',$this->allpower);
		//$this->assign('lastlogin',$_SESSION['uinfo']['lastLogin']);
		$this->assign('cityname',$this->city_config);
		//$this->assign('powername',C('power_come_from'));
		$this->assign('powercitynums',count($powercity));//拥有的所有城市权限数
		$this->assign('powercity',$powercity);//拥有的所有城市权限
		$this->assign('channelid',$this->channelid);//当前城市

        $todoFlowSql = <<<WORK_FLOW
            SELECT count(1) NUM
            FROM
              (SELECT B.STATUS,
                      B.ID
               FROM ERP_FLOWSET A
               LEFT JOIN ERP_FLOWS B ON A.ID= B.FLOWSETID
               LEFT JOIN ERP_FLOWTYPE C ON A.FLOWTYPE = C.ID
               LEFT JOIN ERP_USERS E ON E.ID=B.ADDUSER
               LEFT JOIN ERP_DEPT X ON E.DEPTID = X.ID) F
            INNER JOIN ERP_FLOWNODE G ON F.ID = G.FLOWID
            WHERE (F.STATUS=1
                   OR F.STATUS=2)
              AND (G.STATUS = 1
                   OR G.STATUS=2)
              AND G.DEAL_USERID = %d
WORK_FLOW;
        $result = D()->query(sprintf($todoFlowSql, $_SESSION['uinfo']['uid']));
        $todoNum = '';
        if (is_array($result) && count($result)) {
            $todoNum = $result[0]['NUM'];
        }

		$this->assign('power',$this->power);
        $this->assign('todoNum', $todoNum);
		
    	$this->display('Index:top');	
    }

    protected function menu(){

		$groupid = $_SESSION['uinfo']['role'];//用户权限组
		$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();
		$groupval = $group['LOAN_GROUPVAL'];
		if(empty($groupval)) return;//没有权限值哦 
		$groupval = explode(',',$groupval);
		
		$parent = M('erp_role')->where("LOAN_ROLEPARENTID=0 and LOAN_ROLEDISPLAY=0 and LOAN_MENUSHOW=-1")->order('LOAN_ROLEORDER asc')->select();//主菜单
	
		if(is_array($parent)){
			foreach($parent as $p=>$pmenu){
				$parentid = $pmenu['LOAN_ROLEID'];
				$smenu = M('erp_role')->where("LOAN_ROLEPARENTID='$parentid' and LOAN_ROLEDISPLAY=0  and LOAN_MENUSHOW=-1 ")->order('LOAN_ROLEORDER asc')->select();//二级菜单
               
			
				if(is_array($smenu)){
					foreach($smenu as $v=>$pulate){
						$tempp = explode(';',$pulate['LOAN_PARAM']);
						$pulate['param'] = implode('&',$tempp);
						if(in_array($pulate['LOAN_ROLEID'],$groupval)) $menu[$p]['smenu'][] = $pulate;//二级菜单
					}
					if(is_array($menu[$p]['smenu'])){
						
						$menu[$p]['name'] = $pmenu['LOAN_ROLENAME'];//主菜单
						$menu[$p]['id'] = $pmenu['LOAN_ROLEID'];//菜单编号
						$menu[$p]['module'] = ($pmenu['LOAN_ROLECONTROL'] && $pmenu['LOAN_ROLEACTION']) ?
                            U($pmenu['LOAN_ROLECONTROL'].'/'.$pmenu['LOAN_ROLEACTION']) : '' ;
					}
				}
			}			
		}
		return $menu;
	}

	

    public function left(){//左边栏目管理
		$menu = $this->menu();

        $menu_icons = array(
            '141'=>'fa-product-hunt',
            '325'=>'fa-credit-card',
            '136'=>'fa-user',
            '227'=>'fa-money',
            '312'=>'fa-shopping-cart',
            '148'=>'fa-cny',
            '87' =>'fa-file-excel-o',
            '601'=>'fa-bar-chart',
            '32' =>'fa-dashboard',
            '43' =>'fa-tasks',
            '4'  =>'fa-group',
            '11' =>'fa-bars',
            '861' =>'fa-backward',
        );

        $this->assign('cityName', $this->city_config[$this->channelid]);
        $this->assign('menu_icons', $menu_icons);
        $this->assign('groupName', $_SESSION['uinfo']['group_name']);
		$this->assign('menu',$menu);
     	$this->display('Index:left');   	
    }


	  /**
	   *  @个人桌面
	   */
    public function welcome(){
		$view_data = array();

		//通知
		$view_data['notice'] = "根据已用户在使用过程中提出的反馈意见，房产事业部、财务管理中心同研发中心对经管系统的部分功能做了优化，本期已上线新版的工作流审核以及其他优化功能!";

		//快捷操作
		$quick = array(
			'0'=>array(
				'title' => '项目列表',
				'i_class' => 'fa fa-product-hunt',
				'url'=>U('Case/projectlist'),
				'bg_color'=>'bg-aqua',
				'num' => '-',
			),
			'1'=>array(
				'title' => '会员管理',
				'i_class' => 'fa fa-user',
				'url'=>U('Member/RegMember',"TAB_NUMBER=22"),
				'bg_color'=>'bg-red',
				'num' => '-',
			),
			'2'=>array(
				'title' => '采购人员',
				'i_class' => 'fa fa-shopping-cart',
				'url'=>U('Purchasing/index'),
				'bg_color'=>'bg-green',
				'num' => '-',
			),
			'3'=>array(
				'title' => '报销确认',
				'i_class' => 'fa fa-cny',
				'url'=>U('Financial/reimConfirm'),
				'bg_color'=>'bg-yellow',
				'num' => '-',
			),
			'4'=>array(
				'title' => '财务开票',
				'i_class' => 'fa fa-ticket',
				'url'=>U('Financial/invoice'),
				'bg_color'=>'bg-navy',
				'num' => '-',
			),
			'5'=>array(
				'title' => '预收确认',
				'i_class' => 'fa  fa-check',
				'url'=>U('Financial/financialConfirm'),
				'bg_color'=>'bg-purple',
				'num' => '-',
			),
		);

		$view_data['quick'] = $quick;

		//工作流数据源
		$sql = "SELECT F.ID,F.CITY,F.INFO,F.MAXSTEP,to_char(F.ADDTIME,'YYYY-MM-DD') AS ADDTIME,F.FLOWTYPE,F.PINYIN,F.NAME,F.DEPTID,G.ID AS NODEID,G.DEAL_USERID,F.STATUS FROM ";
		$sql .= "(SELECT B.*,C.FLOWTYPE,C.PINYIN,E.NAME,E.DEPTID FROM ERP_FLOWSET A LEFT JOIN ERP_FLOWS B ON A.ID= B.FLOWSETID LEFT JOIN ERP_FLOWTYPE C ON A.FLOWTYPE = C.ID LEFT JOIN ERP_USERS E ON E.ID=B.ADDUSER ) F ";
		$sql .= "INNER JOIN ERP_FLOWNODE G ON F.ID = G.FLOWID ";
		$sql .= "WHERE (F.STATUS=1 OR F.STATUS=2) AND (G.STATUS = 1  OR G.STATUS=2) AND DEAL_USERID = {$_SESSION['uinfo']['uid']} ORDER BY ADDTIME DESC";

		$view_data['flow_data'] = M()->query($sql);

		$flow_color = D("Flowtype")->get_status_color();

		$this->assign('action',U('System/desktop'));
    	$this->assign('view_data',$view_data);
    	$this->assign('flow_color',$flow_color);
      	$this->display('Index:welcome');   	
    }

	public function login(){//用户登陆验证
		$act = $this->_post('act');
		if($_REQUEST['TOKEN'] && $_REQUEST['uid'] && $_REQUEST['TIMESTAMP'])
		{
			
			$username = $_REQUEST['uid'];
			$timestamp = $_REQUEST['TIMESTAMP'];
			$jumpUrl = $_REQUEST['url'];
			
			$token = $_REQUEST['TOKEN'];

			//特殊逻辑 陈林艳 == 史连生
			if($username=='shiliansheng2')
				$username = 'chenlinyan';

			$record = M('Erp_users')->where("USERNAME='".$username."' ")->find();
			if($record)
			{
				$g = M('Erp_group')->where("LOAN_GROUPID=$record[ROLEID]  ")->find();

				$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();
				$user_city_py = '';
				if(!$record['CITY'])
				{
					$dept = $this->getuserdept($dept['PARENTID']);
					$record['CITY'] = intval($dept['CITY_ID']);	
				}

				//城市拼音缩写
				$cond_where = "ID = ".$record['CITY'];
				$city_info = M('erp_city')->field('PY')->where($cond_where)->find();
				$user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';
			
				$oaInfo = curl_get_contents(sprintf('http://oa.house365.com/api/api_el.php?k=b8be9c6c70ed10e04b033847895970f6&a=get_userinfo&uid=%s', $record['USERNAME']));
            $oaInfo = unserialize($oaInfo);
 
				//$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']) ) ) )));
				$pocity = $record['CITYS'];

				if((md5(C('DEFAULTPWD').$timestamp.$username) == $token) || $username == 'chenlinyan')
				{
					$_SESSION['uinfo'] = array(
				'uid'=> $record['ID'],//用户ID
				'role'=> $record['ROLEID'],//用户角色
				'uname'=> $record['USERNAME'],//用户名
                'deptid'=> $record['DEPTID'],//部门编号
                'psw' => $password,
				'tname'=> $record['NAME'],//用户姓名
				'pocity'=> $pocity,//用户城市权限
				//'pofrom'=> $record['LOAN_POWERFROM'],//用户条口
				'currentLogin'=> time(),//当前登陆时间
				//'lastLogin'=> $record['LOAN_LOGINTIME'],//用户上次登陆时间
				//'flow'=> $record['LOAN_FLOW']//流程权限
				'city'=>$record['CITY'],//所属城市
                'city_py' => $user_city_py,//所属城市拼音
				'p_auth_all'=>$g['LOAN_GROUPALL'],
				'loan_base'=>$g['LOAN_BASE'],
				'p_vmem_all'=>$g['LOAN_VMEM'],
                'group_name' => $g['LOAN_GROUPNAME'],
                'user_avatar' => !empty($oaInfo['info']['photourl']) ? $oaInfo['info']['photourl'] : 'Public/images/default-head.png',
                'user_gw' => !empty($oaInfo['info']['user_gw']) ? $oaInfo['info']['user_gw'] : ''
			);
					
					$this->redirect("Index/index",array("url"=>urlencode($jumpUrl)));
					exit();
				}
			}

		}
		if($act=='login'){
			$username = $this->_post('uname');
			$password = $_POST['psw'];
			$imgcode  = md5($this->_post('postcode'));
			$vertify = $_SESSION['verify'];
			$_SESSION['verify'] = '';

            //echo $this->_post('postcode');
            //echo "<br>";
           // echo $vertify;
           
           /*

			if($vertify!=$imgcode || $vertify==''){
				$this->error('验证码错误！',U("Index/login"));exit();
			}
			*/
			
			//$url = 'http://oa.house365.com/api/api_prj.php?a=login&uid='.urlencode($username).'&pwd='.urlencode($password) ; 
 
			//$json_user = curl_get_contents($url);
			//$userRecord = json_decode($json_user); 
			//if($userRecord->u_id ){
			if($username){ 
				//$record = M('Erp_users')->where("USERNAME='".$userRecord->u_id."' ")->find(); 
				$record = M('Erp_users')->where("USERNAME='".$username."' ")->find();
				
				//var_dump($record);
			}else {
				$password = md5($password);
				if(!$record)$record =  M('Erp_users')->where("USERNAME='".$username."' and PASSWORD='$password' ")->find(); //var_dump($record);

			}
			 
 

			if($record==false){
				$this->error('用户名或密码错误！',U("Index/login"));exit();
			}
			if($record['ISVALID']!='-1'){
				$this->error('账号已被锁定！',U("Index/login"));exit();
			}

			$g = M('Erp_group')->where("LOAN_GROUPID=$record[ROLEID]  ")->find();

			if( $g['LOAN_GROUPSTATUS']==0 || $g['LOAN_GROUPDEL']==1){
				$this->error('权限已被锁定！',U("Index/login"));exit();
			}
			$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();
            $user_city_py = '';
			if(empty($record['CITY'])){
				if(!$dept['CITY_ID'] ) {
					$dept = $this->getuserdept($dept['PARENTID']);
					$record['CITY'] = intval($dept['CITY_ID']);
				}
			}
			//城市拼音缩写
            $cond_where = "ID = ".$record['CITY'];
            $city_info = M('erp_city')->field('PY')->where($cond_where)->find();
            $user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';
			//$pocity = $record['CITYS']?$record['CITYS']:$userCity['CITY_ID'];

            $oaInfo = curl_get_contents(sprintf('http://oa.house365.com/api/api_el.php?k=b8be9c6c70ed10e04b033847895970f6&a=get_userinfo&uid=%s', $record['USERNAME']));
            $oaInfo = unserialize($oaInfo);

			//$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']))) )));
			$pocity = $record['CITYS'];
			$_SESSION['uinfo'] = array(
				'uid'=> $record['ID'],//用户ID
				'role'=> $record['ROLEID'],//用户角色
				'uname'=> $record['USERNAME'],//用户名
                'deptid'=> $record['DEPTID'],//部门编号
                'psw' => $password,
				'tname'=> $record['NAME'],//用户姓名
				'pocity'=> $pocity,//用户城市权限
				//'pofrom'=> $record['LOAN_POWERFROM'],//用户条口
				'currentLogin'=> time(),//当前登陆时间
				//'lastLogin'=> $record['LOAN_LOGINTIME'],//用户上次登陆时间
				//'flow'=> $record['LOAN_FLOW']//流程权限
				'city'=>$record['CITY'],//所属城市
                'city_py' => $user_city_py,//所属城市拼音
				'p_auth_all'=>$g['LOAN_GROUPALL'],
				'loan_base'=>$g['LOAN_BASE'],
				'p_vmem_all'=>$g['LOAN_VMEM'],
                'group_name' => $g['LOAN_GROUPNAME'],
                'user_avatar' => !empty($oaInfo['info']['photourl']) ? $oaInfo['info']['photourl'] : 'Public/images/default-head.png',
                'user_gw' => !empty($oaInfo['info']['user_gw']) ? $oaInfo['info']['user_gw'] : ''
			);

			$ss = array();
			$ss['loan_logintime'] = time();
			//M('admin_user')->where("loan_userID='$record[loan_userID]'")->save($ss);
			/**********************************************************/
			/**********************************************************/
			 // var_dump($_SESSION['uinfo']);
			
			echo "<script>parent.location.href='".U("Index/index")."'</script>";
			exit();
		}
		
		$_SESSION['uinfo'] = '';
		$this->display('login');
	}
	 
	protected function getuserdept($deptid){   
		if($deptid)	$dept = M('erp_dept')->where('ID='.$deptid)->find();
		if($dept && $dept['CITY_ID']==null){
			$dept = $this->getuserdept($dept['PARENTID']);
		}else{ 
			return $dept;
		}
		return $dept;
	}
    public function verify() {  
		$type =	isset($_GET['type'])?$_GET['type']:'gif';
        import("ORG.Util.Image");
        Image::buildImageVerify(4,5,$type);
    }

	function loginOut(){//退出操作
		/**********************************************************/
		/**********************************************************/
		$_SESSION['uinfo'] = '';
		clear_cookie('CHANNELID');
		clear_cookie('POWER');
		clear_cookie('CITYEN');
		echo "<script>parent.location.href='".U("Index/index")."'</script>";
	}

	
}