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
 * The helper for the Coursecertificate module.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursecertificate;

use core_availability\info_module;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir . '/gradelib.php');

/**
 * The helper for the Coursecertificate module.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Gets users who meet access restrictionss and had not been issued.
     *
     * @param \stdClass $coursecertificate
     * @param \cm_info $cm
     * @return array
     */
    public static function get_users_to_issue(\stdClass $coursecertificate, \cm_info $cm): array {
        global $DB;

        // Get all the users already issued.
        $usersissued = $DB->get_fieldset_select(
            'tool_certificate_issues',
            'userid',
            'component = :component AND courseid = :courseid AND templateid = :templateid',
            ['component' => 'mod_coursecertificate', 'courseid' => $coursecertificate->course,
                'templateid' => $coursecertificate->template]
        );
        // Get active users in course context with receiveissue capability.
        $context = \context_course::instance($coursecertificate->course);
        $enrolledusers = get_enrolled_users($context, 'mod/coursecertificate:receive', 0, 'u.*', null,
            0, 0, true);
        // Filter only users with access to the activity (Does not filter mod visibility).
        $info = new \core_availability\info_module($cm);
        $potentialusers = $info->filter_user_list($enrolledusers);

        // Filter only users without 'viewall' capabilities and that had not been issued.
        $users = [];
        foreach ($potentialusers as $potentialuser) {
            if (has_capability('tool/certificate:viewallcertificates', $context, $potentialuser)) {
                continue;
            }
            if (!info_module::is_user_visible($cm, $potentialuser->id, false)) {
                continue;
            }
            if (!in_array($potentialuser->id, $usersissued)) {
                $users[] = $potentialuser;
            }
        }
        return $users;
    }

    /**
     * Get data for the issue. Important course fields (id, shortname, fullname and URL) and course customfields.
     *
     * @param \stdClass $course
     * @param \stdClass $user
     * @return array
     */
    public static function get_issue_data(\stdClass $course, \stdClass $user): array {
        global $DB;

        // Get user course completion date.
        $result = $DB->get_field('course_completions', 'timecompleted',
            ['course' => $course->id, 'userid' => $user->id]);
        $completiondate = $result ? userdate($result, get_string('strftimedatefullshort')) : '';

        // Get user course grade.
        $grade = grade_get_course_grade($user->id, $course->id);
        if ($grade && $grade->grade) {
            $gradestr = $grade->str_grade;
        }

        $issuedata = [
            'courseid' => $course->id,
            'courseshortname' => $course->shortname,
            'coursefullname' => $course->fullname,
            'courseurl' => course_get_url($course)->out(),
            'coursecompletiondate' => $completiondate,
            'coursegrade' => $gradestr ?? '',
        ];
        // Add course custom fields data.
        $handler = \core_course\customfield\course_handler::create();
        foreach ($handler->get_instance_data($course->id, true) as $data) {
            $issuedata['coursecustomfield_' . $data->get_field()->get('shortname')] = $data->export_value();
        }

        return $issuedata;
    }
}
