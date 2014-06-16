<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
	
	<div class="col-md-10"><legend><?php echo __('Edit User')?></legend>
		<?php echo $this->Form->create()?>			
			<div class="form-group">
				<?php echo $this->Form->label('Username', 'Username', array('class'=>'col-md-2 control-label'));?>								
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Username', array('label' => false, 'class' => 'form-control', 'placeholder' => 'Username', 'value' => $user['User']['Username']));?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->Form->label('Disabled', 'Disabled', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-3">
						<?php echo $this->Form->checkbox('Disabled', array( 'checked' => (($user['User']['Disabled'] == '1') ? true : false)));?>						
						<?php echo __('Disabled')?>					
					</div>
				</div>
			</div>
			
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><?php echo __('Save')?></button>
				<a class="btn btn-default" href="<?php echo BASE_URL; ?>/users/index" role="button"><?php echo __('Cancel')?></a>				
			</div>
		<?php echo $this->Form->end(); ?>
		
	<!-- 	<span class="glyphicon glyphicon-time" /> - Processing,
		<span class="glyphicon glyphicon-edit" /> - Override, 
		<span class="glyphicon glyphicon-import" /> - Being Added,
		<span class="glyphicon glyphicon-trash" /> - Being Removed,
		<span class="glyphicon glyphicon-random" /> - Conflicts-->
		</div>
	</div>
</div>


