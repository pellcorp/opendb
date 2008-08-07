###################################################
# Multiple Age Ratings
#
# Author: Matthew Barbour <sanmadjack@gmail.com>
###################################################

UPDATE `s_attribute_type` SET `input_type` = 'checkbox_grid' WHERE CONVERT( `s_attribute_type`.`s_attribute_type` USING utf8 ) = 'AGE_RATING' LIMIT 1 ;
UPDATE `s_attribute_type` SET `input_type` = 'checkbox_grid' WHERE CONVERT( `s_attribute_type`.`s_attribute_type` USING utf8 ) = 'GAMERATING' LIMIT 1 ;
