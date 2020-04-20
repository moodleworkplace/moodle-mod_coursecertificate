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
 * mod_coursecertificate data generator.
 *
 * @package    mod_coursecertificate
 * @category   test
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * mod_coursecertificate data generator class.
 *
 * @package    mod_coursecertificate
 * @category   test
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_generator extends testing_module_generator {

    /**
     * @var int keep track of how many chapters have been created.
     */
    protected $chaptercount = 0;

    /**
     * To be called from data reset code only,
     * do not use in tests.
     * @return void
     */
    public function reset() {
        $this->chaptercount = 0;
        parent::reset();
    }

    /**
     * Creates an instance of the module for testing purposes.
     *
     * Module type will be taken from the class name. Each module type may overwrite
     * this function to add other default values used by it.
     *
     * @param array|stdClass $record data for module being generated. Requires 'course' key
     *     (an id or the full object). Also can have any fields from add module form.
     * @param null|array $options general options for course module. Since 2.6 it is
     *     possible to omit this argument by merging options into $record
     * @return stdClass record from module-defined table with additional field
     *     cmid (corresponding id in course_modules table)
     */
    public function create_instance($record = null, array $options = null) {
        $record = (array)$record;
        if (empty($record['template'])) {
            throw new coding_exception('Certificate template is required to create an instance of mod_coursecertificate');
        }

        $defaultsettings = [
            'automaticsend' => 0,
            'expires' => 0
        ];
        foreach ($defaultsettings as $name => $value) {
            if (!isset($record[$name])) {
                $record[$name] = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }

}
