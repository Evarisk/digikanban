<div id="kanban-board" class="kanban-board" style="background-image: url(<?php echo $object->image_path ?>)">
	<?php
	// Cette section gère l'affichage des colonnes et des objets associés dans le Kanban
	// Itération sur les catégories (colonnes du Kanban)
	if (is_array($columns) && !empty($columns)) {
		$ajaxActionsUrl = dol_buildpath('/custom/digikanban/ajax/kanban.php', 1);
		print '<input type="hidden" id="post_name" value="' . $objectLinkedMetadata['post_name'] . '">';
		print '<input type="hidden" id="object_linked_langs" value="' . $objectLinkedMetadata['langs'] . '">';
		print '<input hidden id="object_array" value="' . htmlspecialchars(json_encode($objectArray)) . '">';

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

			print '<div class="kanban-column-body" id="' . strtolower(str_replace(' ', '-', $column['label'])) . '-column">';
			$objectsInColumn = $column['objects'];

			if (is_array($objectsInColumn) && !empty($objectsInColumn)) {
				foreach($objectsInColumn as $objectInColumn) {
					if (method_exists($objectInColumn, 'getKanbanView')) {
						print $objectInColumn->getKanbanView();
					} else {
						print $object->getObjectKanbanView($objectInColumn);
					}
				}
			}
			print '</div>';

			if (!$publicView) {
				print '<div class="add-item">';
				print '<form method="POST" action="add_object_to_kanban.php">';
				print $objectSelector;
				print '<button type="button" disabled class="butAction butActionRefused validate-button">Valider</button>';
				print '</form>';
				print '</div>';
			}
			print '</div>';

		}
	}
	?>

	<div class="kanban-add-column" onclick="window.digikanban.kanban.addColumn()">
		<div class="add-column-text">+ Ajouter une colonne</div>
	</div>
</div>
