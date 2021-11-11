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
include_once("./lib/site/AmazonECS.class.php");

// avoid class with the included amazonecs class.
class odbamazonecs extends SitePlugin {
	private $siteAttributeType = NULL;
	private $isConfigured = FALSE;
	private $client;

	function __construct($site_type) {
		parent::__construct($site_type);

		$this->siteAttributeType = strtolower(fetch_site_attribute_type($site_type));

		$siteDomain = ifempty($this->getConfigValue('amazon_site_domain'), 'com');
		$accessKey = $this->getConfigValue('amazon_access_key', 0);
		$secretKey = $this->getConfigValue('amazon_secret_key', 0);

		// Can not continue if no amazon access key has been set
		if ($accessKey != '' && $secretKey != '') {
			$this->isConfigured = TRUE;

			$this->client = new AmazonECS($accessKey, $secretKey, $siteDomain, 'aztag-20');
			$this->client->returnType(AmazonECS::RETURN_TYPE_ARRAY);
		}
	}

	// Items per page ignored, offset ignored
	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (!$this->isConfigured) {
			return FALSE;
		}

		if (strlen($search_vars_r[$this->siteAttributeType]) > 0) {
			$context_search_vars[$this->siteAttributeType] = $search_vars_r[$this->siteAttributeType];

			$this->addListingRow(NULL, NULL, NULL, $context_search_vars);
			return TRUE;
		} else {
			$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));
			$response = $this->client->category($index_type)->responseGroup("Small,Images")->page($page_no)->search($search_vars_r['title']);

			if (is_array($response['Items']) && is_array($response['Items']['Request']) && $response['Items']['Request']['IsValid'] == 'True') {
				$this->setTotalCount($response['Items']['TotalResults']);

				foreach ($response['Items']['Item'] as $item_r) {
					$this->addListingRow($item_r['ItemAttributes']['Title'], $item_r['SmallImage']['URL'], NULL, array($this->siteAttributeType => $item_r['ASIN'], 'search.title' => $search_vars_r['title']));

				}
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	function queryItem($search_attributes_r, $s_item_type) {
		if (!$this->isConfigured) {
			return FALSE;
		}

		$idType = 'ASIN';

		// if search term is a 12 or 13 digits number then we assume it is an EAN number
		if (preg_match('/[0-9]{12,13}/', $search_attributes_r[$this->siteAttributeType])) {
			$idType = "EAN";
		}

		$response = $this->client->optionalParameters(array('IdType' => $idType))->responseGroup("ItemAttributes,Images,EditorialReview")->lookup($search_attributes_r[$this->siteAttributeType]);

		if (is_array($response['Items']) && is_array($response['Items']['Request']) && $response['Items']['Request']['IsValid'] == 'True') {

			$response = $response['Items']['Item'];

			if (is_array($response['LargeImage']) > 0) {
				$this->addItemAttribute('imageurl', $response['LargeImage']['URL']);
			}

			if (is_array($response['EditorialReviews']) && is_array($response['EditorialReviews']['EditorialReview'])) {
				foreach ($response['EditorialReviews']['EditorialReview'] as $review_r) {
					$review = $review_r['Content'];
					$review = str_replace("<br />", "\n", $review);
					$review = trim(html_entity_decode(strip_tags($review)));
					$this->addItemAttribute('blurb', $review);
				}
			}

			if (is_array($response['ItemAttributes'])) {
				$attributes = $response['ItemAttributes'];
				//print_r($attributes);

				$title = $attributes['Title'];
				if (($idx = strpos($title, "[Blu-ray")) !== FALSE) {
					$title = substr($title, 0, $idx);
				} else if (($idx = strpos($title, "(Blu-ray")) !== FALSE) {
					$title = substr($title, 0, $idx);
				}

				$this->addItemAttribute('title', $title);
				$this->addItemAttribute('upc_id', $attributes['UPC']);

				// TODO - figure out how to get Genre info???

				if (is_array($attributes['ListPrice'])) {
					$price = $attributes['ListPrice']['FormattedPrice'];
					if (starts_with($price, "$")) {
						$price = substr($price, 1);
					}
					$this->addItemAttribute('listprice', $price);
				}

				switch ($s_item_type) {
				case 'DVD':
				case 'BD':
					$this->parse_video_data($attributes);
					break;
				}
			}

			return TRUE;
		} else {
			return FALSE;
		}
	}

	function parse_video_data($attributes) {
		if (is_array($attributes['Languages'])) {
			foreach ($attributes['Languages'] as $lang_r) {
				if ($lang_r['Type'] == 'Subtitled') {
					$this->addItemAttribute('subtitles', $lang_r['Name']);
				} else {
					$this->addItemAttribute('audio_lang', $lang_r['Name']);
				}
			}
		}

		if (is_array($attributes['Actor'])) {
			foreach ($attributes['Actor'] as $actor) {
				$this->addItemAttribute('actors', $actor);
			}
		}

		if (is_array($attributes['Director'])) {
			foreach ($attributes['Director'] as $director) {
				$this->addItemAttribute('director', $director);
			}
		} else {
			$this->addItemAttribute('director', $attributes['Director']);
		}

		$this->addItemAttribute('ratio', $attributes['AspectRatio']);

		if (strlen($attributes['AudienceRating']) > 0) {
			$rating = $attributes['AudienceRating'];
			if (($idx = strpos($rating, "(")) !== FALSE) {
				$rating = substr($rating, 0, $idx);
			}
			$this->addItemAttribute('age_rating', $rating);
		}

		$this->addItemAttribute('studio', $attributes['Studio']);
		$this->addItemAttribute('no_discs', $attributes['NumberOfDiscs']);
		$this->addItemAttribute('dvd_rel_dt', $attributes['ReleaseDate']);

		if (is_array($attributes['RunningTime'])) {
			$this->addItemAttribute('run_time', $attributes['RunningTime']['_']);
		}

		if (is_array($attributes['Feature'])) {
			foreach ($attributes['Feature'] as $feature) {
				$this->addItemAttribute('dvd_extras', $feature);
			}
		}

	}
}
?>
