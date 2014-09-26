<style type="text/css">
body {
	padding-top: 50px;
}

.pagination .current a {
	background-color: #EEEEEE;
}
</style>

<script type="text/javascript">
function confirmDelete(msg, link)
{
	if (confirm(msg)) {
		location.href = link;
	} else {
		return false;
	}
}
</script>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('User Group List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><?php echo $this->Paginator->sort('ID', 'ID'); ?></th>
						<th><?php echo $this->Paginator->sort('Name', 'Name'); ?></th>
<?php
						if($this->Access->check($groupName, 'UserGroupsDelete')) {
?>
							<th><a><?php echo __('Actions'); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($UserGroup as $UserGroup) {
?>
						<tr>
							<td><?php echo h($UserGroup['UserGroup']['ID']); ?></td>
							<td><?php echo h($UserGroup['UserGroup']['group']); ?></td>
							<td>
								<a href="#" onclick="return confirmDelete(
									'Are you sure you want to delete this.',
									'<?php
										echo $this->Html->url(
											array(
												'controller' => 'user_groups',
												'action' => 'remove',
												$UserGroup['UserGroup']['ID'],
												$userId
											)
										); ?>'
									)">
<?php
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table_delete.png",
										array(
											"alt" => "Delete",
											"title" => "Remove Group"
										)
									);
?>
								</a>
							</td>
						</tr>
<?php
					}
?>
					<tr>
						<td align="center" colspan="10">
<?php
							$total = $this->Paginator->counter(
								array(
									'format' => '%pages%'
								)
							);
							if ($total > 1) {
?>
								<ul class="pagination">
<?php
								if ($this->Paginator->first()) {
									echo $this->Paginator->first('First', array('tag' => 'li'), null, null);
								} else {
									echo '<li class="disabled"><a href="#">First</a></li>';
								}

								if ($this->Paginator->hasPrev()) {
									echo $this->Paginator->prev('&laquo;', array('tag' => 'li', 'escape' => false), null, null);
								} else {
									echo '<li class="disabled"><a href="#">&laquo;</a></li>';
								}

								echo $this->Paginator->numbers(array('separator' => false, 'tag' => 'li', 'currentTag' => 'a'));

								if ($this->Paginator->hasNext()) {
									echo $this->Paginator->next('&raquo;', array('tag' => 'li', 'escape' => false), null, null);
								} else {
									echo '<li class="disabled"><a href="#">&raquo;</a></li>';
								}

								if ($this->Paginator->last()) {
									echo $this->Paginator->last('Last', array('tag' => 'li'), null, null);
								} else {
									echo '<li class="disabled"><a href="#">Last</a></li>';
								}

								echo "<span style='margin-left:20px;'>Page : ".$this->Paginator->counter()."</span>";
?>
								</ul>
<?php
							}
?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="form-group">
<?php
			if ($this->Access->check($groupName, 'UserGroupsAdd')) {
				echo $this->Html->link(
					'Add Group',
					array(
						'action' => 'add',
						$userId
					),
					array(
						'class' => 'btn btn-primary'
					)
				);
			}
?>
<?php
			echo $this->Html->link(
				'Cancel',
				array(
					'controller' => 'users',
					'action' => 'index',
					$userId
				),
				array(
					'class' => 'btn btn-default'
				)
			);
?>
		</div>
	</div>
</div>
