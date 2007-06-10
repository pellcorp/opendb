<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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
include_once("./functions/SitePlugin.class.inc");

function amazonItemFactory($itemType)
{
	switch($itemType)
	{		
		case "DVD":
			return new amazonDVD();
			break;
		case "Music":
			return new amazonMusic();
			break;
		case "Books":
			return new amazonBook();
			break;
		default;
			return new amazonItem();
	}
}

class amazonItem
{
	var $asin = "";
	var $detailPageURL = "";
	var $smallImageURL = "";
	var $mediumImageURL = "";
	var $largeImageURL = "";
	var $title = "";
	var $manufacturer = "";
	var $productGroup = "";
	var $releaseDate = "";	
	var $averageRating = "";
	// $inAncestprs is set to TRUE if we are in an ancestor tag
	// this is used to get only last level categories
	var $inAncestors = FALSE;
	// $browseNodeIsGenres is set to TRUE when BrowseNode name is equal to 'Genres'
	// We use it to keep only BrowseNodes which type is 'Genres'
	var $browseNodeIsGenres = FALSE;
	var $browseNodeName = "";
	var $genres = array();
	// To store editorial review and description
	// Todo need to improve reviews parsing
	var $reviews = array();
	
	function amazonItem()
	{
	}
	
	// $parentTag must be an empty string if there is no parent Tag
	// $continue must be TRUE if $data is a continuation of previous data from a current tag
	// or FALSE for the beginning of data for a new Tag
	function parsing($tag, $parentTag, $data, $continue)
	{
		switch($tag)
		{
			case "ASIN":
				if ($parentTag == 'Item')
					$this->asin .= $data;
				break;
			case "Title":
				if ($parentTag == 'ItemAttributes')
					$this->title .= $data;
				break;
			case "DetailPageURL":
				$this->detailPageURL .= $data;
				break;
			case "URL":
				// We only store the first set of images that is parsed,
				// this is done by testing if the extension jpg is already present in the field
				if ($parentTag == 'SmallImage' && substr($this->smallImageURL, -4) != ".jpg")
					$this->smallImageURL .= $data;
				elseif ($parentTag == 'MediumImage' && substr($this->mediumImageURL, -4) != ".jpg")
					$this->mediumImageURL .= $data;
				elseif ($parentTag == 'LargeImage' && substr($this->largeImageURL, -4) != ".jpg")
					$this->largeImageURL .= $data;
				break;
			case "Manufacturer":
				$this->manufacturer .= $data;
				break;
			case "ProductGroup":
				$this->productGroup .= $data;
				break;
			case "ReleaseDate":
				$this->releaseDate .= $data;
				break;
			case "AverageRating":
				$this->averageRating .= $data;
				break;
			case 'Name':
				if ($parentTag == 'BrowseNode' && !$this->inAncestors)
				{
					if ($continue)
						$this->genres[count($this->genres) - 1] .= $data;
					else
						$this->genres[count($this->genres)] = $data;
				}
				elseif ($parentTag == 'BrowseNode')
				{
					if ($continue)
						$this->browseNodeName .= $data;
					else
						$this->browseNodeName = $data;
					if ($this->browseNodeName == 'Genres')
						$this->browseNodeIsGenres = TRUE;
				}
				break;
			case 'Content':
				if ($parentTag == 'EditorialReview')
				{
					if ($continue)
						$this->reviews[count($this->reviews) - 1] .= $data;
					else
						$this->reviews[count($this->reviews)] = $data;
				}
				break;
		}		
	}

	function startTag($tagName)
	{
		switch ($tagName)
		{
			case 'Ancestors':
				$this->inAncestors = TRUE;
				break;
		}
	}
	
	function endTag($tagName)
	{
		switch ($tagName)
		{
			case 'Ancestors':
				// Remove last added genres if it is not effectively a genre browseNode
				if (!$this->browseNodeIsGenres && $this->inAncestors)
					array_pop($this->genres);
				$this->inAncestors = FALSE;
				$this->browseNodeIsGenres = FALSE;
				break;
		}
	}
	
	function setSitePluginAttributes(&$plugin)
	{
		$plugin->addItemAttribute('aecsasin', $this->asin);
		$plugin->addItemAttribute('title', $this->title);
		$plugin->addItemAttribute('imageurl', $this->largeImageURL);
		$plugin->addItemAttribute('year', substr($this->releaseDate, 0, 4));
		$plugin->addItemAttribute('genre', $this->genres);
		if (count($this->reviews) >= 1)
			$plugin->addItemAttribute('blurb', $this->reviews[0], TRUE);
	}
}

