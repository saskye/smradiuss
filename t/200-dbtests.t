use strict;
use warnings;


use AWITPT::Util;
use Data::Dumper;
use POSIX qw(:sys_wait_h);
use Test::Most;
use Test::Most::Exception 'throw_failure';



#
# Check that database tests are enabled
#

# We need DBTESTS enabled to run this
if (!$ENV{'DBTESTS'}) {
	plan skip_all => 'DBTESTS not set in ENV';
	done_testing();
	exit 0;
}



#
# Load database handling libraries
#

require_ok('AWITPT::DB::DBILayer');
require_ok('AWITPT::DB::DBLayer');

use AWITPT::DB::DBLayer;


#
# Load our server and client
#

require_ok("smradius::daemon");
require_ok("smradius::client");


#
# Daemon help
#

can_ok("smradius::daemon","displayHelp");


#
# Try connect to database
#

my $dbh = AWITPT::DB::DBILayer->new({
	'Username' => 'root',
	'DSN' => 'DBI:mysql:database=smradiustest;host=localhost',
});


# If we cannot connect, just bail out
if ($dbh->connect()) {
	BAIL_OUT("ERROR: Failed to connect to database for testing purposes: ".$dbh->error());
}

AWITPT::DB::DBLayer::setHandle($dbh);


#
# Make sure DB is clean
#

my $sth;

$sth = DBDo("DELETE FROM accounting");
is(AWITPT::DB::DBLayer::error(),"","Clean table 'accounting");

$sth = DBDo("DELETE FROM user_attributes");
is(AWITPT::DB::DBLayer::error(),"","Clean table 'user_attributes");

$sth = DBDo("DELETE FROM client_attributes");
is(AWITPT::DB::DBLayer::error(),"","Clean table 'client_attributes");

$sth = DBDo("DELETE FROM users");
is(AWITPT::DB::DBLayer::error(),"","Clean table 'users'");

$sth = DBDo("DELETE FROM clients_to_realms");
is(AWITPT::DB::DBLayer::error(),"","Clean table 'clients_to_realms'");

$sth = DBDo("DELETE FROM clients");
is(AWITPT::DB::DBLayer::error(),"","Clean table 'clients'");

$sth = DBDo("DELETE FROM realms");
is(AWITPT::DB::DBLayer::error(),"","Clean table 'realms'");


#
# Run server and client
#

