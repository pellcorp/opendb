set LYNX_HOME="c:\Program Files\Lynx\"
set OPENDB_URL=http://opendb.iamvegan.net/demo1.0
set OPENDB_ADMIN_UID=admin
set OPENDB_ADMIN_PWD=admin

set LOGFILE=c:\OpenDb_Reminder.log
set OPENDB_ADMIN_EMAIL=opendb@iamvegan.net

%LYNX_HOME%\lynx -accept_all_cookies -dump -dont_wrap_pre "%OPENDB_URL%/login.php?op=login&uid=%OPENDB_ADMIN_UID%&passwd=%OPENDB_ADMIN_PWD%&redirect=item_borrow.php?op%3Dadmin_send_reminders%26mode%3Djob" > %LOGFILE%

REM mail -s "Borrower Reminders Log `date +%d/%m/%y`" %OPENDB_ADMIN_EMAIL% < %LOGFILE%
REM rm %LOGFILE%
