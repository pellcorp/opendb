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
	'tbl_dump_header',
	'backup_database',
	'column',
	'choose_export_columns'
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
	'email_site_administrator',
	'link',
	'linked_item',
	'linked_item(s)',
	'linked_items_cannot_be_reserved',
	'linked_items_not_supported',
	'linked_item_must_be_type',
	'linked_item_not_found',
	'no_linked_items',
	'refresh_linked_title',
	'title_linked_item_exists',
	'update_linked_item');

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

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'advanced_search', 'Advanced Search');

INSERT INTO s_title_display_mask ( id, description ) VALUES ( 'feeds', 'RSS Feeds' );

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('feeds', '*', 'GAME', '{title}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('feeds', '*', '*', '{title}{ifdef(year, " ({year})")}{if(instance_no>1," #{instance_no}")}');

# resolve imdb image not showing issue.
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '2', 'ia.media-imdb.com');

# provide option to set a theme content type character set
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'themes', 5, 'Themes', 'Themes Configuration' );
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('themes', 'charset', 1, 'Content Type Charset', 'This setting will force a meta http-equiv Content Type header to be included in the source of each page.', 'value_select', ',utf-8,iso-8859-1');

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

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'item_is_not_checked_out', 'Item is not checked out');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'quick_check_in', 'Quick Checkin');

UPDATE s_language_var SET varname = 'quick_check_out_for_fullname' WHERE varname = 'item_quick_check_out_for_fullname';
DELETE FROM s_language_var WHERE varname = 'item_quick_check_out';

# add DISC_ID to DIVX
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DISC_ID', 'Disc ID', 'Disc ID', 'text', '10', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'DISC_ID', 1, NULL, 'Y', 'N', 'Y', 'N' );

DELETE FROM s_config_group_item WHERE group_id = 'http' AND id IN ('debug');
DELETE FROM s_config_group_item_var WHERE group_id = 'http' AND id IN ('debug');

# change link text on user profile page to something less misleading.
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'list_user_items', 'List User Items'); 

# border attribute no longer required
UPDATE s_attribute_type SET input_type_arg3 = NULL WHERE input_type IN('checkbox_grid', 'radio_grid');

# change columns value to an orientation, and let CSS work out which is which.
UPDATE s_attribute_type SET input_type_arg2 = 'VERTICAL' WHERE input_type IN('checkbox_grid', 'radio_grid') AND (input_type_arg2 = '*' OR input_type_arg2 IS NULL OR input_type_arg2 <> '1');
UPDATE s_attribute_type SET input_type_arg2 = NULL WHERE input_type IN('checkbox_grid', 'radio_grid') AND input_type_arg2 <> 'VERTICAL';

# ratio should be horizontal - looks better
UPDATE s_attribute_type SET input_type_arg2 = NULL WHERE s_attribute_type = 'RATIO';
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'general_stats', 'General Stats');
DELETE FROM s_language_var WHERE varname = 'general_facts';

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'change_admin_user_password', 'Change Admin User Password'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'change_admin_user_password_msg', 'You must change your password.');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'change_admin_user_email', 'Change Admin User Email');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'change_admin_user_email_msg', 'You must change your email address.');

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'no_item_types', 'No Item Types');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'add_new_item_type_msg', 'There are no Item Types installed.');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'no_site_plugins', 'No Site Plugins');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'add_new_site_plugin_msg', 'There are no Site Plugins installed.');

# use s_item_attribute_type attribute_id instead of order_no which should purely be for order!!!
#ALTER TABLE s_item_attribute_type CHANGE order_no s_attribute_type_id TINYINT(3) UNSIGNED NOT NULL;
#ALTER TABLE item_attribute CHANGE order_no s_attribute_type_id TINYINT(3) UNSIGNED NOT NULL;

UPDATE s_language_var SET value = 'Status Comment' WHERE varname =  'status_comment' AND language = 'ENGLISH';
UPDATE s_language_var SET value = 'Borrow Status' WHERE varname =  'borrow_status' AND language = 'ENGLISH';
UPDATE s_language_var SET value = 'Due Date / Borrow Duration' WHERE varname =  'due_date_or_duration' AND language = 'ENGLISH';

UPDATE s_language_var SET value = 'There are {no_of_users} user(s) awaiting activation.' WHERE varname =  'there_are_no_of_users_awaiting_activation' AND language = 'ENGLISH';

DELETE FROM s_language_var WHERE language = 'ENGLISH' and varname = 'activate_user_list'; 

INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ACT_COMMENT', 'Actor''s Commentary', 'director.gif', 'N');
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'PROD_COMMENT', 'Producer''s Commentary', 'director.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'OTHER_COMMENT', 'Other Commentary', 'director.gif', 'N' );

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'field', 'Field');
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'choose_export_fields', 'Choose Export Fields');

INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_input.upload', 1, 'File Uploads', 'Item Input File Uploads configuration' );
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input.upload', 'file_location', 1, 'File Upload Location', '', 'readonly');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input.upload', 'file_location', './upload/');

# need to be able to use the auto-increment sequence number for cache_file name, so will save
# cache record and then update it with the cache_file reference.
ALTER TABLE file_cache CHANGE cache_file cache_file VARCHAR(255);
ALTER TABLE file_cache CHANGE content_type content_type VARCHAR(100);
ALTER TABLE file_cache CHANGE content_length content_length INTEGER(10) UNSIGNED;
ALTER TABLE file_cache CHANGE url url TEXT;
