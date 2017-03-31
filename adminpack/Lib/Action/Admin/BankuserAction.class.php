<?php
class BankuserAction extends ExtendAction{
	
	public function index(){
		//echo $this->city;
        
        //��ȡ400�̺Ų���
        $act=$this->_get("act");
        if($act=="get400"){
           $sortnum = $this->get400();
           $r['sortnum'] = $sortnum;
           $r['status'] = 1;
           echo json_encode($r);
           exit;
        }
        
		//��ȡ��ǰ������Ϣ
		$city_id = $this->loan_city_en;
		
		//��ȡǰ̨��������
        $bu_del = $this->_request('bu_del');
			
        if(!isset($bu_del)){
			$bu_del = 0;
		}
        $model = D('bank_user');
		$count = $model->where("bu_del = '$bu_del' and bu_city = '$city_id'")->count();

        //��ҳ
		import("ORG.Util.Page");
		$p = new Page($count,C('PAGESIZE'));
		$para = "&bu_del=".$bu_del;
		if($para) $p->parameter = $para;
		$page = $p->show();	

        $user_info = $model->join('loan_bank ON loan_bank.b_id = loan_bank_user.bu_bid')->where("bu_del = '$bu_del' and bu_city = '$city_id'")->field('bu_id,bu_uname,b_name,bu_tel,bu_del,bu_indate,bu_subbranch,bu_contact')->order('bu_id desc')->limit($p->firstRow.','.$p->listRows)->select();
		
		$this->assign('page',$page);
		$this->assign('data',$user_info);
		$this->display();
	}

	public function edit(){
			
		if($this->_post('do') == 'edit'){//��������

			//�����޸���֤ ��֤֮ǰ�Ƿ���400 �Ƿ���Ҫ�޸�
			$ww['bu_id'] = $this->_post('bu_id');
			$info = M("Bank_user")->where($ww)->find();
			if($info['bu_400'] and $info['bu_400tel']){
				$has400 = true;
			}

			$refurl = $this->_post('refurl');
            if($this->_post('bu_pwd')){
            $data['bu_pwd'] = md5(trim($this->_post('bu_pwd')));
            }
            $data['bu_contact'] = $this->_post('bu_contact');
            $data['bu_id'] = $this->_post('bu_id');
			$data['bu_tel'] = $this->_post('bu_tel');
			$data['bu_del'] = $this->_post('bu_del');
			$data['bu_bid'] = $this->_post('bu_bid');
            $data['bu_subbranch'] = $this->_post('bu_subbranch');
            
            $data['bu_400'] = $this->_post("bu_400");
            $data['bu_400tel'] = $this->_post("bu_400tel");
            
			$Form = D('bank_user');
            $result =   $Form->save($data);
           
           
			if($result == 1) {
			     //��Ч
                 if($data['bu_del']==1){
                    $new = array();
                    $new['sort_del'] = 1;
                    M("sort")->where("sort_bu_id=".$data['bu_id'])->save($new);
                    //echo "aaaaaaaaaaaaaaaa";
                    //echo M("sort")->getLastSql();
                   // die();
                 }
                if($data['bu_400'] && $data['bu_400tel']){//��400
                    //��ȡ��������
                    $b_info = M("Bank")->where("b_id=".$data['bu_bid'])->find();
                    
                    $re = $this->bind400($info['bu_uname'].$b_info['b_name'].$data['bu_subbranch'],$data['bu_400'],$data['bu_400tel'],1-$data['bu_del']);
                    if(!$re){
                        $msg = "400��ʧ��!ʧ��ԭ��:".$this->error;
						//ʧ��֮��ȡ��400
						if(!$has400){
							$s['bu_400']="";
						}
						$s['bu_400tel']=$info['bu_400tel'];
						M("Bank_user")->where("bu_id=".$this->_post('bu_id'))->save($s);
                    }
                }else{
                    $msg = "�����޸İ󶨵�400!";
						//ʧ��֮��ȡ��400
						if(!$has400){
							$s['bu_400']="";
						}
						$s['bu_400tel']=$info['bu_400tel'];
						M("Bank_user")->where("bu_id=".$this->_post('bu_id'))->save($s);
                }
				//js_alert('�û����³ɹ���');exit();  $refurl
				js_alert('�û��޸ĳɹ���'.$msg,$refurl,$sty=1);exit();
			}else{
				js_alert('�û�����ʧ�ܣ�');exit();
			}

		}else{
			$bu_id = $this->_request('bu_id');
			//echo $bu_id;die;
			if(!$bu_id){
			  //	exit();
			}
			$user_info = D('bank_user')->join(' loan_bank ON loan_bank.b_id = loan_bank_user.bu_id')->where("bu_id = '$bu_id'")->field('bu_id,bu_bid,bu_uname,bu_pwd,b_name,bu_tel,bu_400,bu_400tel,bu_del,bu_indate,bu_subbranch,bu_contact')->find();

			//��ȡ������Ϣ
			$banks = $this->getBanks();
			$bank_info = array();
			if(is_array($banks)){
				foreach($banks as $val){
					$bank_info[$val['b_id']] = $val['b_name'];
				}
			}
 
			$banks_select = create_htmlable('bu_bid','select',$user_info['bu_bid'] ,$bank_info,$class='',$style='',$js='');
			$this->assign('banks_select',$banks_select);


			$this->assign('data',$user_info);
			$this->display();
		}
	}

