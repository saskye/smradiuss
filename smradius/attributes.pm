# Attribute handling functions
# Copyright (C) 2007-2011, AllWorldIT
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
	checkAuthAttribute
	setReplyAttribute
	setReplyVAttribute
	processConfigAttribute

	getAttributeValue

	addAttributeConditionalVariable
	processAttributeConditionals
);


use Math::Expression;

use smradius::logging;
use smradius::util;


# Attributes we do not handle
my @attributeCheckIgnoreList = (
	'User-Password'
);
my @attributeReplyIgnoreList = (
	'User-Password',
	'SMRadius-Capping-Traffic-Limit',
	'SMRadius-Capping-Uptime-Limit',
	'SMRadius-Validity-ValidFrom',
	'SMRadius-Validity-ValidTo',
	'SMRadius-Validity-ValidWindow',
	'SMRadius-Username-Transform',
	'SMRadius-Evaluate',
	'SMRadius-Peer-Address',
	'SMRadius-Disable-WebUITopup'
);
my @attributeVReplyIgnoreList = (
);


## @fn addAttribute($server,$user,$attribute)
# Function to add an attribute to $attributes
#
# @param server Server instance
# @param nattributes Hashref of normal attributes we already have and/or must add to
# @param vattributes Hashref of vendor attributes we already have and/or must add to
# @param attribute Attribute to add, eg. Those from a database
sub addAttribute
{
	my ($server,$user,$attribute) = @_;


	# Check we have the name, operator AND value
	if (!defined($attribute->{'Name'}) || !defined($attribute->{'Operator'}) || !defined($attribute->{'Value'})) {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] Problem adding attribute with name = ".niceUndef($attribute->{'Name'}).
				", operator = ".niceUndef($attribute->{'Operator'}).", value = ".niceUndef($attribute->{'Value'}));
		return;
	}

	# Clean them up a bit
	$attribute->{'Name'} =~ s/\s*(\S+)\s*/$1/;
	$attribute->{'Operator'} =~ s/\s*(\S+)\s*/$1/;

	# Grab attribute name, operator and value
	my $name = $attribute->{'Name'};
	my $operator = $attribute->{'Operator'};
	my $value = $attribute->{'Value'};
	# Default attribute to add is normal
	my $attributes = $user->{'Attributes'};

	# Check where we must add this attribute, maybe to the vendor attributes?
	if ($name =~ /^\[(\d+):(\S+)\]$/) {
		my $vendor = $1; $name = $2;
		# Set vendor
		$attribute->{'Vendor'} = $vendor;
		# Reset attribute name
		$attribute->{'Name'} = $name;
		# Set the attributes to use to the vendor
		$attributes = $user->{'VAttributes'};
	}

	# Check if this is an array
	if ($operator =~ s/^\|\|//) {
		# Check if we've seen this before
		if (defined($attributes->{$name}->{$operator}) &&
				ref($attributes->{$name}->{$operator}->{'Value'}) eq "ARRAY" ) {
			# Then add value to end of array
			push(@{$attributes->{$name}->{$operator}->{'Value'}}, $value);

		# If we have not seen it before, initialize it
		} else {
			# Assign attribute
			$attributes->{$name}->{$operator} = $attribute;
			# Override type ... else we must create a custom attribute hash, this is dirty, but faster
			$attributes->{$name}->{$operator}->{'Value'} = [ $value ];
		}

	# If its not an array, just add it normally
	} else {
		$attributes->{$name}->{$operator} = $attribute;
	}

	# Process the item incase its a config attribute
	processConfigAttribute($server,$user,$attribute);
}



