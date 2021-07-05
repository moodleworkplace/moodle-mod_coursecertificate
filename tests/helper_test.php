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
    public function setUp(): void {
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
        // Create course.
        $course = $this->getDataGenerator()->create_course();

        // Create and enrol users.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);
        $user2 = $this->getDataGenerator()->create_and_enrol($course);

        // Create certificate template.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);

        // Create coursecertificate1 module without restrictions.
        $coursecertificate1 = $this->getDataGenerator()->create_module('coursecertificate', ['course' => $course->id,
            'template' => $certificate1->get_id()]);
        $cm1 = get_fast_modinfo($course)->instances['coursecertificate'][$coursecertificate1->id];

        // Check both users are retured.
        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate1, $cm1);
        $this->assertCount(2, $users);

        $certificate1->issue_certificate($user1->id, null, [], 'mod_coursecertificate', $course->id);

        // Check just user2 is returned (user1 was already issued).
        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate1, $cm1);
        $this->assertCount(1, $users);
        $this->assertEquals($users[0]->id, $user2->id);

        // Create coursecertificate2 module with data restriction in the future.
        $futuredate = strtotime('+1year');
        $availabilityvalue = '{"op":"&","c":[{"type":"date","d":">=","t":' . $futuredate . '}],"showc":[true]}';
        $coursecertificate2 = $this->getDataGenerator()->create_module('coursecertificate', ['course' => $course->id,
                'template' => $certificate1->get_id(), 'availability' => $availabilityvalue]);
        $cm2 = get_fast_modinfo($course)->instances['coursecertificate'][$coursecertificate2->id];

        // Check no user is returned.
        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate2, $cm2);
        $this->assertEmpty($users);
    }

    /**
     * Test get course issue data.
     */
    public function test_get_issue_data() {
        // Create a course customfield.
        $catid = $this->getDataGenerator()->create_custom_field_category([])->get('id');
        $field = $this->getDataGenerator()->create_custom_field(['categoryid' => $catid, 'type' => 'text', 'shortname' => 'f1']);

        // Create course with completion self enabled.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01', 'fullname' => 'Course 01',
            'enablecompletion' => COMPLETION_ENABLED, 'customfield_f1' => 'some text']);
        $criteriadata = new \stdClass();
        $criteriadata->id = $course->id;
        $criteriadata->criteria_self = COMPLETION_CRITERIA_TYPE_SELF;

        /** @var \completion_criteria_self $criterion */
        $criterion = \completion_criteria::factory(['criteriatype' => COMPLETION_CRITERIA_TYPE_SELF]);
        $criterion->update_config($criteriadata);

        // Create and enrol user.
        $user = $this->getDataGenerator()->create_and_enrol($course);

        // Set user grade to 10.00.
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $gradeitem2 = grade_item::fetch(['itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $assign->id,
            'courseid' => $course->id]);
        $gradeitem2->update_final_grade($user->id, 10, 'gradebook');

        // Complete the course.
        $this->setUser($user);
        \core_completion_external::mark_course_self_completed($course->id);
        $ccompletion = new \completion_completion(['course' => $course->id, 'userid' => $user->id]);
        $ccompletion->mark_complete();

        $issuedata = \mod_coursecertificate\helper::get_issue_data($course, $user);
        $this->assertEquals($course->id, $issuedata['courseid']);
        $this->assertEquals('C01', $issuedata['courseshortname']);
        $this->assertEquals('Course 01', $issuedata['coursefullname']);
        $this->assertEquals(course_get_url($course)->out(), $issuedata['courseurl']);
        $this->assertEquals('some text', $issuedata['coursecustomfield_f1']);
        $coursecompletiondate = userdate($ccompletion->timecompleted, get_string('strftimedatefullshort'));
        $this->assertEquals($coursecompletiondate, $issuedata['coursecompletiondate']);
        $this->assertEquals('10.00', $issuedata['coursegrade']);
    }
}
