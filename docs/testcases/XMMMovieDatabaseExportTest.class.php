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

require_once 'PHPUnit.php';

include_once("./export/XMMMovieDatabasePlugin.class.php");

class XMMMovieDatabaseExportTest extends PHPUnit_TestCase
{
	function XMMMovieDatabaseExportTest($name) {
		parent::PHPUnit_TestCase($name);
	}
	
	function testXMLParse() {
		$plugin = new XMMMovieDatabasePlugin();
		
		$startItem = $plugin->start_item(1, 'DVD', 'Family Man, The');
		
		/**
		 * 'YEAR'=>'Year', 
			'RUN_TIME'=>'Length',
	 		'MOVIE_PLOT'=>'Plot',
			'PUR_DATE'=>'Purchase',
			'UPC_ID'=>'UPC',
			'DIRECTOR'=>'Director',
			'AGE_RATING'=>'Rating',
		 */
		$plugin->item_attribute('YEAR', NULL, '2002');
		$plugin->item_attribute('RUN_TIME', NULL, '93');
		$plugin->item_attribute('MOVIE_PLOT', NULL, 'This is a test again
		thanks again
		stuff all');
		
		$plugin->item_attribute('PUR_DATE', NULL, '20020912000000');//YYYYMMDDHH24MISS
		$plugin->item_attribute('UPC_ID', NULL, '2132133123213213');
		$plugin->item_attribute('DIRECTOR', NULL, 'Jason Pell');
		$plugin->item_attribute('AGE_RATING', NULL, 'R');
		
		$plugin->item_attribute('MOVIEGENRE', NULL, 'Action');
		$plugin->item_attribute('MOVIEGENRE', NULL, 'Adventure');
		$plugin->item_attribute('MOVIEGENRE', NULL, 'Comedy');
		
		$plugin->item_attribute('ACTOR', NULL, 'Clair Pell');
		$plugin->item_attribute('ACTOR', NULL, 'Lucy Pell');
		$plugin->item_attribute('ACTOR', NULL, 'Thomas Pell');
		
		$plugin->item_attribute('IMAGEURLB', NULL, 'http://nowhere.com/image2.gif');
		$plugin->item_attribute('IMAGEURL', NULL, 'http://images.amazon.com/images/P/B00005JCCC.01.MZZZZZZZ.jpg');
		
		$plugin->item_attribute('DVD_REGION', NULL, '4');

		$xml = $plugin->file_header('Family Man, The')
		.$startItem
		.$plugin->end_item()
		.$plugin->file_footer();
		
		$this->assertEquals(
"<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<XMM_Movie_Database>
	<Movie>
	<MovieID>1</MovieID>
	<Title>Family Man, The</Title>
	<PersonalRating>5</PersonalRating>
	<Media>DVD-Rom</Media>
	<Year>2002</Year>
	<Length>93</Length>
	<Plot>This is a test again
	thanks again
	stuff all</Plot>
	<Purchase>12/09/2002</Purchase>
	<UPC>2132133123213213</UPC>
	<Director>Jason Pell</Director>
	<Rating>R</Rating>
	<Genre>Action,Adventure,Comedy</Genre>
	<Actors>
	<Actor>Clair Pell</Actor>
	<Actor>Lucy Pell</Actor>
	<Actor>Thomas Pell</Actor>
	</Actors>
	<Cover>itemThumb_846_86051.jpeg</Cover>
	<Country>Australia</Country>
	</Movie>
</XMM_Movie_Database>
",
$xml);
		
	}
}
?>