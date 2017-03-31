<?php
/**
 * 365tf
 *
 * An open source application development framework for PHP 5.3 or newer
 *
 * @package    ${PACKAGE_NAME}
 * @author     <yangzhiguo0903@gmail.com>
 * @copyright  Copyright (c) 2010 - 2014, Yzg, Inc.
 * @license    ${LICENSE_LINK}
 * @link       ${PROJECT_LINK}
 * @since      Version ${VERSION} 2014-05-04 8:53 $
 * @filesource CmsAction.class.php
 */

// ------------------------------------------------------------------------

/**
 * CmsAction
 *
 * ${CLASS_DESCRIPTION}
 *
 * @package     ${PACKAGE_NAME}
 * @subpackage  ${SUBPACKAGE_NAME}
 * @category    ${CATEGORY_NAME}
 * @author      <yangzhiguo0903@gmail.com>
 * @link        ${FILE_LINK}/CmsAction.class.php
 */

class CmsAction extends ExtendAction
{
    public $name = array(
        1 => '明星经纪人',
        2 => '本月赚佣金排行',
        3 => '免费看房通图',
        4 => '搜索关键词',
    );

    /*
    public $yaoqiu = "<strong style='color:rgb(235,130,121)'>明星经纪人</strong> 图片：经纪人头像,标题1：经纪人姓名, 标题2：赚取的佣金数字, 内容：描述 62个汉字以内！
        <strong style='color:rgb(235,130,121)'>本月赚佣金排行</strong> 标题1：经纪人：后面的 “手机号码”, 标题2：赚的佣金, 标题3：推荐套数, 内容：小手机号码
        <strong style='color:rgb(235,130,121)'>免费看房通图</strong> 免费看房 -> 导航正下方, 图片地址： 背景图片地址 只能上传一张或一个图片地址
        <strong style='color:rgb(235,130,121)'>搜索关键词</strong> 搜索框下方关键词 列表, 标题1：关键词, 标题2：关键词链接";
  */
    public $yaoqiu = "<strong style='color:rgb(235,130,121)'>搜索关键词</strong> 搜索框下方关键词 列表, 标题1：关键词, 标题2：关键词链接";

    public function index()
    {
        import("ORG.Util.Page");
        $count = M('cms_category')->count();
        $p = new Page($count, C('PAGESIZE'));
        $page = $p->show();

        $listinfo = M('cms_category')->where('city=\''.$this->city.'\'')->limit($p->firstRow . ',' . $p->listRows)->order('`order` desc')->select();
        foreach ($listinfo as & $v)
        {
            $v['description'] = nl2br($v['description']);
        }

        $this->assign('page', $page);
        $this->assign('re', $listinfo);
        $this->display('list');
    }

    public function category()
    {
        $cid = intval($this->_get('id'));

        if(!$this->_post('dosubmit'))
        {
            if($cid)
            {
                $info = M('cms_category')->where('cid='.$cid .' AND city=\''.$this->city.'\'')->select();
                $info = $info[0];
            }

            $this->assign('infoname', array_flip($this->name));
            $this->assign('yaoqiu', nl2br($this->yaoqiu));
            $this->assign('info', $info);
            $this->display('edit');
        }
        else
        {
            $pcid = intval($_POST['cid']);
            $info = $_POST['info'];
            $info['name'] = $this->name[$info['name']];

            if(empty($info['name']))
            {
                $this->error('栏目名称不能为空！',U("Cms/category"));
            }
            if($pcid> 0 )
            {
                $affected = M('cms_category')->where("cid='$pcid' AND city='".$this->city .'\'')->save($info);
            }
            else
            {
                $info['city'] = $this->city;
                $affected = M('cms_category')->add($info);
            }

            if($affected)
            {
                $this->success('修改成功！',U("Cms/index"));
            }
            else
            {
                $this->error('修改失败！',U("Cms/index"));
            }
            die();
        }
    }

