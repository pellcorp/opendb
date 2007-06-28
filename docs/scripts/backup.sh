#!/bin/bash

#
filename="opendb-backup-`date +%d%m%y`.sql"

OPENDB_URL=http://opendb.iamvegan.net/demo
OPENDB_ADMIN_UID=admin
OPENDB_ADMIN_PWD=admin
OPENDB_ADMIN_EMAIL=opendb@iamvegan.net

lynx -accept_all_cookies -dump -dont_wrap_pre "$OPENDB_URL/login.php?op=login&uid=$OPENDB_ADMIN_UID&passwd=$OPENDB_ADMIN_PWD&redirect=admin.php%3Ftype%3Dbackup%26op%3Dexport%26all_tables%3Dy%26mode%3Djob" > /tmp/$filename

# Compress sql
gzip -c /tmp/$filename > /tmp/${filename}.gz
rm /tmp/$filename

# Encode and Send email
uuencode /tmp/${filename}.gz ${filename}.gz > /tmp/${filename}.gz.enc
rm /tmp/${filename}.gz

mail -s "OpenDb Backup" $OPENDB_ADMIN_EMAIL < /tmp/${filename}.gz.enc  
rm /tmp/${filename}.gz.enc

