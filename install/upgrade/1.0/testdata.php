<?php

chdir('../../../');

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");

include_once("./functions/item_attribute.php");

function insert_item_image_attrib_and_cache($item_attrib_r, $count)
{
	$filename = "image$count.jpg";
	
	echo "<br>Inserting item...".$item_attrib_r['id']." - $filename";
	
	insert_item_attribute(
		$item_attrib_r['id'], 
		NULL, 
		$item_attrib_r['s_attribute_type'], 
		$item_attrib_r['order_no'], 
		"1", 
		NULL, 
		$filename);

	$url = "file://opendb/upload/".
				$item_attrib_r['id']."/".
				"0/".
				$item_attrib_r['s_attribute_type']."/".
				$item_attrib_r['order_no']."/".
				"1/".
				$filename;
	
	$cacheFile = "item".$item_attrib_r['id']."_0";
	$cacheFileThumb = "T_item".$item_attrib_r['id']."_0";
	
	db_query("INSERT INTO file_cache(cache_type, cache_date, expire_date, url, upload_file_ind, cache_file, cache_file_thumb)
			VALUES ('ITEM', NOW(), NULL, '$url', 'Y', '$cacheFile', '$cacheFileThumb')");
	
	copy("./install/upgrade/1.0/image.jpg", "./itemcache/".$cacheFile);
	copy("./install/upgrade/1.0/image.jpg", "./itemcache/".$cacheFileThumb);
}

function insert_unique_imageurl()
{
	$query = "SELECT i.id, siat.s_attribute_type, siat.order_no 
		FROM item i, s_item_attribute_type siat
		WHERE i.s_item_type = siat.s_item_type AND
		siat.s_attribute_type = 'IMAGEURL' AND 
		i.id LIMIT 100";

	$results = db_query($query);
	if($results)
	{
		$count = 0;
		while($item_attrib_r = db_fetch_assoc($results))
		{	
			insert_item_image_attrib_and_cache($item_attrib_r, $count);
			$count++;
		}
		db_free_result($results);
	}
}

function insert_duplicate_imageurl()
{
/*	$query = "SELECT i.id, siat.s_attribute_type, siat.order_no 
		FROM item i, s_item_attribute_type siat
		WHERE i.s_item_type = siat.s_item_type AND
		siat.s_attribute_type = 'IMAGEURL' AND 
		i.id LIMIT 101, 201";
*/
	switch ($_opendb_dbtype) {
		case 'mysql':
			$query = "SELECT i.id, siat.s_attribute_type, siat.order_no 
					FROM item i, s_item_attribute_type siat
					WHERE i.s_item_type = siat.s_item_type AND
					siat.s_attribute_type = 'IMAGEURL' AND 
					i.id LIMIT 101, 201";
			break ;
		case 'postgresql':
			$query = "SELECT i.id, siat.s_attribute_type, siat.order_no 
					FROM item i, s_item_attribute_type siat
					WHERE i.s_item_type = siat.s_item_type AND
					siat.s_attribute_type = 'IMAGEURL' AND 
					i.id LIMIT 201 OFFSET 101";
			break;
	}
	

	$results = db_query($query);
	if($results)
	{
		while($item_attrib_r = db_fetch_assoc($results))
		{	
			insert_item_image_attrib_and_cache($item_attrib_r, "XXX");
		}
		db_free_result($results);
	}
}

db_query("DELETE FROM item_attribute WHERE s_attribute_type = 'IMAGEURL'");
db_query("DELETE FROM file_cache");

insert_unique_imageurl();
insert_duplicate_imageurl();

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>