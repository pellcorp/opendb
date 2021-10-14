<?php
/* 	
    Baka-Updates Manga Division Plugin
    By fans for fans
 */

include_once("./lib/SitePlugin.class.php");

function html_to_utf8($data) {
	return preg_replace("/\\&\\#([0-9]{3,10})\\;/e", '_html_to_utf8("\\1")', $data);
}

function _html_to_utf8($data) {
	if ($data > 127) {
		$i = 5;
		while (($i--) > 0) {
			if ($data != ($a = $data % ($p = pow(64, $i)))) {
				$ret = chr(base_convert(str_pad(str_repeat(1, $i + 1), 8, "0"), 2, 10) + (($data - $a) / $p));
				for ($i; $i > 0; $i--)
					$ret .= chr(128 + ((($data % pow(64, $i)) - ($data % ($p = pow(64, $i - 1)))) / $p));
				break;
			}
		}
	} else
		$ret = "&#$data;";
	return $ret;
}

class mangaupdat extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		// standard block of code to cater for refresh option, where item already has
		// reference to site item unique ID.
		if (strlen($search_vars_r['MANGAUID']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('MANGAUID' => $search_vars_r['MANGAUID']));
			return TRUE;
		}

		// $this->addListingRow($title, $cover_image_url, $comments, $attributes_r) function call for each item you want to list as an option.
		//    * $title - is the complete title of the item (for users to recognise), which might include the year or other info, but not any images! 
		//    * cover_image_url - should be a complete url (including http:// prefix) for a cover image url. If you do not provide one, the architecture will generate a default theme specific one, so don't do it yourself. 
		//    * $comments - any additional comments about the title, for instance a release date, or the fact that its a special edition, vs. normal, etc. 
		//    * $attributes_r - a set of attribute values that can be used to uniquely identify the item, such as the 'imdb_id' or 'amazonasin' for example. You would format the call thus: 
		//$this->addListingRow($regs[1], $regs[2], array('amazonasin'=>$regs[3])
		//where $regs is a regular expression result, which matched title, cover_img_url and amazonasin (in that order!) 

		// $this->addListingRow('MPD Psycho', 'http://www.mangaupdates.com/image/i1240.jpg', 'Horror, Mature, Psychological, Se...  	1997  	8.21', array('MANGAUID'=>1403) );
		// $this->addListingRow('Psycho Busters', 'http://www.mangaupdates.com/image/i29789.jpg', 'Action, Shounen, Supernatural  	2006  	8.57', array('MANGAUID'=>6662) );

		$queryUrl = "http://www.mangaupdates.com/series.html?search=" . urlencode($search_vars_r['title']) . "&perpage=$items_per_page&page=$page_no";

		$pageBuffer = $this->fetchURI($queryUrl);

		if (strlen($pageBuffer) > 0) {

			// 1 = href, 2 = title
			if (preg_match_all("!><a href='http:\/\/www\.mangaupdates\.com\/series\.html\?id=([0-9]+)' alt='Series Info'>(.*?)<\/a><\/td>!m", $pageBuffer, $matches)) {
				for ($i = 0; $i < count($matches[0]); $i++) {
					$this->addListingRow($matches[2][$i], NULL, NULL, array('MANGAUID' => $matches[1][$i]));
				}
			}

			//while ( preg_match("!><a href='http:\/\/www\.mangaupdates\.com\/series\.html\?id=([0-9]+)' alt='Series Info'>(.*?)<\/a><\/td>!i", $pageBuffer, $regs ) ) {
			//$this->addListingRow($regs[1], '', '', array('MANGAUID'=>$regs[0]) );
			//}
		}

		//else - if no ListingRows then 'No Records Found' is displayed, while returning FALSE
		// results in a Undefined Exception
		return TRUE;
	}

	function queryItem($search_attributes_r, $s_item_type) {

		// assumes we have an exact match here
		$pageBuffer = $this->fetchURI("http://www.mangaupdates.com/series.html?id=" . $search_attributes_r['MANGAUID']);

		if (strlen($pageBuffer) == 0)
			return FALSE;

		if (preg_match('/<span class="releasestitle tabletitle">(.*?)<\/span>/', $pageBuffer, $regs)) {
			$this->addItemAttribute('title', $regs[1]);
		}

		if (preg_match('/<div class="sContent" style="text-align:justify"><!-- google_ad_section_start -->(.*?)<!-- google_ad_section_end -->/', $pageBuffer, $regs)) {
			$this->addItemAttribute('description', $regs[1]);
		}

		if (preg_match('!item=associated.*?\s<div class="sContent" (.*?)\s</div>!', $pageBuffer, $regs)) {
			if (preg_match_all('!>(.*?)<br!', $regs[1], $string)) {
				for ($i = 0; $i < count($string[0]); $i++) {
					$this->addItemAttribute('asscname', $string[1][$i]);
				}
			}
		}

		if (preg_match('!User Rating<\/b><\/div>\s<div class="sContent" >(.*?)<table!', $pageBuffer, $regs)) {
			$this->addItemAttribute('userrating', $regs[1]);
			if (preg_match('!Average: ([0-9.]+) /.*?Bayesian Average: <b>([0-9.]+)</b>!', $regs[1], $ratings)) {
				$this->addItemAttribute('averagerating', $ratings[1]);
				$this->addItemAttribute('bayesianrating', $ratings[2]);
				$this->addItemAttribute('S_RATING', round($ratings[2] / 2));
			}
		}

		if (preg_match("!<div class=\"sContent\" ><center><img height='[0-9]+' width='[0-9]+' src='(.*?)'><\/center>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('imageurl', $regs[1]);
		}

		if (preg_match("!<br /><a href='series.html\?genre=(.*?)'><i><u>Search!", $pageBuffer, $regs)) {
			$genres = explode("_", $regs[1]);
			for ($i = 1; $i < count($genres); $i++) {
				$genrestring = strtr($genres[$i], "+", " ");
				$this->addItemAttribute('genre', $genrestring);
			}
		}

		if (preg_match("!item=author.*?\n.*?title='Author Info'><u>(.*?)<\/u><\/a><BR>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('author', $regs[1]);
		}

		if (preg_match("!item=artists.*?\n.*?title='Author Info'><u>(.*?)<\/u><\/a><BR>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('artists', $regs[1]);
		}

		if (preg_match("!item=publisher.*?\n.*?title='Publisher Info'><u>(.*?)<\/u><\/a>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('publisher', $regs[1]);
		}

		if (preg_match("!item=america_publisher.*?\n.*?title='Publisher Info'><u>(.*?)<\/u><\/a>!", $pageBuffer, $regs)) {
			$this->addItemAttribute('publisher', $regs[1]);
		}

		return TRUE;
	}
}
?>
