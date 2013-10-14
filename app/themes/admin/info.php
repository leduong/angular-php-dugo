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
	<?php if (!isset($form)) { ?> <a onclick="$('#form').submit();" class="button red right mr mb"><small class="icon cross"></small><span><?php T('delete');?></span></a> <?php } ?>
	<a href="admin/info/create" class="button green right mr mb"><small class="icon plus"></small><span><?php T('create');?></span></a>
	<a href="/admin/info/lists" class="button green right mr mb"><small class="icon documents"></small><span><?php T('list');?></span></a>
</div>

<br class="clear" />
  
<div class="content" style="min-height: 150px;">
<?php if (isset($form)) { echo $form; } ?>
<?php if (isset($lists)) { ?>
  <script type="text/javascript">
<!--
$(document).ready(function(){
    $('#form').submit(function(){
        if ($(this).attr('action').indexOf('delete',1) != -1) {if (!confirm ('<?php T('delete_confirm');?>')) {return false;}}
    });
});
-->
</script>
  <form action="/admin/info/delete" method="post" id="form">
    <table class="list">
      <tr>
        <th style="width:10px"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></th>
        <th><?php T('name');?></th>
        <th><?php T('sort_order');?></th>
        <th><?php T('action');?></th>
      </tr>
      <?php foreach($lists as $c){?>
      <tr>
        <td><input type="checkbox" name="selected[]" value="<?php echo $c->id;?>" /></td>
        <td><?php echo $c->name;?></td>
        <td><?php echo $c->sort_order;?></td>
        <td><a href="/admin/info/edit/<?php echo $c->id;?>" class="button"><span><?php T('edit');?></span></a></td>
      </tr>
      <?php }?>
    </table>
    <div class="clear">
      <?php if(isset($pagination)) {print $pagination;}?>
    </div>
  </form>
<?php } ?>
</div>
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
	form#form {margin:0 auto;padding:0; text-align:left;}
	form#form p {margin:5px 0}
	form#form div.error,form#form #submit {margin:2px 0 2px 220px}
	form#form div.help {font-size:12px;margin:2px 0 2px 220px}
	form#form #description {height:310px;width:650px}
	form#form #excerpt{height:110px;width:650px;}
	form#form label {float:left;text-align:right;width:200px;margin:5px 10px 0 0;}
	form#form select,form#form input[type="file"]{padding:1px;margin:2px 0pt 5px 5px;}
	form#form input[type="text"],form#form input[type="password"]{width:650px;}
</style>