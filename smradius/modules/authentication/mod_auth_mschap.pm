# Microsoft CHAP version 1 and 2 support
# Copyright (C) 2007-2015, AllWorldIT
#
# References: 
#	RFC1994 - PPP Challenge Handshake Authentication Protocol (CHAP)
#	RFC2443 - Microsoft PPP CHAP Extensions
#	RFC2759 - Microsoft PPP CHAP Extensions, Version 2
#	RFC2548 - Microsoft Vendor-specific RADIUS Attributes
#	RFC3079 - Deriving Keys for use with Microsoft Point-to-Point 
#	          Encryption (MPPE)
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




package smradius::modules::authentication::mod_auth_mschap;

use strict;
use warnings;


# Modules we need
use smradius::attributes;
use smradius::constants;
use smradius::logging;
use Crypt::DES;
use Crypt::RC4;
use Digest::SHA1;
use Digest::MD4 qw( md4 );
use Digest::MD5 qw( );

# Don't use unicode
use bytes;


# Exporter stuff
require Exporter;
our (@ISA,@EXPORT,@EXPORT_OK);
@ISA = qw(Exporter);
@EXPORT = qw(
);
@EXPORT_OK = qw(
	GenerateNTResponse
	ChallengeHash
	NtPasswordHash
	HashNtPasswordHash
	ChallengeResponse
	GenerateAuthenticatorResponse
	CheckAuthenticatorResponse
	NtChallengeResponse
);


use constant {
	MSCHAPV2_MAXPWLEN => 256
};



# Plugin info
our $pluginInfo = {
	Name => "MSCHAP v1/2 Authentication",
	Init => \&init,

	# Authentication
	Authentication_try => \&authenticate,
};



## @internal
# Initialize module
sub init
{
	my $server = shift;
}



