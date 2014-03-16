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
 * Javascript helper function for Aspirelist module
 *
 * @package    mod_aspirelist
 * @copyright  2014 Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.mod_aspirelist = {};

M.mod_aspirelist.init_list = function(Y, id, url) {
    Y.use('node', function(Y) {
        Y.one(id).hide();

        Y.delegate('click', function(e) {
            var linkhref = e.currentTarget.get('href'),
                list = Y.one(id);

            if (linkhref === url) {
                list.toggleView();
            }

        }, document, 'a');
    });
};
