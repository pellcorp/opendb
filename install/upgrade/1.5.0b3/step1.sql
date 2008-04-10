INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'cannot_checkout_items_you_own', 'You cannot checkout an item you own');

#
# remove user delete functionality in favour of simply deactivating users and not display 
# deactivated users items, stats, etc.  Greatly simplifies logic surrounding what to 
# display when a user is deleted.  Essentially a deactivated user is a logically deleted 
# user.
#
DELETE FROM s_language_var WHERE varname IN (
	'delete_user',
	'confirm_user_delete',
	'confirm_user_delete_deactivate',
	'user_deleted',
	'cannot_delete_yourself');

DELETE FROM s_config_group_item
WHERE group_id = 'user_admin' AND id IN (
	'user_delete_with_reviews',
	'user_delete_with_borrower_inactive_borrowed_items',
	'user_delete_with_owner_inactive_borrowed_items');

DELETE FROM s_config_group_item_var
WHERE group_id = 'user_admin' AND id IN (
	'user_delete_with_reviews',
	'user_delete_with_borrower_inactive_borrowed_items',
	'user_delete_with_owner_inactive_borrowed_items');

UPDATE s_language_var
SET value = 'Hi {admin_name},\\n\\nThe following user has requested to become a member of {site}.\\n\\n{user_info}\\n\\nYou can use this URL to activate the user:\\n{activate_url}'
WHERE varname = 'new_account_email' AND
language = 'ENGLISH';

