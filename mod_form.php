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
 * Aspirelist configuration form
 *
 * @package    mod_aspirelist
 * @copyright  2014 Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/aspirelist/locallib.php');

/**
 * Settings form for the aspirelist module
 *
 * @copyright 2014 Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_aspirelist_mod_form extends moodleform_mod {

    /** @var aspirelist The standard base class for mod_aspirelist */
    private $aspirelist;

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $CFG, $DB, $OUTPUT;
        $mform = $this->_form;

        $ctx = null;
        if ($this->current && $this->current->coursemodule) {
            $cm = get_coursemodule_from_instance('aspirelist', $this->current->id, 0, false, MUST_EXIST);
            $ctx = context_module::instance($cm->id);
        }
        $this->aspirelist = new aspirelist($ctx, null, null);
        if ($this->current && $this->current->course) {
            if (!$ctx) {
                $ctx = context_course::instance($this->current->course);
            }
            $course = $DB->get_record('course', array('id'=>$this->current->course), '*', MUST_EXIST);
            $this->aspirelist->set_course($course);
        }

        $config = get_config('aspirelist');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('aspirelistname', 'aspirelist'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        $mform->addElement('header', 'content', get_string('contentheader', 'aspirelist'));
        $mform->setExpanded('content', true);

        if ($this->aspirelist->test_connection()) {
            if ($lists = $this->aspirelist->get_lists($course)) {
                $this->setup_list_elements($mform, $lists);
            } else {
                $module = rtrim(strtolower($config->knowledgegroup), 's');
                $noaspirelists = $OUTPUT->heading(get_string('noaspirelists', 'aspirelist', $module), 3, 'warning');
                $mform->addElement('html', $noaspirelists);
            }
        } else {
            $noconnection = $OUTPUT->heading(get_string('noconnection', 'aspirelist'), 3, 'warning');
            $mform->addElement('html', $noconnection);
        }

        //-------------------------------------------------------
        $mform->addElement('header', 'appearance', get_string('appearance'));
        $mform->setExpanded('appearance', true);

        $mform->addElement('select', 'display', get_string('display', 'aspirelist'),
                array(ASPIRELIST_DISPLAY_PAGE => get_string('displaypage', 'aspirelist'),
                    ASPIRELIST_DISPLAY_INLINE => get_string('displayinline', 'aspirelist')));
        $mform->addHelpButton('display', 'display', 'aspirelist');
        $mform->setDefault('display', $config->defaultdisplay);

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    private function setup_list_elements(&$mform, $lists) {
        $checkboxgrp = 1;
        $wassection = true;

        foreach ($lists as $list) {
            $selectresources = html_writer::div(get_string('selectresources', 'aspirelist', $list->name), 'selectresources');
            $mform->addElement('html', $selectresources);

            // Get DOM node list for top level sections and list items.
            $listnodes = $this->aspirelist->get_list_nodes($list->xpath);
            foreach ($listnodes as $listnode) {
                if ($this->aspirelist->is_section($list->xpath, $listnode)) {
                    // This is a section, so fetch its data and set up its elements.
                    $section = $this->aspirelist->get_section_data($list->xpath, $listnode, null, 'list-' . $list->id, true);
                    $this->setup_section_elements($mform, $checkboxgrp, $list->xpath, $section);
                } else {
                    // This is a resource list item, so fetch its data and set up its elements.
                    $listitem = $this->aspirelist->get_item_data($list->xpath, $listnode, null, 'list-' . $list->id);
                    if ($listitem) {
                        if ($wassection) {
                            // If this is the first list item in a section, add a checkbox controller.
                            $this->add_checkbox_controller($checkboxgrp, null, null, 0);
                            // Increment checkbox group for next section.
                            $checkboxgrp++;
                        }
                        $this->setup_item_elements($mform, $checkboxgrp, $listitem);
                        // Remember that this was not a section heading.
                        $wassection = false;
                    }
                }
            }
            unset($listnodes);
        }
        unset($lists);
    }

    private function setup_section_elements(&$mform, &$checkboxgrp, $xpath, $section, $headinglevel = 3) {
        global $OUTPUT;

        $countspan = html_writer::tag('span', '(' . $section->itemcount . ')', array('class' => 'itemcount dimmed_text'));
        $heading = $OUTPUT->heading($section->name . ' ' . $countspan, $headinglevel, 'sectionheading', $section->id);
        $mform->addElement('html', $heading . $section->note);

        // Don't let heading level exceed 6.
        $headinglevel = $headinglevel <= 6 ? $headinglevel : 6;

        // Remember that this was a section heading.
        $wassection = true;

        foreach ($section->items as $sectionitem) {
            if ($this->aspirelist->is_section($xpath, $sectionitem)) {
                // This is a sub-section, so fetch its data and set up its elements.
                $subsection = $this->aspirelist->get_section_data($xpath, $sectionitem, null, $section->path, true);
                $this->setup_section_elements($mform, $checkboxgrp, $xpath, $subsection, $headinglevel + 1);
            } else {
                // This is a resource list item, so fetch its data and set up its elements.
                $listitem = $this->aspirelist->get_item_data($xpath, $sectionitem, null, $section->path);
                if ($listitem) {
                    if ($wassection) {
                        // If this is the first list item in a section, add a checkbox controller.
                        $this->add_checkbox_controller($checkboxgrp, null, null, 0);
                        // Increment checkbox group for next section.
                        $checkboxgrp++;
                    }
                    $this->setup_item_elements($mform, $checkboxgrp, $listitem);
                    // Remember that this was not a section heading.
                    $wassection = false;
                }
            }
        }
        unset($section->items);
    }

    private function setup_item_elements(&$mform, &$checkboxgrp, $item) {
        // Pre-select previously selected list items if this is an update.
        if ($config = $this->aspirelist->get_instance_config()) {
            $items = explode(',', $config);
            if (in_array($item->path, $items)) {
                $default = 1;
            } else {
                $default = 0;
            }
        } else {
            $default = 0;
        }

        $mform->addElement('advcheckbox', $item->path, $item->link . $item->formats, null, array('group' => $checkboxgrp - 1));
        $mform->setDefault($item->path, $default);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Automatic on-view completion cannot work together with 'display inline' option.
        if (empty($errors['completion']) &&
                array_key_exists('completion', $data) &&
                $data['completion'] == COMPLETION_TRACKING_AUTOMATIC &&
                !empty($data['completionview']) &&
                $data['display'] == ASPIRELIST_DISPLAY_INLINE) {
            $errors['completion'] = get_string('noautocompletioninline', 'aspirelist');
        }

        return $errors;
    }
}
