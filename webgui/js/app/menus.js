/*
Webgui Menus
Copyright (C) 2007-2011, AllWorldIT

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

var radiusMenu = new Ext.menu.Menu({
	items: [
	
		{
			text: 'Users',
			iconCls: 'silk-user',
			handler: function() {
				showAdminUserWindow();
			}
		},

		{
			text: 'Groups',
			iconCls: 'silk-group',
			handler: function() {
				showAdminGroupWindow();
			}
		},

		{
			text: 'Realms',
			iconCls: 'silk-world',
			handler: function() {
				showAdminRealmWindow();
			}
		},
	
		{
			text: 'Clients',
			iconCls: 'silk-server',
			handler: function() {
				showAdminClientWindow();
			}
		}

	]
});


var wispMenu = new Ext.menu.Menu({
	items: [
	
		{
			text: 'Users',
			iconCls: 'silk-user',
			handler: function() {
				showWiSPUserWindow();
			}
		},

//		{
//			text: 'Resellers',
//			handler: function() {
//				showWiSPResellersWindow();
//			}
//		},

		{
			text: 'Locations',
			iconCls: 'silk-map',
			handler: function() {
				showWiSPLocationWindow();
			}
		}
	
	]
});

