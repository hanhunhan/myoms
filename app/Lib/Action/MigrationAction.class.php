<?php
	//����˳�� user_data  project_data member_limit_data
	import('Org.Io.Mylog');

	class MigrationAction extends Action{
		public $dbstr="mysql://root:111111@localhost:3306/tlferp";
		
		//public $mysqldsn = 'mysql:dbname=onethink;host=localhost;port=3306';
		public $dbstr2="oracle://oms:oms@lorcl:1521";
		public $citys = '1';
		public $model;
		public $mysqlmodel;
		public function __construct(){
            parent::__construct();
			$this->model = M();
			$this->mysqlmodel = new Model();
			$this->omsomdel2 = new Model();
			$this->mysqlmodel->db(12,$this->dbstr);
			$this->omsomdel2->db(15,$this->dbstr2);
			
 
		}
		public function testt(){
			//$projectlist = $this->mysqlmodel->query('select * from erp_project where city in ('.$this->citys.') and state<10 and old_id=0');
			 M('Erp_project');
			echo 'ok';
		}

		public function project_data(){
			
			$projectlist = $this->mysqlmodel->query('select * from erp_project where city in ('.$this->citys.') and state<10 and old_id=0     ');
			Vendor('Oms.Changerecord');	 
			$migration = D('Migration');//���뷽��model
			foreach($projectlist as $v){
				//�ɼ����ж��Ƿ��Ѿ����� ??
				//$v['submit_userid'] = 'chuzhouadmin';
				 
				Mylog::write( "��Ŀ(".$v['id'].") ��ʼ����>>");
				if( M('Erp_project')->where("STATUS<>2 AND TLF_PROJECT_ID=".$v['id'])->find() ){
					Mylog::write( "��Ŀ(".$v['id'].") �Ѵ��ڣ������е��������");
					continue;
				}
				
				$this->model->startTrans();
				$pdata = $cdata = $bdata = $hdata = array();
				$pdata['CONTRACT'] = $v['contract_num'];
				$pdata['CITY_ID'] = $v['city'];
				$pdata['PROJECTNAME'] = $v['pro_name'];
				$pdata['CUSER'] = $migration->get_users_id($v['submit_userid']);//������id
				$pdata['ETIME'] = date('Y-m-d H:i:s',$v['exec_sdate']);
				$pdata['PSTATUS'] = $migration->get_project_status($v['state'],$v['exec_sdate'],$v['exec_edate']);//״̬
				$pdata['BSTATUS'] = $migration->get_project_bstatus($v['state'],$v['exec_sdate'],$v['exec_edate']);// ����״̬
				$pdata['COMPANY'] = $v['dev_ent'];
				$pdata['TLF_PROJECT_ID'] = $v['id'];
				$pdata['STATUS'] = -1;
				
				$pres = M('Erp_project')->add($pdata);//������Ŀid
				if($pres){
					 
					Mylog::write(' Erp_project���¼����ɹ�,ID��'.$pres.'ԭID:'.$pdata['TLF_PROJECT_ID']);
					 
				}else{
					$this->model->rollback(); 
					Mylog::write(' Erp_project���¼����ʧ��! ԭID:'.$pdata['TLF_PROJECT_ID'],'error');
					continue;
				}
				
				$cdata['SCALETYPE'] = 1;
				$cdata['CTIME'] = date('Y-m-d H:i:s',$v['submit_date']);
				$cdata['CUSER'] = $pdata['CUSER'];
				$cdata['PROJECT_ID'] = $pres;//������Ŀid
				$cdata['FSTATUS'] = $pdata['BSTATUS'];// ״̬
				$cres = M('Erp_case')->add($cdata);//����ҵ��id
				if($cres){
					 
					Mylog::write( ' Erp_case �������¼����ɹ�,ID��'.$cres);
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_case ������ ��¼����ʧ��! ','error');
					continue;
				}
				//�������  ��ֹ ��¼
				if($pdata['BSTATUS']==3 || $pdata['BSTATUS']==5){
					$temp = array();
					$temp['PROJECT'] = $pres;
					$temp['CITY'] = $pdata['CITY_ID'];
					$temp['CONTRACT_NUM'] =  $v['contract_num'];
					$temp['BTYPE'] = 1;
					$temp['CASE_ID'] = $cres;

					$temp['APPLICANT'] = $pdata['CUSER'];
					$temp['APPDATE'] = date('Y-m-d H:i:s');  
					$temp['TYPE'] = $pdata['BSTATUS']==3 ? 1:2;
					//$temp['ZJTIME'] = $pdata['BSTATUS']==3 ? null:2;
					$temp['STATUS'] = 2;//ͨ����
					$flcres = M('Erp_finalaccounts')->add($temp);
					if($flcres){
					 
						Mylog::write( 'Erp_finalaccounts ��Ŀ������ֹ��¼����ɹ�,ID��'.$flcres);
					}else{
						$this->model->rollback(); 
						 
						Mylog::write('Erp_finalaccounts ��Ŀ������ֹ��¼����ʧ��! ','error');
						continue;
					}
				}
				//������Ŀ��¼��
				$temp = array();
				$temp['PROJECT_ID'] = $pres;//������Ŀid
				$temp['USER_ID'] = $pdata['CUSER'];
				$temp['CTIME'] = date('Y-m-d H:i:s',$v['exec_sdate']);//��ʼʱ��
				$project_log_id = M('Erp_project_log')->add($temp);
				if($project_log_id){
					 
					Mylog::write( 'Erp_project_log ��Ŀlog��¼����ɹ�,ID��'.$project_log_id);
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_project_log ��Ŀlog��¼����ʧ��! ','error');
					continue;
				}
				//������ĿȨ�ޱ�
				$propower_list = $this->mysqlmodel->query('select distinct userid from erp_propower where  pid='.$v['id'].' and state=1 ');
				$prorole_res = false;
				$power_user = array();
				foreach($propower_list as $prone){
					$power_user[] = $prone['userid'];
				}
				if(!in_array($v['submit_userid'],$power_user) ){
					$power_user[] = $v['submit_userid'];
				}
				foreach($power_user as $prone){
					$temp = array();
					$temp['USE_ID'] = $migration->get_users_id($prone );//��Ȩ�޵���
					$temp['PRO_ID'] = $pres;//������Ŀid
					$temp['ISVALID'] = -1;
					$temp['ERP_ID'] = 1;
					$prorole_res = M('Erp_prorole')->add($temp);
				}
				if($prorole_res){
					 
					Mylog::write( 'Erp_prorole ��ĿȨ�ޱ��¼����ɹ� ' );
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_prorole ��ĿȨ�ޱ��¼����ʧ��! ','error');
					continue;
				} 
				//
			 
				$bdata = $this->prjbudget_arr($v,$cres);// Ԥ������Ϣ

				$bres = M('Erp_prjbudget')->add($bdata);//����Ԥ��id
				if($bres){
					 
					Mylog::write(' Erp_prjbudget Ԥ����¼����ɹ�,ID��'.$bres);
					 
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_prjbudget Ԥ��� ��¼����ʧ��! ','error');
					continue;
				}

				$v['CUSER'] = $migration->get_users_id($v['submit_userid'] ); 
				$hdata = $this->house_arr($v,$pres);//house����Ϣ
				$hres = M('Erp_house')->add($hdata);//����¥�̼�¼id
				if($hres){
					 
					Mylog::write(' Erp_house ¥�̱��¼����ɹ�,ID��'.$hres); 
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_house ¥�̼�¼����ʧ��! ','error');
					continue;
				}
				/*if($v['tag_products'] ){ //��� ������Ʒ
					$tagparr = explode(',',$v['tag_products']);
					$rpres = true;
					$tagp_arr = array();
					$list = M('Erp_products_type')->select();
					foreach($list as $key =>$value){
						$temp = array();
						$temp['ISVAILD'] = in_array($value['ID'],$tagparr) ? '-1' : '0';
						$temp['HOUSE_ID'] = $hres;
						$temp['CHANGPINID'] = $value['ID'];
						$rpres = M('Erp_relatedproducts')->add($temp); //���ع�����Ʒ��ϵid
						$tagp_arr[$value['ID']] = $rpres;
						if(!$rpres) break;
					}
					if($rpres){
							 
							Mylog::write(' Erp_relatedproducts ������Ʒ���¼����ɹ� '); 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_relatedproducts ������Ʒ��¼����ʧ��! ','error');
							continue;
					}
				}*/
				if($v['sales_target']){
					$sales_target = unserialize($v['sales_target']);
					
					$bgres = true;
					$bgres_arr = array();
					foreach($sales_target as $key=>$one){
						 
						$temp = $this->sales_target_arr($one,$bres,$key,$pres);
						$bgres = M('Erp_budgetsale')->add($temp);//����Ŀ��ֽ�id
						$bgres_arr[$key] = $bgres;
						if(!$bgres) break;
					}
					if($bgres){
							 
							Mylog::write(' Erp_budgetsale Ŀ��ֽ� ��¼����ɹ� '); 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_budgetsale Ŀ��ֽ��¼����ʧ��! ','error');
							continue;
					}

				}
				if($v['offline_cost']){
					$offline_cost = unserialize($v['offline_cost']);
					
					$list = M('Erp_fee')->where('INPUTNAME is not null')->select();
					$bgfres = true;
					$feearr = array();
					foreach($list as $key=>$one){
						$temp=array();
						if($offline_cost[$one['INPUTNAME']] ){
							 
							$temp = $this->budgetfee_arr($one,$bres,$v,$offline_cost);
							$bgfres = M('Erp_budgetfee')->add($temp);//�������·���id
							$offline_fee_arr[$key] =  $bgfres; 
							if(!$bgfres) break;
						}
					}
					if($bgfres){
							 
							Mylog::write(' Erp_budgetfee ���·��� ��¼����ɹ� '); 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write( ' Erp_budgetfee ���·��ü�¼����ʧ��! ','error');
							//var_dump($offline_cost);var_dump($temp);var_dump($bgfres);
							continue;
					}

				}
				if($v['online_cost']){
					$online_cost = unserialize($v['online_cost']);
					
					$list = M('Erp_fee')->where('INPUTNAME is not null and ISONLINE=-1 ')->select();
					$bgfres = true;
					$online_fee_arr = array();
					foreach($list as $key=>$one){
						$temp = array();
						$one['INPUTNAME'] = $one['INPUTNAME']=='cost_online_ad'?'ad_budget':$one['INPUTNAME'];
						$one['INPUTNAME'] = $one['INPUTNAME']=='cost_online_had'?'ad_index_budget':$one['INPUTNAME'];
						if($online_cost[$one['INPUTNAME']] ){
							 
							$temp = $this->budgetfee_arr($one,$bres,$v,$online_cost);
							$bgfres = M('Erp_budgetfee')->add($temp);//�������·���id
							$online_fee_arr[$key] = $bgfres;
							if(!$bgfres) break;
						}
					}
					if($bgfres){
							 
							Mylog::write(' Erp_budgetfee ���Ϸ��� ��¼����ɹ� '); 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_budgetfee ���Ϸ��ü�¼����ʧ��! ','error');
							continue;
					}
				}
				if($v['house_price']){
					$house_price = explode(',',$v['house_price']);
					
					$fsres = true;
					foreach($house_price as $one){
						$temp = array();
						$temp['PRJ_ID'] = $bres;//Ԥ��ID
						$temp['SCALETYPE'] = 1;
						$temp['AMOUNT'] = $one;
						$temp['SCALE'] = $one;//�Ƿ�
						$temp['CASE_ID'] = $cres; //����id
						$temp['ISVALID'] = -1; // 
						$temp['PAYDATE'] = date('Y-m-d H:i:s',$v['submit_date']);
						$fsres = M('Erp_feescale')->add($temp);
						if(!$fsres) break;

					}
					if($fsres){
							 
							Mylog::write(' Erp_feescale ���ñ�׼ ��¼����ɹ�'); 
					}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_feescale ���ñ�׼ ��¼����ʧ��! ','error');
							continue;
					}


				}
				if($v['state']==1){
					$this->project_flow_data($v,$pres,$cres,$migration);//������������

				}
				//$changelist = $this->mysqlmodel->query("select * from erp_project where old_id='".$v['id']."'  and state=1 ");//�����¼
				if($changelist){
					Mylog::write( "���°汾����У�");
					$flow_first = $this->getFlowStep($changelist[0]['id']);//��һ��
					$flow_last = $this->getFlowStep($changelist[0]['id'],'DESC');//��ǰ
					$changevid = $this->createChangeRecordVersion($pres,date('Y-m-d H:i:s',$flow_first['deal_time']),$migration->get_users_id($flow_first['deal_userid']),$migration->get_users_id($flow_last['deal_userid']),1,1);
					if($changevid){
						$bdata_change = $this->prjbudget_arr($changelist[0],$cres);// Ԥ������Ϣ
						$changer = new Changerecord();
						$changer->fields=$this->getAllCols($bdata);
						$bdata = $this->addChangeversion($bdata,$bdata_change);
						$optt['TABLE'] = 'ERP_PRJBUDGET';
						$optt['BID'] = $bres ;//Ԥ���ǰ��¼id  
						$optt['CID'] = $changevid ;//����汾id
						$optt['CDATE'] = date('Y-m-d H:i:s');
						$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
						$optt['ISNEW'] = 0;//����-1 �� �޸�0
						$changeres =  $changer->saveRecords($optt,$bdata);
						if($changeres )
							Mylog::write( 'ERP_PRJBUDGET�����¼����ɹ� ' );
						else {
							$this->model->rollback(); 
							Mylog::write(' ERP_PRJBUDGET�����¼����ʧ��!  ','error');
							continue;
						}

						$hdata_change = $this->house_arr($changelist[0],$pres);// house�����Ϣ
						$changer->fields=$this->getAllCols($hdata);
						$hdata = $this->addChangeversion($hdata,$hdata_change);
						$optt['TABLE'] = 'ERP_HOUSE';
						$optt['BID'] = $hres ;//house��ǰ��¼id  
						$optt['CID'] = $changevid ;//����汾id
						$optt['CDATE'] = date('Y-m-d H:i:s');
						$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
						$optt['ISNEW'] = 0;//����-1 �� �޸�0
						$changeres =  $changer->saveRecords($optt,$hdata);
						if($changeres )
							Mylog::write( 'ERP_HOUSE�����¼����ɹ� ' );
						else {
							$this->model->rollback(); 
							Mylog::write(' ERP_HOUSE�����¼����ʧ��!  ','error');
							continue;
						}

						if($changelist[0]['sales_target']){
							$sales_target_change = unserialize($changelist[0]['sales_target']);
							
							$bgres = true;
							foreach($sales_target_change as $key=>$one){
								$temp = $temp_change = array();
								$temp = $this->sales_target_arr($sales_target[$key],$bres,$key,$pres); 
								$temp_change = $this->sales_target_arr($one,$bres,$key,$pres);
								$changer->fields=$this->getAllCols($temp);
								$temp = $this->addChangeversion($temp,$temp_change);
								$optt['TABLE'] = 'ERP_BUDGETSALE';
								$optt['BID'] = $bgres_arr[$key] ;//ERP_BUDGETSALE��ǰ��¼id  
								$optt['CID'] = $changevid ;//����汾id
								$optt['CDATE'] = date('Y-m-d H:i:s');
								$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
								$optt['ISNEW'] = 0;//����-1 �� �޸�0
								$bgres =  $changer->saveRecords($optt,$temp);
								if($bgres )
									Mylog::write( 'ERP_BUDGETSALE �����¼����ɹ� key:'.$key );
								else {
									$this->model->rollback(); 
									Mylog::write(' ERP_BUDGETSALE�����¼����ʧ��!  ','error');
									break;
								} 
							}
							if(!$bgres )continue;
							 

						}

						if($v['offline_cost']){
							$offline_cost = unserialize($v['offline_cost']);
							$offline_cost_change = unserialize($changelist[0]['offline_cost']);
							
							$list = M('Erp_fee')->where('INPUTNAME is not null')->select();
							$bgfres = true;
							foreach($list as $key=>$one){
								$temp=$temp_change=array();
								if($offline_cost[$one['INPUTNAME']] ){
									 
									$temp = $this->budgetfee_arr($one,$bres,$v,$offline_cost);
									$temp_change = $this->budgetfee_arr($one,$bres,$changelist[0],$offline_cost_change);
									$changer->fields=$this->getAllCols($temp);
									$temp = $this->addChangeversion($temp,$temp_change);
									$optt['TABLE'] = 'ERP_BUDGETFEE';
									$optt['BID'] = $offline_fee_arr[$key] ;//ERP_BUDGETSALE��ǰ��¼id  
									$optt['CID'] = $changevid ;//����汾id
									$optt['CDATE'] = date('Y-m-d H:i:s');
									$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
									$optt['ISNEW'] = 0;//����-1 �� �޸�0
									$bgfres =  $changer->saveRecords($optt,$temp);
									if($bgfres )
										Mylog::write( 'ERP_BUDGETFEE  offline_cost �����¼����ɹ� key:'.$key );
									else {
										$this->model->rollback(); 
										Mylog::write(' ERP_BUDGETFEE offline_cost �����¼����ʧ��!  ','error');
										break;
									} 
								}
							}
							if(!$bgres )continue;
							 

						}
						if($v['online_cost']){
							$online_cost = unserialize($v['online_cost']);
							$online_cost_change = unserialize($changelist[0]['online_cost']);
							
							$list = M('Erp_fee')->where('INPUTNAME is not null')->select();
							$bgfres = true;
							foreach($list as $key=>$one){
								$temp=$temp_change=array();
								if($online_cost[$one['INPUTNAME']] ){
									 
									$temp = $this->budgetfee_arr($one,$bres,$v,$online_cost);
									$temp_change = $this->budgetfee_arr($one,$bres,$changelist[0],$online_cost_change);
									$changer->fields=$this->getAllCols($temp);
									$temp = $this->addChangeversion($temp,$temp_change);
									$optt['TABLE'] = 'ERP_BUDGETFEE';
									$optt['BID'] = $online_fee_arr[$key] ;//ERP_BUDGETSALE��ǰ��¼id  
									$optt['CID'] = $changevid ;//����汾id
									$optt['CDATE'] = date('Y-m-d H:i:s');
									$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
									$optt['ISNEW'] = 0;//����-1 �� �޸�0
									$bgfres =  $changer->saveRecords($optt,$temp);
									if($bgfres )
										Mylog::write( 'ERP_BUDGETFEE online_cost �����¼����ɹ� key:'.$key );
									else {
										$this->model->rollback(); 
										Mylog::write(' ERP_BUDGETFEE online_cost �����¼����ʧ��!  ','error');
										break;
									} 
								}
							}
							if(!$bgres )continue; 

						}

						/*if($v['tag_products'] ){ //��� ������Ʒ
							$tagparr = explode(',',$v['tag_products']);
							$tagparr_change = explode(',',$changelist[0]['tag_products']);
							$rpres = true;
							$list = M('Erp_products_type')->select();
							foreach($list as $key =>$value){
									$temp = $temp_change = array();
									$temp['HOUSE_ID'] = $hres;
									$temp['ISVAILD'] = in_array($value['ID'],$tagparr)?-1:0;
									$temp['CHANGPINID'] = $value['ID'];
							
									$temp_change['HOUSE_ID'] = $hres;
									$temp_change['ISVAILD'] = in_array($value['ID'],$tagparr_change)?-1:0;
									$temp_change['CHANGPINID'] = $value['ID']; 
									 
									$changer->fields=$this->getAllCols($temp);
									$temp = $this->addChangeversion($temp,$temp_change);
									$optt['TABLE'] = 'ERP_RELATEDPRODUCTS';
									$optt['BID'] = $tagp_arr[$value['ID']] ;//ERP_BUDGETSALE��ǰ��¼id  
									$optt['CID'] = $changevid ;//����汾id
									$optt['CDATE'] = date('Y-m-d H:i:s');
									$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
									$optt['ISNEW'] = 0;//����-1 �� �޸�0
									$bgfres =  $changer->saveRecords($optt,$temp);
									if($bgfres )
										Mylog::write('Erp_relatedproducts �����¼����ɹ� key:'.$key );
									else {
										$this->model->rollback(); 
										Mylog::write('Erp_relatedproducts �����¼����ʧ��! ','error');
										break;
									} 
									 
									
							}
							 
							 
						}*/

						//$this->project_flow_data($changelist[0],$pres,$cres,$migration,7);//����������



					}else{
						$this->model->rollback(); 
						Mylog::write(' �汾�Ų���ʧ��!  ','error');
						continue;
					}
					
				}
				$reimdata = $this->reimbursement_data($pres,$cres,$migration,$v);
				if(!$reimdata){
					$this->model->rollback(); 
					Mylog::write(' ������Ϣ����ʧ��!  ','error');
					continue;
				}
				
				Mylog::write(" <<��Ŀ(".$v['id'].") ����ɹ� ");
				$this->model->commit(); 

			}



		}
		//��ȡ�����ֶ�
		public function getAllCols($arr){
			foreach($arr as $key=>$v){
				$temp[] = $key;
			}
			//$str = implode(',',$temp);
			return $temp;
		}
		//��ӱ���汾��¼
		public function createChangeRecordVersion($pid,$adate,$applicant,$cuser,$status,$type){
			$data['PROJECT_ID'] = $pid;
			$data['ADATE'] = $adate;
			$data['APPLICANT'] = $applicant;
			$data['CURUSER'] = $applicant;
			$data['STATUS'] = $status;
			$data['TYPE'] = $type;
			$res =  M('Erp_project_change')->add($data);
			return $res;
			
		}
		//��ȡ������Ϣ
		public function getFlowStep($pid,$type='ASC'){
			 
			$data = $this->mysqlmodel->query("select * from erp_workstep where pid=$pid order by id $type ");
			$res = $data[0] ? $data[0]:false;
			return $res;
		}
		//
		public function addChangeversion($data,$change){
			$temp = array();
			foreach($data as $key=>$v){
				$temp[$key] = $v;
				$temp[$key.'_OLD'] = $change[$key];
			}
			return $temp;

		}
		//prjbudget
		public function prjbudget_arr($v,$cres){
			$bdata = array();
			$offline_cost = unserialize($v['offline_cost']);
			$bdata['CASE_ID'] = $cres;
			$bdata['SCALETYPE'] = 1;
			$bdata['FROMDATE'] = date('Y-m-d H:i:s',$v['exec_sdate']);
			$bdata['TODATE'] = date('Y-m-d H:i:s',$v['exec_edate']);
			$bdata['AVERAGESETS'] = $v['three_house_num'];//�¾�ȥ������
			$bdata['FIRSTSETS'] = $v['last_house_num'];;//�״�ȥ������
			$bdata['FEE'] = $v['house_price'];//�����շѱ�׼
			$bdata['SUMPROFIT'] = $v['estimate_total'];//Ԥ��������
			$bdata['OFFLINE_COST_SUM_PROFIT'] = $v['estimate_total']-$v['offline_cost_sum'];//����������
			$bdata['OFFLINE_COST_SUM_PROFIT_RATE'] = round(($v['estimate_total']-$v['offline_cost_sum'])/$v['estimate_total']*100,2);//��������
			$bdata['OFFLINE_COST_SUM'] = $v['offline_cost_sum'];
			$bdata['PRO_TAXES'] = ($v['estimate_total']- $offline_cost['third_party']) * 0.1;
			$bdata['PRO_TAXES_PROFIT'] = ($v['estimate_total']- $v['offline_cost_sum']) - $bdata['PRO_TAXES'];
			$bdata['PRO_TAXES_PROFIT_RATE'] =round(($v['estimate_total'] - $v['offline_cost_sum'] - $bdata['PRO_TAXES'])/$v['estimate_total']*100,2);
			$bdata['ONLINE_COST'] = ($v['estimate_total'] - $v['offline_cost_sum'] - $v['online_cost_sum']);
			$bdata['ONLINE_COST_RATE'] =round(($v['estimate_total'] - $v['offline_cost_sum'] - $v['online_cost_sum'])/$v['estimate_total'],4)*100;
			return $bdata;
		}
		//house
		public function house_arr($v,$pres){
			$s_arr = array('1'=>-1,'0'=>0);
			$isfundpool_arr = array('1'=>-1,'0'=>1);
			$hdata = array();
			$hdata['CUSTOMER_MAN'] = $v['CUSER'];//$v['customer_man'];
				$hdata['CONTRACT_NUM'] = $v['contract_num'];
				$hdata['CIT_ID'] = $v['city'];
				$hdata['UCITY_ID'] = $v['city'];
				$hdata['REL_PROPERTY'] = $v['rel_property'];//����¥��
				$hdata['PRO_BLOCK_ID'] = $v['pro_block_id'];//����id
				$hdata['PRO_LISTID'] = $v['pro_listid'];//¥��id
				$hdata['REL_NEWHOUSEID'] = $v['rel_newhouseid'];//����id
				$hdata['DEV_ENT'] = $v['dev_ent'];
				$hdata['PROPERTY_CLASS'] = $v['property_class'];
				$hdata['PRO_ADDR'] = $v['pro_addr'];
				$hdata['PRO_NAME'] = $v['pro_name'];
				$hdata['TLF_SOURCE'] = $v['tlf_source'] ? $v['tlf_source']: ' ';
				$hdata['TLF_DISCOUNT'] = $v['tlf_discount'] ? $v['tlf_discount'] : ' ';
				$hdata['SALEPERMIT'] = $s_arr[$v['salepermit']];//���֤
				$hdata['ISFUNDPOOL'] = $isfundpool_arr[$v['isfundpool']];
				$hdata['SP_EXP'] = $v['sp_exp'];
				$hdata['FPSCALE'] = $v['fpscale'];
				$hdata['RETURN_CONTENT'] = $v['return_content'] ? $v['return_content'] : ' ';
				$hdata['PRO_ADV'] = $v['pro_adv'] ? $v['pro_adv'] :' ';
				$hdata['PRO_INF'] = $v['pro_inf'] ? $v['pro_inf'] : ' ';
				$hdata['PRO_INFO'] = $v['pro_info'];
				$hdata['STATE'] = $v['state'];//*�Ƿ���
				$hdata['APPLY_MODIFY'] = $v['apply_modify'];//*
				$hdata['FINAL_ACCOUNTS'] = $v['final_accounts'];//*
				$hdata['ISRECORD'] = $v['isrecord'];//*
				$hdata['SUBMIT_DATE'] = date('Y-m-d H:i:s',$v['submit_date']);
				$hdata['WORK_STATE'] = $v['work_state'];//*
				$hdata['PROJECT_ID'] = $pres;
				$hdata['ONLINE_AD_SCH'] = '';// ������ڸ���
				$hdata['CONTRACT_FILE'] = '';//��Ŀ��ͬ����ظ���
				$hdata['MONEY_BET'] =  $v['money_bet'];//�ʽ�Զ�
				$hdata['PROPERTIES'] = 1;//������Ŀ����
				$hdata['CONDOMINIUM'] = 0;//�Ƿ��蹫���˺�
				$hdata['PAYMENT_SECURITY'] = $v['sp_exp'];//�Ƿ�֧����֤��
				$hdata['ISCONTRACT'] = -1;//��ͬ�Ƿ��ջ�
				if($v['tag_products'] ){ //��� ������Ʒ
					$tagparr = explode(',',$v['tag_products']);
					$hdata['USING_DECORATION_PRODUCT']  = in_array(1,$tagparr)?1:2; //�Ƿ�ʹ��װ�޲�Ʒ(1=�ǣ�2=��
					$hdata['USING_FINANCIAL_PRODUCT']  = in_array(2,$tagparr)?1:2;//�Ƿ�ʹ�ý��ڲ�Ʒ��1=�ǣ�2=��
				}
			return $hdata;
		}
		//sales_target 
		public function sales_target_arr($one,$bres,$key,$pres){
			$temp = array();
			$temp['BUDGETID'] = $bres;//Ԥ��ID
			$temp['SALEMETHODID'] = $key;//���۷�ʽID ?
			$temp['SETS'] = $one['estimate_house'];//�ɽ�����
			$temp['CUSTOMERS'] = $one['estimate_guide'];//������
			$temp['PROJECTT_ID'] = $pres;
			$temp['ISVALID'] = -1;//״̬
			return $temp;
		}
		//budgetfee
		public function budgetfee_arr($one,$bres,$v,$offline_cost){
			$temp = array();
			$temp['FEEID'] = $one['ID'];
			$temp['BUDGETID'] = $bres;//Ԥ��ID
			$temp['AMOUNT'] = $offline_cost[$one['INPUTNAME']];//���ý�� 
			$temp['RATIO'] = round($offline_cost[$one['INPUTNAME']]/$v['offline_cost_sum'],4)*100;//���ñ���
			$temp['REMARK'] = $offline_cost[$one['INPUTNAME'].'_info'];//˵��
			//$temp['ADDTIME'] = ;//���ʱ��
			$temp['ISVALID'] = '-1';//�Ƿ���Ч
			if( in_array($one['ID'],array(98,99) ) ) {
				$temp['ISONLINE'] = '-1';//���Ϸ���
			}
			return $temp;
		}
		//���� ��� ����
		public function project_flow_data($v,$pres,$cres,$migration,$flowset=6){
			$flowlist = $this->mysqlmodel->query("select * from erp_workstep where pid='".$v['id']."' order by id asc");
			if($flowlist){
				$temp = array();
				$temp['FLOWSETID'] = $flowset;
				$temp['CASEID'] = $cres;
				$temp['MAXSTEP'] = $v['maxstep'];
				$temp['ADDTIME'] = date('Y-m-d H:i:s',$v['submit_date']);
				$temp['ADDUSER'] = $migration->get_users_id($flowlist[0]['deal_userid']);
				$temp['STATUS'] = 1;//���ڽ�����
				$temp['INFO'] = $flowlist[0]['deal_info'];
				$temp['CITY'] = $v['city'];
				$temp['RECORDID'] = $pres;
				$flowres = M('Erp_flows')->add($temp);
				Mylog::write(' Erp_flows ���̴��� ��¼����ɹ�! id: '.$flowres );
			}
			if($flowres){
				foreach($flowlist as $key=>$one){
					$node = array();
					$node['FLOWID'] = $flowres;
					$node['DEAL_USERID'] = $migration->get_users_id($one['deal_userid']);
					if($one['deal_time'])$node['S_TIME'] = date('Y-m-d H:i:s',$one['deal_time']);
					if($one['end_time'])$node['E_TIME'] =  date('Y-m-d H:i:s',$one['end_time']);
					$node['DEAL_INFO'] = $one['deal_info'];
					$node['STEP'] = $one['step'];
					$node['STATUS'] = $one['state'];
					//$node['FILES'] = 
					//$node['ISMALL'] = 
					//$node['ISPHONE'] = 
					$noderes = M('Erp_flownode')->add($node);
					if(!$noderes){
						Mylog::write(' Erp_flownode ���̽ڵ㴴�� ��¼����ʧ��! ','error');
						 
						return false;

					}
							
				}
				Mylog::write(' Erp_flownode ���̴��� ��¼����ɹ�!  '  );
				return true;
			}else{
					Mylog::write(' Erp_flows ���̴��� ��¼����ʧ��! ','error');
					return false; 
			}
		}
		//�����û�����
		public function user_data(){
			$userlist = $this->mysqlmodel->query("select * from erp_user where city in (".$this->citys.")"); // ��ʧЧ�û��Ƿ���Ҫ����?
			 
			foreach($userlist as $one){
				
				$temp = array();
				if(!$user = M('Erp_users')->where("USERNAME='".$one['uid']."' ")->find() ){
					 
					Mylog::write("�û�(".$one['uid'].") ��ʼ����>>  ");
					$this->model->startTrans();
					$temp['DEPTID'] = $one['did'];
					$temp['NAME'] = $one['username'];
					$temp['USERNAME'] = $one['uid'];
					$temp['PASSWORD'] = $one['password'];
					//$temp['TITLE']  ;ְ�� ���������費��Ҫ��
					$temp['ISVALID'] = $one['status']==1 ? -1:0;
					//$temp['GROUP'] =
					$temp['CITYS'] = $one['authcity'];
					$temp['CITY'] = $one['city'];
					$temp['ISPARTTIME'] = $one['parttime']==1 ? -1:0;

					$ures = M('Erp_users')->add($temp);
					if($ures){
						Mylog::write(' Erp_users �û��� ��¼����ɹ�,ID��'.$ures );
							 
					}else{
						$this->model->rollback(); 
						 
						Mylog::write(' Erp_users �û��� ��¼����ʧ��! ','error');
						continue;
					}
					 
					Mylog::write(" <<�û�(".$one['uid'].") ����ɹ� "); 
					$this->model->commit(); 
				}else{
					 
					Mylog::write( "�û�(".$one['uid'].") �Ѵ��� \n"); 
					//if(!$user['CITY'] || !$user['CITYS'] ){
						$this->model->startTrans();
						 
						
						if( $user['CITY']!=$one['city']){
							$temp['CITY'] = $one['city'];
							 
							Mylog::write("��ʼ�����û�(".$one['uid'].") CITY�ֶ� " );
						}
						if( $user['CITYS']!=$one['authcity']){
							$temp['CITYS'] = $one['authcity'];
							 
							Mylog::write("��ʼ�����û�(".$one['uid'].") CITYS�ֶ�  " );
						}

						if(is_null($user['ISPARTTIME'])){
							$temp['ISPARTTIME'] = $one['parttime']==1 ? -1:0;
							 
							Mylog::write("��ʼ�����û�(".$one['uid'].") ISPARTTIME�ֶ�  " );
						}
						if($user['DEPTID']!=$one['did']){
							$temp['DEPTID'] = $one['did'];
							 
							Mylog::write("��ʼ�����û�(".$one['uid'].") DEPTID�ֶ�  " );
						}
						if(!empty($temp)){
							Mylog::write("�û�(".$one['uid'].") ��ʼ����>>  " );
							$ures = M('Erp_users')->where("USERNAME='".$one['uid']."'")->save($temp);
							if($ures){
								 
								Mylog::write('���¸ü�¼�ɹ�  ' );
									 
							}else{
								$this->model->rollback(); 
								 
								Mylog::write(' Erp_users �û��� ����ʧ��! �û�:'.$one['uid'],'error');
								continue;
							}
						 
							Mylog::write(" <<�û�(".$one['uid'].") ���³ɹ� \n");
							
							$this->model->commit(); 
						}
					//}
					

				}

				
			}

		}
		//�����û�����
		public function user_role(){
			$userlist = $this->omsomdel2->query("select * from ERP_USERS where CITY in($this->citys) "); // ��ʧЧ�û��Ƿ���Ҫ����?
			  
			foreach($userlist as $one){
				
				$temp = array();
				if($user = M('Erp_users')->where("USERNAME='".$one['USERNAME']."'")->find() ){
					 
				 
					if($user['ROLEID'] != $one['ROLEID'] ){
						$this->model->startTrans();
						 
						Mylog::write("�û�(".$one['uid'].") ��ʼ����>>  " );
						 
						$temp['ROLEID'] = $one['ROLEID'];
						$ures = M('Erp_users')->where("USERNAME='".$one['USERNAME']."'")->save($temp);
						if($ures){
							 
							Mylog::write('���¸ü�¼�ɹ�  ' );
								 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_users �û��� ����ʧ��! �û�:'.$one['uid'],'error');
							continue;
						}
					 
						Mylog::write(" <<�û�(".$one['uid'].") ���³ɹ� \n");
						
						$this->model->commit(); 
					}else{
						echo $user['ROLEID'].$one['ROLEID'].'next<br>';
					}
					

				}

				
			}

		}

		//���
		//��Ա����
		public function member_limit_data(){
			$res = $this->mysqlmodel->query("select count(*) as count from erp_member where city in (".$this->citys.") "); //
			$count = $res[0]['count'];
			/*for($i=0;$i<3000;$i=$i+1){
				sleep(1);
				$this->member_data($i,$i+1000);
			}*/
			$this->member_data(0,4000);

		}
		public function member_data($star=0,$limit=100000){
		 
			$memberlist = $this->mysqlmodel->query("select * from erp_member where  city in (".$this->citys.") and isdel=0    limit $star,$limit"); //city in (".$this->citys.") and isdel=0  and
			//var_dump($memberlist);
			$migration = D('Migration');//���뷽��model
			
			foreach($memberlist as $v){
				
				$mdata = array();
				Mylog::write( "��Ա(".$v['id'].") ��ʼ����>>");
				if( M('Erp_cardmember')->where("  TLF_MEMBER_ID=".$v['id'])->find() ){
					Mylog::write( "��Ա(".$v['id'].") �Ѵ��ڣ������е��������");
					continue;
				}
				$this->model->startTrans();
				$mdata['CITY_ID'] = $v['city'];
				$mdata['PRJ_ID'] = $migration->get_project_id($v['prjid']);//��ȡ��Ŀid
				$mdata['PRJ_NAME'] = $migration->get_project_name($v['prjid']);//��ȡ��Ŀ����
				$mdata['CASE_ID'] = $migration->get_case_id($mdata['PRJ_ID']);//������Ŀid��ȡcaseid
				$mdata['REALNAME'] = $v['realname'];
				$mdata['MOBILENO'] = $v['mobileno'];//�������ֻ�
				$mdata['LOOKER_MOBILENO'] = $v['looker_mobileno'];//�������ֻ�
				$mdata['CERTIFICATE_TYPE'] = $v['certificate_type'];//֤������
				$mdata['CERTIFICATE_NO'] = $v['idcardno'];//֤����
				$mdata['SOURCE'] = $v['source'];//��Դ
				if($v['subscribetime']) $mdata['SUBSCRIBETIME'] = date('Y-m-d H:i:s',$v['subscribetime']);// �Ϲ�ʱ�� �ύʱ��?
				$mdata['IS_TAKE'] = $v['istake'];//�Ƿ����
				$mdata['ORDER_ID'] = $v['orderid'];//�������������
				$mdata['ROOMNO'] = $v['roomno'];//����
				$mdata['SIGNEDSUITE'] = $v['signedsuite'];//ǩԼ����
				if($v['signtime'])$mdata['SIGNTIME'] = date('Y-m-d H:i:s',$v['signtime']);//ǩԼʱ��
				$mdata['CARDSTATUS'] = $migration->get_cardstatus($v['cardstatus']);//�쿨״̬********
				
				//������˿�δ�����˿��״̬�������������˿���ʱ�ų�����
				/*if($mdata['CARDSTATUS']==4 && $v['paidmoney']>0){
					$mdata['CARDSTATUS']=3;//�������  ״̬��Ϊ�Ѱ���ǩԼ���Ա��ھ��������˿�� 
					Mylog::write( "��Ա(".$v['id'].") <������> ������˿�δ�����˿��״̬�������������˿���ʱ�ų����⣡");
				}
				*/
				if($v['cardtime'])$mdata['CARDTIME'] = date('Y-m-d H:i:s',$v['cardtime']);//�쿨ʱ��
				/*$operator = $migration->get_users_id($v['adduid']);//$migration->get_users_id_byname($v['operator']);
				if(count($operator)>1){
					Mylog::write(' operator �쿨���������� ! �û�����:'.$v['operator'],'error');
				}elseif(count($operator)==1) $mdata['ADD_UID'] = $operator; 
				else Mylog::write(' operator �쿨�����˲����� ! �û�����:'.$v['operator'],'error');*/
				$operator = $mdata['ADD_UID']  = $migration->get_city_adduid($mdata['CITY_ID']);

				$mdata['PAY_TYPE'] = $v['paytype'];//���ʽ
				$mdata['PAID_MONEY'] = (float)$v['paidmoney'];//�Ѹ����
				$mdata['UNPAID_MONEY'] = (float)$v['unpaidmoney'];//δ�����
				//$mdata['REDUCE_MONEY'] =  ;������
				$mdata['INVOICE_STATUS'] = $migration->get_invoice_status($v['invoicestatus']);//��Ʊ״̬******
				$mdata['INVOICE_NO'] = $v['invoice_no'];//��Ʊ���
				$mdata['RECEIPTSTATUS'] = $migration->get_receipt_status($v['receiptstatus']);//�վ�״̬******
				$mdata['RECEIPTNO'] = $v['receiptno'];//�վݱ��
				if($v['confirmtime'])$mdata['CONFIRMTIME'] = date('Y-m-d H:i:s',$v['confirmtime']);//����ȷ��ʱ��/��Ʊʱ��
				//$mdata['CONFIRM_UID'] = //����ȷ���û�
				$mdata['IS_SMS'] = $v['smssign'];//�Ƿ��Ͷ���
				 //�˿�ʱ�� �˿�ʱ��
				
				/*$backoperator = $migration->get_users_id_byname($v['backoperator']);
				if(count($backoperator)>1){
					Mylog::write(' backoperator �˿����������� ! �û�����:'.$v['backoperator'].'ѡ��ʹ�� '.$operator);
					$mdata['BACK_UID'] = $operator;
				}elseif(count($backoperator)==1) $mdata['BACK_UID'] = $backoperator[0]['ID'];
				else {
					$mdata['BACK_UID'] = $operator;
					Mylog::write(' backoperator �˿������˲����� ! �û�����:'.$v['backoperator'].'ѡ��ʹ�� '.$operator);
				}*/
				if($mdata['CARDSTATUS']==4 ){
					$backoperator =  $mdata['BACK_UID'] = $operator;
					if($v['backtime'])$mdata['BACKTIME'] = date('Y-m-d H:i:s',$v['backtime']);//�˿�ʱ��
				}

				$mdata['TOTAL_PRICE'] = $v['total_price'];//�ܼ� 
				//$mdata['AGENCY_REWARD'] = $v['total_price'];�н�Ӷ��
				$mdata['HOUSEAREA'] = $v['housearea'];
				$mdata['HOUSETOTAL'] = $v['housetotal'];
				$mdata['FGJ_SOURCE_DIFF'] = $v['fgj_source_diff'];//���ܼ�״̬�Ƿ���ͬ
				$mdata['NOTE'] = $v['NOTE'];//��ע
				if($v['createtime'])$mdata['CREATETIME'] = date('Y-m-d H:i:s',$v['createtime']);//����ʱ��
				if($v['updatetime'])$mdata['UPDATETIME'] = date('Y-m-d H:i:s',$v['updatetime']);//����ʱ��
				//$mdata['MERCHANT_ID'] = $v['merchantnumber'];//�̻����
				$mdata['FINANCIALCONFIRM'] = $migration->get_financialconfirm($v['financialconfirm']);//����ȷ��״̬ ******
				$mdata['TLF_MEMBER_ID'] = $v['id'];//ԭ������id

				if($v['leadtime'])$mdata['LEAD_TIME'] = date('Y-m-d H:i:s',$v['leadtime']);//����ʱ��
				$mdata['DECORATION_STANDARD'] = $v['decstandard']; //ë����װ 
				$mres = M('Erp_cardmember')->add($mdata);//���ػ�Աid
				if($mres){
					 
					Mylog::write('ERP_MEMBER ��Ա���¼����ɹ�,ID��'.$mres);
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('ERP_MEMBER ��Ա ��¼����ʧ��! ID��'.$mdata['TLF_MEMBER_ID'],'error');
					continue;
				}
				$financialconfirm = $migration->get_payment_financialconfirm($v['financialconfirm']);
				if($v['tradetime']) $tradetime = date('Y-m-d H:i:s',$v['tradetime']);
				else $tradetime = date('Y-m-d H:i:s',$v['cardtime']);
				$rmoney = $this->get_refundmoney($v['id'],$v);//�û�Ա�˿���
				$trademoney = $v['total_price'] - $v['unpaidmoney'] + $rmoney;//��ʼ���׽��
				if($trademoney> $v['total_price']) $trademoney = (float)$v['total_price'];//���⴦�� 
				//if(count($payinfo) ){//�ۺϸ��ʽ
				$invoicemoney =  $trademoney;
				if($v['paytype']==4){//��	
					Mylog::write( ' �˻�ԱΪ�ۺϸ��ʽ ');
					$payinfo = unserialize($v['payinfo']);
					$temp = array();

					//�˿�״̬
					$refundstatus = 0;
					if($mdata['CARDSTATUS']==4 ){//�˿�״̬ 
						$refundstatus = 0;//1;
					}
					foreach($payinfo as $key=>$one){
						$temp = array();
						$pres = $ires1=$ires2= $rres =true;
						$temp['MID'] = $mres;//��Ա���
						$temp['PAY_TYPE'] = $one['paytype'];//���׷�ʽ
						$temp['TRADE_MONEY'] = (float)$one['trademoney'];//���׽��
						$temp['ORIGINAL_MONEY'] = (float)$one['trademoney'];//ԭʼ���
						$temp['RETRIEVAL'] = $one['retrieval'];//��λ������
						$temp['CVV2'] = $one['cvv2'];//���ź���λ
						if($one['tradetime'])$temp['TRADE_TIME'] = date('Y-m-d H:i:s',$one['tradetime']);
						else  $temp['TRADE_TIME'] = $tradetime;
						$temp['STATUS'] = $financialconfirm;//����״̬������ϸ
						$temp['REFUND_STATUS'] = $refundstatus;//�˿�״̬ ??
						if($mdata['CARDSTATUS']==4)$temp['REFUND_MONEY'] = $one['refundmoney']?$one['refundmoney']:$temp['TRADE_MONEY'];//�˿���
						else {
							 $refundone= $this->mysqlmodel->query("select * from erp_member_newrefund where refundstatus=10 and  mid='".$mdata['TLF_MEMBER_ID'] ."'  and payid=$key "); 
							//var_dump($refundone);
							$temp['REFUND_MONEY'] = $refundone ? $refundone[0]['rmoney'] : 0;
						}
						$temp['ADD_UID'] = $mdata['ADD_UID'];//����� ������
						$temp['MERCHANT_NUMBER'] = $one['merchantnumber'];	

						$pres = M('Erp_member_payment')->add($temp);//����֧�����
						if(!$pres) {
							Mylog::write( ' Erp_member_payment ֧����ϸ��¼����ʧ��1 ��Ա���:'.$mres ,'error');  
							break; 
						}else  Mylog::write( ' Erp_member_payment ֧����ϸ��¼����ɹ�,ID��'.$pres);

						///////
						// ������ϸ  ���̻�Ա֧��
						$ires1 = $this->income_info($mdata['CASE_ID'],$mres,$pres,1,$temp['TRADE_MONEY'],$operator,$mdata['CARDTIME'],'���̻�Ա֧��');
						if(!$ires1 ){ 
								Mylog::write( ' ���̻�Ա֧������ʧ�� ��Ա���:'.$mres ,'error');  
					  
								break;
						}
						if($temp['STATUS']==1){// || $rmoney >0
							// ������ϸ  ȷ�ϵ��̻�Ա����
							$ires2 = $this->income_info($mdata['CASE_ID'],$mres,$pres,2,$temp['TRADE_MONEY'],$operator,$mdata['CARDTIME'],'ȷ�ϵ��̻�Ա����') ;
							if(!$ires2){ 
									Mylog::write( ' ȷ�ϵ��̻�Ա�������ʧ�� ��Ա���:'.$mres,'error' ); 
						  
									break;
							}
						}

						if($rmoney && $pres ){// �˿�
							$where = " and payid = $key ";
							$rres=$this->refund_list_detail($mdata['CITY_ID'],$migration,$mdata['CASE_ID'],$v['id'],$mres,$pres,$temp['ADD_UID'],$where,$v['backtime'],$v['invoicestatus'],$temp['TRADE_MONEY'],$temp);
							if(!$rres ){//�˿����ϸ
								 
								Mylog::write( ' �˿����ϸ����ʧ�� ��Ա���:'.$mres,'error' );
								break;
							}
						}
						 
					}
					if($payinfo){
						if($pres && $ires1 && $ires2 && $rres ){
								Mylog::write( ' Erp_member_payment  ֧����ϸ��¼ȫ������ɹ� ' );
						}else{
								$this->model->rollback(); 
						 
								Mylog::write(' Erp_member_payment ֧����ϸ��¼����ʧ��2! ','error');
								continue;
						}
					}
					else
					{
						Mylog::write( $mres . '<������> payinfo Ϊ�� ' );						
					}
				}else{//���ۺϸ��ʽ
					$refundstatus = $refundmoney = 0;
					Mylog::write( ' �˻�Ա���ʽ��'.$v['paytype']);
					//$trademoney = $v['trademoney'] ? $v['trademoney']: ($v['refundmoney']? 0 :$v['paidmoney']);
					//$rmoney = $this->get_refundmoney($v['id']);//�û�Ա�˿���
					//$trademoney = $v['total_price'] - $v['unpaidmoney'] + $rmoney;
					if($mdata['CARDSTATUS']==4 ){//�˿�״̬ 
						//$trademoney = $refundmoney =  $v['unpaidmoney'];//�����˿������ݲ�����
						$trademoney = $rmoney;
						//$refundstatus = 1;

					}
					
					 
					if($trademoney){
						$temp = array();
						$temp['MID'] = $mres;//��Ա���
						$temp['PAY_TYPE'] = $v['paytype'];//���׷�ʽ
						if($mdata['CARDSTATUS']!=4 &&  $rmoney){
							$temp['TRADE_MONEY'] = $trademoney  ;//���׽��
							$invoicemoney = $trademoney-$rmoney;
							
						}else{
							$temp['TRADE_MONEY'] = $trademoney ;//���׽��
							$invoicemoney = $trademoney;
						}
						$temp['ORIGINAL_MONEY'] = $trademoney ;//ԭʼ���
						$temp['RETRIEVAL'] = $v['retrieval'];//��λ������
						$temp['CVV2'] = $v['cvv2'];//���ź���λ
						$temp['TRADE_TIME'] = $tradetime;
						 
						$temp['STATUS'] = $financialconfirm;//����״̬
						$temp['REFUND_STATUS'] = $refundstatus;//�˿�״̬ ??
						$temp['REFUND_MONEY'] = $rmoney;//�˿���
						$temp['ADD_UID'] = $mdata['ADD_UID'];//����� ������
						$temp['MERCHANT_NUMBER'] = $v['merchantnumber'];	
						$pres = M('Erp_member_payment')->add($temp);//����֧�����
						if($pres){
							Mylog::write( ' Erp_member_payment ֧����ϸ��¼����ɹ�,ID��'.$pres);
						}else{
							$this->model->rollback(); 
					 
							Mylog::write(' Erp_member_payment ֧����ϸ��¼����ʧ��! ��Ա���:'.$mres,'error');
							continue;
						}

							///////
						// ������ϸ ���̻�Ա֧��
						$ires = $this->income_info($mdata['CASE_ID'],$mres,$pres,1,$temp['TRADE_MONEY'],$operator,$mdata['CARDTIME'],'���̻�Ա֧��');
						if(!$ires ){ 
								$this->model->rollback(); 
						  
								continue;
						}
						if($temp['STATUS']==1){// || $rmoney >0
							// ������ϸ  ȷ�ϵ��̻�Ա����
							$ires2 = $this->income_info($mdata['CASE_ID'],$mres,$pres,2,$temp['TRADE_MONEY'],$operator,$mdata['CARDTIME'],'ȷ�ϵ��̻�Ա����');
							if(!$ires2 ){ 
									$this->model->rollback(); 
							  
									continue;
							}
						}
 


						if( $mdata['CARDSTATUS']==4 || $v['paidmoney']<$v['total_price'] || ($mdata['CARDSTATUS']!=4 &&  $rmoney) ){// �˿� 
							$rdres=$this->refund_list_detail($mdata['CITY_ID'],$migration,$mdata['CASE_ID'],$v['id'],$mres,$pres,$temp['ADD_UID'],null,$v['backtime'],$v['invoicestatus'],$rmoney,$temp);
							//echo '�˿� id'.var_dump($rdres);
							if(!$rdres ){//�˿����ϸ
								$this->model->rollback();
						  
								continue;
							}
 
						

						}
					}
					
					
					
					

				}


				if(in_array($v['invoicestatus'],array(2,3,4)) ){//��Ʊ �ѽ�ͨ���Ĳŵ���
						//$invoicemoney =  $trademoney;
						$invoicestatus = $migration->get_invoicestatu($v['invoicestatus']);//******
						$icres = $this->invoice_record($v,$mres,$operator,$mdata['INVOICE_STATUS'],$mdata['CASE_ID'],$invoicemoney);
						if( !$icres ){//��Ʊ��¼
							$this->model->rollback();
					  
							continue;
						}
						// ������ϸ  ���̻�Ա��Ʊ
						//$invoicemoney = $v['paidmoney']?$v['paidmoney']:$v['total_price'];
						$confirmtime = $v['confirmtime'] ? $v['confirmtime']:$v['cardtime'];
						$ires = $this->income_info($mdata['CASE_ID'],$mres,$pres,3,$invoicemoney,$operator,date('Y-m-d H:i:s',$confirmtime),'���̻�Ա��Ʊ');
						if(!$ires ){ 
							$this->model->rollback();
					  
							continue;
						}

						
				}
				

				Mylog::write(" <<��Ա(".$v['id'].") ����ɹ� ");
				$this->model->commit(); 

			}

		}
		//��ȡ��Ա�˿���
		public function get_refundmoney($mid,$v){
			$res = $this->mysqlmodel->query("select sum(rmoney)  as rmoney from erp_member_newrefund where refundstatus=10 and  mid='$mid' ");// var_dump($res);
			if($res[0]['rmoney']){
				return $res[0]['rmoney'];
			}else {
				if($v['paytype']==4 && $v['cardstatus']==4){
					$payinfo = unserialize($v['payinfo']);
					$money = 0;
					foreach($payinfo  as $one){
						if($one['refundmoney'])$money += $one['refundmoney'];
						else $money += $one['trademoney'];
					}
					return $money;
				}elseif( $v['cardstatus']==4 ){
					return (float)$v['total_price'];
				}else
				return 0;
			}
		}
		//�˿����ϸ
		public function refund_list_detail($city,$migration,$caseid,$mres,$newmres,$pres,$ADD_UID,$where,$backtime,$invoicestatus=0,$rmoney=0,$temp=null){
				$flag = $where ? 1:0;
				$where = $where ? $where : ' limit 1';
				$mlist = $this->mysqlmodel->query("select * from erp_member_newrefund where refundstatus=10 and  mid='$mres' $where");//var_dump($mlist ); echo "select * from erp_member_newrefund where mid='$mres' $where"; ֵ������״̬10 
				//var_dump($mlist);
				if(!$mlist) { 
					$mlist = $this->mysqlmodel->query("select * from erp_member_refund where refundstatus=2 and  mid='$mres' $where"); 
					$oldrefund =1;
					if(!$mlist && !$flag){
						if($rmoney>0){
							$mone['refundstatus'] = 10;
							$mone['rmoney'] = (float)$temp['TRADE_MONEY'];
							$mone['createtime'] = $backtime;
							$mlist[] = $mone;
						}
					}
				}else $oldrefund =0;
				$listid = $rres = $inres = true; //var_dump($backtime);
				foreach($mlist as $mone){
					$mone['refundstatus'] = $oldrefund ? 10:$mone['refundstatus'] ;//����������̼�¼ 
					$mone['rmoney'] = $oldrefund ?$rmoney:(float)$mone['rmoney'];
					$listid = $rres = $inres = true;
					$temp = $temp2 =  array();
					$temp['ADD_UID'] = $ADD_UID;//����� ������
					$temp['CREATETIME'] = date('Y-m-d H:i:s',$mone['createtime']);
					$temp['STATUS'] = $migration->get_refund_list_status($mone['refundstatus']);//״̬****
					$temp['CITY_ID'] = $city;
					$listid = M('Erp_member_refund_list')->add($temp);
					if(!$listid){
						Mylog::write(' Erp_member_refund_list �˿��¼����ʧ��! ','error');
						
						 
						break;
					}else{
						Mylog::write( ' Erp_member_refund_list �˿��¼����ɹ�,ID��'.$listid);
					}
					$temp2['MID'] = $newmres; //��Աid
					$temp2['PAY_ID'] = $pres ;//�����
					$temp2['REFUND_MONEY'] = (float)$mone['rmoney'];//�˿���
					if($backtime)$temp2['CONFIRMTIME'] =  date('Y-m-d H:i:s',$backtime);// ����ȷ��ʱ��
					$temp2['REFUND_STATUS'] = $migration->get_refund_status($mone['refundstatus']);//����״̬****
					$temp2['LIST_ID'] = $listid;//�˿���
					$temp2['APPLY_UID'] = $ADD_UID;//����� ������
					if($mone['createtime'])$temp2['CREATETIME'] = date('Y-m-d H:i:s',$mone['createtime']);
					$temp2['CITY_ID'] = $city;

					$rres = M('Erp_member_refund_detail')->add($temp2);
					if(!$rres){
						Mylog::write(' Erp_member_refund_detail �˿���ϸ��¼����ʧ��! ','error');
						
						 
						break;
					}else{
						Mylog::write( ' Erp_member_refund_detail �˿���ϸ��¼����ɹ�,ID��'.$rres);
					}
					//if(in_array($invoicestatus,array(2,3,4)) ){//���̿�Ʊ��Ա�˿�  
					if(false){
						$inres = $this->income_info($caseid,$newmres,$pres,20,-intval($mone['rmoney']),$ADD_UID,$temp2['CREATETIME'],'���̿�Ʊ��Ա�˿�');

					}else{
						$inres = $this->income_info($caseid,$newmres,$pres,4,-intval($mone['rmoney']),$ADD_UID,$temp2['CREATETIME'],'����δ��Ʊ��Ա�˿�');
					}
					//������ϸ ���̻�Ա�˿�
					
					if(!$inres ){ 
						 
						
						 
						break;
					}

					/*if($mone['refundstatus']==2){
						$this->refund_flow_data( $listid,$city);

					}*/  //���� 
					//$rmoney +=$mone['rmoney'];

				}
				if($listid && $rres && $inres) 
					return true ;
				else return false ;
		}
		public function refund_flow_data( $listid,$city,$flowset=14){
			$flows = $this->mysqlmodel->query("select * from erp_flows where FIND_IN_SET(".$v['id'].",context) ");
			if($flows){
				$temp = array();
				$temp['FLOWSETID'] = $flowset;
				//$temp['CASEID'] = 
				$temp['MAXSTEP'] = $flows[0]['maxstep'];
				$temp['ADDTIME'] = date('Y-m-d H:i:s',$flows[0]['addtime']);
				$temp['ADDUSER'] = $migration->get_users_id($flows[0]['adduser']);
				$temp['STATUS'] = 1;//���ڽ�����
				$temp['INFO'] = $flows[0]['info'];
				$temp['CITY'] = $city;
				$temp['RECORDID'] = $listid;
				$flowres = M('Erp_flows')->add($temp);
				Mylog::write(' Erp_flows ���̴��� ��¼����ɹ�! id: '.$flowres );
			}
			if($flowres){
				$flowlist = $this->mysqlmodel->query(" select * from erp_workflow where flowid = $flowres order by id asc ");
				foreach($flowlist as $key=>$one){
					$node = array();
					$node['FLOWID'] = $flowres;
					$node['DEAL_USERID'] = $migration->get_users_id($one['deal_userid']);
					if($one['s_time'])$node['S_TIME'] = date('Y-m-d H:i:s',$one['s_time']);
					if($one['e_time'])$node['E_TIME'] =  date('Y-m-d H:i:s',$one['e_time']);
					$node['DEAL_INFO'] = $one['deal_info'];
					$node['STEP'] = $one['step'];
					//$node['STATUS'] = ?
					//$node['FILES'] = 
					//$node['ISMALL'] = 
					//$node['ISPHONE'] = 
					$noderes = M('Erp_flownode')->add($node);
					if(!$noderes){
						Mylog::write(' Erp_flownode ���̽ڵ㴴�� ��¼����ʧ��! ','error');
						 
						return false;

					}
							
				}
				Mylog::write(' Erp_flownode ���̴��� ��¼����ɹ�!  '  );
				return true;
			}else{
					Mylog::write(' Erp_flows ���̴��� ��¼����ʧ��! ','error');
					return false; 
			}

		}
		//��Ʊ
		public function invoice_record($data,$mres,$operator,$invoicestatus,$caseid,$trademoney){
			$temp = array();
			$temp['CASE_ID'] = $caseid;
			$temp['CONTRACT_ID'] =  $mres;//��Աid
			$temp['INVOICE_NO'] =  $data['invoice_no'];//��Ʊ��
			//$temp['INVOICE_MONEY'] = $data['paidmoney']?$data['paidmoney']:$data['total_price'];//$data['invoicemoney'];//��Ʊ���
			$temp['INVOICE_MONEY'] = $trademoney;
			$temp['USER_ID'] = $operator;//��Ʊ��
			if($data['confirmtime'])$temp['CREATETIME'] = date('Y-m-d H:i:s',$data['confirmtime']); //����ʱ��
			$temp['APPLY_USER_ID'] = $operator;//��Ʊ������
			$temp['STATUS'] = $invoicestatus;// ��Ʊ״̬ ??????????????
			if($data['confirmtime'])$temp['INVOICE_TIME'] = date('Y-m-d H:i:s',$data['confirmtime']);//��Ʊʱ��
			$temp['INVOICE_TYPE'] = 2;//1����ͬ�Ŀ�Ʊ 2����Ա�Ŀ�Ʊ 3��������Ա��Ʊ
			//$temp['FLOW_ID'] ='] =
			$inres = M('Erp_billing_record')->add($temp);
			if(!$inres){
				Mylog::write(' Erp_billing_record ��Ʊ��¼����ʧ��! ','error');
				return false; 
			}else{
				Mylog::write( ' Erp_billing_record ��Ʊ��¼����ɹ�,ID��'.$inres);
				return true;
			}

			
		}
		//�����¼
		public function income_info($caseid,$mres,$pres,$incomefrom,$money,$operator,$otime,$remark){
			$ProjectIncome_model = D("ProjectIncome");
			$temp['CASE_ID']  =  $caseid;
			$temp['ENTITY_ID']  =$mres;
			$temp['PAY_ID']  =  $pres;
			$temp['ORG_ENTITY_ID']  =$mres;//ԭʼҵ��id
			$temp['ORG_PAY_ID']  =  $pres;//ԭʼ..
			$temp['INCOME_FROM']  = $incomefrom;
			$temp['INCOME']  =  $money;
			$temp['INCOME_REMARK']  = $remark;
			//$temp['OUTPUT_TAX']  =  
			$temp['ADD_UID']  = $operator;
			$temp['OCCUR_TIME']  = $otime;//����ʱ��
			$res = $ProjectIncome_model->add_income_info($temp);
			if($res){
				
				Mylog::write( ' ERP_INCOME_LIST �����¼����ɹ�,ID��'.$res);
				return true;
			}else{
				Mylog::write(' ERP_INCOME_LIST �����¼����ʧ��! ','error');
				return false; 
				
			}
		}
		//�ɱ���¼
		public function cost_info($type,$cres,$purreq ,$purlist,$feeid,$fee,$userid,$apptime,$isfp,$iskf,$info){
			//���ɱ�������Ӽ�¼
			$cost_info['CASE_ID'] = $cres;            //������� �����       
			$cost_info['ENTITY_ID'] = $purreq;                 //ҵ��ʵ���� �����
			$cost_info['EXPEND_ID'] = $purlist;                    //�ɱ���ϸ��� �����
			
			$cost_info['ORG_ENTITY_ID'] = $purreq;              //ҵ��ʵ���� �����
			$cost_info['ORG_EXPEND_ID'] = $purlist;
			
			$cost_info['FEE'] = $fee;                // �ɱ���� ����� 
			$cost_info['ADD_UID'] = $userid ;             //�����û���� �����
			$cost_info['OCCUR_TIME'] = $apptime;        //����ʱ�� �����
			$cost_info['ISFUNDPOOL'] = $isfp;                                //�Ƿ��ʽ�أ�0��1�ǣ� �����
			$cost_info['ISKF'] = $iskf;                                    //�Ƿ�۷� �����
			$cost_info['FEE_REMARK'] = $info;                 //�������� ��ѡ�
			$cost_info['INPUT_TAX'] = 0;                              //����˰ ��ѡ�
			$cost_info['FEE_ID'] = $feeid;                               //�ɱ�����ID �����
			//�ɱ���Դ
			 
			$cost_info['EXPEND_FROM'] = $type;//��Դ����
			 
			$project_cost_model = D("ProjectCost");
			//var_dump($cost_info);die;
			return $project_cost_model->add_cost_info($cost_info);

		}
		//��������
		public function reimbursement_data($pres,$cres,$migration,$v){


			$iskf = 1;//�۷�
			$rlist = $this->mysqlmodel->query("select * from erp_reimbursement where pid='".$v['id']."' and state = 1");
			if($rlist){
				
				$amount = 0;
				foreach($rlist as $key=>$one){
					/////
					$submit_user =  $one['submit_user'] ? $one['submit_user'] :$v['submit_userid'];
					$requisition['CASE_ID'] = $cres;
					$requisition['REASON'] = '��������������';
					$requisition['USER_ID'] = $migration->get_users_id($submit_user);
					$requisition['DEPT_ID'] =$migration->get_users_deptid($submit_user) ;
					$requisition['APPLY_TIME'] = date('Y-m-d H:i:s',$one['submit_date']);//�ύʱ��
					if($one['re_con_time'])$requisition['END_TIME'] = date('Y-m-d H:i:s',$one['re_con_time']);//�ʹ�ʱ��
					$requisition['PRJ_ID'] = $pres;
					$requisition['TYPE'] = 1;//project_purchase
					$requisition['CITY_ID'] = $v['city'];
					$requisition['STATUS'] = 4;//�Ѳɹ�
					$purreq = M('Erp_purchase_requisition')->add($requisition);//�ɹ�����
					//var_dump($purreq );
					if(!$purreq) {
						Mylog::write( ' Erp_purchase_requisition �ɹ������¼����ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_purchase_requisition �ɹ������¼����ɹ�: '.$purreq);
					$reim_list_arr = array();
					//$reim_list_arr['AMOUNT'] = //?
					$reim_list_arr['STATUS'] = 2; //״̬
					if($one['re_con_time'])$reim_list_arr['REIM_TIME'] = date('Y-m-d H:i:s',$one['re_con_time']);//����ʱ��
					$reim_list_arr['REIM_UID'] = $requisition['USER_ID'];//���������
					$reim_list_arr['TYPE'] =  1;//�ɹ�
					$reim_list_arr['APPLY_UID'] =  $requisition['USER_ID'];
					$reim_list_arr['APPLY_TRUENAME'] = $migration->get_users_name($submit_user) ;
					$reim_list_arr['APPLY_TIME'] =  date('Y-m-d H:i:s',$one['submit_date']);//����ʱ��
					$reim_list_arr['CITY_ID'] =  $v['city'];
					$reim_list_id = M('Erp_reimbursement_list')->add($reim_list_arr);//�������뵥
					if(!$reim_list_id) {
						Mylog::write( ' Erp_reimbursement_list �������뵥��¼����ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_reimbursement_list ���������¼����ɹ�: '.$reim_list_id);
					$contract = array();
					$contract['CONTRACTID'] = '';//��ͬ��?
					$contract['PROMOTER'] = $requisition['USER_ID'];//������
					$contract['TYPE'] =1;//����
					$contract['SIGINGTIME'] =date('Y-m-d H:i:s',$one['submit_date']);//ǩ��ʱ��
					$contract['REIM_ID'] = $reim_list_id;//������ID
					$contract['ISSIGN'] = 2;//�Ƿ�ǩԼ  ״ֵ̬����
					$contract['CITY_ID'] = $v['city'];// 
					$conid = M('Erp_contract')->add($contract);//�ɹ���ͬ
					if(!$conid)  {
						Mylog::write( ' Erp_contract �ɹ���ͬ��¼����ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_contract �ɹ���ͬ��¼����ɹ�: '.$conid);




					//////
					$isfp = 0;
					if(TRIM($one['submit_type'])=='re_third_party')
						$isfp = 1;

					$purchase_info = array();
					$purchase_info['PR_ID'] = $purreq;//�ɹ�������
					//$purchase_info['BRAND'] = u2g(strip_tags($_POST['BRAND']));
					//$purchase_info['MODEL'] = u2g(strip_tags($_POST['MODEL']));
					$purchase_info['PRODUCT_NAME'] =  $one['submit_info'] ;
					$purchase_info['NUM_LIMIT'] = 1;//��������
					$purchase_info['PRICE_LIMIT'] = $one['submit_cost'];//�۸�����
					$purchase_info['PRICE'] = $one['submit_cost'];//�۸�����
					$purchase_info['FEE_ID'] = $migration->get_fee_id(substr($one['submit_type'],3));
					$purchase_info['IS_FUNDPOOL'] = $isfp;//�Ƿ��ʽ��
					$purchase_info['IS_KF'] = $iskf;//�Ƿ�۷�
					$purchase_info['P_ID'] = 0;//ָ���ɹ���
					$purchase_info['TYPE'] = 1;//�ɹ����� 1ҵ��ɹ���2���ڲɹ�
					$purchase_info['CONTRACT_ID'] = $conid;//��ͬ��
					$purchase_info['CASE_ID'] =  $cres;//��
					$purchase_info['CITY_ID'] = $v['city']; 
					$purchase_info['STATUS'] = 2; //�ѱ���

					$purchase_info['NUM'] = 1; 
					//$purchase_info['PURCHASE_COST'] = $one['submit_cost']; 
					//$purchase_info['TOTAL_COST'] = $one['submit_cost']; 

					$purlist = M('Erp_purchase_list')->add($purchase_info);//�ɹ���ϸ
					if(!$purlist)  {
						Mylog::write( ' Erp_purchase_list �ɹ���ϸ��¼����ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_purchase_list �ɹ���ϸ��¼����ɹ�: '.$purlist);
					$arr_reim_data = array();
					$arr_reim_data['CITY_ID'] = $v['city'];
    				$arr_reim_data['CASE_ID'] = $cres;
    				$arr_reim_data['BUSINESS_ID'] = $purlist;//������ҵ��ID 
    				$arr_reim_data['MONEY'] = (float)$one['submit_cost'];//�������
    				$arr_reim_data['STATUS'] = 1;//�ѱ���
    				$arr_reim_data['APPLY_TIME'] = date('Y-m-d H:i:s',$one['submit_date']);
    				$arr_reim_data['ISFUNDPOOL'] = $isfp;
    				$arr_reim_data['ISKF'] = $iskf;//Ĭ�Ͽ۷�
    				$arr_reim_data['TYPE'] =  1;//��������
					$arr_reim_data['LIST_ID'] = $reim_list_id;//���������
					$arr_reim_data['FEE_ID'] = $purchase_info['FEE_ID'];


					$reimdetail = M('Erp_reimbursement_detail')->add($arr_reim_data);//������ϸ
					if(!$reimdetail) {
						Mylog::write( ' Erp_reimbursement_detail ������ϸ��¼����ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_reimbursement_detail ������ϸ��¼����ɹ�: '.$reimdetail);
					$amount += $one['submit_cost'];
					$costrest = $this->cost_info(1,$cres,$purreq ,$purlist,$purchase_info['FEE_ID'],$one['submit_cost'],$requisition['USER_ID'],date('Y-m-d H:i:s',$one['submit_date']),$isfp,$iskf,$one['submit_info'].'�ɹ�����');// 
					if(!$costrest) {
						Mylog::write( ' Erp_COST_LIST �ɹ����� ����ɱ���¼ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_COST_LIST �ɹ����� ����ɱ���¼����ɹ�: '.$costrest);
					$costrest = $this->cost_info(2,$cres,$purreq ,$purlist,$purchase_info['FEE_ID'],$one['submit_cost'],$requisition['USER_ID'],date('Y-m-d H:i:s',$one['submit_date']),$isfp,$iskf,$one['submit_info'].'�ɹ���ͬǩ��');// 
					if(!$costrest) {
						Mylog::write( ' Erp_COST_LIST �ɹ���ͬǩ�� ����ɱ���¼ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_COST_LIST �ɹ���ͬǩ�� ����ɱ���¼����ɹ�: '.$costrest);
					/*$costrest = $this->cost_info(3,$cres,$purreq ,$purlist,$purchase_info['FEE_ID'],$one['submit_cost'],$requisition['USER_ID'],date('Y-m-d H:i:s',$one['submit_date']),$isfp,$iskf,$one['submit_info'].'�ɹ���������');//  
					if(!$costrest) {
						Mylog::write( ' Erp_COST_LIST �ɹ��������� ����ɱ���¼ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_COST_LIST �ɹ��������� ����ɱ���¼����ɹ�: '.$costrest);*/
					$costrest = $this->cost_info(4,$cres,$purreq ,$purlist,$purchase_info['FEE_ID'],$one['submit_cost'],$requisition['USER_ID'],date('Y-m-d H:i:s',$one['re_con_time']),$isfp,$iskf,$one['submit_info'].'�ɹ�����ͨ��');// 
					if(!$costrest) {
						Mylog::write( ' Erp_COST_LIST �ɹ�����ͨ�� ����ɱ���¼ʧ��','error');
						return false;
					}else Mylog::write( ' Erp_COST_LIST �ɹ�����ͨ�� ����ɱ���¼����ɹ�: '.$costrest);
				}
				$temp = array();
				$temp['AMOUNT'] = $amount;
				$reim_list_res = M('Erp_reimbursement_list')->where("ID=$reim_list_id")->save($temp);
				if(!$reim_list_res)  {
					Mylog::write( ' Erp_reimbursement_list �������������ʧ��','error');
					return false;
				}else Mylog::write( ' Erp_reimbursement_list ������������³ɹ�: '.$reim_list_res);
			}
			return true;

		}
		//���̵��룿
		//�ļ�����
		//����
		public function city_data(){
			$cfg['city'] = array(
				'1' => '�Ͼ�',
				'2' => '����',
				'3' => '��ɽ',
				'4' => '����',
				'5' => '����',
				'6' => '�Ϸ�',
				'7' => '�ߺ�',
				'8' => '����',
				'9' => '����',
				'10' => '����',
				'11' => '����',
				'101' => '����',
				'102' => '����',
				'103' => '��ɽ',
				'104' => '����',
				'105' => '�人',
				'106' => '������',
				'107' => '����',
				'108' => '���',
				'109' => '����',
				'110' => 'ʯ��ׯ',
				'111' => '����',
				'112' => '֣��',
				'113' => '��ͨ',
			);

			$cfg['citypinyin'] = array(
				'1' => 'nj',
				'2' => 'sz',
				'3' => 'ks',
				'4' => 'wx',
				'5' => 'cz',
				'6' => 'hf',
				'7' => 'wh',
				'8' => 'hz',
				'9' => 'xa',
				'10' => 'cq',
				'11' => 'sy',
				'101' => 'bb',
				'102' => 'chuzhou',
				'103' => 'mas',
				'104' => 'fy',
				'105' => 'wuhan',
				'106' => 'hrb',
				'107' => 'cc',
				'108' => 'tj',
				'109' => 'km',
				'110' => 'sjz',
				'111' => 'la',
				'112' => 'zz',
				'113' => 'nt',
			);
			foreach($cfg['city'] as $key=>$v){
				$data = array();
				$data['ID'] = $key;
				$data['NAME'] = $v;
				$data['ISVAILD'] = -1;
				$data['PY'] = $cfg['citypinyin'][$key]; 
				$one = M('Erp_city')->where("ID=".$key)->find();
				if($one){
					$res = M('Erp_city')->where("ID=".$key)->save($data);
				}else{
					$res = M('Erp_city')->add($data);
				}
				if(!$res)  Mylog::write(' ���в���! '.$v,'error');
			}


		}
		//��ɫ����
		public function group_data(){
			$list = $this->mysqlmodel->query("select * from erp_group where title like '%����%' and status=1"); 
			foreach($list as $one){
				
				$temp = array();
				$temp['LOAN_GROUPNAME'] = $one['title'];
				$temp['LOAN_GROUPSTATUS'] = 1;
				$temp['LOAN_GROUPCREATED'] = $one['dateline'];
				$res = D('Erp_group')->add($temp);
				if($res){
					$utemp = array();
					$user =  $this->mysqlmodel->query("select uid from erp_user where gid ='".$one['id']."'");
					$utemp['ROLEID'] = $res;
					$ress = M('Erp_users')->where("USERNAME='".$user[0]['uid']."'")->save($utemp);
					if($ress )echo $one['title'].'ok'; 
					else var_dump($user);
				}
				else echo 'error<br>';
			}

		}

		//��������
		function import_file(){
			$projectlist = $this->mysqlmodel->query('select * from erp_project where city in ('.$this->citys.') and state<10 and old_id=0      ');
			foreach($projectlist as $key=>$one){
				$contract = unserialize($one['pro_annex']);  
				$online_ad = unserialize($one['online_ad_sch']);//var_dump($online_ad);
				$arr = array();
				$temp = array();
				if( $contract['filename']){
					$res = $this->execUpload($contract);
					if($res) $temp[] = $res;
				}else{
					foreach($contract as $vv){
						if( $vv) {
							$res =  $this->execUpload($vv); 
							if($res) $temp[] = $res;
						}
					}
				}
				$arr['CONTRACT_FILE'] = implode(',',$temp);
				$temp = array();
				if( $online_ad['filename']){
					$res = $this->execUpload($online_ad);
					if($res) $temp[] = $res;
				}else{
					foreach($online_ad as $vv){
						if( $vv){
							$res = $this->execUpload($vv);
							if($res) $temp[] = $res;
						}
					}
				}
				
				$arr['ONLINE_AD_SCH'] = implode(',',$temp);  //var_dump($arr);
				$project = M('Erp_project')->where('TLF_PROJECT_ID='.$one['id'])->find();
				$res = M('Erp_house')->where('PROJECT_ID='.$project['ID'])->save($arr);
				if($res){
					Mylog::write(' �ļ�����ɹ�! '.$one['id'] );
				}else{
					Mylog::write(' �ļ�����! '.$one['id'],'error');
				}
			}

		}
		function execUpload($file){
			 
			//$file = '/doucment/Readme.txt';
			$ch = curl_init();
			$post_data = array(
				 
				'Filedata' => '@'.realpath('D:'.$file['filesrc']).";type=".'application/octet-stream'.";filename=".$file['filename']
			);
			 
			$ch = curl_init();
			$t_url = $t_url ? $t_url : "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			 
			 
			      
			curl_setopt($ch, CURLOPT_URL, 'http://localhost/tpp/app/index.php?s=Migration/save2oracle'); 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($ch, CURLOPT_REFERER, $t_url); 
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
			 
			
			$content = curl_exec($ch);
			curl_close($ch);
			return  $content;
			//return $content; 
		}
		function save2oracle(){
				ini_set('display_errors',1);  
				 
				$lob_upload = $_FILES['Filedata'];
				//Mylog::write( $lob_upload['name']);
				$FILE_TYPE = $lob_upload['type'];
				$FILE_NAME = $lob_upload['name'];  
				$FILE_SIZE = $lob_upload['size']; 
				$FILE_CODE = md5($FILE_TYPE .$FILE_NAME.$FILE_SIZE.time() );
				//$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 202.102.83.186)(PORT = 1521)))(CONNECT_DATA=(SID=OMS2)))";
				//$conn =  oci_connect(C('DB_USER'),C('DB_PWD'),$db); 
				$conn = oci_connect(C('DB_USER'), C('DB_PWD'), C('DB_NAME'));
				
				//$conn = oci_connect($user, $password);
				$lobb = oci_new_descriptor($conn, OCI_D_LOB); 
				$sql = "insert into ERP_FILES(FILE_TYPE,FILE_NAME,FILE_SIZE,FILE_CODE,FILE_DATA)values('$FILE_TYPE','$FILE_NAME','$FILE_SIZE','$FILE_CODE',EMPTY_BLOB()) returning FILE_DATA into :blobb";
				$stmt = oci_parse($conn, $sql);
				//Mylog::write( '$sql;'.$sql);
				oci_bind_by_name($stmt, ':blobb', $lobb, -1, OCI_B_BLOB);
				oci_execute($stmt, OCI_DEFAULT);
				if ($lobb->saveFile($lob_upload['tmp_name'])){
					oci_commit($conn);
					/*$sql = "SELECT seq_erp_files.currval currval FROM dual";
					$ora_test = oci_parse($conn,$sql);
					oci_execute($ora_test,OCI_DEFAULT);  
					//echo "Blob successfully uploaded ";
					$r=oci_fetch_row($ora_test);
					echo $r[0];*/
					echo $FILE_CODE.'-'.$FILE_NAME.'-'.$FILE_SIZE;
					//Mylog::write( '$FILE_CODE;'.$FILE_CODE);

				}else{
					//echo "error";
				}
				oci_free_descriptor($lobb);
				oci_free_statement($stmt);
				oci_close($conn);
				//exit();
				 
		}
		//�����û��ֻ����� �� ��ְ״̬
		function import_phone(){
			$userlist = $this->mysqlmodel->query("select * from user2");
			foreach($userlist  as $one){
				$data = array();
				$data['PHONE'] = $one['MOBIL_NO'];
				
				if($one['DEPT_ID']==0 ){
					$data['ISVALID'] = 0;
				}
				else {
					$data['ISVALID'] = -1;
					$data['DEPTID'] = $one['DEPT_ID'];
				}
				$res = D('Erp_users')->where("USERNAME = '".$one['USER_ID']."'")->save($data);
				if($res){
					Mylog::write(' �ֻ�������³ɹ�! '.$one['USER_ID'] );
				}else{
					Mylog::write(' �ֻ��������ʧ��! '.$one['USER_ID'],'error');
				}
			}

		}
		//���벿��
		function import_dept(){
			$deplist = $this->mysqlmodel->query("select * from department");
			foreach($deplist  as $one){
				$ress = M('Erp_dept')->where('ID='.$one['DEPT_ID'])->find();
				$data['DEPTNAME'] = $one['DEPT_NAME'];
				$data['PARENTID'] = $one['DEPT_PARENT'];
				if($ress){
					$re = D('Erp_dept')->where("ID = '".$one['DEPT_ID']."'")->save($data);
				}else{
					$data['ID'] = $one['DEPT_ID'];
					$data['ISVALID'] = -1;
					$re = D('Erp_dept')->add($data);
				}
				if($re){
					Mylog::write(' ���Ÿ��³ɹ�! '.$one['DEPT_ID'] );
				}else{
					Mylog::write(' ���Ÿ���ʧ��! '.$one['DEPT_ID'],'error');
				}
			}

		}
		//���벿��
		function import_dept2(){
			$deplist = $this->mysqlmodel->query("select * from department");
			foreach($deplist  as $one){
				 
				$data['DEPTNAME'] = $one['DEPT_NAME'];
				$data['PARENTID'] = $one['DEPT_PARENT'];
				 
					$data['ID'] = $one['DEPT_ID'];
					$data['ISVALID'] = -1;
					$re = D('Erp_dept')->add($data);
			 
				if($re){
					Mylog::write(' ���Ų���ɹ�! '.$one['DEPT_ID'] );
				}else{
					Mylog::write(' ���Ų���ʧ��! '.$one['DEPT_ID'],'error');
				}
			}

		}

		function getEmtpyRe(){
			//$memberlist = $this->mysqlmodel->query("select * from erp_member where city in (".$this->citys.") and isdel=0  and cardstatus=4 "); 

			$aa=0;
			$memberlist = M('Erp_cardmember')->where("cardstatus=4 and city_id=6 and pay_type=4")->select();
			foreach($memberlist as $one){
				$res = M('Erp_member_refund_detail')->where("MID=".$one['ID'])->find();
				if(!$res) {echo $one['ID'].'-'.$one['MOBILENO'].'-'.$one['REALNAME'].'<BR>';
				$aa++;}
			}
			echo $aa;
		}
		function getEmtpyRe2(){
			//$memberlist = $this->mysqlmodel->query("select * from erp_member where city in (".$this->citys.") and isdel=0  and cardstatus=4 "); 

			$aa=0;
			$memberlist =  M()->query("select tlf_member_id  as ID from ERP_CARDMEMBER a left join ERP_MEMBER_PAYMENT b on a.id=b.mid where  a.cardstatus=4 and b.refund_money=0 and a.PAY_TYPE=4 group by tlf_member_id");
			foreach($memberlist as $one){
				 echo $one['ID'].',';
			}
			 
		}
		function getEmtpyRe3(){
			$memberlist = $this->mysqlmodel->query("select * from erp_member where city in (".$this->citys.") and isdel=0  and cardstatus=4 and unpaidmoney<>total_price "); 

			$aa=0;
			//$memberlist = M('Erp_cardmember')->where("cardstatus=4 and city_id=6 and pay_type=4")->select();
			foreach($memberlist as $one){
				$res = $this->mysqlmodel->query("select * from erp_member_newrefund where mid=".$one['id']);
				
				if(!$res) {
					//$res = $this->mysqlmodel->query("select * from erp_member_refund where mid=".$one['id']);
					
					if(!$res) {
						echo $one['id'].'-'.$one['mobileno'].'-'.$one['realname'].'<BR>';
						$aa++;
					}
				}
			}
			echo $aa;
		}
		//�ۺϸ�����ϸ ���Ѹ���һ��
		function getEmtpyRe4(){
			$memberlist = $this->mysqlmodel->query("select * from erp_member where city in (".$this->citys.") and isdel=0 and paytype=4 ");  

			$aa=0;
			//$memberlist = M('Erp_cardmember')->where("cardstatus=4 and city_id=6 and pay_type=4")->select();
			foreach($memberlist as $v){
				$payinfo = unserialize($v['payinfo']);
				$money = 0; 
				foreach($payinfo as $key=>$one){
					$money += $one['trademoney'];
				}
				if($money!= $v['paidmoney']+$v['unpaidmoney']){
					//echo $v['id'].'-'.$v['mobileno'].'-money:'.$money.'-'.$v['paidmoney'].'-'.$v['unpaidmoney'].'<br>';
					echo $v['mobileno'].',';
					$aa++;
				}

			}
			
			echo $aa;
		}
		//���ۺϸ��� ���˿��¼����1�������
		function getEmtpyRe5(){
			$memberlist = $this->mysqlmodel->query("select * from erp_member where city in (".$this->citys.") and isdel=0  and cardstatus=4  and paytype<>4 "); 

			$aa=0;
			//$memberlist = M('Erp_cardmember')->where("cardstatus=4 and city_id=6 and pay_type=4")->select();
			foreach($memberlist as $one){
				$res = $this->mysqlmodel->query("select count(*) as countt from erp_member_newrefund where refundstatus=10 and mid=".$one['id']);
				
				if($res[0]['countt']>1){
					echo $one['id'].'-'.$one['mobileno'].'-'.$res[0]['countt'].'<br>';
					$aa++;
				}
				 
			}
			echo $aa;
		}
		//��Ա֮�� ��֧��paidmoney δ֧��unpaidmoney  �ֶ�ֵδ����Ӧ���������� ����Ӱ��֧����ϸ���ļ���
		function getEmtpyRe6(){
			$memberlist = $this->mysqlmodel->query("select * from erp_member where city in (".$this->citys.") and isdel=0  and cardstatus=4    "); 

			$aa=0;
			//$memberlist = M('Erp_cardmember')->where("cardstatus=4 and city_id=6 and pay_type=4")->select();
			foreach($memberlist as $one){
				$res = $this->mysqlmodel->query("select count(*) as countt  from erp_member_newrefund where refundstatus=10 and mid=".$one['id']);
				if($res[0]['countt']) {
					//if($res[0]['money']!=$one['unpaidmoney']){
					if( $one['paidmoney']!=0){
						 echo $one['id'].'-'.$one['mobileno'].'-'.$res[0]['money'].'-'.$one['unpaidmoney'].'-'.$one['paidmoney'].'-'.$one['total_price'].'<br>';
						//echo $one['mobileno'].',';
						$aa++;
					}
				}
				 
			}
			echo $aa;
		}
		//���㽻�׽������շѱ�׼�����
		function getEmtpyRe7(){
			$memberlist = $this->mysqlmodel->query("select * from erp_member where city in (".$this->citys.") and isdel=0    "); 

			$aa=0;
			//$memberlist = M('Erp_cardmember')->where("cardstatus=4 and city_id=6 and pay_type=4")->select();
			foreach($memberlist as $one){
				$res = $this->mysqlmodel->query("select sum(rmoney) as money  from erp_member_newrefund where refundstatus=10 and mid=".$one['id']);
				if($res[0]['money']) {
					$trademoney = $one['total_price']-$one['unpaidmoney']+$res[0]['money'];
					if($trademoney >$one['total_price'] ){
						echo $one['id'].'-'.$one['mobileno'].'-'.$trademoney.'-'.$res[0]['money'].'-'.$one['unpaidmoney'].'-'.$one['paidmoney'].'-'.$one['total_price'].'<br>';
						//echo $one['mobileno'].',';
						$aa++;
					}
				}
				 
			}
			echo $aa;
		}
		//���˿�״̬��Ա ���˿��¼
		function getEmtpyRe8(){
			$memberlist = $this->mysqlmodel->query("select * from erp_member where city in (".$this->citys.") and isdel=0 and cardstatus<>4   "); 

			$aa=0;
			//$memberlist = M('Erp_cardmember')->where("cardstatus=4 and city_id=6 and pay_type=4")->select();
			foreach($memberlist as $one){
				$res = $this->mysqlmodel->query("select count(*) as countt  from erp_member_newrefund where refundstatus=10 and mid=".$one['id']);
				if($res[0]['countt']>1) {
					echo  $one['id'].'-'.$one['mobileno'].','; 
				}
				 
			}
			echo $aa;
		}

    }
?>