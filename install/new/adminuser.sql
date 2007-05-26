# --------------------------------------------------------
# Admin user installation
# --------------------------------------------------------
INSERT INTO user (user_id, fullname, pwd, type) VALUES ( 'admin', 'Admin User', '21232f297a57a5a743894a0e4a801fc3', 'A');
#
# Insert User Email address
# 
INSERT INTO user_address (sequence_number, user_id, s_address_type, start_dt, end_dt)
VALUES (1, 'admin', 'EMAIL', now(), NULL);

INSERT INTO user_address_attribute (ua_sequence_number, s_attribute_type, order_no, attribute_val)
VALUES (1, 'EMAIL_ADDR', 1, 'opendb@iamvegan.net');
