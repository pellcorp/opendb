# -----------------------------------------------------------------------------------------------
# Default Installation System Data
# -----------------------------------------------------------------------------------------------
#
# System Item Type Group
#
INSERT INTO s_item_type_group(s_item_type_group, description) VALUES('AUDIO', 'Audio Item Types');
INSERT INTO s_item_type_group(s_item_type_group, description) VALUES('VIDEO', 'Video Item Types');

#
# System Attribute Types
#

# System attributes
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('S_STATCMNT', 'System Status Comment', 'Status Comment', 'textarea', '50', '5', NULL, NULL, NULL, 'hidden', NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'STATUSCMNT', NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('S_STATUS', 'System Status Type', 'Status Type', '', NULL, NULL, NULL, NULL, NULL, 'hidden', NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'STATUSTYPE', NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('S_TITLE', 'Item Title', 'Title', 'text', '50', '255', NULL, NULL, NULL, 'hidden', NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'TITLE', NULL);

#
# This attribute is reserved for use in item_review.  Please do not use it for your own s_item_attribute_type structures.
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('S_RATING', 'Item Rating', 'Rating', 'review_options', '%display%', 'VERTICAL', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'Y', 'N', 'RATING', NULL);

#
# This attribute is reserved for use in Borrow Duration functionality.  Please do not use it for your own s_item_attribute_type structures.
# If you want you could run this update to change the input type for S_DURATION a numeric input field instead:
# UPDATE s_attribute_type SET input_type = 'number(3, %field% <em>days</em>)' WHERE s_attribute_type = 'S_DURATION'
#
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('S_DURATION', 'Borrow Duration', 'Borrow Duration', 'single_select', '%display%', NULL, NULL, NULL, NULL, 'display', '%display%', NULL, NULL, NULL, NULL, 'N', 'N', 'Y', 'N', 'DURATION', NULL);

#
# Display item.id
#
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('S_ITEM_ID', 'OpenDb Item ID', 'ID#', 'hidden', NULL, NULL, NULL, NULL, NULL, 'hidden', NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'ITEM_ID', NULL);

#
# Address type attributes
#
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('ADDR_LINE', 'Address Line', 'Address', 'text', '50', '255', NULL, NULL, NULL, 'display', '%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'ADDRESS', NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('CITY', 'City', 'City', 'text', '50', '100', NULL, NULL, NULL, 'display', '%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'ADDRESS', NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('COUNTRY', 'Country', 'Country', 'single_select', '%display%', NULL, NULL, NULL, NULL, 'display', '%display%', NULL, NULL, NULL, NULL, 'N', 'N', 'Y', 'N', 'ADDRESS', NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('PHONE_NO', 'Phone Number', 'Phone', 'filtered', '20', '50', '0-9 \\-+', NULL, NULL, 'display', '%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'ADDRESS', NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('POSTCODE', 'Post code', 'Postcode', 'number', '10', NULL, NULL, NULL, NULL, 'display', '%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'ADDRESS', NULL);
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type) VALUES ('STATE', 'State', 'State', 'text', '20', '100', NULL, NULL, NULL, 'display', '%display%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'ADDRESS', NULL);

#
# System Address Type
# 
INSERT INTO s_address_type ( s_address_type, description, display_order, closed_ind )
VALUES ( 'SNAIL', 'Postal Address', '1', 'N' );

#
# System Address Type relationship
# 
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'ADDR_LINE', '1', 'Address Line 1', 'N' );
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'ADDR_LINE', '2', 'Address Line 2', 'N' );
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'CITY', '3', NULL, 'N' );
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'STATE', '4', NULL, 'N' );
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'POSTCODE', '5', NULL, 'N' );
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'COUNTRY', '6', NULL, 'N' );
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'PHONE_NO', '10', 'Home Phone', 'N' );
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'PHONE_NO', '11', 'Work Phone', 'N' );
INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt, closed_ind)
VALUES ( 'SNAIL', 'PHONE_NO', '12', 'Mobile Phone', 'N' );

#
# This attribute is reserved for use in item_review.  Please do not use it for your own s_item_attribute_type structures.
#
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'S_RATING', NULL, '1', 'Disgraceful!', NULL, NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'S_RATING', NULL, '2', 'Terrible!', NULL, NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'S_RATING', NULL, '3', 'Decent!', NULL, NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'S_RATING', NULL, '4', 'Great!', NULL, NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'S_RATING', NULL, '5', 'Sensational!', NULL, NULL);