## @authenticate
# Try authenticate user
#
# @param server Server object
# @param user User hash
# @param packet Radius packet
#
# @return Result
sub authenticate
{
	my ($server,$user,$packet) = @_;


	# Get some params
	my $rawChallenge = $packet->vsattr('311','MS-CHAP-Challenge');
	my $rawResponse = $packet->vsattr('311','MS-CHAP-Response');
	my $rawResponse2 = $packet->vsattr('311','MS-CHAP2-Response');

	# Return if not recognized...
	return MOD_RES_SKIP if (!defined($rawChallenge) || (!defined($rawResponse) && !defined($rawResponse2)));

	$server->log(LOG_DEBUG,"[MOD_AUTH_MSCHAP] This is a MSCHAP challenge");

	# Grab our own version of the password
	my $unicodePassword;
	if (defined($user->{'Attributes'}->{'User-Password'})) {
		# Operator: ==
		if (defined($user->{'Attributes'}->{'User-Password'}->{'=='})) {
			# Set password
			$unicodePassword = $user->{'Attributes'}->{'User-Password'}->{'=='}->{'Value'};
			$unicodePassword =~ s/(.)/$1\0/g; # convert ASCII to unicaode
		} else {
			$server->log(LOG_NOTICE,"[MOD_AUTH_CHAP] No valid operators for attribute 'User-Password', ".
					"supported operators are: ==");
		}
	} else {
		$server->log(LOG_NOTICE,"[MOD_AUTH_CHAP] No 'User-Password' attribute, cannot authenticate");
		return MOD_RES_NACK;
	}

	# Grab usrename
	my $username = $user->{'Username'};
	if (!defined($username)) {
		$server->log(LOG_NOTICE,"[MOD_AUTH_CHAP] No 'Username' attribute in packet, cannot authenticate");
		return MOD_RES_NACK;
	}

	# MSCHAPv1
	if ($rawResponse) {
		$server->log(LOG_DEBUG,"[MOD_AUTH_MSCHAP] This is a MSCHAPv1 challenge");

		# Pull off challenge & response
		my $challenge = @{$rawChallenge}[0];
		my $response = substr(@{$rawResponse}[0],2);

		# Chop off NtResponse
		my $NtResponse = substr($response,24,24);

		# Generate our response
		my $ourResponse = NtChallengeResponse($challenge,$unicodePassword);


# MPPE Code
##################
		my $NtPasswordHash = NtPasswordHash($unicodePassword);
		my $HashNtPasswordHash = HashNtPasswordHash($NtPasswordHash);

		my $sendkey = pack("x8a16",$HashNtPasswordHash);
		my $recvkey;
		
		setReplyVAttribute($server,$user->{'ReplyVAttributes'}, {
			'Vendor' => 311,
			'Name' => 'MS-CHAP-MPPE-Keys',
			'Operator' => ":=",
			'Value' => $sendkey
		});
##################


		# Check responses match
		if ($NtResponse eq $ourResponse) {
			return MOD_RES_ACK;
		}


	# MSCHAPv2
	} elsif ($rawResponse2) {
		$server->log(LOG_DEBUG,"[MOD_AUTH_MSCHAP] This is a MSCHAPv2 challenge");

		# Pull off challenge & response
		my $challenge = @{$rawChallenge}[0];
		my $ident = unpack("C", substr(@{$rawResponse2}[0],0,1));
		my $response = substr(@{$rawResponse2}[0],2);

		# Grab peer challenge and response
		my $peerChallenge = substr($response,0,16);
		my $NtResponse = substr($response,24,24);

		# Generate our challenge and our response
		my $ourChallenge = ChallengeHash($peerChallenge,$challenge,$username);
		my $ourResponse = NtChallengeResponse($ourChallenge,$unicodePassword);

		# Check response match
		if ($NtResponse eq $ourResponse) {
			# Generate authenticator response
			my $authenticatorResponse = pack("C",$ident) . GenerateAuthenticatorResponse($unicodePassword,$ourResponse,
					$peerChallenge,$challenge,$username);

			# MPPE Code
################################

			my $NtPasswordHash = NtPasswordHash($unicodePassword);
			my $HashNtPasswordHash = HashNtPasswordHash($NtPasswordHash);

			# Create master key
			my $MasterKey = GetMasterKey($HashNtPasswordHash,$NtResponse);

			# Create MPPE keys
			my $mppe_sendKey = GetAsymmetricStartKey($MasterKey,16,1,1);
			my $mppe_recvKey = GetAsymmetricStartKey($MasterKey,16,1,0);


			# Generate salts ... this should be in its own module and salt_offset should be global
			my $salt_offset = 0;
			my $salt1 = pack("C2",(0x80 | ( (($salt_offset++) & 0x0f) << 3) |
   	            (rand(255) & 0x07)),rand(255));
			my $salt2 = pack("C2",(0x80 | ( (($salt_offset++) & 0x0f) << 3) |
   	            (rand(255) & 0x07)),rand(255));

			# Encode keys
			my $mppe_sendKey_e = mppe_encode_key(
					getAttributeValue($user->{'ConfigAttributes'},"SMRadius-Config-Secret"),
					$packet->authenticator,
					$salt1,
					$mppe_sendKey
			);
			my $mppe_recvKey_e = mppe_encode_key(
					getAttributeValue($user->{'ConfigAttributes'},"SMRadius-Config-Secret"),
					$packet->authenticator,
					$salt2,
					$mppe_recvKey
			);

			# Finally setup arguments
			setReplyVAttribute($server,$user->{'ReplyVAttributes'}, {
				'Vendor' => 311,
				'Name' => 'MS-MPPE-Recv-Key',
				'Operator' => ":=",
				'Value' => $mppe_recvKey_e
			});

			setReplyVAttribute($server,$user->{'ReplyVAttributes'}, {
				'Vendor' => 311,
				'Name' => 'MS-MPPE-Send-Key',
				'Operator' => ":=",
				'Value' => $mppe_sendKey_e
			});
#################################


			setReplyVAttribute($server,$user->{'ReplyVAttributes'}, {
				'Vendor' => 311,
				'Name' => 'MS-CHAP2-Success',
				'Operator' => ":=",
				'Value' => $authenticatorResponse
			});

			return MOD_RES_ACK;
		}

	}

	return MOD_RES_SKIP;
}



