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

use core\notification;

/**
 * Unit test for the lib functions.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2022 Ruslan Kabalin
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {

    /**
     * Set up
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test deleting coursecertificate instance.
     *
     * @covers ::coursecertificate_delete_instance
     */
    public function test_delete_instance(): void {
        $course = $this->getDataGenerator()->create_course();
        $certinstance = $this->getDataGenerator()->create_module('coursecertificate', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('coursecertificate', $certinstance->id);

        // Must not throw.
        course_delete_module($cm->id);
    }

    /**
     * Test for callback before_http_headers
     *
     * @covers ::mod_coursecertificate_before_http_headers()
     */
    public function test_mod_coursecertificate_before_http_headers(): void {
        global $PAGE;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $certinstance = $this->getDataGenerator()->create_module('coursecertificate', ['course' => $course->id]);

        $PAGE = new \moodle_page();
        $cm = get_fast_modinfo($course)->get_cm($certinstance->cmid);
        $PAGE->set_cm($cm, $course, $certinstance);
        $PAGE->set_url(new \moodle_url('/filter/manage.php', ['contextid' => \context_module::instance($certinstance->cmid)->id]));

        // Call the callback once, it will add a notification that 'multilang' will be used for PDFs.
        mod_coursecertificate_before_http_headers();

        $x = notification::fetch();
        $this->assertMatchesRegularExpression('/Only filter\(s\) "multilang" will be used/',
            $x[0]->get_message());

        // Now remove all filters from allowed, call callback. The notification will say that no filters will be used.
        set_config('allowfilters', '', 'tool_certificate');

        mod_coursecertificate_before_http_headers();

        $x = notification::fetch();
        $this->assertMatchesRegularExpression('/No filters will be used/',
            $x[0]->get_message());
    }
}
