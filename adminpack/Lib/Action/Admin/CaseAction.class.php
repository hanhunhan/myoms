<?php
/*
* ��Ŀ״̬
��״̬ 0
�Ѵ���  1
ִ���� 2
���  3
��Ŀ��� 4
��ֹ  5
����� 6



*/

/**
 * Class CaseAction
 */
class CaseAction extends ExtendAction {
    /**
     * ������ĿȨ��
     */
    const CREATE_CASE = 143;

    /**
     * ����������ť
     */
    const HIDE_ADD_BTN = 2;

    /**
     * �Ƿ�ɲ鿴ͳ����ϢȨ�ޱ���
     */
    const STAT_PERMISSION_ID = 701;

    /**
     * ��ʾ������ť
     */
    const SHOW_ADD_BTN = 1;
    public $_merge_url_param = array();
    public $model;
	private $UserLog;
    /**
     * ���ҷ��ճ�case����
     */
    const SCALETYPE_FWFSC = 8;

    /**
     * �ٷֺ�
     */
    const PERCENT_MARK = '%';

    //���캯��
    public function __construct() {
		Vendor('Oms.UserLog');
		$this->UserLog = UserLog::Init();
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'create_case' => self::CREATE_CASE
        );

        $this->model = new Model();
        parent::__construct();

