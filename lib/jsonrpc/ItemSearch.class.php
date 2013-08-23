<?php

include_once("./lib/TitleMask.class.php");

class ItemSearch {
	public function titleSearch($params_r) {
		$titleMaskCfg = new TitleMask ( 'item_listing' );
		
		// if no limit provided, provide one!
		if (empty($params_r['limit'])) {
			$params_r['limit'] = 10;
		}
		
		$results = fetch_item_listing_rs ( $params_r, array (), 'title', 'ASC', 0, $params_r['limit']);
		while ( $item_r = db_fetch_assoc ( $results ) ) {
			$item_r ['title'] = $titleMaskCfg->expand_item_title ( $item_r );
			$jsonResults [] = $item_r;
		}
		db_free_result($results);
		
		return $jsonResults;
	}
}