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
 * File contains the unit tests for the mod_coursecertificate generator
 *
 * @package    mod_coursecertificate
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the mod_coursecertificate generator
 *
 * @package    mod_coursecertificate
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_generator_testcase extends advanced_testcase {

    /**
     * Get certificate generator
     * @return tool_certificate_generator
     */
    protected function get_certificate_generator() : tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    /**
     * Test create instance of module
     */
    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);

        $this->assertFalse($DB->record_exists('coursecertificate', ['course' => $course->id]));
        $mod = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $certificate1->get_id()]);
        $this->assertEquals(1, $DB->count_records('coursecertificate', ['course' => $course->id]));
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));
        $this->assertEquals($certificate1->get_id(), $DB->get_field('coursecertificate', 'template', ['id' => $mod->id]));

        // Create an instance specifying the template by name.
        $mod = $this->getDataGenerator()->create_module('coursecertificate', ['course' => $course->id,
            'template' => $certificate1->get_name()]);
        $this->assertEquals(2, $DB->count_records('coursecertificate', ['course' => $course->id]));
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));
        $this->assertEquals($certificate1->get_id(), $DB->get_field('coursecertificate', 'template', ['id' => $mod->id]));

        // Create an instance without specifying the certificate, a new one should be created.
        $mod = $this->getDataGenerator()->create_module('coursecertificate', ['course' => $course->id]);
        $this->assertEquals(3, $DB->count_records('coursecertificate', ['course' => $course->id]));
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));
        $this->assertNotEquals($certificate1->get_id(), $DB->get_field('coursecertificate', 'template', ['id' => $mod->id]));
    }
}
