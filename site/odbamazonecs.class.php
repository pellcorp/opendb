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
include_once("./site/AmazonECS.class.php");

// avoid class with the included amazonecs class.
class odbamazonecs extends SitePlugin {
	private $siteAttributeType = NULL;
	private $isConfigured = FALSE;
	private $client;
	
	function odbamazonecs($site_type) {
		parent::SitePlugin($site_type);
		
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
		
		if(strlen($search_vars_r[$this->siteAttributeType])>0) {
			$context_search_vars[$this->siteAttributeType] = $search_vars_r[$this->siteAttributeType];
			
			$this->addListingRow(NULL, NULL, NULL, $context_search_vars);
			return TRUE;
		} else {
			$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));

			$response = $this->client->category($index_type)->responseGroup("Small,Images")->page($page_no)->search($search_vars_r['title']);
			
			
			if (is_array($response['Items']) && is_array($response['Items']['Request']) 
					&& $response['Items']['Request']['IsValid'] == 'True') {
				$this->setTotalCount($response['Items']['TotalResults']);
				
				while(list(,$item_r) = each($response['Items']['Item'])) {
					$this->addListingRow( 
							$item_r['ItemAttributes']['Title'],
							$item_r['SmallImage']['URL'], NULL, 
							array($this->siteAttributeType=>$item_r['ASIN'], 
									'search.title'=>$search_vars_r['title']));
					
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
		
		$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));
		
		
	}
}
?>