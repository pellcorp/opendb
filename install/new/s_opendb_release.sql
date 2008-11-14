#
# System OpenDb Release table
#
# upgrade_step - set to 0 by default, which means no upgrade steps
# have been performed.  Once all upgrade steps have been completed,
# the upgrade step column should be cleared.

# in case an old version of table exists, recreate
DROP TABLE IF EXISTS s_opendb_release;

CREATE TABLE s_opendb_release(
	sequence_number 	INTEGER(10) UNSIGNED NOT NULL auto_increment,
	release_version		VARCHAR(50) NOT NULL,
	description			VARCHAR(100) NOT NULL,
	upgrade_step		TINYINT(2) UNSIGNED DEFAULT 0,
	upgrade_step_part	TINYINT(2) UNSIGNED DEFAULT NULL,
	update_on			TIMESTAMP(14) NOT NULL,
	PRIMARY KEY ( sequence_number ),
	UNIQUE KEY ( release_version )
) TYPE=MyISAM COMMENT='System OpenDb Release table';
