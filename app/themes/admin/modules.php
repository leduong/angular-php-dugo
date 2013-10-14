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
	<a href="admin/modules/lists" class="button green right mr mb"><small class="icon documents"></small><span><?php T('list');?></span></a>
</div>
<br class="clear" />

<?php if (isset($form)) { echo $form; } ?>
<?php if (isset($lists)) { ?>
  <table class="list">
    <tr>
      <th style="width:10px"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></th>
      <th><?php T('name');?></th>
      <th><?php T('action');?></th>
    </tr>
    <?php foreach($lists as $c){?>
    <tr>
      <td><input type="checkbox" name="selected[]" value="<?php echo $c->id;?>" /></td>
      <td><?php T($c->key);?></td>
      <td><a href="/admin/modules/edit/<?php echo $c->id;?>" class="button"><span><?php T('edit');?></span></a></td>
    </tr>
    <?php }?>
  </table>
<?php } ?>

<style type="text/css" media="screen">
	form p {margin:5px 0}
	form #description{height:300px;width:600px;}
</style>

<script type="text/javascript" src="/js/kindeditor/kindeditor.js"></script> 
<script type="text/javascript">
    KE.show({
        id : 'description',
        skinType: 'office',
		filterMode : false,
		allowFileManager : true,
    });
</script>