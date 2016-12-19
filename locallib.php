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
 * Private aspirelist module utility functions
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/aspirelist/lib.php');

/**
 * Standard base class for mod_aspirelist.
 *
 * @package    mod_aspirelist
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */
class aspirelist {

    /** @var stdClass the aspirelist record that contains the global settings for this aspirelist instance */
    private $instance;

    /** @var context the context of the course module for this aspirelist instance
     *               (or just the course if we are creating a new one)
     */
    private $context;

    /** @var stdClass the course this aspirelist instance belongs to */
    private $course;

    /** @var stdClass the admin config for all aspirelist instances  */
    private $adminconfig;

    /** @var aspirelist_renderer the custom renderer for this module */
    private $output;

    /** @var stdClass the course module for this aspirelist instance */
    private $coursemodule;

    /** @var array cache for things like the coursemodule name or the scale menu -
     *             only lives for a single request.
     */
    private $cache;

    /** @var string action to be used to return to this page
     *              (without repeating any form submissions etc).
     */
    private $returnaction = 'view';

    /** @var array params to be used to return to this page */
    private $returnparams = array();

    /** @var string modulename prevents excessive calls to get_string */
    private static $modulename = null;

    /** @var string modulenameplural prevents excessive calls to get_string */
    private static $modulenameplural = null;

    /** @var array list of suspended user IDs in form of ([id1] => id1) */
    public $susers = null;

    /** @var string Regular expression matching an aspirelist section ID */
    private $sectionidregex = '/^section-[A-F0-9\-]{36}$/';

    /** @var string Regular expression matching an aspirelist item ID */
    private $itemidregex = '/^item-[A-F0-9\-]{36}$/';

    /**
     * Constructor for the base aspirelist class.
     *
     * @param mixed $coursemodulecontext context|null the course module context
     *                                   (or the course context if the coursemodule has not been
     *                                   created yet).
     * @param mixed $coursemodule the current course module if it was already loaded,
     *                            otherwise this class will load one from the context as required.
     * @param mixed $course the current course  if it was already loaded,
     *                      otherwise this class will load one from the context as required.
     */
    public function __construct($coursemodulecontext, $coursemodule, $course) {
        $this->context = $coursemodulecontext;
        $this->coursemodule = $coursemodule;
        $this->course = $course;

        // Temporary cache only lives for a single request - used to reduce db lookups.
        $this->cache = array();

    }

    /**
     * Set the action and parameters that can be used to return to the current page.
     *
     * @param string $action The action for the current page
     * @param array $params An array of name value pairs which form the parameters
     *                      to return to the current page.
     * @return void
     */
    public function register_return_link($action, $params) {
        global $PAGE;

        $params['action'] = $action;
        $currenturl = $PAGE->url;

        $currenturl->params($params);
        $PAGE->set_url($currenturl);
    }

    /**
     * Return an action that can be used to get back to the current page.
     *
     * @return string action
     */
    public function get_return_action() {
        global $PAGE;

        $params = $PAGE->url->params();

        if (!empty($params['action'])) {
            return $params['action'];
        }
        return '';
    }

    /**
     * Return a list of parameters that can be used to get back to the current page.
     *
     * @return array params
     */
    public function get_return_params() {
        global $PAGE;

        $params = $PAGE->url->params();
        unset($params['id']);
        unset($params['action']);
        return $params;
    }

    /**
     * Set the submitted form data.
     *
     * @param stdClass $data The form data (instance)
     */
    public function set_instance(stdClass $data) {
        $this->instance = $data;
    }

    /**
     * Set the context.
     *
     * @param context $context The new context
     */
    public function set_context(context $context) {
        $this->context = $context;
    }

    /**
     * Set the course data.
     *
     * @param stdClass $course The course data
     */
    public function set_course(stdClass $course) {
        $this->course = $course;
    }

    /**
     * Has this aspirelist been constructed from an instance?
     *
     * @return bool
     */
    public function has_instance() {
        return $this->instance || $this->get_course_module();
    }

    /**
     * Get the settings for the current instance of this aspirelist
     *
     * @return stdClass The settings
     */
    public function get_instance() {
        global $DB;
        if ($this->instance) {
            return $this->instance;
        }
        if ($this->get_course_module()) {
            $params = array('id' => $this->get_course_module()->instance);
            $this->instance = $DB->get_record('aspirelist', $params, '*', MUST_EXIST);
        }
        if (!$this->instance) {
            throw new coding_exception('Improper use of the aspirelist class. ' .
                                       'Cannot load the aspirelist record.');
        }
        return $this->instance;
    }