#
# Duration support
#
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ('S_DURATION', '0', '', 'Undefined', NULL, 'Y');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ('S_DURATION', '1', '1', 'One Day', NULL, NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ('S_DURATION', '2', '3', 'Three Days', NULL, NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ('S_DURATION', '3', '7', 'One Week', NULL, NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ('S_DURATION', '4', '14', 'Two Weeks', NULL, NULL);
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ('S_DURATION', '5', '28', 'One Month', NULL, NULL);

#
# Country lookups
#
# cleanup
DELETE FROM s_attribute_type_lookup WHERE s_attribute_type IN('COUNTRY');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, '', '', '', 'Y');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AF', 'AFGHANISTAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AL', 'ALBANIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'DZ', 'ALGERIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AS', 'AMERICAN SAMOA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AD', 'ANDORRA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AO', 'ANGOLA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AI', 'ANGUILLA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AQ', 'ANTARCTICA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AG', 'ANTIGUA AND BARBUDA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AZ', 'AZERBAIJAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AR', 'ARGENTINA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AM', 'ARMENIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AW', 'ARUBA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AU', 'AUSTRALIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AT', 'AUSTRIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BS', 'BAHAMAS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BH', 'BAHRAIN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BD', 'BANGLADESH', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BB', 'BARBADOS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BY', 'BELARUS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BE', 'BELGIUM', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BZ', 'BELIZE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BJ', 'BENIN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BM', 'BERMUDA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BT', 'BHUTAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BO', 'BOLIVIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BA', 'BOSNIA AND HERZEGOWINA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BW', 'BOTSWANA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BV', 'BOUVET ISLAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BR', 'BRAZIL', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'IO', 'BRITISH INDIAN OCEAN TERRITORY', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BN', 'BRUNEI DARUSSALAM', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BG', 'BULGARIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BF', 'BURKINA FASO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'BI', 'BURUNDI', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CA', 'CANADA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KH', 'CAMBODIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CM', 'CAMEROON', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CV', 'CAPE VERDE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CF', 'CENTRAL AFRICAN REPUBLIC', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TD', 'CHAD', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CL', 'CHILE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CN', 'CHINA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CX', 'CHRISTMAS ISLAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CC', 'COCOS (KEELING) ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CO', 'COLOMBIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KM', 'COMOROS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CG', 'CONGO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CD', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CK', 'COOK ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CR', 'COSTA RICA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CI', 'COTE D\'IVOIRE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'HR', 'CROATIA (localname:Hrvatska)', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CU', 'CUBA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CY', 'CYPRUS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CZ', 'CZECH REPUBLIC', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'DE', 'GERMANY', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'DK', 'DENMARK', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'DJ', 'DJIBOUTI', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'DM', 'DOMINICA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'DO', 'DOMINICAN REPUBLIC', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TP', 'EAST TIMOR', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'EC', 'ECUADOR', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'EG', 'EGYPT', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SV', 'ELSALVADOR', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GQ', 'EQUATORIAL GUINEA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ER', 'ERITREA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'EE', 'ESTONIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ET', 'ETHIOPIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'FK', 'FALKLAND ISLANDS (MALVINAS)', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'FO', 'FAROE ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'FJ', 'FIJI', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'FI', 'FINLAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'FR', 'FRANCE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'FX', 'FRANCE, METROPOLITAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GF', 'FRENCH GUIANA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PF', 'FRENCH POLYNESIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TF', 'FRENCH SOUTHERN TERRITORIES', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GM', 'GAMBIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GA', 'GABON', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GE', 'GEORGIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GH', 'GHANA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GI', 'GIBRALTAR', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GR', 'GREECE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GL', 'GREENLAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GD', 'GRENADA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GP', 'GUADELOUPE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GU', 'GUAM', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GT', 'GUATEMALA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GN', 'GUINEA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GW', 'GUINEA-BISSAU', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GY', 'GUYANA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'HT', 'HAITI', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'HM', 'HEARD AND MCDONALD ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'HN', 'HONDURAS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'HK', 'HONGKONG', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'HU', 'HUNGARY', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'IS', 'ICELAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ID', 'INDONESIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'IL', 'ISRAEL', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'IN', 'INDIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'IR', 'IRAN (ISLAMIC REPUBLIC OF)', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'IQ', 'IRAQ', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'IE', 'IRELAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'IT', 'ITALY', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'JM', 'JAMAICA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'JP', 'JAPAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'JO', 'JORDAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KZ', 'KAZAKHSTAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KY', 'CAYMAN ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KE', 'KENYA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KI', 'KIRIBATI', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KP', 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KR', 'KOREA, REPUBLIC OF', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KW', 'KUWAIT', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KG', 'KYRGYZSTAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LV', 'LATVIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LB', 'LEBANON', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LS', 'LESOTHO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LR', 'LIBERIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LY', 'LIBYAN ARAB JAMAHIRIYA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LI', 'LIECHTENSTEIN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LT', 'LITHUANIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LA', 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LU', 'LUXEMBOURG', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MK', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MG', 'MADAGASCAR', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MW', 'MALAWI', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MY', 'MALAYSIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MV', 'MALDIVES', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ML', 'MALI', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MH', 'MARSHALL ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MQ', 'MARTINIQUE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MD', 'MOLDOVA, REPUBLIC OF', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MP', 'NORTHERN MARIANA ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MA', 'MOROCCO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MR', 'MAURITANIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MU', 'MAURITIUS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'YT', 'MAYOTTE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MX', 'MEXICO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'FM', 'MICRONESIA, FEDERATED STATES OF', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MN', 'MONGOLIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MO', 'MACAU', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MS', 'MONTSERRAT', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MT', 'MALTA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MC', 'MONACO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MZ', 'MOZAMBIQUE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'MM', 'MYANMAR', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NR', 'NAURU', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NE', 'NIGER', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NP', 'NEPAL', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NL', 'NETHERLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AN', 'NETHERLANDS ANTILLES', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NZ', 'NEW ZEALAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NF', 'NORFOLK ISLAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NI', 'NICARAGUA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NG', 'NIGERIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NU', 'NIUE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NC', 'NEW CALEDONIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NO', 'NORWAY', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'NA', 'NAMIBIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'OM', 'OMAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PK', 'PAKISTAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PW', 'PALAU', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PS', 'PALESTINIAN TERRITORY, OCCUPIED', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PG', 'PAPUA NEW GUINEA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PY', 'PARAGUAY', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PA', 'PANAMA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PH', 'PHILIPPINES', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PN', 'PITCAIRN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PL', 'POLAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PT', 'PORTUGAL', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PE', 'PERU', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PR', 'PUERTO RICO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'QA', 'QATAR', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'RE', 'REUNION', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'RO', 'ROMANIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'RU', 'RUSSIAN FEDERATION', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'RW', 'RWANDA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'KN', 'SAINT KITTS AND NEVIS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LC', 'SAINT LUCIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'VC', 'SAINT VINCENT AND THE GRENADINES', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'WS', 'SAMOA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SM', 'SANMARINO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ST', 'SAO TOME AND PRINCIPE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SK', 'SLOVAKIA (Slovak Republic)', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SA', 'SAUDI ARABIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SN', 'SENEGAL', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SL', 'SIERRA LEONE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SG', 'SINGAPORE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SI', 'SLOVENIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SB', 'SOLOMON ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SO', 'SOMALIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SC', 'SEYCHELLES', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ZA', 'SOUTH AFRICA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GS', 'SOUTH GEORGIA AND THE SOUTHS AND WICH ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SD', 'SUDAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ES', 'SPAIN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'LK', 'SRI LANKA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SH', 'ST.HELENA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'PM', 'ST.PIERRE AND MIQUELON', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SR', 'SURINAME', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SJ', 'SVALBARD AND JANMAYEN ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SZ', 'SWAZILAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SE', 'SWEDEN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'CH', 'SWITZERLAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'SY', 'SYRIAN ARAB REPUBLIC', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TW', 'TAIWAN, PROVINCE OF CHINA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TJ', 'TAJIKISTAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TZ', 'TANZANIA, UNITED REPUBLIC OF', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TN', 'TUNISIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TH', 'THAILAND', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TG', 'TOGO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TK', 'TOKELAU', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TO', 'TONGA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TT', 'TRINIDAD AND TOBAGO', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TR', 'TURKEY', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TM', 'TURKMENISTAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TC', 'TURKS AND CAICOS ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'TV', 'TUVALU', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'UG', 'UGANDA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'UA', 'UKRAINE', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'AE', 'UNITED ARAB EMIRATES', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'GB', 'UNITED KINGDOM', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'US', 'UNITED STATES', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'UM', 'UNITED STATES MINOR OUTLYING ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'UY', 'URUGUAY', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'UZ', 'UZBEKISTAN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'VU', 'VANUATU', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'VE', 'VENEZUELA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'VN', 'VIETNAM', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'VA', 'HOLY SEE (VATICAN CITY STATE)', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'VG', 'VIRGIN ISLANDS (BRITISH)', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'VI', 'VIRGIN ISLANDS (U.S.)', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'WF', 'WALLIS AND FUTUNA ISLANDS', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'EH', 'WESTERN SAHARA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'YE', 'YEMEN', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'YU', 'YUGOSLAVIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ZM', 'ZAMBIA', '', '');
INSERT INTO s_attribute_type_lookup (s_attribute_type, order_no, value, display, img, checked_ind) VALUES ( 'COUNTRY', NULL, 'ZW', 'ZIMBABWE', '', '');

#
# Configuration Groups
#
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site', 1, 'Opendb Site', 'Site configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site.public_access', 1, 'Public Access', 'Public Access configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site.url', 2, 'Opendb Site URL', 'Override OpenDb Site URL configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'site.gzip_compression', 3, 'GZIP Compression', 'Configure gzip compression' );

INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'logging', 2, 'Logging', 'Logging configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http', 3, 'Http', 'HTTP configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http.cache', 1, 'Http Cache', 'HTTP Cache configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http.item.cache', 1, 'Item File Cache', 'Item File Cache configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http.stream_external_images', 2, 'Stream External Images', 'Stream external image URLs via Snoopy. Very useful for working around IMDB cover image display restrictions');
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'http.proxy_server', 3, 'Proxy Server', 'Proxy Server configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'email', 4, 'Email', 'Email configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'email.smtp', 1, 'Smtp Configuration', 'SMTP Server configuration' );

INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'themes', 5, 'Themes', 'Themes Configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'widgets', 6, 'Widgets', 'Widgets configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'login', 7, 'Login', 'Login / Logout configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'login.signup', 1, 'Signup', 'Signup configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'user_admin', 8, 'User Administration', 'User Administration configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'listings', 9, 'Item Listings', 'Item Listings configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'listings.filters', 1, 'Item Listing Filters', 'Item Listing filter configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'listings.borrow', 2, 'Item Listing Borrow', 'Item Listing Borrow configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'listings.multi_borrow', 3, 'Item Listing Multi Borrow', 'Item Listing Multi Borrow configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_display', 10, 'Item Display', 'Item Display configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_input', 11, 'Item Input', 'Item Input configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_input.site', 1, 'Site Plugins', 'Item Input Site Plugins configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'item_review', 12, 'Item Review', 'Item Review configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'borrow', 13, 'Item Borrow', 'Borrow Functionality configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'borrow.reminder', 1, 'Item Borrow Reminders', 'Reminders Job configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'borrow.checkout', 2, 'Item Borrow Checkouts / Checkins', 'Borrow check in / check out configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'search', 14, 'Item Search', 'Item Search configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'import', 15, 'Import', 'Import configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'stats', 16, 'Statistics', 'Statistics configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'feeds', 17, 'Feeds', 'Feeds configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'feeds.new_items', 1, 'New Items Feed', 'New Items Feed configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'feeds.announcements', 2, 'Announcements Feed', 'Announcements Feed configuration' );

INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'welcome', 18, 'Welcome', 'Login Welcome configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'welcome.last_items_list', 1, 'Last Items List', 'Last items listing configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'welcome.whats_new', 2, 'Whats New', 'Whats new summary configuration' );
INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'welcome.announcements', 3, 'Announcements', 'Announcements configuration' );

