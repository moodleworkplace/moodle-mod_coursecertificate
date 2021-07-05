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
 * Unit tests for permission class.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Unit tests for permission class.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_permission_test_testcase extends advanced_testcase {
    /**
     * Set up
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test can_view_report.
     */
    public function test_can_view_report() {
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_and_enrol($course);
        $this->setUser($user);

        // User without view report capabilities.
        $this->assertFalse(has_capability('mod/coursecertificate:viewreport', context_course::instance($course->id)));
        $this->assertFalse(\mod_coursecertificate\permission::can_view_report(context_course::instance($course->id)));

        // Enrol user as teacher (with view report capabilities).
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'teacher');
        $this->assertTrue(has_capability('mod/coursecertificate:viewreport', context_course::instance($course->id)));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_report(context_course::instance($course->id)));
    }

    /**
     * Test can_verify_issues.
     */
    public function test_can_verify_issues() {
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_and_enrol($course);
        $this->setUser($user);

        // Every user can verify.
        $this->assertTrue(has_capability('tool/certificate:verify', context_system::instance()));
        $this->assertTrue(\mod_coursecertificate\permission::can_verify_issues());
    }

    /**
     * Test can_revoke_issues.
     */
    public function test_can_revoke_issues() {
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_and_enrol($course);
        $this->setUser($user);

        // User without issue capabilities.
        $this->assertFalse(has_capability('tool/certificate:issue', context_course::instance($course->id)));
        $this->assertFalse(\mod_coursecertificate\permission::can_revoke_issues($course->id));

        // Enrol user as student (without issue capabilities).
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->assertFalse(has_capability('tool/certificate:issue', context_course::instance($course->id)));
        $this->assertFalse(\mod_coursecertificate\permission::can_revoke_issues($course->id));

        // Enrol user as editingteacher (with issue capabilities).
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'teacher');
        $this->assertTrue(has_capability('tool/certificate:issue', context_course::instance($course->id)));
        $this->assertTrue(\mod_coursecertificate\permission::can_revoke_issues($course->id));
    }

    /**
     * Test can_view_all_issues
     */
    public function test_can_view_all_issues() {
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_and_enrol($course);
        $this->setUser($user);

        // User without view all certificates capabilities.
        $this->assertFalse(has_capability('tool/certificate:viewallcertificates', context_course::instance($course->id)));
        $this->assertFalse(\mod_coursecertificate\permission::can_view_all_issues($course->id));

        // Enrol user as student (without view all certificates capabilities).
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->assertFalse(has_capability('tool/certificate:viewallcertificates', context_course::instance($course->id)));
        $this->assertFalse(\mod_coursecertificate\permission::can_view_all_issues($course->id));

        // Enrol user as editingteacher (with view all certificates capabilities).
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'teacher');
        $this->assertTrue(has_capability('tool/certificate:viewallcertificates', context_course::instance($course->id)));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_all_issues($course->id));
    }

    /**
     * Test can_receive_issues.
     */
    public function test_can_receive_issues() {
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertFalse(has_capability('mod/coursecertificate:receive', context_course::instance($course->id)));
        $this->assertFalse(\mod_coursecertificate\permission::can_receive_issues(context_course::instance($course->id)));

        // Enrol user as editingteacher (without receive issue capabilities).
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'editingteacher');
        $this->assertFalse(has_capability('mod/coursecertificate:receive', context_course::instance($course->id)));
        $this->assertFalse(\mod_coursecertificate\permission::can_receive_issues(context_course::instance($course->id)));

        // Enrol user as student (with receive issue capabilities).
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->assertTrue(has_capability('mod/coursecertificate:receive', context_course::instance($course->id)));
        $this->assertTrue(\mod_coursecertificate\permission::can_receive_issues(context_course::instance($course->id)));
    }
}
