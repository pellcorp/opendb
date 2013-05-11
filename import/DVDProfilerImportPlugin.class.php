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

include_once("./lib/XMLImportPlugin.class.php");
include_once("./lib/import.php");

class DVDProfilerImportPlugin extends XMLImportPlugin
{
	var $_image_prefix = "http://www.invelos.com/mpimages/";
	
	var $v_audio = NULL;
	var $v_extras = NULL;
	
	function DVDProfilerImportPlugin() {
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
	
	function start_element($xpath, $name, $attribs, $pcdata)
	{
		if(isXpathMatch($xpath, '/Collection/DVD'))
		{
			$this->startItem('DVD');
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/ID'))
		{
			$this->addAttribute('IMAGEURL', NULL, $this->__getImageUrl($pcdata."f.jpg"));
			$this->addAttribute('IMAGEURL', NULL, $this->__getImageUrl($pcdata."b.jpg"));
			$this->addAttribute('FIMAGEURL', NULL, $this->__getImageUrl($pcdata."f.jpg"));
			$this->addAttribute('BIMAGEURL', NULL, $this->__getImageUrl($pcdata."b.jpg"));
		}
  		else if(isXpathMatch($xpath, '/Collection/DVD/Title'))
		{
			$this->setTitle($pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/UPC'))
		{
			$this->addAttribute('UPC_ID', NULL, $pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Genres/Genre'))
		{
			$this->addAttribute('MOVIEGENRE', NULL, $this->__getMappedGenre($pcdata));
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/CollectionType'))
		{
			$this->startItemInstance();
			if(strcmp($pcdata, 'WishList') === 0) {
				$this->setInstanceStatusType('W');
			} else if(strcmp($pcdata, 'Ordered') === 0) {
				$this->setInstanceStatusType('O');
			} else { //if(strcmp($pcdata, 'Owned') === 0)
				$this->setInstanceStatusType('A');
			}
			$this->endItemInstance();
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Rating'))
		{
			$this->addAttribute('AGE_RATING', NULL, $this->__getMappedAgeRating($pcdata));
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/ProductionYear'))
		{
			$this->addAttribute('YEAR', NULL, $pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Released'))
		{
			$this->addAttribute('DVD_REL_DT', NULL,	$this->__getFormattedDate($pcdata));
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/RunningTime'))
		{
			$this->addAttribute('RUN_TIME', NULL, $pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Regions/Region'))
		{
			$this->addAttribute('DVD_REGION', NULL, $pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Format/FormatVideoStandard'))
		{
			$this->addAttribute('VID_FORMAT', NULL, $pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Format/FormatAspectRatio'))
		{
			$this->addAttribute('RATIO', NULL, $pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Format/Format16X9') && strcmp($pcdata, 'True')===0)
		{
			$this->addAttribute('ANAMORPHIC', NULL, 'Y');
		}
		/*else if(isXpathMatch($xpath, '/Collection/DVD/Format/FormatDualSided'))
		else if(isXpathMatch($xpath, '/Collection/DVD/Format/FormatDualLayered'))
		else if(isXpathMatch($xpath, '/Collection/DVD/Format/FormatFlipper'))
		else if(isXpathMatch($xpath, '/Collection/DVD/Format/FormatLetterBox'))
		else if(isXpathMatch($xpath, '/Collection/DVD/Format/FormatPanAndScan'))
		else if(isXpathMatch($xpath, '/Collection/DVD/Format/FormatFullFrame'))
		*/
		else if(isXpathStartsWith($xpath, '/Collection/DVD/Features/Feature') && strcmp($pcdata, 'True')===0)
		{
			$this->v_extras[] = $name;
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Studios/Studio'))
		{
			$this->addAttribute('STUDIO', NULL, $pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Subtitles/Subtitle'))
		{
			$this->addAttribute('SUBTITLES', NULL, $pcdata); 
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Credits/Credit'))
		{
			if($attribs['CreditType'] == 'Direction' && $attribs['CreditSubtype'] == 'Director') {
				$this->addAttribute('DIRECTOR', NULL, $this->__getFormattedName($attribs));
			} 
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Actors/Actor'))
		{
			$this->addAttribute('ACTORS', NULL, $this->__getFormattedName($attribs));
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Overview'))
		{
			$this->addAttribute('MOVIE_PLOT', NULL, $pcdata);
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Audio/AudioTrack/AudioContent'))
		{
			$this->v_audio['language'] = $pcdata;
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Audio/AudioTrack/AudioFormat'))
		{
			$this->v_audio['format'] = $pcdata;
		}
	}
	
	function end_element($xpath, $name)
	{
		if(isXpathMatch($xpath, '/Collection/DVD'))
		{
			$this->endItem();
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Features'))
		{
			$this->addAttribute('DVD_EXTRAS', NULL, $this->__getMappedFeatures($this->v_extras));
			$this->v_extras = NULL;
		}
		else if(isXpathMatch($xpath, '/Collection/DVD/Audio/AudioTrack'))
		{
			if(is_array($this->v_audio)) {
				if($this->v_audio['language'] == 'Commentary') {
					$this->addAttribute('AUDIO_XTRA', NULL, 'DIR_COMMENT'); 
				} else if($this->v_audio['language'] == 'English') {
					$this->addAttribute('AUDIO_LANG', NULL, $this->__getMappedAudioLang($this->v_audio['format'])); 
				} else {
					$this->addAttribute('AUDIO_LANG', NULL, $this->__getMappedAudioLang($this->v_audio['language'].' '.$this->v_audio['format'])); 
				}
				$this->v_audio = NULL;
			}
		}
	}
	
	function __getMappedAudioLang($lang) {
		$audioLangMap = array(
			'Dolby Digital Stereo'=>'ENGLISH',
			'Dolby Digital 5.1'=>'ENGLISH_5.1',
			'Dolby Digital Surround EX'=>'ENGLISH_SR',
			'DTS ES (Discrete)'=>'ENGLISH_DTS',
			'DTS ES (Matrixed)'=>'ENGLISH_DTS',
			'Dolby Digital Surround'=>'ENGLISH_SR',
		);
		
		if(is_array($audioLangMap))
		{
			if(strlen($audioLangMap[$lang])>0)
				$lang = $audioLangMap[$lang];
		}
		
		return $lang;
	}
	
	function __getMappedFeatures($feature_r) {
		$featureMap = array(
				'FeatureSceneAccess'=>'Scene Access',
				'FeatureCommentary'=>'Commentary',
				'FeatureTrailer'=>'Trailer(s)',
				'FeatureDeletedScenes'=>'Deleted Scenes',
				'FeatureMakingOf'=>'Featurette',
				'FeatureProductionNotes'=>'Prod. Notes/Bios',
				'FeatureGame'=>'Interactive Game',
				'FeatureDVDROMContent'=>'DVD-ROM Content',
				'FeatureMultiAngle'=>'Multi-angle',
				'FeatureMusicVideos'=>'Music Video(s)',
				'FeatureClosedCaptioned'=>'Closed Captioned',
				'FeatureTHXCertified'=>'THX Certified',
				'FeatureInterviews'=>'Interviews',
				'FeatureStoryboardComparisons'=>'Story Boards',
				'FeatureOuttakes'=>'Outtakes'
				);
		
		if(is_array($feature_r)) {
			reset($feature_r);
			
			$mapped_feature_r = array();
			while(list(,$feature) = each($feature_r)) {
				$mapped_feature_r[] = ifempty($featureMap[$feature], $feature);
			}
			
			return implode("\n", $mapped_feature_r);
		}
		
		return NULL;
	}
	
	/**
	 * The genre's supported by DVD Profiler are as follows:
	 * 	Accessories, Action, Adult, Adventure, Animation, Anime, Classic, Comedy, Documentary
	 * 	Drama, Family, Fantasy, Foreign, Horror, Music, Musical, Romance, Science-Fiction
	 * 	Special Interest, Sports, Suspence/Thriller, Television, War, Western
	*/
	function __getMappedGenre($genre) {
		if($genre == 'Science-Fiction') {
			return 'ScienceFiction';
		} else if($genre == 'Suspence/Thriller') {
			return array('Suspense', 'Thriller');
		} else if($pcdata == 'Special Interest') {
			return 'Other';
		} else {
			return $genre;
		}
	}
	
	function __getMappedAgeRating($rating) {
		$ageCertMap = 
				array(
					'PG-13'=>'PG',
					'R'=>'MA',
					'NC-17'=>'MA',
					'X'=>'R');
		
		if(is_array($ageCertMap))
		{
			if(strlen($ageCertMap[$rating])>0)
				$rating = $ageCertMap[$rating];
		}
		
		return $rating;
	}
	
	function __getFormattedName($attribs) {
		$name = $attribs['FirstName']." ";
		if(strlen($attribs['MiddleName'])>0) {
			$name .= $attribs['MiddleName']." ";
		}
		$name .= $attribs['LastName'];
		
		return $name;
	}
	
	/**
	 */
	function __getFormattedDate($pcdata) {
		if(strlen($pcdata)>0) {
			// Date Format YYYY-MM-DD
			list($year, $month, $day) = sscanf($pcdata,"%d-%d-%d");
	
			return str_pad($year,4,'0', STR_PAD_LEFT) // Format: 'YYYYMMDDHH24MISS'
						.str_pad($month,2,'0', STR_PAD_LEFT)
						.str_pad($day,2,'0', STR_PAD_LEFT)
						.'00' // hours
						.'00' // minutes
						.'00';
		} else {
			return NULL;
		}
	}
	
	function __getImageUrl($image) {
		return $this->_image_prefix.substr($image,0,2)."/".$image;
	}
}
?>