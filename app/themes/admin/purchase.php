<div class="buttons"> <a onClick="$('#form').submit();" class="button red right mr mb"><small class="icon cross"></small><span><?php T('delete');?></span></a></div>
<br class="clear" />

<form action="/admin/purchase/delete" method="post" id="form">
    <table class="list">
      <tr>
        <th style="width:10px"><input type="checkbox" onClick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></th>
				<th><?php T('ID');?></th>
				<th><?php T('email');?></th>
				<th><?php T('name');?></th>
				<th><?php T('address');?></th>
				<th><?php T('phone');?></th>
        <th><?php T('title');?></th>
        <th><?php T('created_date');?></th>
        <th><?php T('status');?></th>
        <th><?php T('action');?></th>
      </tr>
      <?php if(isset($purchases)) foreach($purchases as $c){
			$product_id = new Model_Product($c->product_id);
			$user_id = new Model_User($c->user_id);
			?>
      <tr>
				<td><input type="checkbox" name="selected[]" value="<?php echo $c->id;?>" /></td>
				<td><?php echo $c->id;?></td>
				<td><?php if($user_id->username) echo $user_id->username;?></td>
				<td><?php if($user_id->full_name) echo $user_id->full_name;?></td>
				<td><?php if($user_id->address) echo $user_id->address;?></td>
				<td><?php if($user_id->phone) echo $user_id->phone;?> <?php if($user_id->mobile) echo $user_id->mobile;?></td>
				<td><?php echo $product_id->name;?></td>
				<td><?php echo $c->purchased_date;?></td>
				<td class="center"><?php echo $order[$c->status];?></td>
				<td class="center"><a href="/admin/purchase/edit/<?php echo $c->id;?>" class="button yellow"><small class="icon pencil"></small><span><?php T('edit');?></span></a></td>
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
        if ($(this).attr('action').indexOf('delete',1) != -1) {if (!confirm('<?php T('delete_confirm');?>')) {return false;}}
    });
});
-->
</script>