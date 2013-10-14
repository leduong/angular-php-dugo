<script type="text/javascript"><!--
function image_upload(field, preview) {
	$('#dialog').remove();
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="/common/filemanager?field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	$('#dialog').dialog({
		title: 'Quản lý hình ảnh',
		close: function (event, ui) {
			if ($('#' + field).attr('value')) {
				$.ajax({
					url: '/common/filemanager/image',
					type: 'POST',
					data: 'image=' + encodeURIComponent($('#' + field).val()),
					dataType: 'text',
					success: function(data) {
						$('#' + preview).replaceWith('<img src="' + data + '" alt="" id="' + preview + '" style="width:100px;height:100px" onclick="image_upload(\'' + field + '\', \'' + preview + '\');" />');
					}
				});
			}
		},	
		bgiframe: false,
		width: 700,
		height: 400,
		resizable: false,
		modal: false
	});
};
//--></script>
<style type="text/css" media="screen">
	.content ul li{list-style: square}
	form#create p {margin:5px 0}
	form#create label {float:left;text-align:right;width:200px;margin:5px 10px 0 0;}
	form#create div.error,form#create #submit {margin:2px 0 2px 220px}
	form#create div.help {font-size:12px;margin:2px 0 2px 220px}
	form#create #address{height:60px;width:600px;}
	form#create input[type="text"]{width:600px;}
</style>

<?php if($error_warning){ ?>

<div class="success">
	<div class="tl"></div>
	<div class="tr"></div>
	<div class="desc"> <?php echo $error_warning; ?> </div>
	<div class="bl"></div>
	<div class="br"></div>
</div>
<?php } ?>
<div class="buttons">	
	<a href="/admin/shop/lists" class="button green right mr mb"><small class="icon documents"></small><span><?php echo lang('list');?></span></a>
</div>
<br class="clear" />

<div class="box header">
<div class="head"><div></div></div>
<h2><?php echo lang('added_new_shop');?></h2>
<div class="desc">
	<p><?php echo lang('information_needed_to_new_shop');?></p>
	<?php if($form)echo $form;?>
</div>
<div class="bottom"><div></div></div>
</div>