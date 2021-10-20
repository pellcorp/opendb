<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001,2013 by Jason Pell

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");

include_once("./lib/chart.php");
include_once("./lib/widgets.php");
include_once("./lib/statsdata.php");
include_once("./lib/user.php");
include_once("./lib/item.php");
include_once("./lib/review.php");
include_once("./lib/item_type.php");
include_once("./lib/item_attribute.php");
include_once("./lib/item.php");

function get_item_type_totals_rs() {
	$item_type_rs = array();
	$itemresults = fetch_item_type_rs();
	if ($itemresults) {
		while ($item_type_r = db_fetch_assoc($itemresults)) {
			$type_total_items = fetch_item_instance_cnt($item_type_r['s_item_type']);
			if ($type_total_items > 0) {
				$item_type_r['count'] = $type_total_items;
				$item_type_rs[] = $item_type_r;
			}
		}
		db_free_result($itemresults);
	}
	return $item_type_rs;
}

function build_review_stats() {
	$avgrate = fetch_review_rating();
	if ($avgrate > 0) {
		echo ("<h3>" . get_opendb_lang_var('review_stats') . "</h3>");

		echo ("<dl class=\"reviewStats\">");

		$num_review = fetch_review_cnt();

		echo ("<dt>" . get_opendb_lang_var('review(s)') . "</dt>");
		echo ("<dd>" . $num_review . "</dd>");

		echo ("<dt>" . get_opendb_lang_var('average_rating') . "</dt>");
		$attribute_type_r = fetch_attribute_type_r("S_RATING");
		echo ("<dd>" . get_display_field($attribute_type_r['s_attribute_type'], NULL, 'review()', $avgrate, FALSE) . "</dd>");

		echo ("</dl>");
	}
}

function build_item_stats() {
	echo ("<h3>" . get_opendb_lang_var('item_stats') . "</h3>");

	echo ("<table class=\"itemStats\">");

	echo ("<tr class=\"navbar\">");
	echo ("<th>" . get_opendb_lang_var('owner') . "</th>");

	$results = fetch_status_type_rs();
	if ($results) {
		while ($status_type_r = db_fetch_assoc($results)) {
			$status_type_r['total'] = 0;
			$status_type_rs[] = $status_type_r;
		}
		db_free_result($results);
	}

	if (is_not_empty_array($status_type_rs)) {
		reset($status_type_rs);
		foreach ( $status_type_rs as $status_type_r ) {
			echo ("<th>" . theme_image($status_type_r['img'], $status_type_r['description'], "s_status_type") . "</th>");
		}
	}

	echo ("<th>" . get_opendb_lang_var('total') . "</th>");

	echo ("</tr>");

	$result = fetch_user_rs(PERM_ITEM_OWNER);
	if ($result) {
		$toggle = TRUE;

		// Totals.
		$sum_total = 0;

		while ($user_r = db_fetch_assoc($result)) {
			$user_name = get_opendb_lang_var('user_name', array('fullname' => $user_r['fullname'], 'user_id' => $user_r['user_id']));

			echo ("<tr class=\"data\"><th>");
			if (is_user_granted_permission(PERM_VIEW_USER_PROFILE)) {
				echo ("<a href=\"user_profile.php?uid=" . $user_r['user_id'] . "\">" . $user_name . "</a>");
			} else {
				echo ($user_name);
			}
			echo ("</th>");

			$num_total = 0;
			if (is_not_empty_array($status_type_rs)) {
				reset($status_type_rs);
				foreach ( $status_type_rs as $key => $status_type_r ) {
					$status_total = fetch_owner_s_status_type_item_cnt($user_r['user_id'], $status_type_r['s_status_type']);
					$status_type_rs[$key]['total'] += $status_total;

					echo ("\n<td>");
					if ($status_total > 0)
						echo ("<a href=\"listings.php?owner_id=" . $user_r['user_id'] . "&s_status_type=" . $status_type_r['s_status_type'] . "&order_by=title&sortorder=ASC\">$status_total</a>");
					else
						echo ("-");
					echo ("</td>");

					$num_total += $status_total;
				}
				$sum_total += $num_total;

				echo ("\n<td>" . $num_total . "</td>");
			}

			echo ("</tr>");
		}//while ($user_r = db_fetch_assoc($result))
		db_free_result($result);

		echo ("<tr class=\"data totals\"><th>" . get_opendb_lang_var('totals') . "</th>");

		if (is_not_empty_array($status_type_rs)) {
			reset($status_type_rs);
			foreach ( $status_type_rs as $status_type_r ) {
				echo ("<td>" . $status_type_r['total'] . "</td>");
			}
			echo ("<td>" . $sum_total . "</td>");
		}

		echo ("</tr>");
	}
	echo ("</table>");

}