    /**
     * Get the context of the current course.
     *
     * @return mixed context|null The course context
     */
    public function get_course_context() {
        if (!$this->context && !$this->course) {
            throw new coding_exception('Improper use of the aspirelist class. ' .
                                       'Cannot load the course context.');
        }
        if ($this->context) {
            return $this->context->get_course_context();
        } else {
            return context_course::instance($this->course->id);
        }
    }


    /**
     * Get the current course module.
     *
     * @return mixed stdClass|null The course module
     */
    public function get_course_module() {
        if ($this->coursemodule) {
            return $this->coursemodule;
        }
        if (!$this->context) {
            return null;
        }

        if ($this->context->contextlevel == CONTEXT_MODULE) {
            $this->coursemodule = get_coursemodule_from_id('aspirelist', $this->context->instanceid, 0, false, MUST_EXIST);
            return $this->coursemodule;
        }
        return null;
    }

    /**
     * Get context module.
     *
     * @return context
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * Get the current course.
     *
     * @return mixed stdClass|null The course
     */
    public function get_course() {
        global $DB;

        if ($this->course) {
            return $this->course;
        }

        if (!$this->context) {
            return null;
        }
        $params = array('id' => $this->get_course_context()->instanceid);
        $this->course = $DB->get_record('course', $params, '*', MUST_EXIST);

        return $this->course;
    }

    /**
     * Get the name of the current module.
     *
     * @return string the module name (Aspire resource list)
     */
    protected function get_module_name() {
        if (isset(self::$modulename)) {
            return self::$modulename;
        }
        self::$modulename = get_string('modulename', 'aspirelist');
        return self::$modulename;
    }

    /**
     * Get the plural name of the current module.
     *
     * @return string the module name plural (Aspire resource lists)
     */
    protected function get_module_name_plural() {
        if (isset(self::$modulenameplural)) {
            return self::$modulenameplural;
        }
        self::$modulenameplural = get_string('modulenameplural', 'aspirelist');
        return self::$modulenameplural;
    }

    /**
     * View a link to go back to the previous page. Uses url parameters returnaction and returnparams.
     *
     * @return string
     */
    protected function view_return_links() {
        $returnaction = optional_param('returnaction', '', PARAM_ALPHA);
        $returnparams = optional_param('returnparams', '', PARAM_TEXT);

        $params = array();
        $returnparams = str_replace('&amp;', '&', $returnparams);
        parse_str($returnparams, $params);
        $newparams = array('id' => $this->get_course_module()->id, 'action' => $returnaction);
        $params = array_merge($newparams, $params);

        $url = new moodle_url('/mod/aspirelist/view.php', $params);
        return $this->get_renderer()->single_button($url, get_string('back'), 'get');
    }

    /**
     * Load and cache the admin config for this module.
     *
     * @return stdClass the plugin config
     */
    public function get_admin_config() {
        global $CFG;

        if ($this->adminconfig) {
            return $this->adminconfig;
        }
        $this->adminconfig = get_config('aspirelist');

        // Clean up Aspire URL if necessary.
        $baseurl = trim(str_ireplace(array('http://', 'https://'), '', $this->adminconfig->aspireurl), '/');
        $slashpos = strpos($baseurl, '/');
        if ($slashpos !== false) {
            $baseurl = substr_replace($baseurl, '', $slashpos);
        }
        $this->adminconfig->aspireurl = 'http://' . $baseurl;

        // Clean up Aspire URL HTTPS alias if necessary.
        if (!empty($this->adminconfig->aspireurlhttpsalias) && $this->adminconfig->aspireurlhttpsalias != 'https//') {
            $basealias = trim(str_ireplace(array('http://', 'https://'), '', $this->adminconfig->aspireurlhttpsalias), '/');
            $slashpos = strpos($basealias, '/');
            if ($slashpos !== false) {
                $basealias = substr_replace($basealias, '', $slashpos);
            }
            $this->adminconfig->aspireurlhttpsalias = 'https://' . $basealias;
        } else {
            $this->adminconfig->aspireurlhttpsalias = '';
        }

        // Remove database prefix from Aspire code table name if present.
        if (isset($this->adminconfig->codetable)) {
            $this->adminconfig->codetable = str_replace($CFG->prefix, '', $this->adminconfig->codetable);
        }

        return $this->adminconfig;
    }