class amazonDVD extends amazonItem
{
	var $actors = array();
	var $directors = array();
	var $format = "";
	var $regionCode = "";
	var $aspectRatio = "";
	var $numberOfDiscs = "";
	var $audienceRating = "NR";
	var $runningTime = "";
	var $strudio = "";
	var $languages = array();
	var $theatricalReleaseDate = "";
	
	function parsing($tag, $parentTag, $data, $continue)
	{
		parent::parsing($tag, $parentTag, $data, $continue);

		switch($tag)
		{
			case "TheatricalReleaseDate":
				$this->theatricalReleaseDate .= $data;
				break;
			case 'Format':
				$this->format .= (($this->format != '' && !$continue)?', ':'') . $data;
				break;
			case 'Actor':
				if ($continue)
					$this->actors[count($this->actors) - 1] .= $data;
				else
					$this->actors[count($this->actors)] = $data;
				break;
			case 'Director':
				if ($continue)
					$this->directors[count($this->directors) - 1] .= $data;
				else
					$this->directors[count($this->directors)] = $data;
				break;
			case 'RegionCode':
				$this->regionCode .= $data;
				break;
			case 'AspectRatio':
				$this->aspectRatio .= $data;
				break;
			case 'NumberOfDiscs':
				$this->numberOfDiscs .= $data;
				break;
			case 'AudienceRating':
				$this->audienceRating .= $data;
				break;			
			case 'RunningTime':
				$this->runningTime .= $data;
				break;
			case 'Studio':
				$this->studio .= $data;
				break;
			case 'Name':
				if ($parentTag == 'Language')
				{
					$this->languages[count($this->languages) - 1]->name .= $data;
				}
				break;
			case 'Type':
				if ($parentTag == 'Language')
				{
					$this->languages[count($this->languages) - 1]->type .= $data;
				}
				break;
			case 'AudioFormat':
				if ($parentTag == 'Language')
				{
					$this->languages[count($this->languages) - 1]->audioFormat .= $data;
				}
				break;
		}
	}

	function startTag($tagName)
	{
		parent::startTag($tagName);
		switch ($tagName)
		{
			case 'Language':
				array_push($this->languages, new dvdLanguage());
				break;
		}
	}
	
	function setSitePluginAttributes(&$plugin)
	{
		parent::setSitePluginAttributes($plugin);
		if (strpos($this->format, 'NTSC') !== FALSE)
			$plugin->addItemAttribute('vid_format', 'NTSC');		
		elseif (strpos($this->format, 'PAL') !== FALSE)
			$plugin->addItemAttribute('vid_format', 'PAL');		
		elseif (strpos($this->format, 'SECAM') !== FALSE)
			$plugin->addItemAttribute('vid_format', 'SECAM');		
		if (strpos($this->format, 'Anamorphic') !== FALSE)
			$plugin->addItemAttribute('anamorphic', 'Y');		
		$plugin->addItemAttribute('actors', $this->actors);		
		$plugin->addItemAttribute('director', $this->directors);		
		$plugin->addItemAttribute('dvd_region', $this->regionCode);		
		$plugin->addItemAttribute('ratio', $this->aspectRatio);		
		$plugin->addItemAttribute('no_discs', $this->numberOfDiscs);		
		$plugin->addItemAttribute('age_rating', $this->audienceRating);	
		$plugin->addItemAttribute('studio', $this->studio);		
		$plugin->addItemAttribute('dvd_rel_dt', $this->releaseDate);		
		$plugin->addItemAttribute('run_time', $this->runningTime);		
		$plugin->addItemAttribute('amazon_review', $this->averageRating);		
		$plugin->addItemAttribute('audio_lang', $this->_generateAudioLanguagesArray());
		$plugin->addItemAttribute('subtitles', $this->_generateSubtitlesLanguagesArray());
	}
	
	function _generateAudioLanguagesArray()
	{		
		$resultArray = array();
		foreach ($this->languages as $language)
		{
			if ($language->type != 'Subtitled')
			{
				array_push($resultArray, $language->name);
			}
		}
		
		return $resultArray;
	}
	
	function _generateSubtitlesLanguagesArray()
	{		
		$resultArray = array();
		foreach ($this->languages as $language)
		{
			if ($language->type == 'Subtitled')
			{
				array_push($resultArray, $language->name);
			}
		}

		return $resultArray;
	}
}

class amazonMusic extends amazonItem
{
	var $artists = array();
	var $numberOfDiscs = "";
	var $label = "";
	var $tracks = array();
	
