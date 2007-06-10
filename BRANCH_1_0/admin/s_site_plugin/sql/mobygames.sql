#########################################################
# OpenDb 1.0 moby.com (mobygames) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url )VALUES ( 'mobygames', 'mobygames', 'moby.com', 'moby.gif', 'Information and reviews of lots of computer games.', 'http://www.mobygames.com', 25, 'http://www.mobygames.com/game/{mgpltfrmid}/{mobygameid}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'mobygames', 'title', 1, '', 'Title Search', 'text', '', '{ifdef(alt_title, \'{alt_title} / \')}{title}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'mobygames', 'mgpltfrmid', 2, '', 'MobyGame Platform ID', 'text', '', '{mgpltfrmid}' );
INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'mobygames', 'mobygameid', 2, '', 'MobyGame ID', 'text', '', '{mobygameid}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'mobygames', '*', 'GAME', 1, 'More Info', 'http://www.mobygames.com/game/{mgpltfrmid}/{mobygameid}', '' );

#
# Site Plugin System Attribute Type Map
#

INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'mobygames', '*', 'GAME', 'alt_title', 'ALT_TITLE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'mobygames', '*', 'GAME', 'description', 'GAME_PLOT', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'mobygames', '*', 'GAME', 'genre', 'GAMEGENRE', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'mobygames', '*', 'GAME', 'mgpltfrmid', 'GAMESYSTEM', 'Y' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'mobygames', '*', 'GAME', 'perspective', 'COMMENTS', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'mobygames', '*', 'GAME', 'publisher', 'GAMEPBLSHR', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'mobygames', '*', 'GAME', 'release_date', 'GAMEPBDATE', 'N' );
INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'mobygames', '*', 'GAME', 'title', 'S_TITLE', 'N' );

#
# Site Plugin System Attribute Type Lookup Map
#

INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMERATING', '89', 'EC' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMERATING', '90', 'E' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMERATING', '91', 'K-A' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMERATING', '92', 'T' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMERATING', '93', 'M' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMERATING', '94', 'AO' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMERATING', '95', 'RP' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'dos', 'PCCDROM' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'dreamcast', 'DREAMCAST' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'gameboy', 'GAMEBOY' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'gameboy-advance', 'GAMEBOYADVANCE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'gamecube', 'GAMECUBE' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'n64', 'N64' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'playstation', 'PS1' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'ps2', 'PS2' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'windows', 'PCCDROM' ); 
INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) VALUES ( 'mobygames', 'GAMESYSTEM', 'xbox', 'XBOX' ); 

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

#
# Site Plugin Attribute Type(s)
#

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'MOBYGAMEID', 'MobyGames game ID', 'MobyGames ID', 'hidden', 'hidden', '', 'mobygames' );
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'MGPLTFRMID', 'MobyGames Platform ID', 'MobyGames Platform ID', 'hidden', 'hidden', '', 'mobygames' );

#
# Site Plugin Item Attribute Type Relationship(s)
#

INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'GAME', 'MGPLTFRMID',  0, '', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'GAME', 'MOBYGAMEID',  0, '', 'N' );