## @fn checkAuthAttribute($server,$packetAttributes,$attribute)
# Function to check an attribute in the authorization stage
#
# @param server Server instance
# @param packetAttributes Hashref of attributes provided, eg. Those from the packet
# @param attribute Attribute to check, eg. One of the ones from the database
sub checkAuthAttribute
{
	my ($server,$user,$packetAttributes,$attribute) = @_;


	# Check ignore list
	foreach my $ignoredAttr (@attributeCheckIgnoreList) {
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
		# Sanitize the operator
		my ($operator) = ($attribute->{'Operator'} =~ /^(?:\|\|)?(.*)$/);

		# Operator: ==
		#
		# Use: Attribute == Value
		# As a check item, it matches if the named attribute is present in the request,
		# AND has the given value.
		#
		if ($operator eq '==' ) {
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

		} elsif ($operator eq '>') {
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

		} elsif ($operator eq '<') {
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

		} elsif ($operator eq '<=') {
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

		} elsif ($operator eq '>=') {
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

		} elsif ($operator eq '=*') {
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

		} elsif ($operator eq '!=') {
			# Check for correct value
			if (!defined($attrVal) || $attrVal ne $tattrVal) {
				$matched = 1;
			}

		# Operator: !*
		#
		# Use: Attribute !* Value
		# As a check item, matches if the request does not contain the named attribute, no matter
		# what the value is.
		#
		# Not allowed as a reply item.

		} elsif ($operator eq '!*') {
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

		} elsif ($operator eq '=~') {
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

		} elsif ($operator eq '!~') {
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

		} elsif ($operator eq '+=') {

			# Check if we're a conditional and process
			if ($attribute->{'Name'} eq "SMRadius-Evaluate") {
				$matched = processConditional($server,$user,$attribute,$tattrVal);
			} else {
				$matched = 1;
			}

		# FIXME
		# Operator: :=
		#
		# Use: Attribute := Value
		# Always matches as a check item, and replaces in the configuration items any attribute of the same name.

		} elsif ($operator eq ':=') {
			# FIXME - Add or replace config items
			# FIXME - Add attribute to request

			# Check if we're a conditional and process
			if ($attribute->{'Name'} eq "SMRadius-Evaluate") {
				$matched = processConditional($server,$user,$attribute,$tattrVal);
			} else {
				$matched = 1;
			}

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




## @fn setReplyAttribute($server,$attributes,$attribute)
# Function which sees if we must reply with this attribute
#
# @param server Server instance
# @param attributes Hashref of reply attributes
# @param attribute Attribute to check
sub setReplyAttribute
{
	my ($server,$attributes,$attribute) = @_;


	# Check ignore list
	foreach my $ignoredAttr (@attributeReplyIgnoreList) {
		# 2 = IGNORE, so return IGNORE for all ignored items
		return 2 if ($attribute->{'Name'} eq $ignoredAttr);
	}

	# Figure out our attr values
	my @attrValues;
	if (ref($attribute->{'Value'}) eq "ARRAY") {
		@attrValues = @{$attribute->{'Value'}};
	} else {
		@attrValues = ( $attribute->{'Value'} );
	}

	$server->log(LOG_DEBUG,"[ATTRIBUTES] Processing REPLY attribute: '".
			$attribute->{'Name'}."' ".$attribute->{'Operator'}." '".join("','",@attrValues)."'");


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
		# If item does not exist
		if (!defined($attributes->{$attribute->{'Name'}})) {
			# Then add
			$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}.
					"' no value exists, setting value to '".join("','",@attrValues)."'");
			@{$attributes->{$attribute->{'Name'}}} = @attrValues;
		}


	# Operator: :=
	#
	# Use: Attribute := Value
	# Always matches as a check item, and replaces in the configuration items any attribute of the same name.
	# If no attribute of that name appears in the request, then this attribute is added.
	#
	# As a reply item, it has an itendtical meaning, but for the reply items, instead of the request items.

	} elsif ($attribute->{'Operator'} eq ':=') {
		# Overwrite
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}.
					"' setting attribute value to '".join("','",@attrValues)."'");
		@{$attributes->{$attribute->{'Name'}}} = @attrValues;


	# Operator: +=
	#
	# Use: Attribute += Value
	# Always matches as a check item, and adds the current
	# attribute with value to the list of configuration items.
	#
	# As a reply item, it has an itendtical meaning, but the
	# attribute is added to the reply items.

	} elsif ($attribute->{'Operator'} eq '+=') {
		# Then add
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}.
				"' appending values '".join("','",@attrValues)."'");
		push(@{$attributes->{$attribute->{'Name'}}},@attrValues);

	# Attributes that are not defined
	} else {
		# Ignore invalid operator
		$server->log(LOG_NOTICE,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' ignored, invalid operator?");
	}

	return;
}




