<?php
class ProjectTerminationAction extends FlowAction{
	public function __construct() {
        parent::__construct();

        // 初始化菜单
        $this->menu = array(
			 'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
             
             
        );

        // 初始化工作流
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('Termination');
        $this->assign('flowType', 'Termination');
    } 
  
	 
 
	public function show(){//数据展示
		$this->flowId = $_REQUEST['flowId'];
		$this->workFlow->nextstep($this->flowId);
		$flow = M('Erp_flows')->where("ID=".$this->flowId)->find();
		$project_id = $flow['CASEID'];//caseid字段中存放的是项目id
		//$flist = M('Erp_finalaccounts')->where("TYPE=1 and PROJECT=".$project_id)->select();
		$recorId= $_REQUEST['RECORDID'];
		$model = M();
		$flist = $model->query("select a.*,to_char(APPDATE,'yyyy-mm-dd hh24:mi:ss') as APPDATE2 from ERP_FINALACCOUNTS a where TYPE=2 and ID = $recorId  ");
		$status_arr = array(0=>'未提交',1=>'审核中',2=>'审核通过',3=>'未通过');
		foreach($flist as $key=>$value){
			$project = M('Erp_project')->where("ID=".$value['PROJECT'])->find();
			$city = M('Erp_city')->where("ID=".$value['CITY'])->find();
			$user = M('Erp_users')->where("ID=".$value['APPLICANT'])->find();
			$btype = M('Erp_businessclass')->where("ID=".$value['BTYPE'])->find(); 
			$flist[$key]['PROJECT'] = $project['PROJECTNAME'];
			$flist[$key]['CITY'] = $city['NAME'];
			$flist[$key]['BTYPE'] =$btype['YEWU'];
			$scaleType  = $value['BTYPE'];
			$flist[$key]['APPLICANT'] = $user['NAME'];
			$flist[$key]['STATUS'] = $status_arr[$value['STATUS'] ];

			$projectModel = D('Project');
			//$oneAccount = M('Erp_finalaccounts')->where("ID = " . $parentChooseID)->find();
			$caseID = $value['CASE_ID'];
			$scaleType = D('ProjectCase')->where('ID = ' . $caseID)->getField('SCALETYPE');
			$oneBudget = M()->query("
					SELECT t.*,
						   to_char(FROMDATE,'yyyy-mm-dd') AS FROMDATE,
						   to_char(TODATE,'yyyy-mm-dd') AS TODATE
					FROM ERP_PRJBUDGET t
					WHERE CASE_ID='$caseID'
				");
			$data['FROMDATE'] = $oneBudget[0]['FROMDATE'];
			$data['TODATE'] = $oneBudget[0]['TODATE'];
			$data['ZJTIME'] = D('Project')->get_zjtime($value['ID']);
			$data['ZJYUANYIN'] = $value['ZJYUANYIN'];
			$data['shouru_shiji'] = $projectModel->getCaseInvoiceAndReturned($caseID, $scaleType, 2);//收入_实际
			$data['shouru_yugu'] = $oneBudget[0]['SUMPROFIT'];//收入_预估
			$data['xianxia_shiji'] = $projectModel->get_bugcost($caseID) + $projectModel->caseSignNoPay($caseID,2);//实际线下费用
			$data['xianxia_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM'];//线下成本预估
			$data['fuxianlirun_shiji'] = floatval($data['shouru_shiji']) - floatval($data['xianxia_shiji']);//实际 付现利润
			$data['fuxianlirun_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM_PROFIT'];
			$data['fuxianlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'], $data['shouru_shiji']);//实际 付现利润率
			$data['fuxianlirunlv_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM_PROFIT_RATE'];
			$data['zhlirunlv_shiji'] = $projectModel->get_prjdata($caseID, 10);//实际 综合利润率
			$data['zhlirunlv_yugu'] = $oneBudget[0]['ONLINE_COST_RATE'];
			$data['ZHAD_SHIJI'] = $value['ZHAD_SHIJI'];//实际 折后广告
			$data['zhad_yugu'] = $projectModel->get_vadcost($caseID);//预估 折后广告费
			if ($data['ZHAD_SHIJI'] !== null) {
				$data['zhlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'] - $data['ZHAD_SHIJI'], $data['shouru_shiji']); // 实际 综合利润率
			} else {
				$data['zhlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'] - $data['zhad_yugu'], $data['shouru_shiji']); // 实际 综合利润率
			}
			$data['status'] = $value['STATUS'];
			$flist[$key]['jsdata'] = $data;

			$this->menu['map_js_'.$key] = array(
                'name' => 'juesuan_'.$key,
                'text' => $btype['YEWU'].'终止'
            );
			$this->menu['map_jsb_'.$key] = array(
                'name' => 'juesuanbiao_'.$key,
                'text' => $btype['YEWU'].'项目终止表'
            );
			$ADDTIME = $value['APPDATE2'];
			$project_id = $value['PROJECT'];
			  
		}
		$this->menu['opinion'] = array(
                'name' => 'opinion',
                'text' =>  '意见审批'
            );
		//$this->menu = $temp;
		if(!$flow){
			$this->assign('recordId', $flist[$key]['ID']);
			$this->assign('CASEID', $project_id);
		}
		$this->assignWorkFlows($this->flowId);
		$this->assign('project', $project);
		$this->assign('title', '项目终止');
		$this->assign('ADDTIME', $ADDTIME);
        $this->assign('flist', $flist);
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
			//$flow = new Flow('finalaccounts');
			$result = $this->workFlow->doit($_REQUEST);
			if (is_array($result)) {
				$response = $result;
			} else {
				$response['result'] = $result;
				$response['status'] = $result>0?1:0;
			}
		} else {
            $response['message'] = '非当前审批人';
        }

        echo json_encode(g2u($response));  
	}
	/**
     * 计算利润率
     * @param $profit
     * @param $income
     * @return float|void
     */
    private function getProfitRate($profit, $income) {
        if (empty($income) || $income == 0) {
            return;
        }
        return round($profit * 100 / $income, 2);
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