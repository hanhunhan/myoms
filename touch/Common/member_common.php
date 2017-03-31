<?php
/**
 * submit_crm_data_by_api
 *
 * 提交CRM接口数据
 *
 * @access  public
 * @param   array   $api_arr 接口需要提交的数据
 * @param   string  $sub_type 接口提交方式,POST/GET
 * @return  boolean 是否提交成功
*/
function submit_crm_data_by_api($api_arr, $sub_type = 'GET')
{
    $result = FALSE;
    $crm_api_url = P_CRM.'index.php/Simulate/sea?';

    if(is_array($api_arr) && !empty($api_arr))
    {
        $crm_url_ext = '';
        foreach($api_arr as $key => $value)
        {
            $crm_url_ext .= $crm_url_ext != '' ? '&'.$key.'='.$value : $key.'='.$value;
        }

        $crm_api_url .= $crm_url_ext;
		//test
		$crm_api_url .= "&solute=1";

        $result = curl_get_contents($crm_api_url, $sub_type);
    }
    return $result;
}

/**
 * get_userinfo_by_code
 *
 * 根据验证码获取（CRM和房管家合并后的）用户信息
 *
 * @access  public
 * @param   int $code   验证码参数
 * @param   int $prolist_id   楼盘ID
 * @return  array  客户信息
*/
function get_userinfo_by_code($code, $prolist_id = 0)
{
	/***根据验证码和城市参数获取CRM用户数据***/
	$userinfo_arr = array('result' => 0 , 'is_from' => 0);

	$code = intval($code);

	//首先通过验证码获取CRM用户信息
	$userinfo_crm_arr = get_crm_userinfo_by_code($code);
    //var_dump($userinfo_crm_arr);die;
	if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1)
	{
		$userinfo_arr['result'] = 1;
		//数据来源
		$userinfo_arr['is_from'] = 1;
		//CRM中用户ID
		$userinfo_arr['customer_id'] = $userinfo_crm_arr['meminfo']['pmid'];
		//楼盘id
		$userinfo_arr['projectid']
				= $userinfo_crm_arr['meminfo']['projectid'];
		//客户姓名
		$userinfo_arr['truename']
				= $userinfo_crm_arr['meminfo']['memname'];
		//客户手机号码
		$userinfo_arr['telno'] = $userinfo_crm_arr['meminfo']['memphone'];
	}
	else
	{
		//如果CRM没有数据，则通过房管家系统查询是否存在用户信息
		$userinfo_fgj_arr = get_fgj_userinfo_by_code($code);

		if(is_array($userinfo_fgj_arr) && $userinfo_fgj_arr['result'] == 1)
		{
			$userinfo_arr['result'] = 1;
			//数据来源
			$userinfo_arr['is_from'] = 2;

			if($prolist_id > 0)
			{
				foreach($userinfo_fgj_arr['data'] as $key => $value)
				{
					if($prolist_id == $value['lp_id'])
					{
						//楼盘ID
						$userinfo_arr['projectid'] = $value['lp_id'];
						//客户姓名
						$userinfo_arr['truename'] = $value['cm_name'];
						//客户电话
						$userinfo_arr['telno'] = $value['cm_phone'];
						//客户id
						$userinfo_arr['customer_id'] = intval($value['cm_id']);
						//经纪人id
						$userinfo_arr['ag_id'] = $value['ag_id'];
						//报备id
						$userinfo_arr['cp_id'] = $value['cp_id'];
						break;
					}
				}
			}
			else
			{
				//楼盘ID
				$userinfo_arr['projectid'] = $userinfo_fgj_arr['data'][0]['lp_id'];
				//客户姓名
				$userinfo_arr['truename'] = $userinfo_fgj_arr['data'][0]['cm_name'];
				//客户电话
				$userinfo_arr['telno'] = $userinfo_fgj_arr['data'][0]['cm_phone'];
				//客户id
				$userinfo_arr['customer_id'] = intval($userinfo_fgj_arr['data'][0]['cm_id']);
				//经纪人id
				$userinfo_arr['ag_id'] = $userinfo_fgj_arr['data'][0]['ag_id'];
				//报备id
				$userinfo_arr['cp_id'] = $userinfo_fgj_arr['data'][0]['cp_id'];
			}
		}
	}

	return $userinfo_arr;
}

