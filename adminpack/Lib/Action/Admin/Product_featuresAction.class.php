<?php
	class Product_featuresAction extends ExtendAction{
		
		public function _initialize(){
			
			parent::_initialize();
			
		}

		public function index(){
		
			
		
			$count = M('product_features')->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('product_features')->order('pf_created desc')->limit($p->firstRow.','.$p->listRows)->select();
			$this->assign('re',$re);	
			$this->assign('page',$page);
			$this->display();
			
		}

		public function edit(){
			$id = $this->_request('id');
			$id = intval($id);
			
			$todo = $this->_request('todo');
			if($todo=='lock'||$todo=='unlock'){
				$ss = array();
				if($todo=='lock'){
					$ss['pf_del'] = 1;
				}
				if($todo=='unlock'){
					$ss['pf_del'] = 0;
				}
				$where = "pf_id = ".$id;
				$affected = M('product_features')->where($where)->save($ss);
				if($affected){
					$this->success('修改成功！',U('Product_features/index'));exit();
				}else{
					$this->error('修改失败',U('Product_features/edit?todo=edit&id='.$id));exit();
				}
			}

			if($todo=='edit'||$todo=='add'){
				if($this->_get("id")){
					$where = " pf_id = ".$id;
					$re = M('product_features')->where($where)->find();
					
				}
				if($this->_post("pf_name")){
					$ss = array();
					$ss['pf_name'] = $this->_post('pf_name');
                    import("ORG.Util.UploadFile");
                    if (!empty($_FILES["pf_img"]['name'])){
                        $uf = new UploadFile("pf_img");
        				$uf->setMaxSize(1024);
        				$uf->setUploadType("ftp");
        				$uf->setSaveDir("/365dai/");
        				$uf->setResizeImage(true);//是否生成调整图
        				$uf->setResizeImageSize(220);//设置缩略图大小
        				$uf->setForceResizeImage(true);//是否强制生成调整图
        
        				$rtnMSG=$uf->upload();
        				if($rtnMSG=="success"){
        					$ss['pf_img'] = $uf->getSaveFileURL();
        					$thumbpic = $uf->getResizeImageURL();
        				}  
                    
                    }else{
                        $ss['pf_img'] = $this->_post('pf_img_old');
                        
                    }
					   
					$ss['pf_updated'] = time();
					if(!$id){
						$ss['pf_created'] = time();
						
						$power = $_COOKIE['loan_power'];
						$affected = M("product_features")->add($ss);
						if($affected){
							$this->success('项目特色添加成功！',U('Product_features/index'));exit();
						}else{
							$this->error('项目特色添加失败',U('Product_features/edit?todo=add'));exit();
						}
					}else{
						$where = " pf_id = ".$id;
						$affected = M('product_features')->where($where)->save($ss);
						if($affected){
							$this->success('项目特色修改成功！',U('Product_features/index'));exit();
						}else{
							$this->error('项目特色修改失败',U('Product_features/edit?todo=edit&id='.$id));exit();
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