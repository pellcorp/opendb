<?php
// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");

include_once("./lib/utils.php");
include_once("./lib/item.php");
include_once("./lib/user.php");
include_once("./lib/widgets.php");

// *****************************************************************************
// MAIN PROCESS
// *****************************************************************************
if (is_site_enabled()) {
    @set_time_limit(600);

    if (is_opendb_valid_session()) {
        switch ($HTTP_VARS['ajax_op']) {
            case 'possible-parents':
                // Get HTML select list of possible item parents.
                if (is_user_granted_permission(PERM_ITEM_OWNER) || is_user_granted_permission(PERM_ITEM_ADMIN)) {
                    echo json_encode(array('select' => format_item_parents_select($HTTP_VARS, fetch_item_r($HTTP_VARS['item_id']), $HTTP_VARS['parent_item_filter'])));
                }
                break;
            default:
                // invalid operation.
                echo json_encode(array('error' => 'invalid-op'));
                break;
        }
    } else {
        // invalid login, Ajax call returns error.
        echo json_encode(array('error' => 'invalid-login'));
    }
} else {
    echo json_encode(array('error' => 'site_disabled'));
}


// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
