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
use tool_certificate_generator;

/**
 * Unit tests for the helper.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @covers      \mod_coursecertificate\helper
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class helper_test extends advanced_testcase {
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
     * Test get users who meet access restrictions and had not been issued.
     */
    public function test_get_users_to_issue(): void {
        // Create course.
        $course = $this->getDataGenerator()->create_course();

        // Create and enrol users.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);
        $user2 = $this->getDataGenerator()->create_and_enrol($course);

        // Create certificate template.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);

        // Create coursecertificate1 module without restrictions.
        $coursecertificate1 = $this->getDataGenerator()->create_module('coursecertificate', ['course' => $course->id,
            'template' => $certificate1->get_id(), ]);
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
                'template' => $certificate1->get_id(), 'availability' => $availabilityvalue, ]);
        $cm2 = get_fast_modinfo($course)->instances['coursecertificate'][$coursecertificate2->id];

        // Check no user is returned.
        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate2, $cm2);
        $this->assertEmpty($users);
    }

    /**
     * Users with multiple roles (student and teacher) should be returned only when they meet the availability criteria
     *
     * @return void
     */
    public function test_get_users_to_issue_multiple_roles(): void {
        global $DB;

        // Create course.
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => true]);
        $page = $this->getDataGenerator()->create_module('page', ['course' => $course->id], ['completion' => 1]);
        $pagecm = get_fast_modinfo($course)->cms[$page->cmid]->get_course_module_record();

        // Create and enrol users. User3 has two roles - student and teacher.
        $user1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $user2 = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $user3 = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $studentrole = $DB->get_field('role', 'id', ['shortname' => 'student']);
        $this->getDataGenerator()->role_assign($studentrole, $user3->id, \context_course::instance($course->id));

        // Create certificate template.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);

        // Create coursecertificate module with availability restriciton of completing another module.
        $availabilityvalue = '{"op":"&","showc":[true],"c":[{"type":"completion","cm":' . $page->cmid .
            ',"e":' . COMPLETION_COMPLETE . '}]}';
        $coursecertificate = $this->getDataGenerator()->create_module('coursecertificate', [
            'course' => $course->id,
            'template' => $certificate1->get_id(),
            'availability' => $availabilityvalue,
        ]);
        $cm = get_fast_modinfo($course)->cms[$coursecertificate->cmid];

        // No users completed $page activity, so no users are eligible for the certificate.
        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate, $cm);
        $this->assertEmpty($users);

        // Complete $page activity as user1. Now this user is eligible for the certificate.
        (new \completion_info($course))->update_state($pagecm, COMPLETION_COMPLETE, $user1->id);

        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate, $cm);
        $this->assertEquals([(object)['id' => $user1->id]], $users);

        // Complete $page activity as user3. Now both users are eligible for the certificate.
        (new \completion_info($course))->update_state($pagecm, COMPLETION_COMPLETE, $user3->id);

        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate, $cm);
        $this->assertEqualsCanonicalizing([(object)['id' => $user1->id], (object)['id' => $user3->id]], $users);

        // Complete $page activity as user2. Since user2 doesn't have a student role, the user will not get a certificate.
        (new \completion_info($course))->update_state($pagecm, COMPLETION_COMPLETE, $user2->id);

        $users = \mod_coursecertificate\helper::get_users_to_issue($coursecertificate, $cm);
        $this->assertEqualsCanonicalizing([(object)['id' => $user1->id], (object)['id' => $user3->id]], $users);
    }

    /**
     * Test get course issue data.
     */
    public function test_get_issue_data(): void {
        // Create a course customfield.
        $catid = $this->getDataGenerator()->create_custom_field_category([])->get('id');
        $field = $this->getDataGenerator()->create_custom_field(['categoryid' => $catid, 'type' => 'text', 'shortname' => 'f1']);

        // Create course with completion self enabled.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01', 'fullname' => 'Course 01',
            'enablecompletion' => COMPLETION_ENABLED, 'customfield_f1' => 'some text', ]);
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
        $gradeitem2 = \grade_item::fetch(['itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $assign->id,
            'courseid' => $course->id, ]);
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

    public function test_get_user_certificate(): void {
        $this->resetAfterTest();

        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course();
        $template1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $template2 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 2']);
        $record = ['course' => $course->id, 'template' => $template1->get_id()];
        $mod = $this->getDataGenerator()->create_module('coursecertificate', $record);

        // Create user with 'student' role.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);

        // User has no certificates.
        $this->assertNull(helper::get_user_certificate($user1->id, $course->id, $template1->get_id()));
        $this->assertNull(helper::get_user_certificate($user1->id, $course->id, $template2->get_id()));

        // Issue one course certificate to user and one general certificate.
        helper::issue_certificate($user1, $mod);
        $template2->issue_certificate($user1->id);

        // Function helper::get_user_certificate() will only return course certificate.
        $cert = helper::get_user_certificate($user1->id, $course->id, $template1->get_id());
        $this->assertNotEmpty($cert->id);
        $this->assertNull(helper::get_user_certificate($user1->id, $course->id, $template2->get_id()));
    }

    public function test_get_user_certificate_race_condition(): void {
        $this->resetAfterTest();

        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course();
        $template1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $template2 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 2']);
        $record = ['course' => $course->id, 'template' => $template1->get_id()];
        $mod = $this->getDataGenerator()->create_module('coursecertificate', $record);

        // Create user with 'student' role.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);

        // Issue two certificates to the user 2 (emulate race condition).
        $id1 = helper::issue_certificate($user1, $mod);
        $id2 = $template1->issue_certificate($user1->id, null, [], 'mod_coursecertificate', $course->id);
        $this->assertNotEmpty($id1);
        $this->assertNotEmpty($id2);
        $this->assertNotEquals($id1, $id2);

        // Now user has two certificates but the helper::get_user_certificate() will only return the last one.
        $cert = helper::get_user_certificate($user1->id, $course->id, $template1->get_id());
        $this->assertEquals($id2, $cert->id);
    }

    public function test_issue_certificate(): void {
        $this->resetAfterTest();

        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course();
        $template1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $record = ['course' => $course->id, 'template' => $template1->get_id()];
        $mod = $this->getDataGenerator()->create_module('coursecertificate', $record);

        // Create user with 'student' role.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);
        $user2 = $this->getDataGenerator()->create_and_enrol($course);
        $user3 = $this->getDataGenerator()->create_and_enrol($course);

        // Issue certificates to users using different number of parameters.
        $id1 = helper::issue_certificate($user1, $mod);
        $this->assertNotEmpty($id1);
        $this->assertNotEmpty(helper::issue_certificate($user2, $mod, $course));
        $this->assertNotEmpty(helper::issue_certificate($user3, $mod, $course, $template1));

        // Try to issue a user a certificate again - no certificate will be issued.
        $this->assertEmpty(helper::issue_certificate($user1, $mod));
    }
}
