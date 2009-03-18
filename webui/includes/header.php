<?php

include_once("includes/config.php");



# Print out HTML header
function printHeader($params = NULL)
{
	global $DB_POSTFIX_DSN;


    # Pull in params
    if (!is_null($params)) {
		if (isset($params['Tabs'])) {
			$tabs = $params['Tabs'];
		}
		if (isset($params['js.onLoad'])) {
			$jsOnLoad = $params['js.onLoad'];
		}
		if (isset($params['Title'])) {
			$title = $params['Title'];
		}
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

    <head>
	<title>Policyd Web Administration</title>
	<link rel="stylesheet" type="text/css" href="stylesheet.css" />
	
	<script type="text/javascript" src="tooltips/BubbleTooltips.js"></script>
	<script type="text/javascript">
		window.onload=function(){enableTooltips(null,"img")};
	</script>
    </head>


	<body<?php if (!empty($jsOnLoad)) { echo " onLoad=\"".$jsOnLoad."\""; } ?>>


	<table id="maintable">
		<tr>
			<td id="header">SMRadiusd Web Administration</td>
		</tr>

		<tr>
			<td>
				<table>
					<tr>
						<td id="menu">
	    					<img style="margin-top:-1px; margin-left:-1px;" src="images/top2.jpg" alt="" />
	    					<p><a href=".">Home</a></p>

							<p>Control Panel</p>
							<ul>
								<li><a href="policy-main.php">Users</a></li>
								<li><a href="policy-group-main.php">Groups</a></li>
							</ul>

	    					<img style="margin-left:-1px; margin-bottom: -6px" src="images/specs_bottom.jpg" alt="" />
						</td>

						<td class="content">
							<table class="content">
<?php
								# Check if we must display tabs or not
								if (!empty($tabs)) {
?>
									<tr><td id="topmenu"><ul>
<?php
										foreach ($tabs as $key => $value) {
?>											<li>
												<a href="<?php echo $value ?>" 
													title="<?php echo $key ?>">
												<span><?php echo $key ?></span></a>
											</li>
<?php
										}
?>
								    	</ul></td></tr>
<?php
								}	
?>
								<tr>
									<td>
<?php
}


# vim: ts=4
?>
