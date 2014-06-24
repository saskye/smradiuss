<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('wisp_left_panel');?>

	<div class="col-md-10"><legend><?php echo __('Edit User Attribute')?></legend>
		<?php echo $this->Form->create()?>
			<!--<div class="form-group">
				<?php echo $this->Form->label('Name', 'Name', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Name', array('label' => false, 'class' => 'form-control',
										'placeholder' => 'Name', 'value' => $wispUsersAttribute['WispUsersAttribute']['Name']));?>
					</div>
				</div>
			</div>-->
			<div class="form-group">
				<?php $options = array('Traffic Limit' => 'Traffic Limit', 'Uptime Limit' => 'Uptime Limit', 'IP Address' => 'IP Address', 'MAC Address' => 'MAC Address');
				$operator=array('Add as reply if unique', 'Set configuration value', 'Match value in request', 'Add reply and set configuration', 'Inverse match value in request', 'Match less-than value in request', 'Match greater-than value in request', 'Match less-than or equal value in request', 'Match greater-than or equal value in request','Match string containing regex in request', 'Match string not containing regex in request', 'Match if attribute is defined in request', 'Match if attribute is not defined in request', 'Match any of these values in request');
				$modifier = array('Seconds' => 'Seconds', 'Minutes' => 'Minutes', 'Hours' => 'Hours', 'Days' => 'Days', 'Weeks' => 'Weeks', 'Months' => 'Months', 'MBytes' => 'MBytes', 'GBytes' => 'GBytes', 'TBytes' => 'TBytes'); ?>
				<?php echo $this->Form->label('Name', 'Name', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Name', array('label' => false, 'class' => 'form-control', 'type' => 'select', 'options' => $options, 'value' => $wispUsersAttribute['WispUsersAttribute']['Name']));?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->Form->label('Operator', 'Operator', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Operator', array('label' => false, 'class' => 'form-control',
							 'type' => 'select', 'options' => $operator, 'value' => $wispUsersAttribute['WispUsersAttribute']['Operator']));?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->Form->label('Value', 'Value', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Value', array('label' => false, 'class' => 'form-control',
								'placeholder' => 'Value', 'value' => $wispUsersAttribute['WispUsersAttribute']['Value']));?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->Form->label('Modifier', 'Modifier', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Modifier', array('label' => false, 'class' => 'form-control','type' => 'select', 'options' => $modifier, 'empty' => true));
						?>
</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->Form->label('Disabled', 'Disabled', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-3">
						<?php if($wispUsersAttribute['WispUsersAttribute']['Disabled'] == 1) {
								 $isCheck = true;
							} else {
								$isCheck = false;
							}
						?>
						<?php echo $this->Form->checkbox('Disabled', array('checked' => $isCheck));?>
						<?php echo __('Disabled')?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<button type="submit" class="btn btn-primary"><?php echo __('Save', true)?></button>
				<?php echo $this->Html->link(__('Cancel', true), array('action' => 'index', $wispUsersAttribute['WispUsersAttribute']['UserID']), array('class' => 'btn btn-default'))?>
			</div>
		<?php echo $this->Form->end(); ?>

	 	<span class="glyphicon glyphicon-time" /> - Processing,
		<span class="glyphicon glyphicon-edit" /> - Override,
		<span class="glyphicon glyphicon-import" /> - Being Added,
		<span class="glyphicon glyphicon-trash" /> - Being Removed,
		<span class="glyphicon glyphicon-random" /> - Conflicts
		</div>
	</div>
</div>


