<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
		<div class="col-md-10"><legend>Group Members List</legend>
			<table class="table">
				<thead>
					<tr>
						<th><?php echo $this->Paginator->sort('ID', 'ID'); ?></th>
						<th><?php echo $this->Paginator->sort('Username', 'Username'); ?></th>
						<th><?php echo $this->Paginator->sort('Disabled', 'Disabled'); ?></th>
						<th><a><?php echo __('Actions', true);?></a></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($GroupMember as $GroupMember): ?>
						<tr>
							<td><? echo $GroupMember['GroupMember']['ID'];?></td>
							<td><? echo $GroupMember['GroupMember']['UserName'];?></td>
							<td><? echo ($GroupMember['GroupMember']['Disabled'] == 1) ? 'true' : 'false';?></td>
							<td>												
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table_delete.png"></img>',array('controller' => 'group_member','action' => 'remove', $GroupMember['GroupMember']['ID'], $groupID), array('escape' => false, 'title' => 'Remove Group'), 'Are you sure you want to remove this group member?');?>
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