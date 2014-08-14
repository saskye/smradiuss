<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px;">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Login'); ?></legend>
			<?php echo $this->Form->create(); ?>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Username',
					'Username',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'Username',
							array(
								'label' => false,
								'class' => 'form-control',
								'placeholder' => 'Username'
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Password',
					'Password',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'Password',
							array(
								'type' => 'password',
								'label' => false,
								'class' => 'form-control',
								'placeholder' => 'Password'
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><?php echo __('Add'); ?></button>
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>
