#
# Manually restore AUDIO_LANG
#

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

UPDATE s_site_plugin_s_attribute_type_map SET s_attribute_type = 'AUDIO_LANG' 
WHERE s_attribute_type = 'DVD_AUDIO'; 

DELETE FROM s_site_plugin_s_attribute_type_lookup_map WHERE s_attribute_type = 'DVD_AUDIO';

