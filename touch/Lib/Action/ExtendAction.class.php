<?php
	class ExtendAction extends Action{
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

		/**
		 * ��ʼ�����ࣨ���Ȩ���жϣ�
		 */
		function _initialize(){
			//http://221.231.141.162/tlfjk_mobile/?m=login&a=login&authcode=a5d1KEhYI7xj7lreJ2phk%2B2hQDbATzih8A%2BrolDYSDQGMgtEjNFhNdo
			//authcodeȨ���ж�(session ��ֵ)
			$authcode = isset($_GET['authcode'])?trim($_GET['authcode']):'';
			if(!empty($authcode)) {
				$authcode = get_authcode($authcode);
				list($uid, $gid, $username) = explode("$", $authcode);

				//session��ֵ
				$this->setUserSession($username);
			}

			//ģ��ͷ���
			$m = MODULE_NAME;
			$a = ACTION_NAME;

			$model = $m.'/'.$a;//ģ��
			if(in_array($model,C('NONEPOWER'))) return;

			//�Ƿ��¼����(��ת����½ҳ��)
			if(!is_array($_SESSION['uinfo']) ) {
				echo "<script>location.href='".U("Index/login")."'</script>";
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
				if(IS_AJAX){
					$ress['msg'] = g2u('����Ȩ�޲��㣬����ϵ����Ա');
					die(@json_encode($ress));
				}else{
					$this->error('����Ȩ�޲��㣬����ϵ����Ա��');
				}
			}
		}

		/**
		 * �����û�ID��ֵ�û��������Ϣ
		 * @param $username �û�ID
		 */
		protected function setUserSession($username){
			//��ȡ�û��������Ϣ
			$record = M('Erp_users')->where("USERNAME='".$username."' ")->find();
			//��ȡ�û����ڲ��ŵ������Ϣ
			$dept = M('erp_dept')->where('ID='.$record['DEPTID'])->find();
			//��ȡ�û�����س���Ȩ��
			$pocity =implode(',',array_filter(array_flip(array_flip(array_merge( explode(',',$record['CITYS']),explode(',',$dept['CITY_ID']))) )));

			//�����ĳ���ƴ��
			$user_city_py = '';
			if(!$dept['CITY_ID'] ) {
				$dept = $this->getuserdept($dept['PARENTID']);
				$cond_where = "ID = ".intval($dept['CITY_ID']);
				$city_info = M('erp_city')->field('PY')->where($cond_where)->find();
				$user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';
			}

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
				'city'=>$dept['CITY_ID'],//��������
				'city_py' => $user_city_py,//��������ƴ��
				'is_login_from_oa' => true,//�Ƿ�����OA
				//'lastLogin'=> $record['LOAN_LOGINTIME'],//�û��ϴε�½ʱ��
				//'flow'=> $record['LOAN_FLOW']//����Ȩ��
			);

		}

		/**
		 * �û������Ȩ����֤
		 *
		 * @param $m ģ��
		 * @param $a ����
		 * @return bool|void
		 */
		protected function roleAuth($m,$a){

			//�û�Ȩ����
			$groupid = $_SESSION['uinfo']['role'];
			$group = M('erp_group')->where("LOAN_GROUPID='$groupid' and LOAN_GROUPSTATUS=1 and LOAN_GROUPDEL=0")->find();
	
			$groupval = $group['LOAN_GROUPVAL'];

			//û��Ȩ��
			if(empty($groupval)) return;

			$groupval = explode(',',$groupval);
			$status = false;
			$nop = false;//��������role�������ȴû������Ȩ������Ϊ��Ȩ�����������֤
			$record = M('erp_role')->where("LOAN_ROLEPARENTID<>0 and LOAN_ROLEDISPLAY=0 and LOAN_PARAM is not null ")->select();//LOAN_ROLEDISPLAY 1Ϊɾ��
			if(is_array($record)){
				foreach($record as $rval){
					$paramCheck = true;
					
					if($rval['LOAN_PARAM']){
						$paramtemp = array_filter( explode(';',$rval['LOAN_PARAM']) );
						foreach($paramtemp as  $vv){
							$vvarr = explode('=',$vv);
							$paramCheck = $_REQUEST[trim($vvarr[0])] == $vvarr[1] ? true:false;  //var_dump($paramCheck);
							if(!$paramCheck) break;//��һ��������һ�����˳�
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