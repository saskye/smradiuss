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
							<td><? echo $group['Group']['Name'];?></td>
							<td><? echo $group['Group']['Priority'];?></td>
							<td><? echo ($group['Group']['Disabled'] == 1) ? 'true' : 'false';?></td>
							<td><? echo $group['Group']['Comment'];?></td>
							<td>
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/group_edit.png"></img>',array('action' => 'edit', $group['Group']['ID']), array('escape' => false, 'title' => 'Edit group'));?>
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/group_delete.png"></img>',array('action' => 'remove', $group['Group']['ID']), array('escape' => false, 'title' => 'Remove group'), 'Are you sure you want to remove this group?');?>
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table.png"></img>',array('controller' => 'group_attributes', 'action' => 'index', $group['Group']['ID']), array('escape' => false, 'title' => 'Group attributes'));?>
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/group.png"></img>',array('controller' => 'group_member', 'action' => 'index', $group['Group']['ID']), array('escape' => false, 'title' => 'Group member'));?>
							</td>
						</tr>
					<? endforeach; ?>
				<tr>
					<td align="center" colspan="10" >
						<?php
						$total = $this->Paginator->counter(array(
    						'format' => '%pages%'));
						if($total >1)
						{		
							echo $this->Paginator->prev('<<', null, null, array('class' => 'disabled'));
						?>
						<?php echo $this->Paginator->numbers(); ?>
						<!-- Shows the next and previous links -->
						<?php echo $this->Paginator->next('>>', null, null, array('class' => 'disabled')); ?>
						<!-- prints X of Y, where X is current page and Y is number of pages -->
						<?php
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