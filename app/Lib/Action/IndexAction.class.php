<?php
  class IndexAction extends ExtendAction {

	public function _initialize(){
		 
		parent::_initialize();

	}

    public function index(){
		$this->display('Index:index');
    }
    
    public function top(){   
		$powercity = explode(',',$this->powercity);
		//$allpower  = explode(',',$this->allpower);
		//$this->assign('lastlogin',$_SESSION['uinfo']['lastLogin']);
		$this->assign('cityname',$this->city_config);
		//$this->assign('powername',C('power_come_from'));
		$this->assign('powercitynums',count($powercity));//ӵ�е����г���Ȩ����
		$this->assign('powercity',$powercity);//ӵ�е����г���Ȩ��
		$this->assign('channelid',$this->channelid);//��ǰ����
	
		//$this->assign('allpower',$allpower);//��������
		$this->assign('power',$this->power);
		
    	$this->display('Index:top');	
    }

    protected function menu(){

		$groupid = $_SESSION['uinfo']['role'];//�û�Ȩ����
		$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();
		$groupval = $group['LOAN_GROUPVAL'];
		if(empty($groupval)) return;//û��Ȩ��ֵŶ 
		$groupval = explode(',',$groupval);
		
		$parent = M('erp_role')->where("LOAN_ROLEPARENTID=0 and LOAN_ROLEDISPLAY=0 and LOAN_MENUSHOW=-1")->order('LOAN_ROLEORDER asc')->select();//���˵�
	
		if(is_array($parent)){
			foreach($parent as $p=>$pmenu){
				$parentid = $pmenu['LOAN_ROLEID'];
				$smenu = M('erp_role')->where("LOAN_ROLEPARENTID='$parentid' and LOAN_ROLEDISPLAY=0  and LOAN_MENUSHOW=-1 ")->order('LOAN_ROLEORDER asc')->select();//�����˵�
               
			
				if(is_array($smenu)){
					foreach($smenu as $v=>$pulate){
						$tempp = explode(';',$pulate['LOAN_PARAM']);
						$pulate['param'] = implode('&',$tempp);
						if(in_array($pulate['LOAN_ROLEID'],$groupval)) $menu[$p]['smenu'][] = $pulate;//�����˵�
					}
					if(is_array($menu[$p]['smenu'])){
						
						$menu[$p]['name'] = $pmenu['LOAN_ROLENAME'];//���˵�
						$menu[$p]['module'] = ($pmenu['LOAN_ROLECONTROL'] && $pmenu['LOAN_ROLEACTION']) ? 
                            U($pmenu['LOAN_ROLECONTROL'].'/'.$pmenu['LOAN_ROLEACTION']) : '' ;
					}
				}
			}			
		}
		return $menu;
	}

	

    public function left(){//�����Ŀ����
		$menu = $this->menu(); 
		$this->assign('menu',$menu);
     	$this->display('Index:left');   	
    }
    
    public function welcome(){
		$this->assign('action',U('System/desktop'));
    	$this->assign('test','cx');
      	$this->display('Index:welcome');   	
    }

	public function login(){//�û���½��֤

		$act = $this->_post('act');
		if($act=='login'){
			$username = $this->_post('uname');
			$password = $this->_post('psw');
			$imgcode  = md5($this->_post('postcode'));
			$vertify = $_SESSION['verify'];
			$_SESSION['verify'] = '';

            //echo $this->_post('postcode');
            //echo "<br>";
           // echo $vertify;
           
           /*

			if($vertify!=$imgcode || $vertify==''){
				$this->error('��֤�����',U("Index/login"));exit();
			}
			*/
			
			//$password = md5($password);
			//$json_user = curl_get_contents("http://oa.house365.com/api/api_prj.php?a=login&uid=$username&pwd=$password");
			//$userRecord = json_decode($json_user); 
			//if($userRecord->u_id ){
			if($username){
				//$record = M('Erp_users')->where("USERNAME='".$userRecord->u_id."' ")->find();
				$record = M('Erp_users')->where("USERNAME='".$username."' ")->find();
			}
			//var_dump($record);
 

			if($record==false){
				$this->error('�û������������',U("Index/login"));exit();
			}
			if($record['ISVALID']!='-1'){
				$this->error('�˺��ѱ�������',U("Index/login"));exit();
			}

			$g = M('erp_group')->where("LOAN_GROUPID=$record[USERGROUP] and LOAN_GROUPSTATUS=0 or LOAN_GROUPDEL=1")->find();

			if(is_array($g)){
				$this->error('Ȩ���ѱ�������',U("Index/login"));exit();
			}
			$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();
            $user_city_py = '';
			if(!$dept['CITY_ID'] ) {
				$dept = $this->getuserdept($dept['PARENTID']);
                //����ƴ����д
                $cond_where = "ID = ".intval($dept['CITY_ID']);
                $city_info = M('erp_city')->field('PY')->where($cond_where)->find();
                $user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';
			}
			//$pocity = $record['CITYS']?$record['CITYS']:$userCity['CITY_ID'];
			 
			 
			$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']))) )));
			$_SESSION['uinfo'] = array(
				'uid'=> $record['ID'],//�û�ID
				'role'=> $record['ROLEID'],//�û���ɫ
				'uname'=> $record['USERNAME'],//�û���
                'deptid'=> $record['DEPTID'],//���ű��
                'psw' => $password,
				'tname'=> $record['NAME'],//�û�����
				'pocity'=> $pocity,//�û�����Ȩ��
				//'pofrom'=> $record['LOAN_POWERFROM'],//�û�����
				'currentLogin'=> time(),//��ǰ��½ʱ��
				//'lastLogin'=> $record['LOAN_LOGINTIME'],//�û��ϴε�½ʱ��
				//'flow'=> $record['LOAN_FLOW']//����Ȩ��
				'city'=>$dept['CITY_ID'],//��������
                'city_py' => $user_city_py,//��������ƴ��
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
        Image::buildImageVerify(4,1,$type);
    }

	function loginOut(){//�˳�����
		/**********************************************************/
		/**********************************************************/
		$_SESSION['uinfo'] = '';
		clear_cookie(CHANNELID);
		clear_cookie(POWER);
		clear_cookie(CITYEN);
		echo "<script>parent.location.href='".U("Index/index")."'</script>";
	}

	
}