	function parsing($tag, $parentTag, $data, $continue)
	{
		parent::parsing($tag, $parentTag, $data, $continue);

		switch($tag)
		{
			case 'Artist':
				if ($continue)
					$this->artists[count($this->artists) - 1] .= $data;
				else
					$this->artists[count($this->artists)] = $data;
				break;
			case 'NumberOfDiscs':
				$this->numberOfDiscs .= $data;
				break;
			case 'MusicLabel':
				$this->label .= $data;
				break;
			case 'Track':
				if ($continue)
					$this->tracks[count($this->tracks) - 1] .= $data;
				else
					$this->tracks[count($this->tracks)] = $data;
				break;				
		}
	}

	function setSitePluginAttributes(&$plugin)
	{
		parent::setSitePluginAttributes($plugin);
		$plugin->addItemAttribute('artist', $this->artists);		
		$plugin->addItemAttribute('no_discs', $this->numberOfDiscs);
		$plugin->addItemAttribute('musiclabel', $this->label);
		$plugin->addItemAttribute('cdtrack', $this->tracks);
	}
}

class amazonBook extends amazonItem
{
	var $authors = array();
	var $isbn = "";
	var $publisher = "";
	var $numberOfPages = "";
	var $publicationDate = "";
	var $edition = "";
			
	function parsing($tag, $parentTag, $data, $continue)
	{
		parent::parsing($tag, $parentTag, $data, $continue);

		switch($tag)
		{
			case 'Author':
				if ($continue)
					$this->authors[count($this->authors) - 1] .= $data;
				else
					$this->authors[count($this->authors)] = $data;
				break;
			case 'ISBN':
				if ($parentTag == "ItemAttributes")
					$this->isbn .= $data;
				break;				
			case 'Publisher':
				$this->publisher .= $data;
				break;				
			case 'PublicationDate':
				$this->publicationDate .= $data;
				break;				
			case 'Edition':
				$this->edition .= $data;
				break;				
			case 'NumberOfPages':
				$this->numberOfPages .= $data;
				break;				
		}
	}

	function setSitePluginAttributes(&$plugin)
	{
		parent::setSitePluginAttributes($plugin);
		$plugin->addItemAttribute('author', $this->authors);		
		$plugin->addItemAttribute('isbn', $this->isbn);
		$plugin->addItemAttribute('publisher', $this->publisher);		
		$plugin->addItemAttribute('edition', $this->edition);		
		$plugin->addItemAttribute('nb_pages', $this->numberOfPages);	
		$plugin->addItemAttribute('pub_date', substr($this->publicationDate, 0, 4));
	}
}

class dvdLanguage 
{
	var $name = "";
	var $audioFormat = "";
	var $type = "";
}


class amazonecs extends SitePlugin
{
	var $itemType = "";
	// Members below are used for XML parsing
	var $currentTags = array();
    var $itemsArray = array();
	var $itemCount = 0;
	var $inItem = FALSE;
	var $amazonErrorCode = "";
	var $amazonError = FALSE;
	var $continue = FALSE;

	var $siteDomain = NULL;
	var $siteAttributeType = NULL;

	function amazonecs($site_type)
	{
		parent::SitePlugin($site_type);
		
		$this->siteAttributeType = strtolower(fetch_site_attribute_type($site_type));
		$this->siteDomain = $this->getConfigValue('amazon_site_domain');
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r)
	{
		if(strlen($search_vars_r[$this->siteAttributeType])>0)
		{
			$context_search_vars[$this->siteAttributeType] = $search_vars_r[$this->siteAttributeType];
			
			// if site domain is not defined, then its assumed that a domain input field will be provided.
			if(empty($siteDomain))
			{
				$context_search_vars['aecsdomain'] = $search_vars_r['aecsdomain'];
			}
			
			$this->addListingRow(NULL, NULL, NULL, $context_search_vars);
			return TRUE;
		}
		else
		{
			$siteDomain = ifempty($this->siteDomain, $search_vars_r['aecsdomain']);
			
			$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));

			// Can not continue if no amazon access key has been set
			if ($this->getConfigValue('amazon_access_key', 0) == '')
				return FALSE;
				
			$queryUrl = "http://webservices.amazon.".$siteDomain."/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".$this->getConfigValue('amazon_access_key', 0)."&Operation=ItemSearch&SearchIndex=".$index_type."&ResponseGroup=Small,Images&ItemPage=".$page_no."&Keywords=".rawurlencode($search_vars_r['title']);
			
			$pageBuffer = $this->fetchURI($queryUrl);
			
			// no sense going any further here.
			if(strlen($pageBuffer)==0)
				return FALSE;