    /**
     * Check that we can connect to the Talis Aspire server.
     *
     * @return boolean True if we can connect, else false
     */
    public function test_connection() {
        $adminconfig = $this->get_admin_config();

        if (!$aspirehost = str_replace('http://', '', $adminconfig->aspireurl)) {
            mtrace('Error: config setting "aspirelist | aspireurl" empty or misconfigured<br>');
        }

        if ($connection = @fsockopen($aspirehost, 80, $errno, $errstr)) {
            fclose($connection);
            return true;
        }

        mtrace('Error "' . $errno . ': ' . $errstr . '" encountered when attempting to connect to host ' . $aspirehost);

        return false;
    }

    /**
     * Fetch the previously selected resource list items for the current aspirelist instance.
     *
     * @return mixed string|null A comma separated list of resource item IDs
     */
    public function get_instance_config() {
        global $DB;

        $coursemodule = $this->get_course_module();

        if ($coursemodule) {
            $config = $DB->get_field('aspirelist', 'items', array('id' => $coursemodule->instance));
            return $config;
        } else {
            return null;
        }
    }

    /**
     * Fetch all Talis Aspire list codes associated with a course.
     *
     * @param stdClass $course The data object for the course
     * @param bool $child Whether or not this is a meta child course
     * @return array An array of Talis Aspire list codes
     */
    private function get_codes($course, $child = false) {
        global $DB;

        $adminconfig = $this->get_admin_config();

        if ($adminconfig->codesource == 'codetable') {
            $codetable = $adminconfig->codetable;
            $codecolumn = $adminconfig->codecolumn;
            $coursecolumn = $adminconfig->coursecolumn;
            $courseattribute = $course->{$adminconfig->courseattribute};

            $codes = array();
            if ($records = $DB->get_records($codetable, array($coursecolumn => $courseattribute))) {
                foreach ($records as $index => $record) {
                    $codes[$index]['code'] = $record->$codecolumn;
                    if ($kgcolumn = $adminconfig->kgcolumn and !empty($record->$kgcolumn)) {
                        $codes[$index]['kg'] = $record->$kgcolumn;
                    }
                }
            }
        } else if ($adminconfig->codesource == 'shortname') {
            $codes = $this->extract_codes($course->shortname);
        }

        // Try ID number as fallback if no code found so far, regardless of code source specified in admin config.
        if ($adminconfig->codesource == 'idnumber' || empty($codes)) {
            $codes = $this->extract_codes($course->idnumber);
        }

        // Check for additional codes in meta child courses (if enabled in site config).
        if ($adminconfig->includechildcodes && !$child) {
            if ($childcourses = $this->get_child_courses($course->id)) {
                foreach ($childcourses as $childcourse) {
                    $codes = array_merge($codes, $this->get_codes($childcourse, true));
                }
            }
        }

        return array_filter($codes);
    }

    /**
     * Extract one or more Talis Aspire list codes from a given source string.
     *
     * @param string $source A string containing one or more codes
     * @return array An array of Aspire list codes
     */
    private function extract_codes($source) {
        $adminconfig = $this->get_admin_config();

        if ($coderegex = $adminconfig->coderegex) {
            preg_match_all($coderegex, $source, $codes, PREG_PATTERN_ORDER);
            $codes = (!empty($codes[1])) ? $codes[1] : $codes[0];
        } else {
            $codes = array($source);
        }
        $codes = array_unique($codes);

        return array_map(create_function('$code', 'return array(\'code\' => $code);'), $codes);
    }

    /**
     * Determine whether the current course has any course meta link enrolment instances,
     * and if it does, fetch the child courses.
     *
     * @param int $courseid The ID of the current course
     * @return array An array of meta child course objects
     */
    private function get_child_courses($courseid) {
        global $DB;

        $childcourses = array();
        $select = "enrol = 'meta' AND status = 0 AND courseid = $courseid";

        if ($childcourseids = $DB->get_fieldset_select('enrol', 'customint1', $select)) {
            foreach ($childcourseids as $childcourseid) {
                $childcourses[] = get_course($childcourseid);
            }
        }

        return $childcourses;
    }

