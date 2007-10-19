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
	'edit_parent',
	'email_site_administrator');

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'related_item(s)', 'Related Item(s)'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'related_parent_item(s)', 'Related Parent Item(s)');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'no_related_item(s)', 'No Related Item(s)'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'add_related_item', 'Add Related Item'); 

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'clone_title', 'Clone {display_title}'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'clone_item_help', 'Related items will not be cloned'); 

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'noreply', 'No Reply');

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'email_administrator', 'Email Administrator');

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

DELETE FROM s_config_group_item WHERE group_id = 'item_display' AND id = 'tabbed_layout';
DELETE FROM s_config_group_item_var WHERE group_id = 'item_display' AND id = 'tabbed_layout';

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

#
# Mailbox for audit of all email sent from within opendb.
#
CREATE TABLE mailbox (
    sequence_number INT( 10 ) NOT NULL AUTO_INCREMENT,
    sent			TIMESTAMP(14) NOT NULL,
    to_user_id	 	VARCHAR(20) NOT NULL,
    from_user_id 	VARCHAR(20),
    from_email_addr	VARCHAR(255),
    subject			VARCHAR(100),
    message			TEXT,
PRIMARY KEY ( sequence_number )
) TYPE=MyISAM COMMENT = 'mailbox';

INSERT INTO s_title_display_mask ( id, description ) VALUES ( 'feeds', 'RSS Feeds' );

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('feeds', '*', 'GAME', '"{title}"{if(instance_no>1," #{instance_no}")}');

# resolve imdb image not showing issue.
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '2', 'ia.media-imdb.com');

# provide option to set a theme content type character set
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site.theme', 4, 'Theme Configuration', 'Configure themes' );
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('site.theme', 'charset', 1, 'Content Type Charset', 'This setting will force a meta http-equiv Content Type header to be included in the source of each page.', 'value_select', ',utf-8,iso-8859-1');

UPDATE s_language_var SET VALUE = 'Import {description}' WHERE language = 'ENGLISH' AND varname = 'type_import';
UPDATE s_language_var SET VALUE = 'Import {description} for {fullname} ({user_id})' WHERE language = 'ENGLISH' AND varname = 'type_import_items_for_name';

UPDATE s_language_var SET VALUE = 'Export {description}' WHERE language = 'ENGLISH' AND varname = 'type_export';
UPDATE s_language_var SET VALUE = 'Export {description} {title} (item_id={item_id})' WHERE language = 'ENGLISH' AND varname = 'type_export_for_item';
UPDATE s_language_var SET VALUE = 'Export {description} {title} (item_id={item_id},instance_no={instance_no})' WHERE language = 'ENGLISH' AND varname = 'type_export_for_item_instance';
UPDATE s_language_var SET VALUE = 'Export {description} {s_item_type} item(s)' WHERE language = 'ENGLISH' AND varname = 'type_export_for_item_type';
UPDATE s_language_var SET VALUE = 'Export {description} for {fullname} ({user_id})' WHERE language = 'ENGLISH' AND varname = 'type_export_for_name';
UPDATE s_language_var SET VALUE = 'Export {description} {s_item_type} item(s) for {fullname} ({user_id})' WHERE language = 'ENGLISH' AND varname = 'type_export_for_name_item_type';

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'export_item_record', 'Export Item');
DELETE FROM s_language_var WHERE varname = 'type_export_item_record';

# this particular help no longer required.
DELETE FROM s_language_var WHERE varname = 'listing_column_header_sort_help';

# remove no rating from item display
DELETE FROM s_language_var WHERE varname = 'no_rating';

DELETE FROM s_language_var WHERE varname = 'item_is_already_selected';

DELETE FROM s_language_var WHERE varname = 'continue';

# removed functionality to disable user delete / deactivate, admin should be allowed to do either.
DELETE FROM s_language_var WHERE varname IN('user_deactivate_not_supported', 'user_delete_not_supported');
DELETE FROM s_config_group_item WHERE group_id = 'user_admin' AND id IN ('user_deactivate_support', 'user_delete_support');
DELETE FROM s_config_group_item_var WHERE group_id = 'user_admin' AND id IN ('user_deactivate_support', 'user_delete_support');

# renamed export plugin
UPDATE s_config_group_item_var SET value = 'OpenDbExportPlugin' WHERE group_id = 'item_display' AND id = 'export_link';

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'alphalist_new_search_context', 18, 'Alpha List New Search Context', 'AlphaList should start a new search instead of further refining current search.', 'boolean');

INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'borrow.checkout', 2, 'Item Borrow Checkouts / Checkins', 'Borrow check in / check out configuration' );
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow.checkout', 'alt_id_attribute_type', 1, 'Alt ID Attribute Type', 'The attribute type that will store the alternate ID used for auto checkin / checkout', 'instance_attribute_type');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow.checkout', 'alt_id_attribute_type', 'S_ITEM_ID');

DELETE FROM s_config_group_item WHERE group_id = 'listings' AND id IN ('save_listing_url', 'user_email_link');
DELETE FROM s_config_group_item_var WHERE group_id = 'listings' AND id IN ('save_listing_url', 'user_email_link');

