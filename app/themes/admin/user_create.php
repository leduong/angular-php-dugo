<!-- BEGIN PAGE HEADER-->
<div class="row-fluid">
	<div class="span12">
		<!-- BEGIN PAGE TITLE & BREADCRUMB-->
		<h3 class="page-title">
			Thành viên
		</h3>
		<ul class="breadcrumb">
			<li>
				<i class="icon-home"></i>
				<a href="/admin">Trang chủ</a>
				<i class="icon-angle-right"></i>
			</li>
			<li><a href="/admin/user">Thành viên</a><i class="icon-angle-right"></i></li>
			<li><a href="/admin/user/create">Thêm</a></li>
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
				<div class="caption"><?php echo lang('register_new_account');?></div>
				<div class="tools">
					<a href="javascript:;" class="collapse"></a>
				</div>
			</div>
			<div class="portlet-body form" style="display: block;">
				<div class="buttons">
					<?php if (!isset($form)) { ?>
					<a onClick="$('#form').submit();" class="btn"><i class="icon-trash"></i><?php echo lang('delete');?></a>
					<?php } ?>
					<a href="admin/user/lists" class="btn btn-primary"><i class="icon-list"></i><?php echo lang('list');?></a>
				<br class="clear"/>

				<?php if (isset($form)) echo $form; ?>
			
			</div>
		</div>
	</div>
</div>