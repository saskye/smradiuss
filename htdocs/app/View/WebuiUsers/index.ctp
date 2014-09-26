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
						if (
							$this->Access->check($groupName, 'WebuiUsersEdit') ||
							$this->Access->check($groupName, 'WebuiUsersDelete')
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
									<a href="#" onclick="return confirmDelete(
										'Are you sure you want to delete this user.',
										'<?php
											echo $this->Html->url(
												array(
													'controller' => 'webui_users',
													'action' => 'remove',
													$webuiUser['WebuiUser']['ID']
												)
											); ?>'
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
	</div>
</div>
