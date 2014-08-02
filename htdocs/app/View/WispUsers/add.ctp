<style type="text/css">
body {
	padding-top: 50px;
}
#slides
{
	padding:30px 5px 5px 5px;border:1px #428BCA solid; border-top:2px #428BCA solid;margin-bottom:9px;
	-webkit-border-radius: 8px;
	-webkit-border-top-left-radius: 0;
	-moz-border-radius: 8px;
	-moz-border-radius-topleft: 0;
	border-radius: 8px;
	border-top-left-radius: 0;
}
ul#tabs
{
	list-style-type: none;
	margin: 30px 0 0 0;
	padding: 0 0 0.3em 0;
}
ul#tabs li
{
	display: inline;
	background-color: #428BCA;
	border: 1px solid #428BCA;
	padding: 0.4em;
	text-decoration: none;
	color: #000;
	-webkit-border-top-left-radius: 8px;
	-webkit-border-top-right-radius: 8px;
	-moz-border-radius-topleft: 8px;
	-moz-border-radius-topright: 8px;
	border-top-left-radius: 8px;
	border-top-right-radius: 8px;
}
ul#tabs li a
{
	color: #fff;
	text-decoration:none;
}
ul#tabs li:hover
{
	background-color: #72BBE0;
	padding: 0.4em;
}
div.tabContent
{
	padding: 0.5em;
	background-color: #428BCA;
}
div.tabContent.hide
{
	display: none;
}
#tabs li.selected
{
	background-color: #fff;
	color: #000;
	padding: 0.6em;
	border: 1px solid #428BCA;
	border-bottom:none;
}
#tabs li.selected:hover
{
	background-color: #fff;
	padding: 0.6em;
}
#tabs li.selected a
{
	color: #000;
	text-decoration:none;
}
</style>

<script>
$(document).ready(function() {
	//Set the initial state: highlight the first button...
	$('#tabs').find('li:eq(0)').addClass('selected');

	//and hide all slides except the first one
	$('#slides').find('> div:eq(0)').nextAll().hide();

	//actions that apply on click of any of the buttons
	$('#tabs li').click( function(event) {

	//turn off the link so it doesn't try to jump down the page
	event.preventDefault();

	//un-highlight the buttons
	$('#tabs li').removeClass();

	//hide all the slides
	$('#slides > div').hide();

	//highlight the current button
	$(this).addClass('selected');

	//get the index of the current button...
	var index = $('#tabs li').index(this);

	//and use that index to show the corresponding slide
	$('#slides > div:eq('+index+')').show();
});

$("#WispUserNumber").keyup(function() {
	if ($("#WispUserNumber").val().length > 0 ) {
		$("#WispUserUsername").removeAttr( "required" );
	} else {
		$("#WispUserUsername").attr( "required","required" );
	}
});

// For groups.
$("#btn").click(function() {
	$("#selectValid").css("display", "none");
	var groupText = $("#groups option:selected").text();
	var groupValue = $("#groups option:selected").val();

	if (groupText == 'Please select') {
		$("#selectValid").html("Please select group.");
		$("#selectValid").css("display", "block");
	} else {
		if ($('#grp'+groupValue).length > 0) {
			$("#selectValid").html("Already Added");
			$("#selectValid").css("display", "block");
			return false;
		}
		$("#selectGroup table").append("<tr id='grp"+groupValue+"'><td>"+groupText+"<input type='hidden' name='groupId[]' value='"+groupValue+"'></td><td align='right'><input type = 'button' value = 'Remove' onclick='deleteGroupRow("+groupValue+");' class='btn btn-primary'/></td></tr>");
		$('select option:contains("Please select")').prop('selected',true);
	}
	$("#groups").html(optionList).selectmenu('refresh', true);
});

// For attributs.
$("#attributeBtn").click(function() {
	var nameText = $("#nameId option:selected").text();
	var nameValue = $("#nameId option:selected").val();
	var operatorText = $("#operatorId option:selected").text();
	var operatorValue = $("#operatorId option:selected").val();
	var attributeValue = $("#valueId").val();
	var modifierText = $("#modifierId option:selected").text();

	if (modifierText == 'Please select') {
		modifierText = '';
	}
	var valid = 1;

	if (nameText == 'Please select') {
		$("#selectName").css("display", "block");
		valid = 0;
	} else {
		$("#selectName").css("display", "none");
	}

	if (operatorText == 'Please select') {
		$("#selectoperator").css("display", "block");
		valid = 0;
	} else {
		$("#selectoperator").css("display", "none");
	}

	if (attributeValue == '') {
		$("#selectvalue").css("display", "block");
		valid = 0;
	} else {
		$("#selectvalue").css("display", "none");
	}

	if (valid == 1) {
		attrTemp = parseInt($("#attribGenerator").val());
		var row = "<tr id='attrib"+attrTemp+"'><td>"+nameText+"<input type='hidden' name='attributeName[]' value='"+nameValue+"'></td><td>"+operatorText+"<input type='hidden' name='attributeoperator[]' value='"+operatorValue+"'></td><td>"+attributeValue+"<input type='hidden' name='attributeValues[]' value='"+attributeValue+"'></td><td>"+modifierText+"<input type='hidden' name='attributeModifier[]' value='"+modifierText+"'></td><td align='right'><input type = 'button' value = 'Remove' onclick='deleteAttributeRow("+attrTemp+");' class='btn btn-primary'/></td></tr>";
		$("#selectAttribute1").css("display","block");
		$("#selectAttribute1 table").append(row);
		$("#attribGenerator").val(attrTemp+1);
		$('select option:contains("Please select")').prop('selected',true);
		$("#valueId").val("");
	}
});
});

