<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en" xml:lang="en">
<head>
<title>Quản lý hình ảnh</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.js"></script>
<script type="text/javascript" src="/js/jquery.tree.js"></script>
<script type="text/javascript" src="/js/ajaxupload.js"></script>
<script type="text/javascript" src="/js/jquery.bgiframe.js"></script>
<link href="/themes/admin/js/jquery-ui.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body {
	padding: 0;
	margin: 0;
	background: #F7F7F7;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
}
img {
	border: 0;
}
#container {
	padding: 0px 10px 7px 10px;
	height: 340px;
}
#menu {
	clear: both;
	height: 29px;
	margin-bottom: 3px;
}
#column_left {
	background: #FFF;
	border: 1px solid #CCC;
	float: left;
	width: 20%;
	height: 320px;
	overflow: auto;
}
#column_right {
	background: #FFF;
	border: 1px solid #CCC;
	float: right;
	width: 78%;
	height: 320px;
	overflow: auto;
	text-align: center;
}
#column_right div {
	text-align: left;
	padding: 5px;
}
#column_right a {
	display: inline-block;
	text-align: center;
	border: 1px solid #EEEEEE;
	cursor: pointer;
	margin: 5px;
	padding: 5px;
}
#column_right a.selected {
	border: 1px solid #7DA2CE;
	background: #EBF4FD;
}
#column_right input {
	display: none;
}
#dialog {
	display: none;
}
.button {
	display: block;
	float: left;
	padding: 8px 5px 8px 25px;
	margin-right: 5px;
	background-position: 5px 6px;
	background-repeat: no-repeat;
	cursor: pointer;
}
.button:hover {
	background-color: #EEEEEE;
}
.thumb {
	padding: 5px;
	width: 105px;
	height: 105px;
	background: #F7F7F7;
	border: 1px solid #CCCCCC;
	cursor: pointer;
	cursor: move;
	position: relative;
}
.img{width: 100px;height: 100px;}
</style>
</head>
<body>
<div id="container">
  <div id="menu"><a id="create" class="button" style="background-image: url('/images/filemanager/folder.png');">Thư mục mới:</a><a id="delete" class="button" style="background-image: url('/images/filemanager/edit-delete.png');">Xóa</a><a id="move" class="button" style="background-image: url('/images/filemanager/edit-cut.png');">Di chuyển</a><a id="copy" class="button" style="background-image: url('/images/filemanager/edit-copy.png');">Sao chép</a><a id="rename" class="button" style="background-image: url('/images/filemanager/edit-rename.png');">Đổi tên</a><a id="upload" class="button" style="background-image: url('/images/filemanager/upload.png');">Tải lên</a><a id="refresh" class="button" style="background-image: url('/images/filemanager/refresh.png');">Làm mới</a></div>

  <div id="column_left"></div>
  <div id="column_right"></div>
