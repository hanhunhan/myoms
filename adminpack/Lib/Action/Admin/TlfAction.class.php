<?php
	class TlfAction extends ExtendAction{
		//列表
		function get_prj_list(){
			$where=" prj_city='".$this->city."' ";
			$prj_list = M('project')->where($where)->order("prj_sort desc")->select();
			//print_r($prj_list);
			return $prj_list;
			
		}
		function index() {
			$prj_list = $this->get_prj_list();
			$prj_idname = array();
			if($prj_list){
				foreach ($prj_list as $key=>$value){
					$prj_idname[$value['prj_id']] = $value['prj_itemname'];
				}
			
			}
			//楼盘名
			import("ORG.Util.Page");
			$where=" prj_city='".$this->city."' ";
			$prj_id = intval($this->_get('prj_id'));
			if($prj_id){
				$where .=" and tg_prj_id='".$prj_id."' ";
			}
			
			$count = M('tlf_group')->where($where)->count();

			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('tlf_group')->join(" project pro on tlf_group.tg_prj_id = pro.prj_id")->where($where)->order('tg_sortorder desc,tg_endtime desc,tg_id desc')->limit($p->firstRow.','.$p->listRows)->select();
			//echo M('tlf_group')->getLastSql();
			$re = $this->format_tlf($re,$prj_idname);
			//print_r($re);
			$itemname = $prj_idname[$prj_id]?($prj_idname[$prj_id]."-"):"";

			$this->assign('itemname',$itemname);
			$this->assign('page',$page);
			$this->assign('re',$re);
			$this->display('index');

		}
		//数据格式化
		function format_tlf($data,$prj_idname) {
			
			//print_r($prj_list);
			//print_r($prj_idname);
			if($data){
				$status_conf = array("是","否",);
				$time = time();
				foreach($data as $key=>$value){
					$data[$key]['tg_name'] = $prj_idname[$value['tg_prj_id']];
					if($value['tg_endtime']<$time){
						$data[$key]['endtime'] = date("Y-m-d H:i:s",$value['tg_starttime'])."-".date("Y-m-d H:i:s",$value['tg_endtime']);;
						$data[$key]['stauts'] = '0';
					}else{
						$data[$key]['endtime'] = date("Y-m-d H:i:s",$value['tg_starttime'])."-".date("Y-m-d H:i:s",$value['tg_endtime']);
						$data[$key]['stauts'] = '1';
					}
					$data[$key]['review_flag'] = $status_conf[$value['tg_review_flag']];
					$data[$key]['sale'] = $status_conf[$value['tg_sale']];
					$data[$key]['status'] = $status_conf[$value['tg_status']];
					
					
				}
			}
			return $data;
		}
		//编辑
		function edit() {
			$prj_list = $this->get_prj_list();
			//print_r($prj_list);
			$tg_id = intval($this->_get('id'));
			if($tg_id){
				$info = M('tlf_group')->where("tg_id='$tg_id'")->find();
				//print_r($info);
				$info['tg_nav_pic']=@unserialize($info['tg_nav_pic']);//楼盘页滚动图片
					
				$sizeof_tg_nav_pic=sizeof($info['tg_nav_pic']);
				
				$info['tg_starttime']= date("Y-m-d H:i:s",$info['tg_starttime']);
				$info['tg_endtime']= date("Y-m-d H:i:s",$info['tg_endtime']);
			}
			
			//print_r($info['tg_nav_pic']);
			//print_r($info['tg_nav_pic_title']);
			
			if($_POST['item']['tg_prj_id']){
				import("ORG.Util.UploadFile");
				$data = $_POST['item'];
				if($_POST['tg_views_old']==$data['tg_views']){
					unset($data['tg_views']);
				}
				$data['tg_pay_money'] = intval($data['tg_pay_money']);
				
				$power_city = $this->city;
				
				$data['tg_starttime']= strtotime($data['tg_starttime']);
				$data['tg_endtime']= strtotime($data['tg_endtime']);
				/************************上传楼盘页滚动图片**********************************/
				
				
				$data['tg_nav_pic'] = array();
			
				if(!empty($_FILES['tg_nav_pic'][tmp_name])){
					foreach($_FILES['tg_nav_pic'][tmp_name] as $key=>$value){
						if($value){
							$_FILES['new_pic_'.$key] = array();
							$_FILES['new_pic_'.$key]['name']=$_FILES['tg_nav_pic']['name'][$key];
							$_FILES['new_pic_'.$key]['type']=$_FILES['tg_nav_pic']['type'][$key];
							$_FILES['new_pic_'.$key]['tmp_name']=$_FILES['tg_nav_pic']['tmp_name'][$key];
							$_FILES['new_pic_'.$key]['error']=$_FILES['tg_nav_pic']['error'][$key];
							$_FILES['new_pic_'.$key]['size']=$_FILES['tg_nav_pic']['size'][$key];
							$uf=new UploadFile('new_pic_'.$key);
							$uf->setMaxSize(2048);
							$uf->setResizeImage(true);
							$uf->setResizeWidth(230);
							$uf->setResizeHeight(230);
							//$uf->setFilledBlank(true);
							$uf->setUploadType("ftp");
							$uf->setSaveDir("/".$power_city."/");
							$uf->setShowAsChinese(true);
							
							if(($rtnMSG=$uf->upload())=="success"){
								$data['tg_nav_pic'][]=$uf->getSaveFileURL();
							}
							else{
								die("<script type=\"text/javascript\">alert('文件操作失败,可能原因：$rtnMSG'); </script>");
							}
						}else{
							
							$data['tg_nav_pic'][]=$_POST['tg_nav_pic_old'][$key];
						}
						
					}
					
				}
				
				
				
				$data['tg_nav_pic']=@serialize($data['tg_nav_pic']);
				
				
				//print_r($data);
				$data['tg_city'] = $this->city;
				
				
				if($data['tg_id']){
					$affected = M('tlf_group')->where("tg_id='".$data['tg_id']."'")->save($data);
				}else{
					unset($data['tg_id']);
					$affected = M('tlf_group')->add($data);
				}
				
				if($affected){
					$this->success('修改成功！',U("Tlf/index"));
				}else{
					$this->error('修改失败！',U("Tlf/index"));
				}
				die();
				
			}
			
			
			$this->assign('sizeof_tg_nav_pic',$sizeof_tg_nav_pic);
			$this->assign('re',$info);
			$this->assign('prj_list',$prj_list);
			$this->display('edit');

		}
		
	}
?>