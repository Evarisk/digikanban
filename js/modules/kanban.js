// Initialize kanban object
window.digikanban.kanban = {};

/**
 * La méthode appelée automatiquement par la bibliothèque EoxiaJS.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return {void}
 */
window.digikanban.kanban.init = function() {
	window.digikanban.kanban.event();
};

/**
 * La méthode contenant tous les événements pour le control.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return {void}
 */
window.digikanban.kanban.event = function() {
	$(document).on('change', '.kanban-select-option', window.digikanban.kanban.selectOption);
	$(document).on('click', '.validate-button:not(.butActionRefused)', window.digikanban.kanban.addObjectToColumn);

	$('.info-box').attr('draggable', 'true');

	// Enable drag-and-drop for all box-flex-item elements
	$('.kanban-column-body').sortable({
		connectWith: '.kanban-column-body', // Allow dragging between columns
		placeholder: 'kanban-placeholder',  // CSS class for placeholder when dragging
		handle: '.info-box',           // Limit dragging to cards only
		tolerance: 'pointer',               // Make dragging smoother
		over: function() {
			// Add dragging class for visual feedback
			$(this).css('cursor', 'grabbing');
		},
		stop: function(event, ui) {
			// Trigger an AJAX call to save the new order of the cards after drop
			window.digikanban.kanban.saveCardOrder();
		},


	});
};
/**
 * Save the new card order after drag-and-drop.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @returns {void}
 */
window.digikanban.kanban.saveCardOrder = function() {
	let objectType = $('#object_type').val();
	let cardOrder = [];
	$('.kanban-column').each(function() {
		let columnId = $(this).attr('category-id');
		let cards = [];

		$(this).find('.info-box').each(function() {
			cards.push($(this).find('.checkforselect').attr('value'));
		});

		cardOrder.push({
			columnId: columnId,
			cards: cards
		});
	});
	let url = $('#ajax_actions_url').val();

	let token = window.saturne.toolbox.getToken();
	$.ajax({
		url: url + "?action=move_object&token=" + token + '&object_type=' + objectType,
		type: "POST",
		data: JSON.stringify({
			order: cardOrder
		}),
		contentType: "application/json",
		success: function(response) {
			console.log("Card order saved successfully.");
		},
		error: function() {
			console.log("Error saving card order.");
		}
	});
};

/**
 * Add a new column to the kanban board
 */
window.digikanban.kanban.addColumn = function() {
	const kanbanBoard = document.getElementById('kanban-board');

	// Nom par défaut pour la colonne
	const defaultColumnName = "Nouvelle colonne";
	let url = $('#ajax_actions_url').val();
	let token = window.saturne.toolbox.getToken();
	let objectType = $('#object_type').val();
	let mainCategoryId = $('#main_category_id').val();
	let postName = $('#post_name').val();
	let objectLinkedLangs = $('#object_linked_langs').val();
	let objectArray = $('#object_array').val();

	// Appel AJAX pour créer la colonne côté serveur
	$.ajax({
		url: url + "?action=add_column&token=" + token + '&object_type=' + objectType + '&category_id=' + mainCategoryId,
		type: "POST",
		data: JSON.stringify({
			column_name: defaultColumnName,
			post_name: postName,
			object_linked_langs: objectLinkedLangs,
			object_array: objectArray
		}),
		contentType: "application/json",
		success: function(response) {
			// Récupère le category_id depuis la réponse
			let decodedResponse = JSON.parse(response);
			let categoryId = decodedResponse.category_id;
			console.log('coucou')
			console.log(JSON.parse(response))
			console.log(decodedResponse.object_selector)
			let objectSelector = (decodedResponse.object_selector);

			// Crée dynamiquement une nouvelle colonne avec les informations reçues
			const newColumn = document.createElement('div');
			newColumn.classList.add('kanban-column');
			newColumn.setAttribute('category-id', categoryId); // Assigner le nouvel ID de colonne

			newColumn.innerHTML = `
                <div class="kanban-column-header">
                    <span class="column-name" onclick="window.digikanban.kanban.editColumn(this)">${defaultColumnName}</span>
                </div>
                <div class="kanban-column-body"></div>
                <div class="add-item">
                    <form method="POST" action="add_object_to_kanban.php">
                        ${objectSelector} <!-- Insérer le sélecteur d'objet ici -->
                        <button type="button" disabled class="butAction butActionRefused validate-button">Valider</button>
                    </form>
                </div>
            `;
			// Insère la nouvelle colonne avant le bouton "Ajouter une colonne"
			const addColumnElement = document.querySelector('.kanban-add-column');
			kanbanBoard.insertBefore(newColumn, addColumnElement);

			const selectElement = newColumn.querySelector('select');
			if (selectElement) {
				$(selectElement).select2({
					width: 'resolve', // Peut être ajusté selon le besoin
					minimumInputLength: 0
				});
			}

		},
		error: function() {
			console.log("Error adding column to the server.");
		}
	});
};


