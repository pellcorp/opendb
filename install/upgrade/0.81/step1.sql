#
# OpenDb 0.81 -> 1.0 upgrade script
#

#
# Addition of columns to System Item Attribute type table for
#
ALTER TABLE s_item_attribute_type ADD rss_ind VARCHAR(1) NOT NULL DEFAULT 'N';
ALTER TABLE s_item_attribute_type ADD printable_ind VARCHAR(1) NOT NULL DEFAULT 'Y';
ALTER TABLE s_item_attribute_type ADD instance_attribute_ind VARCHAR(1) NOT NULL DEFAULT 'N';

ALTER TABLE user_address ADD public_address_ind VARCHAR(1) NOT NULL DEFAULT 'N';
ALTER TABLE user_address ADD borrow_address_ind VARCHAR(1) NOT NULL DEFAULT 'N';

#otherwise the next statement won't work because of the NOT NULL bit.
DELETE FROM import_cache;

ALTER TABLE import_cache ADD cache_file VARCHAR(255) NOT NULL;

#otherwise the next statement won't work because of the NOT NULL bit.
DELETE FROM file_cache;
ALTER TABLE file_cache ADD cache_file VARCHAR(255) NOT NULL;
ALTER TABLE file_cache ADD cache_file_thumb VARCHAR(255);
ALTER TABLE file_cache ADD upload_file_ind VARCHAR(1) NOT NULL DEFAULT 'N';
ALTER TABLE file_cache DROP content;
ALTER TABLE file_cache DROP gzcompress_level;

#
# Update compulsory_ind, so one or the other
#
UPDATE s_item_attribute_type SET compulsory_ind = 'N' WHERE (compulsory_ind IS NULL OR compulsory_ind <> 'Y');

# the display types will be fixed up by a later step, so don't worry about it here!
UPDATE s_attribute_type SET display_type = 'display(%display%, list-link)' WHERE s_attribute_type = 'RATIO';

UPDATE s_attribute_type SET display_type = 'display(%value%, list-link)' WHERE s_attribute_type = 'DVD_REGION';

UPDATE s_attribute_type SET display_type = 'display(%value%)' WHERE s_attribute_type = 'NO_DISCS';

UPDATE s_item_type SET description = 'Digital Versatile Disc' WHERE s_item_type = 'DVD';

ALTER TABLE s_attribute_type ADD listing_link_ind VARCHAR(1) NOT NULL DEFAULT 'N';
ALTER TABLE s_attribute_type ADD input_type_arg1 VARCHAR(50);
ALTER TABLE s_attribute_type ADD input_type_arg2 VARCHAR(50);
ALTER TABLE s_attribute_type ADD input_type_arg3 VARCHAR(50);
ALTER TABLE s_attribute_type ADD input_type_arg4 VARCHAR(50);
ALTER TABLE s_attribute_type ADD input_type_arg5 VARCHAR(50);
ALTER TABLE s_attribute_type ADD display_type_arg1 VARCHAR(50);
ALTER TABLE s_attribute_type ADD display_type_arg2 VARCHAR(50);
ALTER TABLE s_attribute_type ADD display_type_arg3 VARCHAR(50);
ALTER TABLE s_attribute_type ADD display_type_arg4 VARCHAR(50);
ALTER TABLE s_attribute_type ADD display_type_arg5 VARCHAR(50);

#decommission popupsize display_type, will implement special javascript resize functionality if required instead
UPDATE s_attribute_type
SET display_type = 'hidden'
WHERE display_type LIKE 'popupsize(%';

ALTER TABLE s_item_attribute_type CHANGE compulsory_ind compulsory_ind VARCHAR(1) NOT NULL DEFAULT 'N';

# Alter file cache to allow expire date to be NULL
ALTER TABLE file_cache CHANGE expire_date expire_date DATETIME;

ALTER TABLE file_cache CHANGE url url TEXT;
ALTER TABLE file_cache CHANGE location location TEXT;

# update content type to be a legal s_file_type content type without encoding
UPDATE file_cache SET content_type = 'text/html'
WHERE content_type LIKE 'text/html;%';

#
# We use '0' in item_attribute table to indicate no instance_no link, so
# need to set default for instance_no to equal 1
#
ALTER TABLE item_instance CHANGE instance_no instance_no SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1;

#
# Addition of functionality to indicate s_attribute_type is a lookup attribute
# type, and or supports multiple attribute values for a single s_attribute_type and order_no
# combination.  Only one of these should be set to 'Y' for any one s_attribute_type.
#
ALTER TABLE s_attribute_type ADD lookup_attribute_ind VARCHAR(1) NOT NULL DEFAULT 'N';
ALTER TABLE s_attribute_type ADD multi_attribute_ind VARCHAR(1) NOT NULL DEFAULT 'N';
ALTER TABLE s_attribute_type ADD file_attribute_ind VARCHAR(1) NOT NULL DEFAULT 'N';

# force default_ind to be either Y or N
UPDATE s_status_type SET default_ind = 'N' WHERE (default_ind IS NULL OR default_ind <> 'Y');

ALTER TABLE s_status_type CHANGE default_ind default_ind VARCHAR(1) NOT NULL DEFAULT 'N';

