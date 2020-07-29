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
 * Unit tests for the helper.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Unit tests for the helper.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_helper_test_testcase extends advanced_testcase {
    /**
     * Set up
     */
    public function setUp() {
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
     * Test get users who meet access restrictions and had not been issued.
     */
    public function test_get_users_to_issue() {
        global $DB;

        // Create course.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01', 'customfield_f1' => 'some text']);

        // Create and enrol users.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);
        $user2 = $this->getDataGenerator()->create_and_enrol($course);

        // Create certificate template and coursecertificate module.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $coursecertificate = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $certificate1->get_id()]);
        $cm = get_fast_modinfo($course)->instances['coursecertificate'][$coursecertificate->id];

        // Check both users are retured.
        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate, $cm);
        $this->assertEquals(2, count($users));

        $certificate1->issue_certificate($user1->id, null, [], 'mod_coursecertificate', $course->id);

        // CHeck just user2 is returned.
        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate, $cm);
        $this->assertEquals(1, count($users));
        $this->assertEquals($users[0], $user2);
    }
}