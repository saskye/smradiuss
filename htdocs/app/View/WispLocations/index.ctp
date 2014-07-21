<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('wisp_left_panel');?>
		<div class="col-md-10"><legend>Wisp Location List</legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID', true);?></a></th>
						<th><a><?php echo __('Name', true);?></a></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($wispLocation as $wLocation): ?>
						<tr>
							<td><? echo $wLocation['WispLocation']['ID'];?></td>
							<td><? echo $wLocation['WispLocation']['Name'];?></td>
							<td>
								<?php
									echo $this->Html->link(
										'<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table_edit.png"></img>',
										array(
											'controller' => 'Wisp_Locations',
											'action' => 'edit',
											$wLocation['WispLocation']['ID']
										),
										array(
											'escape' => false,
											'title' => 'Edit location'
										)
									);
								?>
								<?php
									echo $this->Html->link(
										'<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table_delete.png"></img>',
										array(
											'controller' => 'Wisp_Locations',
											'action' => 'remove',
											$wLocation['WispLocation']['ID']
										),
										array(
											'escape' => false,
											'title' => 'Remove location'
										),
										'Are you sure you want to remove this locations?'
									);
								?>
								<?php
									echo $this->Html->link(
										'<img src="'.BASE_URL.'/resources/custom/images/silk/icons/user.png"></img>',
										array(
											'controller' => 'WispLocation_Members',
											'action' => 'index',
											$wLocation['WispLocation']['ID']
										),
										array(
											'escape' => false,
											'title' => 'Location Member'
										)
									);
								?>
							</td>
						</tr>
					<? endforeach; ?>
					<tr>
						<td align="center" colspan="10">
							<?php
								$total = $this->Paginator->counter(
									array(
									    'format' => '%pages%'
									)
								);
								if ($total >1) {
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
