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
									<li><a href="<?php echo BASE_URL;?>/users/index"><img
										src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/user.png"></img><? echo __('List User')?></a></li>
									<li><a href="<?php echo BASE_URL;?>/users/add"><img
										src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/user_add.png"></img><? echo __('Add User')?></a></li>		
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
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseGroups"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/group.png"></img><?=__('Groups')?></a>
					</p>
				</div>
				<div id="collapseGroups" class="panel-collapse collapse in">
					<div class="panel-body">
						<div class="col-md-12">
							<div class="form-group">
								<ul class="nav nav-pills nav-stacked">
									<li><a href="<?php echo BASE_URL;?>/groups/index"><img
										src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/group.png"></img><?=__('List Groups')?></a></li>
									<li><a href="<?php echo BASE_URL;?>/groups/add"><img
										src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/group_add.png"></img><?=__('Add Groups')?></a></li>																		
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
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#collapseWorlds"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/world.png"></img><?=__('Realms')?></a>
					</p>
				</div>
				<div id="collapseWorlds" class="panel-collapse collapse in">
					<div class="panel-body">
						<div class="col-md-12">
							<div class="form-group">
								<ul class="nav nav-pills nav-stacked">
									<li><a href="<?php echo BASE_URL;?>/realms/index"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/world.png"></img><?=__('List Realms')?></a></li>
									<li><a href="<?php echo BASE_URL;?>/realms/add"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/world_add.png"></img><?=__('Add Realms')?></a></li>																
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
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseServers"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/server.png"></img><?=__('Clients')?></a>
					</p>
				</div>
				<div id="collapseServers" class="panel-collapse collapse in">
					<div class="panel-body">
						<div class="col-md-12">
							<div class="form-group">
								<ul class="nav nav-pills nav-stacked">
									<li><a href="<?php echo BASE_URL;?>/clients/index"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/server_edit.png"></img><?=__('List Clients')?></a></li>
									<li><a href="<?php echo BASE_URL;?>/clients/add"><img src="<?php echo BASE_URL;?>/resources/custom/images/silk/icons/server_add.png"></img><?=__('Add Clients')?></a></li>																
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</ul>
</div>