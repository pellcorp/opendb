#########################################################
# OpenDb 1.0.3 comicbookdb.com (cmbkdbt) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'cmbkdbt', 'cmbkdbt', 'comicbookdb.com', 'comicbookdb.gif', 'Comic Book Database', 'http://www.comicbookdb.com', 25, 'http://www.comicbookdb.com/title.php?ID={cmbkdbt_id}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'cmbkdbt', 'title', 1, 'Enter the name of the title here', 'Title Search', 'text', '', '{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'cmbkdbt', 'cmbkdbt_id', 2, 'The title ID number', 'Title ID', 'text', '', '{cmbkdbt_id}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'cmbkdbt', 'issue_num', 3, 'The issue Number(do not enter any 0s before issue number)', 'Issue Number', 'text', '', '{cbissue_num}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'cmbkdbt', 'cmbkdbi_id', 4, 'The issue ID number', 'Issue ID', 'text', '', '{cmbkdbi_id}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'cmbkdbt', '', 'BOOK', 1, 'More Info', '{cmbkdbt_id}', 'http://www.comicbookdb.com/title.php?ID={cmbkdbt_id}' );
INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'cmbkdbt', '', 'BOOK', 2, 'More Info', '{cmbkdbi_id}', 'http://www.comicbookdb.com/issue.php?ID={cmbkdbi_id}' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'bookpart', 'BOOK_PART', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'genre', 'BOOKGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'illustrator', 'ARTIST', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'notes', 'COMMENTS', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'pages', 'NB_PAGES', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'release', 'PUB_DATE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'storyline', 'SYNOPSIS', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'cmbkdbt', '*', 'BOOK', 'title', 'S_TITLE', 'N' );

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'CMBKDBI_ID', 'Comicbookdb Title ID', 'Comicbookdb Title ID', 'hidden', 'hidden', '', 'cmbkdbt' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BOOK', 'CMBKDBI_ID',  0, '', 'N' );