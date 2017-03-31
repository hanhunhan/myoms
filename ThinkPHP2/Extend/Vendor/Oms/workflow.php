<?php

	class workflow{

		public $model;
		public $nowtime;
		public $user;
		public $role;
		public $flowtype;
		public $city;

		public function __construct(){
			$this->model = new model();
			$this->user = $_SESSION['uinfo']['uid'];
			$this->nowtime = date('Y-m-d h:m:s');
			$this->role = $_SESSION['uinfo']['role'];
			$this->city = $_SESSION['uinfo']['city'];
		}

		//新建流程权限
		public function start_authority($type){	
			$this->flowtype = $type;
			
			$auth=$this->model->query("select * from erp_flowset a left join erp_flowtype b on 
			
			a.flowtype = b.id where b.pinyin = '{$type}' and a.flowstart = {$this->role}");
			
			if($auth){
				return true;
			}else{
				return false;
			}
		}

		//结束流程权限
		public function end_authority($type){
			$auth=$this->model->query("select * from erp_flowset a left join erp_flowtype b on 
			
			a.flowtype = b.id where b.pinyin = '{$type}' and a.flowend = {$this->role}");

			if($auth){
				return true;
			}else{
				return false;
			}
		}

		//删除流程权限
		public function del_authority($flowid){
			$auth = $this->model->query("select * from erp_flows where id = $flowid and maxstep = 2");

			if($auth){
				return true;
			}else{
				return false;
			}
		}

		//新建流程
		public function createworkflow($data){
			$sql = "select a.id from erp_flowset a left join erp_flowtype b on a.flowtype = b.id where b.pinyin = '{$data['type']}'";
           //echo $sql;die;
			$flowSet = $this->model->query($sql);
			$flowsetid = $flowSet[0]['ID'];
			
			$this->model->startTrans();
			//$sql = "insert into erp_flows(FLOWSETID,CASEID,MAXSTEP,ADDTIME,ADDUSER,STATUS,INFO,CITY)VALUES($flowsetid,'{$data['CASEID']}',2,'{$this->nowtime}',{$this->user},1,'{$data['INFO']}',{$this->city})";
			//$flows = $this->model->execute($sql);
			//$flowid =  SEQ_ERP_FLOWS.'.'.currval;
			$insert['FLOWSETID'] = $flowsetid;
			$insert['CASEID'] = $data['CASEID'];
			$insert['MAXSTEP'] = 2;
			$insert['ADDTIME'] = "{$this->nowtime}";
			$insert['ADDUSER'] = $this->user;
			$insert['STATUS'] = 1;
			$insert['INFO'] = "{$data['INFO']}";
			$insert['CITY'] = $this->city;
			$insert['RECORDID'] = $data['RECORDID'];
			$insert['ACTIVID'] = $data['ACTIVID']?$data['ACTIVID']:'';
			
			$flowid = M('Erp_flows')->add($insert);
			
			$sql = "insert into erp_flownode(FLOWID,DEAL_USERID,S_TIME,DEAL_INFO,STEP,STATUS,FILES,ISMALL,ISPHONE)VALUES($flowid,{$this->user},'{$this->nowtime}','{$data['DEAL_INFO']}',1,3,'{$data['FILES']}',{$data['ISMALL']},{$data['ISPHONE']})";
			
			$affect = $this->model->execute($sql);
			
			$sql = "insert into erp_flownode(FLOWID,DEAL_USERID,STEP,STATUS)VALUES($flowid,{$data['DEAL_USERID']},2,1)";
			//var_dump($affect);
			$affected = $this->model->execute($sql);
			//var_dump($affected);
			$sign = $this->beginOperate($flowsetid,$data['CASEID'],$data['RECORDID']);
           // var_dump($sign);
			if($flowid && $affect && $affected && $sign){
				$this->model->commit();
				return $flowid;
			}else{
				$this->model->rollback();
				return false;
			}
		}

		//点击操作
		public function nextstep($flowid){
			
			$lastworkflow = $this->model->execute("update erp_flownode set STATUS = 4,E_TIME ='{$this->nowtime}' where FLOWID = $flowid and STATUS = 3 ");

			$last = $this->model->execute("update erp_flownode set STATUS= 2,S_TIME='{$this->nowtime}' where FLOWID = $flowid and DEAL_USERID = {$this->user} and STATUS = 1");

			return true;
		}

		//委托
		public function deliverworkflow($data){

			$this->model->startTrans();
			$step = $this->getmaxstep($data['flowId'])+1;

			$last = $this->model->execute("update erp_flownode set STATUS=3,E_TIME='{$this->nowtime}',DEAL_INFO = '已委托',ISMALL = -1,ISPHONE = -1 where FLOWID = {$data['flowId']} and STATUS = 2 and DEAL_USERID = {$this->user}");
			
			$next = $this->model->execute("insert into erp_flownode(FLOWID,DEAL_USERID,STEP,STATUS)VALUES({$data['flowId']},{$data['DEAL_USERID']},$step,1)");
			
			$flows = $this->model->execute("update erp_flows set MAXSTEP = {$step} where ID = {$data['flowId']}");

			if($last && $next && $flows ){

				$this->model->commit();
				return true;
				
			}else{

				$this->model->rollback();
				return false;
			}
						
		}

		// 下一步
		public function handleworkflow($data){
			$this->model->startTrans();
			$step = $this->getmaxstep($data['flowId'])+1;
			
			$last = $this->model->execute("update erp_flownode set STATUS=3,E_TIME='{$this->nowtime}',DEAL_INFO = '{$data['DEAL_INFO']}',FILES= '{$data['FILES']}',ISMALL = {$data['ISMALL']},ISPHONE = {$data['ISPHONE']} where FLOWID = {$data['flowId']} and STATUS = 2 and DEAL_USERID = {$this->user}");
			
			$next = $this->model->execute("insert into erp_flownode(FLOWID,DEAL_USERID,S_TIME,STEP,STATUS)VALUES({$data['flowId']},{$data['DEAL_USERID']},'{$this->nowtime}',$step,1)");
			
			$flows = $this->model->execute("update erp_flows set MAXSTEP = {$step} where ID = {$data['flowId']}");


			if($last && $next && $flows ){

				$this->model->commit();
				return true;
			
			}else{

				$this->model->rollback();
				return false;
			}
		}

		//同意
		public function passWorkflow($data){
			$this->model->startTrans();
			$step = $this->getmaxstep($data['flowId'])+1;
			
			$last = $this->model->execute("update erp_flownode set STATUS=3,E_TIME='{$this->nowtime}',DEAL_INFO = '{$data['DEAL_INFO']}',FILES= '{$data['FILES']}',ISMALL = {$data['ISMALL']},ISPHONE = {$data['ISPHONE']} where FLOWID = {$data['flowId']} and STATUS = 2 and DEAL_USERID = {$this->user}");
			
			$next = $this->model->execute("insert into erp_flownode(FLOWID,DEAL_USERID,S_TIME,STEP,STATUS)VALUES({$data['flowId']},{$data['DEAL_USERID']},'{$this->nowtime}',$step,1)");
			
			$flows = $this->model->execute("update erp_flows set MAXSTEP = {$step},status = 2 where ID = {$data['flowId']}");

			$sign = $this->passOperate($data['flowId']);

			if($last && $next && $flows && $sign){

				$this->model->commit();
				return true;
			
			}else{

				$this->model->rollback();
				return false;
			}
		}

		//否决
		public function notWorkflow($data){
			
			$this->model->startTrans();

			$next = $this->model->execute("update erp_flownode set STATUS = 4,E_TIME='{$this->nowtime}',DEAL_INFO = '{$data['DEAL_INFO']}',ISMALL = {$data['ISMALL']},ISPHONE = {$data['ISPHONE']} where FLOWID = {$data['flowId']} and STATUS =2");

			$flows = $this->model->execute("update erp_flows set STATUS = 3 where ID = {$data['flowId']}");
			
			$sign = $this->notOperate($data['flowId']);

			if($next && $flows && $sign){
				$this->model->commit();
				return true;
				
			}else{
				$this->model->rollback();
				return false;
			}
		}


		//备案
		public function finishworkflow($data){
			$this->model->startTrans();
			
			$next = $this->model->execute("update erp_flownode set STATUS = 4,E_TIME='{$this->nowtime}',DEAL_INFO = '{$data['DEAL_INFO']}',ISMALL = {$data['ISMALL']},ISPHONE = {$data['ISPHONE']} where FLOWID = {$data['flowId']} and STATUS =2");

			$flows = $this->model->execute("update erp_flows set status = 4 where id = {$data['flowId']}");
			
			$sign = $this->endOperate($data['flowId']);
			

			if($next && $flows && $sign){
				$this->model->commit();
				return true;
				
			}else{
				$this->model->rollback();
				return false;
			}

		}

		//退回流程发起人
		public function backFlow($flowid){
			$this->model->startTrans();
			$last = $this->model->execute("update erp_flownode set STATUS = 4 where STATUS = 3 AND FLOWID = $flowid");

			$next = $this->model->execute("update erp_flownode set S_TIME = '{$this->nowtime}',E_TIME = '{$this->nowtime}',STATUS = 3,DEAL_INFO = '回退流程发起人',ISMALL = -1,ISPHONE = -1 where  FLOWID = $flowid and DEAL_USERID = {$this->user} and STATUS = 1");
			
			$maxstep = $this->getmaxstep()+1;
			$info = $this->model->query("select * from erp_flows where ID = $flowid");
			
			$insert = $this->model->execute("insert into erp_flownode(FLOWID,DEAL_USERID,STEP,STATUS)VALUES($flowid,{$info[0]['ADDUSER']},$maxstep,1)");

			$flows = $this->model->execute("update erp_flows set MAXSTEP = $maxstep where ID = $flowid");

			if($last && $next && $insert && $flows){

				$this->model->commit();
				return true;
			}else{  
				$this->model->rollback();
				return false;
			}	
		}

		//收回
		public function recoverFlow($flowid){
			
			$this->model->startTrans();
			
			$delete = $this->model->execute("delete erp_flownode where flowid = $flowid and status = 1");

			$affect = $this->model->execute("update erp_flownode set status =2,deal_info ='' where flowid = $flowid and deal_userid = {$this->user} and status = 3");
			
			$step = $this->getmaxstep($flowid)-1;

			$flows = $this->model->execute("update erp_flows set MAXSTEP = $step where ID = $flowid");

			if($affect && $delete && $flows){
				$this->model->commit();
				return true;
			}else{
				$this->model->rollback();
				return false;
			}

		}

		//已办结
		public function alreadyworkflow(){
			$sql = "select f.id,f.addtime,f.flowtype,f.name,g.id as nodeid,f.status  
			from (select b.*,c.flowtype,e.name from erp_flowset a 
			left join erp_flows b on a.id= b.flowsetid 
			left join erp_flowtype c on a.flowtype = c.id
			left join erp_users e on e.id=b.adduser ) f 
			inner join  erp_flownode g on f.id = g.flowid 
			where (f.status=1 or f.status=2) and g.deal_userid={$this->user} and g.status = 3 
			ORDER BY addtime DESC";

			//(SELECT F.ID,F.CITY,F.INFO,F.MAXSTEP,F.ADDTIME,F.FLOWTYPE,F.NAME,F.DEPTID,G.ID AS NODEID,G.DEAL_USERID,F.STATUS FROM (SELECT B.*,C.FLOWTYPE,E.NAME,E.DEPTID FROM ERP_FLOWSET A LEFT JOIN ERP_FLOWS B ON A.ID= B.FLOWSETID LEFT JOIN ERP_FLOWTYPE C ON A.FLOWTYPE = C.ID LEFT JOIN ERP_USERS E ON E.ID=B.ADDUSER ) F INNER JOIN ERP_FLOWNODE G ON F.ID = G.FLOWID WHERE (F.STATUS=1 OR F.STATUS =2) and g.deal_userid={$this->user} AND G.STATUS = 3 ORDER BY ADDTIME DESC)
			
			$record = $this->model->query($sql);
			
			return $record;
		}

		//待办
		public function waitworkflow(){
			
			$sql = "select f.id,f.addtime,f.flowtype,f.name,g.id as nodeid,f.status  
			from (select b.*,c.flowtype,e.name from erp_flowset a 
			left join erp_flows b on a.id= b.flowsetid 
			left join erp_flowtype c on a.flowtype = c.id
			left join erp_users e on e.id=b.adduser ) f 
			inner join  erp_flownode g on f.id = g.flowid 
			where (f.status=1 or f.status=2) and g.deal_userid={$this->user} and (g.status = 1 or g.status =2) 
			ORDER BY addtime DESC";
			//$sql = "select A.*,B.DEAL_USERID,B.S_TIME,B.E_TIME,B.DEAL_INFO,B.STEP,B.STATUS AS NODESTATUS,C.FLOWTYPE from (erp_flows A left join erp_flownode C on A.FLOWSETID = C.ID) inner join erp_flownode B on A.ID=B.FLOWID where A.STATUS =1 AND B.DEAL_USERID = {$this->user} and (B.STATUS = 1 or B.STATUS =2) order by ADDTIME DESC";
			
			$record = $this->model->query($sql);

			return $record;

		}
		//经办
		public function throughworkflow(){
			$sql = "select f.id,f.addtime,f.flowtype,f.name,g.id as nodeid,f.status 
			from (select b.*,c.flowtype,e.name from erp_flowset a 
			left join erp_flows b on a.id= b.flowsetid 
			left join erp_flowtype c on a.flowtype = c.id
			left join erp_users e on e.id=b.adduser ) f 
			inner join  erp_flownode g on f.id = g.flowid 
			where g.deal_userid={$this->user} and g.status = 4 
			ORDER BY addtime DESC";
			//$sql = "select A.*,B.DEAL_USERID,B.S_TIME,B.E_TIME,B.DEAL_INFO,B.STEP,B.STATUS AS NODESTATUS from (erp_flows A left join erp_flownode C on A.FLOWSETID = C.ID) inner join erp_flownode B on A.ID=B.FLOWID where B.DEAL_USERID = {$this->user} and B.STATUS = 4 order by ADDTIME DESC";
			
			$record = $this->model->query($sql);

			return $record;
		}
	
		//流程图
		public function chartworkflow($flowid){
			$sql = "select a.*,b.name from erp_flownode a left join erp_users b on a.deal_userid = b.id where a.flowid = ".$flowid."ORDER BY a.id ASC";
           
			$record = $this->model->query($sql);

			return $record;
		}
		
		//删除
		public function deleteworkflow($flowid){
			$this->model->startTrans();
			$workflow = $this->model->execute("delete from erp_flownode where FLOWID = $flowid");

			$flows = $this->model->execute("delete from erp_flows where ID = $flowid");

			if ($flows && $workflow){
				$this->model->commit();
				return true;
			} else{
				$this->model->rollback();
				return false;
			}
		}

		//获取最大步骤
		public function getmaxstep($flowid){
			$sql = "select * from erp_flownode where FLOWID = $flowid";
			$record = $this->model->query($sql);

			return count($record);
		}


		public function createHtml($flowid=''){
			//if($_REQUEST['flowid']){
				$workflow = $this->model->query("select a.*,b.name from erp_flownode a left join erp_users b on a.deal_userid = b.id where a.flowid = ".$flowid."ORDER BY a.id ASC");
				//print_r($workflow);

				$html = "<div>";
				if($workflow){
					$html .= "<div class='contractinfo-table'><table><thead><tr>";
					$html .= "<td>步骤</td>";
					$html .= "<td>经手人</td>";
					$html .= "<td>状态</td>";
					$html .= "<td>审批意见</td>";
					$html .= "<td>开始日期</td>";
					$html .= "<td>提交日期</td>";
					$html .= "</tr></thead><tbody>";
						foreach($workflow as $key=>$value){
							if($value['STATUS']=='1'){
								$status = "未办理";
							}elseif($value['STATUS']=='2'){
								$status = "办理中";
							}else{
								$status = "已办结";
							}
							$html .= "<tr>";
							$html .= "<td>第{$value['STEP']}步</td>";
							$html .= "<td>{$value['NAME']}</td>";
							$html .= "<td>{$status}</td>";
							$html .= "<td>{$value['DEAL_INFO']}</td>";
							$html .= "<td>{$value['S_TIME']}</td>";
							$html .= "<td>{$value['E_TIME']}</td>";
							$html .= "</tr>";
						}
					$html .= "</tbody></table></div>";
				}

				$finish = $this->model->query("select * from erp_flows where id = $flowid and (status = 3 or status = 4)");

				if(!$finish){
					$html .= "<div class='caseinfo-table'><form class='registerform' method='post' action='{$this->joinUrl(__SELF__,'savedata=1')}' enctype='multipart/form-data'>";
					$html .= "<table>";
					if(!$flowid){
						$html .= "<tr><td width = '20%' align='center'>文字/说明</td><td><input type='text' name='INFO' placeholder='流程说明'></td></tr>";
					}
					$html .= "<tr><td width='20%' align='center'>转交至</td><td><input type='text' name='DEAL_USER' id='DEAL_USER' placeholder='名称' /><input type='hidden' name='DEAL_USERID' id='DEAL_USERID'  /><input type='hidden' name='PHONE' id = 'PHONE' /><input type='hidden' name='CITY' id = 'CITY' />";
					
					$roleId = $this->getFixedRole($flowid);
					
					$html .= "<input type='hidden' name='roleId' id='roleId' value='{$roleId}' />";
					 
					$html .= "</td></tr>";
					$html .= "<tr><td align='center'>短信</td><td><input type='radio' name='ISPHONE' value='-1' checked/>是&nbsp;<input type='radio' name='ISPHONE' value='0' />否</td></tr>";	
					$html .= "<tr><td align='center'>OA邮件</td><td><input type='radio' name='ISMALL' value='-1' checked/>是&nbsp;<input type='radio' name='ISMALL' value='0' />否</td></tr>";
					$html .= "<tr><td align='center'>审批意见</td><td><span class='fclos' style='display:inline'>
							<textarea cols='100' rows='4' maxlength='300' name='DEAL_INFO'></textarea>
							</span></td></tr>";
					$html .= "<tr><td align='center'>附件</td><td><input type='file' name='FILES' /></td></tr>";
					$html .= "</table>";		
					$html .= "<div class='handle-btn'>";

					$pass = $this->judgePassRole($flowid);
					if($pass){
						$html .= "<input type='submit' value='同&nbsp;&nbsp;意' name='flowPass' class='btn-blue' />";
					}
					$not = $this->judgeNotRole($flowid);
					if($not){
						$html .= "<input type='submit' value='否&nbsp;&nbsp;决' name='flowNot' class='btn-blue' />";
					}
					$judgeRole = $this->judgeFinishRole($flowid);
					if($judgeRole){
						$html .= "<input type='submit' value='备&nbsp;&nbsp;案' name='flowStop' class='btn-blue' />";
					}
					$next = $this->judgeNextRole($flowid);
					if($next){
						$html .= "<input type='submit' value='转交下一步' name='flowNext' class='btn-blue' />";
					}
					$html .= "</div></form></div>";
					$html .= '<script>
								$(function(){
									 $("#DEAL_USER").autocomplete({
										
										source:function(request, response){
											$.ajax({
												url:"'.U('Api/getFlowPeople').'",
												dataType:"json",
												data:{
													"search":request.term,
													"roleId":$("#roleId").val()
												},
												success:function(obJect){
												response($.map(obJect,function(item){
													return {
														label:item.name,
														value:item.name,
														USERID: item.id	
													}
													}));
												}
											});
										},
										select:function(event,ui){
											$("#DEAL_USERID").val(ui.item.USERID);		
										}
									 });
												
								}); 
							</script>';
				}
				$html .= "</div>";
				return $html;
		}

		public function judgeFinishRole($flowid){
			
			if($flowid){
				$type = $this->judgeFlowType($flowid);
				
				if($type){
					$rolestep = explode(',',$type[0]['FLOWCURRENT']);
					if($type[0]['MAXSTEP'] == count($rolestep)){
						return true;
					}else{
						return false;
					}
				}else{
					$affect = $this->executeSql($flowid);
					if($affect ){
						if($affect['FLOWEND']){
							if($affect['FLOWEND'] == $this->role){
								return true;
							}else{
								return false;
							}
						}else{
							return true;
						}
						
					}else{
						return false;
					}
				}
				
			}else{
				
				return false;
			}
		}

		public function judgePassRole($flowid){
			if($flowid){
				$type = $this->judgeFlowType($flowid);
				if($type){
					return false;
					
				}else{
					$affect = $this->executeSql($flowid);
					if($affect ){
						if($affect['FLOWPASS']){
							if($affect['FLOWPASS'] == $this->role){
								return true;
							}else{
								return false;
							}
						}else{
							return true;
						}
					}else{
						return false;
					}
				}
				
			}else{
				return false;
			}
		}

		public function judgeNotRole($flowid){

			if($flowid){
				
				$type = $this->judgeFlowType($flowid);
				
				if($type){
					return true;
				}else{
					$affect = $this->executeSql($flowid);
					
					if($affect){
						if($affect['FLOWNOT']){
							if(strstr($affect['FLOWNOT'],$this->role)){
								return true;
							}else{
								return false;
							}
						}else{
							return true;
						}
						
					}else{
						return false;
					}
				}
			}else{
				
				
				return false;
			}
		}

		public function judgeNextRole($flowid=''){
			if($flowid){
				$type = $this->judgeFlowType($flowid);
				if($type){
					$rolestep = explode(',',$type[0]['FLOWCURRENT']);
					if($type[0]['MAXSTEP'] == count($rolestep)){
						return false;
					}else{
						return true;
					}
				}else{
					return true;
				}
			}else{
				return true;
			}
			
		}

		
		public function joinUrl($url,$vars){
			$info =  parse_url($url); 
			// 解析参数
			if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
				parse_str($vars,$vars);
			}elseif(!is_array($vars)){
				$vars = array();
			}

			if(isset($info['query'])) { // 解析地址里面参数 合并到vars
				parse_str($info['query'],$params);//var_dump($params);
				unset($params['s']); 
				$vars = array_merge($params,$vars);
				
			}
			$str = http_build_query( $vars);
			$ljstr = strstr($url,'?') ?  '&' :'?'; 
			$arr = explode('&',$url);
			return $arr[0].$ljstr.$str;
		}
		

		public function beginOperate($flowsetid,$caseid,$recordid){
			if($flowsetid){
				$sql = "select * from erp_flowset where id = {$flowsetid}";
				$record = $this->model->query($sql);
                //var_dump($record);die;
				$this->model->startTrans();
				$sign = true;
				if($record){
					if($record[0]['STARTSQL']){	
						foreach(explode(";",$record[0]['STARTSQL']) as $key=>$val){
							if(strstr($val,"%RECORDID%")){
								$sql = str_replace("%RECORDID%",$recordid,$val); 
								
								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
							if(strstr($val,"%CASEID%")){
								$sql = str_replace("%CASEID%",$caseid,$val);
								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
						}
					}
				}
				if($sign){
					$this->model->commit();
					return true;
				}else{
					$this->model->rollback();
					return false;
				}
			}
		}


		public function endOperate($flowid){
			if($flowid){

				$sql = "select a.caseid,a.recordid,b.* from erp_flows a left join erp_flowset b on a.flowsetid = b.id where a.id = {$flowid}";
				$record = $this->model->query($sql);
				$this->model->startTrans();
				$sign = true;
				if($record){
					if($record[0]['ENDSQL']){
						$RECORDID = $record[0]['RECORDID'];
						$CASEID = $record[0]['CASEID'];
						foreach(explode(";",$record[0]['ENDSQL']) as $key=>$val){
							if(strstr($val,"%RECORDID%")){
								$sql = str_replace("%RECORDID%",$RECORDID,$val);
								
								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
							if(strstr($val,"%CASEID%")){
								$sql = str_replace("%CASEID%",$CASEID,$val);
								
								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
						}
					}
				}
				if($sign){
					$this->model->commit();
					return true;
				}else{
					$this->model->rollback();
					return false;
				}
			}
		}

		public function passOperate($flowid){
			if($flowid){
				$sql = "select a.caseid,a.recordid,b.* from erp_flows a left join erp_flowset b on a.flowsetid = b.id where a.id = {$flowid}";
				$record = $this->model->query($sql);
				
				$this->model->startTrans();
				$sign = true;
				if($record){
					if($record[0]['PASSSQL']){

						$RECORDID = $record[0]['RECORDID'];
						$CASEID = $record[0]['CASEID'];
						foreach(explode(";",$record[0]['PASSSQL']) as $key=>$val){
							if(strstr($val,"%RECORDID%")){
								$sql = str_replace("%RECORDID%",$RECORDID,$val);
								
								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
							if(strstr($val,"%CASEID%")){
								$sql = str_replace("%CASEID%",$CASEID,$val);
								
								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
						}
					}
				}
				
				if($sign){
					$this->model->commit();
					return true;
				}else{
					$this->model->rollback();
					return false;
				}
			}
		}

		public function notOperate($flowid){
			if($flowid){
				
				$sql = "select a.caseid,a.recordid,b.* from erp_flows a left join erp_flowset b on a.flowsetid = b.id where a.id = {$flowid}";
				$record = $this->model->query($sql);
				
				$this->model->startTrans();
				$sign = true;
				if($record){
					if($record[0]['NOTSQL']){
						//$notSql = explode(";",$record[0]['NOTSQL']);
						$RECORDID = $record[0]['RECORDID'];
						$CASEID = $record[0]['CASEID'];
						
							foreach(explode(";",$record[0]['NOTSQL']) as $key=>$val){
								if(strstr($val,"%RECORDID%")){
									$sql = str_replace("%RECORDID%",$RECORDID,$val);
									
									$record = $this->model->execute($sql);
									if(!$record){
										$sign = false;
									}
								}
								if(strstr($val,"%CASEID%")){
									$sql = str_replace("%CASEID%",$CASEID,$val);
									
									$record = $this->model->execute($sql);
									if(!$record){
										$sign = false;
									}
								}
							}
					}
				}
				if($sign){
					$this->model->commit();
					return true;
				}else{
					$this->model->rollback();
					return false;
				}
			}
		}

		public function flowPassRole($flowid){
			$type = $this->judgeFlowType($flowid);
			$sign = false;
			if(!$type){
				$auth = $this->executeSql($flowid);
				
				if($auth){
					if($auth['FLOWTHROUGH']){
						$sql = "select b.roleid from erp_flownode a left join erp_users b on a.deal_userid = b.id where a.flowid = {$flowid} and a.step != 1";
						$pass = $this->model->query($sql); 
						
						if($pass){
							foreach($pass as $key=>$val){
								if(strstr($auth['FLOWTHROUGH'],$val['ROLEID'])){
									$sign = true;
								}
							}
						}
					}else{
						$sign = true;
					}
				}
			}else{
				$sign = true;
			}

			return $sign;

		}

		public function executeSql($flowid){
			$sql = "select b.id,b.judgeflowsql,a.recordid,a.caseid from erp_flows a left join erp_flowset b on a.flowsetid = b.id  where a.id = {$flowid}";

			$record = $this->model->query($sql);
			$flowsetid = $record[0]['ID'];
			$judgesql = $record[0]['JUDGEFLOWSQL'];
			$recordid = $record[0]['RECORDID'];
			$caseid = $record[0]['CASEID'];
			
			$choose = $this->model->query("select * from erp_flowrole where flowsetid = {$flowsetid}");
			
			if(!$judgesql){
				foreach($choose as $key=>$val){
						if(!isset($val['SIGN']) && !isset($val['VALUE'])){
								return $val;
						}
					}
			}else{
				if(strstr($judgesql,"%RECORDID%")){
					$sqll = str_replace("%RECORDID%",$recordid,substr($judgesql,0,-1));
				}
				if(strstr($judgesql,"%CASEID%")){
					$sqll = str_replace("%CASEID%",$caseid,substr($judgesql,0,-1));
				}
				
				$execute = $this->model->query($sqll);
				
				if($choose){
					foreach($choose as $key=>$val){
						
						if(isset($val['SIGN']) && isset($val['VALUE'])){
							if($execute[0]['RESULT'].$val['SIGN'].$val['VALUE']){
								return $val;
							}
						}
						
						
					}
				}
			}
			
			return false;
		}

		public function getFixedRole($flowid=''){
			$result = '';
			
			if($flowid){
				$sql = "select maxstep, flowsetid from erp_flows where id= $flowid";

				$record = $this->model->query($sql);
				
				if($record){
					$step = $record[0]['MAXSTEP'];
					$flowsetid = $record[0]['FLOWSETID'];

					$sql = "select * from erp_fixedflow where city = {$this->city} and flowsetid = $flowsetid";
					
					$fixed = $this->model->query($sql);
					
					if($fixed){
						$roleStep = explode(',',$fixed[0]['FLOWCURRENT']);
						
						if(count($roleStep) >= ($step+1)){
							$result= $roleStep[$step];
						}
					}

				}
				
			}else{
				$sql = "select a.id from erp_flowset a left join erp_flowtype b on a.flowtype = b.id where b.pinyin = '{$this->flowtype}'";
				$record = $this->model->query($sql);
				
				if($record){
					$id = $record[0]['ID'];

					$sql = "select * from erp_fixedflow where flowsetid = {$id} and city = {$this->city}";
					$list = $this->model->query($sql);
					
					if($list){
						$roleStep = explode(',',$list[0]['FLOWCURRENT']);
						
						if(count($roleStep) >= 2){
							$result=  $roleStep[1];
						}
					}
				}
			}

			return $result;
		}
		
		public function judgeFlowType($flowid = ''){
			if($flowid){
				$sql = "select a.maxstep,b.flowcurrent from erp_flows a inner join erp_fixedflow b on a.flowsetid = b.flowsetid where a.id = $flowid";
				
				$list = $this->model->query($sql);

				if($list) {
					return $list;
				}else{
					return false;
				}
			}else{
				$sql = "select * from erp_flowset a inner join erp_fixedflow b on a.id = b.flowsetid where a.flowtype = {$this->flowtype} and b.city = {$this->city}";

				$list = $this->model->query($sql);

				if($list){
					return true;
				}else{
					return false;
				}
			}
		}
	}
        
?>