    /**
     * Return the pluralised version of a given knowledge group name (as used in Aspire URLs),
     * or that of the admin configured default knowledge group if not valid.
     *
     * @param string $name Official name of an Aspire knowledge group
     * @return string Pluralised name as used in Aspire URLs
     */
    private function get_url_knowledge_group($name) {
        $adminconfig = $this->get_admin_config();
        $name = strtolower($name);
        $knowledgegroups = array(
            'center'      => 'centers',
            'college'     => 'colleges',
            'course'      => 'courses',
            'department'  => 'departments',
            'division'    => 'divisions',
            'faculty'     => 'faculties',
            'field'       => 'fields',
            'institute'   => 'institutes',
            'institution' => 'institutions',
            'level'       => 'levels',
            'module'      => 'modules',
            'pathway'     => 'pathways',
            'programme'   => 'programmes',
            'school'      => 'schools',
            'subject'     => 'subjects',
            'unit'        => 'units'
        );

        if (!in_array($name, array_keys($knowledgegroups))) {
            return $adminconfig->knowledgegroup;
        }

        return $knowledgegroups[$name];
    }

    /**
     * Fetch all Talis Aspire lists associated with the current course (and year if applicable).
     *
     * @param stdClass $course The data for the current course
     * @return array An array of Talis Aspire resource list objects
     */
    public function get_lists($course) {
        $adminconfig = $this->get_admin_config();
        $codes = $this->get_codes($course);

        // Check if the course idnumber or shortname contains a year reference.
        $year = false;
        if ($yearregex = $adminconfig->yearregex) {
            if (preg_match($yearregex, $course->idnumber, $year) || preg_match($yearregex, $course->shortname, $year)) {
                $year = (!empty($year[1])) ? $year[1] : $year[0];
            }
        }

        $lists = array();

        foreach ($codes as $code) {
            $knowledgegroup = isset($code['kg']) ? $this->get_url_knowledge_group($code['kg']) : $adminconfig->knowledgegroup;

            // Build the URL for the JSON request.
            $codedata = $adminconfig->aspireurl . '/' . $knowledgegroup . '/' . strtolower($code['code']);
            if ($year) {
                $url = $codedata . '/lists/' . $year . '.json';
            } else {
                $url = $codedata . '/lists.json';
            }

            $curl = new curl(array('cache' => 'true', 'module_cache' => 'mod_aspirelist'));
            $curl->setopt(array('CURLOPT_TIMEOUT' => 30));
            if ($response = $curl->get($url)) {
                $data = json_decode($response, true);
                if (isset($data[$codedata]) && isset($data[$codedata]['http://purl.org/vocab/resourcelist/schema#usesList'])) {
                    // Some lists were returned.
                    foreach ($data[$codedata]['http://purl.org/vocab/resourcelist/schema#usesList'] as $useslist) {
                        $listid = substr($useslist['value'], -36);
                        $list = $this->get_list_data($listid);
                        if (isset($list->name)) {
                            $lists[$list->name] = $list;
                        }
                    }
                    unset($data);
                }
            }
        }
        unset($codes);

        // Sort the lists by name.
        $keys = array_map(create_function('$a', 'return strtolower($a->name);'), $lists);
        array_multisort($keys, SORT_ASC, SORT_STRING, $lists);

        return $lists;
    }

    /**
     * Fetch the name and contents of the specified Talis Aspire list.
     *
     * @param string $listid The GUID of the required Talis list
     * @param bool $cached Whether to return cached data if available
     * @return stdClass An object containing the list's data
     */
    private function get_list_data($listid, $cached = false) {
        $list = new stdClass();

        $list->id = $listid;

        if ($list->xpath = $this->get_xpath($listid, $cached)) {
            $namequery = '//h1[@id = "pageTitle"]';
            // We only want the main text content of the h1, not its sub-elements.
            $list->name = trim($this->get_dom_nodelist($list->xpath, $namequery, null, true)->firstChild->textContent);
        }

        return $list;
    }

    /**
     * Create a DOMXPath object from the resource list with the given ID.
     *
     * @param string $id The GUID of a Talis Aspire resource list
     * @param bool $cached Whether to return cached data if available
     * @return DOMXPath A document object suitable for querying with XPath
     */
    private function get_xpath($id, $cached = false) {
        $adminconfig = $this->get_admin_config();
        $url = $adminconfig->aspireurl . '/lists/' . $id . '.html';

        // Create a cache object to store list data.
        $cache = cache::make('mod_aspirelist', 'listdata');

        $doc = new DOMDocument();
        if (!$cached || (!$list = $cache->get($id))) {
            if (@$doc->loadHTMLFile($url)) {
                $list = $doc->saveHTML();
                $cache->set($id, $list);
            } else if (!$list = $cache->get($id)) {
                return null;
            }
        }
        @$doc->loadHTML($list);

        return new DOMXPath($doc);
    }