/**
 * get_userinfo_by_pid_telno
 *
 * 根据项目ID和手机号码获取（CRM和房管家合并后的）用户信息
 *
 * @access  public
 * @param   int $project_id 项目ID
 * @param   string $telno   手机号码
 * @param   int $pro_listid 楼盘ID
 * @return  array  客户信息
*/
function get_userinfo_by_pid_telno($project_id, $telno, $pro_listid, $city_spell = 'nj')
{
	/***根据验证码和城市参数获取CRM用户数据***/
	$userinfo_arr = array('result' => 0);
    
    $city_spell = strtolower($city_spell);
	//通过验证码获取CRM用户信息
	$userinfo_crm_arr = get_crm_userinfo_by_pid_telno($project_id, $telno, $city_spell);
	if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 &&
			$userinfo_crm_arr['meminfo']['codestatus'] != null)
	{
		$userinfo_arr['result'] = 1;
		$userinfo_arr['is_from_crm'] = 1;//数据来源
		$userinfo_arr['user_num_crm'] = 1;

		//CRM中用户ID
		$userinfo_arr['crm_user']['customer_id'] = $userinfo_crm_arr['meminfo']['pmid'];
		//客户姓名
		$userinfo_arr['crm_user']['truename'] = $userinfo_crm_arr['meminfo']['memname'];
		//客户验证码
		$userinfo_arr['crm_user']['code'] = $userinfo_crm_arr['meminfo']['code'];
		//验证码状态[0未到场 1已到场 2已过期/未发送]
		$userinfo_arr['crm_user']['confirm_status'] = $userinfo_crm_arr['meminfo']['codestatus'];

		//用户来源
		$userinfo_arr['crm_user']['usersource'] = $userinfo_crm_arr['meminfo']['usersource'];
	}

	//通过房管家系统查询是否存在用户信息
	$user_num = 0;
	$userinfo_fgj_arr = get_fgj_userinfo_by_pid_telno($pro_listid , $telno);
	if(is_array($userinfo_fgj_arr) && $userinfo_fgj_arr['result'] == 1 &&
			!empty($userinfo_fgj_arr['data']))
	{
		$is_need_confirm = 1;
		foreach ($userinfo_fgj_arr['data'] as $key => $value)
		{
			//0表示未过保护期，1表示已经过了保护期
			if($value['overProtection'] == 0 &&
					($value['status'] == 0 || $value['status'] == 1))
			{
				//客户id
				$userinfo_arr['fgj_user'][$user_num]['customer_id'] = intval($value['cm_id']);
				//客户姓名
				$userinfo_arr['fgj_user'][$user_num]['truename'] = $value['cm_name'];
				//客户验证码
				$userinfo_arr['fgj_user'][$user_num]['code'] = $value['code'];
				//用户来源
				$userinfo_arr['fgj_user'][$user_num]['usersource'] = 1;
				//到场确认状态 1未带看，0代表已带看，2代表带看失败，3代表报备中
				$userinfo_arr['fgj_user'][$user_num]['confirm_status'] = $value['status'];
				//返回数据中存在已到场确认信息，则不需要再次确认
				if( $value['status'] == 0)
				{
					$is_need_confirm = 0;
				}

				//是否已经过了保护期
				$userinfo_arr['fgj_user'][$user_num]['overProtection'] = $value['overProtection'];
				//经纪人id
				$userinfo_arr['fgj_user'][$user_num]['ag_id'] = $value['ag_id'];
				//报备id
				$userinfo_arr['fgj_user'][$user_num]['cp_id'] = $value['cp_id'];
				++ $user_num;
			}
		}

		if($user_num > 0 )
		{
			$userinfo_arr['result'] = 2;
			$userinfo_arr['is_from_fgj'] = 2;//数据来源
			$userinfo_arr['user_num_fgj'] = $user_num;
			$userinfo_arr['is_need_confirm_fgj'] = $is_need_confirm;
		}
	}

	if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 &&
			isset($userinfo_arr['user_num_crm']) && $userinfo_arr['user_num_crm'] >= 1 && $user_num > 0)
	{
		   $userinfo_arr['result'] = 3;
	}

	return $userinfo_arr;
}

 /**
 * get_userinfo_by_telno
 *
 * 根据用户手机号码 及经纪人手机号码获取用户信息，
 * 如果用户没有填写经纪人手机号码，通过CRM系统查询，
 * 如果有填写经纪人手机号码通过FGJ系统进行查询
 *
 * @access  public
 * @param   int $project_id 项目ID
 * @param   string $customer_telno   客户手机号码
 * @param   string $agent_telno   经济人手机号码
 * @return  array  客户信息
*/
function get_userinfo_by_telno($project_id, $customer_telno, $agent_telno = '')
{
	$userinfo_arr = array();
	$project_id = intval($project_id);
	$customer_telno = strip_tags($customer_telno);
	$agent_telno = strip_tags($agent_telno);

	//用户只填写了用户手机号码，未填写经纪人手机号码，从CRM系统查询信息
   if(strlen($customer_telno) == 11 && strlen($agent_telno) == 0)
   {
		$api_crm = P_CRM_API."rt_get_capcode?projectid=".$project_id."&phone=".$customer_telno;
		$userinfo_crm_json = '';
		$userinfo_crm_json = curl_get_contents($api_crm , 'get');
		$userinfo_crm_arr = json_decode($userinfo_crm_json , TRUE);
		$userinfo_arr['result'] = 0;
		$userinfo_arr["is_from"] = 1;//表示信息来源于CRM系统
		if(is_array($userinfo_crm_arr) && !empty($userinfo_crm_arr) &&
				$userinfo_crm_arr['result'] == 1)
		{
			$userinfo_arr['truename'] = $userinfo_crm_arr['meminfo']['memname'];
			$userinfo_arr['cm_id'] = $userinfo_crm_arr['meminfo']['pmid'];
			$userinfo_arr['code'] = $userinfo_crm_arr['meminfo']['code'];
			$userinfo_arr['result'] = $userinfo_crm_arr['result'];
		}
   }
   else if(strlen($customer_telno) == 11  && strlen($agent_telno) == 11)
   {
		$api_fgj = P_FGJ_API."method=getCodeByPhone&ver=v1&lp_id=".$project_id."&"
				. "cm_phone=".$customer_telno."&ag_phone=".$agent_telno;
		$userinfo_fgj_json = '';
		$userinfo_fgj_json = curl_get_contents($api_fgj , 'get');
		$userinfo_fgj_arr = json_decode($userinfo_fgj_json , TRUE);
		$userinfo_arr['result'] = 0;
		$userinfo_arr["is_from"] = 2;//表示信息来源于FGJ系统
		if(is_array($userinfo_fgj_arr) && !empty($userinfo_fgj_arr) && !empty($userinfo_fgj_arr['data'])
				&& $userinfo_fgj_arr['result'] == 1)
		{
			$userinfo_arr['truename'] = $userinfo_fgj_arr['data']['cm_name'];
			$userinfo_arr['cm_id'] = $userinfo_fgj_arr['data']['cm_id'];
			$userinfo_arr['code'] = $userinfo_fgj_arr['data']['code'];
			$userinfo_arr['ag_id'] = $userinfo_fgj_arr['data']['ag_id'];
			$userinfo_arr['cp_id'] = $userinfo_fgj_arr['data']['cp_id'];
			$userinfo_arr['result'] = $userinfo_fgj_arr['result'];
		}
   }
	return $userinfo_arr;
}

