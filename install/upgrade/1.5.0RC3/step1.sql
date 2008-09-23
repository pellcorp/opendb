#
# bug in new installation for 1.5RC2 did not update this var
#

UPDATE s_language_var
SET value = 'Hi {admin_name},\\n\\nThe following user has requested to become a member of {site}.\\n\\n{user_info}\\n\\nYou can use this URL to activate the user:\\n{activate_url}\\n\\nYou can use this URL to delete the user:\\n{delete_url}'
WHERE varname = 'new_account_email' AND
language = 'ENGLISH';