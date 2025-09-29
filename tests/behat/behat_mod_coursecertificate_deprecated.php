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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_deprecated_base.php');

/**
 * Deprecated Behat step definitions for Course certificate
 *
 * @package     mod_coursecertificate
 * @copyright   2025 Paul Holden <paulh@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_coursecertificate_deprecated extends behat_deprecated_base {

    /**
     * Opens the activity chooser and opens the activity/resource form page. Sections 0 and 1 are also allowed on frontpage.
     *
     * @Given I add a new instance of coursecertificate module to course :coursefullname section :sectionnum
     * @param string $coursefullname
     * @param int $sectionnum
     * @deprecated Since Workplace 5.1
     */
    public function i_add_a_new_instance_of_coursecertificate_module_to_course_section(
        string $coursefullname,
        int $sectionnum,
    ) {
        $this->deprecated_message('behat_course::i_add_to_course_section');
        $this->execute(
            [behat_course::class, 'i_add_to_course_section'],
            ['coursecertificate', $coursefullname, $sectionnum],
        );
    }

    /**
     * Check that the manual completion button for the activity is disabled.
     *
     * @Given /^the manual completion button for "(?P<activityname>(?:[^"]|\\")*)" course certificate should be disabled$/
     * @param string $activityname The activity name.
     * @deprecated Since Workplace 5.1
     */
    public function the_manual_completion_button_for_activity_coursecertificate_should_be_disabled(string $activityname): void {
        $this->deprecated_message('behat_completion::the_manual_completion_button_for_activity_should_be_disabled');
        $this->execute(
            [behat_completion::class, 'the_manual_completion_button_for_activity_should_be_disabled'],
            [$activityname],
        );
    }

    /**
     * Check that the activity has the given automatic completion condition.
     *
     * phpcs:ignore
     * @Given /^"(?P<activityname>(?:[^"]|\\")*)" course certificate should have the "(?P<conditionname>(?:[^"]|\\")*)" completion condition$/
     * @param string $activityname The activity name.
     * @param string $conditionname The automatic condition name.
     * @deprecated Since Workplace 5.1
     */
    public function activity_coursecertificate_should_have_the_completion_condition(
        string $activityname,
        string $conditionname,
    ): void {
        $this->deprecated_message('behat_completion::activity_should_have_the_completion_condition');
        $this->execute(
            [behat_completion::class, 'activity_should_have_the_completion_condition'],
            [$activityname, $conditionname],
        );
    }

    /**
     * Step to open current course or activity settings page (language string changed between 3.11 and 4.0)
     *
     * @When /^I open course or activity settings page$/
     * @deprecated Since Workplace 5.1
     */
    public function i_open_course_or_activity_settings_page(): void {
        $this->deprecated_message('behat_navigation::i_navigate_to_in_current_page_administration');
        $this->execute(
            [behat_navigation::class, 'i_navigate_to_in_current_page_administration'],
            [get_string('settings')],
        );
    }
}
