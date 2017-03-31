<?php
	class FormSetAction extends ExtendAction{
		
		 
		 function FormList(){// 
			 
			Vendor('Oms.Form');
			$form = new Form();
			$form->CZBTN ='<a class="contrtable-link" onclick="editForm(this);" href="javascript:;">修改</a>
			|<a class="contrtable-link" onclick="FieldList(this);" href="javascript:;">字段</a>|<a class="contrtable-link" onclick="ViewForm(this);" href="javascript:;"> 预览</a>';
			$form->FORMFORWARD = $_REQUEST['fromurl']; 
			$formhtml =  $form->initForminfo(53)->getResult();//echo $form->getFilter();
			$this->assign('form',$formhtml);
			$this->display('Form');
		 }
		  function Field(){// 
			Vendor('Oms.Form');
			 
			$form = new Form();
			$form->FORMFORWARD = $_REQUEST['fromurl'];
			$form->initForminfo(62);
			$form->setMyFieldVal('FORMNO',$_REQUEST['FORMID'],true);
			$form->where("FORMNO='".$_REQUEST['FORMID']."'");
			$formhtml = $form->getResult();
			$this->assign('form',$formhtml);
			$this->assign('FORMID',$_REQUEST['FORMID']);
			$this->display('Field');
		 }
		 function ViewForm(){// 
			Vendor('Oms.Form');
			$FORMNO = $_REQUEST['FORMNO']; 
			$form = new Form();
			$form =  $form->initForminfo($FORMNO )->getResult();
			$this->assign('form',$form);
			$this->display('Form');
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
					$info = array();
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


	}
 