<?php
include_once("../common/mysql.class.php");
include_once("../common/function.php");
include_once("../common/check.php");
/*
$sql = "SELECT * FROM ".$DB_PREFIX."new_user  ";
$row = $db->getAll($sql); 
foreach($row as $val){
	$one = $db->getOne("SELECT * FROM user2 where user_id='".$val['USER_ID']."'"); 
	$data['phone'] = $one['mobil_no'];
	$res = $db->update($DB_PREFIX.'new_user',$data,"USER_ID='".$val['USER_ID']."'"); 

}
*/
$msg = '���ã���������������12��7��ǰ�μ���֯��Χ�����ߵ��У���������OA�����е����ӻ�ֱ�ӽ���OA��֯��Χͨ�����𣬽���ʹ�û����IE9���ϵ�������������Ŷӽ��ϸ�֤���ε��е������Ժ�����׼ȷ�ԣ��������ʣ�����ϵ������ѧԺҶ���13505164496��';

if($_REQUEST['oanotice']){
	$sql = "SELECT * FROM ".$DB_PREFIX."new_user where oastatus<>1 and needsend=1";
	$row = $db->getAll($sql); 
	foreach($row as $key=> $val){
		$touid_arr[] = $val['USER_ID'];
		$data['oastatus'] = 1;
		$res = $db->update($DB_PREFIX.'new_user',$data,"USER_ID='".$val['USER_ID']."'"); 
		$k = floor($key/100);
		if($val['phone'])$phone[$k][] = $val['phone'];
	}
	$touids = implode(',',$touid_arr);
	$sql = "SELECT * FROM ".$DB_PREFIX."new_admin_set where id=1";
		$set = $db->getOne($sql); 
		$startime = $set['startime'];
		$endtime = $set['endtime'];
		$sendtime = date("Y��m��d�� H:i:s");
		$subject = "��μ�������������֯��Χ���ߵ���";
		$urll = "http://zt.house365.com/njzt/2016/11/21/zzfw/mark/index.php?authcode=";
	 $content = '
${name} :<br>
���ã�<br>
���������������μ���֯��Χ�����ߵ��С�<br> 
 ����������µ�ַ�������ƽ̨����֧��PC�ˣ��ݲ�֧���ֻ����𣩣� <a href="'.$urll.'${authcode}" target="_blank"> '.$urll.'${authcode}</a><br> 
 
 ��������ר�����е�ַ����������Ϣ�󶨣�����ת�������ˡ������Ŷӳ�ŵ�����ϸ�֤���ε���������Ժ�����׼ȷ�ԡ�<br> 
 ����޷�����������ӣ��������֯��ΧOAͨ��ֱ������<br> 
 ������ʹ�û���������IE9���ϵ��������������<br> 
 ���β���������<font color="#ff0000"> ${begindate}</font> ��Ч���� <font color="#ff0000"> ${enddate}</font> ʧЧ����ץ��ʱ����ɵ��С�<br> 
 �������� <br> 
${senddate} <br>
 
<br> <br> <br> <br> 
 ����(Declaration):<br> 
 ���ʼ����б�����Ϣ���������ռ������á���ֹ�κ���δ��������������κ���ʽ�������������ڲ��ֵ�й¶�����ƻ�ɢ����������ʹ�ñ��ʼ��е���Ϣ������������˱��ʼ������������绰���ʼ�֪ͨ�����˲�ɾ�����ʼ���лл��<br> 
 This email contains confidential information, which is intended only for the receiver. Any use of the information contained herein in any way (including, but not limited to, total or partial disclosure, reproduction, or dissemination) by persons other than the intended recipient(s) is prohibited. If you receive this email in error, please notify the sender by phone or email immediately and delete it. Thanks!
';
	$param=array(
				'name'=>'auto',
				'authcode'=>'auto',
				'begindate'=>$startime,
				'enddate'=>$endtime,
				'senddate'=>$sendtime
			);
	$param = serialize($param);
	//oa_notice2('huanghonghe', 'huanghonghe', $subject, $content,null,$param);
	oa_notice2($touids, 'yelinjing', $subject, $content,null,$param);
	 
	//$phone1[0][] = '13852294251';
	//$phone1[0][] = '15195997341';
	send_sms_arr($msg,$phone,'nj');
}elseif($_REQUEST['oanotice2']){
	$sql = "SELECT * FROM ".$DB_PREFIX."new_user a left join ".$DB_PREFIX."new_user_result b  on  a.USER_ID=b.uid  where  a.needsend=1  and b.uid is null ";
	$row = $db->getAll($sql); 
	foreach($row as $key=>$val){
		$touid_arr[] = $val['USER_ID'];
		//$data['oastatus'] = 1;
		//$res = $db->update($DB_PREFIX.'new_user',$data,"USER_ID='".$val['USER_ID']."'"); 
		//$phone[$val['']]
		$k = floor($key/100);
		if($val['phone']) $phone[$k][] = $val['phone'];
	}
	$touids = implode(',',$touid_arr);
	$sql = "SELECT * FROM ".$DB_PREFIX."new_admin_set where id=1";
		$set = $db->getOne($sql); 
		$startime = $set['startime'];
		$endtime = $set['endtime'];
		$sendtime = date("Y��m��d�� H:i:s");
		$subject = "��μ�������������֯��Χ���ߵ���";
		$urll = "http://zt.house365.com/njzt/2016/11/21/zzfw/mark/index.php?authcode=";
	 $content = '
${name} :<br>
���ã�<br>
���������������μ���֯��Χ���ߵ��е�ʱ���ѽӽ�β����<br> 
 ����������µ�ַ�������ƽ̨����֧��PC�ˣ��ݲ�֧���ֻ����𣩣� <a href="'.$urll.'${authcode}" target="_blank"> '.$urll.'${authcode}</a><br> 
 
 ��������ר�����е�ַ����������Ϣ�󶨣�����ת�������ˡ������Ŷӳ�ŵ�����ϸ�֤���ε���������Ժ�����׼ȷ�ԡ�<br> 
 ����޷�����������ӣ��������֯��ΧOAͨ��ֱ������<br> 
 ������ʹ�û���������IE9���ϵ��������������<br> 
 ���β���������<font color="#ff0000"> ${begindate}</font> ��Ч���� <font color="#ff0000"> ${enddate}</font> ʧЧ����ץ��ʱ����ɵ��С�<br> 
 �������� <br> 
${senddate} <br>
 
<br> <br> <br> <br> 
 ����(Declaration):<br> 
 ���ʼ����б�����Ϣ���������ռ������á���ֹ�κ���δ��������������κ���ʽ�������������ڲ��ֵ�й¶�����ƻ�ɢ����������ʹ�ñ��ʼ��е���Ϣ������������˱��ʼ������������绰���ʼ�֪ͨ�����˲�ɾ�����ʼ���лл��<br> 
 This email contains confidential information, which is intended only for the receiver. Any use of the information contained herein in any way (including, but not limited to, total or partial disclosure, reproduction, or dissemination) by persons other than the intended recipient(s) is prohibited. If you receive this email in error, please notify the sender by phone or email immediately and delete it. Thanks!
';
	$param=array(
				'name'=>'auto',
				'authcode'=>'auto',
				'begindate'=>$startime,
				'enddate'=>$endtime,
				'senddate'=>$sendtime
			);
	$param = serialize($param);
	//oa_notice2('huanghonghe', 'huanghonghe', $subject, $content,null,$param);
	//$phone1[0][] = '13852294251';
	//$phone1[0][] = '15195997341';
	//$touids = 'huanghonghe,huketing';
	//var_dump($phone); var_dump($touids);
	//echo 'sss';
	send_sms_arr($msg,$phone,'nj');
	oa_notice2($touids, 'yelinjing', $subject, $content,null,$param);
}
if($_REQUEST['sb']){
	$data['startime'] = $_REQUEST['startime'];
	$data['endtime'] = $_REQUEST['endtime'];
	$res = $db->update($DB_PREFIX.'new_admin_set',$data,"ID=1"); 
	if($res){
		Jalert('����ɹ�!','set.php');
	}else{
		Jalert('ʧ��','set.php');
	}

}
$sql = "SELECT * FROM ".$DB_PREFIX."new_admin_set where id=1";
$set = $db->getOne($sql); 
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>��վ��Ϣ</title>  
    <link rel="stylesheet" href="../css/pintuer.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="../js/jquery.js"></script>
    <script src="../js/pintuer.js"></script>  
	<script language="javascript" type="text/javascript" src="../js/My97DatePicker/WdatePicker.js"></script>
