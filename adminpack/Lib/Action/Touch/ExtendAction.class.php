<?php
	class ExtendAction extends Action{
        const MONEY_UNIT = '元';
        const PERCENT_MARK = '%';

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

        protected $workFlow = null;  // 工作流
        protected $menu = null;

        protected $flowId = null;  // 工作流ID
        protected $recordId = null;  // 对应的RECORD字段
        protected $record = null;  // record记录
        protected $flowType = null;  // 工作流类型
		protected $CASEID = null;  // WTF? Just Guess! Cuz I don't know either.
        protected $ACTIVID = null;  // WTF?

        /**
         * 是否论到当前用户处理
         * @var bool
         */
        protected $myTurn = false;

        /**
         * 可显示抄送至按钮的工作流类型
         * @var array
         */
        protected $showCCValues = array(
            'projectset',  // 立项申请
            'lixiangbiangeng',  // 立项变更
            'dulihuodong',  // 独立活动立项
            'xiangmuxiahuodong',  // 项目下活动申请
        );

        /**
         * web版可编辑业务内容的工作流
         * @var array
         */
        protected $webEditableFlowList = array(
            'dulihuodong',
            'xiangmuxiahuodong',
			'lixiangshenqing',
			'lixiangbiangeng',
            'dulihuodongbiangeng',
            'caigoushenqing',
            'xiangmuxiahuodongbiangeng',
            'zhihuanshenqing',
            'shoumai',
            'neibulingyong',
            'baosun',
        );

//        protected function getProcessProjectName() {
//            $this-
//            $this->assign('_project_name', todo);
//        }


		/**
		 * 初始化基类（相关权限判断）
		 */
		function _initialize(){
            $this->assign('isMobile', isMobile());  // 判断是否为移动设备
            $this->flowId = intval($_REQUEST['flowId']);  // 工作流id
            if (intval($this->flowId) > 0) {  // 如果是工作流审批
                $this->record = $this->getFlowRecord($this->flowId);
                if (!empty($this->record)) {
                    $this->recordId = $this->record['RECORDID'];  // 获取相应的recordId
                    $this->CASEID = $this->record['CASEID'];
                }
            } else { // 否则是工作流创建
                $this->recordId = $_REQUEST['recordId'];
            }

            //获取权限
            $this->authMyTurn($this->flowId);

            $this->initValuesAccordingFlowType();
            $this->assign('application', $this->record);
            $this->assign('flowId', $this->flowId);
            $this->assign('recordId', $this->recordId);
			$this->assign('CASEID', $this->CASEID);


			//authcode权限判断(session 赋值)
			$authcode = isset($_GET['authcode'])?trim($_GET['authcode']):'';
			if(!empty($authcode)) {
				//获取userid
				$authcode = get_authcode($authcode);
				$user_data = explode("$", $authcode);
				$uid =  $user_data[0];
				//session赋值
				$this->setUserSession($uid);
			}

			//模块和方法
			$m = MODULE_NAME;
			$a = ACTION_NAME;

			$model = $m.'/'.$a;//模块
			if(in_array($model,C('NONEPOWER'))) return;

            //创建工作流权限判断
            //$this->submitFlowAuth($this->flowId);

			//是否登录过期(跳转到登陆页面)
			if(!is_array($_SESSION['uinfo']) ) {
                if (isMobile()) {
                    echo "<script>location.href='".U("Index/login")."'</script>";
                } else {
                    echo "<script>location.href='".U("Admin/Index/login")."'</script>";
                }

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
//			$_SESSION['uinfo']['city'] = $channelid;
			

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
				js_alert('您的权限不足，请联系管理员！');
//				if(IS_AJAX){
//					$ress['msg'] = g2u('您的权限不足，请联系管理员');
//					die(@json_encode($ress));
//				}else{
//					$this->error('您的权限不足，请联系管理员！');
//				}
			}
		}

		/**
		 * 根据用户ID赋值用户的相关信息
		 * @param $username 用户ID
		 */
		protected function setUserSession($uid){
			//获取用户的相关信息
			$record = M('Erp_users')->where("USERNAME='".$uid."' ")->find();
			//获取用户所在部门的相关信息
			$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();
			//获取用户的相关城市权限
			$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']))) )));

			if(!$record['CITY'])
			{
				$dept = $this->ss_getuserdept($dept['PARENTID']);
				$record['CITY'] = intval($dept['CITY_ID']);
			}

			//城市拼音缩写
			$cond_where = "ID = ".$record['CITY'];
			$city_info = M('erp_city')->field('PY')->where($cond_where)->find();
			$user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';

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
				'city'=>$record['CITY'],//所属城市
				'city_py' => $user_city_py,//所属城市拼音
				'is_login_from_oa' => true,//是否来自OA
				//'lastLogin'=> $record['LOAN_LOGINTIME'],//用户上次登陆时间
				//'flow'=> $record['LOAN_FLOW']//流程权限
			);
		}

		/**
		 * 获取用户的相关信息
		 *
		 * @param $deptid
		 * @return  获取用户部门
		 */
		protected function ss_getuserdept($deptid){
			if($deptid)	$dept = M('erp_dept')->where('ID='.$deptid)->find();
			if($dept && $dept['CITY_ID']==null){
				$dept = $this->ss_getuserdept($dept['PARENTID']);
			}else{
				return $dept;
			}
			return $dept;
		}
		/**
		 * 用户的相关权限验证
		 *
		 * @param $m 模块
		 * @param $a 方法
		 * @return bool|void
		 */
        protected function roleAuth($m,$a){
            $groupid = $_SESSION['uinfo']['role'];//用户权限组
            $group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();

            $groupval = $group['LOAN_GROUPVAL'];

            if(empty($groupval)) return;//没有权限值哦
            $groupval = explode(',',$groupval);

            $status = false;
            $nop = false;//带参数的role如果存在却没有设置权限则认为无权限无需继续验证 LOAN_ROLEPARENTID<>0 and

            $record = M('erp_role')->where(" LOAN_ROLECONTROL='$m' and LOAN_ROLEACTION='$a' and LOAN_ROLEDISPLAY=0 and LOAN_PARAM is not null ")->select();//LOAN_ROLEDISPLAY 1为删除
            if (is_array($record)) {
                $maxParamsNum = 0;
                $maxMatchLoanRoleID = null;
                $maxMatchOrder = null;
                foreach ($record as $rval) {
                    if ($rval['LOAN_PARAM']) {
                        $cnt = 0;
                        $paramtemp = array_filter(explode(';', $rval['LOAN_PARAM']));
                        foreach ($paramtemp as $vv) {
                            $vvarr = explode('=', $vv);
                            $paramCheck = $_REQUEST[trim($vvarr[0])] == $vvarr[1] ? true : false;
                            if ($paramCheck) {
                                $cnt++;
                            } else {
                                break;
                            }
                        }
                        if (count($paramtemp) == $cnt) {
                            if ($cnt > $maxParamsNum) {
                                $maxParamsNum = $cnt;
                                $maxMatchLoanRoleID = $rval['LOAN_ROLEID'];
                                $maxMatchOrder = $rval['LOAN_ROLEORDER'];
                            } else if ($cnt == $maxParamsNum && $rval['LOAN_ROLEORDER'] < $maxMatchOrder) {
                                $maxMatchLoanRoleID = $rval['LOAN_ROLEID'];
                                $maxMatchOrder = $rval['LOAN_ROLEORDER'];
                            }
                        }
                    }
                }
                if ($maxParamsNum > 0) {
                    if (in_array($maxMatchLoanRoleID, $groupval)) {
                        $status = true;
                    } else {
                        $status = false;
                        $nop = true;
                    }
                }
            }

            if ($status == false && $nop == false) {
                $record = M('erp_role')->where("  LOAN_ROLECONTROL='$m' and LOAN_ROLEACTION='$a' AND LOAN_ROLEDISPLAY=0 and LOAN_PARAM is null ")->select();
                if (is_array($record)) {
                    foreach ($record as $rval) {
                        if ($rval['LOAN_ROLECONTROL'] == $m && $rval['LOAN_ROLEACTION'] == $a && in_array($rval['LOAN_ROLEID'], $groupval)) {
                            $status = true;
                        }
                    }
                }
            }
            if($status==false){
//                $status = $this->flowRoleAuth();  // todo comment by xuke@2016-07-14 验证工作流，要替换272的语句，考虑到可能存在的风险，暂时不替
                $status = $this->getFlowRole();
            }

            return $status;
        }

    /**
     * @return bool
     */
    function getFlowRole(){
			$status = false;
			if($_REQUEST['flowId']){
				$role_arr = array();
				$flows = M('Erp_flows')->where("ID=".$_REQUEST['flowId'])->find();
				if($flows )$flowset = M('Erp_flowset')->where("ID=".$flows['FLOWSETID'])->find();
				if($flowset) $flowrole = M('Erp_flowrole')->where('FLOWSETID='.$flowset['ID'])->find();
				if($flowrole){
					$role_arr = srt2arr($flowrole['FLOWEND'],$flowrole['FLOWNOT'],$flowrole['FLOWPASS'],$flowrole['FLOWTHROUGH']);
					if($role_arr){ 
						 
						if(in_array($_SESSION['uinfo']['role'],$role_arr)) {
							$status = true;
						}
						 
					}
				}
			} 
			return $status;
		}

    /**
     * 工作流权限验证
     * 只有在工作流节点中存在的用户才有查看的权限
     */
    protected function flowRoleAuth() {
        $response = false;
        if (intval($_REQUEST['flowId']) > 0) {
            $sql = <<<FLOW_ROLE_AUTH_SQL
                SELECT T.DEAL_USERID
                FROM ERP_FLOWNODE T
                WHERE T.FLOWID = %d
FLOW_ROLE_AUTH_SQL;
            $dbResult = D()->query(sprintf($sql, $_REQUEST['flowId']));
            if (notEmptyArray($dbResult) && $_SESSION['uinfo']['uid']) {
                $okUserList = array();
                foreach ($dbResult as $user) {
                    $okUserList []= $user['DEAL_USERID'];
                }
                $response = in_array($_SESSION['uinfo']['uid'], $okUserList);
            }
        }

        return $response;
    }

    /**
     * 获取工作流附件
     * @param $data
     * @return array
     */
    protected function getWorkFlowFiles($data) {
        $response = array();
        if ($data) {
            $temp = explode(',', $data);
            foreach($temp as $v) {
                $fileInfo = array(
                    'code' => '',
                    'name' => '',
                    'size' => ''
                );

                $singleFile = explode('-', $v);
                if (is_array($singleFile) && count($singleFile)) {
                    if (count($singleFile) > 1 && is_numeric($singleFile[count($singleFile) - 1])) {
                        $fileInfo['code'] = $singleFile[0];
                        $fileInfo['size'] = $singleFile[count($singleFile) - 1];
                        $fileInfo['size'] = ceil(floatval($fileInfo['size']) / 1024) . 'KB';
                        unset($singleFile[count($singleFile) - 1]);
                        unset($singleFile[0]);
                        $fileInfo['name'] = implode('-', $singleFile);
                    } else {
                        $fileInfo['name'] = $singleFile[0];
                    }
                }

                // 只显示有效的文件
                if (!empty($fileInfo['name']) && !empty($fileInfo['size']) && !empty($fileInfo['code'])) {
                    $response []= $fileInfo;
                }
            }
        }

        return $response;
    }


    /**
     * 根据工作流id找到固定工作流
     * @param $flowId
     * @return array|bool|mixed
     */
    protected function findFixedFlow($flowId) {
        $response = null;

        if (intval($flowId)) {
            $sql = <<<FLOWTYPE_SQL
                SELECT A.MAXSTEP,
                       B.FLOWCURRENT
                FROM ERP_FLOWS A
                INNER JOIN ERP_FIXEDFLOW B ON A.FLOWSETID = B.FLOWSETID
                WHERE A.ID = %d
                  AND B.CITY = A.CITY
FLOWTYPE_SQL;

            $response = D()->query(sprintf($sql, $flowId));
        } else {
            $sql = <<<FLOWTYP_SQL2
                SELECT FF.*
                FROM ERP_FLOWSET FS
                LEFT JOIN ERP_FLOWTYPE FT ON FT.ID = FS.FLOWTYPE
                INNER JOIN ERP_FIXEDFLOW FF ON FF.FLOWSETID = FS.ID
                AND FF.CITY = %d
                WHERE FT.PINYIN = %s
FLOWTYP_SQL2;
            $response = D()->query(sprintf($sql, $this->channelid, "'{$this->flowType}'"));
        }
        return $response;
    }

    /**
     * 获取工作流可用按钮
     * @param string $flowId
     * @return array
     */
    protected function availableButtons($flowId = '') {
        $response = array(
            'pass' => false,  // 通过
            'deny' => false,  // 否决
            'finish' => false,  // 备案
            'next' => false  // 下一步
        );

        $fixedFlow = $this->findFixedFlow($flowId); //判断是否是固定流
        if(is_array($fixedFlow) && count($fixedFlow)) {
            // 固定流情况
            $this->assign('isFixedFlow', true);
            $steps = explode(',',$fixedFlow[0]['FLOWCURRENT']);
            $response['deny'] = true;
            if ($flowId) {
                if ($fixedFlow[0]['MAXSTEP'] == count($steps)) {
                    $response['finish'] = true;
                } else if ($fixedFlow[0]['MAXSTEP'] < count($steps)) {
                    $response['next'] =true;
                }
            } else {
                $response['next'] = true;
            }
        } else {
            //自由流
            $this->assign('isFixedFlow', false);
            $data = $this->getFlowSet($flowId);
            $response['next'] = true;  // 转交下一步
            if ($flowId) {
                $isPass = M("Erp_flows")->where("ID = " . $flowId . " AND STATUS = 2")->find();
                if (!$isPass && $data['FLOWPASS'] && in_array($_SESSION['uinfo']['role'], explode(',', $data['FLOWPASS']))) {
                    $response['pass'] = true;
                }

                if (!$isPass && $data['FLOWNOT'] && in_array($_SESSION['uinfo']['role'], explode(',', $data['FLOWNOT']))) {
                    $response['deny'] = true;
                }

                if ($data['FLOWEND'] && in_array($_SESSION['uinfo']['role'], explode(',', $data['FLOWEND']))) {
                    $response['finish'] = true;
                }
            } else {
                if ($data['FLOWPASS'] && in_array($this->role, explode(',', $data['FLOWPASS']))) {
                    $response['pass'] = true;
                }
            }
        }

        return $response;
    }

    /**
     * 获取FlowSet
     * @param $flowId
     * @return mixed
     */
    protected function getFlowSet($flowId) {
        $sql = <<<FLOWSET_SQL
            SELECT B.ID,
                   B.JUDGEFLOWSQL,
                   A.RECORDID,
                   A.CASEID
            FROM ERP_FLOWS A
            LEFT JOIN ERP_FLOWSET B ON A.FLOWSETID = B.ID
            WHERE A.ID = %d
FLOWSET_SQL;

        $flowSet = D()->query(sprintf($sql, $flowId));
        if(is_array($flowSet) && count($flowSet)) {
            $flowSetId = $flowSet[0]['ID'];
            $judgeSql = $flowSet[0]['JUDGEFLOWSQL'];
            $recordId = $flowSet[0]['RECORDID'];
            $caseId = $flowSet[0]['CASEID'];

            $flowRole = D()->query(sprintf("SELECT * FROM ERP_FLOWROLE WHERE FLOWSETID = %d", $flowSetId));//获取流程角色
            if($judgeSql) {
                if(strstr($judgeSql,"%RECORDID%")) {
                    $flowsql = str_replace("%RECORDID%", $recordId, substr($judgeSql,0,-1));
                }

                if(strstr($judgeSql,"%CASEID%")) {
                    $flowsql = str_replace("%CASEID%", $caseId, substr($judgeSql,0,-1));
                }

                $sqlResult = D()->query($flowsql);
                if($flowRole){
                    foreach($flowRole as $key=>$val) {
                        if(isset($val['SIGN']) && isset($val['VALUE'])) {
                            $condition = intval($sqlResult[0]['RESULT']).$val['SIGN'].$val['VALUE'];
                            if(eval("return $condition;")) {
                                return $val;
                            }
                        }
                    }
                }
            } else {
                if($flowRole) {
                    foreach($flowRole as $key=>$val) {
                        return $val;
                    }
                }
            }
        }
    }

    /**
     * 获取工作流列表
     * @param $flowId
     * @return array|mixed|void
     */
    protected function getWorkFlows($flowId) {
        $response = array();
        if (!empty($flowId)) {
            $sql = <<<FLOW_SQL
            SELECT A.*,
                   B.NAME
            FROM ERP_FLOWNODE A
            LEFT JOIN ERP_USERS B ON A.DEAL_USERID = B.ID
            WHERE A.FLOWID = %d
            ORDER BY A.ID DESC
FLOW_SQL;
            $response = D()->query(sprintf($sql, $flowId));
        }

        return $response;
    }

    /**
     * 映射工作流数据
     * @param $workFlows
     * @return array
     */
    protected function mapWorkFlows($workFlows) {
        $response = array();
        if (is_array($workFlows) && count($workFlows)) {
            $step = count($workFlows);
            foreach($workFlows as $k => $v) {
                $response[$k] = $v;
                $response[$k]['step'] = $step--;
                // 格式化文件
                if (!empty($v['FILES'])) {
                    $response[$k]['FILES'] = $this->getWorkFlowFiles($v['FILES']);
                }
            }
        }
        return $response;
    }

    protected function getFlowRecord($flowId) {
        $response = array();
        if (intval($flowId)) {
            $sql = <<<FLOW_RECORD_SQL
                SELECT F.*, U.NAME AS USER_NAME
                FROM ERP_FLOWS F
                LEFT JOIN ERP_USERS U ON U.ID = F.ADDUSER
                WHERE F.ID = %d
FLOW_RECORD_SQL;
            $result = D()->query(sprintf($sql, $flowId));
            if (is_array($result) && count($result)) {
                $response = $result[0];
            }
        }

        return $response;
    }

    /**
     * 获取工作流的RecordID
     * @param $flowId
     * @return null
     */
    protected function getRecordID($flowId) {
        if (empty($flowId)) {
            return null;
        }

        $sql = <<<ACTIVITYID_SQL
            SELECT RECORDID
            FROM ERP_FLOWS
            WHERE ID = %d
ACTIVITYID_SQL;


        $result = D()->query(sprintf($sql, $flowId));
        if (is_array($result) && count($result)) {
            return $result[0]['RECORDID'];
        } else {
            return null;
        }
    }

    /**
     * 获取工作流信息并导入至模板中
     * @param $flowID
     */
        protected function assignWorkFlows($flowID) {
        if (intval($flowID) > 0) {
            $workFlows = $this->getWorkFlows($flowID);  
            if (is_array($workFlows) && count($workFlows)) {
                $this->menu['flow_graph'] = array(
                    'name' => 'flow-graph',
                    'text' => '流程图'
                );

                // 摘下审批意见，放到队列末尾
                if (!empty($this->menu['opinion'])) {
                    $tmpMenuItem = $this->menu['opinion'];
                    unset($this->menu['opinion']);
                    $this->menu['opinion'] = $tmpMenuItem;
                }

                $this->assign('applicationTime', sprintf('创建时间&nbsp;&nbsp;%s', $workFlows[count($workFlows) - 1]['E_TIME']));
                // 映射所有的审批记录
                $workFlows = $this->mapWorkFlows($workFlows);

                if (in_array($workFlows[0]['STATUS'], array(3, 4))) {
                    $this->unsetOpinionMenu();
                } else {   
                    // 当前工作流处于未处理或正在处理状态，且办理人是当前登录用户则显示审批意见面板
                    // 否则移掉审批意见面板

                    if ($workFlows[0]['DEAL_USERID'] != $_SESSION['uinfo']['uid']) {
                        $this->unsetOpinionMenu();
                    }
                }
                // 最后一条工作流，填充“申请说明”内容
                $this->assign('lastWorkFlow', $workFlows[count($workFlows) - 1]);
                // 所有工作流，填充“流程图”内容
                $this->assign('workFlows', $workFlows);

                // web是否有编辑的权限
                // 条件：是否当前审批人、是否是流程发起者、是否处于可编辑集合、是否为PC版
                $bizWebEditable = false;
                if (!isMobile()) {
                    if ($this->myTurn &&
                        $_SESSION['uinfo']['uid'] == $workFlows[count($workFlows) - 1]['DEAL_USERID'] &&
                        in_array($this->flowType, $this->webEditableFlowList)) {
                        $bizWebEditable = true;
                    }
                }

                $this->assign('bizWebEditable', $bizWebEditable);  
            }
        } else {
			//$bizWebEditable = in_array($this->flowType, $this->webEditableFlowList);
			//$this->assign('bizWebEditable', $bizWebEditable);
            unset($this->menu['application']);  // 移除审批意见菜单
        }
    }

    /**
     * 获取权限
     * @param $flowID
     */
    protected function authMyTurn($flowID){
        if($flowID>0) {
            $workFlows = $this->getWorkFlows($flowID);
            // 映射所有的审批记录
            $workFlows = $this->mapWorkFlows($workFlows);

            if (in_array($workFlows[0]['STATUS'], array(1, 2)) && $workFlows[0]['DEAL_USERID'] == $_SESSION['uinfo']['uid'])
                $this->myTurn = true;
            $this->assign('myTurn',$this->myTurn);
        } else {  // 如果是提交工作流，则查看创建者是不是当前审批用户
            $creator = D('erp_project')->where("ID = {$this->recordId}")->getField('CUSER');
            if ($creator == $_SESSION['uinfo']['uid']) {
                $this->myTurn = true;
            } else {
                $this->myTurn = false;
            }
        }
    }

    /**
     *
     */
    protected function unsetOpinionMenu() {
//        unset($this->menu['application']);  // 移除申请说明菜单
        unset($this->menu['opinion']);  // 移除审批意见菜单
    }
	//功能点权限判断
		public function haspermission($id){
			$groupid = $_SESSION['uinfo']['role'];//用户权限组
			$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();
			$groupval = $group['LOAN_GROUPVAL'];
			$groupval = explode(',',$groupval);
			if(in_array($id,$groupval)) return true;
			else return false;

		}

    /**
     * 根据工作流类型进行一些初始化操作
     */
    private function initValuesAccordingFlowType() {
        $colors = D('Flowtype')->get_status_color();
        if (!empty($_REQUEST['flowTypePinYin'])) {
            $this->flowType = $_REQUEST['flowTypePinYin'];
        }

        $labelBackgroundColor = $colors[$this->flowType];
        if ($labelBackgroundColor) {  // 设置标签背景色
            $this->assign('labelStyle', sprintf("style='background-color:%s'", $labelBackgroundColor));
        }

        if (empty($this->flowType)) {
            $this->flowType = $_REQUEST['flowType'];
        }

        if (in_array($this->flowType, $this->showCCValues)) {  // 设置是否显示抄送框
            $this->assign('showCC', true);
        } else {
            $this->assign('showCC', false);
        }
        $this->assign('flowType', $this->flowType);
    }

    /**
     * 是否有提交工作流的权限
     */
    private function submitFlowAuth($flowId){
        //模块和方法
        $module = MODULE_NAME;
        $action = ACTION_NAME;

        $authMapKey = $module . '_' . $action;

        //权限MAP
        /**这些**/
        $authMap = array(
            //小蜜蜂
            'PurchasingBee_show'=>700,
            //预算其他
            'BenefitFlow_process'=>461,
            //会员退票
            'ChangeInvoice_process'=>308,
            //减免申请
            'MemberDiscount_process'=>355,
            //借款申请
            'Loan_process'=>620,
            //项目决算
            'Finalaccounts_show'=>282,
            //项目终止
            'ProjectTermination_show'=>281,
            //独立活动
            //项目下活动
            //独立活动变更
            //项目下活动变更
            //'Activ_process'=>1,
            //立项申请
            'ProjectSet_show'=>169,
            //会员退票
            'InvoiceRecycle_process'=>308,
            //垫资比例调整
            'Payout_change_process'=>289,
            //退款申请
            'MemberRefund_process'=>370,
            //合同开票
            'Advert_process'=>743,
            //标准调整
            'Feescale_change_process'=>356,
            //立项变更
            'ProjectChange_show'=>448,
            //成本划拨
            'Cost_process'=>451,
            //非付现成本申请
            'PurchaseNocash_process'=>500,
            //采购申请(按钮已经做隐藏)
            //'Purchase_process'=>1,
            //业务津贴
            'Benefits_process'=>456,
        );

        //权限判断
        if(!$flowId && $authMap[$authMapKey] && !in_array($authMap[$authMapKey],$this->getUserAuthorities()))
        {
            js_alert('对不起，您不必备创建该工作流的权限！');
            exit();
        }
    }

    protected function getUserAuthorities() {
        $response = array();

        $groupID = $_SESSION['uinfo']['role'];//用户权限组
        if (!empty($groupID)) {
            $group = M('erp_group')->where("LOAN_GROUPID='$groupID' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();
            $groupVal = $group['LOAN_GROUPVAL'];

            if (!empty($groupVal)) {
                $response = explode(',', $groupVal);
            }
        }

        return $response;
    }
}
?>