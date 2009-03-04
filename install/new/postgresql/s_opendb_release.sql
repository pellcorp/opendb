#
# System OpenDb Release table
#
# upgrade_step - set to 0 by default, which means no upgrade steps
# have been performed.  Once all upgrade steps have been completed,
# the upgrade step column should be cleared.

# in case an old version of table exists, recreate
DROP TABLE IF EXISTS s_opendb_release;

CREATE TABLE s_opendb_release(
	sequence_number    SERIAL,
	release_version	   VARCHAR(50) NOT NULL UNIQUE,
	description        VARCHAR(100) NOT NULL,
	upgrade_step       SMALLINT DEFAULT 0,
	upgrade_step_part  SMALLINT DEFAULT NULL,
	update_on          TIMESTAMP,
	PRIMARY KEY ( sequence_number )
) ;
COMMENT ON TABLE s_opendb_release IS 'System OpenDb Release table';
