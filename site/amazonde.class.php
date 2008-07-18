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
include_once("./site/amazonutils.php");

function mymonthtonumber($str)
{
	switch($str) {
		case 'Januar':
			$ret=1;
			break;
		case 'Februar':
			$ret=2;
			break;
		case 'M�rz':
			$ret=3;
			break;
		case 'April':
			$ret=4;
			break;
		case 'Mai':
			$ret=5;
			break;
		case 'Juni':
			$ret=6;
			break;
		case 'Juli':
			$ret=7;
			break;
		case 'August':
			$ret=8;
			break;
		case 'September':
			$ret=9;
			break;
		case 'Oktober':
			$ret=10;
			break;
		case 'November':
			$ret=11;
			break;
		case 'Dezember':
			$ret=12;
			break;
	}
	return $ret;
}

function mystrip_tags($str)
{
	//As seen on http://www.php.net/manual/de/function.preg-replace.php ....
	$search = array ("'<[\/\!]*?[^<>]*?>'si",	// Strip out html tags
			"'([\r\n])[\s]+'",		// Strip out white space
			"'&(quot|#34);'i",		// Replace html entities
			"'&(amp|#38);'i",
			"'&(lt|#60);'i",
			"'&(gt|#62);'i",
			"'&(nbsp|#160);'i",
			"'&(iexcl|#161);'i",
			"'&(cent|#162);'i",
			"'&(pound|#163);'i",
			"'&(copy|#169);'i",
			"'&#(\d+);'e");			// evaluate as php

	$replace = array ("", 
			"\\1",
			"\"", 
			"&",
			"<",
			">",
			" ",
			chr(161),
			chr(162),
			chr(163),
			chr(169),
			"chr(\\1)");

	return trim(preg_replace($search, $replace, $str));
}

