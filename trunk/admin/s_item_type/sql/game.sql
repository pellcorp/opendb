#########################################################
# OpenDb 1.5.0b6 'GAME' Item Type
#########################################################

#
# Item Type
#
INSERT INTO s_item_type ( s_item_type, description, image ) VALUES ( 'GAME', 'Console / PC Game', 'joystick.gif' );

#
# Attributes (non-core)
#
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'ALT_ID', 'Alternate Item ID', 'Alt Item ID', 'text', '10', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'ALT_TITLE', 'Alternate Title', 'Alternate Title', 'text', '50', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'UPC_ID', 'UPC ID', 'UPC ID', 'text', '13', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'IMAGEURL', 'Item Image URL', 'Image', 'url', '50', '*', 'IMAGE', NULL, NULL, 'hidden',NULL, NULL, NULL, NULL, NULL, 'N', 'Y', 'N', 'N', 'IMAGE', NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'IMAGEURLB', 'Back Image URL', 'Back Image', 'url', '50', '*', 'IMAGE', NULL, NULL, 'hidden',NULL, NULL, NULL, NULL, NULL, 'N', 'Y', 'N', 'N', 'IMAGE', NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAME_PLOT', 'Description of game', 'Description', 'textarea', '50', '5', NULL, NULL, NULL, '%value%',NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEPBLSHR', 'Publisher', 'Publisher', 'text', '50', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEDVLPR', 'Game Developer', 'Developer(s)', 'text', '50', NULL, NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DIRECTOR', 'Director of a Movie', 'Director(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'PRODUCER', 'Producer(s) of a media', 'Producer(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'WRITER', 'The Writer(s) of a media', 'Writer(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DESIGNER', 'Designer for show', 'Designer(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'COMPOSER', 'Music Composer(s)', 'Composer(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'ACTORS', 'List of Actors in a movie', 'Actor(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'PROGRAMMER', 'Programmers', 'Programmer(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMESYSTEM', 'Game System', 'System', 'radio_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEREQS', 'System Requirements', 'System Requirements', 'textarea', '50', '5', NULL, NULL, NULL, 'list','ticks', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAME_AUDIO', 'Game audio format support', 'Audio Format', 'checkbox_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'TV_RES', 'Supported TV Resolutions', 'TV Resolution(s)', 'checkbox_grid', NULL, 'VERTICAL', NULL, NULL, NULL, 'list',NULL, NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEGENRE', 'Game Genre', 'Genre', 'checkbox_grid', '%display%', 'VERTICAL', NULL, NULL, NULL, 'category','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', 'CATEGORY', NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEFLOW', 'How the game flows', 'Flow', 'checkbox_grid', '%display%', 'VERTICAL', NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'AUDIO_LANG', 'Audio Languages', 'Audio Language(s)', 'checkbox_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'TEXT_LANG', 'Text Languages', 'Text Language(s)', 'checkbox_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'SUBTITLES', 'Subtitle languages', 'Subtitles', 'checkbox_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEREGION', 'Game Region', 'Region', 'single_select', '%display%', NULL, NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEPERSP', 'Game Perspective', 'Pespective', 'checkbox_grid', '%display%', 'VERTICAL', NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEPBDATE', 'Date Published', 'Published', 'filtered', '10', '10', '0-9/', '%field% (DD/MM/YYYY)', NULL, '%value%',NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'NO_PLAYERS', 'Number Players', 'Players', 'number', '3', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAME_ADDON', 'Wether the Game is an expansion pack or whatever', 'Standalone/Expansion', 'value_select', 'Standalone, Expansion Pack', NULL, NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'CONTROLLER', 'Special Controller', 'Special Controller', 'text', '50', NULL, NULL, NULL, NULL, '%value%',NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMERATING', 'ESRB Video and Computer Game Rating', 'Rating', 'radio_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAME_MEDIA', 'The media type the game is stored on', 'Media', 'radio_grid', NULL, 'VERTICAL', NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'NO_MEDIA', 'Records no of Media', 'Number of Media', 'number', '3', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'FEATURES', 'Game Extra Features Details', 'Features', 'textarea', '50', '5', NULL, NULL, NULL, 'list','ticks', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'COMMENTS', 'Extra Comments', 'Comments', 'textarea', '50', '5', NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);

#
# Item Attribute Relationships
#
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_ITEM_ID', 0, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_TITLE', 1, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'ALT_ID', 2, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'ALT_TITLE', 2, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'UPC_ID', 3, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'IMAGEURL', 4, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'IMAGEURLB', 5, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAME_PLOT', 10, NULL, 'N', 'N', 'Y', 'Y' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEPBLSHR', 20, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEDVLPR', 21, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'DIRECTOR', 22, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'PRODUCER', 22, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'WRITER', 23, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'DESIGNER', 24, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'COMPOSER', 26, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'ACTORS', 27, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'PROGRAMMER', 28, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMESYSTEM', 30, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEREQS', 35, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAME_AUDIO', 36, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'TV_RES', 37, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEGENRE', 40, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEFLOW', 41, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'AUDIO_LANG', 42, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'TEXT_LANG', 43, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'SUBTITLES', 44, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEREGION', 45, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEPERSP', 46, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEPBDATE', 47, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'NO_PLAYERS', 50, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAME_ADDON', 55, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'CONTROLLER', 60, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMERATING', 65, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAME_MEDIA', 66, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'NO_MEDIA', 67, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'FEATURES', 70, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'COMMENTS', 70, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_STATUS', 253, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_STATCMNT', 254, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_DURATION', 255, NULL, 'N', 'N', 'Y', 'N' );

#
# Attribute Type Lookup (GAMESYSTEM)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 1, 'PS1', 'Playstation', 'ps1.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 2, 'PS2', 'Playstation 2', 'ps2.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 3, 'PS3', 'Playstation 3', 'ps3.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 4, 'PSP', 'Playstation Portable', 'psp.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 5, 'GAMEGEAR', 'Game Gear', 'gamegear.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 6, 'MASTER', 'Master System', 'mastersystem.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 7, 'GENESIS', 'Genesis', 'genesis.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 8, 'SATURN', 'Saturn', 'saturn.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 9, 'DREAMCAST', 'Dreamcast', 'dreamcast.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 10, 'GAMEBOY', 'Gameboy', 'gameboy.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 11, 'GAMEBOYCOLOR', 'Gameboy Color', 'gameboycolor.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 12, 'GAMEBOYADVANCE', 'Gameboy ADVANCE', 'gba.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 13, 'DS', 'DS', 'ds.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 14, 'NES', 'Nintendo Entertainment System', 'nes.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 15, 'SNES', 'Super Nintendo Entertainment System', 'snes.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 16, 'N64', 'Nintendo 64', 'n64.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 17, 'GAMECUBE', 'Game Cube', 'gamecube.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 18, 'WII', 'Wii', 'wii.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 19, 'XBOX', 'Xbox', 'xbox.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 20, 'XBOX360', 'Xbox 360', 'xbox360.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 21, 'TURBOGRAFX', 'TurboGrafx-16', 'turbografx.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 22, 'ATARI', 'Atari', 'atari.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 23, 'NEOGEO', 'NeoGeo', 'neogeo.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 24, 'MACCDROM', 'Macintosh', 'apple.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 25, 'DOS', 'DOS', 'dos.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 26, 'PCCDROM', 'Windows', 'windows.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 27, 'LINUX', 'Linux', 'linux.gif', 'N' );

#
# Attribute Type Lookup (GAME_AUDIO)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 0, 'MONO', 'Monaural', 'mono.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 1, 'STEREO', 'Analog Stereo', 'stereo.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 1, 'SURROUND', 'Surround', 'surround51.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 2, 'PCM2', 'PCM 2.0', 'stereo.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 2, 'PCM4', 'PCM 4.0', 'surround4.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 2, 'PCM5.1', 'PCM 5.1', 'surround51.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 2, 'PCM6', 'PCM 6.1', 'surround61.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 2, 'PCM7.1', 'PCM 7.1', 'surround71.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 2, 'PCM8', 'PCM 8.1', 'surround81.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 3, 'DOLBY', 'Dolby', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 3, 'DOLBYDIGITAL5.1', 'Dolby Digital 5.1', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 3, 'DOLBYDIGITAL6.1', 'Dolby Digital 6.1', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 3, 'DOLBYDIGITAL7.1', 'Dolby Digital 7.1', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 3, 'DOLBYPROLOGICII', 'Dolby Pro Logic II', 'dolby.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_AUDIO', 3, 'DTS5.1', 'DTS 5.1', 'dts.gif', 'N' );

#
# Attribute Type Lookup (TV_RES)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TV_RES', 1, '480i', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TV_RES', 2, '480p', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TV_RES', 3, '576i', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TV_RES', 4, '576p', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TV_RES', 5, '720i', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TV_RES', 6, '720p', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TV_RES', 7, '1080i', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TV_RES', 8, '1080p', '', '', 'N' );

#
# Attribute Type Lookup (GAMEGENRE)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Action', '', '', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Adventure', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Compilation', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Exploration', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Fantasy', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Fighting', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'FlightSimulation', 'Flight Simulation', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Futuristic', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Horror', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'MMO', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'MusicSimulation', 'Music Simulation', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Mystery', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Platform', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Puzzle', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Race', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'RPG', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Shooting', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Sports', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Stealth', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Strange', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Strategy', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', NULL, 'Survival', '', '', 'N' );

#
# Attribute Type Lookup (GAMEFLOW)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEFLOW', 0, 'RealTime', 'Real-Time', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEFLOW', 0, 'TurnBased', 'Turn-Based', '', 'N' );

#
# Attribute Type Lookup (AUDIO_LANG)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH', 'English', 'usa.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'FINNISH', 'Finnish', 'finnish.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'FRENCH', 'French', 'french.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN', 'German', 'german.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ITALIAN', 'Italian', 'italian.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'SPANISH', 'Spanish', 'spanish.gif', 'N' );

#
# Attribute Type Lookup (TEXT_LANG)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TEXT_LANG', NULL, 'ENGLISH', 'English', 'usa.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TEXT_LANG', NULL, 'FINNISH', 'Finnish', 'finnish.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TEXT_LANG', NULL, 'FRENCH', 'French', 'french.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TEXT_LANG', NULL, 'GERMAN', 'German', 'german.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TEXT_LANG', NULL, 'ITALIAN', 'Italian', 'italian.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'TEXT_LANG', NULL, 'SPANISH', 'Spanish', 'spanish.gif', 'N' );

#
# Attribute Type Lookup (SUBTITLES)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', NULL, 'ENGLISH', 'English', 'usa.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', NULL, 'ENG_H_IMP', 'English (Hearing Impaired)', 'english.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', NULL, 'FRENCH', 'French', 'french.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', NULL, 'GERMAN', 'German', 'german.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', NULL, 'ITALIAN', 'Italian', 'italian.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', NULL, 'SPANISH', 'Spanish', 'spanish.gif', 'N' );

#
# Attribute Type Lookup (GAMEREGION)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'AU', 'Australia', 'australia.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'CA', 'Canada', 'canada.png', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'EU_DE', 'Germany', 'german.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'EU_FR', 'France', 'french.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'EU_UK', 'England', 'english.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'JP', 'Japan', 'japanese.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'US', 'United States', 'usa.gif', 'Y' );

#
# Attribute Type Lookup (GAMEPERSP)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEPERSP', 0, 'FirstPerson', 'First Person', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEPERSP', 0, 'Isometric', 'Isometric', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEPERSP', 0, 'Platform', 'Platform', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEPERSP', 0, 'SideScrolling', 'Side-Scrolling', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEPERSP', 0, 'ThirdPerson', 'Third Person', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEPERSP', 0, 'TopDown', 'Top-Down', '', 'N' );

#
# Attribute Type Lookup (GAMERATING)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 0, 'EC', 'Early Childhood', 'game/game_ec.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 1, 'K-A', 'Kids to Adults', 'game/game_ka.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 2, 'E', 'Everyone', 'game/game_e.gif', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 3, 'E10', 'Everyone 10+', 'game/game_e10.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 4, 'T', 'Teen', 'game/game_t.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 5, 'M', 'Mature', 'game/game_m.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 6, 'AO', 'Adults Only', 'game/game_ao.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 7, 'RP', 'Rating Pending', 'game/game_rp.gif', 'N' );

#
# Attribute Type Lookup (GAME_MEDIA)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_MEDIA', 0, 'BD', 'Blu-Ray Disc', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_MEDIA', 0, 'Cartridge', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_MEDIA', 0, 'CD', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_MEDIA', 0, 'DVD', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_MEDIA', 0, 'GCGD', 'GameCube Game Disc', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_MEDIA', 0, 'GD-ROM', 'Dreamcast Game Disc', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAME_MEDIA', 0, 'UMD', '', '', 'N' );
