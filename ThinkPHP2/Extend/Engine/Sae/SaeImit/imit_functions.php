<?php
//ģ������Ҫ�õĺ���
function Imit_L($key){
    static $msgs=array();
    if(is_array($key)){
        $msgs=array_merge($msgs,$key);
        return ;
    }
    return $msgs[$key];
    
}
