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

$mpaa_age_certification_map = 
				array(
					'PG-13'=>'PG',
					'R'=>'MA',
					'NC-17'=>'MA',
					'X'=>'R');

include_once("./functions/XMLImportPlugin.class.php");

class DVDProfiler_XML extends XMLImportPlugin
{
	var $_image_prefix = "http://www.dvdprofiler.com/cgi-bin/data/myprofiler/images/";
	
	var $v_category_r = NULL;
	var $v_region_r = NULL;
	var $v_studio_r = NULL;
	
	var $v_audio = NULL;
	var $v_audio_r = NULL;
	
	var $v_subtitle_r = NULL;

	var $v_director_r = NULL;
	var $v_director = NULL;
	var $v_actor_r = NULL;
	var $v_actor = NULL;
	var $v_extras = NULL;
	
	// parent element name - only used by specific elements
	var $v_element_name;
	
	function DVDProfiler_XML() {
		parent::XMLImportPlugin();
	}
	
	function get_display_name()
	{
		return 'DVD Profiler Collection XML';
	}
	
	function get_plugin_type()
	{
		return 'xml';
	}
	
	function is_doctype_supported($doctype)
	{
		return (strcasecmp($doctype, 'COLLECTION') === 0);
	}

	function start_element($name, $attribs, $pcdata)
	{
		if($this->v_element_name == 'Locks' || strcmp($name, 'Locks')===0)
		{
			if($this->v_element_name != 'Locks')
			{
				// ignore all elements until close of Lock tag section
				$this->v_element_name = 'Locks';
			}
		}
		else if(strcmp($name, 'DVD')===0)
		{
			$this->startItem('DVD');
		}
		else if(strcmp($name, 'ID')===0)
		{
			$this->addAttribute('IMAGEURL', NULL, array($this->_image_prefix.$pcdata."f.jpg", $this->_image_prefix.$pcdata."b.jpg"));
			$this->addAttribute('FIMAGEURL', NULL, $this->_image_prefix.$pcdata."f.jpg");
			$this->addAttribute('BIMAGEURL', NULL, $this->_image_prefix.$pcdata."b.jpg");
		}
  		else if(strcmp($name, 'Title')===0)
		{
			$this->setTitle($pcdata);
		}
		else if(strcmp($name, 'UPC')===0)
		{
			if(strlen($pcdata)>0)
			{
				$this->addAttribute('UPC_ID', NULL, $pcdata);
			}
		}
		else if(strcmp($name, 'Genres')===0)
		{
			$this->v_element_name = 'Genres';
		}
		else if(strcmp($this->v_element_name, 'Genres')===0)
		{
			if(strcmp($name, 'Genre')===0)
			{
				if(strlen($pcdata)>0)
				{
					/* The genre's supported by DVD Profiler are as follows:
						Accessories, Action, Adult, Adventure, Animation, Anime, Classic, Comedy, Documentary
						Drama, Family, Fantasy, Foreign, Horror, Music, Musical, Romance, Science-Fiction
						Special Interest, Sports, Suspence/Thriller, Television, War, Western
					*/
					
					if($pcdata == 'Science-Fiction')
					{
						$this->v_category_r[] = 'ScienceFiction';
					}
					else if($pcdata == 'Suspence/Thriller')
					{
						$this->v_category_r[] = 'Suspense';
						$this->v_category_r[] = 'Thriller';
					}
					else if($pcdata == 'Special Interest')
					{
						$this->v_category_r[] = 'Other';
					}
					else
					{
						$this->v_category_r[] = $pcdata;
					}
				}
			}
		}
		else if(strcmp($name, 'CollectionType')===0)
		{
			if(strcmp($pcdata, 'WishList') === 0) {
				$this->itemInstance('W');
			} else if(strcmp($pcdata, 'Ordered') === 0) {
				$this->itemInstance('O');
			} else { //if(strcmp($pcdata, 'Owned') === 0)
				$this->itemInstance('A');
			}
		}
		else if(strcmp($name, 'Rating')===0)
		{
			if(strlen($pcdata)>0)
			{
				global $mpaa_age_certification_map;
				if(is_array($mpaa_age_certification_map))
				{
					if(strlen($mpaa_age_certification_map[$pcdata])>0)
						$pcdata = $mpaa_age_certification_map[$pcdata];
				}
				$this->addAttribute('AGE_RATING', NULL, $pcdata);
			}
		}
		else if(strcmp($name, 'ProductionYear')===0)
		{
			if(strlen($pcdata)>0)
			{
				$this->addAttribute('YEAR', NULL, $pcdata);
			}
		}
		else if(strcmp($name, 'Released')===0)
		{
			if(strlen($pcdata)>0)
			{
				// Date Format YYYY-MM-DD
				list($year, $month, $day) = sscanf($pcdata,"%d-%d-%d");

				$this->addAttribute(
						'DVD_REL_DT', 
						NULL,
						str_pad($year,4,'0', STR_PAD_LEFT) // Format: 'YYYYMMDDHH24MISS'
							.str_pad($month,2,'0', STR_PAD_LEFT)
							.str_pad($day,2,'0', STR_PAD_LEFT)
							.'00' // hours
							.'00' // minutes
							.'00'); // seconds
			}
		}
		else if(strcmp($name, 'RunningTime')===0)
		{
			$this->addAttribute('RUN_TIME', NULL, $pcdata);
		}
		else if(strcmp($name, 'Regions')===0)
		{
			$this->v_element_name = 'Regions';
		}
		else if(strcmp($this->v_element_name, 'Regions')===0)
		{
			if(strcmp($name, 'Region')===0)
			{
				if(strlen($pcdata)>0)
				{
					$this->v_region_r[] = $pcdata;
				}
			}
		}
		else if(strcmp($name, 'Format')===0)
		{
			$this->v_element_name = 'Format';
		}
		else if(strcmp($this->v_element_name, 'Format')===0)
		{
			if(strcmp($name, 'FormatVideoStandard')===0)
			{
				if(strlen($pcdata)>0)
				{
					$this->addAttribute('VID_FORMAT', NULL, $pcdata);
				}
			}
			else if(strcmp($name, 'FormatAspectRatio')===0)
			{
				if(strlen($pcdata)>0)
				{
					$this->addAttribute('RATIO', NULL, $pcdata);
				}
			}
			else if(strcmp($name, 'FormatLetterBox')===0)
			{
			}
			else if(strcmp($name, 'FormatPanAndScan')===0)
			{
			}
			else if(strcmp($name, 'FormatFullFrame')===0)
			{
			}
			else if(strcmp($name, 'Format16X9')===0)
			{
				if(strcmp($pcdata, 'True')===0) {
					$this->addAttribute('ANAMORPHIC', NULL, 'Y');
				}
			}
			else if(strcmp($name, 'FormatDualSided')===0)
			{
			}
			else if(strcmp($name, 'FormatDualLayered')===0)
			{
			}
			else if(strcmp($name, 'FormatFlipper')===0)
			{
			}
		}
		else if(strcmp($name, 'Features')===0)
		{
			$this->v_element_name = 'Features';
		}
		else if(strcmp($this->v_element_name, 'Features')===0)
		{
			if(strcmp($name, 'FeatureSceneAccess')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Scene Access';
			}
			else if(strcmp($name, 'FeatureCommentary')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Commentary';
			}
			else if(strcmp($name, 'FeatureTrailer')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Trailer(s)';
			}
			else if(strcmp($name, 'FeatureDeletedScenes')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Deleted Scenes';
			}
			else if(strcmp($name, 'FeatureMakingOf')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Featurette';
			}
			else if(strcmp($name, 'FeatureProductionNotes')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Prod. Notes/Bios';
			}
			else if(strcmp($name, 'FeatureGame')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Interactive Game';
			}
			else if(strcmp($name, 'FeatureOther')===0)
			{
				if(strlen($pcdata)>0)
					$this->v_extras[] = $pcdata;
			}
			else if(strcmp($name, 'FeatureDVDROMContent')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'DVD-ROM Content';
			}
			else if(strcmp($name, 'FeatureMultiAngle')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Multi-angle';
			}
			else if(strcmp($name, 'FeatureMusicVideos')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Music Video(s)';
			}
			else if(strcmp($name, 'FeatureClosedCaptioned')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'Closed Captioned';
			}
			else if(strcmp($name, 'FeatureTHXCertified')===0)
			{
				if(strcmp($pcdata, 'True')===0)
					$this->v_extras[] = 'THX Certified';
			}
		}
		else if(strcmp($name, 'Studios')===0)
		{
			$this->v_element_name = 'Studios';
		}
		else if(strcmp($this->v_element_name, 'Studios')===0)
		{
			if(strcmp($name, 'Studio')===0)
			{
				if(strlen($pcdata)>0)
				{
					$this->v_studio_r[] = $pcdata;
				}
			}
		}
		else if(strcmp($name, 'Audio')===0)
		{
			$this->v_element_name = 'Audio';
		}
		else if(strcmp($this->v_element_name, 'Audio')===0)
		{
			if(strcmp($name, 'AudioFormat')===0)
			{
				$this->v_element_name = 'Audio/AudioFormat';
			}
		}
		else if(strcmp($this->v_element_name, 'Audio/AudioFormat')===0)
		{
			if(strcmp($name, 'AudioLanguage')===0)
			{
				if($pcdata == 'Commentary')
					$this->v_audio['language'] = 'DIR_COMMENT';
				else if($pcdata == 'Music Only') { /* do nothing */ }
				else if($pcdata == 'Other') { /* do nothing */ }
				else
				{
					$this->v_audio['language'] = strtoupper($pcdata);
				}
			}
			else if(strcmp($name, 'AudioCompression')===0)
			{
				if(starts_with($pcdata, 'DD'))
					$this->v_audio['type'] = 'DD';
				else if(starts_with($pcdata, 'PCM'))
					$this->v_audio['type'] = 'PCM';
				else if(starts_with($pcdata, 'DTS'))
					$this->v_audio['type'] = 'DTS';
				else if(starts_with($pcdata, 'MPEG'))
					$this->v_audio['type'] = 'MPEG';
			}
			else if(strcmp($name, 'AudioChannels')===0)
			{
				if($pcdata == 'Mono')
					$this->v_audio['channels'] = '1.0';
				else if($pcdata == 'Stereo')
					$this->v_audio['channels'] = '2.0';
				else if($pcdata == 'Dolby Surround')
					$this->v_audio['channels'] = '3.0';
				else if($pcdata == 'Pro-Logic')
					$this->v_audio['channels'] = '4.0';
				else if($pcdata == '5.0 Surround')
					$this->v_audio['channels'] = '5.0';
				else if($pcdata == '5.1 Surround')
					$this->v_audio['channels'] = '5.1';
				else if($pcdata == '6.1 Surround')
					$this->v_audio['channels'] = '6.1';
				else if($pcdata == '7.1 Surround')
					$this->v_audio['channels'] = '7.1';
			}
		}
		else if(strcmp($name, 'Subtitles')===0)
		{
			$this->v_element_name = 'Subtitles';
		}
		else if(strcmp($this->v_element_name, 'Subtitles')===0)
		{
			if(strcmp($name, 'Subtitle')===0)
			{
				if(strlen($pcdata)>0)
				{
					$this->v_subtitle_r[] = $pcdata;
				}
			}
		}
		else if(strcmp($name, 'Directors')===0)
		{
			$this->v_element_name = 'Directors';
		}
		else if(strcmp($this->v_element_name, 'Directors')===0)
		{
			if(strcmp($name, 'Director')===0)
			{
				$this->v_element_name = 'Directors/Director';
			}
		}
		else if(strcmp($this->v_element_name, 'Directors/Director')===0)
		{
			if(strcmp($name, 'FirstName')===0)
			{
				if(strlen($pcdata)>0)
				{
					// in case LastName was already specified.
					if(strlen($this->v_director)>0)
						$this->v_director = $pcdata.' '.$this->v_director;
					else
						$this->v_director = $pcdata;
				}
			}
			else if(strcmp($name, 'LastName')===0)
			{
				if(strlen($pcdata)>0)
				{
					// in case LastName was already specified.
					if(strlen($this->v_director)>0)
						$this->v_director .= ' '.$pcdata;
					else
						$this->v_director = $pcdata;
				}
			}
		}
		else if(strcmp($name, 'Actors')===0)
		{
			$this->v_element_name = 'Actors';
		}
		else if(strcmp($this->v_element_name, 'Actors')===0)
		{
			if(strcmp($name, 'Actor')===0)
			{
				$this->v_element_name = 'Actors/Actor';
			}
		}
		else if(strcmp($this->v_element_name, 'Actors/Actor')===0)
		{
			if(strcmp($name, 'FirstName')===0)
			{
				if(strlen($pcdata)>0)
				{
					// in case LastName was already specified.
					if(strlen($this->v_actor)>0)
						$this->v_actor = $pcdata.' '.$this->v_actor;
					else
						$this->v_actor = $pcdata;
				}
			}
			else if(strcmp($name, 'LastName')===0)
			{
				if(strlen($pcdata)>0)
				{
					// in case LastName was already specified.
					if(strlen($this->v_actor)>0)
						$this->v_actor .= ' '.$pcdata;
					else
						$this->v_actor = $pcdata;
				}
			}
		}
		else if(strcmp($name, 'Overview')===0)
		{
			if(strlen($pcdata)>0)
			{
				$this->addAttribute('MOVIE_PLOT', NULL, $pcdata);
			}
		}
	}
	
