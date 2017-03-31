<?php
/**
 * submit_crm_data_by_api
 *
 * �ύCRM�ӿ�����
 *
 * @access  public
 * @param   array   $api_arr �ӿ���Ҫ�ύ������
 * @param   string  $sub_type �ӿ��ύ��ʽ,POST/GET
 * @return  boolean �Ƿ��ύ�ɹ�
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
 * ������֤���ȡ��CRM�ͷ��ܼҺϲ���ģ��û���Ϣ
 *
 * @access  public
 * @param   int $code   ��֤�����
 * @param   int $prolist_id   ¥��ID
 * @return  array  �ͻ���Ϣ
*/
function get_userinfo_by_code($code, $prolist_id = 0)
{
	/***������֤��ͳ��в�����ȡCRM�û�����***/
	$userinfo_arr = array('result' => 0 , 'is_from' => 0);

	$code = intval($code);

	//����ͨ����֤���ȡCRM�û���Ϣ
	$userinfo_crm_arr = get_crm_userinfo_by_code($code);
    //var_dump($userinfo_crm_arr);die;
	if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1)
	{
		$userinfo_arr['result'] = 1;
		//������Դ
		$userinfo_arr['is_from'] = 1;
		//CRM���û�ID
		$userinfo_arr['customer_id'] = $userinfo_crm_arr['meminfo']['pmid'];
		//¥��id
		$userinfo_arr['projectid']
				= $userinfo_crm_arr['meminfo']['projectid'];
		//�ͻ�����
		$userinfo_arr['truename']
				= $userinfo_crm_arr['meminfo']['memname'];
		//�ͻ��ֻ�����
		$userinfo_arr['telno'] = $userinfo_crm_arr['meminfo']['memphone'];
	}
	else
	{
		//���CRMû�����ݣ���ͨ�����ܼ�ϵͳ��ѯ�Ƿ�����û���Ϣ
		$userinfo_fgj_arr = get_fgj_userinfo_by_code($code);

		if(is_array($userinfo_fgj_arr) && $userinfo_fgj_arr['result'] == 1)
		{
			$userinfo_arr['result'] = 1;
			//������Դ
			$userinfo_arr['is_from'] = 2;

			if($prolist_id > 0)
			{
				foreach($userinfo_fgj_arr['data'] as $key => $value)
				{
					if($prolist_id == $value['lp_id'])
					{
						//¥��ID
						$userinfo_arr['projectid'] = $value['lp_id'];
						//�ͻ�����
						$userinfo_arr['truename'] = $value['cm_name'];
						//�ͻ��绰
						$userinfo_arr['telno'] = $value['cm_phone'];
						//�ͻ�id
						$userinfo_arr['customer_id'] = intval($value['cm_id']);
						//������id
						$userinfo_arr['ag_id'] = $value['ag_id'];
						//����id
						$userinfo_arr['cp_id'] = $value['cp_id'];
						break;
					}
				}
			}
			else
			{
				//¥��ID
				$userinfo_arr['projectid'] = $userinfo_fgj_arr['data'][0]['lp_id'];
				//�ͻ�����
				$userinfo_arr['truename'] = $userinfo_fgj_arr['data'][0]['cm_name'];
				//�ͻ��绰
				$userinfo_arr['telno'] = $userinfo_fgj_arr['data'][0]['cm_phone'];
				//�ͻ�id
				$userinfo_arr['customer_id'] = intval($userinfo_fgj_arr['data'][0]['cm_id']);
				//������id
				$userinfo_arr['ag_id'] = $userinfo_fgj_arr['data'][0]['ag_id'];
				//����id
				$userinfo_arr['cp_id'] = $userinfo_fgj_arr['data'][0]['cp_id'];
			}
		}
	}

	return $userinfo_arr;
}

