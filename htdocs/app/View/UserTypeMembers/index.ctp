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
		<div class="col-md-10"><legend><?php echo __('User Type Members List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><?php echo $this->Paginator->sort('ID', 'ID'); ?></th>
						<th><?php echo $this->Paginator->sort('Username', 'Username'); ?></th>
						<th><?php echo $this->Paginator->sort('Disabled', 'Disabled'); ?></th>
<?php
						if ($this->Access->check($groupName, 'UserTypeMembersDelete')) {
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
							<td><?php echo h($userType['UserTypeMember']['ID']); ?></td>
							<td><?php echo h($userType['UserTypeMember']['Username']); ?></td>
							<td><?php echo ($userType['UserTypeMember']['Disabled'] == 1) ? 'true' : 'false'; ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'UserTypeMembersDelete')) {
?>
									<a href="#" onclick="return confirmDelete(
										'Are you sure you want to delete.',
										'<?php
											echo $this->Html->url(
												array(
													'controller' => 'userType_members',
													'action' => 'remove',
													$userType['UserTypeMember']['ID'],
													$userTypeId
												)
											); ?>'
										)">
<?php
										echo $this->Html->image(
											"/resources/custom/images/silk/icons/table_delete.png",
											array(
												"alt" => "Delete",
												"title" => "Delete user type"
											)
										);
?>
									</a>
<?php
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
							if($total > 1) {
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
