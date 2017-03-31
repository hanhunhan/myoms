<?php
	class ExtendAction extends Action{

        const HTML_A_TAG_PREG = "|(<a.*?id\s*=\s*[\"'](.*?)[\"'].*?>.*?</a>)|";

        /**
         * 显示新增（提交……)等按钮
         */
        const SHOW_OPTION_BTN = 1;

        /**
         * 隐藏新增（提交……）等按钮
         */
        const HIDE_OPTION_BTN = 2;

        /**
         * 电商业务ID
         */
        const DS = 1;

        /**
         * 分销业务ID
         */
        const FX = 2;

        /**
         * 硬广业务ID
         */
        const YG = 3;

        /**
         * 活动业务ID
         */
        const HD = 4;

        /**
         * 产品业务ID
         */
        const CP = 5;

        /**
         * 项目下活动业务ID
         */
        const XMXHD = 7;

        /**
         * 非我方收筹业务ID
         */
        const FWFSC = 8;

        /**
         * 默认Excel表高度
         */
        const DEFAULT_EXCEL_ROW_HEIGHT = 30;

        /**
         * 默认Excel列宽
         */
        const DEFAULT_EXCEL_COLUMN_WIDTH = 20;

        /**
         * 项目类型名称缩略映射表
         */
        protected $scaleTypeAliasMap = array(
            self::DS => 'ds',
            self::FX => 'fx',
            self::YG => 'yg',
            self::HD => 'hd',
            self::FWFSC => 'fwfsc',
            self::XMXHD => 'xmxhd',
            self::CP => 'cp'
        );

        protected $authorityMap;  // 权限映射表
        protected $options;  // 操作表
        protected $projectContextMenuScope = array(
            'benefits' => array(self::DS, self::YG, self::FWFSC, self::HD, self::FX),  // 津贴
            'otherBenefits' => array(self::DS, self::FWFSC, self::FX),  // 预算其他
            'feeScaleChange' => array(self::DS, self::FX, self::FWFSC),  // 标准调整
            'payoutChange' => array(self::DS, self::FX, self::FWFSC)  // 垫资比例调整
        );

		//右击菜单的权限树
		protected $authContextMenu = array(
			//项目详情
			'Project/projectDetail'=>783,
			//电商采购
			'Purchase/purchase_manage_ds'=>191,
			//分销采购
			'Purchase/purchase_manage_fx'=>430,
			//硬广采购
			'Purchase/purchase_manage_yg'=>435,
			//活动采购
			'Purchase/purchase_manage_hd'=>440,
			//非我方收筹采购
			'Purchase/purchase_manage_fwfsc'=>420,
			//采购管理
			'Purchasing/index'=>230,
			//项目权限
			'House/projectAuth'=>164,
			//业务津贴
			'Benefits/benefits'=>163,
			//资金池费用申请
			'Benefits/fundPoolCost'=>784,
			//预算外其他
			'Benefits/otherBenefits'=>221,
			//借款申请
			'Loan/loan_application'=>326,
			//垫资比例
			'Payout_change/payout_change'=>288,
			//电商会员
			'Member/main'=>147,
			//分销会员
			'MemberDistribution/manage'=>310,
		);

		public $noLoginWaitSecond = '0';
		public $noLoginMessage = '你的登录过期，页面跳转中~';
		public $noPowerCity = '你没有城市的权限';
		public $noPowerFrom = '你没有条口权限';
		public $channelid = '';//当前城市权限
		public $channelid_py = '';//当前城市权限
		public $testidd = '';//当前城市权限
		public $powercity = '';//所有城市权限
		public $allpower = '';
		public $power = '';//当前条口
		//public $nodelist ='';//所有的公海权限
		//public $nodename = '';
		public $city_config_array;
		public $city_config;
		public $p_auth_all;
		public $p_vmem_all;//查看全部会员
		protected function roleAuth($m,$a){
			$groupid = $_SESSION['uinfo']['role'];//用户权限组
			$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();

			$groupval = $group['LOAN_GROUPVAL'];

			if(empty($groupval)) return;//没有权限值哦
			$groupval = explode(',',$groupval);
			//print_r($groupval);

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
				$status = $this->getFlowRole();
			}

			return $status;
		}//权限认证

		function getFlowRole(){
			$status = false;
			if($_REQUEST['flowId']){
				$role_arr = array();
				$flows = M('Erp_flows')->where("ID=".$_REQUEST['flowId'])->find();
				if($flows )$flowset = M('Erp_flowset')->where("ID=".$flows['FLOWSETID'])->find();
				if($flowset) $flowrole = M('Erp_flowrole')->where('FLOWSETID='.$flowset['ID'])->find();
				if($flowset) $fixedflowrole = M('Erp_fixedflow')->where('FLOWSETID='.$flowset['ID'])->find();
				if($flowrole){
					$role_arr = srt2arr($flowrole['FLOWEND'],$flowrole['FLOWNOT'],$flowrole['FLOWPASS'],$flowrole['FLOWTHROUGH'],$fixedflowrole['FLOWCURRENT']);
					if($role_arr){

						if(in_array($_SESSION['uinfo']['role'],$role_arr)) {
							$status = true;
						}

					}
				}
			}
			return $status;
		}
		function _initialize(){

			$a = ACTION_NAME;
			$m = MODULE_NAME;

			 $model = $m.'/'.$a;//模块
			/*
			echo $model;
			echo "<br/>";

			print_r( C('NONEPOWER'));
			die('111');
			*/
			if(in_array($model,C('NONEPOWER'))) return;


			if(!is_array($_SESSION['uinfo']) ) {

				if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
					$ress['msg'] =g2u('您长时间未操作登陆已失效，请重新登陆！');
					$ress['info'] =g2u('您长时间未操作登陆已失效，请重新登陆！');
					$ress['status'] ='noauth';
					exit( json_encode($ress));
				}else{
					echo "<script>parent.location.href='".U("Index/login")."'</script>";
				}

				exit();
			}//是否登录过期

			$channel = $_SESSION['uinfo']['pocity'];

			if(empty($channel) && empty($_SESSION['uinfo']['city'])){
			//if( empty($_SESSION['uinfo']['city'])){
			    js_alert('无城市权限！ ');
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
			// echo $channelid;

			if(!$channelid ){
				//$channelid = $_SESSION['uinfo']['city'];//$channel[0];
				$channelid = $channel[0];
			}
			$this->channelid = $channelid;
			$channelid_py = M('erp_city')->where("ISVALID=-1 AND ID = " . $this->channelid)->find();
			$this->channelid_py = $channelid_py['PY'];
			//$_COOKIE['CHANNELID'] = $channelid;//add by gehaifeng

			cookie('CHANNELID',$channelid,3600*24);//城市id
			$_SESSION['uinfo']['city'] = $channelid;

			//$city_config_array = C('city_config_array');
			$cityarr = M('erp_city')->where("ISVALID=-1")->select();
			foreach($cityarr as $v){
				$this->city_config_array[$v['ID']] = $v['PY'];
				$this->city_config[$v['ID']] = $v['NAME'];
			}
			$this->city = $this->city_config_array[$channelid]; //echo $channelid;

			$_SESSION['uinfo']['city_py'] = $this->city;
			cookie('CITYEN',$this->city);//城市拼音简写

			//给予默认城市


			if($_SESSION['uinfo']['p_auth_all']==1){
				$this->p_auth_all=true;
			}
			if($_SESSION['uinfo']['p_vmem_all']==1){
				$this->p_vmem_all=true;
			}

			if(in_array($model,C('NONEROLE'))) {  return ; }//这个页面不做角色判断哦
			//下面开始角色认证了！
			$auth = $this->roleAuth($m,$a);
			//echo $_SESSION['uinfo']['uname'];

			if($auth==false){
				//if(IS_AJAX){
				if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
					$ress['msg'] =g2u('您的权限不足，请联系管理员');
					$ress['info'] =g2u('您的权限不足，请联系管理员');
					$ress['status'] ='noauth';
					exit( json_encode($ress));
				}else{
					//$this->error('您的权限不足，请联系管理员！',U("Index/welcome"));
					$this->error('您的权限不足，请联系管理员！' );
				}
				exit();
			}

            //$this->loan_city_en = $_COOKIE['loan_city_en'];
		}
		//公共子页面
        function publicChildren(){
			//if($_REQUEST['showForm'] && empty($_REQUEST['budgetid'])  )exit("<script>alert('请先添加立项预算！');self.location=document.referrer;</script>");
			Vendor('Oms.Form');
			$form = new Form();
			$form =  $form->initForminfo($_REQUEST['parentFormNo'])->where($_REQUEST['parentField']."='".$_REQUEST['parentId']."'")->setMyFieldVal($_REQUEST['parentField'],$_REQUEST['parentId'],true)->getResult();
			$this->assign('form',$form);
			$this->display('public/publicChildren');

		 }


        //页签
		public function getTabs($tabsId,$param){
			$groupid = $_SESSION['uinfo']['role'];//用户权限组
			$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();
			$groupval = $group['LOAN_GROUPVAL'];
			$groupval = explode(',',$groupval);

			$status = $this->getFlowRole();
			$tablist = M('Erp_tabs_role')->where("TABSID=".$tabsId)->order("QUEUE ASC")->select();
			foreach($tablist as $k=>$v){
				$role = M('Erp_role')->where("LOAN_ROLEID=".$v['ROLEID'])->find();
				if( $v['ISOPINION']==-1 && MODULE_NAME ==$role['LOAN_ROLECONTROL'] && ACTION_NAME ==$role['LOAN_ROLEACTION']){
					//$showflag = true;
					$param['showOpinion'] =1;
				}
			}
			foreach($tablist as $k=>$v){
				$showflag = true;
				$role = M('Erp_role')->where("LOAN_ROLEID=".$v['ROLEID'])->find();
				if($v['ISOPINION']==-1 && empty($_REQUEST['flowId'])){
					$showflag = false;
				}
				if(MODULE_NAME ==$role['LOAN_ROLECONTROL'] && ACTION_NAME ==$role['LOAN_ROLEACTION']){
					$showflag = true;
					//$param['showOpinion'] =1;
				}
				if($_REQUEST['showOpinion']==1){
					$showflag = true;
					$param['showOpinion'] =1;
				}

                $flowID = $_REQUEST['flowId'];
                if (!empty($flowID) && intval($flowID) > 0 && intval($v['SHOW_IN_OPINION']) != -1) {
                    $showflag = false;
                }

				if($showflag ){
					if(in_array($v['ROLEID'],$groupval) || $status) { 
						//$role = M('Erp_role')->where("LOAN_ROLEID=".$v['ROLEID'])->find();
						$tempp = explode(';',$role['LOAN_PARAM']);
						$role['param'] = implode('&',$tempp);
						$urlori = U($role['LOAN_ROLECONTROL'].'/'.$role['LOAN_ROLEACTION'].'/'.'?'.$role['param']);
						$url = U($role['LOAN_ROLECONTROL'].'/'.$role['LOAN_ROLEACTION'].'/'.'?'.$role['param'],$param);
						$self = substr(__SELF__,-1)=='/'? substr(__SELF__,0,strlen(__SELF__)-1):__SELF__;
						//$self = U( MODULE_NAME .'/'. ACTION_NAME .'?'.$role['param'],$param);
						$selected = '';
                        if (MODULE_NAME ==$role['LOAN_ROLECONTROL'] && ACTION_NAME ==$role['LOAN_ROLEACTION']) {
                            $selected = ' class="selected"';
                        }
//						$selected = ($url== $self || $urlori==$self)? ' class="active"' : '';
						$tabhtml .= '<li '.$selected.' ><a href="'.$url.'">'.$v['NAME'].'</a></li>';
					}
				}
			} 

			return $tabhtml = '<ul>'.$tabhtml.'</ul>';
		}

		//项目权限判断 项目id   业务类型 （数组或字符串）
		public function project_auth($prjid,$scaletype,$flowId=0){
            //echo 1111111;
			if(!$this->p_auth_all){
				if(is_array($scaletype))$scaletype = implode(',',$scaletype);
				$auth = M('Erp_prorole')->where("PRO_ID=$prjid and ERP_ID in($scaletype) and ISVALID=-1  and USE_ID='".$_SESSION['uinfo']['uid']."'")->find();
                //var_dump($auth);die;
				if(!$auth){
					$role_arr = array();
					$flows = M('Erp_flows')->where("ID='$flowId'")->find();
					if($flows )$flowset = M('Erp_flowset')->where("ID=".$flows['FLOWSETID'])->find();
					if($flowset) $flowrole = M('Erp_flowrole')->where('FLOWSETID='.$flowset['ID'])->find();
					if($flowrole){
						$role_arr = srt2arr($flowrole['FLOWEND'],$flowrole['FLOWNOT'],$flowrole['FLOWPASS'],$flowrole['FLOWTHROUGH']);
					}
					if(!in_array($_SESSION['uinfo']['role'],$role_arr )){
						if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
							$ress['msg'] =g2u('您的权限不足，请联系管理员');
							$ress['info'] =g2u('您的权限不足，请联系管理员');
							exit( json_encode($ress));
						}else{
							$this->error('您的项目权限不足，请联系项目主管！',U("Case/projectlist"));
							//$this->error('您的权限不足，请联系管理员！' );
							exit();
						}
						exit();
					}


				}
			}
		}
		//立项信息权限
		public function project_pro_auth($prjid,$flowId=0){
			$flag = true;
			if(!$this->p_auth_all){
				$project = M('Erp_project')->where("ID='$prjid'")->find();
				if( $project['CUSER'] != $_SESSION['uinfo']['uid'] ){
					$role_arr = array();
					$flows = M('Erp_flows')->where("ID='$flowId'")->find();
					if($flows )$flowset = M('Erp_flowset')->where("ID=".$flows['FLOWSETID'])->find();
					if($flowset) $flowrole = M('Erp_flowrole')->where('FLOWSETID='.$flowset['ID'])->find();
					if($flowrole){
						$role_arr = srt2arr($flowrole['FLOWEND'],$flowrole['FLOWNOT'],$flowrole['FLOWPASS'],$flowrole['FLOWTHROUGH']);
					}
					if(!in_array($_SESSION['uinfo']['role'],$role_arr )){
						$flag = false;
					}else $flag = true;
				}else $flag = true;
			}else $flag = true;

			if(!$flag){
				if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
					$ress['info'] = $ress['msg'] = g2u('您的权限不足，请联系管理员');
					exit( json_encode($ress));
				}else{
					$this->error('您无权查看立项信息！',U("Case/projectlist") );
					exit();
				}
			}
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
        //项目业务权限判断
		public function project_case_auth($prjid){
			$auth=false;
			if(!$this->p_auth_all){
				// $list = M('Erp_case')->where('SCALETYPE<6 and PROJECT_ID='.$prjid)->select();
				$list = M('Erp_case')->where('SCALETYPE<>7 and PROJECT_ID='.$prjid)->select();
				foreach($list as $val){
					$one = M('Erp_prorole')->where("PRO_ID='".$val['PROJECT_ID']."' and ERP_ID='".$val['SCALETYPE']."' and ISVALID=-1 and USE_ID='".$_SESSION['uinfo']['uid']."'")->find();
					if(!$one){
						$auth=false;
						break;
					}$auth=true;
				}
			}else $auth=true;
			if($auth==false){
				$auth = $this->getFlowRole();
			}
			if($auth==false){
				//if(IS_AJAX){
				if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
					$ress['msg'] =g2u('您的项目权限不足');
					$ress['info'] =g2u('您的项目权限不足');
					$ress['status'] ='noauth';
					exit( json_encode($ress));
				}else{
					$this->error('您的项目权限不足！',U("Case/projectlist"));

				}
				exit();
			}
		}
     /**
      * 小蜜蜂采购众客api
      *
      */
	protected function _zk_api($purchase_info,$purchase_list_info){
	    if ( isset($purchase_list_info['ZK_STATUS']) && $purchase_list_info['ZK_STATUS']==1){
	        return false;
	    }
	    //获取城市简拼
	    $model_city = D('City');
	    $city = $model_city->get_city_info_by_id($purchase_info['CITY_ID']);
	    $citypy = strtolower($city["PY"]);
	    //获取项目房产信息
	    $house =  D('Erp_house')->where("PROJECT_ID =".$purchase_info['PRJ_ID'])->find();
	    //获取项目信息
	    $prgect= D('Project')->find($purchase_info['PRJ_ID']);

	    //数据整合
	    $param = array(
	        'prj_id' => $purchase_info['PRJ_ID'],
	        'prj_name' => mb_convert_encoding($prgect['PROJECTNAME'], 'UTF-8','GBK'),
	        'p_id' => $purchase_list_info['ID'],
	        'p_name' => mb_convert_encoding($purchase_info['REASON'], 'UTF-8','GBK'),
	        'price_limit' => $purchase_list_info['PRICE_LIMIT'],
	        'num_limit' => $purchase_list_info['NUM_LIMIT'],
	        'city' => $citypy,
	        'pro_listid' => $house['PRO_LISTID'],
	        'rel_newhouseid' => $house['PRO_LISTID'],
	        'rel_newhouse' => mb_convert_encoding($house['REL_PROPERTY'], 'UTF-8','GBK'),
	        'end_time' => strtotime($purchase_info['END_TIME']),
	        'key' => md5(md5($purchase_list_info['ID'].$citypy)."BEE"),
	    );
	    //写入众客系统
        $api = ZKAPI1;
        return curlPost($api, $param);
    }

        /**
         * 采购页面是否显示可操作按钮
         * @param $caseID
         * @return int
         */
        protected function isShowOptionBtn($caseID) {
            if (empty($caseID)) {
                return self::SHOW_OPTION_BTN;
            }

            $condition = "ID = {$caseID} AND FSTATUS IN (3, 5)";
            if (D('ProjectCase')->where($condition)->count()) {
                return self::HIDE_OPTION_BTN;
            }

            return self::SHOW_OPTION_BTN;
        }

        /**
         * 项目列表中的上下文菜单是否可用
         * @param $projectID
         * @param $scope
         * @return int
         */
        protected function isProjectContextMenuRunnable($projectID, $scope) {
            if (empty($projectID)) {
                return self::SHOW_OPTION_BTN;
            }

            $sql = "
                SELECT c.fstatus status,
                       c.id,
                       c.scaleType
                FROM erp_case c
                WHERE c.project_id = {$projectID}
                AND c.scaleType <> 7
            ";

            $flag = false;  // 默认上下文菜单打开的页面是不可以做新增、提交等操作的
            $records = D('ProjectCase')->query($sql);
            if (is_array($records) && count($records)) {
                foreach ($records as $record) {
                    $flag = $flag || $this->checkCaseStatus($record, $scope);
                }
            }

            return $flag ? self::SHOW_OPTION_BTN : self::HIDE_OPTION_BTN;
        }

        /**
         * 检查案例所处的状态，以此作为项目的上下文菜单是否可用的标志
         * @param $data
         * @param $scope
         * @return bool
         */
        private function checkCaseStatus($data, $scope) {
            if (!is_array($data) || count($data) == 0) {
                return false;
            }

            // 如果该案例类型不存在相关的操作
            if (empty($data['SCALETYPE']) || !in_array($data['SCALETYPE'], $this->projectContextMenuScope[$scope])) return false;
            // 项目已终止或决算 且 已进入审核或审核完成，则不可以显示可选操作
            if (!empty($data['STATUS'])
                && !in_array($data['STATUS'], array(2,3,4))
            ) {
                return false;
            }
            return true;
        }

        /**
         * 检查是否拥有页签组中任意一个页签的权限
         * @param $tabNum
         * @return array
         */
        protected function checkTabAuthority($tabNum) {
            $groupID = $_SESSION['uinfo']['role'];//用户权限组
            $strGroups = M('erp_group')->where("LOAN_GROUPID='{$groupID}' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->getField('LOAN_GROUPVAL');

            if(empty($strGroups)) {
                return array(
                    'result' => false,
                    'msg' => '用户所在分组没有权限值'
                );
            }
            $arrGroups = explode(',', $strGroups);

            $sql = "
                SELECT t.ROLEID AS ID
                FROM erp_tabs_role t
                WHERE t.TABSID = {$tabNum}
                AND t.ISOPINION = 0
                ORDER BY t.QUEUE

            ";
            $roleIDs = D()->query($sql);
            if (is_array($roleIDs) && count($roleIDs)) {
                foreach($roleIDs as $roleID) {
                    if (in_array($roleID['ID'], $arrGroups)) {
                        return array(
                            'result' => true,
                            'msg' => '获取第一个有权限页签',
                            'roleID' => $roleID['ID']
                        );
                    }
                }
            }

            return array(
                'result' => false,
                'msg' => '用户没有该页签下的权限'
            );
        }

        protected function checkSingleTabAuthority($method, $action, $tabNum) {
            $sql = "
                SELECT t.ROLEID AS ID
                FROM erp_tabs_role t
                WHERE t.TABSID = {$tabNum}
                AND t.ISOPINION = 0
                ORDER BY t.QUEUE

            ";
            $roleIDs = D()->query($sql);
            if (is_array($roleIDs) && count($roleIDs)) {
                $maxParamsNum = 0;
                $maxMatchLoanRoleID = null;
                foreach($roleIDs as $roleID) {
                    $sql = "
                        SELECT t.LOAN_ROLEID ID, t.LOAN_PARAM
                        FROM erp_role t
                        where t.LOAN_ROLECONTROL = '{$method}'
                        AND t.LOAN_ROLEACTION = '{$action}'
                        AND t.LOAN_ROLEID = {$roleID['ID']}
                    ";
                    $roleRecord = D()->query($sql);
                    if (is_array($roleRecord) && count($roleRecord)) {
                        if ($roleRecord['LOAN_PARAM']) {
                            $cnt = 0;
                            $paramtemp = array_filter(explode(';', $roleRecord['LOAN_PARAM']));
                            foreach ($paramtemp as $vv) {
                                $vvarr = explode('=', $vv);
                                $paramCheck = $_REQUEST[trim($vvarr[0])] == $vvarr[1] ? true : false;
                                if ($paramCheck) {
                                    $cnt++;
                                } else {
                                    break;
                                }
                            }
                            if ($cnt > $maxParamsNum) {
                                $maxParamsNum = $cnt;
                                $maxMatchLoanRoleID = $roleRecord['ID'];
                            }
                        }
                    }
                }
                return $maxMatchLoanRoleID;
            }

            return false;
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

        /**
         * @param $objActSheet worksheet 对象
         * @param $data 待导出的数据
         * @param $row  起始行
         * @param $title 标题
         * @param $map 字段配置
         * @param array $cellStyle 样式
         */
        protected function commonExportAction(&$objActSheet, $data, &$row, $title, $map, $cellStyle = array()) {
            // 标题行
            $objActSheet->setCellValueExplicitByColumnAndRow(0, $row, iconv("gbk//ignore", "utf-8//ignore", $title));
            $objActSheet->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
            $objActSheet->mergeCellsByColumnAndRow(0, $row, count($map) - 1, $row);

            $row++;
            $column = 0;
            $objActSheet->getRowDimension($row)->setRowHeight(self::DEFAULT_EXCEL_ROW_HEIGHT);
            foreach ($map as $key => $val) {
                $width = empty($val['width']) ? self::DEFAULT_EXCEL_COLUMN_WIDTH : $val['width'];
                if ($objActSheet->getColumnDimensionByColumn($column)->getWidth() < $width) {
                    $objActSheet->getColumnDimensionByColumn($column)->setWidth($width);
                }

                $objActSheet->getStyleByColumnAndRow($column, $row)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000')
                            )
                        ),
                        'font' => array(
                            'bold' => true
                        )
                    )
                );

                $value = $val['name'];
                $value = iconv("gbk//ignore", "utf-8//ignore", $value);
                $objActSheet->setCellValueExplicitByColumnAndRow($column, $row, $value);
                $column++;
            }

            // 记录列表
            foreach($data as $item) {
                $row++;
                $column = 0;
                foreach($map as $k => $v) {
                    if (array_key_exists($k, $item)) {
                        $value = $item[$k];
                        // 映射数据
                        if (array_key_exists('map', $v)) {
                            $value = $v['map'][$value];
                        }
                        // 转码操作
                        $value = !empty($value) ? iconv("gbk//ignore", "utf-8//ignore", $value) : '';
                        if (empty($value)) {
                            if (array_key_exists('dataType', $v) && $v['dataType'] == 'number') {
                                $value = 0;
                            }
                        }

                        $objActSheet->setCellValueExplicitByColumnAndRow($column, $row, $value);
                        $objActSheet->getStyleByColumnAndRow($column, $row)->applyFromArray($cellStyle);
                    }
                    $column++;
                }
            }
        }

        /**
         * 初始化导出工作
         * @param $objPHPExcel PHPExcel对象
         * @param $objActSheet  worksheet对象
         * @param string $worksheetTitle worksheet标题
         * @param int $width  默认列宽
         * @param int $height 默认行高
         */
        protected function initExport(&$objPHPExcel, &$objActSheet, $worksheetTitle, $width = 20, $height = 20) {
            try {
                Vendor('phpExcel.PHPExcel');
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0);
                $objActSheet = $objPHPExcel->getActiveSheet();

                $objActSheet->getDefaultRowDimension()->setRowHeight($height);//默认行高
                $objActSheet->getDefaultColumnDimension()->setWidth(-1);//默认列宽
                $objActSheet->setTitle(iconv("gbk//ignore", "utf-8//ignore", $width));
                $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore", "utf-8//ignore", '宋体'));
                $objActSheet->getDefaultStyle()->getFont()->setSize(10);
                $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
                $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objActSheet->getRowDimension('1')->setRowHeight(40);
                $objActSheet->setTitle(iconv("gbk//ignore", "utf-8//ignore", $worksheetTitle));

            } catch (Exception $e) {
                die(sprintf('%s:%s', $e->getCode(), $e->getMessage()));
            }
        }

		/**
		 * 返回上下文菜单
		 * @param $caseId 案列ID
		 * @return bool|string 返回菜单json格式
		 */
		protected function getContextMenu($caseId){
			$contextMenu = array();

			if(!$caseId)
				return false;

			//获取项目信息
			$proSql = <<< ET
		SELECT C.SCALETYPE,P.PROJECTNAME,P.ID,P.BSTATUS FROM ERP_PROJECT P INNER JOIN ERP_CASE C ON P.ID = C.PROJECT_ID WHERE C.ID = {$caseId}
ET;
			$projectInfo = D()->query($proSql);

			//业务类型
			$scaleType = $projectInfo[0]['SCALETYPE'];
			$scaleTypePY = $this->scaleTypeAliasMap[$scaleType];
			$bStatus = intval($projectInfo[0]['BSTATUS']);

			//上下文菜单
			$contextMenu = array(
				array(
					'header'=>$projectInfo[0]['PROJECTNAME'],
					'show'=>1,
				),
				array(
					'icon'=>'glyphicon glyphicon-king',
					'text'=>'授予项目权限',
					'href'=>U('House/projectAuth','prjid='.$projectInfo[0]['ID']),
					'target'=>'_self',
					'title'=>'',
					'auth'=>'House/projectAuth',
					'show'=>1,
				),
//				array(
//					'icon'=>'glyphicon glyphicon-equalizer',
//					'text'=>'项目执行详情',
//					'href'=>U('Project/projectDetail','omsPId='.$projectInfo[0]['ID'].'&omsCaseId='.$caseId),
//					'target'=>'_blank',
//					'title'=>'',
//					'auth'=>'Project/projectDetail',
//					'show'=>1,
//				),
				array(
					'divider'=>true,
					'show'=>1,
				),
				array(
					'icon'=>'glyphicon glyphicon-shopping-cart',
					'text'=>'采购管理',
					'href'=>U('Purchasing/index'),
					'target'=>'_self',
					'auth'=>'Purchasing/index',
					'show'=>1,
				),
				array(
					'divider'=>true,
					'show'=>1,
				),
				array(
					'icon'=>'glyphicon glyphicon-jpy',
					'text'=>'业务津贴申请',
					'href'=>U('Benefits/benefits','prjid='.$projectInfo[0]['ID']),
					'target'=>'_self',
					'auth'=>'Benefits/benefits',
					'show'=>1,
				),
			);

			//“预算外其他申请”只有电商、分销、非我方收筹有
			if($scaleTypePY=='ds' || $scaleTypePY=='fx' || $scaleTypePY=='fwfsc'){
				$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-jpy',
					'text'=>'预算外其他申请',
					'href'=>U('Benefits/otherBenefits','prjid='.$projectInfo[0]['ID']),
					'target'=>'_self',
					'auth'=>'Benefits/otherBenefits',
					'show'=>1,
				);
			}

			//“资金池费用申请”只有电商有(并且不是终止状态)、是资金池的电商项目
			if($scaleTypePY=='ds' && $bStatus != 5 && D('House')->get_isfundpool_by_prjid($projectInfo[0]['ID'])){
				$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-jpy',
					'text'=>'资金池费用申请',
					'href'=>U('Benefits/fundPoolCost','prjId='.$projectInfo[0]['ID'].'&TAB_NUMBER=15'),
					'target'=>'_self',
					'auth'=>'Benefits/fundPoolCost',
					'show'=>1,
				);
			}

			$contextMenu[] = array(
				'icon'=>'glyphicon glyphicon-jpy',
				'text'=>'借款申请',
				'href'=>U('Loan/loan_application'),
				'target'=>'_self',
				'auth'=>'Loan/loan_application',
				'show'=>1,
			);

			/**** 如果是电商和分销则加上会员管理*****/
			if($scaleTypePY == 'ds' || $scaleTypePY == 'fx'){
				$contextMenu[] = array(
					'divider'=>true,
					'show'=>1,
				);
				$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-user',
					'text'=>'会员管理',
					'href'=>U('Member/RegMember','TAB_NUMBER=22&search1=PRJ_NAME&search1_s=3&search1_t='. urlencode($projectInfo[0]['PROJECTNAME'])),
					'target'=>'_self',
					'auth'=>'Member/main',
					'show'=>1,
				);

			}

			$contextMenu[] = array(
				'divider'=>true,
				'show'=>1,
			);
			$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-refresh',
					'text'=>'刷新',
					'href'=>'javascript:location.reload();',
					'target'=>'_self',
					'show'=>1,
				);
			$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-arrow-left',
					'text'=>'返回',
					'href'=>'javascript:history.back(-1);',
					'target'=>'_self',
					'show'=>1,
				);

			//权限按钮前置
			$userAuth =$this->getUserAuthorities();

			foreach($contextMenu as $key=>$val){

				if($val['auth']=='Purchase/purchase_manage'){
					$contextMenu[$key]['auth'] = 'Purchase/purchase_manage_' . $scaleTypePY;
				}

				if($val['auth']=='Member/main' && $scaleTypePY=='fx'){
					$contextMenu[$key]['auth'] = 'Member/main';
					$contextMenu[$key]['href'] = U('Member/DisRegMember','TAB_NUMBER=22&search1=PRJ_NAME&search1_s=3&search1_t='. urlencode($projectInfo[0]['PROJECTNAME']));
				}

				if($contextMenu[$key]['auth'] && !in_array($this->authContextMenu[$contextMenu[$key]['auth']],$userAuth)){
					$contextMenu[$key]['show'] = 0;
				}
			}

			//返回json格式
			return @json_encode(g2u($contextMenu));
		}
}
?>