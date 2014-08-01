<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('wisp_left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Wisp Location List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID', true); ?></a></th>
						<th><a><?php echo __('Name', true); ?></a></th>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($wispLocation as $wLocation) {
?>
						<tr>
							<td><? echo h($wLocation['WispLocation']['ID']); ?></td>
							<td><? echo h($wLocation['WispLocation']['Name']); ?></td>
							<td>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/table_edit.png",
									array(
										"alt" => "Edit",
										"url" => array(
											'controller' => 'Wisp_Locations',
											'action' => 'edit',
											$wLocation['WispLocation']['ID']
										),
										"title" => "Edit location"
									)
								);
?>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/table_delete.png",
									array(
										"alt" => "Edit",
										"Remove" => array(
											'controller' => 'Wisp_Locations',
											'action' => 'remove',
											$wLocation['WispLocation']['ID']
										),
										"title" => "Remove location"
									)
								);
?>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/user.png",
									array(
										"alt" => "Location Member",
										"url" => array(
											'controller' => 'WispLocation_Members',
											'action' => 'index',
											$wLocation['WispLocation']['ID']
										),
										"title" => "Location Member"
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
