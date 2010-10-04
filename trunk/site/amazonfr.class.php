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

 Plugin created by Laurent Chauvin <lchauvin@yahoo.com>
 Converted to 0.81 format by Jason Pell <jasonpell@users.sourceforge.net>
 Updated for 1.0 by Marc Powell <shaddw@users.sourceforge.net>
 */
include_once("./functions/SitePlugin.class.inc");
include_once("./site/amazonutils.php");

class amazonfr extends SitePlugin
{
	function amazonfr($site_type)
	{
		parent::SitePlugin($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r)
	{
		if(strlen($search_vars_r['amazfrasin'])>0)
		{
			$this->addListingRow(NULL, NULL, NULL, array('amazfrasin'=>$search_vars_r['amazfrasin']));
			return TRUE;
		}
		else
		{
			// Get the mapped AMAZON index type
			$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));
			
			$queryUrl = "http://www.amazon.fr/exec/obidos/external-search?index=".$index_type."&keyword=".rawurlencode($search_vars_r['title'])."&sz=$items_per_page&pg=$page_no";
			//$queryUrl = "http://www.amazon.fr/s/url=search-alias%3D".$index_type."&field-keywords=".rawurlencode($search_vars_r['title'])."&sz=$items_per_page&pg=$page_no";
			$pageBuffer = $this->fetchURI($queryUrl);
		}

