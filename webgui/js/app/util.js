/* Generate a random password */
function getRandomPass(len) {
	var keylist="abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789"
	var ret=''


	for (i = 0; i < len; i++)
		ret += keylist.charAt( Math.floor(Math.random() * keylist.length) )

	return ret
}

