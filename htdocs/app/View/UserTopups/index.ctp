<style type="text/css">
body {
	padding-top: 50px;
}
.pagination .current a {
	background-color: #EEEEEE;
}
</style>

<script type="text/javascript">
function confirmDelete(msg, link)
{
	if (confirm(msg)) {
		location.href = link;
	} else {
		return false;
	}
}
</script>

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
						if (
							$this->Access->check($groupName, 'UserTopupsEdit') ||
							$this->Access->check($groupName, 'UserTopupsDelete')
						) {
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
?>
									<a href="#" onclick="return confirmDelete(
										'Are you sure you want to delete this.',
										'<?php
											echo $this->Html->url(
												array(
													'controller' => 'user_topups',
													'action' => 'remove',
													$topup['UserTopup']['ID'],
													$userId
												)
											); ?>'
										)">
<?php
										echo $this->Html->image(
											"/resources/custom/images/silk/icons/table_delete.png",
											array(
												"alt" => "Delete",
												"title" => "Remove topup"
											)
										);
?>
									</a>
<?php
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
?>
								<ul class="pagination">
<?php
								if ($this->Paginator->first()) {
									echo $this->Paginator->first('First', array('tag' => 'li'), null, null);
								} else {
									echo '<li class="disabled"><a href="#">First</a></li>';
								}

								if ($this->Paginator->hasPrev()) {
									echo $this->Paginator->prev('&laquo;', array('tag' => 'li', 'escape' => false), null, null);
								} else {
									echo '<li class="disabled"><a href="#">&laquo;</a></li>';
								}

								echo $this->Paginator->numbers(array('separator' => false, 'tag' => 'li', 'currentTag' => 'a'));

								if ($this->Paginator->hasNext()) {
									echo $this->Paginator->next('&raquo;', array('tag' => 'li', 'escape' => false), null, null);
								} else {
									echo '<li class="disabled"><a href="#">&raquo;</a></li>';
								}

								if ($this->Paginator->last()) {
									echo $this->Paginator->last('Last', array('tag' => 'li'), null, null);
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
		<div class="form-group">
<?php
			if ($this->Access->check($groupName, 'UserTopupsAdd')) {
				echo $this->Html->link(
					'Add Topups',
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
				'Cancel',
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
