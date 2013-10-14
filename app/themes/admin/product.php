<?php if($message){ ?>

<div class="success">
	<div class="tl"></div>
	<div class="tr"></div>
	<div class="desc"> <?php echo $message; ?> </div>
	<div class="bl"></div>
	<div class="br"></div>
</div>
<?php } ?>
<div class="buttons"> <a onClick="$('#form').submit();" class="button red right mr mb"><small class="icon cross"></small><span><?php echo lang('delete');?></span></a> <a href="admin/product/create" class="button green right mr mb"><small class="icon plus"></small><span><span><?php echo lang('create');?></span></span></a> </div>
<br class="clear" />
<form action="/admin/product/delete" method="post" id="form">
	<table class="list">
		<tr>
			<th style="width:10px"><input type="checkbox" onClick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></th>
			<th><?php echo lang('title');?></th>
			<th><?php echo lang('category');?></th>
			<th><?php echo lang('created_date');?></th>
			<th><?php echo lang('status');?></th>
			<th><?php echo lang('action');?></th>
		</tr>
		<?php if(isset($coupons)) foreach($coupons as $c){?>
		<tr>
			<td><input type="checkbox" name="selected[]" value="<?php echo $c->id;?>" /></td>
			<td><?php echo $c->name;?></td>
			<td><?php if($c->category){$b = new Model_ProductCategory($c->category);echo $b->name;}?></td>
			<td><?php echo $c->created_date;?></td>
			<td class="center"><a class="checkbox<?php echo ($c->status)?' on':' off';?>"><small></small></a></td>
			<td class="center"><a href="/admin/product/edit/<?php echo $c->id;?>" class="button yellow"><small class="icon pencil"></small><span><?php echo lang('edit');?></span></a></td>
		</tr>
		<?php }?>
	</table>
</form>
<div class="clear">
	<?php if(isset($pagination)) {print $pagination;}?>
</div>
<script type="text/javascript">
<!--
$(document).ready(function(){
    $('#form').submit(function(){
        if ($(this).attr('action').indexOf('delete',1) != -1) {if (!confirm('<?php echo lang('delete_confirm');?>')) {return false;}}
    });
});
-->
</script>