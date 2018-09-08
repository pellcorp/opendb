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

    private $sites = array('amazon' => array('asinId' => 'amazonasin', 'url' => 'www.amazon.com'),
                           'amazonuk' => array('asinId' => 'amazukasin', 'url' => 'www.amazon.co.uk'),
                           'amazonfr' => array('asinId' => 'amazfrasin', 'url' => 'www.amazon.fr'),
                           'amazonde' => array('asinId' => 'amazdeasin', 'url' => 'www.amazon.de')
    );

    function amazon($site_type) {
        parent::SitePlugin($site_type);

        $this->asinId = $this->sites[$site_type]['asinId'];
        $this->url = $this->sites[$site_type]['url'];
    }

    function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
        if (strlen($search_vars_r[$this->asinId]) > 0) {
            $this->addListingRow(NULL, NULL, NULL, array($this->asinId => $search_vars_r[$this->asinId]));
            return TRUE;
        } else {
            // Get the mapped AMAZON index type
            $index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));

            // amazon does not provide the ability to specify how many items per page, so $items_per_page is ignored!
            $queryUrl = "https://" . $this->url . "/s?ie=UTF8&index=" . $index_type .
                      "&keyword=" . urlencode($search_vars_r['title']) . "&page=$page_no";

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
        $url = "https://" . $this->url . "/gp/product/" . $search_attributes_r[$this->asinId];
        $pageBuffer = preg_replace('/\s*[\r\n]+/', "\n", $this->fetchURI($url));

        // no sense going any further here.
        if (strlen($pageBuffer) == 0)
            return FALSE;

        // Remove all the style and script tags and all &nbsp; so DOM Parser doesn't throw up
        $pageBuffer = preg_replace( array('/&nbsp;/is', '/<script.*?<\/script>/ism', '/<style.*?<\/style>/ims'),
                                    array(' ', '<script></script>', '<style></style>'), $pageBuffer );

        $xmlDoc = new DOMDocument();
        $xmlDoc->loadHTML($pageBuffer);
        // free pageBuffer
        $pageBuffer = "";

        // TITLE
        $e = $xmlDoc->getElementById('btAsinTitle');
        if (!$e)
            $e = $xmlDoc->getElementById('productTitle');
        if (!$e)
            $e = $xmlDoc->getElementById("ebooksProductTitle");
        if (!$e)
            $e = $xmlDoc->getElementById("title");
        if ($e) {
            // remove extra info at end "(Special Edition) (2018)..."
            $title = trim(preg_replace('/\s*\([^)]*\)/', "", $e->textContent));
            $this->addItemAttribute('title', $title);
        }

        // Use search info to fill in known fields
        $upcId = get_upc_code($search_attributes_r['search.title']);
        if ($upcId && $upcId != $this->getItemAttribute('title')) {
            $this->addItemAttribute('upc_id', $upcId);
        }

        $isbn = get_isbn_code($search_attributes_r['search.title']);
        if ($isbn) {
            if (strlen($isbn) == 10)
                $this->addItemAttribute('isbn', $isbn);
            else if (strlen($isbn) == 13)
                $this->addItemAttribute('isbn13', $isbn);
        }

        // Front Cover
        $e = $xmlDoc->getElementById('main-image');
        if (!$e)
            $e = $xmlDoc->getElementById('landingImage');
        if (!$e)
            $e = $xmlDoc->getElementById('imgBlkFront');
        if (!$e)
            $e = $xmlDoc->getElementById('ebooksImgBlkFront');
        if ($e)
            $this->addItemAttribute('imageurl', amazon_image2url($e));


        // Back Cover
        $e = $xmlDoc->getElementById('imgBlkBack');
        if ($e)
            $this->addItemAttribute('imageurlb', amazon_image2url($e));

        // Price
        $e = $xmlDoc->getElementById('buyBoxInner');
        if ($e)
            $e = $e->getElementsByTagName('span')->item(2);

        if (!$e) { // kindle eBook pricing
            $f = $xmlDoc->getElementById('buybox');
            if ($f)
                foreach($f->getElementsByTagName('tr') as $tr)
                    if (strpos($tr->getAttribute('class'), 'kindle-price') !== FALSE)
                        $e = $tr->getElementsByTagName('td')->item(1);
        }
        if ($e)
            $this->addItemAttribute('listprice', trim($e->childNodes[0]->textContent));

        // Get the mapped AMAZON index type
        $index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type),
                              strtolower($s_item_type));

        switch ($index_type) {
        case 'dvd':
        case 'bd':
        case 'vhs':
            $this->parse_amazon_video_data($search_attributes_r, $s_item_type, $xmlDoc);
            break;

        case 'videogames':
            $this->parse_amazon_game_data($search_attributes_r, $xmlDoc);
            break;

        case 'books':
            $this->parse_amazon_books_data($search_attributes_r, $xmlDoc);
            break;

        case 'music':
            $this->parse_amazon_music_data($search_attributes_r, $xmlDoc);
            break;

        default: //Not much here, but what else can we do?
            // @@ - error for unknown type
            break;
        }

        return TRUE;
    }

    /* BOOKS DATA */
    function parse_amazon_books_data($search_attributes_r, $xmlDoc) {
        // Author(s)
        $e = $xmlDoc->getElementById('bylineInfo');
        if ($e) {
            $f = $e->getElementsByTagName('table')->item(0);
            if ($f)
                $e = $f;
            if ($e) {
                $a = array_map(function($f) {
                    $l = $f->childNodes[0];
                    if (trim($l->textContent) == "")
                        $l = $f->getElementsByTagName('*')->item(0);
                    return trim($l->textContent);
                }, iterator_to_array($e->getElementsByTagName('span')));
                if ($a) {
                    $i = 0;
                    while ($i < count($a)) {
                        if (strpos($a[$i+1], 'Author') !== false)
                            $this->addItemAttribute('author', $a[$i++]);
                        ++$i;
                    }
                }
            }
        }

        // Binding
        $e = $xmlDoc->getElementById('title');
        if ($e)
            $e = $e->getElementsByTagName('span');
        if ($e) {
            $t = trim($e->item(1)->textContent);
            if (strpos($t, 'Paperback') !== FALSE)
                $this->addItemAttribute('binding', 'Paperback');
            else if (strpos($t, 'Hardcover') !== FALSE)
                $this->addItemAttribute('binding', 'Hardcover');
            else if (strpos($t, 'Kindle') !== FALSE)
                $this->addItemAttribute('binding', 'eBook');
            else
                $this->addItemAttribute('binding', 'Other');
        }

        // Synopsis
        $e = $xmlDoc->getElementById('bookDescription_feature_div');
        if ($e)
            $e = $e->getElementsByTagName('noscript')->item(0);
        if ($e)
            $this->addItemAttribute('synopsis', trim($e->textContent));

        // Info from Product Details
        $details = amazon_details($xmlDoc);
        if ($details) {
            // Publisher
            if (array_key_exists('Publisher', $details))
                $this->addItemAttribute('publisher', preg_replace(array('/\([^)]*\)/', '/;.*/'), '', $details['Publisher']));
            // Page Number
            foreach (['Paperback', 'Hardcover', 'Print Length', 'Mass Market Paperback'] as $i)
                if (array_key_exists($i, $details))
                    $this->addItemAttribute('no_pages', first_word($details[$i]));
            // ISBN
            if (array_key_exists('ISBN-10', $details))
                $this->addItemAttribute('isbn', $details['ISBN-10']);

            // ISBN-13
            if (array_key_exists('ISBN-13', $details))
                $this->addItemAttribute('isbn13', $details['ISBN13']);

            // Language
            if (array_key_exists('Lanuage', $details))
                $this->addItemAttribute('text_lang', $details['Language']);

            // Publication Date
            $year = false;
            if (array_key_exists('Publication Date', $details))
                $year = date('Y', strtotime($details['Publication Date']));
            else if (array_key_exists('Publisher', $details))
                if (preg_match("!.*?\(([^\)]*[0-9]+)\)!", $details['Publisher'], $regs))
                    $year = year_of($regs[1]);
            if ($year)
                $this->addItemAttribute('pub_year', $year);

            // GENRE
            if (array_key_exists("Amazon Best Sellers Rank", $details)) {
                $a = amazon_rank2genre($details["Amazon Best Sellers Rank"]);
                if ($a)
                    $this->addItemAttribute('bookgenre', $a);
            }

            // Series
            if (array_key_exists("Series", $details))
                $this->addItemAttribute("series", $details["Series"]);
        }
    }

    /* VIDEO DATA */
    function parse_amazon_video_data($search_attributes_r, $s_item_type, $xmlDoc) {

        // Info from Product Details
        $details = amazon_details($xmlDoc);
        if ($details) {
            // Actors, Studio
            foreach (['Actors', 'Studio'] as $N)
                $n = lcfirst($n);
            if (array_key_exists($N, $details))
                $this->addItemAttribute($n, preg_split('/\s*,\s*/', $details[$N]));

            // Director, Writer, Producer
            foreach (['Director', 'Writer', 'Producer'] as $n) {
                $N = $n.'s';
                if (array_key_exists($N, $details))
                    $this->addItemAttribute(lcfirst($n), preg_split('/\s*,\s*/', $details[$N]));
            }
            // Region
            if (array_key_exists('Region', $details) &&
                preg_match("/([0-9]+)/", $details['Region'], $matches))
                $this->addItemAttribute('dvd_region', $matches[1]);

            // Ratio
            if (array_key_exists('Ratio', $details))
                $this->addItemAttribute('ratio', strstr($details['Ratio'], ':', TRUE));

            // # of Discs
            if (array_key_exists('Number of discs', $details))
                $this->addItemAttribute('no_discs', $details['Number of discs']);

            // Rating
            if (array_key_exists('Rated', $details))
                $this->addItemAttribute('age_rating', first_word($details['Rated']));

            // Run Time
            if (array_key_exists('Run Time', $details))
                $this->addItemAttribute('run_time', first_word($details['Run Time']));

            // Year
            if (array_key_exists('DVD Release Date', $details))
                $this->addItemAttribute('year', year_of($details['DVD Release Date']));

            // GENRE
            if (array_key_exists("Amazon Best Sellers Rank", $details)) {
                $a = amazon_rank2genre($details["Amazon Best Sellers Rank"]);
                if ($a)
                    $this->addItemAttribute('genre', $a);
            }
        }
    }

    function parse_amazon_game_data($search_attributes_r, $xmlDoc) {

        // Rating
        $e = $xmlDoc->getElementById('vgRating_feature_div');
        if ($e)
            $this->addItemAttribute('gamerating', trim(""));
        // @@
        
        // Info from Product Details
        $details = amazon_details($xmlDoc);
        if ($details) {
            // Release Date
            if (array_key_exists("Release date", $details))
                $this->addItemAttribute('gamepbdate', date('D/M/Y',strtotime($details["Release date"])));

            // GENRE
            if (array_key_exists("Amazon Best Sellers Rank", $details)) {
                $a = amazon_rank2genre($details["Amazon Best Sellers Rank"]);
                if ($a)
                    $this->addItemAttribute('genre', $a);
            }
        }
    }

    function parse_amazon_music_data($search_attributes_r, $xmlDoc) {

        // Info from Product Details
        $details = amazon_details($xmlDoc);
        if ($details) {
            // GENRE
            if (array_key_exists("Amazon Best Sellers Rank", $details)) {
                $a = amazon_rank2genre($details["Amazon Best Sellers Rank"]);
                if ($a)
                    $this->addItemAttribute('genre', $a);
            }
        }
    }
}

?>
