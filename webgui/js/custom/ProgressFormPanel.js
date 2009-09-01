

Ext.ux.ProgressFormPanel = function(config,submitAjaxConfig) {


	var submitMsg = 'Saving...';

	// Check for submit message override
	if (submitAjaxConfig && submitAjaxConfig.submitMsg) {
		submitMsg = 'Logging in...';
	}


	config = Ext.apply({
		bodyStyle: 'border:0px;',
		autoHeight: true,
		loadMsg: 'Loading...',
		submitMsg: submitMsg
	}, config);
  
	Ext.ux.ProgressFormPanel.superclass.constructor.call(this, config);

	// Create event handlers
	this.on({
		// Display failure message
    		actionfailed: { scope:this, fn:function(form, action){
			var hideMe = 0;

			// We should hide the loadmask here
			if ((action.type == 'load' || action.type == 'submit') && this.rendered == true) {
				this.getLoadMask().hide();
			}

			// If we loading hide the window
			if (action.type == 'load') {
				hideMe = 1;
			}

			printStr = '<b>Error: </b>';

			// Check if we have result.msg
			if (action.result && action.result.msg) {
				printStr += action.result.msg;

			// Check if we have result.errors
			} else if (action.result && action.result.errors) {
				// Check if we just have a error code
				if (action.result.data && action.result.data.ErrorCode) {
					printStr += "Code: "+action.result.data.ErrorCode+"<br />";
					printStr += "Reason: "+action.result.data.ErrorReason+"<br />";
				// Add all errors	
				} else {
					for (var i = 0; i < action.result.errors.length; i++) {
						printStr += action.result.errors[i] + '<br />';
					}
				}

			// Check if we just have a result
			} else if (
				isset(action.result) &&
				isset(action.result.data) && 
				isset(action.result.data.ErrorCode) &&
				isset(action.result.data.ErrorReason)
			) {
				printStr = action.result.data.ErrorReason+"<br/>code:"+action.result.data.ErrorCode;

			// Check if we have an action response
			} else if (action.response) {
				printStr += action.response.responseText;

			// Unknown
			} else {
				printStr += 'UNKNOWN ERROR: '+action.failureType;
			}

			// Display error
			Ext.Msg.show({
				title: "Data Load Exception: ",
				msg: printStr,
				icon: Ext.MessageBox.ERROR,
				modal: true,
				buttons: Ext.Msg.CANCEL,
				scope: this,
				fn: function() {
					// Check if we must hide this	
					if (hideMe) {
						this.ownerCt.hide();
					}	
				}
			});
	
		}},

    		// Before action, fire up mask
		beforeaction: { scope:this, fn:function(form, action){
			if((action.type == 'load'  || action.type == 'submit') && this.rendered == true){
				this.getLoadMask().show();
			}
		}},

		// Completed action, hide mask
		actioncomplete: { scope:this, fn:function(form, action){
			if((action.type == 'load' || action.type == 'submit') && this.rendered == true){
				this.getLoadMask().hide();
			}
		}}
	});
}

Ext.reg('progressformpanel',Ext.ux.ProgressFormPanel);

Ext.extend(Ext.ux.ProgressFormPanel, Ext.FormPanel, {
	// Setup the loadmask messages
	load: function(options){
		this.getLoadMask().msg = this.loadMsg;
		Ext.ux.ProgressFormPanel.superclass.load.call(this,options);
	},
	submit: function(options){
		this.getLoadMask().msg = this.submitMsg;
//		Ext.ux.ProgressFormPanel.superclass.submit.call(this,options);
		this.getForm().doAction('submit',options);
	},

	// Get load mask
	getLoadMask: function() {
		// If we don't have a load mask ,create one
		if(!this.loadmask){
			this.loadmask = new Ext.LoadMask(this.ownerCt.getEl());
		}
		return this.loadmask;
	}
});



