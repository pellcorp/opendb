#########################################################
# OpenDb 1.0 DVDFr.com (dvdfr) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'dvdfr', 'dvdfr', 'DVDFr.com', 'dvdfr.gif', 'A good source for region 2 DVD (french).', 'http://www.dvdfr.com', 25, 'http://www.dvdfr.com/dvd/dvd.php?id={dvdfr_id}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'dvdfr', 'title', 1, '', 'Title Search', 'text', '', '{ifdef(alt_title, \'{alt_title} / \')}{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'dvdfr', 'dvdfr_id', 2, '', 'DVDFr ID', 'text', '', '{dvdfr_id}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'dvdfr', '*', '*', 1, 'More Info', 'http://www.dvdfr.com/dvd/dvd.php?id={dvdfr_id}', 'http://www.dvdfr.com/search/search.php?multiname={title}&x=0&y=0' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', '*', '*', 'alt_title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', 'VIDEO', '*', 'blurb', 'MOVIE_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', 'VIDEO', '*', 'genre', 'MOVIEGENRE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'dvdfr', '*', '*', 'title', 'S_TITLE', 'N' );

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'DVDFRID', 'Dvdfr ID', 'Dvdfr ID', 'hidden', 'hidden', '', 'dvdfr' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'DVDFRID',  0, '', 'N' );
