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
use mod_coursecertificate\task\issue_certificates_task;
use tool_certificate\certificate;
use tool_certificate_generator;

/**
 * Unit test for the task.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @covers      \mod_coursecertificate\task\issue_certificates_task
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class issue_certificates_task_test extends advanced_testcase {
    /**
     * Set up
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Get certificate generator
     *
     * @return tool_certificate_generator
     */
    protected function get_certificate_generator(): tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    /**
     * Test issue_certificates_task with automaticsend setting enabled.
     */
    public function test_issue_certificates_task_automaticsend_enabled(): void {
        global $DB;

        // Create a course customfield.
        $catid = $this->getDataGenerator()->create_custom_field_category([])->get('id');
        $field = $this->getDataGenerator()->create_custom_field(['categoryid' => $catid, 'type' => 'text', 'shortname' => 'f1']);

        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01', 'customfield_f1' => 'some text']);

        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $expirydate = strtotime('+5 day');
        $record = [
            'course' => $course->id,
            'template' => $certificate1->get_id(),
            'expirydatetype' => certificate::DATE_EXPIRATION_ABSOLUTE,
            'expirydateoffset' => $expirydate,
        ];
        $mod = $this->getDataGenerator()->create_module('coursecertificate', $record);
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));

        // Create user with 'student' role.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);
        // Create user with 'editingteacher' role.
        $user2 = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        $mod->automaticsend = 1;
        $DB->update_record('coursecertificate', $mod);

        $task = new issue_certificates_task();
        ob_start();
        $task->execute();
        ob_end_clean();

        $issues = $DB->get_records('tool_certificate_issues', ['templateid' => $certificate1->get_id(),
            'courseid' => $course->id, ]);

        // Check certificate issue was created for the user.
        $issue = reset($issues);
        $this->assertEquals($user1->id, $issue->userid);
        $this->assertEquals($expirydate, $issue->expires);
        $issuedata = @json_decode($issue->data, true);
        $this->assertEquals('C01', $issuedata['courseshortname']);
        $this->assertEquals('some text', $issuedata['coursecustomfield_' . $field->get('shortname')]);
        $this->assertEmpty($issuedata['coursegrade']);
        $this->assertEmpty($issuedata['coursecompletiondate']);

        // Check certificate issue was not created for the teacher.
        $adminissues = $DB->get_records('tool_certificate_issues', ['userid' => $user2->id]);
        $this->assertEmpty($adminissues);
    }

    /**
     * Test issue_certificates_task with automaticsend setting disabled.
     */
    public function test_issue_certificates_task_automaticsend_disabled(): void {
        global $DB;

        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course();
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $mod = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $certificate1->get_id()]);

        // Create user with 'student' role.
        $this->getDataGenerator()->create_and_enrol($course);

        // Sanity check.
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));
        $this->assertEquals(0, $mod->automaticsend);

        // Run the task.
        $task = new \mod_coursecertificate\task\issue_certificates_task();
        ob_start();
        $task->execute();
        ob_end_clean();

        // Check no issues were created.
        $issues = $DB->get_records('tool_certificate_issues', ['templateid' => $certificate1->get_id(),
            'courseid' => $course->id, ]);
        $this->assertEmpty($issues);
    }

    /**
     * Test issue_certificates_task with automaticsend setting enabled.
     */
    public function test_issue_certificates_task_automaticsend_after_reset(): void {
        global $DB;

        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01']);
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $record = [
            'course' => $course->id,
            'template' => $certificate1->get_id(),
            'automaticsend' => 1,
        ];
        $mod = $this->getDataGenerator()->create_module('coursecertificate', $record);

        // Create several users and enrol as students.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);
        $user2 = $this->getDataGenerator()->create_and_enrol($course);
        $user3 = $this->getDataGenerator()->create_and_enrol($course);

        // One of the user has active certificate and second one has archived certificate.
        helper::issue_certificate($user1, $mod);
        $lastissueid = helper::issue_certificate($user2, $mod);
        $DB->update_record('tool_certificate_issues', ['id' => $lastissueid, 'archived' => 1]);

        // Run scheduled task.
        $task = new \mod_coursecertificate\task\issue_certificates_task();
        ob_start();
        $task->execute();
        ob_end_clean();

        // There should be two new issues.
        $newissues = $DB->get_records_select('tool_certificate_issues',
            'templateid = :templateid and courseid = :courseid and id > :lastissueid',
            ['templateid' => $certificate1->get_id(), 'courseid' => $course->id, 'lastissueid' => $lastissueid]);
        $this->assertCount(2, $newissues);

        // Now each student has one active certificate and user2 has two certificates - one active and one archived.
        $sql = "SELECT id, userid, archived FROM {tool_certificate_issues}
                WHERE component = :component AND courseid = :courseid AND templateid = :templateid
                ORDER BY userid, archived";
        $params = [
            'component' => 'mod_coursecertificate',
            'courseid' => $course->id,
            'templateid' => $certificate1->get_id(),
        ];
        $res = [];
        foreach ($DB->get_records_sql($sql, $params) as $record) {
            $res[$record->userid] = array_merge($res[$record->userid] ?? [], [$record->archived]);
        }
        $this->assertEquals([$user1->id => [0], $user2->id => [0, 1], $user3->id => [0]], $res);
    }

    /**
     * Test issue_certificates_task with a non-existant template.
     */
    public function test_issue_certificates_task_invalid_template(): void {
        global $DB;

        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01']);
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $record = [
            'course' => $course->id,
            'template' => $certificate1->get_id(),
            'automaticsend' => 1,
        ];
        $mod = $this->getDataGenerator()->create_module('coursecertificate', $record);

        // Create a user and enrol as student.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);

        $issues = $DB->get_records('tool_certificate_issues', ['courseid' => $course->id]);
        $this->assertCount(0, $issues);

        // Issue the certificate to user1.
        helper::issue_certificate($user1, $mod);

        $task = new issue_certificates_task();

        $coursecertificates = $task->get_coursecertificates();

        $realrecord = array_shift($coursecertificates);

        // Create fake record from the real one.
        $fakerecord = clone $realrecord;
        $fakerecord->template = 0; // Non-existent template id.

        $issuecerificatetask = $this->getMockBuilder(issue_certificates_task::class)
            ->onlyMethods(['get_coursecertificates'])
            ->getMock();

        // Override get_coursecertificates as if there's 2 record in DB ( real & fake ) records.
        $issuecerificatetask->method('get_coursecertificates')->willReturn([$fakerecord, $realrecord]);

        ob_start();
        $issuecerificatetask->execute();
        ob_end_clean();

        // Check that only the certificate issue with the correct template id has been created.
        $issues = $DB->get_records('tool_certificate_issues', ['courseid' => $course->id]);
        $this->assertCount(1, $issues);
        $this->assertEquals($certificate1->get_id(), reset($issues)->templateid);
    }

    /**
     * Test issue_certificates_task with a non-existance course.
     */
    public function test_issue_certificates_task_invalid_course(): void {
        global $DB;

        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01']);
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $record = [
            'course' => $course->id,
            'template' => $certificate1->get_id(),
            'automaticsend' => 1,
        ];
        $mod = $this->getDataGenerator()->create_module('coursecertificate', $record);

        // Create a user and enrol as student.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);

        // Issue the certificate to user1.
        helper::issue_certificate($user1, $mod);

        $task = new issue_certificates_task();

        $coursecertificates = $task->get_coursecertificates();

        $realrecord = array_shift($coursecertificates);

        // Create fake record from the real one.
        $fakerecord = clone $realrecord;
        $fakerecord->id = 0; // Non-existent id.

        $issuecerificatetask = $this->getMockBuilder(issue_certificates_task::class)
            ->onlyMethods(['get_coursecertificates'])
            ->getMock();

        // Override get_coursecertificates as if there's 2 record in DB ( real & fake ) records.
        $issuecerificatetask->method('get_coursecertificates')->willReturn([$fakerecord, $realrecord]);

        ob_start();
        $issuecerificatetask->execute();
        ob_end_clean();

        // Check that only the certificate issue with the correct module id has been created.
        $issues = $DB->get_records('tool_certificate_issues', ['courseid' => $course->id]);
        $this->assertCount(1, $issues);
        $this->assertEquals($certificate1->get_id(), reset($issues)->templateid);
    }
}
