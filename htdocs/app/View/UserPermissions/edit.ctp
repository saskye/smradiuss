<style type="text/css">
body {
	padding-top: 50px;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
	var typeId = $('#typeId').val();
	var controllerId = $('#controllerns').val();
	var aroId = <?php echo $permissionData['Aro']['id']; ?>;
	var acoId = <?php echo $permissionData['Aco']['parent_id']; ?>;
	$.ajax({
		type: "GET",
		url: '<?php echo BASE_URL; ?>/user_permissions/editActions/'+aroId+'/'+acoId,
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
		<div class="col-md-10"><legend><? echo __('Edit User Permission'); ?></legend>
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
						echo h($permissionData['Aro']['alias']);
						echo $this->Form->input(
							'aro_id',
							array(
								'label' => false,
								'class' => 'form-control',
								'type' => 'hidden',
								'id' => 'typeId',
								'value' => $aroId
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
						echo h($permissionData['Aco']['model']);
						echo $this->Form->input(
							'aco_id',
							array(
								'label' => false,
								'class' => 'form-control',
								'type' => 'type',
								'id' => 'controllerns',
								'value' => $acoId
							)
						);
?>
					</div>
				</div>
			</div>
			<div id="actions"></div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary" name="edit"><?php echo __('Edit'); ?></button>
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
