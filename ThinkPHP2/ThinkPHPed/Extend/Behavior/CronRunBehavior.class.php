<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: CronRunBehavior.class.php 2616 2012-01-16 08:36:46Z liu21st $

class CronRunBehavior extends Behavior {
    protected $options   =  array(
            'CRON_MAX_TIME'=>60,
        );
    public function run(&$params) {
        // �����Զ�ִ��
        $lockfile	 =	 RUNTIME_PATH.'cron.lock';
        if(is_writable($lockfile) && filemtime($lockfile) > $_SERVER['REQUEST_TIME'] - C('CRON_MAX_TIME')) {
            return ;
        } else {
            touch($lockfile);
        }
        set_time_limit(1000);
        ignore_user_abort(true);

        // ����cron�����ļ�
        // ��ʽ return array(
        // 'cronname'=>array('filename',intervals,nextruntime),...
        // );
        if(is_file(RUNTIME_PATH.'~crons.php')) {
            $crons	=	include RUNTIME_PATH.'~crons.php';
        }elseif(is_file(CONF_PATH.'crons.php')){
            $crons	=	include CONF_PATH.'crons.php';
        }
        if(isset($crons) && is_array($crons)) {
            $update	 =	 false;
            $log	=	array();
            foreach ($crons as $key=>$cron){
                if(empty($cron[2]) || $_SERVER['REQUEST_TIME']>=$cron[2]) {
                    // ����ʱ�� ִ��cron�ļ�
                    G('cronStart');
                    include LIB_PATH.'Cron/'.$cron[0].'.php';
                    $_useTime	 =	 G('cronStart','cronEnd', 6);
                    // ����cron��¼
                    $cron[2]	=	$_SERVER['REQUEST_TIME']+$cron[1];
                    $crons[$key]	=	$cron;
                    $log[] = "Cron:$key Runat ".date('Y-m-d H:i:s')." Use $_useTime s\n";
                    $update	 =	 true;
                }
            }
            if($update) {
                // ��¼Cronִ����־
                Log::write(implode('',$log));
                // ����cron�ļ�
                $content  = "<?php\nreturn ".var_export($crons,true).";\n?>";
                file_put_contents(RUNTIME_PATH.'~crons.php',$content);
            }
        }
        // �������
        unlink($lockfile);
        return ;
    }
}