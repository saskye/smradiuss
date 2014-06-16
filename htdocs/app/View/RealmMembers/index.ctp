<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
	
	<div class="col-md-10"><legend>Realm Members List</legend>
		<table class="table">
			<thead>
				<tr>
					<th><a><?php echo __('ID', true);?></a></th>
					<th><a><?php echo __('Name', true);?></a></th>
					<th><a><?php echo __('Action', true);?></a></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($realmMember as $realmMember): ?>
				<tr>
					<td><? echo __($realmMember['RealmMember']['ID'])?></td>
					<td><? echo __($realmMember['RealmMember']['clientName'])?></td>
					<td>											
						<?php echo $this->Html->link('<img src="'.BASE_URL.'/resources/custom/images/silk/icons/table_delete.png"></img>',array('controller' => 'realm_members','action' => 'remove', $realmMember['RealmMember']['ID'], $realmID), array('escape' => false, 'title' => 'Remove member'), 'Are you sure you want to remove this member?');?>
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
	 	<!--<span class="glyphicon glyphicon-time" /> - Processing,
		<span class="glyphicon glyphicon-edit" /> - Override, 
		<span class="glyphicon glyphicon-import" /> - Being Added,
		<span class="glyphicon glyphicon-trash" /> - Being Removed,
		<span class="glyphicon glyphicon-random" /> - Conflicts-->
		</div>
	</div>
</div>