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
 * mod_coursecertificate steps definitions.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Moodle\BehatExtension\Exception\SkippedException;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps definitions for mod_coursecertificate.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_coursecertificate extends behat_base {
    /**
     * Check that the manual completion button for the activity is disabled.
     *
     * @Given /^the manual completion button for "(?P<activityname>(?:[^"]|\\")*)" course certificate should be disabled$/
     * @param string $activityname The activity name.
     */
    public function the_manual_completion_button_for_activity_coursecertificate_should_be_disabled(string $activityname): void {
        global $CFG;
        // Execute only on Moodle 3.11 and above. Skip for previous versions.
        if ($CFG->version < 2021050700) {
            throw new SkippedException('Moodle version is too low.');
        }
        $this->execute("behat_completion::the_manual_completion_button_for_activity_should_be_disabled", [$activityname]);
    }

    /**
     * Check that the activity has the given automatic completion condition.
     *
     * phpcs:ignore
     * @Given /^"(?P<activityname>(?:[^"]|\\")*)" course certificate should have the "(?P<conditionname>(?:[^"]|\\")*)" completion condition$/
     * @param string $activityname The activity name.
     * @param string $conditionname The automatic condition name.
     */
    public function activity_coursecertificate_should_have_the_completion_condition(string $activityname,
                                                                                    string $conditionname): void {
        global $CFG;
        // Execute only on Moodle 3.11 and above. Skip for previous versions.
        if ($CFG->version < 2021050700) {
            throw new SkippedException('Moodle version is too low.');
        }
        $this->execute("behat_completion::activity_should_have_the_completion_condition", [$activityname, $conditionname]);
    }
}
