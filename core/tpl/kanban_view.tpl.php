<div id="kanban-board" class="kanban-board" style="background-image: url(<?php echo $object->image_path ?>)">
	<?php
	// Cette section gère l'affichage des colonnes et des objets associés dans le Kanban
	// Itération sur les catégories (colonnes du Kanban)
	if (is_array($columns) && !empty($columns)) {
		$ajaxActionsUrl = dol_buildpath('/custom/digikanban/ajax/kanban.php', 1);
		print '<input type="hidden" id="post_name" value="' . $objectLinkedMetadata['post_name'] . '">';
		print '<input type="hidden" id="object_linked_langs" value="' . $objectLinkedMetadata['langs'] . '">';
		print '<input hidden id="object_array" value="' . htmlspecialchars(json_encode($objectArray)) . '">';
		$actionsButton = '<span class="fas fa-ellipsis-h"></span>';

		foreach ($columns as $column) {
			$objectSelector = $form->selectArray($objectLinkedMetadata['post_name'] . $column['category_id'], $objectArray, GETPOST($objectLinkedMetadata['post_name']), $langs->trans('Select') . ' ' . strtolower($langs->trans($objectLinkedMetadata['langs'])), 0, 0, '', 0, 0, dol_strlen(GETPOST('fromtype')) > 0 && GETPOST('fromtype') != $objectLinkedMetadata['link_name'], '', 'maxwidth400 minheight30 widthcentpercentminusxx kanban-select-option');
			$selectorName = $objectLinkedMetadata['post_name'] . $column['category_id'];
			$objectsInColumn = $column['objects'];

			print '<div class="kanban-column" category-id="'. $column['category_id'] .'">';
			print '<div class="kanban-column-header">';
			print '<input type="hidden" id="ajax_actions_url" value="' . $ajaxActionsUrl . '">';
			print '<input type="hidden" id="token" value="' .  newToken() . '">';
			print '<input type="hidden" id="object_type" value="' . $objectLinkedType . '">';
			print '<input type="hidden" id="selector_name" value="' . $selectorName . '">';

			if (is_array($objectsInColumn) && !empty($objectsInColumn)) {
				$objectsCounter = count($objectsInColumn);
			} else {
				$objectsCounter = 0;
			}

			print '<div class="column-left">';
			print '<span class="column-name" onclick="window.digikanban.kanban.editColumn(this)">' . htmlspecialchars($column['label']) . '</span>';
			print '<span class="column-counter">' . $objectsCounter . '</span>';
			print '</div>';
			print '<span class="fas fa-ellipsis-h actions-icon" onclick="window.digikanban.kanban.toggleColumnMenu(this)"></span>';
			print '<div class="column-menu hidden">';
			print '  <div class="menu-item rename" onclick="window.digikanban.kanban.editColumn(this)">';
			print '    <i class="fas fa-pen"></i> Renommer';
			print '  </div>';
			print '  <div class="menu-item delete" onclick="window.digikanban.kanban.deleteColumn(this)">';
			print '    <i class="fas fa-trash"></i> Supprimer';
			print '  </div>';
			print '</div>';
			print '</div>';


			print '<div class="kanban-column-body" id="' . strtolower(str_replace(' ', '-', $column['label'])) . '-column">';

			if (is_array($objectsInColumn) && !empty($objectsInColumn)) {
				foreach($objectsInColumn as $objectInColumn) {
					print $object->getObjectKanbanView($objectInColumn, $elementArray[$objectLinkedType]);
				}
			}
			print '</div>';

			if (!$publicView) {
				print '<div class="add-item" >';
				print '<form method="POST" action="add_object_to_kanban.php">';
				print $objectSelector;
				print '<button type="button" disabled class="butAction butActionRefused validate-button"><i class="fas fa-plus"></i></button>';
				print '</form>';
				print '</div>';
			}
			print '</div>';

		}
	}
	?>

	<div class="kanban-add-column" onclick="window.digikanban.kanban.addColumn()">
		<i class="fas fa-plus"></i>
	</div>
</div>