/**
 * arrival_confirm_crm
 *
 * 设置客户状态带看状态(CRM系统)
 *
 * @access  public
 * @param   int $pmid CRM客户编号
 * @param   int $code 验证码
 * @return  array  验证结果
*/
function arrival_confirm_crm( $pmid , $code )
{
	//根据验证码获取CRM用户数据
	$pmid = intval($pmid);
	$code = intval($code);
	$result = array();
	if($pmid > 0 && $code > 0 )
	{
		$auth_key = md5(date('Ymd').'3653');
		$api_crm = P_CRM_API."rt_dcyz?crmkey=".$auth_key."&pmid=".$pmid."&code=".$code;

		$confirm_json = '';
		$confirm_json = curl_get_contents($api_crm , 'get');

		$result = json_decode( $confirm_json , TRUE);
	}
	return $result;
}

/**
 * arrival_confirm_fgj
 *
 * 设置客户状态带看状态（房管家系统）
 *
 * @access  public
 * @param   int $cm_id 客户ID
 * @param   int $ag_id 经纪人ID
 * @param   int $cp_id 报备ID
 * @return  array  验证结果
*/
function arrival_confirm_fgj( $cm_id , $ag_id , $cp_id)
{
	//根据验证码获取CRM用户数据
	$cm_id = intval($cm_id);
	$ag_id = intval($ag_id);
	$cp_id = intval($cp_id);

	$result = array();

	if( $cm_id > 0 && $ag_id > 0 && $cp_id > 0 )
	{
		$api_crm = P_FGJ_API."method=setCpStatus&ver=v1&cm_id=".$cm_id."&ag_id=".$ag_id."&cp_id=".$cp_id;
		$confirm_json = '';
		$confirm_json = curl_get_contents($api_crm , 'get');
		$result = json_decode( $confirm_json , TRUE);
	}

	return $result;
}


