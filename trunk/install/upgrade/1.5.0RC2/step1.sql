#
#
#
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'login_redirect_message', 'You will be redirected to {pageid} page once you have successfully logged in');

#
# reinstate delete user for not activated users only
#
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'delete_user', 'Delete User');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'confirm_user_delete', 'Are you sure you want to delete user \"{fullname}\" ({user_id})?');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'user_not_deleted', 'User not deleted');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'user_deleted', 'User deleted');

UPDATE s_language_var
SET value = 'Hi {admin_name},\\n\\nThe following user has requested to become a member of {site}.\\n\\n{user_info}\\n\\nYou can use this URL to activate the user:\\n{activate_url}\\n\\nYou can use this URL to delete the user:\\n{delete_url}'
WHERE varname = 'new_account_email' AND
language = 'ENGLISH';

