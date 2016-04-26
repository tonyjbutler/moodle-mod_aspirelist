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
 * @copyright  2014 onwards Lancaster University {@link http://www.lancaster.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tony Butler <a.butler4@lancaster.ac.uk>
 */

M.mod_aspirelist = {};

M.mod_aspirelist.init_list = function(Y, cmid, url) {
    Y.use('node', 'transition', function(Y) {
        function setRelativePosition() {
            this.setStyle('position', 'relative');
        }
        Y.Transition.fx.slideFadeOut = {
            opacity: 0,
            top: '-100px',
            left: '-600px',
            duration: 0.2,
            easing: 'ease-out',
            on: { start: setRelativePosition }
        };
        Y.Transition.fx.slideFadeIn = {
            opacity: 1.0,
            top: '0px',
            left: '0px',
            duration: 0.2,
            easing: 'ease-in',
            on: { start: setRelativePosition }
        };

        var listid = '#aspirelist-' + cmid,
            arrowid = '#showhide-' + cmid;

        // Hide list if JS enabled.
        Y.one(listid).hide('slideFadeOut');
        // Have to hide again without transition to get display: none.
        Y.one(listid).hide();
        Y.one(arrowid).addClass('collapsed');

        Y.delegate('click', function(e) {
            if (e.currentTarget.ancestor('div').hasClass('activityinstance')) {
                var linkhref = e.currentTarget.get('href'),
                    list = Y.one(listid),
                    arrow = Y.one(arrowid);

                // Add a JavaScript loading icon.
                var spinner = M.util.add_spinner(Y, e.currentTarget.ancestor('div'));
                spinner.removeClass('iconsmall');
                spinner.setStyle('position', 'static');

                if (linkhref === url) {
                    // Display the JS loading icon.
                    spinner.show();

                    if (arrow.hasClass('collapsed')) {
                        // Send AJAX request for view.php (to trigger log/completion events).
                        if (window.XMLHttpRequest) {
                            httpRequest = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            try {
                                httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
                            }
                            catch (e) {
                                try {
                                    httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                catch (e) {}
                            }
                        }
                        // Parse the response and check for errors.
                        function checkResponse() {
                            if (httpRequest.readyState === 4) {
                                var data = Y.JSON.parse(httpRequest.responseText);
                                if (data.hasOwnProperty('error')) { // Alert user if an error has occurred.
                                    alert(data.error);
                                    window.location.href = url;
                                } else { // If all is well, expand the list.
                                    list.show('slideFadeIn');
                                    arrow.removeClass('collapsed');
                                    // Hide the JS loading icon.
                                    spinner.hide();
                                }
                            }
                        }
                        httpRequest.onreadystatechange = checkResponse;
                        httpRequest.open('GET', url);
                        httpRequest.setRequestHeader('X-Requested-With', 'xmlhttprequest');
                        httpRequest.send();
                    } else {
                        list.hide('slideFadeOut');
                        arrow.addClass('collapsed');
                        // Hide the JS loading icon.
                        spinner.hide();
                    }
                }
            }

        }, document, 'a');
    });
};
