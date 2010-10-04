#########################################################
# OpenDb 1.5.0 RC2 Moviemeter.nl (moviemeter) Site Plugin
# Plugin: Bas ter Vrugt <bastervrugt@gmail.com>
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'moviemeter', 'moviemeter', 'moviemeter.nl', 'moviemeter.png', 'Nederlandse site met film informatie.', 'http://www.moviemeter.nl', 0, 'http://www.moviemeter.nl/film/{moviemeter_id}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'moviemeter', 'title', 1, '', 'Zoek op titel', 'text', '', '{ifdef(alt_title, \'{alt_title} / \')}{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'moviemeter', 'moviemeter_id', 2, '', 'Moviemeter ID', 'text', '', '{moviemeter_id}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'moviemeter', 'VIDEO', '*', 1, 'Meer informatie', 'http://www.moviemeter.nl/film/{moviemeter_id}', 'http://www.moviemeter.nl/film/search/{title}' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'moviemeter', '*', '*', 'genre', 'MOVIEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'moviemeter', '*', '*', 'plot', 'MOVIE_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'moviemeter', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'moviemeter', '*', '*', 'title', 'S_TITLE', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Actie', 'Action' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Animatie', 'Animation' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Avontuur', 'Adventure' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Documentaire', 'Documentary' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Erotiek', 'Adult' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Familie', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Film-Noir', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Komedie', 'Comedy' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Misdaad', 'Action' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Muziek', 'Music' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Oorlog', 'War' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Roadmovie', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Romantiek', 'Romance' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'moviemeter', 'MOVIEGENRE', 'Science-Fiction', 'ScienceFiction' ); 


####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'moviemeter_ID', 'Moviemeter ID', 'Moviemeter ID', 'hidden', 'hidden', '', 'moviemeter' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'moviemeter_ID',  0, '', 'N' );

