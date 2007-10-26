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

/**
 */
function ImageHexColorAllocate(&$img, $HexColorString)
{
	$start = 0 ;
	if($HexColorString{0} == '#')
		$start = 1;
	
	$R = hexdec(substr($HexColorString, $start, 2));
	$G = hexdec(substr($HexColorString, $start+2, 2));
	$B = hexdec(substr($HexColorString, $start+4, 2));
	return ImageColorAllocate($img, $R, $G, $B);
}

/**
* 	Note that this file/feature requires GD libraries installed on the php server
* 
* @param $sortorder 'asc' or 'desc'
* @param $chartType 'barchart' or 'piechart'
* @param $imgType 'gif', 'jpg', 'png'
* @param $chartOptions Array of options for chart, options include:
* 			striped=>[TRUE|FALSE]
* 			12oclock=>[TRUE|FALSE]
* 			threshold=>a number
* 
* @param $graphCfg = array(
					'size'=>$HTTP_VARS['size'],
					'font-size'=>$HTTP_VARS['font-size'],
					'background'=>$HTTP_VARS['background'],
					'text-color'=>$HTTP_VARS['text-color'],
					'caption-color'=>$HTTP_VARS['caption-color'],
					'light-color'=>$HTTP_VARS['light-color'],
					'dark-color'=>$HTTP_VARS['dark-color'],
					'light-border-color'=>$HTTP_VARS['light-border-color'],
					'dark_border-color'=>$HTTP_VARS['dark_border-color'],
					'background-color'=>$HTTP_VARS['background-color']);
*/
function build_and_send_graph($itemCount, $data, $sortorder, $chartType, $chartOptions, $graphCfg, $imgType)
{
	// size of pie-chart (not counting text borders)
	$imgsize = $graphCfg['size'];
	
	$fontsize = $graphCfg['font-size'];
	$fontheight = imagefontheight($fontsize);

	// Create a new image
	$side_margin = round($imgsize * 2/3);

	$top_margin      = $fontheight+1;
	$xsize = $imgsize + 2*$side_margin;
	$ysize = $imgsize + 2*$top_margin;

	$im = ImageCreate($xsize, $ysize);

	$bgcolor 		= ImageHexColorAllocate($im, $graphCfg['background-color']);
	$text_color 	= ImageHexColorAllocate($im, $graphCfg['text-color']);
	$captions_color = ImageHexColorAllocate($im, $graphCfg['caption-color']);
	$lt_color 		= ImageHexColorAllocate($im, $graphCfg['light-color']);
	$dk_color 		= ImageHexColorAllocate($im, $graphCfg['dark-color']);
	$lt_border 		= ImageHexColorAllocate($im, $graphCfg['light-border-color']);
	$dk_border 		= ImageHexColorAllocate($im, $graphCfg['dark-border-color']);

	// Do this if you want your background image to show through:
	if($graphCfg['background'] == 'transparent')
	{
		imagecolortransparent($im, $bgcolor);
	}

	// color background
	ImageFilledRectangle($im, 0, 0, $xsize, $ysize, $bgcolor);

	if(is_array($data))
	{
		// process data
		$dataCount = @count($data);
	
		if($dataCount > 0 && !empty($sortorder))
		{
			if($sortorder == 'asc')	
				asort($data);
			else
				arsort($data);
		}
		
		$TotalArrayValues = @array_sum($data);
		if($TotalArrayValues > 0 && $itemCount > 0)
		{	
			$maxdata = 0;
			
			foreach ($data as $key => $value)
			{
				$value = number_format(@($value / $itemCount) * 100, 1);
				$data[$key] = $value;
				
				if ($maxdata < $value)
				{
					$maxdata = $value + 1;
				}
			}
		}
		
		if($chartType == 'barchart')
		{
			if($TotalArrayValues>0)
			{
				$sidegap = 10;			// pixels.
				$topgap = $sidegap;
	
				// An iterative process to determine the best box width.
				// It will perform only one loop, unless you have so many items that
				// the box width is smaller than the font height.  Then it will
				// recompute with fewer items.
				$dataCount++;			// We undo this on the next line.
				do {
					$dataCount--;
					$boxwidth = round(($xsize - ($sidegap*2)) / ($dataCount + 1));
					$gapwidth = round($boxwidth / 10);
					if ($gapwidth < 1) $gapwidth = 1;
					$boxwidth = floor(($xsize - ($sidegap*2) - ($gapwidth * ($dataCount-1))) / $dataCount);
					$totalwidth = $boxwidth * $dataCount + $gapwidth * ($dataCount-1);
				} while ($dataCount>0 && $boxwidth < $fontheight + 2);
	
				// centre it:
				$sidegap = ($xsize - $totalwidth) / 2;
				
				$ix = 0;
				foreach ($data as $key => $value)
				{
					//$height = $data[$ix][1] * ($ysize - $topgap*2) / 100;
					$height = $value * ($ysize - $topgap*2) / $maxdata;
	
					$x1 = $sidegap + ($ix*$boxwidth) + ($ix*$gapwidth);
	
					// ImageLine($im, $x1, $ysize - $top_margin, $x1, $ysize - $top_margin - $height, $dk_border );
					ImageFilledRectangle($im,
						 $x1, $ysize - $topgap - $height,
						 $x1+$boxwidth, $ysize - $topgap,
						 $lt_color);
	
					// Highlight it from the top left, shadow on bottom and right.
					ImageLine($im, $x1, $ysize - $topgap - $height, $x1, $ysize - $topgap, $lt_border);
					ImageLine($im, $x1, $ysize - $topgap - $height, $x1+$boxwidth, $ysize - $topgap - $height, $lt_border);
					ImageLine($im, $x1+$boxwidth, $ysize - $topgap - $height, $x1+$boxwidth, $ysize - $topgap, $dk_border);
					ImageLine($im, $x1, $ysize - $topgap, $x1+$boxwidth, $ysize - $topgap, $dk_border);
	
					$text = $key." ".round($value)."%"; // show percent
					ImageStringUp($im, $fontsize, $x1 + $boxwidth/2 - $fontheight/2, $ysize - $topgap - 3, $text, $text_color);
					
					$ix++;
				}
			}
		}//if($chartType == 'piechart')
		else
		{
			// A pie chart.
			if(is_numeric($chartOptions['threshold']))// minimum wedge percent to omit from caption
				$threshold = $chartOptions['threshold'];
			else
				$threshold = 3;
	
			$radius = round($imgsize/2);
	
			$originx = $radius+$side_margin;
			$originy = $radius+$top_margin;
	
			// draw a circle
			ImageArc($im, $originx, $originy, $radius*2, $radius*2, 0, 360, $dk_border);
	
			// fill circle with color
			ImageFill($im, $originx, $originy, $lt_color);
	
			// GD-2 version of the above two calls.	 Damn PHP.
			// ImageFilledArc($im, $originx, $originy, $radius*2, $radius*2, 0, 360, $dk_border, IMG_ARC_PIE);
	
			if($chartOptions['12oclock'] !== FALSE)
			{
				// draw a wedge
				$last_angle = deg2rad(-90);
				// Draw line at 0 degrees if we have more than one item.
				if ($TotalArrayValues > 1)
				{
					ImageLine($im,
							$originx, $originy,
							$originx, $originy - $radius + 1,
							$dk_border );
				}
			}
			else
			{
				$last_angle = deg2rad(0);
				if ($TotalArrayValues > 1)
				{
					ImageLine($im,
							$originx, $originy,
							$originx + $radius - 1, $originy,
							$dk_border );
				}
			}
	
			if($chartOptions['striped'] !== FALSE)
			{
				// Draw every other pie wedge a different colour.
				$striped = TRUE;
			}
		
			$ix = 0;
			foreach ($data as $key => $value)
			{
				$angle = deg2rad(($value * 3.6))+$last_angle;
	
				$x2 = ($radius-1)*cos($angle) + $originx;
				$y2 = ($radius-1)*sin($angle) + $originy;
	
				if($ix != $TotalArrayValues-1) // don't draw wedge-line for 100%
				{
					ImageLine($im, $originx, $originy, $x2, $y2, $dk_border );
				}
				
				$mid_angle = ($angle-$last_angle)/2;
	
				if($value > $threshold) // caption if over $threshold
				{
					// Fill every other wedge with a different colour:
					if (($ix % 2) && $striped)
					{
						ImageFillToBorder($im,
									$radius*0.9*cos($mid_angle+$last_angle) + $originx,
									$radius*0.9*sin($mid_angle+$last_angle) + $originy,
									$dk_border,
									$dk_color);
					}
			
					$x1 = ($radius/2)*cos($mid_angle+$last_angle) + $originx;
					$y1 = ($radius/2)*sin($mid_angle+$last_angle) + $originy;
	
					$x2 = $radius*cos($mid_angle+$last_angle) + $originx;
					$y2 = $radius*sin($mid_angle+$last_angle) + $originy;
	
					ImageArc( $im, $x1, $y1, $imgsize/25, $imgsize/25, 0, 360, $captions_color);  // display caption
					ImageLine($im, $x1, $y1, $x2, $y2, $captions_color);
	
					$text = $key." ".round($value)."%"; // show percent
					if($x1 > $originx)
					{
						ImageLine($im, $x2, $y2, $x2+$side_margin, $y2, $captions_color); // caption on right side
						if ($y2 > $originy) // Bottom half
							ImageString($im, $fontsize, $x2, $y2, $text, $text_color);
						else
							// Should use font height here:
							ImageString($im, $fontsize, $x2, $y2-15, $text, $text_color);
					}
					else
					{
						ImageLine($im, $x2, $y2, $x2-$side_margin, $y2, $captions_color); // caption on left side
						if ($y2 > $originy) // Bottom half
							ImageString($im, $fontsize, $x2-$side_margin, $y2, $text, $text_color);
						else
						{
							// Should use font height here:
							ImageString($im, $fontsize, $x2-$side_margin, $y2 - 15, $text, $text_color);
						}
					}
				}
				$last_angle = $angle;
				
				$ix++;
			}
		}
	}
	
	header("Pragma: no-cache");
	header("Expires: 0");
				
	switch($imgType)
	{
		case 'jpeg':
		case 'jpg':
			Header("Content-Type: image/jpeg");
			ImageJPEG($im);
			break;

		case 'gif'://not all sites support GIF!
			Header("Content-Type: image/gif");
			ImageGIF($im);
			break;

		case 'png':
		default:
			Header("Content-Type: image/png");
			ImagePNG($im);// send image as PNG to browser
	}

	// destroy image when done
	ImageDestroy($im);
}
?>