<?php
  class MallAction extends ExtendAction {
    
     public function index(){
        
        header("Location:http://192.168.105.28:8181/house365-taofanghui/php/login?userName=".$_SESSION['uinfo']['uname']."&userPassword=".$_SESSION['uinfo']['psw']."&cityId=".$this->city."&sysCode=jia");
     
     }
     
     public function pai(){
        header("Location:http://192.168.105.28:8182/house365-taofangpai/php/login?userName=".$_SESSION['uinfo']['uname']."&userPassword=".$_SESSION['uinfo']['psw']."&cityId=".$this->city."&sysCode=jia");
     }
     public function api_check(){
        $username =  $this->_get("username");
        $password = $this->_get("psw");
        $city = $this->_get("powerCity");
        $city_config_array = C("city_config_array");
       
        $city = array_search($city,$city_config_array);
        $record = M('admin_user')->where("loan_userName='$username' and loan_userPwd='$password'")->find();
        
		if($record==false){
		  
			$this->ajaxReturn("",'用户名或密码错误！',0);exit();
		}
		if($record['loan_lock']){
		  
			$this->ajaxReturn("",'账号已被锁定！',0);exit();
		}
        
		$g = M('admin_group')->where("loan_groupID=$record[loan_userGroup] and loan_groupStatus=1 and loan_groupDel=0")->find();
        
         
		if(!is_array($g)){
		  
			$this->ajaxReturn("",'权限已被锁定！',0);exit();
		}
        $_SESSION['uinfo'] = array(
			'uid'=> $record['loan_userID'],//用户ID
			'role'=> $record['loan_userGroup'],//用户角色
			'uname'=> $record['loan_userName'],//用户名
			'tname'=> $record['loan_trueName'],//用户姓名
			'pocity'=> $record['loan_powerCity'],//用户城市权限
			'pofrom'=> $record['loan_powerFrom'],//用户条口
			'currentLogin'=> time(),//当前登陆时间
			'lastLogin'=> $record['loan_logintime']//用户上次登陆时间
		);
        $cityval = explode(',',$record['loan_powerCity']);
       
     
        if(!in_array($city,$cityval)){
            
				$this->ajaxReturn("",'没有权限',0);exit();
		}	
        $status = $this->roleAuth("Mall","index");
        if($status){
            $this->ajaxReturn("",'验证通过',1);exit();
        }
            
        $this->ajaxReturn("",'没有权限',0);exit();
    }
  }
?>