########################################
# mangaupdate.sql site plugin definition
########################################

#
# Cleanup
#
# DELETE FROM s_site_plugin WHERE site_type = 'mangaupdat';
# DELETE FROM s_site_plugin_conf WHERE site_type = 'mangaupdat';
# DELETE FROM s_site_plugin_input_field WHERE site_type = 'mangaupdat';
# DELETE FROM s_site_plugin_link WHERE site_type = 'mangaupdat';
# DELETE FROM s_site_plugin_s_attribute_type_map WHERE site_type = 'mangaupdat';
# DELETE FROM s_site_plugin_s_attribute_type_lookup_map WHERE site_type = 'mangaupdat';

INSERT INTO s_site_plugin (site_type, classname, title, image, description, external_url, items_per_page, more_info_url) VALUES ('mangaupdat', 'mangaupdat', 'mangaupdates.com', 'mangaupdate.jpg', 'Baka-Updates - Manga Division - Good Manga Series information', 'http://www.mangaupdates.com', 25, 'http://www.mangaupdates.com/series.html?id={mangauid}');

#
# Input Fields
#	
INSERT INTO s_site_plugin_input_field (site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask) VALUES ('mangaupdat', 'title', 1, '', 'Title Search', 'text', '', '{title}');
INSERT INTO s_site_plugin_input_field (site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask) VALUES ('mangaupdat', 'MANGAUID', 2, '', 'Manga Update ID', 'text', '', '{mangauid}');

#
# Links
#
INSERT INTO s_site_plugin_link(site_type, s_item_type_group, s_item_type, order_no, description, url, title_url) VALUES ('mangaupdat', '*', '*', 1, 'More Info', 'http://www.mangaupdates.com/series.html?id={mangauid}', 'http://www.mangaupdates.com/series.html?search={title}');

#
# site variable to s_attribute_type mapping
#
INSERT INTO s_site_plugin_s_attribute_type_map(site_type, variable, s_item_type_group, s_item_type, s_attribute_type) VALUES ('MANGAUPDAT', 'title', '*', '*', 'S_TITLE');
INSERT INTO s_site_plugin_s_attribute_type_map(site_type, variable, s_item_type_group, s_item_type, s_attribute_type) VALUES ('MANGAUPDAT', 'description', '*', '*', 'SYNOPSIS');
INSERT INTO s_site_plugin_s_attribute_type_map(site_type, variable, s_item_type_group, s_item_type, s_attribute_type) VALUES ('MANGAUPDAT', 'genre', '*', '*', 'BOOKGENRE');
INSERT INTO s_site_plugin_s_attribute_type_map(site_type, variable, s_item_type_group, s_item_type, s_attribute_type) VALUES ('MANGAUPDAT', 'asscname', '*', '*', 'ALT_TITLE');

#
# mangaupdates BOOKGENRE list
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Action' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Adult' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Adventure' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Comedy' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Doujinshi' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Drama' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Ecchi' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Fantasy' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Gender Bender' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Harem' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Hentai' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Historical' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Horror' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Josei' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Lolicon' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Martial Arts' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Mature' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Mecha' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Mystery' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Psychological' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Romance' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'School Life' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Sci-fi' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Seinen' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Shotacon' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Shoujo' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Shoujo Ai' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Shounen' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Shounen Ai' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Slice of Life' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Smut' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Sports' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Supernatural' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Tragedy' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Yaoi' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, value ) VALUES ( 'BOOKGENRE', 'Yuri' );

#
# Site Plugin System Attribute Type Map
#


####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

# use the following line instead if you want the ID hidden
# INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, site_type ) VALUES ( 'MANGAUID', 'Manga Update ID', 'Manga Update ID', 'hidden', 'hidden', 'mangaupdat' );

# use the following line if you want the ID exposed
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, display_type, display_type_arg1, site_type ) VALUES ( 'MANGAUID', 'Manga Update ID', 'Manga Update ID', 'text', 30, '*', 'display', '%value%', 'mangaupdat' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind, rss_ind, printable_ind ) VALUES ( 'BOOK', 'MANGAUID',  0, '', 'N', 'N', 'Y' );