function deleteGroupRow(valData)
{
	$('#grp'+valData).remove();
}
function deleteAttributeRow(valData)
{
	$('#attrib'+valData).remove();
}
</script>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('wisp_left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Add Wisp User'); ?></legend>
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
								'placeholder' => 'Username',
								'type' => 'text'
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
								'class' => 'form-control',
								'placeholder' => 'Password',
								'type' => 'text'
							)
						);
?>
					</div>
				</div>
			</div>
			<!-- Starting of tabs. -->
			<div id="tabs1">
				<ul id="tabs">
					<li><a href="#pinfo"><?php echo __('Personal'); ?></a></li>
					<li><a href="#groups"><?php echo __('Groups'); ?></a></li>
					<li><a href="#attributes"><?php echo __('Attributes'); ?></a></li>
					<li><a href="#addmany"><?php echo __('Add Many'); ?></a></li>
				</ul>
			</div>
			<!-- Ending of tabs. -->
			<div id="slides">
				<!-- Starting of personal info div. -->
				<div id="pinfo">
					<div class="form-group">
<?php
						echo $this->Form->label(
							'FirstName',
							'First Name',
							array(
								'class' => 'col-md-2 control-label'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'FirstName',
									array(
										'label' => false,
										'class' => 'form-control',
										'placeholder' => 'First Name',
										'type' => 'text'
									)
								);
?>
							</div>
						</div>
					</div>
					<div class="form-group">
<?php
						echo $this->Form->label(
							'LastName',
							'LastName',
							array(
								'class' => 'col-md-2 control-label'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'LastName',
									array(
										'label' => false,
										'class' => 'form-control',
										'placeholder' => 'Last Name',
										'type' => 'text'
									)
								);
?>
							</div>
						</div>
					</div>
					<div class="form-group">
<?php
						echo $this->Form->label(
							'Phone',
							'Phone',
							array(
								'class' => 'col-md-2 control-label'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'Phone',
									array(
										'label' => false,
										'class' => 'form-control',
										'placeholder' => 'Phone',
										'type' => 'text'
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
										'type' => 'text'
									)
								);
?>
							</div>
						</div>
					</div>
					<div class="form-group">
<?php
						echo $this->Form->label(
							'Location',
							'Location',
							array(
								'class' => 'col-md-2 control-label'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'Location',
									array(
										'label' => false,
										'class' => 'form-control',
										'type' => 'select',
										'options' => $location,
										'empty' => true
									)
								);
?>
							</div>
						</div>
					</div>
				</div>
				<!-- Ending of personal info div. -->
				<!-- Starting of group div. -->
				<div id="groups" style="display:none;">
					<div class="form-group">
<?php
						echo $this->Form->label(
							'Group',
							'Group',
							array(
								'class' => 'col-md-2 control-label'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group" style="float:left;">
<?php
								echo $this->Form->input(
									'Type',
									array(
										'empty' => array(
											0 => 'Please select'
										),
										'label' => false,
										'class' => 'form-control',
										'type' => 'select',
										'options' => $groups,
										'id' => 'groups'
									)
								);
?>
								<span style="display:none;" id="selectValid"></span>
							</div>
							<div style="padding-left:600px;">
								<input type="button" value="Add Group" id="btn" class="btn btn-primary"/>
							</div>
						</div>
					</div>
					<div id='selectGroup'>
						<table class="table">
							<thead>
								<tr><th><a><?php echo __('Name'); ?></a></th></tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
				<!-- Ending of group div. -->
				<!-- Starting fo attributes div. -->
				<div id="attributes" style="display:none;">
<?php
						$options = array(
							'Traffic Limit' => __('Traffic Limit'),
							'Uptime Limit' => __('Uptime Limit'),
							'IP Address' => __('IP Address'),
							'MAC Address' => __('MAC Address')
						);
						$operator = array(
							__('Add as reply if unique'),
							__('Set configuration value'),
							__('Match value in request'),
							__('Add reply and set configuration'),
							__('Inverse match value in request'),
							__('Match less-than value in request'),
							__('Match greater-than value in request'),
							__('Match less-than or equal value in request'),
							__('Match greater-than or equal value in request'),
							__('Match string containing regex in request'),
							__('Match string not containing regex in request'),
							__('Match if attribute is defined in request'),
							__('Match if attribute is not defined in request'),
							__('Match any of these values in request')
						);
						$modifier = array(
							'Seconds' => __('Seconds'),
							'Minutes' => __('Minutes'),
							'Hours' => __('Hours'),
							'Days' => __('Days'),
							'Weeks' => __('Weeks'),
							'Months' => __('Months'),
							'MBytes' => __('MBytes'),
							'GBytes' => __('GBytes'),
							'TBytes' => __('TBytes')
						);
?>
					<div class="form-group" style="float:left;width:200px;">
<?php
						echo $this->Form->label(
							'Name',
							'Name',
							array(
								'class' => 'col-md-2 control-label',
								'style'=>'width:60px;'
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
										'type' => 'select',
										'options' => $options,
										'empty' => array(
											0 => 'Please select'
										),
										'id' => 'nameId',
										'style' => 'width:150px;'
									)
								);
?>
								<span style="display:none;" id="selectName"><?php echo __('Please select name')."."; ?></span>
							</div>
						</div>
					</div>
					<div class="form-group" style="float:left;width:250px;">
<?php
						echo $this->Form->label(
							'Operator',
							'Operator',
							array(
								'class' => 'col-md-2 control-label',
								'style' => 'margin-left:0px;width:80px;'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'Operator',
									array(
										'label' => false,
										'class' => 'form-control',
										'type' => 'select',
										'options' => $operator,
										'empty' => array(
											'' => 'Please select'
										),
										'style' => 'width:180px;',
										'id' => 'operatorId'
									)
								);
?>
								<span style="display:none;" id="selectoperator"><?php echo __('Please select operator')."."; ?></span>
							</div>
						</div>
					</div>
					<div class="form-group" style="float:left;width:250px;">
<?php
						echo $this->Form->label(
							'Value',
							'Value',
							array(
								'class' => 'col-md-2 control-label',
								'style' => 'width:60px;margin-left:0px;'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'Value',
									array(
										'label' => false,
										'class' => 'form-control',
										'placeholder' => 'Value',
										'style' => 'width:180px;',
										'id' => 'valueId'
									)
								);
?>
								<span style="display:none;" id="selectvalue"><?php echo __('Please enter value')."."; ?></span>
							</div>
						</div>
					</div>
					<div class="form-group" style="float:left;width:200px">
<?php
						echo $this->Form->label(
							'Modifier',
							'Modifier',
							array(
								'class' => 'col-md-2 control-label',
								'style' => 'width:80px;margin-left:0px;'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'Modifier',
									array(
										'label' => false,
										'class' => 'form-control',
										'type' => 'select',
										'options' => $modifier,
										'empty' => array(
											0 => 'Please select'
										),
										'style' => 'width:90px;margin-left:0px;',
										'id' => 'modifierId'
									)
								);
?>
								<span style="display:none;" id="selectmodifier"><?php echo __('Please select modifier')."."; ?></span>
							</div>
						</div>
					</div>
					<div style="padding-left:0px;">
						<input type="button" value="Add Group" id="attributeBtn" class="btn btn-primary"/>
					</div>
					<br><br><br>
					<div id='selectAttribute1'>
						<input type='hidden' id='attribGenerator' value='1'/>
						<table class="table">
							<thead>
								<tr>
									<th><a><?php echo __('Name'); ?></a></th>
									<th><a><?php echo __('Operator'); ?></a></th>
									<th><a><?php echo __('Value'); ?></a></th>
									<th><a><?php echo __('Modifier'); ?></a></th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
				<!-- Ending of attributes div. -->
				<!-- Starting of add many div. -->
				<div id="addmany" style="display:none;">
					<div class="form-group">
<?php
						echo $this->Form->label(
							'Prefix',
							'Prefix',
							array(
								'class' => 'col-md-2 control-label'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'Prefix',
									array(
										'label' => false,
										'class' => 'form-control',
										'placeholder' => 'Prefix',
										'type' => 'text'
									)
								);
?>
							</div>
						</div>
					</div>
					<div class="form-group">
<?php
						echo $this->Form->label(
							'Number',
							'Number',
							array(
								'class' => 'col-md-2 control-label'
							)
						);
?>
						<div class="row">
							<div class="col-md-4 input-group">
<?php
								echo $this->Form->input(
									'Number',
									array(
										'label' => false,
										'class' => 'form-control',
										'placeholder' => 'Number',
										'type' => 'text'
									)
								);
?>
							</div>
						</div>
					</div>
				</div>
				<!-- Ending of add many div. -->
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><?php echo __('Add'); ?></button>
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
