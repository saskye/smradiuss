<style type="text/css">
body {
	padding-top: 50px;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
		<div class="col-md-10"><legend>Edit Realm</legend>
			<?php echo $this->Form->create()?>
				<div class="form-group">
					<?php
						echo $this->Form->label(
							'Realm',
							'Realm',
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
										'placeholder' => 'Realm',
										'value' => $realm['Realm']['Name']
									)
								);
							?>
						</div>
					</div>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary"><?php echo __('Save')?></button>
					<?php
						echo $this->Html->link(
							'Cancel',
							array(
								'controller' => 'realms',
								'action' => 'index'
							),
							array(
								'class' => 'btn btn-default'
							)
						)
					?>
				</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>