# drop columns that enforce complicated functionality that is not required.
ALTER TABLE s_status_type DROP new_owner_instance_ind;
ALTER TABLE s_status_type DROP new_not_owner_instance_ind;

# update file upload / saveurl attributes to be file resources - will not update elements that are set to 'url',
# this specifically does not allow caching of images.
UPDATE s_attribute_type SET file_attribute_ind = 'Y' WHERE input_type LIKE '%saveurl%' OR input_type LIKE '%upload%';

ALTER TABLE s_site_plugin_input_field CHANGE field_type field_type varchar(20);

# provide ability to restrict whether the site plugin will match non-lookup values.
ALTER TABLE s_site_plugin_s_attribute_type_map ADD lookup_attribute_val_restrict_ind VARCHAR(1) DEFAULT 'N';

# update to restrict all common lookup attribute mappings.
UPDATE s_site_plugin_s_attribute_type_map SET lookup_attribute_val_restrict_ind = 'Y'
WHERE s_attribute_type IN ('MOVIEGENRE', 'BOOKGENRE', 'MUSICGENRE', 'AUDIO_LANG', 'SUBTITLES');

UPDATE s_attribute_type SET file_attribute_ind = 'Y' WHERE s_attribute_type = 'IMAGEURL';

#
# re-order status type, status comment borrow duration, so that borrow duration is last.
#
UPDATE s_item_attribute_type SET order_no = '253' WHERE s_attribute_type = 'S_STATUS' AND order_no = '254';
UPDATE s_item_attribute_type SET order_no = '254' WHERE s_attribute_type = 'S_STATCMNT' AND order_no = '255';
UPDATE s_item_attribute_type SET order_no = '255' WHERE s_attribute_type = 'S_DURATION' AND order_no = '200';

#
# Minor fix for country, so that an empty option is available and first option selected.
#
UPDATE s_attribute_type_lookup SET checked_ind = 'N'
WHERE s_attribute_type = 'COUNTRY' AND checked_ind = 'Y';

INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, '', '', '', 'Y');

# allow existing item types to link UPC_ID as required.
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'UPC_ID', 'UPC ID', 'UPC ID', 'hidden', '', '', '', '', '', 'hidden','','','','','','N', 'N', 'N', 'N', '', '');

CREATE TABLE s_file_type_content_group (
  content_group		VARCHAR(10) NOT NULL,
  PRIMARY KEY ( content_group )
) ENGINE=MyISAM COMMENT='System File Type Content Groups';

# image is basename of image provided via theme_image call.
CREATE TABLE s_file_type (
  content_type			VARCHAR(100) NOT NULL,
  content_group			VARCHAR(10) NOT NULL,
  description			VARCHAR(255),
  image					VARCHAR(255),
  thumbnail_support_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( content_type )
) ENGINE=MyISAM COMMENT='System Supported File Types';

CREATE TABLE s_file_type_extension (
  content_type	VARCHAR(100) NOT NULL,
  extension		VARCHAR(10) NOT NULL,
  default_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( content_type, extension )
) ENGINE=MyISAM COMMENT='System File Type Alternate Extension';

CREATE TABLE announcement (
  sequence_number   INTEGER(10) unsigned NOT NULL auto_increment,
  user_id           VARCHAR(20) NOT NULL,
  title             VARCHAR(255) NOT NULL,
  content           text,
  min_user_type     VARCHAR(1) NOT NULL DEFAULT 'B',
  submit_on         timestamp(14) NOT NULL,
  display_days      INTEGER(10) unsigned NOT NULL DEFAULT 0,
  closed_ind        VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( sequence_number ),
  KEY submit_idx ( submit_on )
) ENGINE=MyISAM COMMENT='Site Announcements table';

#
# Install php session table to provide option of using database
# session management.
#
CREATE TABLE php_session(
	SID 		CHAR(32) NOT NULL,
	expiration 	INT NOT NULL,
	value 		TEXT NOT NULL,
	PRIMARY KEY ( SID )
) ENGINE=MyISAM COMMENT='PHP Session table';

CREATE TABLE s_config_group (
	id			VARCHAR(50) NOT NULL,
	name		VARCHAR(50) NOT NULL,
	order_no	TINYINT(2) NOT NULL DEFAULT 0,
	description	VARCHAR(255) NOT NULL,
	PRIMARY KEY ( id )
) ENGINE=MyISAM COMMENT='System Config Group';

CREATE TABLE s_config_group_item (
	group_id		VARCHAR(50) NOT NULL,
	id				VARCHAR(100) NOT NULL,
	keyid       	VARCHAR(50) NOT NULL DEFAULT '0',
	order_no		TINYINT(2) NOT NULL DEFAULT 0,
	prompt      	VARCHAR(50) NOT NULL,
	description		TEXT NOT NULL,
	type			VARCHAR(30) NOT NULL DEFAULT 'text',
    subtype			VARCHAR(255),
	PRIMARY KEY ( group_id, id, keyid )
) ENGINE=MyISAM COMMENT='System Config Group Item';

