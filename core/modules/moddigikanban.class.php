<?php
/* Copyright (C) 2022 EVARISK <technique@evarisk.com>
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
 * 	\defgroup   digikanban     Module DigiKanban
 *  \brief      DigiKanban module descriptor.
 *
 *  \file       core/modules/modDigiKanban.class.php
 *  \ingroup    digikanban
 *  \brief      Description and activation file for module DigiKanban
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module digikanban
 */
class modDigiKanban extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
        global $conf, $langs;

        $this->db = $db;

        if (file_exists(__DIR__ . '/../../../saturne/lib/saturne_functions.lib.php')) {
			require_once __DIR__ . '/../../../saturne/lib/saturne_functions.lib.php';
			saturne_load_langs(['digikanban@digikanban']);
		} else {
			$this->error++;
			$this->errors[] = $langs->trans('activateModuleDependNotSatisfied', 'DigiQuali', 'Saturne');
		}

        // Id for module (must be unique).
		$this->numero = 19055200;

        // Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'digikanban';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = '';

        // Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		$this->familyinfo = ['Evarisk' => ['position' => '01', 'label' => $langs->trans('Evarisk')]];
        // Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
        
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "DigiKanbanDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = $langs->trans('DigiKanbanDescriptionLong');

        // Author
        $this->editor_name = 'Evarisk';
        $this->editor_url = 'https://evarisk.com/';

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.0.0';

		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='digikanban@digikanban';
		
		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = [
			'hooks' => ['projecttaskcard','projecttaskscard'], 
		];

		// Data directories to create when module is enabled.
		// Example: this->dirs = ["/digikanban/temp"];
		$this->dirs = [];

		// Config pages. Put here list of php page, stored into digikanban/admin directory, to use to setup module.
		$this->config_page_url = ['admin.php@digikanban'];

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of modules id that must be enabled if this module is enabled
		$this->depends = ['modProjet'];
		$this->requiredby = [];	// List of modules id to disable if this one is disabled
		$this->conflictwith = []; // List of modules id this module is in conflict with

		// The language file dedicated to your module
		$this->langfiles = ["digikanban@digikanban"];

		// Prerequisites
		$this->phpmin = [7, 4]; // Minimum version of PHP required by module
		$this->need_dolibarr_version = [17, 0]; // Minimum version of Dolibarr required by module

		// Messages at activation
		$this->warnings_activation = []; // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = []; // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		
		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		$i = 0;
		$this->const = [
			$i++ => ['DIGIKANBAN_VERSION','chaine', $this->version, '', 0, 'current'],
			$i++ => ['DIGIKANBAN_DB_VERSION', 'chaine', $this->version, '', 0, 'current'],
		];

		if (!isset($conf->digikanban) || !isset($conf->digikanban->enabled))
		{
			$conf->digikanban = new stdClass();
			$conf->digikanban->enabled=0;
		}

		$this->tabs = [
			'task:+tab_commentaire:Comments:digikanban@digikanban:$user->rights->digikanban->lire:/digikanban/commentaire.php?id=__ID__',
		];

        $this->dictionaries = [
            'langs' => 'digikanban@digikanban',
            'tabname' => [
                MAIN_DB_PREFIX . 'c_tasks_columns',
            ],
            'tablib' => [
                'TasksColumns',
            ],
            'tabsql' => [
                'SELECT t.rowid as rowid, t.ref, t.label, t.lowerpercent, t.upperpercent, t.position, t.active FROM ' . MAIN_DB_PREFIX . 'c_tasks_columns as t',
            ],
            'tabsqlsort' => [
                'position ASC',
            ],
            'tabfield' => [
                'ref,label,lowerpercent,upperpercent,position',
            ],
            'tabfieldvalue' => [
                'ref,label,lowerpercent,upperpercent,position',
            ],
            'tabfieldinsert' => [
                'ref,label,lowerpercent,upperpercent,position',
            ],
            'tabrowid' => [
                'rowid',
            ],
            'tabcond' => [
                $conf->digikanban->enabled,
            ]
        ];

        // Boxes/Widgets
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = [];

		// Permission array used by this module
		$this->rights = [];		
		$r = 0;

		$this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
		$this->rights[$r][1] = $langs->trans('Show');
		$this->rights[$r][4] = 'lire';
		$this->rights[$r][5] = 1;
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
		$this->rights[$r][1] = $langs->trans('Create');
		$this->rights[$r][4] = 'creer';
		$this->rights[$r][5] = 1;
		$r++;
		
		$this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
		$this->rights[$r][1] = $langs->trans('Delete');
		$this->rights[$r][4] = 'supprimer';
		$this->rights[$r][5] = 1;
		$r++;
		

		// Main menu entries
		$this->menu = [];
		$r = 0;

		// Add here entries to declare new menus
		$this->menu[$r++] = [
			'fk_menu'  => 'fk_mainmenu=project',
			'type'     => 'left',
			'titre'    => 'digikanban',
			'prefix'   => '<span class="paddingrightonly fa fa-th-list"></span>',
			'leftmenu' => 'digikanban',
			'url'      => '/digikanban/index.php',
			'langs'    => 'digikanban@digikanban',
			'position' => 100,
			'enabled'  => '1',
			'perms'    => '$user->rights->digikanban->lire',
			'target'   => '',
			'user'     => 2,
		];

		$this->menu[$r++] = [
			'fk_menu'  => 'fk_mainmenu=project,fk_leftmenu=digikanban',
			'type'     => 'left',
			'titre'    => 'viewkanban',
			'leftmenu' => 'viewkanban',
			'url'      => '/digikanban/index.php',
			'langs'    => 'digikanban@digikanban',
			'position' => 201,
			'enabled'  => '1',
			'perms'    => '($conf->global->DOLIBARR_PLATEFORME_DEMO_MODULES || $user->admin)',
			'target'   => '',
			'user'     => 2,
		];

		$this->menu[$r++] = [
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=digikanban',
			'type'=>'left',
			'titre'=>'columns',
			'leftmenu'=>'columns',
			'url'=>'/digikanban/columns/list.php',
			'langs'=>'digikanban@digikanban',
			'position'=>202,
			'enabled'=>'1',
			'perms'=>'($conf->global->DOLIBARR_PLATEFORME_DEMO_MODULES || $user->admin)',
			'target'=>'',
			'user'=>2,
		];

		$this->menu[$r++] = [
			'fk_menu'  => 'fk_mainmenu=project,fk_leftmenu=digikanban',
			'type'     => 'left',
			'titre'    => 'Configuration',
			'leftmenu' => 'configdigikanban',
			'url'      => '/digikanban/admin/admin.php',
			'langs'    => 'digikanban@digikanban',
			'position' => 203,
			'enabled'  => '1',
			'perms'    => '($conf->global->DOLIBARR_PLATEFORME_DEMO_MODULES || $user->admin)',
			'target'   => '',
			'user'     => 2
		];
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function init($options = ''): int
	{
		global $conf;

		if ($this->error > 0) {
			setEventMessages('', $this->errors, 'errors');
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

        $sql = [];
        $result = $this->_load_tables('/digikanban/sql/');

        if ($result < 0) {
			return -1;
		} // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')

        dolibarr_set_const($this->db, 'DIGIKANBAN_VERSION', $this->version, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($this->db, 'DIGIKANBAN_DB_VERSION', $this->version, 'chaine', 0, '', $conf->entity);

        // Permissions
        $this->remove($options);

        $result = $this->_init($sql, $options);

		return $result;
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function remove($options = ''): int
	{
		$sql = [];
		return $this->_remove($sql, $options);
	}

}