	function end_element($name)
	{
		if(strcmp($name, 'Locks')===0)
		{
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'DVD')===0)
		{
			$this->endItem();
		}
		else if(strcmp($name, 'Genres')===0)
		{
			if(is_array($this->v_category_r))
			{
                $this->addAttribute('MOVIEGENRE', NULL, $this->v_category_r);
			}
			$this->v_category_r = NULL;
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'Regions')===0)
		{
			if(is_array($this->v_region_r))
			{
				$this->addAttribute('DVD_REGION', NULL, $this->v_region_r);
			}
			$this->v_region_r = NULL;
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'Format')===0)
		{
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'Features')===0)
		{
			if(is_array($this->v_extras))
			{
				$this->addAttribute('DVD_EXTRAS', NULL, implode("\n", $this->v_extras));
			}
			$this->v_extras = NULL;
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'Studios')===0)
		{
			if(is_array($this->v_studio_r))
			{
				$this->addAttribute('STUDIO', NULL, $this->v_studio_r);
			}
			$this->v_studio_r = NULL;
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'AudioFormat')===0)
		{
			if(strcmp($this->v_element_name, 'Audio/AudioFormat')===0)
			{
				$this->v_element_name = 'Audio';
				
				if(is_array($this->v_audio) && strlen($this->v_audio['language'])>0)
				{
					if($this->v_audio['language'] == 'DIR_COMMENT')
					{
						$this->v_audio_r[] = $this->v_audio['language'];
					}
					else if($this->v_audio['language'] == 'ENGLISH')
					{
						if($this->v_audio['type'] == 'DD')
						{
							if(is_numeric($this->v_audio['channels']) && $this->v_audio['channels'] == '3.0')
								$this->v_audio_r[] = $this->v_audio['language'].'_SR';
							if(is_numeric($this->v_audio['channels']) && $this->v_audio['channels'] > 1)
								$this->v_audio_r[] = $this->v_audio['language'].'_'.$this->v_audio['channels'];
							else
								$this->v_audio_r[] = $this->v_audio['language'];
						}
						else if($this->v_audio['type'] == 'DTS')
						{
							if(is_numeric($this->v_audio['channels']) && $this->v_audio['channels'] >= '6.1')
								$this->v_audio_r[] = $this->v_audio['language'].'_'.$this->v_audio['channels'].'_DTS';
							else
								$this->v_audio_r[] = $this->v_audio['language'].'_DTS';
						}
						else
						{
							$this->v_audio_r[] = $this->v_audio['language'];
						}
					}
					else
					{
						$this->v_audio_r[] = $this->v_audio['language'];
					}
				}
				$this->v_audio = NULL;
			}
		}
		else if(strcmp($name, 'Audio')===0)
		{
			if(is_array($this->v_audio_r))
			{
				$this->addAttribute('AUDIO_LANG', NULL, $this->v_audio_r); // let the handler sort out the array
			}
			$this->v_audio_r = NULL;
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'Subtitles')===0)
		{
			if(is_array($this->v_subtitle_r))
			{
				$this->addAttribute('SUBTITLES', NULL, $this->v_subtitle_r); // let the handler sort out the array
			}
			$this->v_subtitle_r = NULL;
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'Directors')===0)
		{
			if(is_array($this->v_director_r))
			{
				$this->addAttribute('DIRECTOR', NULL, $this->v_director_r);
			}
			$this->v_director_r = NULL;
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'Director')===0)
		{
			if(strcmp($this->v_element_name, 'Directors/Director')===0)
			{
				$this->v_element_name = 'Directors';
			
				if(strlen($this->v_director)>0)
					$this->v_director_r[] = $this->v_director;
					
				$this->v_director = NULL;
			}
		}
		else if(strcmp($name, 'Actors')===0)
		{
			if(is_array($this->v_actor_r))
			{
				$this->addAttribute('ACTORS', NULL, $this->v_actor_r);
			}
			$this->v_actor_r = NULL;
			$this->v_element_name = NULL;
		}
		else if(strcmp($name, 'Actor')===0)
		{
			if(strcmp($this->v_element_name, 'Actors/Actor')===0)
			{
				$this->v_element_name = 'Actors';
			
				if(strlen($this->v_actor)>0)
					$this->v_actor_r[] = $this->v_actor;
				
				$this->v_actor = NULL;
			}
		}
	}
}
?>