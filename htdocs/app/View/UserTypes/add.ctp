<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Add Type'); ?></legend>
			<?php echo $this->Form->create(); ?>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Type',
					'Type',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'alias',
							array(
								'type' => 'text',
								'label' => false,
								'class' => 'form-control',
								'placeholder' => 'Type'
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary" name="add_user_type"><?php echo __('Add'); ?></button>
<?php
				echo $this->Html->link(
					__('Cancel'),
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
