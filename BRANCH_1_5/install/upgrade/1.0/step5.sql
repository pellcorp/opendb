#
# cleanup email address system data
#

DELETE FROM user_address WHERE s_address_type = 'EMAIL';
DELETE FROM user_address_attribute WHERE s_attribute_type = 'EMAIL_ADDR';
DELETE FROM s_addr_attribute_type_rltshp WHERE s_attribute_type = 'EMAIL_ADDR';
DELETE FROM s_address_type WHERE s_address_type = 'EMAIL';
DELETE FROM s_attribute_type WHERE s_attribute_type = 'EMAIL_ADDR';

# remove configuration stuff
DELETE FROM s_config_group_item_var WHERE group_id = 'email' AND id = 'user_address_attribute';
DELETE FROM s_config_group_item WHERE group_id = 'email' AND id = 'user_address_attribute';
