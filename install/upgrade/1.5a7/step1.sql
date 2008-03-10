# michaeld plugin fix

UPDATE s_site_plugin_s_attribute_type_map 
SET variable = 'audio_lang'
WHERE s_attribute_type = 'AUDIO_LANG'
AND site_type = 'michaeld';

UPDATE s_site_plugin_s_attribute_type_map 
SET variable = 'subtitles'
WHERE s_attribute_type = 'SUBTITLES' 
AND site_type = 'michaeld';

DELETE FROM s_language_var WHERE varname IN (
	'borrower_usertype_description',
	'administrator_usertype_description',
	'guest_usertype_description',
	'normal_usertype_description',
	's_status_type_create_access_disabled_for_usertype',
	's_status_type_display_access_disabled_for_usertype',
	's_status_type_status_comments_not_supported',
	'choose_user_type',
	'normal',
	'admin',
	'administrator',
	'guest');

