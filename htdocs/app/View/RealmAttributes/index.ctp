<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
		<div class="col-md-10"><legend>Realm Attribute List</legend>
			<table class="table">
				<thead>
					<tr>
						<th><a><?php echo __('ID', true);?></a></th>
						<th><a><?php echo __('Name', true);?></a></th>
						<th><a><?php echo __('Operator', true);?></a></th>
						<th><a><?php echo __('Value', true);?></a></th>
						<th><a><?php echo __('Disabled', true);?></a></th>					
						<th><a><?php echo __('Actions', true);?></a></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$options=array('=', ':=', '==', '+=', '!=', '<', '>', '<=', '>=','=~', '!~', '=*', '!*', '||==');
					foreach ($realmAttributes as $realmAttributes): ?>
					<tr>
						<td><? echo $realmAttributes['RealmAttribute']['ID'];?></td>
						<td><? echo $realmAttributes['RealmAttribute']['Name'];?></td>
						<td><? echo $options[$realmAttributes['RealmAttribute']['Operator']];?></td>
						<td><? echo $realmAttributes['RealmAttribute']['Value'];?></td>
						<td><? echo ($realmAttributes['RealmAttribute']['Disabled'] == 1) ? 'true' : 'false';?></td>					
						<td>
							<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table_edit.png"></img>',array('controller' => 'realm_attributes',  'action' => 'edit', $realmAttributes['RealmAttribute']['ID'], $realmId), array('escape' => false, 'title' => 'Edit attribute'));?>												
							<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table_delete.png"></img>',array('controller' => 'realm_attributes','action' => 'remove', $realmAttributes['RealmAttribute']['ID'], $realmId), array('escape' => false, 'title' => 'Remove attribute'), 'Are you sure you want to remove this attribute?');?>
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
			<div class="form-group">			
				<?php echo $this->Html->link(__('Add'), array('action' => 'add', $realmId), array('class' => 'btn btn-primary'))?>			
				<?php echo $this->Html->link(__('Cancel'), array('controller' => 'realms', 'action' => 'index'), array('class' => 'btn btn-default'))?>
			</div>
		</div>
	</div>
</div>