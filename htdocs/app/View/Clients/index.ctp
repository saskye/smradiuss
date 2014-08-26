<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Clients List'); ?></legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID'); ?></a></th>
						<th><a><?php echo __('Name'); ?></a></th>
						<th><a><?php echo __('AccessList'); ?></a></th>
<?php
						if ($this->Access->check($groupName, 'ClientsEdit') ||
								$this->Access->check($groupName, 'ClientsDelete') ||
								$this->Access->check($groupName, 'ClientAttributesView') ||
								$this->Access->check($groupName, 'ClientRealmsView')) {
?>
							<th><a><?php echo __('Actions'); ?></a></th>
<?php
						}
?>
					</tr>
				</thead>
				<tbody>
<?php
					foreach ($client as $client) {
?>
						<tr>
							<td><?php echo h($client['Client']['ID']); ?></td>
							<td><?php echo h($client['Client']['Name']); ?></td>
							<td><?php echo h($client['Client']['AccessList']); ?></td>
							<td>
<?php
								if ($this->Access->check($groupName, 'ClientsEdit')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'clients',
												'action' => 'edit',
												$client['Client']['ID']
											),
											"title" => "Edit client"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'ClientsDelete')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table_delete.png",
										array(
											"alt" => "Delete",
											"url" => array(
												'controller' => 'clients',
												'action' => 'remove',
												$client['Client']['ID']
											),
											"title" => "Remove client"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'ClientAttributesView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table.png",
										array(
											"alt" => "Client Attributes",
											"url" => array(
												'controller' => 'client_attributes',
												'action' => 'index',
												$client['Client']['ID']
											),
											"title" => "Client Attributes"
										)
									);
								}
?>
<?php
								if ($this->Access->check($groupName, 'ClientRealmsView')) {
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/world.png",
										array(
											"alt" => "Client Realms",
											"url" => array(
												'controller' => 'client_realms',
												'action' => 'index',
												$client['Client']['ID']
											),
											"title" => "Client Realms"
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
		</div>
	</div>
</div>