    /**
     * Fetch a list of DOM nodes based on the execution of the given XPath query.
     * Can also return a single DOM node or a node value.
     *
     * @param DOMXPath $xpath A DOMXPath document object
     * @param string $query The XPath expression to evaluate
     * @param DOMNode $contextnode If supplied, queries will be executed relative to this node (otherwise root)
     * @param bool $singlenode Return a single node instead of a list (but only if the list contains only one node,
     *                         otherwise null will be returned)
     * @param bool $nodevalue Return a single value instead of a node ($singlenode must be specified too)
     * @return mixed DOMNodeList|DOMNode|string|null An object containing the DOM node(s), or a string value
     */
    private function get_dom_nodelist($xpath, $query, $contextnode = null, $singlenode = false, $nodevalue = false) {
        if ($xpath) {
            $nodelist = $xpath->query($query, $contextnode);

            if ($singlenode) {
                if ($nodelist->length == 1) {
                    if ($nodevalue) {
                        return $nodelist->item(0)->nodeValue;
                    } else {
                        return $nodelist->item(0);
                    }
                } else {
                    return null;
                }
            } else {
                return $nodelist;
            }
        }

        return null;
    }

    /**
     * Fetch a list of DOM nodes for the top level sections of a given Talis Aspire list.
     *
     * @param DOMXPath $xpath A DOMXPath document object representing the list
     * @return DOMNodeList An object containing the DOM nodes for the top level sections
     */
    public function get_list_nodes($xpath) {
        $sectionsquery = '//ol[contains(@id, "listEntries")]/li[contains(@class, "section")]' .
                ' | //ol[contains(@id, "listEntries")]/li[contains(@class, "item")]';
        $sectionnodes = $this->get_dom_nodelist($xpath, $sectionsquery);
        return $sectionnodes;
    }

    /**
     * Determine whether or not a given section item is itself a section.
     *
     * @param DOMXPath $xpath A DOMXPath document object representing the list
     * @param DOMNode $node The DOM node of the section item being tested
     * @return bool True if section item is a sub-section, otherwise false
     */
    public function is_section($xpath, $node) {
        $query = 'ol[contains(@class, "sectionItems")]/li';
        $subnodes = $this->get_dom_nodelist($xpath, $query, $node);

        if ($subnodes->length > 0) { // Sections contain section items!
            return true;
        } else {
            return false;
        }
    }

    /**
     * Fetch the data for a Talis Aspire resource list section, given the list's DOMXPath
     * document object, and either the section's DOM node or the section ID.
     *
     * @param DOMXPath $xpath A DOMXPath document object representing the list
     * @param DOMNode $sectionnode The DOM node of the required section
     * @param string $sectionid The ID of the required section
     * @param string $parentpath A path comprising the list ID and any parent section IDs
     * @param bool $getitems Return data on the resource list items contained in the section
     * @return stdClass An object containing the section data
     */
    public function get_section_data($xpath, $sectionnode = null, $sectionid = null, $parentpath = null, $getitems = false) {
        $section = new stdClass();

        $section->id = $sectionid ? $sectionid : $sectionnode->getAttribute('id');

        if (!$sectionnode) {
            $nodequery = '//li[@id = "' . $section->id . '"]';
            $sectionnode = $this->get_dom_nodelist($xpath, $nodequery, null, true);
        }

        if ($parentpath) {
            $section->path = $parentpath . '_' . $section->id;
        }

        $detailsquery = 'div[contains(@class, "sectionDetails")]/div';

        if ($sectiondetails = $this->get_dom_nodelist($xpath, $detailsquery, $sectionnode, true)) {

            $namequery = 'span[contains(@class, "name")]';
            $section->name = trim($this->get_dom_nodelist($xpath, $namequery, $sectiondetails, true, true));

            $countquery = 'span[contains(@class, "itemCount")]';
            $section->itemcount = $this->get_dom_nodelist($xpath, $countquery, $sectiondetails, true, true);

            $notequery = 'div[contains(@class, "sectionNote")]';
            $sectionnote = $this->get_dom_nodelist($xpath, $notequery, $sectionnode, true, true);
            if ($sectionnote) {
                $section->note = html_writer::div($sectionnote, 'sectionnote');
            } else {
                $section->note = '';
            }

            if ($getitems) {
                $itemsquery = 'ol[contains(@class, "sectionItems")]/li';
                $section->items = $this->get_dom_nodelist($xpath, $itemsquery, $sectionnode);
            }

            return $section;
        }

        return null;
    }

