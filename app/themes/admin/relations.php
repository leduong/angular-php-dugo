<!-- BEGIN PAGE HEADER-->
<div class="row-fluid">
	<div class="span12">
		<!-- BEGIN PAGE TITLE & BREADCRUMB-->
		<h3 class="page-title">
			Relations
		</h3>
		<ul class="breadcrumb">
			<li>
				<i class="icon-home"></i>
				<a href="/admin">Trang chủ</a>
				<i class="icon-angle-right"></i>
			</li>
			<li><a href="/admin/relations">Relations</a></li>
		</ul>
		<!-- END PAGE TITLE & BREADCRUMB-->
	</div>
</div>
<!-- END PAGE HEADER-->

<?php if($message){ ?>
<div class="row-fluid">
	<div class="span12">
		<!-- BEGIN ALERTS PORTLET-->
		<div class="portlet">
			<div class="portlet-body">
				<div class="alert alert-success">
					<button class="close" data-dismiss="alert"></button>
					<?php echo $message; ?>
				</div>
			</div>
		</div>
		<!-- END ALERTS PORTLET-->
	</div>
</div>
<?php } ?>

<div class="row-fluid">
	<div class="span12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">Relations</div>
				<div class="tools">
					<a href="javascript:;" class="collapse"></a>
				</div>
			</div>
			<div class="portlet-body form" style="display: block;">
				<div class="buttons">
					<?php if (!isset($form)) { ?>
					<a onClick="$('#form').submit();" class="btn"><i class="icon-trash"></i> <?php echo lang('delete');?></a>
					<?php } ?>
					<a href="admin/relations/lists" class="btn btn-primary"><i class="icon-list"></i> <?php echo lang('list');?></a>
					<a href="admin/relations/create" class="btn btn-success"><i class="icon-plus"></i> <?php echo lang('create');?></a></div>
				<br class="clear"/>

				<?php if (isset($form)) echo $form; ?>
				<?php if (isset($relations)) { ?>

				<form action="/admin/relations/delete" method="post" id="form">
					<input type="hidden" name="page" value="<?php echo $page; ?>">
					<table class="table table-striped table-bordered table-hover" id="dataTable">
						<thead>
							<tr>
								<th style="width:8px;"><input type="checkbox" class="group-checkable" data-set="#dataTable .checkboxes" /></th>
								<th>Leader</th>
								<th>Subscriber</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($relations as $c){ ?>
							<tr class="odd gradeX">
								<td><input type="checkbox" name="selected[]" class="checkboxes" value="<?php echo $c->id; ?>" /></td>
								<td><?php $u = new Model_User($c->leader); echo $u->username;?></td>
								<td><?php $u = new Model_User($c->subscriber); echo $u->username;?></td>
								<td><a href="/admin/relations/edit/<?php echo $c->id;?>"><i class="icon-pencil"></i> <?php echo lang('edit') ?></a></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</form>
					<?php if(isset($pagination)) {print $pagination;}?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<script>
jQuery(document).ready(function() {
	$('#form').submit(function(){
		if ($(this).attr('action').indexOf('delete',1) != -1) {
			if (!confirm ('<?php echo lang('delete_confirm');?>')) {
				return false;
			}
		}
	});

	var oTable = $('#dataTable').dataTable( {
		"bLengthChange": false,
		"bPaginate": false,
		"aoColumns": [
			{ "bSortable": false },
			null,
			null,
			{ "bSortable": false }
		],
		"aoColumnDefs": [
				{ "aTargets": [ 0 ] }
		],
		"aaSorting": [[1, 'asc']],

		// set the initial value
		"iDisplayLength": 10,
		"bInfo": false
	});

	jQuery('#dataTable .dataTables_filter input').addClass("m-wrap small"); // modify table search input

	$(".pagingTable").html($(".dataTables_paginate"));

	jQuery('#dataTable .group-checkable').change(function () {
			var set = jQuery(this).attr("data-set");
			var checked = jQuery(this).is(":checked");
			jQuery(set).each(function () {
					if (checked) {
							$(this).attr("checked", true);
					} else {
							$(this).attr("checked", false);
					}
			});
			jQuery.uniform.update(set);
	});
});
</script>