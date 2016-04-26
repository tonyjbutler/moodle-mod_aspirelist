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
 * Mandatory public API of aspirelist module
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */

defined('MOODLE_INTERNAL') || die();

/** Display aspirelist contents on a separate page */
define('ASPIRELIST_DISPLAY_PAGE', 0);
/** Display aspirelist contents inline in a course */
define('ASPIRELIST_DISPLAY_INLINE', 1);

/**
 * List of features supported in Aspirelist module.
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function aspirelist_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * Returns all other caps used in module.
 * @return array
 */
function aspirelist_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course
 * @return array status array
 */
function aspirelist_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions.
 * @return array
 */
function aspirelist_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions.
 * @return array
 */
function aspirelist_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add aspirelist instance.
 * @param object $data
 * @param object $mform
 * @return int new aspirelist instance id
 */
function aspirelist_add_instance($data, $mform) {
    global $DB;

    $cmid = $data->coursemodule;
    $aspirelist = new aspirelist(context_module::instance($cmid), null, null);

    $data->timemodified = time();
    $data->items = $aspirelist->get_items_list($data);
    $data->id = $DB->insert_record('aspirelist', $data);

    $DB->set_field('course_modules', 'instance', $data->id, array('id' => $cmid));

    return $data->id;
}

/**
 * Update aspirelist instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function aspirelist_update_instance($data, $mform) {
    global $DB;

    $context = context_module::instance($data->coursemodule);
    $aspirelist = new aspirelist($context, null, null);

    $data->id = $data->instance;
    $data->timemodified = time();
    $data->items = $aspirelist->get_items_list($data);

    $DB->update_record('aspirelist', $data);

    return true;
}

/**
 * Delete aspirelist instance.
 * @param int $id
 * @return bool true
 */
function aspirelist_delete_instance($id) {
    global $DB;

    if (!$aspirelist = $DB->get_record('aspirelist', array('id' => $id))) {
        return false;
    }

    // Note: all context files are deleted automatically.

    $DB->delete_records('aspirelist', array('id' => $aspirelist->id));

    return true;
}

/**
 * Return a list of page types.
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function aspirelist_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array('mod-aspirelist-*' => get_string('page-mod-aspirelist-x', 'aspirelist'));
    return $modulepagetype;
}

/**
 * Given a coursemodule object, this function returns the extra
 * information needed to print this activity in various places.
 *
 * If aspirelist needs to be displayed inline we store additional information
 * in customdata, so functions {@link aspirelist_cm_info_dynamic()} and
 * {@link aspirelist_cm_info_view()} do not need to do DB queries.
 *
 * @param cm_info $cm
 * @return cached_cm_info info
 */
function aspirelist_get_coursemodule_info($cm) {
    global $DB;

    if (!($aspirelist = $DB->get_record('aspirelist', array('id' => $cm->instance),
            'id, name, intro, introformat, display, items'))) {
        return null;
    }
    $cminfo = new cached_cm_info();
    $cminfo->name = $aspirelist->name;
    if ($aspirelist->display == ASPIRELIST_DISPLAY_INLINE) {
        // Prepare aspirelist object to store in customdata.
        $fdata = new stdClass();
        if ($cm->showdescription && strlen(trim($aspirelist->intro))) {
            $fdata->intro = $aspirelist->intro;
            if ($aspirelist->introformat != FORMAT_MOODLE) {
                $fdata->introformat = $aspirelist->introformat;
            }
        }
        $fdata->items = $aspirelist->items;
        $cminfo->customdata = $fdata;
    } else {
        if ($cm->showdescription) {
            // Convert intro to html. Do not filter cached version, filters run at display time.
            $cminfo->content = format_module_intro('aspirelist', $aspirelist, $cm->id, false);
        }
    }
    return $cminfo;
}

/**
 * Sets dynamic information about a course module.
 *
 * This function is called from cm_info when displaying the module.
 * mod_aspirelist can be displayed inline on course page and therefore have no course link.
 *
 * @param cm_info $cm
 */
function aspirelist_cm_info_dynamic(cm_info $cm) {
    if ($cm->customdata) {
        // The field 'customdata' is not empty IF AND ONLY IF we display contents inline.
        $cm->set_on_click('return false;');

        // Display a visual cue to users that clicking the link toggles visibility.
        $showhidearrow = html_writer::div('', 'showhidearrow', array('id' => 'showhide-' . $cm->id,
            'title' => get_string('showhide', 'aspirelist')));
        $showhidelink = html_writer::link($cm->url, $showhidearrow, array('onclick' => 'return false;'));
        $cm->set_after_link($showhidelink);
    }
}

/**
 * Overwrites the content in the course-module object with the aspirelist content
 * if aspirelist.display == ASPIRELIST_DISPLAY_INLINE.
 *
 * @param cm_info $cm
 */
function aspirelist_cm_info_view(cm_info $cm) {
    global $PAGE;

    if ($cm->uservisible && $cm->customdata && has_capability('mod/aspirelist:view', $cm->context)) {
        // Restore aspirelist object from customdata.
        // Note the field 'customdata' is not empty IF AND ONLY IF we display contents inline.
        // Otherwise the content is default.
        $aspirelist = $cm->customdata;
        $aspirelist->id = (int)$cm->instance;
        $aspirelist->course = (int)$cm->course;
        $aspirelist->display = ASPIRELIST_DISPLAY_INLINE;
        $aspirelist->name = $cm->name;
        if (empty($aspirelist->intro)) {
            $aspirelist->intro = '';
        }
        if (empty($aspirelist->introformat)) {
            $aspirelist->introformat = FORMAT_MOODLE;
        }
        // Display aspirelist.
        $renderer = $PAGE->get_renderer('mod_aspirelist');
        $cm->set_content($renderer->display_aspirelist($aspirelist));
    }
}