    /**
     * Fetch the data for a Talis Aspire resource list item, given the list's DOMXPath
     * document object, and either the item's DOM node or the item ID.
     *
     * @param DOMXPath $xpath A DOMXPath document object representing the list
     * @param DOMNode $itemnode The DOM node of the required item
     * @param string $itemid The ID of the required item
     * @param string $parentpath A path comprising the list ID and all parent section IDs
     * @return stdClass An object containing the item data
     */
    public function get_item_data($xpath, $itemnode = null, $itemid = null, $parentpath = null) {
        global $OUTPUT;

        $adminconfig = $this->get_admin_config();
        $item = new stdClass();

        $item->id = $itemid ? $itemid : $itemnode->getAttribute('id');

        if (!$itemnode) {
            $nodequery = '//li[@id = "' . $item->id . '"]';
            $itemnode = $this->get_dom_nodelist($xpath, $nodequery, null, true);
        }

        if ($parentpath) {
            $item->path = $parentpath . '_' . $item->id;
        }

        $detailsquery = 'div[contains(@class, "outlineItem")]/div/div/p[contains(@class, "itemBibData")]';

        if ($itemdetails = $this->get_dom_nodelist($xpath, $detailsquery, $itemnode, true)) {
            $linkquery = 'a[contains(@class, "itemLink")]';
            $link = $this->get_dom_nodelist($xpath, $linkquery, $itemdetails, true);
            $item->name = $link->nodeValue;
            $item->href = $link->getAttribute('href');
            $itemtitle = get_string('previewitem', 'aspirelist');

            $linkaction = new popup_action('click', $item->href, 'popup', array('width' => 1024, 'height' => 768));
            $item->link = $OUTPUT->action_link($item->href, $item->name, $linkaction, array('id' => $item->id,
                'class' => 'itemlink', 'title' => $itemtitle));

            $authors = $this->get_authors($xpath, $itemdetails);
            if ($authors) {
                $item->authors = ' ' . html_writer::tag('span', $authors, array('class' => 'itemauthors'));
            } else {
                $item->authors = '';
            }

            $datequery = 'span[contains(@class, "publishedDate")]';
            $publisheddate = $this->get_dom_nodelist($xpath, $datequery, $itemdetails, true, true);
            if ($publisheddate) {
                $item->published = ' ' . html_writer::tag('span', $publisheddate, array('class' => 'itempublished'));
            } else {
                $item->published = '';
            }

            $formats = $this->get_formats($xpath, $itemdetails);
            if ($formats) {
                $item->formats = ' ' . html_writer::tag('span', '(' . $formats . ')', array('class' => 'itemformats'));
            } else {
                $item->formats = '';
            }

            $typequery = '../p/span[contains(@class, "resourceType")]';
            $resourcetype = $this->get_dom_nodelist($xpath, $typequery, $itemdetails, true, true);
            if ($resourcetype) {
                $item->resourcetype = html_writer::tag('span', $resourcetype, array('class' => 'resourcetype'));
            } else {
                $item->resourcetype = '';
            }

            $importance = $this->get_dom_nodelist($xpath, '../p/strong', $itemdetails, true, true);
            if ($importance) {
                $item->importance = ' ' . html_writer::tag('span', $importance, array('class' => 'itemimportance'));
            } else {
                $item->importance = '';
            }

            $studynotequery = '../p/span[contains(@class, "itemStudyNote")]';
            $itemstudynote = $this->get_dom_nodelist($xpath, $studynotequery, $itemdetails, true, true);
            if ($itemstudynote) {
                $item->studynote = ' ' . html_writer::tag('span', $itemstudynote, array('class' => 'itemstudynote'));
            } else {
                $item->studynote = '';
            }

            $buttonquery = '../../div/div[contains(@class, "item-actions")]/div/div/p/a[contains(@class, "btnWebLink")]';
            $webbutton = $this->get_dom_nodelist($xpath, $buttonquery, $itemdetails, true);
            if ($webbutton) {
                $buttonlabel = get_string('onlineresource', 'aspirelist');
                $buttonhref = $webbutton->getAttribute('href');
                if (strpos($buttonhref, '/') === 0) {
                    $buttonhref = $adminconfig->aspireurl . $buttonhref;
                }
                $buttontitle = $item->name;
                $buttonaction = new popup_action('click', $buttonhref, 'popup', array('width' => 1024, 'height' => 768));
                $item->webbutton = $OUTPUT->action_link($buttonhref, $buttonlabel, $buttonaction,
                        array('class' => 'webbutton', 'title' => $buttontitle));
            } else {
                $item->webbutton = '';
            }

            return $item;
        }

        return null;
    }

