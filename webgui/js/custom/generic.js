function storeException(requestConfig, storeObj, xmlEr, except) {

	if (isset(xmlEr.response) && isset(xmlEr.response.errors)) { 
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
		title: "Data Load Exception: ",
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

	var panelID = Ext.id();
	var windowID = Ext.id();


	// Override button text?
	var submitButtonText = 'Save';
	if (submitAjaxConfig && submitAjaxConfig.submitButtonText) {
		submitButtonText = submitAjaxConfig.submitButtonText;
	}


	// Form configuration
	formConfig = Ext.apply({
		xtype: 'progressformpanel',
		id: panelID,

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
				text: submitButtonText,
				formBind: true,
				handler: function() {
					var panel = Ext.getCmp(panelID);
					var win = Ext.getCmp(windowID);

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
						success: function(form,action) {
							// Check if we have a custom function to execute on success
							if (submitAjaxConfig.onSuccess) {
								submitAjaxConfig.onSuccess(form,action);
							}
							win.close();
						}
					});
				}
			},{
				text: 'Cancel',
				handler: function() {
					var win = Ext.getCmp(windowID);
					win.close();
				}
			}
		],
		// Align buttons
		buttonAlign: 'center'
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
		id: windowID,
		layout: 'fit',
		items: [
			new Ext.ux.ProgressFormPanel(formConfig,submitAjaxConfig)
		]
	}, windowConfig);

	// Set grid panel ID
	this.formPanelID = panelID;

	Ext.Window.superclass.constructor.call(this, windowConfig);
}

Ext.extend(Ext.ux.GenericFormWindow, Ext.Window, {
	// Override functions here
});




// Generic grid window
Ext.ux.GenericGridWindow = function(windowConfig,gridConfig,storeConfig,filtersConfig) {
	var panelID = Ext.id();
	var windowID = Ext.id();

	// Setup data store
	storeConfig = Ext.apply({
		autoLoad: false
	}, storeConfig);
	var store = new Ext.ux.JsonStore(storeConfig);

	store.on('exception', storeException);

	// Setup filters for the grid
	var filters = new Ext.ux.grid.GridFilters(filtersConfig);

	// Setup paging toolbar
	var pagingToolbar =  new Ext.PagingToolbar({
			pageSize: 25,
			store: store,
			displayInfo: true,
			plugins: filters
	});

	// Grid configuration
	gridConfig = Ext.apply({
		xtype: 'gridpanel',
		id: panelID,
		
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
					var win = Ext.getCmp(windowID);
					win.close();
				}
			}
		],
		buttonAlign: 'center',
		
		// Bottom bar
		bbar: [
			pagingToolbar
		]
	
	}, gridConfig);

	// Store handling
	store.on('beforeload', function() { var win = Ext.getCmp(windowID); win.getEl().mask("Loading..."); } );
	store.on('load', function() { var win = Ext.getCmp(windowID); win.getEl().unmask(); } );
	store.on('exception', function(thisobj, action, rs, params) { 
			var win = Ext.getCmp(windowID); win.getEl().unmask();
			storeException(thisobj,action,rs,params);
	});

	// Apply our own window configuration
	windowConfig = Ext.apply({
		id: windowID,
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

	// Set grid panel ID
	this.gridPanelID = panelID;

	Ext.Window.superclass.constructor.call(this, windowConfig);
}


Ext.extend(Ext.ux.GenericGridWindow, Ext.Window, {
	// Override functions here
	show: function() {
		Ext.ux.GenericGridWindow.superclass.show.call(this,arguments);

		// Load initial records
		Ext.getCmp(this.gridPanelID).store.load({
			params: {
				start: 0,
				limit: 25
			}
		});
	}
});



function getJsonAccessor(expr) {
    var re = /[\[\.]/;
    return function(expr) {
        try {
            return(re.test(expr)) ?
            new Function("obj", "return obj." + expr) :
            function(obj){
                return obj[expr];
            };
        } catch(e){}
        return Ext.emptyFn;
    };
};

// Generic ajax request
uxAjaxRequest = function(theWindow,config) {

	var requestConfig = Ext.apply({
		url: 'ajax.php',

		// Success function
		success: function(response,options) {
			var result;

			// Try decode response, if we can't throw exception
			try {
				result = Ext.decode(response.responseText);
			} catch(e) {
				if (options.failure) {
					options.failure.call(options.scope, response, options);
				}
				if (options.callback) {
					options.callback.call(options.scope, options, false, response);
				}
				return;
			}

			// Check success property is defined
			if (!isset(result.success)) {
				if (options.failure) {
					options.failure.call(options.scope, response, options);
				}
				if (options.callback) {
					options.callback.call(options.scope, options, false, response);
				}
				return;
			}

			// Check if the request actually succeeded
			if (result.success == false) {
				var myResponse;

				// We should have a data attribute
				if (!isset(result.data)) {
					myResponse = response;

				// Make sure we have the error
				} else if (!isset(result.data.ErrorCode) || !isset(result.data.ErrorReason)) {
					myResponse = response;

				// We have everything we need
				} else {
					myResponse = result.data;
				}

				if (options.failure) {
					options.failure.call(options.scope, myResponse, options);
				}
				if (options.callback) {
					options.callback.call(options.scope, options, false, myResponse);
				}
				return;
			}

			// if we have a custom success function, run it
			if (config.customSuccess) {
				config.customSuccess(response,options);
			}
			// Lastly unmask theWindow window if we have one
			if (theWindow) {
				theWindow.getEl().unmask();
			}			
		},

		// Failure function
		failure: function(response,options) {
			var title;
			var msg;

			// Check what kind of error we got 
			if (isset(response.responseText)) {
				// Connection related
				title = "Connection error: ";
				msg = response.responseText;

			// Backend related
			} else if (isset(response.ErrorCode) && isset(response.ErrorReason)) {
				title = "Error: ";
				msg = response.ErrorReason+"<br/>code:"+response.ErrorCode;
			} else {
				title = "Unknown Error";
				msg = "Unknown Error";
			}

			Ext.Msg.show({
				title: title,
				msg: msg,
				icon: Ext.MessageBox.ERROR,
				fn: function() {
					if (theWindow) {
						theWindow.getEl().unmask();
					}			
				}
			});
		}
	}, config);

	Ext.Ajax.request(requestConfig);
}


// vim: ts=4