</div>
<script type="text/javascript">
<!--
$(document).ready(function () { 
	$('#column_left').tree({
		data: { 
			type: 'json',
			async: true, 
			opts: { 
				method: 'POST', 
				url: '/common/filemanager/directory.html'
			} 
		},
		selected: 'top',
		ui: {		
			theme_name: 'apple',
			animation: 700
		},	
		types: { 
			'default': {
				clickable: true,
				creatable: false,
				renameable: false,
				deletable: false,
				draggable: false,
				max_children: -1,
				max_depth: -1,
				valid_children: 'all'
			}
		},
		callback: {
			beforedata: function(NODE, TREE_OBJ) { 
				if (NODE == false) {
					TREE_OBJ.settings.data.opts.static = [ 
						{
							data: 'image',
							attributes: { 
								'id': 'top',
								'directory': ''
							}, 
							state: 'closed'
						}
					];
					
					return { 'directory': '' } 
				} else {
					TREE_OBJ.settings.data.opts.static = false;  
					
					return { 'directory': $(NODE).attr('directory') } 
				}
			},		
			onselect: function (NODE, TREE_OBJ) {
				$.ajax({
					url: '/common/filemanager/files.html',
					type: 'POST',
					data: 'directory=' + encodeURIComponent($(NODE).attr('directory')),
					dataType: 'json',
					success: function(json) {
						html = '<div>';						
						if (json) {
							for (i = 0; i < json.length; i++) {								
								name = '';								
								filename = json[i]['filename'];								
								for (j = 0; j < filename.length; j = j + 15) {
									name += filename.substr(j, 15) + '<br />';
								}								
								name += json[i]['size'];								
								html += '<a file="' + json[i]['file'] + '"><img  class="img" src="' + json[i]['thumb'] + '" title="' + json[i]['filename'] + '" /><br />' + name + '</a>';
							}
						}						
						html += '</div>';						
						$('#column_right').html(html);
					}
				});
			}
		}
	});	
	
	$('#column_right a').live('click', function () {
		if ($(this).attr('class') == 'selected') {
			$(this).removeAttr('class');
		} else {
			$('#column_right a').removeAttr('class');			
			$(this).attr('class', 'selected');
		}
	});
	
	$('#column_right a').live('dblclick', function () {
		parent.$('#<?php echo $_GET["field"];?>').attr('value', '/uploads/' + $(this).attr('file'));
		parent.$('#dialog').dialog('close');		
		parent.$('#dialog').remove();	
			});		
						
	$('#create').bind('click', function () {
		var tree = $.tree.focused();		
		if (tree.selected) {
			$('#dialog').remove();			
			html  = '<div id="dialog">';
			html += 'Thư mục mới: <input type="text" name="name" value="" /> <input type="button" value="Submit" />';
			html += '</div>';
			
			$('#column_right').prepend(html);			
			$('#dialog').dialog({
				title: 'Thư mục mới:',
				resizable: false
			});	
			
			$('#dialog input[type=\'button\']').bind('click', function () {
				$.ajax({
					url: '/common/filemanager/create.html',
					type: 'POST',
					data: 'directory=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();							
							tree.refresh(tree.selected);							
							alert(json.success);
						} else {
							alert(json.error);
						}
					}
				});
			});
		} else {
			alert('Thông báo: Hãy chọn một thư mục!');	
		}
	});
	
	$('#delete').bind('click', function () {
		path = $('#column_right a.selected').attr('file');							 
		if (path) {
			$.ajax({
				url: '/common/filemanager/delete.html',
				type: 'POST',
				data: 'path=' + path,
				dataType: 'json',
				success: function(json) {
					if (json.success) {
						var tree = $.tree.focused();					
						tree.select_branch(tree.selected);						
						alert(json.success);
					}					
					if (json.error) {
						alert(json.error);
					}
				}
			});				
		} else {
			var tree = $.tree.focused();			
			if (tree.selected) {
				$.ajax({
					url: '/common/filemanager/delete.html',
					type: 'POST',
					data: 'path=' + encodeURIComponent($(tree.selected).attr('directory')),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							tree.select_branch(tree.parent(tree.selected));							
							tree.refresh(tree.selected);							
							alert(json.success);
						}
						if (json.error) {alert(json.error);
						}
					}
				});			
			} else {
				alert('Thông báo: Hãy chọn một thư mục hoặc tập tin!');
			}			
		}
	});
	
	$('#move').bind('click', function () {
		$('#dialog').remove();		
		html  = '<div id="dialog">';
		html += 'Di chuyển: <select name="to"></select> <input type="button" value="Submit" />';
		html += '</div>';
		$('#column_right').prepend(html);		
		$('#dialog').dialog({
			title: 'Di chuyển',
			resizable: false
		});

		$('#dialog select[name=\'to\']').load('/common/filemanager/folders.html');		
		$('#dialog input[type=\'button\']').bind('click', function () {
			path = $('#column_right a.selected').attr('file');
							 
			if (path) {																
				$.ajax({
					url: '/common/filemanager/move.html',
					type: 'POST',
					data: 'from=' + encodeURIComponent(path) + '&to=' + encodeURIComponent($('#dialog select[name=\'to\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();							
							var tree = $.tree.focused();							
							tree.select_branch(tree.selected);							
							alert(json.success);
						}						
						if (json.error) {
							alert(json.error);
						}
					}
				});
			} else {
				var tree = $.tree.focused();
				
				$.ajax({
					url: '/common/filemanager/move.html',
					type: 'POST',
					data: 'from=' + encodeURIComponent($(tree.selected).attr('directory')) + '&to=' + encodeURIComponent($('#dialog select[name=\'to\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();							
							tree.select_branch('#top');								
							tree.refresh(tree.selected);							
							alert(json.success);
						}
						if (json.error) {
							alert(json.error);
						}
					}
				});				
			}
		});
	});

	$('#copy').bind('click', function () {
		$('#dialog').remove();
		
		html  = '<div id="dialog">';
		html += 'Tên: <input type="text" name="name" value="" /> <input type="button" value="Submit" />';
		html += '</div>';

		$('#column_right').prepend(html);		
		$('#dialog').dialog({
			title: 'Sao chép',
			resizable: false
		});
		
		$('#dialog select[name=\'to\']').load('/common/filemanager/folders.html');		
		$('#dialog input[type=\'button\']').bind('click', function () {
			path = $('#column_right a.selected').attr('file');							 
			if (path) {																
				$.ajax({
					url: '/common/filemanager/copy.html',
					type: 'POST',
					data: 'path=' + encodeURIComponent(path) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();							
							var tree = $.tree.focused();							
							tree.select_branch(tree.selected);							
							alert(json.success);
						}						
						if (json.error) {
							alert(json.error);
						}
					}
				});
			} else {
				var tree = $.tree.focused();
				$.ajax({
					url: '/common/filemanager/copy.html',
					type: 'POST',
					data: 'path=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();							
							tree.select_branch(tree.parent(tree.selected));							
							tree.refresh(tree.selected);							
							alert(json.success);
						}						
						if (json.error) {
							alert(json.error);
						}
					}
				});				
			}
		});	
	});
	
	$('#rename').bind('click', function () {
		$('#dialog').remove();		
		html  = '<div id="dialog">';
		html += 'Tên: <input type="text" name="name" value="" /> <input type="button" value="Submit" />';
		html += '</div>';

		$('#column_right').prepend(html);		
		$('#dialog').dialog({
			title: 'Đổi tên',
			resizable: false
		});
		
		$('#dialog input[type=\'button\']').bind('click', function () {
			path = $('#column_right a.selected').attr('file');							 
			if (path) {		
				$.ajax({
					url: '/common/filemanager/rename.html',
					type: 'POST',
					data: 'path=' + encodeURIComponent(path) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();							
							var tree = $.tree.focused();					
							tree.select_branch(tree.selected);							
							alert(json.success);
						}						
						if (json.error) {
							alert(json.error);
						}
					}
				});			
			} else {
				var tree = $.tree.focused();				
				$.ajax({ 
					url: '/common/filemanager/rename.html',
					type: 'POST',
					data: 'path=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();								
							tree.select_branch(tree.parent(tree.selected));							
							tree.refresh(tree.selected);							
							alert(json.success);
						}
						if (json.error) {
							alert(json.error);
						}
					}
				});
			}
		});		
	});
	
	new AjaxUpload('#upload', {
		action: '/common/filemanager/upload.html',
		name: 'image',
		autoSubmit: false,
		responseType: 'json',
		onChange: function(file, extension) {
			var tree = $.tree.focused();
			if (tree.selected) {
				this.setData({'directory': $(tree.selected).attr('directory')});
			} else {
				this.setData({'directory': ''});
			}			
			this.submit();
		},
		onSubmit: function(file, extension) {
			$('#upload').append('<img src="/images/loading.gif" id="loading" style="padding-left: 5px;" />');
		},
		onComplete: function(file, json) {
			if (json.success) {
				var tree = $.tree.focused();					
				tree.select_branch(tree.selected);				
				alert(json.success);
			}			
			if (json.error) {
				alert(json.error);
			}			
			$('#loading').remove();
		}
	});
	
	$('#refresh').bind('click', function () {
		var tree = $.tree.focused();		
		tree.refresh(tree.selected);
	});	
});
//-->
</script>
</body>
</html>