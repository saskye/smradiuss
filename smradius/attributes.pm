# Attribute handling functions
# Copyright (C) 2008, AllWorldIT
# Copyright (C) 2007, Nigel Kukard  <nkukard@lbsd.net>
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
);


use smradius::logging;


## @fn addAttribute($server,$attributes,$attribute)
# Function to add an attribute to $attributes
#
# @param server Server instance
# @param attributes Hashref of attributes we already have and / or must add to
# @param attribute Attribute to add, eg. Those from a database
sub addAttribute
{
	my ($server,$attributes,$attribute) = @_;

	# FIXME - quick hack
	$attributes->{$attribute->{'Name'}} = $attribute->{'Value'};
}



## @fn checkAttributeAuth($server,$attributes,$attribute)
# Function to check an attribute in the authorization stage
#
# @param server Server instance
# @param attributes Hashref of attributes provided, eg. Those from the packet
# @param attribute Attribute to check, eg. One of the ones from the database
sub checkAttributeAuth
{
	my ($server,$attributes,$attribute) = @_;


	my $matched = 0;


	# Get attribute value
	my $attrVal = $attributes->{$attribute->{'Name'}};

	$server->log(LOG_DEBUG,"[ATTRIBUTES] Processing CHECK attribute value ".niceUndef($attrVal)."' against: '".
			$attribute->{'Name'}."' ".$attribute->{'Operator'}." '".$attribute->{'Value'}."'");

	# Operator: ==
	#
	# Use: Attribute == Value
	# As a check item, it matches if the named attribute is present in the request,
	# AND has the given value.
	#
	if ($attribute->{'Operator'} eq '==' ) {
		# Check for correct value
		if (defined($attrVal) && $attrVal eq $attribute->{'Value'}) {
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
			if ($attrVal > $attribute->{'Value'}) {
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
		if (defined($attrVal) && $attrVal < $attribute->{'Value'}) {
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
		if (defined($attrVal) && $attrVal <= $attribute->{'Value'}) {
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
		if (defined($attrVal) && $attrVal >= $attribute->{'Value'}) {
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
		if (defined($attrVal) && $attrVal ne $attribute->{'Value'}) {
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
	# This operator may only be applied to string attributes.
	#
	# Not allowed as a reply item.

	} elsif ($attribute->{'Operator'} eq '=~') {
		# Check for correct value
		my $regex = $attribute->{'Value'};
		if (defined($attrVal) && $attrVal =~ /$regex/) {
			$matched = 1;
		}

	# Operator: !~
	#
	# Use: Attribute !~ Value
	# As a check item, matches if the request does not contain the named attribute, no matter
	# what the value is.
	#
	# Not allowed as a reply item.
# FIXME: WRONG description
	} elsif ($attribute->{'Operator'} eq '!~') {
		# Check for correct value
		my $regex = $attribute->{'Value'};
		if (defined($attrVal) && !($attrVal =~ /$regex/)) {
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
		# Check for correct value
		if (defined($attrVal) && $attrVal eq $attribute->{'Value'}) {
			# FIXME - Add to config items
			$matched = 1;
		}

	# FIXME
	# Operator: :=
	#
	# Use: Attribute := Value
	# Always matches as a check item, and replaces in the configuration items any attribute of the same name. 
	# If no attribute of that name appears in the request, then this attribute is added.
	#
	# As a reply item, it has an itendtical meaning, but for the reply items, instead of the request items.

	} elsif ($attribute->{'Operator'} eq ':=') {
		# Check for correct value
		if (defined($attrVal) && $attrVal eq $attribute->{'Value'}) {
			# FIXME - Add or replace config items
			$matched = 1;
		}
	}

	# Some debugging info	
	if ($matched) {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' matched");
	} else {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' not matched");
	}

	return $matched;
}




## @fn getReplyAttribute($server,$attributes,$attribute)
# Function which sees if we must reply with this attribute
#
# @param server Server instance
# @param attributes Hashref of attributes provided
# @param attribute Attribute to check
sub getReplyAttribute
{
	my ($server,$attributes,$attribute) = @_;


	my $matched = 0;

	# Grab attribute value
	my $attrVal = $attributes->{$attribute->{'Name'}};

	$server->log(LOG_DEBUG,"[ATTRIBUTES] Processing REPLY attribute value ".niceUndef($attrVal)."' against: '".
			$attribute->{'Name'}."' ".$attribute->{'Operator'}." '".$attribute->{'Value'}."'");

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
	}

	# Some debugging info	
	if ($matched) {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' matched");
	} else {
		$server->log(LOG_DEBUG,"[ATTRIBUTES] - Attribute '".$attribute->{'Name'}."' not matched");
	}

	return $matched;
}










1;
# vim: ts=4