#
# Override Config Group Item Value
#
CREATE TABLE s_config_group_item_var (
	group_id	VARCHAR(50) NOT NULL,
	id			VARCHAR(100) NOT NULL,
	keyid		VARCHAR(50) NOT NULL DEFAULT '0',
	value		TEXT NOT NULL,
	PRIMARY KEY ( group_id, id, keyid )
) ENGINE=MyISAM COMMENT='Config Group Item Variable';

#
# Title Display Mask config
#
CREATE TABLE s_title_display_mask (
	id				VARCHAR(50) NOT NULL,
	description     VARCHAR(100) NOT NULL,
	PRIMARY KEY ( id )
) ENGINE=MyISAM COMMENT='System Title Display Mask Config';

CREATE TABLE s_title_display_mask_item (
	stdm_id				VARCHAR(50) NOT NULL,
	s_item_type_group	VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type			VARCHAR(10) NOT NULL DEFAULT '*',
	display_mask		TEXT,
	PRIMARY KEY ( stdm_id, s_item_type_group, s_item_type )
) ENGINE=MyISAM COMMENT='System Title Display Mask Config Item';

#
# Item Listing config
#
CREATE TABLE s_item_listing_conf (
	id							INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	s_item_type_group			VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type					VARCHAR(10) NOT NULL DEFAULT '*',
	PRIMARY KEY ( id ),
	UNIQUE KEY ( s_item_type_group, s_item_type )
) ENGINE=MyISAM COMMENT='Item Listing Configuration';

#
# Item Listing Column Conf
#
# column_type:
#	s_field_type
#		ITEM_ID, TITLE, STATUSTYPE, STATUSCMNT, CATEGORY, RATING, ITEMTYPE, OWNER
#
#	s_attribute_type
#
#	s_item_type
#	owner
#	action_links
#	borrow_status
#
# orderby_datatype:
#	NUMERIC
#   ALPHA
#
CREATE TABLE s_item_listing_column_conf (
	silc_id					INTEGER(10) UNSIGNED NOT NULL,
	column_no				TINYINT(3) UNSIGNED NOT NULL,
	column_type				VARCHAR(20) NOT NULL DEFAULT 's_field_type',
	s_field_type			VARCHAR(10),
	s_attribute_type		VARCHAR(10),
	override_prompt			VARCHAR(30),
	orderby_support_ind		VARCHAR(1) NOT NULL DEFAULT 'Y',
	orderby_datatype		VARCHAR(10),
	printable_support_ind	VARCHAR(1) NOT NULL DEFAULT 'Y',
	PRIMARY KEY ( silc_id, column_no )
) ENGINE=MyISAM COMMENT='Item Listing Column Configuration';

#
# System Language table
#
CREATE TABLE s_language (
	language	VARCHAR(10) NOT NULL,
	default_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
	description	VARCHAR(50) NOT NULL,
	PRIMARY KEY ( language )
) ENGINE=MyISAM COMMENT='System Language Table';

# all language codes are now uppercase in the database
UPDATE user SET language = UPPER(language);

#
# System Language Variable Table
#
CREATE TABLE s_language_var (
	language	VARCHAR(10) NOT NULL,
	varname		VARCHAR(50) NOT NULL,
	value		TEXT,
	PRIMARY KEY ( language, varname )
) ENGINE=MyISAM COMMENT='System Language Variable Table';

#
# System Table Language Variable Table
#
# tablename is one of:
#	s_item_type 					(description) 	[key1 = s_item_type]
#	s_item_type_group				(description) 	[key1 = s_item_type_group]
#	s_attribute_type				(prompt)		[key1 = s_attribute_type]	
#	s_item_attribute_type			(prompt)		[key1 = s_item_type AND key2 = s_attribute_type AND key3 = order_no ]					
#	s_attribute_type_lookup			(display)		[key1 = s_attribute_type AND key2 = value]	
#	s_status_type					(description)	[key1 = s_status_type]
#	s_address_type					(description)	[key1 = s_address_type]
#	s_addr_attribute_type_rltshp	(prompt)		[key1 = s_address_type AND key2 = s_attribute_type AND key3 = order_no ]
#
CREATE TABLE s_table_language_var (
	language	VARCHAR(10) NOT NULL,
	tablename	VARCHAR(50) NOT NULL,
	columnname	VARCHAR(50) NOT NULL,
	key1		VARCHAR(50) NOT NULL,
	key2		VARCHAR(50) NOT NULL,
	key3		VARCHAR(50) NOT NULL,
	value		TEXT,
	PRIMARY KEY ( language, tablename, columnname, key1, key2, key3 )
) ENGINE=MyISAM COMMENT='System Language Variable Table';

#
# Configuration Groups
#
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site', 1, 'Opendb Site', 'Site configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site.public_access', 1, 'Public Access', 'Public Access configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site.url', 2, 'Opendb Site URL', 'Override OpenDb Site URL configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site.gzip_compression', 3, 'GZIP Compression', 'Configure gzip compression' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'logging', 2, 'Logging', 'Logging configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http', 3, 'Http', 'HTTP configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http.cache', 1, 'Http Cache', 'HTTP Cache configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http.item.cache', 1, 'Item File Cache', 'Item File Cache configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http.stream_external_images', 2, 'Stream External Images', 'Stream external image URLs via Snoopy. Very useful for working around IMDB cover image display restrictions');
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http.proxy_server', 3, 'Proxy Server', 'Proxy Server configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'email', 4, 'Email', 'Email configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'email.smtp', 1, 'Smtp Configuration', 'SMTP Server configuration' );

INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'widgets', 5, 'Widgets', 'Widgets configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'login', 6, 'Login', 'Login / Logout configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'login.signup', 1, 'Signup', 'Signup configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'login.last_items_list', 2, 'Login Last Items List', 'Login last items listing configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'login.whats_new', 3, 'Login Whats New', 'Login whats new summary configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'login.announcements', 4, 'Login Announcements', 'Login announcements configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'user_admin', 7, 'User Administration', 'User Administration configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'listings', 8, 'Item Listings', 'Item Listings configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'listings.filters', 1, 'Item Listing Filters', 'Item Listing filter configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'listings.borrow', 2, 'Item Listing Borrow', 'Item Listing Borrow configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'listings.multi_borrow', 3, 'Item Listing Multi Borrow', 'Item Listing Multi Borrow configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_display', 9, 'Item Display', 'Item Display configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_input', 10, 'Item Input', 'Item Input configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_input.site', 1, 'Site Plugins', 'Item Input Site Plugins configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_review', 11, 'Item Review', 'Item Review configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'borrow', 12, 'Item Borrow', 'Borrow Functionality configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'borrow.reminder', 1, 'Item Borrow Reminders', 'Reminders Job configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'search', 13, 'Item Search', 'Item Search configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'import', 14, 'Import', 'Import configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'import.cache', 1, 'Import Cache', 'Import Cache configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'stats', 15, 'Statistics', 'Statistics configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'announcements', 16, 'Announcements', 'Announcements configuration' );

