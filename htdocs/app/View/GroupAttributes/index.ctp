<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel') ;?>
		<div class="col-md-10"><legend><?php echo __('Group Attribute List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
						<th><a><?php echo __('Operator'); ?></a></th>
						<th><a><?php echo __('Value'); ?></a></th>
						<th><a><?php echo __('Disabled'); ?></a></th>
						<th><a><?php echo __('Actions'); ?></a></th>
					</tr>
				</thead>
				<tbody>
<?php
					$options=array(
						'=',
						':=',
						'==',
						'+=',
						'!=',
						'<',
						'>',
						'<=',
						'>=',
						'=~',
						'!~',
						'=*',
						'!*',
						'||=='
					);
					foreach ($groupAttributes as $groupAttribute) {
?>
						<tr>
							<td><?php echo h($groupAttribute['GroupAttribute']['ID']); ?></td>
							<td><?php echo h($groupAttribute['GroupAttribute']['Name']); ?></td>
							<td><?php echo h($options[$groupAttribute['GroupAttribute']['Operator']]); ?></td>
							<td><?php echo h($groupAttribute['GroupAttribute']['Value']); ?></td>
							<td><?php echo ($groupAttribute['GroupAttribute']['Disabled'] == 1) ? 'true' : 'false'; ?></td>
							<td>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/table_edit.png",
									array(
										"alt" => "Edit",
										"url" => array(
											'controller' => 'group_attributes',
											'action' => 'edit',
											$groupAttribute['GroupAttribute']['ID'],
											$groupId
										),
										"title" => "Edit attribute"
									)
								);
?>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/table_delete.png",
									array(
										"alt" => "Delete",
										"url" => array(
											'controller' => 'group_attributes',
											'action' => 'remove',
											$groupAttribute['GroupAttribute']['ID'],
											$groupId
										),
										"title" => "Remove attribute"
									)
								);
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
								// Prints X of Y, where X is current page and Y is number of pages
								echo "<span style='margin-left:20px;'>Page : ".$this->Paginator->counter()."</span>";
							}
?>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="form-group">
<?php
				echo $this->Html->link(
					__('Add'),
					array(
						'action' => 'add',
						$groupId
					),
					array(
						'class' => 'btn btn-primary'
					)
				);
?>
<?php
				echo $this->Html->link(
					__('Cancel'),
					array(
						'controller' => 'groups',
						'action' => 'index',
						$groupId
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
