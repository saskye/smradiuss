<style type="text/css">
body {
	padding-top: 50px;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
	$('#UserGroupType').change(function() {
		var group = ($("#UserGroupType option:selected").text());
		
		if(group == '')
     		$('#save').prop('disabled', true);
		else
			$('#save').prop('disabled', false);
	});  
});
</script>
<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
	
	<div class="col-md-10"><legend><?php echo __('Add User Group')?></legend>
		<?php echo $this->Form->create()?>
			<div class="form-group">
				<?php echo $this->Form->label('Group', 'Group', array('class'=>'col-md-2 control-label'));?>								
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Type', array('empty'=>true,'label' => false, 'class' => 'form-control', 'type' => 'select', "options" =>$arr));?>
					</div>					
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><?php echo __('Save')?></button>
				<?php echo $this->Html->link('Cancel', array('action' => 'index', $userId), array('class' => 'btn btn-default'))?>							
			</div>
		<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>