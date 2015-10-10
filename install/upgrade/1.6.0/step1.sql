#
# Restore AUDIO_LANG
#

INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH', 'English', 'english.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_2.0', 'English(2.0)', 'english.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_5.0', 'English(5.0)', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_5.1', 'English(5.1)', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_6.1', 'English(6.1)', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_6.1_DTS_ES', 'English (6.1 DTS ES)', 'dts.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_6.1_EX', 'English(6.1 EX)', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_DTS', 'English(DTS)', 'dts.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_SR', 'English(Surround)', 'english.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH_THX', 'English(THX)', 'thx.gif', 'N' );

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_2.0', order_no = 70
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'DOLBY2.0';

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_5.0', order_no = 70
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'DOLBY';

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_5.1', order_no = 70 
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'DOLBY5.1';

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_6.1', order_no = 70 
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'DOLBY6.1';

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_6.1_DTS_ES', order_no = 70
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'DTS6.1';

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_6.1_EX', order_no = 70 
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'DOLBY6.1';

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_DTS', order_no = 70 
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'DTS';

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_SR', order_no = 70
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'SURROUND';

UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', lookup_attribute_val = 'ENGLISH_THX', order_no = 70
WHERE s_attribute_type = 'DVD_AUDIO' AND lookup_attribute_val = 'THX';

# replace any remaining with just the existing value
UPDATE item_attribute SET s_attribute_type = 'AUDIO_LANG', order_no = 70
WHERE s_attribute_type = 'DVD_AUDIO';

DELETE FROM s_item_attribute_type WHERE s_attribute_type = 'DVD_AUDIO';

DELETE FROM s_site_plugin_s_attribute_type_map WHERE s_attribute_type = 'DVD_AUDIO'; 
DELETE FROM s_site_plugin_s_attribute_type_lookup_map WHERE s_attribute_type = 'DVD_AUDIO';

DELETE FROM s_attribute_type_lookup WHERE s_attribute_type = 'DVD_AUDIO';
DELETE FROM s_attribute_type WHERE s_attribute_type = 'DVD_AUDIO';
