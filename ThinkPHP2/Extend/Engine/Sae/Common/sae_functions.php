<?php
//ƽ��������sae�ͱ��ض������ã�����ϵͳƽ����
function sae_unlink($filePath) {
    if (IS_SAE) {
        $arr = explode('/', ltrim($filePath, './'));
        $domain = array_shift($arr);
        $filePath = implode('/', $arr);
        $s = Think::instance('SaeStorage');
        return $s->delete($domain, $filePath);
    } else {
        return unlink($filePath);
    }
}