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

namespace mod_coursecertificate\local\tool_brickfield\areas;

use tool_brickfield\local\areas\module_area_base;

/**
 * Area class for coursecertificate intro.
 *
 * @package    mod_coursecertificate
 * @copyright  2021 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class intro extends module_area_base {

    /**
     * Get table name.
     *
     * @return string
     */
    public function get_tablename(): string {
        return 'coursecertificate';
    }

    /**
     * Get field name.
     *
     * @return string
     */
    public function get_fieldname(): string {
        return 'intro';
    }

    /**
     * Check if the system plugin is available.
     *
     * @return bool
     */
    public function is_available(): bool {
        return true;
    }

    /**
     * Return the component
     *
     * @return string
     */
    public function get_component(): string {
        return 'mod_coursecertificate';
    }
}
