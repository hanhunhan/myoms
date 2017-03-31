<?php 
class cache
{
	private static $_instance = null;
	protected $_options = array(
        'cache_dir'        => "./",
        'file_name_prefix' => 'cache',
        'mode'            => '1', //mode 1 Ϊserialize model 2Ϊ����Ϊ��ִ���ļ�
    );  
     
    /**
     * �õ�����ʵ��
     * 
     * @return Ambiguous
     */
    public static function getInstance()
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    } 
     
    /**
     * �õ�������Ϣ
     * 
     * @param string $id
     * @return boolean|array
     */
    public static function get($id)
    {
        $instance = self::getInstance();
         
        //�����ļ�������
        if(!$instance->has($id))
        {
            return false;
        }
         
        $file = $instance->_file($id);
         
        $data = $instance->_fileGetContents($file);
         
        if($data['expire'] == 0 || time() < $data['expire'])
        {
            return $data['contents'];
        }
        return false;
    }
     
    /**
     * ����һ������
     * 
     * @param string $id   ����id
     * @param array  $data ��������
     * @param int    $cacheLife �������� Ĭ��Ϊ0��������
     */
    public static function set($id, $data, $cacheLife = 0)
    {
        $instance = self::getInstance();
         
        $time = time();
        $cache         = array();
        $cache['contents'] = $data;
        $cache['expire']   = $cacheLife === 0 ? 0 : $time + $cacheLife;
        $cache['mtime']    = $time;
         
        $file = $instance->_file($id);
         
        return $instance->_filePutContents($file, $cache);
    }
     
    /**
     * ���һ������
     * 
     * @param string cache id    
     * @return void
     */  
    public static function delete($id)
    {
        $instance = self::getInstance();
         
        if(!$instance->has($id))
        {
            return false;
        }
        $file = $instance->_file($id);
        //ɾ���û���
        return unlink($file);
    }
     
    /**
     * �жϻ����Ƿ����
     * 
     * @param string $id cache_id
     * @return boolean true ������� false ���治����
     */
    public static function has($id)
    {
        $instance = self::getInstance();
        $file     = $instance->_file($id);
         
        if(!is_file($file))
        {
            return false;
        }
        return true;
    }
     
    /**
     * ͨ������id�õ�������Ϣ·��
     * @param string $id
     * @return string �����ļ�·��
     */
    protected function _file($id)
    {
        $instance  = self::getInstance();
        $fileNmae  = $instance->_idToFileName($id);
        return $instance->_options['cache_dir'] . $fileNmae;
    }   
     
    /**
     * ͨ��id�õ�������Ϣ�洢�ļ���
     * 
     * @param  $id
     * @return string �����ļ���
     */
    protected function _idToFileName($id)
    {
        $instance  = self::getInstance();
        $prefix    = $instance->_options['file_name_prefix'];
        return $prefix . '---' . $id;
    }
     
    /**
     * ͨ��filename�õ�����id
     * 
     * @param  $id
     * @return string ����id
     */
    protected function _fileNameToId($fileName)
    {
        $instance  = self::getInstance();
        $prefix    = $instance->_options['file_name_prefix'];
        return preg_replace('/^' . $prefix . '---(.*)$/', '$1', $fileName);
    }
     
    /**
     * ������д���ļ�
     * 
     * @param string $file �ļ�����
     * @param array  $contents ��������
     * @return bool 
     */
    protected function _filePutContents($file, $contents)
    {
        if($this->_options['mode'] == 1)
        {
            $contents = serialize($contents);
        }
        else
        {
            $time = time(); 
            $contents = "<?php\n".
                    " // mktime: ". $time. "\n".
                    " return ".
                    var_export($contents, true).
                    "\n?>";
        }
         
        $result = false;
        $f = @fopen($file, 'w');
        if ($f) {
            @flock($f, LOCK_EX);
            fseek($f, 0);
            ftruncate($f, 0);
            $tmp = @fwrite($f, $contents);
            if (!($tmp === false)) {
                $result = true;
            }
            @fclose($f);
        }
        @chmod($file,0777);
        return $result;             
    }
     
    /**
     * ���ļ��õ�����
     * 
     * @param  sring $file
     * @return boolean|array
     */
    protected function _fileGetContents($file)
    {
        if(!is_file($file))
        {
            return false;
        }
         
        if($this->_options['mode'] == 1)
        {
            $f = @fopen($file, 'r'); 
            @$data = fread($f,filesize($file));
            @fclose($f);
            return unserialize($data);
        }
        else
        {
            return include $file;
        }
    }
     
    /**
     * ���캯��
     */
    protected function __construct()
    {
     
    }
     
    /**
     * ���û���·��
     * 
     * @param string $path
     * @return self
     */
    public static function setCacheDir($path)
    {
        $instance  = self::getInstance();
        if (!is_dir($path)) {
            exit('file_cache: ' . $path.' ����һ����Ч·�� ');
        }
        if (!is_writable($path)) {
            exit('file_cache: ·�� "'.$path.'" ����д');
        }
     
        $path = rtrim($path,'/') . '/';
        $instance->_options['cache_dir'] = $path;
         
        return $instance;
    }
     
    /**
     * ���û����ļ�ǰ׺
     * 
     * @param srting $prefix
     * @return self
     */
    public static function setCachePrefix($prefix)
    {
        $instance  = self::getInstance();
        $instance->_options['file_name_prefix'] = $prefix;
        return $instance;
    }
     
    /**
     * ���û���洢����
     * 
     * @param int $mode
     * @return self
     */
    public static function setCacheMode($mode = 1)
    {
        $instance  = self::getInstance();
        if($mode == 1)
        {
            $instance->_options['mode'] = 1;
        }
        else
        {
            $instance->_options['mode'] = 2;
        }
         
        return $instance;
    }
     
    /**
     * ɾ�����л���
     * @return boolean
     */
    public static function flush()
    {
        $instance  = self::getInstance();
        $glob = @glob($instance->_options['cache_dir'] . $instance->_options['file_name_prefix'] . '--*');
         
        if(empty($glob))
        {
            return false;
        }
         
        foreach ($glob as $v)
        {
            $fileName = basename($v);
            $id =  $instance->_fileNameToId($fileName);
            $instance->delete($id);
        }
        return true;
    }
}

/* ��ʼ������cache��������Ϣʲô�� */
cache::setCachePrefix('core'); //���û����ļ�ǰ׺
cache::setCacheDir('./cache'); //���ô�Ż����ļ���·��
 
//ģʽ1 ����洢��ʽ
//a:3:{s:8:"contents";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:34;i:4;i:5;i:5;i:6;i:6;i:6;}s:6:"expire";i:0;s:5:"mtime";i:1318218422;}
//ģʽ2 ����洢��ʽ
/*
 <?php
 // mktime: 1318224645
 return array (
  'contents' => 
  array (
    0 => 1,
    1 => 2,
    2 => 3,
    3 => 34,
    4 => 5,
    5 => 6,
    6 => 6,
  ),
  'expire' => 0,
  'mtime' => 1318224645,
)
?>
 * 
 * 
 */
cache::setCacheMode('2'); 
 
if(!$row = cache::get('zj2'))
{
     
    $array = array(1,2,3,34,5,6,6);
    $row = cache::set('zj2',$array);
}
// cache::flush(); ������л���
 
print_r($row);