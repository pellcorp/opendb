
# patch to install new gamerating E10 
DELETE FROM s_attribute_type_lookup WHERE s_attribute_type IN('GAMERATING', 'GAMESYSTEM');
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 0, 'EC', 'Early Childhood', 'game/game_ec.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 1, 'K-A', 'Kids to Adults', 'game/game_ka.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 2, 'E', 'Everyone', 'game/game_e.gif', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 3, 'E10', 'Everyone 10+', 'game/game_e10.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 4, 'T', 'Teen', 'game/game_t.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 5, 'M', 'Mature', 'game/game_m.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 6, 'AO', 'Adults Only', 'game/game_ao.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'GAMERATING', 7, 'RP', 'Rating Pending', 'game/game_rp.gif', 'N' );

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