		if(strlen($pageBuffer)>0)
		{
			$amazonasin = false;
			// check for an exact match, but not if this is second page of listings or more
			if(!$this->isPreviousPage())
			{
				if (preg_match("/ASIN: <font>(\w{10})<\/font>/", $pageBuffer, $regs))
				{
					$amazonasin = trim($regs[1]);
				}
				else if (preg_match ("!<li><b>ASIN:</b> ([^<]*)</li>!m", $pageBuffer, $regs))
				{
					$amazonasin = trim($regs[1]);
				}
				else if (preg_match ("!<li><b>ISBN:</b> ([^<]*)</li>!m", $pageBuffer, $regs) || // for books, ASIN is the same as ISBN
							preg_match ("!<li><b>ISBN-10:</b> ([^<]*)</li>!m", $pageBuffer, $regs))
				{
					$amazonasin = trim ($regs[1]);
				}
			}
			// exact match
			if($amazonasin!==false)
			{
				// single record returned
				$this->addListingRow(NULL, NULL, NULL, array('amazfrasin'=>$amazonasin));

				return TRUE;
			}
			else
			{
				$pageBuffer = preg_replace('/[\r\n]+/', ' ', $pageBuffer);
//print_r($pageBuffer); important use this for debugging, since amazon.fr creates a table based layout when queried by the script (probably a browser check, since in firefox it is a div based layout.)
				//<td class="resultCount">Résultats 1 - 12 sur 38</td>
				if(preg_match("/<td class=\"resultCount\">R.*?sultats \d+ - \d+ sur (\d*).*?(\d*)<\/td>/i", $pageBuffer, $regs) ||
						preg_match("/<td class=\"resultCount\">([0-9]+) r.*?sultats<\/td>/i", $pageBuffer, $regs))
				{
					if(is_numeric($regs[1]) && is_numeric($regs[2]))
						$totalCount = ($regs[1].$regs[2]);
					else
						$totalCount = $regs[1];
					// store total count here.
					$this->setTotalCount($totalCount);
						
					if(preg_match_all("!<td class=\"imageColumn\".*?".
										"<a href=\"[^\"]+\">.*?".
										"<img .*?src=\"([^\"]+)\".*?".
										"<td class=\"dataColumn\">.*?".
										"<a href=\"([^\"]+)\"><span class=\"srTitle\">([^<]*)</span></a>!m", $pageBuffer, $matches))
					{
						for($i=0; $i<count($matches[0]); $i++)
						{
							//http://www.amazon.com/First-Blood-David-Morrell/dp/0446364401/sr=1-1/qid=1157433908/ref=pd_bbs_1/104-6027822-1371911?ie=UTF8&s=books
							if(preg_match("!/dp/([^/]+)/!", $matches[2][$i], $regs))
							{
								if(strpos($matches[1][$i], "no-img")!==FALSE)
									$matches[1][$i] = NULL;

								$this->addListingRow($matches[3][$i], $matches[1][$i], NULL, array('amazfrasin'=>$regs[1], 'search.title'=>$search_vars_r['title']));
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

	function queryItem($search_attributes_r, $s_item_type)
	{
		// assumes we have an exact match here
		$pageBuffer = $this->fetchURI("http://www.amazon.fr/gp/product/".$search_attributes_r['amazfrasin']);

		// no sense going any further here.
		if(strlen($pageBuffer)==0)
		return FALSE;

		$pageBuffer = preg_replace('/[\r\n]+/', ' ', $pageBuffer);
		$pageBuffer = preg_replace('/>[\s]*</', '><', $pageBuffer);

		// The location of the title is the same for all formats.
		//<title>Amazon.fr : Big Fish: DVD</title>
		//if(preg_match("/<title>.*Amazon\.fr\s:\s([^:]*):(.*)<\/title>/s", $pageBuffer, $regs))
		if(preg_match("/<span id=\"btAsinTitle\"[^>]*>([^<]+)<\/span>/s", $pageBuffer, $regs) ||
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

		if(preg_match("/de <a href=\"(.*?)\">(.*?)<\/a> \(Auteur\)/", $pageBuffer, $regs))
		{
			$this->addItemAttribute('author', $regs[2]);
		}

		$imageBuffer = $this->fetchURI("http://www.amazon.fr/gp/product/images/".$search_attributes_r['amazfrasin']."/");
		if($imageBuffer!==FALSE)
		{
			//fetchImage("alt_image_0", "http://images.amazon.com/images/P/B0000640RX.01._SS400_SCLZZZZZZZ_.jpg" );
			if(preg_match_all("!fetchImage\(\"[^\"]+\", \"([^\"]+)\"!", $imageBuffer, $regs))
			{
				if(preg_match("!(.*)/(.*?)\.(.*)\.jpg!", $regs[1][0], $regs2))
				{
					$this->addItemAttribute('imageurl', $regs2[1]."/".$regs2[2].".jpg");
				}

				$this->addItemAttribute('imageurl', $regs[1]);

			} //<img src="http://images.amazon.com/images/P/B000FMH8RG.01._SS500_SCLZZZZZZZ_V52187861_.jpg" id="prodImage" />
			else if(preg_match_all("!<img src=\"([^\"]+)\" id=\"prodImage\" />!", $imageBuffer, $regs))
			{
				if(preg_match("!(.*)/(.*?)\.(.*)\.jpg!", $regs[1][0], $regs2))
				{
					$this->addItemAttribute('imageurl', $regs2[1]."/".$regs2[2].".jpg");
				}

				$this->addItemAttribute('imageurl', $regs[1]);
			}
		}

		if(preg_match("/Descriptions du produit(.*?)<div class=\"content\">(.*?)<\/div>/", $pageBuffer, $regs))
		{
			$this->addItemAttribute('blurb', $regs[2]);
		}

		if(preg_match("/<span class=\"price\">EUR ([^<]*)<\/span>/m", $pageBuffer, $regs))
		{
			$this->addItemAttribute('listprice', $regs[1]);
		}
		else if(preg_match("/<td class=\"listprice\">EUR ([^<]*)<\/td>/i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('listprice', $regs[1]);
		}
		else if(preg_match("/<b>List Price:<\/b>[^EUR]+EUR([0-9\.]+)/m", $pageBuffer, $regs))
		{
			$this->addItemAttribute('listprice', $regs[1]);
		}

		// amazon price value
		if(preg_match("/<span class=\"price\">EUR ([^<]*)<\/span>/m", $pageBuffer, $regs))
		{
			$this->addItemAttribute('price', $regs[1]);
		}
		else if(preg_match("/<b class=\"price\">EUR ([^<]*)<\/b>/m", $pageBuffer, $regs))
		{
			$this->addItemAttribute('price', $regs[1]);
		}

		if(preg_match("!<li><b>.*?client.*?:</b>[\s]*<img src=\".*?/stars-([^\.]+).!i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('amznrating', str_replace('-', '.', $regs[1])) ;
		}

		// Get the mapped AMAZON index type
		$index_type = ifempty($this->getConfigValue('item_type_to_index_map', $s_item_type), strtolower($s_item_type));
		switch($index_type)
		{
			case 'dvd':
			case 'vhs':
			case 'blu-ray':
				$this->parse_amazon_video_data($search_attributes_r, $s_item_type, $pageBuffer);
				break;

			case 'video-games-fr':
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
	function parse_amazon_game_data($search_attributes_r, $pageBuffer)
	{
		if (preg_match("/de <a[^<]*>([^<]+)<\/a>/i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('gamepblshr', $regs[1]);
		}

		if (preg_match("/<b>Plate-forme:[\s]*<\/b>([^<]+)<\/li>/si", $pageBuffer, $regs) || 
				preg_match("/<b>Plate-forme:[\s]*<\/b>[^<]+<img [^<]*>([^<]+)<\/li>/si", $pageBuffer, $regs))
		{
			// Different combo's of windows, lets treat them all as windows.
			if(strpos($regs[1], "Windows")!==FALSE)
				$this->addItemAttribute('gamesystem', "Windows");
			else
				$this->addItemAttribute('gamesystem', $regs[1]);
		}

		if (preg_match("/<b>ESRB Rating:[\s]*<\/b>(.+?)<br>/si", $pageBuffer, $regs))
		{
			if(preg_match(":videogames/ratings/esrb-(.*).gif:", $regs[1], $regs2))
				$this->addItemAttribute('gamerating', $regs2[1]);
			else
				$this->addItemAttribute('gamerating', strtoupper($regs[1]));
		}

		if(preg_match("/<b>Features:<\/b>[\s]<ul>(.+?)<\/ul>/si", $pageBuffer, $featureblock))
		{
			if(preg_match_all("/<li.*?>(.*?)<\/li>/si", $featureblock[1], $matches))
			{
				for($i = 0; $i < count($matches[1]); $i++)
				{
					$matches[1][$i] = strip_tags($matches[1][$i]);
				}

				$this->addItemAttribute('features', implode("\n", $matches[1]));
			}
		}
	}

	function parse_music_tracks($pageBuffer)
	{
		if(preg_match_all("!<tr class=\"[^\"]*\">[\s]*<td>[\s]*[0-9]+\.[\s]*([^<]+)</td>!", $pageBuffer, $matches))
		{
			return $matches[1];
		}
		return NULL;
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
	function parse_amazon_music_data($search_attributes_r, $pageBuffer)
	{
		//<meta name="description" content="Dangerous [Remastered], Michael Jackson">
		//<meta name="keywords" content="Dangerous [Remastered], Music, Michael Jackson">

		//<meta name="description" content="Up!, Shania Twain">
		//<meta name="keywords" content="Up!, Music, Shania Twain, Country, Pop">

		//<meta name="description" content="Essential Mozart: 32 Of His Greatest Masterpieces, Wolfgang Amadeus Mozart, Neville Marriner, Uri Segal, Gyorgy Fischer, Stephen Cleobury, David Hill, Christopher Hogwood, Georg Solti, Willi Boskovsky, Herbert von Karajan, Christoph von Dohnanyi, Myung-Whun Chung, Jack Brymer, Peter Maag, George Guest, Radu Lupu, Cecilia Bartoli, Fritz Dolezal, Werner Hink, Hubert Kroisamer, Peter Schmidl, James Vivian, Emma Kirkby, Lisa Beznosiuk, Frances Kelly, Renee Fleming, Barry Tuckwell, Bryn Terfel, Vladimir Ashkenazy, Andras Schiff, Sumi Jo, Franklin Cohen, Hermann Prey, Kiri Te Kanawa, Joshua Bell, Margaret Marshall, Leontyne Price">
		//<meta name="keywords" content="Essential Mozart: 32 Of His Greatest Masterpieces, Music, Wolfgang Amadeus Mozart, Neville Marriner, Uri Segal, Gyorgy Fischer, Stephen Cleobury, David Hill, Christopher Hogwood, Georg Solti, Willi Boskovsky, Herbert von Karajan, Christoph von Dohnanyi, Myung-Whun Chung, Jack Brymer, Peter Maag, George Guest, Radu Lupu, Cecilia Bartoli, Fritz Dolezal, Werner Hink, Hubert Kroisamer, Peter Schmidl, James Vivian, Emma Kirkby, Lisa Beznosiuk, Frances Kelly, Renee Fleming, Barry Tuckwell, Bryn Terfel, Vladimir Ashkenazy, Andras Schiff, Sumi Jo, Franklin Cohen, Hermann Prey, Kiri Te Kanawa, Joshua Bell, Margaret Marshall, Leontyne Price">

		if(preg_match("/<meta name=\"description\" content=\"([^\"]*)\"/i",$pageBuffer, $regs))
		{
			if(preg_match("/by (.*)/i", $regs[1], $regs2))
			{
				// the artist is the last part of the description.
				// Amazon.fr : Dangerous: Musique: Michael Jackson by Michael Jackson
				$this->addItemAttribute('artist', $regs2[1]);
			}
		}

		// <li><b>CD audio</b>  (16 octobre 2001)</li>
		if(preg_match("/<b>CD audio<\/b>[^\(]*\(([^\)]+)\)/i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('release_dt', $regs[1]);
			if(preg_match("!([0-9]+)$!", $this->getItemAttribute('release_dt'), $regs2))
			{
				$this->addItemAttribute('year', $regs2[1]);
			}
		}

		// <li><b>Label:</b> Epic</li>
		if(preg_match("/<b>Label:<\/b>([^<]+)</i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('musiclabel', $regs[1]);
		}

		// <li><b>Nombre de disques:</b> 1</li>
		if(preg_match("/<b>Nombre de disques:<\/b>[\s]*([0-9]+)/i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('no_discs', $regs[1]);
		}

		//http://www.amazon.co.uk/dp/samples/B0000029LG/
		if(preg_match("!http://www.amazon.fr/.*/dp/samples/".$search_attributes_r['amazfrasin']."/!", $pageBuffer, $regs))
		{
			$samplesPage = $this->fetchURI("http://www.amazon.fr/dp/samples/".$search_attributes_r['amazfrasin']."/");
			if(strlen($samplesPage)>0)
			{
				$samplesPage = preg_replace('/[\r\n]+/', ' ', $samplesPage);
				$tracks = $this->parse_music_tracks($samplesPage);
				$this->addItemAttribute('cdtrack', $tracks);
			}
		}
		else if(preg_match("!<div class=\"bucket\">[\s]*<b class=\"h1\">&Eacute;couter des extraits musicaux</b>(.*?)</div>!", $pageBuffer, $regs) ||
		preg_match("!<div class=\"bucket\">[\s]*<b class=\"h1\">Liste des titres</b>(.*?)</div>!", $pageBuffer, $regs))
		{
			$tracks = $this->parse_music_tracks($regs[1]);
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
	function parse_amazon_books_data($search_attributes_r, $pageBuffer)
	{
		// Author extraction
		//<meta name="description" content="Amazon.com: Books: Managing and Using MySQL (2nd Edition) by George Reese,Randy Jay Yarger,Tim King" />
		// Author extraction
		//<meta name="description" content="Amazon.fr : The Da Vinci Code: Livres: Dan Brown by Dan Brown" />

		if(preg_match("/<meta name=\"description\" content=\"([^\"]*)\"/i",$pageBuffer, $regs))
		{
			if(preg_match("/by (.*)/i", $regs[1], $regs2))
			{
				// the artist is the last part of the description.
				// Amazon.fr : The Da Vinci Code: Livres: Dan Brown by Dan Brown
				$this->addItemAttribute('author', $regs2[1]);
			}
		}

		// <b class="h1">Détails sur le produit</b><br>
		if(preg_match("/<b class=\"h1\">D.tails sur le produit<\/b>(.*)<\/ul>/si", $pageBuffer, $regs))
		{
			$productDetails = html_entity_decode(trim($regs[1]), ENT_COMPAT, get_opendb_config_var('themes', 'charset')=='utf-8'?'UTF-8':'ISO-8859-1');

			if(preg_match("/<li><b>ISBN:<\/b>([^<]*)<\/li>/i", $productDetails, $regs2))
			{
				$this->addItemAttribute('isbn', $regs2[1]);
			}
			else if(preg_match("/<li><b>ISBN-10:<\/b>([^<]*)<\/li>/i", $productDetails, $regs2))
			{
				$this->addItemAttribute('isbn', $regs2[1]);
			}

			if(preg_match("/<li><b>ISBN-13:<\/b>([^<]*)<\/li>/i", $productDetails, $regs2))
			{
				$this->addItemAttribute('isbn13', $regs2[1]);
			}

			if(preg_match("!x-locale/common/customer-reviews/ratings/stars-(.*?)-(.*?)!", $productDetails, $regs2))
			{
				$this->addItemAttribute('s_rating',$regs2[1]);
			}

			if(preg_match("!<li><b>Langue.*?</b>(.*?)</li>!", $productDetails, $regs2))
			{
				$this->addItemAttribute('booklang', $regs2[1]);
			}

			if(preg_match("!<li><b>Collection.*?</b>(.*?)</li>!", $productDetails, $regs2))
			{
				$this->addItemAttribute('bookseries', $regs2[1]);
			}

			if(preg_match("/([0-9]+) pages/", $productDetails, $regs2))
			{
				$this->addItemAttribute('nb_pages', $regs2[1]);
			}

			if(preg_match("/<li><b>Editeur[^>]*>[\s]*([^<]*)<\/li>/i", $productDetails, $regs2))
			{

				if(preg_match("/([^\(]+)\(([^\)]+)\)/", $regs2[1], $regs2))
				{
					// All we want is the year here.
					if (preg_match("/([0-9]+)$/", $regs2[2], $regs3))
					{
						$this->addItemAttribute('pub_date', $regs3[1]);
					}

					if(preg_match("/([^;]+);([^$]+)$/", $regs2[1], $regs3))
					{
						$this->addItemAttribute('publisher', $regs3[1]);
						$this->addItemAttribute('edition', $regs3[2]);
					}
					else
					{
						$this->addItemAttribute('publisher', $regs2[1]);
					}
				}
				else
				{
					$this->addItemAttribute('publisher', $regs2[1]);
				}
			}
		}
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
	function parse_amazon_video_data($search_attributes_r, $s_item_type, $pageBuffer)
	{
		if (preg_match("/<b>Date(.*)<\/b>(.*)<\/li>/", $pageBuffer, $regs))
		{
			// Get year only, for now.  In the future we may add ability to
			// convert date to local date format.
			if(preg_match("/([0-9]+)$/m", $regs[2], $regs2))
			{
				$this->addItemAttribute('year', $regs2[1]);
			}
		}

		// <li><b>Format : </b>Anamorphic, Couleur, Plein écran, PAL</li>
		if (preg_match("/<b>Format[\s]*:[\s]*<\/b>(.*)<\/li>/", $pageBuffer, $regs))
		{
			if(preg_match("/PAL/",$regs[1]))
			{
				$this->addItemAttribute('vid_format', 'PAL');
			}
			else if(preg_match("/NTSC/",$regs[1]))
			{
				$this->addItemAttribute('vid_format', 'NTSC');
			}
			else if(preg_match("/SECAM/",$regs[1]))
			{
				$this->addItemAttribute('vid_format', 'SECAM');
			}
		}
		else
		{
			// All Amazon.fr (FR) items should be PAL!
			$this->addItemAttribute('vid_format', 'PAL');
		}

		// MPO: does not get displayed for AMAZON.FR
		// genre extraction block.
		$startidx = strpos($pageBuffer, "<li><b>Genres:</b>");
		if($startidx !== FALSE)
		{
			// Move past start text.
			$startidx+=18;//"Genres:</b>"

			$endidx = strpos($pageBuffer,"</li>", $startidx);

			if ($endidx !== FALSE)
			{
				// Get rid of all the html - a quick hack!
				$genre = trim(substr($pageBuffer,$startidx,$endidx-$startidx));
				$genre = strip_tags($genre);

				// If composite genre, get rid of / as we do not need it.
				$genre = str_replace(" / "," ",$genre);

				// Expand Sci-Fi to OpenDb matching value.
				$genre = str_replace("Sci-Fi", "ScienceFiction", $genre);

				// Match all whitespace and convert to a comma.
				$genre = preg_replace("/[\s]+/", ",", $genre);

				$genre = str_replace("(more)","", $genre);

				$this->addItemAttribute('genre', explode(",", $genre));
			}
		}

		$this->addItemAttribute('actors', parse_amazon_video_people("Acteurs", $pageBuffer));

		if (preg_match("!<li><b>.*?alisateurs[\s]*(.*?)</li>!", $pageBuffer, $regs))
		{
			if(preg_match_all("/<a href=([^>]+)>([^<]+)<\/a>/", $regs[1], $matches))
			{
				for($i=0; $i<count($matches[1]); $i++)
				{
					if(strpos($matches[2][$i], "See more")===FALSE)
					{
						$this->addItemAttribute('director', $matches[2][$i]);
					}
				}
			}
		}

		// Region extraction block
		//<li><b>Région: </b>Région 1
		if (preg_match("/<li><b>R.*?gion[\s]*:[\s]*<\/b>R.*?gion ([0-6])/", $pageBuffer, $regs))
		{
			$this->addItemAttribute('dvd_region', $regs[1]);
			switch($regs[1]) {
				case 1:
				case 4:
					$this->addItemAttribute('bd_region', "A");
					break;
				case 0:
				case 2:
					$this->addItemAttribute('bd_region', "B");
					break;
				case 3:
				case 5:
				case 6:
					$this->addItemAttribute('bd_region', "C");
					break;
				default:
					$this->addItemAttribute('bd_region', "B");
					break;
			}
		}
		else	// default zone 2
		{
			$this->addItemAttribute('dvd_region', "2");
			$this->addItemAttribute('bd_region', "B");
		}

		// Ratio
		//<li><b>Rapport de forme :</b> 2.35:1</li>
		if (preg_match("/<li><b>Rapport de forme[\s]*:<\/b>([^<]+)<\/li>/i", $pageBuffer, $regs));
		{
			if(preg_match_all("/([0-9]{1}\.[0-9]+)/", $regs[1], $matches))
			{
				$this->addItemAttribute('ratio', $matches[1]);
			}
		}
		//<li><b>Format:</b> 1.85 - 16:9<P>
		if(preg_match("/<li><b>Format[\s]*:<\/b>(.*)<P>/", $pageBuffer, $regs))
		{
			if(preg_match_all("/([0-9]{1}\.[0-9]+)/", $regs[1], $matches))
			{
				$this->addItemAttribute('ratio', $matches[1]);
			}
		}
		//<b>Nombre de disques&nbsp;:</b> 1</li>
		if (preg_match("/<b>Nombre de disques.*?:<\/b> ([0-9]*)<\/li>/i", $pageBuffer, $regs));
		{
			$this->addItemAttribute('no_media', $regs[1]);
		}

		// rating not given on amazon.fr
		//<li><b>Rating</b> <img src="http://g-images.amazon.com/images/G/01/detail/pg-13.gif" width=35 height=11 width=35 height=11>
		// Rating extraction block
		if (preg_match("/<b>Rating[\s]*<\/b>[\s]*<img src=\"([^\"]+)\"/mi", $pageBuffer, $regs))
		{
			//if(preg_match("/<img src=.* alt=\"(.*)\">/", trim($regs[1]), $regs2))
			if(preg_match("!detail/([^\.]+)\.[gif|jpg]!", $regs[1], $regs2))
			{
				$age_rating = strtoupper(trim($regs2[1]));

				// A very strange problem with an age rating of 'PG-13 \"'
				$indexOfSpace = strpos($age_rating," ");
				if($indexOfSpace!==FALSE)
					$age_rating = substr($age_rating,0,$indexOfSpace);

				$this->addItemAttribute('age_rating', $age_rating);
			}
			else
			{
				$this->addItemAttribute('age_rating', $regs[1]);
			}
		}

		if (preg_match("!<li><b>Studio:[\s]*</b>([^<]*)</li>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('studio', $regs[1]);
		}

		//<li><b>Date de sortie du DVD :</b> 9 janvier 2002
		if(preg_match("/<b>Date de sortie.*?<\/b>(.*?)<\/li>/i", $pageBuffer, $regs))
		{
			// Get year only, for now.  In the future we may add ability to
			// convert date to local date format.
			if(preg_match("/([0-9]+)$/m", $regs[1], $regs2))
			{
				$this->addItemAttribute('dvd_rel_dt', $regs[1]);

				// if year not defined, use dvd_rel_dt year
				if($this->getItemAttribute('year') === FALSE)
				{
					$this->addItemAttribute('year', $regs2[1]);
				}
			}
		}

		// Duration extraction block
		//<li><b>Durée :</b> 120 minutes </li>
		if (preg_match("/<li><b>Dur.*?e.*?:<\/b>[\s]*([0-9]+) minutes/i", $pageBuffer, $regs))
		{
			$this->addItemAttribute('run_time', $regs[1]);
		}

		// Get the anamorphic format attribute - Thanks to André Monz <amonz@users.sourceforge.net
		if(preg_match("/anamorphic/",$pageBuffer))
		{
			$this->addItemAttribute('anamorphic', 'Y');
		}

		if (preg_match("/THX Certified/i", $pageBuffer))
		{
			$this->addItemAttribute('dvd_audio', 'THX');
		}

		// Spoken languages
		//<BR>Langues et formats sonores :  Francais (Dolby Digital 5.1), Francais (DTS)<BR>
		//<li>Available Audio Tracks:  English (Dolby Digital 5.1), French (Dolby Digital 2.0 Surround)</li>
		if(preg_match("/<b>Langue.*?:<\/b>[\s]*([^<]*)<\/li>/i", $pageBuffer, $regs))
		{
			//print_r($regs[1]);
			$audio_lang_r = explode(',', unaccent($regs[1]));
			
			$amazon_dvd_audio_map = array(
				array("Anglais", "2.0"), // Dolby2.0
				array("Anglais", "5.0"), //Dolby
				array("Anglais", "5.1"),// Dolby5.1
				array("Anglais", "6.1", "EX"), // Dolby6.1
				array("Anglais", "6.1", "DTS", "ES"), // DTS6.1
				array("Anglais", "6.1"),// Dolby6.1
				array("Anglais", "DTS"), // DTS
				array("Franais", "2.0"), // Dolby2.0
				array("Franais", "5.0"),//Dolby
				array("Franais", "5.1"),// Dolby5.1
				array("Franais", "6.1", "EX"), // Dolby6.1
				array("Franais", "6.1", "DTS", "ES"), // DTS6.1
				array("Franais", "6.1"),// Dolby6.1
				array("Franais", "DTS"),// DTS
				array("Francais", "2.0"),// Dolby2.0
				array("Francais", "5.0"),//Dolby
				array("Francais", "5.1"),// Dolby5.1
				array("Francais", "6.1", "EX"), // Dolby6.1
				array("Francais", "6.1", "DTS", "ES"), // DTS6.1
				array("Francais", "6.1"),// Dolby6.1
				array("Francais", "DTS"));// DTS

			$amazon_audio_lang_map = array(
				array("Anglais"),
				array("Francais"),
				array("Espagnol"),
				array("Italien"),
				array("Allemand"));

			while(list(,$audio_lang) = @each($audio_lang_r)) {
				$key = parse_language_info($audio_lang, $amazon_dvd_audio_map);
				if($key!==NULL) {
					$this->addItemAttribute('dvd_audio', $key);
				}

				$key = parse_language_info($audio_lang, $amazon_audio_lang_map);
				if($key!==NULL) {
					$this->addItemAttribute('audio_lang', $key);
				}
			}
		}

		// Subtitles
		//<li>Sous-titres : Anglais, Francais</li>
		if (preg_match("/<b>Sous-titre.*?:<\/b>[\s]*([^<]*)<\/li>/i", $pageBuffer, $regs))
		{
			$audio_lang_r = explode(',', unaccent($regs[1]));
				
			$amazon_video_subtitle_map = array(
				array("Anglais"),
				array("Francais"),
				array("Espagnol"),
				array("Allemand"),
				array("Italien"));
				
			while(list(,$audio_lang) = @each($audio_lang_r)) {
				$key = parse_language_info($audio_lang, $amazon_video_subtitle_map);
				if($key!==NULL) {
					$this->addItemAttribute('subtitles', $key);
				}
			}
		}

		// Edition details block - 'dvd_extras' attribute
		// <li><b>Fonctions DVD&nbsp;:</b><ul>
		if(preg_match("/<li><b>Fonctions DVD[^:]*:<\/b><ul>(.+?)<\/ul>/si", $pageBuffer, $regs))
		{
			$dvdFeaturesBlock = $regs[1];

			// may use "bullet" character instead of <li>
			$dvdFeaturesBlock = str_replace("&#149;", "</li><li>", $dvdFeaturesBlock);

			if(preg_match_all("/<li>(.*)<\/li>/mUi", $dvdFeaturesBlock, $matches))
			{
				$dvd_extras = '';

				while(list(,$item) = @each($matches[1]))
				{
					$item = html_entity_decode(strip_tags($item), ENT_COMPAT, get_opendb_config_var('themes', 'charset')=='utf-8'?'UTF-8':'ISO-8859-1');

					// We may have a hard space here, so get rid of it.
					$item = trim(strtr($item, chr(160), ' '));

					// Don't include anamorphic, subtitles, audio tracks, etc
					if(strpos($item, "anamorphic")===FALSE &&
							strpos($item, "Sous-titres")===FALSE &&
							strpos($item, "Langues")===FALSE)
					{
						if(preg_match("/\"([^\"]+)\"/", $item, $reg2))
						{
							$item = $reg2[1];
						}
						$dvd_extras .= $item."\n";
					}
				}

				if(strlen($dvd_extras)>0)
				{
					$this->addItemAttribute('dvd_extras', $dvd_extras);
				}
			}
		}

//print_r($pageBuffer);
		// no editorial reviews for amazon.fr
		// search for "Synopsis" or "Description"
		//<h3 class="productDescriptionSource">Synopsis</h3><div class="productDescriptionWrapper">
		if (preg_match("/Synopsis<\/h3><div.*?>[\s]*(.*?)<div class=\"emptyClear\">/si", $pageBuffer, $regs))
		{
			$this->addItemAttribute('blurb', $regs[1]);
		}
		else
		if (preg_match("/<b>Description<\/b><br[\s]*[\/]*>([^<]*)/si", $pageBuffer, $regs))
		{
			$this->addItemAttribute('blurb', $regs[1]);
		}
		//genres:
		if (preg_match("!<b>Classement parmi les ventes Amazon.fr.*?:</b>(.*?)</li>!i", $pageBuffer, $regs))
		{
			//<a href="/gp/bestsellers/dvd-de/289099/ref=pd_zg_hrsr_d_1_3">Fantasy</a>
			if (preg_match_all('!<a href=\".*?\">(.*?)</a>!i', $regs[1], $genres))
			{
				$moviegenres=array_map("unaccent", array_unique($genres[1]));
				sort($moviegenres);
				//mahbe here we should add some kind of mapping function that maps amazon.de genres to opendb default genres?
				$this->addItemAttribute('genre', $moviegenres);
			}
		}

		// IMDB ID block (does not seem to be present on amazon.fr)
		//<A HREF="http://amazon.imdb.com/title/tt0319061/">
		//http://www.amazon.com/gp/redirect.html/103-0177494-1143005?location=http://amazon.imdb.com/title/tt0319061&token=F5BF95E1B869FD4EB1192434BA5B7FECBA8B3718
		//http://amazon.imdb.com/title/tt0319061
		if(preg_match("!http://amazon.imdb.com/title/tt([0-9]+)!is", $pageBuffer, $regs))
		{
			$this->addItemAttribute('imdb_id', $regs[1]);
		}

		// Attempt to include data from IMDB if available - but only for DVD, VHS, etc
		// as IMDB does not work with BOOKS or CD's.
		if(is_numeric($this->getItemAttribute('imdb_id')))
		{
			$sitePlugin =& get_site_plugin_instance('imdb');
			if($sitePlugin !== FALSE)
			{
				if($sitePlugin->queryItem(array('imdb_id'=>$this->getItemAttribute('imdb_id')), $s_item_type))
				{
					// no mapping process is performed here, as no $s_item_type was provided.
					$itemData = $sitePlugin->getItemData();
					if(is_array($itemData))
					{
						// merge data in here.
						while(list($key,$value) = each($itemData))
						{
							if($key == 'actors')
								$this->replaceItemAttribute('actors', $value);
							else if($key == 'director')
								$this->replaceItemAttribute('director', $value);
							else if($key == 'year')
								$this->replaceItemAttribute('year', $value);
							else if($key == 'actors')
								$this->replaceItemAttribute('actors', $value);
							else if($key == 'genre')
								$this->replaceItemAttribute('genre', $value);
							else if($key == 'plot') //have to map from imdb to amazon attribute type.
								$this->addItemAttribute('blurb', $value);
							else if($key != 'age_rating' && $key != 'run_time')
								$this->addItemAttribute($key, $value);
						}
					}
				}
			}
		}
	}
}
?>
