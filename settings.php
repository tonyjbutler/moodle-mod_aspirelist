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
 * Aspirelist module admin settings and defaults
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/aspirelist/adminlib.php');

if ($ADMIN->fulltree) {
    // General settings.
    $settings->add(new admin_setting_configcheckbox('aspirelist/requiremodintro', get_string('requiremodintro', 'aspirelist'),
            get_string('configrequiremodintro', 'aspirelist'), 0));

    // Authors in module config form.
    $settings->add(new admin_setting_configcheckbox('aspirelist/authorsinconfig', get_string('authorsinconfig', 'aspirelist'),
            get_string('configauthorsinconfig', 'aspirelist'), 0));

    // Display settings.
    $optionsdd = array();
    $optionsdd[0] = get_string('displaypage', 'aspirelist');
    $optionsdd[1] = get_string('displayinline', 'aspirelist');
    $settings->add(new admin_setting_configselect('aspirelist/defaultdisplay', get_string('defaultdisplay', 'aspirelist'),
            get_string('configdefaultdisplay', 'aspirelist'), 0, $optionsdd));

    // Aspire URL.
    $settings->add(new admin_setting_configtext('aspirelist/aspireurl', get_string('aspireurl', 'aspirelist'),
            get_string('configaspireurl', 'aspirelist'), 'http://', PARAM_URL));

    // Aspire URL HTTPS alias.
    $settings->add(new admin_setting_configtext('aspirelist/aspireurlhttpsalias', get_string('aspireurlhttpsalias', 'aspirelist'),
            get_string('configaspireurlhttpsalias', 'aspirelist'), 'https://', PARAM_URL));

    // Knowledge group.
    $optionskg = array();
    $optionskg['centers'] = get_string('center', 'aspirelist');
    $optionskg['colleges'] = get_string('college', 'aspirelist');
    $optionskg['courses'] = get_string('course', 'aspirelist');
    $optionskg['departments'] = get_string('department', 'aspirelist');
    $optionskg['divisions'] = get_string('division', 'aspirelist');
    $optionskg['faculties'] = get_string('faculty', 'aspirelist');
    $optionskg['fields'] = get_string('field', 'aspirelist');
    $optionskg['institutes'] = get_string('institute', 'aspirelist');
    $optionskg['institutions'] = get_string('institution', 'aspirelist');
    $optionskg['levels'] = get_string('level', 'aspirelist');
    $optionskg['modules'] = get_string('module', 'aspirelist');
    $optionskg['pathways'] = get_string('pathway', 'aspirelist');
    $optionskg['programmes'] = get_string('programme', 'aspirelist');
    $optionskg['schools'] = get_string('school', 'aspirelist');
    $optionskg['subjects'] = get_string('subject', 'aspirelist');
    $optionskg['units'] = get_string('unit', 'aspirelist');
    $settings->add(new admin_setting_configselect('aspirelist/knowledgegroup', get_string('knowledgegroup', 'aspirelist'),
            get_string('configknowledgegroup', 'aspirelist'), 'modules', $optionskg));

    // Code source.
    $optionscs = array();
    $optionscs['idnumber'] = get_string('idnumbercourse');
    $optionscs['shortname'] = get_string('shortnamecourse');
    $optionscs['codetable'] = get_string('codetable', 'aspirelist');
    $settings->add(new aspirelist_codesource_setting('aspirelist/codesource', get_string('codesource', 'aspirelist'),
            get_string('configcodesource', 'aspirelist'), 'idnumber', $optionscs));

    // Meta child codes.
    $settings->add(new admin_setting_configcheckbox('aspirelist/includechildcodes', get_string('includechildcodes', 'aspirelist'),
            get_string('configincludechildcodes', 'aspirelist'), 0));

    // Code regexes.
    $settings->add(new admin_setting_configtext('aspirelist/coderegex', get_string('coderegex', 'aspirelist'),
            get_string('configcoderegex', 'aspirelist'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('aspirelist/yearregex', get_string('yearregex', 'aspirelist'),
            get_string('configyearregex', 'aspirelist'), '', PARAM_TEXT));

    // Code table details.
    $settings->add(new aspirelist_codetable_setting('aspirelist/codetable', get_string('codetable', 'aspirelist'),
            get_string('configcodetable', 'aspirelist'), '', PARAM_TEXT));
    $settings->add(new aspirelist_codecolumn_setting('aspirelist/codecolumn', get_string('codecolumn', 'aspirelist'),
            get_string('configcodecolumn', 'aspirelist'), '', PARAM_TEXT));
    $settings->add(new aspirelist_coursecolumn_setting('aspirelist/coursecolumn', get_string('coursecolumn', 'aspirelist'),
            get_string('configcoursecolumn', 'aspirelist'), '', PARAM_TEXT));
    $settings->add(new aspirelist_courseattribute_setting('aspirelist/courseattribute',
            get_string('courseattribute', 'aspirelist'), get_string('configcourseattribute', 'aspirelist'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('aspirelist/kgcolumn', get_string('kgcolumn', 'aspirelist'),
            get_string('configkgcolumn', 'aspirelist'), '', PARAM_TEXT));
}
