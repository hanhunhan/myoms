<?php
/*
* 项目状态
无状态 0
已创建  1
执行中 2
办结  3
项目办结 4
终止  5
审核中 6



*/

/**
 * Class CaseAction
 */
class CaseAction extends ExtendAction {
    /**
     * 新增项目权限
     */
    const CREATE_CASE = 143;

    /**
     * 隐藏新增按钮
     */
    const HIDE_ADD_BTN = 2;

    /**
     * 是否可查看统计信息权限编码
     */
    const STAT_PERMISSION_ID = 701;

    /**
     * 显示新增按钮
     */
    const SHOW_ADD_BTN = 1;
    public $_merge_url_param = array();
    public $model;
	private $UserLog;
    /**
     * 非我方收筹case类型
     */
    const SCALETYPE_FWFSC = 8;

    /**
     * 百分号
     */
    const PERCENT_MARK = '%';

    //构造函数
    public function __construct() {
		Vendor('Oms.UserLog');
		$this->UserLog = UserLog::Init();
        // 权限映射表
        $this->authorityMap = array(
            'create_case' => self::CREATE_CASE
        );

        $this->model = new Model();
        parent::__construct();

        //TAB URL参数
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
        , array('1', 'ACSTATUS'), array('1', 'CPSTATUS'), array('1', 'SCSTATUS'));//绑定状态颜色array('1','BSTATUS') 1为类型对应ERP_STATUS_TYPE表   BSTATUS为需要绑定颜色的字段名
        $form->showStatusTable($arrparam);
        $form->compareField = array('CCOUNT');
        $form->CZBTN = array(
            '%PSTATUS%==2' => '<a  href="javascript:void(0);" class="contrtable-link btn btn-danger btn-xs" title="删除" onclick="delproject(this);"><i class="glyphicon glyphicon-trash"></i></a> ',
            '%ASTATUS%==2 and %CCOUNT%<1' => '<a href="javascript:void(0);" class="contrtable-link btn btn-danger btn-xs" title="删除" onclick="delproject(this);"><i class="glyphicon glyphicon-trash"></i></a>',
            '%CPSTATUS%==2 and %CCOUNT%<1' => '<a  href="javascript:void(0);" class="contrtable-link btn btn-danger btn-xs" title="删除" onclick="delproject(this);"><i class="glyphicon glyphicon-trash"></i></a>',
            '%PSTATUS%>2' => '<a class="contrtable-link btn btn-success btn-xs"  href="javascript:void(0);" onclick="viewFlow(this);">流程图</a>',
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
            if (count($arr) == 1 && $arr[0] == 'yg') {//硬广
                $data['ASTATUS'] = 2;
                $cdata['CTIME'] = $CTIME;
                $cdata['CUSER'] = $CUSER;
                $cdata['SCALETYPE'] = 3;
                $cdata['FSTATUS'] = 2;//硬广直接执行
                $acdata[] = $cdata;
                $jumpurl = U('Advert/contract/', '&is_from=1&CASE_TYPE=yg');

            } elseif (count($arr) == 1 && $arr[0] == 'hd') {//活动
                $data['PSTATUS'] = 2;
                $data['ACSTATUS'] = 1;
                $cdata['CTIME'] = $CTIME;
                $cdata['CUSER'] = $CUSER;
                $cdata['SCALETYPE'] = 4;
                $cdata['FSTATUS'] = 6;//立项审核中
                $acdata[] = $cdata;
                $jumpurl = U("Activ/activPro", array('tabNum' => 8, 'flowType' => "lixiangshenqing", 'showOpinion' => 1));
            } elseif (count($arr) == 1 && $arr[0] == 'cp') {//产品
                $data['CPSTATUS'] = 2;
                $cdata['CTIME'] = $CTIME;
                $cdata['CUSER'] = $CUSER;
                $cdata['SCALETYPE'] = 5;
                $acdata[] = $cdata;
                $jumpurl = U('Case/projectlist');

            } elseif (in_array('fwfsc', $arr)) {
                $data['PSTATUS'] = 2;
                if (count($arr) == 1) {  // 单独的非我方收筹项目
                    $data['SCSTATUS'] = 1;
                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = self::SCALETYPE_FWFSC;
                    $cdata['FSTATUS'] = 6;//立项审核中
                    $acdata[] = $cdata;
                    $jumpurl = U('House/projectDetail', array('tabNum' => 23));
                } else if (count($arr) == 2 && in_array('fx', $arr)) {  // 分销与非我方收筹的组合
                    // 非我方收筹
                    $data['SCSTATUS'] = 1;
                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = self::SCALETYPE_FWFSC;
                    $cdata['FSTATUS'] = 6;// 立项审核中
                    $acdata[] = $cdata;

                    // 分销
                    $data['MSTATUS'] = 1;
                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 2;
                    $cdata['FSTATUS'] = 6;//立项审核中
                    $acdata[] = $cdata;
                    $jumpurl = U('House/projectDetail', array('tabNum' => 20));
                } else {  // 其他类型的组合则不支持
                    js_alert('非立项类型无法多选！', U('Case/createcase'));
                    exit();
                }
            } elseif (in_array('ds', $arr) || in_array('fx', $arr)) {
                $data['PSTATUS'] = 2;

                if (in_array('ds', $arr)) {
                    $data['BSTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 1;
                    $cdata['FSTATUS'] = 6;//立项审核中
                    $acdata[] = $cdata;
                    $jumpurl = U('House/projectDetail', array('tabNum' => 20));
                }
                if (in_array('fx', $arr)) {
                    $data['MSTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 2;
                    $cdata['FSTATUS'] = 6;//立项审核中
                    $acdata[] = $cdata;
                    $jumpurl = U('House/projectDetail', array('tabNum' => 20));
                }
                if (in_array('yg', $arr)) {
                    /*$data['ASTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 3;
                    $acdata[] = $cdata;*/
                    js_alert('硬广类型无需立项！', U('Case/createcase'));
                    exit();
                }
                if (in_array('hd', $arr)) {
                    /*$data['ACSTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 4;
                    $acdata[] = $cdata;*/
                    js_alert('活动类型不能组合立项！', U('Case/createcase'));
                    exit();
                }
                if (in_array('cp', $arr)) {
                    /*$data['CPSTATUS'] = 1;

                    $cdata['CTIME'] = $CTIME;
                    $cdata['CUSER'] = $CUSER;
                    $cdata['SCALETYPE'] = 5;
                    $acdata[] = $cdata;*/
                    js_alert('非立项类型无法多选！', U('Case/createcase'));
                    exit();
                }

            } else {
                //if(in_array('yg',$arr)) $data['ASTATUS'] = 2;
                //if(in_array('hd',$arr)) $data['ACSTATUS'] = 2;
                //if(in_array('cp',$arr)) $data['CPSTATUS'] = 2;
                js_alert('非立项类型无法多选！', U('Case/createcase'));
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
                        //插入权限
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
                //插入项目记录
                $temp = array();
                $temp['PROJECT_ID'] = $lastid;
                $temp['USER_ID'] = $_SESSION['uinfo']['uid'];
                $temp['CTIME'] = date('Y-m-d H:i:s');
                $plres = M('Erp_project_log')->add($temp);
                if (!$plres) {
                    $model->rollback();

                }
                $model->commit();
				$this->UserLog->writeLog($lastid,$_SERVER["REQUEST_URI"],"新增项目成功" ,serialize($_REQUEST));

                js_alert('新增项目成功', $jumpurl . '&prjid=' . $lastid, 1);
                exit();
            } else {
                js_alert('新增项目失败', U('Case/projectlist'), 1);
				$this->UserLog->writeLog($lastid,$_SERVER["REQUEST_URI"],"新增项目失败" ,serialize($_REQUEST));
                exit();
                $model->rollback();
            }

        }
        $this->display('createproject');

    }

    /**
     * 项目决算业务
     */
    function project_finalaccounts() {
        if ($_REQUEST['act'] == 'checkproject') {
            if (1) {//判断决算条件
                $project = M('Erp_project')->where("ID=" . $this->_get('prjid'))->find();
                if ($project) {
                    $case = M('Erp_case')->where('SCALETYPE in(1,2,8) and PROJECT_ID=' . $project['ID'])->select();
                    $conModel = D("Erp_finalaccounts");
                    $fc = $conModel->where(" TYPE=1 and PROJECT=" . $this->_get('prjid'))->select();
                    $cres = true;
                    if ($fc) {//只查看不添加新记录
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
						$this->UserLog->writeLog($res,$_SERVER["REQUEST_URI"],"新增决算成功,项目：".$data['PROJECT'] ,serialize($_REQUEST));
                    } else {
                        $result['status'] = 'n';
                        $result['info'] = g2u('失败');
						$this->UserLog->writeLog($res,$_SERVER["REQUEST_URI"],"新增决算失败,项目：".$data['PROJECT'] ,serialize($_REQUEST));
                    }
                }

            } else {
                $result['status'] = 'n';
                $result['info'] = g2u('不符合决算条件');
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
                $result['info'] = g2u('提交成功');
            } else {
                $finalAccountModel->rollback();
                $result['status'] = 'n';
                $result['info'] = g2u('失败');
            }
            echo json_encode($result);
            exit();
        }

        if ($_REQUEST['ischildren'] == 1) { //决算统计
            $prj = D('Project');
            $fc = M('Erp_finalaccounts')->where("ID=" . $this->_get('parentchooseid'))->find();
            $caseid = $fc['CASE_ID'];
            $scaleType = D('ProjectCase')->where('ID = ' . $caseid)->getField('SCALETYPE');  // 项目类型
            $adCost = $fc['ZHAD_SHIJI'] ?$fc['ZHAD_SHIJI']: $prj->get_vadcost($caseid);  // 广告费用
            $offlineCost = $prj->get_bugcost($caseid);  // 线下费用
            $m = M();
            $prjbudget = $m->query("select t.*,to_char(FROMDATE,'yyyy-mm-dd') as FROMDATE,to_char(TODATE,'yyyy-mm-dd') as TODATE,to_char(UNDOTIME,'yyyy-mm-dd') as UNDOTIME   from ERP_PRJBUDGET t where CASE_ID='$caseid' ");

            $data['FROMDATE'] = $prjbudget[0]['FROMDATE'];
            $data['UNDOTIME'] = $prjbudget[0]['UNDOTIME'];
            $data['caiwuyushou'] = $prj->getCaseAdvances($caseid, $scaleType, 2);  // 财务预收
            $data['yikaipiaohk'] = $prj->getCaseInvoiceAndReturned($caseid, $scaleType, 2);  // 回款收入
            $data['w_yibaoxiaofy'] = $prj->getCaseCost($caseid, 1);  // 非资金池已报销费用
            $data['w_yifswbxfy'] = $prj->getCaseCost($caseid, 3) + $prj->caseSignNoPay($caseid,2);  // 非资金池已发生未报销费用 + 已开票未报销费用（中介等）
            $data['z_yibaoxiaofy'] = $prj->getFundPoolAmount($caseid, $scaleType, 1);  // 资金池已报销费用
            $data['z_yifswbxfy'] = $prj->getFundPoolAmount($caseid, $scaleType, 2);  // 资金池已发生未报销费用
            $data['z_yichongdi'] = $fc['OFFSET_COST'];  // 已冲抵费用
			$data['ZHAD_SHIJI'] = $fc['ZHAD_SHIJI'];  // 实际广告费
			$data['tobepaid_yewu'] = $fc['TOBEPAID_YEWU'];  // 实际广告费
			$data['tobepaid_fundpool'] = $fc['TOBEPAID_FUNDPOOL'];  // 实际广告费
			$data['z_tax'] = $fc['Z_TAX'];  // 实际广告费
			$data['Z_counterfee'] = $fc['Z_COUNTERFEE'];  // 实际广告费
            $data['fuxianlirun'] = $data['yikaipiaohk'] - $offlineCost - $data['tobepaid_yewu']-$data['tobepaid_fundpool'];
            $data['fuxianlirunlv'] = $this->getProfitRate($data['fuxianlirun'], $data['yikaipiaohk']);
            $data['lirunlv'] = $this->getProfitRate($data['fuxianlirun'] - $adCost, $data['yikaipiaohk']);
            $data['lixiangyugushouru'] = $prjbudget[0]['SUMPROFIT'];
            $data['lixiangyugufuxianlirunlv'] = $prjbudget[0]['OFFLINE_COST_SUM_PROFIT_RATE'];
            $data['status'] = $fc['STATUS'];
            $data['PRJBUDGET_ID'] = $prjbudget[0]['ID'];
            $data['FINAL_ACCOUNT_ID'] = $this->_get('parentchooseid');  // 项目决算表
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
                array('项目决算表', U('/Case/project_finalaccounts', 'ischildren=1'))
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
     * 项目终止业务
     */
    function project_termination() {
        if ($_REQUEST['act'] == 'checkproject') {
            if (1) {//判断终止条件
                $caseids = $_REQUEST['caseids'];
                $project = M('Erp_project')->where("ID=" . $this->_get('prjid'))->find();
                if ($caseids && $project) {
                    $case = M('Erp_case')->where("ID in( $caseids )")->select();
                    $conModel = D("Erp_finalaccounts");
                    $fc = $conModel->where("TYPE=2 and CASE_ID  in( $caseids ) ")->select();
                    $cres = true;
                    if ($fc) {//只查看不添加新记录
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
                            $data['TYPE'] = 2;//项目终止
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
						$this->UserLog->writeLog($res,$_SERVER["REQUEST_URI"],"新增终止申请成功,项目：".$data['PROJECT'] ,serialize($_REQUEST));
                    } else {
                        $result['status'] = 'n';
                        $result['info'] = g2u('失败');
						$this->UserLog->writeLog($res,$_SERVER["REQUEST_URI"],"新增终止申请失败,项目：".$data['PROJECT'] ,serialize($_REQUEST));
                    }
                }

            } else {
                $result['status'] = 'y';
                $result['info'] = g2u('不符合终止条件');
            }

            echo json_encode($result);
            exit();
        } elseif ($_REQUEST['act'] == 'canelcase') {
            if (D("Erp_finalaccounts")->where(" ( STATUS=0 or STATUS=3) and  ID=" . $this->_get('fcid'))->delete()) {
                $result['status'] = 'y';
                $result['info'] = g2u('撤销成功');
            } else {
                $result['status'] = 'n';
                $result['info'] = g2u('不符合撤销条件');
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
                $result['info'] = g2u('提交成功');
            } else {
                $result['status'] = 'n';
                $result['info'] = g2u('失败');
            }
            echo json_encode($result);
            exit();

        } elseif ($_REQUEST['layer'] == 1) {//选择窗口
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

        } elseif ($_REQUEST['ischildren'] == 1) {//终止  决算统计
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
            $children = array(array('项目终止表', U('/Case/project_termination', 'ischildren=1'))
            );
            $form->setChildren($children);
            $form->GABTN = "<a href='javascript:void(0);' onclick='canelcase();' class='btn btn-info btn-sm'>撤销</a> <!--a href='javascript:void(0);' onclick='termination_flow();'>提交</a-->";
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
        $this->project_case_auth($this->_get('prjid'));//项目业务权限判断

        if ($this->_get('type') == 2) {//项目下活动变更
            $activi = M('Erp_activitics')->where("ID=" . $this->_get('activId'))->find();
            $case = M('Erp_case')->where("ID=" . $activi['CASE_ID'])->find();
            $project_id = $case['PROJECT_ID'];
        } else {
            $project_id = $this->_get('prjid');
        }
        $ccount1 = M('Erp_finalaccounts')->where("PROJECT=$project_id and STATUS in(1,2) and TYPE=1")->count();
        $ccount2 = M('Erp_finalaccounts')->where("PROJECT=$project_id and STATUS in(1,2) and TYPE=2")->count();
        $clist = D('Project')->get_businessclass($project_id);//项目的业务类型
        if ($_REQUEST['act'] == 'checkprjChange') {
            // 如果是从项目表进入的，则可进入查看
            if ($this->_request('from') == 'projectList') {
                echo json_encode(array(
                    'status' => 'y',
                    'info' => 'success'
                ));
                exit();
            }
            if ($ccount1 + $ccount2 == count($clist)) {
                $result['status'] = 'n';
                $result['info'] = g2u('项目下的全部业务类型决算或终止已提交审批或审批通过');
            } elseif ($ccount1 == count($clist)) {
                $result['status'] = 'n';
                $result['info'] = g2u('项目下的全部业务类型决算已提交审批或审批通过');
            } elseif ($ccount2 == count($clist)) {
                $result['status'] = 'n';
                $result['info'] = g2u('项目下的全部业务类型终止已提交审批或审批通过');
            } else {
                $result['status'] = 'y';
                $result['info'] = 'success';
            }
            echo json_encode($result);
            exit();
        }

        //新增变更
        if ($_REQUEST['act'] == 'addChange') {
            $type = $_REQUEST['type'];
            if ($type == 2) {
                $project_id = $_REQUEST['activId'];
            } else {
                $project_id = $_REQUEST['prjid'];
            }

            //判断是否可新增
            $isChange = M('Erp_project_change')->where("(STATUS = 0 OR STATUS = 1) AND PROJECT_ID=" . $project_id . " AND TYPE=" . $type)->find();
            if ($isChange) {
                $result['status'] = 'n';
                $result['msg'] = g2u("变更已存在或正在审核中,暂不能添加");
            } else {
                $data = array();
                $data['PROJECT_ID'] = $project_id;
                $data['ADATE'] = date("Y-m-d H:i:s");
                $data['APPLICANT'] = $_SESSION['uinfo']['uid'];
                $data['STATUS'] = 0;
                $data['TYPE'] = $type;

                if (D('Erp_project_change')->add($data)) {
                    $result['status'] = 'y';
                    $result['msg'] = g2u('添加成功');
                } else {
                    $result['status'] = 'n';
                    $result['msg'] = g2u('添加失败');
                }
            }
            echo json_encode($result);
            exit;
        }

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(165);

        if ($this->_get('type') == 2) {//项目下活动变更
            $form->setMyField('PROJECT_ID', 'LISTSQL', 'select ID,TITLE from ERP_ACTIVITIES ');
        } else {
            $form->setMyField('PROJECT_ID', 'LISTSQL', 'select ID,PROJECTNAME from ERP_PROJECT ');
        }

        $form->CZBTN = array(
            '%STATUS% == 0' => '<a class="contrtable-link btn btn-default btn-sm" onclick="changeDetail(this);" href="javascript:;">变更详细</a>',
            '%STATUS% == 1' => '<a class="contrtable-link btn btn-primary btn-sm" onclick="viewDetail(this);" href="javascript:;">查看变更</a>',
            '%STATUS% == 2' => '<a class="contrtable-link btn btn-primary btn-sm" onclick="viewDetail(this);" href="javascript:;">查看变更</a>',
            '%STATUS% == 3' => '<a class="contrtable-link btn btn-primary btn-sm" onclick="viewDetail(this);" href="javascript:;">查看变更</a>');

        //权限判断
        if(in_array(448,$this->getUserAuthorities()))
            $form->GCBTN = '<a href="javascript:;" class="btn btn-info btn-sm" onclick="submitFlow(this);">提交</a>';

        if ($this->_get('type') == 2) {//项目下活动变更
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
        // 是否显示新增按钮 1=显示，2=不显示
        $isShowAddBtn = $this->isShowAddBtn($ccount1, $ccount2, count($clist));
        $this->assign('isShowAddBtn', $isShowAddBtn);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign('form', $formHtml);
        $this->assign('prjid', $_REQUEST['prjid']);
        $this->assign('type', $_REQUEST['type']);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('flowId', $_REQUEST['flowId']);

        $this->display('project_change');
    }

    //立项变更
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
            //处理已经存在的工作流
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //下一步
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('办理成功', U('Flow/workStep'));
                    } else {
                        js_alert('办理失败');
                    }
                } //通过按钮
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {
                        js_alert('同意成功', U('Flow/workStep'));
                    } else {
                        js_alert('同意失败');
                    }
                } //否决按钮
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {
                        //$project_model = D('Project'); 
                        // $project_model->update_finalaccounts_nopass_status($prjId);


                        js_alert('否决成功', U('Flow/workStep'));
                    } else {
                        js_alert('否决失败');
                    }
                } //终止按钮
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);

