# SMRadius Utility Functions
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


=encoding utf8

=head1 NAME

smradius::util - SMRadius utils

=head1 SYNOPSIS

	my ($str,@vals) = templateReplace("SELECT * FROM abc WHERE %{abc} = ?",{ 'abc' => "some value" });

	my $str = quickTemplateToolkit('someval is "[% someval %]"',{ 'someval' = "hello world" });

=head1 DESCRIPTION

The smradius::util class provides utility classes for SMRadius.

=cut


package smradius::util;
use parent qw(Exporter);

use strict;
use warnings;

our (@EXPORT_OK,@EXPORT);
@EXPORT_OK = qw(
);
@EXPORT = qw(
	templateReplace
	quickTemplateToolkit
);


use Template;



=head1 METHODS

The following utility methods are available.

=cut



=head2 templateReplace

	my ($str,@vals) = templateReplace("SELECT * FROM abc WHERE %{abc} = ?",{ 'abc' => "some value" });

The C<templatereplace> method is used to replace variables with a placeholder. This is very useful for SQL templates. The values
are returned in the second and subsequent array items.

=over

=back

=cut

# Replace hashed variables with placeholders and return an array with the values.
sub templateReplace
{
	my ($string,$hashref,$placeholder) = @_;


	my @valueArray = ();
	$placeholder //= '?';

	# Replace blanks
	while (my ($entireMacro,$section,$item,$default) = ($string =~ /(\%{([a-z]+)\.([a-z0-9\-]+)(?:=([^}]*))?})/i )) {
		# Replace macro with ?
		$string =~ s/$entireMacro/$placeholder/;

		# Get value to substitute
		my $value = (defined($hashref->{$section}) && defined($hashref->{$section}->{$item})) ?
				$hashref->{$section}->{$item} : $default;

		# Add value onto our array
		push(@valueArray,$value);
	}

	return ($string, @valueArray);
}



=head2 quickTemplateToolkit

	my $str = quickTemplateToolkit('someval is "[% someval %]"',{ 'someval' = "hello world" });

The C<quickTemplateToolkit> is a quick and easy template toolkit function.

=over

=back

=cut

# Replace hashed variables with placeholders and return an array with the values.
sub quickTemplateToolkit
{
	my ($string,$variables) = @_;


	# This is the config we're going to pass to Template
	my $config = {
		# Our include path built below
		INCLUDE_PATH => [ ],
		# Chomp whitespaces
		PRE_CHOMP => 1,
		POST_CHOMP => 1,
	};

	# Create template engine
	my $tt = Template->new($config);

	# Process the template and output to our OUTPUT_PATH
	my $output = "";
	if (!(my $res = $tt->process(\$string, $variables, \$output))) {
		return (undef,$tt->error());
	}

	return $output;
}



1;
__END__

=head1 AUTHORS

Nigel Kukard E<lt>nkukard@lbsd.netE<gt>

=head1 BUGS

All bugs should be reported via the project issue tracker
L<http://gitlab.devlabs.linuxassist.net/awit-frameworks/awit-perl-toolkit/issues/>.

=head1 LICENSE AND COPYRIGHT

Copyright (C) 2007-2016, AllWorldIT

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

=head1 SEE ALSO

L<Template>.

=cut
