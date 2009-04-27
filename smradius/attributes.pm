# Attribute handling functions
# Copyright (C) 2007-2009, AllWorldIT
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


## @class smradius::attributes
# Attribute functions
package smradius::attributes;

use strict;
use warnings;

# Exporter stuff
require Exporter;
our (@ISA,@EXPORT);
@ISA = qw(Exporter);
@EXPORT = qw(
	addAttribute
	checkAttributeAuth
	getReplyAttribute
	checkAttributeConfig
);


use smradius::logging;
use smradius::util;


# Attributes we do not handle
my @attributeIgnoreList = (
	'User-Password'
);


## @fn addAttribute($server,$attributes,$attribute)
# Function to add an attribute to $attributes
#
# @param server Server instance
# @param attributes Hashref of attributes we already have and / or must add to
# @param attribute Attribute to add, eg. Those from a database
sub addAttribute
{
	my ($server,$attributes,$attribute) = @_;

	# Check if this is an array 
	if ($attribute->{'Operator'} =~ s/^\|\|//) {
		# Check if we've seen this before
		if (defined($attributes->{$attribute->{'Name'}}->{$attribute->{'Operator'}}) && 
				ref($attributes->{$attribute->{'Name'}}->{$attribute->{'Operator'}}->{'Value'}) eq "ARRAY" ) {
			# Then add value to end of array
			push(@{$attributes->{$attribute->{'Name'}}->{$attribute->{'Operator'}}->{'Value'}}, $attribute->{'Value'});

		# If we have not seen it before, initialize it	
		} else {
			# Assign attribute
			$attributes->{$attribute->{'Name'}}->{$attribute->{'Operator'}} = $attribute;
			# Override type ... else we must create a custom attribute hash, this is dirty, but faster
			$attributes->{$attribute->{'Name'}}->{$attribute->{'Operator'}}->{'Value'} = [ $attribute->{'Value'} ];
		}

	# If its not an array, just add it normally
	} else {
		$attributes->{$attribute->{'Name'}}->{$attribute->{'Operator'}} = $attribute;
	}
}



