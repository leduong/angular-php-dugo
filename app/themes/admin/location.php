<div class="box">
  <div class="title">
    <h2>Tỉnh / Thành</h2>
  </div>
<div class="buttons">
	<?php if($form) {?>
		<a href="/admin/location" class="button"><span><?php echo lang('list');?></span></a><?php } else {?>
		<a href="/admin/location/create" class="button"><span><?php echo lang('create');?></span></a>
		<a onclick="$('#form').submit();" class="button"><span><?php echo lang('delete');?></span></a>
	<?php }?>
</div>
  <?php if ($error_warning) { ?>
  <div class="error"><h3><?php echo $error_warning; ?></h3></div>
  <?php } ?>
  <div class="content" style="min-height: 150px;">
    <div><?php if($form) { echo $form; } ?></div>
	<?php if($categories) {?> <script type="text/javascript">
	<!--
	$(document).ready(function(){
	    $('#form').submit(function(){
	        if ($(this).attr('action').indexOf('delete',1) != -1) {if (!confirm ('<?php echo lang('delete_confirm');?>')) {return false;}}
	    });
	});
	-->
	</script>
    <form action="/admin/location/delete" method="post" id="form">
      <table border="1" cellspacing="5" cellpadding="5" style="width:100%; border-collapse:collapse; border-color:#ccc">
        <tr>
          <th style="width:10px"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></th>
          <th>Tỉnh / Thành</th>
          <th><?php echo lang('action');?></th>
        </tr>
        <?php foreach($categories as $c){?>
        <tr>
          <td><input type="checkbox" name="selected[]" value="<?php echo $c->id;?>" /></td>
          <td><?php echo $c->name;?></td>
          <td class="center"><a href="/admin/location/edit/<?php echo $c->id;?>" class="button"><span><?php echo lang('edit');?></span></a></td>
        </tr>
        <?php }?>
      </table>
    <div class="clear">
      <?php if(isset($pagination)) {print $pagination;}?>
    </div>
  </form>
  <?php }?>
  </div>
</div>