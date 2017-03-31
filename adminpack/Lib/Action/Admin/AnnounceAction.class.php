<?php
class AnnounceAction extends ExtendAction{
		
	function index(){
	    $a_del = $this->_post('a_del');
		$a_del = isset($a_del) ? $a_del : 0 ;
		//print_r($GLOBALS);die;
		$city_key = $this->_cookie('loan_city_en');
 
		$count = D('announce')->where("a_del = '$a_del' and a_city = '{$city_key}'")->count();

		 //分页
		import("ORG.Util.Page");
		$p = new Page($count,C('PAGESIZE'));
		$para = "&a_del=".$a_del;
		if($para) $p->parameter = $para;
		$page = $p->show();	

		$data = D('announce')->where("a_del = '$a_del' and a_city = '{$city_key}'")->field('a_id,a_title,a_time,a_del')->order('a_id')->select();
        //print_r($data);die;
        $this->assign('page',$page);
		$this->assign('data',$data);
		$this->display();
	}

	function add(){
		$do = $this->_post('do');
		$refurl = $this->_post('ref_url');
		if($do == 'add'){
			$ss['a_title'] = trim($this->_post('a_title'));
			$ss['a_content'] = $_POST['a_content'];
			$ss['a_city'] = $this->_cookie('loan_city_en');
			$ss['a_time'] = time();
			$a_id = D('announce')->add($ss);
			//插入到公告和银行的映射表
			$bu_ids = $_POST['bu_id'];
			$sql = 'insert into loan_announce_map (bu_id,a_id) values ';
			$insert_flag = 0;
			if(is_array($bu_ids) && $a_id){
				foreach($bu_ids as $val){
					$insert_flag = 1;
					$sql .= "({$val},'{$a_id}'),";
				}
			}
			$sql = rtrim($sql,',');
			if($insert_flag == 1){
				D('announce_map')->execute($sql);
			}
			
		    if($a_id){
				js_alert('公告添加成功！',$refurl,$sty=1);exit();
			}else{
				js_alert('公告添加失败！',$refurl,$sty=1);exit();
			}
		}
		$city_key = $this->_cookie('loan_city_en');
		//获取银行用户机构
		$bank_arr = D('bank_user')->join('loan_bank on loan_bank.b_id=loan_bank_user.bu_bid')->where(" bu_del = '0' and b_del = '0' and bu_city = '{$city_key}'")->field('bu_id,b_name,bu_subbranch')->select();
		//print_r($bank_arr);die;
		$banks = array();
        if(is_array($bank_arr)){
			foreach($bank_arr as $val){
				$banks[$val['bu_id']] = $val['b_name'].$val['bu_subbranch'];
			}
		}
		$banks_checkbox = create_htmlable('bu_id[]','checkbox','',$banks,$class='',$style='',$js='');

		$this->assign('banks_checkbox',$banks_checkbox);
		$this->display();
	}

	
	function edit(){
		$do = $this->_post('do');
		if($do == 'edit'){
			$refurl = $this->_post('ref_url');
			$ss['a_id'] = $a_id = $this->_post('a_id');
			$ss['a_title'] = trim($this->_post('a_title'));
			$ss['a_content'] = $_POST['a_content'];
			$ss['a_del'] = $this->_post('a_del');
			$result = D('announce')->save($ss);
            
            //插入到公告和银行的映射表
			$b_ids = $_POST['bu_id'];
			$sql = 'insert into loan_announce_map (bu_id,a_id) values ';
			$insert_flag = 0;
			if(is_array($b_ids) && $a_id){
				foreach($b_ids as $val){
					$insert_flag = 1;
					$sql .= "({$val},'{$a_id}'),";
				}
			}
			$sql = rtrim($sql,',');
			if($insert_flag == 1 && $ss['a_del'] != '1'){
				$result2 =  D('announce_map')->execute($sql);
			}

			if($ss['a_del'] == '1'){
				$sql = "delete from loan_announce_map where a_id ='{$a_id}';";
			    $result3 = D('announce_map')->execute($sql);
			}
			
			if($result == 1 || $result2 || $result3 ){
				js_alert('公告或分配修改成功！',$refurl,$sty=1);exit();
			}else{
				js_alert('公告或分配修改失败！',$refurl,$sty=1);exit();
			}
		} 
		$a_id = $this->_request('a_id');
		$data = D('announce')->where("a_id = '{$a_id}'")->find();

		//获取该公告下的银行机构
		$announce_map = D('announce_map')->where("a_id = '{$a_id}' and am_del = '0'")->field('bu_id')->select() ;
		$bank_ids = array();
		if(is_array($announce_map)){
			foreach($announce_map as $val){
				$bank_ids[$val['bu_id']] = $val['bu_id'];
			}
		}
        
		$city_key = $this->_cookie('loan_city_en');
		//获取银行用户机构
		$bank_arr = D('bank_user')->join('loan_bank on loan_bank.b_id=loan_bank_user.bu_bid')->where(" bu_del = '0' and b_del = '0' and bu_city = '{$city_key}'")->field('bu_id,b_name,bu_subbranch')->select();

		$banks = array();
        if(is_array($bank_arr)){
			foreach($bank_arr as $val){
				if(!in_array($val['bu_id'],$bank_ids)){
					$banks[$val['bu_id']] = $val['b_name'].$val['bu_subbranch'];
				}
			}
		}

	
		//生成前台html
		$banks_checkbox = create_htmlable('bu_id[]','checkbox',$bank_ids,$banks,$class='',$style='',$js='');
		if(!$banks_checkbox) $banks_checkbox = '已经全部分配';
	    $this->assign('banks_checkbox',$banks_checkbox);
		$this->assign('data',$data);
		$this->display();
	}
}
?>