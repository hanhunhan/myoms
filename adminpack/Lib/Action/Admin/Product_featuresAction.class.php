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
					$this->success('�޸ĳɹ���',U('Product_features/index'));exit();
				}else{
					$this->error('�޸�ʧ��',U('Product_features/edit?todo=edit&id='.$id));exit();
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
        				$uf->setResizeImage(true);//�Ƿ����ɵ���ͼ
        				$uf->setResizeImageSize(220);//��������ͼ��С
        				$uf->setForceResizeImage(true);//�Ƿ�ǿ�����ɵ���ͼ
        
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
							$this->success('��Ŀ��ɫ��ӳɹ���',U('Product_features/index'));exit();
						}else{
							$this->error('��Ŀ��ɫ���ʧ��',U('Product_features/edit?todo=add'));exit();
						}
					}else{
						$where = " pf_id = ".$id;
						$affected = M('product_features')->where($where)->save($ss);
						if($affected){
							$this->success('��Ŀ��ɫ�޸ĳɹ���',U('Product_features/index'));exit();
						}else{
							$this->error('��Ŀ��ɫ�޸�ʧ��',U('Product_features/edit?todo=edit&id='.$id));exit();
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