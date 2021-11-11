<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001,2013 by Jason Pell

	Google books site plugin
	Copyright (C) 2013 by Esben Madsen
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

    Change Information
    -------------------

    0.1 Initial version

 */
include_once("./lib/SitePlugin.class.php");

class gbooks extends SitePlugin {
	
	var $base_url='https://www.googleapis.com/books/v1/volumes';
	
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		// standard block of code to cater for refresh option, where item already has
		// reference to site item unique ID.
		if (strlen($search_vars_r['gbooks_id']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('gbooks_id' => $search_vars_r['gbooks_id']));
			return TRUE;
		}

// compose search query - Google Books API has the following definitions:
/* q - Search for volumes that contain this text string. 
 * There are special keywords you can specify in the search terms to search in particular fields, such as:
 *   intitle:     Returns results where the text following this keyword is found in the title.
 *   inauthor:    Returns results where the text following this keyword is found in the author.
 *   inpublisher: Returns results where the text following this keyword is found in the publisher.
 *   subject:     Returns results where the text following this keyword is listed in the category list of the volume.
 *   isbn:        Returns results where the text following this keyword is the ISBN number.
 *   lccn:        Returns results where the text following this keyword is the Library of Congress Control Number.
 *   oclc:        Returns results where the text following this keyword is the Online Computer Library Center number.
 */

		$search_query = array();
		if (strlen($search_vars_r['author']) > 0)
			$search_query[] = 'inauthor:'.urlencode('"'.$search_vars_r['author'].'"');
		if (strlen($search_vars_r['title']) > 0)
			$search_query[] = 'intitle:'.urlencode('"'.$search_vars_r['title'].'"');
		if (strlen($search_vars_r['isbn']) > 0)
			$search_query[] = 'isbn:'.urlencode($search_vars_r['isbn']);
		
		$result = json_decode($this->fetchURI($this->base_url . '?q=' . join('+',$search_query),true),true);		

// Get number of matches
		if(isset($result['totalItems'])&& ($result['totalItems'] > 0) ) {
			foreach($result['items'] as $item) {
				$vol = $item['volumeInfo'];
				$title ='';
					isset($vol['title'])		&& $title = $vol['title'] . ' ';
					isset($vol['subtitle'])	&& $title .= '- '. $vol['subtitle'] . ' ';
					isset($vol['publishedDate'])	&& $title .= '('.$vol['publishedDate'] . ') ';
					isset($vol['authors'])	&& $title .= 'by ' . join('; ', $vol['authors']);
				$cover_image_url = (isset($vol['imageLinks']) ? $vol['imageLinks']['thumbnail'] : NULL );
				$comments='';
					if(isset($vol['industryIdentifiers'])){
						$iids = array();
						foreach($vol['industryIdentifiers'] as $iid){
							$iids[] = $iid['type'] .': '. $iid['identifier'];
						}
						$comments = join(', ',$iids);
					}
				$attributes_r=array('gbooks_id' => $item['id']);
				$this->addListingRow($title, $cover_image_url, $comments, $attributes_r);
			}
		}
		return TRUE;
	}

	function queryItem($search_attributes_r, $s_item_type) {
		$result = json_decode($this->fetchURI($this->base_url . '/' . $search_attributes_r['gbooks_id'],true),true);		
//echo('GBOOKS debug: '.print_r($result,true).'<br/>');

		// make sure we actually got data
		if(!isset($result['id'])) {
			return FALSE;
		}

		$vol = $result['volumeInfo'];

		
		$title ='';
			isset($vol['title'])	&& $title = $vol['title'] . ' ';
			isset($vol['subtitle'])	&& $title .= '- '. $vol['subtitle'] . ' ';
		$this->addItemAttribute('title', $title);	
		
		// only year is allowd in the DB - google sometimes knows the exact date in the format YYYY-MM-DD
		isset($vol['publishedDate']) && $this->addItemAttribute('pub_year', substr($vol['publishedDate'],0,4));
		
		isset($vol['authors']) && $this->addItemAttribute('author', $vol['authors']);
		
		// dummy file name ending appended to the image since opendb requires ending
		isset($vol['imageLinks']) &&  $this->addItemAttribute('imageurl', $vol['imageLinks']['thumbnail'].'.jpg') ;
		
		
		if(isset($vol['industryIdentifiers'])){
			foreach($vol['industryIdentifiers'] as $iid){
				$iid['type'] == "ISBN_10" && $this->addItemAttribute('isbn', $iid['identifier']);
				$iid['type'] == "ISBN_13" && $this->addItemAttribute('isbn13', $iid['identifier']);
			}
		}

		isset($vol['categories']) && $this->addItemAttribute('genre', $vol['categories']);

		isset($vol['description']) && $this->addItemAttribute('synopsis', $vol['description']);
				
		isset($vol['publisher']) && $this->addItemAttribute('publisher', $vol['publisher']);

		isset($vol['pageCount']) && $this->addItemAttribute('no_pages', $vol['pageCount']);
		
		$lang_replace=array('!en!i' => 'english',
						    '!da!i' => 'danish'
							);  
		isset($vol['language']) && $this->addItemAttribute('text_lang', preg_replace(array_keys($lang_replace),array_values($lang_replace),$vol['language']));

				
		return TRUE;
	}
}
?>
