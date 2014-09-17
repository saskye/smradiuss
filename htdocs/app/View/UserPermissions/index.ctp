<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<script type="text/javascript">
function confirmDelete(msg, link)
{
	yes = confirm(msg);
	if (yes) {
		location.href = link;
	} else {
		return false;
	}
}
</script>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('User Permission List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __("ID"); ?></a></th>
						<th><a><?php echo __("Group Name"); ?></a></th>
						<th><a><?php echo __("Controller"); ?></a></th>
<?php
						if ($this->Access->check($groupName, 'UserPermissionEdit') ||
								$this->Access->check($groupName, 'UserPermissionDelete')) {
?>
							<th><a><?php echo __("Actions"); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($permissionList as $permissionLists) {
?>
						<tr>
							<td><?php echo $permissionLists['Permission']['id']; ?></td>
							<td><?php echo $permissionLists['Aro']['alias']; ?></td>
							<td><?php echo $permissionLists['Aco']['model']; ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'UserPermissionEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/user_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'user_permissions',
												'action' => 'edit',
												$permissionLists['Permission']['id']
											),
											"title" => "Edit permission"
										)
									);
								}
?>

<?php
								if ($this->Access->check($groupName, 'UserPermissionDelete')) {
?>
									<a href="#"
										onclick="return confirmDelete(
											'Are you sure you want to delete this user permission.',
											'<?php
												echo $this->Html->url(
													array(
														'controller' => 'user_permissions',
														'action' => 'remove',
														$permissionLists['Permission']['id']
													)
												);
											?>'
										)"
									>
<?php
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/user_delete.png",
										array(
											"alt" => "Delete",
											"title" => "Delete Permission"
										)
									);
								}
?>
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
								echo $this->Paginator->prev(
									'<<',
									null,
									null,
									array(
										'class' => 'disabled'
									)
								);
								echo $this->Paginator->numbers();
								// Shows the next and previous links.
								echo $this->Paginator->next(
									'>>',
									null,
									null,
									array(
										'class' => 'disabled'
									)
								);
								// Prints X of Y, where X is current page and Y is number of pages.
								echo "<span style='margin-left:20px;'>Page : ".$this->Paginator->counter()."</span>";
							}
?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
