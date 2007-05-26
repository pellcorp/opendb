#
# Configure an alternate ID for items.
#
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'ALT_ID', 'Alternate Item ID', 'Item ID', 'text', '10', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);

INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind) VALUES ( 'CD', 'ALT_ID', '1', NULL, 'Y');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind) VALUES ( 'LD', 'ALT_ID', '1', NULL, 'Y');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind) VALUES ( 'VCD', 'ALT_ID', '1', NULL, 'Y');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind) VALUES ( 'DVD', 'ALT_ID', '1', NULL, 'Y');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind) VALUES ( 'DIVX', 'ALT_ID', '1', NULL, 'Y');
