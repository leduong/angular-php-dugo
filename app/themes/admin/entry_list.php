<?php if($error_warning){ ?>

<div class="success">
	<div class="tl"></div>
	<div class="tr"></div>
	<div class="desc"> <?php echo $error_warning; ?> </div>
	<div class="bl"></div>
	<div class="br"></div>
</div>
<?php } ?>

<div class="buttons"> <a onClick="$('#form').submit();" class="button red right mr mb"><small class="icon cross"></small><span><?php echo lang('delete');?></span></a> <a href="admin/entry/create" class="button green right mr mb"><small class="icon plus"></small><span><span><?php echo lang('create');?></span></span></a> </div>

<br class="clear" />

<form action="/admin/entry/delete" method="post" id="form">
  <table class="list">
    <tr>
      <th style="width:10px"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></th>
      <th><?php echo lang('title');?></th>
	  <th><?php echo lang('category');?></th>
      <th><?php echo lang('published');?></th>
      <th><?php echo lang('action');?></th>
    </tr>
    <?php foreach($entries as $c){?>
    <tr>
      <td><input type="checkbox" name="selected[]" value="<?php echo $c->id;?>" /></td>
      <td><?php echo $c->title;?></td>
	  <td><?php if($c->category_id){$category = new Model_Articles($c->category_id);echo $category->name;}?></td>
	  <td class="center"><a class="checkbox<?php echo ($c->published)?' on':' off';?>"><small></small></a></td>
      <td class="center"><a href="/admin/entry/edit/<?php echo $c->id;?>" class="button yellow"><small class="icon pencil"></small><span><?php echo lang('edit');?></span></a></td>
    </tr>
    <?php }?>
  </table>
</form>
<?php if(isset($pagination)) {print $pagination;}?>

<script type="text/javascript">
<!--
$(document).ready(function(){
    $('#form').submit(function(){
        if ($(this).attr('action').indexOf('delete',1) != -1) {if (!confirm ('<?php echo lang('delete_confirm');?>')) {return false;}}
    });
});
-->
</script>