#GenerateNTResponse(
#	IN  16-octet              AuthenticatorChallenge,
#	IN  16-octet              PeerChallenge,
#	IN  0-to-256-char         UserName,
#	IN  0-to-256-unicode-char Password,
#	OUT 24-octet              Response )
#{
#	8-octet  Challenge
#	16-octet PasswordHash
#
#	ChallengeHash( PeerChallenge, AuthenticatorChallenge, UserName,
#       		giving Challenge)
#
#	NtPasswordHash( Password, giving PasswordHash )
#	ChallengeResponse( Challenge, PasswordHash, giving Response )
#}
sub GenerateNTResponse
{
	my ($AuthenticatorChallenge,$PeerChallenge,$UserName,$Password) = @_;


	my $Challenge = ChallengeHash($PeerChallenge,$AuthenticatorChallenge,$UserName);

	my $PasswordHash = NtPasswordHash($Password);
	my $Response = ChallengeResponse($Challenge,$PasswordHash);

	return $Response;
}



#ChallengeHash(
#	
#
#	/*
#	 * SHAInit(), SHAUpdate() and SHAFinal() functions are an
#	 * implementation of Secure Hash Algorithm (SHA-1) [11]. These are
#	 * available in public domain or can be licensed from
#	 * RSA Data Security, Inc.
#	 */
#
#	SHAInit(Context)
#	SHAUpdate(Context, PeerChallenge, 16)
#	SHAUpdate(Context, AuthenticatorChallenge, 16)
#
#	/*
#	 * Only the user name (as presented by the peer and
#	 * excluding any prepended domain name)
#	 * is used as input to SHAUpdate().
#	 */
#
#	SHAUpdate(Context, UserName, strlen(Username))
#	SHAFinal(Context, Digest)
#	memcpy(Challenge, Digest, 8)
#}
sub ChallengeHash
{
	my ($PeerChallenge,$AuthenticatorChallenge,$UserName) = @_;


	# SHA encryption
	my $sha = Digest::SHA1->new();
	$sha->add($PeerChallenge);
	$sha->add($AuthenticatorChallenge);
	$sha->add($UserName);

	my $digest = $sha->digest();
	# Cut off 8 bytes
	my $Challenge = substr($digest,0,8);

	return $Challenge;
}



#NtPasswordHash(
#	IN  0-to-256-unicode-char Password,
#	OUT 16-octet              PasswordHash)
#{
#	/*
#	 * Use the MD4 algorithm [5] to irreversibly hash Password
#	 * into PasswordHash.  Only the password is hashed without
#	 * including any terminating 0.
#	 */
#}
sub NtPasswordHash
{
	my $Password = shift;


	# Ieversibly hash Password
	my $PasswordHash = md4($Password);

	return $PasswordHash;
}



#HashNtPasswordHash(
#	IN  16-octet PasswordHash,
#	OUT 16-octet PasswordHashHash)
#{
#	/*
#	 * Use the MD4 algorithm [5] to irreversibly hash
#	 * PasswordHash into PasswordHashHash.
#	 */
#}
sub HashNtPasswordHash
{
	my $PasswordHash = shift;


	# Ieversibly hash PasswordHash
	my $PasswordHashHash = md4($PasswordHash);

	return $PasswordHashHash;
}



#ChallengeResponse(
#	IN  8-octet  Challenge,
#	IN  16-octet PasswordHash,
#	OUT 24-octet Response )
#{
#	Set ZPasswordHash to PasswordHash zero-padded to 21 octets
#
#	DesEncrypt( Challenge,
#		1st 7-octets of ZPasswordHash,
#		giving 1st 8-octets of Response )
#
#	DesEncrypt( Challenge,
#		2nd 7-octets of ZPasswordHash,
#		giving 2nd 8-octets of Response )
#
#	DesEncrypt( Challenge,
#		3rd 7-octets of ZPasswordHash,
#		giving 3rd 8-octets of Response )
#}
sub ChallengeResponse
{
	my ($Challenge,$PasswordHash) = @_;


	# Set ZPasswordHash to PasswordHash zero-padded to 21 octets
	my $ZPasswordHash = pack("a21", $PasswordHash);

	my @Response;
	# 1st 7-octets of ZPasswordHash giving 1st 8-octets of Response
	$Response[0] = DesEncrypt($Challenge,substr($ZPasswordHash,0,7));
	# 2nd 7-octets of ZPasswordHash giving 2nd 8-octets of Response
	$Response[1] = DesEncrypt($Challenge,substr($ZPasswordHash,7,7));
	# 3rd 7-octets of ZPasswordHash giving 3rd 8-octets of Response
	$Response[2] = DesEncrypt($Challenge,substr($ZPasswordHash,14,7));

	# Pack into Response
	my $Response = pack("a8a8a8",@Response);
	return $Response;
}



