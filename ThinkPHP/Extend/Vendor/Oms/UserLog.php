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

    //��ʼ��
    public static function Init()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @todo �����Ƿ����,����������򴴽��±�
     */
    private function _check_table_is_exist()
    {

        //�жϱ��Ƿ���ڣ����������ɾ��
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
--������
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
     * @param $opt_obj  ��������
     * @param $route    ����·��
     * @param $action_info   ����˵��
     * @param $action_data   ��������
     * @throws Exception
     */
    public function writeLog($opt_obj, $route, $action_info,$action_data = '')
    {
        try {
            //�û�ID
            $log_data['USER_ID'] = $this->uid;
            //������������ID��
            $log_data['OPT_OBJ'] = $opt_obj;
            //����ҳ��

            $pos = strpos($route, '&');
            if ($pos !== false) {
                $route = substr($route, 0, $pos);
            }

            if (strlen($route) < 200) {
                $log_data['ROUTE'] = $route;
            } else {
                $log_data['ROUTE'] = mb_substr($route, 0, 200);
            }

            //����˵��
            $log_data['ACTION'] = $action_info;
            if (strlen($action_data) > 4000) {
                $action_data = mb_substr($action_data, 0, 3500);
            }
            //����˵��
            $log_data['DATA'] = $action_data;
            //IP��ַ
            $log_data['IP'] = $this->_get_client_ip(0);
            //����ʱ��
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
     * @todo ��ѯ������־
     * @param array $map Ŀǰֻ֧���û�id�Ĳ�ѯ.
     */
    public function logList($map = array())
    {

    }

    /**
     * @��ȡ�ͻ���IP��ַ
     * @param integer $type �������� 0 ����IP��ַ  1 ����IPV4��ַ����
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

        // IP��ַ�Ϸ���֤
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}