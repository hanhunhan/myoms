<?php

class newWorkFlow{

	protected $model;
	public $nowtime;
	public $user;
	public $role;
	public $flowtype;
	public $city;

    private $needCCFromUsers = array(2144, 269, 4715, 524, 278, 2145);
    private $needCCToUsers = array(277);
    private $_conf_flow_status = array(
        1=>"δ��ʼδ����",
        2=>"������",
        3=>"���",
        4=>"���"
    );

	public function __construct()
	{
		$this->model = new Model();
		$this->user = $_SESSION['uinfo']['uid'];
		$this->nowtime = date('Y-m-d H:i:s');
		$this->role = $_SESSION['uinfo']['role'];
		$this->city = $_SESSION['uinfo']['city'];
	}

	//�½�����Ȩ��
	public function start_authority($type)
	{
		$this->flowtype = $type;
		$fixed = $this->judgeFlowType();

		if($fixed)
		{
			return true;
		}
		else
		{
			$sql = "select a.FLOWSTART,b.ID from erp_flowrole a
			left join erp_flowset b on a.FLOWSETID = b.ID
			left join erp_flowtype c on  b.FLOWTYPE = c.ID
			where c.PINYIN = '{$type}'";
			//echo $sql;die;
			$auth = $this->model->query($sql);

			if($auth[0]['FLOWSTART'] && in_array($this->role,explode(',',$auth[0]['FLOWSTART'])))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		exit;
	}

	//�½�����
	public function createworkflow($data)
	{
		$data['DEAL_INFO'] = str_replace(PHP_EOL, '<br>', $data['DEAL_INFO']);
		//�˿����Ա�ڴ���ʱ�����ʼ�
		$data['step'] = 1;
		$type = $data['type']?$data['type']:$this->flowtype;
		$sql = "select a.id from erp_flowset a left join erp_flowtype b on a.flowtype = b.id where b.pinyin = '{$type}'";
		$flowSet = $this->model->query($sql);
		$flowsetid = $flowSet[0]['ID'];//��������

		if(!$flowsetid) return false;

		//$this->model->startTrans();

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
        $insert['ISNONCASH'] = isset($data['ISNONCASH'])?$data['ISNONCASH']:'';

		$flowid = M('Erp_flows')->add($insert);

		$sql = "insert into erp_flownode(FLOWID,DEAL_USERID,E_TIME,DEAL_INFO,STEP,STATUS,FILES,ISMALL,ISPHONE)VALUES($flowid,{$this->user},'{$this->nowtime}','{$data['DEAL_INFO']}',1,3,'{$data['FILES']}',{$data['ISMALL']},{$data['ISPHONE']})";

		$affect = $this->model->execute($sql);

		$sql = "insert into erp_flownode(FLOWID,DEAL_USERID,S_TIME,STEP,STATUS)VALUES($flowid,{$data['DEAL_USERID']},'{$this->nowtime}',2,1)";
		$affected = $this->model->execute($sql);
		$sign = $this->beginOperate($flowsetid,$data['CASEID'],$data['RECORDID'],$data['ACTIVID']);

		$content = $this->typeDistinction($data,$flowid);
		if($flowid && $affect && $affected && $sign){

            $dealUser = $this->getDealUserInfo($data['DEAL_USERID']);
			if($data['ISPHONE']==-1)$this->send_Mobile_Message($data['ISPHONE'],$dealUser['PHONE'],$dealUser['CITY'],$type,$content['PROCITY'],$content['USERNAME'],$content['PROJECTNAME']);

			if($data['ISMALL']==-1 || $this->shouldSendOAMail() || $data['type'] == "tksq")$this->send_OA_Mail($flowid,$type,$data);


			return $flowid;
		}else{
			//$this->model->rollback();
			return false;
		}
	}

    private function shouldSendOAMail() {
        return in_array($_REQUEST['DEAL_USERID'], $this->needCCFromUsers);
    }

	//�������
	public function nextstep($flowid){

		if($_GET['operate'] != 'view')
		{//���̲鿴
			$lastworkflow = $this->model->execute("update erp_flownode set STATUS = 4 where FLOWID = $flowid and STATUS = 3 ");

			$last = $this->model->execute("update erp_flownode set STATUS= 2 where FLOWID = $flowid and DEAL_USERID = {$this->user} and STATUS = 1");
		}
		return true;
	}

	//��һ��
	public function handleworkflow($data){
		$data['DEAL_INFO'] = str_replace(PHP_EOL, '<br>', $data['DEAL_INFO']);
		$step = $this->getmaxstep($data['flowId'])+1;
		//$this->model->startTrans();

		$last = $this->model->execute("update erp_flownode set STATUS=3,E_TIME='{$this->nowtime}',DEAL_INFO = '{$data['DEAL_INFO']}',FILES= '{$data['FILES']}',ISMALL = {$data['ISMALL']},ISPHONE = {$data['ISPHONE']} where FLOWID = {$data['flowId']} and STATUS = 2 and DEAL_USERID = {$this->user}");

		$next = $this->model->execute("insert into erp_flownode(FLOWID,DEAL_USERID,S_TIME,STEP,STATUS)VALUES({$data['flowId']},{$data['DEAL_USERID']},'{$this->nowtime}',$step,1)");

		$flows = $this->model->execute("update erp_flows set MAXSTEP = {$step} where ID = {$data['flowId']}"); // echo  '-'.$last .'-'.$next .'-'. $flows;

		$content = $this->typeDistinction($data);

		if($last && $next && $flows ){

			//$this->model->commit();

			$flowInfo = get_Flows_Info($data['flowId']);
            $dealUser = $this->getDealUserInfo($data['DEAL_USERID']);  // ��ȡת���˵���Ϣ

			if($data['ISPHONE']==-1)$this->send_Mobile_Message($data['ISPHONE'],$dealUser['PHONE'],$dealUser['CITY'],$flowInfo['type'],$content['PROCITY'],$content['USERNAME'],$content['PROJECTNAME']);

			if($data['ISMALL']==-1 || $this->shouldSendOAMail())$this->send_OA_Mail($data['flowId'],$flowInfo['type'],$flowInfo['data']);


			return true;

		}else{

			//$this->model->rollback();
			return false;
		}
	}

	//ͬ��
	public function passWorkflow($data)
	{

		$data['DEAL_INFO'] = str_replace(PHP_EOL, '<br>', $data['DEAL_INFO']);
		$step = $this->getmaxstep($data['flowId'])+1;
		//$this->model->startTrans();
		$last = $this->model->execute("update erp_flownode set STATUS=3,E_TIME='{$this->nowtime}',DEAL_INFO = '{$data['DEAL_INFO']}',FILES= '{$data['FILES']}',ISMALL = '{$data['ISMALL']}',ISPHONE = '{$data['ISPHONE']}' where FLOWID = {$data['flowId']} and STATUS = 2 and DEAL_USERID = {$this->user}");

		$next = $this->model->execute("insert into erp_flownode(FLOWID,DEAL_USERID,S_TIME,STEP,STATUS)VALUES({$data['flowId']},{$data['DEAL_USERID']},'{$this->nowtime}',$step,1)");

		$flows = $this->model->execute("update erp_flows set MAXSTEP = {$step},status = 2 where ID = {$data['flowId']}");

		$sign = $this->passOperate($data['flowId']);

		$content = $this->typeDistinction($data);
		if($last && $next && $flows && $sign){

			//$this->model->commit();

			$flowInfo = get_Flows_Info($data['flowId']);
            $dealUser = $this->getDealUserInfo($data['DEAL_USERID']);  // ��ȡת���˵���Ϣ

			if($data['ISPHONE']==-1)$this->send_Mobile_Message($data['ISPHONE'],$dealUser['PHONE'],$dealUser['CITY'],$flowInfo['type'],$content['PROCITY'],$content['USERNAME'],$content['PROJECTNAME']);

			if($data['ISMALL']==-1 || $this->shouldSendOAMail())$this->send_OA_Mail($data['flowId'],$flowInfo['type'],$flowInfo['data']);
			return true;

		}else{
			//$this->model->rollback();

			return false;
		}
	}

	//���
	public function notWorkflow($data)
	{
		$data['DEAL_INFO'] = str_replace(PHP_EOL, '<br>', $data['DEAL_INFO']);
		//$this->model->startTrans();

		$next = $this->model->execute("update erp_flownode set STATUS = 4,E_TIME='{$this->nowtime}',DEAL_INFO = '{$data['DEAL_INFO']}',ISMALL = '{$data['ISMALL']}',ISPHONE = '{$data['ISPHONE']}', FILES= '{$data['FILES']}' where FLOWID = {$data['flowId']} and STATUS =2");

		$flows = $this->model->execute("update erp_flows set STATUS = 3 where ID = {$data['flowId']}");

		$sign = $this->notOperate($data['flowId']);

		$content = $this->typeDistinction($data);
		if($next && $flows && $sign){
			//$this->model->commit();
			$flowInfo = get_Flows_Info($data['flowId']);
			if($flowInfo['type'] == 'yewujintie'){
				foreach($data['COPY_USER'] as $copyUserId){
					$copyUser = $this->getDealUserInfo($copyUserId);
					if($data['ISPHONE']==-1)$this->send_Mobile_Message($data['ISPHONE'],$copyUser['PHONE'],$copyUser['CITY'],$flowInfo['type'],$content['PROCITY'],$content['USERNAME'],$content['PROJECTNAME']);

				}
			}
            $dealUser = $this->getDealUserInfo($data['DEAL_USERID']);  // ��ȡת���˵���Ϣ
			if($data['ISPHONE']==-1)$this->send_Mobile_Message($data['ISPHONE'],$dealUser['PHONE'],$dealUser['CITY'],$flowInfo['type'],$content['PROCITY'],$content['USERNAME'],$content['PROJECTNAME']);

			if($data['ISMALL']==-1 || $this->shouldSendOAMail())$this->send_OA_Mail($data['flowId'],$flowInfo['type'],$flowInfo['data']);
			return true;

		}else{
			//$this->model->rollback();
			return false;
		}
	}


	//����
	public function finishworkflow($data)
	{
		$data['DEAL_INFO'] = str_replace(PHP_EOL, '<br>', $data['DEAL_INFO']);
		//$this->model->startTrans();

		$next = $this->model->execute("update erp_flownode set STATUS = 4,E_TIME='{$this->nowtime}',DEAL_INFO = '{$data['DEAL_INFO']}',ISMALL = '{$data['ISMALL']}',ISPHONE = '{$data['ISPHONE']}', FILES= '{$data['FILES']}' where FLOWID = {$data['flowId']} and STATUS =2");
 
		$flows = $this->model->execute("update erp_flows set status = 4 where id = {$data['flowId']}");

		$sign = $this->endOperate($data['flowId']);
///var_dump($next );var_dump($flows );var_dump($sign );
		$content = $this->typeDistinction($data);
		if($next && $flows && $sign){
			//$this->model->commit();
			$flowInfo = get_Flows_Info($data['flowId']);
			if($flowInfo['type'] == 'yewujintie'){
				foreach($data['COPY_USER'] as $copyUserId){
					$copyUser = $this->getDealUserInfo($copyUserId);
					if($data['ISPHONE']==-1)$this->send_Mobile_Message($data['ISPHONE'],$copyUser['PHONE'],$copyUser['CITY'],$flowInfo['type'],$content['PROCITY'],$content['USERNAME'],$content['PROJECTNAME']);
				}
			}
            $dealUser = $this->getDealUserInfo($data['DEAL_USERID']);  // ��ȡת���˵���Ϣ
			if($data['ISPHONE']==-1)$this->send_Mobile_Message($data['ISPHONE'],$dealUser['PHONE'],$dealUser['CITY'],$flowInfo['type'],$content['PROCITY'],$content['USERNAME'],$content['PROJECTNAME']);
			if($data['ISMALL']==-1 || $this->shouldSendOAMail())$this->send_OA_Mail($data['flowId'],$flowInfo['type'],$flowInfo['data']);
			return true;

		}else{
			//$this->model->rollback();
			return false;
		}

	}

	//�ջ�
	public function recoverFlow($flowid){

		//$this->model->startTrans();

		$delete = $this->model->execute("delete erp_flownode where flowid = $flowid and status = 1");

		$affect = $this->model->execute("update erp_flownode set status =2,deal_info ='',E_TIME='' where flowid = $flowid and deal_userid = {$this->user} and status = 3");

		$step = $this->getmaxstep($flowid);

		$flows = $this->model->execute("update erp_flows set MAXSTEP = $step where ID = $flowid");

		if($affect && $delete && $flows){
			//$this->model->commit();
			return true;
		}else{
			//$this->model->rollback();
			return false;
		}

	}

	//����ͼ
	public function chartworkflow($flowid)
	{
		$sql = "select a.*,b.name from erp_flownode a
				left join erp_users b on a.deal_userid = b.id
				where a.flowid = ".$flowid."ORDER BY a.id ASC";

		$record = $this->model->query($sql);

		return $record;
	}

	//��ȡ�����
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
				$html .= "<td width='5%'>����</td>";
				$html .= "<td width='10%'>������</td>";
				$html .= "<td width='10%'>״̬</td>";
				$html .= "<td width='45%'>�������</td>";
				$html .= "<td width='15%'>��ʼ����</td>";
				$html .= "<td width='15%'>�ύ����</td>";
				$html .= "</tr></thead><tbody>";
					foreach($workflow as $key=>$value){
						if($value['STATUS']=='1'){
							$status = "δ����";
						}elseif($value['STATUS']=='2'){
							$status = "������";
						}else{
							$status = "�Ѱ��";
						}
						$html .= "<tr>";
						$html .= "<td>��{$value['STEP']}��</td>";
						$html .= "<td>{$value['NAME']}</td>";
						$html .= "<td>{$status}</td>";
						$html .= "<td>{$value['DEAL_INFO']}</td>";
						$html .= "<td>{$value['S_TIME']}</td>";
						$html .= "<td>{$value['E_TIME']}</td>";
						$html .= "</tr>";
					}
				$html .= "</tbody></table></div>";
			}

