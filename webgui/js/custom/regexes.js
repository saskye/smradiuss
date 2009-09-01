// Domain name
var domainRe = /[a-z0-9\.\-]/;


// Local part of email address regex (or mask in this case)
var emailLocalPartRe = /[a-z0-9\._\+&]/;
// Full email address
var emailAddressRe = /[a-z0-9\._\+&@\-]/;

// Typical username
var usernamePartRe = /[a-z0-9\._]/;
// Radius username allows @
var usernameRadiusPartRe = /[a-z0-9\._\-@]/;


//  username
var usernameRe = /[a-z0-9\._\-]/;
