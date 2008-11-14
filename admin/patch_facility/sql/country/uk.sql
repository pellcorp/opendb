##############################################################
# Formatted for United Kingdom - with BBFC rating system
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

UPDATE `s_attribute_type_lookup`
SET checked_ind = NULL
WHERE s_attribute_type = 'GAMEREGION';

UPDATE `s_attribute_type_lookup`
SET checked_ind = 'Y'
WHERE s_attribute_type = 'GAMEREGION'
AND value = 'E_UK';

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
# Use British flag for all English values
# 
UPDATE s_attribute_type_lookup SET img = 'english.gif'
WHERE s_attribute_type = 'AUDIO_LANG' AND value IN ('ENGLISH', 'ENGLISH_SR', 'ENGLISH_2.0');

UPDATE s_attribute_type_lookup SET img = 'english.gif'
WHERE s_attribute_type = 'SUBTITLES' AND value  = 'ENGLISH';

#
# Customize ratings for country
#
DELETE FROM s_attribute_type_lookup WHERE s_attribute_type = 'AGE_RATING';
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'E', 'Exempt from classification', 'E.gif', NULL, '0');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'U', 'Universal (suitable for all)', 'U.gif', NULL, '1');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'PG', 'Parental Guidence', 'PG.gif', NULL, '2');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', '12', 'Passed only for persons 12 and over', '12.gif', NULL, '3');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', '15', 'Passed only for persons 15 and over', '15.gif', 'Y', '4');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', '18', 'Passed only for persons 18 and over', '18.gif', NULL, '5');
