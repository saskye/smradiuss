<style type="text/css">
body {
	padding-top: 50px;
}
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/demos/style.css">
<script>
$(function() {
	var date = new Date(), y = date.getFullYear(), m = date.getMonth();
	var firstDay = new Date(y, m, 1);
	var lastDay = new Date(y, m + 1, 1);
	$('#datepickerFrom').datepicker({
		defaultDate:firstDay,
		dateFormat:'yy-mm-dd',
    	beforeShowDay: function (date) {
	      //getDate() returns the day (0-31)
    	   if (date.getDate() == 1) {
        	   return [true, ''];
	       }
    	   return [false, ''];
	    }
	});
	$('#datepickerTo').datepicker({
		minDate: lastDay,
		dateFormat:'yy-mm-dd',
		 beforeShowDay: function (date) {
			//getDate() returns the day (0-31)
			if (date.getDate() == 1) {
	           return [true, ''];
    	   }
	       return [false, ''];
	    }
	});
});
</script>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('wisp_left_panel');?>
		<div class="col-md-10"><legend><?php echo __('Add Wisp User Topup')?></legend>
			<?php echo $this->Form->create()?>
			<div class="form-group">
				<?php echo $this->Form->label('Type', 'Type', array('class'=>'col-md-2 control-label'));?>								
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Type', array('label' => false, 'class' => 'form-control', 'type' => 'select', 'empty' => true, 'options' => array('1'=>'Traffic', '2'=>'Uptime')));?>
					</div>					
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->Form->label('Value', 'Value', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('Value', array('label' => false, 'class' => 'form-control', 'placeholder' => 'Value', 'type' => 'text'));?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->Form->label('Valid From', 'Valid From', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-4 input-group">
					<?php echo $this->Form->input('valid_from', array('label' => false, 'class' => 'form-control', 'id' => 'datepickerFrom', 'readonly'=>'readonly','value' => date("Y-m-01")));?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->Form->label('Valid To', 'Valid To', array('class'=>'col-md-2 control-label'));?>
				<div class="row">
					<div class="col-md-4 input-group">
						<?php echo $this->Form->input('valid_to', array('label' => false, 'class' => 'form-control', 'id' => 'datepickerTo', 'readonly'=>'readonly','value' => date("Y-m-01", strtotime('+1 month'))));?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><?php echo __('Add')?></button>
				<?php echo $this->Html->link('Cancel', array('action' => 'index', $userId), array('class' => 'btn btn-default'))?>							
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>