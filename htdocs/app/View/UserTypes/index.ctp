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
		<div class="col-md-10"><legend><?php echo __('User Types List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
<?php
						if ($this->Access->check($groupName, 'UserTypesEdit') ||
								$this->Access->check($groupName, 'UserTypesDelete') ||
								$this->Access->check($groupName, 'UserTypeMembersView')) {
?>
							<th><a><?php echo __('Actions'); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($userTypes as $userType) {
?>
						<tr>
							<td><? echo h($userType['UserType']['id']); ?></td>
							<td><? echo h($userType['UserType']['alias']); ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'UserTypesEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'user_types',
												'action' => 'edit',
												$userType['UserType']['id']
											),
											"title" => "Edit user type"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'UserTypesDelete')) {
?>
									<a href="#"
											onclick="return confirmDelete(
												'Are you sure you want to delete this type.',
												'<?php
													echo $this->Html->url(
														array(
															'controller' => 'user_types',
															'action' => 'remove',
															$userType['UserType']['id']
														)
													);
												?>'
											)">
<?php
										echo $this->Html->image(
												"/resources/custom/images/silk/icons/group_delete.png",
												array(
													"alt" => "Delete",
													"title" => "Remove User Type"
												)
											);
?>
									</a>
<?php
								}
?>
<?php
								if ($this->Access->check($groupName, 'UserTypeMembersView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group.png",
										array(
											"alt" => "User type member",
											"url" => array(
												'controller' => 'userType_members',
												'action' => 'index',
												$userType['UserType']['id']
											),
											"title" => "User type member"
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
