<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>�ɹ���ͬ</title>
        <meta charset="GBK">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="Public/third/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="Public/css/style2.css?time=20160815" type="text/css" rel="stylesheet"/>
<link href="Public/css/jquery-ui.css" type="text/css" rel="stylesheet"/>
<link rel="stylesheet" href="./Tpl/css/jquery.treeview.css" />
<link rel="stylesheet" href="./Tpl/css/screen.css" />
<link rel="stylesheet" href="./Tpl/css/boxy.css" />
<link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all" />
<link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all" />
<!--select 2 style-->
<link rel="stylesheet" href="./Public/select2/select2.css" type="text/css" media="all"/>

<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>
<script type="text/javascript" src="./Public/validform/js/common.js?time=20160815"></script>


<script type="text/javascript" src="./Public/js/common.js?time=20160815"></script>
<script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>
<script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>
<script src="Public/third/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="./Public/js/jquery-ui.js"></script>

<script src="./Tpl/js/jquery.cookie.js" type="text/javascript"></script>
<script src="./Tpl/js/jquery.treeview.js" type="text/javascript"></script>
<script src="./Tpl/js/jquery.boxy.js" type="text/javascript"></script>

<script type="text/javascript" src="Public/js/jquery.nicescroll.min.js"></script>

<!--������-->
<script language="javascript" type="text/javascript" src="./Public/layer/layer.js"></script>
<script language="javascript" type="text/javascript" src="./Public/layer/extend/layer.ext.js"></script>
<!--select2 js-->
<script type="text/javascript" src="./Public/select2/select2.js"></script>
<script type="text/javascript" src="./Public/js/template.js"></script>

<script>
    $(function() {
//        $('html').niceScroll();
        // ��ȡ�ϴε���������
        var lastFilterResult = '<?php echo ($lastFilter); ?>';
        $('#last_filter_result').text(lastFilterResult);
    });
</script>

		<style>
			.ui-autocomplete {
				max-height: 200px;
				overflow-y: auto;
				/* ��ֹˮƽ������ */
				overflow-x: hidden;
			}
		</style>
   
</head>
<body>
         
<div class="containter">     
  <div class="right fright j-right">	 
      <div class="handle-tab  ">
      <?php if($layer != 1): echo ($tabs); endif; ?>
      </div>
          <?php echo ($form); ?>
    </div>   
</div>
<script>
	
	$(function(){
        // ����δǩԼ����Ч�Ĺ�Ӧ��
        var CONTRACT = {
            INVALID_SUPPLIER: 0,
            NO_SIGNED: 0
        };

        $('.contractinfo-table > table  tr').each(function(index, elem) {
            $(elem).find('td:eq(-2)').hide();  // ���ع�Ӧ��״̬��
            $(elem).find('th:eq(-2)').hide();  // ���ع�Ӧ��״̬��
            var supplierStatusElem = $(elem).find('select[name*="_SUPPLIER_STATUS"]');
            var isSignElem = $(elem).find('select[name*="_ISSIGN"]');
            if (supplierStatusElem && isSignElem) {
                var supplierStatus = parseInt(supplierStatusElem.val());
                var isSignStatus = parseInt(isSignElem.val());
                if (supplierStatus == CONTRACT.INVALID_SUPPLIER
                        && isSignStatus == CONTRACT.NO_SIGNED) {
                    $(elem).find('select[name*="_SUPPLIER_ID"]').closest('td').text('');
                }
            }
        });

		var islayer = '<?php echo ($layer); ?>';
                $(".contractinfo-table tbody tr").click(function () {
			$(this).siblings().removeClass("selected");
			$(this).addClass("selected");
		});
		/*if($("[name='ISSIGN_OLD']").val()==-1){
			$("[name='ISSIGN']").parent().parent().find('span').removeClass('spanhidden');
			$("[name='ISSIGN']").parent().hide();

		}*/
		function checkboxevent(obj){
			var fid = $(obj).val(); 
			if( $(obj).is(':checked') ){
				if(!$("#selecttr"+fid).length )$(obj).after('<input name="selecttr[]" id="selecttr'+fid+'"   class="selecttr" value="'+fid+'" type="hidden">');
			}else{
				$("#selecttr"+fid).remove();
			}
		}
		if(islayer == 1){
			//���к�ͬ��ҳ��
			$('.checkedtd').each(function(){
				var val = $(this).val();
				$(this).after('<input type="radio" name="checkradio" value="'+val+'" />').remove();
			});
			$("#checkall").remove();
			$('input[name=checkradio]').click(function(){
				$('input[name=aptocontractId]',window.parent.document).val($(this).val());
			});

		}else{
			$('.checkedtd').each(function(){
				$(this).click(function(){
					checkboxevent(this);
				});
			});
			$("#checkall").click( 
				function(){ 
					if(this.checked){ 
						$("input[name='checkedtd']").each(function(){ 
							 
							this.checked=true;
							checkboxevent(this);
						}); 
					}else{ 
						$("input[name='checkedtd']").each(function(){
							 
							this.checked=false;
							checkboxevent(this);
							 
						}); 
					} 
				} 
			);
		}     
	}) ;
	//�ж�ѡ��
	function  ischeck(){
		var count = 0;
		$('.checkedtd').each(function(){
			if(this.checked){
				count++;
			} 
		});
		return count;
	}
	
	//������ܺ�ͬ
	function addkjcontract()
	{
		var url = "<?php echo U('Purchasing/contract?showForm=1&kjcontract=1');?>";
		window.location.href =url;
	}
	
	//���ɱ�������
	function addreimbursement()
	{
            if(ischeck())
            {
                var contract_ids = [];
                $(".selecttr").each(function(){
                        contract_ids.push($(this).val());
                });

                $.ajax({
                    url:"<?php echo U('Reimbursement/apply_purchase_contract_reim');?>",
                    dataType : 'json',
                    type: 'GET',
                    data:{'contract_ids':contract_ids},
                    success:function(data)
                    {
                        if(data.state == 1)
                        {
                            layer.alert(
                                    data.msg, 
                                    {icon: 1},
                                    function(){                            
                                        if(data.forward != '')
                                        {
                                            self.location = data.forward;
                                        }
                                        else
                                        {
                                            window.location.reload();
                                        }
                                    });
                        }
                        else if(data.state == 0)
                        {
                            layer.alert(data.msg, {icon: 2});
                        }
                        else
                        {
                            var msg = data.msg ? data.msg : '���뱨�������쳣';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                });
            }
            else
            {
                layer.alert('����ѡ��ɹ���ͬ��', {icon: 2});
            }
	}
        
	//ɾ����ͬ
	function fthisDelContract(obj){
		var contract_id   =$(obj).parent().attr('fid');

        layer.confirm(
                'ȷ��ɾ����ͬ��',
                {title: 'ɾ����ͬ'},
                function(index)
                {
                    $.ajax({
                        url:"<?php echo U('Purchasing/del_contract');?>",
                        dataType : 'json',
                        type: 'GET',
                        data:{'contract_id':contract_id},
                        success:function(data)
                        {
                            // layer.alert
                            if(data.status==1){
                                layer.alert('ɾ����ͬ�ɹ���');
                                window.location.href= window.location.href;
                            }else layer.alert(data.info);
                        }
                    });
                }
        );
	}
	 
</script>
<textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // �������ϴ������Ľ��
    });
</script>
</body>
</html>