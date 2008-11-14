# Bug 1885700 - remove OTHER item type group
DELETE FROM s_item_type_group WHERE s_item_type_group = 'OTHER';
DELETE FROM s_item_type_group_rltshp WHERE s_item_type_group = 'OTHER';

# Drop system indicator column as no longer makes any sense
ALTER TABLE s_item_type_group DROP system_ind;
