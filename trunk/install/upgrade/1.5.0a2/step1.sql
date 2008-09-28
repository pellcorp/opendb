#
# allow disabling captcha
#
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.signup', 'disable_captcha', 2, 'Disable Captcha', '', 'boolean');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.signup', 'disable_captcha', 'FALSE');