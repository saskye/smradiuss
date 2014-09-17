<?php
echo "
	<div class='form-group'>
";
echo
		$this->Form->label('Action', 'Action', array('class'=>'col-md-2 control-label'));
echo "
		<div class='row'>
			<div class='col-md-3'>
";
				for ($i=0;$i<count($userActions);$i++) {
					echo $this->Form->checkbox('Permission',
						array(
							'value' => $userActions[$i]['id'],
							'name' => 'permission[]',
							'checked' => (($userActions[$i]['checked'] == '1') ? true : false)
						)
					);
					echo __($userActions[$i]['value']);
				}
echo "
			</div>
		</div>
	</div>
";
?>