# Function to be used in DesEncrypt() to insert parity bits
sub str_to_keys
{
	my $str = shift;

	# Unpack string
	my $pack_str = unpack("B*",$str);
	# Add a )1
	$pack_str =~ s/(.......)/$1)1/g;
	# Remove the )
	$pack_str =~ s/\)//g;
	# Repack
	return pack("B*",$pack_str);
}



#DesEncrypt(
#	IN  8-octet Clear,
#	IN  7-octet Key,
#	OUT 8-octet Cypher)
#{
#	/*
#	 * Use the DES encryption algorithm [4] in ECB mode [10]
#	 * to encrypt Clear into Cypher such that Cypher can
#	 * only be decrypted back to Clear by providing Key.
#	 * Note that the DES algorithm takes as input a 64-bit
#	 * stream where the 8th, 16th, 24th, etc.  bits are
#	 * parity bits ignored by the encrypting algorithm.
#	 * Unless you write your own DES to accept 56-bit input
#	 * without parity, you will need to insert the parity bits
#	 * yourself.
#	 */
#}
sub DesEncrypt
{
	my ($Clear,$Key) = @_;

	my $des = Crypt::DES->new(str_to_keys($Key));
	my $Cypher = $des->encrypt($Clear);

	return $Cypher;
}



#GenerateAuthenticatorResponse(
#	IN  0-to-256-unicode-char Password,
#	IN  24-octet              NT-Response,
#	IN  16-octet              PeerChallenge,
#	IN  16-octet              AuthenticatorChallenge,
#	IN  0-to-256-char         UserName,
#	OUT 42-octet              AuthenticatorResponse)
#{
#	16-octet              PasswordHash
#	16-octet              PasswordHashHash
#	8-octet               Challenge
#
#	/*
#	 * "Magic" constants used in response generation
#	 */
#
#	Magic1[39] =
#		{0x4D, 0x61, 0x67, 0x69, 0x63, 0x20, 0x73, 0x65, 0x72, 0x76,
#		 0x65, 0x72, 0x20, 0x74, 0x6F, 0x20, 0x63, 0x6C, 0x69, 0x65,
#		 0x6E, 0x74, 0x20, 0x73, 0x69, 0x67, 0x6E, 0x69, 0x6E, 0x67,
#		 0x20, 0x63, 0x6F, 0x6E, 0x73, 0x74, 0x61, 0x6E, 0x74};
#
#	Magic2[41] =
#		{0x50, 0x61, 0x64, 0x20, 0x74, 0x6F, 0x20, 0x6D, 0x61, 0x6B,
#		 0x65, 0x20, 0x69, 0x74, 0x20, 0x64, 0x6F, 0x20, 0x6D, 0x6F,
#		 0x72, 0x65, 0x20, 0x74, 0x68, 0x61, 0x6E, 0x20, 0x6F, 0x6E,
#		 0x65, 0x20, 0x69, 0x74, 0x65, 0x72, 0x61, 0x74, 0x69, 0x6F,
#		 0x6E};
#
#	/*
#	 * Hash the password with MD4
#	 */
#
#	NtPasswordHash( Password, giving PasswordHash )
#
#	/*
#	 * Now hash the hash
#	 */
#
#	HashNtPasswordHash( PasswordHash, giving PasswordHashHash)
#
#	SHAInit(Context)
#	SHAUpdate(Context, PasswordHashHash, 16)
#	SHAUpdate(Context, NTResponse, 24)
#	SHAUpdate(Context, Magic1, 39)
#	SHAFinal(Context, Digest)
#
#	ChallengeHash( PeerChallenge, AuthenticatorChallenge, UserName,
#		giving Challenge)
#
#	SHAInit(Context)
#	SHAUpdate(Context, Digest, 20)
#	SHAUpdate(Context, Challenge, 8)
#	SHAUpdate(Context, Magic2, 41)
#	SHAFinal(Context, Digest)
#
#	/*
#	 * Encode the value of 'Digest' as "S=" followed by
#	 * 40 ASCII hexadecimal digits and return it in
#	 * AuthenticatorResponse.
#	 * For example,
#	 *   "S=0123456789ABCDEF0123456789ABCDEF01234567"
#	 */
#
#}
sub GenerateAuthenticatorResponse
{
	my ($Password,$NTResponse,$PeerChallenge,$AuthenticatorChallenge,$UserName) = @_;


	# "Magic" constants used in response generation - this is in hex
	my @Magic1 = 
		("4D", "61", "67", "69", "63", "20", "73", "65", "72", "76",
		 "65", "72", "20", "74", "6F", "20", "63", "6C", "69", "65",
		 "6E", "74", "20", "73", "69", "67", "6E", "69", "6E", "67",
		 "20", "63", "6F", "6E", "73", "74", "61", "6E", "74");
	my @Magic2 = 
		("50", "61", "64", "20", "74", "6F", "20", "6D", "61", "6B",
		 "65", "20", "69", "74", "20", "64", "6F", "20", "6D", "6F",
		 "72", "65", "20", "74", "68", "61", "6E", "20", "6F", "6E",
		 "65", "20", "69", "74", "65", "72", "61", "74", "69", "6F",
		 "6E");
	
	# Hash the password with MD4
	my $PasswordHash = NtPasswordHash($Password);

	# Now hash the hash
	my $PasswordHashHash = HashNtPasswordHash($PasswordHash);
		
	# SHA encryption
	my $sha = Digest::SHA1->new();
	$sha->add($PasswordHashHash);
	$sha->add($NTResponse);
	foreach my $item (@Magic1) {
		$sha->add(pack("H*",$item));
	}
	my $Digest = $sha->digest();

	my $Challenge = ChallengeHash($PeerChallenge, $AuthenticatorChallenge, $UserName);

	$sha = Digest::SHA1->new();
	$sha->add($Digest);
	$sha->add($Challenge);
	foreach my $item (@Magic2) {
		$sha->add(pack("H*",$item));
	}
	$Digest = $sha->digest();

	# Encode digest and return response, UPPERCASE response
	my $AuthenticatorResponse = "S=" . uc( unpack("H*",$Digest) );

	return $AuthenticatorResponse;
}




