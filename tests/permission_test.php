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

namespace mod_coursecertificate;

use advanced_testcase;
use context_course;
use context_system;
use tool_certificate_generator;

/**
 * Unit tests for permission class.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @covers      \mod_coursecertificate\permission
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class permission_test extends advanced_testcase {
    /**
     * Set up
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Get certificate generator
     * @return tool_certificate_generator
     */
    protected function get_certificate_generator(): tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    /**
     * Test can_view_report.
     */
    public function test_can_view_report(): void {
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
    public function test_can_verify_issues(): void {
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
    public function test_can_revoke_issues(): void {
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
    public function test_can_view_all_issues(): void {
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
    public function test_can_receive_issues(): void {
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

    /**
     * Test can_view_group_in_context.
     */
    public function test_can_view_group_in_context(): void {
        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course();
        $template1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate Course 1']);
        $record = [
            'course' => $course,
            'template' => $template1->get_id(),
        ];
        $modnogroups = $this->getDataGenerator()->create_module('coursecertificate',
            array_merge($record, ['groupmode' => NOGROUPS]));
        $contextmodnogroups = \context_module::instance($modnogroups->cmid);
        $modseparate = $this->getDataGenerator()->create_module('coursecertificate',
            array_merge($record, ['groupmode' => SEPARATEGROUPS]));
        $contextmodseparate = \context_module::instance($modseparate->cmid);
        $modvisiblegroups = $this->getDataGenerator()->create_module('coursecertificate',
            array_merge($record, ['groupmode' => VISIBLEGROUPS]));
        $contextmodvisiblegroups = \context_module::instance($modvisiblegroups->cmid);

        $user1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $user2 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $teacher1 = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        $group1 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $group2 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group1->id, 'userid' => $user1->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group2->id, 'userid' => $user2->id]);

        // Check as editing teacher, expect no restrictions.
        $this->setUser($teacher1);
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, 0));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, 0));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, 0));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, $group1->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, $group1->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, $group1->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, $group2->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, $group2->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, $group2->id));

        // Test with not existing group.
        $this->assertFalse(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, 1000));

        // Check as user1, expect restrictions in separate groups when requesting all user and group user is not in.
        $this->setUser($user1);
        $this->assertFalse(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, 0));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, 0));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, 0));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, $group1->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, $group1->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, $group1->id));
        $this->assertFalse(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, $group2->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, $group2->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, $group2->id));

        // Check as user2, expect restrictions in separate groups when requesting all user and group user is not in.
        $this->setUser($user2);
        $this->assertFalse(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, 0));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, 0));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, 0));
        $this->assertFalse(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, $group1->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, $group1->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, $group1->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodseparate, $group2->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodnogroups, $group2->id));
        $this->assertTrue(\mod_coursecertificate\permission::can_view_issues($contextmodvisiblegroups, $group2->id));
    }
}
