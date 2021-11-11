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

/**
 * Convenience method for stripping html from reviews in site plugins.
 *
 * TODO - make this a more generic function
 */
function &strip_review_html_formatting($review) {
	// some specific fucked up review formatting to deal with!!!
	$review = preg_replace("/<p>/i", "\n\n", $review);
	$review = preg_replace("/<br>/i", "\n", $review);
	$review = str_replace("&#149;", "*", $review);
	$review = str_replace("&#8220;", "\"", $review);
	$review = str_replace("&#8221;", "\"", $review);
	$review = str_replace("&#8217;", "'", $review);
	$review = str_replace("&#8211;", "-", $review);
	$review = str_replace("&#8212;", "-", $review);
	$review = str_replace("&ndash;", "-", $review);

	$review = trim(html_entity_decode(strip_tags($review), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1'));

	// some extra processing to try and remove as many duplicate reviews as possible
	$review = str_replace("\"", "", $review);
	$review = preg_replace("/[ \t]+/i", " ", $review);
	$review = str_replace("\n ", "\n", $review);

	return trim($review);
}

function parse_amazon_reviews($reviewPage) {
	$reviews = array();

	$start = strpos($reviewPage, "<b class=\"h1\">Editorial Reviews</b>");
	if ($start === FALSE)
		$start = strpos($reviewPage, "<b class=\"h1\">Reviews</b>");
	if ($start === FALSE)
		$start = strpos($reviewPage, "<b class=\"h1\">Produktbeschreibungen</b>");
	// todo fix: done 13th July 2008

	if ($start !== FALSE) {
		$start = strpos($reviewPage, "<div class=\"content\">", $start);
		if ($start !== FALSE) {
			$end = strpos($reviewPage, "</div>", $start);
			if ($end !== FALSE)
				$reviewPage = substr($reviewPage, $start, $end - $start);
			else
				$reviewPage = substr($reviewPage, $start);

			// If still something to parse.
			if (strlen($reviewPage) > 0) {
				//<b>The Times of London</b><br />
				if (preg_match_all("!<b>(.*?)</b>!m", $reviewPage, $matches)) {
					for ($i = 0; $i < count($matches[0]); $i++) {
						$block = NULL;

						$start = strpos($reviewPage, $matches[0][$i]);
						if ($start !== FALSE) {
							$start += strlen($matches[0][$i]);

							$start = strpos($reviewPage, "<br />", $start);
							if ($start != FALSE) {
								$start += strlen("<br />");

								$end = strpos($reviewPage, "<br />", $start);

								if ($end !== FALSE) {
									$block = substr($reviewPage, $start, $end - $start);
								}
							}
						}

						if (strlen($block) > 0) {
							// The author, is the first match, the actual review the second one.
							$author = trim(html_entity_decode(strip_tags($matches[1][$i]), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1'));

							if ($author != 'About the Author' && strpos($author, 'Special Features') === FALSE) { // a hack!
								// trim copyright notice.
								if (($copyidx = strpos($matches[2][$i], "-- <I>Copyright")) !== FALSE) {
									$block = trim(substr($block, 0, $copyidx));
								}

								$review = strip_review_html_formatting($block);

								if (strlen($author) > 0 && $author != 'Book Info' && $author != 'Product Description:' && $author != 'Amazon.co.uk Review' && author != 'Amazon.com' && $author != 'Synopsis') {
									$review .= "\n-- $author";
								}

								$reviews[] = $review;
							}
						}
					}
				}
			}
		}
	}

	return $reviews;
}

function parse_music_tracks($pageBuffer) {
	if (preg_match_all("!<tr class=\"[^\"]*\">[\s]*<td>[\s]*[0-9]+\.[\s]*([^<]+)</td>!", $pageBuffer, $matches)) {
		return $matches[1];
	}
	return NULL;
}

function parse_amazon_video_people($header, $pageBuffer) {
	$persons = NULL;

	$startidx = strpos($pageBuffer, "<li><b>$header");
	if ($startidx !== FALSE) {
		$startidx += strlen("<li><b>$header");
		$endidx = strpos($pageBuffer, "</li>", $startidx);
		if ($endidx !== FALSE) {
			$personBlock = substr($pageBuffer, $startidx, $endidx - $startidx);
			if (preg_match_all("/<a href=([^>]+)>([^<]+)<\/a>/", $personBlock, $matches)) {
				for ($i = 0; $i < count($matches[1]); $i++) {
					if (strpos($matches[2][$i], "See more") === FALSE) {
						$persons[] = trim(html_entity_decode(strip_tags($matches[2][$i]), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1'));
					}
				}
			}
		}
	}
	return $persons;
}

function parse_language_info($audio_lang, $audio_map) {
	@reset($audio_map);
	foreach ($audio_map as $key => $find_r) {
		$match = NULL;

		// all components of the $find_r have to be present for a match to occur
		$found = TRUE;
		foreach ($find_r as $srch) {
			if (strpos($audio_lang, $srch) !== FALSE) {
				if (strlen($match) > 0)
					$match .= ' ';

				$match .= $srch;
			} else {
				$found = FALSE;
				break;
			}
		}

		if ($found) {
			return $match;
		}
	}
	//else
	return NULL;
}

function unaccent($text) {
	if (get_opendb_config_var('themes', 'charset') == 'utf-8') {
		$text = utf8_decode($text);
	}
	// strip out characters that aren't valid in ISO-8859-1 (latin1)
	$text = preg_replace('/[^\x09\x0A\x0D\x20-\x7F\xC0-\xFF]/', '', $text);

	//Get the entities table into an array
	$trans = get_html_translation_table(HTML_ENTITIES);

	//Create two arrays, for accented and unaccented forms
	foreach ($trans as $literal => $entity) {
		//Don't contemplate other characters such as fractions, quotes etc.
		if (ord($literal) >= 192) {
			//Get 'E' from string '&Eaccute' etc.
			$replace[] = substr($entity, 1, 1);
			//Get accented form of the letter
			$search[] = $literal;
		}
	}
	return str_replace($search, $replace, $text);
}
?>
