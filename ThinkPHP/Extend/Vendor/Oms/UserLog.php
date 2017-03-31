<?php
/**
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2016/5/20
 * Time: 14:40
 */
import('ORG.Io.Mylog');
 
class UserLog
{
    protected $tablePrefix = 'erp_';
    protected $tableName = 'log';
    private $uid;
    private static $instance = null;

    public function __construct()
    {
        $this->uid = $_SESSION['uinfo']['uid'];
    }

    public function __destruct()
    {
    }

    //初始化
    public static function Init()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @todo 检测表是否存在,如果不存在则创建新表
     */
    private function _check_table_is_exist()
    {

        //判断表是否存在，如果存在则删除
        $create_table_sql = <<<TABLE_SQL
declare
num   number;
begin
      select count(1) into num from all_tables where TABLE_NAME = 'EMP' and OWNER='SCOTT';
      if   num=1   then
          execute immediate 'drop table EMP';
      end  if;
end;
/
--创建表
CREATE TABLE EMP
        (EMPNO NUMBER(4) NOT NULL,
        ENAME VARCHAR2(10),
        JOB VARCHAR2(9),
        MGR NUMBER(4),
        HIREDATE DATE,
        SAL NUMBER(7, 2),
        COMM NUMBER(7, 2),
        DEPTNO NUMBER(2));

    }
TABLE_SQL;


    }

    /**
     * @param $opt_obj  操作对象
     * @param $route    操作路径
     * @param $action_info   操作说明
     * @param $action_data   核心数据
     * @throws Exception
     */
    public function writeLog($opt_obj, $route, $action_info,$action_data = '')
    {
        try {
            //用户ID
            $log_data['USER_ID'] = $this->uid;
            //操作对象（主键ID）
            $log_data['OPT_OBJ'] = $opt_obj;
            //操作页面

            $pos = strpos($route, '&');
            if ($pos !== false) {
                $route = substr($route, 0, $pos);
            }

            if (strlen($route) < 200) {
                $log_data['ROUTE'] = $route;
            } else {
                $log_data['ROUTE'] = mb_substr($route, 0, 200);
            }

            //操作说明
            $log_data['ACTION'] = $action_info;
            if (strlen($action_data) > 4000) {
                $action_data = mb_substr($action_data, 0, 3500);
            }
            //操作说明
            $log_data['DATA'] = $action_data;
            //IP地址
            $log_data['IP'] = $this->_get_client_ip(0);
            //操作时间
            $log_data['CREATEDATE'] = date("Y-m-d H:i:s", time());

            $add = M($this->tablePrefix . $this->tableName)->add($log_data);
            if (!$add) {
//                Mylog::write(implode($log_data, ","));/
            }


        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @todo 查询操作日志
     * @param array $map 目前只支持用户id的查询.
     */
    public function logList($map = array())
    {

    }

    /**
     * @获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址  1 返回IPV4地址数字
     * @return mixed
     */
    private function _get_client_ip($type = 0)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}