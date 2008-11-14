#########################################################
# OpenDb 1.0.5 eMusic.com (emusic) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'emusic', 'emusic', 'eMusic.com', 'emusic.jpg', 'eMusic.com: MP3 Music and Audiobook Downloads', 'http://emusic.com', 50, 'http://www.emusic.com/album/{emusic_lnk}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'emusic', 'emusic_lnk', 1, 'Partial URL of the album on eMusic', 'eMusic Link', 'text', '', '{emusic_lnk}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'emusic', 'title', 2, 'Album Title', 'Album Title', 'text', '', '{title}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'emusic', '*', '*', 1, 'More info', 'http://www.emusic.com/album/{emusic_lnk}', '' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'emusic', 'AUDIO', '*', 'musicgenre', 'MUSICGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'emusic', '*', '*', 'run_time', 'CDTIME', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'emusic', '*', '*', 'title', 'S_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'emusic', '*', '*', 'tracks', 'CDTRACK', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'emusic', 'MUSICGENRE', 'Alternative/Punk', 'punk' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'emusic', 'MUSICGENRE', 'Country/Folk', 'folk' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'emusic', 'MUSICGENRE', 'Hip-Hop/R&B', 'hiphop' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'emusic', 'MUSICGENRE', 'Rock/Pop', 'pop' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'emusic', 'MUSICGENRE', 'Soundtracks/Other', 'soundtrack' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'emusic', 'MUSICGENRE', 'Spiritual', 'other' ); 

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'EMUSIC_LNK', 'eMusic.com Link', 'eMusic.com Link', 'hidden', 'hidden', '', 'emusic' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'MP3', 'EMUSIC_LNK',  0, '', 'N' );
