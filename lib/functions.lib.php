<?php

function getDictionary($tableName) {
	global $db;
	$dictionary = [];
	$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "$tableName";
	$resql = $db->query($sql);
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$dictionary[] = $obj;
		}
	}
	return $dictionary;
}
