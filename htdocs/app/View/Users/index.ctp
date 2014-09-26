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
		<div class="col-md-10"><legend><?php echo __('User List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><?php echo $this->Paginator->sort('ID', 'ID'); ?></th>
						<th><?php echo $this->Paginator->sort('Username', 'Username'); ?></th>
						<th><a><?php echo __('Disabled'); ?></a></th>
<?php
						if (
							$this->Access->check($groupName, 'UsersEdit') ||
							$this->Access->check($groupName, 'UsersDelete') ||
							$this->Access->check($groupName, 'UserAttributesView') ||
							$this->Access->check($groupName, 'UserLogsView') ||
							$this->Access->check($groupName, 'UserGroupsView') ||
							$this->Access->check($groupName, 'UserTopupsView')
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
					foreach ($users as $user) {
?>
						<tr>
							<td><?php echo h($user['User']['ID']); ?></td>
							<td><?php echo h($user['User']['Username']); ?></td>
							<td><?php echo ($user['User']['Disabled'] == 1) ? 'true' : 'false'; ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'UsersEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/user_edit.png",
										array("alt" => "Edit",
											"url" => array(
												'controller' => 'users',
												'action' => 'edit',
												$user['User']['ID']
											),
											"title" => "Edit user"
										)
									);
								}

								if($this->Access->check($groupName, 'UsersDelete')) {
?>
									<a href="#" onclick="return confirmDelete(
										'Are you sure you want to delete this.',
										'<?php
											echo $this->Html->url(
												array(
													'controller' => 'users',
													'action' => 'remove',
													$user['User']['ID']
												)
											); ?>'
										)">
<?php
										echo $this->Html->image(
											"/resources/custom/images/silk/icons/user_delete.png",
											array(
												"alt" => "Delete",
												"title" => "Remove user"
											)
										);
?>
									</a>
<?php
								}

								if($this->Access->check($groupName, 'UserAttributesView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table.png",
										array(
											"alt" => "User attributes",
											"url" => array(
												'controller' => 'user_attributes',
												'action' => 'index',
												$user['User']['ID']
											),
											"title" => "User attributes"
										)
									);
								}

								if($this->Access->check($groupName, 'UserLogsView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/page_white_text.png",
										array(
											"alt" => "User logs",
											"url" => array(
												'controller' => 'user_logs',
												'action' => 'index',
												$user['User']['ID']
											),
											"title" => "User logs"
										)
									);
								}

								if($this->Access->check($groupName, 'UserGroupsView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group.png",
										array(
											"alt" => "User groups",
											"url" => array(
												'controller' => 'user_groups',
												'action' => 'index',
												$user['User']['ID']
											),
											"title" => "User groups"
										)
									);
								}

								if($this->Access->check($groupName, 'UserTopupsView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/chart_bar.png",
										array(
											"alt" => "User topups",
											"url" => array(
												'controller' => 'user_topups',
												'action' => 'index',
												$user['User']['ID']
											),
											"title" => "User topups"
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
	</div>
</div>
