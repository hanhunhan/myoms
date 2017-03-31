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
            $this->success('删除成功！',$_SERVER['HTTP_REFERER']);
        }
        else
        {
            $this->error('删除失败！',$_SERVER['HTTP_REFERER']);
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
                $this->success('修改成功！',U("Qa/answer", array('id'=>$id)));
            }
            else
            {
                $this->error('修改失败！',U("Qa/answer", array('id'=>$id)));
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
     * 全部问答
     */
    public function all($wait = 0, $title = '全部问答')
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
            $listinfo[$key]['ansed'] = !empty($user['answer']) ? '<span style="color:#808080">已回答</span>' : '<span style="color:darkred">回答</span>';
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
        $this->all(1, '待回答');
    }


    public function _get_prj_by_listid($listid, $city)
    {
        /*模拟前台缓存环境*/
        include_once '../WEB-INF/config.inc.php';
        include_once '../functions/global.func.php';
        /*初始化  memcache*/
        global $memcache;
        $memcache =new Memcache;
        foreach ($memcache_conf as $memcache_confitem)
        {
            $memcache->addserver($memcache_confitem['host'], $memcache_confitem['port']);
        }
        /*模拟前台缓存环境*/

        $api = 'http://api.house365.com/xf/newhouse/get_prj_365tf.php?prj_listid='.$listid.'&city='.$city;
        $response = fetch_from_cache(3600, 1, 'curl_get_contents', $api);
        if(empty($response[0]))
        {
            return array();
        }
        $response = $response[0];

        /*楼盘信息处理*/
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
        $response['price'] = $response['price'] ? $response['price'].$response['price_more'] : '待定';

        /*主力户型*/
        $response['prj_main_force_string'] = '';
        $hx=unserialize($response['prj_main_force']);
        if(!empty($hx)){
            foreach($hx as $val){
                $response['prj_main_force_string'] .= $val['content']." ";
            }
        }

        /*表格内内容*/
        //均价
        if( !empty($response['pd_price']) ) {
            if( $response['pd_price'] < 1000 ) {
                $response['pd_price_str'] = $response['pd_price'].'万/套';
            } else {
                $response['pd_price_str'] = $response['pd_price'].'元/平';
            }
        } else {
            $response['pd_price_str'] = '待定';
        }

        //团购、口碑
        if( CITY == 'nj' ) {
            $response['result_tuan']      = 'http://newhouse.house365.com/'.$response['prj_pinyin'];
            $response['result_forumlink'] = 'http://bbs.house365.com/forumdisplay.php?forumid='.$response['prj_forumid'];
        } else {
            $response['result_tuan']     = 'http://newhouse.'.CITY.'.house365.com/'.$response['prj_pinyin'];
            $response['result_forumlink']= 'http://bbs.'.CITY.'.house365.com/forumdisplay.php?forumid='.$response['prj_forumid'];
        }
        //楼盘电话
        $loupan_mobiles = explode(',', $response['tel']);
        $response['loupan_mobile'] = $loupan_mobiles[0];

        return $response;
    }
}