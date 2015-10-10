#
# AUDIO LANG / AUDIO XTRA restructure
# 

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'AUDIO_XTRA', 'Additional Audio Tracks', 'Additional Audio', 'checkbox_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'N', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DVD_AUDIO', 'DVD Audio Format', 'Audio Format(s)', 'checkbox_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'N', 'N', 'Y', 'N', NULL, NULL);

INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_XTRA', 0, 'SOUNDTRACK', 'Soundtrack Only', 'director.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_XTRA', 1, 'ACT_COMMENT', 'Actor\'s Commentary', 'director.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_XTRA', 2, 'DIR_COMMENT', 'Director\'s Commentary', 'director.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_XTRA', 3, 'PROD_COMMENT', 'Producer\'s Commentary', 'director.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_XTRA', 4, 'OTHER_COMMENT', 'Other Commentary', 'director.gif', 'N' );

INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 1, 'MONO', 'Mono - 1.0', 'mono.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 2, 'STEREO', 'Stereo - 2.0', 'stereo.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 3, 'SURROUND', 'Surround - 5.1', 'surround51.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 4, 'DOLBY', 'Dolby', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 5, 'DOLBY2.0', 'Dolby 2.0', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 6, 'DOLBY5.1', 'Dolby 5.1', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 7, 'DOLBY6.1', 'Dolby 6.1', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 8, 'DOLBY7.1', 'Dolby 7.1', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 9, 'DTS', 'DTS', 'dts.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 10, 'DTS2.0', 'DTS 2.0', 'dts.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 11, 'DTS5.1', 'DTS 5.1', 'dts.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 12, 'DTS6.1', 'DTS 6.1', 'dts.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 13, 'DTS7.1', 'DTS 7.1', 'dts.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DVD_AUDIO', 14, 'THX', 'THX Certified', 'thx.gif', 'N' );

# add audio_xtra and dvd_audio to DVD
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DVD', 'AUDIO_XTRA', 75, NULL, 'N', 'N', 'Y', 'N' );
#INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DVD', 'DVD_AUDIO', 76, NULL, 'N', 'N', 'Y', 'N' );

# add audio_xtra to LD
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'LD', 'AUDIO_XTRA', 75, NULL, 'N', 'N', 'Y', 'N' );

# move audio_xtra
DELETE FROM s_attribute_type_lookup
WHERE s_attribute_type = 'AUDIO_LANG' AND value IN (
	'ACT_COMMENT', 
	'DIR_COMMENT', 
	'OTHER_COMMENT',
	'PROD_COMMENT');

# Move existing AUDIO_LANG to AUDIO_XTRA
UPDATE item_attribute SET s_attribute_type = 'AUDIO_XTRA', order_no = 75
WHERE s_attribute_type = 'AUDIO_LANG' AND
lookup_attribute_val IN ('ACT_COMMENT', 'DIR_COMMENT', 'PROD_COMMENT', 'OTHER_COMMENT');

# new dvdempire mappings
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'DVD_AUDIO', 'DTS Surround', 'DTS' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'DVD_AUDIO', 'ENGLISH: DTS 5.1', 'DTS' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'DVD_AUDIO', 'ENGLISH: DD-EX 5.1', 'DOLBY5.1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'DVD_AUDIO', 'ENGLISH: Dolby Digital 5.1', 'DOLBY5.1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'DVD_AUDIO', 'ENGLISH: Dolby Digital Surround', 'SURROUND' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'DVD_AUDIO', 'ENGLISH: DTS ES 6.1', 'DTS6.1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'DVD_AUDIO', 'FRENCH: Dolby Digital 5.1', 'DOLBY5.1' );

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'DTS Surround', 'ENGLISH_DTS' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'ENGLISH: DTS 5.1', 'ENGLISH_5.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'ENGLISH: DD-EX 5.1', 'ENGLISH_5.1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'ENGLISH: Dolby Digital 5.1', 'ENGLISH_5.1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'ENGLISH: Dolby Digital Surround', 'ENGLISH_SR' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'ENGLISH: DTS ES 6.1', 'ENGLISH_6.1_DTS_ES' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'dvdempire', 'AUDIO_LANG', 'FRENCH: Dolby Digital 5.1', 'ENGLISH_5.1' );

# new amazon mappings
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 2.0', 'DOLBY2.0' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 5.0', 'DOLBY' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 6.1 EX', 'DOLBY6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 6.1 DTS ES', 'DTS6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English 6.1', 'DTS6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'DVD_AUDIO', 'English DTS', 'DTS' );

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'AUDIO_LANG', 'English 2.0', 'ENGLISH_2.0' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'AUDIO_LANG', 'English 5.0', 'ENGLISH_5.0' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'AUDIO_LANG', 'English 6.1 EX', 'ENGLISH_6.1_EX' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'AUDIO_LANG', 'English 6.1 DTS ES', 'ENGLISH_6.1_DTS_ES' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'AUDIO_LANG', 'English 6.1', 'ENGLISH_6.1' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazon', 'AUDIO_LANG', 'English DTS', 'ENGLISH_DTS' );

