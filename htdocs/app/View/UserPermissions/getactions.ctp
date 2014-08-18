<?php
echo "
	<div class='form-group'>
";
echo
		$this->Form->label('Disabled', 'Disabled', array('class'=>'col-md-2 control-label'));
echo "
		<div class='row'>
			<div class='col-md-3'>
";
				foreach ($controllerActions as $key => $value) {
					echo $this->Form->checkbox('Permission', array('value' => $key, 'name' => 'permission[]'));
					echo __($value);
				}
echo "
			</div>
		</div>
	</div>
";
?>
