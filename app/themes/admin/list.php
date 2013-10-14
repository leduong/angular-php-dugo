<div class="content" style="min-height: 150px;">
	<div>
		<table class="list">
			<tr><th>ID</th><th>Category Name</th><th>Parent Category</th><th>Slug</th><th>Meta Keywords</th><th>Meta Description</th></tr>
			<?php foreach($categories as $c){?>
				<tr><td><?php echo $c->id;?></td>
					<td><a href="/admin/category/edit/<?php echo $c->id;?>">[edit]</a> <?php echo $c->name;?></td>
					<td><?php echo $c->category_id;?></td>
					<td><?php echo $c->slug;?></td>
					<td><?php echo $c->meta_keywords;?></td>
					<td><?php echo $c->meta_description;?></td>
				</tr>
			<?php }?>
		</table>
	</div>
</div>