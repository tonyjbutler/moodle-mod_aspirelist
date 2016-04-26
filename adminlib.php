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
 * Aspirelist module admin lib
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Admin setting for code source, adds validation.
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */
class aspirelist_codesource_setting extends admin_setting_configselect {

    /**
     * Validate data.
     *
     * This ensures that all required table details are provided if custom table
     * is selected as the code source.
     *
     * @param string $data
     * @return mixed True on success, else error message
     */
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $codetable = get_config('aspirelist', 'codetable');
        $codecolumn = get_config('aspirelist', 'codecolumn');
        $coursecolumn = get_config('aspirelist', 'coursecolumn');
        $courseattribute = get_config('aspirelist', 'courseattribute');
        if ($data === 'codetable'
                && (empty($codetable) || empty($codecolumn)
                || empty($coursecolumn) || empty($courseattribute))) {
            return get_string('errorcodesource', 'aspirelist');
        }
        return true;
    }
}

/**
 * Admin setting for code table, adds validation.
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */
class aspirelist_codetable_setting extends admin_setting_configtext {

    /**
     * Validate data.
     *
     * This ensures that a table name is specified if custom table
     * is selected as the code source.
     *
     * @param string $data
     * @return mixed True on success, else error message
     */
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $codesource = get_config('aspirelist', 'codesource');
        if ($codesource === 'codetable' && empty($data)) {
            return get_string('errorcodetable', 'aspirelist');
        }
        return true;
    }
}

/**
 * Admin setting for code column, adds validation.
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */
class aspirelist_codecolumn_setting extends admin_setting_configtext {

    /**
     * Validate data.
     *
     * This ensures that a code column is specified if custom table
     * is selected as the code source.
     *
     * @param string $data
     * @return mixed True on success, else error message
     */
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $codesource = get_config('aspirelist', 'codesource');
        if ($codesource === 'codetable' && empty($data)) {
            return get_string('errorcodecolumn', 'aspirelist');
        }
        return true;
    }
}

/**
 * Admin setting for course column, adds validation.
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */
class aspirelist_coursecolumn_setting extends admin_setting_configtext {

    /**
     * Validate data.
     *
     * This ensures that a course column is specified if custom table
     * is selected as the code source.
     *
     * @param string $data
     * @return mixed True on success, else error message
     */
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $codesource = get_config('aspirelist', 'codesource');
        if ($codesource === 'codetable' && empty($data)) {
            return get_string('errorcoursecolumn', 'aspirelist');
        }
        return true;
    }
}

/**
 * Admin setting for course attribute, adds validation.
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */
class aspirelist_courseattribute_setting extends admin_setting_configtext {

    /**
     * Validate data.
     *
     * This ensures that a course attribute is specified if custom table
     * is selected as the code source.
     *
     * @param string $data
     * @return mixed True on success, else error message
     */
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $codesource = get_config('aspirelist', 'codesource');
        if ($codesource === 'codetable' && empty($data)) {
            return get_string('errorcourseattribute', 'aspirelist');
        }
        return true;
    }
}
