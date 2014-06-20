<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
		<div class="col-md-10"><legend>Realms List</legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID');?></a></th>
						<th><a><?php echo __('Name');?></a></th>				
						<th><a><?php echo __('Disabled');?></a></th>
						<th><a><?php echo __('Actions');?></a></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($realm as $realm): ?>
						<tr>
							<td><? echo $realm['Realm']['ID'];?></td>
							<td><? echo $realm['Realm']['Name'];?></td>
							<td><? echo ($realm['Realm']['Disabled'] == 1) ? 'true' : 'false';?></td>
							<td>
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/group_edit.png"></img>',array('action' => 'edit', $realm['Realm']['ID']), array('escape' => false, 'title' => 'Edit realm'));?>												
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table_delete.png"></img>',array('controller' => 'realms','action' => 'remove', $realm['Realm']['ID']), array('escape' => false, 'title' => 'Remove realm'), 'Are you sure you want to remove this realm?');?>
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table.png"></img>',array('controller' => 'realm_attributes', 'action' => 'index', $realm['Realm']['ID']), array('escape' => false, 'title' => 'Realm Attributes'));?>
								<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/group.png"></img>',array('controller' => 'realm_members', 'action' => 'index', $realm['Realm']['ID']), array('escape' => false, 'title' => 'Realm Member'));?>
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