    /**
     * Fetch a list of authors for a given resource list item.
     *
     * @param DOMXPath $xpath A DOMXPath document object representing the list
     * @param DOMNode $itemdetails The DOM node containing the item's bibliographic data
     * @return string A comma separated list of authors
     */
    private function get_authors($xpath, $itemdetails) {
        $authorsquery = 'span[contains(@class, "author")]';
        $authors = $this->get_dom_nodelist($xpath, $authorsquery, $itemdetails);

        $authorlist = '';
        foreach ($authors as $author) {
            $authorlist .= $author->nodeValue . ', ';
        }
        unset($authors);
        return substr($authorlist, 0, -2);
    }

    /**
     * Fetch a list of available media formats for a given resource list item.
     *
     * @param DOMXPath $xpath A DOMXPath document object representing the list
     * @param DOMNode $itemdetails The DOM node containing the item's bibliographic data
     * @return string A comma separated list of available formats
     */
    private function get_formats($xpath, $itemdetails) {
        $formatsquery = 'span[contains(@class, "formats")]/span[contains(@class, "format")]';
        $formats = $this->get_dom_nodelist($xpath, $formatsquery, $itemdetails);

        $formatlist = '';
        foreach ($formats as $format) {
            $formatlist .= $format->nodeValue . ', ';
        }
        unset($formats);
        return substr($formatlist, 0, -2);
    }

    /**
     * Extract all selected resource items from submitted aspirelist module config form data,
     * and return as comma separated list.
     *
     * @param object $formdata The config form data submitted
     * @return string A comma separated list of selected resource items
     */
    public function get_items_list($formdata) {
        $itemregex = '/^list-[A-F0-9\-]{36}(_section-[A-F0-9\-]{36})*_item-[A-F0-9\-]{36}$/';

        $items = array();
        foreach ($formdata as $name => $value) {
            if (preg_match($itemregex, $name) && $value == 1) {
                $items[] = $name;
            }
        }
        $itemslist = implode(',', $items);

        return $itemslist;
    }

    /**
     * Given a comma separated list of selected resource items, create an array representing a tree
     * structure of the selection, and use this to generate the HTML output to display the custom list.
     *
     * @param string $itemslist A comma separated list of selected items from the config form
     * @return string The final HTML output to display the custom resource list
     */
    public function get_list_html($itemslist) {
        global $OUTPUT;

        if ($this->test_connection()) {
            $html = '';

            if (!empty($itemslist)) {
                $items = explode(',', $itemslist);
                $tree = array();

                foreach ($items as $item) {
                    $path = $this->get_item_path($item);
                    $tree = array_merge_recursive($tree, $path);
                }
                unset($items);

                $lists = array_keys($tree);

                foreach ($lists as $list) {
                    $listid = substr($list, 5);
                    // Fetch list data, from cache if available.
                    $listdata = $this->get_list_data($listid, true);

                    $subtree = $tree[$list];
                    $html .= $this->print_section($listdata, $subtree);
                }
                unset($lists);
            }

            return $this->condense_whitespace($html);
        }

        return $OUTPUT->heading(get_string('noconnection', 'aspirelist'), 3, 'warning');
    }

    /**
     * Return an array representing the full path (i.e. list => section(s) => item) for a given item ID.
     *
     * @param string $itemid A selected resource item ID from the config form
     * @return array A partial tree structure representation of the path components
     */
    private function get_item_path($itemid) {
        $parts = explode('_', $itemid);
        $partscount = count($parts);

        $path = array($parts[$partscount - 1]);

        for ($i = $partscount - 2; $i >= 0; $i--) {
            $path = array($parts[$i] => $path);
        }
        return $path;
    }

