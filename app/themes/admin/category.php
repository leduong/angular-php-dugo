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
	<?php if (!isset($form)) { ?>
	<a onClick="$('#form').submit();" class="button red right mr mb"><small class="icon cross"></small><span><?php echo lang('delete');?></span></a>
	<?php } ?> 
	<a href="admin/category/lists" class="button green right mr mb"><small class="icon documents"></small><span><?php echo lang('list');?></span></a>
	<a href="admin/category/create" class="button green right mr mb"><small class="icon plus"></small><span><span><?php echo lang('create');?></span></span></a> </div>
<br class="clear"/>

<?php if (isset($form)) echo $form; ?>
<?php if (isset($categories)) { ?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$('#form').submit(function(){
    	if ($(this).attr('action').indexOf('delete',1) != -1) {if (!confirm ('<?php echo lang('delete_confirm');?>')) {return false;}}
	});
});
-->
</script>
<form action="/admin/category/delete" method="post" id="form">
	<table class="list">
		<tr>
			<th style="width:10px">
				<input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" />
			</th>
			<th><?php echo lang('name');?></th>
			<th><?php echo lang('parent_category');?></th>
			<th><?php echo lang('sort_order');?></th>
			<th><?php echo lang('status');?></th>
			<th><?php echo lang('action');?></th>
		</tr>
		<?php foreach($categories as $c){?>
		<tr>
			<td><input type="checkbox" name="selected[]" value="<?php echo $c->id;?>" /></td>
			<td><?php echo $c->name;?></td>
			<td><?php if($c->category_id){$parent = new Model_ProductCategory($c->category_id);echo $parent->name;}?></td>
			<td><?php echo $c->sort_order;?></td>
			<td class="center"><a class="checkbox<?php echo ($c->status)?' on':' off';?>"><small></small></a></td>
			<td class="center"><a href="/admin/category/edit/<?php echo $c->id;?>" class="button yellow"><small class="icon pencil"></small><span><?php echo lang('edit');?></span></a></td>
		</tr>
		<?php }?>
	</table>
</form>
<div class="clear">
	<?php if(isset($pagination)) {print $pagination;}?>
</div>
<?php } ?>

<style type="text/css" media="screen">
	.content ul li{list-style: square}
	.preview{width:100px;height:100px}
	form#form {margin:0 auto;padding:0; text-align:left;}
	form#form p {margin:5px 0}
	form#form div.error,form#form #submit {margin:2px 0 2px 220px}
	form#form div.help {font-size:12px;margin:2px 0 2px 220px}
	form#form #description {height:310px;width:600px}
	form#form #excerpt{height:110px;width:600px;}
	form#form label {float:left;text-align:right;width:200px;margin:5px 10px 0 0;}
	form#form select,form#form input[type="file"]{padding:1px;margin:2px 0pt 5px 5px;}
	form#form input[type="text"],form#form input[type="password"]{width:600px;}
</style>
<script type="text/javascript" src="/js/kindeditor/kindeditor.js"></script> 
<script type="text/javascript">
    KE.show({
        id : 'description',
        skinType: 'office',
		allowFileManager : true,
    });
</script>