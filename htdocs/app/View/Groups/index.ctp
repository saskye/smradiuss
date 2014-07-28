<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
		<div class="col-md-10"><legend>Groups List</legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID');?></a></th>
						<th><a><?php echo __('Name');?></a></th>
						<th><a><?php echo __('Priority');?></a></th>
						<th><a><?php echo __('Disabled');?></a></th>
						<th><a><?php echo __('Comment');?></a></th>
						<th><a><?php echo __('Actions');?></a></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($groups as $group): ?>
						<tr>
							<td><? echo $group['Group']['ID'];?></td>
							<td><? echo h($group['Group']['Name']);?></td>
							<td><? echo $group['Group']['Priority'];?></td>
							<td><? echo ($group['Group']['Disabled'] == 1) ? 'true' : 'false';?></td>
							<td><? echo h($group['Group']['Comment']);?></td>
							<td>
								<?php
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group_edit.png",
										array(
											"alt" => "Edit",
											"url" => array(
												'controller' => 'groups',
												'action' => 'edit',
												$group['Group']['ID']
											),
											"title" => "Edit group"
										)
									);
								?>
								<?php
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group_delete.png",
										array(
											"alt" => "Delete",
											"url" => array(
												'controller' => 'groups',
												'action' => 'remove',
												$group['Group']['ID']
											),
											"title" => "Remove group"
										)
									);
								?>
								<?php
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/table.png",
										array(
											"alt" => "Group attributes",
											"url" => array(
												'controller' => 'group_attributes',
												'action' => 'index',
												$group['Group']['ID']
											),
											"title" => "Group attributes"
										)
									);
								?>
								<?php
									echo $this->Html->image(
										"/resources/custom/images/silk/icons/group.png",
										array(
											"alt" => "Group member",
											"url" => array(
												'controller' => 'group_member',
												'action' => 'index',
												$group['Group']['ID']
											),
											"title" => "Group member"
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
