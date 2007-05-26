#!/bin/bash

#
filename="opendb-backup-`date +%d%m%y`.sql"

lynx -accept_all_cookies -dump -dont_wrap_pre 'http://opendb.iamvegan.net/demo/login.php?op=login&uid=<opendb_admin_uid>&passwd=<opendb_admin_password>&redirect=backup.php%3Fop%3Dexport%26all_tables%3Dy%26send_as_format%3Dfile' > /tmp/$filename

# Compress sql
gzip -c /tmp/$filename > /tmp/${filename}.gz
rm /tmp/$filename

# Encode and Send email
uuencode /tmp/${filename}.gz ${filename}.gz > /tmp/${filename}.gz.enc
rm /tmp/${filename}.gz

mail -s "OpenDb Backup" opendb@iamvegan.net < /tmp/${filename}.gz.enc  
rm /tmp/${filename}.gz.enc

