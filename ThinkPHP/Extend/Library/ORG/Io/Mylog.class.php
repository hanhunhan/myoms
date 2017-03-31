<?php
/** 
 * ��־�� 
 * @package_log    log 
 */
//define("LOG_PATH","\tpp\adminpack\log");
class Mylog  
{  
    /** 
     * ��־�ļ���С���� 
     * @var int �ֽ��� 
     */ 
    private static $log_size =  50000000;  
        
    /** 
     * ���õ�����־�ļ���С���� 
     *  
     * @param int $size �ֽ��� 
     */ 
    public static function set_size($size)  
    {  
        if( is_numeric($size) ){  
            self::$log_size = $size;  
        }  
    }  
    
    /** 
     * д��־ 
     * 
     * @param string $log_message ��־��Ϣ 
     * @param string $log_type    ��־���� 
     */ 
    public static function write($log_message, $log_type = 'log')  
    {  
        // �����־Ŀ¼�Ƿ��д  
         if ( !file_exists(LOG_PATH) ) {  
            @mkdir(LOG_PATH);       
        }  
         chmod(LOG_PATH,0777);  
        if (!is_writable(LOG_PATH)) exit('LOG_PATH is not writeable !');  
        $s_now_time = date('[Y-m-d H:i:s]');  
        $log_now_day  = date('Y_m_d');  
        // ��������������־Ŀ��λ��  
        $log_path   = LOG_PATH;  
        switch($log_type)  
        {  
            case 'debug':  
                $log_path .= 'Out_' . $log_now_day . '.log';  
                break;  
            case 'error':  
                $log_path .= 'Err_' . $log_now_day . '.log';  
                break;  
            case 'log':  
                $log_path .= 'Log_' . $log_now_day . '.log';  
                break;  
            case 'every_page':  
                $log_path .= 'every_page_' . $log_now_day . '.log';  
                break; 
            case 'quality':  
                $log_path .= 'quality_' . $log_now_day . '.log';  
                break; 
            case 'pay_ok':  
                $log_path .= 'pay_ok_' . $log_now_day . '.log';  
                break; 
            case 'pay_error':  
                $log_path .= 'pay_error' . $log_now_day . '.log';  
                break; 
            default:  
                $log_path .= 'Log_' . $log_now_day . '.log';  
                break;  
        }  
            
        //�����־�ļ���С, �������ô�С��������  
        if (file_exists($log_path) && self::$log_size <= filesize($log_path)) {
            $s_file_name = substr(basename($log_path), 0, strrpos(basename($log_path), '.log')). '_' . time() . '.log';  
            rename($log_path, dirname($log_path) . DS . $s_file_name);
        }
        clearstatcache();
        // д��־, ���سɹ����  
        $res = error_log("$s_now_time $log_message\n", 3, $log_path);
		//echo $res ? "$s_now_time $log_message\n <br>":'log write error!';
		//echo  "$s_now_time $log_message\n <br>" ;
		 ob_flush();
		 flush();
		return $res;
    }  
}
 