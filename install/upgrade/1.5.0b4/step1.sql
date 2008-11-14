#
# fixes for UPC_ID
#

UPDATE s_attribute_type SET input_type = 'text', input_type_arg1 = '13', display_type = 'display', display_type_arg1 = '%value%' WHERE s_attribute_type = 'UPC_ID';
