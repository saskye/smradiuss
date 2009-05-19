// FIXME
function storeLoadException(requestConfig, storeObj, xmlEr, except) {
	printStr = 'An exception occured while trying to load data.<br /><br /><br />';
	printStr += '<b>Exception:</b> '+except.toString()+'<br />';
	// add http status description
	printStr += '<b>Service:</b> '+storeObj.url+'<br />';
	printStr += '<b>Status:</b> '+xmlEr.status+' - '+xmlEr.statusText+'<br />';

	if (xmlEr.response && xmlEr.response.errors) { 
		// If this is a return object with errors, display them...
		printStr += '<b>Response:</b>';
		for (var i = 0; i < xmlEr.response.errors.length; i++) {
			printStr += xmlEr.response.errors[i] + '<br />';
		}

	} else {
		// add response text
		printStr += '<b>Response:</b><br />'+xmlEr.responseText;
	}

	Ext.Msg.show({
		title: "Exception occured: ",
		msg: printStr,
		icon: Ext.MessageBox.ERROR	
	});
}






// Generic jason store
Ext.ux.JsonStore = function(config) {
	config = Ext.apply({
		url: 'ajax.php',
		remoteSort: true
	}, config);
	
	var store = new Ext.data.JsonStore(config);
	
	Ext.data.JsonStore.superclass.constructor.call(this, config);
}
Ext.extend(Ext.ux.JsonStore, Ext.data.JsonStore, {
});




// Create a generic window and specify the window, form and submission ajax configuration
Ext.ux.GenericFormWindow = function(windowConfig,formConfig,submitAjaxConfig) {

	// Form configuration
	formConfig = Ext.apply({
		xtype: 'progressformpanel',
		id: 'formpanel',

		// AJAX connector
		url: 'ajax.php',

		// Space stuff a bit so it looks better
		bodyStyle: 'padding: 5px',

		// Default form item is text field
		defaultType: 'textfield',

		// Button uses formBind = true, this is undocumented
		// we may need to define an event to call  'clientvalidation'
		monitorValid: true,
		
		// Buttons for the form
		buttons: [
			{
				text: 'Save',
				formBind: true,
				handler: function() {
					var panel = this.ownerCt;
					var win = panel.ownerCt;

					var ajaxParams;

					if (submitAjaxConfig.params) {
						ajaxParams = submitAjaxConfig.params;

						if (submitAjaxConfig.hook) {
							var extraParams = submitAjaxConfig.hook();
						}

						ajaxParams = Ext.apply(ajaxParams,extraParams);

					} else {
						ajaxParams = submitAjaxConfig;
					}

					// Submit panel
					panel.submit({
						params: ajaxParams,
						// Close window on success
						success: function() {
							win.close();
						}
					});
				}
			},{
				text: 'Cancel',
				handler: function() {
					var panel = this.ownerCt;
					var win = panel.ownerCt;
					win.close();
				}
			}
		]
	}, formConfig);

	// Add any extra buttons we may have
	if (formConfig.extrabuttons) {
		// Loop and add
		for (i = 0; i < formConfig.extrabuttons.length; i += 1) {
			formConfig.buttons.push(formConfig.extrabuttons[i]);
		}
	}

	// Apply our own window configuration
	windowConfig = Ext.apply({
		layout: 'fit',
		items: [
			new Ext.ux.ProgressFormPanel(formConfig)
		]
	}, windowConfig);

	Ext.Window.superclass.constructor.call(this, windowConfig);
}

Ext.extend(Ext.ux.GenericFormWindow, Ext.Window, {
	// Override functions here
});




// Generic grid window
Ext.ux.GenericGridWindow = function(windowConfig,gridConfig,storeConfig,filtersConfig) {

	// Setup data store
	storeConfig = Ext.apply({
		autoLoad: false,
	}, storeConfig);
	var store = new Ext.ux.JsonStore(storeConfig);

	store.on('loadexception', storeLoadException);

	// Setup filters for the grid
	var filters = new Ext.grid.GridFilters(filtersConfig);

	// Grid configuration
	gridConfig = Ext.apply({
		xtype: 'gridpanel',
		id: 'gridpanel',
		
		plain: true,
		
		height: 300,

		// Link store
		store: store,
		
		// Plugins
		plugins: filters,
	
		// View configuration
		viewConfig: {
			forceFit: true
		},
	
		// Set row selection model
		selModel: new Ext.grid.RowSelectionModel({
			singleSelect: true
		}),
		
		// Inline buttons
		buttons: [
			{
				text:'Close',
				handler: function() {
					var grid = this.ownerCt;
					var win = grid.ownerCt;
					win.close();
				}
			}
		],
		buttonAlign:'center',
		
		// Bottom bar
		bbar: new Ext.PagingToolbar({
			pageSize: 25,
			store: store,
			displayInfo: true,
			displayMsg: 'Displaying items {0} - {1} of {2}',
			emptyMsg: "No data to display",
			plugins: filters
		})
	
	}, gridConfig);

	// Apply our own window configuration
	windowConfig = Ext.apply({
		items: [
			new Ext.grid.GridPanel(gridConfig)
		]
	}, windowConfig);

	// If we have additional items, push them onto the item list
	if (windowConfig.uxItems) {
		for (i = 0; i < windowConfig.uxItems.length; i += 1) {
			windowConfig.items.push(windowConfig.uxItems[i]);
		}
	}


	Ext.Window.superclass.constructor.call(this, windowConfig);
}


Ext.extend(Ext.ux.GenericGridWindow, Ext.Window, {
	// Override functions here
	show: function() {
		Ext.ux.GenericGridWindow.superclass.show.call(this,arguments);

		// Load initial records
		this.getComponent('gridpanel').store.load({
			params: {
				start: 0,
				limit: 25
			}
		});
	}
});




// Generic ajax request
uxAjaxRequest = function(parent,config) {
	config = Ext.apply({
		url: 'ajax.php',

		success: function(response,options) {
			if (parent) {
				parent.getEl().unmask();
			}			
		},

		// Failure function
		failure: function(response,options) {
			Ext.Msg.show({
				title: "Exception occured:",
				msg: response.responseText,
				icon: Ext.MessageBox.ERROR,
				fn: function() {
					if (parent) {
						parent.getEl().unmask();
					}			
				}
			});
		}
	}, config);

	Ext.Ajax.request(config);
}


