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
include_once("./site/imdbphp2/imdb.class.php");
include_once("./site/imdbphp2/imdbsearch.class.php");



class imdbphp extends SitePlugin
{
	var $results;
	function imdbphp($site_type)
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
			if($this->_site_plugin_conf_r['title_search_faster_alternate']!='TRUE')
			{
				$imdbsearch = new imdbsearch();
				$imdbsearch->setsearchname($search_vars_r['title']);
					$this->results = $imdbsearch->results();
				
				
				if(is_array($this->results))
				{
					foreach($this->results as $res_id => $res)
					{
						$this->addListingRow($res->title(), $res->photo(), $res->year(), array('imdb_id'=>$res->imdbid(), 'res_id'=>$res_id));
					}
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
			else
			{
				$pageBuffer = $this->fetchURI("http://www.imdb.com/find?q=".rawurlencode(strtolower($search_vars_r['title'])).";more=tt");
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
		}
	}
	
	function queryItem($search_attributes_r, $s_item_type)
	{
		if(is_array($this->results))
		{
			$imdb=$this->results[$search_attributes_r['res_id']];
			unset($this->results);
		}
		else
		{
			$imdb= new imdb($search_attributes_r['imdb_id']);
		}
		$imdb->imdb_utf8recode = get_opendb_config_var('themes', 'charset')=='utf-8'?TRUE:FALSE;
		$this->addItemAttribute('title', $imdb->title());
		$this->addItemAttribute('year', $imdb->year());
		foreach($imdb->alsoknow() as $alt_title)
		{
			$this->addItemAttribute('alt_title', array($alt_title['country'] => $alt_title['title']));				
		}
		if($imdb->photo()) $this->addItemAttribute('imageurl', $imdb->photo());
		foreach($imdb->director() as $person)
		{
			$this->addItemAttribute('director', $person['name']);				
		}
		foreach($imdb->producer() as $person)
		{
			if(stristr($person['role'], 'executive')) $this->addItemAttribute('exproducer', $person['name']);
			else $this->addItemAttribute('producer', $person['name']);
		}
		foreach($imdb->writing() as $person)
		{
			$this->addItemAttribute('writer', $person['name']);				
		}
		foreach($imdb->composer() as $person)
		{
			$this->addItemAttribute('composer', $person['name']);				
		}
		foreach($imdb->cast() as $person)
		{
			$this->addItemAttribute('actors', $person['name']);				
		}
		$this->addItemAttribute('genre', $imdb->genres());
		$this->addItemAttribute('imdbrating', $imdb->rating());
		$this->addItemAttribute('run_time', $imdb->runtime());
		$this->addItemAttribute('audio_lang', $imdb->languages());
		$this->addItemAttribute('dvd_audio', $imdb->sound());
		foreach($imdb->mpaa() as $country => $rating)
		{
				$this->addItemAttribute($country.'_age_rating', $rating);				
		}
		$age_certification_codes_r = $this->getConfigValue('age_certification_codes');
		if(!is_array($age_certification_codes_r) && strlen($age_certification_codes_r)>0) // single value
			$age_certification_codes_r = array($age_certification_codes_r);			
		if(is_array($age_certification_codes_r)) // get a single value for the age rating depending on the users settings
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
		$this->addItemAttribute('plot', $imdb->plot());
		/** aspect ratio is not supported by imdbphp yet.
		 * <div class="info">
		 <h5>Aspect Ratio:</h5>
		 2.35 : 1 <a class="tn15more inline" href="/rg/title-tease/aspect/title/tt0083944/technical">more</a>
		 </div>
		
		if(preg_match("!<h5>Aspect Ratio:</h5>[\s]*([0-9\.]*)[\s]*:[\s]*([0-9\.]*)!", $pageBuffer, $matches))
		{
			$this->addItemAttribute('ratio', $matches[1]);
		}*/

		return TRUE;
	}
}
?>
