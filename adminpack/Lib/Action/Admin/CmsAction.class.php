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
        1 => '���Ǿ�����',
        2 => '����׬Ӷ������',
        3 => '��ѿ���ͨͼ',
        4 => '�����ؼ���',
    );

    /*
    public $yaoqiu = "<strong style='color:rgb(235,130,121)'>���Ǿ�����</strong> ͼƬ��������ͷ��,����1������������, ����2��׬ȡ��Ӷ������, ���ݣ����� 62���������ڣ�
        <strong style='color:rgb(235,130,121)'>����׬Ӷ������</strong> ����1�������ˣ������ ���ֻ����롱, ����2��׬��Ӷ��, ����3���Ƽ�����, ���ݣ�С�ֻ�����
        <strong style='color:rgb(235,130,121)'>��ѿ���ͨͼ</strong> ��ѿ��� -> �������·�, ͼƬ��ַ�� ����ͼƬ��ַ ֻ���ϴ�һ�Ż�һ��ͼƬ��ַ
        <strong style='color:rgb(235,130,121)'>�����ؼ���</strong> �������·��ؼ��� �б�, ����1���ؼ���, ����2���ؼ�������";
  */
    public $yaoqiu = "<strong style='color:rgb(235,130,121)'>�����ؼ���</strong> �������·��ؼ��� �б�, ����1���ؼ���, ����2���ؼ�������";

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
                $this->error('��Ŀ���Ʋ���Ϊ�գ�',U("Cms/category"));
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
                $this->success('�޸ĳɹ���',U("Cms/index"));
            }
            else
            {
                $this->error('�޸�ʧ�ܣ�',U("Cms/index"));
            }
            die();
        }
    }

    /**
     * �����б�
     */
    public function content_list()
    {
        $cid = intval($this->_get('id'));

        //��Ŀ
        $info = M('cms_category')->where('cid='.$cid .' AND city=\''.$this->city.'\'')->select();
        $info = $info[0];

        import("ORG.Util.Page");
        $count = M('cms_content')->where('catid='.$cid .' AND city=\''.$this->city.'\'')->count();
        $p = new Page($count, C('PAGESIZE'));
        $page = $p->show();

        $listinfo = M('cms_content')->where('catid='.$cid .' AND city=\''.$this->city.'\'')->limit($p->firstRow . ',' . $p->listRows)->order('`order` desc')->select();

        foreach ($listinfo as $key => $value)
        {
            $listinfo[$key]['display'] = $value['display'] == 1 ? '��' : '��';
        }

        $this->assign('page', $page);
        $this->assign('re', $listinfo);
        $this->assign('info', $info);
        $this->display('content_list');
    }

    /**
     * �༭���������
     */
    public function content()
    {
        $nid = intval($this->_get('nid'));
        if(!$this->_post('dosubmit'))
        {
            //��Ŀ
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
                        die("<script type=\"text/javascript\">alert('�ļ�����ʧ��,����ԭ��$rtnMSG'); </script>");
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
                $this->success('�޸ĳɹ���',U("Cms/content_list", array('id' => $catid)));
            }
            else
            {
                $this->error('�޸�ʧ�ܣ�',U("Cms/content", array('cid' => $catid, 'nid' => $nid)));
            }
            die();

        }
    }

    /**
     * ��Ŀɾ��
     */
    public function del_category()
    {
        $id = $this->_get('id');
        $count = M('cms_content')->where('catid='.$id .' AND city=\''.$this->city.'\'')->count();
        if($count> 0)
        {
            $this->error('����ɾ���ǿ���Ŀ��',U("Cms/index"));
        }
        if(M('cms_category')->delete($id))
        {
            $this->success('ɾ���ɹ�');
        }else{
            $this->success('ɾ��ʧ��');
        }
    }

    /**
     * ����ɾ��
     */
    public function del_content()
    {
        $id = $this->_get('nid');
        if(M('cms_content')->delete($id))
        {
            $this->success('ɾ���ɹ�');
        }else{
            $this->success('ɾ��ʧ��');
        }
    }
}

// END CmsAction class

/* End of file CmsAction.class.php */
/* Location: ${FILE_PATH}/CmsAction.class.php */ 
