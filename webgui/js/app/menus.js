/*
Webgui Menus
Copyright (C) 2007-2009, AllWorldIT

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
			handler: function() {
				showAdminUserWindow();
			}
		},

		{
			text: 'Groups',
			handler: function() {
				showAdminGroupWindow();
			}
		},

		{
			text: 'Realms',
			handler: function() {
				showAdminRealmWindow();
			}
		}
	
	]
});


var wispMenu = new Ext.menu.Menu({
	items: [
	
		{
			text: 'Users',
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
			handler: function() {
				showWiSPLocationWindow();
			}
		}
	
	]
});

