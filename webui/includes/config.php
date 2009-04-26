<?php
# Web Admin UI Config
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



# mysql:host=xx;dbname=yyy
#
# pgsql:host=xx;dbname=yyy
#
# sqlite:////full/unix/path/to/file.db?mode=0666
#
#$DB_DSN="sqlite:////tmp/cluebringer.sqlite";
$DB_DSN="mysql:host=localhost;dbname=smradius";
$DB_USER="root";
$DB_PASS="root";
#$DB_PASS="";
$DB_TABLE_PREFIX="";


#
# THE BELOW SECTION IS UNSUPPORTED AND MEANT FOR THE ORIGINAL SPONSOR OF V2
#

#$DB_POSTFIX_DSN="mysql:host=localhost;dbname=postfix";
#$DB_POSTFIX_USER="root";
#$DB_POSTFIX_PASS="";

?>
