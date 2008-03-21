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
										    
											if(preg_match("/<br>&#160;aka(.*)/", $matches2[3], $regs))
										    {
										    	$comments = unhtmlentities(strip_tags($regs[1]));
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
		
		// no sense going any further here.
		if(strlen($pageBuffer)==0)
			return FALSE;
			
		if(preg_match("!<h1>([^<]+)<span>\(<a href=\"/Sections/Years/.*\">([0-9]+)</a>[^\)]*\)</span></h1>!", $pageBuffer, $matches))
		{
			$this->addItemAttribute('title', $matches[1]);
			$this->addItemAttribute('year', $matches[2]);
		}
		
		//image src extraction block
		$start = strpos($pageBuffer,"alt=\"No poster or movie still available\"", $end);
		if($start === FALSE)
		{
			//<a name="poster" href="photogallery" title="&#34;Band of Brothers&#34;"><img border="0" alt="&#34;Band of Brothers&#34;" title="&#34;Band of Brothers&#34;" src="http://ia.imdb.com/media/imdb/01/I/56/50/31m.jpg" height="122" width="100"></a>
			if(preg_match("/<a name=\"poster\" href=\"[^\"]+\" .*><img .* src=\"([^\"]+)\"/", $pageBuffer, $matches))
			{
				if(starts_with($matches[1], 'http://'))
					$this->addItemAttribute('imageurl', $matches[1]);
				else
					$this->addItemAttribute('imageurl', 'http://'.$matches[1]);
			}
		}
	
		/*<div class="info">
		<h5>Director:</h5>
		<a href="/name/nm0467646/">Ted Kotcheff</a><br>
		</div>*/
/*<h5>Director:</h5>
<a href="/name/nm0268380/">Peter Farrelly</a><br><a class="tn15more" href="fullcredits#directors">more</a>
*/
		if(preg_match("!<h5>Director:</h5>[\s]*<a href=\".*?\">([^<]*)</a>!", $pageBuffer, $matches))
		{
			$this->addItemAttribute('director', $matches[1]);
		}
		
		$start = strpos($pageBuffer,"<h5>Genre:</h5>", $end);
		if($start !== FALSE)
		{
			$end = strpos($pageBuffer,"</div>", $start);
			
			$genre = trim(substr($pageBuffer,$start,$end-$start));
			
			if(preg_match_all("!<a href=\"/Sections/Genres/[^/]+/\">([^<]+)</a>!", $genre, $matches))
			{
				$this->addItemAttribute('genre', $matches[1]);
			}
		}
	
		if(preg_match("!<b>User Rating:</b>[\s]*<b>([0-9|\.]+)/10</b>!", $pageBuffer, $regs))
		{
			$this->addItemAttribute('imdbrating', $regs[1]);
		}
		
		$start = strpos($pageBuffer,"<table class=\"cast\">");
		if($start!==FALSE)
		{
			$end = strpos($pageBuffer,"</table>", $start);
			$actorBlock = substr($pageBuffer, $start, $end);

			if(preg_match_all("!<tr.*?>".
							"<td.*?>(.*?)</td>".
							"<td.*?><a href=\"([^\"]*)\">([^<]*)</a></td>".
							"<td.*?>([^<]*)</td>".
							"<td.*?>([^<]*)</td></tr>!", $actorBlock, $matches))
			{
				$this->addItemAttribute('actors', $matches[3]);
			}
		}
	
		if(preg_match("!<h5>Runtime:</h5>[\s]*([0-9]*) min!", $pageBuffer, $matches))
		{
			$this->addItemAttribute('run_time', $matches[1]);
		}
		
		/**
		 * <div class="info">
		 <h5>Aspect Ratio:</h5>
		 2.35 : 1 <a class="tn15more inline" href="/rg/title-tease/aspect/title/tt0083944/technical">more</a>
		 </div>
		 */
		if(preg_match("!<h5>Aspect Ratio:</h5>[\s]*([0-9\.]*)[\s]*:[\s]*([0-9\.]*)!", $pageBuffer, $matches))
		{
			$this->addItemAttribute('ratio', $matches[1]);
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
			
			if(is_array($age_certification_codes_r)) // single value
			{
				reset($age_certification_codes_r);
				while (list(,$age_code) = @each($age_certification_codes_r))
				{
					if(preg_match("!<a href=\"/[^\"]*\">".preg_quote($age_code, "!").":([^<]*)</a>!", $certification, $matches))
					{
						$this->addItemAttribute('age_rating', $matches[1]);
						break; // found it!
					}
				}
			}
			
			// get a list of all age ratings, with a country prefix.
			if(preg_match_all("!<a href=\"/[^\"]*\">([^:]*):([^<]*)</a>!", $certification, $matches))
			{
				for($i=0; $i<count($matches[1]); $i++)
				{
					$country = strtolower(str_replace(' ', '_', $matches[1][$i]));
					$this->addItemAttribute($country.'_age_rating', $matches[2][$i]);
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