## @fn setReplyVAttribute($server,$attributes,$attribute)
# Function which sees if we must reply with this attribute
#
# @param server Server instance
# @param attributes Hashref of reply attributes
# @param attribute Attribute to check
sub setReplyVAttribute
{
	my ($server,$attributes,$attribute) = @_;


	# Check ignore list
	foreach my $ignoredAttr (@attributeVReplyIgnoreList) {
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

	$server->log(LOG_DEBUG,"[VATTRIBUTES] Processing REPLY attribute: '".
			$attribute->{'Name'}."' ".$attribute->{'Operator'}." '".join("','",@attrValues)."'");


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
		# If item does not exist
		if (!defined($attributes->{$attribute->{'Vendor'}}->{$attribute->{'Name'}})) {
			# Then add
			$server->log(LOG_DEBUG,"[VATTRIBUTES] - Attribute '".$attribute->{'Name'}.
					"' no value exists, setting value to '".join("','",@attrValues)."'");
			@{$attributes->{$attribute->{'Vendor'}}->{$attribute->{'Name'}}} = @attrValues;
		}


	# Operator: :=
	#
	# Use: Attribute := Value
	# Always matches as a check item, and replaces in the configuration items any attribute of the same name.
	# If no attribute of that name appears in the request, then this attribute is added.
	#
	# As a reply item, it has an itendtical meaning, but for the reply items, instead of the request items.

	} elsif ($attribute->{'Operator'} eq ':=') {
		# Overwrite
		$server->log(LOG_DEBUG,"[VATTRIBUTES] - Attribute '".$attribute->{'Name'}.
					"' setting attribute value to '".join("','",@attrValues)."'");
		@{$attributes->{$attribute->{'Vendor'}}->{$attribute->{'Name'}}} = @attrValues;


	# Operator: +=
	#
	# Use: Attribute += Value
	# Always matches as a check item, and adds the current
	# attribute with value to the list of configuration items.
	#
	# As a reply item, it has an itendtical meaning, but the
	# attribute is added to the reply items.

	} elsif ($attribute->{'Operator'} eq '+=') {
		# Then add
		$server->log(LOG_DEBUG,"[VATTRIBUTES] - Attribute '".$attribute->{'Name'}.
				"' appending values '".join("','",@attrValues)."'");
		push(@{$attributes->{$attribute->{'Vendor'}}->{$attribute->{'Name'}}},@attrValues);

	# Attributes that are not defined
	} else {
		# Ignore and b0rk out
		$server->log(LOG_NOTICE,"[VATTRIBUTES] - Attribute '".$attribute->{'Name'}."' ignored, invalid operator?");
		last;
	}

	return;
}




## @fn processConfigAttribute($server,$user,$attribute)
# Function to process a configuration attribute
#
# @param server Server instance
# @param packetAttributes Hashref of attributes provided, eg. Those from the packet
# @param attribute Attribute to process, eg. One of the ones from the database
sub processConfigAttribute
{
	my ($server,$user,$attribute) = @_;

	# Make things easier?
	my $configAttributes = $user->{'ConfigAttributes'};

	# Did we get processed?
	my $processed = 0;

	# Figure out our attr values
	my @attrValues;
	if (ref($attribute->{'Value'}) eq "ARRAY") {
		@attrValues = @{$attribute->{'Value'}};
	} else {
		@attrValues = ( $attribute->{'Value'} );
	}

	# Operator: +=
	#
	# Use: Attribute += Value
	# Always matches as a check item, and adds the current
	# attribute with value to the list of configuration items.
	#
	# As a reply item, it has an itendtical meaning, but the
	# attribute is added to the reply items.

	if ($attribute->{'Operator'} eq '+=') {
		push(@{$configAttributes->{$attribute->{'Name'}}},@attrValues);
		$processed = 1;

	# Operator: :=
	#
	# Use: Attribute := Value
	# Always matches as a check item, and replaces in the configuration items any attribute of the same name.
	# If no attribute of that name appears in the request, then this attribute is added.
	#
	# As a reply item, it has an itendtical meaning, but for the reply items, instead of the request items.

	} elsif ($attribute->{'Operator'} eq ':=') {
		@{$configAttributes->{$attribute->{'Name'}}} = @attrValues;
		$processed = 1;

	}

	# If we got procsessed output some debug
	if ($processed) {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] Processed CONFIG attribute: '".$attribute->{'Name'}."' ".
				$attribute->{'Operator'}." '".join("','",@attrValues)."'");
	}

	return $processed;
}


