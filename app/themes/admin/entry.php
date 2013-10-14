<?php if($message){ ?>

<div class="success">
	<div class="tl"></div>
	<div class="tr"></div>
	<div class="desc"> <?php echo $message; ?> </div>
	<div class="bl"></div>
	<div class="br"></div>
</div>
<?php } ?>

<div class="buttons">	
	<a href="/admin/entry/list" class="button green right mr mb"><small class="icon documents"></small><span><?php echo lang('list');?></span></a>
</div>
<br class="clear" />
<?php if($form) { echo $form; } ?>

<style type="text/css" media="screen">
	form#entry textarea {clear:both;}
	form#entry #excerpt {height:60px}
</style>
<script type="text/javascript" src="/js/kindeditor/kindeditor.js"></script>
<script type="text/javascript">
    KE.show({
        id : 'description',
        skinType: 'office',
		allowFileManager : true,
    });
</script>
<style type="text/css" media="screen">
	.content ul li{list-style: square}
	.preview{width:100px;height:100px}
	form#entry {margin:0 auto;padding:0; text-align:left;}
	form#entry p {margin:5px 0}
	form#entry div.error,form#entry #submit {margin:2px 0 2px 220px}
	form#entry div.help {font-size:12px;margin:2px 0 2px 220px}
	form#entry #description {height:310px;width:650px}
	form#entry #excerpt{height:110px;width:650px;}
	form#entry label {float:left;text-align:right;width:200px;margin:5px 10px 0 0;}
	form#entry select,form#entry input[type="file"]{padding:1px;margin:2px 0pt 5px 5px;}
	form#entry input[type="text"],form#entry input[type="password"]{width:650px;}
</style>