			$this->amazonParseXML($pageBuffer);
			if (!$this->amazonError)
			{
				foreach ($this->itemsArray as $item)
				{
					$context_search_vars[$this->siteAttributeType] = $item->asin;
			
					// if site domain is not defined, then its assumed that a domain input field will be provided.
					if(empty($this->siteDomain))
					{
						$context_search_vars['aecsdomain'] = $search_vars_r['aecsdomain'];
					}
			
					$this->addListingRow($item->title, $item->smallImageURL, NULL, $context_search_vars);
				}
					
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
	}
	
	function queryItem($search_attributes_r, $s_item_type)
	{
		$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));
		$this->itemType = $index_type;

		// Can not continue if no amazon access key has been set
		if ($this->getConfigValue('amazon_access_key', 0) == '')
			return FALSE;

		$siteDomain = ifempty($this->siteDomain, $search_attributes_r['aecsdomain']);
		
		// assumes we have an exact match here
		$queryUrl = "http://webservices.amazon.".$siteDomain."/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".$this->getConfigValue('amazon_access_key', 0)."&Operation=ItemLookup&ResponseGroup=Large&ItemId=".$search_attributes_r[$this->siteAttributeType];
   		$pageBuffer = $this->fetchURI($queryUrl);

		// no sense going any further here.
   		if(strlen($pageBuffer)==0)
   			return FALSE;
		
		$this->amazonParseXML($pageBuffer);
		if (!$amazonError)
		{
			$item = $this->itemsArray[0];
			
			$item->setSitePluginAttributes($this);
			
			if(empty($this->siteDomain))
			{
				$this->addItemAttribute('aecsdomain', $search_attributes_r['aecsdomain']);
			}
			
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}	


	//******************************************
	// Xml parsing functions
	//******************************************
	// Parses xml answer from Amazon web service
	// $xmlcontent is a string containing xml answer
	function amazonParseXML($xmlContent)
	{
		// Reinitialize parsing values
		$this->itemsArray = array();
		$this->inItem = FALSE;
		$this->currentTags = array();
		$this->itemCount = 0;
		$this->amazonErrorCode = "";
		$this->amazonError = FALSE;
		$this->continue = FALSE;		
		
		$xmlParser = xml_parser_create();
		
		xml_set_object($xmlParser,$this);
		xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, false); 
		xml_set_element_handler($xmlParser, "amazonStartTag", "amazonEndTag"); 
		xml_set_character_data_handler($xmlParser, "amazonCharacterData");
		
		xml_parse($xmlParser, $xmlContent, true);
		
		xml_parser_free($xmlParser);
	}

	// Sets the current XML Tag, and pushes itself onto the Tag hierarchy
	function amazonStartTag($parser, $name, $attrs)
	{
		array_push($this->currentTags, $name);
		
		if($name == "Item")
		{
			$this->itemCount += 1;
			array_push($this->itemsArray, amazonItemFactory($this->itemType));
			$this->inItem = TRUE;
		}		
		elseif ($this->inItem)
		{			
			$this->itemsArray[$this->itemCount - 1]->startTag($name);
		}			
	} 

	function amazonCharacterData($parser, $data)
	{
		$currentCount = count($this->currentTags);
		$currentTag = $this->currentTags[$currentCount-1];
		$parentTag = "";
		if ($currentCount > 1)
			$parentTag = $this->currentTags[$currentCount - 2];

		$data = mb_convert_encoding($data, "ISO-8859-1", "UTF-8");		
			
		if ($this->inItem)
		{					
			$this->itemsArray[$this->itemCount - 1]->parsing($currentTag, $parentTag, $data, $this->continue);
		}
		else
		{
			switch($currentTag)
			{
				case "TotalResults":
					$this->setTotalCount(intval($data));
					break;
				case "Code":
					// Todo need to manage Amazon Errors
					if ($parentTag == 'Error' &&
						$data != "AWS.ECommerceService.NoExactMatches")
						$this->amazonError = TRUE;
					break;				
			}
		}

		$this->continue = TRUE;
	} 

   // If the XML Tag has ended, it is popped off the hierarchy
   function amazonEndTag($parser, $name)
   {
		$currentCount = count($this->currentTags);
		if($this->currentTags[$currentCount-1] == $name)
		{
			array_pop($this->currentTags);
		}

		if($name == "Item")
		{
			$this->inItem = FALSE;
		}
		elseif ($this->inItem)
		{					
			$this->itemsArray[$this->itemCount - 1]->endTag($name);
		}
				
		$this->continue = FALSE;
   }
}
?>