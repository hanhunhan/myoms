<?php
	class DepartmentAction extends ExtendAction {

		function listt(){
			$mod = $this->_request('mod');
			$act = $this->_request('act');
			$roleid = $this->_request('roleid');
			$password = $this->_request('rolepass');

			if($mod=='edit'){
				$data['DMNAME'] = iconv("UTF-8","GB2312//IGNORE" , $_REQUEST['DMNAME']);;
				//$data['LEVELS'] = $_REQUEST['LEVELS'];
				$data['QUEUE'] = $_REQUEST['QUEUE'];
				$data['STATUS'] = $_REQUEST['STATUS'];
				 
				if(M('admin_depart')->where("ID='".$_REQUEST['ID']."'")->save($data) ){
					$rs['msg'] = 'ok';
					
				}else $rs['msg'] = 'error';
				 
				exit(json_encode($rs));
				 
			}elseif($mod=='add'){
				$data['DMNAME'] = iconv("UTF-8","GBk" , urldecode($_REQUEST['DMNAME']) );;
				$data['LEVELS'] = $_REQUEST['LEVELS'];
				$data['QUEUE'] = $_REQUEST['QUEUE'];
				$data['STATUS'] = $_REQUEST['STATUS'];
				$data['PARENTID'] = $_REQUEST['PARENTID'];
				if(M('admin_depart')->add($data) ){
					$rs['msg'] = 'ok';
					
				}else $rs['msg'] = 'error';
				 
				exit(json_encode($rs));
			 
			}elseif($mod=='del'){
				 
				exit();
			}elseif($mod=='getJson'){  
				$where = $_REQUEST['id']  ? "  PARENTID='".$_REQUEST['id']."'":"LEVELS=0  "; 
				$record = M('admin_depart')->where($where)->order('QUEUE asc')->select();//
				foreach($record as $key=>$val){
					$record[$key]['DMNAME'] = iconv("GB2312//IGNORE", "UTF-8", $val['DMNAME']); 
					
					$c=M('admin_depart')->where("PARENTID='".$val['ID']."'")->count();
					$record[$key]['state'] = $c ? 'closed':'opened';
					//$record[$key]['children'] = null;
				}
				//$res['total'] = count($record);
				//$res['rows'] = $record;
				//$res['footer'] = null;
				echo json_encode($record); 
				exit();
			}
			
			if($act=='edit' && $roleid){
				 
				exit();
			}elseif($act=='add'){
				 
				js_show('info','','恭喜添加成功哦！');
				exit();
			}elseif($act=='del'){
				 
				js_show('info','','恭喜删除成功哦！');
				exit();
			}
			/**********以上为iframe操作*********/
			
			$this->assign('menu',$menu);
			$this->display('listt');
		}
	}
?>