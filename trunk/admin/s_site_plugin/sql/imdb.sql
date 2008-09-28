#########################################################
# OpenDb 1.0 Imdb.com (imdb) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'imdb', 'imdb', 'Imdb.com', 'imdb.gif', 'The source of movie information, but with nothing specific to distribution formats such as DVD and VHS.', 'http://www.imdb.com', 0, 'http://www.imdb.com/title/tt{imdb_id}' );

#
# Site Plugin Configuration
#.

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'imdb', 'age_certification_codes', '0', '', 'Australia' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'imdb', 'age_certification_codes', '1', '', 'USA' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'imdb', 'title_search_match_types', '0', '', 'exact' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'imdb', 'title_search_match_types', '1', '', 'partial' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'imdb', 'title', 1, '', 'Title Search', 'text', '', '{ifdef(alt_title,\'{alt_title}\',\'{title}\')}{ifdef(year,\' ({year})\')}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'imdb', 'imdb_id', 2, '', 'IMDB ID', 'text', '', '{imdb_id}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'imdb', 'VIDEO', '*', 1, 'More Info', 'http://www.imdb.com/Title?{imdb_id}', '{ifdef(year, http://www.imdb.com/Tsearch?title={title}&type=substring&year={year})}' );
INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'imdb', 'VIDEO', '*', 2, 'Trailer', 'http://www.imdb.com/Trailers?{imdb_id}', '' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'imdb', '*', '*', 'genre', 'MOVIEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'imdb', '*', '*', 'plot', 'MOVIE_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'imdb', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'imdb', '*', '*', 'title', 'S_TITLE', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'imdb', 'AGE_RATING', 'NC-17', 'MA' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'imdb', 'AGE_RATING', 'PG-13', 'PG' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'imdb', 'AGE_RATING', 'R', 'MA' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'imdb', 'AGE_RATING', 'X', 'R' ); 

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'IMDB_ID', 'ImDb ID', 'Imdb ID', 'hidden', 'hidden', '', 'imdb' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'IMDB_ID',  0, '', 'N' );
