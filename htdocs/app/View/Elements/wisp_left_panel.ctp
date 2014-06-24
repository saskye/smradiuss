<div class="col-md-2">
	<ul class="nav nav-pills nav-stacked">
		<div class="panel-group" id="accordion">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<p class="panel-title text-center">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseUsers"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/user.png"></img><? echo __('Users')?></a>
					</p>
				</div>
				<div id="collapseUsers" class="panel-collapse collapse in">
					<div class="panel-body">
						<div class="col-md-12">
							<div class="form-group">
								<ul class="nav nav-pills nav-stacked">
									<li><a href="<?php echo BASE_URL;?>/wispUsers/index"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/user.png"></img> <? echo __('List User')?></a></li>
									<li><a href="<?php echo BASE_URL;?>/wispUsers/add"><img
										src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/user_add.png"></img> <? echo __('Add User')?></a></li>		
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<div class="panel-group">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<p class="panel-title text-center">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseGroups"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/map.png"></img> <?=__('Locations')?></a>
					</p>
				</div>
				<div id="collapseGroups" class="panel-collapse collapse in">
					<div class="panel-body">
						<div class="col-md-12">
							<div class="form-group">
								<ul class="nav nav-pills nav-stacked">
									<li><a href="<?php echo BASE_URL;?>/WispLocations/index"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/map.png"></img> <?=__('List Locations')?></a></li>
									<li><a href="<?php echo BASE_URL;?>/WispLocations/add"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/map_add.png"></img> <?=__('Add Locations')?></a></li>																		
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</ul>
</div>