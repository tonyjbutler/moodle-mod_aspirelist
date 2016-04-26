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
 * Aspirelist module renderer
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */
defined('MOODLE_INTERNAL') || die();

class mod_aspirelist_renderer extends plugin_renderer_base {

    /**
     * Returns html to display the content of mod_aspirelist.
     *
     * @param stdClass $aspirelist record from 'aspirelist' table
     * @return string
     */
    public function display_aspirelist(stdClass $aspirelist) {
        $output = '';
        $aspirelistinstances = get_fast_modinfo($aspirelist->course)->get_instances_of('aspirelist');
        if (!isset($aspirelistinstances[$aspirelist->id]) ||
                !($cm = $aspirelistinstances[$aspirelist->id]) ||
                !($context = context_module::instance($cm->id))) {
            // Some error in parameters.
            // Don't throw any errors in renderer, just return empty string.
            // Capability to view module must be checked before calling renderer.
            return $output;
        }

        if (trim($aspirelist->intro)) {
            if ($aspirelist->display != ASPIRELIST_DISPLAY_INLINE) {
                $output .= $this->output->box(format_module_intro('aspirelist', $aspirelist, $cm->id),
                        'generalbox', 'intro');
            } else if ($cm->showdescription) {
                // For "display inline" do not filter, filters run at display time.
                $output .= format_module_intro('aspirelist', $aspirelist, $cm->id, false);
            }
        }

        $resourcelist = new resource_list($aspirelist, $cm);
        if ($aspirelist->display == ASPIRELIST_DISPLAY_INLINE) {
            $viewlink = (string) $cm->url;
            $listid = $cm->modname . '-' . $cm->id;

            // YUI function to hide inline resource list until user clicks 'view' link.
            $this->page->requires->js_init_call('M.mod_aspirelist.init_list', array($cm->id, $viewlink));
            $output .= $this->output->box($this->render($resourcelist), 'generalbox aspirelistbox', $listid);
        } else {
            $output .= $this->output->box($this->render($resourcelist), 'generalbox', 'aspirelist');
        }

        return $output;
    }

    public function render_resource_list(resource_list $list) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/aspirelist/locallib.php');

        $aspirelist = new aspirelist($list->context, $list->cm, null);
        $output = $aspirelist->get_list_html($list->aspirelist->items);

        return $output;
    }
}

class resource_list implements renderable {
    public $context;
    public $aspirelist;
    public $cm;

    public function __construct($aspirelist, $cm) {
        $this->aspirelist = $aspirelist;
        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);
    }
}
