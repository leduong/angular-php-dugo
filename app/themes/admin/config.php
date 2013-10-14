<?php if($error_warning){ ?>

<div class="success">
	<div class="tl"></div>
	<div class="tr"></div>
	<div class="desc"> <?php echo $error_warning; ?> </div>
	<div class="bl"></div>
	<div class="br"></div>
</div>
<?php } ?>
<div class="box header">
	<div class="head">
		<div></div>
	</div>
	<h2>Setting</h2>
	<div class="desc">
		<?php if($form)echo $form;?>
	</div>
	<div class="bottom">
		<div></div>
	</div>
</div>
<style type="text/css" media="screen">
	form#form p {margin:5px 0}
	form#form label {float:left;text-align:right;width:200px;margin:5px 10px 0 0;}
	form#form #submit {margin:2px 0 2px 220px;font-size:15px;}
	form#form select{border:1px solid #dbdbdb;color:#666;background:#f0f0f0;padding:5px;}
	form#form input[type="text"]{width:500px;}
	form#form div.error, form#form div.help {font-size:12px;margin:2px 0 2px 220px;padding:0;}
</style>
