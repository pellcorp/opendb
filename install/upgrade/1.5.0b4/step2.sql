
#
# User item interest table
#
DROP TABLE IF EXISTS user_item_interest;
CREATE TABLE user_item_interest (
	sequence_number	INTEGER(10) UNSIGNED NOT NULL auto_increment,
	item_id			INTEGER(10) UNSIGNED NOT NULL,
	instance_no		SMALLINT(5) NOT NULL,
	user_id			VARCHAR(20) NOT NULL,
	level			VARCHAR(1) NOT NULL,
	comment			text,
	update_on		TIMESTAMP NOT NULL,
	PRIMARY KEY ( sequence_number ),
	KEY user_idx ( user_id ),
	KEY item_idx ( item_id)
) ENGINE=MyISAM COMMENT='user item interest table';

-- Permissions for interest
INSERT INTO s_permission (permission_name, description) VALUES ('PERM_USER_INTEREST', 'User item interest');
INSERT INTO s_role_permission (role_name , permission_name ) VALUES ( 'ADMINISTRATOR', 'PERM_USER_INTEREST');
INSERT INTO s_role_permission (role_name , permission_name ) VALUES ( 'OWNER', 'PERM_USER_INTEREST');
INSERT INTO s_role_permission (role_name , permission_name ) VALUES ( 'BORROWER', 'PERM_USER_INTEREST');

-- Configuration in admin panel for interest
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_interest', 6, 'Show interest', '', 'boolean');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_interest', 'TRUE');


-- Configuration for listings
INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 11, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 13, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 12, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 13, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 13, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

-- English lang for interest
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest', 'Interest'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_mark', 'Click to mark your interest for this item'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_remove', 'Click to remove your interest for this item'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_remove_all', 'Remove all your interest marks'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_help', 'You marked your interest in this item'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_only_marked', 'Only items with marked interest'); 
