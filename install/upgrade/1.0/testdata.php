<?php

chdir('../../../');

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");

$item_attrib_rs = NULL;

$query = "SELECT * FROM item_attribute WHERE s_attribute_type = 'IMAGEURL' ORDER BY item_id LIMIT 0, 100";
$results = db_query($query);
if($results)
{
	while($item_attrib_r = db_fetch_assoc($results))
	{
		$item_attrib_rs[] = $item_attrib_r;
	}
	db_free_result($results);
}

if(is_array($item_attrib_rs))
{
	$count = 0;
	while(list(,$item_attrib_r) = each($item_attrib_rs))
	{
		$filename = "image$count.jpg";
		
		echo "<br>Updating item...".$item_attrib_r['item_id']." - $filename";
		
		
		$count++;
		
		db_query("UPDATE item_attribute 
				SET attribute_val = '$filename' WHERE
				item_id = ${item_attrib_r['item_id']} AND
				instance_no = ${item_attrib_r['instance_no']} AND
				s_attribute_type = 'IMAGEURL' AND 
				order_no = ${item_attrib_r['order_no']} AND
				attribute_no = ${item_attrib_r['attribute_no']}");
				
		$url = "file://opendb/upload/".
					$item_attrib_r['item_id']."/".
					$item_attrib_r['instance_no']."/".
					"IMAGEURL/".
					$item_attrib_r['order_no']."/".
					$item_attrib_r['attribute_no']."/".
					$filename;
		
		$cacheFile = "item".$item_attrib_r['item_id'].$item_attrib_r['instance'];
		$cacheFileThumb = "T_item".$item_attrib_r['item_id'].$item_attrib_r['instance'];
		
		db_query("INSERT INTO file_cache(cache_type, cache_date, expire_date, url, upload_file_ind, cache_file, cache_file_thumb)
				VALUES ('ITEM', NOW(), NULL, '$url', 'Y', '$cacheFile', '$cacheFileThumb')");
		
		copy("./upload/image.jpg", "./itemcache/".$cacheFile);
		copy("./upload/image.jpg", "./itemcache/".$cacheFileThumb);
	}
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>