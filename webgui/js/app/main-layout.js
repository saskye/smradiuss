/*
Webgui Main Layout
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

// Main viewport
function initViewport() {

	// Create viewport
	var viewport = new Ext.Viewport({
		layout: 'border',
		items: [
			{
				// Top bar
				region:'north',
	
				height: 30,
				border: false,
	
				items: [
					{
						// Add toolbar
						xtype: 'toolbar',
						items: [
							{
								text: "Radius Control Panel",
								menu: radiusMenu
							},
							{
								text: "WiSP Control Panel",
								menu: wispMenu
							}
						]
					}
				]
			},
			// Main content
			{
				xtype: 'panel',
				region: 'center',
				autoScroll: true,
				border: true,
				margins:'5 5 5 5'
//				items: [
//					mainWindow
//				]
			},

			{
				id: 'main-statusbar',
				xtype: 'panel',
				region: 'south',
				border: true,
				height: 30,
    				bbar: new Ext.ux.StatusBar({
					id: 'my-status',

					// defaults to use when the status is cleared:
					defaultText: 'Default status text',
					defaultIconCls: 'default-icon',

					// values to set initially:
					text: 'Ready',
					iconCls: 'ready-icon'

					// any standard Toolbar items:
//					items: [{
//						text: 'A Button'
//					}, '-', 'Plain Text']
				})
			}
/*
			{
				region: 'east',
				html: '<img src="resources/custom/images/smarty_icon.gif"></img>',
				margins:'5 5 5 5',
				border: true
			}
*/
		]	
	});
}