			if($_GET['operate'] !='view'){
			$finish = $this->model->query("select * from erp_flows where id = $flowid and (status = 3 or status = 4)");

			if(!$finish && $workflow){
				$html .= "<div id='ifmulli' class='handle-tab-twolevl'><div class='topline'></div><ul class='twolevelul'><li class='twolevelli on'><a href='javascript:void(0)'>���̰���</a></li></ul></div>";
			}

			if(!$finish){
				$html .= "<div class='caseinfo-table' ><form  method='post' action='{$this->joinUrl(__SELF__,'savedata=1')}' enctype='multipart/form-data' onSubmit='return validateForm()' >";
				$html .= "<table>";
				if(!$flowid){
					$html .= "<tr><td width = '20%' align='center'>����/˵��</td><td><input type='text' datatype='s' name='INFO' placeholder='����˵��' class='form-control' style='width:200px;' /></td></tr>";
				}
				$html .= "<tr><td width='20%' align='center'>ת����</td><td class='copy-to'><input type='text' name='DEAL_USER' id='DEAL_USER' placeholder='ת��������' class='form-control' style='width:200px;' />";

				$html .= "<input type='hidden' name='DEAL_USERID' id='DEAL_USERID' /><input type='hidden' name='PHONE' id = 'PHONE' /><input type='hidden' name='CITY' id = 'CITY' class='form-control' />";

				$roleId = $this->getFixedRole($flowid);
				if($roleId)
				{
					$html .= "<input type='button' value='���' id='fixedUser' class='btn btn-info btn-after-input' onclick = Choose_Fixed_User('".$roleId."'); />";
				}
				$html .= "<input type='hidden' name='roleId' id='roleId' value='{$roleId}' />";
				$html .= "</td></tr>";

				// ����
				if($flowid)
				{
					$flowInfo = get_Flows_Info($flowid);
					$flowType = $flowInfo['type'];
				}
				else
				{
					$flowType = $this->flowtype;
				}

				if($flowType == "lixiangshenqing" or $flowType == "lixiangbiangeng" or $flowType == "dulihuodong" or $flowType == "xiangmuxiahuodong")
				{
					$html .= "<tr><td width='20%' align='center'>������</td><td><input type='text' name='COPY_USERID' id='COPY_USER' placeholder='����������' class='form-control'/>";
				}


				$html .= "<tr><td align='center'>����</td><td><input type='radio' name='ISPHONE' value='-1' checked/>��&nbsp;<input type='radio' name='ISPHONE' value='0'/>��</td></tr>";
				$html .= "<tr><td align='center'>OA�ʼ�</td><td><input type='radio' name='ISMALL' value='-1' checked/>��&nbsp;<input type='radio' name='ISMALL' value='0'/>��</td></tr>";
				$html .= "<tr><td align='center'>�������</td><td><span class='fclos' style='display:inline'><textarea cols='100' rows='4' maxlength='2000' name='DEAL_INFO' class = 'suggestion form-control'></textarea></span></td></tr>";
				$html .= '<tr><td align="center">����</td><td><span class="fclos"></span> <span class="fclos "><input id="FILES" name="FILES" type="file" multiple="true" class="form-control"/></span> <input  name="filesvalue" class="form-control" tfield="FILES" type="hidden" value=""/><script>$(function(){$(\'#FILES\').uploadify({\'uploader\':\'index.php?s=/Upload/save2oracle/\',\'onUploadSuccess\':function(file,data){uploadify_uploadfilelist(file.name,file.size,data,\'FILES\');},\'formData\':{\'timestamp\':\''.time().'\',\'token\':\''.md5('nr234n9i92n2' . time()).'\'}  });});</script> </td></tr>';
				$html .= "</table>";
				$html .= "<div class='handle-btn'><input type='hidden' value='0' name='checksubmitflag' id='checksubmitflag' />";

				$pass = $this->judgeBtnDisplay($flowid,'Pass');
				if($pass){
					$html .= "<input type='submit' value='ͬ&nbsp;&nbsp;��' name='flowPass' class='btn btn-primary' />";
				}
				$not = $this->judgeBtnDisplay($flowid,'Not');
				if($not){
					$html .= "<input type='submit' value='��&nbsp;&nbsp;��' name='flowNot' class='btn btn-primary' />";
				}
				$judgeRole = $this->judgeBtnDisplay($flowid,'Finish');
				if($judgeRole){
					$html .= "<input type='submit' value='��&nbsp;&nbsp;��' name='flowStop' class='btn btn-primary' />";
				}
				$next = $this->judgeBtnDisplay($flowid,'Next');
				if($next){
					$html .= "<input type='submit' value='ת����һ��' name='flowNext' class='btn btn-primary'/>";
				}
				$html .= "</div></form></div>";
			}
			}
			$html .= '<link rel="stylesheet" href="./Public/uploadify/uploadify.css"			  type="text/css" />
					  <link rel="stylesheet" href="./Public/css/flow.css" type="text/css" />
					  <link rel="stylesheet" href="./Public/tokeninput/token-input-facebook.css" type="text/css" />
					  <script type="text/javascript" src="./Public/tokeninput/jquery.tokeninput.js"></script>
					  <script type="text/javascript" src="./Public/uploadify/jquery.uploadify.js"></script>
					  <script type="text/javascript" src="./Public/validform/js/common.js"></script>
					  <script type="text/javascript" src="./Public/js/common.js"></script>
					  <script type="text/javascript" src="./Public/validform/js/flow.js"></script><script type="text/javascript" src="./Public/layer/layer.js"></script>
					  ';
			$html .= "</div>";

