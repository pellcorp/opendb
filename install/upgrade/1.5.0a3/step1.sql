# remove 'special' upgrade permission in favour of listing all users who
# do not themselves possess the admin change user permission.
DELETE FROM s_permission WHERE permission_name = 'PERM_CHANGE_USER';
DELETE FROM s_role_permission WHERE permission_name = 'PERM_CHANGE_USER';

ALTER TABLE s_role ADD signup_avail_ind VARCHAR(1) NOT NULL DEFAULT 'Y';

UPDATE s_role SET signup_avail_ind = 'N' WHERE role_name IN('ADMINISTRATOR', 'PUBLICACCESS');