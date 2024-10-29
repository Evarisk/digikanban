<?php

// Load digikanban environment
if (file_exists('../digikanban.main.inc.php')) {
	require_once __DIR__ . '/../digikanban.main.inc.php';
} elseif (file_exists('../../digikanban.main.inc.php')) {
	require_once __DIR__ . '/../../digikanban.main.inc.php';
} else {
	die('Include of digikanban main fails');
}

global $db, $user;

require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
require_once __DIR__ . '/../lib/digikanban_kanban.lib.php';

$categorie = new Categorie($db);



$elementArray = get_kanban_linkable_objects();


$category_id = GETPOST('category_id');
$object_id = GETPOST('object_id');
$object_type = GETPOST('object_type');
$category_name = GETPOST('category_name');

$objectLinkedClassPath = $elementArray[$object_type]['class_path'];
require_once DOL_DOCUMENT_ROOT . '/' . $objectLinkedClassPath;
$objectLinked = $elementArray[$object_type]['className'];

$object = new $objectLinked($db);

$categorie->fetch($category_id);
$linkedCategories = $categorie->get_filles();


$action = GETPOST('action');
$categorie = new Categorie($db);

if ($action == 'rename_column') {
	$categorie->fetch($category_id);
	$categorie->label = $category_name;

	$test = $categorie->update($user);
}

if ($action == 'add_object_to_column') {
	$object->fetch($object_id);
	$categorie->fetch($category_id);

	$result = $categorie->add_type($object, $categorie->type);
	if ($result < 0) {
		echo 'coucou';
	} else {
		echo $object->getKanbanView();
	}
}

if ($action == 'move_object') {
	// get action payload
	$payload = json_decode(file_get_contents('php://input'), true);
	if (is_array($payload) && !empty($payload)) {
		$order = $payload['order'];
		if (is_array($order) && !empty($order)) {
			foreach ($order as $columnDetails) {
				$column_id = $columnDetails['columnId'];
				$objects = $columnDetails['cards'];
				$categorie->fetch($column_id);
				$objectsInColumn = $categorie->getObjectsInCateg($object_type);
				if (is_array($objectsInColumn) && !empty($objectsInColumn)) {
					foreach ($objectsInColumn as $linkedObject) {
						$object->fetch($linkedObject->id);
						$test = $categorie->del_type($object, $object_type);

					}

				}
				if (is_array($objects) && !empty($objects)) {
					foreach ($objects as $object_id) {
						$object->fetch($object_id);
						$categorie->add_type($object, $object_type);
					}
				}
			}
		}
	}
}
