<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Add Webui User'); ?></legend>
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
								'class' => 'form-control',
								'placeholder' => 'Name'
							)
						);
?>
					</div>
				</div>
			</div>
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
								'label' => false,
								'type' => 'password',
								'class' => 'form-control',
								'placeholder' => 'Password'
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Email',
					'Email',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'Email',
							array(
								'label' => false,
								'class' => 'form-control',
								'placeholder' => 'Email'
							)
						);
?>
					</div>
				</div>
			</div>
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
							'Type', array(
								'label' => false,
								'class' => 'form-control',
								'type' => 'select',
								'empty' => __('Please select'),
								'options' => $types
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
						echo $this->Form->checkbox('Disabled');
						echo __('Disabled');
?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary" name="add_webui_user"><?php echo __('Add'); ?></button>
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
