<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell
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
*/
include_once("./functions/SitePlugin.class.inc");


class moviemeter extends SitePlugin
{
	function moviemeter($site_type)
	{
		parent::SitePlugin($site_type);
	}
	
	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r)
	{
	    if(strlen($search_vars_r['moviemeter_id'])>0)
		{
			$pageBuffer = $this->fetchURI("http://www.moviemeter.nl/film/".$search_vars_r['moviemeter_id']);

			if(strlen($pageBuffer)>0)
				$this->addListingRow(NULL,NULL,NULL,array('moviemeter_id'=>$search_vars_r['moviemeter_id']));

			return TRUE;
		}
		else
		{
			$FirstSearch = $this->fetchURI("http://www.moviemeter.nl/film/search/".rawurlencode($search_vars_r['title']));
			//this will display a page with some ajax functions/javascript redirects and a secret hash code.
			//first get the hash code
			$regx = "/new quickSearch\('(.*)'\);/";
			$matchCount = preg_match($regx,$FirstSearch,$matches);
			if ($matchCount==0) {
				return FALSE;
			}
			$SearchHash = $matches[1];
			//search again to get results
			$pageBuffer = $this->fetchURI("http://www.moviemeter.nl/calls/quicksearch.php?hash=".$SearchHash."&search=".rawurlencode($search_vars_r['title']));
		}
		
		if(strlen($pageBuffer)>0)
		{
			if(preg_match_all('/film;;([0-9]{1,6});;(.*) %/', $pageBuffer, $matches))
			{
				for ($i = 0; $i < count($matches[1]); $i++)
				{
					$movieid = $matches[1][$i];
					//url can be:
					//http://www.moviemeter.nl/images/covers/39000/39008.jpg
					//but also
					//http://www.moviemeter.nl/images/covers/1000/1375.jpg
					if(strlen($movieid)<4)
					{
						$imagedir = "0";
					}
					else
					{
						$imagedir = substr($movieid, 0, -3) . "000";
					}
					$thumbimg = "http://www.moviemeter.nl/images/covers/". $imagedir ."/". $movieid . ".jpg";;
					
					$title = urldecode($matches[2][$i]);
					$this->addListingRow($title, $thumbimg, NULL, array('moviemeter_id'=>$matches[1][$i]));
				}
				return TRUE;
			}
			else
			{
			  	// no matches
				return TRUE;
			}
		}
		else
		{
			// no matches(this is a JSON result page that can be 0 if no results)
			return TRUE;
		}		
	}

	
	function queryItem($search_attributes_r, $s_item_type)
	{
		$pageBuffer = $this->fetchURI("http://www.moviemeter.nl/film/".$search_attributes_r['moviemeter_id']);
		
		// no sense going any further here.
		if(strlen($pageBuffer)==0)
			return FALSE;
		
		//title year
		if(preg_match("!<head><title>([^\<]*)\(([0-9]*)\) - MovieMeter.nl<!", $pageBuffer, $matches))
		{
			$this->addItemAttribute('title', $matches[1]);
			$this->addItemAttribute('year', $matches[2]);
		}
		
		//image
		if(preg_match("!([^<]*)><img class=\"poster\"([^<]*)([^<]*)src=\"([^<]*)\" style=\"width:!", $pageBuffer, $matches))
		{
			if(starts_with($matches[4], 'http://'))
				$this->addItemAttribute('imageurl', $matches[4]);
			else
				$this->addItemAttribute('imageurl', 'http://'.$matches[4]);
		}
	
		//director
		if(preg_match("!geregisseerd door (.*)<br \/>met!", $pageBuffer, $matches))
		{
			$this->addItemAttribute('director', $matches[1]);
		}
		
		//  genre, runtime
		if(preg_match("!<div id=\"film_info\">([^<]*)<br \/>([^<]*)<br \/>([0-9]*) minuten!", $pageBuffer, $matches))
		{
			//runtime
			$this->addItemAttribute('run_time', $matches[3]);
			//genre
			$genre = explode(" / ", $matches[2]);
			$this->addItemAttribute('genre', $genre);
		}
		
		//cast, plot
		if(preg_match("!geregisseerd door <a href=\"(.*)</a><br \/>met (de stemmen van )*([^\<]*)<br \/><br \/>([^\<]*)!", $pageBuffer, $matches))
		{
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
