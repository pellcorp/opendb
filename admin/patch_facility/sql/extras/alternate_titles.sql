#
# Alternate titles for all Types
#
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'ALT_TITLE', 'Alternate Title', 'Alternate Title', 'text', '50', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);

INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'DVD', 'ALT_TITLE', '5', NULL, 'N');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'VCD', 'ALT_TITLE', '5', NULL, 'N');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'VHS', 'ALT_TITLE', '5', NULL, 'N');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'LD', 'ALT_TITLE', '5', NULL, 'N');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'CD', 'ALT_TITLE', '5', NULL, 'N');
# DIVX was not supported before Alternate titles were introduced, which is why the order_no is 10.
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'DIVX', 'ALT_TITLE', '10', NULL, 'N');

