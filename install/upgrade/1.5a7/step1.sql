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

# Feature request #1757580 - Owner Can CheckOut Own Items
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'owner_self_checkout', 10, 'Owner Self Checkout', 'Allows an owner to checkout their own items', 'boolean');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'owner_self_checkout', 'FALSE');

# patch to install new gamerating E10 
DELETE FROM s_attribute_type_lookup WHERE s_attribute_type = 'GAMERATING';
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 0, 'EC', 'Early Childhood', 'game/game_ec.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 1, 'K-A', 'Kids to Adults', 'game/game_ka.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 2, 'E', 'Everyone', 'game/game_e.gif', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 3, 'E10', 'Everyone 10+', 'game/game_e10.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 4, 'T', 'Teen', 'game/game_t.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 5, 'M', 'Mature', 'game/game_m.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 6, 'AO', 'Adults Only', 'game/game_ao.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 7, 'RP', 'Rating Pending', 'game/game_rp.gif', 'N' );
