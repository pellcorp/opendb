#########################################################
# OpenDb 1.5 The Movie Database (tmdb) Site Plugin
#########################################################

#
# Site Plugin.
#
INSERT INTO s_site_plugin (site_type, classname, title, image, description, external_url, items_per_page, more_info_url)
    VALUES ('tmdb', 'tmdb', 'TheMovieDB', 'tmdb.png', 'TheMovieDB', 'https://www.themoviedb.org/', 50, 'https://www.themoviedb.org/movie/{tmdb_id}');

#
# Site Plugin Configuration
#

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value )
    VALUES ( 'tmdb', 'tmdb_apikey', '0', 'TheMovieDB API Key', '' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value )
  VALUES ( 'tmdb', 'cover_width', '0', 'Cover Image Width', 'original' );

#
# Site Plugin Input Fields
#
INSERT INTO s_site_plugin_input_field (site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask)
    VALUES ('tmdb', 'title', 1, '', 'Title', 'text', '', '{title}');
INSERT INTO s_site_plugin_input_field (site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask)
    VALUES ('tmdb', 'year', 2, '', 'Year', 'text', '', '{year}');
INSERT INTO s_site_plugin_input_field (site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask)
    VALUES ('tmdb', 'tmdb_id', 3, '', 'TheMovieDB ID', 'text', '', '{tmdb_id}');

#
# Site Plugin Links
#
INSERT INTO s_site_plugin_link (site_type, s_item_type_group, s_item_type, order_no, description, url, title_url)
    VALUES ('tmdb', '*', '*', 1, 'More Info', 'http://www.themoviedb.org/movie/{tmdb_id}', '');
INSERT INTO s_site_plugin_link (site_type, s_item_type_group, s_item_type, order_no, description, url, title_url)
    VALUES ('tmdb', '*', '*', 2, 'Trailers', 'http://www.themoviedb.org/movie/{tmdb_id}/trailers', '');
INSERT INTO s_site_plugin_link (site_type, s_item_type_group, s_item_type, order_no, description, url, title_url)
    VALUES ('tmdb', '*', '*', 3, 'More Info (IMDB)', '{ifdef(imdb_id, http://www.imdb.com/title/{imdb_id})}', '');

#
# Site Plugin System Attribute Type Map
#
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'orig_title', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'tagline', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'plot', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'runtime', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'cover', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'imdb_id', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'year', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'budget', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'revenue', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'genre', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'prod_companies', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'actors', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'directors', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'producers', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'music', '', 'N');
INSERT INTO s_site_plugin_s_attribute_type_map (site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind)
    VALUES ('tmdb', '*', '*', 'writers', '', 'N');

#
# Site Plugin Attribute Type(s)
#
INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type)
    VALUES ('TMDB_ID', 'TheMovieDB ID', 'TheMovieDB ID', 'hidden', 'hidden', '', 'tmdb');
