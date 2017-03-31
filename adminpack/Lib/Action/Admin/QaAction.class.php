<?php

/**
 * Class QaAction
 */
class QaAction extends ExtendAction
{

    public function delete()
    {
        $config = include './Conf/qa_config.php';
        $model = M('fbs_zxjd_whole', '', $config, 3);

        $id = $_GET['id'];
        $affected = $model->where("id=$id")->delete();
        if($affected)
        {
            $this->success('ɾ���ɹ���',$_SERVER['HTTP_REFERER']);
        }
        else
        {
            $this->error('ɾ��ʧ�ܣ�',$_SERVER['HTTP_REFERER']);
        }
        die();
    }

    public function answer()
    {
        $config = include './Conf/qa_config.php';
        $model = M('fbs_zxjd_whole', '', $config, 3);
        if($_POST['act'] == 'edit')
        {
            $id = $_POST['id'];
            $answer = $_POST['answer'];
            $data = array(
                'answer' => $answer,
                'answeruser' => $_SESSION['uinfo']['tname'],
                'modifydateline' => time(),
            );
            $affected = $model->where("id=$id")->save($data);

            if($affected)
            {
                $this->success('�޸ĳɹ���',U("Qa/answer", array('id'=>$id)));
            }
            else
            {
                $this->error('�޸�ʧ�ܣ�',U("Qa/answer", array('id'=>$id)));
            }
            die();
        }

        $id = $_GET['id'];
        $info = $model->where("id=$id")->select();
        $info = $info[0];

        $info['prjid'] = 626;
        if($info['prjid']){
            $listid = $info['prjid'];
            $res = $this->_get_prj_by_listid($listid, $this->city);
            $info['prj'] = $res;
        }
        $info['date'] = date('Y-m-d H:i', $info['dateline']);
        $info['modifydate'] = date('Y-m-d H:i', $info['modifydateline']);

        $this->assign('info', $info);
        $this->display('edit');
    }

    /**
     * ȫ���ʴ�
     */
    public function all($wait = 0, $title = 'ȫ���ʴ�')
    {
        $config = include './Conf/qa_config.php';
        $model = M('fbs_zxjd_whole', '', $config, 3);

        $cityid = $_COOKIE[CHANNELID];
        $where = "channelid='".$cityid."'";
        if($wait)
        {
            $where .= " AND answer=''";
        }

        import("ORG.Util.Page");
        $count = $model->where($where)->count();
        $p = new Page($count, C('PAGESIZE'));
        $page = $p->show();

        $listinfo = $model->order('dateline desc')->where($where)->limit($p->firstRow . ',' . $p->listRows)->select();

        foreach ($listinfo as $key => $user)
        {
            $listinfo[$key]['date'] = date('Y-m-d H:i', $user['dateline']);
            $listinfo[$key]['modifydate'] = date('Y-m-d H:i', $user['modifydateline']);
            $listinfo[$key]['ansed'] = !empty($user['answer']) ? '<span style="color:#808080">�ѻش�</span>' : '<span style="color:darkred">�ش�</span>';
            $user['prjid'] = 626;
            if($user['prjid']){
                $listid = $user['prjid'];
                $res = $this->_get_prj_by_listid($listid, $this->city);
                $listinfo[$key]['prj'] = $res;
            }
        }

        $this->assign('title', $title);
        $this->assign('page', $page);
        $this->assign('listinfo', $listinfo);
        $this->display('list');
    }

    public function wait()
    {
        $this->all(1, '���ش�');
    }


    public function _get_prj_by_listid($listid, $city)
    {
        /*ģ��ǰ̨���滷��*/
        include_once '../WEB-INF/config.inc.php';
        include_once '../functions/global.func.php';
        /*��ʼ��  memcache*/
        global $memcache;
        $memcache =new Memcache;
        foreach ($memcache_conf as $memcache_confitem)
        {
            $memcache->addserver($memcache_confitem['host'], $memcache_confitem['port']);
        }
        /*ģ��ǰ̨���滷��*/

        $api = 'http://api.house365.com/xf/newhouse/get_prj_365tf.php?prj_listid='.$listid.'&city='.$city;
        $response = fetch_from_cache(3600, 1, 'curl_get_contents', $api);
        if(empty($response[0]))
        {
            return array();
        }
        $response = $response[0];

        /*¥����Ϣ����*/
        /*logo*/
        if($response['prj_logopic'] == ''){
            $response['prj_logopic'] = 'http://newhouse.house365.com/images/default/nopic.jpg';
        }else{
            $response['prj_logopic'] = get_image_thumb($response['prj_logopic']);
            $response['prj_logopic_o'] = $response['prj_logopic'];
        }
        /*price*/
        if(strpos($response['price_more'],',')!=-1)
        {
            $response['price_more']=substr($response['price_more'],0,strpos($response['price_more'],','));
        }
        $response['price'] = $response['price'] ? $response['price'].$response['price_more'] : '����';

        /*��������*/
        $response['prj_main_force_string'] = '';
        $hx=unserialize($response['prj_main_force']);
        if(!empty($hx)){
            foreach($hx as $val){
                $response['prj_main_force_string'] .= $val['content']." ";
            }
        }

        /*���������*/
        //����
        if( !empty($response['pd_price']) ) {
            if( $response['pd_price'] < 1000 ) {
                $response['pd_price_str'] = $response['pd_price'].'��/��';
            } else {
                $response['pd_price_str'] = $response['pd_price'].'Ԫ/ƽ';
            }
        } else {
            $response['pd_price_str'] = '����';
        }

        //�Ź����ڱ�
        if( CITY == 'nj' ) {
            $response['result_tuan']      = 'http://newhouse.house365.com/'.$response['prj_pinyin'];
            $response['result_forumlink'] = 'http://bbs.house365.com/forumdisplay.php?forumid='.$response['prj_forumid'];
        } else {
            $response['result_tuan']     = 'http://newhouse.'.CITY.'.house365.com/'.$response['prj_pinyin'];
            $response['result_forumlink']= 'http://bbs.'.CITY.'.house365.com/forumdisplay.php?forumid='.$response['prj_forumid'];
        }
        //¥�̵绰
        $loupan_mobiles = explode(',', $response['tel']);
        $response['loupan_mobile'] = $loupan_mobiles[0];

        return $response;
    }
}