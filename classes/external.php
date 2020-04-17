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
 * The external API for the Coursecertificate module.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursecertificate;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/externallib.php');

/**
 * The external class for the Coursecertificate module.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends \external_api {
    /**
     * Returns the structure of parameters for update_automaticsend function.
     * @return \external_function_parameters
     */
    protected static function update_automaticsend_parameters() {
        $params = [
            'id' => new \external_value(PARAM_INT, 'The ID of the certificate', VALUE_REQUIRED),
            'automaticsend' => new \external_value(PARAM_BOOL, 'The value of automaticsend setting')
        ];
        return new \external_function_parameters($params);
    }

    /**
     * Update automaticsend setting value.
     *
     * @param int $id
     * @param bool $automaticsend
     * @return bool
     */
    public static function update_automaticsend(int $id, bool $automaticsend) {
        global $DB;

        $params = self::validate_parameters(self::update_automaticsend_parameters(),
            ['id' => $id, 'automaticsend' => $automaticsend]);

        $certificate = $DB->get_record('coursecertificate', ['id' => $params['id']], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('coursecertificate', $certificate->id);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);

        if (permission::can_manage($context)) {
            $certificate->automaticsend = $params['automaticsend'];
            if ($DB->update_record('coursecertificate', $certificate)) {
                // TODO: Event module updated.
                return true;
            }
        }

        return false;
    }

    /**
     * Describes the return function of update_certificate_automaticsend
     *
     * @return \external_value
     */
    public static function update_automaticsend_returns() {
        return new \external_value(PARAM_BOOL, 'True if successfully updated.');
    }

    /**
     * Returns the structure of parameters for issue_certificate function.
     * @return \external_function_parameters
     */
    protected static function receive_issue_parameters() {
        $params = [
            'id' => new \external_value(PARAM_INT, 'The ID of the certificate', VALUE_REQUIRED)
        ];
        return new \external_function_parameters($params);
    }

    /**
     * Issue coursecertificate template for current user.
     *
     * @param int $id
     * @return bool
     */
    public static function receive_issue(int $id) {
        global $DB, $USER;

        $params = self::validate_parameters(self::receive_issue_parameters(),
            ['id' => $id]);

        $coursecertificate = $DB->get_record('coursecertificate', ['id' => $params['id']], '*', MUST_EXIST);
        [$course, $cm] = get_course_and_cm_from_instance($coursecertificate->id, 'coursecertificate', );
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        // Require receiveissue capabilities
        permission::require_can_receive_issues($context);

        // Check module visibility for user.
        if (\core_availability\info_module::is_user_visible($cm)) {
            if ($templaterecord = $DB->get_record('tool_certificate_templates', ['id' => $coursecertificate->template], '*', MUST_EXIST)) {
                $issueid = \tool_certificate\template::instance($templaterecord->id)->issue_certificate(
                    $USER->id,
                    $coursecertificate->expires,
                    [],
                    'mod_coursecertificate',
                    $course->id
                );
                if ($issueid) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Describes the return function of issue_certificate
     *
     * @return \external_value
     */
    public static function receive_issue_returns() {
        return new \external_value(PARAM_BOOL, 'True if successfully issued.');
    }
}