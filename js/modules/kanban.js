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

	$('.kanban-column-body').sortable({
		connectWith: '.kanban-column-body',
		placeholder: 'kanban-placeholder',
		handle: '.info-box',
		tolerance: 'pointer',
		over: function() {
			$(this).css('cursor', 'grabbing');
		},
		stop: function(event, ui) {
			window.digikanban.kanban.saveCardOrder();
		},
	});

	const kanbanBoard = document.getElementById('kanban-board');
	let isDragging = false;
	let startX, scrollLeft;

	kanbanBoard.addEventListener('mousedown', (e) => {
		const isClickInsideKanban = e.target.closest('.kanban-column, .kanban-column-header, .kanban-card, .kanban-select-option');

		if (!isClickInsideKanban) {
			isDragging = true;
			kanbanBoard.classList.add('dragging');
			startX = e.pageX - kanbanBoard.offsetLeft;
			scrollLeft = kanbanBoard.scrollLeft;
		}
	});

	kanbanBoard.addEventListener('mousemove', (e) => {
		if (!isDragging) return;
		e.preventDefault();
		const x = e.pageX - kanbanBoard.offsetLeft;
		const walk = (x - startX) * 1.5;
		kanbanBoard.scrollLeft = scrollLeft - walk;
	});

	kanbanBoard.addEventListener('mouseup', () => {
		isDragging = false;
		kanbanBoard.classList.remove('dragging');
	});

	kanbanBoard.addEventListener('mouseleave', () => {
		isDragging = false;
		kanbanBoard.classList.remove('dragging');
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
		success: function() {
			console.log("Card order saved successfully.");
			for (let i = 0; i < cardOrder.length; i++) {
				let columnCounterElement = $('.kanban-column[category-id="' + cardOrder[i].columnId + '"]').find('.column-counter');
				columnCounterElement.text(cardOrder[i].cards.length);
			}
		},
		error: function() {
			console.log("Error saving card order.");
		}
	});
};

/**
 * Add a new column to the kanban board.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @returns {void}
 */
window.digikanban.kanban.addColumn = function() {
	const kanbanBoard = document.getElementById('kanban-board');

	// Nom par défaut pour la colonne
	const defaultColumnName = "Nouvelle colonne";
	let url                 = $('#ajax_actions_url').val();
	let token               = window.saturne.toolbox.getToken();
	let objectType          = $('#object_type').val();
	let mainCategoryId      = $('#main_category_id').val();
	let postName            = $('#post_name').val();
	let objectLinkedLangs   = $('#object_linked_langs').val();
	let objectArray         = $('#object_array').val();

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
			let decodedResponse = JSON.parse(response);
			let categoryId      = decodedResponse.category_id;
			let objectSelector  = (decodedResponse.object_selector);

			const newColumn = document.createElement('div');
			newColumn.classList.add('kanban-column');
			newColumn.setAttribute('category-id', categoryId);

			newColumn.innerHTML = `
                <div class="kanban-column-header">
                    <span class="column-name" onclick="window.digikanban.kanban.editColumn(this)">${defaultColumnName}</span>
                </div>
                <div class="kanban-column-body"></div>
                <div class="add-item">
                    <form method="POST" action="add_object_to_kanban.php">
                        ${objectSelector}
                        <button type="button" disabled class="butAction butActionRefused validate-button">Valider</button>
                    </form>
                </div>
            `;

			const addColumnElement = document.querySelector('.kanban-add-column');
			kanbanBoard.insertBefore(newColumn, addColumnElement);

			const selectElement = newColumn.querySelector('select');
			if (selectElement) {
				$(selectElement).select2({
					width: 'resolve',
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
 * Edit the name of a column.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @returns {void}
 */
window.digikanban.kanban.editColumn = function(nameElement) {
	let columnNameElement = $(nameElement).closest('.kanban-column').find('.column-name')[0]; // Récupère l'élément DOM

	if (!columnNameElement) {
		console.error('Erreur : Impossible de trouver l\'élément column-name.');
		return;
	}

	const currentName = columnNameElement.innerText.trim(); // Récupère le texte actuel
	const input = document.createElement('input'); // Crée un champ input
	input.type = 'text';
	input.value = currentName;
	input.classList.add('column-name-input');

	input.addEventListener('blur', function() {
		saveColumnName(input, columnNameElement);
	});

	$(nameElement).closest('.kanban-column').find('.column-menu').addClass('hidden');

	columnNameElement.style.display = 'none';
	columnNameElement.parentNode.insertBefore(input, columnNameElement);
	input.focus(); // Met le curseur dans l'input
};


/**
 * Save the new column name.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @returns {void}
 */
function saveColumnName(input, nameElement) {
	let url = $('#ajax_actions_url').val();

	let token       = window.saturne.toolbox.getToken();
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
 * Select an option in the dropdown menu.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @returns {void}
 */
window.digikanban.kanban.selectOption = function() {
	const validateButton = $(this).parent().find('.validate-button');
	validateButton.removeClass('butActionRefused')
	validateButton.removeAttr('disabled')
}

/**
 * Add an object to a column.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @returns {void}
 */
window.digikanban.kanban.addObjectToColumn = function() {
	const objectId   = $(this).parent().find('.kanban-select-option').val();
	const categoryId = $(this).closest('.kanban-column').attr('category-id');
	const token      = window.saturne.toolbox.getToken();

	let objectType = $('#object_type').val();
	let url        = $('#ajax_actions_url').val();

	window.saturne.loader.display($(this).parent().find('.kanban-select-option'));
	url += '?action=add_object_to_column&object_id=' + objectId + '&category_id=' + categoryId + '&token=' + token + '&object_type=' + objectType;

	let columnCounterElement = $(this).closest('.kanban-column').find('.column-counter');

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
			columnCounterElement.text(parseInt(columnCounterElement.text()) + 1);
		},
		error: function() {
			console.log("Failed to add object to column.");
			$('.wpeo-loader').removeClass('wpeo-loader');
		}
	});
};

/**
 * Refresh the selector after adding an object to a column.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @returns {void}
 */
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
					$(this).replaceWith($(resp).find('#' + selectorId).closest('form'));
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

window.digikanban.kanban.toggleColumnMenu = function(iconElement) {
	const menu = iconElement.nextElementSibling;
	if (menu.classList.contains('hidden')) {
		document.querySelectorAll('.column-menu').forEach(m => m.classList.add('hidden')); // Fermer tous les autres menus
		menu.classList.remove('hidden');
	} else {
		menu.classList.add('hidden');
	}
};

window.digikanban.kanban.deleteColumn = function(menuItem) {
	const column = menuItem.closest('.kanban-column');
	const columnId = column.getAttribute('category-id');
	const url = $('#ajax_actions_url').val();
	const token = $('#token').val();
	const objectType = $('#object_type').val();
	if (confirm('Êtes-vous sûr de vouloir supprimer cette colonne ?')) {
		$.ajax({
			url: `${url}?action=delete_column&token=${token}&category_id=${columnId}&object_type=${objectType}`,
			type: 'POST',
			success: function() {
				column.remove();
				alert('Colonne supprimée avec succès.');
			},
			error: function() {
				alert('Erreur lors de la suppression de la colonne.');
			},
		});
	}
};

document.addEventListener('click', function(event) {
	if (!event.target.closest('.kanban-column-header')) {
		document.querySelectorAll('.column-menu').forEach(menu => menu.classList.add('hidden'));
	}
});
