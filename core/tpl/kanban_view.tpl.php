<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kanban Board</title>
	<link rel="stylesheet" href="kanban.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div id="kanban-board" class="kanban-board">

	<?php
	// Cette section gère l'affichage des colonnes et des objets associés dans le Kanban
	// Itération sur les catégories (colonnes du Kanban)
	if (is_array($columns) && !empty($columns)) {
		$ajaxActionsUrl = dol_buildpath('/custom/digikanban/ajax/kanban.php', 1);

		foreach ($columns as $column) {
			$objectSelector = $form->selectArray($objectLinkedMetadata['post_name'] . $column['category_id'], $objectArray, GETPOST($objectLinkedMetadata['post_name']), $langs->trans('Select') . ' ' . strtolower($langs->trans($objectLinkedMetadata['langs'])), 0, 0, '', 0, 0, dol_strlen(GETPOST('fromtype')) > 0 && GETPOST('fromtype') != $objectLinkedMetadata['link_name'], '', 'maxwidth200 widthcentpercentminusxx kanban-select-option');
			$selectorName = $objectLinkedMetadata['post_name'] . $column['category_id'];
			print '<div class="kanban-column" category-id="'. $column['category_id'] .'">';
			print '<div class="kanban-column-header">';
			print '<input type="hidden" id="ajax_actions_url" value="' . $ajaxActionsUrl . '">';
			print '<input type="hidden" id="token" value="' .  newToken() . '">';
			print '<input type="hidden" id="object_type" value="' . $objectLinkedType . '">';
			print '<input type="hidden" id="selector_name" value="' . $selectorName . '">';
			print '<span class="column-name" onclick="window.digikanban.kanban.editColumn(this)">' . htmlspecialchars($column['label']) . '</span>';
			print '</div>';

			// Corps de la colonne où les objets sont listés
			print '<div class="kanban-column-body" id="' . strtolower(str_replace(' ', '-', $column['label'])) . '-column">';
			$objectsInColumn = $column['objects'];

			if (is_array($objectsInColumn) && !empty($objectsInColumn)) {
				foreach($objectsInColumn as $object) {
					print $object->getKanbanView();
				}
			}
			print '</div>';

			// Affichage du sélecteur d'objet pour ajouter de nouveaux objets dans chaque colonne
			print '<div class="add-item">';
			print '<form method="POST" action="add_object_to_kanban.php">'; // Form pour ajouter un objet
			print $objectSelector;
			print '<button type="button" disabled class="butAction butActionRefused validate-button">Valider</button>';
			print '</form>';
			print '</div>';
			print '</div>';
		}
	}
	?>

	<div class="kanban-add-column" onclick="window.digikanban.kanban.addColumn()">
		<div class="add-column-text">+ Ajouter une colonne</div>
	</div>
</div>
</body>
</html>
