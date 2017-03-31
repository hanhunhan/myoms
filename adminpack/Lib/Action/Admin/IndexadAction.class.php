<?php
	class IndexadAction extends ExtendAction {
        public function _initialize(){
			
			parent::_initialize();
			
		}
        public function index(){
            
            $where = " ad_city = '".$this->city."' ";
			$count = M('ad')->where($where)->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('ad')->where($where)->order('ad_created desc')->limit($p->firstRow.','.$p->listRows)->select();
			//echo M('ad')->getLastSql();
            
			$this->assign('re',$re);	
            $this->display();
            
        }
        public function edit(){
       	    $id = $this->_request('id');
			$id = intval($id);
        	if($this->_get("id")){
				$where = " ad_id = ".$id;
				$re = M('ad')->where($where)->find();
                if($re){
                    $pic = array();
                    $link_arr = array();
                    $picarr = unserialize($re['ad_content']);
                    if($picarr){
                        foreach($picarr as $key=>$value){
                            $pic[] = $value['pic'];
                            $link_arr[] = $value['link'];
                        }
                        
                    }
                    
                }
				
			}
            $pic_arr =  array(1,2,3,4,5,6,7,8,9,10);
   
			
			if($this->_post("id")){
			 
                
                $ss = array();
    			//$tempFile = $_FILES['Filedata']['tmp_name'];
    			import("ORG.Util.UploadFile");
    			
                foreach($pic_arr as $key=>$value){
                    $nn = array();
                    if (!empty($_FILES["Filedata_".$value]['name'])){
                        $uf = new UploadFile("Filedata_".$value);
        				$uf->setMaxSize(1024);
        				$uf->setUploadType("ftp");
        				$uf->setSaveDir("/365dai/");
        				$uf->setResizeImage(true);//是否生成调整图
        				$uf->setResizeImageSize(220);//设置缩略图大小
        				$uf->setForceResizeImage(true);//是否强制生成调整图
        
        				$rtnMSG=$uf->upload();
        				if($rtnMSG=="success"){
        					$nn['pic'] = $uf->getSaveFileURL();
        					$thumbpic = $uf->getResizeImageURL();
        				}  
                    
                    }else{
                        $pp = $this->_post("Filedata_".$value."_old");
                        $nn['pic'] = $pp;
                        
                    }
                    $nn['link'] = $this->_post("Filedata_link_".$value);
                    $ss[] = $nn;
    		     }
                $img_str = serialize($ss);
				$arr = array();
				$arr['ad_updated'] = time();
                $arr['ad_content'] = $img_str;
                
				
				$where = "ad_id = ".$id;
				$affected = M('ad')->where($where)->save($arr);
				if($affected){
					$this->success('修改成功！',U('Indexad/index'));exit();
				}else{
					$this->error('修改失败',U('Indexad/edit?todo=edit&id='.$id));exit();
				}
				
				
			}
            //print_r($pic);
            $this->assign('pic',$pic);
            $this->assign('pic_arr',$pic_arr);
            $this->assign('link_arr',$link_arr);
            
            $this->assign('todo',$todo);
			$this->assign('re',$re);
			$this->display();
        }
    
    }
    
 ?>