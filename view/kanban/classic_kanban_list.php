<?php
/* Copyright (C) 2022-2024 EVARISK <technique@evarisk.com>
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
 *   	\file       view/kanban/kanban_list.php
 *		\ingroup    digikanban
 *		\brief      List page for kanban
 */

// Load DigiQuali environment
if (file_exists('../digikanban.main.inc.php')) {
	require_once __DIR__ . '/../digikanban.main.inc.php';
} elseif (file_exists('../../digikanban.main.inc.php')) {
	require_once __DIR__ . '/../../digikanban.main.inc.php';
} else {
	die('Include of digikanban main fails');
}

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcategory.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

// Load digikanban libraries
require_once __DIR__ . '/../../class/kanban.class.php';
require_once __DIR__ . '/../../lib/digikanban_kanban.lib.php';

if (isModEnabled('digiquali')) {
	require_once DOL_DOCUMENT_ROOT . '/custom/digiquali/lib/digiquali_control.lib.php';
}

// Global variables definitions
global $db, $hookmanager, $langs, $user;

// Load translation files required by the page
saturne_load_langs(["other"]);

// Define kanbans
$kanbans = [
	[
		'type' => 'ticket',
		'title' => $langs->trans("KanbanTickets"),
		'picto' => 'object_ticket',
	],
	[
		'type' => 'project',
		'title' => $langs->trans("KanbanProjects"),
		'picto' => 'object_project',
	],
	[
		'type' => 'opportunity',
		'title' => $langs->trans("KanbanOpportunities"),
		'picto' => 'object_project',
	]
];

// Header
saturne_header(0, '', $langs->trans("KanbanList"), '');

// Title
print load_fiche_titre($langs->trans("KanbanList"), '', 'title_generic.png');

// Content
print '<div class="classic-kanban-container">';
foreach ($kanbans as $kanban) {
	print '<a href="' . dol_buildpath('/custom/digikanban/view/kanban/classic_kanban_card?type=' . $kanban['type'], 1) . '" class="classic-kanban-card">';
	print '<div class="classic-kanban-card-content">';
	print '<span class="img-picto">' . img_picto('', $kanban['picto']) . '</span>';
	print '<div class="classic-kanban-text">';
	print '<h2>' . $kanban['title'] . '</h2>';
	print '</div>';
	print '</div>';
	print '</a>';
}
print '</div>';

// End of page
llxFooter();
$db->close();
