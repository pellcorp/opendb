#########################################################
# OpenDb 1.0 freedb2.org (freedb) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'freedb', 'freedb', 'freedb2.org', 'freedb.gif', 'A free approach to cddbp.', 'http://www.freedb2.org', 50, 'http://www.freedb2.org/freedb/{cddbgenre}/{freedb_id}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'freedb', 'title', 1, '', 'Artist / Title Search', 'text', '', '{ifdef(artist, \'{artist} / \')}{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'freedb', 'freedb_id', 2, '', 'Freedb ID', 'hidden', '', '{freedb_id}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'freedb', 'cddbgenre', 3, '', 'CDDB Genre', 'hidden', '', '{cddbgenre}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'freedb', '*', '*', 1, 'More Info', 'http://www.freedb2.org/freedb/{cddbgenre}/{freedb_id}', '' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'freedb', '*', '*', 'genre', 'MUSICGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'freedb', '*', '*', 'run_time', 'CDTIME', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'freedb', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'freedb', '*', '*', 'title', 'S_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'freedb', '*', '*', 'tracks', 'CDTRACK', 'N' );

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'FREEDB_ID', 'Freedb ID', 'Disc ID', 'hidden', 'hidden', '', 'freedb' );
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'CDDBGENRE', 'CDDB Genre', 'Genre', 'hidden', 'hidden', '', 'freedb' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'CD', 'CDDBGENRE',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'CD', 'FREEDB_ID',  0, '', 'N' );
