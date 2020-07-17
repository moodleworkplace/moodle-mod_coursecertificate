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
 * Unit test for the task.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Unit test for the task.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_task_test_testcase extends advanced_testcase {
    /**
     * Set up
     */
    public function setUp() {
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Get certificate generator
     * @return tool_certificate_generator
     */
    protected function get_certificate_generator() : tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    public function test_issue_certificates_task_automaticsend_enabled() {
        global $DB;

        // Create a course customfield.
        $catid = $this->getDataGenerator()->create_custom_field_category([])->get('id');
        $field = $this->getDataGenerator()->create_custom_field(['categoryid' => $catid, 'type' => 'text', 'shortname' => 'f1']);

        // Create course, certificate tempalte and coursecertificate module.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01', 'customfield_f1' => 'some text']);

        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $mod = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $certificate1->get_id()]);
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));

        // Create user with 'student' role.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);

        $mod->automaticsend = 1;
        $DB->update_record('coursecertificate', $mod);

        $task = new mod_coursecertificate\task\issue_certificates_task();
        $task->execute();

        $issues = $DB->get_records('tool_certificate_issues', ['templateid' => $certificate1->get_id(),
            'courseid' => $course->id]);

        // Check certificate issue was created for the user.
        $issue = reset($issues);
        $this->assertEquals($user1->id, $issue->userid);
        $issuedata = @json_decode($issue->data, true);
        $this->assertEquals('C01', $issuedata['courseshortname']);
        $this->assertEquals('some text', $issuedata['coursecustomfield_' . $field->get('id')]);
    }

    public function test_issue_certificates_task_automaticsend_disabled() {
        global $DB;

        // Create course, certificate tempalte and coursecertificate module.
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
        $task = new mod_coursecertificate\task\issue_certificates_task();
        $task->execute();

        // Check no issues were created.
        $issues = $DB->get_records('tool_certificate_issues', ['templateid' => $certificate1->get_id(),
            'courseid' => $course->id]);
        $this->assertEmpty($issues);
    }
}
