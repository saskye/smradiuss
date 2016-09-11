# Radius daemon request processing
# Copyright (C) 2007-2016, AllWorldIT
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



package smradius::daemon::request;

use strict;
use warnings;

use base qw{AWITPT::Object};



# Parse radius packet
sub parsePacket
{
	my ($self,$dictionary,$rawPacket) = @_;


	# Parse the radius packet
	$self->{'packet'} = Radius::Packet->new($dictionary,$rawPacket);

	# Loop with packet attribute names and add to our log line
	$self->addLogLine("PACKET => ");
	foreach my $attrName (sort $self->{'packet'}->attributes()) {
		# Make the value a bit more pretty to print
		my $attrVal;
		if ($attrName eq "User-Password") {
			$attrVal = "-encrypted-";
		} else {
			$attrVal = $self->{'packet'}->rawattr($attrName);
		}
		# Add it onto the log line...
		$self->addLogLine(
			"%s: '%s'",
			$attrName,
			$attrVal,
		);
	}

	# Set the username
	$self->{'user'}->{'Username'} = $self->{'packet'}->attr('User-Name');

	# Set the packet timestamp in unix time
	if (my $timestamp = $self->{'packet'}->rawattr('Event-Timestamp')) {
		$self->setTimestamp($timestamp);
	} else {
		$self->setTimestamp(time());
	}

	return $self;
}



# Set internal timestamp
sub setTimestamp
{
	my ($self,$timestamp) = @_;


	# Set timestamp
	$self->{'user'}->{'_Internal'}->{'Timestamp-Unix'} = $timestamp;

	# Grab real event timestamp in local time uzing the time zone
	my $eventTimestamp = DateTime->from_epoch(
			epoch => $self->{'user'}->{'_Internal'}->{'Timestamp-Unix'},
			time_zone => $self->{'timeZone'},
	);
	# Set the timestamp (not in unix)
	$self->{'user'}->{'_Internal'}->{'Timestamp'} = $eventTimestamp->strftime('%Y-%m-%d %H:%M:%S');

	return $self;
}



# Set internal time zone
sub setTimeZone
{
	my ($self,$timeZone) = @_;


	$self->{'timeZone'} = $timeZone;

	return $self;
}



# Add something onto the log line in printf() style...
sub addLogLine
{
	my ($self,$template,@params) = @_;


	# Add on template and params
	push(@{$self->{'logLine'}},$template);
	push(@{$self->{'logLineParams'}},@params);

	return $self;
}



# Return if the Username attribute of the user is defined
sub hasUsername
{
	my $self = shift;


	return defined($self->{'user'}->{'Username'});
}



#
# INTERNAL METHODS
#


# This method is called from the new-> method during instantiation
sub _init
{
	my ($self) = @_;


	# Initialize log line
	$self->{'logLine'} = [ ];
	$self->{'logLineParams'} = [ ];

	$self->{'timeZone'} = "UTC";

	# Initialize user
	$self->{'user'} = {
		'Username' => undef,

		'ConfigAttributes' => { },
		'Attributes' => { },
		'VAttributes' => { },
		'ReplyAttributes' => { },
		'ReplyVAttributes' => { },
		'AttributeConditionalVariables' => { },
	};

	return $self;
}



1;
# vim: ts=4
