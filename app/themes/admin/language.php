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
	<a href="admin/language/lists" class="button green right mr mb"><small class="icon documents"></small><span><?php T('list');?></span></a>
</div>
<br class="clear" />

<?php if (isset($form)) { echo $form; } ?>
<?php if (isset($lists)) { ?>
  <table class="list">
    <tr>
      <th style="width:10px"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></th>
      <th><?php T('name');?></th>
			<th>English</th>
			<th>中文</th>
			<th>한국어</th>
			<th>日本</th>
			<th>Tiếng Việt</th>
      <th><?php T('action');?></th>
    </tr>
    <?php foreach($lists as $c){?>
    <tr>
      <td><input type="checkbox" name="selected[]" value="<?php echo $c->id;?>" /></td>
      <td><?php echo $c->name;?></td>
			<td><?php echo $c->en;?></td>
			<td><?php echo $c->zh;?></td>
			<td><?php echo $c->ko;?></td>
			<td><?php echo $c->ja;?></td>
			<td><?php echo $c->vi;?></td>
      <td><a href="/admin/language/edit/<?php echo $c->id;?>" class="button"><span><?php T('edit');?></span></a></td>
    </tr>
    <?php }?>
  </table>
		<div class="clear">
		  <?php if(isset($pagination)) {print $pagination;}?>
		</div>
<? } ?>

<style type="text/css" media="screen">
	form p {margin:5px 0}
	form textarea{height:40px;width:99%;}
</style>