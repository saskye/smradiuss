<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Edit Group'); ?></legend>
			<?php echo $this->Form->create(); ?>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Name',
					'Name',
					array(
						'class' => 'col-md-2 control-label'
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
								'type' => 'text',
								'class' => 'form-control',
								'placeholder' => 'Name',
								'value' => $group['Group']['Name']
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Priority',
					'Priority',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'Priority',
							array(
								'label' => false,
								'type' => 'text',
								'class' => 'form-control',
								'placeholder' => 'Priority',
								'value' => $group['Group']['Priority']
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Comment',
					'Comment',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'Comment',
							array(
								'label' => false,
								'type' => 'text',
								'class' => 'form-control',
								'placeholder' => 'Comment',
								'value' => $group['Group']['Comment']
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
						echo $this->Form->checkbox(
							'Disabled',
							array(
								'checked' => (($group['Group']['Disabled'] == '1') ? true : false)
							)
						);
						echo __('Disabled');
?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary" name="edit_group"><?php echo __('Save'); ?></button>
<?php
				echo $this->Html->link(
					'Cancel',
					array(
						'action' => 'index'
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
