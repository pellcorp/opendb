#########################################################
# OpenDb 1.0 Amazon.com (amazon) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'amazon', 'amazon', 'Amazon.com', 'amazon.gif', 'A good source of CD, DVD (Region 1), VHS, Books, Games, etc.', 'http://www.amazon.com', 25, 'http://www.amazon.com/exec/obidos/ASIN/{amazonasin}' );

#
# Site Plugin Configuration
#.

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'BOOK', '', 'books' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'CD', '', 'music' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'DIVX', '', 'dvd' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'DVD', '', 'dvd' );
# the dvd page has more info we can parse for blu-ray!
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'BD', '', 'dvd' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'GAME', '', 'videogames' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'LD', '', 'dvd' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'MP3', '', 'music' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'VCD', '', 'dvd' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'VHS', '', 'vhs' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'amazon', 'title', 1, '', 'Title/UPC/ISBN Search', 'scan-upc', '', '{ifdef(upc_id,{upc_id},{title})}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'amazon', 'amazonasin', 2, '', 'ASIN Number', 'text', '', '{ifdef(amazonasin,{amazonasin},{if(s_item_type==BOOK,{isbn},\'\')})}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'amazon', '*', '*', 1, 'More Info', 'http://www.amazon.com/exec/obidos/ASIN/{amazonasin}', 'http://www.amazon.com/exec/obidos/external-search?tag=&index={config_var_value(item_type_to_index_map, {s_item_type})}&keyword={title}' );
INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'amazon', '*', 'GAME', 2, 'Screenshots', 'http://www.amazon.com/exec/obidos/tg/stores/detail/-/videogames/{amazonasin}/pictures#more-pictures', '' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', 'AUDIO', '*', 'blurb', 'COMMENTS', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', 'BOOK', 'blurb', 'COMMENTS', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', 'VIDEO', '*', 'blurb', 'MOVIE_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', '*', 'audio_lang', 'AUDIO_LANG', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', '*', 'audio_xtra', 'AUDIO_XTRA', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', 'DVD', 'dvd_audio', 'DVD_AUDIO', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', 'BOOK', 'genre', 'BOOKGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', 'GAME', 'genre', 'GAMEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', 'VIDEO', '*', 'genre', 'MOVIEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', 'AUDIO', '*', 'genre', 'MUSICGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', '*', 'subtitles', 'SUBTITLES', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', '*', 'listprice', 'COVERPRICE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', 'VIDEO', '*', 'listprice', 'RET_PRICE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazon', '*', '*', 'title', 'S_TITLE', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'AGE_RATING', 'Unrated', 'NR' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMERATING', 'adults_only', 'AO' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMERATING', 'early_childhood', 'EC' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMERATING', 'everyone', 'E' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMERATING', 'mature', 'M' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMERATING', 'rating_pending', 'RP' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMERATING', 'teen', 'T' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'Game Boy', 'GAMEBOY' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'Game Boy Advance', 'GAMEBOYADVANCE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'Game Boy Color', 'GAMEBOY' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'GameCube', 'GAMECUBE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'Mac OS', 'MACCDROM' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'Nintendo 64', 'N64' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'PlayStation', 'PS1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'PlayStation2', 'PS2' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'Sega Dreamcast', 'DREAMCAST' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'Windows', 'PCCDROM' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'GAMESYSTEM', 'Xbox', 'XBOX' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 2.0', 'DOLBY2.0' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 5.0', 'DOLBY' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 6.1 EX', 'DOLBY6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 6.1 DTS ES', 'DTS6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 6.1', 'DTS6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English DTS', 'DTS' );

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'AMAZONASIN', 'Amazon Standard Item Number', 'Amazon ASIN', 'hidden', 'hidden', '', 'amazon' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BOOK', 'AMAZONASIN',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'CD', 'AMAZONASIN',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'AMAZONASIN',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'GAME', 'AMAZONASIN',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BD', 'AMAZONASIN',  0, '', 'N' );

