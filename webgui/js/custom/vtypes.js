// Add the additional 'advanced' VTypes
Ext.apply(Ext.form.VTypes, {


	'number': function(v) {
		var numberMask = /^[0-9]+$/;
		return numberMask.test(v);
	},
	'numberText': "This field must contain a number",


	'domain': function(v) {
		var domainMask = /^[a-z0-9]+[a-z0-9\-]*(\.[a-z0-9\-]+)+$/;
		return domainMask.test(v);
	},
	'domainText': "This is not a valid domain",


	'emailLocalPart': function(v) {
		var emailLocalPartMask = /^[a-z0-9\._\+&]+$/;
		return emailLocalPartMask.test(v);
	},
	'emailLocalPartText': "This is not a valid local part",


	'usernamePart': function(v) {
		var usernamePartMask = /^[a-z0-9\._]+$/;
		return usernamePartMask.test(v);
	},
	'usernamePartText': "This is not a valid username",


	'emailAddress': function(v) {
		var emailAddressMask = /^[a-z0-9\._\-\+&]+@[a-z0-9]+[a-z0-9\-]*(\.[a-z0-9\-]+)+$/;
		return emailAddressMask.test(v);
	},
	'emailAddressPartText': "This is not a valid email address",


	'daterange': function(val, field) {
		var date = field.parseDate(val);

		if(!date){
			return;
		}
		// Set maximum value for date, cant be less than minimum value
		if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
			var start = Ext.getCmp(field.startDateField);
			start.setMaxValue(date);
			start.validate();
			this.dateRangeMax = date;
		// Set minimum value for date, cant be more than maximum value 
		} else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
			var end = Ext.getCmp(field.endDateField);
			end.setMinValue(date);
			end.validate();
			this.dateRangeMin = date;
		}
		/*
		 * Always return true since we're only using this vtype to set the
		 * min/max allowed values (these are tested for after the vtype test)
		 */
		return true;
	}

});

