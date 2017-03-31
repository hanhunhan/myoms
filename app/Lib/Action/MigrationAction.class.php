<?php
	//导入顺序 user_data  project_data member_limit_data
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
			$migration = D('Migration');//导入方法model
			foreach($projectlist as $v){
				//可加入判断是否已经导入 ??
				//$v['submit_userid'] = 'chuzhouadmin';
				 
				Mylog::write( "项目(".$v['id'].") 开始导入>>");
				if( M('Erp_project')->where("STATUS<>2 AND TLF_PROJECT_ID=".$v['id'])->find() ){
					Mylog::write( "项目(".$v['id'].") 已存在，不进行导入操作！");
					continue;
				}
				
				$this->model->startTrans();
				$pdata = $cdata = $bdata = $hdata = array();
				$pdata['CONTRACT'] = $v['contract_num'];
				$pdata['CITY_ID'] = $v['city'];
				$pdata['PROJECTNAME'] = $v['pro_name'];
				$pdata['CUSER'] = $migration->get_users_id($v['submit_userid']);//申请人id
				$pdata['ETIME'] = date('Y-m-d H:i:s',$v['exec_sdate']);
				$pdata['PSTATUS'] = $migration->get_project_status($v['state'],$v['exec_sdate'],$v['exec_edate']);//状态
				$pdata['BSTATUS'] = $migration->get_project_bstatus($v['state'],$v['exec_sdate'],$v['exec_edate']);// 电商状态
				$pdata['COMPANY'] = $v['dev_ent'];
				$pdata['TLF_PROJECT_ID'] = $v['id'];
				$pdata['STATUS'] = -1;
				
				$pres = M('Erp_project')->add($pdata);//返回项目id
				if($pres){
					 
					Mylog::write(' Erp_project表记录插入成功,ID：'.$pres.'原ID:'.$pdata['TLF_PROJECT_ID']);
					 
				}else{
					$this->model->rollback(); 
					Mylog::write(' Erp_project表记录插入失败! 原ID:'.$pdata['TLF_PROJECT_ID'],'error');
					continue;
				}
				
				$cdata['SCALETYPE'] = 1;
				$cdata['CTIME'] = date('Y-m-d H:i:s',$v['submit_date']);
				$cdata['CUSER'] = $pdata['CUSER'];
				$cdata['PROJECT_ID'] = $pres;//所属项目id
				$cdata['FSTATUS'] = $pdata['BSTATUS'];// 状态
				$cres = M('Erp_case')->add($cdata);//返回业务id
				if($cres){
					 
					Mylog::write( ' Erp_case 案例表记录插入成功,ID：'.$cres);
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_case 案例表 记录插入失败! ','error');
					continue;
				}
				//插入决算  终止 记录
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
					$temp['STATUS'] = 2;//通过的
					$flcres = M('Erp_finalaccounts')->add($temp);
					if($flcres){
					 
						Mylog::write( 'Erp_finalaccounts 项目决算终止记录插入成功,ID：'.$flcres);
					}else{
						$this->model->rollback(); 
						 
						Mylog::write('Erp_finalaccounts 项目决算终止记录插入失败! ','error');
						continue;
					}
				}
				//插入项目记录表
				$temp = array();
				$temp['PROJECT_ID'] = $pres;//所属项目id
				$temp['USER_ID'] = $pdata['CUSER'];
				$temp['CTIME'] = date('Y-m-d H:i:s',$v['exec_sdate']);//开始时间
				$project_log_id = M('Erp_project_log')->add($temp);
				if($project_log_id){
					 
					Mylog::write( 'Erp_project_log 项目log记录插入成功,ID：'.$project_log_id);
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_project_log 项目log记录插入失败! ','error');
					continue;
				}
				//插入项目权限表
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
					$temp['USE_ID'] = $migration->get_users_id($prone );//有权限的人
					$temp['PRO_ID'] = $pres;//所属项目id
					$temp['ISVALID'] = -1;
					$temp['ERP_ID'] = 1;
					$prorole_res = M('Erp_prorole')->add($temp);
				}
				if($prorole_res){
					 
					Mylog::write( 'Erp_prorole 项目权限表记录插入成功 ' );
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_prorole 项目权限表记录插入失败! ','error');
					continue;
				} 
				//
			 
				$bdata = $this->prjbudget_arr($v,$cres);// 预算表的信息

				$bres = M('Erp_prjbudget')->add($bdata);//返回预算id
				if($bres){
					 
					Mylog::write(' Erp_prjbudget 预算表记录插入成功,ID：'.$bres);
					 
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_prjbudget 预算表 记录插入失败! ','error');
					continue;
				}

				$v['CUSER'] = $migration->get_users_id($v['submit_userid'] ); 
				$hdata = $this->house_arr($v,$pres);//house表信息
				$hres = M('Erp_house')->add($hdata);//返回楼盘记录id
				if($hres){
					 
					Mylog::write(' Erp_house 楼盘表记录插入成功,ID：'.$hres); 
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('Erp_house 楼盘记录插入失败! ','error');
					continue;
				}
				/*if($v['tag_products'] ){ //添加 关联产品
					$tagparr = explode(',',$v['tag_products']);
					$rpres = true;
					$tagp_arr = array();
					$list = M('Erp_products_type')->select();
					foreach($list as $key =>$value){
						$temp = array();
						$temp['ISVAILD'] = in_array($value['ID'],$tagparr) ? '-1' : '0';
						$temp['HOUSE_ID'] = $hres;
						$temp['CHANGPINID'] = $value['ID'];
						$rpres = M('Erp_relatedproducts')->add($temp); //返回关联产品关系id
						$tagp_arr[$value['ID']] = $rpres;
						if(!$rpres) break;
					}
					if($rpres){
							 
							Mylog::write(' Erp_relatedproducts 关联产品表记录插入成功 '); 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_relatedproducts 关联产品记录插入失败! ','error');
							continue;
					}
				}*/
				if($v['sales_target']){
					$sales_target = unserialize($v['sales_target']);
					
					$bgres = true;
					$bgres_arr = array();
					foreach($sales_target as $key=>$one){
						 
						$temp = $this->sales_target_arr($one,$bres,$key,$pres);
						$bgres = M('Erp_budgetsale')->add($temp);//返回目标分解id
						$bgres_arr[$key] = $bgres;
						if(!$bgres) break;
					}
					if($bgres){
							 
							Mylog::write(' Erp_budgetsale 目标分解 记录插入成功 '); 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_budgetsale 目标分解记录插入失败! ','error');
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
							$bgfres = M('Erp_budgetfee')->add($temp);//返回线下费用id
							$offline_fee_arr[$key] =  $bgfres; 
							if(!$bgfres) break;
						}
					}
					if($bgfres){
							 
							Mylog::write(' Erp_budgetfee 线下费用 记录插入成功 '); 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write( ' Erp_budgetfee 线下费用记录插入失败! ','error');
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
							$bgfres = M('Erp_budgetfee')->add($temp);//返回线下费用id
							$online_fee_arr[$key] = $bgfres;
							if(!$bgfres) break;
						}
					}
					if($bgfres){
							 
							Mylog::write(' Erp_budgetfee 线上费用 记录插入成功 '); 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_budgetfee 线上费用记录插入失败! ','error');
							continue;
					}
				}
				if($v['house_price']){
					$house_price = explode(',',$v['house_price']);
					
					$fsres = true;
					foreach($house_price as $one){
						$temp = array();
						$temp['PRJ_ID'] = $bres;//预算ID
						$temp['SCALETYPE'] = 1;
						$temp['AMOUNT'] = $one;
						$temp['SCALE'] = $one;//是否
						$temp['CASE_ID'] = $cres; //案例id
						$temp['ISVALID'] = -1; // 
						$temp['PAYDATE'] = date('Y-m-d H:i:s',$v['submit_date']);
						$fsres = M('Erp_feescale')->add($temp);
						if(!$fsres) break;

					}
					if($fsres){
							 
							Mylog::write(' Erp_feescale 费用标准 记录插入成功'); 
					}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_feescale 费用标准 记录插入失败! ','error');
							continue;
					}


				}
				if($v['state']==1){
					$this->project_flow_data($v,$pres,$cres,$migration);//导入立项流程

				}
				//$changelist = $this->mysqlmodel->query("select * from erp_project where old_id='".$v['id']."'  and state=1 ");//变更记录
				if($changelist){
					Mylog::write( "有新版本审核中！");
					$flow_first = $this->getFlowStep($changelist[0]['id']);//第一个
					$flow_last = $this->getFlowStep($changelist[0]['id'],'DESC');//当前
					$changevid = $this->createChangeRecordVersion($pres,date('Y-m-d H:i:s',$flow_first['deal_time']),$migration->get_users_id($flow_first['deal_userid']),$migration->get_users_id($flow_last['deal_userid']),1,1);
					if($changevid){
						$bdata_change = $this->prjbudget_arr($changelist[0],$cres);// 预算表的信息
						$changer = new Changerecord();
						$changer->fields=$this->getAllCols($bdata);
						$bdata = $this->addChangeversion($bdata,$bdata_change);
						$optt['TABLE'] = 'ERP_PRJBUDGET';
						$optt['BID'] = $bres ;//预算表当前记录id  
						$optt['CID'] = $changevid ;//变更版本id
						$optt['CDATE'] = date('Y-m-d H:i:s');
						$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
						$optt['ISNEW'] = 0;//新增-1 或 修改0
						$changeres =  $changer->saveRecords($optt,$bdata);
						if($changeres )
							Mylog::write( 'ERP_PRJBUDGET变更记录插入成功 ' );
						else {
							$this->model->rollback(); 
							Mylog::write(' ERP_PRJBUDGET变更记录插入失败!  ','error');
							continue;
						}

						$hdata_change = $this->house_arr($changelist[0],$pres);// house表的信息
						$changer->fields=$this->getAllCols($hdata);
						$hdata = $this->addChangeversion($hdata,$hdata_change);
						$optt['TABLE'] = 'ERP_HOUSE';
						$optt['BID'] = $hres ;//house当前记录id  
						$optt['CID'] = $changevid ;//变更版本id
						$optt['CDATE'] = date('Y-m-d H:i:s');
						$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
						$optt['ISNEW'] = 0;//新增-1 或 修改0
						$changeres =  $changer->saveRecords($optt,$hdata);
						if($changeres )
							Mylog::write( 'ERP_HOUSE变更记录插入成功 ' );
						else {
							$this->model->rollback(); 
							Mylog::write(' ERP_HOUSE变更记录插入失败!  ','error');
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
								$optt['BID'] = $bgres_arr[$key] ;//ERP_BUDGETSALE当前记录id  
								$optt['CID'] = $changevid ;//变更版本id
								$optt['CDATE'] = date('Y-m-d H:i:s');
								$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
								$optt['ISNEW'] = 0;//新增-1 或 修改0
								$bgres =  $changer->saveRecords($optt,$temp);
								if($bgres )
									Mylog::write( 'ERP_BUDGETSALE 变更记录插入成功 key:'.$key );
								else {
									$this->model->rollback(); 
									Mylog::write(' ERP_BUDGETSALE变更记录插入失败!  ','error');
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
									$optt['BID'] = $offline_fee_arr[$key] ;//ERP_BUDGETSALE当前记录id  
									$optt['CID'] = $changevid ;//变更版本id
									$optt['CDATE'] = date('Y-m-d H:i:s');
									$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
									$optt['ISNEW'] = 0;//新增-1 或 修改0
									$bgfres =  $changer->saveRecords($optt,$temp);
									if($bgfres )
										Mylog::write( 'ERP_BUDGETFEE  offline_cost 变更记录插入成功 key:'.$key );
									else {
										$this->model->rollback(); 
										Mylog::write(' ERP_BUDGETFEE offline_cost 变更记录插入失败!  ','error');
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
									$optt['BID'] = $online_fee_arr[$key] ;//ERP_BUDGETSALE当前记录id  
									$optt['CID'] = $changevid ;//变更版本id
									$optt['CDATE'] = date('Y-m-d H:i:s');
									$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
									$optt['ISNEW'] = 0;//新增-1 或 修改0
									$bgfres =  $changer->saveRecords($optt,$temp);
									if($bgfres )
										Mylog::write( 'ERP_BUDGETFEE online_cost 变更记录插入成功 key:'.$key );
									else {
										$this->model->rollback(); 
										Mylog::write(' ERP_BUDGETFEE online_cost 变更记录插入失败!  ','error');
										break;
									} 
								}
							}
							if(!$bgres )continue; 

						}

						/*if($v['tag_products'] ){ //添加 关联产品
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
									$optt['BID'] = $tagp_arr[$value['ID']] ;//ERP_BUDGETSALE当前记录id  
									$optt['CID'] = $changevid ;//变更版本id
									$optt['CDATE'] = date('Y-m-d H:i:s');
									$optt['APPLICANT'] = $migration->get_users_id($flow_first['deal_userid']);
									$optt['ISNEW'] = 0;//新增-1 或 修改0
									$bgfres =  $changer->saveRecords($optt,$temp);
									if($bgfres )
										Mylog::write('Erp_relatedproducts 变更记录插入成功 key:'.$key );
									else {
										$this->model->rollback(); 
										Mylog::write('Erp_relatedproducts 变更记录插入失败! ','error');
										break;
									} 
									 
									
							}
							 
							 
						}*/

						//$this->project_flow_data($changelist[0],$pres,$cres,$migration,7);//导入变更流程



					}else{
						$this->model->rollback(); 
						Mylog::write(' 版本号插入失败!  ','error');
						continue;
					}
					
				}
				$reimdata = $this->reimbursement_data($pres,$cres,$migration,$v);
				if(!$reimdata){
					$this->model->rollback(); 
					Mylog::write(' 报销信息导入失败!  ','error');
					continue;
				}
				
				Mylog::write(" <<项目(".$v['id'].") 导入成功 ");
				$this->model->commit(); 

			}



		}
		//获取数组字段
		public function getAllCols($arr){
			foreach($arr as $key=>$v){
				$temp[] = $key;
			}
			//$str = implode(',',$temp);
			return $temp;
		}
		//添加变更版本记录
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
		//获取流程信息
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
			$bdata['AVERAGESETS'] = $v['three_house_num'];//月均去化套数
			$bdata['FIRSTSETS'] = $v['last_house_num'];;//首次去化套数
			$bdata['FEE'] = $v['house_price'];//单套收费标准
			$bdata['SUMPROFIT'] = $v['estimate_total'];//预估总收益
			$bdata['OFFLINE_COST_SUM_PROFIT'] = $v['estimate_total']-$v['offline_cost_sum'];//付现利润率
			$bdata['OFFLINE_COST_SUM_PROFIT_RATE'] = round(($v['estimate_total']-$v['offline_cost_sum'])/$v['estimate_total']*100,2);//付现利润
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
				$hdata['REL_PROPERTY'] = $v['rel_property'];//关联楼盘
				$hdata['PRO_BLOCK_ID'] = $v['pro_block_id'];//区属id
				$hdata['PRO_LISTID'] = $v['pro_listid'];//楼盘id
				$hdata['REL_NEWHOUSEID'] = $v['rel_newhouseid'];//关联id
				$hdata['DEV_ENT'] = $v['dev_ent'];
				$hdata['PROPERTY_CLASS'] = $v['property_class'];
				$hdata['PRO_ADDR'] = $v['pro_addr'];
				$hdata['PRO_NAME'] = $v['pro_name'];
				$hdata['TLF_SOURCE'] = $v['tlf_source'] ? $v['tlf_source']: ' ';
				$hdata['TLF_DISCOUNT'] = $v['tlf_discount'] ? $v['tlf_discount'] : ' ';
				$hdata['SALEPERMIT'] = $s_arr[$v['salepermit']];//许可证
				$hdata['ISFUNDPOOL'] = $isfundpool_arr[$v['isfundpool']];
				$hdata['SP_EXP'] = $v['sp_exp'];
				$hdata['FPSCALE'] = $v['fpscale'];
				$hdata['RETURN_CONTENT'] = $v['return_content'] ? $v['return_content'] : ' ';
				$hdata['PRO_ADV'] = $v['pro_adv'] ? $v['pro_adv'] :' ';
				$hdata['PRO_INF'] = $v['pro_inf'] ? $v['pro_inf'] : ' ';
				$hdata['PRO_INFO'] = $v['pro_info'];
				$hdata['STATE'] = $v['state'];//*是否保留
				$hdata['APPLY_MODIFY'] = $v['apply_modify'];//*
				$hdata['FINAL_ACCOUNTS'] = $v['final_accounts'];//*
				$hdata['ISRECORD'] = $v['isrecord'];//*
				$hdata['SUBMIT_DATE'] = date('Y-m-d H:i:s',$v['submit_date']);
				$hdata['WORK_STATE'] = $v['work_state'];//*
				$hdata['PROJECT_ID'] = $pres;
				$hdata['ONLINE_AD_SCH'] = '';// 广告排期附近
				$hdata['CONTRACT_FILE'] = '';//项目合同及相关附件
				$hdata['MONEY_BET'] =  $v['money_bet'];//资金对赌
				$hdata['PROPERTIES'] = 1;//电商项目属性
				$hdata['CONDOMINIUM'] = 0;//是否开设公共账号
				$hdata['PAYMENT_SECURITY'] = $v['sp_exp'];//是否支付保证金
				$hdata['ISCONTRACT'] = -1;//合同是否收回
				if($v['tag_products'] ){ //添加 关联产品
					$tagparr = explode(',',$v['tag_products']);
					$hdata['USING_DECORATION_PRODUCT']  = in_array(1,$tagparr)?1:2; //是否使用装修产品(1=是，2=否）
					$hdata['USING_FINANCIAL_PRODUCT']  = in_array(2,$tagparr)?1:2;//是否使用金融产品（1=是，2=否）
				}
			return $hdata;
		}
		//sales_target 
		public function sales_target_arr($one,$bres,$key,$pres){
			$temp = array();
			$temp['BUDGETID'] = $bres;//预算ID
			$temp['SALEMETHODID'] = $key;//销售方式ID ?
			$temp['SETS'] = $one['estimate_house'];//成交套数
			$temp['CUSTOMERS'] = $one['estimate_guide'];//导客量
			$temp['PROJECTT_ID'] = $pres;
			$temp['ISVALID'] = -1;//状态
			return $temp;
		}
		//budgetfee
		public function budgetfee_arr($one,$bres,$v,$offline_cost){
			$temp = array();
			$temp['FEEID'] = $one['ID'];
			$temp['BUDGETID'] = $bres;//预算ID
			$temp['AMOUNT'] = $offline_cost[$one['INPUTNAME']];//费用金额 
			$temp['RATIO'] = round($offline_cost[$one['INPUTNAME']]/$v['offline_cost_sum'],4)*100;//费用比例
			$temp['REMARK'] = $offline_cost[$one['INPUTNAME'].'_info'];//说明
			//$temp['ADDTIME'] = ;//添加时间
			$temp['ISVALID'] = '-1';//是否有效
			if( in_array($one['ID'],array(98,99) ) ) {
				$temp['ISONLINE'] = '-1';//线上费用
			}
			return $temp;
		}
		//立项 变更 流程
		public function project_flow_data($v,$pres,$cres,$migration,$flowset=6){
			$flowlist = $this->mysqlmodel->query("select * from erp_workstep where pid='".$v['id']."' order by id asc");
			if($flowlist){
				$temp = array();
				$temp['FLOWSETID'] = $flowset;
				$temp['CASEID'] = $cres;
				$temp['MAXSTEP'] = $v['maxstep'];
				$temp['ADDTIME'] = date('Y-m-d H:i:s',$v['submit_date']);
				$temp['ADDUSER'] = $migration->get_users_id($flowlist[0]['deal_userid']);
				$temp['STATUS'] = 1;//正在进行中
				$temp['INFO'] = $flowlist[0]['deal_info'];
				$temp['CITY'] = $v['city'];
				$temp['RECORDID'] = $pres;
				$flowres = M('Erp_flows')->add($temp);
				Mylog::write(' Erp_flows 流程创建 记录插入成功! id: '.$flowres );
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
						Mylog::write(' Erp_flownode 流程节点创建 记录插入失败! ','error');
						 
						return false;

					}
							
				}
				Mylog::write(' Erp_flownode 流程创建 记录插入成功!  '  );
				return true;
			}else{
					Mylog::write(' Erp_flows 流程创建 记录插入失败! ','error');
					return false; 
			}
		}
		//管理用户导入
		public function user_data(){
			$userlist = $this->mysqlmodel->query("select * from erp_user where city in (".$this->citys.")"); // 已失效用户是否需要导入?
			 
			foreach($userlist as $one){
				
				$temp = array();
				if(!$user = M('Erp_users')->where("USERNAME='".$one['uid']."' ")->find() ){
					 
					Mylog::write("用户(".$one['uid'].") 开始导入>>  ");
					$this->model->startTrans();
					$temp['DEPTID'] = $one['did'];
					$temp['NAME'] = $one['username'];
					$temp['USERNAME'] = $one['uid'];
					$temp['PASSWORD'] = $one['password'];
					//$temp['TITLE']  ;职务 部门名称需不需要？
					$temp['ISVALID'] = $one['status']==1 ? -1:0;
					//$temp['GROUP'] =
					$temp['CITYS'] = $one['authcity'];
					$temp['CITY'] = $one['city'];
					$temp['ISPARTTIME'] = $one['parttime']==1 ? -1:0;

					$ures = M('Erp_users')->add($temp);
					if($ures){
						Mylog::write(' Erp_users 用户表 记录插入成功,ID：'.$ures );
							 
					}else{
						$this->model->rollback(); 
						 
						Mylog::write(' Erp_users 用户表 记录插入失败! ','error');
						continue;
					}
					 
					Mylog::write(" <<用户(".$one['uid'].") 导入成功 "); 
					$this->model->commit(); 
				}else{
					 
					Mylog::write( "用户(".$one['uid'].") 已存在 \n"); 
					//if(!$user['CITY'] || !$user['CITYS'] ){
						$this->model->startTrans();
						 
						
						if( $user['CITY']!=$one['city']){
							$temp['CITY'] = $one['city'];
							 
							Mylog::write("开始更新用户(".$one['uid'].") CITY字段 " );
						}
						if( $user['CITYS']!=$one['authcity']){
							$temp['CITYS'] = $one['authcity'];
							 
							Mylog::write("开始更新用户(".$one['uid'].") CITYS字段  " );
						}

						if(is_null($user['ISPARTTIME'])){
							$temp['ISPARTTIME'] = $one['parttime']==1 ? -1:0;
							 
							Mylog::write("开始更新用户(".$one['uid'].") ISPARTTIME字段  " );
						}
						if($user['DEPTID']!=$one['did']){
							$temp['DEPTID'] = $one['did'];
							 
							Mylog::write("开始更新用户(".$one['uid'].") DEPTID字段  " );
						}
						if(!empty($temp)){
							Mylog::write("用户(".$one['uid'].") 开始更新>>  " );
							$ures = M('Erp_users')->where("USERNAME='".$one['uid']."'")->save($temp);
							if($ures){
								 
								Mylog::write('更新该记录成功  ' );
									 
							}else{
								$this->model->rollback(); 
								 
								Mylog::write(' Erp_users 用户表 更新失败! 用户:'.$one['uid'],'error');
								continue;
							}
						 
							Mylog::write(" <<用户(".$one['uid'].") 更新成功 \n");
							
							$this->model->commit(); 
						}
					//}
					

				}

				
			}

		}
		//管理用户导入
		public function user_role(){
			$userlist = $this->omsomdel2->query("select * from ERP_USERS where CITY in($this->citys) "); // 已失效用户是否需要导入?
			  
			foreach($userlist as $one){
				
				$temp = array();
				if($user = M('Erp_users')->where("USERNAME='".$one['USERNAME']."'")->find() ){
					 
				 
					if($user['ROLEID'] != $one['ROLEID'] ){
						$this->model->startTrans();
						 
						Mylog::write("用户(".$one['uid'].") 开始更新>>  " );
						 
						$temp['ROLEID'] = $one['ROLEID'];
						$ures = M('Erp_users')->where("USERNAME='".$one['USERNAME']."'")->save($temp);
						if($ures){
							 
							Mylog::write('更新该记录成功  ' );
								 
						}else{
							$this->model->rollback(); 
							 
							Mylog::write(' Erp_users 用户表 更新失败! 用户:'.$one['uid'],'error');
							continue;
						}
					 
						Mylog::write(" <<用户(".$one['uid'].") 更新成功 \n");
						
						$this->model->commit(); 
					}else{
						echo $user['ROLEID'].$one['ROLEID'].'next<br>';
					}
					

				}

				
			}

		}

		//变更
		//会员导入
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
			$migration = D('Migration');//导入方法model
			
			foreach($memberlist as $v){
				
				$mdata = array();
				Mylog::write( "会员(".$v['id'].") 开始导入>>");
				if( M('Erp_cardmember')->where("  TLF_MEMBER_ID=".$v['id'])->find() ){
					Mylog::write( "会员(".$v['id'].") 已存在，不进行导入操作！");
					continue;
				}
				$this->model->startTrans();
				$mdata['CITY_ID'] = $v['city'];
				$mdata['PRJ_ID'] = $migration->get_project_id($v['prjid']);//获取项目id
				$mdata['PRJ_NAME'] = $migration->get_project_name($v['prjid']);//获取项目名称
				$mdata['CASE_ID'] = $migration->get_case_id($mdata['PRJ_ID']);//根据项目id获取caseid
				$mdata['REALNAME'] = $v['realname'];
				$mdata['MOBILENO'] = $v['mobileno'];//购房人手机
				$mdata['LOOKER_MOBILENO'] = $v['looker_mobileno'];//看房人手机
				$mdata['CERTIFICATE_TYPE'] = $v['certificate_type'];//证件类型
				$mdata['CERTIFICATE_NO'] = $v['idcardno'];//证件号
				$mdata['SOURCE'] = $v['source'];//来源
				if($v['subscribetime']) $mdata['SUBSCRIBETIME'] = date('Y-m-d H:i:s',$v['subscribetime']);// 认购时间 提交时间?
				$mdata['IS_TAKE'] = $v['istake'];//是否带看
				$mdata['ORDER_ID'] = $v['orderid'];//第三方订单编号
				$mdata['ROOMNO'] = $v['roomno'];//房号
				$mdata['SIGNEDSUITE'] = $v['signedsuite'];//签约套数
				if($v['signtime'])$mdata['SIGNTIME'] = date('Y-m-d H:i:s',$v['signtime']);//签约时间
				$mdata['CARDSTATUS'] = $migration->get_cardstatus($v['cardstatus']);//办卡状态********
				
				//针对已退卡未申请退款的状态做处理　　部分退款暂时排除在外
				/*if($mdata['CARDSTATUS']==4 && $v['paidmoney']>0){
					$mdata['CARDSTATUS']=3;//这种情况  状态改为已办已签约，以便在经管申请退款处理 
					Mylog::write( "会员(".$v['id'].") <特异性> 针对已退卡未申请退款的状态做处理　　部分退款暂时排除在外！");
				}
				*/
				if($v['cardtime'])$mdata['CARDTIME'] = date('Y-m-d H:i:s',$v['cardtime']);//办卡时间
				/*$operator = $migration->get_users_id($v['adduid']);//$migration->get_users_id_byname($v['operator']);
				if(count($operator)>1){
					Mylog::write(' operator 办卡操作人重名 ! 用户姓名:'.$v['operator'],'error');
				}elseif(count($operator)==1) $mdata['ADD_UID'] = $operator; 
				else Mylog::write(' operator 办卡操作人不存在 ! 用户姓名:'.$v['operator'],'error');*/
				$operator = $mdata['ADD_UID']  = $migration->get_city_adduid($mdata['CITY_ID']);

				$mdata['PAY_TYPE'] = $v['paytype'];//付款方式
				$mdata['PAID_MONEY'] = (float)$v['paidmoney'];//已付金额
				$mdata['UNPAID_MONEY'] = (float)$v['unpaidmoney'];//未付金额
				//$mdata['REDUCE_MONEY'] =  ;减免金额
				$mdata['INVOICE_STATUS'] = $migration->get_invoice_status($v['invoicestatus']);//发票状态******
				$mdata['INVOICE_NO'] = $v['invoice_no'];//发票编号
				$mdata['RECEIPTSTATUS'] = $migration->get_receipt_status($v['receiptstatus']);//收据状态******
				$mdata['RECEIPTNO'] = $v['receiptno'];//收据编号
				if($v['confirmtime'])$mdata['CONFIRMTIME'] = date('Y-m-d H:i:s',$v['confirmtime']);//财务确认时间/开票时间
				//$mdata['CONFIRM_UID'] = //财务确认用户
				$mdata['IS_SMS'] = $v['smssign'];//是否发送短信
				 //退款时间 退卡时间
				
				/*$backoperator = $migration->get_users_id_byname($v['backoperator']);
				if(count($backoperator)>1){
					Mylog::write(' backoperator 退卡操作人重名 ! 用户姓名:'.$v['backoperator'].'选择使用 '.$operator);
					$mdata['BACK_UID'] = $operator;
				}elseif(count($backoperator)==1) $mdata['BACK_UID'] = $backoperator[0]['ID'];
				else {
					$mdata['BACK_UID'] = $operator;
					Mylog::write(' backoperator 退卡操作人不存在 ! 用户姓名:'.$v['backoperator'].'选择使用 '.$operator);
				}*/
				if($mdata['CARDSTATUS']==4 ){
					$backoperator =  $mdata['BACK_UID'] = $operator;
					if($v['backtime'])$mdata['BACKTIME'] = date('Y-m-d H:i:s',$v['backtime']);//退卡时间
				}

				$mdata['TOTAL_PRICE'] = $v['total_price'];//总价 
				//$mdata['AGENCY_REWARD'] = $v['total_price'];中介佣金
				$mdata['HOUSEAREA'] = $v['housearea'];
				$mdata['HOUSETOTAL'] = $v['housetotal'];
				$mdata['FGJ_SOURCE_DIFF'] = $v['fgj_source_diff'];//房管家状态是否相同
				$mdata['NOTE'] = $v['NOTE'];//备注
				if($v['createtime'])$mdata['CREATETIME'] = date('Y-m-d H:i:s',$v['createtime']);//创建时间
				if($v['updatetime'])$mdata['UPDATETIME'] = date('Y-m-d H:i:s',$v['updatetime']);//更新时间
				//$mdata['MERCHANT_ID'] = $v['merchantnumber'];//商户编号
				$mdata['FINANCIALCONFIRM'] = $migration->get_financialconfirm($v['financialconfirm']);//财务确认状态 ******
				$mdata['TLF_MEMBER_ID'] = $v['id'];//原团立方id

				if($v['leadtime'])$mdata['LEAD_TIME'] = date('Y-m-d H:i:s',$v['leadtime']);//交付时间
				$mdata['DECORATION_STANDARD'] = $v['decstandard']; //毛坯精装 
				$mres = M('Erp_cardmember')->add($mdata);//返回会员id
				if($mres){
					 
					Mylog::write('ERP_MEMBER 会员表记录插入成功,ID：'.$mres);
				}else{
					$this->model->rollback(); 
					 
					Mylog::write('ERP_MEMBER 会员 记录插入失败! ID：'.$mdata['TLF_MEMBER_ID'],'error');
					continue;
				}
				$financialconfirm = $migration->get_payment_financialconfirm($v['financialconfirm']);
				if($v['tradetime']) $tradetime = date('Y-m-d H:i:s',$v['tradetime']);
				else $tradetime = date('Y-m-d H:i:s',$v['cardtime']);
				$rmoney = $this->get_refundmoney($v['id'],$v);//该会员退款金额
				$trademoney = $v['total_price'] - $v['unpaidmoney'] + $rmoney;//初始交易金额
				if($trademoney> $v['total_price']) $trademoney = (float)$v['total_price'];//特殊处理 
				//if(count($payinfo) ){//综合付款方式
				$invoicemoney =  $trademoney;
				if($v['paytype']==4){//？	
					Mylog::write( ' 此会员为综合付款方式 ');
					$payinfo = unserialize($v['payinfo']);
					$temp = array();

					//退款状态
					$refundstatus = 0;
					if($mdata['CARDSTATUS']==4 ){//退卡状态 
						$refundstatus = 0;//1;
					}
					foreach($payinfo as $key=>$one){
						$temp = array();
						$pres = $ires1=$ires2= $rres =true;
						$temp['MID'] = $mres;//会员编号
						$temp['PAY_TYPE'] = $one['paytype'];//交易方式
						$temp['TRADE_MONEY'] = (float)$one['trademoney'];//交易金额
						$temp['ORIGINAL_MONEY'] = (float)$one['trademoney'];//原始金额
						$temp['RETRIEVAL'] = $one['retrieval'];//六位检索号
						$temp['CVV2'] = $one['cvv2'];//卡号后四位
						if($one['tradetime'])$temp['TRADE_TIME'] = date('Y-m-d H:i:s',$one['tradetime']);
						else  $temp['TRADE_TIME'] = $tradetime;
						$temp['STATUS'] = $financialconfirm;//财务状态付款明细
						$temp['REFUND_STATUS'] = $refundstatus;//退款状态 ??
						if($mdata['CARDSTATUS']==4)$temp['REFUND_MONEY'] = $one['refundmoney']?$one['refundmoney']:$temp['TRADE_MONEY'];//退款金额
						else {
							 $refundone= $this->mysqlmodel->query("select * from erp_member_newrefund where refundstatus=10 and  mid='".$mdata['TLF_MEMBER_ID'] ."'  and payid=$key "); 
							//var_dump($refundone);
							$temp['REFUND_MONEY'] = $refundone ? $refundone[0]['rmoney'] : 0;
						}
						$temp['ADD_UID'] = $mdata['ADD_UID'];//添加人 操作人
						$temp['MERCHANT_NUMBER'] = $one['merchantnumber'];	

						$pres = M('Erp_member_payment')->add($temp);//返回支付编号
						if(!$pres) {
							Mylog::write( ' Erp_member_payment 支付明细记录插入失败1 会员编号:'.$mres ,'error');  
							break; 
						}else  Mylog::write( ' Erp_member_payment 支付明细记录插入成功,ID：'.$pres);

						///////
						// 收益明细  电商会员支付
						$ires1 = $this->income_info($mdata['CASE_ID'],$mres,$pres,1,$temp['TRADE_MONEY'],$operator,$mdata['CARDTIME'],'电商会员支付');
						if(!$ires1 ){ 
								Mylog::write( ' 电商会员支付插入失败 会员编号:'.$mres ,'error');  
					  
								break;
						}
						if($temp['STATUS']==1){// || $rmoney >0
							// 收益明细  确认电商会员收入
							$ires2 = $this->income_info($mdata['CASE_ID'],$mres,$pres,2,$temp['TRADE_MONEY'],$operator,$mdata['CARDTIME'],'确认电商会员收入') ;
							if(!$ires2){ 
									Mylog::write( ' 确认电商会员收入插入失败 会员编号:'.$mres,'error' ); 
						  
									break;
							}
						}

						if($rmoney && $pres ){// 退款
							$where = " and payid = $key ";
							$rres=$this->refund_list_detail($mdata['CITY_ID'],$migration,$mdata['CASE_ID'],$v['id'],$mres,$pres,$temp['ADD_UID'],$where,$v['backtime'],$v['invoicestatus'],$temp['TRADE_MONEY'],$temp);
							if(!$rres ){//退款单及明细
								 
								Mylog::write( ' 退款单及明细插入失败 会员编号:'.$mres,'error' );
								break;
							}
						}
						 
					}
					if($payinfo){
						if($pres && $ires1 && $ires2 && $rres ){
								Mylog::write( ' Erp_member_payment  支付明细记录全部插入成功 ' );
						}else{
								$this->model->rollback(); 
						 
								Mylog::write(' Erp_member_payment 支付明细记录插入失败2! ','error');
								continue;
						}
					}
					else
					{
						Mylog::write( $mres . '<特异性> payinfo 为空 ' );						
					}
				}else{//非综合付款方式
					$refundstatus = $refundmoney = 0;
					Mylog::write( ' 此会员付款方式：'.$v['paytype']);
					//$trademoney = $v['trademoney'] ? $v['trademoney']: ($v['refundmoney']? 0 :$v['paidmoney']);
					//$rmoney = $this->get_refundmoney($v['id']);//该会员退款金额
					//$trademoney = $v['total_price'] - $v['unpaidmoney'] + $rmoney;
					if($mdata['CARDSTATUS']==4 ){//退卡状态 
						//$trademoney = $refundmoney =  $v['unpaidmoney'];//部分退款的情况暂不考虑
						$trademoney = $rmoney;
						//$refundstatus = 1;

					}
					
					 
					if($trademoney){
						$temp = array();
						$temp['MID'] = $mres;//会员编号
						$temp['PAY_TYPE'] = $v['paytype'];//交易方式
						if($mdata['CARDSTATUS']!=4 &&  $rmoney){
							$temp['TRADE_MONEY'] = $trademoney  ;//交易金额
							$invoicemoney = $trademoney-$rmoney;
							
						}else{
							$temp['TRADE_MONEY'] = $trademoney ;//交易金额
							$invoicemoney = $trademoney;
						}
						$temp['ORIGINAL_MONEY'] = $trademoney ;//原始金额
						$temp['RETRIEVAL'] = $v['retrieval'];//六位检索号
						$temp['CVV2'] = $v['cvv2'];//卡号后四位
						$temp['TRADE_TIME'] = $tradetime;
						 
						$temp['STATUS'] = $financialconfirm;//财务状态
						$temp['REFUND_STATUS'] = $refundstatus;//退款状态 ??
						$temp['REFUND_MONEY'] = $rmoney;//退款金额
						$temp['ADD_UID'] = $mdata['ADD_UID'];//添加人 操作人
						$temp['MERCHANT_NUMBER'] = $v['merchantnumber'];	
						$pres = M('Erp_member_payment')->add($temp);//返回支付编号
						if($pres){
							Mylog::write( ' Erp_member_payment 支付明细记录插入成功,ID：'.$pres);
						}else{
							$this->model->rollback(); 
					 
							Mylog::write(' Erp_member_payment 支付明细记录插入失败! 会员编号:'.$mres,'error');
							continue;
						}

							///////
						// 收益明细 电商会员支付
						$ires = $this->income_info($mdata['CASE_ID'],$mres,$pres,1,$temp['TRADE_MONEY'],$operator,$mdata['CARDTIME'],'电商会员支付');
						if(!$ires ){ 
								$this->model->rollback(); 
						  
								continue;
						}
						if($temp['STATUS']==1){// || $rmoney >0
							// 收益明细  确认电商会员收入
							$ires2 = $this->income_info($mdata['CASE_ID'],$mres,$pres,2,$temp['TRADE_MONEY'],$operator,$mdata['CARDTIME'],'确认电商会员收入');
							if(!$ires2 ){ 
									$this->model->rollback(); 
							  
									continue;
							}
						}
 


						if( $mdata['CARDSTATUS']==4 || $v['paidmoney']<$v['total_price'] || ($mdata['CARDSTATUS']!=4 &&  $rmoney) ){// 退款 
							$rdres=$this->refund_list_detail($mdata['CITY_ID'],$migration,$mdata['CASE_ID'],$v['id'],$mres,$pres,$temp['ADD_UID'],null,$v['backtime'],$v['invoicestatus'],$rmoney,$temp);
							//echo '退款 id'.var_dump($rdres);
							if(!$rdres ){//退款单及明细
								$this->model->rollback();
						  
								continue;
							}
 
						

						}
					}
					
					
					
					

				}


				if(in_array($v['invoicestatus'],array(2,3,4)) ){//开票 已结通过的才导入
						//$invoicemoney =  $trademoney;
						$invoicestatus = $migration->get_invoicestatu($v['invoicestatus']);//******
						$icres = $this->invoice_record($v,$mres,$operator,$mdata['INVOICE_STATUS'],$mdata['CASE_ID'],$invoicemoney);
						if( !$icres ){//开票记录
							$this->model->rollback();
					  
							continue;
						}
						// 收益明细  电商会员开票
						//$invoicemoney = $v['paidmoney']?$v['paidmoney']:$v['total_price'];
						$confirmtime = $v['confirmtime'] ? $v['confirmtime']:$v['cardtime'];
						$ires = $this->income_info($mdata['CASE_ID'],$mres,$pres,3,$invoicemoney,$operator,date('Y-m-d H:i:s',$confirmtime),'电商会员开票');
						if(!$ires ){ 
							$this->model->rollback();
					  
							continue;
						}

						
				}
				

				Mylog::write(" <<会员(".$v['id'].") 导入成功 ");
				$this->model->commit(); 

			}

		}
		//获取会员退款金额
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
		//退款单及明细
		public function refund_list_detail($city,$migration,$caseid,$mres,$newmres,$pres,$ADD_UID,$where,$backtime,$invoicestatus=0,$rmoney=0,$temp=null){
				$flag = $where ? 1:0;
				$where = $where ? $where : ' limit 1';
				$mlist = $this->mysqlmodel->query("select * from erp_member_newrefund where refundstatus=10 and  mid='$mres' $where");//var_dump($mlist ); echo "select * from erp_member_newrefund where mid='$mres' $where"; 值导入了状态10 
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
					$mone['refundstatus'] = $oldrefund ? 10:$mone['refundstatus'] ;//如果是老流程记录 
					$mone['rmoney'] = $oldrefund ?$rmoney:(float)$mone['rmoney'];
					$listid = $rres = $inres = true;
					$temp = $temp2 =  array();
					$temp['ADD_UID'] = $ADD_UID;//添加人 操作人
					$temp['CREATETIME'] = date('Y-m-d H:i:s',$mone['createtime']);
					$temp['STATUS'] = $migration->get_refund_list_status($mone['refundstatus']);//状态****
					$temp['CITY_ID'] = $city;
					$listid = M('Erp_member_refund_list')->add($temp);
					if(!$listid){
						Mylog::write(' Erp_member_refund_list 退款单记录插入失败! ','error');
						
						 
						break;
					}else{
						Mylog::write( ' Erp_member_refund_list 退款单记录插入成功,ID：'.$listid);
					}
					$temp2['MID'] = $newmres; //会员id
					$temp2['PAY_ID'] = $pres ;//付款单号
					$temp2['REFUND_MONEY'] = (float)$mone['rmoney'];//退款金额
					if($backtime)$temp2['CONFIRMTIME'] =  date('Y-m-d H:i:s',$backtime);// 财务确认时间
					$temp2['REFUND_STATUS'] = $migration->get_refund_status($mone['refundstatus']);//财务状态****
					$temp2['LIST_ID'] = $listid;//退款单编号
					$temp2['APPLY_UID'] = $ADD_UID;//添加人 操作人
					if($mone['createtime'])$temp2['CREATETIME'] = date('Y-m-d H:i:s',$mone['createtime']);
					$temp2['CITY_ID'] = $city;

					$rres = M('Erp_member_refund_detail')->add($temp2);
					if(!$rres){
						Mylog::write(' Erp_member_refund_detail 退款明细记录插入失败! ','error');
						
						 
						break;
					}else{
						Mylog::write( ' Erp_member_refund_detail 退款明细记录插入成功,ID：'.$rres);
					}
					//if(in_array($invoicestatus,array(2,3,4)) ){//电商开票会员退款  
					if(false){
						$inres = $this->income_info($caseid,$newmres,$pres,20,-intval($mone['rmoney']),$ADD_UID,$temp2['CREATETIME'],'电商开票会员退款');

					}else{
						$inres = $this->income_info($caseid,$newmres,$pres,4,-intval($mone['rmoney']),$ADD_UID,$temp2['CREATETIME'],'电商未开票会员退款');
					}
					//收益明细 电商会员退款
					
					if(!$inres ){ 
						 
						
						 
						break;
					}

					/*if($mone['refundstatus']==2){
						$this->refund_flow_data( $listid,$city);

					}*/  //流程 
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
				$temp['STATUS'] = 1;//正在进行中
				$temp['INFO'] = $flows[0]['info'];
				$temp['CITY'] = $city;
				$temp['RECORDID'] = $listid;
				$flowres = M('Erp_flows')->add($temp);
				Mylog::write(' Erp_flows 流程创建 记录插入成功! id: '.$flowres );
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
						Mylog::write(' Erp_flownode 流程节点创建 记录插入失败! ','error');
						 
						return false;

					}
							
				}
				Mylog::write(' Erp_flownode 流程创建 记录插入成功!  '  );
				return true;
			}else{
					Mylog::write(' Erp_flows 流程创建 记录插入失败! ','error');
					return false; 
			}

		}
		//开票
		public function invoice_record($data,$mres,$operator,$invoicestatus,$caseid,$trademoney){
			$temp = array();
			$temp['CASE_ID'] = $caseid;
			$temp['CONTRACT_ID'] =  $mres;//会员id
			$temp['INVOICE_NO'] =  $data['invoice_no'];//发票号
			//$temp['INVOICE_MONEY'] = $data['paidmoney']?$data['paidmoney']:$data['total_price'];//$data['invoicemoney'];//发票金额
			$temp['INVOICE_MONEY'] = $trademoney;
			$temp['USER_ID'] = $operator;//开票人
			if($data['confirmtime'])$temp['CREATETIME'] = date('Y-m-d H:i:s',$data['confirmtime']); //申请时间
			$temp['APPLY_USER_ID'] = $operator;//开票申请人
			$temp['STATUS'] = $invoicestatus;// 发票状态 ??????????????
			if($data['confirmtime'])$temp['INVOICE_TIME'] = date('Y-m-d H:i:s',$data['confirmtime']);//开票时间
			$temp['INVOICE_TYPE'] = 2;//1、合同的开票 2、会员的开票 3、分销会员开票
			//$temp['FLOW_ID'] ='] =
			$inres = M('Erp_billing_record')->add($temp);
			if(!$inres){
				Mylog::write(' Erp_billing_record 开票记录插入失败! ','error');
				return false; 
			}else{
				Mylog::write( ' Erp_billing_record 开票记录插入成功,ID：'.$inres);
				return true;
			}

			
		}
		//收益记录
		public function income_info($caseid,$mres,$pres,$incomefrom,$money,$operator,$otime,$remark){
			$ProjectIncome_model = D("ProjectIncome");
			$temp['CASE_ID']  =  $caseid;
			$temp['ENTITY_ID']  =$mres;
			$temp['PAY_ID']  =  $pres;
			$temp['ORG_ENTITY_ID']  =$mres;//原始业务id
			$temp['ORG_PAY_ID']  =  $pres;//原始..
			$temp['INCOME_FROM']  = $incomefrom;
			$temp['INCOME']  =  $money;
			$temp['INCOME_REMARK']  = $remark;
			//$temp['OUTPUT_TAX']  =  
			$temp['ADD_UID']  = $operator;
			$temp['OCCUR_TIME']  = $otime;//发生时间
			$res = $ProjectIncome_model->add_income_info($temp);
			if($res){
				
				Mylog::write( ' ERP_INCOME_LIST 收益记录插入成功,ID：'.$res);
				return true;
			}else{
				Mylog::write(' ERP_INCOME_LIST 收益记录插入失败! ','error');
				return false; 
				
			}
		}
		//成本记录
		public function cost_info($type,$cres,$purreq ,$purlist,$feeid,$fee,$userid,$apptime,$isfp,$iskf,$info){
			//往成本表中添加记录
			$cost_info['CASE_ID'] = $cres;            //案例编号 【必填】       
			$cost_info['ENTITY_ID'] = $purreq;                 //业务实体编号 【必填】
			$cost_info['EXPEND_ID'] = $purlist;                    //成本明细编号 【必填】
			
			$cost_info['ORG_ENTITY_ID'] = $purreq;              //业务实体编号 【必填】
			$cost_info['ORG_EXPEND_ID'] = $purlist;
			
			$cost_info['FEE'] = $fee;                // 成本金额 【必填】 
			$cost_info['ADD_UID'] = $userid ;             //操作用户编号 【必填】
			$cost_info['OCCUR_TIME'] = $apptime;        //发生时间 【必填】
			$cost_info['ISFUNDPOOL'] = $isfp;                                //是否资金池（0否，1是） 【必填】
			$cost_info['ISKF'] = $iskf;                                    //是否扣非 【必填】
			$cost_info['FEE_REMARK'] = $info;                 //费用描述 【选填】
			$cost_info['INPUT_TAX'] = 0;                              //进项税 【选填】
			$cost_info['FEE_ID'] = $feeid;                               //成本类型ID 【必填】
			//成本来源
			 
			$cost_info['EXPEND_FROM'] = $type;//来源类型
			 
			$project_cost_model = D("ProjectCost");
			//var_dump($cost_info);die;
			return $project_cost_model->add_cost_info($cost_info);

		}
		//报销导入
		public function reimbursement_data($pres,$cres,$migration,$v){


			$iskf = 1;//扣非
			$rlist = $this->mysqlmodel->query("select * from erp_reimbursement where pid='".$v['id']."' and state = 1");
			if($rlist){
				
				$amount = 0;
				foreach($rlist as $key=>$one){
					/////
					$submit_user =  $one['submit_user'] ? $one['submit_user'] :$v['submit_userid'];
					$requisition['CASE_ID'] = $cres;
					$requisition['REASON'] = '团立方报销导入';
					$requisition['USER_ID'] = $migration->get_users_id($submit_user);
					$requisition['DEPT_ID'] =$migration->get_users_deptid($submit_user) ;
					$requisition['APPLY_TIME'] = date('Y-m-d H:i:s',$one['submit_date']);//提交时间
					if($one['re_con_time'])$requisition['END_TIME'] = date('Y-m-d H:i:s',$one['re_con_time']);//送达时间
					$requisition['PRJ_ID'] = $pres;
					$requisition['TYPE'] = 1;//project_purchase
					$requisition['CITY_ID'] = $v['city'];
					$requisition['STATUS'] = 4;//已采购
					$purreq = M('Erp_purchase_requisition')->add($requisition);//采购申请
					//var_dump($purreq );
					if(!$purreq) {
						Mylog::write( ' Erp_purchase_requisition 采购申请记录插入失败','error');
						return false;
					}else Mylog::write( ' Erp_purchase_requisition 采购申请记录插入成功: '.$purreq);
					$reim_list_arr = array();
					//$reim_list_arr['AMOUNT'] = //?
					$reim_list_arr['STATUS'] = 2; //状态
					if($one['re_con_time'])$reim_list_arr['REIM_TIME'] = date('Y-m-d H:i:s',$one['re_con_time']);//报销时间
					$reim_list_arr['REIM_UID'] = $requisition['USER_ID'];//报销审核人
					$reim_list_arr['TYPE'] =  1;//采购
					$reim_list_arr['APPLY_UID'] =  $requisition['USER_ID'];
					$reim_list_arr['APPLY_TRUENAME'] = $migration->get_users_name($submit_user) ;
					$reim_list_arr['APPLY_TIME'] =  date('Y-m-d H:i:s',$one['submit_date']);//申请时间
					$reim_list_arr['CITY_ID'] =  $v['city'];
					$reim_list_id = M('Erp_reimbursement_list')->add($reim_list_arr);//报销申请单
					if(!$reim_list_id) {
						Mylog::write( ' Erp_reimbursement_list 报销申请单记录插入失败','error');
						return false;
					}else Mylog::write( ' Erp_reimbursement_list 报销申请记录插入成功: '.$reim_list_id);
					$contract = array();
					$contract['CONTRACTID'] = '';//合同号?
					$contract['PROMOTER'] = $requisition['USER_ID'];//发起人
					$contract['TYPE'] =1;//定额
					$contract['SIGINGTIME'] =date('Y-m-d H:i:s',$one['submit_date']);//签订时间
					$contract['REIM_ID'] = $reim_list_id;//报销单ID
					$contract['ISSIGN'] = 2;//是否签约  状态值？？
					$contract['CITY_ID'] = $v['city'];// 
					$conid = M('Erp_contract')->add($contract);//采购合同
					if(!$conid)  {
						Mylog::write( ' Erp_contract 采购合同记录插入失败','error');
						return false;
					}else Mylog::write( ' Erp_contract 采购合同记录插入成功: '.$conid);




					//////
					$isfp = 0;
					if(TRIM($one['submit_type'])=='re_third_party')
						$isfp = 1;

					$purchase_info = array();
					$purchase_info['PR_ID'] = $purreq;//采购申请编号
					//$purchase_info['BRAND'] = u2g(strip_tags($_POST['BRAND']));
					//$purchase_info['MODEL'] = u2g(strip_tags($_POST['MODEL']));
					$purchase_info['PRODUCT_NAME'] =  $one['submit_info'] ;
					$purchase_info['NUM_LIMIT'] = 1;//数量限制
					$purchase_info['PRICE_LIMIT'] = $one['submit_cost'];//价格限制
					$purchase_info['PRICE'] = $one['submit_cost'];//价格限制
					$purchase_info['FEE_ID'] = $migration->get_fee_id(substr($one['submit_type'],3));
					$purchase_info['IS_FUNDPOOL'] = $isfp;//是否资金池
					$purchase_info['IS_KF'] = $iskf;//是否扣非
					$purchase_info['P_ID'] = 0;//指定采购人
					$purchase_info['TYPE'] = 1;//采购类型 1业务采购，2大宗采购
					$purchase_info['CONTRACT_ID'] = $conid;//合同号
					$purchase_info['CASE_ID'] =  $cres;//？
					$purchase_info['CITY_ID'] = $v['city']; 
					$purchase_info['STATUS'] = 2; //已报销

					$purchase_info['NUM'] = 1; 
					//$purchase_info['PURCHASE_COST'] = $one['submit_cost']; 
					//$purchase_info['TOTAL_COST'] = $one['submit_cost']; 

					$purlist = M('Erp_purchase_list')->add($purchase_info);//采购明细
					if(!$purlist)  {
						Mylog::write( ' Erp_purchase_list 采购明细记录插入失败','error');
						return false;
					}else Mylog::write( ' Erp_purchase_list 采购明细记录插入成功: '.$purlist);
					$arr_reim_data = array();
					$arr_reim_data['CITY_ID'] = $v['city'];
    				$arr_reim_data['CASE_ID'] = $cres;
    				$arr_reim_data['BUSINESS_ID'] = $purlist;//各报销业务ID 
    				$arr_reim_data['MONEY'] = (float)$one['submit_cost'];//报销金额
    				$arr_reim_data['STATUS'] = 1;//已报销
    				$arr_reim_data['APPLY_TIME'] = date('Y-m-d H:i:s',$one['submit_date']);
    				$arr_reim_data['ISFUNDPOOL'] = $isfp;
    				$arr_reim_data['ISKF'] = $iskf;//默认扣非
    				$arr_reim_data['TYPE'] =  1;//报销类型
					$arr_reim_data['LIST_ID'] = $reim_list_id;//报销单编号
					$arr_reim_data['FEE_ID'] = $purchase_info['FEE_ID'];


					$reimdetail = M('Erp_reimbursement_detail')->add($arr_reim_data);//报销明细
					if(!$reimdetail) {
						Mylog::write( ' Erp_reimbursement_detail 报销明细记录插入失败','error');
						return false;
					}else Mylog::write( ' Erp_reimbursement_detail 报销明细记录插入成功: '.$reimdetail);
					$amount += $one['submit_cost'];
					$costrest = $this->cost_info(1,$cres,$purreq ,$purlist,$purchase_info['FEE_ID'],$one['submit_cost'],$requisition['USER_ID'],date('Y-m-d H:i:s',$one['submit_date']),$isfp,$iskf,$one['submit_info'].'采购申请');// 
					if(!$costrest) {
						Mylog::write( ' Erp_COST_LIST 采购申请 插入成本记录失败','error');
						return false;
					}else Mylog::write( ' Erp_COST_LIST 采购申请 插入成本记录插入成功: '.$costrest);
					$costrest = $this->cost_info(2,$cres,$purreq ,$purlist,$purchase_info['FEE_ID'],$one['submit_cost'],$requisition['USER_ID'],date('Y-m-d H:i:s',$one['submit_date']),$isfp,$iskf,$one['submit_info'].'采购合同签订');// 
					if(!$costrest) {
						Mylog::write( ' Erp_COST_LIST 采购合同签订 插入成本记录失败','error');
						return false;
					}else Mylog::write( ' Erp_COST_LIST 采购合同签订 插入成本记录插入成功: '.$costrest);
					/*$costrest = $this->cost_info(3,$cres,$purreq ,$purlist,$purchase_info['FEE_ID'],$one['submit_cost'],$requisition['USER_ID'],date('Y-m-d H:i:s',$one['submit_date']),$isfp,$iskf,$one['submit_info'].'采购报销申请');//  
					if(!$costrest) {
						Mylog::write( ' Erp_COST_LIST 采购报销申请 插入成本记录失败','error');
						return false;
					}else Mylog::write( ' Erp_COST_LIST 采购报销申请 插入成本记录插入成功: '.$costrest);*/
					$costrest = $this->cost_info(4,$cres,$purreq ,$purlist,$purchase_info['FEE_ID'],$one['submit_cost'],$requisition['USER_ID'],date('Y-m-d H:i:s',$one['re_con_time']),$isfp,$iskf,$one['submit_info'].'采购报销通过');// 
					if(!$costrest) {
						Mylog::write( ' Erp_COST_LIST 采购报销通过 插入成本记录失败','error');
						return false;
					}else Mylog::write( ' Erp_COST_LIST 采购报销通过 插入成本记录插入成功: '.$costrest);
				}
				$temp = array();
				$temp['AMOUNT'] = $amount;
				$reim_list_res = M('Erp_reimbursement_list')->where("ID=$reim_list_id")->save($temp);
				if(!$reim_list_res)  {
					Mylog::write( ' Erp_reimbursement_list 报销申请金额更新失败','error');
					return false;
				}else Mylog::write( ' Erp_reimbursement_list 报销申请金额更新成功: '.$reim_list_res);
			}
			return true;

		}
		//流程导入？
		//文件导入
		//城市
		public function city_data(){
			$cfg['city'] = array(
				'1' => '南京',
				'2' => '苏州',
				'3' => '昆山',
				'4' => '无锡',
				'5' => '常州',
				'6' => '合肥',
				'7' => '芜湖',
				'8' => '杭州',
				'9' => '西安',
				'10' => '重庆',
				'11' => '沈阳',
				'101' => '蚌埠',
				'102' => '滁州',
				'103' => '马鞍山',
				'104' => '阜阳',
				'105' => '武汉',
				'106' => '哈尔滨',
				'107' => '长春',
				'108' => '天津',
				'109' => '昆明',
				'110' => '石家庄',
				'111' => '六安',
				'112' => '郑州',
				'113' => '南通',
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
				if(!$res)  Mylog::write(' 城市插入! '.$v,'error');
			}


		}
		//角色导入
		public function group_data(){
			$list = $this->mysqlmodel->query("select * from erp_group where title like '%西安%' and status=1"); 
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

		//附件导入
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
					Mylog::write(' 文件插入成功! '.$one['id'] );
				}else{
					Mylog::write(' 文件插入! '.$one['id'],'error');
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
		//导入用户手机号码 与 离职状态
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
					Mylog::write(' 手机号码更新成功! '.$one['USER_ID'] );
				}else{
					Mylog::write(' 手机号码更新失败! '.$one['USER_ID'],'error');
				}
			}

		}
		//导入部门
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
					Mylog::write(' 部门更新成功! '.$one['DEPT_ID'] );
				}else{
					Mylog::write(' 部门更新失败! '.$one['DEPT_ID'],'error');
				}
			}

		}
		//导入部门
		function import_dept2(){
			$deplist = $this->mysqlmodel->query("select * from department");
			foreach($deplist  as $one){
				 
				$data['DEPTNAME'] = $one['DEPT_NAME'];
				$data['PARENTID'] = $one['DEPT_PARENT'];
				 
					$data['ID'] = $one['DEPT_ID'];
					$data['ISVALID'] = -1;
					$re = D('Erp_dept')->add($data);
			 
				if($re){
					Mylog::write(' 部门插入成功! '.$one['DEPT_ID'] );
				}else{
					Mylog::write(' 部门插入失败! '.$one['DEPT_ID'],'error');
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
		//综合付款明细 与已付金额不一致
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
		//费综合付款 的退款记录多余1条的情况
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
		//会员之后 已支付paidmoney 未支付unpaidmoney  字段值未做相应的增减操作 ，会影响支付明细金额的计算
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
		//计算交易金额大于收费标准的情况
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
		//费退款状态会员 有退款记录
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