/**
 * get_userinfo_by_pid_telno
 *
 * ������ĿID���ֻ������ȡ��CRM�ͷ��ܼҺϲ���ģ��û���Ϣ
 *
 * @access  public
 * @param   int $project_id ��ĿID
 * @param   string $telno   �ֻ�����
 * @param   int $pro_listid ¥��ID
 * @return  array  �ͻ���Ϣ
*/
function get_userinfo_by_pid_telno($project_id, $telno, $pro_listid, $city_spell = 'nj')
{
	/***������֤��ͳ��в�����ȡCRM�û�����***/
	$userinfo_arr = array('result' => 0);
    
    $city_spell = strtolower($city_spell);
	//ͨ����֤���ȡCRM�û���Ϣ
	$userinfo_crm_arr = get_crm_userinfo_by_pid_telno($project_id, $telno, $city_spell);
	if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 &&
			$userinfo_crm_arr['meminfo']['codestatus'] != null)
	{
		$userinfo_arr['result'] = 1;
		$userinfo_arr['is_from_crm'] = 1;//������Դ
		$userinfo_arr['user_num_crm'] = 1;

		//CRM���û�ID
		$userinfo_arr['crm_user']['customer_id'] = $userinfo_crm_arr['meminfo']['pmid'];
		//�ͻ�����
		$userinfo_arr['crm_user']['truename'] = $userinfo_crm_arr['meminfo']['memname'];
		//�ͻ���֤��
		$userinfo_arr['crm_user']['code'] = $userinfo_crm_arr['meminfo']['code'];
		//��֤��״̬[0δ���� 1�ѵ��� 2�ѹ���/δ����]
		$userinfo_arr['crm_user']['confirm_status'] = $userinfo_crm_arr['meminfo']['codestatus'];

		//�û���Դ
		$userinfo_arr['crm_user']['usersource'] = $userinfo_crm_arr['meminfo']['usersource'];
	}

	//ͨ�����ܼ�ϵͳ��ѯ�Ƿ�����û���Ϣ
	$user_num = 0;
	$userinfo_fgj_arr = get_fgj_userinfo_by_pid_telno($pro_listid , $telno);
	if(is_array($userinfo_fgj_arr) && $userinfo_fgj_arr['result'] == 1 &&
			!empty($userinfo_fgj_arr['data']))
	{
		$is_need_confirm = 1;
		foreach ($userinfo_fgj_arr['data'] as $key => $value)
		{
			//0��ʾδ�������ڣ�1��ʾ�Ѿ����˱�����
			if($value['overProtection'] == 0 &&
					($value['status'] == 0 || $value['status'] == 1))
			{
				//�ͻ�id
				$userinfo_arr['fgj_user'][$user_num]['customer_id'] = intval($value['cm_id']);
				//�ͻ�����
				$userinfo_arr['fgj_user'][$user_num]['truename'] = $value['cm_name'];
				//�ͻ���֤��
				$userinfo_arr['fgj_user'][$user_num]['code'] = $value['code'];
				//�û���Դ
				$userinfo_arr['fgj_user'][$user_num]['usersource'] = 1;
				//����ȷ��״̬ 1δ������0�����Ѵ�����2�������ʧ�ܣ�3��������
				$userinfo_arr['fgj_user'][$user_num]['confirm_status'] = $value['status'];
				//���������д����ѵ���ȷ����Ϣ������Ҫ�ٴ�ȷ��
				if( $value['status'] == 0)
				{
					$is_need_confirm = 0;
				}

				//�Ƿ��Ѿ����˱�����
				$userinfo_arr['fgj_user'][$user_num]['overProtection'] = $value['overProtection'];
				//������id
				$userinfo_arr['fgj_user'][$user_num]['ag_id'] = $value['ag_id'];
				//����id
				$userinfo_arr['fgj_user'][$user_num]['cp_id'] = $value['cp_id'];
				++ $user_num;
			}
		}

		if($user_num > 0 )
		{
			$userinfo_arr['result'] = 2;
			$userinfo_arr['is_from_fgj'] = 2;//������Դ
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
 * �����û��ֻ����� ���������ֻ������ȡ�û���Ϣ��
 * ����û�û����д�������ֻ����룬ͨ��CRMϵͳ��ѯ��
 * �������д�������ֻ�����ͨ��FGJϵͳ���в�ѯ
 *
 * @access  public
 * @param   int $project_id ��ĿID
 * @param   string $customer_telno   �ͻ��ֻ�����
 * @param   string $agent_telno   �������ֻ�����
 * @return  array  �ͻ���Ϣ
*/
function get_userinfo_by_telno($project_id, $customer_telno, $agent_telno = '')
{
	$userinfo_arr = array();
	$project_id = intval($project_id);
	$customer_telno = strip_tags($customer_telno);
	$agent_telno = strip_tags($agent_telno);

	//�û�ֻ��д���û��ֻ����룬δ��д�������ֻ����룬��CRMϵͳ��ѯ��Ϣ
   if(strlen($customer_telno) == 11 && strlen($agent_telno) == 0)
   {
		$api_crm = P_CRM_API."rt_get_capcode?projectid=".$project_id."&phone=".$customer_telno;
		$userinfo_crm_json = '';
		$userinfo_crm_json = curl_get_contents($api_crm , 'get');
		$userinfo_crm_arr = json_decode($userinfo_crm_json , TRUE);
		$userinfo_arr['result'] = 0;
		$userinfo_arr["is_from"] = 1;//��ʾ��Ϣ��Դ��CRMϵͳ
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
		$userinfo_arr["is_from"] = 2;//��ʾ��Ϣ��Դ��FGJϵͳ
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
 * ���ÿͻ�״̬����״̬(CRMϵͳ)
 *
 * @access  public
 * @param   int $pmid CRM�ͻ����
 * @param   int $code ��֤��
 * @return  array  ��֤���
*/
function arrival_confirm_crm( $pmid , $code )
{
	//������֤���ȡCRM�û�����
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
 * ���ÿͻ�״̬����״̬�����ܼ�ϵͳ��
 *
 * @access  public
 * @param   int $cm_id �ͻ�ID
 * @param   int $ag_id ������ID
 * @param   int $cp_id ����ID
 * @return  array  ��֤���
*/
function arrival_confirm_fgj( $cm_id , $ag_id , $cp_id)
{
	//������֤���ȡCRM�û�����
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
 * CRM ע����Ȼ����
 *
 * @access  public
 * @param   sting $city ����ƴ����д
 * @param   sting $username �ͻ�����
 * @param   sting $mobile �ͻ��ֻ�����
 * @param   int $project_id ͨ����Ŀ���
 * @param   sting $project_name ��Ŀ����
 * @return  array  ע����
*/
function register_natural_customer($city , $username , $mobile , $project_id , $project_name)
{
	$url = P_CRM.'index.php/Simulate/sea?';
	$url .= "mobile=$mobile";
	$url .= "&username=".urlencode(u2g($username));
	$url .= "&activefrom=231"; //���̿ͻ�-��Ȼ����
	$url .= "&city=$city";
	$url .= "&activename=".urlencode(u2g($project_name.'��Ȼ����'));
	$url .= "&loupanids=$project_id";
	$result = curl_get_contents($url , 'get');
	return $result;
}

/**
 * get_fgj_userinfo_by_code
 *
 * ������֤���ȡ�����ܼң��û���Ϣ
 *
 * @access  public
 * @param   int $code   ��֤�����
 * @return  array  �ͻ���Ϣ
*/
function get_fgj_userinfo_by_code($code)
{
	$userinfo_fgj_arr = array();

	if($code <= 0)
	{
		return $userinfo_fgj_arr;
	}

	//������֤���ȡ���ܼҿͻ�����
	$api_fgj = P_FGJ_API."method=getCustomerByCode&ver=v1&code=".$code;

	$userinfo_fgj_json = '';
	$userinfo_fgj_json = curl_get_contents($api_fgj , 'get');
	$userinfo_fgj_arr = json_decode($userinfo_fgj_json , TRUE);
	return $userinfo_fgj_arr;
}

/**
 * get_fgj_userinfo_by_pid_telno
 *
 * ������ĿID���ֻ������ȡ�����ܼң��û���Ϣ
 *
 * @access  public
 * @param   int $project_id ��ĿID
 * @param   string $telno   �ֻ�����
 * @return  array  �ͻ���Ϣ
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

	//������ĿID���ֻ������ȡ���ܼҿͻ�����
	$api_fgj = P_FGJ_API."method=getCpInfo&ver=v1&lp_id=".$project_id."&cm_phone=".$telno;

	$userinfo_fgj_json = '';
	$userinfo_fgj_json = curl_get_contents($api_fgj , 'get');
	$userinfo_fgj_arr = json_decode($userinfo_fgj_json , TRUE);

	return $userinfo_fgj_arr;
}

/**
 * get_crm_userinfo_by_code
 *
 * ������֤���ȡ��CRM���û���Ϣ
 *
 * @access  public
 * @param   int $code   ��֤�����
 * @return  array  �ͻ���Ϣ
*/
function get_crm_userinfo_by_code($code)
{
    $userinfo_crm_arr = array();

    if($code <= 0)
    {
        return $userinfo_crm_arr;
    }

    //������֤���ȡCRM�û�����
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
 * ������ĿID���ֻ������ȡ��CRM���û���Ϣ
 *
 * @access  public
 * @param   int $project_id ��ĿID
 * @param   string $telno   �ֻ�����
 * @return  array  �ͻ���Ϣ
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

    //������֤���ȡCRM�û�����
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
 * ����CRM�û���Դ�ӿ�
 *
 * @access  public
 * @param   int $pmid CRM�ͻ����
 * @param   int $source ��Դ��ʾ��Ĭ��5
 * @return  array  ���½��
*/
function update_crm_user_source($pmid, $source = 5)
{
    //������֤���ȡCRM�û�����
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
 * �ж��û��ͻ�������
 * @param string $return_type num�������֣�letter ������ĸ
 * @return string �ͻ�������
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
		$agent_type =  $agent_type_conf['ios'][$return_type]; //IOS�ͻ���
	}
	else if (preg_match("/android/i", $userAgent))
	{
		$agent_type =  $agent_type_conf['android'][$return_type]; //android�ͻ���
	}
	else if (preg_match("/WP/", $userAgent))
	{
		$agent_type =  $agent_type_conf['wp'][$return_type];//WinPhone�ͻ���
	}
	else
	{
		$agent_type =  $agent_type_conf['other'][$return_type];
	}

	return $agent_type;
}
?>