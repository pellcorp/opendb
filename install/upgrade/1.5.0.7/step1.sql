UPDATE s_site_plugin
SET classname = 'imdbphp'
WHERE site_type = 'imdb';

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'imdb', 'title_search_faster_alternate', '0',
 'use our own fast alternate page search parser', 'TRUE' );

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type, subtype ) VALUES ('email.smtp', 'secure', 5, 'Email SMTP Secure Connection', 'Enables secure connections with the SMTP server (ssl or tls).', 'value_select', 'none,ssl,tls');

# no longer supporting partial match for imdb
DELETE FROM s_site_plugin_conf WHERE site_type = 'imdb' AND name = 'title_search_match_types';

# Adding bluray support to amazon.com
# the dvd page has more info we can parse for blu-ray!
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazon', 'item_type_to_index_map', 'BD', '', 'dvd' );

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BD', 'AMAZONASIN',  0, '', 'N' );

# Adding Bluray to imdb
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BD', 'IMDB_ID',  0, '', 'N' );

# Adding blueray to dvdempire
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BD', 'DVDEMPR_ID',  0, '', 'N' );

# Change the BOOK PUB_DATE to be a PUB_YEAR
UPDATE s_attribute_type SET s_attribute_type = 'PUB_YEAR', description = 'Year Published' WHERE s_attribute_type = 'PUB_DATE';
UPDATE s_item_attribute_type SET s_attribute_type = 'PUB_YEAR' WHERE s_attribute_type = 'PUB_DATE';
UPDATE item_attribute SET s_attribute_type = 'PUB_YEAR' WHERE s_attribute_type = 'PUB_DATE';

INSERT INTO s_item_type_group_rltshp ( s_item_type_group, s_item_type ) VALUES ( 'VIDEO', 'BD' );

# Update all amazon plugins to use the amazon class
UPDATE s_site_plugin SET classname = 'amazon' WHERE site_type IN ('amazonfr', 'amazonuk', 'amazonde');

# iblist no longer supports isbn
DELETE FROM s_site_plugin_input_field
WHERE site_type = 'iblist' AND field = 'isbn';

# Add attribute permission support.
ALTER TABLE s_attribute_type ADD view_perm VARCHAR(50);
ALTER TABLE s_role ADD priority TINYINT(3) unsigned NOT NULL;
UPDATE s_role SET priority = 255 WHERE role_name = 'ADMINISTRATOR';
UPDATE s_role SET priority = 150 WHERE role_name = 'OWNER';
UPDATE s_role SET priority = 100 WHERE role_name = 'BORROWER';
UPDATE s_role SET priority = 50 WHERE role_name = 'GUEST';
UPDATE s_role SET priority = 0 WHERE role_name = 'PUBLICACCESS';
