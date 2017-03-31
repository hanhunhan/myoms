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
$msg = '您好！三六五网邀您于12月7日前参加组织氛围的在线调研，请您进入OA邮箱中的链接或直接进入OA组织氛围通栏作答，建议使用火狐或IE9以上的浏览器。调研团队将严格保证本次调研的匿名性和数据准确性，如有疑问，请联系三六五学院叶琳婧13505164496。';

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
		$sendtime = date("Y年m月d日 H:i:s");
		$subject = "请参加三六五网的组织氛围在线调研";
		$urll = "http://zt.house365.com/njzt/2016/11/21/zzfw/mark/index.php?authcode=";
	 $content = '
${name} :<br>
您好！<br>
三六五网邀请您参加组织氛围的在线调研。<br> 
 请您点击以下地址进入调研平台（仅支持PC端，暂不支持手机作答）： <a href="'.$urll.'${authcode}" target="_blank"> '.$urll.'${authcode}</a><br> 
 
 这是您的专属调研地址，与您的信息绑定，请勿转发给他人。调研团队承诺，将严格保证本次调查的匿名性和数据准确性。<br> 
 如果无法打开上面的链接，请进入组织氛围OA通栏直接作答。<br> 
 建议您使用火狐浏览器或IE9以上的浏览器进行作答。<br> 
 本次测试邀请于<font color="#ff0000"> ${begindate}</font> 生效，于 <font color="#ff0000"> ${enddate}</font> 失效。请抓紧时间完成调研。<br> 
 三六五网 <br> 
${senddate} <br>
 
<br> <br> <br> <br> 
 声明(Declaration):<br> 
 本邮件含有保密信息，仅限于收件人所用。禁止任何人未经发件人许可以任何形式（包括但不限于部分地泄露、复制或散发）不当地使用本邮件中的信息。如果您错收了本邮件，请您立即电话或邮件通知发件人并删除本邮件，谢谢！<br> 
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
		$sendtime = date("Y年m月d日 H:i:s");
		$subject = "请参加三六五网的组织氛围在线调研";
		$urll = "http://zt.house365.com/njzt/2016/11/21/zzfw/mark/index.php?authcode=";
	 $content = '
${name} :<br>
您好！<br>
三六五网邀请您参加组织氛围在线调研的时间已接近尾声。<br> 
 请您点击以下地址进入调研平台（仅支持PC端，暂不支持手机作答）： <a href="'.$urll.'${authcode}" target="_blank"> '.$urll.'${authcode}</a><br> 
 
 这是您的专属调研地址，与您的信息绑定，请勿转发给他人。调研团队承诺，将严格保证本次调查的匿名性和数据准确性。<br> 
 如果无法打开上面的链接，请进入组织氛围OA通栏直接作答。<br> 
 建议您使用火狐浏览器或IE9以上的浏览器进行作答。<br> 
 本次测试邀请于<font color="#ff0000"> ${begindate}</font> 生效，于 <font color="#ff0000"> ${enddate}</font> 失效。请抓紧时间完成调研。<br> 
 三六五网 <br> 
${senddate} <br>
 
<br> <br> <br> <br> 
 声明(Declaration):<br> 
 本邮件含有保密信息，仅限于收件人所用。禁止任何人未经发件人许可以任何形式（包括但不限于部分地泄露、复制或散发）不当地使用本邮件中的信息。如果您错收了本邮件，请您立即电话或邮件通知发件人并删除本邮件，谢谢！<br> 
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
		Jalert('保存成功!','set.php');
	}else{
		Jalert('失败','set.php');
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
    <title>网站信息</title>  
    <link rel="stylesheet" href="../css/pintuer.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="../js/jquery.js"></script>
    <script src="../js/pintuer.js"></script>  
	<script language="javascript" type="text/javascript" src="../js/My97DatePicker/WdatePicker.js"></script>
</head>
<body>
<div class="panel admin-panel">
  <div class="panel-head"><strong><span class="icon-pencil-square-o"></span> 邮件设置</strong></div>
  <div class="body-content">
    <form method="post" class="form-x" action="">
      <div class="form-group">
        <div class="label">
          <label>生效时间：</label>
        </div>
        <div class="field">
          <input type="text" class="input" name="startime" value="<?php echo $set['startime'];?>"  onFocus="WdatePicker({lang:'zh-cn',dateFmt:'yyyy年MM月dd日 HH:mm:ss'})"  style="width:200px;" maxlength="50" size="50"/>
          <div class="tips"></div>
        </div>
      </div>
      <div class="form-group">
        <div class="label">
          <label>截止时间：</label>
        </div>
        <div class="field">
          <input type="text" class="input" name="endtime" value="<?php echo $set['endtime'];?>" onFocus="WdatePicker({lang:'zh-cn',dateFmt:'yyyy年MM月dd日 HH:mm:ss'})"  style="width:200px;" maxlength="30" />
        </div>
      </div>
       
      <div class="form-group">
        <div class="label">
          <label></label>
        </div>
        <div class="field">
          <button class="button bg-main icon-check-square-o" name='sb' value='1' type="submit"> 保存设置</button>
		 <button class="button bg-main icon-check-square-o" name='oanotice' value='1' type="submit" onclick="return confirm('您开始发送邀请邮件吗？')"> 发送邀请邮件</button>
		  <button class="button bg-main icon-check-square-o" name='oanotice2' value='1' type="submit" onclick="return confirm('您开始发送提醒邮件吗？')"> 发送提醒邮件</button>
          
        </div>
      </div>
    </form>
  </div>
</div>
<script>
 

</script>
</body></html>