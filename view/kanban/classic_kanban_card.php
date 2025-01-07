<?php
/* Copyright (C) 2022-2023 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *   	\file       view/kanban/kanban_card.php
 *		\ingroup    digikanban
 *		\brief      Page to create/edit/view kanban
 */

// Load digikanban environment
if (file_exists('../digikanban.main.inc.php')) {
	require_once __DIR__ . '/../digikanban.main.inc.php';
} elseif (file_exists('../../digikanban.main.inc.php')) {
	require_once __DIR__ . '/../../digikanban.main.inc.php';
} else {
	die('Include of digikanban main fails');
}

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/project.lib.php';

// Load module libraries
require_once __DIR__ . '/../../class/kanban.class.php';
require_once __DIR__ . '/../../lib/digikanban_kanban.lib.php';
require_once __DIR__ . '/../../lib/functions.lib.php';


// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user, $langs;

// Load translation files required by the page
saturne_load_langs(['projects', 'thirdparty']);

// Get parameters
$id                  = GETPOST('id', 'int');
$ref                 = GETPOST('ref', 'alpha');
$action              = GETPOST('action', 'aZ09');
$subaction           = GETPOST('subaction', 'aZ09');
$confirm             = GETPOST('confirm', 'alpha');
$cancel              = GETPOST('cancel', 'aZ09');
$contextpage         = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'kanbancard'; // To manage different context of search
$backtopage          = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$type 		   	     = GETPOST('type', 'alpha');

// Initialize objects
// Technical objets
$object         = new Kanban($db);
$extrafields    = new ExtraFields($db);
$categorie 	    = new Categorie($db);

// View objects
$form = new Form($db);

$elementArray = get_kanban_linkable_objects();
$hookmanager->initHooks(array('kanbancard', 'globalcard'));

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$searchAll = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) $search[$key] = GETPOST('search_'.$key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

$permissiontoread   = $user->rights->digikanban->kanban->read;
$permissiontoadd    = $user->rights->digikanban->kanban->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->digikanban->kanban->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);

// Security check - Protection if external user
saturne_check_access($permissiontoread, $object);

/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/digikanban/view/kanban/kanban_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
			else $backtopage = dol_buildpath('/digikanban/view/kanban/kanban_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
		}
	}

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';
}

/*
 * View
 */

$title    = $langs->trans(ucfirst($object->element));
$help_url = 'FR:Module_digikanban';



saturne_header(1,'', $title, $help_url, '', 0, 0);

print '<div class="clearboth"></div>';

$possibleKanbans = [
	'ticket' => [
		"statusField" => "fk_statut",
		"useClassStatus" => 1,
		"className" => "Ticket",
	],
	'project' => [
		"statusField" => "fk_statut",
		"useClassStatus" => 1,
		"className" => "Project",
	],
	"opportunity" => [
		"statusField" => "fk_opp_status",
		"useClassStatus" => 0,
		"statusDictionary" => "c_lead_status",
		"className" => "Project",
	],
];

$object->object_type = $type;
$kanbanMetadata = $possibleKanbans[$type];
$kanbanObject = new $kanbanMetadata["className"]($db);

if ($kanbanMetadata['useClassStatus']) {
	$kanbanObject->useClassStatus = $kanbanMetadata['useClassStatus'];
	$reflection = new ReflectionClass(get_class($kanbanObject));
	$constants = $reflection->getConstants();

	$columnList = array_filter($constants, function($key) {
		return strpos($key, 'STATUS_') === 0;
	}, ARRAY_FILTER_USE_KEY);
} else if (dol_strlen($kanbanMetadata['statusDictionary']) > 0) {
	$columnList = getDictionary($kanbanMetadata['statusDictionary']);
}

if (!empty($elementArray)) {
	foreach ($elementArray as $linkableElementType => $linkableElement) {
		if ($kanbanObject->element == $linkableElement['category_name']) {
			$objectLinkedMetadata = $linkableElement;
			$objectLinkedType = $linkableElementType;
		}
	}
}

$objectFilter = [];
$columns = [];
if (is_array($columnList) && !empty($columnList)) {
	foreach ($columnList as $column) {

//		$objectsInStatus = $kanbanObject->fetchAll('', '', 0,0, ['status' => $column]);

		if ($kanbanMetadata['useClassStatus']) {
			$objectsInColumn = saturne_fetch_all_object_type($kanbanObject->element, '', '', 0, 0, ['t.' . $kanbanMetadata['statusField'] => $column]);
			$columnLabel = $kanbanObject->labelStatus[$column];

		} else if ($kanbanMetadata['statusDictionary']) {
			$columnLabel = $column->label;
			$objectsInColumn = saturne_fetch_all_object_type($kanbanObject->element, '', '', 0, 0, ['t.' . $kanbanMetadata['statusField'] => $column->rowid]);
		}

		if (is_array($objectsInColumn) && !empty($objectsInColumn)) {
			foreach ($objectsInColumn as $objectInStatus) {
				$objectFilter[] = $objectInStatus->id;
			}
		}
		$columns[] = [
			'label' => $columnLabel,
			'category_id' => $column->rowid,
			'objects' => $objectsInColumn
		];
	}
}

require_once DOL_DOCUMENT_ROOT . '/' . $objectLinkedMetadata['class_path'];

print '<input hidden class="main-category-id" id="main_category_id" value="' . $categorie->id . '">';

$publicView = 1;
$disableAddColumn = 1;
$disableActions = 1;
$object->image_path = '';
$object->track_id = '';

print '<input hidden class="disable-kanban-actions" value="1">';
include_once __DIR__ . '/../../core/tpl/kanban_view.tpl.php';

print dol_get_fiche_end();

print '<div class="fichecenter"><div class="fichehalfright">';

$maxEvent = 10;

$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-bars imgforviewmode', dol_buildpath('/saturne/view/saturne_agenda.php', 1) . '?id=' . $object->id . '&module_name=digikanban&object_type=' . $object->element);

print '</div></div>';

// End of page
llxFooter();
$db->close();