                    if (!$auth) {
                        js_alert('未经过必经角色');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {
                        $CID = $_REQUEST['RECORDID'];
                        $changer->setRecords($CID);

                        $project_model = D('Project');
                        $project_model->set_project_change($prjId);//变更后的数据统计
                        //$ress =$project_model->update_finalaccounts_status($prjId);
                        js_alert('备案成功', U('Flow/workStep'));
                    } else {
                        js_alert('备案失败');
                    }
                }
                exit;
            }
        } else {
            //创建工作流
            $auth = $workflow->start_authority($flowType);
            if (!$auth) {
                js_alert('暂无权限');
            }
            $form = $workflow->createHtml();

            if ($_REQUEST['savedata']) {

                $form = $workflow->createHtml();

                if ($_REQUEST['savedata']) {

                    $str = $workflow->createworkflow($_REQUEST);
                    if ($str) {

                        js_alert('提交成功', U('Flow/workStep'));
                    } else {
                        js_alert('提交失败');
                    }
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow');

    }
	 //决算审批意见
    function opinionFlow_final2(){
		//$prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['CASEID'];
        ////工作流ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        ////工作流关联业务ID
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
    //决算审批意见
    function opinionFlow_final() {
        $prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['CASEID'];
        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ?
            intval($_REQUEST['RECORDID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();

        if ($flowId > 0) {
            //处理已经存在的工作流
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //下一步
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('办理成功', U('Flow/workStep'));
                    } else {
                        js_alert('办理失败');
                    }
                } //通过按钮
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {
                        js_alert('同意成功', U('Flow/workStep'));
                    } else {
                        js_alert('同意失败');
                    }
                } //否决按钮
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {
                        $project_model = D('Project');
                        $project_model->update_finalaccounts_nopass_status($recordId);


                        js_alert('否决成功', U('Flow/workStep'));
                    } else {
                        js_alert('否决失败');
                    }
                } //终止按钮
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);
                    if (!$auth) {
                        js_alert('未经过必经角色');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {
                        $project_model = D('Project');
                        $ress = $project_model->update_finalaccounts_status($recordId);
                        // echo $prjId;var_dump($ress);

                        if ($_REQUEST['ISPHONE'] && $_REQUEST['PHONE']) {
                            $msg = "经管系统:你有一条工作流待办";
                            send_sms($msg, $_REQUEST['PHONE'], $_REQUEST['CITY']);
                        }

                        if ($_REQUEST['ISMALL']) {
                            $subject = "经管系统:工作流待办";
                            $content = "你有一条待办的工作流,请及时处理";
                            oa_notice($_SESSION['uinfo']['uid'], $_REQUEST['DEAL_USERID'], $subject, $content);
                        }
                        js_alert('备案成功', U('Flow/workStep'));
                    } else {
                        js_alert('备案失败');
                    }
                }
            }
        } else {
            //创建工作流
            $auth = $workflow->start_authority('xiangmujuesuan');
            if (!$auth) {
                //js_alert("暂无权限");
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
                            //提交..申请

                            //$project_model = D('Project');
                            $project_model->update_finalaccounts_check_status($recordId);

                            js_alert('提交成功', U('Case/opinionFlow_final', $this->_merge_url_param));

                            exit;
                        } else {

                            js_alert('提交失败', U('Case/opinionFlow_final', $this->_merge_url_param));

                            exit;
                        }
                    } else {

                        js_alert('请不要重复提交', U('Case/opinionFlow_final', $this->_merge_url_param));

                        exit;
                    }
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow_final');
    }

    //项目终止审批意见
    function opinionFlow_termination() {

        $prjId = $_REQUEST['prjid'] ? $_REQUEST['prjid'] : $_REQUEST['CASEID'];

        //$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
        $type = 18;

       // $recordId = $_REQUEST['RECORDID'];

        //流程类型
        // $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'tksq'")->find();
        // $type = !empty($workflow_type_info['ID']) ? intval($workflow_type_info['ID']) : 0;
        if (!$type) {
            js_alert('工作流类型不存在');
        }

        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ?
            intval($_REQUEST['RECORDID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();


        if ($flowId > 0) {
            //处理已经存在的工作流
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //下一步
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('办理成功', U('Flow/workStep'));
                    } else {
                        js_alert('办理失败');
                    }
                } //通过按钮
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {


                        js_alert('同意成功', U('Flow/workStep'));
                    } else {
                        js_alert('同意失败');
                    }
                } //否决按钮
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {
                        $project_model = D('Project');
                        $project_model->update_termination_nopass_status($recordId);


                        js_alert('否决成功', U('Flow/workStep'));
                    } else {
                        js_alert('否决失败');
                    }
                } //终止按钮
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);

                    if (!$auth) {
                        js_alert('未经过必经角色');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {


                        $project_model = D('Project');
                        $project_model->update_termination_status($recordId);

                        if ($_REQUEST['ISPHONE'] && $_REQUEST['PHONE']) {
                            $msg = "经管系统:你有一条工作流待办";
                            send_sms($msg, $_REQUEST['PHONE'], $_REQUEST['CITY']);
                        }

                        if ($_REQUEST['ISMALL']) {
                            $subject = "经管系统:工作流待办";
                            $content = "你有一条待办的工作流,请及时处理";
                            oa_notice($_SESSION['uinfo']['uid'], $_REQUEST['DEAL_USERID'], $subject, $content);
                        }
                        js_alert('备案成功', U('Flow/workStep'));
                    } else {
                        js_alert('备案失败');
                    }
                }
                exit;
            }
        } else {
            //创建工作流
            $auth = $workflow->start_authority($type);
            if (!$auth) {
                //js_alert("暂无权限");
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
                            //提交..申请

                            //$project_model = D('Project');

                            $project_model->update_termination_check_status($recordId);
                            js_alert('提交成功', U('Case/opinionFlow_termination', $this->_merge_url_param));

                            exit;
                        } else {

                            js_alert('提交失败', U('Case/opinionFlow_termination', $this->_merge_url_param));

                            exit;
                        }
                    } else {

                        js_alert('请不要重复提交', U('Case/opinionFlow_termination', $this->_merge_url_param));

                        exit;
                    }
                }
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('opinionFlow_termination');

    }

    //项目变更审批意见
    function opinionFlow_change() {

        $prjId = $_REQUEST['prjid'];

        //$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
        $type = $_REQUEST['FORMTYPE'] ? $_REQUEST['FORMTYPE'] : 17;

        $recordId = $_REQUEST['RECORDID'];

        //流程类型
        // $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'tksq'")->find();
        // $type = !empty($workflow_type_info['ID']) ? intval($workflow_type_info['ID']) : 0;
        if (!$type) {
            js_alert('工作流类型不存在');
        }

        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ?
            intval($_REQUEST['flowId']) : 0;

        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ?
            intval($_REQUEST['RECORDID']) : 0;

        Vendor('Oms.workflow');
        $workflow = new workflow();


        if ($flowId > 0) {
            //处理已经存在的工作流
            $click = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if ($_REQUEST['savedata']) {
                //下一步
                if ($_REQUEST['flowNext']) {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if ($str) {
                        js_alert('办理成功', U('Flow/workStep'));
                    } else {
                        js_alert('办理失败');
                    }
                } //通过按钮
                else if ($_REQUEST['flowPass']) {
                    $str = $workflow->passWorkflow($_REQUEST);

                    if ($str) {


                        js_alert('同意成功', U('Flow/workStep'));
                    } else {
                        js_alert('同意失败');
                    }
                } //否决按钮
                else if ($_REQUEST['flowNot']) {
                    $str = $workflow->notWorkflow($_REQUEST);
                    if ($str) {


                        js_alert('否决成功', U('Flow/workStep'));
                    } else {
                        js_alert('否决失败');
                    }
                } //终止按钮
                else if ($_REQUEST['flowStop']) {
                    $auth = $workflow->flowPassRole($flowId);

                    if (!$auth) {
                        js_alert('未经过必经角色');
                        exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if ($str) {
                        if ($_REQUEST['ISPHONE'] && $_REQUEST['PHONE']) {
                            $msg = "经管系统:你有一条工作流待办";
                            send_sms($msg, $_REQUEST['PHONE'], $_REQUEST['CITY']);
                        }

                        if ($_REQUEST['ISMALL']) {
                            $subject = "经管系统:工作流待办";
                            $content = "你有一条待办的工作流,请及时处理";
                            oa_notice($_SESSION['uinfo']['uid'], $_REQUEST['DEAL_USERID'], $subject, $content);
                        }

                        js_alert('备案成功', U('Flow/workStep'));
                    } else {
                        js_alert('备案失败');
                    }
                }
                exit;
            }
        } else {
            //创建工作流
            $auth = $workflow->start_authority($type);
            if (!$auth) {
                //js_alert("暂无权限");
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
                            //提交..申请


                            js_alert('提交成功', U('Case/opinionFlow', $this->_merge_url_param));
                            exit;
                        } else {
                            js_alert('提交失败', U('Case/opinionFlow', $this->_merge_url_param));
                            exit;
                        }
                    } else {
                        js_alert('请不要重复提交', U('Case/opinionFlow', $this->_merge_url_param));
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
                    $result['info'] = g2u('删除成功');
					$this->UserLog->writeLog($prjid,$_SERVER["REQUEST_URI"],"项目删除,项目：".$prjid ,serialize($_REQUEST));

                } else {
                    $model->rollback();
                }
            } else {
                $result['status'] = 'n';
                $result['info'] = g2u('无权限删除');


            }

        } else {
            $result['info'] = g2u('无项目id');
        }
        echo json_encode($result);
        exit();

    }

    /**
     * 为百分比添加百分号
     * @param $data
     */
    private function addPercentMark(&$data) {
        // 实际付现利润率
        if (!empty($data['fuxianlirunlv_shiji'])) {
            $data['fuxianlirunlv_shiji'] .= self::PERCENT_MARK;
        }

        // 预估付现利润率
        if (!empty($data['fuxianlirunlv_yugu'])) {
            $data['fuxianlirunlv_yugu'] .= self::PERCENT_MARK;
        }

        // 实际综合利润率
        if (!empty($data['zhlirunlv_shiji'])) {
            $data['zhlirunlv_shiji'] .= self::PERCENT_MARK;
        }

        // 预估综合利润率
        if (!empty($data['zhlirunlv_yugu'])) {
            $data['zhlirunlv_yugu'] .= self::PERCENT_MARK;
        }

        // 付现利润率
        if (!empty($data['fuxianlirunlv'])) {
            $data['fuxianlirunlv'] .= self::PERCENT_MARK;
        }

        // 利润率
        if (!empty($data['lirunlv'])) {
            $data['lirunlv'] .= self::PERCENT_MARK;
        }

        // 立项预估付现利润率
        if (!empty($data['lixiangyugufuxianlirunlv'])) {
            $data['lixiangyugufuxianlirunlv'] .= self::PERCENT_MARK;
        }
    }

    /**
     * 获取项目财务数据
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
        $data['shouru_shiji'] = $projectModel->getCaseInvoiceAndReturned($caseID, $scaleType, 2);//收入_实际
        $data['shouru_yugu'] = $oneBudget[0]['SUMPROFIT'];//收入_预估
        $data['xianxia_shiji'] = $projectModel->get_bugcost($caseID) + $projectModel->caseSignNoPay($caseID,2);//实际线下费用 + 会员已开票的费用
        $data['xianxia_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM'];//线下成本预估
        $data['fuxianlirun_shiji'] = floatval($data['shouru_shiji']) - floatval($data['xianxia_shiji']);//实际 付现利润
        $data['fuxianlirun_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM_PROFIT'];
        $data['fuxianlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'], $data['shouru_shiji']);//实际 付现利润率
        $data['fuxianlirunlv_yugu'] = $oneBudget[0]['OFFLINE_COST_SUM_PROFIT_RATE'];
        $data['zhlirunlv_shiji'] = $projectModel->get_prjdata($caseID, 10);//实际 综合利润率
        $data['zhlirunlv_yugu'] = $oneBudget[0]['ONLINE_COST_RATE'];
        $data['ZHAD_SHIJI'] = $oneAccount['ZHAD_SHIJI'];//实际 折后广告
        $data['zhad_yugu'] = $projectModel->get_vadcost($caseID);//预估 折后广告费
        if ($data['ZHAD_SHIJI'] !== null) {
            $data['zhlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'] - $data['ZHAD_SHIJI'], $data['shouru_shiji']); // 实际 综合利润率
        } else {
            $data['zhlirunlv_shiji'] = $this->getProfitRate($data['fuxianlirun_shiji'] - $data['zhad_yugu'], $data['shouru_shiji']); // 实际 综合利润率
        }
        $data['status'] = $oneAccount['STATUS'];

        $this->_merge_url_param['prjid'] = $oneAccount['PROJECT'];
        return $data;
    }

    /**
     * 控制师傅显示新增按钮
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

    public function asyncUpdateFinalAccounts() {
        if (!$this->_get('parentchooseid')) {
            echo json_encode(array(
                'code' => '0',
                'msg' => g2u('缺失参数')
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
                'msg' => g2u('数据保存成功')
            ));
        } else {
            $finalAccountModel->rollback();
            echo json_encode(array(
                'state' => 0,
                'msg' => g2u('数据保存失败')
            ));
        }
    }

}

?>