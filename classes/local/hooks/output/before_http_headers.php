<?php
// This file is part of the mod_coursecertificate plugin for Moodle - http://moodle.org/
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

namespace mod_coursecertificate\local\hooks\output;

/**
 * Hook callbacks for mod_coursecertificate
 *
 * @package    mod_coursecertificate
 * @copyright  2024 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_http_headers {

    /**
     * Callback allowing to add warning on the filter settings page
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function callback(\core\hook\output\before_http_headers $hook): void {
        global $PAGE, $CFG;

        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }

        if ($PAGE->context->contextlevel == CONTEXT_MODULE &&
                $PAGE->url->compare(new \moodle_url('/filter/manage.php'), URL_MATCH_BASE) &&
                $PAGE->activityname === 'coursecertificate') {
            if ($allowedfilters = \tool_certificate\element_helper::get_allowed_filters()) {
                $link = new \moodle_url('/filter/manage.php', ['contextid' => $PAGE->context->get_course_context()->id]);
                $a = (object)[
                    'link' => $link->out(),
                    'list' => join(', ', $allowedfilters),
                ];
                $message = get_string('filterswarninglist', 'mod_coursecertificate', $a);
            } else {
                $message = get_string('filterswarningnone', 'mod_coursecertificate');
            }
            \core\notification::add(
                get_string('filterswarning', 'mod_coursecertificate') .
                '<br>' . $message,
                \core\output\notification::NOTIFY_WARNING);
        }
    }
}
