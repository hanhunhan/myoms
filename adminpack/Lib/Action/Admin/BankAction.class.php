<?php
	class BankAction extends ExtendAction{
		
		public function _initialize(){
			
			parent::_initialize();
			
		}

		public function index(){
        
        	$organization = JJ('organization');
            

              
			$count = M('bank')->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('bank')->order('b_created desc')->limit($p->firstRow.','.$p->listRows)->select();
			//echo M('bank')->getLastSql();
            
            $this->assign('page',$page);
            $this->assign('organization',$organization);
			$this->assign('re',$re);	
			$this->display();
			
		}
        
		public function edit(){
			$id = $this->_request('id');
			$id = intval($id);
            
            $organization = JJ('organization');
			
			$todo = $this->_request('todo');
			if($todo=='lock'||$todo=='unlock'){
				$ss = array();
				if($todo=='lock'){
					$ss['b_del'] = 1;
				}
				if($todo=='unlock'){
					$ss['b_del'] = 0;
				}
				$where = "b_id = ".$id;
				$affected = M('bank')->where($where)->save($ss);
				if($affected){
					$this->success('修改成功！',U('Bank/index'));exit();
				}else{
					$this->error('修改失败',U('Bank/edit?todo=edit&id='.$id));exit();
				}
			}

			if($todo=='edit'||$todo=='add'){
				if($this->_get("id")){
					$where = " b_id = ".$id;
					$re = M('bank')->where($where)->find();
				//	print_r($re);
				}
				$ss = array();
				if (!empty($_FILES["Filedata"]['name'])){
					//$tempFile = $_FILES['Filedata']['tmp_name'];
					import("ORG.Util.UploadFile");
					$uf = new UploadFile("Filedata");
					$uf->setMaxSize(1024);
					$uf->setUploadType("ftp");
					$uf->setSaveDir("/365dai/");
					$uf->setResizeImage(true);//是否生成调整图
					$uf->setResizeImageSize(220);//设置缩略图大小
					$uf->setForceResizeImage(true);//是否强制生成调整图

					$rtnMSG=$uf->upload();
					if($rtnMSG=="success"){
						$ss['b_logo'] = $uf->getSaveFileURL();
						$thumbpic = $uf->getResizeImageURL();
					}
				}else{
				    $ss['b_logo'] = $this->_post("Filedata_old");
                    
				}
				if($this->_post("b_name")){
					
                    $ss['b_organization_type'] = $this->_post('b_organization_type');
					$ss['b_name'] = $this->_post('b_name');
					$ss['b_updated'] = time();
					if(!$id){
						$ss['b_created'] = time();
						$power = $_COOKIE['loan_power'];
						$affected = M("bank")->add($ss);
						if($affected){
							$this->success('银行添加成功！',U('Bank/index'));exit();
						}else{
							$this->error('银行添加失败',U('Bank/edit?todo=add'));exit();
						}
					}else{
						$where = "b_id = ".$id;
						$affected = M('bank')->where($where)->save($ss);
						if($affected){
							$this->success('银行修改成功！',U('Bank/index'));exit();
						}else{
							$this->error('银行修改失败',U('Bank/edit?todo=edit&id='.$id));exit();
						}
					}
					
				}
				
                
                $this->assign('organization',$organization);
				$this->assign('todo',$todo);
				$this->assign('re',$re);
				$this->display();
			}
		}
        
        

		
	}
?>