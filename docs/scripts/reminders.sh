#!/bin/bash

OPENDB_URL=http://opendb.iamvegan.net/demo
OPENDB_ADMIN_UID=admin
OPENDB_ADMIN_PWD=admin

LOGFILE=log.$$
OPENDB_ADMIN_EMAIL=opendb@iamvegan.net

lynx -accept_all_cookies -dump -dont_wrap_pre "$OPENDB_URL/login.php?op=login&uid=$OPENDB_ADMIN_UID&passwd=$OPENDB_ADMIN_PWD&redirect=item_borrow.php?op%3Dadmin_send_reminders%26mode%3Djob" > /tmp/$LOGFILE

mail -s "Borrower Reminders Log `date +%d/%m/%y`" $OPENDB_ADMIN_EMAIL < /tmp/$LOGFILE

rm /tmp/$LOGFILE
