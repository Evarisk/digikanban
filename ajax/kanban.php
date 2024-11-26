<?php

// Load digikanban environment
if (file_exists('../digikanban.main.inc.php')) {
	require_once __DIR__ . '/../digikanban.main.inc.php';
} elseif (file_exists('../../digikanban.main.inc.php')) {
	require_once __DIR__ . '/../../digikanban.main.inc.php';
} else {
	die('Include of digikanban main fails');
}

global $db, $user, $langs;

require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/project.lib.php';
require_once __DIR__ . '/../lib/digikanban_kanban.lib.php';
require_once __DIR__ . '/../class/kanban.class.php';


$kanban    = new Kanban($db);
$categorie = new Categorie($db);
$form      = new Form($db);

$elementArray  = get_kanban_linkable_objects();
$category_id   = GETPOST('category_id');
$object_id     = GETPOST('object_id');
$object_type   = GETPOST('object_type');
$category_name = GETPOST('category_name');

$objectLinkedClassPath = $elementArray[$object_type]['class_path'];

require_once DOL_DOCUMENT_ROOT . '/' . $objectLinkedClassPath;

$objectLinked = $elementArray[$object_type]['className'];

$object = new $objectLinked($db);

$categorie->fetch($category_id);
$linkedCategories = $categorie->get_filles();
$elementArray	 = get_kanban_linkable_objects();

$action    = GETPOST('action');
$categorie = new Categorie($db);

if ($action == 'rename_column') {
	$categorie->fetch($category_id);
	$categorie->label = $category_name;

	$categorie->update($user);
}

if ($action == 'add_column') {
	$data = json_decode(file_get_contents("php://input"), true);

	$category_name     = $data['column_name'];
	$postName          = $data['post_name'];
	$objectLinkedLangs = $data['object_linked_langs'];
	$objectArray       = json_decode($data['object_array'], true);

	$categorie->fetch($category_id);
	$sameCategories = $categorie->get_filles();
	$sameCategoriesCounter = 0;
	if (is_array($sameCategories) && !empty($sameCategories)) {
		foreach($sameCategories as $sameCategory) {
			if (strstr($sameCategory->label, $category_name)) {
				$sameCategoriesCounter += 1;
			}
		}
	}

	if ($sameCategoriesCounter > 0) {
		$category_name = $category_name . ' (' . $sameCategoriesCounter . ')';
	}

	$categorie = new Categorie($db);
	$categorie->label = $category_name;
	$categorie->type = $object_type;
	$categorie->fk_parent = $category_id;

	$result = $categorie->create($user);

	if ($result > 0) {
		$objectSelector = $form->selectArray($postName . $result, $objectArray, GETPOST($postName), $langs->trans('Select') . ' ' . strtolower($langs->trans($objectLinkedLangs)), 0, 0, '', 0, 0, dol_strlen(GETPOST('fromtype')) > 0 && GETPOST('fromtype') != $objectLinkedMetadata['link_name'], '', 'maxwidth200 widthcentpercentminusxx kanban-select-option');

		echo json_encode([
			'category_id' => $result,
			'object_selector' => $objectSelector
		]);
	}
}

if ($action == 'add_object_to_column') {
	$object->fetch($object_id);
	$categorie->fetch($category_id);
	$categoryType = $categorie::$MAP_ID_TO_CODE[$categorie->type];

	$result = $categorie->add_type($object, $categoryType);
	if ($result < 0) {
		echo 'Error';
	} else {
		print $kanban->getObjectKanbanView($object, $elementArray[$object_type]);
	}
}

if ($action == 'move_object') {
	$payload = json_decode(file_get_contents('php://input'), true);

	if (is_array($payload) && !empty($payload)) {
		$order = $payload['order'];
		if (is_array($order) && !empty($order)) {
			foreach ($order as $columnDetails) {
				$column_id = $columnDetails['columnId'];
				$objects   = $columnDetails['cards'];
				$categorie->fetch($column_id);
				$categoryType = $categorie::$MAP_ID_TO_CODE[$categorie->type];

				$objectsInColumn = $categorie->getObjectsInCateg($categoryType);
				if (is_array($objectsInColumn) && !empty($objectsInColumn)) {
					foreach ($objectsInColumn as $linkedObject) {
						$categorie->del_type($linkedObject, $categoryType);
					}

				}
				if (is_array($objects) && !empty($objects)) {
					foreach ($objects as $object_id) {
						$object->fetch($object_id);
						$categorie->add_type($object, $categoryType);
					}
				}
			}
		}
	}
}

if ($action == 'delete_column') {
	$categorie->fetch($category_id);
	$result = $categorie->delete($user);
}
