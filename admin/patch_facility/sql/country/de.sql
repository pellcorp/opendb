##############################################################
# Formatted for Deutschland - with USK rating system
##############################################################

#
# Customize default region for country
#
UPDATE s_attribute_type_lookup
SET checked_ind = NULL
WHERE s_attribute_type = 'DVD_REGION';

UPDATE s_attribute_type_lookup
SET checked_ind = 'Y'
WHERE s_attribute_type = 'DVD_REGION' 
AND value = '2';

UPDATE s_attribute_type_lookup
SET checked_ind = NULL
WHERE s_attribute_type = 'GAMEREGION';

UPDATE s_attribute_type_lookup
SET checked_ind = 'Y'
WHERE s_attribute_type = 'GAMEREGION'
AND value = 'EU_DE';

#
# Customize default video format for country
#
UPDATE s_attribute_type_lookup
SET checked_ind = NULL
WHERE s_attribute_type = 'VID_FORMAT';

UPDATE s_attribute_type_lookup
SET checked_ind = 'Y'
WHERE s_attribute_type = 'VID_FORMAT' 
AND value = 'PAL';

#
# Add more German audio formats
#
DELETE FROM s_attribute_type_lookup WHERE s_attribute_type = 'AUDIO_LANG' AND value LIKE 'GERMAN_%';

INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_6.1_DTS_ES', 'German (6.1 DTS ES)', 'dts.gif', NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_6.1_EX', 'German(6.1 EX)', 'dolby.gif', NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_6.1', 'German(6.1)', 'dolby.gif', NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_5.1', 'German(5.1)', 'dolby.gif', NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_DTS', 'German(DTS)', 'dts.gif', NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_THX', 'German(THX)', 'thx.gif', NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_5.0', 'German(5.0)', 'dolby.gif', NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_SR', 'German(Surround)', 'german.gif', NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN_2.0', 'German(2.0)', 'german.gif', NULL);


#
# Customize ratings for country
#
DELETE FROM s_attribute_type_lookup WHERE s_attribute_type = 'AGE_RATING' AND value LIKE 'USK%';
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'USK0', 'Ohne Altersbeschr�nkung', 'rating/USK0.gif', NULL, '1');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'USK6', 'Ab 6 Jahren', 'rating/USK6.gif', NULL, '2');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'USK12', 'Ab 12 Jahren', 'rating/USK12.gif', NULL, '3');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'USK16', 'Ab 16 Jahren', 'rating/USK16.gif', 'Y', '4');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'USK18', 'Nicht geeignet unter 18 Jahren', 'rating/USK18.gif', NULL, '5');
DELETE FROM s_attribute_type_lookup WHERE s_attribute_type = 'GAMERATING' AND value LIKE 'USK%';
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'GAMERATING', 'USK0', 'Ohne Altersbeschr�nkung', 'rating/USK0.gif', NULL, '1');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'GAMERATING', 'USK6', 'Ab 6 Jahren', 'rating/USK6.gif', NULL, '2');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'GAMERATING', 'USK12', 'Ab 12 Jahren', 'rating/USK12.gif', NULL, '3');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'GAMERATING', 'USK16', 'Ab 16 Jahren', 'rating/USK16.gif', 'Y', '4');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'GAMERATING', 'USK18', 'Nicht geeignet unter 18 Jahren', 'rating/USK18.gif', NULL, '5');