# CheckAuthenticatorResponse(
#	IN  0-to-256-unicode-char Password,
#	IN  24-octet              NtResponse,
#	IN  16-octet              PeerChallenge,
#	IN  16-octet              AuthenticatorChallenge,
#	IN  0-to-256-char         UserName,
#	IN  42-octet              ReceivedResponse,
#	OUT Boolean               ResponseOK)
#{
#	20-octet MyResponse
#
#	set ResponseOK = FALSE
#	GenerateAuthenticatorResponse( Password, NtResponse, PeerChallenge,
#		AuthenticatorChallenge, UserName,
#		giving MyResponse)
#
#	if (MyResponse = ReceivedResponse) then set ResponseOK = TRUE
#	return ResponseOK
#}
sub CheckAuthenticatorResponse
{
	my ($Password,$NtResponse,$PeerChallenge,$AuthenticatorChallenge,$UserName,$ReceivedResponse) = @_;


	# Generate response
	my $MyResponse = GenerateAuthenticatorResponse($Password,$NtResponse,$PeerChallenge,$AuthenticatorChallenge,$UserName);

	# Check if it matches
	if ($MyResponse eq $ReceivedResponse) {
		return 1;
	} else {
		return 0;
	}
}



#datatype-PWBLOCK
#{
#	256-unicode-char Password
#	4-octets         PasswordLength
#}
#
#NewPasswordEncryptedWithOldNtPasswordHash(
#	IN  0-to-256-unicode-char NewPassword,
#	IN  0-to-256-unicode-char OldPassword,
#	OUT datatype-PWBLOCK      EncryptedPwBlock )
#{
#	NtPasswordHash( OldPassword, giving PasswordHash )
#
#	EncryptPwBlockWithPasswordHash(NewPassword,
#		PasswordHash,
#		giving EncryptedPwBlock)
#}
sub NewPasswordEncryptedWithOldNtPasswordHash
{
	my ($NewPassword,$OldPassword) = @_;


	my $PasswordHash = NtPasswordHash($OldPassword);
	my $EncryptedPwBlock = EncryptPwBlockWithPasswordHash($NewPassword,$PasswordHash);

	return $EncryptedPwBlock;
}



