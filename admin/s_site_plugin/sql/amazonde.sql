#########################################################
# OpenDb 1.0 Amazon.de (amazonde) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'amazonde', 'amazon', 'Amazon.de', 'amazonde.gif', 'A good source of CD, DVD (Region 2), VHS, Books, Games, etc.', 'http://www.amazon.de', 25, 'http://www.amazon.de/exec/obidos/ASIN/{amazdeasin}' );

#
# Site Plugin Configuration
#.

INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_input.title_articles', '0', '', 'Ein' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_input.title_articles', '1', '', 'Eine' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_input.title_articles', '2', '', 'Der' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_input.title_articles', '3', '', 'Die' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_input.title_articles', '4', '', 'Das' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'BOOK', '', 'books' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'CD', '', 'music' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'DIVX', '', 'dvd' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'DVD', '', 'dvd' );
# the dvd page has more info we can parse for blu-ray!
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'BD', '', 'dvd' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'GAME', '', 'videogames' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'LD', '', 'dvd' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'MP3', '', 'music' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'VCD', '', 'dvd' );
INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) VALUES ( 'amazonde', 'item_type_to_index_map', 'VHS', '', 'vhs' );

#
# Update the binding attribute
#
UPDATE s_attribute_type SET input_type_arg1='Hardcover,Paperback,Taschenbuch,Gebundene Ausgabe' WHERE s_attribute_type Like 'BINDING';

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'amazonde', 'title', 1, '', 'Title/ISBN/UPC Search', 'text', '', '{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'amazonde', 'amazdeasin', 2, '', 'ASIN Number', 'text', '', '{ifdef(amazdeasin,{amazdeasin},{if(s_item_type==BOOK,{isbn},\'\')})}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'amazonde', '*', '*', 1, 'More Info', 'http://www.amazon.de/exec/obidos/ASIN/{amazdeasin}', '' );
INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'amazonde', '*', 'GAME', 2, 'Screenshots', 'http://www.amazon.de/exec/obidos/tg/stores/detail/-/videogames/{amazdeasin}/pictures#more-pictures', '' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', 'AUDIO', '*', 'blurb', 'COMMENTS', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', 'BOOK', 'blurb', 'COMMENTS', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', 'GAME', 'blurb', 'GAME_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', 'VIDEO', '*', 'blurb', 'MOVIE_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', '*', 'audio_lang', 'AUDIO_LANG', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', '*', 'audio_xtra', 'AUDIO_XTRA', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', 'DVD', 'dvd_audio', 'DVD_AUDIO', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', 'BOOK', 'genre', 'BOOKGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', 'BOOK', 'language', 'TEXT_LANG', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', 'GAME', 'genre', 'GAMEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', 'VIDEO', '*', 'genre', 'MOVIEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', 'AUDIO', '*', 'genre', 'MUSICGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', '*', 'subtitles', 'SUBTITLES', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', '*', 'listprice', 'COVERPRICE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', 'VIDEO', '*', 'listprice', 'RET_PRICE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', '*', 'title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'amazonde', '*', '*', 'title', 'S_TITLE', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AGE_RATING', 'Freigegeben ab 12 Jahren', 'USK12' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AGE_RATING', 'Freigegeben ab 16 Jahren', 'USK16' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AGE_RATING', 'Freigegeben ab 18 Jahren', 'USK18' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AGE_RATING', 'Freigegeben ab 6 Jahren', 'USK6' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AGE_RATING', 'Ohne Altersbeschrankung', 'USK0' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch (Dolby Digital 2.0)', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch (Dolby Digital 5.1 EX)', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch (Dolby Digital 5.1 EX, Dolby Digital 5.1)', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch (Dolby Digital 5.1)', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch (Dolby Digital 5.1, THX Surround EX)', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch (Dolby Digital 5.1, THX Surround EX)', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch (Dolby Surround)', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch (Mono)', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch THX', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Deutsch', 'GERMAN' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Englisch (Dolby Digital 2.0)', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Englisch (Dolby Digital 5.1 EX)', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Englisch (Dolby Digital 5.1 EX, Dolby Digital 5.1)', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Englisch (Dolby Digital 5.1)', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Englisch (Dolby Digital 5.1, THX Surround EX)', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Englisch (Dolby Surround)', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Englisch (Mono)', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Englisch', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Franzosisch (Dolby Digital 5.1)', 'FRENCH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Franzosisch (Dolby Surround)', 'FRENCH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Franzosisch', 'FRENCH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Italienisch (Dolby Digital 5.1)', 'ITALIAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Italienisch (Dolby Surround)', 'ITALIAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Italienisch', 'ITALIAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Spanisch (Dolby Digital 5.1)', 'SPANISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Spanisch (Dolby Surround)', 'SPANISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'AUDIO_LANG', 'Spanisch', 'SPANISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'RATIO', '1.77', '1.78' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'TEXT_LANG', 'Deutsch', 'GERMAN' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'TEXT_LANG', 'Englisch', 'ENGLISH' );
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BINDING', 'Gebundene Ausgabe', 'Hardcover' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BINDING', 'Taschenbuch', 'Paperback' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Amazon.de-Sonderausgaben', 'SONDER' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Antiquarische Bucher', 'ANTIQ' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Belletristik', 'BELL' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Bestseller aller Ressorts', 'BESTSELL' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Business & Karriere', 'BUS' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Bucher in den Medien', 'BIDM' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Borse & Geld', 'GELD' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Computer & Internet', 'COMP' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Computers & Internet', 'COMP' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'E-Books', 'EBOOK' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'English Books', 'INTERNAT' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Erotik', 'EROS' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Fachbucher', 'FACH' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Film, Kultur & Comics', 'FILM' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Gebrauchte Bucher', 'USED' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Horbucher', 'HORCD' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Kinder- & Jugendbucher', 'KIND' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Kinderwelt', 'KINDER' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Kochen & Lifestyle', 'KOCH' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Krimis & Thriller', 'THRILLER' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Lernen & Nachschlagen', 'LERNEN' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Musiknoten', 'MUSIK' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Naturwissenschaften & Technik', 'NATUR' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Politik, Biografien & Geschichte', 'POLITIK' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Preis-Hits', 'PREIS' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Ratgeber', 'RATGEBER' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Reise & Sport', 'REISE' ); 
#INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Religion & Esoterik', 'RELIGION' ); 

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Bilderbucher', 'Children' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Handbuch/Lehrbuch', 'Science' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Kinder- und Jugendbucher', 'Children' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Science Fiction', 'Sci-Fi' );
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'BOOKGENRE', 'Zeitschriften', 'Newspaper' ); 

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMERATING', 'Freigegeben ab 12 Jahren gem�� � 14 JuSchG', 'USK12' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMERATING', 'Freigegeben ab 16 Jahren gem�� � 14 JuSchG', 'USK16' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMERATING', 'Freigegeben ab 6 Jahren gem�� � 14 JuSchG', 'USK6' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMERATING', 'Freigegeben ohne Altersbeschr�nkung gem�� � 14 JuSchG', 'USK0' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMERATING', 'Keine Jugendfreigabe gem�� � 14 JuSchG', 'USK18' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'Game Boy', 'GAMEBOY' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'Game Boy Advance', 'GAMEBOYADVANCE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'Game Boy Color', 'GAMEBOY' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'GameCube', 'GAMECUBE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'Mac OS', 'MACCDROM' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'Nintendo 64', 'N64' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'PlayStation', 'PS1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'PlayStation2', 'PS2' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'Sega Dreamcast', 'DREAMCAST' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'Windows', 'PCCDROM' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'GAMESYSTEM', 'Xbox', 'XBOX' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Arabisch', 'ARABIC' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Bulgarisch', 'BULGARIAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Chechisch', 'CZECH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Chinesisch', 'CHINESE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Deutsch', 'GERMAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Danisch', 'DANISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Englisch', 'ENGLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Finnisch', 'FINNISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Franzosisch', 'FRENCH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Griechisch', 'GREEK' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Hebraisch', 'HEBREW' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Hinduisch', 'HINDOE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Hollandisch', 'DUTCH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Islandisch', 'ISLANDIC' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Italienisch', 'ITALIAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Japanisch', 'JAPANESE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Norwegisch', 'NORWEGIAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Polnisch', 'POLISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Portugiesisch', 'PORTUGUESE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Schwedisch', 'SWEDISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Slowenisch', 'SLOVAKIAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Spanisch', 'SPANISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Turkisch', 'TURKISH' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'SUBTITLES', 'Ungarisch', 'HUNGARIAN' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Komodie', 'COMEDY' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Erotik', 'ADULT' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Anime', 'ANIMATION' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Abenteuer', 'ADVENTURE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Krimi', 'THRILLER' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Kriegsfilm', 'WAR' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Dokumentationen', 'DOCUMENTARY' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Erotikdrama', 'ADULT' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Musik', 'MUSIC' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Musik-DVDs', 'MUSIC' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'amazonde', 'MOVIEGENRE', 'Romantische Komodie', 'ROMANCE' ); 

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'AMAZDEASIN', 'Amazon Germany Standard Item Number', 'German Amazon ASIN', 'hidden', 'hidden', '', 'amazonde' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BOOK', 'AMAZDEASIN',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'CD', 'AMAZDEASIN',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'DVD', 'AMAZDEASIN',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BD', 'AMAZDEASIN',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'GAME', 'AMAZDEASIN',  0, '', 'N' );
