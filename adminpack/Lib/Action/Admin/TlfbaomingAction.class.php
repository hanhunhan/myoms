<?php
	class TlfbaomingAction extends ExtendAction{
		//�б�
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

			$where=" prj_city='".$this->city."' ";
			$re = M('tlf_group')->join(" project pro on tlf_group.tg_prj_id = pro.prj_id")->where($where)->order('tg_sortorder desc,tg_endtime desc,tg_id desc')->select();
			$tg_id_arr = array();
			$tg_arr = array(); 
			$tg_prj_arr = array();
			if($re){
				foreach($re as $key=>$value){
					$tg_id_arr[] = $value['tg_id'];
					$tg_arr[$value['tg_id']] = $value['tg_lpbiaoyu'];
					$tg_prj_arr[$value['tg_id']] = $value['tg_prj_id'];
				}
			}
			$tg_ids = implode(",",$tg_id_arr);


			$count = M('tlf_teambuy')->where(" tb_tg_id in (".$tg_ids.")")->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	

			$re = M('tlf_teambuy')->where("tb_tg_id in (".$tg_ids.")")->limit($p->firstRow.','.$p->listRows)->select();;
			//echo M('tlf_teambuy')->getLastSql();
			$re = $this->format_tlf($re,$prj_idname,$tg_arr,$tg_prj_arr);
			//print_r($re);
			//die();
			
			//print_r($re);
			$this->assign('prj_idname',$prj_idname);
			$this->assign('tg_arr',$tg_arr);
			$this->assign('page',$page);
			$this->assign('re',$re);
			$this->display('index');

		}
		//���ݸ�ʽ��
		function format_tlf($data,$prj_idname,$tg_arr,$tg_prj_arr) {
			
			//print_r($prj_list);
			//print_r($prj_idname);
			if($data){
				
				foreach($data as $key=>$value){
					$data[$key]['tg_lpbiaoyu'] = $prj_idname[$tg_prj_arr[$value['tb_tg_id']]]."(".$tg_arr[$value['tb_tg_id']].")";
				
					$data[$key]['dateline'] = date("Y-m-d H:i:s",$value['dateline']);
						
					

					
				}
			}
			return $data;
		}
		//�༭
		function edit() {
			$prj_list = $this->get_prj_list();
			//print_r($prj_list);
			$tg_id = intval($this->_get('id'));
			if($tg_id){
				$info = M('tlf_group')->where("tg_id='$tg_id'")->find();
				//print_r($info);
				$info['tg_nav_pic']=@unserialize($info['tg_nav_pic']);//¥��ҳ����ͼƬ
					
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
				/************************�ϴ�¥��ҳ����ͼƬ**********************************/
				
				
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
								die("<script type=\"text/javascript\">alert('�ļ�����ʧ��,����ԭ��$rtnMSG'); </script>");
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
					$this->success('�޸ĳɹ���',U("Tlf/index"));
				}else{
					$this->error('�޸�ʧ�ܣ�',U("Tlf/index"));
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