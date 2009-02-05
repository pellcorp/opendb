#
# Move Image Type to common location for Stats and Login captcha
#

UPDATE s_config_group_item SET group_id = 'site', order_no = 14, subtype = 'auto,png,jpg,gif', description = 'Stats and Captcha Image Type' WHERE group_id = 'stats' AND id = 'image_type';
UPDATE s_config_group_item_var SET group_id = 'site' WHERE group_id = 'stats' AND id = 'image_type';
