#
# 1.0.1 -> 1.1 update script step 1
#

CREATE TABLE item_instance_relationship (
    sequence_number INT( 10 ) NOT NULL AUTO_INCREMENT,
    item_id INT( 10 ) NOT NULL,
    instance_no SMALLINT( 5 ) NOT NULL,
    related_item_id INT( 10 ) NOT NULL,
    related_instance_no SMALLINT( 5 ) NOT NULL,
PRIMARY KEY ( sequence_number ),
UNIQUE KEY ( item_id, instance_no, related_item_id, related_instance_no )
) TYPE=MyISAM COMMENT = 'item instance relationship table';

# 1.0 RC phase removal of language vars
DELETE FROM s_language_var WHERE varname IN (
	'do_it_yourself',
	'you_have_several_choices',
	'try_again_later',
	'owner_information',
	'itemtype_items',
	'itemtype_ownership',
	'itemtype_category',
	'delimiter',
	'title_exists',
	'specify_title',
	'import_progress_message',
	'reservation_cancelled',
	'item_in_reserve_list',
	'update_reserve_list',
	'item_check_in',
	'item_check_out',
	'check_in',
	'check_out',
	'session_invalid',
	'session_has_expired',
	'login_successful',
	'uid_is_logged_in',
	'list_other_items',
	'list_all_users',
	'log_cleared',
	'log_not_cleared',
	'log_file',
	'user_updated',
	'change_passwd',
	'filename_is_not_valid',
	'file_not_found',
	'upload_prompt',
	'saveurl_prompt',
	'search_query',
	'submitted',
	'display_days',
	'show_old_announcements',
	'update_status',
	'not_reviewed',
	'borrow_item',
	'external_header_text',
	'add_item_instance',
	'item_instance_change_owner_title',
	'operation_not_avail_linked_items',
	'url_save_error',
	'invalid_url_error',
	'item_instance_owner_changed',
	'item_instance_updated',
	'item_status_updated',
	'patch_facility',
	'printable_version_notes',
	'item_status_not_updated',
	'clone_title',
	's_status_type_borrow_duration_not_supported',
	'confirm_item_instance_insert',
	'confirm_item_instance_update',
	'confirm_item_instance_change_owner',
	'invalid_s_item_type',
	'invalid_s_item_type_attribute_type',
	'confirm_clone_item',
	'site_login',
	'print_item_cover',
	'item_search',
	'tbl_dump_header'
);

# delete linked item vars, replace with related item vars

DELETE FROM s_language_var WHERE language = 'ENGLISH' AND 
varname IN(
	'confirm_title_linked_item_insert', 
	'parent_id', 
	'parent_item_not_found', 
	'linked_item(s)', 
	'add_linked_item', 
	'coerce_child_item_types',
	'delete_linked_item',
	'edit_linked_item',
	'edit_parent');

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'related_item(s)', 'Related Item(s)'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'related_parent_item(s)', 'Related Parent Item(s)');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'no_related_item(s)', 'No Related Item(s)'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'add_related_item', 'Add Related Item'); 

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'clone_title', 'Clone {display_title}'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'clone_item_help', 'Related items will not be cloned'); 

# delete configuration entries for linked item support.

DELETE FROM s_config_group_item WHERE group_id = 'listings' AND id IN('linked_items');
DELETE FROM s_config_group_item WHERE group_id = 'search' AND id IN('default_include_linked_items');
DELETE FROM s_config_group_item WHERE group_id = 'item_input' AND id IN(
	'linked_item_support', 
	'link_same_type_only', 
	'confirm_duplicate_linked_item_insert',
	'confirm_linked_item_delete',
	'new_instance_admin_diff_owner_support',
	'clone_item_admin_diff_owner_support');

DELETE FROM s_config_group_item_var WHERE group_id = 'listings' AND id IN('linked_items');
DELETE FROM s_config_group_item_var WHERE group_id = 'search' AND id IN('default_include_linked_items');
DELETE FROM s_config_group_item_var WHERE group_id = 'item_input' AND id IN(
	'linked_item_support', 
	'link_same_type_only', 
	'confirm_duplicate_linked_item_insert',
	'confirm_linked_item_delete',
	'new_instance_admin_diff_owner_support',
	'clone_item_admin_diff_owner_support');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'related_item_support', 4, 'Related Item Support', '', 'boolean');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'related_item_support', 'TRUE');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('site.public_access', 'enabled_pages', 3, 'Enabled Pages', 'If the list is empty, standard guest user restrictions will apply.  Otherwise only pages listed will be accessible while public access is in effect.', 'array', 'text');

# move announcements datetime mask into login.announcements section which is only place its really used.
UPDATE s_config_group_item SET group_id = 'login.announcements', order_no = 3 WHERE group_id = 'announcements' AND id = 'datetime_mask';
UPDATE s_config_group_item_var SET group_id = 'login.announcements' WHERE group_id = 'announcements' AND id = 'datetime_mask';
DELETE FROM s_config_group WHERE id = 'announcements';

INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'feeds', 16, 'Feeds', 'Feeds configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'feeds.new_items', 1, 'New Items Feed', 'New Items Feed configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'feeds.announcements', 2, 'Announcements Feed', 'Announcements Feed configuration' );

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('feeds.new_items', 'total_num_items', 1, 'Total Items to List', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('feeds.announcements', 'total_num_items', 1, 'Total Announcements to List', '', 'number');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('feeds.new_items', 'total_num_items', '18');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('feeds.announcements', 'total_num_items', '5');

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'item_related_to_other_items', 'This item is related to one or more other items.'); 

ALTER TABLE item_attribute ADD INDEX lookup_attribute_val_idx ( lookup_attribute_val );

# action links are not printable
UPDATE s_item_listing_column_conf SET printable_support_ind = 'N' WHERE column_type = 'action_links';

# add default orderby support
ALTER TABLE s_item_listing_column_conf ADD orderby_sort_order VARCHAR(4);
ALTER TABLE s_item_listing_column_conf ADD orderby_default_ind VARCHAR(1) NOT NULL DEFAULT 'N';

# remove option to have B and X borrow indicators
UPDATE s_status_type SET borrow_ind = 'N' WHERE borrow_ind IN ('X', 'B');

# status comments can always be populated but will be invisible if 'N'
UPDATE s_status_type SET status_comment_ind = 'N' WHERE status_comment_ind  = 'H';

# insert / update indicators no longer supported
ALTER TABLE s_status_type DROP update_ind;
ALTER TABLE s_status_type DROP insert_ind;

# moving email address back to user table, as it is always compulsory.  will also
# make it easier to work with bridges to other software too.
ALTER TABLE user ADD email_addr	VARCHAR(255);

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'email', 'Email'); 

# allow null pwd - prevents logins
ALTER TABLE user CHANGE pwd pwd VARCHAR(40);
