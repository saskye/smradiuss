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
	color: #ffffff;
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
$(document).ready(function()
{
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

$("#WispUserNumber").keyup(function()
{
	if($("#WispUserNumber").val().length > 0 )
	{
		$("#WispUserUsername").removeAttr( "required" );
	}
	else
	{
		$("#WispUserUsername").attr( "required","required" );
	}
});

/* -- for groups -- */
$("#btn").click(function()
{
	$("#selectValid").css("display", "none");
	var groupText = $("#groups option:selected").text();
	var groupValue = $("#groups option:selected").val();
	if(groupText == 'Please Select')
	{
		$("#selectValid").html("Please select group.");
		$("#selectValid").css("display", "block");
	}
	else
	{
		if ($('#grp'+groupValue).length > 0)
		{
			$("#selectValid").html("Already Added");
			$("#selectValid").css("display", "block");
			return false;
		}
		var userGroups = "<tr id='grp"+groupValue+"'>" +
				"<td>"+groupText+"<input type='hidden' name='groupId[]' value='"+groupValue+"'></td>" +
				"<td align='right'>" +
				"<input type='button' value='Remove' onclick='deleteGroupRow("+groupValue+");' class='btn btn-primary'/>" +
				"</td></tr>";
		$("#selectGroup table").append(userGroups);

		$('select option:contains("Please Select")').prop('selected',true);
		$('#groups select').prop('disabled', true);
}
	$("#groups").html(optionList).selectmenu('refresh', true);
});

/* -- for attributs -- */
$("#attributeBtn").click(function()
{
	var nameText = $("#nameId option:selected").text();
	var nameValue = $("#nameId option:selected").val();
	var operatorText = $("#operatorId option:selected").text();
	var operatorValue = $("#operatorId option:selected").val();
	var attributeValue = $("#valueId").val();
	var modifierText = $("#modifierId option:selected").text();
	if(modifierText == 'Please Select')
	{
		modifierText = '';
	}
	var valid = 1;
	if(nameText == 'Please Select')
	{
		$("#selectName").css("display", "block");
		valid = 0;
	}
	else
	{
		$("#selectName").css("display", "none");
	}
	if(operatorText == 'Please Select')
	{
		$("#selectoperator").css("display", "block");
		valid = 0;
	}
	else
	{
		$("#selectoperator").css("display", "none");
	}
	if(attributeValue == '')
	{
		$("#selectvalue").css("display", "block");
		valid = 0;
	}
	else
	{
		$("#selectvalue").css("display", "none");
	}
	if(valid == 1)
	{
		if($(this).val()=='Update Group')
		{
			attrTemp = $("#editCheck").val();
			var row = "<tr id='attrib"+attrTemp+"'><td>"+nameText+"<input type='hidden' name='attributeName[]' value='"+nameValue+"' id='attributeName"+attrTemp+"'></td><td>"+operatorText+"<input type='hidden' name='attributeoperator[]' id='attributeoperator"+attrTemp+"' value='"+operatorValue+"'></td><td>"+attributeValue+"<input type='hidden' name='attributeValues[]' id='attributeValues"+attrTemp+"' value='"+attributeValue+"'></td><td>"+modifierText+"<input type='hidden' id='attributeModifier"+attrTemp+"' name='attributeModifier[]' value='"+modifierText+"'></td><td align='right'><input type = 'button' value = 'Edit' onclick='editAttributeRow("+attrTemp+");' class='btn btn-primary'/> <input type = 'button' value = 'Remove' onclick='deleteAttributeRow("+attrTemp+");' class='btn btn-primary'/></td></tr>";
			$("#attrib"+attrTemp).replaceWith(row);
			$(this).val('Add Group');
			$("#nameId").val(0);
			$("#operatorId").val('');
			$("#valueId").val('');
		}
		else
		{
			attrTemp = parseInt($("#attribGenerator").val());
			var row = "<tr id='attrib"+attrTemp+"'><td>"+nameText+"<input type='hidden' name='attributeName[]' id='attributeName"+attrTemp+"' value='"+nameValue+"'></td><td>"+operatorText+"<input type='hidden' name='attributeoperator[]' id='attributeoperator"+attrTemp+"' value='"+operatorValue+"'></td><td>"+attributeValue+"<input type='hidden' name='attributeValues[]' id='attributeValues"+attrTemp+"' value='"+attributeValue+"'></td><td>"+modifierText+"<input type='hidden' id='attributeModifier"+attrTemp+"' name='attributeModifier[]' value='"+modifierText+"'></td><td align='right'><input type = 'button' value = 'Edit' onclick='editAttributeRow("+attrTemp+");' class='btn btn-primary'/> <input type = 'button' value = 'Remove' onclick='deleteAttributeRow("+attrTemp+");' class='btn btn-primary'/></td></tr>";
			$("#selectAttribute1").css("display","block");
			$("#selectAttribute1 table").append(row);
			$("#attribGenerator").val(attrTemp+1);
			$('select option:contains("Please Select")').prop('selected',true);
			$("#valueId").val("");
		}
	}
});
});
function deleteGroupRow(valData)
{
	$('#grp'+valData).remove();
	$("select").removeAttr('disabled');
}
function deleteAttributeRow(valData)
{
	$('#attrib'+valData).remove();
}
function editAttributeRow(valData)
{
	$('#attrib'+$("#editCheck").val()).css("background-color","#fff");
	$('#attrib'+valData).css("background-color","#F4F4F4");
	$("#editCheck").val(valData);
	$("#attributeBtn").val('Update Group');
	$("#nameId").val($('#attributeName'+valData).val());
	$("#operatorId").val($('#attributeoperator'+valData).val());
	$("#valueId").val($('#attributeValues'+valData).val());
	$("#modifierId").val($('#attributeModifier'+valData).val());
}
</script>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('wisp_left_panel'); ?>
		<div class="col-md-10"><legend><?php echo __('Edit Wisp User'); ?></legend>
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
								'value' => $user['WispUser']['Username']
							)
						);
