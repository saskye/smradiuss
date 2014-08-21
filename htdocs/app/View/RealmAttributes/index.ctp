<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Realm Attribute List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
						<th><a><?php echo __('Operator'); ?></a></th>
						<th><a><?php echo __('Value'); ?></a></th>
						<th><a><?php echo __('Disabled'); ?></a></th>
<?php
						if ($this->Access->check($groupName, 'RealmAttributesEdit') ||
								$this->Access->check($groupName, 'RealmAttributesDelete')) {
?>
							<th><a><?php echo __('Actions'); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					$options = $this->Access->attributeOptions();
					foreach ($realmAttributes as $realmAttributes) {
?>
						<tr>
							<td><?php echo h($realmAttributes['RealmAttribute']['ID']); ?></td>
							<td><?php echo h($realmAttributes['RealmAttribute']['Name']); ?></td>
							<td><?php echo h($options[$realmAttributes['RealmAttribute']['Operator']]); ?></td>
							<td><?php echo h($realmAttributes['RealmAttribute']['Value']); ?></td>
							<td><?php echo (h($realmAttributes['RealmAttribute']['Disabled']) == 1) ? 'true' : 'false'; ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'RealmAttributesEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'realm_attributes',
												'action' => 'edit',
												$realmAttributes['RealmAttribute']['ID'],
												$realmId
											),
											"title" => "Edit attribute"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'RealmAttributesDelete')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table_delete.png",
										array(
											"alt" => "Delete",
											"url" => array(
												'controller' => 'realm_attributes',
												'action' => 'remove',
												$realmAttributes['RealmAttribute']['ID'],
												$realmId
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
				if ($this->Access->check($groupName, 'RealmAttributesAdd')) {
					echo $this->Html->link(
						__('Add'),
						array(
							'action' => 'add',
							$realmId
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
							'controller' => 'realms',
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