    /**
     * 内容列表
     */
    public function content_list()
    {
        $cid = intval($this->_get('id'));

        //栏目
        $info = M('cms_category')->where('cid='.$cid .' AND city=\''.$this->city.'\'')->select();
        $info = $info[0];

        import("ORG.Util.Page");
        $count = M('cms_content')->where('catid='.$cid .' AND city=\''.$this->city.'\'')->count();
        $p = new Page($count, C('PAGESIZE'));
        $page = $p->show();

        $listinfo = M('cms_content')->where('catid='.$cid .' AND city=\''.$this->city.'\'')->limit($p->firstRow . ',' . $p->listRows)->order('`order` desc')->select();

        foreach ($listinfo as $key => $value)
        {
            $listinfo[$key]['display'] = $value['display'] == 1 ? '是' : '否';
        }

        $this->assign('page', $page);
        $this->assign('re', $listinfo);
        $this->assign('info', $info);
        $this->display('content_list');
    }

    /**
     * 编辑或添加内容
     */
    public function content()
    {
        $nid = intval($this->_get('nid'));
        if(!$this->_post('dosubmit'))
        {
            //栏目
            $cid  = $this->_get('cid');
            if($nid){
                $where = 'nid='.$nid .' ';
                $info = M('cms_category')->join(" cms_content c on cms_category.cid = c.catid")->order('c.`order` desc')->where($where)->select();
            }else{
                $where = 'cid='.$cid .' ';
                $info = M('cms_category')->where($where)->select();
            }
            $info = $info[0];
            $this->assign('info', $info);
            $this->assign('nid', $nid);
            $this->display('content_edit');
        }
        else
        {
            if(!empty($_FILES['imageupload']['tmp_name']))
            {
                import("ORG.Util.UploadFile");
                if($_FILES['imageupload']['tmp_name'])
                {
                    $_FILES['cms_365tf_0'] = array();
                    $_FILES['cms_365tf_0']['name']=$_FILES['imageupload']['name'];
                    $_FILES['cms_365tf_0']['type']=$_FILES['imageupload']['type'];
                    $_FILES['cms_365tf_0']['tmp_name']=$_FILES['imageupload']['tmp_name'];
                    $_FILES['cms_365tf_0']['error']=$_FILES['imageupload']['error'];
                    $_FILES['cms_365tf_0']['size']=$_FILES['imageupload']['size'];
                    $uf=new UploadFile('cms_365tf_0');
                    $uf->setMaxSize(2048);
                    $uf->setResizeImage(false);
//                    $uf->setResizeWidth(230);
//                    $uf->setResizeHeight(230);
                    $uf->setUploadType("ftp");
                    $uf->setSaveDir("/".$this->city."/");
                    $uf->setShowAsChinese(true);

                    if(($rtnMSG=$uf->upload())=="success")
                    {
                        $imgsrc =$uf->getSaveFileURL();
                    }
                    else
                    {
                        die("<script type=\"text/javascript\">alert('文件操作失败,可能原因：$rtnMSG'); </script>");
                    }
                }
            }

            $item = $_POST['item'];
            if($imgsrc)
            {
                $item['imagesrc'] = $imgsrc;
            }

            if($nid = intval($this->_post('nid')))
            {
                $item['updatetime'] = date('Y-m-d H:i:s');
                $affected = M('cms_content')->where("nid='$nid'" .' AND city=\''.$this->city.'\'')->save($item);
            }
            else
            {
                $item['city'] = $this->city;
                $item['updatetime'] = $item['createtime'] = date('Y-m-d H:i:s');
                $affected = M('cms_content')->add($item);
            }

            $catid = $item['catid'];
            if($affected)
            {
                $this->success('修改成功！',U("Cms/content_list", array('id' => $catid)));
            }
            else
            {
                $this->error('修改失败！',U("Cms/content", array('cid' => $catid, 'nid' => $nid)));
            }
            die();

        }
    }

    /**
     * 栏目删除
     */
    public function del_category()
    {
        $id = $this->_get('id');
        $count = M('cms_content')->where('catid='.$id .' AND city=\''.$this->city.'\'')->count();
        if($count> 0)
        {
            $this->error('不能删除非空栏目！',U("Cms/index"));
        }
        if(M('cms_category')->delete($id))
        {
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }

    /**
     * 内容删除
     */
    public function del_content()
    {
        $id = $this->_get('nid');
        if(M('cms_content')->delete($id))
        {
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
}

// END CmsAction class

/* End of file CmsAction.class.php */
/* Location: ${FILE_PATH}/CmsAction.class.php */ 