our $child;
if ($child = fork()) {

	# CHLD handler
	local $SIG{CHLD} = sub {
		warn "SIGCHLD TRIGGER";
		waitpid($child,-1);
	};

	# Install signal handlers to cleanup if we get a TERM or INT
	local $SIG{TERM} = local $SIG{INT} = \&cleanup;


	# Wait before starting
	sleep(2);


	# Setup failure handler
	set_failure_handler( sub { my @params = @_; cleanup(); throw_failure } );


	my $res;



	#
	# Make sure basic test without any config does not authenticate users
	#

	$res = smradius::client->run(
		"--raddb","dicts",
		"127.0.0.1",
		"auth",
		"secret123",
		'User-Name=testuser1',
		'User-Password=test123',
	);
	is(ref($res),"","smradclient ref should return ''");
	is($res,1,"smradclient result should be 1");



	#
	# Create test case data
	#

	my $client1_ID = testDBInsert("Create client 'localhost'",
		"INSERT INTO clients (Name,AccessList,Disabled) VALUES ('localhost','127.0.0.0/8',0)"
	);

	my $client1attr1_ID = testDBInsert("Create client 'localhost' secret",
		"INSERT INTO client_attributes (ClientID,Name,Operator,Value,Disabled) VALUES (?,?,?,?,0)",
			$client1_ID,'SMRadius-Config-Secret',':=','secret123'
	);

	my $realm1_ID = testDBInsert("Create realm ''",
		"INSERT INTO realms (Name,Disabled) VALUES ('',0)"
	);

	my $clientTOrealm1_ID = testDBInsert("Link client 'localhost' to realm ''",
		"INSERT INTO clients_to_realms (ClientID,RealmID,Disabled) VALUES (?,?,0)",$client1_ID,$realm1_ID
	);



	#
	# Check we get an Access-Reject for an unconfigured user
	#

	my $user1_ID = testDBInsert("Create user 'testuser1'",
		"INSERT INTO users (UserName,Disabled) VALUES ('testuser1',0)"
	);

	my $user1attr1_ID = testDBInsert("Create user 'testuser1' attribute 'User-Password'",
		"INSERT INTO user_attributes (UserID,Name,Operator,Value,Disabled) VALUES (?,?,?,?,0)",
			$user1_ID,'User-Password','==','test123'
	);

	$res = smradius::client->run(
		"--raddb","dicts",
		"127.0.0.1",
		"auth",
		"secret123",
		'User-Name=testuser1',
		'User-Password=test123',
	);
	is(ref($res),"HASH","smradclient should return a HASH");
	is($res->{'response'}->{'code'},"Access-Reject","Check our return is 'Access-Reject' for unconfigured user");



	#
	# Check we get a Access-Accept for an uncapped usage user
	#

	my $user2_ID = testDBInsert("Create user 'testuser2'",
		"INSERT INTO users (UserName,Disabled) VALUES ('testuser2',0)"
	);

	my $user2attr1_ID = testDBInsert("Create user 'testuser2' attribute 'User-Password'",
		"INSERT INTO user_attributes (UserID,Name,Operator,Value,Disabled) VALUES (?,?,?,?,0)",
			$user2_ID,'User-Password','==','test123'
	);

	my $user2attr2_ID = testDBInsert("Create user 'testuser2' attribute 'SMRadius-Capping-Traffic-Limit'",
		"INSERT INTO user_attributes (UserID,Name,Operator,Value,Disabled) VALUES (?,?,?,?,0)",
			$user2_ID,'SMRadius-Capping-Traffic-Limit',':=','0'
	);

	my $user2attr3_ID = testDBInsert("Create user 'testuser2' attribute 'SMRadius-Capping-Uptime-Limit'",
		"INSERT INTO user_attributes (UserID,Name,Operator,Value,Disabled) VALUES (?,?,?,?,0)",
			$user2_ID,'SMRadius-Capping-Uptime-Limit',':=','0'
	);

	$res = smradius::client->run(
		"--raddb","dicts",
		"127.0.0.1",
		"auth",
		"secret123",
		'User-Name=testuser2',
		'User-Password=test123',
	);
	is(ref($res),"HASH","smradclient should return a HASH");
	is($res->{'response'}->{'code'},"Access-Accept","Check our return is 'Access-Accept' for a basically configured user");


	#
	# Test missing accounting START packet
	#

	my $session1_ID = 81700217;
	my $session1_Timestamp = time();
	my $session1_Timestamp_str = DateTime->from_epoch(epoch => $session1_Timestamp,time_zone => 'UTC')
			->strftime('%Y-%m-%d %H:%M:%S');

	$res = smradius::client->run(
		"--raddb","dicts",
		"127.0.0.1",
		"acct",
		"secret123",
		'User-Name=testuser2',
		'NAS-IP-Address=10.0.0.1',
		'Acct-Delay-Time=12',
		'NAS-Identifier=Test-NAS',
		'Acct-Status-Type=Interim-Update',
		'Acct-Output-Packets=786933',
		'Acct-Output-Gigawords=0',
		'Acct-Output-Octets=708163705',
		'Acct-Input-Packets=670235',
		'Acct-Input-Gigawords=0',
		'Acct-Input-Octets=102600046',
		'Acct-Session-Time=800',
		'Event-Timestamp='.$session1_Timestamp,
		'Framed-IP-Address=10.0.1.1',
		'Acct-Session-Id='.$session1_ID,
		'NAS-Port-Id=wlan1',
		'Called-Station-Id=testservice',
		'Calling-Station-Id=00:00:0C:EE:47:BF',
		'User-Name=testuser2',
		'NAS-Port-Type=Ethernet',
		'NAS-Port=15729175',
		'Framed-Protocol=PPP',
		'Service-Type=Framed-User',
	);
	is(ref($res),"HASH","smradclient should return a HASH");

	testDBResults("Check accounting record is created correctly",'accounting',{'AcctSessionID' => $session1_ID},
		{
			'Username' => 'testuser2',
			'NASIPAddress' => '10.0.0.1',
			'AcctDelayTime' => '12',
			'NASIdentifier' => 'Test-NAS',
			'AcctStatusType' => 3,
			'AcctOutputPackets' => '786933',
			'AcctOutputGigawords' => '0',
			'AcctOutputOctets' => '708163705',
			'AcctInputPackets' => '670235',
			'AcctInputGigawords' => '0',
			'AcctInputOctets' => '102600046',
			'AcctSessionTime' => '800',
			'EventTimestamp' => $session1_Timestamp_str,
			'FramedIPAddress' => '10.0.1.1',
			'AcctSessionId' => $session1_ID,
			'NASPortId' => 'wlan1',
			'CalledStationId' => 'testservice',
			'CallingStationId' => '00:00:0C:EE:47:BF',
			'NASPortType' => 15,
			'NASPort' => '15729175',
			'FramedProtocol' => 1,
			'ServiceType' => 2,
		}
	);


	sleep(5);


} else {

	smradius::daemon->run(
		"--fg",
		"--debug",
		"--config", "smradiusd.conf.test",
	);

	sleep 4;

	exit 0;
}



cleanup();

done_testing();




# Cleanup function
sub cleanup
{
	if ($child) {
		# Kill the child if it exists
		if (kill(0,$child)) {
			kill('TERM',$child);
		}
		# Wait for it to be reaped
		waitpid($child,-1);
	}

}



# Function to quickly and easily insert data into the DB and generate 2 tests out of it
sub testDBInsert
{
	my ($name,@params) = @_;


	# Do the work...
	DBDo(@params);
	# Make sure we got no error
	is(AWITPT::DB::DBLayer::error(),"",$name);

	# Grab the last insert ID
	my $id = DBLastInsertID();
	# Make sure its > 0
	is($id > 0,1,"$name, insert ID > 0");

	return $id;
}



# Test DB select results
sub testDBResults
{
	my ($name,$table,$where,$resultCheck) = @_;


	# Build column list
	my $columnList_str = join(',',keys %{$resultCheck});

	# Create where criteria
	my @whereLines = ();
	my @whereData = ();
	foreach my $columnName (keys %{$where}) {
		# Add template placeholders
		push(@whereLines,"$columnName = ?");
		# Add data for template placeholders
		push(@whereData,$where->{$columnName});
	}
	my $whereLines_str = join(',',@whereLines);

	# Do select
	my $sth = DBSelect("
		SELECT
			$columnList_str
		FROM
			$table
		WHERE
			$whereLines_str
	",@whereData);

	# Make sure we got no error
	is(AWITPT::DB::DBLayer::error(),"","Errors on DBSelect: $name");

	# We should get one result...
	my $row = hashifyLCtoMC($sth->fetchrow_hashref(),keys %{$resultCheck});
	is(defined($row),1,"DBSelect row defined: $name");

	# Loop through results and check if they match
	foreach my $resultName (keys %{$resultCheck}) {
		is($row->{$resultName},$resultCheck->{$resultName},"$name: $resultName check");
	}
}



