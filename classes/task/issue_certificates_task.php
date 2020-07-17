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
 * Issue certificates scheduled task.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_coursecertificate\task;

use context_module;

defined('MOODLE_INTERNAL') || die();

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

        // Get all the coursecertificates.
        $coursecertificates = $DB->get_records('coursecertificate');
        foreach ($coursecertificates as $coursecertificate) {
            if (!$coursecertificate->automaticsend) {
                // Skip coursecertificates with automaticsend disabled.
                continue;
            }
            // Check coursecertificate template exists.
            if ($templaterecord = $DB->get_record('tool_certificate_templates', ['id' => $coursecertificate->template])) {
                $template = \tool_certificate\template::instance($templaterecord->id);
            } else {
                mtrace("... Warning: Skipping coursecertificate $coursecertificate->id (invalid templateid: " .
                    "$coursecertificate->template)");
                continue;
            }
            [$course, $cm] = get_course_and_cm_from_instance($coursecertificate->id, 'coursecertificate');
            if (!$cm->visible) {
                // Skip coursecertificate modules not visible.
                continue;
            }
            // Add course data to the issue.
            $issuedata = [
                'courseid' => $course->id,
                'courseshortname' => $course->shortname,
                'coursefullname' => $course->fullname,
                'courseurl' => course_get_url($course)->out(),
            ];
            // Add course custom fields data.
            $handler = \core_course\customfield\course_handler::create();
            foreach ($handler->get_instance_data($course->id, true) as $data){
                $issuedata['coursecustomfield_' . $data->get_field()->get('id')] = s($data->get_value());
            }

            // Get all the users already issued.
            $usersissued = $DB->get_fieldset_select(
                'tool_certificate_issues',
                'userid',
                'component = :component AND courseid = :courseid',
                ['component' => 'mod_coursecertificate', 'courseid' => $coursecertificate->course]
            );
            // Get active users in course context with receiveissue capability.
            $context = \context_course::instance($coursecertificate->course);
            $potentialusers = get_enrolled_users($context, 'mod/coursecertificate:receive', 0, 'u.*', null,
                0, 0, true);
            // Filter only users with access to the activity (Does not filter mod visibility).
            $info = new \core_availability\info_module($cm);
            $users = $info->filter_user_list($potentialusers);
            // Issue the certificate.
            foreach ($users as $user) {
                if (!in_array($user->id, $usersissued)) {
                    $template->issue_certificate(
                        $user->id,
                        $coursecertificate->expires,
                        $issuedata,
                        'mod_coursecertificate',
                        $course->id
                    );
                    mtrace("... issued coursecertificate $coursecertificate->id for user $user->id on course $course->id");
                }
            }
        }
    }
}