/**
 * register_natural_customer
 *
 * CRM 注册自然来客
 *
 * @access  public
 * @param   sting $city 城市拼音缩写
 * @param   sting $username 客户姓名
 * @param   sting $mobile 客户手机号码
 * @param   int $project_id 通用项目编号
 * @param   sting $project_name 项目名称
 * @return  array  注册结果
*/
function register_natural_customer($city , $username , $mobile , $project_id , $project_name)
{
	$url = P_CRM.'index.php/Simulate/sea?';
	$url .= "mobile=$mobile";
	$url .= "&username=".urlencode(u2g($username));
	$url .= "&activefrom=231"; //电商客户-自然来客
	$url .= "&city=$city";
	$url .= "&activename=".urlencode(u2g($project_name.'自然来客'));
	$url .= "&loupanids=$project_id";
	$result = curl_get_contents($url , 'get');
	return $result;
}

/**
 * get_fgj_userinfo_by_code
 *
 * 根据验证码获取（房管家）用户信息
 *
 * @access  public
 * @param   int $code   验证码参数
 * @return  array  客户信息
*/
function get_fgj_userinfo_by_code($code)
{
	$userinfo_fgj_arr = array();

	if($code <= 0)
	{
		return $userinfo_fgj_arr;
	}

	//根据验证码获取房管家客户数据
	$api_fgj = P_FGJ_API."method=getCustomerByCode&ver=v1&code=".$code;

	$userinfo_fgj_json = '';
	$userinfo_fgj_json = curl_get_contents($api_fgj , 'get');
	$userinfo_fgj_arr = json_decode($userinfo_fgj_json , TRUE);
	return $userinfo_fgj_arr;
}

/**
 * get_fgj_userinfo_by_pid_telno
 *
 * 根据项目ID和手机号码获取（房管家）用户信息
 *
 * @access  public
 * @param   int $project_id 项目ID
 * @param   string $telno   手机号码
 * @return  array  客户信息
*/
function get_fgj_userinfo_by_pid_telno($project_id , $telno)
{
	$userinfo_fgj_arr = array();
	$project_id = intval($project_id);
	$telno = strip_tags($telno);

	if($project_id <= 0 || strlen($telno) == 0)
	{
		return $userinfo_fgj_arr;
	}

	//根据项目ID和手机号码获取房管家客户数据
	$api_fgj = P_FGJ_API."method=getCpInfo&ver=v1&lp_id=".$project_id."&cm_phone=".$telno;

	$userinfo_fgj_json = '';
	$userinfo_fgj_json = curl_get_contents($api_fgj , 'get');
	$userinfo_fgj_arr = json_decode($userinfo_fgj_json , TRUE);

	return $userinfo_fgj_arr;
}