#
# Configuration Items
#
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'title', 2, 'Title', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'language', 5, 'Default Language', '', 'language');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'theme', 6, 'Default Theme', '', 'theme');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'idle_timeout', 7, 'Idle Timeout', 'In milliseconds', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'login_timeout', 8, 'Login Timeout', 'In milliseconds', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'security_hash', 9, 'Security Hash', 'Change for each OpenDb site for added security', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'file_upload_enable', 10, 'Enable File Uploads', 'Override PHP file upload configuration', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'register_globals_enabled', 11, 'Enable Register Globals', 'Override PHP register globals configuration', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'restrict_session_cookie_to_host_path', 12, 'Restrict Session to Virtual Location', 'Restrict session to this OpenDb instance. [EXPERIMENTAL]', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'upgrade_check', 13, 'Upgrade Check', 'Whenever index.php is accessed a version check will be made between the OpenDb database and opendb installation to make sure they match.', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.gzip_compression', 'enable', 1, 'Enable GZIP Compression', 'If enabled all html output from opendb will be gzip compressed', 'boolean'); 
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('site.gzip_compression', 'disabled', 2, 'Exclude Specific Pages', 'This is a workaround where low memory limits are enabled.', 'array', 'text'); 

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.url', 'host', 1, 'Host', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.url', 'port', 2, 'Port', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.url', 'path', 3, 'Path', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('site.url', 'protocol', 4, 'Protocol', '', 'value_select', ',http,https');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.public_access', 'enable', 1, 'Enable', 'Expose the OpenDb as a public site, with \'guest\' User ID', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.public_access', 'user_id', 2, 'Guest User', 'You must choose a valid \'guest\' User', 'guest_userid');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('widgets', 'enable_javascript_validation', 1, 'Enable Javascript Validation', 'Enforce javascript data validations in addition to backend validations.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('widgets', 'show_prompt_compulsory_ind', 2, 'Show Prompt Compulsory Indicator', 'Any Mandatory data elements will show a visual mandatory element identifier', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('widgets', 'legal_html_tags', 3, 'Legal HTML Tags', '', 'array', 'text');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('logging', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('logging', 'file', 2, 'Log File', '', 'readonly');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('logging', 'backup_ext_date_format', 3, 'Date Format', 'Logfile Backup Extension Date Format', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http', 'debug', 1, 'Debug', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.stream_external_images', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) 	VALUES ('http.stream_external_images', 'domain_list', 2, 'Domain List', 'Restrict streaming to specified domain names.', 'array', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http', 'datetime_mask', 5, 'Cache datetime mask', 'Http / Item Cache Admin Tool Datetime Mask', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.cache', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.cache', 'directory', 2, 'Directory', '', 'readonly');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.cache', 'lifetime', 3, 'Refresh Timeout', 'In seconds', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.item.cache', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.item.cache', 'directory', 2, 'Directory', '', 'readonly');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.item.cache', 'lifetime', 3, 'Refresh Timeout', 'In seconds', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'host', 2, 'Host', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'port', 3, 'Port', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'userid', 4, 'Username', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'password', 5, 'Password', '', 'password');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email', 'send_to_site_admin', 1, 'Send to Admin', 'Sending Email to admin is supported.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email', 'user_address_attribute', 2, 'User Address Attribute', '', 'readonly');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email', 'noreply_address', 3, 'No Reply Address', 'Configure no-reply address for events such as password resets', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email', 'windows_smtp_server', 4, 'Windows SMTP Server', 'If smtp server either configured via php mail or smtp mailer is running on windows (for example if its an MS Exchange Server) and mail is not getting through, it may help to check this.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('email', 'mailer', 5, 'Mailer', '', 'value_select', 'smtp,mail,none');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email.smtp', 'host', 1, 'Email SMTP Host', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email.smtp', 'port', 2, 'Email SMTP Port', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email.smtp', 'username', 3, 'Email SMTP Username', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email.smtp', 'password', 4, 'Email SMTP Password', '', 'password');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login', 'enable_new_pwd_gen', 1, 'Enable new Password request', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login', 'enable_change_user', 2, 'Enable Change User', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login', 'show_menu', 3, 'Show Menu', 'Should menu be displayed when logging in / out', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.signup', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('login.signup', 'restrict_usertypes', 2, 'Signup Usertype Restrictions', 'Restrict what user types can be signed up', 'array', 'usertype');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.whats_new', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.whats_new', 'exclude_current_user', 3, 'Exclude Current User', 'Exclude current users items from being listed', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.whats_new', 'borrow_stats', 4, 'Show Borrow Stats', 'Show Items Returned, Reserved, etc', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.whats_new', 'review_stats', 5, 'Show Review Stats', 'Show number of reviews added.', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.last_items_list', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.last_items_list', 'exclude_current_user', 3, 'Exclude Current User', 'Exclude current users items', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.last_items_list', 'restrict_last_login', 4, 'Restrict Last Login', 'Restrict list to items added since last login', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.last_items_list', 'total_num_items', 5, 'Total Items to List', 'Should be evenly divisible by Items Per Column', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.last_items_list', 'datetime_mask', 8, 'Datetime Mask', '', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.announcements', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.announcements', 'display_count', 3, 'Display Count', 'Number of Announcements to list', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('announcements', 'datetime_mask', 1, 'Datetime Mask', '', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('import', 'row_import_default_initcap_checked', 1, 'Row Import Initcap Enabled', 'Initcap checked by default', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('import.cache', 'file_location', 2, 'Import Cache location', 'Location of import cache files', 'readonly');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_themes_support', 1, 'User Themes Support', 'Users can change theme', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_language_support', 2, 'User Language Support', 'Users can change language', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_deactivate_support', 3, 'Deactivate User Support', 'Deactivate instead of deleting user', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_delete_support', 4, 'Delete User Support', 'If Deactivate User Support unchecked, this will allow delete of users', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_delete_with_reviews', 5, 'Delete User with reviews', 'User can be deleted even if they have authored review(s)', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_delete_with_borrower_inactive_borrowed_items', 6, 'Delete User with inactive borrower records', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_delete_with_owner_inactive_borrowed_items', 7, 'Delete user with inactive lender records', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_passwd_change_allowed', 8, 'Change Password support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'datetime_mask', 10, 'Datetime Mask', '', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('stats', 'piechart_striped', 1, 'Piechart striped', 'Draw every other pie wedge a different colour', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('stats', 'piechart_12oclock', 2, 'Piechart 12oclock', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('stats', 'piechart_sort', 3, 'Piechart sort', '', 'value_select', 'asc,desc');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('stats', 'category_barchart', 4, 'Category Barchart', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('stats', 'barchart_sort', 5, 'Barchart sort', '', 'value_select', 'asc,desc');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('stats', 'image_type', 6, 'Image Type', '', 'value_select', 'png,jpg,gif');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'show_item_image', 1, 'Show Item Image', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('item_display', 'item_image_size', 'height', 2, 'Item Image Height', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('item_display', 'item_image_size', 'width', 3, 'Item Image Width', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'no_image', 4, 'No Image', 'Whether to display a \'missing\' image, if no image defined for item', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'tabbed_layout', 5, 'Tabbed Layout', 'Item Display tabbed layout', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'review_datetime_mask', 6, 'Review Datetime Mask', '', 'datemask');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'export_link', 7, 'Export Plugin Link Type', 'If defined will provide a export link', 'export');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'item_instance_support', 1, 'Item Instance Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'new_instance_owner_only', 2, 'New Instance Owner Only', 'Whether item instances can be created across owners', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'linked_item_support', 4, 'Linked Item Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'link_same_type_only', 5, 'Linked Item Restrict Type', 'Linked items must be same type as parent item', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'clone_item_support', 6, 'Clone Item Support', '', 'boolean');
# INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'auto_site_insert', 9, 'Auto Site Insert', 'Bypass new item edit screen', 'boolean');
#INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'auto_site_update', 10, 'Auto Site Refresh', 'Bypass update item edit screen', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'duplicate_title_support', 11, 'Duplicate Title Support', 'Duplicate title with same type and owner allowed', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'confirm_duplicate_insert', 12, 'Confirm Duplicate Insert', 'Confrm insert of duplicate title with same type regardless of owner', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'confirm_duplicate_owner_insert', 13, 'Confirm Duplicate Owner Insert', 'Confrm insert of duplicate title with same type and owner', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'confirm_duplicate_linked_item_insert', 14, 'Confirm Linked Item Duplicate Insert', 'Confirm a duplicate title insert for same owner', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'confirm_item_delete', 15, 'Confirm Item Delete', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'confirm_linked_item_delete', 16, 'Confirm Linked Item Delete', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'allow_delete_with_closed_or_cancelled_borrow_records', 17, 'Allow Item Delete with inactive borrow records', ' exist', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('item_input', 'title_articles', 19, 'Title Articles', 'Format title, so that articles appear at the end of the title.', 'array', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input.site', 'debug', 1, 'Debug Site Plugins', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('item_input.site', 'item_image_size', 'height', 2, 'Item Image Height', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('item_input.site', 'item_image_size', 'width', 3, 'Item Image Width', '', 'number');


INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'update_support', 1, 'Update Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'delete_support', 2, 'Delete Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'include_other_title_reviews', 3, 'Include Other Titles', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'other_title_reviews_restrict_to_item_type_group', 4, 'Restrict Other Title Item Type Group', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_item_image', 1, 'Show Item Images', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'allow_override_show_item_image', 2, 'Allow Override Show Item Images', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('listings', 'item_image_size', 'height', 3, 'Item Image Height', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('listings', 'item_image_size', 'width', 4, 'Item Image Width', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'no_image', 5, 'No Image', 'Whether to display a \'missing\' image, if no image defined for an item', 'text');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('listings', 'items_per_page_options', 6, 'Items Per Page Options', 'List of options for the \'Items Per Page\' list.  A value of \'0\' can be used to specify an empty option', 'array', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'items_per_page', 7, 'Default Items Per Page', 'How many items will be shown per page', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_input_actions', 8, 'Show Item Input Actions', 'Show update, edit, delete actions', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_refresh_actions', 9, 'Show Item Refresh Action', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'user_email_link', 10, 'User Email Link', 'User name linkable to send email', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'title_mask_macro_theme_img_help', 11, 'Title Mask Help Entries', 'Display any \'theme_img\' title mask macro elements in the help section.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_borrowed_or_returned', 12, 'Show Previously Borrowed Indication', 'Show indication if a user has already borrowed/returned item', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('listings', 'linked_items', 13, 'Display Linked Items', 'Override search functionality, to always include / restrict / exclude linked items.  If this variable is set, no linked items field will be in the search form.', 'value_select', 'undefined,restrict,include,exclude');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'print_listing_datetime_mask', 15, 'Print Listing Datetime Mask', '', 'datemask');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'save_listing_url', 16, 'Enable \'Back to Listing\' Links', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_exact_match_search_columns', 17, 'Include Exact Match Search Columns', 'Include a column for each exact match search column, by default this is disabled.', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_item_type_group_lov', 2, 'Show Item Type Group LOV', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_item_type_lov', 3, 'Show Item Type LOV', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_owner_lov', 4, 'Show Owner LOV', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_s_status_type_lov', 5, 'Show Status Type LOV', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.borrow', 'enable', 1, 'Enable', 'Enable item level actions, Reserve, Add to Basket, etc', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.borrow', 'quick_checkout_action', 2, 'Quick Checkout Action', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.borrow', 'reserve_action', 3, 'Reserve Action', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.borrow', 'basket_action', 4, 'Reserve Basket Action', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.multi_borrow', 'enable', 1, 'Enable', 'Reserve checkboxes and actions should be enabled', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.multi_borrow', 'reserve_action', 2, 'Reserve Action', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.multi_borrow', 'basket_action', 3, 'Reserve Basket Action', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.multi_borrow', 'basket_action_if_not_empty_only', 4, 'Reserve Basket If Not Empty', 'Provide a \'Add to Basket\' action only if the Reserve Basket is not empty.', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reserve_email_only', 2, 'Reserve Email Only', 'Reservation of item sends email only, no other borrow functionality is enabled.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reserve_basket', 3, 'Enable Reserve Basket', 'Enable/Disable Reserve Basket support.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'allow_multi_reserve', 4, 'Allow Multible Reserve', 'Can more than one user can reserve same item', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'allow_reserve_if_borrowed', 5, 'Allow Reservation if Borrowed', 'Allow reservation if item already borrowed', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'duration_support', 6, 'Duration Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'quick_checkout', 7, 'Quick Checkout Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'quick_checkout_use_existing_reservation', 8, 'Quick Checkout Use Existing Reservation', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'admin_quick_checkout_borrower_lov', 9, 'Admin Quick Checkout Borrower LOV', 'Display list of users instead of text field.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'include_borrower_column', 10, 'Include Borrower Column', 'Show borrower of item in item display', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reserve_more_information', 11, 'Reserve More Information', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'cancel_more_information', 12, 'Cancel More Information', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'checkout_more_information', 13, 'Checkout More Information', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'checkin_more_information', 14, 'Check in More Information', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reminder_more_information', 15, 'Reminder Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reserve_email_notification', 16, 'Reservation Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'cancel_email_notification', 17, 'Cancel Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'checkout_email_notification', 18, 'Checkout Reservation', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'quick_checkout_email_notification', 19, 'Quick Checkout Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'checkin_email_notification', 20, 'Check in Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'date_mask', 21, 'Date Mask', 'Due date formatting', 'datemask');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'datetime_mask', 22, 'Datetime Mask', '', 'datemask');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'list_all_borrowed', 23, 'All Borrowed Items Listing', 'Allow listing of all borrowed items', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'list_all_reserved', 24, 'All Reserved Items Listing', 'Allow listing of all reserved items', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow.reminder', 'duration_range', 1, 'Duration Range', 'Reminder job duration range, as follows: +X = X days overdue; 0 = on day due; -X = X days before due date', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('search', 'default_include_linked_items', 1, 'Include Linked Items', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('search', 'datetime_mask', 2, 'Datetime Mask', 'Search datetime mask', 'datemask');

#
# Configuration Item Values.
#

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'title', 'Open Media Collectors Database');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'idle_timeout', '3600');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'security_hash', '0eXf5yUKlaeDgREQ72091mvFX');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'language', 'english');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'theme', 'default');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'file_upload_enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'register_globals_enabled', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'restrict_session_cookie_to_host_path', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'upgrade_check', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site.public_access', 'enable', 'FALSE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site.gzip_compression', 'enable', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('site.gzip_compression', 'disabled', '0', 'admin');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('site.gzip_compression', 'disabled', '1', 'import');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('site.gzip_compression', 'disabled', '2', 'export');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('site.gzip_compression', 'disabled', '3', 'item_input');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('widgets', 'enable_javascript_validation', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '0', 'p');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '1', 'b');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '2', 'i');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '3', 'u');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '4', 's');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '5', 'em');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '6', 'br');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '7', 'strong');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '8', 'strike');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '9', 'big');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '10', 'sup');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '11', 'sub');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('widgets', 'show_prompt_compulsory_ind', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.signup', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('login.signup', 'restrict_usertypes', '0', 'N');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('login.signup', 'restrict_usertypes', '1', 'B');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('logging', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('logging', 'file', './log/usagelog.txt');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('logging', 'backup_ext_date_format', 'DDMONYYYY');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http', 'debug', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.stream_external_images', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '0', 'imdb.com');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '1', 'imdb.org');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http', 'datetime_mask', 'DD/MM/YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.cache', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.cache', 'directory', './httpcache');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.cache', 'lifetime', '604800');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.item.cache', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.item.cache', 'directory', './itemcache');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.item.cache', 'lifetime', '604800');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.proxy_server', 'enable', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email', 'mailer', 'mail');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email', 'send_to_site_admin', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email', 'noreply_address', 'noreply@iamvegan.net');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email', 'user_address_attribute', 'EMAIL_ADDR');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email.smtp', 'host', 'mail.domain.edu.au');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email.smtp', 'username', 'jpell');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login', 'enable_new_pwd_gen', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login', 'enable_change_user', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login', 'show_menu', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.whats_new', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.whats_new', 'show_heading', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.whats_new', 'borrow_stats', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.whats_new', 'review_stats', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.whats_new', 'exclude_current_user', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.last_items_list', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.last_items_list', 'show_heading', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.last_items_list', 'exclude_current_user', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.last_items_list', 'restrict_last_login', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.last_items_list', 'datetime_mask', 'DD/MM/YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.last_items_list', 'total_num_items', '18');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.last_items_list', 'show_item_image', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.announcements', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.announcements', 'show_heading', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.announcements', 'display_count', '3');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('menu', 'other_items_listing', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('menu', 'all_items_listing', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('import', 'row_import_default_initcap_checked', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('import.cache', 'file_location', './importcache/');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'datetime_mask', 'DDth Month YYYY HH24:MI');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_themes_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_passwd_change_allowed', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_language_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_deactivate_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_delete_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_delete_with_reviews','TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_delete_with_borrower_inactive_borrowed_items', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_delete_with_owner_inactive_borrowed_items', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('stats', 'image_type', 'png');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('stats', 'piechart_12oclock', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('stats', 'piechart_sort', 'asc');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('stats', 'piechart_striped', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('stats', 'category_barchart', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('stats', 'barchart_sort', 'desc');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'show_item_image', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'no_image', 'no-image.gif');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'tabbed_layout', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_display', 'item_image_size', 'height', '100');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'review_datetime_mask', 'Day, DDth Month YYYY HH24:MI');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'export_link', 'OpenDb_XML');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'linked_item_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'link_same_type_only', 'FALSE');
#INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'auto_site_insert', 'FALSE');
#INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'auto_site_update', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'clone_item_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'item_instance_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'new_instance_owner_only', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'confirm_item_delete', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'confirm_linked_item_delete', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'allow_delete_with_closed_or_cancelled_borrow_records', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'duplicate_title_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'confirm_duplicate_insert','FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'confirm_duplicate_owner_insert', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'confirm_duplicate_linked_item_insert', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input', 'item_image_size', 'height', '50');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input', 'title_articles', '0', 'The');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input', 'title_articles', '1', 'A');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input', 'title_articles', '2', 'An');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input.site', 'item_image_size', 'height', '50');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'update_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'delete_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'include_other_title_reviews', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'other_title_reviews_restrict_to_item_type_group', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '0', '0');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '1', '21');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '2', '31');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '3', '41');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '4', '51');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'item_image_size', 'width', '69');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'user_email_link', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'save_listing_url', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_exact_match_search_columns', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'title_mask_macro_theme_img_help', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'no_image', 'no-image.gif');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_item_image', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'allow_override_show_item_image', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'print_listing_datetime_mask', 'Day, DDth Month YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'linked_items', 'undefined');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'items_per_page', '21');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_input_actions', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_refresh_actions', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.borrow', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.borrow', 'quick_checkout_action', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.borrow', 'reserve_action', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.borrow', 'basket_action', 'FALSE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.multi_borrow', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.multi_borrow', 'reserve_action', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.multi_borrow', 'basket_action', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.multi_borrow', 'basket_action_if_not_empty_only', 'FALSE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_item_type_group_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_item_type_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_owner_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_s_status_type_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_borrowed_or_returned', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reserve_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'cancel_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'checkout_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'checkin_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reminder_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reserve_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'cancel_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'checkout_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'quick_checkout_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'checkin_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'quick_checkout', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'admin_quick_checkout_borrower_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reserve_email_only', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'allow_multi_reserve',  'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'allow_reserve_if_borrowed', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'quick_checkout_use_existing_reservation', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'list_all_borrowed', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'list_all_reserved', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'include_borrower_column', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reserve_basket', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'duration_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'date_mask', 'DDth Month YYYY');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'datetime_mask', 'DD/MM/YYYY HH:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow.reminder', 'duration_range', '-1');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('search', 'datetime_mask', 'DD/MM/YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('search', 'default_include_linked_items', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('announcements', 'datetime_mask', 'DD/MM/YYYY HH:MI:SS');

#
# Title Display Mask data
#
INSERT INTO s_title_display_mask(id, description)
VALUES('last_items_list', 'Last Items List');

INSERT INTO s_title_display_mask(id, description)
VALUES('item_display', 'Item Display');

INSERT INTO s_title_display_mask(id, description)
VALUES('item_listing', 'Item Listings');

INSERT INTO s_title_display_mask(id, description)
VALUES('item_borrow', 'Item Borrow');

#
# Title Display Mask Group Items - Item Display
#
INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_display', '*', '*', '"{title}"{ifdef(year, " ({year})")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_display', '*', 'BOOK', '{title}{ifdef(pub_date, " ({pub_date.display_type})")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_display', '*', 'GAME', '"{title}"{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_display', 'AUDIO', '*', '"{title}"{if(instance_no>1," #{instance_no}")}');

#
# Title Display Mask Group Items - Item Listings
#
INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_listing', '*', '*', '{title}{ifdef(year, " ({year})")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_listing', '*', 'BOOK', '{title}{ifdef(pub_date, " ({pub_date.display_type})")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_listing', '*', 'GAME', '{title}{ifdef(gamesystem, " {gamesystem.img}")}{if(instance_no>1," #{instance_no}")}');

#INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
#VALUES('item_listing', 'AUDIO', '*', '{title}{ifdef(artist, " / {artist}")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_listing', 'AUDIO', '*', '{title}{if(instance_no>1," #{instance_no}")}');

#
# Item Listing Configuration
#
INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type) 
VALUES (1, '*', '*');

INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type)
VALUES (2, 'VIDEO', '*');

INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type)
VALUES (3, 'AUDIO', '*');

INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type)
VALUES (4, '*', 'BOOK');

INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type)
VALUES (5, '*', 'GAME');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (1, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 5, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 6, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 7, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 8, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 9, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 10, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (2, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 5, 's_attribute_type', NULL, 'DIRECTOR', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 6, 's_attribute_type', NULL, 'AGE_RATING', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 7, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 8, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 9, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 10, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 11, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 12, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (3, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 5, 's_attribute_type', NULL, 'ARTIST', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 6, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 7, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 8, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 9, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 10, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 11, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (4, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 5, 's_attribute_type', NULL, 'AUTHOR', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 7, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 8, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 9, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 10, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 11, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 12, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (5, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 5, 's_attribute_type', NULL, 'GAMEPBLSHR', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 6, 's_attribute_type', NULL, 'NO_PLAYERS', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 7, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 8, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 9, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 10, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 11, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 12, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

#
# File Types
#
INSERT INTO s_file_type_content_group (content_group) VALUES ('IMAGE');
INSERT INTO s_file_type_content_group (content_group) VALUES ('AUDIO');
INSERT INTO s_file_type_content_group (content_group) VALUES ('VIDEO');
INSERT INTO s_file_type_content_group (content_group) VALUES ('DOCUMENT');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('image/jpeg', 'IMAGE', 'JPEG Image', NULL, 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/jpeg', 'jpeg', 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/jpeg', 'jpg', 'N');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/jpeg', 'jpe', 'N');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('image/gif', 'IMAGE', 'GIF Image', NULL, 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/gif', 'gif', 'Y');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('image/png', 'IMAGE', 'PNG Image', NULL, 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/png', 'png', 'Y');

# default support for site plugin cached html pages
INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('text/html', 'DOCUMENT', 'HTML Page', NULL, 'N');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/html', 'html', 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/html', 'htm', 'N');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('text/xml', 'DOCUMENT', 'XML Page', NULL, 'N');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/xml', 'xml', 'Y');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('text/plain', 'DOCUMENT', 'Plain Text', NULL, 'N');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/plain', 'txt', 'Y');
