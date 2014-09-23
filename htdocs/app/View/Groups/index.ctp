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
		<div class="col-md-10"><legend><?php echo __('Groups List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
						<th><a><?php echo __('Priority'); ?></a></th>
						<th><a><?php echo __('Disabled'); ?></a></th>
						<th><a><?php echo __('Comment'); ?></a></th>
<?php
						if (
							$this->Access->check($groupName, 'GroupsEdit') ||
							$this->Access->check($groupName, 'GroupsDelete') ||
							$this->Access->check($groupName, 'GroupAttributesView') ||
							$this->Access->check($groupName, 'GroupMemberView')
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
					foreach ($groups as $group) {
?>
						<tr>
							<td><? echo h($group['Group']['ID']); ?></td>
							<td><? echo h($group['Group']['Name']); ?></td>
							<td><? echo h($group['Group']['Priority']); ?></td>
							<td><? echo ($group['Group']['Disabled'] == 1) ? 'true' : 'false'; ?></td>
							<td><? echo h($group['Group']['Comment']); ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'GroupsEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'groups',
												'action' => 'edit',
												$group['Group']['ID']
											),
											"title" => "Edit group"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'GroupsDelete')) {
?>
									<a href="#" onclick="return confirmDelete(
										'Are you sure you want to delete.',
										'<?php
											echo $this->Html->url(
												array(
													'controller' => 'groups',
													'action' => 'remove',
													$group['Group']['ID']
												)
											); ?>'
										)">
<?php
										echo $this->Html->image(
											"/resources/custom/images/silk/icons/group_delete.png",
											array(
												"alt" => "Delete",
												"title" => "Remove group"
											)
										);
?>
									</a>
<?php
								}
?>
<?php
								if ($this->Access->check($groupName, 'GroupAttributesView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table.png",
										array(
											"alt" => "Group attributes",
											"url" => array(
												'controller' => 'group_attributes',
												'action' => 'index',
												$group['Group']['ID']
											),
											"title" => "Group attributes"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'GroupMemberView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group.png",
										array(
											"alt" => "Group member",
											"url" => array(
												'controller' => 'group_member',
												'action' => 'index',
												$group['Group']['ID']
											),
											"title" => "Group member"
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
							$total = $this->Paginator->counter(array('format' => '%pages%'));

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
									echo $this->Paginator->prev('«', array('tag' => 'li'), null, null);
								} else {
									echo '<li class="disabled"><a href="#">«</a></li>';
								}

								echo $this->Paginator->numbers(array('separator' => false, 'tag' => 'li', 'currentTag' => 'a'));

								if ($this->Paginator->hasNext()) {
									echo $this->Paginator->next('»', array('tag' => 'li'), null, null);
								} else {
									echo '<li class="disabled"><a href="#">»</a></li>';
								}

								if ($this->Paginator->last()) {
									echo $this->Paginator->last('Last',	array('tag' => 'li'), null, null);
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
