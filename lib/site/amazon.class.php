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
include_once("./lib/SitePlugin.class.php");
include_once("./lib/site/amazonutils.php");

class amazon extends SitePlugin {
	private $url;
	private $asinId;

	private $sites = array(
				'amazon' => array('asinId' => 'amazonasin', 'url' => 'www.amazon.com'), 
				'amazonuk' => array('asinId' => 'amazukasin', 'url' => 'www.amazon.co.uk'), 
				'amazonfr' => array('asinId' => 'amazfrasin', 'url' => 'www.amazon.fr'), 
				'amazonde' => array('asinId' => 'amazdeasin', 'url' => 'www.amazon.de')
	);

	function __construct($site_type) {
		parent::__construct($site_type);

		$this->asinId = $this->sites[$site_type]['asinId'];
		$this->url = $this->sites[$site_type]['url'];
		$this->_httpClient->agent = 'Mozilla/5.0 (X11; OpenDB) Gecko/20100101 Firefox/50.0';
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (strlen($search_vars_r[$this->asinId]) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array($this->asinId => $search_vars_r[$this->asinId]));
			return TRUE;
		} else {
			//http://www.amazon.com/s/ref=sr_nr_p_n_format_browse-bi_mrr_0?rh=i%3Advd%2Ck%3Aguard%2Cp_n_format_browse-bin%3A2650304011&sort=movies-tv&keywords=guard&ie=UTF8&qid=1410661852&rnid=2650303011
			//http://www.amazon.com/s/ref=sr_nr_p_n_format_browse-bi_mrr_3?rh=i%3Advd%2Ck%3Aguard%2Cp_n_format_browse-bin%3A2650305011&sort=movies-tv&keywords=guard&ie=UTF8&qid=1410661852&rnid=2650303011
			// Get the mapped AMAZON index type
			$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));

			// amazon does not provide the ability to specify how many items per page, so $items_per_page is ignored!
			$queryUrl = "https://" . $this->url . "/exec/obidos/external-search?index=" . $index_type . "&keyword=" . urlencode($search_vars_r['title']) . "&page=$page_no";

			$pageBuffer = $this->fetchURI($queryUrl);
		}

		if (strlen($pageBuffer) > 0) {
			$amazonasin = FALSE;
			
			//<li><b>ISBN-10:</b> 0812929985</li>
			//<a href="http://www.amazon.com/Batteries-Not-Included-Jessica-Tandy/dp/0783232047/ref=sr_1_1?s=movies-tv&amp;ie=UTF8&amp;qid=1409445432&amp;sr=1-1&amp;keywords=025192052026">
			// check for an exact match, but not if this is second page of listings or more
			if (!$this->isPreviousPage()) {
				if (preg_match("/ASIN: <font>(\w{10})<\/font>/", $pageBuffer, $regs)) {
					$amazonasin = trim($regs[1]);
				} else if (preg_match("/ASIN: (\w{10})/", strip_tags($pageBuffer), $regs)) {
					$amazonasin = trim($regs[1]);
				} else if (preg_match("!<li><b>ISBN-10:</b>\s*([0-9]+)</li>!", $pageBuffer, $regs)) { // for books, ASIN is the same as ISBN
					$amazonasin = trim($regs[1]);
				} else if (preg_match_all("!<div id=\"result_[0-9]+\"[^>]*?name=\"([^\"])\"!", $pageBuffer, $regs)) {
					if (count($regs[0]) == 1) {
						$amazonasin = trim($regs[1]);
					}
				} else if (preg_match_all("!<div id=\"result_([0-9]+)\"!", $pageBuffer, $regs)) {
					if (count($regs[0]) == 1) {
						if (preg_match("!<a href=\".*?/dp/([^/]+)/.*?keywords=.*?!", $pageBuffer, $regs)) {
							$amazonasin = trim($regs[1]);
						}
					}
				}
			}

			
			// exact match
			if ($amazonasin !== FALSE) {
				// single record returned
				$this->addListingRow(NULL, NULL, NULL, array($this->asinId => $amazonasin, 'search.title' => $search_vars_r['title']));

				return TRUE;
			} else {
				// this is a severe memory hog!!!
				$pageBuffer = preg_replace('/[\r\n]+/', ' ', $pageBuffer);

				//<div class="resultCount">Showing 1 - 12 of 55 Results</div> || class="resultCount">Showing 1 Result</
				//<span>1-24 von 194 Ergebnissen</span>
				if ((preg_match("/ id=\"resultCount\">.*?<span>.*?.[0-9]+[\s]+?-[\s]+?[0-9]+.*?([0-9,]+).*?<\//", $pageBuffer, $regs) ||
				     preg_match("/ id=\"resultCount\">.*?<span>.*?.([0-9]+).*?<\//", $pageBuffer, $regs) ||
				     preg_match("/ id=.s-result-count.*?([0-9,]+) results? for/", $pageBuffer, $regs) )) {
					// need to remove the commas from the total
					$total = str_replace(",", "", $regs[1]);

					// store total count here.
					$this->setTotalCount($total);

					// 2 = img, 1 = href, 3 = title		
					if (preg_match_all("/id=\"result_.*?href=\"(.*?)\">.*?<img.*?src=\"([^\"]+)\".*?<a.*?>(.*?)<\/a/i", $pageBuffer, $matches)) {
						for ($i = 0; $i < count($matches[0]); $i++) {
							
							$imageuri = preg_replace('!(\/[^.]+\.)_[^.]+_\.!', "$1", $matches[2][$i]);

							if (preg_match("!/dp/([^/]+)/!", $matches[1][$i], $regs)) {
								if (strpos($matches[2][$i], "no-img") !== FALSE)
									$matches[2][$i] = NULL;

								if (!preg_match("!<a .*title=\"Shop Instant Video\" href=\"[^>]*/dp/".$regs[1]."/!i", $pageBuffer, $newregs)) {
									$this->addListingRow($matches[3][$i], $imageuri, NULL, array($this->asinId => $regs[1], 'search.title' => $search_vars_r['title']));
								}
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

	function queryItem($search_attributes_r, $s_item_type) {
		// assumes we have an exact match here
		$pageBuffer = $this->fetchURI("https://" . $this->url . "/gp/product/" . $search_attributes_r[$this->asinId]);

		// no sense going any further here.
		if (strlen($pageBuffer) == 0)
			return FALSE;

		$pageBuffer = preg_replace('/[\r\n]+/', ' ', $pageBuffer);
		$pageBuffer = preg_replace('/>[\s]*</', '><', $pageBuffer);

		//<span id="btAsinTitle">Prometheus (Blu-ray/ DVD + Digital Copy) (2012)</span>
		//<span id="btAsinTitle" style="">Homeland: The Dark Elf Trilogy, Part 1 (Forgotten Realms: The Legend of Drizzt, Book I) (Bk. 1) <span style="text-transform:capitalize; font-size: 16px;">[Mass Market Paperback]</...
		//<h1 class="a-size-large a-spacing-none" id="title"> Illustration School: Let's Draw Happy People <span class="a-size-medium a-color-secondary a-text-normal">Hardcover</span></h1>
		//<span id="productTitle" class="a-size-large">Men in Black 3 [Blu-ray]</span>
		if (preg_match("/<span id=\"btAsinTitle\"[^>]*>([^<]+)<\/?span/s", $pageBuffer, $regs) || 
		    preg_match("/<span id=\"productTitle\"[^>]*?>([^<]+)<\/span/s", $pageBuffer, $regs) ||
		    // <h1 id="title">...
		    preg_match("/<h[^>]*?id=\"title\"[^>]*>([^<]+)</", $pageBuffer, $regs) ||
		    preg_match("/<b class=\"sans\">([^<]+)<\/b>/s", $pageBuffer, $regs) ||
		    preg_match("/<b class=\"sans\">([^<]+)<!--/s", $pageBuffer, $regs)) {
			$title = trim($regs[1]);

			// If extra year appended, remove it and just get the title.
			if (preg_match("/(.*)\([0-9]+\)$/", $title, $regs2)) {
				$title = $regs2[1];
			}

			$title = trim(str_replace("\"", "", $title));

			if (($idx = strpos($title, '(Blu-ray')) !== FALSE) {
				$title = substr($title, 0, $idx);
			} else if (($idx = strpos($title, '[Blu-ray')) !== FALSE) {
				$title = substr($title, 0, $idx);
			}

			$this->addItemAttribute('title', $title);

			//Amazon.com: DVD: First Blood (Special Edition) (1982)
			// Need to escape any (, ), [, ], :, .,
			if (preg_match("/" . preg_quote($this->getItemAttribute('title'), "/") . " \(([0-9]*)\)/s", $pageBuffer, $regs)) {
				$this->addItemAttribute('year', $regs[1]);
			}
		}

		// a hack!
		$upcId = get_upc_code($search_attributes_r['search.title']);
		if ($upcId && $upcId != $this->getItemAttribute('title')) {
			$this->addItemAttribute('upc_id', $upcId);
		}

		// ** Front Cover Image **
		if (preg_match("!<img id=\"main-image\" src=\"(http[^\"]+)\"!s", $pageBuffer, $regs)) {
			// remove image extras _xxx_.
			$image = preg_replace('!(\/[^.]+\.)_[^.]+_\.!', "$1", $regs[1]);
			$this->addItemAttribute('imageurl', $image);

		} else if (preg_match("!registerImage\(\"original_image[^\"]*\", \"(http[^\"]+)\"!", $pageBuffer, $regs)) {
			// remove image extras _xxx_.
			$image = preg_replace('!(\/[^.]+\.)_[^.]+_\.!', "$1", $regs[1]);
			$this->addItemAttribute('imageurl', $image);

		} else if (preg_match("!<img id=\"landingImage\".*?src=\"(http[^\"]+)\"!s", $pageBuffer, $regs)) {
			// remove image extras _xxx_.
			$image = preg_replace('!(\/[^.]+\.)_[^.]+_\.!', "$1", $regs[1]);
			$this->addItemAttribute('imageurl', $image);

		} else if (preg_match("!<img [^>]*?id=\"imgBlkFront\" [^>]*?src=\"(http[^\"]+)\"!s", $pageBuffer, $regs) ||
			   preg_match("!<img [^>]*?src=\"(http[^\"]+)\" [^>]*?id=\"imgBlkFront\"!s", $pageBuffer, $regs)) {
			// remove image extras _xxx_.
			$image = preg_replace('!(\/[^.]+\.)_[^.]+_\.!', "$1", $regs[1]);
			$this->addItemAttribute('imageurl', $image);

		} else if (preg_match("!imageGalleryData'[^a-z]*mainUrl\"[^\"]+\"(http[^\"]+)!s", $pageBuffer, $regs)) {
			$this->addItemAttribute('imageurl', $regs[1]);

		}

		// ** Back Cover Image **
		if (preg_match("!<img [^>]*?id=\"imgBlkBack\" [^>]*?src=\"([^\"]+)\"!", $pageBuffer, $regs)||
		    preg_match("!<img [^>]*?src=\"([^\"]+)\" [^>]*?id=\"imgBlkBack\"!", $pageBuffer, $regs)) {
			// remove image extras _xxx_.
			$image = preg_replace('!(\/[^.]+\.)_[^.]+_\.!', "$1", $regs[1]);
			$this->addItemAttribute('imageurlb', $image);

		}

		if (preg_match_all("!registerImage\(\"cust_image[^\"]*\", \"([^\"]+)\"!", $pageBuffer, $regs)) {
			foreach ($regs[1] as $image) {
                // remove image extras _xxx_.
				$image = preg_replace('!(\/[^.]+\.)_[^.]+_\.!', "$1", $image);
				$this->addItemAttribute('cust_imageurl', $image);
			}
		}

		//http://www.amazon.com/gp/product/product-description/0007136587/ref=dp_proddesc_0/002-1041562-0884857?ie=UTF8&n=283155&s=books
		if (preg_match("!<a href=\"http://" . $this->url . "/gp/product/product-description/" . $search_attributes_r[$this->asinId] . "/[^>]*>See all Editorial Reviews</a>!", $pageBuffer, $regs)
				|| preg_match("!<a href=\"http://" . $this->url . "/gp/product/product-description/" . $search_attributes_r[$this->asinId] . "/[^>]*>See all Reviews</a>!", $pageBuffer, $regs)) {
			$reviewPage = $this->fetchURI("http://" . $this->url . "/gp/product/product-description/" . $search_attributes_r[$this->asinId] . "/reviews/");
			if (strlen($reviewPage) > 0) {
				$reviews = parse_amazon_reviews($reviewPage);
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

		if (preg_match("/<span class=listprice>\\\$([^<]*)<\/span>/i", $pageBuffer, $regs)) {
			$this->addItemAttribute('listprice', $regs[1]);

		} else if (preg_match("/<td class=\"listprice\">\\\$([^<]*)<\/td>/i", $pageBuffer, $regs)) {
			$this->addItemAttribute('listprice', $regs[1]);

		} else if (preg_match("!>List Price:</[^\\$]+\\\$([0-9\.]+)!m", $pageBuffer, $regs)) {
			$this->addItemAttribute('listprice', $regs[1]);
		}

		// amazon price value: <b class="priceLarge">$7.99</b>
		if (preg_match("!<b class=\"(priceLarge|price)\">\\\$([^<]*)</b>!i", $pageBuffer, $regs)) {
			$this->addItemAttribute('listprice', $regs[2]);
			$this->addItemAttribute('price', $regs[2]);
		}

		//http://g-ec2.images-amazon.com/images/G/01/x-locale/common/customer-reviews/stars-4-0._V47081936_.gif 
		if (preg_match("!<li><b>Average Customer Review:</b>[\s]*<img src=\".*?/stars-([^\.]+).!i", $pageBuffer, $regs)) {
			$amazonreview = str_replace('-', '.', $regs[1]);
			$this->addItemAttribute('amznrating', $amazonreview);
			$this->addItemAttribute('amazon_review', $amazonreview);
		}

		if (($startIndex = strpos($pageBuffer, "<h2>Look for Similar Items by Subject</h2>")) !== FALSE && ($endIndex = strpos($pageBuffer, "matching ALL checked", $startIndex)) !== FALSE) {
			$subjectform = substr($pageBuffer, $startIndex, $endIndex - $startIndex);

			if (preg_match_all("!<input type=\"checkbox\" name=\"field\+keywords\" value=\"([^\"]+)\"!", $subjectform, $matches)) {
				$this->addItemAttribute('genre', $matches[1]);
			}
		}

		// Get the mapped AMAZON index type
		$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));

		switch ($index_type) {
		case 'dvd':
		case 'vhs':
			$this->parse_amazon_video_data($search_attributes_r, $s_item_type, $pageBuffer);
			break;

		case 'videogames':
			$this->parse_amazon_game_data($search_attributes_r, $pageBuffer);
			break;

		case 'books':
			$this->parse_amazon_books_data($search_attributes_r, $pageBuffer);
			break;

		case 'music':
			$this->parse_amazon_music_data($search_attributes_r, $pageBuffer);
			break;

		default://Not much here, but what else can we do?
			break;
		}

		return TRUE;
	}

	/**
	    Will return an array of the following structure.
	        array(
	            "gamepblshr"=>game publisher,
	            "gamesystem"=>game platform,
	            "gamerating"=>esrb rating
	            "features"=>features listing for game,
	        );
	 */
	function parse_amazon_game_data($search_attributes_r, $pageBuffer) {
		//Other products by <a href="/exec/obidos/search-handle-url/002-1041562-0884857?%5Fencoding=UTF8&amp;store-name=videogames&amp;search-type=ss&amp;index=videogames&amp;field-brandtextbin=Electronic%20Arts">Electronic Arts</a>
		// Publisher extraction block
		if (preg_match("/Other products by <a[^<]*>([^<]*)<\/a>/i", $pageBuffer, $regs)) {
			$this->addItemAttribute('gamepblshr', $regs[1]);
		}

		// Platform extraction block
		if (preg_match("!<b>Platform:</b>[^<]*<img src=\"([^\"]+)\"[^<]*>([^<]+)</div>!mi", $pageBuffer, $regs)) {
			// Different combo's of windows, lets treat them all as windows.
			if (strpos($regs[2], "Windows") !== FALSE)
				$platform = "Windows";
			else
				$platform = trim($regs[2]);

			$this->addItemAttribute('gamesystem', $platform);
		}

		// Rating extraction block
		if (preg_match("!<b>ESRB Rating:[\s]*</b>.*?<a href=\"[^\"]*\">([^<]+)</a></li>!si", $pageBuffer, $regs)) {
			$this->addItemAttribute('gamerating', strtoupper($regs[1]));
		}

		// Features extraction block
		if (preg_match("/<b[^<]*>Product Features<\/b>.*?<ul[^<]*>(.+?)<\/ul>/msi", $pageBuffer, $featureblock)) {
			if (preg_match_all("/<li>([^<]*)<\/li>/si", $featureblock[1], $matches)) {
				for ($i = 0; $i < count($matches[1]); $i++) {
					$matches[1][$i] = strip_tags($matches[1][$i]);
				}

				$this->addItemAttribute('features', implode("\n", $matches[1]));
			}
		}

		if (preg_match("!<b>Media:[\s]*</b>[\s]*([^<]+)</li>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('media', $regs[1]);
		}

		if (preg_match("!<li><b> Release Date:</b>([^<]*)</li>!si", $pageBuffer, $regs)) {
			$timestamp = strtotime($regs[1]);
			$date = date('d/m/Y', $timestamp);
			$this->addItemAttribute('gamepbdate', $date);
		}

		// now parse game plot
		$start = strpos($pageBuffer, "<div class=\"bucket\" id=\"productDescription\">");
		if ($start !== FALSE)
			$start = strpos($pageBuffer, "<div class=\"content\">", $start);
		if ($start !== FALSE)
			$start = strpos($pageBuffer, "<b>Product Description</b>", $start);

		if ($start !== FALSE) {
			$start += strlen("<b>Product Description</b>");
			$end = strpos($pageBuffer, "</div>", $start);
			$productDescriptionBlock = substr($pageBuffer, $start, $end - $start);
			$this->addItemAttribute('game_plot', $productDescriptionBlock);
		}
	}

	/*
	 * 	Parse Amazon.com CD item
	 *
	 * 	Will return
	 * 	Array(
	 * 		'artist'=>'',
	 * 		'release_dt'=>'',
	 * 		'year'=>'',
	 * 		'musiclabel'=>'',
	 * 		'no_discs'=>'',
	 * 		'cdtrack'=>Array(...)
	 * 	);
	 */
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

		if (preg_match("!<b>Original Release Date:[\s]*</b>[\s]*([^<]+)<!", $pageBuffer, $regs)) {
			$timestamp = strtotime($regs[1]);
			$this->addItemAttribute('orig_release_dt', date('d/m/Y', $timestamp));
		}

		if (preg_match("!http://" . $this->url . "/.*/dp/samples/" . $search_attributes_r[$this->asinId] . "/!", $pageBuffer, $regs)) {
			$samplesPage = $this->fetchURI("http://" . $this->url . "/dp/samples/" . $search_attributes_r[$this->asinId] . "/");
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

	/**
	    Will return an array of the following structure.
	        array(
	            "author"=>author,
	            "publisher"=>publisher,
	            "pub_date"=>date published,
	            "isbn"=>ISBN number,
	            "listprice"=>Regular price,
	        );
	
	    If nothing parsed correctly, then this function will returned
	    unitialised array.
	 */

	/*
	Some search URL examples:
	- search by ISBN:
	  http://www.amazon.com/gp/search/ref=sr_adv_b/?search-alias=stripbooks&unfiltered=1&field-isbn=1591163056
	 */
	function parse_amazon_books_data($search_attributes_r, $pageBuffer) {
		//an id="btAsinTitle" style="">Biochemistry <span style="text-transform:capitalize; font-size: 16px;">[Hardcover]</span></span></h1><span ><a href="/Donald-Voet/e/B000APBABS/ref=ntt_athr_dp_pel_1">Donald Voet</a> (Author) </span></div><div class="jumpBar">
		$authors = "";

		$start = strpos($pageBuffer, "id=\"btAsinTitle\"", $end);
		if ($start !== FALSE) {
			$end = strpos($pageBuffer, "<div class=\"jumpBar\">", $start);

			$authors = trim(substr($pageBuffer, $start, $end - $start));

		} else if (preg_match("!<div id=\"byline\"[^>]*>(.*?)</div!", $pageBuffer, $regs)) {
			$authors = $regs[1];
		}

		if ($authors != "") {
			//print_r($authors);
			if (preg_match_all("!<a href=\".*?\">(.*?)</a> \(Author!i", $authors, $regs) ||
			    preg_match_all("!<span[^>]*>([^<]*)<span[^>]*>\(Author!", $authors, $regs) ||
			    preg_match_all("!<a[^>]*>([^<]*)</a><a[^<]*<i[^<]*</i></a></span><span[^<]*<span[^>]*>\(Author!i", $authors, $regs)) {
				//where are the author first and last names used anyways? for now we just support the author full names to avoid confusion with middle initials etc.
				//foreach($regs[1] as $author) {
				//	preg_match("!(.*?) (.*?)!" $author, $matches
				//	$this->addItemAttribute('authorln', $matches[2]);
				//	$this->addItemAttribute('authorfn', $matches[1]);
				//	$this->addItemAttribute('author', $matches[1]." ".$matches[2]);
				//}
				$this->addItemAttribute('author', $regs[1]);
			}
		}

		//<span>4.5 out of 5 stars</span></span>&nbsp;</a>&nbsp;<span class="histogramButton"
		if (preg_match("!<span>([0-9.]+) out of [0-9]+ stars</span></span>&nbsp;</a>&nbsp;<span class=\"histogramButton\"!", $pageBuffer, $regs)) {
			$this->addItemAttribute('rating', $regs[1]);
		}

		if (preg_match("!class=\"productDescriptionWrapper\">[\s]*([^<]+)<div!", $pageBuffer, $regs)) {
			$this->addItemAttribute('synopsis', $regs[1]);
		}

		// "priceBlockLabel">List Price:</td><td>$9.99 </td
		if (preg_match_all("!\"priceBlockLabel\">List Price:</.*?\\$([0-9.]+)[\s]*?<!", $pageBuffer, $regs)) {
			$this->addItemAttribute('listprice', $regs[1]);
		}

		//<b>Reading level:</b> Young Adult<br
		if (preg_match("!<b>Reading level:</b>[\s]*([^<]+)<br!", $pageBuffer, $regs)) {
			$this->addItemAttribute('readinglevel', $regs[1]);
		}

		//<li><b>Paperback:</b> 1500 pages</li>
		if (preg_match("/([0-9]+) pages/", $pageBuffer, $regs)) {
			$this->addItemAttribute('no_pages', $regs[1]);
		}

		//<h2>Product Details</h2><div class="content"><ul><li><b>Reading level:</b> Young Adult<br /></li><li><b>Mass Market Paperback:</b> 352 pages
		$start = strpos($pageBuffer, "<h2>Product Details</h2>", $end);
		if ($start !== FALSE) {
			$end = strpos($pageBuffer, "</div>", $start);

			$details = trim(substr($pageBuffer, $start, $end - $start));

			$end = strpos($details, "pages");
			$cover = trim(substr($details, 0, $end));
			if (strpos($cover, "Hardcover") !== FALSE) {
				$this->addItemAttribute('binding', 'Hardcover');
			} else {
				$this->addItemAttribute('binding', 'Paperback');
			}

			if (preg_match("!<b>Publisher:</b>[\s]*([^;\(]+);!U", $details, $regs) || preg_match("!<b>Publisher:</b>[\s]*([^\(]+)\(!U", $details, $regs) || preg_match("!<b>Publisher:</b>[\s]*([^<]+)</li>!U", $details, $regs)) {
				$this->addItemAttribute('publisher', $regs[1]);
			}

			if (preg_match("!<b>Publisher:</b>.*?\(([^\)]*[0-9]+)\)!", $details, $regs)) {
				$timestamp = strtotime($regs[1]);
				$this->addItemAttribute('pub_date', date('d M Y', $timestamp));
				$this->addItemAttribute('pub_year', date('Y', $timestamp));
			}

			//<li><b>Language:</b> English</li>
			if (preg_match("!<b>Language:</b>[\s]*([^<]+)</li>!", $details, $regs)) {
				$this->addItemAttribute('text_lang', $regs[1]);
			}

			if (preg_match("!<b>ISBN-10:</b>[\s]*([0-9X]+)!", $details, $regs)) {
				$this->addItemAttribute('isbn', $regs[1]);
				$this->addItemAttribute('isbn10', $regs[1]);
			}

			if (preg_match("!<b>ISBN-13:</b>[\s]*([0-9\-]+)!", $details, $regs)) {
				$this->addItemAttribute('isbn13', $regs[1]);
			}

			if (preg_match("!<b>[\s]*Product Dimensions:[\s]*</b>[\s]*([^<]+)</!", $details, $regs)) {
				$this->addItemAttribute('dimensions', $regs[1]);
			}

			if (preg_match("!<b>[\s]*Shipping Weight:[\s]*</b>[\s]*([^(]+)\(!", $details, $regs)) {
				$this->addItemAttribute('weight', $regs[1]);
			}

		}

		//pages</li><li><b>Publisher:</b> VIZ Media LLC; 2 edition (June 23, 2004)</li><li><b>Language:</b> English</li><li><b>

	}

	/**
	    Will return an array of the following structure.
	        array(
	            "year"=>year,
	            "age_rating"=>age_rating,
	            "dvd_region"=>dvd_region, // not applicable for VHS,DIVX,etc
	            "ratio"=>ration,
	            "audio_lang"=>spoken languages,
	            "subtitles"=>subtitles,
	            "run_time"=>runtime,
	            "director"=>director,
	            "actors"=>actors,
	        );
	
	    If nothing parsed correctly, then this function will returned
	    unitialised array.
	 */
	function parse_amazon_video_data($search_attributes_r, $s_item_type, $pageBuffer) {
		// FIXME - this is used for more than just US site now, so this is invalid
		//$this->addItemAttribute('vid_format', 'NTSC');

		// genre extraction block.
		$startidx = strpos($pageBuffer, "<li><b>Genres:</b>");
		if ($startidx !== FALSE) {
			// Move past start text.
			$startidx += 18;//"Genres:</b>"

			$endidx = strpos($pageBuffer, "</li>", $startidx);

			if ($endidx !== FALSE) {
				// Get rid of all the html - a quick hack!
				$genre = trim(substr($pageBuffer, $startidx, $endidx - $startidx));
				$genre = strip_tags($genre);

				// If composite genre, get rid of / as we do not need it.
				$genre = str_replace(" / ", " ", $genre);

				// Expand Sci-Fi to OpenDb matching value.
				$genre = str_replace("Sci-Fi", "ScienceFiction", $genre);

				// Match all whitespace and convert to a comma.
				$genre = preg_replace("/[\s]+/", ",", $genre);

				$genre = str_replace("(more)", "", $genre);

				$this->addItemAttribute('genre', explode(",", $genre));
			}
		}

		$this->addItemAttribute('actors', parse_amazon_video_people("Actors", $pageBuffer));
		$this->addItemAttribute('director', parse_amazon_video_people("Directors", $pageBuffer));

		// Region extraction block
		//<li><b>Region: </b>Region 1
		if (preg_match("/<li><b>Region:[\s]*<\/b>Region ([0-6])/", $pageBuffer, $regs)) {
			$this->addItemAttribute('dvd_region', $regs[1]);
		}

		// Ratio
		//<li><b>Aspect Ratio:</b> 1.85:1</li>
		if (preg_match("!<li><b>Aspect Ratio:</b>(.*?)<\/li>!", $pageBuffer, $regs)) {
			if (preg_match_all("/([0-9]{1}\.[0-9]+):1/", $regs[1], $matches)) {
				$this->addItemAttribute('ratio', $matches[1]);
			}
		}

		if (preg_match("/<li><b>Number of discs:[\s]*<\/b>[\s]*([0-9]+)/", $pageBuffer, $regs2)) {
			$this->addItemAttribute('no_discs', $regs2[1]);
		}

		//<b>Rating</b>  <img src="http://ec1.images-amazon.com/images/G/01/detail/r._V46905301_.gif" alt="R" align="absmiddle" border="0" height="11" width="12"></li>
		if (preg_match("!Rated:</span>&nbsp;(.*?)&nbsp;!mis", $pageBuffer, $regs)) {
			$this->addItemAttribute('age_rating', $regs[1]);

		} else if (preg_match("!Rated:.*?<span>\s*(.*?)\s!ms", $pageBuffer, $regs)) {
			$this->addItemAttribute('age_rating', $regs[1]);
		}

		if (preg_match("!<b>Studio:[\s]*</b>[\s]*([^<]+)</li>!i", $pageBuffer, $regs)) {
			$this->addItemAttribute('studio', $regs[1]);
		}

		//<li><b>DVD Release Date:</b> April 27, 2004</li>
		if (preg_match("/<b>DVD Release Date:<\/b>([^<]+)<\/li>/i", $pageBuffer, $regs)) {
			$timestamp = strtotime($regs[1]);

			// if year not defined, use dvd_rel_dt
			if ($this->getItemAttribute('year') === FALSE) {
				$this->addItemAttribute('year', date('Y', $timestamp));
			}

			$this->addItemAttribute('dvd_rel_dt', date('d/m/Y', $timestamp));
		}

		// Duration extraction block
		//<li><b>Run Time:</b> 125 minutes </li>
		if (preg_match("/<li><b>Run Time:<\/b>[\s]*([0-9]+) minutes/i", $pageBuffer, $regs)) {
			$this->addItemAttribute('run_time', $regs[1]);
		}

		// Get the anamorphic format attribute - Thanks to André Monz <amonz@users.sourceforge.net
		if (preg_match("/anamorphic/", $pageBuffer)) {
			$this->addItemAttribute('anamorphic', 'Y');
		}

		if (preg_match("/THX Certified/i", $pageBuffer)) {
			$this->addItemAttribute('audio_lang', 'ENGLISH_THX');
		}

		if (preg_match("!<li><b>Language:</b>[\s]*(.*?)</li>!i", $pageBuffer, $regs)) {
			$audio_lang_r = explode(',', $regs[1]);

			$amazon_dvd_audio_map = array(
					array("English", "2.0"), 
					array("English", "5.0"), 
					array("English", "5.1"), 
					array("English", "6.1", "EX"), // Dolby Digital 6.1 EX
					array("English", "6.1", "DTS", "ES"), // English (6.1 DTS ES)
					array("English", "6.1"), 
					array("English", "DTS"));

			$amazon_audio_lang_map = array(
					array("French"), 
					array("Spanish"), 
					array("German"));

			foreach ($audio_lang_r as $audio_lang) {
				$key = parse_language_info($audio_lang, $amazon_dvd_audio_map);
				if ($key !== NULL) {
					$this->addItemAttribute('audio_lang', $key);
				}

				$key = parse_language_info($audio_lang, $amazon_audio_lang_map);
				if ($key !== NULL) {
					$this->addItemAttribute('audio_lang', $key);
				}
			}
		}

		if (preg_match("!<li><b>Subtitles:</b>[\s]*(.*?)</li>!i", $pageBuffer, $regs)) {
			$amazon_video_subtitle_map = array(array("English"), array("French"), array("Spanish"), array("German"));

			$audio_lang_r = explode(',', $regs[1]);

			foreach ($audio_lang_r as $audio_lang) {
				$key = parse_language_info($audio_lang, $amazon_video_subtitle_map);
				if ($key !== NULL) {
					$this->addItemAttribute('subtitles', $key);
				}
			}
		}

		// Edition details block - 'dvd_extras' attribute
		if (preg_match("!<b>DVD Features:<\/b><ul>(.*?)<\/ul>!", $pageBuffer, $regs)) {
			$dvdFeaturesBlock = $regs[1];

			if (preg_match_all("/<li>(.*)<\/li>/mUi", $dvdFeaturesBlock, $matches)) {
				$dvd_extras = NULL;

				foreach ($matches[1] as $item) {
					$item = html_entity_decode(strip_tags($item), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1');

					// We may have a hard space here, so get rid of it.
					$item = trim(strtr($item, chr(160), ' '));

					if (strpos($item, "anamorphic") === FALSE && strpos($item, "Available Subtitles") === FALSE && strpos($item, "Available Audio Tracks") === FALSE) {
						//Commentary by: director George Cosmatos
						if (strpos($item, "Commentary by") !== FALSE && ends_with($item, "Unknown Format")) {
							$item = substr($item, 0, strlen($item) - strlen("Unknown Format"));
						} else if (preg_match("/\"([^\"]+)\"/", $item, $reg2)) {
							$item = $reg2[1];
						}

						$dvd_extras[] = $item;
					}
				}

				if (is_array($dvd_extras)) {
					$this->addItemAttribute('dvd_extras', implode("\n", $dvd_extras));
				}
			}
		}

		// IMDB ID block
		//<A HREF="http://amazon.imdb.com/title/tt0319061/">
		//http://www.amazon.com/gp/redirect.html/103-0177494-1143005?location=http://amazon.imdb.com/title/tt0319061&token=F5BF95E1B869FD4EB1192434BA5B7FECBA8B3718
		//http://amazon.imdb.com/title/tt0319061
		if (preg_match("!://amazon.imdb.com/title/tt([0-9]+)!is", $pageBuffer, $regs)) {
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
						foreach ($itemData as $key => $value) {
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
