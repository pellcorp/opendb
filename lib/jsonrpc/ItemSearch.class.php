<?php

include_once("./lib/TitleMask.class.php");

class ItemSearch {
	public function titleSearch($params_r) {
		$titleMaskCfg = new TitleMask ( 'item_listing' );
		
		$results = fetch_item_listing_rs ( $params_r, array (), 'title', 'ASC' );
		while ( $item_r = db_fetch_assoc ( $results ) ) {
			$item_r ['title'] = $titleMaskCfg->expand_item_title ( $item_r );
			$jsonResults [] = $item_r;
		}
		db_free_result($results);
		
		return $jsonResults;
	}
}