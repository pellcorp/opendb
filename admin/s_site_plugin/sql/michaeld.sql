#########################################################
# OpenDb 1.0 MichaelD (michaeld) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'michaeld', 'michaeld', 'MichaelD', 'michaeld.png', 'MichaelDVD is the best source of region 2 and 4 DVD information.', 'http://www.michaeldvd.com.au', 25, 'http://www.michaeldvd.com.au/Discs/Disc.asp?ID={michaeldid}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'michaeld', 'title', 1, '', 'Title Search', 'text', '', '{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'michaeld', 'michaeldid', 2, '', 'MichaelDVD ID', 'text', '', '{michaeldid}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'michaeld', '*', '*', 1, 'More Info', 'http://www.michaeldvd.com.au/Discs/Disc.asp?ID={michaeldid}', 'http://www.michaeldvd.com.au/Search/TitleSearch.asp?title={title}' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'michaeld', 'VIDEO', '*', 'blurb', 'MOVIE_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'michaeld', '*', '*', 'audio_lang', 'AUDIO_LANG', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'michaeld', '*', '*', 'audio_xtra', 'AUDIO_XTRA', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'michaeld', '*', 'DVD', 'dvd_audio', 'DVD_AUDIO', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'michaeld', 'VIDEO', '*', 'genre', 'MOVIEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'michaeld', '*', '*', 'subtitles', 'SUBTITLES', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'michaeld', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'michaeld', '*', '*', 'title', 'S_TITLE', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'anime', 'Animation' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'black comedy', 'Comedy' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'bond', 'Action' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'bond', 'Adventure' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'claymation', 'Animation' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'comedy western', 'Comedy' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'comedy western', 'Western' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'demo/test', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'disaster', 'Adventure' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'karaoke', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'martial arts', 'Action' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'opera', 'Music' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'romantic comedy', 'Comedy' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'romantic comedy', 'Romance' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'science fiction', 'ScienceFiction' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'Sci-Fi', 'ScienceFiction' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'MOVIEGENRE', 'star trek', 'ScienceFiction' );

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'DVD_AUDIO', 'ENGLISH5.1', 'DOLBY5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'michaeld', 'DVD_AUDIO', 'ENGLISH_DTS', 'DTS' );

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'MICHAELDID', 'MichaelD ID', 'MichaelD ID', 'hidden', 'hidden', '', 'michaeld' );
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type, listing_link_ind, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, lookup_attribute_ind, multi_attribute_ind, file_attribute_ind) VALUES ('MIKER4R1', 'R4 vs. R1 comparsion', 'Best region', 'textarea', 'display', '', 'michaeld', 'N', '50', '3', NULL, NULL, NULL, '%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N');

#
# Site Plugin Item Attribute Type Relationship(s)
#
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'MICHAELDID',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BD', 'MICHAELDID',  0, '', 'N' );
