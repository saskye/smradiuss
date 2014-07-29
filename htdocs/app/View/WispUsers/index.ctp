<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('wisp_left_panel');?>
		<div class="col-md-10"><legend>Wisp User List</legend>
			<table class="table">
				<thead>
					<tr>
						<th><?php echo $this->Paginator->sort('UserID', 'UserID'); ?></th>
						<th><?php echo $this->Paginator->sort('Username', 'Username'); ?></th>
						<th><a><?php echo __('Disabled', true);?></a></th>
						<th><a><?php echo __('First Name', true);?></a></th>
						<th><a><?php echo __('Last Name', true);?></a></th>
						<th><a><?php echo __('Email', true);?></a></th>
						<th><a><?php echo __('Phone', true);?></a></th>
						<th><a><?php echo __('Actions', true);?></a></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($wispUser as $wUser): ?>
						<tr>
							<td><? echo h($wUser['WispUser']['UserID']);?></td>
							<td><? echo h($wUser['WispUser']['Username']);?></td>
							<td><? echo ($wUser['WispUser']['Disabled'] == 1) ? 'true' : 'false';?></td>
							<td><? echo h($wUser['WispUser']['FirstName']);?></td>
							<td><? echo h($wUser['WispUser']['LastName']);?></td>
							<td><? echo h($wUser['WispUser']['Email']);?></td>
							<td><? echo h($wUser['WispUser']['Phone']);?></td>
							<td>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/user_edit.png",
									array(
										"alt" => "Edit",
										"url" => array(
											'controller' => 'wispUsers',
											'action' => 'edit',
											$wUser['WispUser']['ID']
										),
										"title" => "Edit user"
									)
								);
?>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/user_delete.png",
									array(
										"alt" => "Remove",
										"url" => array(
											'controller' => 'wispUsers',
											'action' => 'remove',
											$wUser['WispUser']['ID']
										),
										"title" => "Remove user"
									)
								);
?>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/page_white_text.png",
									array(
										"alt" => "Remove",
										"url" => array(
											'controller' => 'wispUserLogs',
											'action' => 'index',
											$wUser['WispUser']['UserID']
										),
										"title" => "User logs"
									)
								);
?>
<?php
								echo $this->Html->image(
									"/resources/custom/images/silk/icons/chart_bar.png",
									array(
										"alt" => "Remove",
										"url" => array(
											'controller' => 'wispUsers_topups',
											'action' => 'index',
											$wUser['WispUser']['UserID']
										),
										"title" => "User topups"
									)
								);
?>
							</td>
						</tr>
					<? endforeach; ?>
					<tr>
						<td align="center" colspan="10" >
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
