<?php
/* Copyright (C) 2024 EVARISK <technique@evarisk.com>
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
 * \file    class/kanban.class.php
 * \ingroup saturne
 * \brief   This file is a CRUD class file for Kanban (Create/Read/Update/Delete)
 */

// Load Saturne libraries
require_once __DIR__ . '/../../saturne/class/saturneobject.class.php';

class Kanban extends SaturneObject
{
	/**
	 * @var DoliDB Database handler
	 */
	public $db;

	/**
	 * @var string Module name
	 */
	public $module = 'digikanban';

	/**
	 * @var string Element type of object
	 */
	public $element = 'kanban';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management
	 */
	public $table_element = 'digikanban_kanban';

	/**
	 * @var int Does this object support multicompany module ?
	 * 0 = No test on entity, 1 = Test with field entity, 'field@table' = Test with link by field@table
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int Does object support extrafields ? 0 = No, 1 = Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string Name of icon for saturne_redirection. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'saturne_redirection@saturne' if picto is file 'img/object_saturne_redirection.png'
	 */
	public string $picto = 'fontawesome_fa-list_fas_#d35968';

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = [
		'rowid'         => ['type' => 'integer',      'label' => 'TechnicalID',      'enabled' => 1, 'position' => 1,   'notnull' => 1, 'visible' => 0, 'noteditable' => 1, 'index' => 1, 'comment' => 'Id'],
		'ref'           => ['type' => 'varchar(128)',  'label' => 'Ref',             'enabled' => 1, 'position' => 10,  'notnull' => 0, 'visible' => 4, 'index' => 1],
		'entity'        => ['type' => 'integer',      'label' => 'Entity',           'enabled' => 1, 'position' => 30,  'notnull' => 1, 'visible' => 0, 'index' => 1],
		'date_creation' => ['type' => 'datetime',     'label' => 'DateCreation',     'enabled' => 1, 'position' => 40,  'notnull' => 1, 'visible' => 0],
		'tms'           => ['type' => 'timestamp',    'label' => 'DateModification', 'enabled' => 1, 'position' => 50,  'notnull' => 1, 'visible' => 0],
		'import_key'    => ['type' => 'varchar(14)',  'label' => 'ImportId',         'enabled' => 1, 'position' => 60,  'notnull' => 0, 'visible' => 0, 'index' => 0],
		'label'         => ['type' => 'varchar(255)', 'label' => 'Label',            'enabled' => 1, 'position' => 80,  'notnull' => 1, 'visible' => 1],
		'description'	=> ['type' => 'text',         'label' => 'Description',      'enabled' => 1, 'position' => 90,  'notnull' => 0, 'visible' => 1],
		'image_path'	=> ['type' => 'varchar(255)', 'label' => 'ImagePath',        'enabled' => 1, 'position' => 95,  'notnull' => 0, 'visible' => 3],
		'track_id'		=> ['type' => 'varchar(255)', 'label' => 'TrackId',          'enabled' => 1, 'position' => 97,  'notnull' => 0, 'visible' => 0],
		'status'		=> ['type' => 'integer',      'label' => 'Status',           'enabled' => 1, 'position' => 100, 'notnull' => 1, 'visible' => 0, 'default' => 1],
		'object_type'   => ['type' => 'varchar(255)', 'label' => 'ObjectType',       'enabled' => 1, 'position' => 105,  'notnull' => 1, 'visible' => 4, 'showinpwa' => 0, 'index' => 1, 'css' => 'maxwidth500 widthcentpercentminusxx', 'positioncard' => 2],
		'fk_user_creat' => ['type' => 'integer',      'label' => 'UserCreator',      'enabled' => 1, 'position' => 130, 'notnull' => 0, 'visible' => 0],
	];

	/**
	 * @var int ID
	 */
	public int $rowid;

	/**
	 * @var string Reference
	 */
	public $ref;

	/**
	 * @var int Entity
	 */
	public $entity;

	/**
	 * @var int|string Creation date
	 */
	public $date_creation;

	/**
	 * @var int|string Timestamp
	 */
	public $tms;

	/**
	 * @var string Import key
	 */
	public $import_key;

