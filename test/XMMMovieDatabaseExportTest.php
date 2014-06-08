<?php
/* 	
 	Open Media Collectors Database
	Copyright (C) 2001,2013 by Jason Pell

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

chdir(dirname(dirname(__FILE__)));

include_once("./lib/export/XMMMovieDatabasePlugin.class.php");

class XMMMovieDatabaseExportTest extends PHPUnit_Framework_TestCase
{
	function testXMLParse() {
		$plugin = new XMMMovieDatabasePlugin(FALSE);
		
		$plugin->file_header('Family Man, The');
		
		$plugin->start_item(1, 'DVD', 'Family Man, The');
		
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

		$plugin->end_item();
		
		$xml = $plugin->file_footer();
		
		$this->assertEquals(
"<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<XMM_Movie_Database>
	<Item>
		<MovieID>1</MovieID>
		<Title>Family Man, The</Title>
		<Media>
			<Medium>DVD</Medium>
		</Media>
		<Location>Default</Location>
		<Year>2002</Year>
		<Length>93</Length>
		<Plot>This is a test again
		thanks again
		stuff all</Plot>
		<PurchaseDate>20020912TT00:00:00</PurchaseDate>
		<UPC>2132133123213213</UPC>
		<Directors>
			<Director>Jason Pell</Director>
		</Directors>
		<MPAA>R</MPAA>
		<Genres>
			<Genre>Action</Genre>
			<Genre>Adventure</Genre>
			<Genre>Comedy</Genre>
		</Genres>
		<Country>Australia</Country>
	</Item>
</XMM_Movie_Database>
",
$xml);
		
	}
}
?>