?>
						<input type='hidden' name='hiddenUserName'
									value='<?php echo h($user['WispUser']['Username']); ?>'/>
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
								'value' => $user['WispUser']['Password']
							)
						);
?>
					</div>
				</div>
			</div>
			<!-- Sataring of tabs. -->
			<div id="tabs1">
				<ul id="tabs">
					<li><a href="#pinfo"><?php echo __('Personal'); ?></a></li>
					<li><a href="#groups"><?php echo __('Groups'); ?></a></li>
					<li><a href="#attributes"><?php echo __('Attributes'); ?></a></li>
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
										'value' => $user['WispUser']['FirstName']
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
										'value' => $user['WispUser']['LastName']
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
										'type' => 'text',
										'value' => $user['WispUser']['Phone']
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
										'value' => $user['WispUser']['Email']
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
										'empty' => false,
										'options' => $location,
										'value' => $user['WispUser']['LocationID'],
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
								if (isset($userGroups)) {
									$disabled = 'disabled';
								} else {
									$disabled = 'FALSE';
								}
								echo $this->Form->input(
									'Type',
									array(
										'empty' => array(
											0 => __('Please Select')
										),
										'label' => false,
										'class' => 'form-control',
										'type' => 'select',
										'options' => $groups,
										'id' => 'groups',
										'disabled' => $disabled
									)
								);
?>
								<span style="display:none;" id="selectValid"></span>
							</div>
							<div style="padding-left:600px;">
								<input type="button" value="<?php echo __('Add Group'); ?>" id="btn"
									class="btn btn-primary" name="add_group"/>
							</div>
						</div>
					</div>
					<div id='selectGroup'>
						<table class="table">
							<thead>
								<tr><th><a><?php echo __('Name'); ?></a></th></tr>
							</thead>
							<tbody>
<?php
								if (isset($userGroups)) {
									foreach($userGroups as $userGroup) {
?>
										<tr id='grp<?php echo h($userGroup['GroupMember']['GroupID']); ?>'>
											<td>
<?php
												echo h($userGroup['Group']['name']);
?>
												<input type='hidden' name='groupId[]'
														value='<?php echo h($userGroup['GroupMember']['GroupID']); ?>'>
											</td>
											<td align='right'>
											<input type='button' value='Remove'
													onclick='deleteGroupRow(
<?php
														echo h($userGroup['GroupMember']['GroupID']);
?>
													);'	class='btn btn-primary'/>
											</td>
										</tr>
<?php
									}
								}
?>
							</tbody>
						</table>
					</div>
				</div>
				<!-- Ending of group div. -->
				<!-- Starting of attributes div. -->
				<div id="attributes" style="display:none;">
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
											0 => __('Please Select')
										),
										'id' => 'nameId',
										'style' => 'width:150px;'
									)
								);
