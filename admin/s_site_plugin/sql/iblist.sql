#########################################################
# OpenDb 1.0 Internet Book List (iblist) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url ) VALUES ( 'iblist', 'iblist', 'Internet Book List', 'iblist.jpg', 'Internet Book List provide a comprehensive and easily accessible database of books.', 'http://www.iblist.com', 50, 'http://www.iblist.com/book.php?id={iblist_id}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'iblist', 'title', 1, '', 'Title Search', 'text', '', '{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'iblist', 'author', 2, '', 'Author', 'text', '', '{author}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'iblist', 'iblist_id', 3, '', 'ID Number', 'hidden', '', '{iblist_id}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'iblist', '*', '*', 1, 'More Info', 'http://www.iblist.com/book.php?id={iblist_id}', '' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'iblist', '*', '*', 'genre', 'BOOKGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'iblist', '*', '*', 'plot', 'SYNOPSIS', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'iblist', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'iblist', '*', '*', 'title', 'S_TITLE', 'N' );

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'IBLIST_ID', 'IBList ID Number', 'IBList ID', 'hidden', 'hidden', '', 'iblist' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BOOK', 'IBLIST_ID',  0, '', 'N' );
