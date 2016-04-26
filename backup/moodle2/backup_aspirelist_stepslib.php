<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Define all the backup steps that will be used by the backup_aspirelist_activity_task
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete aspirelist structure for backup, with file and id annotations
 */
class backup_aspirelist_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $aspirelist = new backup_nested_element('aspirelist', array('id'), array('name', 'intro', 'introformat',
            'timemodified', 'display', 'items'));

        // Build the tree
        // (nice mono-tree, lol).

        // Define sources.
        $aspirelist->set_source_table('aspirelist', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations
        // (none).

        // Define file annotations.
        $aspirelist->annotate_files('mod_aspirelist', 'intro', null);

        // Return the root element (aspirelist), wrapped into standard activity structure.
        return $this->prepare_activity_structure($aspirelist);
    }
}
