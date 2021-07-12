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

use tool_brickfield\manager;

/**
 * Test for accessibility tool support (Workplace only)
 *
 * @package    mod_coursecertificate
 * @copyright  2021 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_tool_brickfield_area_test extends \advanced_testcase {

    /**
     * Skip the test if this is not a Workplace installation
     *
     * Workplace modified core to include this module in accessibility checks
     */
    protected function skip_if_not_workplace() {
        if (!core_component::get_component_directory('tool_wp')) {
            $this->markTestSkipped('Not Workplace');
        }
        if (!core_component::get_component_directory('tool_brickfield')) {
            $this->markTestSkipped('Plugin tool_brickfield is not available');
        }
    }

    /**
     * Tests for the function manager::get_all_areas()
     */
    public function test_get_areas() {
        $this->skip_if_not_workplace();
        $this->resetAfterTest();
        $areas = manager::get_all_areas();
        $areaclassnames = array_map('get_class', $areas);

        // Make sure the list of areas contains some known areas.
        $this->assertContains(mod_coursecertificate\local\tool_brickfield\areas\intro::class, $areaclassnames);
        $this->assertContains(mod_coursecertificate\local\tool_brickfield\areas\name::class, $areaclassnames);
    }

    /**
     * Get certificate generator
     * @return tool_certificate_generator
     */
    protected function get_certificate_generator() : tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    /**
     * Create two modules
     *
     * @return array of two cm_info instances
     */
    protected function create_modules() {
        $course = $this->getDataGenerator()->create_course();

        // Create certificate template.
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);

        // Create coursecertificate module.
        $choice1 = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $certificate1->get_id(), 'visible' => 0]);

        list($course1, $cm1) = get_course_and_cm_from_instance($choice1->id, 'coursecertificate');
        $choice2 = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $certificate1->get_id(), 'visible' => 0]);
        list($course2, $cm2) = get_course_and_cm_from_instance($choice2->id, 'coursecertificate');

        return [$cm1, $cm2];
    }

    /**
     * Test for the areas choice intro and choice options
     */
    public function test_intro() {
        $this->skip_if_not_workplace();
        $this->resetAfterTest();
        [$cm1, $cm2] = $this->create_modules();

        // Testing the choice intro.
        $intro = new \mod_coursecertificate\local\tool_brickfield\areas\intro();
        $resultsrs = $intro->find_course_areas($cm1->course);
        // Set up a results array from the recordset for easier testing.
        $results = self::array_from_recordset($resultsrs);

        $this->assertCount(2, $results);
        $this->assertEquals('mod_coursecertificate', $results[0]->component);
        $this->assertEquals($cm1->id, $results[0]->cmid);
        $this->assertEquals($cm2->instance, $results[1]->itemid);
        $this->assertEquals('intro', $results[1]->fieldorarea);

        // Emulate the course_module_created event.
        $event = \core\event\course_module_created::create_from_cm($cm1);
        $relevantresultsrs = $intro->find_relevant_areas($event);
        $relevantresults = self::array_from_recordset($relevantresultsrs);
        $this->assertEquals([$results[0]], $relevantresults);

        // Emulate the course_module_updated event.
        $event = \core\event\course_module_updated::create_from_cm($cm1);
        $relevantresultsrs = $intro->find_relevant_areas($event);
        $relevantresults = self::array_from_recordset($relevantresultsrs);
        $this->assertEquals([$results[0]], $relevantresults);

    }

    /**
     * Test for the areas choice intro and choice options
     */
    public function test_name() {
        $this->skip_if_not_workplace();
        $this->resetAfterTest();
        [$cm1, $cm2] = $this->create_modules();

        $name = new \mod_coursecertificate\local\tool_brickfield\areas\name();
        $resultsrs = $name->find_course_areas($cm1->course);
        // Set up a results array from the recordset for easier testing.
        $resultsname = self::array_from_recordset($resultsrs);

        $this->assertCount(2, $resultsname);
        $this->assertEquals('mod_coursecertificate', $resultsname[0]->component);
        $this->assertEquals($cm1->id, $resultsname[0]->cmid);
        $this->assertEquals($cm2->instance, $resultsname[1]->itemid);
        $this->assertEquals('name', $resultsname[1]->fieldorarea);

        // Emulate the course_module_created event.
        $event = \core\event\course_module_created::create_from_cm($cm1);
        $relevantresultsrs = $name->find_relevant_areas($event);
        $relevantresults = self::array_from_recordset($relevantresultsrs);
        $this->assertEquals([$resultsname[0]], $relevantresults);
    }

    /**
     * Array from recordset.
     *
     * @param \moodle_recordset $rs
     * @return array
     */
    private static function array_from_recordset($rs) {
        $records = [];
        foreach ($rs as $record) {
            $records[] = $record;
        }
        $rs->close();
        return $records;
    }
}
