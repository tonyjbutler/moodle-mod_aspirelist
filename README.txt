This file is part of Moodle - http://moodle.org/

Moodle is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Moodle is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

copyright 2014 onwards Lancaster University (http://www.lancaster.ac.uk/)
license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
author    Tony Butler <a.butler4@lancaster.ac.uk>


Aspire resource list module for Moodle
======================================

The Aspire resource list module enables a teacher to include a selection of
resources from associated Talis Aspire resource lists directly within the
content of their course.

The resource list can be displayed either in a separate, linked page, or
embedded in the course page itself (hidden initially, with a link to toggle
visibility).

The development of this module was based on previous work by the University of
Sussex and Falmouth Exeter Plus (on behalf of Falmouth University).


Changelog
---------

2016-12-19  v3.2.0

  * Refactor and reorganise admin config settings form
  * Update styles to support Boost and other theme changes

2016-12-19  v3.2.0/2.9.6

  * Include all valid node types in knowledge group options
  * Enable per list knowledge group config via custom table
  * Use $CFG->prefix as default for custom database table
  * Set cURL timeout option to 30 seconds

2016-05-25  v2.9.5

  * Suppress DOMDocument 'invalid tags' debug messages
  * Address coding style issues highlighted by Moodle code checker tool
  * Remove support for deprecated 'groupmembersonly' feature
  * Add @author tag to all docblocks (and update copyright)
  * Add HTTPS alias config setting and improve URL cleaning
  * Replace module icon with a correctly sized version
  * Verify compatibility with Moodle 3.1 core code

2015-12-10  v2.9.4/2.7.8

  * Add option to include author data for items in module config form

2015-09-28  v2.9.3/2.7.7/2.6.8

  * Fix buggy link for TADC digitised resources and reinstate button

2015-09-23  v2.6.7

  * Bug fix to reflect change in list structure by Talis (GitHub issue #3)

2015-09-23  v2.9.2/2.7.6/2.6.7

  * Fix pattern matching where capturing groups are specified in regex

2015-09-21  v2.7.5/2.6.6

  * Don't render 'Online resource' button for TADC digitised resources

2015-09-15  v2.9.1/2.7.4

  * Bug fix to reflect change in list structure by Talis (GitHub issue #3)

2015-05-21  v2.9.0

  * Add support for using course shortname as an Aspire code/year source
  * Don't render 'Online resource' button for TADC digitised resources
  * Enable views of lists displayed inline to trigger log events
  * Allow automatic completion on view to be used with inline display
  * Replace add_intro_editor with standard_intro_elements for Moodle 2.9

2015-01-21  v2.7.3/2.6.5/2.5.5

  * Implement Moodle Universal Cache support for caching of resource list data

2014-10-30  v2.7.2/2.6.4/2.5.4

  * Animate showing/hiding of lists and use arrow icon to indicate visibility
  * Add option to include Aspire codes from meta linked child courses
  * Fix styles for config form display on mobile devices
  * Display individual lists in separate collapsible fieldsets in config form
  * Add support for multiple Aspire code matches in idnumber string
  * Fix HTML rendering for single-item sections

2014-08-03  v2.7.1/2.6.3/2.5.3

  * Add admin config setting for default list display mode
  * Support loose resource items not contained within a list section
  * Update styles to work better with both Bootstrap and standard themes
  * Use ID number as fallback code source if no code found in custom table


Installation
------------

Installing from the Git repository (recommended if you installed Moodle from
Git):

Follow the instructions at
http://docs.moodle.org/32/en/Git_for_Administrators#Installing_a_contributed_extension_from_its_Git_repository,
e.g. for the Moodle 3.2.x code:
$ cd /path/to/your/moodle/
$ cd mod/
$ git clone https://github.com/tonyjbutler/moodle-mod_aspirelist.git aspirelist
$ cd aspirelist/
$ git checkout -b MOODLE_32_STABLE origin/MOODLE_32_STABLE
$ git branch -d master
$ cd /path/to/your/moodle/
$ echo /mod/aspirelist/ >> .git/info/exclude


Installing from a zip archive downloaded from
https://moodle.org/plugins/pluginversions.php?plugin=mod_aspirelist:

1. Download and unzip the appropriate release for your version of Moodle.
2. Place the extracted "aspirelist" folder in your "/mod/" subdirectory.

Whichever method you use to get the module code in place, the final step is to
visit your Site Administration > Notifications page in a browser to invoke the
installation script and make the necessary database changes.


Updating Moodle
---------------
If you installed Moodle and the Aspire resource list module from Git you can
run the following commands to update both (see
http://docs.moodle.org/32/en/Git_for_Administrators#Installing_a_contributed_extension_from_its_Git_repository):
$ cd /path/to/your/moodle/
$ git pull
$ cd mod/aspirelist/
$ git pull

If you installed from a zip archive you will need to repeat the installation
procedure using the appropriate zip file downloaded from
https://moodle.org/plugins/pluginversions.php?plugin=mod_aspirelist for your
new Moodle version.
