<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<title>��Ŀ����</title>
    <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="./Tpl/css/jquery.treeview.css" />
	<link rel="stylesheet" href="./Tpl/css/screen.css" />
	<link rel="stylesheet" href="./Tpl/css/boxy.css" />
	<link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>

	<script type="text/javascript" src="./Tpl/js/jquery-1.5.2.min.js"></script>
	<script src="./Tpl/js/jquery.cookie.js" type="text/javascript"></script>
	<script src="./Tpl/js/jquery.treeview.js" type="text/javascript"></script>
	<script src="./Tpl/js/jquery.boxy.js" type="text/javascript"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		$(function() {
			$("#tree").treeview({
				collapsed: true,
				animated: "fast",
				control:"#sidetreecontrol",
				prerendered: true,
				persist: "location"
			});
		})
		
	</script>

	<style>
		.tabl {font-size:13px;margin:5px;border-collapse:collapse;clear:both}
		.tabl th{height:30px;line-height:30px;}
		.tabl td{height:30px;line-height:30px;text-align:left;padding-left:10px;}
		.tabl td input{border:solid 1px #ccc;height:22px;line-height:22px;}
		.tabl td select{border:solid 1px #ccc}
		.tabl td .btn{width:50px;height:25px;cursor:pointer}	
	</style>
</head>
<body>
<div class="right fright j-right">
	<div class="handle-tab">
		<ul>
			<li class="selected"><a href="<?php echo U('Group/menu');?>">�˵�����</a></li>
		</ul>
	</div>
	<div id="main" style="padding:0 10px;">
		<iframe name="pxiframeTop" id="pxiframeTop" width="100%" style="display:none"></iframe>
		<iframe name="pxiframe" id="pxiframe" width="100%" style="display:none"></iframe>
		<iframe name="pxiframeParam" id="pxiframeParam" width="100%" style="display:none"></iframe>
		<div id="sidetree">
		  <div class="treeheader">&nbsp;</div>
		  <div id="sidetreecontrol" > <a href="?#">-�رղ˵�</a> | <a href="?#">+չ���˵�</a> </div>

		  <ul class="treeview" id="tree" >
				<?php if(is_array($menu)){ foreach($menu as $mval){ echo '<li class="expandable"><div class="hitarea expandable-hitarea"></div>'.$mval['fmenu']['LOAN_ROLENAME'].'-----<a target="pxiframeTop" href="'.U('Group/menu',array('mod'=>'edit','roleid'=>$mval['fmenu']['LOAN_ROLEID'])).'">�޸�</a>-----<a target ="pxiframeTop" href="'.U('Group/menu',array('mod'=>'del','roleid'=>$mval['fmenu']['LOAN_ROLEID'])).'">ɾ��</a>'; echo '<ul style="display: none;">'; if(is_array($mval['smenu'])){ foreach($mval['smenu'] as $sval){ echo '<li class="expandable"><div class="hitarea expandable-hitarea"></div>'.$sval['LOAN_ROLENAME'].'-----<a target="pxiframeTop" href="'.U('Group/menu',array('mod'=>'edit','roleid'=>$sval['LOAN_ROLEID'])).'">�޸�</a>-----<a target ="pxiframeTop" href="'.U('Group/menu',array('mod'=>'del','roleid'=>$sval['LOAN_ROLEID'])).'">ɾ��</a>'; echo '<ul style="display: none;">'; if(is_array($sval['loan_pulate'])){ foreach($sval['loan_pulate'] as $pval){ echo '<li class="expandable"><div class="hitarea expandable-hitarea"></div>'.$pval['LOAN_ROLENAME'].'-----<a target="pxiframe" href="'.U('Group/menu',array('mod'=>'edit','roleid'=>$pval['LOAN_ROLEID'],'tact'=>'oper')).'">�޸�</a>-----<a target ="pxiframe" href="'.U('Group/menu',array('mod'=>'del','roleid'=>$pval['LOAN_ROLEID'])).'">ɾ��</a>'; echo '</li>'; } } echo '<li class="last"><a target="pxiframe" href="'.U('Group/menu',array('mod'=>'add','roleid'=>$sval['LOAN_ROLEID'],'tact'=>'oper')).'">���Ӳ���</a></li>'; echo '</ul>'; echo '</li>'; } } echo '<li class="last"><a target="pxiframeTop" href="'.U('Group/menu',array('mod'=>'add','roleid'=>$mval['fmenu']['LOAN_ROLEID'])).'">���Ӷ����˵�</a></li>'; echo '</ul>'; echo '</li>'; } echo '<li class="last"><a target="pxiframeTop" href="'.U('Group/menu',array('mod'=>'add')).'">��������Ŀ</a></li>'; } ?>
		  </ul>
		</div>
	</div>
	<!--������-->
	<form onsubmit="return check()"  method="post" target="pxiframe" action="<?php echo U('Group/menu');?>" id="showmodel" style='display:none;overflow:auto;zoom:1;margin:0px;padding:8px;'>
	<div>
		<table class="tabl">
			<tr class="a"><td>���ƣ�</td><td><input type="text" name="rolename" id="rolename"></td>
			<tr class="a"><td>ģ�飺</td><td><input type="text" name="rolemodule" id="rolemodule"></td>
			<tr class="a"><td>������</td><td><input type="text" name="roleaction" id="roleaction"></td>
			 <tr class="p"><td>������</td><td><input type="text" name="roleparam" id="roleparam"></td>
			<tr class="m"><td>�˵���ʾ��</td><td>��ʾ<input type="radio" name="roledisplay"   value="-1" checked="checked" /> ����<input type="radio" name="roledisplay" value="0"   /></td>
			<tr class="a"><td>����</td><td><input type="text" name="rolesort" id="rolesort"></td>
			<tr ><td>���룺</td><td><input type="password" name="rolepass" id="rolepass"></td>
			<tr ><td colspan="2" style="text-align:center"><input type="submit" id="sbt" value="ȷ��" style="width:60px;height:30px;cursor:pointer"></td>
		</table>
		<span id="info"></span>
		<input type="hidden" name="act" id="act" value="">
		<input type="hidden" name="roleid" id="roleid" value="">
	</div>
	</form>

	 

</body>
<script>

	function check(){
		var act = $('#act1').val();

		if(act=='edit' || act=='add'){
			var rolename = $('#rolename').val();
			rolename = rolename.replace(/^\s+|\s+$/,'');
			if(rolename=='') {
				$('#info1').html('<font color="red">���Ʋ���Ϊ�գ�</font>');return false;
			}else
				$('#info1').html('');
			
			var rolemodule = $('#rolemodule').val();
			rolemodule = rolemodule.replace(/^\s+|\s+$/,'');
			if(rolemodule=='') {
				$('#info1').html('<font color="red">ģ�鲻��Ϊ�գ�</font>');return false;
			}else
				$('#info1').html('');

			var roleaction = $('#roleaction').val();
			roleaction = roleaction.replace(/^\s+|\s+$/,'');
			if(roleaction=='') {
				$('#info1').html('<font color="red">��������Ϊ�գ�</font>');return false;
			}else
				$('#info1').html('');
		}		
	}
	 
	function showp(){
		$('.p').show();
	}
	function hidep(){
		$('.p').hide();
	}
	function showm(){
		$('.m').show();
	}
	function hidem(){
		$('.m').hide();
	}
	var box = '';
	 
	function showboxy(o){
		 if(typeof(box)=='object'){
			box.hide();	
		 }

		 var width = 0;
		 var height = 0;
		 if(o=='a'){
			$('.a').show();
			width = 240;
			height= 200;
		 }else{
			$('.a').hide();
			width = 240;
			height= 100;
		 }
		
		 box = new Boxy($("#showmodel"), {
		  modal: true,
		  title:"��Ŀ����",
					closeText:"�ر�" 
		  });
		 
		 	  
	}
</script>
</html>