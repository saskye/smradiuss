<style type="text/css">
body {
padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Client Attributes List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
						<th><a><?php echo __('Operator'); ?></a></th>
						<th><a><?php echo __('Value'); ?></a></th>
						<th><a><?php echo __('Disabled'); ?></a></th>
<?php
						if ($this->Access->check($groupName, 'ClientAttributesEdit') ||
								$this->Access->check($groupName, 'ClientAttributesDelete')) {
?>
							<th><a><?php echo __('Actions'); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					$options = $attributeOperators;
					foreach ($clientAttributes as $clientAttributes) {
?>
					<tr>
						<td><?php echo h($clientAttributes['ClientAttribute']['ID']); ?></td>
						<td><?php echo h($clientAttributes['ClientAttribute']['Name']); ?></td>
						<td><?php echo h($options[$clientAttributes['ClientAttribute']['Operator']]); ?></td>
						<td><?php echo h($clientAttributes['ClientAttribute']['Value']); ?></td>
						<td><?php echo ($clientAttributes['ClientAttribute']['Disabled'] == 1) ? 'true' : 'false'; ?></td>
						<td>
<?php
							if ($this->Access->check($groupName, 'ClientAttributesEdit')) {
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/table_edit.png",
									array(
										"alt" => "Edit",
										"url" => array(
											'controller' => 'client_attributes',
											'action' => 'edit',
											$clientAttributes['ClientAttribute']['ID'],
											$clientID
										),
										"title" => "Edit attribute"
									)
								);
							}
?>
<?php
							if ($this->Access->check($groupName, 'ClientAttributesDelete')) {
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/table_delete.png",
									array(
										"alt" => "Delete",
										"url" => array(
											'controller' => 'client_attributes',
											'action' => 'remove',
											$clientAttributes['ClientAttribute']['ID'],
											$clientID
										),
										"title" => "Remove attribute"
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
			<div class="form-group">
<?php
				if ($this->Access->check($groupName, 'ClientAttributesAdd')) {
					echo $this->Html->link(
						__('Add'),
						array(
							'action' => 'add',
							$clientID
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
						'controller' => 'clients',
						'action' => 'index'
					),
					array(
						'class' => 'btn btn-default'
					)
				);
?>
			</div>
		</div>
	</div>
</div>