#EncryptPwBlockWithPasswordHash(
#	IN  0-to-256-unicode-char Password,
#	IN  16-octet              PasswordHash,
#	OUT datatype-PWBLOCK      PwBlock )
#{
#
#	Fill ClearPwBlock with random octet values
#
#	PwSize = lstrlenW( Password ) * sizeof( unicode-char )
#	PwOffset = sizeof( ClearPwBlock.Password ) - PwSize
#	Move PwSize octets to (ClearPwBlock.Password + PwOffset ) from Password
#	ClearPwBlock.PasswordLength = PwSize
#	Rc4Encrypt( ClearPwBlock,
#		sizeof( ClearPwBlock ),
#		PasswordHash,
#		sizeof( PasswordHash ),
#		giving PwBlock )
#}
sub EncryptPwBlockWithPasswordHash
{
	my ($Password,$PasswordHash) = @_;

# TODO

	# Its unicode, the size is two bytes wide
	# Fill ClearPwBlock with random octet values
	my @rawClearPwBlock;
	for (my $i = 0; $i < (MSCHAPV2_MAXPWLEN * 2); $i++) {
		push(@rawClearPwBlock, int(rand(256)));
	}
	my $ClearPwBlock = pack("C*",@rawClearPwBlock);

	my $PwSize = length($Password);
	my $PwOffset = length($ClearPwBlock) - $PwSize;

	# Move PwSize octets to (ClearPwBlock.Password + PwOffset ) from Password
	@rawClearPwBlock = unpack("C*",$ClearPwBlock);
	my @rawPassword = unpack("C*",$Password);
	for (my $i = 0; $i < ($PwSize * 2); $i++) {
		$rawClearPwBlock[$PwOffset + $i] = $rawPassword[$i];
	}

	# Pack into 256 x unicode (2 byte) + 32-bit wide counter
	my $ClearBlock = pack("C".(MSCHAPV2_MAXPWLEN * 2)."L",@rawClearPwBlock,$PwSize);

	# Encrypt
	my $rc4 = Crypt::RC4->new($ClearBlock);
	my $PwBlock = $rc4->RC4($PasswordHash);

	return $PwBlock;
}



#Rc4Encrypt(
#	IN  x-octet Clear,
#	IN  integer ClearLength,
#	IN  y-octet Key,
#	IN  integer KeyLength,
#	OUT x-octet Cypher )
#{
#	/*
#	 * Use the RC4 encryption algorithm [6] to encrypt Clear of
#	 * length ClearLength octets into a Cypher of the same length
#	 * such that the Cypher can only be decrypted back to Clear
#	 * by providing a Key of length KeyLength octets.
#	 */
#}
sub Rc4Encrypt
{
	my ($Clear,$ClearLength,$Key,$KeyLength) = @_;


	my $rc4 = Crypt::RC4->new(substr($Key,0,$KeyLength));
	my $Cypher = $rc4->encrypt(substr($Clear,0,$ClearLength));

	return $Cypher;
}



#OldNtPasswordHashEncryptedWithNewNtPasswordHash(
#	IN  0-to-256-unicode-char NewPassword,
#	IN  0-to-256-unicode-char OldPassword,
#	OUT 16-octet              EncryptedPasswordHash)
#{
#	NtPasswordHash( OldPassword, giving OldPasswordHash )
#	NtPasswordHash( NewPassword, giving NewPasswordHash )
#	NtPasswordHashEncryptedWithBlock( OldPasswordHash,
#		NewPasswordHash,
#		giving EncryptedPasswordHash )
#}
sub OldNtPasswordHashEncryptedWithNewNtPasswordHash
{
	my ($NewPassword,$OldPassword) = @_;


	my $OldPasswordHash = NtPasswordHash($OldPassword);
	my $NewPasswordHash = NtPasswordHash($NewPassword);

	my $EncryptedPasswordHash = NtPasswordHashEncryptedWithBlock($OldPasswordHash,$NewPasswordHash);

	return $EncryptedPasswordHash;
}



