<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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
    
    -- CHANGLOG --
        
    Version		Comments
    -------		--------
    0.81		initial 0.81 release
    0.81p7		Fix to remove debug info.
    0.81p8		Fix to parse audio_lang correctly.

    Mike E <mikee@saxicola.idps.co.uk>
    2008-07-17
    Changes for getting books based in ISBN13 number, getting of reviews and getting book images.
 */
include_once("lib/SitePlugin.class.inc");
include_once("lib/site/amazonutils.php");

class amazonuk extends SitePlugin {
	function amazonuk($site_type) {
		parent::SitePlugin($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (strlen($search_vars_r['amazukasin']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('amazukasin' => $search_vars_r['amazukasin']));
			return TRUE;
		} else {
			// Get the mapped AMAZON index type
			$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));

			$queryUrl = 'http://www.amazon.co.uk/exec/obidos/external-search?url=' . rawurlencode('index=' . $index_type) . '&keyword=' . urlencode($search_vars_r['title']) . '&sz=' . $items_per_page . '&pg=' . $page_no;

			$pageBuffer = $this->fetchURI($queryUrl);
		}

		if (strlen($pageBuffer) > 0) {
			$amazukasin = FALSE;

			// check for an exact match, but not if this is second page of listings or more
			if (!$this->isPreviousPage()) {
				if (preg_match("/ASIN: <font>(\w{10})<\/font>/", $pageBuffer, $regs)) {
					$amazukasin = trim($regs[1]);
				} else if (preg_match("/ASIN: (\w{10})/", strip_tags($pageBuffer), $regs)) {
					$amazukasin = trim($regs[1]);
				} else if (preg_match("/ISBN: ([^;]+);/", strip_tags($pageBuffer), $regs)) // for books, ASIN is the same as ISBN
 {
					$amazukasin = trim($regs[1]);
				}
			}

			// exact match
			if ($amazukasin !== FALSE) {
				// single record returned
				$this->addListingRow(NULL, NULL, NULL, array('amazukasin' => $amazukasin));

				return TRUE;
			} else {
				$pageBuffer = preg_replace('/[\r\n]+/', ' ', $pageBuffer);
				//print_r($pageBuffer);

				//<div class="resultCount">Showing 1 - 12 of 55 Results</div>
				if (preg_match("/<td class=\"resultCount\">Showing [0-9]+[\s]*-[\s]*[0-9]+ of ([0-9]+)[,]*([\d]*) Results<\/td>/i", $pageBuffer, $regs) || preg_match("/<td class=\"resultCount\">Showing ([0-9]+) Result[s]*<\/td>/i", $pageBuffer, $regs)) {
					// store total count here.
					$this->setTotalCount($regs[1] . $regs[2]);

					if (preg_match_all("!<td class=\"imageColumn\".*?" . "<a href=\"[^\"]+\">.*?" . "<img .*?src=\"([^\"]+)\".*?" . "<td class=\"dataColumn\">.*?" . "<a href=\"([^\"]+)\"><span class=\"srTitle\">([^<]*)</span></a>!m", $pageBuffer, $matches)) {
						for ($i = 0; $i < count($matches[0]); $i++) {
							if (preg_match("!/dp/([^/]+)/!", $matches[2][$i], $regs)) {
								if (strpos($matches[1][$i], "no-img") !== FALSE)
									$matches[1][$i] = NULL;

								$this->addListingRow($matches[3][$i], $matches[1][$i], NULL, array('amazukasin' => $regs[1], 'search.title' => $search_vars_r['title']));
							}
						}
					}
				}
			}

			//default
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// Perform a query based on !ASIN number	ISBN number
	function queryItem($search_attributes_r, $s_item_type) {//print_r( $search_attributes_r['amazukasin']);
	// Mike E 2008-07-16.  Not fully tested with other media types so please feel free to test.
		switch ($s_item_type) {
		case 'BOOK':
			$index = "books";
			$search_attributes_r['amazukasin'] = preg_replace("/[^0-9xX]/", "", $search_attributes_r['amazukasin']);
			break;
		case 'DVD':
			$index = "dvds";
			break;
		case 'CD':
			$index = "cds";
			break;

		}

		//print (" $s_item_type\n");// Debug
		// We could remove hyphens here

		//print_r( $search_attributes_r['amazukasin']); print ("\n");// Debug
		//$pageBuffer = $this->fetchURI("http://www.amazon.co.uk/gp/search?keywords=". $search_attributes_r['amazukasin'] ."&index=" . $index);
		$pageBuffer = $this->fetchURI("http://www.amazon.co.uk/exec/obidos/ASIN/" . $search_attributes_r['amazukasin']);
		//print_r($pageBuffer);

		// no sense going any further here.
		if (strlen($pageBuffer) == 0)
			return FALSE;

		$pageBuffer = preg_replace('/[\r\n]+/', ' ', $pageBuffer);
		$pageBuffer = preg_replace('/>[\s]*</', '><', $pageBuffer);

		//<b class="sans">The Open Door </b>
		if (preg_match("/<span id=\"btAsinTitle\"[^>]*>([^<]+)<\/span>/s", $pageBuffer, $regs) || preg_match("/<b class=\"sans\">([^<]+)<\/b>/s", $pageBuffer, $regs) || preg_match("/<b class=\"sans\">([^<]+)<!--/s", $pageBuffer, $regs)) {
			$title = $regs[1];

			if (($sqidx = strpos($title, "[")) !== FALSE) {
				$title = substr($title, 0, $sqidx);
			}

			$this->addItemAttribute('title', $title);
		}

		// <td class="listprice">£20.00 </td>
		//<td><b class="price">£14.00</b>
		if (preg_match("!<span.*?class=\"listprice\">.*?([0-9\.]+)[\s]*</span>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('listprice', $regs[1]);
		}

		if (preg_match("!<b class=\"priceLarge\">.?([0-9\.]+)[\s]*</b>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('price', $regs[1]);
		}

		//http://g-ec2.images-amazon.com/images/G/01/x-locale/common/customer-reviews/stars-4-0._V47081936_.gif 
		if (preg_match("!<li><b>Average Customer Review:</b>[\s]*<img src=\".*?/stars-([^\.]+).!i", $pageBuffer, $regs)) {
			$this->addItemAttribute('amznrating', str_replace('-', '.', $regs[1]));
		}

		// Get the mapped AMAZON index type
		$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));

		//genre:
		if (preg_match("!<b>Amazon.co.uk Sales Rank:</b>(.*?)</li>!i", $pageBuffer, $regs)) {
			//<a href="/gp/bestsellers/dvd-de/289099/ref=pd_zg_hrsr_d_1_3">Fantasy</a>
			if (preg_match_all('!<a href=\".*?\">(.*?)</a>!i', $regs[1], $genres)) {
				$genres = array_map("unaccent", array_unique($genres[1]));
				sort($genres);
				//print_r($genres);
				$this->addItemAttribute('genre', $genres);
			}
		}

		switch ($index_type) {
		case 'dvd-uk':
		case 'vhs-uk':
			$this->parse_amazon_video_data($search_attributes_r, $s_item_type, $pageBuffer);
			$this->get_image($search_attributes_r['amazukasin']);
			break;

		case 'video-games-uk':
			$this->parse_amazon_game_data($search_attributes_r, $pageBuffer);
			$this->get_image($search_attributes_r['amazukasin']);
			break;

		case 'books-uk':
			$this->parse_amazon_books_data($search_attributes_r, $pageBuffer);
			$this->get_image($this->getItemAttribute('isbn10'));//$search_attributes_r['amazukasin']
			$this->get_reviews($pageBuffer, $this->getItemAttribute('isbn10'));
			break;

		case 'music':
			$this->parse_amazon_music_data($search_attributes_r, $pageBuffer);
			$this->get_image($search_attributes_r['amazukasin']);
			break;

		default://Not much here, but what else can we do?
			break;
		}

		return TRUE;
	}

	/**
	Get the image based in the ISBN10 number
	Mike E: 2008-07-16
	This section moved to here from lines 178 in function queryItem() as we need to parse the book data before we know the ISBN10 number to get the image URL.  Call this function at the end of parse_amazon_books_data with: .$this->get_image($this->getItemAttribute('isbn10'));
	todo: Make it work for all other media.
	 */
	function get_image($isbn) {

		//http://www.amazon.co.uk/gp/product/images/B000050YLT/ref=dp_image_text_0/026-9147519-9634865?ie=UTF8
		$imageBuffer = $this->fetchURI("http://www.amazon.co.uk/gp/product/images/" . $isbn);
		if ($imageBuffer !== FALSE) {
			//fetchImage("alt_image_0", "http://images.amazon.com/images/P/B0000640RX.01._SS400_SCLZZZZZZZ_.jpg" );
			if (preg_match_all("!fetchImage\(\"[^\"]+\", \"([^\"]+)\"!", $imageBuffer, $regs)) {
				$this->addItemAttribute('imageurl', $regs[1]);
			} //<img src="http://images.amazon.com/images/P/B000FMH8RG.01._SS500_SCLZZZZZZZ_V52187861_.jpg" id="prodImage" />
 else if (preg_match_all("!<img src=\"([^\"]+)\" id=\"prodImage\" />!", $imageBuffer, $regs)) {
				$this->addItemAttribute('imageurl', $regs[1]);
			}
		}

		return $imageBuffer;
	}

	/**
	Get the reviews from the Amazon reviews page
	Mike E 2008-07-16
	I've moved this from amazonutils.php.  It goes against the principal of not duplicating code but I think that the sites are sufficientl different in each country to justify it. See also following function.
	 */
	function get_reviews($pageBuffer, $isbn) {
		if (preg_match("!http://www.amazon.co.uk/review/product/.*>See all [0-9] customer reviews\.\.\.</a>!", $pageBuffer, $regs)) {
			//print "Tested for reviews";
			$reviewPage = $this->fetchURI("http://www.amazon.co.uk/review/product/" . $isbn);//
			//print("Getting reviews");
			if (strlen($reviewPage) > 0) {//print("Parsing reviews");
				$reviews = $this->parse_amazon_reviews($reviewPage);
				//print ($reviews);
				if (is_not_empty_array($reviews)) {

					$this->addItemAttribute('blurb', $reviews);
				}
			}
		} else {
			$reviews = parse_amazon_reviews($pageBuffer);
			if (is_not_empty_array($reviews)) {
				$this->addItemAttribute('blurb', $reviews);
			}
		}

	}

	/**
	Mike E 2008-07-16
	New function to parse the reviews.  You end up with a set of tabs in the 'add item' page from which you can choose ONE review.  Perhaps you might want to choose more?  TODO...
	 */
	function parse_amazon_reviews($reviewPage) {
		$reviews = array();

		$start = strpos($reviewPage, "<!-- BOUNDARY -->");

		if ($start == FALSE)
			return $reviews;
		// Nothing found

		// Extract the reviewer's names
		preg_match_all("!href=\"http://www.amazon.co.uk/gp/pdp/profile/[^>]+>[^>]+>([A-z\.\"\s]*)!", $reviewPage, $reg);
		//print_r($reg[1][1]); // Debug
		foreach ($reg[1] as $key => $value) {
			//print "$key => $value<br/>\n";
			$this->addItemAttribute('reviewers', $value);
			$reviews = $value;
		}
		if (1) {
			// Now get the reviews
			if (preg_match_all("!BOUNDARY.*?</div>.*?</div>.*?</div>.*?</div>(.*?)<div style!s", $reviewPage, $matches)) {//print_r($matches[1]);
				foreach ($matches[1] as $key => $value) {
					//$reviews .= $value;
				}
			}
			//print_r ($matches);
		}// END if()					

		return $matches[1];//$reviews;
	}

	function parse_amazon_game_data($search_attributes_r, $pageBuffer) {
		if (preg_match("!by <a href=\".*?field-keywords=[^\"]*\">([^<]*)</a>!i", $pageBuffer, $regs)) {
			$this->addItemAttribute('gamepblshr', $regs[1]);
		}

		if (preg_match("!<b>Platform:</b>[^<]*<img src=\"([^\"]+)\"[^<]*>([^<]+)</div>!mi", $pageBuffer, $regs)) {
			// Different combo's of windows, lets treat them all as windows.
			if (strpos($regs[2], "Windows") !== FALSE)
				$platform = "Windows";
			else
				$platform = trim($regs[2]);

			$this->addItemAttribute('gamesystem', $platform);
		}

		//ELSPA</a> Minimum Age:</b> 15 <br>
		// Rating extraction block - For more information see:
		//  http://www.amazon.co.uk/exec/obidos/tg/browse/-/502556/202-1345170-2851025/202-1345170-2851025
		if (preg_match("!ELSPA</a> Rating:[\s]*</b>([^<]*)<!i", $pageBuffer, $regs)) {
			$this->addItemAttribute('elsparated', $regs[1] . '+'); // the '+' is required
		}

		//<a href="/gp/help/customer/display.html/203-0071143-3853558?ie=UTF8&amp;nodeId=502556">PEGI</a> Rating: </b>Ages 16 and Over
		if (preg_match("!PEGI</a> Rating:[\s]*</b>([^<]*)<!i", $pageBuffer, $regs)) {
			$this->addItemAttribute('pegirated', $regs[1]);
		}

		if (preg_match("/<h2>Product Feat.*?<ul[^<]*>(.+?)<\/ul>/msi", $pageBuffer, $featureblock)) {
			if (preg_match_all("/<li>([^<]*)<\/li>/si", $featureblock[1], $matches)) {
				for ($i = 0; $i < count($matches[1]); $i++) {
					$matches[1][$i] = strip_tags($matches[1][$i]);
				}

				$this->addItemAttribute('features', implode("\n", $matches[1]));
			}
		}

		if (preg_match("!<li><b> Release Date:</b>([^<]*)</li>!si", $pageBuffer, $regs)) {
			$timestamp = strtotime($regs[1]);
			$date = date('d/m/Y', $timestamp);
			$date = date('Y', $timestamp);
			$this->addItemAttribute('gamepbdate', $date);
		}

		// now parse game plot
		$start = strpos($pageBuffer, "<div class=\"bucket\" id=\"productDescription\">");
		if ($start !== FALSE)
			$start = strpos($pageBuffer, "<b class=\"h1\">Reviews</b>", $start);
		if ($start !== FALSE)
			$start = strpos($pageBuffer, "<div class=\"content\">", $start);
		if ($start !== FALSE)
			$start = strpos($pageBuffer, "<b>Manufacturer's Description</b>", $start);

		if ($start !== FALSE) {
			$start += strlen("<b>Manufacturer's Description</b>");
			$end = strpos($pageBuffer, "</div>", $start);
			$productDescriptionBlock = substr($pageBuffer, $start, $end - $start);
			$this->addItemAttribute('game_plot', $productDescriptionBlock);
		}
	}

	function parse_amazon_music_data($search_attributes_r, $pageBuffer) {
		//<META name="description" content="Come on Over, Shania Twain, Mercury">
		if (preg_match("/<meta name=\"description\" content=\"([^\"]*)\"/i", $pageBuffer, $regs)) {
			if (preg_match("/by (.*)/i", $regs[1], $regs2)) {
				// the artist is the last part of the description.
				// Amazon.fr : Dangerous: Musique: Michael Jackson by Michael Jackson
				$this->addItemAttribute('artist', $regs2[1]);
			}
		}

		//<li><b>Label:</b> Columbia</li>
		//<b>Label:</b> <a HREF="/exec/obidos/search-handle-url/size=20&store-name=music&index=music&field-label=Mercury/026-5027435-0842841">Mercury</a><br>
		if (preg_match("!<b>Label:[\s]*</b>[\s]*([^<]+)</li>!i", $pageBuffer, $regs)) {
			$this->addItemAttribute('musiclabel', $regs[1]);
		}

		//<B> Audio CD </B>
		//(November 18, 2002)<br>
		//<li><b>Audio CD</b>  (2 Oct 2006)</li>
		if (preg_match("!<b>[\s]*Audio CD[\s]*</b>.*\(([^\)]+)\)</li>!sUi", $pageBuffer, $regs)) {
			$timestamp = strtotime($regs[1]);

			$this->addItemAttribute('release_dt', date('d/m/Y', $timestamp));
			$this->addItemAttribute('year', date('Y', $timestamp));
		}

		//<li><b>Number of Discs:</b> 1</li>
		if (preg_match("!<b>Number of Discs:[\s]*</b>[\s]*([0-9]+)!", $pageBuffer, $regs)) {
			$this->addItemAttribute('no_discs', $regs[1]);
		}

		//http://www.amazon.co.uk/dp/samples/B0000029LG/
		if (preg_match("!http://www.amazon.co.uk/.*/dp/samples/" . $search_attributes_r['amazukasin'] . "/!", $pageBuffer, $regs)) {
			$samplesPage = $this->fetchURI("http://www.amazon.co.uk/dp/samples/" . $search_attributes_r['amazukasin'] . "/");
			if (strlen($samplesPage) > 0) {
				$samplesPage = preg_replace('/[\r\n]+/', ' ', $samplesPage);
				$tracks = parse_music_tracks($samplesPage);
				$this->addItemAttribute('cdtrack', $tracks);
			}
		} else if (preg_match("!<div class=\"bucket\">[\s]*<b class=\"h1\">Track Listings</b>(.*?)</div>!", $pageBuffer, $regs) || preg_match("!<div class=\"bucket\">[\s]*<b class=\"h1\">Listen to Samples</b>(.*?)</div>!", $pageBuffer, $regs)) {
			$tracks = parse_music_tracks($regs[1]);
			$this->addItemAttribute('cdtrack', $tracks);
		}

	}

	function parse_amazon_books_data($search_attributes_r, $pageBuffer) {
		//<meta name="description" content="Amazon.co.uk: Rambo: First Blood: Books: David Morrell by David Morrell" />
		$start = strpos($pageBuffer, "<div class=\"buying\">");
		if ($start !== FALSE)
			$start = strpos($pageBuffer, "<b class=\"sans\">");

		if ($start !== FALSE) {
			$end = strpos($pageBuffer, "</div>", $start);
			$authorBlock = substr($pageBuffer, $start, $end - $start);

			if (preg_match_all("!<a href=\".*?field-author=[^\"]*\">([^<]*)</a>!i", $authorBlock, $regs)) {
				$this->addItemAttribute('author', $regs[1]);
			}
		}

		if (($startIndex = strpos($pageBuffer, "<b class=\"h1\">Look for similar items by subject</b>")) !== FALSE && ($endIndex = strpos($pageBuffer, "</form>", $startIndex)) !== FALSE) {
			$subjectform = substr($pageBuffer, $startIndex, $endIndex - $startIndex);

			if (preg_match_all("!<input type=\"checkbox\" name=\"field\+keywords\" value=\"([^\"]+)\"!", $subjectform, $matches)) {
				$this->addItemAttribute('genre', $matches[1]);
			}
		}

		// Synopsis extraction
		// Mike E
		if (preg_match("!<b>Synopsis</b><br[\s]*/>(.*?)</div>!mix", $pageBuffer, $regs)) {
			//print_r($regs);
			$this->addItemAttribute('synopsis', $regs[1], HTML_CONTENT_IS_LEGAL);
		}

		//<li><b>ISBN-10:</b> 0261102389</li>
		//<li><b>ISBN-13:</b> 978-0261102385</li>

		if (preg_match("!<b>ISBN-10:</b>[\s]*([0-9]+)!", $pageBuffer, $regs)) {
			$this->addItemAttribute('isbn', $regs[1]);
			$this->addItemAttribute('isbn10', $regs[1]);
		}

		if (preg_match("!<b>ISBN-13:</b>[\s]*([0-9\-]+)!", $pageBuffer, $regs)) {
			$this->addItemAttribute('isbn13', $regs[1]);
		}

		//<li><b>Paperback:</b> 1500 pages</li>
		if (preg_match("/([0-9]+) pages/", $pageBuffer, $regs)) {
			$this->addItemAttribute('nb_pages', $regs[1]);
		}

		//<li><b>Publisher:</b> HarperCollins; New Ed edition (1 Mar 1999)</li>
		if (preg_match("!<b>Publisher:</b>[\s]*([^;<]+);([^<]+)</li>!U", $pageBuffer, $regs)) {
			$this->addItemAttribute('publisher', $regs[1]);
			// Mike E:  Removed day and month from date			
			if (preg_match("!\(([^\)]*[0-9]+)\)!", $regs[2], $regs2)) {
				$timestamp = strtotime($regs2[1]);
				$date = date('Y', $timestamp);
				$this->addItemAttribute('pub_date', $date);
			}
		} else if (preg_match("!<b>Publisher:</b>[\s]*([^<]+)</li>!U", $pageBuffer, $regs)) {
			if (preg_match("!([^\(]+)\(!", $regs[1], $regs2)) {
				$this->addItemAttribute('publisher', $regs2[1]);
			}

			if (preg_match("!\(([^\)]*[0-9]+)\)!", $regs[1], $regs2)) {
				$timestamp = strtotime($regs2[1]);
				$date = date('Y', $timestamp);
				$this->addItemAttribute('pub_date', $date);
			}
		}
		//$this->get_image($this->getItemAttribute('isbn10'));//$search_attributes_r['amazukasin']

	}

	function parse_amazon_video_data($search_attributes_r, $s_item_type, $pageBuffer) {
		//<b>Rambo - First Blood [1982]</b>
		// Need to escape any (, ), [, ], :, ., 
		//<b class="sans">Rambo: First Blood Part II [1985] (1985)</b>
		if (preg_match("/<b.*>" . preg_quote($this->getItemAttribute('title'), "/") . "[\s]*\[([0-9]*)\]/s", $pageBuffer, $regs)) {
			$this->addItemAttribute('year', $regs[1]);
		} else if (preg_match("/<b.*>" . preg_quote($this->getItemAttribute('title'), "/") . "[\s]*\(([0-9]*)\)<\/b>/s", $pageBuffer, $regs)) {
			$this->addItemAttribute('year', $regs[1]);
		} else if (preg_match("!DVD Release Date:</b>.*?([\d][\d][\d][\d])</li>!i", $pageBuffer, $regs)) {
			$this->addItemAttribute('year', $regs[1]);
		}

		//<b>Classification: </b> <span class="medSprite s_med15 " ><span>15</span></span> </li>
		if (preg_match("/<b>Classification:.*?<span>(.*?)<\/span>/i", $pageBuffer, $regs)) {
			$this->addItemAttribute('age_rating', $regs[1]);
		}

		$this->addItemAttribute('actors', parse_amazon_video_people("Actors", $pageBuffer));
		$this->addItemAttribute('director', parse_amazon_video_people("Directors", $pageBuffer));
		$this->addItemAttribute('writer', parse_amazon_video_people("Writers", $pageBuffer));

		//<li><b>Studio:</b>  Momentum Pictures Home Ent</li>			
		if (preg_match("!<li><b>Studio:[\s]*</b>([^<]*)</li>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('studio', $regs[1]);
		}

		if (preg_match("!<li><b>DVD Release Date:[\s]*</b>([^<]*)</li>!", $pageBuffer, $regs)) {
			// Get year only, for now.  In the future we may add ability to
			// convert date to local date format.
			if (preg_match("/([0-9]+)$/m", $regs[1], $regs2)) {
				$this->addItemAttribute('dvd_rel_dt', $regs2[1]);
			}
		}

		if (preg_match("!<b>Number of discs:[\s]*</b>[\s]*([0-9]+)!", $pageBuffer, $regs)) {
			$this->addItemAttribute('no_media', $regs[1]);
		}

		if (preg_match("!<li><b>Aspect Ratio:[\s]*</b>[\s]*([0-9\.]+)!", $pageBuffer, $regs)) {
			$this->addItemAttribute('ratio', $regs[1]);
		}

		if (preg_match("!<li><b>Run Time:[\s]*</b>[\s]*([0-9]+)!", $pageBuffer, $regs)) {
			$this->addItemAttribute('run_time', $regs[1]);
		}

		// Region extraction block
		if (preg_match("!<li><b>Region:[\s]*</b>Region ([0-9]+)!", $pageBuffer, $regs)) {
			$this->addItemAttribute('dvd_region', $regs[1]);
		}

		//<li><b>Format: </b>Anamorphic, PAL, Widescreen</li>
		if (preg_match("!<li><b>Format:[\s]*</b>([^<]*)</li>!", $pageBuffer, $regs)) {
			if (preg_match("/NTSC/", $regs[1], $regs2))
				$this->addItemAttribute('vid_format', 'NTSC');
			else
				$this->addItemAttribute('vid_format', 'PAL');

			if (preg_match("/Anamorphic/", $regs[1], $regs2))
				$this->addItemAttribute('anamorphic', 'Y');
		}

		$amazon_dvd_audio_map = array(array("English"), array("Spanish"), array("French"), array("German"), array("Italian"));

		if (preg_match("/<b>Language.*?<\/b> ([^<]*)<\/li>/i", $pageBuffer, $regs)) {
			$audio_lang_r = explode(',', unaccent($regs[1]));

			while (list(, $audio_lang) = @each($audio_lang_r)) {
				$key = parse_language_info($audio_lang, $amazon_dvd_audio_map);
				if ($key !== NULL) {
					$this->addItemAttribute('audio_lang', $key);
				}
			}

			if (preg_match_all("/\(([^\)]*)\)/", $regs[1], $aud)) {
				$this->addItemAttribute('dvd_audio', preg_replace('/Dolby Digital/i', 'Dolby', array_unique($aud[1])));
			}
		}
		if (preg_match("/<b>Subtitle.*?<\/b> ([^<]*)<\/li>/i", $pageBuffer, $regs)) {
			$audio_lang_r = explode(',', unaccent($regs[1]));

			while (list(, $audio_lang) = @each($audio_lang_r)) {
				$key = parse_language_info($audio_lang, $amazon_dvd_audio_map);
				if ($key !== NULL) {
					$this->addItemAttribute('subtitles', $key);
				}
			}
		}

		if (preg_match("!<li><b>DVD Features:[\s]*</b><ul>(.*?)</ul>!", $pageBuffer, $regs)) {
			//Available Subtitles, Available Audio Tracks, Main Language, Available Audio Tracks, Sub Titles, Disc Format
			if (preg_match_all("!<li>(.*?)</li>!", $regs[1], $matches)) {
				$dvd_extras = NULL;

				for ($i = 0; $i < count($matches[0]); $i++) {
					if (preg_match("!<li>(.*?):(.*?)</li>!", $matches[0][$i], $matches2)) {
						if ($matches2[1] == 'Available Subtitles' || $matches2[1] == 'Sub Titles') {
							$this->addItemAttribute('subtitles', trim_explode(",", $matches2[2]));
						} else if ($matches2[1] == 'Available Audio Tracks') {
							$this->addItemAttribute('audio_lang', trim_explode(",", $matches2[2]));
						}
					} else {
						$dvd_extras[] = $item;
					}
				}

				if (is_array($dvd_extras)) {
					$this->addItemAttribute('dvd_extras', implode("\n", $dvd_extras));
				}
			}
		}

		if (preg_match("!http://amazon.imdb.com/title/tt([0-9]*)!", $pageBuffer, $regs)) {
			$this->addItemAttribute('imdb_id', $regs[1]);
		}

		// Attempt to include data from IMDB if available - but only for DVD, VHS, etc
		// as IMDB does not work with BOOKS or CD's.
		if (is_numeric($this->getItemAttribute('imdb_id'))) {
			$sitePlugin = &get_site_plugin_instance('imdb');
			if ($sitePlugin !== FALSE) {
				if ($sitePlugin->queryItem(array('imdb_id' => $this->getItemAttribute('imdb_id')), $s_item_type)) {
					// no mapping process is performed here, as no $s_item_type was provided.
					$itemData = $sitePlugin->getItemData();
					if (is_array($itemData)) {
						// merge data in here.
						while (list($key, $value) = each($itemData)) {
							if ($key == 'actors')
								$this->replaceItemAttribute('actors', $value);
							else if ($key == 'director')
								$this->replaceItemAttribute('director', $value);
							else if ($key == 'year')
								$this->replaceItemAttribute('year', $value);
							else if ($key == 'actors')
								$this->replaceItemAttribute('actors', $value);
							else if ($key == 'genre')
								$this->replaceItemAttribute('genre', $value);
							else if ($key == 'plot') //have to map from imdb to amazon attribute type.
								$this->addItemAttribute('blurb', $value);
							else if ($key != 'age_rating' && $key != 'run_time')
								$this->addItemAttribute($key, $value);
						}
					}
				}
			}
		}
	}
}
?>
