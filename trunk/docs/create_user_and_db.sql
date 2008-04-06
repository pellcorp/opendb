#
# Script to create a new OpenDB 'lender' User and 'opendb' Database
#
# Execute at Mysql 'root' user:
# 	mysql mysql -u root -p < docs/create_user_and_db.sql
#
# USE THIS SCRIPT AT YOUR OWN RISK.  I WILL NOT BE HELD RESPONSIBLE
# Jason Pell - 3rd May 2003.
#

#
# Cteate a Opendb user 'lender', with password 'test'
#
INSERT INTO user (Host, User, Password) 
VALUES ('localhost', 'lender', '378b243e220ca493');

#
# Configure permissions for 'lender' user for a database 'opendb'.  Access is limited to 'localhost', so
# the database server and Apache/PHP server must be on the same server.
# 
INSERT INTO db (Host, Db, User, Select_priv, Insert_priv, Update_priv, Delete_priv, Create_priv, Drop_priv, Grant_priv, References_priv, Index_priv, Alter_priv, Lock_tables_priv) 
VALUES ('localhost', 'opendb', 'lender', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');

#
# Create the 'opendb' database.
#
CREATE DATABASE opendb;

#
# Flush privileges so that the new user/db are recognised.
#
FLUSH PRIVILEGES;

