<style type="text/css">
body {
	padding-top: 50px;
}
</style>
<script type="text/javascript">
$(document).on("change" , "#controllerns" , function() {
	var controllerId = $('#controllerns option:selected').val();

	$.ajax({
		type: "POST",
		url: '<?php echo BASE_URL; ?>/user_permissions/getactions/'+controllerId,
		success: function(data) {
			$("#actions").html(data);
		},
		error : function(xhr, status, error) {
			alert(xhr.responseText);
		}
	});
});
</script>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel'); ?>
		<div class="col-md-10"><legend><? echo __('Add User Permission'); ?></legend>
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
							'aro_id',
							array(
								'empty' => __('Please select type'),
								'label' => false,
								'class' => 'form-control',
								'type' => 'select',
								'options' => $allTypes
							)
						);
?>
					</div>
				</div>
			</div>
			<div class="form-group">
<?php
				echo $this->Form->label(
					'Controller',
					'Controller',
					array(
						'class' => 'col-md-2 control-label'
					)
				);
?>
				<div class="row">
					<div class="col-md-4 input-group">
<?php
						echo $this->Form->input(
							'aco_id',
							array(
								'empty' => __('Please select controller'),
								'label' => false,
								'class' => 'form-control',
								'type' => 'select',
								'options' => $controllers,
								'id' => 'controllerns'
							)
						);
?>
					</div>
				</div>
			</div>
			<div id="actions"></div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><?php echo __('Add'); ?></button>
<?php
				echo $this->Html->link(
					__('Cancel'),
					array(
						'controller' => 'user_permissions',
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