        //TAB URL����
        //$this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['prjid']) ? $this->_merge_url_param['prjid'] = $_GET['prjid'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['prjid'] = $_GET['CASEID'] : '';
        !empty($_GET['type']) ? $this->_merge_url_param['type'] = $_GET['type'] : '';
        !empty($_GET['flowType']) ? $this->_merge_url_param['flowType'] = $_GET['flowType'] : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : '';
        !empty($_GET['activId']) ? $this->_merge_url_param['activId'] = $_GET['activId'] : '';
        !empty($_GET['parentchooseid']) ? $this->_merge_url_param['parentchooseid'] = $_GET['parentchooseid'] : '';
    }

    function  projectlist() {
        Vendor('Oms.Form');
        $statView = 'STAT_TLFPRJ_MV';
        if (!$this->haspermission(self::STAT_PERMISSION_ID)) {
            $statView = 'STAT_NULL_TLFPRJ';
        }
        $form = new Form();
        $form->initForminfo(113);
//             $form->TABLELAYOUT = 'fixed';
        $arrparam = array(array('1', 'PSTATUS'), array('1', 'BSTATUS'), array('1', 'MSTATUS'), array('1', 'ASTATUS')
        , array('1', 'ACSTATUS'), array('1', 'CPSTATUS'), array('1', 'SCSTATUS'));//��״̬��ɫarray('1','BSTATUS') 1Ϊ���Ͷ�ӦERP_STATUS_TYPE��   BSTATUSΪ��Ҫ����ɫ���ֶ���
        $form->showStatusTable($arrparam);
        $form->compareField = array('CCOUNT');
        $form->CZBTN = array(
            '%PSTATUS%==2' => '<a  href="javascript:void(0);" class="contrtable-link btn btn-danger btn-xs" title="ɾ��" onclick="delproject(this);"><i class="glyphicon glyphicon-trash"></i></a> ',
            '%ASTATUS%==2 and %CCOUNT%<1' => '<a href="javascript:void(0);" class="contrtable-link btn btn-danger btn-xs" title="ɾ��" onclick="delproject(this);"><i class="glyphicon glyphicon-trash"></i></a>',
            '%CPSTATUS%==2 and %CCOUNT%<1' => '<a  href="javascript:void(0);" class="contrtable-link btn btn-danger btn-xs" title="ɾ��" onclick="delproject(this);"><i class="glyphicon glyphicon-trash"></i></a>',
            '%PSTATUS%>2' => '<a class="contrtable-link btn btn-success btn-xs"  href="javascript:void(0);" onclick="viewFlow(this);">����ͼ</a>',
        );
        if (!$this->p_auth_all) {
//                 $form->SQLTEXT = "(select A.*,C.CCOUNT as CCOUNT,D.* from ERP_PROJECT A inner join ( select *
//  from (select a.*,row_number() over(partition by PRO_ID order by isvalid) r from ERP_PROROLE a where a.USE_ID='" . $_SESSION['uinfo']['uid'] . "' and a.ERP_ID<>6) where r=1)  B on A.ID=B.PRO_ID  left join (select  PROJECT_ID,COUNT(PROJECT_ID) as CCOUNT from ERP_INCOME_CONTRACT aa inner join ERP_CASE bb on aa.CASE_ID=bb.ID where bb.SCALETYPE=3 group by PROJECT_ID) C on A.ID=C.PROJECT_ID left join STAT_TLFPRJ_MV D on A.ID=D.CPROJECT_ID where   B.ISVALID=-1 and A.CITY_ID='" . $this->channelid . "' and A.STATUS<>2)";

            $form->SQLTEXT = "
                 (SELECT A.*,
                          C.CCOUNT AS CCOUNT,
                          D.*
                   FROM ERP_PROJECT A
                   INNER JOIN
                     (SELECT *
                      FROM
                        (SELECT a.*,
                                row_number() over(partition BY PRO_ID
                                                  ORDER BY isvalid) r
                         FROM ERP_PROROLE a
                         WHERE a.USE_ID='" . $_SESSION['uinfo']['uid'] . "'
                           AND a.ERP_ID<>6)
                      WHERE r=1) B ON A.ID=B.PRO_ID
                   LEFT JOIN
                     (SELECT PROJECT_ID,
                             COUNT(PROJECT_ID) AS CCOUNT
                      FROM ERP_INCOME_CONTRACT aa
                      INNER JOIN ERP_CASE bb ON aa.CASE_ID=bb.ID
                      WHERE bb.SCALETYPE=3
                      GROUP BY PROJECT_ID) C ON A.ID=C.PROJECT_ID
                   LEFT JOIN {$statView} D ON A.ID=D.CPROJECT_ID
                   WHERE B.ISVALID=-1
                     AND A.CITY_ID='{$this->channelid}'
                     AND A.STATUS<>2)
                 ";
        } else {
//                 $form->SQLTEXT = "(select  A.*,C.*,B.CCOUNT as CCOUNT from ERP_PROJECT A left join (select  PROJECT_ID,COUNT(PROJECT_ID) as CCOUNT from ERP_INCOME_CONTRACT aa inner join ERP_CASE bb on aa.CASE_ID=bb.ID where bb.SCALETYPE=3 group by PROJECT_ID) B on A.ID=B.PROJECT_ID  left join STAT_TLFPRJ_MV C on A.ID=C.CPROJECT_ID where  A.STATUS<>2 and A.CITY_ID='" . $this->channelid . "' ) ";
            $form->SQLTEXT = "
                    (SELECT A.*,
                          C.*,
                          B.CCOUNT AS CCOUNT
                    FROM ERP_PROJECT A
                    LEFT JOIN
                     (SELECT PROJECT_ID,
                             COUNT(PROJECT_ID) AS CCOUNT
                      FROM ERP_INCOME_CONTRACT aa
                      INNER JOIN ERP_CASE bb ON aa.CASE_ID=bb.ID
                      WHERE bb.SCALETYPE=3
                      GROUP BY PROJECT_ID) B ON A.ID=B.PROJECT_ID
                    LEFT JOIN {$statView} C ON A.ID=C.CPROJECT_ID
                    WHERE A.STATUS<>2
                     AND A.CITY_ID='{$this->channelid}')
                 ";
        }

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);
        $formhtml = $form->getResult();

        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('form', $formhtml);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('projectlist');

    }

    function createcase() {

        if ($this->_post('checktype')) {
            $arr = explode(',', $this->_post('checktype'));
            $arr = array_filter($arr);
            sort($arr);
            $data = array();
            $data['CITY_ID'] = $_SESSION['uinfo']['city'];//?
            $data['CUSER'] = $_SESSION['uinfo']['uid'];
            $data['ETIME'] = date('Y-m-d H:i:s', time());
            $data['STATUS'] = 0;
            $CTIME = date('Y-m-d H:i:s', time());
            $CUSER = $_SESSION['uinfo']['uid'];
            $acdata = array();
            $budgetdata = array();
            if (count($arr) == 1 && $arr[0] == 'yg') {//Ӳ��
                $data['ASTATUS'] = 2;
                $cdata['CTIME'] = $CTIME;
                $cdata['CUSER'] = $CUSER;
                $cdata['SCALETYPE'] = 3;
                $cdata['FSTATUS'] = 2;//Ӳ��ֱ��ִ��
                $acdata[] = $cdata;
                $jumpurl = U('Advert/contract/', '&is_from=1&CASE_TYPE=yg');

            } elseif (count($arr) == 1 && $arr[0] == 'hd') {//�
                $data['PSTATUS'] = 2;
                $data['ACSTATUS'] = 1;
                $cdata['CTIME'] = $CTIME;
                $cdata['CUSER'] = $CUSER;
                $cdata['SCALETYPE'] = 4;
                $cdata['FSTATUS'] = 6;//���������
                $acdata[] = $cdata;
                $jumpurl = U("Activ/activPro", array('tabNum' => 8, 'flowType' => "lixiangshenqing", 'showOpinion' => 1));
            } elseif (count($arr) == 1 && $arr[0] == 'cp') {//��Ʒ
                $data['CPSTATUS'] = 2;
                $cdata['CTIME'] = $CTIME;
                $cdata['CUSER'] = $CUSER;
                $cdata['SCALETYPE'] = 5;
                $acdata[] = $cdata;
                $jumpurl = U('Case/projectlist');

            } elseif (in_array('fwfsc', $arr)) {
                $data['PSTATUS'] = 2;
                if (count($arr) == 1) {  // �����ķ��ҷ��ճ���Ŀ
                    $data['SCSTATUS'] = 1;
                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = self::SCALETYPE_FWFSC;
                    $cdata['FSTATUS'] = 6;//���������
                    $acdata[] = $cdata;
                    $jumpurl = U('House/projectDetail', array('tabNum' => 23));
                } else if (count($arr) == 2 && in_array('fx', $arr)) {  // ��������ҷ��ճ�����
                    // ���ҷ��ճ�
                    $data['SCSTATUS'] = 1;
                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = self::SCALETYPE_FWFSC;
                    $cdata['FSTATUS'] = 6;// ���������
                    $acdata[] = $cdata;

                    // ����
                    $data['MSTATUS'] = 1;
                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 2;
                    $cdata['FSTATUS'] = 6;//���������
                    $acdata[] = $cdata;
                    $jumpurl = U('House/projectDetail', array('tabNum' => 20));
                } else {  // �������͵������֧��
                    js_alert('�����������޷���ѡ��', U('Case/createcase'));
                    exit();
                }
            } elseif (in_array('ds', $arr) || in_array('fx', $arr)) {
                $data['PSTATUS'] = 2;

                if (in_array('ds', $arr)) {
                    $data['BSTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 1;
                    $cdata['FSTATUS'] = 6;//���������
                    $acdata[] = $cdata;
                    $jumpurl = U('House/projectDetail', array('tabNum' => 20));
                }
                if (in_array('fx', $arr)) {
                    $data['MSTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 2;
                    $cdata['FSTATUS'] = 6;//���������
                    $acdata[] = $cdata;
                    $jumpurl = U('House/projectDetail', array('tabNum' => 20));
                }
                if (in_array('yg', $arr)) {
                    /*$data['ASTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 3;
                    $acdata[] = $cdata;*/
                    js_alert('Ӳ�������������', U('Case/createcase'));
                    exit();
                }
                if (in_array('hd', $arr)) {
                    /*$data['ACSTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 4;
                    $acdata[] = $cdata;*/
                    js_alert('����Ͳ���������', U('Case/createcase'));
                    exit();
                }
                if (in_array('cp', $arr)) {
                    /*$data['CPSTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 5;
                    $acdata[] = $cdata;*/
                    js_alert('�����������޷���ѡ��', U('Case/createcase'));
                    exit();
                }

            } else {
                //if(in_array('yg',$arr)) $data['ASTATUS'] = 2;
                //if(in_array('hd',$arr)) $data['ACSTATUS'] = 2;
                //if(in_array('cp',$arr)) $data['CPSTATUS'] = 2;
                js_alert('�����������޷���ѡ��', U('Case/createcase'));
                exit();
            }
            $model = new Model();
            $model->startTrans();
            $lastid = D('Erp_project')->add($data);

            if ($lastid) {
                foreach ($acdata as $v) {
                    $v['PROJECT_ID'] = $lastid;
                    $status = D('Erp_case')->add($v);

                    if (!$status) {
                        $model->rollback();
                        break;
                    } else {
                        if ($v['SCALETYPE'] == 1 || $v['SCALETYPE'] == 2 || $v['SCALETYPE'] == self::SCALETYPE_FWFSC) {
                            $budgetdata['SCALETYPE'] = $v['SCALETYPE'];
                            $budgetdata['CASE_ID'] = $status;
                            $prjbudgetlastid = D('Erp_prjbudget')->add($budgetdata);
                            if (!$prjbudgetlastid) {
                                $model->rollback();
                                break;
                            }
                        }
                        //����Ȩ��
                        $temp = array();
                        $temp['USE_ID'] = $_SESSION['uinfo']['uid'];
                        $temp['PRO_ID'] = $lastid;
                        $temp['ISVALID'] = -1;
                        $temp['ERP_ID'] = $v['SCALETYPE'];
                        $roleid = M('Erp_prorole')->add($temp);
                        if (!$roleid) {
                            $model->rollback();
                            break;
                        }
                        if ($v['SCALETYPE'] == 1) {
                            $temp['ERP_ID'] = 6;
                            $roleid = M('Erp_prorole')->add($temp);
                            if (!$roleid) {
                                $model->rollback();
                                break;
                            }
                        }


                    }
                }
                //������Ŀ��¼
                $temp = array();
                $temp['PROJECT_ID'] = $lastid;
                $temp['USER_ID'] = $_SESSION['uinfo']['uid'];
                $temp['CTIME'] = date('Y-m-d H:i:s');
                $plres = M('Erp_project_log')->add($temp);
                if (!$plres) {
                    $model->rollback();

                }
                $model->commit();
				$this->UserLog->writeLog($lastid,$_SERVER["REQUEST_URI"],"������Ŀ�ɹ�" ,serialize($_REQUEST));

                js_alert('������Ŀ�ɹ�', $jumpurl . '&prjid=' . $lastid, 1);
                exit();
            } else {
                js_alert('������Ŀʧ��', U('Case/projectlist'), 1);
				$this->UserLog->writeLog($lastid,$_SERVER["REQUEST_URI"],"������Ŀʧ��" ,serialize($_REQUEST));
                exit();
                $model->rollback();
            }

        }
        $this->display('createproject');

    }

    /**
     * ��Ŀ����ҵ��
     */
    function project_finalaccounts() {
        if ($_REQUEST['act'] == 'checkproject') {
            if (1) {//�жϾ�������
                $project = M('Erp_project')->where("ID=" . $this->_get('prjid'))->find();
                if ($project) {
                    $case = M('Erp_case')->where('SCALETYPE in(1,2,8) and PROJECT_ID=' . $project['ID'])->select();
                    $conModel = D("Erp_finalaccounts");
                    $fc = $conModel->where(" TYPE=1 and PROJECT=" . $this->_get('prjid'))->select();
                    $cres = true;
                    if ($fc) {//ֻ�鿴������¼�¼
                        $result['status'] = 'y';
                        $result['info'] = '';
                    } else {
                        $conModel->startTrans();
                        foreach ($case as $v) {
                            $data = array();
                            $data['PROJECT'] = $project['ID'];
                            $data['CITY'] = $project['CITY_ID'];
                            $data['CONTRACT_NUM'] = $project['CONTRACT'];
                            $data['BTYPE'] = $v['SCALETYPE'];
                            $data['CASE_ID'] = $v['ID'];

                            $data['APPLICANT'] = $_SESSION['uinfo']['uid'];
                            $data['APPDATE'] = date('Y-m-d H:i:s'); //var_dump($data);
                            $data['TYPE'] = 1;
                            $data['STATUS'] = 0;
                            $res = $conModel->add($data);
                            if (!$res) {
                                $conModel->rollback();
                                $cres = false;
                                break;
                            }

                        }
                    }
                    if ($cres) {
                        $conModel->commit();
                        $result['status'] = 'y';
                        $result['info'] = '';
						$this->UserLog->writeLog($res,$_SERVER["REQUEST_URI"],"��������ɹ�,��Ŀ��".$data['PROJECT'] ,serialize($_REQUEST));
                    } else {
                        $result['status'] = 'n';
                        $result['info'] = g2u('ʧ��');
						$this->UserLog->writeLog($res,$_SERVER["REQUEST_URI"],"��������ʧ��,��Ŀ��".$data['PROJECT'] ,serialize($_REQUEST));
                    }
                }

            } else {
                $result['status'] = 'n';
                $result['info'] = g2u('�����Ͼ�������');
            }

            echo json_encode($result);
            exit();
        } elseif ($_REQUEST['act'] == 'savezjdata') {
            $pdata['UNDOTIME'] = $this->_post('UNDOTIME');
            $accountData['OFFSET_COST'] = $this->_post('OFFSET_COST');
			$accountData['ZHAD_SHIJI'] = $this->_post('ZHAD_SHIJI');
			$accountData['TOBEPAID_FUNDPOOL'] = $this->_post('TOBEPAID_FUNDPOOL');
			$accountData['TOBEPAID_YEWU'] = $this->_post('TOBEPAID_YEWU');
			$accountData['Z_TAX'] = $this->_post('Z_TAX');
			$accountData['Z_COUNTERFEE'] = $this->_post('Z_COUNTERFEE');
            $finalAccountModel = D('Erp_finalaccounts');
            $finalAccountModel->startTrans();
            $updatedBudget = M('Erp_prjbudget')->where('ID=' . $this->_post('PRJBUDGET_ID'))->save($pdata);
            if ($updatedBudget !== false) {
                $type = $_POST['TYPE'];
                if ($type == 'OFFSET_COST') {
                    if (is_numeric($accountData['OFFSET_COST'])) {
                        $updatedBudget = $finalAccountModel->where("ID = " . $this->_post('FINAL_ACCOUNT_ID'))->save($accountData);
                    } else {
                        $updatedBudget = false;
                    }
                } else if ($type == 'ZHAD_SHIJI') {
                    if (is_numeric($accountData['ZHAD_SHIJI'])) {
                        $updatedBudget = $finalAccountModel->where("ID = " . $this->_post('FINAL_ACCOUNT_ID'))->save($accountData);
                    } else {
                        $updatedBudget = false;
                    }
                }else{
					 $updatedBudget = $finalAccountModel->where("ID = " . $this->_post('FINAL_ACCOUNT_ID'))->save($accountData);

				}
            }

            if ($updatedBudget !== false) {
                $finalAccountModel->commit();
                $result['status'] = 'y';
                $result['info'] = g2u('�ύ�ɹ�');
            } else {
                $finalAccountModel->rollback();
                $result['status'] = 'n';
                $result['info'] = g2u('ʧ��');
            }
            echo json_encode($result);
            exit();
        }

        if ($_REQUEST['ischildren'] == 1) { //����ͳ��
            $prj = D('Project');
            $fc = M('Erp_finalaccounts')->where("ID=" . $this->_get('parentchooseid'))->find();
            $caseid = $fc['CASE_ID'];
            $scaleType = D('ProjectCase')->where('ID = ' . $caseid)->getField('SCALETYPE');  // ��Ŀ����
            $adCost = $fc['ZHAD_SHIJI'] ?$fc['ZHAD_SHIJI']: $prj->get_vadcost($caseid);  // ������
            $offlineCost = $prj->get_bugcost($caseid);  // ���·���
            $m = M();
            $prjbudget = $m->query("select t.*,to_char(FROMDATE,'yyyy-mm-dd') as FROMDATE,to_char(TODATE,'yyyy-mm-dd') as TODATE,to_char(UNDOTIME,'yyyy-mm-dd') as UNDOTIME   from ERP_PRJBUDGET t where CASE_ID='$caseid' ");

            $data['FROMDATE'] = $prjbudget[0]['FROMDATE'];
            $data['UNDOTIME'] = $prjbudget[0]['UNDOTIME'];
            $data['caiwuyushou'] = $prj->getCaseAdvances($caseid, $scaleType, 2);  // ����Ԥ��
            $data['yikaipiaohk'] = $prj->getCaseInvoiceAndReturned($caseid, $scaleType, 2);  // �ؿ�����
            $data['w_yibaoxiaofy'] = $prj->getCaseCost($caseid, 1);  // ���ʽ���ѱ�������
            $data['w_yifswbxfy'] = $prj->getCaseCost($caseid, 3) + $prj->caseSignNoPay($caseid,2);  // ���ʽ���ѷ���δ�������� + �ѿ�Ʊδ�������ã��н�ȣ�
            $data['z_yibaoxiaofy'] = $prj->getFundPoolAmount($caseid, $scaleType, 1);  // �ʽ���ѱ�������
            $data['z_yifswbxfy'] = $prj->getFundPoolAmount($caseid, $scaleType, 2);  // �ʽ���ѷ���δ��������
            $data['z_yichongdi'] = $fc['OFFSET_COST'];  // �ѳ�ַ���
			$data['ZHAD_SHIJI'] = $fc['ZHAD_SHIJI'];  // ʵ�ʹ���
			$data['tobepaid_yewu'] = $fc['TOBEPAID_YEWU'];  // ʵ�ʹ���
			$data['tobepaid_fundpool'] = $fc['TOBEPAID_FUNDPOOL'];  // ʵ�ʹ���
			$data['z_tax'] = $fc['Z_TAX'];  // ʵ�ʹ���
			$data['Z_counterfee'] = $fc['Z_COUNTERFEE'];  // ʵ�ʹ���
            $data['fuxianlirun'] = $data['yikaipiaohk'] - $offlineCost - $data['tobepaid_yewu']-$data['tobepaid_fundpool'];
            $data['fuxianlirunlv'] = $this->getProfitRate($data['fuxianlirun'], $data['yikaipiaohk']);
            $data['lirunlv'] = $this->getProfitRate($data['fuxianlirun'] - $adCost, $data['yikaipiaohk']);
            $data['lixiangyugushouru'] = $prjbudget[0]['SUMPROFIT'];
            $data['lixiangyugufuxianlirunlv'] = $prjbudget[0]['OFFLINE_COST_SUM_PROFIT_RATE'];
            $data['status'] = $fc['STATUS'];
            $data['PRJBUDGET_ID'] = $prjbudget[0]['ID'];
            $data['FINAL_ACCOUNT_ID'] = $this->_get('parentchooseid');  // ��Ŀ�����
            $this->addPercentMark($data);
            $this->assign('data', $data);
            $this->_merge_url_param['prjid'] = $fc['PROJECT'];
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->display('project_finalaccounts_statistics');
        } else {
            $this->project_case_auth($this->_get('prjid'));
            Vendor('Oms.Form');
            $form = new Form();
            if ($_REQUEST['RECORDID']) $wherecond = " and ID=" . $_REQUEST['RECORDID'];
            $form->initForminfo(162);
            $form->where("BTYPE<>7 and TYPE=1 and PROJECT=" . $this->_get("prjid") . $wherecond);
            $children = array(
                array('��Ŀ�����', U('/Case/project_finalaccounts', 'ischildren=1'))
            );
            $form->setChildren($children);
            $form->FKFIELD = 'ID';
            $form = $form->getResult();
            $this->_merge_url_param['FORMTYPE'] = 17;
            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('formtype', $this->_get('formtype'));
            $this->assign('showForm', $this->_get('showForm'));
            $this->assign('RECORDID', $this->_get('RECORDID'));
            $this->display('project_finalaccounts');
        }
    }

    /**
     * ��Ŀ��ֹҵ��
     */
    function project_termination() {
        if ($_REQUEST['act'] == 'checkproject') {
            if (1) {//�ж���ֹ����
                $caseids = $_REQUEST['caseids'];
                $project = M('Erp_project')->where("ID=" . $this->_get('prjid'))->find();
                if ($caseids && $project) {
                    $case = M('Erp_case')->where("ID in( $caseids )")->select();
                    $conModel = D("Erp_finalaccounts");
                    $fc = $conModel->where("TYPE=2 and CASE_ID  in( $caseids ) ")->select();
                    $cres = true;
                    if ($fc) {//ֻ�鿴������¼�¼
                        $result['status'] = 'y';
                        $result['info'] = '';
                    } else {
                        $conModel->startTrans();

                        foreach ($case as $v) {
                            $data = array();
                            $data['PROJECT'] = $project['ID'];
                            $data['CITY'] = $project['CITY_ID'];
                            $data['CONTRACT_NUM'] = $project['CONTRACT'];
                            $data['BTYPE'] = $v['SCALETYPE'];
                            $data['CASE_ID'] = $v['ID'];

                            $data['APPLICANT'] = $_SESSION['uinfo']['uid'];
                            $data['APPDATE'] = date('Y-m-d H:i:s'); //var_dump($data);
                            $data['TYPE'] = 2;//��Ŀ��ֹ
                            $data['STATUS'] = 0;

                            $res = $conModel->add($data);
                            if (!$res) {
                                $conModel->rollback();
                                $cres = false;
                                break;
                            }

                        }
                    }
                    if ($cres) {
                        $conModel->commit();
                        $result['status'] = 'y';
                        $result['info'] = '';
						$this->UserLog->writeLog($res,$_SERVER["REQUEST_URI"],"������ֹ����ɹ�,��Ŀ��".$data['PROJECT'] ,serialize($_REQUEST));
                    } else {
                        $result['status'] = 'n';
                        $result['info'] = g2u('ʧ��');
						$this->UserLog->writeLog($res,$_SERVER["REQUEST_URI"],"������ֹ����ʧ��,��Ŀ��".$data['PROJECT'] ,serialize($_REQUEST));
                    }
                }

            } else {
                $result['status'] = 'y';
                $result['info'] = g2u('��������ֹ����');
            }

            echo json_encode($result);
            exit();
        } elseif ($_REQUEST['act'] == 'canelcase') {
            if (D("Erp_finalaccounts")->where(" ( STATUS=0 or STATUS=3) and  ID=" . $this->_get('fcid'))->delete()) {
                $result['status'] = 'y';
                $result['info'] = g2u('�����ɹ�');
            } else {
                $result['status'] = 'n';
                $result['info'] = g2u('�����ϳ�������');
            }
            echo json_encode($result);
            exit();

        } elseif ($_REQUEST['act'] == 'savezjdata') {
            $data['ZJTIME'] = $this->_post('ZJTIME');
            $data['ZJYUANYIN'] = u2g($_POST['ZJYUANYIN']);
            $data['ZHAD_SHIJI'] = $this->_post('ZHAD_SHIJI');
            $res = M("Erp_finalaccounts")->where("ID=" . $this->_post('FINALACCOUNTS_ID'))->save($data);
            if ($res) {
                $result['status'] = 'y';
                $result['info'] = g2u('�ύ�ɹ�');
            } else {
                $result['status'] = 'n';
                $result['info'] = g2u('ʧ��');
            }
            echo json_encode($result);
            exit();

        } elseif ($_REQUEST['layer'] == 1) {//ѡ�񴰿�
            $project = M('Erp_project')->where("ID=" . $this->_get('prjid'))->find();
            $case = M('Erp_case')->where('PROJECT_ID=' . $project['ID'] . ' and FSTATUS in( 2,4) and SCALETYPE in(1,2,8)')->select();
            foreach ($case as $keyy => $vv) {
                $temp = M('Erp_businessclass')->where('ID=' . $vv['SCALETYPE'])->find();
                if (!M('Erp_finalaccounts')->where('TYPE=2 and CASE_ID=' . $vv['ID'])->find()) {
                    $business[$keyy]['disable'] = '0';
                  
                }else  $business[$keyy]['disable'] = '1';
				$business[$keyy]['ID'] = $temp['ID'];
				$business[$keyy]['YEWU'] = $temp['YEWU'];
				$business[$keyy]['CASEID'] = $vv['ID'];

            } 
            $this->assign('layer', $_REQUEST['layer']);
            $this->assign('business', $business);
            $this->assign('prjid', $this->_get('prjid'));
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->display('project_termination');

        } elseif ($_REQUEST['ischildren'] == 1) {//��ֹ  ����ͳ��
            $data = $this->getFinancialData($this->_get('parentchooseid'));
            $this->addPercentMark($data);
            $this->assign('data', $data);
            $this->assign('paramUrl', $this->_merge_url_param);

            $this->display('project_termination_statistics');
        } else {
            $this->project_case_auth($this->_get('prjid'));
            Vendor('Oms.Form');
            $form = new Form();
            if ($_REQUEST['RECORDID']) $wherecond = " and ID=" . $_REQUEST['RECORDID'];
            $form->initForminfo(162);
            $form->where("TYPE=2 and PROJECT=" . $this->_get("prjid") . $wherecond);
            $children = array(array('��Ŀ��ֹ��', U('/Case/project_termination', 'ischildren=1'))
            );
            $form->setChildren($children);
            $form->GABTN = "<a href='javascript:void(0);' onclick='canelcase();' class='btn btn-info btn-sm'>����</a> <!--a href='javascript:void(0);' onclick='termination_flow();'>�ύ</a-->";
            $form->FKFIELD = 'ID';
            $form = $form->getResult();
            $this->_merge_url_param['FORMTYPE'] = 18;
            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('formtype', $this->_get('formtype'));
            $this->assign('showForm', $this->_get('showForm'));
            $this->assign('prjid', $this->_get('prjid'));
            $this->assign('RECORDID', $this->_get('RECORDID'));
            $this->display('project_termination');
        }
    }

    function project_change() {
        $this->project_case_auth($this->_get('prjid'));//��Ŀҵ��Ȩ���ж�

        if ($this->_get('type') == 2) {//��Ŀ�»���
            $activi = M('Erp_activitics')->where("ID=" . $this->_get('activId'))->find();
            $case = M('Erp_case')->where("ID=" . $activi['CASE_ID'])->find();
            $project_id = $case['PROJECT_ID'];
        } else {
            $project_id = $this->_get('prjid');
        }
        $ccount1 = M('Erp_finalaccounts')->where("PROJECT=$project_id and STATUS in(1,2) and TYPE=1")->count();
        $ccount2 = M('Erp_finalaccounts')->where("PROJECT=$project_id and STATUS in(1,2) and TYPE=2")->count();
        $clist = D('Project')->get_businessclass($project_id);//��Ŀ��ҵ������
        if ($_REQUEST['act'] == 'checkprjChange') {
            // ����Ǵ���Ŀ�����ģ���ɽ���鿴
            if ($this->_request('from') == 'projectList') {
                echo json_encode(array(
                    'status' => 'y',
                    'info' => 'success'
                ));
                exit();
            }
            if ($ccount1 + $ccount2 == count($clist)) {
                $result['status'] = 'n';
                $result['info'] = g2u('��Ŀ�µ�ȫ��ҵ�����;������ֹ���ύ����������ͨ��');
            } elseif ($ccount1 == count($clist)) {
                $result['status'] = 'n';
                $result['info'] = g2u('��Ŀ�µ�ȫ��ҵ�����;������ύ����������ͨ��');
            } elseif ($ccount2 == count($clist)) {
                $result['status'] = 'n';
                $result['info'] = g2u('��Ŀ�µ�ȫ��ҵ��������ֹ���ύ����������ͨ��');
            } else {
                $result['status'] = 'y';
                $result['info'] = 'success';
            }
            echo json_encode($result);
            exit();
        }

        //�������
        if ($_REQUEST['act'] == 'addChange') {
            $type = $_REQUEST['type'];
            if ($type == 2) {
                $project_id = $_REQUEST['activId'];
            } else {
                $project_id = $_REQUEST['prjid'];
            }

            //�ж��Ƿ������
            $isChange = M('Erp_project_change')->where("(STATUS = 0 OR STATUS = 1) AND PROJECT_ID=" . $project_id . " AND TYPE=" . $type)->find();
            if ($isChange) {
                $result['status'] = 'n';
                $result['msg'] = g2u("����Ѵ��ڻ����������,�ݲ������");
            } else {
                $data = array();
                $data['PROJECT_ID'] = $project_id;
                $data['ADATE'] = date("Y-m-d H:i:s");
                $data['APPLICANT'] = $_SESSION['uinfo']['uid'];
                $data['STATUS'] = 0;
                $data['TYPE'] = $type;

                if (D('Erp_project_change')->add($data)) {
                    $result['status'] = 'y';
                    $result['msg'] = g2u('��ӳɹ�');
                } else {
                    $result['status'] = 'n';
                    $result['msg'] = g2u('���ʧ��');
                }
            }
            echo json_encode($result);
            exit;
        }

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(165);

        if ($this->_get('type') == 2) {//��Ŀ�»���
            $form->setMyField('PROJECT_ID', 'LISTSQL', 'select ID,TITLE from ERP_ACTIVITIES ');
        } else {
            $form->setMyField('PROJECT_ID', 'LISTSQL', 'select ID,PROJECTNAME from ERP_PROJECT ');
        }

        $form->CZBTN = array(
            '%STATUS% == 0' => '<a class="contrtable-link btn btn-default btn-sm" onclick="changeDetail(this);" href="javascript:;">�����ϸ</a>',
            '%STATUS% == 1' => '<a class="contrtable-link btn btn-primary btn-sm" onclick="viewDetail(this);" href="javascript:;">�鿴���</a>',
            '%STATUS% == 2' => '<a class="contrtable-link btn btn-primary btn-sm" onclick="viewDetail(this);" href="javascript:;">�鿴���</a>',
            '%STATUS% == 3' => '<a class="contrtable-link btn btn-primary btn-sm" onclick="viewDetail(this);" href="javascript:;">�鿴���</a>');

        //Ȩ���ж�
        if(in_array(448,$this->getUserAuthorities()))
            $form->GCBTN = '<a href="javascript:;" class="btn btn-info btn-sm" onclick="submitFlow(this);">�ύ</a>';

        if ($this->_get('type') == 2) {//��Ŀ�»���
            if ($_REQUEST['flowId']) {
                $formHtml = $form->where("PROJECT_ID = " . $this->_get('activId') . " AND TYPE = " . $this->_get('type') . " AND (STATUS =1 OR STATUS = 2) ")->getResult();
            } else {
                $formHtml = $form->where("PROJECT_ID = " . $this->_get('activId') . " AND TYPE = " . $this->_get('type'))->getResult();
            }

            $this->assign('activId', $_REQUEST['activId']);
        } else {
            if ($_REQUEST['flowId']) {
                $formHtml = $form->where("PROJECT_ID = " . $this->_get('prjid') . " AND TYPE = " . $this->_get('type') . " AND (STATUS =1 OR STATUS = 2)")->getResult();
            } else {
                $formHtml = $form->where("PROJECT_ID = " . $this->_get('prjid') . " AND TYPE = " . $this->_get('type'))->getResult();
            }

            $case = M('Erp_case')->where('SCALETYPE=4 and PROJECT_ID=' . $_REQUEST['prjid'])->find();

            $one = M('Erp_activities')->field('ID')->where('CASE_ID=' . $case['ID'])->find();

            $this->assign('activId', $one['ID']);

        }
        // �Ƿ���ʾ������ť 1=��ʾ��2=����ʾ
        $isShowAddBtn = $this->isShowAddBtn($ccount1, $ccount2, count($clist));
        $this->assign('isShowAddBtn', $isShowAddBtn);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
        $this->assign('form', $formHtml);
        $this->assign('prjid', $_REQUEST['prjid']);
        $this->assign('type', $_REQUEST['type']);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('flowId', $_REQUEST['flowId']);

        $this->display('project_change');
    }

    //������
    function opinionFlow() {

        $prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['CASEID'];
        $flowId = !empty($_REQUEST['flowId']) ? intval($_REQUEST['flowId']) : 0;
        $recordId = !empty($_REQUEST['RECORDID']) ? intval($_REQUEST['RECORDID']) : 0;
        $flowType = $_REQUEST['flowType'] ? $_REQUEST['flowType'] : '';

        if (!$flowId && !$flowType) {
            js_alert();
        }
        Vendor('Oms.workflow');
        $workflow = new workflow();
        Vendor('Oms.Changerecord');
        $changer = new Changerecord();

        if ($flowId > 0) {
            //�����Ѿ����ڵĹ�����
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //��һ��
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                } //ͨ����ť
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {
                        js_alert('ͬ��ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('ͬ��ʧ��');
                    }
                } //�����ť
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {
                        //$project_model = D('Project'); 
                        // $project_model->update_finalaccounts_nopass_status($prjId);


                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('���ʧ��');
                    }
                } //��ֹ��ť
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);

                    if (!$auth) {
                        js_alert('δ�����ؾ���ɫ');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {
                        $CID = $_REQUEST['RECORDID'];
                        $changer->setRecords($CID);

                        $project_model = D('Project');
                        $project_model->set_project_change($prjId);//����������ͳ��
                        //$ress =$project_model->update_finalaccounts_status($prjId);
                        js_alert('�����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                }
                exit;
            }
        } else {
            //����������
            $auth = $workflow->start_authority($flowType);
            if (!$auth) {
                js_alert('����Ȩ��');
            }
            $form = $workflow->createHtml();

            if ($_REQUEST['savedata']) {

                $form = $workflow->createHtml();

                if ($_REQUEST['savedata']) {

                    $str = $workflow->createworkflow($_REQUEST);
                    if ($str) {

                        js_alert('�ύ�ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('�ύʧ��');
                    }
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow');

    }
	 //�����������
    function opinionFlow_final2(){
		//$prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['CASEID'];
        ////������ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        ////����������ҵ��ID
       // $recordId = !empty($_REQUEST['RECORDID']) ?
           // intval($_REQUEST['RECORDID']) : 0;
		$data['url_param'] = $this->_merge_url_param;
		Vendor('Oms.Flows.Flow');
		$flow = new Flow('finalaccounts');
		//$flow->setcType('pc');
		$form = $flow->createHtml($flowId);
		$flow->doit($_REQUEST);
		 
		$this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow_final');

	}
    //�����������
    function opinionFlow_final() {
        $prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['CASEID'];
        //������ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ?
            intval($_REQUEST['RECORDID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();

        if ($flowId > 0) {
            //�����Ѿ����ڵĹ�����
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //��һ��
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                } //ͨ����ť
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {
                        js_alert('ͬ��ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('ͬ��ʧ��');
                    }
                } //�����ť
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {
                        $project_model = D('Project');
                        $project_model->update_finalaccounts_nopass_status($recordId);


                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('���ʧ��');
                    }
                } //��ֹ��ť
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);
                    if (!$auth) {
                        js_alert('δ�����ؾ���ɫ');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {
                        $project_model = D('Project');
                        $ress = $project_model->update_finalaccounts_status($recordId);
                        // echo $prjId;var_dump($ress);

                        if ($_REQUEST['ISPHONE'] && $_REQUEST['PHONE']) {
                            $msg = "����ϵͳ:����һ������������";
                            send_sms($msg, $_REQUEST['PHONE'], $_REQUEST['CITY']);
                        }

                        if ($_REQUEST['ISMALL']) {
                            $subject = "����ϵͳ:����������";
                            $content = "����һ������Ĺ�����,�뼰ʱ����";
                            oa_notice($_SESSION['uinfo']['uid'], $_REQUEST['DEAL_USERID'], $subject, $content);
                        }
                        js_alert('�����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                }
            }
        } else {
            //����������
            $auth = $workflow->start_authority('xiangmujuesuan');
            if (!$auth) {
                //js_alert("����Ȩ��");
            }
            $form = $workflow->createHtml();

            if ($_REQUEST['savedata']) {


                if ($recordId) {
                    $project_model = D('Project');
                    $fstatus = $project_model->get_finalaccounts_status($recordId);
                    if ($fstatus == 0 || $fstatus == 3) {

                        $flow_data['type'] = 'xiangmujuesuan';//$type;
                        $flow_data['CASEID'] = $prjId;
                        $flow_data['RECORDID'] = $recordId;
                        $flow_data['INFO'] = strip_tags($_POST['INFO']);
                        $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                        $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                        $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                        $flow_data['FILES'] = $_POST['FILES'];
                        $flow_data['ISMALL'] = intval($_POST['ISMALL']);
                        $flow_data['ISPHONE'] = intval($_POST['ISPHONE']);

                        $str = $workflow->createworkflow($flow_data);

                        if ($str) {
                            //�ύ..����

                            //$project_model = D('Project');
                            $project_model->update_finalaccounts_check_status($recordId);

                            js_alert('�ύ�ɹ�', U('Case/opinionFlow_final', $this->_merge_url_param));

                            exit;
                        } else {

                            js_alert('�ύʧ��', U('Case/opinionFlow_final', $this->_merge_url_param));

                            exit;
                        }
                    } else {

                        js_alert('�벻Ҫ�ظ��ύ', U('Case/opinionFlow_final', $this->_merge_url_param));

                        exit;
                    }
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow_final');
    }

    //��Ŀ��ֹ�������
    function opinionFlow_termination() {

        $prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['CASEID'];

        //$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
        $type = 18;

       // $recordId = $_REQUEST['RECORDID'];

        //��������
        // $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'tksq'")->find();
        // $type = !empty($workflow_type_info['ID']) ? intval($workflow_type_info['ID']) : 0;
        if (!$type) {
            js_alert('���������Ͳ�����');
        }

        //������ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ?
            intval($_REQUEST['RECORDID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();


        if ($flowId > 0) {
            //�����Ѿ����ڵĹ�����
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //��һ��
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                } //ͨ����ť
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {


                        js_alert('ͬ��ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('ͬ��ʧ��');
                    }
                } //�����ť
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {
                        $project_model = D('Project');
                        $project_model->update_termination_nopass_status($recordId);


                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('���ʧ��');
                    }
                } //��ֹ��ť
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);

                    if (!$auth) {
                        js_alert('δ�����ؾ���ɫ');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {


                        $project_model = D('Project');
                        $project_model->update_termination_status($recordId);

                        if ($_REQUEST['ISPHONE'] && $_REQUEST['PHONE']) {
                            $msg = "����ϵͳ:����һ������������";
                            send_sms($msg, $_REQUEST['PHONE'], $_REQUEST['CITY']);
                        }

                        if ($_REQUEST['ISMALL']) {
                            $subject = "����ϵͳ:����������";
                            $content = "����һ������Ĺ�����,�뼰ʱ����";
                            oa_notice($_SESSION['uinfo']['uid'], $_REQUEST['DEAL_USERID'], $subject, $content);
                        }
                        js_alert('�����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                }
                exit;
            }
        } else {
            //����������
            $auth = $workflow->start_authority($type);
            if (!$auth) {
                //js_alert("����Ȩ��");
            }
            $form = $workflow->createHtml();

            if ($_REQUEST['savedata']) {
 

                if ($prjId && $recordId) {
                    $project_model = D('Project');
                    $tstatus = $project_model->get_termination_status($recordId);
                    if ($tstatus == 0 || $tstatus == 3) {

                        $flow_data['type'] = 'xiangmuzhongzhi';//$type;
                        $flow_data['CASEID'] = $prjId;
                        $flow_data['RECORDID'] = $recordId;

                        $flow_data['INFO'] = strip_tags($_POST['INFO']);
                        $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                        $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                        $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                        $flow_data['FILES'] = $_POST['FILES'];
                        $flow_data['ISMALL'] = intval($_POST['ISMALL']);
                        $flow_data['ISPHONE'] = intval($_POST['ISPHONE']);

                        $str = $workflow->createworkflow($flow_data);
 
                        if ($str) {
                            //�ύ..����

                            //$project_model = D('Project');

                            $project_model->update_termination_check_status($recordId);
                            js_alert('�ύ�ɹ�', U('Case/opinionFlow_termination', $this->_merge_url_param));

                            exit;
                        } else {

                            js_alert('�ύʧ��', U('Case/opinionFlow_termination', $this->_merge_url_param));

                            exit;
                        }
                    } else {

                        js_alert('�벻Ҫ�ظ��ύ', U('Case/opinionFlow_termination', $this->_merge_url_param));

                        exit;
                    }
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow_termination');

    }

    //��Ŀ����������
    function opinionFlow_change() {

        $prjId = $_REQUEST['prjid'];

        //$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
        $type = $_REQUEST['FORMTYPE'] ? $_REQUEST['FORMTYPE'] : 17;

        $recordId = $_REQUEST['RECORDID'];

        //��������
        // $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'tksq'")->find();
        // $type = !empty($workflow_type_info['ID']) ? intval($workflow_type_info['ID']) : 0;
        if (!$type) {
            js_alert('���������Ͳ�����');
        }

        //������ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ?
            intval($_REQUEST['RECORDID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();


        if ($flowId > 0) {
            //�����Ѿ����ڵĹ�����
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //��һ��
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                } //ͨ����ť
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {


                        js_alert('ͬ��ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('ͬ��ʧ��');
                    }
                } //�����ť
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {


                        js_alert('����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('���ʧ��');
                    }
                } //��ֹ��ť
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);

                    if (!$auth) {
                        js_alert('δ�����ؾ���ɫ');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {
                        if ($_REQUEST['ISPHONE'] && $_REQUEST['PHONE']) {
                            $msg = "����ϵͳ:����һ������������";
                            send_sms($msg, $_REQUEST['PHONE'], $_REQUEST['CITY']);
                        }

                        if ($_REQUEST['ISMALL']) {
                            $subject = "����ϵͳ:����������";
                            $content = "����һ������Ĺ�����,�뼰ʱ����";
                            oa_notice($_SESSION['uinfo']['uid'], $_REQUEST['DEAL_USERID'], $subject, $content);
                        }

                        js_alert('�����ɹ�', U('Flow/workStep'));
                    } else {
                        js_alert('����ʧ��');
                    }
                }
                exit;
            }
        } else {
            //����������
            $auth = $workflow->start_authority($type);
            if (!$auth) {
                //js_alert("����Ȩ��");
            }
            $form = $workflow->createHtml();

            if ($_REQUEST['savedata']) {


                if ($prjId) {

                    if ($fstatus == 0) {

                        $flow_data['type'] = 'xiangmujuesuan';//$type;
                        //$flow_data['CASEID'] = '';
                        $flow_data['RECORDID'] = $prjId;
                        $flow_data['INFO'] = strip_tags($_POST['INFO']);
                        $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                        $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                        $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                        $flow_data['FILES'] = $_POST['FILES'];
                        $flow_data['ISMALL'] = intval($_POST['ISMALL']);
                        $flow_data['ISPHONE'] = intval($_POST['ISPHONE']);

                        $str = $workflow->createworkflow($flow_data);

                        if ($str) {
                            //�ύ..����


                            js_alert('�ύ�ɹ�', U('Case/opinionFlow', $this->_merge_url_param));
                            exit;
                        } else {
                            js_alert('�ύʧ��', U('Case/opinionFlow', $this->_merge_url_param));
                            exit;
                        }
                    } else {
                        js_alert('�벻Ҫ�ظ��ύ', U('Case/opinionFlow', $this->_merge_url_param));
                        exit;
                    }
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow_change');

    }

    public function del_project() {
        $prjid = $_REQUEST['prjid'];
        $this->project_case_auth($prjid);
        if ($prjid) {
            /* $caselist = M('Erp_case')->where("PROJECT_ID=".$prjid)->select();
             foreach($caselist as $v){
                M('Erp_prjbudget')->where("CASE_ID=".$v['ID'])->delete();
             }
             M('Erp_case')->where("PROJECT_ID=".$prjid)->delete();
             M('Erp_house')->where("PROJECT_ID=".$prjid)->delete();$_SESSION['uinfo']['uid']
             */
            $one = M('Erp_project')->where("ID=" . $prjid)->find();
            if ($one['CUSER'] == $_SESSION['uinfo']['uid'] || $_SESSION['uinfo']['uname'] == 'admin') {
                $model = new Model();
                $model->startTrans();
                $temp['STATUS'] = 2;
                $res = M('Erp_project')->where("ID=" . $prjid)->save($temp);
                $casetemp['FSTATUS'] = 7;
                $res2 = M('Erp_case')->where("PROJECT_ID=" . $prjid)->save($casetemp);
                if ($res && $res2) {
                    $model->commit();
                    $result['status'] = 'y';
                    $result['info'] = g2u('ɾ���ɹ�');
					$this->UserLog->writeLog($prjid,$_SERVER["REQUEST_URI"],"��Ŀɾ��,��Ŀ��".$prjid ,serialize($_REQUEST));

                } else {
                    $model->rollback();
                }
            } else {
                $result['status'] = 'n';
                $result['info'] = g2u('��Ȩ��ɾ��');


            }

        } else {
            $result['info'] = g2u('����Ŀid');
        }
        echo json_encode($result);
        exit();

    }

    /**
     * Ϊ�ٷֱ���Ӱٷֺ�
     * @param $data
     */
    private function addPercentMark(&$data) {
        // ʵ�ʸ���������
        if (!empty($data['fuxianlirunlv_shiji'])) {
            $data['fuxianlirunlv_shiji'] .= self::PERCENT_MARK;
        }

        // Ԥ������������
        if (!empty($data['fuxianlirunlv_yugu'])) {
            $data['fuxianlirunlv_yugu'] .= self::PERCENT_MARK;
        }

        // ʵ���ۺ�������
        if (!empty($data['zhlirunlv_shiji'])) {
            $data['zhlirunlv_shiji'] .= self::PERCENT_MARK;
        }

        // Ԥ���ۺ�������
        if (!empty($data['zhlirunlv_yugu'])) {
            $data['zhlirunlv_yugu'] .= self::PERCENT_MARK;
        }

        // ����������
        if (!empty($data['fuxianlirunlv'])) {
            $data['fuxianlirunlv'] .= self::PERCENT_MARK;
        }

        // ������
        if (!empty($data['lirunlv'])) {
            $data['lirunlv'] .= self::PERCENT_MARK;
        }

        // ����Ԥ������������
        if (!empty($data['lixiangyugufuxianlirunlv'])) {
            $data['lixiangyugufuxianlirunlv'] .= self::PERCENT_MARK;
        }
    }

    /**
     * ��ȡ��Ŀ��������
     * @param $parentChooseID
     * @return mixed
     */
    private function getFinancialData($parentChooseID) {
        $projectModel = D('Project');
        $oneAccount = M('Erp_finalaccounts')->where("ID = " . $parentChooseID)->find();
        $caseID = $oneAccount['CASE_ID'];
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
        $data['ZJTIME'] = D('Project')->get_zjtime($this->_get('parentchooseid'));
        $data['ZJYUANYIN'] = $oneAccount['ZJYUANYIN'];
        $data['shouru_shiji'] = $projectModel->getCaseInvoiceAndReturned($caseID, $scaleType, 2);//����_ʵ��
        $data['shouru_yugu'] = $oneBudget[0]['SUMPROFIT'];//����_Ԥ��
        $data['xianxia_shiji'] = $projectModel->get_bugcost($caseID) + $projectModel->caseSignNoPay($caseID,2);//ʵ�����·��� + ��Ա�ѿ�Ʊ�ķ���
        $data['xianxia_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM'];//���³ɱ�Ԥ��
        $data['fuxianlirun_shiji'] = floatval($data['shouru_shiji']) - floatval($data['xianxia_shiji']);//ʵ�� ��������
        $data['fuxianlirun_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM_PROFIT'];
        $data['fuxianlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'], $data['shouru_shiji']);//ʵ�� ����������
        $data['fuxianlirunlv_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM_PROFIT_RATE'];
        $data['zhlirunlv_shiji'] = $projectModel->get_prjdata($caseID, 10);//ʵ�� �ۺ�������
        $data['zhlirunlv_yugu'] = $oneBudget[0]['ONLINE_COST_RATE'];
        $data['ZHAD_SHIJI'] = $oneAccount['ZHAD_SHIJI'];//ʵ�� �ۺ���
        $data['zhad_yugu'] = $projectModel->get_vadcost($caseID);//Ԥ�� �ۺ����
        if ($data['ZHAD_SHIJI'] !== null) {
            $data['zhlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'] - $data['ZHAD_SHIJI'], $data['shouru_shiji']); // ʵ�� �ۺ�������
        } else {
            $data['zhlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'] - $data['zhad_yugu'], $data['shouru_shiji']); // ʵ�� �ۺ�������
        }
        $data['status'] = $oneAccount['STATUS'];

        $this->_merge_url_param['prjid'] = $oneAccount['PROJECT'];
        return $data;
    }

    /**
     * ����ʦ����ʾ������ť
     * @param $cnt1
     * @param $cnt2
     * @param $total
     * @return int
     */
    private function isShowAddBtn($cnt1, $cnt2, $total) {
        if (($cnt1 == $total) || ($cnt2 == $total) || ($cnt1 + $cnt2 == $total)) {
            return self::HIDE_ADD_BTN;
        }
        return self::SHOW_ADD_BTN;
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

    public function asyncUpdateFinalAccounts() {
        if (!$this->_get('parentchooseid')) {
            echo json_encode(array(
                'code' => '0',
                'msg' => g2u('ȱʧ����')
            ));
            exit;
        }
        $data = $_POST;
		$data['ZJYUANYIN'] = u2g($_POST['ZJYUANYIN']);
        $finalAccountModel = D('Erp_finalaccounts');
        $finalAccountModel->startTrans();
        $updated = $finalAccountModel->where('ID = ' . $this->_get('parentchooseid'))->save($data);
        if ($updated !== false) {
            $finalAccountModel->commit();
            echo json_encode(array(
                'state' => 1,
                'msg' => g2u('���ݱ���ɹ�')
            ));
        } else {
            $finalAccountModel->rollback();
            echo json_encode(array(
                'state' => 0,
                'msg' => g2u('���ݱ���ʧ��')
            ));
        }
    }

}

?>