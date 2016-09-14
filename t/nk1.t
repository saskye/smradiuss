use strict;
use warnings;

use Test::More;


use Data::Dumper;



use POSIX ();



#$SIG{CHLD} = sub {
#	while () {
#		my $child = waitpid -1, POSIX::WNOHANG;
#		last if $child <= 0;
#		my $localtime = localtime;
#		warn "Parent: Child $child was reaped - $localtime.\n";
#		}
#};




require_ok("smradius::daemon");
require_ok("smradius::client");



if (my $child = fork()) {

	warn "\n\nPARENT: I AM PARENT $$, my child is $child\n\n";


	warn "\n\nPARENT: RUNNING CLIENT IN 5s\n\n";

	sleep 5;

	my $res = smradius::client->run(
		"--raddb","dicts",
		"127.0.0.1",
		"auth",
		"unittests",
		'User-Name=unit@test',
		'User-Password=test123',
	);


	warn "\n\nCLIENT RESULT:\n". Dumper($res) ."\n\n";


#	is($res, 0, "User authentication test");


	warn "\n\nPARENT: WAITING 10s\n\n";

	sleep 10;

	warn "\n\nPARENT: KILLING CHILD $child\n\n";

	kill('TERM',$child);

	warn "\n\nPARENT: WAITING...\n\n";
	waitpid($child,0);
	warn "\n\nPARENT: WAIT DONE\n\n";



} else {

	warn "\n\nCHILD: STARTED $$\n\n";


	smradius::daemon->run(
		"--fg",
		"--debug",
		"--config", "smradiusd.conf.test",
	);

	warn "\n\nCHILD: SLEEP START\n\n";
	sleep 4;
	warn "\n\nCHILD: SLEEP DONE\n\n";

	exit 0;

}


warn "\n\nPARENT: I WAS $$\n\n";

can_ok("smradius::daemon","displayHelp");


#  pass("First test");
#  subtest 'An example subtest' => sub {
#      plan tests => 2;
#      pass("This is a subtest");
#      pass("So is this");
#  };
#  pass("Third test");


done_testing();