#
# Configuration Items
#
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'title', 2, 'Title', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'language', 5, 'Default Language', '', 'language');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'theme', 6, 'Default Theme', '', 'theme');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'idle_timeout', 7, 'Idle Timeout', 'In milliseconds', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'login_timeout', 8, 'Login Timeout', 'In milliseconds', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'security_hash', 9, 'Security Hash', 'Change for each OpenDb site for added security', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'file_upload_enable', 10, 'Enable File Uploads', 'Override PHP file upload configuration', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'register_globals_enabled', 11, 'Enable Register Globals', 'Override PHP register globals configuration', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'restrict_session_cookie_to_host_path', 12, 'Restrict Session to Virtual Location', 'Restrict session to this OpenDb instance. [EXPERIMENTAL]', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site', 'upgrade_check', 13, 'Upgrade Check', 'Whenever index.php is accessed a version check will be made between the OpenDb database and opendb installation to make sure they match.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('site', 'image_type', 14, 'Image Type', 'Stats and Captcha Image Type', 'value_select', 'auto,png,jpg,gif');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.gzip_compression', 'enable', 1, 'Enable GZIP Compression', 'If enabled all html output from opendb will be gzip compressed', 'boolean'); 
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('site.gzip_compression', 'disabled', 2, 'Exclude Specific Pages', 'This is a workaround where low memory limits are enabled.', 'array', 'text'); 

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.url', 'host', 1, 'Host', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.url', 'port', 2, 'Port', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.url', 'path', 3, 'Path', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('site.url', 'protocol', 4, 'Protocol', '', 'value_select', ',http,https');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('site.public_access', 'enable', 1, 'Enable', 'Enable guest public access', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('themes', 'charset', 1, 'Content Type Charset', 'This setting will force a meta http-equiv Content Type header to be included in the source of each page.', 'value_select', ',utf-8,iso-8859-1');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('widgets', 'enable_javascript_validation', 1, 'Enable Javascript Validation', 'Enforce javascript data validations in addition to backend validations.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('widgets', 'legal_html_tags', 3, 'Legal HTML Tags', '', 'array', 'text');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('logging', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('logging', 'file', 2, 'Log File', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('logging', 'backup_ext_date_format', 3, 'Date Format', 'Logfile Backup Extension Date Format', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.stream_external_images', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) 	VALUES ('http.stream_external_images', 'domain_list', 2, 'Domain List', 'Restrict streaming to specified domain names.', 'array', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http', 'datetime_mask', 3, 'Cache Datetime Mask', 'Http / Item Cache Admin Tool Datetime Mask', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.cache', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.cache', 'lifetime', 3, 'Refresh Timeout', 'In seconds', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.item.cache', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.item.cache', 'lifetime', 3, 'Refresh Timeout', 'In seconds', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'host', 2, 'Host', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'port', 3, 'Port', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'userid', 4, 'Username', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('http.proxy_server', 'password', 5, 'Password', '', 'password');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email', 'send_to_site_admin', 1, 'Send to Admin', 'Sending Email to admin is supported.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email', 'noreply_address', 2, 'No Reply Address', 'Configure no-reply address for events such as password resets', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email', 'windows_smtp_server', 3, 'Windows SMTP Server', 'If smtp server either configured via php mail or smtp mailer is running on windows (for example if its an MS Exchange Server) and mail is not getting through, it may help to check this.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('email', 'mailer', 4, 'Mailer', '', 'value_select', 'smtp,mail,none');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('email.smtp', 'secure', 5, 'Email SMTP Secure Connection', 'Enables secure connections with the SMTP server (ssl or tls).', 'value_select', 'none,ssl,tls');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email.smtp', 'host', 1, 'Email SMTP Host', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email.smtp', 'port', 2, 'Email SMTP Port', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email.smtp', 'username', 3, 'Email SMTP Username', '', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('email.smtp', 'password', 4, 'Email SMTP Password', '', 'password');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login', 'enable_new_pwd_gen', 1, 'Enable new Password request', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login', 'show_menu', 2, 'Show Menu', 'Should menu be displayed when logging in / out', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.signup', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('login.signup', 'disable_captcha', 2, 'Disable Captcha', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.whats_new', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.whats_new', 'exclude_current_user', 2, 'Exclude Current User', 'Exclude current users items', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.whats_new', 'restrict_last_login', 3, 'Restrict Last Login', 'Restrict whats new stats to items added since last login', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.whats_new', 'borrow_stats', 4, 'Show Borrow Stats', 'Show Items Returned, Reserved, etc', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.whats_new', 'review_stats', 5, 'Show Review Stats', 'Show number of reviews added.', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.last_items_list', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.last_items_list', 'exclude_current_user', 2, 'Exclude Current User', 'Exclude current users items', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.last_items_list', 'restrict_last_login', 3, 'Restrict Last Login', 'Restrict list to items added since last login', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.last_items_list', 'total_num_items', 4, 'Total Items to List', 'Number of items to list', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.last_items_list', 'datetime_mask', 5, 'Datetime Mask', '', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.announcements', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.announcements', 'display_count', 2, 'Display Count', 'Number of Announcements to list', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.announcements', 'datetime_mask', 3, 'Datetime Mask', '', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('import', 'row_import_default_initcap_checked', 1, 'Row Import Initcap Enabled', 'Initcap checked by default', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_themes_support', 1, 'User Themes Support', 'Users can change theme', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_language_support', 2, 'User Language Support', 'Users can change language', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'user_passwd_change_allowed', 6, 'Change Password support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin', 'datetime_mask', 7, 'Datetime Mask', '', 'datemask');

INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'user_admin.change_user', 1, 'Change User', 'Change User Configuration' );
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('user_admin.change_user', 'enable', 1, 'Enable', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('stats', 'chart_lib', 1, 'Chart Library', 'LibChart (V1.1 and 1.2) and Legacy are included, but JPGraph (V2.3) and PhpPlot (V5.0.4) will require installation.', 'value_select', 'libchart,jpgraph,phplot,legacy');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('stats', 'category_barchart', 2, 'Category Barchart', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'show_item_image', 1, 'Show Item Image', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('item_display', 'item_image_size', 'height', 2, 'Item Image Height', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('item_display', 'item_image_size', 'width', 3, 'Item Image Width', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'no_image', 4, 'No Image', 'Whether to display a \'missing\' image, if no image defined for item', 'text');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'review_datetime_mask', 5, 'Review Datetime Mask', '', 'datemask');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_display', 'export_link', 6, 'Export Plugin Link Type', 'If defined will provide a export link', 'export');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'item_instance_support', 1, 'Item Instance Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'new_instance_owner_only', 2, 'New Instance Owner Only', 'Whether item instances can be created across owners', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'related_item_support', 4, 'Related Item Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'clone_item_support', 5, 'Clone Item Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'auto_site_insert', 6, 'Auto Site Insert', 'Bypass new item edit screen', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'auto_site_update', 7, 'Auto Site Refresh', 'Bypass update item edit screen', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'duplicate_title_support', 8, 'Duplicate Title Support', 'Duplicate title with same type and owner allowed', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'confirm_duplicate_insert', 9, 'Confirm Duplicate Insert', 'Confrm insert of duplicate title with same type regardless of owner', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'confirm_duplicate_owner_insert', 10, 'Confirm Duplicate Owner Insert', 'Confrm insert of duplicate title with same type and owner', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'confirm_item_delete', 11, 'Confirm Item Delete', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input', 'allow_delete_with_closed_or_cancelled_borrow_records', 12, 'Allow Item Delete with inactive borrow records', ' exist', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('item_input', 'title_articles', 13, 'Title Articles', 'Format title, so that articles appear at the end of the title.', 'array', 'text');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_input.site', 'debug', 1, 'Debug Site Plugins', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('item_input.site', 'item_image_size', 'height', 2, 'Item Image Height', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('item_input.site', 'item_image_size', 'width', 3, 'Item Image Width', '', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'update_support', 2, 'Update Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'delete_support', 3, 'Delete Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'include_other_title_reviews', 4, 'Include Other Titles', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'other_title_reviews_restrict_to_item_type_group', 5, 'Restrict Other Title Item Type Group', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'comment_compulsory', 6, 'Review Comment Compulsory', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'rating_compulsory', 7, 'Review Rating Compulsory', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_item_image', 1, 'Show Item Images', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'allow_override_show_item_image', 2, 'Allow Override Show Item Images', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('listings', 'item_image_size', 'height', 3, 'Item Image Height', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, keyid, order_no, prompt, description, type ) VALUES ('listings', 'item_image_size', 'width', 4, 'Item Image Width', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'no_image', 5, 'No Image', 'Whether to display a \'missing\' image, if no image defined for an item', 'text');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('listings', 'items_per_page_options', 6, 'Items Per Page Options', 'List of options for the \'Items Per Page\' list.  A value of \'0\' can be used to specify an empty option', 'array', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'items_per_page', 7, 'Default Items Per Page', 'How many items will be shown per page', 'number');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_input_actions', 8, 'Show Item Input Actions', 'Show update, edit, delete actions', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_refresh_actions', 9, 'Show Item Refresh Action', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'print_listing_datetime_mask', 10, 'Print Listing Datetime Mask', '', 'datemask');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'show_exact_match_search_columns', 11, 'Include Exact Match Search Columns', 'Include a column for each exact match search column.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings', 'alphalist_new_search_context', 12, 'Alpha List New Search Context', 'AlphaList should start a new search instead of further refining current search.', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_item_type_group_lov', 2, 'Show Item Type Group LOV', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_item_type_lov', 3, 'Show Item Type LOV', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_owner_lov', 4, 'Show Owner LOV', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_s_status_type_lov', 5, 'Show Status Type LOV', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.filters', 'show_interest', 6, 'Show interest', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.borrow', 'enable', 1, 'Enable', 'Enable item level actions, Reserve, Add to Basket, etc', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.borrow', 'quick_checkout_action', 2, 'Quick Checkout Action', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.borrow', 'reserve_action', 3, 'Reserve Action', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.borrow', 'basket_action', 4, 'Reserve Basket Action', '', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.multi_borrow', 'enable', 1, 'Enable', 'Reserve checkboxes and actions should be enabled', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.multi_borrow', 'reserve_action', 2, 'Reserve Action', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.multi_borrow', 'basket_action', 3, 'Reserve Basket Action', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('listings.multi_borrow', 'basket_action_if_not_empty_only', 4, 'Reserve Basket If Not Empty', 'Provide a \'Add to Basket\' action only if the Reserve Basket is not empty.', 'boolean');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'enable', 1, 'Enable', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reserve_email_only', 2, 'Reserve Email Only', 'Reservation of item sends email only, no other borrow functionality is enabled.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reserve_basket', 3, 'Enable Reserve Basket', 'Enable/Disable Reserve Basket support.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'allow_multi_reserve', 4, 'Allow Multible Reserve', 'Can more than one user reserve the same item', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'allow_reserve_if_borrowed', 5, 'Allow Reservation if Borrowed', 'Allow reservation if item already borrowed', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'duration_support', 6, 'Duration Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'quick_checkout', 7, 'Quick Checkout Support', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'quick_checkout_use_existing_reservation', 8, 'Quick Checkout Use Existing Reservation', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'admin_quick_checkout_borrower_lov', 9, 'Admin Quick Checkout Borrower LOV', 'Display list of users instead of text field.', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'owner_self_checkout', 10, 'Owner Self Checkout', 'Allows an owner to checkout their own items', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'include_borrower_column', 11, 'Include Borrower Column', 'Show borrower of item in item display', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reserve_more_information', 12, 'Reserve More Information', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'cancel_more_information', 13, 'Cancel More Information', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'checkout_more_information', 14, 'Checkout More Information', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'checkin_more_information', 15, 'Check in More Information', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reminder_more_information', 16, 'Reminder Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'reserve_email_notification', 17, 'Reservation Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'cancel_email_notification', 18, 'Cancel Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'checkout_email_notification', 19, 'Checkout Reservation', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'quick_checkout_email_notification', 20, 'Quick Checkout Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'checkin_email_notification', 21, 'Check in Notification', '', 'boolean');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'date_mask', 22, 'Date Mask', 'Due date formatting', 'datemask');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow', 'datetime_mask', 23, 'Datetime Mask', '', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow.reminder', 'duration_range', 1, 'Duration Range', 'Reminder job duration range, as follows: +X = X days overdue; 0 = on day due; -X = X days before due date', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('borrow.checkout', 'alt_id_attribute_type', 1, 'Alt ID Attribute Type', 'The attribute type that will store the alternate ID used for auto checkin / checkout', 'instance_attribute_type');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('search', 'datetime_mask', 1, 'Datetime Mask', 'Search datetime mask', 'datemask');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('feeds.new_items', 'total_num_items', 1, 'Total Items to List', '', 'number');
INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('feeds.announcements', 'total_num_items', 1, 'Total Announcements to List', '', 'number');

#
# Configuration Item Values.
#

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'title', 'Open Media Collectors Database');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'idle_timeout', '3600');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'security_hash', '0eXf5yUKlaeDgREQ72091mvFX');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'language', 'english');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'theme', 'default');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'file_upload_enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'register_globals_enabled', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'restrict_session_cookie_to_host_path', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'upgrade_check', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site', 'image_type', 'auto');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site.public_access', 'enable', 'FALSE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('site.gzip_compression', 'enable', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('site.gzip_compression', 'disabled', '0', 'admin');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('site.gzip_compression', 'disabled', '1', 'import');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('site.gzip_compression', 'disabled', '2', 'export');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('site.gzip_compression', 'disabled', '3', 'item_input');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('widgets', 'enable_javascript_validation', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '0', 'p');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '1', 'u');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '2', 'em');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '3', 'br');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('widgets', 'legal_html_tags', '4', 'strong');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.signup', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.signup', 'disable_captcha', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('logging', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('logging', 'file', './log/usagelog.txt');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('logging', 'backup_ext_date_format', 'DDMONYYYY');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.stream_external_images', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '0', 'imdb.com');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '1', 'imdb.org');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '2', 'ia.media-imdb.com');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '3', 'moviemeter.nl');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('http.stream_external_images', 'domain_list', '4', 'mobygames.com');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http', 'datetime_mask', 'DD/MM/YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.cache', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.cache', 'lifetime', '604800');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.item.cache', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.item.cache', 'lifetime', '604800');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('http.proxy_server', 'enable', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email', 'mailer', 'mail');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email', 'send_to_site_admin', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email', 'noreply_address', 'noreply@iamvegan.net');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email.smtp', 'host', 'mail.domain.edu.au');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('email.smtp', 'username', 'jpell');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login', 'enable_new_pwd_gen', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login', 'show_menu', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.whats_new', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('login.whats_new', 'show_heading', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.whats_new', 'borrow_stats', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.whats_new', 'review_stats', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.whats_new', 'exclude_current_user', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.whats_new', 'restrict_last_login', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.last_items_list', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.last_items_list', 'show_heading', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.last_items_list', 'exclude_current_user', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.last_items_list', 'restrict_last_login', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.last_items_list', 'datetime_mask', 'DD/MM/YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.last_items_list', 'total_num_items', '18');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.last_items_list', 'show_item_image', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.announcements', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.announcements', 'datetime_mask', 'DD/MM/YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.announcements', 'display_count', '3');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('menu', 'other_items_listing', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('menu', 'all_items_listing', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('import', 'row_import_default_initcap_checked', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'datetime_mask', 'DDth Month YYYY HH24:MI');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_themes_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_passwd_change_allowed', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin', 'user_language_support', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('user_admin.change_user', 'enable', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('stats', 'chart_lib', 'libchart');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('stats', 'category_barchart', 'FALSE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'show_item_image', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'no_image', 'no-image.gif');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_display', 'item_image_size', 'height', '100');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'review_datetime_mask', 'Day, DDth Month YYYY HH24:MI');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_display', 'export_link', 'OpenDbExportPlugin');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'auto_site_insert', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'auto_site_update', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'related_item_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'clone_item_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'item_instance_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'new_instance_owner_only', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'confirm_item_delete', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'allow_delete_with_closed_or_cancelled_borrow_records', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'duplicate_title_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'confirm_duplicate_insert','FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_input', 'confirm_duplicate_owner_insert', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input', 'item_image_size', 'height', '50');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input', 'title_articles', '0', 'The');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input', 'title_articles', '1', 'A');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input', 'title_articles', '2', 'An');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('item_input.site', 'item_image_size', 'height', '50');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'update_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'delete_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'include_other_title_reviews', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'other_title_reviews_restrict_to_item_type_group', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'comment_compulsory', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'rating_compulsory', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '0', '0');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '1', '21');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '2', '31');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '3', '41');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'items_per_page_options', '4', '51');
INSERT INTO s_config_group_item_var ( group_id, id, keyid, value ) VALUES ('listings', 'item_image_size', 'width', '69');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_exact_match_search_columns', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'no_image', 'no-image.gif');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_item_image', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'allow_override_show_item_image', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'print_listing_datetime_mask', 'Day, DDth Month YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'items_per_page', '21');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_input_actions', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings', 'show_refresh_actions', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.borrow', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.borrow', 'quick_checkout_action', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.borrow', 'reserve_action', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.borrow', 'basket_action', 'FALSE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.multi_borrow', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.multi_borrow', 'reserve_action', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.multi_borrow', 'basket_action', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.multi_borrow', 'basket_action_if_not_empty_only', 'FALSE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_item_type_group_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_item_type_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_owner_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_s_status_type_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('listings.filters', 'show_interest', 'TRUE');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'enable', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reserve_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'cancel_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'checkout_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'checkin_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reminder_more_information', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reserve_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'cancel_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'checkout_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'quick_checkout_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'checkin_email_notification', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'quick_checkout', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'admin_quick_checkout_borrower_lov', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'owner_self_checkout', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reserve_email_only', 'FALSE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'allow_multi_reserve',  'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'allow_reserve_if_borrowed', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'quick_checkout_use_existing_reservation', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'include_borrower_column', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'reserve_basket', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'duration_support', 'TRUE');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'date_mask', 'DDth Month YYYY');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow', 'datetime_mask', 'DD/MM/YYYY HH:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow.reminder', 'duration_range', '-1');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('borrow.checkout', 'alt_id_attribute_type', 'S_ITEM_ID');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('search', 'datetime_mask', 'DD/MM/YYYY HH24:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('announcements', 'datetime_mask', 'DD/MM/YYYY HH:MI:SS');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('feeds.new_items', 'total_num_items', '18');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('feeds.announcements', 'total_num_items', '5');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('themes', 'charset', 'utf-8');

#
# Title Display Mask data
#
INSERT INTO s_title_display_mask(id, description)
VALUES('last_items_list', 'Last Items List');

INSERT INTO s_title_display_mask(id, description)
VALUES('item_display', 'Item Display');

INSERT INTO s_title_display_mask(id, description)
VALUES('item_listing', 'Item Listings');

INSERT INTO s_title_display_mask(id, description)
VALUES('item_borrow', 'Item Borrow');

INSERT INTO s_title_display_mask(id, description)
VALUES('feeds', 'RSS Feeds');

#
# Title Display Mask Group Items - Item Display
#
INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_display', '*', '*', '"{title}"{ifdef(year, " ({year})")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_display', '*', 'BOOK', '{title}{ifdef(pub_date, " ({pub_date.display_type})")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_display', '*', 'GAME', '"{title}"{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_display', 'AUDIO', '*', '"{title}"{if(instance_no>1," #{instance_no}")}');

#
# Title Display Mask Group Items - Item Listings
#
INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_listing', '*', '*', '{title}{ifdef(year, " ({year})")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_listing', '*', 'BOOK', '{title}{ifdef(pub_date, " ({pub_date.display_type})")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_listing', '*', 'GAME', '{title}{ifdef(gamesystem, " {gamesystem.img}")}{if(instance_no>1," #{instance_no}")}');

#INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
#VALUES('item_listing', 'AUDIO', '*', '{title}{ifdef(artist, " / {artist}")}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('item_listing', 'AUDIO', '*', '{title}{if(instance_no>1," #{instance_no}")}');

#
# Feeds 
#
INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('feeds', '*', 'GAME', '{title}{if(instance_no>1," #{instance_no}")}');

INSERT INTO s_title_display_mask_item(stdm_id, s_item_type_group, s_item_type, display_mask)
VALUES('feeds', '*', '*', '{title}{ifdef(year, " ({year})")}{if(instance_no>1," #{instance_no}")}');

#
# Item Listing Configuration
#
INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type) 
VALUES (1, '*', '*');

INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type)
VALUES (2, 'VIDEO', '*');

INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type)
VALUES (3, 'AUDIO', '*');

INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type)
VALUES (4, '*', 'BOOK');

INSERT INTO s_item_listing_conf(id, s_item_type_group, s_item_type)
VALUES (5, '*', 'GAME');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (1, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 5, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 6, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 7, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 8, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 9, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 10, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (1, 11, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (2, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 5, 's_attribute_type', NULL, 'DIRECTOR', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 6, 's_attribute_type', NULL, 'AGE_RATING', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 7, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 8, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 9, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 10, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 11, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 12, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (2, 13, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (3, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 5, 's_attribute_type', NULL, 'ARTIST', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 6, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 7, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 8, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 9, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 10, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 11, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (3, 12, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (4, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 5, 's_attribute_type', NULL, 'AUTHOR', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 7, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 8, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 9, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 10, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 11, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 12, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (4, 13, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

#INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
#VALUES (5, 1, 's_field_type', 'ITEM_ID', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 2, 's_field_type', 'ITEMTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 3, 's_field_type', 'TITLE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 4, 's_field_type', 'RATING', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 5, 's_attribute_type', NULL, 'GAMEPBLSHR', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 6, 's_attribute_type', NULL, 'NO_PLAYERS', NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 7, 'action_links', NULL, NULL, NULL, 'N', NULL, 'N');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 8, 's_field_type', 'OWNER', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 9, 's_field_type', 'STATUSTYPE', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 10, 's_field_type', 'STATUSCMNT', NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 11, 'borrow_status', NULL, NULL, NULL, 'N', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 12, 's_field_type', 'CATEGORY', NULL, NULL, 'Y', NULL, 'Y');

INSERT INTO s_item_listing_column_conf (silc_id, column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, printable_support_ind)
VALUES (5, 13, 's_field_type', 'INTEREST', NULL, NULL, 'N', NULL, 'Y');

#
# File Types
#
INSERT INTO s_file_type_content_group (content_group) VALUES ('IMAGE');
INSERT INTO s_file_type_content_group (content_group) VALUES ('AUDIO');
INSERT INTO s_file_type_content_group (content_group) VALUES ('VIDEO');
INSERT INTO s_file_type_content_group (content_group) VALUES ('DOCUMENT');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('image/jpeg', 'IMAGE', 'JPEG Image', NULL, 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/jpeg', 'jpeg', 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/jpeg', 'jpg', 'N');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/jpeg', 'jpe', 'N');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('image/gif', 'IMAGE', 'GIF Image', NULL, 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/gif', 'gif', 'Y');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('image/png', 'IMAGE', 'PNG Image', NULL, 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('image/png', 'png', 'Y');

# default support for site plugin cached html pages
INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('text/html', 'DOCUMENT', 'HTML Page', NULL, 'N');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/html', 'html', 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/html', 'htm', 'N');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('text/xml', 'DOCUMENT', 'XML Page', NULL, 'N');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/xml', 'xml', 'Y');

INSERT INTO s_file_type (content_type, content_group, description, image, thumbnail_support_ind)
VALUES ('text/plain', 'DOCUMENT', 'Plain Text', NULL, 'N');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/plain', 'txt', 'Y');

INSERT INTO s_role(role_name, description, signup_avail_ind) VALUES('ADMINISTRATOR', 'Administrator', 'N', 255);
INSERT INTO s_role(role_name, description, signup_avail_ind) VALUES('OWNER', 'Owner', 'Y', 150);
INSERT INTO s_role(role_name, description, signup_avail_ind) VALUES('BORROWER', 'Borrower', 'Y', 100);
INSERT INTO s_role(role_name, description, signup_avail_ind) VALUES('GUEST', 'Guest', 'Y', 50);
INSERT INTO s_role(role_name, description, signup_avail_ind) VALUES('PUBLICACCESS', 'Public Access', 'N', 0);

INSERT INTO s_permission(permission_name, description) values('PERM_VIEW_ANNOUNCEMENTS', 'View Announcements');
INSERT INTO s_permission(permission_name, description) values('PERM_VIEW_WHATSNEW', 'View Whats New');
INSERT INTO s_permission(permission_name, description) values('PERM_VIEW_LISTINGS', 'View Listings');
INSERT INTO s_permission(permission_name, description) values('PERM_VIEW_STATS', 'View Stats');
INSERT INTO s_permission(permission_name, description) values('PERM_VIEW_ADVANCED_SEARCH', 'View Advanced Search');
INSERT INTO s_permission(permission_name, description) values('PERM_VIEW_USER_PROFILE', 'View User Profile');
INSERT INTO s_permission(permission_name, description) values('PERM_VIEW_ITEM_DISPLAY', 'View Item Display');
INSERT INTO s_permission(permission_name, description) values('PERM_VIEW_ITEM_COVERS', 'View Item Covers');

INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_TOOLS', 'Admin Tools');

INSERT INTO s_permission(permission_name, description) values('PERM_USER_BORROWER', 'Borrower User');
INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_BORROWER', 'Borrower Admin');

INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_REVIEWER', 'Review Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_USER_REVIEWER', 'Review Author');

INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_EXPORT', 'Export Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_USER_EXPORT', 'Export User');

INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_IMPORT', 'Import Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_USER_IMPORT', 'Import User');

INSERT INTO s_permission(permission_name, description) values('PERM_USER_INTEREST', 'User item interest');

INSERT INTO s_permission(permission_name, description) values('PERM_ITEM_OWNER', 'Item Owner');
INSERT INTO s_permission(permission_name, description) values('PERM_ITEM_ADMIN', 'Item Admin');

INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_ANNOUNCEMENTS', 'Announcements Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_USER_PROFILE', 'User Profile Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_USER_LISTING', 'User Listing Admin');

INSERT INTO s_permission(permission_name, description) values('PERM_EDIT_USER_PROFILE', 'User Profile Editor');
INSERT INTO s_permission(permission_name, description) values('PERM_CHANGE_PASSWORD', 'Change Password');

INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_QUICK_CHECKOUT', 'Quick Checkout Admin');

INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_CREATE_USER', 'Create User Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_CHANGE_PASSWORD', 'Change Password Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_LOGIN', 'Login Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_CHANGE_USER', 'Admin Change User');

INSERT INTO s_permission(permission_name, description) values('PERM_ADMIN_SEND_EMAIL', 'Send Email Admin');
INSERT INTO s_permission(permission_name, description) values('PERM_SEND_EMAIL', 'Send Email');
INSERT INTO s_permission(permission_name, description) values('PERM_RECEIVE_EMAIL', 'Receive Email');

# role permissions

INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_ANNOUNCEMENTS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_BORROWER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_CHANGE_PASSWORD');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_CHANGE_USER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_CREATE_USER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_EXPORT');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_IMPORT');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_LOGIN');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_QUICK_CHECKOUT');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_REVIEWER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_SEND_EMAIL');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_TOOLS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_USER_LISTING');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ADMIN_USER_PROFILE');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_CHANGE_PASSWORD');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_EDIT_USER_PROFILE');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ITEM_ADMIN');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_ITEM_OWNER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_RECEIVE_EMAIL');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_SEND_EMAIL');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_USER_BORROWER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_USER_EXPORT');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_USER_IMPORT');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_USER_REVIEWER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_USER_INTEREST');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_VIEW_ADVANCED_SEARCH');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_VIEW_ANNOUNCEMENTS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_VIEW_ITEM_COVERS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_VIEW_ITEM_DISPLAY');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_VIEW_LISTINGS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_VIEW_STATS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_VIEW_USER_PROFILE');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('ADMINISTRATOR', 'PERM_VIEW_WHATSNEW');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_CHANGE_PASSWORD');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_EDIT_USER_PROFILE');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_RECEIVE_EMAIL');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_SEND_EMAIL');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_USER_BORROWER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_USER_EXPORT');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_USER_REVIEWER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_USER_INTEREST');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_VIEW_ADVANCED_SEARCH');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_VIEW_ANNOUNCEMENTS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_VIEW_ITEM_COVERS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_VIEW_ITEM_DISPLAY');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_VIEW_LISTINGS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_VIEW_STATS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_VIEW_USER_PROFILE');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('BORROWER', 'PERM_VIEW_WHATSNEW');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('GUEST', 'PERM_VIEW_ADVANCED_SEARCH');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('GUEST', 'PERM_VIEW_ANNOUNCEMENTS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('GUEST', 'PERM_VIEW_ITEM_COVERS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('GUEST', 'PERM_VIEW_ITEM_DISPLAY');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('GUEST', 'PERM_VIEW_LISTINGS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('GUEST', 'PERM_VIEW_STATS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('GUEST', 'PERM_VIEW_WHATSNEW');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_CHANGE_PASSWORD');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_EDIT_USER_PROFILE');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_ITEM_OWNER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_RECEIVE_EMAIL');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_SEND_EMAIL');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_USER_BORROWER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_USER_EXPORT');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_USER_IMPORT');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_USER_REVIEWER');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_USER_INTEREST');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_VIEW_ADVANCED_SEARCH');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_VIEW_ANNOUNCEMENTS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_VIEW_ITEM_COVERS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_VIEW_ITEM_DISPLAY');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_VIEW_LISTINGS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_VIEW_STATS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_VIEW_USER_PROFILE');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('OWNER', 'PERM_VIEW_WHATSNEW');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('PUBLICACCESS', 'PERM_VIEW_ADVANCED_SEARCH');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('PUBLICACCESS', 'PERM_VIEW_ITEM_COVERS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('PUBLICACCESS', 'PERM_VIEW_ITEM_DISPLAY');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('PUBLICACCESS', 'PERM_VIEW_LISTINGS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('PUBLICACCESS', 'PERM_VIEW_STATS');
INSERT INTO s_role_permission (role_name, permission_name) VALUES ('PUBLICACCESS', 'PERM_VIEW_WHATSNEW');

