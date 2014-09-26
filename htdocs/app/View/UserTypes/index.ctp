<style type="text/css">
body {
	padding-top: 50px;
}
.pagination .current a {
	background-color: #eee;
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
		<div class="col-md-10"><legend><?php echo __('User Types List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
<?php
						if (
							$this->Access->check($groupName, 'UserTypesEdit') ||
							$this->Access->check($groupName, 'UserTypesDelete') ||
							$this->Access->check($groupName, 'UserTypeMembersView')
						) {
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
									<a href="#" onclick="return confirmDelete(
										'Are you sure you want to delete this type&period;',
										'<?php
											echo $this->Html->url(
												array(
													'controller' => 'user_types',
													'action' => 'remove',
													$userType['UserType']['id']
												)
											); ?>'
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
									echo $this->Paginator->last('Last',	array('tag' => 'li'), null, null);
								} else {
									echo '<li class="disabled"><a href="#">Last</a></li>';
								}

								echo "<span style='margin-left:20px;'>Page &colon; ".$this->Paginator->counter()."</span>";
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
	</div>
</div>
