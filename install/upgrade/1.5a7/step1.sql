# michaeld plugin fix

UPDATE s_site_plugin_s_attribute_type_map 
SET variable = 'audio_lang'
WHERE s_attribute_type = 'AUDIO_LANG'
AND site_type = 'michaeld';

UPDATE s_site_plugin_s_attribute_type_map 
SET variable = 'subtitles'
WHERE s_attribute_type = 'SUBTITLES' 
AND site_type = 'michaeld';
