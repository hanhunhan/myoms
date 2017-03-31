<?php
class FlowAction extends ExtendAction{
	public $noauth = 1;
	/*�ϲ���ǰģ���URL����*/
    public $_merge_url_param = array();

	private $uid = 0;
	private $uname = '';
	private $tname = '';
	private $group_name = '';
	private $city_id = 0;

	public function __construct(){
		$this->uid = intval($_SESSION['uinfo']['uid']);
		$this->uname = trim($_SESSION['uinfo']['uname']);
		$this->tname = trim($_SESSION['uinfo']['tname']);
		$this->city_id = intval($_SESSION['uinfo']['city']);
		$this->group_name = trim($_SESSION['uinfo']['group_name']);

		parent::__construct();
	}

	/**
	 * --------------------------------
	 * --------------------------------
	 * ---------��������---------------
	 * --------------------------------
	 * --------------------------------
	 */
	public function flowList(){

		//���ܲ���
		$status = isset($_GET['status'])?intval($_GET['status']):1;
		$pagesize = 10;
		$page = isset($_GET['page'])?intval($_GET['page']):1;
		$act = isset($_GET['act'])?trim($_GET['act']):'';

		//����״̬
		$flow_status = array(
			1=>'������',
			2=>'��ͬ��',
			3=>'�ѷ��',
			4=>'�ѱ���',
		);

		//ajax��ȡ��ҳ��Ϣ
		if($act=='getPageList'){
			$ret = array(
				'status'=>false,
				'msg'=>'',
				'data'=>null,
			);

			//��ȡ����
			$flowListData = $this->getFlowlist(0,$status,($page-1)*$pagesize,$page*$pagesize);

			if(!empty($flowListData)){
				//��ɫ��ֵ
				$flow_color = D("Flowtype")->get_status_color();
				foreach($flowListData as $key=>$val){
					$flowListData[$key]['COLOR'] = $flow_color[$val['PINYIN']];
					$flowListData[$key]['FLOW_STATUS'] = $flow_status[$val['STATUS']];
				}

				$ret['status'] = true;
				$ret['data'] = $flowListData;
			}

			die(@json_encode(g2u($ret)));
		}

		$viewData = array();

		$cityModel = D("City");
		$city_info = $cityModel->get_city_info_by_id($this->city_id);
		//title
		$viewData['title'] = "��˹�����";
		//�û���Ϣ
		$viewData['user']['city'] = $city_info['NAME'];
		$viewData['user']['dept'] = $this->group_name;
		$viewData['user']['time'] = date("Y��m��d�� H:i:s",time());
		//����������
		$typeList = M('erp_flowtype')->select();
		$viewData['typeList'] = $typeList;
		//������״̬
		$status_arr = array(
			'1'=>'��������',
			'2'=>'��������',
			'3'=>'��������',
		);
		$viewData['status'] = $status_arr;

		//��������ɫ
		$flow_color = D("Flowtype")->get_status_color();
		$viewData['flowColor'] = $flow_color;

		//����������
		$flowListData = $this->getFlowlist(0,$status,0,10);

		if(!empty($flowListData)){
			foreach($flowListData as $key=>$val){
				$flowListData[$key]['COLOR'] = $flow_color[$val['PINYIN']];
				$flowListData[$key]['FLOW_STATUS'] = $flow_status[$val['STATUS']];
			}
		}

		$viewData['flowListData'] = $flowListData;
		$this->assign('viewData',$viewData);
		//��ǰ״̬
		$this->assign('status',$status);
		$this->display('Flow:flow_list');
	}

	/**
	 * ���չ�����
	 */
	public function recycle(){

		//���ղ���
		$flowId = $_REQUEST['flowId'];

		$ret = array(
			'status'=>false,
			'msg'=>'',
			'data'=>null,
		);

		Vendor('Oms.newWorkFlow');
		$workflow = new newWorkFlow();
		//���մ���
		D()->startTrans();
		$affect = $workflow->recoverFlow($flowId);

		if ($affect) {
			$ret['status'] = true;
			D()->commit();
		}
		else
		{
			D()->rollback();
		}

		die(@json_encode(g2u($ret)));
	}

