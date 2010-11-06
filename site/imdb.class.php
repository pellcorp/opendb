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
	* 
	* Search for 'Rambo' to return a list of various titles.
		Search for '12 Angry Men' to get an exact title match.
		Search for 'faddsda' to trigger a search error.
*/
include_once("./functions/SitePlugin.class.inc");

class imdb extends SitePlugin
{
	function imdb($site_type)
	{
		parent::SitePlugin($site_type);
	}
	
	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r)
	{
		if(strlen($search_vars_r['imdb_id'])>0)
		{
			$this->addListingRow(NULL, NULL, NULL, array('imdb_id'=>$search_vars_r['imdb_id']));
			return TRUE;
		}
		else
		{
			$pageBuffer = $this->fetchURI("http://www.imdb.com/find?q=".rawurlencode(strtolower($search_vars_r['title'])).";more=tt");
		}
		
		if(strlen($pageBuffer)>0)
		{
			if(preg_match("!http://us.imdb.com/title/tt([^/]+)/!", $this->getFetchedURILocation(), $regs))
			{
				$this->addListingRow(NULL, NULL, NULL, array('imdb_id'=>$regs[1]));
				return TRUE;
			}
			else
			{
				//<b>Titles (Exact Matches)</b> (Displaying 1 Result) <table>
				//<p><b>Titles (Exact Matches)</b> (Displaying 4 Results)
				if( preg_match_all("/<b>([a-zA-Z]+) \(([a-zA-Z]+) Matches\)<\/b> \(Displaying ([0-9]+) Result[s]*\)[\s]*<table>/m", $pageBuffer, $gmatches) )
				{
				    // we need to know what match types to support (exact, partial, approx).
					$match_type_r = $this->getConfigValue('title_search_match_types');
					
				    for ($i = 0; $i < count($gmatches[0]); $i++)
					{
					    if(!is_array($match_type_r) || in_array(strtolower($gmatches[2][$i]), $match_type_r))
						{
							$start = strpos($pageBuffer, $gmatches[0][$i]);
							if($start!==FALSE)
							{
							    $start += strlen($gmatches[0][$i]);
							    $end = strpos($pageBuffer, "</table>", $start);

							    $search_block = substr($pageBuffer, $start, $end-$start);
							
								if(preg_match_all("!<tr>[\s]*<td.*?>(.*?)</td>[\s]*<td.*?>(.*?)</td>[\s]*<td.*?>(.*?)</td>[\s]*</tr>!", $search_block, $matches))
								{
									for ($j = 0; $j < count($matches[1]); $j++)
									{
										$image = NULL;
										$title = NULL;
										$comments = NULL;
										$imdb_id = NULL;
										
										if( preg_match("!<a href=\"/title/tt([^/]+)/\"[^>]*>([^>]+)</a>(.*)!", $matches[3][$j], $matches2))
										{
											$imdb_id = $matches2[1];
											
											$title = $matches2[2];
											if(preg_match("/[\s]*\(([0-9]+)\)/", $matches2[3], $regs))
										    {
										    	$title .= " (".$regs[1].")";
										    }
											
											//\"(new Image()).src=[^>]*><img src=\"([^\"]+)\"
											//<tr> <td valign="top"><a href="/title/tt0083944/" onclick="(new Image()).src='/rg/photo-find/title-tiny/images/b.gif?link=/title/tt0083944/';"><img src="http://ia.imdb.com/media/imdb/01/M/==/QM/0Y/jN/0E/TO/wc/TZ/tF/kX/nB/na/B5/lM/B5/FN/5I/jN/1Y/TM/3U/TM/B5/VM._SX23_SY30_.jpg" border="0" height="32" width="23"></a>&nbsp;</td><td align="right" valign="top"><img src="/images/b.gif" height="6" width="1"><br>1.</td><td valign="top"><img src="/images/b.gif" height="6" width="1"><br><a href="/title/tt0083944/">First Blood</a> (1982)<br>&nbsp;aka <em>"Rambo"</em> - Austria, USA <em>(TV title)</em>, Japan <em>(English title)</em>, Argentina, Venezuela, Hungary, Italy, Portugal, Germany, France<br>&nbsp;aka <em>"Rambo: First Blood"</em></td></tr>
											if(preg_match("!<a href=\"/title/tt([^/]+)/\" onclick=[^>]*><img src=\"([^\"]+)\"!i", $matches[1][$j], $regs))
											{
												$image = $regs[2];
											}
										    
											if(preg_match("!<p class=\"find-aka\">aka (.*?)</p>!", $matches2[3], $regs))
										    {
										    	$comments = html_entity_decode(strip_tags($regs[1]), ENT_COMPAT, get_opendb_config_var('themes', 'charset')=='utf-8'?'UTF-8':'ISO-8859-1');
										    }
										    
											$this->addListingRow($title, $image, $comments, array('imdb_id'=>$imdb_id));
										}
									}
								}
							}
						}
					}
					
					$pageBuffer = NULL;
					return TRUE;
				}
			}
			
			//else no results found
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	function queryItem($search_attributes_r, $s_item_type)
	{
		$pageBuffer = $this->fetchURI("http://us.imdb.com/Title?".$search_attributes_r['imdb_id']);
		//print_r($pageBuffer); // for debugging purposes output exactly what we are getting from imdb.

		// no sense going any further here.
		if(strlen($pageBuffer)==0)
			return FALSE;
		
/*<h1 class="header">
Planet Terror



<span>(<a href="/year/2007/">2007</a>)</span>
*/
		if(preg_match("!<h1 class=\"header\">([^<]+)<span>\(.*?<a href=\".*\">([0-9]+)</a>!ms", $pageBuffer, $matches))
		{
			$this->addItemAttribute('title', $matches[1]);
			$this->addItemAttribute('year', $matches[2]);
		}
		
		//<h5>Also Known As:</h5><div class="info-content">       <a class="tn15more" href="/title/tt1234548/releaseinfo#akas"
		if(preg_match("!Also Known As:</h4>.*?href=\"(.*?)#akas\"!ms", $pageBuffer, $matches))
		{
			$akas = $this->fetchURI("http://us.imdb.com/title/tt".$search_attributes_r['imdb_id']."/".$matches[1]);
			//print_r($akas);
			//<h5><a name="akas">Also Known As (AKA)</a></h5>
			if(preg_match("!Also Known As \(AKA\)(.*?)</table>!ms", $akas, $matches))
			{
/*
<tr>
<td>Hombres de mentes</td>

<td>Argentina (festival title)</td>

</tr>
*/
				if (preg_match_all("!<tr>.*?<td>([^<]*)</td>.*?<td>([^\(/]*)[^<]*?</td>!ms", $matches[1], $results))
				{
					$res = array_combine($results[2], $results[1]);
					$this->addItemAttribute('alt_title', $res);
				}
			}
		}
	
		//image src extraction block
/*<td rowspan="2" id="img_primary">



<a    onclick="(new Image()).src='/rg/title-overview/primary/images/b.gif?link=%2Fmedia%2Frm3412696576%2Ftt0133093';"     href="/media/rm3412696576/tt0133093"       ><img src="http://ia.media-imdb.com/images/M/MV5BMjEzNjg1NTg2NV5BMl5BanBnXkFtZTYwNjY3MzQ5._V1._SY314_CR4,0,214,314_.jpg"
     height="314" width="214" 
     alt="Matrix Poster"
     title="Matrix Poster" /></a>

</td>
*/
		$start = strpos($pageBuffer,">Own the rights?<br />Add a poster</a>", $end);
		if($start === FALSE)
		{
			if(preg_match("!id=\"img_primary\".*?<a.*?href=\"/media[^\"]+\".*?><img.*? src=\"([^\"]+)\"!ms", $pageBuffer, $matches))
			{
				if(starts_with($matches[1], 'http://'))
					$this->addItemAttribute('imageurl', $matches[1]);
				else
					$this->addItemAttribute('imageurl', 'http://'.$matches[1]);
			}
		}
	
/*  <h4 class="inline">
    Directors:
  </h4>

<a  href="/name/nm0905152/">Andy Wachowski</a>, <a  href="/name/nm0905154/">Lana Wachowski</a></div>
*/
		if(preg_match("!<h4.*?Director(.*?)/div>!ms", $pageBuffer, $matches))
		{
			$buffer = $matches[1];
			if(preg_match_all("!<a.*?href=\"/name/nm([0-9]+)/\"[^>]*>([^<]+)</a>!", $buffer, $matches))
			{
				$this->addItemAttribute('director', $matches[2]);
			}
		}

		if(preg_match("!<h4.*?Writer(.*?)</div>!ms", $pageBuffer, $matches))
		{
			$buffer = $matches[1];
			if(preg_match_all("!<a.*?href=\"/name/nm([0-9]+)/\"[^>]*>([^<]+)</a>!", $buffer, $matches))
			{
				$this->addItemAttribute('writer', $matches[2]);
			}
		}
		
/*<div class="infobar">
105 min&nbsp;&nbsp;-&nbsp;&nbsp;<a    onclick="(new Image()).src='/rg/title-overview/genre/images/b.gif?link=%2Fgenre%2FAction';"     href="/genre/Action"       >Action</a>&nbsp;<span>|</span> <a    onclick="(new Image()).src='/rg/title-overview/genre/images/b.gif?link=%2Fgenre%2FHorror';"     href="/genre/Horror"       >Horror</a>&nbsp;<span>|</span> <a    onclick="(new Image()).src='/rg/title-overview/genre/images/b.gif?link=%2Fgenre%2FSci-Fi';"     href="/genre/Sci-Fi"       >Sci-Fi</a>

</div>
*/
		$start = strpos($pageBuffer,"<div class=\"infobar\">", $end);
		if($start !== FALSE)
		{
			$end = strpos($pageBuffer,"</div>", $start);
			
			$genre = trim(substr($pageBuffer,$start,$end-$start));
			
			if(preg_match("!([0-9]*) min!", $genre, $matches))
			{
				$this->addItemAttribute('run_time', $matches[1]);
			}

			if(preg_match_all("!<a.*?href=\"/genre/.*?>([^<]+)</a>!", $genre, $matches))
			{
				$this->addItemAttribute('genre', $matches[1]);
			}
		}

//id="star-bar-user-rate"><b>8.7</b><span class="mellow">/10</span>
		if(preg_match("!id=\"star-bar-user-rate\">.*?<b>([0-9|\.]+)</b>!ms", $pageBuffer, $regs))
		{
			$this->addItemAttribute('imdbrating', $regs[1]);
		}
		
		$start = strpos($pageBuffer,"<table class=\"cast_list\">");
		if($start!==FALSE)
		{
			$end = strpos($pageBuffer,"</table>", $start);
			$actorBlock = substr($pageBuffer, $start, $end-$start);

			if(preg_match_all("!<tr.*?<td class=\"name\">.*?<a.*?href=\"/name/([^\"]*)\"[^>]*>([^<]*)</a>".
							".*?".
//							"<td class=\"character\">.*?<a href=\"([^\"]*)\"[^>]*>([^<]*)</a>.*?</td>".
							"</tr>!ms", $actorBlock, $matches))
			{
				$this->addItemAttribute('actors', $matches[2]);
			}
		}
	
		
//FCG 
/* <h4 class="inline">Language:</h4>

<a href="/language/en">English</a>

</div>
*/
                if(preg_match_all("!<a href=\"/language/[^\"]*\">([^/]*)</a>!", $pageBuffer, $matches))
                {
                        $this->addItemAttribute('audio_lang', $matches[1]);
                }

		if(preg_match("!>Aspect Ratio:</h4>[\s]*([0-9\.]*)[\s]*:[\s]*([0-9\.]*)!", $pageBuffer, $matches))
		{
			$this->addItemAttribute('ratio', $matches[1]);
		}
		
//FCG	
                /*
                    <div class="info">
                    <h5>Sound Mix:</h5>
                    <div class="info-content">

                    <a href="/List?sound-mix=Dolby%20Digital&&heading=15;Dolby%20Digital">
                    Dolby Digital</a> 
                    </div>
                    </div>
                */
                if(preg_match_all("!<a href=\"/search/title\?sound_mixes=.*?>(.*?)</a>!", $pageBuffer, $matches))
                {
                        $this->addItemAttribute('dvd_audio', $matches[1]);
                }

		//Certification extraction block
		$start = strpos($pageBuffer,"<h5>Certification:</h5>", $end);
		if($start !== FALSE)
		{
			$end = strpos($pageBuffer,"</div>", $start);
			$certification = trim(substr($pageBuffer,$start,$end-$start));
			
			$age_certification_codes_r = $this->getConfigValue('age_certification_codes');
			if(!is_array($age_certification_codes_r) && strlen($age_certification_codes_r)>0) // single value
				$age_certification_codes_r = array($age_certification_codes_r);
			
			// get a list of all age ratings, with a country prefix.
			if(preg_match_all("!<a href=\"/[^\"]*\">([^:]*):([^<]*)</a>!", $certification, $matches))
			{
				for($i=0; $i<count($matches[1]); $i++)
				{
					$country = strtolower(trim(str_replace(' ', '_', $matches[1][$i])));
					$this->addItemAttribute($country.'_age_rating', $matches[2][$i]);
				}
			}
			
			if(is_array($age_certification_codes_r)) // single value
			{
				reset($age_certification_codes_r);
				while (list(,$country) = @each($age_certification_codes_r))
				{
					$country = strtolower($country);
					
					$ageRating = $this->getItemAttribute($country.'_age_rating');
					if($ageRating!==FALSE) 
					{
						$this->addItemAttribute('age_rating', $ageRating);
						break; // found it!
					}
				}
			}
		}

		$plotPage = $this->fetchURI("http://us.imdb.com/Plot?".$search_attributes_r['imdb_id']);

		if(preg_match_all("!<p class=\"plotpar\">[\s]*(.*?)<i>[\s]*Written by[\s]*<a href=\"([^<]*)\">(.*?)</a>[\s]*</i>[\s]*</p>!ms", $plotPage, $matches))
		{
			$this->addItemAttribute('plot', $matches[1]);
		}
		unset($plotPage);
		
		return TRUE;
	}
}
?>
