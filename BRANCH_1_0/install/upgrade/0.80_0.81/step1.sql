#
# System Item Type Group
#
# system_ind - indicates if a group should be used by the system
# to group s_item_type's or if the grouping is only for use in
# listings functionality.
#
CREATE TABLE s_item_type_group (
	s_item_type_group	VARCHAR(10) NOT NULL,
	description		VARCHAR(60) NOT NULL,
	system_ind		VARCHAR(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY ( s_item_type_group )
) TYPE=MyISAM COMMENT='System Item Type Group';

#
# System Item Type Group Relationship
#
CREATE TABLE s_item_type_group_rltshp (
	s_item_type_group	VARCHAR(10) NOT NULL,
	s_item_type		VARCHAR(10) NOT NULL,
	PRIMARY KEY ( s_item_type_group, s_item_type )
) TYPE=MyISAM COMMENT='System Item Type Group Relationship';

#
# System Item Type Group system data
#
INSERT INTO s_item_type_group(s_item_type_group, description, system_ind) VALUES('AUDIO', 'Audio Item Types', 'Y');
INSERT INTO s_item_type_group(s_item_type_group, description, system_ind) VALUES('VIDEO', 'Video Item Types', 'Y');
INSERT INTO s_item_type_group(s_item_type_group, description, system_ind) VALUES('OTHER', 'Miscellaneous Item Types', 'N');

INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('AUDIO', 'CD');
INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('AUDIO', 'MP3');
INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('VIDEO', 'DVD');
INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('VIDEO', 'VHS');
INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('VIDEO', 'VCD');
INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('VIDEO', 'LD');
INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('VIDEO', 'DIVX');
INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('OTHER', 'BOOK');
INSERT INTO s_item_type_group_rltshp(s_item_type_group, s_item_type) VALUES('OTHER', 'GAME');

#
# Site Plugin Configuration
#
CREATE TABLE s_site_plugin (
	site_type	VARCHAR(10) NOT NULL,
	classname	VARCHAR(50) NOT NULL,
	order_no	TINYINT(2) UNSIGNED NOT NULL,
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
# the plugin in the item_add script.
#
CREATE TABLE s_site_plugin_input_field (
	site_type	VARCHAR(10) NOT NULL,
	field		VARCHAR(20) NOT NULL,
	order_no	TINYINT(2) UNSIGNED NOT NULL,
	description	VARCHAR(255) NOT NULL,
	prompt		VARCHAR(30) NOT NULL,
	field_type	VARCHAR(10) NOT NULL DEFAULT 'text',
	default_value	VARCHAR(50),
	refresh_mask	VARCHAR(255),
	PRIMARY KEY ( site_type, field )
) TYPE=MyISAM COMMENT='Site Plugin Input Field';

#
# Site Plugin Attribute Type Map
#
CREATE TABLE s_site_plugin_s_attribute_type_map (
	sequence_number		INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	site_type		VARCHAR(10) NOT NULL,
	s_item_type_group	VARCHAR(10) NOT NULL DEFAULT '*',
	s_item_type		VARCHAR(10) NOT NULL DEFAULT '*',
	variable		VARCHAR(20) NOT NULL,
	s_attribute_type	VARCHAR(10) NOT NULL,
	PRIMARY KEY ( sequence_number ),
	UNIQUE KEY ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type )
) TYPE=MyISAM COMMENT='Site Plugin Attribute Type Map';

#
# Site Plugin Attribute Type Lookup Map
#
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
# Create Import Cache table
#
CREATE TABLE import_cache (
  sequence_number	INTEGER(10) unsigned NOT NULL auto_increment,
  user_id			VARCHAR(20) NOT NULL,
  plugin_name		VARCHAR(100),
  content_length	INTEGER(10) unsigned NOT NULL,
  PRIMARY KEY ( sequence_number )
) TYPE=MyISAM COMMENT='Import Cache table';

#
# Create File Cache table
#
CREATE TABLE file_cache (
  sequence_number	INTEGER(10) unsigned NOT NULL auto_increment,
  cache_type		VARCHAR(10) NOT NULL DEFAULT 'HTTP',
  cache_date		DATETIME NOT NULL,
  expire_date		DATETIME NOT NULL,
  url				MEDIUMTEXT NOT NULL,
  # if url was redirected, this stores the redirected URL
  location			MEDIUMTEXT,
  content_type		VARCHAR(100) NOT NULL,
  # we do not want to calculate the length at 
  # runtime of the content column
  content_length	INTEGER(10) unsigned NOT NULL,
  # if GZIP compression used, this will record what level.  A
  # zero (0) level means no compression.  This allows the
  # config variable to be changed without effecting existing
  # cache entries.
  gzcompress_level	tinyint(1) NOT NULL DEFAULT 0,
  content			BLOB,
  PRIMARY KEY ( sequence_number )
) TYPE=MyISAM COMMENT='File Cache table';

#
# Patch the display type for author to take advantage of Amazon.com multi-author parsing
# support.
#
UPDATE s_attribute_type
SET display_type = 'list(plain, ",", list-link)'
WHERE s_attribute_type = 'AUTHOR';

UPDATE s_attribute_type
SET site_type = NULL
WHERE s_attribute_type = 'IMDBRATING';

#
# If you want to see anamorphic in item_display even if the checkbox is un-checked
# in the item_input script, specify a non-empty unchecked value.  For instance checkbox(Y,N,)
#
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type) VALUES ( 'ANAMORPHIC','Anamorphic indicator', 'Anamorphic', 'checkbox(Y,,)', '%value%', NULL, NULL);
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'DVD', 'ANAMORPHIC', '115', NULL, NULL);

#
# All site plugins should be explicitly linked, this functionality is being removed, as its 
# cumbersome at best and unused in most cases.
#
ALTER TABLE s_item_type DROP default_site_type;