	/**
	 * @param $flowType ����������
	 * @param $flowStatus  ������״̬
	 * @param $begin  ��ҳ��ʼ��
	 * @param $end	��ҳ������
	 * @return array|mixed
	 */
	private function getFlowlist($flowType,$flowStatus,$begin,$end){
		$fowList = array();

		switch($flowStatus) {
			case "1":
				$flowSql = <<<FLOWSQL
					SELECT F.ID,F.CITY,F.CITYNAME,F.INFO,F.MAXSTEP,to_char(F.ADDTIME,'YYYY-MM-DD') AS ADDTIME,F.FLOWTYPE,F.PINYIN,F.NAME,F.DEPTID,G.ID AS NODEID,G.DEAL_USERID,F.STATUS
					FROM
					(SELECT B.*,C.FLOWTYPE,C.PINYIN,E.NAME,E.DEPTID,CY.NAME AS CITYNAME FROM ERP_FLOWSET A
					  LEFT JOIN ERP_FLOWS B ON A.ID= B.FLOWSETID
					  LEFT JOIN ERP_FLOWTYPE C ON A.FLOWTYPE = C.ID
					  LEFT JOIN ERP_USERS E ON E.ID=B.ADDUSER
					  LEFT JOIN ERP_CITY CY ON CY.ID = B.CITY
					  ) F
					INNER JOIN ERP_FLOWNODE G ON F.ID = G.FLOWID
					WHERE (F.STATUS=1 OR F.STATUS=2) AND (G.STATUS = 1  OR G.STATUS=2)
					AND G.DEAL_USERID = %d
					ORDER BY F.ID DESC
FLOWSQL;
				break;
			case "2":
				$flowSql = <<<FLOWSQL
					(SELECT F.ID,F.CITY,F.CITYNAME,F.INFO,F.MAXSTEP,to_char(F.ADDTIME,'YYYY-MM-DD') AS ADDTIME,F.FLOWTYPE,F.PINYIN,F.NAME,F.DEPTID,G.DEAL_USERID,min(F.STATUS) AS STATUS,MAX(G.E_TIME) AS E_TIME
					FROM
					(SELECT
					B.*,C.FLOWTYPE,E.NAME,E.DEPTID,C.PINYIN,CY.NAME AS CITYNAME
					FROM ERP_FLOWSET A
					LEFT JOIN ERP_FLOWS B ON A.ID= B.FLOWSETID
					LEFT JOIN ERP_FLOWTYPE C ON A.FLOWTYPE = C.ID
					LEFT JOIN ERP_USERS E ON E.ID=B.ADDUSER
					LEFT JOIN ERP_CITY CY ON CY.ID = B.CITY
					) F
					LEFT JOIN ERP_FLOWNODE G ON F.ID = G.FLOWID
					WHERE (G.STATUS = 3 OR G.STATUS = 4) AND G.DEAL_USERID = %d
					GROUP BY G.DEAL_USERID, F.ID,F.CITY,F.CITYNAME,F.INFO,F.MAXSTEP,F.ADDTIME,F.FLOWTYPE,F.NAME,F.DEPTID,F.PINYIN
					ORDER BY E_TIME DESC NULLS LAST
					)
FLOWSQL;
				break;
			case "3":
				$flowSql = <<<FLOWSQL
				(SELECT
				  F.ID,F.CITY,F.CITYNAME,F.INFO,F.MAXSTEP,to_char(F.ADDTIME,'YYYY-MM-DD') AS ADDTIME,F.FLOWTYPE,F.NAME,F.DEPTID,G.DEAL_USERID,F.STATUS,MAX(G.E_TIME) AS E_TIME,F.PINYIN
				  FROM
				  (SELECT
				   B.*,C.FLOWTYPE,E.NAME,E.DEPTID,C.PINYIN,CY.NAME AS CITYNAME
				   FROM ERP_FLOWSET A
				   LEFT JOIN ERP_FLOWS B ON A.ID= B.FLOWSETID
				   LEFT JOIN ERP_FLOWTYPE C ON A.FLOWTYPE = C.ID
				   LEFT JOIN ERP_USERS E ON E.ID=B.ADDUSER
				   LEFT JOIN ERP_CITY CY ON CY.ID = B.CITY
				   ) F
				   LEFT JOIN ERP_FLOWNODE G ON F.ID = G.FLOWID
				   WHERE (F.STATUS = 3 OR F.STATUS = 4) AND G.DEAL_USERID = %d
				   GROUP BY G.DEAL_USERID, F.ID,F.CITY,F.CITYNAME,F.INFO,F.MAXSTEP,F.ADDTIME,F.FLOWTYPE,F.NAME,F.DEPTID,F.STATUS,F.PINYIN
				   ORDER BY E_TIME DESC NULLS LAST
				   )
FLOWSQL;
				break;
		}

		$flowSql = sprintf($flowSql,$this->uid);

		$fields =  '*';
		$wheresql = '';
		$bandanspager="select $fields from ($flowSql) $wheresql " ;
		$sql=" SELECT * FROM
		(
			SELECT A.*, rownum r
			FROM
			( ".$bandanspager.") A
			WHERE rownum <= $end
		 ) B
		WHERE r > $begin";

		$fowList = D()->query($sql);

		return $fowList;
	}
}
?>