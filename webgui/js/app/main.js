/*
 * Ext JS Library 2.1
 * Copyright(c) 2006-2008, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */



Ext.onReady(function(){
	// Turn off the loading icon
	document.getElementById('loading').style.visibility = 'hidden';

// this seems to save window states
//    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	// Enable tips
	Ext.QuickTips.init();

	// Turn on validation errors beside the field globally
	Ext.form.Field.prototype.msgTarget = 'side';

	// Range menu items, used on GridFilter
        Ext.menu.RangeMenu.prototype.icons = {
		gt: 'resources/extjs/images/greater_then.png',
		lt: 'resources/extjs/images/less_then.png',
		eq: 'resources/extjs/images/equals.png'
        };
        Ext.grid.filter.StringFilter.prototype.icon = 'resources/extjs/images/find.png';


	// Fire everything up
	initViewport();

});