			return $html;
	}

	public function judgeBtnDisplay($flowid='',$action)//Passͬ��Not���Finish����Next��һ��
	{
		$fixed = $this->judgeFlowType($flowid); //�ж��Ƿ��ǹ̶���
		//var_dump($fixed);
		if($fixed){//�̶������
			$steps = explode(',',$fixed[0]['FLOWCURRENT']);
			if($action == 'Pass'){
				return false;
			}elseif($action == 'Not'){

				if($flowid)
				{
					return true;
				}
			}elseif($action == 'Finish'){

				if($flowid)
				{
					if($fixed[0]['MAXSTEP'] == count($steps))
					{
						return true;
					}
				}
			}else{
				if($flowid)
				{
					if($fixed[0]['MAXSTEP'] < count($steps)){
						return true;
					}
				}
				else{
					return true;
				}
			}

		}else{//������
			$data = $this->executeSql($flowid);

			if($action == 'Pass'){
				// �ж��Ƿ���ͬ��
				if($flowid)
				{
					$is_Pass = M("Erp_flows")->where("ID = ".$flowid." AND STATUS = 2")->find();

					if(!$is_Pass)
					{
						if($data['FLOWPASS']&&in_array($this->role,explode(',',$data['FLOWPASS'])))
						{
							return true;
						}
					}

				}
				else
				{
					if($data['FLOWPASS']&&in_array($this->role,explode(',',$data['FLOWPASS']))){
						return true;
					}
				}

			}elseif($action == 'Not'){
				$is_Pass = M("Erp_flows")->where("ID = ".$flowid." AND STATUS = 2")->find();
				if(!$is_Pass && $data['FLOWNOT'] && in_array($this->role,explode(',',$data['FLOWNOT']))){
					return true;
				}
			}elseif($action == 'Finish'){
				if($data['FLOWEND'] && in_array($this->role,explode(',',$data['FLOWEND']))){
					return true;
				}
			}else{
				return true;
			}
		}

		return false;
	}

	public function joinUrl($url,$vars){
		$info =  parse_url($url);
		// ��������
		if(is_string($vars)) { // aaa=1&bbb=2 ת��������
			parse_str($vars,$vars);
		}elseif(!is_array($vars)){
			$vars = array();
		}

		if(isset($info['query'])) { // ������ַ������� �ϲ���vars
			parse_str($info['query'],$params);//var_dump($params);
			unset($params['s']);
			$vars = array_merge($params,$vars);

		}
		$str = http_build_query( $vars);
		$ljstr = strstr($url,'?') ?  '&' :'?';
		$arr = explode('&',$url);
		return $arr[0].$ljstr.$str;
	}

	//�½���ִ�в���
	public function beginOperate($flowsetid,$caseid,$recordid,$activid){
		if($flowsetid){
			$sql = "select * from erp_flowset where id = {$flowsetid}";
			$record = $this->model->query($sql);
			//var_dump($record);die;
			//$this->model->startTrans();
			$sign = true;

			if($record){
				if($record[0]['STARTSQL']){
					foreach(explode(";",$record[0]['STARTSQL']) as $key=>$val){
						if($val && strstr($val,"%RECORDID%")){
							$sql = str_replace("%RECORDID%",$recordid,$val);

							$record = $this->model->execute($sql);

							if(!$record){
								$sign = false;
							}
						}
						if($val && strstr($val,"%CASEID%")){
							$sql = str_replace("%CASEID%",$caseid,$val);

							$record = $this->model->execute($sql);

							if(!$record){
								$sign = false;
							}
						}
						if($val && strstr($val,"%ACTIVID%")){
							$sql = str_replace("%ACTIVID%",$activid,$val);
							$record = $this->model->execute($sql);
							if(!$record){
								$sign = false;
							}
						}
					}
				}
			}
			if($sign){
				//$this->model->commit();
				return true;
			}else{
				//$this->model->rollback();
				return false;
			}
		}
	}

	//������ִ�в���
	public function endOperate($flowid){
		if($flowid){
			$sql =
			"select a.caseid,a.recordid,a.activid,b.* from erp_flows a
			left join erp_flowset b on a.flowsetid = b.id
			where a.id = {$flowid}";
			$record = $this->model->query($sql);
			//$this->model->startTrans();
			$sign = true;
			if($record){
				if($record[0]['ENDSQL']){
					$RECORDID = $record[0]['RECORDID'];
					$CASEID = $record[0]['CASEID'];
					$ACTIVID = $record[0]['ACTIVID'];
					foreach(explode(";",$record[0]['ENDSQL']) as $key=>$val){
						if($val && strstr($val,"%RECORDID%") ){
							$sql = str_replace("%RECORDID%",$RECORDID,$val);

							$record = $this->model->execute($sql);
							if(!$record){
								$sign = false;
							}
						}
						if( $val && strstr($val,"%CASEID%")){
							$sql = str_replace("%CASEID%",$CASEID,$val);

							$record = $this->model->execute($sql);
							if(!$record){
								$sign = false;
							}
						}
						if( $val && strstr($val,"%ACTIVID%")){
							$sql = str_replace("%ACTIVID%",$ACTIVID,$val);
							$record = $this->model->execute($sql);
							if(!$record){
								$sign = false;
							}
						}
					}
				}
			}
			if($sign){
				//$this->model->commit();
				return true;
			}else{
				//$this->model->rollback();
				return false;
			}
		}
	}
	//ͬ���ִ�в���
	public function passOperate($flowid){
		if($flowid){
			$sql =
			"select a.caseid,a.recordid,a.activid,b.* from erp_flows a
			left join erp_flowset b on a.flowsetid = b.id
			where a.id = {$flowid}";
			$record = $this->model->query($sql);

			//$this->model->startTrans();
			$sign = true;
			if($record){
				if($record[0]['PASSSQL']){

					$RECORDID = $record[0]['RECORDID'];
					$CASEID = $record[0]['CASEID'];
					$ACTIVID = $record[0]['ACTIVID'];
					foreach(explode(";",$record[0]['PASSSQL']) as $key=>$val){
						if($val && strstr($val,"%RECORDID%")){
							$sql = str_replace("%RECORDID%",$RECORDID,$val);

							$record = $this->model->execute($sql);
							if(!$record){
								$sign = false;
							}
						}
						if($val && strstr($val,"%CASEID%")){
							$sql = str_replace("%CASEID%",$CASEID,$val);

							$record = $this->model->execute($sql);
							if(!$record){
								$sign = false;
							}
						}
						if($val && strstr($val,"%ACTIVID%")){
							$sql = str_replace("%ACTIVID%",$ACTIVID,$val);
							$record = $this->model->execute($sql);
							if(!$record){
								$sign = false;
							}
						}
					}
				}
			}

			if($sign){
				//$this->model->commit();
				return true;
			}else{
				//$this->model->rollback();
				return false;
			}
		}
	}
	//�����ִ�в���
	public function notOperate($flowid){
		if($flowid){

			$sql =
			"select a.caseid,a.recordid,a.activid,b.* from erp_flows a
			left join erp_flowset b on a.flowsetid = b.id
			where a.id = {$flowid}";
			$record = $this->model->query($sql);

			//$this->model->startTrans();
			$sign = true;
			if($record){
				if($record[0]['NOTSQL']){
					//$notSql = explode(";",$record[0]['NOTSQL']);
					$RECORDID = $record[0]['RECORDID'];
					$CASEID = $record[0]['CASEID'];
					$ACTIVID = $record[0]['ACTIVID'];

						foreach(explode(";",$record[0]['NOTSQL']) as $key=>$val){
							if($val && strstr($val,"%RECORDID%")){
								$sql = str_replace("%RECORDID%",$RECORDID,$val);

								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
							if($val && strstr($val,"%CASEID%")){
								$sql = str_replace("%CASEID%",$CASEID,$val);

								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
							if($val && strstr($val,"%ACTIVID%")){
								$sql = str_replace("%ACTIVID%",$ACTIVID,$val);
								$record = $this->model->execute($sql);
								if(!$record){
									$sign = false;
								}
							}
						}
				}
			}
			if($sign){
				//$this->model->commit();
				return true;
			}else{
				//$this->model->rollback();
				return false;
			}
		}
	}
	//���̱ؾ���ɫ
	public function flowPassRole($flowid){
		$type = $this->judgeFlowType($flowid);

		if($type){//�̶�����
			return true;
		}
		$auth = $this->executeSql($flowid);

		if($auth){
			if($auth['FLOWTHROUGH']){//���ñؾ���ɫ�����
				$sql = "select b.roleid from erp_flownode a
					   left join erp_users b on a.deal_userid = b.id
					   where a.flowid = {$flowid} and a.step != 1";
				$records = $this->model->query($sql);

				if($records)
				{
					foreach($records as $key=>$val)
					{
						if(in_array($val['ROLEID'],explode(',',$auth['FLOWTHROUGH'])))
						{
							return true;
						}
					}
				}
			}else{//δ����

				return true;
			}
		}

		return false;

	}
	//�������� judgeflowsql�ж�����
	public function executeSql($flowid){
		$sql =
		"select b.id,b.judgeflowsql,a.recordid,a.caseid from erp_flows a
		left join erp_flowset b on a.flowsetid = b.id
		where a.id = {$flowid}";
		$flowSet = $this->model->query($sql);

		if($flowSet)
		{
			$flowSetId = $flowSet[0]['ID'];
			$judgeSql = $flowSet[0]['JUDGEFLOWSQL'];
			$recordId = $flowSet[0]['RECORDID'];
			$caseId = $flowSet[0]['CASEID'];

			$flowRole = $this->model->query("select * from erp_flowrole where flowsetid = {$flowSetId}");//��ȡ���̽�ɫ

			if($judgeSql)
			{
				if(strstr($judgeSql,"%RECORDID%"))
				{
					$flowsql = str_replace("%RECORDID%",$recordId,substr($judgeSql,0,-1));
				}
				if(strstr($judgeSql,"%CASEID%"))
				{
					$flowsql = str_replace("%CASEID%",$caseId,substr($judgeSql,0,-1));
				}

				$sqlResult = $this->model->query($flowsql);

				if($flowRole){
					foreach($flowRole as $key=>$val)
					{
						if(isset($val['SIGN']) && isset($val['VALUE']))
						{
							$condition = intval($sqlResult[0]['RESULT']).$val['SIGN'].$val['VALUE'];

							if(eval("return $condition;"))
							{
								return $val;
							}
						}
					}
				}
			}else
			{
				if($flowRole)
				{
					foreach($flowRole as $key=>$val)
					{
						return $val;
					}
				}
			}
		}

		return false;
	}
	//��ȡ�̶�����ɫ
	public function getFixedRole($flowid=''){
		$result = '';

		if($flowid)
		{
			$sql = "select maxstep, flowsetid,city from erp_flows where id= $flowid";

			$flows = $this->model->query($sql);

			if($flows)
			{
				$step = $flows[0]['MAXSTEP'];
				$flowsetid = $flows[0]['FLOWSETID'];

				$sql = "select * from erp_fixedflow where city = '".$flows[0]['CITY']."' and flowsetid = $flowsetid";

				$fixed = $this->model->query($sql);

				if($fixed)
				{
					$roleStep = explode(',',$fixed[0]['FLOWCURRENT']);

					if(count($roleStep) >= ($step+1)){
						$step_ = $step?$step:$step+1;
						$result= $roleStep[$step_];
					}
				}

			}

		}else
		{
			$sql = "select a.id from erp_flowset a
					left join erp_flowtype b on a.flowtype = b.id
					where b.pinyin = '{$this->flowtype}'";

			$flowset = $this->model->query($sql);

			if($flowset){
				$flowSetId = $flowset[0]['ID'];

				$sql = "select * from erp_fixedflow where flowsetid = {$flowSetId} and city = {$this->city}";
				$fixed = $this->model->query($sql);

				if($fixed){
					$roleStep = explode(',',$fixed[0]['FLOWCURRENT']);

					if(count($roleStep) >= 2){
						$result=  $roleStep[1];
					}
				}
			}
		}

		return $result;
	}
	//�ж���������-- ���� -- �̶�
	public function judgeFlowType($flowid = ''){
		if($flowid)
		{
			$sql = "select a.maxstep,b.flowcurrent from erp_flows a
			inner join erp_fixedflow b on a.flowsetid = b.flowsetid
			where a.id = $flowid and b.city = a.city  ";

			$list = $this->model->query($sql);

			if($list) return $list;

		}else
		{
			$sql ="select * from erp_flowtype a
			left join erp_flowset b on a.ID =b.FLOWTYPE
			inner join erp_fixedflow c on b.ID = c.FLOWSETID
			where a.PINYIN = '{$this->flowtype}' and c.city = {$this->city}";
			$list = $this->model->query($sql);
			if($list) return true;

		}

		return false;
	}

	//���Ͷ���
	public function send_Mobile_Message($send,$phone,$city,$flowType,$proCity = '',$userName = '' ,$projectName = '')
	{
		$info = '';
		//����ж����Ŀ���ƣ����ڶ����в���ʾ
		if(is_array($projectName) && !empty($projectName) && count($projectName) == 1){
			$projectName = "��Ŀ���ƣ�$projectName[0]��";
		}else{
			$projectName ='';
		}
		if($send && $phone && $city && $flowType)
		{
			//��ȡ��������
			$flow_Type_Name = M("Erp_flowtype")->where("PINYIN = '".$flowType."'")->getField("FLOWTYPE");

			$msg= "����ϵͳ-������(���ͣ�".$flow_Type_Name."��".$projectName." ���У�".$proCity."�������ˣ�".$userName.")��������,�뼰ʱ����.  ";

			$info = send_sms($msg,$phone,$city);
		}
		return $info;
	}

    /**
     * ��װ����ҵ������
     * @param string $flowId ������ID
     * @param string $flowType ����������
     * @return string
     */
    private function packBizContent($flowId = '', $flowType = '') {
        $response = '';
        if (intval($flowId)) {
            // �Ȼ�ȡ��ʽ
            $style = empty($_REQUEST['style']) ? '' : $_REQUEST['style'];

            // ��ȡҵ������
            $bizHtml = empty($_REQUEST['bizHtml']) ? '' : $_REQUEST['bizHtml'];

            // �޸ı���
            $codeType = mb_detect_encoding($bizHtml, array('UTF-8', 'GBK'));
            if ($codeType == 'UTF-8') {
                $bizHtml = u2g($bizHtml);
            }
            $bizHtml = str_replace('δ����' , '������', $bizHtml);  // ������������״̬���޸�

            if ($flowType == 'jiekuanshenqing') {
                $bizHtml = str_replace('δ�ύ', '�ύ�����', $bizHtml);
            } else {
                $bizHtml = str_replace('δ�ύ', '�����', $bizHtml);
            }
            $workFlowHtml = $this->getWorkFlowHtml($flowId, $applicationHtml);
            // ƴװ������ҵ������
            if (strstr($bizHtml, 'name="application"') === false && strstr($bizHtml, "name='application'") === false) {
                $response = $style . $applicationHtml . $bizHtml . $workFlowHtml;
            } else {
                $response = $style . $bizHtml . $workFlowHtml;
            }
        }

        return $response;
    }

	//����OA�ʼ�
	public function send_OA_Mail($flowid,$flowtype,$data)
	{
		$info = '';

		if($flowtype == 'lixiangshenqing' ) {
			$content = D("House")->get_House_Info_Html($flowid,$data['RECORDID'],$flowtype);
		} elseif ($flowtype == 'lixiangbiangeng') {
			$content = D("House")->get_House_Info_Html($flowid,$data['CASEID'],$flowtype,$data['RECORDID']);
		} elseif ($flowtype == 'dulihuodong' || $flowtype == "xiangmuxiahuodong") {
			$content = D("House")->get_Activ_Info_Html($flowid,$data['CASEID'],$flowtype,$data['RECORDID']);
		} else {
            // ��ȡҵ������
            $content = $this->packBizContent($flowid, $flowtype);
		}

		//��ȡ��������
		$flow_Type_Name = M("Erp_flowtype")->where("PINYIN = '".$flowtype."'")->getField("FLOWTYPE");

        if ($_REQUEST['ISMALL'] == -1) {  // ���ѡ������Ҫ�����ʼ�
            $to = get_UserName_User_ID($_REQUEST['DEAL_USERID']);
            $subject = "��������-����ϵͳ-" . $flow_Type_Name . "-" . $data['INFO'];
            if (!empty($_REQUEST['COPY_USERID'])) {  // ����������б�Ϊ��
                if ($this->shouldSendOAMail()) {
                    // ���ϳ���ָ��������������ϲ��������б�
                    $ccUserIds = array_merge(explode(',', (string)$_REQUEST['COPY_USERID']), $this->needCCToUsers);
                } else {
                    // ����ֻ��ȡָ���������б�
                    $ccUserIds = explode(',', (string)$_REQUEST['COPY_USERID']);
                }
            } else {
                // �����б�Ϊ��
                if ($this->shouldSendOAMail()) {
                    $ccUserIds = $this->needCCToUsers;
                }
            }

			//�����鳭���ʼ�
			/*if (!empty($_REQUEST['COPY_USERGROUP'])) {
					if ($this->shouldSendOAMail()) {
						// ���ϳ���ָ��������������ϲ��������б�
						$ccUserGroupIds = array_merge(explode(',', (string)$_REQUEST['COPY_USERGROUP']), $this->needCCToUsers);
					} else {
						// ����ֻ��ȡָ���������б�
						$ccUserGroupIds = explode(',',(string)$_REQUEST['COPY_USERGROUP']);
						foreach($ccUserGroupIds as $ccUserGroupId){
							$ccGroupIds = M("Erp_group_flow")->where("ID=".$ccUserGroupId)->getField('GROUPUSERID');
							if(is_array($ccUserIds) && count($ccUserIds) > 0){
								$ccUserIds = array_unique(array_merge(explode(',',(string)$ccGroupIds),$ccUserIds));
							}else{
								$ccUserIds = array_merge(explode(',',(string)$ccGroupIds));
							}


						}

					}
			} else {
				// �����б�Ϊ��
					if ($this->shouldSendOAMail()) {
						$ccUserIds = $this->needCCToUsers;
					}
			}*/


            foreach ($ccUserIds as $copyid) {
                $toid[] = get_UserName_User_ID($copyid);
            }
            $copy_uids = implode(',', $toid);

            if (empty($copy_uids)) {
                $copy_uids = null;
            }
        } else {
            // ���ѡ���˲������ʼ�����һ��ת�����������Ҫת����Ƶ���ϵ���б���
            if ($this->shouldSendOAMail()) {
                $to = $this->needCCToUsers[0];  // ���͸�ָ���ռ���
            }
        }



		//�˿������һ��ת��������ʱ��ֱ�ӷ��͸�ָ��������Ա
		if($flowtype == "tksq" && $data['step'] == 1){
			$subject = "��������-����ϵͳ-" . $flow_Type_Name . "-" . $data['INFO'];
			$too = get_UserName_User_ID($_REQUEST['DEAL_USERID']);
			$tksq_uids = 520; //�����÷

			if($this->city == 1) {
				$msg = oa_notice('chenmei', $_SESSION['uinfo']['uname'], $subject, $content, '');
			}

		}
		//var_dump($copy_uids);
		//
		if ($to) {
            $info = oa_notice($to, $_SESSION['uinfo']['uname'], $subject, $content, $copy_uids);
        } else {
            if ($toid[0]) {
                $info = oa_notice($toid[0], $_SESSION['uinfo']['uname'], $subject, $content, $copy_uids);
            }
        }

		return $info;
	}

	// ��ȡ���̷����˵ĳ���
	public function get_Flow_Creator_Info($flowid){
		$result = array();

		if($flowid)
		{
			$result = M("Erp_flows")->where("ID=$flowid")->find();
		}

		return $result;
	}

    private function getDealUserInfo($userId) {
        $response = array();
        if (intval($userId) > 0) {
            $sql = <<<USER_INFO_SQL
                SELECT T.PHONE,
                       C.PY AS CITY
                FROM ERP_USERS T LEFT
                JOIN ERP_CITY C ON C.ID = T.CITY
                WHERE T.ID = %d
USER_INFO_SQL;

            $result = D()->query(sprintf($sql, $userId));
            if (is_array($result) && count($result)) {
                $response = $result[0];
            }
        }

        return $response;
    }

    private function getWorkFlowHtml($flowid = '', &$applicationHtml)
    {
        Vendor('Oms.workflow');
        $workflow = new workflow();
        $applicationHtml = '';  // Ĭ��Ϊ��

        $flowStep = $workflow->chartworkflow($flowid);
        $headHtml = '<div class="panel panel-primary"><div class="panel-heading">��������ͼ</div><div class="table-responsive"><table class="table table-bordered">';
        $tailHtml = '</table></div></div>';
        $html = '';
        if($flowStep) {
            $flowInfo = $fileHtml = '';
            foreach($flowStep as $step)
            {
                if($step['FILES'])
                {
                    $attach = explode(',',$step['FILES']);
                    foreach($attach as $key=>$val)
                    {
                        if($val)
                        {
                            $fileInfo = explode('-',$val);
                            $filecode= $fileInfo[0];
                            $filesize= $fileInfo[2];
                            unset($fileInfo[count($fileInfo) - 1]);
                            unset($fileInfo[0]);
                            $filename= implode('-', $fileInfo);

                            $fileHtml .= '<a target="_blank" href="'.C('DOMAIN_NAME').'/index.php?s=/Upload/showfile&filecode='.$filecode.'">'.$filename.'</a><br/>';
                        }
                    }
                }

                $flowInfo .= '<tr><td width="20%" align="center">��'.$step["STEP"].'</span>��</td>
				<td width="70%"><div style="text-align:left;">'.$this->_conf_flow_status[$step['STATUS']].'<br/>�������:'.$step["DEAL_INFO"].'<br/><small>';

                if($step["S_TIME"])
                {
                    $flowInfo .= "��ʼʱ�䣺".$step["S_TIME"];
                }

                if($step["E_TIME"])
                {
                    $flowInfo .= "����ʱ�䣺".$step["E_TIME"];
                }

                $flowInfo .= "�����ˣ�".$step['NAME']."</small></div></td></tr>";

            }

            if (count($flowStep) == 2) {
                $application = $this->getFlowRecord($flowid);
                if (notEmptyArray($application)) {
                    $applicationHtml = '<div class="panel panel-primary"><a name="application"></a><div class="panel-heading"><i class="glyphicon glyphicon-comment"></i>&nbsp;����˵��</div><div class="panel-body"><p>'.$application['INFO'].'</p><p class="text-right">�����ˣ�'.$application['USER_NAME'].'</p></div></div>';
                }
            }

            $html .= $headHtml . $flowInfo . "<tr><td width='20%'>�����б�</td><td width='70%' style='text-align:left;'>".$fileHtml."</td></tr>" . $tailHtml;
        }

        return $html;
    }

    private function getFlowRecord($flowId) {
        $response = array();
        if (intval($flowId)) {
            $sql = <<<FLOW_RECORD_SQL
                SELECT F.*, U.NAME AS USER_NAME
                FROM ERP_FLOWS F
                LEFT JOIN ERP_USERS U ON U.ID = F.ADDUSER
                WHERE F.ID = %d
FLOW_RECORD_SQL;
            $result = D()->query(sprintf($sql, $flowId));
            if (is_array($result) && count($result)) {
                $response = $result[0];
            }
        }

        return $response;
    }

	/**
	 * ��������
	 * @param $data
	 * @return $content��������
	 */
	private function typeDistinction($data,$flowId =""){
		if($data['type'] == 'jiekuanshenqing') {
			$sql = "select PROJECTNAME from erp_loanapplication where id =" . $data['RECORDID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key] = $pros['PROJECTNAME'];
			}
		}else if($data['type'] == 'jianmianshenqing') {
			$sql = "select a.PRJ_NAME from erp_member_discount_detail b left join erp_cardmember a on a.id=b.mid where b.list_id = " . $data['RECORDID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key] = $pros['PRJ_NAME'];
			}
		}else if($data['type'] == 'dulihuodong' or $data['type'] == 'dulihuodongbiangeng' or $data['type'] == 'xiangmuxiahuodongbiangeng'
				or $data['type'] == 'xiangmuxiahuodong'){
				$sql = "select PROJECTNAME from erp_project where id =".$data['CASEID'];
				$proArr = D()->query($sql);
				foreach ($proArr as $key=>$pros) {
					$projectName[$key] = $pros['PROJECTNAME'];
				}
		}else if($data['type'] == 'tksq') {
			$sql = "select a.PRJ_NAME from erp_cardmember a left join erp_member_refund_detail b on a.id=b.mid where b.list_id = " . $data['RECORDID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key]= $pros['PRJ_NAME'];
			}
		}else if($data['type'] == 'projectset' or $data['type'] == 'lixiangshenqing') {
			$sql = "select e.PRO_NAME as PRJ_NAME from erp_house e where PROJECT_ID  = " . $data['RECORDID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key]= $pros['PRJ_NAME'];
			}
		}else if($data['type'] == 'lixiangbiangeng') {
			$sql = "select e.PRO_NAME as PRJ_NAME from erp_house e where PROJECT_ID  = " . $data['CASEID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key]= $pros['PRJ_NAME'];
			}
		}else if($data['type'] == 'huiyuantuipiao') {
			$sql = "select PRJ_NAME from erp_cardmember c left join erp_invoice_recycle_detail d on c.id = d.mid where d.list_id = " . $data['RECORDID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key]= $pros['PRJ_NAME'];
			}
		}else if($data['type'] == 'projectchange' or $data['type'] == 'finalaccounts' or $data['type'] == 'Termination' or $data['type'] == 'xiangmujuesuan'
			or $data['type'] == 'xiangmuzhongzhi')  {
			$sql = "select PROJECTNAME from erp_project where id=".$data['CASEID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key]= $pros['PROJECTNAME'];
			}
		}else if($data['type'] == 'xiaomifengchaoe' or $data['type'] == 'PurchasingBee')  {
			$sql = "select PROJECTNAME  from erp_project p left join erp_case c on p.ID= c.PROJECT_ID left join erp_purchase_list l on l.case_id = c.id
			where l.id=".$data['RECORDID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key]= $pros['PROJECTNAME'];
			}
		}else if($data['type'] == 'dianzibilichaoe')  {
			$sql = "select PROJECTNAME  from erp_project p left join erp_case c on p.ID= c.PROJECT_ID left join erp_reimbursement_detail d on d.case_id = c.id
			left join erp_reimbursement_list l on l.id = d.list_id
			where l.id=".$data['RECORDID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key]= $pros['PROJECTNAME'];
			}
		}else{
			$sql = "select PROJECTNAME from erp_project p left join erp_case c on c.PROJECT_ID = p.ID where c.id=".$data['CASEID'];
			$proArr = D()->query($sql);
			foreach ($proArr as $key=>$pros) {
				$projectName[$key]= $pros['PROJECTNAME'];
			}
		}
			$userid = $this->user;
			$sql = "select name from erp_users where id =" . $userid;
			$userName = D()->query($sql);
			if($flowId != ""){
				$cityNo = M("Erp_flows")->where("id=".$flowId)->getfield("city");
			}else{
				$cityNo = M("Erp_flows")->where("id=".$data['flowId'])->getfield("city");
			}
			$city = M("Erp_city")->where("id=".$cityNo)->getfield("name");
			$content['PROCITY'] = $city;
			$content['USERNAME'] = $userName[0]['NAME'];
			$content['PROJECTNAME'] = $projectName;
		return $content;

	}

}
        
?>
