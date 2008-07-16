##############################################################
# Formatted for USA - with MPAA rating system
##############################################################

#
# Customize default regions for country
#
UPDATE s_attribute_type_lookup
SET checked_ind = NULL
WHERE s_attribute_type = 'DVD_REGION';

UPDATE s_attribute_type_lookup
SET checked_ind = 'Y'
WHERE s_attribute_type = 'DVD_REGION' 
AND value = '1';

UPDATE `s_attribute_type_lookup`
SET checked_ind = NULL
WHERE s_attribute_type = 'GAMEREGION';

UPDATE `s_attribute_type_lookup`
SET checked_ind = 'Y'
WHERE s_attribute_type = 'GAMEREGION'
AND value = 'US';


#
# Customize default video format for country
#
UPDATE s_attribute_type_lookup
SET checked_ind = NULL
WHERE s_attribute_type = 'VID_FORMAT';

UPDATE s_attribute_type_lookup
SET checked_ind = 'Y'
WHERE s_attribute_type = 'VID_FORMAT' 
AND value = 'NTSC';

#
# Use US flag for all English values
# 
UPDATE s_attribute_type_lookup SET img = 'usa.gif'
WHERE s_attribute_type = 'AUDIO_LANG' AND value IN ('ENGLISH', 'ENGLISH_SR', 'ENGLISH_2.0');

UPDATE s_attribute_type_lookup SET img = 'usa.gif'
WHERE s_attribute_type = 'SUBTITLES' AND value  = 'ENGLISH';

#
# Customize ratings for country
#
DELETE FROM s_attribute_type_lookup WHERE s_attribute_type = 'AGE_RATING';
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'G', 'General Audiences', 'G.gif', NULL, '0');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'PG', 'Parental Guidance Suggested (6+)', 'PG.gif', NULL, '1');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'PG-13', 'Parental Guidance Strongly Cautioned (13+)', 'PG-13.gif', 'Y', '2');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'R', 'Restricted Audience (17+)', 'R.gif', NULL, '3');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'NC-17', 'No Admittance 17 and under', 'NC-17.gif', NULL, '4');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'X', 'Explicit Sexual Content', 'X.gif', NULL, '5');
INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) VALUES ( 'AGE_RATING', 'NR', 'Unrated Content', 'NR.gif', NULL, '6');
