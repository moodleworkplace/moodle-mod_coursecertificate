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
 * Unit tests for the webservices.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_coursecertificate\external;

defined('MOODLE_INTERNAL') || die;

/**
 * Unit tests for the webservices.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_external_test_testcase extends advanced_testcase
{
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
    protected function get_certificate_generator() : tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    /**
     * Test update automaticsend as editingteacher.
     */
    public function test_update_automaticsend() {
        global $DB;

        // Create course and user enrolled as 'editingteacher'.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->setUser($user->id);

        // Create certificate template.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);

        // Create coursecertificate module.
        $mod = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $certificate1->get_id(), 'visible' => 0]);

        // Sanity check.
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));
        $this->assertEquals(0, $mod->automaticsend);

        // Enable automaticsend.
        $result = external::update_automaticsend($mod->id, true);
        $result = external::clean_returnvalue(external::update_automaticsend_returns(), $result);

        $this->assertEquals(1, $DB->get_field('coursecertificate', 'automaticsend', ['id' => $mod->id]));

        $this->assertTrue($result['showhiddenwarning']);
        $this->assertFalse($result['shownoautosendinfo']);

        $cm = get_coursemodule_from_instance('coursecertificate', $mod->id);
        $DB->update_record('course_modules', (object)['id' => $cm->id, 'visible' => 1]);

        // Disable automaticsend.
        $result = external::update_automaticsend($mod->id, false);
        $result = external::clean_returnvalue(external::update_automaticsend_returns(), $result);

        $this->assertEquals(0, $DB->get_field('coursecertificate', 'automaticsend', ['id' => $mod->id]));

        $this->assertFalse($result['showhiddenwarning']);
        $this->assertTrue($result['shownoautosendinfo']);

        // Enable automaticsend.
        $result = external::update_automaticsend($mod->id, true);
        $result = external::clean_returnvalue(external::update_automaticsend_returns(), $result);

        $this->assertFalse($result['showhiddenwarning']);
        $this->assertFalse($result['shownoautosendinfo']);
    }

    /**
     * Test update automaticsend as teacher (no capabilities).
     */
    public function test_update_automaticsend_without_capabilities() {
        global $DB;

        // Create course and user enrolled as 'teacher'.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $this->setUser($user->id);

        // Create certificate template.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);

        // Create coursecertificate module.
        $mod = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $certificate1->get_id()]);

        // Sanity check.
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));
        $this->assertEquals(0, $mod->automaticsend);

        // Try to create an existing issue file.
        external::update_automaticsend($mod->id, true);
        $this->assertEquals(0, $DB->get_field('coursecertificate', 'automaticsend', ['id' => $mod->id]));
    }
}
