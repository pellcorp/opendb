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
 * 
 * Search for 'Rambo' to return a list of various titles.
        Search for '12 Angry Men' to get an exact title match.
        Search for 'faddsda' to trigger a search error.
 */

// use local config file instead of the one included with imdb, so we can disable caching, etc.
define(IMDBPHP_CONFIG, './lib/site/imdb.config.php');

include_once("./lib/SitePlugin.class.php");
include_once("./lib/site/imdbphp2/imdb.class.php");
include_once("./lib/site/imdbphp2/imdbsearch.class.php");

class imdbphp extends SitePlugin {
	var $results;
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (strlen($search_vars_r['imdb_id']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('imdb_id' => $search_vars_r['imdb_id']));
			return TRUE;
		} else {
			if ($this->_site_plugin_conf_r['title_search_faster_alternate'] != 'TRUE') {
				$imdbsearch = new imdbsearch();
				$imdbsearch->setsearchname($search_vars_r['title']);
				$this->results = $imdbsearch->results();

				if (is_array($this->results)) {
					foreach ($this->results as $res_id => $res) {
						if (starts_with($res->photo(), '/images/nopicture')) {
							$image = NULL;
						} else {
							$image = $res->photo();
						}
						$this->addListingRow($res->title(), $image, $res->year(), array('imdb_id' => $res->imdbid(), 'res_id' => $res_id));
					}
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				$pageBuffer = $this->fetchURI("http://www.imdb.com/find?q=" . rawurlencode(strtolower($search_vars_r['title'])) . ";more=tt");
				if (strlen($pageBuffer) > 0) {
					if (preg_match("!http://us.imdb.com/title/tt([^/]+)/!", $this->getFetchedURILocation(), $regs)) {
						$this->addListingRow(NULL, NULL, NULL, array('imdb_id' => $regs[1]));
						return TRUE;
					} else {
						//<h1 class="findHeader">Results for <span class="findSearchTerm">"prometheus;more=tt"</span></h1>
						if (strpos($pageBuffer, "<h1 class=\"findHeader\">Results for <span class=\"findSearchTerm\">") !== FALSE) {
							//<tr class="findResult odd"> 
							//<td class="primary_photo"> <a href="/title/tt1256632/?ref_=fn_al_tt_2" ><img src="http://ia.media-imdb.com/images/G/01/imdb/images/nopicture/32x44/film-3119741174._V397576370_.png" /></a> </td> 
							//<td class="result_text"> <a href="/title/tt2411516/?ref_=fn_al_tt_6" >Prometheus Trap</a> (2012) </td>
							//</tr>

							if (($idx = strpos($pageBuffer, "<table class=\"findList\">")) !== FALSE) {
								$pageBuffer = substr($pageBuffer, $idx);
								$idx = strpos($pageBuffer, "</table>");
								$pageBuffer = substr($pageBuffer, 0, $idx);

								if (preg_match_all("/<tr class=\"findResult[^\"]*\">.*?<\/tr>/m", $pageBuffer, $matches)) {
									for ($i = 0; $i < count($matches[0]); $i++) {
										if (preg_match("/<td class=\"primary_photo\">[\s]*<a href=\"[^\"]*\"[\s]*>[\s]*<img src=\"([^\"]*)\"[^>]*>[\s]*<\/a>[\s]*<\/td>/m", $matches[0][$i], $lmatches)) {
											$image = trim($lmatches[1]);
										}

										if (preg_match("/<td class=\"result_text\">[\s]*<a href=\"\/title\/tt([0-9]+)[^\"]*\"[\s]*>([^<]+)<\/a>[\s]*([^<]+)</m", $matches[0][$i], $lmatches)) {
											$imdb_id = trim($lmatches[1]);
											$title = trim($lmatches[2]) . " " . trim($lmatches[3]);
										}

										$this->addListingRow($title, $image, NULL, array('imdb_id' => $imdb_id));
									}
								}
							}

							$pageBuffer = NULL;
							return TRUE;
						}
					}

					//else no results found
					return TRUE;
				} else {
					return FALSE;
				}
			}
		}
	}

	function queryItem($search_attributes_r, $s_item_type) {
		if (is_array($this->results)) {
			$imdb = $this->results[$search_attributes_r['res_id']];
			unset($this->results);
		} else {
			$imdb = new imdb($search_attributes_r['imdb_id']);
		}

		// WTF?
		$imdb->imdb_utf8recode = get_opendb_config_var('themes', 'charset') == 'utf-8' ? TRUE : FALSE;

		$this->addItemAttribute('title', $imdb->title());
		$this->addItemAttribute('year', $imdb->year());
		foreach ($imdb->alsoknow() as $alt_title) {
			$this->addItemAttribute('alt_title', array($alt_title['country'] => $alt_title['title']));
		}
		if ($imdb->photo())
			$this->addItemAttribute('imageurl', $imdb->photo());
		foreach ($imdb->director() as $person) {
			$this->addItemAttribute('director', $person['name']);
		}
		foreach ($imdb->producer() as $person) {
			if (stristr($person['role'], 'executive'))
				$this->addItemAttribute('exproducer', $person['name']);
			else
				$this->addItemAttribute('producer', $person['name']);
		}
		foreach ($imdb->writing() as $person) {
			$this->addItemAttribute('writer', $person['name']);
		}
		foreach ($imdb->composer() as $person) {
			$this->addItemAttribute('composer', $person['name']);
		}
		foreach ($imdb->cast() as $person) {
			$this->addItemAttribute('actors', $person['name']);
		}
		$this->addItemAttribute('genre', $imdb->genres());
		$this->addItemAttribute('imdbrating', $imdb->rating());
		$this->addItemAttribute('run_time', $imdb->runtime());
		$this->addItemAttribute('audio_lang', $imdb->languages());
		$this->addItemAttribute('audio_lang', $imdb->sound());
		
		foreach ($imdb->mpaa() as $country => $rating) {
			$country = strtolower($country);
			$this->addItemAttribute($country . '_age_rating', $rating);
		}
		$age_certification_codes_r = $this->getConfigValue('age_certification_codes');
		if (!is_array($age_certification_codes_r) && strlen($age_certification_codes_r) > 0) // single value
			$age_certification_codes_r = array($age_certification_codes_r);
		if (is_array($age_certification_codes_r)) {
			// get a single value for the age rating depending on the users settings
			reset($age_certification_codes_r);
			foreach ($age_certification_codes_r as $country) {
				$country = strtolower($country);

				$ageRating = $this->getItemAttribute($country . '_age_rating');
				//				echo('<pre>');
				//				print_r($ageRating);
				//				echo('</pre>');

				if ($ageRating !== FALSE) {
					$this->addItemAttribute('age_rating', $ageRating);
					break; // found it!
				}
			}
		}
		$this->addItemAttribute('plot', $imdb->plot());
		/** aspect ratio is not supported by imdbphp yet.
		 * <div class="info">
		 <h5>Aspect Ratio:</h5>
		 2.35 : 1 <a class="tn15more inline" href="/rg/title-tease/aspect/title/tt0083944/technical">more</a>
		 </div>
		
		if(preg_match("!<h5>Aspect Ratio:</h5>[\s]*([0-9\.]*)[\s]*:[\s]*([0-9\.]*)!", $pageBuffer, $matches))
		{
		    $this->addItemAttribute('ratio', $matches[1]);
		}*/

		return TRUE;
	}
}
?>
