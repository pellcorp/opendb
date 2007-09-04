# --------------------------------------------------------
# OpenDb Tables
# --------------------------------------------------------

#
# Install php session table to provide option of using database
# session management.
#
DROP TABLE IF EXISTS php_session;
CREATE TABLE php_session(
	SID 		CHAR(32) NOT NULL,
	expiration 	INT NOT NULL,
	value 		TEXT NOT NULL,
	PRIMARY KEY ( SID )
) TYPE=MyISAM COMMENT='PHP Session table';

#
# System Config Group
#
DROP TABLE IF EXISTS s_config_group;
CREATE TABLE s_config_group (
	id			VARCHAR(50) NOT NULL,
	name		VARCHAR(50) NOT NULL,
	order_no	TINYINT(2) NOT NULL DEFAULT 0,
	description	VARCHAR(255) NOT NULL,
	PRIMARY KEY ( id )
) TYPE=MyISAM COMMENT='System Config Group';

#
# System Config Group Item
#
# type:
# 		array - keys will be numeric and in sequence only.
#			The array type, supports several subtypes, which will cause each
#			individual value in the array to be validated individually:
#				text
#				number
#				usertype
#
# 		boolean - TRUE or FALSE only
# 		text - arbritrary text
#       email - email address
# 		textarea - arbritrary text
# 		number - enforce a numeric value
# 		datemask - enforce a date mask.
#		usertype - Restrict to a single user type only.
#       guest_userid - Restrict to GUEST userid
#       language - Restrict to a single legal language only
#       theme - Restrict to a single legal language only
#       value_select [option1,option2] - choose one option from the list, specified in the subtype
#
# order_no - should be unique for a group_id only
#
DROP TABLE IF EXISTS s_config_group_item;
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
) TYPE=MyISAM COMMENT='System Config Group Item';

#
# Override Config Group Item Value
#
DROP TABLE IF EXISTS s_config_group_item_var;
CREATE TABLE s_config_group_item_var (
	group_id	VARCHAR(50) NOT NULL,
	id			VARCHAR(100) NOT NULL,
	keyid		VARCHAR(50) NOT NULL DEFAULT '0',
	value		TEXT NOT NULL,
	PRIMARY KEY ( group_id, id, keyid )
) TYPE=MyISAM COMMENT='Config Group Item Variable';

#
# System Item Type table
#
DROP TABLE IF EXISTS s_item_type;
CREATE TABLE s_item_type (
  s_item_type		VARCHAR(10) NOT NULL,
  description		VARCHAR(60) NOT NULL,
  image				VARCHAR(255),
  order_no			TINYINT(2),
  PRIMARY KEY ( s_item_type )
) TYPE=MyISAM COMMENT='System Item Type table';

