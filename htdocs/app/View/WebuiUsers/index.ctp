<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<script type="text/javascript">
function confirmDelete(msg, link)
{
	yes = confirm(msg);
	if (yes) {
		location.href = link;
	} else {
		return false;
	}
}
</script>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Webui User List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
						<th><a><?php echo __('Email'); ?></a></th>
						<th><a><?php echo __('Disabled'); ?></a></th>
						<th><a><?php echo __('Type'); ?></a></th>
<?php
						if ($this->Access->check($groupName, 'WebuiUsersEdit') ||
								$this->Access->check($groupName, 'WebuiUsersDelete')) {
?>
							<th><a><?php echo __('Action'); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($webuiUsers as $webuiUser) {
?>
						<tr>
							<td><?php echo h($webuiUser['WebuiUser']['ID']); ?></td>
							<td><?php echo h($webuiUser['WebuiUser']['Name']); ?></td>
							<td><?php echo h($webuiUser['WebuiUser']['Email']); ?></td>
							<td><?php echo (h($webuiUser['WebuiUser']['Disabled']) == 1) ? 'true' : 'false'; ?></td>
							<td><?php echo (h($webuiUser['WebuiUser']['Type']) == 0) ? 'Null' :
									$types[h($webuiUser['WebuiUser']['Type'])]  ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'WebuiUsersEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/user_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'webui_users',
												'action' => 'edit',
												$webuiUser['WebuiUser']['ID']
											),
											"title" => "Edit webui iser"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'WebuiUsersDelete')) {
?>
									<a href="#"
										onclick="return confirmDelete(
											'Are you sure you want to delete this user.',
											'<?php
												echo $this->Html->url(
													array(
														'controller' => 'webui_users',
														'action' => 'remove',
														$webuiUser['WebuiUser']['ID']
													)
												);
											?>'
										)">
<?php
										echo $this->Html->image(
											"/resources/custom/images/silk/icons/user_delete.png",
											array(
												"alt" => "Delete",
												"title" => "Remove webui user"
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