</head>
<body>
<div class="panel admin-panel">
  <div class="panel-head"><strong><span class="icon-pencil-square-o"></span> �ʼ�����</strong></div>
  <div class="body-content">
    <form method="post" class="form-x" action="">
      <div class="form-group">
        <div class="label">
          <label>��Чʱ�䣺</label>
        </div>
        <div class="field">
          <input type="text" class="input" name="startime" value="<?php echo $set['startime'];?>"  onFocus="WdatePicker({lang:'zh-cn',dateFmt:'yyyy��MM��dd�� HH:mm:ss'})"  style="width:200px;" maxlength="50" size="50"/>
          <div class="tips"></div>
        </div>
      </div>
      <div class="form-group">
        <div class="label">
          <label>��ֹʱ�䣺</label>
        </div>
        <div class="field">
          <input type="text" class="input" name="endtime" value="<?php echo $set['endtime'];?>" onFocus="WdatePicker({lang:'zh-cn',dateFmt:'yyyy��MM��dd�� HH:mm:ss'})"  style="width:200px;" maxlength="30" />
        </div>
      </div>
       
      <div class="form-group">
        <div class="label">
          <label></label>
        </div>
        <div class="field">
          <button class="button bg-main icon-check-square-o" name='sb' value='1' type="submit"> ��������</button>
		 <button class="button bg-main icon-check-square-o" name='oanotice' value='1' type="submit" onclick="return confirm('����ʼ���������ʼ���')"> ���������ʼ�</button>
		  <button class="button bg-main icon-check-square-o" name='oanotice2' value='1' type="submit" onclick="return confirm('����ʼ���������ʼ���')"> ���������ʼ�</button>
          
        </div>
      </div>
    </form>
  </div>
</div>
<script>
 

</script>
</body></html>