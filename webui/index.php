<?php
# Main index file
# Copyright (C) 2008, LinuxRulz
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

include_once("includes/header.php");
include_once("includes/footer.php");


printHeader();

?>
	<p class="pageheader">Features Supported</p>
	<ul>
		<li>Users
			<ul>
				<li>Add, remove and edit users</li>
				<li>Add, remove and edit user attributes</li>
				<li>Add groups, remove groups and edit group attributes</li>
			</ul>
		</li>
		<li>Groups
			<ul>
				<li>Add and remove groups</li>
				<li>Add, remove and edit group attributes</li>
				<li>Assign users to groups</li>
			</ul>
		</li>
	</ul>
<?php

printFooter();

# vim: ts=4
?>
