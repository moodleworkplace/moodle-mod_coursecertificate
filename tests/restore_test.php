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
 * Unit tests for restore.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . "/phpunit/classes/restore_date_testcase.php");

/**
 * Unit tests for restore.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_restore_testcase extends restore_date_testcase {
    /**
     * Set up
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Get certificate generator
     *
     * @return tool_certificate_generator
     */
    protected function get_certificate_generator() : tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    /**
     * Backs a course up.
     *
     * @param stdClass $course Course object to backup
     */
    protected function backup($course) {
        global $USER, $CFG;

        // Turn off file logging, otherwise it can't delete the file (Windows).
        $CFG->backup_file_logger_level = backup::LOG_NONE;

        // Do backup with default settings.
        set_config('backup_general_users', 1, 'backup');
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id,
            backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_GENERAL,
            $USER->id);
        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];
        $fp = get_file_packer('application/vnd.moodle.backup');
        $filepath = $CFG->dataroot . '/temp/backup/test-restore-course';
        $file->extract_to_pathname($fp, $filepath);
        $bc->destroy();
    }

    /**
     * Restores a course.
     *
     * @param stdClass $course Course object to restore
     * @return int ID of newly restored course
     */
    protected function restore($course) {
        global $USER;
        // Do restore to new course with default settings.
        $newcourseid = restore_dbops::create_new_course(
            $course->fullname, $course->shortname . '_2', $course->category);
        $rc = new restore_controller('test-restore-course', $newcourseid,
            backup::INTERACTIVE_NO, backup::MODE_GENERAL, $USER->id,
            backup::TARGET_NEW_COURSE);

        $newdate = $this->restorestartdate;

        $rc->get_plan()->get_setting('course_startdate')->set_value($newdate);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }

    /**
     * Test restore with existing template and existing issue with same code
     */
    public function test_restore_without_issues() {
        global $DB;

        // Create course and coursecertificate module.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        [$course, $coursecertificate] = $this->create_course_and_module('coursecertificate',
            ['template' => $certificate1->get_id()]);

        // Create user with 'student' role and issue a certificate.
        $user = $this->getDataGenerator()->create_and_enrol($course);
        $issueid = $certificate1->issue_certificate($user->id, null, [], 'mod_coursecertificate', $course->id);
        $DB->get_record('tool_certificate_issues', ['id' => $issueid]);

        // Do backup and restore.
        $newcourseid = $this->backup_and_restore($course);
        $newcoursecertificate = $DB->get_record('coursecertificate', ['course' => $newcourseid]);

        // Check new coursecertificate data.
        $this->assertFieldsNotRolledForward($coursecertificate, $newcoursecertificate, ['timecreated', 'timemodified']);
        $this->assertEquals($coursecertificate->name, $newcoursecertificate->name);
        $this->assertEquals($coursecertificate->automaticsend, $newcoursecertificate->automaticsend);

        // Check new issue is not generated.
        $newissue = $DB->get_record('tool_certificate_issues', ['courseid' => $newcourseid, 'userid' => $user->id,
            'templateid' => $certificate1->get_id()], '*', IGNORE_MISSING);
        $this->assertEmpty($newissue);
    }

    /**
     * Test restore with existing template and non-existing issue with same code.
     */
    public function test_restore_with_issues() {
        global $DB;

        // Create course and coursecertificate module.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        [$course, $coursecertificate] = $this->create_course_and_module('coursecertificate',
            ['template' => $certificate1->get_id()]);

        // Create user with 'student' role and issue a certificate.
        $user = $this->getDataGenerator()->create_and_enrol($course);
        $issueid = $certificate1->issue_certificate($user->id, null, [], 'mod_coursecertificate', $course->id);
        $issue = $DB->get_record('tool_certificate_issues', ['id' => $issueid]);

        $fs = get_file_storage();
        $files = $fs->get_area_files(context_system::instance()->id, 'tool_certificate', 'issues',
            $issue->id, 'itemid', false);
        $issuefile = reset($files);

        // Do backup.
        $this->backup($course);

        // Delete issue.
        $DB->delete_records('tool_certificate_issues', ['id' => $issue->id]);

        // Do restore.
        $newcourseid = $this->restore($course);
        $newcoursecertificate = $DB->get_record('coursecertificate', ['course' => $newcourseid]);

        // Check new coursecertificate data.
        $this->assertFieldsNotRolledForward($coursecertificate, $newcoursecertificate, ['timecreated', 'timemodified']);
        $this->assertEquals($coursecertificate->name, $newcoursecertificate->name);
        $this->assertEquals($coursecertificate->automaticsend, $newcoursecertificate->automaticsend);

        // Check new issue is generated.
        $newissue = $DB->get_record('tool_certificate_issues', ['courseid' => $newcourseid, 'userid' => $user->id,
            'templateid' => $certificate1->get_id()], '*', IGNORE_MISSING);
        $this->assertEquals($issue->data, $newissue->data);

        $files = $fs->get_area_files(context_system::instance()->id, 'tool_certificate', 'issues',
            $newissue->id, 'itemid', false);
        $newissuefile = reset($files);
        $this->assertEquals($issuefile->get_contenthash(), $newissuefile->get_contenthash());
    }
}
