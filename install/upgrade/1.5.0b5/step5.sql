#
# The new and improved step2.sql!
# Tremble in fear before it's reduced operations!
#

#
# Update NO_PLAYERS to be input type number
#

UPDATE s_attribute_type SET input_type = 'number', input_type_arg1 = '3' WHERE s_attribute_type = 'NO_PLAYERS';

#
# Rearrange the order numbers for GAMEREGION and GAMEPBDATE to allow space for other attributes
#

UPDATE s_item_attribute_type SET order_no = '45' WHERE s_attribute_type = 'GAMEREGION';
UPDATE s_item_attribute_type SET order_no = '47' WHERE s_attribute_type = 'GAMEPBDATE';
UPDATE item_attribute SET order_no = '45' WHERE s_attribute_type = 'GAMEREGION' AND order_no = '43';
UPDATE item_attribute SET order_no = '47' WHERE s_attribute_type = 'GAMEPBDATE' AND order_no = '45';

#
# Change NO_DISCS to NO_MEDIA for format neutrality
#

UPDATE item_attribute SET s_attribute_type = 'NO_MEDIA' WHERE s_attribute_type = 'NO_DISCS';
UPDATE s_item_attribute_type SET s_attribute_type = 'NO_MEDIA' WHERE s_attribute_type = 'NO_DISCS';

#
# Update existing lookups that used the sound system icons to the new .png file names
# They turned out to be smaller than as GIFs
#

UPDATE s_attribute_type_lookup SET img = 'mono.png' WHERE img = 'mono.gif';
UPDATE s_attribute_type_lookup SET img = 'stereo.png' WHERE img = 'stereo.gif';
UPDATE s_attribute_type_lookup SET img = 'surround3.png' WHERE img = 'surround3.gif';
UPDATE s_attribute_type_lookup SET img = 'surround4.png' WHERE img = 'surround4.gif';
UPDATE s_attribute_type_lookup SET img = 'surround51.png' WHERE img = 'surround51.gif';
UPDATE s_attribute_type_lookup SET img = 'surround61.png' WHERE img = 'surround61.gif';
UPDATE s_attribute_type_lookup SET img = 'surround71.png' WHERE img = 'surround71.gif';
UPDATE s_attribute_type_lookup SET img = 'surround81.png' WHERE img = 'surround81.gif';
UPDATE s_attribute_type_lookup SET img = 'stereo.png' WHERE s_attribute_type = 'BD_AUDIO' AND value = 'PCM2';
UPDATE s_attribute_type_lookup SET img = 'surround51.png' WHERE s_attribute_type = 'BD_AUDIO' AND value = 'PCM5';
UPDATE s_attribute_type_lookup SET img = 'surround61.png' WHERE s_attribute_type = 'BD_AUDIO' AND value = 'PCM6';
UPDATE s_attribute_type_lookup SET img = 'surround71.png' WHERE s_attribute_type = 'BD_AUDIO' AND value = 'PCM7';

#
# Change BD_RES to TV_RES
#

UPDATE s_attribute_type SET s_attribute_type = 'TV_RES' WHERE s_attribute_type = 'BD_RES';
UPDATE s_attribute_type SET description = 'Supported TV Resolutions', prompt = 'TV Resolution(s)', listing_link_ind = 'Y' WHERE s_attribute_type = 'TV_RES';
UPDATE item_attribute SET s_attribute_type = 'TV_RES' WHERE s_attribute_type = 'BD_RES';
UPDATE s_item_attribute_type SET s_attribute_type = 'TV_RES' WHERE s_attribute_type = 'BD_RES';
UPDATE s_attribute_type_lookup SET s_attribute_type = 'TV_RES' WHERE s_attribute_type = 'BD_RES';

#
# Update AUDIO_LANG's prompt to reflect that it is AUDIO language
#

UPDATE s_attribute_type SET prompt = 'Audio Language(s)' WHERE s_attribute_type = 'AUDIO_LANG';

#
# Set NULL values to the SUBTITLES order, as anything other than alphabetical is bloody annoying
#

UPDATE s_attribute_type_lookup SET order_no = NULL WHERE s_attribute_type = 'SUBTITLES';

#
# Update ANAMORPHIC and AUDIO_XTRA to link
#

UPDATE s_attribute_type SET display_type = 'display', display_type_arg1 = '%display%' WHERE s_attribute_type = 'ANAMORPHIC';
UPDATE s_attribute_type SET listing_link_ind = 'Y' WHERE s_attribute_type = 'AUDIO_XTRA';
UPDATE s_attribute_type SET listing_link_ind = 'Y' WHERE s_attribute_type = 'ANAMORPHIC';

#
# Update ARTIST to have a more accurate description and prompt
#

UPDATE s_attribute_type SET description = 'Music Artist or Band', prompt = 'Artist(s)/Band(s)' WHERE s_attribute_type = 'ARTIST';

# MovieMeter support
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '3', 'moviemeter.nl');