?>
								<span style="display:none;" id="selectName">
<?php
									echo __('Please select name.');
?>
								</span>
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
											'' => __('Please Select')
										),
										'style' => 'width:180px;',
										'id' => 'operatorId'
									)
								);
?>
								<span style="display:none;" id="selectoperator">
<?php
									echo __('Please select operator.');
?>
								</span>
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
								<span style="display:none;" id="selectvalue">
<?php
									echo __('Please enter value.');
?>
								</span>
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
											0 => __('Please Select')
										),
										'style' => 'width:90px;margin-left:0px;',
										'id' => 'modifierId'
									)
								);
?>
								<span style="display:none;" id="selectmodifier">
<?php
									echo __('Please select modifier.');
?>
								</span>
							</div>
						</div>
					</div>
					<div style="padding-left:0px;">
						<input type="button" value="<?php echo __('Add Attribute'); ?>" id="attributeBtn"
								class="btn btn-primary" name="add_attribute"/>
					</div>
					<br><br><br>
					<div id='selectAttribute1' style="">
						<input type='hidden' id='editCheck' value='0' />
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
<?php
								$i = 0;
								foreach ($userAttributes as $userAttribute) {
									$i++;
									if ($userAttribute['UserAttribute']['Name']=='User-Password') {
										continue;
									}
?>
									<tr id='attrib<?php echo $i; ?>'>
										<td>
<?php
											echo h($userAttribute['UserAttribute']['Name']);
?>
											<input type='hidden' name='attributeName[]'
													id='attributeName<?php echo $i; ?>'
													value='<?php echo h($userAttribute['UserAttribute']['Name']); ?>'>
										</td>
										<td>
<?php
											echo h($operators[
													$userAttribute['UserAttribute']['Operator']
											]);
?>
											<input type='hidden' name='attributeoperator[]' id='attributeoperator<?php echo $i; ?>'
													value='<?php
														echo h($userAttribute['UserAttribute']['Operator']);
													?>'>
										</td>
										<td>
<?php
											$attrValue = reverseSwitchModifier(
													$userAttribute['UserAttribute']['modifier'],
													$userAttribute['UserAttribute']['Value']);
											echo h($attrValue);
?>
											<input type='hidden' name='attributeValues[]' value='<?php echo $attrValue; ?>'
													id='attributeValues<?php echo $i; ?>' >
										</td>
										<td>
<?php
											echo h($userAttribute['UserAttribute']['modifier']);
?>
											<input type='hidden' name='attributeModifier[]'id='attributeModifier<?php echo $i; ?>'
													value='<?php echo h($userAttribute['UserAttribute']['modifier']); ?>'>
										</td>
										<td align='right'>
											<input type='button' value='<?php echo __("Edit"); ?>' name='edit_attribute'
													onclick='editAttributeRow(<?php echo $i; ?>);' class='btn btn-primary'/>
											<input type='button' value='<?php echo __("Remove"): ?>' name='delete_attribute'
													onclick='deleteAttributeRow(<?php echo $i; ?>);' class='btn btn-primary'/>
										</td>
									</tr>
<?php
								}
?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- Ending of attributes div. -->
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary"><?php echo __('Update'); ?></button>
<?php
					echo $this->Html->link(
						'Cancel',
						array(
							'action' => 'index'
						),
						array(
							'class' => 'btn btn-default'
						)
					)
?>
				</div>
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>
<?php
	/**
	 * @method reverceSwitchModifier
	 * This method is used for calculate attribute value according val.
	 */
	function reverseSwitchModifier($val,$attrValues)
	{
		$av = '';

		switch ($val)
		{
			case "Seconds":
				$av = $attrValues * 60;
				break;
			case "Minutes":
				$av = $attrValues;
				break;
			case "Hours":
				$av = $attrValues / 60;
				break;
			case "Days":
				$av = $attrValues / 1440;
				break;
			case "Weeks":
				$av = $attrValues / 10080;
				break;
			case "Months":
				$av = $attrValues / 44640;
				break;
			case "MBytes":
				$av = $attrValues;
				break;
			case "GBytes":
				$av = $attrValues / 1000;
				break;
			case "TBytes":
				$av = $attrValues / 1000000;
				break;
		}
		return $av;
	}
?>
