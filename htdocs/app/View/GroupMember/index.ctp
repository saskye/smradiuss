<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Group Members List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><?php echo $this->Paginator->sort('ID', 'ID'); ?></th>
						<th><?php echo $this->Paginator->sort('Username', 'Username'); ?></th>
						<th><?php echo $this->Paginator->sort('Disabled', 'Disabled'); ?></th>
						<th><a><?php echo __('Actions'); ?></a></th>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($GroupMember as $GroupMember) {
?>
						<tr>
							<td><?php echo h($GroupMember['GroupMember']['ID']); ?></td>
							<td><?php echo h($GroupMember['GroupMember']['UserName']); ?></td>
							<td><?php echo ($GroupMember['GroupMember']['Disabled'] == 1) ? 'true' : 'false'; ?></td>
							<td>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/table_delete.png",
									array(
										"alt" => "Delete",
										"url" => array(
											'controller' => 'group_member',
											'action' => 'remove',
											$GroupMember['GroupMember']['ID'],
											$groupID
										),
										"title" => "Remove Group"
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
