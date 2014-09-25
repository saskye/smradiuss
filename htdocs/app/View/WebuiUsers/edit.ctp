<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Edit Webui User'); ?></legend>
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
								'placeholder' => 'Name',
								'value' => $webuiUser['WebuiUser']['Name']
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
								'placeholder' => 'Username',
								'value' => $webuiUser['WebuiUser']['Username']
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Old Password',
					'Old Password',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'OldPassword',
							array(
								'label' => false,
								'type' => 'password',
								'class' => 'form-control',
								'placeholder' => 'Old Password'
							)
						);

?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'New Password',
					'New Password',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'NewPassword',
							array(
								'label' => false,
								'type' => 'password',
								'class' => 'form-control',
								'placeholder' => 'New Password'
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
								'placeholder' => 'Email',
								'value' => $webuiUser['WebuiUser']['Email']
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
								'options' => $types,
								'value' => $webuiUser['WebuiUser']['Type']
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
						if($webuiUser['WebuiUser']['Disabled'] == 1) {
							$isCheck = true;
						} else {
							$isCheck = false;
						}
?>
<?php
						echo $this->Form->checkbox('Disabled', array('checked' => $isCheck));
						echo __('Disabled');
?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary" name="edit_webui_user"><?php echo __('Edit'); ?></button>
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
