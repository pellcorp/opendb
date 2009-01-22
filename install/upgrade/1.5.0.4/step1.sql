INSERT INTO s_language_var (language, varname, value) VALUES ('ENGLISH', 'category_chart', 'Categories Chart');

UPDATE s_language_var SET value = 'Item Types Chart' WHERE language = 'ENGLISH' AND varname = 'database_itemtype_chart';
UPDATE s_language_var SET value = 'Ownership Chart' WHERE language = 'ENGLISH' AND varname = 'database_ownership_chart';
