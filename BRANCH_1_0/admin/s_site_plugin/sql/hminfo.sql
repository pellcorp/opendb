#########################################################
# OpenDb 1.0 HometheaterInfo.com (hminfo) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'hminfo', 'hminfo', 'HometheaterInfo.com', 'hminfo.gif', 'HometheaterInfo.com Region 1 DVD Listing', 'http://www.hometheaterinfo.com', 20, '' );

#
# Site Plugin Configuration
#.

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'hminfo', 'datetime_mask', '0', 'Format DVD and Original Release Dates', 'DD/MM/YYYY' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'hminfo', 'upc_id', 1, '', 'UPC Number', 'text', '', '{upc_id}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'hminfo', 'title', 2, '', 'Title Search', 'text', '', '{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'hminfo', 'hmi_id', 3, '', 'HometheaterInfo ID', 'text', '', '{hmi_id}' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'hminfo', '*', '*', 'genre', 'AUDIO_LANG', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'hminfo', '*', '*', 'genre', 'MOVIEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'hminfo', '*', '*', 'genre', 'SUBTITLES', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'hminfo', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'hminfo', '*', '*', 'title', 'S_TITLE', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AGE_RATING', 'NC-17', 'MA' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AGE_RATING', 'PG-13', 'PG' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AGE_RATING', 'R', 'MA' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AGE_RATING', 'UR', 'X' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AGE_RATING', 'X', 'R' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', '1.0', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', '2.0', 'ENGLISH_2.0' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', '4.0', 'ENGLISH_4.0' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', '5.0', 'ENGLISH_5.0' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', '5.1', 'ENGLISH_5.1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', '6.1 ES', 'ENGLISH_6.1_DTS_ES' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', '6.1 EX', 'ENGLISH_6.1_EX' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', 'DTS', 'ENGLISH_DTS' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'AUDIO_LANG', 'SUR', 'ENGLISH_SR' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'MOVIEGENRE', 'Anime', 'Animation' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'MOVIEGENRE', 'Family', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'MOVIEGENRE', 'Foreign', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'MOVIEGENRE', 'LateNight', 'Adult' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'MOVIEGENRE', 'Opera', 'Music' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'MOVIEGENRE', 'SciFi', 'ScienceFiction' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'MOVIEGENRE', 'SpecialInterest', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'hminfo', 'MOVIEGENRE', 'TVClassics', 'Other' ); 

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'HMI_ID', 'HomeTheaterInfo ID', 'HMI ID', 'hidden', 'hidden', '', 'hminfo' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'HMI_ID',  0, '', 'N' );