class amazonde extends SitePlugin
{
	function amazonde($site_type)
	{
		parent::SitePlugin($site_type);
	}
	
	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r)
	{
		if(strlen($search_vars_r['amazdeasin'])>0)
		{
			$this->addListingRow(NULL, NULL, NULL, array('amazdeasin'=>$search_vars_r['amazdeasin']));
			return TRUE;
		}
		else
		{
			// Get the mapped AMAZON index type
			$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));
			
			$queryUrl = 'http://www.amazon.de/exec/obidos/external-search?index='.$index_type.'&keyword='.rawurlencode($search_vars_r['title']).'&sz='.$items_per_page.'&pg='.$page_no;

			$pageBuffer = $this->fetchURI($queryUrl);
		}
		
		if(strlen($pageBuffer)>0)
		{
			$amazdeasin = FALSE;
			
			// check for an exact match, but not if this is second page of listings or more
			if(!$this->isPreviousPage())
			{
				if (preg_match("/ASIN: <font>(\w{10})<\/font>/", $pageBuffer, $regs))
				{
					$amazdeasin = trim($regs[1]);
				}
				else if (preg_match("/ASIN: (\w{10})/", strip_tags($pageBuffer), $regs))
				{
					$amazdeasin = trim($regs[1]);
				}
				else if (preg_match ("/ISBN: ([^;]+);/", strip_tags($pageBuffer), $regs)) // for books, ASIN is the same as ISBN
				{
					$amazdeasin = trim ($regs[1]);
				} 
			}
			
			// exact match
			if($amazdeasin!==FALSE)
			{
				// single record returned
				$this->addListingRow(NULL, NULL, NULL, array('amazdeasin'=>$amazdeasin));
				
				return TRUE;
			}
			else
			{
				$pageBuffer = preg_replace('/[\r\n]+/', ' ', $pageBuffer);
			
				//<td class="resultCount">1-24 von 22.345 Ergebnissen</td>
				//1-24 von 66 Ergebnissen
				if(preg_match("/<td class=\"resultCount\">[0-9]+[\s]*-[\s]*[0-9]+ von ([0-9,\.]+) Ergebnissen<\/td>/i", $pageBuffer, $regs) || 
						preg_match("/<td class=\"resultCount\">([0-9]+) Ergebnisse<\/td>/i", $pageBuffer, $regs))
				{
					// store total count here.
					$this->setTotalCount($regs[1]);

					// 1 = img, 2 = href, 3 = title					
					if(preg_match_all("!<td class=\"imageColumn\"[^>]*>.*?".
									"<img src=\"([^\"]+)\"[^>]*>".
									".*?".
									"<a href=\"([^\"]+)\"[^>]*><span class=\"srTitle\">([^<]+)</span></a>!m", $pageBuffer, $matches))
					{
						for($i=0; $i<count($matches[0]); $i++)
						{
							if(preg_match("!/dp/([^/]+)/!", $matches[2][$i], $regs))
							{
								$this->addListingRow($matches[3][$i], $matches[1][$i], NULL, array('amazdeasin'=>$regs[1]));
							}
						}
					}
				}
			}
			
			//default
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	* 
	*/
	function queryItem($search_attributes_r, $s_item_type)
	{
		$pageBuffer = $this->fetchURI("http://www.amazon.de/exec/obidos/ASIN/".$search_attributes_r['amazdeasin']);
		
		// no sense going any further here.
		if(strlen($pageBuffer)==0)
			return FALSE;
		
		$pageBuffer = preg_replace('/[\r\n]+/', ' ', $pageBuffer);
		$pageBuffer = preg_replace('/>[\s]*</', '><', $pageBuffer);
		
		//if(preg_match("/<title>.*Amazon\.de: ([^:]*):(.*)<\/title>/s", $pageBuffer, $regs))
		if(preg_match("/<span id=\"btAsinTitle\">([^<]+)<\/span>/s", $pageBuffer, $regs) ||
				preg_match("/<b class=\"sans\">([^<]+)<\/b>/s", $pageBuffer, $regs) || 
				preg_match("/<b class=\"sans\">([^<]+)<!--/s", $pageBuffer, $regs))
		{
		    $title = trim($regs[1]);

			// If extra year appended, remove it and just get the title.
			if(preg_match("/(.*)\([0-9]+\)$/", $title, $regs2))
				$title = $regs2[1];

			$title = str_replace("\"", "", $title);

			$this->addItemAttribute('title', $title);
		}

		$imageBuffer = $this->fetchURI("http://www.amazon.de/gp/product/images/".$search_attributes_r['amazdeasin']."/");
		if($imageBuffer!==FALSE)
	    {
	        //fetchImage("alt_image_0", "http://images.amazon.com/images/P/B0000640RX.01._SS400_SCLZZZZZZZ_.jpg" );
	        if(preg_match_all("!fetchImage\(\"[^\"]+\", \"([^\"]+)\"!", $imageBuffer, $regs))
	        {
	        	$this->addItemAttribute('imageurl', $regs[1]);
	        } //<img src="http://images.amazon.com/images/P/B000FMH8RG.01._SS500_SCLZZZZZZZ_V52187861_.jpg" id="prodImage" />
	        else if(preg_match_all("!<img src=\"([^\"]+)\" id=\"prodImage\" />!", $imageBuffer, $regs))
	        {
	        	$this->addItemAttribute('imageurl', $regs[1]);	
	        }
	    }
	
		//<td class="listprice">EUR 9,99 </td>
		if (preg_match("/<td class=\"listprice\">EUR ([^<]*) <\/td>/i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('listprice', preg_replace('/,/', '.', trim($regs[1])));
		}
		
		if (preg_match("!<b class=\"price\">EUR ([0-9,]+)[\s]*</b>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('price', $regs[1]);
		}
		
		if(preg_match("!<a href=\"http://www.amazon.de/gp/product/product-description/".$search_attributes_r['amazdeasin']."/[^>]*>Alle Rezensionen</a>!", $pageBuffer, $regs))
		{
			$reviewPage = $this->fetchURI("http://www.amazon.de/gp/product/product-description/".$search_attributes_r['amazdeasin']."/reviews/");
			if(strlen($reviewPage)>0)
			{
				$reviews = parse_amazon_reviews($reviewPage);
				if(is_not_empty_array($reviews))
				{
					$this->addItemAttribute('blurb', $reviews);
				}	
			}
		}
		else
		{
			$reviews = parse_amazon_reviews($pageBuffer);
			if(is_not_empty_array($reviews))
			{
				$this->addItemAttribute('blurb', $reviews);
			}
		}
		
		if(preg_match("!<li><b>Durchschnittliche Kundenbewertung:</b>[\s]*<img src=\".*?/stars-([^\.]+).!i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('amznrating', str_replace('-', '.', $regs[1])) ;
		}
		
		// Get the mapped AMAZON index type
		$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));
				
		switch($index_type)
		{
			case 'dvd-de':
			case 'vhs-de':
				$this->parse_amazon_video_data($search_attributes_r, $s_item_type, $pageBuffer);
				break;
			
			case 'video-games-de':
				$this->parse_amazon_game_data($search_attributes_r, $pageBuffer);
				break;
				
			case 'books-de':
				$this->parse_amazon_books_data($search_attributes_r, $pageBuffer);
				break;
				
			case 'music-de':
				$this->parse_amazon_music_data($search_attributes_r, $pageBuffer);
				break;
		}
	
		return TRUE;
	}
	
	function parse_amazon_game_data($search_attributes_r, $pageBuffer)
	{
		// Publisher extraction block
		if(preg_match("!von <a href=\".*?field-keywords=[^\"]*\">([^<]*)</a>!i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('gamepblshr', $regs[1]);
		}
	
		//<b>Plattform:</b> &nbsp;<img src="http://ec1.images-amazon.com/images/G/03/videogames/icons/browse-icon-windows._V45804877_.gif" style="" align="absmiddle" border="0" height="20" width="20">&nbsp;Windows 98 /  2000 /  XP</li>
		if (preg_match("!<b>Plattform:</b> &nbsp;[^<]*<img src=\"([^\"]+)\"[^<]*>([^<]+)</div>!mi", $pageBuffer, $regs))
		{
			// Different combo's of windows, lets treat them all as windows.
			if(strpos($regs[2], "Windows")!==FALSE)
				$platform = "Windows";
			else
				$platform = trim($regs[2]);

			$this->addItemAttribute('gamesystem', $platform);
		}
		
		//<li><b>USK-Einstufung:</b> <a href="/gp/help/customer/display.html/028-8658436-7225309?ie=UTF8&node=200039890">Keine Jugendfreigabe gemäß § 14 JuSchG</a></li>
		// Rating extraction block
		if (preg_match("!<b>USK-Einstufung:</b>.*?<a href=\"[^\"]*\">([^<]*)</a>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('gamerating', strtoupper($regs[1]));
		}
	
		//<b> Erscheinungsdatum:</b> 5. Oktober 2005
		if (preg_match("!<b> Erscheinungsdatum:</b>([^<]*)</li>!si", $pageBuffer, $regs))
		{
			$regs2 = explode(' ', trim($regs[1]));
			$this->addItemAttribute('gamepbdate', substr($regs2[0],0,strlen($regs2[0])-1) . '/' . mymonthtonumber($regs2[1]) . '/' . $regs2[2]);
		}
	
		if (preg_match("/Unsere Besten<\/a>\n&gt; <a href=(.*?)[^>]>(.*?)[^<]<\/a>/", $pageBuffer, $regs2))
		{
			switch($regs2[2])
			{
				case 'PC Adventures & Rollenspiel':
					$regs3='Adventure';
					break;
				case 'PC Actionspiel':
					$regs3='Action';
					break;
				case 'PC Simulatione':
					$regs3='FlightSimulation';
					break;
				case 'PC Rennspiel':
					$regs3='Race';
					break;
				case 'PC Sportspiel':
					$regs3='Sports';
					break;
				case 'PC Strategiespiel':
					$regs3='RPG';
					break;
				default:
					break;
			}
			if (isset($regs3))
			{
				$this->addItemAttribute('genre', $regs3);
				unset($regs3);
			}
		}
	
		// Features extraction block
		if(preg_match("/<b>Features:<\/b>[\s]<ul>(.+?)<\/ul>/si", $pageBuffer, $featureblock))
		{
			$features = '';
			
			if(preg_match_all("/<li>(.*?)<li>/si", $featureblock[1], $matches))
			{
				// generate a list of features
				for($i = 0; $i < count($matches[1]); $i++)
				{
					$features .= mystrip_tags($matches[1][$i])."\n";
				}
			}
			
			if(strlen($features)>0)
			{
				$this->addItemAttribute('features', $features);
			}
		}
	}
	
	function parse_amazon_music_data($search_attributes_r, $pageBuffer)
	{
		if(preg_match("!<meta name=\"description\" content=\"([^\"]*)\">!i", $pageBuffer, $regs))
		{
			$contents = explode(",", $regs[1]);
			if(is_not_empty_array($contents))
			{
				// the artist is the last entry in the description.
				$this->addItemAttribute('artist', $contents[1]);
			}
		}
	
		if( ($sqidx = strpos($this->getItemAttribute('title'), "["))!==FALSE)
		{
			$this->addItemAttribute('comments', str_replace(array('[',']'), array("\n",''), substr($this->getItemAttribute('title'),$sqidx)));
			$this->replaceItemAttribute('title', substr($this->getItemAttribute('title'),0,$sqidx));
		}
	
		if(preg_match("!<b>Erscheinungsdatum:</b>&#160;([^<)]+)<br>!sU", $pageBuffer, $regs))
		{
			$this->addItemAttribute('release_dt', $regs[1]);
			
			if(preg_match("!([0-9]+)$!", $this->getItemAttribute('release_dt'), $regs2))
			{
				$this->addItemAttribute('year', $regs2[1]);
			}
		}
	
		if(preg_match("!<B>Label:</B>([^<]+)<BR>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('musiclabel', $regs[1]);
		}
	
		if(preg_match("!CD-Anzahl\: (.*?)\)!si", $pageBuffer, $regs))
		{
			$this->addItemAttribute('no_discs', $regs[1]);
		}
	
		if(is_numeric($this->getItemAttribute('no_discs')) && $this->getItemAttribute('no_discs') > 1)
		{
			for($i=0; $i<$this->getItemAttribute('no_discs'); $i++)
			{
				$cdtracks[$i] = $this->parse_music_tracks("Titelverzeichnis", $i+1, $pageBuffer);
				if($cdtracks[$i] == NULL)
					$cdtracks[$i] = $this->parse_music_tracks("H�rbeispiele", $i+1, $pageBuffer);
			}
			
			// Now coalesce into single cdtracks array
			if(is_not_empty_array($cdtracks))
			{
				for($i=0; $i<count($cdtracks); $i++)
				{
					if(is_not_empty_array($cdtracks[$i]))
					{
						for($j=0; $j<count($cdtracks[$i]); $j++)
						{
							$this->addItemAttribute('cdtrack', $cdtracks[$i][$j]);
						}
					}
				}
			}
		}
		else
		{ // one disc
			$cdtracks = $this->parse_music_tracks("Titelverzeichnis", NULL, $pageBuffer);
			if($cdtracks!=NULL)
			{
				$this->addItemAttribute('cdtrack', $this->parse_music_tracks("Titelverzeichnis", NULL, $pageBuffer));
			}
			else
			{
				$this->addItemAttribute('cdtrack', $this->parse_music_tracks("H�rbeispiele", NULL, $pageBuffer));
			}
		}			
	}
	
function parse_amazon_books_data($search_attributes_r, $pageBuffer)
	{
		// Author(s) and/or Editor(s)
		if (preg_match('|von <a href=".*?">(.*?)</a>|si', $pageBuffer, $regs))
		{
			$this->addItemAttribute('author', $regs[1]);
		}

		// ISBN-10 (Note: there is also an ISBN-13; just change 10 to 13 to get it)
		if (preg_match("/<li><b>ISBN-10:<\/b>(.*?)<\/li>/", $pageBuffer, $regs2))
		{
			$this->addItemAttribute('isbn', $regs2[1]);
		}

		// Publisher, Edition no., Publication date
		if (preg_match("/<li><b>Verlag:<\/b>(.*?); Auflage:(.*?)\((.*?)\)<\/li>/", $pageBuffer, $regs2))
		{
			$this->addItemAttribute('publisher', $regs2[1]);
			$this->addItemAttribute('edition', $regs2[2]);

			// All we want of publication date is the year
			if (preg_match("/([0-9]+)$/", $regs2[3], $regs3))
				$this->addItemAttribute('pub_date', $regs3[1]);
		}

		// Book type (edition?), Pages
		if (preg_match("/<li><b>([Gebundene Ausgabe|Kalender|Taschenbuch|Broschiert|CD]+?):<\/b>(.*?)Seiten<\/li>/", $pageBuffer, $regs))
		{
			//$this->addItemAttribute('type', $regs[1]);
			$this->addItemAttribute('nb_pages', $regs[2]);
		}

		// Category -- hmmm, Amazon seems to have removed genre information from books
		if (preg_match('|<b>Kategorie\(n\):</b> <a .*?>(.*?)</a>|', $pageBuffer, $regs2))
		{
			$this->addItemAttribute('genre', $regs2[1]);
		}

		// Plot (Amazon blurb)
		$this->addItemAttribute('blurb', $this->parse_amazon_book_blurb($pageBuffer));

		// Editorial reviews
		if (preg_match("/<a href=([^\"]+)>Alle Rezensionen ansehen/i", $pageBuffer, $regs))
		{
			$reviewPage = $this->fetchURI('http://www.amazon.de/' . $regs[1]);

			// Fetch the information if page not empty
			if (strlen($reviewPage) > 0)
			{
				$this->addItemAttribute('blurb', $this->parse_amazon_book_blurb($reviewPage));
			}
		}
	}
	
	function parse_amazon_video_data($search_attributes_r, $s_item_type, $pageBuffer)
	{
		if(preg_match("!<li><b>Produktion:[\s]*</b>([^<]*)</li>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('year', $regs[1]);
		}
		
		//<li><b>DVD-Erscheinungstermin:</b>  1. Dez. 2005</li>
		if(preg_match("!<li><b>DVD-Erscheinungstermin:[\s]*</b>([^<]*)</li>!", $pageBuffer, $regs))
		{
			// Get year only, for now.  In the future we may add ability to
			// convert date to local date format.
			$regs = preg_split("/[\s,]+/", trim($regs[1]));
			if(preg_match("/([0-9]+)$/m", $regs[2], $regs2))
				$this->addItemAttribute('dvd_rel_dt', $regs2[1]);
				
			if($this->getItemAttribute('year') === FALSE)
			{
				$this->addItemAttribute('year', $regs2[1]);
			}
		}
		
		//<li><b>Spieldauer:</b>  482 Minuten</li>
		// Duration extraction block
		if(preg_match("!<li><b>Spieldauer:[\s]*</b>[\s]*([0-9]+) Minuten</li>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('run_time', $regs[1]);
		}
		
		// Rating extraction block
		if(preg_match("!<li><b>FSK:[\s]*</b>([^<]*)</li>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('age_rating', $regs[1]);
		}
		
		// Actor extraction block
		$this->addItemAttribute('actors', parse_amazon_video_people("Darsteller",$pageBuffer));
	
		// Director extraction block
		$this->addItemAttribute('director', parse_amazon_video_people("Regisseur(e)",$pageBuffer));
		
		if(preg_match("!<li><b>Studio:[\s]*</b>([^<]*)</li>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('studio', $regs[1]);
		}
		
		if(preg_match("!<li><b>Region:[\s]*</b>Region ([0-6])</li>!", $pageBuffer, $regs))
			$this->addItemAttribute('dvd_region', $regs[1]);
		else
			$this->addItemAttribute('dvd_region', '2'); //otherwise assume region 2
		
		//<li><b>DVD Features:</b><ul><li>ALIEN - DIE WIEDERGEBURT: Kommentare, Pre- Production-Featurettes, Multi- Angle-Segmente, Produktions- Dokumentationen, Post- Production-Featurettes</li><li>ALIEN 3: Kommentare, Pre-Production-Featurettes, Multi-Angle-Segmente, Produktions-Dokumentationen, Post-Production-Featurettes</li><li>ALIEN: Infos von Ridley Scott zur Director's Cut-Version, Kommentare, Pre-Production-Featurettes, Produktions-Dokumentation, Post-Production-Featurettes, unveröffentlichte Szenen</li><li>ALIENS - DIE RÜCKKEHR: Infos von James Cameron zur Extended Version, Kommentare, Pre-Production- Featurettes, Multi-Angle-Animatics, Produktions-Dokumentationen, Post-Production-Featurettes</li><li>Jeder der vier Filme ist als Extended-Version zu sehen. Dieses Set enthält keine zusätzliche Bonus-DVD</li></ul>

		// Edition details block - 'dvd_extras' attribute
		if(preg_match("!<li><b>DVD Features:[\s]*</b><ul>(.*?)</ul>!si", $pageBuffer, $regs))
		{
			// TODO "anamorphic" Other formating in Amazon EU
			// "no_discs" NOT Supported by Amazon EU
			
			if(preg_match_all("!<li>(.*?)</li>!i",$regs[1], $matches))
			{
				$dvd_extras = NULL;
				
				while(list(,$item) = @each($matches[1]))
				{
					$item = html_entity_decode(strip_tags($item));
	
					// Don't include the region, no_discs, anamorphic
					if(strpos($item, "Ton:")===FALSE)  // audio languages already parse this.
					{
						$dvd_extras[] = $item;
					}
				}
				
				if(is_array($dvd_extras))
				{
					$this->addItemAttribute('dvd_extras', implode("\n", $dvd_extras));
				}
			}
		}
		
		if( ($sqidx = strpos($this->getItemAttribute('title'), "["))!==FALSE)
		{
			$this->replaceItemAttribute('title', substr($this->getItemAttribute('title'),0,$sqidx));
			
			$comments = str_replace(array('[',']'), array("\n",''), substr($this->getItemAttribute('title'),$sqidx));
			
			$dvd_extras = $this->getItemAttribute('dvd_extras');
			if(strlen($dvd_extras)>0)
				$this->replaceItemAttribute('dvd_extras', $comments."\n".$dvd_extras);
			else
				$this->addItemAttribute('dvd_extras', $comments);
		}
		
		// "imdb_id" Not supported in Amazon EU
	
		// All Amazon.de items should be PAL!
		$this->addItemAttribute('vid_format', 'PAL');
	
		//Plot (Amazon blurb)
		$this->addItemAttribute('blurb', $this->parse_amazon_video_blurb($pageBuffer));
	
		// Plot (Costumer Blurb)
		// If possible, fetch additional (technical) info from the site
		if(preg_match("/<a href=([^\"]+)>Alle Rezensionen ansehen/i", $pageBuffer, $regs))
		{
			$detailPage = $this->fetchURI('http://www.amazon.de/' . $regs[1]);
	
			// Fetch the information if page not empty
			if(strlen($detailPage)>0)
			{
				$this->addItemAttribute('blurb', $this->parse_amazon_video_blurb($detailPage));
			}
		}
	
		// If possible, fetch additional (technical) info from the site
		if(preg_match("/<a href=\"([^\"]+)\">Technische Informationen/i", $pageBuffer, $regs))
		{
			$detailPage = $this->fetchURI('http://www.amazon.de/' . $regs[1]);
	
			// Fetch the information if page not empty
			if(strlen($detailPage)>0)
			{
				if (preg_match("/Production Company:([^<]*)<br>/i", $detailPage, $regs))
				{
					$this->addItemAttribute('studio', $regs[1]);
				}
				
				// Ratio
				if (preg_match(":Bildformat(.*)<br>:i", $detailPage, $regs))
				{
					if(preg_match_all("/([0-9]{1}\.[0-9]+):1/", $regs[1], $matches))
					{
						$this->addItemAttribute('ratio', $matches[1]);
					}
					
					if(strpos($regs[1], "4:3"))
					{
						$this->addItemAttribute('ratio', '1.33');
					}
					
					if(strpos($regs[1], "16:9"))
					{
						$this->addItemAttribute('ratio', '1.78');
					}
				}
				
				// Spoken languages
				if (preg_match("/Sprache[n]*:\\n([^<]*)<br>/i", $detailPage, $regs))
				{
					$this->addItemAttribute('audio_lang', trim_explode("\n", trim(preg_replace("/[\n]+/", "\n", str_replace("&nbsp;", "\n", $regs[1])))));
				}
				
				// Subtitles
				if(preg_match("/Untertitel:([^<]*)<br>/i", $detailPage, $regs))
				{
				    $this->addItemAttribute('subtitles', trim_explode(",", $regs[1]));
				}
			}
		}
	}
	
	function parse_amazon_game_blurb($str)
	{
		return $this->parse_amazon_video_blurb($str);
	}
	
	function parse_amazon_book_blurb($str)
	{
		return $this->parse_amazon_video_blurb($str);
	}
	
	function parse_amazon_video_blurb($str)
	{
		if(preg_match_all("/<i>(.*[^<])<\/i><\/b><\/span><br>[\n]<span class=\"serif\">(.*)<\/span>[\n]/i", $str, $regs))
		{
			$offset = 0;
			for ($i=0; $i< count($regs[1]); $i++)
			{
				if ($regs[1][$i] != "")
				{
					$ret[$offset] = trim(mystrip_tags($regs[2][$i]));
					
					$ret[$offset] .= "\n--".$regs[1][$i];
					$offset++;
				}
			}
		}
		else
		{
			return NULL;
		}
		return $ret;
	}
	
	function parse_music_tracks($title, $disc_no, $titlePage)
	{
		$tracks = NULL;
		if(preg_match("!<b class=\"h1\">".preg_quote($title, "!")."</b><br>(.*)<hr noshade size=1>!Usi", $titlePage, $regs))
		{
			// Only parse for the disc number if disc_no specified
			if(!is_numeric($disc_no) || preg_match("!<b>Disc $disc_no</b>(.*)</table>!Usi", $regs[1], $regs2))
			{
				if(preg_match_all("![0-9]+\.(.*?)<br>!msi", is_numeric($disc_no)?$regs2[1]:$regs[1], $matches))
				{
					for ($i = 0; $i < count($matches[1]); $i++)
					{
						if(preg_match("!<a href=[^>]*>([^<]*)<img!i", $matches[1][$i], $regs3)) 
							$track = $regs3[1];
						else
							$track = $matches[1][$i];
	
						if(strlen($track)>0)
						{						
							$track = html_entity_decode(strip_tags($track));
							$tracks[] = $track;
						}
					}
				}
			}
		}
		return $tracks;
	}
}
?>