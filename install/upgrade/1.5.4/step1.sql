INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'category_chart', 'Categories Chart');

UPDATE s_language_var SET value = 'Item Types Chart' WHERE language = 'ENGLISH' AND varname = 'database_itemtype_chart';
UPDATE s_language_var SET value = 'Ownership Chart' WHERE language = 'ENGLISH' AND varname = 'database_ownership_chart';

UPDATE s_config_group_item SET order_no = 6 WHERE group_id = 'item_review' AND order_no = 5;
UPDATE s_config_group_item SET order_no = 5 WHERE group_id = 'item_review' AND order_no = 4;
UPDATE s_config_group_item SET order_no = 4 WHERE group_id = 'item_review' AND order_no = 3;
UPDATE s_config_group_item SET order_no = 3 WHERE group_id = 'item_review' AND order_no = 2;
UPDATE s_config_group_item SET order_no = 2 WHERE group_id = 'item_review' AND order_no = 1;

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('item_review', 'enable', 1, 'Enable', '', 'boolean');

INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('item_review', 'enable', 'TRUE');

ALTER TABLE borrowed_item ADD  more_information	TEXT;

DROP TABLE IF EXISTS borrowed_item_hist;
CREATE TABLE borrowed_item_hist (
  sequence_number	INTEGER(10) UNSIGNED NOT NULL auto_increment,
  bi_sequence_number	INTEGER(10) UNSIGNED NOT NULL,
  more_information	TEXT,
  status			VARCHAR(1) NOT NULL,
  update_on			TIMESTAMP NOT NULL,
  PRIMARY KEY ( sequence_number )
) ENGINE=MyISAM COMMENT='Borrowed Item History table';

DELETE FROM s_language_var WHERE varname IN('external_url_error', 'user_listing_column_header_sort_help');

# new users since 1.0 might not have had the interest language vars loaded as they were not in the english.sql file, but rather in the upgrade sql only!
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest', 'Interest'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_mark', 'Click to mark your interest for this item'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_remove', 'Click to remove your interest for this item'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_remove_all', 'Remove all your interest marks'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_help', 'You marked your interest in this item'); 
INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'interest_only_marked', 'Only items with marked interest'); 

INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'more_information_help', 'Additional information can be entered into the More Info field');

INSERT INTO s_config_group_item ( group_id, id, order_no, prompt, description, type ) VALUES ('welcome.whats_new', 'restrict_last_login', 3, 'Restrict Last Login', 'Restrict whats new stats to items added since last login', 'boolean');
INSERT INTO s_config_group_item_var ( group_id, id, value ) VALUES ('welcome.whats_new', 'restrict_last_login', 'TRUE');


