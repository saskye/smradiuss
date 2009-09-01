/*
 * Ext JS Library 2.1
 * Copyright(c) 2006-2008, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */



Ext.onReady(function(){

	// Enable tips
	Ext.QuickTips.init();

	// Turn on validation errors beside the field globally
	Ext.form.Field.prototype.msgTarget = 'side';

	// Turn off the loading icon
	var hideMask = function () {
		Ext.get('loading').remove();
		Ext.fly('loading-mask').fadeOut({
			remove: true,
			callback: function() {
				// Fire everything up
				initViewport();
			}
		});
	}

	hideMask.defer(250);
});
