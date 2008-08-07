###################################################
# Multiple Game Systems
#
# Author: Matthew Barbour <sanmadjack@gmail.com>
###################################################

UPDATE `s_attribute_type` SET `input_type` = 'checkbox_grid' WHERE CONVERT( `s_attribute_type`.`s_attribute_type` USING utf8 ) = 'GAMESYSTEM' LIMIT 1 ;