#NtPasswordHashEncryptedWithBlock(
#	IN  16-octet PasswordHash,
#	IN  16-octet Block,
#	OUT 16-octet Cypher )
#{
#	DesEncrypt( 1st 8-octets PasswordHash,
#		1st 7-octets Block,
#		giving 1st 8-octets Cypher )
#
#	DesEncrypt( 2nd 8-octets PasswordHash,
#		2nd 7-octets Block,
#		giving 2nd 8-octets Cypher )
#}
sub NtPasswordHashEncryptedWithBlock
{
	my ($PasswordHash,$Block) = @_;


	my @Response;
	# 1st 8-octets PasswordHash, 1st 7-octets Block
	$Response[0] = DesEncrypt(substr($PasswordHash,0,8),substr($Block,0,7));
	# 2nd 8-octets PasswordHash, 2nd 7-octets Block
	$Response[1] = DesEncrypt(substr($PasswordHash,8,8),substr($Block,7,7));

	# Pack into Cypher
	my $Cypher = pack("a8a8",@Response);
	return $Cypher;
}



# Function to hash the password and create a challenge response,
# this is a little short from GenerateNTResponse as we already
# have the challenge
sub NtChallengeResponse {
	my ($Challenge,$Password) = @_;


	my $PasswordHash = NtPasswordHash($Password);
	my $Response = ChallengeResponse($Challenge,$PasswordHash);

	return $Response;
}



#
# RFC 3079
#

#GetMasterKey(
#	IN  16-octet  PasswordHashHash,
#	IN  24-octet  NTResponse,
#	OUT 16-octet  MasterKey )
#{
#	20-octet Digest
#
#	ZeroMemory(Digest, sizeof(Digest));
#
#	/*
#	 * SHSInit(), SHSUpdate() and SHSFinal()
#	 * are an implementation of the Secure Hash Standard [7].
#	 */
#
#	SHSInit(Context);
#	SHSUpdate(Context, PasswordHashHash, 16);
#	SHSUpdate(Context, NTResponse, 24);
#	SHSUpdate(Context, Magic1, 27);
#	SHSFinal(Context, Digest);
#
#	MoveMemory(MasterKey, Digest, 16);
#}
sub GetMasterKey
{
	my ($PasswordHashHash,$NTResponse) = @_;


	# "Magic" constants used in key derivations - in hex
	my @Magic1 =
		("54", "68", "69", "73", "20", "69", "73", "20", "74",
		 "68", "65", "20", "4d", "50", "50", "45", "20", "4d",
		 "61", "73", "74", "65", "72", "20", "4b", "65", "79");

	my $sha = Digest::SHA1->new();
	$sha->add($PasswordHashHash);
	$sha->add($NTResponse);
	foreach my $item (@Magic1) {
		$sha->add(pack("H*",$item));
	}
	my $Digest = $sha->digest();
	# Cut off MasterKey
	my $MasterKey = substr($Digest,0,16);

	return $MasterKey;
}




