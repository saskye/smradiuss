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

		<li>Users &amp; Groups
			<a title="Help on policies and groups" href="http://www.policyd.org/tiki-index.php?page=Policies%20%26%20Groups&structure=Documentation" class="help">
				<img src="images/help.gif" alt="Help" />
			</a>
			<ul>
				<li>Define policy groups made up of various combinations of tags.</li>
				<li>Define and manage policies comprising of ACL's which can include groups.</li>
			</ul>
		</li>
	</ul>
<?php

printFooter();

# vim: ts=4
?>
