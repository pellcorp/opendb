#########################################################
# OpenDb 1.0 DVDFr.com (dvdfr) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'dvdfr', 'dvdfr', 'DVDFr.com', 'dvdfr.gif', 'A good source for region 2 DVD (french).', 'http://www.dvdfr.com', 25, 'http://www.dvdfr.com/dvd/dvd.php?id={dvdfr_id}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'dvdfr', 'title', 1, '', 'Title Search', 'text', '', '{ifdef(alt_title, \'{alt_title} / \')}{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'dvdfr', 'dvdfr_id', 2, '', 'DVDFr ID', 'text', '', '{dvdfrid}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'dvdfr', '*', '*', 1, 'More Info', 'http://www.dvdfr.com/dvd/dvd.php?id={dvdfr_id}', 'http://www.dvdfr.com/search/search.php?multiname={title}&x=0&y=0' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', '*', '*', 'alt_title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', 'VIDEO', '*', 'blurb', 'MOVIE_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', 'VIDEO', '*', 'genre', 'MOVIEGENRE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', '*', '*', 'title', 'S_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', '*', '*', 'dvdfr_id', 'DVDFRID', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', '*', 'DVD', 'audio_lang', 'DVD_AUDIO', 'N' );


#
# Site Plugin System Attribute Type Lookup Map
#


#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Afghan', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Allemand', 'GERMAN' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Anglais', 'ENGLISH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Anglais (australien)', 'ENGLISH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Arabe', 'ARABIC' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Bengali', '' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Breton', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Cantonais', 'CANTONESE' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Chinois', 'CHINESE' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Croate', 'CROATIAN' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Danois', 'DANISH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Espagnol', 'SPANISH' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Farsi', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Finlandais', 'FINNISH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Flamand', 'DUTCH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Français', 'FRENCH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Grec', 'GREEK' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Hindi', 'HINDOE' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Hongrois', 'HUNGARIAN' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'International', '' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Inuit', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Islandais', 'ISLANDIC' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Italien', 'ITALIAN' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Japonais', 'JAPANESE' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Kurde', '' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Malien', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Mandarin', 'MANDARIN' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Musique', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Néerlandais', 'DUTCH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Norvégien', 'NORWEGIAN' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Polonais', 'POLISH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Portugais', 'PORTUGUESE' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Roumain', '' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Russe', '' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Serbe', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Suédois', 'SWEDISH' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Taiwanais', '' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Tibetain', '' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Tunisien', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Turc', 'TURKISH' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Vietnamien', '' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Tchéque', 'CZECH' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Hébreu', 'HEBREW' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Coréen', 'KOREAN' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'Créole', '' );

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'U', 'G' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'PG', 'PG' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', '-12', 'M' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', '-13', 'M' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', '-16', 'MA' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', '-18', 'R' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'X', 'X' );

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'Espagnol 4.0', 'DOLBY2.0' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'Portugais 4.0', 'DOLBY2.0' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'Anglais 5.1', 'DOLBY5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'Français 5.1', 'DOLBY5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'Allemand 5.1', 'DOLBY5.1' );

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'DVDFRID', 'Dvdfr ID', 'Dvdfr ID', 'hidden', 'hidden', '', 'dvdfr' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'DVDFRID',  0, '', 'N' );
