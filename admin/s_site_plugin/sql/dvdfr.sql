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

#
# Site Plugin System Attribute Type Lookup Map
#


#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Afghan' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'GERMAN', 'Allemand' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'ENGLISH', 'Anglais' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'ENGLISH', 'Anglais (australien)' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'ARABIC', 'Arabe' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Bengali' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Breton' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'CANTONESE', 'Cantonais' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'CHINESE', 'Chinois' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'CROATIAN', 'Croate' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'DANISH', 'Danois' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'SPANISH', 'Espagnol' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Farsi' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'FINNISH', 'Finlandais' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'DUTCH', 'Flamand' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'FRENCH', 'Français' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'GREEK', 'Grec' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'HINDOE', 'Hindi' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'HUNGARIAN', 'Hongrois' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'International' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Inuit' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'ISLANDIC', 'Islandais' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'ITALIAN', 'Italien' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'JAPANESE', 'Japonais' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Kurde' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Malien' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'MANDARIN', 'Mandarin' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Musique' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'DUTCH', 'Néerlandais' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'NORWEGIAN', 'Norvégien' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'POLISH', 'Polonais' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'PORTUGUESE', 'Portugais' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Roumain' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Russe' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Serbe' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'SWEDISH', 'Suédois' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Taiwanais' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Tibetain' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Tunisien' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'TURKISH', 'Turc' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Vietnamien' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'CZECH', 'Tch�que' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'HEBREW', 'H�breu' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', 'KOREAN', 'Cor�en' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AUDIO_LANG', '', 'Cr�ole' );

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'G', 'U' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'PG', 'PG' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'M', '-12' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'M', '-13' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'MA', '-16' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'R', '-18' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'AGE_RATING', 'X', 'X' );

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'DOLBY2.0', 'Espagnol 4.0' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'DOLBY2.0', 'Portugais 4.0' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'DOLBY5.1', 'Anglais 5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'DOLBY5.1', 'Français 5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdfr', 'DVD_AUDIO', 'DOLBY5.1', 'Allemand 5.1' );

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
