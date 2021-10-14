<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001,2013 by Jason Pell
    comicbookdb.com Site Plugin for Open Media Collectors Database
    Copyright (C) 2007 by Joe Miller

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

NOTES FROM THE AUTHOR:

    For the search fields:
    
        An entry in Title Search only will get you the title of the series.
        An entry in Title ID only will get you the direct title
        An entry in Issue ID only will get you the direct issue
    
    An entry in Title Search AND Issue Number will bring up the title search.  If you select
    a title it will search that title for the Issue Number and bring up that issue if it finds it.
    
    An entry in Title ID AND Issue Number will directly search that Title ID and search for
    Issue Number in that.

    -Joe Miller
 */

include_once("./lib/SitePlugin.class.php");

class cmbkdbt extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (strlen($search_vars_r['cmbkdbt_id']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('cmbkdbt_id' => $search_vars_r['cmbkdbt_id'], 'issue_num' => $search_vars_r['issue_num']));
			return TRUE;
		} else if (strlen($search_vars_r['cmbkdbi_id']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('cmbkdbi_id' => $search_vars_r['cmbkdbi_id']));
			return TRUE;
		}

		$pageBuffer = $this->fetchURI("http://www.comicbookdb.com/search.php?form_search=" . urlencode(strtolower(trim($search_vars_r['title']))) . "&form_searchtype=Title");
		if (strlen($pageBuffer) > 0) {
			$start = strpos($pageBuffer, "<strong>Your search:</strong>");
			if ($start) {
				$end = strpos($pageBuffer, "</td>", $start);
				if ($end) {
					// <a href="title.php?ID=2699">40 Years of the Amazing Spider-Man (2004)</a> (Topics Entertainment)<br>
					$cbktitles = substr($pageBuffer, $start, $end - $start);

					$pattern = "!<a href=[\"]title.php[\?]ID=([0-9]+)[\"]>([\-A-Za-z0-9\s\.:]+)\(([0-9]+)[\)]</a>[\s]\(([\-A-Za-z0-9\s\.\+/]+)!";
					$results = preg_match_all($pattern, $cbktitles, $gmatches);
					if ($results) {
						for ($i = 0; $i < $results; $i++) {
							$title = $gmatches[2][$i];
							$image = "";
							$cmbk_id = $gmatches[1][$i];
							$comments = $gmatches[3][$i] . " " . $gmatches[4][$i];

							$this->addListingRow($title, $image, $comments, array('cmbkdbt_id' => $cmbk_id));
						}
					}
				}
			}

			//default
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function queryItem($search_attributes_r, $s_item_type) {
		// http://www.comicbookdb.com/title.php?ID=14795
		if ($search_attributes_r['cmbkdbt_id'] > 0) {
			return $this->queryItemTitle($search_attributes_r, $s_item_type);
		} else if ($search_attributes_r['cmbkdbi_id'] > 0) {
			return $this->queryItemIssue($search_attributes_r, $s_item_type);
		}

		return FALSE;
	}

	function queryItemTitle($search_attributes_r, $s_item_type) {
		//$search_attributes_r['issue_num'];
		$pageBuffer = $this->fetchURI("http://www.comicbookdb.com/title.php?ID=" . $search_attributes_r['cmbkdbt_id']);

		if (strlen($pageBuffer) > 0) {
			$this->addItemAttribute('cmbkdbt_id', $search_attributes_r['cmbkdbt_id']);
			if (strlen($search_attributes_r['issue_num']) > 0) {
				$num = strtolower($search_attributes_r['issue_num']);
				//<td align="right" valign="top"> <a href="issue.php?ID=31699">1</a><br></td>
				$results = preg_match_all("!<td align=\"right\" valign=\"top\">\s?<a href=\"issue.php\?ID=([0-9]+)\">([^<]+)</a><br></td>!", $pageBuffer, $matches);
				if ($results) {
					for ($i = 0; $i < $results; $i++) {
						if ($num === strtolower($matches[2][$i])) {
							$search_attributes_r['cmbkdbi_id'] = $matches[1][$i];
							return $this->queryItemIssue($search_attributes_r, $s_item_type);
						}
					}
				}
				return FALSE;
			}

			//<span class="page_headline">Amazing Adult Fantasy</span><br>
			if (preg_match("!<span class=\"page_headline\">([^<]+)!", $pageBuffer, $matches)) {
				$this->addItemAttribute('title', $matches[1]);
			}

			//<strong>Publisher: </strong><a href="imprint.php?ID=16">All*Star</a> (<a href="publisher.php?ID=1">DC Comics</a>)
			//<strong>Publisher: </strong><a href="publisher.php?ID=4">Marvel Comics</a></a><br><br>
			if (preg_match("!</strong><a href=\"imprint.php\?ID=[0-9]+\">([^<]+)!", $pageBuffer, $matches)) {
				$this->addItemAttribute('publisher', $matches[1]);
			}

			$notes = "";
			if (preg_match("!Notes: </strong><br>\n[\s]+([^<]+)!", $pageBuffer, $matches)) {
				$notes = $matches[1] . "\n";
			}

			//<strong>Title Continuity:</strong><br><a href="title.php?ID=14328">Amazing Adult Fantasy (1961)</a> #7 continues from <a href="title.php?ID=4015">Amazing Adventures (1961)</a> #6<br><a href="title.php?ID=14328">Amazing Adult Fantasy (1961)</a> #14 continues to <a href="title.php?ID=111">Amazing Fantasy (1961)</a> #15      <br><br>
			$start = strpos($pageBuffer, "<strong>Title Continuity:</strong><br>");
			if ($start) {
				$end = strpos($pageBuffer, "<br><br>", $start);
				$section = substr($pageBuffer, $start, $end - $start);

				$results = preg_match_all("!<[^>]+>([^<]+)!", $section, $matches);
				if ($results) {
					for ($i = 0; $i < $results; $i++) {
						$notes = $notes . $matches[1][$i] . " ";
					}
				}
			}
			$this->addItemAttribute('notes', $notes);

			//<a href="issue_add.php?titleID=14328&p=4&i=&m=12&y=1961">
			if (preg_match("!issue_add.php\?titleID=[0-9]+&p=[0-9]+&i=&m=([0-9]+)&y=([0-9]+)\">!", $pageBuffer, $matches)) {
				if (!preg_match("![0-9][0-9]!", $matches[1], $blah)) {
					$matches[1] = "0" . $matches[1];
				} else if (!strcmp($matches[1], "17")) {
					$matches[1] = "";
				} else if (!strcmp($matches[1], "18")) {
					$matches[1] = "";
				}

				//$cbkdate = $matches[1].$matches[2];
				$cbkdate = $matches[2]; // year only currently supported.
				$this->addItemAttribute('release', $cbkdate);
			}

			$this->addItemAttribute('genre', "comic");

			//<a href="issue.php?ID=1611">1</a>
			if (preg_match("!<a href=\"([^\"]+)\">1<!", $pageBuffer, $the_url)) {
				$pageBuffertemp = $this->fetchURI("http://www.comicbookdb.com/" . $the_url[1]);
				if (strlen($pageBuffertemp) > 0) {
					//<img src="graphics/comic_graphics/1/133/67090_20061021064647_thumb.jpg"
					if (preg_match("!<img src=\"(graphics/comic_graphics/[0-9/_]+_thumb.jpg)\"!", $pageBuffertemp, $matches)) {
						$this->addItemAttribute('imageurl', "http://www.comicbookdb.com/" . $matches[1]);
					}
				}
			} else if (preg_match("!<a href=\"([^\"]+)\">GN<!", $pageBuffer, $the_url)) {
				$pageBuffertemp = $this->fetchURI("http://www.comicbookdb.com/" . $the_url[1]);
				if (strlen($pageBuffertemp) > 0) {
					//<img src="graphics/comic_graphics/1/133/67090_20061021064647_thumb.jpg"
					if (preg_match("!<img src=\"(graphics/comic_graphics/[0-9/_]+_thumb.jpg)\"!", $pageBuffertemp, $matches)) {
						$this->addItemAttribute('imageurl', "http://www.comicbookdb.com/" . $matches[1]);
					}
				}
			} else if (preg_match("!<a href=\"([^\"]+)\">CD Collection<!", $pageBuffer, $the_url)) {
				$pageBuffertemp = $this->fetchURI("http://www.comicbookdb.com/" . $the_url[1]);
				if (strlen($pageBuffertemp) > 0) {
					//<img src="graphics/comic_graphics/1/133/67090_20061021064647_thumb.jpg"
					if (preg_match("!<img src=\"(graphics/comic_graphics/[0-9/_]+_thumb.jpg)\"!", $pageBuffertemp, $matches)) {
						$this->addItemAttribute('imageurl', "http://www.comicbookdb.com/" . $matches[1]);
					}
				}
			} else if (preg_match("!<a href=\"([^\"]+)\">TPB<!", $pageBuffer, $the_url)) {
				$pageBuffertemp = $this->fetchURI("http://www.comicbookdb.com/" . $the_url[1]);
				if (strlen($pageBuffertemp) > 0) {
					//<img src="graphics/comic_graphics/1/133/67090_20061021064647_thumb.jpg"
					if (preg_match("!<img src=\"(graphics/comic_graphics/[0-9/_]+_thumb.jpg)\"!", $pageBuffertemp, $matches)) {
						$this->addItemAttribute('imageurl', "http://www.comicbookdb.com/" . $matches[1]);
					}
				}
			}

			return TRUE;
		}
		return FALSE;
	}

	function queryItemIssue($search_attributes_r, $s_item_type) {
		$pageBuffer = $this->fetchURI("http://www.comicbookdb.com/issue.php?ID=" . $search_attributes_r['cmbkdbi_id']);
		if (strlen($pageBuffer) > 0) {
			//<span class="page_headline"><a href="title.php?ID=2084">Amazing Adventures (1979)</a> - #6</span><br><a href="publisher.php?ID=4">Marvel Comics</a><br>
			$start = strpos($pageBuffer, "<span class=\"page_headline\">");
			if ($start) {
				$end = strpos($pageBuffer, "<table border", $start);
				$cbktitles = substr($pageBuffer, $start, $end - $start);
				//
				if (preg_match("!href=\"title.php\?ID=([0-9]+)\">([^\(]+)[^>]+>[\s|-]+([^<]+)!", $cbktitles, $matches)) {
					$this->addItemAttribute('cbkdbi_id', $matches[1]);
					$this->addItemAttribute('title', $matches[2] . $matches[3]);

					if (preg_match("!([0-9]+)!", $matches[3], $match)) {
						$this->addItemAttribute('bookpart', $match[1]);//$match[1]);
					}
				}
			}

			//(<a href="publisher.php?ID=1">DC Comics</a>)<br>
			if (preg_match("!\(?<a href=\"publisher.php\?ID=[0-9]+\">([^<]+)\)?</a>\)?<br>!", $pageBuffer, $matches)) {
				$this->addItemAttribute('publisher', $matches[1]);
			}

			//<img src="graphics/comic_graphics/1/50/31696_20060308162742_thumb.jpg"
			if (preg_match("!<img src=\"(graphics/comic_graphics/[0-9/_]+_thumb.jpg)\"!", $pageBuffer, $matches)) {
				$this->addItemAttribute('imageurl', "http://www.comicbookdb.com/" . $matches[1]);
			}

			$illustrator = "";
			//<strong>Writer(s):</strong><br><a href="creator.php?ID=98">Stan Lee</a><br><br>
			$numwriters = preg_match_all("!Writer\(s\):!", $pageBuffer, $parsematch);
			if ($numwriters) {
				$author = "";
				$start = strpos($pageBuffer, "Writer(s):");
				$end = strpos($pageBuffer, "<br><br>", $start);

				for ($n = 0; $n < $numwriters; $n++) {
					if ($start) {
						if ($end) {
							//Writer(s):</strong><br><a href="creator.php?ID=3837">Thomas Jane</a><br><a href="creator.php?ID=2209">Steve Niles</a><br><br>
							$writeblock = substr($pageBuffer, $start, $end - $start);
							$results = preg_match_all("!creator.php\?ID=[^>]+>([a-zA-Z\s\.]+)!", $writeblock, $matches);
							if ($results) {
								for ($i = 0; $i < $results; $i++) {
									if (strpos($author, $matches[1][$i]) === FALSE) {
										if (($n == 0) && ($i == 0)) {
											$author = $matches[1][$i];
										} else {
											$author = $author . ", " . $matches[1][$i];
										}
									}
								}
							}
						}
					}
					$start = strpos($pageBuffer, "Writer(s)", $end);
					$end = strpos($pageBuffer, "<br><br>", $start);
				}
				$this->addItemAttribute('author', $author);
			}
			$this->addItemAttribute('genre', "Comic");

			$illustrator = "";
			$numartists = preg_match_all("!Penciller\(s\):!", $pageBuffer, $parsematch);
			if ($numartists) {
				$start = strpos($pageBuffer, "Penciller(s):");
				$end = strpos($pageBuffer, "<br><br>", $start);

				for ($n = 0; $n < $numartists; $n++) {
					if ($start) {
						if ($end) {
							//Writer(s):</strong><br><a href="creator.php?ID=3837">Thomas Jane</a><br><a href="creator.php?ID=2209">Steve Niles</a><br><br>
							$writeblock = substr($pageBuffer, $start, $end - $start);
							$results = preg_match_all("!creator.php\?ID=[^>]+>([a-zA-Z\s\.]+)!", $writeblock, $matches);
							if ($results) {
								for ($i = 0; $i < $results; $i++) {
									if (strpos($illustrator, $matches[1][$i]) === FALSE) {
										if (($n == 0) && ($i == 0)) {
											$illustrator = $matches[1][$i];
										} else {
											$illustrator = $illustrator . ", " . $matches[1][$i];
										}
									}
								}
							}
						}
					}
					$start = strpos($pageBuffer, "Penciller(s)", $end);
					$end = strpos($pageBuffer, "<br><br>", $start);
				}

				$this->addItemAttribute('illustrator', $illustrator);
			}

			//<span class="page_subheadline">"The Other - Evolve or Die, Part 12: Post Mortem"</span>
			$description = "";
			$notes = "";

			if (strpos($pageBuffer, "Multiple stories do not exist for this issue.")) {
				if (preg_match("!<span class=\"page_subheadline\">([^<]+)</span>!", $pageBuffer, $matches)) {
					$description = $description . $matches[1] . "\n";
					if (preg_match("!Synopsis:</strong><br>\n([^<]+)!", $pageBuffer, $match)) {
						$description = $description . "Synopsis: " . $match[1] . "\n";
					}
				}
			} else {
				$start = strpos($pageBuffer, "Multiple Stories in this Issue");
				if ($start) {
					$end = strpos($pageBuffer, "</table>", $start);
					if ($end) {
						$block = substr($pageBuffer, $start, $end - $start);
						$results = preg_match_all("!\"size13\"><strong>([^<]+)!", $block, $matches);
						if ($results) {
							for ($i = 0; $i < $results; $i++) {
								//22924
								$newstart = strpos($block, $matches[1][$i]);
								if ($newstart) {
									$newend = strpos($block, "of this story to another story", $newstart);
									$description = $description . "\n\n" . $matches[1][$i];
									if ($newend) {
										$newblock = substr($block, $newstart, $newend - $newstart);
										if (preg_match("!Synopsis:</strong><br>\n?([^<]+)!", $newblock, $match)) {
											$description = $description . "\nSynopsis: " . $match[1];
										} else {
											$description = $description . "\nSynopsis: None Entered.";
										}
										if (preg_match("!Notes:</strong><br>([^<]+)!", $newblock, $match)) {
											$description = $description . "\nNotes: " . $match[1];
										}
									}
								}
							}
						}
					}
				}
			}

			$this->addItemAttribute('synopsis', $description);
			//Add/remove story arcs to this issue</a><br><a href="storyarc.php?ID=176">The Other - Evolve or Die</a><br><br>

			//<strong>Cover Date:</strong> <a href="coverdate.php?month=17&amp;year=1989"> Annual 1989</a><br>
			if (preg_match("!Cover Date:</strong> ?<a href=\"coverdate.php\?month=([0-9]+)&amp;year=([0-9]+)\">!", $pageBuffer, $matches)) {
				if (!preg_match("![0-9][0-9]!", $matches[1], $blah)) {
					$matches[1] = "0" . $matches[1];
				} else if (!strcmp($matches[1], "17")) {
					$matches[1] = "";
				} else if (!strcmp($matches[1], "18")) {
					$matches[1] = "";
				}

				//$cbkdate = $matches[1].$matches[2];
				$cbkdate = $matches[2]; // year only supported.
				$this->addItemAttribute('release', $cbkdate);
			}

			$start = strpos($pageBuffer, "Add/remove story arcs to this issue");
			if ($start) {
				$end = strpos($pageBuffer, "<br><br>", $start);
				if ($end) {
					$storyline = "";
					$arcblock = substr($pageBuffer, $start, $end - $start);
					$results = preg_match_all("!storyarc.php\?ID=[0-9]+\">([^<]+)!", $arcblock, $matches);
					if ($results) {
						for ($i = 0; $i < $results; $i++) {
							if ($i == 0) {
								$blah = preg_replace("(,\s?)", " ", $matches[1][0]);
								$storyline = $storyline . $blah;
							} else {
								$blah = preg_replace("(,\s?)", " ", $matches[1][$i]);
								$storyline = $storyline . ", " . $blah;
							}
						}

						$this->addItemAttribute('storyline', $storyline);
					}
				}
			}
			$this->addItemAttribute('notes', $notes);

			$start = strpos($pageBuffer, "Format:</strong>");
			if ($start) {
				$end = strpos($pageBuffer, "<br><br>", $end);
				if ($end) {
					$block = substr($pageBuffer, $start, $end - $start);
					if (preg_match("!([0-9]+) pages!", $block, $matches)) {
						$this->addItemAttribute('pages', $matches[1]);
					}
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
