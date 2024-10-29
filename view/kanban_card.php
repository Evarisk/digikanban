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

require_once __DIR__ . '/../class/kanban.class.php';
require_once __DIR__ . '/../lib/digikanban_kanban.lib.php';


// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user, $langs;

// Load translation files required by the page
saturne_load_langs();

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

// Initialize objects
// Technical objets
$object         = new Kanban($db);
$extrafields    = new ExtraFields($db);
$categorie 	    = new Categorie($db);

// View objects
$form = new Form($db);

$elementArray = get_kanban_linkable_objects();
$hookmanager->initHooks(array('kanbancard', 'globalcard')); // Note that conf->hooks_modules contains array

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
			else $backtopage = dol_buildpath('/digikanban/view/kanban_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
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

// Part to create
if ($action == 'create') {
	print load_fiche_titre($langs->trans('NewKanban'), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'" id="createQuestionForm" enctype="multipart/form-data">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldcreate kanban-table">'."\n";

	require_once DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	$linkableObjectsList = [];
	foreach($elementArray as $linkableElementType => $linkableElement) {
		$linkableObjectsList[$linkableElement['post_name']] = img_picto('', $linkableElement['picto']) . ' ' . $langs->trans($linkableElement['langs']);
	}

	//show dolibarr selector with $linkableObjectsList
	print '<tr><td class="fieldrequired minwidth400">'.$langs->trans('ObjectType').'</td><td>';
	print $form->selectarray('object_type', $linkableObjectsList, GETPOST('object_type', 'alpha'), 1, 0, 0, '', '', false);
	print '</td></tr>';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button wpeo-button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print ' &nbsp; <input type="button" id ="actionButtonCancelCreate" class="button" name="cancel" value="' . $langs->trans("Cancel") . '" onClick="javascript:history.go(-1)">';
	print '</div>';

	print '</form>';

	dol_set_focus('input[name="label"]');
}


// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	saturne_get_fiche_head($object, 'card', $title);
	saturne_banner_tab($object);

	$formconfirm = '';

	// Lock confirmation
	if (($action == 'lock' && (empty($conf->use_javascript_ajax) || !empty($conf->dol_use_jmobile))) || (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))) {
		$formconfirm .= $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('LockObject', $langs->transnoentities('The' . ucfirst($object->element))), $langs->trans('ConfirmLockObject', $langs->transnoentities('The' . ucfirst($object->element))), 'confirm_lock', '', 'yes', 'actionButtonLock', 350, 600);
	}

	// Clone confirmation
	if (($action == 'clone' && (empty($conf->use_javascript_ajax) || !empty($conf->dol_use_jmobile))) || (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))) {
		// Define confirmation messages
		$formkanbanclone = [
			['type' => 'text', 'name' => 'clone_label', 'label' => $langs->trans('NewLabelForClone', $langs->transnoentities('The' . ucfirst($object->element))), 'value' => $langs->trans('CopyOf') . ' ' . $object->ref, 'size' => 24],
			['type' => 'checkbox', 'name' => 'clone_photos', 'label' => $langs->trans('ClonePhotos'), 'value' => 1],
			['type' => 'checkbox', 'name' => 'clone_categories', 'label' => $langs->trans('CloneCategories'), 'value' => 1],
		];
		$formconfirm .= $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('CloneObject', $langs->transnoentities('The' . ucfirst($object->element))), $langs->trans('ConfirmCloneObject', $langs->transnoentities('The' . ucfirst($object->element)), $object->ref), 'confirm_clone', $formkanbanclone, 'yes', 'actionButtonClone', 350, 600);
	}

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Delete') . ' ' . $langs->transnoentities('The'  . ucfirst($object->element)), $langs->trans('ConfirmDeleteObject', $langs->transnoentities('The' . ucfirst($object->element))), 'confirm_delete', '', 'yes', 1);
	}

	// Call Hook formConfirm
	$parameters = ['formConfirm' => $formconfirm];
	$reshook    = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;

	print '<div class="clearboth"></div>';
	$categorie->fetch('', $object->ref);
	$linkedCategories = $categorie->get_filles();
	if (!empty($elementArray)) {
		foreach ($elementArray as $linkableElementType => $linkableElement) {
			if ($object->object_type == $linkableElement['post_name']) {
				$objectLinkedMetadata = $linkableElement;
				$objectLinkedType = $linkableElementType;
			}
		}
	}

	$objectFilter = [];
	$columns = [];
	if (is_array($linkedCategories) && !empty($linkedCategories)) {
		foreach($linkedCategories as $linkedCategory) {
			$objectsInCategory = $linkedCategory->getObjectsInCateg($objectLinkedMetadata['tab_type']);
			if (is_array($objectsInCategory) && !empty($objectsInCategory)) {
				foreach($objectsInCategory as $objectInCategory) {
					$objectFilter[] = $objectInCategory->id;
				}
			}
			$columns[] = [
				'label' => $linkedCategory->label,
				'category_id' => $linkedCategory->id,
				'objects' => $objectsInCategory
			];
		}
	}
	require_once DOL_DOCUMENT_ROOT . '/' . $objectLinkedMetadata['class_path'];

	$objectList = saturne_fetch_all_object_type($objectLinkedMetadata['className']);

	if (is_array($objectList) && !empty($objectList)) {
		foreach ($objectList as $objectSingle) {
			if (!in_array($objectSingle->id, $objectFilter)) {
				$objectName = '';
				$nameField = $objectLinkedMetadata['name_field'];
				if (strstr($nameField, ',')) {
					$nameFields = explode(', ', $nameField);
					if (is_array($nameFields) && !empty($nameFields)) {
						foreach ($nameFields as $subnameField) {
							$objectName .= $objectSingle->$subnameField . ' ';
						}
					}
				} else {
					$objectName = $objectSingle->$nameField;
				}
				$objectArray[$objectSingle->id] = $objectName;
			}
		}
	}


	print '<input hidden class="main-category-id" id="main_category_id" value="' . $categorie->id . '">';
//	print '<input hidden id="object_selector"'

	include_once __DIR__ . '/../core/tpl/kanban_view.tpl.php';

	print dol_get_fiche_end();

	print '<div class="fichecenter"><div class="fichehalfright">';

	$maxEvent = 10;

	$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-bars imgforviewmode', dol_buildpath('/saturne/view/saturne_agenda.php', 1) . '?id=' . $object->id . '&module_name=digikanban&object_type=' . $object->element);

	// List of actions on element
	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
	$formactions = new FormActions($db);
	$somethingshown = $formactions->showactions($object, $object->element . '@' . $object->module, '', 1, '', $MAXEVENT, '', $morehtmlcenter);

	print '</div></div>';
}

// End of page
llxFooter();
$db->close();
