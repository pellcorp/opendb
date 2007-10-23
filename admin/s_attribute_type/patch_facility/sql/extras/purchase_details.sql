#
# Extra variables for DVD
#
# purchase price/date/store
# release date
# suggested retail price

DELETE FROM s_attribute_type WHERE s_attribute_type IN ('PUR_DATE','PUR_PRICE', 'PUR_STORE', 'RET_PRICE');

INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type) VALUES ( 'PUR_DATE', 'Purchase Date', 'Purchase Date', 'datetime(DD/MM/YYYY)', 'datetime(DD/MM/YYYY)', NULL, NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type) VALUES ( 'PUR_PRICE', 'Purchase Price', 'Purchase Price', 'filtered(6, 6, 0-9.)', 'display(%value%)', NULL, NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type) VALUES ( 'PUR_STORE', 'Purchase Store', 'Purchase Store', 'text(50)', 'display(%value%)', NULL, NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type) VALUES ( 'RET_PRICE', 'Retail Price', 'Retail Price', 'filtered(6, 6, 0-9.)', 'display(%value%)', NULL, NULL);

DELETE FROM s_item_attribute_type WHERE s_attribute_type IN ('PUR_DATE','PUR_PRICE', 'PUR_STORE', 'RET_PRICE') AND s_item_type IN ('DVD');

INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'DVD', 'PUR_STORE', '121', NULL, 'N');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'DVD', 'PUR_DATE', '122', NULL, 'N');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'DVD', 'PUR_PRICE', '123', NULL, 'N');
INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, compulsory_ind) VALUES ( 'DVD', 'RET_PRICE', '124', NULL, 'N');
