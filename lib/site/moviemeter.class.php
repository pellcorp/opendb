<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001,2013 by Jason Pell
    Moviemeter plugin by Bas ter Vrugt

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

    9-9-08 : fixed search results (moviemeter site changed)

 */
include_once("./lib/SitePlugin.class.php");

class moviemeter extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (strlen($search_vars_r['moviemeter_id']) > 0) {
			$pageBuffer = $this->fetchURI("http://www.moviemeter.nl/film/" . $search_vars_r['moviemeter_id']);

			if (strlen($pageBuffer) > 0)
				$this->addListingRow(NULL, NULL, NULL, array('moviemeter_id' => $search_vars_r['moviemeter_id']));

			return TRUE;
		} else {
			$FirstSearch = $this->fetchURI("http://www.moviemeter.nl/film/search/" . rawurlencode($search_vars_r['title']));
			//this will display a page with some ajax lib/javascript redirects and a secret hash code.
			//first get the hash code
			$regx = "/search.php\?hash=((.*))\&qs=1/";
			$matchCount = preg_match($regx, $FirstSearch, $matches);
			if ($matchCount == 0) {
				return FALSE;
			}
			$SearchHash = $matches[1];
			//search again to get results
			$pageBuffer = $this->fetchURI("http://www.moviemeter.nl/calls/quicksearch.php?hash=" . $SearchHash . "&search=" . rawurlencode($search_vars_r['title']));
		}

		if (strlen($pageBuffer) > 0) {
			if (preg_match_all('/film;;([0-9]{1,6});;(.*) %28([0-9]+)%/', $pageBuffer, $matches)) {
				for ($i = 0; $i < count($matches[1]); $i++) {
					$movieid = $matches[1][$i];
					//url can be:
					//http://www.moviemeter.nl/images/covers/39000/39008.jpg
					//but also
					//http://www.moviemeter.nl/images/covers/1000/1375.jpg
					if (strlen($movieid) < 4) {
						$imagedir = "0";
					} else {
						$imagedir = substr($movieid, 0, -3) . "000";
					}
					$thumbimg = "http://www.moviemeter.nl/images/covers/" . $imagedir . "/" . $movieid . ".jpg";
					;

					$title = urldecode($matches[2][$i] . ' (' . $matches[3][$i] . ')');
					$this->addListingRow($title, $thumbimg, NULL, array('moviemeter_id' => $matches[1][$i]));
				}
				return TRUE;
			} else {
				// no matches
				return TRUE;
			}
		} else {
			// no matches(this is a JSON result page that can be 0 if no results)
			return TRUE;
		}
	}

	function queryItem($search_attributes_r, $s_item_type) {
		$pageBuffer = $this->fetchURI("http://www.moviemeter.nl/film/" . $search_attributes_r['moviemeter_id']);
		//print_r($pageBuffer);
		// no sense going any further here.
		if (strlen($pageBuffer) == 0)
			return FALSE;

		//title year
		if (preg_match("!<head><title>([^\<]*)\(([0-9]*)\) - MovieMeter.nl<!", $pageBuffer, $matches)) {
			$this->addItemAttribute('title', $matches[1]);
			$this->addItemAttribute('year', $matches[2]);
		}

		//Alternate titles Alternatieve titels: Cyber Wars, Matrix Hunter&nbsp;</p>
		if (preg_match("!Alternatieve titels: (.*?)<!", $pageBuffer, $matches)) {
			$this->addItemAttribute('alt_title', explode(', ', $matches[1]));
		}

		//image
		if (preg_match("!<img.*?class=\"poster\".*?src=\"(.*?)\"!", $pageBuffer, $matches)) {
			if (starts_with($matches[1], 'http://'))
				$this->addItemAttribute('imageurl', $matches[1]);
			else
				$this->addItemAttribute('imageurl', 'http://' . $matches[1]);
		}

		//director
		if (preg_match("!geregisseerd door (.*)<br \/>met!", $pageBuffer, $matches)) {
			$this->addItemAttribute('director', $matches[1]);
		}

		//  genre, runtime <div id="film_info" class="film_info"><!-- geachte schrijvers van grabbers, scrapers en import scriptjes: gebruik svp de API - http://wiki.moviemeter.nl/index.php/API -->Singapore<br />Science-Fiction / Actie<br />102 minuten<br /><br />geregisseerd door <a href="http://www.moviemeter.nl/director/6132">Jian Hong Kuo</a><br />met Genevieve O'Reilly, Luoyong Wang en Kay Siu Lim<br /><br />In de nabije toekomst wordt iedereen's identiteit bijgehouden in de CyberLink database. Hier is geen ontkomen aan, behalve via illegale implantaten. Een jonge bountyhunter die op zoek gaat naar personen met deze implantaten ontmoet een politieagent, die vermoed dat de CyberLink voor minder legale doeleinden gebruikt wordt. Samen besluiten ze de waarheid te onthullen.</div></div><a name="messages"></a><br /><br /><div class="to_page entitypages" id="pages_top">&nbsp;</div><br /><div class="form_horizontal_divider"><p>&nbsp;</p></div><div class="forum_message_header"><div class="forum_message_header_user">gebruiker</div><div class="forum_message_header_message">bericht</div></div><div class="form_horizontal_divider"><p>&nbsp;</p></div><a name="0"></a><div class="forum_message_row1" id="message_482849"><div class="forum_message_user"><a href="http://www.moviemeter.nl/user/4270">Onderhond</a><br /><span class="subtext"></span><br /><img src="http://www.moviemeter.nl/images/avatars/4000/4270.jpg?date=1260983075" alt="avatar van Onderhond" /></div><div class="message_icons"><a href="http://www.moviemeter.nl/film/31586/message/quote/482849"><img src="http://www.moviemeter.nl/images/icon_quote_new.gif" style="width: 18px; height: 20px;" alt="quote" title="quote" /></a></div><div class="forum_message_message_date"><span class="subgray" title="Onderhond heeft dit bericht als persoonlijke recensie of mening gemarkeerd, en geeft deze film 1,5 sterren" style="margin-right: 8px;"><img src="http://www.moviemeter.nl/images/star_full_small.gif" style="margin-left: 2px; margin-bottom: -1px; margin-top: -2px;" /> 1,5</span><img src="http://www.moviemeter.nl/images/icon_minipost_new_nd.gif" alt="nieuw bericht" title="nieuw bericht" /> <span class="subgray log">[<a class="subgray" href="http://www.moviemeter.nl/film/31586/info/0#0">permalink</a>] geplaatst op 23 augustus 2005, 11:34 uur</span></div><div class="forum_message_message avatar_message">Nogal vreemde film.<br />

		if (preg_match("!<br \/>([^<]*)<br \/>([0-9]*) minuten!", $pageBuffer, $matches)) {
			//runtime
			$this->addItemAttribute('run_time', $matches[2]);
			//genre
			$genre = explode(" / ", $matches[1]);
			$this->addItemAttribute('genre', $genre);
		}

		//cast, plot
		if (preg_match("!geregisseerd door <a href=\"(.*)</a><br \/>met (de stemmen van )*([^\<]*)<br \/><br \/>([^\<]*)!", $pageBuffer, $matches)) {
			//cast
			$cast = explode(" en ", $matches[3]);
			$cast2 = explode(", ", $cast[0]);
			$cast2[] = $cast[1];
			$this->addItemAttribute('actors', $cast2);
			//plot
			$this->addItemAttribute('plot', $matches[4]);
		}

		return TRUE;
	}
}
?>
