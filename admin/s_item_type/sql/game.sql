#########################################################
# OpenDb 1.1.0 'GAME' Item Type
#########################################################

#
# Item Type
#
INSERT INTO s_item_type ( s_item_type, description, image ) VALUES ( 'GAME', 'Console / PC Game', 'joystick.gif' );

#
# Attributes (non-core)
#
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'IMAGEURL', 'Item Image URL', 'Image', 'url', '50', '*', 'IMAGE', NULL, NULL, 'hidden',NULL, NULL, NULL, NULL, NULL, 'N', 'Y', 'N', 'N', 'IMAGE', NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAME_PLOT', 'Description of game', 'Description', 'textarea', '50', '5', NULL, NULL, NULL, '%value%',NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEPBLSHR', 'Publisher', 'Publisher', 'text', '50', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMESYSTEM', 'Game System', 'System', 'radio_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEGENRE', 'Game Genre', 'Genre', 'checkbox_grid', '%display%', 'VERTICAL', NULL, NULL, NULL, 'category','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', 'CATEGORY', NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEREGION', 'Game Region', 'Region', 'single_select', '%display%', NULL, NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMEPBDATE', 'Date Published', 'Published', 'filtered', '10', '10', '0-9/', '%field% (DD/MM/YYYY)', NULL, '%value%',NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'NO_PLAYERS', 'Number Players', 'Players', 'value_select', '1,2,3,4,5,6,7,8,9,10', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'CONTROLLER', 'Special Controller', 'Special Controller', 'text', '50', NULL, NULL, NULL, NULL, '%value%',NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'GAMERATING', 'ESRB Video and Computer Game Rating', 'Rating', 'radio_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'FEATURES', 'Game Extra Features Details', 'Features', 'textarea', '50', '5', NULL, NULL, NULL, 'list','ticks', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'COMMENTS', 'Extra Comments', 'Comments', 'textarea', '50', '5', NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);

#
# Item Attribute Relationships
#
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_TITLE', 1, NULL, 'N', 'Y', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'IMAGEURL', 2, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAME_PLOT', 10, NULL, 'N', 'N', 'Y', 'Y' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEPBLSHR', 20, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMESYSTEM', 30, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEGENRE', 40, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEREGION', 43, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMEPBDATE', 45, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'NO_PLAYERS', 50, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'CONTROLLER', 60, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'GAMERATING', 65, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'FEATURES', 70, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'COMMENTS', 70, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_STATUS', 253, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_STATCMNT', 254, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'GAME', 'S_DURATION', 255, NULL, 'N', 'N', 'N', 'N' );

#
# Attribute Type Lookup (GAMESYSTEM)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 1, 'PS1', 'Playstation', 'ps1.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 2, 'PS2', 'Playstation 2', 'ps2.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 3, 'DREAMCAST', 'Dreamcast', 'dreamcast.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 4, 'GAMEBOY', 'Gameboy', 'gameboy.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 5, 'GAMEBOYADVANCE', 'Gameboy ADVANCE', 'gba.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 6, 'N64', 'Nintendo 64', 'n64.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 7, 'GAMECUBE', 'Game Cube', 'gamecube.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 8, 'XBOX', 'Xbox', 'xbox.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 9, 'MACCDROM', 'Mac-Cdrom', 'apple.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMESYSTEM', 9, 'PCCDROM', 'PC-Cdrom', 'pc-cdrom.gif', 'N' );

#
# Attribute Type Lookup (GAMEGENRE)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Action', '', '', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Adventure', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Exploration', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Fighting', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'FlightSimulation', 'Flight Simulation', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Futuristic', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'MusicSimulation', 'Music Simulation', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Platform', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Puzzle', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Race', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'RPG', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Shooting', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Sports', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEGENRE', 0, 'Strange', '', '', 'N' );

#
# Attribute Type Lookup (GAMEREGION)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'EU_DE', 'Germany', 'german.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'EU_FR', 'France', 'french.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'EU_UK', 'England', 'english.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'JP', 'Japan', 'japanese.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMEREGION', 0, 'US', 'United States', 'usa.gif', 'N' );

#
# Attribute Type Lookup (GAMERATING)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 0, 'EC', 'Early Childhood', 'game/game_ec.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 1, 'K-A', 'Kids to Adults', 'game/game_ka.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 2, 'E', 'Everyone', 'game/game_e.gif', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 3, 'T', 'Teen', 'game/game_t.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 4, 'M', 'Mature', 'game/game_m.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 5, 'AO', 'Adults Only', 'game/game_ao.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 6, 'RP', 'Rating Pending', 'game/game_rp.gif', 'N' );
