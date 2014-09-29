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
		<div class="col-md-10"><legend><?php echo __('Realms List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
						<th><a><?php echo __('Disabled'); ?></a></th>
<?php
						if (
							$this->Access->check($groupName, 'RealmsEdit') ||
							$this->Access->check($groupName, 'RealmsDelete') ||
							$this->Access->check($groupName, 'RealmAttributesView') ||
							$this->Access->check($groupName, 'RealmMembersView')
						) {
?>
							<th><a><?php echo __('Actions'); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($realm as $realm) {
?>
						<tr>
							<td><?php echo h($realm['Realm']['ID']); ?></td>
							<td><?php echo h($realm['Realm']['Name']); ?></td>
							<td><?php echo (h($realm['Realm']['Disabled']) == 1) ? 'true' : 'false'; ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'RealmsEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'realms',
												'action' => 'edit',
												$realm['Realm']['ID']
											),
											"title" => "Edit realm"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'RealmsDelete')) {
?>
									<a href="#" onclick="return confirmDelete(
										'Are you sure you want to delete.',
										'<?php
											echo $this->Html->url(
												array(
													'controller' => 'realms',
													'action' => 'remove',
													$realm['Realm']['ID']
												)
											); ?>'
										)">
<?php
										echo $this->Html->image(
											"/resources/custom/images/silk/icons/table_delete.png",
											array(
												"alt" => "Delete",
												"title" => "Delete realm"
											)
										);
?>
									</a>
<?php
								}
?>
<?php
								if ($this->Access->check($groupName, 'RealmAttributesView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table.png",
										array(
											"alt" => "Realm Attributes",
											"url" => array(
												'controller' => 'realm_attributes',
												'action' => 'index',
												$realm['Realm']['ID']
											),
											"title" => "Realm Attributes"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'RealmMembersView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group.png",
										array(
											"alt" => "Realm Member",
											"url" => array(
												'controller' => 'realm_members',
												'action' => 'index',
												$realm['Realm']['ID']
											),
											"title" => "Realm Member"
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
