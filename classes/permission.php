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
        return has_capability('mod/coursecertificate:manage', $context);
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
        if (!class_exists('\\tool_certificate\\permission')) {
            throw new \coding_exception('\\tool_certificate\\permission class does not exists');
        }
        return \tool_certificate\permission::can_verify();
    }

    /**
     * If a user can manage templates.
     *
     * @param \context $context
     * @return bool
     */
    public static function can_manage_templates(\context $context): bool {
        if (!class_exists('\\tool_certificate\\permission')) {
            throw new \coding_exception('\\tool_certificate\\permission class does not exists');
        }
        return \tool_certificate\permission::can_manage($context);
    }

    /**
     * If a user can view templates in course.
     *
     * @param int $courseid
     * @return bool
     */
    public static function can_view_templates(int $courseid): bool {
        if (!class_exists('\\tool_certificate\\permission')) {
            throw new \coding_exception('\\tool_certificate\\permission class does not exists');
        }
        $context = context_course::instance($courseid);
        return \tool_certificate\permission::can_view_templates_in_context($context);
    }
}