    /**
     * Given a list's metadata and an array representing a tree structure of the selected items
     * and their parent section(s), return the section data as HTML.
     *
     * @param stdClass $list An object containing metadata about the parent resource list
     * @param array $tree A tree structure representing a section (or item) from the list
     * @param string $html The HTML output that has been generated from previous iterations
     * @param int $headinglevel The heading level for the next section heading
     * @param boolean $wassection Whether the previous item was a section or a resource item
     * @return string The HTML output for this section of the list
     */
    private function print_section($list, $tree, &$html = '', $headinglevel = 3, $wassection = true) {
        // Don't let heading level exceed 6.
        $headinglevel = $headinglevel <= 6 ? $headinglevel : 6;

        foreach ($tree as $key => $value) {
            if (preg_match($this->sectionidregex, $key)) {
                // This is a section so count its items and print its details.
                $itemcount = $this->count_items($value);
                if (!$wassection) {
                    // If previous item was a resource, close the unordered list element.
                    $html .= html_writer::end_tag('ul');
                }
                // Open a section box and print the heading data.
                $html .= html_writer::start_div('listsection');
                $html .= $this->get_section_html($list, $key, $itemcount, $headinglevel);
                // Remember that this was a section.
                $wassection = true;
                // Then process any sub-sections.
                if (is_array($value)) {
                    $subtree = $tree[$key];
                    $this->print_section($list, $subtree, $html, $headinglevel + 1);
                }
                // Close the section box.
                $html .= html_writer::end_div();
            } else if (preg_match($this->itemidregex, $value)) {
                // This is a list item so print it.
                $html .= $this->print_item($list, $value, $wassection);
            }
        }
        unset($tree);

        if (!$wassection) {
            // If the last item was a resource, close the unordered list element.
            $html .= html_writer::end_tag('ul');
        }

        return $html;
    }

    /**
     * Given an array containing a number of resource items, return a count of those items.
     *
     * @param array|string $items The items contained within a list section
     * @return int The number of resource items (but not sub-sections)
     */
    private function count_items($items) {
        // A section can contain just a single resource item.
        if (!is_array($items)) {
            return 1;
        } else {
            $keys = array_keys($items);
            $itemcount = count($keys);
            foreach ($keys as $key) {
                if (preg_match($this->sectionidregex, $key)) {
                    // We only want to count resource items, not sub-sections.
                    $itemcount--;
                }
            }
            return $itemcount;
        }
    }

    /**
     * Given a list's metadata and a resource item ID, return the item data as HTML.
     *
     * @param stdClass $list An object containing metadata about the parent resource list
     * @param string $itemid The ID of the resource item to print
     * @param boolean $wassection Whether the previous item was a section or a resource item
     * @return string The HTML output for this resource item
     */
    private function print_item($list, $itemid, &$wassection) {
        $html = '';

        if ($wassection) {
            // If previous item was a section heading, open an unordered list element.
            $html .= html_writer::start_tag('ul', array('class' => 'sectionitems'));
        }
        $html .= $this->get_item_html($list, $itemid);
        // Remember that this was not a section.
        $wassection = false;

        return $html;
    }

    /**
     * Given a list's metadata and a section ID, return the section heading and details as HTML.
     *
     * @param stdClass $list An object containing metadata about the parent resource list
     * @param string $sectionid The ID of the required section
     * @param int $itemcount A count of resource items belonging to the section
     * @param int $headinglevel The heading level for the section heading
     * @return string The HTML output for the section heading and details
     */
    private function get_section_html($list, $sectionid, $itemcount, $headinglevel) {
        global $OUTPUT;

        if ($section = $this->get_section_data($list->xpath, null, $sectionid)) {
            if ($itemcount > 0) {
                $plural = $itemcount > 1 ? 'plural' : '';
                $itemcount = ' (' . get_string('itemcount' . $plural, 'aspirelist', $itemcount) . ')';
                $countspan = html_writer::tag('span', $itemcount, array('class' => 'itemcount dimmed_text'));
            } else {
                $countspan = '';
            }

            $heading = $OUTPUT->heading($section->name . $countspan, $headinglevel, 'sectionheading', $section->id);
            $html = $heading . $section->note;

            return $html;
        }

        return '';
    }

    /**
     * Given a list's metadata and a resource item ID, return the item link and details as HTML.
     *
     * @param stdClass $list An object containing metadata about the parent resource list
     * @param string $itemid The ID of the required resource item
     * @return string An HTML list element containing the resource item link and details
     */
    private function get_item_html($list, $itemid) {
        if ($item = $this->get_item_data($list->xpath, null, $itemid)) {
            $html = html_writer::start_tag('li', array('class' => 'listitem'));
            $html .= $item->webbutton;
            $html .= $item->link . $item->authors. $item->published . $item->formats;
            $html .= html_writer::empty_tag('br');
            $html .= $item->resourcetype . $item->importance . $item->studynote;
            $html .= html_writer::end_tag('li');

            return $html;
        }

        return '';
    }

    /**
     * Return a given string with multiple consecutive whitespace characters condensed to a single space.
     *
     * @param string $string The original string to process
     * @return string The output string with excess whitespace removed
     */
    private function condense_whitespace($string) {
        $string = preg_replace('/\s+/', ' ', $string);
        return $string;
    }
}