## @fn checkAttributeAuth($server,$packetAttributes,$attribute)
# Function to check an attribute in the authorization stage
#
# @param server Server instance
# @param packetAttributes Hashref of attributes provided, eg. Those from the packet
# @param attribute Attribute to check, eg. One of the ones from the database
sub checkAttributeAuth
{
	my ($server,$packetAttributes,$attribute) = @_;


	# Check ignore list
	foreach my $ignoredAttr (@attributeIgnoreList) {
		# 2 = IGNORE, so return IGNORE for all ignored items
		return 2 if ($attribute->{'Name'} eq $ignoredAttr);
	}

	# Matched & ok?
	my $matched = 0;

	# Figure out our attr values
	my @attrValues;
	if (ref($attribute->{'Value'}) eq "ARRAY") {
		@attrValues = @{$attribute->{'Value'}};
	} else {
		@attrValues = ( $attribute->{'Value'} );
	}	

	# Get packet attribute value
	my $attrVal = $packetAttributes->{$attribute->{'Name'}};

	$server->log(LOG_DEBUG,"[ATTRIBUTES] Processing CHECK attribute value ".niceUndef($attrVal)." against: '".
			$attribute->{'Name'}."' ".$attribute->{'Operator'}." '".join("','",@attrValues)."'");
	
	# Loop with all the test attribute values
	foreach my $tattrVal (@attrValues) { 	
		# Operator: ==
		#
		# Use: Attribute == Value
		# As a check item, it matches if the named attribute is present in the request,
		# AND has the given value.
		#
		if ($attribute->{'Operator'} eq '==' ) {
			# Check for correct value
			if (defined($attrVal) && $attrVal eq $tattrVal) {
				$matched = 1;
			}
	
		# Operator: >
		#
		# Use: Attribute > Value
		# As a check item, it matches if the request contains an attribute
		# with a value greater than the one given.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '>') {
			if (defined($attrVal) && $attrVal =~ /^[0-9]+$/) {
				# Check for correct value
				if ($attrVal > $tattrVal) {
					$matched = 1;
				}
			} else {
				$server->log(LOG_WARN,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' is NOT a number!");
			}
	
		# Operator: <
		#
		# Use: Attribute < Value
		# As a check item, it matches if the request contains an attribute
		# with a value less than the one given.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '<') {
			# Check for correct value
			if (defined($attrVal) && $attrVal < $tattrVal) {
				$matched = 1;
			}
	
		# Operator: <=
		#
		# Use: Attribute <= Value
		# As a check item, it matches if the request contains an attribute
		# with a value less than, or equal to the one given.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '<=') {
			# Check for correct value
			if (defined($attrVal) && $attrVal <= $tattrVal) {
				$matched = 1;
			}
	
		# Operator: >=
		#
		# Use: Attribute >= Value
		# As a check item, it matches if the request contains an attribute
		# with a value greater than, or equal to the one given.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '>=') {
			# Check for correct value
			if (defined($attrVal) && $attrVal >= $tattrVal) {
				$matched = 1;
			}
	
		# Operator: =*
		#
		# Use: Attribute =* Value
		# As a check item, it matches if the request contains the named attribute,
		# no matter what the value is.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '=*') {
			# Check for matching value
			if (defined($attrVal)) {
				$matched = 1;
			}
	
		# Operator !=
		#
		# Use: Attribute != Value
		# As a check item, matches if the given attribute is in the
		# request, AND does not have the given value.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '!=') {
			# Check for correct value
			if (defined($attrVal) && $attrVal ne $tattrVal) {
				$matched = 1;
			}
	
		# Operator: !*
		#
		# Use: Attribute !* Value
		# As a check item, matches if the request does not contain the named attribute, no matter
		# what the value is.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '!*') {
			# Skip if value not defined
			if (!defined($attrVal)) {
				$matched = 1;
			}
	
		# Operator: =~
		#
		# Use: Attribute =~ Value
		# As a check item, matches if the request contains an attribute which matches the given regular expression.
		# This operator may only be applied to string packetAttributes.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '=~') {
			# Check for correct value
			if (defined($attrVal) && $attrVal =~ /$tattrVal/) {
				$matched = 1;
			}
	
		# Operator: !~
		#
		# Use: Attribute !~ Value
		# As a check item, matches if the request does not match the given regular expression. This Operator may only
		# be applied to string packetAttributes.
		# what the value is.
		#
		# Not allowed as a reply item.
	
		} elsif ($attribute->{'Operator'} eq '!~') {
			# Check for correct value
			if (defined($attrVal) && !($attrVal =~ /$tattrVal/)) {
				$matched = 1;
			}
	
		# Operator: +=
		#
		# Use: Attribute += Value
		# Always matches as a check item, and adds the current
		# attribute with value to the list of configuration items.
		#
		# As a reply item, it has an itendtical meaning, but the
		# attribute is added to the reply items.
	
		} elsif ($attribute->{'Operator'} eq '+=') {
			# FIXME - Add to config items
			$matched = 1;
	
		# FIXME
		# Operator: :=
		#
		# Use: Attribute := Value
		# Always matches as a check item, and replaces in the configuration items any attribute of the same name. 
		# If no attribute of that name appears in the request, then this attribute is added.
		#
		# As a reply item, it has an itendtical meaning, but for the reply items, instead of the request items.
	
		} elsif ($attribute->{'Operator'} eq ':=') {
			# FIXME - Add or replace config items
			# FIXME - Add attribute to request
			$matched = 1;
	
		# Attributes that are not defined
		} else {
			# Ignore
			$matched = 2;
			last;
		}
	}

	# Some debugging info	
	if ($matched == 1) {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' matched");
	} elsif ($matched == 2) {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' ignored");
	} else {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' not matched");
	}

	return $matched;
}




## @fn getReplyAttribute($server,$attributes,$attribute)
# Function which sees if we must reply with this attribute
#
# @param server Server instance
# @param attributes Hashref of reply attributes
# @param attribute Attribute to check
sub getReplyAttribute
{
	my ($server,$attributes,$attribute) = @_;

	
	# Check ignore list
	foreach my $ignoredAttr (@attributeIgnoreList) {
		# 2 = IGNORE, so return IGNORE for all ignored items
		return 2 if ($attribute->{'Name'} eq $ignoredAttr);
	}

	# Did we find a match
	my $matched = 0;

	# Figure out our attr values
	my @attrValues;
	if (ref($attribute->{'Value'}) eq "ARRAY") {
		@attrValues = @{$attribute->{'Value'}};
	} else {
		@attrValues = ( $attribute->{'Value'} );
	}	

	$server->log(LOG_DEBUG,"[ATTRIBUTES] Processing REPLY attribute: '".
			$attribute->{'Name'}."' ".$attribute->{'Operator'}." '".join("','",@attrValues)."'");
	

	# Loop with all values
	foreach my $attrVal (@attrValues) { 	
		# Operator: =
		#
		# Use: Attribute = Value
		# Not allowed as a check item for RADIUS protocol attributes. It is allowed for server
		# configuration attributes (Auth-Type, etc), and sets the value of on attribute,
		# only if there is no other item of the same attribute.
		#
		# As a reply item, it means "add the item to the reply list, but only if there is
		# no other item of the same attribute.
	
		if ($attribute->{'Operator'} eq '=') {
			if (!defined($attrVal)) {
				$matched = 1;
			}
	
		# Operator: :=
		#
		# Use: Attribute := Value
		# Always matches as a check item, and replaces in the configuration items any attribute of the same name. 
		# If no attribute of that name appears in the request, then this attribute is added.
		#
		# As a reply item, it has an itendtical meaning, but for the reply items, instead of the request items.
	
		} elsif ($attribute->{'Operator'} eq ':=') {
			# Add attribute if attribute appears
			if (!defined($attrVal)) {
				$matched = 1;
			}
	
		# Operator: +=
		#
		# Use: Attribute += Value
		# Always matches as a check item, and adds the current
		# attribute with value to the list of configuration items.
		#
		# As a reply item, it has an itendtical meaning, but the
		# attribute is added to the reply items.
	
		} elsif ($attribute->{'Operator'} eq '+=') {
			$matched = 1;
		
		# Attributes that are not defined
		} else {
			# Ignore and b0rk out
			$matched = 2;
			last;
		}
	}

	# Some debugging info	
	if ($matched == 1) {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' matched");
		push(@{$attributes->{$attribute->{'Name'}}},@attrValues);
	} elsif ($matched == 2) {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' ignored");
	} else {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' not matched");
	}

	return $matched;

}




## @fn checkAttributeConfig($server,$packetAttributes,$attribute)
# Function to check an attribute in the configuration stage
#
# @param server Server instance
# @param packetAttributes Hashref of attributes provided, eg. Those from the packet
# @param attribute Attribute to check, eg. One of the ones from the database
sub checkAttributeConfig
{
	my ($server,$configAttributes,$attribute) = @_;


	# Matched & ok?
	my $matched = 0;

	# Figure out our attr values
	my @attrValues;
	if (ref($attribute->{'Value'}) eq "ARRAY") {
		@attrValues = @{$attribute->{'Value'}};
	} else {
		@attrValues = ( $attribute->{'Value'} );
	}	

	$server->log(LOG_DEBUG,"[ATTRIBUTES] Processing CONFIG attribute: '".$attribute->{'Name'}."' ".
			$attribute->{'Operator'}." '".join("','",@attrValues)."'");
	
	# FIXME
	# Operator: +=
	#
	# Use: Attribute += Value
	# Always matches as a check item, and adds the current
	# attribute with value to the list of configuration items.
	#
	# As a reply item, it has an itendtical meaning, but the
	# attribute is added to the reply items.

	if ($attribute->{'Operator'} eq '+=') {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] Operator '+=' triggered: Adding item to configuration items.");
		push(@{$configAttributes->{$attribute->{'Name'}}},@attrValues);

	# FIXME
	# Operator: :=
	#
	# Use: Attribute := Value
	# Always matches as a check item, and replaces in the configuration items any attribute of the same name. 
	# If no attribute of that name appears in the request, then this attribute is added.
	#
	# As a reply item, it has an itendtical meaning, but for the reply items, instead of the request items.

	} elsif ($attribute->{'Operator'} eq ':=') {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] Operator ':=' triggered: Adding or replacing item in configuration items.");
		@{$configAttributes->{$attribute->{'Name'}}} = @attrValues;

	# Operators that are not defined
	} else {
		# Ignore
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' ignored");
	}
}






1;
# vim: ts=4
