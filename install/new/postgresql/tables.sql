-- --------------------------------------------------------
-- OpenDb Tables
-- --------------------------------------------------------

--
-- Install php session table to provide option of using database
-- session management.
--
DROP TABLE IF EXISTS php_session;
CREATE TABLE php_session(
	SID 		CHAR(32) NOT NULL,
	expiration 	INT NOT NULL,
	value 		TEXT NOT NULL,
	PRIMARY KEY ( SID )
) ;
COMMENT ON TABLE php_session IS 'PHP Session table';

--
-- System Config Group
--
DROP TABLE IF EXISTS s_config_group;
CREATE TABLE s_config_group (
	id			VARCHAR(50) NOT NULL,
	name		VARCHAR(50) NOT NULL,
	order_no	SMALLINT NOT NULL DEFAULT 0,
	description	VARCHAR(255) NOT NULL,
	PRIMARY KEY ( id )
) ;
COMMENT ON TABLE s_config_group IS 'System Config Group';

--
-- System Config Group Item
--
-- type:
-- 		array - keys will be numeric and in sequence only.
--			The array type, supports several subtypes, which will cause each
--			individual value in the array to be validated individually:
--				text
--				number
--
-- 		boolean - TRUE or FALSE only
-- 		text - arbritrary text
--       email - email address
-- 		textarea - arbritrary text
-- 		number - enforce a numeric value
-- 		datemask - enforce a date mask.
--       language - Restrict to a single legal language only
--       theme - Restrict to a single legal language only
--       value_select [option1,option2] - choose one option from the list, specified in the subtype
--
-- order_no - should be unique for a group_id only
--
DROP TABLE IF EXISTS s_config_group_item;
CREATE TABLE s_config_group_item (
	group_id		VARCHAR(50) NOT NULL,
	id				VARCHAR(100) NOT NULL,
	keyid       	VARCHAR(50) NOT NULL DEFAULT '0',
	order_no		SMALLINT NOT NULL DEFAULT 0,
	prompt      	VARCHAR(50) NOT NULL,
	description		TEXT NOT NULL,
	type			VARCHAR(30) NOT NULL DEFAULT 'text',
    subtype			VARCHAR(255),
	PRIMARY KEY ( group_id, id, keyid )
) ;
COMMENT ON TABLE s_config_group_item IS 'System Config Group Item';

--
-- Override Config Group Item Value
--
DROP TABLE IF EXISTS s_config_group_item_var;
CREATE TABLE s_config_group_item_var (
	group_id	VARCHAR(50) NOT NULL,
	id			VARCHAR(100) NOT NULL,
	keyid		VARCHAR(50) NOT NULL DEFAULT '0',
	value		TEXT NOT NULL,
	PRIMARY KEY ( group_id, id, keyid )
);
COMMENT ON TABLE s_config_group_item_var IS 'Config Group Item Variable';

--
-- System Item Type table
--
DROP TABLE IF EXISTS s_item_type;
CREATE TABLE s_item_type (
  s_item_type		VARCHAR(10) NOT NULL,
  description		VARCHAR(60) NOT NULL,
  image				VARCHAR(255),
  order_no			SMALLINT,
  PRIMARY KEY ( s_item_type )
) ;
COMMENT ON TABLE s_item_type IS 'System Item Type table';

--
-- System Item Type Group
--
DROP TABLE IF EXISTS s_item_type_group;
CREATE TABLE s_item_type_group (
	s_item_type_group	VARCHAR(10) NOT NULL,
	description			VARCHAR(60) NOT NULL,
	PRIMARY KEY ( s_item_type_group )
);
COMMENT ON TABLE s_item_type_group IS 'System Item Type Group';

--
-- System Item Type Group Relationship
--
DROP TABLE IF EXISTS s_item_type_group_rltshp;
CREATE TABLE s_item_type_group_rltshp (
	s_item_type_group	VARCHAR(10) NOT NULL,
	s_item_type		VARCHAR(10) NOT NULL,
	PRIMARY KEY ( s_item_type_group, s_item_type )
);
COMMENT ON TABLE s_item_type_group_rltshp IS 'System Item Type Group Relationship';

