<?php
	class ExtendAction extends Action{
        const MONEY_UNIT = 'Ԫ';
        const PERCENT_MARK = '%';

		public $noLoginWaitSecond = '0';
		public $noLoginMessage = '���ĵ�¼���ڣ�ҳ����ת��~';
		public $noPowerCity = '��û�г��е�Ȩ��';
		public $noPowerFrom = '��û������Ȩ��';
		public $channelid = '';//��ǰ����Ȩ��
		public $testidd = '';//��ǰ����Ȩ��
		public $powercity = '';//���г���Ȩ��
		public $allpower = '';
		public $power = '';//��ǰ����
		public $city_config_array;
		public $city_config;
		//public $nodelist ='';//���еĹ���Ȩ��
		//public $nodename = '';

        protected $workFlow = null;  // ������
        protected $menu = null;

        protected $flowId = null;  // ������ID
        protected $recordId = null;  // ��Ӧ��RECORD�ֶ�
        protected $record = null;  // record��¼
        protected $flowType = null;  // ����������
		protected $CASEID = null;  // WTF? Just Guess! Cuz I don't know either.
        protected $ACTIVID = null;  // WTF?

        /**
         * �Ƿ��۵���ǰ�û�����
         * @var bool
         */
        protected $myTurn = false;

        /**
         * ����ʾ��������ť�Ĺ���������
         * @var array
         */
        protected $showCCValues = array(
            'projectset',  // ��������
            'lixiangbiangeng',  // ������
            'dulihuodong',  // ���������
            'xiangmuxiahuodong',  // ��Ŀ�»����
        );

        /**
         * web��ɱ༭ҵ�����ݵĹ�����
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
		 * ��ʼ�����ࣨ���Ȩ���жϣ�
		 */
		function _initialize(){
            $this->assign('isMobile', isMobile());  // �ж��Ƿ�Ϊ�ƶ��豸
            $this->flowId = intval($_REQUEST['flowId']);  // ������id
            if (intval($this->flowId) > 0) {  // ����ǹ���������
                $this->record = $this->getFlowRecord($this->flowId);
                if (!empty($this->record)) {
                    $this->recordId = $this->record['RECORDID'];  // ��ȡ��Ӧ��recordId
                    $this->CASEID = $this->record['CASEID'];
                }
            } else { // �����ǹ���������
                $this->recordId = $_REQUEST['recordId'];
            }

            //��ȡȨ��
            $this->authMyTurn($this->flowId);

            $this->initValuesAccordingFlowType();
            $this->assign('application', $this->record);
            $this->assign('flowId', $this->flowId);
            $this->assign('recordId', $this->recordId);
			$this->assign('CASEID', $this->CASEID);


			//authcodeȨ���ж�(session ��ֵ)
			$authcode = isset($_GET['authcode'])?trim($_GET['authcode']):'';
			if(!empty($authcode)) {
				//��ȡuserid
				$authcode = get_authcode($authcode);
				$user_data = explode("$", $authcode);
				$uid =  $user_data[0];
				//session��ֵ
				$this->setUserSession($uid);
			}

			//ģ��ͷ���
			$m = MODULE_NAME;
			$a = ACTION_NAME;

			$model = $m.'/'.$a;//ģ��
			if(in_array($model,C('NONEPOWER'))) return;

            //����������Ȩ���ж�
            //$this->submitFlowAuth($this->flowId);

			//�Ƿ��¼����(��ת����½ҳ��)
			if(!is_array($_SESSION['uinfo']) ) {
                if (isMobile()) {
                    echo "<script>location.href='".U("Index/login")."'</script>";
                } else {
                    echo "<script>location.href='".U("Admin/Index/login")."'</script>";
                }

				exit();
			}

			//�û��ĳ��в���Ȩ��
			$channel = $_SESSION['uinfo']['pocity'];

			if(empty($channel) && empty($_SESSION['uinfo']['city'])){
				$this->redirect("Index/login","",$this->noLoginWaitSecond,$this->noPowerCity);
			}
			$this->powercity = $channel;
			$channel = explode(',',$channel);
			$channel = array_filter($channel);

			//����Ȩ������
			$channelid = $this->_request('channelid');
			if(!$channelid ){
				$channelid = $_COOKIE['CHANNELID']; //echo $channelid;
			}
			if(!in_array($channelid,$channel)){
				$channelid = '';
			}

			if(!$channelid ){ $channelid = $channel[0] ;}
			$this->channelid = $channelid;

			cookie('CHANNELID',$channelid,3600*24);//����id
//			$_SESSION['uinfo']['city'] = $channelid;
			

			$cityarr = M('erp_city')->where("ISVALID=-1")->select();
			foreach($cityarr as $v){
				$this->city_config_array[$v['ID']] = $v['PY'];
				$this->city_config[$v['ID']] = $v['NAME'];
			}
			$this->city = $this->city_config_array[$channelid]; //echo $channelid;
			cookie('CITYEN',$this->city);//����ƴ����д

			//����Ĭ�ϳ���
			if(in_array($model,C('NONEROLE'))) return ;//���ҳ�治����ɫ�ж�Ŷ

			//��ģ��Ȩ�޵���֤��
			$auth = $this->roleAuth($m,$a);

			//û��Ȩ��
			if($auth==false){
				js_alert('����Ȩ�޲��㣬����ϵ����Ա��');
//				if(IS_AJAX){
//					$ress['msg'] = g2u('����Ȩ�޲��㣬����ϵ����Ա');
//					die(@json_encode($ress));
//				}else{
//					$this->error('����Ȩ�޲��㣬����ϵ����Ա��');
//				}
			}
		}

		/**
		 * �����û�ID��ֵ�û��������Ϣ
		 * @param $username �û�ID
		 */
		protected function setUserSession($uid){
			//��ȡ�û��������Ϣ
			$record = M('Erp_users')->where("USERNAME='".$uid."' ")->find();
			//��ȡ�û����ڲ��ŵ������Ϣ
			$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();
			//��ȡ�û�����س���Ȩ��
			$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']))) )));

			if(!$record['CITY'])
			{
				$dept = $this->ss_getuserdept($dept['PARENTID']);
				$record['CITY'] = intval($dept['CITY_ID']);
			}

			//����ƴ����д
			$cond_where = "ID = ".$record['CITY'];
			$city_info = M('erp_city')->field('PY')->where($cond_where)->find();
			$user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';

			//session��ֵ
			$_SESSION['uinfo'] = array(
				'uid'=> $record['ID'],//�û�ID
				'role'=> $record['ROLEID'],//�û���ɫ
				'uname'=> $record['USERNAME'],//�û���
				'deptid'=> $record['DEPTID'],//���ű��
				'psw' => $record['PASSWORD'],
				'tname'=> $record['NAME'],//�û�����
				'pocity'=> $pocity,//�û�����Ȩ��
				'currentLogin'=> time(),//��ǰ��½ʱ��
				'city'=>$record['CITY'],//��������
				'city_py' => $user_city_py,//��������ƴ��
				'is_login_from_oa' => true,//�Ƿ�����OA
				//'lastLogin'=> $record['LOAN_LOGINTIME'],//�û��ϴε�½ʱ��
				//'flow'=> $record['LOAN_FLOW']//����Ȩ��
			);
		}

		/**
		 * ��ȡ�û��������Ϣ
		 *
		 * @param $deptid
		 * @return  ��ȡ�û�����
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
		 * �û������Ȩ����֤
		 *
		 * @param $m ģ��
		 * @param $a ����
		 * @return bool|void
		 */
        protected function roleAuth($m,$a){
            $groupid = $_SESSION['uinfo']['role'];//�û�Ȩ����
            $group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();

            $groupval = $group['LOAN_GROUPVAL'];

            if(empty($groupval)) return;//û��Ȩ��ֵŶ
            $groupval = explode(',',$groupval);

            $status = false;
            $nop = false;//��������role�������ȴû������Ȩ������Ϊ��Ȩ�����������֤ LOAN_ROLEPARENTID<>0 and

            $record = M('erp_role')->where(" LOAN_ROLECONTROL='$m' and LOAN_ROLEACTION='$a' and LOAN_ROLEDISPLAY=0 and LOAN_PARAM is not null ")->select();//LOAN_ROLEDISPLAY 1Ϊɾ��
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
//                $status = $this->flowRoleAuth();  // todo comment by xuke@2016-07-14 ��֤��������Ҫ�滻272����䣬���ǵ����ܴ��ڵķ��գ���ʱ����
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
     * ������Ȩ����֤
     * ֻ���ڹ������ڵ��д��ڵ��û����в鿴��Ȩ��
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
     * ��ȡ����������
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

                // ֻ��ʾ��Ч���ļ�
                if (!empty($fileInfo['name']) && !empty($fileInfo['size']) && !empty($fileInfo['code'])) {
                    $response []= $fileInfo;
                }
            }
        }

        return $response;
    }


    /**
     * ���ݹ�����id�ҵ��̶�������
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
     * ��ȡ���������ð�ť
     * @param string $flowId
     * @return array
     */
    protected function availableButtons($flowId = '') {
        $response = array(
            'pass' => false,  // ͨ��
            'deny' => false,  // ���
            'finish' => false,  // ����
            'next' => false  // ��һ��
        );

        $fixedFlow = $this->findFixedFlow($flowId); //�ж��Ƿ��ǹ̶���
        if(is_array($fixedFlow) && count($fixedFlow)) {
            // �̶������
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
            //������
            $this->assign('isFixedFlow', false);
            $data = $this->getFlowSet($flowId);
            $response['next'] = true;  // ת����һ��
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
     * ��ȡFlowSet
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

            $flowRole = D()->query(sprintf("SELECT * FROM ERP_FLOWROLE WHERE FLOWSETID = %d", $flowSetId));//��ȡ���̽�ɫ
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
     * ��ȡ�������б�
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
     * ӳ�乤��������
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
                // ��ʽ���ļ�
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
     * ��ȡ��������RecordID
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
     * ��ȡ��������Ϣ��������ģ����
     * @param $flowID
     */
        protected function assignWorkFlows($flowID) {
        if (intval($flowID) > 0) {
            $workFlows = $this->getWorkFlows($flowID);  
            if (is_array($workFlows) && count($workFlows)) {
                $this->menu['flow_graph'] = array(
                    'name' => 'flow-graph',
                    'text' => '����ͼ'
                );

                // ժ������������ŵ�����ĩβ
                if (!empty($this->menu['opinion'])) {
                    $tmpMenuItem = $this->menu['opinion'];
                    unset($this->menu['opinion']);
                    $this->menu['opinion'] = $tmpMenuItem;
                }

                $this->assign('applicationTime', sprintf('����ʱ��&nbsp;&nbsp;%s', $workFlows[count($workFlows) - 1]['E_TIME']));
                // ӳ�����е�������¼
                $workFlows = $this->mapWorkFlows($workFlows);

                if (in_array($workFlows[0]['STATUS'], array(3, 4))) {
                    $this->unsetOpinionMenu();
                } else {   
                    // ��ǰ����������δ��������ڴ���״̬���Ұ������ǵ�ǰ��¼�û�����ʾ����������
                    // �����Ƶ�����������

                    if ($workFlows[0]['DEAL_USERID'] != $_SESSION['uinfo']['uid']) {
                        $this->unsetOpinionMenu();
                    }
                }
                // ���һ������������䡰����˵��������
                $this->assign('lastWorkFlow', $workFlows[count($workFlows) - 1]);
                // ���й���������䡰����ͼ������
                $this->assign('workFlows', $workFlows);

                // web�Ƿ��б༭��Ȩ��
                // �������Ƿ�ǰ�����ˡ��Ƿ������̷����ߡ��Ƿ��ڿɱ༭���ϡ��Ƿ�ΪPC��
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
            unset($this->menu['application']);  // �Ƴ���������˵�
        }
    }

    /**
     * ��ȡȨ��
     * @param $flowID
     */
    protected function authMyTurn($flowID){
        if($flowID>0) {
            $workFlows = $this->getWorkFlows($flowID);
            // ӳ�����е�������¼
            $workFlows = $this->mapWorkFlows($workFlows);

            if (in_array($workFlows[0]['STATUS'], array(1, 2)) && $workFlows[0]['DEAL_USERID'] == $_SESSION['uinfo']['uid'])
                $this->myTurn = true;
            $this->assign('myTurn',$this->myTurn);
        } else {  // ������ύ����������鿴�������ǲ��ǵ�ǰ�����û�
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
//        unset($this->menu['application']);  // �Ƴ�����˵���˵�
        unset($this->menu['opinion']);  // �Ƴ���������˵�
    }
	//���ܵ�Ȩ���ж�
		public function haspermission($id){
			$groupid = $_SESSION['uinfo']['role'];//�û�Ȩ����
			$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();
			$groupval = $group['LOAN_GROUPVAL'];
			$groupval = explode(',',$groupval);
			if(in_array($id,$groupval)) return true;
			else return false;

		}

    /**
     * ���ݹ��������ͽ���һЩ��ʼ������
     */
    private function initValuesAccordingFlowType() {
        $colors = D('Flowtype')->get_status_color();
        if (!empty($_REQUEST['flowTypePinYin'])) {
            $this->flowType = $_REQUEST['flowTypePinYin'];
        }

        $labelBackgroundColor = $colors[$this->flowType];
        if ($labelBackgroundColor) {  // ���ñ�ǩ����ɫ
            $this->assign('labelStyle', sprintf("style='background-color:%s'", $labelBackgroundColor));
        }

        if (empty($this->flowType)) {
            $this->flowType = $_REQUEST['flowType'];
        }

        if (in_array($this->flowType, $this->showCCValues)) {  // �����Ƿ���ʾ���Ϳ�
            $this->assign('showCC', true);
        } else {
            $this->assign('showCC', false);
        }
        $this->assign('flowType', $this->flowType);
    }

    /**
     * �Ƿ����ύ��������Ȩ��
     */
    private function submitFlowAuth($flowId){
        //ģ��ͷ���
        $module = MODULE_NAME;
        $action = ACTION_NAME;

        $authMapKey = $module . '_' . $action;

        //Ȩ��MAP
        /**��Щ**/
        $authMap = array(
            //С�۷�
            'PurchasingBee_show'=>700,
            //Ԥ������
            'BenefitFlow_process'=>461,
            //��Ա��Ʊ
            'ChangeInvoice_process'=>308,
            //��������
            'MemberDiscount_process'=>355,
            //�������
            'Loan_process'=>620,
            //��Ŀ����
            'Finalaccounts_show'=>282,
            //��Ŀ��ֹ
            'ProjectTermination_show'=>281,
            //�����
            //��Ŀ�»
            //��������
            //��Ŀ�»���
            //'Activ_process'=>1,
            //��������
            'ProjectSet_show'=>169,
            //��Ա��Ʊ
            'InvoiceRecycle_process'=>308,
            //���ʱ�������
            'Payout_change_process'=>289,
            //�˿�����
            'MemberRefund_process'=>370,
            //��ͬ��Ʊ
            'Advert_process'=>743,
            //��׼����
            'Feescale_change_process'=>356,
            //������
            'ProjectChange_show'=>448,
            //�ɱ�����
            'Cost_process'=>451,
            //�Ǹ��ֳɱ�����
            'PurchaseNocash_process'=>500,
            //�ɹ�����(��ť�Ѿ�������)
            //'Purchase_process'=>1,
            //ҵ�����
            'Benefits_process'=>456,
        );

        //Ȩ���ж�
        if(!$flowId && $authMap[$authMapKey] && !in_array($authMap[$authMapKey],$this->getUserAuthorities()))
        {
            js_alert('�Բ��������ر������ù�������Ȩ�ޣ�');
            exit();
        }
    }

    protected function getUserAuthorities() {
        $response = array();

        $groupID = $_SESSION['uinfo']['role'];//�û�Ȩ����
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