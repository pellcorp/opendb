#########################################################
# OpenDb 1.5a8 DVDEmpire.com (dvdempire) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'dvdempire', 'dvdempire', 'DVDEmpire.com', 'dvdempire.gif', 'The place for Region 1 DVDs', 'http://www.dvdempire.com', 25, 'http://www.dvdempire.com/Exec/v4_item.asp?item_id={dvdempr_id}' );

#
# Site Plugin Configuration
#

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'dvdempire', 'item_type_url_config', 'DIVX', '', 'site_id=4' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'dvdempire', 'item_type_url_config', 'DVD', '', 'site_id=4' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'dvdempire', 'item_type_url_config', 'LD', '', 'site_id=4' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'dvdempire', 'item_type_url_config', 'VCD', '', 'site_id=4' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'dvdempire', 'item_type_url_config', 'BD', '', 'site_id=69' );
# HD-DVD
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'dvdempire', 'item_type_url_config', 'HDDVD', '', 'site_id=68' );
# VOD (Video on Demand)
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'dvdempire', 'item_type_url_config', 'VOD', '', 'site_id=55' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'dvdempire', 'title', 1, '', 'Title / UPC Search', 'text', '', '{ifdef(upc_id,{upc_id},{title})}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'dvdempire', 'dvdempr_id', 2, '', 'DVD Empire ID', 'text', '', '{dvdempr_id}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'dvdempire', '*', '*', 1, 'More Info', 'http://www.dvdempire.com/Exec/v4_item.asp?item_id={dvdempr_id}', 'http://www.dvdempire.com/exec/v5_search_item.asp?string={title}&{config_var_value(item_type_url_config, {s_item_type})}&display_pic=1' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', '*', 'blurb', 'MOVIE_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', '*', 'genre', 'MOVIEGENRE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', '*', 'audio_lang', 'AUDIO_LANG', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', '*', 'audio_xtra', 'AUDIO_XTRA', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', 'DVD', 'audio_format', 'AUDIO_LANG', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', 'BD', 'audio_format', 'BD_AUDIO', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', '*', 'listprice', 'COVERPRICE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '', 'CD', 'listprice', 'RET_PRICE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', 'VIDEO', '*', 'listprice', 'RET_PRICE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdempire', '*', '*', 'title', 'S_TITLE', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

# DVD audio maps
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AGE_RATING', 'NC-17', 'MA' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AGE_RATING', 'PG-13', 'PG' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AGE_RATING', 'R', 'MA' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AGE_RATING', 'X', 'R' ); 

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'DTS Surround', 'ENGLISH_DTS' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'DTS 5.1', 'ENGLISH_DTS' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'DD-EX 5.1', 'ENGLISH_5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'Dolby Digital 5.1', 'ENGLISH_5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'Dolby Digital Surround', 'SURROUND' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'DTS ES 6.1', 'DTS6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'Dolby Digital 5.1', 'ENGLISH_5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'Dolby Digital Stereo', 'ENGLISH_2.0' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'Dolby Digital Mono', 'ENGLISH' );

# BD audio maps
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby', 'DOLBY' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby 2.0', 'DOLBY2.0' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby 5.1', 'DOLBY5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby Digital Plus 2.0', 'DOLBYPLUS2' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby Digital Plus 5.1', 'DOLBYPLUS5' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby Digital Plus 6.1', 'DOLBYPLUS6' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby Digital Plus 7.1', 'DOLBYPLUS7' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby TrueHD 2.0', 'DOLBYTH2' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby TrueHD 5.1', 'DOLBYTH5' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby TrueHD 6.1', 'DOLBYTH6' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'Dolby TrueHD 7.1', 'DOLBYTH7' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS', 'DTS' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS 2.0', 'DTS2.0' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS 5.1', 'DTS5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS 6.1', 'DTS6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS 7.1', 'DTS7.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS-HD High Resolution 2.0', 'DTSHDHR2' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS-HD High Resolution 5.1', 'DTSHDHR5' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS-HD High Resolution 6.1', 'DTSHDHR6' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS-HD High Resolution 7.1', 'DTSHDHR7' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS-HD Master Audio 2.0', 'DTSHDMA2' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS-HD Master Audio 5.1', 'DTSHDMA5' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS-HD Master Audio 6.1', 'DTSHDMA6' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'DTS-HD Master Audio 7.1', 'DTSHDMA7' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'PCM 2.0', 'PCM2' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'PCM 5.1', 'PCM5' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'PCM 6.1', 'PCM6' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'BD_AUDIO', 'PCM 7.1', 'PCM7' );

# movie genre maps
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Action / Adventure', 'Action' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Action / Adventure', 'Adventure' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Anime', 'Animation' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Cine Latino', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Classics', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Comedy', 'Comedy' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Cult Classics', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Documentary', 'Documentary' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Drama', 'Drama' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Family Viewing', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Gay/Lesbian Cinema', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Horror', 'Horror' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Interactive', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'International / Avant Garde', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Mature / Adult', 'Adult' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Popular Music / Performing Arts', 'Musical' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Sci-Fi / Fantasy', 'Fantasy' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Sci-Fi / Fantasy', 'ScienceFiction' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Special Interest', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Sports', 'Other' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Suspense / Mystery', 'Mystery' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Suspense / Mystery', 'Suspense' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'MOVIEGENRE', 'Television', 'Other' ); 

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'DVDEMPR_ID', 'DVD Empire ID', 'DVD Empire ID', 'hidden', 'hidden', '', 'dvdempire' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'DVDEMPR_ID',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BD', 'DVDEMPR_ID',  0, '', 'N' );