/**
 * get_crm_userinfo_by_code
 *
 * 根据验证码获取（CRM）用户信息
 *
 * @access  public
 * @param   int $code   验证码参数
 * @return  array  客户信息
*/
function get_crm_userinfo_by_code($code)
{
    $userinfo_crm_arr = array();

    if($code <= 0)
    {
        return $userinfo_crm_arr;
    }

    //根据验证码获取CRM用户数据
    $auth_key = md5(date('Ymd').'3653');
    $api_crm = P_CRM_API."rt_dcqr?crmkey=".$auth_key."&code=".$code;
    $userinfo_crm_json = '';
    $userinfo_crm_json = curl_get_contents($api_crm , 'get');
    $userinfo_crm_arr = json_decode($userinfo_crm_json , TRUE);

    return $userinfo_crm_arr;
}


/**
 * get_crm_userinfo_by_pid_telno
 *
 * 根据项目ID和手机号码获取（CRM）用户信息
 *
 * @access  public
 * @param   int $project_id 项目ID
 * @param   string $telno   手机号码
 * @return  array  客户信息
*/
function get_crm_userinfo_by_pid_telno($project_id , $telno ,$city_spell = 'nj')
{
    $userinfo_crm_arr = array();

    $project_id = intval($project_id);
    $telno = strip_tags($telno);

    if($project_id <= 0 || strlen($telno) == 0)
    {
        return $userinfo_crm_arr;
    }

    //根据验证码获取CRM用户数据
    $auth_key = md5(date('Ymd').'3653');
    $api_crm = P_CRM_API."rt_get_memdetail?projectid=".$project_id."&phone=".$telno."&city=".$city_spell;
    $userinfo_crm_json = '';
    $userinfo_crm_json = curl_get_contents($api_crm , 'get');
    $userinfo_crm_arr = json_decode($userinfo_crm_json , TRUE);

    return $userinfo_crm_arr;
}

/**
 * update_crm_user_source
 *
 * 更新CRM用户来源接口
 *
 * @access  public
 * @param   int $pmid CRM客户编号
 * @param   int $source 来源标示，默认5
 * @return  array  更新结果
*/
function update_crm_user_source($pmid, $source = 5)
{
    //根据验证码获取CRM用户数据
    $pmid = intval($pmid);
    $source = intval($source);

    $result = array();

    if($pmid > 0 && $source > 0 )
    {
        $auth_key = md5(date('Ymd').'3653');
        $api_crm = P_CRM_API."rt_usersource?crmkey=".$auth_key."&pmid=".$pmid."&source=".$source;

        $result_json = '';
        $result_json = curl_get_contents($api_crm , 'get');

        $result = json_decode( $result_json , TRUE);
    }

    return $result;
}

/*
 * get_user_agent_device
 * 判断用户客户端类型
 * @param string $return_type num返回数字，letter 返回字母
 * @return string 客户端类型
 */
function get_user_agent_device($return_type = 'letter')
{
	$userAgent = $_SERVER['HTTP_USER_AGENT'];
	$agent_type = '';

	$agent_type_conf = array(
		'ios' => array('letter' => 'ios', 'num' => '1'),
		'android' => array('letter' => 'android', 'num' => '2'),
		'wp' => array('letter' => 'wp', 'num' => '3'),
		'other' => array('letter' => 'other', 'num' => '4')
	);
	if (preg_match("/(iPod|iPad|iPhone)/", $userAgent))
	{
		$agent_type =  $agent_type_conf['ios'][$return_type]; //IOS客户端
	}
	else if (preg_match("/android/i", $userAgent))
	{
		$agent_type =  $agent_type_conf['android'][$return_type]; //android客户端
	}
	else if (preg_match("/WP/", $userAgent))
	{
		$agent_type =  $agent_type_conf['wp'][$return_type];//WinPhone客户端
	}
	else
	{
		$agent_type =  $agent_type_conf['other'][$return_type];
	}

	return $agent_type;
}
?>