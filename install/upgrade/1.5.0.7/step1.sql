UPDATE s_site_plugin
SET classname = 'imdbphp'
WHERE site_type = 'imdb';

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'imdb', 'title_search_faster_alternate', '0',
 'use our own fast alternate page search parser', 'TRUE' );

