<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Edit Group Attribute'); ?></legend>
			<?php echo $this->Form->create(); ?>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Name',
					'Name',
					array(
						'class'=>'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'Name',
							array(
								'label' => false,
								'class' => 'form-control',
								'placeholder' => 'Name',
								'value' => $groupAttribute['GroupAttribute']['Name']
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Operator',
					'Operator',
					array(
						'class'=>'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'Operator',
							array(
								'label' => false,
								'class' => 'form-control',
								'type' => 'select',
								'options' => $operators,
								'value' => $groupAttribute['GroupAttribute']['Operator']
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Value',
					'Value',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'Value',
							array(
								'label' => false,
								'class' => 'form-control',
								'placeholder' => 'Value',
								'value' => $groupAttribute['GroupAttribute']['Value']
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Disabled',
					'Disabled',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-3">
<?php
						if ($groupAttribute['GroupAttribute']['Disabled'] == 1) {
							$isCheck = true;
						} else {
							$isCheck = false;
						}

						echo $this->Form->checkbox('Disabled', array('checked' => $isCheck));
						echo __('Disabled');
?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary" name="edit_group_attribute"><?php echo __('Save'); ?></button>
<?php
				echo $this->Html->link(
					'Cancel',
					array(
						'action' => 'index',
						$groupAttribute['GroupAttribute']['GroupID']
					),
					array(
						'class' => 'btn btn-default'
					)
				);
?>
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>
