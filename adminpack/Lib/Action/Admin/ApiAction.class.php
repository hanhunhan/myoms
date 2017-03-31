<?php
	class ApiAction extends Action{

		static public $propertyApi = "http://newapi.house365.com/projects";
		static public $houselistApi = "http://newapi.house365.com/projects/name";
		//static public $contractApi = "http://172.17.1.8:81/365tongji_beta/admin/api/is_ct_back.php";
		//static public $incomeApi = "http://172.17.1.8:81/365tongji_beta/admin/api/get_ct_info.php";

		//获取关联楼盘
		static public function getHouselist(){
			
			$cityId = intval($_REQUEST['city']);
            
            if( $cityId > 0)
            {
                $record = M("Erp_city")->find($cityId);
                $city = strtolower($record['PY']);
            }
            else
            {
                $city = $_SESSION['uinfo']['city_py'];
            }
            
			$search = $_REQUEST['search'];//iconv('UTF-8','GBK',$_REQUEST['q']);
			
			$str = file_get_contents(self::$houselistApi."?city={$city}&limit=10&name={$search}&like=1");
			
			$result = array();//去重
			if($str)
			{	
				$data = json_decode($str,true);
				
				foreach($data['data']['list'] as $key=>$val){
					$result[$val["prj_id"]] = $val;
				}
			}

			echo json_encode($result);exit;
		}
        
		//获取关联项目属性
		static public function getHouseProperty(){
			$cityId = $_REQUEST['city'];
			$record = M("Erp_city")->find($cityId);
			$city = strtolower($record['PY']);
			$houseid = $_REQUEST['search'];
			$channel = $_REQUEST['channel'];

			$str = curl_get_contents(self::$propertyApi."?city={$city}&prjid={$houseid}&channel={$channel}",'get');
		
			echo $str;exit;
		}

		//获取合同是否收回
		static public function getIsContract(){
			
			$cityId = $_REQUEST['cityId'];

			$record = M("Erp_city")->find($cityId);
			$city = strtolower($record['PY']);
			
			$contractNum = $_REQUEST['contractNum'];
			//获取非付现成本的业务类型
			$advert = M("Erp_project")->where("CONTRACT = "."'{$contractNum}' and ASTATUS=2")->getField("ASTATUS");
			if($advert){
				$sql = "select c.scaletype  from ERP_project p left join erp_case c on p.id = c.project_id  where p.contract =". "'{$contractNum}'" . "  and p.ASTATUS=2 and p.status != 2 and city_id =".$cityId;
			}else{
				  $sql = "select c.scaletype  from ERP_project p left join erp_case c on p.id = c.project_id  where p.contract =". "'{$contractNum}'" . "  and p.PSTATUS =3 and p.status != 2 and city_id =".$cityId;
			}

			$res = M()->query($sql);
			//$str = curl_get_contents(CONTRACTAPI."?city={$city}&contractnum={$contractNum}",'get');
			//$str = json_decode($str,TRUE);
			if($res){
				$str['data'] = $res;
				$str['msg'] = g2u("合同存在与于系统中并且已立项");
				$str['status'] = true;
			}else{
				$str['msg'] = g2u("亲，合同在系统中没有立项通过");
				$str['status'] = false;
			}
			echo json_encode($str);exit;
		}
		//获取合同是否被相同业务类型使用
		static public function getIsContract2(){
			
			$cityId = $_REQUEST['cityId'];
			$projectId = $_REQUEST['projectId'];
			$record = M("Erp_city")->find($cityId);
			$city = strtolower($record['PY']);
			$contractNum = $_REQUEST['contractNum'];
			//$sql = "select b.CONTRACT_NUM  as CONTRACT from ERP_CASE a left join ERP_HOUSE b on a.PROJECT_ID=b.PROJECT_ID where a.SCALETYPE in (select SCALETYPE from ERP_CASE where PROJECT_ID='$projectId') and b.CONTRACT_NUM='$contractNum' and A.FSTATUS<>7 and A.FSTATUS<>5 ";
			$sql = "select b.CONTRACT_NUM  as CONTRACT from ERP_CASE a left join ERP_HOUSE b on a.PROJECT_ID=b.PROJECT_ID left join ERP_PROJECT c on a.PROJECT_ID=c.ID where a.SCALETYPE in (select SCALETYPE from ERP_CASE where PROJECT_ID='$projectId') and b.CONTRACT_NUM='$contractNum' and A.FSTATUS<>7 and c.PSTATUS<>5 and c.CITY_ID='$cityId' ";
			$res = M()->query($sql); 
			if($res){
				$result['status'] = 0; 
				$result['msg'] = g2u('该合同号已经被相同业务类型使用！');
				$str = json_encode($result);
				//$str = '该合同号已经被相同业务类型使用！';
			}else{
				$str = curl_get_contents(CONTRACTAPI."?city={$city}&contractnum={$contractNum}",'get');
				if(is_null(json_decode($str)) ) {
					$result['status'] = 0;
					$result['msg'] = g2u($str );
					$str = json_encode($result);
				}
			}
			echo $str;exit;
		}
		//根据合同号获取合同数据

		static public function get_Income_Contract(){
			$cityId = $_REQUEST['cityId'];
			$cityInfo = M("Erp_city")->find($cityId);
			$city = $cityInfo['PY'];
			$contractNum = trim($_REQUEST['contractNum']);
			$prjid = $_REQUEST['prjid'];

			$cases = M('Erp_case')->field("id,project_id")->where("project_id=".$prjid)->select();
			
			$Income = curl_get_contents(INCOMEAPI."?city={$city}&contractnum={$contractNum}&action=",'get');
			$Income = unserialize($Income);
			if($Income && is_array($Income)){
				foreach($cases as $case){
					$data = array();
					$data['CONTRACT_NO'] = $contractNum;
					$data['COMPANY'] = $Income['contunit'];
					$data['START_TIME'] = date('Y-m-d H:i:s',$Income['contbegintime']);
					$data['END_TIME'] = date('Y-m-d H:i:s',$Income['contendtime']);
					$data['PUB_TIME'] =$Income['pubdate'];
					$data['STATUS'] = $Income['step'];
					$data['MONEY'] = $Income['contmoney'];
					$data['ADD_TIME'] = date('Y-m-d H:i:s');
					$data['CONTRACT_TYPE'] = $Income['type'];
					$data['SIGN_USER'] = $Income['addman'];
					
					$Income_contract = M('Erp_income_contract')->where("CASE_ID=".$case['ID']." AND CITY_PY='{$city}' AND CITY_ID = $cityId")->find();
					if(!$Income_contract){//新增
						$data['CASE_ID'] = $case['ID'];
						$data['CITY_PY'] = $city;
						$data['CITY_ID'] = $cityId;

						$add = M('Erp_income_contract')->add($data);
					}else{//编辑
						$update= M('Erp_income_contract')->where("CASE_ID=".$case['ID']." AND CITY_PY='{$city}'")->save($data);
					}
				}
			}
			exit(true);
			
		}

		//获取代办工作流数据
		function get_Wait_Workflow()
		{
			$model = new Model();
			$username = $_REQUEST['USERNAME']?$_REQUEST['USERNAME']:'';

			//特殊逻辑 史连生==陈林艳
			if($username == 'shiliansheng2')
				$username = 'chenlinyan';

			if($username)
			{
				$user = M("Erp_users")->where("USERNAME = '{$username}'")->find();
				
				if($user){
					$user_Id = $user['ID'];

					$sql = "SELECT F.ID,F.INFO,F.MAXSTEP,to_char(F.ADDTIME,'YYYY-MM-DD') AS			   ADDTIME,F.FLOWTYPE,F.NAME,F.DEPTNAME
						   FROM 
						   (
						    SELECT B.*,C.FLOWTYPE,E.NAME,X.DEPTNAME FROM ERP_FLOWSET A
							LEFT JOIN ERP_FLOWS B ON A.ID= B.FLOWSETID 
							LEFT JOIN ERP_FLOWTYPE C ON A.FLOWTYPE = C.ID 
							LEFT JOIN ERP_USERS E ON E.ID=B.ADDUSER 
							LEFT JOIN ERP_DEPT X ON E.DEPTID = X.ID
						   ) F 
						   INNER JOIN ERP_FLOWNODE G ON F.ID = G.FLOWID 
						   WHERE (F.STATUS=1 OR F.STATUS=2) AND (G.STATUS = 1  OR G.STATUS=2) and G.DEAL_USERID = {$user_Id} ORDER BY ADDTIME DESC";
					
					$result['data'] = $model->query($sql);
					$result['msg'] = '';
					$result['result'] = 1;
					$result['url'] = '/tpp/adminpack/index.php?s=/Flow/workStep';
				}
				else
				{
					$result['data'] = array();
					$result['msg'] = '用户名不存在';
					$result['result'] = 0;
					$result['url'] = '';
				}
			}
			else
			{
				$result['data'] = array();
				$result['msg'] = '所传参数为空';
				$result['result'] = 0;
				$result['url'] = '';
			}

			echo json_encode(g2u($result));exit;
		}

		function check_User_Exist()// 用户管理编辑或新增时验证用户是否已存在
		{
			$username = strip_tags(trim($_REQUEST['USERNAME']));
			$id = intval($_REQUEST['ID']);

			if($id)
			{
				$condition = "USERNAME='{$username}' and ID !={$id}";
			}
			else
			{
				$condition = "USERNAME='{$username}'";
			}
			$check = M("Erp_users")->where($condition)->find();

			if($check){
				$result = array(
							'status'=>'n',
							'info'=>g2u("用户名已存在！")
						);
			}
			else
			{
				$result = array(
							'status'=>'y',
							'info'=>''
						);
			}

			echo json_encode($result); exit();
		}
        // 判断流程是否可以收回
		
		function judge_Recover_Flow()
		{
			$flowId = intval($_REQUEST["flowId"]);
			
			$flowNode = M("Erp_flownode")->where("FLOWID = {$flowId} AND STATUS = 1")->select();
			
			if($flowNode)
			{
				$result = array('data' => 1,'msg'=>'yes');
			}
			else
			{
				
				$result = array('data' => 0,'msg'=>'fail');
			}

			echo json_encode($result);exit;

		}

        //获取已有项目名称
		static public function getProjectName()
        {
			$search = $_REQUEST['term']; 
			
			$str = M('Erp_house')->field("pro_name label,pro_name value")->where("pro_name like '%{$search}%'")->select();
			
			$data = array();
			if($str){
				foreach($str as $key=>$val){
					$data[$key]['label'] = g2u($val['LABEL']);
					$data[$key]['value'] = g2u($val['VALUE']);
				}
			}
			echo json_encode($data);exit;
		}
        
        //获取已有活动ID和活动名称
        static public function getActiveName(){
            $search = $_REQUEST["search"];
            $str = M("Erp_activities")->field("ID","TITLE")->where("TITLE like '%{$search}%'")->select();
            echo json_encode($str);exit;
        }
        
		//获取部门
		static public function getAuth(){
			$dept = M('Erp_dept')->query("select * from erp_dept where isvalid = -1 start with  id=1 connect by prior id = parentid order siblings by parentid asc");
			
			$str = array();
			foreach($dept as $key=>$val){
				$str[$key]['id'] = $val['ID'];
				$str[$key]['pId'] = $val['PARENTID'];
				$str[$key]['name'] = iconv('GBk', 'UTF-8', $val['DEPTNAME']);
			}
			//print_r($str);exit;
			echo json_encode($str);exit;
		}
        
		//获取流程转交人员
        function getFlowPeople(){
		
           $model = new Model();
           $term = $_REQUEST['search'] ? $_REQUEST['search'] : $_REQUEST['q'];
           $myself = $_SESSION['uinfo']['uid'];
		   $roleId = $_REQUEST['roleId'];
		   $city = $_SESSION['uinfo']['city'];
		   if($roleId){
			  $sql = "select a.id,a.name,a.phone,b.deptname,c.py as city from erp_users a 
					 left join erp_dept b on a.deptid = b.id  
					 left join erp_city c on a.city = c.id
					 where (a.name like '%".iconv('UTF-8','GBk',$term)."%' or a.username like '%".strtolower($term)."%') and a.isvalid = -1 and a.id != $myself and a.roleid = {$roleId} and c.isvalid = -1 and rownum <31";
		   }else{
			  $sql = "select a.id,a.name,a.phone,b.deptname,c.py as city from erp_users a 
					 left join erp_dept b on a.deptid = b.id 
					 left join erp_city c on a.city = c.id
					  where (a.name like '%".iconv('UTF-8','GBk',$term)."%' or a.username like '%".strtolower($term)."%') and a.isvalid = -1 and a.id != $myself  and c.isvalid = -1 and rownum <31";
		   }
           
           $data = $model->query($sql);
           $str = array();
           if($data){
               foreach($data as $key=>$val){
                   $str[$key]['id'] = $val['ID'];
                   $str[$key]['name'] = $val['DEPTNAME']? iconv('GBk','UTF-8',$val['NAME']."[".$val['DEPTNAME']."]"):iconv('GBk','UTF-8',$val['NAME']);
                   $str[$key]['phone'] = $val['PHONE'];
				   $str[$key]['city'] = $val['CITY'];
                   //$str[$key]['city'] = strtolower($val['PY']);
               }
           }
           echo json_encode($str);
           exit;
        }

		//获取工作流分组
		function getFlowGroup(){
			$myself = $_SESSION['uinfo']['uid'];
 			$model = new Model();
			$term = $_REQUEST['search'] ? $_REQUEST['search'] : $_REQUEST['q'];
			$sql = "select a.id,a.groupname  from erp_group_flow a
				  where (a.groupname like '%".iconv('UTF-8','GBk',$term)."%') and a.userid = $myself and rownum <31";

			$data = $model->query($sql);
			$str = array();
			if($data){
				foreach($data as $key=>$val){
					$str[$key]['id'] = $val['ID'];
					$str[$key]['groupname'] = g2u($val['GROUPNAME']);
					//$str[$key]['groupuserid'] = $val['GROUPUSERID'];
				}
			}
			echo json_encode($str);
			exit;
		}
		//获取直销人员
		function getDirectSaller(){
			$model = new Model();
			$term = $_REQUEST['search'] ? $_REQUEST['search'] : $_REQUEST['q'];
			$city = $_SESSION['uinfo']['city'];
			$term  = u2g(urldecode($term));
			$sql = "select a.id,a.name,a.phone,b.deptname,c.py as city from erp_users a
				 left join erp_dept b on a.deptid = b.id
				 left join erp_city c on a.city = c.id
				 where (a.name like '%".$term."%' or a.username like '%".strtolower($term)."%') and a.isvalid = -1 and  c.isvalid = -1 and rownum <31";
			$data = $model->query($sql);
			$str = array();
			if($data){
				foreach($data as $key=>$val){
					$str[$key]['id'] = $val['ID'];
					$str[$key]['deptname'] = $val['DEPTNAME']? iconv('GBk','UTF-8',$val['NAME']."[".$val['DEPTNAME']."]"):iconv('GBk','UTF-8',$val['NAME']);
					$str[$key]['phone'] = $val['PHONE'];
					$str[$key]['city'] = $val['CITY'];
					$str[$key]['name'] = iconv('GBk','UTF-8',$val['NAME']);
				}
			}
			echo json_encode($str);
			exit;
		}
		//获取固定流人员
		function Choose_Fixed_User()
		{
			$roleId = $_REQUEST['roleId'];

			$roleName = M("Erp_group")
						->where("LOAN_GROUPID = {$roleId}")
						->getField("LOAN_GROUPNAME");
					    
			$userLists = M("Erp_users")
						->alias("a")
						->field("a.id,a.name,b.name as city")
						->join("erp_city b on a.city=b.id")
						->where("b.isvalid = -1 and a.isvalid = -1 and a.roleid = $roleId")
						->select();

			$result['roleName'] = g2u($roleName);
			$result['users'] = g2u($userLists);
			
			echo json_encode($result);exit;
		}

		//根据部门获取人员
		static public function getUsers(){
			$deptId = $_REQUEST['id'];
			$users = M('Erp_users')->field("id,name")->where("deptid ={$deptId} and isvalid = -1 ")->select();
			
			$str = array();
			if($users){
				foreach($users as $key=>$val){
					$str[$key]['id'] = $val['ID'];
					$str[$key]['name'] = iconv('GBk','UTF-8',$val['NAME']);
				}
			}
			
			echo json_encode($str);exit;
		}
        
        
		//根据人员获取已有的业务类型
		function getSelectType(){
			$uid = $_REQUEST['uid'];
			$projId = $_REQUEST['projId'];
			$record = M('Erp_prorole')->field("id,erp_id")->where("use_id ={$uid} and pro_id = {$projId} and isvalid = -1 ")->select();
			
			echo json_encode($record);exit;
		}
        
		// 驻场根据人员获取已选择项目
		function getChooseProj(){
			$uid = $_REQUEST['id'];
			$type = 1;
			$model = new Model();
			$record = $model->query("select a.id,a.pro_id,b.projectname from erp_prorole a left join erp_project b on a.pro_id = b.id where a.isvalid = -1 and a.use_id = {$uid} and erp_id = {$type}");
			
			$str = array();
			if($record){
				foreach($record as $key=>$val){
					$str[$key]['id'] = $val['ID'];
					$str[$key]['pro_id'] = $val['PRO_ID'];
					$str[$key]['projectname'] = iconv('GBk','UTF-8',$val['PROJECTNAME']);
				}
			}
            
			echo json_encode($str);exit;
		}
        
        
		// 驻场根据项目获取已选择人员
		function getChooseUser(){
			$pid = $_REQUEST['id'];
			$type = 1;
			
			$record = M('Erp_prorole')->alias('a')->field('a.id,a.use_id,b.name')->join('erp_users b ON a.use_id = b.id')->where("a.isvalid = -1 and a.pro_id = {$pid} and erp_id = {$type}")->select();
			
			$str = array();
			if($record){
				foreach($record as $key=>$val){
					$str[$key]['id'] = $val['ID'];
					$str[$key]['use_id'] = $val['USE_ID'];
					$str[$key]['name'] = iconv('GBk','UTF-8',$val['NAME']);
				}
			}

			echo json_encode($str);exit;
		}

		// 驻场权限 项目选择 城市 正在进行的电商
		function get_Auth_Projects(){
			//调取基类的是p_auth_all属性
			$auth_all = $_SESSION['uinfo']['p_auth_all'];
			$city = $_SESSION['uinfo']['city'];
			$user = $_SESSION['uinfo']['uid'];

			$keyword = u2g($_REQUEST['keyword']);//搜索关键词
			$Model = new Model();

			if($auth_all){
				$SQL = "SELECT ID,PROJECTNAME FROM ERP_PROJECT 
						WHERE (BSTATUS =2 OR BSTATUS =4) AND PSTATUS = 3 AND STATUS != 2 AND CITY_ID = {$city} AND PROJECTNAME LIKE '%{$keyword}%'";
			}else{
				$SQL = "SELECT ID,PROJECTNAME FROM ERP_PROJECT 
						WHERE ID IN(SELECT DISTINCT PRO_ID FROM ERP_PROROLE WHERE ISVALID = -1 AND PSTATUS = 3 AND STATUS != 2 AND USE_ID = {$user} AND ERP_ID = 1 ) AND (BSTATUS =2 OR BSTATUS =4) AND CITY_ID = {$city} AND PROJECTNAME LIKE '%{$keyword}%'";
			}
			$projects = $Model->query($SQL);
			die(json_encode(g2u($projects)));
		}

		//项目权限 获取人员
		function getUserByType(){
			$prjId = $_REQUEST['prjId'];
			$erpId = $_REQUEST['erpId'];

			$record = M('Erp_prorole')->alias('a')->field('a.id,a.use_id,b.name')->join('erp_users b ON a.use_id = b.id')->where("a.isvalid = -1 and a.pro_id = {$prjId} and erp_id = {$erpId}")->select();
			
			$str = array();
			if($record){
				foreach($record as $key=>$val){
					$str[$key]['id'] = $val['ID'];
					$str[$key]['use_id'] = $val['USE_ID'];
					$str[$key]['name'] = iconv('GBk','UTF-8',$val['NAME']);
				}
			}

			echo json_encode($str);exit;
		}

		// 项目权限 搜索获取人员
		function getSearchUser(){
			$model = new Model();
			$search = $_REQUEST['term']; 
			$sql = "select a.id,a.name,a.phone,b.deptname from erp_users a left join erp_dept b on a.deptid = b.id  where (a.name like '%".iconv('UTF-8','GBk',$search)."%' or a.username like '%".strtolower($search)."%') and a.isvalid = -1 and rownum <16";
			$record = $model->query($sql);
			//$record = M('Erp_users')->field("id,name")->where("name like '%".iconv('UTF-8','GBk',$search)."%' or username like '%".$search."%' or  username like '%".strtolower($search)."%'")->limit(10)->select();

			$str = array();
			if($record){
				foreach($record as $key=>$val){
					$str[$key]['id'] = $val['ID'];
					$str[$key]['label'] = $val['DEPTNAME']?iconv('GBk','UTF-8',$val['NAME']."[".$val['DEPTNAME']."]"):iconv('GBk','UTF-8',$val['NAME']);
					$str[$key]['value'] = iconv('GBk','UTF-8',$val['NAME']);
				}
			}

			echo json_encode($str);exit;

		}

		//判断销售方式是否存在

		public function getSaleMethod(){
			$projectId = $_REQUEST['projectId'];
			$methodId = $_REQUEST['methodId']; 

			$record = M('Erp_budgetsale')->where("projectt_id = {$projectId} and salemethodid = {$methodId}")->select();
			
			if($record){
				$result= array('status'=>1);
			}else{
				$result = array('status'=>0);
			}

			echo json_encode($result);exit;
		}
        
		public function getFlowType(){
			$flowId = $_REQUEST['flowId'];
			$sql = "select a.caseid,a.recordid,a.activid,c.pinyin,a.city from erp_flows a left join erp_flowset b on a.flowsetid = b.id left join erp_flowtype c on b.flowtype= c.id where a.id = {$flowId }";
			
			$model = new Model();
			$record = $model->query($sql);
			
			$powercity = explode(',',$_SESSION['uinfo']['pocity']);
			if(!in_array($record[0]['CITY'],$powercity) ){
				$record['nopower'] = 1;
                if (empty($_SESSION['uinfo'])) {
                    $record['status'] = 'not_login';
                }
			}
				
			 
			echo json_encode($record);
            exit;
		}

		//获取配置开始角色

		public function getFlowStartRole(){
			$id = $_REQUEST['FLOWSETID'];
			
			$role = M('Erp_flowset')->field("id,flowstart")->where("id = {$id}")->find();
			
			echo json_encode($role);
            exit;
		
		}
		//获取角色

		public function getRole(){
			$roleList = M("Erp_group")->field("loan_groupid as ID,loan_groupname as ROLENAME")->select();
			
			$data = array();
			if($roleList){
				foreach($roleList as $key=>$val){
					$data[$key]['ID'] = $val['ID'];
					$data[$key]['ROLENAME'] = iconv('GBk','UTF-8',$val['ROLENAME']);
				}
			}
			echo json_encode($data);
			exit;
		}
        //项目下活动获取case_id
		public function get_Case_id(){
			$activId = $_REQUEST['activId'];
            
			$list = M('Erp_activities')->field('case_id')->where("id=$activId")->find();
            
			echo json_encode($list);exit;
		}
        
		//新增用户 接口
		public function addUser(){ 
			if ( $this->_get('TOKEN') == md5(C('DEFAULTPWD') . $this->_get('TIMESTAMP') ) ){
				$user = D("Erp_users");
				$data = array(); 
				if($this->_get('DEPTID') && $this->_get('NAME') && $this->_get('USERNAME') && $this->_get('TITLE') ){
					if(!$one = $user->where("USERNAME='".$this->_get('USERNAME')."'")->find() ){
						$data['DEPTID'] = $this->_get('DEPTID');
						$data['NAME'] = $this->_get('NAME');
						$data['USERNAME'] = $this->_get('USERNAME');
						$data['TITLE'] = $this->_get('TITLE');
						$data['PHONE'] = $this->_get('PHONE');
						$data['ISVALID'] =-1;
						$dept = M('erp_dept')->where('ID='.$this->_get('DEPTID'))->find();
						if(!$dept['CITY_ID'] ) {
							$dept = $this->getuserdept($dept['PARENTID']);
							$city = intval($dept['CITY_ID']);
						}else $city = $dept['CITY_ID'];
						$data['CITYS'] = $city;
					    $data['CITY'] = $city;
						if( $user->add($data) ){
							$res['result'] = 1;
							$res['msg'] = 'success';
							//file_put_contents('api_log.txt','addUser:'.serialize($data).PHP_EOL.'\r\n\n\r',FILE_APPEND);
						}else{ 
							$res['result'] = 0;
							$res['msg'] = 'fail ';
						}
					}else{
						$res['result'] = 0;
						$res['msg'] = 'error -user Already exists';
					}
				}else{
					$res['result'] = 0;
					$res['msg'] = 'error - missing field';
				}
			}else{
				$res['result'] = 0;
			    $res['msg'] = 'error - Without permission';
			}
			file_put_contents('api_log.txt','addUser:'.serialize($data)."res:".serialize($res).PHP_EOL.'\r\n\n\r',FILE_APPEND);
			echo json_encode($res);
		}
        
        
		//修改用户 接口
		public function modifyUser(){ //echo urlencode('测试');
			if ( $this->_get('TOKEN') == md5(C('DEFAULTPWD') . $this->_get('TIMESTAMP') ) ){
				$user = D("Erp_users");
				$data = array();
				if( $this->_get('USERNAME')   ){
					
					if($this->_get('NAME'))$data['NAME'] = $this->_get('NAME');
					//$data['USERNAME'] = $this->_get('USERNAME');
					if($this->_get('TITLE'))$data['TITLE'] = $this->_get('TITLE');
					//$old = $user->where()
					if($this->_get('DEPTID')){
						$data['DEPTID'] = $this->_get('DEPTID');
						$dept = M('erp_dept')->where('ID='.$this->_get('DEPTID'))->find();
						if(!$dept['CITY_ID'] ) {
							$dept = $this->getuserdept($dept['PARENTID']);
							$city = intval($dept['CITY_ID']);
						}else $city = $dept['CITY_ID'];
						if($city){
							//$data['CITYS'] = $city;
							//$data['CITY'] = $city;
						}
					}
					if($this->_get('PHONE'))
					{
						$data['PHONE'] = $this->_get('PHONE');
					}

					if( $user->where("USERNAME = '".$this->_get('USERNAME')."'")->save($data) ){
						$res['result'] = 1;
						$res['msg'] = 'success';
						file_put_contents('api_log.txt','modifyUser:'.serialize($data).PHP_EOL.'\r\n\n\r',FILE_APPEND);
					}else{
						$res['result'] = 0;
						$res['msg'] = 'fail';
					}
				}else{
					$res['result'] = 0;
					$res['msg'] = 'error - missing field';
				}
			}else{
				$res['result'] = 0;
			    $res['msg'] = 'error - Without permission';
			}
			file_put_contents('api_log.txt','modifyUser:'.serialize($data).'res:'.serialize($res).PHP_EOL.'\r\n\n\r',FILE_APPEND);
			echo json_encode($res);
		}
        
        
		//禁用删除用户 接口
		public function delUser(){
			if ( $this->_get('TOKEN') == md5(C('DEFAULTPWD') . $this->_get('TIMESTAMP') ) ){
				$user = D("Erp_users");
				$data = array();
				if( $this->_get('USERNAME') ){
					$data['ISVALID'] = 0;

					if( $user->where("USERNAME = '".$this->_get('USERNAME')."'")->save($data) ){
						$res['result'] = 1;
						$res['msg'] = 'success';
						//file_put_contents('api_log.txt','delUser:'.serialize($data).PHP_EOL.'\r\n\n\r',FILE_APPEND);
					}else{
						$res['result'] = 0;
						$res['msg'] = 'fail';
					}
				}else{
					$res['result'] = 0;
					$res['msg'] = 'error - missing field';
				}
			}else{
				$res['result'] = 0;
			    $res['msg'] = 'error - Without permission';
			}
			file_put_contents('api_log.txt','delUser:'.serialize($data).'res:'.serialize($res).PHP_EOL.'\r\n\n\r',FILE_APPEND);
			echo json_encode($res);
		}
		//获取部门城市
		protected function getuserdept($deptid){   
			if($deptid)	$dept = M('erp_dept')->where('ID='.$deptid)->find();
			if($dept && $dept['CITY_ID']==null){
				$dept = $this->getuserdept($dept['PARENTID']);
			}else{ 
				return $dept;
			}
			return $dept;
		}
        
        
		//新增部门 接口
		public function addDepartment(){
			if ( $this->_get('TOKEN') == md5(C('DEFAULTPWD') . $this->_get('TIMESTAMP') ) ){
				$dept = D("Erp_dept");
				$data = array();
				if($this->_get('DEPTNAME') && $this->_get('PARENTID') && $this->_get('DEPTID')  ){
					$citys = M("Erp_city")->where("ISVALID=-1")->select();
					foreach($citys as  $v){  
						if(strstr($this->_get('DEPTNAME'),$v['NAME']) ){
							$data['CITY_ID'] = $v['ID'];
						}
					}  
					$data['DEPTNAME'] = $this->_get('DEPTNAME');
					$data['ID'] = $this->_get('DEPTID');
					$data['PARENTID'] = $this->_get('PARENTID');
					$data['ISVALID'] = -1; 
					if( $dept->add($data) ){
						$res['result'] = 1;
						$res['msg'] = 'success';
						file_put_contents('api_log.txt','addDepartment:'.serialize($data).PHP_EOL.'\r\n\n\r',FILE_APPEND);
					}else{
						$res['result'] = 0;
						$res['msg'] = 'fail';
					}
				}else{
					$res['result'] = 0;
					$res['msg'] = 'error - missing field';
				}
			}else{
				$res['result'] = 0;
			    $res['msg'] = 'error - Without permission';
			}
			echo json_encode($res);
		}
        
        
		//修改部门 接口
		public function modifyDepartment(){
			if ( $this->_get('TOKEN') == md5(C('DEFAULTPWD') . $this->_get('TIMESTAMP') ) ){
				$dept = D("Erp_dept");
				$data = array();
				if(  $this->_get('DEPTID')  ){
					$citys = M("Erp_city")->where("ISVALID=-1")->select();
					foreach($citys as  $v){  
						if(strstr($this->_get('DEPTNAME'),$v['NAME']) ){
							$data['CITY_ID'] = $v['ID'];
						}
					}  

					if($this->_get('DEPTNAME'))$data['DEPTNAME'] = $this->_get('DEPTNAME');
					if($this->_get('PARENTID'))$data['PARENTID'] = $this->_get('PARENTID');

					if( $dept->where("ID='".$this->_get('DEPTID')."'")->save($data) ){
						$res['result'] = 1;
						$res['msg'] = 'success';
						file_put_contents('api_log.txt','modifyDepartment:'.serialize($data).PHP_EOL.'\r\n\n\r',FILE_APPEND);
					}else{
						$res['result'] = 0;
						$res['msg'] = 'fail';
					}
				}else{
					$res['result'] = 0;
					$res['msg'] = 'error - missing field';
				}
			}else{
				$res['result'] = 0;
			    $res['msg'] = 'error - Without permission';
			}
			echo json_encode($res);
		}
        
        
		//禁用删除部门 接口
		public function delDept(){
			if ( $this->_get('TOKEN') == md5(C('DEFAULTPWD') . $this->_get('TIMESTAMP') ) ){
				$user = D("Erp_dept");
				$data = array();
				if( $this->_get('DEPTID') ){
					$data['ISVALID'] = 0;

					if( $user->where("ID = '".$this->_get('DEPTID')."'")->save($data) ){
						$res['result'] = 1;
						$res['msg'] = 'success';
						file_put_contents('api_log.txt','delDept:'.serialize($data).PHP_EOL.'\r\n\n\r',FILE_APPEND);
					}else{
						$res['result'] = 0;
						$res['msg'] = 'fail';
					}
				}else{
					$res['result'] = 0;
					$res['msg'] = 'error - missing field';
				}
			}else{
				$res['result'] = 0;
			    $res['msg'] = 'error - Without permission';
			}
			echo json_encode($res);
		}
        
        //更新收益合同 接口
		public function updateContractStatus ()
        {
			$advert = D("Erp_income_contract");
			$data = array();
            $contract_no = trim($_REQUEST['contract_no']);
            $city = strtoupper(trim($_REQUEST['city']));
            
			if(  $contract_no && $city )
            { 
                if(strip_tags(trim($_REQUEST["status"])))$data['STATUS'] = strip_tags(trim($_REQUEST["status"]));//合同状态
                if(strip_tags(trim($_REQUEST["conf_time"]))) $data['CONF_TIME'] = strip_tags(trim($_REQUEST["conf_time"]));//合同确认时间
                if(strip_tags(trim($_REQUEST["company"]))) $data['COMPANY'] = u2g(strip_tags(trim($_REQUEST["company"])));//合同单位
                if(strip_tags(trim($_REQUEST["start_time"]))) $data['START_TIME'] = strip_tags(trim($_REQUEST["start_time"]));//合同开始时间
                if(strip_tags(trim($_REQUEST["end_time"]))) $data['END_TIME'] = strip_tags(trim($_REQUEST["end_time"]));//合同结束时间
                if(strip_tags(trim($_REQUEST["pub_time"]))) $data['PUB_TIME'] = strip_tags(trim($_REQUEST["pub_time"]));//发布时间
                //合同确认人
                if(strip_tags(trim($_REQUEST["conf_user"]))) $data['CONF_USER'] = strip_tags(trim($_REQUEST["conf_user"]));
                if(strip_tags(trim($_REQUEST["money"]))) $data['MONEY'] = strip_tags(trim($_REQUEST["money"]));//合同金额
                //合同类型
                if(strip_tags(trim($_REQUEST["contract_type"]))) $data['CONTRACT_TYPE'] = strip_tags(trim($_REQUEST["contract_type"]));
                if(strip_tags(trim($_REQUEST["sign_user"]))) $data['SIGN_USER'] = u2g(strip_tags(trim($_REQUEST["sign_user"])));//合同签约人  
                //根据合同号和城市查找是否有该合同
                $income_contract_moel = D("Contract");
                $cond_where = "CONTRACT_NO = $contract_no and CITY_PY = $city";
                $contract_info = $income_contract_moel->get_info_by_cond($cond_where,array("ID"));
                
                if(is_array($contract_info) && !empty($contract_info))
                {
                    $where = "CONTRACT_NO = ".$contract_no." AND CITY_PY = ".$city;      
                    $result = $income_contract_moel->update_info_by_cond($where,$data);   
                }
                //没有找到合同信息 新增一条合同
                else
                {
                    $data['CONTRACT_NO'] = $contract_no;
                    $data['CITY_PY'] = $city;
                    $data['ADD_TIME'] = date("Y-m-d H:i:s");
                    $data['IS_NEED_INVOICE'] = 0;
                    $result = $income_contract_moel-> add_contract_info($data);
                }
                
                if( $result)
                {
                    $res['result'] = 1;
                    $res['msg'] = 'success';
                }
                else
                {
                    $res['result'] = 0;
                    $res['msg'] = 'fail';
                }
                   
            }
            else
            {
                $res['result'] = 0;
                $res['msg'] = 'missing message';
            }
            echo json_encode($res);exit;
				
		}
        
        //新增收益合同 接口
		public function add_contract ()
        {
            $model = new Model();
			$data = array();
            $contract_no = trim($_REQUEST['contract_no']);
            $city = strtoupper(trim($_REQUEST['city']));
            
            //根据合同号和城市查找是否有该合同
            $income_contract_moel = D("Contract");
            $cond_where = "CONTRACT_NO = '$contract_no' and CITY_PY = '$city'";
            $contract_info = $income_contract_moel->get_info_by_cond($cond_where,array("ID"));
            if($contract_info)//更新合同信息
            {
                if(  $contract_no && $city )
                { 
                    if(strip_tags(trim($_REQUEST["status"]))) $data['STATUS'] = strip_tags(trim($_REQUEST["status"]));//合同状态
                    if(strip_tags(trim($_REQUEST["conf_time"])))$data['CONF_TIME'] = strip_tags(trim($_REQUEST["conf_time"]));//合同确认时间
                    if(strip_tags(trim($_REQUEST["company"])))$data['COMPANY'] = u2g(strip_tags(trim($_REQUEST["company"])));//合同单位
                    if(strip_tags(trim($_REQUEST["start_time"])))$data['START_TIME'] = strip_tags(trim($_REQUEST["start_time"]));//合同开始时间
                    if(strip_tags(trim($_REQUEST["end_time"])))$data['END_TIME'] = strip_tags(trim($_REQUEST["end_time"]));//合同结束时间
                    if(strip_tags(trim($_REQUEST["pub_time"])))$data['PUB_TIME'] = strip_tags(trim($_REQUEST["pub_time"]));//发布时间
                    //合同确认人
                    if(strip_tags(trim($_REQUEST["conf_user"])))$data['CONF_USER'] = strip_tags(trim($_REQUEST["conf_user"]));
                    if(strip_tags(trim($_REQUEST["money"])))$data['MONEY'] = strip_tags(trim($_REQUEST["money"]));//合同金额
                    //合同类型
                    if(strip_tags(trim($_REQUEST["contract_type"])))$data['CONTRACT_TYPE'] = strip_tags(trim($_REQUEST["contract_type"]));
                    if(strip_tags(trim($_REQUEST["sign_user"])))$data['SIGN_USER'] = u2g(strip_tags(trim($_REQUEST["sign_user"])));//合同签约人  
 
                    $where = "CONTRACT_NO = .$contract_no' AND CITY_PY = '$city'";      
                    $result = $income_contract_moel->update_info_by_cond($where,$data); 

                    if( $result !== false)
                    {
                        $res['result'] = 1;
                        $res['msg'] = 'success';
                    }
                    else
                    {
                        $res['result'] = 0;
                        $res['msg'] = 'fail';
                    }
                }
                else
                {
                    $res['result'] = 0;
                    $res['msg'] = 'missing message';
                }
            }
            else //同步添加合同信息
            {
                if($_REQUEST['activities_id'])
                {     
                    $case_id = D("Erp_activities")->where("ID=".$_REQUEST['activities_id'])->field("CASE_ID")->find();
                    $case_id = $case_id["CASE_ID"];
                    $data['CASE_ID'] = $case_id;                    
                    if(trim($_REQUEST['contract_no']))              $data['CONTRACT_NO']  = trim($_REQUEST['contract_no']);
                    if($_REQUEST['status'])                         $data['STATUS'] = $_REQUEST['status'];//合同状态
                    if(strip_tags(trim($_REQUEST['conf_time'])))    $data['CONF_TIME'] = strip_tags(trim($_REQUEST['conf_time']));//合同确认时间
                    if(trim($_REQUEST['company']))                  $data['COMPANY'] = trim($_REQUEST['company']);//合同单位
                    if(trim($_REQUEST['start_time']))               $data['START_TIME'] = trim($_REQUEST['start_time']);//合同开始时间
                    if(trim($_REQUEST['end_time']))                 $data['END_TIME'] = trim($_REQUEST['end_time']);//合同结束时间
                    if(trim($_REQUEST['pub_time']))                 $data['PUB_TIME'] = trim($_REQUEST['pub_time']);//发布时间
                    if(trim($_REQUEST['conf_user']))                $data['CONF_USER'] = trim($_REQUEST['conf_user']);//合同确认人
                    if(trim($_REQUEST['money']))                    $data['MONEY'] = trim($_REQUEST['money']);//合同金额
                    if($_REQUEST['contract_type'])                  $data['CONTRACT_TYPE'] = trim($_REQUEST['contract_type']);//合同类型
                    if($_REQUEST['city'])                           $data['CITY_PY'] = strtolower(trim($_REQUEST['city'])) ;// 城市py
                    if(trim($_REQUEST['sign_user']))                $data['SIGN_USER'] = u2g(trim($_REQUEST['sign_user']));//合同签约人

                    $data["ADD_TIME"] = date("Y-m-d H:i:s");
                    $data["IS_NEED_INVOICE"] = 0;
                    $city_id = D("Erp_city")->where("PY = '".$data['CITY_PY']."'")->field("ID")->find();
                    //var_dump($city_id);
                    $city_id = $city_id["ID"];
                    $data['CITY_ID'] = $city_id;
                    
                    //var_dump($data);die;
                    if( $data['CONTRACT_NO'] && $data['STATUS'] && $data['COMPANY'] && $data['START_TIME'] 
                        && $data['END_TIME'] && $data['MONEY'] && $data['CONTRACT_TYPE'] && $data['SIGN_USER'] && $data['CITY_PY'])
                    {
                        $insertid = $income_contract_moel->add_contract_info($data);
                        if( $insertid ){
                            //合同添加成功 同步更新项目表中的合同编号
                            $case_model = D("ProjectCase");
                            $project_model = D("Project");
                            $prj_id = $case_model->get_info_by_id($case_id,array("PROJECT_ID"));
                            $prj_id = $prj_id[0]["PROJECT_ID"];
                            $up_num = $project_model->update_prj_info_by_id($prj_id,array("CONTRACT"=>$data['CONTRACT_NO']));
                            $res['result'] = 1;
                            $res['msg'] = 'success';
                        }else{
                            $res['result'] = 0;
                            $res['msg'] = 'fail';
                        }
                    }else{
                        $res['result'] = 0;
                        $res['msg'] = 'missing message';
                    }

                }
                else
                {
                   $res['result'] = 0;
                   $res['msg'] = 'missing message'; 
                } 
            }          
			echo json_encode($res);
		}
        
		//验证收费类型
		public function checkFeeId(){//atfeeID
			if($this->_get('activitiesId')) $sqlcon .= "  and ACTIVITIES_ID = ".$this->_get('activitiesId');   
			if($this->_get('atfeeID') ) $sqlcon .= " and ID<>'".$this->_get('atfeeID'). "'" ;

            $CID = $_REQUEST['CID'];
            if (intval($CID) > 0) {
                $where = sprintf(" FEE_ID = %d AND (ISVALID = -1 OR CID = %d) ", $_REQUEST['param'], $CID) . $sqlcon;
            } else {
                $where = $where = sprintf(" FEE_ID = %d AND ISVALID = -1 ", $_REQUEST['param']) . $sqlcon;
            }

			$fee = M('Erp_actibudgetfee')->where($where)->select(); //var_dump($fee);
			 if($fee){
				  $result['status'] = 'n';
				  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '该类别费用已录入，请选择其他费用类型！') ;
			 } else{
				 $result['status'] = 'y';
				 $result['info'] = '';
				 $fee1 = M('Erp_fee')->where(' PARENTID='.$this->_post('param') )->select();  
				 if($fee1){
					  $result['status'] = 'n';
					  $result['info'] = iconv("GB2312//IGNORE", "UTF-8", '请选择无子类费用') ;
				 } else{
					  $result['status'] = 'y';
					  $result['info'] = '';
					   
				 }
				   
			 }

			
			 echo json_encode($result);
		}
        
		// 查询供应商
		public function getSupplier(){
			$name = u2g($_REQUEST['search']); 
			$data = M('Erp_supplier')->where("NAME like '%".$name."%' ")->select(); 
			foreach($data as $k=>$v){
				$supplier[$k]['ID'] = $v['ID'];
				$supplier[$k]['NAME'] = g2u($v['NAME']);
			}
			echo json_encode($supplier);
		}
        
		//检查供应商
		public function checksupplier()
        {
			$name =  u2g( $_REQUEST['param'] );
			$data = M('erp_supplier')->where(" NAME ='".$name."' ")->find();
            
			if($data)
            {
				$result['status'] = 'y';
				$result['info'] = '';
			} 
            else
            {
				$result['status'] = 'n';
				$result['info'] = iconv("GB2312//IGNORE", "UTF-8", '请输入已存在的供应商！');
			}
            
			echo json_encode($result);
            exit;
		}
		 
        //根据活动名称联想获取活动信息
        public function get_activities_info_by_title(){
            $activities_title = strip_tags(u2g($_REQUEST["title"]));
            $city_py = strtolower(strip_tags($_REQUEST["city"]));
            $city_id = M("Erp_city")->where("PY = '".$city_py."'")->field(array("ID"))->find();
            $city_id = $city_id["ID"];
            $activity_model = D("Erp_activities");
            //$where['TITLE']=array('like','%'.$activities_title.'%');
            $sql = "SELECT A.ID,A.TITLE,B.ID CASE_ID
                    FROM ERP_ACTIVITIES A 
                    LEFT JOIN ERP_CASE B ON A.CASE_ID = B.ID
                    LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID=C.ID 
                    WHERE A.TITLE LIKE '%$activities_title%' AND A.BUSINESSCLASS_ID = 4 AND C.ACSTATUS in(2,4) AND C.CITY_ID = '".$city_id."' AND C.STATUS<>2";
            //ECHO $SQL;DIE;
            //$activity_info = $activity_model->where($where)->field(array("ID","TITLE"))->select();
            $activity_info = M()->query($sql);
            if(!$activity_info)
            {
                $result["status"] = "0";
                $result["msg"] = "没有找到相关信息";
                $result["data"] = "";
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);exit;
            }
            else
            {
                $result["status"] = "1";
                $result["msg"] = "获取信息成功";                 
                $result["data"] = $activity_info;
                $result["msg"] = g2u($result["msg"]);
                $result["data"] = g2u($result["data"]);
                echo json_encode($result);
                exit;
            }
        }
        
        //根据活动id获取活动信息 接口
        public function get_activities_info_by_id()
        {
            $activities_id = $this->_get('id') ? $this->_get('id') : '';
            if( !$activities_id )
            {
                $result["status"] = "0";
                $result["msg"] = "missing parameter";
                $result["data"] = "";
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);exit;
            }
            else
            {
                $activity_model = D("Erp_activities");
                $activity_info = $activity_model->field(array("TITLE","APPLICANT"))->find($activities_id);
                $user_name = D("Erp_users")->where("ID=".$activity_info["APPLICANT"])->field("USERNAME")->find();
                $activity_info["APPLICANT"] = $user_name["USERNAME"];
                if(!$activity_info)
                {
                    $result["status"] = "0";
                    $result["msg"] = "没有找到相关信息";
                    $result["data"] = "";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);exit;
                }
                else
                {
                    $result["status"] = "1";
                    $result["msg"] = "获取信息成功";                 
                    $result["data"] = $activity_info;
                    $result["msg"] = g2u($result["msg"]);
                    $result["data"] = g2u($result["data"]);
                    echo json_encode($result);
                    exit;
                }
            }
        }
        

		/***************************************************************************************************/
		/******************************************CRM对接接口******************************************/
		/***************************************************************************************************/

		/**
		+----------------------------------------------------------
		 * 获取项目的数据
		+----------------------------------------------------------
		 * @param none
		+----------------------------------------------------------
		 * @return 序列化项目数据
		+----------------------------------------------------------
		 */
		public function get_proinfo(){

//			$return = array(
//				'status'=>1,
//				'msg'=>'',
//				'data'=>null,
//			);
			//返回结果
			$return = array();

			//where 条件
			$where = " 1 = 1";
			$where .= " and (erp_project.bstatus in (2,3,4) or erp_project.mstatus in (2,3,4))";

			if($projectid = trim($_REQUEST['projectid']))
			{
				$where .= " and erp_project.id in ($projectid) ";
			}

			$projectInfo = M("erp_project")
				->join("erp_house on erp_project.id = erp_house.project_id")
				->field("erp_project.id,erp_house.rel_property,erp_house.pro_block_id,pro_listid")
				->where($where)->select();

			if(is_array($projectInfo)){
				foreach($projectInfo as $k => $r){
					$return[$k]['projectid'] = $r['ID'];
					$return[$k]['projectname'] = $r['REL_PROPERTY'];
					$return[$k]['blockid'] = $r['PRO_BLOCK_ID'];
					$return[$k]['loupanid'] = $r['PRO_LISTID'];
				}
			}

			die(serialize($return));
		}
        
		
        /**
		+----------------------------------------------------------
		 * 根据关键词获取小蜜蜂采购任务清单
		+----------------------------------------------------------
		 * @param none
		+----------------------------------------------------------
		 * @return json 字符串信息
		+----------------------------------------------------------
		 */
        public function get_purchase_list_by_keyword()
        {   
        	//任务采购物品名称
            $purchase_name = trim(strip_tags(u2g($_GET['purchase_name'])));
            
            //城市拼音参数
            $city_py = trim(strip_tags($_GET['city']));
            
            //显示条数
            $show_num = intval($_GET['show_num']) > 0 ? intval($_GET['show_num']) : 10;
            
            //通信秘钥
            $secret_key = !empty($_GET['secret_key']) ? 
            		strip_tags($_GET['secret_key']) : '';
            
            //拼接通信秘钥
            $secret_key_yz = md5($city_py.'_'.'JG.HOUSE365');
            //echo $secret_key_yz;
            /***验证秘钥是否正确***/
            if($secret_key != $secret_key_yz)
            {
            	$result['result'] = 0;
            	$result['msg'] = g2u('验证失败');
            	$result['data_info'] = array();
            		
            	echo json_encode($result);
            	exit;
            }
            
            /***查询符合条件的采购明细***/
            $result = array();
            if($purchase_name != '' && $city_py != '')
            {	
            	//城市MODEL
				$city_model = D('City');
				$city_info = $city_model->get_city_info_by_py($city_py, array('ID'));
				
				/***城市编号查询***/
				if(empty($city_info))
				{
					$result['result'] = 0;
					$result['msg'] = g2u('无法查找到相关城市');
					$result['data_info'] = array();
					
					echo json_encode($result);
					exit;
				}
				
				$city_id = $city_info['ID'];
				
				//采购类型、状态配置信息查询
                $purchase_model = D('PurchaseRequisition');
                $purchase_type = $purchase_model->get_conf_purchase_type();
                $purchase_status = $purchase_model->get_conf_requisition_status();
                
                //查询符合条件的采购明细
                $cond_where =  " erp_purchase_list.FEE_ID = '58' AND "
                		. " erp_purchase_requisition.STATUS = '".$purchase_status['approved']."' AND "
                        . " erp_purchase_list.TYPE = ".$purchase_type['project_purchase']." AND "
                        . " erp_purchase_list.PRODUCT_NAME LIKE '%".$purchase_name."%' AND "
                        . " erp_purchase_requisition.CITY_ID = '".$city_id."' AND erp_purchase_list.S_ID IS NULL";
                
                $pruchase_info_temp = M('erp_purchase_list')
                ->join("erp_purchase_requisition ON erp_purchase_list.PR_ID = erp_purchase_requisition.ID")
                ->field("erp_purchase_list.ID, erp_purchase_list.PRODUCT_NAME, "
                		." erp_purchase_list.PRICE_LIMIT, erp_purchase_list.NUM_LIMIT, "
                		." erp_purchase_requisition.CASE_ID, erp_purchase_requisition.PRJ_ID, "
                		." to_char(erp_purchase_requisition.END_TIME,'yyyy-mm-dd hh24:mi:ss') AS END_TIME, "
                		."erp_purchase_requisition.CITY_ID")->where($cond_where)->limit($show_num)->select();
                //echo M('erp_purchase_list')->getLastSql();
                if(is_array($pruchase_info_temp) && !empty($pruchase_info_temp))
                {   
                	$prj_arr = array();
                	foreach ($pruchase_info_temp as $key => $value)
                	{
                		$pruchase_info[$value['ID']]['prj_id'] = $value['PRJ_ID'];
                		$pruchase_info[$value['ID']]['p_id'] = $value['ID'];
                		$pruchase_info[$value['ID']]['p_name'] = g2u($value['PRODUCT_NAME']);
                		$pruchase_info[$value['ID']]['price_limit'] = $value['PRICE_LIMIT'];
                		$pruchase_info[$value['ID']]['num_limit'] = $value['NUM_LIMIT'];
                		$pruchase_info[$value['ID']]['send_time'] = strtotime($value['END_TIME']);
                		
                		$prj_arr[$value['PRJ_ID']] = $value['PRJ_ID'];
                	}
                	//var_dump($prj_arr);
                	$project_ext_info = array();
                	if(is_array($prj_arr) && !empty($prj_arr))
                	{	
                		$house_model = D('House');
                		$search_field = array('PROJECT_ID', 'PRO_NAME', 'PRO_LISTID', 'REL_NEWHOUSEID');
                		$house_info = 
                			$house_model->get_house_info_by_prjid($prj_arr, $search_field);
                		
                		if(is_array($house_info) && !empty($house_info))
                		{
                			foreach($house_info as $h_key => $h_value )
                			{	
                				//楼盘编号
                				$project_ext_info[$h_value['PROJECT_ID']]['PRO_LISTID'] 
                					= $h_value['PRO_LISTID'];
                				//关联id
                				$project_ext_info[$h_value['PROJECT_ID']]['REL_NEWHOUSEID'] 
                					= $h_value['REL_NEWHOUSEID'];
                				//项目名称
                				$project_ext_info[$h_value['PROJECT_ID']]['PRO_NAME']
                				= $h_value['PRO_NAME'];
                			}
                		}
                	}
                	
                	foreach ($pruchase_info as $key => $value)
                	{	
                		//楼盘编号
                		$pruchase_info[$key]['pro_listid'] 
                			= $project_ext_info[$value['prj_id']]['PRO_LISTID'];
                		//关联id
                		$pruchase_info[$key]['rel_newhouseid'] 
                			= $project_ext_info[$value['prj_id']]['REL_NEWHOUSEID'];
                		//项目名称
                		$pruchase_info[$key]['prj_name']
                		= g2u($project_ext_info[$value['prj_id']]['PRO_NAME']);
                	}
                	
                    $result['result'] = 1;
                    $result['msg'] = g2u('获取数据成功');
                    $result['data_info'] = $pruchase_info;
                }
                else
                {
                    $result['result'] = 0;
                    $result['msg'] = g2u('无符合条件信息');
                    $result['data_info'] = array();
                }
            }
            else
            {
                $result['result'] = 0;
                $result['msg'] = g2u('参数异常');
                $result['data_info'] = array();
            }
            
            echo json_encode($result);
            exit;
        }
        
        
        /**
         +----------------------------------------------------------
         * 根据采购任务编号提交采购任务成本信息
         +----------------------------------------------------------
         * @param none
         +----------------------------------------------------------
         * @return json 字符串信息
         +----------------------------------------------------------
         */
        public function sub_purchase_cost_by_pid()
        {	
        	//项目编号
        	$prj_id = intval($_GET['prj_id']);
        	
        	//采购明细编号
        	$p_id = intval($_GET['p_id']);
        	
        	//数量
        	$p_num = floatval($_GET['p_num']);
            
            //单价
            $p_unit_cost = floatval($_GET['p_unit_cost']);
        	
        	//通信秘钥
        	$secret_key = !empty($_GET['secret_key']) ?
        	strip_tags($_GET['secret_key']) : '';
        	
        	//拼接通信秘钥
        	$secret_key_yz = md5($prj_id.'_'.$p_id.'_'.$p_num.'_'.$p_unit_cost.'_'.'JG.HOUSE365');
			
        	/***验证秘钥是否正确***/
        	if($secret_key != $secret_key_yz)
        	{
        		$result['result'] = 0;
        		$result['msg'] = g2u('验证失败');
        		$result['data_info'] = array();
        
        		echo json_encode($result);
        		exit;
        	}
            
            if($prj_id > 0 && $p_id > 0)
            {   
                //采购明细MODEL
                $purchase_list_model = D('PurchaseList');
                
                //查询采购明细
                $search_field = array('ID', 'CASE_ID', 'PR_ID', 'PRICE' , 'NUM', 'FEE_ID', 'IS_KF' , 'IS_FUNDPOOL');
                $purchase_list_info = $purchase_list_model->get_purchase_list_by_id($p_id);
                
                //根据申请单编号查询申请单信息
                if(is_array($purchase_list_info) && !empty($purchase_list_info))
                {   
                    $pr_id = $purchase_list_info[0]['PR_ID'];
                    $p_id = $purchase_list_info[0]['ID'];

                    //本次总金额
                    //$cond_where = "ID = '".$p_id."'";
                    //$update_num = $purchase_list_model->update_purchase_list_by_cond($update_arr, $cond_where);
                    
                    $update_num = 1;
                    if($update_num > 0)
                    {    
                        //添加成本信息
                        $project_cost_model = D('ProjectCost');
                        
                        $cost_info['CASE_ID'] = $purchase_list_info[0]['CASE_ID'];  //案例编号    
                        $cost_info['ENTITY_ID'] = $pr_id; //业务实体编号
                        $cost_info['EXPEND_ID'] = $p_id; //成本明细编号
                        $cost_info['ORG_ENTITY_ID'] = $pr_id; //原始业务实体编号
                        $cost_info['ORG_EXPEND_ID'] = $p_id; //原始成本明细编号
                        $cost_info['FEE'] = $p_num * $p_unit_cost;  //成本金额
                        $cost_info['ADD_UID'] = 2363;  //操作用户编号
                        $cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());  //发生时间
                        $cost_info['ISFUNDPOOL'] = $purchase_list_info[0]["IS_FUNDPOOL"];   //是否资金池
                        $cost_info['ISKF'] = $purchase_list_info[0]["IS_KF"];   //是否扣非
                        // $cost_info['IS_KF'] = $purchase_list_info[0]["FEE_ID"];   //是否扣非
                        $cost_info['INPUT_TAX'] = 0;  //进项税
                        
                        $add_result = $project_cost_model->add_cost_info($cost_info);
                        
                        if($add_result)
                        {   
                            $result['result'] = 1;
                            $result['msg'] .= g2u('采购任务信息更新成功，成本添加成功');
                        }
                        else
                        {
                            $result['result'] = 0;
                            $result['msg'] .= g2u('采购任务更新成功，成本添加失败'); 
                        }
                    }
                    else
                    {
                        $result['result'] = 0;
                        $result['msg'] = g2u('采购任务更新失败');
                    }
                }
                else
                {
                    $result['result'] = 0;
                    $result['msg'] = g2u('无符合条件的采购信息'); 
                }
            }
            else 
            {
                $result['result'] = 0;
                $result['msg'] = g2u('项目编号、采购任务编号不能为空'); 
            }
        	
        	echo json_encode($result);
        	exit;
        }

		/**
		 * api 异步接口运行脚本
		 *  type：1    合同系统
		 *  type：2    crm系统
		 */
		public function run_api_log(){
			//每次运行数量
			$pageSize = 40;

			//获取类型
			$type = 1;

			//获取api数据
			$api_data = M("erp_api_log")
				->field("id,api_address")
				->where('state=0 and type=' . $type)
				->limit($pageSize)
				->order('id desc')
				->select();

			echo "开始";

			//循环处理业务
			foreach($api_data as $_data){
				//处理标示
				$flag = false;

				//获取数据
				$_data['API_ADDRESS'] = str_replace("###","&",$_data['API_ADDRESS']);
				$return = @curl_get_contents($_data['API_ADDRESS'],'get');

				switch($type){
					//合同系统
					case 1:
						$arr_return = @unserialize($return);
						if($arr_return['status']) $flag = true;
						break;
					//crm系统
					case 2:
						if($return) $flag = true;
						break;
				}

				file_put_contents("api.log",$_data['API_ADDRESS'] . "--------------" . $return . "\n",FILE_APPEND);

				//根据返回值处理
				if($flag){
					echo "run_api_log:" . $_data['ID'] . "success";

					$info = array();
					$info['state'] = 1;

					$sql = "update erp_api_log set state = 1  where id = " . $_data['ID'];
					M("erp_api_log")->query($sql);
				}
			}
		}

		/**
		 * api 异步接口运行脚本
		 *  type：2    crm系统
		 */
		public function run_api_log_crm(){
			//每次运行数量
			$pageSize = 40;

			//获取类型
			$type = 2;

			//获取api数据
			$api_data = M("erp_api_log")
				->field("id,api_address")
				->where('state=0 and type=' . $type)
				->limit($pageSize)
				->order('id desc')
				->select();

			echo "开始";

			//循环处理业务
			foreach($api_data as $_data){
				//处理标示
				$flag = false;

				//获取数据
				$_data['API_ADDRESS'] = str_replace("###","&",$_data['API_ADDRESS']);
				$return = @curl_get_contents($_data['API_ADDRESS'],'get');

				switch($type){
					//合同系统
					case 1:
						$arr_return = @unserialize($return);
						if($arr_return['status']) $flag = true;
						break;
					//crm系统
					case 2:
						if($return) $flag = true;
						break;
				}

				file_put_contents("api.log",$_data['API_ADDRESS'] . "--------------" . $return . "\n",FILE_APPEND);

				//根据返回值处理
				if($flag){
					echo "run_api_log:" . $_data['ID'] . "success";

					$info = array();
					$info['state'] = 1;

					$sql = "update erp_api_log set state = 1  where id = " . $_data['ID'];
					M("erp_api_log")->query($sql);
				}
			}
		}

		/**
		 * 全链条精准导购系统数据同步
		 */
		public function runQltApiLog(){

			$pageSize = 40;

			//获取api数据
			$api_data = M("erp_api_log")
				->field("id,api_address,type")
				->where('state=0 and type in (3,4)')
				->limit($pageSize)
				->order('id desc')
				->select();

			echo "开始";

			//循环处理业务
			foreach($api_data as $_data){
				//处理标示
				$flag = false;

				//获取数据
				$_data['API_ADDRESS'] = str_replace("###","&",$_data['API_ADDRESS']);

				if($_data['TYPE']==4)
					$_data['API_ADDRESS'] = 'http://oms.house365.com' . $_data['API_ADDRESS'];

				echo $_data['API_ADDRESS'];

				$return = @curl_get_contents($_data['API_ADDRESS'],'get');
				$flag = true;
				file_put_contents("api.log",$_data['API_ADDRESS'] . "--------------" . $return . "\n",FILE_APPEND);

				//根据返回值处理
				if($flag){
					echo "run_api_log:" . $_data['ID'] . "success";

					$info = array();
					$info['state'] = 1;

					$sql = "update erp_api_log set state = 1  where id = " . $_data['ID'];
					M("erp_api_log")->query($sql);
				}
			}
		}

		/**
		 * 同步合同系统
		 */
		public function run_contract_systemc(){

			echo "开始";

			$contract = M("Erp_income_contract")->field('ID')->select();

				$contract_model = M("Erp_income_contract");

				$error_str = '';

				foreach($contract as $key=>$val){
					$contract_ret = $contract_model->field("contract_no,city_py")
							->where("ID =".$val['ID'])->find();

					$contract_url = CONTRACT_API . "get_ct_info.php?city=" . $contract_ret['CITY_PY'] . "&contractnum=" . $contract_ret['CONTRACT_NO'];
					$contract_data = curl_get_contents($contract_url);

					$contract_data = unserialize($contract_data);

					if(empty($contract_data)) {
						$error_str .= "第" . ($key + 1) . "条合同，从合同系统中未取到数据(可能跟合同状态有关)!\n";
						continue;
					}

					//合作单位
					$data['COMPANY'] =  $contract_data['contunit'];
					//开始时间
					$data['START_TIME'] = date("Y-m-d",$contract_data['contbegintime']);
					//结束时间
					$data['END_TIME'] = date("Y-m-d",$contract_data['contendtime']);
					//合同状态
					$data['STATUS'] = $contract_data['step'];
					//合同金额
					$data['MONEY'] = $contract_data['contmoney'];
					//合同类型
					$data['CONTRACT_TYPE'] = $contract_data['type'];
					//合同签约人
					$data['SIGN_USER'] = $contract_data['addman'];
					//已发布金额
					$data['ISSUEAMOUNT'] = $contract_data['all_fb'];
					//财务确认时间
					if($contract_data['confirmtime'])
						$data['CONF_TIME'] = date("Y-m-d H:i:s",$contract_data['confirmtime']);

					$update = $contract_model->where("ID =".$val['ID'])->save($data);

					if($update){
						echo "run_contract_systemc:" . $val['ID'] . "success";

						$info = array();
						$info['state'] = 1;
					}

//					if(!$update){
//						$error_str .= "第" . ($key + 1) . "条合同，更新失败!\n";
//						continue;
//					}

				}

		}

		/**
		 * api 众客回调接口
		 */
        public function zk_get_back(){
            $post = $_POST;
            
            $return = array();
            //数据存储
            $model = D('PurchaseBeeDetails');
			$project_cost_model = D("ProjectCost");
            $return = array(
                'code'=>400,
                'message' => 'failure',
            );
            //检测小蜜蜂采购详情是否存在
            $model_bee_list = D('PurchaseList');
            if (!isset($post['p_id'])){
                ajaxJsonReturn(false,'p_id is empty',0);
            }
            $bee_list = $model_bee_list->find($post['p_id']);
            
            if (!$bee_list || empty($bee_list)){
                ajaxJsonReturn(false,'p_id is wrong',0);
            }
            //备注处理
            if ($post['mark']=='none'){
                $post['mark'] = '';
            }
            if (!empty($post)){
				M()->startTrans();
                $need_add = array(
                    'TASK_ID' => $post['task_id'],
                    'TASK_NAME' => mb_convert_encoding($post['task_name'], 'GBK','UTF-8'),
                    'SUPPLIER' => mb_convert_encoding($post['supplier'], 'GBK','UTF-8'),
                    'SUPPLIER_ID' => $post['supplier_id'],
                    'EXEC_START' => $post['exec_start'],
                    'EXEC_END' => $post['exec_end'],
                    'TOTAL_NUM' => $post['total_num'],
                    'TOTAL_WAGES' => $post['total_wages'],
                    'TOTAL_BONUS' => $post['total_bonus'],
                    'TOTAL_MONEY' => $post['total_money'],
                    'MARK' => mb_convert_encoding($post['mark'], 'GBK','UTF-8'),
                    'FILE1' => mb_convert_encoding($post['file1'], 'GBK','UTF-8'),
                    'FILE2' => mb_convert_encoding($post['file2'], 'GBK','UTF-8'),
                    'FILE3' => mb_convert_encoding($post['file3'], 'GBK','UTF-8'),
                    'P_ID' => $post['p_id'],
					'REIM_MONEY' => $post['reim_money'],
                );
				//CLOB 特殊处理
				$sql = "INSERT INTO erp_purchaser_bee_details (TASK_ID,TASK_NAME,SUPPLIER,SUPPLIER_ID,EXEC_START,EXEC_END,TOTAL_NUM,TOTAL_WAGES,TOTAL_BONUS,TOTAL_MONEY,MARK,FILE1,FILE2,FILE3,P_ID,REIM_MONEY)
VALUES({$need_add['TASK_ID']},'{$need_add['TASK_NAME']}','{$need_add['SUPPLIER']}',{$need_add['SUPPLIER_ID']},to_date('{$need_add['EXEC_START']}','yyyy-mm-dd hh24:mi:ss'),to_date('{$need_add['EXEC_END']}','yyyy-mm-dd hh24:mi:ss'),{$need_add['TOTAL_NUM']},{$need_add['TOTAL_WAGES']},{$need_add['TOTAL_BONUS']},{$need_add['TOTAL_MONEY']},'{$need_add['MARK']}',:file1,:file2,:file3,{$need_add['P_ID']},{$need_add['REIM_MONEY']})";

				$conn = oci_connect(C('DB_USER'), C('DB_PWD'), C('DB_NAME'));
				$stmt = oci_parse($conn, $sql);
				oci_bind_by_name($stmt, ':file1', mb_convert_encoding($post['file1'], 'GBK','UTF-8'));
				oci_bind_by_name($stmt, ':file2', mb_convert_encoding($post['file2'], 'GBK','UTF-8'));
				oci_bind_by_name($stmt, ':file3', mb_convert_encoding($post['file3'], 'GBK','UTF-8'));

				$add_result = oci_execute($stmt);

				$model_bee      = D('PurchaseList');
				$bee = $model_bee->find($post['p_id']);
				$cost_info['CASE_ID'] = $bee["CASE_ID"]; //案例编号 【必填】       
				$cost_info['ENTITY_ID'] = $bee["PR_ID"];                                 
				$cost_info['EXPEND_ID'] = $bee["ID"];                            
				$cost_info['ORG_ENTITY_ID'] = $bee["PR_ID"];                    
				$cost_info['ORG_EXPEND_ID'] = $bee["ID"];                //业务实体编号 【必填】
				$cost_info['FEE'] =  (float)$post['reim_money'];                // 成本金额 【必填】 
				$cost_info['ADD_UID'] = $bee["P_ID"];//$_SESSION["uinfo"]["uid"];            //操作用户编号 【必填】
				$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //发生时间 【必填】
				$cost_info['ISFUNDPOOL'] = $bee["IS_FUNDPOOL"];                  //是否资金池（0否，1是） 【必填】
				$cost_info['ISKF'] = $bee["IS_KF"];                             //成本类型ID 【必填】
				//$cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //进项税 【选填】
				$cost_info['FEE_ID'] =  $bee["FEE_ID"];   
				$cost_info['EXPEND_FROM'] = 30; //?
                $cost_info['FEE_REMARK'] = "众客采购申请";//成本类型ID 【必填】
				$cost_insert_id = $project_cost_model->add_cost_info($cost_info);

                if ($add_result && $cost_insert_id){
                    M()->commit();
                    ajaxJsonReturn($post['task_id'],'success',1);
                }else{
					M()->rollback();
                    ajaxJsonReturn($post['task_id'],'failure',0);
                }
            }
            ajaxJsonReturn(false,'Request parameter is empty',0);
        }
		public function updateStatus(){
			$model = new Model();
			$list = M('Erp_project')->where("STATUS<>2    ")->select(); 
			foreach($list as $key=>$val){ 
				$temp = $ctemp = $cres = array();
				$caseids  = array();
				if($val['BSTATUS']==2 || $val['BSTATUS']==4 ){//电商
					$state = $this->getExeStatus(1,$val['ID']);   
					if($state){
						$ctemp['FSTATUS'] = $temp['BSTATUS']=$state['status'];
						$caseids[] = $state['case_id'];
						$cres = $this->getAcase(1,$val['ID']); 
						$caseids = array_merge($caseids,$cres);

						//M('Erp_project')->where->save($temp);
					}
				}
				if($val['MSTATUS']==2 || $val['MSTATUS']==4 ){//分销
					$state = $this->getExeStatus(2,$val['ID']);
					if($state){
						$ctemp['FSTATUS'] = $temp['MSTATUS']=$state['status'];
						$caseids[] = $state['case_id'];
						$cres = $this->getAcase(2,$val['ID']); 
						$caseids = array_merge($caseids,$cres);
						//M('Erp_project')->where->save($temp);
					}
				}
				if($val['SCSTATUS']==2 || $val['SCSTATUS']==4 ){//非我方收筹
					$state = $this->getExeStatus(8,$val['ID']);
					if($state){
						$ctemp['FSTATUS'] = $temp['SCSTATUS']=$state['status'];
						$caseids[] = $state['case_id'];
						$cres = $this->getAcase(8,$val['ID']);
						$caseids = array_merge($caseids,$cres);
						//M('Erp_project')->where->save($temp);
					}
				}
				if($val['ASTATUS']==2  || $val['ASTATUS']==4 ){//硬广
					$state = $this->getContractStatus(3,$val['ID']);
					if($state){
						$ctemp['FSTATUS'] = $temp['ASTATUS']=$state['status'];
						$caseids[] = $state['case_id'];

						$cres = $this->getAcase(3,$val['ID']);
						$caseids = array_merge($caseids,$cres);
						//$caseids[] = $state;
						//M('Erp_project')->where->save($temp);
					}
				}
				if($val['ACSTATUS']==2 ||  $val['ACSTATUS']==4 ){//活动
					$state = $this->getAcStatus(4,$val['ID']); 
					if($state){
						$ctemp['FSTATUS'] = $temp['ACSTATUS'] = $state['status'];
						$caseids[] = $state['case_id'];

						//$caseids[] = $state;
						//M('Erp_project')->where->save($temp);
					}
				}
				$cids = implode(',',$caseids);
				
				
				$model->startTrans();
				if(!empty($temp)){
					$result = M('Erp_project')->where("ID=".$val['ID'])->save($temp);
				}else $result = true;
				if (!empty($caseids) ) {
					 
					$result2 = M('Erp_case')->where("ID in ($cids) and FSTATUS in(2,4) ")->save($ctemp); 
				}else $result2=true;
				var_dump($caseids);
				if($result && $result2){
						$model->commit();
						echo $val['ID'].'成功'; var_dump($temp);var_dump($ctemp);

				}else{
						$model->rollback();
						echo $val['ID'].'失败';var_dump($temp);var_dump($ctemp);
				}
			}

		}
		//更新项目状态
		public function getExeStatus($scaletype,$project_id){
			$case = M('Erp_case')->where("PROJECT_ID=$project_id and SCALETYPE=$scaletype")->find();
			//M("Erp_prjbudget")->where()->find();
			$today = date('Y-m-d');
			if($case){
				$res = M()->query("select  to_char(TODATE,'yyyy-mm-dd') as TODATE  from ERP_PRJBUDGET where  CASE_ID=".$case['ID']);  
				$result['case_id'] = $case['ID'];
				$todate = strtotime($res[0]['TODATE']);
				 
				$result['status'] = $todate<time()?4:2; 

				return $res ? $result :false;
			}
			return false;
		}
		public function getContractStatus($scaletype,$project_id){
			$case = M('Erp_case')->where("PROJECT_ID=$project_id and SCALETYPE=$scaletype")->find();
			//M("Erp_prjbudget")->where()->find();
			$today = date('Y-m-d');
			if($case){
				$res = M()->query("select to_char(END_TIME,'yyyy-mm-dd') as END_TIME  from ERP_INCOME_CONTRACT where CASE_ID=".$case['ID']);
				$result['case_id'] = $case['ID'];
				$todate = strtotime($res[0]['END_TIME']);
				 
				$result['status'] = $todate<time()?4:2; 

				return $res ? $result :false;
				 
			}
			return false;
		}
		public function getAcStatus($scaletype,$project_id){
			$case = M('Erp_case')->where("PROJECT_ID=$project_id and SCALETYPE=$scaletype")->find();
			//M("Erp_prjbudget")->where()->find();
			//$today = date('Y-m-d');
			 
			if($case){
				$res = M()->query("select to_char(HETIME,'yyyy-mm-dd') as HETIME from ERP_ACTIVITIES where     CASE_ID=".$case['ID']);
				$result['case_id'] = $case['ID'];
				$todate = strtotime($res[0]['HETIME']);
				 
				$result['status'] = $todate<time()?4:2; 

				return $res ? $result :false;
				
			 
			}
			return false;
		}
		public function getAcase($scaletype,$project_id ){
			$caseid = array();
			$activ = M('Erp_activities')->where("PROJECT_ID=$project_id and BUSINESSCLASS_ID=$scaletype")->select();
			foreach($activ  as $one){
			$caseid[] =  $one['CASE_ID'];
			}
			return $caseid;
		}


		public function getContractList(){
			//返回结果集
			$response = array(
				'status'=>false,
				'msg'=>'',
				'data'=>null,
			);

			//合同号
			$contract = isset($_REQUEST['contract'])?trim($_REQUEST['contract']):'';
			//城市
			$city = isset($_REQUEST['city'])?trim($_REQUEST['city']):'nj';

			//获取城市ID
			$resCity = M("Erp_city")->where("PY = '$city'")->field('ID,NAME')->find();
			$cityId = $resCity['ID'];

			if(!$contract || !$cityId){
				$response['msg'] = g2u('对不起，合同号不能为空!');
				die(json_encode($response));
			}

			//获取合同列表
			$sql = 'SELECT CONTRACT FROM ERP_PROJECT WHERE CITY_ID = ' . $cityId . ' AND CONTRACT like ' . "'%$contract%' AND STATUS <> 2 AND PSTATUS = 3";
			$res = D()->query($sql);

			if($res) {
				$response['status'] = true;
				foreach ($res as $key => $val) {
					$response['data'][] = $val['CONTRACT'];
				}
			}

			die(json_encode($response));
		}

		/**
		 * 全链条经管系统 - 获取项目信息接口
		 */
		public function getProInfo(){

			//返回结果集
			$response = array(
				'status'=>false,
				'msg'=>'',
				'data'=>null,
			);

			//合同号
			$contract = isset($_REQUEST['contract'])?trim($_REQUEST['contract']):'';
			//城市
			$city = isset($_REQUEST['city'])?trim($_REQUEST['city']):'nj';
			//条数
			$limit = isset($_REQUEST['limit'])?trim($_REQUEST['limit']):10;

			//获取城市ID
			$resCity = M("Erp_city")->where("PY = '$city'")->field('ID,NAME')->find();
			$cityId = $resCity['ID'];
			$cityName = $resCity['NAME'];

			if(!$contract || !$cityId){
				$response['msg'] = g2u('对不起，合同号不能为空!');
				die(json_encode($response));
			}

			//获取项目数据

			$sql = 'SELECT * FROM (SELECT T.*,ROWNUM RN FROM';
			$sql .= '(SELECT A.*,U.NAME AS USERNAME,B.ETIME AS STARTTIME FROM ERP_HOUSE A INNER JOIN ERP_PROJECT B ON A.PROJECT_ID = B.ID LEFT JOIN ERP_USERS U ON A.CUSTOMER_MAN = U.ID WHERE B.CITY_ID = ' . $cityId . ' AND A.CONTRACT_NUM like ' . "'%$contract%' AND STATUS <> 2 AND B.PSTATUS = 3 ORDER BY CONTRACT_NUM ASC) T";
			$sql .= ' WHERE ROWNUM <= ' . $limit . ')WHERE RN >=1';
			$res = D()->query($sql);

			if(!$res){
				$response['msg'] = g2u('对不起，您传递的城市或合同号有误!');
				die(json_encode($response));
			}

			//业务类型状态
			$scaleTypeArr = D("ProjectCase")->get_conf_case_type_remark();

			$response['status'] = true;
			foreach($res as $key=>$val){
				//项目编号
				$response['data'][$key]['projectCode'] = intval($val['PROJECT_ID']);
				//合同编号
				$response['data'][$key]['contractCode'] = g2u($val['CONTRACT_NUM']);
				//项目名称
				$response['data'][$key]['projectName'] = g2u($val['PRO_NAME']);
				//关联ID
				$response['data'][$key]['buildingId'] = intval($val['PRO_LISTID']);
				//关联楼盘名称
				$response['data'][$key]['buildingName'] = g2u($val['REL_PROPERTY']);
				//项目状态
				$response['data'][$key]['status'] = intval($val['FSTATUS']);
				//项目经理
				$response['data'][$key]['manager'] = g2u($val['USERNAME']);
				//物业类型
				$response['data'][$key]['type'] = g2u($val['PROPERTY_CLASS']);
				//开发商
				$response['data'][$key]['kfs'] = g2u($val['DEV_ENT']);
				//城市
				$response['data'][$key]['city'] = $city;
				//城市名称
				$response['data'][$key]['cityName'] = g2u($cityName);
				//区属
				$response['data'][$key]['district'] = '';
				//地址
				$response['data'][$key]['address'] = g2u($val['PRO_ADDR']);
				//开盘时间
				$response['data'][$key]['startTime'] = oracle_date_format($val['STARTTIME']);

				//业务类型
				$scaleType = M('Erp_case')
					->field('SCAlETYPE,FSTATUS')
					->where('PROJECT_ID='.$val['PROJECT_ID'] . ' AND SCALETYPE != 7')
					->order('SCAlETYPE ASC')
					->select();
				foreach($scaleType as $k=>$v){
					$response['data'][$key]['business'] .= g2u($scaleTypeArr[$v['SCALETYPE']]) . ',';
					//获取状态
					if($k == 0)
						$response['data'][$key]['status'] = intval($v['FSTATUS']);
				}
				$response['data'][$key]['business'] = trim($response['data'][$key]['business'],',');

			}

			die(json_encode($response));
		}

		/**
		 * 全链条精准导购系统，
		 */
		public function getOneProInfo(){

			$pID = isset($_REQUEST['pID'])?intval($_REQUEST['pID']):0;

			//获取项目数据
			$sql = 'SELECT A.*,U.NAME AS USERNAME,B.ETIME AS STARTTIME FROM ERP_HOUSE A INNER JOIN ERP_PROJECT B ON A.PROJECT_ID = B.ID LEFT JOIN ERP_USERS U ON A.CUSTOMER_MAN = U.ID WHERE B.ID = ' . $pID;
			$res = D()->query($sql);

			//业务类型状态
			$scaleTypeArr = D("ProjectCase")->get_conf_case_type_remark();

			if($res){
				$response['data'][0]['serviceName'] = 'updateProjectInfo';
				//项目编号
				$response['data'][0]['projectCode'] = intval($res[0]['PROJECT_ID']);
				//合同编号
				$response['data'][0]['contractCode'] = g2u($res[0]['CONTRACT_NUM']);
				//项目名称
				$response['data'][0]['projectName'] = g2u($res[0]['PRO_NAME']);
				//关联ID
				$response['data'][0]['buildingId'] = intval($res[0]['PRO_LISTID']);
				//关联楼盘名称
				$response['data'][0]['buildingName'] = g2u($res[0]['REL_PROPERTY']);
				//项目状态
				$response['data'][0]['status'] = intval($res[0]['FSTATUS']);
				//项目经理
				$response['data'][0]['manager'] = g2u($res[0]['USERNAME']);
				//物业类型
				$response['data'][0]['type'] = g2u($res[0]['PROPERTY_CLASS']);
				//开发商
				$response['data'][0]['kfs'] = g2u($res[0]['DEV_ENT']);
				//地址
				$response['data'][0]['address'] = g2u($res[0]['PRO_ADDR']);

				//业务类型
				$scaleType = M('Erp_case')
					->field('SCAlETYPE,FSTATUS')
					->where('PROJECT_ID='.$res[0]['PROJECT_ID'] . ' AND SCALETYPE != 7')
					->order('SCAlETYPE ASC')
					->select();
				foreach($scaleType as $k=>$v){
					$response['data'][0]['business'] .= g2u($scaleTypeArr[$v['SCALETYPE']]) . ',';
					//获取状态
					if($k == 0)
						$response['data'][0]['status'] = intval($v['FSTATUS']);
				}
				$response['data'][0]['business'] = trim($response['data'][0]['business'],',');
			}

			//写入众客系统
			$apiUrl = QLTAPI;
			$apiRes = curlPost($apiUrl, $response['data'][0]);
			var_dump($apiRes);
			die("调用结束");
		}

		public function is_over_payout_limit(){
			$response = array(
				'status'=>false,
				'msg'=>'',
				'data'=>null,
			);

			$response['data']['state'] = false;

			$case_id = isset($_GET['case_id'])?intval($_GET['case_id']):0;
			$apply_amount = isset($_GET['apply_amount'])?intval($_GET['apply_amount']):0;
			$type = isset($_GET['type'])?intval($_GET['type']):0;
			$p_id = isset($_GET['p_id'])?intval($_GET['p_id']):0;


			//如果是采购
			if($type=='purchase'){
				$sql = "SELECT B.CASE_ID,A.PRICE_LIMIT,A.NUM_LIMIT,C.SCALETYPE FROM ERP_PURCHASE_LIST A ";
				$sql .= "LEFT JOIN ERP_PURCHASE_REQUISITION B ON A.PR_ID = B.ID ";
				$sql .= "LEFT JOIN ERP_CASE C ON B.CASE_ID = C.ID ";
				$sql .= "WHERE B.ID = $p_id";

				$purchaseInfo = M()->query($sql);

				if($purchaseInfo){
					$case_type = $purchaseInfo['0']['SCALETYPE'];
					$case_id = $purchaseInfo['0']['CASE_ID'];
				}

				//电商、分销、非我方收筹
				if ($case_type == 1 || $case_type == 2|| $case_type == 8) {
					$apply_amount = 0;
					foreach ($purchaseInfo as $key => $value) {
						$apply_amount += $value['PRICE_LIMIT'] * $value['NUM_LIMIT'];
					}
				}
			}

			if(!$case_id || !$apply_amount){
				$response['msg'] = g2u("参数有误!");
				die(@json_encode($response));
			}

			//算上签约未付（成本）
			$return = is_overtop_payout_limit($case_id,$apply_amount,1);

			if($return) {
				$response['status'] = true;
				$response['data']['state'] = true;
			}
			else
			{
				$response['status'] = false;
				$response['data']['state'] = false;
			}

			die(@json_encode($response));

		}

		//获取登录用户设置的工作流分组名称
		public function ajax_get_groupname(){
			$uid = $_SESSION['uinfo']['uid'];
			$sql = "select * from erp_group_flow where USERID = ".$uid. " order by  id asc";
			$returnArr = D()->query($sql);
			echo json_encode(g2u($returnArr));
		}


		//获得工作流分组里面用户名称
		public function getFlowGroupName(){
			$groupUserId = $_REQUEST['groupUserId'] ? $_REQUEST['groupUserId']:0;
			if($_REQUEST['actt']!=1){
				if($groupUserId[0] > 0){
					$groupId = end($groupUserId);
				}
				$groupId = $groupUserId;
				 
			}else{
				$groupId = $groupUserId;
			}
			if($groupId){
				 
				$groupUserStrId = M("Erp_group_flow")->where("ID=".$groupId)->getField("GROUPUSERID");
				$UserIdArr = explode(",",$groupUserStrId);
				if(is_array($UserIdArr) && count($UserIdArr) > 0){
					foreach($UserIdArr as $key=>$UserId){
						$SinUserName = M("Erp_users")->where("ID=".$UserId)->getField("NAME");
						$username = M("Erp_users")->where("ID=".$UserId)->getField("ID");

						$UserNameArr[$key]['USERNAME'] = $username;
						$UserNameArr[$key]['SinUserName'] = g2u($SinUserName);
					}
					//$UserNameStr = implode(",",$UserNameArr);
				}
			}
			echo json_encode($UserNameArr);
		}
	}
?>