--
-- Title Display Mask configuration
--
DROP TABLE IF EXISTS s_title_display_mask;
CREATE TABLE s_title_display_mask (
	id				VARCHAR(50) NOT NULL,
	description     VARCHAR(100) NOT NULL,
	PRIMARY KEY ( id )
);
COMMENT ON TABLE s_title_display_mask IS 'System Title Display Mask Config';

DROP TABLE IF EXISTS s_title_display_mask_item;
CREATE TABLE s_title_display_mask_item (
	stdm_id				VARCHAR(50) NOT NULL,
	s_item_type_group	VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type			VARCHAR(10) NOT NULL DEFAULT '*',
	display_mask		TEXT,
	PRIMARY KEY ( stdm_id, s_item_type_group, s_item_type )
);
COMMENT ON TABLE s_title_display_mask_item IS 'System Title Display Mask Config Item';

--
-- Item Listing config
--
DROP TABLE IF EXISTS s_item_listing_conf;
CREATE TABLE s_item_listing_conf (
	id                 SERIAL,
	s_item_type_group  VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type        VARCHAR(10) NOT NULL DEFAULT '*',
        UNIQUE (s_item_type_group, s_item_type),
	PRIMARY KEY ( id )
);
COMMENT ON TABLE s_item_listing_conf IS 'Item Listing Configuration';

--
-- Item Listing Column Conf
--
-- column_type:
--	s_field_type
--       ITEM_ID, TITLE, STATUSTYPE, STATUSCMNT, CATEGORY, RATING
--
--	s_attribute_type
--
--	s_item_type
--	owner
--	action_links
--	borrow_status
--
-- orderby_datatype:
--	NUMERIC
--   ALPHA
--
DROP TABLE IF EXISTS s_item_listing_column_conf;
CREATE TABLE s_item_listing_column_conf (
	silc_id					INTEGER NOT NULL,
	column_no				INTEGER NOT NULL,
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
);
COMMENT ON TABLE s_item_listing_column_conf IS 'Item Listing Column Configuration';

--
-- System Attribute Type table
--
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
);
COMMENT ON TABLE s_attribute_type IS 'System Attribute table';

