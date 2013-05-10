#
# Document widget
#
INSERT INTO s_file_type (content_type, content_group, description, image)
VALUES ('application/pdf', 'DOCUMENT', 'PDF Document', 'pdf.png');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('application/pdf', 'pdf', 'Y');

INSERT INTO s_file_type (content_type, content_group, description, image)
VALUES ('application/msword', 'DOCUMENT', 'Microsoft Word Document', 'winword.gif');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('application/msword', 'doc', 'Y');

INSERT INTO s_file_type (content_type, content_group, description, image)
VALUES ('text/plain', 'DOCUMENT', 'Text Document', 'default.gif');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/plain', 'txt', 'Y');

INSERT INTO s_file_type_extension (content_type, extension, default_ind)
VALUES ('text/plain', 'text', 'N');

DELETE FROM s_attribute_type WHERE s_attribute_type = 'DOCUMENT';
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DOCUMENT', 'Document', 'Document', 'url', '50', '500', 'DOCUMENT', NULL, NULL, 'fileviewer','%img% %value%', '1024', '768', NULL, NULL, 'N', 'Y', 'N', 'N', NULL, NULL);
