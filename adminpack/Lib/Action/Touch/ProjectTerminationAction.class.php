<?php
class ProjectTerminationAction extends FlowAction{
	public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
			 'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
             
             
        );

        // ��ʼ��������
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('Termination');
        $this->assign('flowType', 'Termination');
    } 
  
	 
 
	public function show(){//����չʾ
		$this->flowId = $_REQUEST['flowId'];
		$this->workFlow->nextstep($this->flowId);
		$flow = M('Erp_flows')->where("ID=".$this->flowId)->find();
		$project_id = $flow['CASEID'];//caseid�ֶ��д�ŵ�����Ŀid
		//$flist = M('Erp_finalaccounts')->where("TYPE=1 and PROJECT=".$project_id)->select();
		$recorId= $_REQUEST['RECORDID'];
		$model = M();
		$flist = $model->query("select a.*,to_char(APPDATE,'yyyy-mm-dd hh24:mi:ss') as APPDATE2 from ERP_FINALACCOUNTS a where TYPE=2 and ID = $recorId  ");
		$status_arr = array(0=>'δ�ύ',1=>'�����',2=>'���ͨ��',3=>'δͨ��');
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
			$data['shouru_shiji'] = $projectModel->getCaseInvoiceAndReturned($caseID, $scaleType, 2);//����_ʵ��
			$data['shouru_yugu'] = $oneBudget[0]['SUMPROFIT'];//����_Ԥ��
			$data['xianxia_shiji'] = $projectModel->get_bugcost($caseID) + $projectModel->caseSignNoPay($caseID,2);//ʵ�����·���
			$data['xianxia_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM'];//���³ɱ�Ԥ��
			$data['fuxianlirun_shiji'] = floatval($data['shouru_shiji']) - floatval($data['xianxia_shiji']);//ʵ�� ��������
			$data['fuxianlirun_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM_PROFIT'];
			$data['fuxianlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'], $data['shouru_shiji']);//ʵ�� ����������
			$data['fuxianlirunlv_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM_PROFIT_RATE'];
			$data['zhlirunlv_shiji'] = $projectModel->get_prjdata($caseID, 10);//ʵ�� �ۺ�������
			$data['zhlirunlv_yugu'] = $oneBudget[0]['ONLINE_COST_RATE'];
			$data['ZHAD_SHIJI'] = $value['ZHAD_SHIJI'];//ʵ�� �ۺ���
			$data['zhad_yugu'] = $projectModel->get_vadcost($caseID);//Ԥ�� �ۺ����
			if ($data['ZHAD_SHIJI'] !== null) {
				$data['zhlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'] - $data['ZHAD_SHIJI'], $data['shouru_shiji']); // ʵ�� �ۺ�������
			} else {
				$data['zhlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'] - $data['zhad_yugu'], $data['shouru_shiji']); // ʵ�� �ۺ�������
			}
			$data['status'] = $value['STATUS'];
			$flist[$key]['jsdata'] = $data;

			$this->menu['map_js_'.$key] = array(
                'name' => 'juesuan_'.$key,
                'text' => $btype['YEWU'].'��ֹ'
            );
			$this->menu['map_jsb_'.$key] = array(
                'name' => 'juesuanbiao_'.$key,
                'text' => $btype['YEWU'].'��Ŀ��ֹ��'
            );
			$ADDTIME = $value['APPDATE2'];
			$project_id = $value['PROJECT'];
			  
		}
		$this->menu['opinion'] = array(
                'name' => 'opinion',
                'text' =>  '�������'
            );
		//$this->menu = $temp;
		if(!$flow){
			$this->assign('recordId', $flist[$key]['ID']);
			$this->assign('CASEID', $project_id);
		}
		$this->assignWorkFlows($this->flowId);
		$this->assign('project', $project);
		$this->assign('title', '��Ŀ��ֹ');
		$this->assign('ADDTIME', $ADDTIME);
        $this->assign('flist', $flist);
        $this->assign('menu', $this->menu);  // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
		$this->display('show');
	}
	public function opinionFlow(){//����ҵ����
			
		$prjId = $_REQUEST['prjid'] ?$_REQUEST['prjid'] :$_REQUEST['CASEID'];
 
		//$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
		//$type = $_REQUEST['FORMTYPE'] ? $_REQUEST['FORMTYPE']:17;
		 
		$recordId = $_REQUEST['RECORDID'];
			
		 
        
        //������ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;
        
       $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );
		if ($this->myTurn) {  // �ǵ�ǰ�������Ǹõ�¼�û�
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
            $response['message'] = '�ǵ�ǰ������';
        }

        echo json_encode(g2u($response));  
	}
	/**
     * ����������
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