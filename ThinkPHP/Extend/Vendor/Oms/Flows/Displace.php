<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * �û�����������
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2016/11/24
 * Time: 11:35
 */

class Displace extends FlowBase {

    /**
     * ��Ҫ���ж�ȼ�����Ŀ����
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
     * ת��
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
     * ͨ��
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
     * ���
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {
        $this->model->startTrans();

        $result = $this->workflow->notWorkflow($data);

        //����û�����(״̬�û���3����˲�ͨ��)
        $dbResult = D('Displace')->submitDisplaceById($data['RECORDID'],3);

        if ($result !== false && $dbResult!==false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * ����������
     * @param $data
     * @return array
     */
    function finishworkflow($data) {

        //���ؽ����
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Flow/flowList', 'status=1'),
        );

        if (is_array($data) && count($data)) {
            $auth = $this->workflow->flowPassRole($data['flowId']);

            if (!$auth) {
                $response['message'] = 'δ�����ؾ���ɫ';
                return $response;
            }

            D()->startTrans();
            //��ɹ�����
            $finishResult = $this->workflow->finishworkflow($data);

            //�����û�����(״̬�û���2)
            $dbResult = D('Displace')->submitDisplaceById($data['RECORDID'],2);

            // ����������ҳɱ������ɹ����������ɹ�
            if ($finishResult !== false && $dbResult !== false) {
                D()->commit();
                $response['status'] = true;
                $response['message'] = '�����ɹ�';
            } else {
                D()->rollback();
                $response['status'] = false;
                $response['message'] = '����ʧ��';
            }
        }

        return $response;
    }

    /**
     * �����û�������
     * @param $data
     * @return bool
     */
    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Displace/displaceApply'),
        );

        // ���Ȩ��
        //$auth = $this->workflow->start_authority('zhihuanshenqing');
        $auth = true;  //�½�����ҪȨ���ж�

        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '����Ȩ��';
        } else {
            $caseInfo = D('ProjectCase')->get_info_by_id($data['CASEID'], array('FSTATUS'));
            //������Ϣ
            if (notEmptyArray($caseInfo)) {
                if (!in_array($caseInfo[0]['FSTATUS'], array(2, 4))) {
                    $response['status'] = 0;
                    $response['message'] = '�û��������̴���ʧ��,ҵ�����Ͳ���ִ��״̬���޷������û���������';
                    $response['url'] = '';
                } else {
                    $data['INFO'] = strip_tags($data['INFO']);
                    $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
                    $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
                    $data['DEAL_USERID'] = intval($data['DEAL_USERID']);

                    D()->startTrans();
                    $dbResult = $this->workflow->createworkflow($data);

                    if ($dbResult !== false) {
                        //�ύ�û�����(״̬�û���1)
                        $dbResult = D('Displace')->submitDisplaceById($data['RECORDID'],1);
                    }

                    if ($dbResult !== false) {
                        $response['status'] = true;
                        $response['message'] = '�ף��û������ύ�ɹ���';
                        D()->commit();
                    } else {
                        $response['status'] = false;
                        $response['message'] = '�ף��û������ύʧ��';
                        D()->rollback();
                    }
                }
                $response['url'] = U('Touch/Displace/process', 'RECORDID=' . $data['RECORDID']);
            } else {
                $response['status'] = false;
                $response['message'] = '��������Ӧ���У�';
            }
        }

        return $response;
    }
}