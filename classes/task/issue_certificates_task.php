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

/**
 * Issue certificates scheduled task.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_coursecertificate\task;

use context_module;
use mod_coursecertificate\helper;
use tool_certificate\certificate;

/**
 * Issue certificates scheduled task class.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue_certificates_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task.
     *
     * @return string
     * @uses \tool_certificate\template
     */
    public function get_name() {
        return get_string('taskissuecertificates', 'coursecertificate');
    }

    /**
     * Execute.
     */
    public function execute() {
        global $DB;

        // Get all the coursecertificates with automatic send enabled.
        $sql = "SELECT c.*
                FROM {coursecertificate} c
                JOIN {tool_certificate_templates} ct
                ON c.template = ct.id
                WHERE c.automaticsend = 1";
        $coursecertificates = $DB->get_records_sql($sql);
        foreach ($coursecertificates as $coursecertificate) {
            [$course, $cm] = get_course_and_cm_from_instance($coursecertificate->id, 'coursecertificate',
                $coursecertificate->course);
            if (!$cm->visible) {
                // Skip coursecertificate modules not visible.
                continue;
            }

            $template = \tool_certificate\template::instance($coursecertificate->template);

            // Get all the users with requirements that had not been issued.
            $users = helper::get_users_to_issue($coursecertificate, $cm);

            // Issue the certificate.
            foreach ($users as $user) {
                if (helper::issue_certificate($user, $coursecertificate, $course, $template)) {
                    mtrace("... issued coursecertificate $coursecertificate->id for user $user->id on course $course->id");
                }
            }
        }
    }
}