	/**
	 * @var string Label
	 */
	public string $label;

	/**
	 * @var string Description
	 */
	public string $description;

	/**
	 * @var string Image path
	 */
	public string $image_path;

	/**
	 * @var string Track ID
	 */
	public string $track_id;

	/**
	 * @var int Status
	 */
	public $status;

	/**
	 * @var int Project
	 */
	public $fk_project;

	/**
	 * @var string Object type
	 */
	public string $object_type;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db                  Database handler
	 * @param string $moduleNameLowerCase Module name
	 * @param string $objectType          Object element type
	 */
	public function __construct(DoliDB $db, string $moduleNameLowerCase = 'digikanban', string $objectType = 'kanban')
	{
		parent::__construct($db, $moduleNameLowerCase, $objectType);
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false = launch triggers after, true = disable triggers
	 * @return int             0 < if KO, ID of created object if OK
	 */
	public function create(User $user, bool $notrigger = false): int
	{
		$this->ref      = $this->getNextNumRef();
		$this->track_id = md5(uniqid(rand(), true));

		return parent::create($user, $notrigger);
	}

	public function setCategories($categories)
	{
		return 0;
	}

	public function getObjectKanbanView($object, $objectMetadata) {
		global $langs;

		$objectTitle = method_exists($object, 'getNomUrl') ? $object->getNomUrl(1) : $object->ref;
		$objectSubtitle = htmlspecialchars($object->label ?? '');
		$objectPicto = $object->picto;
		$moreData = '';

		if ($object->element == 'project') {
			$object->getLinesArray($user);
			$tasksCounter = is_array($object->lines) ? count($object->lines) : 0;
			if (is_array($object->lines) && !empty($object->lines)) {
				foreach($object->lines as $task) {
					$timeSpent += $task->duration_effective;
				}
			}
			$projectDate = dol_print_date($object->date ?? time(), 'day');
			$moreData = '<span class="kanban-data"><i class="fas fa-tasks"></i> ' . $tasksCounter . '</span> ';
			$timeSpentInHoursAndMinutes = gmdate('H:i', $timeSpent);
			$moreData .= '<span class="kanban-data"><i class="fas fa-clock"></i> ' . htmlspecialchars($timeSpentInHoursAndMinutes) . '</span> ';
			$moreData .= '<span class="kanban-data"><i class="fas fa-calendar"></i> ' . $projectDate . '</span>';
		}

		$nameField = $objectMetadata['name_field'];
		if (strstr($nameField, ',')) {
			$nameFields = explode(', ', $nameField);
			if (is_array($nameFields) && !empty($nameFields)) {
				foreach ($nameFields as $subnameField) {
					if ($subnameField != 'ref') {
						$objectSubtitle .= $object->$subnameField . ' ';
					}
				}
			}
		} else {
			$objectSubtitle = $object->$nameField;
		}

		$selected = (empty($arraydata['selected']) ? 0 : $arraydata['selected']);
		$actionsButton = '
    <span class="fas fa-ellipsis-h edit-card actions-icon" onclick="window.digikanban.kanban.toggleCardMenu(this)"></span>
    <div class="card-menu hidden">
        <div class="menu-item delete" onclick="window.digikanban.kanban.deleteCard(this)" data-card-id="' . $object->id . '">
            <i class="fas fa-trash"></i> Supprimer
        </div>
    </div>
';

		$return = '<div>';
		$return .= '<div class="kanban-card info-box ">';
		if ($selected >= 0) {
			$return .= '<input hidden id="cb'.$object->id.'" class="flat checkforselect fright" type="checkbox" name="toselect[]" value="'.$object->id.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		$return .= '<div class="kanban-card-header">';
		$return .= '<span class="kanban-card-ref">' . $objectTitle . '&nbsp;' . $actionsButton .'</span>';
		$return .= '</div>';
		$return .= '<div class="kanban-card-body">';
		$return .= '<span class="kanban-card-subtitle">' . $objectSubtitle . '</span>';
		$return .= '</div>';
		$return .= '<div class="kanban-card-footer">';
		$return .= $moreData;
		$return .= '</div>';
		$return .= '</div>';
		$return .= '</div>';
		return $return;
	}
}