#VOID
#GetAsymetricStartKey(
#	IN   16-octet      MasterKey,
#	OUT  8-to-16 octet SessionKey,
#	IN   INTEGER       SessionKeyLength,
#	IN   BOOLEAN       IsSend,
#	IN   BOOLEAN       IsServer )
#{
#
#	20-octet Digest;
#
#	ZeroMemory(Digest, 20);
#
#	if (IsSend) {
#		if (IsServer) {
#			s = Magic3
#		} else {
#			s = Magic2
#		}
#	} else {
#		if (IsServer) {
#			s = Magic2
#		} else {
#			s = Magic3
#		}
#	}
#
#	/*
#	 * SHSInit(), SHSUpdate() and SHSFinal()
#	 * are an implementation of the Secure Hash Standard [7].
#	 */
#
#	SHSInit(Context);
#	SHSUpdate(Context, MasterKey, 16);
#	SHSUpdate(Context, SHSpad1, 40);
#	SHSUpdate(Context, s, 84);
#	SHSUpdate(Context, SHSpad2, 40);
#	SHSFinal(Context, Digest);
#
#	MoveMemory(SessionKey, Digest, SessionKeyLength);
#}
sub GetAsymmetricStartKey
{
	my ($MasterKey,$SessionKeyLength,$IsSend,$IsServer) = @_;


	# "Magic" constants used in key derivations - in hex
	my @Magic2 =
		("4f", "6e", "20", "74", "68", "65", "20", "63", "6c", "69",
		 "65", "6e", "74", "20", "73", "69", "64", "65", "2c", "20",
		 "74", "68", "69", "73", "20", "69", "73", "20", "74", "68",
		 "65", "20", "73", "65", "6e", "64", "20", "6b", "65", "79",
		 "3b", "20", "6f", "6e", "20", "74", "68", "65", "20", "73",
		 "65", "72", "76", "65", "72", "20", "73", "69", "64", "65",
		 "2c", "20", "69", "74", "20", "69", "73", "20", "74", "68",
		 "65", "20", "72", "65", "63", "65", "69", "76", "65", "20",
		 "6b", "65", "79", "2e");

	my @Magic3 =
		("4f", "6e", "20", "74", "68", "65", "20", "63", "6c", "69",
		 "65", "6e", "74", "20", "73", "69", "64", "65", "2c", "20",
		 "74", "68", "69", "73", "20", "69", "73", "20", "74", "68",
		 "65", "20", "72", "65", "63", "65", "69", "76", "65", "20",
		 "6b", "65", "79", "3b", "20", "6f", "6e", "20", "74", "68",
		 "65", "20", "73", "65", "72", "76", "65", "72", "20", "73",
		 "69", "64", "65", "2c", "20", "69", "74", "20", "69", "73",
		 "20", "74", "68", "65", "20", "73", "65", "6e", "64", "20",
		 "6b", "65", "79", "2e");

	# Pads used in key derivation - in hex
	my @SHSpad1 =
		("00", "00", "00", "00", "00", "00", "00", "00", "00", "00",
		 "00", "00", "00", "00", "00", "00", "00", "00", "00", "00",
		 "00", "00", "00", "00", "00", "00", "00", "00", "00", "00",
		 "00", "00", "00", "00", "00", "00", "00", "00", "00", "00");

	my @SHSpad2 =
		("f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2",
		 "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2",
		 "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2",
		 "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2", "f2");

	my @s;
	if ($IsSend) {
		if ($IsServer) {
			@s = @Magic3;
		} else {
			@s = @Magic2;
		}
	} else {
		if ($IsServer) {
			@s = @Magic2;
		} else {
			@s = @Magic3;
		}
	}

	my $sha = Digest::SHA1->new();
	$sha->add($MasterKey);
	foreach my $item (@SHSpad1) {
		$sha->add(pack("H*",$item));
	}
	foreach my $item (@s) {
		$sha->add(pack("H*",$item));
	}
	foreach my $item (@SHSpad2) {
		$sha->add(pack("H*",$item));
	}
	my $digest = $sha->digest();
	# Cut off SessionKey
	my $SessionKey = substr($digest,0,$SessionKeyLength);

	return $SessionKey;
}


# Function to encode a key
sub mppe_encode_key
{
	my ($secret,$vector,$salt,$enckey) = @_;


	# Ok, to do this we need the length of the key first
	my @plain = (
		16, # Length
		unpack("C*",pack("a31",$enckey))
	);
	
	# Create our first digest
	my $sha = Digest::MD5->new();
	$sha->add($secret);
	$sha->add($vector);
	$sha->add($salt);
	# Unpack digest for calculation
	my @buf = unpack("C*",$sha->digest());

	# Calculate
	for(my $i=0; $i < 16; $i++) {
		$plain[$i] ^= $buf[$i];
	}

	# Second round
	$sha = Digest::MD5->new();
	$sha->add($secret);
	# Add the values we calculated above
	for (my $i = 0; $i < 16; $i++) {
		$sha->add(pack("C",$plain[$i]));
	}
	# Unpack digest for calculation
	@buf = unpack("C*",$sha->digest());

	# Calculate
	for (my $i = 0; $i < 16; $i++) {
		$plain[$i+16] ^= $buf[$i];
	}
	# Pack salt, and result
	my $key = pack("a2C32",$salt,@plain);

	return $key;
}


1;
# vim: ts=4
