<?php
	class GroupAction extends ExtendAction {

		protected function getGroup($sqlwhere = ''){
			$city = M('Erp_city')->where("ID='".$_SESSION['uinfo']['city']."'")->find();
			$cityname = $city['NAME'];  
			//if(!$this->p_auth_all) $sqlwhere = " and LOAN_GROUPNAME like '%$cityname%'";
			$count = M('Erp_group')->where("LOAN_GROUPDEL=0 $sqlwhere ")->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			$page = $p->show();		
			$re = M('Erp_group')->where("LOAN_GROUPDEL=0 $sqlwhere ")->order('LOAN_GROUPCREATED desc')->limit($p->firstRow.','.$p->listRows)->select();
			return array('val'=>$re,'page'=>$page);
		}

		protected function getAllRole(){
			$propy = array();
			$menu = M('Erp_role')->where("LOAN_ROLEPARENTID=0 and LOAN_ROLEDISPLAY=0")->order("LOAN_ROLEORDER ASC")->select();//菜单栏目parentID为0
			if(is_array($menu)){
				foreach($menu as $key=>$m_val){
					$propy[$key]['menuName'] = $m_val['LOAN_ROLENAME'];
					$propy[$key]['menuID'] = $m_val['LOAN_ROLEID'];
					$smenu = M('Erp_role')->where("LOAN_ROLEPARENTID='$m_val[LOAN_ROLEID]' and LOAN_ROLEDISPLAY=0")->order("LOAN_ROLEORDER ASC")->select();
					if(is_array($smenu)){
						foreach($smenu as $s_key=>$s_val){
							$propy[$key]['smenu'][$s_key]['smenuval'] = $s_val;	
							$role = M('Erp_role')->where("LOAN_ROLEPARENTID='$s_val[LOAN_ROLEID]' and LOAN_ROLEDISPLAY=0")->order("LOAN_ROLEORDER ASC")->select();
							if(is_array($role)){
								foreach($role as $r_val){
									$propy[$key]['smenu'][$s_key]['sroleval'][] = $r_val;
								}
							}
									
						}
					}
				}				
			}
			return $propy;
		}

		function viewGroup(){
            // 获取所有的权限组供搜索时用
            $allGroup = M('Erp_group')->field('LOAN_GROUPID, LOAN_GROUPNAME')->where("LOAN_GROUPDEL=0")->order('LOAN_GROUPCREATED desc')->select();
			$group = $this->getGroup();
			$this->assign('re',$group['val']);
			$this->assign('page',$group['page']);
            $this->assign('all_group', $allGroup);
			$this->display('group');
		}
		
		function addGroup(){
			$mod = 'add';
			$act = $this->_post('act');
		
			$propy = $this->getAllRole();
			$this->assign('propy',$propy);											

			if($act=='add'){
				$created = time();
				$rolemain = $_POST['rolemain'];
				
				if(is_array($rolemain) && count($rolemain)){
					foreach($rolemain as $r_val){							
						$role = $_POST['role'.$r_val];
						if($r_val) $roleval[] = $r_val;
						if(is_array($role) && count($role)){
							foreach($role as $val){
								$roleval[] = $val;
							}
						}
					}
					if($roleval){
						$ss['LOAN_GROUPVAL'] = join(',',$roleval);
						$ss['LOAN_GROUPNAME'] = $this->_post('groupName');
						$ss['LOAN_GROUPSTATUS'] = $this->_post('status');	
						$ss['LOAN_GROUPALL'] = $this->_post('auth');	
						$ss['LOAN_BASE'] = $this->_post('bases');
						$ss['LOAN_GROUPCUSTOM'] = $this->_post('iscustom');
						$ss['LOAN_GROUPCREATED'] = $ss['LOAN_GROUPUPDATED'] = $created;	
						
						$insertId = D('erp_group')->add($ss);
                       
						if($insertId){
							$this->success('权限组添加成功！',U('Group/viewGroup'));exit();
						}else{
							$this->error('权限组添加失败',U('Group/addGroup'));exit();
						}
					}

				}
			}

            // 获取所有的权限组供搜索时用
            $allGroup = M('Erp_group')->field('LOAN_GROUPID, LOAN_GROUPNAME')->where("LOAN_GROUPDEL=0")->order('LOAN_GROUPCREATED desc')->select();
            $this->assign('all_group', $allGroup);
			$group = $this->getGroup();
			$this->assign('re',$group['val']);
			$this->assign('page',$group['page']);
			$this->assign('act',$mod);
			$this->assign('action',U('Group/addGroup'));
			$this->display('Group:group');		
		}

		function editGroup(){
			$act = $this->_post('act');
			$id = $this->_request('id');
			$groupRole = array();$mod = 'edit';
			$group = M('Erp_group')->where("LOAN_GROUPID='$id' and LOAN_GROUPDEL=0")->find();
			if(!is_array($group)) { $this->error('该权限组不存在！',U("Group/viewGroup"));exit();}
			$groupAll = $this->getGroup();//获取所有角色

			if($act=='edit' && $id){
				$newrole = array();
				$rolemain = $_POST['rolemain'];
				if(is_array($rolemain)){
					foreach($rolemain as $r_val){
						if($r_val) $newrole[] = $r_val;
						$role = $_POST['role'.$r_val];
						if(is_array($role) && count($role)){
							foreach($role as $val){
								$newrole[] = $val;	
							}
						}
					}
				}

				$ss = array();
				$ss['LOAN_GROUPVAL'] = join(',',$newrole);
				$ss['LOAN_GROUPNAME'] = $this->_post('groupName');
				$ss['LOAN_GROUPSTATUS'] = $this->_post('status');
				$ss['LOAN_GROUPALL'] = $this->_post('auth');
				$ss['LOAN_BASE'] = $this->_post('bases');
				$ss['LOAN_GROUPCUSTOM'] = $this->_post('iscustom');
				$ss['LOAN_VMEM'] = $this->_post('LOAN_VMEM');
				$ss['LOAN_GROUPUPDATED'] = time();
				$affected = D('erp_group')->where("LOAN_GROUPID='$id'")->save($ss);

				if($affected===false){
					$this->error('权限组修改失败',U('Group/addGroup'));exit();					
				}else{
					$this->success('权限组修改成功！',U('Group/viewGroup'));exit();//用户组的编辑*/
				}
			}


            $groupRole = explode(',',$group['LOAN_GROUPVAL']);
			$role = $this->getAllRole();
			if(is_array($role) && is_array($groupRole) ){
				foreach($role as $r_key=>$menu){
					foreach($menu['smenu'] as $m_key=>$smenu){
						$smenuval = $smenu['smenuval'];
						if(is_array($smenuval) && in_array($smenuval['LOAN_ROLEID'],$groupRole)) $role[$r_key]['smenu'][$m_key]['smenuval']['loan_status']=1;
						$sroleval = $smenu['sroleval'];
						if(is_array($sroleval)){
							foreach($sroleval as $s_key=>$s_val){
								if(in_array($s_val['LOAN_ROLEID'],$groupRole)) $role[$r_key]['smenu'][$m_key]['sroleval'][$s_key]['loan_status']=1;
							}
						}
					}
				}
			}//通过tp_status来确定用户组哪些功能是选中的

			$this->assign('propy',$role);
			$this->assign('act',$mod);


			$this->assign('group',$group);

            $allGroup = M('Erp_group')->field('LOAN_GROUPID, LOAN_GROUPNAME')->where("LOAN_GROUPDEL=0")->order('LOAN_GROUPCREATED desc')->select();
            $this->assign('all_group', $allGroup);

            $this->assign('re',$groupAll['val']);
			$this->assign('page',$groupAll['page']);
			$this->assign('action',U('Group/editGroup'));
			$this->display('Group:group');			

		}

		function deleteGroup(){
			$id = $this->_get('id');
			M('Erp_group')->where("LOAN_GROUPID='$id'")->delete();//删除权限组
			$affected = $this->success('用户组删除成功！',U('Group/viewGroup'));
			if($affected===false){
				$this->error('权限组删除失败',U('Group/addGroup'));exit();	
			}else{
				//$sql = M('group')->getLastSql();
				//$this->logs($sql);
				$this->success('权限组删除成功！',U('Group/viewGroup'));exit();//用户组的编辑*/
			}
		}

		function menu(){
			$mod = $this->_request('mod');
			$act = $this->_request('act');
			$tact = $this->_request('tact');
			$roleid = $this->_request('roleid');
			$password = $this->_request('rolepass');

			if($mod=='edit'){
				$roleid = $this->_request('roleid');
				$record = M('Erp_role')->where("LOAN_ROLEDISPLAY=0 and LOAN_ROLEID='$roleid'")->find();				
				echo '<script>';
				echo "parent.document.getElementById('rolename').value = '".$record['LOAN_ROLENAME']."';";
				echo "parent.document.getElementById('rolemodule').value = '".$record['LOAN_ROLECONTROL']."';";
				echo "parent.document.getElementById('roleaction').value = '".$record['LOAN_ROLEACTION']."';";
				echo "parent.document.getElementById('roleparam').value = '".$record['LOAN_PARAM']."';";
				echo "parent.document.getElementById('rolesort').value = '".$record['LOAN_ROLEORDER']."';";
				echo "parent.document.getElementById('rolepass').value = '';";
				echo "parent.document.getElementById('act').value = '".$mod."';";
				echo "parent.document.getElementById('roleid').value = '".$record['LOAN_ROLEID']."';";
				echo "var menshow = parent.document.getElementsByName('roledisplay');";
				echo "for (var i=0; i<menshow.length; i++){  ";
				echo " if (menshow[i].value=='".$record['LOAN_MENUSHOW']."' ) {  ";
				echo " menshow[i].checked= true;  ";
				echo " break;  }}";
				echo "parent.showboxy('a');";
				echo "parent.showm();";
				echo "parent.showp();";
				if($tact=='param'){
					echo "parent.showp();";
					echo "parent.hidem();";
				}
				if($tact=='oper'){
					echo "parent.hidem();";
				}
				
				
				echo '</script>';
				exit();
			}elseif($mod=='add'){
				echo '<script>';
				echo "parent.document.getElementById('rolename').value = '';";
				echo "parent.document.getElementById('rolemodule').value = '';";
				echo "parent.document.getElementById('roleparam').value = '';";
				echo "parent.document.getElementById('roleaction').value = '';";
				echo "parent.document.getElementById('rolesort').value = '';";
				echo "parent.document.getElementById('rolepass').value = '';";
				echo "parent.document.getElementById('act').value = '".$mod."';";
				echo "parent.document.getElementById('roleid').value = '".$roleid."';";
				echo "parent.showboxy('a');";
				echo "parent.showm();";
				echo "parent.showp();";
				if($tact=='param'){
					echo "parent.showp();";
					echo "parent.hidem();";
				}
				if($tact=='oper'){
					echo "parent.hidem();";
				}
				echo '</script>';
				exit();				
			}elseif($mod=='del'){
				echo '<script>';
				echo "parent.document.getElementById('rolepass').value = '';";
				echo "parent.document.getElementById('act').value = '".$mod."';";
				echo "parent.document.getElementById('roleid').value = '".$roleid."';";
				echo "parent.showboxy('d')";
				echo '</script>';
				exit();
			}
			
			if($act=='edit' && $roleid){
				if($password!=='cxxpyw'){
					js_show('info',1,'囧，密码错误哦！');
					exit();
				}
				$module = ucfirst($this->_request('rolemodule'));
				$action = $this->_request('roleaction');
				$ss['LOAN_ROLENAME'] = $this->_post('rolename',false);
                //var_dump($this->_request('rolemodule'));
				$ss['LOAN_ROLECONTROL'] = $module;
				$ss['LOAN_ROLEACTION']  = $action;
				$ss['LOAN_ROLEORDER'] = $this->_request('rolesort');
				$ss['LOAN_MENUSHOW'] = $this->_request('roledisplay');
				$ss['LOAN_PARAM'] = $this->_request('roleparam');
				$d = D('Erp_role');
				 //$d = new Erp_roleModel();
				$d->where("LOAN_ROLEID='$roleid'")->save($ss); 
				//echo $d->getLastSql();
				js_show('info','','恭喜修改成功哦！');
				exit();
			}elseif($act=='add'){
				if($password!=='cxxpyw'){
					//js_show('info',1,'囧，密码错误哦！');
					//exit();
				}
				$module = ucfirst($this->_request('rolemodule'));
				$action = $this->_request('roleaction');
				$param = $this->_request('roleparam');
				$record = M('Erp_role')->where("LOAN_ROLEDISPLAY=0 and LOAN_ROLEPARENTID<>0  and LOAN_ROLECONTROL='$module' and LOAN_ROLEACTION='$action' and LOAN_PARAM='$param'")->find();
				//echo M('erp_role')->getLastSql();
				if(is_array($record)){
					js_show('info',1,'囧，模块和方法重名！');
					exit();
				}

				$roleid = $this->_request('roleid');
				$ss['LOAN_ROLENAME'] = $this->_request('rolename','');
				$ss['LOAN_ROLECONTROL'] = $module;
				$ss['LOAN_ROLEACTION'] = $action;
				$ss['LOAN_ROLEORDER'] = $this->_request('rolesort');
				$ss['LOAN_CREATED'] = $ss['LOAN_UPDATED'] = time();
				$ss['LOAN_MENUSHOW'] = $this->_request('roledisplay');
				$ss['LOAN_PARAM'] = $this->_request('roleparam');
				if(empty($roleid))
					$ss['LOAN_ROLEPARENTID'] = 0;
				else
					$ss['LOAN_ROLEPARENTID'] = $roleid;
				$d = D('Erp_role');//new erp_roleModel();
				$d->add($ss);
				
				js_show('info','','恭喜添加成功哦！');
				exit();
			}elseif($act=='del'){
				if($password!=='cxxpyw'){
					js_show('info',1,'囧，密码错误哦！');
					exit();
				}
				$ss['LOAN_ROLEDISPLAY'] = 1;
				D('Erp_role')->where("LOAN_ROLEID='$roleid'")->save($ss);
				js_show('info','','恭喜删除成功哦！');
				exit();
			}
			/**********以上为iframe操作*********/
			$record = M('Erp_role')->where("LOAN_ROLEDISPLAY=0 and LOAN_ROLEPARENTID=0")->order('LOAN_ROLEORDER asc')->select();//获取主菜单栏目
			//echo M('erp_role')->getLastSql();
			if(is_array($record)){
				foreach($record as $key=>$fmenu){
					$fid = $fmenu['LOAN_ROLEID'];
					$menu[$key]['fmenu'] = $fmenu;//主菜单啊
					$sval = M('Erp_role')->where("LOAN_ROLEDISPLAY=0 and LOAN_ROLEPARENTID='$fid'")->order('LOAN_ROLEORDER asc')->select();
					if(is_array($sval)){
						foreach($sval as $key2=>$smenu){
							$menu[$key]['smenu'][$key2] = $smenu;
							$sid = $smenu['LOAN_ROLEID'];
							$pval = M('Erp_role')->where("LOAN_ROLEDISPLAY=0 and LOAN_ROLEPARENTID='$sid'")->order('LOAN_ROLEORDER asc')->select();
							if(is_array($pval)){
								foreach($pval as $key3=>$pulate){
									$menu[$key]['smenu'][$key2]['loan_pulate'][$key3] = $pulate;	
									$lid = $pulate['LOAN_ROLEID'];
									$lval = M('Erp_role')->where("LOAN_ROLEDISPLAY=0 and LOAN_ROLEPARENTID='$lid'")->order('LOAN_ROLEORDER desc')->select();
									if(is_array($lval)){
										foreach($lval as $key4=>$lmenu ){
											$menu[$key]['smenu'][$key2]['loan_pulate'][$key3]['loan_lrole'][$key4] = $lmenu;	


										}
									}

								}
							}
						}
					}
				}
			}
			$this->assign('menu',$menu);
			$this->assign('tact',$_REQUEST['tact']);
			$this->display('menu');
		}

        public function searchByName() {
            $groupID = $_REQUEST['LOAN_GROUPID'];
            if (intval($groupID) <= 0) {
                $this->redirect('viewGroup');
                return;
            }

            // 获取所有的权限组供搜索时用
            $allGroup = M('Erp_group')->field('LOAN_GROUPID, LOAN_GROUPNAME')->where("LOAN_GROUPDEL=0")->order('LOAN_GROUPCREATED desc')->select();
            $group = $this->getGroup(" AND LOAN_GROUPID = {$groupID} ");
            $this->assign('re',$group['val']);
            $this->assign('page',$group['page']);
            $this->assign('all_group', $allGroup);
            $this->display('group');
        }
	}
?>