	public function add(){

		if($this->_post('do') == 'add'){
			$ref_url = $this->_post('ref_url');
			//��ȡ������Ϣ
			$city_id = $this->loan_city_en;
			if(!$city_id){
				//
			}
			$data['bu_uname'] = $this->_post('bu_uname');
            $data['bu_contact'] = $this->_post('bu_contact');
            
			$data['bu_pwd'] = md5(trim($this->_post('bu_pwd')));
			$data['bu_tel'] = $this->_post('bu_tel');
			$data['bu_del'] = $this->_post('bu_del');
			$data['bu_bid'] = $this->_post('bu_bid');
			$data['bu_indate'] = date('Y-m-d H:i:s');
			$data['bu_city'] = $city_id;
            $data['bu_subbranch'] = $this->_post('bu_subbranch');
			$data['bu_power'] = $this->power;
			
            
            
            $data['bu_400'] = $this->_post("bu_400");
            $data['bu_400tel'] = $this->_post("bu_400tel");

			$result = M('Bank_user')->add($data);

			if($result){
                if($data['bu_400'] && $data['bu_400tel']){//��400
                    //��ȡ��������
                    $b_info = M("Bank")->where("b_id=".$data['bu_bid'])->find();
                    
                    $re = $this->bind400($data['bu_uname'].$b_info['b_name'].$data['bu_subbranch'],$data['bu_400'],$data['bu_400tel'],1-$data['bu_del']);
                    if(!$re){
                        $msg = "400��ʧ��!ʧ��ԭ��:".$this->error;
						//ʧ��֮��ȡ��400
						$s['bu_400']="";
						$s['bu_400tel']="";
						M("Bank_user")->where("bu_id=".$result)->save($s);
                    }
                }else{
                    $msg = "���ް�400!";
					//ʧ��֮��ȡ��400
						$s['bu_400']="";
						$s['bu_400tel']="";
						M("Bank_user")->where("bu_id=".$result)->save($s);
                }
				//js_alert('�û����³ɹ���');exit();  $refurl
				js_alert('�û���ӳɹ���'.$msg,$ref_url,$sty=1);exit();
			}else{
				js_alert('�û����ʧ�ܣ�����û����Ƿ��ظ�!');exit();
			}
		}else{
			//��ȡ������Ϣ
			$banks = $this->getBanks();
			$bank_info = array();
			$bank_info[0] = '��ѡ����������';
			if(is_array($banks)){
				foreach($banks as $val){
					$bank_info[$val['b_id']] = $val['b_name'];
				}
			}
			$banks_select = create_htmlable('bu_bid','select','0',$bank_info,$class='',$style='',$js='');
			$this->assign('banks_select',$banks_select);

			$this->assign('del_radio',$del_radio);
			$this->display();
		
		}
	
	
	}


	public function getBanks(){
		$result = D('bank')->where(" b_del = '0'")->field('b_id,b_name')->select() ;
		return $result;
	}
    
    private function get400(){
        $url = "http://tel400.house365.com:81/index.php/ProApi/apiGetCorr?type=finance&city=".$this->loan_city_en;
        return curl_get_contents($url);
    }
    
    private function bind400($name,$sort,$tel,$status){
        $url = "http://tel400.house365.com:81/index.php/ProApi/apiFinanceFrom?city=".$this->loan_city_en."&short_tel=".$sort."&tp_name=".urlencode($name)."&telphone=".$tel."&status=".$status;
        $re =  curl_get_contents($url);
        //js_alert($re,"",1);exit;
        $r = json_decode($re,true);
        $this->error = u2g($r['info']);
        return $r['status'];
    }
    
 



		
}

?>