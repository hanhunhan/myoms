<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * 置换工作流处理
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2016/11/24
 * Time: 11:35
 */

class Displace extends FlowBase {

    /**
     * 需要进行额度检查的项目类型
     * @var array
     */
    protected $needRangeCheckType = array('fx', 'ds', 'fwfsc');

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
    }

    function nextstep($flowId) {
        return $this->workflow->nextstep($flowId);
    }

    function createHtml($flowId) {
        // TODO: Implement createHtml() method.
    }

    /**
     * 转交
     * @param $data
     * @return bool
     */
    function handleworkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->handleworkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }
        return $result;
    }

    /**
     * 通过
     * @param $data
     * @return bool
     */
    function passWorkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->passWorkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * 否决
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {
        $this->model->startTrans();

        $result = $this->workflow->notWorkflow($data);

        //否决置换申请(状态置换成3：审核不通过)
        $dbResult = D('Displace')->submitDisplaceById($data['RECORDID'],3);

        if ($result !== false && $dbResult!==false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * 备案工作流
     * @param $data
     * @return array
     */
    function finishworkflow($data) {

        //返回结果集
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Flow/flowList', 'status=1'),
        );

        if (is_array($data) && count($data)) {
            $auth = $this->workflow->flowPassRole($data['flowId']);

            if (!$auth) {
                $response['message'] = '未经过必经角色';
                return $response;
            }

            D()->startTrans();
            //完成工作流
            $finishResult = $this->workflow->finishworkflow($data);

            //备案置换申请(状态置换成2)
            $dbResult = D('Displace')->submitDisplaceById($data['RECORDID'],2);

            // 工作流完成且成本表插入成功表明备案成功
            if ($finishResult !== false && $dbResult !== false) {
                D()->commit();
                $response['status'] = true;
                $response['message'] = '备案成功';
            } else {
                D()->rollback();
                $response['status'] = false;
                $response['message'] = '备案失败';
            }
        }

        return $response;
    }

    /**
     * 创建置换工作流
     * @param $data
     * @return bool
     */
    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Displace/displaceApply'),
        );

        // 检查权限
        //$auth = $this->workflow->start_authority('zhihuanshenqing');
        $auth = true;  //新建不需要权限判断

        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '暂无权限';
        } else {
            $caseInfo = D('ProjectCase')->get_info_by_id($data['CASEID'], array('FSTATUS'));
            //案例信息
            if (notEmptyArray($caseInfo)) {
                if (!in_array($caseInfo[0]['FSTATUS'], array(2, 4))) {
                    $response['status'] = 0;
                    $response['message'] = '置换申请流程创建失败,业务类型不在执行状态，无法创建置换审批流程';
                    $response['url'] = '';
                } else {
                    $data['INFO'] = strip_tags($data['INFO']);
                    $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
                    $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
                    $data['DEAL_USERID'] = intval($data['DEAL_USERID']);

                    D()->startTrans();
                    $dbResult = $this->workflow->createworkflow($data);

                    if ($dbResult !== false) {
                        //提交置换申请(状态置换成1)
                        $dbResult = D('Displace')->submitDisplaceById($data['RECORDID'],1);
                    }

                    if ($dbResult !== false) {
                        $response['status'] = true;
                        $response['message'] = '亲，置换申请提交成功！';
                        D()->commit();
                    } else {
                        $response['status'] = false;
                        $response['message'] = '亲，置换申请提交失败';
                        D()->rollback();
                    }
                }
                $response['url'] = U('Touch/Displace/process', 'RECORDID=' . $data['RECORDID']);
            } else {
                $response['status'] = false;
                $response['message'] = '不存在相应案列！';
            }
        }

        return $response;
    }
}