<?php
class FinalaccountsAction extends FlowAction{
	public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
			 'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
             
            
             
        );
		$this->message = array(
			'-6' => 'δ�����ؾ���ɫ',
		);

        // ��ʼ��������
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('finalaccounts');
        $this->assign('flowType', 'finalaccounts');
    } 
  
	 
 
	public function show(){//����չʾ
		$this->flowId = $_REQUEST['flowId'];
		$this->workFlow->nextstep($this->flowId);
		$flow = M('Erp_flows')->where("ID=".$this->flowId)->find();
		$project_id = $flow['CASEID'];//caseid�ֶ��д�ŵ�����Ŀid
		$recorId= $_REQUEST['RECORDID'];
		//$flist = M('Erp_finalaccounts')->where("TYPE=1 and PROJECT=".$project_id)->select();
		//$scaleType = D('ProjectCase')->where('ID = ' . $caseid)->getField('SCALETYPE');  // ��Ŀ����
		$model = M();
		$sql = "select a.*,to_char(APPDATE,'yyyy-mm-dd hh24:mi:ss') as APPDATE2 from ERP_FINALACCOUNTS a where TYPE=1 and  ID = $recorId ";
		$flist = $model->query($sql);
		$status_arr = array(0=>'δ�ύ',1=>'�����',2=>'���ͨ��',3=>'δͨ��');
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

			$adCost = $value['ZHAD_SHIJI'] ?  $value['ZHAD_SHIJI'] : $prj->get_vadcost($caseid);  // ������
            $offlineCost = $prj->get_bugcost($caseid);  // ���·���
				 
			$prjbudget = $model->query("select t.*,to_char(FROMDATE,'yyyy-mm-dd') as FROMDATE,to_char(UNDOTIME,'yyyy-mm-dd') as UNDOTIME  from ERP_PRJBUDGET t where CASE_ID='$caseid' "); 
			$data = array(); 
		   $data['FROMDATE'] = $prjbudget[0]['FROMDATE'];
            $data['UNDOTIME'] = $prjbudget[0]['UNDOTIME'];
            $data['caiwuyushou'] = $prj->getCaseAdvances($caseid, $scaleType, 2);  // ����Ԥ��
            $data['yikaipiaohk'] = $prj->getCaseInvoiceAndReturned($caseid, $scaleType, 2);  // �ؿ�����
            $data['w_yibaoxiaofy'] = $prj->getCaseCost($caseid, 1);  // ���ʽ���ѱ�������
            $data['w_yifswbxfy'] = $prj->getCaseCost($caseid, 3);  // ���ʽ���ѷ���δ��������
            $data['z_yibaoxiaofy'] = $prj->getFundPoolAmount($caseid, $scaleType, 1);  // �ʽ���ѱ�������
            $data['z_yifswbxfy'] = $prj->getFundPoolAmount($caseid, $scaleType, 2);  // �ʽ���ѷ���δ��������

			$data['tobepaid_yewu'] = $value['TOBEPAID_YEWU'];  // ʵ�ʹ���
			$data['tobepaid_fundpool'] = $value['TOBEPAID_FUNDPOOL'];  // ʵ�ʹ���
			$data['z_tax'] = $value['Z_TAX'];  // ʵ�ʹ���
			$data['Z_counterfee'] = $value['Z_COUNTERFEE'];  // ʵ�ʹ���


            $data['z_yichongdi'] = $value['OFFSET_COST'];  // �ѳ�ַ���
            $data['fuxianlirun'] = $data['yikaipiaohk'] - $offlineCost -$data['tobepaid_yewu']-$data['tobepaid_fundpool'];
            $data['fuxianlirunlv'] = $this->getProfitRate($data['fuxianlirun'], $data['yikaipiaohk']);
            $data['lirunlv'] = $this->getProfitRate($data['fuxianlirun'] - $adCost, $data['yikaipiaohk']);
            $data['lixiangyugushouru'] = $prjbudget[0]['SUMPROFIT'];
            $data['lixiangyugufuxianlirunlv'] = $prjbudget[0]['OFFLINE_COST_SUM_PROFIT_RATE'];
			$data['ZHAD_SHIJI'] = $value['ZHAD_SHIJI'];


			$data['tobepaid_yewu'] = $value['TOBEPAID_YEWU'];  // ʵ�ʹ���
			$data['tobepaid_fundpool'] = $value['TOBEPAID_FUNDPOOL'];  // ʵ�ʹ���
			$data['z_tax'] = $value['Z_TAX'];  // ʵ�ʹ���
			$data['Z_counterfee'] = $value['Z_COUNTERFEE'];  // ʵ�ʹ���


			$flist[$key]['jsdata'] = $data;

			$this->menu['map_js_'.$key] = array(
                'name' => 'juesuan_'.$key,
                'text' => $btype['YEWU'].'����'
            );
			$this->menu['map_jsb_'.$key] = array(
                'name' => 'juesuanbiao_'.$key,
                'text' => $btype['YEWU'].'��Ŀ�����'
            );
			
			$ADDTIME = $value['APPDATE2'];
			$project_id =$value['PROJECT'];
			  
		}
		$this->menu['opinion'] = array(
                'name' => 'opinion',
                'text' =>  '�������'
            );
		//$this->menu = $temp;
		$this->assignWorkFlows($this->flowId);
		if(!$flow){
			$this->assign('recordId', $flist[$key]['ID']);
			$this->assign('CASEID', $project_id);
		}
		$this->assign('project', $project);
		$this->assign('title', '����');
        $this->assign('flist', $flist);
		$this->assign('ADDTIME', $ADDTIME);
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
				$response['message'] = g2u($this->message[$result]);
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