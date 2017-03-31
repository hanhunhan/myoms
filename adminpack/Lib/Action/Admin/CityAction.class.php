<?php
	class CityAction extends ExtendAction{
		
		public function _initialize(){
			
			parent::_initialize();
			
		}

		public function index(){
		
			$power = $_COOKIE['loan_power'];
			$channel = $_COOKIE['loan_city_en'];
			$where = " 1=1 ";
			$count = M('city')->where($where)->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('city')->where($where)->order('city_order asc ,city_created desc')->limit($p->firstRow.','.$p->listRows)->select();
			$this->assign('re',$re);	
			//print_r($re);
			$this->display();
			
		}

		public function edit(){
			$id = $this->_request('id');
			$id = intval($id);
			
			$todo = $this->_request('todo');
			if($todo=='lock'||$todo=='unlock'){
				$ss = array();
				if($todo=='lock'){
					$ss['city_del'] = 1;
				}
				if($todo=='unlock'){
					$ss['city_del'] = 0;
				}
				$where = "city_id = ".$id;
				$affected = M('city')->where($where)->save($ss);
				if($affected){
					$this->success('修改成功！',U('City/index'));exit();
				}else{
					$this->error('修改失败',U('City/edit?todo=edit&id='.$id));exit();
				}
			}

			if($todo=='edit'||$todo=='add'){
				if($this->_get("id")){
					$where = " city_id = ".$id;
					$re = M('city')->where($where)->find();
					
				}
				if($this->_post("city_name")){
					$ss = array();
					$ss['city_name'] = $this->_post('city_name');
					$ss['city_key'] = $this->_post('city_key');
					$ss['city_order'] = $this->_post('city_order');
					$ss['city_updated'] = time();
					if(!$id){
						$ss['city_created'] = time();
						$power = $_COOKIE['loan_power'];
						$affected = M("city")->add($ss);
						if($affected){
							$this->success('银行添加成功！',U('City/index'));exit();
						}else{
							$this->error('银行添加失败',U('City/edit?todo=add'));exit();
						}
					}else{
						$where = "city_id = ".$id;
						$affected = M('city')->where($where)->save($ss);
						if($affected){
							$this->success('城市修改成功！',U('City/index'));exit();
						}else{
							$this->error('城市修改失败',U('City/edit?todo=edit&id='.$id));exit();
						}
					}
					
				}
				
				$this->assign('todo',$todo);
				$this->assign('re',$re);
				$this->display();
			}
		}

		
	}
?>