/**
 * Edit the column name when clicking on the column name or pencil icon
 */
window.digikanban.kanban.editColumn = function(nameElement) {
	const currentName = nameElement.innerText;
	const input = document.createElement('input');
	input.type = 'text';
	input.value = currentName;
	input.classList.add('column-name-input');

	input.addEventListener('blur', function() {
		saveColumnName(input, nameElement);
	});

	// Affiche l'input et cache le nom actuel
	nameElement.style.display = 'none';
	nameElement.parentNode.insertBefore(input, nameElement);
	input.focus();
}


/**
 * Save the column name and revert back to display mode
 */
function saveColumnName(input, nameElement) {
	let url = $('#ajax_actions_url').val();

	let token = window.saturne.toolbox.getToken();
	let object_type = $('#object_type').val();
	let category_id = nameElement.closest('.kanban-column').getAttribute('category-id');

	$.ajax({
		url: url + "?action=rename_column&token=" + token + '&category_name=' + input.value + '&object_type=' + object_type + '&category_id=' + category_id,
		type: "POST",
		contentType: "application/json",
		success: function(response) {
			nameElement.innerText = input.value;
			nameElement.style.display = 'inline';
			input.remove();
			},
		error: function() {
			console.log("Error saving card order.");
		}
	})

}

/**
 * Triggers when element is selected in the select box
 */
window.digikanban.kanban.selectOption = function() {
	const validateButton = $(this).parent().find('.validate-button');
	validateButton.removeClass('butActionRefused')
	validateButton.removeAttr('disabled')
}

window.digikanban.kanban.addObjectToColumn = function() {
	const objectId = $(this).parent().find('.kanban-select-option').val();
	const categoryId = $(this).closest('.kanban-column').attr('category-id');
	const token = window.saturne.toolbox.getToken();

	let objectType = $('#object_type').val();
	let url = $('#ajax_actions_url').val();

	window.saturne.loader.display($(this).parent().find('.kanban-select-option'));
	url += '?action=add_object_to_column&object_id=' + objectId + '&category_id=' + categoryId + '&token=' + token + '&object_type=' + objectType;

	$.ajax({
		url: url,
		type: 'POST',
		processData: false,
		contentType: false,
		success: function(resp) {
			let kanbanColumn = $('.kanban-column[category-id="' + categoryId + '"]');
			kanbanColumn.find('.kanban-column-body').append(resp);

			window.digikanban.kanban.refreshSelector().then(() => {
				$('.wpeo-loader').removeClass('wpeo-loader');
			}).catch(() => {
				console.log("Error refreshing selectors.");
				$('.wpeo-loader').removeClass('wpeo-loader');
			});
		},
		error: function() {
			console.log("Failed to add object to column.");
			$('.wpeo-loader').removeClass('wpeo-loader');
		}
	});
};

window.digikanban.kanban.refreshSelector = function() {
	return new Promise((resolve, reject) => {
		let token = window.saturne.toolbox.getToken();
		let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);
		let form = $('.kanban-board').find('form');

		$.ajax({
			url: document.URL + querySeparator + 'token=' + token,
			type: 'POST',
			processData: false,
			contentType: false,
			success: function(resp) {
				form.each(function() {
					let selectorId = $(this).find('select').attr('id');
					$(this).replaceWith($(resp).find('#' + selectorId).closest('form')); // Remplacer uniquement si le nouveau sélecteur existe
				});
				resolve();
			},
			error: function() {
				console.log("Failed to refresh the selectors.");
				reject(); 
			}
		});
	});
};

