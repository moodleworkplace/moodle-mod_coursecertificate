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
use tool_certificate\certificate;
use tool_certificate\template;

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

        $context = \context_course::instance($coursecertificate->course);
        // Get users already issued subquery.
        [$usersissuedsql, $usersissuedparams] = self::get_users_issued_select($coursecertificate->course,
            $coursecertificate->template);
        // Get users enrolled with receive capabilities subquery.
        [$enrolledsql, $enrolledparams] = get_enrolled_sql($context, 'mod/coursecertificate:receive', 0, true);
        $sql  = "SELECT eu.id FROM ($enrolledsql) eu WHERE eu.id NOT IN ($usersissuedsql)";
        $params = array_merge($enrolledparams, $usersissuedparams);
        $potentialusers = $DB->get_records_sql($sql, $params);

        // Filter only users with access to the activity {@see info_module::filter_user_list}.
        $info = new info_module($cm);
        $filteredusers = $info->filter_user_list($potentialusers);

        // Filter only users without 'viewall' capabilities and with access to the activity.
        $users = [];
        foreach ($filteredusers as $filtereduser) {
            $modinfouser = get_fast_modinfo($cm->get_course(), $filtereduser->id);
            $cmuser = $modinfouser->get_cms()[$cm->id] ?? null;
            // Property 'cm_info::uservisible' checks if user has access to the activity - it is visible, in the
            // correct group, user has capability to view it, is available. However, for teachers it
            // can return true even if they do not satisfy availability criteria,
            // therefore we need to additionally check property 'cm_info::available'.
            if ($cmuser && $cmuser->uservisible && $cmuser->available) {
                $users[] = $filtereduser;
            }
        }
        return $users;
    }

    /**
     * Returns the record for the certificate user has in a given course
     *
     * In rare situations (race conditions) there can be more than one certificate, in which case return the last record.
     *
     * @param int $userid
     * @param int $courseid
     * @param int $templateid
     * @return \stdClass
     */
    public static function get_user_certificate(int $userid, int $courseid, int $templateid): ?\stdClass {
        global $DB;
        $sql = "SELECT * FROM {tool_certificate_issues} ci
                WHERE component = :component AND courseid = :courseid AND templateid = :templateid AND userid = :userid
                      AND archived = 0
                ORDER BY id DESC";
        $params = [
            'component' => 'mod_coursecertificate',
            'courseid' => $courseid,
            'templateid' => $templateid,
            'userid' => $userid,
        ];
        $records = $DB->get_records_sql($sql, $params);
        return $records ? reset($records) : null;
    }

    /**
     * Issue a course certificate to the user if they don't already have one
     *
     * @param \stdClass $user
     * @param \stdClass $coursecertificate
     * @param \stdClass|null $course course record, if known (to save on retrieving one)
     * @param template|null $template template, if known (for performance reasons when called in a loop)
     * @return int id of the certificate issue or 0 if user already had an issued certificate
     */
    public static function issue_certificate(\stdClass $user, \stdClass $coursecertificate,
                                             ?\stdClass $course = null, ?template $template = null): int {
        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_coursecertificate_issue');
        $lock = $lockfactory->get_lock("i_{$user->id}_{$coursecertificate->template}_{$coursecertificate->course}", MINSECS);
        if (!$lock) {
            throw new \moodle_exception('locktimeout');
        }

        if (self::get_user_certificate($user->id, $coursecertificate->course, $coursecertificate->template)) {
            // If user already has a certificate - do not issue a new one.
            $lock->release();
            return 0;
        }

        $course = $course ?? get_course($coursecertificate->course);
        $template = $template ?? template::instance($coursecertificate->template);
        $issuedata = self::get_issue_data($course, $user);
        $expirydate = certificate::calculate_expirydate(
            $coursecertificate->expirydatetype,
            $coursecertificate->expirydateoffset,
            $coursecertificate->expirydateoffset
        );
        return $template->issue_certificate($user->id, $expirydate, $issuedata, 'mod_coursecertificate', $course->id, $lock);
    }

    /**
     * Returns select for the users that have been already issued
     *
     * @param int $courseid
     * @param int $templateid
     * @return array
     */
    private static function get_users_issued_select(int $courseid, int $templateid): array {
        $sql = "SELECT DISTINCT ci.userid FROM {tool_certificate_issues} ci
                WHERE component = :component AND courseid = :courseid AND templateid = :templateid
                      AND archived = 0";
        $params = ['component' => 'mod_coursecertificate', 'courseid' => $courseid,
            'templateid' => $templateid, ];
        return [$sql, $params];
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