## @fn getAttributeValue($attributes,$attrName)
# Function which will return an attributes value
#
# @param attributes Attribute hash
# @param attrName Attribute name
#
# @return Attribute value
sub getAttributeValue
{
	my ($attributes,$attrName) = @_;

	my $value;

	# Set the value to the first item in the array
	if (defined($attributes->{$attrName})) {
		($value) = @{$attributes->{$attrName}};
	}

	return $value;
}


## @fn addAttributeConditionalVariable($user,$name,$value)
# Function that adds a conditional variable
#
# @param user User hash
# @param name Variable name
# @param value Variable value
sub addAttributeConditionalVariable
{
	my ($user,$name,$value) = @_;

	print(STDERR "CONDITIONAL VARIABLE:  $name => $value\n");
	$user->{'AttributeConditionalVariables'}->{$name} = [ $value ];
}


## @fn processConditional($server,$user,$attribute,$attrVal)
# This function processes a attribute conditional
#
# @param server Server hash
# @param user User hash
# @param attribute Attribute hash to process
# @param attrVal Current value we need to process
sub processConditional
{
	my ($server,$user,$attribute,$attrVal) = @_;

	# Split off expression
	my ($condition,$onTrue,$onFalse) = ($attrVal =~ /^([^\?]*)(?:\?\s*((?:\S+)?[^:]*)(?:\s*\:\s*(.*))?)?$/);

	# If there is no condition we cannot really continue?
	if (!defined($condition)) {
		$server->log(LOG_WARN,"[ATTRIBUTES] Conditional '$attrVal' cannot be parsed");
		return 1;
	}

	$server->log(LOG_DEBUG,"[ATTRIBUTES] Conditional parsed ".$attribute->{'Name'}." => if ($condition) then {".
			( $onTrue ? $onTrue : "-undef-")."} else {".( $onFalse ? $onFalse : "-undef-")."}");

	# Create the environment
	my @error;
	my $mathEnv = new Math::Expression(
			'PrintErrFunc' => sub { @error = @_ },
			'VarHash' => $user->{'AttributeConditionalVariables'}
	);

	# Parse and create math tree
	my $mathTree = $mathEnv->Parse($condition);
	# Check for error
	if (@error) {
		my $errorStr = sprintf($error[0],$error[1]);
		$server->log(LOG_WARN,"[ATTRIBUTES] Conditional '$condition' in '$attrVal' does not parse: $errorStr");
		return 1;
	}

	# Evaluate tree
	my $res = $mathEnv->Eval($mathTree);
	if (!defined($res)) {
		$server->log(LOG_WARN,"[ATTRIBUTES] Conditional '$condition' in '$attrVal' does not evaluate");
		return 1;
	}

	# Check result
	# If we have a onTrue or onFalse we will return "Matched = True"
	# If we don't have an onTrue or onFalse we will return the result of the $condition
	my $attribStr;
	if ($res && defined($onTrue)) {
		$attribStr = $onTrue;
		$res = 1;
	} elsif (!$res && defined($onFalse)) {
		$attribStr = $onFalse;
		$res = 1;
	} elsif (defined($onTrue) || defined($onFalse)) {
		$res = 1;
	}

	$server->log(LOG_DEBUG,"[ATTRIBUTES] - Evaluated to '$res' returning '".(defined($attribStr) ? $attribStr : "-undef-")."'");

	# Loop with attributes:
	# We only get here if $res is set to 1 above, if its only a conditional with no onTrue & onFalse
	# Then attribStr will be unef
	if ($res && defined($attribStr)) {
		foreach my $rawAttr (split(/;/,$attribStr)) {
			# Split off attribute string:  name = value
			my ($attrName,$attrVal) = ($rawAttr =~ /^\s*([^=]+)=\s*(.*)/);
			# Build attribute
			my $attribute = {
				'Name' => $attrName,
				'Operator' => ':=',
				'Value' => $attrVal
			};
			# Add attribute
			addAttribute($server,$user,$attribute);
		}
	}

	return $res;
}



1;
# vim: ts=4
