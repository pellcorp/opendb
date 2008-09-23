###################################################
# Multiple Age Ratings
#
# Author: Matthew Barbour <sanmadjack@gmail.com>
###################################################

UPDATE s_attribute_type SET input_type = 'checkbox_grid' WHERE s_attribute_type = 'AGE_RATING';
UPDATE s_attribute_type SET input_type = 'checkbox_grid' WHERE s_attribute_type = 'GAMERATING';
