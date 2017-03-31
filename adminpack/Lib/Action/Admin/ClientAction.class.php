<?php
	class ClientAction extends ExtendAction{
		
		public function _initialize(){
			
			parent::_initialize();
			
		}

		public function index(){
			$identity = JJ('company_type');
			$paytype = JJ('paytype');


			$city = $this->city;
            
            
            
			$where = "  m_city = '".$city."' and client_del = 0 ";
            
            $m_id = $this->_get("m_id");
            if($m_id){
                $where .= " and m_id = ".$m_id; 
                $member_info = M('member')->where(" m_id = ".$m_id)->find();
             
                $this->assign('member_info',$member_info);
            }
            
            
			$count = M('client')->join('loan_member ON loan_member.m_id = loan_client.client_m_id')->where($where)->order('client_created desc')->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('client')->join('loan_member ON loan_member.m_id = loan_client.client_m_id')->where($where)->order('client_created desc')->field('loan_client.*,loan_member.m_uid')->limit($p->firstRow.','.$p->listRows)->select();
            //echo M('client')->getLastSql();
            
            $this->assign('m_id',$m_id);
            $this->assign('page',$page);
			$this->assign('paytype',$paytype);
			$this->assign('identity',$identity);			
			$this->assign('re',$re);
			$this->display();
		}

		private function BBSID($uid){
			$channel = $this->city;
			$where = "  client_del=0 and client_uid='$uid'";
			$re = M('client')->where($where)->find();
			return $re['client_uid'];
		}

		public function checkid(){
			$uid = intval($this->_get('uid'));
			$client_uid = $this->BBSID($uid);

			if($client_uid){
				echo '1';
			}else{
				echo '0';
			}
		}


		public function del(){
			$id = $this->_get('id');
			$ss = array();
			$ss['client_del'] = 1;
			$ss['client_updated'] = time();

			$power = $_COOKIE['loan_power'];
			$channel = $this->city;
			$where = "  client_id='$id'";
			M('client')->where($where)->save($ss);
			$this->success('删除成功！',U('Client/index'));exit();
		}

		public function edit(){
			$id = $this->_request('id');
			$act = $this->_post("act");

			$power = $_COOKIE['loan_power'];
			$channel = $this->city;

			if($id && $act=='edit'){

				$ss = array();
				$ss['client_nickname'] = $this->_post('client_nickname');
				$ss['client_phone'] = $this->_post('client_phone');
				$ss['client_email'] = $this->_post('client_email');
				
				$ss['client_company_type'] = $this->_post('client_company_type');
				$ss['client_birthday'] = $this->_post('client_birthday');
				$ss['client_pay_type'] = $this->_post('client_pay_type');
				$ss['client_pay'] = $this->_post('client_pay');
				//$ss['client_year'] = $this->_post('client_year');
			//	$ss['client_month'] = $this->_post('client_month');
                $ss['client_seniority'] = intval($this->_post('client_year'))*12+intval($this->_post('client_month'));
				$ss['client_creditcard'] = $this->_post('client_creditcard');
				$ss['client_creditcard_num'] = $this->_post('client_creditcard_num');
				$ss['client_creditcard_amount'] = $this->_post('client_creditcard_amount');
				$ss['client_liabilities'] = $this->_post('client_liabilities');
				$ss['client_liabilities_num'] = $this->_post('client_liabilities_num');
				$ss['client_loan'] = $this->_post('client_loan');
				$ss['client_isloan_liabilities'] = $this->_post('client_isloan_liabilities');
				$ss['client_loan_liabilities'] = $this->_post('client_loan_liabilities');
				$ss['client_desc'] = $this->_post('client_desc');
				$ss['client_updated'] = time();

				$where = "  client_id='$id' ";
				$affected = M('client')->where($where)->save($ss);
				if($affected){
                    //更新CRM
                    $w['client_id'] = $id;
                    $info = M("Client")->where($w)->find();
                     //更新CRM
                    $crmarr = array(
                        "username"=>urlencode($info["client_nickname"]),
                        "loan_companyt"=>$info["client_company_type"],
                        "loan_payt"=>$info["client_pay_type"],
                        "loan_worky"=>urlencode(floor($info["client_seniority"]/12)."年".($info["client_seniority"]%12)."月"),
                        "loan_salary"=>$info["client_pay"],
                        "loan_credits"=>$info["client_creditcard_num"],
                        "loan_creditd"=>$info["client_liabilities_num"],
                        "loan_debt"=>$info["client_loan_liabilities"],
                        "otherinfo"=>serialize(array("email"=>$info["client_email"]))
                    );
                    toCrm("114",$this->loan_city_en,$info['client_phone'],"贷款人信息更新",$crmarr);
                    
					$this->success('用户基本信息修改成功！',U('Client/edit',array('id'=>$id)));exit();
				}else{
					$this->error('用户基本信息修改失败',U('Client/edit',array('id'=>$id)));exit();
				}
				exit();
			}
			if($id){
				$todo = 'edit';
				$action = U('Loan/editsort');
				$identity = JJ('company_type');
				$paytype = JJ('paytype');

				$where = "  client_id='$id'";
                $re = M('client')->join('loan_member ON loan_member.m_id = loan_client.client_m_id')->where($where)->field('loan_client.*,loan_member.m_uid')->find();
                $year_month = $re['client_seniority'];
                $re['client_year'] = floor($year_month/12);
                $re['client_month'] = $year_month-$re['client_year']*12;
				$BBSID = $re['m_uid'];
                $this->assign('todo','edit');
                
			}
            $this->assign('BBSID',$BBSID);
			$this->assign('paytype',$paytype);
			$this->assign('identity',$identity);
			$this->assign('menutitle','客户基本信息修改');
			$this->assign('re',$re);
			$this->display('client');
		}

		public function add(){
			$todo = 'add';
			$action = U('Client/add');
			$act = $this->_post('act');
            
            

			$power = $_COOKIE['loan_power'];
			$channel = $this->city;

			if($act=='add'){
				$ss = array();
                $ss['client_m_id'] = $this->_post('client_m_id');
				$ss['client_nickname'] = $this->_post('client_nickname');
				$ss['client_phone'] = $this->_post('client_phone');
				$ss['client_email'] = $this->_post('client_email');
				$ss['client_company_type'] = $this->_post('client_company_type');
				$ss['client_birthday'] = $this->_post('client_birthday');
				$ss['client_pay_type'] = $this->_post('client_pay_type');
				$ss['client_pay'] = $this->_post('client_pay');
				//$ss['client_year'] = $this->_post('client_year');
				//$ss['client_month'] = $this->_post('client_month');
                $ss['client_seniority'] = $this->_post('client_year').'-'.$this->_post('client_month');
				$ss['client_creditcard'] = $this->_post('client_creditcard');
				$ss['client_creditcard_num'] = $this->_post('client_creditcard_num');
				$ss['client_creditcard_amount'] = $this->_post('client_creditcard_amount');
				$ss['client_liabilities'] = $this->_post('client_liabilities');
				$ss['client_liabilities_num'] = $this->_post('client_liabilities_num');
				$ss['client_loan'] = $this->_post('client_loan');
				$ss['client_isloan_liabilities'] = $this->_post('client_isloan_liabilities');
				$ss['client_loan_liabilities'] = $this->_post('client_loan_liabilities');
				$ss['client_desc'] = $this->_post('client_desc');
				$ss['client_channel'] = $channel;
				$ss['client_power'] = $power;
				$ss['client_created'] = $ss['client_updated'] = time();
                
				$affected = M('client')->add($ss);
				if($affected){
                    //更新CRM
                    $w['client_id'] = $affected;
                    $info = M("Client")->where($w)->find();
                     //更新CRM
                    $crmarr = array(
                        "username"=>urlencode($info["client_nickname"]),
                        "loan_companyt"=>$info["client_company_type"],
                        "loan_payt"=>$info["client_pay_type"],
                        "loan_worky"=>urlencode(floor($info["client_seniority"]/12)."年".($info["client_seniority"]%12)."月"),
                        "loan_salary"=>$info["client_pay"],
                        "loan_credits"=>$info["client_creditcard_num"],
                        "loan_creditd"=>$info["client_liabilities_num"],
                        "loan_debt"=>$info["client_loan_liabilities"],
                        "otherinfo"=>serialize(array("email"=>$info["client_email"]))
                    );
                    toCrm("114",$this->loan_city_en,$info['client_phone'],"贷款人信息更新",$crmarr);
                    
                
					$this->success('用户基本信息添加成功！',U('Client/index',array('m_id'=>$ss['client_m_id'])));exit();
				}else{
					$this->error('用户基本信息添加失败',U('Client/add',array('m_id'=>$ss['client_m_id'])));exit();
				}
				die();
			}else{
			    $m_id = $this->_get("m_id");
                if($m_id){
                   $mem_info = M('member')->where("m_id='".$m_id."'")->find();
                   if(!$mem_info){
                    $this->error('入口错误2',U('Member/index'));exit();
                   }
                   $BBSID = $mem_info['m_uid'];
                }else{
                    $this->error('入口错误',U('Member/index'));exit();
                }
             
			}

			$identity = JJ('company_type');
            //print_r($identity);
			$paytype = JJ('paytype');
            $this->assign('BBSID',$BBSID);
            $this->assign('m_id',$m_id);
            $this->assign('mem_info',$mem_info);
			$this->assign('action',$action);
			$this->assign('todo',$todo);
			$this->assign('paytype',$paytype);
			$this->assign('identity',$identity);
			$this->assign('menutitle','客户基本信息');
			$this->display('client');
		}
	}

?>