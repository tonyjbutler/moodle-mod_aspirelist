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
 * List of aspirelists in course
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // Course id.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_aspirelist\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$straspirelist       = get_string('modulename', 'aspirelist');
$straspirelists      = get_string('modulenameplural', 'aspirelist');
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/aspirelist/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$straspirelists);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($straspirelists);
echo $OUTPUT->header();

if (!$aspirelists = get_all_instances_in_course('aspirelist', $course)) {
    notice(get_string('thereareno', 'moodle', $straspirelists), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($aspirelists as $aspirelist) {
    $cm = $modinfo->cms[$aspirelist->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($aspirelist->section !== $currentsection) {
            if ($aspirelist->section) {
                $printsection = get_section_name($course, $aspirelist->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $aspirelist->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($aspirelist->timemodified)."</span>";
    }

    $class = $aspirelist->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.
    $table->data[] = array (
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">".format_string($aspirelist->name)."</a>",
        format_module_intro('aspirelist', $aspirelist, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
