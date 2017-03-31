<?php
    /*** 合同系统接口函数***/
    
    
    /**
    +----------------------------------------------------------
    *根据合同号获取合同开票数据
    +----------------------------------------------------------
    * @param string $citypy 城市拼音
    * @param string $contractnum 合同编号
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
    *根据合同号获取合同回款数据
    +----------------------------------------------------------
    * @param string $citypy 城市拼音
    * @param string $contractnum 合同编号
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
    *根据合同号获取合同基本数据
    +----------------------------------------------------------
    * @param string $citypy 城市拼音
    * @param string $contractnum 合同编号
    * @param string $action 获取内容类型
    +----------------------------------------------------------
    * @return serialize $data 
    +----------------------------------------------------------
    */
    function getContractData($citypy, $contractnum, $action = '')
    {   
        //获取合同基本信息
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
    *开票财务确认后 回写到合同系统中
    +----------------------------------------------------------
    * @param  array $data_arr 开票数据数组
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

            //同步合同开票数据
            $tongji_url = CONTRACT_API."sync_ct_invoice.php?".$param;
            $ret = api_log($city,$tongji_url,0,$user,1);
        }

        return $ret;
    }
    
    
    /**
    +----------------------------------------------------------
    *新增回款记录 会写到合同系统中
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
