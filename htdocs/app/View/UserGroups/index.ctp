<style type="text/css">
body {
	padding-top: 50px;
}
</style>

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
<?php
								if($this->Access->check($groupName, 'UserGroupsDelete')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table_delete.png",
										array(
											"alt" => "Delete",
											"url" => array(
												'controller' => 'user_groups',
												'action' => 'remove',
												$UserGroup['UserGroup']['ID'],
												$userId
											),
											"title" => "Remove Group"
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
		<div class="form-group">
<?php
			if ($this->Access->check($groupName, 'UserGroupsAdd')) {
				echo $this->Html->link(
					__('Add Group'),
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
				__('Cancel'),
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