function build_borrower_stats() {
	if (get_opendb_config_var('borrow', 'enable') !== FALSE) {
		echo ("<h3>" . get_opendb_lang_var('borrow_stats') . "</h3>");

		echo ("<table class=\"itemStats\">");

		echo ("<tr class=\"navbar\">");
		echo ("<th>" . get_opendb_lang_var('owner') . "</th>");
		echo ("<th>" . theme_image('reserved.gif', get_opendb_lang_var('reserved'), "borrowed_item") . "</th>");
		echo ("<th>" . theme_image('borrowed.gif', get_opendb_lang_var('borrowed'), "borrowed_item") . "</th>");
		echo ("</tr>");

		$result = fetch_user_rs(PERM_ITEM_OWNER);
		if ($result) {
			$toggle = TRUE;

			// Totals.
			$sum_loaned = 0;
			$sum_reserved = 0;

			while ($user_r = db_fetch_assoc($result)) {
				$user_name = get_opendb_lang_var('user_name', array('fullname' => $user_r['fullname'], 'user_id' => $user_r['user_id']));

				echo ("<tr class=\"data\"><th>");
				if (is_user_granted_permission(PERM_VIEW_USER_PROFILE)) {
					echo ("<a href=\"user_profile.php?uid=" . $user_r['user_id'] . "\">" . $user_name . "</a>");
				} else {
					echo ($user_name);
				}
				echo ("</th>");

				$reserved_total = fetch_owner_reserved_item_cnt($user_r['user_id']);
				$sum_reserved += $reserved_total;

				echo ("\n<td>");
				if ($reserved_total > 0)
					echo ($reserved_total);
				else
					echo ("-");
				echo ("</td>");

				$loan_total = fetch_owner_borrowed_item_cnt($user_r['user_id']);
				$sum_loaned += $loan_total;

				echo ("\n<td>");
				if ($loan_total > 0)
					echo ($loan_total);
				else
					echo ("-");
				echo ("</td>");

				echo ("</tr>");
			}//while ($user_r = db_fetch_assoc($result))
			db_free_result($result);

			echo ("<tr class=\"data totals\"><th>" . get_opendb_lang_var('totals') . "</th>");

			// sum loaned.
			if (get_opendb_config_var('borrow', 'enable') !== FALSE) {
				echo ("<td>" . $sum_reserved . "</td>");
				echo ("<td>" . $sum_loaned . "</td>");
			}

			echo ("</tr>");
		}
		echo ("</table>");
	}
}

if (is_site_enabled()) {
	if (is_opendb_valid_session() || is_site_public_access()) {
		if (is_user_granted_permission(PERM_VIEW_STATS)) {
			if ($HTTP_VARS['op'] == 'graph') {
				do_stats_graph($HTTP_VARS);
			} else {
				echo _theme_header(get_opendb_lang_var('statistics'));
				echo ("<h2>" . get_opendb_lang_var('statistics') . "</h2>");

				build_review_stats();
				build_borrower_stats();
				build_item_stats();

				$item_type_rs = get_item_type_totals_rs();
				if (count($item_type_rs) > 0) {
					echo ("<div class=\"tabContainer\">");
					echo ("<ul class=\"tabMenu\" id=\"tab-menu\">");

					echo ("<li id=\"menu-breakdown\" class=\"first activeTab\" onclick=\"return activateTab('breakdown', 'tab-menu', 'tab-content', 'activeTab', 'tabContent')\">" . get_opendb_lang_var('overview') . "</li>");

					reset($item_type_rs);
					foreach ( $item_type_rs as $item_type_r ) {
						echo ("<li id=\"menu-${item_type_r['s_item_type']}\" onclick=\"return activateTab('${item_type_r['s_item_type']}', 'tab-menu', 'tab-content', 'activeTab', 'tabContent')\">${item_type_r['s_item_type']}</li>");
					}
					echo ("</ul>");

					$graphCfg = _theme_graph_config();
					$chartLib = get_opendb_config_var('stats', 'chart_lib');
					if ($chartLib != 'legacy') {
						$widthHeightAttribs = "width=\"${graphCfg['width']}\" height=\"${graphCfg['height']}\"";
					}

					echo ("<div id=\"tab-content\">");

					echo ("\n<div class=\"tabContent\" id=\"breakdown\">");
					echo ("<ul class=\"graph\">");

					echo ("<li>" . render_chart_image('item_ownership') . "</li>");
					echo ("<li>" . render_chart_image('item_types') . "</li>");
					echo ("<li>" . render_chart_image('categories') . "</li>");
					echo ("</ul>");
					echo ("</div>");

					reset($item_type_rs);
					foreach ( $item_type_rs as $item_type_r) {
						echo ("\n<div class=\"tabContentHidden\" id=\"${item_type_r['s_item_type']}\">");
						echo ("<h3>" . get_opendb_lang_var('itemtype_breakdown', array('desc' => $item_type_r['description'], 's_item_type' => $item_type_r['s_item_type'], 'total' => $item_type_r['count'])) . "</h3>");
						echo ("<ul class=\"graph\">");
						echo ("<li>" . render_chart_image('item_type_ownership', $item_type_r['s_item_type']) . "</li>");
						echo ("<li>" . render_chart_image('item_type_category', $item_type_r['s_item_type']) . "</li>");
						echo ("</ul>");
						echo ("</div>\n");
					}
				}

				echo ("</div>");
			}

			echo _theme_footer();
		} else {
			opendb_not_authorised_page(PERM_VIEW_STATS, $HTTP_VARS);
		}
	} else {
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
} else { //if(is_site_enabled())
	opendb_site_disabled();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
