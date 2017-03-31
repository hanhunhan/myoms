<?php
	class ApplyAction extends ExtendAction{

		function _initialize(){
			parent::_initialize();
		}

		function index(){
			
            $LOAN_STATUS = C('LOAN_STATUS');
            $city = $this->city;
            
			$where = " apply_del=0 and m_city = '".$city."' ";
            $m_id = $this->_get("m_id");
            if($m_id){
                $where .= " and m_id = ".$m_id; 
                $member_info = M('member')->where(" m_id = ".$m_id)->find();
             
                $this->assign('member_info',$member_info);
            }
            
         $count = M('apply')->join('loan_member ON loan_member.m_id = loan_apply.apply_m_id')->join('loan_sort ON loan_sort.sort_id = loan_apply.apply_sort_id')->where($where)->count();
		
        	import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('apply')->join('loan_member ON loan_member.m_id = loan_apply.apply_m_id')->join('loan_sort ON loan_sort.sort_id = loan_apply.apply_sort_id')->where($where)->order('apply_created desc')->limit($p->firstRow.','.$p->listRows)->select();
			//echo M('apply')->getLastSql();
            
            
            

			if(is_array($re)){
				foreach($re as $key=>$val){
					$id = $val['apply_client_id'];
					$re[$key]['nickname'] = M('client')->where(" client_id='$id'")->getField('client_nickname');
				}
			}
            
            $this->assign('m_id',$m_id);
            $this->assign('LOAN_STATUS',$LOAN_STATUS);
            $this->assign('page',$page);
			$this->assign('re',$re);
			$this->display();
		}

		function add(){
			$act = $this->_post('act');
			$power = $_COOKIE['loan_power'];
            
            $m_id = $this->_get("m_id");
            
            $LOAN_STATUS = C('LOAN_STATUS');
            
            
		
			$sort = M('sort')->join('loan_bank_user on loan_sort.sort_bu_id=loan_bank_user.bu_id')->where(" bu_city='".$this->city."' and  sort_del=0")->select();
			if($act=='add'){
				$ss = array();
                $client_mid = $this->_post('client_mid');
                $client_mid = explode('_',$client_mid); 	
                $ss['apply_client_id'] = $client_mid[1];
                $ss['apply_m_id'] = $client_mid[0];
				$ss['apply_sort_id'] = intval($this->_post('apply_sort_id'));
				$ss['apply_momeny'] = $this->_post('apply_momeny');
				$ss['apply_time'] =	$this->_post('apply_time');		
				$ss['apply_power'] = $power;
	
                $ss['apply_status'] = $this->_post('apply_status');
                
				$ss['apply_created'] = $ss['apply_updated'] = time();
				$affected = M('apply')->add($ss);
				if($affected){
				    //历史记录
                    
                    $db =  new Model();
                    $sql = "update loan_sort set sort_applicants_num = sort_applicants_num+1 where sort_id = ".$ss['apply_sort_id'];
                    $db->query($sql);
                   
                    $bank = M('sort')->join('loan_bank on loan_bank.b_id = loan_sort.sort_bank_id')->where(" sort_id=".$ss['apply_sort_id'])->find();
                        
                   
                    
                    
					$this->success('用户申请添加成功！',U('Apply/index',array('m_id'=>$m_id)));exit();
				}else{
					$this->error('用户申请添加失败',U('Apply/index',array('m_id'=>$m_id)));exit();					
				}
			}

			$clientarr = array();
            $where = "client_del=0";
            if($m_id){
                $where .= " and client_m_id = ".$m_id; 
            }
			$client = M('client')->where($where)->select();
			if(is_array($client)){
				foreach($client as $val){
					$clientarr[$val['client_m_id'].'_'.$val['client_id']] = $val['client_m_id'].'(ID)---'.$val['client_nickname'].'(昵称)---'.$val['client_phone'].'(手机)';
				}
			}
            
            
            foreach($LOAN_STATUS as $key=>$value){
                if($key>0)unset($LOAN_STATUS[$key]);
            }
            
            $this->assign('LOAN_STATUS',$LOAN_STATUS);
			$this->assign('clientarr',$clientarr);

			$this->assign('todo','add');
			$this->assign('sort',$sort);
			$this->assign('menutitle','客户申请信息');
			$this->display('apply');
		}

		function del(){
			$id = $this->_get('id');
			$ss = array();
			$ss['apply_del'] = 1;
			$ss['apply_updated'] = time();
			$where = " apply_id='$id'";
			M('apply')->where($where)->save($ss);
            
            
            $where = "  apply_id='$id'";
			$re = M('apply')->where($where)->find();
            $db =  new Model();
           
            $sql = "update loan_sort set sort_applicants_num =sort_applicants_num-1 where sort_id = ".$re['apply_sort_id'];
            $db->query($sql);
			$this->success('删除成功！',U('Apply/index'));exit();
		}

		function edit(){
			$id = $this->_request('id');
			$act = $this->_post("act");

	       $LOAN_STATUS = C('LOAN_STATUS');
           $allStatus = array(
               "用户提交申请资料",
               "接收资料，等待客户经理联系",
               "资料审核通过，等待签约",
               "签约成功，等待放款",
               "贷款已成功发放，请查收",
               "放款失败",
           );
			
			

			if($id){
				$todo = 'edit';
				//$sort = M('sort')->where(" sort_del=0")->select();
                $sort = M('sort')->join('loan_bank_user on loan_sort.sort_bu_id=loan_bank_user.bu_id')->where(" bu_city='".$this->city."' and  sort_del=0")->select();

				$where = "  apply_id='$id'";
				$re = M('apply')->where($where)->find();	 	
                $re['client_mid'] = $re['apply_m_id'].'_'.$re['apply_client_id'];	
                
                
                $where = "  client_m_id = ".$re['apply_m_id']; 
				$clientarr = array();
				$client = M('client')->where($where)->select();
                
				if($client){
					foreach($client as $val){
						$clientarr[$val['client_m_id'].'_'.$val['client_id']] = $val['client_m_id'].'(ID)---'.$val['client_nickname'].'(昵称)---'.$val['client_phone'].'(手机)';
					}
				}
                
				$this->assign('clientarr',$clientarr);
                
				$this->assign('sort',$sort);

			}
            
            if($id && $act=='edit'){
				$ss = array();
                $client_mid = $this->_post('client_mid');
                $client_mid = explode('_',$client_mid); 	
                $ss['apply_client_id'] = $client_mid[1];
                $ss['apply_m_id'] = $client_mid[0];
                
				$ss['apply_sort_id'] = intval($this->_post('apply_sort_id'));
				$ss['apply_momeny'] = $this->_post('apply_momeny');
				$ss['apply_time'] =	$this->_post('apply_time');		
				$ss['apply_updated'] = time();
                $ss['apply_status'] = $this->_post('apply_status');
                $ss['apply_desc'] = $this->_post('apply_desc');
                
				$where = "  apply_id='$id'";

				$affected = M('apply')->where($where)->save($ss);
               // echo M('apply')->getLastSql();
               // die();
                
				if($affected){
				    if($re['apply_status']!=$ss['apply_status']){
    				    $pp = array();
                        $pp['p_apply_id'] = $re['apply_id'];
                        $pp['p_apply_status'] = $ss['apply_status'];
                        $pp['p_title'] = $allStatus[($ss['apply_status']+1)];
                        $pp['p_time'] = time();
                        
                        M('progress')->add($pp);
                    }
				    
					$this->success('用户申请修改成功！',U('Apply/edit',array('id'=>$id)));exit();
				}else{
					$this->error('用户申请修改失败',U('Apply/edit',array('id'=>$id)));exit();					
				}				
			}
            
            /*
            '0'=>'接受资料，等待联系',
    		'1'=>'资料通过，等待签约',
    		'2'=>'签约成功，等待放款',
    		'3'=>'放款成功',
    		'4'=>'放款失败'
            */
            switch($re['apply_status']){
                case '0' :
                    unset($LOAN_STATUS[2]);
                    unset($LOAN_STATUS[3]);
                    break;
                case '1':
                    unset($LOAN_STATUS[0]);
                    unset($LOAN_STATUS[3]);
                    break;
                case '2':
                    unset($LOAN_STATUS[0]);
                    unset($LOAN_STATUS[1]);
                    break;
                case '3':
                    unset($LOAN_STATUS[0]);
                    unset($LOAN_STATUS[1]);
                    unset($LOAN_STATUS[2]);
                    unset($LOAN_STATUS[4]);
                    break;
                case '4':
                    unset($LOAN_STATUS[0]);
                    unset($LOAN_STATUS[1]);
                    unset($LOAN_STATUS[2]);
                    unset($LOAN_STATUS[3]);
                    break;
                
            }
            
            $this->assign('LOAN_STATUS',$LOAN_STATUS);
            
			$this->assign('menutitle','客户申请信息修改');
			$this->assign('todo',$todo);
			$this->assign('re',$re);
			$this->display('apply');
		}

		
	}
?>