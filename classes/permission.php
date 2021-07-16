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
 * Course certificate related permissions.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursecertificate;

use context_course;

defined('MOODLE_INTERNAL') || die;

/**
 * Course certificate related permissions class.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @uses        \tool_certificate\permission
 */
class permission {
    /**
     * If a user can manage coursecertificate module.
     *
     * @param \context $context
     * @return bool
     */
    public static function can_manage(\context $context): bool {
        return has_capability('mod/coursecertificate:addinstance', $context);
    }

    /**
     * If a user can view coursecertificate issues report.
     *
     * @param \context $context
     * @return bool
     */
    public static function can_view_report(\context $context): bool {
        return has_capability('mod/coursecertificate:viewreport', $context);
    }

    /**
     * If a user can verify template issues.
     *
     * @return bool
     */
    public static function can_verify_issues(): bool {
        return \tool_certificate\permission::can_verify();
    }

    /**
     * If a user can revoke.
     *
     * @param int $courseid
     * @return bool
     */
    public static function can_revoke_issues(int $courseid): bool {
        if (!$context = context_course::instance($courseid, IGNORE_MISSING)) {
            return false;
        }
        return \tool_certificate\permission::can_issue_to_anybody($context);
    }

    /**
     * If a user can preview issues.
     *
     * @param int $courseid
     * @return bool
     */
    public static function can_view_all_issues(int $courseid): bool {
        if (!$context = context_course::instance($courseid, IGNORE_MISSING)) {
            return false;
        }
        return \tool_certificate\permission::can_view_all_certificates($context);
    }

    /**
     * If a user can receive issues.
     *
     * @param \context $context
     * @return bool
     */
    public static function can_receive_issues(\context $context): bool {
        return has_capability('mod/coursecertificate:receive', $context);
    }
}