#
# System Item Type Group
#
# system_ind - indicates if a group should be used by the system
# to group s_item_type or if the grouping is only for use in
# listings functionality.
#
DROP TABLE IF EXISTS s_item_type_group;
CREATE TABLE s_item_type_group (
	s_item_type_group	VARCHAR(10) NOT NULL,
	description			VARCHAR(60) NOT NULL,
	system_ind			VARCHAR(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY ( s_item_type_group )
) TYPE=MyISAM COMMENT='System Item Type Group';

#
# System Item Type Group Relationship
#
DROP TABLE IF EXISTS s_item_type_group_rltshp;
CREATE TABLE s_item_type_group_rltshp (
	s_item_type_group	VARCHAR(10) NOT NULL,
	s_item_type		VARCHAR(10) NOT NULL,
	PRIMARY KEY ( s_item_type_group, s_item_type )
) TYPE=MyISAM COMMENT='System Item Type Group Relationship';

#
# Title Display Mask configuration
#
DROP TABLE IF EXISTS s_title_display_mask;
CREATE TABLE s_title_display_mask (
	id				VARCHAR(50) NOT NULL,
	description     VARCHAR(100) NOT NULL,
	PRIMARY KEY ( id )
) TYPE=MyISAM COMMENT='System Title Display Mask Config';

DROP TABLE IF EXISTS s_title_display_mask_item;
CREATE TABLE s_title_display_mask_item (
	stdm_id				VARCHAR(50) NOT NULL,
	s_item_type_group	VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type			VARCHAR(10) NOT NULL DEFAULT '*',
	display_mask		TEXT,
	PRIMARY KEY ( stdm_id, s_item_type_group, s_item_type )
) TYPE=MyISAM COMMENT='System Title Display Mask Config Item';

#
# Item Listing config
#
DROP TABLE IF EXISTS s_item_listing_conf;
CREATE TABLE s_item_listing_conf (
	id							INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	s_item_type_group			VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type					VARCHAR(10) NOT NULL DEFAULT '*',
	PRIMARY KEY ( id ),
	UNIQUE KEY ( s_item_type_group, s_item_type )
) TYPE=MyISAM COMMENT='Item Listing Configuration';

#
# Item Listing Column Conf
#
# column_type:
#	s_field_type
#       ITEM_ID, TITLE, STATUSTYPE, STATUSCMNT, CATEGORY, RATING
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
DROP TABLE IF EXISTS s_item_listing_column_conf;
CREATE TABLE s_item_listing_column_conf (
	silc_id					INTEGER(10) UNSIGNED NOT NULL,
	column_no				TINYINT(3) UNSIGNED NOT NULL,
	column_type				VARCHAR(20) NOT NULL DEFAULT 's_field_type',
	s_field_type			VARCHAR(10),
	s_attribute_type		VARCHAR(10),
	override_prompt			VARCHAR(30),
	printable_support_ind	VARCHAR(1) NOT NULL DEFAULT 'Y',
	orderby_support_ind		VARCHAR(1) NOT NULL DEFAULT 'Y',
	orderby_datatype		VARCHAR(10),
	orderby_default_ind		VARCHAR(1) NOT NULL DEFAULT 'N',
	orderby_sort_order		VARCHAR(4),
	PRIMARY KEY ( silc_id, column_no )
) TYPE=MyISAM COMMENT='Item Listing Column Configuration';

#
# System Attribute Type table
#
DROP TABLE IF EXISTS s_attribute_type;
CREATE TABLE s_attribute_type (
  s_attribute_type		VARCHAR(10) NOT NULL,
  description			VARCHAR(60) NOT NULL,
  prompt				VARCHAR(30),
  lookup_attribute_ind 	VARCHAR(1) NOT NULL DEFAULT 'N',
  multi_attribute_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  file_attribute_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  listing_link_ind		VARCHAR(1) NOT NULL DEFAULT 'N',
  input_type			VARCHAR(20),
  input_type_arg1		VARCHAR(50),
  input_type_arg2		VARCHAR(50),
  input_type_arg3		VARCHAR(50),
  input_type_arg4		VARCHAR(50),
  input_type_arg5		VARCHAR(50),
  display_type			VARCHAR(20),
  display_type_arg1		VARCHAR(50),
  display_type_arg2		VARCHAR(50),
  display_type_arg3		VARCHAR(50),
  display_type_arg4		VARCHAR(50),
  display_type_arg5		VARCHAR(50),
  s_field_type			VARCHAR(10),
  site_type				VARCHAR(10),
  PRIMARY KEY ( s_attribute_type )
) TYPE=MyISAM COMMENT='System Attribute table';

#
# System Item Attribute Type relationship table
#
DROP TABLE IF EXISTS s_item_attribute_type;
CREATE TABLE s_item_attribute_type (
  s_item_type		VARCHAR(10) NOT NULL,
  s_attribute_type	VARCHAR(10) NOT NULL,
  order_no			TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  prompt			VARCHAR(30),
  instance_attribute_ind VARCHAR(1) NOT NULL DEFAULT 'N',
  compulsory_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  rss_ind			VARCHAR(1) NOT NULL DEFAULT 'N',
  printable_ind		VARCHAR(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY ( s_item_type, s_attribute_type, order_no )
) TYPE=MyISAM COMMENT='System Item Attribute table';

#
# System Attribute Type Lookup table
#
DROP TABLE IF EXISTS s_attribute_type_lookup;
CREATE TABLE s_attribute_type_lookup (
  s_attribute_type		VARCHAR(10) NOT NULL,
  order_no				TINYINT(3) UNSIGNED,
  value					VARCHAR(50) NOT NULL,
  display				VARCHAR(255),
  img					VARCHAR(255),
  checked_ind			VARCHAR(1),
  PRIMARY KEY ( s_attribute_type, value )
) TYPE=MyISAM COMMENT='System Attribute Type Lookup table';

#
# System Item Status table
#
DROP TABLE IF EXISTS s_status_type;
CREATE table s_status_type (
  s_status_type				VARCHAR(1) NOT NULL DEFAULT 'Y',
  description				VARCHAR(30) NOT NULL,
  img						VARCHAR(255),
  delete_ind				VARCHAR(1) NOT NULL DEFAULT 'Y',
  change_owner_ind			VARCHAR(1) NOT NULL DEFAULT 'N',
  min_display_user_type		VARCHAR(1),
  min_create_user_type		VARCHAR(1),
  borrow_ind				VARCHAR(1) NOT NULL DEFAULT 'Y',
  status_comment_ind		VARCHAR(1) NOT NULL DEFAULT 'N',
  default_ind				VARCHAR(1) NOT NULL DEFAULT 'N',
  closed_ind				VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( s_status_type )
) TYPE=MyISAM COMMENT='System Item Status table';

#
# System Address type table
#
DROP TABLE IF EXISTS s_address_type;
CREATE TABLE s_address_type (
  s_address_type			VARCHAR(10) NOT NULL,
  description				VARCHAR(30) NOT NULL,
  display_order				TINYINT(2),
  min_create_user_type		VARCHAR(1) NOT NULL DEFAULT 'B', # borrower
  min_display_user_type		VARCHAR(1) NOT NULL DEFAULT 'N', # normal
  compulsory_for_user_type	VARCHAR(1) NOT NULL DEFAULT 'B', # normal
  closed_ind				VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( s_address_type )
) TYPE=MyISAM COMMENT='System address type';

#
# System Address Type Attribute relationship table
#
DROP TABLE IF EXISTS s_addr_attribute_type_rltshp;
CREATE TABLE s_addr_attribute_type_rltshp (
  s_address_type		VARCHAR(10) NOT NULL,
  s_attribute_type		VARCHAR(10) NOT NULL,
  order_no				TINYINT(3) unsigned NOT NULL,
  # override for s_attribute_type prompt field
  prompt				VARCHAR(30),
  min_create_user_type	VARCHAR(1),
  min_display_user_type	VARCHAR(1),
  compulsory_for_user_type VARCHAR(1),
  closed_ind			VARCHAR(1) NOT NULL default 'N',
  PRIMARY KEY ( s_address_type, s_attribute_type, order_no )
) TYPE=MyISAM COMMENT='System address attribute type relationship';

#
# User table
#
DROP TABLE IF EXISTS user;
CREATE TABLE user (
  user_id		VARCHAR(20) NOT NULL,
  fullname		VARCHAR(100) NOT NULL,
  pwd			VARCHAR(40),
  type			VARCHAR(1) NOT NULL DEFAULT 'N',
  language		VARCHAR(20),
  theme			VARCHAR(20),
  email_addr	VARCHAR(255),
  lastvisit		TIMESTAMP(14) NOT NULL,
  active_ind	VARCHAR(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY ( user_id )
) TYPE=MyISAM COMMENT='User table';

#
# User address
#
# public_address_ind = Y, means address details will be displayed on user profile page
# for all but guest users.  Where public_ind=Y, but borrow_access_ind = N, address
# details will be available but will not be included in notification emails.
#
# borrow_address_ind = Y, means address details will be included in borrow workflow
# emails and be made available to owners / borrowers.  For all but quick checkout
# actions, this value can be overriden for specific borrow events.
#
DROP TABLE IF EXISTS user_address;
CREATE TABLE user_address (
  sequence_number		INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id				VARCHAR(20) NOT NULL,
  s_address_type		VARCHAR(10) NOT NULL,
  start_dt				DATE NOT NULL DEFAULT '0000-00-00',
  end_dt				DATE,
  public_address_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  borrow_address_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  update_on				TIMESTAMP(14) NOT NULL,
  PRIMARY KEY ( sequence_number ),
  KEY user_address_idx ( user_id, s_address_type, start_dt )
) TYPE=MyISAM COMMENT='User address';

#
# User address attribute
#
DROP TABLE IF EXISTS user_address_attribute;
CREATE TABLE user_address_attribute (
  ua_sequence_number	INTEGER(10) UNSIGNED NOT NULL,
  s_attribute_type		VARCHAR(10) NOT NULL,
  order_no				TINYINT(3) UNSIGNED NOT NULL,
  attribute_no 			TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
  lookup_attribute_val 	VARCHAR(50),
  attribute_val			TEXT,
  update_on				TIMESTAMP(14) NOT NULL,
  PRIMARY KEY ( ua_sequence_number, s_attribute_type, order_no, attribute_no )
) TYPE=MyISAM COMMENT='User address attribute';

#
# Item table
#
DROP TABLE IF EXISTS item;
CREATE TABLE item (
  id			INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  title			VARCHAR(255) NOT NULL,
  s_item_type	VARCHAR(10) NOT NULL,
  PRIMARY KEY ( id ),
  KEY title_idx ( title ),
  KEY s_item_type_idx ( s_item_type )
) TYPE=MyISAM COMMENT='Item table';

#
# Item instance table
#
DROP TABLE IF EXISTS item_instance;
CREATE table item_instance (
  item_id			BIGINT(10) UNSIGNED NOT NULL,
  instance_no		SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1,
  owner_id			VARCHAR(20) NOT NULL,
  borrow_duration	SMALLINT(3) UNSIGNED,
  s_status_type		VARCHAR(1) NOT NULL DEFAULT 'Y',
  status_comment 	VARCHAR(255),
  update_on			TIMESTAMP(14) NOT NULL,
  PRIMARY KEY ( item_id, instance_no ),
  KEY owner_id_idx ( owner_id ),
  KEY s_status_type_idx ( s_status_type )
) TYPE=MyISAM COMMENT='Item Instance table';

CREATE TABLE item_instance_relationship (
    sequence_number INT( 10 ) NOT NULL AUTO_INCREMENT,
    item_id INT( 10 ) NOT NULL,
    instance_no SMALLINT( 5 ) NOT NULL,
    related_item_id INT( 10 ) NOT NULL,
    related_instance_no SMALLINT( 5 ) NOT NULL,
PRIMARY KEY ( sequence_number ),
UNIQUE KEY ( item_id, instance_no, related_item_id, related_instance_no )
) TYPE=MyISAM COMMENT = 'item instance relationship table';

#
# Item Attribute table
#
CREATE TABLE item_attribute (
  item_id				INTEGER(10) UNSIGNED NOT NULL,
  instance_no			SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  s_attribute_type		VARCHAR(10) NOT NULL,
  order_no				TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  attribute_no 			TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
  lookup_attribute_val	VARCHAR(50),
  attribute_val			TEXT,
  update_on				TIMESTAMP(14) NOT NULL,
  PRIMARY KEY ( item_id, instance_no, s_attribute_type, order_no, attribute_no )
) TYPE=MyISAM COMMENT='Item Attribute table';

#
# Borrowed Item table
#
DROP TABLE IF EXISTS borrowed_item;
CREATE TABLE borrowed_item (
  sequence_number	INTEGER(10) UNSIGNED NOT NULL auto_increment,
  item_id			INTEGER(10) UNSIGNED NOT NULL,
  instance_no		SMALLINT(5) UNSIGNED NOT NULL,
  borrower_id		VARCHAR(20) NOT NULL,
  borrow_duration	SMALLINT(3) UNSIGNED,
  total_duration	SMALLINT(3) UNSIGNED,
  status			VARCHAR(1) NOT NULL,
  update_on			TIMESTAMP(14) NOT NULL,
  PRIMARY KEY ( sequence_number ),
  KEY borrower_idx ( borrower_id ),
  KEY item_instance_idx ( item_id, instance_no )
) TYPE=MyISAM COMMENT='Borrowed Item table';

#
# Review table
#
DROP TABLE IF EXISTS review;
CREATE TABLE review (
  sequence_number	INTEGER(10) UNSIGNED NOT NULL auto_increment,
  author_id			VARCHAR(20) NOT NULL,
  item_id			INTEGER(10) UNSIGNED NOT NULL,
  comment			TEXT,
  rating			VARCHAR(1) NOT NULL,
  update_on			TIMESTAMP(14) NOT NULL,
  PRIMARY KEY ( sequence_number ),
  KEY author_idx ( author_id ),
  KEY item_idx ( item_id )
) TYPE=MyISAM COMMENT='Item Review table';

#
# Site Plugin Configuration
#
DROP TABLE IF EXISTS s_site_plugin;
CREATE TABLE s_site_plugin (
	site_type	VARCHAR(10) NOT NULL,
	classname	VARCHAR(50) NOT NULL,
	order_no	TINYINT(2) UNSIGNED,
	title		VARCHAR(50) NOT NULL,
	image		VARCHAR(255) NOT NULL,
	description	VARCHAR(255) NOT NULL,
	external_url	VARCHAR(255) NOT NULL,
	items_per_page	TINYINT(3) UNSIGNED NOT NULL,
	more_info_url	VARCHAR(255),
	PRIMARY KEY ( site_type )
) TYPE=MyISAM COMMENT='Site Plugin Configuration';

#
# This table provides any site plugin specific variable configuration,
# and a plugin should provide defaults for all such conf variables
# when installed, so that the user can correctly configure them
# if required based on the description field.
#
DROP TABLE IF EXISTS s_site_plugin_conf;
CREATE TABLE s_site_plugin_conf (
	site_type	VARCHAR(10) NOT NULL,
	name		VARCHAR(50) NOT NULL,
	keyid		VARCHAR(50) NOT NULL DEFAULT '0',
	description	VARCHAR(255),
	value		VARCHAR(255),
	PRIMARY KEY ( site_type, name, keyid )
) TYPE=MyISAM COMMENT='Site Plugin Configuration';

#
# Site Plugin Input Field
#
# This table will define the input fields generated for
# the plugin in the item add screen.
#
DROP TABLE IF EXISTS s_site_plugin_input_field;
CREATE TABLE s_site_plugin_input_field (
	site_type	VARCHAR(10) NOT NULL,
	field		VARCHAR(20) NOT NULL,
	order_no	TINYINT(2) UNSIGNED NOT NULL,
	description	VARCHAR(255) NOT NULL,
	prompt		VARCHAR(30) NOT NULL,
	field_type	VARCHAR(20) NOT NULL DEFAULT 'text',
	default_value		VARCHAR(50),
	refresh_mask	VARCHAR(255),
	PRIMARY KEY ( site_type, field )
) TYPE=MyISAM COMMENT='Site Plugin Input Field';

#
# Site Plugin Attribute Type Map
#
DROP TABLE IF EXISTS s_site_plugin_s_attribute_type_map;
CREATE TABLE s_site_plugin_s_attribute_type_map (
	sequence_number			INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	site_type			VARCHAR(10) NOT NULL,
	s_item_type_group		VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type			VARCHAR(10) NOT NULL DEFAULT '*',
	variable			VARCHAR(20) NOT NULL,
	s_attribute_type		VARCHAR(10) NOT NULL,
	lookup_attribute_val_restrict_ind 	VARCHAR(1) DEFAULT 'N',
	PRIMARY KEY ( sequence_number ),
	UNIQUE KEY ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type )
) TYPE=MyISAM COMMENT='Site Plugin Attribute Type Map';

#
# Site Plugin Attribute Type Lookup Map
#
DROP TABLE IF EXISTS s_site_plugin_s_attribute_type_lookup_map;
CREATE TABLE s_site_plugin_s_attribute_type_lookup_map (
	sequence_number		INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	site_type		VARCHAR(10) NOT NULL,
	s_attribute_type	VARCHAR(10) NOT NULL,
	value			VARCHAR(255) NOT NULL,
	lookup_attribute_val	VARCHAR(50) NOT NULL,
	PRIMARY KEY ( sequence_number ),
	UNIQUE KEY ( site_type, s_attribute_type, value, lookup_attribute_val )
) TYPE=MyISAM COMMENT='Site Plugin Attribute Type Lookup Map';

#
# Site Plugin Link
#
DROP TABLE IF EXISTS s_site_plugin_link;
CREATE TABLE s_site_plugin_link (
	sequence_number		INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	site_type		VARCHAR(10) NOT NULL,
	s_item_type_group	VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type		VARCHAR(10) NOT NULL DEFAULT '*',
	order_no		TINYINT(2) UNSIGNED NOT NULL,
	description		VARCHAR(50),
	url			VARCHAR(255),
	title_url		VARCHAR(255),
	PRIMARY KEY ( sequence_number ),
	UNIQUE KEY ( site_type, s_item_type_group, s_item_type, order_no )
) TYPE=MyISAM COMMENT='Site Plugin Link';

#
# Import Cache table
#
DROP TABLE IF EXISTS import_cache;
CREATE TABLE import_cache (
  sequence_number	INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id			VARCHAR(20) NOT NULL,
  plugin_name		VARCHAR(100),
  content_length	INTEGER(10) UNSIGNED NOT NULL,
  cache_file		VARCHAR(255) NOT NULL,
  PRIMARY KEY ( sequence_number )
) TYPE=MyISAM COMMENT='Import Cache table';

#
# File Cache table
#
# The code will enforce a URL and Location length of 2083 characters,
# according to http://support.microsoft.com/kb/q208427/
#
DROP TABLE IF EXISTS file_cache;
CREATE TABLE file_cache (
  sequence_number		INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  cache_type			VARCHAR(10) NOT NULL DEFAULT 'HTTP',
  cache_date			DATETIME NOT NULL,
  expire_date			DATETIME,
  url					TEXT NOT NULL,
  # if url was redirected, this stores the redirected URL
  location				TEXT,
  upload_file_ind 		VARCHAR(1) NOT NULL DEFAULT 'N',
  content_type			VARCHAR(100) NOT NULL,
  content_length		INTEGER(10) UNSIGNED NOT NULL,
  cache_file			VARCHAR(255) NOT NULL,
  cache_file_thumb		VARCHAR(255),
  PRIMARY KEY ( sequence_number )
) TYPE=MyISAM COMMENT='File Cache table';

#
# Create announcement table
#
DROP TABLE IF EXISTS announcement;
CREATE TABLE announcement (
  sequence_number   INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id           VARCHAR(20) NOT NULL,
  title             VARCHAR(255) NOT NULL,
  content	        TEXT,
  min_user_type     VARCHAR(1) NOT NULL DEFAULT 'B',
  submit_on         TIMESTAMP(14) NOT NULL,
  display_days      INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  closed_ind        VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( sequence_number ),
  KEY submit_idx ( submit_on )
) TYPE=MyISAM COMMENT='Site Announcements table';

CREATE TABLE s_file_type_content_group (
  content_group		VARCHAR(10) NOT NULL,
  PRIMARY KEY ( content_group )
) TYPE=MyISAM COMMENT='System Supported File Content Groups';

# image is basename of image provided via theme_image call.
CREATE TABLE s_file_type (
  content_type			VARCHAR(100) NOT NULL,
  content_group			VARCHAR(10) NOT NULL,
  description			VARCHAR(255),
  image					VARCHAR(255),
  thumbnail_support_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( content_type )
) TYPE=MyISAM COMMENT='System Supported File Types';

CREATE TABLE s_file_type_extension (
  content_type	VARCHAR(100) NOT NULL,
  extension		VARCHAR(10) NOT NULL,
  default_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( content_type, extension )
) TYPE=MyISAM COMMENT='System File Type Alternate Extension';

#
# System Language table
#
DROP TABLE IF EXISTS s_language;
CREATE TABLE s_language (
	language	VARCHAR(10) NOT NULL,
	default_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
	description	VARCHAR(50) NOT NULL,
	PRIMARY KEY ( language )
) TYPE=MyISAM COMMENT='System Language Table';

#
# System Language Variable Table
#
DROP TABLE IF EXISTS s_language_var;
CREATE TABLE s_language_var (
	language	VARCHAR(10) NOT NULL,
	varname		VARCHAR(50) NOT NULL,
	value		TEXT,
	PRIMARY KEY ( language, varname )
) TYPE=MyISAM COMMENT='System Language Variable Table';

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
DROP TABLE IF EXISTS s_table_language_var;
CREATE TABLE s_table_language_var (
	language	VARCHAR(10) NOT NULL,
	tablename	VARCHAR(50) NOT NULL,
	columnname	VARCHAR(50) NOT NULL,
	key1		VARCHAR(50) NOT NULL,
	key2		VARCHAR(50) NOT NULL,
	key3		VARCHAR(50) NOT NULL,
	value		TEXT,
	PRIMARY KEY ( language, tablename, columnname, key1, key2, key3 )
) TYPE=MyISAM COMMENT='System Language Variable Table';
