<?php
	class ExtendAction extends Action{

        const HTML_A_TAG_PREG = "|(<a.*?id\s*=\s*[\"'](.*?)[\"'].*?>.*?</a>)|";

        /**
         * ��ʾ�������ύ����)�Ȱ�ť
         */
        const SHOW_OPTION_BTN = 1;

        /**
         * �����������ύ�������Ȱ�ť
         */
        const HIDE_OPTION_BTN = 2;

        /**
         * ����ҵ��ID
         */
        const DS = 1;

        /**
         * ����ҵ��ID
         */
        const FX = 2;

        /**
         * Ӳ��ҵ��ID
         */
        const YG = 3;

        /**
         * �ҵ��ID
         */
        const HD = 4;

        /**
         * ��Ʒҵ��ID
         */
        const CP = 5;

        /**
         * ��Ŀ�»ҵ��ID
         */
        const XMXHD = 7;

        /**
         * ���ҷ��ճ�ҵ��ID
         */
        const FWFSC = 8;

        /**
         * Ĭ��Excel��߶�
         */
        const DEFAULT_EXCEL_ROW_HEIGHT = 30;

        /**
         * Ĭ��Excel�п�
         */
        const DEFAULT_EXCEL_COLUMN_WIDTH = 20;

        /**
         * ��Ŀ������������ӳ���
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

        protected $authorityMap;  // Ȩ��ӳ���
        protected $options;  // ������
        protected $projectContextMenuScope = array(
            'benefits' => array(self::DS, self::YG, self::FWFSC, self::HD, self::FX),  // ����
            'otherBenefits' => array(self::DS, self::FWFSC, self::FX),  // Ԥ������
            'feeScaleChange' => array(self::DS, self::FX, self::FWFSC),  // ��׼����
            'payoutChange' => array(self::DS, self::FX, self::FWFSC)  // ���ʱ�������
        );

		//�һ��˵���Ȩ����
		protected $authContextMenu = array(
			//��Ŀ����
			'Project/projectDetail'=>783,
			//���̲ɹ�
			'Purchase/purchase_manage_ds'=>191,
			//�����ɹ�
			'Purchase/purchase_manage_fx'=>430,
			//Ӳ��ɹ�
			'Purchase/purchase_manage_yg'=>435,
			//��ɹ�
			'Purchase/purchase_manage_hd'=>440,
			//���ҷ��ճ�ɹ�
			'Purchase/purchase_manage_fwfsc'=>420,
			//�ɹ�����
			'Purchasing/index'=>230,
			//��ĿȨ��
			'House/projectAuth'=>164,
			//ҵ�����
			'Benefits/benefits'=>163,
			//�ʽ�ط�������
			'Benefits/fundPoolCost'=>784,
			//Ԥ��������
			'Benefits/otherBenefits'=>221,
			//�������
			'Loan/loan_application'=>326,
			//���ʱ���
			'Payout_change/payout_change'=>288,
			//���̻�Ա
			'Member/main'=>147,
			//������Ա
			'MemberDistribution/manage'=>310,
		);

		public $noLoginWaitSecond = '0';
		public $noLoginMessage = '��ĵ�¼���ڣ�ҳ����ת��~';
		public $noPowerCity = '��û�г��е�Ȩ��';
		public $noPowerFrom = '��û������Ȩ��';
		public $channelid = '';//��ǰ����Ȩ��
		public $channelid_py = '';//��ǰ����Ȩ��
		public $testidd = '';//��ǰ����Ȩ��
		public $powercity = '';//���г���Ȩ��
		public $allpower = '';
		public $power = '';//��ǰ����
		//public $nodelist ='';//���еĹ���Ȩ��
		//public $nodename = '';
		public $city_config_array;
		public $city_config;
		public $p_auth_all;
		public $p_vmem_all;//�鿴ȫ����Ա
		protected function roleAuth($m,$a){
			$groupid = $_SESSION['uinfo']['role'];//�û�Ȩ����
			$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();

			$groupval = $group['LOAN_GROUPVAL'];

			if(empty($groupval)) return;//û��Ȩ��ֵŶ
			$groupval = explode(',',$groupval);
			//print_r($groupval);

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
				$status = $this->getFlowRole();
			}

			return $status;
		}//Ȩ����֤

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

			 $model = $m.'/'.$a;//ģ��
			/*
			echo $model;
			echo "<br/>";

			print_r( C('NONEPOWER'));
			die('111');
			*/
			if(in_array($model,C('NONEPOWER'))) return;


			if(!is_array($_SESSION['uinfo']) ) {

				if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
					$ress['msg'] =g2u('����ʱ��δ������½��ʧЧ�������µ�½��');
					$ress['info'] =g2u('����ʱ��δ������½��ʧЧ�������µ�½��');
					$ress['status'] ='noauth';
					exit( json_encode($ress));
				}else{
					echo "<script>parent.location.href='".U("Index/login")."'</script>";
				}

				exit();
			}//�Ƿ��¼����

			$channel = $_SESSION['uinfo']['pocity'];

			if(empty($channel) && empty($_SESSION['uinfo']['city'])){
			//if( empty($_SESSION['uinfo']['city'])){
			    js_alert('�޳���Ȩ�ޣ� ');
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
			// echo $channelid;

			if(!$channelid ){
				//$channelid = $_SESSION['uinfo']['city'];//$channel[0];
				$channelid = $channel[0];
			}
			$this->channelid = $channelid;
			$channelid_py = M('erp_city')->where("ISVALID=-1 AND ID = " . $this->channelid)->find();
			$this->channelid_py = $channelid_py['PY'];
			//$_COOKIE['CHANNELID'] = $channelid;//add by gehaifeng

			cookie('CHANNELID',$channelid,3600*24);//����id
			$_SESSION['uinfo']['city'] = $channelid;

			//$city_config_array = C('city_config_array');
			$cityarr = M('erp_city')->where("ISVALID=-1")->select();
			foreach($cityarr as $v){
				$this->city_config_array[$v['ID']] = $v['PY'];
				$this->city_config[$v['ID']] = $v['NAME'];
			}
			$this->city = $this->city_config_array[$channelid]; //echo $channelid;

			$_SESSION['uinfo']['city_py'] = $this->city;
			cookie('CITYEN',$this->city);//����ƴ����д

			//����Ĭ�ϳ���


			if($_SESSION['uinfo']['p_auth_all']==1){
				$this->p_auth_all=true;
			}
			if($_SESSION['uinfo']['p_vmem_all']==1){
				$this->p_vmem_all=true;
			}

			if(in_array($model,C('NONEROLE'))) {  return ; }//���ҳ�治����ɫ�ж�Ŷ
			//���濪ʼ��ɫ��֤�ˣ�
			$auth = $this->roleAuth($m,$a);
			//echo $_SESSION['uinfo']['uname'];

			if($auth==false){
				//if(IS_AJAX){
				if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
					$ress['msg'] =g2u('����Ȩ�޲��㣬����ϵ����Ա');
					$ress['info'] =g2u('����Ȩ�޲��㣬����ϵ����Ա');
					$ress['status'] ='noauth';
					exit( json_encode($ress));
				}else{
					//$this->error('����Ȩ�޲��㣬����ϵ����Ա��',U("Index/welcome"));
					$this->error('����Ȩ�޲��㣬����ϵ����Ա��' );
				}
				exit();
			}

            //$this->loan_city_en = $_COOKIE['loan_city_en'];
		}
		//������ҳ��
        function publicChildren(){
			//if($_REQUEST['showForm'] && empty($_REQUEST['budgetid'])  )exit("<script>alert('�����������Ԥ�㣡');self.location=document.referrer;</script>");
			Vendor('Oms.Form');
			$form = new Form();
			$form =  $form->initForminfo($_REQUEST['parentFormNo'])->where($_REQUEST['parentField']."='".$_REQUEST['parentId']."'")->setMyFieldVal($_REQUEST['parentField'],$_REQUEST['parentId'],true)->getResult();
			$this->assign('form',$form);
			$this->display('public/publicChildren');

		 }


        //ҳǩ
		public function getTabs($tabsId,$param){
			$groupid = $_SESSION['uinfo']['role'];//�û�Ȩ����
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

		//��ĿȨ���ж� ��Ŀid   ҵ������ ��������ַ�����
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
							$ress['msg'] =g2u('����Ȩ�޲��㣬����ϵ����Ա');
							$ress['info'] =g2u('����Ȩ�޲��㣬����ϵ����Ա');
							exit( json_encode($ress));
						}else{
							$this->error('������ĿȨ�޲��㣬����ϵ��Ŀ���ܣ�',U("Case/projectlist"));
							//$this->error('����Ȩ�޲��㣬����ϵ����Ա��' );
							exit();
						}
						exit();
					}


				}
			}
		}
		//������ϢȨ��
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
					$ress['info'] = $ress['msg'] = g2u('����Ȩ�޲��㣬����ϵ����Ա');
					exit( json_encode($ress));
				}else{
					$this->error('����Ȩ�鿴������Ϣ��',U("Case/projectlist") );
					exit();
				}
			}
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
        //��Ŀҵ��Ȩ���ж�
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
					$ress['msg'] =g2u('������ĿȨ�޲���');
					$ress['info'] =g2u('������ĿȨ�޲���');
					$ress['status'] ='noauth';
					exit( json_encode($ress));
				}else{
					$this->error('������ĿȨ�޲��㣡',U("Case/projectlist"));

				}
				exit();
			}
		}
     /**
      * С�۷�ɹ��ڿ�api
      *
      */
	protected function _zk_api($purchase_info,$purchase_list_info){
	    if ( isset($purchase_list_info['ZK_STATUS']) && $purchase_list_info['ZK_STATUS']==1){
	        return false;
	    }
	    //��ȡ���м�ƴ
	    $model_city = D('City');
	    $city = $model_city->get_city_info_by_id($purchase_info['CITY_ID']);
	    $citypy = strtolower($city["PY"]);
	    //��ȡ��Ŀ������Ϣ
	    $house =  D('Erp_house')->where("PROJECT_ID =".$purchase_info['PRJ_ID'])->find();
	    //��ȡ��Ŀ��Ϣ
	    $prgect= D('Project')->find($purchase_info['PRJ_ID']);

	    //��������
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
	    //д���ڿ�ϵͳ
        $api = ZKAPI1;
        return curlPost($api, $param);
    }

        /**
         * �ɹ�ҳ���Ƿ���ʾ�ɲ�����ť
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
         * ��Ŀ�б��е������Ĳ˵��Ƿ����
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

            $flag = false;  // Ĭ�������Ĳ˵��򿪵�ҳ���ǲ��������������ύ�Ȳ�����
            $records = D('ProjectCase')->query($sql);
            if (is_array($records) && count($records)) {
                foreach ($records as $record) {
                    $flag = $flag || $this->checkCaseStatus($record, $scope);
                }
            }

            return $flag ? self::SHOW_OPTION_BTN : self::HIDE_OPTION_BTN;
        }

        /**
         * ��鰸��������״̬���Դ���Ϊ��Ŀ�������Ĳ˵��Ƿ���õı�־
         * @param $data
         * @param $scope
         * @return bool
         */
        private function checkCaseStatus($data, $scope) {
            if (!is_array($data) || count($data) == 0) {
                return false;
            }

            // ����ð������Ͳ�������صĲ���
            if (empty($data['SCALETYPE']) || !in_array($data['SCALETYPE'], $this->projectContextMenuScope[$scope])) return false;
            // ��Ŀ����ֹ����� �� �ѽ�����˻������ɣ��򲻿�����ʾ��ѡ����
            if (!empty($data['STATUS'])
                && !in_array($data['STATUS'], array(2,3,4))
            ) {
                return false;
            }
            return true;
        }

        /**
         * ����Ƿ�ӵ��ҳǩ��������һ��ҳǩ��Ȩ��
         * @param $tabNum
         * @return array
         */
        protected function checkTabAuthority($tabNum) {
            $groupID = $_SESSION['uinfo']['role'];//�û�Ȩ����
            $strGroups = M('erp_group')->where("LOAN_GROUPID='{$groupID}' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->getField('LOAN_GROUPVAL');

            if(empty($strGroups)) {
                return array(
                    'result' => false,
                    'msg' => '�û����ڷ���û��Ȩ��ֵ'
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
                            'msg' => '��ȡ��һ����Ȩ��ҳǩ',
                            'roleID' => $roleID['ID']
                        );
                    }
                }
            }

            return array(
                'result' => false,
                'msg' => '�û�û�и�ҳǩ�µ�Ȩ��'
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

        /**
         * @param $objActSheet worksheet ����
         * @param $data ������������
         * @param $row  ��ʼ��
         * @param $title ����
         * @param $map �ֶ�����
         * @param array $cellStyle ��ʽ
         */
        protected function commonExportAction(&$objActSheet, $data, &$row, $title, $map, $cellStyle = array()) {
            // ������
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

            // ��¼�б�
            foreach($data as $item) {
                $row++;
                $column = 0;
                foreach($map as $k => $v) {
                    if (array_key_exists($k, $item)) {
                        $value = $item[$k];
                        // ӳ������
                        if (array_key_exists('map', $v)) {
                            $value = $v['map'][$value];
                        }
                        // ת�����
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
         * ��ʼ����������
         * @param $objPHPExcel PHPExcel����
         * @param $objActSheet  worksheet����
         * @param string $worksheetTitle worksheet����
         * @param int $width  Ĭ���п�
         * @param int $height Ĭ���и�
         */
        protected function initExport(&$objPHPExcel, &$objActSheet, $worksheetTitle, $width = 20, $height = 20) {
            try {
                Vendor('phpExcel.PHPExcel');
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0);
                $objActSheet = $objPHPExcel->getActiveSheet();

                $objActSheet->getDefaultRowDimension()->setRowHeight($height);//Ĭ���и�
                $objActSheet->getDefaultColumnDimension()->setWidth(-1);//Ĭ���п�
                $objActSheet->setTitle(iconv("gbk//ignore", "utf-8//ignore", $width));
                $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore", "utf-8//ignore", '����'));
                $objActSheet->getDefaultStyle()->getFont()->setSize(10);
                $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
                $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objActSheet->getRowDimension('1')->setRowHeight(40);
                $objActSheet->setTitle(iconv("gbk//ignore", "utf-8//ignore", $worksheetTitle));

            } catch (Exception $e) {
                die(sprintf('%s:%s', $e->getCode(), $e->getMessage()));
            }
        }

		/**
		 * ���������Ĳ˵�
		 * @param $caseId ����ID
		 * @return bool|string ���ز˵�json��ʽ
		 */
		protected function getContextMenu($caseId){
			$contextMenu = array();

			if(!$caseId)
				return false;

			//��ȡ��Ŀ��Ϣ
			$proSql = <<< ET
		SELECT C.SCALETYPE,P.PROJECTNAME,P.ID,P.BSTATUS FROM ERP_PROJECT P INNER JOIN ERP_CASE C ON P.ID = C.PROJECT_ID WHERE C.ID = {$caseId}
ET;
			$projectInfo = D()->query($proSql);

			//ҵ������
			$scaleType = $projectInfo[0]['SCALETYPE'];
			$scaleTypePY = $this->scaleTypeAliasMap[$scaleType];
			$bStatus = intval($projectInfo[0]['BSTATUS']);

			//�����Ĳ˵�
			$contextMenu = array(
				array(
					'header'=>$projectInfo[0]['PROJECTNAME'],
					'show'=>1,
				),
				array(
					'icon'=>'glyphicon glyphicon-king',
					'text'=>'������ĿȨ��',
					'href'=>U('House/projectAuth','prjid='.$projectInfo[0]['ID']),
					'target'=>'_self',
					'title'=>'',
					'auth'=>'House/projectAuth',
					'show'=>1,
				),
//				array(
//					'icon'=>'glyphicon glyphicon-equalizer',
//					'text'=>'��Ŀִ������',
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
					'text'=>'�ɹ�����',
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
					'text'=>'ҵ���������',
					'href'=>U('Benefits/benefits','prjid='.$projectInfo[0]['ID']),
					'target'=>'_self',
					'auth'=>'Benefits/benefits',
					'show'=>1,
				),
			);

			//��Ԥ�����������롱ֻ�е��̡����������ҷ��ճ���
			if($scaleTypePY=='ds' || $scaleTypePY=='fx' || $scaleTypePY=='fwfsc'){
				$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-jpy',
					'text'=>'Ԥ������������',
					'href'=>U('Benefits/otherBenefits','prjid='.$projectInfo[0]['ID']),
					'target'=>'_self',
					'auth'=>'Benefits/otherBenefits',
					'show'=>1,
				);
			}

			//���ʽ�ط������롱ֻ�е�����(���Ҳ�����ֹ״̬)�����ʽ�صĵ�����Ŀ
			if($scaleTypePY=='ds' && $bStatus != 5 && D('House')->get_isfundpool_by_prjid($projectInfo[0]['ID'])){
				$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-jpy',
					'text'=>'�ʽ�ط�������',
					'href'=>U('Benefits/fundPoolCost','prjId='.$projectInfo[0]['ID'].'&TAB_NUMBER=15'),
					'target'=>'_self',
					'auth'=>'Benefits/fundPoolCost',
					'show'=>1,
				);
			}

			$contextMenu[] = array(
				'icon'=>'glyphicon glyphicon-jpy',
				'text'=>'�������',
				'href'=>U('Loan/loan_application'),
				'target'=>'_self',
				'auth'=>'Loan/loan_application',
				'show'=>1,
			);

			/**** ����ǵ��̺ͷ�������ϻ�Ա����*****/
			if($scaleTypePY == 'ds' || $scaleTypePY == 'fx'){
				$contextMenu[] = array(
					'divider'=>true,
					'show'=>1,
				);
				$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-user',
					'text'=>'��Ա����',
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
					'text'=>'ˢ��',
					'href'=>'javascript:location.reload();',
					'target'=>'_self',
					'show'=>1,
				);
			$contextMenu[] = array(
					'icon'=>'glyphicon glyphicon-arrow-left',
					'text'=>'����',
					'href'=>'javascript:history.back(-1);',
					'target'=>'_self',
					'show'=>1,
				);

			//Ȩ�ް�ťǰ��
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

			//����json��ʽ
			return @json_encode(g2u($contextMenu));
		}
}
?>