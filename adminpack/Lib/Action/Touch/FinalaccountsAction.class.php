<?php
class FinalaccountsAction extends FlowAction{
	public function __construct() {
        parent::__construct();

        // 初始化菜单
        $this->menu = array(
			 'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
             
            
             
        );
		$this->message = array(
			'-6' => '未进过必经角色',
		);

        // 初始化工作流
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('finalaccounts');
        $this->assign('flowType', 'finalaccounts');
    } 
  
	 
 
	public function show(){//数据展示
		$this->flowId = $_REQUEST['flowId'];
		$this->workFlow->nextstep($this->flowId);
		$flow = M('Erp_flows')->where("ID=".$this->flowId)->find();
		$project_id = $flow['CASEID'];//caseid字段中存放的是项目id
		$recorId= $_REQUEST['RECORDID'];
		//$flist = M('Erp_finalaccounts')->where("TYPE=1 and PROJECT=".$project_id)->select();
		//$scaleType = D('ProjectCase')->where('ID = ' . $caseid)->getField('SCALETYPE');  // 项目类型
		$model = M();
		$sql = "select a.*,to_char(APPDATE,'yyyy-mm-dd hh24:mi:ss') as APPDATE2 from ERP_FINALACCOUNTS a where TYPE=1 and  ID = $recorId ";
		$flist = $model->query($sql);
		$status_arr = array(0=>'未提交',1=>'审核中',2=>'审核通过',3=>'未通过');
		foreach($flist as $key=>$value){
			$project = M('Erp_project')->where("ID=".$value['PROJECT'])->find();
			$city = M('Erp_city')->where("ID=".$value['CITY'])->find();
			$user = M('Erp_users')->where("ID=".$value['APPLICANT'])->find();
			$btype = M('Erp_businessclass')->where("ID=".$value['BTYPE'])->find();
			
			
			 
			$flist[$key]['PROJECT'] = $project['PROJECTNAME'];
			$flist[$key]['CITY'] = $city['NAME'];
			$flist[$key]['BTYPE'] = $btype['YEWU'];
				
			$scaleType  = $value['BTYPE'];
			$flist[$key]['APPLICANT'] = $user['NAME'];
			$flist[$key]['STATUS'] = $status_arr[$value['STATUS'] ];

			$prj = D('Project');
			$caseid = $value['CASE_ID'];

			$adCost = $value['ZHAD_SHIJI'] ?  $value['ZHAD_SHIJI'] : $prj->get_vadcost($caseid);  // 广告费用
            $offlineCost = $prj->get_bugcost($caseid);  // 线下费用
				 
			$prjbudget = $model->query("select t.*,to_char(FROMDATE,'yyyy-mm-dd') as FROMDATE,to_char(UNDOTIME,'yyyy-mm-dd') as UNDOTIME  from ERP_PRJBUDGET t where CASE_ID='$caseid' "); 
			$data = array(); 
		   $data['FROMDATE'] = $prjbudget[0]['FROMDATE'];
            $data['UNDOTIME'] = $prjbudget[0]['UNDOTIME'];
            $data['caiwuyushou'] = $prj->getCaseAdvances($caseid, $scaleType, 2);  // 财务预收
            $data['yikaipiaohk'] = $prj->getCaseInvoiceAndReturned($caseid, $scaleType, 2);  // 回款收入
            $data['w_yibaoxiaofy'] = $prj->getCaseCost($caseid, 1);  // 非资金池已报销费用
            $data['w_yifswbxfy'] = $prj->getCaseCost($caseid, 3);  // 非资金池已发生未报销费用
            $data['z_yibaoxiaofy'] = $prj->getFundPoolAmount($caseid, $scaleType, 1);  // 资金池已报销费用
            $data['z_yifswbxfy'] = $prj->getFundPoolAmount($caseid, $scaleType, 2);  // 资金池已发生未报销费用

			$data['tobepaid_yewu'] = $value['TOBEPAID_YEWU'];  // 实际广告费
			$data['tobepaid_fundpool'] = $value['TOBEPAID_FUNDPOOL'];  // 实际广告费
			$data['z_tax'] = $value['Z_TAX'];  // 实际广告费
			$data['Z_counterfee'] = $value['Z_COUNTERFEE'];  // 实际广告费


            $data['z_yichongdi'] = $value['OFFSET_COST'];  // 已冲抵费用
            $data['fuxianlirun'] = $data['yikaipiaohk'] - $offlineCost -$data['tobepaid_yewu']-$data['tobepaid_fundpool'];
            $data['fuxianlirunlv'] = $this->getProfitRate($data['fuxianlirun'], $data['yikaipiaohk']);
            $data['lirunlv'] = $this->getProfitRate($data['fuxianlirun'] - $adCost, $data['yikaipiaohk']);
            $data['lixiangyugushouru'] = $prjbudget[0]['SUMPROFIT'];
            $data['lixiangyugufuxianlirunlv'] = $prjbudget[0]['OFFLINE_COST_SUM_PROFIT_RATE'];
			$data['ZHAD_SHIJI'] = $value['ZHAD_SHIJI'];


			$data['tobepaid_yewu'] = $value['TOBEPAID_YEWU'];  // 实际广告费
			$data['tobepaid_fundpool'] = $value['TOBEPAID_FUNDPOOL'];  // 实际广告费
			$data['z_tax'] = $value['Z_TAX'];  // 实际广告费
			$data['Z_counterfee'] = $value['Z_COUNTERFEE'];  // 实际广告费


			$flist[$key]['jsdata'] = $data;

			$this->menu['map_js_'.$key] = array(
                'name' => 'juesuan_'.$key,
                'text' => $btype['YEWU'].'决算'
            );
			$this->menu['map_jsb_'.$key] = array(
                'name' => 'juesuanbiao_'.$key,
                'text' => $btype['YEWU'].'项目决算表'
            );
			
			$ADDTIME = $value['APPDATE2'];
			$project_id =$value['PROJECT'];
			  
		}
		$this->menu['opinion'] = array(
                'name' => 'opinion',
                'text' =>  '意见审批'
            );
		//$this->menu = $temp;
		$this->assignWorkFlows($this->flowId);
		if(!$flow){
			$this->assign('recordId', $flist[$key]['ID']);
			$this->assign('CASEID', $project_id);
		}
		$this->assign('project', $project);
		$this->assign('title', '决算');
        $this->assign('flist', $flist);
		$this->assign('ADDTIME', $ADDTIME);
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
				$response['message'] = g2u($this->message[$result]);
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