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

copyright 2014 Lancaster University (http://www.lancaster.ac.uk/)
license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


Aspire resource list module for Moodle
======================================

The Aspire resource list module enables a teacher to include a selection of
resources from associated Talis Aspire resource lists directly within the
content of their course.

The resource list can be displayed either in a separate, linked page, or
embedded in the course page itself (hidden initially, with a link to toggle
visibility).

The development of this module was based on previous work by the University of
Sussex and Falmouth University.


Installation
------------

Installing from the Git repository (recommended if you installed Moodle from
Git):

Follow the instructions at
http://docs.moodle.org/26/en/Git_for_Administrators#Installing_a_contributed_extension_from_its_Git_repository,
e.g. for the Moodle 2.6.x code:
$ cd /path/to/your/moodle/
$ cd mod/
$ git clone https://github.com/lucisgit/moodle-mod_aspirelist.git aspirelist
$ cd aspirelist/
$ git checkout -b MOODLE_26_STABLE origin/MOODLE_26_STABLE
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
http://docs.moodle.org/26/en/Git_for_Administrators#Installing_a_contributed_extension_from_its_Git_repository):
$ cd /path/to/your/moodle/
$ git pull
$ cd mod/aspirelist/
$ git pull

If you installed from a zip archive you will need to repeat the installation
procedure using the appropriate zip file downloaded from
https://moodle.org/plugins/pluginversions.php?plugin=mod_aspirelist for your
new Moodle version.
