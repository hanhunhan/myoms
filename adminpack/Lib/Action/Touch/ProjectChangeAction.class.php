<?php
class ProjectChangeAction extends FlowAction{
	public function __construct() {
        parent::__construct();

        // 初始化菜单
        $this->menu = array(
			 'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'application2' => array(
                'name' => 'application2',
                'text' => '项目详情'
            ),
            'purchase_detail' => array(
                'name' => 'purchase-detail',
                'text' => '立项预算'
            ),
            'purchase_list' => array(
                'name' => 'purchase-list',
                'text' => '目标分解'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        // 初始化工作流
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('projectchange');
        $this->assign('flowType', 'projectchange');
    } 

	protected $message = array(
		'0'=> '操作失败',
		'1'=>'操作成功',
		 
		'-5'=>'未经过必经角色',
		'-7'=>'无权限',
	);
 
	public function show(){//数据展示
		if($_REQUEST['flowId']){
			$this->flowId = $_REQUEST['flowId'];
            if ($this->myTurn) {
                $this->workFlow->nextstep($this->flowId);
            }

			$flow = M('Erp_flows')->where("ID=".$this->flowId )->find();
			$project_id = $flow['CASEID'];//caseid字段中存放的是项目id
			$cid = $flow['RECORDID'];
		}else{
			$project_id = $_REQUEST['prjid'];
			$cid = $_REQUEST['CID'];
			if($_REQUEST['RECORDID'])
				$cid = intval($_REQUEST['RECORDID']);
		}

		$isMobile = isMobile();
		$project = M('Erp_project')->where("ID=".$project_id)->find(); 
		if($_REQUEST['showtable'] ){

			$content = D("House")->get_House_Info_Html($this->flowId ,$project_id,'lixiangbiangeng',$cid,$_REQUEST['showtable']);
			$this->assign('content',$content);
			$this->assign('isMobile',$isMobile);
			$this->display('show_talbe');
			exit;
		}
		//$budgetlist = M('Erp_budgetsale')->where("PROJECTT_ID=$project_id")->select(); 
		$budgetlist = M()->query("select A.*,B.NAME from ERP_BUDGETSALE A left join ERP_SALEMETHOD  B on A.SALEMETHODID=B.ID where A.PROJECTT_ID=$project_id");  

		Vendor('Oms.Changerecord');
		$changer = new Changerecord();
		$changer->fields = array(
            'SALEMETHODID', 'CUSTOMERS','SETS');

		foreach($budgetlist  as $key=>$one ){
			$temp = array();

			$temp['TABLE'] = 'ERP_BUDGETSALE';
			$temp['BID'] = $one['ID'];//79
			$temp['CID'] = $cid;//53
			$budgetChange = $changer->getRecords($temp);

			$budgetlist[$key]['NAME'] = D("House")->get_Contrast_Data('SALEMETHODID',$one['SALEMETHODID'],$budgetChange['SALEMETHODID']);
			$budgetlist[$key]['CUSTOMERS'] = D("House")->get_Contrast_Data('CUSTOMERS',$one['CUSTOMERS'],$budgetChange['CUSTOMERS']);
			$budgetlist[$key]['SETS'] = D("House")->get_Contrast_Data('SETS',$one['SETS'],$budgetChange['SETS']);
			//$budgetlist[$key]['SETS'] = $one['ID'];
			//$budgetlist[$key] = $newone;
			$CUSTOMERS += $budgetChange['CUSTOMERS']['ISNEW'] ? 0 : $one['CUSTOMERS'];
			$CUSTOMERS2 += $budgetChange['CUSTOMERS']?$budgetChange['CUSTOMERS']['VALUEE']:$one['CUSTOMERS'];
			$SETS += $budgetChange['SETS']['ISNEW'] ? 0 : $one['SETS'];
			$SETS2 += $budgetChange['SETS']?$budgetChange['SETS']['VALUEE']:$one['SETS'];
		}
		$CUSTOMERS = D("House")->get_Contrast_Data_total($CUSTOMERS2,$CUSTOMERS);
		$SETS = D("House")->get_Contrast_Data_total($SETS2,$SETS);
		$this->assign('content',$content);
		$this->assign('project',$project);
		$this->assign('CUSTOMERS',$CUSTOMERS);
		$this->assign('SETS',$SETS);
		$this->assign('budgetlist',$budgetlist);
		$this->assign('flowId',$this->flowId );
		$this->assign('ADDTIME',oracle_date_format($flow['ADDTIME']));
		if ($_REQUEST['flowId']) {
            if (judgeFlowEdit($_REQUEST['flowId'], $_SESSION['uinfo']['uid'])) {//判断是否回到发起人
                $editFlag = 1;//可以编辑状态

            }
        }
		if(!$this->recordId){
			$this->assign('CASEID', $_REQUEST['prjid']);
			$this->assign('RECORDID', $_REQUEST['RECORDID']);
			$this->assign('recordId', $_REQUEST['RECORDID']);
		}
		
		//$this->menu = $temp;
		$this->assignWorkFlows($this->flowId); 
		$this->assign('bizWebEditable', $editFlag); 
		$this->assign('title', '立项变更');
        $this->assign('flist', $flist);
		$this->assign('isMobile',$isMobile);
        $this->assign('menu', $this->menu);  // 菜单
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // 控制按钮显示
		$this->display('show');
	}
	public function opinionFlow(){//流程业务处理
			
		$prjId = $_REQUEST['prjid'] ?$_REQUEST['prjid'] :$_REQUEST['CASEID'];
 
		//$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
		//$type = $_REQUEST['FORMTYPE'] ? $_REQUEST['FORMTYPE']:17;
		 
		$recordId = $_REQUEST['RECORDID'];
			
		 
        
        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;
        
       $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
             'url' => U('Flow/flowList', 'status=1')
        );
		if ($this->myTurn) {  // 非当前审批人是该登录用户
			$_REQUEST = u2g($_REQUEST);
			//Vendor('Oms.Flows.Flow');
			//$flow = new Flow('ProjectChange');
			$result = $this->workFlow->doit($_REQUEST);
			if (is_array($result)) {
				$response = $result;
			} else {
				$response['result'] = $result;
				$response['status'] = $result>0?1:0;
				$response['message'] = g2u($this->message[$result]);
			}
		} else {
            $response['message'] = '非当前审批人';
        }

        echo json_encode($response);
	}

	protected function authMyTurn($flowId) {
        if (intval($flowId) > 0) {
            parent::authMyTurn($flowId);
        } else {
            switch($this->flowType) {

                default:
            }

            $this->myTurn = true;
        }
    }
	  
}
?>