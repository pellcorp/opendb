UPDATE s_site_plugin
SET classname = 'imdbphp'
WHERE site_type = 'imdb';

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'imdb', 'title_search_faster_alternate', '0',
 'use our own fast alternate page search parser', 'TRUE' );

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('email.smtp', 'secure', 5, 'Email SMTP Secure Connection', 'Enables secure connections with the SMTP server (ssl or tls).', 'value_select', 'none,ssl,tls');

# no longer supporting partial match for imdb
DELETE FROM s_site_plugin_conf WHERE site_type = 'imdb' AND name = 'title_search_match_types';