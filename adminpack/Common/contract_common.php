<?php
    /*** ��ͬϵͳ�ӿں���***/
    
    
    /**
    +----------------------------------------------------------
    *���ݺ�ͬ�Ż�ȡ��ͬ��Ʊ����
    +----------------------------------------------------------
    * @param string $citypy ����ƴ��
    * @param string $contractnum ��ͬ���
    +----------------------------------------------------------
    * @return serialize $data 
    +----------------------------------------------------------
    */
    function get_invoice_data_by_no($citypy, $contractnum)
    {   
        $data = '';
        
        $data = getContractData($citypy, $contractnum, 'invoice');
        //var_dump($data);die;
        return $data;
    }
    
    
    /**
    +----------------------------------------------------------
    *���ݺ�ͬ�Ż�ȡ��ͬ�ؿ�����
    +----------------------------------------------------------
    * @param string $citypy ����ƴ��
    * @param string $contractnum ��ͬ���
    +----------------------------------------------------------
    * @return serialize $data 
    +----------------------------------------------------------
    */
    function get_backmoney_data_by_no($citypy, $contractnum)
    {   
        $data = '';
        
        $data = getContractData($citypy, $contractnum, 'backmoney');
        
        return $data;
    }
    
    
    /**
    +----------------------------------------------------------
    *���ݺ�ͬ�Ż�ȡ��ͬ��������
    +----------------------------------------------------------
    * @param string $citypy ����ƴ��
    * @param string $contractnum ��ͬ���
    * @param string $action ��ȡ��������
    +----------------------------------------------------------
    * @return serialize $data 
    +----------------------------------------------------------
    */
    function getContractData($citypy, $contractnum, $action = '')
    {   
        //��ȡ��ͬ������Ϣ
        $citypy = strip_tags($citypy);
        $contractnum = strip_tags($contractnum);
        $action = strip_tags($action);
        
        $url = CONTRACT_API."get_ct_info.php?city=$citypy&contractnum=$contractnum&"
                . "action=$action";
        $data = curl_get_contents($url, 'get');
        $data = unserialize($data);
        return $data;
    }
    
    
    /**
    +----------------------------------------------------------
    *��Ʊ����ȷ�Ϻ� ��д����ͬϵͳ��
    +----------------------------------------------------------
    * @param  array $data_arr ��Ʊ��������
    +----------------------------------------------------------
    * @return array $data
    +----------------------------------------------------------
    */
    function saveInvoice2Con($data_arr)
    {
        $city = $_SESSION['uinfo']['city'];
        $user = $_SESSION['uinfo']['uid'];

        $param = "";
        if(is_array($data_arr) && !empty($data_arr))
        {
            foreach ($data_arr as $key=>$val)
            {
				$param .= "###$key=$val";
            }
            $param = ltrim($param,"###");

            //ͬ����ͬ��Ʊ����
            $tongji_url = CONTRACT_API."sync_ct_invoice.php?".$param;
            $ret = api_log($city,$tongji_url,0,$user,1);
        }

        return $ret;
    }
    
    
    /**
    +----------------------------------------------------------
    *�����ؿ��¼ ��д����ͬϵͳ��
    +----------------------------------------------------------
    * @param  none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    function saveRefund2Con($data_arr)
    {
        $param = "";
        $resarr = array();

        if(is_array($data_arr) && !empty($data_arr))
        {
            foreach ($data_arr as $key=>$val)
            {
                $param .= "$key=$val&";
            }
            
            $param = rtrim($param,"&"); 
            $url = CONTRACT_API."sync_ct_backmoney.php?".$param;
            //echo $url;die;
            $res = curl_get_contents($url , 'get');
            $resarr = unserialize($res);
        }
        
        return $resarr;           
    }
