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
	

	'username': function(v) {
		var usernameMask = /^[a-z0-9\._\-]+$/;
		return usernameMask.test(v);
	},
	'usernameText': "This is not a valid username",


	'usernameRadius': function(v) {
		var usernameRadiusMask = /^[a-z0-9\._\-@]+$/;
		return usernameRadiusMask.test(v);
	},
	'usernameRadiusText': "This is not a valid username",


	'emailAddress': function(v) {
		var emailAddressMask = /^[a-z0-9\._\-\+&]+@[a-z0-9]+[a-z0-9\-]*(\.[a-z0-9\-]+)+$/;
		return emailAddressMask.test(v);
	},
	'emailAddressPartText': "This is not a valid email address",

	// Date Vtypes 1 
	/*'daterange': function(val, field) {
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
		}*/
		
	// Date Vtype 2
	// New vtype
	'daterange': function(val, field) {
		var date = field.parseDate(val);
		
		var dispUpd = function(picker) {
			var ad = picker.activeDate;
			picker.activeDate = null;
			picker.update(ad);
		};

		if (field.startDateField){
			var sd = Ext.getCmp(field.startDateField);
			sd.maxValue = date;
			if (sd.menu && sd.menu.picker){
				sd.menu.picker.maxDate = date;
				dispUpd(sd.menu.picker);
			}
		}else if (field.endDateField){
			var ed = Ext.getCmp(field.endDateField);
			ed.minValue = date;
			if (ed.menu && ed.menu.picker){
				ed.menu.picker.minDate = date;
				dispUpd(ed.menu.picker);
			}
		}
		
	// Date Vtype 3
	/*'daterange': function(val, field) {
       		 var date = field.parseDate(val), otherFieldProp, dateProp, other;
        
       		 if (field.startDateField) {
           		 otherFieldProp = 'startDateField';
           		 dateProp = 'max';
       		 }
       		 else if (field.endDateField) {
           		 otherFieldProp = 'endDateField';
           		 dateProp = 'min';
       		 }
        
       		 if (otherFieldProp && field[otherFieldProp]) {
           		 other = field[otherFieldProp];
           		 if (typeof other == 'string'){
               			 other = field[otherFieldProp] = field.ownerCt.getComponent(other) || Ext.getCmp(other);
           		 }
           		 if (!other[dateProp + 'OldValue'] || other[dateProp + 'OldValue'] != (date?date.getTime():undefined)) {
               			 other[dateProp + 'Value'] = date;
               			 if (other.menu && other.menu.picker) {
                   			 other.menu.picker[dateProp + 'Date'] = date;
                   			 other.menu.picker.update(other.menu.picker.activeDate, true);
               			 }
               			 other[dateProp + 'OldValue'] = date ? date.getTime() : undefined;
           		 }
       		 }*/


		/*
		 * Always return true since we're only using this vtype to set the
		 * min/max allowed values (these are tested for after the vtype test)
		 */
		return true;
	}

})

