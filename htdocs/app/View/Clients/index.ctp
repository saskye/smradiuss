<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
	
	<div class="col-md-10"><legend>Clients List</legend>
		<table class="table">
			<thead>
				<tr>
					<th><a><?php echo __('ID');?></a></th>
					<th><a><?php echo __('Name');?></a></th>				
					<th><a><?php echo __('AccessList');?></a></th>
					<th><a><?php echo __('Actions');?></a></th>
				</tr>
			</thead>
			<tbody>
				
				<?php foreach ($client as $client): ?>
				<tr>
					<td><? echo __($client['Client']['ID'])?></td>
					<td><? echo __($client['Client']['Name'])?></td>
					<td><? echo __($client['Client']['AccessList'])?></td>
					<td>
						<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/group_edit.png"></img>',array('action' => 'edit', $client['Client']['ID']), array('escape' => false, 'title' => 'Edit client'));?>												
						<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table_delete.png"></img>',array('controller' => 'clients','action' => 'remove', $client['Client']['ID']), array('escape' => false, 'title' => 'Remove client'), 'Are you sure you want to remove this client?');?>
						<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table.png"></img>',array('controller' => 'client_attributes', 'action' => 'index', $client['Client']['ID']), array('escape' => false, 'title' => 'Client Attributes'));?>
						<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/world.png"></img>',array('controller' => 'client_realms', 'action' => 'index', $client['Client']['ID']), array('escape' => false, 'title' => 'Client Realms'));?>
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
	 <!--	<span class="glyphicon glyphicon-time" /> - Processing,
		<span class="glyphicon glyphicon-edit" /> - Override, 
		<span class="glyphicon glyphicon-import" /> - Being Added,
		<span class="glyphicon glyphicon-trash" /> - Being Removed,
		<span class="glyphicon glyphicon-random" /> - Conflicts-->
		</div>
	</div>
</div>