<?php
	class ShowAction extends ExtendAction{
		
		function viewForm(){
//			Vendor('Oms.Form');
			$form = D("Form");
			// var_dump($form->testdb());
			 
			//var_dump($form->initForminfo(1)->getFormHtml() );
			//var_dump($form->initForminfo(2)->mvarCols);
                           
			$where = '1=1';$para = '';
			$searchtype = $this->_request('seartchtype');
			
			$keyword    = $this->_request('keyword');
			$keyword = trim($keyword);
			if($searchtype && $keyword){
				switch($searchtype){
					case 1: $where.= " and FORMTITLE like '%$keyword%'"; break;
					case 2: $where.= " and FORMTYPE like '%$keyword%'"; break;
				}
				$para.= 'seartchtype='.urlencode($searchtype).'&';
				$para.= 'keyword='.urlencode($keyword).'&';
				$this->assign('con',array('searchtype'=>$searchtype,'keyword'=>$keyword));
			}
			$count = M('form')->where($where)->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('form')->where($where)->order('FORMNO desc')->limit($p->firstRow.','.$p->listRows)->select();    
                            
			$this->assign('page',$page);
			$this->assign('re',$re);
			$this->assign('depart',$depart);
			$this->display('form');
		}
	
		function addForm(){
			$data = $_POST;
			$act = $this->_post('act');
			if($act=='add'){
				$insert =array();
				foreach($data as $key=>$vo){
					if($key !== '__hash__' || $key !=='act' || $key !=='refurl'){
						$insert[$key] = $vo;
					}
				}
				$affected = D('Form')->add($insert); 

				if($affected){
					js_alert('表单添加成功！',U('Show/viewForm'),$sty=1);exit();//用户组的编辑*/
				}else{
					js_alert('表单添加失败！');exit();
				}
			}

			$this->assign('action',U('Show/addForm'));
			$this->assign('todo','add');
			$this->assign('menutitle','表单添加');
		 
			$this->display('addForm');			
		}

		function editForm(){
			 
			$todo = $this->_get('todo');
			$act = $this->_post('act');
			if($todo=='edit'){
			    $id = $this->_get('id');
				$forminfo = M('form')->where("FORMNO='$id'")->find();  
				$this->assign('forminfo',$forminfo);
				$this->assign('todo',$todo);
				$this->assign('refurl',base64_encode($_SERVER['HTTP_REFERER']));

			}
			if($act=='edit'){
				$refurl = $this->_post('refurl');
				if($refurl){
					$refurl = base64_decode($refurl);
				}else{
					$refurl = '';
				}
				
				$id = $this->_post('FORMNO');
				$update = $_POST;
                  unset($update['act']);  
                  unset($update['__hash__']);  
                  unset($update['refurl']);  

				$affected = D('Form')->where("FORMNO='$id'")->save($update);  
				if($affected){
					js_alert('表单修改成功！',$refurl,$sty=1);exit();//用户组的编辑*/
				}else{
					js_alert('表单修改失败！');exit();
				}				
			}
			$this->assign('action',U('Show/editForm'));
			$this->assign('menutitle','表单修改');
			 
			$this->display('addForm');
		}
    
        public function delForm() {
            $FORMNO = $this->_get('FORMNO');
            $FieldList = D('formlist')->where("FORMNO={$FORMNO}")->select();
            if ($FieldList) {
                js_alert('请先删除字段');exit;
            } else {
                $delete = D('form')->where("FORMNO={$FORMNO}")->delete();
                if ($delete) {
                   $this->success('表单删除成功！',U('Show/viewForm'));exit;
                } else {
                    $this->error('表单删除失败！');exit;
                }
            }
        }


		function fieldList(){
			$para = '';
             $FORMNO = $this->_get('FORMNO'); 
			$where = "FORMNO = {$FORMNO}";
            
			$count = M('formlist')->where($where)->count();
			import("ORG.Util.Page");
			$p = new Page($count,C('PAGESIZE'));
			if($para) $p->parameter = $para;
			$page = $p->show();	
			$re = M('formlist')->where($where)->limit($p->firstRow.','.$p->listRows)->select();
			
			$this->assign('page',$page);
			$this->assign('re',$re);
			$this->assign('FORMNO',$FORMNO);
			$this->assign('formtitle',$_REQUEST['formtitle']);
            
			$this->display('formlist');

		}
		function addAutoFormlist(){
			$FORMNO = $this->_get('FORMNO');
			$list =  D('Formlist')->where("FORMNO = {$FORMNO}  ")->select();
			if($list){
				//js_alert('该表单已经存在字段 不允许自动添加',U('Show/fieldList'),$sty=1);exit();//用户组的编辑*/
				$this->error('该表单已经存在字段 不允许自动添加');exit();
			}else{
				$form = D('Form')->where("FORMNO = {$FORMNO} ")->find();
				$model = new Model();
				$sql ="SELECT * FROM USER_TAB_COLUMNS WHERE TABLE_NAME='".$form['SQLTEXT']."'";
				$data = $model->query($sql);
				foreach($data as $k=>$v){
					if($form['PKFIELD']!=$v['COLUMN_NAME']){
						$info['FORMNO'] = $FORMNO;
						$info['FIELDNAME'] = $v['COLUMN_NAME'];
						$info['FIELDMEANS'] = $v['COLUMN_NAME'];
						$info['LINENO'] = $k+1;
						$info['COLNO'] = 1;
						$info['NOTNULL']=-1;
						$info['FIELDLENGTH'] = $v['DATA_LENGTH'];
						$info['EDITMAXLENGTH'] = $v['DATA_LENGTH'];
						switch($v['DATA_TYPE']){
							case 'DATE':
								$info['FIELDTYPE'] = 3;
								$info['EDITTYPE'] = 13;
								break;
							case 'NUMBER':
								$info['FIELDTYPE'] = 2;
								$info['EDITTYPE'] = 12;
								break;
							default:
								break;
						}
						D('Formlist')->add($info);
					}
				}
				$this->success('自动添加字段成功'); 
			}

		}
		function addFormlist(){
            $FORMNO = $this->_get('FORMNO');
			$act = $this->_post('act');
			
			if($act=='add'){
                  $insert['FORMNO'] = $FORMNO;
				$insert['FIELDNAME'] = $this->_post('FIELDNAME');
				$insert['FIELDMEANS'] = $this->_post('FIELDMEANS');
				$insert['LINENO'] = $this->_post('LINENO');
				$insert['COLNO'] = $this->_post('COLNO');
				$insert['READONLY'] = $this->_post('READONLY');
				$insert['NOTNULL'] = $this->_post('NOTNULL');
				$insert['GRIDVISIBLE'] = $this->_post('GRIDVISIBLE');
				$insert['FORMVISIBLE'] = $this->_post('FORMVISIBLE');
				$insert['SORT'] = $this->_post('SORT');
				$insert['FILTER'] = $this->_post('FILTER');
				$insert['FIELDLENGTH'] = $this->_post('FIELDLENGTH')?$this->_post('FIELDLENGTH'):0;
				$insert['EDITLENGTH'] = $this->_post('EDITLENGTH')?$this->_post('EDITLENGTH'):0;
				$insert['EDITMAXLENGTH'] = $this->_post('EDITMAXLENGTH')?$this->_post('EDITMAXLENGTH'):0;
				$insert['DEFAULTVALUE'] = $this->_post('DEFAULTVALUE');
				$insert['EDITTYPE'] = $this->_post('EDITTYPE');
				$insert['LISTSQL'] = $this->_post('LISTSQL');
				$insert['DECLENGTH'] = $this->_post('DECLENGTH');
				$insert['ALIGN'] = $this->_post('ALIGN');
				$insert['EDITFORMAT'] = $this->_post('EDITFORMAT');
				$insert['LISTCHAR'] = $this->_post('LISTCHAR');
				$insert['PARENTCOL'] = $this->_post('PARENTCOL');
				$insert['HELPTEXT'] = $this->_post('HELPTEXT');
				$insert['TRANSFER'] = $this->_post('TRANSFER');
				$insert['GRIDTD'] = $this->_post('GRIDTD');
				$insert['INPUTPROPERTY'] = $this->_post('INPUTPROPERTY');
				$insert['DBBOUND'] = $this->_post('DBBOUND');
				$insert['CHKADD'] = $this->_post('CHKADD');
				$insert['CHKADDERRMSG'] = $this->_post('CHKADDERRMSG');
				$insert['UNIT'] = $this->_post('UNIT');
				$insert['VIRTUAL'] = $this->_post('VIRTUAL');
                  $unique = D('Formlist')->where("FORMNO = {$FORMNO} AND FIELDNAME = '{$this->_post('FIELDNAME')}'")->find();
                 
                  if ($unique) {
                      js_alert('字段名称已经存在！');exit;
                  }
                    
				$affected = M('Formlist')->add($insert);  
                  
				if($affected){
					js_alert('字段添加成功！',U('Show/fieldList?FORMNO='.$FORMNO),$sty=1);exit();// 
				}else{
					js_alert('字段添加失败！');exit();
				}
			}
                           
            $this->assign('FORMNO',$FORMNO);
			$this->assign('action',U('Show/addFormlist?FORMNO='.$FORMNO));
			$this->assign('todo','add');
			$this->assign('menutitle','字段添加');
			$this->assign('formtitle',$_REQUEST['formtitle']);
		 
			$this->display('addFormlist');			
		}
		function preView(){
			Vendor('Oms.Form');
			$form = new Form();
			// var_dump($form->testdb());
			$FORMNO = $this->_get('FORMNO') ;
			$form =  $form->initForminfo($FORMNO)->getResult();
			//var_dump($form->initForminfo(2)->mvarCols);
			$this->assign('form',$form);
			$this->assign('FORMNO',$FORMNO);
			$this->display('caseadd');
			 
		}

		function editFormlist(){
                           
			$FORMNO = $this->_get('FORMNO');
                           
			$todo = $this->_get('todo');
			$act = $this->_post('act');
			if($todo=='edit'){
				$FIELDNAME = trim($this->_get('FIELDNAME'));
				$forminfo = M('formlist')->where("FIELDNAME='$FIELDNAME' and FORMNO={$FORMNO}")->find();  
				
                $this->assign('FORMNO',$FORMNO);    
				$this->assign('forminfo',$forminfo);
				$this->assign('todo',$todo);
				$this->assign('formtitle',$_REQUEST['formtitle']);
				$this->assign('refurl',base64_encode($_SERVER['HTTP_REFERER']));

			}
			if($act=='edit'){
                                
				$refurl = $this->_post('refurl');
				if($refurl){
					$refurl = base64_decode($refurl);
				}else{
					$refurl = '';
				}
				 
				$update = $_POST;
                  unset($update['act']);
                  unset($update['__hash__']);
                  unset($update['refurl']);
				$update['FORMNO'] = $FORMNO;
				$FIELDNAME = $this->_post('FIELDNAME');
				
				$affected = D('Formlist')->where("FIELDNAME='$FIELDNAME' and FORMNO={$FORMNO}")->save($update);   
				
				if($affected){
					js_alert('字段修改成功！',$refurl,$sty=1);exit(); 
				}else{
					js_alert('字段修改失败！');exit();
				}				
			}
            
              $this->assign('action',U('Show/editFormlist',array('FORMNO'=>$FORMNO)));
			$this->assign('menutitle','字段修改');
			$this->assign('group',$group);
			$this->assign('city',$city);
			 
			$this->display('addFormlist');
		}
    
        public function delFormlist()
        {
            $FORMNO = $this->_get('FORMNO');
            $where = "FIELDNAME = '{$this->_get('FIELDNAME')}' AND FORMNO= {$FORMNO}";
            $del = D('Formlist')->where($where)->delete();
            if ($del) {
                $this->success('删除成功！');exit;
            } else {
                $this->error('删除失败！');exit;
            }
        }    

		function lockUser(){
			$act = $this->_get('act');
			if($act=='lock'){
				$id = $this->_get('id');
				$ss['LOAN_LOCK'] = $this->_get('val');
				M('admin_user')->where("LOAN_USERID='$id'")->save($ss);
				if($ss['LOAN_LOCK']){
					$info = '用户锁定成功！';
				}else{
					$info = '用户解锁成功！';
				}
				$this->success($info,U('User/viewUser'));exit();
			}
		}

		function userinfo(){
			$act = $this->_post('act');
			$uid = $_SESSION['uinfo']['uid'];
			$userinfo = M('admin_user')->where("LOAN_USERID='$uid'")->find();

			if($act=='edit'){
				$user = array();
				$user['LOAN_TRUENAME'] = $this->_post('username');
				$user['LOAN_MOBILE'] = $this->_post('mo');
				$user['LOAN_QQ'] = $this->_post('qq');
				$user['LOAN_EMAIL'] = $this->_post('email');
				$affected = D('Admin_user')->where("LOAN_USERID='$uid'")->save($user);
				if($affected===false){
					$this->error('个人信息修改失败',U('User/userinfo'));exit();		
				}else{
					$this->success('个人信息修改成功！',U('User/userinfo'));exit();
				}
			}

			if($act=='pwd'){
				$pwd = trim($this->_post('oldpwd'));
				$newpwd = $this->_post('newpwd');
				if(md5($pwd)!=$userinfo['LOAN_USERPWD']){
					$this->error('旧密码输入不正确！',U('User/userinfo'));exit();
				}
				if($newpwd){
					$user = array();
					$user['LOAN_USERPWD'] = md5($newpwd);
					$affected = D('Admin_user')->where("LOAN_USERID='$uid'")->save($user);
					if($affected===false){
						$this->error('个人密码修改失败',U('User/userinfo'));exit();		
					}else{
						$this->success('个人密码修改成功！',U('User/userinfo'));exit();
					}
				}

			}
			$userinfo = M('admin_user')->where("LOAN_USERID='$uid'")->find();
			$this->assign('depart',C('department_aray'));
			$this->assign('userinfo',$userinfo);
			$this->display('userinfo');
		}		
	}
?>