--
-- System Item Attribute Type relationship table
--
DROP TABLE IF EXISTS s_item_attribute_type;
CREATE TABLE s_item_attribute_type (
  s_item_type		VARCHAR(10) NOT NULL,
  s_attribute_type	VARCHAR(10) NOT NULL,
  order_no			INTEGER NOT NULL DEFAULT 0,
  prompt			VARCHAR(30),
  instance_attribute_ind VARCHAR(1) NOT NULL DEFAULT 'N',
  compulsory_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  rss_ind			VARCHAR(1) NOT NULL DEFAULT 'N',
  printable_ind		VARCHAR(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY ( s_item_type, s_attribute_type, order_no )
);
COMMENT ON TABLE s_item_attribute_type IS 'System Item Attribute table';

--
-- System Attribute Type Lookup table
--
DROP TABLE IF EXISTS s_attribute_type_lookup;
CREATE TABLE s_attribute_type_lookup (
  s_attribute_type		VARCHAR(10) NOT NULL,
  order_no				INTEGER,
  value					VARCHAR(50) NOT NULL,
  display				VARCHAR(255),
  img					VARCHAR(255),
  checked_ind			VARCHAR(1),
  PRIMARY KEY ( s_attribute_type, value )
);
COMMENT ON TABLE s_attribute_type_lookup IS 'System Attribute Type Lookup table';

--
-- System Item Status table
--
DROP TABLE IF EXISTS s_status_type;
CREATE table s_status_type (
  s_status_type				VARCHAR(1) NOT NULL DEFAULT 'Y',
  description				VARCHAR(30) NOT NULL,
  img						VARCHAR(255),
  delete_ind				VARCHAR(1) NOT NULL DEFAULT 'Y',
  change_owner_ind			VARCHAR(1) NOT NULL DEFAULT 'N',
  borrow_ind				VARCHAR(1) NOT NULL DEFAULT 'Y',
  status_comment_ind		VARCHAR(1) NOT NULL DEFAULT 'N',
  hidden_ind 				VARCHAR(1) NOT NULL DEFAULT 'N',
  default_ind				VARCHAR(1) NOT NULL DEFAULT 'N',
  closed_ind				VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( s_status_type )
);
COMMENT ON TABLE s_status_type IS 'System Item Status table';

--
-- System Address type table
--
DROP TABLE IF EXISTS s_address_type;
CREATE TABLE s_address_type (
  s_address_type			VARCHAR(10) NOT NULL,
  description				VARCHAR(30) NOT NULL,
  display_order				SMALLINT,
  closed_ind				VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( s_address_type )
);
COMMENT ON TABLE s_address_type IS 'System address type';

--
-- System Address Type Attribute relationship table
--
DROP TABLE IF EXISTS s_addr_attribute_type_rltshp;
CREATE TABLE s_addr_attribute_type_rltshp (
  s_address_type		VARCHAR(10) NOT NULL,
  s_attribute_type		VARCHAR(10) NOT NULL,
  order_no				INTEGER NOT NULL,
  -- override for s_attribute_type prompt field
  prompt				VARCHAR(30),
  closed_ind			VARCHAR(1) NOT NULL default 'N',
  PRIMARY KEY ( s_address_type, s_attribute_type, order_no )
);
COMMENT ON TABLE s_addr_attribute_type_rltshp IS 'System address attribute type relationship';

CREATE TABLE s_role (
    role_name VARCHAR(20) NOT NULL,
    description VARCHAR(100),
	signup_avail_ind VARCHAR(1) NOT NULL DEFAULT 'Y',
PRIMARY KEY ( role_name )
);
COMMENT ON TABLE s_role IS 'System Role table';

CREATE TABLE s_permission (
    permission_name VARCHAR(30) NOT NULL,
	description VARCHAR(100),
PRIMARY KEY ( permission_name )
);
COMMENT ON TABLE s_permission IS 'System Permission table';

CREATE TABLE s_role_permission (
	role_name VARCHAR(20) NOT NULL,
    permission_name VARCHAR(30) NOT NULL,
PRIMARY KEY ( role_name, permission_name )
);
COMMENT ON TABLE s_role_permission IS 'System Role Permission table';

--
-- User table
--
DROP TABLE IF EXISTS "user";
CREATE TABLE "user" (
  user_id		VARCHAR(20) NOT NULL,
  fullname		VARCHAR(100) NOT NULL,
  pwd			VARCHAR(40),
  user_role 	VARCHAR(20) NOT NULL,
  language		VARCHAR(20),
  theme			VARCHAR(20),
  email_addr	VARCHAR(255),
  lastvisit		TIMESTAMP NOT NULL DEFAULT '1970/01/01',
  active_ind	VARCHAR(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY ( user_id )
);
COMMENT ON TABLE "user" IS 'User table';

--
-- User address
--
-- public_address_ind = Y, means address details will be displayed on user profile page
-- for all but public access users.  Where public_ind=Y, but borrow_access_ind = N, address
-- details will be available but will not be included in notification emails.
--
-- borrow_address_ind = Y, means address details will be included in borrow workflow
-- emails and be made available to owners / borrowers.  For all but quick checkout
-- actions, this value can be overriden for specific borrow events.
--
DROP TABLE IF EXISTS user_address;
CREATE TABLE user_address (
  sequence_number       SERIAL,
  user_id               VARCHAR(20) NOT NULL,
  s_address_type        VARCHAR(10) NOT NULL,
  start_dt              DATE NOT NULL DEFAULT '1970-01-01',
  end_dt                DATE,
  public_address_ind    VARCHAR(1) NOT NULL DEFAULT 'N',
  borrow_address_ind    VARCHAR(1) NOT NULL DEFAULT 'N',
  update_on             TIMESTAMP NOT NULL,
  PRIMARY KEY ( sequence_number )
);
CREATE INDEX user_address_idx ON user_address(user_id, s_address_type, start_dt) ;
COMMENT ON TABLE user_address IS 'User address';

--
-- User address attribute
--
DROP TABLE IF EXISTS user_address_attribute;
CREATE TABLE user_address_attribute (
  ua_sequence_number	INTEGER NOT NULL,
  s_attribute_type		VARCHAR(10) NOT NULL,
  order_no				INTEGER NOT NULL,
  attribute_no 			INTEGER NOT NULL DEFAULT 1,
  lookup_attribute_val 	VARCHAR(50),
  attribute_val			TEXT,
  update_on				TIMESTAMP NOT NULL,
  PRIMARY KEY ( ua_sequence_number, s_attribute_type, order_no, attribute_no )
);
COMMENT ON TABLE user_address_attribute IS 'User address attribute';

--
-- Item table
--
DROP TABLE IF EXISTS item;
CREATE TABLE item (
  id			SERIAL,
  title			VARCHAR(255) NOT NULL,
  s_item_type	VARCHAR(10) NOT NULL,
  PRIMARY KEY ( id )
);
CREATE INDEX title_idx ON item(title) ;
CREATE INDEX s_item_type_idx ON item(s_item_type) ;
COMMENT ON TABLE item IS 'Item table';

--
-- Item instance table
--
DROP TABLE IF EXISTS item_instance;
CREATE table item_instance (
  item_id			BIGINT NOT NULL,
  instance_no		INTEGER NOT NULL DEFAULT 1,
  owner_id			VARCHAR(20) NOT NULL,
  borrow_duration	INTEGER,
  s_status_type		VARCHAR(1) NOT NULL DEFAULT 'Y',
  status_comment 	VARCHAR(255),
  update_on			TIMESTAMP NOT NULL,
  PRIMARY KEY ( item_id, instance_no )
);
CREATE INDEX owner_id_idx ON item_instance (owner_id) ;
CREATE INDEX s_status_type_idx ON item_instance (s_status_type) ;
COMMENT ON TABLE item_instance IS 'Item Instance table';

CREATE TABLE item_instance_relationship (
    sequence_number SERIAL,
    item_id INTEGER NOT NULL,
    instance_no INTEGER NOT NULL,
    related_item_id INTEGER NOT NULL,
    related_instance_no INTEGER NOT NULL,
UNIQUE (item_id, instance_no, related_item_id, related_instance_no),
PRIMARY KEY ( sequence_number )
);
COMMENT ON TABLE item_instance_relationship IS 'item instance relationship table';

--
-- Item Attribute table
--
CREATE TABLE item_attribute (
  item_id				INTEGER NOT NULL,
  instance_no			INTEGER NOT NULL DEFAULT 0,
  s_attribute_type		VARCHAR(10) NOT NULL,
  order_no				SMALLINT NOT NULL DEFAULT 0,
  attribute_no 			SMALLINT NOT NULL DEFAULT 1,
  lookup_attribute_val	VARCHAR(50),
  attribute_val			TEXT,
  update_on				TIMESTAMP NOT NULL,
  PRIMARY KEY ( item_id, instance_no, s_attribute_type, order_no, attribute_no )
);
COMMENT ON TABLE item_attribute IS 'Item Attribute table';

--
-- User item interest table
--
DROP TABLE IF EXISTS user_item_interest;
CREATE TABLE user_item_interest (
	sequence_number	SERIAL,
	item_id			INTEGER NOT NULL,
	instance_no		SMALLINT NOT NULL,
	user_id			VARCHAR(20) NOT NULL,
	level			VARCHAR(1) NOT NULL,
	comment			text,
	update_on		TIMESTAMP NOT NULL,
	PRIMARY KEY ( sequence_number )
);
CREATE INDEX user_idx ON user_item_interest(user_id) ;
CREATE INDEX item_idx ON user_item_interest(item_id) ;
COMMENT ON TABLE user_item_interest IS 'user item interest table';

--
-- Borrowed Item table
--
DROP TABLE IF EXISTS borrowed_item;
CREATE TABLE borrowed_item (
  sequence_number	SERIAL,
  item_id			INTEGER NOT NULL,
  instance_no		INTEGER NOT NULL,
  borrower_id		VARCHAR(20) NOT NULL,
  borrow_duration	SMALLINT,
  total_duration	SMALLINT,
  status			VARCHAR(1) NOT NULL,
  update_on			TIMESTAMP NOT NULL,
  PRIMARY KEY ( sequence_number )
);
CREATE INDEX borrower_idx ON borrowed_item(borrower_id) ;
CREATE INDEX item_instance_idx ON borrowed_item(item_id,instance_no) ;
COMMENT ON TABLE borrowed_item IS 'Borrowed Item table';

--
-- Review table
--
DROP TABLE IF EXISTS review;
CREATE TABLE review (
  sequence_number	SERIAL,
  author_id			VARCHAR(20) NOT NULL,
  item_id			INTEGER NOT NULL,
  comment			TEXT,
  rating			VARCHAR(1) NOT NULL,
  update_on			TIMESTAMP NOT NULL,
  PRIMARY KEY ( sequence_number )
);
CREATE INDEX author_idx ON review(author_id) ;
CREATE INDEX item_review_idx ON review(item_id) ;
COMMENT ON TABLE review IS 'Item Review table';

--
-- Mailbox for audit of all email sent from within opendb.
--
CREATE TABLE mailbox (
    sequence_number SERIAL,
    sent			TIMESTAMP NOT NULL,
    to_user_id	 	VARCHAR(20) NOT NULL,
    from_user_id 	VARCHAR(20),
    from_email_addr	VARCHAR(255),
    subject			VARCHAR(100),
    message			TEXT,
PRIMARY KEY ( sequence_number )
);
COMMENT ON TABLE mailbox IS 'mailbox';

--
-- Site Plugin Configuration
--
DROP TABLE IF EXISTS s_site_plugin;
CREATE TABLE s_site_plugin (
	site_type	VARCHAR(10) NOT NULL,
	classname	VARCHAR(50) NOT NULL,
	order_no	SMALLINT,
	title		VARCHAR(50) NOT NULL,
	image		VARCHAR(255) NOT NULL,
	description	VARCHAR(255) NOT NULL,
	external_url	VARCHAR(255) NOT NULL,
	items_per_page	SMALLINT NOT NULL,
	more_info_url	VARCHAR(255),
	PRIMARY KEY ( site_type )
);
COMMENT ON TABLE s_site_plugin IS 'Site Plugin Configuration';

--
-- This table provides any site plugin specific variable configuration,
-- and a plugin should provide defaults for all such conf variables
-- when installed, so that the user can correctly configure them
-- if required based on the description field.
--
DROP TABLE IF EXISTS s_site_plugin_conf;
CREATE TABLE s_site_plugin_conf (
	site_type	VARCHAR(10) NOT NULL,
	name		VARCHAR(50) NOT NULL,
	keyid		VARCHAR(50) NOT NULL DEFAULT '0',
	description	VARCHAR(255),
	value		VARCHAR(255),
	PRIMARY KEY ( site_type, name, keyid )
);
COMMENT ON TABLE s_site_plugin_conf IS 'Site Plugin Configuration';

--
-- Site Plugin Input Field
--
-- This table will define the input fields generated for
-- the plugin in the item add screen.
--
DROP TABLE IF EXISTS s_site_plugin_input_field;
CREATE TABLE s_site_plugin_input_field (
	site_type	VARCHAR(10) NOT NULL,
	field		VARCHAR(20) NOT NULL,
	order_no	SMALLINT NOT NULL,
	description	VARCHAR(255) NOT NULL,
	prompt		VARCHAR(30) NOT NULL,
	field_type	VARCHAR(20) NOT NULL DEFAULT 'text',
	default_value		VARCHAR(50),
	refresh_mask	VARCHAR(255),
	PRIMARY KEY ( site_type, field )
);
COMMENT ON TABLE s_site_plugin_input_field IS 'Site Plugin Input Field';

--
-- Site Plugin Attribute Type Map
--
DROP TABLE IF EXISTS s_site_plugin_s_attribute_type_map;
CREATE TABLE s_site_plugin_s_attribute_type_map (
	sequence_number			SERIAL,
	site_type			VARCHAR(10) NOT NULL,
	s_item_type_group		VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type			VARCHAR(10) NOT NULL DEFAULT '*',
	variable			VARCHAR(20) NOT NULL,
	s_attribute_type		VARCHAR(10) NOT NULL,
	lookup_attribute_val_restrict_ind 	VARCHAR(1) DEFAULT 'N',
        UNIQUE (site_type, s_item_type_group, s_item_type, variable, s_attribute_type),
	PRIMARY KEY ( sequence_number )
);
COMMENT ON TABLE s_site_plugin_s_attribute_type_map IS 'Site Plugin Attribute Type Map';

--
-- Site Plugin Attribute Type Lookup Map
--
DROP TABLE IF EXISTS s_site_plugin_s_attribute_type_lookup_map;
CREATE TABLE s_site_plugin_s_attribute_type_lookup_map (
	sequence_number		SERIAL,
	site_type		VARCHAR(10) NOT NULL,
	s_attribute_type	VARCHAR(10) NOT NULL,
	value			VARCHAR(255) NOT NULL,
	lookup_attribute_val	VARCHAR(50) NOT NULL,
        UNIQUE (site_type, s_attribute_type, value, lookup_attribute_val),
	PRIMARY KEY ( sequence_number )
);
COMMENT ON TABLE s_site_plugin_s_attribute_type_lookup_map IS 'Site Plugin Attribute Type Lookup Map';

--
-- Site Plugin Link
--
DROP TABLE IF EXISTS s_site_plugin_link;
CREATE TABLE s_site_plugin_link (
	sequence_number		SERIAL,
	site_type		VARCHAR(10) NOT NULL,
	s_item_type_group	VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type		VARCHAR(10) NOT NULL DEFAULT '*',
	order_no		SMALLINT NOT NULL,
	description		VARCHAR(50),
	url			VARCHAR(255),
	title_url		VARCHAR(255),
	PRIMARY KEY ( sequence_number ),
	UNIQUE ( site_type, s_item_type_group, s_item_type, order_no )
);
COMMENT ON TABLE s_site_plugin_link IS 'Site Plugin Link';

--
-- Import Cache table
--
DROP TABLE IF EXISTS import_cache;
CREATE TABLE import_cache (
  sequence_number	SERIAL,
  user_id			VARCHAR(20) NOT NULL,
  plugin_name		VARCHAR(100),
  content_length	INTEGER NOT NULL,
  cache_file		VARCHAR(255) NOT NULL,
  PRIMARY KEY ( sequence_number )
);
COMMENT ON TABLE import_cache IS 'Import Cache table';

--
-- File Cache table
--
-- The code will enforce a URL and Location length of 2083 characters,
-- according to http://support.microsoft.com/kb/q208427/
--
DROP TABLE IF EXISTS file_cache;
CREATE TABLE file_cache (
  sequence_number		SERIAL,
  cache_type			VARCHAR(10) NOT NULL DEFAULT 'HTTP',
  cache_date			DATE NOT NULL,
  expire_date			DATE,
  url					TEXT,
  -- if url was redirected, this stores the redirected URL
  location				TEXT,
  upload_file_ind 		VARCHAR(1) NOT NULL DEFAULT 'N',
  content_type			VARCHAR(100),
  content_length		INTEGER,
  cache_file			VARCHAR(255),
  cache_file_thumb		VARCHAR(255),
  PRIMARY KEY ( sequence_number )
);
COMMENT ON TABLE file_cache IS 'File Cache table';

--
-- Create announcement table
--
DROP TABLE IF EXISTS announcement;
CREATE TABLE announcement (
  sequence_number   SERIAL,
  user_id           VARCHAR(20) NOT NULL,
  title             VARCHAR(255) NOT NULL,
  content	        TEXT,
  submit_on         TIMESTAMP NOT NULL,
  display_days      INTEGER NOT NULL DEFAULT 0,
  closed_ind        VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( sequence_number )
);
CREATE INDEX submit_idx ON announcement(submit_on) ;
COMMENT ON TABLE announcement IS 'Site Announcements table';

CREATE TABLE s_file_type_content_group (
  content_group		VARCHAR(10) NOT NULL,
  PRIMARY KEY ( content_group )
);
COMMENT ON TABLE s_file_type_content_group IS 'System Supported File Content Groups';

-- image is basename of image provided via theme_image call.
CREATE TABLE s_file_type (
  content_type			VARCHAR(100) NOT NULL,
  content_group			VARCHAR(10) NOT NULL,
  description			VARCHAR(255),
  image					VARCHAR(255),
  thumbnail_support_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( content_type )
);
COMMENT ON TABLE s_file_type IS 'System Supported File Types';

CREATE TABLE s_file_type_extension (
  content_type	VARCHAR(100) NOT NULL,
  extension		VARCHAR(10) NOT NULL,
  default_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY ( content_type, extension )
);
COMMENT ON TABLE s_file_type_extension IS 'System File Type Alternate Extension';

--
-- System Language table
--
DROP TABLE IF EXISTS s_language;
CREATE TABLE s_language (
	language	VARCHAR(10) NOT NULL,
	default_ind	VARCHAR(1) NOT NULL DEFAULT 'N',
	description	VARCHAR(50) NOT NULL,
	PRIMARY KEY ( language )
);
COMMENT ON TABLE s_language IS 'System Language Table';

--
-- System Language Variable Table
--
DROP TABLE IF EXISTS s_language_var;
CREATE TABLE s_language_var (
	language	VARCHAR(10) NOT NULL,
	varname		VARCHAR(50) NOT NULL,
	value		TEXT,
	PRIMARY KEY ( language, varname )
);
COMMENT ON TABLE s_language_var IS 'System Language Variable Table';

--
-- System Table Language Variable Table
--
-- tablename is one of:
--	s_item_type 					(description) 	[key1 = s_item_type]
--	s_item_type_group				(description) 	[key1 = s_item_type_group]
--	s_attribute_type				(prompt)		[key1 = s_attribute_type]	
--	s_item_attribute_type			(prompt)		[key1 = s_item_type AND key2 = s_attribute_type AND key3 = order_no ]					
--	s_attribute_type_lookup			(display)		[key1 = s_attribute_type AND key2 = value]	
--	s_status_type					(description)	[key1 = s_status_type]
--	s_address_type					(description)	[key1 = s_address_type]
--	s_addr_attribute_type_rltshp	(prompt)		[key1 = s_address_type AND key2 = s_attribute_type AND key3 = order_no ]
--
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
);
COMMENT ON TABLE s_table_language_var IS 'System Language Variable Table';


-- Function for mysql - postgresl compatibility
CREATE OR REPLACE FUNCTION from_unixtime(integer) RETURNS timestamp AS '
SELECT
$1::abstime::timestamp without time zone AS result
' LANGUAGE 'SQL';
CREATE OR REPLACE FUNCTION unix_timestamp() RETURNS integer AS '
SELECT
ROUND(EXTRACT( EPOCH FROM abstime(now()) ))::int4 AS result;
' LANGUAGE 'SQL';
CREATE OR REPLACE FUNCTION unix_timestamp(timestamp with time zone) RETURNS integer AS '
SELECT
ROUND(EXTRACT( EPOCH FROM ABSTIME($1) ))::int4 AS result;
' LANGUAGE 'SQL';

CREATE OR REPLACE FUNCTION IFNULL (text, text) RETURNS text AS
  'SELECT COALESCE($1,$2) AS result'
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION ifnull (int4, int4) returns int4 as '
select coalesce($1, $2) as result
' language 'sql';
CREATE OR REPLACE FUNCTION to_days(timestamp) returns integer as '
       select date_part(''day'', $1 - ''0000-01-01'')::int4 as result
' language 'sql';
CREATE OR REPLACE FUNCTION to_days(timestamp with time zone) returns integer as '
       select date_part(''day'', $1 - ''0000-01-01'')::int4 as result
' language 'sql';
CREATE OR REPLACE FUNCTION from_days(integer) returns timestamp as '
       select ''0000-01-02''::timestamp + ($1 || '' days'')::interval as result
' language 'SQL';

CREATE OR REPLACE FUNCTION concat(text, text) RETURNS text AS $$
    SELECT $1 || $2;
$$ LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION concat(text, text, text) RETURNS text AS $$
    SELECT $1 || $2 || $3;
$$ LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION concat(text, text, text, text) RETURNS text AS $$
    SELECT $1 || $2 || $3 || $4;
$$ LANGUAGE 'sql';

CREATE LANGUAGE "plpgsql" ;

CREATE OR REPLACE FUNCTION "if"(boolean, text, text) RETURNS text AS '
BEGIN
IF $1 IS NOT NULL AND $1 IS TRUE THEN
RETURN $2;
ELSE
RETURN $3;
END IF;
END;
' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION "if"(integer, text, text) RETURNS text AS '
BEGIN
IF $1 IS NOT NULL AND $1 > 0 THEN
RETURN $2;
ELSE
RETURN $3;
END IF;
END;
' LANGUAGE 'plpgsql';
