#!/bin/bash

#
# Defined variables
#
LOGFILE=log.$$
MAIL=opendb@iamvegan.net

lynx -accept_all_cookies -dump -dont_wrap_pre 'http://opendb.iamvegan.net/demo/login.php?op=login&uid=<opendb_admin_uid>&passwd=<opendb_admin_password>&redirect=item_borrow.php?op%3Dadmin_send_reminders%26mode%3Djob' > /tmp/$LOGFILE

mail -s "Borrower Reminders Log `date +%d/%m/%y`" $MAIL < /tmp/$LOGFILE

rm /tmp/$LOGFILE
