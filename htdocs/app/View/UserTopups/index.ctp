<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('User Topups List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><?php echo $this->Paginator->sort('ID', 'ID'); ?></th>
						<th><?php echo $this->Paginator->sort('Type', 'Type'); ?></th>
						<th><?php echo $this->Paginator->sort('Value', 'Value'); ?></th>
						<th><a><?php echo __('Valid From'); ?></a></th>
						<th><a><?php echo __('Valid To'); ?></a></th>
<?php
						if ($this->Access->check($groupName, 'UserTopupsEdit') ||
								$this->Access->check($groupName, 'UserTopupsDelete')) {
?>
							<th><a><?php echo __('Action'); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($topups as $topup) {
?>
						<tr>
							<td><?php echo h($topup['UserTopup']['ID']); ?></td>
							<td><?php echo ($topup['UserTopup']['Type'] == 1) ? __('Traffic') : __('Uptime'); ?></td>
							<td><?php echo h($topup['UserTopup']['Value']); ?></td>
							<td><?php echo date("Y-m-d", strtotime(h($topup['UserTopup']['ValidFrom']))); ?></td>
							<td><?php echo date("Y-m-d", strtotime(h($topup['UserTopup']['ValidTo']))); ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'UserTopupsEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'user_topups',
												'action' => 'edit',
												$topup['UserTopup']['ID'],
												$userId
											),
											"title" => "Edit topup"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'UserTopupsDelete')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table_delete.png",
										array(
											"alt" => "Delete",
											"url" => array(
												'controller' => 'user_topups',
												'action' => 'remove',
												$topup['UserTopup']['ID'],
												$userId
											),
											"title" => "Remove topup"
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
			if ($this->Access->check($groupName, 'UserTopupsAdd')) {
				echo $this->Html->link(
					__('Add Topups'),
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
			)
?>
		</div>
	</div>
</div>
