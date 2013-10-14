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
	<a href="admin/product/lists" class="button green right mr mb"><small class="icon documents"></small><span><?php T('list');?></span></a>
</div>
<br class="clear" />
<div class="box header">
<div class="head"><div></div></div>
<h2><?php T('added_new_product');?></h2>
<div class="desc">
	<p><?php T('information_needed_to_new_product');?></p>
	<?php if($form)echo $form;?>
</div>
<div class="bottom"><div></div></div>
</div>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript">
<!--
$(function() {$("#start_date").datetimepicker({timeFormat: 'hh:mm:ss',dateFormat:'yy-mm-dd'});});
$(function() {$("#end_date").datetimepicker({timeFormat: 'hh:mm:ss',dateFormat:'yy-mm-dd'});});
var type_id = '<?php echo $type_id;?>';
var subtype_id = '<?php echo $subtype_id;?>';
function objload(k,v){
	if (k=='sub_type') {l = 'admin/product/subtype/type_id/'+v+'/subtype_id/'+subtype_id;type_id=v;}
	$('select[name=\''+k+'\']').load(l);
}
objload('sub_type',type_id);
//-->
</script>
<style type="text/css" media="screen">
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; }
.ui-timepicker-div dl dd { margin: -25px 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
	.content ul li{list-style: square}
	.preview{width:100px;height:100px}
	form#create {margin:0 auto;padding:0; text-align:left;}
	form#create p {margin:5px 0}
	form#create div.error,form#create #submit {margin:2px 0 2px 220px}
	form#create div.help {font-size:12px;margin:2px 0 2px 220px}
	form#create #description {height:310px;width:600px}
	form#create #excerpt{height:110px;width:600px;}
	form#create label {float:left;text-align:right;width:200px;margin:5px 10px 0 0;}
	form#create select,form#create input[type="file"]{padding:1px;margin:2px 0pt 5px 5px;}
	form#create input[type="text"],form#create input[type="password"]{width:600px;}
	form#create #min_limit, form#create #max_limit, form#create #offer, form#create #real_value, form#create #start_date, form#create #end_date{width:160px;}
</style>
<script type="text/javascript" src="/js/kindeditor/kindeditor.js"></script>
<script type="text/javascript">
    KE.show({
        id : 'description',
        skinType: 'office',
		allowFileManager : true,
    });
</script>